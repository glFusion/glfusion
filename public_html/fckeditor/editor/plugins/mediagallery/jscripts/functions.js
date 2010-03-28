/*
 * Media Gallery - The ultimate gallery plugin for Geeklog
 * Copyright (C) 2003-2010  Mark R. Evans
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
 *  media browser FCKeditor plugin.
 *
 * File Authors:
 * 		Mark R. Evans (mark@glfusion.org)
 */



function insertImage(obj) {
	imagehtml=makeHtmlForInsertion(obj);
	if (imagehtml == false ) {
		return false;
	}
	FCK.InsertHtml(imagehtml);
	window.close();
}

function makeHtmlForInsertion(obj){
	var tag = '';
	var autotag = '';
	var width = '';
	var border = '';
	var alignment = '';
	var source = '';
	var link = '';
	var autoplay = '';
	var caption = '';
	var aid = 0;
	var thumbnail = '';
	var mid = '';
	var dest = '';
	var alturl = '';
	var ribbon = '';
	var showtitle = '';

	// see which auto tag is selected...
	for (i=0;i<obj.autotag.length;i++) {
	    if ( obj.autotag[i].checked ) {
	        autotag = obj.autotag[i].value;
	    }
	}
	if ( autotag == '' ) {
	    alert(lang.no_autotag);
	    return false;
	}

	width 	  = obj.width.value;
	height    = obj.height.value;
	border 	  = obj.border.value;
	alignment = obj.alignment.value;
	source 	  = obj.source.value;
	link 	  = obj.link.value;
	autoplay  = obj.autoplay.value;
	caption   = obj.caption.value;
	delay     = obj.delay.value;
	aid 	  = obj.aid.value;
	alturl    = obj.alturl.value;
	if (obj.dest != undefined) {
	    dest = obj.dest.value;
	}
	if ( obj.ribbon != undefined ) {
	    ribbon = obj.ribbon.value;
	}
	if ( obj.showtitle != undefined ) {
	    showtitle = obj.showtitle.value;
	}

	switch ( autotag ) {
	    case 'album' :
	        if ( aid == '' ) {
	            alert(lang.no_album);
	            return false;
	        }
	        tag = "[" + autotag + ":" + aid;
	        if ( width != '' ) {
	            tag += " width:" + width;
	        }
	        if ( height != '' ) {
	            tag += " height:" + height;
	        }
	        tag += " border:" + border;
	        tag += " align:" + alignment;
	        if ( source != 'tn') {
	            tag += " src:" + source;
	        }
	        if ( link != '' ) {
	            tag += " link:" + link;
	        }
	        if ( dest == 'block' ) {
	            tag += " dest:block";
	        }
	        if ( alturl != '' ) {
	            tag += " alt:" + alturl;
	        }
	        if ( caption != '' ) {
	          tag += " " + caption;
	        }
	        tag += "]";
	        break;
	    case 'playall' :
	        if ( aid == '' ) {
	            alert(lang.no_album);
	            return false;
	        }
	        tag = "[" + autotag + ":" + aid;
	        if ( autoplay != '' ) {
	        	tag += " autoplay:" + autoplay;
	        }
	        tag += " align:" + alignment;
	        tag += "]";
	        break;
	    case 'slideshow' :
	    case 'fslideshow' :
	        if ( aid == '' ) {
	            alert(lang.no_album);
	            return false;
	        }
	        tag = "[" + autotag + ":" + aid;
	        if ( width != '' ) {
	            tag += " width:" + width;
	        }
	        if ( height != '' ) {
	            tag += " height:" + height;
	        }
	        if ( delay != '' ) {
	            tag += " delay:" + delay;
	        }
	        tag += " border:" + border;
	        tag += " align:" + alignment;
	        if ( source != 'tn') {
	            tag += " src:" + source;
	        }
	        if ( link != '' ) {
	            tag += " link:" + link;
	        }
	        if ( dest == 'block' ) {
	            tag += " dest:block";
	        }
	        if ( showtitle == 'top' ) {
	            tag += " title:top";
	        }
	        if ( showtitle == 'bottom' ) {
	            tag += " title:bottom";
	        }

	        if ( caption != '' ) {
	          tag += " " + caption;
	        }
	        tag += "]";
	        break;
	    case 'media' :
	    case 'mlink' :
	    	// find the selected media id...
	    	if ( obj.thumbnail.length == null ) {
		    	if ( obj.thumbnail.checked ) {
			    	mid = obj.thumbnail.value;
		    	}
	    	} else {
				for (i=0;i<obj.thumbnail.length;i++) {
				    if ( obj.thumbnail[i].checked ) {
				        mid = obj.thumbnail[i].value;
				    }
				}
			}
			if ( mid == '' ) {
			    alert(lang.no_media);
			    return false;
			}
	    	tag = "[" + autotag + ":" + mid;
	        if ( width != '' ) {
	            tag += " width:" + width;
	        }
	        if ( height != '' ) {
	            tag += " height:" + height;
	        }
	        tag += " border:" + border;
	        tag += " align:" + alignment;
	        if ( source != 'tn') {
	            tag += " src:" + source;
	        }
	        if ( link != '' ) {
	            tag += " link:" + link;
	        }
	        if ( dest == 'block' ) {
	            tag += " dest:block";
	        }
	        if ( alturl != '' ) {
	            tag += " alt:" + alturl;
	        }
	        if ( caption != '' ) {
	          tag += " " + caption;
	        }
	        tag += "]";
	        break;
	    case 'img' :
	    	// find the selected media id...
	    	if ( obj.thumbnail.length == null ) {
		    	if ( obj.thumbnail.checked ) {
			    	mid = obj.thumbnail.value;
		    	}
	    	} else {
				for (i=0;i<obj.thumbnail.length;i++) {
				    if ( obj.thumbnail[i].checked ) {
				        mid = obj.thumbnail[i].value;
				    }
				}
			}
			if ( mid == '' ) {
			    alert(lang.no_media);
			    return false;
			}

	    	tag = "[" + autotag + ":" + mid;
	        if ( width != '' ) {
	            tag += " width:" + width;
	        }
	        if ( height != '' ) {
	            tag += " height:" + height;
	        }
	        tag += " align:" + alignment;
	        if ( source != 'tn') {
	            tag += " src:" + source;
	        }
	        if ( link != '' ) {
	            tag += " link:" + link;
	        }
	        if ( dest == 'block' ) {
	            tag += " dest:block";
	        }
	        if ( alturl != '' ) {
	            tag += " alt:" + alturl;
	        }
	        tag += "]";
	        break;
	    case 'video' :
	    	// find the selected media id...
	    	if ( obj.thumbnail.length == null ) {
		    	if ( obj.thumbnail.checked ) {
			    	mid = obj.thumbnail.value;
		    	}
	    	} else {
				for (i=0;i<obj.thumbnail.length;i++) {
				    if ( obj.thumbnail[i].checked ) {
				        mid = obj.thumbnail[i].value;
				    }
				}
			}
			if ( mid == '' ) {
			    alert(lang.no_media);
			    return false;
			}

	    	tag = "[" + autotag + ":" + mid;
	        if ( width != '' ) {
	            tag += " width:" + width;
	        }
	        if ( height != '' ) {
	            tag += " height:" + height;
	        }
	        tag += " border:" + border;
	        tag += " align:" + alignment;
	        if ( source != 'tn') {
	            tag += " src:" + source;
	        }
	        if ( autoplay != '' ) {
	        	tag += " autoplay:" + autoplay;
	        }
	        if ( dest == 'block' ) {
	            tag += " dest:block";
	        }
	        if ( caption != '' ) {
	          tag += " " + caption;
	        }
	        tag += "]";
	        break;

	    case 'audio' :
	    	// find the selected media id...
	    	if ( obj.thumbnail.length == null ) {
		    	if ( obj.thumbnail.checked ) {
			    	mid = obj.thumbnail.value;
		    	}
	    	} else {
				for (i=0;i<obj.thumbnail.length;i++) {
				    if ( obj.thumbnail[i].checked ) {
				        mid = obj.thumbnail[i].value;
				    }
				}
			}
			if ( mid == '' ) {
			    alert(lang.no_media);
			    return false;
			}

	    	tag = "[" + autotag + ":" + mid;
	        if ( width != '' ) {
	            tag += " width:" + width;
	        }
	        if ( height != '' ) {
	            tag += " height:" + height;
	        }
	        tag += " border:" + border;
	        tag += " align:" + alignment;
	        if ( source != 'tn') {
	            tag += " src:" + source;
	        }
	        if ( autoplay != '' ) {
	        	tag += " autoplay:" + autoplay;
	        }
	        if ( dest == 'block' ) {
	            tag += " dest:block";
	        }
	        if ( ribbon == 1 ) {
	            tag += " type:ribbon";
	        }
	        if ( caption != '' ) {
	          tag += " " + caption;
	        }
	        tag += "]";
	        break;
	    case 'playall' :
	        if ( aid == '' ) {
	            alert(lang.no_album);
	            return false;
	        }
	        tag = "[" + autotag + ":" + aid;
	        if ( autoplay != '' ) {
	        	tag += " autoplay:" + autoplay;
	        }
	        tag += " align:" + alignment;
	        tag += "]";
	        break;

	}
	return tag;
}