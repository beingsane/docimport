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

class DocimportViewProcessing extends JView
{
	function display()
	{
		// Set the toolbar title
		JToolBarHelper::title('DocImport - Processing files');

		$task = JRequest::getCmd('task', 'default');

		if($task == 'done')
		{
			parent::display('done');
		}
		else
		{
			$this->assign('filename', JRequest::getVar('filename',''));
			parent::display();
		}
	}
}
?>