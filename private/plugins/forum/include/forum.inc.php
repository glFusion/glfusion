<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | forum.inc.php                                                            |
// |                                                                          |
// | Forum functions                                                          |
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

if ( $_FF_CONF['usermenu'] == 'blockmenu' ) {
    $_FF_CONF['leftblocks'] = array ('forum_menu');
    $_FF_CONF['leftblocks'] = FF_GetUserBlocks($_FF_CONF['leftblocks']);
}

function FF_siteFooter() {
    global $_FF_CONF;

    $retval = '';

    if ($_FF_CONF['showblocks'] == 'noblocks' OR $_FF_CONF['showblocks'] == 'leftblocks') {
        $retval .= COM_siteFooter(false);
    } elseif ($_FF_CONF['showblocks'] == 'rightblocks') {
        if ($_FF_CONF['usermenu'] == 'blockmenu') {
            $retval .= COM_siteFooter(true, array('forum_showBlocks',$_FF_CONF['leftblocks']) );
        } else {
            $retval .= COM_siteFooter(true);
        }
    } elseif ($_FF_CONF['showblocks'] == 'allblocks') {
        $retval .= COM_siteFooter(true);
    } else {
        $retval .= COM_siteFooter();
    }
    return $retval;
}

function FF_siteHeader($subject = '',$headercode='') {
    global $_FF_CONF;

    $retval = '';

    // Display Common headers
    if (!isset($_FF_CONF['showblocks'])) $_FF_CONF['showblocks'] = 'leftblocks';
    if (!isset($_FF_CONF['usermenu'])) $_FF_CONF['usermenu'] = 'blockmenu';

    if ($_FF_CONF['showblocks'] == 'noblocks' OR $_FF_CONF['showblocks'] == 'rightblocks') {
        $retval .= COM_siteHeader('none', $subject,$headercode);
    } elseif ($_FF_CONF['showblocks'] == 'leftblocks' OR $_FF_CONF['showblocks'] == 'allblocks' ) {
        if ($_FF_CONF['usermenu'] == 'blockmenu') {
            $retval .= COM_siteHeader( array('forum_showBlocks',$_FF_CONF['leftblocks']), $subject,$headercode );
        } else {
            $retval .= COM_siteHeader('menu', $subject,$headercode);
        }
    } else {
        $retval .= COM_siteHeader('menu', $subject,$headercode);
    }
    return $retval;
}

function FF_NavbarMenu($current='') {
    global $_FF_CONF, $_CONF,$_USER,$LANG_GF01,$LANG_GF02;

    $navmenu = new navbar;
    $navmenu->add_menuitem($LANG_GF01['INDEXPAGE'],"{$_CONF['site_url']}/forum/index.php");
    if ( !COM_isAnonUser() ) {
        $navmenu->add_menuitem($LANG_GF01['SUBSCRIPTIONS'],"{$_CONF['site_url']}/forum/notify.php");
        $navmenu->add_menuitem($LANG_GF01['BOOKMARKS'],"{$_CONF['site_url']}/forum/list.php?op=bookmarks");
        $navmenu->add_menuitem($LANG_GF02['new_posts'],"{$_CONF['site_url']}/forum/list.php?op=newposts");
    }
    if ( $_FF_CONF['allow_memberlist'] && !COM_isAnonUser() ) {
        $navmenu->add_menuitem($LANG_GF02['msg88'],"{$_CONF['site_url']}/forum/memberlist.php");
    }
    $navmenu->add_menuitem($LANG_GF01['LASTX'],"{$_CONF['site_url']}/forum/list.php?op=lastx");
    $navmenu->add_menuitem($LANG_GF02['msg201'],"{$_CONF['site_url']}/forum/list.php?op=popular");
    if ($current != '') {
        $navmenu->set_selected($current);
    }
    return $navmenu->generate();
}

