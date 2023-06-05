<?php

class Ldap
{
    private $log;
    private $config;
    private $connection;
    private $baseDn;
    private $bindDn;
    private $bindPassword;
    private $userFilter;
    private $attributes;

    
    public function __construct($host, $port, $baseDn, $bindDn, $bindPassword, $userFilter, $attributes)
    {    
        global $ld_config,$ld_log;
        $this->config = $ld_config;
        $this->log= $ld_log;
        
        if(!extension_loaded('ldap')){
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> LDAP extension not loaded, see php_ldap module.");
        }
        
        $this->connection = @ldap_connect($host, $port) or throw new Exception();
        if(!$this->connection)
        {
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> Could not connect to LDAP server.");
        }
        $this->baseDn = $baseDn;
        $this->bindDn = $bindDn;
        $this->bindPassword = $bindPassword;
        $this->userFilter = $userFilter;
        $this->attributes = $attributes;

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
        
        $this->bind();
    }

    public function __destruct()
    {
        ldap_close($this->connection);
    }

    private function bind()
    {
        if (!ldap_bind($this->connection, $this->bindDn, $this->bindPassword)) {
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> Could not bind to LDAP server.");
            return false;
        }
    }
    
    
    public function authenticate($username, $password)
    {
        $userDn = $this->getUserDn($username);

        if (!$userDn) {
            return false;
        }

        if (ldap_bind($this->connection, $userDn, $password)) {
            return true;
        }

        return false;
    }

    public function getUserDn($username)
    {

        $searchResult = ldap_search($this->connection, $this->baseDn, $this->getUserFilter($username), $this->attributes);

        if($searchResult == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ".ldap_err2str(ldap_errno($this->connection)));
            return false;
        }
        $entries = ldap_get_entries($this->connection, $searchResult);
        if ($entries['count'] !== 1) {
            return false;
        }

        return $entries[0]['dn'];
    }

    public function getUserFilter($username)
    {
        return str_replace('%username%', $username, $this->userFilter);
    }
    
    public function isUserMemberOfGroup($user_dn, $group_dn) {
        $groupInfo = @ldap_read($this->connection, $group_dn, "(member=$user_dn)");
        if($groupInfo == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ".ldap_err2str(ldap_errno($this->connection)));
            return false;
        }
        $entries = ldap_get_entries($this->connection, $groupInfo);

        return $entries['count'] > 0;
    }

    public function getAttribute($user_dn,array $attr){
		$searchResult=@ldap_read($this->connection, $user_dn, "(objectclass=*)",$attr);
        if($searchResult == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ".ldap_err2str(ldap_errno($this->connection)));
            return false;
        }
		$entries = @ldap_get_entries($this->connection, $searchResult);
        if ($entries['count'] !== 1) {
            return false;
        }
        return $entries[0];
	}
    
}
?>
