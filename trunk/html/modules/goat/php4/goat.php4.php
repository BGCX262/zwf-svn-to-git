<?php
/**
 * GOAT (GHA Output And Template) for PHP4
 *
 * System for creating rich template output by defining a series of callbacks
 * to functions that may be accessed from within a template string.
 * Please consult the documentation for the php5 version of GOaT for all
 * documentation.
 *
 * @version 2.1.0d 20060501 (Segundo)
 * @author Andrew Murphy <andrew@booleangate.org>
 * @license http://bool-goat.sourceforge.net/license The GOaT Software License
 * @copyright Copyright 2005-2006, The Grey Hat Association
 *
 * @package php.gt.goat
 */


/**#@+
 * @ignore
 */











/**
 * GOAT Object
 *
 * System for creating rich template output by defining a series of callbacks
 * to functions that may be accessed from within a template string.bject.
 *
 * Please note that the php4 and php5 versions of this class both function in
 * the same manner.
 *
 * @version 2.1.0d-php4 (20060501)
 * @package php.gt.goat
 */
class goat {


	/**
	 * Callback storage
	 * @access private
	 * @var matrix
	*/
	var $myCallbacks = array();
	/**
	 * Error message storage
	 * @access private
	 * @var matrix
	*/
	var $myErrors = array();
    /**
	 * Strict or lax ruleset
	 * @access private
	 * @var boolean
	*/
	var $myStrict = GOAT_STRICT;
	/**
	 * String used as a tab by GOaT
	 * @access private
	 * @var string
	*/
	var $myTab = GOAT_DEFAULT_TAB;
	/**
	 * Default number of tabs GOaT prepends to the beginning of a new line
	 * @access private
	 * @var numeric
	*/
	var $myTabCount = GOAT_DEFAULT_TAB_COUNT;
	/**
	 * Optional callback called upon register_error 
	 * @access private
	 * @var string
	*/
	var $myErrorCallback = "";


    /**
	 * Constructor
	 *
	 * Create an initalize the object.
	 *
	 * @name construct
	 *
	 * @param array $callbacks previously defined callbacks; Default array()
	 * @param string $tab tab string
	 * @param numeric $tabCount default number of tabs
	 * @param numeric $strict registry strictness
	*/
	function goat(
		$callbacks = array()                , $tab    = GOAT_DEFAULT_TAB,
		$tabCount  = GOAT_DEFAULT_TAB_COUNT , $strict = GOAT_STRICT
	){
		//if we have a valid callback list defined
		if(is_array($callbacks)){
			$this->myCallbacks = $callbacks; //use it
		}else{
			//throw error
			$this->register_error("Invalid default callback list");
			
			//create a new list
			$this->myCallbacks = array();
		}

		//make sure we have the var group 
		if(!array_key_exists(GOAT_GROUP_VAR, $this->myCallbacks)){
			$this->myCallbacks[GOAT_GROUP_VAR] = array();
		}

		$this->myTab      = $tab;      //tab character
		$this->myTabCount = $tabCount; //tab character

		return;
	}


	/**
	 * Generate GOaT tag
	 *
	 * Generate a GOaT tag based on supplied parameters that may be inserted
	 * into a template and parsed by a GOaT object.
	 * Notice:
	 * This function will NOT parse the argument string to check for bad syntax,
	 * that will occur when the template is parsed. User beware!
	 *
	 * @param string $gid Group ID
	 * @param string $cid Callback ID
	 * @param string $strArgs [Optional] CSV list of all arguments that will be parsed along with the tag
	 * @param numeric $tabCount [Optional] number of tabs to prepend returned value (default -1 will not include the tab parameter)
	*/
	function generate_tag($gid, $cid, $strArgs = "", $tabCount = -1){
		/*
			Since people will surely attempt to find a security hole by including
			%GROUP, %CALLBACK% or the such in the callback, we need to parse the
			template in seperate parts
		*/
		//TODO: Add support for goat variables
		return str_replace(
			"%ARGS%",
			$strArgs,
			str_replace(
				array(
					"%GROUP%",
					"%CALLBACK%",
					"%EXT%",
					"%TAB%"
				),
				array(
					$gid,
					$cid,
					(is_numeric($tabCount) && $tabCount > -1 ? GOAT_TEMPLATE_EXT : ""),
					$tabCount
				),
				GOAT_TEMPLATE_STD
			)
		);
	}


