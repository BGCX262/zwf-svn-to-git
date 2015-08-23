<?php
/**
 * @abstract File system utilities
 * 
 * @author Justin Johnson <justin@boolenagate.org>
 * @version 0.2.1 20080430 JJ
 * @version 0.2.0 20080416 JJ
 * @version 0.1.0 20070311 JJ
 * 
 * @package zk.modules.filesyst
 */
 
class Filesys {
	/** 
	 * @abstract Function to remove directories, even if they contain files or subdirectories.  
	 * @return array/boolean Array of deleted items, or false if nothing was deleted.
	 * 
	 * @version 18-Jan-2007 09:42
	 * @author Justin Frim <PHPcoder@cyberpimp.sexventure.com>
	 * @url http://us2.php.net/rmdir 
	 */
	public static function deltree($dirname) {
		// Operate on dirs only
		if ( is_dir($dirname) ) {
			 $result = array();
			 
			 // Append slash if necessary
			 if ( substr($dirname,-1) != '/' ) {
				  $dirname.='/';
			 }
			 
			 $handle = opendir($dirname);
			 
			 while ( false !== ($file = readdir($handle)) ) {
				  if ( !($file == '.' || $file = '..') ) {
				  		// Ignore . and ..
						$path = $dirname . $file;
						
						// Recurse if subdir, Delete if file
						if ( is_dir($path) ) {
							 $result = array_merge($result, Filesys::deltree($path));
						}
						else {
							 unlink($path);
							 $result[] = $path;
						}
				  }
			 }
			 closedir($handle);
			 
			 // Remove dir
			 rmdir($dirname);
			 $result[] = $dirname;
			 
			 // Return array of deleted items
			 return $result;
		}
		// Return false if attempting to operate on a file or non-existant dir
		else {
			 return false;
		}
	}
}

