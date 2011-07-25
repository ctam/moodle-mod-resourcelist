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
 * restorelib.php
 *
 * @copyright &copy; 2010 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
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