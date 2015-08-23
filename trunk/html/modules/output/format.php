<?php
/**
 * @abstract General formatting functions
 *
 * @author Justin Johnson <johnsonj>
 * @version 1.2.2 20091113 JJ
 * @version 1.2.1 20080506 JJ
 * @version 1.2.0 20080416 JJ
 * @version 1.0.0 20080410 JJ
 *
 * @package zk.modules.output.format
 */


class Format {
	const TMPL_HTML_OPTION = "<option value=\"%s\" title=\"%s\"%s>%s</option>\n";
	const TMPL_PHONE_7     = '%s - %s';
	const TMPL_PHONE_10    = '(%s) %s - %s';


	public static function phone($phone, $template=false) {
		return ( strlen($phone) == 10 )
			? sprintf(
				($template === false ? Format::TMPL_PHONE_10 : $template),
				substr($phone, 0, 3),
				substr($phone, 3, 3),
				substr($phone, 6, 4)
			)
			: sprintf((
				$template === false ? Format::TMPL_PHONE_7 : $template),
				substr($phone, 0, 3),
				substr($phone, 3, 4)
			);
	}
	

	/**
	 * @abstract Formats a 2D array into a list of HTML <option>'s for use in a <select>.
	 *
	 * @param array $options The 2D array to process.
	 * @param string $valueIndex The index of the second dimension of $options to use as the option's value (default: 0).
	 * @param string $titleIndex The index of the second dimension of $options to use as the option's title (default: 1).
	 * @param mixed $template Optional. An sprintf-style template to format each <option> (requires 4 parameters), or false to
	 * use the class provided template, Format::TMPL_HTML_OPTION (default: false).
	 * @return string The <option> list.
	 * @see Format::TMPL_HTML_OPTION
	 * @see Format
	 */
	public static function selectOptionLits(&$options, $valueIndex=0, $titleIndex=1, $selectedValue=false, $template=false) {
		$select = '';

		// Default template
		if ( $template === false ) {
			$template = Format::TMPL_HTML_OPTION;
		}

		// Format each option
		foreach ( $options as $option ) {
			$select .= format_select_option($option[$valueIndex], stripslashes($option[$titleIndex], $selectedValue));
		}

		return $select;
	}
	
	
	/**
	 * @abstract Formats an HTML <option> element.
	 *
	 * @param string $value
	 * @param string $innerHTML
	 * @param string $selectedValue
	 * @param string $template
	 * @return string Returns the parameters formatted into an HTML <option> element
	 */
	public static function selectOption($value, $innerHTML, $selectedValue=false, $template=false) {
		$innerHTML = htmlentities($innerHTML);

		return sprintf(
			$template,
			$value, $innerHTML,
			($selectedValue!==false && $selectedValue==$value ? ' selected' : ''),
			$innerHTML
		);
	}
	
	
	/**
	 * Formats the an array into a parameter string used in POST/GET requests.
	 *
	 * @param array $params An associative array of key-value pairs.
	 * @return string An HTTP request string suitable for POST/GET requests.
	 */
	public static function HttpParms(&$params, $separator='&') {
		$request = array();
		foreach ( $params as $key=>$value ) {
			$request[] = $key . '=' . urlencode($value);
		}
	
		return implode($separator, $request);
	}
	
	/**
	 * Courtesy of http://stackoverflow.com/questions/980902/
	 */
	public static function text2links($data) {
        $strParts = preg_split( '/(<[^>]+>)/', $data, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
	    foreach( $strParts as $key=>$part ) {
	        // check this part isn't a tag or inside a link 
	        if (
	        	!preg_match('`(<[^>]+>)`',  $part ) && 
	        	!preg_match('`(<a[^>]+>)`', $strParts[$key ? $key - 1 : 0])
	        ) {
	            $strParts[$key] = preg_replace(
					'@((http(s)?://)?(\S+\.{1}[^\s\,\.\!]+))@',
					'<a href="http$3://$4">$1</a>',
					$strParts[$key]
				);
	        }
	
	    }

        return implode( $strParts );
	}
	
	public static function listGenerator($list, $list_formatter=null, $list_type="ul") {
		$result = "<$list_type>\n";
		
		foreach ( $list as $key=>$value ) {
			$result .= is_callable($list_formatter) 
				? call_user_func($list_formatter, $key, $value) 
				: "\t<li>$value</li>\n"; 
		}
		
		return $result . "\n</$list_type>\n";
	}
}

