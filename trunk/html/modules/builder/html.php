<?php
/**
 * @abstract For building HTML elments.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 1.3.1 20080421 JJ
 * @version 1.3.0 20080412 JJ
 * @version 1.2.1 20080411 JJ
 * @version 1.2.0 20080410 JJ
 * 
 * @package raygun.modules.builder.html
 */

//include_gobe_module('builder.interface');

/**
 * @abstract The base object for all HTML elements.
 */
class HTMLElement {
//	implements Builder_static {
	const TMPL_OPEN_CLOSE = '<%s%s%s>%s</%s>';
	const TMPL_SELF_CLOSE = '<%s%s%s/>';
	const TMPL_ATTRIBUTE  = ' %s="%s"';
	const TMPL_FLAG       = ' %s';
	
	public $tagName;
	public $innerHTML;
	public $attributes;
	public $flags;


	/**
	 * @abstract Creates a new HTMLElement of type <$tagName>.
	 * @param string $tagName The tag name of the HTML element (e.g.: <h2>).
	 * @param array $attributes An associative array where keys are attribute types and the values at those 
	 * keys are the attribute values.
	 * @param array $flags Like $attributes, but key values are either true or false. If a key is true, the 
	 * flag will be insert; otherwise (false), the flag will be ignored.
	 */
	public function __construct($tagName='', $innerHTML='', $attributes=array(), $flags=array()) {
		$this->tagName    = $tagName;        // h2, div, option, img, span, p, a, etc
		$this->innerHTML  = $innerHTML;  // False for self-closing tagNames (e.g.: img)
		$this->attributes = $attributes; // Id, class, title, rel, alt, etc
		$this->flags      = $flags;      // checked, selected, etc
	}

	/**
	 * @abstract Generates the formatted HTML for this element
	 * @return string An XHTML string as formatted by HTMLElement::build().
	 * @see HTMLElement::build()
	 */
	public function __toString() {
		return HTMLElement::build($this->tagName, $this->innerHTML, $this->attributes, $this->flags);
	}

	/**
	 * @abstract Generates the formatted HTML for this element.
	 * @param array $tagName A tag name as described by HTMLElement.
	 * @param array $flags Flags as described by HTMLElement.
	 * @param array $attributes Attributes as described by HTMLElement.
	 * @param array $flags Flags as described by HTMLElement.
	 * @return string A formatted XHTML string.
	 */
	public static function build($tagName='', $innerHTML='', $attributes=array(), $flags=array()) {
		/* Self-closing element */
		if ( $innerHTML === false ) {
			return sprintf(
				HTMLElement::TMPL_SELF_CLOSE,
				$tagName,
				HTMLElement::formatAttributes($attributes),
				HTMLElement::formatFlags($flags)
			);
		}

		/* An open/close element */
		return sprintf(
			HTMLElement::TMPL_OPEN_CLOSE,
			$tagName,
			HTMLElement::formatAttributes($attributes),
			HTMLElement::formatFlags($flags),
			(is_array($innerHTML)
				// Will recursively __toString nested HTMLElement objects
				? "\n\t" . implode("\n\t", $innerHTML) . "\n"
				: (is_string($innerHTML) ? $innerHTML : "\n\t" .$innerHTML ."\n")
			),
			$tagName
		);
	}


	public static function formatAttributes($attributes) {
		$strAttributes = '';
		foreach ( $attributes as $name=>$value ) {
			$strAttributes .= sprintf(HTMLElement::TMPL_ATTRIBUTE, $name, $value);
		}

		return $strAttributes;
	}


	public static function formatFlags($flags) {
		$strFlags = '';
		foreach ( $flags as $name=>$value ) {
			if ( $value ) {
				$strFlags .= sprintf(HTMLElement::TMPL_FLAG, $name);
			}
		}

		return $strFlags;
	}
}



/**
 * @abstract An HTML <select> element.
 */
