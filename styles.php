/**
 * Copyright © 2011 The Regents of the University of California.
 * All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions 
 * are met:
 *   • Redistributions of source code must retain the above copyright 
 *     notice, this list of conditions and the following disclaimer.
 *   • Redistributions in binary form must reproduce the above copyright 
 *     notice, this list of conditions and the following disclaimer in the 
 *     documentation and/or other materials provided with the distribution.
 *   • None of the names of any campus of the University of California, 
 *     the name "The Regents of the University of California," or the 
 *     names of any of its contributors may be used to endorse or promote 
 *     products derived from this software without specific prior written 
 *     permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * styles.php
 *
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
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
