<?php
global $conf;
class Ldap {
	var $cnx;
	var $config;

	// for debug, put @ before file_put_contents
	public function write_log($message){
		$log_path='/var/log/';
		$ts = date_format(date_create() ,DATE_ATOM);
		$full = $ts . ": " . ($message);
		if($this->config['ldap_debug']){
			file_put_contents($log_path . 'ldap_login.log',$full."\n",FILE_APPEND);
		}
	}

	/**
	 * check ldap configuration
	 *
	 * Dans le cas ou l'acces au ldap est anonyme il faut impérativement faire une recherche
	 * pour tester la connection.
	 *
	 * When OpenLDAP 2.x.x is used, ldap_connect() will always return a resource as it does not actually connect
	 * but just initializes the connecting parameters. The actual connect happens with the next calls
	 * to ldap_* funcs, usually with ldap_bind().
	 */
	public function check_ldap(){
		//$this->write_log("[function]> check_ldap");
		if (!$this->ldap_conn()) {
			return $this->getErrorString();
		}

		// test du compte root si renseigné
		if (!empty($this->config['ld_binddn']) && !empty($this->config['ld_bindpw'])){ // if empty ld_binddn, anonymous search
			// authentication with rootdn and rootpw for search
			if (!$this->ldap_bind_as($this->config['ld_binddn'],$this->config['ld_bindpw'])){
				return $this->getErrorString();
			}
		} else {
			// sinon recherche du basedn (cf comportement ldap_connect avec OpenLDAP)
			if (!$this->ldap_check_basedn()){ // search userdn
				return $this->getErrorString();
			}
		}
		return true;
	}

	public function load_default_config(){
		$this->config['forgot_url'] = 'password.php';
		$this->config['ldap_debug'] = False;
		$this->config['host'] = 'localhost';
		$this->config['basedn'] = 'ou=people,dc=example,dc=com'; // racine !
		$this->config['port'] = ''; // if port is empty, I count on the software to care of it !
		$this->config['ld_server'] = 'ad'; //active directory as default.
		$this->config['ld_attr'] = 'sAMAccountName';
		$this->config['ld_group'] = 'cn=myPiwigoLDAPGroup,cn=users,dc=example,dc=com';
		$this->config['ld_group_class']	 = 'group'; //group as default.
		$this->config['ld_group_member_attrib']	 = 'member'; //member as default.
		$this->config['ld_use_ssl'] = False;
		$this->config['ld_bindpw'] ='';
		$this->config['ld_binddn'] ='';

		$this->config['allow_newusers'] = False;
		$this->config['advertise_admin_new_ldapuser'] = False;
		$this->config['send_password_by_mail_ldap'] = False;
	}

	function load_config() {
		// first we load the base config
		$conf_file = @file_get_contents( LDAP_LOGIN_PATH.'data.dat' );
		if ($conf_file!==false)
		{
			$this->config = unserialize($conf_file);
		}
	}

	function save_config()
	{
		$file = fopen( LDAP_LOGIN_PATH.'/data.dat', 'w' );
		fwrite($file, serialize($this->config) );
		fclose( $file );
	}

	function ldap_admin_menu($menu)
	{
		array_push($menu,
		array(
		'NAME' => 'Ldap Login',
		'URL' => get_admin_plugin_menu_link(LDAP_LOGIN_PATH.'/admin.php') )
		);
		return $menu;
	}

	// LDAP connection public
	public function ldap_conn(){
		if( $this->cnx = $this->make_ldap_conn() ){
			return true;
		}
		return false;
	}

