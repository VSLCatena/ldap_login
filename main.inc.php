<?php
/*
Plugin Name: Ldap_Login
Version: 13.5
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

// +-----------------------------------------------------------------------+
// | Load Classess                                                         |
// +-----------------------------------------------------------------------+

require_once realpath(LDAP_LOGIN_PATH . '/vendor/autoload.php');
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;

// +-----------------------------------------------------------------------+
// | Set Event Handlers                                                    |
// +-----------------------------------------------------------------------+

add_event_handler('init', 'ld_init');

// ld_azure included in 'ld_init' 
// add_event_handler('blockmanager_apply', 'ld_azure');

add_event_handler('blockmanager_apply', 'ld_forgot');

add_event_handler('loc_begin_identification', 'ld_redirect_identification');

add_event_handler('try_log_user','login', 0, 4);

add_event_handler('load_profile_in_template','ld_profile');

add_event_handler('get_admin_plugin_menu_links', 'ldap_admin_menu');



// +-----------------------------------------------------------------------+
// | Admin menu loading                                                    |
// +-----------------------------------------------------------------------+

set_plugin_data($plugin['id'], $ld_config);

// +-----------------------------------------------------------------------+
// | functions                                                             |
// +-----------------------------------------------------------------------+

/**
 * Create random password
 * 
 *  Example:
 * 
 *       ")DqEfMGik=,ut@h!*jF+r2Y9XNlKLQ$V"   
 *
 * @since ~13.6
 *
 * @param int $length
 * @return string
 */
