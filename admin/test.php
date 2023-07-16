<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template, $ld_config, $ld_log;
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/test.tpl') );
$template->assign(
    array(
        'PLUGIN_ACTION' => get_root_url().'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-test',
        'PLUGIN_CHECK' => get_root_url().'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-test',
    ));

    
    ###
    ### POST (submit/load page)
    ###
    
    // Checking LDAP configuration --> rewrite!
    
    
if (isset($_POST['check_ldap']) ){
    $ld_log->debug("[function]> Ldap_Login Test");
    if($ld_config->getValue('ld_anonbind') == 0){
        $ld_ldap=new ldap();
        $result = $ld_ldap->authenticate($_POST['USERNAME'], $_POST['PASSWORD'],$test = True);
        if($result->bindSuccess == True && $result->credentialsCorrect == True) {
            $statusMessage = '<p style="color:green;">Configuration LDAP OK:<pre>' . json_encode($result, JSON_PRETTY_PRINT) . '</pre></p>';
        } elseif($result->credentialsCorrect == False) {
            $statusMessage = '<p style="color:orange;">Binding OK, but there are errors: '. $result->lastError . '<br><pre>' . json_encode($result, JSON_PRETTY_PRINT) . '</pre></p>';
        } else {
            $statusMessage = '<p style="color:red;">Error :'.$result->lastError . '<br><pre>' . json_encode($result, JSON_PRETTY_PRINT) . '</pre></p>';
        }
        $template->assign('LD_CHECK_LDAP',$statusMessage);
        #$username = $ld_ldap->ldap_search_dn($_POST['USERNAME']);
        #$error=$ld_ldap->check_ldap();
/*         if($error==1 && $_POST['USERNAME']) { //need to clean this part..
            if ($ld_ldap->ldap_bind_as($_POST['USERNAME'],$_POST['PASSWORD'])){
                if($ld_ldap->check_ldap_group_membership($username,$p_username)){
                                $template->assign('LD_CHECK_LDAP','<p style="color:green;">Configuration LDAP OK : '.$username.'</p>');
                } else {
                    $template->assign('LD_CHECK_LDAP','<p style="color:orange;">Credentials OK, Check GroupMembership for: '.$username.'</p>');
                }
                    }
                    else {
                $template->assign('LD_CHECK_LDAP','<p style="color:red;"> Binding OK, but check credentials on server '.$ld_config->getValue('ld_host').' for user '.$username.'</p>');
                    }
        } elseif($error==1 && !$username){
            $template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : Binding OK, but no valid DN found on server '.$ld_config->getValue('ld_host').' for user '.$username.'</p>');
        } elseif($error && $username){
            $template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : Binding OK, but check credentials on '.$ld_config->getValue('ld_host').' for user '.$username.'</p>');
        } else {
            $template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : '.$error.' for binding on server '.$ld_config->getValue('ld_host').' for user '.$username.', check your binding!</p>');
        } */

    }
    if($ld_config->getValue('ld_anonbind') == 1){
        //anonymous binding
        $error=$ld_ldap->check_ldap();
        $username = $ld_ldap->ldap_search_dn($_POST['USERNAME']);
        if($error==1) {
            if(!$username){
                $template->assign('LD_CHECK_LDAP','<p style="color:green;">Configuration LDAP OK, user not found </p>');
            }
            else {
                $template->assign('LD_CHECK_LDAP','<p style="color:green;">Configuration LDAP OK, user: '.ldap_explode_dn($username,1)[0] .' </p>');
            }
        }
        else {
            $template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : '.$error.' for binding on server, check your config!</p>');
        }
        
    }
}
if($ld_config->getValue('ld_auth_type') == 'ld_auth_azure'){
    if(null !== $ld_config->getValue('ld_azure_tenant_id')){
        $auth_base = str_replace("{TENANT_ID}",$ld_config->getValue('ld_azure_tenant_id'),$ld_config->getValue('ld_azure_auth_url'));
    }
    if(null !== $ld_config->getValue('ld_azure_client_id') && null !== $ld_config->getValue('ld_azure_redirect_uri') && null !== $ld_config->getValue('ld_azure_scopes' ) ){
        $form_params = array(
            'response_type' => 'code',
            'client_id' => $ld_config->getValue('ld_azure_client_id'),
            'redirect_uri' => $ld_config->getValue('ld_azure_redirect_uri'),
            'scope' => $ld_config->getValue('ld_azure_scopes'),
            'prompt' =>'select_account'
        );
        $oAuthURL = $auth_base . '?' .http_build_query($form_params);
        $template->assign('OAUTH_URL',$oAuthURL);
        $jwt_data = pwg_get_session_var('jwt_data_test' );
        $template->assign('JWT_CONTENT',$jwt_data);
    }
}
$template->assign('LDAP_LOGIN_PATH',LDAP_LOGIN_PATH);
$template->assign('LD_AUTH_TYPE',$ld_config->getValue('ld_auth_type'));
$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');
?>
