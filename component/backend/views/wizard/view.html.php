<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2008 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class DocimportViewWizard extends JView
{
	function display()
	{
		$task = JRequest::getCmd('task','default');
		$act = JRequest::getCmd('act','start');

		// Set the toolbar title
		JToolBarHelper::title('DocImport');
                JToolBarHelper::preferences('com_docimport', '550');

		if($task == 'upload')
		{
			parent::display('upload');
		}
		else
		{
			$options = array();
			$options[] = JHTML::_('select.option', '-1', '- Choose upload method -');
			$options[] = JHTML::_('select.option', '0', 'Upload a file');
			$options[] = JHTML::_('select.option', '1', 'Use a file uploaded in the site\'s temporary directory');
			$this->assign('options', $options);
			parent::display(JRequest::getCmd('tpl',null));
		}

	}
}
?>