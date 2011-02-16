<?php
/**
* Copyright (C) 2009  Moodlerooms Inc.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
* 
* @copyright  Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
* @license    http://opensource.org/licenses/gpl-3.0.html     GNU Public License
* @author Chris Stones
*/
/**
 * Functions here hook into the auth pluggin at these points

    loginpage_hook() {
    If SAMLRequest:
     If User is logged in form a SAMLResponse and redirect to the service
     Else let user log in normally and mark SESSION->samlrequest
    
    user_authenticated_hook(&$user, $username, $password) {
    User authenticaed 
    Check IF SAMLRequest 
    Now send the response and open moodle in a new page (to complete login)
    
    (if no SAML requests don't do anything)
    
    prelogout_hook()
      log out of the SP send messages 
 */
 
 // this lib is included in an auth plugin 
 //require_once('../config.php');
 global $CFG;
 
 // Absolutly necessary samllibs
 // if you have already defined this one THEN you have prob included the rest already
if ( !class_exists('SimpleSAML_Utilities') ) {
    require_once($CFG->dirroot.'/auth/gsaml/samllib/Utilities.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/Configuration.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/SessionHandler.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/SessionHandlerPHP.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/Session.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/MetaDataStorageSource.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/MetaDataStorageHandlerFlatFile.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/MetaDataStorageHandler.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/Validator.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/xmlseclibs.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/AuthnRequest.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/AuthnResponse_abstract.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/AuthnResponse.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/Logger.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/LoggingHandlerErrorLog.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/LoggingHandlerFile.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/LoggingHandlerSyslog.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/AttributeFilter.php');
     
    //Template
    require_once($CFG->dirroot.'/auth/gsaml/samllib/Template.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/HTTPPost.php');
    require_once($CFG->dirroot.'/auth/gsaml/samllib/HTTPRedirect.php');
}

/**
 * Accept a SAML Request and form a Response
 * NOTE: that this function is Google Specific
 * 
 */
