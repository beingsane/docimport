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

//JHtml::_('formbehavior.chosen', 'select.fancySelect')
?>

	<form action="<?php echo $submitUrl ?>" method="POST" id="dius-form">
		<div style="float:none" class="row col-xs-7 center-block text-center">
			<div id="dius-searchform">
				<h3>What do you need to know?</h3>
				<div class="col-xs-10">
					<input type="text" class="form-control" id="dius-search" name="search"
					       placeholder="<?php echo JText::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHSUPPORT') ?>"
					       value="<?php echo htmlentities($this->search); ?>"
					>
				</div>
				<div class="col-xs-2">
					<button class="btn btn-primary" type="submit">Search</button>
				</div>
			</div>

			<div id="dius-searchutils">
				<div id="dius-searching-container" class="row col-xs-12">
					<div class="pull-left">
						<label id="dius-searching-label" for="dius-searching-areas">
							<?php echo JText::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHINGSECTIONS') ?>
						</label>
						<span id="dius-searching-areas"></span>
					</div>
					<span id="dius-searching-toggle" class="pull-right">
						<a data-toggle="collapse" href="#dius-searchutils-collapsible" aria-expanded="false" aria-controls="dius-searchutils-collapsible">
							Edit search options
						</a>
					</span>
				</div>
				<div id="dius-searchutils-collapsible">
					<div class="row col-xs-12 form">

						<div class="panel-group" id="dius-searchutils-groupcontainer">

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
				<div class="clearfix"></div>
			</div>
			<div id="dius-troubleshoot-links">

			</div>
		</div>

		<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1" />
	</form>
	<div class="clearfix"></div>

<?php if (empty($this->search)) return; ?>

<?php if (!$this->pagination->pagesTotal): ?>

	<div class="row col-xs-12">
		<div class="alert alert-danger">
			<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NOTHINGFOUND'); ?>
		</div>
	</div>

<?php else:
// Get a smart active slider
	$active = 'docimport';

	if ($this->items['ats']['count'] && ($this->items['ats']['count'] >= $this->limitStart))
	{
		$active = 'ats';
	}

	if ($this->items['video']['count'] && ($this->items['video']['count'] >= $this->limitStart))
	{
		$active = 'video';
	}
	?>
	<div class="row col-xs-12 form">
		<div class="panel-group" id="dius-results-accordion" role="tablist" aria-multiselectable="true">
			<?php foreach ($this->items as $section => $data):
				// Skip over sections with no results
				if (!$data['count'])
				{
					continue;
				}

				// Skip over sections with less result pages than the current page
				if ($this->limitStart > $data['count'])
				{
					continue;
				}

				$ariaExpanded = ($section == $active) ? 'true' : 'false';
				$collapseClass = ($section == $active) ? 'collapse in' : 'collapse';
				?>

				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="dius-results-slide-<?php echo $section ?>-head">
						<h4 class="panel-title">
							<a role="button" data-toggle="collapse" data-parent="#dius-results-accordion"
							   href="#dius-results-slide-<?php echo $section ?>"
							   aria-expanded="<?php echo $ariaExpanded ?>"
							   aria-controls="dius-results-slide-<?php echo $section ?>"
							>
								<?php echo JText::_('COM_DOCIMPORT_SEARCH_SECTION_' . $section) ?>
							</a>
						</h4>
					</div>
					<div id="dius-results-slide-<?php echo $section ?>"
					     class="panel-collapse <?php echo $collapseClass ?>"
					     role="tabpanel"
					     aria-labelledby="dius-results-slide-<?php echo $section ?>-head"
					>
						<div class="panel-body">
							<?php
							// Render the section using template sectionName, e.g. joomla
							try
							{
								echo $this->loadAnyTemplate('site:com_docimport/Search/' . $section, $data);
							}
							catch (Exception $e)
							{
								echo $e->getMessage(); die;
							}
							?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="row col-xs-12 form">
		<div class="pagination">
			<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	</div>

<?php endif; ?>