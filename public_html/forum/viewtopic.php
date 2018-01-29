<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | viewtopic.php                                                            |
// |                                                                          |
// | Display a topic list                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
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

USES_forum_functions();
USES_forum_format();
USES_forum_topic();

$display = '';

$mytimer = new timerobject();
$mytimer->startTimer();

$showtopic = isset($_GET['showtopic']) ? COM_applyFilter($_GET['showtopic'],true) : 0;
$show      = isset($_GET['show'])      ? COM_applyFilter($_GET['show'],true) : 0;
$page      = isset($_GET['page'])      ? COM_applyFilter($_GET['page'],true) : 0;
$mode      = isset($_GET['mode'])      ? COM_applyFilter($_GET['mode']) : '';
$highlight = isset($_GET['query'])     ? COM_applyFilter($_GET['query']) : '';
$topic     = isset($_GET['topic'])     ? COM_applyFilter($_GET['topic'],true) : 0;
$iframe    = isset($_GET['onlytopic']) ? 1 : 0;

// if no showtopic is provided, use the topic instead
if ( $showtopic == 0 ) {
    $showtopic = $topic;
}

if ( $showtopic == 0 ) {
    COM_setMsg( $LANG_GF02['msg172'], 'error' );
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}

$result = DB_query("SELECT forum, pid, subject FROM {$_TABLES['ff_topic']} WHERE id=".(int) $showtopic);
list($forum, $topic_pid, $subject) = DB_fetchArray($result);

if ($topic_pid == '') {
    COM_setMsg( $LANG_GF02['msg172'], 'error' );
    echo COM_refresh($_CONF['site_url'].'/forum/index.php');
    exit;
}
if ($topic_pid != 0) {
    $showtopic = $topic_pid;
}

if (empty($show) AND $FF_userprefs['postsperpage'] > 0) {
    $show = $FF_userprefs['postsperpage'];
} elseif (empty($show)) {
    $show = 20;
}

$sql  = "SELECT a.forum,a.pid,a.locked,a.subject,a.replies,b.forum_cat,b.forum_name,b.is_readonly,b.grp_id,b.rating_post,c.cat_name,c.id ";
$sql .= "FROM {$_TABLES['ff_topic']} a ";
$sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON b.forum_id=a.forum ";
$sql .= "LEFT JOIN {$_TABLES['ff_categories']} c on c.id=b.forum_cat ";
$sql .= "WHERE a.id=".(int) $showtopic;
$viewtopic = DB_fetchArray(DB_query($sql),false);

$canPost = _ff_canPost( $viewtopic );

$replies  = $viewtopic['replies'];
$numpages = ceil(($replies+1) / $show);

if ( $page > $numpages ) {
    $page = $numpages;
}

if ($_FF_CONF['use_censor']) {
    $viewtopic['subject'] = COM_checkWords($viewtopic['subject']);
}

$topicTemplate = new Template($_CONF['path'] . 'plugins/forum/templates/');
$topicTemplate->set_file ('topictemplate','topic_full.thtml');

if ( !$iframe ) {
    $topicTemplate->set_var('full',1);
} else if ( $iframe ) {
    $topicTemplate->set_var('iframe',1);
} else {
    $topicTemplate->set_var('full',1);
}

if ( !$iframe )  {
    $pageTitle = strip_tags(COM_checkWords($subject));
    $canonical = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$showtopic;
    if ( $page > 1 ) {
        $canonical .= '&amp;page='.$page;
    }
    $headercode = '<link rel="canonical" href="' . $canonical . '" />';
    $display .= FF_siteHeader($pageTitle,$headercode);
    $display .= FF_ForumHeader($forum,$showtopic);
} else {
    list($cacheFile,$cacheURL) = COM_getStyleCacheLocation();
    $csslink = $cacheURL;
    $topicTemplate->set_var('csslink',$csslink);
    $outputHandle = outputHandler::getInstance();
    $topicTemplate->set_var(array(
                'meta-header'  => $outputHandle->renderHeader('meta'),
                'css-header'   => $outputHandle->renderHeader('style'),
                'js-header'    => $outputHandle->renderHeader('script'),
                'raw-header'   => $outputHandle->renderHeader('raw'),
                'charset'      => COM_getCharset(),
                'lang_locale'  => $_CONF['iso_lang'],
                'direction'    => (empty($LANG_DIRECTION) ? 'ltr' : $LANG_DIRECTION),
    ));
}

