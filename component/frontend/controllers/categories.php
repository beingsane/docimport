<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerCategories extends F0FController
{
	public function onBeforeBrowse()
	{
		$enableTranslation = JFactory::getApplication()->getLanguageFilter();

		if($enableTranslation) {
			$lang = JFactory::getLanguage()->getTag();
		} else {
			$user = JFactory::getUser();
			if(property_exists($user, 'language')) {
				$lang = $user->language;
			} else {
				$params = $user->params;
				if(!is_object($params)) {
					$originalParams = $params;
					$params = new JRegistry;
					$params->loadString($originalParams, 'JSON');
				}
				$lang = $params->get('language','');
			}
			if(empty($lang)) {
				$lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			}
		}

		$this->getThisModel()
			->language(array('*',$lang))
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->filter_order('ordering')
			->filter_order_Dir('ASC');

		return true;
	}

	public function onBeforeRead()
	{
		$id = $this->input->getInt('id',0);
		if($id === 0) {
			$menu = JMenu::getInstance('site')->getActive();
			$menuparams = $menu->params;
			if(!($menuparams instanceof JRegistry)) {
				$x = new JRegistry();
				$x->loadString($menuparams, 'INI');
				$menuparams = $x;
			}
			$id = $menuparams->get('catid', 0);
			$this->input->set('id', $id);
		}

        if($id)
        {
            $cat = $this->getThisModel()->getItem($id);
            
            if(!$cat->enabled)
            {
                return false;
            }
        }

		return true;
	}
}