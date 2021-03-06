<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Admin\Model\Articles;
use FOF30\Controller\DataController;
use FOF30\Controller\Exception\ItemNotFound;

class Category extends DataController
{
	public function onBeforeBrowse()
	{
		$this->getModel()->filter_order('ordering')->filter_order_Dir('ASC')->limit(0)->limitstart(0);
	}

	protected function getCrudTask()
	{
		$id = $this->input->getInt('id', 0);
		$catid = $this->input->getInt('catid', 0);

		if (!$id && $catid)
		{
			$this->input->set('id', $catid);
		}

		$task = parent::getCrudTask();

		if ($task == 'edit')
		{
			$task = 'read';
		}

		return $task;
	}
}