function random_password( $length = 32, $limited=false ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    if($limited == true){
        $chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}


/**
 * Piggyback function after initialising Piwigo 'init'
 * 		Loads languages
 *
 * 
 * @since ~ 1.2
 * @return void
 *
 */
function ld_init(){
    global $conf,$template;
    load_language('plugin.lang', LDAP_LOGIN_PATH);
    
    include_once(LDAP_LOGIN_PATH.'/class.config.php');
    include_once(LDAP_LOGIN_PATH.'/functions_sql.inc.php');
    
    // ErrorLogHandler: Logs records to PHP’s error_log() function.
    // StreamHandler: Logs records into any PHP stream, use this for log files.
    global $ld_config,$ld_log;
    $ld_log = new Logger('Ldap_Login');
    $ld_log->pushHandler(new ErrorLogHandler()); //To php_error.log | NOTICE: PHP message: [2023-05-31T19:39:38.832666+00:00] Ldap_Login.DEBUG
    $ld_log->pushHandler(new BrowserConsoleHandler()); //to Browser Console 
    $ld_log->pushHandler(new StreamHandler(LDAP_LOGIN_PATH . '/logs/ldap_login.log')); //To local file
    $ld_config = new Config();
    $ld_config->loadConfig();	
    
    $template->clear_assign('U_REGISTER'); // disable self-registration of users while using this plugin
    if($ld_config->getValue('ld_auth_type')=="ld_auth_azure"){	
        include_once(LDAP_LOGIN_PATH.'/class.ldap.php');
    }
    

    if (is_a_guest()){
        // only when not logged in it will replace the 'login'  link , else it wil break identification menu
        add_event_handler('blockmanager_apply', 'ld_azure');
    }
    if(!pwg_get_session_var('oauth2_state')){
        $stateValue= random_password(64,$limited=true);
        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Set state in session: $stateValue");
        pwg_set_session_var('oauth2_state',$stateValue );
    };


}


/**
 * Piggyback function after initialising menu and blocks 'blockmanager_apply'
 * 		Loads alternative link for 'forgot password' via Smarty Template
 *
 *
 * @since ~2.2
 * @return void
 *
 */
function ld_forgot(){
    global $template,$ld_config;
    $forgoturl=$ld_config->getValue('ld_forgot_url');
    if(!($forgoturl=="")){
        $template->assign('U_LOST_PASSWORD',$forgoturl);
    } else {
        $template->clear_assign('U_LOST_PASSWORD');
    }
}

/**
 * Piggyback function after initialising page and blocks 'loc_begin_identification'
 *
 *
 * @since ~13.7
 * @return void
 *
 */
function ld_redirect_identification(){
    global $ld_config;
    if($ld_config->getValue('ld_auth_type')=="ld_auth_azure"){
        redirect('index.php');
    }
}


/**
 * Piggyback function after initialising menu and blocks 'blockmanager_apply'
 * 		Loads alternative link for 'login' via Smarty Template
 *
 *
 * @since ~13.7
 * @return void
 *
 */
function ld_azure(){
    global $template,$ld_config,$ld_log;
    if($ld_config->getValue('ld_auth_type')=="ld_auth_azure"){

        include_once(LDAP_LOGIN_PATH.'/azure/callback.php');
        $state = pwg_get_session_var('oauth2_state');
        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Get state from session: $state");
        $auth_base = str_replace("{TENANT_ID}",$ld_config->getValue('ld_azure_tenant_id'),$ld_config->getValue('ld_azure_auth_url'));
        $form_params = array(
            'response_type' => 'code',
            'client_id' => $ld_config->getValue('ld_azure_client_id'),
            'redirect_uri' => $ld_config->getValue('ld_azure_redirect_uri'),
            'scope' => $ld_config->getValue('ld_azure_scopes'),
            'prompt' =>'select_account',
            'state' => $state
        );
        $oAuthURL = $auth_base . '?' .http_build_query($form_params);
        $template->assign('U_LOGIN',$oAuthURL);
    }
}


/**
 * Piggyback function for logging in 'try_log_user'
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
 * @since ~1.2
 *
 * @param boolean $success
 * @param string $username 
 * @param string $password
 * @param boolean $remember_me
 * @return boolean
 */
function login($success, $username, $password, $remember_me){
    //force users to lowercase name, or else duplicates will be made, like user,User,uSer etc.
    $username=strtolower($username);
    global $conf;
    if(strlen(trim($username)) == 0 || strlen(trim($password)) == 0){
            trigger_notify('login_failure', stripslashes($username));
            return false; // wrong user/password or no group access
    }
    global $ld_log,$ld_config;
    
    if($ld_config->getValue('ld_auth_type')=="ld_auth_ldap"){
        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> New login session: ld_auth_ldap");
        $ld_use_ssl=$ld_config->getValue('ld_use_ssl');
        $ldap_host=$ld_config->getValue('ld_host');
        $base_dn=$ld_config->getValue('ld_basedn');
        $port=$ld_config->getValue('ld_port');
        $binddn=$ld_config->getValue('ld_binddn');
        $bindpw=$ld_config->getValue('ld_bindpw');
        $user_filter = '(&(&(objectClass='.$ld_config->getValue('ld_user_class').')('.$ld_config->getValue('ld_user_attr').'=%username%))('.$ld_config->getValue('ld_user_filter',True).'))';
        
        $host = "ldap" . ($ld_use_ssl==1 ? "s" : "") . "://". $ldap_host;
        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> new Ldap($host,$port,$base_dn,$binddn,bindpw,$user_filter,array('dn')");
        $ld_ldap = new Ldap($host,$port,$base_dn,$binddn,$bindpw,$user_filter,array('dn'));
        if($ld_ldap == False){
            return False;
        }
        $user_dn = $ld_ldap->getUserDn($username);	// retrieve the userdn
        if(!($user_dn && $ld_ldap->authenticate($username,$password))){
        // If we have userdn, attempt to login an check user's group access via LDAP
            if(!($ld_ldap->isUserMemberOfGroup($user_dn, $ld_config->getValue('ld_group_user')))) {
                trigger_notify('login_failure', stripslashes($username));
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> wrong u/p or no group access");
                return False; // wrong user/password or no group access
            }
        }

        // search user in piwigo database
        $query = 'SELECT '.$conf['user_fields']['id'].' AS id FROM '.USERS_TABLE.' WHERE '.$conf['user_fields']['username'].' = \''.pwg_db_real_escape_string($username).'\' ;';
        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ". $query);
        $row = pwg_db_fetch_assoc(pwg_query($query));

        // if query is not empty, it means everything is ok and we can continue, auth is done !
        if (!empty($row['id'])) {
            $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ". $row['id']);
            if ($ld_config->getValue('ld_group_webmaster_active') || $ld_config->getValue('ld_group_admin_active')) {
                //check admin status
                $uid = pwg_db_real_escape_string($row['id']);
                $group_query = 'SELECT user_id, status FROM piwigo_user_infos  WHERE `piwigo_user_infos`.`user_id` = ' . $uid . ';';
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ". $group_query);
                $pwg_status = pwg_db_fetch_assoc(pwg_query($group_query))['status']; //current status according to Piwigo
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> info: $username, Current status:$pwg_status");
                
                //enable upgrade / downgrade from administrator
                if (($ld_config->getValue('ld_group_admin_active')==True) && ($ld_ldap->isUserMemberOfGroup($user_dn, $ld_config->getValue('ld_group_admin')))) {
                    //is user admin?
                    $status = 'admin'; //according to LDAP
                }
                //enable upgrade / downgrade from webmaster
                if (($ld_config->getValue('ld_group_webmaster_active')==True) && ($ld_ldap->isUserMemberOfGroup($user_dn, $ld_config->getValue('ld_group_webmaster')))) {
                    //is user webmaster?
                    $status = 'webmaster'; //according to LDAP
                }
                if(($ld_config->getValue('ld_group_webmaster_active')==True) && ($ld_config->getValue('ld_group_admin_active')==True) && $status == Null){
                    // functionality enabled but user not admin or webmaster. 
                    $status = "normal";
                }
                
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Admin_active:" . $ld_config->getValue('ld_group_admin_active') ." WebmasterActive:" . $ld_config->getValue('ld_group_webmaster_active') . "Current: $status");
                if (is_null($status)) {}//user is not a webmaster / admin or functionality disabled
                
                elseif($status == "admin") {
                    if($pwg_status=='webmaster') {$status='admin';}//ignore & keep webmaster
                    elseif($pwg_status=='admin') {$status='admin';} // admin
                    elseif($pwg_status=='normal') {$status='admin';} // admin
                }
                elseif($status == "webmaster"){
                    if($pwg_status=='webmaster') {$status='webmaster';}//ignore & keep webmaster
                    elseif($pwg_status=='admin') {$status='webmaster';}//ignore & keep webmaster
                    elseif($pwg_status=='normal') {$status='webmaster';} // normal
                }
                elseif($status == "normal"){} // always downgrade to normal if status was set
                
                if(isset($status)){
                    $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Target status $status");
                    if ($status!=$pwg_status) {
                        $query = '
                            UPDATE `piwigo_user_infos` SET `status` = "'. $status . '" WHERE `piwigo_user_infos`.`user_id` = ' . $uid . ';';
                        pwg_query($query);
                        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Changed $username with id " . $row['id'] . " from ".$pwg_status. " to " . $status);
                        include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
                        invalidate_user_cache();
                    }
                }
            }
            
            log_user($row['id'], $remember_me);
            trigger_notify('login_success', stripslashes($username));
            $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> User " . $username . " found in SQL DB and login success");
            return true;
        }
        
        // if query is empty but ldap auth is done we can create a piwigo user if it's said so !
        else {
            $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> User found in LDAP but not in SQL");
            // this is where we check we are allowed to create new users upon that.
            if ($ld_config->getValue('ld_allow_newusers')) {
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Creating new user and store in SQL");
                $mail=null;
                if($ld_config->getValue('ld_use_mail')){
                    // retrieve LDAP e-mail address and create a new user
                    $mail = array_shift($ld_ldap->getAttribute($user_dn,array('mail')));
                }
                $errors=[];
                $new_id = register_user($username,random_password(),$mail,true,$errors);
                if(count($errors) > 0) {
                    foreach ($errors as &$e){
                        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ".$e, 'ERROR');
                    }
                    return false;
                }
                // Login user
                log_user($new_id, False);
                trigger_notify('login_success', stripslashes($username));

                // in case the e-mail address is empty, redirect to profile page
                if ($ld_config->getValue('ld_allow_profile')) {
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
                $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Not allowed to create user (ld_allow_newusers=false)");
                return false;
            }
        }
    }
    if($ld_config->getValue('ld_auth_type')=="ld_auth_azure"){
        $ld_log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> New login session: ld_auth_azure");
    }
    unset($ld_config,$ld_log);
}

/**
 * Piggyback function for profile page 'load_profile_in_template'
 * Removes email/password 'block'
 * 	
 *
 *
 * @since 2.10.1
 *
 */
function ld_profile(){

    //removes the Profile/Registration block for new users.
    global $template;
    global $userdata;
    if($userdata['id']>2){
        $template->assign('SPECIAL_USER', True);
    }
}


/**
 * Piggyback function for admin menu links 'get_admin_plugin_menu_links'
 * 	
 *
 *
 * @since 2.10.1
 *
 */
function ldap_admin_menu($menu)
{
    array_push($menu,
    array(
    'NAME' => str_replace("_"," ",LDAP_LOGIN_ID),
    'URL' => get_admin_plugin_menu_link(LDAP_LOGIN_PATH . 'admin.php') )
    );
    return $menu;
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
