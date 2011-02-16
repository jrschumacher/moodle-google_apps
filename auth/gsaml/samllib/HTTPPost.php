<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 * Implementation of the SAML 2.0 HTTP-POST binding.
 *
 * @author Andreas �kre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id: HTTPPost.php 639 2008-06-12 08:48:10Z olavmrk $
 */
class SimpleSAML_Bindings_SAML20_HTTPPost {

	private $configuration = null;
	private $metadata = null;

	function __construct(SimpleSAML_Configuration $configuration, SimpleSAML_Metadata_MetaDataStorageHandler $metadatastore) {
		$this->configuration = $configuration;
		$this->metadata = $metadatastore;
	}
	
	
	public function sendResponseUnsigned($response, $idpentityid, $spentityid, $relayState = null, $endpoint = 'AssertionConsumerService') {

		SimpleSAML_Utilities::validateXMLDocument($response, 'saml20');

		$idpmd = $this->metadata->getMetaData($idpentityid, 'saml20-idp-hosted');
		$spmd = $this->metadata->getMetaData($spentityid, 'saml20-sp-remote');
		
		$destination = $spmd[$endpoint];
		
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8">
			<title>Send SAML 2.0 Authentication Response</title>
		</head>
		<body>
		<h1>Send SAML 2.0 Authentication Response</h1>
		 
		 <form style="border: 1px solid #777; margin: 2em; padding: 2em" method="post" action="' . $destination . '">
			<input type="hidden" name="SAMLResponse" value="' . base64_encode($response) . '" />
			<input type="hidden" name="RelayState" value="' . $relayState. '">
			<input type="submit" value="Submit the SAML 1.1 Response" />
		 </form>
		 
		<ul>
			<li>From IdP: <tt>' . $idpentityid . '</tt></li>
			<li>To SP: <tt>' . $spentityid . '</tt></li>
			<li>SP Assertion Consumer Service URL: <tt>' . $destination . '</tt></li>
			<li>RelayState: <tt>' . $relayState . '</tt></li>
		</ul>
		
		<p>SAML Message: <pre>' .  htmlentities($response) . '</pre>
		
		
		</body>
		</html>';
	}
	
