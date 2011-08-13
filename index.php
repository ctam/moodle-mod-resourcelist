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
 * index.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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