	/**
	 * Define tab string
	 *
	 * Defines the tab string prepended to every new line
	 *
	 * @param string $strTab New tab string
	*/
	function set_tab($strTab = GOAT_DEFAULT_TAB){
		$this->myTab = $strTab;
		return "";
	}


	/**
	 * Define number of tabs
	 *
	 * Defines the default number of tabs prepended to every new line
	 *
	 * @param numeric $tabCount default number of tabs used
	*/
	function set_tab_count($tabCount = GOAT_DEFAULT_TAB_COUNT){
		if(is_int($tabCount)){
			$this->myTabCount = $tabCount;
		}else{
			$this->register_error("Unable to set tab count; Not an integer");
		}

		return "";
	}


	/**
	 * Register an error message
	 *
	 * Registers and stores an error message, and the time it occurred for
	 * retrieval later.
	 *
	 * @version 2.1a (20060127)
	 *
	 * @param string $msg Error message
	*/
	function register_error($msg){
		$err = array(
			GOAT_KEY_ERROR_MSG  => $msg,
			GOAT_KEY_ERROR_TIME => microtime() //more accurate that just time()
		);

		//if we have a valid error callback function, call the function with
		//the arguments 
		if(
			is_string($this->myErrorCallback)      &&
			$this->myErrorCallback != ""           &&
			function_exists($this->myErrorCallback)
		){
			call_user_func_array($this->myErrorCallback, $err);
		}

		$this->myErrors[] = $err;

		return "";
	}


	/**
	 * Retrieve the last error message
	 *
	 * Retrieve the last error message registered with the object.
	 *
	 * @return string
	*/
	function pop_error($format = GOAT_TEMPLATE_ERROR_STD){
		if(count($this->error_count()) > 0){
			$err = array_pop($this->myErrors);

			return str_replace(
				array("%t", "%m"),
				array(
					$err[GOAT_KEY_ERROR_TIME] ,
					$err[GOAT_KEY_ERROR_MSG]
				),
				$format
			);
		}else{
			return "";
		}
	}


	/**
	 * Retrieve all errors
	 *
	 * Retrieve an array of all error message registered with the object.
	 *
	 * @since 2.0.1b (20050519)
	 *
	 * @return array
	*/
	function pop_errors($format = GOAT_TEMPLATE_ERROR_STD){
		$retVal = array();
		

		foreach($this->myErrors as $err){
			$retVal[] = str_replace(
				array("%t", "%m"),
				array(
					$err[GOAT_KEY_ERROR_TIME] ,
					$err[GOAT_KEY_ERROR_MSG]
				),
				$format
			);
		}

		$this->myErrors = array();

		return $retVal;
	}


	/**
	 * Number of stored error messages
	 *
	 * Retrieve the number of error messages registered with the object.
	 *
	 * @return numeric
	*/	
	function error_count(){
		return count($this->myErrors);
	}


	/**
	 * Prepare an id for use by GOAT
	 *
	 * Prepare a callback and grouping id's by capturing only valid characters:
	 * Alpha-numeric, -, _
	 *
	 * @param string $identifier Identifier to be cleaned
	 * @return string
	*/
	function clean_id($identifier){
		return trim(preg_replace("/([a-z0-9\\-_]+)/i", "\\1", $identifier));
	}


