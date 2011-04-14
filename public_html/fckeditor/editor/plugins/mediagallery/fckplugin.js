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
 * 	This plugin registers the Media Gallery media browswer plugin.
 * 	This requires that Media Gallery v1.4.x be installed on the Geeklog site.
 *
 * File Authors:
 * 		Mark R. Evans (mark@gllabs.org)
 */

function FCKeditor_OnComplete(editorInstance)
{
    editorInstance.EditorWindow.parent.IM_directEdit = true;
}

var mediagalleryCommand=function(){
	//create our own command, we dont want to use the FCKDialogCommand because it uses the default fck layout and not our own
};
mediagalleryCommand.prototype.Execute=function(){

}
mediagalleryCommand.GetState=function() {
    return FCK_TRISTATE_OFF; //we dont want the button to be toggled
}

mediagalleryCommand.Execute=function() {
    //open a popup window when the button is clicked

    window.open(FCKPlugins.Items['mediagallery'].Path + 'mediagallery.php?i=' + FCK.Name, 'mediagallery', 'width=600,height=700,resizable=yes,scrollbars=yes,scrolling=yes,location=no,toolbar=no');
}

FCKCommands.RegisterCommand('mediagallery', mediagalleryCommand ); //otherwise our command will not be found

var omediagallery = new FCKToolbarButton('mediagallery', 'Insert Media Gallery Auto Tag');
omediagallery.IconPath = FCKPlugins.Items['mediagallery'].Path + 'images/mediagallery.gif'; //specifies the image used in the toolbar

FCKToolbarItems.RegisterItem( 'mediagallery', omediagallery );