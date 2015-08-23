<?php
/**
 * @abstract Common functions and classes used site wide.
 *
 * @author Justin Johnson <johnsonj>
 * @version 2.3.1 20080430 JJ
 * @version 2.1.0 20080421 JJ
 * @version 2.0.0 20080416 JJ
 * @version 1.7.0 20080407 JJ
 * @version 1.6.7 20080310 JJ
 * @version 1.6.6 20080219 JJ
 * @version 1.6.5 20080217 JJ
 * @version 1.6.0 20080215 JJ
 * @version 1.4.0 20071126 JJ
 *
 * @package zk.stdlib
 */


class ExceptionList extends Exception {
	public function __construct($exceptions) {
		$this->message = is_array($exceptions) ? $exceptions : array($exceptions);
		$this->code    = null;
	}
};


class DatabaseException extends Exception {
	public function __construct($messsage, $code) {
		$this->message = $message;
		$this->code    = $code;
	}
}




/**
 * A case insensitive implementatio of PHP's array_key_exists.
 *
 * @param mixed $needle The array key to search for.
 * @param array $haystack The array to search for
 */
function array_key_existsi($needle, $haystack) {
	$needle = strtolower($needle);
	
	foreach( $haystack as $key=>$value ) {
		if ( strtolower($key) == $needle ) {
			return true;
		}
	}
	
	return false;
}



/**
 * Checks to see whether a row/rows exists with the passed data in a given table
 *
 * @param $sql_obj SQLWrap The SQLWrap object to use to access the database.
 * @param $table string The name of the table query.
 * @param $column string/array The column name(s) to query.
 * @param $data string/array The data to check for and the comparison operator to use (defaults to '=').
 * @param $conditional_join string The conditional operator to link multiple conditions with (e.g.: 'AND', 'OR') (defaults to 'AND').
 *
 * @return boolean True if a row with such specified data exists, false otherwise.
 *
 * @throws DatabaseException
 *
 * @see gobe.database.sqlwrap
 */
function column_data_exists($sql_obj, $table, $column, $data, $conditional_join='AND') {
	$conditions = NULL;

	// Multiple columns/data to match
	if ( is_array($column) ) {
		/* As much error handling as we can do.  This will only save the exception through
		 * if column has only 1 index
		 */
		if ( !is_array($data) ) {
			$data = array($data);
		}

		// Flexiblity for assocative arrays
		$column_keys = array_keys($column);
		$data_keys   = array_keys($data);
		$limit       = count($column_keys);
		$conditions  = array();

		// Make sure we have as much data as we have columns
		if ( $limit != count($data_keys) ) {
			throw new Exception('Column-data mis-match', INVALID_DATA);
		}

		// Create the condition pieces
		$col_value = $col_condition = $col = NULL;
		for ($i=0; $i<$limit; $i++) {
			$col = $data[$data_keys[$i]];

			if ( is_array($col) ) {
				// If the column value is an array, look for a condition operator
				$col_condition = isset($col['condition']) ? $col['condition'] : '=';
				// And a column value
				$col_value     = isset($col['value'])     ? $col['value']     : $col;
			}
			else {
				// Column data is not an array; business as normal
				$col_condition = '=';
				$col_value     = $col;
			}

			// Create the condition for this column
			$conditions[] = $column[$column_keys[$i]] . $col_condition. "'" .
								mysql_real_escape_string($col_value, $sql_obj->resource()). "'";
		}

		// Wham, bam, thank you ma'am
		$conditions = implode(' ' .$conditional_join. ' ' , $conditions);
	}
	// Single column/data to match
	else {
		$conditions = $column . "='" .mysql_real_escape_string($data, $sql_obj->resource()). "'";
	}

	$response = $sql_obj->query(
		'SELECT COUNT(*) AS c ' .
		'FROM ' . $table .
		' WHERE ' . $conditions,
		ERROR_DATABASE_SELECT
	);

	return $response[0]['c'] != 0;
}


function database_touch($sql_obj, $table, $columns, $row_id, $id_col=false) {
	$now = time();

	$table = str_replace('`', '', $table);

	if ( !is_array($columns) ) {
		$columns = array($columns);
	}

	foreach ( $columns as $index=>$value ) {
		$columns[$index] = '`' .mysql_escape_String($value). '`=' . $now;
	}


	$id_col = empty($id_col)
				? '`' . mysql_real_escape_string($table,  $sql_obj->resource()) . '_id`'
				: '`' . mysql_real_escape_string($id_col, $sql_obj->resource()) . '`';

	// Make sure table name is `table`
	$table  = '`' . mysql_real_escape_string($table, $sql_obj->resource()) . '`';

	$sql_obj->query(
		'UPDATE ' .$table . ' SET ' .
			implode(',', $columns) .
		' WHERE ' . $id_col . '=' . (int)$row_id,
		ERROR_DATABASE_UPDATE
	);
}


function zipcode_to_state($zip) {
	global $_sql;
	
	try {
		establish_global_sqlwrap($_sql);

		$response = $_sql->query(
			'SELECT state FROM ' . DATABASE_TABLE_ZIPCODE .
			' WHERE zipcode=' . (int)$zip,
			ERROR_DATABASE_UPDATE
		);

		// No state for that zip?
		if ( empty($response)) {
			throw new Exception('Zip code `' .$zip. '` does not exist in the zip code table.', ERROR_MISSING_DATA);
		}
	}
	catch (DatabaseException $e) {
		error_log_and_rethrow($e, ERROR_LOG_DATABASE);
	}

	// State exists, return it.
	return $response[0]['state'];
}




function upload_is_image($upload) {
	if ( !file_exists($upload['tmp_name']) ) {
		return false;
	}
	elseif ( function_exists('exif_imagetype') ) {
		if ( ($type = exif_imagetype($upload['tmp_name'])) === false ||
			!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))
		) {
			return false;
		}
	}
	else {
		$mime_parts = split('/', strtolower($upload['type']));

		if (
			!isset($mime_parts[0]) ||
			!isset($mime_parts[1]) ||
			$mime_parts[0] != 'image' ||
			!in_array($mime_parts[1], array('jpeg', 'jpg', 'gif', 'png'))
		) {
			return false;
		}
	}

	return true;
}


function image_valid_type($image, $types=array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) {
	if ( function_exists('exif_imagetype') ) {
		if ( ($type = exif_imagetype($image)) === false ||
			!in_array($type, $types)
		) {
			var_dump($type);
			return false;
		}

		return true;
	}

	return false;
}


/**
 * @abstract Gets an image's typical extension (string) for its image type as define by exif_imagetype.  Works only for
 * typical web image formats (gif, jpg, and png).
 *
 * @param string $imagePath The path to a given image.
 *
 * @return mixed Returns a string representation of the image's type/extension if it is a valid type; otherwise, returns false.
 */
function extension_from_mime($imagePath) {
	// Currently only supports image files
	switch ( exif_imagetype($imagePath) ) {
		case IMAGETYPE_GIF:
			return 'gif';

		case IMAGETYPE_JPEG:
			return 'jpg';

		case IMAGETYPE_PNG:
			return 'png';
	}

	return false;
}




function setGoatArrayVariables(&$goat, $data, $keys, $passthru='stripslashes', $method='register_variable') {
	foreach ( $keys as $k ) {
		if ( isset($data[$k]) ) {
			$goat->$method($k, $passthru($data[$k]));
		}
	}
}

