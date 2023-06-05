<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

if (isset($_GET['code'])) {
    global $ld_config,$ld_log;
    $state = pwg_get_session_var('oauth2_state');
    $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> State of Session: $state , ". $_GET['state']);
    if ($_GET['state'] != $state) {
        $ld_log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> invalid state");
        return false;
    }
    echo("<pre>");debug_backtrace();debug_print_backtrace();echo("</pre>");
    define('TENANT_ID', $ld_config->getValue('ld_azure_tenant_id'));
    define('CLIENT_ID', $ld_config->getValue('ld_azure_client_id'));
    define('CLIENT_SECRET', $ld_config->getValue('ld_azure_client_secret'));
    define('REDIRECT_URI', $ld_config->getValue('ld_azure_redirect_uri'));
    $jwks_url = str_replace("{TENANT_ID}",TENANT_ID,$ld_config->getValue('ld_azure_jwks_url'));
    $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> azure_login");
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
            echo 'Exception catched: ',  $e->getMessage(), "\n";
        }
        $userIdentifier=$ld_config->getValue('ld_azure_user_identifier');
        $resourceClient = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken
        ];
        $request= new Request('GET', $ld_config->getValue('ld_azure_resource_url'), $headers);          
        $userResource['data'] = json_decode($resourceClient->sendAsync($request)->wait()->getBody(),true);
        $_SESSION['access_token'] = $accessToken;
        $_SESSION['userObject'] = $userResource;
        //echo("<pre>");print_r($userResource);

		global $prefixeTable,$conf;
		// search user in piwigo database based on username & additional search on email
		$query = 'SELECT '.$conf['user_fields']['id'].' AS id FROM '.USERS_TABLE.' WHERE '.$conf['user_fields']['username'].' = \''.pwg_db_real_escape_string($userResource['data'][$userIdentifier]).'\' OR '.$conf['user_fields']['email'].' = \''.pwg_db_real_escape_string($userResource['data']['mail']).'\' ;';
		$row = pwg_db_fetch_assoc(pwg_query($query));
		$ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> username found in db:" . (!empty($row1['id'])) . " mail found in db: " . (!empty($row['id'])));
		// if query is not empty, it means everything is ok and we can continue, auth is done !
		if (!empty($row['id'])) {
		//user exist
            if($ld_config->getValue('ld_group_user_active') == 1) {
                $status=false;
            } else {
                $status='normal';
            }
            if (in_array($ld_config->getValue('ld_group_user'),  $userResource['claim'])) {
                $status='normal';
            }
            if (($ld_config->getValue('ld_group_admin_active') == 1) && (in_array($ld_config->getValue('ld_group_admin'),  $userResource['claim']))) {
                $status='admin';
            }
            if (($ld_config->getValue('ld_group_webmaster_active') == 1) && (in_array($ld_config->getValue('ld_group_webmaster'),  $userResource['claim']))) {
                $status='webmaster';
            }
            if($status == false){
                trigger_notify('login_failure', stripslashes($userResource['data'][$userIdentifier]));
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> User does not have role / claim as user to login");
                return false;
            }
            $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Update username in db based on return values of OAuth2 & userIdentifier");                                
            $query = 'UPDATE `'.USERS_TABLE.'` SET `username` =  \''.pwg_db_real_escape_string($userResource['data'][$userIdentifier]).'\' WHERE `'.USERS_TABLE.'`.`id` = ' . $row['id'] . ';';
            pwg_query($query);
            $query = 'UPDATE `'.USER_INFOS_TABLE.'` SET `status` = "'. $status . '" WHERE `'.USER_INFOS_TABLE.'`.`user_id` = ' . $row['id'] . ';';
            pwg_query($query);        
			log_user($row['id'], False);
			trigger_notify('login_success', stripslashes($userResource['data'][$userIdentifier]));
            redirect('index.php');
			return true;
		} else {
		//user doest not (yet) exist
			$ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> User found in Azure but not in SQL");
			//this is where we check we are allowed to create new users upon that.
			if ($ld_config->getValue('ld_allow_newusers')) {
				$ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Creating new user and store in SQL");
				$mail=null;
				if($ld_config->getValue('ld_use_mail')){
					//retrieve LDAP e-mail address and create a new user
					$mail = $userResource['data']['mail'];
				}
				$errors=[];
				$new_id = register_user($userResource['data'][$userIdentifier],random_password(32),$userResource['data']['mail'],true,$errors);
				if(count($errors) > 0) {
					foreach ($errors as &$e){
						$ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ".$e, 'ERROR');
					}
					return false;
				}
                if($ld_config->getValue('ld_group_user_active') == 1) {
                    $status=false;
                } else {
                    $status='normal';
                }
                if (in_array($ld_config->getValue('ld_group_user'),  $userResource['claim'])) {
                    $status='normal';
                }
                if (( $ld_config->getValue('ld_group_admin_active') == 1 ) && ((in_array($ld_config->getValue('ld_group_admin'),  $userResource['claim'])))) {
                    $status='admin';
                }
                if (( $ld_config->getValue('ld_group_webmaster_active') == 1) && ((in_array($ld_config->getValue('ld_group_webmaster'),  $userResource['claim'])))) {
                    $status='webmaster';
                }
                if($status == false){
                    trigger_notify('login_failure', stripslashes($userResource['data'][$userIdentifier]));
                    $ld_log->error("[".basename(__FILE__)."/".__FUNCTION__."]> User does not have role / claim as user to login");
                    return false;
                }                              
                $query = 'UPDATE `'.USER_INFOS_TABLE.'` SET `status` = "'. $status . '" WHERE `'.USER_INFOS_TABLE.'`.`user_id` = ' . $new_id . ';';
				pwg_query($query);
                
				//Login user
				log_user($new_id, False);
				trigger_notify('login_success', stripslashes($userResource['data'][$userIdentifier]));

				//in case the e-mail address is empty, redirect to profile page
				if ($ld_config->getValue('ld_allow_profile')) {
					redirect('profile.php');
				} else {
		//                    redirect('index.php');
				}
                redirect('index.php');
				return true;
			}
			//else : this is the normal behavior ! user is not created.
			else {
				trigger_notify('login_failure', stripslashes($userResource['data'][$userIdentifier]));
				$ld_log->error("[".basename(__FILE__)."/".__FUNCTION__."]> Not allowed to create user (ld_allow_newusers=false)");
				return false;
			}
		}		


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
} else {
    return false;
}
