<?php
/**
 * @abstract Cryptography library
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.3.0 20080418 JJ
 * @version 0.2.0 20080411 JJ
 * @version 0.1.0 20080319 JJ
 * 
 * @package zk.modules.crypt.aes
 */

include_gobe_module('crypt.interface');

/* Adapted from code by "mre (at) reinhardt (dot) info" found at 
 * <http://php.net/mcrypt>
 */
class Aes128
	implements cryptTemplate_static {
	const DEFAULT_SALT = 'salt123@#$';
	
	
	/**
	 * @abstract Encrypts a plain-text string with AES 128 bit. 
	 *
	 * @param string $text The string to encrypt.
	 * @param string $salt Optional. A string to salt the encryption with (default: Aes128::DEFAULT_SALT).
	 * @param string $iv Optional. A 16 character initialization vector (default: false).  If not provided (i.e.: false), a random 
	 * IV will be generated.
	 * @param bool $returnHex Optional.  Whether to return hex encoded string (true) or a binary string (false) (default: true).
	 * @return string the encrypted string
	 */
	public static function encrypt($text, $salt=Aes128::DEFAULT_SALT, $iv=false, $returnHex=true) {
		if ( strlen($text) % 16 != 0 ) {
			$text .= "\0";
		}
		
		$text = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_128, $salt, $text, MCRYPT_MODE_ECB, Aes128::iv($iv)
		);
		
		return $returnHex ? bin2hex($text) : $text;
	}
	
	
	/**
	 * @abstract Decrypts a string AES 128 bit into plain-text. 
	 *
	 * @param string $text The string to decrypt.
	 * @param string $salt Optional. The string that the encryption was salted with (default: Aes128::DEFAULT_SALT).
	 * @param string $iv Optional. A 16 character initialization vector (default: false).  If not provided (i.e.: false), a random 
	 * IV will be generated.
	 * @param bool $isHex Optional.  Whether to treat $text as a hex (true) or binary string (false) (default: true).
	 * @return string the encrypted string
	 */
	public static function decrypt($text, $salt=Aes128::DEFAULT_SALT, $iv=false, $isHex=true) {
		return mcrypt_decrypt(
			MCRYPT_RIJNDAEL_128, 
			$salt, 
			$isHex ? pack("H*", $text) : $text,
			MCRYPT_MODE_ECB, 
			Aes128::iv($iv)
		);
	}
	
	
	/**
	 * @abstract Deteremins the initialization vector for the encryption
	 *
	 * @param string $iv Optional. An initialization vector of the proper size (default: false). If provided, this value will be 
	 * returned unchanged.
	 * @param string $cipher Optional.  Identical to $cipher in mcrypt_get_iv_size() (default: MCRYPT_RIJNDAEL_128).
	 * @param string $mode Optional.  Identical to $mode in mcrypt_get_iv_size() (default: MCRYPT_MODE_CBC).
	 * @param int $source Optional.  Identical too $source in mcrypt_create_iv() (default: MCRYPT_DEV_RANDOM).
	 * @return string The decrypted string.  If the original string was not a multiple of 16, there will be trailing zeros (0) appended to
	 * the returned plain text.
	 * 
	 * @see mcrypt_get_iv_size()
	 * @see mcrypt_create_iv()
	 */
	public static function iv($iv=false, $cipher=MCRYPT_RIJNDAEL_128, $mode=MCRYPT_MODE_CBC, $source=MCRYPT_DEV_RANDOM) {
		return ($iv === false)
				? mcrypt_create_iv(
					mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM
				)
				: $iv;
	}
}




