<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\View\Categories;

// Protect from unauthorized access
defined('_JEXEC') or die();

use FOF30\View\DataView\Html as BaseView;
use JFactory;

class Html extends BaseView
{
	/** @var   bool  Should I display page headings? */
	public $showPageHeading = false;

	/** @var   string  The page heading to display */
	public $pageHeading = '';

	protected function onBeforeBrowse()
	{
		parent::onBeforeBrowse();

		// Get the title of the active menu item, if present
		$app        = JFactory::getApplication();
		$menus      = $app->getMenu();
		$menu       = $menus->getActive();
		$menuTitle  = is_null($menu) ? '' : $menu->title;

		// Do I have to show page headings per menu item configuration?
		$pageParams            = $this->getPageParams();
		$this->showPageHeading = $pageParams->get('show_page_heading');

		if ($this->showPageHeading)
		{
			// Find our the heading text to display. If there's none, don't display the heading at all.
			$this->pageHeading     = $this->escape($pageParams->get('page_heading', $menuTitle));
			$this->showPageHeading = !empty($this->pageHeading);
		}

	}

}