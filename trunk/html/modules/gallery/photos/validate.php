<?php
/**
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @version 0.0.8a 20071108 JJ
 */

global $VALID_FIELDS_TABLE_PHOTO;


$VALID_FIELDS_TABLE_PHOTO = array(
	'*'          => array(                                         ),
	'photo_id'   => array(PDO::PARAM_INT                           ),
	'type'       => array(PDO::PARAM_INT                           ),
	'user_id'    => array(PDO::PARAM_INT                           ),
	'posted'     => array(PDO::PARAM_STR, 20, 'VALIDATE_FORMAT_TS' ),
	'caption'    => array(PDO::PARAM_STR, 255                      ),
	'thumbnail'  => array(PDO::PARAM_LOB                           ),
	'fullsize'   => array(PDO::PARAM_LOB                           ),
	'twidth'     => array(PDO::PARAM_INT                           )
);



/**
 * Validates an image file, its type, and provides image info returned as a reference. The file must reside within PATH_UPLOAD_PHOTO.
 *
 * @param string $filename The filename (just name and extension, no path) of the image.
 * @param array $image_info An array that will store the return of getimageinfo.
 *
 * @return array a[0] => bool: True if the file is valid, false otherwise; a[1] => string: Any applicable error message.
 */
function photos_valid_file($filename, &$image_info=null) {
	$filename = PATH_UPLOAD_PHOTO . $filename;

	// Make sure the file exists
	if ( !file_exists($filename) ) {
//TODO: log this error as a system error
		return array(false, 'The file could not be found.');
	}

	// Make sure the file is readable
	if ( !is_readable($filename) ) {
//TODO: log this error as a system error
		return array(false, 'The file could not be opened.');
	}

	// Make sure the file is where we expect it
	if ( str_replace('/','\\', strtolower(realpath($filename))) != str_replace('/','\\', strtolower($filename)) ) {
//TODO: loog this error as a malicious user error
		return array(false, 'File could not be found.  Your IP and user ID have been logged for review.');
	}

	// Get the image size and mime-type
	$image_info = getimagesize($filename);

	// Validate the image type
	if ( !photos_valid_type($image_info[2]) ) {
		return array(false, 'Only jpeg, png and gif image formats are allow.');
	}

	return array(true, null);
}



/**
 * Whether or not an image is a valid type.
 *
 * @param int $mime_code The mime code of the image that corresponds to PHP's IMAGETYPE_XXX define's.
 *
 * @return bool True if the image type is allowed, false otherwise.
 */
function photos_valid_type($mime_code) {
	switch ( $mime_code ) {
		case IMAGETYPE_GIF:
		case IMAGETYPE_JPEG:
		case IMAGETYPE_PNG:
			return true;
	}

	return false;
}



/**
 * Validates column name and data for the photos table
 *
 * @param array $pairs An associative array where the keys are the table's columns names and the values are those columns' data.
 * @param bool $hasValues True: validate the values of the pairs; False: just validate the column names.
 *
 * @return array An empty array if there are no errors or an array of two key arrays if there are.
 *
 * @see db_validate_table_input
 */
function photos_validate_table_input($pairs, $hasValues=true) {
	global $VALID_FIELDS_TABLE_PHOTO;
	return db_validate_table_input($pairs, $VALID_FIELDS_TABLE_PHOTO, $hasValues);
}

?>