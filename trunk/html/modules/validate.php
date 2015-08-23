<?php
/**
 * @abstract Common validation routines
 * 
 * @author Justin Johnson <johnsonj>
 * @version 1.0.1 20080619 JJ
 * @version 1.0.0 20080416 JJ
 * 
 * @package zk.modules.validate
 */


class Validate {
	/**
	 * @abstract Validates an email address.
	 *
	 * @param string $addr An email address to validate.
	 * @return bool Whether or not $host is a valid host.
	 * 
	 * @see Validate::host()
	 */
	public static function email($addr) {
		$addr = split('@', $addr);
		return isset($addr[1]) && 
			   (bool)preg_match('`^\w[-._\w]*\w$`i', $addr[0]) && 
			   Validate::host($addr[1], false);
	}
	
	
	/**
	 * @abstract Validates a string as a valid IP address.
	 *
	 * @param string $$addr An IP address to validate. 
	 * @param bool $allowPort Optional. Whether or not to allow mathcing of a port prefix (e.g.: ...:80) (default: false).
	 * @return bool Whether or not $$addr is a valid IP address.
	 */
	public static function ip($addr, $allowPort=false) {
		return (bool)preg_match(
			'`^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'.
			'(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'  .
			'(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'  .
			'(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)' .
			($allowPort ? '(:\d{1,5})?' : '') .
			'$`',
			$addr
		);
	}
	
	
	/**
	 * @abstract Validates a string as a valid domain name (supports subdomains).
	 *
	 * @param string $domain A domain name to validate. 
	 * @param bool $allowPort Optional. Whether or not to allow mathcing of a port prefix (e.g.: ...:80) (default: false).
	 * @return bool Whether or not $domain is a valid domain.
	 */
	public static function domain($domain, $allowPort=false) {
		return (bool)preg_match('`^(\w[-_\w]*\w\.)+' .
				'((aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|tv)|'.
				'(co\.[a-z]{2}))' .
				($allowPort ? '(:\d{1,5})?' : '') .
				'$`i',
				$domain
			);
	}
	
	
	/**
	 * @abstract Validates a host as either a domain or an IP address (with optional port prefix).
	 *
	 * @param string $host An IP address or domain name.
	 * @param bool $allowPort Optional. Whether or not to allow mathcing of a port prefix (e.g.: ...:80) (default: false).
	 * @return bool Whether or not $host is a valid host.
	 * 
	 * @see Validate::ip()
	 * @see Validate::domain()
	 */
	public static function host($host, $allowPort=false) {
		return Validate::ip($host, $allowPort) || Validate::domain($host, $allowPort);
	}
	
	
	public static function url() {
		 
	}
	
	
	public static function timestamp($t, $baseDate=0) {
		return $t <= $baseDate;
	}
	
	
	public static function timeString($t) {
		return strtotime($t) !== false;
	}
	
	
	public static function bounds($obj, $min, $max) {
		$length = is_string($obj) ? 'strlen' : 'count';
		return $length($obj) >= $min && $length($obj) <= $max;
	}
	
	
	public static function money(&$money) {
		$money = trim($money);
		return is_numeric($money) && $money != '';
	}
	
	
	public static function phone($phone, $require_area=true) {
		return preg_match('`^' .
				$require_area ? '|\d{0}' : '' .
				'(\d{3})[\s\-]*(\d{4})$`',
				$phone
		);
	}
	
	
	/**
	 * @abstract Attempts to detect a string that could be intended to execute an html/javascript insertion-type attack.
	 * @param $str string The string to test for malicious characters.
	 * @return boolean True if $str contains ", ', ;, \, /, [, ], <,  or >   False otherwise.
	 */
	public static function is_malicious_string($str) {
		return preg_match("`[\"';\\/\[\]<>]+`", $str);
	}
}

