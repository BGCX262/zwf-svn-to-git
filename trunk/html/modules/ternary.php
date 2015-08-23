<?php
/**
 * @abstract Ternary operators for us only in templates.  Do not use these in server-side scripting.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.1.1 20080430 JJ
 * @version 0.1.0 20080428 JJ
 * 
 * @package zk.modules.ternary
 */



/**
 * @abstract Ternary operators
 */
class Ternary {
	public static function eq($lvalue, $rvalue, $trueCase, $falseCase='') {
		return ($lvalue == $rvalue ? $trueCase : $falseCase);
	}
	
	public static function neq($lvalue, $rvalue, $trueCase, $falseCase='') {
		return ($lvalue != $rvalue ? $trueCase : $falseCase);
	}
	
	public static function gt($lvalue, $rvalue, $trueCase, $falseCase='') {
		return ($lvalue == $rvalue ? $trueCase : $falseCase);
	}
	
	public static function gte($lvalue, $rvalue, $trueCase, $falseCase='') {
		return ($lvalue >= $rvalue ? $trueCase : $falseCase);
	}
	
	public static function lt($lvalue, $rvalue, $trueCase, $falseCase='') {
		return ($lvalue < $rvalue ? $trueCase : $falseCase);
	}
	public static function lte($lvalue, $rvalue, $trueCase, $falseCase='') {
		return ($lvalue <= $rvalue ? $trueCase : $falseCase);
	}
}


if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback('ternary' , 'eq'  , array('Ternary', 'eq'));
	add_gobe_callback('ternary' , 'neq' , array('Ternary', 'neq'));
	add_gobe_callback('ternary' , 'gt'  , array('Ternary', 'gt'));
	add_gobe_callback('ternary' , 'gte' , array('Ternary', 'gte'));
	add_gobe_callback('ternary' , 'lt'  , array('Ternary', 'lt'));
	add_gobe_callback('ternary' , 'lte' , array('Ternary', 'lte'));
}

