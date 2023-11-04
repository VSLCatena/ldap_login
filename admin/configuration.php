<?php

if (! defined('PHPWG_ROOT_PATH')) {
    exit('Hacking attempt!');
}

global $template, $ld_config, $ld_log;
$template->set_filenames(['plugin_admin_content' => dirname(__FILE__) . '/configuration.tpl']);
$template->assign(
    [
        'PLUGIN_ACTION' => get_root_url() . 'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-configuration',
        'PLUGIN_CHECK'  => get_root_url() . 'admin.php?page=plugin-' . LDAP_LOGIN_ID . '-configuration',
    ]
);
$ld_config->loadConfig();

// ##
// ## POST (submit/load page)
// ##

// Save LDAP configuration when submitted
if (isset($_POST['save']) or isset($_POST['savetest'])) {
    $special = [
        'LD_AZURE_CLIENT_SECRET',
        'LD_BINDPW',
        'LD_ANONBIND',
        'LD_MEMBERSHIP_USER',
    ];
    $POSTVALUES = array_diff_key($_POST, array_flip($special));

    foreach ($POSTVALUES as $key => $value) {
        if (isset($_POST[strtoupper($key)])) {
            $ld_config->setValue(strtolower($key), $_POST[strtoupper($key)]);
        }
    }

    if (isset($_POST['LD_AZURE_CLIENT_SECRET'])) {
        if ($_POST['LD_AZURE_CLIENT_SECRET'] != '************************') {
            $ld_config->setValue('ld_azure_client_secret', $_POST['LD_AZURE_CLIENT_SECRET']);
        }
        // do nothing.
    }

    if ($_POST['LD_BINDPW'] != '************************') {
        $ld_config->setValue('ld_bindpw', $_POST['LD_BINDPW']);
    }
    // do nothing.

    // $ld_config->setValue('ld_bindpw',ldap_escape($_POST['LD_BINDPW'], '', LDAP_ESCAPE_DN);

    if (strlen($_POST['LD_BINDDN']) < 1 && strlen($_POST['LD_BINDPW']) < 1) {
        $ld_config->setValue('ld_anonbind', 1);
    }
    else {
        $ld_config->setValue('ld_anonbind', 0);
    }
    $ld_config->saveConfig();
}

// Checking LDAP configuration

if (isset($_POST['check_ldap']) or isset($_POST['savetest'])) {
    $ld_config->ldap_conn();
    $ld_log->debug('[function]> Ldap_Login Test');

    if ($ld_config->getValue('ld_anonbind') == 0) {
        $p_username = isset($_POST['savetest']) ? ldap_explode_dn($_POST['LD_BINDDN'], 1)[0] : $_POST['USERNAME'];
        $p_password = isset($_POST['savetest']) ? $_POST['LD_BINDPW'] : $_POST['PASSWORD'];

        $username = $ld_config->ldap_search_dn($p_username);
        $error    = $ld_config->check_ldap();

        if ($error == 1 && $username) { // need to clean this part..
            if ($ld_config->ldap_bind_as($username, $p_password)) {
                if ($ld_config->check_ldap_group_membership($username, $p_username)) {
                    $template->assign('LD_CHECK_LDAP', '<p style="color:green;">Configuration LDAP OK : ' . $username . '</p>');
                }
                else {
                    $template->assign('LD_CHECK_LDAP', '<p style="color:orange;">Credentials OK, Check GroupMembership for: ' . $username . '</p>');
                }
            }
            else {
                $template->assign('LD_CHECK_LDAP', '<p style="color:red;"> Binding OK, but check credentials on server ' . $ld_config->getValue('uri') . ' for user ' . $username . '</p>');
            }
        }
        elseif ($error == 1 && ! $username) {
            $template->assign('LD_CHECK_LDAP', '<p style="color:red;">Error : Binding OK, but no valid DN found on server ' . $ld_config->getValue('uri') . ' for user ' . $p_username . '</p>');
        }
        elseif ($error && $username) {
            $template->assign('LD_CHECK_LDAP', '<p style="color:red;">Error : Binding OK, but check credentials on ' . $ld_config->getValue('uri') . ' for user ' . $username . '</p>');
        }
        else {
            $template->assign('LD_CHECK_LDAP', '<p style="color:red;">Error : ' . $error . ' for binding on server ' . $ld_config->getValue('uri') . ' for user ' . $p_username . ', check your binding!</p>');
        }
    }

    if ($ld_config->getValue('ld_anonbind') == 1) {
        // anonymous binding
        $error    = $ld_config->check_ldap();
        $username = $ld_config->ldap_search_dn($_POST['USERNAME']);

        if ($error == 1) {
            if (! $username) {
                $template->assign('LD_CHECK_LDAP', '<p style="color:green;">Configuration LDAP OK, user not found </p>');
            }
            else {
                $template->assign('LD_CHECK_LDAP', '<p style="color:green;">Configuration LDAP OK, user: ' . ldap_explode_dn($username, 1)[0] . ' </p>');
            }
        }
        else {
            $template->assign('LD_CHECK_LDAP', '<p style="color:red;">Error : ' . $error . ' for binding on server, check your config!</p>');
        }
    }
}

// Fill template

// Automatic values
$templateKeys = array_keys($ld_config->getAllValues($default = true));
$special      = [
    'LD_AZURE_CLIENT_SECRET',
    'LD_BINDPW',
];
$templateKeys = array_diff_key($templateKeys, array_flip($special));

foreach ($templateKeys as $tkey) {
    $template->assign(strtoupper($tkey), $ld_config->getValue(strtolower($tkey)));
}
// Manual values
$template->assign('LDAP_LOGIN_PATH', LDAP_LOGIN_PATH);

if ($ld_config->getValue('ld_azure_client_secret') == '') {
    // only if empty then give back empty
    $template->assign('LD_AZURE_CLIENT_SECRET', $ld_config->getValue('ld_azure_client_secret'));
}
else {
    $template->assign('LD_AZURE_CLIENT_SECRET', '************************');
}

if ($ld_config->getValue('ld_bindpw') == '') {
    // only if empty then give back empty
    $template->assign('LD_BINDPW', $ld_config->getValue('ld_bindpw'));
}
else {
    $template->assign('LD_BINDPW', '************************');
}

// if (is_array($ld_config->warn_msg) && sizeof($ld_config->warn_msg)>0){
// 	if($ld_config->getValue('ld_debug_level') == "debug"){
// 		$keys=":<br>- " . implode("<br>- ", array_keys($ld_config->warn_msg));
// 	}
// 	$ld_config->warn_msg['general']='Warning: (some) default values are loaded. Please edit and save your configuration' . $keys;
// 	foreach ($ld_config->warn_msg as $key=>$value) {
// 		$template->assign('WARN_' . strtoupper($key),$value);
// 	}
// }
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
