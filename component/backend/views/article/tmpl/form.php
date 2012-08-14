<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$this->loadHelper('Select');

$editor = JFactory::getEditor();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_docimport" />
	<input type="hidden" name="view" value="article" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="docimport_article_id" value="<?php echo $this->item->docimport_article_id ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />
	
	<fieldset id="article-basic">
		<legend><?php echo JText::_('COM_DOCIMPORT_ARTICLES_BASIC_TITLE'); ?></legend>
		
		<label for="title_field" class="main title"><?php echo JText::_('COM_DOCIMPORT_ARTICLES_FIELD_TITLE'); ?></label>
		<input type="text" size="40" id="title_field" name="title" class="title" value="<?php echo $this->escape($this->item->title) ?>" />
		<div class="docimport-clear"></div>
		
		<label for="slug_field" class="main"><?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_SLUG'); ?></label>
		<input type="text" size="30" id="slug_field" name="slug" value="<?php echo $this->escape($this->item->slug) ?>" />
		<div class="docimport-clear"></div>
		
		<label for="category" class="main"><?php echo JText::_('COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY'); ?></label>
		<?php echo DocimportHelperSelect::categories($this->item->docimport_category_id, 'category') ?>
		<div class="docimport-clear"></div>
		
		<label for="enabled" class="main" class="mainlabel">
			<?php if(version_compare(JVERSION,'1.6.0','ge')): ?>
			<?php echo JText::_('JPUBLISHED'); ?>
			<?php else: ?>
			<?php echo JText::_('PUBLISHED'); ?>
			<?php endif; ?>
		</label>
		<span class="akeebasubs-booleangroup">
			<?php echo JHTML::_('select.booleanlist', 'enabled', null, $this->item->enabled); ?>
		</span>
		<div class="akeebasubs-clear"></div>
		
	</fieldset>
	
	<fieldset id="article-description">
		<legend><?php echo JText::_('COM_DOCIMPORT_ARTICLES_FIELD_FULLTEXT');?></legend>
		
		<?php echo $editor->display( 'fulltext',  $this->item->fulltext, '80%', '500', '50', '10', false ) ; ?>
	</fieldset>
</form>