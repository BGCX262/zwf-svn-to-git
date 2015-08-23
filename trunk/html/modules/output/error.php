<?php
/**
 * @abstract Error handling procedures
 *
 * @author Justin Johnson <johnsonj>
 *
 * @version 1.0.1 20080312 JJ
 *
 * @package zk.modules.output.error
 */

//TODO: OO

include_gobe_module('output.multiform');


/**
 * Formats error strings into an error group container as deteremined by mode
 * @param array/string error The error strings to format
 * @param string mode Either 'text' or 'html'; defaults to 'html'
 * @return string Returns formatted errors in an error group container
 */
function error_compile($errors, $mode="html") {
	// Default to html mode for output
	if( $mode != "html" || $mode != "text" ) {
		$mode = "html";
	}

	// Always handle as an array
	if ( !is_array($errors) )
		$errors = array($errors);

	// Get necessary templates
	$forms = multiform(PATH_TEMPLATES_STUBS . "errors.gform.html");
	$error_str = "";

	// Make all of the errors into one string
	foreach ( $errors as $err ) {
		$error_str .= error_format($err, $mode, $forms);
	}

	// Format the error items into the error item group
	return sprintf($forms['error-group-'.$mode], $error_str);
}

/**
 * Formats an error string into an error item container as deteremined by mode
 * @param string error The error string to format
 * @param string mode Either 'text' or 'html'; defaults to 'html'
 * @param array forms (Optional) Templates to use for formatting
 * @return string Returns formatted error item.
 */
function error_format($error, $mode="html", $forms=false) {
	// Default to html mode for output
	if( $mode != "html" || $mode != "text" )
		$mode = "html";

	// Allow forms to be passed
	if ( $forms === false ) {
		// Get necessary templates if not included
		$forms = multiform(PATH_TEMPLATES_STUBS . "errors.gform.html");
	}

	// Format this error into the error item container
	return sprintf($forms['error-item-'.$mode], $error);
}



/**
 * Logs an error to a file.
 */
function error_log_to_file($message, $log_file, $code=false, $file=false, $line=false) {
	$code = $code != false
				? "Error code: " . $code . "\n"
				: "";
	$file = $file
				? "File:       " . $file . "\n"
				: '';
	$line = $line
				? "Line:       " . $line . "\n"
				: '';

	return file_put_contents(ERROR_LOG_DIR .$log_file,
			// Build our log report
			"------------------------------------------------\n" .
			date("r") . "\n" .
			$code .
			"IP address: " . $_SERVER["REMOTE_ADDR"]      . "\n" .
			"Agent:      " . $_SERVER["HTTP_USER_AGENT"]  . "\n" .
			"Referer:    " . @$_SERVER["HTTP_REFERER"]    . "\n" .
			"Script:     " . $_SERVER["SCRIPT_NAME"]      . "\n" .
			"Query:      " . $_SERVER["QUERY_STRING"]     . "\n" .
			$file .
			$line .
			"Method:     " . $_SERVER["REQUEST_METHOD"]   . "\n\t" .

			// Tab indent the message
			preg_replace("`(\n|\r\n|\r)`", "\n\t", error_remove_passwords($message)) .
			"\n\n\n",

			// Lock (exclusive) file and append
			FILE_APPEND | LOCK_EX ) !== false;
}






/**
 * Logs AJAX errors
 */
function error_log_to_file_ajax($class_name, $message, $code=false, $file=false, $line=false) {
	/* Insert the class name in to the code field */
	$error_code = $code . "\nObject:     " . $class_name . "\n";

	return error_log_to_file($message, ERROR_LOG_AJAX, $code, $code, $file, $line);
}

/**
 * Logs database error
 */
function error_log_to_file_database($message, $code=false, $file=false, $line=false) {
	return error_log_to_file($message, ERROR_LOG_DATABASE, $code, $file, $line);
}

/**
 * Logs malicious error
 */
function error_log_to_file_malicious($message, $code=false, $file=false, $line=false) {
	return error_log_to_file($message, ERROR_LOG_MALICIOUS, $code, $file, $line);
}

/**
 * Logs merchant account error
 */
function error_log_to_file_merchant($message, $code=false, $file=false, $line=false) {
	return error_log_to_file($message, ERROR_LOG_MERCHANT, $code, $file, $line);
}

/**
 * Logs merchant transactions
 */
function error_log_to_file_merchant_trans($order_id, $message) {
	return is_readable(LOG_MERCHANT_TRANS)
			? file_put_contents(LOG_MERCHANT_TRANS . $order_id . '.txt', $message, FILE_APPEND)
			: false;
}




/**
 * Logs an exception to the database log file if the exception code is part of the database subset
 */
function error_log_if_database_error($exception) {
	if ( error_is_database_error($exception->getCode()) ) {
		error_log_to_file_database($exception);
	}
}






/**
 * Determines if an error code is within the database subset
 */
function error_is_database_error($code) {
	return $code >= 10 && $code <=20;
}


/**
 * Logs and rethrows database errors
 */
function error_log_and_rethrow($e, $log_file, $new_code=false) {
	error_log_to_file(
		method_exists($e, 'getMessage') ? $e->getMessage() : $e,
		$log_file,
		(method_exists($e, 'getCode') ? $e->getCode() : null)
	);

	throw new Exception(
		method_exists($e, 'getMessage') ? $e->getMessage() : $e,
		(int)($new_code !== false ? $new_code : (method_exists($e, 'getCode') ? $e->getCode() : null))
	);
}


function error_remove_passwords($str) {
	return preg_replace(
			array(
				// Remove database password
				"/(" .quotemeta(DATABASE_PASSWORD). ")/i",
				// Remove user passwords
				"/(user_login\('[^']*', *)('[^']*')/i"
			),
			array(
				'*******',
				'\1\'*******\''
			),
			(string)$str
	);
}



function error_email_error($message, $code, $file) {
echo <<< EOF
	$message
	CODE: $code
	FILE: $file
EOF;
}
