<?php
/**
 * @abstract Utilities and other fun. 
 *
 * @author Justin Johnson <johnsonj>
 * @version 1.2.0 20080428 JJ
 * @version 1.0.0 20080416 JJ
 * 
 * @package zk.modules.utils
 */


/**
 * @abstract General utilities.
 */
class Utils {
	/**
	 * @abstract Converts an array to XML notation where all indexes are tag elements and values are inserted therein.  
 	 * String and non-numeric values are wrapped in <![CDATA[value]]>.
	 * 
	 * @param array $data The array to convert into XML.  Can be multidimensional.
	 * @param int $tabDepth Optional.  The number of tabs to prepend before each line (default 0).  This value is managed 
	 * by recursivecalls to this function and need not be set (under normal circumstances) by the calling scope.
	 * 
	 * @return string Returns the formatted XML.
	 */
	public static function array2xml($data, $tabDepth=0) {
		$output = '';
		$nl    = "\n" . str_repeat("\t", $tabDepth++);
		
		foreach ( $data as $key=>$value ) {
			$output .= $nl . '<' . $key . ">";
			
			if ( is_bool($value) ) {
				$value = (int)$value;
			}
			
			if ( is_array($value) ) {
				$output .= array2xml($value, $tabDepth) .$nl;
			} elseif ( is_int($value) || is_float($value) ) {
				$output .= $value;
			} else {
				$output .= '<![CDATA[' . $value . ']]>';
			}
			
			$output .= '</' . $key . ">";
		}
		
		return $output;
	}
	
	
	/**
	 * @abstract Changes the client's location through either a `header` command or a meta refresh.  
	 * ** Always ends script execution with die() ** 
	 * 
	 * @param string $location The location to change to. If empty, will default to SITE_BASEURL.
	 * @param bool $preferHeader Optional. Whether or not to use prefer using the header method (default: true).  If false or 
	 * if the headers have already been sent (see headers_sent()), a meta refresh will be used instead.
	 * 
	 * @see headers_sent().
	 */
	public static function location($location, $preferHeader=true) {
		// Set $prefer_header to false when you want to stop page refreshes from re-submitting data.
		if ( empty($location) ) {
			$location = SITE_BASEURL;
		}
	
		if ( $preferHeader && !headers_sent() ) {
			header('Location: ' . $location);
		} else {
			printf(
				'<html><head><meta http-equiv="refresh" content="0;url=%s"/></head><body><center><br/><br/>Redirecting, please wait</center></body></html>', 
				$location
			);
		}
		
		die();
	}
	
	
	/**
	 * @abstract Returns either the value present in $array at $index, or $default if $index does not exist.
	 *
	 * @param array &$array The array to check $index in.
	 * @param mixed $index The index to check the existance of in $array.
	 * @param mixed $default The value to return if $index does not exist in $array (default: '').
	 *
	 * @return mixed Returns either the value present in $array at $index, or $default if $index does not exist.
	 */
	public static function aset(&$array, $index, $default='') {
		return isset($array[$index]) ? $array[$index] : $default;
	}
	
	
	/**
	 * @abstract Determines if a given string is hexadecimal.
	 * 
	 * @param string $str The string to test.
	 * @return bool Whether or not the string is all hex characters.
	 */
	public static function isHex($str) {
		return (bool)preg_match('`^[a-f0-9]+$`i', $str);
	}
	
	
	/**
	 * @abstract Swaps two values.
	 * 
	 * @param mixed $a The first value.
	 * @param mixed $b The second value.
	 */
	public static function swap(&$a, &$b) {
		$t = $a;
		$a = $b;
		$b = $a;
	}
}

