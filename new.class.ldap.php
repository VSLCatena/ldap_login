<?php

class Ldap
{
    private $connection;

    private $baseDn;

    private $bindDn;

    private $bindPassword;

    private $userFilter;

    private $attributes;

    public function __construct($host, $port, $baseDn, $bindDn, $bindPassword, $userFilter, $attributes)
    {
        $this->connection   = ldap_connect($host, $port) or exit('Could not connect to LDAP server.');
        $this->baseDn       = $baseDn;
        $this->bindDn       = $bindDn;
        $this->bindPassword = $bindPassword;
        $this->userFilter   = $userFilter;
        $this->attributes   = $attributes;

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

        if (! ldap_bind($this->connection, $this->bindDn, $this->bindPassword)) {
            exit('Could not bind to LDAP server.');
        }
    }

    public function authenticate($username, $password)
    {
        $userDn = $this->getUserDn($username);

        if (! $userDn) {
            return false;
        }

        return (bool) ldap_bind($this->connection, $userDn, $password);
    }

    public function getUserDn($username)
    {
        $searchResult = ldap_search($this->connection, $this->baseDn, $this->getUserFilter($username), $this->attributes);
        $entries      = ldap_get_entries($this->connection, $searchResult);

        if ($entries['count'] !== 1) {
            return false;
        }

        return $entries[0]['dn'];
    }

    public function getUserFilter($username)
    {
        return str_replace('%username%', $username, $this->userFilter);
    }

    public function __destruct()
    {
        ldap_close($this->connection);
    }
}
