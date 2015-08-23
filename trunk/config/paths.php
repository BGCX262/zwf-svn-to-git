<?php
/**
 * @abstract Overrides for GOBE paths and any other arbitrary path definitions.
 *
 * @author Justin Johnson <justin@booleangate.org>
 *
 * @package gobe.config.paths
 */

// All path's should end in '/'

// RPC services
define('PATH_SERVICES'       , GOBE_WEB_ROOT . 'rpc/services/');
define('PATH_SERVICES_ADMIN' , GOBE_WEB_ROOT . 'admin/' . PATH_SERVICES);

// Templates
define('PATH_TEMPLATES'      , GOBE_DOC_ROOT . GOBE_PATH_TEMPLATES);
define('PATH_TEMPLATES_STUBS', PATH_TEMPLATES . 'stubs/');

// Resources
define('PATH_CSS'            , GOBE_WEB_ROOT . GOBE_PATH_RESOURCES . 'css/');
define('PATH_JAVASCRIPT'     , GOBE_WEB_ROOT . GOBE_PATH_RESOURCES . 'javascript/');
define('PATH_IMAGES'         , GOBE_WEB_ROOT . GOBE_PATH_RESOURCES . 'images/');