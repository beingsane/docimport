<?php
/**
 *  @package	docimport
 *  @copyright	Copyright (c)2010-2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
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

// no direct access
defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('F0F_INCLUDED'))
{
	$paths = array(
		(defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f/include.php',
		__DIR__ . '/fof/include.php',
	);

	foreach ($paths as $filePath)
	{
		if (!defined('F0F_INCLUDED') && file_exists($filePath))
		{
			@include_once $filePath;
		}
	}
}

// Pre-load the installer script class from our own copy of FOF
if (!class_exists('F0FUtilsInstallscript', false))
{
	@include_once __DIR__ . '/fof/utils/installscript/installscript.php';
}

// Pre-load the database schema installer class from our own copy of FOF
if (!class_exists('F0FDatabaseInstaller', false))
{
	@include_once __DIR__ . '/fof/database/installer.php';
}

// Pre-load the update utility class from our own copy of FOF
if (!class_exists('F0FUtilsUpdate', false))
{
	@include_once __DIR__ . '/fof/utils/update/update.php';
}

// Pre-load the cache cleaner utility class from our own copy of FOF
if (!class_exists('F0FUtilsCacheCleaner', false))
{
	@include_once __DIR__ . '/fof/utils/cache/cleaner.php';
}

class Com_DocimportInstallerScript extends F0FUtilsInstallscript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'com_docimport';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'DocImport<sup>3</sup>';

	/**
	 * The list of extra modules and plugins to install on component installation / update and remove on component
	 * uninstallation.
	 *
	 * @var   array
	 */
	protected $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(
				//'foobar' => array('cpanel', 1)
			),
			'site' => array(
				//'foobar' => array('left', 0),
			)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'search' => array(
				'docimport'					=> 1,
			),
            'sh404sefextplugins' => array(
                'com_docimport'             => 1
            ),
			'finder' => array(
				'docimport'					=> 1,
			),
		)
	);

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		'files'	=> array(
			'cache/com_docimport.updates.php',
			'cache/com_docimport.updates.ini',
			'administrator/cache/com_docimport.updates.php',
			'administrator/cache/com_docimport.updates.ini',
			'components/com_docimport/controllers/article.php',
		),
		'folders' => array(
		)
	);

	/**
	 * A list of scripts to be copied to the "cli" directory of the site
	 *
	 * @var   array
	 */
	protected $cliScriptFiles = array(
		'docimport-update.php',
		'docimport-upgrade.php',
	);

	/**
	 * Renders the post-installation message
	 */
	protected function renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent)
	{
		$this->warnAboutJSNPowerAdmin();
?>
		<h1>Akeeba DocImport</h1>

		<div style="margin: 1em; font-size: 14pt; background-color: #fffff9; color: black">
			You can download translation files <a href="http://akeeba-cdn.s3-website-eu-west-1.amazonaws.com/language/docimport/">directly from our CDN page</a>.
		</div>
		<img src="../media/com_docimport/images/docimport-48.png" width="48" height="48" alt="Akeeba DocImport" align="left" />
		<h2 style="font-size: 14pt; font-weight: black; padding: 0; margin: 0 0 0.5em;">&nbsp;Welcome to Akeeba DocImport!</h2>
		<span>
			The easiest way to provide up-to-date documentation
		</span>

		<?php
		parent::renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent);
	}

	protected function renderPostUninstallation($status, $parent)
	{
?>
<h2 style="font-size: 14pt; font-weight: black; padding: 0; margin: 0 0 0.5em;">&nbsp;Akeeba DocImport Uninstallation</h2>
<p>We are sorry that you decided to uninstall Akeeba DocImport. Please let us know why by using the Contact Us form on our site. We appreciate your feedback; it helps us develop better software!</p>

<?php
		parent::renderPostUninstallation($status, $parent);
	}


	/**
	 * The PowerAdmin extension makes menu items disappear. People assume it's our fault. JSN PowerAdmin authors don't
	 * own up to their software's issue. I have no choice but to warn our users about the faulty third party software.
	 */
	private function warnAboutJSNPowerAdmin()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
			->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$hasPowerAdmin = $db->setQuery($query)->loadResult();

		if (!$hasPowerAdmin)
		{
			return;
		}

		echo <<< HTML
<div class="well" style="margin: 2em 0;">
<h1 style="font-size: 32pt; line-height: 120%; color: red; margin-bottom: 1em">WARNING: Menu items for {$this->componentName} might not be displayed on your site.</h1>
<p style="font-size: 18pt; line-height: 150%; margin-bottom: 1.5em">
	We have detected that you are using JSN PowerAdmin on your site. This software ignores Joomla! standards and
	<b>hides</b> the Component menu items to {$this->componentName} in the administrator backend of your site. Unfortunately we
	can't provide support for third party software. Please contact the developers of JSN PowerAdmin for support
	regarding this issue.
</p>
<p style="font-size: 18pt; line-height: 120%; color: green;">
	Tip: You can disable JSN PowerAdmin to see the menu items to Akeeba Backup.
</p>
</div>

HTML;

	}

}