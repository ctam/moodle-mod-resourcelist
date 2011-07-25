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
 * resourcelist_test.php
 * Unit test fixtures for mod/resourcelist/simpletest
 *
 * @copyright &copy; 2010 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
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