	/**
	 * Build a specified expression for parsing
	 *
	 * Build a specified expression for parsing by GOAT; Expressions may be
	 * either standard or extended.
	 *
	 * @param numeric $type Type of expression to construct; Default GOAT_EXTTYPE_DEFAULT
	 * @return string
	*/
	function build_exp($expType = GOAT_EXPTYPE_DEFAULT){
		switch($expType){
			case GOAT_EXPTYPE_DEFAULT:
			case GOAT_EXPTYPE_EXT:
				$exp      = GOAT_EXP_STD;
				$expType -= GOAT_EXPTYPE_DEFAULT;
				break;
			case GOAT_EXPTYPE_VAR:
			case GOAT_EXPTYPE_VAR_EXT:
				$exp      = GOAT_EXP_VAR;
				$expType -= GOAT_EXPTYPE_VAR;
				break;
		}

		if($expType == GOAT_EXPTYPE_EXT){
			$exp .= GOAT_EXP_EXT;
		}

		return $exp.GOAT_EXP_END;
	}


	/**
	 * Check to see if a callback group exists
	 *
	 * Check the callback group listing to see if the group has already been
	 * added to the object.
	 *
	 * @param string $gid Group ID
	 * @return boolean
	*/
	function group_exists($gid){
		$gid = goat::clean_id($gid);
		return (
			$gid == GOAT_GROUP_PREF                   ||
			array_key_exists($gid, $this->myCallbacks)
		);
	}


	/**
	 * Add a callback group
	 *
	 * Register and store a callback group with the object assuming it doesn't
	 * already exist.
	 *
	 * @param string $gid Group ID
	 * @return boolean
	*/
	function register_group($gid){
		//clean id
		$gid = goat::clean_id($gid);

		//if the group doesn't already exist
		if(!$this->group_exists($gid)){
			$this->myCallbacks[$gid] = array();

			return true;		
		}else{
			$this->register_error("Could not register Group; Group '$gid' already exists");
		}

		return false;
	}


	/**
	 * Define a callback to be called whenever register_error is called
	 *
	 * @param string $callback Callback Id
	 * @return Empty string
	*/
	function register_error_callback($callback){
		if(is_string($callback)){
			$this->myErrorCallback = $callback;
		}else{
			$this->register_error("Unable to define error callback; Invalid callback Id (Not a string)");
		}

		return "";
	}


	/**
	 * Remove a callback group
	 *
	 * Unregister and store a callback group with the object assuming it
	 * already exist.
	 *
	 * @param string $gid Group ID
	 * @return boolean
	*/
	function unregister_group($gid){
		//ensure that all id's are safe and clean
		$gid = goat::clean_id($gid);

		switch($gid){
			//can't unregister the following groups
			case GOAT_GROUP_PREF:
			case GOAT_GROUP_VAR:
				$this->register_error("Could not unregister Group; Cannot unregister '".$gid."'");
				break;
			//attempt to remove the group
			default:
				//does the group exist?
				if($this->group_exists($gid)){
					unset($this->myCallbacks[$gid]);
					return true;
				}else{
					$this->register_error("Could not unregister Group; Group '$gid' doesn't exist");
				}
		}

		return false;
	}


	/**
	 * Check if callback exists
	 *
	 * Checks to see if a callback has been defined within the object.
	 *
	 * @param string $gid Group ID
	 * @param string $cid Callback ID
	 * @return boolean
	*/
	function callback_exists($gid, $cid){
		//ensure that all id's are safe and clean
		$gid = goat::clean_id($gid);
		$cid = goat::clean_id($cid);

		//since the preference group is a psuedo group, we need a special way
		//of detecting whether or not its member callbacks exist
		if($gid == GOAT_GROUP_PREF){
			switch($cid){
				case GOAT_CALLBACK_PREF_TAB:
				case GOAT_CALLBACK_PREF_TAB_COUNT:
					return true;
				default:
					return false;
			}
		}
		
		if($this->group_exists($gid)){
			return array_key_exists($cid, $this->myCallbacks[$gid]);
		}

		return false;
	}


