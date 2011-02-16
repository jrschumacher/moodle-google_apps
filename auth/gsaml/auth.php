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
 
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/weblib.php');

// include SAML for gsaml_send_auth_response
require_once($CFG->dirroot.'/auth/gsaml/samllib.php');


/**
 * SAML Authentication Plugin
 */
class auth_plugin_gsaml extends auth_plugin_base {
    
     
    /**
     * Constructor.
     */
    function auth_plugin_gsaml() {
         $this->authtype = 'gsaml';
         $this->loggedin = false;
         $this->config   = get_config('auth/gsaml');
    }

    // =============================================================
    
    /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     */
    function user_authenticated_hook(&$user, $username, $password) {
    	
       global $SESSION,$CFG,$_REQUEST;
       
       
       // Shouldn't need due to Gmail using OAuth 
       //
	   // TODO: IMPORTANT user_auth hook gets called for all plugins so
	   //       setting user to gsaml auth may override all moodle user auth plugins.
	   //       auth_gsaml still needs to run the update password code somehow.
	   //       if there was another way to test for it.... as compare if password is diff
	   //       and then set the google user to the new password. :/
	   //
//	   if( !set_field('user', 'auth', $this->authtype, 'id', $user->id)) {
//			error("could not set auth to gsaml");
//	   }
									
	   // Verify that user has a google account. If not create one for them.
       if (!file_exists($CFG->dirroot.'/blocks/gdata/gapps.php')) {
        	debugging('gdata block is not installed');
        	
        } else {
        	require_once($CFG->dirroot.'/blocks/gdata/gapps.php');
        	
        	try {
	        	$g = new blocks_gdata_gapps();
	        	
	        	try { 
	        		$g_user = $g->gapps_get_user($username);
	        		
	        		if (empty($g_user)) {
	        			/*
	        			 * MOODLE must enforce the above minium 6 char passwords!  
	        			 * http://www.google.com/support/a/bin/answer.py?answer=33386
	        			 */
	        			 
	        			 // Create Moodle User in the Gsync system
	        			 $g->moodle_create_user($user);
	        			 
	        			 // Create google user
	        			 $m_user = $g->moodle_get_user($user->id);
	        			 $g->create_user($m_user);
	        		}
	        		
	        	} catch (blocks_gdata_exception $e) {
                    // TODO: catch and inform of this common error
                    //if (stripos($e->getMessage(),'Error 1100: UserDeletedRecently') ) {
                    //    notice('Error 1100: UserDeletedRecently.<br/> Google does not allow a user to be created after deletion until at least 5 days have passed.');
                    //}
	        		debugging($e, DEBUG_DEVELOPER);
	        	}
	        	
  			} catch (blocks_gdata_exception $e) {
                //'Authentication with Google Apps failed. Please check your credentials. ->getMessage() ?
                // if Authentication with Google Apps failed. Please check your credentials.
                // print $e->getMessage();
                
                // TODO: catch and inform of this Error
                //if (stripos($e->getMessage(),'Error 1100: UserDeletedRecently') ) {
                //    notice('Error 1100: UserDeletedRecently.<br/> Google does not allow a user to be created after deletion until at least 5 days have passed.');
                //}
                
				debugging($e, DEBUG_DEVELOPER);
    		}
        }
        
        // No longer necessary due to new OAuth Support!!!
        // 
       	// Obtain and Store GMail feed while we have the password available
       	// Later we may store the password in a revertible encrypted format for
       	// GMail Block updating.
//       	if (!file_exists($CFG->dirroot.'/blocks/gmail/gmailfeedlib.php')) {
//			// TODO: Check gmailnotinstalled somewhere            
//			$SESSION->gmailnotinstalled = true;	
//            debugging('gmail block not installed', DEBUG_DEVELOPER);
//            
//       	} else {
//       	    //require_once($CFG->dirroot.'/blocks/gmail/gmailfeedlib.php');
//        	//set_gmail_feed($username,$this->config->domainname,$password); 
//       	}

        	 		
        // Debugging info added to moodle logs
//        if (debugging('', DEBUG_DEVELOPER)) {
//        	 	$module = "auth_saml"; // where were you
//        	 	$error = "sample error message";
//        	 	add_to_log(SITEID, $module, $error, '',$user->id, 0, $user->id);
//        }
     
           // We are Succesfully logged in and we have a SAML Request
           // So we want to process the rest of the log in and redirect
           // to the Service that the SAML Request is asking for.
           //
           // All this code essentialy makes up for the fact that
		   // we have to exit the login page prematurely.
	       if (isset($SESSION->samlrequest)) { 
	       	
	       		$SESSION->samlrequest = false;
				
		        if (!$user = get_record('user', 'username', $username, 'mnethostid', $CFG->mnet_localhost_id)) {
                   // User could not be logged in
                   error(get_string('errusernotloggedin','auth_gsaml'));
		        }
		        
		        if (!validate_internal_user_password($user, $password)) {
                    // Password not valid
                    error(get_string('pwdnotvalid','auth_gsaml')); 
		        }
		        
		        // Added to fix navigation
		        $navlinks = array(array('name' => 'test', 'link' => null, 'type' => 'misc'));
		        $navigation = build_navigation($navlinks);
		       		       
		        update_login_count();
		        
		        if ($user) {
		
		            // language setup
		            if ($user->username == 'guest') {
		                // no predefined language for guests - use existing session or default site lang
		                unset($user->lang);
		
		            } else if (!empty($user->lang)) {
		                // unset previous session language - use user preference instead
		                unset($SESSION->lang);
		            }
		
		            if (empty($user->confirmed)) {       // This account was never confirmed
		                print_header(get_string("mustconfirm"), get_string("mustconfirm") );
		                print_heading(get_string("mustconfirm"));
		                print_simple_box(get_string("emailconfirmsent", "", $user->email), "center");
		                print_footer();
		                die;
		            }
		
		            // TODO : Fix this bug frm isn't on this page here
		            if (isset($frm) ) { // if isset placed here for now
			            if ($frm->password == 'changeme') {
			                //force the change
			                set_user_preference('auth_forcepasswordchange', true, $user->id);
			            }
		            } // end of if issuet
		
		        	/// Let's get them all set up.
		            add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID,
		                       $user->id, 0, $user->id);
		                               
		            $USER = complete_user_login($user);
	
		        	/// Prepare redirection
		            if (user_not_fully_set_up($USER)) {
		                $urltogo = $CFG->wwwroot.'/user/edit.php';
		                // We don't delete $SESSION->wantsurl yet, so we get there later
		
		            } else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
		            	
		                $urltogo = $SESSION->wantsurl;    /// Because it's an address in this site
		                unset($SESSION->wantsurl);
		
		            } else {
		                // no wantsurl stored or external - go to homepage
		                $urltogo = $CFG->wwwroot.'/';
		                unset($SESSION->wantsurl);
		            }
		
		        	/// Go to my-moodle page instead of homepage if mymoodleredirect enabled
		            if (!has_capability('moodle/site:config',get_context_instance(CONTEXT_SYSTEM)) and !empty($CFG->mymoodleredirect) and !isguest()) {
		                if ($urltogo == $CFG->wwwroot or $urltogo == $CFG->wwwroot.'/' or $urltogo == $CFG->wwwroot.'/index.php') {
		                    $urltogo = $CFG->wwwroot.'/my/';
		                }
		            }
		
		
		        	/// check if user password has expired
		        	/// Currently supported only for ldap-authentication module
		            $userauth = get_auth_plugin($USER->auth);
		            if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
		                if ($userauth->can_change_password()) {
		                    $passwordchangeurl = $userauth->change_password_url();
		                } else {
		                    $passwordchangeurl = $CFG->httpswwwroot.'/login/change_password.php';
		                }
		                $days2expire = $userauth->password_expire($USER->username);
		                if (intval($days2expire) > 0 && intval($days2expire) < intval($userauth->config->expiration_warning)) {
		                    print_header("$site->fullname: $loginsite", "$site->fullname", $navigation, '', '', true, "<div class=\"langmenu\">$langmenu</div>");
		                    notice_yesno(get_string('auth_passwordwillexpire', 'auth', $days2expire), $passwordchangeurl, $urltogo);
		                    print_footer();
		                    exit;
		                } elseif (intval($days2expire) < 0 ) {
		                    print_header("$site->fullname: $loginsite", "$site->fullname", $navigation, '', '', true, "<div class=\"langmenu\">$langmenu</div>");
		                    notice_yesno(get_string('auth_passwordisexpired', 'auth'), $passwordchangeurl, $urltogo);
		                    print_footer();
		                    exit;
		                }
		            }
		
