// +---------------------------------------------------------------------------+
// | Copyright (C) 2003,2004 by the following authors:                         |
// | Version 1.0    Date: Jun 4, 2005                                          |
// | Authors:   Blaine Lang - blaine@portalparts.com                           |
// |                                                                           |
// | Javascript functions for FCKEditor Integration into Geeklog                |
// |                                                                           |
// +---------------------------------------------------------------------------+

    var undefined;

    window.onload = function() {
        var oFCKeditor1 = new FCKeditor( 'introhtml' ) ;
        oFCKeditor1.BasePath = geeklogEditorBasePath;
        oFCKeditor1.Config['CustomConfigurationsPath'] = geeklogEditorBaseUrl + '/fckeditor/myconfig.js';
        oFCKeditor1.ToolbarSet = 'editor-toolbar1' ;
        if ( undefined != window.geeklogStyleBasePath ) {
            oFCKeditor1.Config['EditorAreaCSS'] = geeklogStyleBasePath + 'style.css';
            oFCKeditor1.Config['StylesXmlPath'] = geeklogStyleBasePath + 'fckstyles.xml';
        }
        oFCKeditor1.Height = 300 ;
        oFCKeditor1.ReplaceTextarea() ;

        var oFCKeditor2 = new FCKeditor( 'bodyhtml' ) ;
        oFCKeditor2.BasePath = geeklogEditorBasePath ;
        oFCKeditor2.Config['CustomConfigurationsPath'] = geeklogEditorBaseUrl + '/fckeditor/myconfig.js';
        oFCKeditor2.ToolbarSet = 'editor-toolbar1' ;
        if ( undefined != window.geeklogStyleBasePath ) {
            oFCKeditor2.Config['EditorAreaCSS'] = geeklogStyleBasePath + 'style.css';
            oFCKeditor2.Config['StylesXmlPath'] = geeklogStyleBasePath + 'fckstyles.xml';
        }
        oFCKeditor2.Height = 300 ;
        oFCKeditor2.ReplaceTextarea() ;
    }

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
        return oEditor.GetXHTML( true );
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
