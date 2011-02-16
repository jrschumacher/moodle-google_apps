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
 * auth_saml Settings
 *
 * @author Chris Stones
 *         based off Mark's code
 * @version $Id$
 * @package auth_saml
 **/

require_once($CFG->dirroot.'/lib/adminlib.php');
require_once($CFG->dirroot.'/auth/gsaml/admin_upload.php');
require_once($CFG->libdir.'/uploadlib.php');

$samlvars = get_config('auth/gsaml');


// PERHAPS: Add a Security Toggle to toggle between datadir storing keys and temp dir storing keys
// WARNING: if your datadir is accessible then the saml sso is at risk!
// You may choose to use the alterative temp dir system or the Datadir system.
// ...If the temp file is written everytime IS it really that much safer?

// WARNING: ABOUT TMPDIR http://www.comptechdoc.org/os/linux/usersguide/linux_ugfilesp.html
// "The /tmp directory is typically world-writable and looks like this in a listing:" :/
// Possible plan for temporary directory...
// Create a temporary file in the temp 
// files directory using sys_get_temp_dir()
// function_exists()
// $temp_file = tempnam(sys_get_temp_dir(), 'Tux');
// string tempnam  ( string $dir  , string $prefix  )
// echo $temp_file;

// I'm giving the options because one's much simplier to get things working and the other
// ensures better security

// TODO: Add a visible Warning that the DATADIR most NOT be in public acceisble
//require_once($CFG->dirroot.'/blocks/gdata/setting.php');
//admin_setting_configtext($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $size=null)
$configs = array();

$domainnamestr = get_string('domainnamestr','auth_gsaml');
$configs[] = new admin_setting_configtext('domainname', $domainnamestr, "", '', PARAM_RAW, 30);


// Private Key Upload Option                                                    
$rsa_str   = get_string('rsakeystr','auth_gsaml');
$desc_key  = get_string('desckeystr','auth_gsaml'); 
$googauthconfstr = get_string('googauthconfstr','auth_gsaml');
$hbutton   = helpbutton('keys', $googauthconfstr, 'auth_gsaml', true, false, '', true,'');

$privatekey_data = !empty($samlvars->privatekey) ? basename($samlvars->privatekey) : '';
$configs[] = new admin_setting_upload('privatekey',$rsa_str.' '.$hbutton, $desc_key,null, PARAM_RAW, null, 'privatekey');


// Certificate Upload Option
$ssl_str   = get_string('ssl_str','auth_gsaml'); 
$desc_cert = get_string('desc_certstr','auth_gsaml'); 
$hbutton   = helpbutton('keys', $googauthconfstr, 'auth_gsaml', true, false, '', true, '');

$sslcert_data = !empty($samlvars->certificate) ? basename($samlvars->certificate) : '';
$configs[] = new admin_setting_upload('certificate',$ssl_str.' '.$hbutton, $desc_cert, null, PARAM_RAW, null, 'sslcertfile');


// Provide a Link to Google Settings
$googsettings = get_string('lnktogoogsettings','auth_gsaml');
if (empty($samlvars->domainname)) {
    $samlvars->domainname = '';
    $googsettings = get_string('nodomainyet','auth_gsaml'); 
} 

        
// Table of Steps String
$a = new stdClass;
$a->domainname = $samlvars->domainname;
$a->googsettings = $googsettings;
$a->wwwroot = $CFG->wwwroot;
$info = get_string('gsamlsetuptableinfo','auth_gsaml',$a);


// Main Instructional Table
$hbutton = helpbutton('config_gsaml', $googauthconfstr, 'auth_gsaml', true, false, '', true, '');
$setupinstrctstr = get_string('setupinstrctstr','auth_gsaml');
$configs[] = new admin_setting_heading('info', $setupinstrctstr.$hbutton, $info);


// Moodle Gadget Info and Set Up
$mgadgethelp = get_string('mgadgethelp','auth_gsaml');                   
$hbutton = helpbutton('mgadget',$mgadgethelp,'auth_gsaml',true, false, '', true, '');
$a = new stdClass;
$a->wwwroot = $CFG->wwwroot;
$gadgetinfo = get_string('gadgetinfostr','auth_gsaml',$a);
$mgadgetstr = get_string('mgadgetstr','auth_gsaml');
$configs[] = new admin_setting_heading('moodlegadget', $mgadgetstr.$hbutton, $gadgetinfo);


// Diagnostics Info and Options
$googdiag   = get_string('googdiag','auth_gsaml');
$hbutton    = helpbutton('diagnostics', $googdiag, 'auth_gsaml', true, false, $text='', true, '');
$debugopts  = get_string('googdebugopts','auth_gsaml'); 
$debugopts .= '<a href="'.$CFG->wwwroot.'/auth/gsaml/diagnostics.php'.'">'.get_string('thediagnosticspage','auth_gsaml').'</a> for confirmation.';
$configs[]  = new admin_setting_heading('diagnostics', "Diagnostics ".$hbutton, $debugopts);


foreach ($configs as $config) {
    $config->plugin = 'auth/gsaml';
    $settings->add($config);
}

?>
