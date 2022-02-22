<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | This file implements the necessary glFusion Plugin API functions and     |
// | includes some common Forum functions                                     |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// load the language file(s), including custom strings if any

$langfile = $_CONF['path'].'plugins/forum/language/'.$_CONF['language'].'.php';
$custfile = $_CONF['path'].'plugins/forum/language/custom/'.$_CONF['language'].'.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/forum/language/english.php';
    $custfile = $_CONF['path'].'plugins/forum/language/custom/english.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

require_once $_CONF['path'].'plugins/forum/forum.php';
if ( isset($_PLUGIN_INFO['forum']) ) {
    $pi_version = $_PLUGIN_INFO['forum']['pi_version'];
} else {
    $pi_version = $_FF_CONF['pi_version'];
}

$ff_config = config::get_instance();
$_FF_FORUM = $ff_config->get_config('forum');
if ( is_array($_FF_FORUM) && is_array($_FF_CONF) ) {
    $_FF_CONF = array_merge($_FF_FORUM, $_FF_CONF);
}

// View Anonymous Posts - registed users can set this false
$_FF_CONF['show_anonymous_posts'] = 1;
$_FF_CONF['topic_order'] = 'ASC';
if ( isset($_FF_CONF['use_wysiwyg_editor']) ) {
    $_FF_CONF['allow_wysiwyg_editor'] = $_FF_CONF['use_wysiwyg_editor'];
} else {
    $_FF_CONF['allow_wysiwyg_editor'] = false;
}

if ( $_CONF['loginrequired'] == 1 ) {
    $_FF_CONF['registration_required'] = 1;
}

$_FF_CONF['default_Datetime_format'] = $_CONF['date'];
$_FF_CONF['default_Topic_Datetime_format'] = $_CONF['date'];

$FF_userprefs['topicsperpage']  = isset($_FF_CONF['show_topics_perpage']) ? $_FF_CONF['show_topics_perpage'] : 10;
$FF_userprefs['postsperpage']   = isset($_FF_CONF['show_posts_perpage']) ? $_FF_CONF['show_posts_perpage'] : 10;
$FF_userprefs['showiframe']     = isset($_FF_CONF['show_topicreview']) ? $_FF_CONF['show_topicreview'] : true;
$FF_userprefs['viewanonposts']  = isset($_FF_CONF['show_anonymous_posts']) ? $_FF_CONF['show_anonymous_posts'] : true;
$FF_userprefs['topic_order']    = isset($_FF_CONF['topic_order']) ? $_FF_CONF['topic_order'] : 'DESC';

if ( !COM_isAnonUser() ) {
    $result = DB_query("SELECT * FROM {$_TABLES['ff_userprefs']} where uid = ".(int) $_USER['uid'],1);
    if ( DB_numRows($result) > 0 ) {
        $FF_userprefs = DB_fetchArray($result);
        if ( $_FF_CONF['allow_wysiwyg_editor'] == 1 && isset($FF_userprefs['use_wysiwyg_editor'])) {
            $_FF_CONF['use_wysiwyg_editor'] = $FF_userprefs['use_wysiwyg_editor'];
        }
    }
}
$_FF_CONF['use_glfilter'] = 1; // always use the filter...

if ( !isset($_FF_CONF['showtopic_review_order']) ) {
    $_FF_CONF['showtopic_review_order'] = 'DESC';
}

if ( $FF_userprefs['topic_order'] != 'ASC' && $FF_userprefs['topic_order'] != 'DESC' ) {
    $FF_userprefs['topic_order'] = 'ASC';
}

if ( empty( $LANG_CHARSET )) {
    $_FF_CONF['charset'] = $_CONF['default_charset'];
    if ( empty( $charset )) {
        $_FF_CONF['charset'] = 'iso-8859-1';
    }
} else {
    $_FF_CONF['charset'] = $LANG_CHARSET;
}

spl_autoload_register(function ($class)
{
    if (strpos($class, 'Forum\\') === 0) {
        $class = str_replace('Forum\\', '', $class);
        $path = __DIR__ . '/classes/' . strtolower($class) . '.class.php';
        if (file_exists($path)) {
            include $path;
        }
    }
});

// PLG APIs

function plugin_getmenuitems_forum()
{
    global $_FF_CONF,$_CONF,$_USER, $LANG_GF00;

    $menuitems = array();

    if ( $_FF_CONF['registration_required'] == 1 ) {
        if (!COM_isAnonUser() ) {
            $menuitems["$LANG_GF00[pluginlabel]"] = $_CONF['site_url'].'/forum/index.php';
        } else {
            $menuitems = '';
        }
    } else {
        $menuitems["$LANG_GF00[pluginlabel]"] = $_CONF['site_url'].'/forum/index.php';
    }

    return $menuitems;
}

function plugin_autotags_forum($op,$content='',$autotag='') {
    global $_CONF, $LANG_FF_AUTOTAG;

    if ($op == 'tagname' ) {
        return 'forum';
    } elseif ($op == 'tagusage') {
        $tagUsage = array(
            array('namespace' => 'forum','usage' => 'signature'),
            array('namespace' => 'forum','usage' => 'post')
        );
        return $tagUsage;
    } elseif ( $op == 'desc' ) {
        $tagDescription = $LANG_FF_AUTOTAG['desc_forum'];
        return $tagDescription;
    } elseif ($op == 'parse') {
        if ($autotag['parm2'] == '') {
            $autotag['parm2'] = 'here';
        }
        $filelink = '<a href="'.$_CONF['site_url'].'/forum/viewtopic.php?showtopic='.(int) strip_tags($autotag['parm1']).'">'.$autotag['parm2'].'</a>';
        $content = str_replace($autotag['tagstr'],$filelink,$content);
        return $content;
    }
}


/**
* This will put an option for forum admin in the command and control block on moderation.php
*
*/
function plugin_cclabel_forum()
{
    global $_CONF;
    if (SEC_hasRights('forum.edit')) {
        return array('Forum',$_CONF['site_admin_url'].'/plugins/forum/index.php',$_CONF['site_url'].'/forum/images/forum.png');
    }
}

/**
* returns the administrative option for this plugin
*
*/
function plugin_getadminoption_forum()
{
    global $_TABLES, $_CONF;

    if (SEC_hasRights('forum.edit')) {
        $numtopics = DB_getITEM($_TABLES['ff_topic'],"count(*)");
        return array('Forum', $_CONF['site_admin_url'] . '/plugins/forum/index.php', $numtopics);
    }

}

function plugin_user_create_forum ($uid)
{
    global $_TABLES,$_FF_CONF;

    DB_query ("INSERT INTO {$_TABLES['ff_userinfo']} (uid,rating,signature) VALUES (".(int) $uid.",0,'')");
    DB_query ("INSERT INTO {$_TABLES['ff_userprefs']} (uid,topicsperpage,postsperpage) VALUES (".(int)$uid.",".
               (int)$_FF_CONF['show_topics_perpage'].",".(int)$_FF_CONF['show_posts_perpage'].")");
}

function plugin_user_changed_forum( $uid )
{
    global $_TABLES, $_USER;

    $curusername = DB_getItem ($_TABLES['users'], 'username',"uid = $uid");
    DB_query("UPDATE {$_TABLES['ff_topic']} SET name='".DB_escapeString($curusername)."' WHERE uid=".(int) $uid);
    Forum\Like::updateUsername($uid, $curusername);
}

function plugin_user_move_forum($origUID, $destUID)
{
    global $_TABLES;

    $username = DB_getItem($_TABLES['users'], "username", "uid=".(int) $destUID);
    $sql = "UPDATE {$_TABLES['ff_topic']} SET uid=".(int)$destUID.",name='".DB_escapeString($username)."' WHERE uid=".(int) $origUID;
    DB_query($sql,1);
}

/**
* Called if admin deletes a user - Removes any moderator records for user
*/
function plugin_user_delete_forum ($uid)
{
    global $_TABLES;

    $username = DB_getItem($_TABLES['users'], "username", "uid=".(int) $uid);

    DB_query("DELETE FROM {$_TABLES['ff_moderators']} WHERE mod_username='".DB_escapeString($username)."'");
    DB_query("DELETE FROM {$_TABLES['ff_userinfo']} WHERE uid =".(int) $uid);
    DB_query("DELETE FROM {$_TABLES['ff_userprefs']} WHERE uid =".(int) $uid);
    DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE uid =".(int) $uid);
    DB_query("DELETE FROM {$_TABLES['ff_bookmarks']} WHERE uid=".(int) $uid);
    DB_query("DELETE FROM {$_TABLES['ff_rating_assoc']} WHERE user_id = ".(int) $uid);
    DB_query("DELETE FROM {$_TABLES['ff_likes_assoc']} WHERE poster_id = " . (int) $uid);

    DB_query("UPDATE {$_TABLES['ff_likes_assoc']} SET voter_id = 1 WHERE voter_id = ".(int) $uid);
    DB_query("UPDATE {$_TABLES['ff_rating_assoc']} SET voter_id = 1 WHERE voter_id = ".(int) $uid);
    DB_query("UPDATE {$_TABLES['ff_topic']} SET name = 'Anonymous', ip = '', email = '' WHERE uid = ".(int) $uid);
    DB_query("UPDATE {$_TABLES['ff_topic']} SET uid = 1 WHERE uid = ".(int) $uid);
}


function plugin_statssummary_forum() {
    global $_USER, $_FF_CONF, $_CONF, $_TABLES, $LANG_GF00;

    if ( ( COM_isAnonUser() ) && $_FF_CONF['registration_required'] == 1 )  {
        return '';
    }

    // This shows in the summary box
    $total_items = DB_count($_TABLES['ff_topic']);      // Total number of Forum Posts
    $summary_label = $LANG_GF00['statslabel'];
    $retval[] = $summary_label;
    $retval[] = $total_items;
    return $retval;
}

