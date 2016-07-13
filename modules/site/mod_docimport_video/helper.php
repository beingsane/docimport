<?php
/**
 *  @package	docimport
 *  @copyright	Copyright (c)2010-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 *  @license	GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

abstract class ModDocimportVideoHelper
{
	public static function getArticles($categories)
	{
		// Let's include our container so the autoloader kicks in
		FOF30\Container\Container::getInstance('com_docimport');

		$db = JFactory::getDbo();

		$categories = array_map(array($db, 'quote'), $categories);

		$query = $db->getQuery(true)
			->select([
				$db->qn('a.id'),
				$db->qn('a.title'),
				$db->qn('a.alias'),
				$db->qn('a.catid'),
				$db->qn('c.title', 'catname'),
				$db->qn('c.alias', 'catalias'),
				$db->qn('a.introtext'),
				$db->qn('a.fulltext'),
				$db->qn('a.language'),
				$db->qn('a.created'),
				$db->qn('a.modified'),
			])
			->from($db->qn('#__content', 'a'))
			->innerJoin($db->qn('#__categories', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('a.catid') . ')')
			->where($db->qn('a.state') . ' = ' . $db->q('1'))
			->where($db->qn('c.published') . ' = ' . $db->q('1'))
			->where($db->qn('a.catid') . ' IN (' . implode(',', $categories) . ')' );

		$articles = $db->setQuery($query)->loadObjectList('', 'Akeeba\DocImport\Site\Model\Search\Result\JoomlaArticle');

		return $articles;
	}
}