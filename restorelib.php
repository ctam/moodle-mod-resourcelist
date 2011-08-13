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
 * restorelib.php
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

//This function executes all the restore procedure about this mod
function resourcelist_restore_mods($mod,$restore) {

    global $CFG;

    $status = true;

    //Get record from backup_ids
    $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

    if ($data) {
        //Now get completed xmlized object
        $info = $data->info;
        //traverse_xmlize($info);                                                                     //Debug
        //print_object ($GLOBALS['traverse_array']);                                                  //Debug
        //$GLOBALS['traverse_array']="";                                                              //Debug
          
        //Now, build the RESOURCELIST record structure
        $resourcelist->course = $restore->course_id;
        $resourcelist->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
        $resourcelist->summary = backup_todb($info['MOD']['#']['SUMMARY']['0']['#']);
        $resourcelist->timemodified = $info['MOD']['#']['TIMEMODIFIED']['0']['#'];

        //The structure is equal to the db, so insert the resourcelist
        $newid = insert_record ("resourcelist",$resourcelist);

        //Do some output     
        if (!defined('RESTORE_SILENTLY')) {
            echo "<li>".get_string("modulename","resourcelist")." \"".format_string(stripslashes($resourcelist->name),true)."\"</li>";
        }
        backup_flush(300);

        if ($newid) {
            //We have the newid, update backup_ids
            backup_putid($restore->backup_unique_code,$mod->modtype,
                         $mod->id, $newid);
            
            $status = resourcelist_items_restore_mods($newid,$info,$restore);
        } else {
            $status = false;
        }
    } else {
        $status = false;
    }

    return $status;
}

function resourcelist_items_restore_mods($resourcelistid,$info,$restore) {
    global $CFG;

    $status = true;
    $listitems = $info['MOD']['#']['LISTITEMS']['0']['#']['LISTITEM'];

    // Iterate over listitems
    for($i=0;$i<sizeof($listitems);$i++) {
        $item_info = $listitems[$i];
        $oldid = backup_todb($item_info['#']['ID']['0']['#']);

        $listitem->resourcelistid = $resourcelistid;
        $resource_old_id = backup_todb($item_info['#']['RESOURCEID']['0']['#']);
        $resource_data = backup_getid($restore->backup_unique_code,"resource",$resource_old_id);
        if (!empty($resource_data)) {
            $listitem->resourceid = $resource_data->new_id;
            $newid = insert_record('resourcelist_items', $listitem);

            // Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                backup_putid($restore->backup_unique_code,"resourcelist_items",$oldid,$newid);
            } else {
                $status = false;
            }
        }
    }
    return $status;
}

/* function resourcelist_decode_content_links($content,$restore) { */
/* } */

/* function resourcelist_decode_content_links_caller($restore) { */
/* } */

//This function returns a log record with all the necessay transformations
//done. It's used by restore_log_module() to restore modules log.
function resourcelist_restore_logs($restore,$log) {
                    
    $status = false;
                    
    //Depending of the action, we recode different things
    switch ($log->action) {
    case "add":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "update":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "view":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "view all":
        $log->url = "index.php?id=".$log->course;
        $status = true;
        break;
    default:
        if (!defined('RESTORE_SILENTLY')) {
            echo "action (".$log->module."-".$log->action.") unknown. Not restored<br />";                 //Debug
        }
        break;
    }

    if ($status) {
        $status = $log;
    }
    return $status;
}

?>