	// LDAP connection private
	private function make_ldap_conn(){
		if ($this->config['ld_use_ssl'] == 1){
			if (empty($this->config['port'])){
				$this->config['uri'] = 'ldaps://'.$this->config['host'];
			}
			else {
			$this->config['uri'] = 'ldaps://'.$this->config['host'].':'.$this->config['port'];
			}
		}

		// now, it's without ssl
		else {
			if (empty($this->config['port'])){
				$this->config['uri'] = 'ldap://'.$this->config['host'];
			}
			else {
				$this->config['uri'] = 'ldap://'.$this->config['host'].':'.$this->config['port'];
			}
		}
		if (!extension_loaded('ldap')) {
			print "LDAP extension not loaded<br>";
			return false;
		}
		if ($conn = @ldap_connect($this->config['uri'])){
			@ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3); // LDAPv3 if possible
			return $conn;
		}
		return false;
	}

	// return ldap error
	public function getErrorString(){
                return ldap_err2str(ldap_errno($this->cnx));
        }

	// authentication public
	public function ldap_bind_as($user,$user_passwd){
		$this->write_log("[function]> ldap_bind_as");
		$this->write_log("[ldap_bind_as]> ".$user);
		
		if($this->make_ldap_bind_as($this->cnx,$user,$user_passwd)){
			$this->write_log("[ldap_bind_as]> Bind was successfull");
			return true;
		}
		return false;
        }

	// authentication private
	private function make_ldap_bind_as($conn,$user,$user_passwd){
		$this->write_log("[function]> make_ldap_bind_as");
		$this->write_log("[make_ldap_bind_as]> \$conn,".$user);
		$bind = @ldap_bind($conn,$user,$user_passwd);
		if($bind ){
			return true;
		}
		return false;
	}

	public function ldap_get_email($user_dn){
		$sr=@ldap_read($this->cnx, $user_dn, "(objectclass=*)", array('mail'));
		$entry = @ldap_get_entries($this->cnx, $sr);

		if (!empty($entry[0]['mail'])) {
			return $entry[0]['mail'][0];
		}
		return null;
	}

	public function ldap_get_user_email($username) {
		return $this->ldap_email($this->ldap_get_dn($username));
	}

	// return userdn (and username) for authentication
	public function ldap_search_dn($value_to_search){
		$this->write_log("[function]> ldap_search_dn(".$value_to_search.")");
		$filter = '(&(objectClass=person)('.$this->config['ld_attr'].'='.$value_to_search.'))';

		// connection handling
		$this->write_log("[ldap_search_dn]> Connecting to server");
		//if(!$bcnx = $this->make_ldap_conn()){
		if(!$this->cnx){
			$this->write_log("[ldap_search_dn]> Cannot connect to server!");
			return false;
		}
		$this->write_log("[ldap_search_dn]> make_ldap_bind_as(\$this->cnx,".$this->config['ld_binddn'].",".$this->config['ld_bindpw'].")");
		//if(!$this->make_ldap_bind_as($bcnx,$this->config['ld_binddn'],$this->config['ld_bindpw'])){
		if(!$this->make_ldap_bind_as($this->cnx,$this->config['ld_binddn'],$this->config['ld_bindpw'])){
			$this->write_log("[ldap_search_dn]> Cannot bind to server!");
			return false;
		}

		$this->write_log("[ldap_search_dn]> @ldap_search(\$this->cnx,".$this->config['basedn'].",".$filter.",array('dn'),0,1)");

		// look for our attribute and get always the DN for login
		//if($search = ldap_search($bcnx,$this->config['basedn'],$filter,array('dn'),0,1)){
		if($search = @ldap_search($this->cnx,$this->config['basedn'],$filter,array('dn'),0,1)){
			$this->write_log("[ldap_search_dn]> ldap_search successfull");
			//$entry = ldap_get_entries($bcnx, $search);
			$entry = @ldap_get_entries($this->cnx, $search);
			//if (!empty($entry[0][strtolower($this->config['ld_attr'])][0])) {
			if (!empty($entry[0]["dn"])) {
				$this->write_log("[ldap_search_dn]> RESULT: ".$entry[0]["dn"]);
				//@ldap_unbind($bcnx);
				return $entry[0]["dn"];
			}
			$this->write_log("[ldap_search_dn]> result is empty!");
			return false;
		}
		$this->write_log("[ldap_search_dn]> ldap_search NOT successfull:");
		return false;
	}

	// look for LDAP group membership
	public function check_ldap_group_membership($user_dn, $user_login){
		$server_mode = $this->config['ld_server'];
		$base_dn = $this->config['basedn'];		
		$group_class = $this->config['ld_group_class'];		
		$group_dn = $this->config['ld_group'];
		$group_cn = preg_replace('/,[a-z]+.*/','',$group_dn);
		$member_attr = $this->config['ld_group_member_attrib'];
		$find_attr = $this->config['ld_attr'];
		$this->write_log("[function]> check_ldap_group_membership('$server_mode','$user_dn', '$group_dn', '$user_login')");
		//if no group specified return true
		if(!$group_dn){
			return true;
		}
		if(!$this->cnx){
			$this->write_log("[check_ldap_group_membership]> Cannot connect to server!");
                        return false;
                }
		if(!$this->make_ldap_bind_as($this->cnx,$this->config['ld_binddn'],$this->config['ld_bindpw'])){
                        $this->write_log("[check_ldap_group_membership]> Cannot bind to server!");
                        return false;
                }
		if($server_mode =="openldap") {

			
			$this->write_log("[check_ldap_group_membership]> OpenLDAP Mode");
			// Do a member search for the user (OpenLDAP)
			$search_filter = "(&(objectclass=$group_class)($group_cn))"; 
			$this->write_log("[check_ldap_group_membership]> @ldap_search(\$this->cnx,'$base_dn', '$search_filter','$member_attr'");
			$search = ldap_search($this->cnx, $base_dn, $search_filter,array($member_attr),0,0,5); //search for group
			if($search){
				$entries = ldap_get_entries($this->cnx,$search); //get group
				$this->write_log("[ldap_get_entries]>". serialize($entries));
				$memberEntries=$entries[0][strtolower($member_attr)];
				for($i=0;$i<$memberEntries['count'];$i++){
					$this->write_log("[check_ldap_group_membership]> Test ".$memberEntries[$i]." = ".$user_login."?");
					if($memberEntries[$i] == $user_login){ // Match the attribute provided from the user.
						$this->write_log("[check_ldap_group_membership]> $member_attr matches $user_login");
						return true;
					}
				}
				$this->write_log("[check_ldap_group_membership]> No matches found for $user_login.");
			} else {
				$this->write_log("[check_ldap_group_membership]> ldap_search NOT successful: " .$this->getErrorString());
			}
			
		}		
				
		if($server_mode =="ad" || $server_mode =="" ) {
			$this->write_log("[check_ldap_group_membership]> AD Mode");
			
			// search for all member and memberUid attributes for a group_dn
			$search_filter = "(|(&(objectClass=posixGroup)(memberUid=$user_login))(&(objectClass=group)(member=$user_dn)))";
			$this->write_log("[check_ldap_group_membership]> @ldap_search(\$this->cnx,'$group_dn', '$search_filter', array('memberOf'),0,1)");
			if($search = @ldap_search($this->cnx, $group_dn, $search_filter, array("dn"),0,1)){
				$entry = @ldap_get_entries($this->cnx, $search);
				//check if there are dn-attributes
				if (!empty($entry[0]["dn"])) {
					$this->write_log("[check_ldap_group_membership]> match found: ".$entry[0]["dn"]);
					return true;			
				} else {
					$this->write_log("[check_ldap_group_membership]> no group membership for user found for given group and user, check on ldap side");
				}
			} else {
				$this->write_log("[check_ldap_group_membership]> ldap_search NOT successfull: " .$this->getErrorString());
			}
			$this->write_log("[check_ldap_group_membership]> No matching groups found for given group_dn: ". $group_dn);
			return false;

		}
	}


	public function getAttr() {
		$search = @ldap_read($this->cnx, "cn=subschema", "(objectClass=*)", array('*', 'subschemasubentry'));
		$entries = @ldap_get_entries($this->cnx, $search);
		echo count($entries);
	}

	public function getRootDse() {
		$search = @ldap_read($this->cnx, NULL, 'objectClass=*', array("*", "+"));
		$entries = @ldap_get_entries($this->cnx, $search);
		return $entries[0];
	}


	public function ldap_check_basedn(){
		if ($read = @ldap_read($this->cnx,$this->config['basedn'],'(objectClass=*)',array('dn'))){
			$entry = @ldap_get_entries($this->cnx, $read);
			if (!empty($entry[0]['dn'])) {
				return true;
			}
		}
		return false;
	}
}
?>
