<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2008 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Get the view and controller from the request, or set to default if they weren't set
JRequest::setVar('view', JRequest::getCmd('view','wizard'));
JRequest::setVar('c', JRequest::getCmd('view','wizard'));

// Black magic: if the AJAX request parameters exist, we force the format to Raw.
if( JRequest::getString('rs','') )
{
	JRequest::setVar('format','raw');
}

// Load the appropriate controller
$c = JRequest::getCmd('c','wizard');
$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$c.'.php';
jimport('joomla.filesystem.file');
if(JFile::exists($path))
{
	// The requested controller exists and there you load it...
	require_once($path);
}
else
{
	// Hmm... an invalid controller was passed
	JError::raiseError('500',JText::_('Unknown controller'));
}

// Instanciate and execute the controller
jimport('joomla.utilities.string');
$c = 'DocimportController'.JString::ucfirst($c);
$controller = new $c();
$controller->execute(JRequest::getCmd('task','display'));

// Redirect
$controller->redirect();
?>