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
 * Subclass admin_setting with admin_setting_upload so we can print form elements
 * in the same style.
 * 
 * The main difference is that output_html() 
 * prints a link to the upload_key pop up window. 
 * 
 * This subclass is now specific to gsaml do to the get_confing('auth/gsaml') usage. :(
 * added in order to fix a bug on a short deadline. 
 * 
 * @author Chris Stones
 * @version $Id$
 * @package auth_gsaml
 */
 
require_once($CFG->dirroot.'/lib/adminlib.php');
require_once($CFG->libdir.'/uploadlib.php');

class admin_setting_upload extends admin_setting {

    var $paramtype;
    var $size;
    var $_upload_manager;
    var $file_name_ref;
    
    /**
     * config text contructor
     * @param string $name of setting
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string $defaultsetting
     * @param mixed $paramtype int means PARAM_XXX type, string is a allowed format in regex
     * @param int $size default field size
     */
    function admin_setting_upload($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $size=null,$fileref=null) {  
        $this->paramtype = $paramtype; // ?? of type file? can't really type it
        if (!is_null($size)) {
            $this->size  = $size;
        } else {
            $this->size  = ($paramtype == PARAM_INT) ? 5 : 30;
        }
		$this->file_name_ref = $fileref;
        parent::admin_setting($name, $visiblename, $description, $defaultsetting);
    }

    function get_setting() {
        return $this->config_read($this->name);
    }

    function write_setting($data) {
       // Uploader doesn't have a value to return.
       return '';
    }

    function get_defaultsetting() {
        
        // HACK Fix to prevent options from always showing up in notifications.
        // Basically, we need to make sure plugin configs get set so the settings
        // don't show up again. Since get_defaultsetting is only called when
        // setting->get_setting() returns null this function should be able to 
        // set up our CFG without overriding anything. 
        //
        $gsaml = get_config('auth/gsaml');
        if ( !isset($gsaml->{$this->name}) ) {
              set_config($this->name,'','auth/gsaml'); 
        } 
        
        return $this->defaultsetting;
    }
    
    function output_html($data, $query='') {
        $default = $this->get_defaultsetting();
        global $CFG;
        $uploadkeystr = get_string('uploadkeystr','auth_gsaml'); 
        $uploadkey = get_string('uploadkey','auth_gsaml'); 
        $uploadstr = get_string('uploadstr','auth_gsaml');
        $f = link_to_popup_window($CFG->wwwroot.'/auth/gsaml/upload_key.php?key='.$this->name, 
                                  $uploadkey.$this->name,$uploadstr,450,600, $uploadkeystr,null, true);
                               
        return format_admin_setting($this, $this->visiblename,$f,$this->description, true, '', $default, $query);
    }
}

?>
