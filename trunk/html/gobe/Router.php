<?php

/**
 * Manager for a collection of custom URL routes.
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 20100202 <johnsonj>
 * @package gobe.router
 */
class Gobe_Router {
	private $routes;
	
	public function addRoute($route) {
		if ( is_array($route) ) {
			foreach ( $route as $r ) {
				$this->addRoute($r);
			}
		} else if ( !$route instanceof Gobe_Route ) {
			trigger_error(__CLASS__ . "::addRoute() expectes a Gobe_Route as a parameter", E_USER_WARNING);
		} else {
			$this->routes[] = $route;
		}
	}
	
	public function getRoutes() {
		return $this->routes;
	}
	
	public function clearRoutes() {
		$this->routes = array();
	}
	
	static public function getInstance() {
		static $instance = null;
		
		if ( is_null($instance) ) {
			$instance = new self;
		}
		
		return $instance;
	}
	
	private function __construct() {
		$this->routes = array();
	}
	
	private function __clone() {}
}
