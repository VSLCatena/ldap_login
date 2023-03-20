<?php


global $conf;
class Ldap {
	var $cnx;
	var $config;
	var $groups = array();
	var $warn_msg = array();
	var	$default_val = array(
		'ld_forgot_url' => 'password.php',
		'ld_debug_location' =>'./plugins/ldap_login/logs/',
		'ld_debug' => 1,
		'ld_debug_clearupdate' => 1,
		'ld_debug_level' => 'debug',
		'ld_debug_php' => 1,
		'ld_host' => 'localhost',
		'ld_port' => '389', 
		'ld_basedn' => 'ou=base,dc=example,dc=com',
		'ld_user_class' => 'person',
		'ld_user_attr' => 'samaccountName',
		'ld_user_filter' => null, 
		'ld_group_class' => 'group',
		'ld_group_filter' => null,
		'ld_group_attr' => 'name',
		'ld_group_desc' => 'description',
		'ld_group_basedn' => 'cn=groups,dc=domain,dc=tld',
		'ld_group_member_attr' => 'member',
		'ld_user_member_attr' => 'memberOf',
		'ld_group_webmaster' => 'cn=piwigo_webmasters,cn=groups,dc=domain,dc=tld',
		'ld_group_admin' => 'cn=piwigo_admins,cn=groups,dc=domain,dc=tld',
		'ld_group_user' => 'cn=piwigo_users,cn=groups,dc=domain,dc=tld',
		'ld_binddn' => 'cn=service_account, ou=Users, ou=base, dc=domain,dc=tld',
		'ld_bindpw' => null,
		'ld_anonbind' => 0,
		'ld_use_ssl' => 0,
		'ld_membership_user' => 0,
		'ld_group_user_active' => 1,
		'ld_group_admin_active' => 0,
		'ld_group_webmaster_active' => 0,
		'ld_sync_data' => null,
		'ld_allow_newusers' => 1,
		'ld_use_mail'=> 1,
		'ld_allow_profile' => 1,
		'ld_advertise_admin_new_ldapuser' => 0,
		'ld_send_password_by_mail_ldap' => 0
		);


	public function write_log($message,$loglevel='DEBUG',$format="Y:m:d H:i:u"){
		//[2020-01-01T23:47:52+00:00] DEBUG: New LDAP --> DATE_ATOM
		//[2020-01-01 23:47:523097] DEBUG: New LDAP --> "Y:m:d H:i:u"
		$start_index = 0;
		$end_index = 8000;
		$ts = date_format(date_create() ,$format);
		$message = strip_tags($message);
		$message = substr( $message , $start_index , $end_index ) ;
		$full = "[" . $ts . "] " . $loglevel . ": " . ($message);
		if(isset($this->config['ld_debug'])&&$this->config['ld_debug']){
			file_put_contents($this->check_config('ld_debug_location') . 'ldap_login.log',$full."\n",FILE_APPEND);
		}
		error_log( $full );
		
	}

	/**
	 * check ldap configuration
	 *
	 * In case the access to the ldap is anonymous it is imperative to make a research
	 * to test the connection.
	 *
	 * When OpenLDAP 2.x.x is used, ldap_connect() will always return a resource as it does not actually connect
	 * but just initializes the connecting parameters. The actual connect happens with the next calls
	 * to ldap_* funcs, usually with ldap_bind().
	 */
	public function check_ldap(){
		$this->write_log("[function]> check_ldap");
		if (!$this->ldap_conn()) {
			return $this->getErrorString();
		}

		// root account test if completed
		if (!empty($this->config['ld_binddn']) && !empty($this->config['ld_bindpw'])){ // if empty ld_binddn, anonymous search
		
			// authentication with rootdn and rootpw for search
			if (!$this->ldap_bind_as($this->config['ld_binddn'],$this->config['ld_bindpw'])){
				return $this->getErrorString();
			}
		} else {
			// otherwise search for basedn (see behavior ldap_connect with OpenLDAP)
			if (!$this->ldap_check_basedn()){ // search userdn
				return $this->getErrorString();
			}
		}
		return true;
	}
	public function check_config($var=null,$ignore=False){
		//loads default config item if no config was found
		$var_u=strtoupper($var);
		$var_l=strtolower($var);
		if (!(isset($this->config[$var_l]))){ //is var set in loaded config
			if($ignore){
				return ""; //return empty string
			}
			else{
				$this->warn_msg[$var_u]="Default loaded"; //give red warning
				return $this->default_val[$var_l]; //return default value
			}
		}
		else{
			return $this->config[$var_l];
		}
		 
	} 
	public function load_default_config(){
		
		foreach($this->default_val as $key=>$value){
			$this->config[$key]=$value;	
		}
		$this->write_log("[function]> load_default_config");
	}
	
	
	
