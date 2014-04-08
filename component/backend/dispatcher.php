<?php

/**
 * @package DocImport
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportDispatcher extends F0FDispatcher
{

	public function onBeforeDispatch()
	{
		// You can't fix stupid… but you can try working around it
		if ((!function_exists('json_encode')) || (!function_exists('json_decode')))
		{
			require_once JPATH_ADMINISTRATOR . '/components/' . $this->component . '/helpers/jsonlib.php';
		}

		if ($result = parent::onBeforeDispatch())
		{
			if (!JFactory::getUser()->authorise('core.manage', 'com_docimport'))
			{
				JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}

			// Language loading
			$paths	 = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
			$jlang	 = JFactory::getLanguage();
			// -- Component
			$jlang->load($this->component, $paths[0], 'en-GB', true);
			$jlang->load($this->component, $paths[0], null, true);
			$jlang->load($this->component, $paths[1], 'en-GB', true);
			$jlang->load($this->component, $paths[1], null, true);
			// -- Component overrides
			$jlang->load($this->component . '.override', $paths[0], 'en-GB', true);
			$jlang->load($this->component . '.override', $paths[0], null, true);
			$jlang->load($this->component . '.override', $paths[1], 'en-GB', true);
			$jlang->load($this->component . '.override', $paths[1], null, true);

			// Load Akeeba Strapper
			include_once JPATH_ROOT . '/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addCSSfile('media://com_docimport/css/backend.css');
		}
		return $result;
	}
}