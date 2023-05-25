<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template;
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/configuration.tpl') );
$template->assign(
  array(
    'PLUGIN_ACTION' => get_root_url().'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-configuration',
    'PLUGIN_CHECK' => get_root_url().'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-configuration',
    ));

$me = new Ldap();
$me->load_config();
$me->write_log("New LDAP Instance");

###
### POST (submit/load page)
###

if (isset($_POST['RESET_AD'])) {
	 ld_sql('update','reset_ad');
	 $me->write_log("Default values for MS Active directory loaded");
	 $me->load_config();
}
if (isset($_POST['RESET_OL'])) {
	ld_sql('update','reset_openldap');
	$me->write_log("Default values for OpenLDAP loaded");
	$me->load_config();
}


// Save LDAP configuration when submitted
if (isset($_POST['save']) or isset($_POST['savetest'])){
	$me->config['ld_forgot_url'] 	 = $_POST['LD_FORGOT_URL'];
	$me->config['ld_debug_location'] 	 = $_POST['LD_DEBUG_LOCATION'];
	$me->config['ld_debug_level'] 	 = $_POST['LD_DEBUG_LEVEL'];
	$me->config['ld_debug_php'] 	 = $_POST['LD_DEBUG_PHP'];
	
	$me->config['ld_auth_type']	= $_POST['LD_AUTH_TYPE'];
	if(isset($_POST['LD_AZURE_CLIENT_ID'])){
		$me->config['ld_azure_client_id'] = $_POST['LD_AZURE_CLIENT_ID'];
	}
	if(isset($_POST['LD_AZURE_CLIENT_SECRET'])){
		if($_POST['LD_AZURE_CLIENT_SECRET'] != '************************'){
			$me->config['ld_azure_client_secret'] =  $_POST['LD_AZURE_CLIENT_SECRET'];
		} else {
			// do nothing.
		}	
	}
	if(isset($_POST['LD_AZURE_TENANT_ID'])){
		$me->config['ld_azure_tenant_id']  = $_POST['LD_AZURE_TENANT_ID'];
	}
	if(isset($_POST['LD_AZURE_REDIRECT_URI'])){
		$me->config['ld_azure_redirect_uri']  = $_POST['LD_AZURE_REDIRECT_URI'];
	}
	if(isset($_POST['LD_AZURE_SCOPES'])){
		$me->config['ld_azure_scopes']  = $_POST['LD_AZURE_SCOPES'];
	}	
	$me->config['ld_host'] 	 = $_POST['LD_HOST'];
	$me->config['ld_port']      = $_POST['LD_PORT'];
	$me->config['ld_basedn']    = $_POST['LD_BASEDN'];

	$me->config['ld_user_class']   = $_POST['LD_USER_CLASS'];
	$me->config['ld_user_attr']   = $_POST['LD_USER_ATTR'];
	$me->config['ld_user_filter']   = $_POST['LD_USER_FILTER']; 
	
	$me->config['ld_group_class']   = $_POST['LD_GROUP_CLASS'];
	$me->config['ld_group_filter']   = $_POST['LD_GROUP_FILTER'];
	$me->config['ld_group_attr']   = $_POST['LD_GROUP_ATTR'];
	$me->config['ld_group_desc']   = $_POST['LD_GROUP_DESC'];
	
	$me->config['ld_group_member_attr']   = $_POST['LD_GROUP_MEMBER_ATTR'];
	$me->config['ld_user_member_attr']   = $_POST['LD_USER_MEMBER_ATTR'];
	
	$me->config['ld_group_user']   = $_POST['LD_GROUP_USER'];
	$me->config['ld_group_admin']   = $_POST['LD_GROUP_ADMIN'];
	$me->config['ld_group_webmaster']   = $_POST['LD_GROUP_WEBMASTER'];
	
	$me->config['ld_binddn'] = $_POST['LD_BINDDN']; //reverted, did not work
	//$me->config['ld_binddn'] = ldap_escape($_POST['LD_BINDDN'], '', LDAP_ESCAPE_DN);
	#$me->config['ld_bindpw'] =  $_POST['LD_BINDPW']; //reverted, did not work
	if($_POST['LD_BINDPW'] != '************************'){
		$me->config['ld_bindpw'] =  $_POST['LD_BINDPW'];
	} else {
		// do nothing.
	}
	//$me->config['ld_bindpw'] =  ldap_escape($_POST['LD_BINDPW'], '', LDAP_ESCAPE_DN);

	if (isset($_POST['LD_DEBUG'])){
		$me->config['ld_debug'] = 1;
	} else {
		$me->config['ld_debug'] = 0;
	}

	if (isset($_POST['LD_DEBUG_PHP'])){
		$me->config['ld_debug_php'] = 1;
	} else {
		$me->config['ld_debug_php'] = 0;
	}	
	
	if (isset($_POST['LD_DEBUG_CLEARUPDATE'])){
		$me->config['ld_debug_clearupdate'] = 1;
	} else {
		$me->config['ld_debug_clearupdate'] = 0;
	}

	if (strlen($_POST['LD_BINDDN'])<1 && strlen($_POST['LD_BINDPW'])<1 ){
		$me->config['ld_anonbind'] = 1;
	} else {
		$me->config['ld_anonbind'] = 0;
	}

	if (isset($_POST['LD_USE_SSL'])){
		$me->config['ld_use_ssl'] = 1;
	} else {
		$me->config['ld_use_ssl'] = 0;
	}	
	
	if (isset($_POST['LD_MEMBERSHIP_USER'])){
		$me->config['ld_membership_user'] = 1;
	} else {
		$me->config['ld_membership_user'] = 0;
	}
	
	if (isset($_POST['LD_GROUP_USER_ACTIVE'])){
		$me->config['ld_group_user_active'] = 1;
	} else {
		$me->config['ld_group_user_active'] = 0;
	}

	if (isset($_POST['LD_GROUP_ADMIN_ACTIVE'])){
		$me->config['ld_group_admin_active'] = 1;
	} else {
		$me->config['ld_group_admin_active'] = 0;
	}
	
	if (isset($_POST['LD_GROUP_WEBMASTER_ACTIVE'])){
		$me->config['ld_group_webmaster_active'] = 1;
	} else {
		$me->config['ld_group_webmaster_active'] = 0;
	}
	$me->save_config();
}

