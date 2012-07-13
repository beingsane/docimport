<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportModelXsl extends FOFModel
{
	/**
	 * Runs the XML to HTML file conversion step for a given category
	 * 
	 * @param int $category_id
	 * @return bool
	 */
	public function processXML($category_id)
	{
		// Get the category record
		$category = FOFModel::getTmpInstance('Categories','DocimportModel')
			->setId($category_id)
			->getItem();
		
		if($category->docimport_category_id != $category_id) {
			$this->setError(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOCATEGORY', $category_id));
			return false;
		}
		
		// Check if directories exist
		$dir_src	= JPATH_ROOT.'/media/com_docimport/'.$category->slug;
		$dir_output	= JPATH_ROOT.'/media/com_docimport/'.$category->slug.'/output';
		
		jimport('joomla.filesystem.folder');
		
		if(!JFolder::exists($dir_src)) {
			$this->setError(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug));
			return false;
		}
		
		if(!JFolder::exists($dir_output)) {
			$result = JFolder::create($dir_output);
			if(!$result) {
				$this->setError(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_CANTCREATEFOLDER', $category->slug.'/output'));
				return false;
			} else {
				jimport('joomla.filesystem.file');
				$content = "order deny, allow\ndeny from all\allow from none\n";
				JFile::write($dir_output.'/.htaccess', $content);
			}
		}
		
		// Find the XML file
		$xmlfiles = JFolder::files($dir_src, '\.xml$', false, true);
		
		// If we have many files, let's filter out only articles and books
		if(count($xmlfiles) > 1) {
			$files = $xmlfiles;
			$xmlfiles = array();
			foreach($files as $file_xml) {
				$xmlDoc = new DOMDocument();
				if(!$xmlDoc->load($file_xml)) continue;

				$tagName = $xmlDoc->documentElement->tagName;
				if(in_array($tagName,array('article','book'))) {
					$xmlfiles[] = $file_xml;
				}
				unset($xmlDoc);
			}
		}

		$xslt_filename = (count($xmlfiles) > 1) ? 'onechunk.xsl' : 'chunk.xsl';
		
		if( ($xmlfiles === false) || (empty($xmlfiles)) ) {
			$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_NOXMLFILES'));
			return false;
		}
		
		$file_xsl = JPATH_ADMINISTRATOR.'/components/com_docimport/assets/dbxsl/xhtml/'.$xslt_filename;
		
		// Load the XSLT filters
		$xslDoc = new DOMDocument();
		if(!$xslDoc->load($file_xsl)) {
			$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXSL'));
			return false;
		}
		
		$timestamp = 0;
		foreach($xmlfiles as $file_xml) {
			// Load the XML document
			$xmlDoc = new DOMDocument();
			if(!$xmlDoc->load($file_xml, LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_NONET | LIBXML_XINCLUDE )) {
				$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXML'));
				return false;
			}
			$doc->documentURI = $file_xml;
			$xmlDoc->xinclude(LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_NONET | LIBXML_XINCLUDE);
			
			$filesprefix = '';
			if(count($xmlfiles) > 1) {
				$filesprefix = basename($file_xml,'.xml');
			}

			// Setup the XSLT processor
			$parameters = array(
				'base.dir'				=> rtrim($dir_output,'/').'/'.(empty($filesprefix) ? '' : $filesprefix.'-'),
				'img.src.path'			=> "/media/com_docimport/{$category->slug}/",
				'admon.graphics.path'	=> 'media/com_docimport/admonition/',
				'admon.graphics'		=> 1,
				'use.id.as.filename'    => 1,
				'toc.section.depth'		=> 5,
				'chunk.section.depth'	=> 3
			);
			$xslt = new XSLTProcessor();
			$xslt->importStylesheet($xslDoc);
			if(!$xslt->setParameter('', $parameters)) {
				$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADPARAMETERS'));
				return false;
			}

			// Process it!
			set_time_limit(0);
			$errorsetting = error_reporting(0);
			if (version_compare(PHP_VERSION,'5.4',"<")) {
				$oldval = ini_set("xsl.security_prefs",XSL_SECPREFS_NONE);
			} else {
				$oldval = $xslt->setSecurityPreferences(XSL_SECPREFS_NONE);
			}
			
			$result = $xslt->transformToXml($xmlDoc);
			
			error_reporting($errorsetting);
			if (version_compare(PHP_VERSION,'5.4',"<")) {
				ini_set("xsl.security_prefs",$oldval);
			} else {
				$xslt->setSecurityPreferences($oldval);
			}
			unset($xslt);
			
			if($result === false) {
				$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_FAILEDTOPROCESS'));
			} else {
				$timestamp_local = @filemtime($file_xml);
				if($timestamp_local > $timestamp) $timestamp = $timestamp_local;
				
				if(!empty($filesprefix)) {
					$fname = rtrim($dir_output,'/')."/$filesprefix-index.html";
					$renamed = rtrim($dir_output,'/')."/$filesprefix.html";
					if(@file_exists($fname)) {
						JFile::move($fname, $renamed);
					}
				}
			}
		}
		
		// Update the database record with the file's/files' timestamp
		$category->save(array(
			'last_timestamp'	=> $timestamp
		));
		
		if($this->getError()) return false;
		
		return true;
	}
	
	/**
	 * Scans the output directory of a category for new HTML files and updates
	 * the database.
	 * 
	 * @param int $category_id 
	 */
	public function processFiles($category_id)
	{
		// Get the category record
		$category = FOFModel::getTmpInstance('Categories','DocimportModel')
			->setId($category_id)
			->getItem();
		
		if($category->docimport_category_id != $category_id) {
			$this->setError(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOCATEGORY', $category_id));
			return false;
		}
		
		// Check if directories exist
		$dir_src	= JPATH_ROOT.'/media/com_docimport/'.$category->slug;
		$dir_output	= JPATH_ROOT.'/media/com_docimport/'.$category->slug.'/output';
		
		jimport('joomla.filesystem.folder');
		
		if(!JFolder::exists($dir_src)) {
			$this->setError(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug));
			return false;
		}
		
		if(!JFolder::exists($dir_output)) {
			$this->setError(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug.'/output'));
			return false;
		}
		
		// Load the list of articles in this category
		$db = $this->getDBO();
		$query = FOFQueryAbstract::getNew()
			->from($db->quoteName('#__docimport_articles'))
			->select(array(
				$db->quoteName('docimport_article_id').' AS '.$db->quoteName('id'),
				$db->quoteName('slug'),
				$db->quoteName('last_timestamp'),
				$db->quoteName('enabled')
			))
			->where($db->quoteName('docimport_category_id').' = '.$db->quote($category_id))
		;
		$db->setQuery($query);
		$articles = $db->loadObjectList('slug');
		if(empty($articles)) $articles=array();
		
		// Get a list of existing files
		$files = JFolder::files($dir_output, '\.html$');
		
		// And now, turn the files into a list of slugs
		$slugs = array();
		if(!empty($files)) foreach($files as $filename) {
			$slugs[] = basename($filename,'.html');
		}

		// First pass: find articles pointing to files no longer existing
		if(!empty($articles)) foreach($articles as $slug => $article) {
			if(!in_array($slug, $slugs)) {
				FOFModel::getTmpInstance('Article','DocimportModel')
					->setId($article->id)
					->save(array(
						'enabled'	=> 0
					));
			}
		}
		
		// Second pass: add articles which are not already there
		if(!empty($slugs)) foreach($slugs as $slug) {
			if(!array_key_exists($slug, $articles)) {
				jimport('joomla.utilities.date');
				
				$jNow = new JDate();
				
				$user_id = JFactory::getUser()->id;
				if(empty($user_id)) $user_id = 42;
				
				$filepath = $dir_output.'/'.$slug.'.html';
				$filedata = $this->_getHTMLFileData($filepath);
				
				FOFModel::getTmpInstance('Articles','DocimportModel')
					->getTable()
					->save(array(
						'docimport_article_id'	=> 0,
						'docimport_category_id'	=> $category_id,
						'title'					=> $filedata->title,
						'slug'					=> $slug,
						'fulltext'				=> $filedata->contents,
						'last_timestamp'		=> $filedata->timestamp,
						'enabled'				=> 1,
						'created_on'			=> $jNow->toMySQL(),
						'created_by'			=> $user_id
						
					));
			}
		}
		
		// Third pass: update existing articles
		if(!empty($slugs) && !empty($articles)) foreach($articles as $article) {
			if(in_array($article->slug, $slugs)) {
				// Do we have to update?
				$filepath = $dir_output.'/'.$article->slug.'.html';
				if(@filemtime($filepath) == $article->last_timestamp) continue;

				jimport('joomla.utilities.date');
				
				$jNow = new JDate();
				
				$user_id = JFactory::getUser()->id;
				if(empty($user_id)) $user_id = 42;
				
				$filedata = $this->_getHTMLFileData($filepath);
				
				FOFModel::getTmpInstance('Articles','DocimportModel')
					->setId($article->id)
					->getItem()
					->save(array(
						'title'					=> $filedata->title,
						'fulltext'				=> $filedata->contents,
						'last_timestamp'		=> $filedata->timestamp,
						'enabled'				=> 1,
						'locked_on'				=> '0000-00-00 00:00:00',
						'locked_by'				=> 0,
						'modified_on'			=> $jNow->toMySQL(),
						'modified_by'			=> $user_id
					));
			}
		}
		
		// Fourth pass: Load a list of enabled articles (IDs and slugs)
		$db = $this->getDBO();
		$query = FOFQueryAbstract::getNew()
			->from($db->quoteName('#__docimport_articles'))
			->select(array(
				$db->quoteName('docimport_article_id').' AS '.$db->quoteName('id'),
				$db->quoteName('slug')
			))
			->where($db->quoteName('docimport_category_id').' = '.$db->quote($category_id))
			->where($db->quoteName('enabled').' = '.$db->quote(1))
		;
		$db->setQuery($query);
		$rawlist = $db->loadObjectList();
		$mapSlugID = array();
		if(!empty($rawlist)) foreach($rawlist as $rawItem) {
			$mapSlugID[$rawItem->slug] = $rawItem->id;
		}
		unset($rawlist);
		
		// Fifth pass: Load the index page and determine ordering of slugs
		$mapSlugOrder = array();
		$maxOrder = 0;
		if(JFile::exists($dir_output.'/index.html')) {
			$file_data = JFile::read($dir_output.'/index.html');
			$domdoc = new DOMDocument();
			$success = $domdoc->loadXML($file_data);
			unset($file_data);

			if($success) {
				// Get a list of anchor elements (<a href="...">)
				$anchors = $domdoc->getElementsByTagName('a');
				if(!empty($anchors)) foreach($anchors as $anchor) {
					// Grab the href
					$href = $anchor->getAttribute('href');
					// Kill any page anchors from the URL, e.g. #some-anchor
					$hashlocation = strpos($href, '#');
					if($hashlocation !== false)
					{
						$href = substr($href, 0, $hashlocation);
					}
					// Only precess if this page is not already found
					$slug = basename($href,'.html');
					if(!array_key_exists($slug, $mapSlugID)) continue;
					if(array_key_exists($slug, $mapSlugOrder)) continue;
					
					$mapSlugOrder[$slug] = ++$maxOrder;
				}
			}
		}
		
		// Sixth pass: Load each article, replace links and modify ordering
		$allIds = array_values($mapSlugID);
		if(!empty($allIds)) foreach($allIds as $id) {
			// Load the article
			$article = FOFModel::getTmpInstance('Articles','DocimportModel')
				->setId($id)
				->getItem();
			// Replace links
			$fulltext = $article->fulltext;
			foreach($mapSlugID as $slug => $id) {
				if($slug == 'index') {
					$url = 'index.php?option=com_docimport&view=category&id='.$category_id;
				} else {
					$url = 'index.php?option=com_docimport&view=article&id='.$id;
				}
				$fulltext = str_replace('href="'.$slug.'.html', 'href="'.$url.'', $fulltext);
			}
			// Replace ordering
			$ordering = $article->ordering;
			if(array_key_exists($article->slug, $mapSlugOrder)) $ordering = $mapSlugOrder[$article->slug];
			// Apply changes
			$article->save(array(
				'fulltext'		=> $fulltext,
				'ordering'		=> $ordering
			));
			unset($fulltext);
			unset($article);
		}
		
		return true;
	}
	
	/**
	 * Scans for the existence of new categories
	 */
	public function scanCategories()
	{
		// Load a list of categories
		$db = $this->getDBO();
		$query = FOFQueryMysql::getNew($db)
			->from($db->quoteName('#__docimport_categories'))
			->select(array(
				$db->quoteName('docimport_category_id').' AS '.$db->quoteName('id'),
				$db->quoteName('slug')
			))
		;
		$db->setQuery($query);
		$categories = $db->loadObjectList('slug');
		
		// Get a list of subdirectories, except the built-in ones
		jimport('joomla.filesystem.folder');
		$path = JPATH_ROOT.'/media/com_docimport';
		$folders = JFolder::folders($path, '.', false, false, array('admonition','css','js','images'));
		
		// If a subdirectory doesn't exist, create a new category
		if(!empty($folders)) foreach($folders as $folder) {
			if(!array_key_exists($folder, $categories)) {
				FOFModel::getTmpInstance('Categories','DocimportModel')
					->save(array(
						'title'			=> JText::sprintf('COM_DOCIMPORT_XSL_DEFAULT_TITLE', $folder),
						'slug'			=> $folder,
						'description'	=> JText::_('COM_DOCIMPORT_XSL_DEFAULT_DESCRIPTION'),
						'ordering'		=> 0
					));
			}
		}
	}
	
	/**
	 * Parse an HTML output file of DocBook XSLT transformation
	 * 
	 * @param string $filepath The full path to the file
	 * @return object
	 */
	private function _getHTMLFileData($filepath)
	{
		$ret = (object)array(
			'title'		=> '',
			'contents'	=> '',
			'timestamp'	=> 0
		);
		
		$ret->timestamp = @filemtime($filepath);
		
		jimport('joomla.filesystem.file');
		$filedata = JFile::read($filepath);
		
		$startOfTitle = strpos($filedata, '<title>') + 7;
		$endOfTitle = strpos($filedata, '</title>');
		$ret->title = substr($filedata, $startOfTitle, $endOfTitle - $startOfTitle);
		
		// Extract the body
		$startOfContent = strpos($filedata, '<body>') + 6;
		$endOfContent = strpos($filedata, '</body>');
		$ret->contents = substr($filedata, $startOfContent, $endOfContent - $startOfContent);
		
		return $ret;
	}
}