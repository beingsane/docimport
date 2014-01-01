<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>

<div class="docimport docimport-page-category">

<?php if(is_object($this->index)): ?>
<div class="docimport-category-index">
	<?php echo $this->index->fulltext ?>
</div>
<?php else: ?>
<?php echo $this->loadTemplate('category'); ?>
<div class="docimport-category-list">
<?php if(!empty($this->items)):?>
<?php foreach($this->items as $item): ?>
<?php
	$url = JRoute::_('index.php?option=com_docimport&view=article&id='.$item->docimport_article_id);
?>
<div class="docimport-article-link">
	<a href="<?php echo $url ?>"><?php echo $this->escape($item->title) ?></a>
</div>
<?php endforeach; ?>
<?php else: ?>
<?php echo JText::_('COM_DOCIMPORT_CATEGORY_EMPTY') ?>
<?php endif; ?>
</div>
<?php endif; ?>
</div>