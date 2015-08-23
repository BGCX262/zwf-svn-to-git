<?php
/**
 * Configurable options that are dependant upon the local environment and site needs. Should
 * include all credentials.
 *
 * @author Justin Johnson <justin@booleangate.org>
 *
 * @package gobe.config.local
 */


/* Install location */
	define('DEFAULT_SITE_TITLE', 'Gobe 3!');
	define('DOMAIN_WEB'        , 'gobe3.local');       // eg: www.zebrakick.com
	define('DOMAIN_EMAIL'      , 'gobe3.local');       // eg: zebrakick.com
	
	define('GOBE_INSTALL_DIR'  , '/');
	

/* Environment variables */
	define("GOBE_ENV_LIVE", in_array($_SERVER['HTTP_HOST'], array(DOMAIN_WEB, DOMAIN_EMAIL)));

	
/* Session */
	define('SESSION_TIMEOUT', 480); // 8 hours (in minutes)


/* Email address */
	define('EMAIL_MAINTAINER', 'support@' . DOMAIN_EMAIL);
	define('EMAIL_NOREPLY'   , 'no-reply@' . DOMAIN_EMAIL);
	

/* Database */
	define('DB_HOST'         , isset($_ENV['DB_SERVER']) ? $_ENV['DB_SERVER'] : 'localhost');
	define('DB_USERNAME'     , 'gobe');
	define('DB_PASSWORD'     , '####');
	define('DB_DATABASE'     , 'gobe');
	define('DB_DRIVER'       , 'mysql');
	define('DB_TB_PREPEND'   , '`');

	
/* Default CSS attributes */
	define("DEFAULT_CSS_TITLE", "style");
	define("DEFAULT_CSS_MEDIA", "screen");


/* Email routing */
	define("MAIL_HOST",     "smtp.gmail.com");
	define("MAIL_PORT",     465);
	define("MAIL_USER",     "gobe3@gmail.com");
	define("MAIL_PASSWORD", "########");
	
	define("MAIL_SENDER_AUTO",    EMAIL_NOREPLY);
	define("MAIL_SENDER_NOREPLY", EMAIL_NOREPLY);
	define("MAIL_SENDER_NOREPLY_NAME", "(no-reply)");
	