<?php
/*
Plugin Name: Ldap_Login
Version: 12.0a
Description: Allow piwigo authentication along an ldap
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=650
Author: Kipjr (Member of Netcie)
Author URI: https://github.com/Kipjr/
Has Settings: true
*/
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+
define('LDAP_LOGIN_ID',      basename(dirname(__FILE__)));
define('LDAP_LOGIN_PATH' ,   PHPWG_PLUGINS_PATH . LDAP_LOGIN_ID . '/');
define('LDAP_LOGIN_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . LDAP_LOGIN_ID);

include_once(LDAP_LOGIN_PATH.'/class.ldap.php');
include_once(LDAP_LOGIN_PATH.'/functions_sql.inc.php');

// +-----------------------------------------------------------------------+
// | Event handlers                                                        |
// +-----------------------------------------------------------------------+

add_event_handler('init', 'ld_init');

add_event_handler('blockmanager_apply', 'ld_forgot');

add_event_handler('try_log_user','login', 0, 4);

add_event_handler('load_profile_in_template','ld_profile');

add_event_handler('get_admin_plugin_menu_links', array(&$ldap, 'ldap_admin_menu'));


// +-----------------------------------------------------------------------+
// | Admin menu loading                                                    |
// +-----------------------------------------------------------------------+

$ldap = new Ldap();
$ldap->load_config();
set_plugin_data($plugin['id'], $ldap);
unset($ldap);

// +-----------------------------------------------------------------------+
// | functions                                                             |
// +-----------------------------------------------------------------------+


function random_password( $length = 8 ) {
/**
 * Create random password
 * Depending on length, it can return :
 * 
 *    - Default:
 *       "Nh=4*CGN"   
 * 
 *    - With other length ($length=16):
 *       "9a-jn3P,AtG%u,%M"   
 *
 *
 * @since ~ 
 *
 * @param int $length
 * @return string
 */
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}

function ld_init(){
/**
 * Piggyback function after initialising Piwigo
 * 		Loads languages
 *
 *
 * @since ~ 
 *
 */
	load_language('plugin.lang', LDAP_LOGIN_PATH);
}

function ld_forgot(){
/**
 * Piggyback function after initialising menu and blocks
 * 		Loads alternative link for 'forgot password' via Smarty Template
 *
 *
 * @since ~ 
 *
 */
	global $template;
	$base = new Ldap();
	$base->load_config();
	if(!($base->config['ld_forgot_url']=="")){
		$template->assign('U_LOST_PASSWORD',$base->config['ld_forgot_url']);
	}
	unset($base);
}


function login($success, $username, $password, $remember_me){
/**
 * Piggyback function for logging in
 * forces the username lowercase and checks if u/p is not null.
 * Tries to connect with LDAP and checks if dn, u/p and group membership is valid
 * 
 *    - Invalid 
 *         No login
 *
 *    - User DN found using LDAP
 *        > Search in Piwigo Database
 *		  	>> Found, check admin status and login succesfull
 *			>> Not found,
 *				>> create user
 *				>> do not create user (not allow due to config)
 * 	
 *
 *
 * @since ~
 *
 * @param ?????? $success
 * @param string $username 
 * @param string $password
 * @param boolean $remember_me
 * @return boolean
 */
 
	//force users to lowercase name, or else duplicates will be made, like user,User,uSer etc.
	$username=strtolower($username);
	global $conf;
	if(strlen(trim($username)) == 0 || strlen(trim($password)) == 0){
			trigger_notify('login_failure', stripslashes($username));
			return false; // wrong user/password or no group access
	}
	
	$obj = new Ldap();
	$obj->load_config();
	$obj->write_log("New LDAP Instance");
	$obj->write_log("[function]> login");
	$obj->ldap_conn() or die("Unable to connect LDAP server : ".$obj->getErrorString());

	$user_dn = $obj->ldap_search_dn($username);	// retrieve the userdn

	// If we have userdn, attempt to login an check user's group access via LDAP
	if (!($user_dn && $obj->ldap_bind_as($user_dn,$password) &&
		$obj->check_ldap_group_membership($user_dn, $username))) {
			trigger_notify('login_failure', stripslashes($username));
			$obj->write_log("[login]> wrong u/p or no group access");
			return false; // wrong user/password or no group access
	}
	

	
	// search user in piwigo database
	$query = 'SELECT '.$conf['user_fields']['id'].' AS id FROM '.USERS_TABLE.' WHERE '.$conf['user_fields']['username'].' = \''.pwg_db_real_escape_string($username).'\' ;';
	$row = pwg_db_fetch_assoc(pwg_query($query));

	// if query is not empty, it means everything is ok and we can continue, auth is done !
  	if (!empty($row['id'])) {
		
		if ($obj->config['ld_group_webmaster_active'] || $obj->config['ld_group_admin_active']) {
			//check admin status
			$uid = pwg_db_real_escape_string($row['id']);
			$group_query = 'SELECT user_id, status FROM piwigo_user_infos  WHERE `piwigo_user_infos`.`user_id` = ' . $uid . ';';
			$pwg_status = pwg_db_fetch_assoc(pwg_query($group_query))['status']; //status in Piwigo
			$webmaster = null; // or True or False
			$admin = null;  // or True or False
			$obj->write_log("[login]> info: $username, status:$pwg_status");
			
			//enable upgrade / downgrade from webmaster
			if ($obj->config['ld_group_webmaster_active']==True) {
				$group_webm = $obj->config['ld_group_webmaster'];
				//is user webmaster?
				$webmaster = $obj->check_ldap_group_membership($user_dn, $username,$group_webm); //is webmaster in LDAP?
			}
			
			//enable upgrade / downgrade from admin
			if ($obj->config['ld_group_admin_active']==True) {
				$group_adm = $obj->config['ld_group_admin'];
				//is user admin?
				$admin = $obj->check_ldap_group_membership($user_dn, $username,$group_adm); //is admin in LDAP?
			}
			$obj->write_log("[login]> Admin_active:" . $obj->config['ld_group_admin_active'] ." is_admin:$admin , WebmasterActive:" . $obj->config['ld_group_webmaster_active'] . " is_webmaster:$webmaster");
			if (is_null($webmaster) && is_null($admin)) {}//ignore 
			elseif($webmaster==false && $admin==true) {$status='admin';} //  admin | when NOT webmaster and admin.
			elseif($webmaster==true && (!is_null($admin))) {$status='webmaster';} // webmaster | when webmaster and whatever value for admin.
			
			elseif(is_null($webmaster)) {
				if($pwg_status=='webmaster') {}//ignore & keep webmaster
			    elseif($admin) {$status='admin';} // admin
				elseif(!($admin)) {$status='normal';} // normal
			}
			elseif(is_null($admin)){
				if($webmaster) {$status='webmaster';} // webmaster
				elseif($pwg_status=='admin') {}//ignore & keep admin
				elseif(!($webmaster)) {$status='normal';} // normal
			}
			
			if(isset($status)){
				$obj->write_log("[login]> Target status $status");
				if ($status!=$pwg_status) {
					$query = '
						UPDATE `piwigo_user_infos` SET `status` = "'. $status . '" WHERE `piwigo_user_infos`.`user_id` = ' . $uid . ';';
					pwg_query($query);
					$obj->write_log("[login]> Changed $username with id " . $row['id'] . " from ".$pwg_status. " to " . $status);
					include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
					invalidate_user_cache();
				}
			}
		}
		
  		log_user($row['id'], $remember_me);
  		trigger_notify('login_success', stripslashes($username));
		$obj->write_log("[login]> User " . $username . " found in SQL DB and login success");
  		return true;
  	}
  	
  	// if query is empty but ldap auth is done we can create a piwigo user if it's said so !
  	else {
		$obj->write_log("[login]> User found in LDAP but not in SQL");
		// this is where we check we are allowed to create new users upon that.
		if ($obj->config['ld_allow_newusers']) {
			$obj->write_log("[login]> Creating new user and store in SQL");
			$mail=null;
			if($obj->config['ld_use_mail']){
				// retrieve LDAP e-mail address and create a new user
				$mail = $obj->ldap_get_email($user_dn);
			}
			$errors=[];
			$new_id = register_user($username,random_password(8),$mail,true,$errors);
			if(count($errors) > 0) {
				foreach ($errors as &$e){
					$obj->write_log("[login]> ".$e, 'ERROR');
				}
				return false;
			}
			// Login user
			log_user($new_id, False);
			trigger_notify('login_success', stripslashes($username));

			// in case the e-mail address is empty, redirect to profile page
			if ($obj->config['ld_allow_profile']) {
				redirect('profile.php');
			}
			else {
				redirect('index.php');
			}
			return true;
		}
		// else : this is the normal behavior ! user is not created.
		else {
			trigger_notify('login_failure', stripslashes($username));
			$obj->write_log("[login]> Not allowed to create user (ld_allow_newusers=false)");
			return false;
		}
  	}
}


function ld_profile(){
/**
 * Piggyback function for profile page
 * Removes email/password block
 * 	
 *
 *
 * @since 2.10.1
 *
 */
	//removes the Profile/Registration block for new users.
	global $template;
	global $userdata;
	if($userdata['id']>2){
		$template->assign('SPECIAL_USER', True);
	}
}


/* function update_user($username,$id) {
	$up = new Ldap();
	$up->load_config();
	$up->ldap_conn() or error_log("Unable to connect LDAP server : ".$up->getErrorString());

	// update user piwigo rights / access according to ldap. Only if it's webmaster / admin, so no normal !
	if($up->ldap_status($username) !='normal') {
		single_update(USER_INFOS_TABLE,array('status' => $up->ldap_status($username)),array('user_id' => $id));
	}

	// search groups
	$group_query = 'SELECT name, id FROM '.GROUPS_TABLE.';';
	
	$result = pwg_query($group_query);
	$inserts = array();
	while ($row = pwg_db_fetch_assoc($result))
	{
		if($up->user_membership($username, $up->ldap_group($row['name']))) {
			$inserts[] = array('user_id' => $id,'group_id' => $row['id']);
		}
	}

	if (count($inserts) > 0)
	{
		mass_inserts(USER_GROUP_TABLE, array('user_id', 'group_id'), $inserts,array('ignore'=>true));
	}
} */

?>
