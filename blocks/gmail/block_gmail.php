<?php
/**
 * GMail Block Beta 2
 * Oct. 16, 2008
 * MoodleRooms Inc.
 * 
 * PREREQ: Requires the G Saml auth plugin to obtain domain name
 * PREREQ: Requires curl be installed on server
 * 
 * @author Chris Stones
 * @version $Id$
 * @package block_gmail
 * */
 /*
  0. The Gmail block will display the most recent emails from Gmail within 
     Moodle with the following informaiton:
  1. Gmail's email chain information CAN'T GET INFO FROM FEED
  2. The email's Subject
  3. The email's Arrival Date
  5. The Gmail block will display a link to the user's Gmail email
  7. The Gmail block will display a link to Compose a new email in Gmail
  8. The Gmail block will verify that a 
     Gmail account exists for the user before displaying their email.
  9. The Gmail block will call the Gmail account creation process 
     in the GMail Batch Account library if a Gmail account doesn't exist.
  */

global $CFG,$USER;

class block_gmail extends block_list {

 	var $domain;
    var $oauthsecret;
    var $msgnumber;
    
    function init() {
        $this->title = get_string('blockname', 'block_gmail');
        $this->version = 2009071901;
    }
    
   
    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'my' => true);
    }
    
    
    function has_config() {
        return true;
    }
    
    
    function get_content() {
            global $SESSION,$CFG,$USER; // $DB
        
            // quick and simple way to prevent block from showing up on front page
            if (!isloggedin()) {
                    $this->content = NULL;
                    return $this->content;
            }

            if ($this->content !== NULL) {
                    return $this->content;
            }

            $this->content = new stdClass;
            $this->content->items = array();
            $this->content->icons = array();
            $this->content->footer = '';//'<span class="notifytiny">('.get_string('refreshtoken','block_gmail').')</span>';


            if ( !$domain = get_config('blocks/gmail','consumer_key')) {
                $this->content->items[] = "Domain not set.";
                return $this->content;
            }
            
            if( !$this->oauthsecret = get_config('blocks/gmail','oauthsecret') ) {
                $this->content->items = array(get_string('missingoauthkey','block_gmail'));
                $this->content->icons = array();
                return $this->content;
            }

            
            require_once($CFG->dirroot.'/blocks/gmail/service/obtain_feed.php');
            $feederror = false; // be optimisic
            
            
            // Obtain gmail feed data
            try {
                $feeddata = oauth3_obtain_feed($USER->id);
            } catch (OAuthException2 $e) {
                // simple error for user then when you turn on debugging you see the rest of the message
                if (debugging('',DEBUG_DEVELOPER) ) {
                    $this->content->items[] = "Error: Feed could not be obtained. ".$e->getMessage();
                    return $this->content;
                } else {
                    $this->content->items[] = get_string('sorrycannotgetmail','block_gmail');
                    return $this->content;
                }
            }

            // Identify Type of Error and handle
            // These use error message strings not moodle strings because they are always in code only
            if( empty($feeddata) or strpos($feeddata,'User not in token table.')
                                 or strpos($feeddata,'User has no token.')
                                 or strpos($feeddata,'Error 401')
                                 or strpos($feeddata,'Unauthorized')) {
                $feederror = true;

                if (debugging('', DEBUG_DEVELOPER)) {
                    $this->content->items[] = 'DEBUG_DEVELOPER is ON: Showing error data <br/>'. $feeddata;
                }

                $req_token_link = $CFG->wwwroot.'/blocks/gmail/service/request_token.php';

                //$hbutton   = helpbutton('grantaccess', 'grantaccess', 'block_gmail', true, false, '', true, '');
                // helpbutton What does Grant access mean?
                $grantacces = get_string('grantaccesstoinbox','block_gmail');
                $this->content->items[] = '<a href="'.$req_token_link.'">'.$grantacces.'</a> ';//.$hbutton;
                return $this->content;
            }
            
            //} // END of 3 legged or 2 legged Toggle


            // TODO: Revoke Token option link
            // If no error parse messages and process for display
            if (!$feederror) {


                if ($USER->id !== 0) {
                    // simplepie lib breaks if included on top level only include when necessary
                    require_once($CFG->dirroot.'/blocks/gmail/simplepie/simplepie.inc');
		}

                // Parse google atom feed
                $feed = new SimplePie(); 
                $feed->set_raw_data($feeddata);
                $status = $feed->init();
                $msgs = $feed->get_items();
               

                $unreadmsgsstr = get_string('unreadmsgs','block_gmail');
                $composestr    = get_string('compose','block_gmail');
                $inboxstr      = get_string('inbox','block_gmail');
                
                // Obtain link option
                $newwinlnk = get_config('blocks/gmail','newwinlink');
                
                $composelink = '<a '.(($newwinlnk)?'target="_new"':'').' href="'.'http://mail.google.com/a/'.$domain.'/?AuthEventSource=SSO#compose">'.$composestr.'</a>';
                $inboxlink = '<a '.(($newwinlnk)?'target="_new"':'').' href="'.'http://mail.google.com/a/'.$domain.'">'.$inboxstr.'</a>';
                
                $this->content->items[] = $inboxlink.' '.$composelink.' '.$unreadmsgsstr.'('.count($msgs).')<br/>';
            
                // Main Mail Icon
                $this->content->icons[] = "<img src=\"$CFG->wwwroot/blocks/gmail/imgs/gmail.png\" alt=\"message\" />";          
    		    // Only show as many messages as specified in config
    		    $countmsg = true;
    		    if( !$msgnumber = get_config('blocks/gmail','msgnumber')) {
    		    	// 0 msg means as many as you want.
    		    	$countmsg = false;
    		    }
    		    $mc = 0;
    		    foreach( $msgs as $msg) {
    		    	
    		    	if($countmsg and $mc == $msgnumber){
    		    		break;
    		    	}
    		    	$mc++;
    		    	
    		    	// Displaying Message Data
    		    	$author = $msg->get_author(); 
    		    	$author->get_name();
    		    	$summary = $msg->get_description();
    		    		
    				// Google partners need a special gmail url
    			    $servicelink = $msg->get_link();
    		    	$servicelink = str_replace('http://mail.google.com/mail','http://mail.google.com/a/'.$domain,$servicelink); 
    		    	
                    
    		    	// To Save Space given them option to show first and last or just last name
                    @list($author_first,$author_last) = split(" ",$author->get_name());
                    
                    // Show first Name
                    if( !$showfirstname = get_config('blocks/gmail','showfirstname')) {
                        $author_first = '';
                    }
                    
                    // Show last Name
                    if( !$showlastname = get_config('blocks/gmail','showlastname')) {
                        $author_last = '';
                    }
                
                    // I should do clean_param($summary, PARAM_TEXT) But then ' will have \' 
                    if ($newwinlnk) {
                        $text  = ' <a target="_new" title="'.format_string($summary);
                        $text .= '" href="'.$servicelink.'">'.format_string($msg->get_title()).'</a> '.$author_first.' '.$author_last;
                        
                        $this->content->items[] = $text;
                    } else {
    		    	    $text  = ' <a title="'.format_string($summary);
                        $text .= '" href="'.$servicelink.'">'.format_string($msg->get_title()).'</a> '.$author_first.' '.$author_last;
                        $this->content->items[]  = $text;
                    }
                    
    		    	// May use message icons, for now a simple dash
    		    	$this->content->icons[] = '-';
                   
    		    }
            }
                    $req_token_link = $CFG->wwwroot.'/blocks/gmail/service/request_token.php';
                    $this->content->footer = '<span class="notifytiny">('.'<a href="'.$req_token_link.'">'.get_string('refreshtoken','block_gmail').'</a>'.')</span>';
		    return $this->content;
	   // }
	}
    

    /**
     * This function uses 2 Legged OAuth to return the atom feed for 
     * the users Gmail. 
     */

