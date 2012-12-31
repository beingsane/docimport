<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class DocimportControllerCategories extends FOFController
{
	public function onBeforeBrowse()
	{
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$enableTranslation = JFactory::getApplication()->getLanguageFilter();
		} else {
			$enableTranslation = false;
		}
		
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
					if(version_compare(JVERSION, '3.0', 'ge')) {
						$params->loadString($originalParams, 'JSON');
					} else {
						$params->loadJSON($originalParams);
					}
				}
				if(version_compare(JVERSION, '3.0', 'ge')) {
					$lang = $params->get('language','');
				} else {
					$lang = $params->getValue('language','');
				}
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
		$id = FOFInput::getInt('id',0,$this->input);
		if($id === 0) {
			$menu = JMenu::getInstance('site')->getActive();
			$menuparams = $menu->params;
			if(!($menuparams instanceof JRegistry)) {
				$x = new JRegistry();
				if(version_compare(JVERSION, '3.0', 'ge')) {
					$x->loadString($menuparams, 'INI');
				} else {
					$x->loadINI($menuparams);
				}
				$menuparams = $x;
			}
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$id = $menuparams->get('catid', 0);
			} else {
				$id = $menuparams->getValue('catid', 0);
			}
			FOFInput::setVar('id', $id, $this->input);
		}
		
		return true;
	}
}