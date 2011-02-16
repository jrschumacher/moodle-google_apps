<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 * AttributeFilter is a mapping between attribute names.
 *
 * @author Andreas �kre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id: AttributeFilter.php 636 2008-06-12 07:07:20Z lassebirnbaum $
 */
class SimpleSAML_XML_AttributeFilter {

	private $attributes = null;

	function __construct(SimpleSAML_Configuration $configuration, $attributes) {
		$this->configuration = $configuration;
		$this->attributes = $attributes;
	}
	

	/**
	 * Will process attribute napping, and altering based on metadata.
	 */
	public function process($idpmetadata, $spmetadata) {
	
		if (isset($idpmetadata['attributemap'])) {
			SimpleSAML_Logger::debug('Applying IdP specific attributemap: ' . $idpmetadata['attributemap']);
			$this->namemap($idpmetadata['attributemap']);
		}
		if (isset($spmetadata['attributemap'])) {
			SimpleSAML_Logger::debug('Applying SP specific attributemap: ' . $spmetadata['attributemap']);
			$this->namemap($spmetadata['attributemap']);
		}
		if (isset($idpmetadata['attributealter'])) {
			if (!is_array($idpmetadata['attributealter'])) {
				SimpleSAML_Logger::debug('Applying IdP specific attribute alter: ' . $idpmetadata['attributealter']);
				$this->alter($idpmetadata['attributealter'],$spmetadata['entityid'],$idpmetadata['entityid']);
			} else {
				foreach($idpmetadata['attributealter'] AS $alterfunc) {
					SimpleSAML_Logger::debug('Applying IdP specific attribute alter: ' . $alterfunc);
					$this->alter($alterfunc,$spmetadata['entityid'],$idpmetadata['entityid']);
				}
			}
		}
		if (isset($spmetadata['attributealter'])) {
			if (!is_array($spmetadata['attributealter'])) {
				SimpleSAML_Logger::debug('Applying SP specific attribute alter: ' . $spmetadata['attributealter']);
				$this->alter($spmetadata['attributealter'],$spmetadata['entityid'],$idpmetadata['entityid']);
			} else {
				foreach($spmetadata['attributealter'] AS $alterfunc) {
					SimpleSAML_Logger::debug('Applying SP specific attribute alter: ' . $alterfunc);
					$this->alter($alterfunc,$spmetadata['entityid'],$idpmetadata['entityid']);
				}
			}
		}
		
	}

	public function processFilter($idpmetadata, $spmetadata) {

		/**
		 * Filter away attributes that are not allowed for this SP.
		 */
		if (isset($spmetadata['attributes'])) {
			SimpleSAML_Logger::debug('Applying SP specific attribute filter: ' . join(',', $spmetadata['attributes']));
			$this->filter($spmetadata['attributes']);
		}
		

	}


	public function namemap($map) {
		
		$mapfile = $this->configuration->getPathValue('attributenamemapdir') . $map . '.php';
		if (!file_exists($mapfile)) throw new Exception('Could not find attributemap file: ' . $mapfile);
		
		include($mapfile);
		
		$newattributes = array();
		foreach ($this->attributes AS $a => $value) {
			if (isset($attributemap[$a])) {
				$newattributes[$attributemap[$a]] = $value;
			} else {
				$newattributes[$a] = $value;
			}
		}
		$this->attributes = $newattributes;
		
	}
	
	/**
	 * This function will call custom alter plugins.
	 */
	public function alter($rule, $spentityid = null, $idpentityid = null) {
		
		$alterfile = $this->configuration->getBaseDir() . 'attributealter/' . $rule . '.php';
		if (!file_exists($alterfile)) throw new Exception('Could not find attributealter file: ' . $alterfile);
		
		include_once($alterfile);
		
		$function = 'attributealter_' . $rule;
		
		if (function_exists($function)) {
			$function($this->attributes, $spentityid, $idpentityid);
		} else {
			throw new Exception('Could not find attribute alter fucntion: ' . $function . ' in file ' .$alterfile);
		}
		
	}
	
	private function addValue($name, $value) {
		if (array_key_exists($name, $this->attributes)) {
			$this->attributes[$name][] = $value;
		} else {
			$this->attributes[$name] = array($value);
		}
	}
	
	public function filter($allowedattributes) {
		$newattributes = array();
		foreach($this->attributes AS $key => $value) {
			if (in_array($key, $allowedattributes)) {
				$newattributes[$key] = $value;
			}
		}
		$this->attributes = $newattributes;
	}
	
	public function getAttributes() {
		return $this->attributes;
	}

	
	
}

?>