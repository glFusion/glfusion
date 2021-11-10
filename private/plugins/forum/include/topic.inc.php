<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | topic.inc.php                                                            |
// |                                                                          |
// | Main functions to show - format topics in the forum                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2020 by the following authors:                        |
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

USES_lib_user();

function FF_showtopic($showtopic, $mode='', $onetwo=1, $page=1, $topictemplate = '',$query='') {
    global $_FF_CONF,$_CONF,$_TABLES,$_USER,$LANG_GF01,$LANG_GF02,$_SYSTEM;
    global $forumfiles;
    global $canPost;

    $retval = '';
    if ( isset($showtopic['date']) ) {
        $dt = new Date($showtopic['date'],$_USER['tzid']);
    } else {
        $dt = new Date('now',$_USER['tzid']);
    }
    if ( isset($showtopic['lastedited']) ) {
        $dt_lu = new Date($showtopic['lastedited'],$_USER['tzid']);
    }

    $mod_perms = array(
        'mod_edit'      => 0,
        'mod_delete'    => 0,
        'mod_move'      => 0,
        'mod_ban'       => 0,
        'mod_lock'      => 0
    );

    static $cacheUserArray = array();
    static $_user_already_voted = array();

    $oldPost = 0;

    if ( $mode == 'preview' ) {
        $topictemplate->set_var(array(
            'lang_postpreview'  => $LANG_GF01['PREVIEW_HEADER'],
            'preview' => true));
    }

    $Poster = \Forum\User::getInstance($showtopic['uid']);

    list($user_level, $user_levelname) = \Forum\Rank::getRank($Poster->posts, $Poster->adminLevel($showtopic['forum']));

    if ($_FF_CONF['show_moods'] &&  $showtopic['mood'] != "") {
        $moodimage = _ff_getImage($showtopic['mood'],'moods');
    } else {
        $moodimage = false;
    }

    $showtopic['comment'] = FF_formatTextBlock($showtopic['comment'],$showtopic['postmode'],$mode,$showtopic['status'],$query);

    $showtopic['subject'] = @htmlspecialchars(strip_tags($showtopic['subject']),ENT_QUOTES,COM_getEncodingt());
    $disp_subject = COM_truncate($showtopic['subject'],$_FF_CONF['show_subject_length'],'...');

    if ($mode != 'preview'  && (!COM_isAnonUser()) && (isset($_USER['uid']) && $_USER['uid'] == $showtopic['uid'])) {
        /* Check if user can still edit this post - within allowed edit timeframe */
        $editAllowed = false;
        if ($_FF_CONF['allowed_editwindow'] > 0) {
            $t1 = $showtopic['date'];
            $t2 = $_FF_CONF['allowed_editwindow'] * 60;
            if ((time() - $t2) < $t1) {
                $editAllowed = true;
            }
        } else {
            $editAllowed = true;
        }
        if ($editAllowed) {
            $editlink = $_CONF['site_url'].'/forum/createtopic.php?mode=edittopic&amp;forum='.$showtopic['forum'].'&amp;id='.$showtopic['id'].'&amp;editid='.$showtopic['id'].'&amp;page='.$page;
            $editlinkimg = '<img src="'._ff_getImage('edit_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['EDITICON'].'" title="'.$LANG_GF01['EDITICON'].'"/>';
            $topictemplate->set_var (array(
                    'editlink'  => $editlink,
                    'editlinkimg'   => $editlinkimg,
                    'LANG_edit' => $LANG_GF01['EDITICON']));
        }
    } else {
        $topictemplate->set_var (array(
                'editlink'  => '',
                'editlinkimg'   => '',
                'LANG_edit' => ''));
    }

//    if ( $query != '' ) {
//        $showtopic['subject'] = COM_highlightQuery($showtopic['subject'],$query);
//    }

    if ($showtopic['pid'] == 0) {
        $replytopicid = $showtopic['id'];
        $is_lockedtopic = $showtopic['locked'];
        $views = $showtopic['views'];
        $topictemplate->set_var ('read_msg', sprintf($LANG_GF02['msg49'],$views) );
        if ($is_lockedtopic) {
            $topictemplate->set_var('locked_icon','<img src="'._ff_getImage('padlock').'" title="'.$LANG_GF02['msg114'].'" alt=""/>');
        }
    } else {
        $is_lockedtopic = $showtopic['locked'];
        $replytopicid = $showtopic['pid'];
        $topictemplate->set_var ('read_msg','');
    }
    if ($_FF_CONF['allow_user_dateformat']) {
        $date = $dt->format($dt->getUserFormat(),true);
    } else {
        $date = $dt->format($_CONF['date'],true);
    }
    $topictemplate->set_var ('posted_date', $date);
    $topictemplate->set_var ('iso8601_date', $dt->toISO8601());

    if ($mode != 'preview') {
        if (!COM_isAnonUser() ) {
            $bmArray = _ff_cacheBookMarks( $_USER['uid'] );
            if (isset($bmArray[$showtopic['id']]) ) {
                $topictemplate->set_var('bookmark_icon','<img src="'._ff_getImage('star_on_sm').'" title="'.$LANG_GF02['msg204'].'" alt=""/>');
                $topictemplate->set_var('bookmarked',true);
            } else {
                $topictemplate->set_var('bookmark_icon','<img src="'._ff_getImage('star_off_sm').'" title="'.$LANG_GF02['msg203'].'" alt=""/>');
                $topictemplate->unset_var('bookmarked');
            }
        }
        $topictemplate->clear_var (array('quotelink','quotelinkimg','LANG_quote'));
        if ($is_lockedtopic == 0) {
            $is_readonly = $showtopic['is_readonly'];
            if ($is_readonly == 0 || forum_modPermission($showtopic['forum'],(COM_isAnonUser() ? 1 : $_USER['uid']),'mod_edit')) {
                if ( $canPost != 0 ) {
                    $quotelink = $_CONF['site_url'].'/forum/createtopic.php?mode=newreply&amp;forum='.$showtopic['forum'].'&amp;id='.$replytopicid.'&amp;quoteid='.$showtopic['id'];
                    $quotelinkimg = '<img src="'._ff_getImage('quote_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['QUOTEICON'].'" title="'.$LANG_GF01['QUOTEICON'].'"/>';
                    $topictemplate->set_var (array(
                                'quotelink' => $quotelink,
                                'quotelinkimg'  => $quotelinkimg,
                                'LANG_quote'    => $LANG_GF01['QUOTEICON']));
                }
            }
        }
        $topictemplate->set_var (array(
                'topic_post_link_begin' => '<a name="'.$showtopic['id'].'">',
                'topic_post_link_end'   => '</a>'));

        $mod_functions = _ff_getmodFunctions($showtopic);
        $mod_perms = Forum\Moderator::getPerms($showtopic['forum']);
        $topictemplate->clear_var(array('profilelink','profilelinkimg','LANG_profile'));
        $topictemplate->clear_var(array('pmlink','pmlinkimg','LANG_pm'));
        if ($Poster->uid > 1) {
            $profile_link = $_CONF['site_url'].'/users.php?mode=profile&amp;uid='.$showtopic['uid'];
            $profile_linkimg = '<img src="'._ff_getImage('profile_button').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF01['ProfileLink'].'" title="'.$LANG_GF01['ProfileLink'].'"/>';
            $topictemplate->set_var (array(
                    'profilelink'   => $profile_link,
                    'profilelinkimg'=> $profile_linkimg,
                    'LANG_profile'  => $LANG_GF01['ProfileLink']));

            if ($_FF_CONF['use_pm_plugin'] && (!COM_isAnonUser() && $_USER['uid'] != $showtopic['uid']) ) {
                $pmplugin_link = forumPLG_getPMlink($showtopic['uid']);
                if ($pmplugin_link != '') {
                    $pm_link = $pmplugin_link;
                    $pm_linkimg = '<img src="'._ff_getImage('pm_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['PMLink'].'" title="'.$LANG_GF01['PMLink'].'"/>';
                    $topictemplate->set_var(array(
                            'pmlink'    => $pm_link,
                            'pmlinkimg' => $pm_linkimg,
                            'LANG_pm'   => $LANG_GF01['PMLink']));
                }
            }
        }
        $topictemplate->clear_var(array('emaillink','emaillinkimg','LANG_email'));
        if (!$Poster->isAnon() && $Poster->email != '' && $Poster->emailfromuser) {
            $email_link = $_CONF['site_url'].'/profiles.php?uid='.$showtopic['uid'];
            $email_linkimg = '<img src="'._ff_getImage('email_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['EmailLink'].'" title="'.$LANG_GF01['EmailLink'].'"/>';
            $topictemplate->set_var(array(
                    'emaillink'     => $email_link,
                    'emaillinkimg'  => $email_linkimg,
                    'LANG_email'    => $LANG_GF01['EmailLink']));
        }
        $topictemplate->clear_var(array('websitelink','websitelinkimg','LANG_website'));

        if ($Poster->homepage != '') {
            //$homepage = trim($userarray['homepage']);
            $homepage = $Poster->homepage;
            if (!preg_match("/http/i",$homepage) ) {
                $homepage = 'http://' .$homepage;
            }
            $homepageimg = '<img src="'._ff_getImage('website_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['WebsiteLink'].'" title="'.$LANG_GF01['WebsiteLink'].'"/>';
            $topictemplate->set_var(array(
                    'websitetarget' => $_CONF['open_ext_url_new_window'] == true ? ' target="_blank" ' : '',
                    'websitelink'   => $homepage,
                    'websitelinkimg'=> $homepageimg,
                    'LANG_website'  => $LANG_GF01['WebsiteLink']));
        }

        $back2 = $LANG_GF01['back2top'];
        $backlink = '<center><a href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $replytopicid. '">' .$back2. '</a></center>';
    } else {
        if (!isset($_GET['onlytopic']) || $_GET['onlytopic'] != 1) {
            $topictemplate->set_var ('preview_topic_subject', $showtopic['subject']);
        } else {
            $topictemplate->set_var ('preview_topic_subject', '');
        }
        $topictemplate->set_var ('read_msg', '');
        $topictemplate->set_var ('locked_icon', '');

        // Check and see if there are no [file] bbcode tags in content and reset the show_inline value
        // This is needed in case user had used the file bbcode tag and then removed it
        $imagerecs = '';
        if (is_array($forumfiles)) {
            $imagerecs = implode(',',$forumfiles);
        }
        if (!empty($_POST['uniqueid'])) {
            $uniqueid = COM_applyFilter($_POST['uniqueid'],true);
            $sql = "UPDATE {$_TABLES['ff_attachments']} SET show_inline = 0 WHERE topic_id=".(int) $uniqueid." ";
            if ($imagerecs != '') $sql .= "AND id NOT IN ($imagerecs)";
            DB_query($sql);
        } else if (isset($_POST['id'])) {
            $tid = COM_applyFilter($_POST['id'],true);
            $sql = "UPDATE {$_TABLES['ff_attachments']} SET show_inline = 0 WHERE topic_id=".(int) $tid ." ";
            if ($imagerecs != '') {
                $sql .= "AND id NOT IN ($imagerecs)";
            }
            DB_query($sql);
        }
    }

    $uniqueid = isset($_POST['uniqueid']) ? COM_applyFilter($_POST['uniqueid'],true) : 0;
    if ($showtopic['id'] > 0 && (!isset($_POST['action']) || $_POST['action'] != 'newreply' )) {
        $topictemplate->set_var('attachments',_ff_showattachments((int) $showtopic['id']));
    } elseif ($uniqueid > 0) {
        $topictemplate->set_var('attachments',_ff_showattachments((int) $uniqueid));
    }
    if ( SEC_inGroup('Root')) {
        if (isset($showtopic['ip']) ) {
            if( !empty( $_CONF['ip_lookup'] )) {
                $iplink = '<a href="'.str_replace( '*', $showtopic['ip'], $_CONF['ip_lookup'] ) . '" target="_blank" rel="noopener noreferrer">'.$showtopic['ip'].'</a>';
                $topictemplate->set_var('ipaddress',$iplink);
            } else {
                $topictemplate->set_var('ipaddress',$showtopic['ip']);
            }
        } else {
            $topictemplate->set_var('ipaddress','');
        }
        if (Forum\Modules\Warning\Warning::featureEnabled()) {
            $warn_level = \Forum\Modules\Warning\Warning::getUserPercent($showtopic['uid']);
            if ($warn_level > 0) {
                $topictemplate->set_var('warn_level', $warn_level);
            } else {
                $topictemplate->clear_var('warn_level');
            }
        }
    }
    $can_voteup = false;
    $can_votedn = false;
    $vote_language = '';

    if ( $_FF_CONF['enable_user_rating_system'] && $Poster->okToVote()) {
        if ($Poster->votes == 0) {
        	// user has never voted for this poster
            $can_voteup = true;
            $can_votedn = true;
    		$vote_language = $LANG_GF01['grade_user'];
        } else {
            // user has already voted for this poster
            $vote_language = $LANG_GF01['retract_grade'];
            if ($Poster->rating > 0) {
                // gave a +1 show the minus to retract
                $can_voteup = false;
                $can_votedn = true;
    		} else {
                // gave a -1 show the plus to retract
                $can_voteup = true;
                $can_votedn = false;
            }
        }
    }

    if ( isset($showtopic['date']) && isset($showtopic['lastedited']) && ($showtopic['date'] != $showtopic['lastedited']  ) && ($showtopic['lastedited'] != '' ) ) {
        $ludate = $dt_lu->format($_CONF['date'],true);
        $topictemplate->set_var('last_edited', $ludate);
    } else {
        $topictemplate->unset_var('last_edited');
    }

    $can_like = false;
    if ($_FF_CONF['enable_likes']) {
        if ($Poster->okToVote()) {
            $can_like = true;
        }
        $post_liked = \Forum\Like::isLikedByUser($showtopic['id']);
        $total_post_likes = \Forum\Like::CountPostLikes($showtopic['id']);
    } else {
        $post_liked = false;
        $total_post_likes = 0;
    }

    $topictemplate->set_var (array(
            'user_name'     => $Poster->UserName($showtopic['name']),
            'csscode'       => $onetwo,
            'postmode'      => $showtopic['postmode'],
            'user_level'    => isset($user_level) ? $user_level : '',
            'moodimage'     => $moodimage,
            'moodtitle'     => $showtopic['mood'],
            'avatar'        => $Poster->avatar,
            'avatar_width'  => $_FF_CONF['avatar_width'],
            'onlinestatus'  => $Poster->onlinestatus,
            'regdate'       => $Poster->regdate,
            'numposts'      => $Poster->isAnon() ? 0 : $Poster->posts,
            'location'      => $Poster->location != '' ? wordwrap(COM_truncate($Poster->location,100),20,'<br />')  : '',
            'topic_subject' => $showtopic['subject'],
            'mod_functions' => isset($mod_functions) ? $mod_functions : '',
            'topic_comment' => $showtopic['comment'],
            'subject'       => $showtopic['subject'],
            'disp_subject'  => $disp_subject,
            'forumid'       => $showtopic['forum'],
            'topic_id'      => $showtopic['id'],
            'parent_id'     => $replytopicid,
            'back_link'     => isset($backlink) ? $backlink : '',
            'member_badge'  => forumPLG_getMemberBadge($showtopic['uid']),
            'topic_uid'     => $showtopic['uid'],
            'current_uid'   => $_USER['uid'],
            'can_voteup'    => $can_voteup,
            'can_votedn'    => $can_votedn,
            'can_vote'      => $can_voteup || $can_votedn,
            'downvote_vis'  => $can_votedn ? '' : 'hidden',
            'upvote_vis'    => $can_voteup ? '' : 'hidden',
            'vote_mode'     => (int)($can_voteup && $can_votedn),
            'vote_lang'     => $vote_language,
            'user_rep'      => $_FF_CONF['enable_user_rating_system'] ? ($Poster->rating ? sprintf('%+d', $Poster->rating) : $Poster->rating) : '',
            'is_online'     => $Poster->isOnline(),
            'is_anon'       => $Poster->isAnon() ? true : false,
            'sig'           => PLG_replaceTags($Poster->tagline, 'forum', 'signature'),
            'likes_enabled' => $_FF_CONF['enable_likes'] ? true : false,
            'can_like'      => $can_like,
            'liked'         => $post_liked,
            'like_tooltip'  => sprintf($LANG_GF01['like_tooltip'], $Poster->username),
            'unlike_tooltip' => sprintf($LANG_GF01['unlike_tooltip'], $Poster->username),
            'like_vis'     => $post_liked ? 'none' : '',
            'unlike_vis'   => $post_liked ? '' : 'none',
            'like_lang_vis' => $Poster->Likes() > 0 ? '' : 'none',
            'total_post_likes' => $total_post_likes,
            'likes_text'    => \Forum\Like::getLikesText($showtopic['id']),
            'liked_times' => sprintf($LANG_GF01['liked_times'], $Poster->Likes(), $Poster->uid),
            'mod_edit'      => $mod_perms['mod_edit'],
            'mod_delete'    => $mod_perms['mod_delete'],
            'mod_move'      => $mod_perms['mod_move'],
            'mod_ban'       => $mod_perms['mod_ban'],
            'mod_lock'      => ($mod_perms['mod_edit'] && $showtopic['pid'] == 0 && $showtopic['locked'] == 0),
            'mod_unlock'    => ($mod_perms['mod_edit'] && $showtopic['pid'] == 0 && $showtopic['locked'] != 0),
            'mod_warn'      => ($mod_perms['mod_edit']),
            'has_mod_perms' => Forum\Moderator::hasPerm($showtopic['forum']),
            'topic_parent_id' => $showtopic['pid'],
    ));

    if ( $replytopicid != 0 && $showtopic['pid'] != 0 ) {
        $check = substr($showtopic['subject'],0,strlen($LANG_GF01['RE']));
        if ( strcasecmp($check,$LANG_GF01['RE']) != 0 ) {
            $topictemplate->set_var('prefix',$LANG_GF01['RE']);
        } else {
            $topictemplate->set_var('prefix','');
        }
    } else {
        $topictemplate->set_var('prefix','');
    }

}

function _ff_getmodFunctions($showtopic)
{
    global $_CONF, $_FF_CONF, $_USER,$_TABLES,$LANG_GF03,$LANG_GF01,$page;

    $retval = '';
    $options = '';
    if ( COM_isAnonUser() ) {
        $_USER['uid'] = 1;
    }
    if (forum_modPermission($showtopic['forum'],$_USER['uid'],'mod_edit')) {
        $options .= '<option value="editpost">' .$LANG_GF03['edit'] . '</option>';
        if ( $showtopic['pid'] == 0 && $showtopic['locked'] == 0) {
            $options .= '<option value="locktopic">' .$LANG_GF03['lock_topic'] . '</option>';
        }
        if ( $showtopic['pid'] == 0 && $showtopic['locked'] != 0) {
            $options .= '<option value="unlocktopic">' .$LANG_GF03['unlock_topic']. '</option>';
        }
    }
    if (forum_modPermission($showtopic['forum'],$_USER['uid'],'mod_delete')) {
        $options .= '<option value="deletepost">' .$LANG_GF03['delete'] . '</option>';
    }
    if (forum_modPermission($showtopic['forum'],$_USER['uid'],'mod_ban')) {
        $options .= '<option value="banip">' .$LANG_GF03['ban'] . '</option>';
    }
    if ($showtopic['pid'] == 0) {
        if (forum_modPermission($showtopic['forum'],$_USER['uid'],'mod_move')) {
            $options .= '<option value="movetopic">' .$LANG_GF03['move'] . '</option>';
            $options .= '<option value="mergetopic">' .$LANG_GF03['merge_topic'] . '</option>';
        }
    } elseif (forum_modPermission($showtopic['forum'],$_USER['uid'],'mod_move')) {
        $options .= '<option value="movetopic">' .$LANG_GF03['split'] . '</option>';
        $options .= '<option value="mergetopic">' .$LANG_GF03['merge_post'] . '</option>';
    }

    if ($options != '') {
        $retval .= '<form class="uk-form" action="'.$_CONF['site_url'].'/forum/moderation.php" method="post" style="margin:0px;"><div><select class="uk-form-small" name="modfunction">';
        $retval .= $options;

        if ($showtopic['pid'] == 0) {
            $msgpid = $showtopic['id'];
            $top = "yes";
        } else {
            $msgpid = $showtopic['pid'];
            $top = "no";
        }
        $retval .= '</select><input type="hidden" name="topic_id" value="' .$showtopic['id']. '"/>';
        $retval .= '<input type="hidden" name="forum_id" value="' .$showtopic['forum']. '"/>';
        $retval .= '<input type="hidden" name="topic_parent_id" value="' .$msgpid. '"/>';
        $retval .= '<input type="hidden" name="top" value="' .$top. '"/>';
        $retval .= '<input type="hidden" name="page" value="' .$page. '"/>';
//        $retval .= '&nbsp;&nbsp;<input type="submit" name="submit" value="' .$LANG_GF01['GO'].'"/>';
        $retval .= '<button class="uk-button uk-button-small" type="submit" name="submit" value="'.$LANG_GF01['GO'].'">'.$LANG_GF01['GO'].'</button>';
        $retval .= '</div></form>';
    }
    return $retval;
}


function _ff_chkpostmode($postmode,$postmode_switch)
{
    global $_TABLES,$_FF_CONF;

    if ($postmode == "") {
        if ($_FF_CONF['allow_html']) {
            $postmode = 'html';
        } else {
            $postmode = 'text';
        }
    } else {
        if ($postmode_switch) {
            if ($postmode == 'html') {
                $postmode = 'text';
            } else {
                $postmode = 'html';
            }
            $postmode_switch = 0;
        }
    }
    return $postmode;
}

?>
