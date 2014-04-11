<?php
/**
 *  @package AkeebaSubs
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportToolbar extends F0FToolbar
{

	public function onArticlesBrowse()
	{
		// Set toolbar title
		$subtitle_key = $this->input->getCmd('option','com_foobar').'_TITLE_'.strtoupper($this->input->getCmd('view','cpanel'));
		JToolBarHelper::title(JText::_( $this->input->getCmd('option','com_foobar')).' &ndash; <small>'.JText::_($subtitle_key).'</small>', str_replace('com_', '', $this->input->getCmd('option','com_foobar')));

		// Add toolbar buttons
		if($this->perms->delete) {
			JToolBarHelper::deleteList();
		}
		if($this->perms->edit) {
			JToolBarHelper::editList();

			JToolBarHelper::divider();

			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
		}

		$this->renderSubmenu();
	}
}