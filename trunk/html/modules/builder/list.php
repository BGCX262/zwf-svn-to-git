<?php
/**
 * @abstract Centralized list handling.
 * 
 * @author Justin Johnson <johnsonj>
 * @version 0.5.1 20080428 JJ
 * @version 0.4.0 20080427 JJ
 * @version 0.1.0 20080421 JJ
 * 
 * @package zk.modules.builder.list
 */

include_gobe_module('builder.interface');


class ListBuilder
	implements Builder_static {
	
	
	/**
	 * @abatract Adds an item to a list.
	 * 
	 * @param mixed $listID The ID of the list to use.
	 * @param mixed $data The data to add to the list.
	 * @param mixed $index Optional. An exact index to insert $data at (default: null). If null, $data is prepended to the list.
	 * 
	 * @return bool Always returns true.
	 */
	public static function add($listID, $data, $index=null) {
		return ListBuilder::handle($listID, HANDLE_ADD, $data, $index);
	}
	
	
	/**
	 * @abatract Removes a list or an item from a list.
	 * 
	 * @param mixed $listID The ID of the list to remove.
	 * @param mixed $index Optional. If not null, only this index will be removed (default: null).
	 * 
	 * @return bool True when successfull, false if $listID or $index did not exist.
	 */
	public static function remove($listID, $index=null) {
		return ListBuilder::handle($listID, HANDLE_DEL, null, $index);
	}
	
	
	/**
	 * @abatract Updates an existing item in a list.
	 * 
	 * @param mixed $listID The ID of the list to update.
	 * @param mixed $index The index within the list to update.
	 * @param mixed $data The new data.
	 * 
	 * @return bool True when successfull, false if $listID or $index did not exist.
	 */
	public static function update($listID, $index, $data) {
		return ListBuilder::handle($listID, HANDLE_MOD, $data, $index);
	}
	
	
	/**
	 * @abstract Gets the a specified list.  Requires at least one (1) listID.
	 *  
	 * @return The list as indicated by the provided listID.  If more than one (1) listID is provided,
	 * an associative array will be returned with listID's as indexes.
	 */
	public static function build() {
		$listIDs = func_get_args();
		$c       = count($listIDs);
		$lists   = array();
		
		// No id's specified
		if ( $c == 0 ) {
			return array(false);
		}
		
		// Get the list for each ID provided
		foreach ( $listIDs as $listID ) {
			$lists[$listID] = ListBuilder::handle($listID, HANDLE_GET);
		}
		
		// If only 1 ID was provided, return just that list; otherwise, return an array of lists.
		return $c == 1 ? $lists[$listIDs[0]] : $lists;
	}
	

	/**
	 * @abstract Handles all internal list activity.
	 * 
	 * @param mixed $listID The ID of the list.
	 * @param flag $mode The operating mode (HANDLE_ADD, HANDLE_MOD, HANDLE_DEL, or HANDLE_GET).
	 * @param mixed $data Optional. The data to be stored at the specified location (default: null).  Used only when adding and 
	 * modifying.
	 * @param mixed $index Optional. An exact index with the list represented by $listID to be modified, removing, retreived or to have 
	 * data inserted at.
	 * 
	 * @return mixed Returns true when adding, modifying, or removing have completed successfully.  Returns an array or index of an 
	 * array (mixed) when getting a list (or index thereof).  Returns false otherwise.
	 */	
	private static function handle($listID, $mode, $data=null, $index=null) {
		static $list = array();

		switch ( $mode ) {
			case HANDLE_ADD:
				// No key specified, append
				if ( is_null($index) ) {
					$list[$listID][] = $data;
				}
				// Key specified, insert at $index
				else {
					$list[$listID][$index] = $data;
				}
				return true;
				
			case HANDLE_MOD:
				// Make sure the list ID exists
				if ( isset($list[$listID]) ) {
					$list[$listID][$index] = $data;
					return true;
				}
				break;
			
			case HANDLE_DEL:
				if ( isset($list[$listID]) ) {
					// Delete the whole list
					if ( is_null($index) ) {
						unset($list[$listID]);
						return true;
					}
					// Delete just an index of the list
					elseif ( isset($list[$listID][$index]) ) {
						unset($list[$listID][$index]);
						return true;
					}
				}
				break;
			
			case HANDLE_GET:
				if ( isset($list[$listID]) ) {
					// Delete the whole list
					if ( is_null($index) ) {
						return $list[$listID];
					}
					// Get just an index of the list
					elseif ( isset($list[$listID][$index]) ) {
						return $list[$listID][$index];
					}
				}
		}
		
		// Default response (invalid mode, id, or key)
		return false;
	}
}