	/**
	 * Check if variable exists
	 *
	 * Checks to see if a variable has been defined within the object.
	 *
	 * @param string $vid Variable ID
	 * @return boolean
	*/
	function variable_exists($vid){
		//ensure that all id's are safe and clean
		$vid = goat::clean_id($vid);

		return array_key_exists($vid, $this->myCallbacks[GOAT_GROUP_VAR]);
	}


	/**
	 * Add a callback
	 *
	 * Register a callback with the object assuming it hasn't already been
	 * defined.
	 * Note: depending on whether or not the object is in strict mode
	 * (true/false) if a callback group is not defined, the function will either
	 * error out or automatically register the group.
	 *
	 * @param string $gid Group ID
	 * @param string $cid Callback ID
	 * @param string $callback Callback function
	 * @return boolean
	*/
	function register_callback($gid, $cid, $callback){
		//ensure that all id's are safe and clean
		$gid = goat::clean_id($gid);
		$cid = goat::clean_id($cid);

		if($gid == GOAT_GROUP_PREF){
			$this->register_error(
				"Could not register Callback; Cannot add Callbacks to group '".GOAT_GROUP_PREF."' (Special Group)"
			);
			return false;
		}
		if($gid == ""){
			$this->register_error(
				"Could not register Callback; No Group Id specified"
			);
		}
		if($cid == ""){
			$this->register_error(
				"Could not register Callback; No Callback Id specified"
			);
		}

		//if the callback doesn't already exist
		if(!$this->callback_exists($gid, $cid)){
			//if the callback group doesn't exist and we are allowed to add the
			//group, do it; otherwise register an error
			if(!$this->group_exists($gid)){
				if(!$this->myStrict){
					$this->register_group($gid);
				}else{
					$this->register_error("Could not register Callback; Group '$gid' doesn't exist");

					return false;
				}
			}

			//register the callback
			$this->myCallbacks[$gid][$cid] = $callback;

			return true;
		}else{
			//register an error
			$this->register_error("Could not register Callback; Callback '$gid.$cid' already exists");
		}

		return false;
	}


	/**
	 * Add a variable
	 *
	 * Register a variable with the object assuming it hasn't already been
	 * defined.
	 *
	 * @param string $vid Variable Id
	 * @param string $value Value to be stored
	 * @return boolean
	*/
	function register_variable($vid, $value = ""){
		$vid = goat::clean_id($vid);

		//if variable doesn't already exist
		if(!$this->variable_exists($vid)){
			$this->myCallbacks[GOAT_GROUP_VAR][$vid] = $value;

			return true;
		}else{
			$this->register_error("Could not register Variable; Variable '$vid' already exists");

			return false;
		}
	}


	/**
	 * Modify a callback
	 *
	 * Modify a previously defined callback assuming it has already been created
	 *
	 * @param string $gid Group ID
	 * @param string $cid Callback ID
	 * @param string $callback Callback function
	 * @return boolean
	*/
	function mod_callback($gid, $cid, $callback){
		//ensure that all id's are safe and clean
		$gid = goat::clean_id($gid);
		$cid = goat::clean_id($cid);

		//you can't modify preference callbacks
		if($gid == GOAT_GROUP_PREF){
			$this->register_error("Could not modify Callback; '".GOAT_GROUP_PREF."' is a special group");

			return false;	
		}

		if($this->callback_exists($gid, $cid)){
			$this->myCallbacks[$gid][$cid] = $callback;

			return true;
		}

		$this->register_error("Could not modify Callback; Callback '$gid.$cid' doesn't exist");

		return false;
	}


	/**
	 * Modify a variable
	 *
	 * Modify a previously defined variable assuming it has already been created
	 *
	 * @param string $vid Var ID
	 * @param string $cid Callback ID
	 * @param string $callback Callback function
	 * @return boolean
	*/
	function mod_variable($vid, $value){
		//ensure that all id's are safe and clean
		$vid = goat::clean_id($vid);


		//if variables doesn't already exist
		if($this->variable_exists($vid)){
			$this->myCallbacks[GOAT_GROUP_VAR][$vid] = $value;

			return true;
		}

		$this->register_error("Could not modify Variable; Variable '$vid' doesn't exist");

		return false;
	}


