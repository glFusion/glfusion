<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | admin.inc.php                                                            |
// |                                                                          |
// | Forum Admin functions                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $LANG_GF06, $navbarMenu;

$navbarMenu = array(
    $LANG_GF06['1']   => $_CONF['site_admin_url'] .'/plugins/forum/index.php',
    $LANG_GF06['3']   => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
    $LANG_GF06['4']   => $_CONF['site_admin_url'] .'/plugins/forum/mods.php',
    $LANG_GF06['5']   => $_CONF['site_admin_url'] .'/plugins/forum/migrate.php',
    $LANG_GF06['6']   => $_CONF['site_admin_url'] .'/plugins/forum/messages.php',
    $LANG_GF06['7']   => $_CONF['site_admin_url'] .'/plugins/forum/ips.php',
    $LANG_GF06['11']  => $_CONF['site_admin_url'] .'/plugins/forum/badges.php',
    $LANG_GF06['12']  => $_CONF['site_admin_url'] .'/plugins/forum/ranks.php',
);
if ( $_FF_CONF['enable_user_rating_system'] ) {
    $navbarMenu[$LANG_GF06['8']] = $_CONF['site_admin_url'] .'/plugins/forum/rating.php';
}


function FF_adminNav( $selected = '' )
{
    global $_CONF, $_FF_CONF, $LANG_GF06, $LANG_ADMIN;

    $menu_arr = array();

    $navbarMenu = array(
        $LANG_GF06['1']   => $_CONF['site_admin_url'] .'/plugins/forum/index.php',
        $LANG_GF06['3']   => $_CONF['site_admin_url'] .'/plugins/forum/boards.php',
        $LANG_GF06['4']   => $_CONF['site_admin_url'] .'/plugins/forum/mods.php',
        $LANG_GF06['5']   => $_CONF['site_admin_url'] .'/plugins/forum/migrate.php',
        $LANG_GF06['6']   => $_CONF['site_admin_url'] .'/plugins/forum/messages.php',
        $LANG_GF06['7']   => $_CONF['site_admin_url'] .'/plugins/forum/ips.php',
        $LANG_GF06['11']  => $_CONF['site_admin_url'] .'/plugins/forum/badges.php',
        $LANG_GF06['12']  => $_CONF['site_admin_url'] .'/plugins/forum/ranks.php',
    );
    if ( $_FF_CONF['enable_user_rating_system'] ) {
        $navbarMenu[$LANG_GF06['8']] = $_CONF['site_admin_url'] .'/plugins/forum/rating.php';
    }

    for ( $i=1; $i <= count($navbarMenu); $i++ )  {
        $parms = explode( "=",current($navbarMenu) );
        if ( key($navbarMenu) != $selected ) {
            $url = current($navbarMenu);
            $label = key($navbarMenu);
            $menu_arr = array_merge($menu_arr,array (
                array('url' => $url,
                      'text'=> $label)
            ));
        }
        next($navbarMenu);
    }

    $menu_arr = array_merge($menu_arr,array (
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    ));

    return $menu_arr;
}


// Site admin can add common footer code here
function FF_adminfooter() {
    global $_CONF, $LANG_GF01;

    $footertemplate = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $footertemplate->set_file (array ('footertemplate'=>'footer.thtml'));

    $footertemplate->set_var ('forumname', $LANG_GF01['forumname']);

    $footertemplate->parse ('output', 'footertemplate');
    return $footertemplate->finish ($footertemplate->get_var('output'));

}


