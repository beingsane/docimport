<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

class DocimportHelperFormat
{
	public static function language($lang = '*')
	{
		jimport('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		if($lang == '*') {
			return JText::_('JALL_LANGUAGE');
		} elseif(array_key_exists($lang, $languages)) {
			return $languages[$lang]->title;
		} else {
			return $lang;
		}
	}
}
