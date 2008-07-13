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
 * 	This plugin registers the video embed plugin.
  *
 * File Authors:
 * 		Mark R. Evans (mark@gllabs.org)
 */

function FCKeditor_OnComplete(editorInstance)
{
    editorInstance.EditorWindow.parent.IM_directEdit = true;
}

var videoCommand=function(){
	//create our own command, we dont want to use the FCKDialogCommand because it uses the default fck layout and not our own
};
videoCommand.prototype.Execute=function(){

}
videoCommand.GetState=function() {
    return FCK_TRISTATE_OFF; //we dont want the button to be toggled
}

videoCommand.Execute=function() {
    //open a popup window when the button is clicked

    window.open(FCKPlugins.Items['video'].Path + 'video.php?i=' + FCK.Name, 'video', 'width=533,height=230,resizable=yes,scrollbars=yes,scrolling=yes,location=no,toolbar=no');
}

FCKCommands.RegisterCommand('video', videoCommand ); //otherwise our command will not be found

var ovideo = new FCKToolbarButton('video', 'Insert Embedded Video');
ovideo.IconPath = FCKPlugins.Items['video'].Path + 'images/video.gif'; //specifies the image used in the toolbar

FCKToolbarItems.RegisterItem( 'video', ovideo );