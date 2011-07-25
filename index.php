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
 * index.php
 *
 * @copyright &copy; 2010 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
 * @package mod_resourcelist
 */

require_once("../../config.php");
require_once("lib.php");

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID

if (! $course = get_record('course', 'id', $id)) {
    error('Incorrect course id');
}

require_course_login($course);

add_to_log($course->id, "resourcelist", "view all", "index?id=$course->id", "");

/// read standard strings
$strresourcelists = get_string('modulenameplural', 'resourcelist');
$strresourcelist = get_string('modulename', 'resourcelist');

$navlinks = array();
$navlinks[] = array('name'=>$strresourcelists, 'link'=>'', 'type'=>'activityinstance');
$navigation = build_navigation($navlinks);

print_header("$course->shortname: $strresourcelists", $course->fullname, $navigation,
             "", "", true, "", navmenu($course));

$strweek = get_string("week");
$strtopic = get_string("topic");
$strname = get_string("name");
$strsummary = get_string("summary");
$strlastmodified = get_string("lastmodified");

if (! $resourcelists = get_all_instances_in_course("resourcelist", $course)) {
    notice(get_string('thereareno', 'moodle', $strresourcelists), "../../course/view.php?id=$course->id");
    exit;
}

if ($course->format == "weeks") {
    $table->head  = array ($strweek, $strname, $strsummary);
    $table->align = array ("center", "left", "left");
} else if ($course->format == "topics") {
    $table->head  = array ($strtopic, $strname, $strsummary);
    $table->align = array ("center", "left", "left");
} else {
    $table->head  = array ($strlastmodified, $strname, $strsummary);
    $table->align = array ("left", "left", "left");
}

$currentsection = "";
$options->para = false;
foreach ($resourcelists as $resourcelist) {
    if ($course->format == "weeks" or $course->format == "topics") {
        $printsection = "";
        if ($resourcelist->section !== $currentsection) {
            if ($resourcelist->section) {
                $printsection = $resourcelist->section;
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $resourcelist->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($resourcelist->timemodified)."</span>";
    }
    if (!empty($resourcelist->extra)) {
        $extra = urldecode($resourcelist->extra);
    } else {
        $extra = "";
    }
    if (!$resourcelist->visible) {      // Show dimmed if the mod is hidden
        $table->data[] = array ($printsection,
                                "<a class=\"dimmed\" $extra href=\"view.php?id=$resourcelist->coursemodule\">".format_string($resourcelist->name,true)."</a>",
                                format_text($resourcelist->summary, FORMAT_MOODLE, $options) );

    } else {                        //Show normal if the mod is visible
        $table->data[] = array ($printsection,
                                "<a $extra href=\"view.php?id=$resourcelist->coursemodule\">".format_string($resourcelist->name,true)."</a>",
                                format_text($resourcelist->summary, FORMAT_MOODLE, $options) );
    }
}

echo "<br />";

print_table($table);

print_footer($course);


            
?>