class LdapLoginTest extends \PHPUnit\Framework\TestCase {
    private $ldap;

    protected function setUp(): void {
        $host = 'ldap://ldap.example.com';
        $port = 389;
        $version = 3;

        $this->ldap = new Ldap($host, $port, $version);
    }

    protected function tearDown(): void {
        $this->ldap = null;
    }

    public function testConnect() {
        $this->assertTrue($this->ldap->connect());
    }

    public function testBind() {
        $username = 'testuser';
        $password = 'testpass';

        $this->assertTrue($this->ldap->bind($username, $password));
    }

    public function testSearch() {
        $baseDn = 'ou=people,dc=example,dc=com';
        $filter = '(uid=testuser)';
        $attributes = ['uid', 'cn'];

        $entries = $this->ldap->search($baseDn, $filter, $attributes);

        $this->assertIsArray($entries);
        $this->assertCount(1, $entries);
        $this->assertArrayHasKey('uid', $entries[0]);
        $this->assertArrayHasKey('cn', $entries[0]));
    }

    public function testGetEntry() {
        $dn = 'uid=testuser,ou=people,dc=example,dc=com';
        $attributes = ['uid', 'cn'];

        $entry = $this->ldap->getEntry($dn, $attributes);

        $this->assertIsArray($entry);
        $this->assertArrayHasKey('uid', $entry);
        $this->assertArrayHasKey('cn', $entry));
    }

    public function testAuthenticate() {
        $username = 'testuser';
        $password = 'testpass';

        $this->assertTrue($this->ldap->authenticate($username, $password));
    }
}
