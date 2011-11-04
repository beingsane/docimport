<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportViewCategory extends FOFViewHtml
{
	public function onRead($tpl = null)
	{
		parent::onRead($tpl);
		
		$catid = $this->getModel()->getItem()->docimport_category_id;
		// Look for an index article
		$index = FOFModel::getTmpInstance('Articles','DocimportModel')
			->category($catid)
			->slug('index')
			->enabled(1)
			->getFirstItem();
		
		if($index->docimport_article_id) {
			$items = array();
			if($this->item->process_plugins) {
				$index->fulltext = JHTML::_('content.prepare',$index->fulltext);
			}
		} else {
			$index = null;
			$items = FOFModel::getTmpInstance('Articles','DocimportModel')
				->category($catid)
				->enabled(1)
				->limit(0)
				->limitstart(0)
				->getList();
		}
		
		$this->assign('items', $items);
		$this->assign('index', $index);
	}
}