<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportViewCategory extends F0FViewHtml
{
	public function onAdd($tpl = null)
	{
		parent::onAdd($tpl);

		$catid = $this->getModel()->getItem()->docimport_category_id;
		// Look for an index article
		$index = F0FModel::getTmpInstance('Articles','DocimportModel')
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
			$items = F0FModel::getTmpInstance('Articles','DocimportModel')
				->category($catid)
				->enabled(1)
				->filter_order('ordering')
				->filter_order('ASC')
				->limit(0)
				->limitstart(0)
				->getList();
		}

		$this->assign('items', $items);
		$this->assign('index', $index);

		// Pass page params
		$params = JFactory::getApplication()->getParams();
		$this->params = $params;
	}
}