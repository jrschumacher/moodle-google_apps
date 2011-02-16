<?php
/* 
 * SAML 2.0 Meta data for simpleSAMLphp
 *
 * The SAML 2.0 SP Remote config is used by the SAML 2.0 IdP to identify trusted SAML 2.0 SPs.
 *
 * Required parameters:
 *   - AssertionConsumerService
 *   - SingleLogoutService
 *
 * Optional parameters:
 *
 *   - simplesaml.attributes (Will you send an attributestatement [true/false])
 *   - NameIDFormat
 *   - ForceAuthn (default: "false")
 *   - simplesaml.nameidattribute (only needed when you are using NameID format email.
 *
 *   - 'base64attributes'	=>	false,
 *   - 'simplesaml.attributes'	=>	true,
 *   - 'attributemap'		=>	'test',
 *   - 'attributes'			=>	array('mail'),
 *   - 'userid.attribute'
 *
 * Request signing
 *    When request.signing is true the certificate of the sp 
 *    will be used to verify all messages received with the HTTPRedirect binding.
 *    The certificate from the SP must be installed in the cert directory 
 *    before verification can be done.  
 *
 *   'request.signing' => false,
 *   'certificate' => "saml2sp.example.org.crt"
 *
 */


require_once('../config.php');
$svars = get_config('auth/gsaml');
 
 
$metadata = array( 

	/*
	 * Example simpleSAMLphp SAML 2.0 SP
	 */
	//'saml2sp.example.org' => array(
 	//	'AssertionConsumerService' => 'https://saml2sp.example.org/simplesaml/saml2/sp/AssertionConsumerService.php', 
 	//	'SingleLogoutService'      => 'https://saml2sp.example.org/simplesaml/saml2/sp/SingleLogoutService.php'
	//),
	
	'google.com' => array(
 		'AssertionConsumerService'		=>	'https://www.google.com/a/'.$svars->domainname.'/acs',
		'NameIDFormat'					=>	'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
		'simplesaml.nameidattribute'	=>	'useridemail', // this key is placed in the attributes of the login script
		'simplesaml.attributes'			=>	false
	)
	
		

);


?>
