// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | forum_fckeditor.js                                                       |
// |                                                                          |
// | Javascript functions for FCKEditor Integration into glFusion's           |
// | Forum plugin                                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
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
        var oFCKeditor1 = new FCKeditor( 'comment' ) ;
        if (undefined == window.glfusionEditorBaseUrl) {
            glfusionEditorBaseUrl = geeklogEditorBaseUrl;
        }
        if (undefined == window.glfusionEditorBasePath) {
            glfusionEditorBasePath = geeklogEditorBasePath;
        }
        oFCKeditor1.BasePath = glfusionEditorBasePath;
        oFCKeditor1.Config['CustomConfigurationsPath'] = glfusionEditorBaseUrl + '/fckeditor/myconfig.js';
        if ( undefined != window.glfusionStyleBasePath ) {
            oFCKeditor1.Config['EditorAreaCSS'] = glfusionStyleCSS;
        }
        oFCKeditor1.Config['LinkBrowser'] = false;
        oFCKeditor1.Config['ImageBrowser'] = false;
        oFCKeditor1.Config['FlashBrowser'] = false;
        oFCKeditor1.Config['ImageUpload'] = false;
        oFCKeditor1.Config['LinkUpload'] = false;
        oFCKeditor1.Config['SkinPath'] = glfusionEditorBasePath + 'editor/skins/default/' ;

        oFCKeditor1.ToolbarSet = 'forum-toolbar' ;

        oFCKeditor1.Height = 400 ;
        oFCKeditor1.ReplaceTextarea() ;
    });

    function change_editmode(obj) {
        if (obj.value == 'html') {
            document.getElementById('text_editor').style.display='none';
            document.getElementById('html_editor').style.display='';
            swapEditorContent('html');
        } else {
            document.getElementById('text_editor').style.display='';
            document.getElementById('html_editor').style.display='none';
            swapEditorContent('text');
        }
    }

    function getEditorContent() {
        // Get the editor instance that we want to interact with.
        var oEditor = FCKeditorAPI.GetInstance('comment') ;
        // return the editor contents in XHTML.
        return oEditor.GetXHTML( true );
    }

    function emoticon_wysiwyg(text) {
        var oEditor = FCKeditorAPI.GetInstance('comment') ;
        oEditor.InsertHtml(text);
    }