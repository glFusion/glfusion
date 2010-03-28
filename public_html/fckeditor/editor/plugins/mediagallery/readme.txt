Media Gallery Media Browser FCKeditor Plugin - version 1.6.0

Author: Mark R. Evans mark@glfusion.org

ChangeLog:

v1.6.0
    - Updated to latest auto tag options for Media Gallery

v1.5.1

    - Fixed a bug that would cause {mid} to be inserted into the story
      instead of the media id when using the Caching Template Library.
    - Added playall: auto tag.

v1.5

    - Updated to support all auto tag functions of
      Media Gallery v1.5.0

v1.4

    - Added new features found in Media Gallery v1.4.9
      - fslideshow auto tag

v1.3

	- Fixed javascript error 'autotag' not defined
	- initialize the state of the auto tag attributes on page load

v1.2

  	- Save the state of the auto tag attributes if you change the albums

v1.1

  	- Path detection would not work properly on systems where GL was not
      installed in the webroot
  	- Javascript errors if no media item or album was selected
  	- Hardcoded javascript strings and now in a separate language file
  	- added img auto tag
  	- prettier MG toolbar icon

v1.0 - Initial Release

Overview

Media Gallery Media Browser is a plugin for the FCKeditor that is included
with Geeklog v1.4.1. This plugin will allow you to easily insert Media Gallery
auto tags into your stories and static pages. Once installed, simply press the
MG button on your editor toolbar to open the Media Browser. Select the type of
auto tag, attribute and the media item, press INSERT and the auto tag will be
placed in the editor window.

Requirements

mgMedia Browser was developed using Geeklog v1.4.1 and Media Gallery v1.4.8.
It may work with earlier versions of both Geeklog and Media Gallery. If using
an earlier release of Geeklog, the installation instructions for adding the
MG icon to the FCKeditor toolbars may not be accurate. You will need to find
the proper files that define the menus and perform the edits on those files.

Installation

Unarchive or FTP mbmediabrowser-1.3.zip to the following directory

    public_html/fckeditor/editor/plugins/

This should create a new directory under the plugins/ directory called
mediagallery/

Edit public_html/fckeditor/fckconfig.js

Around line 77 add

FCKConfig.Plugins.Add(’mediagallery’);

While editing fckconfig.js, add the Media Galler Media Browser icon to the
“Default” FCKeditor toolbar. Find the toolbar, it will begin with
FCKConfig.ToolbarSets[”Default”] = [

Add ‘mediagallery’ after the ‘Image’ button, so your toolbar will look like
this:

  FCKConfig.ToolbarSets["Default"] = [
    ['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
    ['Smiley','SpecialChar','PageBreak','UniversalKey'], ['TextColor','BGColor'],
    ['Image','mediagallery','Flash','Table','Rule'],
    '/',
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['OrderedList','UnorderedList','-','Outdent','Indent'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['Link','Unlink','Anchor'],['FitWindow','-','About'],
    '/',
    ['Style','FontFormat','FontName','FontSize']
  ] ;


Edit public_html/fckeditor/myconfig.js

You will want to add the mgMedia Browser Icon to all the standard Geeklog
FCKeditor toolbars:

I prefer to add ‘mediagallery’ right after the existing ‘Image’ button.

NOTE:

“editor-toolbar1” does not have an existing ‘Image’ button. This is the
default toolbar that is used for comments. You will need to decide if you want
 to add the mgMedia Browser button to this toolbar.

If you add the mgMedia Browser button to toolbar1, it will look something like
this:

  FCKConfig.ToolbarSets["editor-toolbar1"] = [
    ['Source','-','Undo','Redo','-','Link','Unlink','-','Bold','Italic',
    '-','JustifyLeft','JustifyCenter','JustifyRight','mediagallery',
    '-','OrderedList','UnorderedList','Outdent','Indent','FitWindow','About']
  ] ;

For the “editor-toolbar2” and “editor-toolbar3” you can add the mgMediaBrowser
button after the ‘Image’ button. Your new toolbars will look like this:

  FCKConfig.ToolbarSets["editor-toolbar2"] = [
    ['Source','-','Undo','Redo','-','Link','Unlink','-','Bold','Italic','Underline','StrikeThrough',
    '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull',
    '-','OrderedList','UnorderedList','Outdent','Indent'],
    ['PasteText','PasteWord','-','FontName','FontSize','TextColor','BGColor','-','Rule','-','Image','mediagallery','Table','FitWindow','-','About']
  ] ;

  FCKConfig.ToolbarSets["editor-toolbar3"] = [
      ['Source','Templates','-','Cut','Copy','Paste','PasteText','PasteWord','-',
      'Find','Replace','-','Undo','Redo','-','RemoveFormat','-','Link','Unlink','-',
      'Image','mediagallery','SpecialChar','-','Print','SpellCheck','FitWindow'],
      ['Table','Rule','Bold','Italic','Underline','StrikeThrough','-',
      'Subscript','Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-',
      'OrderedList','UnorderedList','-','Outdent','Indent','-','TextColor','BGColor','-','About'],
      ['Style','-','FontFormat','-','FontName','-','FontSize']
  ] ;

Save the file and you are done!

Upgrading

Upgrading is very simple, simply copy the new files over the old files.
You should not need to edit any of the FCKeditor files, just replace all the
files in the fckeditor/editor/plugins/mediagallery/ directory.

NOTE:  You will need to clear your browser's cache after upgrading.  I have
found that the Javascript routines are generally cached, so clear your cache
just to be on the safe side.

Configuration

You can set the default values for the auto tags by editing the values in the
public_html/fckeditor/editor/plugins/mediagallery/config.php file.

Usage

Fire up your story editor using the Advanced Editor and look for the new MG
toolbar button. Click on the button and the Media Browser window will pop-up.
Select the type of auto tag to add, set the auto tag attributes and finally
select a media item, press INSERT and you should have an auto tag
automatically inserted into your editor window.

