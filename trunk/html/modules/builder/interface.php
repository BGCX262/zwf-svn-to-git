<?php
/**
 * @abstract Defines the common functionality for all builder objects.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.2.0 20080421 JJ
 * @version 0.1.0 20080410 JJ
 * 
 * @package raygun.modules.builder.interface
 */


interface Builder {
	public function build();
}

interface Builder_static {
	public static function build();
}