function FF_ForumHeader($forum,$showtopic) {
    global $_TABLES, $_USER, $_CONF, $_FF_CONF, $LANG_GF01, $LANG_GF02;

    $retval = '';
    $navbar = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $navbar->set_file (array ('topicheader'=>'navbar.thtml'));
    $navbar->set_var ('search_forum', f_forumsearch($forum));
    $navbar->set_var ('select_forum', f_forumjump());

    if ($_FF_CONF['usermenu'] == 'navbar') {
        if ($forum == 0) {
            $navbar->set_var('navmenu', FF_NavbarMenu($LANG_GF01['INDEXPAGE']));
        } else {
            $navbar->set_var('navmenu', FF_NavbarMenu());
        }
    } else {
        $navbar->set_var('navmenu','');
    }
    $navbar->parse ('output', 'topicheader');
    $retval .= $navbar->finish($navbar->get_var('output'));

    if (($forum != '') || ($showtopic != '')) {
        if ($showtopic != '') {
            $forum_id = DB_getItem($_TABLES['ff_topic'],'forum',"id=".(int) $showtopic);
            $grp_id = DB_getItem($_TABLES['ff_forums'],'grp_id',"forum_id=".(int) $forum_id);
        } elseif ($forum != "") {
            $grp_id = DB_getItem($_TABLES['ff_forums'],'grp_id',"forum_id=".(int) $forum);
        }
        $groupname = _ff_getGroup($grp_id);
        if (!SEC_inGroup($groupname)) {
            echo COM_404();
            exit;
        }
    }
    return $retval;
}

