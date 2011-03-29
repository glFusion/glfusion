<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | gf_showtopic.php                                                         |
// |                                                                          |
// | Main functions to show - format topics in the forum                      |
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

function showtopic($showtopic,$mode='',$onetwo=1,$page=1) {
    global $CONF_FORUM,$_CONF,$_TABLES,$_USER,$LANG_GF01,$LANG_GF02,$_SYSTEM;
    global $fromblock,$highlight;
    global $oldPost,$forumfiles;
    global $canPost;

    $retval = '';

    $dt = new Date('now',$_CONF['timezone']);

    static $cacheUserArray = array();

    $oldPost = 0;

    if (!class_exists('StringParser') ) {
        require_once ($_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php');
    }

    $topictemplate = new Template(array($_CONF['path'] . 'plugins/forum/templates/',$_CONF['path'] . 'plugins/forum/templates/links/'));
    $topictemplate->set_file (array (
            'topictemplate' =>  'topic.thtml',
            'profile'       =>  'profile.thtml',
            'pm'            =>  'pm.thtml',
            'email'         =>  'email.thtml',
            'website'       =>  'website.thtml',
            'quote'         =>  'quotetopic.thtml',
            'edit'          =>  'edittopic.thtml'));
    // if preview, only stripslashes is gpc=on, else assume from db so strip
    if ( $mode == 'preview' ) {
        $topictemplate->set_var('show_topicrow1','none');
        $topictemplate->set_var('show_topicrule','none');
        $topictemplate->set_var('lang_postpreview',$LANG_GF01['PREVIEW_HEADER']);
    } else {
        $topictemplate->set_var('show_topicrow2','none');
    }

    $min_height = 50;     // Base minimum  height of topic - will increase if avatar or sig is used
    $dt->setTimestamp($showtopic['date']);
    $date = $dt->format($CONF_FORUM['default_Topic_Datetime_format'],true);

    $foundUser = 0;
    if ( $showtopic['uid'] > 1 ) {
        if ( isset($cacheUserArray[$showtopic['uid']]) ) {
            $userarray = $cacheUserArray[$showtopic['uid']];
            $username = $userarray['display_name'];
            $location = $userarray['location'];
            $posts = $userarray['posts'];
            $user_level = $userarray['user_level'];
            $user_levelname = $userarray['user_levelname'];
            $avatar = $userarray['avatar'];
            $onlinestatus = $userarray['onlinestatus'];
            $min_height = $userarray['min_height'];
            $regdate = $userarray['regdate'];
            $numposts = $userarray['numposts'];
            $foundUser = 1;
        } else {
            $sql = "SELECT users.*,userprefs.*,userinfo.*,gf_userinfo.rating,gf_userinfo.signature FROM {$_TABLES['users']} users LEFT JOIN {$_TABLES['userprefs']} userprefs ON users.uid=userprefs.uid LEFT JOIN {$_TABLES['userinfo']} userinfo ON users.uid=userinfo.uid LEFT JOIN {$_TABLES['gf_userinfo']} gf_userinfo ON users.uid=gf_userinfo.uid WHERE users.uid=".intval($showtopic['uid']);
            $userQuery = DB_query($sql);
            if ( DB_numRows($userQuery) == 1 ) {
                $userarray = DB_fetchArray($userQuery);
                $username = COM_getDisplayName($showtopic['uid']);
                $userarray['display_name'] = $username;

                $postcount = DB_query("SELECT * FROM {$_TABLES['gf_topic']} WHERE uid='".intval($showtopic['uid'])."'");
                $posts = DB_numRows($postcount);
                $userarray['posts'] = $posts;

                $starimage = "<img src=\"%s\" alt=\"{$LANG_GF01['FORUM']} %s\" title=\"{$LANG_GF01['FORUM']} %s\"/>";

                if ($posts < $CONF_FORUM['level2']) {
                    $user_level = sprintf($starimage, gf_getImage('rank1','ranks'), $CONF_FORUM['level1name'],$CONF_FORUM['level1name']);
                    $user_levelname = $CONF_FORUM['level1name'];
                } elseif (($posts >= $CONF_FORUM['level2']) && ($posts < $CONF_FORUM['level3'])){
                    $user_level = sprintf($starimage,gf_getImage('rank2','ranks'),$CONF_FORUM['level2name'],$CONF_FORUM['level2name']);
                    $user_levelname = $CONF_FORUM['level2name'];
                } elseif (($posts >= $CONF_FORUM['level3']) && ($posts < $CONF_FORUM['level4'])){
                    $user_level = sprintf($starimage,gf_getImage('rank3','ranks'),$CONF_FORUM['level3name'],$CONF_FORUM['level3name']);
                    $user_levelname = $CONF_FORUM['level3name'];
                } elseif (($posts >= $CONF_FORUM['level4']) && ($posts < $CONF_FORUM['level5'])){
                    $user_level = sprintf($starimage,gf_getImage('rank4','ranks'),$CONF_FORUM['level4name'],$CONF_FORUM['level4name']);
                    $user_levelname = $CONF_FORUM['level4name'];
                } elseif (($posts > $CONF_FORUM['level5'])){
                    $user_level = sprintf($starimage,gf_getImage('rank5','ranks'),$CONF_FORUM['level5name'],$CONF_FORUM['level5name']);
                    $user_levelname = $CONF_FORUM['level5name'];
                }
                if (forum_modPermission($showtopic['forum'],$showtopic['uid'])) {
                    $user_level = sprintf($starimage,gf_getImage('rank_mod','ranks'),$LANG_GF01['moderator'],$LANG_GF01['moderator']);
                    $user_levelname=$LANG_GF01['moderator'];
                }
                if (SEC_inGroup(1,$showtopic['uid'])) {
                    $user_level = sprintf($starimage,gf_getImage('rank_admin','ranks'),$LANG_GF01['admin'],$LANG_GF01['admin']);
                    $user_levelname=$LANG_GF01['admin'];
                }
                $userarray['user_level'] = $user_level;
                $userarray['user_levelname'] = $user_levelname;

                if ($userarray['photo'] != "") {
                    $avatar = '<img src="' . USER_getPhoto($showtopic['uid'],'','','','0') . '" alt="" title="" class="forum-userphoto" style="width:' . $CONF_FORUM['avatar_width'] . 'px;"/>';
                    $min_height = $min_height + 150;
                } else {
                    $avatar = '';
                }
                if ( $CONF_FORUM['enable_user_rating_system']) {
                    if ( $showtopic['uid'] > 1 ) {
                        $min_height = $min_height + 10;
                    }
                }
                if ( SEC_inGroup('Root') && function_exists('plugin_cclabel_nettools') && isset($showtopic['ip']) ) {
                    $min_height = $min_height + 5;
                }
                $dt->setTimestamp(strtotime($userarray['regdate']));

                $regdate = $LANG_GF01['REGISTERED']. ': ' . $dt->format('m/d/y',true) . '<br/>';
                $numposts = $LANG_GF01['POSTS']. ': ' .$posts;
                if (DB_count( $_TABLES['sessions'], 'uid', intval($showtopic['uid'])) > 0 AND DB_getItem($_TABLES['userprefs'],'showonline',"uid=".intval($showtopic['uid'])."") == 1) {
                    $avatar .= '<br/>' .$LANG_GF01['STATUS']. ' ' .$LANG_GF01['ONLINE'];
                    $onlinestatus = $LANG_GF01['ONLINE'];
                } else {
                    $avatar .= '<br/>' .$LANG_GF01['STATUS']. ' ' .$LANG_GF01['OFFLINE'];
                    $onlinestatus = $LANG_GF01['OFFLINE'];
                }
                $userarray['avatar'] = $avatar;
                $userarray['onlinestatus'] = $onlinestatus;
                $userarray['min_height'] = $min_height;
                $userarray['regdate']    = $regdate;
                $userarray['numposts']   = $numposts;
                $location = $userarray['location'];
                $cacheUserArray[$showtopic['uid']] = $userarray;
                $foundUser = 1;
            }
        }
    }
    if ($foundUser) {
        $userlink = "<a href=\"{$_CONF['site_url']}/users.php?mode=profile&amp;uid={$showtopic['uid']}\" ";
        $userlink .= "class=\"authorname {$onetwo}\" rel=\"nofollow\"><b>{$username}</b></a>";
        $uservalid = true;

        if($userarray['sig'] != '' || $userarray['signature'] != '' ) {
            $sig = '<hr style="width:95%;color=:black;text-align:left;margin-left:0; margin-bottom:5;padding:0" noshade="noshade"/>';

            $usersig = GF_getSignature( $userarray['sig'],$userarray['signature'], 'html' );
            $sig .= $usersig;
            $min_height = $min_height + 30;
        }

    } else {
        $uservalid = false;
        $userlink = '<b>' .$showtopic['name']. '</b>';
        $userlink = '<font size="-2">' .$LANG_GF01['ANON']. '</font>' .$showtopic['name'];
    }

    if ($CONF_FORUM['show_moods'] &&  $showtopic['mood'] != "") {
        $moodimage = '<img align="middle" src="'.gf_getImage($showtopic['mood'],'moods') .'" title="'.$showtopic['mood'].'" alt=""/><br/>';
        $min_height = $min_height + 30;
    }
    // Handle Pre ver 2.5 quoting and New Line Formatting - consider adding this to a migrate function
    if ($CONF_FORUM['pre2.5_mode']) {
        // try to determine if we have an old post...
        if (strstr($showtopic['comment'],'<pre class="forumCode">') !== false)  $oldPost = 1;
        if (strstr($showtopic['comment'],"[code]<code>") !== false) $oldPost = 1;
        if (strstr($showtopic['comment'],"<pre>") !== false ) $oldPost = 1;

        if ( stristr($showtopic['comment'],'[code') == false || stristr($showtopic['comment'],'[code]<code>') == true) {
            if (strstr($showtopic['comment'],"<pre>") !== false)  $oldPost = 1;
            $showtopic['comment'] = str_replace('<pre>','[code]',$showtopic['comment']);
            $showtopic['comment'] = str_replace('</pre>','[/code]',$showtopic['comment']);
        }
        $showtopic['comment'] = str_ireplace("[code]<code>",'[code]',$showtopic['comment']);
        $showtopic['comment'] = str_ireplace("</code>[/code]",'[/code]',$showtopic['comment']);
        $showtopic['comment'] = str_replace(array("<br/>\r\n","<br/>\n\r","<br/>\r","<br/>\n"), '<br/>', $showtopic['comment'] );
        $showtopic['comment'] = preg_replace("/\[QUOTE\sBY=\s(.+?)\]/i","[QUOTE] Quote by $1:",$showtopic['comment']);
        /* Reformat code blocks - version 2.3.3 and prior */
        $showtopic['comment'] = str_replace( '<pre class="forumCode">', '[code]', $showtopic['comment'] );
        $showtopic['comment'] = preg_replace("/\[QUOTE\sBY=(.+?)\]/i","[QUOTE] Quote by $1:",$showtopic['comment']);

        if ( $oldPost ) {
            if ( strstr($showtopic['comment'],"\\'") !== false ) {
                $showtopic['comment'] = stripslashes($showtopic['comment']);
            }
        }
    }
    // Check and see if there are now no [file] bbcode tags in content and reset the show_inline value
    // This is needed in case user had used the file bbcode tag and then removed it
    if ($mode == 'preview' AND strpos($showtopic['comment'],'[file]') === false) {
        $usql = "UPDATE {$_TABLES['gf_attachments']} SET show_inline = 0 ";
        if (isset($_POST['uniqueid']) AND $_POST['uniqueid'] > 0) {  // User is previewing a new post
            $usql .= "WHERE topic_id = ".intval($_POST['uniqueid'])." AND tempfile=1 ";
        } else if(isset($showtopic['id'])) {
             $usql .= "WHERE topic_id = ".intval($showtopic['id'])." ";
        }
        DB_query($usql);
    }

    $showtopic['comment'] = gf_formatTextBlock($showtopic['comment'],$showtopic['postmode'],$mode,$showtopic['status']);
    $showtopic['subject'] = gf_formatTextBlock($showtopic['subject'],'text',$mode,$showtopic['status']);

    $showtopic['subject'] = COM_truncate($showtopic['subject'],$CONF_FORUM['show_subject_length'],'...');

    if ($mode != 'preview' && $uservalid && (!COM_isAnonUser()) && (isset($_USER['uid']) && $_USER['uid'] == $showtopic['uid'])) {
        /* Check if user can still edit this post - within allowed edit timeframe */
        $editAllowed = false;
        if ($CONF_FORUM['allowed_editwindow'] > 0) {
            $t1 = $showtopic['date'];
            $t2 = $CONF_FORUM['allowed_editwindow'];
            if ((time() - $t2) < $t1) {
                $editAllowed = true;
            }
        } else {
            $editAllowed = true;
        }
        if ($editAllowed) {
            $editlink = "{$_CONF['site_url']}/forum/createtopic.php?method=edit&amp;forum={$showtopic['forum']}&amp;id={$showtopic['id']}&amp;editid={$showtopic['id']}&amp;page=$page";
            $editlinkimg = '<img src="'.gf_getImage('edit_button').'" border="0" align="middle" alt="'.$LANG_GF01['EDITICON'].'" title="'.$LANG_GF01['EDITICON'].'"/>';
            $topictemplate->set_var ('editlink', $editlink);
            $topictemplate->set_var ('editlinkimg', $editlinkimg);
            $topictemplate->set_var ('LANG_edit', $LANG_GF01['EDITICON']);
            $topictemplate->parse ('edittopic_link', 'edit');
        }
    }

    if($highlight != '') {
        $showtopic['subject'] = str_replace("$highlight","<span class=\"b\">$highlight</span>", $showtopic['subject']);
        $showtopic['comment'] = str_replace("$highlight","<span class=\"b\">$highlight</span>", $showtopic['comment']);
    }

    if ($showtopic['pid'] == 0) {
        $replytopicid = $showtopic['id'];
        $is_lockedtopic = $showtopic['locked'];
        $views = $showtopic['views'];
        $topictemplate->set_var ('read_msg', sprintf($LANG_GF02['msg49'],$views) );
        if ($is_lockedtopic) {
            $topictemplate->set_var('locked_icon','<img src="'.gf_getImage('padlock').'" title="'.$LANG_GF02['msg114'].'" alt=""/>');
        }
    } else {
        $replytopicid = $showtopic['pid'];
        $is_lockedtopic = DB_getItem($_TABLES['gf_topic'],'locked', "id=".intval($showtopic['pid']));
        $topictemplate->set_var ('read_msg','');
    }
    // Bookmark feature
    if (!COM_isAnonUser() ) {
        if (DB_count($_TABLES['gf_bookmarks'],array('uid','topic_id'),array($_USER['uid'],intval($showtopic['id'])))) {
            $topictemplate->set_var('bookmark_icon','<img src="'.gf_getImage('star_on_sm').'" title="'.$LANG_GF02['msg204'].'" alt=""/>');
        } else {
            $topictemplate->set_var('bookmark_icon','<img src="'.gf_getImage('star_off_sm').'" title="'.$LANG_GF02['msg203'].'" alt=""/>');
        }
    }

    if ($CONF_FORUM['allow_user_dateformat']) {
        $date = COM_getUserDateTimeFormat($showtopic['date']);
        $topictemplate->set_var ('posted_date', $date[0]);
    } else {
        $dt->setTimestamp($showtopic['date']);
        $date = $dt->format($CONF_FORUM['default_Topic_Datetime_format'],true);
        $topictemplate->set_var ('posted_date', $date);
    }

    if ($mode != 'preview') {
        if ($is_lockedtopic == 0) {
            $is_readonly = DB_getItem($_TABLES['gf_forums'],'is_readonly','forum_id=' . intval($showtopic['forum']));
            if ($is_readonly == 0 OR forum_modPermission($showtopic['forum'],(COM_isAnonUser() ? 1 : $_USER['uid']),'mod_edit')) {
                if ( $canPost != 0 ) {
                    $quotelink = "{$_CONF['site_url']}/forum/createtopic.php?method=postreply&amp;forum={$showtopic['forum']}&amp;id=$replytopicid&amp;quoteid={$showtopic['id']}";
                    $quotelinkimg = '<img src="'.gf_getImage('quote_button').'" border="0" align="middle" alt="'.$LANG_GF01['QUOTEICON'].'" title="'.$LANG_GF01['QUOTEICON'].'"/>';
                    $topictemplate->set_var ('quotelink', $quotelink);
                    $topictemplate->set_var ('quotelinkimg', $quotelinkimg);
                    $topictemplate->set_var ('LANG_quote', $LANG_GF01['QUOTEICON']);
                    $topictemplate->parse ('quotetopic_link', 'quote');
                }
            }
        }

        $topictemplate->set_var ('topic_post_link_begin', '<a name="'.$showtopic['id'].'">');
        $topictemplate->set_var ('topic_post_link_end', '</a>');

        $mod_functions = forum_getmodFunctions($showtopic);
        if($showtopic['uid'] > 1 && $uservalid) {
            $profile_link = "{$_CONF['site_url']}/users.php?mode=profile&amp;uid={$showtopic['uid']}";
            $profile_linkimg = '<img src="'.gf_getImage('profile_button').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF01['ProfileLink'].'" title="'.$LANG_GF01['ProfileLink'].'"/>';
            $topictemplate->set_var ('profilelink', $profile_link);
            $topictemplate->set_var ('profilelinkimg', $profile_linkimg);
            $topictemplate->set_var ('LANG_profile',$LANG_GF01['ProfileLink']);
            $topictemplate->parse ('profile_link', 'profile');
            if ($CONF_FORUM['use_pm_plugin'] && (isset($_USER['uid']) && $_USER['uid'] != $showtopic['uid']) ) {
                $pmplugin_link = forumPLG_getPMlink($showtopic['uid']);
                if ($pmplugin_link != '') {
                    $pm_link = $pmplugin_link;
                    $pm_linkimg = '<img src="'.gf_getImage('pm_button').'" border="0" align="middle" alt="'.$LANG_GF01['PMLink'].'" title="'.$LANG_GF01['PMLink'].'"/>';
                    $topictemplate->set_var ('pmlink', $pm_link);
                    $topictemplate->set_var ('pmlinkimg', $pm_linkimg);
                    $topictemplate->set_var ('LANG_pm', $LANG_GF01['PMLink']);
                    $topictemplate->parse ('pm_link', 'pm');
                }
            }
        }

        if(isset($userarray['email']) && $userarray['email'] != '' && $showtopic["uid"] > 1 && $userarray['emailfromuser'] == 1) {
            $email_link = "{$_CONF['site_url']}/profiles.php?uid={$showtopic['uid']}";
            $email_linkimg = '<img src="'.gf_getImage('email_button').'" border="0" align="middle" alt="'.$LANG_GF01['EmailLink'].'" title="'.$LANG_GF01['EmailLink'].'"/>';
            $topictemplate->set_var ('emaillink', $email_link);
            $topictemplate->set_var ('emaillinkimg', $email_linkimg);
            $topictemplate->set_var ('LANG_email', $LANG_GF01['EmailLink']);
            $topictemplate->parse ('email_link', 'email');
        }
        if(isset($userarray['homepage']) && $userarray['homepage'] != '') {
            $homepage = $userarray['homepage'];
            if (!preg_match("/http/i",$homepage) ) {
                $homepage = 'http://' .$homepage;
            }
            $homepageimg = '<img src="'.gf_getImage('website_button').'" border="0" align="middle" alt="'.$LANG_GF01['WebsiteLink'].'" title="'.$LANG_GF01['WebsiteLink'].'"/>';
            $topictemplate->set_var ('websitelink', $homepage);
            $topictemplate->set_var ('websitelinkimg', $homepageimg);
            $topictemplate->set_var ('LANG_website', $LANG_GF01['WebsiteLink']);
            $topictemplate->parse ('website_link', 'website');
        }

        if ($fromblock != "") {
            $back2 = $LANG_GF01['back2parent'];
        } else {
            $back2 = $LANG_GF01['back2top'];
        }
        $backlink = '<center><a href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $replytopicid. '">' .$back2. '</a></center>';
    } else {
        if (!isset($_GET['onlytopic']) || $_GET['onlytopic'] != 1) {
            $topictemplate->set_var ('posted_date', '');
            $topictemplate->set_var ('preview_topic_subject', $showtopic['subject']);
        } else {
            $topictemplate->set_var ('preview_topic_subject', '');
        }
        $topictemplate->set_var ('read_msg', '');
        $topictemplate->set_var ('locked_icon', '');

        $topictemplate->set_var ('preview_mode', 'none');
        // Check and see if there are no [file] bbcode tags in content and reset the show_inline value
        // This is needed in case user had used the file bbcode tag and then removed it
        $imagerecs = '';
        if (is_array($forumfiles)) $imagerecs = implode(',',$forumfiles);
        if (!empty($_POST['uniqueid'])) {
            $sql = "UPDATE {$_TABLES['gf_attachments']} SET show_inline = 0 WHERE topic_id=".intval($_POST['uniqueid'])." ";
            if ($imagerecs != '') $sql .= "AND id NOT IN ($imagerecs)";
            DB_query($sql);
        } else if (isset($_POST['id'])) {
            $sql = "UPDATE {$_TABLES['gf_attachments']} SET show_inline = 0 WHERE topic_id=".intval($_POST['id'])." ";
            if ($imagerecs != '') $sql .= "AND id NOT IN ($imagerecs)";
            DB_query($sql);
        }
    }

    $uniqueid = isset($_POST['uniqueid']) ? COM_applyFilter($_POST['uniqueid'],true) : 0;
    if ($showtopic['id'] > 0 && (!isset($_POST['method']) || $_POST['method'] != 'postreply' )) {
        $topictemplate->set_var('attachments',gf_showattachments($showtopic['id']));
    } elseif ($uniqueid > 0) {
        $topictemplate->set_var('attachments',gf_showattachments($uniqueid));
    }

    if ( SEC_inGroup('Root') && function_exists('plugin_cclabel_nettools') && isset($showtopic['ip']) ) {
        $iplink = '<a href="' . $_CONF['site_url'] . '/nettools/whois.php?domain=' . $showtopic['ip'] . '">' . $showtopic['ip'] . '</a>';
        $topictemplate->set_var('ipaddress',$iplink);
    } else {
        $topictemplate->set_var('ipaddress','');
    }

    $voteHTML = '';
    if ( $CONF_FORUM['enable_user_rating_system']) {
        if ( $showtopic['uid'] > 1 ) { //not an anonymous poster
            // grab the poster's current rating...
    	    $rating = intval(DB_getItem($_TABLES['gf_userinfo'],'rating','uid='.intval($showtopic['uid'])));
    		if ($rating > 0) {
    			$grade = '+'. $rating;
    		} else {
    			$grade = $rating;
    		}

    		//Find out if user has rights to increase / decrease score
    		if ( $_USER['uid'] > 1 && $_USER['uid'] != $showtopic['uid'] ) { //Can't vote for yourself & must be logged in
    			$user_already_voted_res = DB_query("SELECT grade FROM {$_TABLES['gf_rating_assoc']} WHERE user_id = {$showtopic['uid']} AND voter_id = {$_USER['uid']}");
    			if (DB_numRows($user_already_voted_res) <= 0 ) {
    			// user has never voted for this poster
    			    $vote_language = $LANG_GF01['grade_user'];
    			    $plus_vote  = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$showtopic['uid'].','.$showtopic['id'].',1,1);return false;"><img src="'.$_CONF['site_url'].'/forum/images/plus.png" alt="plus" /></a>';
                    $minus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$showtopic['uid'].','.$showtopic['id'].',-1,1);return false;"><img src="'.$_CONF['site_url'].'/forum/images/minus.png" alt="minus" /></a>';
                    $min_height = $min_height + 10;
                } else {
                    // user has already voted for this poster
                    $vote_language = $LANG_GF01['retract_grade'];
    				$user_already_voted_row = DB_fetchArray($user_already_voted_res);
                    if ($user_already_voted_row['grade'] > 0 ) {
                        // gave a +1 show the minus to retract
                        $plus_vote = '';
                        $minus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$showtopic['uid'].','.$showtopic['id'].',-1,0);return false;"><img src="'.$_CONF['site_url'].'/forum/images/minus.png" alt="minus" /></a>';
                        $min_height = $min_height + 10;
    				} else {
                        // gave a -1 show the plus to retract
                        $minus_vote = '';
                        $plus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$showtopic['uid'].','.$showtopic['id'].',1,0);return false;"><img src="'.$_CONF['site_url'].'/forum/images/plus.png" alt="plus" /></a>';
                        $min_height = $min_height + 10;
    				}
    			}
    			$voteHTML = '<div class="c'.$showtopic['uid'].'"><span id="vote'.$showtopic['id'].'">'.$vote_language.'<br />'.$minus_vote.$plus_vote.'<br />'.$LANG_GF01['grade'].': '.$grade.'</span></div>';
            } else {
                // display 'rating'
      			$voteHTML =  $LANG_GF01['grade'].': '.$grade;
            }
        }
    }
    $topictemplate->set_var ('vote_html', $voteHTML);

    $topictemplate->set_var ('layout_url', $_CONF['layout_url']);
    $topictemplate->set_var ('csscode', $onetwo);
    $topictemplate->set_var ('postmode', $showtopic['postmode']);
    $topictemplate->set_var ('userlink', $userlink);
    $topictemplate->set_var ('lang_forum', $LANG_GF01['FORUM']);
    $topictemplate->set_var ('user_levelname', isset($user_levelname) ? $user_levelname : '');
    $topictemplate->set_var ('user_level', isset($user_level) ? $user_level : '');
    $topictemplate->set_var ('magical_image', isset($moodimage) ? $moodimage : '');
    $topictemplate->set_var ('avatar', isset($avatar) ? $avatar : '');
    $topictemplate->set_var ('onlinestatus',isset($onlinestatus) ? $onlinestatus : '');
    $topictemplate->set_var ('regdate', isset($regdate) ? $regdate : '');
    $topictemplate->set_var ('numposts', isset($numposts) ? $numposts : '');
    $topictemplate->set_var ('location', isset($location) ? wordwrap(COM_truncate($location,100),20,'<br />')  : '');
    $topictemplate->set_var ('site_url', $_CONF['site_url']);
    $topictemplate->set_var ('topic_subject', $showtopic['subject']);
    $topictemplate->set_var ('LANG_ON2', $LANG_GF01['ON2']);
    $topictemplate->set_var ('mod_functions', isset($mod_functions) ? $mod_functions : '');
    $topictemplate->set_var ('topic_comment', $showtopic['comment']);
    $topictemplate->set_var ('comment_minheight', "min-height:{$min_height}px");
    if (isset($sig) && trim($sig) != '') {
        $topictemplate->set_var ('sig', PLG_replaceTags($sig,'forum','signature'));
        $topictemplate->set_var ('show_sig', '');
    } else {
        $topictemplate->set_var ('sig', '');
        $topictemplate->set_var ('show_sig', 'none');
    }
    $topictemplate->set_var ('forumid', $showtopic['forum']);
    $topictemplate->set_var ('topic_id', $showtopic['id']);
    $topictemplate->set_var ('parent_id',$replytopicid);
    $topictemplate->set_var ('back_link', isset($backlink) ? $backlink : '');
    $topictemplate->set_var ('member_badge',forumPLG_getMemberBadge($showtopic['uid']));
    $topictemplate->parse ('output', 'topictemplate');
    $retval .= $topictemplate->finish ($topictemplate->get_var('output'));

    return $retval;
}

function forum_getmodFunctions($showtopic) {
    global $_CONF, $_USER,$_TABLES,$LANG_GF03,$LANG_GF01,$page;

    $retval = '';
    $options = '';
    if ( !isset($_USER['uid']) ) {
        $_USER['uid'] = 1;
    }
    if (forum_modPermission($showtopic['forum'],$_USER['uid'],'mod_edit')) {
        $options .= '<option value="editpost">' .$LANG_GF03['edit'] . '</option>';
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
        $retval .= '<form action="'.$_CONF['site_url'].'/forum/moderation.php" method="post" style="margin:0px;"><div><select name="modfunction">';
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
        $retval .= '&nbsp;&nbsp;<input type="submit" name="submit" value="' .$LANG_GF01['GO'].'!"/>';
        $retval .= '</div></form>';
    }
    return $retval;
}


function gf_chkpostmode($postmode,$postmode_switch) {
    global $_TABLES,$CONF_FORUM;

    if ($postmode == "") {
        if($CONF_FORUM['allow_html']) {
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