/**
* shows the statistics for the plugin when stats.php is called.
* If $showsitestats is 1 then we are to only print the overall stats in the 'site statistics box'
* otherwise we show the detailed stats for the photo album
*
* @showsitestate        int         Flag to let us know which stats to get
*/
function plugin_showstats_forum($showsitestats)
{
    global $_USER, $_CONF, $_FF_CONF,$_TABLES, $LANG_GF00,$LANG_GF01;

    if ( COM_isAnonUser() && $_FF_CONF['registration_required'] == 1 )  {
        return '';
    }

    $stat_templates = new Template($_CONF['path_layout'] . 'stats');
    $stat_templates->set_file(array(
                    'itemstats'=>'itemstatistics.thtml',
                    'statrow'=>'singlestat.thtml'));
    $retval='';

    if ($showsitestats == 1) {
        // This shows in the summary box
        $total_pages = DB_count($_TABLES['ff_topic']);      // Total number of Forum Posts
        $summary_label = $LANG_GF00['statslabel'];        // Label to display

        $retval = "<table border='0' width='100%' cellspacing='0' cellpadding='0'>";
        $retval .= "<tr><td>$summary_label</td>";
        $retval .= "<td align='right'>" . $total_pages . "&nbsp;&nbsp;</td></tr></table>";
    } else {
        $header_arr = array(
            array('text' => $LANG_GF01['TOPICSUBJECT'], 'field' => 'topicsubject','header_class' => 'stats-header-title'),
            array('text' => $LANG_GF01['VIEWS'], 'field' => 'views','field_class'=>'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_GF00['statsheading1'],
        );
        $result = DB_query("SELECT forum,subject,views,id FROM {$_TABLES['ff_topic']} where pid='0' ORDER BY views desc");
        $nrows  = DB_numRows($result);
        if ($nrows > 0) {
            $displaycount = 0;
            while (list ($forum,$subject, $views,$id) = DB_fetchArray($result)) {
                $forum_id = DB_getItem($_TABLES['ff_topic'],'forum',"id=".(int) $id);
                $grp_id = DB_getItem($_TABLES['ff_forums'],'grp_id',"forum_id=".(int) $forum_id);
                $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id=".(int) $grp_id);
                if (SEC_inGroup($groupname) OR $grp_id == 2) {
                    if ($_FF_CONF['use_censor']) {
                        $subject = COM_checkWords($subject);
                    }
                    $url = $_CONF['site_url']. '/forum/viewtopic.php?showtopic='.$id;
                    $S['topicsubject'] = '<a href="' . $url . '">' . COM_truncate($subject,$_FF_CONF['show_subject_length'],'...') . '</a>';
                    $S['views']  = $views;
                    $data_arr[$displaycount] = $S;
                    $displaycount++;
                    if ($displaycount > 10) {
                        break;
                    }
                }
            }
            $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
        } else {
            $retval .= $LANG_GF00['statsheading3'];
        }
        $header_arr = array(
            array('text' => $LANG_GF01['TOPICSUBJECT'], 'field' => 'topicsubject','header_class' => 'stats-header-title'),
            array('text' => $LANG_GF01['REPLIES'], 'field' => 'replies','field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_GF00['statsheading2'],
        );
        $result = DB_query("SELECT forum,subject,replies,id FROM {$_TABLES['ff_topic']} where pid='0' ORDER BY replies desc");
        $nrows  = DB_numRows($result);
        if ($nrows > 0) {
            $stat_templates->set_var('item_label',$LANG_GF01['TOPICSUBJECT']);
            $stat_templates->set_var('stat_name',$LANG_GF01['REPLIES']);
            $displaycount=0;
            while (list ($forum,$subject, $replies,$id) = DB_fetchArray($result)) {
                $forum_id = DB_getItem($_TABLES['ff_topic'],'forum',"id=".(int) $id);
                $grp_id = DB_getItem($_TABLES['ff_forums'],'grp_id',"forum_id=".(int) $forum_id);
                $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id=".(int) $grp_id);
                if (SEC_inGroup($groupname) OR $grp_id == 2) {
                    if ($_FF_CONF['use_censor']) {
                        $subject = COM_checkWords($subject);
                    }
                    $url = $_CONF['site_url']. '/forum/viewtopic.php?showtopic='.$id;
                    $S['topicsubject'] = '<a href="' . $url . '">' . COM_truncate($subject,$_FF_CONF['show_subject_length'],'...') . '</a>';
                    $S['replies']  = $replies;
                    $data_arr[$displaycount] = $S;
                    $displaycount++;
                    if ($displaycount > 10) {
                        break;
                    }
                }
            }
            $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
        } else {
            $retval .= $LANG_GF00['statsheading3'];
        }
    }
    return $retval;
}

function plugin_getheadercss_forum() {
    global $_CONF;

    $styles = array();

    if ( @file_exists($_CONF['path_layout'].'plugins/forum/style.css') ) {
        $styles[] = $_CONF['path_layout'].'plugins/forum/style.css';
    } else if ( @file_exists($_CONF['path'] . 'plugins/forum/css/custom/style.css') ) {
        $styles[] = $_CONF['path'] . 'plugins/forum/css/custom/style.css';
    } else {
        $styles[] = $_CONF['path'] . 'plugins/forum/css/style.css';
    }
    return($styles);
}

function plugin_getheaderjs_forum() {
    global $_CONF, $_SYSTEM;

    $js = array();

    return($js);
}

/**
* glFusion is asking us to provide any new items that show up in the type drop-down
* on search.php.  Let's let users search the Filelistings in the Filemgmt Plugin
*
*/

function plugin_searchtypes_forum()
{
    global $_USER, $_FF_CONF, $LANG_GF00;

    if ( COM_isAnonUser()  && $_FF_CONF['registration_required'] == 1 )  {
        return '';
    }

    $tmp['forum'] = $LANG_GF00['searchlabel'];
    return $tmp;
}

/**
* this searches for files matching the user query and returns an array of
* for the header and table rows back to search.php where it will be formated and
* printed
*
* @query            string          Keywords user is looking for
* @datestart        date/time       Start date to get results for
* @dateend          date/time       End date to get results for
* @topic            string          The topic they were searching in
* @type             string          Type of items they are searching
* @author           string          Get all results by this author
*
*/

function plugin_dopluginsearch_forum($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $LANG_GF00, $LANG_GF01, $_USER, $_TABLES, $_CONF, $_FF_CONF;

    if ( COM_isAnonUser() && $_FF_CONF['registration_required'] == 1 )  {
        return;
    }

    $query = trim(DB_escapeString($query));

    $sql   = "SELECT id,subject AS title,comment AS description,date,uid,views AS hits, CONCAT('/forum/viewtopic.php?showtopic=',id,'&amp;topic=',id,'#',id) AS url ";
    $sql  .= "FROM {$_TABLES['ff_topic']} t ";
    $sql  .= "LEFT JOIN {$_TABLES['ff_forums']} f ON t.forum=f.forum_id ";
    $sql  .= forum_buildSearchAccessSql('WHERE');

    if (!empty ($author))
        $sql .= "AND (uid = '$author') ";

    $search = new SearchCriteria('forum', 'Forum');
    $columns = array('comment','subject');
    $sql .= $search->getDateRangeSQL('AND', 'date', $datestart, $dateend);
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(4);

    return $search;
}

// Common function used to build group access SQL
function forum_buildSearchAccessSql($clause='AND') {
    global $_TABLES,$_USER;

    if (!COM_isAnonUser()) {
        $uid = $_USER['uid'];
    } else {
        $uid = 1;
    }

    $_GROUPS = SEC_getUserGroups($uid);
    $groupsql = '';
    if (count($_GROUPS) == 1) {
        $groupsql .= " $clause grp_id = '" . current($_GROUPS) ."'";
    } else {
        $groupsql .= " $clause grp_id IN (" . implode(',',array_values($_GROUPS)) .")";
    }
    return $groupsql;
}

/* RSS FEED Related API functions */

function plugin_getfeednames_forum ()
{
    global $_TABLES;

    $feeds = array ();

    $feeds[] = array ('id' => '0', 'name' => 'ALL FORUMS');

    $asql = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($asql)) {
        $firstforum=true;
        $bsql = DB_query("SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat='$A[id]' ORDER BY forum_order ASC");
        while($B = DB_fetchArray($bsql)) {
            if ( $firstforum ) {
                $feeds[] = array('id' => $A['id'] + 20000, 'name' => $A['cat_name']);
            }
            $firstforum=false;
            $feeds[] = array('id' => $B['forum_id'], 'name' => '&nbsp;&nbsp;&nbsp;&#187;&nbsp;' . $B['forum_name']);
        }
    }
    return $feeds;
}

function forum_buildFeedsSql ($forumID, $limits)
{
    global $pi_version, $_FF_CONF;

    if ( !COM_checkVersion($pi_version, $_FF_CONF['pi_version']) ) {
        return '';
    }

    $where = '';

    $groups = array ();
    $usergroups = SEC_getUserGroups(1);
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    if ( $forumID > 20000 ) {
        // pulling all forms for a category
        $catId = $forumID - 20000;
        $where = " WHERE forum_cat=" . $catId . " AND no_newposts=0 AND forum.grp_id IN ($grouplist) ";
    } else {
        if ($forumID > 0) {
            $where = " WHERE forum=".(int) $forumID." AND no_newposts=0 AND forum.grp_id IN ($grouplist) ";
        } else {
            $where = " WHERE no_newposts=0 AND forum.grp_id IN ($grouplist) ";
        }
    }

    $limitsql = '';
    if (!empty ($limits)) {
        if (substr ($limits, -1) == 'h') { // last xx hours
            $limitsql = '';
            $hours = substr ($limits, 0, -1);
            if (!empty ($where)) {
                $where .= ' AND ';
            }
            $where .= "date >= DATE_SUB('".$_CONF['_now']->toMySQL(true)."',INTERVAL $hours HOUR) ORDER BY date DESC";
        } else {
            $limitsql = ' ORDER BY date DESC LIMIT ' . $limits;
        }
    } else {
        $limitsql = ' ORDER BY date DESC LIMIT 10';
    }

    $sql = $where . $limitsql;

    return $sql;
}

function plugin_getfeedcontent_forum ($feed, &$link, &$update)
{
    global $_CONF, $_TABLES, $_FF_CONF, $LANG_GF01, $pi_version;

    $content = array();
    $lids = array();
    $ids = array();

    if ( !COM_checkVersion($pi_version, $_FF_CONF['pi_version']) ) {
        return '';
    }

    $allow_smilies = $_FF_CONF['allow_smilies'];
    $_FF_CONF['allow_smilies']=0;

    USES_forum_format();

    $result = DB_query ("SELECT topic,limits,content_length FROM {$_TABLES['syndication']} WHERE fid=".(int)$feed);
    $F = DB_fetchArray ($result);

    $sql = "SELECT * ";
    $sql .= "FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_forums']} forum ON topic.forum=forum.forum_id ";
    $sql .= forum_buildFeedsSql ($F['topic'], $F['limits']);
    $result = DB_query ($sql);

    while ( $A=DB_fetchArray($result) ) {
        $preface = '';
        if ($F['content_length'] == 0) {
            $comment = '';
        } elseif ($F['content_length'] > 1) {
            $comment = COM_truncate($A['comment'],$F['content_length']);
        } else {
            $comment = $A['comment'];
            $preface = $LANG_GF01['BY'] . ' ';
            if ( $A['uid'] > 1 ) {
                $preface .= '<a href="' . $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid'] . '">' . $A['name'] . '</a>';
            } else {
                $preface .= $A['name'];
            }
            $preface .= '<br /><br />';
        }
        $comment = FF_formatTextBlock($comment,$A['postmode'],'preview',$A['status']);

        // we don't have a stylesheet in the news feed, so replace our div with the style...
        $comment = str_replace('<div class="quotemain">','<div style="border: 1px dotted #000;border-left: 4px solid #8394B2;color:#465584;  padding: 4px;  margin: 5px auto 8px auto;">',$comment);

//        $desc = $preface . $comment;
        $desc = $comment;
        $guid = $_CONF['site_url'].'/forum/viewtopic.php?topic='.$A['id'];
        $link = $_CONF['site_url'].'/forum/viewtopic.php?topic='.$A['id'].'#'.$A['id'];

        if ($_FF_CONF['use_censor']) {
            $A['subject'] = COM_checkWords($A['subject']);
        }
        $content[] = array ('author'    => $A['name'],
                            'title'     => $A['forum_name'] . ' :: ' . $A['subject'],
                            'summary'   => $desc,
                            'text'      => $desc,
                            'link'      => $link,
                            'uid'       => $A['uid'],
                            'date'      => $A['date'],
                            'format'    => $A['postmode'],
                            'guid'      => $guid,
                           );
        $ids[] = $A['id'];
    }

    if ($F['topic'] == 0) {
        $link = $_CONF['site_url'].'/forum/index.php';
    } else {
        $link = $_CONF['site_url'].'/forum/index.php?forum='.$F['topic'];
    }

    if (count($ids) > 0 ) {
        $update = implode (',', $ids);
    }

    $_FF_CONF['allow_smilies'] = $allow_smilies;

    return $content;
}

function plugin_feedupdatecheck_forum ($feed, $topic, $update_data, $limit)
{
    global $_TABLES;

    $sql = "SELECT id,forum.grp_id FROM {$_TABLES['ff_topic']} topic " .
           "LEFT JOIN {$_TABLES['ff_forums']} forum ON topic.forum=forum.forum_id " .
           forum_buildFeedsSql ($topic, $limit);
    $result = DB_query ($sql);
    $num = DB_numRows ($result);

    $ids = array ();
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $ids[] = $A['id'];
    }
    $current = implode (',', $ids);

    $rc = ( $current != $update_data ) ? false : true;
    return $rc;
}

