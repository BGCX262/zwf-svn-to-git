<?php
/**
 * @abstract Error codes.
 *
 * @author Justin Johnson <justin@booleangate.org>
 *
 * @package gobe.config.error
 */


/* General */
	define('ERR_SUCCESS'            , 0);
	define('ERR_WARN'               , 10);
	define('ERR_FATAL'              , 11);
	define('ERR_USER'               , 20);
	define('ERR_UNKNOWN'            , 21);
	define('ERR_INTERNAL'           , 22);
	define('ERR_DATA_INVALID'       , 30);
	define('ERR_DATA_REQUIRED'      , 31);
	define('ERR_DATA_MALICIOUS'     , 32);


/* Database */
	define('ERR_DB_GENERAL'         , 100);
	define('ERR_DB_CONNECT'         , 101);
	define('ERR_DB_QUERY'           , 102);
	define('ERR_DB_SELECT'          , 103);
	define('ERR_DB_INSERT'          , 104);
	define('ERR_DB_UPDATE'          , 105);
	define('ERR_DB_DELETE'          , 106);


/* File system */
	define('ERR_FILE_GENERAL'       , 200);
	define('ERR_FILE_NO_WRITE'      , 201);
	define('ERR_FILE_NO_READ'       , 202);
	define('ERR_FILE_NO_ACCESS'     , 203);


/* Connection encryption */
	define('ERR_UNSECURE_CONNECTION', 500);

