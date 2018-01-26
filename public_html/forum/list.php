<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | list.php                                                                 |
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

USES_forum_format();
USES_forum_functions();

//Check is anonymous users can access
if ( $_FF_CONF['registration_required'] && COM_isAnonUser()) {
    $display = COM_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

function FF_newPosts($forum = 0)
{
    global $_CONF, $_TABLES, $_USER, $_FF_CONF, $LANG_GF01, $LANG_GF02, $LANG_GF92;

    $pageTitle = $LANG_GF02['new_posts'];
    $retval = '';

    if ( COM_isAnonUser() ) {
        $retval .= SEC_loginRequiredForm();
        return array($pageTitle,$retval);
    }

    $c = glFusion\Cache::getInstance();
    $key = 'forum'.$forum.'__'.$c->securityHash(true);
    $retval = $c->get($key);
    if ( $retval !== null ) {
        return array($pageTitle,$retval);
    }

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file('list', 'lists.thtml');

    if ( $_FF_CONF['enable_user_rating_system'] && !COM_isAnonUser() ) {
        $rating = (int) _ff_getUserRating((int) $_USER['uid']);
    }

    USES_lib_admin();

    $dt = new Date('now',$_USER['tzid']);

    $header_arr = array(
        array('text' => $LANG_GF01['FORUM'],  'field' => 'forum'),
        array('text' => $LANG_GF01['TOPIC'],  'field' => 'subject'),
        array('text' => $LANG_GF92['sb_latestposts'],   'field' => 'date', 'nowrap' => true),
    );
    $data_arr = array();
    $text_arr = array('no_data' => $LANG_GF02['msg202']);
    if ($_FF_CONF['usermenu'] == 'navbar') {
        $T->set_var('navbar',FF_NavbarMenu($LANG_GF02['new_posts']));
    }
    $T->set_var('block_start',COM_startBlock($LANG_GF02['msg111'], '',
                              COM_getBlockTemplate('_admin_block', 'header')));
    $groups = array ();
    $usergroups = SEC_getUserGroups();
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    if ($forum > 0) {
        $inforum = "AND forum=".(int) $forum;
    } else {
        $inforum = "";
    }

    $orderby    = 'lastupdated';
    $order      = 1;
    $direction  = "DESC";

    if ($_FF_CONF['enable_user_rating_system']) {
        $sql = "SELECT * FROM {$_TABLES['ff_topic']} a
                LEFT JOIN (SELECT topic, time, uid as user FROM {$_TABLES['ff_log']} where uid=".(int)$_USER['uid'].") as l on a.id=l.topic
                LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id
                WHERE (pid=0) AND b.rating_view <= ".$rating." $inforum AND b.grp_id IN (".$grouplist.") AND b.no_newposts = 0
                and (l.topic IS NULL OR a.lastupdated > l.time)
                ORDER BY $orderby $direction";
    } else {
        $sql = "SELECT * FROM {$_TABLES['ff_topic']} a
                LEFT JOIN (SELECT topic, time, uid as user FROM {$_TABLES['ff_log']} WHERE uid=".(int)$_USER['uid'].") as l on a.id=l.topic
                LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id
                WHERE (pid=0) $inforum AND b.grp_id IN (".$grouplist.") AND b.no_newposts = 0
                and (l.topic IS NULL OR a.lastupdated > l.time)
                ORDER BY $orderby $direction";
    }

    $result = DB_query($sql);

    $nrows = DB_numRows($result);

    $displayrecs = 0;
    for ($i = 1; $i <= $nrows; $i++) {
        $P = DB_fetchArray($result);

        if ($_FF_CONF['use_censor']) {
            $P['subject'] = COM_checkWords($P['subject']);
            $P['comment'] = COM_checkWords($P['comment']);
        }
        $topic_id = $P['id'];
        $displayrecs++;

        $dt->setTimestamp($P['date']);
        $firstdate = $dt->format($_CONF['date'],true);
        $dt->setTimestamp($P['lastupdated']);
        $lastdate = $dt->format($_CONF['date'],true);

        if ($P['uid'] > 1) {
            $topicinfo = "{$LANG_GF01['STARTEDBY']} " . COM_getDisplayName($P['uid']) . ', ';
        } else {
            $topicinfo = "{$LANG_GF01['STARTEDBY']} {$P['name']},";
        }

        $topicinfo .= "{$firstdate}<br/>{$LANG_GF01['VIEWS']}:{$P['views']}, {$LANG_GF01['REPLIES']}:{$P['replies']}<br/>";

        if (empty ($P['last_reply_rec']) || $P['last_reply_rec'] < 1) {
            $lastid = $P['id'];
            $testText = FF_formatTextBlock($P['comment'],'text','text',$P['status']);
            $testText = strip_tags($testText);
            $html2txt = new Html2Text\Html2Text($testText,false);
            $testText = trim($html2txt->get_text());
            $lastpostinfogll = @htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')));
        } else {
            $qlreply = DB_query("SELECT id,uid,name,comment,date,status FROM {$_TABLES['ff_topic']} WHERE id=".(int) $P['last_reply_rec']);
            $B = DB_fetchArray($qlreply);
            $lastid = $B['id'];
            $lastcomment = $B['comment'];
            $P['date'] = $B['date'];
            if ($B['uid'] > 1) {
                $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],COM_getDisplayName($B['uid']));
            } else {
                $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],$B['name']);
            }
            $testText = FF_formatTextBlock($B['comment'],'text','text',$B['status']);
            $testText = strip_tags($testText);
            $html2txt = new Html2Text\Html2Text($testText,false);
            $testText = trim($html2txt->get_text());
            $lastpostinfogll = @htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')));
        }
        $link = '<a class="'.COM_getTooltipStyle().'" style="text-decoration:none; white-space:nowrap;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $topic_id . '&amp;lastpost=true#' . $lastid . '" title="' . @htmlspecialchars($P['subject']) . '::' . $lastpostinfogll . '" rel="nofollow">';

        $topiclink = '<a class="'.COM_getTooltipStyle().'" style="text-decoration:none;" href="' . $_CONF['site_url'] .'/forum/viewtopic.php?showtopic=' . $topic_id . '" title="' . @htmlspecialchars($P['subject']) . '::' . $topicinfo . '">' . $P['subject'] . '</a>';

        $dt->setTimestamp($P['date']);
        $tdate = $dt->format($_CONF['date'],true);

        $data_arr[] = array('forum'   => '<a href="'.$_CONF['site_url'].'/forum/index.php?forum='.$P['forum_id'].'">'.$P['forum_name'].'</a>',
                            'subject' => $topiclink,
                            'date'    => $link . $tdate . '</a>'
                            );

        if ($displayrecs >= 100) {
            break;
        }
    }

    $T->set_var('list_data',ADMIN_simpleList("", $header_arr, $text_arr, $data_arr));
    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse ('output', 'list');
    $retval = $T->finish($T->get_var('output'));

    $c->set($key,$retval,'forum',3600); // 1 hours

    return array($pageTitle,$retval);
}

