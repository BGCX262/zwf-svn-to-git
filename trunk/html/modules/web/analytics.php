<?php

include_gobe_module("goat");

/**
 * @abstract Generic web anayltics insertion
 * 
 * @author Justin Johnson <johnsonj>
 * @version 20090726 <johnsonj>
 * 
 * @package zk.modules.web.analytics
 */
class Analytics {
	const TEMPLATE_EXTENSION = ".html";
	const TEMPLATE_PATH      = "web/analytics/";
	const DEFAULT_TEMPLATE   = "google";
	
	static private $blockedServers = array();
	static private $blockedUsers   = array();
	
	static public function build($template, $id) {
		$template = self::getTemplate($template);

		return self::isBlocked() ? self::format($template, $id) : "";
	}
	
	static public function blockServer($domains) {
		self::$blockedServers = array_merge(self::$blockedServers, (array)$domains);
	}
	
	static public function blockUser($ips) {
		self::$blockedUsers = array_merge(self::$blockedUsers, (array)$ips);
	}
	
	static private function getTemplate($template) {
		if ( empty($template) ) {
			 $template = self::DEFAULT_TEMPLATE;
		}
		$template .= self::TEMPLATE_EXTENSION;
		
		return file_get_contents(PATH_TEMPLATES_STUBS . self::TEMPLATE_PATH . $template);
	}
	
	static private function format($template, $id) {
		$goat = new goat();
		
		$goat->register_variable("id", $id);
		
		return $goat->parse($template);
	}
	
	static public function isBlocked() {
		return !in_array($_SERVER['SERVER_ADDR'], self::$blockedServers) && 
			   !in_array($_SERVER['HTTP_HOST'],   self::$blockedServers) &&
			   !in_array($_SERVER['REMOTE_ADDR'], self::$blockedUsers);
	}
}



if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback('web' , 'analytics' , array('Analytics', 'build'));
}
