<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerCpanels extends FOFController
{
	public function execute($task) {
		$task = 'browse';
		parent::execute($task);
	}
	
	public function onBeforeBrowse()
	{
		FOFModel::getTmpInstance('Xsl','DocimportModel')
			->scanCategories();
		
		return true;
	}
}