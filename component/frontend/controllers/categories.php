<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
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
					$params = new JParameter($params);
				}
				$lang = $params->getValue('language','');
			}
			if(empty($lang)) {
				$lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			}
		}
		
		$this->getThisModel()
			->language(array('*',$lang))
			->limit(0)
			->limitstart(0);
		
		return true;
	}
}