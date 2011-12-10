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
 * collapse-resourcelist.js
 * 
 * @copyright &copy; 2011 The Regents of the University of California
 * @author carson.tam@ucsf.edu
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
    var a = el.firstChild;

    if (YAHOO.util.Dom.hasClass(a, "dimmed")) 
	YAHOO.util.Dom.addClass(divTree, "dimmed_text");

    while (el.firstChild) {
	if (el.firstChild.nodeName == "SPAN") {
	    if (el.firstChild.className == 'commands') {
		// Override javascript functions on editing_moveleft and editing_moveright
		for (var i=0; i<el.firstChild.childNodes.length; i++) {
		    var aCmd = el.firstChild.childNodes[i];
		    var cmdClass = aCmd.className;
		    var cmdTitle = aCmd.title;

		    if (cmdClass == "editing_moveleft") {
			YAHOO.util.Event.removeListener( aCmd, 'click' );
			YAHOO.util.Event.addListener( aCmd, 'click', moveLeft_resourcelist, el, true );
		    } else if (cmdClass == "editing_moveright") {
			YAHOO.util.Event.removeListener( aCmd, 'click' );
			YAHOO.util.Event.addListener( aCmd, 'click', moveRight_resourcelist, el, true );
		    } else if ((cmdTitle == main.portal.strings['show']) ||
			       (cmdTitle == main.portal.strings['hide'])) {
			YAHOO.util.Event.removeListener( aCmd, 'click' );
			YAHOO.util.Event.addListener( aCmd, 'click', toggleHide_resourcelist, aCmd, el );
		    }
		}
		spanCmds.appendChild( el.firstChild );
	    }
	} else if (el.firstChild.nodeName == "A") {
	    var a = el.firstChild;

	    while (a.firstChild) {

		if (a.firstChild.nodeName == "SPAN") {
		    var strMod = a.firstChild.getAttribute('modules');		
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
			a.firstChild.removeAttribute('modules');

			if (a.firstChild.firstChild && a.firstChild.firstChild.nodeValue) {
			    var nodeText = document.createTextNode(a.firstChild.firstChild.nodeValue);
			    var spanText = document.createElement('span');
			    var ahref = document.createElement('a');
			    ahref.setAttribute('href', 'javascript:void(0);');
			    spanText.appendChild( nodeText );
			    ahref.appendChild( spanText );
			    liResourceList.appendChild( ahref );
			}
		    }
		    var isExpanded = a.firstChild.className;
		    if (isExpanded == "expanded")
			expandedTrees[treeid] = true;
		}
		a.removeChild(a.firstChild);
	    } 
	    el.removeChild(el.firstChild);
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
function moveLeft_resourcelist(e) { 
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
function moveRight_resourcelist(e) { 
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
        YAHOO.util.Event.addListener(button, 'click', moveLeft_resourcelist, this, true);
        commandContainer.insertBefore(button, rightButton);
    }	

    var modId = this.id.split('-')[1];

    marginWidth += 20;

    this.style.marginLeft = marginWidth.toString() + "px";
    main.connect('POST', 'class=resource&field=indentright', null, 'id='+modId);
    return true; 
}

function toggleHide_resourcelist(e, viewButton) {
    var strhide = main.portal.strings['hide'];
    var strshow = main.portal.strings['show'];
    var modId = this.id.split('-')[1];
    var tree = YAHOO.util.Dom.get(this.id + "-tree");

    if (viewButton.title == strshow) {
        YAHOO.util.Dom.removeClass(tree, 'dimmed_text');

        viewButton.childNodes[0].src = viewButton.childNodes[0].src.replace(/show.gif/i, 'hide.gif');
        viewButton.childNodes[0].alt = viewButton.childNodes[0].alt.replace(strshow, strhide);
        viewButton.title = viewButton.title.replace(strshow, strhide);

        main.connect('POST', 'class=resource&field=visible', null, 'value=1&id='+modId);
    } else {
        YAHOO.util.Dom.addClass(tree, 'dimmed_text');

        viewButton.childNodes[0].src = viewButton.childNodes[0].src.replace(/hide.gif/i, 'show.gif');
        viewButton.childNodes[0].alt = viewButton.childNodes[0].alt.replace(strhide, strshow);
        viewButton.title = viewButton.title.replace(strhide, strshow);

        main.connect('POST', 'class=resource&field=visible', null, 'value=0&id='+modId);
    }
    return true;
}

YAHOO.util.Event.onDOMReady(buildResourceListTrees);
