<?php

class Ldap
{
    public function __construct()
    {    
        global $ld_config,$ld_log;
        $this->config = $ld_config;
        $this->log= $ld_log;
        
        $this->host = $ld_config->getValue('ld_host');
        $this->port = $ld_config->getValue('ld_port');
        
        $this->baseDn = $ld_config->getValue('ld_basedn');
        $this->bindDn = $ld_config->getValue('ld_binddn');
        $this->bindPassword = $ld_config->getValue('ld_bindpw');
        
        $this->attributes =  array("cn","dn","email","samaccountname","memberOf","member"); 
        $this->userAttribute =  $ld_config->getValue('ld_user_attr');
        $this->userClass =  $ld_config->getValue('ld_user_class');
        $this->userFilter =  $ld_config->getValue('ld_user_filter');
        $this->resultObject = new stdClass();
        $this->resultObject->lastError="";
        
        if(!extension_loaded('ldap')){
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> LDAP extension not loaded, see php_ldap module.");
            $this->resultObject->extensionLoaded=False;
        }
        $this->resultObject->extensionLoaded=True;
        
        $ld_log->debug("New LDAP Instance");
        $this->connect();
        $this->bind();
    }
    
    private function connect()
    {
        $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ldap_connect($this->host, $this->port)");
        $this->connection = @ldap_connect($this->host, $this->port) or throw new Exception();
        if(!$this->connection)
        {
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> Could not connect to LDAP server.");
            $this->resultObject->connectedToLdap=False;
        } else {
            $this->resultObject->connectedToLdap=True;
        }
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);        
    }
    
    public function __destruct()
    {
        ldap_close($this->connection);
    }

    private function bind()
    {
        if (!ldap_bind($this->connection, $this->bindDn, $this->bindPassword)) {
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> Could not bind to LDAP server.");
            $this->resultObject->bindSuccess=False;
            return false;
        } else {
            $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Connected to LDAP server.");
            $this->resultObject->bindSuccess=True;
            return true;
        }
    }
    
    
    public function authenticate($username, $password,$test=False)
    {
        $userDn = $this->getUserDn($username);

        if (!$userDn) {
            $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> User $username not found");
            $this->resultObject->credentialsCorrect=false;
            $this->resultObject->result=false;
        }
        if (@ldap_bind($this->connection, $userDn, $password)) {
            $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> $username can login");
            $this->resultObject->credentialsCorrect=true;
            $this->resultObject->result=true;
        } else {
            $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ". $this->getLdapError() );
            $this->resultObject->credentialsCorrect=false;
            $this->resultObject->result=false;
        }
        if($test == True) {
            return $this->resultObject;
        } else{
            return $this->resultObject->result;
        }
    }

    public function getUserDn($username)
    {
        $userFilter = $this->getUserFilter($username);
        $searchResult = ldap_search($this->connection, $this->baseDn, $userFilter, array('dn','cn'));
        $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> ldap_search(connection, ". $this->baseDn .", " . $userFilter . ", array('dn','cn'))");

        if($searchResult == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> " . $username ." : " . getLdapError());
            $this->resultObject->userFound=False;
            return false;
        }
        $entries = ldap_get_entries($this->connection, $searchResult);
        $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> Found:" . ($entries['count']));
        if ($entries['count'] !== 1) {
            $this->resultObject->userFound=False;
            return false;
        }
        $this->resultObject->userFound=True;
        return $entries[0]['dn'];
    }

    public function getUserFilter($username)
    {
        $userFilter = empty($this->userFilter) ? "cn=*" : $this->userFilter;
        $filter = '(&(&(objectClass='. $this->userClass.')('.$this->userAttribute.'=%username%))('.$userFilter.'))';
        $this->log->debug("[".basename(__FILE__)."/".__FUNCTION__."]> " . str_replace('%username%', $username, $filter));
        return str_replace('%username%', $username, $filter);
    }
    
    public function isUserMemberOfGroup($user_dn, $group_dn) {
        $groupInfo = @ldap_read($this->connection, $group_dn, "(member=$user_dn)");
        if($groupInfo == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ". getLdapError() );
            return false;
        }
        $entries = ldap_get_entries($this->connection, $groupInfo);

        return $entries['count'] > 0;
    }

    public function getAttribute($user_dn,array $attr){
		$searchResult=@ldap_read($this->connection, $user_dn, "(objectclass=*)",$attr);
        if($searchResult == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ". getLdapError() );
            return false;
        }
		$entries = @ldap_get_entries($this->connection, $searchResult);
        if ($entries['count'] !== 1) {
            return false;
        }
        return $entries[0];
	}
    
    public function getLdapError(){
        $err = ldap_err2str(ldap_errno($this->connection));
        $this->resultObject->lastError=$err;
        return $err;
    }
}
?>
