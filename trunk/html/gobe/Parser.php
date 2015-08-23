<?php

require_once("Parser/Abstract.php");

/**
 * Mechanism for getting a Gobe page parser
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 2.0.0 20100205 (johnsonj)
 * @package gobe.parser
 */
class Gobe_Parser {
	static public function factory($type) {
		require_once("Parser/$type.php");
		$type = "Gobe_Parser_$type";
		
		return new $type();
	}
}