/* End of RSS Feed Related Functions */



/**
* Called by the plugin Editor to display the current plugin code version
* This may be different then the version installed and registered currently.
* If newer then you may want to run the update
*/
function plugin_chkVersion_forum() {
    global $_FF_CONF;

    return $_FF_CONF['pi_version'] ;
}


/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_forum() {
    global $_CONF, $_TABLES,$_FF_CONF;

    require_once $_CONF['path'] . 'plugins/forum/upgrade.php';

    return forum_upgrade();
}


//  ------- GENERAL FORUM FUNCTIONS --------

function forum_chkUsercanPost() {
    global $_CONF,$_FF_CONF,$LANG_GF00, $LANG_GF01, $LANG_GF02, $_TABLES, $_USER;

    $display = '';

    //Check is anonymous users can post
    if ($_FF_CONF['registered_to_post'] && COM_isAnonUser() ){
        $display  = COM_siteHeader();
        $display .= SEC_loginRequiredForm();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }

    // Check if IP of user has been banned
    $isBanned = DB_count($_TABLES['ff_banned_ip'],'host_ip',DB_escapeString($_SERVER['REAL_ADDR']));
    if ( $isBanned > 0 ) {
        $display .= FF_siteHeader();
        $display .= COM_startBlock($LANG_GF00['access_denied']);
        $display .= $LANG_GF02['msg14'];
        $display .= sprintf ($LANG_GF02['msg15'],$_CONF['site_mail']);
        $display .= COM_endBlock();
        $display .= FF_siteFooter();
        echo $display;
        exit();
    }
}

function forum_chkUsercanAccess($secure=false) {
    global $_CONF, $_FF_CONF, $LANG_GF01, $LANG_GF02;

    $display = '';

    if ($_FF_CONF['registration_required'] && COM_isAnonUser() ) {
        $display  = FF_siteHeader();
        $display .= SEC_loginRequiredForm();
        $display .= FF_siteFooter();
        echo $display;
        exit;
    } elseif ( $secure AND COM_isAnonUser() ) {
        $display = FF_siteHeader();
        $display .= '<br />';
        $display .= FF_BlockMessage($LANG_GF01['ACCESSERROR'],$LANG_GF02['msg83'],false);
        $display .= FF_siteFooter();
        echo $display;
        exit;
    }
}


function forum_xchsmilies($message,$reverse=false) {

    $search = array(":D", ":)", ":(", "8O", ":?", "B)", ":cool:", ":lol:", ":x", ":P" ,":oops:", ":o",":cry:",
        ":evil:", ":twisted:", ":roll:", ";)", ":wink:", ":!:", ":question:", ":idea:", ":arrow:", ":|",
        ":mrgreen:",":mrt:",":love:",":cat:");
    $replace = array(
    '<img style="vertical-align:middle;" src="images/smilies/biggrin.gif" alt="Big Grin"/>',
    '<img style="vertical-align:middle;" src="images/smilies/smile.gif" alt="Smile"/>',
    '<img style="vertical-align:middle;" src="images/smilies/frown.gif" alt="Frown"/>',
    '<img style="vertical-align:middle;" src="images/smilies/eek.gif" alt="Eek!"/>',
    '<img style="vertical-align:middle;" src="images/smilies/confused.gif" alt="Confused"/>',
    '<img style="vertical-align:middle;" src="images/smilies/cool.gif" alt="Cool"/>',
    '<img style="vertical-align:middle;" src="images/smilies/cool.gif" alt="Cool"/>',
    '<img style="vertical-align:middle;" src="images/smilies/lol.gif" alt="Laughing Out Loud"/>',
    '<img style="vertical-align:middle;" src="images/smilies/mad.gif" alt="Angry"/>',
    '<img style="vertical-align:middle;" src="images/smilies/razz.gif" alt="Razz"/>',
    '<img style="vertical-align:middle;" src="images/smilies/redface.gif" alt="Oops!"/>',
    '<img style="vertical-align:middle;" src="images/smilies/surprised.gif" alt="Surprised!"/>',
    '<img style="vertical-align:middle;" src="images/smilies/cry.gif" alt="Cry"/>',
    '<img style="vertical-align:middle;" src="images/smilies/evil.gif" alt="Evil"/>',
    '<img style="vertical-align:middle;" src="images/smilies/twisted.gif" alt="Twisted Evil"/>',
    '<img style="vertical-align:middle;" src="images/smilies/rolleyes.gif" alt="Rolling Eyes"/>',
    '<img style="vertical-align:middle;" src="images/smilies/wink.gif" alt="Wink"/>',
    '<img style="vertical-align:middle;" src="images/smilies/wink.gif" alt="Wink"/>',
    '<img style="vertical-align:middle;" src="images/smilies/exclaim.gif" alt="Exclaimation"/>',
    '<img style="vertical-align:middle;" src="images/smilies/question.gif" alt="Question"/>',
    '<img style="vertical-align:middle;" src="images/smilies/idea.gif" alt="Idea"/>',
    '<img style="vertical-align:middle;" src="images/smilies/arrow.gif" alt="Arrow"/>',
    '<img style="vertical-align:middle;" src="images/smilies/neutral.gif" alt="Neutral"/>',
    '<img style="vertical-align:middle;" src="images/smilies/mrgreen.gif" alt="Mr. Green"/>');

    if (!$reverse) {
        $message = str_replace($search, $replace, $message);
/*
        $counter = 0;
        foreach ( $search AS $emoticon) {
            if ( isset($replace[$counter])) {
                $message = preg_replace("#(?<=\s|^)" . preg_quote($emoticon,"#") . "#", $replace[$counter], $message);
            }
            $counter++;
        }
*/
    } else {
        $message = str_replace($replace, $search, $message);
    }
    return $message;
}


function FF_statusMessage($message,$url,$prompt,$post=false,$forum='',$autorefresh = 1) {
    global $_CONF,$_TABLES,$_USER,$LANG_GF02,$_FF_CONF;

    COM_setMsg($message,'info',0);
    if ( $url == '') {
        if ( $forum != '' ) {
            $url = $_CONF['site_url'].'/forum/index.php?forum='.(int) $forum;
        } else {
            $url = $_CONF['site_url'].'/forum/index.php';
        }
    }
    echo COM_refresh($url);
    exit;

    // legacy code - no longer used...

    $statusmsg = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $statusmsg->set_file ('statusmsg','statusmessage.thtml');
    $statusmsg->set_var (array(
            'heading'   => $LANG_GF02['StatusHeading'],
            'message'   => $message));
    if ($_FF_CONF['use_autorefresh'] == "0" || $autorefresh == false ) {
        $statusmsg->set_var ('prompt',sprintf($LANG_GF02['msg139'],$prompt,$url));
        $statusmsg->set_var('autoredirect_javascript','');
    } else {
        $autorefresh_delay = $_FF_CONF['autorefresh_delay'] * 1000;
        $statusmsg->set_var('autoredirect_javascript',
            "\n\n<script language=\"javascript1.1\">\nwindow.setTimeout(\"window.location.href='{$url}'\",{$autorefresh_delay});\n</script>\n\n");
        if ($post) {
            $forumindex = 'index.php?forum=' . $forum;
            $prompt = sprintf($LANG_GF02['msg162'],$forumindex,$url);
        } else {
            $prompt = sprintf($LANG_GF02['msg141'],$url);
        }
        $statusmsg->set_var ('prompt',$prompt);
    }
    $statusmsg->parse ('output', 'statusmsg');

    $retval = $statusmsg->finish ($statusmsg->get_var('output'));

    return $retval;
}

