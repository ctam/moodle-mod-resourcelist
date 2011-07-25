// Copyright © 2011 The Regents of the University of California.
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
 * collapse-resourcelist.js
 * 
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license BSD License
 * @package mod_resourcelist
 */

var resourceListTrees = new Array();
var expandedTrees = new Array();

/**
 * This function convert a <li> element and its contents into a format
 * structure that can be used by YUI Treeview.  It uses the module list 
 * stored in the 'modules' attribute to find the corresponding <li> 
 * elements and appends them to as children.
 */
function convertElementToList(el) {

    // Create an id for this tree
    var treeid = el.getAttribute('id') + "-tree";

    // Create the essential tree nodes
    var divTreeSkin = document.createElement('div');
    var divTree = document.createElement('div');
    var ulResourceList = document.createElement('ul');
    var liResourceList = document.createElement('li');
    var ulResourceItems = document.createElement('ul');

    // Set node attributes
    divTreeSkin.setAttribute('class', 'yui-skin-sam');
    divTree.setAttribute('id', treeid);

    // Build the tree structure
    divTreeSkin.appendChild( divTree );
    divTree.appendChild( ulResourceList );
    ulResourceList.appendChild( liResourceList );

    // Build a list for each resource in the Resource List
    var spanCmds = document.createElement('span');
    var marginWidth = 0;

    // examine each child, create the tree struct under liResourceList,
    // move the Commands Span to a spanCmds placeholder, which will later
    // append to the end of el, remove all Img nodes, and append the 
    //rest to liResourceList.
    while (el.firstChild) {
	if (el.firstChild.nodeName == "SPAN") {
	    if (el.firstChild.className == 'commands') {
		// Override javascript functions on editing_moveleft and editing_moveright
		for (var i=0; i<el.firstChild.childNodes.length; i++) {
		    var aCmd = el.firstChild.childNodes[i];
		    var cmdClass = aCmd.className;

		    if (cmdClass == "editing_moveleft") {
			YAHOO.util.Event.removeListener( aCmd, 'click' );
			YAHOO.util.Event.addListener( aCmd, 'click', moveLeft, el, true );
		    } else if (cmdClass == "editing_moveright") {
			YAHOO.util.Event.removeListener( aCmd, 'click' );
			YAHOO.util.Event.addListener( aCmd, 'click', moveRight, el, true );
		    }
		}
		spanCmds.appendChild( el.firstChild );
	    } else {
		var strMod = el.firstChild.getAttribute('modules');		
		if (strMod) {
		    var modules = strMod.split(',');

		    for (var i=0; i < modules.length; i++) {
			var liMod = document.getElementById( modules[i] );
			
			// remove leading space & edit controls
			var j = liMod.childNodes.length;
			while (j--) {
			    var node = liMod.childNodes[j];
			    var nodeClass = node.className;

			    if ((nodeClass == "spacer") || (nodeClass == "commands")) {
				liMod.removeChild( node );
			    }
			}
			ulResourceItems.appendChild( liMod );
		    }
		    el.firstChild.removeAttribute('modules');

		    if (el.firstChild.firstChild && el.firstChild.firstChild.nodeValue) {
			var nodeText = document.createTextNode(el.firstChild.firstChild.nodeValue);
			var spanText = document.createElement('span');
			var ahref = document.createElement('a');
			ahref.setAttribute('href', 'javascript:void(0);');
			spanText.appendChild( nodeText );
			ahref.appendChild( spanText );
			liResourceList.appendChild( ahref );
		    }
		}
		var isExpanded = el.firstChild.className;
		if (isExpanded == "expanded")
		    expandedTrees[treeid] = true;
		el.removeChild(el.firstChild);
	    }
	} else if (el.firstChild.nodeName == "IMG") {
	    // We strip out all images, mainly the icon and spacer
	    if (el.firstChild.className == "spacer") {
		marginWidth += parseInt(el.firstChild.getAttribute('width'));
	    }
	    el.removeChild(el.firstChild);
	} else {
	    el.removeChild(el.firstChild);
	}
    }
    
    // Has to append ulResourceItems here, after the Span text to have the tree render correctly.
    liResourceList.appendChild( ulResourceItems );

    // el should have no child left, append divTreeSkin and Commands Span if present
    el.appendChild( divTreeSkin );

    // Append Commands spans to the el
    while (spanCmds.firstChild) {
    	el.appendChild( spanCmds.firstChild );
    }

    // Set the margin width for el for proper indention.
    if (marginWidth) {
	var styleValue = "margin-left:" + marginWidth + "px;";
	el.setAttribute("style", styleValue);
    }

    return treeid;
}

