<?php
/**
 * Examples listing adapted from HLocator
 *
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @version 1.4.0 20080430 JJ
 * @version 1.2.0 20080317 JJ
 * @version 1.1.0 20080315 JJ
 * @version 1.0.0 20080314 JJ
 *
 */


include_gobe_module('output.multiform');
include_gobe_module('gallery.main');
include_gobe_module('gallery.results');



$countPerPage = isset($_GET['count']) && (int)$_GET['count'] >  0 ? (int)$_GET['count'] : DB_DEFAULT_LIMIT;
$currentPage  = isset($_GET['page'])  && (int)$_GET['page']  >  1 ? (int)$_GET['page']  : 1;
$startListing = ($currentPage - 1) * $countPerPage;
$sortBy       = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'high-low';
$templates    = multiform(PATH_TEMPLATES.'stubs/examples/sidebar.gform.html');
$results      = getExamples();

if ( !empty($results) ) {
	$resultsList = $title = '';
	$goat = new goat();
	$goat->register_variable('title',     '');
	$goat->register_variable('url',       '');
	
	$goat->register_variable('alt',       '');

	foreach ( $results as $listing ) {
		$title = stripslashes($listing['title']);
		$goat->mod_variable('title'     , $title);
		$goat->mod_variable('url'       , get_path('examples-details', true).'?id='.$listing['listing_id']);
		
		$goat->mod_variable('alt'       , str_replace('"', "''", $title));

		$resultsList .= $goat->parse($templates['sidebar-list']);
	}

} else {
	$resultsList = $templates['noresults'];
}

add_gobe_variable('sidebar-examples', $resultsList);

?>