<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\Results\Articles;
use Akeeba\DocImport\Site\Model\Search\Results\DocImportArticles;
use Akeeba\DocImport\Site\Model\Search\Results\Tickets;
use JDatabaseQuery;
use FOF30\Model\Model;

class Search extends Model
{
	/**
	 * Finds Joomla! articles in the specified categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of Joomla! category IDs to look into
	 * @param   int     $limitstart  Search result to start from, default 0
	 * @param   int     $limit       Maximum number of articles to return, default 10
	 *
	 * @return  Articles[]  Array of Articles objects
	 */
	public function findJoomlaArticles($search, array $categories, $limitstart = 0, $limit = 10)
	{
		// Initialize return
		$ret = [];

		// We need to be told which categories to search in
		if (empty($categories))
		{
			return $ret;
		}

		// We need to search for something
		if (empty($search))
		{
			return $ret;
		}

		// Get the db object
		$db = $this->container->db;

		$query = $this->getJoomlaArticlesQuery($search, $categories);

		// Search the database
		try
		{
			$ret = $db->setQuery($query, $limitstart, $limit)->loadObjectList('', '\\Akeeba\\DocImport\\Site\\Model\\Search\\Results\\Articles');

			return empty($ret) ? [] : $ret;
		}
		catch (\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Counts total found Joomla! articles in the specified categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of Joomla! category IDs to look into
	 *
	 * @return  int
	 */
	public function countJoomlaArticles($search, array $categories)
	{
		// We need to be told which categories to search in
		if (empty($categories))
		{
			return 0;
		}

		// We need to search for something
		if (empty($search))
		{
			return 0;
		}

		// Get the db object
		$db = $this->container->db;

		$query = $this->getJoomlaArticlesQuery($search, $categories, true);

		// Search the database
		try
		{
			return (int)($db->setQuery($query)->loadResult());
		}
		catch (\Exception $e)
		{
			return 0;
		}
	}

	/**
	 * Finds DocImport articles in the specified categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of DocImport category IDs to look into
	 * @param   int     $limitstart  Search result to start from, default 0
	 * @param   int     $limit       Maximum number of articles to return, default 10
	 *
	 * @return  DocImportArticles[]  Array of DocImportArticles objects
	 */
	public function findDocImportArticles($search, array $categories, $limitstart = 0, $limit = 10)
	{
		// Initialize return
		$ret = [];

		// We need to be told which categories to search in
		if (empty($categories))
		{
			return $ret;
		}

		// We need to search for something
		if (empty($search))
		{
			return $ret;
		}

		// Get the db object
		$db    = $this->container->db;
		$query = $this->getDocImportArticlesQuery($search, $categories);

		try
		{
			$ret = $db->setQuery($query, $limitstart, $limit)->loadObjectList('', '\\Akeeba\\DocImport\\Site\\Model\\Search\\Results\\DocImportArticles');

			return empty($ret) ? [] : $ret;
		}
		catch(\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Counts total found DocImport articles in the specified categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of DocImport category IDs to look into
	 *
	 * @return  int
	 */
	public function countDocImportArticles($search, array $categories)
	{
		// We need to be told which categories to search in
		if (empty($categories))
		{
			return 0;
		}

		// We need to search for something
		if (empty($search))
		{
			return 0;
		}

		// Get the db object
		$db = $this->container->db;

		$query = $this->getDocImportArticlesQuery($search, $categories, true);

		// Search the database
		try
		{
			return (int)($db->setQuery($query)->loadResult());
		}
		catch (\Exception $e)
		{
			return 0;
		}
	}

	/**
	 * Finds ATS posts/tickets in the specified ATS categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of Joomla! category IDs to look into (ATS uses com_categories)
	 * @param   int     $limitstart  Search result to start from, default 0
	 * @param   int     $limit       Maximum number of articles to return, default 10
	 *
	 * @return  Tickets[]  Array of Tickets objects
	 */
	public function findTickets($search, array $categories, $limitstart = 0, $limit = 10)
	{
		// Initialize return
		$ret = [];

		// We need to be told which categories to search in
		if (empty($categories))
		{
			return $ret;
		}

		// We need to search for something
		if (empty($search))
		{
			return $ret;
		}

		// Get the db object
		$db    = $this->container->db;
		$query = $this->getTicketsQuery($search, $categories);

		try
		{
			$ret = $db->setQuery($query, $limitstart, $limit)->loadObjectList('', '\\Akeeba\\DocImport\\Site\\Model\\Search\\Results\\Tickets');

			return empty($ret) ? [] : $ret;
		}
		catch(\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Counts total found ATS tickets in the specified categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of DocImport category IDs to look into
	 *
	 * @return  int
	 */
	public function countTickets($search, array $categories)
	{
		// We need to be told which categories to search in
		if (empty($categories))
		{
			return 0;
		}

		// We need to search for something
		if (empty($search))
		{
			return 0;
		}

		// Get the db object
		$db = $this->container->db;

		$query = $this->getTicketsQuery($search, $categories, true);

		// Search the database
		try
		{
			return (int)($db->setQuery($query)->loadResult());
		}
		catch (\Exception $e)
		{
			return 0;
		}
	}

	/**
	 * Filters a query by front-end language
	 *
	 * @param   \JDatabaseQuery  $query          The query to filter
	 * @param   string           $languageField  The name of the language field in the query, default is "language"
	 *
	 * @return  void  The $query object is modified directly
	 *
	 * @throws  \Exception
	 */
	private function filterByLanguage(\JDatabaseQuery &$query, $languageField = 'language')
	{
		// Make sure the field actually exists AND we're not in CLI
		if ($this->getContainer()->platform->isCli())
		{
			return;
		}

		/** @var \JApplicationSite $app */
		$app               = \JFactory::getApplication();
		$hasLanguageFilter = method_exists($app, 'getLanguageFilter');

		if ($hasLanguageFilter)
		{
			$hasLanguageFilter = $app->getLanguageFilter();
		}

		if (!$hasLanguageFilter)
		{
			return;
		}

		// Ask Joomla for the plugin only if we don't already have it. Useful for tests
		if (!$this->lang_filter_plugin)
		{
			$this->lang_filter_plugin = \JPluginHelper::getPlugin('system', 'languagefilter');
		}

		$lang_filter_params = new \JRegistry($this->lang_filter_plugin->params);

		$languages = array('*');

		if ($lang_filter_params->get('remove_default_prefix'))
		{
			// Get default site language
			$platform    = $this->container->platform;
			$lg          = $platform->getLanguage();
			$languages[] = $lg->getTag();
		}
		else
		{
			// We have to use JInput since the language fragment is not set in the $_REQUEST, thus we won't have it in our model
			$languages[] = \JFactory::getApplication()->input->getCmd('language', '*');
		}

		// Filter out double languages
		$languages = array_unique($languages);

		// And filter the query output by these languages
		$languages = array_map(array($query, 'quote'), $languages);
		$query->where($query->qn($languageField) . ' IN(' . implode(',', $languages) . ')');
	}

	/**
	 * Gets the SQL query for Joomla! articles
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of Joomla! category IDs to look into
	 * @param   bool    $onlyCount   True to return a COUNT(*) query
	 *
	 * @return  JDatabaseQuery
	 */
	private function getJoomlaArticlesQuery($search, array $categories, $onlyCount = false)
	{
		// Get the db object
		$db = $this->container->db;

		// Get the authorized user access levels
		$accessLevels = \JFactory::getUser()->getAuthorisedViewLevels();
		$accessLevels = array_map([$db, 'quote'], $accessLevels);

		// Sanitize categories
		$categories = array_map(function ($c) use ($db)
		{
			$c = trim($c);
			$c = (int)$c;

			return $db->q($c);
		}, $categories);
		$categories = array_unique($categories);

		// Get the column collation for searches
		$collation = $db->hasUTF8mb4Support() ? 'utf8mb4_unicode_ci' : 'utf8_general_ci';

		// Search the content table
		$query = $db->getQuery(true)
					->select([
						$db->qn('a.id'),
						$db->qn('a.title'),
						$db->qn('a.alias'),
						$db->qn('a.catid'),
						$db->qn('c.title', 'catname'),
						$db->qn('c.alias', 'catalias'),
						$db->qn('a.introtext'),
						$db->qn('a.fulltext'),
						$db->qn('a.language'),
						$db->qn('a.created'),
						$db->qn('a.modified'),
					])
					->from($db->qn('#__content', 'a'))
					->innerJoin($db->qn('#__categories', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('a.catid') . ')')
					->where($db->qn('a.state') . ' = ' . $db->q(1))
					->where($db->qn('c.published') . ' = ' . $db->q(1))
					->where($db->qn('a.access') . ' IN(' . implode(',', $accessLevels) . ')')
					->where($db->qn('a.catid') . ' IN(' . implode(',', $categories) . ')')
					->where('(' .
						$db->qn('a.title') . ' LIKE ' . $db->q("%$search%") . ' COLLATE ' . $collation .
						' OR ' . $db->qn('a.introtext') . ' LIKE ' . $db->q("%$search%") . ' COLLATE ' . $collation .
						' OR ' . $db->qn('a.fulltext') . ' LIKE ' . $db->q("%$search%") . ' COLLATE ' . $collation .
						' OR ' . $db->qn('c.title') . ' LIKE ' . $db->q("%$search%") . ' COLLATE ' . $collation .
						' OR ' . $db->qn('c.description') . ' LIKE ' . $db->q("%$search%") . ' COLLATE ' . $collation
						. ')');

		// Filter query by language
		$this->filterByLanguage($query);

		if ($onlyCount)
		{
			$query->clear('select');
			$query->clear('order');
			$query->select('COUNT(*)');
		}

		return $query;
	}

	/**
	 * Gets the SQL query for DocImport articles
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of DocImport category IDs to look into
	 * @param   bool    $onlyCount   True to return a COUNT(*) query
	 *
	 * @return  JDatabaseQuery
	 */
	private function getDocImportArticlesQuery($search, array $categories, $onlyCount = false)
	{
		// Get the db object
		$db    = $this->container->db;

		// Get the authorized user access levels
		$accessLevels = \JFactory::getUser()->getAuthorisedViewLevels();
		$accessLevels = array_map([$db, 'quote'], $accessLevels);

		// Sanitize categories
		$categories = array_map(function ($c) use ($db)
		{
			$c = trim($c);
			$c = (int)$c;

			return $db->q($c);
		}, $categories);
		$categories = array_unique($categories);

		// Get the search query
		$query = $db->getQuery(true)
					->select(array(
						$db->qn('docimport_article_id', 'id'),
						$db->qn('a.title'),
						$db->qn('a.slug'),
						$db->qn('docimport_category_id', 'catid'),
						$db->qn('c.title', 'cattitle'),
						$db->qn('c.slug', 'catslug'),
						$db->qn('a.fulltext'),
						$db->qn('a.created_on'),
						$db->qn('a.modified_on'),
						'MATCH(' . $db->qn('fulltext') . ') AGAINST (' . $db->q($search) . ') as ' . $db->qn('score')
					))
					->from($db->qn('#__docimport_articles', 'a'))
					->innerJoin($db->qn('#__docimport_categories', 'c') . ' USING(' . $db->qn('docimport_category_id') . ')')
					->where($db->qn('a.enabled') . ' = ' . $db->qn(1))
					->where($db->qn('c.enabled') . ' = ' . $db->qn(1))
					->where($db->qn('c.access') . ' IN (' . implode(',', $accessLevels) . ')')
					->where($db->qn('docimport_category_id') . ' IN (' . implode(',', $categories) . ')')
					->where('MATCH(' . $db->qn('fulltext') . ') AGAINST (' . $db->q($search) . ')')
					->order($db->qn('score') . ' DESC');

		$this->filterByLanguage($query);

		if ($onlyCount)
		{
			$query->clear('select');
			$query->clear('order');
			$query->select('COUNT(*)');
		}

		return $query;
	}

	/**
	 * Gets the SQL query for ATS tickets
	 *
	 * Finds ATS posts/tickets in the specified ATS categories using the provided search criteria
	 *
	 * @param   string  $search      String to search for
	 * @param   array   $categories  A list of Joomla! category IDs to look into (ATS uses com_categories)
	 * @param   bool    $onlyCount   True to return a COUNT(*) query
	 *
	 * @return  JDatabaseQuery
	 */
	private function getTicketsQuery($search, array $categories, $onlyCount = false)
	{
		// Get the db object
		$db    = $this->container->db;

		// Get the authorized user access levels
		$accessLevels = \JFactory::getUser()->getAuthorisedViewLevels();
		$accessLevels = array_map([$db, 'quote'], $accessLevels);

		// Sanitize categories
		$categories = array_map(function ($c) use ($db)
		{
			$c = trim($c);
			$c = (int)$c;

			return $db->q($c);
		}, $categories);
		$categories = array_unique($categories);

		// Get the search query
		$query = $db->getQuery(true)
					->select(array(
						$db->qn('t.ats_ticket_id', 'id'),
						$db->qn('t.title'),
						$db->qn('t.alias', 'slug'),
						$db->qn('p.content_html', 'fulltext'),
						$db->qn('p.created_on'),
						$db->qn('p.modified_on'),
						$db->qn('p.ats_post_id', 'pid'),
						$db->qn('t.catid'),
						$db->qn('c.title', 'catname'),
						$db->qn('c.alias', 'catslug'),
						$db->qn('c.language', 'language'),
						'MATCH(' . $db->qn('content_html') . ') AGAINST (' . $db->q($search) . ') ' .
						'* (730 - DATEDIFF(NOW(), ' . $db->qn('t.created_on') . ')) AS ' . $db->qn('score')
					))
					->from($db->qn('#__ats_posts', 'p'))
					->innerJoin($db->qn('#__ats_tickets', 't') . ' USING(' . $db->qn('docimport_category_id') . ')')
					->innerJoin($db->qn('#__categories', 'c') . ' ON(' . $db->qn('c.id') . ' = ' . $db->qn('t.catid') . ')')
					->where($db->qn('c.id') . ' IN (' . implode(',', $categories) . ')')
					->where($db->qn('c.published') . ' = ' . $db->qn(1))
					->where($db->qn('c.access') . ' IN (' . implode(',', $accessLevels) . ')')
					->where($db->qn('t.enabled') . ' = ' . $db->qn(1))
					->where($db->qn('t.public') . ' = ' . $db->qn(1))
					->where($db->qn('t.status') . ' IN(' . $db->qn('P') . ',' . $db->qn('C') . ')')
					->where($db->qn('t.created_on') . ' >= NOW() - INTERVAL 2 YEAR')
					->where($db->qn('p.enabled') . ' = ' . $db->qn(1))
					->where('MATCH(' . $db->qn('content_html') . ') AGAINST (' . $db->q($search) . ')')
					->order($db->qn('score') . ' DESC');

		$this->filterByLanguage($query);

		if ($onlyCount)
		{
			$query->clear('select');
			$query->clear('order');
			$query->select('COUNT(*)');
		}

		return $query;
	}
}