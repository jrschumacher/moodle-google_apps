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
 * GAccess Settings
 *
 * @author Chris Stones
 *         based off Mark's code
 * @version $Id$
 * @package block_gmail
 **/

require_once($CFG->dirroot.'/lib/adminlib.php');

$configs   = array();
$configs[] = new admin_setting_configcheckbox('newwinlink', "New Window Links", "If selected links will open in new window.", '1');

// Define the config plugin so it is saved to
// the config_plugin table then add to the settings page
foreach ($configs as $config) {
    $config->plugin = 'blocks/gaccess';
    $settings->add($config);
}

?>
