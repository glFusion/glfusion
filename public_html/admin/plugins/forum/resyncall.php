<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | resyncall.php                                                            |
// |                                                                          |
// | Forum Plugin admin - Utility Script to re-sync all forums                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('forum.edit')) {
  $display = COM_siteHeader();
  $display .= COM_startBlock($LANG_GF00['access_denied']);
  $display .= $LANG_GF00['admin_only'];
  $display .= COM_endBlock();
  $display .= COM_siteFooter(true);
  echo $display;
  exit();
}

USES_forum_functions();
USES_forum_format();
USES_forum_admin();

$display = FF_siteHeader();
$display .= COM_startBlock('Forum Re-Sync Utility');

$query = DB_query("SELECT forum_name,forum_id FROM {$_TABLES['ff_forums']} ORDER BY forum_order");
while (list($forum_name,$id) = DB_fetchArray($query)) {
    $display .= "<br/>Re-Syncing Forum:$forum_name";
    // Update all the Topics lastupdated timestamp to that of the last posted comment
    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=$id and pid=0");
    $numTopics   = DB_numRows($topicsQuery);
    DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count = '$numTopics' WHERE forum_id=$id");

    $topicsQuery = DB_query("SELECT MAX(id) as maxid FROM {$_TABLES['ff_topic']} WHERE forum=$id");
    $lasttopic = 0;
    if ( DB_numRows($topicsQuery) > 0 ) {
        $lasttopic   = DB_fetchArray($topicsQuery);
        if ( $lasttopic == NULL || $lasttopic['maxid'] == '' ) {
            $lasttopic['maxid'] = 0;
        }
        DB_query("UPDATE {$_TABLES['ff_forums']} SET last_post_rec = {$lasttopic['maxid']} WHERE forum_id=$id");
    } else {
        DB_query("UPDATE {$_TABLES['ff_forums']} SET last_post_rec = 0 WHERE forum_id=$id");
    }

    // Update the forum definition record to know the number of topics

    $postCount = DB_Count($_TABLES['ff_topic'],'forum',$id);
    // Update the forum definition record to know the number of posts
    if ( $postCount == NULL || $postCount == '' ) {
        $postCount = 0;
    }
    DB_query("UPDATE {$_TABLES['ff_forums']} SET post_count = '$postCount' WHERE forum_id=$id");

    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=$id and pid=0");

    while($trecord = DB_fetchArray($topicsQuery)){
        // Retrieve the oldest post records for this topic and update the lastupdated time in the parent topic record
        $lsql = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['ff_topic']} WHERE pid={$trecord['id']}");
        $lastrec = DB_fetchArray($lsql);
        if ($lastrec['maxid'] != NULL) {
            $postCount = DB_count($_TABLES['ff_topic'],'forum',$id);
            $latest = DB_getITEM($_TABLES['ff_topic'],'date',"id={$lastrec['maxid']}");
            DB_query("UPDATE {$_TABLES['ff_topic']} SET lastupdated = '$latest' where id='{$trecord['id']}'");
        } else {
            $latest = DB_getITEM($_TABLES['ff_topic'],'date',"id={$trecord['id']}");
            DB_query("UPDATE {$_TABLES['ff_topic']} SET lastupdated = '$latest' WHERE id='{$trecord['id']}'");
        }
        // Recalculate and Update the number of replies
        $numreplies = DB_Count($_TABLES['ff_topic'], "pid", $trecord['id']);
        DB_query("UPDATE {$_TABLES['ff_topic']} SET replies = '$numreplies' WHERE id='{$trecord['id']}'");
    }
}

$display .= COM_endBlock();
$display .= FF_siteFooter();
echo $display;
?>