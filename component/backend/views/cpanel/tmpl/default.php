<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$lang = JFactory::getLanguage();

if(!class_exists('LiveUpdate')) {
	require_once JPATH_ADMINISTRATOR.'/components/com_ats/liveupdate/liveupdate.php';
}
?>
<div id="cpanel">
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=com_docimport&view=categories">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_docimport/images/categories.png"
				border="0" alt="<?php echo JText::_('COM_DOCIMPORT_TITLE_CATEGORIES') ?>" />
				<span>
					<?php echo JText::_('COM_DOCIMPORT_TITLE_CATEGORIES') ?><br/>
				</span>
			</a>
		</div>
	</div>

	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=com_docimport&view=articles">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_docimport/images/articles.png"
				border="0" alt="<?php echo JText::_('COM_DOCIMPORT_TITLE_ARTICLES') ?>" />
				<span>
					<?php echo JText::_('COM_DOCIMPORT_TITLE_ARTICLES') ?><br/>
				</span>
			</a>
		</div>
	</div>

	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=com_docimport&view=urls&task=nuke">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_docimport/images/nuke.png"
				border="0" alt="<?php echo JText::_('COM_DOCIMPORT_CPANEL_NUKEURLS') ?>" />
				<span>
					<?php echo JText::_('COM_DOCIMPORT_CPANEL_NUKEURLS') ?><br/>
				</span>
			</a>
		</div>
	</div>

	<?php echo LiveUpdate::getIcon(); ?>
</div>