<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportModelArticles extends FOFModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = FOFQueryAbstract::getNew()
			->select(array(
				$db->nameQuote('a').'.*',
				$db->nameQuote('c').'.'.$db->nameQuote('title').' AS '.$db->nameQuote('category_title')
			))
			->from($db->nameQuote('#__docimport_articles').' AS '.$db->nameQuote('a'))
			->join('INNER', $db->nameQuote('#__docimport_categories').' AS '.$db->nameQuote('c').
					' USING ('.$db->nameQuote('docimport_category_id').')');
		;

		$search = $this->getState('search', null,'string');
		if(!empty($search)) {
			$query->where(
				$db->nameQuote('a').'.'.$db->nameQuote('title').' LIKE '.$db->quote('%'.$search.'%')
			);
		}
		
		$enabled = $this->getState('enabled',null,'cmd');
		if(is_numeric($enabled)) {
			$query->where(
				$db->nameQuote('a').'.'.$db->nameQuote('enabled').' = '.$db->quote($enabled)
			);
		}
		
		$category = $this->getState('category',null,'cmd');
		if(is_numeric($category)) {
			$query->where(
				$db->nameQuote('a').'.'.$db->nameQuote('docimport_category_id').' = '.$db->quote($category)
			);
		}
		
		$slug = $this->getState('slug',null,'string');
		if(!empty($slug)) {
			$query->where(
				$db->nameQuote('a').'.'.$db->nameQuote('slug').' = '.$db->quote($slug)
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
					$db->nameQuote('c').'.'.$db->nameQuote('language').' IN ('.implode(',',$langs).')'
				);
			} else {
				$query->where(
					$db->nameQuote('c').'.'.$db->nameQuote('language').' = '.$db->quote($language)
				);
			}
		}
		return $query;
	}
}