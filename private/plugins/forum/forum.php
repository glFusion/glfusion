<?php
/**
* glFusion CMS - Forum Plugin
*
* Plugin Integration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2010 by the following authors:
*   Blaine Lang          blaine AT portalparts DOT com
*                        www.portalparts.com
*   Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com
*   Prototype & Concept :        Mr.GxBlock, www.gxblock.com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_table_prefix, $_TABLES;

$_FF_CONF = array();

// Plugin info

$_FF_CONF['pi_name']            = 'forum';
$_FF_CONF['pi_display_name']    = 'Forum';
$_FF_CONF['pi_version']         = '3.4.3.2';
$_FF_CONF['gl_version']         = '2.0.0';
$_FF_CONF['pi_url']             = 'https://www.glfusion.org/';

$_TABLES['ff_userprefs']    = $_DB_table_prefix . 'forum_userprefs';
$_TABLES['ff_topic']        = $_DB_table_prefix . 'forum_topic';
$_TABLES['ff_categories']   = $_DB_table_prefix . 'forum_categories';
$_TABLES['ff_forums']       = $_DB_table_prefix . 'forum_forums';
$_TABLES['ff_watch']        = $_DB_table_prefix . 'forum_watch';
$_TABLES['ff_moderators']   = $_DB_table_prefix . 'forum_moderators';
$_TABLES['ff_banned_ip']    = $_DB_table_prefix . 'forum_banned_ip';
$_TABLES['ff_log']          = $_DB_table_prefix . 'forum_log';
$_TABLES['ff_userinfo']     = $_DB_table_prefix . 'forum_userinfo';
$_TABLES['ff_attachments']  = $_DB_table_prefix . 'forum_attachments';
$_TABLES['ff_bookmarks']    = $_DB_table_prefix . 'forum_bookmarks';
$_TABLES['ff_rating_assoc']	= $_DB_table_prefix . 'forum_rating_assoc';
$_TABLES['ff_badges']       = $_DB_table_prefix . 'forum_badges';
$_TABLES['ff_ranks']        = $_DB_table_prefix . 'forum_ranks';
$_TABLES['ff_likes_assoc']  = $_DB_table_prefix . 'forum_likes_assoc';
$_TABLES['ff_warnings']     = $_DB_table_prefix . 'forum_warnings';
$_TABLES['ff_warningtypes'] = $_DB_table_prefix . 'forum_warningtypes';
$_TABLES['ff_warninglevels'] = $_DB_table_prefix . 'forum_warninglevels';

?>
