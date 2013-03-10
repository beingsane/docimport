<?php
/**
 * @package docimport
 * @copyright Copyright (c)2011-2012 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class DocimportModelCategories extends FOFModel
{
   /**
    * This trick allows us to proxy the back-end CategoriesModelCategories
    * model through a FOFModel instance
    *
    * @param type $overrideLimits
    * @return type
    */
   function buildQuery($overrideLimits = false) {
      // Create a new query object.
      $db      = $this->getDbo();
      $query   = $db->getQuery(true);
      $userid = $this->getState('userid', 0, 'int');
      if(!$userid) {
         $user = JFactory::getUser();
      } else {
         $user = JFactory::getUser($userid);
      }

      // Select the required fields from the table.
      $query->select(
         'a.*'
      );
      $query->from('#__docimport_categories AS a');

      // Join over the language
      $query->select('l.title AS language_title');
      $query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

      // Join over the asset groups.
      $query->select('ag.title AS access_level');
      $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

      // Filter by access level.
      if ($access = $this->getState('access', null)) {
         $query->where('a.access = ' . (int) $access);
      }

      // Implement View Level Access
      if (!$user->authorise('core.admin'))
      {
         $groups  = implode(',', $user->getAuthorisedViewLevels());
         $query->where('a.access IN ('.$groups.')');
      }

      // Using enabled flag
      $query->where('a.enabled = 1');

      // Add the list ordering clause
      $listOrdering = $this->getState('ordering', 'a.ordering');
      $listDirn = $db->escape($this->getState('direction', 'ASC'));
      if ($listOrdering == 'a.access') {
         $query->order('a.access '.$listDirn.', a.ordering '.$listDirn);
      } else {
         $query->order($db->escape($listOrdering).' '.$listDirn);
      }

      // Language filtering
      list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
      if ($language = $this->getState('language')) {
         $query->where("a.language IN ('".implode("','",$language)."') ");
      } elseif(!$isCli) {
         if(JFactory::getApplication()->getLanguageFilter()) {
            $lang_filter_plugin = JPluginHelper::getPlugin('system', 'languagefilter');
            $lang_filter_params = new JRegistry($lang_filter_plugin->params);
            if ($lang_filter_params->get('remove_default_prefix')) {
               // Get default site language
               $lg = JFactory::getLanguage();
               $query->where('a.language IN ('.$db->quote($lg->getTag()).', '.$db->quote('*').')');
            }else{
               $query->where('a.language IN ('.$this->input->getCmd('language', '*').', '.$db->quote('*').')');
            }
            /*
            $lang = JFactory::getLanguage()->getTag();
            if(!empty($lang)) {
               $query->where('a.language IN ('.$db->quote($lang).', '.$db->quote('*').')');
            }
            */
         }
      }

      // Hack to show all categories
      $this->setState('limit',0);
      $this->setState('limitstart',0);

      return $query;
   }
}