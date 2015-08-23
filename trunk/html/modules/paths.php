<?php
/**
 * GOBE Module (paths)
 * 
 * Path handling and insertion
 * 
 * @author Andrew Murphy <andrew@booleangate.com>
 * @version 1.0.1b 20061208
 */


function handle_paths($action, $value = -1){
	static $paths = null;

	if(!is_array($paths)){
		$paths = array();
	}

	switch($action){
		case HANDLE_SET:
			if(is_array($value) && count($value) == 2){
				$value[0] = strtolower($value[0]);
				register_gobe_debug("gomo.paths: Setting path[".$value[0]."] = ".$value[1]);
				$paths[$value[0]]  = $value[1];
			}else{
				register_gobe_module_error("gomo.paths: Unable to set path; Incorrect number of parameters");
				return false;
			}
			break;
		case HANDLE_RESET:
			$paths = array();
			break;
		case HANDLE_GET:
			if(is_array($value) && count($value) > 0){
				$value = $value[0];
			}

			$value = strtolower($value);

			if(array_key_exists($value, $paths)){
				return $paths[$value];
			}else{
				register_gobe_module_error("gomo.paths: Unable to get path; '$value' doesn't exist");
				return "";
			}
			break;
		
	}
	
	return true;
}



function get_path($path, $endSlash = false){
	//endSlash - do we have a slash at the end of the url
	$path = handle_paths(HANDLE_GET, $path);

	if($endSlash){
		if(substr($path, -1) != "/" && substr($path, -1) != "\\"){
			$path .= "/";
		}
	}else{
		if(substr($path, -1) == "/" || substr($path, -1) == "\\"){
			$path = substr($path, 0, -1);
		}
	}

	return $path; 
}


function set_path($path, $value){
	return handle_paths(HANDLE_SET, array($path, $value));
}


//Note: assumes no end slash
function parse_path($str){
	return preg_replace_callback("/%([^\s]+)%/i", "callback_parse_path", $str);
}

function callback_parse_path($matches){
	if(count($matches) < 2){
		return "";
	}
	
	return get_path($matches[1]);
}


function infolder($needle, $haystack) {
	$haystack = pathinfo($haystack, PATHINFO_DIRNAME);

	return strpos($haystack, $needle) !== false;
}



if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback("path", "set", "set_path");
	add_gobe_callback("path", "get", "get_path");
	add_gobe_callback("path", "parse", "	");
}

