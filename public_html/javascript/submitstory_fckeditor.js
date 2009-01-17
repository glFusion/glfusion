// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | submitstory_fckeditor.js                                                 |
// |                                                                          |
// | Javascript functions for FCKEditor Integration into glFusion             |
// | For user submitted stories.                                              |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2003-2008 by the following authors:                        |
// |                                                                          |
// | Authors:   Blaine Lang - blaine@portalparts.com                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

    var undefined;

    window.addEvent('load', function() {
        var oFCKeditor1 = new FCKeditor( 'introhtml' ) ;
        if (undefined == window.glfusionEditorBaseUrl) {
            glfusionEditorBaseUrl = geeklogEditorBaseUrl;
        }
        if (undefined == window.glfusionEditorBasePath) {
            glfusionEditorBasePath = geeklogEditorBasePath;
        }
        oFCKeditor1.BasePath = glfusionEditorBasePath;
        oFCKeditor1.Config['CustomConfigurationsPath'] = glfusionEditorBaseUrl + '/fckeditor/myconfig.js';
        oFCKeditor1.ToolbarSet = 'editor-toolbar4' ;
        if ( undefined != window.glfusionStyleBasePath ) {
            oFCKeditor1.Config['EditorAreaCSS'] = glfusionStyleCSS;
            oFCKeditor1.Config['StylesXmlPath'] = glfusionStyleBasePath + 'fckstyles.xml';
        }
        oFCKeditor1.Height = 300 ;
        oFCKeditor1.ReplaceTextarea() ;

        var oFCKeditor2 = new FCKeditor( 'bodyhtml' ) ;
        if (undefined == window.glfusionEditorBaseUrl) {
            glfusionEditorBaseUrl = geeklogEditorBaseUrl;
        }
        if (undefined == window.glfusionEditorBasePath) {
            glfusionEditorBasePath = geeklogEditorBasePath;
        }
        oFCKeditor2.BasePath = glfusionEditorBasePath ;
        oFCKeditor2.Config['CustomConfigurationsPath'] = glfusionEditorBaseUrl + '/fckeditor/myconfig.js';
        oFCKeditor2.ToolbarSet = 'editor-toolbar4' ;
        if ( undefined != window.glfusionStyleBasePath ) {
            oFCKeditor2.Config['EditorAreaCSS'] = glfusionStyleCSS;
            oFCKeditor2.Config['StylesXmlPath'] = glfusionStyleBasePath + 'fckstyles.xml';
        }
        oFCKeditor2.Height = 300 ;
        oFCKeditor2.ReplaceTextarea() ;
    });

    function change_editmode(obj) {
        if (obj.value == 'html') {
            document.getElementById('text_editor').style.display='none';
            document.getElementById('html_editor').style.display='';
            swapEditorContent('html','introhtml');
            swapEditorContent('html','bodyhtml');
        } else {
            document.getElementById('text_editor').style.display='';
            document.getElementById('html_editor').style.display='none';
            swapEditorContent('text','introhtml');
            swapEditorContent('text','bodyhtml');
        }
    }

    function getEditorContent(instanceName) {
        // Get the editor instance that we want to interact with.
        var oEditor = FCKeditorAPI.GetInstance(instanceName) ;
        // return the editor contents in XHTML.
        var content = '';
        try {
            content = oEditor.GetXHTML( true );
        } catch (e) {}

        return content;
    }

    function swapEditorContent(curmode,instanceName) {
        var content = '';
        var oEditor = FCKeditorAPI.GetInstance(instanceName) ;
        //alert(curmode + ':' + instanceName);
        if (curmode == 'html') { // Switching from Text to HTML mode
            // Get the content from the textarea 'text' content and copy it to the editor
            if (instanceName == 'introhtml' )  {
                content = document.getElementById('introtext').value;
                //alert('Intro :' + instanceName + '\n' + content);
            } else {
                content = document.getElementById('bodytext').value;
                //alert('HTML :' + instanceName + '\n' + content);
            }
            oEditor.SetHTML(content);
        } else {
               content = getEditorContent(instanceName);
              if (instanceName == 'introhtml' )  {
                  document.getElementById('introtext').value = content;
              } else {
                  document.getElementById('bodytext').value = content;
              }
          }
    }

    function set_postcontent() {
        if (document.getElementById('sel_editmode').value == 'html') {
            document.getElementById('introtext').value = getEditorContent('introhtml');
            document.getElementById('bodytext').value = getEditorContent('bodyhtml');
        }
    }
