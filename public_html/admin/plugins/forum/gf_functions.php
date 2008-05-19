<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | gb_functions.php                                                          | 
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Plugin Authors                                                            |
// | Blaine Lang,                  blaine@portalparts.com, www.portalparts.com |
// | Version 1.0 co-developer:     Matthew DeWyer, matt@mycws.com              |   
// | Prototype & Concept :         Mr.GxBlock, www.gxblock.com                 |
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

if (!SEC_hasRights('forum.edit')) {
  echo COM_siteHeader();
  echo COM_startBlock($LANG_GF00['access_denied']);
  echo $LANG_GF00['admin_only'];
  echo COM_endBlock();
  echo adminfooter();
  echo COM_siteFooter(true);
  exit();
}

$navbarMenu = array(
    $LANG_GF06['1']   => $_CONF['site_admin_url'] .'/plugins/forum/index.php',
    $LANG_GF06['2']   => $_CONF['site_admin_url'] .'/plugins/forum/settings.php',
    $LANG_GF06['3']   => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
    $LANG_GF06['4']   => $_CONF['site_admin_url'] .'/plugins/forum/mods.php',
    $LANG_GF06['5']   => $_CONF['site_admin_url'] .'/plugins/forum/migrate.php',
    $LANG_GF06['6']   => $_CONF['site_admin_url'] .'/plugins/forum/messages.php',
    $LANG_GF06['7']   => $_CONF['site_admin_url'] .'/plugins/forum/ips.php',
);

// Site admin can add common footer code here
function adminfooter() {
    global $_CONF, $LANG_GF01;
    
    $footertemplate = new Template($_CONF['path_layout'] . 'forum/layout/admin');
    $footertemplate->set_file (array ('footertemplate'=>'footer.thtml'));
    
    $footertemplate->set_var ('forumname', $LANG_GF01['forumname']);
    
    $footertemplate->parse ('output', 'footertemplate');
    echo $footertemplate->finish ($footertemplate->get_var('output'));
    
}


function gf_resyncforum($id) {
    global $_CONF,$_TABLES;

    COM_errorLog("Re-Syncing Forum id:$id");
    // Update all the Topics lastupdated timestamp to that of the last posted comment
    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$id and pid=0");
    $topicCount = DB_numRows($topicsQuery);
    if ($topicCount > 0) {
        $lastTopicQuery = DB_query("SELECT MAX(id) as maxid FROM {$_TABLES['gf_topic']} WHERE forum=$id");
        $lasttopic = DB_fetchArray($lastTopicQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET last_post_rec = {$lasttopic['maxid']} WHERE forum_id=$id");
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$id);
        // Update the forum definition record to know the number of topics and number of posts
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$id");

        $recCount = 0;
        while($trecord = DB_fetchArray($topicsQuery)) {
            $recCount++;
            // Retrieve the oldest post records for this topic and update the lastupdated time in the parent topic record
            $lsql = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['gf_topic']} WHERE pid={$trecord['id']}");
            $lastrec = DB_fetchArray($lsql);
            if ($lastrec['maxid'] != NULL) {
                $postCount = DB_count($_TABLES['gf_topic'],'forum',$id);
                $latest = DB_getITEM($_TABLES['gf_topic'],date,"id={$lastrec['maxid']}");
                DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated = '$latest' where id='{$trecord['id']}'");
                // Update the parent topic record to know the id of the Last Reply
                DB_query("UPDATE {$_TABLES['gf_topic']} SET last_reply_rec = {$lastrec['maxid']} where id='{$trecord['id']}'");
            } else {
                $latest = DB_getITEM($_TABLES['gf_topic'],date,"id={$trecord['id']}");
                DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated = '$latest' WHERE id='{$trecord['id']}'");
            }
            // Recalculate and Update the number of replies
            $numreplies = DB_Count($_TABLES['gf_topic'], "pid", $trecord['id']);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET replies = '$numreplies' WHERE id='{$trecord['id']}'");
        }
        COM_errorLog("$recCount Topic Records Updated");
    } else {
        COM_errorLog("No topic records to resync");
    }

}

?>