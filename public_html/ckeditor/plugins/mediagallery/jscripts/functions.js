/*
 * Media Gallery Autotag Plugin
 *
 * @license Copyright (c) 2003-2020, Mark R. Evans. All rights reserved.
 * Licensed under the terms of the GNU General Public License
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 * @version 1.8.1
 */

var errorMessage = '';

function insertImage(obj) {
    imagehtml = makeHtmlForInsertion(obj);
    if (imagehtml == false) {
        return false
    }
    instance.insertHtml(imagehtml);
    window.close()
}

function makeHtmlForInsertion(obj) {

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
    var ribbon = '';
    var showtitle = '';
    var linksrc = '';
    var alttext = '';
    var alt = '';
    var classes = '';
    for (i = 0; i < obj.autotag.length; i++) {
        if (obj.autotag[i].checked) {
            autotag = obj.autotag[i].value
        }
    }
    if (autotag == '') {
        alert('You must select an autotag');
        return false
    }
    width = obj.width.value;
    height = obj.height.value;
    border = obj.border.value;
    alignment = obj.alignment.value;
    source = obj.source.value;
    link = obj.link.value;
    autoplay = obj.autoplay.value;
    caption = obj.caption.value;
    delay = obj.delay.value;
    aid = obj.aid.value;

    if (obj.classes != undefined ) {
        cls = obj.classes.value
        var clsarray = cls.split(" ");
        var arrayLength = clsarray.length;
        for (var i = 0; i < arrayLength; i++) {
            if (i != 0 ) classes += ','
            classes += clsarray[i]
        }
    }
    if (obj.dest != undefined) {
        dest = obj.dest.value
    }
    if (obj.ribbon != undefined) {
        ribbon = obj.ribbon.value
    }
    if (obj.showtitle != undefined) {
        showtitle = obj.showtitle.value
    }
    if (obj.link_src != undefined) {
        linksrc = obj.link_src.value
    }
    if (obj.alttext != undefined ) {
        var alttmp = obj.alttext.value;
        alt = alttmp.replace(/ /g,'_');
    }
    switch (autotag) {
        case 'album':
            if (aid == '') {
                alert(instance.lang.mediagallery.no_album);
//                alert('You must select an album');
                return false
            }
            tag = "[" + autotag + ":" + aid;
            if (width != '') {
                tag += " width:" + width
            }
            if (height != '') {
                tag += " height:" + height
            }
            tag += " border:" + border;
            tag += " align:" + alignment;
            if (source != 'tn') {
                tag += " src:" + source
            }
            if (link != '') {
                tag += " link:" + link
            }
            if (dest == 'block') {
                tag += " dest:block"
            }
            if (classes != '' ) {
                tag += " class:" + classes
            }
            if (alt != '' ) {
                tag += " tag:" + alt
            }
            if (caption != '') {
                tag += " " + caption
            }
            tag += "]";
            break;
        case 'playall':
            if (aid == '') {
                alert('You must select an album');
                return false
            }
            tag = "[" + autotag + ":" + aid;
            if (autoplay != '') {
                tag += " autoplay:" + autoplay
            }
            tag += " align:" + alignment;
            if (classes != '' ) {
                tag += " class:" + classes
            }
            tag += "]";
            break;
        case 'slideshow':
            if (aid == '') {
                alert('You must select an album');
                return false
            }
            tag = "[" + autotag + ":" + aid;
            if (width != '') {
                tag += " width:" + width
            }
            if (height != '') {
                tag += " height:" + height
            }
            if (delay != '') {
                tag += " delay:" + delay
            }
            tag += " border:" + border;
            tag += " align:" + alignment;
            if (source != 'tn') {
                tag += " src:" + source
            }
            if (link != '') {
                tag += " link:" + link
            }
            if (dest == 'block') {
                tag += " dest:block"
            }
            if (showtitle == 'top') {
                tag += " title:top"
            }
            if (showtitle == 'bottom') {
                tag += " title:bottom"
            }
            if (classes != '' ) {
                tag += " class:" + classes
            }
            if (caption != '') {
                tag += " " + caption
            }
            tag += "]";
            break;
        case 'media':
        case 'mlink':
            if (obj.thumbnail.length == null) {
                if (obj.thumbnail.checked) {
                    mid = obj.thumbnail.value
                }
            } else {
                for (i = 0; i < obj.thumbnail.length; i++) {
                    if (obj.thumbnail[i].checked) {
                        mid = obj.thumbnail[i].value
                    }
                }
            }
            if (mid == '') {
                alert('You must select a media item');
                return false
            }
            tag = "[" + autotag + ":" + mid;
            if (width != '') {
                tag += " width:" + width
            }
            if (height != '') {
                tag += " height:" + height
            }
            tag += " border:" + border;
            tag += " align:" + alignment;
            if (source != 'tn') {
                tag += " src:" + source
            }
            if (link != '') {
                tag += " link:" + link
            }
            if (dest == 'block') {
                tag += " dest:block"
            }
            if (linksrc != '') {
                tag += " src:" + linksrc
            }
            if (classes != '' ) {
                tag += " class:" + classes
            }
            if (alt != '' ) {
                tag += " tag:" + alt
            }
            if (caption != '') {
                tag += " " + caption
            }
            tag += "]";
            break;
        case 'img':
            if (obj.thumbnail.length == null) {
                if (obj.thumbnail.checked) {
                    mid = obj.thumbnail.value
                }
            } else {
                for (i = 0; i < obj.thumbnail.length; i++) {
                    if (obj.thumbnail[i].checked) {
                        mid = obj.thumbnail[i].value
                    }
                }
            }
            if (mid == '') {
                alert('You must select a media item');
                return false
            }
            tag = "[" + autotag + ":" + mid;
            if (width != '') {
                tag += " width:" + width
            }
            if (height != '') {
                tag += " height:" + height
            }
            tag += " align:" + alignment;
            if (source != 'tn') {
                tag += " src:" + source
            }
            if (link != '') {
                tag += " link:" + link
            }
            if (dest == 'block') {
                tag += " dest:block"
            }
            if (linksrc != '') {
                tag += " src:" + linksrc
            }
            if (classes != '' ) {
                tag += " class:" + classes
            }
            if (alt != '' ) {
                tag += " tag:" + alt
            }
            tag += "]";
            break;
        case 'video':
            if (obj.thumbnail.length == null) {
                if (obj.thumbnail.checked) {
                    mid = obj.thumbnail.value
                }
            } else {
                for (i = 0; i < obj.thumbnail.length; i++) {
                    if (obj.thumbnail[i].checked) {
                        mid = obj.thumbnail[i].value
                    }
                }
            }
            if (mid == '') {
                alert('You must select a media item');
                return false
            }
            tag = "[" + autotag + ":" + mid;
            if (width != '') {
                tag += " width:" + width
            }
            if (height != '') {
                tag += " height:" + height
            }
            tag += " border:" + border;
            tag += " align:" + alignment;
            if (source != 'tn') {
                tag += " src:" + source
            }
            if (autoplay != '') {
                tag += " autoplay:" + autoplay
            }
            if (dest == 'block') {
                tag += " dest:block"
            }
            if (classes != '' ) {
                tag += " class:" + classes
            }
            if (alt != '' ) {
                tag += " tag:" + alt
            }
            if (caption != '') {
                tag += " " + caption
            }
            tag += "]";
            break;
        case 'audio':
            if (obj.thumbnail.length == null) {
                if (obj.thumbnail.checked) {
                    mid = obj.thumbnail.value
                }
            } else {
                for (i = 0; i < obj.thumbnail.length; i++) {
                    if (obj.thumbnail[i].checked) {
                        mid = obj.thumbnail[i].value
                    }
                }
            }
            if (mid == '') {
                alert('You must select a media item');
                return false
            }
            tag = "[" + autotag + ":" + mid;
            if (width != '') {
                tag += " width:" + width
            }
            if (height != '') {
                tag += " height:" + height
            }
            tag += " border:" + border;
            tag += " align:" + alignment;
            if (source != 'tn') {
                tag += " src:" + source
            }
            if (autoplay != '') {
                tag += " autoplay:" + autoplay
            }
            if (dest == 'block') {
                tag += " dest:block"
            }
            if (ribbon == 1) {
                tag += " type:ribbon"
            }
            if (classes != '' ) {
                tag += " class:" + classes
            }
            if (caption != '') {
                tag += " " + caption
            }
            tag += "]";
            break;
        case 'playall':
            if (aid == '') {
                alert('You must select an album');
                return false
            }
            tag = "[" + autotag + ":" + aid;
            if (autoplay != '') {
                tag += " autoplay:" + autoplay
            }
            tag += " align:" + alignment;
            if (classes != '' ) {
                tag += " class:" + classes
            }
            tag += "]";
            break
    }
    return tag
}
