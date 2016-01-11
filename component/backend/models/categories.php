<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportModelCategories extends F0FModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__docimport_categories'));

		$search = $this->getState('search', null, 'string');
		if (!empty($search))
		{
			$query->where(
				$db->quoteName('title') . ' LIKE ' . $db->quote('%' . $search . '%')
			);
		}

		$enabled = $this->getState('enabled', null, 'cmd');
		if (is_numeric($enabled))
		{
			$query->where(
				$db->quoteName('enabled') . ' = ' . $db->quote($enabled)
			);
		}

		$language = $this->getState('language', null, 'array');
		if (empty($language))
		{
			$language = $this->getState('language', null, 'string');
		}
		if (!empty($language) && (is_array($language) ? (!empty($language[0])) : true))
		{
			if (is_array($language))
			{
				$langs = array();
				foreach ($language as $l)
				{
					$langs[] = $db->quote($l);
				}
				$query->where(
					$db->quoteName('language') . ' IN (' . implode(',', $langs) . ')'
				);
			}
			else
			{
				$query->where(
					$db->quoteName('language') . ' = ' . $db->quote($language)
				);
			}
		}

		// Fix the ordering
		$order = $this->getState('filter_order', 'docimport_category_id', 'cmd');
		if (!in_array($order, array_keys($this->getTable()->getData())))
		{
			$order = 'docimport_category_id';
		}
		$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
		$query->order($order . ' ' . $dir);

		return $query;
	}

	public function onProcessList(&$resultArray)
	{
		JLoader::import('joomla.filesystem.folder');

		// First get the configured root directory
		JLoader::import('cms.component.helper');
		$cparams = JComponentHelper::getParams('com_docimport');
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		if (!empty($resultArray))
		{
			foreach ($resultArray as $key => $item)
			{
				$resultArray[$key]->status = 'missing';

				$folder = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $item->slug;

				if (!JFolder::exists($folder))
				{
					$folder = JPATH_ROOT . '/media/com_docimport/' . $item->slug;
				}

				if (!JFolder::exists($folder))
				{
					$folder = JPATH_ROOT . '/media/com_docimport/books/' . $item->slug;
				}

				if (!JFolder::exists($folder))
				{
					$resultArray[$key]->status = 'missing';
					continue;
				}

				$xmlfiles = JFolder::files($folder, '\.xml$', false, true);
				if (empty($xmlfiles))
				{
					continue;
				}

				$timestamp = 0;
				foreach ($xmlfiles as $filename)
				{
					$my_timestamp = @filemtime($filename);
					if ($my_timestamp > $timestamp)
					{
						$timestamp = $my_timestamp;
					}
				}

				if ($timestamp != $item->last_timestamp)
				{
					$resultArray[$key]->status = 'modified';
				}
				else
				{
					$resultArray[$key]->status = 'unmodified';
				}
			}
		}
	}
}