function phpblock_forum_newposts() {
    global $_CONF, $_SYSTEM, $_USER, $_TABLES, $LANG_GF01, $LANG_GF02, $mode, $_FF_CONF,$pi_version;

    if ( !COM_checkVersion($pi_version, $_FF_CONF['pi_version']) ) {
        return '';
    }

    if ( COM_isAnonUser() && $_FF_CONF['registration_required'] == 1 )  {
        return;
    }
    $post_limit = $_FF_CONF['sideblock_numposts'];
    if ( isset($_GET['mode']) ) {
        $mode = COM_applyFilter($_GET['mode']);
    } else {
        $mode = '';
    }
    if ( isset($_GET['order']) ) {
        $order = COM_applyFilter($_GET['order']);
    } else {
        $order = '';
    }
    if (DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name="forum"')) {
        // Check if user has clicked on a sort option in the block
        if ($mode == 'forumblock') {
            if (($order == 'new') OR ($order == '')) {
                $view_modemsg = sprintf($LANG_GF02['msg74'],$post_limit);
                if ($_FF_CONF['sb_latestpostonly']) {
                    $orderby = 'lastupdated';
                } else {
                    $orderby = 'date';
                }
            } elseif ($order == 'views') {
                $view_modemsg = sprintf($LANG_GF02['msg75'],$post_limit);
                $orderby = 'views';
            } elseif ($order == 'posts') {
                $view_modemsg = sprintf($LANG_GF02['msg76'],$post_limit);
                $orderby = 'replies';
            }
        } else {
            $view_modemsg = sprintf($LANG_GF02['msg74'],$post_limit);
                if ($_FF_CONF['sb_latestpostonly']) {
                    $orderby = 'lastupdated';
                } else {
                    $orderby = 'date';
                }
        }

        $block = new Template($_CONF['path'] . 'plugins/forum/templates/blocks/');
        $block->set_file (array ('block'=>'latestpostsblock.thtml','topicrecord' => 'block_displayline.thtml'));
        $block->set_var ('phpself', $_CONF['site_url'] .'/index.php');
        $block->set_var ('LANG_ORDERBY', $LANG_GF01['ORDERBY']);
        $block->set_var ('LANG_NEW', $LANG_GF01['NEW']);
        $block->set_var ('LANG_VIEWS', $LANG_GF01['VIEWS']);
        $block->set_var ('LANG_POSTS', $LANG_GF01['POSTS']);
        $block->set_var ('view_modemsg', $view_modemsg);

        // Select SQL to either show the latest posts even if they are for the same topic
        // Or show only the lastest post for each of the last updated topics
        if ($_FF_CONF['sb_latestpostonly']) {
            $sql = "SELECT a.id,a.forum,a.name,a.date,a.subject,a.replies,a.views,a.uid,a.pid FROM {$_TABLES['ff_topic']} a ";
            $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id ";
            $sql .= "WHERE a.pid=0 AND b.no_newposts = 0 ORDER BY $orderby DESC";
            $result = DB_query($sql);
        } else {
            $sql = "SELECT a.id,a.forum,a.name,a.date,a.subject,a.replies,a.views,a.uid,a.pid FROM {$_TABLES['ff_topic']} a ";
            $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id ";
            $sql .= "WHERE b.no_newposts = 0 ORDER BY $orderby DESC";
            $result = DB_query($sql);
        }

        $nrows  = DB_numrows($result);
        if ($nrows == 0) {
            return;
        }
        $onetwo = 1;
        $displaycount = 0;

        for ($i =1; $i <= $nrows; $i++) {

            $A = DB_fetchArray($result,false);
            if (trim($A['subject']) == '') {
                $A['subject'] = DB_getItem($_TABLES['ff_topic'],'subject',"id='{$A['pid']}'");
            }

            $fresult = DB_query ("SELECT grp_id, no_newposts FROM {$_TABLES['ff_forums']} WHERE forum_id='{$A['forum']}'");
            list ($grp_id, $no_newposts) = DB_fetchArray ($fresult);
            $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");

            if ($no_newposts==0 AND (SEC_inGroup($groupname) OR $grp_id == 2)) {
                $displaycount++;
                if ($_FF_CONF['use_censor']) {
                    $A['subject'] = COM_checkWords($A['subject']);
                }
                $fullsubject = $A['subject'];
                $A['subject'] = COM_truncate( $A['subject'], $_FF_CONF['sb_subject_size'], '...', false );

                if ($A['replies'] > 0 AND $_FF_CONF['sb_latestpostonly']) {
                    $lastreplySQL = DB_query("SELECT uid,name,date from {$_TABLES['ff_topic']} WHERE pid={$A['id']} ORDER BY date DESC LIMIT 1");
                    list ($uid,$postername,$postdate) = DB_fetchArray($lastreplySQL);
                } else {
                    $postername = $A['name'];
                    $postdate = $A['date'];
                    $uid = $A['uid'];
                }
                if ($uid > 1) {
                    $postername = COM_getDisplayName($uid);
                    $profilelink = "<a href='{$_CONF['site_url']}/users.php?mode=profile&amp;uid={$uid}'>";
                } else {
                    $profilelink = '';
                }
                $block->set_var ('css_id', $onetwo);
                $block->set_var ('img_dir', $_CONF['site_url'] . '/forum/images');
                $block->set_var ('forum_id', $A['forum']);
                $block->set_var ('forum_name', DB_getItem($_TABLES['ff_forums'],'forum_name',"forum_id='{$A['forum']}'"));
                $block->set_var ('topic_id', $A['id']);
                $block->set_var ('topic_subject', $A['subject']);
                $block->set_var ('fullsubject', $fullsubject);
                $block->set_var ('topic_id', $A['id']);
                $block->set_var ('profilelink', $profilelink);
                $block->set_var ('user_name', $postername);
                $block->set_var ('LANG_BY', $LANG_GF01['BY']);
                $block->set_var ('LANG_ON', $LANG_GF01['ON']);
                $block->set_var ('LANG_VIEWS', $LANG_GF01['VIEWS']);
                $block->set_var ('LANG_REPLIES', $LANG_GF01['REPLIES']);
                $block->set_var ('views', $A['views']);
                $block->set_var ('replies', $A['replies']);
                $dt = new Date($postdate,$_USER['tzid']);
                if ($_FF_CONF['allow_user_dateformat']) {
                    $date = $dt->format($dt->getUserFormat(),true);
                    $block->set_var ('date', $date);
                } else {
                    $tdate = $dt->format($_CONF['date'],true);
                    $block->set_var ('date', $tdate);
                }

               $block->parse ('block_records', 'topicrecord',true);

                if ($onetwo == 1) {
                    $onetwo = 2;
                } else {
                    $onetwo = 1;
                }
                if ($displaycount >= $_FF_CONF['sideblock_numposts']) {
                    break;
                }
            }
        }

        if ($displaycount == 0) {
            return;
        }

        $block->parse ('output', 'block');
        $retval = $block->finish($block->get_var('output'));
        return $retval;
    } else {
        return;
    }
}

function stripBBCode($text_to_search) {
	// Replace paragraph tags
	$pattern = '[p]';
	$replace = '<br>';
	$text_to_search = str_replace($pattern, $replace, $text_to_search);
	$pattern = '[/p]';
	$replace = '<br>';
	$text_to_search = str_replace($pattern, $replace, $text_to_search);

	$pattern = '[QUOTE]';
	$replace = "<span style='font-style:italic;'>";
	$replace = "<i>";
	$text_to_search = str_replace($pattern, $replace, $text_to_search);

	$pattern = '[/QUOTE]';
	$replace = '</span>';
	$replace = '</i>';
	$text_to_search = str_replace($pattern, $replace, $text_to_search);

	// Replace everything else with nothing
	$pattern = '|[[\/\!]*?[^\[\]]*?]|si';
	$replace = '';
	$text_to_search = preg_replace($pattern, $replace, $text_to_search);

	return $text_to_search;
}


