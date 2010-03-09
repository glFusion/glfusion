<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | moderation.php                                                           |
// |                                                                          |
// | Moderation routines                                                      |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
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

require_once '../lib-common.php';
require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';
require_once $_CONF['path'] . 'plugins/forum/include/gf_showtopic.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

// validate we have a logged in user..
if ( COM_isAnonUser() ) {
    echo COM_refresh($_CONF['site_url'].'/users.php');
    exit;
}

/**
  * Delete forum post(s)
  *
  * This function will delete the requested forum post and update all the
  * topic / forum counters.
  *
  * @param  int     $topic_id        Topic ID to delete
  * @param  int     $topic_parent_id Parent ID of topic
  * @param  int     $forum_id        Forum ID where topic exists
  *
  * @return  string HTML to display confirmation
  */
function moderator_deletePost($topic_id,$topic_parent_id,$forum_id)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF02;

    $retval = '';

    $topicparent = DB_getItem($_TABLES['gf_topic'],"pid","id='$topic_id'");
    if ($topicparent == 0) {
        // Need to check for any attachments and delete if required
        $q1 = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE pid=$topic_id OR id=$topic_id");
        while($A = DB_fetchArray($q1)) {
            $q2 = DB_query("SELECT id FROM {$_TABLES['gf_attachments']} WHERE topic_id={$A['id']}");
            while ($B = DB_fetchArray($q2)) {
                forum_delAttachment($B['id']);
            }
            PLG_itemDeleted($A['id'],'forum');
        }
        DB_query("DELETE FROM {$_TABLES['gf_topic']} WHERE (id=$topic_id)");
        DB_query("DELETE FROM {$_TABLES['gf_topic']} WHERE (pid=$topic_id)");
        DB_query("DELETE FROM {$_TABLES['gf_watch']} WHERE (id=$topic_id)");
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum_id);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$forum_id and pid=0");
        $topicCount = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount,post_count=$postCount WHERE forum_id=$forum_id");
        // Remove any lastviewed records in the log so that the new updated topic indicator will appear
        DB_query("DELETE FROM {$_TABLES['gf_log']} WHERE topic=$topicparent");
    } else {
        // Need to check for any attachments and delete if required
        $q1 = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE id=$topic_id");
        while($A = DB_fetchArray($q1)) {
            $q2 = DB_query("SELECT id FROM {$_TABLES['gf_attachments']} WHERE topic_id={$A['id']}");
            while ($B = DB_fetchArray($q2)) {
                forum_delAttachment($B['id']);
            }
        }
        DB_query("UPDATE {$_TABLES['gf_topic']} SET replies=replies-1 WHERE (id=$topicparent)");
        DB_query("DELETE FROM {$_TABLES['gf_topic']} WHERE (id='$topic_id')");
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum_id);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET post_count=$postCount WHERE forum_id=$forum_id");
        PLG_itemDeleted($topic_id,'forum');
    }
    if ( $topicparent == 0 ) {
        $topicparent = $topic_id;
        gf_updateLastPost($forum_id);
    } else {
        gf_updateLastPost($forum_id,$topicparent);
    }

    CACHE_remove_instance('forumcb');

    if ($topicparent == $topic_id ) {
        $link = "{$_CONF['site_url']}/forum/index.php?forum=$forum_id";
        $retval .= forum_statusMessage($LANG_GF02['msg55'],$link,$LANG_GF02['msg55'],true,$forum_id,true);
    } else {
        $link = "{$_CONF['site_url']}/forum/viewtopic.php?showtopic=$topicparent";
        $retval .= forum_statusMessage($LANG_GF02['msg55'],$link,$LANG_GF02['msg55'],true,$forum_id,true);
    }

    return $retval;
}


/**
  * Bans an IP address
  *
  * This function insert the IP address into the banned IP table.
  *
  * @param  int     $topic_id        Topic ID to delete
  * @param  int     $topic_parent_id Parent ID of topic
  * @param  int     $forum_id        Forum ID where topic exists
  * @param  string  $hostip          Host IP (IPv4 format)
  *
  * @return  string HTML to display confirmation
  */
function moderator_banIP($topic_id,$topic_parent_id,$forum_id,$hostip)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF02;

    $retval = '';

    DB_query("INSERT INTO {$_TABLES['gf_banned_ip']} (host_ip) VALUES ('".addslashes($hostip)."')");

    $link = $_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id";

    $retval .= forum_statusMessage($LANG_GF02['msg56'],$link,$LANG_GF02['msg56'],false,'',true);

    return $retval;
}


