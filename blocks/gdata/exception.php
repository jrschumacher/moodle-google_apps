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
 * Google Data Exception Class
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package block_gdata
 **/
class blocks_gdata_exception extends Exception {
    /**
     * Constructor
     *
     * @param string $identifier The key identifier for the localized string
     * @param string $module The module where the key identifier is stored. If none is specified then moodle.php is used.
     * @param mixed $a An object, string or number that can be used within translation strings
     * @param int $code Error code
     * @return void
     **/
    public function __construct($identifier, $module = 'block_gdata', $a = NULL, $code = 0) {
        global $CFG;

        $message = get_string($identifier, $module, $a);

        if (debugging()) {
            // Code compliments of /lib/weblib.php :D
            $callers  = $this->getTrace();
            $message .= '<p>Debugging Traceback (to hide, turn off debugging):<ul style="text-align: left">';
            foreach ($callers as $caller) {
                if (!isset($caller['line'])) {
                    $caller['line'] = '?'; // probably call_user_func()
                }
                if (!isset($caller['file'])) {
                    $caller['file'] = $CFG->dirroot.'/unknownfile'; // probably call_user_func()
                }
                $message .= '<li>line ' . $caller['line'] . ' of ' . substr($caller['file'], strlen($CFG->dirroot) + 1);
                if (isset($caller['function'])) {
                    $message .= ': call to ';
                    if (isset($caller['class'])) {
                        $message .= $caller['class'] . $caller['type'];
                    }
                    $message .= $caller['function'] . '()';
                }
                $message .= '</li>';
            }
            $message .= '</ul></p>';
        }

        parent::__construct($message, $code);
    }
} // END class blocks_gdata_exception extends Exception

?>