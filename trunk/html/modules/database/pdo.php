<?php
/**
 * @author Andrew Murphy <andrew.ap.murphy@gmail.com>
 * @author Justin Johnson <justin@booleangate.org>
 * @version 1.0.0a 20071115 AM
 */

//require_once '../../config/database-config.php';

define('VALIDATE_ERR_NO_COLUMN' , 1);
define('VALIDATE_ERR_LENGTH'    , 2);
define('VALIDATE_ERR_MISMATCH'  , 4);
define('VALIDATE_ERR_FORMAT'    , 8);
define('VALIDATE_ERR_DB'        , 16);
define('VALIDATE_ERR_TABLE'     , 32);
define('VALIDATE_ERR_DUPLICATE' , 64);
define('FETCH_NO_REPLY'         , 128);


define('VALIDATE_FORMAT_DATE'  , '/^$|\\d{4}\\-\\d{2}\\-\\d{2}|CURRENT_TIMESTAMP/');
define('VALIDATE_FORMAT_TS'    , '/^$|\\d{4}\\-\\d{2}\\-\\d{2}\s+\\d{2}\\:\\d{2}:\\d{2}|CURRENT_TIMESTAMP/');
define('VALIDATE_FORMAT_TIME'  , '/^$|\\d{2}:\\d{2}:\\d{2}|CURRENT_TIMESTAMP/');
define('VALIDATE_FORMAT_EMAIL' , '/^$|^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$/i');

define('VALIDATE_FORMAT_FLOAT' , '/\d+|\d+\.\d+/i');



function db_get_resource(&$link) {
	static $resource = null;

	if ( is_null($resource) ) {
		try {
			$resource = new PDO(DB_DRIVER.':dbname='.DB_DATABASE.';host='.DB_HOST, DB_USERNAME, DB_PASSWORD);
		}
		catch (PDOException $e) {
			if ( DEBUG_MODE || FORCE_PHP_ERRORS ) {
				echo $e->getMessage(), "<br/><br/>\n";
			}
			
			die('Unable to establish database connection.');
		}
	}

	$link = $resource;
	return true;
}



function db_validate_table_input($pairs, &$validFields, $hasValues = true) {
	$errors = array();
	
	//shortcut
	if ( count($pairs) == 1 && isset($pairs['*']) ) {
		return $errors;
	}

	if ( !$hasValues ) {
		foreach($pairs as $key){
			if(!isset($validFields[$key])){
				$errors[] = array(VALIDATE_ERR_NO_COLUMN, $key);
				continue;
			}
		}
		return $errors;
	}

	foreach ( $pairs as $key => $value ) {
		if ( !isset($validFields[$key]) ) {
			$errors[] = array(VALIDATE_ERR_NO_COLUMN, $key);
			continue;
		}

		//no requirements
		if ( empty($validFields[$key]) ) {
			continue;
		}
		$temp = $validFields[$key];

		//must look like an integer
		if ( $temp[0] == PDO::PARAM_INT && (int)$value != $value ) {
			$errors[] = array(VALIDATE_ERR_MALFROMED, $key);
			continue;
		} else {
			//string

			//length
			if ( isset($temp[1]) && strlen($value) > $temp[1] ) {
				$errors[] = array(VALIDATE_ERR_LENGTH, $key);
				continue;
			}
			
			//formatted
			if ( isset($temp[2]) && !preg_match($temp[2], $value) ) {
				$errors[] = array(VALIDATE_ERR_FORMAT, $key);
				continue;
			}
		}
	}

	return $errors;
}



/**
 * Checks to see if a value exists in a given column in a given table. 
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * 
 * @param string $table The table to operate on.
 * @param string $column The column to compare $value against.
 * @param string $value The value to compare $column with.
 * 
 * @return array a[0] => bool: status of the operation; a[1] => bool: the successfulness of the operation.
 */
