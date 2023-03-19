<?php
defined('LDAP_LOGIN_PATH') or die('Hacking attempt!');

global $template, $page, $conf;


// get current tab
$page['tab'] = (isset($_GET['tab'])) ? $_GET['tab'] : $page['tab'] = 'configuration';

// tabsheet
include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('ldap_login');

$tabsheet->add('configuration', l10n('Configuration'), LDAP_LOGIN_ADMIN . '-configuration');
$tabsheet->add('newusers', l10n('New user management'), LDAP_LOGIN_ADMIN . '-newusers');
$tabsheet->add('group_management', l10n('Test Login'), LDAP_LOGIN_ADMIN . '-test'); 
$tabsheet->add('support', l10n('Support'), LDAP_LOGIN_ADMIN . '-support'); 
$tabsheet->select($page['tab']);
$tabsheet->assign();
  
// include page

include(LDAP_LOGIN_PATH . 'admin/' . $page['tab'] . '.php');

// template vars
$template->assign('LDAP_LOGIN_PATH', get_root_url() . LDAP_LOGIN_PATH );
  
?>