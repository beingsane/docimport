<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$lang = JFactory::getLanguage();

/** @var \Akeeba\DocImport\Admin\View\ControlPanel\Html $this */

?>

<a href="index.php?option=com_docimport&view=categories" class="btn btn-primary">
	<img
		src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_docimport/images/categories.png"
		border="0" alt="<?php echo JText::_('COM_DOCIMPORT_TITLE_CATEGORIES') ?>" />
				<span>
					<?php echo JText::_('COM_DOCIMPORT_TITLE_CATEGORIES') ?><br/>
				</span>
</a>

<a href="index.php?option=com_docimport&view=articles" class="btn btn-inverse">
	<img
		src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_docimport/images/articles.png"
		border="0" alt="<?php echo JText::_('COM_DOCIMPORT_TITLE_ARTICLES') ?>" />
				<span>
					<?php echo JText::_('COM_DOCIMPORT_TITLE_ARTICLES') ?><br/>
				</span>
</a>
