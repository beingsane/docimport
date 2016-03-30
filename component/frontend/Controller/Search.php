<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use FOF30\Controller\Controller;

class Search extends Controller
{
	public function main()
	{
		// Get query string parameters
		$search       = $this->input->getString('search', '');
		$supportAreas = $this->input->get('areas', [], 'array');
		$limit        = $this->input->getInt('limit', '10');
		$limitStart   = $this->input->getInt('limitstart', '0');

		// Make sure the limit is between 5 and 100
		$limit = max($limit, 5);
		$limit = min($limit, 100);

		/** @var \Akeeba\DocImport\Site\Model\Search $model */
		$model = $this->getModel();

		// Set the model state and perform the actual search
		$model->setState('search', $search);
		$model->setState('areas', $supportAreas);
		$model->setState('limit', $limit);
		$model->setState('limitStart', $limitStart);

		if ($search)
		{
			$model->produceSearchResults();
		}

		// Render the view (non-cacheable)
		$this->display(false);
	}
}