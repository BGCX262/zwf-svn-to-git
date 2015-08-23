<?php

require_once(GOBE_PATH_MODULES . "goat/goat.php");

/**
 * Defines a facade to the GOAT parser that conforms to Gobe's parser requirements.
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100205 (johnsonj)
 * @package gobe.parser.goat
 */
class Gobe_Parser_Goat extends Gobe_Parser_Abstract  {
	public function __construct() {
		$this->engine = new Goat();
		$this->setErrorCallback(array($this, "defaultErrorHandler"));
	}
	
	public function parse($template) {
		return $this->engine->parse($template);
	}
	
	public function setVariable($name, $value) {
		if ( $this->engine->variable_exists($name) ) {
			$this->engine->mod_variable($name, $value);
		} else {
			$this->engine->register_variable($name, $value);
		}
	}
	
	public function unsetVariable($name) {
		$this->engine->unregister_variable($name);
	}
	
	public function setCallback($group, $name, $callback) {
		if ( !$this->engine->group_exists($group) ) {
			$this->engine->register_group($group);
		}
		
		if ( $this->engine->callback_exists($group, $name) ) {
			$this->engine->mod_callback($group, $name, $callback);
		} else {
			$this->engine->register_callback($group, $name, $callback);
		}
	}
	
	public function unsetCallback($group, $name) {
		$this->engine->unregister_callback($group, $name);
	}
	
	public function setErrorCallback($callback) {
		$this->engine->register_error_callback($callback);
	}
	
	public function setTab($tab) {
		$this->engine->set_tab($tab);
	}
	
	public function defaultErrorHandler($error) {
		trigger_error($error, E_USER_WARNING);
	}
}