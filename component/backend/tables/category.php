<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportTableCategory extends FOFTable
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