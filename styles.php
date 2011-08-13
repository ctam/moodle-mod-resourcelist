/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright © 2011 The Regents of the University of California.
 * All Rights Reserved.
 */

/**
 * styles.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_resourcelist
 */

.resourcelist .ygtvlm,
.resourcelist .ygtvlmh,
.resourcelist .ygtvlmhh {
    background:url(<?php echo $CFG->modpixpath ?>/resourcelist/switch_minus.gif) 0 0 no-repeat;
    width: 18px;
    height: 22px;
    cursor: pointer;
}

.resourcelist .ygtvlp,
.resourcelist .ygtvlph,
.resourcelist .ygtvlphh {
    background:url(<?php echo $CFG->modpixpath ?>/resourcelist/switch_plus.gif) 0 0 no-repeat;
    width: 18px;
    height: 22px;
    cursor: pointer;
}

.resourcelist .ygtvtn {
 width:18px;
 height:22px;
 background:url(<?php echo $CFG->wwwroot ?>/lib/yui/treeview/assets/treeview-sprite.gif) 0 -5600px no-repeat;
 }

.resourcelist .ygtvln {
 width:18px;
 height:22px;
 background:url(<?php echo $CFG->wwwroot ?>/lib/yui/treeview/assets/treeview-sprite.gif) 0 -1600px no-repeat;
 }

.resourcelist .ygtvblankdepthcell {
    width:18px;
    height:22px;
}    

.resourcelist a.ygtvspacer {
    outline: 0;
}

.mod-resourcelist .timemodified {
    text-align: center;
    font-size: 0.6em;
}