	function load_config($merge=false) {
		if(!$merge){
			$this->write_log("[load_config]> Getting data from SQL table");
			$this->config=ld_sql('get');
		}
		else{ //only in situation where default config has  been loaded (x+n keys) and personal config (x keys) are to be merged
			$data=ld_sql('get'); //old config (x keys)
			foreach($data as $key=>$value){ //looping over x keys (thus omitting n keys)
				$this->config[$key]=$value;	 //setting value
			}
		}
	}
	function load_old_config() {
		if (file_exists(LDAP_LOGIN_PATH .'/config/data.dat' )){
			// first we load the base config
			$conf_file = @file_get_contents( LDAP_LOGIN_PATH . '/config/data.dat' );
			if ($conf_file!==false)
			{
				$this->config = unserialize($conf_file);
				$this->write_log("[load_old_config]> Getting data from ./config/data.dat");
			} 
		}	
	}

	function save_config()
	{	
		
		$this->write_log("[save_config]> Saving values in SQL table");
		ld_sql('update','update_value',$this->config);
		
	}
	function export_config()
	{			
		$file = fopen( LDAP_LOGIN_PATH.'/config/data.dat', 'w' );
		fwrite($file, serialize($this->config) );
		fclose( $file );
		
	}

	function ldap_admin_menu($menu)
	{
		array_push($menu,
		array(
		'NAME' => 'Ldap Login',
		'URL' => get_admin_plugin_menu_link(LDAP_LOGIN_PATH . 'admin.php') )
		);
		return $menu;
	}

	// LDAP connection public
	public function ldap_conn(){
		$this->write_log("[function]> ldap_conn");
		if( $this->cnx = $this->make_ldap_conn() ){
			$this->write_log("[ldap_conn]> true");
			return true;
		}
		$this->write_log("[ldap_conn]> false");
		return false;
	}

	// LDAP connection private
	private function make_ldap_conn(){
		$this->write_log("[function]> make_ldap_conn");
		if ($this->config['ld_use_ssl'] == 1){
			$this->write_log("[make_ldap_conn]> using LDAPS");
			if (empty($this->config['ld_port'])){
				$this->write_log("[make_ldap_conn]> ld_port is empty,using ldaps://");
				$this->config['uri'] = 'ldaps://'.$this->config['ld_host'];
			}
			else {
			$this->write_log("[make_ldap_conn]> ld_port is ". $this->config['ld_port'] .". Connecting using ldaps://");
			$this->config['uri'] = 'ldaps://'.$this->config['ld_host'].':'.$this->config['ld_port'];
			}
		}

		// now, it's without ssl
		else {
			if (empty($this->config['ld_port'])){
				$this->write_log("[make_ldap_conn]> ld_port is empty, using default protocol");
				$this->config['uri'] = 'ldap://'.$this->config['ld_host'];
			}
			else {
				$this->write_log("[make_ldap_conn]> ld_port is ". $this->config['ld_port'] .". Connecting using default protocol");
				$this->config['uri'] = 'ldap://'.$this->config['ld_host'].':'.$this->config['ld_port'];
			}
		}
		if (!extension_loaded('ldap')) {
			$this->write_log("[make_ldap_conn]> LDAP extension not loaded, see php_ldap module.");
			print "LDAP extension not loaded<br>";
			return false;
		}
		if ($conn = @ldap_connect($this->config['uri'])){
        	@ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
			@ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3); // LDAPv3 if possible
			$this->write_log("[make_ldap_conn]> connected (LDAP_OPT_PROTOCOL_VERSION 3)");
			return $conn;
		}
		$this->write_log("[make_ldap_conn]> unable to connect");
		return false;
	}

	// return ldap error
	public function getErrorString(){
				$this->write_log("[function]> getErrorString ");
				$result = ldap_err2str(ldap_errno($this->cnx));
				$this->write_log("[getErrorString]> ".$result);
                return $result;
	}

	// authentication public
	public function ldap_bind_as($user,$user_passwd){
		$this->write_log("[function]> ldap_bind_as");
		$this->write_log("[ldap_bind_as]> ".$user);
		
		if($this->make_ldap_bind_as($this->cnx,$user,$user_passwd)){
			$this->write_log("[ldap_bind_as]> Bind was successfull");
			return true;
		}
			$this->write_log("[ldap_bind_as]> Bind failed");
			return false;
        }

	// authentication private
	private function make_ldap_bind_as($conn,$user,$user_passwd){
		$this->write_log("[function]> make_ldap_bind_as");
		$this->write_log("[make_ldap_bind_as]> \$conn,".$user);
		$bind = @ldap_bind($conn,$user,$user_passwd);
		if($bind ){
			$this->write_log("[make_ldap_bind_as]> Bind was successfull");
			return true;
		}
		$this->write_log("[make_ldap_bind_as]> Bind failed");
		return false;
	}

