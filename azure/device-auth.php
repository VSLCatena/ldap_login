<?php

if (! defined('PHPWG_ROOT_PATH')) {
    exit('Hacking attempt!');
}

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

function device_auth_request()
{
    global $ld_config, $ld_log;
    define('TENANT_ID', $ld_config->getValue('ld_azure_tenant_id'));
    define('CLIENT_ID', $ld_config->getValue('ld_azure_client_id'));
    define('AZURE_SCOPES', $ld_config->getValue('ld_azure_scopes'));

    $auth_base = str_replace('{TENANT_ID}', $ld_config->getValue('ld_azure_tenant_id'), $ld_config->getValue('ld_azure_auth_url'));
    $oAuthURL  = str_replace('authorize', 'devicecode', $auth_base);
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Initializing OAuth2 devicelogin');
    $client = new Client();

    try {
        $options = [
            'form_params' => [
                'client_id' => CLIENT_ID,
                'scope'     => AZURE_SCOPES,
            ],
            // These options are needed to enable getting
            // the response body from a 4xx response
            'http_errors' => true,
            'curl'        => [
                CURLOPT_FAILONERROR => false,
            ],
        ];
        $request     = new Request('POST', $oAuthURL, $headers = []);
        $response    = $client->sendAsync($request, $options)->wait();
        $reponseData = json_decode($response->getBody()->getContents());
        pwg_set_session_var('device_code', $reponseData->device_code);
    }
    catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $response);
    }
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> DeviceLogin: ' . $reponseData->user_code);
}
