<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$this->loadHelper('Select');

$editor = JFactory::getEditor();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_docimport" />
	<input type="hidden" name="view" value="category" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="docimport_category_id" value="<?php echo $this->item->docimport_category_id ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />
	
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('COM_DOCIMPORT_CATEGORY_BASIC_TITLE'); ?></h3>

			<div class="control-group">
				<label for="title_field" class="control-label">
					<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_TITLE'); ?>
				</label>
				<div class="controls">
					<input type="text" size="40" id="title_field" name="title" class="input" value="<?php echo $this->escape($this->item->title) ?>" />
				</div>
			</div>
			
			<div class="control-group">
				<label for="slug_field" class="control-label">
					<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_SLUG'); ?>
				</label>
				<div class="controls">
					<input type="text" size="30" id="slug_field" name="slug" value="<?php echo $this->escape($this->item->slug) ?>" />
				</div>
			</div>
			
			<div class="control-group">
				<label for="process_plugins" class="control-label">
					<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_PROCESS_PLUGINS'); ?>
				</label>
				<div class="controls">
					<?php echo JHTML::_('select.booleanlist', 'process_plugins', null, $this->item->process_plugins); ?>
				</div>
			</div>

			<div class="control-group">
				<label for="language" class="control-label">
					<?php echo JText::_('COM_DOCIMPORT_COMMON_FIELD_LANGUAGE'); ?>
				</label>
				<div class="controls">
					<?php echo DocimportHelperSelect::languages($this->item->language, 'language') ?>
				</div>
			</div>
			
			<div class="control-group">
				<label for="access" class="control-label">
					<?php echo JText::_('COM_DOCIMPORT_COMMON_FIELD_ACCESS'); ?>
				</label>
				<div class="controls">
					<?php echo JHTML::_('access.level', 'access', $this->item->access); ?>
				</div>
			</div>
			
			<div class="control-group">
				<label for="enabled" class="control-label">
					<?php echo JText::_('JPUBLISHED'); ?>
				</label>
				<div class="controls">
					<?php echo JHTML::_('select.booleanlist', 'enabled', null, $this->item->enabled); ?>
				</div>
			</div>
		</div>
		<div class="span6">
			<h3><?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_DESCRIPTION');?></h3>
		
			<?php echo $editor->display( 'description',  $this->item->description, '100%', '500', '50', '20', false ) ; ?>
		</div>
	</div>
</form>