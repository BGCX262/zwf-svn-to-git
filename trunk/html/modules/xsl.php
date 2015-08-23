<?php
/**
 * @abstract XSL fun.
 *
 * @author Andrew Murphy <andrew@booleangate.org>
 * @author Justin Johnson <johnsonj>
 * @version 1.2.0 20080318 AM
 * @version 1.0.0 20080211 AM
 * 
 * @package zk.modules.xsl
 */

register_gobe_debug_load_module('Loading gomo.xsl');

function xsl_transform_feed($feedUrl, $xslPath) {
	$doc = new DOMDocument();
	$xsl = new XSLTProcessor();

	$doc->load(parse_path($xslPath));
	$xsl->importStyleSheet($doc);

	$doc->loadXML(file_get_contents(parse_path($feedUrl)));
	
	$xsl->registerPHPFunctions(array('xsl_counter'));

	return $xsl->transformToXML($doc);
}


function xsl_counter($index) {
	static $list = array();
	
	if ( isset($list[$index]) ) {
		$list[$index]++;
	}
	else {
		$list[$index] = 1;
	}
	
	return $list[$index];
}




if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback('xsl', 'transform_feed', 'xsl_transform_feed');
}
