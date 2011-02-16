<?php

/**
 * Obtains the access token after the user grants access
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

require_once $CFG->dirroot.'/blocks/gmail/library/OAuthRequester.php';
require_once $CFG->dirroot.'/blocks/gmail/library/OAuthException2.php';

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        return array();
    }
}

$consumer_key = required_param('consumer_key');
$oauth_token  = required_param('oauth_token');
$user_id      = required_param('usr_id');

$store  = OAuthStore::instance('Google');

try {
    OAuthRequester::requestAccessToken($consumer_key, $oauth_token, $user_id);
    // The token was succefully authorized and the gmail block will now be able
    // to show you your emails
    $message='The OAuth token was successfully authorized.';// TODO: send where to redirect back to;
    // THE CALLBACK should have been passed data to return to where you were
    $url = $CFG->wwwroot; // temporary location 
    redirect($url, $message, $delay=3);
} catch (OAuthException2 $e) {
    $message = 'The OAuth token was not able to authorize. ';
    $message .= "Due to ".$e->getMessage();
    //notice($message, $link=$CFG->wwwroot);
}

?>