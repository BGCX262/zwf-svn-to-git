<?php

/**
 * GOAT (GHA Output And Template)
 *
 * System for creating rich template output by defining a series of callbacks
 * to functions that may be accessed from within a template string.
 *
 * @version 2.1.0d 20060501 (Segundo)
 * @author Andrew Murphy <andrew@booleangate.org>
 * @license http://bool-goat.sourceforge.net/license The GOaT Software License
 * @copyright Copyright 2005-2006, The Grey Hat Association
 *
 * @package php.gt.goat
 *
 * @todo Beta testing.
 */

if ( !defined("GOAT_PATH") ) {
	/**
	 * Inclusion path
	 *
	 * Inclusion path for all GOaT source code
	*/
	define("GOAT_PATH", "");
}




//if the user hasn't already defined the default tab character(s) used by goat
if ( !defined("GOAT_DEFAULT_TAB") ) {
	//check to see if have the GHA Toolkit tab character(s) defined
	if ( defined("GT_TAB") ) {
		/**
		 * @ignore
		 */
		define("GOAT_DEFAULT_TAB", GT_TAB); //use them
	} else {
		/**
		 * GOAT default tab character
		 *
		 * Character(s) GOAT by default prepends to the beginning of every line
		 * in the return value of each callback. Used to further make templates
		 * seemless.
		 *
		 * @uses \t
		 * @uses &nbsp;&nbsp;
		 * @uses \t&nbps;\t
		 */
		define("GOAT_DEFAULT_TAB", "\t"); //default value
	}
}


 //if the user hasn't already defined the default tab count used by goat
if ( !defined("GOAT_DEFAULT_TAB_COUNT") ) {
	//check to see if have the GHA Toolkit tab count defined
	if ( defined("GT_TAB_COUNT") ) {
		/**
		 * @ignore
		 */
		define("GOAT_DEFAULT_TAB_COUNT", GT_TAB_COUNT); //use them
	} else {
		/**
		 * GOAT default tab count
		 *
		 * Default number of tabs prepended to the beginning of every line.
		 *
		 * @uses GT_TAB_COUNT (default)
		 */
		define("GOAT_DEFAULT_TAB_COUNT", 0); //default value, do not apply tabs by
		                                     //default
	}
}


 //if the user hasn't already defined the tab character(s) used by goat
if ( !defined("GOAT_MAX_ARGS") ) {
	//check to see if have the GHA Toolkit maximum argument count defined
	if ( defined("GT_MAX_ARGS") ) {
		/**
		 * @ignore
		 */
		define("GOAT_MAX_ARGS", GT_MAX_ARGS);
	} else {
		/**
		 * GOAT Argument count limit
		 *
		 * Maximum number of arguments a goat callback my have
		 *
		 * @uses GT_MAX_ARGS
		 * @uses numeric
		 */
		define("GOAT_MAX_ARGS", 100);
	}
}


//if the user hasn't already defined parsing iteration limit
if ( !defined("GOAT_LOOP_LIMIT") ) {
	//check to see if have the GHA Toolkit iteration limit defined
	if ( defined("GT_LOOP_LIMIT") ) {
		/**
		 * @ignore
		 */
		define("GOAT_LOOP_LIMIT", GT_LOOP_LIMIT);
	} else {
		/**
		 * Iteration count limit
		 *
		 * Numer of times GOAT may parse a given string before it escapes in
		 * order avoid a loop-of-death.
		 *
		 * @since version 1.0b
		 *
		 * @uses -1 Unlimited numer of iterations (not suggested)
		 * @uses GOAT_LOOP_LIMIT > 0
		 * @uses GOAT_LOOP_LIMIT = GT_LOOP_LIMIT (default)
		 * @var numeric
		 */
		define("GOAT_LOOP_LIMIT", 100000); //Good Gord, who would have that many
		                                   //tags?
	}
}


if ( !defined("GOAT_STRICT") ) {
	/**
	 * Default setting for registration restrictions.
	 *
	 * Default setting used by all GOaT objects when initialized to specify
	 * which ruleset, strict or lax, are used when defining and accessing
	 * callbacks. When set to true, the default setting for the ruleset is
	 * strict.
	 *
	 * @var boolean
	 */
	define("GOAT_STRICT", true);
}


/**
 * Array index key for error message
 *
 * Used by the error message array to specify the index of the errors string
 * message.
 *
 * @var integer
 * @version 2.0a (no longer string) (20050325)
 */
define("GOAT_KEY_ERROR_MSG"  , 0);
/**
 * Array index key for error time
 *
 * Used by the error message array to specify the index the time an error
 * occurred.
 *
 * @var integer
 * @version 2.0a (no longer string) (20050325)
 */
define("GOAT_KEY_ERROR_TIME" , 1);


/**
 * Array index key for the callback group id
 *
 * Used by the expression captures array in order to index the callback group id
 * specified by the callback tag.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_GROUP" , 1);
/**
 * Array index key for the variable Id
 *
 * Used by the expression captures array in order to index the variable id
 * specified by the variable tag.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_VARIABLE" , 1);
/**
 * Array index key for the callback id
 *
 * Used by the expression captures array in order to index the callback id
 * specified by the callback tag.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_CALLBACK" , 2);
/**
 * Array index key for the callback arguments
 *
 * Used by the expression captures array in order to index the arguments
 * specified by the callback tag.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_ARGS" , 3);
/**
 * Array index key for number of tabs (legacy; callbacks)
 *
 * Used by the expression captures array in order to index the number of tabs to
 * be prepended to the beginning of each line in a callback string.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_TABS" , 4);
/**
 * Array index key for number of tabs (callbacks)
 *
 * Used by the expression captures array in order to index the number of tabs to
 * be prepended to the beginning of each line in a callback string.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_TABS_CALLBACK" , 4);
/**
 * Array index key for number of tabs (variables)
 *
 * Used by the expression captures array in order to index the number of tabs to
 * be prepended to the beginning of each line in a variable string.
 *
 * @var integer
 */
