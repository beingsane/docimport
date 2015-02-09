<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$lang = JFactory::getLanguage();

?>

<?php
// Obsolete PHP version check
if (version_compare(JVERSION, '5.9.0', 'lt')):
	JLoader::import('joomla.utilities.date');
	$akeebaCommonDatePHP = new JDate('2014-08-14 00:00:00', 'GMT');
	$akeebaCommonDateObsolescence = new JDate('2015-05-14 00:00:00', 'GMT');
	?>
	<div id="phpVersionCheck" class="alert alert-warning">
		<h3><?php echo JText::_('AKEEBA_COMMON_PHPVERSIONTOOOLD_WARNING_TITLE'); ?></h3>
		<p>
			<?php echo JText::sprintf(
				'AKEEBA_COMMON_PHPVERSIONTOOOLD_WARNING_BODY',
				PHP_VERSION,
				$akeebaCommonDatePHP->format(JText::_('DATE_FORMAT_LC1')),
				$akeebaCommonDateObsolescence->format(JText::_('DATE_FORMAT_LC1')),
				'5.5'
			);
			?>
		</p>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>

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
</div>

<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			$.ajax('index.php?option=com_docimport&view=cpanel&task=updateinfo&tmpl=component', {
				success: function(msg, textStatus, jqXHR)
				{
					// Get rid of junk before and after data
					var match = msg.match(/###([\s\S]*?)###/);
					data = match[1];

					if (data.length)
					{
						$('#updateNotice').html(data);
					}
				}
			})
		});
	})(akeeba.jQuery);
</script>