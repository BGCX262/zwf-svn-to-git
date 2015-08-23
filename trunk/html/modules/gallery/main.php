<?php
/**
 * Gallery config
 *
 * @author Andrew Murphy 
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @version 1.1.0 20080430 JJ
 * @version 1.0.0 20071120 AM
 *
 * @package zk.modules.gallery.main
 */


define('DB_TABLE_LISTING' , '`listing`');
define('DB_TABLE_PHOTO'   , '`photo`');
define('DB_DEFAULT_LIMIT' , 5);

set_path('imageGateway'   , '/bin/photos/');

?>