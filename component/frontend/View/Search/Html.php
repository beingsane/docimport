<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\View\Search;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search;
use FOF30\View\View as BaseView;
use JFactory;
use JPagination;

class Html extends BaseView
{
	/** @var   array  Search results */
	public $items = null;

	/** @var   JPagination  Pagination for search results */
	public $pagination;

	protected function onBeforeMain()
	{
		/** @var Search $model */
		$model = $this->getModel();

		$this->items = $model->searchResults;
		$this->pagination = $model->getPagination();
	}

}