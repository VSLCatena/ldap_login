<?php

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
class Log extends Logger {
	
	private $handler = "ErrorLogHandler"; 
	// ErrorLogHandler: Logs records to PHP’s error_log() function.
	// StreamHandler: Logs records into any PHP stream, use this for log files.
    private $config;
    private $debug;
    private $debug_location;
    private $debug_php;
    
	private $log;


	/**
	 * Class constructor.
	 *
	 * @param array $options
	 * @return void
	 */
	public function __construct()
	{

		global $ld_config;
        $this->config           =   $ld_config;
        $this->debug            =   $this->getConfigValue('ld_debug');
        $this->debug_location   =   $this->getConfigValue('ld_debug_location');
        $this->debug_php        =   $this->getConfigValue('ld_debug_php');		
	}
   

    
    public function writeLog($message,$loglevel='DEBUG',$format="Y:m:d H:i:u"){
		//[2020-01-01T23:47:52+00:00] DEBUG: New LDAP --> DATE_ATOM
		//[2020-01-01 23:47:523097] DEBUG: New LDAP --> "Y:m:d H:i:u"
		$start_index = 0;
		$end_index = 8000;
		$ts = date_format(date_create() ,$format); 
		// the default date format is "Y-m-d\TH:i:sP"
		// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
		$message = strip_tags($message);
		$message = substr( $message , $start_index , $end_index ) ;
		$full = "[" . $ts . "] " . $loglevel . ": " . ($message);
		if(isset($this->debug)&&$this->debug){
			if(str_starts_with($this->debug_location , "/")) {
				# absolute
				if(is_writable($this->debug_location . 'ldap_login.log')){
					file_put_contents($this->debug_location . 'ldap_login.log',$full."\n",FILE_APPEND);
				} else {
					error_log("Unable to write to " . $this->debug_location . 'ldap_login.log');
				}
			} else {
				# relative (nothing or ./logs/)
				if(is_writable(LDAP_LOGIN_PATH . 'logs/' . 'ldap_login.log')){
					file_put_contents( LDAP_LOGIN_PATH . 'logs/' . 'ldap_login.log',$full."\n",FILE_APPEND);
				} else {
					error_log("Unable to write to " . LDAP_LOGIN_PATH . 'logs/' . 'ldap_login.log');
				}

			}
		}
		if(isset($this->debug_php)&&$this->debug_php){
			error_log( $full );
		}
	}

}