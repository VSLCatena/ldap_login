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
	
	$special = array(
		"LD_AZURE_CLIENT_SECRET",
		"LD_BINDPW",
		"LD_ANONBIND",
		"LD_MEMBERSHIP_USER",
	);
	$POSTVALUES = array_diff_key($_POST, array_flip($special));

	foreach ($POSTVALUES as $key => $value) {
		if(isset($_POST[strtoupper($key)])){
			$me->config[strtolower($key)] 	 = $_POST[strtoupper($key)];
		}
	}
	if(isset($_POST['LD_AZURE_CLIENT_SECRET'])){
		if($_POST['LD_AZURE_CLIENT_SECRET'] != '************************'){
			$me->config['ld_azure_client_secret'] =  $_POST['LD_AZURE_CLIENT_SECRET'];
		} else {
			// do nothing.
		}	
	}
	if($_POST['LD_BINDPW'] != '************************'){
		$me->config['ld_bindpw'] =  $_POST['LD_BINDPW'];
	} else {
		// do nothing.
	}
	//$me->config['ld_bindpw'] =  ldap_escape($_POST['LD_BINDPW'], '', LDAP_ESCAPE_DN);


	if (strlen($_POST['LD_BINDDN'])<1 && strlen($_POST['LD_BINDPW'])<1 ){
		$me->config['ld_anonbind'] = 1;
	} else {
		$me->config['ld_anonbind'] = 0;
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


# Fill template

# Automatic values
$templateKeys = array_keys($me->default_val);
$special = array(
	"LD_AZURE_CLIENT_SECRET",
	"LD_BINDPW"
);
$templateKeys = array_diff_key($templateKeys, array_flip($special));
foreach ($templateKeys as $tkey) {
	$template->assign(strtoupper($tkey),$me->config[strtolower($tkey)]);
}
# Manual values
$template->assign('LDAP_LOGIN_PATH',LDAP_LOGIN_PATH);
if($me->config['ld_azure_client_secret'] == ''){
	//only if empty then give back empty
	$template->assign('LD_AZURE_CLIENT_SECRET',$me->config['ld_azure_client_secret']);
} else {
	$template->assign('LD_AZURE_CLIENT_SECRET','************************');
}
if($me->config['ld_bindpw'] == ''){
	//only if empty then give back empty
	$template->assign('LD_BINDPW',$me->config['ld_bindpw']);
} else {
	$template->assign('LD_BINDPW','************************');
}



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