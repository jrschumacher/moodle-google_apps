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
 * Rest page for accepting user accounts to sync
 *
 * The closing PHP tag (?>) was deliberately left out
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package block_gdata
 **/

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomoodlecookie = true;

    require('../../config.php');
    require_once($CFG->dirroot.'/blocks/gdata/gapps.php');

    $response = array('counts' => array('errors' => 1), 'message' => '');

    if ($userid = optional_param('userid', 0, PARAM_INT)) {
        try {
            // Want to capture output so we
            // can return it properly
            ob_start();

            $gapps = new blocks_gdata_gapps();

            $moodleuser = $gapps->moodle_get_user($userid);
            $gapps->sync_moodle_user_to_gapps($moodleuser);

            $output = ob_get_contents();
            $output = trim($output);
            ob_end_clean();

            if (!empty($output)) {
                $response['message'] = $output;
            }
            $response['counts'] = $gapps->counts;

        } catch (blocks_gdata_exception $e) {
            $response['message'] = $e->getMessage();
        } catch (Zend_Exception $e) {
            // Catch Zend_Exception just in case it happens
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid userid passed';
    }

    echo serialize($response);
}