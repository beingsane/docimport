<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use FOF30\Controller\Controller;

class Search extends Controller
{
	public function search()
	{
		// Get query string parameters
		$search      = $this->input->getString('search', '');
		$limit       = $this->input->getInt('limit', '10');
		$limitStart  = $this->input->getInt('limitstart', '0');
		$supportArea = $this->input->getCmd('area', '');

		// Make sure the limit is between 5 and 100
		$limit  = max($limit, 5);
		$limit  = min($limit, 100);

		/** @var \Akeeba\DocImport\Site\Model\Search $model */
		$model = $this->getModel();

		// TODO Get categories based on support area
		$categories = [
			'joomla' => [1204, 1205, 1207],
			'docimport' => [11, 1, 3, 7],
		];
		//$categories = $model->getCategoriesFor($supportArea);


		return json_encode([
			'joomla'    => $model->findJoomlaArticles($search, $categories['joomla'], $limitStart,$limit),
			'docimport' => $model->findDocImportArticles($search, $categories['docimport'], $limitStart,$limit),
			'ats'       => $model->findTickets($search, $categories['ats'], $limitStart,$limit),
		]);
	}
}