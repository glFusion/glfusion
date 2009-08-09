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

	fixedEmbedString = HTMLEncode(embedString);

    tag = '<pre>' + fixedEmbedString + '</pre>';

	return tag;
}

function HTMLEncode(text) {
    if (!text)
        return '';

    text = text.replace(/&/g, '&amp;');
    text = text.replace(/</g, '&lt;');
    text = text.replace(/>/g, '&gt;');

    return text;
}

function HTMLDecode(text) {
    if (!text)
        return '';

    text = text.replace(/&gt;/g, '>');
    text = text.replace(/&lt;/g, '<');
    text = text.replace(/&amp;/g, '&');
    text = text.replace(/<br>/g, '\n');
    text = text.replace(/&quot;/g, '"');

    return text;
}
