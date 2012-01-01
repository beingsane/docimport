<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

FOFTemplateUtils::addCSS('media://com_docimport/css/frontend.css');
?>
<div class="docimport-category">
	<h2 class="docimport-category-title">
		<?php echo $this->escape($this->item->title) ?>
	</h2>

	<div class="docimport-category-description">
		<div class="docimport-category-description-inner">
			<?php echo $this->item->description ?>
		</div>
	</div>
</div>