// Checking LDAP configuration

if (isset($_POST['check_ldap']) or isset($_POST['savetest'])){
	$me->ldap_conn();
	$me->write_log("[function]> Ldap_Login Test");
	if($me->config['ld_anonbind'] == 0){
		$p_username=isset($_POST['savetest'])? ldap_explode_dn($_POST['LD_BINDDN'],1)[0] : $_POST['USERNAME'];
		$p_password=isset($_POST['savetest'])? $_POST['LD_BINDPW'] : $_POST['PASSWORD'];
		
		$username = $me->ldap_search_dn($p_username);
		$error=$me->check_ldap();
		if($error==1 && $username) { //need to clean this part..
			if ($me->ldap_bind_as($username,$p_password)){
				if($me->check_ldap_group_membership($username,$p_username)){
								$template->assign('LD_CHECK_LDAP','<p style="color:green;">Configuration LDAP OK : '.$username.'</p>');
				} else {
					$template->assign('LD_CHECK_LDAP','<p style="color:orange;">Credentials OK, Check GroupMembership for: '.$username.'</p>');
				}
					}
					else {
				$template->assign('LD_CHECK_LDAP','<p style="color:red;"> Binding OK, but check credentials on server '.$me->config['uri'].' for user '.$username.'</p>');
					}
		} elseif($error==1 && !$username){
			$template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : Binding OK, but no valid DN found on server '.$me->config['uri'].' for user '.$p_username.'</p>');
		} elseif($error && $username){
			$template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : Binding OK, but check credentials on '.$me->config['uri'].' for user '.$username.'</p>');
		} else {
			$template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : '.$error.' for binding on server '.$me->config['uri'].' for user '.$p_username.', check your binding!</p>');
		}

	}
	if($me->config['ld_anonbind'] == 1){
		//anonymous binding
		$error=$me->check_ldap();
		$username = $me->ldap_search_dn($_POST['USERNAME']);
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


$template->assign('LDAP_LOGIN_PATH',LDAP_LOGIN_PATH);

$template->assign('LD_FORGOT_URL',$me->check_config('ld_forgot_url'));
$template->assign('LD_DEBUG_LOCATION',$me->check_config('ld_debug_location'));
$template->assign('LD_DEBUG',$me->check_config('ld_debug'));
$template->assign('LD_DEBUG_PHP',$me->check_config('ld_debug_php'));
$template->assign('LD_DEBUG_CLEARUPDATE',$me->check_config('ld_debug_clearupdate'));
$template->assign('LD_DEBUG_LEVEL',$me->check_config('ld_debug_level'));

$template->assign('LD_AUTH_TYPE',$me->check_config('ld_auth_type'));
$template->assign('LD_AZURE_CLIENT_ID',$me->config['ld_azure_client_id']);
#$template->assign('LD_AZURE_CLIENT_SECRET',$me->config['ld_azure_client_secret']);
if($me->config['ld_azure_client_secret'] == ''){
	//only if empty then give back empty
	$template->assign('LD_AZURE_CLIENT_SECRET',$me->config['ld_azure_client_secret']);
} else {
	$template->assign('LD_AZURE_CLIENT_SECRET','************************');
}	
$template->assign('LD_AZURE_TENANT_ID',$me->config['ld_azure_tenant_id']);
$template->assign('LD_AZURE_AUTH_URL',$me->config['ld_azure_auth_url']);
$template->assign('LD_AZURE_TOKEN_URL',$me->config['ld_azure_token_url']);
$template->assign('LD_AZURE_LOGOUT_URL',$me->config['ld_azure_logout_url']);
$template->assign('LD_AZURE_RESOURCE_URL',$me->config['ld_azure_resource_url']);
$template->assign('LD_AZURE_USER_IDENTIFIER',$me->config['ld_azure_user_identifier']);
$template->assign('LD_AZURE_REDIRECT_URI',$me->config['ld_azure_redirect_uri']);
$template->assign('LD_AZURE_SCOPES',$me->config['ld_azure_scopes']);

$template->assign('LD_HOST',$me->check_config('ld_host'));
$template->assign('LD_PORT',$me->check_config('ld_port'));
$template->assign('LD_USE_SSL',$me->check_config('ld_use_ssl'));
$template->assign('LD_BASEDN',$me->check_config('ld_basedn'));

$template->assign('LD_USER_CLASS',$me->check_config('ld_user_class'));
$template->assign('LD_USER_ATTR',$me->check_config('ld_user_attr'));
$template->assign('LD_USER_FILTER',$me->config['ld_user_filter']); 

$template->assign('LD_GROUP_CLASS',$me->check_config('ld_group_class'));
$template->assign('LD_GROUP_FILTER',$me->config['ld_group_filter']);
$template->assign('LD_GROUP_ATTR',$me->check_config('ld_group_attr'));
$template->assign('LD_GROUP_DESC',$me->check_config('ld_group_desc'));

$template->assign('LD_GROUP_MEMBER_ATTR',$me->check_config('ld_group_member_attr'));
$template->assign('LD_USER_MEMBER_ATTR',$me->check_config('ld_user_member_attr'));
$template->assign('LD_MEMBERSHIP_USER',$me->check_config('ld_membership_user'));

$template->assign('LD_GROUP_USER',$me->check_config('ld_group_user'));
$template->assign('LD_GROUP_ADMIN',$me->check_config('ld_group_admin'));
$template->assign('LD_GROUP_WEBMASTER',$me->check_config('ld_group_webmaster'));
$template->assign('LD_GROUP_USER_ACTIVE',$me->check_config('ld_group_user_active'));
$template->assign('LD_GROUP_ADMIN_ACTIVE',$me->check_config('ld_group_admin_active'));
$template->assign('LD_GROUP_WEBMASTER_ACTIVE',$me->check_config('ld_group_webmaster_active'));

//$template->assign('LD_BINDPW',$me->config['ld_bindpw']);
if($me->config['ld_bindpw'] == ''){
	//only if empty then give back empty
	$template->assign('LD_BINDPW',$me->config['ld_bindpw']);
} else {
	$template->assign('LD_BINDPW','************************');
}

$template->assign('LD_BINDDN',$me->config['ld_binddn']);

if (is_array($me->warn_msg) && sizeof($me->warn_msg)>0){
	if($me->config['ld_debug_level'] == "debug"){
		$keys=":<br>- " . implode("<br>- ", array_keys($me->warn_msg));
	}	
	$me->warn_msg['general']='Warning: (some) default values are loaded. Please edit and save your configuration' . $keys;
	foreach ($me->warn_msg as $key=>$value) {
		$template->assign('WARN_' . strtoupper($key),$value);
	}
}
$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');
?>
