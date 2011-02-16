<?php
/* 
 * SAML 2.0 Meta data for simpleSAMLphp
 *
 * The SAML 2.0 IdP Remote config is used by the SAML 2.0 SP to identify trusted SAML 2.0 IdPs.
 *
 */

// This data is google site specific
/*

   * This example shows an example config that works with Google Apps for education.
   * What is important is that you have an attribute in your IdP that maps to the local part of the email address
   * at Google Apps. E.g. if your google account is foo.com, and you have a user with email john@foo.com, then you
   * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
  
  'google.com' => array(
    'AssertionConsumerService'   => 'https://www.google.com/a/g.feide.no/acs', 
    'spNameQualifier'            => 'google.com',
    'NameIDFormat'               => 'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
    'simplesaml.nameidattribute' => 'uid',
    'simplesaml.attributes'      => false
  );
  
  */
  // THIS FILE WRONG IFLE
 // BUGFIX: to use the proper ACS
 require_once('../../../config.php');
 $svars = get_config('auth/gsaml');
 
  
$metadata = array( 


  /*
   * This example shows an example config that works with Google Apps for education.
   * What is important is that you have an attribute in your IdP that maps to the local part of the email address
   * at Google Apps. E.g. if your google account is foo.com, and you have a user with email john@foo.com, then you
   * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
   */
  'google.com' => array(
    'AssertionConsumerService'   => 'https://www.google.com/a/'.$svars->domainname.'/acs', 
    'spNameQualifier'            => 'google.com', 
    'NameIDFormat'               => 'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
    'simplesaml.nameidattribute' => 'useridemail', // cstones a user in that domain ?@mroomsdev .com
    'simplesaml.attributes'      => false
  )
);
  
?>
