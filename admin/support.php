<?php

if (! defined('PHPWG_ROOT_PATH')) {
    exit('Hacking attempt!');
}

global $template;
$template->set_filenames(['plugin_admin_content' => dirname(__FILE__) . '/support.tpl']);
$template->assign(
    [
        'PLUGIN_NEWUSERS' => get_root_url() . 'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-support',
    ]
);

$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