/* Function _ff_getImage - used to return the image URL for icons
 * The forum uses a number of icons and you may have a need to use a mixture of image types.
 * Enabling the $_FF_CONF['autoimagetype'] feature will invoke a test that will first
 * check for an image of the type set in your themes function.php $_IMAGE_TYPE
 * If the icon of that image type is not found, then it will use an image of type
 * specified by the $_FF_CONF['image_type_override'] setting.

 * Set $_FF_CONF['autoimagetype'] to false in the plugins config.php to disable this feature and
 * only icons of type set by the themes $_IMAGE_TYPE setting will be used
*/
function _ff_getImage($image,$directory='') {
    global $_CONF,$_FF_CONF,$_IMAGE_TYPE;

    $imageMap=array(
        'accent'            =>    'accent.gif',
        'add'               =>    'add.gif',
        'aim'               =>    'aim.gif',
        'alert_warning'     =>    'alert_warning.gif',
        'allread'           =>    'allread.gif',
        'asc'               =>    'asc.gif',
        'asc_on'            =>    'asc_on.gif',
        'board'             =>    'board.gif',
        'bullet'            =>    'bullet.gif',
        'busyforum'         =>    'busyforum.png',
        'busytopic'         =>    'busytopic.png',
        'busytopic_footer'  =>    'busytopic_footer.png',
        'button-left'       =>    'button-left.png',
        'button-middle'     =>    'button-middle.png',
        'button-right'      =>    'button-right.png',
        'button'            =>    'button.gif',
        'button_over'       =>    'button_over.gif',
        'captchasupport'    =>    'captchasupport.jpg',
        'chatrooms'         =>    'chatrooms.gif',
        'delete'            =>    'delete.gif',
        'desc'              =>    'desc.gif',
        'desc_on'           =>    'desc_on.gif',
        'document_sm'       =>    'document_sm.gif',
        'edit'              =>    'edit.png',
        'edit_button'       =>    'edit_button.png',
        'email'             =>    'email.png',
        'email_button'      =>    'email_button.png',
        'end-quote'         =>    'end-quote.gif',
        'folder'            =>    'folder.gif',
        'forum-wrap-b'      =>    'forum-wrap-b.gif',
        'forum-wrap-bl'     =>    'forum-wrap-bl.gif',
        'forum-wrap-br'     =>    'forum-wrap-br.gif',
        'forum-wrap-l'      =>    'forum-wrap-l.gif',
        'forum-wrap-r'      =>    'forum-wrap-r.gif',
        'forum-wrap-t'      =>    'forum-wrap-t.gif',
        'forum-wrap-tl'     =>    'forum-wrap-tl.gif',
        'forum-wrap-tr'     =>    'forum-wrap-tr.gif',
        'forum'             =>    'forum.png',
        'forumindex'        =>    'forumindex.png',
        'forumname'         =>    'forumname.gif',
        'forumnotify_off'   =>    'notify_off.png',
        'forumnotify_on'    =>    'notify_on.png',
        'gl_mootip_bg200'   =>    'gl_mootip_bg200.png',
        'green_dot'         =>    'green_dot.gif',
        'home'              =>    'home.png',
        'home_button'       =>    'home_button.png',
        'icon_last_posts'   =>    'icon_last_posts.gif',
        'icon_minipost'     =>    'icon_minipost.gif',
        'icon_topic_latest' =>    'icon_topic_latest.gif',
        'icon_www'          =>    'icon_www.gif',
        'icq'               =>    'icq.gif',
        'im'                =>    'im.gif',
        'img_quote'         =>    'img_quote.gif',
        'im_inbox'          =>    'im_inbox.gif',
        'im_new'            =>    'im_new.gif',
        'ip'                =>    'ip.gif',
        'lastpost'          =>    'lastpost.gif',
        'latestposts'       =>    'latestposts.png',
        'lb-b'              =>    'lb-b.gif',
        'lb-bl'             =>    'lb-bl.gif',
        'lb-br'             =>    'lb-br.gif',
        'lb-l'              =>    'lb-l.gif',
        'lb-r'              =>    'lb-r.gif',
        'lb-t'              =>    'lb-t.gif',
        'lb-tl'             =>    'lb-tl.gif',
        'lb-tr'             =>    'lb-tr.gif',
        'locked'            =>    'locked.png',
        'locked_new'        =>    'locked_new.gif',
        'locked_new'        =>    'locked_new.png',
        'members'           =>    'members.gif',
        'modify'            =>    'modify.gif',
        'msn'               =>    'msn.gif',
        'msnm'              =>    'msnm.gif',
        'nav_breadcrumbs'   =>    'nav_breadcrumbs.png',
        'nav_down'          =>    'nav_down.gif',
        'nav_topic'         =>    'nav_topic.gif',
        'nav_topic'         =>    'nav_topic.png',
        'nav_up'            =>    'nav_up.gif',
        'new'               =>    'new.gif',
        'newpost'           =>    'newpost.gif',
        'newposts'          =>    'newposts.png',
        'next'              =>    'next.gif',
        'next2'             =>    'next2.gif',
        'nexttopic'         =>    'nexttopic.gif',
        'noposts'           =>    'noposts.png',
        'notifications'     =>    'notifications.gif',
        'notify_off'        =>    'notify_off.gif',
        'notify_on'         =>    'notify_on.gif',
        'padlock'           =>    'padlock.gif',
        'pixel'             =>    'pixel.gif',
        'pm'                =>    'pm.png',
        'pm_button'         =>    'pm_button.png',
        'popular'           =>    'popular.gif',
        'post_newtopic'     =>    'post_newtopic.gif',
        'post_reply'        =>    'post_reply.gif',
        'prev'              =>    'prev.gif',
        'prevtopic'         =>    'prevtopic.gif',
        'print'             =>    'print.gif',
        'printer'           =>    'printer.gif',
        'private'           =>    'private.gif',
        'profile'           =>    'profile.png',
        'profile_button'    =>    'profile_button.png',
        'quietforum'        =>    'quietforum.png',
        'quiettopic_footer' =>    'quiettopic_footer.png',
        'quote'             =>    'quote.png',
        'quote_button'      =>    'quote_button.png',
        'redarrow'          =>    'redarrow.gif',
        'red_dot'           =>    'red_dot.gif',
        'replypost'         =>    'replypost.gif',
        'return'            =>    'return.gif',
        'rss_feed'          =>    'rss_small.png',
        'search'            =>    'search.gif',
        'spacer'            =>    'spacer.gif',
        'start-quote'       =>    'start-quote.gif',
        'star_off_sm'       =>    'star_off_sm.gif',
        'star_on_sm'        =>    'star_on_sm.gif',
        'sticky'            =>    'sticky.png',
        'sticky_new'        =>    'sticky_new.png',
        'top'               =>    'top.gif',
        'topic_markread'    =>    'topic_markread.png',
        'topic_viewnew'     =>    'topic_viewnew.png',
        'viewnew'           =>    'topic_viewnew.png',
        'trash'             =>    'trash.gif',
        'viewallnew'        =>    'viewallnew.gif',
        'view_last_reply'   =>    'view_last_reply.gif',
        'website'           =>    'website.png',
        'website_button'    =>    'website_button.png',
        'yim'               =>    'yim.gif',
        'rank0'             =>    'rank0.gif',
        'rank1'             =>    'rank1.gif',
        'rank2'             =>    'rank2.gif',
        'rank3'             =>    'rank3.gif',
        'rank4'             =>    'rank4.gif',
        'rank5'             =>    'rank5.gif',
        'rank_admin'        =>    'rank_admin.gif',
        'rank_mod'          =>    'rank_mod.gif',
    );

    if ($directory != '')  {
        if ( $directory == 'moods' ) {
            $fullImagePath = $_CONF['site_url'].'/forum/images/'.$directory .'/'.$image.'.gif';
        } else {
            $fullImagePath = $_CONF['site_url'].'/forum/images/'.$directory .'/'.$imageMap[$image];
        }
    } else {
        $fullImagePath = $_CONF['site_url'].'/forum/images/'.$imageMap[$image];
    }
    $fullImageURL = $fullImagePath;
    return $fullImageURL;
}

