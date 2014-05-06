<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerUrls extends F0FController
{
	public function nuke()
	{
		F0FModel::getAnInstance('Urls','DocimportModel')
			->nuke();

		$this->setRedirect('index.php?option=com_docimport', JText::_('COM_DOCIMPORT_CPANEL_NUKEURLS_DONE'));
	}
}