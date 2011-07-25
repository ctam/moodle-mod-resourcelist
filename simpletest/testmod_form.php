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
 * Unit tests for mod/resourcelist/mod_form.php.
 *
 * @copyright &copy; 2010 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
 * @package mod_resourcelist
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');  // It must be included from a Moodle page
}

// Make sure the code being tested is accessible.
require_once($CFG->dirroot . '/mod/resourcelist/mod_form.php');
require_once('fixtures/resourcelist_test_case.php');

/** This class contains the test cases for the functions in lib.php. */
class resourcelist_mod_resourcelist_mod_form_test extends resourcelist_test_case {

    var $mform = NULL;
    var $form = NULL;

    function setUp() {
        global $CFG;

        parent::setUp();

        if (isset($CFG->resource_websearch))
            $ov_resource_websearch = $CFG->resource_websearch;
        $CFG->resource_websearch = 1;

        if (isset($CFG->resource_allowlocalfiles))
            $ov_resource_allowlocalfiles = $CFG->resource_allowlocalfiles;
        $CFG->resource_allowlocalfiles = 1;

        if (isset($CFG->enablegroupings))
            $ov_enablegroupings = $CFG->enablegroupings;
        unset($CFG->enablegroupings);

        if (isset($CFG->resourcelist_addmoreno))
            $ov_resourcelist_addmoreno = $CFG->resourcelist_addmoreno;
        $CFG->resourcelist_addmoreno = 3;

        if (isset($CFG->resourcelist_listmax))
            $ov_resourcelist_listmax = $CFG->resourcelist_listmax;
        $CFG->resourcelist_listmax = 5;

    }

    function tearDown() {
        global $CFG;

        if (isset($ov_resource_websearch))
            $CFG->resource_websearch = $ov_resource_websearch;
        else
            unset($CFG->resource_websearch);
        unset($ov_resource_websearch);

        if (isset($ov_resource_allowlocalfiles)) 
            $CFG->resource_allowlocalfiles = $ov_resource_allowlocalfiles;
        else
            unset($CFG->resource_allowlocalfiles);
        unset($ov_resource_allowlocalfiles);

        if (isset($ov_enablegroupings)) 
            $CFG->enablegroupings = $ov_enablegroupings;
        else
            unset($CFG->enablegroupings);
        unset($ov_enablegroupings);

        if (isset($ov_resourcelist_addmoreno))
            $CFG->resourcelist_addmoreno = $ov_resourcelist_addmoreno;
        else
            unset($CFG->resourcelist_addmoreno);
        unset($ov_resourcelist_addmoreno);

        if (isset($ov_resourcelist_listmax))
            $CFG->resourcelist_listmax = $ov_resourcelist_listmax;
        else
            unset($CFG->resourcelist_listmax);
        unset($ov_resourcelist_listmax);

        parent::tearDown();
    }

    function _create_resourcelist_mod_form($instance, $section, $cm) {
        $this->mform = new mod_resourcelist_mod_form($instance, $section, $cm);
        $this->form = $this->mform->_form;
        return $this->mform;
    }

