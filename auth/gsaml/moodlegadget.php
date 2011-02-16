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

// SECURITY ISSUE:
// ALL information on this would be public until it is written
// with the google js API's (a lot of work)
// For now this gadget isn't in a block yet so it can't be configured.
// Currently using legacy gadget 
// but we can stuff ANYTHING from Moodle into here!

require_once("../../config.php");
global $CFG;

@header("content-type: text/xml");

$basexml = '<?xml version="1.0" encoding="UTF-8"?>
<Module>
<ModulePrefs title="Moodle Link" />
<Content type="html"><![CDATA[
<a target="_top" href="'.$CFG->wwwroot.'">'.get_string('tomoodle','auth_gsaml').'</a>
]]></Content>
</Module>';

print $basexml;

?>
