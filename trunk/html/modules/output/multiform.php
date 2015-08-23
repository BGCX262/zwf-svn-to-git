<?php
/**
 * Multiform, a template snippet origanizing tool.
 *
 * @author Justin Johnson <justin@booleangate.com>
 * @version 2.5.0 20080225 JJ
 * @version 2.3.2 20071217 JJ
 * @version 2.0.0 20070219 JJ
 *
 * @license This product is licensed under the gLabs license
 * @copyright Copyright 2005-2007, Booleangate.org
 *
 * @todo Add support for regex-find and regex-replace attributes.
 *
 * @package booleangate.glabs.php.output.multiform
 */

// Param array indexes, for internal use
define('MULTIFORM__ID'        , 1);
define('MULTIFORM__TAB_DEPTH' , 2);
define('MULTIFORM__TAB_CHAR'  , 3);
define('MULTIFORM__SRC'       , 4);


/**
 * @global array Key-value pairs for default path keys used when including alt-source multiforms via the src attribute.
 * Overwrite elsewhere to your little heart's content.  Should look something like this:
 *
 *		$multiform_defaultPathKeys = array(
 *			'{PATH_TEMPLATES}'       => 'templates/',
 *			'{PATH_TEMPLATES_STUBS}' => 'templates/stubs/'
 *		);
 */
global $multiform_defaultPathKeys;



/**
 * @abstract Parse a multiform file or string.
 *
 * @param string $src Either a file name or string containing HTML snippets in multiform format
 * @param bool $isFile (Optional; default true) If true (default), $src will be treated as a file; otherwise, it is
 * treated as a template and parsed as it is.
 * @param array $pathKeys (Optional; default empty array) Like $multiform_defaultPathKeys, but used for this form only.
 * @param bool $cacheVars (Optional; default true) Whether or not to cache multiforms dependent on $pathKeys.  If true, caching will
 * be based on $src and $pathKeys combined; otherwise, caching is based on $src alone.
 *
 * @return bool/array An array of HTML snippets parsed from param raw or false on error.
 */
function multiform($src, $isFile=true, $pathKeys=array(), $cacheVars=true) {
	static $cache = array();

	// Base the hash off of the $pathKeys if applicable
	$hash = md5($src . ($cacheVars ? serialize($pathKeys) : ''));

	// If the processed form is cached, just return that
	if ( isset($cache[$hash]) ) {
		echo '<strong>USE CACHE</strong>';
		return $cache[$hash];
	}

	if ( $isFile ) {
		if ( file_exists($src) ) {
			$src = file_get_contents($src);
		} else {
			return false;	// Invalid file path!
		}
	}

	$parts = $forms = array();

	// Get each multiform tag (from front to back)
	preg_match_all("/(<multiform[^>]*>(\n|\r\n|\r)?)(.*)((\n|\r\n|\r)?<\/multiform>)/simU", $src, $parts);
	/*
	 * 0: entire multiform tag (open and close tags)
	 * 1: <multiform.*> tags
	 * 2: [blank line]
	 * 3: innerHTML for each multiform
	 * 4: [blank line]
	 * 5: </multiform>
	 */

	// If there are parts to parse
	if ( count($parts[0]) > 0 ) {
		foreach ($parts[1] as $index => $params) {
			// Get the params for this multiform part
			$params = multiform_parse_params($params);

			// Bad id?
			if ( empty($params) ) continue;

			// If an alternative source is defined, load that and then parse the rest of the parameters
			if ( !empty($params[MULTIFORM__SRC]) ) {
				$parts[3][$index] = multiform_load_alt_src($params[MULTIFORM__SRC], $pathKeys);
				unset($params[MULTIFORM__SRC]);
			}

			// If there is a tab modifcation to be made, make it and add it to the return
			$forms[$params[MULTIFORM__ID]] = ($params[MULTIFORM__TAB_DEPTH] != false)
				? multiform_apply_params($parts[3][$index], $params)
				: $parts[3][$index];
		}
	}

	// Nothing to parse
	else {
		$forms = false;
	}

	// Add this form to the cache
	$cache[$hash] = $forms;

	return $forms;
}