	/**
	 * Remove a callback
	 *
	 * Unregister and store a callback with the object assuming it
	 * already exist.
	 *
	 * @param string $gid Group ID
	 * @param string $cid Callback ID
	 * @return boolean
	*/
	function unregister_callback($gid, $cid){
		//ensure that all id's are safe and clean
		$gid = goat::clean_id($gid);
		$cid = goat::clean_id($cid);

		//can't unregister certain callbacks
		if($gid == GOAT_GROUP_PREF){
			$this->register_error("Could not unregister Callback; '$gid' is a Special Group");

			return false;			
		}

		if($this->callback_exists($gid, $cid)){
			unset($this->myCallbacks[$gid][$cid]);
			return true;
		}else{
			$this->register_error("Could not unregister Callback; Callback '$gid.$cid' doesn't exist");
		}

		return false;
	}


	/**
	 * Remove a variable
	 * 
	 * Unregister and store a variable with the object assuming it already
	 * exist.
	 *
	 * @param string $vid Variable ID
	 * @return boolean
	*/
	function unregister_variable($vid){
		//ensure that all id's are safe and clean
		$vid = goat::clean_id($vid);

		if($this->variable_exists($vid)){
			unset($this->myCallbacks[GOAT_GROUP_VAR][$vid]);
			return true;
		}else{
			$this->register_error("Could not unregister Variable; Variable '$vid' doesn't exist");
		}

		return false;
	}


	/**
	 * Return a function pointer based on its callback
	 *
	 * Based on a specified callback's ID and group ID, return a pointer to
	 * its function pointer.
	 *
	 * @param string $gid Group ID
	 * @param string $cid Callback ID
	 * @access private
	 * @return string
	*/
	function get_callback($gid, $cid){
		if($this->callback_exists($gid, $cid)){
			return $this->myCallbacks[$gid][$cid];
		}else{
			$this->register_error("Could not retrieve Callback; Callback '$gid.$cid' doesn't exist");
			return "";
		}
	}


	/**
	 * Return a variable
	 *
	 * Based on a specified variable Id, return a value
	 *
	 * @param string $vid Variable ID
	 * @access private
	 * @return string
	*/
	function get_variable($vid){
		if($this->variable_exists($vid)){
			return $this->myCallbacks[GOAT_GROUP_VAR][$vid];
		}else{
			$this->register_error("Could not retrieve Variable; Variable '$vid' doesn't exist");
			return "";
		}
	}


	/**
	 * Return the translated value of a callback variable
	 *
	 * Based on the formatting of the argument passed via the callback, cast it
	 * as a certain type. For example, if the argument is in quotations, it is
	 * interperated as being a string and is returned with its quotations
	 * truncated; if the argument is the string "true" or "false" they are
	 * assumed to be boolean and are translated to their respective values;
	 * if the value is a numeric value, float or integer, return a numeric
	 * value. If the type is a string but doesn't match any particular
	 * formatting, default to a string and evoke an error message.
	 *
	 * @param string $arg callback argument
	 * @access private
	 * @return mixed
	*/
	function translate_arg($arg){
		if(is_string($arg)){
			$arg = trim($arg);

			//of type string
			if(
				(substr($arg, 0, 1) == "\"" && substr($arg, -1, 1) == "\"") ||
				(substr($arg, 0, 1) == "'" && substr($arg, -1, 1) == "'")
			){
				return substr($arg, 1, -1); //return just the contents of the
				                            //quotation
			}

			//of type null
			if(strtolower($arg) == "null"){
				return null; //return a null value
			}

			//of type boolean
			if(strtolower($arg) == "true" || strtolower($arg) == "false"){
				return (strtolower($arg) == "true"); //return a boolean value
			}

			if(is_numeric($arg)){
				//check we we have a float, if its not, its an int, either way
				//return a numeric value
				return (strstr($arg, ".") ? floatval($arg) : intval($arg));
			}

			//if we reach this point, the argument formatting doesn't compute
			//default to a string, report an error, and keep going.

			$this->register_error(
				"Could not translate argument; Invalid argument: '$arg' (Defaulting to type String)"
			);
		}

		return $arg; //default because the value is already a primitive
	}


