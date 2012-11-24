<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

if(!defined('FOF_INCLUDED')) {
	include_once JPATH_LIBRARIES.'/fof/include.php';
}

function docimportBuildRoute(&$query)
{
	static $model = null;
	
	if(!is_object($model)) {
		$model = FOFModel::getAnInstance('Urls','DocimportModel');
	}
	
	$sef = $model->getSef($query);
	if($sef === false) {
		$oldQuery = $query;
		$sef = docimportBuildRouteCLASSIC($query);
		if(!empty($sef)) {
			if(array_key_exists('Itemid', $oldQuery)) {
				$value = $oldQuery['Itemid'];
			} else {
				$value = '0';
			}
			$value .= '/'.implode('/', $sef);
			$model->saveQuery($oldQuery, $value);
		}
	} else {
		if(is_array($sef)) $sef = $sef['sef'];
		$sef = explode('/', $sef);
		array_shift($sef);
		foreach(array_keys($query) as $k) {
			if(in_array($k,array('option','Itemid'))) continue;
			unset($query[$k]);
		}
	}
	return $sef;
}

function docimportParseRoute(&$segments)
{
	static $model = null;
	
	if(!is_object($model)) {
		$model = FOFModel::getAnInstance('Urls','DocimportModel');
	}
	
	$menus = JMenu::getInstance('site');
	$menu = $menus->getActive();
	if($menu) {
		$itemID = $menu->id;
	} else {
		$itemID = 0;
	}
	
	$segments = DocimportRouterHelper::preconditionSegments($segments);
	
	$sef = $itemID.'/'.implode('/', $segments);
	$nonsef = $model->getNonsef($sef);
	if(is_array($nonsef)) {
		$segments = array();
		return $nonsef;
	} else {
		return docimportParseRouteCLASSIC($segments);
	}
}

