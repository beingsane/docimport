<?php

/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportDispatcher extends F0FDispatcher
{

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->defaultView = 'categories';
	}

	public function onBeforeDispatch()
	{
		// You can't fix stupid… but you can try working around it
		if ((!function_exists('json_encode')) || (!function_exists('json_decode')))
		{
			require_once JPATH_ADMINISTRATOR . '/components/' . $this->component . '/helpers/jsonlib.php';
		}

		if ($result = parent::onBeforeDispatch())
		{
			$view = $this->input->getCmd('view', $this->defaultView);
			if (empty($view) || ($view == 'cpanel'))
			{
				$view = 'categories';
			}
			$this->input->set('view', $view);

			// Load Akeeba Strapper
			include_once JPATH_ROOT . '/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addCSSfile('media://com_docimport/css/frontend.css');

			// If the action is "add" in the front-end, map it to "read"
			$view	 = $this->input->getCmd('view', $this->defaultView);
			$task	 = $this->input->getCmd('task', '');
			if (empty($task))
			{
				$task = $this->getTask($view);
			}
			if ($task == 'add')
				$task = 'read';
			$this->input->set('view', $view);
			$this->input->set('task', $task);
		}

		return $result;
	}

}