<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$this->loadHelper('Select');
$this->loadHelper('Format');

JHtml::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="com_docimport" />
<input type="hidden" name="view" value="articles" />
<input type="hidden" id="task" name="task" value="browse" />
<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />

<table class="adminlist">
	<thead>
		<tr>
			<th width="60">
				<?php echo JHTML::_('grid.sort', 'Num', 'docimport_article_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<th width="25"></th>
			<th width="20%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY', 'docimport_category_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_ARTICLES_FIELD_TITLE', 'title', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_ORDERING', 'ordering', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
				<?php echo JHTML::_('grid.order', $this->items); ?>
			</th>
			<th width="8%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_ENABLED', 'enabled', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>
				<?php echo DocimportHelperSelect::categories($this->getModel()->getState('category',''), 'category', array('onchange'=>'this.form.submit();')) ?>
			</td>
			<td>
				<input type="text" name="search" id="search"
					value="<?php echo $this->escape($this->getModel()->getState('search',''));?>"
					class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();">
					<?php echo JText::_('COM_DOCIMPORT_COMMON_GO'); ?>
				</button>
				<button onclick="document.adminForm.search.value='';this.form.submit();">
					<?php echo JText::_('COM_DOCIMPORT_COMMON_Reset'); ?>
				</button>
			</td>
			<td></td>
			<td>
				<?php echo DocimportHelperSelect::published($this->getModel()->getState('enabled',''), 'enabled', array('onchange'=>'this.form.submit();')) ?>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="20">
				<?php if($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>	
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php if($count = count($this->items)): ?>
		<?php $i = -1; $m = 0; ?>
		<?php foreach ($this->items as $item) : ?>
		<?php
			$i++; $m = 1-$m;
			$checkedOut = ($item->locked_by != 0);
			$item->published = $item->enabled;
			$ordering = $this->lists->order == 'ordering';
		?>
		<tr class="<?php echo 'row'.$m; ?>">
			<td align="center">
				<?php echo $item->docimport_article_id; ?>
			</td>
			<td>
				<?php echo JHTML::_('grid.id', $i, $item->docimport_article_id, $checkedOut); ?>
			</td>
			<td align="left">
				<?php echo $item->category_title; ?>
			</td>
			<td align="left">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_DOCIMPORT_ARTICLES_EDITARTICLE_TOOLTIP')?> <?php echo $this->escape($item->title); ?>::<?php echo $this->escape(substr(strip_tags($item->fulltext), 0, 300)).'...'; ?>">
					<a href="index.php?option=com_docimport&view=article&id=<?php echo $item->docimport_article_id ?>" class="di3article">
						<strong><?php echo $this->escape($item->title) ?></strong>
					</a>
				</span>
			</td>
			<td class="order" align="center">
				<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $count, true, 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<?php echo JHTML::_('grid.published', $item, $i); ?>
			</td>
		</tr>
		<?php endforeach;?>
		<?php else: ?>
		<tr>
			<td colspan="20">
				<?php echo  JText::_('COM_DOCIMPORT_COMMON_NORECORDS') ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

</form>