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
 * settings.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */

require_once($CFG->dirroot.'/mod/resourcelist/lib.php');

$settings->add(new admin_setting_configtext('resourcelist_listmax', get_string('listmax', 'resourcelist'),
                                            get_string('configlistmax', 'resourcelist'), 20, PARAM_INT));
$settings->add(new admin_setting_configtext('resourcelist_addmoreno', get_string('addmoreno', 'resourcelist'),
                                            get_string('configaddmoreno', 'resourcelist'), 3, PARAM_INT));

?>