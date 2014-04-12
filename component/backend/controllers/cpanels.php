<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerCpanels extends F0FController
{
	public function execute($task)
	{
		if (!in_array($task, array('updateinfo')))
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	public function onBeforeBrowse()
	{
		// Run maintenance tasks
		$this->getThisModel()
			->updateMagicParameters()
			->checkAndFixDatabase();

		// Run the automatic update site refresh
		/** @var DocimportModelUpdates $updateModel */
		$updateModel = F0FModel::getTmpInstance('Updates', 'DocimportModel');
		$updateModel->refreshUpdateSite();

		F0FModel::getTmpInstance('Xsl', 'DocimportModel')
			->scanCategories();

		return true;
	}

	public function updateinfo()
	{
		/** @var DocimportModelUpdates $updateModel */
		$updateModel = F0FModel::getTmpInstance('Updates', 'DocimportModel');
		$updateInfo = (object)$updateModel->getUpdates();

		$result = '';

		if ($updateInfo->hasUpdate)
		{
			$strings = array(
				'header'		=> JText::sprintf('COM_DOCIMPORT_CPANEL_MSG_UPDATEFOUND', $updateInfo->version),
				'button'		=> JText::sprintf('COM_DOCIMPORT_CPANEL_MSG_UPDATENOW', $updateInfo->version),
				'infourl'		=> $updateInfo->infoURL,
				'infolbl'		=> JText::_('COM_DOCIMPORT_CPANEL_MSG_MOREINFO'),
			);

			$result = <<<ENDRESULT
	<div class="alert alert-warning">
		<h3>
			<span class="icon icon-exclamation-sign glyphicon glyphicon-exclamation-sign"></span>
			{$strings['header']}
		</h3>
		<p>
			<a href="index.php?option=com_installer&view=update" class="btn btn-primary">
				{$strings['button']}
			</a>
			<a href="{$strings['infourl']}" target="_blank" class="btn btn-small btn-info">
				{$strings['infolbl']}
			</a>
		</p>
	</div>
ENDRESULT;
		}

		echo $result;

		// Cut the execution short
		JFactory::getApplication()->close();
	}
}