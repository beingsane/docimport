<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$this->loadHelper('Select');
$this->loadHelper('Format');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$hasAjaxOrderingSupport = $this->hasAjaxOrderingSupport();

$sortFields = array(
	'docimport_article_id'	=> JText::_('JGRID_HEADING_ID'),
	'ordering'				=> JText::_('COM_DOCIMPORT_COMMON_FIELD_ORDERING'),
	'docimport_category_id'	=> JText::_('COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY'),
	'title' 				=> JText::_('COM_DOCIMPORT_ARTICLES_FIELD_TITLE'),
	'enabled' 				=> JText::_('COM_DOCIMPORT_COMMON_FIELD_ENABLED'),
);
?>

<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '$order')
		{
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn);
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="com_docimport" />
<input type="hidden" name="view" value="articles" />
<input type="hidden" id="task" name="task" value="browse" />
<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />


	<div id="filter-bar" class="btn-toolbar">
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?></label>
			<?php echo $this->getModel()->getPagination()->getLimitBox(); ?>
		</div>
		<?php
		$asc_sel	= ($this->getLists()->order_Dir == 'asc') ? 'selected="selected"' : '';
		$desc_sel	= ($this->getLists()->order_Dir == 'desc') ? 'selected="selected"' : '';
		?>
		<div class="btn-group pull-right hidden-phone">
			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC') ?></label>
			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC') ?></option>
				<option value="asc" <?php echo $asc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING') ?></option>
				<option value="desc" <?php echo $desc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING') ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY') ?></label>
			<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JGLOBAL_SORT_BY') ?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $this->getLists()->order) ?>
			</select>
		</div>
	</div>
	<div class="clearfix"> </div>

<table class="table table-striped" width="100%" id="itemsList">
	<thead>
		<tr>
			<?php if($hasAjaxOrderingSupport !== false): ?>
			<th width="55px">
				<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $this->lists->order_Dir, $this->lists->order, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				<a href="javascript:saveorder(<?php echo count($this->items) - 1 ?>, 'saveorder')" rel="tooltip"
				   class="btn btn-micro pull-right" title="<?php echo JText::_('JLIB_HTML_SAVE_ORDER') ?>">
					<span class="icon-ok"></span>
				</a>
			</th>
			<?php endif; ?>
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
			<?php if($hasAjaxOrderingSupport === false): ?>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_ORDERING', 'ordering', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
				<?php echo JHTML::_('grid.order', $this->items); ?>
			</th>
			<?php endif; ?>
			<th width="8%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_ENABLED', 'enabled', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
		</tr>
		<tr>
			<?php if($hasAjaxOrderingSupport !== false): ?>
			<td></td>
			<?php endif; ?>
			<td></td>
			<td>
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
			</td>
			<td>
				<?php echo DocimportHelperSelect::categories($this->getModel()->getState('category',''), 'category', array('onchange'=>'this.form.submit();')) ?>
			</td>
			<td class="form-inline">
				<input type="text" name="search" id="search"
					value="<?php echo $this->escape($this->getModel()->getState('search',''));?>"
					class="input-medium" onchange="document.adminForm.submit();" />
				<nobr>
					<button onclick="this.form.submit();" class="btn btn-small">
						<?php echo JText::_('COM_DOCIMPORT_COMMON_GO'); ?>
					</button>
					<button onclick="document.adminForm.search.value='';this.form.submit();" class="btn btn-small">
						<?php echo JText::_('COM_DOCIMPORT_COMMON_Reset'); ?>
					</button>
				</nobr>
			</td>
			<?php if($hasAjaxOrderingSupport === false): ?>
			<td></td>
			<?php endif; ?>
			<td>
				<?php echo DocimportHelperSelect::published($this->getModel()->getState('enabled',''), 'enabled', array('onchange'=>'this.form.submit();', 'class' => 'input-small')) ?>
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
			<?php if($hasAjaxOrderingSupport !== false): ?>
			<td class="order nowrap center hidden-phone">
			<?php if ($this->perms->editstate) :
				$disableClassName = '';
				$disabled          = '';
				$disabledLabel	  = '';
				if (!$hasAjaxOrderingSupport['saveOrder']) :
					$disabledLabel    = JText::_('JORDERINGDISABLED');
					$disabled         = 'disabled="disabled"';
					$disableClassName = 'inactive tip-top';
				endif; ?>
				<span class="sortable-handler <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>" rel="tooltip">
					<i class="icon-menu"></i>
				</span>
				<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>"
					   class="input-mini text-area-order" <?php echo $disabled?> />
			<?php else : ?>
				<span class="sortable-handler inactive" >
					<i class="icon-menu"></i>
				</span>
			<?php endif; ?>
			</td>
			<?php endif; ?>
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
			<?php if($hasAjaxOrderingSupport === false): ?>
			<td class="order" align="center">
				<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $count, true, 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<?php endif; ?>
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