function FF_BlockMessage($title,$message='',$sitefooter=true)
{
    $retval = '';

    $retval .= COM_startBlock($title);
    $retval .= $message;
    $retval .= COM_endBlock();
    if ($sitefooter) {
        $retval .= FF_siteFooter();
    }
    return $retval;
}

//@TODO change to FF_alertMessage
function _ff_alertMessage($message,$title='',$prompt='') {
    global $_CONF, $_FF_CONF,$LANG_GF02;

    $alertmsg = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $alertmsg->set_file ('alertmsg','alertmsg.thtml');

    $alertmsg->set_var ('alert_title', $title);
    $alertmsg->set_var ('alert_message', $message);
    if ($prompt == '') {
        $alertmsg->set_var ('prompt', $LANG_GF02['msg148']);
    } else {
        $alertmsg->set_var ('prompt', $prompt);
    }
    $alertmsg->parse ('alert_header', 'outline_header');
    $alertmsg->parse ('alert_footer', 'outline_footer');
    $alertmsg->parse ('output', 'alertmsg');
    $retval = $alertmsg->finish ($alertmsg->get_var('output'));
    return $retval;
}

function FF_BaseFooter($showbottom=true) {
    global $_USER,$_CONF,$LANG_GF02,$forum,$_FF_CONF;

    $retval = '';

    if (!$_FF_CONF['registration_required'] || !COM_isAnonUser()) {
        $footer = new Template($_CONF['path'] . 'plugins/forum/templates/footer/');
        $footer->set_file ('footerblock','footer.thtml');
        if ($forum == '') {
            $footer->set_var ('forum_time', f_forumtime() );
            if ($showbottom == "true") {
                $footer->set_var ('forum_legend', f_legend() );
                $footer->set_var ('forum_whosonline', f_whosonline() );
            }
          } else {
            $footer->set_var ('forum_time', f_forumtime() );
            if ($showbottom == "true") {
                $footer->set_var ('forum_legend', f_legend() );
                $footer->set_var ('forum_rules', f_forumrules() );
            }
        }
        $footer->set_var ('search_forum', f_forumsearch($forum) );
        $footer->set_var ('select_forum', f_forumjump() );
        $footer->parse ('output', 'footerblock');
        $retval .= $footer->finish($footer->get_var('output'));
    }
    return $retval;
}

function f_forumsearch($forum) {
    global $_CONF,$_TABLES,$LANG_GF01,$LANG_GF02;

    $forum_search = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $forum_search->set_file (array ('forum_search'=>'forum_search.thtml'));
    $forum_search->set_var ('forum', $forum);
    if ($forum == "") {
        $forum_search->set_var ('search', $LANG_GF02['msg117']);
    } else {
        $forum_search->set_var ('search', $LANG_GF02['msg118']);
    }
    $forum_search->set_var ('jumpheading', $LANG_GF02['msg103']);
    $forum_search->set_var ('LANG_GO', $LANG_GF01['GO']);
    $forum_search->parse ('output', 'forum_search');
    return $forum_search->finish($forum_search->get_var('output'));
}

