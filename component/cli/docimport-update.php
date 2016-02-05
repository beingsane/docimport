<?php
/**
 *  @package DocImport3
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
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
 *
 *  --
 *
 *  Command-line script to schedule the documentation rebuild
 */
// Define ourselves as a parent file
define('_JEXEC', 1);

// Enable Akeeba Engine
define('AKEEBAENGINE', 1);

// Required by the CMS
define('DS', DIRECTORY_SEPARATOR);

$minphp = '5.3.4';
if (version_compare(PHP_VERSION, $minphp, 'lt'))
{
	$curversion = PHP_VERSION;
	$bindir = PHP_BINDIR;
	echo <<< ENDWARNING
================================================================================
WARNING! Incompatible PHP version $curversion
================================================================================

This CRON script must be run using PHP version $minphp or later. Your server is
currently using a much older version which would cause this script to crash. As
a result we have aborted execution of the script. Please contact your host and
ask them for the correct path to the PHP CLI binary for PHP $minphp or later, then
edit your CRON job and replace your current path to PHP with the one your host
gave you.

For your information, the current PHP version information is as follows.

PATH:    $bindir
VERSION: $curversion

Further clarifications:

1. There is absolutely no possible way that you are receiving this warning in
   error. We are using the PHP_VERSION constant to detect the PHP version you
   are currently using. This is what PHP itself reports as its own version. It
   simply cannot lie.

2. Even though your *site* may be running in a higher PHP version that the one
   reported above, your CRON scripts will most likely not be running under it.
   This has to do with the fact that your site DOES NOT run under the command
   line and there are different executable files (binaries) for the web and
   command line versions of PHP.

3. Please note that you MUST NOT ask us for support about this error. We cannot
   possibly know the correct path to the PHP CLI binary as we have not set up
   your server. Your host must know and give that information.

4. The latest published versions of PHP can be found at http://www.php.net/
   Any older version is considered insecure and must NOT be used on a live
   server. If your server uses a much older version of PHP than that please
   notify them that their servers are insecure and in need of an update.

This script will now terminate. Goodbye.

ENDWARNING;
	die();
}

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	if (function_exists('error_reporting'))
	{
		$oldLevel = error_reporting(0);
	}
	$serverTimezone	 = @date_default_timezone_get();
	if (empty($serverTimezone) || !is_string($serverTimezone))
		$serverTimezone	 = 'UTC';
	if (function_exists('error_reporting'))
	{
		error_reporting($oldLevel);
	}
	@date_default_timezone_set($serverTimezone);
}

// Load system defines
if (file_exists(__DIR__ . '/defines.php'))
{
	include_once __DIR__ . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	$path = rtrim(__DIR__, DIRECTORY_SEPARATOR);
	$rpos = strrpos($path, DIRECTORY_SEPARATOR);
	$path = substr($path, 0, $rpos);
	define('JPATH_BASE', $path);
	require_once JPATH_BASE . '/includes/defines.php';
}

// Load the rest of the framework include files
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}
else
{
	require_once JPATH_LIBRARIES . '/import.php';
}
require_once JPATH_LIBRARIES . '/cms.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');

if (version_compare(JVERSION, '3.4.9999', 'ge'))
{
	// Joomla! 3.5 and later does not load the configuration.php unless you explicitly tell it to.
	JFactory::getConfig(JPATH_CONFIGURATION . '/configuration.php');
}

