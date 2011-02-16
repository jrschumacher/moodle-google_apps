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
 * Google Integration Diagnostics Page
- check setup gmail gdata, gsaml etc
- enable saml error reporting
- view relevat logs of activity

- RUN OAuth check

- RUN google gmail feed check

- RUN SAML auth and response check (unwritten)
- VIEW Sample XML output and requests
- Collect a SAML Request and Auth Response Example
- use this data to confirm what they want is what you are giving them and what you have is
  what they need.

-- running the sync users cron job with out the rest of the cron job running
-- to sync users immedialy

-- link to the google error codes

- Cert expiration date 
would be nice to find a way to test teh certifcate and the keys

 */
 
require_once('../../config.php');
global $USER,$CFG;

require_login();

function gsaml_print_config_table($heading,$table_obj) {
    print_heading($heading);
    $conf_table = new object;
    $conf_table->head  = array('Setting','Value');
    $conf_table->align = array('left','left');
    $conf_table->data  = array();

    foreach( $table_obj as $setting => $value ) {
        $conf_table->data[] = array($setting,$value);
    }
    print_table($conf_table);
}  

$strcurrentrelease = get_string('googsamldiag','auth_gsaml');
$navigation = build_navigation(array(array('name'=>$strcurrentrelease, 'link'=>null, 'type'=>'misc')));
print_header($strcurrentrelease, $strcurrentrelease, $navigation, "", "", false, "&nbsp;", "&nbsp;");

