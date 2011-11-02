<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2008 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'sajax.php';
sajax_init();
sajax_force_page_ajax('wizard');
sajax_export('getCategoriesCombo','getFiles');

?>
<script type="text/javascript" language="Javascript">
	/**
	 * (S)AJAX JavaScript
	 */
	<?php sajax_show_javascript(); ?>

	//sajax_debug_mode = 1;
	sajax_profiling = false;
	sajax_fail_handle = onInvalidData;
	 	 
	function onInvalidData(data)
	{
		error("Invalid AJAX Response:\n"+data);		
	}
	
	function doShowMethod(method)
	{
		document.getElementById('submittarget').style.display='none';
		document.getElementById('categorycontainer').style.display='none';
		if(method == 0)
		{
			// File upload
			document.getElementById('filename').value='';
			document.getElementById('uploadtarget').style.display='table-row';
			document.getElementById('existingtarget').style.display='none';
			doGetCategories();
		}
		else
		{
			document.getElementById('uploadtarget').style.display='none';
			doGetFiles();
		}
	}
	
	function doGetFiles()
	{
		x_getFiles(doGetFiles_cb)
	}
	
	function doGetFiles_cb(ret)
	{
		document.getElementById('existingtarget').style.display='table-row';
		document.getElementById('filetarget').innerHTML = ret;
	}
	
	function doFileSelected()
	{
		doGetCategories();
	}
	
	function doGetCategories()
	{
		x_getCategoriesCombo(doGetCategories_cb)
	}
	
	function doGetCategories_cb(res)
	{
		document.getElementById('categorycontainer').style.display='table-row';
		document.getElementById('categorytarget').innerHTML = res;
	}
	
	function doShowSubmit()
	{
		document.getElementById('submittarget').style.display='table-row';
	}
	
</script>
<h1>Welcome to DocImport<sup>2</sup></h1>
<p>This wizard will guide you through importing your documentation
package archive as K2 items.</p>
<form enctype="multipart/form-data" method="post"
	action="<?php echo JURI::base(); ?>index.php" name="adminForm"
	id="adminForm"><input type="hidden" name="option" value="com_docimport" />
<input type="hidden" name="task" value="upload" />
<table border="0" style="font-size: 110%;">
	<tr id="methodselect">
		<td>Uploading method</td>
		<td><?php echo JHTML::_('select.genericlist', $this->options, 'uploadmethod', 'onChange="doShowMethod(document.getElementById(\'uploadmethod\').value)" ') ?></td>
	</tr>
	<tr id="uploadtarget" style="display: none;">
		<td>Package to upload</td>
		<td><input name="userfile" type="file" /></td>
	</tr>
	<tr id="existingtarget" style="display: none;">
		<td>File to use</td>
		<td id="filetarget"><input type="hidden" name="filename" id="filename"
			value="" /></td>
	</tr>
	<tr id="categorycontainer" style="display: none;">
		<td>Category</td>
		<td id="categorytarget"></td>
	</tr>
	<tr id="submittarget" style="display: none">
		<td>&nbsp;</td>
		<td><input type="submit" value="Start processing" /></td>
	</tr>
</table>
</form>
