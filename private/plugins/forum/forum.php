<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | forum.php                                                                |
// |                                                                          |
// | Plugin intergration                                                      |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

global $_DB_table_prefix, $_TABLES;

$_FF_CONF = array();

// Plugin info

$_FF_CONF['pi_name']            = 'forum';
$_FF_CONF['pi_display_name']    = 'Forum';
$_FF_CONF['pi_version']         = '3.1.5';
$_FF_CONF['gl_version']         = '1.1.6';
$_FF_CONF['pi_url']             = 'http://www.glfusion.org/';

$_TABLES['gf_userprefs']    = $_DB_table_prefix . 'forum_userprefs';
$_TABLES['gf_topic']        = $_DB_table_prefix . 'forum_topic';
$_TABLES['gf_categories']   = $_DB_table_prefix . 'forum_categories';
$_TABLES['gf_forums']       = $_DB_table_prefix . 'forum_forums';
$_TABLES['gf_watch']        = $_DB_table_prefix . 'forum_watch';
$_TABLES['gf_moderators']   = $_DB_table_prefix . 'forum_moderators';
$_TABLES['gf_banned_ip']    = $_DB_table_prefix . 'forum_banned_ip';
$_TABLES['gf_log']          = $_DB_table_prefix . 'forum_log';
$_TABLES['gf_userinfo']     = $_DB_table_prefix . 'forum_userinfo';
$_TABLES['gf_attachments']  = $_DB_table_prefix . 'forum_attachments';
$_TABLES['gf_bookmarks']    = $_DB_table_prefix . 'forum_bookmarks';
$_TABLES['gf_rating_assoc']	= $_DB_table_prefix . 'forum_rating_assoc';
?>