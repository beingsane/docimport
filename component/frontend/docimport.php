<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Include the component versioning
require_once JPATH_COMPONENT_ADMINISTRATOR.'/version.php';

// Include FOF
if(!defined('FOF_INCLUDED')) {
	include_once JPATH_LIBRARIES.'/fof/include.php';
}

// Dispatch
FOFDispatcher::getAnInstance('com_docimport')->dispatch();