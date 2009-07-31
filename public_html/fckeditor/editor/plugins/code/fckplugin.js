/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 *
 * For further information visit:
 * 		http://www.fckeditor.net/
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: fckplugin.js
 * 	This plugin registers the code embed plugin.
  *
 * File Authors:
 * 		Mark R. Evans (mark@glfusion.org)
 */

function FCKeditor_OnComplete(editorInstance)
{
    editorInstance.EditorWindow.parent.IM_directEdit = true;
}

var codeCommand=function(){
	//create our own command, we dont want to use the FCKDialogCommand because it uses the default fck layout and not our own
};
codeCommand.prototype.Execute=function(){

}
codeCommand.GetState=function() {
    return FCK_TRISTATE_OFF; //we dont want the button to be toggled
}

codeCommand.Execute=function() {
    //open a popup window when the button is clicked

    window.open(FCKPlugins.Items['code'].Path + 'code.php?i=' + FCK.Name, 'code', 'width=533,height=430,resizable=yes,scrollbars=yes,scrolling=yes,location=no,toolbar=no');
}

FCKCommands.RegisterCommand('code', codeCommand ); //otherwise our command will not be found

var ocode = new FCKToolbarButton('code', 'Insert Embedded code');
ocode.IconPath = FCKPlugins.Items['code'].Path + 'images/code.gif'; //specifies the image used in the toolbar

FCKToolbarItems.RegisterItem( 'code', ocode );