function FF_popular()
{
    global $_CONF, $_TABLES, $_USER, $_FF_CONF, $LANG_GF01, $LANG_GF02, $LANG_GF92;

    $pageTitle = $LANG_GF02['msg152'];
    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file('list', 'lists.thtml');

    USES_lib_admin();

    $header_arr = array(      # display 'text' and use table field 'field'
                  array('text' => $LANG_GF01['FORUM'],     'field' => 'forum_name', 'sort' => false),
                  array('text' => $LANG_GF01['TOPIC'],     'field' => 'subject', 'sort' => false),
                  array('text' => $LANG_GF01['REPLIES'],   'field' => 'replies', 'sort' => false),
                  array('text' => $LANG_GF01['VIEWS'],     'field' => 'views', 'sort' => false),
                  array('text' => $LANG_GF01['DATE'],      'field' => 'date', 'sort' => false, 'nowrap' => true)
    );
    if ($_FF_CONF['usermenu'] == 'navbar') {
        $T->set_var('navbar',FF_NavbarMenu($LANG_GF02['msg201']));
    }

    $T->set_var('block_start',COM_startBlock($LANG_GF02['msg201'], '',
                              COM_getBlockTemplate('_admin_block', 'header')));

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_url'] . '/forum/list.php?op=popular',
        'help_url'   => '',
        'nowrap'     => 'date'
    );

    $defsort_arr = array('field'     => 'views',
                         'direction' => 'DESC');

    $groups = array ();
    $usergroups = SEC_getUserGroups();
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    if ( !COM_isAnonUser() && $_FF_CONF['enable_user_rating_system'] ) {
        $grade = (int) _ff_getUserRating((int) $_USER['uid']);
        $ratingSQL = ' AND b.rating_view <= '.$grade;
    } else {
        $ratingSQL = '';
    }

    $sql  = "SELECT a.id, a.forum, a.name, a.date, a.lastupdated, a.last_reply_rec, a.subject, ";
    $sql .= "a.comment, a.uid, a.name, a.pid, a.replies, a.views, b.forum_name  ";
    $sql .= "FROM {$_TABLES['ff_topic']} a ";
    $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id ";
    $sql .= "WHERE pid=0 AND b.grp_id IN ($grouplist) AND b.no_newposts = 0 " . $ratingSQL;

    $query_arr = array('table'          => 'topic',
                       'sql'            => $sql,
                       'query_fields'   => array('date','subject','comment','replies','views','id','forum','forum_name'),
                       'default_filter' => '');

    $T->set_var('list_data',ADMIN_list('popular', '_ff_getListField_forum', $header_arr,
                          $text_arr, $query_arr, $defsort_arr));
    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse ('output', 'list');
    $retval = $T->finish($T->get_var('output'));

    return array($pageTitle,$retval);
}

