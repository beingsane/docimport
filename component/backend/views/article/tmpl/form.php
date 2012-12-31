<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$this->loadHelper('Select');

$editor = JFactory::getEditor();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<input type="hidden" name="option" value="com_docimport" />
	<input type="hidden" name="view" value="article" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="docimport_article_id" value="<?php echo $this->item->docimport_article_id ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />
	
	<h3><?php echo JText::_('COM_DOCIMPORT_ARTICLES_BASIC_TITLE'); ?></h3>
	
	<div class="control-group">
		<label for="title_field" class="control-label">
			<?php echo JText::_('COM_DOCIMPORT_ARTICLES_FIELD_TITLE'); ?>
		</label>
		<div class="controls">
			<input type="text" size="40" id="title_field" name="title" class="input-xxlarge" value="<?php echo $this->escape($this->item->title) ?>" />
		</div>
	</div>
	
	<div class="control-group">
		<label for="slug_field" class="control-label">
			<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_SLUG'); ?>
		</label>
		<div class="controls">
			<input type="text" size="30" id="slug_field" name="slug" value="<?php echo $this->escape($this->item->slug) ?>" class="input-xxlarge" />
		</div>
	</div>
	
	<div class="control-group">
		<label for="category" class="control-label">
			<?php echo JText::_('COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY'); ?>
		</label>
		<div class="controls">
			<?php echo DocimportHelperSelect::categories($this->item->docimport_category_id, 'category') ?>
		</div>
	</div>
	
	<div class="control-group">
		<label for="enabled" class="control-label">
			<?php if(version_compare(JVERSION,'1.6.0','ge')): ?>
			<?php echo JText::_('JPUBLISHED'); ?>
			<?php else: ?>
			<?php echo JText::_('PUBLISHED'); ?>
			<?php endif; ?>
		</label>
		<div class="controls">
			<?php echo JHTML::_('select.booleanlist', 'enabled', null, $this->item->enabled); ?>
		</div>
	</div>
	
	<h3><?php echo JText::_('COM_DOCIMPORT_ARTICLES_FIELD_FULLTEXT');?></h3>

	<?php echo $editor->display( 'fulltext',  $this->item->fulltext, '100%', '600', '50', '10', false ) ; ?>
</form>