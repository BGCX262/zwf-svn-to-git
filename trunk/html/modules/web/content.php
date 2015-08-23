<?php
/**
 * @abstract HTML Page functionality
 * 
 * @author Justin Johnson <johnsonj>
 * @version 2.1.0 20080428 JJ
 * 
 * @package zk.modules.web.html
 */




class ContentStub {
	private $content;
	private $delayedContent;
	
	
	public function __construct() {
		$this->content        = array();
		$this->delayedContent = array();
	}
	
	
	public function add($pane, $content, $delay=false) {
		if ( $delay ) {
			$this->delayedContent[$pane][] = $content;
		} else {
			$this->content[$pane][] = $content;
		}
		
		return '';
	}
	
	
	public function display($pane, $truncate=false) {
		$content = parse_gobe_goat(
			(isset($this->content[$pane])        ? implode("\n", $this->content[$pane])        : '') .
			(isset($this->delayedContent[$pane]) ? implode("\n", $this->delayedContent[$pane]) : '')
		);
		
		if ( $truncate ) {
			$this->remove($pane);
		}
	
		return $content;
	}
	
	
	public function remove($pane=null) {
		if ( !is_null($pane) ) {
			unset($this->content[$pane]);
			unset($this->delayedContent[$pane]);
		} else {
			$this->content        = array();
			$this->delayedContent = array();
		}
	}
}



if ( function_exists('add_gobe_callback') ) {
	global $__ContentStub;
	$__ContentStub = new ContentStub();
	
	add_gobe_callback('content', 'display', array($__ContentStub, 'display'));
	add_gobe_callback('content', 'add'    , array($__ContentStub, 'add'));
	add_gobe_callback('content', 'remove' , array($__ContentStub, 'remove'));
}


