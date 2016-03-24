<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
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

	public function onBeforeRead()
	{
		$id = $this->input->getInt('id', 0);

		if ($id === 0)
		{
			$menu       = \JMenu::getInstance('site')->getActive();
			$menuparams = $menu->params;

			if (!($menuparams instanceof \JRegistry))
			{
				$x = new \JRegistry();
				$x->loadString($menuparams, 'JSON');
				$menuparams = $x;
			}

			$id = $menuparams->get('catid', 0);
			$this->input->set('id', $id);
		}
	}
}