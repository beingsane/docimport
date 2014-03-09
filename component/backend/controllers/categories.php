<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerCategories extends FOFController
{
	public function rebuild($caching = false) {
		$id = $this->input->getInt('id',0);
		$model = FOFModel::getTmpInstance('Xsl','DocimportModel');
		$status = $model->processXML($id);
		if($status) $status = $model->processFiles($id);

		$url = 'index.php?option=com_docimport&view=categories';
		if(!$status) {
			$this->setRedirect($url,$model->getError(),'error');
		} else {
			$this->setRedirect($url,JText::_('COM_DOCIMPORT_CATEGORIES_REBUILT'));
		}
	}
}