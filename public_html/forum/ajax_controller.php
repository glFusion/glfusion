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
            case 'admin_toggle':
                admin_toggle();
                break;
            case 'like':
                user_like();
                break;
            case 'vote':
                user_vote();
                break;
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
            case 'warn_getform':
                $retval = Forum\Modules\Warning\Warning::getPopupForm((int)$_POST['uid'], (int)$_POST['t_id']);
                echo $retval;
                exit;
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


function user_vote()
{
    global $_CONF, $_FF_CONF, $_TABLES, $_USER, $LANG_GF01;

    if ( !$_FF_CONF['enable_user_rating_system'] ) {
        exit;
    }

    $v_uid = isset($_POST['v_uid']) ? COM_applyFilter($_POST['v_uid'],true) : 0;
    $t_uid = isset($_POST['t_uid']) ? COM_applyFilter($_POST['t_uid'],true) : 0;
    $t_id  = isset($_POST['t_id']) ? COM_applyFilter($_POST['t_id'],true) : 0;
    $vote  = isset($_POST['vote']) ? COM_applyFilter($_POST['vote'],true) : 0;
    $vote  = $vote > 0 ? 1 : -1;

    // Can't vote if:
    //      Anonymous
    //      current user doesn't match supplied user ID,
    //      topic ID is invalid
    //      current user is also the topic poster
    if (
        COM_isAnonUser() || $_USER['uid'] != $v_uid || $t_id < 1 ||
            $_USER['uid'] == $t_uid
    ) {
        echo json_encode(array(
            'statusMessage' => 'Cannot vote',
        ) );
        exit;
    }

    $existing_vote = DB_getItem($_TABLES['ff_rating_assoc'], 'grade',
            "user_id = {$t_uid} AND voter_id = {$v_uid}");
    $user_already_voted = $existing_vote === NULL ? false : true;
    $user_rating = DB_getItem($_TABLES['ff_userinfo'], 'rating', "uid = {$t_uid}");
    if ($user_rating === NULL) {
        // There is no user info record for this user yet. See if there are rated posts to
        // sync up with
        $check = DB_query("SELECT SUM(grade) AS user_rating FROM {$_TABLES['ff_rating_assoc']}
                            WHERE user_id = $t_uid");
        $check_row = DB_fetchArray($check);
        $user_rating = (int)$check_row['user_rating'];
        DB_query("INSERT INTO {$_TABLES['ff_userinfo']}
                (uid,location,aim,icq,yim,msnm,interests,occupation,signature,rating)
                VALUES ($t_uid,'','','','','','','','',$user_rating);", 1);
    } else {
        $user_rating = (int)$user_rating;
    }
    $user_rating = $user_rating + $vote;

    if (!$user_already_voted) {       // Normal voting
        DB_query("UPDATE {$_TABLES['ff_userinfo']} SET rating = $user_rating WHERE uid = $t_uid");
        DB_query("INSERT INTO {$_TABLES['ff_rating_assoc']}
                    (user_id, voter_id, grade, topic_id)
        		VALUES ($t_uid, $v_uid, $vote, $t_id)");
    } else {    // retract vote
        // validate an entry exists
        if ($existing_vote != $vote) {  // Can only retract, not add votes
            DB_query("UPDATE {$_TABLES['ff_userinfo']} SET rating = $user_rating WHERE uid = {$t_uid}");
            //Delete Their vote in the associative table
            DB_delete($_TABLES['ff_rating_assoc'], array('user_id', 'voter_id'), array($t_uid, $v_uid));
        }
        $vote = 0;
    }

    if ($vote == 0) {
    	// user has never voted for this poster
		$vote_language = $LANG_GF01['grade_user'];
        $plus_vote = true;
        $minus_vote = true;
    } else {
        // user has already voted for this poster
        $vote_language = $LANG_GF01['retract_grade'];
        if ($vote > 0) {
            // gave a +1 show the minus to retract
            $plus_vote = false;
            $minus_vote = true;
		} else {
            // gave a -1 show the plus to retract
            $minus_vote = false;
            $plus_vote = true;
		}
	}

    $retval = json_encode(array(
        't_id' => $t_id,
        't_uid' => $t_uid,
        'v_uid' => $v_uid,
        'plus_vote' => $plus_vote,
        'minus_vote' => $minus_vote,
        'vote_language' => $vote_language,
        'rating' => $user_rating,
        'statusMessage' => '',
    ) );
    header("Cache-Control: no-store, no-cache, must-revalidate");
    echo $retval;
    exit;
}

function user_like()
{
    global $_CONF, $_FF_CONF, $_TABLES, $_USER, $LANG_GF01;

    if ( !$_FF_CONF['enable_likes'] ) {
        exit;
    }

    $v_uid = isset($_POST['v_uid']) ? COM_applyFilter($_POST['v_uid'],true) : 0;
    $t_uid = isset($_POST['t_uid']) ? COM_applyFilter($_POST['t_uid'],true) : 0;
    $t_id  = isset($_POST['t_id']) ? COM_applyFilter($_POST['t_id'],true) : 0;
    $vote  = isset($_POST['vote']) ? COM_applyFilter($_POST['vote'],true) : 0;
    $vote  = $vote > 0 ? 1 : 0;

    // Can't vote if:
    //      Anonymous
    //      current user doesn't match supplied user ID,
    //      topic ID is invalid
    //      current user is also the topic poster
    if (
        COM_isAnonUser() || $_USER['uid'] != $v_uid || $t_id < 1 ||
            $_USER['uid'] == $t_uid
    ) {
        echo json_encode(array(
            'statusMessage' => 'Cannot vote',
        ) );
        exit;
    }

    if ($vote == 1) {
        \Forum\Like::addLike($v_uid, $t_uid, $t_id, $_USER['username']);
    } else {
        $vote = 0;
        \Forum\Like::remLike($v_uid, $t_uid, $t_id);
    }

    $retval = json_encode(array(
        't_id' => $t_id,
        't_uid' => $t_uid,
        'v_uid' => $v_uid,
        'vote'  => $vote,
        'vote_count' => \Forum\Like::CountLikesReceived($t_uid),
        'likes_text' => \Forum\Like::getLikesText($t_id),
        'statusMessage' => '',
    ) );

    header("Cache-Control: no-store, no-cache, must-revalidate");
    echo $retval;
    exit;
}


function admin_toggle()
{
    global $LANG_GF01;

    if (!SEC_hasRights('forum.edit')) {
        die;
    }

    $component = $_POST['component'];
    $field = $_POST['field'];
    $oldval = $_POST['oldval'];
    $newval = $oldval;
    switch($component) {
    case 'badge':
        $newval = \Forum\Badge::Toggle($_POST['id'], $field, $oldval);
        break;
    }

    $response = array(
        'newval' => $newval,
        'id'    => $_POST['id'],
        'field'  => $_POST['field'],
        'component' => $_POST['component'],
        'statusMessage' => $newval != $oldval ?
                    $LANG_GF01['msg_item_updated'] :
                    $LANG_GF01['msg_item_nochange'],
    );
    echo json_encode($response);
    exit;
}

?>
