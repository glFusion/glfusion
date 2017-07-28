<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | topic.inc.php                                                            |
// |                                                                          |
// | Main functions to show - format topics in the forum                      |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

USES_lib_user();

function FF_showtopic($showtopic, $mode='', $onetwo=1, $page=1, $topictemplate,$query='') {
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

    static $cacheUserArray = array();
    static $_user_already_voted = array();

    $oldPost = 0;

    if (!class_exists('StringParser') ) {
        require_once ($_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php');
    }

    if ( $mode == 'preview' ) {
        $topictemplate->set_var(array(
            'lang_postpreview'  => $LANG_GF01['PREVIEW_HEADER'],
            'preview' => true));
    }

    $min_height = 50;     // Base minimum  height of topic - will increase if avatar or sig is used

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
            $sql = "SELECT users.*,userprefs.*,userinfo.*,gf_userinfo.rating,gf_userinfo.signature FROM {$_TABLES['users']} users LEFT JOIN {$_TABLES['userprefs']} userprefs ON users.uid=userprefs.uid LEFT JOIN {$_TABLES['userinfo']} userinfo ON users.uid=userinfo.uid LEFT JOIN {$_TABLES['ff_userinfo']} gf_userinfo ON users.uid=gf_userinfo.uid WHERE users.uid=".(int) $showtopic['uid'];
            $userQuery = DB_query($sql);
            if ( DB_numRows($userQuery) == 1 ) {
                $userarray = DB_fetchArray($userQuery);
                $username = COM_getDisplayName($showtopic['uid']);
                $userarray['display_name'] = $username;

                $postcount = DB_query("SELECT * FROM {$_TABLES['ff_topic']} WHERE uid='".(int) $showtopic['uid']."'");
                $posts = DB_numRows($postcount);
                $userarray['posts'] = $posts;

                $starimage = '<img src="%s" alt="'.$LANG_GF01['FORUM'].' %s" title="'.$LANG_GF01['FORUM'].' %s"/>';

                if ($posts < $_FF_CONF['level2']) {
                    $user_level = sprintf($starimage, _ff_getImage('rank1','ranks'), $_FF_CONF['level1name'],$_FF_CONF['level1name']);
                    $user_levelname = $_FF_CONF['level1name'];
                } elseif (($posts >= $_FF_CONF['level2']) && ($posts < $_FF_CONF['level3'])){
                    $user_level = sprintf($starimage,_ff_getImage('rank2','ranks'),$_FF_CONF['level2name'],$_FF_CONF['level2name']);
                    $user_levelname = $_FF_CONF['level2name'];
                } elseif (($posts >= $_FF_CONF['level3']) && ($posts < $_FF_CONF['level4'])){
                    $user_level = sprintf($starimage,_ff_getImage('rank3','ranks'),$_FF_CONF['level3name'],$_FF_CONF['level3name']);
                    $user_levelname = $_FF_CONF['level3name'];
                } elseif (($posts >= $_FF_CONF['level4']) && ($posts < $_FF_CONF['level5'])){
                    $user_level = sprintf($starimage,_ff_getImage('rank4','ranks'),$_FF_CONF['level4name'],$_FF_CONF['level4name']);
                    $user_levelname = $_FF_CONF['level4name'];
                } elseif (($posts > $_FF_CONF['level5'])){
                    $user_level = sprintf($starimage,_ff_getImage('rank5','ranks'),$_FF_CONF['level5name'],$_FF_CONF['level5name']);
                    $user_levelname = $_FF_CONF['level5name'];
                }
                if (forum_modPermission($showtopic['forum'],$showtopic['uid'])) {
                    $user_level = sprintf($starimage,_ff_getImage('rank_mod','ranks'),$LANG_GF01['moderator'],$LANG_GF01['moderator']);
                    $user_levelname=$LANG_GF01['moderator'];
                }
                if (SEC_inGroup(1,$showtopic['uid'])) {
                    $user_level = sprintf($starimage,_ff_getImage('rank_admin','ranks'),$LANG_GF01['admin'],$LANG_GF01['admin']);
                    $user_levelname=$LANG_GF01['admin'];
                }
                $userarray['user_level'] = $user_level;
                $userarray['user_levelname'] = $user_levelname;

                if ($userarray['photo'] != "") {
                    $avatar = '<img src="' . USER_getPhoto($showtopic['uid'],'','','','0') . '" alt="" title="" class="forum-userphoto" style="width:' . $_FF_CONF['avatar_width'] . 'px;"/>';
                    $min_height = $min_height + 150;
                } else {
                    if ( !isset($_CONF['default_photo']) || $_CONF['default_photo'] == '' ) {
                        $img = $_CONF['site_url'] . '/images/userphotos/default.jpg';
                    } else {
                        $img = $_CONF['default_photo'];
                    }
                    $avatar = '<img src="' . $img . '" alt="" title="" class="forum-userphoto" style="width:' . $_FF_CONF['avatar_width'] . 'px;"/>';
                    $min_height = $min_height + 150;
                }
                if ( $_FF_CONF['enable_user_rating_system']) {
                    if ( $showtopic['uid'] > 1 ) {
                        $min_height = $min_height + 10;
                    }
                }
                if ( SEC_inGroup('Root') && isset($showtopic['ip']) ) {
                    $min_height = $min_height + 5;
                }
                $udt = new Date(strtotime($userarray['regdate']),$_USER['tzid']);
                $regdate = $udt->format($_CONF['shortdate'],true) . '<br/>';
                $numposts = $posts;
                if ( DB_count( $_TABLES['sessions'], 'uid', (int) $showtopic['uid']) > 0 AND DB_getItem($_TABLES['userprefs'],'showonline',"uid=".(int) $showtopic['uid']."") == 1) {
                    $onlinestatus = $LANG_GF01['ONLINE'];
                } else {
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
    } else {
        if ( !isset($_CONF['default_photo']) || $_CONF['default_photo'] == '' ) {
            $img = $_CONF['site_url'] . '/images/userphotos/default.jpg';
        } else {
            $img = $_CONF['default_photo'];
        }
        $avatar = '<img src="' . $img . '" alt="" title="" class="forum-userphoto" style="width:' . $_FF_CONF['avatar_width'] . 'px;"/>';
        $min_height = $min_height + 150;
    }

    if ( $foundUser ) {
        $userlink = '<a href="'.$_CONF['site_url'].'/users.php?mode=profile&amp;uid='.$showtopic['uid'].'" ';
        $userlink .= 'class="authorname '.$onetwo.'" rel="nofollow"><strong>'.$username.'</strong></a>';
        $uservalid = true;
        if ( $userarray['sig'] != '' || $userarray['signature'] != '' ) {
            $sig = '';
            $sig .= FF_getSignature( $userarray['sig'],$userarray['signature'], 'html' );
            $min_height = $min_height + 30;
        }
    } else {
        $uservalid = false;
        $userlink = $LANG_GF01['ANON'].$showtopic['name'];
    }

    if ($_FF_CONF['show_moods'] &&  $showtopic['mood'] != "") {
        $moodimage = '<img style="vertical-align:middle;" src="'._ff_getImage($showtopic['mood'],'moods') .'" title="'.$showtopic['mood'].'" alt=""/><br/>';
        $min_height = $min_height + 30;
    }

    $showtopic['comment'] = FF_formatTextBlock($showtopic['comment'],$showtopic['postmode'],$mode,$showtopic['status'],$query);

    $showtopic['subject'] = COM_truncate($showtopic['subject'],$_FF_CONF['show_subject_length'],'...');
    $showtopic['subject'] = @htmlspecialchars(strip_tags($showtopic['subject']),ENT_QUOTES,COM_getEncodingt());

    if ($mode != 'preview' && $uservalid && (!COM_isAnonUser()) && (isset($_USER['uid']) && $_USER['uid'] == $showtopic['uid'])) {
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

    if ( $query != '' ) {
        $showtopic['subject'] = COM_highlightQuery($showtopic['subject'],$query);
    }

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
        $topictemplate->clear_var(array('profilelink','profilelinkimg','LANG_profile'));
        $topictemplate->clear_var(array('pmlink','pmlinkimg','LANG_pm'));
        if ( $showtopic['uid'] > 1 && $uservalid ) {
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
        if (isset($userarray['email']) && $userarray['email'] != '' && $showtopic["uid"] > 1 && $userarray['emailfromuser'] == 1) {
            $email_link = $_CONF['site_url'].'/profiles.php?uid='.$showtopic['uid'];
            $email_linkimg = '<img src="'._ff_getImage('email_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['EmailLink'].'" title="'.$LANG_GF01['EmailLink'].'"/>';
            $topictemplate->set_var(array(
                    'emaillink'     => $email_link,
                    'emaillinkimg'  => $email_linkimg,
                    'LANG_email'    => $LANG_GF01['EmailLink']));
        }
        $topictemplate->clear_var(array('websitelink','websitelinkimg','LANG_website'));
        if (isset($userarray['homepage']) && $userarray['homepage'] != '') {
            $homepage = trim($userarray['homepage']);
            if (!preg_match("/http/i",$homepage) ) {
                $homepage = 'http://' .$homepage;
            }
            $homepageimg = '<img src="'._ff_getImage('website_button').'" style="vertical-align:middle;" alt="'.$LANG_GF01['WebsiteLink'].'" title="'.$LANG_GF01['WebsiteLink'].'"/>';
            $topictemplate->set_var(array(
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
    if ( SEC_inGroup('Root') && isset($showtopic['ip']) ) {
        if( !empty( $_CONF['ip_lookup'] )) {
            $iplink = '<a href="'.str_replace( '*', $showtopic['ip'], $_CONF['ip_lookup'] ) . '" target="_blank" rel="noopener noreferrer">'.$showtopic['ip'].'</a>';
            $topictemplate->set_var('ipaddress',$iplink);
        } else {
            $topictemplate->set_var('ipaddress',$showtopic['ip']);
        }
    } else {
        $topictemplate->set_var('ipaddress','');
    }
    $voteHTML = '';
    if ( $_FF_CONF['enable_user_rating_system']) {
        if ( $showtopic['uid'] > 1 ) { //not an anonymous poster
            // grab the poster's current rating...
            $rating = _ff_getUserRating($showtopic['uid']);
    		if ($rating > 0) {
    			$grade = '+'. $rating;
    		} else {
    			$grade = $rating;
    		}
    		//Find out if user has rights to increase / decrease score
    		if ( !COM_isAnonUser() && $_USER['uid'] != $showtopic['uid'] ) { //Can't vote for yourself & must be logged in
                if ( !isset($_user_already_voted[$showtopic['uid']] ) ) {
                    $_user_already_voted[$showtopic['uid']] = DB_getItem($_TABLES['ff_rating_assoc'],'grade',"user_id = ".(int) $showtopic['uid'].' AND voter_id = '.(int) $_USER['uid']);
                }
                if ( $_user_already_voted[$showtopic['uid']] == '' ) {
    			// user has never voted for this poster
    			    $vote_language = $LANG_GF01['grade_user'];
    			    $plus_vote  = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$showtopic['uid'].','.$showtopic['id'].',1,1);return false;"><img src="'.$_CONF['site_url'].'/forum/images/plus.png" alt="plus" /></a>';
                    $minus_vote = '<a href="#" onclick="ajax_voteuser('.$_USER['uid'].','.$showtopic['uid'].','.$showtopic['id'].',-1,1);return false;"><img src="'.$_CONF['site_url'].'/forum/images/minus.png" alt="minus" /></a>';
                    $min_height = $min_height + 10;
                } else {
                    // user has already voted for this poster
                    $vote_language = $LANG_GF01['retract_grade'];
                    if ($_user_already_voted[$showtopic['uid']] > 0 ) {
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

    if ( isset($showtopic['date']) && isset($showtopic['lastedited']) && ($showtopic['date'] != $showtopic['lastedited']  ) && ($showtopic['lastedited'] != '' ) ) {
        $ludate = $dt_lu->format($_CONF['date'],true);
        $topictemplate->set_var('last_edited', $ludate);
    } else {
        $topictemplate->unset_var('last_edited');
    }

    $topictemplate->set_var (array(
            'user_name'     => isset($username) ? $username : 'Anonymous',
            'vote_html'     => $voteHTML,
            'csscode'       => $onetwo,
            'postmode'      => $showtopic['postmode'],
            'userlink'      => $userlink,
            'lang_forum'    => $LANG_GF01['FORUM'],
            'user_levelname'=> isset($user_levelname) ? $user_levelname : '',
            'user_level'    => isset($user_level) ? $user_level : '',
            'magical_image' => isset($moodimage) ? $moodimage : '',
            'avatar'        => isset($avatar) ? $avatar : '',
            'onlinestatus'  => isset($onlinestatus) ? $onlinestatus : '',
            'regdate'       => isset($regdate) ? $regdate : '',
            'numposts'      => isset($numposts) ? $numposts : '',
            'location'      => isset($location) ? wordwrap(COM_truncate($location,100),20,'<br />')  : '',
            'topic_subject' => $showtopic['subject'],
            'LANG_ON2'      => $LANG_GF01['ON2'],
            'mod_functions' => isset($mod_functions) ? $mod_functions : '',
            'topic_comment' => $showtopic['comment'],
            'subject'       => $showtopic['subject'],
            'comment_minheight' => "min-height:{$min_height}px",
            'forumid'       => $showtopic['forum'],
            'topic_id'      => $showtopic['id'],
            'parent_id'     => $replytopicid,
            'back_link'     => isset($backlink) ? $backlink : '',
            'member_badge'  => forumPLG_getMemberBadge($showtopic['uid'])
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
    if (isset($sig) && trim($sig) != '') {
        $topictemplate->set_var ('sig', PLG_replaceTags($sig,'forum','signature'));
    } else {
        $topictemplate->set_var ('sig', '');
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