if (isset($_GET['lastpost']) && $_GET['lastpost']) {
    if ( $page == 0 ) {
        $page = $numpages;
    }
    if ( isset($_GET['onlytopic']) && $_GET['onlytopic'] == 1 ) {
        $order = $_FF_CONF['showtopic_review_order'];
        $page = 1;
    } else {
        $order = $FF_userprefs['topic_order']; //'ASC';
    }
    if ( $page > 1 ) {
        $offset = ($page - 1) * $show;
    } else {
        $offset = 0;
    }
    $base_url = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$showtopic.'&amp;mode='.$mode.'&amp;show='.$show;
} else {
    if ( $topic != 0 ) {
        $order = $FF_userprefs['topic_order'];
        $sql = "SELECT id FROM {$_TABLES['ff_topic']} WHERE pid=".(int) $showtopic." ORDER BY date " . $order;
        $idResult = DB_query($sql);
        $ids = array();
        while ( $I = DB_fetchArray($idResult)) {
            $ids[] = $I['id'];
        }
        $key = array_search($topic,$ids);
        $key = $key + 1;
        $page = intval($key / $show) + 1;
    }

    if ( $page == 0 ) {
        $page = 1;
    }
    if ( $page > 1 ) {
        $offset = ($page - 1) * $show;
    } else {
        $offset = 0;
    }

    $base_url = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$showtopic.'&amp;mode='.$mode.'&amp;show='.$show;

    if ( isset($_GET['onlytopic']) && $_GET['onlytopic'] == 1 ) {
        $order = $_FF_CONF['showtopic_review_order'];
    } else {
        $order = $FF_userprefs['topic_order']; // 'ASC';
    }
}

