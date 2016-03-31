<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Site\Model\Search\Result\JoomlaArticle[]  $items */
/** @var  int $count */

if (empty($items)):
?>
<div class="alert alert-info">
	<?php if (empty($count)): ?>
		<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NO_VIDEO'); ?>
	<?php else: ?>
		<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NOMORE_VIDEO'); ?>
	<?php endif; ?>
</div>
<?php return; endif; ?>

TODO – SHOW RESULTS