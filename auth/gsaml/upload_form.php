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
 * Mimimalist Upload Form
 * 
 * @author Chris Stones
 * @version $Id$
 * @package auth_gsaml
 */
 
//require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->dirroot.'/lib/formslib.php');

class gsaml_upload_form extends moodleform {

    var $key; // the name of the file 
    
    function gsaml_upload_form($key) {
          $this->key = $key;
          parent::moodleform(qualified_me()); // when the form is resubmitted we need the key defined still.
    }
   
    function definition() {
        $mform    =& $this->_form;
        $this->set_upload_manager(new upload_manager($this->key, false, false, null, false, 0, true, true, false));
        //$mform->setHelpButton('shufflequestions', array("shufflequestions", get_string("shufflequestions","quiz"), "quiz"));
        $mform->addElement('header', 'keyupload', "Upload");
        $mform->addElement('file',$this->key); 
        $this->add_action_buttons();
    }

    function validation($data, $files) {
       // Check to see that Save Changes was called with a file! 
       if (empty($files) ) {
            return array($this->key=>"No File to Upload");
       }
       return true;
    }
}
?>