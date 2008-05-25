<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.3 for Geeklog - The Ultimate Weblog               |
// | Release date: Jan 11,2004                                                 |
// +---------------------------------------------------------------------------+
// | resyncall.php                                                             |
// | Forum Plugin admin - Utility Script to re-sync all forums                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003,2004 by the following authors:          |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Author                                                                    |
// | Blaine Lang,    contact: blaine@portalparts.com   www.portalparts.com     |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once('../../../lib-common.php');

include_once('gf_functions.php');
require_once($_CONF['path_html'] . 'forum/include/gf_format.php');
require_once($_CONF['path'] . 'plugins/forum/debug.php');  // Common Debug Code

// Only let admin users access this page
if (!SEC_hasRights('forum.edit')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Forum Resync All page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock('Access Denied');
    $display .= 'Access Denied';
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

echo COM_siteHeader();
echo COM_startBlock('Forum Re-Sync Utility');

$query = DB_query("SELECT forum_name,forum_id FROM {$_TABLES['gf_forums']} ORDER BY forum_order");
while (list($forum_name,$id) = DB_fetchArray($query)) {
    echo "<br>Re-Syncing Forum:$forum_name";
    // Update all the Topics lastupdated timestamp to that of the last posted comment
    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$id and pid=0");
    $numTopics   = DB_numRows($topicsQuery);
    DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count = '$numTopics' WHERE forum_id=$id");

    $topicsQuery = DB_query("SELECT MAX(id) as maxid FROM {$_TABLES['gf_topic']} WHERE forum=$id");
    $lasttopic = 0;
    if ( DB_numRows($topicsQuery) > 0 ) {
        $lasttopic   = DB_fetchArray($topicsQuery);
        if ( $lasttopic == NULL || $lasttopic['maxid'] == '' ) {
            $lasttopic['maxid'] = 0;
        }
        DB_query("UPDATE {$_TABLES['gf_forums']} SET last_post_rec = {$lasttopic['maxid']} WHERE forum_id=$id");
    } else {
        DB_query("UPDATE {$_TABLES['gf_forums']} SET last_post_rec = 0 WHERE forum_id=$id");
    }

    // Update the forum definition record to know the number of topics

    $postCount = DB_Count($_TABLES['gf_topic'],'forum',$id);
    // Update the forum definition record to know the number of posts
    if ( $postCount == NULL || $postCount == '' ) {
        $postCount = 0;
    }
    DB_query("UPDATE {$_TABLES['gf_forums']} SET post_count = '$postCount' WHERE forum_id=$id");

    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$id and pid=0");

    while($trecord = DB_fetchArray($topicsQuery)){
        // Retrieve the oldest post records for this topic and update the lastupdated time in the parent topic record
        $lsql = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['gf_topic']} WHERE pid={$trecord['id']}");
        $lastrec = DB_fetchArray($lsql);
        if ($lastrec['maxid'] != NULL) {
            $postCount($_TABLES['gf_topic'],'forum',$id);
            $latest = DB_getITEM($_TABLES['gf_topic'],date,"id={$lastrec['maxid']}");
            DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated = '$latest' where id='{$trecord['id']}'");
        } else {
            $latest = DB_getITEM($_TABLES['gf_topic'],date,"id={$trecord['id']}");
            DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated = '$latest' WHERE id='{$trecord['id']}'");
        }
        // Recalculate and Update the number of replies
        $numreplies = DB_Count($_TABLES['gf_topic'], "pid", $trecord['id']);
        DB_query("UPDATE {$_TABLES['gf_topic']} SET replies = '$numreplies' WHERE id='{$trecord['id']}'");
    }
}

echo COM_endBlock();
echo COM_siteFooter();

?>