function gf_resyncforum($id) {
    global $_CONF,$_TABLES;

    COM_errorLog("Re-Syncing Forum id:$id");
    // Update all the Topics lastupdated timestamp to that of the last posted comment
    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=$id and pid=0");
    $topicCount = DB_numRows($topicsQuery);
    if ($topicCount > 0) {
        $lastTopicQuery = DB_query("SELECT MAX(id) as maxid FROM {$_TABLES['ff_topic']} WHERE forum=$id");
        $lasttopic = DB_fetchArray($lastTopicQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET last_post_rec = {$lasttopic['maxid']} WHERE forum_id=$id");
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',$id);
        // Update the forum definition record to know the number of topics and number of posts
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$id");

        $recCount = 0;
        while($trecord = DB_fetchArray($topicsQuery)) {
            $recCount++;
            // Retrieve the oldest post records for this topic and update the lastupdated time in the parent topic record
            $lsql = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['ff_topic']} WHERE pid={$trecord['id']}");
            $lastrec = DB_fetchArray($lsql);
            if ($lastrec['maxid'] != NULL) {
                $postCount = DB_count($_TABLES['ff_topic'],'forum',$id);
                $latest = DB_getITEM($_TABLES['ff_topic'],'date',"id={$lastrec['maxid']}");
                DB_query("UPDATE {$_TABLES['ff_topic']} SET lastupdated = '$latest' where id='{$trecord['id']}'");
                // Update the parent topic record to know the id of the Last Reply
                DB_query("UPDATE {$_TABLES['ff_topic']} SET last_reply_rec = {$lastrec['maxid']} where id='{$trecord['id']}'");
            } else {
                $latest = DB_getITEM($_TABLES['ff_topic'],'date',"id={$trecord['id']}");
                DB_query("UPDATE {$_TABLES['ff_topic']} SET lastupdated = '$latest' WHERE id='{$trecord['id']}'");
            }
            // Recalculate and Update the number of replies
            $numreplies = DB_Count($_TABLES['ff_topic'], "pid", $trecord['id']);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET replies = '$numreplies' WHERE id='{$trecord['id']}'");
        }
        COM_errorLog("$recCount Topic Records Updated");
    } else {
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=0, post_count=0 WHERE forum_id=$id");
        COM_errorLog("No topic records to resync");
    }

}

/* Function to create a new forum
*
* @param        string     $name        Forum name
* @param        string     $category    Category id to add the forum
* @param        string     $dscp        Optional Category Description
* @param        string     $order       Optional Display order
* @param        string     $order       Optional Group ID if a private group - Default Group 'All Users'
* @param        string     $order       Optional Readonly flag
* @param        string     $order       Optional Hidden flag
* @param        string     $order       Optional Don't show in newposts and centerblock
* @return       string                  Returns the Forum ID for the new forum if successful
*/

//DEPRECIATED - can remove from code.

function ff_addForum($name,$category,$dscp="",$order="",$grp_id=2,$is_readonly=0,$is_hidden=0,$no_newposts=0,$attachmentgroup=1) {
    global $_TABLES, $_USER;

    $fields = 'forum_order,forum_name,forum_dscp,forum_cat,grp_id,is_readonly,is_hidden,no_newposts,use_attachment_grpid,rating_view,rating_post';

    if ( empty($name) || $name == '' ) {
        return false;
    }

    DB_query("INSERT INTO {$_TABLES['ff_forums']} ($fields)
        VALUES ('$order','$name','$dscp','$category','$grp_id','$is_readonly','$is_hidden','$no_newposts',$attachmentgroup,0,0)");

    $query = DB_query("SELECT max(forum_id) FROM {$_TABLES['ff_forums']} ");
    list ($forumid) = DB_fetchArray($query);
    $modquery = DB_query("SELECT * FROM {$_TABLES['ff_moderators']} WHERE mod_uid='{$_USER['uid']}' AND mod_forum='$forumid'");
    if (DB_numrows($modquery) < 1) {
        $fields = 'mod_uid,mod_username,mod_forum,mod_delete,mod_ban,mod_edit,mod_move,mod_stick';
        DB_query("INSERT INTO {$_TABLES['ff_moderators']} ($fields) VALUES ('{$_USER['uid']}','{$_USER['username']}', '$forumid','1','1','1','1','1')");
    }
    return $forumid;
}

/* Function to delete a forum
*
* @param        string     $id        Forum id to delete
* @return       boolean               Returns true
*/
function ff_deleteForum($id) {
    global $_TABLES;

    // pull all topic ids for the forum being deleted.
    $totalTopics = DB_count($_TABLES['ff_topic'],'forum',(int) $id);
    $bufferSize = (int) ($totalTopics * 6);
    if ( $bufferSize < 1024) $bufferSize = 1024;
    DB_query("SET group_concat_max_len = " . $bufferSize);
    $ids = DB_getItem($_TABLES['ff_topic'],'GROUP_CONCAT(id SEPARATOR \',\')', 'forum='.(int) $id);
    // Delete the likes. This must be done first as it relies on the topics
    // still existing in the table
    Forum\Like::deleteForum($id);
    DB_query("DELETE FROM {$_TABLES['ff_forums']} WHERE forum_id=".(int) $id);
    DB_query("DELETE FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $id);
    DB_query("DELETE FROM {$_TABLES['ff_moderators']} WHERE mod_forum=".(int) $id);
    DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND category=".(int)$id);

    PLG_itemDeleted($id, 'forum', $ids);

    return true;
}
?>
