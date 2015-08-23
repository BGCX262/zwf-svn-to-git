<?php

/**
 * DTO to define a rewritable URL route
 * 
 * @author Justin Johnson <justin@booleangate.org>
 * @version 20100202 <johnsonj>
 * @package gobe.route
 */
class Gobe_Route {
	public $pattern;
	public $rewrite;
	public $status;
	public $is_last;
	
	public function __construct($pattern, $rewrite, $status=false, $is_last=false) {
		$this->pattern = $pattern;
		$this->rewrite = $rewrite;
		$this->status  = $status;
		$this->is_last = $is_last;
	}
}