<?php
/**
 * @abstract Session initialization and maintainance.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.1.0 20080430 JJ
 * 
 * @package zk.modules.session
 */


session_start();

define("SN_ROOT", "gobe-module-session");
// Make sure that there is at least a default user auth level
if ( !isset($_SESSION[SN_ROOT]) ) {
	Session::defaults();
}


class Session {
	public static function defaults() {
			
	}
	
	public static function read() {
		return $_SESSION[SN_ROOT];
	}
	
	public static function write($data) {
		$_SESSION[SN_ROOT] = $data;
	}
}

