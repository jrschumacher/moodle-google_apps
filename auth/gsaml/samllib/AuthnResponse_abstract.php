<?php
 
 if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 * Abstract class of an Authentication response
 *
 * @author Andreas Åkre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id: AuthnResponse.php 610 2008-06-06 06:04:20Z olavmrk $
 * @abstract
 */
 abstract class SimpleSAML_XML_AuthnResponse {

	private $configuration = null;
	private $metadata = 'default.php';
	
	private $message = null;
	private $dom;
	private $relayState = null;
	
	private $validIDs = null;
	
	const PROTOCOL = 'urn:oasis:names:tc:SAML:2.0';

	function __construct(SimpleSAML_Configuration $configuration, SimpleSAML_Metadata_MetaDataStorageHandler $metadatastore) {
		$this->configuration = $configuration;
		$this->metadata = $metadatastore;
	}
	
	
	abstract public function validate();

	abstract public function createSession();

	
	
	abstract public function getAttributes();

	
	abstract public function getIssuer();
	
	abstract public function getNameID();
	
	
	public function setXML($xml) {
		$this->message = $xml;
	}
	
	public function getXML() {
		return $this->message;
	}
	
	public function setRelayState($relayState) {
		$this->relayState = $relayState;
	}
	
	public function getRelayState() {
		return $this->relayState;
	}
	
	public function getDOM() {
		if (isset($this->message) ) {
			
			if (isset($this->dom)) {
				return $this->dom;
			}
		
			$token = new DOMDocument();
			$token->loadXML(str_replace ("\r", "", $this->message));
			if (empty($token)) {
				throw new Exception("Unable to load token");
			}
			$this->dom = $token;
			return $this->dom;
		
		} 
		
		return null;
	}

}

?>