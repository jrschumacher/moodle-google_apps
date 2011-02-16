<?php
/* 
 * SAML 2.0 Meta data for simpleSAMLphp
 *
 * The SAML 2.0 IdP Hosted config is used by the SAML 2.0 IdP to identify itself.
 *
 * Required parameters:
 *   - host
 *   - privatekey
 *   - certificate
 *   - auth
 *   - authority
 *
 * Optional Parameters:
 *   - 'userid.attribute'
 *
 *
 * Request signing (optional paramters)
 *    When request.signing is true the privatekey and certificate of the SP
 *    will be used to sign/verify all messages received/sent with the HTTPRedirect binding.
 *    The certificate and privatekey from above will be used for signing and 
 *    verification purposes.  
 *
 *   - request.signing
 *
 */


$metadata = array( 

	// The SAML entity ID is the index of this config.
	'__DYNAMIC:1__' => array(
		'host'				=>	'__DEFAULT__', 
		// X.509 key and certificate. Relative to the cert directory.
		// TODO:
		// names of the files uploaded need to appear here.
		// TODO: ARGH! I could have changed made 3 changes and left the other samllibs alone! dang it.
		'privatekey'		=>	'googleappsidp.pem', 
		'certificate'		=>	'googleappsidp.crt', 
		
        'privatekey'		=>	basename(get_config('auth/gsaml','privatekey')),
		'certificate'		=>	basename(get_config('auth/gsaml','certificate')),
		'auth'				=>	'../../../../login/index.php', // To GoTo Moodle's Login page
		//'auth'				=>	'auth/login-auto.php',        //  Goto regular login page
		'authority'         =>  'login',
	)

// I could write override code here... but it might break things.. many things.

);

?>
