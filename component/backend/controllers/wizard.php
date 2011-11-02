<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2008 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

class DocimportControllerWizard extends JController
{
	function display()
	{
		$format = JRequest::getCmd('format','html');

		// For raw view with default task use the default_raw.php template file
		if($format == 'raw')
		{
			JRequest::setVar('tpl', 'raw');
		}

		parent::display();
	}

	function upload()
	{
		// Do we have a docpackage.jpa file in the temporary directory?
		$jreg =& JFactory::getConfig();
		$tempdir = $jreg->getValue('config.tmp_path');

		jimport('joomla.filesystem.file');

		// Get uploaded file name
		$fileDescriptor = JRequest::getVar('userfile', '', 'FILES', 'array');

		// Did someone forget to specify a file at all?
		if( ((!is_array($fileDescriptor)) || (!isset($fileDescriptor['name'])) || ($fileDescriptor['size'] < 1) )
		&& (JRequest::getVar('filename', '') == '') )
		{
			$this->setRedirect('index.php?option=com_docimport', 'You have to specify a file to use with either method and that its size does not exceed your server limits', 'error');
			return;
		}

		// Make sure a category was chosen
		$category = JRequest::getString('catid', -1);
		if($category <= 0)
		{
			$this->setRedirect('index.php?option=com_docimport', 'No category specified!', 'error');
			return;
		}

		jimport('joomla.filesystem.file');

		// Is there a user set filename from the temp dir?
		if( JRequest::getVar('filename', '') != '' )
		{
			$jreg =& JFactory::getConfig();
			$tempdir = $jreg->getValue('config.tmp_path');
			$sourceLocation = $tempdir.DS.JRequest::getVar('filename', '');

			// Move file
			$targetLocation = $tempdir.DS.'docpackage.jpa';

			if(!JFile::move($sourceLocation, $targetLocation))
			{
				$this->setRedirect('index.php?option=com_docimport', 'Could not move the uploaded file to the storage area', 'error');
				return;
			}
		}
		else
		{
			// Handle no uploaded file error
			if( (!is_array($fileDescriptor)) || (!isset($fileDescriptor['name'])) )
			{
				$this->setRedirect('index.php?option=com_docimport', 'No file was specified', 'error');
				return;
			}

			// Handle zero length file
			if($fileDescriptor['size'] < 1)
			{
				$this->setRedirect('index.php?option=com_docimport', 'Zero length file', 'error');
				return;
			}

			// Handle error in upload
			if($fileDescriptor['error'] != 0)
			{
				$this->setRedirect('index.php?option=com_docimport', 'Transport error', 'error');
				return;
			}

			// Move file
			$sourceLocation = $fileDescriptor['tmp_name'];
			$targetLocation = $tempdir.DS.'docpackage.jpa';

			// First remove any old files
			if(JFile::exists($targetLocation))
			{
				if(!JFile::delete($targetLocation))
				{
					$this->setRedirect('index.php?option=com_docimport', 'Could not delete the old file from the tmp directory', 'error');
					return;
				}
			}

			if(!JFile::upload($sourceLocation, $targetLocation))
			{
				$this->setRedirect('index.php?option=com_docimport', 'Could not move the uploaded file to the storage area', 'error');
				return;
			}

		}

		// Open archive
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'unjpa.php';
		$archiver =& DocimportUnjpa::getInstance($targetLocation);

		if(!$archiver->readHeader())
		{
			$this->setRedirect('index.php?option=com_docimport', 'Uploaded file is not a valid JPA archive', 'error');
			return;
		}
		else
		{
			$offset = $archiver->offset;
		}

		// Let's read the manifest off the JPA archive
		$manifestRawArray = $archiver->extract($offset);
		$offset = $manifestRawArray['offset'];
		$parsedManifest = $this->_parse_ini_file($manifestRawArray['data'], true);
		$overwrite = true;

		// Make an image mapping array
		$images = array();
		foreach($parsedManifest['images'] as $hash => $filename)
		{
			$images[$hash] = $filename;
		}
		$IMSer = serialize($images);
		unset($images);

		// Make an article mapping array
		$articles = array();
		$aliases = array();
		$submenuMap = array();
		$model =& $this->getModel('Wizard');
		foreach($parsedManifest['articles'] as $hash => $filename)
		{
			$filename = JFile::stripExt($filename);
			$filename = str_replace('_','-',$filename);
			$createNewArticle = true;
			// If in overwrite mode, search for a relevant article
			if($overwrite)
			{
				$articleID = $model->getArticleByAlias($filename, $category);
				if(!is_numeric($articleID))
				{
					$createNewArticle = true;
				}
				else
				{
					$createNewArticle = false;
				}
			}

			if($createNewArticle)
			{
				// Create a new dummy (empty) article
				$articleID = $model->createDummyArticle($filename, $category, $section);
			}

			$articles[$hash] = $articleID;
			$aliases[$articleID] = $filename;

			$submenuMap[$hash] = $model->createMenuItem($category, $articleID, $filename);
		}

		$AMSer = serialize($articles);
		$ALSer = serialize($aliases);
		$SMSer = serialize($submenuMap);

		// Make the artile title map
		$titles = array();
		foreach($parsedManifest['titles'] as $filename => $title)
		{
			$filename = JFile::stripExt($filename);
			$filename = str_replace('_','-',$filename);
			$titles[$filename] = $title;
		}

		$TMSer = serialize($titles);

		// Make the article order map
		$order = array();
		foreach($parsedManifest['order'] as $filename => $myorder)
		{
			$filename = JFile::stripExt($filename);
			$filename = str_replace('_','-',$filename);
			$order[$filename] = $myorder;
		}
		$OMSer = serialize($order);

		// Create session variables
		$alias = $model->getCategoryAlias($category);
		$session =& JFactory::getSession();
		$session->set('category',		$category,	'docimport'); // Category where to store articles in
		$session->set('overwrite',		$overwrite,	'docimport'); // Are we overwriting articles?
		$session->set('offset',			$offset,	'docimport'); // Current archive offset
		$session->set('step',			'html',		'docimport'); // Current processing step
		$session->set('articleMap',		$AMSer,		'docimport'); // Article ID to Joomla! content ID mapping
		$session->set('aliasMap',		$ALSer,		'docimport'); // Joomla! content ID to alias (filename) mapping
		$session->set('titleMap',		$TMSer,		'docimport'); // Article ID to article title mapping
		$session->set('imageMap',		$IMSer,		'docimport'); // Image ID to relative URL mapping
		$session->set('orderMap',		$OMSer,		'docimport'); // ArticleID to article order mapping
		$session->set('submenuMap',		$SMSer,		'docimport'); // Submenu map
		$session->set('catAlias',		$alias,		'docimport'); // Category alias

		parent::display();
	}