/**
  * Moves or splits a topic to another forum
  *
  * This function will move either a single topic or a full topic
  * to a new forum as a new post.
  *
  * @param  int    $topic_id        Topic ID to delete
  * @param  int    $topic_parent_id Parent ID of topic
  * @param  int    $forum_id        Forum ID where topic exists
  * @param  int    $move_to_forum   Forum ID to receive posts
  * @param  string $new_topic_title Title to use for new topic
  * @param  string $splittype       What type of split (single or remaining)
  *
  * @return  string HTML to display confirmation
  */
function moderator_movePost($topic_id,$topic_parent_id,$forum_id, $move_to_forum, $new_topic_title,$splittype)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF02;

    $retval = '';

    $date = time();
    $movetitle = gf_preparefordb($new_topic_title,text);
    $newforumid = $move_to_forum;
    /* Check and see if we are splitting this forum thread */

    if ($splittype != '') {
        $curpostpid = DB_getItem($_TABLES['gf_topic'],"pid","id=$topic_id");
        if ( $curpostpid == '' || $curpostpid == 0 ) {
            echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
            exit();
        }
        if ($splittype == 'single' ) {  // Move only the single post - create a new topic
            $topicdate = DB_getItem($_TABLES['gf_topic'],"date","id=$topic_id");
            $sql  = "UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum, pid=0,lastupdated='$topicdate', ";
            $sql .= "subject='$movetitle', replies = '0' WHERE id=$topic_id ";
            DB_query($sql);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET replies=replies-1 WHERE id=$curpostpid ");

            // Update Topic and Post Count for the effected forums
            // new forum
            $postCount   = DB_Count($_TABLES['gf_topic'],'forum',$move_to_forum);
            $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$move_to_forum and pid=0");
            $topicCount  = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$move_to_forum");
            //oldforum
            $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum_id);
		    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$forum_id and pid=0");
		    $topic_count = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topic_count, post_count=$postCount WHERE forum_id=$forum_id");

            // Update the Forum and topic indexes
            gf_updateLastPost($forum_id,$curpostpid);
            gf_updateLastPost($move_to_forum,$topic_id);

        } else { // move all posts from this point forward.
            $movesql = DB_query("SELECT id,date FROM {$_TABLES['gf_topic']} WHERE pid=$curpostpid AND id >= $topic_id");
            $numreplies = DB_numRows($movesql); // how many replies are being moved.
            $topicparent = 0;
            while($movetopic = DB_fetchArray($movesql)) {
                if ($topicparent == 0) {
                    $sql  = "UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum, pid=0,lastupdated='{$movetopic['date']}', ";
                    $sql .= "replies=$numreplies - 1, subject='$movetitle' WHERE id={$movetopic['id']}";
                    DB_query($sql);
                    $topicparent = $movetopic['id'];
                } else {
                    $sql  = "UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum, pid=$topicparent, ";
                    $sql .= "subject='$movetitle' WHERE id='{$movetopic['id']}'";
                    DB_query($sql);
                    $topicdate = DB_getItem($_TABLES['gf_topic'],"date","id={$movetopic['id']}");
                    DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated='$topicdate' WHERE id=$topicparent");
                }
            }
            // update counters
            // new forum
            $postCount = DB_Count($_TABLES['gf_topic'],'forum',$move_to_forum);
            $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$move_to_forum and pid=0");
            $topicCount = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$move_to_forum");
            //oldforum
            $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum);
		    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$forum_id and pid=0");
		    $topic_count = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topic_count, post_count=$postCount WHERE forum_id=$forum_id");

            // Update the Forum and topic indexes
            gf_updateLastPost($forum_id,$curpostpid);
            gf_updateLastPost($move_to_forum,$topicparent);
        }
        $link = "{$_CONF['site_url']}/forum/viewtopic.php?showtopic=$topic_id";
        $retval .= forum_statusMessage(sprintf($LANG_GF02['msg183'],$move_to_forum),$link,$LANG_GF02['msg183'],false,'',true);
    } else {  // Move complete topic
        $moveResult = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE pid=$topic_id");
        $postCount = DB_numRows($moveResult)+1;  // Need to account for the parent post
        while($movetopic = DB_fetchArray($moveResult)) {
            DB_query("UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum WHERE id={$movetopic['id']}");
        }
        // Update any topic subscription records - need to change the forum ID record
        DB_query("UPDATE {$_TABLES['gf_watch']} SET forum_id=$move_to_forum WHERE topic_id=$topic_id");
        // this moves the parent record.
        DB_query("UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum, moved='1' WHERE id=$topic_id");

        // new forum
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$newforumid);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$move_to_forum and pid=0");
        $topicCount = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$move_to_forum");
        //oldforum
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum_id);
	    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$forum_id and pid=0");
	    $topic_count = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topic_count, post_count=$postCount WHERE forum_id=$forum_id");

        // Update the Last Post Information
        gf_updateLastPost($move_to_forum,$topic_id);
        gf_updateLastPost($forum_id);

        // Remove any lastviewed records in the log so that the new updated topic indicator will appear
        DB_query("DELETE FROM {$_TABLES['gf_log']} WHERE topic=$topic_id");
        $link = "{$_CONF['site_url']}/forum/viewtopic.php?showtopic=$topic_id";
        $retval .= forum_statusMessage($LANG_GF02['msg163'],$link,$LANG_GF02['msg163'],false,'',true);
    }
    CACHE_remove_instance('forumcb');

    return $retval;

}

