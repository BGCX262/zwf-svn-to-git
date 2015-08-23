<?php
/**
 * @abstract HTML Page functionality
 * 
 * @author Justin Johnson <johnsonj>
 * @author Andrew Muprhy <andrew@booleangate.com>
 * @version 20080430 JJ
 * @version 20080428 JJ
 * @version 20080427 JJ
 * @version 20080421 JJ
 * @version 20070701 AM
 * 
 * @package zk.modules.web.html
 */

include_gobe_module('builder.html');
include_gobe_module('builder.list');


class WebPage {
	const TMPL_INC_CSS        = "<link rel=\"stylesheet\" type=\"text/css\" media=\"%s\" title=\"%s\" href=\"%s\">\n";
	const TMPL_INC_CSS_IF     = "<!--[if %s]>\n\t%s<![endif]-->\n";
	const TMPL_INC_JAVASCRIPT = "<script type=\"text/javascript\" src=\"%s\"></script>\n";
	const TMPL_INC_META       = "<meta %s=\"%s\" content=\"%s\" />\n";
	
	private static $pageTitle;
	private static $relList;
	

	public function __construct() {
		self::$pageTitle = '';
		self::$relList   = array();
	}

	/**
	 * @abstract Gets the site's main title.
	 * 
	 * @return string The site's title.
	 * 
	 * @see DEFAULT_SITE_TITLE. 
	 */
	public static function getSiteTitle() {
		return DEFAULT_SITE_TITLE;
	}
	
	
	/**
	 * @abstract Sets the title for a given page.
	 * 
	 * @param string $title The title to use.
	 * @param bool $append Optional.  If true, $title is appended to the existing page's title; otherwise, $title 
	 * overwrites the existing title (default: false).
	 * 
	 * @return string Returns an empty string for use with GOAT.
	 */
	public function setPageTitle($title, $append=false) {
		self::$pageTitle = $append ? self::$pageTitle . $title : $title;
		return '';
	}
	
	public function getPageTitle($separator=' - ') {
		return !empty(self::$pageTitle)
				? self::$pageTitle . $separator
				: '';
	}
	
	/**
	 * @abstract Sets a `rel` attribute for one or many links.  As many links can be passed at once following $relValue.
	 * 
	 * @param string $relValue The rel value to apply to all of the passed link names.
	 * 
	 * @return string Returns an empty string for use with GOAT. 
	 */
	public function setRel($relValue) {
		$linkNames = func_get_args();
		$count     = func_num_args();
	
		for ( $i = 1; $i < $count; ++$i ) {
			$this->relList[ $linkNames[$i] ] = $relValue;
		}
		
		return '';
	}
	
	/**
	 * @abstract Gets the `rel` attribute for a given link.
	 * 
	 * @param string $linkName The name of a given link.
	 * @param string $default Optional.  If no `rel` has been sent for $linkName, this value is used (default: nofollow).
	 * 
	 * @return string Returns the `rel` value for the link.
	 */
	public function getRel($linkName, $default='nofollow') {
		return isset($this->relList[$linkName]) ? $this->relList[$linkName] : $default;
	}
	
	/**
	 * @abstract Tracks Meta tag information for inclusion later.
	 * 
	 * @param string $type The type of meta tag.  Acceptable types are `name` and `http-equiv`. 
	 * @param string $type_value The $types value (if $type_value='author' then name='author').
	 * @param string $content  The content of the `content` attribute of the meta tag ( name='author' content='Justin Johnson').
	 * @param string $append Optional. If false, any $content already at $type-$type_value will be overwritten; otherwise,
	 * $content will be appended to the existing $type-$type_value pair and separated by $append (default: false).
	 * 
	 * @return string Returns an empty string for use with GOAT.
	 */
	public static function metaInclude($type, $type_value, $content, $append=false) {
		$type       = strtolower(trim($type));
		$type_value = strtolower(trim($type_value));
		$metaData   = ListBuilder::build('meta-inc');
		
		if ( !$metaData ) {
			$metaData = array();
		} else {
			$metaData = $metaData[0];
		}
		
		if ( !isset($metaData[$type]) ) {
			$metaData[$type] = array();
		}
	
		if ( $append !== false && isset($metaData[$type][$type_value]) ) {
			$metaData[$type][$type_value] = $metaData[$type][$type_value] . $append . $content;
		} else {
			$metaData[$type][$type_value] = $content;
		}
		
		ListBuilder::remove('meta-inc');
		ListBuilder::add('meta-inc', $metaData);

		return '';
	}
	
