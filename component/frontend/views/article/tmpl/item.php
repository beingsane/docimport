<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

$app = JFactory::getApplication();
$menus = $app->getMenu();
$menu = $menus->getActive();

?>
<div class="docimport docimport-page-article">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading', $menu->title)); ?></h1>
		</div>
	<?php endif;?>

	<?php echo $this->item->fulltext; ?>
</div>

<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			$('pre.programlisting').each(function(i, e){
				language = $(e).attr('data-language');
				content = $(e).text();

				if (!language)
				{
					return;
				}

				result = hljs.highlight(language, content);
				$(e).html(result.value);
			});
		});
	})(akeeba.jQuery);

</script>