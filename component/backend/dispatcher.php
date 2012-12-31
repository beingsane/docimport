<?php
/**
 * @package AkeebaSubs
 * @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportDispatcher extends FOFDispatcher
{
	public function onBeforeDispatch() {
		if($result = parent::onBeforeDispatch()) {
			// Load Akeeba Strapper
			include_once JPATH_ROOT.'/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addCSSfile('media://com_docimport/css/backend.css');
		}
		return $result;
	}
}