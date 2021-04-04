/*
 * Media Gallery Autotag Plugin
 *
 * @license Copyright (c) 2003-2020, Mark R. Evans. All rights reserved.
 * Licensed under the terms of the GNU General Public License
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 * @version 1.8.1
 */
(function(){var e="mediagallery";var t=window,n=document,r=n.documentElement,i=n.getElementsByTagName("body")[0],s=t.innerWidth||r.clientWidth||i.clientWidth,o=t.innerHeight||r.clientHeight||i.clientHeight;CKEDITOR.plugins.add(e,{init:function(e){e.ui.addButton("Mediagallery",{label:"Media Gallery Browser",click:function(e){window.open(CKEDITOR.plugins.getPath("mediagallery")+"mediagallery.php?i="+e.name,"Image Browser","location=no,menubar=no,resizable,scrollbars=yes,width="+s*.8+",height="+o*.9+"")},icon:CKEDITOR.plugins.getPath("mediagallery")+"images/mediagallery.gif",toolbar:"insert"})}})})()