	/**
	 * A PHP based INI file parser.
	 *
	 * Thanks to asohn ~at~ aircanopy ~dot~ net for posting this handy function on
	 * the parse_ini_file page on http://gr.php.net/parse_ini_file
	 *
	 * @param string $data Ini text to process
	 * @param bool $process_sections True to also process INI sections
	 * @return array An associative array of sections, keys and values
	 * @access private
	 */
	function _parse_ini_file($data, $process_sections = false)
	{
		$process_sections = ($process_sections !== true) ? false : true;

		$ini = explode("\n", $data);
		if (count($ini) == 0) {return array();}

		$sections = array();
		$values = array();
		$result = array();
		$globals = array();
		$i = 0;
		foreach ($ini as $line) {
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}

			// Sections
			if ($line{0} == '[') {
				$tmp = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			$value = trim($value);
			if (strstr($value, ";")) {
				$tmp = explode(';', $value);
				if (count($tmp) == 2) {
					if ((($value{0} != '"') && ($value{0} != "'")) ||
					preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
					preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
						$value = $tmp[0];
					}
				} else {
					if ($value{0} == '"') {
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					} elseif ($value{0} == "'") {
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					} else {
						$value = $tmp[0];
					}
				}
			}
			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0) {
				if (substr($line, -1, 2) == '[]') {
					$globals[$key][] = $value;
				} else {
					$globals[$key] = $value;
				}
			} else {
				if (substr($line, -1, 2) == '[]') {
					$values[$i-1][$key][] = $value;
				} else {
					$values[$i-1][$key] = $value;
				}
			}
		}

		for($j = 0; $j < $i; $j++) {
			if ($process_sections === true) {
				$result[$sections[$j]] = $values[$j];
			} else {
				$result[] = $values[$j];
			}
		}

		return $result + $globals;
	}
}
?>