function f_forumjump($action='',$selected=0) {
    global $_FF_CONF, $_CONF,$_TABLES,$LANG_GF01,$LANG_GF02;

    $initialOptGroup = 0;
    $selecthtml = "";

    $asql = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($asql)) {
        $firstforum=true;
        $bsql = DB_query("SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $A['id']." AND is_hidden=0 ORDER BY forum_order ASC");

        $initialOptGroup = 0;
        while( $B = DB_fetchArray($bsql) ) {
            if (SEC_inGroup($B['grp_id'])) {
                if ($firstforum) {
                    $selecthtml .= '<optgroup label="' .$A['cat_name']. '">';
                    $initialOptGroup = 1;
                }
                $firstforum=false;
                if ($selected > 0 AND $selected == $B['forum_id']) {
                    $selecthtml .= LB .'<option value="' .$B['forum_id']. '" selected="selected">&#187;&nbsp;' .$B['forum_name']. '</option>';
                } else {
                    $selecthtml .= LB .'<option value="' .$B['forum_id']. '">&#187;&nbsp;' .$B['forum_name']. '</option>';
                }
            }
        }
        if ( $initialOptGroup == 1 ) {
            $selecthtml .= '</optgroup>';
        }
    }
    $forum_jump = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $forum_jump->set_file (array ('forum_jump'=>'forum_jump.thtml'));
    $forum_jump->set_var ('LANG_msg103', $LANG_GF02['msg103']);
    $forum_jump->set_var ('LANG_msg106', $LANG_GF02['msg106']);
    $forum_jump->set_var ('jumpheading', $LANG_GF02['msg103']);

    if ($action == '') {
        $forum_jump->set_var ('action', $_CONF['site_url'] . '/forum/index.php');
    } else {
        $forum_jump->set_var ('action', $action);
    }
    $forum_jump->set_var ('selecthtml', $selecthtml);
    $forum_jump->set_var ('LANG_GO', $LANG_GF01['GO']);
    $forum_jump->parse ('output', 'forum_jump');
    return $forum_jump->finish($forum_jump->get_var('output'));
}

function f_forumtime() {
    global $_USER, $_FF_CONF, $_CONF,$_TABLES,$LANG_GF01,$LANG_GF02,$forum;

    $dt = new Date('now',$_USER['tzid']);
    $tz = $dt->getTimezone();

    $forum_time = new Template($_CONF['path'] . 'plugins/forum/templates/footer');
    $forum_time->set_file (array ('forum_time'=>'forum_time.thtml'));
    $timezone = $dt->format('T',true);
    $time = $dt->format($_CONF['timeonly'],true);
    $forum_time->set_var ('message', sprintf($LANG_GF02['msg121'],$timezone,$time));
    $forum_time->parse ('output', 'forum_time');
    return $forum_time->finish($forum_time->get_var('output'));
}

