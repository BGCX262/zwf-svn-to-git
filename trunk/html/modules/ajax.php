<?php
/**
 * @abstract Ajax helpers. 
 * 
 * @author Justin Johnson <johnsonj>
 * @version 1.0.0 20080415 JJ
 * @version 0.0.1 20070306 JJ
 * 
 * @package zk.modules.ajax
 */
 
include_gobe_module('utils');

/**
 * @abstract An Ajax service helper.  Ajax::response should be used to handle all Ajax responses.
 * @uses Utils::array2xml Defined in Utils.
 */
class Ajax {
	const XML   = 1;
	const JSON  = 2;
	const PLAIN = 3;
	const DEBUG = 4;

	
	/**
	 * @abstract Creates a standard response the can be formatted into Json, Xml, plain 
	 * text or 'debug' using var_export.
	 * @param bool $status The status of the Ajax service response.
	 * @param mixed $message  Optional. A message pertaining to the execution of the Ajax service 
	 * from whence this method is called (default: '').  Can be empty.  Can be anything, really.
	 * @param array $additionItems Optional.  Additional reponse data can be passed here (default: false).
	 * @param flag $format The format in which to return the response as indicated by the const values in 
	 * this class (Ajax::JSON, Ajax::XML, Ajax::PLAIN, and Ajax::DEBUG) (default: Ajax::JSON). If invalid,
	 * it will behave as Ajax::PLAIN.  Ajax::PLAIN will cause only $message to be returned.
	 * $return string The complete response in the indicated format.
	 */
	public static function response($status, $message='', $additionalItems=false, $format=Ajax::JSON) {
		$response = array(
			"status"  => $status,
			"message" => $message
		); 
		
		/* Return the standard array with additional_items tacked on the end */ 
		if ( is_array($additionalItems) && !empty($additionalItems) ) {  
			$response =  array_merge($response, $additionalItems);
		}
		
		switch ( $format ) {
			case Ajax::JSON:
				return json_encode($response);
				
			case Ajax::XML:
				return Utils::array2xml(array('response'=>$response));
				
			case Ajax::DEBUG:
				return var_export($response, true);
		}
		
		// Plain text response just returns the message
		return $message;	
	}
}

?>