<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportDispatcher extends FOFDispatcher
{
	public function onBeforeDispatch() {
		if($result = parent::onBeforeDispatch()) {
			if (!JFactory::getUser()->authorise('core.manage', 'com_docimport')) {
				JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}

			// Language loading
			$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
			$jlang = JFactory::getLanguage();
			// -- Component
			$jlang->load($this->component, $paths[0], 'en-GB', true);
			$jlang->load($this->component, $paths[0], null, true);
			$jlang->load($this->component, $paths[1], 'en-GB', true);
			$jlang->load($this->component, $paths[1], null, true);
			// -- Component overrides
			$jlang->load($this->component.'.override', $paths[0], 'en-GB', true);
			$jlang->load($this->component.'.override', $paths[0], null, true);
			$jlang->load($this->component.'.override', $paths[1], 'en-GB', true);
			$jlang->load($this->component.'.override', $paths[1], null, true);
			// Live Update translation
			$jlang->load('liveupdate', JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'liveupdate', 'en-GB', true);
			$jlang->load('liveupdate', JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'liveupdate', $jlang->getDefault(), true);
			$jlang->load('liveupdate', JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'liveupdate', null, true);

			// Load Akeeba Strapper
			include_once JPATH_ROOT.'/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addCSSfile('media://com_docimport/css/backend.css');
		}
		return $result;
	}

	public function dispatch() {
		// Handle Live Update requests
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/liveupdate/liveupdate.php';
		if(($this->input->getCmd('view','') == 'liveupdate')) {
			LiveUpdate::handleRequest();
			return;
		}

		parent::dispatch();
	}
}