/**
* Display latest forum posts in the center block.
*
* @param   where   int      where the block will be displayed (0..2)
* @param   page    int      page number
* @param   topic   string   topic ID
* @return          string   HTML for the center blcok (can be empty)
*/
function plugin_centerblock_forum ($where = 1, $page = 1, $topic = '')
{
    global $_CONF, $_USER, $_TABLES, $LANG_GF01,$_FF_CONF,
           $LANG_GF02, $mode, $order,$pi_version, $_GROUPS,$_SYSTEM;

    if ( $_FF_CONF['centerblock_where'] == 0 && $where == 0 ) {
        require_once $_CONF['path'].'plugins/forum/include/forum_index.php';
        forum_index();
    }

    if ( !COM_checkVersion($pi_version, $_FF_CONF['pi_version']) ) {
        return '';
    }

    if ( COM_isAnonUser() && $_FF_CONF['registration_required'] == 1 )  {
        return;
    }

    if ( $_FF_CONF['centerblock_where'] != $where && $where != CENTERBLOCK_FORCE) {
        return;
    }

    $dt = new Date('now',$_USER['tzid']);
    USES_forum_format();

    $retval = '';

    $cb_enable   = $_FF_CONF['show_centerblock'];
    $cb_homepage = $_FF_CONF['centerblock_homepage'];
    $cb_where    = $_FF_CONF['centerblock_where'];

    // If enabled only for homepage and this is not page 1 or a topic page,
    // then set disable flag
    if ($cb_homepage == 1 AND ($page > 1 OR !empty ($topic))) {
        $cb_enable = 0;
    } elseif ($cb_homepage == 0 and $page > 1) {
        $cb_where = CENTERBLOCK_TOP;
    }

    if (($cb_enable && ($cb_where == $where)) || $where == CENTERBLOCK_FORCE) {
        $cacheInstance = 'forumcb__' . CACHE_security_hash() . '__' . $_USER['theme'];

        $retval = '';
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            return $retval;
        }

        $block = new Template($_CONF['path'] . 'plugins/forum/templates/blocks/');
        $block->set_file ('block','centerblock.thtml');
        $block->set_var (array(
                'phpself'       => $_CONF['site_url'] .'/index.php',
                'startblock'    => COM_startBlock($LANG_GF02['msg170']),
                'endblock'      => COM_endBlock(),
                'LANG_title'    => $LANG_GF02['msg170'],
                'LANG_FORUM'    => $LANG_GF01['FORUM'],
                'LANG_TOPIC'    => $LANG_GF01['TOPIC'],
                'LANG_LASTPOST' => $LANG_GF01['LASTPOST'],
                'LANG_viewlastpost' => $LANG_GF02['msg160'],
                'LANG_forumjump'    => $LANG_GF02['msg195'],
                'tooltip_style' => COM_getToolTipStyle()
        ));
        $groups = array ();
        $usergroups = SEC_getUserGroups();
        foreach ($usergroups as $group) {
            $groups[] = $group;
        }
        $grouplist = implode(',',$groups);

        $sql  = "SELECT a.id, a.forum, a.name, a.date, a.lastupdated, a.last_reply_rec, a.subject, ";
        $sql .= "a.comment, a.uid, a.name, a.pid, a.replies, a.views, a.status, b.forum_name  ";
        $sql .= "FROM {$_TABLES['ff_topic']} a ";
        $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id ";
        $sql .= "WHERE pid=0 AND b.grp_id IN ($grouplist) AND b.no_newposts = 0 ";
        $sql .= "ORDER BY lastupdated DESC LIMIT {$_FF_CONF['centerblock_numposts']}";
        $result = DB_query ($sql);

        if (DB_numRows($result) == 0) {
            return;
        }
        $cssid = 0;
        $block->set_block('block', 'blockrow', 'brow');
        while ( ($A = DB_fetchArray ($result)) != NULL ) {
            $A['subject'] = @htmlspecialchars($A['subject'],ENT_COMPAT,COM_getEncodingt());
            if ($_FF_CONF['use_censor']) {
                $A['subject'] = COM_checkWords($A['subject']);
                $A['comment'] = COM_checkWords($A['comment']);
            }
            $fullsubject = "{$A['subject']}\n{$LANG_GF01['POSTEDBY']}:{$A['name']}{$LANG_GF01['VIEWS']}:{$A['views']}, {$LANG_GF01['REPLIES']}:{$A['replies']}";
            $A['subject'] = COM_truncate( $A['subject'], $_FF_CONF['cb_subject_size'], '...',false );
            $dt = new Date($A['date'],$_USER['tzid']);
            $firstdate = $dt->format($_CONF['daytime'],true);
            $dt = new Date($A['lastupdated'],$_USER['tzid']);
            $lastdate = $dt->format($_CONF['daytime'],true);

            $topicinfo = "{$LANG_GF01['STARTEDBY']} " . $A['name'].', ';
            $topicinfo .= "{$firstdate}&lt;br/&gt;{$LANG_GF01['VIEWS']}: {$A['views']}, {$LANG_GF01['REPLIES']}: {$A['replies']}&lt;br/&gt;";

            if (empty ($A['last_reply_rec']) || $A['last_reply_rec'] < 1) {
                $lastid = $A['id'];
                $testText = FF_formatTextBlock($A['comment'],'text','noquote',$A['status']);
                $testText = strip_tags($testText);
                $html2txt = new Html2Text\Html2Text($testText,false);
                $testText = trim($html2txt->get_text());
                $shortComment = '';
                $commentArray = explode(' ',$testText);
                $wordCount = count($commentArray);
                $lengthCount = 0;
                $tailString = '';
                foreach ($commentArray AS $word) {
                    $lengthCount = $lengthCount + strlen($word);
                    $shortComment .= $word.' ';
                    if ( $lengthCount >= $_FF_CONF['contentinfo_numchars'] ) {
                        $tailString = '...';
                        break;
                    }
                }
                $shortComment = trim($shortComment).$tailString;
                $lastpostinfo = @htmlspecialchars(preg_replace('#\r?\n#',' ',strip_tags($shortComment)),ENT_COMPAT,COM_getEncodingt());
                $lastpostinfo = stripBBCode($lastpostinfo);
            } else {
                $qlreply = DB_query("SELECT id,uid,name,comment FROM {$_TABLES['ff_topic']} WHERE id={$A['last_reply_rec']}");
                $B = DB_fetchArray($qlreply);
                $lastid = $B['id'];
                $lastcomment = $B['comment'];
                if ($B['uid'] > 1) {
                    $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],COM_getDisplayName($B['uid']));
                } else {
                    $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],$B['name']);
                }
                $testText = FF_formatTextBlock($B['comment'],'text','noquote',$A['status']);
                $testText = strip_tags($testText);
                $html2txt = new Html2Text\Html2Text($testText,false);
                $testText = trim($html2txt->get_text());
                $shortComment = '';
                $commentArray = explode(' ',$testText);
                $wordCount = count($commentArray);
                $lengthCount = 0;
                $tailString = '';
                foreach ($commentArray AS $word) {
                    $lengthCount = $lengthCount + strlen($word);
                    $shortComment .= $word.' ';
                    if ( $lengthCount >= $_FF_CONF['contentinfo_numchars'] ) {
                        $tailString = '...';
                        break;
                    }
                }
                $shortComment = trim($shortComment).$tailString;
                $lastpostinfo = @htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags($shortComment)),ENT_COMPAT,COM_getEncodingt());
                $lastpostinfo = stripBBCode($lastpostinfo);
            }
            $cssid = ($cssid ==1) ? 2: 1;
            $block->set_var (array(
                    'lastpostinfo'  => $lastpostinfo,
                    'lastpostid'    => $lastid,
                    'topicinfo'     => $topicinfo,
                    'cssid'         => $cssid,
                    'forum_id'      => $A['forum'],
                    'forum_name'    => $A['forum_name'],
                    'topic_id'      => $A['id'],
                    'topic_subject' => $A['subject'],
                    'fullsubject'   => $fullsubject,
                    'views'         => $A['views'],
                    'replies'       => $A['replies'],
                    'date'          => $lastdate,
                    'lastpostby'    => $A['name']
            ));
            $block->parse('brow', 'blockrow',true);
        }
        $block->parse ('output', 'block');
        $retval .= $block->finish ($block->get_var ('output'));

        CACHE_create_instance($cacheInstance, $retval, 0);
    }
    return $retval;
}

$LANG_MYACCOUNT['pe_forumprefs'] = $LANG_GF92['forum_prefs'];

function plugin_profileedit_forum($uid,$panel,$fieldset)
{
    global $_CONF, $_FF_CONF, $FF_userprefs, $_TABLES, $_USER, $LANG_GF01,$LANG_GF02,$LANG_GF92;

    $retval = '';

    if ( COM_isAnonUser() ) {
        return $retval;
    }

    if ( $panel == 'userinfo' && $fieldset == 'personalinfo' && $_FF_CONF['bbcode_signature'] ) {
        $signature = DB_getItem($_TABLES['ff_userinfo'],'signature','uid='.(int) $uid);
        return FF_sigEditorProfile($signature);
    }

    if ($panel != '' || $fieldset != '' ) {
        return $retval;
    }

    // Get user specific settings from database
    $result = DB_query("SELECT * FROM {$_TABLES['ff_userprefs']} WHERE uid=".(int) $uid);
    $nrows = DB_numRows($result);
    if ($nrows == 0) {
        // Insert a new blank record. Defaults are set in SQL Defintion for table.
        DB_query("INSERT INTO {$_TABLES['ff_userprefs']} (uid,topicsperpage,postsperpage) VALUES (".(int)$uid.",".
            (int)$FF_userprefs['topicsperpage'].",".(int)$FF_userprefs['postsperpage'].")");
        $result = DB_query("SELECT * FROM {$_TABLES['ff_userprefs']} WHERE uid=".(int) $uid);
    }

    $A = DB_fetchArray($result);

    $viewanonposts_checked = $A['viewanonposts'] == 1 ? ' checked="checked"' : '';
    $alwaysnotify_checked  = $A['alwaysnotify']  == 1 ? ' checked="checked"' : '';
    $enablenotify_checked  = $A['enablenotify']  == 1 ? ' checked="checked"' : '';
    $notifyonce_checked    = $A['notify_once']   == 1 ? ' checked="checked"' : '';
    $showiframe_checked    = $A['showiframe']    == 1 ? ' checked="checked"' : '';
    $notifyfull_checked    = $A['notify_full']   == 1 ? ' checked="checked"' : '';

    $usersettings = new Template($_CONF['path'] . 'plugins/forum/templates/userprefs');
    $usersettings->set_file ('usersettings','profile_user_settings.thtml');
    $usersettings->set_var (array(
            'LANG_GF92[topicspp]'   => $LANG_GF92['topicspp'],
            'topicsperpage'         => $A['topicsperpage'],
            'LANG_GF92[postspp]'    => $LANG_GF92['postspp'],
            'postsperpage'          => $A['postsperpage'],
            'LANG_GF02[msg130]'     => $LANG_GF02['msg130'],
            'LANG_GF02[msg131]'     => $LANG_GF02['msg131'],
            'viewanonposts'         => $A['viewanonposts'],
            'viewanonposts_checked' => $viewanonposts_checked,
            'LANG_GF02[msg167]'     => $LANG_GF02['msg167'],
            'LANG_GF02[msg168]'     => $LANG_GF02['msg168'],
            'enablenotify'          => $A['enablenotify'],
            'emailnotify_checked'   => $enablenotify_checked,
            'LANG_GF02[msg184]'     => $LANG_GF02['msg184'],
            'LANG_GF02[msg185]'     => $LANG_GF02['msg185'],
            'notifyonce_checked'    => $notifyonce_checked,
            'LANG_GF02[msg132]'     => $LANG_GF02['msg132'],
            'LANG_GF02[msg133]'     => $LANG_GF02['msg133'],
            'alwaysnotify'          => $A['alwaysnotify'],
            'alwaysnotify_checked'  => $alwaysnotify_checked,
            'lang_notify_full'      => $LANG_GF02['notify_full'],
            'notifyfull'            => $A['notify_full'],
            'notifyfull_checked'    => $notifyfull_checked,
            'LANG_GF92[showiframe]' => $LANG_GF92['showiframe'],
            'LANG_GF92[showiframedscp]' => $LANG_GF92['showiframedscp'],
            'showiframe'            => $A['showiframe'],
            'showiframe_checked'    => $showiframe_checked,
            'lang_forum_prefs'      => $LANG_GF92['forum_prefs'],
            'lang_forumprefs_help_title'    => $LANG_GF92['forumprefs_help_title'],
            'lang_forumprefs_help'  => $LANG_GF92['forumprefs_help'],
            'lang_topic_order'      => $LANG_GF92['topic_order']
    ));
    $to_select =  '<select name="topic_order" id="topic_order">';
    $to_select .= '<option value="ASC" '.($A['topic_order'] == 'ASC' ? ' selected="selected"' : '').'>'.$LANG_GF92['ascending'].'</option>';
    $to_select .= '<option value="DESC" '.($A['topic_order'] == 'DESC' ? ' selected="selected"' : '').'>'.$LANG_GF92['descending'].'</option>';
    $to_select .= '</select>';
    $usersettings->set_var('topic_order_select',$to_select);

    if ( $_FF_CONF['allow_wysiwyg_editor'] && $_FF_CONF['post_htmlmode']) {
        $usersettings->set_var('lang_use_wysiwyg',$LANG_GF92['editor_type']);
        $wysiwyg_select = '<select name="use_wysiwyg_editor" id="use_wysiwyg_editor">';
        $wysiwyg_select .= '<option value="0" '.($A['use_wysiwyg_editor'] == 0 ? ' selected="selected"' : '').'>'.$LANG_GF92['bbcode'].'</option>';
        $wysiwyg_select .= '<option value="1" '.($A['use_wysiwyg_editor'] == 1 ? ' selected="selected"' : '').'>'.$LANG_GF92['wysiwyg'].'</option>';
        $wysiwyg_select .= '</select>';
        $usersettings->set_var('wysiwyg_select',$wysiwyg_select);
    } else {
        $usersettings->set_var('wysiwyg_select','');
    }

    $usersettings->parse ('output', 'usersettings');
    $retval .= $usersettings->finish($usersettings->get_var('output'));

    return $retval;
}