function f_legend() {
    global $_FF_CONF,$forum,$_CONF,$LANG_GF01,$LANG_GF02;

    $forum_legend = new Template($_CONF['path'] . 'plugins/forum/templates/footer/');
    $forum_legend->set_file (array ('forum_legend'=>'forum_legend.thtml'));

    if ($forum == '') {
        $forum_legend->set_var ('normal_msg', $LANG_GF02['msg194']);
        $forum_legend->set_var ('new_msg', $LANG_GF02['msg108']);
        $forum_legend->set_var ('normal_icon','<img src="'._ff_getImage('quietforum').'" alt="'.$LANG_GF02['msg194'].'" title="' .$LANG_GF02['msg194']. '"/>');
        $forum_legend->set_var ('new_icon','<img src="'._ff_getImage('busyforum').'" alt="'.$LANG_GF02['msg111'].'" title="' .$LANG_GF02['msg111']. '"/>');
        $forum_legend->set_var ('viewnew_icon','<img src="'._ff_getImage('viewnew').'" alt="' . $LANG_GF02['msg112'] .'" title="' .$LANG_GF02['msg112']. '"/>');
        $forum_legend->set_var ('viewnew_msg', $LANG_GF02['msg112']);
        $forum_legend->set_var ('markread_icon','<img src="'._ff_getImage('allread').'" alt="' . $LANG_GF02['msg84'] .'" title="' .$LANG_GF02['msg84']. '"/>');
        $forum_legend->set_var ('markread_msg', $LANG_GF02['msg84']);
    } else {
        $sticky_icon = '<img src="'._ff_getImage('sticky').'" alt="' .$LANG_GF02['msg61']. '" title="' .$LANG_GF02['msg61']. '"/>';
        $locked_icon = '<img src="'._ff_getImage('locked').'" alt="' .$LANG_GF02['msg114']. '" title="' .$LANG_GF02['msg114']. '"/>';
        $stickynew_icon = '<img src="'._ff_getImage('sticky_new').'" alt="' .$LANG_GF02['msg115']. '" title="' .$LANG_GF02['msg115']. '"/>';
        $lockednew_icon = '<img src="'._ff_getImage('locked_new').'" alt="' .$LANG_GF02['msg116']. '" title="' .$LANG_GF02['msg116']. '"/>';
        $forum_legend->set_var ('normal_icon','<img src="'._ff_getImage('noposts').'" alt="'.$LANG_GF02['msg59'].'" title="' .$LANG_GF02['msg59']. '"/>');
        $forum_legend->set_var ('new_icon','<img src="'._ff_getImage('newposts').'" alt="'.$LANG_GF02['msg60'].'" title="' .$LANG_GF02['msg60']. '"/>');
        $forum_legend->set_var ('normal_msg', $LANG_GF02['msg59']);
        $forum_legend->set_var ('new_msg', $LANG_GF02['msg60']);
        $forum_legend->set_var ('sticky_msg',$LANG_GF02['msg61']);
        $forum_legend->set_var ('locked_msg', $LANG_GF02['msg114']);
        $forum_legend->set_var ('stickynew_msg', $LANG_GF02['msg115']);
        $forum_legend->set_var ('lockednew_msg', $LANG_GF02['msg116']);
        $forum_legend->set_var ('locked_icon', $locked_icon);
        $forum_legend->set_var ('sticky_icon', $sticky_icon);
        $forum_legend->set_var ('stickynew_icon', $stickynew_icon);
        $forum_legend->set_var ('lockednew_icon', $lockednew_icon);
    }

    $forum_legend->parse ('output', 'forum_legend');
    return $forum_legend->finish($forum_legend->get_var('output'));
}

function f_whosonline(){
    global $_FF_CONF, $_CONF,$_TABLES,$LANG_GF02;

    $onlineusers = phpblock_whosonline();
    $forum_users = new Template($_CONF['path'] . 'plugins/forum/templates/footer/');
    $forum_users->set_file (array ('forum_users'=>'forum_users.thtml'));
    $forum_users->set_var ('LANG_msg07', $LANG_GF02['msg07']);
    $forum_users->set_var ('onlineusers', $onlineusers);
    $forum_users->parse ('output', 'forum_users');
    return $forum_users->finish($forum_users->get_var('output'));
}

