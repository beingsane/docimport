<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class DocimportModelUrls extends JModel
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
			->from($db->nameQuote('#__docimport_urls'));
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
				->update($db->nameQuote('#__docimport_urls'))
				->set(
					$db->nameQuote('sef').' = '.$db->quote($value)
				)->where(
					$db->nameQuote('nonsef').' = '.$db->quote($key)
				);
			$db->setQuery($query);
			return $db->query();
		} else {
			$query = FOFQueryAbstract::getNew($db)
				->insert($db->nameQuote('#__docimport_urls'))
				->columns(array(
					$db->nameQuote('nonsef'),
					$db->nameQuote('sef')
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
		$db->setQuery('TRUNCATE TABLE '.$db->nameQuote('#__docimport_urls'));
		return $db->query();
	}
}