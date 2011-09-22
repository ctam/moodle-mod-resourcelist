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
 * backuplib.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */

//This php script contains all the stuff to backup/restore
//resourcelist mods

//This is the "graphical" structure of the resourcelist mod:
//
//                    resourcelist
//                 (CL,pk->id,files)
//
// Meaning: pk->primary key field of the table
//          fk->foreign key to link with parent
//          nt->nested field (recursive data)
//          CL->course level info
//          UL->user level info
//          files->table may have files)
//
//-----------------------------------------------------------

//This function executes all the backup procedure about this mod
function resourcelist_backup_mods($bf,$preferences) {
    global $CFG;

    $status = true; 

    ////Iterate over resourcelist table
    $resourcelists = get_records ("resourcelist","course",$preferences->backup_course,"id");
    if ($resourcelists) {
        foreach ($resourcelists as $resourcelist) {
            if (backup_mod_selected($preferences,'resourcelist',$resourcelist->id)) {
                $status = resourcelist_backup_one_mod($bf,$preferences,$resourcelist);
            }
        }
    }
    return $status;
}
   
function resourcelist_backup_one_mod($bf,$preferences,$resourcelist) {

    global $CFG;
    
    if (is_numeric($resourcelist)) {
        $resourcelist = get_record('resourcelist','id',$resourcelist);
    }
    
    $status = true;

    //Start mod
    fwrite ($bf,start_tag("MOD",3,true));
    //Print assignment data
    fwrite ($bf,full_tag("ID",4,false,$resourcelist->id));
    fwrite ($bf,full_tag("MODTYPE",4,false,"resourcelist"));
    fwrite ($bf,full_tag("NAME",4,false,$resourcelist->name));
    fwrite ($bf,full_tag("SUMMARY",4,false,$resourcelist->summary));
    fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$resourcelist->timemodified));

    //Now backup resourcelist_items
    $status = backup_resourcelist_items($bf,$preferences,$resourcelist->id);

    //End mod
    $status = fwrite ($bf,end_tag("MOD",3,true));

    return $status;
}

// backup resourcelist items (executed from choice_backup_mods)
function backup_resourcelist_items($bf,$preferences,$resourcelist) {
    global $CFG;

    $status = true;

    $resourcelist_items = get_records('resourcelist_items','resourcelistid',$resourcelist,'id');

    if ($resourcelist_items) {
        //Write start tag
        $status = fwrite($bf,start_tag("LISTITEMS",4,true));
        foreach($resourcelist_items as $listitem) {
            //Start listitem
            $status = fwrite($bf,start_tag("LISTITEM",5,true));
            //Print listitem contents
            fwrite($bf,full_tag("ID",6,false,$listitem->id));
            fwrite($bf,full_tag("RESOURCELISTID",6,false,$listitem->resourcelistid));
            fwrite($bf,full_tag("RESOURCEID",6,false,$listitem->resourceid));
            //End listitem
            $status = fwrite($bf,end_tag("LISTITEM",5,true));
        }
        //Write end tag
        $status = fwrite($bf,end_tag("LISTITEMS",4,true));
    }
    return $status;
}

// generates an array of course and user data information used to select which instances to 
// backup (and whether to include user data or not). This includes details at [0][0] and [0][1] 
// about the course module name and number of instances for the course and at [1][0] and [1][1] 
// with the module name and count of user information.
function resourcelist_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
    if (!empty($instances) && is_array($instances) && count($instances)) {
        $info = array();
        foreach ($instances as $id => $instance) {
            $info += resourcelist_check_backup_mods_instances($instance,$backup_unique_code);
        }
        return $info;
    }
        
    //First the course data
    $info[0][0] = get_string("modulenameplural","resourcelist");
    $info[0][1] = count_records("resourcelist", "course", "$course");
    return $info;
}

function resourcelist_check_backup_mods_instances($instance,$backup_unique_code) {
    //First the course data
    $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
    $info[$instance->id.'0'][1] = '';
    return $info;
}

// Recode links to ensure they work when re-imported.
/* function resourcelist_encode_content_links($content,$preferences) { */
/* } */

?>