	/**
	 * Parse an argument string for values
	 *
	 * Parse a comma seperated string containing callback arguments.
	 * This function parses the string by searching for quotation marks,
	 * stores off any non-string arguments before the string, removes them from
	 * the front of the string, stores the string, and removes the string from
	 * the front of the string. Rinse wash, repeat.
	 *
	 * @param string $argStr Argument string
	 * @access private
	 * @return array
	 */
	function parse_arg_str($argStr){
		//This is the meat of GOaT... Abandon all hope, ye who enter here

    	$orgStr = $argStr;
    	$argStr    = trim($argStr);
    
    	if($argStr == ""){
    		return array();
    	}

    	//no quotes, just numeric or boolean values, no need to do advanced
		//parsing
    	if(!preg_match("/'|\"/", $argStr)){
    		$args = explode(",", $argStr);
    	}else{
            $args = array(); //arguments captured
            $i    = 0;       //iteration limiter counter

    		//run until we reach our iteration limit, or we jump out by
    		//completing the parsing
    		while($i < GOAT_MAX_ARGS){
    			//what quotation type are we handling?
    			$quoteChr = (
    				(strpos($argStr, "'") === true && strpos($argStr, "\"") !== false) ?
    				(strpos($argStr, "'") < strpos($argStr, "\"") ? "'" : "\"")        :
    				(strpos($argStr, "'") === false            ? "\"" : "'")
    			);

    			$openQuotePos = strpos($argStr, $quoteChr);

    			//if there are arguments before the string
    			if($openQuotePos != 0){
    				/*
    				  logically, if the open quotation mark isn't the first
					  character using the logic of all arguments being seperated
					  by commas, the location of the opening quotation mark MUST
					  be at least str[2]
    				*/
    				if($openQuotePos <= 1){
    					$this->register_error(
							"Could not parse arguments; Invalid quotation character placement ($orgStr)"
						);
    					return array();
    				}

    				$temp = trim(substr($argStr, 0, $openQuotePos));

    				if(substr($temp, -1, 1) != ","){
    					$this->register_error(
							"Could not parse arguments; Empty argument before string ($orgStr)"
						);
    				}

    				$args = array_merge(
    					$args,
    					explode(",", substr($temp, 0, -1))
    				);
    				$argStr = substr($argStr, $openQuotePos); //remove the front
					                                    //arguments
    			}

    			//NOTE: openQuotePos and closeQuoteChr have nothing to do with
    			//eachother other than self-documenting namesake 
    			$closeQuotePos = strpos($argStr, $quoteChr, 1);

    			//unterminated string
    			if($closeQuotePos === false){
    				$this->register_error(
						"Could not parse arguments; Unterminated string ($orgStr)"
					);
    				return array();
    			}

    			$j = 0; //iteration counter
    			//make sure our closing quotation isn't just an escape char
    			while($argStr{$closeQuotePos-1} == "\\" && $j < GOAT_LOOP_LIMIT){
					for($i = 2; $closeQuotePos-$i >= 0; $i++){
    					//if there are the right number of escape chars
    					//to negate a \'
						if($argStr{$closeQuotePos-$i} != "\\"){
							break (($i+1) % 2 == 0 ? 2 : 1);
    					}
    				}
					$closeQuotePos = strpos($argStr, $quoteChr, $closeQuotePos+1);
    				$j++;
    				if($j == GOAT_LOOP_LIMIT){
    					$this->register_error(
    						"Could not parse arguments; Unterminated string (too many escape quotations) ($orgStr)"
    					);
    				}
    			}

    			$args[] = substr($argStr, 0, $closeQuotePos+1);

    			$argStr = ltrim(substr($argStr, $closeQuotePos+1)); //remove string

    			//nothing left in the string, jump out
    			if(trim($argStr) == ""){
    				break;
    			}

    			if($argStr{0} != ","){
    				$this->register_error(
						"Could not parse arguments; Empty argument after string ($orgStr)"
					);
    				return array();
    			}
    			$argStr = ltrim(substr($argStr, 1));

                //if at the end of the iteration there are no more quotations, take
    			//the shortcut out
    			if(!preg_match("/'|\"/", $argStr)){
    				$args = array_merge($args, explode(",", $argStr));
    				break;
    			}

    			$i++; //count as an iteration
    		}
    	}

    	//make sure we don't have an illegal argument (missing an arument ang just
    	//have an emprty string, eg: 10,2,,
    	foreach($args as $key => $value){
    		$args[$key] = trim($value);
    		if(trim($value) == ""){
    			$this->register_error(
					"Could not parse arguments; Empty argument (Post Parsing) ($orgStr)"
				);
    			return array();
    		}
    	}

    	return $args;
	}


