<?php
/**
 * @abstract Cryptography interface definition
 * 
 * @author Justin Johnson <johnsonj>
 * @version 1.0.0 20080418 JJ
 * 
 * @package zk.modules.crypt.interface
 */

interface cryptTemplate {
	public function encrypt($text);
	public function decrypt($text);
}


interface cryptTemplate_static {
	public static function encrypt($text);
	public static function decrypt($text);
}