function moderator_mergePost($topic_id,$topic_parent_id,$forum_id, $move_to_forum, $move_to_topic,$splittype)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF02;

    $retval = '';

    // right now we are only implementing moving a single post.

    if ( $move_to_topic == 0 ) {
        echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
        exit();
    }

    $curpostpid = DB_getItem($_TABLES['gf_topic'],"pid","id=$topic_id");
    if ( $curpostpid == '' ) {
        echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
        exit();
    }

    $move_to_forum = DB_getItem($_TABLES['gf_topic'],"forum","id=$move_to_topic");

    if ( $move_to_forum == 0 || $move_to_forum == '' ) {
        echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
        exit();
    }

    if ($curpostpid == 0 ) {
        $subject = addslashes(DB_getItem($_TABLES['gf_topic'],'subject','id='.$move_to_topic));
        $pidDate = DB_getItem($_TABLES['gf_topic'],'date','id='.$move_to_topic);
        $moveResult = DB_query("SELECT id,date FROM {$_TABLES['gf_topic']} WHERE pid=$topic_id");
        $postCount = DB_numRows($moveResult)+1;  // Need to account for the parent post
        while($movetopic = DB_fetchArray($moveResult)) {
            DB_query("UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum,pid=$move_to_topic,subject='$subject' WHERE id={$movetopic['id']}");
            // check to see if we need to swap pids
            if ( $movetopic['date'] < $pidDate ) {
                DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=".$movetopic['id']." WHERE id=".$move_to_topic);
                DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=0 WHERE id=".$movetopic['id']);
                DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=".$movetopic['id']." WHERE pid=".$move_to_topic);
                $move_to_topic = $movetopic['id'];
                $pidDate = $movetopic['date'];
            }
        }
        // Update any topic subscription records - need to change the forum ID record
        DB_query("UPDATE {$_TABLES['gf_watch']} SET forum_id=$move_to_forum WHERE topic_id=$topic_id");
        // this moves the parent record.
        DB_query("UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum,pid=$move_to_topic,subject='$subject' WHERE id=$topic_id");
        $topicDate = DB_getItem($_TABLES['gf_topic'],'date','id='.$topic_id);
        if ( $topicDate < $pidDate ) {
            DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=".$topic_id." WHERE id=".$move_to_topic);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=0 WHERE id=".$topic_id);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=".$topic_id." WHERE pid=".$move_to_topic);
            $move_to_topic = $topic_id;
            $pidDate = $topicDate;
        }
        // new forum
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$move_to_forum);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$move_to_forum and pid=0");
        $topicCount = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$move_to_forum");
        //oldforum
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum_id);
	    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$forum_id and pid=0");
	    $topic_count = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topic_count, post_count=$postCount WHERE forum_id=$forum_id");

        // Update the Last Post Information
        gf_updateLastPost($move_to_forum,$topic_id);
        gf_updateLastPost($forum_id);

        // Remove any lastviewed records in the log so that the new updated topic indicator will appear
        DB_query("DELETE FROM {$_TABLES['gf_log']} WHERE topic=$topic_id");
        $link = "{$_CONF['site_url']}/forum/viewtopic.php?showtopic=$topic_id";
        $retval .= forum_statusMessage($LANG_GF02['msg163'],$link,$LANG_GF02['msg163'],false,'',true);
    } else {
        $subject = addslashes(DB_getItem($_TABLES['gf_topic'],'subject','id='.$move_to_topic));

        $sql  = "UPDATE {$_TABLES['gf_topic']} SET forum=$move_to_forum, pid=$move_to_topic, subject='$subject' WHERE id=$topic_id ";
        DB_query($sql);
        DB_query("UPDATE {$_TABLES['gf_topic']} SET replies=replies-1 WHERE id=$curpostpid ");

        $movedDate = DB_getItem($_TABLES['gf_topic'],'date','id='.$topic_id);
        $targetDate = DB_getItem($_TABLES['gf_topic'],'date','id='.$move_to_topic);
        if ( $movedDate < $targetDate ) {
            DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=".$topic_id." WHERE id=".$move_to_topic);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=0 WHERE id=".$topic_id);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET pid=".$topic_id." WHERE pid=".$move_to_topic);
            $move_to_topic = $topic_id;
            $pidDate = $topicDate;
        }

        // Update Topic and Post Count for the effected forums
        // new forum
        $postCount   = DB_Count($_TABLES['gf_topic'],'forum',$move_to_forum);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$move_to_forum and pid=0");
        $topicCount  = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$move_to_forum");
        //oldforum
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$forum_id);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$forum_id and pid=0");
        $topic_count = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topic_count, post_count=$postCount WHERE forum_id=$forum_id");

        // Update the Forum and topic indexes
        gf_updateLastPost($forum_id,$curpostpid);
        gf_updateLastPost($move_to_forum, $move_to_topic);

        $link = $_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id";
        $retval .= forum_statusMessage($LANG_GF02['msg163'],$link,$LANG_GF02['msg163'],false,'',true);
    }
    CACHE_remove_instance('forumcb');
    return $retval;
}

