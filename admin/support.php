<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template;
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/support.tpl') );
$template->assign(
  array(
    'PLUGIN_NEWUSERS' => get_root_url().'admin.php?page=plugin-Ldap_Login-support',
    ));


$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');
?>