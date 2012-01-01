<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
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
		
		$language = $this->getState('language',null,'array');
		if(empty($language)) $language = $this->getState('language',null,'string');
		if(!empty($language) && (is_array($language) ? (!empty($language[0])) : true) ) {
			if(is_array($language)) {
				$langs = array();
				foreach($language as $l) {
					$langs[] = $db->quote($l);
				}
				$query->where(
					$db->nameQuote('language').' IN ('.implode(',',$langs).')'
				);
			} else {
				$query->where(
					$db->nameQuote('language').' = '.$db->quote($language)
				);
			}
		}
		
		// Fix the ordering
		$order = $this->getState('filter_order', 'docimport_category_id', 'cmd');
		if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'docimport_category_id';
		$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
		$query->order($order.' '.$dir);
		
		return $query;
	}
	
	public function onProcessList(&$resultArray)
	{
		jimport('joomla.filesystem.folder');
		if(!empty($resultArray)) foreach($resultArray as $key => $item) {
			$resultArray[$key]->status = 'missing';
			
			$folder = JPATH_ROOT.'/media/com_docimport/'.$item->slug;
			if(!JFolder::exists($folder)) {
				$resultArray[$key]->status = 'missing';
				continue;
			}
			
			$xmlfiles = JFolder::files($folder, '\.xml$', false, true);
			if(empty($xmlfiles)) continue;
			
			$timestamp = 0;
			foreach($xmlfiles as $filename) {
				$my_timestamp = @filemtime($filename);
				if($my_timestamp > $timestamp) $timestamp = $my_timestamp;
			}
			
			if($timestamp != $item->last_timestamp) {
				$resultArray[$key]->status = 'modified';
			} else {
				$resultArray[$key]->status = 'unmodified';
			}
		}
	}
}