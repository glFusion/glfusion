/*
 * glFusion CMS
 *
 * @license Copyright (c) 2005-2008, Blaine Lang. All rights reserved.
 * @license Copyright (c) 2008-2014, Mark R. Evans. All rights reserved.
 * Licensed under the terms of the GNU General Public License
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 */
   function caItems(f, name) {
       var n=f.elements.length;
       for (i=0;i<n; i++) {
           var field=f.elements[i];
           if (field.type == 'checkbox' && field.name.match(name)) {
                if (f.chk_selectall.checked) {
                    field.checked=true;
                } else {
                    field.checked=false;
                }
           }

       }
   }

// Basic function to show/hide (toggle) an element - pass in the elment id
    function elementToggle(id) {
        var obj = document.getElementById(id);
        if (obj.style.display == 'none') {
            obj.style.display = '';
        } else {
            obj.style.display = 'none';
        }
    }

// Basic function to show/hide an element - pass in the elment id and option.
// Where option can be: show or hide or toggle
    function elementShowHide(id,option) {
        var obj = document.getElementById(id);
        if (option == 'hide') {
            obj.style.display = 'none';
        } else if (option == 'show') {
            obj.style.display = '';
        } else if (option == 'toggle') {
            elementToggle(id);
        }
    }

    /**
    * Pops up a new window in the middle of the screen
    */
    function popupWindow(mypage, myname, w, h, scroll) {
        var winl = (screen.width - w) / 2;
        var wint = (screen.height - h) / 2;
        winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
        win = window.open(mypage, myname, winprops)
        if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
    }

    /**
    * Event handler
    */
    function adddwEvent(element, type, handler) {
        // assign each event handler a unique ID
        if (!handler.$$guid) handler.$$guid = adddwEvent.guid++;
        // create a hash table of event types for the element
        if (!element.events) element.events = {};
        // create a hash table of event handlers for each element/event pair
        var handlers = element.events[type];
        if (!handlers) {
            handlers = element.events[type] = {};
            // store the existing event handler (if there is one)
            if (element["on" + type]) {
                handlers[0] = element["on" + type];
            }
        }
        // store the event handler in the hash table
        handlers[handler.$$guid] = handler;
        // assign a global event handler to do all the work
        element["on" + type] = handleEvent;
    };
    // a counter used to create unique IDs
    adddwEvent.guid = 1;

    // double confirmation (are you really sure?)
    function doubleconfirm( msg1, msg2 ) {
        return confirm(msg1) && confirm(msg2);
    }

    //widget wrapper iframe buster (load links to parent site in parent window)
    if (top.location != location) {
        top.location.href = document.location.href;
    }
