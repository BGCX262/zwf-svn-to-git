<?php
/**
 * @abstract Breadcrumb storage and output.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 2.0.2 20080428 JJ
 * @version 2.0.0 20080421 JJ
 * @version 1.0.0 20070305 JJ
 * 
 * @package zk.modules.web.breadcrumbs
 */


/**
 * @abstract For building breadcrumb paths.
 */
class Breadcrumbs {
	// implements Builder {
	const TMPL_SPACER = " &#187; \n";
	const TMPL_NORM   = '<a href="%s" title="%s" class="breacrumb">%s</a>';
	const TMPL_LAST   = '<span class="breacrumb">%s</span>';
	
	
	/**
	 * @abstract Store and retreive breadcrumbs.
	 */ 
	private static function handler($action, $data=array()) {
		static $breadcrumbs = array();
		
		switch ( $action ) {
			case HANDLE_ADD:
				if ( count($data) >= 3 ) {
					$breadcrumbs[] = ( $data[0] !== false ) ?
						sprintf(
							Breadcrumbs::TMPL_NORM,
							$data[0], $data[2], $data[1]
						)
						:
						sprintf(Breadcrumbs::TMPL_LAST, $data[1]);
					return true;
				}
				
			case HANDLE_GET:
				return $breadcrumbs;
		}
		
		return false;
	}


	/**
	 * @abstract Add a breadcrumb to the list.
	 * @param string The URL for the breadcrumb. If $url is empty or '#', then 
	 * Breadcrumbs::TMPL_LAST will be used to format the link; otherwise, Breadcrumbs::TMPL_NORM will be used.
	 * @param $innerHTML The displayed/clickable link.
	 * @param string $title Optional. The value for the title attribute of the link (default: ''). Ignored if
	 * $url is empty.
	 * @return string Returns an empty string for us in GOAT callbacks.
	 */
	public static function add($url, $innerHTML, $title='') {
		$url = trim($url);
		
		// Add this breadcrumb
		Breadcrumbs::handler(
			HANDLE_ADD,
			array(
				( $url == '#' || empty($url) ) ? false : $url, 
				htmlentities($innerHTML, ENT_QUOTES), 
				str_replace('"', '', $title)
			)
		);
		
		return '';
	}


	/**
	 * @abstract Constructs the breadcrumbs output.
	 * @return string The formatted breadcrumbs separated by Breadcrumbs::TMPL_SPACER.
	 */
	public static function build() {
		 return implode(Breadcrumbs::TMPL_SPACER, Breadcrumbs::handler(HANDLE_GET));
	}
}



if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback('breadcrumbs' , 'display' , array('Breadcrumbs', 'build'));
	add_gobe_callback('breadcrumbs' , 'add'     , array('Breadcrumbs', 'add'));
}