function plugin_profilesave_forum($uid = 0 )
{
    global $_CONF, $_TABLES, $_FF_CONF, $_USER;

    if ( COM_isAnonUser() ) {
        return;
    }

    if ( $uid == 0 ) {
        $uid = (int) $_USER['uid'];
    } else {
        $uid = (int) $uid;
    }

    $xtopicsperpage     = COM_applyFilter($_POST['xtopicsperpage'],true);
    $xpostsperpage      = COM_applyFilter($_POST['xpostsperpage'],true);
    $xpostsperpage      = COM_applyFilter($_POST['xpostsperpage'],true);
    $xemailnotify       = isset($_POST['xemailnotify']) && $_POST['xemailnotify'] == 'on' ? 1 : 0;
    $xviewanonpost      = isset($_POST['xviewanonposts']) && $_POST['xviewanonposts'] == 'on' ? 1 : 0;
    $xviewanonposts     = isset($_POST['xviewanonposts']) && $_POST['xviewanonposts'] == 'on' ? 1 : 0;
    $xalwaysnotify      = isset($_POST['xalwaysnotify']) && $_POST['xalwaysnotify'] == 'on' ? 1 : 0;
    $xnotifyonce        = isset($_POST['xnotifyonce']) && $_POST['xnotifyonce'] == 'on' ? 1 : 0;
    $notifyfull         = isset($_POST['notifyfull']) && $_POST['notifyfull'] == 'on' ? 1 : 0;
    $xshowiframe        = isset($_POST['xshowiframe']) && $_POST['xshowiframe'] == 'on' ? 1 : 0;
    $topic_order        = isset($_POST['topic_order']) ? COM_applyFilter($_POST['topic_order']) : 'ASC';
    $wysiwyg_editor     = isset($_POST['use_wysiwyg_editor']) ? COM_applyFilter($_POST['use_wysiwyg_editor'],true) : 0;

    if ( $topic_order != 'ASC' && $topic_order != 'DESC' ) {
        $topic_order = 'ASC';
    }

    DB_query("UPDATE {$_TABLES['ff_userprefs']} SET
        topicsperpage=".(int)$xtopicsperpage.",
        postsperpage=".(int)$xpostsperpage.",
        enablenotify=".(int)$xemailnotify.",
        viewanonposts=".(int)$xviewanonposts.",
        alwaysnotify=".(int)$xalwaysnotify.",
        notify_once=".(int)$xnotifyonce.",
        notify_full=".(int)$notifyfull.",
        showiframe=".(int)$xshowiframe.",
        topic_order='".DB_escapeString($topic_order)."',
        use_wysiwyg_editor=".(int)$wysiwyg_editor." WHERE uid=".(int)$uid);


    if ( isset($_POST['signature'] ) ){
        $signature = $_POST['signature'];

        // see if user already has a preference record...
        // Get user specific settings from database
        $result = DB_query("SELECT * FROM {$_TABLES['ff_userinfo']} WHERE uid=".(int) $uid);
        $nrows = DB_numRows($result);
        if ($nrows == 0) {
            // Insert a new blank record. Defaults are set in SQL Defintion for table.
            DB_query("INSERT INTO {$_TABLES['ff_userinfo']} (uid,rating,signature) VALUES (".(int)$uid.",0,'')");
        }
        $sql = "UPDATE {$_TABLES['ff_userinfo']} SET signature='".DB_escapeString($signature)."' WHERE uid=".$uid;
        DB_query($sql);
    }
}


/**
* Return information for a forum topic
*
* @param    string  $id         topic id or *
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_forum($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_FF_CONF, $_USER, $_TABLES, $LANG_GF00;

    if ($uid == 0) {
        $uid = $_USER['uid'];
    }

    $buildingSearchIndex = false;
    $batchreindex = false;
    $summary = false;
    $properties = explode(',', $what);
    $fields = array();

    foreach ($properties as $p) {
        switch ($p) {
            case 'summary' :
                $summary = true;
                break;
            case 'search_index' :
                $buildingSearchIndex = true;
                break;
            case 'reindex' :
                $batchreindex = true;
                break;
            case 'date' :
            case 'date-modified':
            case 'date-created' :
                if ($summary) {
                    $fields[] = 'a.lastupdated AS unixdate';
                } else {
                    $fields[] = 'a.date AS unixdate';
                }
                break;
            case 'description':
            case 'excerpt':
            case 'raw-description' :
            case 'searchidx' :
                $fields[] = 'a.comment';
                break;
            case 'id':
                $fields[] = 'a.id';
                break;
            case 'title':
                $fields[] = 'a.subject';
                break;
            case 'label':
            case 'url':
                $fields[] = 'a.id';
                break;
            case 'author' :
                $fields[] = 'a.uid';
                break;
            case 'author_name' :
                $fields[] = 'a.name';
                break;
            case 'hits' :
                $fields[] = 'a.views';
                break;
            case 'perms' :
                $fields[] = 'a.uid';
                break;
            default:
                break;
        }
    }
    $fields = array_unique($fields);
    if (count($fields) == 0) {
        $retval = array();
        return $retval;
    }
    USES_forum_format();
    if ($id == '*') {
        if ( $summary ) {
            $where = " WHERE a.pid=0 ";
            $permOp = " AND ";
        } else {
            $where = '';
            $permOp = ' WHERE ';
        }
    } else {
        if ($summary == true) {
            // check to see if we have the PID or a reply ID
            // query the record for PID
            // if it is 0 - we are good
            // if it is not 0 - update ID to PID
            $pid = DB_getItem($_TABLES['ff_topic'],'pid','id='.DB_escapeString($id));
            if ( $pid != 0 && $pid != NULL) {
                $id = $pid;
            }
            $fields[] = "GROUP_CONCAT(a.comment ORDER BY a.date DESC SEPARATOR ' ') AS summarycomment ";
            $lastupdated = DB_getItem($_TABLES['ff_topic'],'lastupdated','id='.DB_escapeString($id));

            $where = " WHERE (id = '" . DB_escapeString($id) . "' OR pid = '".DB_escapeString($id)."') ";
            DB_query("SET group_concat_max_len = 18196");
        } else {
            $where = " WHERE id = '" . DB_escapeString($id) . "'";
        }
        $permOp = ' AND ';
    }
    $groups = array ();
    $usergroups = SEC_getUserGroups($uid);
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);
    $sql  = "SELECT " . implode(',', $fields) . ",a.pid,a.postmode, a.status, b.* ";
    $sql .= "FROM {$_TABLES['ff_topic']} a ";
    $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON a.forum=b.forum_id ";
    $sql .= $where . $permOp . " b.grp_id IN ($grouplist)";
    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }

    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);
        $props = array();
        foreach ($properties as $p) {
            switch ($p) {
                case 'date' :
                case 'date-created' :
                case 'date-modified':
                    if ($summary) {
                        $props[$p] = $lastupdated;
                    } else {
                        $props[$p] = $A['unixdate'];
                    }
                    break;
                case 'description':
                case 'excerpt':
                    $props[$p] = FF_formatTextBlock($A['comment'],$A['postmode'],'noquote',$A['status']);
                    break;
                case 'searchidx' :
                case 'raw-description' :
                    if ($summary == true) {
                        $comment = _forum_strip_bbcode($A['summarycomment']);
                    } else {
                        $comment = _forum_strip_bbcode($A['comment']);
                    }
                    $props[$p] = $comment;
                    break;
                case 'parent_id':
                case 'pid':
                    $props[$p] = $A['pid'];
                    break;
                case 'id':
                    $props['id'] = $A['id'];
                    break;
                case 'title':
                    $props['title'] = strip_tags($A['subject']);
                    break;
                case 'url':
                    if ( !empty($A['id']) ) {
                        $id = $A['id'];
                    }
                    if ( $A['pid'] == 0 ) {
                        $props['url'] = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$id;
                    } else {
                        $props['url'] = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$A['pid'].'&amp;topic='.$id.'#'.$id;
                    }
                    break;
                case 'label':
                    $props['label'] = $LANG_GF00['pluginlabel'];
                    break;
                case 'status':
                    $props['status'] = 1; // stub - default
                    break;
                case 'author' :
                    $props['author'] = $A['uid'];
                    break;
                case 'author_name' :
                    $props['author_name'] = $A['name'];
                    break;
                case 'hits' :
                    $props['hits'] = $A['views'];
                    break;
               case 'perms' :
                    // Default to the post owner and forum access group with
                    // read access for all
                    $props['perms'] = array(
                        'owner_id'      => $A['uid'],
                        'group_id'      => $A['grp_id'],
                        'perm_owner'    => 2,
                        'perm_group'    => 2,
                        'perm_members'  => 2,
                        'perm_anon'     => 2,
                    );
                    switch ($A['grp_id']) {
                    case 2:  // all users
                        // check login required...
                        if ( $_CONF['loginrequired'] == 1 || $_FF_CONF['registration_required'] == 1 ) {
                            $props['perms']['perm_anon'] = 0;
                        }
                        // otherwise - all users keep perm_member and perm_anon
                        break;
                    case 13: // Logged-in-users
                        // Logged-in users implies all groups except anon
                        $props['perms']['perm_anon'] = 0;
                        break;
                    default:
                        // Single group ID has access
                        $props['perms']['perm_anon'] = 0;
                        $props['perms']['perm_members'] = 0;
                        break;
                    }
                    break;
                default:
                    $props[$p] = '';
                    break;
            }
        }
        $mapped = array();
        if ( is_array($props) ) {
            foreach ($props as $key => $value) {
                if ($id == '*') {
                    if ($value != '') {
                        $mapped[$key] = $value;
                    }
                } else {
                    $mapped[$key] = $value;
                }
            }
        }
        if ($id == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }
    if (($id != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }
    if ( $retval === '' || count($retval) == 0 ) return NULL;
    return $retval;
}

function plugin_getconfigelementhelp_forum($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/forum.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/forum.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

function plugin_supportadblock_forum()
{
    return array('forum_category_list','forum_topic_list');
}

// +---------------------------------------------------------------------------+
// | Commmon Forum Functions                                                   |
// +---------------------------------------------------------------------------+

function forum_modPermission($forum,$uid='',$permission='') {
    global $_USER,$_TABLES;

    if ($uid == '') {
        $uid = 1;
    }

    if ( COM_isAnonUser() ) {
        return FALSE;
    }

    if ( isset($_USER['uid']) && $uid == $_USER['uid'] ) {
        if ( SEC_inGroup('Root') ) {
            return true;
        }
        if ( SEC_hasRights('forum.edit') ) {
            return true;
        }
    }

    /* Retrieve all moderator records for this forum */
    $modrecords = DB_query("SELECT * FROM {$_TABLES['ff_moderators']} WHERE mod_forum='$forum'");
    while ($A = DB_fetchArray($modrecords)) {
        if ($A['mod_groupid'] > 0) {
            if (SEC_inGroup($A['mod_groupid'],$uid)) {
                if ($permission != '') {
                    if ($A[$permission] == '1') {
                        return TRUE;
                    }
                 } else {
                    return TRUE;
                 }
           }
        } else {
            if ($A['mod_uid'] == $uid) {
                if ($permission != '') {
                    if ($A[$permission] == '1') {
                        return TRUE;
                    }
                 } else {
                    return TRUE;
                 }
            }
        }
    }

    /* If I get this far then I exited the loop without finding a permission record */
    return FALSE;

}

