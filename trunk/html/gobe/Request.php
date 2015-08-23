<?php

/*
 * Defines requests and interprets custom routes.
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100222 (johnsonj)
 * @package gobe.request
 */
class Gobe_Request {
	const DEFAULT_PATH   = "/";
	const DEFAULT_STATUS = 200;
	const ERROR_PATH     = GOBE_PATH_TEMPLATES_ERRORS;
	
	private $routes;
	private $url;
	private $status;
	
	public function __construct($routes=array(), $url=false, $status=false) {
		$this->setRoutes($routes);
		$this->setUrl($url       === false ? $this->getUrlRaw(true)    : $url);
		$this->setStatus($status === false ? $this->getStatusRaw(true) : $status);
	}
	
	public function setRoutes($routes) {
		if ( !is_array($routes) ) {
			trigger_error(__CLASS__ . "::setRoutes() expects an array", E_USER_NOTICE);
		}
		
		return $this->routes = $routes;
	}
	
	public function getRoutes() {
		return $this->routes;
	}
	
	public function setUrl($url) {
		$this->url = $url;
		$this->normalizeUrl();
		return $this->url;
	}
	
	public function setErrorUrlByStatus($status) {
		$this->setStatus($status);
		$this->setUrl(self::ERROR_PATH . $status . ".html");
	}
	
	public function getUrl($translate=true) {
		return $translate ? $this->translate()->url : $this->url;
	}
	
	public function getUrlRaw() {
		return isset($_GET[GOBE_QUERY_PATH]) ? $_GET[GOBE_QUERY_PATH] : self::DEFAULT_PATH;
	}
	
	public function isUrlLocal() {
		return !$this->isUrlRemote();
	}
	
	public function isUrlRemote() {
		return (bool)preg_match("`^http(s?)://.*`i", $this->url); 
	}
	
	public function normalizeUrl() {
		$this->url = trim(urldecode($this->url));
		
		// Ensure that the URL always starts with /
		if ( $this->isUrlLocal() && $this->url[0] != '/' ) { 
			$this->url = '/' . $this->url;
		}
	}
	
	public function setStatus($status) {
		return $this->status = (int)$status;
	}
	
	public function getStatus($translate=true) {
		return $translate ? $this->translate()->status : $this->status;
	}
	
	public function getStatusRaw() {
		return isset($_GET[GOBE_QUERY_STATUS]) ? $_GET[GOBE_QUERY_STATUS] : self::DEFAULT_STATUS;
	}
	
	public function isStatusSuccess() {
		return (int)($this->status / 100) == 2;
	}
	
	public function isStatusRedirect() {
		return (int)($this->status / 100) == 3;
	}
	
	public function isStatusError() {
		return !$this->isStatusSuccess() && !$this->isStatusRedirect();
	}
	
	public function redirect($url=null, $status=null) {
		if ( is_null($url) ) {
			$url = $this->url;
		} 
		if ( is_null($status) ) {
			$status = $this->status;
		}
		
		header("status", true, $this->status);
		header("Location: $url");
		die;
	}
	
	public function translate() {
		// Initialize the response with the current state
		$request = new self(array(), $this->url, $this->status);
		
		// Attempt to translate the url until no more translations are found
		do {
			// If this flag is set to true, then translation will continue another round
			$translated = false;
			
			// Routes is formatted as `array(url-pattern-to-match => rewrite-instructions, ...)`.
			// Step through each route and try to match the base URL pattern with the current URL
			foreach ( $this->routes as $route ) {
				
				// A match with the current URL was found
				if ( preg_match($route->pattern, $request->url) ) {
					// Rewrite the URL
					$request->setUrl( preg_replace($route->pattern, $route->rewrite, $request->url) );
					$translated = true;

					// Optional: If a new status was provided, set it
					if ( $route->status !== false ) {
						$request->setStatus($route->status);
					}
					
					// If the `last` flag is set or a redirect is required, then stop all translations
					if ( !empty($route->is_last) || $request->isStatusRedirect() 	) {
						$translated = false;
						break;
					}
				}
			}
		} while ($translated);
		
		// Make sure that the URL is set strictly as a path
		$url_parts = parse_url($request->getUrl(false));
		$request->setUrl($url_parts['path']);
		
		// Inject rewritten URL params into _GET 
		if ( !empty($url_parts['query']) ) {
			$params = array();
			parse_str($url_parts['query'], $params);
			$_GET = array_merge($_GET, $params);
		}

		// If the new status is a redirect
		if ( (int)($request->status / 100) == 3 ) {
			// Clean up the query string before creeating the new request
			unset($_GET[GOBE_QUERY_PATH]);
			unset($_GET[GOBE_QUERY_STATUS]);
			
			header("Location: " . $url_parts['path'] . '?' .http_build_query($_GET), true, $request->status);
			die;
		}
		
		// Set the new status in the HTTP header
//		header("gobe-status", true, $request->status);
		
		return $request;
	}
	
	public function __toString() {
		return $this->getUrl();
	}
}
