<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

if(!class_exists('JoomlaSucksController')) {
	if(interface_exists('JController')) {
		abstract class JoomlaSucksController extends JControllerLegacy {}
	} else {
		class JoomlaSucksController extends JController {}
	}
}

class DocimportControllerUrls extends JoomlaSucksController
{
	public function nuke()
	{
		FOFModel::getAnInstance('Urls','DocimportModel')
			->nuke();
		
		$this->setRedirect('index.php?option=com_docimport', JText::_('COM_DOCIMPORT_CPANEL_NUKEURLS_DONE'));
	}
}