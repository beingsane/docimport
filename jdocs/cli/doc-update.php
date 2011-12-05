<?php

// Define ourselves as a parent file
define( '_JEXEC', 1 );
// Required by the CMS
define('DS', DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(__FILE__).'/defines.php')) {
        dirname(__FILE__).'/defines.php';
}

if (!defined('_JDEFINES')) {
        define('JPATH_BASE', dirname(__FILE__).'/../');
        require_once JPATH_BASE.'/includes/defines.php';
}

// Load the rest of the necessary files
include_once JPATH_LIBRARIES.'/import.php';
require_once JPATH_BASE.'/includes/version.php';

jimport( 'joomla.application.cli' );
 
class AppDocupdate extends JCli
{
	private $_folderMap = array(
		'docs/manual/en-US'				=> 'manual',
		'docs/coding-standards/en-US'	=> 'coding-standards'
	);
	
	private $_media = '';
	
	/**
	 * The main entry point of the application
	 */
	public function execute()
	{
		// Set all errors to output the messages to the console, in order to
		// avoid infinite loops in JError ;)
		restore_error_handler();
		JError::setErrorHandling(E_ERROR, 'die');
		JError::setErrorHandling(E_WARNING, 'echo');
		JError::setErrorHandling(E_NOTICE, 'echo');
		
		// Set up the path of our media directory
		$this->_media = JPATH_ROOT.'/media/com_docimport/';
		
		$this->_updatePlatformSnapshot();

		$this->_copyDocFiles();

		$this->_processFiles();
    }
	
	private function _updatePlatformSnapshot()
	{
		jimport('joomla.filesystem.folder');
		
		$folder = $this->_media.'.jplatform';
		if(!JFolder::exists($folder)) {
			$this->out('Fetching a new copy of the Joomla! Platform repo (it will take a while)...');
			JFolder::create($folder);
			$cmd = 'git clone "https://github.com/joomla/joomla-platform.git" '.
					$folder;
			exec($cmd);
		} else {
			$this->out('Updating the Joomla! Platform repo copy');
			$cwd = getcwd();
			chdir($folder);
			$cmd = 'git pull';
			exec($cmd);
			chdir($cwd);
		}
	}
	
	private function _copyDocFiles()
	{
		jimport('joomla.filesystem.folder');
		
		foreach($this->_folderMap as $sourceFolder => $targetFolder)
		{
			$this->out("Copying $targetFolder files...");
			$sourceFolder = $this->_media.'.jplatform/'.$sourceFolder;
			$targetFolder = $this->_media.$targetFolder;
			
			if(JFolder::exists($targetFolder)) JFolder::delete($targetFolder);
			JFolder::copy($sourceFolder, $targetFolder);
		}
	}
	
	private function _processFiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		require_once JPATH_ADMINISTRATOR.'/components/com_docimport/fof/include.php';
		
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
	}
}
 
JCli::getInstance( 'AppDocupdate' )->execute( );