/**
 * This function iterates through all the elements with 'activity resourcelist' class in the document
 * and pass them to convertElementToList to build the YUI treeview.
 */
function buildResourceListTrees() {

    var listitems = null;

    if (document.getElementsByClassName) {
	listitems = document.getElementsByClassName("activity resourcelist");
    } else {
	liArray = document.getElementsByTagName('li');
	listitems = new Array();
	for (var i = 0; i < liArray.length; i++) {
	    if ((liArray[i].className == "activity resourcelist") ||
		(liArray[i].getAttribute('class') == "activity resourcelist") ||
		(liArray[i].getAttribute('className') == "activity resourcelist")) {
		listitems.push( liArray[i] );
	    }
	}
    }

    for (var i = 0; i  < listitems.length; i++) {
	var treeid = convertElementToList(listitems[i]);
	if (treeid) {
	    var modid = listitems[i].id.split('-')[1];
	    var resourcelist_tree = new YAHOO.widget.TreeView(treeid);
    	    resourceListTrees.push( resourcelist_tree );
	    if (typeof expandedTrees[treeid] != 'undefined') {
		resourcelist_tree.expandAll();
	    }
	    resourcelist_tree.subscribe("expandComplete", 
					function(node, mod){ 
					    YAHOO.util.Connect.asyncRequest('POST', resourcelist_wwwroot + '/course/rest.php?courseId='+resourcelist_courseid+"&sesskey="+resourcelist_sesskey+'&class=resource&field=expand',null,'id='+mod.id);
					},
					{id:modid});
	    resourcelist_tree.subscribe("collapseComplete", 
					function(node, mod){ 
					    YAHOO.util.Connect.asyncRequest('POST', resourcelist_wwwroot + '/course/rest.php?courseId='+resourcelist_courseid+"&sesskey="+resourcelist_sesskey+'&class=resource&field=collapse',null,'id='+mod.id);
					},
					{id:modid});
  	    resourcelist_tree.render();
	}
    }
}

/**
 * This function replaces the Moodle's default ajax moveLeft function so that
 * a Resource List can be indented correctly.
 */
function moveLeft(e) { 
    var strWidth = this.style.marginLeft;
    var marginWidth = parseInt(strWidth.substr(0, strWidth.indexOf('px')));

    if (marginWidth > 20) {
	marginWidth -= 20;
    } else {
	var commandContainer = YAHOO.util.Dom.getElementsByClassName('commands', 'span', this)[0];
	var leftButton = YAHOO.util.Dom.getElementsByClassName('editing_moveleft', 'a', commandContainer)[0];
	commandContainer.removeChild(leftButton);

	marginWidth = 0;
    }

    var modId = this.id.split('-')[1];

    this.style.marginLeft = marginWidth.toString() + "px";    
    main.connect('POST', 'class=resource&field=indentleft', null, 'id='+modId);
    return true; 
}

/**
 * This function replaces the Moodle's default ajax moveRight function so that
 * a Resource List can be indented correctly.
 */
function moveRight(e) { 
    // for RTL support
    var isrtl = (document.getElementsByTagName("html")[0].dir=="rtl");

    var strWidth = this.style.marginLeft;
    var marginWidth = 0;

    if (strWidth) {
    	marginWidth = parseInt(strWidth.substr(0, strWidth.indexOf('px')));
    }

    var commandContainer = YAHOO.util.Dom.getElementsByClassName('commands', 'span', this)[0];
    var leftButton = YAHOO.util.Dom.getElementsByClassName('editing_moveleft', 'a', commandContainer)[0];
    var rightButton = YAHOO.util.Dom.getElementsByClassName('editing_moveright', 'a', commandContainer)[0];

    if (!leftButton) {
        var button = main.mk_button('a', (isrtl?'/t/right.gif':'/t/left.gif'), main.portal.strings['moveleft'],
                [['class', 'editing_moveleft']]);
        YAHOO.util.Event.addListener(button, 'click', moveLeft, this, true);
        commandContainer.insertBefore(button, rightButton);
    }	

    var modId = this.id.split('-')[1];

    marginWidth += 20;

    this.style.marginLeft = marginWidth.toString() + "px";
    main.connect('POST', 'class=resource&field=indentright', null, 'id='+modId);
    return true; 
}

YAHOO.util.Event.onDOMReady(buildResourceListTrees);
