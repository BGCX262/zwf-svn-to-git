<?php
/**
 * @abstract Database library wrapper class (PHP 5 implementation)
 * @author Justin Johnson <johnsonj>
 * @version 0.0.8 20080218 JJ
 * @version 0.0.6 20071127 JJ
 * 
 * @package zk.modules.database.dblib
 */

/* Globals for tracking efficiency */
$__SQLWrap_total_objects   = 0;
$__SQLWrap_current_objects = 0;
$__SQLWrap_total_queries   = 0;
 
class SQLWrap {
	/* Modified by Justin Johnson <johnsonj> 20070301
	 *  -Exception throwing on error
	 *  -Flagged result freeing
	 *  -Query_to_array()
	 *  -insert_id()
	 *  -de/construct
	 *  -statistics
	 * 
	 * 
	 * Original version posted at http://www.phpnoise.com/tutorials/43/1
	 * by haiden@westnet.com.au (July 26, 2004)
	 */
	
	var $dbhost;
	var $dbuser;
	var $dbpass;
	var $dbase;
	var $sql_query;
	var $mysql_link;
	var $sql_result;
	var $query_count;


	function __construct($auto_connect=true, $auto_select=true,
						 $host=DATABASE_HOST, $user=DATABASE_USERNAME, 
						 $pass=DATABASE_PASSWORD, $db=DATABASE) {
		global $__SQLWrap_total_objects, $__SQLWrap_current_objects;
		$__SQLWrap_total_objects++;
		$__SQLWrap_current_objects++;
		
		$this->dbhost = $host;
		$this->dbuser = $user;
		$this->dbpass = $pass;
		$this->dbase  = $db;
		
		$this->mysql_link  = NULL;
		$this->query_count = 0;
		$this->sql_result  = NULL;
		
		if ( $auto_connect ) {
			$this->connect();
			
			// Only auto-select if auto-connecting
			if ( $auto_select ) {
				$this->select_db();
			}
		}
	}
	
	function __destruct() {
		global $__SQLWrap_current_objects;
		$__SQLWrap_current_objects--;
		$this->free($this->sql_result);
		$this->close();
	}


	public function connect() {
		$this->mysql_link = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass) 
						or $this->error( ERROR_DATABASE_CONNECT );

		$this->query("SET NAMES utf8",        ERROR_DATABASE_QUERY);
		// MySQL 5 uses CHACATER_SET
		$this->query("SET CHARACTER SET utf8;", ERROR_DATABASE_QUERY);
	}


	public function close($reset=false) {
		$this->free($this->sql_result);
		
		if ( $reset ) {
			$this->query_count = 0;
			$this->sql_query = NULL;
		}
		
		return mysql_close($this->mysql_link) 
						or $this->error( ERROR_DATABASE_CONNECT );
	}
	
	
	public function select_db($dbase=NULL) {
		// New database connection
		if ( $dbase !== NULL ) {
			$this->dbase = $dbase;
		}
		
		mysql_select_db($this->dbase) 
						or $this->error( ERROR_DATABASE_SELECT );
	}
	
	public function resource() {
		return $this->mysql_link;
	}


	public function query($sql_query, $code_on_error=ERROR_DATABASE_QUERY, $return_assoc_array=true, $free=true) {
		$this->sql_query  = $sql_query;
		$this->sql_result = mysql_query($sql_query, $this->mysql_link) or $this->error($code_on_error);
		
		if ( !$this->sql_result ) {
			$this->error( $code_on_error );
		}

		// Increment query counter
		$this->query_count++;
		
		/* --- */
		global $__SQLWrap_total_queries; 
		$__SQLWrap_total_queries++;
		/* --- */
		
		// mysql_query will return boolean true for update, insert, drop, delete, alter, replace
		if ( $this->sql_result === true ) 
			return true;
		elseif ( $return_assoc_array ) {
			return $this->query_to_array($free);
		}
	}
	
	
	protected function query_to_array($free) {
		// This function takes the returned object of a mysql function
		//  and converts it into an array for simplified processesing.
	
		// Each row becomes an element of the array
		$a = array();
		while ($row = mysql_fetch_assoc($this->sql_result) )
			$a[] = $row;
		
		if ( $free ) 
			$this->free($this->sql_result);
			
		return $a;
	}


	public function num_rows() {
		$mysql_rows = mysql_num_rows($this->sql_result);

		if ( !$mysql_rows ) {
			$this->error( ERROR_DATABASE_GENERAL );
		}

		return $mysql_rows;
	}



	public function fetch_array() {
		if ( $this->num_rows() > 0 ) {
			$mysql_array = mysql_fetch_assoc($this->sql_result);

			if (!is_array( $mysql_array )) {
				return false;
			}
	
			return $mysql_array;
		} 
	}


	public function fetch_rows() {
		if ( $this->num_rows() > 0 ) {
			$mysql_array = mysql_fetch_row($this->sql_result);

			if ( !is_array($mysql_array) ) {
				return false;
			}
		
			return $mysql_array;
		}
	}
	
	public function raw_query_result() {
		return $this->sql_result;
	}	
	

	public function affected_rows() {
		return mysql_affected_rows($this->mysql_link);
	}
	
	public function insert_id() {
		return mysql_insert_id($this->mysql_link);
	}	
	
	

	public function query_count() {
		return $this->query_count;
	}	


	public function statistics() {
		global $__SQLWrap_total_objects, $__SQLWrap_current_objects, $__SQLWrap_total_queries;
		return array(
			"total-objects"   => $__SQLWrap_total_objects,
			"current-objects" => $__SQLWrap_current_objects,
			"total-queries"   => $__SQLWrap_total_queries
		);
	}


	protected function error($code) {
//		var_dump(mysql_error()); var_dump(mysql_error()); die;
		throw new DatabaseException(
		 	mysql_error($this->mysql_link) . "\n" .
			$this->sql_query . "\n" .
			"Mysql Error    #" .mysql_errno($this->mysql_link) . "\n" .
			"Internal Error #" .$code,
			$code
		);
	}	
	
	protected function free($obj) {
		if ( is_resource($obj) && get_resource_type($obj) == "mysql result" )
		mysql_free_result($obj);
	}
	
	public static function str_or_null($val) {
		return empty($val) ? ('null') : ('"' . $val .'"');
	}
}

/* This extension servers to distinguish database exceptions from all others */
class DatabaseException extends Exception {
	public function __construct($message="", $code=0) {
	     parent::__construct();
	     $this->message = $message;
	     $this->code    = $code;
	}
   
	public function __toString() {
		return parent::__toString();	
	}
}


function establish_global_sqlwrap(&$sql, $auto_connect=true, $auto_select=true,
						 $host=DATABASE_HOST, $user=DATABASE_USERNAME, 
						 $pass=DATABASE_PASSWORD, $db=DATABASE_DATABASE) {
	/*
	 * Methodology: declare a variable in the global space set to NULL. Before attempting
	 * to access the database through SQLWrap, pass that global to this function from your 
	 * working function.  This way you can use one SQLWrap object that is instansiated only
	 * when needed rather than everytime.  
	 * 
	 * You could simply create a new SQLWrap object in every function but this eliminates all
	 * of those needless __constructs and __destructs whenever the object comes in and out of
	 * scope.
	 */
						
	if ( $sql === NULL ) {
		$sql = new SQLWrap($auto_connect, $auto_select, $host, $user, $pass, $db);
	}
}

/* Global SQL object for all SQL queries and actions. */
static $_sql = NULL;