/*  https://www.php.net/manual/en/function.ldap-bind.php#124188
	define("EXPIRED_PASSWORD", 532);
	define("PASSWORD_RESET", 773);

	$handle = ldap_connect('ldap://active.directory.server/');
	$bind = ldap_bind($handle, 'user', 'expiredpass');

	if (!$bind) {
		if (ldap_get_option($handle, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
				$errno = explode(',', $extended_error)[2];
				$errno = explode(' ', $errno)[2];
				$errno = intval($errno);

				if ($errno === EXPIRED_PASSWORD) {
					$err = 'Unable to login: Password expired';
				} else if ($errno === PASSWORD_RESET) {
					$err = 'Unable to login: Password needs to be reset';
				} else {
					$err = $extended_error;
				}
				if ($errno === EXPIRED_PASSWORD || $errno === PASSWORD_RESET) {
				   #Change password
				}
			}
	}
 */
	public function ldap_get_email($user_dn){
		$this->write_log("[function]> ldap_get_email");
		$sr=@ldap_read($this->cnx, $user_dn, "(objectclass=*)", array('mail'));
		$entry = @ldap_get_entries($this->cnx, $sr);

		if (!empty($entry[0]['mail'])) {
			$this->write_log("[ldap_get_email]> retrieved email");
			return $entry[0]['mail'][0];
		}
		return null;
	}

	public function ldap_get_user_email($username) {
		$this->write_log("[function]> ldap_get_user_email");
		return $this->ldap_email($this->ldap_get_dn($username));
	}

	// return userdn (and username) for authentication
	public function ldap_search_dn($value_to_search){
		$this->write_log("[function]> ldap_search_dn ");
		$user_filter = strlen($this->config['ld_user_filter'])<1?"cn=*":$this->config['ld_user_filter'];
		$this->write_log("[function]> ldap_search_dn(".$value_to_search.")");
		$filter = '(&(&(objectClass='.$this->config['ld_user_class'].')('.$this->config['ld_user_attr'].'='.$value_to_search.'))('.$user_filter.'))';

		// connection handling
		$this->write_log("[ldap_search_dn]> Connecting to server");
		if(!$this->cnx){
			$this->write_log("[ldap_search_dn]> Cannot connect to server!");
			return false;
		}
		$this->write_log("[ldap_search_dn]> make_ldap_bind_as(\$this->cnx,".$this->config['ld_binddn']." ,\$this->config['ld_bindpw']");
		if(!$this->make_ldap_bind_as($this->cnx,$this->config['ld_binddn'],$this->config['ld_bindpw'])){
			$this->write_log("[ldap_search_dn]> Cannot bind to server!");
			return false;
		}

		$this->write_log("[ldap_search_dn]> @ldap_search(\$this->cnx,".$this->config['ld_basedn'].",".$filter.",array('dn'),0,1)");

		// look for our attribute and get always the DN for login
		if($search = @ldap_search($this->cnx,$this->config['ld_basedn'],$filter,array('dn'),0,1)){
			$this->write_log("[ldap_search_dn]> ldap_search successfull");
			$entry = @ldap_get_entries($this->cnx, $search);
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
	public function check_ldap_group_membership($user_dn, $user_login,$group_dn=null){
		$this->write_log("[function]> check_ldap_group_membership");
		$base_dn = $this->config['ld_basedn'];		
		$group_class = $this->config['ld_group_class'];		
		$group_filter = strlen($this->config['ld_group_filter'])<1?"cn=*":$this->config['ld_group_filter'];
		$group_dn = is_null($group_dn)?$this->config['ld_group_user']:$group_dn;
		$group_cn = ldap_explode_dn($group_dn,1)[0];
		$member_attr = $this->config['ld_group_member_attr'];
		

		//if no group specified return true
		if((!$group_dn) OR $this->config['ld_group_user_active'] == False){
			$this->write_log("[check_ldap_group_membership]> No check needed, usergroup inactive or DN empty");
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
		
		$search_filter = "(&(objectclass=$group_class)(cn=$group_cn)($member_attr=$user_dn)($group_filter))"; 
		$this->write_log("[check_ldap_group_membership]> @ldap_search(\$this->cnx,'$base_dn', '$search_filter','$member_attr') for $group_cn");
		$search = ldap_search($this->cnx, $base_dn, $search_filter,array($member_attr),0,0,5); //search for group
		if($search){
			$entries = ldap_get_entries($this->cnx,$search); //get group
			if($entries['count']>0){
				if($this->config['ld_membership_user']==0){
					$this->write_log("[check_ldap_group_membership]> Found user using (&(objectclass=$group_class)(cn=$group_cn)($member_attr=$user_dn)($group_filter))");
					return true;
				}
				$this->write_log("[ldap_get_entries]>". serialize($entries));
				$memberEntries=$entries[0][strtolower($member_attr)];
				for($i=0;$i<$memberEntries['count'];$i++){
					$memberEntry_dn = $memberEntries[$i];
					$this->write_log("[check_ldap_group_membership]> Test ".$user_dn." == ".$memberEntry_dn." ?");
					$this->write_log("[check_ldap_group_membership]> type/len of data: [" . gettype($memberEntry_dn) . ", " . strlen($memberEntry_dn) . "], [" . gettype($user_dn) . ", " .strlen($user_dn) . "]");
					if($memberEntry_dn === $user_dn){ // Match the user.
						$this->write_log("[check_ldap_group_membership]> $member_attr $memberEntry_dn matches $user_login");
						return true;
					}
					unset($memberEntry_dn);
				}
				$this->write_log("[check_ldap_group_membership]> No matches found for $user_login in ". $group_cn);
			}
			else{
				$this->write_log("[check_ldap_group_membership]> No result from ldap_search, check search filter / member_attr");
			}
			
		} else {
			$this->write_log("[check_ldap_group_membership]> ldap_search NOT successful: " .$this->getErrorString());
		}
		return False;
		
	}

	function getUsers($groupDN=null, $attrib='cn'){
    	$ld_basedn=$this->config['ld_basedn'];
    	if(!$this->make_ldap_bind_as($this->cnx,$this->config['ld_binddn'],$this->config['ld_bindpw'])){
			return false;
    	}
		//get users or gets plain (no recursive) users from group
		if(!isset($groupDN)){
			$group_cn = $this->config['ld_group_user_active'] ? ldap_explode_dn($this->config['ld_group_user'],1)[0]:null;
		}
		else{
			$group_cn = ldap_explode_dn($groupDN);
		}
		
		if(!$group_cn){ 
			//full users search
			$search_filter = "(&(objectclass=".$this->config['ld_user_class']."))"; 
			$search = ldap_search($this->cnx, $ld_basedn, $search_filter,array($attrib),0,0,5); //search for group
			$entries = ldap_get_entries($this->cnx,$search); //get users
			unset($entries['count']);
			$ldap_users=array();
			foreach($entries as $k=>$v){
				$ldap_users[]=$v[$attrib][0];
			}
		}
		else {
			//user in usergroup search
			$search_filter = "(&(objectclass=".$this->config['ld_group_class'].")(cn=".$group_cn."))";
        	$this->write_log('[getUsers] -> ldap_search($this->cnx, ' . $ld_basedn . ', ' . $search_filter . ',array("member"),0,0,5); ');
        	if($search = ldap_search($this->cnx,$ld_basedn,$search_filter,array('member'),0,0,5)){ //search for group
				$entries = ldap_get_entries($this->cnx,$search); //get users
				unset($entries[0][0]);
           		unset($entries[0][1]);
          		unset($entries[0]['count']);
           		unset($entries[0]['dn']);
				unset($entries[0]['member']);
            	$ke=array_keys($entries[0]);
				$ldap_users=array();
        		
				if($attrib != 'cn'){
                	foreach($ke as $key => $val){
                    	//print_r($val);
                    	unset ($entries[0][$val]['count']);
						foreach($entries[0][$val] as $k=>$v){                 	
							$sr = ldap_read($this->cnx, $v, '(objectClass=*)', array($attrib));
							$entry = ldap_get_entries($this->cnx, $sr);
                        	//print_r($entry);
                        	$ldap_users[] = $entry[0][$attrib][0];
						

						}
                	}
                //print_r($ldap_users);die;
				}
            	
				else {
					foreach($entries[0]['member'] as $k=>$v){
						$ldap_users[]=ldap_explode_dn($v,1)[0];
					}
				}				
			return $ldap_users;
            }
        }	
     }		

	public function getAttr() {
		$this->write_log("[function]> getAttr ");
		$search = @ldap_read($this->cnx, "cn=subschema", "(objectClass=*)", array('*', 'subschemasubentry'));
		$entries = @ldap_get_entries($this->cnx, $search);
		echo count($entries);
	}

	public function getRootDse() {
		$this->write_log("[function]> GetRootDse ");
		$search = @ldap_read($this->cnx, NULL, 'objectClass=*', array("*", "+"));
		$entries = @ldap_get_entries($this->cnx, $search);
		return $entries[0];
	}


	public function ldap_check_basedn(){
		$this->write_log("[function]> ldap_check_basedn ");
		if ($read = @ldap_read($this->cnx,$this->config['ld_basedn'],"objectClass=*",array('cn'))){

			$entry = @ldap_get_entries($this->cnx, $read);
			if (!empty($entry[0]['dn'])) {
				return true;
			}
		}
		return false;
	}

	public function ldap_get_groups($ld_prim_group){
		$master = array();
		if(!$this->make_ldap_bind_as($this->cnx,$this->config['ld_binddn'],$this->config['ld_bindpw'])){
			return false;
		}
		function ldap_get_group_data($group,$con,$depth,$path,$parent,$attrs) {
			if ($parentData=ldap_read($con,$group, "(|(objectclass=person)(objectclass=groupOfNames))", $attrs)){
				$entry = ldap_get_entries($con, $parentData); #get all info from query
				if($entry['count']>0){ //only if object person / group, will alway return 1 array!
					foreach($attrs as $attr){
						if (in_array($attr, array('dn', 'member', 'objectclass'))){
							$obj_group[$attr]=$entry[0][$attr] ?? null;
						} else { //cn (and ld_user_attr): unique value
							$obj_group[$attr]=$entry[0][$attr][0] ?? null;
						}
					}
					$obj_group['memberCount']=$entry['0']['member']['count'] ?? 0;
					unset($entry['0']['member']['count']);	//remove awefull count key
					$obj_group['member']=$entry[0]['member'] ?? null; //if entry has members than copy to object.
					$obj_group['depth']=$depth;
					$obj_group['path']=$path;
					$parent['dn'] ? $obj_group['parentDN']=	$parent['dn']:null;	//create parentDN if parent['dn'] exist		
					$parent['cn'] ? $obj_group['parentCN']=	$parent['cn']:null;			

					global $master;

					if($depth ==0){ //modify self
						$master[$depth][$obj_group['cn']]=$obj_group;
					}
					if($depth ==1){ //modify childs
						//dept 1 = all users / groups under dept 0
						$obj_group['top']=$parent['cn'];
						$path .= $obj_group['cn'] . " => " ;
						$master[1][$obj_group['top']][$obj_group['cn']]=$obj_group;
					}
					if($depth >1) { //modify grandchilds
						//dept 2,3,4.. = all users / groups under dept 1
						$obj_group['top']=$parent['top'];
						$path .= $obj_group['cn'] . " => " ;
						$master[1][$obj_group['top']][$obj_group['cn']]=$obj_group;
					}
				

					if(in_array('groupOfNames', $obj_group['objectclass'])){
						#modify for next round	
						$depth+=1;
						foreach($obj_group['member'] as $key=>$value){
							if ($value !== $group) {
								ldap_get_group_data($value,$con,$depth,$path,$obj_group,$attrs);
							}
						}
						
					}	
				}
			}
			
			else { 
				#invalid primary group
				return null;
			}
		}
		
		$this->write_log("[function]> ldap_get_groups");
		$attrs = array('cn','dn','member','objectclass');
		if (! in_array($this->config['ld_user_attr'], $attrs)){
			$attrs[] = $this->config['ld_user_attr'];
		}
		$sr=ldap_search($this->cnx, $ld_prim_group, "(!(objectclass=organizationalUnit))", array('dn'));
		$info = ldap_get_entries($this->cnx, $sr);
		unset($info['count']);	
		foreach($info as $k=>$v){
		if (ldap_get_group_data($v['dn'],$this->cnx,$depth=0,$path="",$parent=null,$attrs)){
				
			}
		}
		global $master;
		return 	$master;
	} 	
}
?>
