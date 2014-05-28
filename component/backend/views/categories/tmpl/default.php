<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$this->loadHelper('select');
$this->loadHelper('format');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
if (version_compare(JVERSION, '3.0', 'gt'))
{
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}

$hasAjaxOrderingSupport = $this->hasAjaxOrderingSupport();

$sortFields = array(
	'docimport_category_id'	=> JText::_('JGRID_HEADING_ID'),
	'ordering'				=> JText::_('COM_DOCIMPORT_COMMON_FIELD_ORDERING'),
	'title' 				=> JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_TITLE'),
	'slug' 					=> JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_SLUG'),
	'enabled' 				=> JText::_('COM_DOCIMPORT_COMMON_FIELD_ENABLED'),
	'language' 				=> JText::_('COM_DOCIMPORT_COMMON_FIELD_LANGUAGE'),
);
?>

<?php if (version_compare(JVERSION, '3.0', 'ge')): ?>
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
<?php endif; ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="com_docimport" />
<input type="hidden" name="view" value="categories" />
<input type="hidden" id="task" name="task" value="browse" />
<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />

<?php if(version_compare(JVERSION, '3.0', 'gt')): ?>
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
<?php endif; ?>

<table class="table table-striped" width="100%" id="itemsList">
	<thead>
		<tr>
			<?php if($hasAjaxOrderingSupport !== false): ?>
			<th width="20px">
				<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $this->lists->order_Dir, $this->lists->order, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				<a href="javascript:saveorder(<?php echo count($this->items) - 1 ?>, 'saveorder')" rel="tooltip"
				   class="btn btn-micro pull-right" title="<?php echo JText::_('JLIB_HTML_SAVE_ORDER') ?>">
					<span class="icon-ok"></span>
				</a>
			</th>
			<?php endif; ?>
			<th width="60">
				<?php echo JHTML::_('grid.sort', 'Num', 'docimport_category_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<th width="25"></th>
			<th>
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_CATEGORIES_FIELD_TITLE', 'title', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_FIELD_STATUS'); ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_CATEGORIES_FIELD_SLUG', 'slug', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<?php if($hasAjaxOrderingSupport === false): ?>
			<th width="8%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_ORDERING', 'ordering', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
				<?php echo JHTML::_('grid.order', $this->items); ?>
			</th>
			<?php endif; ?>
			<th width="8%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_ENABLED', 'enabled', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<?php if(version_compare(JVERSION, '1.6.0', 'ge')):?>
			<th width="8%">
				<?php echo JHTML::_('grid.sort', 'COM_DOCIMPORT_COMMON_FIELD_LANGUAGE', 'language', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
			</th>
			<?php endif; ?>
		</tr>
		<tr>
			<?php if($hasAjaxOrderingSupport !== false): ?>
			<td></td>
			<?php endif; ?>
			<td colspan="2">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
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
			<td></td>
			<?php if($hasAjaxOrderingSupport === false): ?>
			<td></td>
			<?php endif; ?>
			<td></td>
			<td>
				<?php echo DocimportHelperSelect::published($this->getModel()->getState('enabled',''), 'enabled', array('onchange'=>'this.form.submit();', 'class' => 'input-small')) ?>
			</td>
			<?php if(version_compare(JVERSION, '1.6.0', 'ge')):?>
			<td>
				<?php echo DocimportHelperSelect::languages($this->getModel()->getState('language',''), 'language', array('onchange'=>'this.form.submit();', 'class' => 'input-small')) ?>
			</td>
			<?php endif; ?>
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
			$ordering = $this->lists->order == 'ordering';
			$item->published = $item->enabled;
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
				<?php echo $item->docimport_category_id; ?>
			</td>
			<td>
				<?php echo JHTML::_('grid.id', $i, $item->docimport_category_id, $checkedOut); ?>
			</td>
			<td align="left">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_DOCIMPORT_CATEGORY_EDITLEVEL_TOOLTIP')?> <?php echo $this->escape($item->title); ?>::<?php echo $this->escape(substr(strip_tags($item->description), 0, 300)).'...'; ?>">
					<a href="index.php?option=com_docimport&view=category&id=<?php echo $item->docimport_category_id ?>" class="di3category">
						<strong><?php echo $this->escape($item->title) ?></strong>
					</a>
				</span>
			</td>
			<td align="center">
				<?php if($item->status == 'missing'): ?>
				<span class="badge badge-important">
					<span class="icon-white icon-remove"></span>
				</span>
				<?php elseif($item->status == 'modified'): ?>
				<span class="badge badge-warning">
					<span class="icon-white icon-warning-sign"></span>
				</span>
				<?php else: ?>
				<span class="badge badge-success">
					<span class="icon-white icon-ok"></span>
				</span>
				<?php endif; ?>
				&nbsp;
				<button onclick="window.location='<?php echo JURI::base() ?>index.php?option=com_docimport&view=categories&task=rebuild&id=<?php echo $item->docimport_category_id ?>';return false;"
					class="btn <?php echo ($item->status == 'modified') ? 'btn-primary btn-mini' : 'btn-inverse btn-mini' ?>"
					title="<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_REBUILD') ?>">
					<i class="icon-white icon-refresh"></i>
				</button>
				
			</td>
			<td>
				<?php echo $this->escape($item->slug) ?>
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
			<?php if(version_compare(JVERSION, '1.6.0', 'ge')):?>
			<td>
				<?php echo DocimportHelperFormat::language($item->language) ?>
			</td>
			<?php endif; ?>
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