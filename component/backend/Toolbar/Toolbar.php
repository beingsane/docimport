<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Toolbar;

use FOF30\Model\DataModel;
use JToolBarHelper;
use JText;

defined('_JEXEC') or die;

class Toolbar extends \FOF30\Toolbar\Toolbar
{
	public function onCategoriesBrowse()
	{
		parent::onBrowse();

		JToolBarHelper::divider();
		JToolBarHelper::preferences($this->container->componentName);
	}

	public function onArticlesBrowse()
	{
		// Set toolbar title
		$subtitle_key = $this->container->componentName . '_TITLE_ARTICLES';
		JToolBarHelper::title(JText::_($this->container->componentName) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', $this->container->bareComponentName);

		// Add toolbar buttons
		if ($this->perms->edit)
		{
			JToolBarHelper::editList();
		}

		if ($this->perms->editstate)
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
		}

		if ($this->perms->delete)
		{
			JToolBarHelper::divider();
			JToolBarHelper::deleteList();
		}

		JToolBarHelper::divider();
		JToolBarHelper::preferences($this->container->componentName);

		// A Check-In button is only added if there is a locked_on field in the table
		try
		{
			/** @var DataModel $model */
			$model = $this->container->factory->model('Articles');

			if ($model->hasField('locked_on') && $this->perms->edit)
			{
				JToolBarHelper::checkin();
			}

		}
		catch (\Exception $e)
		{
			// Yeah. Let's not add the button if we can't load the model...
		}
	}
}