if ( !$iframe ) {
    $topicTemplate->clear_var(array('replytopiclink','replytopiclinkimg','LANG_reply'));
    $printlink = $_CONF['site_url'].'/forum/print.php?id='.$showtopic;
    $printlinkimg = '<img src="'._ff_getImage('print').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF01['PRINTABLE'].'" title="'.$LANG_GF01['PRINTABLE'].'"/>';
    if ( $viewtopic['locked'] == 1 ) {
        $topicTemplate->set_var('locked',true);
        $topicTemplate->set_var('locked_topic_msg',$LANG_GF03['locked_topic_msg']);
    }
    if ( $canPost != 0 ) {
        $newtopiclink = $_CONF['site_url'].'/forum/createtopic.php?mode=newtopic&amp;forum='.$forum;
        $newtopiclinkimg = '<img src="'._ff_getImage('post_newtopic').'" style="border:none;" alt="'.$LANG_GF01['NEWTOPIC'].'" title="'.$LANG_GF01['NEWTOPIC'].'"/>';
        if ( $viewtopic['locked'] != 1 ) {
            $replytopiclink = $_CONF['site_url'].'/forum/createtopic.php?mode=newreply&amp;forum='.$forum.'&amp;id='.$showtopic;
            $replytopiclinkimg = '<img src="'._ff_getImage('post_reply').'" style="border:none;" alt="'.$LANG_GF01['POSTREPLY'].'" title="'.$LANG_GF01['POSTREPLY'].'"/>';
            $topicTemplate->set_var (array(
                'replytopiclink'    => $replytopiclink,
                'replytopiclinkimg' => $replytopiclinkimg,
                'LANG_reply'        => $LANG_GF01['POSTREPLY']
            ));
        }
    } else {
        $newtopiclink = '';
        $newtopiclinkimg = '';
    }
    // Enable subscriptions if member
    if ( !COM_isAnonUser() ) {
        $forumid = $viewtopic['forum'];

        /* Check for a un-subscribe record */
        $ntopicid = -$showtopic;
        if (DB_count($_TABLES['subscriptions'], array('type','category','id', 'uid'), array('forum',(int) $forumid, $ntopicid,(int) $_USER['uid'])) > 0) {
            $notifylinkimg = '<img src="'._ff_getImage('notify_on').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['msg62'].'" title="'.$LANG_GF02['msg62'].'"/>';
            $notifylink = $_CONF['site_url'].'/forum/notify.php?forum='.$forumid.'&amp;submit=save&amp;topic='.$showtopic;
            $topicTemplate->set_var ('LANG_notify', $LANG_GF01['SubscribeLink']);

            $topicTemplate->unset_var ('topic_subscribed');
            $topicTemplate->set_var('suboption','subscribe_topic');
        /* Check if user has subscribed to complete forum */
        } elseif (DB_count($_TABLES['subscriptions'], array('type','category', 'id', 'uid'), array('forum',(int) $forumid, '0',(int) $_USER['uid'])) > 0) {
            $notifyID = DB_getItem($_TABLES['subscriptions'],'sub_id', "type='forum' AND category=".(int)$forumid." AND id=0 AND uid=".(int)$_USER['uid']);
            $notifylinkimg = '<img src="'._ff_getImage('notify_off').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['msg137'].'" title="'.$LANG_GF02['msg137'].'"/>';
            $notifylink = $_CONF['site_url'].'/forum/notify.php?submit=delete2&amp;id='.$notifyID.'&amp;forum='.$forumid.'&amp;topic='.$showtopic;
            $topicTemplate->set_var ('LANG_notify', $LANG_GF01['unSubscribeLink']);

            $topicTemplate->set_var ('topic_subscribed',true);
            $topicTemplate->set_var('suboption','unsubscribe_topic');
            $topicTemplate->set_var('notify_id',$notifyID);
        /* Check if user is subscribed to this specific topic */
        } elseif (DB_count($_TABLES['subscriptions'], array('type','category', 'id', 'uid'), array('forum',(int) $forumid, (int) $showtopic,(int) $_USER['uid'])) > 0) {
            $notifyID = DB_getItem($_TABLES['subscriptions'],'sub_id', "type='forum' AND category=".(int)$forumid." AND id=".(int)$showtopic." AND uid=".(int)$_USER['uid']);
            $notifylinkimg = '<img src="'._ff_getImage('notify_off').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['msg137'].'" title="'.$LANG_GF02['msg137'].'"/>';
            $notifylink = $_CONF['site_url'].'/forum/notify.php?submit=delete2&amp;id='.$notifyID.'&amp;forum='.$forumid.'&amp;topic='.$showtopic;
            $topicTemplate->set_var ('LANG_notify', $LANG_GF01['unSubscribeLink']);
            $topicTemplate->set_var ('topic_subscribed',true);

            $topicTemplate->set_var('suboption','unsubscribe_topic');
            $topicTemplate->set_var('notify_id',$notifyID);
        } else {
            $notifylinkimg = '<img src="'._ff_getImage('notify_on').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['msg62'].'" title="'.$LANG_GF02['msg62'].'"/>';
            $notifylink = $_CONF['site_url'].'/forum/notify.php?forum='.$forumid.'&amp;submit=save&amp;topic='.$showtopic;
            $topicTemplate->set_var ('LANG_notify', $LANG_GF01['SubscribeLink']);
            $topicTemplate->unset_var ('topic_subscribed');
            $topicTemplate->set_var('suboption','subscribe_topic');
        }
        $topicTemplate->set_var (array(
            'notifylinkimg' => $notifylinkimg,
            'notifylink'    => $notifylink,
            'topic_id'      => $showtopic,
            'forum'         => $forumid,
        ));
    }

    $topicTemplate->set_var (array(
        'printlink'     => $printlink,
        'printlinkimg'  => $printlinkimg,
        'LANG_print'    => $LANG_GF01['PRINTABLE'],
        'navbreadcrumbsimg' => '<img src="'._ff_getImage('nav_breadcrumbs').'" style="border:none;vertical-align:middle;" alt=""/>',
        'navtopicimg'   => '<img src="'._ff_getImage('nav_topic').'" style="border:none;vertical-align:middle;" alt=""/>',
        'forum_home'    => $LANG_GF01['INDEXPAGE'],
        'cat_name'      => $viewtopic['cat_name'],
        'cat_id'        => $viewtopic['forum_cat'],
        'forum_id'      => $forum,
        'forum_name'    => $viewtopic['forum_name'],
        'topic_id'      => $showtopic,
        'newtopiclink'  => $newtopiclink,
        'newtopiclinkimg'   => $newtopiclinkimg,
        'LANG_newtopic' => $LANG_GF01['NEWTOPIC'],
        'LANG_next'     => $LANG_GF01['NEXT'],
        'LANG_TOP'      => $LANG_GF01['TOP'],
        'subject'       => $viewtopic['subject'],
        'LANG_HOME'     => $LANG_GF01['HOMEPAGE'],
        'num_posts'     => $replies+1,
        'page'          => $page,
        'num_pages'     => $numpages
    ));
}