//    function obtain_gmail_feed() {
//        global $USER;
//        // http://code.google.com/p/oauth/
//        // under Apache License, Version 2.0
//        // http://www.apache.org/licenses/GPL-compatibility.html (some dispute if not GPL 3)
//        // Moodle can be GPL 3 at your option
//
//        require_once('OAuth.php');
//        $consumer  = new OAuthConsumer($this->domain, $this->oauthsecret, NULL);
//        $user      = "$USER->username@$this->domain";
//        $feed      = 'https://mail.google.com/mail/feed/atom';
//        $params    = array('xoauth_requestor_id' => $user);
//        $request   = OAuthRequest::from_consumer_and_token($consumer, NULL, 'GET', $feed, $params);
//
//        // TODO: Debug now pass it the string from teh PEM key that must also be on google
//        // upload one to gmail and to the google OAuth settings place
//        // seems to need new OAuthSignatureMethod_RSA_SHA1() rather .... to run the privkey code
//        // old new OAuthSignatureMethod_HMAC_SHA1()
//        $privkey = NULL; // for now
//        $request->sign_request(new OAuthSignatureMethod_RSA_SHA1(), $consumer, NULL,$privkey);
//
//        // URL Encode the the params
//        $url = $feed.'?xoauth_requestor_id='.urlencode($user);
//
//        // Check if curl is installed?
//
//        // Perform a GET to obtain the feed
//        $curl = curl_init($url);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_FAILONERROR, false);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        // Stream of OAuth Header params
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array($request->to_header()));
//
//        if (!$feeddata = curl_exec($curl)) {
//            // Prevent Users from seeing the really nasty errors unless thye are developers
//            $feederror = curl_error($curl);
//            debugging('Gmail feed failed with: '.$feederror, DEBUG_DEVELOPER);
//            $feeddata = '';
//        }
//        // There's no feed error when
//        /*<HTML>
//          <HEAD>
//          <TITLE>Unauthorized</TITLE>
//          </HEAD>
//          <BODY BGCOLOR="#FFFFFF" TEXT="#000000">
//          <H1>Unauthorized</H1>
//          <H2>Error 401</H2>
//          </BODY>
//          </HTML>
//        */
//
//        curl_close($curl);
//        return $feeddata;
//    }

 }

?>
