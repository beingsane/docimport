<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportViewArticle extends FOFViewHtml
{
	protected function onRead($tpl = null)
	{
		parent::onRead($tpl);
		
		$model = $this->getModel();
		
		$category = FOFModel::getTmpInstance('Category','DocimportModel')
			->setId($this->item->docimport_category_id)
			->getItem();
		JFactory::getDocument()->setTitle($category->title.' :: '.$this->item->title);

		if($category->process_plugins) {
			$this->item->fulltext = JHTML::_('content.prepare', $this->item->fulltext);
		}
	}
}