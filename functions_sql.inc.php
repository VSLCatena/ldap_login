<?php

if (! defined('PHPWG_ROOT_PATH')) {
    exit('Hacking attempt!');
}

function ld_table_exist()
{
    /*
     * Checks table in SQL-database Piwigo for plugin ldap_login
     *
     *
     *    	 - Return an boolean for table existance
     *
     * @since 12.0
     *
     *
     * @return boolean
     */

    global $prefixeTable, $conf, $ld_log;

    $query = "SELECT count(*) as count FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '" . $conf['db_base'] . "') AND (TABLE_NAME = '" . $prefixeTable . "ldap_login_config')";
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query);

    try {
        $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Try query on database');
        $qresult = query2array($query);

        if ($qresult[0]['count'] == 0) {
            $result = false;
        }
        else {
            $result = true;
        }
    }
    catch (mysqli_sql_exception $mes) {
        $ld_log->error('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> mysqli_sql_exception caught.');
        $ld_log->error('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $mes);
        $result = false;
    }
    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . ($result ? 'true' : 'false'));

    return $result;
}

function ld_sql($action = 'get', $type = null, $data = null)
{
    /*
         * Does actions on SQL-database Piwigo for plugin ldap_login
         * Depending on $action and $type it can do for:
         *
         *		ld_sql($action='get')
         *    	 - Return an associative array of a table values (key='...', value='...')
         *
         *		ld_sql($action='create',$type='create_table')
         *    	 - Creates table  . $prefixeTable . ldap_login_config
         *
         *		ld_sql($action='create',$type='create_data','data')
         *    	 - Inserts data (array(array($k,$v),...)) in table
         *
         *		ld_sql($action='update','reset_openldap')
         *    	 - Updates values corresponding with OpenLDAP values (classes and attributes)
         *
         *		ld_sql($action='update','reset_ad')
         *    	 - Updates values corresponding with AD values (classes and attributes)
         *
         *		ld_sql($action='update','update_value','data')
         *    	 - Updates data from array($k1=>$v1,$k2=>$v2,...) in table
         *
         *		ld_sql($action='delete')
         *    	 - Deletes $prefixeTable . ldap_login_config
         *
         *
         * @since 2.10.1
         *
         * @param string $action
         * @param string $type
         * @param array $data
         * @return array $result
         */

    global $prefixeTable,$ld_log;

    // ##
    // ## GET
    // ##

    if ($action === 'get') {
        if (ld_table_exist()) {
            $query = 'SELECT param,value FROM ' . $prefixeTable . 'ldap_login_config';
            $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query);
            $result = query2array($query, 'param', 'value');

            return $result;
        }
    }

    // ##
    // ## CREATE
    // ##	ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;
    if ($action == 'create') {
        if ($type == 'create_table') {
            $query = 'CREATE TABLE IF NOT EXISTS `' . $prefixeTable . 'ldap_login_config` (`param` varchar(40) CHARACTER SET utf8 NOT NULL,`value` text CHARACTER SET utf8,`comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,UNIQUE KEY `param` (`param`)) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;';
            $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query);
            pwg_query($query);
            $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query);
            $query = 'ALTER TABLE `' . $prefixeTable . 'ldap_login_config` ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `value`;';
            pwg_query($query);
        }

        if ($type == 'create_data') {
            if (isset($data)) {
                foreach ($data as $k => $v) {
                    $datas[] = [
                        'param' => $k,
                        'value' => pwg_db_real_escape_string($v),
                    ];
                }
                mass_inserts($prefixeTable . 'ldap_login_config', ['param', 'value'], $datas, ['ignore' => true]);
            }
        }
    }
    // ##
    // ## Update
    // ##

    // maybe try this in future, but piwigo functions dont support it (yet)
    // INSERT OR IGNORE INTO book(id) VALUES(1001);
    // UPDATE book SET name = 'Programming' WHERE id = 1001;

    if ($action == 'update') {
        if (ld_table_exist()) {
            if ($type == 'reset_openldap') {
                $updates = [
                    [
                        'param' => 'ld_user_attr',
                        'value' => 'cn',
                    ],
                    [
                        'param' => 'ld_user_class',
                        'value' => 'inetOrgPerson',
                    ],
                    [
                        'param' => 'ld_group_class',
                        'value' => 'groupOfNames',
                    ],
                ];
                mass_updates(
                    $prefixeTable . 'ldap_login_config',
                    [
                        'primary' => ['param'],
                        'update'  => ['value'],
                    ],
                    $updates
                );
            }

            if ($type == 'reset_ad') {
                $updates = [
                    [
                        'param' => 'ld_user_attr',
                        'value' => 'sAMAccountname',
                    ],
                    [
                        'param' => 'ld_user_class',
                        'value' => 'user',
                    ],
                    [
                        'param' => 'ld_group_class',
                        'value' => 'group',
                    ],
                ];
                mass_updates(
                    $prefixeTable . 'ldap_login_config',
                    [
                        'primary' => ['param'],
                        'update'  => ['value'],
                    ],
                    $updates
                );
            }

            if ($type == 'update_value') {
                if (isset($data)) {
                    foreach ($data as $k => $v) {
                        $updates[] = [
                            'param' => $k,
                            'value' => pwg_db_real_escape_string($v),
                        ];
                    }
                    mass_updates(
                        $prefixeTable . 'ldap_login_config',
                        [
                            'primary' => ['param'],
                            'update'  => ['value'],
                        ],
                        $updates
                    );
                }
            }

            if ($type == 'clear_mail_address') {
                $query = 'update piwigo_users SET mail_address = null WHERE id > 2';
                $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query);
                pwg_query($query);
            }

            if ($type == 'update_sql_structure') {
                $query1 = 'SHOW COLUMNS FROM  `' . $prefixeTable . "ldap_login_config` LIKE 'modified'; ";
                $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query1);
                $query2 = 'ALTER TABLE `' . $prefixeTable . 'ldap_login_config` ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `value`;';

                if (! pwg_query($query1)) {
                    $ld_log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $query2);
                    pwg_query($query2);
                }
            }
        }
    }

    // ##
    // ## DELETE
    // ##

    if ($action == 'delete') {
        if (ld_table_exist()) {
            if ($type == 'delete_table') {
                $query = '
				DROP TABLE `' . $prefixeTable . 'ldap_login_config`;
				';
                pwg_query($query);
            }
        }
    }
}
