<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportViewArticle extends F0FViewHtml
{
	protected function onRead($tpl = null)
	{
		parent::onRead($tpl);

		$document = JFactory::getDocument();

		// Load highlight.js
		$document->addScript('https://yandex.st/highlightjs/8.0/highlight.min.js', 'text/javascript', false, false);
		//$document->addStyleSheet('https://yandex.st/highlightjs/8.0/styles/default.min.css');
		$document->addStyleSheet('https://yandex.st/highlightjs/8.0/styles/idea.min.css');

		$category = F0FModel::getTmpInstance('Category','DocimportModel')
			->setId($this->item->docimport_category_id)
			->getItem();
		$document->setTitle($category->title.' :: '.$this->item->title);

		if($category->process_plugins)
		{
			$this->item->fulltext = JHTML::_('content.prepare', $this->item->fulltext);
		}

		// Pass page params
		$params = JFactory::getApplication()->getParams();
		$this->params = $params;
	}
}