if ($_FF_CONF['enable_likes']) {
    \Forum\Like::TopicLikes($showtopic);
}

$sql = "SELECT * FROM {$_TABLES['ff_topic']} WHERE id=".(int) $showtopic." OR pid=".(int) $showtopic." ORDER BY date $order LIMIT $offset, $show";
$result  = DB_query($sql);

// Display each post in this topic
$onetwo = 1;
$cantView = 0;

$topicTemplate->set_block('topictemplate', 'topicrow', 'trow');

while ($topicRec = DB_fetchArray($result) ) {
    if ($FF_userprefs['viewanonposts'] == 0 AND $topicRec['uid'] == 1) {
       $display .= '<div class="uk-alert uk-alert-danger" style="padding:10px;margin:10px;">Your preferences have block anonymous posts enabled</div>';
       break;
	} else if ( !_ff_canUserViewRating($forum) ) {
	    if ( $cantView == 0 ) {
    		$display .= '<div class="uk-alert uk-alert-danger" style="padding:10px;margin:10px;">'.$LANG_GF02['rate_too_low_thread'].'</div>';
    	}
    	$cantView++;
    } else {
        $topicRec['is_readonly'] = $viewtopic['is_readonly'];
        $topicRec['locked'] = $viewtopic['locked'];
        FF_showtopic($topicRec,$mode,$onetwo,$page,$topicTemplate,$highlight);
        $topicTemplate->parse('trow', 'topicrow',true);
        $onetwo = ($onetwo == 1) ? 2 : 1;
    }
}

if (!$iframe) {
    DB_query("UPDATE {$_TABLES['ff_topic']} SET views=views+1 WHERE id=".(int) $showtopic);
//@TODO look at optimizing this better
    if ( !COM_isAnonUser() ) {
        $showtopicpid = $showtopic;
        $forumid      = $viewtopic['forum'];
        $lrows = DB_count($_TABLES['ff_log'],array('uid','topic'),array((int) $_USER['uid'], (int) $showtopic));
        $logtime = time();
        if ($lrows < 1) {
            DB_query("INSERT INTO {$_TABLES['ff_log']} (uid,forum,topic,time) VALUES (".(int) $_USER['uid'].",".(int) $forumid.",". (int) $showtopicpid.",".$logtime.")");
        } else {
            DB_query("UPDATE {$_TABLES['ff_log']} SET time=".$logtime." WHERE uid=".(int) $_USER['uid'] ." AND topic=".(int) $showtopic);
        }
    }
} else {
    $base_url .= '&amp;onlytopic=1';
}

$page_navigation = forum_pagination($base_url,$page,$numpages);

$topicTemplate->set_var(array(
        'pagenavigation'  => $page_navigation, // COM_printPageNavigation($base_url,$page,$numpages),
        'page_generated_time',sprintf($LANG_GF02['msg179'],$mytimer->stopTimer())));

$topicTemplate->parse ('output', 'topictemplate');
$display .= $topicTemplate->finish($topicTemplate->get_var('output'));

if( !$iframe ) {
    $display .= FF_BaseFooter(false);
    $display .= FF_siteFooter();
}
echo $display;
?>
