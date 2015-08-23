<?php

/**
 * Defines an generic abstraction for a pageparser.  All page parsers used with GOBE should 
 * extend this abstraction.
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100205 (johnsonj)
 * @package gobe.parser.abstract
 */
abstract class Gobe_Parser_Abstract {
	protected $engine;
	protected $layout;
	
	public function getEngine() {
		return $this->engine;
	}
	
	public function setLayout($layout, $base_path=GOBE_PATH_TEMPLATES_LAYOUTS) {
		$layout = $base_path . $layout;
		
		// Make sure the layout exists and is readable
		if ( !file_exists($layout) || !is_readable($layout) ) {
			trigger_error(__CLASS__ ."::setLayout() Could not load layout at `$layout`", E_USER_NOTICE);
			return false;
		}
		
		return $this->layout = $layout;
	}
	
	public function getLayout() {
		return $this->layout;
	}
	
	abstract public function parse($file);
	
	abstract public function setVariable($name, $value);
	abstract public function unsetVariable($name);
	
	abstract public function setCallback($group, $name, $callback);
	abstract public function unsetCallback($group, $name);
	
	abstract public function setTab($tab);
	
	abstract public function setErrorCallback($callback);
}