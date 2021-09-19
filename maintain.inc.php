<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/**
 * This class is used to expose maintenance methods to the plugins manager
 * It must extends PluginMaintain and be named "PLUGINID_maintain"
 * where PLUGINID is the directory name of your plugin
 */
class Ldap_Login_maintain extends PluginMaintain
{
  /*
   * My pattern uses a single installation method, which handles both installation
   * and activation, where Piwigo always calls 'activate' just after 'install'
   * As a result I use a marker in order to not execute the installation method twice
   *
   * The installation function is called by main.inc.php and maintain.inc.php
   * in order to install and/or update the plugin.
   *
   * That's why all operations must be conditionned :
   *    - use "if empty" for configuration vars
   *    - use "IF NOT EXISTS" for table creation
   */
	private $installed = false;
	

	function __construct($plugin_id)
	{
		parent::__construct($plugin_id); // always call parent constructor

		// Class members can't be declared with computed values so initialization is done here
		if(!defined('LDAP_LOGIN_PATH')){
			// +-----------------------------------------------------------------------+
			// | Define plugin constants                                               |
			// +-----------------------------------------------------------------------+
			define('LDAP_LOGIN_ID',      basename(dirname(__FILE__)));
			define('LDAP_LOGIN_PATH' ,   PHPWG_PLUGINS_PATH . LDAP_LOGIN_ID . '/');
		
			include_once(LDAP_LOGIN_PATH.'/class.ldap.php');
			include_once(LDAP_LOGIN_PATH.'/functions_sql.inc.php'); 
		}
		

	}
	function __destruct(){
		 //nothing
	}



	function install($plugin_version, &$errors=array())
	{
	/**
     * perform here all needed step for the plugin installation
     * such as create default config, add database tables,
     * add fields to existing tables, create local folders...
	 *
	 * Checks for data.dat and ./config/data.dat
	 * Migrates data.dat to ./config/data.dat
	 * Loads config from ./config/data.dat and imports it to SQL
	 * 
	 * If no config found load default config
	 * At the end, save the config to SQL
	 * 
	 * @since ~
	 *
	 */
		global $conf;
		
		$ldap=new Ldap();
		
		if(!ld_table_exist()){ //new install or from old situation
			$ldap->load_default_config();
			//prepare sql-table
			$ldap->write_log("[Maintain.inc.php/Install]> Created SQL-table");
			ld_sql('create','create_table');
			$ldap->write_log("[Maintain.inc.php/Install]> Created SQL-data from default values");
			ld_sql('create','create_data',$ldap->config);
			
			//everyone, in old situation (ONCE)
			if (file_exists(LDAP_LOGIN_PATH .'/data.dat' ) && !file_exists(LDAP_LOGIN_PATH .'/config/data.dat' )) { //only in root not in .config/
				rename(LDAP_LOGIN_PATH . '/data.dat', LDAP_LOGIN_PATH .'/config/data.dat'); //migrate old location to new
				$ldap->write_log("[Maintain.inc.php/Install]> Moved data.dat");
			}
			
			//future, in new situation (inactivated plugin)
			if (file_exists(LDAP_LOGIN_PATH .'/config/data.dat' )) { 
				$ldap->write_log("[Maintain.inc.php/Install]> loading ./config/data.dat ");
				$ldap->write_log("[Maintain.inc.php/Install]> function load_old_config ");
				$ldap->load_old_config();; // will overwrite default values
				unlink( LDAP_LOGIN_PATH .'/config/data.dat'  ); //delete data.dat
				$ldap->write_log("[Maintain.inc.php/Install]> deleted ./config/data.dat ");
			}
			
		}
		else { 
			$ldap->load_default_config();
			$ldap->write_log("[Maintain.inc.php/Install]> Default config loaded ");
			$ldap->load_config($merge=True);
			$ldap->write_log("[Maintain.inc.php/Install]> Merged old config");
		}
		$ldap->write_log("[Maintain.inc.php/Install]> Saving config");
		$ldap->save_config();
		$ldap->write_log("[Maintain.inc.php/Install]> plugin installed");
		$this->installed = true;

	}


	function activate($plugin_version, &$errors=array())
	{
	/**
     * this function is triggered after installation, by manual activation
     * or after a plugin update
     * for this last case you must manage updates tasks of your plugin in this function
	 *
	 * Creates table and default data
	 * Clears log if parameter not set or True
	 * 
	 * @since ~
	 *
	 */
		$ldap=new Ldap();
		$ldap->load_default_config();
		$ldap->load_config($merge=True);
		$ldap->write_log("[function]> activate");
		if (!isset($ldap->config['ld_debug_clearupdate']) OR ($ldap->config['ld_debug_clearupdate'] == True))
		{   
			$loc = $ldap->check_config('ld_debug_location');
			file_put_contents($loc . 'ldap_login.log','');
			$ldap->write_log("[Maintain.inc.php/Activate]> Ldap_login.log cleared");
		}
		
		if (!$this->installed)
		{ 
			//this first after activation.
			$ldap->write_log("[Maintain.inc.php/Activate]> [Maintain.inc.php/Install] ");
			$this->install($plugin_version, $errors); //then install
			$ldap->write_log("[Maintain.inc.php/Activate]> plugin activated");
		}
		unset($ldap);
	}
	

	function update($old_version, $new_version, &$errors=array())
	{
	/**
	* Plugin (auto)update
	*
	* This function is called when Piwigo detects that the registered version of
	* the plugin is older than the version exposed in main.inc.php
	* Thus it's called after a plugin update from admin panel or a manual update by FTP
	* I (Kipjr) chosed to handle install and update in the same method
	* you are free to do otherwise
	*/
		$this->install($new_version, $errors);
	}
  
	function deactivate()
	{
	/**
	 * this function is triggered after deactivation, by manual deactivation
	 * or after a plugin update
	 *  
	 * Does nothing but writing in a log and exporting data
	 *
	 * @since ~
	 *
	 */	$ldap=new Ldap();
		$ldap->write_log("[Maintain.inc.php/deactivate]> function load_config ");
		$ldap->load_config();
		$ldap->write_log("[function]> deactivate");
		$ldap->export_config(); 	//exports config
		$ldap->write_log("[deactivate]> data exported to ./config/data.dat & plugin deactivated");
		unset($ldap);
	}

	function uninstall()
	{ 
	
	/**
	 * Perform here all cleaning tasks when the plugin is removed
     * you should revert all changes made in 'install'
	 *  
	 * Removes the SQL-table and writes in log
	 *
	 * @since ~
	 *
	 */	
	 	$ldap=new Ldap();
		$ldap->load_config();
		$ldap->write_log("[function]> uninstall");
		ld_sql('delete','delete_table');
		$ldap->write_log("[function]> removed piwigo_ldap_login_config table");
		$ldap->write_log("[deactivate]> plugin uninstalled");
		unset($ldap);
	}
}
?>