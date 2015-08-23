<?php

/**
 * Handles all Gobe script inclusion and execution (preload, cascade, local and context)
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 20100209 <johnsonj>
 * @package gobe.scritps
 */
class Gobe_Scripts {
	const TYPE_INITIALIZE = 0x01;
	const TYPE_CASCADE    = 0x02;
	const TYPE_LOCAL      = 0x04;
	const TYPE_CONTEXT    = 0x08;
	const TYPE_CLEANUP    = 0x10;
	
	/**
	 * Everything is enabled by default
	 * @var int
	 */
	private $disabled;
	
	public function __construct() {
		$this->disabled = 0;
	}
	
	public function enable($type) {
		if ( $this->isDisabled($type) ) {
			$this->disabled ^= $type; 
		}
	}
	
	public function disable($type) {
		$this->disabled |= $type;
	}
	
	public function isDisabled($type) {
		return ($this->disabled & $type) != 0;
	}
	
	public function isEnabled($type) {
		return !$this->isDisabled($type);
	}
	
	public function load($request_url, $type) {
		switch ( $type ) {
			case self::TYPE_INITIALIZE: return $this->loadInitialize($request_url);
			case self::TYPE_CASCADE:    return $this->loadCascade($request_url);
			case self::TYPE_LOCAL:      return $this->loadLocal($request_url);
			case self::TYPE_CONTEXT:    return $this->loadContext($request_url);
			case self::TYPE_CLEANUP:    return $this->loadCleanup($request_url);
		}
		
		return false;
	}
	
	public function loadInitialize($request_url) {
		return $this->loadScript(self::TYPE_INITIALIZE, $request_url, GOBE_SCRIPTING_INITIALIZE);
	}
	
	public function loadCascade($request_url) {
		// Get all directories after the doc root
		$directories = explode("/", substr($this->pathToDirectory($request_url), strlen(GOBE_DOC_ROOT))); 
		$file        = $this->makeFile(GOBE_SCRIPTING_CASCADE);
		array_unshift($directories, GOBE_DOC_ROOT);
		
		foreach ( $directories as $dir ) {
			if ( empty($dir) ) {
				continue;
			}
			
			chdir($dir);
			
			// Check eligibility before each inclusion as it can be turned off by
			// any cascade include
			if ( $this->isDisabled(self::TYPE_CASCADE) ) {
				return false;
			}
			
			if ( file_exists($file) ) {
				$this->loadUserScript($file);
			}
		}
		
		return true;
	}
	
	public function loadLocal($request_url) {
		return $this->loadScript(self::TYPE_LOCAL, $request_url, GOBE_SCRIPTING_LOCAL);
	}
	
	public function loadContext($request_url) {
		if ( substr($request_url, -1) == '/' ) {
			$request_url .=  GOBE_DEFAULT_INDEX;
		}
		return $this->loadScript(self::TYPE_CONTEXT, $request_url, pathinfo($request_url, PATHINFO_FILENAME));
	}

	public function loadCleanup($request_url) {
		return $this->loadScript(self::TYPE_CLEANUP, $request_url, GOBE_SCRIPTING_CLEANUP);
	}
	
	private function loadScript($type, $request_url, $basename) {
		if ( $this->isDisabled($type) ) {
			return false;
		}
		
		chdir($this->pathToDirectory($request_url));
		$file = $this->makeFile($basename);
		
		if ( file_exists($file) ) {
			$this->loadUserScript($file);
		}
		
		return true;
	}
	
	private function loadUserScript($path) {
		$gc = new Gobe_Controller($path);
		$gc->execute();
	}
	
	private function pathToDirectory($request_url) {
		$request_url = substr(GOBE_DOC_ROOT, 0, -1) . $request_url;
		return is_dir($request_url) 
			? $request_url 
			: pathinfo($request_url, PATHINFO_DIRNAME);
	}
	
	private function makeFile($file) {
		return $file . "." .  GOBE_SCRIPTING_EXTENSION;
	}
}