	public function sendResponse($response, $idmetaindex, $spentityid, $relayState = null) {

		$idpmd = $this->metadata->getMetaData($idmetaindex, 'saml20-idp-hosted');
		$spmd = $this->metadata->getMetaData($spentityid, 'saml20-sp-remote');
		
		$destination = $spmd['AssertionConsumerService'];
	
		$privatekey = $this->configuration->getPathValue('certdir') . $idpmd['privatekey'];
		$publiccert = $this->configuration->getPathValue('certdir') . $idpmd['certificate'];

		if (!file_exists($privatekey))
			throw new Exception('Could not find private key file [' . $privatekey . '] which is needed to sign the authentication response');

		if (!file_exists($publiccert)) 
			throw new Exception('Could not find certificate [' . $publiccert . '] to attach to the authentication resposne');

		
		/*
		 * XMLDSig. Sign the complete request with the key stored in cert/server.pem
		 */
		$objXMLSecDSig = new XMLSecurityDSig();
		$objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
	
	
		try {
			$responsedom = new DOMDocument();
			$responsedom->loadXML(str_replace("\n", "", str_replace ("\r", "", $response)));
		} catch (Exception $e) {
			throw new Exception("foo");
		}

		$responseroot = $responsedom->getElementsByTagName('Response')->item(0);
		$firstassertionroot = $responsedom->getElementsByTagName('Assertion')->item(0);


		/* Determine what we should sign - either the Response element or the Assertion. The default
		 * is to sign the Assertion, but that can be overridden by the 'signresponse' option in the
		 * SP metadata or 'saml20.signresponse' in the global configuration.
		 */
		$signResponse = FALSE;
		if(array_key_exists('signresponse', $spmd) && $spmd['signresponse'] !== NULL) {
			$signResponse = $spmd['signresponse'];
			if(!is_bool($signResponse)) {
				throw new Exception('Expected the \'signresponse\' option in the metadata of the' .
					' SP \'' . $spmd['entityid'] . '\' to be a boolean value.');
			}
		} else {
			$signResponse = $this->configuration->getBoolean('saml20.signresponse', FALSE);
		}

		if($signResponse) {
			/* Sign the response. */

			$objXMLSecDSig->addReferenceList(array($responseroot), XMLSecurityDSig::SHA1,
				array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N),
				array('id_name' => 'ID'));
		} else {
			/* Sign the assertion. */

			$objXMLSecDSig->addReferenceList(array($firstassertionroot), XMLSecurityDSig::SHA1,
				array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N),
				array('id_name' => 'ID'));
		}
		
		
		/* create new XMLSecKey using RSA-SHA-1 and type is private key */
		$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
		
		/* Set the passphrase which should be used to open the key, if this attribute is
		 * set in the metadata.
		 */
		if(array_key_exists('privatekey_pass', $idpmd)) {
			$objKey->passphrase = $idpmd['privatekey_pass'];
		}

		/* load the private key from file - last arg is bool if key in file (TRUE) or is string (FALSE) */
		$objKey->loadKey($privatekey,TRUE);
				
		$objXMLSecDSig->sign($objKey);
		
		$public_cert = file_get_contents($publiccert);
		$objXMLSecDSig->add509Cert($public_cert, true);

		if($signResponse) {
			$objXMLSecDSig->appendSignature($responseroot, true, false);
		} else {
			$objXMLSecDSig->appendSignature($firstassertionroot, true, true);
		}
		
		if (isset($spmd['assertion.encryption']) && $spmd['assertion.encryption']) {
			$encryptedassertion = $responsedom->createElement("saml:EncryptedAssertion");
			$encryptedassertion->setAttribute("xmlns:saml", "urn:oasis:names:tc:SAML:2.0:assertion");
		
			$firstassertionroot->parentNode->replaceChild($encryptedassertion, $firstassertionroot);
			$encryptedassertion->appendChild($firstassertionroot);
	
			$enc = new XMLSecEnc();
			$enc->setNode($firstassertionroot);
			$enc->type = XMLSecEnc::Element;
			
			$objKey = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
			if (isset($spmd['sharedkey'])) {
				$objKey->loadkey($spmd['sharedkey']);
			} else {
				$key = $objKey->generateSessionKey();
				$objKey->loadKey($key);

				if (!isset($spmd['certificate'])) {
					throw new Exception("Public key for encrypting assertion needed, but not specified for saml20-sp-remote id: " . $spentityid);
				}

				$sp_publiccert = @file_get_contents($this->configuration->getPathValue('certdir') . $spmd['certificate']);

				if ($sp_publiccert === FALSE) {
					throw new Exception("Public key for encrypting assertion specified but not found for saml20-sp-remote id: " . $spentityid . " Filename: " . $spmd['certificate']);
				}
				
				$keyKey = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, array('type'=>'public'));
				
				$keyKey->loadKey($sp_publiccert);
				
				$enc->encryptKey($keyKey, $objKey);
			}
			$encNode = $enc->encryptNode($objKey); # replacing the unencrypted node
	
		}
		$response = $responsedom->saveXML();
		
		SimpleSAML_Utilities::validateXMLDocument($response, 'saml20');
		
		# openssl genrsa -des3 -out server.key 1024 
		# openssl rsa -in server.key -out server.pem
		# openssl req -new -key server.key -out server.csr
		# openssl x509 -req -days 60 -in server.csr -signkey server.key -out server.crt
		
		
		if ($this->configuration->getValue('debug')) {
	
			$p = new SimpleSAML_XHTML_Template($this->configuration, 'post-debug.php');
			
			$p->data['header'] = 'SAML Response Debug-mode';
			$p->data['RelayStateName'] = 'RelayState';
			$p->data['RelayState'] = $relayState;
			$p->data['destination'] = $destination;
			$p->data['response'] = str_replace("\n", "", base64_encode($response));
			$p->data['responseHTML'] = htmlentities($responsedom->saveHTML());
			
			$p->show();

		
		} else {

			$p = new SimpleSAML_XHTML_Template($this->configuration, 'post.php');
	
			$p->data['RelayStateName'] = 'RelayState';
			$p->data['RelayState'] = $relayState;
			$p->data['destination'] = $destination;
			$p->data['response'] = base64_encode($response);
			$p->show();

		
		}
		
		
	}
	
	public function decodeResponse($post) {
		if (!isset($post["SAMLResponse"])) throw new Exception('Could not get SAMLResponse from Browser/POST. May be there is some redirection related problem on your server? In example apache redirecting the POST to http to a GET on https.');
		
		$rawResponse = 	$post["SAMLResponse"];
		$relaystate = 	$post["RelayState"];
		

		
		$samlResponseXML = base64_decode( $rawResponse );

		SimpleSAML_Utilities::validateXMLDocument($samlResponseXML, 'saml20');
		
		//error_log("Response is: " . $samlResponseXML);
        
		$samlResponse = new SimpleSAML_XML_SAML20_AuthnResponse($this->configuration, $this->metadata);
	
		$samlResponse->setXML($samlResponseXML);
		
		if (isset($relaystate)) {
			$samlResponse->setRelayState($relaystate);
		}
	
        #echo("Authn response = " . $samlResponse );

        return $samlResponse;
        
	}


	
}

?>