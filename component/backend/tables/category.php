<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportTableCategory extends F0FTable
{
	function check()
	{
		$result = parent::check();

		if ($result)
		{
			if (empty($this->language))
			{
				$this->language = '*';
			}

			if (empty($this->access))
			{
				$this->access = 1;
			}
		}

		return $result;
	}
}