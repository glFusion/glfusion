<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | moderation.php                                                           |
// |                                                                          |
// | Moderation routines                                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
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

require_once '../lib-common.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

// validate we have a logged in user..
if ( COM_isAnonUser() ) {
    echo COM_refresh($_CONF['site_url'].'/users.php');
    exit;
}

USES_forum_functions();
USES_forum_format();
USES_forum_topic();

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
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF02;

    $retval = '';

    $topicparent = DB_getItem($_TABLES['ff_topic'],"pid","id=". (int) $topic_id);
    if ($topicparent == 0) {
        // Need to check for any attachments and delete if required
        $q1 = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE pid=".(int) $topic_id." OR id=".(int) $topic_id);
        while($A = DB_fetchArray($q1)) {
            $q2 = DB_query("SELECT id FROM {$_TABLES['ff_attachments']} WHERE topic_id=".(int) $A['id']);
            while ($B = DB_fetchArray($q2)) {
                forum_delAttachment($B['id']);
            }
            PLG_itemDeleted($A['id'],'forum');
        }
        \Forum\Like::remAllLikes($topic_id, true);
        DB_query("DELETE FROM {$_TABLES['ff_topic']} WHERE id=".(int) $topic_id);
        DB_query("DELETE FROM {$_TABLES['ff_topic']} WHERE pid=".(int) $topic_id);
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE (type='forum' AND id=".(int)$topic_id.")");
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $forum_id);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $forum_id." AND pid=0");
        $topicCount = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int) $topicCount.",post_count=".(int) $postCount." WHERE forum_id=".(int) $forum_id);
        // Remove any lastviewed records in the log so that the new updated topic indicator will appear
        DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE topic=".(int) $topicparent);
    } else {
        // Need to check for any attachments and delete if required
        $q1 = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE id=".(int) $topic_id);
        while($A = DB_fetchArray($q1)) {
            $q2 = DB_query("SELECT id FROM {$_TABLES['ff_attachments']} WHERE topic_id=".(int) $A['id']);
            while ($B = DB_fetchArray($q2)) {
                forum_delAttachment($B['id']);
            }
        }
        DB_query("UPDATE {$_TABLES['ff_topic']} SET replies=replies-1 WHERE id=".(int) $topicparent);
        \Forum\Like::remAllLikes($topic_id, false);
        DB_query("DELETE FROM {$_TABLES['ff_topic']} WHERE id=".(int) $topic_id);
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $forum_id);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET post_count=".(int) $postCount." WHERE forum_id=".(int) $forum_id);

        $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $topicparent. " OR topic.pid=".(int) $topicparent.") and att.filename <> ''";
        $result = DB_query($sql);
        if ( DB_numRows($result) > 0 ) {
            list($attCount) = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".(int) $attCount." WHERE id=".(int) $topicparent);
        }
        PLG_itemDeleted($topic_id,'forum');
    }
    if ( $topicparent == 0 ) {
        $topicparent = $topic_id;
        gf_updateLastPost($forum_id);
    } else {
        gf_updateLastPost($forum_id,$topicparent);
    }
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('forumcb');

    if ($topicparent == $topic_id ) {
        $link = $_CONF['site_url'].'/forum/index.php?forum='.$forum_id;
        $retval .= FF_statusMessage($LANG_GF02['msg55'],$link,$LANG_GF02['msg55'],true,$forum_id,true);
    } else {
        $link = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topicparent;
        $retval .= FF_statusMessage($LANG_GF02['msg55'],$link,$LANG_GF02['msg55'],true,$forum_id,true);
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
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF02;

    $retval = '';

    DB_query("INSERT INTO {$_TABLES['ff_banned_ip']} (host_ip) VALUES ('".DB_escapeString($hostip)."')");

    $link = $_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id";

    $retval .= FF_statusMessage($LANG_GF02['msg56'],$link,$LANG_GF02['msg56'],false,'',true);

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
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF02;

    $retval = '';

    $date = time();
    $movetitle = _ff_preparefordb($new_topic_title,'text');
    $newforumid = $move_to_forum;
    /* Check and see if we are splitting this forum thread */

    if ($splittype != '') {
        $curpostpid = DB_getItem($_TABLES['ff_topic'],"pid","id=".(int) $topic_id);
        if ( $curpostpid == '' || $curpostpid == 0 ) {
            echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
            exit();
        }
        if ($splittype == 'single' ) {  // Move only the single post - create a new topic
            $topicdate = DB_getItem($_TABLES['ff_topic'],"date","id=".(int) $topic_id);
            $sql  = "UPDATE {$_TABLES['ff_topic']} SET forum=".(int) $move_to_forum.", pid=0,lastupdated='".DB_escapeString($topicdate)."', ";
            $sql .= "subject='".DB_escapeString($movetitle)."', replies=0 WHERE id=".(int) $topic_id;
            DB_query($sql);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET replies=replies-1 WHERE id=".(int) $curpostpid);

            // Update Topic and Post Count for the effected forums
            // new forum
            $postCount   = DB_Count($_TABLES['ff_topic'],'forum',(int) $move_to_forum);
            $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $move_to_forum." AND pid=0");
            $topicCount  = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topicCount.", post_count=".(int)$postCount." WHERE forum_id=".(int)$move_to_forum);
            //oldforum
            $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int)$forum_id);
		    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $forum_id." AND pid=0");
		    $topic_count = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topic_count.", post_count=".(int)$postCount." WHERE forum_id=".(int)$forum_id);

            // Update the Forum and topic indexes
            gf_updateLastPost($forum_id,$curpostpid);
            gf_updateLastPost($move_to_forum,$topic_id);

            $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $curpostpid. " OR topic.pid=".(int) $curpostpid.") and att.filename <> ''";
            $result = DB_query($sql);
            if ( DB_numRows($result) > 0 ) {
                list($attCount) = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".(int) $attCount." WHERE id=".(int) $curpostpid);
            }
            $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $topic_id. " OR topic.pid=".(int) $topic_id.") and att.filename <> ''";
            $result = DB_query($sql);
            if ( DB_numRows($result) > 0 ) {
                list($attCount) = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".(int) $attCount." WHERE id=".(int) $topic_id);
            }
        } else { // move all posts from this point forward.
            $movesql = DB_query("SELECT id,date FROM {$_TABLES['ff_topic']} WHERE pid=".(int) $curpostpid." AND id >= ".(int) $topic_id);
            $numreplies = DB_numRows($movesql); // how many replies are being moved.
            $topicparent = 0;
            while($movetopic = DB_fetchArray($movesql)) {
                if ($topicparent == 0) {
                    $sql  = "UPDATE {$_TABLES['ff_topic']} SET forum=".(int)$move_to_forum.", pid=0,lastupdated='".DB_escapeString($movetopic['date'])."', ";
                    $sql .= "replies=".(int) ($numreplies-1).", subject='".DB_escapeString($movetitle)."' WHERE id=".(int)$movetopic['id'];
                    DB_query($sql);
                    $topicparent = $movetopic['id'];
                } else {
                    $sql  = "UPDATE {$_TABLES['ff_topic']} SET forum=".(int)$move_to_forum.", pid=".(int)$topicparent.", ";
                    $sql .= "subject='".DB_escapeString($movetitle)."' WHERE id=".(int)$movetopic['id'];
                    DB_query($sql);
                    $topicdate = DB_getItem($_TABLES['ff_topic'],"date","id=".(int)$movetopic['id']);
                    DB_query("UPDATE {$_TABLES['ff_topic']} SET lastupdated='".DB_escapeString($topicdate)."' WHERE id=".(int)$topicparent);
                }
            }
            // update counters
            // new forum
            $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $move_to_forum);
            $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int)$move_to_forum." AND pid=0");
            $topicCount = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topicCount.", post_count=".(int)$postCount." WHERE forum_id=".(int)$move_to_forum);

            $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $topicparent. " OR topic.pid=".(int) $topicparent.") and att.filename <> ''";
            $result = DB_query($sql);
            if ( DB_numRows($result) > 0 ) {
                list($attCount) = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".(int) $attCount." WHERE id=".(int) $topicparent);
            }
            //oldforum
            $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int)$forum_id);
		    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int)$forum_id." AND pid=0");
		    $topic_count = DB_numRows($topicsQuery);
            DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=$topic_count, post_count=$postCount WHERE forum_id=$forum_id");
            $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $curpostpid. " OR topic.pid=".(int) $curpostpid.") and att.filename <> ''";
            $result = DB_query($sql);
            if ( DB_numRows($result) > 0 ) {
                list($attCount) = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".$attCount." WHERE id=".(int) $curpostpid);
            }
            // Update the Forum and topic indexes
            gf_updateLastPost($forum_id,$curpostpid);
            gf_updateLastPost($move_to_forum,$topicparent);
        }
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('forumcb');
        $link = "{$_CONF['site_url']}/forum/viewtopic.php?showtopic=$topic_id";
        $retval .= FF_statusMessage(sprintf($LANG_GF02['msg183'],$move_to_forum),$link,$LANG_GF02['msg183'],false,'',true);
    } else {  // Move complete topic
        $moveResult = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE pid=".(int) $topic_id);
        $postCount = DB_numRows($moveResult)+1;  // Need to account for the parent post
        while($movetopic = DB_fetchArray($moveResult)) {
            DB_query("UPDATE {$_TABLES['ff_topic']} SET forum=$move_to_forum WHERE id={$movetopic['id']}");
        }
        // Update any topic subscription records - need to change the forum ID record
        if ( DB_count($_TABLES['subscriptions'],array('type,category,id'),array('forum',$move_to_forum,0)) == 0 ) {
            DB_query("UPDATE {$_TABLES['subscriptions']} SET category=$move_to_forum WHERE type='forum' AND id=".(int)$topic_id);
        } else {
            DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND id=".(int)$topic_id);
        }
        // this moves the parent record.
        DB_query("UPDATE {$_TABLES['ff_topic']} SET forum=".(int) $move_to_forum.", moved=1 WHERE id=".(int)$topic_id);
        // new forum
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $newforumid);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $move_to_forum." AND pid=0");
        $topicCount = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topicCount.", post_count=".(int)$postCount." WHERE forum_id=".(int)$move_to_forum);
        //oldforum
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $forum_id);
	    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $forum_id." AND pid=0");
	    $topic_count = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topic_count.", post_count=".(int)$postCount." WHERE forum_id=".(int)$forum_id);

        // Update the Last Post Information
        gf_updateLastPost($move_to_forum,$topic_id);
        gf_updateLastPost($forum_id);

        // Remove any lastviewed records in the log so that the new updated topic indicator will appear
        DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE topic=".(int) $topic_id);
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('forumcb');
        $link = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id;
        $retval .= FF_statusMessage($LANG_GF02['msg163'],$link,$LANG_GF02['msg163'],false,'',true);
    }

    return $retval;

}

