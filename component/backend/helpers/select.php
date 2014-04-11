<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportHelperSelect
{
	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		if(empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			foreach($attribs as $key=>$value)
			{
				$temp .= $key.' = "'.$value.'"';
			}
			$attribs = $temp;
		}

		return JHTML::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	public static function booleanlist( $name, $attribs = null, $selected = null )
	{
		$options = array(
			JHTML::_('select.option','','---'),
			JHTML::_('select.option',  '0', JText::_( 'No' ) ),
			JHTML::_('select.option',  '1', JText::_( 'Yes' ) )
		);
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $id = 'enabled', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,'- '.JText::_('COM_DOCIMPORT_COMMON_SELECTSTATE').' -');
		$options[] = JHTML::_('select.option',0,JText::_((version_compare(JVERSION, '1.6.0', 'ge')?'J':'').'UNPUBLISHED'));
		$options[] = JHTML::_('select.option',1,JText::_((version_compare(JVERSION, '1.6.0', 'ge')?'J':'').'PUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function languages($selected = null, $id = 'language', $attribs = array() )
	{
		JLoader::import('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		$options = array();
		$options[] = JHTML::_('select.option','','- '.JText::_('COM_DOCIMPORT_COMMON_SELECTLANGUAGE').' -');
		$options[] = JHTML::_('select.option','*',JText::_('JALL_LANGUAGE'));
		if(!empty($languages)) foreach($languages as $key => $lang)
		{
			$options[] = JHTML::_('select.option',$key,$lang->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function categories($selected = null, $name = 'category', $attribs = array())
	{
		$model = F0FModel::getTmpInstance('Categories','DocimportModel');
		$items = $model->limit(0)->limitstart(0)->getItemList();

		$options = array();

		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->docimport_category_id, $item->title);
		}

		array_unshift($options, JHTML::_('select.option',0,'- '.JText::_('COM_DOCIMPORT_CATEGORY').' -'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

}