<?php

/**
 * This is the sample google OAuth Demo Code
 * @author Chris B Stones based off Marc Worrell's code
 * 
 * The MIT License
 * 
 * Copyright (c) 2007-2008 Mediamatic Lab
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */




require_once('../../../config.php');
require_login();
global $USER,$CFG;


// If we are Refreshing Access Token We will Delete the token then
// continue.
$refresh = optional_param('id', 0, PARAM_INT);
if ( $refresh == 1) {
    // Delete Token
    if( !$result = delete_records('block_gmail_oauth_consumer_token','user_id',$USER->id) ) {
        redirect($CFG->wwwroot,get_string('refreshfailed','block_gmail'),3);
    }
}

require_once $CFG->dirroot.'/blocks/gmail/library/OAuthRequester.php';
require_once $CFG->dirroot.'/blocks/gmail/library/OAuthException2.php';

if (!function_exists('getallheaders'))
{
	function getallheaders()
	{
		return array();
	}
}


$req_token_link = $CFG->wwwroot.'/blocks/gmail/service/request_token.php';

$store  = OAuthStore::instance('Google');

$user_id = $USER->id; // most often the current user

// We must check if this user has a authorized token
// if they do not we need a link that says "grant access to my gmail"

// TODO:invalid old token link
// NOTE: if they need to revoke token or if current token doesn't work this link should
//       show up.

$consumer_key = get_config('blocks/gmail','consumer_key'); // the google apps domain
$params = array('scope'=>'https://mail.google.com/mail/feed/atom');


// Disable SSL security for debugging only
$curlopts = array();
if ( debugging('',DEBUG_DEVELOPER) ) {
    $curlopts = array('CURLOPT_SSL_VERIFYHOST' => FALSE,
                      'CURLOPT_SSL_VERIFYPEER' => FALSE,
                      'CURLOPT_FTP_SSL' => 'CURLFTPSSL_TRY' );
}

try {
    $token = OAuthRequester::requestRequestToken($consumer_key, $user_id, $params, 'POST', array(), $curlopts );
} catch (OAuthException2 $e) {

    if ($e->getMessage() == 'User not in token table.') {
        // return the grant access link since user doesn't even have an entry in the tokens table
       // print 'User has no token.';
    }
    notice('Error: '.$e->getMessage() );
}

// Call back location after getting the first token
$callback_uri = $CFG->wwwroot.'/blocks/gmail/service/obtain_auth.php?consumer_key='.rawurlencode($consumer_key).'&usr_id='.intval($user_id);

// Now redirect to the autorization uri and get us authorized
if (!empty($token['authorize_uri']))
{
	// Redirect to the server, add a callback to our server
	if (strpos($token['authorize_uri'], '?')) {
		$uri = $token['authorize_uri'] . '&'; 
	} else {
		$uri = $token['authorize_uri'] . '?'; 
	}
    // WARNING: google specific code
    // hd parameter tells us we want access to THIS domain's email
	$uri .= 'oauth_token='.rawurlencode($token['token']).'&oauth_callback='.rawurlencode($callback_uri).'&hd='.$consumer_key;
} else {
	// No authorization uri, assume we are authorized, exchange request token for access token
	$uri = $callback_uri . '&oauth_token='.rawurlencode($token['token']).'&hd='.$consumer_key;
}

header('Location: '.$uri);
exit();

// Access will resume when we get back to the obtain_auth.php page

// The call back functions obtain the access token that we keep for later
// (make sure teh time on the expriation is a long one and that we can renew
//  gracefully)

?>
