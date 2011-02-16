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
 * @author Mark Nielsen
 */


/**
 * Google Data Block Global Settings
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package block_gdata
 **/

if (!class_exists('admin_setting_special_croninterval')) {
    /**
     * This setting behaves exactly like
     * admin_setting_configtext except it
     * also stores the value from this config
     * as seconds in the cron field of the
     * gdata block record.
     *
     * @package block_gdata
     **/
    class admin_setting_special_croninterval extends admin_setting_configtext {

        /**
         * Set the cron field for the gdata block record
         * to the number of sections set in this setting.
         *
         * @return boolean
         **/
        function config_write($name, $value) {
            if (empty($value)) {
                $cron = 0;
            } else {
                $cron = $value * MINSECS;
            }
            if (set_field('block', 'cron', $cron, 'name', 'gdata')) {
                return parent::config_write($name, $value);
            }
            return false;
        }
    }
}

$configs   = array();
$configs[] = new admin_setting_configtext('username', get_string('usernamesetting', 'block_gdata'), get_string('usernamesettingdesc', 'block_gdata'), '', PARAM_RAW, 30);
$configs[] = new admin_setting_configpasswordunmask('password', get_string('passwordsetting', 'block_gdata'), get_string('passwordsettingdesc', 'block_gdata'), '');
$configs[] = new admin_setting_configtext('domain', get_string('domainsetting', 'block_gdata'), get_string('domainsettingdesc', 'block_gdata'), '', PARAM_RAW, 30);
$configs[] = new admin_setting_configcheckbox('usedomainemail', get_string('usedomainemailsetting', 'block_gdata'), get_string('usedomainemailsettingdesc', 'block_gdata'), 0);
$configs[] = new admin_setting_configcheckbox('allowevents', get_string('alloweventssetting', 'block_gdata'), get_string('alloweventssettingdesc', 'block_gdata'), 1);
$configs[] = new admin_setting_special_croninterval('croninterval', get_string('cronintervalsetting', 'block_gdata'), get_string('cronintervalsettingdesc', 'block_gdata'), 30, PARAM_INT, 30);
$configs[] = new admin_setting_configtext('cronexpire', get_string('cronexpiresetting', 'block_gdata'), get_string('cronexpiresettingdesc', 'block_gdata'), '24', PARAM_INT, 30);

// Define the config plugin so it is saved to
// the config_plugin table then add to the settings page
foreach ($configs as $config) {
    $config->plugin = 'blocks/gdata';
    $settings->add($config);
}

?>