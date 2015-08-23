<?php

/* Rewrite variables */
	Gobe_safeDefine("GOBE_QUERY_PATH",   "--gobe-path");
	Gobe_safeDefine("GOBE_QUERY_STATUS", "--gobe-status");
	

/* Default paths */
	Gobe_safeDefine("GOBE_WEB_ROOT", "/");
	Gobe_safeDefine("GOBE_DOC_ROOT", realpath($_SERVER['DOCUMENT_ROOT'] . GOBE_WEB_ROOT) . "/");
	
	// General (accessed via file system and web)
		Gobe_safeDefine('GOBE_PATH_TMP' , 'tmp/');
		Gobe_safeDefine('GOBE_PATH_RESOURCES', 'resources/');
	
	// Web paths
		# Templates
		Gobe_safeDefine('GOBE_PATH_TEMPLATES'       , GOBE_PATH_RESOURCES . 'templates/');
		Gobe_safeDefine('GOBE_PATH_TEMPLATES_ERRORS', GOBE_PATH_TEMPLATES . 'errors/');
		
	// File system paths
		# Modules
		Gobe_safeDefine('GOBE_PATH_ENGINE' , GOBE_DOC_ROOT . 'gobe/');
		Gobe_safeDefine('GOBE_PATH_MODULES', GOBE_DOC_ROOT . 'modules/');
		
		# Templates
		Gobe_safeDefine('GOBE_PATH_TEMPLATES_LAYOUTS', GOBE_DOC_ROOT . GOBE_PATH_TEMPLATES . 'layouts/');
	
		
/* Default environment settings */
	Gobe_safeDefine("GOBE_ENV_LIVE", true);
	
/* Module settings */
	Gobe_safeDefine("GOBE_MODULES_DEFAULT", "session, paths, web.page");
	
/* Scripting settings */
	Gobe_safeDefine("GOBE_SCRIPTING_EXTENSION" , "php");
	Gobe_safeDefine("GOBE_SCRIPTING_INITIALIZE", "init");
	Gobe_safeDefine("GOBE_SCRIPTING_CASCADE"   , "cascade");
	Gobe_safeDefine("GOBE_SCRIPTING_LOCAL"     , "local");
	Gobe_safeDefine("GOBE_SCRIPTING_CLEANUP"   , "cleanup");
	
/* Layout & content settings */
	Gobe_safeDefine("GOBE_DEFAULT_INDEX"     , "index.html");
	Gobe_safeDefine("GOBE_LAYOUT_DEFAULT"    , "default.html");
	Gobe_safeDefine("GOBE_LAYOUT_CONTENT_VAR", "main-content");
	
/* Parser settings */
	Gobe_safeDefine("GOBE_PARSER", "Goat");
	
/* Timezone settings */
	Gobe_safeDefine("GOBE_DEFAULT_TIMEZONE", "America/Los_Angeles");
	
/* Error reporting and logging */
	Gobe_safeDefine("GOBE_ERROR_REPORTING", !GOBE_ENV_LIVE);
	Gobe_safeDefine("GOBE_ERROR_LOGGING"  , true);

/* Legacy support */
	Gobe_safeDefine("GOBE_LEGACY_SUPPORT",  true);