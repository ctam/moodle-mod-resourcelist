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
 * lib.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */


require_once($CFG->dirroot.'/mod/resource/lib.php');

function resourcelist_get_types() {
    global $CFG;

    $types = array();

    $type = new object();
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = 'resourcelist';
    $type->typestr = get_string('modulename', 'resourcelist');
    $types[] = $type;

    return $types;
}

function resourcelist_get_coursemodule_info(&$coursemodule) {
    global $CFG;

    $info = NULL;

    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/datalib.php');

    if ($resourcelistitems = get_records("resourcelist_items", "resourcelistid", $coursemodule->instance, 'id')) {

        $info = new object();    
        $modules = array();
    
        foreach ($resourcelistitems as $listitem) {
            if ($cm = get_coursemodule_from_instance("resource", $listitem->resourceid, $coursemodule->course) ) {
                $modules[] = "module-$cm->id";
            }
        }

        if (!empty($modules)) {
            $info->extra = urlencode('modules="'.implode(",", $modules).'"');
        }

        $info->icon = 'switch_minus.gif';
    }
    return $info;
}

function resourcelist_postprocess(&$resourcelist) {
    global $CFG, $RESOURCE_WINDOW_OPTIONS;
    $alloptions = $RESOURCE_WINDOW_OPTIONS;

    $numofitems = 0;
    if (isset($resourcelist->listitem_repeats))
        $numofitems = $resourcelist->listitem_repeats;

    $addlist = array();
    $updatelist = array();
    $removelist = array();

    $resfields = array('name', 'reference', 'forcedownload', 'windowpopup', 'framepage', 'type');

    for ($i=0;$i<$numofitems;$i++) {
    
        // Convert $resourcelist->{"listitemreference[i]"} to $resourcelist->listitemreference[i]
        if (isset($resourcelist->{"listitemreference[$i]"})) {
            $resourcelist->listitemreference[$i] = $resourcelist->{"listitemreference[$i]"};
            //  unset($resourcelist->{"listitemreference[$i]"});
        }

        // if name field is empty set value to reference field.
        if (empty($resourcelist->listitemname[$i])) {
            if (empty($resourcelist->listitemreference[$i]) ||
                $resourcelist->listitemreference[$i] == $CFG->resource_defaulturl) {
                if (!empty($resourcelist->listitemid[$i]))
                    $removelist[] = $resourcelist->listitemid[$i];
                continue;
            }

            $resourcelist->listitemname[$i] = $resourcelist->listitemreference[$i];
        }

        $resitem = new object();
    
        // Set resource's fields
        foreach ($resfields as $field) {
            $listitemfield = 'listitem'.$field;
            if (isset($resourcelist->{$listitemfield}[$i])) {
                $resitem->$field = $resourcelist->{$listitemfield}[$i];
            }
        }
      
        // Set resource's window options

        foreach ($alloptions as $option) {
            $listitemoption = 'listitemwindow'.$option;
            if (isset($resourcelist->{$listitemoption}[$i])) {
                $resitem->$option = $resourcelist->{$listitemoption}[$i];
            } else {
                $resitem->$option = 0;
            }      
        }

        // Constant fields
        $resitem->course = $resourcelist->course;
        $resitem->section = $resourcelist->section;
        if (! $module = get_record("modules", "name", "resource")) {
            error("This module type doesn't exist");
        }
        $resitem->module = $module->id;
        $resitem->visible = $resourcelist->visible;

        // Add resitem to the corresponding list
        if (empty($resourcelist->listitemid[$i])) {
            $addlist[] = $resitem;
        } else {
            $resitem->instance = $resourcelist->listitemid[$i];
            $updatelist[] = $resitem;
        }   
    }

    $resourcelist->addlistitems = $addlist;
    $resourcelist->updatelistitems = $updatelist;
    $resourcelist->removelistitems = $removelist;

}

function resourcelist_add_instance($resourcelist) {
    global $CFG;

    resourcelist_postprocess($resourcelist);

    $resourcelist->timecreated = time();
    $resourcelist->timemodified = $resourcelist->timecreated;

    $returnfromfunc = insert_record('resourcelist', $resourcelist);

    return $returnfromfunc;
}

function resourcelist_update_instance($resourcelist) {
    resourcelist_postprocess($resourcelist);

    $resourcelist->id = $resourcelist->instance;
    $resourcelist->timemodified = time();

    return update_record('resourcelist', $resourcelist);
}

