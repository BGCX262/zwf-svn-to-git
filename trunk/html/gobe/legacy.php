<?php

/**
 * Defines legacy module and templating methods for backward compatibility
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100205 (johnsonj)
 * @package gobe.legacy
 */

if ( !function_exists("include_gobe_module") ) {
	function include_gobe_module($module) {
		Gobe::getInstance()->getModules()->register($module);
	}
}

if ( !function_exists("add_gobe_variable") ) {
	function add_gobe_variable($name, $value) {
		Gobe::getInstance()->getParser()->setVariable($name, $value);
	}
}
	
if ( !function_exists("add_gobe_callback") ) {
	function add_gobe_callback($group, $name, $callback) {
		Gobe::getInstance()->getParser()->setCallback($group, $name, $callback);	
	}
}
	
if ( !function_exists("register_gobe_module_error") ) {
	function register_gobe_module_error($error) {
		trigger_error($error, E_USER_NOTICE);
	}
}

if ( !function_exists("register_gobe_debug") ) {
	function register_gobe_debug() {
		
	}
}

if ( !function_exists("register_gobe_debug_load_module") ) {
	function register_gobe_debug_load_module() {
		
	}
}

if ( !function_exists("set_layout") ) {
	function set_layout($layout) {
		Gobe::getInstance()->setLayout($layout);
	}
}

