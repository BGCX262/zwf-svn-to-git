<?php
/**
 * @abstract Email containers and functionality.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 1.2.0 20090304 JJ (integrated PHP mailer)
 * @version 1.2.0 20080421 JJ
 * @version 1.0.0 20080416 JJ
 * 
 * @package zk.modules.email
 */


include_gobe_module('validate');
include_gobe_module('stdlib');
include_gobe_module('phpmailer.phpmailer');



/**
 * @absrtact Basic emailing functionality
 */
class Email {
	/**
	 * @abstract A wrapper to the mail function.  All parameters are the same accept for $headers.  The $to address will be validated
	 * via Email::validate.
	 *
	 * @param mixed $to An email address which to send this email to.  Can either be a string address or an EmailAddress object.
	 * @param string $subject The subject of the email.
	 * @param string $message The message of the email.
	 * @param mixed $headers Optional. If a string, then it is passed directly to mail() as it is; otherwise, it is passed to Email::headers()
	 * (default: false).
	 * @return bool True if the mail was sent successfully; otherwise, false.
	 * 
	 * @see EmailAddress()
	 * @see Email::validate()
	 * @see Email::headers()
	 */
	public static function send($to, $subject, $body, $replyTo=false) {
		if ( !Email::validate($to) ) {
			return false;
		}
		
		$mail             = self::newMailer();
		$mail->AddAddress($to);
		$mail->From       = MAIL_SENDER_NOREPLY;
		$mail->FromName   = MAIL_SENDER_NOREPLY_NAME;
		$mail->Subject    = $subject;
		$mail->Body       = $body;
		
		if ( $replyTo ) {
			if ( !is_array($replyTo) ) {
				$replyTo = array($replyTo, '');
			}
			
			$mail->AddReplyTo($replyTo[0], $replyTo[1]);
			$mail->From      = $replyTo[0];
			$mail->FromName  = $replyTo[1];
		}
		
		return $mail->Send();
	}
	
	
	/**
	 * @abstract Formats provides additional standard headers and formats additional headers from an array.
	 *
	 * @param mixed $address If it is an object it is treated as an EmailAddress and converted into long form "name <address@domain.tld>".
	 * @param mixed $headers Optional.  If an array, it is formatted into a string; otherwise, it is ignored (default: false).
	 * @return string Formatted headers string.
	 */
	public static function headers($address, $headers=false) {
		// If address is an object, treat it as an EmailAddress
		if ( is_object($address) && !empty($address->name) ) {
			$address = $address->address . ' <' .$address->name . '>';
		}
		
		$standard_headers = array(
			'from'         => $address,
			'reply-to'     => $address,
			'return-path'  => $address,
			'x-mailer'     => 'PHP/' . phpversion() 
		);
		
		
		if ( is_array($headers) ) {
			// Add an $standard_headers that are not in $headers
			$headerskeys = array_keys($headers);
			foreach ( $standard_headers as $key=>$value ) {
				// Caseless comparison amongst keys
				if ( !array_key_existsi($key, $headers) ) {
					$headers[$key] = $value;
				}
			}
		}
		// No extra headers passed, use only $standard_headers
		else {
			$headers = $standard_headers;
		}
		
		$headerStr = '';
		foreach ( $headers as $key=>$value ) {
			$headerStr .=  $key . ": " . $value . "\r\n";
		}
		
		return $headerStr;
	}
	

	/**
	 * @abstract A wrapper to Validate::email()
	 *
	 * @param string $addr 
	 * @return unknown
	 * 
	 * @see Validate::email()
	 */
	public static function validate($addr) {
		return is_string($addr) ? Validate::email($addr) : Validate::email($addr->address); 
	}

	public static function newMailer() {
		$mail             = new PHPMailer();
	
		$mail->IsSMTP();
		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = "ssl";
		$mail->Host       = MAIL_HOST;
		$mail->Port       = MAIL_PORT;
		
		$mail->Username   = MAIL_USER;
		$mail->Password   = MAIL_PASSWORD;
		
		return $mail;
	}
}






/**
 * @abstract Basic email address container.
 */
class EmailAddress {
	public $address;
	public $name;
	
	/**
	 * @abstract Accepts an email address and optional name.
	 *
	 * @param string $address An email address.
	 * @param string $name (Optional) A name associated with the $address (default: '').
	 */
	public function __construct($address, $name='') {
		$this->address = $address;
		$this->name    = $name;
	}
	
	
	/**
	 * @abstract Returns a string form of EmailAddress.
	 *
	 * @return string Returns just the email address.
	 */
	public function __toString() {
		return $this->address;
	}
}