function moderator_mergePost($topic_id,$topic_parent_id,$forum_id, $move_to_forum, $move_to_topic,$splittype)
{
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF02;

    $retval = '';

    // right now we are only implementing moving a single post.

    if ( $move_to_topic == 0 ) {
        echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
        exit();
    }

    $curpostpid = DB_getItem($_TABLES['ff_topic'],"pid","id=".(int)$topic_id);
    if ( $curpostpid == '' ) {
        echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
        exit();
    }

    $move_to_forum = DB_getItem($_TABLES['ff_topic'],"forum","id=".(int)$move_to_topic);

    if ( $move_to_forum == 0 || $move_to_forum == '' ) {
        echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
        exit();
    }

    // ensure move_to_topic is a parent id
    $move_to_topic_pid = DB_getItem($_TABLES['ff_topic'],'pid','id='.(int) $move_to_topic);
    if ( $move_to_topic_pid != 0 && $move_to_topic_pid != '' ) {
        $move_to_topic = $move_to_topic_pid;
    }

    if ($curpostpid == 0 ) {
        $subject = DB_escapeString(DB_getItem($_TABLES['ff_topic'],'subject','id='.(int) $move_to_topic));
        $pidDate = DB_getItem($_TABLES['ff_topic'],'date','id='.(int) $move_to_topic);
        $moveResult = DB_query("SELECT id,date FROM {$_TABLES['ff_topic']} WHERE pid=".(int) $topic_id);
        $postCount = DB_numRows($moveResult)+1;  // Need to account for the parent post
        while($movetopic = DB_fetchArray($moveResult)) {
            DB_query("UPDATE {$_TABLES['ff_topic']} SET forum=".(int)$move_to_forum.",pid=".(int)$move_to_topic.",subject='".$subject."' WHERE id=".(int) $movetopic['id']);
            // check to see if we need to swap pids
            if ( $movetopic['date'] < $pidDate ) {
                DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=".(int) $movetopic['id']." WHERE id=".(int) $move_to_topic);
                DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=0 WHERE id=".(int) $movetopic['id']);
                DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=".(int)$movetopic['id']." WHERE pid=".(int)$move_to_topic);
                $move_to_topic = $movetopic['id'];
                $pidDate = $movetopic['date'];
            }
        }
        // Update any topic subscription records - need to change the forum ID record
        //check if the whole forum is already subscribed to?
        if ( DB_count($_TABLES['subscriptions'],array('type,category,id'),array('forum',(int) $move_to_forum,0)) == 0 ) {
            DB_query("UPDATE {$_TABLES['subscriptions']} SET category=".(int)$move_to_forum." WHERE type='forum' AND id=".(int)$topic_id);
        } else {
            DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND id=".(int)$topic_id);
        }
        // this moves the parent record.
        DB_query("UPDATE {$_TABLES['ff_topic']} SET forum=".(int)$move_to_forum.",pid=".(int)$move_to_topic.",subject='".$subject."' WHERE id=".(int)$topic_id);
        $topicDate = DB_getItem($_TABLES['ff_topic'],'date','id='.(int) $topic_id);
        if ( $topicDate < $pidDate ) {
            DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=".(int) $topic_id." WHERE id=".(int) $move_to_topic);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=0 WHERE id=".(int) $topic_id);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=".(int) $topic_id." WHERE pid=".(int) $move_to_topic);
            $move_to_topic = $topic_id;
            $pidDate = $topicDate;
        }
        // new forum
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $move_to_forum);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $move_to_forum." AND pid=0");
        $topicCount = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topicCount.", post_count=".(int)$postCount." WHERE forum_id=".(int)$move_to_forum);

        $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $move_to_topic. " OR topic.pid=".(int) $move_to_topic.") and att.filename <> ''";
        $result = DB_query($sql);
        if ( DB_numRows($result) > 0 ) {
            list($attCount) = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".$attCount." WHERE id=".(int) $move_to_topic);
        }

        //oldforum
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $forum_id);
	    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $forum_id." AND pid=0");
	    $topic_count = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int) $topic_count.", post_count=".(int)$postCount." WHERE forum_id=".(int)$forum_id);

        $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $topic_id. " OR topic.pid=".(int) $topic_id.") and att.filename <> ''";
        $result = DB_query($sql);
        if ( DB_numRows($result) > 0 ) {
            list($attCount) = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".(int) $attCount." WHERE id=".(int) $topic_id);
        }

        // Update the Last Post Information
        gf_updateLastPost($move_to_forum,$topic_id);
        gf_updateLastPost($forum_id);

        // Remove any lastviewed records in the log so that the new updated topic indicator will appear
        DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE topic=".(int)$topic_id);
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('forumcb');
        $link = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id;
        $retval .= FF_statusMessage($LANG_GF02['msg163'],$link,$LANG_GF02['msg163'],false,'',true);
    } else {
        $subject = DB_escapeString(DB_getItem($_TABLES['ff_topic'],'subject','id='.(int) $move_to_topic));

        $sql  = "UPDATE {$_TABLES['ff_topic']} SET forum=".(int) $move_to_forum.", pid=".(int) $move_to_topic.", subject='".$subject."' WHERE id=".(int)$topic_id;
        DB_query($sql);
        DB_query("UPDATE {$_TABLES['ff_topic']} SET replies=replies-1 WHERE id=".(int)$curpostpid);

        $movedDate = DB_getItem($_TABLES['ff_topic'],'date','id='.(int)$topic_id);
        $targetDate = DB_getItem($_TABLES['ff_topic'],'date','id='.(int) $move_to_topic);
        if ( $movedDate < $targetDate ) {
            DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=".(int)$topic_id." WHERE id=".(int)$move_to_topic);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=0 WHERE id=".(int)$topic_id);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET pid=".(int)$topic_id." WHERE pid=".(int)$move_to_topic);
            $move_to_topic = $topic_id;
            $pidDate = $movedDate;
        }

        // Update Topic and Post Count for the effected forums
        // new forum
        $postCount   = DB_Count($_TABLES['ff_topic'],'forum',(int) $move_to_forum);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int)$move_to_forum." AND pid=0");
        $topicCount  = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int)$topicCount.", post_count=".(int)$postCount." WHERE forum_id=".(int)$move_to_forum);

        $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $move_to_topic. " OR topic.pid=".(int) $move_to_topic.") and att.filename <> ''";
        $result = DB_query($sql);
        if ( DB_numRows($result) > 0 ) {
            list($attCount) = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".$attCount." WHERE id=".(int) $move_to_topic);
        }

        //oldforum
        $postCount = DB_Count($_TABLES['ff_topic'],'forum',(int) $forum_id);
        $topicsQuery = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".(int) $forum_id." AND pid=0");
        $topic_count = DB_numRows($topicsQuery);
        DB_query("UPDATE {$_TABLES['ff_forums']} SET topic_count=".(int) $topic_count.", post_count=".(int)$postCount." WHERE forum_id=".(int)$forum_id);

        $sql = "SELECT count(*) AS count FROM {$_TABLES['ff_topic']} topic left join {$_TABLES['ff_attachments']} att ON topic.id=att.topic_id WHERE (topic.id=".(int) $curpostpid. " OR topic.pid=".(int) $curpostpid.") and att.filename <> ''";
        $result = DB_query($sql);
        if ( DB_numRows($result) > 0 ) {
            list($attCount) = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=".$attCount." WHERE id=".(int) $curpostpid);
        }

        // Update the Forum and topic indexes
        gf_updateLastPost($forum_id,$curpostpid);
        gf_updateLastPost($move_to_forum, $move_to_topic);
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('forumcb');
        $link = $_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id";
        $retval .= FF_statusMessage($LANG_GF02['msg163'],$link,$LANG_GF02['msg163'],false,'',true);
    }
    return $retval;
}

