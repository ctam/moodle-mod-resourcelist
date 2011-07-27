Resource List for Moodle 1.9.9
------------------------------
This module is designed and developed by The University of California,
San Francisco.

For copyrights and license information please refer to this file,
"BSD_License.txt", which is included in the distribution.

For copyrights and license information on the patch file, moodle19.patch,
which modifies part of the Moodle's core code, please refer to the GPL
license, "COPYING.txt", distributed with the Moodle source code.

ABOUT THIS MODULE
-----------------
Resource List is a new Activity Module type in Moodle. It can be
created and added to a Course from the 'Add a resource' drop down
list. Resource List provides a way for instructors to group course
resources into a single list. If the web browser is Javascript
enabled, students will see a collapsed list by default. This allows
instructors present a more condensed course to their students. If
Javascript is disabled on the browser, Resource List is always
presented as an expanded list.

Currently only 'Link to files or websites' can be added to a Resource
List.


UPGRADE INSTRUCTIONS
--------------------
1. Please follow the Removal Instructions to remove the previously
installed Resource List

2. Follow Installation Instructions to install the newer version of
Resource List


INSTALLATION INSTRUCTIONS
-------------------------
1. Load the resourcelist module directory into "<moodle root>/mod"
   directory.  (Make sure directory is called 'resourcelist', not 
   'moodle-mod-resourcelist'.)

2. Go to the "<moodle root>/mod/resourcelist" directory and run the
   following command:

   $ patch -b -p0 -i moodle19.patch

3. If your theme uses customized pix, copy these files, "icon.gif",
   "switch_plus.gif", and "switch_minus.gif" to the "<moodle
   root>/theme/<your theme>/pix/mod/resourcelist/" directory. 
   e.g.

   $ cp *.gif ../../theme/<your theme>/pix/mod/resourcelist/

   * Check the permission on these files, make sure Apache has read 
   access right to them.

4. Visit your admin page to create all the necessary data tables.


REMOVAL INSTRUCTIONS
--------------------
Note: You can either hide or delete this resourcelist module from
Moodle by visiting the "Modules/Activities/Manage activites"
page. Deleting it there will remove all resourcelists that were added
to any course earlier.  If you would like to preserve them in all the
courses that you may re-install resourcelist in the future, you should
hide it instead of delete it.

To remove patch and files from disk:

1. Go to "mod/resourcelist" directory and run the following command:

      $ patch -R -p0 -i moodle19.patch

2. Remove files from your theme directory.
   Go to "<moodle root>/theme/<yourtheme>/pix/mod/" and remove the
   "resourcelist" directory.

3. Remove "<moodle root>/mod/resourcelist" directory all together.


ABOUT THE PATCH FILE
--------------------
Because of the constraints in Moodle's course implementation, it is
impossible to display multiple items under an Activity Module, which
is what the Resource List is built upon.  The following is a list
changes in the patch that are necessary for the Resource List to
function correctly with a course.

1. Modified print_section function in course/lib.php to handle
   printing of the Resource List along with its resources on the
   Course viewing page.  Also skip showing the resources that are
   already printed under a Resource List.

2. Added required styling and javascript to course/view.php file.

3. Modified course/rest.php to handle Ajax calls on expand and
   collapse of a Resource List.  Stored expand state into SESSION
   object. 

4. Changed course/mod.php and course/modedit.php to correctly handle
   Resource List during course editing.

5. Extended repeat_elements function in lib/formslib.php to handle
   more repeat attributes: type, grouprule, advanced, and multiple
   disabledif (disableifs).


ADDED FEATURES AND BUG FIXES
----------------------------
2011.07.25
* Added lines that lead to each list item under Resource List.

2011.01.24
* Eliminated javascript message "Can't move focus to the control..." from IE 8's error log.
* Fixed unsupported javascript function calls in IE 7.

2010.11.10
* The expand state of a Resource List is now persistent throughout a single login session.
* Updated Resource List's static icon.
* Added help file.

2010.10.19
* Fixed icon issues when theme does not use customized pix.
* Removed yui css defaults so that text color and cursor pointer would be 
  consistent with the theme.
* Removed groups icon when editing is turned on, as it has no effect on Resource List.

2010.10.07
* Fixed stylesheet conflicts with YUI Menu block
* Switched back to Moodle's YUI Treeview 2.6.0 instead of the latest Yahoo version, 2.8.1.
* Removed links and tags around the parent node's text because it is not supported in 2.6.0.

2010.10.04
* Added backup and restore functionalities.
* Added logging to 'view' action on Resource List
* Added text to setting page for resourcelist_listmax to indicate 0 value for 
  unlimited number of entries in a Resource List.

2010.09.21
* Updated README file with Uninstall instruction.
* Added BSD licensing information.

2010.09.14
* Added new icons and updated old icon to the correct size (16x16).
* Added settings page to Resource List, allowing administrator to set a limit to
  the number of items in a Resource List, and change the number entries to be 
  added each time.

2010.08.31
* Created a patch file to the core course codes instead of modifying the theme
  files.
* When Javascript is disabled, Resource List will display in expanded mode.
* Removed highlight on selected nodes.
* Corrected issue with moving Resource List to a different Course/Section.
* Fixed issue with adding new resources to existing Resource List.

2010.08.18
* Fixed bug: Failed to remove individual item from the list
* Added icon for Activities Block
* Collapsed/Expanded list: Changed behavior of clicking text title. 
  It will now expand/collapse the list instead of bring them to the Resource List page.
* Collapsed list: Removed extra "dots" on top
* Expanded list: Removed highlighting/selected region
* Resource list page: Disabled first link
