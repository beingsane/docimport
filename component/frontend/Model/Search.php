<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\CategoriesConfiguration;
use Akeeba\DocImport\Site\Model\Search\SearchSection;
use FOF30\Model\Model;
use JApplicationCms;
use JPagination;

class Search extends Model
{
	/**
	 * The search results. It is a hash array of arrays in the format:
	 * [ 'sectionName' => ['items' => ResultInterface[], 'count' => int], ... ]
	 *
	 * @var  array
	 */
	public $searchResults = [];

	public function produceSearchResults()
	{
		$query      = $this->getState('search', '', 'string');
		$areas      = $this->getState('areas', [], 'array');
		$limitStart = $this->getState('limitStart', 0, 'int');
		$limit      = $this->getState('limit', 10, 'int');

		$this->searchResults = [];

		$catConfig    = new CategoriesConfiguration($this->container);
		$sectionNames = SearchSection::getSections();

		foreach ($sectionNames as $sectionName)
		{
			$section = new SearchSection($this->container, $sectionName, $areas, $catConfig);

			$this->searchResults[ $sectionName ] = [
				'items' => $section->getItems($query, $limitStart, $limit),
				'count' => $section->getCount($query),
			];
		}
	}

	/**
	 * Get the pagination results for the composite search query
	 *
	 * @param   string          $prefix
	 * @param   JApplicationCms $app
	 *
	 * @return  JPagination
	 */
	public function getPagination($prefix = '', $app = null)
	{
		$limitStart = $this->getState('limitStart', 0, 'int');
		$limit      = $this->getState('limit', 10, 'int');

		// Find the maximum number of items
		$maxCount = 0;

		foreach ($this->searchResults as $sectionName => $sectionResults)
		{
			$maxCount = max($maxCount, $sectionResults['count']);
		}

		return new \JPagination($maxCount, $limitStart, $limit, $prefix, $app);
	}
}