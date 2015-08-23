<?php

/**
 * Gobe 2: A complete rewrite of GOBE originally by Andrew Murphy <andrew.ap.murphy@gmail.com>.
 * 
 * All Gobe requests are routed through this page.
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100209 (johnsonj)
 */

define('GOBE_PATH_CONFIG', $_SERVER['DOCUMENT_ROOT'] . '/../config/');

// Load config and custom routes
require_once(GOBE_PATH_CONFIG . "boot.php");

// Load Gobe classes
require_once(GOBE_PATH_ENGINE . "Router.php");
require_once(GOBE_PATH_ENGINE . "Route.php");
require_once(GOBE_PATH_ENGINE . "Request.php");
require_once(GOBE_PATH_ENGINE . "Modules.php");
require_once(GOBE_PATH_ENGINE . "Controller.php");
require_once(GOBE_PATH_ENGINE . "Scripts.php");
require_once(GOBE_PATH_ENGINE . "Parser.php");
require_once(GOBE_PATH_ENGINE . "Gobe.php");

require_once(GOBE_PATH_CONFIG . "routes.php");

if ( GOBE_LEGACY_SUPPORT ) {
	require_once(GOBE_PATH_ENGINE . "legacy.php");
}

// Instantiate GOBE
$gobe = Gobe::getInstance();

// Configure the request
$gobe->setRequest();

// Load GOBE_MODULES_DEFAULT if it is not empty
if ( GOBE_MODULES_DEFAULT != "" ) {
	$gobe->getModules()->register(preg_split("`\s*,\s*`", GOBE_MODULES_DEFAULT));
}

// Configure the parser
$gobe->setLayout();
$gobe->setView();

// Load user scripts
$gobe->loadScripts();

// Load layout and parse template
echo $gobe->loadParser();
