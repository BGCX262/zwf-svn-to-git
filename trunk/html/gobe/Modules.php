<?php

/**
 * Mechanism for including and managing modules
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100209 (johnsonj)
 * @package gobe.modules
 */
class Gobe_Modules {
	private $modules;
	private $include_helper;
	
	public function __construct($directory=GOBE_PATH_MODULES) {
		$this->modules = array();
		$this->setBaseDirectory($directory);
	}
	
	public function setBaseDirectory($directory) {
		return $this->base_directory = $directory;
	}
	
	public function getBaseDirectory() {
		return $this->base_directory;
	}
	
	public function register($modules, $directory=null) {
		if ( empty($modules) ) {
			trigger_error(__CLASS__."::register() No modules specified", E_USER_WARNING);
			return false;
		}
		
		$response = array();
		foreach ( (array)$modules as $module ) {
			$response[$module] = $this->load($module, $directory);
		}
		
		if ( count($response) == 1 ) {
			 $response = array_values($response);
			 return $response[0];
		}
		
		return $response;
	}
	
	public function getRegistered() {
		return array_keys($this->modules);
	}
	
	private function load($module, $directory) {
		if ( isset($this->modules[$module]) ) {
			return $this->modules[$module];
		}
		
		// Assume that $module is in package format
		$path = $this->toPath($module, $directory);
		$this->modules[$module] = file_exists($path) && is_readable($path) && !is_dir($path);
		
		// If the file doesn't exist, register an error
		if ( !$this->modules[$module] ) {
			trigger_error(
				"Could not load module `$module` at `$path`.  File is either doesn't exist, " .
				"isn't readable, or isn't a file after all.", 
				E_USER_WARNING
			);
			return false;
		}
		
		$gc = new Gobe_Controller($path);
		$gc->execute();
		
		return $this->modules[$module];
	}
	
	private function defaultIncludeHelper($file) {
		Gobe::getInstance()->scriptsIncludeHelper($file);
	}
	
	private function toPath($module, $directory) {
		if ( is_null($directory) ) {
			$directory = $this->getBaseDirectory();
		} elseif ( substr($directory, -1) != "/" ) {
			$directory .= "/";
		}
		
		return $directory . str_replace(".", "/", $module) . ".php";
	}
}