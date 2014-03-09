<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportModelArticles extends FOFModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(array(
				$db->quoteName('a').'.*',
				$db->quoteName('c').'.'.$db->quoteName('title').' AS '.$db->quoteName('category_title')
			))
			->from($db->quoteName('#__docimport_articles').' AS '.$db->quoteName('a'))
			->join('INNER', $db->quoteName('#__docimport_categories').' AS '.$db->quoteName('c').
					' USING ('.$db->quoteName('docimport_category_id').')');
		;

		$search = $this->getState('search', null,'string');
		if(!empty($search)) {
			$query->where(
				$db->quoteName('a').'.'.$db->quoteName('title').' LIKE '.$db->quote('%'.$search.'%')
			);
		}
		
		$enabled = $this->getState('enabled',null,'cmd');
		if(is_numeric($enabled) && ($enabled > 0)) {
			$query->where(
				$db->quoteName('a').'.'.$db->quoteName('enabled').' = '.$db->quote($enabled)
			);
		}
		
		$category = $this->getState('category',null,'cmd');
		if(is_numeric($category) && ($category > 0)) {
			$query->where(
				$db->quoteName('a').'.'.$db->quoteName('docimport_category_id').' = '.$db->quote($category)
			);
		}
		
		$slug = $this->getState('slug',null,'string');
		if(!empty($slug)) {
			$query->where(
				$db->quoteName('a').'.'.$db->quoteName('slug').' = '.$db->quote($slug)
			);
		}
		
		$language = $this->getState('language',null,'array');
		if(empty($language)) $language = $this->getState('language',null,'string');
		if(!empty($language) && (is_array($language) ? (!empty($language[0])) : true) ) {
			if(is_array($language)) {
				$langs = array();
				foreach($language as $l) {
					$langs[] = $db->quote($l);
				}
				$query->where(
					$db->quoteName('c').'.'.$db->quoteName('language').' IN ('.implode(',',$langs).')'
				);
			} else {
				$query->where(
					$db->quoteName('c').'.'.$db->quoteName('language').' = '.$db->quote($language)
				);
			}
		}
		
		$order = $this->getState('filter_order', 'docimport_article_id', 'cmd');
		if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'docimport_article_id';
		$order = $db->quoteName('a').'.'.$db->quoteName($order);
		$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
		$query->order( $order.' '.$dir);
		return $query;
	}
}