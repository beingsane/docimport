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

class DocimportControllerProcessing extends JController
{

	var $articleMap;

	var $titleMap;

	var $imageMap;

	var $orderMap;

	var $submenuMap;

	function display()
	{
		// Get the stored parameters
		$session =& JFactory::getSession();
		$step		= $session->get('step',			null,	'docimport'); // Selected step
		$category	= $session->get('category',		null,	'docimport'); // Category where to store articles in
		$offset		= $session->get('offset',		null,	'docimport'); // Current archive offset
		$articleMap	= $session->get('articleMap',	null,	'docimport'); // Article ID to Joomla! content ID mapping
		$titleMap	= $session->get('titleMap',		null,	'docimport'); // Article ID to article title mapping
		$imageMap	= $session->get('imageMap',		null,	'docimport'); // Image ID to relative URL mapping
		$orderMap	= $session->get('orderMap',		null,	'docimport'); // ArticleID to article order mapping
		$submenuMap = $session->get('submenuMap',	null,	'docimport'); // ArticleID to submenu ItemID

		$this->articleMap = unserialize($articleMap);
		$this->titleMap = unserialize($titleMap);
		$this->imageMap = unserialize($imageMap);
		$this->orderMap = unserialize($orderMap);
		$this->submenuMap = unserialize($submenuMap);

		// Open archive
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'unjpa.php';
		$jreg =& JFactory::getConfig();
		$tempdir = $jreg->getValue('config.tmp_path');
		$targetLocation = $tempdir.DS.'docpackage.jpa';
		$archiver =& DocimportUnjpa::getInstance($targetLocation);

		$timestart = microtime(true);

		while( $timestart - microtime(true) < 5 )
		{
			// Get the next file to process
			$fileArray = $archiver->extract($offset);
			$offset = $fileArray['offset'];

			// Are we done yet?
			if(($fileArray === false) || $fileArray['done'])
			{
				$this->setRedirect('index.php?option=com_docimport&view=processing&task=done');
				return;
			}

			// What kind of file is it?
			if(substr($fileArray['filename'], 0, 4) == 'file')
			{
				// HTML file detected
				$this->_processHTML($fileArray, $category);
			}
			else
			{
				// Image detected
				$imageFolder = JPATH_ROOT.DS.'images'.DS.'stories'.DS.'docimport'.$category;

				if($step == 'html')
				{
					// We were processing HTML files till now. Is the image folder ready?
					$step = 'images';
					jimport('joomla.filesystem.folder');
					if(!JFolder::exists($imageFolder))
					{
						// No, create it
						JFolder::create($imageFolder);
					}
				}

				// Now, process the image file
				$this->_uploadImage($category, $fileArray);
			}

			// Return control
			$session->set('offset',			$offset,	'docimport');
			$session->set('step',			$step,		'docimport');
			JRequest::setVar('filename', $fileArray['filename']);
		}
		parent::display();
	}

	function done()
	{
		// Remove archive
		$jreg =& JFactory::getConfig();
		$tempdir = $jreg->getValue('config.tmp_path');
		$targetLocation = $tempdir.DS.'docpackage.jpa';
		//$targetLocation = JPATH_COMPONENT_ADMINISTRATOR.DS.'storage'.DS.'docpackage.jpa';
		jimport('joomla.filesystem.file');
		@JFile::delete($targetLocation) or @unlink($targetLocation);

		$this->setRedirect('index.php?option=com_docimport','Import finished');
	}

	function _processHTML( &$dataArray, $category )
	{
		require_once JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php';

		$session =& JFactory::getSession();
		$aliasMap = unserialize($session->get('aliasMap',null,'docimport')); // Alias map
		$ItemId = $session->get('ItemId', null, 'docimport');

		// Replace links to HTML files
		foreach($this->articleMap as $hash => $id)
		{
			$needle = '{{'.$hash.'}}';
			//$replace = K2HelperRoute::getItemRoute($id, $category);
			$replace = 'index.php?option=com_k2&view=item&id='.$id;
			// Do we have a submenu?
			$articleItemID = $this->submenuMap[$hash];
			if(!empty($articleItemID))
			{
				$replace = 'index.php?Itemid='.$articleItemID;
			}
			else
			{
				if(!empty($ItemId)) $replace .= '&Itemid='.$ItemId;
			}

			$dataArray['data'] = str_replace($needle, $replace, $dataArray['data']);
		}

		// Replace links to image files
		foreach($this->imageMap as $hash => $realfile)
		{
			$needle = '{{'.$hash.'}}';
			$location = $this->_getImageURL($category, $realfile);
			$dataArray['data'] = str_replace($needle, $location, $dataArray['data']);
		}

		// Store to database
		$model =& $this->getModel('wizard');
		$hash = $dataArray['filename'];
		jimport('joomla.filesystem.file');
		$hash = JFile::stripExt($hash);
		$myID = $this->articleMap[$hash];
		$model->saveArticle($myID, $dataArray['data'], $this->titleMap, $this->orderMap);
	}