			        reset_login_count();

					// END of the regular Moodle Login Procedures
					
					// Process the SAML Request and redirect to the Service
					// it is asking for.
                    // This function should never return unless there's an error. 
					if (!gsaml_send_auth_response($SESSION->samlrequestdata)) {
                        // SAML code failed turn debugging on
                        error(get_string('samlcodefailed','auth_gsaml'));
                    }
	
		        
		        } else {
		            if (empty($errormsg)) {
		                $errormsg = get_string("invalidlogin");
		                $errorcode = 3;
		            }
		
		            // TODO: if the user failed to authenticate, check if the username corresponds to a remote mnet user
		            if ( !empty($CFG->mnet_dispatcher_mode)
		                 && $CFG->mnet_dispatcher_mode === 'strict'
		                 && is_enabled_auth('mnet')) {
		                $errormsg .= get_string('loginlinkmnetuser', 'mnet', "mnet_email.php?u=$frm->username");
		            }
		        }

		} // else if NO SAML request is made we don't do anything but log in normally
    }

    
    /**
     * Perform a Google SAML Logout by visiting a page on logout
     */
    function logoutpage_hook() {
		require_logout();

        // TODO: if the Google SAML SSO Link Failed don't bother redirecting

		// Google doesn't have an SSO logout procedure as far as I know right now.
		// So we visit this and it logs us out of all of the google's services
		redirect('https://mail.google.com/a/'.$this->config->domainname.'/?logout');
    }
    
    
    /**
     * Check for a SAML Request if you find one and you are logged in
     * send a SAML Reply. Else continue with the login
     */
    function loginpage_hook() {
	        global $frm;  // can be used to override submitted login form
	        global $user; // can be used to replace authenticate_user_login()
	        global $SESSION,$CFG;
	        
	        // Store SAMLRequest for processing upon user auth
	        if( !empty($_REQUEST['SAMLRequest']) ) {
	        	// Case 2: if we aren't logged in we need this SAMl request for later
	        	// store it in session and invoke upon auth hook
	        	$SESSION->samlrequestdata = $_REQUEST['SAMLRequest'];
	        	$SESSION->samlrelaystate = $_REQUEST['RelayState'];
	        	$SESSION->samlrequest = true;
	        }
	        
	        // Case 1: if your logged in already and the SAML request just needs to 
	        // be processed go ahead and redirect with authentication.
	        if( isloggedin() and !is_null($_REQUEST['SAMLRequest']) ) { 
	            $SESSION->samlrequestdata = $_REQUEST['SAMLRequest'];
	        	$SESSION->samlrelaystate = $_REQUEST['RelayState'];
                
                if (!gsaml_send_auth_response($_REQUEST['SAMLRequest'])) {
                    // Saml auth code failed
                    notice(get_string('samlauthcodefailed','auth_gsaml'),$CFG->wwwroot);
                }
	        }
	}

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) { // therefore leave this code as is
        global $CFG;
        // TODO: might set user->auth to gsaml here :/
        if ($user = get_record('user', 'username', $username, 'mnethostid', $CFG->mnet_localhost_id)) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object  (with system magic quotes)
     * @param  string  $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        global $CFG,$FULLME;
        
    	// Enforce 6 char min google password rules.
        // TODO: fix error where page jumps to some random other page
        // Site Administration > Security > Site policies Dang
        if( strlen($newpassword) < 6 ) {
            //helpbutton inside of the notice ?
            $sixchar_msg = get_string('sixcharmsg','auth_gsaml');
            $link = $FULLME;
            notice($sixchar_msg,$link);
        }
        
        // TODO: if moodle user is not the same as google user
        //       use the mapping IF we go that route
        
    	// Check and update on the moodle side
    	$user = get_complete_user_data('id', $user->id);
    	if (!update_internal_user_password($user, $newpassword) ) {
    		return false;
    	}
    	
        // if the user isn't synced or google sync fails
        // moodles password will be the new one but google will still
        // think it is the old one.
        
        
        
        // Basically we need OAuth for the GMail to make this code work
        // smoothly. Since there will no longer be  arequiremnet to keep the google and moodle
        // passwords the same.
        // 
        // Assuming the user is synced so that this code has relevants
        // Choices.. if there is an error forgive it and change the moodle code anyway.
        // their gmail block would break but if we used OAuth for it someday it would
        // work anyway.
        //
        // perhaps resync the accounts later?
        //
        // Need to know if user is synced or not?
        // 

        // 
//        require_once($CFG->dirroot.'/blocks/gdata/gapps.php');
//        
//    	// Moodle Password change clears now adjust google account
//        try {
//        	$g = new blocks_gdata_gapps();
//    		$m_user = $g->moodle_get_user($user->id);
//        	$g_user = $g->gapps_get_user($user->username);        	
//        	$g->sync_moodle_user_to_gapps($m_user, $g_user,false);
//        	
//        } catch (blocks_gdata_exception $e) {
//            // we can now have google and moodle passwords be different and
//            // Gmail will still work so we can forgive this error
//        	debugging($e, DEBUG_DEVELOPER);
//        	return false;
//        }
        
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include 'settings.php';
    }


// TODO: better intergrate with this.
   /**
     * Confirm the new user as registered. This should normally not be used,
     * but it may be necessary if the user auth_method is changed to manual 
     * before the user is confirmed.
     */
    function user_confirm($username, $confirmsecret = null) { 
    	
    	// TODO: Check for google account too??
    	       
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else { 
                if (!set_field("user", "confirmed", 1, "id", $user->id)) {
                    return AUTH_CONFIRM_FAIL;
                }
                if (!set_field("user", "firstaccess", time(), "id", $user->id)) {
                    return AUTH_CONFIRM_FAIL;
                }
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }
    
     function get_description() {
        $authdescription = get_string("auth_{$this->authtype}description", "auth");
        if ($authdescription == "[[auth_{$this->authtype}description]]") {
            $authdescription = get_string("auth_{$this->authtype}description", "auth_{$this->authtype}");
        }
        return $authdescription;
    }
}

?>