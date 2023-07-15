<?php
define('PHPWG_ROOT_PATH','./');

define('LDAP_LOGIN_ID',  basename(dirname(dirname(__FILE__))));
define('LDAP_LOGIN_PATH' , PHPWG_ROOT_PATH .  'plugins/'  . LDAP_LOGIN_ID . '/');

require_once(LDAP_LOGIN_PATH . 'vendor/autoload.php');
include_once(LDAP_LOGIN_PATH . 'class.ldap.php');
include_once(LDAP_LOGIN_PATH . 'functions_sql.inc.php');

class LdapLoginTest extends \PHPUnit\Framework\TestCase {
    private $ldap;

    protected function setUp(): void {
        $host = 'ldap://ldap';  #dc=domain,dc=tld
        $port = 389;
        $version = 3;
        $baseDn = "dc=domain,dc=tld";
        $bindDn = "cn=testadmin,ou=admins,ou=domain,dc=domain,dc=tld";
        $bindPassword = "";
        $userFilter = "(&(objectClass='person')('cn'='%username%'))"; 
        $attributes = ['uid', 'cn','userPrincipalName'];
        
        

        $this->ldap = new Ldap($host, $port, $baseDn, $bindDn, $bindPassword, $userFilter, $attributes);
    }

    protected function tearDown(): void {
        $this->ldap = null;
    }

    public function testcheck_ldap() {
        $this->assertTrue($this->ldap->connect());
    }

    public function testldap_bind_as() {
        $username = 'testuser';
        $password = 'testpass';

        $this->assertTrue($this->ldap->bind($username, $password));
    }

    public function testSearch() {
        $baseDn = 'dc=domain,dc=tld';
        $filter = '(cn=testuser)';
        $attributes = ['uid', 'cn'];

        $entries = $this->ldap->search($baseDn, $filter, $attributes);

        $this->assertIsArray($entries);
        $this->assertCount(1, $entries);
        $this->assertArrayHasKey('uid', $entries[0]);
        $this->assertArrayHasKey('cn', $entries[0]);
    }

    public function testGetEntry() {
        $dn = 'uid=testuser,ou=people,dc=example,dc=com';
        $attributes = ['uid', 'cn'];

        $entry = $this->ldap->getEntry($dn, $attributes);

        $this->assertIsArray($entry);
        $this->assertArrayHasKey('uid', $entry);
        $this->assertArrayHasKey('cn', $entry);
    }

    public function testAuthenticate() {
        $username = 'testuser';
        $password = 'testpass';

        $this->assertTrue($this->ldap->authenticate($username, $password));
    }
}
?>