	/**
	 * @abstract Builds the Meta tag markup.
	 * 
	 * @return string The Meta tag  markup as included through WebPage::metaInclude() 
	 * and formatted using WebPage::TMPL_INC_META.
	 * 
	 * @see WebPage::metaInclude();
	 * @see WebPage::TMPL_INC_META
	 */
	public static function metaList(){
		$includes = ListBuilder::build('meta-inc');
		$response = '';
		
		if ( is_array($includes) ) {
			foreach($includes[0] as $type => $list){
				foreach ( $list as $type_value => $content ) {
					$response .= sprintf(WebPage::TMPL_INC_META, $type, $type_value, $content);
				}
			}
		}
		
		return $response;
	}
	
	/**
	 * @abstract Adds a CSS path for inclusion later.
	 * 
	 * @param string $path The path to a given CSS file.
	 * @param string $baseDir Optional. A base directory to prepend to $path (default: PATH_CSS).
	 * 
	 * @return string Returns an empty string for use with GOAT.
	 * 
	 * @see PATH_CSS.
	 */
	public static function cssInclude($path, $title=DEFAULT_CSS_TITLE, $media=DEFAULT_CSS_MEDIA, $ieCondition='', $baseDir=PATH_CSS) {
		ListBuilder::add(
			'css-inc',
			array(
				'path'  => $baseDir . $path,
				'title' => empty($title) ? DEFAULT_CSS_TITLE : $title,
				'media' => empty($media) ? DEFAULT_CSS_MEDIA : $media,
				'ie'    => $ieCondition
			) 
		);
		return '';
	}
	
	
	/**
	 * @abstract Builds the CSS include markup.
	 * 
	 * @return string The CSS include markup as included through WebPage::cssInclude() 
	 * and formatted using WebPage::TMPL_INC_CSS and WebPage::TMPL_INC_CSS_ID.
	 * 
	 * @see WebPage::cssInclude();
	 * @see WebPage::TMPL_INC_CSS
	 * @see WebPage::TMPL_INC_CSS_IF
	 */
	public static function cssList() {
		$includes = ListBuilder::build('css-inc');
		$response = '';
		
		if ( is_array($includes) ) {
			foreach ( $includes as $include ) {
				if ( !empty($include['ie']) ) {
					$response .= sprintf(WebPage::TMPL_INC_CSS_IF,
						$include['ie'],
						sprintf(WebPage::TMPL_INC_CSS,
							$include['media'],
							$include['title'],
							$include['path']
						)
					);
				} else {
					$response .= sprintf(WebPage::TMPL_INC_CSS,
						$include['media'],
						$include['title'],
						$include['path']
					);
				}
			}
		}
		
		return $response;
	}
	
	/**
	 * @abstract Gets the default CSS `title` attribute
	 * @return string The default CSS `title` attribute.
	 * @see DEFAULT_CSS_TITLE.
	 */
	public static function cssTitle() {
		return DEFAULT_CSS_TITLE;
	}
	
	/**
	 * @abstract Gets the default CSS `media` attribute
	 * @return string The default CSS `media` attribute.
	 * @see DEFAULT_CSS_MEDIA.
	 */
	public static function cssMedia() {
		return DEFAULT_CSS_MEDIA;
	}
	
	/**
	 * @abstract Adds a Javascript path for inclusion later.
	 * 
	 * @param string $path The path to a given Javascript file.
	 * @param string $baseDir Optional. A base directory to prepend to $path (default: PATH_JAVASCRIPT).
	 * 
	 * @return string Returns an empty string for use with GOAT.
	 * 
	 * @see PATH_JAVASCRIPT.
	 */
	public static function javascriptInclude($path, $baseDir=PATH_JAVASCRIPT) {
		ListBuilder::add('js-inc', $baseDir . $path);
		return '';
	}
	