function moderator_confirmDelete($topic_id,$topic_parent_id,$forum_id)
{
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF01, $LANG_GF02;

    $retval = '';
    $message = '';

    $subject = DB_getItem($_TABLES['ff_topic'],"subject","id=".(int) $topic_id);

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
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF01, $LANG_GF02, $LANG_GF03;

    $retval = '';
    $message = '';

    $modfunction = COM_applyFilter($_POST['modfunction']);

    $subject = DB_getItem($_TABLES['ff_topic'],"subject","id=".(int) $topic_id);

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
	$splitTopic = false;
	/* Check and see request to move complete topic or split the topic */
	if (DB_getItem($_TABLES['ff_topic'],"pid","id=".(int) $topic_id) == 0) {
		$splitTopic = false;
	} else {
		$splitTopic = true;
	}
    $forumList = array();
    $categoryResult = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($categoryResult)) {
        $cat_id = $A['cat_name'];

        if ( SEC_inGroup('Root') ) {
            $sql = "SELECT forum_id,forum_name,forum_dscp FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int)$A['id']." ORDER BY forum_order ASC";
        } else {
            $sql = "SELECT * FROM {$_TABLES['ff_moderators']} a , {$_TABLES['ff_forums']} b ";
            $sql .= "WHERE b.forum_cat=".(int) $A['id']." AND a.mod_forum = b.forum_id AND (a.mod_uid=".(int) $_USER['uid']." OR a.mod_groupid in ($modgroups)) ORDER BY forum_order ASC";
        }
        $forumResult = DB_query($sql);

        while($B = DB_fetchArray($forumResult)) {
            $forumList[$cat_id][$B['forum_id']] = $B['forum_name'];
        }
    }

    $target = 0;
    $destination_forum_select = '<select name="movetoforum" id="movetoforum">' . LB;
    foreach ($forumList AS $category => $forums ) {
        if ( count ($forums) > 0 ) {
            $target = 1;
            $destination_forum_select .= '<optgroup label="'.$category.'">' . LB;
            foreach ($forums AS $id => $name ) {
            	if ( $splitTopic == false ) {
            		if ( $id != $forum_id ) {
            			$destination_forum_select .= '<option value="'.$id.'">'.$name.'</option>'. LB;
            		} else {
            			$destination_forum_select .= '<option value="'.$id.'" disabled="disabled">'.$name.'</option>'. LB;
            		}
            	} else {
	                $destination_forum_select .= '<option value="'.$id.'">'.$name.'</option>'. LB;
	            }
            }
            $destination_forum_select .= '</optgroup>' . LB;
        }
    }
    $destination_forum_select .= '</select>';

    if ($target == 0) {
        $retval = _ff_alertMessage($LANG_GF02['msg181'],$LANG_GF01['WARNING'],'',true);
        return $retval;
    } else {
        $T->set_var('destination_forum_select',$destination_forum_select);
        $T->set_var('move_title',$subject);

        /* Check and see request to move complete topic or split the topic */
 		if ( $splitTopic == false ) {
            $message .= sprintf($LANG_GF03['movetopicmsg'],$subject);
            $button_text = $LANG_GF03['movetopic'];
        } else {
            $poster   = DB_getItem($_TABLES['ff_topic'],"name","id=".(int) $topic_id);
            $postdate = COM_getUserDateTimeFormat(DB_getItem($_TABLES['ff_topic'],"date","id=".(int)$topic_id));
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
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF01, $LANG_GF02;

    $retval = '';
    $message = '';

    $subject = DB_getItem($_TABLES['ff_topic'],"subject","id=".(int)$topic_id);

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');

    $T->set_file('confirm','mod_confirm.thtml');

    $iptobansql = DB_query("SELECT ip FROM {$_TABLES['ff_topic']} WHERE id=".(int)$topic_id);
    $forumpostipnum = DB_fetchArray($iptobansql);
    if ($forumpostipnum['ip'] == '') {
        $retval .= _ff_alertMessage($LANG_GF02['msg174'],'','',true);
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
    global $_CONF, $_USER, $_TABLES, $_FF_CONF, $LANG_GF01, $LANG_GF02, $LANG_GF03;

    $retval = '';
    $message = '';

    $pid = DB_getItem($_TABLES['ff_topic'],'pid','id='.(int) $topic_id);
    if ( $pid == 0 ) {
        $message .= '<p style="padding-bottom:10px;">'.$LANG_GF03['mergeparent'].'<br /></p>';
    }

    $subject = DB_getItem($_TABLES['ff_topic'],"subject","id=".(int) $topic_id);

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

    $display = '';

    if ( $type == ACCESS_DENIED ) {
        echo COM_refresh($_CONF['site_url'].'/forum/index.php');
        exit;
    }
    $display  = FF_siteHeader();
    $display .= FF_ForumHeader($forum_id,'');
    $display .= _ff_alertMessage($LANG_GF02['msg166'],$LANG_GF01['WARNING'],'',true);
    $display .= FF_siteFooter();
    echo $display;
    exit;
}

$pageBody = '';
$modfunction     = isset($_POST['modfunction']) ? COM_applyFilter($_POST['modfunction']) : '';
$topic_id        = isset($_POST['topic_id']) ? COM_applyFilter($_POST['topic_id'],true) : 0;  // the topic id we are working on
$topic_parent_id = isset($_POST['topic_parent_id']) ? COM_applyFilter($_POST['topic_parent_id'],true) : 0; // the parent id
$forum_id        = isset($_POST['forum_id']) ? COM_applyFilter($_POST['forum_id'],true) : 0; // the forum where topic resides

// check to see if we at least have some type of moderator access...
if (!forum_modPermission($forum_id,$_USER['uid'])) {
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}

if (isset($_POST['cancel']) ) {
    if ($modfunction == 'modconfirmdelete' && $topic_id != '') {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
        exit;
    } else if ($modfunction == 'confirmbanip' ) {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
        exit;
    } else if ($modfunction == 'confirm_move' && $topic_id != 0) {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
        exit;
    } else if ($modfunction == 'confirm_merge' && $topic_id != 0 ) {
        echo COM_refresh($_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topic_id);
        exit;
    } else {
        echo COM_refresh($_CONF['site_url'].'/forum/index.php');
        exit;
    }
    exit;
}

if ($forum_id == 0) {
    $pageBody .= _ff_alertMessage($LANG_GF02['msg71'],'','',true);
} else {
    switch ( $modfunction ) {
        case 'deletepost' :
            if ( !forum_modPermission($forum_id,$_USER['uid'],'mod_delete') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $pageBody .= moderator_ConfirmDelete($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'editpost' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_edit')) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $page = isset($_POST['page']) ? COM_applyFilter($_POST['page'],true) : 0;
            echo COM_refresh($_CONF['site_url']."/forum/createtopic.php?mode=edittopic&amp;id=$topic_id&amp;page=$page");
            exit;
            break;
        case 'movetopic' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $pageBody .= moderator_confirmMove($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'mergetopic' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $pageBody .= moderator_confirmMerge($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'banip' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_ban') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $pageBody .= moderator_confirmBan($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'modconfirmdelete' :
            if ( !forum_modPermission($forum_id,$_USER['uid'],'mod_delete') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $pageBody .= moderator_deletePost($topic_id,$topic_parent_id,$forum_id);
            break;
        case 'confirm_move' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $move_to_forum  = isset($_POST['movetoforum']) ? COM_applyFilter($_POST['movetoforum'],true) : 0;
            $move_title     = isset($_POST['movetitle']) ? COM_applyFilter($_POST['movetitle']) : '';
            $splittype      = isset($_POST['splittype']) ? COM_applyFilter($_POST['splittype']) : '';

            if ( !forum_modPermission($move_to_forum,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $splittype != 'single' && $splittype != 'remaining' ) {
                $splittype = '';
            }
            $pageBody .= moderator_movePost($topic_id,$topic_parent_id,$forum_id,$move_to_forum,$move_title,$splittype);
            break;
        case 'confirm_merge' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $move_to_topic  = isset($_POST['mergetopic']) ? COM_applyFilter($_POST['mergetopic'],true) : 0;
            $splittype = '';
            $move_to_forum = DB_getItem($_TABLES['ff_topic'],'forum','id='.(int)$move_to_topic);
            if ( $move_to_forum == '' ) {
                moderator_error(ACCESS_DENIED);
            }

            if ( !forum_modPermission($move_to_forum,$_USER['uid'],'mod_move') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $splittype != 'single' && $splittype != 'remaining' ) {
                $splittype = '';
            }
            $pageBody .= moderator_mergePost($topic_id,$topic_parent_id,$forum_id,$move_to_forum,$move_to_topic,$splittype);
            break;
        case 'confirmbanip' :
            if ( ! forum_modPermission($forum_id,$_USER['uid'],'mod_ban') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0) {
                moderator_error(ERROR_TOPIC_ID);
            }

            $hostip = isset($_POST['hostip']) ? COM_applyFilter($_POST['hostip']) : '';

            $pageBody .= moderator_banIP($topic_id,$topic_parent_id,$forum_id, $hostip);
            break;

        case 'locktopic' :
            if ( !forum_modPermission($forum_id,$_USER['uid'],'mod_edit') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $sql = "UPDATE {$_TABLES['ff_topic']} SET locked=1 WHERE id=".(int) $topic_id;
            DB_query($sql);
            echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
            break;
        case 'unlocktopic' :
            if ( !forum_modPermission($forum_id,$_USER['uid'],'mod_edit') ) {
                moderator_error(ACCESS_DENIED);
            }
            if ( $topic_id == 0 ) {
                moderator_error(ERROR_TOPIC_ID);
            }
            $sql = "UPDATE {$_TABLES['ff_topic']} SET locked=0 WHERE id=".(int) $topic_id;
            DB_query($sql);
            echo COM_refresh($_CONF['site_url']."/forum/viewtopic.php?showtopic=$topic_id");
            break;


        default :
            $pageBody .= _ff_alertMessage($LANG_GF02['msg71'],'','',true);
            break;
    }
}

// Display Common headers
$display  = FF_siteHeader();
$display .= FF_ForumHeader($forum_id,'');
$display .= $pageBody;
$display .= FF_siteFooter();
echo $display;
exit;
?>