    function test_definition() {
        global $CFG, $RESOURCE_WINDOW_OPTIONS;

        $this->_create_resourcelist_mod_form(0,0,0);
        $form = $this->form;

        // Assert General Header
        $this->assertTrue($form->elementExists('general'));
        $this->assertTrue($form->elementExists('name'));
        $this->assertTrue($form->elementExists('summary'));
    
        if (!empty($CFG->formatstringstriptags))
            $text_type = PARAM_TEXT;
        else
            $text_type = PARAM_CLEAN;

        // Assert that the default repeat element number is resourcelist_addmoreno
        $repeat_num = $this->mform->repeat_element_num;
    
        $this->assertEqual($repeat_num, $CFG->resourcelist_addmoreno);

        // Assert List Items
        for ($i=0;$i<$repeat_num;$i++) {
            $this->assertTrue($form->elementExists("listitemheader[$i]"), "List item header is missing!");
            $this->assertTrue($form->elementExists("listitemname[$i]"), "List item name field is missing!");
            $this->assertEqual($form->_types["listitemname[$i]"], $text_type, "listitemname[$i] has the wrong text type!");
            $this->assertTrue($form->elementExists("listitemreference[$i]"), "List item location field is missing!");
            $this->assertTrue($form->elementExists("listitemsearchbutton[$i]"), "Search for web page button is missing!");
            $this->assertTrue($form->elementExists("listitemlocalfilesbutton[$i]"), "Choose a local file button is missing!");
            $this->assertTrue($form->elementExists("listitemforcedownload[$i]"), "List item force download field is missing!");
            $this->assertTrue($form->elementExists("listitemwindowpopup[$i]"), "List item window popup option is missing!");
            $this->assertTrue($form->elementExists("listitemframepage[$i]"), "List item frame page option is missing!");
            $this->assertTrue(isset($form->_advancedElements["listitemframepage[$i]"]), 
                              "listitemframepage[$i] is not set as Advanced field");
            foreach($RESOURCE_WINDOW_OPTIONS as $option) {
                $this->assertTrue($form->elementExists('listitemwindow'.$option."[$i]"), 
                                  "List item window $option option (listitemwindow{$option}[$i]) is missing!");
                $this->assertTrue(isset($form->_advancedElements["listitemwindow{$option}[$i]"]), 
                                  "listitemwindow{$option}[$i] is not set as Advanced field");
            }      
        }

        // Assert standard course module elements
        $this->assertTrue($form->elementExists('modstandardelshdr'));

        // Assert action buttons group (result of calling add_action_buttons()
        $this->assertTrue($form->elementExists('buttonar'));
    }
   
    function test_definition_elements_have_correct_disabledif_dependencies() {
        global $RESOURCE_WINDOW_OPTIONS;

        $this->_create_resourcelist_mod_form(0,0,0);
        $dlist = $this->form->_dependencies;   // this is the disabledif list

        for ($i=0; $i<$this->mform->repeat_element_num; $i++) {
            $this->assertTrue(in_array("listitemforcedownload[$i]", 
                                       $dlist["listitemwindowpopup[$i]"]['eq'][1]));
            $this->assertTrue(in_array("listitemwindowpopup[$i]",
                                       $dlist["listitemforcedownload[$i]"]['checked'][1]));
            $this->assertTrue(in_array("listitemframepage[$i]",
                                       $dlist["listitemwindowpopup[$i]"]['eq'][1]));
            $this->assertTrue(in_array("listitemframepage[$i]",
                                       $dlist["listitemforcedownload[$i]"]['checked'][1]));

            foreach ($RESOURCE_WINDOW_OPTIONS as $option) {
                $this->assertTrue(in_array("listitemwindow{$option}[$i]",
                                           $dlist["listitemwindowpopup[$i]"]['eq'][0]), 
                                  "listitemwindow{$option}[$i] is not in array, _dependencies[listitemwindowpopup[$i]][eq][0] (".
                                  print_r($dlist["listitemwindowpopup[$i]"]['eq'][0], true) );
            }
        }
    }    

    function test_data_preprocessing() {
        global $CFG, $db;

        $rl_id = 1;

        load_test_table($CFG->prefix.'resource', $this->resource_data, $db);
        load_test_table($CFG->prefix.'resourcelist_items', $this->resourcelist_items_data, $db);

        $form_data = array();
        $mform = $this->_create_resourcelist_mod_form($rl_id,1,1);
        $mform->data_preprocessing($form_data);

        $listitems = get_records('resourcelist_items', 'resourcelistid', $rl_id);

        $i = 0;
        foreach($listitems as $item) {
            $res = get_record('resource', 'id', $item->resourceid);
            $this->assertEqual( $form_data["listitemid[$i]"], $res->id );
            $this->assertEqual( $form_data["listitemname[$i]"], $res->name );
            $this->assertEqual( $form_data["listitemreference[$i]"], $res->reference );
            $this->assertEqual( $form_data["listitemtype[$i]"], $res->type );
            $i++;
        }
        
        remove_test_table($CFG->prefix.'resource', $db);
        remove_test_table($CFG->prefix.'resourcelist_items', $db);
    
    }

    function test_resourcelist_listmax () {
        global $CFG;

        $_POST['listitem_repeats'] = $CFG->resourcelist_listmax + 1;
        $_POST['listitem_add_more'] = 'Add more field';
        $this->_create_resourcelist_mod_form(0,0,0);

        // listitemname[listmax] should not be there because listitemname[listmax - 1] should be the last item.
        $this->assertFalse(isset($this->form->_elementIndex["listitemname[$CFG->resourcelist_listmax]"]));
    }
  
}

?>