function resourcelist_post_update_instance($resourcelist) {

    foreach ($resourcelist->removelistitems as $listitemid) {
        _resourcelist_remove_resource($listitemid);
        delete_records("resourcelist_items", "resourcelistid", $resourcelist->instance, "resourceid", $listitemid); 
    }

    $mod_ids = array();
    foreach ($resourcelist->updatelistitems as $listitem) {
        $cm = get_coursemodule_from_instance("resource", $listitem->instance);
        $mod_ids[] = $cm->id;

        $ret = resource_update_instance($listitem);

        if (!$ret) {
            error("Could not update resource, $listitem->name.");
        }
        if (is_string($ret)) {
            error($ret);
        }
        if (isset($listitem->visible)) {
            set_coursemodule_visible($cm->id, $listitem->visible);
        }
    }

    $beforemod = NULL;

    if (!empty($mod_ids)) {
        $beforemod = new object();
        if ($section = get_record("course_sections", "course", $resourcelist->course, "section", $resourcelist->section)) {
            if (!empty($section->sequence)) {      
                $modarray = explode(",", $section->sequence);   
                $modarray_count = count($modarray);

                for ($i=0; $i<$modarray_count; $i++) {
                    if (in_array($modarray[$i], $mod_ids)) {
                        if ($i+1 < $modarray_count) {
                            $beforemod->id = $modarray[$i+1];
                        } else {
                            $beforemod = NULL;
                        }
                    }
                }
            }
        }
    }

    foreach ($resourcelist->addlistitems as $listitem) {
        $ret = _resourcelist_add_resource($resourcelist->instance, $listitem, $beforemod);

        if (!$ret) {
            error("Could not add new resource, $listitem->name.");
        }
    }
}

function resourcelist_delete_instance($id) {
    global $CFG;
    require_once($CFG->libdir.'/datalib.php');
  
    if( ! $reslist = get_record("resourcelist", "id", "$id")) {
        return false;
    }
 
    $reslistitems = get_records("resourcelist_items", "resourcelistid", $reslist->id);

    foreach ($reslistitems as $resitem) {
        _resourcelist_remove_resource( $resitem->resourceid );
    }

    delete_records("resourcelist_items", 'resourcelistid', $reslist->id);
    return delete_records("resourcelist", "id", $reslist->id);
}

function _resourcelist_add_resource( $resourcelist_id, $resource, $beforemod=NULL ) {
    $ret = resource_add_instance($resource);

    if (!$ret) {
        error("Could not add new resource, $resource->name.");
    }

    if (is_string($ret)) {
        error($ret);
    }

    $resource->instance = $ret;

    $resinstance = new object();
    $resinstance->resourceid = $ret;
    $resinstance->resourcelistid = $resourcelist_id;
    $ret = insert_record('resourcelist_items', $resinstance);

    if (!$ret) {
        error("Could not add resource ($resource->name) to resource list ($resourcelist->name).");
    }
    if (is_string($ret)) {
        error($ret);
    }

    // add to course modules and sections (The order is wrong!) (Also, make sure to change 'update')
    $mod = $resource;
    $mod->indent = 1;

    if (! $mod->coursemodule = add_course_module($mod) ) {
        error("Could not add a new course module");
    }

    $mod->id = $mod->coursemodule;
    if (! $sectionid = add_mod_to_section($mod, $beforemod) ) {
        error("Could not add the new course module to that section");
    }

    if (! set_field("course_modules", "section", $sectionid, "id", $mod->coursemodule)) {
        error("Could not update the course module with the correct section");
    }

    if (!isset($mod->visible)) {   // We get the section's visible field status
        $mod->visible = get_field("course_sections","visible","id",$sectionid);
    }

    // make sure visibility is set correctly (in particular in calendar)
    set_coursemodule_visible($mod->coursemodule, $mod->visible);

    return $ret;
}

function _resourcelist_remove_resource( $resource_id ) {  
    if ($resource = get_record("resource", "id", $resource_id)) {

        $cm = get_coursemodule_from_instance("resource", $resource->id, $resource->course);

        if (!resource_delete_instance($resource->id)) {
            error("Could not delete $resource->name");
        }

        if ($cm != null) {
            if (! delete_course_module($cm->id)) {
                notify("Could not delete $resource->name from course module");
            }
            if (! delete_mod_from_section($cm->id, "$cm->section")) {
                notify("Could not delete $resource->name from course section $cm->section");
            }
        }
    }
}

?>
