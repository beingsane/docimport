<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Model;

use FOF30\Container\Container;
use FOF30\Model\DataModel;

defined('_JEXEC') or die();

/**
 * Model class for the Articles
 *
 * Fields:
 *
 * @property int    $docimport_category_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $image
 * @property int    $process_plugins
 * @property int    $last_timestamp
 * @property string $language
 * @property int    $access
 *
 * Filters / state:
 *
 * @method  $this  docimport_article_id()   docimport_article_id(int $v)
 * @method  $this  docimport_category_id()  docimport_category_id(int $v)
 * @method  $this  category()               category(int $v)
 * @method  $this  title()                  title(string $v)
 * @method  $this  search()                 search(string $v)
 * @method  $this  language()               language(array $v)
 *
 * Relations:
 *
 * @property-read  Articles[]	 $articles  The Articles of this DocImport Category
 */
class Categories extends DataModel
{
	/**
	 * Public constructor. Adds behaviours and sets up the behaviours and the relations
	 *
	 * @param   Container  $container
	 * @param   array      $config
	 */
	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		// Add the filtering behaviour
		$this->addBehaviour('Filters');

		$this->blacklistFilters([
			'title',
		    'description',
		    'image',
		    'process_plugins',
		    'last_timestamp',
		]);

		// Other front-end behaviours
		if ($container->platform->isFrontend())
		{
			$this->addBehaviour('Access');
			$this->addBehaviour('Langauge');
		}

		// Set up relations
		$this->hasMany('articles', 'Articles', 'docimport_category_id', 'docimport_category_id');
	}

	/**
	 * @param   \JDatabaseQuery  $query
	 */
	protected function onBeforeBuildQuery(\JDatabaseQuery &$query)
	{
		$db = $this->getDbo();

		// Search filter, use either "title" or "search" model state.
		$fltSearch = $this->getState('title', null, 'string');
		$fltSearch = $this->getState('search', $fltSearch, 'string');

		if (!empty($fltSearch))
		{
			$query->where(
				$db->quoteName('a') . '.' . $db->quoteName('title') . ' LIKE ' . $db->quote('%' . $fltSearch . '%')
			);
		}

		// Set the default ordering by ID, descending
		if (is_null($this->getState('filter_order', null, 'cmd')) && is_null($this->getState('filter_order_Dir', null, 'cmd')))
		{
			$this->setState('filter_order', $this->getIdFieldName());
			$this->setState('filter_order_Dir', 'DESC');
		}
	}

	public function check()
	{
		// Create a new or sanitise an existing slug
		if (empty($this->slug))
		{
			// Auto-fetch a slug
			$this->slug = \JApplicationHelper::stringURLSafe($this->title);
		}
		else
		{
			// Make sure nobody adds crap characters to the slug
			$this->slug = \JApplicationHelper::stringURLSafe($this->slug);
		}

		// Look for a similar slug
		/** @var self $existingItems */
		$existingItems = $this->getClone()->setIgnoreRequest(true)->savestate(false)
		                      ->slug([
			                      'method' => 'exact',
			                      'value' => $this->slug
		                      ])
		                      ->get(true);

		if ($existingItems->count())
		{
			$count = 0;
			$k     = $this->getKeyName();

			foreach ($existingItems as $item)
			{
				if ($item->$k != $this->$k)
				{
					$count ++;
				}
			}

			if ($count != 0)
			{
				$this->slug .= ' ' . \JFactory::getDate()->toUnix();
			}
		}

		if (empty($this->language))
		{
			$this->language = '*';
		}

		if (empty($this->access))
		{
			$this->access = 1;
		}
	}
}