<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
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
			}
		}
		
		// Find the XML file
		$xmlfiles = JFolder::files($dir_src, '\.xml$', false, true);
		
		if( ($xmlfiles === false) || (empty($xmlfiles)) ) {
			$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_NOXMLFILES'));
			return false;
		}
		
		$file_xsl = JPATH_ADMINISTRATOR.'/components/com_docimport/assets/dbxsl/xhtml/chunk.xsl';
		
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
			if(!$xmlDoc->load($file_xml)) {
				$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXML'));
				return false;
			}

			// Setup the XSLT processor
			$parameters = array(
				'base.dir'				=> rtrim($dir_output,'/').'/',
				'img.src.path'			=> "/media/com_docimport/{$category->slug}/",
				'admon.graphics.path'	=> '/media/com_docimport/admonition',
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
			$result = $xslt->transformToXml($xmlDoc);
			error_reporting($errorsetting);

			unset($xslt);
			
			if($result === false) {
				$this->setError(JText::_('COM_DOCIMPORT_XSL_ERROR_FAILEDTOPROCESS'));
			} else {
				$timestamp_local = @filemtime($file_xml);
				if($timestamp_local > $timestamp) $timestamp = $timestamp_local;
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
		$query = FOFQueryMysql::getNew($db)
			->from($db->nameQuote('#__docimport_articles'))
			->select(array(
				$db->nameQuote('docimport_article_id').' AS '.$db->nameQuote('id'),
				$db->nameQuote('slug'),
				$db->nameQuote('last_timestamp'),
				$db->nameQuote('enabled')
			))
			->where($db->nameQuote('docimport_category_id').' = '.$db->quote($category_id))
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
		// Second pass: add articles which are not already there
		if(!empty($slugs) && !empty($articles)) foreach($articles as $article) {
			if(in_array($article->slug, $slugs)) {
				// Do we have to update?
				$filepath = $dir_output.'/'.$slug.'.html';
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
						'slug'					=> $slug,
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
			->from($db->nameQuote('#__docimport_categories'))
			->select(array(
				$db->nameQuote('docimport_category_id').' AS '.$db->nameQuote('id'),
				$db->nameQuote('slug')
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