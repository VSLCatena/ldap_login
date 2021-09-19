<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


function ld_table_exist() {
/**
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
	global $prefixeTable, $conf;
	$query = "SELECT count(*) as count FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '" . $conf['db_base'] . "') AND (TABLE_NAME = '" . $prefixeTable . "ldap_login_config')";
	$r = query2array($query);
	$c = reset($r)['count'];
	if(($c) == 0)
	{
	   //table not found..
 
	   return false;
	}
	else
	{
		return true;
		//I can find it...or more than one table found..
	}
}


function ld_sql($action='get',$type=null,$data=null){
	
/**
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

global $prefixeTable;
	
	###
	### GET 
	###

	if ($action === 'get') {
		if(ld_table_exist()){
			$query ='SELECT param,value FROM ' . $prefixeTable . 'ldap_login_config';
			$result = query2array($query,'param','value');
			return $result;
		}
	};

	###
	### CREATE 
	###	ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;
	if ($action == 'create'){
		if ($type == 'create_table') {
			$query="CREATE TABLE IF NOT EXISTS `" . $prefixeTable . "ldap_login_config` (`param` varchar(40) CHARACTER SET utf8 NOT NULL,`value` text CHARACTER SET utf8,`comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,UNIQUE KEY `param` (`param`)) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;";
			pwg_query($query);
		
		}	
		if ($type == 'create_data') {
			if(isset($data)){
				foreach($data as $k => $v){
					$datas[]=array(
						'param' => $k,
						'value' => pwg_db_real_escape_string($v)
						);
				}
				mass_inserts($prefixeTable . 'ldap_login_config', array('param','value'), $datas,array('ignore'=>true));
			}
			
		}	
	}
	###
	### Update 
	###	
	
	//maybe try this in future, but piwigo functions dont support it (yet)
	//INSERT OR IGNORE INTO book(id) VALUES(1001);
	//UPDATE book SET name = 'Programming' WHERE id = 1001;	

	
	if ($action == 'update'){
		if(ld_table_exist()){
			if ($type == 'reset_openldap') {
				$updates = array(
					array(
						'param' => 'ld_user_attr',
						'value' => 'cn'
						),			
					array(
						'param' => 'ld_user_class',
						'value' => 'inetOrgPerson'
						),			
					array(
						'param' => 'ld_group_class',
						'value' => 'groupOfNames'
						)
					);
				mass_updates(
					$prefixeTable . 'ldap_login_config',
					array(
					'primary' => array('param'),
					'update' => array('value')
					),
					$updates
				);			
			}
			if ($type == 'reset_ad') {
				$updates = array(
					array(
						'param' => 'ld_user_attr',
						'value' => 'sAMAccountname'
						),			
					array(
						'param' => 'ld_user_class',
						'value' => 'user'
						),			
					array(
						'param' => 'ld_group_class',
						'value' => 'group'
						)
					);
				mass_updates(
					$prefixeTable . 'ldap_login_config',
					array(
					'primary' => array('param'),
					'update' => array('value')
					),
					$updates
				);	
			}
			if ($type == 'update_value') {
				if (isset($data)) {
					foreach($data as $k => $v){
						$updates[]=array(
							'param' => $k,
							'value' => pwg_db_real_escape_string($v)
							);
					}
					mass_updates(
						$prefixeTable . 'ldap_login_config',
						array(
						'primary' => array('param'),
						'update' => array('value')
						),
						$updates
					);
				}				
			}			
			if ($type == 'clear_mail_address') {
				$query="update piwigo_users SET mail_address = null WHERE id > 2";
				pwg_query($query);				
			}
		}
	}

	###
	### DELETE 
	###	

	if ($action == 'delete'){
		if(ld_table_exist()){
			if ($type=='delete_table') {
				$query="
				DROP TABLE `" . $prefixeTable . "ldap_login_config`;
				";
				pwg_query($query);
			}
		}
	}	
}
?>