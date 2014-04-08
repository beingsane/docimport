<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportViewArticle extends F0FViewHtml
{
	protected function onRead($tpl = null)
	{
		parent::onRead($tpl);

		$category = F0FModel::getTmpInstance('Category','DocimportModel')
			->setId($this->item->docimport_category_id)
			->getItem();
		JFactory::getDocument()->setTitle($category->title.' :: '.$this->item->title);

		if($category->process_plugins) {
			$this->item->fulltext = JHTML::_('content.prepare', $this->item->fulltext);
		}

		// Pass page params
		$params = JFactory::getApplication()->getParams();
		$this->params = $params;
	}
}