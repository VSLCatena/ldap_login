<?php

declare(strict_types=1);

class Config
{
    private $log;

    private $config;

    private $warn_msg;

    private $default_val = [
        'ld_forgot_url'                   => 'password.php',
        'ld_debug_location'               => './logs/',
        'ld_debug_file'                   => 1,
        'ld_debug_clearupdate'            => 1,
        'ld_debug_level'                  => 'debug',
        'ld_debug_php'                    => 1,
        'ld_host'                         => 'localhost',
        'ld_port'                         => '389',
        'ld_basedn'                       => 'ou=domain,dc=domain,dc=tld',
        'ld_user_class'                   => 'person',
        'ld_user_attr'                    => 'samaccountName',
        'ld_user_filter'                  => null,
        'ld_group_class'                  => 'group',
        'ld_group_filter'                 => null,
        'ld_group_attr'                   => 'name',
        'ld_group_desc'                   => 'description',
        'ld_group_basedn'                 => 'cn=groups,ou=domain,dc=domain,dc=tld',
        'ld_group_member_attr'            => 'member',
        'ld_user_member_attr'             => 'memberOf',
        'ld_user_mail_attr'               => 'mail',
        'ld_group_webmaster'              => 'cn=piwigo_webmasters,cn=groups,ou=domain,dc=domain,dc=tld',
        'ld_group_admin'                  => 'cn=piwigo_admins,cn=groups,ou=domain,dc=domain,dc=tld',
        'ld_group_user'                   => 'cn=piwigo_users,cn=groups,ou=domain,dc=domain,dc=tld',
        'ld_binddn'                       => 'cn=service_account, ou=users, ou=domain, dc=domain,dc=tld',
        'ld_bindpw'                       => null,
        'ld_anonbind'                     => 0,
        'ld_use_ssl'                      => 0,
        'ld_membership_user'              => 0,
        'ld_group_user_active'            => 1,
        'ld_group_admin_active'           => 0,
        'ld_group_webmaster_active'       => 0,
        'ld_sync_data'                    => null,
        'ld_auth_type'                    => 'ld_auth_ldap',
        'ld_azure_auth_url'               => null,
        'ld_azure_scopes'                 => null,
        'ld_azure_token_url'              => null,
        'ld_azure_resource_url'           => null,
        'ld_azure_logout_url'             => null,
        'ld_azure_user_identifier'        => null,
        'ld_azure_client_id'              => null,
        'ld_azure_client_secret'          => null,
        'ld_azure_jwks_url'               => null,
        'ld_azure_tenant_id'              => null,
        'ld_azure_redirect_uri'           => null,
        'ld_azure_claim_name'             => 'roles',
        'ld_allow_newusers'               => 1,
        'ld_use_mail'                     => 1,
        'ld_allow_profile'                => 1,
        'ld_advertise_admin_new_ldapuser' => 0,
        'ld_send_password_by_mail_ldap'   => 0,
    ];

    public function __construct()
    {
        global $ld_log;
        $this->log = $ld_log;
        $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> initialized Config Class');
    }

    /**
     * Retrieves value from Config.
     *
     * @param  string  $var_name
     * @param  bool  $ignore_default
     */
    public function getValue($var_name, $ignore_default = false)
    {
        return $this->checkConfig($var_name, $ignore_default);
    }

    public function setValue($var_name, $var_value)
    {
        return $this->config[$var_name] = $var_value;
    }

    /**
     * Retrieves full config in Array.
     *
     * @param bool
     */
    public function getAllValues($default = false)
    {
        if (! $default) {
            return $this->config;
        }

        return $this->default_val;
    }

    /**
     * loads default config item if no config was found.
     *
     * @param  string  $var_name
     * @param  bool  $ignore_default
     */
    private function checkConfig($var_name = null, $ignore_default = false)
    {
        $var_u = strtoupper($var_name);
        $var_l = strtolower($var_name);

        if (! (isset($this->config[$var_l]))) { // is var set in loaded config
            if ($ignore_default) {
                return; // return empty
            }

            // $this->warn_msg[$var_u]="Default loaded"; //give red warning
            return $this->default_val[$var_l]; // return default value
        }

        return $this->config[$var_l];
    }

    /**
     * Loads default config.
     *
     * @return void
     */
    public function loadDefaultConfig()
    {
        foreach ($this->default_val as $key => $value) {
            $this->config[$key] = $value;
        }
        $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> load_default_config');
    }

    /**
     * Loads Config from DB.
     *
     * @param  bool  $merge
     *
     * @return void
     */
    public function loadConfig($merge = false)
    {
        if (! $merge) {
            $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Getting data from SQL table');
            $this->config = ld_sql('get'); // get x keys
        }
        else {
            // New default config contains n keys.  Old personal config x keys.
            $data = ld_sql('get'); // old config (x keys)

            foreach ($data as $key => $value) { // looping over x keys and replace n key with x key. New config will contain user x keys and a few new n keys
                $this->config[$key] = $value;     // setting value
            }
        }
    }

    /**
     * Load Old Config from file.
     *
     * @return void
     */
    public function loadOldConfig()
    {
        if (file_exists(LDAP_LOGIN_PATH . '/config/data.dat')) {
            // first we load the base config
            $conf_file = @file_get_contents(LDAP_LOGIN_PATH . '/config/data.dat');

            if ($conf_file !== false) {
                $this->config = unserialize($conf_file);
                $this->log->info('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Getting data from ./config/data.dat');
            }
        }
    }

    /**
     * Save Config to DB.
     *
     * @return void
     */
    public function saveConfig()
    {
        $this->log->info('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Saving values in SQL table');
        ld_sql('update', 'update_value', $this->config);
    }

    /**
     * Export Config.
     *
     * @return void
     */
    public function exportConfig()
    {
        $file = fopen(LDAP_LOGIN_PATH . '/config/data.dat', 'w');
        fwrite($file, serialize($this->config));
        fclose($file);
        $this->log->info('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Saving values in config/data.dat');
    }
}
