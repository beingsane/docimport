<?php
/**
 *  @package	docimport
 *  @copyright	Copyright (c)2010-2012 Nicholas K. Dionysopoulos / AkeebaBackup.com
 *  @license	GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *  @version 	$Id$
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

// no direct access
defined('_JEXEC') or die();

// =============================================================================
// Akeeba Component Installation Configuration
// =============================================================================
$installation_queue = array(
	// modules => { (folder) => { (module) => { (position), (published) } }* }*
	'modules' => array(
		'admin' => array(
		),
		'site' => array(
		)
	),
	// plugins => { (folder) => { (element) => (published) }* }*
	'plugins' => array(
		'search' => array(
			'docimport'		=> 1,
		)
	)
);

// Define files and directories to remove
$removeFiles = array(
);
$removeFolders = array(
);

// Joomla! 1.6 Beta 13+ hack
if( version_compare( JVERSION, '1.6.0', 'ge' ) && !defined('_AKEEBA_HACK') ) {
	return;
} else {
	global $akeeba_installation_has_run;
	if($akeeba_installation_has_run) return;
}

$db = JFactory::getDBO();

// =============================================================================
// Pre-installation checks
// =============================================================================

// Do you have at least Joomla! 1.5.14?
if(!version_compare(JVERSION, '1.5.14', 'ge')) {
	JError::raiseWarning(0, "The Joomla! version you are using is old, buggy, vulnerable and doesn't support Akeeba DocImport. Please upgrade your site then retry installing this component.");
	return false;
}

// Does the server has PHP 5.2.7 or later?
if(!version_compare(phpversion(), '5.2.7', 'ge')) {
	JError::raiseWarning(0, "Your PHP version is older than 5.2.7. Akeeba DocImport may not work properly!");
} elseif(!version_compare(phpversion(), '5.2.6', 'ge')) {
	JError::raiseWarning(0, "Your PHP version is older than 5.2.6. Akeeba DocImport <u>WILL NOT</u> work. The installation is aborted.");
	return false;
}

// Do we have the minimum required version of MySQL?
if(!version_compare($db->getVersion(), '5.0.41', 'ge')) {
	JError::raiseWarning(0, "Your MySQL version is older than 5.0.41. Akeeba DocImport can't work on such an old database server.");
	return false;
}

// =============================================================================
// Database update
// =============================================================================
// Sample
/**
$sql = 'SHOW CREATE TABLE `#__xxx`';
$db->setQuery($sql);
$ctableAssoc = $db->loadResultArray(1);
$ctable = empty($ctableAssoc) ? '' : $ctableAssoc[0];
if(!strstr($ctable, '`fieldname`'))
{
	$sql = "";
	$db->setQuery($sql);
	$status = $db->query();
}
/**/

// =============================================================================
// Sub-extension installation
// =============================================================================

// Setup the sub-extensions installer
jimport('joomla.installer.installer');
$db = & JFactory::getDBO();
$status = new JObject();
$status->modules = array();
$status->plugins = array();
$src = $this->parent->getPath('source');

// Remove unused files and folders (or the component will explode!)
if(!empty($removeFiles)) foreach($removeFiles as $removedFile) {
	$removePath = JPATH_SITE.'/'.$removedFile;
	if(JFile::exists($removePath)) JFile::delete($removePath);
}
if(!empty($removeFolders)) foreach($removeFolders as $removedFolder) {
	$removePath = JPATH_SITE.'/'.$removedFolder;
	if(JFolder::exists($removePath)) JFolder::delete(JPATH_SITE.'/'.$removedFolder);
}

// Modules installation
if(count($installation_queue['modules'])) {
	foreach($installation_queue['modules'] as $folder => $modules) {
		if(count($modules)) foreach($modules as $module => $modulePreferences) {
			// Install the module
			if(empty($folder)) $folder = 'site';
			$path = "$src/modules/$folder/$module";
			if(!is_dir($path)) continue;
			// Was the module alrady installed?
			$sql = 'SELECT COUNT(*) FROM #__modules WHERE `module`='.$db->Quote('mod_'.$module);
			$db->setQuery($sql);
			$count = $db->loadResult();
			$installer = new JInstaller;
			$result = $installer->install($path);
			$status->modules[] = array('name'=>'mod_'.$module, 'client'=>$folder, 'result'=>$result);
			// Modify where it's published and its published state
			if(!$count) {
				// A. Position and state
				list($modulePosition, $modulePublished) = $modulePreferences;
				if(version_compare(JVERSION, '2.5.0', 'ge') && ($modulePosition == 'cpanel')) {
					$modulePosition = 'icon';
				}
				$sql = "UPDATE #__modules SET position=".$db->Quote($modulePosition);
				if($modulePublished) $sql .= ', published=1';
				$sql .= ' WHERE `module`='.$db->Quote('mod_'.$module);
				$db->setQuery($sql);
				$db->query();
				if(version_compare(JVERSION, '1.7.0', 'ge')) {
					// B. Change the ordering of back-end modules to 1 + max ordering in J! 1.7+
					if($folder == 'admin') {
						$query = $db->getQuery(true);
						$query->select('MAX('.$db->nq('ordering').')')
							->from($db->nq('#__modules'))
							->where($db->nq('position').'='.$db->q($modulePosition));
						$db->setQuery($query);
						$position = $db->loadResult();
						$position++;
						
						$query = $db->getQuery(true);
						$query->update($db->nq('#__modules'))
							->set($db->nq('ordering').' = '.$db->q($position))
							->where($db->nq('module').' = '.$db->q('mod_'.$module));
						$db->setQuery($query);
						$db->query();
					}
					// C. Link to all pages on Joomla! 1.7+
					$query = $db->getQuery(true);
					$query->select('id')->from($db->nq('#__modules'))
						->where($db->nq('module').' = '.$db->q('mod_'.$module));
					$db->setQuery($query);
					$moduleid = $db->loadResult();
					
					$query = $db->getQuery(true);
					$query->select('*')->from($db->nq('#__modules_menu'))
						->where($db->nq('moduleid').' = '.$db->q($moduleid));
					$db->setQuery($query);
					$assignments = $db->loadObjectList();
					$isAssigned = !empty($assignments);
					if(!$isAssigned) {
						$o = (object)array(
							'moduleid'	=> $moduleid,
							'menuid'	=> 0
						);
						$db->insertObject('#__modules_menu', $o);
					}
				}
			}
		}
	}
}