function _ff_canPost( $forum ) {
    global $_CONF, $_FF_CONF,$_USER, $_TABLES;

    if ( $_FF_CONF['registered_to_post'] && COM_isAnonUser() ) {
        return false;
    }

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    if (SEC_inGroup('Root') || SEC_hasRights('forum.edit')) {
        return true;
    }
    if (forum_modPermission($forum['forum'],$uid,'mod_edit')) {
        return true;
    }
    if ( $forum['is_readonly'] ) {
        return false;
    }
    if (!SEC_inGroup($forum['grp_id'])) {
        return false;
    }
    if ( $_FF_CONF['enable_user_rating_system'] ) {
        if ( COM_isAnonUser() ) {
            //Annon user can only post in forums with 0 or less
            if ( $forum['rating_post'] < 1 ) {
                return true;
            } else {
                return false;
            }
        }
        //get users forum rating
        $user_score_res = DB_query("SELECT rating FROM {$_TABLES['ff_userinfo']} WHERE uid = {$_USER['uid']}");
        $user_score_row = DB_fetchArray($user_score_res);
        if ( $forum['rating_post'] > intval($user_score_row['rating']) ) {
            return false;
        } else {
            return true;
        }
    }
    return true;
}


function forum_delAttachment($deleteid) {
    global $_TABLES,$_FF_CONF,$filemgmt_FileStore;

    // Check and see if the file is in the repository or a saved to the local forum data area
    $lid = DB_getItem($_TABLES['ff_attachments'],'repository_id',"id=$deleteid");
    if ($lid > 0 AND DB_count($_TABLES['filemgmt_filedetail'],"lid",$lid ) == 1) {
        // Verify Access rights
        $groupsql = filemgmt_buildAccessSql();
        $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= "WHERE a.lid='$lid' $groupsql";
        list($testaccess_cnt) = DB_fetchArray(DB_query($sql));
        if ($testaccess_cnt == 1) {
            $name = DB_getItem($_TABLES['filemgmt_filedetail'],"url","lid=$lid");
            DB_query("DELETE FROM {$_TABLES['filemgmt_filedesc']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_TABLES['filemgmt_votedata']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");
            $fullname  = $filemgmt_FileStore . $name;
            if ($fullname != '' && file_exists($fullname) && (!is_dir($fullname))) {
                @unlink($fullname);
            }
            DB_query("DELETE FROM {$_TABLES['ff_attachments']} WHERE id=$deleteid");
        }

    } else {
        $filename = DB_getItem($_TABLES['ff_attachments'],'filename',"id=$deleteid");
        $filedata = explode(':', $filename);
        $filename = $filedata[0];
        $realname = $filedata[1];
        $fullname = $_FF_CONF['uploadpath'].'/'.$filename;
        @unlink($fullname);
        $thumbnail = $_FF_CONF['uploadpath'].'/tn/'.$filename;
        @unlink($thumbnail);
        DB_query("DELETE FROM {$_TABLES['ff_attachments']} WHERE id=".(int) $deleteid);
    }
}

// +---------------------------------------------------------------------------+
// | Forum's OWN Plugin API Functions                                          |
// +---------------------------------------------------------------------------+


function forumPLG_showsmilies($wysiwyg = 0) {
    global $_CONF,$_FF_CONF;

    // Check and see if smiley plugin is installed
    if ($_FF_CONF['use_smilies_plugin'] AND function_exists('msg_showsmilies')) {
        return msg_showsmilies($wysiwyg);
    } else {
        $t = new Template($_CONF['path'] . 'plugins/forum/templates/');
        $t->set_file ('smilies' , 'smilies.thtml');
        $t->set_var ('imgset', "{$_CONF['site_url']}/forum/images");
        if ( $wysiwyg ) {
            $t->set_var ('emoticon','emoticon_wysiwyg');
        } else {
            $t->set_var ('emoticon','emoticon');
        }
        $t->parse ('output', 'smilies');
        return $t->finish($t->get_var('output'));
    }
}

function forumPLG_restoreEmoticons($str) {

    // Check and see if smiley plugin is installed
    if (function_exists(msg_showsmilies)) {
        return msg_restoreEmoticons($str);
    } else {
        forum_xchsmilies($str);
    }
}

function forumPLG_getPMlink($uid=0) {
    global $_TABLES,$_CONF, $_PLUGINS;

    static $blockArray = array();

    if (in_array('pm', $_PLUGINS)) {
        if ( !isset($blockArray[$uid]) || $uid == 0 ) {
            $username = DB_getItem($_TABLES['users'],'username','uid='.(int)$uid);
            if ( $username == '' ) {
                return '';
            }
            $blockArray[$uid]['username'] = $username;
            $block = DB_getItem($_TABLES['pm_userprefs'],'block','uid='.(int)$uid);
            $blockArray[$uid]['allow'] = (int) $block;
        }
        if ( $blockArray[$uid]['allow'] == 0 ) {
            return $_CONF['site_url'].'/pm/compose.php?mode=new&amp;username='.htmlentities($blockArray[$uid]['username']);
        }
    }
    return '';
}

function forumPLG_getMemberBadge($uid) {
    global $_CONF,$_FF_CONF;

    $retval = '';
    static $cache = array();
    if (array_key_exists($uid, $cache)) {
        return $cache[$uid];
    }

    $badges = Forum\Badge::getUserBadges($uid);
    $badge_urls = array();
    foreach ($badges as $badge) {
        $badge_urls[] = $badge->html;
    }
    $retval .= implode('<br />', $badge_urls);

    if ( function_exists('plugin_getmedals_medals') ) {
        $retval .= plugin_getmedals_medals($uid);
    }
    $cache[$uid] = $retval;
    return $cache[$uid];
}

function phpblock_forum_menu() {
    global $_TABLES,$_CONF,$_USER,$_FF_CONF,$LANG_GF01,$LANG_GF02,$LANG_GF07;

// let's move this to a template - shall we....

    $menuArray = array();

    $retval = '<ul>';
    $menuArray[] = '<a href="'.$_CONF['site_url'].'/forum/index.php" title="'.$LANG_GF02['msg196'].'">'.$LANG_GF01['INDEXPAGE'].'</a>';
    if ( !COM_isAnonUser() ) {
        $menuArray[] = '<a href="' . $_CONF['site_url'] . '/forum/notify.php' . '" title="' . $LANG_GF01['SUBSCRIPTIONS'] . '">' . $LANG_GF01['SUBSCRIPTIONS'] . '</a>';
        $menuArray[] = '<a href="' . $_CONF['site_url'] . '/forum/list.php?op=bookmarks' . '" title="' . $LANG_GF01['BOOKMARKS'] . '">' . $LANG_GF01['BOOKMARKS'] . '</a>';
    }
    if ( $_FF_CONF['allow_memberlist'] && !COM_isAnonUser() ) {
        $menuArray[] = '<a href="' . $_CONF['site_url'] . '/forum/memberlist.php" title="' . $LANG_GF02['msg88'] . '">' . $LANG_GF02['msg88'] . '</a>';
    }
    $menuArray[] = '<a href="' . $_CONF['site_url'] . '/forum/list.php?op=lastx" title="' . $LANG_GF01['LASTX'] . '">' . $LANG_GF01['LASTX'] . '</a>';
    $menuArray[] = '<a href="'.$_CONF['site_url'].'/forum/list.php?op=popular" title="' . $LANG_GF02['msg201'] . '">'.$LANG_GF07['3'].'</a>';

    $retval = COM_makeList($menuArray);
    return $retval;
}

function _ff_canUserViewRating( $forum_id ) {
    global $_USER, $_TABLES, $_FF_CONF;

    static $forum_req = array();
    static $user_rate = array();

    if ( !$_FF_CONF['enable_user_rating_system'] || SEC_inGroup('Root') || SEC_inGroup('forum Admin')) {
        return true;
    }

    $uid = COM_isAnonUser() ? 1 : $_USER['uid'];

    if ( isset($forum_req[$forum_id]) && $uid == 1 ) {
        if ( $forum_req[$forum_id] < 1 ) {
            return true;
        } else {
            return false;
        }
    }
    if ( !isset($forum_req[$forum_id]) ) {
        $forum_requirment_res = DB_query("SELECT rating_view FROM {$_TABLES['ff_forums']} WHERE forum_id = ". (int) $forum_id);
        $forum_requirement_row = DB_fetchArray($forum_requirment_res);
        $forum_req[$forum_id] = $forum_requirement_row['rating_view'];
        if (COM_isAnonUser() ) {
            //Annon user can only view forums with 0 or less
            if ($forum_requirement_row['rating_view'] < 1)
                return true;
            else
                return false;
        }
    }
    $user_rate = _ff_getUserRating($uid);

    if ($forum_req[$forum_id] > (int) $user_rate ) {
        return false;
    } else {
        return true;
    }
}

function _ff_getUserRating( $uid = 1 )
{
    global $_FF_CONF, $_TABLES, $_USER;

    static $_user_rate = array();

    if ( !$_FF_CONF['enable_user_rating_system'] ) {
        return;
    }
    if ( !isset($_user_rate[$uid]) ) {
        $_user_rate[$uid] = (int) DB_getItem($_TABLES['ff_userinfo'],'rating','uid='.(int) $uid);
    }
    return $_user_rate[$uid];
}

function _ff_cacheBookMarks( $uid )
{
    global $_CONF, $_TABLES;

    static $_bmArray = array();

    if ( !COM_isAnonUser() && count($_bmArray) == 0 ) {
        $sql = "SELECT * FROM {$_TABLES['ff_bookmarks']} WHERE uid=".(int) $uid;
        $result = DB_query($sql);
        while (($bm = DB_fetchArray($result)) != NULL ) {
            $_bmArray[$bm['topic_id']] = 1;
        }
    }
    return $_bmArray;
}

