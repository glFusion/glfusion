<?php
// +--------------------------------------------------------------------------+
// | CommentFeeds Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | glupgrade.inc                                                            |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Comment Feed Plugin for Geeklog                             |
// | Author:                                                                  |
// | Michael Jervis <mike AT fuckingbrit DOT com>                             |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


/*
 * If we are being called glFusion's upgrade routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

function plugin_plgupgrade_commentfeeds ()
{
    global $_TABLES;

    // Bad Behavior handles its database changes automatically,
    // so only update the version number
    DB_query ("UPDATE {$_TABLES['plugins']} SET pi_version = '1.1.0.fusion', pi_gl_version = '1.1.0', pi_homepage = 'http://www.glfusion.org/' WHERE pi_name = 'commentfeeds'");

    return true;
}
?>