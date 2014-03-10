Media Gallery Browser Plugin for CKEditor 4
===========================================

Copyright © 2014 Mark R. Evans <mark AT glfusion DOT org>.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This plugin allow you to insert Media Gallery autotags into the editor.

How To Use
----------
The Media Gallery icon should appear on the CKEditor toolbar in the 'Insert' area.
Click it, a pop-up window will appear that allows you to select items from your
Media Gallery albums, automatically build the proper autotag and insert it into
the editor.

Configuration
-------------
You can set the default values for the auto tags by editing the values in the
public_html/ckeditor/plugins/mediagallery/config.php file.

Usage
-----
Fire up your story editor using the Advanced Editor and look for the new MG
toolbar button. Click on the button and the Media Browser window will pop-up.
Select the type of auto tag to add, set the auto tag attributes and finally
select a media item, press INSERT and you should have an auto tag
automatically inserted into your editor window.


[![Licensed under the GPLv2]http://www.opensource.org/licenses/gpl-license.php "Licensed under the GPLv2")](http://opensource.org/licenses/GPL-2.0)

Changelog
=========
v1.7.0
------
 - Updated to support CKEditor v4+

v1.6.0
------
 - Updated to latest auto tag options for Media Gallery

v1.5.1
------
 - Fixed a bug that would cause {mid} to be inserted into the story
   instead of the media id when using the Caching Template Library.
 - Added playall: auto tag.

v1.5
----
 - Updated to support all auto tag functions of
   Media Gallery v1.5.0

v1.4
----
 - Added new features found in Media Gallery v1.4.9
 - fslideshow auto tag

v1.3
----
 - Fixed javascript error 'autotag' not defined
 - initialize the state of the auto tag attributes on page load

v1.2
----
 - Save the state of the auto tag attributes if you change the albums

v1.1
----
 - Path detection would not work properly on systems where GL was not
   installed in the webroot
 - Javascript errors if no media item or album was selected
 - Hardcoded javascript strings and now in a separate language file
 - added img auto tag
 - prettier MG toolbar icon

v1.0 - Initial Release
----------------------

