<?php
/**
 * @package LiveUpdate
 * @copyright Copyright ©2011 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 */

defined('_JEXEC') or die();

/**
 * Configuration class for your extension's updates. Override to your liking.
 */
class LiveUpdateConfig extends LiveUpdateAbstractConfig
{
	var $_extensionName			= 'com_docimport';
	var $_versionStrategy		= 'different';
	var $_storageAdapter		= 'component';
	var $_storageConfig			= array(
		'extensionName'	=> 'com_docimport',
		'key'			=> 'liveupdate'
	);

	function __construct()
	{
		JLoader::import('joomla.filesystem.file');

		// Load the component parameters, not using JComponentHelper to avoid conflicts ;)
		JLoader::import('joomla.html.parameter');
		JLoader::import('joomla.application.component.helper');
		$db = JFactory::getDbo();
		$sql = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type').' = '.$db->quote('component'))
			->where($db->quoteName('element').' = '.$db->quote('com_docimport'));
		$db->setQuery($sql);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$params->loadString($rawparams, 'JSON');
		} else {
			$params->loadJSON($rawparams);
		}

		$this->_updateURL = 'http://cdn.akeebabackup.com/updates/docimport.ini';
		$this->_extensionTitle = 'Akeeba DocImport';

		// Set up the version strategy
		if(defined('DOCIMPORT_VERSION')) {
			if(in_array(substr(DOCIMPORT_VERSION, 0, 3), array('svn','dev','rev'))) {
				// Dev releases use the "newest" (date comparison) strategy.
				$this->_versionStrategy = 'newest';
			} else {
				// In all other cases, we check for a different version
				$this->_versionStrategy = 'different';
			}
		}

		// Get the minimum stability level for updates
		$this->_minStability = $params->get('minstability', 'alpha');

		//Force authorization
		$this->_requiresAuthorization = false;

		// Should I use our private CA store?
		if(@file_exists(dirname(__FILE__).'/../assets/cacert.pem')) {
			$this->_cacerts = dirname(__FILE__).'/../assets/cacert.pem';
		}

		parent::__construct();
	}
}