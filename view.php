<?php 
// Copyright © 2010 The Regents of the University of California.
// All Rights Reserved.

// Redistribution and use in source and binary forms, with or without 
// modification, are permitted provided that the following conditions 
// are met:
//   • Redistributions of source code must retain the above copyright 
//     notice, this list of conditions and the following disclaimer.
//   • Redistributions in binary form must reproduce the above copyright 
//     notice, this list of conditions and the following disclaimer in the 
//     documentation and/or other materials provided with the distribution.
//   • None of the names of any campus of the University of California, 
//     the name "The Regents of the University of California," or the 
//     names of any of its contributors may be used to endorse or promote 
//     products derived from this software without specific prior written 
//     permission.

// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS OR CONTRIBUTORS BE 
// LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
// CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
// SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
// INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
// CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
// ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
// POSSIBILITY OF SUCH DAMAGE.

/**
 * view.php
 *
 * @copyright &copy; 2010 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
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