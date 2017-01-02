<?php
/**
 * @package   DocImport3
 * @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
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

use \Joomla\Registry\Registry;

// Define ourselves as a parent file
define('_JEXEC', 1);

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
require_once JPATH_LIBRARIES . '/import.legacy.php';
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
	 * @param   JInputCli         $input       Input object
	 * @param   Registry          $config      Configuration for the CLI application object
	 * @param   JEventDispatcher  $dispatcher  Events dispatcher, used to
	 */
	public function __construct(JInputCli $input = null, Registry $config = null, JEventDispatcher $dispatcher = null)
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

					$query = ltrim($query);
					$argv  = explode(' ', $query);

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
	 *
	 * @return  void
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
		$version        = DOCIMPORT_VERSION;
		$date           = DOCIMPORT_DATE;
		$year           = gmdate('Y');
		$phpversion     = PHP_VERSION;
		$phpenvironment = PHP_SAPI;
		$phpos          = PHP_OS;
		$memusage       = $this->memUsage();
		$start_time     = time();

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

		error_reporting(E_ALL);
		ini_set('display_error', 1);

		// Load Joomla! classes
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.environment.request');
		JLoader::import('joomla.environment.uri');

		// Load the translation strings
		$jlang = JFactory::getLanguage();
		$jlang->load('com_docimport', JPATH_ADMINISTRATOR, 'en-GB', true);

		// Load FOF
		require_once JPATH_LIBRARIES . '/fof30/include.php';

		// Get the component container
		$container = \FOF30\Container\Container::getInstance('com_docimport', [], 'admin');
		$container->factoryClass = '\\FOF30\\Factory\\SwitchFactory';

		// Force the server root URL
		$rootURL = $container->params->get('siteurl', '');

		if (!empty($rootURL))
		{
			$rootURL = rtrim($rootURL, '/') . '/';
			define('DOCIMPORT_SITEURL', $rootURL);
		}

		$rootPath = $container->params->get('sitepath', '');

		if (!empty($rootPath))
		{
			$rootPath = rtrim($rootPath, '/') . '/';
			define('DOCIMPORT_SITEPATH', $rootPath);
		}

		// Scan for any missing categories
		/** @var \Akeeba\DocImport\Admin\Model\Xsl $xslModel */
		$xslModel = $container->factory->model('Xsl')->tmpInstance();
		$xslModel->scanCategories();

		// List all categories
		/** @var \Akeeba\DocImport\Admin\Model\Categories $categoriesModel */
		$categoriesModel = $container->factory->model('Categories')->tmpInstance();

		$categories = $categoriesModel
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->get();

		/** @var \Akeeba\DocImport\Admin\Model\Categories $cat */
		foreach ($categories as $cat)
		{
			$this->out("Processing \"$cat->title\"");

			/** @var \Akeeba\DocImport\Admin\Model\Xsl $model */
			$model = $xslModel->tmpInstance();

			try
			{
				$this->out("\tProcessing XML to HTML...");
				$model->processXML($cat->docimport_category_id);

				$this->out("\tGenerating articles...");
				$model->processFiles($cat->docimport_category_id);

				$this->out("\tSuccess!");
			}
			catch (\RuntimeException $e)
			{
				$this->out("\tFAILED: " . $e->getMessage());
			}
		}

		$this->out('');
		$this->out('Documentation processing finished after approximately ' . $this->timeAgo($start_time, time(), '', false));
		$this->out('');
		$this->out("Peak memory usage: " . $this->peakMemUsage());
	}

	/**
	 * Returns the current memory usage
	 *
	 * @return  string
	 */
	private function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size = memory_get_usage();
			$unit = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');

			return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[ $i ];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage
	 *
	 * @return  string
	 */
	private function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size = memory_get_peak_usage();
			$unit = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');

			return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[ $i ];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param   int     $referenceDate Timestamp of the reference date/time
	 * @param   int     $timePointer   Timestamp of the current date/time
	 * @param   string  $measureBy     One of s, m, h, d, or y (time unit)
	 * @param   boolean $autoText      Automatically add ago/from now
	 *
	 * @return  string
	 */
	private function timeAgo($referenceDate = 0, $timePointer = null, $measureBy = '', $autoText = true)
	{
		if (is_null($timePointer))
		{
			$timePointer = time();
		}

		// Raw time difference
		$Raw   = $timePointer - $referenceDate;
		$Clean = abs($Raw);

		$calcNum = array(
			array('s', 60),
			array('m', 60 * 60),
			array('h', 60 * 60 * 60),
			array('d', 60 * 60 * 60 * 24),
			array('y', 60 * 60 * 60 * 24 * 365)
		);

		$calc = array(
			's' => array(1, 'second'),
			'm' => array(60, 'minute'),
			'h' => array(60 * 60, 'hour'),
			'd' => array(60 * 60 * 24, 'day'),
			'y' => array(60 * 60 * 24 * 365, 'year')
		);

		if ($measureBy == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($Clean <= $calcNum[ $i ][1])
				{
					$usemeasure = $calcNum[ $i ][0];
					$i          = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureBy;
		}

		$datedifference = floor($Clean / $calc[ $usemeasure ][0]);

		if ($autoText == true && ($timePointer == time()))
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

		if ($referenceDate != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[ $usemeasure ][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[ $usemeasure ][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}
}

JApplicationCli::getInstance('AppDocupdate')->execute();
