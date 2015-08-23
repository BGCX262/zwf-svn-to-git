<?php
/**
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @version 0.1.8a 20071107 JJ
 */




/**
 * Resizes and adds a photo and its title to the database associated to a user.
 *
 * @param int $listing_id The listing ID of the photo's owner.
 * @param string $filename The path to the image to be resized and added.
 *
 * @return array a[0] => bool: status of the operation; a[1] => int: The ID of the inserted photo if the operation was successful; error code.
 */
function photos_add($listing_id, $filename) {
	// Make sure the photo is valid
	$image_info = array();
	$valid = photos_valid_file($filename, $image_info);

	if ( !$valid[0] ) {
		var_dump($valid);

		return $valid;
	}

	// Validate user ID
	$resp = db_column_data_exists(DB_TABLE_LISTING, 'listing_id', $listing_id);
	if ( !$resp[1] ) {
		return array(false, 'This user does not exist.');
	}


	// Full path to the file
	$file_path = PATH_UPLOAD_PHOTO . $filename;

	// Load the image resources
	$original_rsc = photos_imagecreate($file_path, $image_info[2]);

	// Resize the images and convert them to strings
	$thumb_blob = photos_image_string(photos_resize($original_rsc, PHOTOS_SIZE_THUMB, $image_info[0], $image_info[1]), $image_info[2]);
	$full_blob  = photos_image_string(photos_resize($original_rsc, PHOTOS_SIZE_FULL,  $image_info[0], $image_info[1]), $image_info[2]);


	// Get the height of the thumb
	$nwidth  = PHOTOS_SIZE_THUMB_WIDTH_LIMIT;
	$nheight = PHOTOS_SIZE_THUMB_HEIGHT_LIMIT;
	photos_requires_resize($image_info[0], $image_info[1], $nwidth, $nheight);


	// Pump that 'ish into the database
	$link = null;
	db_get_resource($link);

	$stmt = $link->prepare(
		'INSERT INTO ' . DB_TABLE_PHOTO . ' ' .
		'(listing_id, thumbnail, fullsize) ' .
		'VALUES(:listing_id, :thumbnail, :fullsize)'
	);

	$stmt->bindParam(':listing_id', $listing_id, PDO::PARAM_INT);
	$stmt->bindParam(':thumbnail',  $thumb_blob, PDO::PARAM_LOB);
	$stmt->bindParam(':fullsize',   $full_blob,  PDO::PARAM_LOB);

	// Perform the SQL insert.  If it failed, return now.
	if ( !$stmt->execute() ) {
		var_dump( $stmt->errorInfo());
		return array(false, 'Could not execute query');
	}

	// The insert was performed successfully.
	$stmt->closeCursor();

	return array(true, $link->lastInsertId());
}



/**
 * Removes the photo from the database.
 *
 * @param int $id The ID of the photo to remove, or the user ID for which to remove all photos.
 * @param bool $remove_user_photos If true, the $id is treated as a user's ID and all photos are removed that belong to that user.
 * If false, $id is treated as a photo ID and a single photo is removed. (default: false)
 *
 * @return array See db_delete_row
 *
 * @see db_delete_row
 */
function photos_remove($id) {
	return db_delete_row(DB_TABLE_PHOTO, 'photo_id', $id);
}











/**
 * Gets the an image resource for the provided file.
 *
 * @param string $filename The name of the image file from which to return the resource.
 * @param int $type The image type (should be validated by photos_valid_type).
 *
 * @return gd_resource/bool Returns the image resource if $type is allowed, otherwise returns false.
 *
 * @see photos_valid_type
 */
function photos_imagecreate($filename, $type) {
	return imageCreateFromJPEG($filename);
}



/**
 * Gets a string representation of an image resource.
 *
 * @param gd_resource $rsc The image resource.
 * @param int $type The image type of $rsc.
 *
 * @return string A string representation of $rsc that can be used for datbase storage or output.
 */
function photos_image_string($rsc, $type) {
	$file = tempnam(PATH_UPLOAD_PHOTO, 'upload_');

	if ( $file ) {
		$resp = imagejpeg($rsc, $file, PHOTOS_IMAGE_RESIZE_QUALITY_JPEG);

		// Make sure that the file was written
		if ( $resp ) {
			// Get the file's content and return
			if ( ($resp = file_get_contents($file)) ) {
				unlink($file);
				return $resp;
			}

			// Could not read the temporary file
//TODO: log this error
			return array(false, 'Could not read the temporary file.');
		}
	}

	// Could not create the temporary file in the PATH_UPLOAD_PHOTO directory
//TODO: log this error
	return array(false, 'Could not create temporary file.');
}



/**
 * Resizes an image if it is greater than the dimension limits of its $size
 *
 * @param gd_resource $image_rsc The image resource to resize.
 * @param int $size The size flag (PHOTOS_SIZE_FULL or PHOTOS_SIZE_THUMB).
 * @param int $owidth The width of $image_rsc.
 * @param int $oheight The height of $image_rsc.
 *
 * @return gd_resource/bool The resized $image_rsc or false if there was an error resizing the image.
 */
function photos_resize(&$image_rsc, $size, $owidth, $oheight) {
	$nwidth  = ($size == PHOTOS_SIZE_FULL) ? PHOTOS_SIZE_FULL_WIDTH_LIMIT  : PHOTOS_SIZE_THUMB_WIDTH_LIMIT;
	$nheight = ($size == PHOTOS_SIZE_FULL) ? PHOTOS_SIZE_FULL_HEIGHT_LIMIT : PHOTOS_SIZE_THUMB_HEIGHT_LIMIT;

	// If the image is already smaller than the new dimensions, just return
	if ( !photos_requires_resize($owidth, $oheight, $nwidth, $nheight) ) {
		return $image_rsc;
	}

	// Create a new image
	$new_image_rsc = imageCreateTrueColor($nwidth, $nheight);

	// Resize and return the new image resource (false if there is an error)
	return imagecopyresampled($new_image_rsc, $image_rsc, 0, 0, 0, 0, $nwidth, $nheight, $owidth, $oheight)
		? $new_image_rsc
		: false;
}



/**
 * Whether or not an image needs resizing and its new dimensions.
 *
 * @param int $owidth The original width.
 * @param int $oheight The original height.
 * @param int $nwidth The new width.
 * @param int $nheight The new height or zero (0) for width dependance.
 * @param int $max_height The maximum height
 *
 * @return bool Returns true if the image needs to be resized based on the passed dimensions, false otherwise.
 */
function photos_requires_resize($owidth, $oheight, &$nwidth, &$nheight) {
	// Determine if this image needs to be resized
	$resize = ( $owidth <= $nwidth )
		? $nheight && $oheight > $nheight
		: true;

	if ( $resize ) {
		// Resize height by width percentage
		if ( $owidth > $nwidth ) {
			$oheight *= ($nwidth / $owidth);

			// For height resizing if necessary (must return true)
			return photos_requires_resize($nwidth, $oheight, $nwidth, $nheight) || true;
		}

		// Resize width	by height percentage if greater than height limit
		if ( $nheight && $oheight > $nheight ) {
			$nwidth = $owidth * ($nheight / $oheight);
		}

		return true;
	}

	// No resizing necessary
	$nwidth  = $owidth;
	$nheight = $oheight;
	return false;
}



?>