function FF_bookmarks()
{
    global $_CONF, $_TABLES, $_USER, $_FF_CONF, $LANG_GF01, $LANG_GF02, $LANG_GF92;

    $retval = '';
    $pageTitle = $LANG_GF01['BOOKMARKS'];

    if ( COM_isAnonUser() ) {
        $retval .= SEC_loginRequiredForm();
        return array($pageTitle,$retval);
    }

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file('list', 'lists.thtml');

    USES_lib_admin();

    $header_arr = array(      # display 'text' and use table field 'field'
                  array('text' => '#',                     'field' => 'bookmark', 'sort' => false),
                  array('text' => $LANG_GF01['FORUM'],     'field' => 'forum_name', 'sort' => true),
                  array('text' => $LANG_GF01['TOPIC'],     'field' => 'subject', 'sort' => true),
                  array('text' => $LANG_GF01['AUTHOR'],    'field' => 'name', 'sort' => true),
                  array('text' => $LANG_GF01['REPLIES'],   'field' => 'replies', 'sort' => true),
                  array('text' => $LANG_GF01['VIEWS'],     'field' => 'views', 'sort' => true),
                  array('text' => $LANG_GF01['DATE'],      'field' => 'date', 'sort' => true, 'nowrap' => true)
    );
    if ($_FF_CONF['usermenu'] == 'navbar') {
        $T->set_var('navbar',FF_NavbarMenu($LANG_GF01['BOOKMARKS']));
    }

    $T->set_var('block_start',COM_startBlock($LANG_GF01['BOOKMARKS'], '',COM_getBlockTemplate('_admin_block', 'header')));

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_url'] . '/forum/list.php?op=bookmarks',
        'help_url'   => '',
        'no_data'    => $LANG_GF02['msg205']
    );

    $defsort_arr = array('field'     => 'date',
                         'direction' => 'DESC');

    $sql = "SELECT * FROM {$_TABLES['ff_bookmarks']} AS bookmarks LEFT JOIN {$_TABLES['ff_topic']} AS topics ON bookmarks.topic_id=topics.id LEFT JOIN {$_TABLES['ff_forums']} AS forums ON topics.forum=forums.forum_id WHERE topics.id != '' AND bookmarks.uid=" . $_USER['uid'];
    $query_arr = array('table'          => 'ff_bookmarks',
                       'sql'            => $sql,
                       'query_fields'   => array('topics.date','topics.subject','topics.comment','topics.name','topics.replies','topics.views','id','forum','forum_name'),
                       'default_filter' => '');

    $T->set_var('list_data',ADMIN_list('bookmarks', '_ff_getListField_forum', $header_arr,
                          $text_arr, $query_arr, $defsort_arr));
    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse ('output', 'list');
    $retval = $T->finish($T->get_var('output'));
    return array($pageTitle,$retval);
}

