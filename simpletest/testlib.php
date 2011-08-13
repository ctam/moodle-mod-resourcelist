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
 * Unit tests for mod/resourcelist/lib.php.
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');  // It must be included from a Moodle page
}

// Make sure the code being tested is accessible.
require_once($CFG->dirroot . '/mod/resourcelist/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once('fixtures/resourcelist_test_case.php');

/** This class contains the test cases for the functions in lib.php. */
class resourcelist_lib_test extends resourcelist_test_case {

    function setUp() {
        parent::setUp();
        $this->load_all_test_tables();
    }

    function tearDown() {

        $this->unload_all_test_tables();
        parent::tearDown();
    }

    function test_resourcelist_get_types() {

        $types = resourcelist_get_types();
        $this->assertIsA($types, 'array');

        // $types[0] should be a resourcelist type object
        $type = $types[0];
        $this->assertEqual($type->modclass, MOD_CLASS_RESOURCE);
        $this->assertEqual($type->type, 'resourcelist');
        $this->assertEqual($type->typestr, 'Resource List');
    }

    function test_resourcelist_get_coursemodule_info_returns_null_if_instance_not_found() {
        $cm = new object();
        $cm->instance = 0;

        $info = resourcelist_get_coursemodule_info( $cm );

        $this->assertNull( $info );
    }

    function test_resourcelist_get_coursemodule_info_contains_icon() {

        $cm = new object();
        $cm->instance = 1;
        $cm->id = 1;
        $cm->course = 1;
    
        $info = resourcelist_get_coursemodule_info( $cm );
    
        $this->assertTrue( isset($info->icon) );
        $this->assertEqual($info->icon, 'switch_minus.gif');
    }

    function test_resourcelist_get_coursemodule_info_contains_resource_module_list() {

        $cm = new object();
        $cm->instance = 1;
        $cm->id = 1;
        $cm->course = 1;
    
        $info = resourcelist_get_coursemodule_info( $cm );
    
        $this->assertTrue( isset($info->extra) );
    
        $expected_extra_string = "modules=\"module-1,module-2\"";
        $this->assertEqual( $info->extra, urlencode($expected_extra_string), 
                            "Equal expectation fails with [".urldecode($info->extra)."] and [$expected_extra_string]" );
    }

    function test_resourcelist_add_instance_with_minimal_data() {

        $new_resourcelist = new object();
        $new_resourcelist->course = 1;
        $new_resourcelist->name = 'newresourcelist';
        $new_resourcelist->summary = 'newresourcelistsummary';
    
        $new_instance = resourcelist_add_instance( $new_resourcelist );

        $new_resourcelist_record = get_record( 'resourcelist', 'id', $new_instance );
        $this->assertEqual( $new_resourcelist_record->course, $new_resourcelist->course );
        $this->assertEqual( $new_resourcelist_record->name, $new_resourcelist->name );
        $this->assertEqual( $new_resourcelist_record->summary, $new_resourcelist->summary );

    }

    function &_create_a_test_resourcelist_with_listitems() {
        $new_resourcelist = new object();
        $new_resourcelist->course = 1;
        $new_resourcelist->section = 1;
        $new_resourcelist->visible = 1;
        $new_resourcelist->name = 'newresourcelist';
        $new_resourcelist->summary = 'newresourcelistsummary';

        // Add list items
        $num_of_items = 3;

        for ($i=0; $i<$num_of_items; $i++) {
            $new_resourcelist->listitemname[$i] = "resourceitem_$i";
            //
            // For some reason, Moodle set the reference field like this, $resourcelist->{"listitemreference[0]"}
            // rather $resourcelist->listitemreference[0].
            //
            $new_resourcelist->{"listitemreference[$i]"} = "resourceitem_reference_$i";
            $new_resourcelist->listitemtype[$i] = "file";
            $new_resourcelist->listitemwindowpopup[$i] = 0;
        }   

        $new_resourcelist->listitem_repeats = $num_of_items;

        return $new_resourcelist;
    }

    function test_resourcelist_add_instance_with_listitems() {

        $new_resourcelist = $this->_create_a_test_resourcelist_with_listitems();
        $num_of_items = $new_resourcelist->listitem_repeats;

        $new_instance = resourcelist_add_instance( $new_resourcelist );
        $new_resourcelist->instance = $new_instance;
        resourcelist_post_update_instance( $new_resourcelist );

        $new_resourcelist_record = get_record( 'resourcelist', 'id', $new_instance );
        $this->assertEqual( $new_resourcelist_record->course, $new_resourcelist->course );
        $this->assertEqual( $new_resourcelist_record->name, $new_resourcelist->name );
        $this->assertEqual( $new_resourcelist_record->summary, $new_resourcelist->summary );

        $listitems_records = get_records('resourcelist_items', 'resourcelistid', $new_instance);

        // Assert list items
        for ($i=0; $i<$num_of_items; $i++) {
            $listitem_found = false;
            $li_record = null;
            foreach ($listitems_records as $li) {
                $li_record = get_record('resource', 'id', $li->resourceid);
                if ($li_record && ($li_record->name == $new_resourcelist->listitemname[$i])) {
                    $listitem_found = true;
                    break;
                }
            }
            $this->assertTrue($listitem_found, "Failed to add Resource item: ".$new_resourcelist->listitemname[$i]);
            if ($listitem_found) {
                $this->assertEqual($li_record->reference, $new_resourcelist->{"listitemreference[$i]"});
                $this->assertEqual($li_record->type, $new_resourcelist->listitemtype[$i]);
            }
        }
    }
  
    function test_resourcelist_delete_instance() {
        $rl = $this->_create_a_test_resourcelist_with_listitems();

        $rl_id = $rl->instance = resourcelist_add_instance( $rl );
        resourcelist_post_update_instance($rl);

        $this->assertTrue(!!$rl_id);

        $listitems_records = get_records('resourcelist_items', 'resourcelistid', $rl_id);

        $ret = resourcelist_delete_instance( $rl_id );
        $this->assertTrue($ret);

        // make sure all the corresponding resources are deleted.
        foreach ($listitems_records as $li) {
            $this->assertFalse(get_record('resource', 'id', $li->resourceid));
        }

        // Deleting the 2nd time should return false
        $ret = resourcelist_delete_instance( $rl_id );
        $this->assertFalse($ret);
    }

    function test_resourcelist_update_instance() {
        $rl = $this->_create_a_test_resourcelist_with_listitems();

        $rl_id = $rl->instance = resourcelist_add_instance($rl);
        resourcelist_post_update_instance($rl);

        $this->assertTrue(!!$rl_id);

        $rl_record = get_record('resourcelist', 'id', $rl_id);

        // update resourcelist id and resource ids before calling resourcelist_update_instance.
        $rl->instance = $rl_id;
        $rl_items = get_records('resourcelist_items', 'resourcelistid', $rl_id);

        foreach($rl_items as $rl_item) {
            $li = get_record('resource', 'id', $rl_item->resourceid);

            for ($i=0; $i<$rl->listitem_repeats; $i++) {
                if ($rl->listitemname[$i] == $li->name) {
                    $rl->listitemid[$i] = $li->id;
                    break;
                }
            }
        }
    
        // Change resourcelist's name test
        $rl->name = "new_resourcelist_name-".$rl_record->name;

        $this->assertNotEqual($rl->name, $rl_record->name);
        $this->assertTrue( resourcelist_update_instance( $rl ) );

        $rl_record = get_record('resourcelist', 'id', $rl_id);
        $this->assertEqual($rl->name, $rl_record->name);

        // Change resourcelist's summary test
        $rl->summary = "new_resourcelist_summary-".$rl_record->summary;
        $this->assertNotEqual($rl->summary, $rl_record->summary);
        $this->assertTrue( resourcelist_update_instance( $rl ) );

        $rl_record = get_record('resourcelist', 'id', $rl_id);
        $this->assertEqual($rl->summary, $rl_record->summary);

        // Change resource item's name test
        $old_name = $rl->listitemname[0];
        $new_name = $rl->listitemname[0] = "new resource name - ".$old_name;
    
        $this->assertTrue(resourcelist_update_instance($rl));
        resourcelist_post_update_instance($rl);

        $res_record = get_record('resource', 'id', $rl->listitemid[0]);

        $this->assertNotEqual($res_record->name, $old_name);
        $this->assertEqual($res_record->name, $new_name);

        // Change resource item's reference test
        $old_reference = $rl->{"listitemreference[0]"};
        $new_reference = $rl->{"listitemreference[0]"} = $old_reference."/new/reference";
    
        $this->assertTrue(resourcelist_update_instance($rl));
        resourcelist_post_update_instance($rl);

        $res_record = get_record('resource', 'id', $rl->listitemid[0]);

        $this->assertNotEqual($res_record->reference, $old_reference);
        $this->assertEqual($res_record->reference, $new_reference);
    }

    function test_resourcelist_remove_an_item() {
        $rl = $this->_create_a_test_resourcelist_with_listitems();
    
        $rl_id = $rl->instance = resourcelist_add_instance( $rl );
        resourcelist_post_update_instance($rl);

        $this->assertTrue(!!$rl_id);

        $rl_items = get_records('resourcelist_items', 'resourcelistid', $rl_id);

        // update resourcelist id and resource ids before calling resourcelist_update_instance.
        $rl->instance = $rl_id;
        foreach($rl_items as $rl_item) {
            $li = get_record('resource', 'id', $rl_item->resourceid);

            for ($i=0; $i<$rl->listitem_repeats; $i++) {
                if ($rl->listitemname[$i] == $li->name) {
                    $rl->listitemid[$i] = $li->id;
                    break;
                }
            }
        }
    
        $total_items_before = count($rl_items);

        // Remove first item by emptying name and reference fields
        $rl->listitemname[0] = "";
        $rl->{"listitemreference[0]"} = "";

        $this->assertTrue(resourcelist_update_instance($rl));
        resourcelist_post_update_instance($rl);

        $rl_items = get_records('resourcelist_items', 'resourcelistid', $rl_id);
        $total_items_after = count($rl_items);

        $this->assertEqual($total_items_before - 1, $total_items_after);
    }
}

?>
