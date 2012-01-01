<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

/**
 * DocImport Search plugin
 */
class plgSearchDocimport extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas() {
		static $areas = array(
			'docimport' => 'PLG_SEARCH_DOCIMPORT_DOCIMPORT'
		);
		return $areas;
	}
	
	/**
	 * DocImport Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$searchText = $text;

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}
		
		$limit			= $this->params->get('search_limit',		50);
		
		$text = trim($text);
		if ($text == '') {
			return array();
		}
		$section	= JText::_('PLG_SEARCH_DOCIMPORT_DOCIMPORT');
		
		$query = $db->getQuery(true);
		
		switch ($phrase)
		{
			case 'exact':
				$text		= $db->Quote('%'.$db->getEscaped($text, true).'%', false);
				$query->where(
					'(('.$db->nq('a').'.'.$db->nq('title').' LIKE '.$text.')'
					.' OR '.
					'('.$db->nq('a').'.'.$db->nq('fulltext').' LIKE '.$text.'))'
				);
				break;

			case 'all':
			default:
				$words	= explode(' ', $text);
				foreach ($words as $word)
				{
					$word		= $db->Quote('%'.$db->getEscaped($word, true).'%', false);
					$query->where(
						'(('.$db->nq('a').'.'.$db->nq('title').' LIKE '.$word.')'
						.' OR '.
						'('.$db->nq('a').'.'.$db->nq('fulltext').' LIKE '.$word.'))'
					);
				}
				break;

			case 'any':
				$words	= explode(' ', $text);
				foreach ($words as $word)
				{
					$word		= $db->Quote('%'.$db->getEscaped($word, true).'%', false);
					$query->where(
						'(('.$db->nq('a').'.'.$db->nq('title').' LIKE '.$word.')'
						.' OR '.
						'('.$db->nq('a').'.'.$db->nq('fulltext').' LIKE '.$word.'))',
						'OR'
					);
				}
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created_on ASC';
				break;

			case 'popular':
				// @todo Implement a hits field
				$order = 'a.created_on ASC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'cattitle ASC, title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created_on DESC';
		}
		
		$query->select(array(
			$db->nq('a').'.'.$db->nq('docimport_article_id'),
			$db->nq('a').'.'.$db->nq('title').' AS '.$db->nq('title'),
			$db->nq('c').'.'.$db->nq('title').' AS '.$db->nq('cattitle'),
			'CONCAT_WS(" / ", '.$db->q($section).', c.title) AS section',
			$db->nq('a').'.'.$db->nq('created_on').' AS '.$db->nq('created'),
			$db->nq('a').'.'.$db->nq('fulltext').' AS '.$db->nq('text'),
			$db->q('1').' AS '.$db->nq('browsernav')
		))->from($db->nq('#__docimport_articles').' AS '.$db->nq('a'))
		->join('INNER',
			$db->nq('#__docimport_categories').' AS '.$db->nq('c').' ON('.
			$db->nq('c').'.'.$db->nq('docimport_category_id').' = '.
			$db->nq('a').'.'.$db->nq('docimport_category_id').')'
		)->where($db->nq('access').' IN ('.$groups.')')
		->where('a.enabled = 1')
		->where('c.enabled = 1')
		->order($order);
		;
		
		// Filter by language
		if ($app->isSite() && $app->getLanguageFilter()) {
			$tag = JFactory::getLanguage()->getTag();
			$query->where('c.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
		}
		
		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		$return = array();
		if ($rows) {
			foreach($rows as $key => $row) {
				$rows[$key]->href = JRoute::_('index.php?option=com_docimport&view=article&id='.$row->docimport_article_id);
			}

			foreach($rows as $key => $article) {
				if (searchHelper::checkNoHTML($article, $searchText, array('url', 'text', 'title'))) {
					$return[] = $article;
				}
			}
		}

		return $return;
	}
}