function FF_lastx()
{
    global $_CONF, $_TABLES, $_USER, $_FF_CONF, $LANG_GF01, $LANG_GF02, $LANG_GF92;

    $retval = '';
    $pageTitle = $LANG_GF01['LASTX'];

    $c = glFusion\Cache::getInstance();
    $key = 'forum_lastx__'.$c->securityHash(true);
    $retval = $c->get($key);
    if ( $retval !== null ) {
        return array($pageTitle,$retval);
    }

    USES_lib_admin();

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file('list', 'lists.thtml');

    $dt = new Date('now',$_USER['tzid']);

    $header_arr = array(
        array('text' => $LANG_GF01['FORUM'],  'field' => 'forum'),
        array('text' => $LANG_GF01['TOPIC'],  'field' => 'subject'),
        array('text' => $LANG_GF92['sb_latestposts'],   'field' => 'date', 'nowrap' => true),
    );
    $data_arr = array();
    $text_arr = array();
    if ($_FF_CONF['usermenu'] == 'navbar') {
        $T->set_var('navbar',FF_NavbarMenu($LANG_GF01['LASTX']));
    }
    $T->set_var('block_start',COM_startBlock($LANG_GF01['LASTX'], '',
                              COM_getBlockTemplate('_admin_block', 'header')));
    $groups = array ();
    $usergroups = SEC_getUserGroups();
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    if ( !COM_isAnonUser() && $_FF_CONF['enable_user_rating_system'] ) {
        $grade = (int) _ff_getUserRating((int) $_USER['uid']);
        $ratingSQL = ' AND b.rating_view <= '.$grade .' ';
    } else {
        $ratingSQL = '';
    }

    $sql  = "SELECT * ";
    $sql .= "FROM {$_TABLES['ff_topic']} a ";
    $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id ";
    $sql .= "WHERE pid=0 AND b.grp_id IN ($grouplist) AND b.no_newposts = 0 " . $ratingSQL;
    $sql .= "ORDER BY lastupdated DESC LIMIT {$_FF_CONF['show_last_post_count']}";
    $result = DB_query ($sql);

    $nrows = DB_numRows($result);
    $displayrecs = 0;
    for ($i = 1; $i <= $nrows; $i++) {
        $P = DB_fetchArray($result);
        if ($_FF_CONF['use_censor']) {
            $P['subject'] = COM_checkWords($P['subject']);
            $P['comment'] = COM_checkWords($P['comment']);
        }
        $topic_id = $P['id'];
        $displayrecs++;

        $dt->setTimestamp($P['date']);
        $firstdate = $dt->format($_CONF['date'],true);
        $dt->setTimestamp($P['lastupdated']);
        $lastdate = $dt->format($_CONF['date'],true);

        if ($P['uid'] > 1) {
            $topicinfo = "{$LANG_GF01['STARTEDBY']} " . COM_getDisplayName($P['uid']) . ', ';
        } else {
            $topicinfo = "{$LANG_GF01['STARTEDBY']} {$P['name']},";
        }

        $topicinfo .= "{$firstdate}<br/>{$LANG_GF01['VIEWS']}:{$P['views']}, {$LANG_GF01['REPLIES']}:{$P['replies']}<br/>";

        if (empty ($P['last_reply_rec']) || $P['last_reply_rec'] < 1) {
            $lastid = $P['id'];
            $testText = FF_formatTextBlock($P['comment'],'text','text',$P['status']);
            $testText = strip_tags($testText);
            $html2txt = new Html2Text\Html2Text($testText,false);
            $testText = trim($html2txt->get_text());
            $lastpostinfogll = @htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')),ENT_QUOTES,COM_getEncodingt());
        } else {
            $qlreply = DB_query("SELECT id,uid,name,comment,date,status FROM {$_TABLES['ff_topic']} WHERE id={$P['last_reply_rec']}");
            $B = DB_fetchArray($qlreply);
            $lastid = $B['id'];
            $lastcomment = $B['comment'];
            $P['date'] = $B['date'];
            if ($B['uid'] > 1) {
                $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],COM_getDisplayName($B['uid']));
            } else {
                $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],$B['name']);
            }
            $testText = FF_formatTextBlock($B['comment'],'text','text',$B['status']);
            $testText = strip_tags($testText);
            $html2txt = new Html2Text\Html2Text($testText,false);
            $testText = trim($html2txt->get_text());
            $lastpostinfogll = @htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')),ENT_QUOTES,COM_getEncodingt());
        }
        $link = '<a class="'.COM_getTooltipStyle().'" style="text-decoration:none; white-space:nowrap;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $topic_id . '&amp;lastpost=true#' . $lastid . '" title="' . @htmlspecialchars($P['subject'],ENT_QUOTES,COM_getEncodingt()) . '::' . $lastpostinfogll . '" rel="nofollow">';

        $link_notip = '<a style="text-decoration:none; white-space:nowrap;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $topic_id . '&amp;lastpost=true#' . $lastid . '" rel="nofollow">';

        $topiclink = '<a class="'.COM_getTooltipStyle().'" style="text-decoration:none;" href="' . $_CONF['site_url'] .'/forum/viewtopic.php?showtopic=' . $topic_id . '" title="' . @htmlspecialchars($P['subject'],ENT_QUOTES,COM_getEncodingt()) . '::' . $topicinfo . '">' . $P['subject'] . '</a>';

        $dt->setTimestamp($P['date']);
        $tdate = $dt->format($_CONF['date'],true);

        $data_arr[] = array('forum'   => $P['forum_name'],
                            'subject' => $topiclink,
                            'date'    => $link . $tdate . '</a>',
                            'date_notip' => $link_notip . $tdate .'</a>',
                            );

        if ($displayrecs >= $_FF_CONF['show_last_post_count']) {
            break;
        }
    }

    $L = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $L->set_file('latest', 'latestposts.thtml');
    $L->set_block('latest','rows','r');
    foreach ( $data_arr AS $row ) {
        $L->set_var(array(
           'forum' => $row['forum'],
           'subject' => $row['subject'],
           'date'   => $row['date'],
           'date_notip' => $row['date_notip'],
        ));
        $L->parse('r','rows',true);
    }
    $L->set_var(array(
        'lang_forum' => $LANG_GF01['FORUM'],
        'lang_topic' => $LANG_GF01['TOPIC'],
        'lang_latest_post' => $LANG_GF92['sb_latestposts']
    ));

    $L->parse ('list', 'latest');
    $output = $L->finish($L->get_var('list'));
    $T->set_var('list_data',$output);

