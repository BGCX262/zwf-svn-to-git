<?php
/**
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @author Andrew Murphy andrew.ap.murphy@gmail.com
 * @version 0.1.3a 20071108 JJ
 */


function photos_get_user_photos($user_id, $columns=null, $where=null) {
	//TODO: does not check for injections in fields
	static $queries = array();
	
	if ( !is_array($columns) || empty($columns) ) {
		$columns = array('*');
	}
	
	$user_id   = (int)$user_id;
	$strFields = implode(', ', $columns);
	$hash      = md5($strFields.$user_id);

	//we already did this dance... lets replay it.
	if ( array_key_exists($hash, $queries) ) {
		return $queries[$hash];
	}

	// Validate request columns
	$errors = photos_validate_table_input($columns, false);

	// If there were errors, return now
	if ( !empty($errors) ) {
		$queries[$hash] = array(false, $errors);
		return $queries[$hash];
	}

	// No errors, get the requested column data
	$value = array();
	$link = null;
	db_get_resource($link);
	
	$stmt = $link->prepare(
		' SELECT '.
			$strFields.
		' FROM '.
			'`'.SALSA_TABLE_PHOTO.'`'.
		' WHERE '.
			'`user_id` = :user_id '.(!is_null($where) ? 'AND '.$where : ''). //TODO: if we ever use a generated where clause, this needs to change
		' ORDER BY `posted` DESC'
	);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

	// Succeeeeeeeess!
	if ( $stmt->execute() ) {
		$queries[$hash] = $value = array(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
		$stmt->closeCursor();
	}
	// Failure, do no cache
	else {
		$value = array(false, array(VALIDATE_ERR_DB, 'Unable to connect'));
	}

	return $value;
}



/**
 * Gets a specific photo based on its photo ID
 * 
 * @param int $photo_id The ID of the photo to fetch.
 * @param array $columns The column names to retreive (as validated by photos_validate_table_input)
 * 
 * @return array a[0] => bool: the status of the operation; a[1] => mixed: an associative array of row information or an error string
 * 
 * @see photos_validate_table_input
 */
function photos_get_photo($photo_id, $columns) {
	// Validate request columns
	$errors = photos_validate_table_input($columns, false);

	// If there were errors, return now
	if ( !empty($errors) ) {
		return $errors;
	}

	// The columns are good, get the photo
	$link = null;
	db_get_resource($link);
	
	$stmt = $link->prepare(
		'SELECT ' . implode(',', $columns) . ' FROM `' . SALSA_TABLE_PHOTO . '` ' .
		'WHERE photo_id=:photo_id'
	);
	$stmt->bindValue(':photo_id', $photo_id, PDO::PARAM_INT);

	if ( $stmt->execute() ) {
		$value = array(true, $stmt->fetch(PDO::FETCH_ASSOC));
		$stmt->closeCursor();
		return $value;
	}
	
	return array(false, 'Could not get photo.');
}



/**
 * Gets the ID of the user's avatar photo.  0 is the photo ID of the default avatar.
 * 
 * @param int $user_id The user ID for which to get an avatar.
 * 
 * @return int/bool If the user exists, there avatar's photo_id is returned; otherwise, false is returned and zero (0) can be used as their avatar's photo ID. 
 */
function photos_get_user_avatar($user_id) {
	static $avatars = array();

	// Is the avatar ID already cached?
	if ( isset($avatar[$user_id]) ) {
		return $avatar[$user_id];
	}

	// Get the user's avatar
	$response = user_get_info($user_id, array('photo_id'));

	// User data not found
	if ( !$response[0] || !isset($response[1]['photo_id']) ) {
		$avatars[$user_id] = 0;
		return false;
	}

	$avatars[$user_id] = $response[1]['photo_id'];
		
	return $avatars[$user_id];
}



/**
 * Gets the number of photos that belong to a given user
 * 
 * @param int $user_id The ID of the user to get the photo count for.
 * 
 * @return array a[0] => bool: The status of the operation; a[1] => int/array: The number of photos the user has or an array of errors.
 */
function photos_count($user_id) {
	$value = array();
	$link = null;
	db_get_resource($link);

	$stmt = $link->prepare(
		' SELECT COUNT(*) as`photo_count`'.
		' FROM '.
			'`'.SALSA_TABLE_PHOTO.'`'.
		' WHERE '.
			'`user_id` = :user_id'.
		' ORDER BY `posted` ASC'
	);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

	if ( $stmt->execute() ) {
		$value = array(true, $stmt->fetch(PDO::FETCH_ASSOC));

		if ( $value[1] ) {
			$value[1] = $value[1]['photo_count'];
		} else {
			$value[1] = 0;
		}

		$stmt->closeCursor();
	} else {
		$value = array(false, array(VALIDATE_ERR_DB, 'Unable to connect'));
		//do not cache
	}
	
	return $value;
}



/**
 * Gets the appropriate/standard http header for images via the gateway
 * 
 * @param int $image_type A numeric image type as defined by PHP's IMAGETYPE_XXX
 */
function photos_http_header($image_type) {
	$extension = 'ext';
	switch ( $image_type ) {
		case IMAGETYPE_GIF:
			$extension = 'gif';	
			break;
			
		case IMAGETYPE_JPEG:
			$extension = 'jpg';	
			break;
			
		case IMAGETYPE_PNG:
			$extension = 'png';	
			break;
	}
	
	header('Content-Disposition: inline; filename="image.' .$extension. '"');
	header('Content-Type: ' .photos_mime_type($image_type). "\n\n");
}



/**
 * Gets the photos mime-type based on its numeric type code.  Currently, this is just a wrapper
 * for php's image_type_to_mime_type but is done so for flexibility in the future.
 * 
 * @param int $image_type A numeric image type as defined by PHP's IMAGETYPE_XXX
 * 
 * @return string See image_type_to_mime_type
 * 
 * @see image_type_to_mime_type
 */
function photos_mime_type($image_type) {
	return image_type_to_mime_type($image_type);
}



/**
 * Whether or not an image is owned by a particular user
 * 
 * @param int $photo_id The ID of the photo in question.
 * @param int $user_id The ID of the user in question
 * 
 * @param bool Return true if the user owns this photo, false otherwise.
 */
function photos_is_owner($photo_id, $user_id) {
	$photo_info = photos_get_photo($photo_id, array('user_id'));
	return $photo_info[0] && $photo_info[1]['user_id'] == $user_id;
}



/**
 * Gets the width of a given photo.  This is a wrapper to photos_get_photo.
 *  
 * @param int $photo_id The ID of the photo for which to get the width.
 * @param bool $for_thumb If true, returns the minimum of 116 and the image's width; otherwise, returns the image's width.  Defaults to true.
 * @param mixed $default_value The value to return if photos_get_photo returns false.
 * 
 * @return int/mixed $default_value if the call to photos_get_photo failed; or the image's twidth  If $for_thumb is true,
 * then the image width or 116 is returned, whichever is smallest.
 * 
 * @see photos_get_photo.
 */
function photos_get_width($photo_id, $for_thumb=true, $default_value=116) {
	$width = photos_get_photo($photo_id, array('twidth'));

	return !$width[0] || !$width[1]
			? 116
			: ($for_thumb
				? ($width[1] > 116) ? 116 : $width[1]
				: $width[1]
			);
}

?>