<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Control Panel model
 *
 */
class DocimportModelCpanels extends FOFModel
{
	/**
	 * Update the cached live site's URL for the front-end backup feature (altbackup.php)
	 * and the detected Joomla! libraries path
	 */
	public function updateMagicParameters()
	{
		// Fetch component parameters
		$component = JComponentHelper::getComponent( 'com_docimport' );
		if(is_object($component->params) && ($component->params instanceof JRegistry)) {
			$params = $component->params;
		} else {
			$params = new JParameter($component->params);
		}

		// Update magic parameters
		$params->set( 'siteurl', str_replace('/administrator','',JURI::base()) );

		// Save parameters
		$db = JFactory::getDBO();
		$data = $params->toString();
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params').' = '.$db->q($data))
			->where($db->qn('element').' = '.$db->q('com_docimport'))
			->where($db->qn('type').' = '.$db->q('component'));
		$db->setQuery($sql);
		$db->execute();

		return $this;
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  DocimportModelCpanels
	 */
	public function refreshUpdateSite()
	{
		// Create the update site definition we want to store to the database
		$update_site = array(
			'name'		=> 'Akeeba DocImport',
			'type'		=> 'extension',
			'location'	=> 'http://cdn.akeebabackup.com/updates/docimport.xml',
			'enabled'	=> 1,
			'last_check_timestamp'	=> 0,
			'extra_query'	=> null
		);

		if (version_compare(JVERSION, '3.2.1', 'lt'))
		{
			unset ($update_site['extra_query']);
		}

		$db = $this->getDbo();

		// Get the extension ID to ourselves
		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_docimport'));
		$db->setQuery($query);

		$extension_id = $db->loadResult();

		if (empty($extension_id))
		{
			return;
		}

		// Get the update sites for our extension
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$updateSiteIDs = $db->loadColumn(0);

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object)$update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object)array(
				'update_site_id'	=> $id,
				'extension_id'		=> $extension_id,
			);
			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				// Does the name and location match?
				if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
				{
					continue;
				}

				$update_site['update_site_id'] = $id;
				$newSite = (object)$update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}

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
		$dbFilePath = JPATH_ADMINISTRATOR . '/components/com_docimport/sql';
		if (!class_exists('AkeebaDatabaseInstaller'))
		{
			require_once $dbFilePath . '/dbinstaller.php';
		}
		$dbInstaller = new AkeebaDatabaseInstaller(JFactory::getDbo());
		$dbInstaller->setXmlDirectory($dbFilePath . '/xml');
		$dbInstaller->updateSchema();

		return $this;
	}
}