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
 * lib.php
 *
 * @copyright &copy; 2010 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
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
