<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

if(!class_exists('JoomlaSucksModel')) {
	if(interface_exists('JModel')) {
		abstract class JoomlaSucksModel extends JModelLegacy {}
	} else {
		class JoomlaSucksModel extends JModel {}
	}
}

class DocimportModelUrls extends JoomlaSucksModel
{
	private $urls = array();
	
	public function __construct($config = array()) {
		parent::__construct($config);
		
		$this->load();
	}
	
	public function normaliseQuery($query)
	{
		if(empty($query)) {
			return '';
		} else {
			ksort($query);
			return json_encode($query);
		}
	}
	
	protected function load()
	{
		$db = $this->getDbo();
		$query = FOFQueryAbstract::getNew()
			->select('*')
			->from($db->quoteName('#__docimport_urls'));
		$db->setQuery($query);
		$this->urls = $db->loadAssocList('nonsef', 'sef');
	}
	
	public function getSef($nonsef)
	{
		$nonsef = $this->normaliseQuery($nonsef);
		if(array_key_exists($nonsef, $this->urls)) {
			return $this->urls[$nonsef];
		} else {
			return false;
		}
	}
	
	public function getNonSef($sef)
	{
		if(is_array($sef)) $sef = implode('/', $sef);
		$result = array_search($sef, $this->urls);
		if($result !== false) {
			$result = json_decode($result, true);
		}
		return $result;
	}
	
	public function saveQuery($nonsef, $sef)
	{
		$key = $this->normaliseQuery($nonsef);
		if(is_array($sef)) {
			$value = implode('/', $sef);
		} else {
			$value = $sef;
		}
		
		$existing = array_key_exists($key, $this->urls);
		$this->urls[$key] = $value;
		
		$db = $this->getDbo();
		if($existing) {
			$query = FOFQueryAbstract::getNew($db)
				->update($db->quoteName('#__docimport_urls'))
				->set(
					$db->quoteName('sef').' = '.$db->quote($value)
				)->where(
					$db->quoteName('nonsef').' = '.$db->quote($key)
				);
			$db->setQuery($query);
			return $db->query();
		} else {
			$query = FOFQueryAbstract::getNew($db)
				->insert($db->quoteName('#__docimport_urls'))
				->columns(array(
					$db->quoteName('nonsef'),
					$db->quoteName('sef')
				))
				->values($db->quote($key).','.$db->quote($value));
			$db->setQuery($query);
			return $db->query();
		}
	}
	
	public function nuke()
	{
		$db = $this->getDbo();
		$this->urls = array();
		$db->setQuery('TRUNCATE TABLE '.$db->quoteName('#__docimport_urls'));
		return $db->query();
	}
}