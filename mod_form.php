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
 * mod_form.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */

require_once($CFG->dirroot.'/mod/resourcelist/lib.php');
require_once($CFG->dirroot.'/mod/resource/type/file/resource.class.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_resourcelist_mod_form extends moodleform_mod {

    var $repeat_element_num = 3;

    function definition() {
        global $CFG, $RESOURCE_WINDOW_OPTIONS;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general','form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'summary', get_string('summary'));
        $mform->setType('summary', PARAM_RAW);
        $mform->setHelpButton('summary', array('summary', get_string('summary'), 'resource'));

        // Set up repeat elements
        if (isset($CFG->resourcelist_addmoreno)) {
            $this->repeat_element_num = $CFG->resourcelist_addmoreno;
        }

        if ($this->_instance) {
            $repeatno = count_records('resourcelist_items', 'resourcelistid', $this->_instance);
            $repeatno = ceil($repeatno/$this->repeat_element_num) * $this->repeat_element_num ;
        } else {
            $repeatno = $this->repeat_element_num;
        }
        $repeatnomax = $CFG->resourcelist_listmax;

        $repeatarray = array();
        $repeateloptions = array();

        $repeatarray[] = &MoodleQuickForm::createElement('header', 'listitemheader', get_string('listitemheader', 'resourcelist'));

        // add hidden fields
        $repeatarray[] = &MoodleQuickForm::createElement('hidden', 'listitemid', NULL);
        $repeateloptions['listitemid']['type'] = PARAM_INT;

        $repeatarray[] = &MoodleQuickForm::createElement('hidden', 'listitemtype', 'file');

        // add list item fields
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'listitemname', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $repeateloptions['listitemname']['type'] = PARAM_TEXT;
        } else {
            $repeateloptions['listitemname']['type'] = PARAM_CLEAN;
        }

        $repeatarray[] = &MoodleQuickForm::createElement('choosecoursefile', 'listitemreference', get_string('location'), null, array('maxlength' => 255, 'size' => 48));
        $repeateloptions['listitemreference']['default'] = $CFG->resource_defaulturl;
        $repeateloptions['listitemreference']['grouprule'] = array('value' => array(array(get_string('maximumchars', '', 255), 'maxlength', 255, 'client')));

        if (!empty($CFG->resource_websearch)) {
            $searchbutton = &MoodleQuickForm::createElement('button', 'listitemsearchbutton', get_string('searchweb', 'resource').'...');
            $buttonattributes = array('title'=>get_string('searchweb', 'resource'), 'onclick'=>"return window.open('"
                                      . "$CFG->resource_websearch', 'websearch', 'menubar=1,location=1,directories=1,toolbar=1,"
                                      . "scrollbars,resizable,width=800,height=600');");
            $searchbutton->updateAttributes($buttonattributes);
            $repeatarray[] = $searchbutton;
        }

        if (!empty($CFG->resource_allowlocalfiles)) {
            $lfbutton = &MoodleQuickForm::createElement('button', 'listitemlocalfilesbutton', get_string('localfilechoose', 'resource').'...');
            $options = 'menubar=0,location=0,scrollbars,resizable,width=600,height=400';
            $url = '/mod/resource/type/file/localfile.php?choose=id_reference_value';
            $buttonattributes = array('title'=>get_string('localfilechoose', 'resource'), 'onclick'=>"return openpopup('$url', '"
                                      . $lfbutton->getName()."', '$options', 0);");
            $lfbutton->updateAttributes($buttonattributes);
            $repeatarray[] = $lfbutton;
        }

        $repeatarray[] = &MoodleQuickForm::createElement('checkbox', 'listitemforcedownload', get_string('forcedownload', 'resource'));
        $repeateloptions['listitemforcedownload']['helpbutton'] = array('forcedownload', get_string('forcedownload', 'resource'), 'resource');
        $repeateloptions['listitemforcedownload']['disabledif'] = array('listitemwindowpopup', 'eq', 1);

        $woptions = array(0 => get_string('pagewindow', 'resource'), 1 => get_string('newwindow', 'resource'));
        $repeatarray[] = &MoodleQuickForm::createElement('select', 'listitemwindowpopup', get_string('display', 'resource'), $woptions);
        $repeateloptions['listitemwindowpopup']['disabledif'] = array('listitemforcedownload', 'checked');
        $repeateloptions['listitemwindowpopup']['default'] = !empty($CFG->resource_popup);

        $navoptions = array(0 => get_string('keepnavigationvisibleno','resource'), 1 => get_string('keepnavigationvisibleyesframe','resource'), 2 => get_string('keepnavigationvisibleyesobject','resource'));
        $repeatarray[] = &MoodleQuickForm::createElement('select', 'listitemframepage', get_string('keepnavigationvisible', 'resource'), $navoptions);

        $repeateloptions['listitemframepage']['helpbutton'] = array('frameifpossible', get_string('keepnavigationvisible', 'resource'), 'resource');
        $repeateloptions['listitemframepage']['default'] = 0;
        $repeateloptions['listitemframepage']['disabledifs'] = array(array('listitemwindowpopup', 'eq', 1), array('listitemforcedownload', 'checked'));
        $repeateloptions['listitemframepage']['advanced'] = true;

        $repeatarray[] = &MoodleQuickForm::createElement('static','shownavigationwarning','','<i>'.get_string('keepnavigationvisiblewarning', 'resource').'</i>');

        foreach ($RESOURCE_WINDOW_OPTIONS as $option) {
            if ($option == 'height' or $option == 'width') {
                $repeatarray[] = &MoodleQuickForm::createElement('text', 'listitemwindow'.$option, get_string('new'.$option, 'resource'), array('size'=>'4'));
            } else {
                $repeatarray[] = &MoodleQuickForm::createElement('checkbox', 'listitemwindow'.$option, get_string('new'.$option, 'resource'));
            }
            $repeateloptions['listitemwindow'.$option]['default'] = $CFG->{'resource_popup'.$option};
            $repeateloptions['listitemwindow'.$option]['disabledif'] = array('listitemwindowpopup', 'eq', 0);
            $repeateloptions['listitemwindow'.$option]['advanced'] = true;
        }

        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'listitem_repeats', 
                               'listitem_add_more', $this->repeat_element_num, get_string('listitemaddmore', 'resourcelist'),
                               false, $repeatnomax);
    
        $this->standard_coursemodule_elements(array('groups'=>false, 'groupmembersonly'=>true, 'gradecat'=>false));

        // Make the name field of the first item 'required'.
        $mform->addRule('listitemname[0]', null, 'required', null, 'client');

        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        global $CFG, $RESOURCE_WINDOW_OPTIONS;
        $alloptions = $RESOURCE_WINDOW_OPTIONS;

        if (!empty($this->_instance)) {
            $reslistitems = get_records('resourcelist_items', 'resourcelistid', $this->_instance, 'id');
      
            $i = 0;

            foreach ($reslistitems as $item) {
                if (! $resitem = get_record('resource', 'id', $item->resourceid)) {
                    error("Cannot find resource id, $item->resourceid");
                }
	
                if (is_object($resitem)) {
                    $resitem = (array)$resitem;
                }

                $res = new resource_file;
                $res->setup_preprocessing($resitem);

                $resfields = array('id', 'name', 'reference', 'forcedownload', 'windowpopup', 'framepage', 'type');
	
                foreach($resfields as $field) {
                    if (! empty($resitem[$field])) {
                        $listitemfield = 'listitem'.$field;
                        /* if ($field == 'reference') { */
                        /*   $default_values[$listitemfield."[$i]"] = $resitem[$field]; */
                        /* } else { */
                        /*   $default_values[$listitemfield][$i] = $resitem[$field]; */
                        /* } */
                        $default_values[$listitemfield."[$i]"] = $resitem[$field];
                    }
                }
                foreach($alloptions as $option) {
                    if ( isset($resitem[$option])) {
                        $listitemoption = 'listitemwindow'.$option;
                        $default_values[$listitemoption."[$i]"] = $resitem[$option];
                    }
                }
                $i++;
            }
        }
    }
}

?>