function db_column_data_exists($table, $column, $value, $conditional_join='AND') {
	$link = null;
	db_get_resource($link);

	$stmt = $link->prepare(
		' SELECT count(*) AS `c` FROM '.
			'`'.$table.'`'.
		' WHERE '.
			'`'.$column.'` = :column'
	);

	$stmt->bindValue(':column', $value, PDO::PARAM_STR);

	if ( $stmt->execute() ) {
		$response = array(true, $stmt->fetch(PDO::FETCH_ASSOC));
		$stmt->closeCursor();
		$link = null;

		if ( !$response[1] ) {
			$response = array(false, FETCH_NO_REPLY);
		}
		return array(true, $response[1]['c'] != 0);
	}
	
	return array(false, array(VALIDATE_ERR_DB, 'Unable to connect'));
}



/**
 * Deletes a row in a given table.
 *
 * @param string $table The table to operate on.
 * @param string $column The column to compare $value against as a basis for row deletion.
 * @param string $value The value to compare $column with.
 * @param int $type A PDO param type (default: PDO::PARAM_INT).
 * 
 * @return array a[0] => bool: status of the operation; a[1] => int: The number of rows affected by the operation.
 */
function db_delete_row($table, $column, $value, $type=PDO::PARAM_INT) {
	$link = null;
	db_get_resource($link);
	
	$stmt = $link->prepare(
		'DELETE FROM `' . $table . '` WHERE `' . $column . '`=:value'
	);
	$stmt->bindValue(':value',  $value, $type);
	
	if ( $stmt->execute() ) {
		$response = array(true, $stmt->rowCount());
		$stmt->closeCursor();

		return $response;
	}
	
	$link = null;
	return array(false, array(VALIDATE_ERR_DB, 'Unable to connect'));
}



/**
 * Constructs an SQL limit clause.  If only $start_limit is specified, it acts as $count_limit.
 *
 * @param int $startLimit The start number of the limit.
 * @param int $countLimit The length/count of limit. 
 * 
 * @return string A limit clause or an empty string.
 */
function db_clause_limit($start_limit=null, $count_limit=null) {
	$limit = '';
	
	if ( !is_null($start_limit) && ($start_limit = (int)$start_limit) >= 0 ) {
		$limit = ' LIMIT ' . $start_limit;
	}
	
	if ( !is_null($count_limit) && ($count_limit = (int)$count_limit) > 0 ) {
		$count_limit = (int)$count_limit;
		
		$limit = empty($limit) 
			? ' LIMIT ' . $count_limit 
			: $limit . ', ' . $count_limit;
	}
	
	return $limit;
}

/**
 * Retrieve the valid options for an enum column
 * 
 * This function is cached at runtime for speed. If for any reason the function fails
 * an empty array is returned.
 *
 * @param string $table   The table that contains the column
 * @param string $column  The column to lookup 
 * 
 * @return string A limit clause or an empty string.
 */
function db_get_enum_options($table, $column) {
	static $cache = array();

	$hash = $table.','.$column;

	if ( isset($cache[$hash]) ) {
		return $cache[$hash];
	}

	if ( preg_match('/[^\\w]/i', $table) || preg_match('/[^\\w]/i', $column) ) {
		return $cache[$hash] = array();
	}

	$link = null;
	db_get_resource($link);
	
	$stmt = $link->prepare('SHOW COLUMNS FROM `'.$table.'`');

	if ( $stmt && $stmt->execute() ) {
		while ( $data = $stmt->fetch(PDO::FETCH_ASSOC) ) {
			if ( $data['Field'] == $column ) {
				$type = $data['Type'];

				if ( preg_match('/^enum/i', $type) ) {
					$beginList = strpos($type, '(') + 1;
					$endList   = strpos($type, ')');

					$type = substr($type, $beginList, ($endList-$beginList) );
					$type = strtr($type, array('\'' => '', '"' => ''));

					return $cache[$hash] = split(',', $type);
				}

				break;
			}
		}
	}

	return $cache[$hash] = array();
}



