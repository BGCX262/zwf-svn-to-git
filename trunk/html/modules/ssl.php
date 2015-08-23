<?php
/**
 * @abstract SSL utilities.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 1.0.0 20080416 JJ
 * 
 * @package zk.modules.ssl
 */

include_gobe_module('utils');


class SSL {
	/**
	 * @abstract Detects whether or not an SSL connection is being used.
	 * @return bool Whether or not an SSL connection is being used.
	 */
	public static function isActive() {
		// This may need to be alterered for each server installation as different servers will have different variables
		//  MediaTemple: 				HTTPS == 'on'  or is not set
		//  XAMPP; OS X (standard):		SSL_CIPHER (along with many other SSL_*) is set.
	
		return isset($_SERVER['SSL_CIPHER']) || isset($_SERVER['HTTPS']);
	}
	
	
	/**
	 * @abstract Forces the request to use an SSL connection by redirecting the page to https:://host/request if it is not.
	 * 
	 * @see SSL::isActive()
	 */
	public static function force() {
		if ( !using_ssl() ) {
			Utils::location('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}
	}
}