	/**
	 * @abstract Builds the Javascript include markup.
	 * 
	 * @return string The Javascript include markup as included through WebPage::javascriptInclude() 
	 * and formatted using WebPage::TMPL_INC_JAVASCRIPT.
	 * 
	 * @see WebPage::javascriptInclude();
	 * @see WebPage::TMPL_INC_JAVASCRIPT
	 */
	public static function javascriptList() {
		$includes = ListBuilder::build('js-inc');
		$response = '';
		
		if ( is_array($includes) ) {
			foreach ( $includes as $include ) {
				$response .= sprintf(WebPage::TMPL_INC_JAVASCRIPT, $include);
			}
		}
		
		return $response;
	}

	/**
	 * @abstract Uses a variable function list and a selected value to build a list of <option>'s.
	 * Pipes, "|", are used to seperate parameters within individual option arguments, and an argument 
	 * with no pipes will set the value and title to the whole argument.
	 *
	 * Example:
	 *   echo generateSelectOptions('1', 'One|1', 'Two|2', '3');
	 *
	 * Would produce:
	 *   <option value="1" title="One" selected>One</option>
	 *   <option value="2" title="Two" >Two</option>
	 *   <option value="3" title="3" >3</option>
	 * 
	 * @param string $selected The value to flag as selected in the generated <option>'s.
	 * 
	 * @return string Returns the generated <option> list markup.
	 */
	public static function generateSelectOptions($selected = '') {
		$args     = func_get_args();
		$numArgs  = func_num_args();
		$response = 
		$title    = 
		$value    = 
		$class    = '';
		$parts    = array();

		for ( $i = 1; $i < $numArgs; ++$i ) {
			$parts = explode('|', $args[$i], 4);
			$title = isset($parts[0]) ? $parts[0] : '';
			$value = isset($parts[1]) ? $parts[1] : $title;
			$class = isset($parts[2]) ? $parts[2] : '';
	
			$response .= HTMLElement::build(
				'option', $title, 
				array(
					'value' => $value, 
					'title' => $title, 
					'class' => $class
				),
				array(
					'selected' => $selected == $value
				)
			) . "\n";
		}

		return $response;
	}
	
	/**
	 * @abstract Gets the URI of the current page.
	 * 
	 * @param bool $fullPath Optional.  If true, returns the whole URI including SITE_BASEURL; otherwise, 
	 * everthing after SITE_BASEURL is returned (default: true).
	 * 
	 * @return string The URI of the current page.
	 */
	public static function thisURI($fullPath=true) {
		return ($fullPath ? SITE_BASEURL : '') . strtr(
			$_SERVER['REQUEST_URI'],
			array(
				'<' => '%3C',
				'>' => '%3E',
				'"' => '%22',
				'\'' => '%27'
			)
		);
	}
}



if ( function_exists('add_gobe_callback') ) {
	global $__WebPage;
	$__WebPage = new WebPage();
	
	// Miscellaneous
	add_gobe_callback('page'   , 'thisuri'            , array('WebPage', 'thisURI'));
	add_gobe_callback('page'   , 'select-options'     , array('WebPage', 'generateSelectOptions'));
	
	// Titles
	add_gobe_callback('page'   , 'site-title'         , array('WebPage' , 'getSiteTitle'));
	add_gobe_callback('page'   , 'page-title'         , array($__WebPage, 'getPageTitle'));
	add_gobe_callback('page'   , 'set-title'          , array($__WebPage, 'setPageTitle'));
	
	// Meta
	add_gobe_callback('page'   , 'meta-includes'      , array('WebPage', 'metaList'));
	add_gobe_callback('include', 'meta'               , array('WebPage', 'metaInclude'));
	
	// CSS
	add_gobe_callback('page'   , 'css-includes'       , array('WebPage', 'cssList'));
	add_gobe_callback('page'   , 'css-title'          , array('WebPage', 'cssTitle'));
	add_gobe_callback('page'   , 'css-media'          , array('WebPage', 'cssMedia'));
	add_gobe_callback('include', 'css'                , array('WebPage', 'cssInclude'));
	
	// Javascript
	add_gobe_callback('page'   , 'javascript-includes', array('WebPage', 'javascriptList'));
	add_gobe_callback('include', 'javascript'         , array('WebPage', 'javascriptInclude'));
	
	// Rels
	add_gobe_callback('page'   , 'set-rel'            , array($__WebPage, 'setRel'));
	add_gobe_callback('page'   , 'get-rel'            , array($__WebPage, 'getRel'));
}