/**
 * @abstract Applies parameters (attributes) set in the multiform tag.
 *
 * @param string &$input This is the actual contents of a multiform (the innerHTML, if you will).
 * @param array &$params Attributes to be applied to this multiform (currently only tabbing).
 *
 * @see multiform, multiform_parse_params
 */
function multiform_apply_params(&$input, &$params) {
	// If the tab value is not set or zero, just return (nothing to do here).
	if ( !empty($params[MULTIFORM__TAB_DEPTH]) ) {
		return $input;
	}

	// Determine and store the line delimeter
	$delimeter = '';
	preg_match('/(\n|\r\n|\r)?/', $input, $delimeter);
	$delimeter = $delimeter[0];

	// Explode into lines
	$lines = explode($delimeter, $input);

	// Capture for convenience and readability
	$depth    = $params[MULTIFORM__TAB_DEPTH];
	$tab_char = $params[MULTIFORM__TAB_CHAR];

	// TAB=[negative]
	// Remove (at most) tab-depth amount of tab-char's from the beginning of each line
	if ( $depth < 0 ) {
		// Absolute number of tabs to remove
		$abs_depth = abs($depth);

		// Analyze each line and remove tab-chars (if applicable)
		foreach ($lines as $index => $line ) {
			// Only alter non-empty lines
			if ( !preg_match('/^\s*$/', $line) ) {
				$lines[$index] = preg_replace('/^' .$tab_char. '{0,' .$abs_depth. '}/', '', $line);
			}
		}
	}

	// TAB=[positive]
	// prepend the custom tabs to every line
	elseif ( $depth > 0 ) {
		// Create the tab string to prepend to each line
		$custom_tab = str_repeat($tab_char, $depth);

		// Append the tab string to all non-empty lines
		foreach ($lines as $index => $line ) {
			if ( !preg_match('/^\s*$/', $line) )
				$lines[$index] = $custom_tab . $line;
		}
	}

	// Re-assemble the lines into one string based on the endl delimeter
	return implode($delimeter, $lines);
}


/**
 * @abstract Parses parameters values from a string and returns them as an associative array.
 * Valid attributes are ID (int, required), tab (int), tabchar (char), src (path string).
 *
 * @param string &$param_string The parameters/attributes of a given multiform in string form.
 *
 * @return mixed Returns an associative array of the parsed attributes.  If the ID attribute does
 * not exist, then false is returned (ID is a required attribute).
 */
function multiform_parse_params(&$param_string) {
	// Find applicable flags
	$id = $tab_depth = $tab_char = $include_src = false;

	// Find the ID first
	preg_match("/id=((\"([^\"]*)\")|('([^']*)'))/i", $param_string, $id);

	// If no ID is found, error out
	if ( ($id = multiform_parse_params_get_param($id)) === false ) {
		return false;
	}

	// Get the reset of the parameters
	preg_match("/tab=((\"([^\"]*)\")|('([^']*)'))/i",     $param_string, $tab_depth);
	preg_match("/tabchar=((\"([^\"]*)\")|('([^']*)'))/i", $param_string, $tab_char);
	preg_match("/src=((\"([^\"]*)\")|('([^']*)'))/i",     $param_string, $include_src);

	// Put all of the parameters in a container
	$params = array(
		MULTIFORM__ID        => $id,
		MULTIFORM__TAB_DEPTH => multiform_parse_params_get_param($tab_depth),
		MULTIFORM__TAB_CHAR  => multiform_parse_params_get_param($tab_char),
		MULTIFORM__SRC       => multiform_parse_params_get_param($include_src)
	);

	// Validate that tab-depth is numeric and round it if necessary
	if ( $params[MULTIFORM__TAB_DEPTH] !== false ) {
		// If tab-depth is zero (0) or not numeric, set it to false and ignore it
		if (
			(float)$params[MULTIFORM__TAB_DEPTH] != $params[MULTIFORM__TAB_DEPTH] ||
			(int)$params[MULTIFORM__TAB_DEPTH] == 0
		) {
			$params[MULTIFORM__TAB_DEPTH] = false;

			// Return now; tab-char is irrelevant if tab-depth is zero
			return $params;
		}

		// Otherwise, make sure it is an int
		else {
			$params[MULTIFORM__TAB_DEPTH] = (int)$params[MULTIFORM__TAB_DEPTH];
		}
	}


	// If tab-char is not empty, parse any \t, \n, etc with stripcslashes
	if ( $params[MULTIFORM__TAB_CHAR] !== false ) {
		$params[MULTIFORM__TAB_CHAR] = stripcslashes($params[MULTIFORM__TAB_CHAR]);
	}

	// Default to tabs (\t) if tab-char is omitted
	else {
		$params[MULTIFORM__TAB_CHAR] = "\t";
	}

	return $params;
}