if (!is_siteadmin($USER->id)) {
    // You are not a site admin no permission to view page
    notice(get_string('notadminnoperm','auth_gsaml'),$CFG->wwwroot);
} else {

// help button for placement later 
$hbutton = helpbutton('diagnostics', $title='Google Intergration Diagnostics','auth_gsaml', true, false, $text='', true, '');


    // Let's make sure we can see our config settings when checking
    // GSaml Authentication SSO Settings
    if(!$gsaml_conf = get_config('auth/gsaml')) {
        $msg = get_string('gsamlconfignotset','auth_gsaml'); 
    }
    gsaml_print_config_table(get_string('googlesamlconfigdata','auth_gsaml'),$gsaml_conf);

    // GData API's
    if(!$gdata_conf = get_config('blocks/gdata')) {
        print_string('gdataconfignotset','auth_gsaml'); 
    }
    gsaml_print_config_table(get_string('gdataconfig','auth_gsaml'),$gdata_conf);

    // GMail configurations
    if(!$gmail_conf = get_config('blocks/gmail')) {
        print_string('gdatanotconfig','auth_gsaml');
    }
    gsaml_print_config_table(get_string('gmailconfig','auth_gsaml'),$gmail_conf);
          
 
    // Verify that the Google/Moodle Components are fully installed in the 
    // correct locations
    print_heading(get_string('componentinstallcheck','auth_gsaml')); 
    $components = '<pre>';
    if (!file_exists($CFG->dirroot.'/blocks/gdata/gapps.php')) {
         $components .= get_string('gdatanotinstalled','auth_gsaml');
         // don't run the tests related to this lib
    } else {
        $components .= get_string('gappsblockinstalled','auth_gsaml');
        require_once($CFG->dirroot.'/blocks/gdata/gapps.php');
    }
    
    if (!file_exists($CFG->dirroot.'/blocks/gmail/block_gmail.php')) {
         $components .= get_string('gmailblocknotinstalled','auth_gsaml');
         // don't run the tests related to gmail block
    } else {
        $components .= get_string('gmailblockinstalled','auth_gsaml');
        require_once($CFG->dirroot.'/blocks/gmail/gmailfeedlib.php');
        
        require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
        require_once($CFG->dirroot.'/blocks/gmail/block_gmail.php');
    }
    print_box($components); 
     
    print_heading(get_string('gdataapitestresults','auth_gsaml'));
        
    // Individual Try blocks for hunting problems
    $result = "<pre>";
    try {
        $result .= get_string('trytoinitgdataconnection','auth_gsaml');
        $g = new blocks_gdata_gapps();
        $result .= get_string('gsamlsuccess','auth_gsaml');
    } catch (blocks_gdata_exception $e) {
       $result .= '<b>'.$e->getMessage().'</b><br/>'.$e.'</pre>'; // show the < > brackets and format
    }  
    print "</pre>";
    print_box($result,'generalbox','',false); 
    
    
    // Create moodle user tests ?
    
    // Sync User Tests ?
    
    // GMail Pull feed for selected User
    // User must have an account
    
//    print_heading(get_string('gmailtestresults','auth_gsaml'));
//    if (!debugging('',DEBUG_DEVELOPER) ) {
//        $result = get_string('gmailtestwillnotrun','auth_gsaml'); 
//        $result = "test being rewriten";
//    } else {
//        $result = '<pre>'.get_string('obtainemailfeed','auth_gsaml').$USER->username."</pre><br/>";
//        // Assume admin for now with DEBUG_DEV on the password is stored
//        // TODO: Later CreateAUser And Use for the test
//        $result .= 'Test being rewritten';
//        //set_gmail_feed($USER->username,$gsaml_conf->domainname,$SESSION->gmailfeedpw); 
//        $result .=  '<pre class="notifytiny">' . htmlspecialchars(print_r(base64_decode($SESSION->gmailfeed),true)) . '</pre>';
//    }
//    print_box($result); 
    
// print_heading("SAML SSO Connection Test Results");
// require_once($CFG->dirroot.'/auth/gsaml/samllib.php');
    
// This func will usually exit if there is no Error so
// we need to mod the function a bit in order to obtain
// test data
    
// attempt the function on a simulated request
//    global $SESSION;
//    $SESSION->samlrequestdata = "fVLfT9swEH6fxP9g%2Bb1JUyZUWU1QB0KrBFtEAw97c%2B1LY2b7Mp%2BTbv%2F93BQEPIDkF393%2Fn6cb3X511k2QiCDvuRFNucMvEJt%2FL7kD83NbMkvq7MvK5LO9mI9xM7fw58BKLL00pOYCiUfghcoyZDw0gGJqMR2fXcrFtlc9AEjKrScba5Lvmttq7HrO292xnsNzmjdyt87sEpa2XamfQKPT5w9vthaHG1tiAbYeIrSxwTN58tZUaTTFEvx9UIszn9xVj8rfTP%2BlOAzW7tTE4nvTVPP6p%2FbZiIYjYbwI3WXfI%2B4t5ApdEf5WhKZMcGttAScrYkgxGTwCj0NDsIWwmgUPNzflryLsSeR54fDIXulyWXuAqIjDePprohX03DFlC%2B8mern7uWLOq9e%2BVf5G6rq%2BdOOWTbXNVqj%2FrG1tXi4CiBjChLDkHLcYHAyfqxWZMWEGD1rp1YxeOpBmdaA5iyvTqrvtyPtzH8%3D"; // pre captured data
//    $SESSION->samlrelaystate = "https%3A%2F%2Fwww.google.com%2Fa%2Fmroomsdev.com%2FServiceLogin%3Fservice%3Dmail%26passive%3Dtrue%26rm%3Dfalse%26continue%3Dhttp%253A%252F%252Fmail.google.com%252Fa%252Fmroomsdev.com%252F%26bsv%3D1k96igf4806cy%26ltmpl%3Ddefault%26ltmplcache%3D2";
//    try {
//        gsaml_send_auth_response($SESSION->samlrequestdata);
//        
//    } catch (Exception $exception) {
//        //print_object($exception);
//        $result = '<pre class="notifytiny">' . htmlspecialchars(print_r($exception,true)) . '</pre>';
//    }
//    print_box($result="No Test until curl test case is written", $classes='generalbox', $ids='', $return=false);    
    
}

print_footer();
 
?>
