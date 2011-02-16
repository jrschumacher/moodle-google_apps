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
 * Upload Key Page (for upload_form) file
 * 
 * Note: if we rewrite this to include both the key and a destination 
 *       we can make a general purpose uploader. But I'd rather submit a patch
 *       to moodle to add an admin_setting_configfile subclass
 * 
 * @author Chris Stones
 * @version $Id$
 * @package auth_gsaml
 */
 
require_once('../../config.php');
require_once('upload_form.php');

require_login();

$strcurrentrelease = get_string('uploadthekey','auth_gsaml');
$navigation = build_navigation(array(array('name'=>$strcurrentrelease, 'link'=>null, 'type'=>'misc')));
print_header($strcurrentrelease, $strcurrentrelease, $navigation, "", "", false, "&nbsp;", "&nbsp;");

if (!is_siteadmin($USER->id)) {
    $notadminnopermin = get_string('notadminnopermin','auth_gsaml');
    notice($notadminnopermin,$CFG->wwwroot);
    close_window_button();
    $CFG->docroot = '';   // We don't want a doc link here
    print_footer($SITE);
    die;
}

// The key param sets the uploaders filename param
// Check to make sure it's only certificate or privatekey
$key = required_param('key', PARAM_TEXT); 
if ($key !== 'certificate' and $key !== 'privatekey') {
    notify("Don't be evil.");
    print $key;
    close_window_button();
    $CFG->docroot = '';   // We don't want a doc link here
    print_footer($SITE);
    die;
}

$mform = new gsaml_upload_form($key); 

if ($mform->is_cancelled()){
    notify(get_string('nokeyuploaded','auth_gsaml'));
    close_window_button();
    $CFG->docroot = '';   // We don't want a doc link here
    print_footer($SITE);
    
} else if ($fromform = $mform->get_data()){
    // Data is validate so Save files to destination
    if (!$mform->save_files('samlkeys') ) {
        notify(get_string('filesnotsaved','auth_gsaml'));
    } else {
        // On Upload success save the path info to auth/gsaml config table
        $uploader = $mform->_upload_manager->files[$key];
        if ($uploader['error'] == 0) {
            $path = $uploader['fullpath'];
        }
        
        if (!set_config($key,$path,'auth/gsaml') ) {
            notify(get_string('keypathnotsaved','auth_gsaml'));
        } else {
            notify("$key saved as ".basename($path),'notifysuccess');
        }
    }
    
    close_window_button();
    $CFG->docroot = '';   
    print_footer($SITE);

} else {
    // Status of current file information
    $gsamlvars = get_config('auth/gsaml');
    if (isset($gsamlvars->$key)) {
        notify("Current file being used for $key <br/>".basename($gsamlvars->$key),'notifysuccess');
    }

    $mform->display();
    
    close_window_button();
    $CFG->docroot = '';   // We don't want a doc link here
    print_footer($SITE);
}
