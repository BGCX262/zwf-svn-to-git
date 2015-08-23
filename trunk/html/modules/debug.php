<?php
/**
 * @abstract Debug functionality.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 2.0.0 20080415 JJ
 * 
 * @package zk.modules.debug
 */


class Debug {
	/**
	 * @abstract The standard var_dump() wrapped in <pre>.
	 * @params mixed An number of variables can be passed.
	 */
	public static function var_dump() {
		echo "<pre>";
		var_dump( func_get_args() );
		echo "</pre>";
	}
	
	
	/**
	 * @abstract The standard print_r() wrapped in <pre>.
	 * @params mixed An number of variables can be passed.
	 */
	public static function print_r() {
		echo "<pre>";
		foreach ( func_get_args() as $arg ) {
			echo print_r($arg, true), "\n";
		}
		echo "</pre>";
	}
}


if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback('debug', 'dump',  array('Debug', 'var_dump'));
	add_gobe_callback('debug', 'print', array('Debug', 'print'));
}

?>