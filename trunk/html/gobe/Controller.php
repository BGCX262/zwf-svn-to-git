<?php

/**
 * @author Justin Johnson <justin@booleangate.org>
 * @version 20100209 johnsonj
 * @package gobe.controller
 */
class Gobe_Controller {
	public $gobe;
	public $modules;
	public $request;
	public $scripts;
	public $parser;
	public $path;
	
	public function __construct($path) {
		$this->gobe    = Gobe::getInstance();
		$this->request = $this->gobe->getRequest();
		$this->modules = $this->gobe->getModules();
		$this->scripts = $this->gobe->getScripts();
		$this->parser  = $this->gobe->getParser();
		$this->path    = $path;
	}
	
	public function execute() {
		require_once($this->path);
	}
	
	// Common parser methods
	
	public function setVariable($name, $value) {
		return $this->parser->setVariable($name, $value);
	}
	
	public function setCallback($group, $name, $callback) {
		return $this->parser->setCallback($group, $name, $callback);
	}

	public function setLayout($path=GOBE_LAYOUT_DEFAULT) {
		return $this->gobe->setLayout($path);
	}
	
	public function setView($path=null) {
		return $this->gobe->setView($path);
	}
	
	
	// Common modules methods
	
	public function register($modules) {
		$this->modules->register($modules);
	}
}