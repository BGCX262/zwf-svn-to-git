<?php
/**
 * @abstract Web page navigation tools.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.3.2 20080430 JJ
 * @version 0.3.0 20080428 JJ
 * 
 * @package zk.modules.web.navigation
 */

		
class NavigationSelector {
	private $classResponse;
	private $selections;

	
	public function __construct($classResponse='selected') {
		$this->setClassResponse($classResponse);
		$this->selections = array();
	}
	
	
	public function setTeirSelection($teir, $selectedItem) {
		$this->selections[$teir] = $selectedItem;
		return '';
	}
	
	
	public function getTeirSelection($teir, $itemComparison, $classResponse=null) {
		return (isset($this->selections[$teir]) && $this->selections[$teir] == $itemComparison) 
			? (!empty($classResponse) ? $classResponse : $this->classResponse)
			: '';
	}
	
	
	public function setClassResponse($classResponse) {
		$this->classResponse = $classResponse;
		return '';
	}
}



if ( function_exists('add_gobe_callback') ) {
	global $__NavigationSelector;
	$__NavigationSelector = new NavigationSelector();
	
	add_gobe_callback('navigation', 'set-class-response', array($__NavigationSelector, 'setClassResponse'));
	add_gobe_callback('navigation', 'set-teir-selection', array($__NavigationSelector, 'setTeirSelection'));
	add_gobe_callback('navigation', 'get-teir-selection', array($__NavigationSelector, 'getTeirSelection'));
}


