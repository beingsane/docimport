<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var \Akeeba\DocImport\Site\View\Search\Html $this */

// Get the submission URL
$returnUrl = base64_encode(JUri::current());
$submitUrl = JRoute::_('index.php?option=com_docimport&view=Search');

JHtml::_('formbehavior.chosen', 'select.fancySelect')
?>

<form action="<?php echo $submitUrl ?>" method="POST" id="dius-form">
	<div id="dius-searchform" class="row col-xs-12">
		<div class="input-group">
			<input type="text" class="form-control" id="dius-search" name="search" placeholder="<?php echo JText::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHSUPPORT') ?>">
			<span class="input-group-btn">
				<button class="btn btn-primary" type="submit">
					<span class="glyphicon glyphicon-search"></span>
				</button>
			</span>
		</div>
	</div>
	<div id="dius-searchutils">
		<div id="dius-searching-container" class="row col-xs-12">
			<label id="dius-searching-label" for="dius-searching-areas">
				<?php echo JText::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHINGSECTIONS') ?>
			</label>
			<span id="dius-searching-areas"></span>
			<span id="dius-searching-toggle" class="pull-right">
				<a data-toggle="collapse" href="#dius-searchutils-collapsible" aria-expanded="false" aria-controls="dius-searchutils-collapsible">
					<?php echo JText::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHTOOLS'); ?>
				</a>
			</span>
		</div>
		<div id="dius-searchutils-collapsible">
			<div class="row col-xs-12 form">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="form-group">
							<label for="dius-searchutils-areas">
								<?php echo JText::_('COM_DOCIMPORT_SEARCH_LBL_SECTIONS'); ?>
							</label>
							<?php echo JHtml::_('select.genericlist', $this->areaOptions, 'areas', [
								'multiple' => 'multiple',
								'class' => 'fancySelect form-control',
								'onchange' => 'akeeba.DocImport.Search.sectionsChange(this)'
							], 'value', 'text', $this->areas, 'dius-searchutils-areas') ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1" />
</form>

<?php if (empty($this->search)) return; ?>

<?php echo $this->pagination->getListFooter(); ?>
