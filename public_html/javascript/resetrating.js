/*
 * glFusion CMS
 *
 * @license Copyright (c) 2009-2014, Mark R. Evans. All rights reserved.
 * Licensed under the terms of the GNU General Public License
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 */
var xmlhttp = null;

function ajax_resetrating(type,id) {

    xmlhttp = new XMLHttpRequest();

    var qs = '';

    if (id != '') {
        qs =  '?id=' + id + '&t=' + type;
    }

    xmlhttp.open('GET', site_admin_url + '/resetrating_rpc.php' + qs, true);
    xmlhttp.onreadystatechange = function() {
       if (xmlhttp.readyState == 4) {
            receiveResetRating(xmlhttp.responseXML);
          }
   };
    xmlhttp.send(null);
}

function receiveResetRating(dom) {

    var html = '';

    try{
        var oxml = dom.getElementsByTagName('html');
        html = oxml[0].childNodes[0].nodeValue;
    }catch(e){}

    if (html != '') {
        var obj = document.getElementById('rating');
        obj.innerHTML = html;
    }
}