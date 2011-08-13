<?php 
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// Copyright © 2011 The Regents of the University of California.
// All Rights Reserved.

/**
 * view.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 *
 */
require_once("../../config.php");
require_once("lib.php");
 
$id = optional_param('id', 0, PARAM_INT);    // Course Module ID

if (! $cm = get_coursemodule_from_id('resourcelist', $id)) {
    error('Course Module ID was incorrect');
}

if (! $course = get_record('course', 'id', $cm->course)) {
    error('Incorrect course id');
}

if (! $resourcelist = get_record('resourcelist', 'id', $cm->instance)) {
    error('Resource list ID was incorrect');
}
  
require_course_login($course, true, $cm);

if (! $reslistitems = get_records('resourcelist_items', 'resourcelistid', $cm->instance, 'id')) {
    error('Cannot get resources for resource list');
}

/// read standard strings
$strresourcelists = get_string('modulenameplural', 'resourcelist');
$strresourcelist = get_string('modulename', 'resourcelist');

$navigation = build_navigation('', $cm);
$pagetitle = strip_tags($course->shortname.': '.format_string($cm->name));
            
print_header($pagetitle, $course->fullname, $navigation,
             "", "", true, update_module_button($cm->id, $course->id, $strresourcelist),
             navmenu($course, $cm));

/* Display resource list */
$strname = get_string("name");
$strsummary = get_string("summary");

if ($course->format == "weeks") {
    $table->head = array( get_string("week"), $strname, $strsummary );
    $table->align = array("center", "left", "left");
} else if ($course->format == "topics") {	
    $table->head = array( get_string("topic"), $strname, $strsummary );
    $table->align = array("center", "left", "left");
} else {
    $table->head = array( get_string("lastmodified"), $strname, $strsummary );
    $table->align = array("left", "left", "left");
}
	    
$options->para = false;

if (! $mod_section = get_record("course_sections", "id", $cm->section)){
    error('Incorrect section id');
}

if ($course->format == "weeks" or $course->format == "topics") {
    $printsection = $mod_section->section;
} else {
    $printsection = '<span class="smallinfo">'.userdate($resourcelist->timemodified)."</span>";
}

$table->data[] = array($printsection, 
                       format_string($resourcelist->name,true),
                       format_text($resourcelist->summary, FORMAT_MOODLE, $options) );

foreach ($reslistitems as $listitem) {
    if ($resource = get_record('resource', 'id', $listitem->resourceid)) {

        $extra = "";
        $table->data[] = array ("", 
                                "<a $extra href=\"../resource/view.php?r=$resource->id\">".format_string($resource->name,true)."</a>",
                                format_text($resource->summary, FORMAT_MOODLE, $options) );

    }
}

add_to_log($course->id, "resourcelist", "view", "view.php?id=$cm->id", $resourcelist->id, $cm->id);
	      
echo "<br />";
print_table($table);
	    
/* End of Display resource list */

$strlastmodified = get_string("lastmodified");
echo "<br /><div class=\"timemodified\">$strlastmodified: ".userdate($resourcelist->timemodified)."</div>";

print_footer($course);

?>