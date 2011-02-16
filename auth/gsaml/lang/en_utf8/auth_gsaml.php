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
$string['domainname']             = 'Domain';
$string['auth_gsamldescription'] = 'This auth plugin enables Moodle to Single Sign on with SAML SPs.';
$string['auth_gsamltitle']       = 'Google Authentication'; // needs a change of some sort
$string['cert']                   = 'Certificate';
$string['key']                    = 'RSA Key';

// for auth/gsaml/settings.php 
$string['domainnamestr'] = 'Domain Name';
$string['rsakeystr'] = 'RSA Key File';
$string['desckeystr'] = 'Upload the RSA key (pem) Note that the moodle saml service supports RSA signed keys only.';
$string['googauthconfstr'] = 'Google Authenticaiton Configuration';
$string['ssl_str'] = 'SSL Signing Certificate';
$string['desc_certstr'] = 'Upload the X.509 Certificate. Note that this is the same file you will upload to google as well.';
$string['setupinstrctstr'] = 'Set Up Instructions ';
$string['mgadgetstr'] = 'Moodle Gadget ';
$string['mgadgethelp'] = 'Moodle Gadget Help';
$string['googdiag'] = 'Google Intergration Diagnostics';
$string['googdebugopts'] = 'Once you are done configuring you may relogin in and visit ';
$string['thediagnosticspage'] = 'The Diagnostics Page';

// upload_key
$string['notadminnopermin'] = 'You are not an administrator. You do not have permission to view these settings.';
$string['nokeyuploaded'] = 'No Key was uploaded';
$string['filesnotsaved'] = 'Files did not save.';
$string['keypathnotsaved'] = 'Key path not saved.';
$string['uploadkeystr'] = 'Upload the key';
$string['uploadkey'] = 'UploadKey';
$string['uploadstr'] = 'Upload';
$string['uploadthekey'] = 'Upload Key';

$string['gadgetinfostr'] = 'Use the following URL to add the Moodle Gadget to your Google Start Page<br/><b>$a->wwwroot/auth/gsaml/moodlegadget.php</b>';
$string['lnktogoogsettings'] = 'Link to Google Settings';
$string['nodomainyet'] = 'No Domain Set Yet'; 
$string['gsamlsetuptableinfo'] = '<ol><li>Set the <b>Domain Name</b> to your google service domain name then click <b>Save Changes</b><br/><br/></li>
<li>In a new window open Google Apps Control Panel page as admin (<a href=\"https://www.google.com/a/$a->domainname\">$a->googsettings</a>)<br/><br/></li>
<li>Click the <b>Advanced tools</b> tab.<br/><br/></li>
<li>Click the <b>Set up single sign-on (SSO)</b> link next to Authentication.<br/><br/></li>
<li>First check the <b>Enable Single Sign-on</b> box.<br/><br/></li>
<li>Now insert this url into the <b>Sign-in page URL</b> text field.<br/><b>$a->wwwroot/login/index.php</b><br/><br/></li>
<li>Insert this url into the <b>Sign-out page URL</b> text field.<br/><b>$a->wwwroot/login/logout.php</b><br/><br/></li>
<li>Insert this url into the <b>Change password URL</b> text field.<br/><b>$a->wwwroot/login/change_password.php</b><br/><br/></li>
<li>Generate and upload a <b>Verification certificate to Google (X.509 certificate containing the public key)</b><br/><br/></li>
<li>Upload the privatekey and certificate to Moodle as well and then click <b>Save Changes</b></b><br/></li></ol>';

// Moodle Gadget
$string['tomoodle'] = 'To Moodle';


// errors
$string['errusernotloggedin'] = 'User could not be logged in';
$string['pwdnotvalid'] = 'Password not valid';
$string['samlcodefailed'] = 'SAML Code Failed turn debugging on to find out why';
$string['samlauthcodefailed'] = 'SAML Auth Code Failed turn debugging on for more information';
$string['sixcharmsg'] = 'User Password Must be longer than 6 characters for Google Intergration. Tell your Admin to adjust the site policy settings';
 
 
// diagnostics
$string['googsamldiag'] = 'Google SAML Diagnostics';
$string['notadminnoperm'] = 'You are not an Site Admin. You do not have permission to view this information';
$string['gdatanotconfig'] = 'gdata configuration table not set.';
$string['googlesamlconfigdata'] = 'Google SAML Configuration Data';
$string['gsamlconfignotset'] = 'Google SAML configuration has not yet been set';
$string['gdataconfignotset'] = 'gdata config table not set';
$string['gdataconfig'] = 'GData Configuration';
$string['gmailconfig'] = 'GMail Configuration';
$string['componentinstallcheck'] = 'Component Install Precheck';
$string['gdatanotinstalled'] = 'gdata block is not installed\n';
$string['gappsblockinstalled'] = 'GApps Block installed\n';
$string['gmailblocknotinstalled'] = 'gmail block is not installed';
$string['gmailblockinstalled'] = 'GMail Block installed\n';
$string['gdataapitestresults'] = 'GData API Test Results';
$string['trytoinitgdataconnection'] = 'Trying to init a gdata to google connection<br/>';
$string['gsamlsuccess'] = 'Success';
$string['gmailtestresults'] = 'GMail Test Results';
$string['gmailtestwillnotrun'] = 'GMail Test will not run unless Moodle is in DEBUG_DEVELOPER Mode';
$string['obtainemailfeed'] = 'Obtaining email feed for username: ';


?>
