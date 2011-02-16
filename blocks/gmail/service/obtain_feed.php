<?php

/**
 * oauth3 lib functions
 */
// if the token doesn't work we need to display that grant access link or error
//require_once('../../../../config.php');
global $CFG;

require_once $CFG->dirroot.'/blocks/gmail/library/OAuthRequester.php';
require_once $CFG->dirroot.'/blocks/gmail/library/OAuthException2.php';

// require_once dirname(__FILE__)
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        return array();
    }
}


// Allow for the nounce to access this even storing an access timestamp and comparing it
//require_login();
//global $USER;

//$user_id = required_param('uid', PARAM_INT); // included for testing
//$request_uri = required_param('request_uri', PARAM_URL);

//if ( $USER->id != $user_id ) {
//    print "false";
//    print "Someone who is not the presently logged in user is trying to access the feed.";
//    exit();
//}

/**
 * Returns the atom feed only requires oauth3/OAuthRequester.php
 * TODO: will need to throw execptions for if user has no token, or if token no longer works... :/
 */
function oauth3_obtain_feed($user_id,$request_uri='https://mail.google.com/mail/feed/atom') {
    // Do we have a token for this user???
    // if not return error print "no token found for" exit();
    // if this is a curl call you can't use global user here
    //$user_id= 5;
    //$request_uri = 'https://mail.google.com/mail/feed/atom';
    
    try {
        $store  = OAuthStore::instance('Google');
        $req = new OAuthRequester($request_uri,'GET', $params=null);
        $result = $req->doRequest($user_id); 
        // $result is an array of the form: array ('code'=>int, 'headers'=>array(), 'body'=>string)
        $feed = $result['body'];
    } catch (OAuthException2 $e) {
        if (debugging('',DEBUG_DEVELOPER)) {
          print 'oauth3_obtain_feed error: '.$e->getMessage(); //  notice( "Error: ".$e->getMessage());
        }
    }
    
    // TODO: how to return whatever error it says 
    // should return feed body Output while still testing
    if ( empty($feed) or !empty($e) ) {
        return "FALSE Error Message: $e";
        // print "reasons for false or error info"; // or just log the error info
    } else {
        return $feed;
    }
}

// test
//print oauth3_obtain_feed(5);//$request_uri='https://mail.google.com/mail/feed/atom');

?>
