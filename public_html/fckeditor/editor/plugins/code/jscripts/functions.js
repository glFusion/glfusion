/*
 * Code Embed
 * Copyright (C) 2003-2009  Mark R. Evans
 *
 * Licensed under the terms of the GNU General Public License:
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 * For further information visit:
 * 		http://www.glfusion.org
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: functions.js
 * 	This file provides the necessary JavaScript routines for the
 *  code embed FCKeditor plugin.
 *
 * File Authors:
 * 		Mark R. Evans (mark@glfusion.org)
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

    tag = '<pre>' + embedString + '</pre>';

	return tag;
}