function docimportBuildRouteCLASSIC(&$query)
{
	$segments = array();
	
	//If there is only the option and Itemid, let Joomla! decide on the naming scheme
	if( isset($query['option']) && isset($query['Itemid']) &&
		!isset($query['view']) && !isset($query['task']) &&
		!isset($query['layout']) && !isset($query['id']) )
	{
		return $segments;
	}
	
	// Load the site's menus
	$menus = JMenu::getInstance('site');
	
	// Get some interesting variables
	$view = DocimportRouterHelper::getAndPop($query, 'view', 'categories');
	$task = DocimportRouterHelper::getAndPop($query, 'task', 'browse');
	$id = DocimportRouterHelper::getAndPop($query, 'id');
	$queryItemid = DocimportRouterHelper::getAndPop($query, 'Itemid');
	
	// Fix the View/Task variables
	switch($view) {
		case 'category':
			if(($task == 'browse') && !empty($id)) {
				$task = 'read';
			} elseif(empty($id)) {
				$view = 'categories';
				$task = 'browse';
			}
			break;
			
		case 'categories':
			$task = 'browse';
			break;
		
		case 'article':
			if(empty($id)) {
				$view = 'categories';
				$task = 'browse';
			} else {
				$task = 'read';
			}
			break;
			
		default:
			$view = 'categories';
			$task = 'browse';
			break;
	}
	
	$qoptions = array( 'view' => $view, 'id' => $id, 'option' => 'com_docimport' );
	
	switch($view) {
		case 'categories':
			// Find a suitable Itemid
			$menu = DocimportRouterHelper::findMenu($qoptions);
			$Itemid = empty($menu) ? null : $menu->id;
			
			if(!empty($Itemid))
			{
				// Joomla! will let the menu item naming work its magic
				$query['Itemid'] = $Itemid;
			} else {
				if($queryItemid) {
					$menu = $menus->getItem($queryItemid);
					$mView = isset($menu->query['view']) ? $menu->query['view'] : 'categories';
					// No, we have to find another root
					if( ($mView != 'categories') ) $Itemid = null;
				}				
			}
			
			break;
		
		case 'category':
			// Get category slug
			$slug = FOFModel::getTmpInstance('Categories','DocimportModel')
				->setId($id)
				->getItem()
				->slug;

			// Try to find a menu item for this category
			$options = $qoptions; unset($options['id']);
			$params = array('catid' => $id);
			$menu = DocimportRouterHelper::findMenu($options, $params);
			$Itemid = empty($menu) ? null : $menu->id;

			if(!empty($Itemid))
			{
				// A category menu item found, use it
				$query['Itemid'] = $Itemid;
			}
			else
			{
				// Not found. Try fetching a browser menu item
				$options = array('view' => 'categories', 'option' => 'com_docimport');
				$menu = DocimportRouterHelper::findMenu($options);
				$Itemid = empty($menu) ? null : $menu->id;
				if(!empty($Itemid))
				{
					// Push the Itemid and category alias
					$query['Itemid'] = $menu->id;
					$segments[] = $slug;
				}
				else
				{
					// Push the browser layout and category alias
					$segments[] = 'categories';
					$segments[] = $slug;
				}
			}
			
			
			// Do we have a category menu?
			if(empty($Itemid) && !empty($queryItemid))
			{
				$itemId = $queryItemid;
				$menu = $menus->getItem($Itemid);
				$mView = isset($menu->query['view']) ? $menu->query['view'] : 'categories';
				// No, we have to find another root
				if( ($mView == 'category') )
				{
					$params = ($menu->params instanceof JRegistry) ? $menu->params : $menus->getParams($Itemid);
					if($params->get('catid',0) == $id)
					{
						$query['Itemid'] = $Itemid;
					}
				}
			}
			break;
		
		case 'article':
			// Get article info
			$article = FOFModel::getTmpInstance('Articles','DocimportModel')
				->setId($id)
				->getItem();
			// Get slug
			$slug = FOFModel::getTmpInstance('Categories','DocimportModel')
				->setId($article->docimport_category_id)
				->getItem()
				->slug;
			
			// Try to find a category menu item
			$options = array('view'=>'category', 'option' => 'com_docimport');
			$params = array('catid'=>$article->docimport_category_id);
			$menu = DocimportRouterHelper::findMenu($options, $params);
			$Itemid = null;
			if(!empty($menu))
			{
				// Found it! Just append the article slug
				$Itemid = $menu->id;
				$query['Itemid'] = $menu->id;
				$segments[] = $article->slug;
			}
			else
			{
				// Nah. Let's find a categories menu item.
				$options = array('view'=>'categories', 'option' => 'com_docimport');
				$menu = DocimportRouterHelper::findMenu($options);
				if(!empty($menu))
				{
					// We must add the category and article slug.
					$Itemid = $menu->id;
					$query['Itemid'] = $menu->id;
					$segments[] = $slug;
					$segments[] = $article->slug;
				}
				else
				{
					// I must add the full path
					$segments[] = 'categories';
					$segments[] = $slug;
					$segments[] = $article->slug;
				}
			}
			
			// Do we have a "category" menu?
			if(!$Itemid && $queryItemid) {
				$Itemid = $queryItemid;
				$menu = $menus->getItem($Itemid);
				$mView = isset($menu->query['view']) ? $menu->query['view'] : 'categories';
				if( ($mView == 'categories') )
				{
					// No. It is a categories menu item. We must add the category and article slug.
					$query['Itemid'] = $Itemid;
				}
				elseif( ($mView == 'category') )
				{
					// Yes! Is it the category we want?
					$params = ($menu->params instanceof JRegistry) ? $menu->params : $menus->getParams($Itemid);
					if($params->get('catid',0) == $article->docimport_category_id)
					{
						// Cool! Just append the article slug
						$query['Itemid'] = $Itemid;
					}
				}
			}
			
			break;
	}
	
	return $segments;
}