// Plugins installation
if(count($installation_queue['plugins'])) {
	foreach($installation_queue['plugins'] as $folder => $plugins) {
		if(count($plugins)) foreach($plugins as $plugin => $published) {
			$path = "$src/plugins/$folder/$plugin";
			if(!is_dir($path)) continue;
			
			// Was the plugin already installed?
			if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
				$query = "SELECT COUNT(*) FROM  #__extensions WHERE element=".$db->Quote($plugin)." AND folder=".$db->Quote($folder);
			} else {
				$query = "SELECT COUNT(*) FROM  #__plugins WHERE element=".$db->Quote($plugin)." AND folder=".$db->Quote($folder);
			}
			$db->setQuery($query);
			$count = $db->loadResult();
			
			$installer = new JInstaller;
			$result = $installer->install($path);
			$status->plugins[] = array('name'=>'plg_'.$plugin,'group'=>$folder, 'result'=>$result);
			
			if($published && !$count) {
				if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
					$query = "UPDATE #__extensions SET enabled=1 WHERE element=".$db->Quote($plugin)." AND folder=".$db->Quote($folder);
				} else {
					$query = "UPDATE #__plugins SET published=1 WHERE element=".$db->Quote($plugin)." AND folder=".$db->Quote($folder);
				}
				$db->setQuery($query);
				$db->query();
			}
		}
	}
}

// Install the FOF framework
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.utilities.date');
$source = $src.'/fof';
if(!defined('JPATH_LIBRARIES')) {
	$target = JPATH_ROOT.'/libraries/fof';
} else {
	$target = JPATH_LIBRARIES.'/fof';
}
$haveToInstallFOF = false;
if(!JFolder::exists($target)) {
	JFolder::create($target);
	$haveToInstallFOF = true;
} else {
	$fofVersion = array();
	if(JFile::exists($target.'/version.txt')) {
		$rawData = JFile::read($target.'/version.txt');
		$info = explode("\n", $rawData);
		$fofVersion['installed'] = array(
			'version'	=> trim($info[0]),
			'date'		=> new JDate(trim($info[1]))
		);
	} else {
		$fofVersion['installed'] = array(
			'version'	=> '0.0',
			'date'		=> new JDate('2011-01-01')
		);
	}
	$rawData = JFile::read($source.'/version.txt');
	$info = explode("\n", $rawData);
	$fofVersion['package'] = array(
		'version'	=> trim($info[0]),
		'date'		=> new JDate(trim($info[1]))
	);
	
	$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
}

if($haveToInstallFOF) {
	$installedFOF = true;
	$files = JFolder::files($source);
	if(!empty($files)) {
		foreach($files as $file) {
			$installedFOF = $installedFOF && JFile::copy($source.'/'.$file, $target.'/'.$file);
		}
	}
}

$akeeba_installation_has_run = true;
?>

<h1>Akeeba DocImport<sup>3</sup></h1>
<?php $rows = 0;?>
<img src="../media/com_docimport/images/docimport-48.png" width="48" height="48" alt="Akeeba DocImport3" align="left" />
<h2 style="font-size: 14pt; font-weight: black; padding: 0; margin: 0 0 0.5em;">Welcome to Akeeba DocImport<sup>3</sup>!</h2>
<span>The easiest way to provide up-to-date documentation</span>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2">Extensions</th>
			<th width="30%">Status</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2">
				<img src="../media/com_docimport/images/docimport-16.png" width="16" height="16" alt="Akeeba DocImport" align="left" />
				&nbsp;
				<strong>Akeeba DocImprot Component</strong>
			</td>
			<td><strong style="color: green">Installed</strong></td>
		</tr>
		<tr class="row1">
			<td class="key" colspan="2">
				<strong>Framework on Framework (FOF)</strong>
			</td>
			<td><strong>
				<span style="color: <?php echo $haveToInstallFOF ? ($installedFOF?'green':'red') : '#660' ?>; font-weight: bold;">
					<?php echo $haveToInstallFOF ? ($installedFOF ?'Installed':'Not Installed') : 'Already up-to-date'; ?>
				</span>	
			</strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr>
			<th>Module</th>
			<th>Client</th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo empty($module['client']) ? 'site' : $module['client']; ?></td>
			<td>
				<span style="color: <?php echo ($module['result'])?'green':'red'?>; font-weight: bold;">
					<?php ($module['result'])?'Installed':'Not Installed'; ?>
				</span>
			</td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th>Plugin</th>
			<th>Group</th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $plugin['name']; ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td>
				<span style="color: <?php echo ($plugin['result'])?'green':'red'?>; font-weight: bold;">
					<?php ($plugin['result'])?'Installed':'Not Installed'; ?>
				</span>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>