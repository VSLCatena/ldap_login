<?php

if (! defined('PHPWG_ROOT_PATH')) {
    exit('Hacking attempt!');
}

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

function AccessTokenRequest($token_url, $options, $headers)
{
    try {
        $client = new Client([
            'timeout' => 1000,
        ]);
        $request       = new Request('POST', $token_url, $headers);
        $tokenResponse = $client->sendAsync($request, $options)->wait();
    }
    catch (GuzzleHttp\Exception\ClientException $e) {
        $tokenResponse = $e->getResponse();
    }

    return $tokenResponse;
}

function Callback($options)
{
    global $ld_config, $ld_log;
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Initializing OAuth2 login');
    $token_url     = str_replace('{TENANT_ID}', TENANT_ID, $ld_config->getValue('ld_azure_token_url'));
    $tokenResponse = AccessTokenRequest($token_url, $options, $headers = []);

    if ($tokenResponse->getStatusCode() == 200) {
        $jwks_url = str_replace('{TENANT_ID}', TENANT_ID, $ld_config->getValue('ld_azure_jwks_url'));
        // Return the access_token
        $responseBody = json_decode($tokenResponse->getBody()->getContents());
        $accessToken  = $responseBody->access_token;
        $idToken      = $responseBody->id_token;
        $userResource = [];

        try {
            $azureKeys             = json_decode(file_get_contents($jwks_url), true);
            $decodedIdToken        = JWT::decode($idToken, JWK::parseKeySet($azureKeys, 'RS256')); // works
            $userResource['claim'] = $decodedIdToken->{$ld_config->getValue('ld_azure_claim_name')} ?? [];
        }
        catch (\Exception $e) {
            $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Exception catched:' . $e->getMessage());
        }

        // currently unable to decode access token

        $userIdentifier = $ld_config->getValue('ld_azure_user_identifier');
        $resourceClient = new Client();
        $headers        = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];
        $request              = new Request('GET', $ld_config->getValue('ld_azure_resource_url'), $headers);
        $userResource['data'] = json_decode($resourceClient->sendAsync($request)->wait()->getBody(), true);
        $jwt_data             = [
            'access_token' => [
                'app_displayname'     => $decodedAccessToken->app_displayname     ?? null,
                'appid'               => $decodedAccessToken->app_id              ?? null,
                'scp'                 => $decodedAccessToken->scp                 ?? null,
                'tid'                 => $decodedAccessToken->tid                 ?? null,
                'tenant_region_scope' => $decodedAccessToken->tenant_region_scope ?? null,
                'ipaddr'              => $decodedAccessToken->ipaddr              ?? null,
                'idtyp'               => $decodedAccessToken->idtyp               ?? null,
                'unique_name'         => $decodedAccessToken->unique_name         ?? null,
                'upn'                 => $decodedAccessToken->upn                 ?? null,
                'name'                => $decodedAccessToken->name                ?? null,
            ],
            'id_token' => [
                'roles'  => $decodedIdToken->roles  ?? null,
                'groups' => $decodedIdToken->groups ?? null,
            ],
        ];

        pwg_set_session_var('userResource', $userResource);
        pwg_set_session_var('jwt_data', $jwt_data);

        $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . "]> Oauth2_login(false,Array,$userIdentifier)");
        Oauth2_login(false, $userResource, $userIdentifier);
    }
    elseif (preg_match('/^4[0-9]+/', $tokenResponse->getStatusCode())) {
        // Check the error in the response body
        $responseBody = json_decode($tokenResponse->getBody()->getContents());

        if (isset($responseBody->error)) {
            $error             = $responseBody->error;
            $error_description = $responseBody->error_description;

            // authorization_pending means we should keep polling
            if (strcmp($error, 'authorization_pending') != 0) {
                $ld_log->error('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']>Token endpoint returned: ' . $error . ' ' . $error_description);

                return false;
            }
        }
    }
}

global $ld_config, $ld_log;
define('TENANT_ID', $ld_config->getValue('ld_azure_tenant_id'));
define('CLIENT_ID', $ld_config->getValue('ld_azure_client_id'));
define('CLIENT_SECRET', $ld_config->getValue('ld_azure_client_secret'));
define('REDIRECT_URI', $ld_config->getValue('ld_azure_redirect_uri'));

if (isset($_GET['code'], $_GET['state'])) {
    $state = pwg_get_session_var('oauth2_state');
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> User Authorization Flow');
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . "]> State of Session: $state , " . $_GET['state']);

    if ($_GET['state'] != $state) {
        $ld_log->error('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> invalid state');

        return false;
    }
    $options = [
        'form_params' => [
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code'],
            'client_id'     => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'redirect_uri'  => REDIRECT_URI,
        ],
        // These options are needed to enable getting
        // the response body from a 4xx response
        'http_errors' => true,
        'curl'        => [
            CURLOPT_FAILONERROR => false,
        ],
    ];

    return Callback($options);
}

if (isset($_GET['device_code'])) {
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Device Authorization Flow');
    $options = [
        'form_params' => [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
            'code'       => pwg_get_session_var('device_code'),
            'client_id'  => CLIENT_ID,
        ],
    ];

    return Callback($options);
}

return false;
