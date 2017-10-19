<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2017 by Mark R. Evans - mark AT glfusion DOT org
*/

require_once '../lib-common.php';

if (!in_array('forum', $_PLUGINS)) {
    exit;
}

if (is_ajax()) {
    if (isset($_POST["action"]) && !empty($_POST["action"])) {
        $action = $_POST["action"];

        switch ( $action ) {
            case 'subscribe_forum' :
                subscribe();
                break;
            case 'unsubscribe_forum' :
                unsubscribe();
                break;
            case 'subscribe_topic' :
                subscribe_topic();
                break;
            case 'unsubscribe_topic' :
                unsubscribe_topic();
                break;
            case 'bookmark' :
                bookmark();
                break;
            case "test":
                test_function();
                break;
        }
    } else {
        die();
    }
}

/*
 * Determine if a valid ajax request
 */
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function subscribe_topic() {

    global $_CONF, $_FF_CONF, $_TABLES, $_USER, $LANG_GF01, $LANG_GF02;

    $retval = array();

    if (COM_isAnonUser()) {
        $retval['statusMessage'] = 'Invalid Request';
        $retval['errorCode'] = 1;
        $return["json"] = json_encode($retval);
        echo json_encode($return);
        exit();
    }

    $forum = COM_applyFilter($_POST['id'],true);
    $topic = COM_applyFilter($_POST['topic_id'],true);
    $notify_id = COM_applyFilter($_POST['notify_id'],true);

    $sql = "SELECT * FROM {$_TABLES['subscriptions']}
            WHERE ((type='forum' AND id=".(int)$topic.") AND (uid=".(int)$_USER['uid'].")
            OR ";
    $sql .= "((type='forum' AND category=".(int) $forum.") AND (id=0) and (uid=".(int) $_USER['uid'].")))";

    $notifyquery = DB_query("$sql");

    $pid = DB_getItem($_TABLES['ff_topic'],'pid',"id=".(int) $topic);

    if ($pid == 0) {
        $pid = $topic;
    }
    $ntopic = -$topic;

    if (DB_numRows($notifyquery) > 0 ) {
        $A = DB_fetchArray($notifyquery);
        if ($A['id'] == 0) {     // User has subscribed to complete forum
           // Check and see if user has a non-subscribe record for this topic id
            $query = DB_query("SELECT sub_id FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int) $forum." AND id = " . $ntopic );
            if (DB_numRows($query) > 0 ) {
                list($watchrec) = DB_fetchArray($query);
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE sub_id=".(int)$watchrec);
                $retval['statusMessage'] = $LANG_GF02['msg142'];
            }  else {
                $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
                $topic_name = DB_getItem($_TABLES['ff_topic'],'subject','id='.(int)$pid);
                DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',".(int)$pid.",'".DB_escapeString($topic_name)."',".(int)$_USER['uid'].",now() )");
                $retval['statusMessage'] = $LANG_GF02['msg142'];
            }
        } else {
            $retval['statusMessage'] = $LANG_GF02['msg40'];
        }
    } else {
        $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
        $topic_name = DB_getItem($_TABLES['ff_topic'],'subject','id='.(int)$pid);
        DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',".(int)$pid.",'".DB_escapeString($topic_name)."',".(int)$_USER['uid'].",now() )");
        $nid = -$notify_id;
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id = ".$nid);
        $retval['statusMessage'] = $LANG_GF02['msg142'];
    }
    $retval['errorCode'] = 0;
    $retval['icon'] = 'uk-icon-bookmark';
    $retval['subOption'] = 'unsubscribe_topic';
    $retval['label'] = $LANG_GF01['unSubscribeLink'];
    $return["json"] = json_encode($retval);
    echo json_encode($return);
    exit();
}

function unsubscribe_topic() {

    global $_CONF, $_FF_CONF, $_TABLES, $_USER, $LANG_GF01, $LANG_GF02;

    $retval = array();

    if (COM_isAnonUser()) {
        $retval['statusMessage'] = 'Invalid Request';
        $retval['errorCode'] = 1;
        $return["json"] = json_encode($retval);
        echo json_encode($return);
        exit();
    }

    $forum      = COM_applyFilter($_POST['id'],true);
    $topic      = COM_applyFilter($_POST['topic_id'],true);
    $notify_id  = COM_applyFilter($_POST['notify_id'],true);

    // Check and see if subscribed to complete forum and if so - unsubscribe to just this topic
    if (DB_getItem($_TABLES['subscriptions'], 'id', "type='forum' AND sub_id=".(int)$notify_id) == 0 ) {
        $ntopic = -(int)$topic;  // Negative Value
        $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
        $topic_name = DB_getItem($_TABLES['ff_topic'],'subject','id='.(int)$topic);
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id = ".(int)$topic);
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id = ".(int) $ntopic);
        DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',".(int)$ntopic.",'".DB_escapeString($topic_name)."',".(int)$_USER['uid'].",now() )");
    } else {
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE (sub_id=".(int)$notify_id.")");
    }
    $retval['statusMessage'] = $LANG_GF02['msg146'];
    $retval['errorCode'] = 0;
    $retval['icon'] = 'uk-icon-bookmark-o';
    $retval['subOption'] = 'subscribe_topic';
    $retval['label'] = $LANG_GF01['SubscribeLink'];
    $return["json"] = json_encode($retval);
    echo json_encode($return);
    exit();
}


function subscribe() {
    global $_USER, $_TABLES, $_FF_CONF, $_CONF, $LANG_GF01, $LANG_GF02;

    $retval = array();

    if (COM_isAnonUser()) {
        $retval['statusMessage'] = 'Invalid Request';
        $retval['errorCode'] = 1;
        $return["json"] = json_encode($retval);
        echo json_encode($return);
        exit();
    }

    $uid = $_USER['uid'];

    $forum = isset($_POST['id']) ? COM_applyFilter($_POST['id'],true) : 0;
    if ( $forum != 0 && $uid > 1 ) {
        $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
        DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',0,'".$LANG_GF02['msg138']."',".(int)$_USER['uid'].", now() )");
        // Delete all individual topic notification records
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id > 0" );
    }
    $retval['statusMessage'] = $LANG_GF02['msg135'];
    $retval['buttonText'] = $LANG_GF01['FORUMUNSUBSCRIBE'];
    $retval['label'] = $LANG_GF01['FORUMUNSUBSCRIBE'];
    $retval['icon'] = 'uk-icon-bookmark';
    $retval['subOption'] = 'unsubscribe_forum';
    $retval['errorCode'] = 0;
    $return["json"] = json_encode($retval);
    echo json_encode($return);
    exit();
}

function unsubscribe() {
    global $_USER, $_TABLES, $_FF_CONF, $_CONF, $LANG_GF01, $LANG_GF02;

    $retval = array();

    if (COM_isAnonUser()) {
        $retval['statusMessage'] = 'Invalid Request';
        $retval['errorCode'] = 1;
        $return["json"] = json_encode($retval);
        echo json_encode($return);
        exit();
    }

    $uid = $_USER['uid'];

    $forum = isset($_POST['id']) ? COM_applyFilter($_POST['id'],true) : 0;
    if ( $forum != 0 && $uid > 1 ) {
        // Delete all individual topic notification records
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum);
    }
    $retval['statusMessage'] = $LANG_GF02['msg146'];
    $retval['buttonText'] = $LANG_GF01['FORUMSUBSCRIBE'];
    $retval['subOption'] = 'subscribe_forum';
    $retval['label'] = $LANG_GF01['FORUMSUBSCRIBE'];
    $retval['icon'] = 'uk-icon-bookmark-o';
    $retval['errorCode'] = 0;
    $return["json"] = json_encode($retval);
    echo json_encode($return);
    exit();
}

function bookmark() {

    global $_CONF, $_FF_CONF, $_TABLES, $_USER;

    $image = 'closed';
    $id = isset($_POST['id']) ? COM_applyFilter($_POST['id'],true) : 0;
    if (!COM_isAnonUser() && $id != 0) {
        if (DB_count($_TABLES['ff_bookmarks'],array('uid','topic_id'),array((int)$_USER['uid'],(int)$id))) {
            DB_query("DELETE FROM {$_TABLES['ff_bookmarks']} WHERE uid=".(int) $_USER['uid']." AND topic_id=".(int)$id);
            $image = 'open';
        } elseif (DB_count($_TABLES['ff_bookmarks'],array('uid','pid'),array($_USER['uid'],$id))) {
            DB_query("DELETE FROM {$_TABLES['ff_bookmarks']} WHERE uid=".(int) $_USER['uid']." AND pid=".(int)$id);
            $image = 'open';
        } else {
            $pid = DB_getItem($_TABLES['ff_topic'],'pid',"id=".(int) $id);
            DB_query("INSERT INTO {$_TABLES['ff_bookmarks']} (uid,topic_id,pid) VALUES (".(int) $_USER['uid'].",".(int)$id.",".(int)$pid.")");
            $image = 'closed';
        }
        $retval['bookmark_image'] = $image;
        $retval['errorCode'] = 0;
    }
    $retval['id'] = $id;
    $retval['bookmark_image'] = $image;
    $return["json"] = json_encode($retval);
    echo json_encode($return);
    exit();
}

/*
 * Test function - used for debugging
 */
function test_function(){

  $return = $_POST;

  $return["json"] = json_encode($return);
  echo json_encode($return);
}
?>