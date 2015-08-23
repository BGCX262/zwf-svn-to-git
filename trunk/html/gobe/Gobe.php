<?php

/**
 * Gobe 2: A complete rewrite of GOBE originally by Andrew Murphy <andrew.ap.murphy@gmail.com>.
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100303 (johnsonj)
 */
class Gobe {
	private $request;
	private $original_request;
	private $modules;
	private $scripts;
	private $parser;
	private $view;
	
	public function setRequest($request=null) {
		$routes = Gobe_Router::getInstance()->getRoutes();
		// If no request is specified, get the current request
		if ( is_null($request) ) {
			$request = new Gobe_Request($routes);
		}
		
		$this->original_request = $request;
		$this->request          = $this->original_request->translate();
		
		// If the request is invalid, redirect to the 404 template
		if ( !$this->isValidRequest($this->request) ) {
			$this->request->setErrorUrlByStatus( $this->request->getStatus(false) );
		}
		
		// Return the translated request or false
		return $this->request; 
	}
	
	public function isValidRequest($request) {
		if ( !$request instanceof Gobe_Request ) {
			trigger_error(__CLASS__ , "::isValidRequest() expects its first parameter to be an instance of Gobe_Request", E_USER_WARNING);
			return false;	 
		}
		
		// Convert the URL to a file system path. 
		$path = $request->getUrl(false);
		
		// If the root is requested, just use the doc root since real path will strip off
		// the trailing slash and cause validation to fail (even though it is valid).
		$path = $path == "/" ? GOBE_DOC_ROOT : realpath(GOBE_DOC_ROOT . $request->getUrl());
		
		return (
			// Local URLs are valid if they exist, are readable, and are within the doc root
			// specified by GOBE_DOC_ROOT
				$request->isUrlLocal() && $path && file_exists($path) &&  
				is_readable($path)     && strpos($path, GOBE_DOC_ROOT) === 0
			) || 
			//  Remote URLs are assumed valid/
			$request->isUrlRemote();
	}
	
	public function loadScripts() {
		// DO NOT cache `$this->request->getUrl(false);`  The URL needs to be
		// be fetched each time a new script is executed as it can be changed
		// by any of the controllers

		// Load preload script
		$this->scripts->loadInitialize($this->request->getUrl(false));
		
		// Load cascading scripts
		$this->scripts->loadCascade($this->request->getUrl(false));
		
		// Load local script
		$this->scripts->loadLocal($this->request->getUrl(false));
		
		// Load context script
		$this->scripts->loadContext($this->request->getUrl(false));

		// Load teardown script
		$this->scripts->loadCleanup($this->request->getUrl(false));
	}
	
	public function setLayout($template=GOBE_LAYOUT_DEFAULT) {
		$this->parser->setLayout($template);
	}
	
	/**
	 * Sets the inner template for the current view. Also allows the view template to 
	 * be set separately of the request.
	 */
	public function setView($view=null) {
		if ( is_null($view) ) {
			$view = substr(GOBE_DOC_ROOT, 0, -1) . $this->request->getUrl(false);
		}
		
		// If a directory was requested
		if ( is_dir($view) ) {
			// Check to see if it has a default index page
			if ( file_exists($view . GOBE_DEFAULT_INDEX) ) {
				$view .= GOBE_DEFAULT_INDEX;
			}
			// Otherwise, set a blank inner view
			else {
				$this->view = "";
				return false;
			}
		}
		
		// Readability/existance check is not necessary.  We *want* a notice to be raised
		// in the event of unreadable template files. It indicates either a bug in Gobe, 
		// or that a file doesn't have proper permissions.
		$this->view = file_get_contents($view);
		
		// Make sure the view loaded correctly
		if ( $this->view === false ) {
			trigger_error(__METHOD__ . ": $view could not be read", E_USER_NOTICE);
			return false;
		}
		
		return $this->view;
	}
	
	public function loadParser() {
		$this->parser->setVariable(
			GOBE_LAYOUT_CONTENT_VAR, 
			$this->view ? $this->parser->parse($this->view) : ""
		);
		
		return $this->parser->parse(file_get_contents($this->parser->getLayout()));
	}
	
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getRequestOriginal() {
		return $this->original_request;
	}
	
	public function getModules() {
		return $this->modules;
	}
	
	public function getScripts() {
		return $this->scripts;
	}
	
	public function getParser() {
		return $this->parser;
	}
	
	static public function getInstance() {
		static $instance = null;
		
		if ( is_null($instance) ) {
			$instance = new self;
		}
		
		return $instance;
	}
	
	private function __construct() {
		$this->modules = new Gobe_Modules();
		$this->scripts = new Gobe_Scripts();
		$this->parser  = Gobe_Parser::factory(GOBE_PARSER);
		$this->view    = "";
	}
	
	private function __clone() {}
}