// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | resetrating.js                                                           |
// |                                                                          |
// | AJAX handler for rating reset                                            |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

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