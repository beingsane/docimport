<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportDispatcher extends FOFDispatcher
{
	public function __construct($config = array()) {
		parent::__construct($config);

		$this->defaultView = 'categories';
	}	
	
	public function onBeforeDispatch() {
		if($result = parent::onBeforeDispatch()) {
			$view = FOFInput::getCmd('view',$this->defaultView, $this->input);
			if(empty($view) || ($view == 'cpanel')) {
				$view = 'categories';
			}
			FOFInput::setVar('view',$view,$this->input);
		}

		return $result;
	}
}