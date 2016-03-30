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
 * Abstract search result class
 *
 * @property-read  string  link             The URL to the search result item
 * @property-read  string  catlink          The URL to the category where the article belongs
 * @property-read  string  youtubeId        Find an embedded YouTube video and return its ID
 * @property-read  string  youtubeLink      Find an embedded YouTube video and return a link for embed
 * @property-read  string  youtubeIframe    Find an embedded YouTube video and return the embed IFRAME
 * @property-read  string  youtubeExternal  Find an embedded YouTube video and return a direct link to YouTube's site
 * @property-read  string  synopsis         A synopsis of the article's text
 */
abstract class AbstractResult implements ResultInterface
{
	/** @var  int  Result item's ID */
	public $id;

	/** @var  string  Result item's title */
	public $title;

	/** @var  string  The name of the field which holds the HTML text */
	public $textFieldName = 'fulltext';

	/**
	 * Get the URL to access the item
	 *
	 * @return  string
	 */
	abstract public function getLink();

	/**
	 * Get the URL to access the item's category
	 *
	 * @return  string
	 */
	abstract public function getCategoryLink();

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeId()
	{
		// //www.youtube-nocookie.com/embed/c1i2sZA58ag?rel=0&cc_load_policy=1"
		$pattern = '#www\.youtube(-nocookie)?\.com/embed/([a-zA-Z0-9]{1,})("|\?)#i';
		$hasMatch = preg_match($pattern, $this->fulltext, $matches);

		if (!$hasMatch)
		{
			return '';
		}

		return $matches[2];
	}

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeExternal()
	{
		$id = $this->getYouTubeId();

		if (!$id)
		{
			return '';
		}

		return "//www.youtube.com/watch?v=$id&playnext=0&list=UL&rel=0&cc_load_policy=1";
	}

	/**
	 * Look for an embedded YouTube video and return an embed URL you can then use in an IFRAME, see getYouTubeIframe
	 *
	 * @return  string
	 */
	public function getYouTubeLink()
	{
		$id = $this->getYouTubeId();

		if (!$id)
		{
			return '';
		}

		return '//www.youtube-nookie.com/embed/' . $id . '?rel=0&cc_load_policy=1&modestbranding=1';
	}

	/**
	 * Look for an embedded YouTube video and returns an embed IFRAME
	 *
	 * @param   string  $class            The class to append to the IFRAME
	 * @param   string  $attributeString  Any attributes to append to the IFRAME
	 *
	 * @return  string
	 */
	public function getYouTubeIframe($class = 'flex-video', $attributeString = '')
	{
		$link = $this->getYouTubeLink();

		if (!$link)
		{
			return '';
		}

		return '<iframe src="' . $link . '" frameborder="0" allowfullscreen="allowfullscreen" class="' . $class . '" ' .
		$attributeString . '></iframe>';
	}

	/**
	 * Generates a synopsis from the article's full text
	 *
	 * @return string
	 */
	public function getSynopsis()
	{
		$stripedText = strip_tags($this->{$this->textFieldName});

		if (strlen($stripedText) < 150)
		{
			return $stripedText;
		}

		return substr($stripedText, 0, 150) . '&hellip;';
	}

	/**
	 * Magic getter, turns the get* methods into virtual properties
	 *
	 * @param   string  $name  The name of the virtual property being accessed
	 *
	 * @return null|string
	 */
	public function __get($name)
	{
		// Map known properties
		switch ($name)
		{
			case 'link':
				return $this->getLink();
				break;

			case 'catlink':
				return $this->getCategoryLink();
				break;

			case 'synopsis':
				return $this->getSynopsis();
				break;

			case 'youtubeId':
				return $this->getYouTubeId();
				break;

			case 'youtubeLink':
				return $this->getYouTubeLink();
				break;

			case 'youtubeIframe':
				return $this->getYouTubeIframe();
				break;

			case 'youtubeExternal':
				return $this->getYouTubeExternal();
				break;
		}

		// Still here? You screwed up. Oops.
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);

		return null;
	}
}