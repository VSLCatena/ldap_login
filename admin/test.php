<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template;
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/test.tpl') );
$template->assign(
  array(
    'PLUGIN_ACTION' => get_root_url().'admin.php?page=plugin-ldap_login-test',
    'PLUGIN_CHECK' => get_root_url().'admin.php?page=plugin-ldap_login-test',
    ));

$me = new Ldap();
$me->load_config();
$me->write_log("New LDAP Instance");

###
### POST (submit/load page)
###

// Checking LDAP configuration

if (isset($_POST['check_ldap']) ){
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

$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');
?>