<?php
// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * The RAW mode view class for the backup page. It serves a double purpose. On one hand,
 * it processes AJAX requests. On the other hand (when task=step), it displays the iframe
 * contents.
 *
 */
class DocimportViewWizard extends JView
{
	function display()
	{
		parent::display(JRequest::getCmd('tpl',null));
	}
}