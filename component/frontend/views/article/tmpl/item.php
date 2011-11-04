<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

FOFTemplateUtils::addCSS('media://com_docimport/css/frontend.css');

$category = FOFModel::getTmpInstance('Category','DocimportModel')
	->setId($this->item->docimport_category_id)
	->getItem();
JFactory::getDocument()->setTitle($category->title.' :: '.$this->item->title);
		
$text = $this->item->fulltext;
if($category->process_plugins) {
	$text = JHTML::_('content.prepare', $text);
}

?>
<div class="docimport docimport-page-article">
	<?php echo $text; ?>
</div>