function docimportParseRouteCLASSIC(&$segments)
{
	$query = array();
	$menus = JMenu::getInstance('site');
	$menu = $menus->getActive();
	
	if(is_null($menu)) {
		// No menu. The segments are categories/category_slug/article_slug
		switch(count($segments)) {
			case 1:
				// Categories view
				$query['view'] = 'categories';
				array_pop($segments); // Remove the "categories" thingy
				break;
			
			case 2:
				// Category view
				$query['view'] = 'category';
				$slug = array_pop($segments);
				array_pop($segments); // Remove the "categories" thingy

				// Load the category
				$category = FOFModel::getTmpInstance('Categories','DocimportModel')
					->slug($slug)
					->getFirstItem();

				if(empty($category))
				{
					$query['view'] = 'browse';
				}
				else
				{
					$query['id'] = $category->docimport_category_id;
				}
				break;
				
			case 3:
				// Article view
				$query['view'] = 'article';
				$slug_article = array_pop($segments);
				$slug_category = array_pop($segments);
				array_pop($segments); // Remove the "categories" thingy
				
				// Load the category
				$category = FOFModel::getTmpInstance('Categories','DocimportModel')
					->slug($slug_category)
					->getFirstItem();
			
				// Load the article
				$article = FOFModel::getTmpInstance('Articles','DocimportModel')
					->category($category->docimport_category_id)
					->slug($slug_article)
					->getFirstItem();

				if(empty($article->docimport_article_id))
				{
					$query['view'] = 'categories';
				}
				else
				{
					$query['id'] = $article->docimport_article_id;
				}

				break;
		}
	} else {
		// A menu item is defined
		$view = $menu->query['view'];
		$slug_article = null;
		$slug_category = null;
		
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
			$catid = $menuparams->get('catid', null);
		} else {
			$catid = $menuparams->getValue('catid', null);
		}
		
		if( empty($view) || ($view == 'categories') || ($view == 'category') )
		{
			switch(count($segments))
			{
				case 0:
					// Category view
					$query['view'] = 'category';
					$view = 'category';
					break;

				case 1:
					// Article view
					$query['view'] = 'article';
					$view = 'article';
					$slug_article = array_pop($segments);
					break;
			}
		}
		else
		{
			$query['view'] = 'article';
		}
		
		if(!is_null($slug_article)) {
			// Load the article
			$article = FOFModel::getTmpInstance('Articles','DocimportModel')
				->category((int)$catid)
				->slug($slug_article)
				->getFirstItem();

			if(empty($article->docimport_article_id))
			{
				$query['view'] = 'category';
				$query['id'] = $catid;
			}
			else
			{
				$query['id'] = $article->docimport_article_id;
			}
		} elseif( is_null($slug_article) ) {
			// Load the category
			$category = FOFModel::getTmpInstance('Categories','DocimportModel')
				->setId($catid)
				->getItem();
			
			if(empty($category->docimport_category_id)) {
				$query['view'] = 'categories';
			} else {
				$query['view'] = 'category';
				$query['id'] = $catid;
			}
		} else {
			$query['view'] = 'categories';
		}
	}

	return $query;
}

class DocimportRouterHelper
{
	static function getAndPop(&$query, $key, $default = null)
	{
		if(isset($query[$key]))
		{
			$value = $query[$key];
			unset($query[$key]);
			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Finds a menu whose query parameters match those in $qoptions
	 * @param array $qoptions The query parameters to look for
	 * @param array $params The menu parameters to look for
	 * @return null|object Null if not found, or the menu item if we did find it
	 */
	static public function findMenu($qoptions = array(), $params = null)
	{
		static $joomla16 = null;
		
		if(is_null($joomla16)) {
			$joomla16 = version_compare(JVERSION,'1.6.0','ge');
		}
		
		// Convert $qoptions to an object
		if(empty($qoptions) || !is_array($qoptions)) $qoptions = array();

		$menus = JMenu::getInstance('site');
		$menuitem = $menus->getActive();

		// First check the current menu item (fastest shortcut!)
		if(is_object($menuitem)) {
			if(self::checkMenu($menuitem, $qoptions, $params)) {
				return $menuitem;
			}
		}

		foreach($menus->getMenu() as $item)
		{
			if($joomla16) {
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			} elseif($item->published)
			{
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			}
		}

		return null;
	}

	/**
	 * Checks if a menu item conforms to the query options and parameters specified
	 *
	 * @param object $menu A menu item
	 * @param array $qoptions The query options to look for
	 * @param array $params The menu parameters to look for
	 * @return bool
	 */
	static public function checkMenu($menu, $qoptions, $params = null)
	{
		$query = $menu->query;
		foreach($qoptions as $key => $value)
		{
			if(is_null($value)) continue;
			if(!isset($query[$key])) return false;
			if($query[$key] != $value) return false;
		}

		if(!is_null($params))
		{
			$menus = JMenu::getInstance('site');
			$check =  $menu->params instanceof JRegistry ? $menu->params : $menus->getParams($menu->id);

			foreach($params as $key => $value)
			{
				if(is_null($value)) continue;
				if( $check->get($key) != $value ) return false;
			}
		}

		return true;
	}

	static public function preconditionSegments($segments)
	{
		$newSegments = array();
		if(!empty($segments)) foreach($segments as $segment)
		{
			if(strstr($segment,':'))
			{
				$segment = str_replace(':','-',$segment);
			}
			if(is_array($segment)) {
				$newSegments[] = implode('-', $segment);
			} else {
				$newSegments[] = $segment;
			}
		}
		return $newSegments;
	}
}