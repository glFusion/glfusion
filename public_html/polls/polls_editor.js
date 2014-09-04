// +--------------------------------------------------------------------------+
// | Polls Plugins - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | polls_editor.js                                                          |
// |                                                                          |
// | Javascript functions for FCKEditor Integration into Polls plugin         |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2003-2008 by the following authors:                        |
// |                                                                          |
// | Authors:   Blaine Lang - blaine@portalparts.com                          |
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


function showhidePollsEditorDiv(option,selindex,questions) {
    // Reset the current selected navbar tab
    var navbar = document.getElementById('current');
    if (navbar) navbar.id = '';
    // Cycle thru the navlist child elements - buiding an array of just the link items
    var navbar = document.getElementById('navlist');
    var menuitems = new Array(10);
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
    for (var j=0; j<menuitem.childNodes.length; j++ ) {
        if (menuitem.childNodes[j].nodeName.toLowerCase() == 'a') {
            menuitem.childNodes[j].id = 'current';
            $('#current').parent().siblings('li').removeClass('uk-active');
            $('#current').parent().addClass('uk-active');
        }
    }

    // Reset or show all the main divs - editor tab sections
    for (i=0; i < questions; i++) {
        var div = 'po_' + i;
        if (option != i) {
            document.getElementById(div).style.display = 'none';
        } else {
            document.getElementById(div).style.display = '';
        }
    }
}