class AppDocupdate extends JApplicationCli
{
	/**
	 * JApplicationCli didn't want to run on PHP CGI. I have my way of becoming
	 * VERY convincing. Now obey your true master, you petty class!
	 *
	 * @param JInputCli $input
	 * @param JRegistry $config
	 * @param JDispatcher $dispatcher
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null)
	{
		// Close the application if we are not executed from the command line, Akeeba style (allow for PHP CGI)
		if (array_key_exists('REQUEST_METHOD', $_SERVER))
		{
			die('You are not supposed to access this script from the web. You have to run it from the command line. If you don\'t understand what this means, you must not try to use this file before reading the documentation. Thank you.');
		}

		$cgiMode = false;

		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$cgiMode = true;
		}

		// If a input object is given use it.
		if ($input instanceof JInput)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			if (class_exists('JInput'))
			{
				if ($cgiMode)
				{
					$query = "";
					if (!empty($_GET))
					{
						foreach ($_GET as $k => $v)
						{
							$query .= " $k";
							if ($v != "")
							{
								$query .= "=$v";
							}
						}
					}
					$query	 = ltrim($query);
					$argv	 = explode(' ', $query);
					$argc	 = count($argv);

					$_SERVER['argv'] = $argv;
				}

				$this->input = new JInputCLI();
			}
		}

		// If a config object is given use it.
		if ($config instanceof JRegistry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new JRegistry;
		}

		// If a dispatcher object is given use it.
		if ($dispatcher instanceof JDispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else
		{
			$this->loadDispatcher();
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());

		// Work around Joomla! 3.4.7's JSession bug
		if (version_compare(JVERSION, '3.4.7', 'eq'))
		{
			JFactory::getSession()->restart();
		}
	}

	/**
	 * The main entry point of the application
	 */
	public function execute()
	{
		// Set all errors to output the messages to the console, in order to
		// avoid infinite loops in JError ;)
		restore_error_handler();
		error_reporting(E_ERROR);
		JError::setErrorHandling(E_ERROR, 'die');
		JError::setErrorHandling(E_WARNING, 'ignore');
		JError::setErrorHandling(E_NOTICE, 'ignore');

		// Set up the path of our media directory
		$this->_media = JPATH_ROOT . '/media/com_docimport/';

		// Get basic information
		require_once JPATH_ADMINISTRATOR . '/components/com_docimport/version.php';
		$version		 = DOCIMPORT_VERSION;
		$date			 = DOCIMPORT_DATE;
		$year			 = gmdate('Y');
		$phpversion		 = PHP_VERSION;
		$phpenvironment	 = PHP_SAPI;
		$phpos			 = PHP_OS;
		$memusage		 = $this->memUsage();
		$start_time		 = time();

		echo <<<ENDBLOCK
Akeeba DocImport³ CLI $version ($date)
Copyright (C) 2010-$year Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Akeeba Backup is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------
You are using PHP $phpversion ($phpenvironment) on $phpos

Starting documentation category rebuild

Current memory usage: $memusage

ENDBLOCK;

		// Load Joomla! classes
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.environment.request');
		JLoader::import('joomla.environment.uri');

		// Load the translation strings
		$jlang = JFactory::getLanguage();
		$jlang->load('com_docimport', JPATH_ADMINISTRATOR, 'en-GB', true);

		// Load F0F
		require_once JPATH_LIBRARIES . '/f0f/include.php';

		// Force-load the back-end Categories model (the front-end model doesn't work under CLI)
		require_once JPATH_ADMINISTRATOR . '/components/com_docimport/models/categories.php';

		// Force the server root URL
		$rootURL = $this->getOptionValue('siteurl', '');

		if (!empty($rootURL))
		{
			$rootURL = rtrim($rootURL, '/') . '/';
			define('DOCIMPORT_SITEURL', $rootURL);
		}

		$rootPath = $this->getOptionValue('sitepath', '');

		if (!empty($rootPath))
		{
			$rootPath = rtrim($rootPath, '/') . '/';
			define('DOCIMPORT_SITEPATH', $rootPath);
		}

		// Scan for any missing categories
		F0FModel::getTmpInstance('Xsl', 'DocimportModel', array('input' => array()))
			->scanCategories();

		// List all categories
		$categories = F0FModel::getTmpInstance('Categories', 'DocimportModel')
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->getList();

		foreach ($categories as $cat)
		{
			$this->out("Processing \"$cat->title\"");

			$model	 = F0FModel::getTmpInstance('Xsl', 'DocimportModel');

			$this->out("\tProcessing XML to HTML...");

			$status	 = $model->processXML($cat->docimport_category_id);

			if ($status)
			{
				$this->out("\tGenerating articles...");
				$status = $model->processFiles($cat->docimport_category_id);
			}

			if ($status)
			{
				$this->out("\tSuccess!");
			}
			else
			{
				$this->out("\tFAILED: " . $model->getError());
			}
		}

		$this->out('');
		$this->out('Documentation processing finished after approximately ' . $this->timeago($start_time, time(), '', false));
		$this->out('');
		$this->out("Peak memory usage: " . $this->peakMemUsage());
	}

	/**
	 * Returns the current memory usage
	 *
	 * @return string
	 */
	private function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size	 = memory_get_usage();
			$unit	 = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
			return @round($size / pow(1024, ($i		 = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage
	 *
	 * @return string
	 */
	private function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size	 = memory_get_peak_usage();
			$unit	 = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
			return @round($size / pow(1024, ($i		 = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param  $referencedate	int		Timestamp of the reference date/time
	 * @param  $timepointer		int		Timestamp of the current date/time
	 * @param  $measureby		string	One of s, m, h, d, or y (time unit)
	 * @param  $autotext			bool
	 *
	 * @return  string
	 */
	private function timeago($referencedate = 0, $timepointer = '', $measureby = '', $autotext = true)
	{
		if ($timepointer == '')
		{
			$timepointer = time();
		}

		// Raw time difference
		$Raw	 = $timepointer - $referencedate;
		$Clean	 = abs($Raw);

		$calcNum = array(
			array('s', 60),
			array('m', 60 * 60),
			array('h', 60 * 60 * 60),
			array('d', 60 * 60 * 60 * 24),
			array('y', 60 * 60 * 60 * 24 * 365)
		);

		$calc = array(
			's'	 => array(1, 'second'),
			'm'	 => array(60, 'minute'),
			'h'	 => array(60 * 60, 'hour'),
			'd'	 => array(60 * 60 * 24, 'day'),
			'y'	 => array(60 * 60 * 24 * 365, 'year')
		);

		if ($measureby == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($Clean <= $calcNum[$i][1])
				{
					$usemeasure	 = $calcNum[$i][0];
					$i			 = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureby;
		}

		$datedifference = floor($Clean / $calc[$usemeasure][0]);

		if ($autotext == true && ($timepointer == time()))
		{
			if ($Raw < 0)
			{
				$prospect = ' from now';
			}
			else
			{
				$prospect = ' ago';
			}
		}
		else
		{
			$prospect = '';
		}

		if ($referencedate != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}

	private function loadComponentOptions()
	{
		$db = JFactory::getDbo();

		$sql		 = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . " = " . $db->q('component'))
			->where($db->qn('element') . " = " . $db->q('com_docimport'));
		$db->setQuery($sql);
		$config_ini	 = $db->loadResult();

		$config_ini = json_decode($config_ini, true);
		if (is_null($config_ini) || empty($config_ini))
		{
			$config_ini = array();
		}
		return $config_ini;
	}

	public function getOptionValue($key, $default)
	{
		static $config;
		if (empty($config))
		{
			$config = $this->loadComponentOptions();
		}

		if (array_key_exists($key, $config))
		{
			return $config[$key];
		}
		else
		{
			return $default;
		}
	}

}
JApplicationCli::getInstance('AppDocupdate')->execute();
