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

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

$category = $params->get('catid', 0);

if (!$category)
{
	return;
}

$container = FOF30\Container\Container::getInstance('com_docimport');

/** @var \Akeeba\DocImport\Site\Model\Articles $article */
$article = $container->factory->model('Articles')->tmpInstance();
$article->category($category);

try
{
	$article->findOrFail(array('slug' => 'index'));
}
catch (\Exception $e)
{
	return;
}

// Since the TOC has a fixed HTML structure, I can use a strpos instead of parsing the whole HTML text
// this will be much faster than creating a DOMobject and trying to traverse it
$start = strpos($article->fulltext, '<dl class="toc">');
$stop  = strrpos($article->fulltext, '</dl>') + 5;
$toc   = substr($article->fulltext, $start, $stop - $start);

$container->template->addCSS('site://modules/mod_docimport_toc/assets/mod_docimport_toc.css', $container->mediaVersion);
$container->template->addCSS('media://com_docimport/css/frontend.css', $container->mediaVersion);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

// Load the layout file
require_once JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));