<?php
/**
 * Main photos API include
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 *
 * @version 0.1.1 20080317 JJ
 */


define('PATH_UPLOAD_PHOTO',   $_SERVER['DOCUMENT_ROOT'] .'/admin/var/upload/');

// Used for jpeg and png compression when saving resized files

// imagejpeg compression paramter is 0-100
define('PHOTOS_IMAGE_RESIZE_QUALITY_JPEG',   90);

// imagepng compression paramter is 0-9
define('PHOTOS_IMAGE_RESIZE_QUALITY_PNG',    9);


define('PHOTOS_SIZE_FULL_WIDTH_LIMIT',   600);
define('PHOTOS_SIZE_FULL_HEIGHT_LIMIT',  432);

define('PHOTOS_SIZE_THUMB_WIDTH_LIMIT',  114);
define('PHOTOS_SIZE_THUMB_HEIGHT_LIMIT', 89);

define('PHOTOS_SIZE_THUMB',              0);
define('PHOTOS_SIZE_AVATAR',             1);
define('PHOTOS_SIZE_FULL',               2);


require_once(PATH_MODULES.'hlocator/photos/validate.php');
require_once(PATH_MODULES.'hlocator/photos/getter.php');
require_once(PATH_MODULES.'hlocator/photos/setter.php');

?>