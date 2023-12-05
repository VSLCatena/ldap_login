<?php

class Ldap
{
    private $config;
    private $log;
    
    private $host;
    private $port;
    private $baseDn;
    private $bindDn;
    private $bindPassword;
    
    
    private $attributes;
    private $userAttribute;
    private $groupMemberAttribute;
    private $groupObjectClass;
    private $userClass;
    private $userFilter;
    private $ldapGroups;
    private $resultObject;
    private $connection;
     
 
    public function __construct()
    {
        global $ld_config,$ld_log;
        $this->config = $ld_config;
        $this->log    = $ld_log;

        $this->host = $ld_config->getValue('ld_host');
        $this->port = $ld_config->getValue('ld_port');

        $this->baseDn       = $ld_config->getValue('ld_basedn');
        $this->bindDn       = $ld_config->getValue('ld_binddn');
        $this->bindPassword = $ld_config->getValue('ld_bindpw');

        $this->attributes           = ['cn', 'dn', 'email', 'samaccountname', 'memberOf', 'member'];
        $this->userAttribute        = $ld_config->getValue('ld_user_attr');
        $this->groupMemberAttribute = $ld_config->getValue('ld_group_member_attr');
        $this->groupObjectClass     = $ld_config->getValue('ld_group_class');
        $this->userClass            = $ld_config->getValue('ld_user_class');
        $this->userFilter           = $ld_config->getValue('ld_user_filter');
        $this->ldapGroups           = [];

        if ($ld_config->getValue('ld_group_user_active') == 1) {
            $this->ldapGroups = array_merge($this->ldapGroups, ['user' => $ld_config->getValue('ld_group_user')]);
        }

        if ($ld_config->getValue('ld_group_admin_active') == 1) {
            $this->ldapGroups = array_merge($this->ldapGroups, ['admin' => $ld_config->getValue('ld_group_admin')]);
        }

        if ($ld_config->getValue('ld_group_webmaster_active') == 1) {
            $this->ldapGroups = array_merge($this->ldapGroups, ['webmaster' => $ld_config->getValue('ld_group_webmaster')]);
        }

        $this->resultObject             = new stdClass();
        $this->resultObject->lastError  = new stdClass();
        $this->resultObject->userObject = new stdClass();

        if (! extension_loaded('ldap')) {
            $this->log->critical('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> LDAP extension not loaded, see php_ldap module.');
            $this->resultObject->extensionLoaded = false;
        }
        $this->resultObject->extensionLoaded = true;

        $ld_log->debug('New LDAP Instance');
        $this->connect();
    }

    public function getDebugData()
    {
        return $this->resultObject;
    }

    private function connect()
    {
        $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . "]> ldap_connect($this->host, $this->port)");
        $this->connection = @ldap_connect($this->host, $this->port) or throw new Exception();

