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
 * Cron file - cannot run this block's
 * cron on the main cron because it conflicts
 * with /search/Zend
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks_gdata
 **/

$nomoodlecookie = true; // cookie not needed

require_once(dirname(dirname(dirname(__FILE__))).'/config.php'); // global moodle config file.
require_once($CFG->libdir.'/blocklib.php');

set_time_limit(0);

$starttime = microtime();
$timenow   = time();

if ($block = get_record_select("block", "cron > 0 AND (($timenow - lastcron) > cron) AND visible = 1 AND name = 'gdata'")) {
    if (block_method_result('gdata', 'cron_alt')) {
        if (!set_field('block', 'lastcron', $timenow, 'id', $block->id)) {
            mtrace('Error: could not update timestamp for '.$block->name);
        }
    }
} else {
    mtrace('Not time to run gdata block cron');
}

$difftime = microtime_diff($starttime, microtime());
mtrace("Execution took ".$difftime." seconds");