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
 * Gdata Event hooks
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package block_gdata
 **/

$handlers = array(
    'user_updated' => array(
        'handlerfile'     => '/blocks/gdata/gapps.php',
        'handlerfunction' => array('blocks_gdata_gapps', 'user_updated_event'),
        'schedule'        => 'instant'
    ),

    'user_deleted' => array(
        'handlerfile'     => '/blocks/gdata/gapps.php',
        'handlerfunction' => array('blocks_gdata_gapps', 'user_deleted_event'),
        'schedule'        => 'instant'
    ),

    'password_changed' => array(
        'handlerfile'     => '/blocks/gdata/gapps.php',
        'handlerfunction' => array('blocks_gdata_gapps', 'password_changed_event'),
        'schedule'        => 'instant'
    )
);

?>