        if (! $this->connection) {
            $this->log->critical('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Could not connect to LDAP server.');
            $this->resultObject->connectedToLdap = false;
        }
        else {
            $this->resultObject->connectedToLdap = true;
        }
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
    }

    private function bind()
    {
        $this->resultObject->anonymousBindSuccess = false;

        if (ldap_bind($this->connection) && $this->ldap_check_basedn()) {
            $this->resultObject->anonymousBindSuccess = true;
        }

        if (! ldap_bind($this->connection, $this->bindDn, $this->bindPassword)) {
            $this->log->critical('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Could not bind to LDAP server.');
            $this->resultObject->bindSuccess = false;

            return false;
        }
        $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Connected to LDAP server.');
        $this->resultObject->bindSuccess = true;

        return true;
    }

    public function authenticate($username, $password)
    {
        $this->bind();

        $userDn = $this->getUserDn($username);

        if (! $userDn) {
            $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . "]> User $username not found");
            $this->resultObject->userObject->credentialsCorrect = false;
            $this->resultObject->result                         = false;
        }
        elseif (@ldap_bind($this->connection, $userDn, $password)) {
            $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . "]> $username can login");
            $this->resultObject->userObject->credentialsCorrect = true;

            foreach ($this->ldapGroups as $k => $group) {
                $this->resultObject->userObject->{$k} = $this->isUserMemberOfGroup($userDn, $group);
            }
            $this->resultObject->result = true;
        }
        else {
            $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $this->getLdapError());
            $this->resultObject->userObject->credentialsCorrect = false;
            $this->resultObject->result                         = false;
        }

        return $this->resultObject->result;
    }

    public function getUserDn($username)
    {
        $userFilter   = $this->getUserFilter($username);
        $searchResult = ldap_search($this->connection, $this->baseDn, $userFilter, ['cn', 'dn']);
        $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ldap_search(connection, ' . $this->baseDn . ', ' . $userFilter . ", array('dn','cn'))");

        if ($searchResult == false) {
            $this->log->warning('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $username . ' : ' . $this->getLdapError());
            $this->resultObject->userObject->userFound = false;

            return false;
        }
        $entries = ldap_get_entries($this->connection, $searchResult);
        $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> Found:' . $entries['count']);

        if ($entries['count'] !== 1) {
            $this->resultObject->userObject->userFound = false;

            return false;
        }
        $this->resultObject->userObject->userFound = true;

        return $entries[0]['dn'];
    }

    public function getUserFilter($username)
    {
        $userFilter = empty($this->userFilter) ? 'cn=*' : $this->userFilter;
        $filter     = '(&(&(objectClass=' . $this->userClass . ')(' . $this->userAttribute . '=%username%))(' . $userFilter . '))';
        $this->log->debug('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . str_replace('%username%', $username, $filter));

        return str_replace('%username%', $username, $filter);
    }

    public function isUserMemberOfGroup($userDn, $groupDn)
    {
        $groupCn = ldap_explode_dn($groupDn, 1)[0];
        /*
                *         $search_filter = "(&(objectclass=$group_class)(cn=$group_cn)($member_attr=$userDn)($group_filter))";
                $search = ldap_search($this->connection, $base_dn, $search_filter,array($member_attr),0,0,5); //search for group
                if($search){
                    $entries = ldap_get_entries($this->connection,$search); //get group
                    if($entries['count']>0){
                        $memberEntries=$entries[0][strtolower($member_attr)];
                        for($i=0;$i<$memberEntries['count'];$i++){
                            $memberEntry_dn = $memberEntries[$i];
                            if($memberEntry_dn === $userDn){ // Match the user.
                                return true;
                            }
                            unset $memberEntry_dn;
                        }
                    }
                }
                */
        // openldap
        $filter    = "($this->groupMemberAttribute=$userDn)";
        $groupInfo = @ldap_read($this->connection, $groupDn, $filter, ['cn'], 0, 0, 5); // No Such Object

        if ($groupInfo == false) {
            $this->log->warning('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . "ldap_read(conn,$groupDn,$filter,array('cn'),0,0,5): " . $this->getLdapError());

            return false;
        }
        $entries = ldap_get_entries($this->connection, $groupInfo);

        return $entries['count'] > 0;
    }

    public function getAttribute($user_dn, array $attr)
    {
        $searchResult = @ldap_read($this->connection, $user_dn, '(objectclass=*)', $attr);

        if ($searchResult == false) {
            $this->log->warning('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $this->getLdapError());

            return false;
        }
        $entries = @ldap_get_entries($this->connection, $searchResult);

        if ($entries['count'] !== 1) {
            return false;
        }

        return $entries[0];
    }

    public function ldap_check_basedn()
    {
        if ($read = @ldap_read($this->connection, $this->baseDn, 'objectClass=*', ['cn'])) {
            $entry = @ldap_get_entries($this->connection, $read);

            if (! empty($entry[0]['dn'])) {
                return true;
            }
            $this->log->warning('[' . basename(__FILE__) . '/' . __FUNCTION__ . ':' . __LINE__ . ']> ' . $this->getLdapError());
        }

        return false;
    }

    public function getLdapError()
    {
        $err                                       = ldap_err2str(ldap_errno($this->connection));
        $this->resultObject->lastError->message    = $err;
        $this->resultObject->lastError->diagnostic = $this->getLdapDiagnostic();

        return $err;
    }

    public function getLdapDiagnostic()
    {
        if (ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
            return $extended_error;
        }
    }

    public function __destruct()
    {
        ldap_close($this->connection);
    }
}
