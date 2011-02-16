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
 * GMail Block Global Settings
 *
 * @author Chris Stones
 *         based off Mark's code
 * @version $Id$
 * @package block_gmail
 **/

require_once($CFG->dirroot.'/lib/adminlib.php');

$configs   = array();
// $hbutton   = helpbutton('porttype', 'porttypes', 'auth_spie', true, false, '', true, '');
//$configs[] = new admin_setting_configcheckbox('useoauth3legs', get_string('use3leggedoauth', 'block_gmail'), get_string('use3leggedoauthinfo', 'block_gmail'), '1','1','0');

$configs[] = new admin_setting_configpasswordunmask('oauthsecret', get_string('oauthsecretstr', 'block_gmail'), get_string('oauthsecretinfo', 'block_gmail'), '');

$configs[] = new admin_setting_configtext('consumer_key', get_string('consumer_key', 'block_gmail'), get_string('consumer_keyinfo', 'block_gmail'), '');


$configs[] = new admin_setting_configtext('msgnumber', get_string('msgnumberunread', 'block_gmail'), get_string('msgnumberunreadinfo', 'block_gmail'), '0', PARAM_RAW, 5);

// Open links in new window
$configs[] = new admin_setting_configcheckbox('newwinlink', get_string('newwinlink', 'block_gmail'), get_string('newwinlinkinfo', 'block_gmail'), '1');

// Choose to Show First and Last Names to Save Space
$configs[] = new admin_setting_configcheckbox('showfirstname', get_string('showfirstname', 'block_gmail'), get_string('showfirstnameinfo', 'block_gmail'), '1');

$configs[] = new admin_setting_configcheckbox('showlastname', get_string('showlastname', 'block_gmail'), get_string('showlastnameinfo', 'block_gmail'), '1');


// Define the config plugin so it is saved to
// the config_plugin table then add to the settings page
foreach ($configs as $config) {
    $config->plugin = 'blocks/gmail';
    $settings->add($config);
}

?>
