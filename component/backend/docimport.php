<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Include the component versioning
require_once JPATH_COMPONENT_ADMINISTRATOR.'/version.php';

// Include FOF
if(!defined('FOF_INCLUDED')) {
	include_once JPATH_LIBRARIES.'/fof/include.php';
}

if(!defined('FOF_INCLUDED') || !class_exists('FOFForm', true)) {?>
<h1>Akeeba DocImport<sup>3</sup></h1>
<h2>Incomplete installation detected</h2>
<p>
	Please consult the documentation
</p>
<?php return; }

// Dispatch
FOFDispatcher::getAnInstance('com_docimport')->dispatch();