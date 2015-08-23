<?php
/**
 * @abstract Main configuration bootstrap.
 *
 * @author Justin Johnson <justin@booleangate.org>
 * @version 3.0.0 20100208 <johnsonj>
 *
 * @package gobe.config.boot
 */


function Gobe_safeDefine($constant, $value) {
	if ( !defined($constant) ) {
		define($constant, $value);
	}
}


/* Install specific configurations */
	require_once(GOBE_PATH_CONFIG . "local-install.php");


/* Domain and install location */
	
	define('SITE_PROTOCOL'    , (isset($_SERVER['SSL_CIPHER']) || isset($_SERVER['HTTPS'])) ? 'https' : 'http');
	define('SITE_BASEURL'     , SITE_PROTOCOL.'://'.DOMAIN_WEB.GOBE_INSTALL_DIR);


/* Miscellaneous */
	define('STD_SALT'             , '$23cmdkf*&32c_`$');
	define('SESSIONN_LIMITER'     , 'private , must-revalidate');


/* Standard date formats */
	define('STD_DATE_COMPACT'     , 'm/d/Y');
	define('STD_DATE_SHORT'       , 'M d , Y');
	define('STD_DATE_LONG'        , 'F d , Y');

	define('STD_TIME_MILITARY'    , 'H:i');
	define('STD_TIME_CIVIL'       , 'h:i a');

	define('STD_DATETIME_COMPACT' , 'm/d/Y h:i a');
	define('STD_DATETIME_SHORT'   , 'M d , Y H:i');
	define('STD_DATETIME_LONG'    , 'F d , Y H:i');


/* Uploads */
	// For file uploads in PHP <5.2.0
	if ( !defined('UPLOAD_ERR_EXTENSION') ) {
		define('UPLOAD_ERR_EXTENSION' , -6253);
	}


/* Additional configurations and GOBE overrides */
	require_once(GOBE_PATH_CONFIG . "error.php");
	require_once(GOBE_PATH_CONFIG . "database.php");
	
/* Gobe Configuration */
	require_once(GOBE_PATH_CONFIG . "gobe.php");
	
/* In this configuration, paths are dependant on gobe, but this can be changed */
	require_once(GOBE_PATH_CONFIG . "paths.php");
	
/* If legacy support is turned on */
if ( GOBE_LEGACY_SUPPORT ) {
	require_once(GOBE_PATH_CONFIG . "legacy.php");
}

/* Load module configuration last so that it has complete access to everything else */
	require_once(GOBE_PATH_CONFIG . "modules.php");