	function _processImage( &$dataArray, $imagepath )
	{
		jimport('joomla.filesystem.file');

		$hash = $dataArray['filename'];
		$hash = JFile::stripExt($hash);
		$filename = $imagepath.DS.$this->imageMap[$hash];

		// Write the actual file data
		JFile::write($filename, $dataArray['data']);
		// Try hard to make it world-readable
		@chmod($filename, 0755);
	}

	function _getImageURL( $category, $realfile )
	{
		$component =& JComponentHelper::getComponent( 'com_docimport' );
		$params = new JParameter($component->params);

		$uses3 = $params->get('uses3', 0);
		$cdnurl = $params->get('cdnurl','');
		$s3basedir = $params->get('s3basedir','');
		$cdnbase = rtrim($cdnurl,'/').'/'.$s3basedir.'/docimport'.$category.'/';

		if($uses3)
		{
			return $cdnbase.$realfile;
		}
		else
		{
			return '/images/stories/docimport'.$category.'/'.$realfile;
		}
	}

	function _uploadImage( $category, $dataArray )
	{
		jimport('joomla.filesystem.file');

		$component =& JComponentHelper::getComponent( 'com_docimport' );
		$params = new JParameter($component->params);

		$uses3 = $params->get('uses3', 0);
		if($uses3)
		{
			// Get some variables
			$bucket = $params->get('s3bucket','');
			$basedir = $params->get('s3basedir','');

			// Load the S3 support classes
			require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'object.php';
			require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'amazons3.php';

			// Init S3
			$accessKey = $params->get('s3accesskey','');
			$secretKey = $params->get('s3secretkey','');
			AEUtilAmazons3::setAuth($accessKey, $secretKey);

			// Save image data to a temporary file
			$jreg =& JFactory::getConfig();
			$tmpdir = $jreg->getValue('config.tmp_path');
			$tempname = tempnam($tmpdir, 'dii');
			JFile::write($tempname, $dataArray['data']);

			// Create an Amazon S3 upload object
			$input = AEUtilAmazons3::inputFile($tempname);

			// Get the real filename
			$hash = $dataArray['filename'];
			$hash = JFile::stripExt($hash);
			$filename = $this->imageMap[$hash];

			// Upload
			$uri = trim($basedir,'/').'/docimport'.$category.'/'.$filename;
			$status = AEUtilAmazons3::putObject($input, $bucket, $uri, AEUtilAmazons3::ACL_PUBLIC_READ);

			// Remove temp file
			@JFile::delete($tempname);

			if(!$status) {
				JError::raiseWarning('603','Could not upload image '.$filename.' to S3');
			}
		}
		else
		{
			$imageFolder = JPATH_ROOT.DS.'images'.DS.'stories'.DS.'docimport'.$category;
			$this->_processImage($dataArray, $imageFolder);
		}
	}

}

class DocimportRouterHelper
{
	static function getAndPop(&$query, $key, $default = null)
	{
		if(isset($query[$key]))
		{
			$value = $query[$key];
			unset($query[$key]);
			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Finds a menu whose query parameters match those in $qoptions
	 * @param array $qoptions The query parameters to look for
	 * @param array $params The menu parameters to look for
	 * @return null|object Null if not found, or the menu item if we did find it
	 */
	static public function findMenu($qoptions = array(), $params = null)
	{
		// Convert $qoptions to an object
		if(empty($qoptions) || !is_array($qoptions)) $qoptions = array();

		$menus =& JMenu::getInstance('site');
		$menuitem =& $menus->getActive();

		// First check the current menu item (fastest shortcut!)
		if(is_object($menuitem)) {
			if(self::checkMenu($menuitem, $qoptions, $params)) {
				return $menuitem;
			}
		}

		foreach($menus->getMenu() as $item)
		{
			if($item->published)
			{
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			}
		}

		return null;
	}

	/**
	 * Checks if a menu item conforms to the query options and parameters specified
	 *
	 * @param object $menu A menu item
	 * @param array $qoptions The query options to look for
	 * @param array $params The menu parameters to look for
	 * @return bool
	 */
	static public function checkMenu($menu, $qoptions, $params = null)
	{
		$query = $menu->query;
		foreach($qoptions as $key => $value)
		{
			if(is_null($value)) continue;
			if(!isset($query[$key])) return false;
			if($query[$key] != $value) return false;
		}

		if(!is_null($params))
		{
			$menus =& JMenu::getInstance('site');
			$check =  $menu->params instanceof JParameter ? $menu->params : $menus->getParams($menu->id);

			foreach($params as $key => $value)
			{
				if(is_null($value)) continue;
				if( $check->get($key) != $value ) return false;
			}
		}

		return true;
	}

	static public function preconditionSegments($segments)
	{
		$newSegments = array();
		if(!empty($segments)) foreach($segments as $segment)
		{
			if(strstr($segment,':'))
			{
				$segment = str_replace(':','-',$segment);
			}
			if(is_array($segment)) {
				$newSegments[] = implode('-', $segment);
			} else {
				$newSegments[] = $segment;
			}
		}
		return $newSegments;
	}
}