function f_forumrules() {
    global $_CONF,$_USER,$FF_userprefs, $LANG_GF01,$LANG_GF02,$_FF_CONF,$canPost;

    if ( ($_FF_CONF['registered_to_post'] AND (COM_isAnonUser() ) ) || $canPost == 0 ) {
        $postperm_msg = $LANG_GF01['POST_PERM_MSG1'];
        $post_perm_image = '<img src="'._ff_getImage('red_dot').'" alt=""/>';
    } else {
        $postperm_msg = $LANG_GF01['POST_PERM_MSG1'];
        $post_perm_image = '<img src="'._ff_getImage('green_dot').'" alt=""/>';
    }
    if ($_FF_CONF['allow_html']) {
        $html_perm_image = '<img src="'._ff_getImage('green_dot').'" alt=""/>';
        if ($_FF_CONF['use_glfilter']) {
            $htmlmsg = $LANG_GF01['HTML_FILTER_MSG'];
        } else {
            $htmlmsg = $LANG_GF01['HTML_FULL_MSG'];
        }
    } else {
        $htmlmsg = $LANG_GF01['HTML_MSG'];
        $html_perm_image = '<img src="'._ff_getImage('red_dot').'" alt=""/>';
    }
    if ($_FF_CONF['use_censor']) {
        $censor_perm_image = '<img src="'._ff_getImage('green_dot').'" alt=""/>';
    } else {
        $censor_perm_image = '<img src="'._ff_getImage('red_dot').'" alt=""/>';
    }

    if ($FF_userprefs['viewanonposts']) {
        $anon_perm_image = '<img src="'._ff_getImage('green_dot').'" alt=""/>';
    } else {
        $anon_perm_image = '<img src="'._ff_getImage('red_dot').'" alt=""/>';
    }

    $forum_rules = new Template($_CONF['path'] . 'plugins/forum/templates/footer/');
    $forum_rules->set_file (array ('forum_rules'=>'forum_rules.thtml'));
    $forum_rules->set_var ('LANG_title', $LANG_GF02['msg101']);

    $forum_rules->set_var ('anonymous_msg', $LANG_GF01['ANON_PERM_MSG']);
    $forum_rules->set_var ('anon_perm_image', $anon_perm_image);

    $forum_rules->set_var ('postingperm_msg',$postperm_msg);
    $forum_rules->set_var ('post_perm_image', $post_perm_image);

    $forum_rules->set_var ('html_msg', $htmlmsg);
    $forum_rules->set_var ('html_perm_image', $html_perm_image);
    $forum_rules->set_var ('censor_msg', $LANG_GF01['CENSOR_PERM_MSG']);
    $forum_rules->set_var ('censor_perm_image', $censor_perm_image);

    $forum_rules->parse ('output', 'forum_rules');
    return $forum_rules->finish($forum_rules->get_var('output'));

}

function FF_isSubscribed( $forum_id, $topic_id, $uid = 0 )
{
    global $_TABLES;

    $sql = "SELECT id FROM {$_TABLES['subscriptions']} WHERE ((type='forum' AND id=".(int) $topic_id." AND uid=".(int) $uid .") ";
    $sql .= "OR (type='forum' AND category=".(int) $forum_id." AND id=0 and uid=".(int) $uid."))";
    $result = DB_query($sql);
    if ( DB_numRows($result) > 0 ) {
        return true;
    }
    return false;
}

function FF_GetUserBlocks( &$blocks ) {
    global $_TABLES, $_CONF, $_USER, $LANG21, $topic, $page, $newstories;

    $retval = '';
    $sql = "SELECT name,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['blocks']} WHERE onleft = 1 AND is_enabled = 1";

    // Get user preferences on blocks
    if( !isset( $_USER['noboxes'] ) || !isset( $_USER['boxes'] )) {
        if( !COM_isAnonUser() ) {
            $result = DB_query( "SELECT boxes,noboxes FROM {$_TABLES['userindex']} WHERE uid = ".(int) $_USER['uid']);
            list($_USER['boxes'], $_USER['noboxes']) = DB_fetchArray( $result );
        } else {
            $_USER['boxes'] = '';
            $_USER['noboxes'] = 0;
        }
    }
    $sql .= " AND (tid = 'all' AND type <> 'layout')";
    if( !empty( $_USER['boxes'] )) {
        $BOXES = str_replace( ' ', ',', trim($_USER['boxes']) );
        $sql .= " AND (bid NOT IN ($BOXES) OR bid = '-1')";
    }

    $sql .= ' ORDER BY blockorder,title asc';
    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );

    for( $i = 1; $i <= $nrows; $i++ ) {
        $A = DB_fetchArray( $result );
        if( SEC_hasAccess( $A['owner_id'], $A['group_id'], $A['perm_owner'], $A['perm_group'], $A['perm_members'], $A['perm_anon']) > 0 ) {
            $blocks[] = $A['name'];
        }
    }
    return $blocks;
}

// we seem to grab the group name a whole lot of times,
// this will reduce the number of DB calls considerably...

function _ff_getGroup($grp_id)
{
    global $_TABLES;

    static $_ff_groups = array();

    if ( !isset($_ff_groups[$grp_id] )) {
        $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='".DB_escapeString($grp_id)."'");
        $_ff_groups[$grp_id] = $groupname;
    }
    return $_ff_groups[$grp_id];
}