class HTMLSelect
	extends HTMLElement {
	
	/**
	 * @abstract Creates a new HTML <select> element.
	 * @param string $name The `name` attribute of the element (e.g.: name="state_list").
	 * @param mixed $options A preformatted string of <option> elements, an HTMLOption, or an array of HTMLOption's.
	 * @param array $attributes Attributes as described by HTMLElement.
	 * @param array $flags Flags as described by HTMLElement.
	 * @see HTMLElement::__construct() 
	 */
	public function __construct($name, $options, $attributes=array(), $flags=array()) {
		$attributes['name'] = $name;
		parent::__construct('select', $options, $attributes, $flags);
	}
}



/**
 * @abstract An HTML <option> element.
 */
class HTMLOption
	extends HTMLElement {

	/**
	 * @abstract Creates a new HTML <option> element.
	 * @param string $value The `value` attribute of the element (e.g.: value="ca").
	 * @param mixed $innerHTML InnerHTML as described by HTMLElement.
	 * @param bool $selected Whether or not the element is selected.
	 * @param array $attributes Attributes as described by HTMLElement.
	 * @param array $flags Flags as described by HTMLElement.
	 * @see HTMLElement::__construct() 
	 */
	public function __construct($value, $innerHTML, $selected=false, $attributes=array(), $flags=array()) {
		$attributes['value'] = $value;
		$flags['selected']   = $selected;
		parent::__construct('option', $innerHTML, $attributes, $flags);
	}
}



/**
 * @abstract An HTML <a> element 
 */
class HTMLAnchor
	extends HTMLElement {

	/**
	 * @abstract Creates a new HTML <a> element.
	 * @param string $href The `href` attribute of the element (e.g.: href="http://asdf.com/").
	 * @param mixed $innerHTML InnerHTML as described by HTMLElement.
	 * @param array $attributes Attributes as described by HTMLElement.
	 * @param array $flags Flags as described by HTMLElement.
	 * @see HTMLElement::__construct() 
	 */
	public function __construct($href, $innerHTML, $attributes=array(), $flags=array()) {
		$attributes['href'] = $href;
		parent::__construct('a', $innerHTML, $attributes, $flags);
	}
}




/*

echo "<pre>";

// Normal innerHTML
echo "<h3>Normal innerHTML</h3>", htmlentities(
	new HTMLAnchor(
		'http://asdf.com', 
		'asdf!', 
		array('title'=>'Everyone loves an ASDF', 'target'=>'_blank')
	)
);


// Nested object
echo "<h3>Nested object</h3>", htmlentities(
	new HTMLSelect(
		'some_list', 
		new HTMLOption('', 'Choose an option'),
		array('id'=>'some-list')
	)
);


// Nested array
echo "<h3>Nested array</h3>", htmlentities(
	new HTMLSelect(
		'states', 
		array(
			new HTMLOption('CA', 'California'),
			new HTMLOption('CO', 'Colorado', true)
		), 
		array('id'=>'state-list')
	)
);



//* Without extended generators (any HTML element) * /

// Nested array
echo "<h3>Non-predefined</h3>",
	htmlentities(
		new HTMLElement(
			'form',
			array(
				new HTMLElement(
					'input', 
					'Select me!', 
					array(
						'type'=>'radio',
						'name'=>'radio_list[]',
						'value'=>'off'
					)
				),
				new HTMLElement(
					'input', 
					'Select me too!', 
					array(
						'type'=>'radio',
						'name'=>'radio_list[]',
						'value'=>'on'
					),
					array('checked'=>true)
				)
			),
			array(
				'method'=>'post',
				'action'=>'./form-processor/'
			)
		)
	);


// Self-closing exmample
echo "<h3>Self-closing example</h3>",
	htmlentities(
		new HTMLElement(
			'img', 
			false, 
			array(
				'src'=>'super/pr0n/time.jpg',
				'alt'=>'Me love you long time',
				'title'=>'Oh ya'
			)
		)
	);
*/