function gsaml_send_auth_response($samldata) {
	global $CFG,$SESSION,$USER;
	
	SimpleSAML_Configuration::init($CFG->dirroot.'/auth/gsaml/config');
	$config   = SimpleSAML_Configuration::getInstance();
	$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
	$session  = SimpleSAML_Session::getInstance();
	
	try {
		$idpentityid = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
		$idmetaindex = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted', 'metaindex');
		$idpmetadata = $metadata->getMetaDataCurrent('saml20-idp-hosted');
		
		if (!array_key_exists('auth', $idpmetadata)) {
			throw new Exception('Missing mandatory parameter in SAML 2.0 IdP Hosted Metadata: [auth]');
		}
		
	} catch (Exception $exception) {
		SimpleSAML_Utilities::fatalError($session->getTrackID(), 'METADATA', $exception);
	}
	
///	SimpleSAML_Logger::info('SAML2.0 - IdP.SSOService: Accessing SAML 2.0 IdP endpoint SSOService');
	
	if (!$config->getValue('enable.saml20-idp', false)) {
		SimpleSAML_Utilities::fatalError($session->getTrackID(), 'NOACCESS');
    }

	$rawRequest = $samldata;
	
	if( !empty($SESSION->samlrelaystate)) {
		$relaystate = $SESSION->samlrelaystate;
	} else {
		$relaystate = NULL;
	}
		
	$decodedRequest = @base64_decode($rawRequest);
	if (!$decodedRequest) {
		throw new Exception('Could not base64 decode SAMLRequest GET parameter');
	}

	$samlRequestXML = @gzinflate($decodedRequest);
	if (!$samlRequestXML) {
		$error = error_get_last();
		throw new Exception('Could not gzinflate base64 decoded SAMLRequest: ' . $error['message'] );
	}		

	SimpleSAML_Utilities::validateXMLDocument($samlRequestXML, 'saml20');
	$samlRequest = new SimpleSAML_XML_SAML20_AuthnRequest($config, $metadata);
	$samlRequest->setXML($samlRequestXML);
	
	if (!is_null($relaystate)) {
		$samlRequest->setRelayState($relaystate);
	}

   // $samlRequest presenting the request object
    $authnrequest = $samlRequest;

    if($session == NULL) {
        debugging('No SAML Session gsaml_send_auth_response', DEBUG_DEVELOPER);
        return false; // if this func returns we Know it's an error
    }
	    
	if(!empty($USER->id)) {
        // TODO: if moodle user is not the same as google user
        //       use the mapping
		$username = $USER->username;
	} else {
		debugging('No User given to gsaml_send_auth_response', DEBUG_DEVELOPER);
        return false;
	}
	
	//TODO: better errors
	if( !$domain = get_config('auth/gsaml','domainname') ) {
        debugging('No domain set in gsaml_send_auth_response', DEBUG_DEVELOPER);
		return false; // if this func returns we Know it's an error
	}
	
	$attributes['useridemail'] =  array($username.'@'.$domain);
    $session->doLogin('login'); // was login
    $session->setAttributes($attributes);
    $session->setNameID(array(
    	'value' => SimpleSAML_Utilities::generateID(),
    	'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'));
	
	  	
	$requestcache = array(
			'RequestID'     => $authnrequest->getRequestID(), 
			'Issuer'        => $authnrequest->getIssuer(),
			'ConsentCookie' => SimpleSAML_Utilities::generateID(),
			'RelayState'    => $authnrequest->getRelayState()
		);
		
		    	    
	try {
		$spentityid = $requestcache['Issuer'];
		$spmetadata = $metadata->getMetaData($spentityid, 'saml20-sp-remote');
		
		$sp_name = (isset($spmetadata['name']) ? $spmetadata['name'] : $spentityid);

		// TODO: Are we really tracking SP's???
		//
		// Adding this service provider to the list of sessions.
		// Right now the list is used for SAML 2.0 only.
		$session->add_sp_session($spentityid);
		
///		SimpleSAML_Logger::info('SAML2.0 - IdP.SSOService: Sending back AuthnResponse to ' . $spentityid);
		
        // TODO: handle passive situtation
        // Rigth now I replaced $isPassive with isset($isPassive) to prevent notice on debug mode
		if (isset($isPassive)) {
			/* Generate an SAML 2.0 AuthNResponse message
			   With statusCode: urn:oasis:names:tc:SAML:2.0:status:NoPassive
			*/
			
			$ar = new SimpleSAML_XML_SAML20_AuthnResponse($config, $metadata);
			$authnResponseXML = $ar->generate($idpentityid, $spentityid, $requestcache['RequestID'], null, array(), 'NoPassive');
		
			// Sending the AuthNResponse using HTTP-Post SAML 2.0 binding
			$httppost = new SimpleSAML_Bindings_SAML20_HTTPPost($config, $metadata);
			$httppost->sendResponse($authnResponseXML, $idpentityid, $spentityid, $requestcache['RelayState']);
			exit;
		}
		
		/*
		 * Attribute handling
		 */
		$attributes = $session->getAttributes();
		$afilter = new SimpleSAML_XML_AttributeFilter($config, $attributes);
		$afilter->process($idpmetadata, $spmetadata);
			
// KEEP this code for REFERENCE
//		/**
//		 * Make a log entry in the statistics for this SSO login.
//		 */
//		$tempattr = $afilter->getAttributes();
//		$realmattr = $config->getValue('statistics.realmattr', null);
//		$realmstr = 'NA';
//		if (!empty($realmattr)) {
//			//error_log('SSO 420: if (!empty($realmattr)) {\n ',0);
//			if (array_key_exists($realmattr, $tempattr) && is_array($tempattr[$realmattr]) ) {
//				$realmstr = $tempattr[$realmattr][0];
//			} else {
//				SimpleSAML_Logger::warning('Could not get realm attribute to log [' . $realmattr. ']');
//			}
//		} 
//		SimpleSAML_Logger::stats('saml20-idp-SSO ' . $spentityid . ' ' . $idpentityid . ' ' . $realmstr);
//		
//		
		$afilter->processFilter($idpmetadata, $spmetadata);
				
		$filteredattributes = $afilter->getAttributes();
//
//		KEEP THIS CODE FOR RERFERENCE
//		/*
//		 * Dealing with attribute release consent.
//		 */
//		$requireconsent = false;
//		if (isset($idpmetadata['requireconsent'])) {
//			//error_log('SSO 453: if (isset($idpmetadata[\'requireconsent\']))\n ',0);
//			if (is_bool($idpmetadata['requireconsent'])) {
//				$requireconsent = $idpmetadata['requireconsent'];
//			} else {
//				throw new Exception('SAML 2.0 IdP hosted metadata parameter [requireconsent] is in illegal format, must be a PHP boolean type.');
//			}
//		}
//		if ($requireconsent) {
//			
//			$consent = new SimpleSAML_Consent_Consent($config, $session, $spentityid, $idpentityid, $attributes, $filteredattributes, $requestcache['ConsentCookie']);
//			
//			if (!$consent->consent()) {	
//				/* Save the request information. */
//				$authId = SimpleSAML_Utilities::generateID();
//				$session->setAuthnRequest('saml2', $authId, $requestcache);
//				
//				$t = new SimpleSAML_XHTML_Template($config, 'consent.php', 'attributes.php');
//				$t->data['header'] = 'Consent';
//				$t->data['sp_name'] = $sp_name;
//				$t->data['attributes'] = $filteredattributes;
//				$t->data['consenturl'] = SimpleSAML_Utilities::selfURLNoQuery();//$selfURLNoQuery; //SimpleSAML_Utilities::selfURLNoQuery(); DEBUG
//				$t->data['requestid'] = $authId;
//				$t->data['consent_cookie'] = $requestcache['ConsentCookie'];
//				$t->data['usestorage'] = $consent->useStorage();
//				$t->data['noconsent'] = '/' . $config->getBaseURL() . 'noconsent.php';
//				$t->show();
//				exit;
//			}
//
//		}
//		// END ATTRIBUTE CONSENT CODE
		
		
		// Generate the SAML 2.0 AuthNResponse message
		$ar = new SimpleSAML_XML_SAML20_AuthnResponse($config, $metadata);
		$authnResponseXML = $ar->generate($idpentityid, $spentityid, $requestcache['RequestID'], null, $filteredattributes);
	    
        // TODO: clean the $SESSION->samlrelaystate so we don't accidently call it again
        
		// Sending the AuthNResponse using HTTP-Post SAML 2.0 binding
		$httppost = new SimpleSAML_Bindings_SAML20_HTTPPost($config, $metadata);
		$httppost->sendResponse($authnResponseXML, $idmetaindex, $spentityid, $requestcache['RelayState']);
		die; // VERY IMPORTANT BUG FIX to stop outputing the rest of the page. 
        
	} catch(Exception $exception) {
		// TODO: better error reporting
		debugging('<pre>'.print_r($exception,true).'</pre>', DEBUG_DEVELOPER);
        return false;
	}
					
}
 
 
 
 
 
 
?>
