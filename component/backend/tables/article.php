<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportTableArticle extends F0FTable
{
	protected function onBeforeStore($updateNulls)
	{
		// Do we have a "Created" set of fields?
		$created_on = $this->getColumnAlias('created_on');
		$created_by = $this->getColumnAlias('created_by');
		$modified_on = $this->getColumnAlias('modified_on');
		$modified_by = $this->getColumnAlias('modified_by');
		$locked_on = $this->getColumnAlias('locked_on');
		$locked_by = $this->getColumnAlias('locked_by');
		$title = $this->getColumnAlias('title');
		$slug = $this->getColumnAlias('slug');

		if (property_exists($this, $created_on) && property_exists($this, $created_by))
		{
			if (empty($this->$created_by) || ($this->$created_on == '0000-00-00 00:00:00') || empty($this->$created_on))
			{
				$this->$created_by = JFactory::getUser()->id;
				JLoader::import('joomla.utilities.date');
				$date = new JDate();
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$this->$created_on = $date->toSql();
				}
				else
				{
					$this->$created_on = $date->toMysql();
				}
			}
			elseif (property_exists($this, $modified_on) && property_exists($this, $modified_by))
			{
				$this->$modified_by = JFactory::getUser()->id;
				JLoader::import('joomla.utilities.date');
				$date = new JDate();
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$this->$modified_on = $date->toSql();
				}
				else
				{
					$this->$modified_on = $date->toMysql();
				}
			}
		}

		// Do we have a set of title and slug fields?
		if (property_exists($this, $title) && property_exists($this, $slug))
		{
			if (empty($this->$slug))
			{
				// Create a slug from the title
				$this->$slug = F0FStringUtils::toSlug($this->$title);
			}
			else
			{
				// Filter the slug for invalid characters
				$this->$slug = F0FStringUtils::toSlug($this->$slug);
			}

			// Make sure we don't have a duplicate slug on this table
			$db = $this->getDbo();
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$query = $db->getQuery(true)
					->select($db->qn($slug))
					->from($this->_tbl)
					->where($db->qn($slug) . ' = ' . $db->q($this->$slug))
					->where('NOT ' . $db->qn($this->_tbl_key) . ' = ' . $db->q($this->{$this->_tbl_key}))
					->where($db->qn('docimport_category_id') . ' = ' . $db->q($this->docimport_category_id));
			}
			else
			{
				$query = $db->getQuery(true)
					->select($db->quoteName($slug))
					->from($this->_tbl)
					->where($db->quoteName($slug) . ' = ' . $db->quote($this->$slug))
					->where('NOT ' . $db->quoteName($this->_tbl_key) . ' = ' . $db->quote($this->{$this->_tbl_key}))
					->where($db->qn('docimport_category_id') . ' = ' . $db->q($this->docimport_category_id));
			}
			$db->setQuery($query);
			$existingItems = $db->loadAssocList();

			$count = 0;
			$newSlug = $this->$slug;
			while (!empty($existingItems))
			{
				$count++;
				$newSlug = $this->$slug . '-' . $count;
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$query = $db->getQuery(true)
						->select($db->qn($slug))
						->from($this->_tbl)
						->where($db->qn($slug) . ' = ' . $db->q($newSlug))
						->where($db->qn($this->_tbl_key) . ' = ' . $db->q($this->{$this->_tbl_key}), 'AND NOT');
				}
				else
				{
					$query = $db->getQuery(true)
						->select($db->quoteName($slug))
						->from($this->_tbl)
						->where($db->quoteName($slug) . ' = ' . $db->quote($newSlug))
						->where($db->quoteName($this->_tbl_key) . ' = ' . $db->quote($this->{$this->_tbl_key}), 'AND NOT');
				}
				$db->setQuery($query);
				$existingItems = $db->loadAssocList();
			}
			$this->$slug = $newSlug;
		}

		// Execute onBeforeStore<tablename> events in loaded plugins
		if ($this->_trigger_events)
		{
			$name = F0FInflector::pluralize($this->getKeyName());
			$dispatcher = JDispatcher::getInstance();

			return $dispatcher->trigger('onBeforeStore' . ucfirst($name), array(&$this, $updateNulls));
		}

		return true;
	}
}