<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Control Panel model
 *
 */
class DocimportModelCpanels extends F0FModel
{
	/**
	 * Update the cached live site's URL for the front-end backup feature (altbackup.php)
	 * and the detected Joomla! libraries path
	 */
	public function updateMagicParameters()
	{
		// Fetch component parameters
		$component = JComponentHelper::getComponent('com_docimport');
		if (is_object($component->params) && ($component->params instanceof JRegistry))
		{
			$params = $component->params;
		}
		else
		{
			$params = new JRegistry($component->params);
		}

		// Update magic parameters
		$params->set('siteurl', str_replace('/administrator', '', JURI::base()));
		$params->set('sitepath', str_replace('/administrator', '', JURI::base(true)));

		// Save parameters
		$db = JFactory::getDBO();
		$data = $params->toString();
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('element') . ' = ' . $db->q('com_docimport'))
			->where($db->qn('type') . ' = ' . $db->q('component'));
		$db->setQuery($sql);
		$db->execute();

		return $this;
	}

	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return DocimportModelCpanels
	 */
	public function checkAndFixDatabase()
	{
		// Install or update database
		$dbInstaller = new F0FDatabaseInstaller(array(
			'dbinstaller_directory' => JPATH_ADMINISTRATOR . '/components/com_docimport/sql/xml'
		));
		$dbInstaller->updateSchema();

		return $this;
	}
}