<?php // $Id$

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
 * Google Services Access
 *
 * Development plans:
 * All services we support will have links and icons
 * Optional Google Icon Set
 * 
 * @author Chris Stones 
 * @version $Id$
 * @package block_gaccess
 **/
class block_gaccess extends block_list {


    function init() {
        $this->title   = get_string('blockname', 'block_gaccess');
        $this->version = 2008102402;
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'my' => true);    
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $USER, $COURSE;


		// quick and simple way to prevent block from showing up on front page
    	if (!isloggedin()) {
    		$this->content = NULL;
    		return $this->content;
    	}
		
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

		
        $domain = get_config('auth/gsaml','domainname');
        if( empty($domain)) {
        	$this->content->items[] = get_string('nodomainyet','block_gaccess');//"No DOMAIN configured yet";
    		return $this->content;
    	}
    	
    	
        // USE the icons from this page
        // https://www.google.com/a/cpanel/mroomsdev.com/Dashboard
        // Google won't mind ;) (I hope)
        $google_services = array();
        
            $google_services[] = array(
        	        'service'   => 'Gmail',
        			'relayurl'  => 'https://mail.google.com/a/'.$domain, 
        			'icon_name' => 'gmail.png'
        	);
        	
        	$google_services[] = array(
        	        'service'   => 'Start Page',
        			'relayurl'  => 'http://partnerpage.google.com/'.$domain,
        			'icon_name' => 'startpage.png'
        	);
        	
        $google_services[] = array(
        	        'service'   => 'Calendar',
        			'relayurl'  => 'https://www.google.com/calendar/a/'.$domain, 
        			'icon_name' => 'calendar.png'
        	);
        	
        $google_services[] = array(
        	        'service'   => 'Docs',
        			'relayurl'  => 'https://docs.google.com/a/'.$domain, 
        			'icon_name' => 'gdocs.png'
        	);

        $google_services[] = array(
        	        'service'   => 'Sites',
        			'relayurl'  => 'https://sites.google.com/a/'.$domain, 
        			'icon_name' => 'sites.gif'
        	);
        	
        $google_services[] = array(
        	        'service'   => 'Wave',
        			'relayurl'  => 'https://wave.google.com/a/'.$domain, 
        			'icon_name' => 'gwave.gif'
        	);

        
        $newwinlnk = get_config('blocks/gaccess','newwinlink');
        if ($newwinlnk) { 
            $target = 'target="_blank"';
        } else {
            $target = '';
        }
        
        foreach( $google_services as $gs ) { // $gs['']
            $this->content->items[] = "<a ".$target." title=\"".$gs['service']."\"  href=\"".$gs['relayurl']."\">".$gs['service']."</a>";
            
            if ( !empty($gs['icon_name']) ) {
        		$this->content->icons[] = "<img src=\"$CFG->wwwroot/blocks/gaccess/imgs/".$gs['icon_name']."\" alt=\"".$gs['service']."\" />";        	
	        } else {
	        	// Default to a check graphic
	        	$this->content->icons[] = "<img src=\"$CFG->pixpath/i/tick_green_small.gif\" alt=\"$service\" />";
	        }
        }
        
        return $this->content;
    }
}

?>