function FF_sigEditorProfile( $signature = '', $errors = array() )
{
    global $_CONF, $_FF_CONF, $_TABLES, $_USER;

    $retval = '';

    USES_lib_bbcode();

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file ('signature','signature_profile.thtml');

    if ( $_FF_CONF['allow_img_bbcode'] != true ) {
        $exclude = array('img');
        $img_tag = false;
        $T->unset_var('img_tag_allowed');
    } else {
        $exclude = array();
        $T->set_var('img_tag_allowed',true);
    }

    $T->set_var(array(
        'signature'   => $signature,
        'preview_signature' => BBC_formatTextBlock($signature,'text',array(),array(), $exclude),
    ));

    $error_message = '';
    if ( count($errors) > 0 ) {
        foreach($errors AS $error) {
            $error_message .= $error .'<br />';
        }
    }
    $T->set_var('error_message',$error_message);

    $T->parse ('output', 'signature');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function FF_navbar ($menuitems, $selected='', $parms='') {
    global $_CONF, $_FF_CONF,$LANG_ADMIN, $LANG_GF00,$LANG_GF06;

    USES_lib_admin();

    $retval = '';

    $menu_arr = array();

    $instructions = $LANG_GF00['instructions'];

    for ( $i=1; $i <= count($menuitems); $i++ )  {
        $parms = explode( "=",current($menuitems) );
        if ( key($menuitems) != $selected ) {
            $url = current($menuitems);
            $label = key($menuitems);
            $menu_arr = array_merge($menu_arr,array (
                array('url' => $url,
                      'text'=> $label)
            ));
        }
        next($menuitems);
    }

    $menu_arr = array_merge($menu_arr,array (
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    ));

    $retval .= COM_startBlock ($LANG_GF06[10], '',COM_getBlockTemplate ('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $instructions,
        $_CONF['site_url'] . '/forum/images/forum.png'
    );

    $retval .= COM_endBlock();

    return $retval;
}

function USES_forum_functions() {
    global $_CONF, $_FF_CONF;
    require_once $_CONF['path'].'plugins/forum/include/forum.inc.php';
}

function USES_forum_format() {
    global $_CONF, $_FF_CONF;
    require_once $_CONF['path'].'plugins/forum/include/format.inc.php';
}

function USES_forum_topic() {
    global $_CONF, $_FF_CONF;
    require_once $_CONF['path'].'plugins/forum/include/topic.inc.php';
}

function USES_forum_upload() {
    global $_CONF, $_FF_CONF;
    require_once $_CONF['path'].'plugins/forum/include/upload.inc.php';
}

function USES_forum_admin() {
    global $_CONF, $_FF_CONF;
    require_once $_CONF['path'].'plugins/forum/include/admin.inc.php';
}


function _forum_strip_bbcode($str)
{
    global $_CONF, $_FF_CONF;

    $bbcode = new StringParser_BBCode ();
    $bbcode->setGlobalCaseSensitive (false);

    $bbcode->addCode ('code', 'usecontent?', 'getItemInfo_code', array ('usecontent_param' => 'default'),
                      'code', array('listitem', 'block', 'inline', 'quote'), array ('link'));
    $bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('p', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('s', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('size', 'simple_replace', null, array('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('color', 'simple_replace', null, array('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('listitem', 'block', 'inline', 'link'), array ());
    $bbcode->addCode ('list', 'simple_replace', null, array('start_tag' => '', 'end_tag' => ''),
                      'inline', array ('inline','block', 'listitem'), array ());
    $bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '*', 'end_tag' => ''),
                      'listitem', array ('list'), array ());
    $bbcode->addCode ('quote', 'usecontent', 'getItemInfo_quote', array (),
                      'link', array ('listitem', 'block', 'inline','link'), array ());

    $bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
    $bbcode->setCodeFlag ('*', 'paragraphs', true);
    $bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
    $bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

    $bbcode->setRootParagraphHandling (false);

    if ($_FF_CONF['use_censor']) {
        $str = COM_checkWords($str);
    }
    $str = $bbcode->parse ($str);

    return $str;
}
function getItemInfo_quote($action, $attributes, $content, $params, $node_object)
{
    if ( $action == 'validate') {
        return true;
    }
    return '';
}
function getItemInfo_code($action, $attributes, $content, $params, $node_object)
{
    if ( $action == 'validate') {
        return true;
    }
    return $content;
}

function plugin_profileblocksdisplay_forum( $uid )
{
    global $_CONF, $_FF_CONF, $_TABLES, $_USER, $LANG_GF01;

    $retval = '';

    if ( !isset($_FF_CONF['enable_likes_profile']) || $_FF_CONF['enable_likes_profile'] != true ) return $retval;

    $likesReceived = Forum\Like::LikesReceived($uid);
    $likesGiven    = Forum\Like::LikesGiven($uid);

    if ( count($likesReceived) == 0 && count($likesGiven) == 0 ) return $retval;

    USES_forum_format();

    $dt = new Date('now',$_USER['tzid']);

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $T->set_file('userlikes','userprofile-likes.thtml');

    $T->set_var(array(
        'tooltip_style' => COM_getToolTipStyle(),
        'lang_likes_given' => $LANG_GF01['likes_given'],
        'lang_likes_received' => $LANG_GF01['likes_received'],
        'lang_no_likes_given' => $LANG_GF01['no_likes_given'],
        'lang_no_likes_received' => $LANG_GF01['no_likes_received'],
        'lang_liked' => $LANG_GF01['liked'],
        'lang_liked_thread' => $LANG_GF01['liked_thread'],
        'lang_likes' => $LANG_GF01['likes'],
    ));

    $receivedCounter = 0;

    if ( count($likesReceived) == 0 ) {
        $T->set_var('no_likes_received','No Likes Received');
    } else {
        $T->unset_var('no_likes_received');
        $T->set_block('userlikes', 'likes', 'likesblock');

        foreach( $likesReceived AS $row ) {
            if ( $receivedCounter > 20 ) break;
            $testText = FF_formatTextBlock($row['comment'],'text','noquote',0);
            $testText = strip_tags($testText);
            $html2txt = new Html2Text\Html2Text($testText,false);
            $testText = trim($html2txt->get_text());
            $shortComment = '';
            $commentArray = explode(' ',$testText);
            $wordCount = count($commentArray);
            $lengthCount = 0;
            $tailString = '';
            foreach ($commentArray AS $word) {
                $lengthCount = $lengthCount + strlen($word);
                $shortComment .= $word.' ';
                if ( $lengthCount >= $_FF_CONF['contentinfo_numchars'] ) {
                    $tailString = '...';
                    break;
                }
            }
            $shortComment = trim($shortComment).$tailString;
            $postinfo = @htmlspecialchars(preg_replace('#\r?\n#',' ',strip_tags($shortComment)),ENT_COMPAT,COM_getEncodingt());
            $postinfo = stripBBCode($postinfo);

            if ( $row['like_date'] == '' ) $row['like_date'] = $dt->toRFC822();

            $dt->setTimestamp(strtotime($row['like_date']));
            $T->set_var(array(
                'user'  => COM_getDisplayName($row['voter_id']),
                'liker-id' => $row['voter_id'],
                'post-date' => $dt->format($_CONF['date'],true),
                'liked-user' => COM_getDisplayName($row['poster_id']),
                'forum-thread' => @htmlspecialchars($row['subject'],ENT_COMPAT,COM_getEncodingt()),
                'forum-snippit' => @htmlspecialchars($row['comment'],ENT_COMPAT,COM_getEncodingt()),
                'forum-thread-link' => $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$row['topic_id'],
                'postinfo' => $postinfo,
            ));
            $T->parse('likesblock','likes',true);
            $receivedCounter++;
        }
    }

    $givenCounter = 0;
    if ( count($likesGiven) == 0 ) {
        $T->set_var('no_likes_given','No Likes Given');
    } else {
        $T->unset_var('no_likes_given');
        $T->set_block('userlikes', 'givenlikes', 'givenlikesblock');
        foreach ( $likesGiven AS $row ) {
            if ( $givenCounter > 20 ) break;
            if ( $row['like_date'] == '' ) $row['like_date'] = $dt->toRFC822();

            $dt->setTimestamp(strtotime($row['like_date']));

            $testText = FF_formatTextBlock($row['comment'],'text','noquote',0);
            $testText = strip_tags($testText);
            $html2txt = new Html2Text\Html2Text($testText,false);
            $testText = trim($html2txt->get_text());
            $shortComment = '';
            $commentArray = explode(' ',$testText);
            $wordCount = count($commentArray);
            $lengthCount = 0;
            $tailString = '';
            foreach ($commentArray AS $word) {
                $lengthCount = $lengthCount + strlen($word);
                $shortComment .= $word.' ';
                if ( $lengthCount >= $_FF_CONF['contentinfo_numchars'] ) {
                    $tailString = '...';
                    break;
                }
            }
            $shortComment = trim($shortComment).$tailString;
            $postinfo = @htmlspecialchars(preg_replace('#\r?\n#',' ',strip_tags($shortComment)),ENT_COMPAT,COM_getEncodingt());
            $postinfo = stripBBCode($postinfo);

            $T->set_var(array(
                'user'  => COM_getDisplayName($row['voter_id']),
                'liker-id' => $row['voter_id'],
                'post-date' => $dt->format($_CONF['date'],true),
                'liked-user' => COM_getDisplayName($row['poster_id']),
                'liked-user-id' => $row['poster_id'],
                'forum-thread' => @htmlspecialchars($row['subject'],ENT_COMPAT,COM_getEncodingt()),
                'forum-snippit' => @htmlspecialchars($row['comment'],ENT_COMPAT,COM_getEncodingt()),
                'forum-thread-link' => $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$row['topic_id'],
                'postinfo' => $postinfo,
            ));
            $T->parse('givenlikesblock','givenlikes',true);
            $givenCounter++;
        }
    }
    $T->parse( 'block', 'userlikes' );
    $retval .= $T->finish( $T->get_var( 'block' ));

    return $retval;

}

function plugin_privacy_export_forum_DISABLED($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $exportFields = array('id','forum_name','uid','name','date','email','subject','ip','permalink');

    $sql = "SELECT t.id,f.forum_name,t.uid,t.name,t.date,t.email,t.subject,t.ip FROM {$_TABLES['ff_topic']} AS t LEFT JOIN {$_TABLES['ff_forums']} AS f ON t.forum=f.forum_id WHERE t.uid = ". (int) $uid;
    if ( $ip != '' ) {
        $sql .= " OR ip = '" . DB_escapeString($ip)."'";
    }
    $sql .= " ORDER BY date ASC";

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

    $retval .= "<forum>\n";

    foreach($rows AS $row) {
        $row['permalink'] = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$row['id'];
        $retval .= "<post>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {
                if ( $item == 'date' ) {
                    $dt = new Date($value,$_USER['tzid']);
                    $value = $dt->format($dt->getUserFormat(),true);
                }
                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</post>\n";
    }
    $retval .= "</forum>\n";

    if ( function_exists('tidy_repair_string')) {
        $retval = tidy_repair_string($retval, array('input-xml' => 1));
    }

    return $retval;
}

?>
