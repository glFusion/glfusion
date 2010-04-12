<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | memberlist.php                                                           |
// |                                                                          |
// | Display a formatted listing of users                                     |
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
USES_lib_admin();

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( !$CONF_FORUM['allow_memberlist'] || COM_isAnonUser() ) {
    echo COM_refresh($_CONF['site_url'] .'/forum/index.php');
    exit;
}

function ADMIN_getListField_memberlist($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $_IMAGE_TYPE;
    global $CONF_FORUM,$_SYSTEM,$LANG_GF02;

    if ( !isset($A['status']) ) {
        $A['status'] = 0;
    }

    $retval = '';

    switch ($fieldname) {
        case 'username' :
            $url = $_CONF['site_url'].'/users.php?mode=profile&uid='.$A['uid'];
            $retval = COM_createLink($fieldvalue,$url);
            break;
        case 'posts' :
            $posts = DB_count($_TABLES['gf_topic'],'uid',$A['uid']);
            $retval = $posts;
            break;
        case 'homepage' :
            $retval = '';
            if($A['homepage'] != '') {
                $homepage = $A['homepage'];
                if(!preg_match("/http/i",$homepage)) {
                    $homepage = 'http://' .$homepage;
                }
                $retval = '<a href="'.$homepage.'"><img src="'.gf_getImage('home').'"/></a>';
            }
            break;
        case 'email' :
            if($A['emailfromuser'] == '1') {
                $retval = '<a href="'.$_CONF['site_url'].'/profiles.php?uid='.$A['uid'].'"><img src="'.gf_getImage('email').'"/></a>';
            }
            break;
        case 'pm' :
            if ($CONF_FORUM['use_pm_plugin']) {
                $pmplugin_link = forumPLG_getPMlink($siteMembers['username']);
                $retval = '<a href="'.$pmplugin_link.'"><img src="'.gf_getImage('pm').'"/></a>';
            }
            break;
        case 'lastpost' :
            $A['posts'] = DB_count($_TABLES['gf_topic'],'uid',$A['uid']);
            if ($A['posts'] > 0) {
                $reportlinkURL = $_CONF['site_url'] .'/forum/memberlist.php?op=last10posts&amp;showuser='.$A['uid'];
//                $reportlinkURL .= '&amp;prevorder='.$order.'&amp;direction='.$direction.'&amp;page='.$page;
                $retval = '<a href="'.$reportlinkURL.'"><img src="'.gf_getImage('latestposts').'"/></a>';
            }
            break;
        case 'regdate':
            $phpdate = strtotime( $fieldvalue );
            $retval = @strftime( $CONF_FORUM['default_Datetime_format'], $phpdate );
            if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                $retval = iconv('ISO-8859-1','UTF-8',$retval);
            }
            break;
        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

$chkactivity = 0;
if ( isset($_GET['chkactivity']) ) {
    $chkactivity = COM_applyFilter($_GET['chkactivity'],true);
}
if ( isset($_POST['chkactivity'] ) ) {
    $chkactivity = COM_applyFilter($_POST['chkactivity'],true);
} elseif ( isset($_POST['query_limit']) ) {
    $chkactivity = 0;
}
if ( isset($_GET['showuser'] ) ) {
    $showuser   = COM_applyFilter($_GET['showuser'],true);
} else {
    $showuser = 0;
}
if ( isset($_GET['op'] ) ) {
    $op         = COM_applyFilter($_GET['op']);
} else {
    $op = '';
}


// Display Common headers
gf_siteHeader();

//Check is anonymous users can access
forum_chkUsercanAccess();

if ($CONF_FORUM['usermenu'] == 'navbar') {
   echo forumNavbarMenu($LANG_GF02['msg200']);;
}

if ($op == "last10posts") {

    USES_lib_admin();
    USES_lib_html2text();

    $retval = '';

    $header_arr = array(
        array('text' => $LANG_GF01['FORUM'],  'field' => 'forum'),
        array('text' => $LANG_GF01['TOPIC'],  'field' => 'subject'),
        array('text' => $LANG_GF92['sb_latestposts'],   'field' => 'date', 'nowrap' => true),
    );
    $data_arr = array();
    $text_arr = array();

    $retval .= COM_startBlock($LANG_GF02['msg86'] . DB_getItem($_TABLES['users'],"username", "uid=".(int) $showuser), '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $groups = array ();
    $usergroups = SEC_getUserGroups();
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    $inforum = "";

    $orderby    = 'date';
    $order      = 1;
    $direction  = "DESC";

    $sql = "SELECT a.uid,a.status,a.date,a.subject,a.comment,a.replies,a.views,a.id,a.forum,b.forum_name,a.lastupdated,b.forum_id FROM {$_TABLES['gf_topic']} a ";
    $sql .= "LEFT JOIN {$_TABLES['gf_forums']} b ON a.forum=b.forum_id ";
    $sql .= "WHERE (a.uid = ".(int) $showuser.") AND b.grp_id IN ($grouplist) ";
    $sql .= "ORDER BY a.date DESC LIMIT {$CONF_FORUM['show_last_post_count']}";

    $result = DB_query($sql);

    $nrows = DB_numRows($result);

    $displayrecs = 0;
    for ($i = 1; $i <= $nrows; $i++) {
        $P = DB_fetchArray($result);
        $userlogtime = DB_getItem($_TABLES['gf_log'],"time", "uid=$_USER[uid] AND topic={$P['id']}");
        if ($userlogtime == NULL OR $P['lastupdated'] > $userlogtime) {
            if ($CONF_FORUM['use_censor']) {
                $P['subject'] = COM_checkWords($P['subject']);
                $P['comment'] = COM_checkWords($P['comment']);
            }
            $topic_id = $P['id'];
            $displayrecs++;

            $firstdate = strftime($CONF_FORUM['default_Datetime_format'], $P['date']);
            if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                $firstdate = @iconv('ISO-8859-1','UTF-8',$firstdate);
            }
            $lastdate  = strftime($CONF_FORUM['default_Datetime_format'], $P['lastupdated']);
            if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                $lastdate = @iconv('ISO-8859-1','UTF-8',$lastdate);
            }

            if ($P['uid'] > 1) {
                $topicinfo = "{$LANG_GF01['STARTEDBY']} " . COM_getDisplayName($P['uid']) . ', ';
            } else {
                $topicinfo = "{$LANG_GF01['STARTEDBY']} {$P['name']},";
            }

            $topicinfo .= "{$firstdate}<br" . XHTML . ">{$LANG_GF01['VIEWS']}:{$P['views']}, {$LANG_GF01['REPLIES']}:{$P['replies']}<br" . XHTML . ">";

            if (empty ($P['last_reply_rec']) || $P['last_reply_rec'] < 1) {
                $lastid = $P['id'];
                $testText = gf_formatTextBlock($P['comment'],'text','text',$P['status']);
                $testText = strip_tags($testText);
                $html2txt = new html2text($testText,false);
                $testText = trim($html2txt->get_text());
                $lastpostinfogll = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$CONF_FORUM['contentinfo_numchars']). '...')));
            } else {
                $qlreply = DB_query("SELECT id,uid,name,comment,date,status FROM {$_TABLES['gf_topic']} WHERE id={$P['last_reply_rec']}");
                $B = DB_fetchArray($qlreply);
                $lastid = $B['id'];
                $lastcomment = $B['comment'];
                $P['date'] = $B['date'];
                if ($B['uid'] > 1) {
                    $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],COM_getDisplayName($B['uid']));
                } else {
                    $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],$B['name']);
                }
                $testText = gf_formatTextBlock($B['comment'],'text','text',$B['status']);
                $testText = strip_tags($testText);
                $html2txt = new html2text($testText,false);
                $testText = trim($html2txt->get_text());
                $lastpostinfogll = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$CONF_FORUM['contentinfo_numchars']). '...')));
            }
            $link = '<a class="gf_mootip" style="text-decoration:none; white-space:nowrap;" href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $topic_id . '&amp;lastpost=true#' . $lastid . '" title="' . htmlspecialchars($P['subject']) . '::' . $lastpostinfogll . '" rel="nofollow">';

            $topiclink = '<a class="gf_mootip" style="text-decoration:none;" href="' . $_CONF['site_url'] .'/forum/viewtopic.php?showtopic=' . $topic_id . '" title="' . htmlspecialchars($P['subject']) . '::' . $topicinfo . '">' . $P['subject'] . '</a>';

            $tdate = strftime( $CONF_FORUM['default_Datetime_format'], $P['date'] );
            if ( $_SYSTEM['swedish_date_hack'] == true && function_exists('iconv') ) {
                $tdate = iconv('ISO-8859-1','UTF-8',$tdate);
            }

            $data_arr[] = array('forum'   => '<a href="'.$_CONF['site_url'].'/forum/index.php?forum='.$P['forum_id'].'">'.$P['forum_name'].'</a>',
                                'subject' => $topiclink,
                                'date'    => $link . $tdate . '</a>'
                                );

            if ($displayrecs >= 100) {
                break;
            }
        }
    }

    $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    echo $retval;
} else {
    $retval = '';

    $header_arr = array(      # display 'text' and use table field 'field'
                  array('text' => 'User',        'field' => 'username', 'sort' => true),
                  array('text' => 'Registered',  'field' => 'regdate', 'sort' => false),
                  array('text' => 'Posts',       'field' => 'posts', 'sort' => false),
                  array('text' => 'Email',       'field' => 'email', 'sort' => false),
                  array('text' => 'WWW',         'field' => 'homepage', 'sort' => false),
                  array('text' => 'Last',        'field' => 'lastpost', 'sort' => false)
    );
    $form_url = $_CONF['site_url'] . '/forum/memberlist.php';
    $form_url .= ($chkactivity) ? '?chkactivity=1' : '';

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $form_url,
        'help_url'   => '',
        'nowrap'     => 'date'
    );

    $defsort_arr = array('field'     => 'username',
                         'direction' => 'ASC');

    if ($chkactivity) {
        $sql = "SELECT user.uid,user.uid,user.username,user.regdate,user.email,user.homepage, count(*) as posts, userprefs.emailfromuser ";
        $sql .= " FROM {$_TABLES['users']} user, {$_TABLES['userprefs']} userprefs, {$_TABLES['gf_topic']} topic WHERE";
        $sql .= " user.uid <> 1 AND user.status = 3 AND user.uid=topic.uid AND user.uid=userprefs.uid ";
        $sql .= "GROUP by uid ";
    } else {
        $sql = "SELECT user.uid,user.uid,user.username,user.regdate,user.email,user.homepage, userprefs.emailfromuser ";
        $sql .= " FROM {$_TABLES['users']} user, {$_TABLES['userprefs']} userprefs WHERE user.uid > 1 AND user.status = 3 ";
        $sql .= "AND user.uid=userprefs.uid ";
    }

    $query_arr = array('table'          => 'topic',
                       'sql'            => $sql,
                       'query_fields'   => array('username'),
                       'default_filter' => '');

    $filter  = '<span style="padding-right:20px;">';

    if ($chkactivity) {
        $filter .= '<label for="chkactivity"><input id="chkactivity" type="checkbox" name="chkactivity" value="1" onclick="this.form.submit();" checked="checked"' . XHTML . '>';
    } else {
        $filter .= '<label for="chkactivity"><input id="chkactivity" type="checkbox" name="chkactivity" value="1" onclick="this.form.submit();"' . XHTML . '>';
    }

    $filter .= $LANG_GF02['msg88b'] . '</label></span>';

    $retval .= COM_startBlock($LANG_GF02['msg200'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_list('popular', 'ADMIN_getListField_memberlist', $header_arr,
                          $text_arr, $query_arr, $defsort_arr, $filter);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    echo $retval;
}

gf_siteFooter();

?>