function forum_showBlocks($showblocks)
{
    global $_CONF, $_USER, $_TABLES;

    $retval = '';
    if( !isset( $_USER['noboxes'] )) {
        if ( !COM_isAnonUser() ) {
            $noboxes = DB_getItem( $_TABLES['userindex'], 'noboxes', "uid=".(int) $_USER['uid']);
        } else {
            $noboxes = 0;
        }
    } else {
        $noboxes = $_USER['noboxes'];
    }

    if ( is_array($showblocks)) {
        foreach($showblocks as $block) {
            $sql = "SELECT bid, name,type,title,content,rdfurl,phpblockfn,help,allow_autotags FROM {$_TABLES['blocks']} WHERE name='".DB_escapeString($block)."'";
            $result = DB_query($sql);
            if (DB_numRows($result) == 1) {
                $A = DB_fetchArray($result);
                $retval .= COM_formatBlock($A,$noboxes);
            }
        }
    }

    return $retval;
}

// Generates the HTML Select element for the listing of filemgmt plugin the user has access to
function gf_makeFilemgmtCatSelect($uid) {
    global $_CONF,$_TABLES, $_DB_name;

    include_once $_CONF['path'].'plugins/filemgmt/include/xoopstree.php';
    include_once $_CONF['path'].'plugins/filemgmt/include/textsanitizer.php';
    $_GROUPS = SEC_getUserGroups( $uid );
    $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");
    $mytree->setGroupUploadAccessFilter($_GROUPS);
    return $mytree->makeMySelBoxNoHeading('title', 'title','','','filemgmtcat');
}


/*
 * The purpose of this function is to update 2 items:
 *
 * 1 - gf_forum - the last_post_rec
 * 2 - gf_topic - the lastupdated and last_reply_rec for a topic parent record
 */

function gf_updateLastPost($forumid,$topicparent=0) {
    global $_TABLES;

    // update the latest post record in the forum table...
    $query = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum=".intval($forumid)." ORDER BY date DESC LIMIT 1" );
    list($lastrecid) = DB_fetchArray($query);
    DB_query("UPDATE {$_TABLES['ff_forums']} SET last_post_rec=".intval($lastrecid)." WHERE forum_id=".intval($forumid));

    if ( $topicparent > 0 ) {  // if 0, we are just setting the forum record...
        // Update the topic record with lastupdated and last_reply_rec
        $sql = "SELECT pid FROM {$_TABLES['ff_topic']} WHERE id=".intval($topicparent)." AND forum=".intval($forumid);
        $query = DB_query($sql);
        list ($parent_id) = DB_fetchArray($query);
        $parent_id = COM_applyFilter($parent_id,true);

        if ( $parent_id == 0 ) {
            $parent_id = $topicparent;
        }
        $countQuery = "SELECT COUNT(*) AS numreplies FROM {$_TABLES['ff_topic']} WHERE forum=".intval($forumid). " AND pid=".$parent_id;
        $query = DB_query($countQuery);
        list($numreplies) = DB_fetchArray($query);

        if ( $numreplies == 0 ) {
            $last_reply_rec = 0;
            $lastupdated = DB_getItem($_TABLES['ff_topic'],'date','id='.intval($parent_id));
            DB_query("UPDATE {$_TABLES['ff_topic']} SET replies=0,last_reply_rec=".$last_reply_rec.", lastupdated='".$lastupdated."' WHERE id=".intval($parent_id));
        } else {
            $query = DB_query("SELECT id,date FROM {$_TABLES['ff_topic']} WHERE forum=".intval($forumid)." AND pid=".$parent_id ." ORDER BY date DESC LIMIT 1");
            list($last_reply_rec,$lastupdated) = DB_fetchArray($query);
            $parent_date = DB_getItem($_TABLES['ff_topic'],'date','id='.$parent_id);

            if ( $parent_date > $lastupdated ) {
                $lastupdated = $parent_date;
            }
            DB_query("UPDATE {$_TABLES['ff_topic']} SET replies=".$numreplies.",last_reply_rec=".$last_reply_rec.", lastupdated='".$lastupdated."' WHERE id=".$parent_id);
        }
    }
}

?>