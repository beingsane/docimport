<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Database\Installer;
use FOF30\Model\Model;
use JRegistry;
use JFactory;

class ControlPanel extends Model
{
	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return  $this
	 */
	public function checkAndFixDatabase()
	{
		$db = $this->container->platform->getDbo();

		$dbInstaller = new Installer($db, JPATH_ADMINISTRATOR . '/components/com_docimport/sql/xml');
		$dbInstaller->updateSchema();

		return $this;
	}

	/**
	 * Save some magic variables we need
	 *
	 * @return  $this
	 */
	public function updateMagicParameters()
	{
		// Store the URL to this site
		$db = $this->container->platform->getDbo();
		$query = $db->getQuery(true)
			->select('params')
			->from($db->qn('#__extensions'))
			->where($db->qn('element') . '=' . $db->q('com_docimport'))
			->where($db->qn('type') . '=' . $db->q('component'));
		$db->setQuery($query);
		$rawparams = $db->loadResult();

		$params = new JRegistry();
		$params->loadString($rawparams, 'JSON');

		$siteURL_stored = $params->get('siteurl', '');
		$siteURL_target = str_replace('/administrator', '', \JUri::base());

		$sitePath_stored = $params->get('sitepath', '');
		$sitePath_target = str_replace('/administrator', '', \JUri::base(true));

		if (($siteURL_target != $siteURL_stored) || ($sitePath_target != $sitePath_stored))
		{
			$params->set('siteurl', $siteURL_target);
			$params->set('sitepath', $sitePath_target);

			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('params') . '=' . $db->q($params->toString()))
				->where($db->qn('element') . '=' . $db->q('com_docimport'))
				->where($db->qn('type') . '=' . $db->q('component'));
			$db->setQuery($query);
			$db->execute();
		}

		return $this;
	}

}