function moderator_confirmDelete($topic_id,$topic_parent_id,$forum_id)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF01, $LANG_GF02;

    $retval = '';
    $message = '';

    $subject = DB_getItem($_TABLES['gf_topic'],"subject","id=$topic_id");

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');

    $T->set_file('confirm','mod_confirm.thtml');

    if ( $topic_parent_id == $topic_id ) {
        $message .= $LANG_GF02['msg65'] . '<br /><br />';
    }
    $message .= sprintf($LANG_GF02['msg64'],$topic_id,$subject);

    $T->set_var(array(
        'mod_action'        => 'modconfirmdelete',
        'topic_id'          => $topic_id,
        'topic_parent_id'   => $topic_parent_id,
        'forum_id'          => $forum_id,
        'subject'           => $subject,
        'message'           => $message,
        'button_text'       => $LANG_GF01['CONFIRM'],
    ));

    $T->parse ('output', 'confirm');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function moderator_confirmMove($topic_id,$topic_parent_id,$forum_id)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF01, $LANG_GF02, $LANG_GF03;

    $retval = '';
    $message = '';

    $subject = DB_getItem($_TABLES['gf_topic'],"subject","id=$topic_id");

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');

    $T->set_file('confirm','mod_confirm.thtml');

    $T->set_var(array(
        'mod_action'        => 'confirm_move',
        'topic_id'          => $topic_id,
        'topic_parent_id'   => $topic_parent_id,
        'forum_id'          => $forum_id,
        'subject'           => $subject,
    ));

    $SECgroups = SEC_getUserGroups();
    $modgroups = '';
    foreach ($SECgroups as $key) {
      if ($modgroups == '') {
         $modgroups = $key;
      } else {
          $modgroups .= ",$key";
      }
    }

    $forumList = array();
    $categoryResult = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($categoryResult)) {
        $cat_id = $A['cat_name'];

        if ( SEC_inGroup('Root') ) {
            $sql = "SELECT forum_id,forum_name,forum_dscp FROM {$_TABLES['gf_forums']} WHERE forum_cat ='{$A['id']}' ORDER BY forum_order ASC";
        } else {
            $sql = "SELECT * FROM {$_TABLES['gf_moderators']} a , {$_TABLES['gf_forums']} b ";
            $sql .= "WHERE b.forum_cat='{$A['id']}' AND a.mod_forum = b.forum_id AND (a.mod_uid='{$_USER['uid']}' OR a.mod_groupid in ($modgroups)) ORDER BY forum_order ASC";
        }
        $forumResult = DB_query($sql);

        while($B = DB_fetchArray($forumResult)) {
            if ( $B['forum_id'] != $forum_id ) {
                $forumList[$cat_id][$B['forum_id']] = $B['forum_name'];
            }
        }
    }

    $target = 0;
    $destination_forum_select = '<select name="movetoforum" id="movetoforum">' . LB;
    foreach ($forumList AS $category => $forums ) {
        if ( count ($forums) > 0 ) {
            $target = 1;
            $destination_forum_select .= '<optgroup label="'.$category.'">' . LB;
            foreach ($forums AS $id => $name ) {
                $destination_forum_select .= '<option value="'.$id.'">'.$name.'</option>'. LB;
            }
            $destination_forum_select .= '</optgroup>' . LB;
        }
    }
    $destination_forum_select .= '</select>';

    if ($target == 0) {
        $retval .= alertMessage($LANG_GF02['msg181'],$LANG_GF01['WARNING'],'',true);
    } else {
        $T->set_var('destination_forum_select',$destination_forum_select);
        $T->set_var('move_title',$subject);

        /* Check and see request to move complete topic or split the topic */
        if (DB_getItem($_TABLES['gf_topic'],"pid","id='$topic_id'") == 0) {
            $message .= sprintf($LANG_GF03['movetopicmsg'],$subject);
            $button_text = $LANG_GF03['movetopic'];
        } else {
            $poster   = DB_getItem($_TABLES['gf_topic'],"name","id='$topic_id'");
            $postdate = COM_getUserDateTimeFormat(DB_getItem($_TABLES['gf_topic'],"date","id='$topic_id'"));
            $button_text = $LANG_GF03['movetopic'];
            $message .= sprintf($LANG_GF03['splittopicmsg'],$subject,$poster,$postdate[0]);
            $T->set_var('split',1);
        }
    }
    $T->set_var('message',$message);
    $T->set_var('button_text',$button_text);

    $T->parse ('output', 'confirm');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function moderator_confirmBan($topic_id,$topic_parent_id,$forum_id)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF01, $LANG_GF02;

    $retval = '';
    $message = '';

    $subject = DB_getItem($_TABLES['gf_topic'],"subject","id=$topic_id");

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');

    $T->set_file('confirm','mod_confirm.thtml');

    $iptobansql = DB_query("SELECT ip FROM {$_TABLES['gf_topic']} WHERE id='$topic_id'");
    $forumpostipnum = DB_fetchArray($iptobansql);
    if ($forumpostipnum['ip'] == '') {
        $retval .= alertMessage($LANG_GF02['msg174'],'','',true);
        exit;
    }

    $message .= '<p>' .$LANG_GF02['msg68'] . '</p><p>';
    $message .= sprintf($LANG_GF02['msg69'],$forumpostipnum['ip']) . '</p>';

    $T->set_var(array(
        'mod_action'        => 'confirmbanip',
        'topic_id'          => $topic_id,
        'topic_parent_id'   => $topic_parent_id,
        'forum_id'          => $forum_id,
        'subject'           => $subject,
        'message'           => $message,
        'hostip'            => $forumpostipnum['ip'],
        'button_text'       => $LANG_GF01['CONFIRM'],
    ));

    $T->parse ('output', 'confirm');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}


