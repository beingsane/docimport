<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerArticles extends F0FController
{
	public function onBeforeRead()
	{
        $model = $this->getThisModel();

        if (!$model->getId())
        {
            $model->setIDsFromRequest();
        }

        $item = $model->getItem();

		$catModel = F0FModel::getAnInstance('Categories', 'DocimportModel');
        $cat = $catModel->getItem($item->docimport_category_id);

        $user = JFactory::getUser();
        $views = $user->getAuthorisedViewLevels();

        // Is the category enabled?
        if(!$cat->enabled)
        {
            return false;
        }

        return in_array($cat->access, $views);
	}
}