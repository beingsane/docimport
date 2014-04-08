<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerCpanels extends F0FController
{
	public function execute($task) {
		$task = 'browse';
		parent::execute($task);
	}

	public function onBeforeBrowse()
	{
		// Run maintenance tasks
		$this->getThisModel()
			->updateMagicParameters()
			->checkAndFixDatabase()
			->refreshUpdateSite();

		F0FModel::getTmpInstance('Xsl','DocimportModel')
			->scanCategories();

		return true;
	}
}