define("GOAT_KEY_CAPTURES_TABS_VARIABLE" , 2);


/**
 * Parsing regular expression type identifier (Callback Legacy)
 *
 * Default regular expression; [group callback(arguments)];
 *
 * @var integer
 * @version 2.0a (20050325)
 * @since 2.0.0a (20050325)
 */
define("GOAT_EXPTYPE_DEFAULT" , 0);
/**
 * Parsing regular expression type identifier (Callback extended Legacy)
 *
 * Extended argument regular expression; [group callback(arguments) tab=x];
 *
 * @var integer
 */
define("GOAT_EXPTYPE_EXT"     , 1);
/**
 * Parsing regular expression type identifier (Callback)
 *
 * Default regular expression; [group callback(arguments)];
 *
 * @var integer
 * @version 2.0a (20050325)
 * @since 2.0.0a (20050325)
 */
define("GOAT_EXPTYPE_CALLBACK" , 0);
/**
 * Parsing regular expression type identifier (Callback Extended)
 *
 * Extended argument regular expression; [group callback(arguments) tab=x];
 *
 * @var integer
 */
define("GOAT_EXPTYPE_CALLBACK_EXT"     , 1);
/**
 * Parsing regular expression type identifier
 *
 * variable regular expression; [var identifier];
 *
 * @var integer
 */
define("GOAT_EXPTYPE_VAR"     , 2);
/**
 * Parsing regular expression type identifier
 *
 * variable extended regular expression; [var identifier tab=x];
 *
 * @var integer
 */
define("GOAT_EXPTYPE_VAR_EXT"  , GOAT_EXPTYPE_VAR+GOAT_EXPTYPE_EXT);


/**
 * Preference group
 *
 * String defining the group used to set goat preferences
 *
 * @var string
 */
define("GOAT_GROUP_PREF", "goat_pref");


/**
 * Variables Group
 *
 * String defining the group used to access variables
 *
 * @var string
 */
define("GOAT_GROUP_VAR", "var");


/**
 * Callback to modify tab string
 *
 * String defining the callback to modify the string prepended to every new line
 *
 * @var string
 */
define("GOAT_CALLBACK_PREF_TAB", "tab");
/**
 * Callback to modify tab count
 *
 * String defining the callback to modify the default tab count prepended to
 * every new line.
 *
 * @var string
 */
define("GOAT_CALLBACK_PREF_TAB_COUNT", "tab-count");




/**
 * Beginning of all callback regular expressions
 *
 * String at the beginning of all callback regular expressions used by GOAT;
 * contains the contents of the default regular expression, minus the closing of
 * the tag.
 *
 * @todo Add escape for example expressions
 */
define("GOAT_EXP_STD" , "/\\[\\s*([a-z0-9\\-_]+)\\s+([a-z0-9\\-_]*)\\s*\\(\\s*(.*)\\s*\\)");
/**
 * Beginning of all variable regular expressions
 *
 * String at the beginning of all variable regular expressions used by GOAT;
 * contains the contents of the default regular expression, minus the closing of
 * the tag.
 *
 * @todo Add escape for example expressions
 */
define("GOAT_EXP_VAR" , "/\\[\\s*var\\s+([a-z0-9\\-_]+)");
/**
 * Extended piece of a regular expression
 *
 * String added to the middle of all "extended" regular expressions used by
 * GOAT; contains pieces of the expression specifying added arguments
 */
define("GOAT_EXP_EXT" , "\\s+tab\\s*=\\s*([1-9]+[0-9]*|[0-9])");
/**
 * End of all regular expressions
 *
 * String appended to the end of all regular expressions used by GOAT; contains
 * the piece of the tag.
 */
define("GOAT_EXP_END" , "\\s*\\]\\s*;/iU");

/**
 * Beginning of tag template
 *
 * String placed at the beginning of all GOaT tag templates, and parsed in order
 * to generate tag templates.
 */
define("GOAT_TEMPLATE_STD", "[%GROUP% %CALLBACK%(%ARGS%)%EXT%];");
/**
 * Extension of tag template
 *
 * String sometimes placed in the %EXT% section in order to add addition
 * parameters
 */
define("GOAT_TEMPLATE_EXT", " tab=%TAB%");

/**
 * Standard error template
 *
 * Default formatting used for GOaT errors
 */
define("GOAT_TEMPLATE_ERROR_STD", "<span class=\"-goat-error\">GOaT Error (%t): %m</span>");




/**
 * GOAT System Title
 */
define("GOAT_TITLE"         , "GOAT"                                   );
/**
 * GOAT Version
 */
define("GOAT_VERSION"       , "2.1.0d"                                 );
/**
 * Version Nickname
 */
define("GOAT_NICKNAME"      , "Segundo"                                );
/**
 * Version Release date
 */
define("GOAT_LAST_REVISION" , "20060501"                               );
/**
 * GOAT author
 */
define("GOAT_AUTHOR"        , "Andrew Murphy <andrew@booleangate.org>" );






if ( version_compare(phpversion(), "5.0.0", ">=") ) {
	//include php5 branch
	require_once(GOAT_PATH."php5/goat.php5.php");
} else {
	//include php4 branch
	require_once(GOAT_PATH."php4/goat.php4.php");
}

?>