/**
 * @abstract A helper to multiform_parse_params.
 *
 * @param array @param The parameter array.
 *
 * @return mixed Returns a string paramter value, or false if there is bad syntax in the attribute declaration.
 *
 * @see multiform_parse_params.
 */
function multiform_parse_params_get_param(&$param) {
	// The preg from multiform_parse_params will return a parameter in capture [3] if
	// double quotes (") are used or capture [5] if single quotes are used (').
	// Return false if nothing is found.

	return isset($param[3]) && $param[3] != ""
			? $param[3]
			: (
				isset($param[5]) && $param[5] != ""
					? $param[5]
					: false
			  );
}


/**
 * @abstract Loads alternative/remote multiforms pointed to be a multiform src attribute.
 *
 * @param string &$src The value of a src attribute (e.g.: src='value').
 * @param array $pathKeys (Optional; default empty) Create path variables through key-value pairs in an
 * associative array where the variable name is the key, and its value is the value at the key in the array.
 * @param bool $allowDefaultKeys (Optional; default true) If true (default), then any variables in $multiform_defaultPathKeys
 * will be included in $pathKeys.  If false, then $multiform_defaultPathKeys is ignored.
 *
 * @return mixed Returns the file contents of $src if $src is a valid file; otherwise, returns false.
 */
function multiform_load_alt_src(&$src, $pathKeys=array(), $allowDefaultKeys=true) {
	/* Source path variables behave in the following manner.
	 *  1. Variables are defined as key-value pairs passed to this function as the associative array, $pathKeys.
	 *  2. Any occurance of {key} within the src attribute of a multiform tag is then replaced with the
	 *    resepective value at that key in the $pathKeys array.
	 *  2a. Default pathKeys can be created in the global scope as key-value pairs in $multiform_defaultPathKeys.
	 */

	// Include default keys if allowed to
	if ( $allowDefaultKeys ) {
		global $multiform_defaultPathKeys;

		// . . . and if they exist
		if ( !empty($multiform_defaultPathKeys) && is_array($multiform_defaultPathKeys) ) {
			$pathKeys = array_merge($pathKeys, $multiform_defaultPathKeys);
		}
	}

	// If any pathKeys have been set, update the $src
	if ( !empty($pathKeys) && is_array($pathKeys) ) {
		$search  = array_keys($pathKeys);
		// Format the path variables
		array_walk($search, 'multiform_format_path_variable');
		$replace = $pathKeys;

		// Replace path variables
		$src = str_replace($search, $replace, $src);
	}

	// Return the valid file or false.
	return file_exists($src) ? file_get_contents($src) : false;
}


/**
 * @abstract Formats identifiers as path variables.
 *
 * @param str &$var The path variable to be formatted.
 *
 * @see multiform_load_alt_src.
 */
function multiform_format_path_variable(&$var) {
	$var = '{' . $var . '}';
}