	/**
	 * Indent lines in output
	 * 
	 * Indent every line AFTER the first line in a GOaT result tabs times, using
	 * the string stored in the member variable myTab.
	 * 
	 * @param string subject String to be indented
	 * @param integer tabs number of tabs to prepend to each line
	 * @access public
	 */
	function indent_output($subject, $tabs){
		$indentation = str_repeat($this->myTab, $tabs); //tabs
		$lines       = explode("\n", $subject);         //every line

		//indent all lines that aren't empty, and aren't the first line
		for($i = 0; $i < count($lines); $i++){
			if($lines[$i] != "" && $i != 0){
				$lines[$i] = $indentation.$lines[$i];
			}
		}

		return implode("\n", $lines);	
	}



	/**
	 * Get the return value of callback
	 *
	 * Get the return value of a callback passed to the function via a regular
	 * expression match from goat.parse()
	 *
	 * @param array $matches regular expression matching captures
	 * @access private
	 * @return string
	*/
	function handle_callback($matches){
		//if we have too few captures, jump out and report an error
		if(count($matches) < 4){
			$this->register_error("Unable to handle callback; Too few callback captures.");
			return "";
		}

		//callback group id
		$gid      = goat::clean_id($matches[GOAT_KEY_CAPTURES_GROUP]);
		//callback id
		$cid      = goat::clean_id($matches[GOAT_KEY_CAPTURES_CALLBACK]);
		//callback arguments (un translated)
		$args_str = $matches[GOAT_KEY_CAPTURES_ARGS];
		//number of tabs to prepend each line

		//number of tabs used
		//default or specified by tab=x
		$tabs     = (
			count($matches) > GOAT_KEY_CAPTURES_TABS_CALLBACK ?
			$matches[GOAT_KEY_CAPTURES_TABS_CALLBACK]         :
			$this->myTabCount
		);

		$args = (
			$args_str != ""                 ?
			goat::parse_arg_str($args_str) :
			array()
		);

		foreach($args as $key => $value){
			$args[$key] = goat::translate_arg($value);
		}

		if($gid == GOAT_GROUP_PREF){
			switch($cid){
				case GOAT_CALLBACK_PREF_TAB:
					if(count($args) == 1){
						$this->set_tab($args[0]);
					}
					break;
				case GOAT_CALLBACK_PREF_TAB_COUNT:
					if(count($args) == 1){
						$this->set_tab_count($args[0]);
					}
					break;
				default:
					$this->register_error("Could not handle Callback; Invalid goat preference ($cid)");
			}
			return "";
		}elseif($gid == GOAT_GROUP_VAR){
			if($this->variable_exists($cid)){
				return $this->get_variable($cid);
			}else{
				$this->register_error("Could not handle Variable; Variable '$cid' doesn't exist");
				return "";
			}
		}

		if($this->callback_exists($gid, $cid)){
			if(function_exists($this->get_callback($gid, $cid))){
				/* error repression!! */
				$output = @call_user_func_array(
					$this->get_callback($gid, $cid),
					$args
				);

				//if we should add tabs, do it
				if($tabs > 0){
					$output = $this->indent_output($output, $tabs);
				}

				return $output; //return our callbacks output

			}else{
				$this->register_error(
					"Could not handle Callback; Callback '$gid.$cid' points to a non-existant function"
				);
			}
		}else{
			$this->register_error("Could not handle Callback; Callback '$gid.$cid' doesn't exist");
		}

		return "";
	}


