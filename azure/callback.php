<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

function Callback(){
    global $ld_config,$ld_log;
    define('TENANT_ID', $ld_config->getValue('ld_azure_tenant_id'));
    define('CLIENT_ID', $ld_config->getValue('ld_azure_client_id'));
    define('CLIENT_SECRET', $ld_config->getValue('ld_azure_client_secret'));
    define('REDIRECT_URI', $ld_config->getValue('ld_azure_redirect_uri'));
    
    $jwks_url = str_replace("{TENANT_ID}",TENANT_ID,$ld_config->getValue('ld_azure_jwks_url'));
    $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Initializing OAuth2 login");
    $client = new Client();
    $token_url = str_replace("{TENANT_ID}",TENANT_ID,$ld_config->getValue('ld_azure_token_url'));
    try{
        $tokenResponse = $client->post($token_url, [
            'form_params' => [
                'code' => $_GET['code'],
                'grant_type' => 'authorization_code',
                'client_id' => CLIENT_ID,
                'client_secret' => CLIENT_SECRET,
                'redirect_uri' => REDIRECT_URI,
            ],
            // These options are needed to enable getting
            // the response body from a 4xx response
            'http_errors' => true,
            'curl' => [
                CURLOPT_FAILONERROR => false
            ]
        ]);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $tokenResponse = $e->getResponse();
    }

    if ($tokenResponse->getStatusCode() == 200) {
        // Return the access_token
        $responseBody = json_decode($tokenResponse->getBody()->getContents());
        $accessToken = $responseBody->access_token;
        $idToken = $responseBody->id_token;
        $userResource = array();
        try {
            $azureKeys = json_decode(file_get_contents($jwks_url),true);
            $decodedIdToken = JWT::decode($idToken, JWK::parseKeySet($azureKeys,'RS256')); //works
            $userResource['claim']=$decodedIdToken->{$ld_config->getValue('ld_azure_claim_name')} ?? array();
        } catch (\Exception $e) {
            $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Exception catched:" . $e->getMessage() );
        }
        
        // currently unable to decode access token
        
        $userIdentifier=$ld_config->getValue('ld_azure_user_identifier');
        $resourceClient = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken
        ];
        $request= new Request('GET', $ld_config->getValue('ld_azure_resource_url'), $headers);        
        $userResource['data'] = json_decode($resourceClient->sendAsync($request)->wait()->getBody(),true);
        $jwt_data=array(
            "access_token" => array(
                "app_displayname" => $decodedAccessToken ->app_displayname ?? null,
                "appid" =>$decodedAccessToken ->app_id ?? null,
                "scp" =>$decodedAccessToken ->scp ?? null,
                "tid" =>$decodedAccessToken ->tid ?? null,
                "tenant_region_scope" =>$decodedAccessToken ->tenant_region_scope ?? null,
                "ipaddr" =>$decodedAccessToken ->ipaddr ?? null,
                "idtyp" =>$decodedAccessToken ->idtyp ?? null,
                "unique_name" =>$decodedAccessToken ->unique_name ?? null,
                "upn" =>$decodedAccessToken ->upn ?? null,
                "name" =>$decodedAccessToken ->name ?? null,
            ),
            "id_token" => array(
                "roles" => $decodedIdToken ->roles ?? null,
                "groups" => $decodedIdToken ->groups ?? null
            )
        );
        
        pwg_set_session_var('userResource',$userResource );
        pwg_set_session_var('jwt_data_test',$jwt_data );
        
        //echo("<pre>");print_r($userResource);
		$ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Oauth2_login(Array,$userIdentifier)");
        Oauth2_login($userResource,$userIdentifier);
        
    } else if (preg_match('/^4[0-9]+/', $tokenResponse->getStatusCode())){
        // Check the error in the response body
        $responseBody = json_decode($tokenResponse->getBody()->getContents());
        if (isset($responseBody->error)) {
            $error = $responseBody->error;
            $error_description = $responseBody->error_description;
            // authorization_pending means we should keep polling
            if (strcmp($error, 'authorization_pending') != 0) {
                $ld_log->error("[".basename(__FILE__)."/".__FUNCTION__."]>Token endpoint returned: " . $error . " " . $error_description);
                return false;
            }
        }
    }
}

if (isset($_GET['code']) && isset($_GET['state'])) {
    global $ld_config,$ld_log;
    $state = pwg_get_session_var('oauth2_state');
    $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> State of Session: $state , ". $_GET['state']);
    if ($_GET['state'] != $state) {
        $ld_log->error("[".basename(__FILE__)."/".__FUNCTION__."]> invalid state");
        return false;
    }
    return Callback();
} else {
    return false;
}
