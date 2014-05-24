<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportHelperFormat
{
	public static function language($lang = '*')
	{
		JLoader::import('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');

		if ($lang == '*')
		{
			return JText::_('JALL_LANGUAGE');
		}
		elseif (array_key_exists($lang, $languages))
		{
			return $languages[$lang]->title;
		}
		else
		{
			return $lang;
		}
	}
}