	/**
	 * Get the value of a variable
	 *
	 * Get the value of a variable request passed to the function via a regular
	 * expression match from goat.parse()
	 *
	 * @param array $matches regular expression matching captures
	 * @access private
	 * @return string
	*/
	function handle_variable($matches){
		//if we have too few captures, jump out and report an error
		if(count($matches) < 2){
			$this->register_error("Unable to handle variable; Too few callback captures.");
			return "";
		}

		$vid = goat::clean_id($matches[GOAT_KEY_CAPTURES_VARIABLE]);
		//number of tabs used
		//default or specified by tab=x
		$tabs     = (
			count($matches) > GOAT_KEY_CAPTURES_TABS_VARIABLE ?
			$matches[GOAT_KEY_CAPTURES_TABS_VARIABLE]         :
			$this->myTabCount
		);

		if($this->variable_exists($vid)){
			$output = $this->get_variable($vid);

			//if we should add tabs, do it
			if($tabs > 0){
				$output = $this->indent_output($output, $tabs);
			}
			return $output;
		}else{
			$this->register_error("Could not handle Variable; Variable '$vid' doesn't exist");
			return "";
		}
	}


	function parse_using_handle($subject, $handle, $exp_type){
		$i   = 0;
		$exp = $this->build_exp($exp_type); //begin by parsing for the default
		                                    //expression type
		$tab      = $this->myTab;      //current tab string
		$tabCount = $this->myTabCount; //current tab count

		//while there is still a string to parse, and we are within the
		//iteration limit
		while(
			preg_match($exp, $subject)                         &&
			(GOAT_LOOP_LIMIT == -1 || $i < GOAT_LOOP_LIMIT)
		){
			$subject = preg_replace_callback(
				$exp,
				array(
					$this,
					$handle
				),
				$subject
			);

			$i++;
		}

		//if we are running in strict mode... reset the tab settings
		if($this->myStrict){
			$this->set_tab($tab);            //reset tab string
			$this->set_tab_count($tabCount); //reset tab count
		}

		return $subject;

	}




	/**
	 * Parse a template for callbacks
	 *
	 * Parses a passed template string for any callbacks that may exist within
	 * the string. Depending on how the function is called, it will either parse
	 * just one type of callback tag, or both.
	 *
	 * @param string $subject Template to be parsed
	 * @param numeric $exp_type What type of callback tag to parse for (See callback tag types). Default GOAT_EXPTYPE_DEFAULT
	 * @param boolean $parse_all Will the parsing of the string run against all possible callback tag types. Default true
	 * @return string
	*/
	function parse($subject, $exp_type = GOAT_EXPTYPE_DEFAULT, $parse_ext = true){
		$subject = $this->parse_using_handle($subject, "handle_variable", GOAT_EXPTYPE_VAR);
		if($parse_ext){
			$subject = $this->parse_using_handle(
				$subject, "handle_variable", GOAT_EXPTYPE_VAR_EXT
			);
		}

		$subject = $this->parse_using_handle($subject, "handle_callback", GOAT_EXPTYPE_CALLBACK);
		if($parse_ext){
			$subject = $this->parse_using_handle(
				$subject, "handle_callback", GOAT_EXPTYPE_CALLBACK_EXT
			);
		}

		return $subject;
	}	
}



//REV3:16


/**#@-*/

?>
