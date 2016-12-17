/*
 * glFusion CMS
 *
 * @license Copyright (c) 2003-2008, Blaine Lang. All rights reserved.
 * Licensed under the terms of the GNU General Public License
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 */
// @param  string   selected    Name of div that has been selected
// @param  int      selindex    index id of the selected tab as in 1 - 7 used to set the selected tab
function showhideProfileEditorDiv(selected, selindex) {

    // Reset the current selected navbar tab
    var cnavbar = document.getElementById('current');
    if (cnavbar) cnavbar.id = '';

    // Cycle thru the navlist child elements - buiding an array of just the link items
    var navbar = document.getElementById('navlist');
    var menuitems = new Array(7);
    var item = 0;
    for (var i=0 ;i < navbar.childNodes.length ; i++ ) {
        if (navbar.childNodes[i].nodeName.toLowerCase() == 'li') {
            menuitems[item] = navbar.childNodes[i];
            item++;
        }
    }
    // Now that I have just the link items I can set the selected tab using the passed selected Item number
    // Set the <a tag to have an id called 'current'
    var menuitem = menuitems[selindex];
    for (var j=0 ;j < menuitem.childNodes.length ; j++ ) {
        if (menuitem.childNodes[j].nodeName.toLowerCase() == 'a') {
            menuitem.childNodes[j].id = 'current';
            $('#current').parent().siblings('li').removeClass('uk-active');
            $('#current').parent().addClass('uk-active');
        }
    }

    // Reset or show all the main divs - editor tab sections
    // Object profilepanels defined in profile.thtml after page is generated
    for( var divid in profilepanels){
        if (selected != divid) {
            document.getElementById(divid).style.display = 'none';
        } else {
            document.getElementById(divid).style.display = '';
        }
    }

    var preview = document.getElementById('pe_preview');
    if (preview) document.getElementById('pe_preview').style.display = 'none';

    if (selected != 'pe_preview') {
        document.getElementById('save_button').style.display = '';
    } else if (selected == 'pe_preview') {
        if (preview) document.getElementById('pe_preview').style.display = '';
        document.getElementById('save_button').style.display = 'none';
    } else {
        if (preview) document.getElementById('pe_preview').style.display = '';
        document.getElementById('save_button').style.display = 'none';
    }
    pf();
}
