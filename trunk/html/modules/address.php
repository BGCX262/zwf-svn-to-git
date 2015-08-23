<?php
/**
 * @abstract Address representation and validation.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.1.2 20080430 JJ
 * @version 0.1.0 20080416 JJ
 * 
 * @package zk.address
 */

class Address {
	public static function validate(&$params, $key_prefix='') {
		/* Name
		 *  -Required, not empty
		 */
		$params[$key_prefix.'name'] = @trim($params[$key_prefix.'name']);
		if ( empty($params[$key_prefix.'name']) ) {
			throw new Exception('A name is required', ERROR_MISSING_DATA);
		}
	
	
	
		/* Address1
		 *  -Required, must match "N xxxxxxxxxxx"
		 */
		if ( empty($params[$key_prefix.'address1']) ) {
			throw new Exception('An address is required', ERROR_MISSING_DATA);
		}
		else {
			$params[$key_prefix.'address1'] = trim($params[$key_prefix.'address1']);
	
			// The street address must match N xxxxxxxxxxx
			// 2 character minimum on the street name
			if ( !preg_match('`^\d+\s+.{2,}`', $params[$key_prefix.'address1']) ) {
				throw new Exception(
					'Please provide your address in a number, street format (e.g.: 123 Street Ave.).',
					ERROR_INVALID_DATA
				);
			}
		}
	
	
		// Address2, company can be anything; not required
		$params[$key_prefix.'address2'] = isset($params[$key_prefix.'address2']) ? trim($params[$key_prefix.'address2']) : '';
		$params[$key_prefix.'company']  = isset($params[$key_prefix.'company'])  ? trim($params[$key_prefix.'company'])  : '';
	
	
		/* City
		 *  -Required, minimum 2 char
		 */
		if ( empty($params[$key_prefix.'city']) ) {
			throw new Exception('A city is required', ERROR_MISSING_DATA);
		}
		else {
			$params[$key_prefix.'city'] = trim($params[$key_prefix.'city']);
	
			// The street address must match N xxxxxxxxxxx
			if ( strlen($params[$key_prefix.'city']) < 2 ) {
				throw new Exception('Please provide a valid city.', ERROR_INVALID_DATA);
			}
		}
	
	
		/* State
		 *  -Required, must be in state list
		 */
		if ( empty($params[$key_prefix.'state']) ) {
			throw new Exception('A state is required', ERROR_MISSING_DATA);
		}
		else {
			$params[$key_prefix.'state'] = trim($params[$key_prefix.'state']);
	
			// The street address must match N xxxxxxxxxxx
			if ( !valid_state($params[$key_prefix.'state'], false) && !valid_state($params[$key_prefix.'state'], true) ) {
				throw new Exception('Please provide a valid city.', ERROR_INVALID_DATA);
			}
		}
	
	
		/* Zip
		 *  -Required, must be 5 digits
		 */
		if ( empty($params[$key_prefix.'zip']) ) {
			throw new Exception('A zip code is required', ERROR_MISSING_DATA);
		}
		else {
			$params[$key_prefix.'zip'] = trim($params[$key_prefix.'zip']);
	
			// The street address must match N xxxxxxxxxxx
			if ( strlen($params[$key_prefix.'zip']) != 5  && !ctype_digit($params[$key_prefix.'zip']) ) {
				throw new Exception('Please provide a valid zip code (5 digits).', ERROR_INVALID_DATA);
			}
		}
	
	
		/* Country
		 *  -optional, defaults to US
		 */
		$params[$key_prefix.'country'] = isset($params[$key_prefix.'country']) ? trim($params[$key_prefix.'country']) : 'US';
	//TODO: when supporting multiple countries, validate codes.
	}
	
	
	public static function validState($state, $fullname=true) {
		$list = &state_names();
	
		return in_array(
					$fullname ? ucwords($state)  :  strtoupper($state),
					$fullname ? $list            :  array_keys($list)
		);
	}
	
	
	/**
	 * @abstract Formats an address into one of two formats.  If $display is true, the address is formatted using HTML; otherwise,
	 * the address is formatted into a single string with spaces replaced by '+' (suitable for Google maps urls, etc).
	 *
	 * @param array &$address An associative array of address information.  Must contain the following indexes: name,
	 * address1, city, state, zip.  Optional indexes: company, address2, phone
	 */
	public static function format(&$address, $prefix, $display=true) {
		return $display
			? $address[$prefix.'name'] . '<br/>' .
				(!empty($address[$prefix.'company'])
					? $address[$prefix.'company'] . '<br/>'
					: '') .
				$address[$prefix.'address1'] . '<br/>' .
				(!empty($address[$prefix.'address2'])
					? $address[$prefix.'address2'] . '<br/>'
					: '') .
				$address[$prefix.'city'] . ', ' .
				$address[$prefix.'state'] . ' ' .
				$address[$prefix.'zip'] .
				(isset($address[$prefix.'phone']) ? ('<br/>' . format_phone($address[$prefix.'phone'])) : '')
			: str_replace(' ', '+',
				$address[$prefix.'address1'] . '+' . aset($address, $prefix.'address2')
				 . '+' . $address[$prefix.'city']
				 . '+' . $address[$prefix.'state']
				 . '+' . $address[$prefix.'zip']
			);
	}
	
	
	public static function zipcode2state($zip) {
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
				return false;
			}
		}
		catch (DatabaseException $e) {
			error_log_and_rethrow($e, ERROR_LOG_DATABASE);
		}
	
		// State exists, return it.
		return $response[0]['state'];
	}
	
	
	public static function states() {
		static $states = array(
				'AL'=>'Alabama',
				'AK'=>'Alaska',
				'AZ'=>'Arizona',
				'AR'=>'Arkansas',
				'CA'=>'California',
				'CO'=>'Colorado',
				'CT'=>'Connecticut',
				'DE'=>'Delaware',
				'DC'=>'District Of Columbia',
				'FL'=>'Florida',
				'GA'=>'Georgia',
				'HI'=>'Hawaii',
				'ID'=>'Idaho',
				'IL'=>'Illinois',
				'IN'=>'Indiana',
				'IA'=>'Iowa',
				'KS'=>'Kansas',
				'KY'=>'Kentucky',
				'LA'=>'Louisiana',
				'ME'=>'Maine',
				'MD'=>'Maryland',
				'MA'=>'Massachusetts',
				'MI'=>'Michigan',
				'MN'=>'Minnesota',
				'MS'=>'Mississippi',
				'MO'=>'Missouri',
				'MT'=>'Montana',
				'NE'=>'Nebraska',
				'NV'=>'Nevada',
				'NH'=>'New Hampshire',
				'NJ'=>'New Jersey',
				'NM'=>'New Mexico',
				'NY'=>'New York',
				'NC'=>'North Carolina',
				'ND'=>'North Dakota',
				'OH'=>'Ohio',
				'OK'=>'Oklahoma',
				'OR'=>'Oregon',
				'PA'=>'Pennsylvania',
				'RI'=>'Rhode Island',
				'SC'=>'South Carolina',
				'SD'=>'South Dakota',
				'TN'=>'Tennessee',
				'TX'=>'Texas',
				'UT'=>'Utah',
				'VT'=>'Vermont',
				'VA'=>'Virginia',
				'WA'=>'Washington',
				'WV'=>'West Virginia',
				'WI'=>'Wisconsin',
				'WY'=>'Wyoming'
		);
		return $states;
	}
	
	
	public static function clean(&$params, &$_sql, $key_prefix='') {
		static $fields = array('name', 'address1', 'address2', 'city', 'state', 'zip', 'country');
		$resource = $_sql->resource();
	
		foreach ( $fields as $f ) {
			$params[$key_prefix.$f] = isset($params[$key_prefix.'country'])
				? mysql_real_escape_string($params[$key_prefix.'country'], $resource)
				: '';
		}
	}
}

