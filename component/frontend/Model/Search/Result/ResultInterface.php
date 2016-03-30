<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Result;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Search result interface
 *
 * @property       string  title            The result's title
 * @property       string  slug             The result's slug (alias)
 * @property       int     id               The result's primary key in its table
 * @property-read  string  link             The URL to the search result item
 * @property-read  string  catlink          The URL to the category where the article belongs
 * @property-read  string  youtubeId        Find an embedded YouTube video and return its ID
 * @property-read  string  youtubeLink      Find an embedded YouTube video and return a link for embed
 * @property-read  string  youtubeIframe    Find an embedded YouTube video and return the embed IFRAME
 * @property-read  string  youtubeExternal  Find an embedded YouTube video and return a direct link to YouTube's site
 * @property-read  string  synopsis         A synopsis of the article's text
 */
interface ResultInterface
{
	/**
	 * Get the URL to access the item
	 *
	 * @return  string
	 */
	public function getLink();

	/**
	 * Get the URL to access the item's category
	 *
	 * @return  string
	 */
	public function getCategoryLink();

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeId();

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeExternal();

	/**
	 * Look for an embedded YouTube video and return an embed URL you can then use in an IFRAME, see getYouTubeIframe
	 *
	 * @return  string
	 */
	public function getYouTubeLink();

	/**
	 * Look for an embedded YouTube video and returns an embed IFRAME
	 *
	 * @param   string  $class            The class to append to the IFRAME
	 * @param   string  $attributeString  Any attributes to append to the IFRAME
	 *
	 * @return  string
	 */
	public function getYouTubeIframe($class = 'flex-video', $attributeString = '');

	/**
	 * Generates a synopsis from the article's full text
	 *
	 * @return string
	 */
	public function getSynopsis();
}