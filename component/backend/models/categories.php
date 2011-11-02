<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportModelCategories extends FOFModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = FOFQueryAbstract::getNew()
			->select('*')
			->from($db->nameQuote('#__docimport_categories'));

		$search = $this->getState('search', null,'string');
		if(!empty($search)) {
			$query->where(
				$db->nameQuote('title').' LIKE '.$db->quote('%'.$search.'%')
			);
		}
		
		$enabled = $this->getState('enabled',null,'cmd');
		if(is_numeric($enabled)) {
			$query->where(
				$db->nameQuote('enabled').' = '.$db->quote($enabled)
			);
		}
		
		$language = $this->getState('language',null,'string');
		if(!empty($language)) {
			$query->where(
				$db->nameQuote('language').' = '.$db->quote($language)
			);
		}
		
		return $query;
	}
}