//    $T->set_var('list_data',ADMIN_simpleList("", $header_arr, $text_arr, $data_arr));
    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse ('output', 'list');
    $retval = $T->finish($T->get_var('output'));

    $c->set($key,$retval,'forum',3600);

    return array($pageTitle,$retval);
}

function _ff_getListField_forum($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;
    global $_FF_CONF,$_SYSTEM,$LANG_GF02;

    if ( !isset($A['status']) ) {
        $A['status'] = 0;
    }

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    switch ($fieldname) {
        case 'date':
        case 'lastupdated' :
            $dt->setTimestamp($fieldvalue);
            $retval = $dt->format($_CONF['date'],true);
            break;
        case 'subject':
            $testText        = FF_formatTextBlock($A['comment'],'text','text',$A['status']);
            $testText        = strip_tags($testText);
            $html2txt        = new Html2Text\Html2Text($testText,false);
            $testText        = trim($html2txt->get_text());
            $lastpostinfogll = @htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$_FF_CONF['contentinfo_numchars']). '...')),ENT_QUOTES,COM_getEncodingt());
            $retval = '<a class="'.COM_getTooltipStyle().'" style="text-decoration:none;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . ($A['pid'] == 0 ? $A['id'] : $A['pid']) . '&amp;topic='.$A['id'].'#'.$A['id'].'" title="' . $A['subject'] . '::' . $lastpostinfogll . '" rel="nofollow">' . $fieldvalue . '</a>';
            break;
        case 'bookmark' :
            $bm_icon_on = '<img src="'._ff_getImage('star_on_sm').'" title="'.$LANG_GF02['msg204'].'" alt=""/>';
            $retval = '<span id="forumbookmark'.$A['topic_id'].'"><a href="#" onclick="ajax_toggleForumBookmark('.$A['topic_id'].');return false;">'.$bm_icon_on.'</a></span>';
            break;
        case 'replies' :
        case 'views'   :
            if ( $fieldvalue != '' ) {
                $retval = $fieldvalue;
            } else {
                $retval = '0';
            }
            break;
        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

/*
 * Main program code
 */

//Check if anonymous users allowed to access forum
forum_chkUsercanAccess();

$op = isset($_GET['op']) ? COM_applyFilter($_GET['op']) : 'lastx';
if ( !in_array($op,array('newposts','popular','bookmarks','lastx') ) ) {
    $op = 'lastx';
}

$pageTitle = '';
$pageBody  = '';

switch ($op) {
    case 'newposts' :
        $forum = 0;
        if ( isset($_GET['forum']) ) {
            $forum = COM_applyFilter($_GET['forum'],true);
        }
        list($pageTitle,$pageBody) = FF_newposts($forum);
        break;
    case 'popular' :
        list($pageTitle,$pageBody) = FF_popular();
        break;
    case 'bookmarks' :
        list($pageTitle,$pageBody) = FF_bookmarks();
        break;
    case 'lastx' :
        list($pageTitle,$pageBody) = FF_lastx();
        break;
    default :
        $pageBody .= 'Unknown option';
        break;
}

$display  = FF_siteHeader($pageTitle);
$display .= $pageBody;
$display .= FF_siteFooter();
echo $display;

?>