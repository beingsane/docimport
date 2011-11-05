<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerUrls extends JController
{
	public function nuke()
	{
		FOFModel::getAnInstance('Urls','DocimportModel')
			->nuke();
		
		$this->setRedirect('index.php?option=com_docimport', JText::_('COM_DOCIMPORT_CPANEL_NUKEURLS_DONE'));
	}
}