function moderator_confirmMerge($topic_id,$topic_parent_id,$forum_id)
{
    global $_CONF, $_USER, $_TABLES, $CONF_FORUM, $LANG_GF01, $LANG_GF02, $LANG_GF03;

    $retval = '';
    $message = '';

    $pid = DB_getItem($_TABLES['gf_topic'],'pid','id='.$topic_id);
    if ( $pid == 0 ) {
        $message .= '<p style="padding-bottom:10px;">'.$LANG_GF03['mergeparent'].'<br /></p>';
    }

    $subject = DB_getItem($_TABLES['gf_topic'],"subject","id=$topic_id");

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');

    $T->set_file('confirm','mod_confirm.thtml');

    $T->set_var(array(
        'mod_action'        => 'confirm_merge',
        'topic_id'          => $topic_id,
        'topic_parent_id'   => $topic_parent_id,
        'forum_id'          => $forum_id,
        'subject'           => $subject,
    ));

    $button_text = $LANG_GF03['merge_topic'];
    $message .= $LANG_GF03['mergetopicmsg'];

    $T->set_var('merge',1);

    $T->set_var('message',$message);
    $T->set_var('button_text',$button_text);

    $T->parse ('output', 'confirm');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function moderator_error($type)
{
    global $forum_id, $_CONF, $LANG_GF02, $LANG_GF01;

    if ( $type == ACCESS_DENIED ) {
        echo COM_refresh($_CONF['site_url'].'/forum/index.php');
        exit;
    }
    $retval = alertMessage($LANG_GF02['msg166'],$LANG_GF01['WARNING'],'',true);
    gf_siteHeader();
    ForumHeader($forum_id,'');
    echo $retval;
    gf_siteFooter();
    exit;
}

$retval = '';
$modfunction     = COM_applyFilter($_POST['modfunction']);

// - these three must always be defined....
$topic_id        = COM_applyFilter($_POST['topic_id'],true);  // the topic id we are working on
$topic_parent_id = COM_applyFilter($_POST['topic_parent_id'],true); // the parent id
$forum_id        = COM_applyFilter($_POST['forum_id'],true); // the forum where topic resides

// check to see if we at least have some type of moderator access...
if (!forum_modPermission($forum_id,$_USER['uid'])) {
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}

if (isset($_POST['cancel']) ) {
    if ($modfunction == 'modconfirmdelete' && $topic_id != '') {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
    } else if ($modfunction == 'confirmbanip' ) {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
    } else if ($modfunction == 'confirm_move' && $topic_id != 0) {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
    } else if ($modfunction == 'confirm_merge' && $topic_id != 0 ) {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
    } else {
        echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    }
    exit;
}

if ($forum_id == 0) {
    $retval .= alertMessage($LANG_GF02['msg71'],'','',true);
} else {
    switch ( $modfunction ) {
        case 'deletepost' :
            if ( !forum_modPermission($forum_id,$_USER['uid'],'mod_delete') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $retval .= moderator_ConfirmDelete($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'editpost' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_edit')) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $page = COM_applyFilter($_POST['page'],true);
            echo COM_refresh($_CONF['site_url']."/forum/createtopic.php?method=edit&amp;id=$topic_id&amp;page=$page");
            exit;
            break;
        case 'movetopic' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $retval .= moderator_confirmMove($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'mergetopic' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $retval .= moderator_confirmMerge($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'banip' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_ban') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $retval .= moderator_confirmBan($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'modconfirmdelete' :
            if ( !forum_modPermission($forum_id,$_USER['uid'],'mod_delete') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $retval .= moderator_deletePost($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'confirm_move' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $move_to_forum  = COM_applyFilter($_POST['movetoforum'],true);
            $move_title     = COM_applyFilter($_POST['movetitle']);
            $splittype      = COM_applyFilter($_POST['splittype']);

            if ( !forum_modPermission($move_to_forum,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $splittype != 'single' && $splittype != 'remaining' ) {
                $splittype = '';
            }
            $retval .= moderator_movePost($topic_id,$topic_parent_id,$forum_id,$move_to_forum,$move_title,$splittype);
            break;
        case 'confirm_merge' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $move_to_forum  = COM_applyFilter($_POST['movetoforum'],true);
            $move_to_topic  = COM_applyFilter($_POST['mergetopic'],true);
            $splittype      = COM_applyFilter($_POST['splittype']);

            if ( !forum_modPermission($move_to_forum,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $splittype != 'single' && $splittype != 'remaining' ) {
                $splittype = '';
            }
            $retval .= moderator_mergePost($topic_id,$topic_parent_id,$forum_id,$move_to_forum,$move_to_topic,$splittype);
            break;
        case 'confirmbanip' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_ban') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }

            $hostip = COM_applyFilter($_POST['hostip']);

            $retval .= moderator_banIP($topic_id,$topic_parent_id,$forum_id, $hostip);
            break;
        default :
            $retval .= alertMessage($LANG_GF02['msg71'],'','',true);
            break;
    }
}

// Display Common headers
gf_siteHeader();
ForumHeader($forum_id,'');

echo $retval;

gf_siteFooter();
exit;
?>