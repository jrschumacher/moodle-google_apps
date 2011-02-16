<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 * Implementation of the SAML 2.0 LogoutRequest message.
 *
 * @author Andreas �kre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id: LogoutRequest.php 610 2008-06-06 06:04:20Z olavmrk $
 */
class SimpleSAML_XML_SAML20_LogoutRequest {

	private $configuration = null;
	private $metadata = null;
	
	private $message = null;
	private $dom;
	private $relayState = null;
	
	
	const PROTOCOL = 'urn:oasis:names:tc:SAML:2.0';


	/**
	 * This variable holds the generated request id for this request.
	 */
	private $id = null;


	function __construct(SimpleSAML_Configuration $configuration, SimpleSAML_Metadata_MetaDataStorageHandler $metadatastore) {
		$this->configuration = $configuration;
		$this->metadata = $metadatastore;

		/* Generate request id. */
		$this->id = SimpleSAML_Utilities::generateID();
	}
	
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
		
			/* if (isset($this->dom) && $this->dom != null ) {
				return $this->dom;
			} */
		
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
	
	
	public function getIssuer() {
		$dom = $this->getDOM();
		$issuer = null;
		
		if (!$dom instanceof DOMDocument) {
			throw new Exception("Could not get message DOM in AuthnRequest object");
		}
		
		//print_r($dom->saveXML());
		
		if ($issuerNodes = $dom->getElementsByTagName('Issuer')) {
			if ($issuerNodes->length > 0) {
				$issuer = $issuerNodes->item(0)->textContent;
			}
		}
		return $issuer;
	}
	
	public function getRequestID() {
		$dom = $this->getDOM();
		$requestid = null;
		
		if (empty($dom)) {
			throw new Exception("Could not get message DOM in AuthnRequest object");
		}
		
		$requestelement = $dom->getElementsByTagName('LogoutRequest')->item(0);
		$requestid = $requestelement->getAttribute('ID');
		return $requestid;
		/*
		if ($issuerNodes = $dom->getElementsByTagName('Issuer')) {
			if ($issuerNodes->length > 0) {
				$requestid = $issuerNodes->item(0)->textContent;
			}
		}
		return $requestid;	
		*/
	}
	


	public function generate($issuer, $receiver, $nameid, $sessionindex, $mode) {
	
		if (!in_array($mode, array('SP', 'IdP'))) {
			throw new Exception('mode parameter of generate() must be either SP or IdP');
		}
		if ($mode == 'IdP') {
			$issuerset = 'saml20-idp-hosted';
			$receiverset = 'saml20-sp-remote';
		} else {
			$issuerset = 'saml20-sp-hosted';
			$receiverset = 'saml20-idp-remote';
		}
	
		$issuermd 	= $this->metadata->getMetaData($issuer, $issuerset);
		$receivermd = $this->metadata->getMetaData($receiver, $receiverset);
		
		if ($mode == 'IdP') {
			$spnamequalifier = isset($receivermd['SPNameQualifier']) ? $receivermd['SPNameQualifier'] : $receivermd['entityid'];
		} else {
			$spnamequalifier = isset($issuermd['SPNameQualifier']) ? $issuermd['SPNameQualifier'] : $issuermd['entityid'];
		}
		
		$issueInstant = SimpleSAML_Utilities::generateTimestamp();

		$destination = $receivermd['SingleLogoutService'];
		
		$logoutRequest = '<samlp:LogoutRequest 
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="' . $this->id . '" Version="2.0"
    Destination="' . htmlspecialchars($destination) . '"
    IssueInstant="' . $issueInstant . '">
    <saml:Issuer >' . htmlspecialchars($issuer) . '</saml:Issuer>
    <saml:NameID Format="' . htmlspecialchars($nameid['Format']) . '" SPNameQualifier="' . htmlspecialchars($spnamequalifier) . '">' . htmlspecialchars($nameid['value']) . '</saml:NameID>
    <samlp:SessionIndex>' . htmlspecialchars($sessionindex) . '</samlp:SessionIndex>
</samlp:LogoutRequest>
';
		
		return $logoutRequest;
	}

	/**
	 * This function retrieves the request id we used for the generated logout request.
	 *
	 * @return The request id of the generated logout request.
	 */
	public function getGeneratedID() {
		return $this->id;
	}

}

?>