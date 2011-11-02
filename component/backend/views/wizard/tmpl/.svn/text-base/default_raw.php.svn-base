<?php
// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'sajax.php';
sajax_init();
sajax_force_page_ajax('wizard');
sajax_export('getCategoriesCombo','getFiles');
$null = null;
sajax_handle_client_request( $null );

function getCategoriesCombo()
{
	//return JHTML::_('list.category', 'catid', $sectid, null, 'onChange="doShowSubmit()" ');	
		$db = &JFactory::getDBO();

		$query = 'SELECT m.* FROM #__k2_categories m WHERE published = 1 ORDER BY parent, ordering';
		$db->setQuery( $query );
		$mitems = $db->loadObjectList();
		$children = array();
		if ( $mitems )
		{
			foreach ( $mitems as $v )
			{
				$pt 	= $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );
		$mitems = array();
		$mitems [] = JHTML::_ ( 'select.option', '0', '- ' . JText::_ ( 'None' ) . ' -' );

		foreach ( $list as $item ) {
			$mitems[] = JHTML::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;'.$item->treename );
		}

		return JHTML::_('select.genericlist',  $mitems, 'catid', 'class="inputbox" onchange="doShowSubmit();"', 'value', 'text' );
}

function getFiles()
{
	jimport('joomla.filesystem.folder');

	$jreg =& JFactory::getConfig();
	$tempdir = $jreg->getValue('config.tmp_path');

	$allFiles = JFolder::files($tempdir, 'jpa$');
	$options = array();
	$options[] = JHTML::_('select.option', '', '- Select a file -');
	if(count($allFiles) > 0)
	{
		foreach($allFiles as $file)
		{
			$options[] = JHTML::_('select.option', $file, $file);
		}
	}

	return JHTML::_('select.genericlist', $options,'filename','onChange="doFileSelected()" ');
}