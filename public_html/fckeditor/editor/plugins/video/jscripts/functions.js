/*
 * Video Embed
 * Copyright (C) 2003-2008  Mark R. Evans
 *
 * Licensed under the terms of the GNU General Public License:
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 * For further information visit:
 * 		http://www.gllabs.org
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: functions.js
 * 	This file provides the necessary JavaScript routines for the
 *  video embed FCKeditor plugin.
 *
 * File Authors:
 * 		Mark R. Evans (mark@gllabs.org)
 */

function insertEmbed(obj) {
	embedhtml=makeHtmlForInsertion1(obj);
	if (embedhtml == false ) {
		return false;
	}
	FCK.InsertHtml(embedhtml);
	window.close();
}

function makeHtmlForInsertion1(obj){
	var embedString;

	embedString = obj.embedstring.value;
	alignment   = obj.alignment.value;

	switch ( alignment ) {
	    case 'none' :
	        tag = embedString;
	        break;
        case 'right' :
        case 'left'  :
            tag = '<div style="float:' + alignment + ';padding:5px;">' + embedString + '</div>';
            break;
        case 'center' :
            tag = '<div style="text-align:center;padding:5px;">' + embedString + '</div>';
            break;
        default:
            tag = embedString;
            break;
    }
	return tag;
}