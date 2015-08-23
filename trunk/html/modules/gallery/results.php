<?php
/**
 * Gallery results library
 *
 * @author Andrew Murphy 
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @version 2.0.0 20080430 JJ
 * @version 1.0.5 20080204 AM
 * @version 1.0.4 20080201 AM
 * @version 1.0.4 20080125 JJ
 * @version 1.0.3 20071227 AM
 * @version 1.0.2 20071203 JJ
 * @version 1.0.1 20071120 AM
 *
 */


function getExamples() {
	$link = null;
	db_get_resource($link);
	
	if ( $link ) {
		$stmt = $link->prepare("SELECT `title`, `listing_id` FROM " . DB_TABLE_LISTING);

		if ( $stmt ) {
			if ( $stmt->execute() ) {
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		}
	}
	
	return false;
}

