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
 * resourcelist_test.php
 * Unit test fixtures for mod/resourcelist/simpletest
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */

require_once($CFG->libdir.'/simpletestlib.php');

class resourcelist_test_case extends prefix_changing_test_case {
  var $tables = array('context', 'course_modules','course_sections','event','modules','resource',
                      'resourcelist','resourcelist_items');

  var $context_data = array(
                            array('id','contextlevel','instanceid','path','depth'),
                            array( 1,   1,             0,           '/1',     1)
                            );

  var $course_modules_data = array(
                                   array('id', 'course', 'module', 'instance', 'section', 'visible'),
                                   array( 1,    1,        1,        1,          1,         1 ),
                                   array( 2,    1,        1,        2,          1,         1 ),
                                   array( 3,    1,        2,        1,          1,         1 )
                                   );
  
  var $modules_data = array( 
                            array('id', 'name',        'visible'),
                            array( 1,   'resource',     1 ),
                            array( 2,   'resourcelist', 1 )
                             );
  
  var $resource_data = array(
                             array('id', 'course', 'name',          'type', 'reference',                'summary',              'alltext', 'popup', 'options'),
                             array( 1,    1,       'testresource1', 'file', 'http://testresource1.com', 'testresource1summary', '', '', '' ),
                             array( 2,    1,       'testresource2', 'file', 'testresource2.gif',        'testresource2summary', '', '', '' )
                             );
  
  var $resourcelist_data = array(
                                 array('id', 'course', 'name',              'summary'),
                                 array( 1,    1,       'testresourcelist1', 'resourcelist1summary')
                                 );

  var $resourcelist_items_data = array(
                                       array('id', 'resourcelistid', 'resourceid'),
                                       array( 1, 1, 1 ), array( 2, 1, 2 )
                                       );

  var $course_sections_data = array( 
                                    array('id', 'course', 'section', 'summary', 'sequence', 'visible'),
                                    array( 1, 1, 1, 'coursesectionsummary', '1,2,3', 1 )
                                     );

  var $event_data = array(
                          array('id','name','description','format','courseid','groupid','userid','repeateid','modulename','instance','eventtype','timestart','timeduration','visible','uuid','sequence','timemodified'),
                          array(1,'','',1,1,0,0,0,'',0,'',0,0,1,null,1,0)
                         );
  
  function load_all_test_tables() {
    global $CFG, $db;

    if (!isset($this->old_prefix)) {
      error("parent::setUp() must be called before calling load_all_test_tables().");
    }

    foreach ($this->tables as $table) {
      load_test_table( $CFG->prefix.$table, $this->{$table.'_data'}, $db );
    }
  }

  function unload_all_test_tables() {
    global $CFG, $db;

    foreach($this->tables as $table){
      remove_test_table( $CFG->prefix.$table, $db );
    }
  }

}

?>