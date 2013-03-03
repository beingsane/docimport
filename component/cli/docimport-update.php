<?php
/**
 *  @package DocImport3
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
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

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if(function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
	if(function_exists('error_reporting')) {
		$oldLevel = error_reporting(0);
	}
	$serverTimezone = @date_default_timezone_get();
	if(empty($serverTimezone) || !is_string($serverTimezone)) $serverTimezone = 'UTC';
	if(function_exists('error_reporting')) {
		error_reporting($oldLevel);
	}
	@date_default_timezone_set( $serverTimezone);
}

// Define ourselves as a parent file
define( '_JEXEC', 1 );
define('AKEEBAENGINE', 1); // Enable Akeeba Engine

// Required by the CMS
define('DS', DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(__FILE__).'/defines.php')) {
        include_once dirname(__FILE__).'/defines.php';
}
if (!defined('_JDEFINES')) {
        define('JPATH_BASE', dirname(__FILE__).'/../');
        require_once JPATH_BASE.'/includes/defines.php';
}

// Load the rest of the framework include files
include_once JPATH_LIBRARIES.'/import.php';
require_once JPATH_LIBRARIES.'/cms.php';

// Load the JApplicationCli class
JLoader::import( 'joomla.application.cli' );

class AppDocupdate extends JApplicationCli
{
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
		$this->_media = JPATH_ROOT.'/media/com_docimport/';
		
		// Get basic information
		require_once JPATH_ADMINISTRATOR . '/components/com_docimport/version.php';
		$version = DOCIMPORT_VERSION;
		$date = DOCIMPORT_DATE;
		$year = gmdate('Y');
		$phpversion = PHP_VERSION;
		$phpenvironment = PHP_SAPI;
		$phpos = PHP_OS;
		$memusage = $this->memUsage();
		$start_time = time();
		
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
		JLoader::import( 'joomla.environment.request' );
		JLoader::import( 'joomla.environment.uri' );
		
		// Load the translation strings
		$jlang = JFactory::getLanguage();
		$jlang->load('com_docimport', JPATH_ADMINISTRATOR, 'en-GB', true);
		
		// Load FOF
		require_once JPATH_LIBRARIES.'/fof/include.php';
		
		// Scan for any missing categories
		FOFModel::getTmpInstance('Xsl','DocimportModel',array('input'=>array()))
			->scanCategories();

		// List all categories
		$categories = FOFModel::getTmpInstance('Categories','DocimportModel')
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->getList();
		
		foreach($categories as $cat) {
			$this->out("Processing \"$cat->title\"");
			$model = FOFModel::getTmpInstance('Xsl','DocimportModel');
			$this->out("\tProcessing XML to HTML...");
			$status = $model->processXML($cat->docimport_category_id);
			if($status) {
				$this->out("\tGenerating articles...");
				$status = $model->processFiles($cat->docimport_category_id);
			}
			if($status) {
				$this->out("\tSuccess!");
			} else {
				$this->out("\tFAILED: ".$model->getError());
			}
		}
		
		$this->out('');
		$this->out('Documentation processing finished after approximately ' . $this->timeago($start_time, time(), '', false));
		$this->out('');
		$this->out("Peak memory usage: ".$this->peakMemUsage());
    }
	
	/**
	 * Returns the current memory usage
	 * 
	 * @return string 
	 */
	private function memUsage()
	{
		if(function_exists('memory_get_usage')) {
			$size = memory_get_usage();
			$unit=array('b','Kb','Mb','Gb','Tb','Pb');
			return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		} else {
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
		if(function_exists('memory_get_peak_usage')) {
			$size = memory_get_peak_usage();
			$unit=array('b','Kb','Mb','Gb','Tb','Pb');
			return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		} else {
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
	private function timeago($referencedate=0, $timepointer='', $measureby='', $autotext=true)
	{
		if($timepointer == '') {
			$timepointer = time();
		}
		
		// Raw time difference
		$Raw = $timepointer-$referencedate;
		$Clean = abs($Raw);
		
		$calcNum = array(
			array('s', 60),
			array('m', 60*60),
			array('h', 60*60*60),
			array('d', 60*60*60*24),
			array('y', 60*60*60*24*365)
		);
		
		$calc = array(
			's' => array(1, 'second'),
			'm' => array(60, 'minute'),
			'h' => array(60*60, 'hour'),
			'd' => array(60*60*24, 'day'),
			'y' => array(60*60*24*365, 'year')
		);

		if($measureby == ''){
			$usemeasure = 's';

			for($i=0; $i<count($calcNum); $i++){
				if($Clean <= $calcNum[$i][1]){
					$usemeasure = $calcNum[$i][0];
					$i = count($calcNum);
				}
			}
		} else {
			$usemeasure = $measureby;
		}

		$datedifference = floor($Clean/$calc[$usemeasure][0]);

		if($autotext==true && ($timepointer==time())){
			if($Raw < 0){
				$prospect = ' from now';
			} else {
				$prospect = ' ago';
			}
		} else {
			$prospect = '';
		}

		if($referencedate != 0){
			if($datedifference == 1){
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			} else {
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		} else {
			return 'No input time referenced.';
		}
	}
}
 
JCli::getInstance( 'AppDocupdate' )->execute( );
