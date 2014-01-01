<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>
<div class="docimport docimport-page-categories akeeba-bootstrap">
<?php if(empty($this->items)):?>
<p><?php echo JText::_('COM_DOCIMPORT_CATEGORIES_NONE') ?></p>
<?php else:?>
<?php foreach($this->items as $item):
$url = JRoute::_('index.php?option=com_docimport&view=category&id='.$item->docimport_category_id);
?>
<div class="docimport-category well">
	<h2 class="docimport-category-title">
		<a href="<?php echo $url ?>">
			<?php echo $this->escape($item->title) ?>
		</a>
	</h2>
	
	<div class="docimport-category-description">
		<div class="docimport-category-description-inner">
			<?php echo $item->description ?>
		</div>
	</div>
	
	<div class="docimport-category-readon">
		<a class="btn btn-primary" href="<?php echo $url?>">
			<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_GOTOINDEX') ?>
		</a>
	</div>
</div>
<?php endforeach;?>
<?php endif; ?>
</div>