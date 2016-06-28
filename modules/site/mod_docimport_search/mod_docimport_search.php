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

$links = $params->get('troubleshooter_links', array());
$links = str_replace("\r", "\n", $links);
$links = str_replace("\n\n", "\n", $links);
$links = explode("\n", $links);

$input = new \FOF30\Input\Input(array(
	'option' => 'com_docimport',
	'view' => 'Search',
	'troubleshooter_links' => $links
));

$container = FOF30\Container\Container::getInstance('com_docimport', array(
	'input' => $input
));

$container->dispatcher->dispatch();