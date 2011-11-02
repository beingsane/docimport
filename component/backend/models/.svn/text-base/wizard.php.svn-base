<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2008 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.model');

class DocimportModelWizard extends JModel
{

	function getCategoryAlias($catID)
	{
		$db =& JFactory::getDBO();
		$sql = 'SELECT '.$db->nameQuote('alias').' FROM #__k2_categories WHERE '.$db->nameQuote('id').
				'='.$db->Quote($catID).' LIMIT 0,1';
		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Returns the article id with an alias equal to $filename in the specified
	 * category, or null if it's not found
	 *
	 * @param string $filename The alias to look for
	 * @param int $categoryID Category to look into
	 * @return int|null
	 */
	function getArticleByAlias($filename, $categoryID)
	{
		$db =& JFactory::getDBO();

		// Clean up filename
		$filename = str_replace('_','-',$filename);

		$query = 'SELECT '.$db->nameQuote('id').' FROM #__k2_items WHERE '.
		$db->nameQuote('alias').' = '.$db->Quote($filename).' AND '.
		$db->nameQuote('catid').' = '.$db->Quote($categoryID);

		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * Creates a new empty article in the specified category, with an
	 * alias equal to $filename.
	 *
	 * @param string $filename The alias to set
	 * @param int $categoryID Category to store into
	 * @return int The generated article's ID
	 */
	function createDummyArticle($filename, $category)
	{
		global $mainframe;

		// Clean up filename
		$filename = str_replace('_','-',$filename);

		// Initialize variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();

		$nullDate	= $db->getNullDate();
		$datenow =& JFactory::getDate();

		$data = array(
			'id'			=> '0',
			'title'			=> $filename,
			'alias'			=> $filename,
			'catid'			=> $category,
			'published'		=> 0,
			'introtext'		=> '<p>Dummy content</p>',
			'fulltext'		=> '',
			'gallery'		=> null,
			'video'			=> null,
			'extra_fields'	=> null,
			'extra_fields_search' => null,
			'created'		=> $datenow->toMySQL(),
			'created_by'	=> $user->get('id'),
			'created_by_alias' => '',
			'checked_out'	=> 0,
			'checked_out_time' => '0000-00-00 00:00:00',
			'modified'		=> $datenow->toMySQL(),
			'modified_by'	=> $user->get('id'),
			'publish_up'	=> $datenow->toMySQL(),
			'publish_down'	=> '0000-00-00 00:00:00',
			'trash'			=> 0,
			'access'		=> 0,
			'ordering'		=> 0,
			'featured'		=> 0,
			'featured_ordering'	=> 0,
			'image_caption'	=> '',
			'image_credits'	=> '',
			'video_caption'	=> '',
			'video_credits'	=> '',
			'hits' => '',
			'params' => 'catItemTitle='."\n".'catItemTitleLinked='."\n".'catItemFeaturedNotice='."\n".'catItemAuthor='."\n".'catItemDateCreated='."\n".'catItemRating='."\n".'catItemImage='."\n".'catItemIntroText='."\n".'catItemExtraFields='."\n".'catItemHits='."\n".'catItemCategory='."\n".'catItemTags='."\n".'catItemAttachments='."\n".'catItemAttachmentsCounter='."\n".'catItemVideo='."\n".'catItemImageGallery='."\n".'catItemDateModified='."\n".'catItemReadMore='."\n".'catItemCommentsAnchor='."\n".
						' =Advanced'."\n".'catItemK2Plugins='."\n".'itemDateCreated='."\n".'itemTitle='."\n".'itemFeaturedNotice='."\n".'itemAuthor='."\n".'itemFontResizer='."\n".'itemPrintButton='."\n".'itemEmailButton='."\n".'itemSocialButton='."\n".'itemVideoAnchor='."\n".'itemImageGalleryAnchor='."\n".'itemCommentsAnchor='."\n".'itemRating='."\n".'itemImage='."\n".'itemImgSize='."\n".'itemImageMainCaption='."\n".'itemImageMainCredits='."\n".'itemIntroText='."\n".'itemFullText='."\n".
						'itemExtraFields='."\n".'itemDateModified='."\n".'itemHits='."\n".'itemTwitterLink='."\n".'itemCategory='."\n".'itemTags='."\n".'itemShareLinks='."\n".'itemAttachments='."\n".'itemAttachmentsCounter='."\n".'itemRelated='."\n".'itemRelatedLimit='."\n".'itemVideo='."\n".'itemVideoCaption='."\n".'itemVideoCredits='."\n".'itemImageGallery='."\n".'itemNavigation='."\n".'itemComments='."\n".'itemAuthorBlock='."\n".'itemAuthorImage='."\n".'itemAuthorDescription='."\n".
						'itemAuthorURL='."\n".'itemAuthorEmail='."\n".'itemAuthorLatest='."\n".'itemAuthorLatestLimit='."\n".'itemK2Plugins=',
			'metadesc'		=> '',
			'metadata'		=> '',
			'metakey'		=> '',
			'plugins'		=> ''
			);

			JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
			$row = & JTable::getInstance('K2Item', 'Table');
			if (!$row->bind($data)) {
				JError::raiseError( 500, $db->stderr() );
				return false;
			}

			// sanitise id field
			$row->id = (int) $row->id;

			$isNew = true;

			// Make sure the data is valid
			if (!$row->check()) {
				JError::raiseError( 500, $row->getError() );
				return false;
			}

			// Store the content to the database
			if (!$row->store()) {
				JError::raiseError( 500, $db->stderr() );
				return false;
			}

			// Check the article and update item order
			$row->checkin();
			$row->reorder('catid = '.(int) $row->catid.' AND published = 1');

			return $row->id;
	}

	function saveArticle($myID, &$data, &$titleMap, &$orderMap)
	{
		global $mainframe;

		// Initialize variables
		$db		= & JFactory::getDBO();
		$user	= & JFactory::getUser();

		$nullDate	= $db->getNullDate();

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
		$row = & JTable::getInstance('K2Item', 'Table');
		$row->load($myID);
		$row->introtext = $data;
		$row->fulltext = '';

		$row->title = $titleMap[$row->alias];
		$row->ordering = $orderMap[$row->alias];

		$isNew = false;
		$datenow =& JFactory::getDate();
		$row->modified 		= $datenow->toMySQL();
		$row->modified_by 	= $user->get('id');

		// Make sure the data is valid
		if (!$row->check()) {
			JError::raiseError( 500, $db->stderr() );
			return false;
		}

		// Store the content to the database
		if (!$row->store()) {
			JError::raiseError( 500, 'dead' );
			return false;
		}

		// Check the article and update item order
		$row->checkin();
		$row->publish(array($row->id), 1, $user->get('id'));

		// Hack me plenty: Joomla! is screwing up the order when you use reorder().
		// Workaround by directly manipulating the database table. Kids, don't try this at home!
		$sql = 'UPDATE '.$db->nameQuote('#__k2_items').' SET '.$db->nameQuote('ordering').' = '.
			$db->Quote($orderMap[$row->alias]).' WHERE '.$db->nameQuote('id').' = '.
			$db->Quote($myID);
		$db->setQuery($sql);
		$db->query();
	}

	/**
	 * Creates a new menu item, if required
	 *
	 */
	function createMenuItem($catID, $k2id, $alias)
	{
		static $parentMenuItem = null;

		$db = JFactory::getDBO();

		// Load the parent menu item
		if(is_null($parentMenuItem))
		{
			$component =& JComponentHelper::getComponent( 'com_docimport' );
			$params = new JParameter($component->params);
			$parentMenuID = $params->get('base', 0);
			$sql = 'SELECT * FROM #__menu WHERE id = '.$parentMenuID;
			$db->setQuery($sql);
			$parentMenuItem = $db->loadObject();
		}

		// First make sure there is a submenu for the category
		$catAlias = $this->getCategoryAlias($catID);
		$sql = 'SELECT * FROM #__menu WHERE `alias` = '.$db->Quote($catAlias).' AND `parent` = '.$parentMenuItem->id;
		$db->setQuery($sql);
		$categoryMenuItem = $db->loadObject();
		if(is_null($categoryMenuItem))
		{
			// Get the category name
			$sql = 'SELECT '.$db->nameQuote('name').' FROM #__k2_categories WHERE '.$db->nameQuote('id').
					'='.$db->Quote($catID).' LIMIT 0,1';
			$db->setQuery($sql);
			$catName = $db->loadResult();

			// Get the component ID
			$sql = "SELECT min(`id`) FROM #__components WHERE `option` = 'com_k2'";
			$db->setQuery($sql);
			$componentID = $db->loadResult();

			// Create a new item
			//jimport('joomla.database.table.menu');
			require_once JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'menu.php';
			$oMenu = new JTableMenu($db);
			$oMenu->save(array(
				'id'				=> 0,
				'menutype'			=> $parentMenuItem->menutype,
				'name'				=> $catName,
				'alias'				=> $catAlias,
				'link'				=> 'index.php?option=com_k2&view=itemlist&layout=category&task=category&id='.$catID,
				'type'				=> 'component',
				'published'			=> 1,
				'componentid'		=> $componentID,
				'parent'			=> $parentMenuItem->id,
				'sublevel'			=> 1,
				'ordering'			=> 0,
				'checked_out'		=> 0,
				'checked_out_time'	=> 0,
				'pollid'			=> 0,
				'browserNav'		=> 0,
				'access'			=> 0,
				'utaccess'			=> 0,
				'params'			=> '',
				'lft'				=> 0,
				'rgt'				=> 0,
				'home'				=> 0
			));

			$categoryMenuItem = clone $oMenu;
		}

		// Does a menu with this alias belonging to that category already exists?
		$sql = 'SELECT `id` FROM `#__menu` WHERE `alias` = '.$db->Quote($alias).' AND `parent` = '.$categoryMenuItem->id;
		$db->setQuery($sql);
		$menuid = $db->loadResult();

		if(!empty($menuid)) return $menuid;

		// No it doesn't exist, let's create it

		// Get the component ID
		$sql = "SELECT min(`id`) FROM #__components WHERE `option` = 'com_k2'";
		$db->setQuery($sql);
		$componentID = $db->loadResult();

		$url = 'index.php?option=com_k2&view=item&id='.$k2id;

		//jimport('joomla.database.table.menu');
		require_once JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'menu.php';
		$oMenu = new JTableMenu($db);
		$oMenu->save(array(
			'id'				=> 0,
			'menutype'			=> $parentMenuItem->menutype,
			'name'				=> $alias,
			'alias'				=> $alias,
			'link'				=> $url,
			'type'				=> 'component',
			'published'			=> 1,
			'componentid'		=> $componentID,
			'parent'			=> $categoryMenuItem->id,
			'sublevel'			=> 2,
			'ordering'			=> 0,
			'checked_out'		=> 0,
			'checked_out_time'	=> 0,
			'pollid'			=> 0,
			'browserNav'		=> 0,
			'access'			=> 0,
			'utaccess'			=> 0,
			'params'			=> '',
			'lft'				=> 0,
			'rgt'				=> 0,
			'home'				=> 0
		));

		return $oMenu->id;
	}
}
?>