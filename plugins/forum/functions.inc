<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.0 for Geeklog - The Ultimate Weblog               |
// | Official release date: Feb 7,2003                                         |
// +---------------------------------------------------------------------------+
// | functions.inc                                                             |
// | This file implements the necessary Geeklog Plugin API functions and       |
// | includes some common Forum functions                                      |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs   - tony@tonybibbs.com                         |
// +---------------------------------------------------------------------------+
// | FORUM Plugin Authors                                                      |
// | Developed by Blaine and Matthew                                           |
// | Blaine Lang,    contact: blaine@portalparts.com   www.portalparts.com     |
// | Matthew DeWyer, contact: matt@mycws.com           www.cweb.ws             |
// | Prototype & Concept :  Mr.GxBlock of www.gxblock.com                      |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

if (strpos ($_SERVER['PHP_SELF'], 'functions.inc') !== false)
{
    die ('This file can not be used on its own.');
}

$langfile = $_CONF['path'] . 'plugins/forum/language/' . $_CONF['language'] . '.php';
if (file_exists ($langfile))
{
    include_once ($langfile);
}
else
{
    include_once ($_CONF['path'] . 'plugins/forum/language/english.php');
}

// Need to add this line because PLG_enableStateChange does an include of this file inside a plugin function and $_TABLES is not global.
// This will be fixed in GL 1.4.1 Final
$_TABLES['plugins']             = $_DB_table_prefix . 'plugins';

$pi_version = floatval (DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name='forum'"));
require_once ($_CONF['path_system'] . 'lib-portalparts.php');
require_once ($_CONF['path'] . 'plugins/forum/config.php');

if( empty( $LANG_CHARSET )) {
    $CONF_FORUM['charset'] = $_CONF['default_charset'];
    if( empty( $charset )) {
        $CONF_FORUM['charset'] = 'iso-8859-1';
    }
} else {
    $CONF_FORUM['charset'] = $LANG_CHARSET;
}

/**
* Returns the items for this plugin that should appear on the main menu
*/
function plugin_getmenuitems_forum()
{
    global $CONF_FORUM,$_CONF,$_USER, $LANG_GF00;

    $menuitems = array();

    if ( $CONF_FORUM['registration_required'] == 1 ) {
        if ($_USER['uid'] > 1) {
            $menuitems["$LANG_GF00[pluginlabel]"] = "$_CONF[site_url]/forum/index.php";
        } else {
            $menuitems = '';
        }
    } else {
        $menuitems["$LANG_GF00[pluginlabel]"] = "$_CONF[site_url]/forum/index.php";
    }

    return $menuitems;
}


function plugin_autotags_forum($op,$content='',$autotag='') {
    global $_CONF;

    if ($op == 'tagname' ) {
        return 'forum';
    } elseif ($op == 'parse') {
        if ($autotag['parm2'] == '')  $autotag['parm2'] = 'here';
        $filelink = '<a href="'.$_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$autotag['parm1'].'">'.$autotag['parm2'].'</a>';
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
        return array('Forum',$_CONF['site_admin_url'] . "/plugins/forum/index.php",$_CONF['site_url'] . '/forum/images/forum.png');
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
        $numtopics = DB_getITEM($_TABLES['gf_topic'],"count(*)");
        return array('Forum', $_CONF['site_admin_url'] . '/plugins/forum/index.php', $numtopics);
    }

}

/**
* Returns the user menuitem option for this plugin
* Only one menu item can be returned.
*/
function plugin_getuseroption_forum()
{
    global $_USER, $CONF_FORUM, $_CONF, $LANG_GF00;

    if ( $CONF_FORUM['registration_required'] == 1 ) {
        if ($_USER['uid'] > 1 || SEC_hasRights('forum.user')) {
            return array($LANG_GF00['useradminmenu'], $_CONF['site_url'] . '/forum/userprefs.php', 0);
        }
    } else {
        return array($LANG_GF00['useradminmenu'], $_CONF['site_url'] . '/forum/userprefs.php', 0);
    }
}


function plugin_user_create_forum ($uid)
{
    global $_TABLES;

    DB_query ("INSERT INTO {$_TABLES['gf_userinfo']} (uid) VALUES ('{$uid}')");
}

/**
* Called if admin deletes a user - Removes any moderator and Watch Records for user
*/
function plugin_user_delete_forum ($uid)
{
    global $_TABLES;

    $username = DB_getItem($_TABLES['users'], "username", "uid=$uid");
    DB_query("DELETE FROM {$_TABLES['gf_moderators']} WHERE mod_username='$username'");
    DB_query("DELETE FROM {$_TABLES['gf_watch']} WHERE uid ='$uid'");
    DB_query("DELETE FROM {$_TABLES['gf_userinfo']} WHERE uid ='$uid'");
    DB_query("DELETE FROM {$_TABLES['gf_userprefs']} WHERE uid ='$uid'");
    DB_query("DELETE FROM {$_TABLES['gf_log']} WHERE uid ='$uid'");
    DB_query("UPDATE {$_TABLES['gf_topic']} SET uid = 1 WHERE uid = '$uid'");
}

function plugin_profileextrassave_forum()
{
    global $_CONF,$_USER, $_TABLES;

    require_once ($_CONF['path_html'] . 'forum/include/gf_format.php');
    $uid = $_USER['uid'];
    if ($uid > 1) {
        $location   = gf_preparefordb($_POST['forum_location'],'html');
        $aim        = gf_preparefordb($_POST['forum_aim'],'html');
        $icq        = gf_preparefordb($_POST['forum_icq'],'html');
        $yim        = gf_preparefordb($_POST['forum_yim'],'html');
        $msnm       = gf_preparefordb($_POST['forum_msnm'],'html');
        $interests  = gf_preparefordb($_POST['forum_interests'],'html');
        $occupation = gf_preparefordb($_POST['forum_occupation'],'html');
        DB_save ($_TABLES['gf_userinfo'], "uid,location,aim,yim,icq,msnm,interests,occupation",
                 "'$uid','$location','$aim','$yim','$icq','$msnm','$interests','$occupation'");
   }
}


function plugin_profilevariablesedit_forum($uid, &$template)
{
    global $_TABLES, $LANG_GF04;

    $result = DB_query ("SELECT uid,location,aim,yim,icq,yim,msnm,interests,occupation FROM {$_TABLES['gf_userinfo']} WHERE uid = $uid");
    $A = DB_fetchArray ($result);
    $template->set_var('forum_location', $A['location']);
    $template->set_var('forum_aim', $A['aim']);
    $template->set_var('forum_icq', $A['icq']);
    $template->set_var('forum_yim', $A['yim']);
    $template->set_var('forum_msnm', $A['msnm']);
    $template->set_var('forum_interests', $A['interests']);
    $template->set_var('forum_occupation', $A['occupation']);

    $template->set_var ('lang_forum', $LANG_GF04['label_forum']);
    $template->set_var ('lang_location', $LANG_GF04['label_location']);
    $template->set_var ('lang_aim', $LANG_GF04['label_aim']);
    $template->set_var ('lang_icq', $LANG_GF04['label_icq']);
    $template->set_var ('lang_yim', $LANG_GF04['label_yim']);
    $template->set_var ('lang_msnm', $LANG_GF04['label_msnm']);
    $template->set_var ('lang_interests', $LANG_GF04['label_interests']);
    $template->set_var ('lang_occupation', $LANG_GF04['label_occupation']);
}

function plugin_profilevariablesdisplay_forum ($uid, &$template)
{

    global $CONF_FORUM,$_TABLES, $_CONF, $LANG_GF02,$LANG_GF04;
    $postlimit="10";        // How many posts you want displayed

    $query = DB_query ("SELECT id,date,forum,subject FROM {$_TABLES['gf_topic']} WHERE uid = {$uid} ORDER BY date DESC LIMIT 100");
    $numposts = 0;

    while (list($postid,$date,$forum_id,$subject) = DB_fetchArray($query))  {
        $grp_id = DB_getItem($_TABLES['gf_forums'],'grp_id',"forum_id='$forum_id'");
        $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");
        if (SEC_inGroup($groupname) OR $grp_id == 2) {
            if ($CONF_FORUM['use_censor']) {
                $subject = COM_checkWords($subject);
            }
            $numposts++;
            $postdate = COM_getUserDateTimeFormat ($date);
            $template->set_var ('row_number', $numposts . '.');
            $template->set_var ('story_begin_href', '<a href="' . $_CONF['site_url'] . '/forum/viewtopic.php?showtopic=' . $postid . '">');
            $template->set_var ('story_title', $subject);
            $template->set_var ('story_end_href', '</a>');
            $template->set_var ('story_date', $postdate[0]);
            $template->parse ('forum_post_row', 'strow', true);
            if ($numposts >= $postlimit) {
                break;
            }
        }
    }

    if ($numposts == 0) {
        $template->set_var ('forum_post_row', '<tr><td>' .$LANG_GF02['msg155']. '</td></tr>');
    }

    $count = DB_count ($_TABLES['gf_topic'], 'uid', $uid);
    $template->set_var ('forum_lang_number_posts', $LANG_GF02['msg156']);
    $template->set_var ('forum_number_posts', $count);

    $template->set_var ('forum_start_block_last10posts', COM_startBlock
        ( $LANG_GF02['msg158'] . DB_getItem ($_TABLES['users'],
           'username', "uid = $uid")));
    $template->set_var ('forum_headline_last10posts', $LANG_GF02['msg157']);

    /* Display Instant Messaging Handles */
    $result = DB_query ("SELECT uid,location,aim,yim,icq,yim,msnm,interests,occupation FROM {$_TABLES['gf_userinfo']} WHERE uid = $uid");
    $A = DB_fetchArray ($result);
    $template->set_var('forum_location', $A['location']);
    $template->set_var('forum_aim', $A['aim']);
    $template->set_var('forum_icq', $A['icq']);
    $template->set_var('forum_yim', $A['yim']);
    $template->set_var('forum_msnm', $A['msnm']);
    $template->set_var('forum_interests', $A['interests']);
    $template->set_var('forum_occupation', $A['occupation']);

    $template->set_var ('lang_forum', $LANG_GF04['label_forum']);
    $template->set_var ('lang_location', $LANG_GF04['label_location']);
    $template->set_var ('lang_aim', $LANG_GF04['label_aim']);
    $template->set_var ('lang_icq', $LANG_GF04['label_icq']);
    $template->set_var ('lang_yim', $LANG_GF04['label_yim']);
    $template->set_var ('lang_msnm', $LANG_GF04['label_msnm']);
    $template->set_var ('lang_interests', $LANG_GF04['label_interests']);
    $template->set_var ('lang_occupation', $LANG_GF04['label_occupation']);
}

function plugin_statssummary_forum() {
    global $_CONF, $_TABLES, $LANG_GF00;

    // This shows in the summary box
    $total_items = DB_count($_TABLES['gf_topic']);      // Total number of Forum Posts
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
    global $_CONF, $CONF_FORUM,$_TABLES, $LANG_GF00,$LANG_GF01;

    $stat_templates = new Template($_CONF['path_layout'] . 'stats');
    $stat_templates->set_file(array('itemstats'=>'itemstatistics.thtml',
                            'statrow'=>'singlestat.thtml'));
    $retval='';
    if ($showsitestats == 1) {
        // This shows in the summary box
        $total_pages = DB_count($_TABLES['gf_topic']);      // Total number of Forum Posts
        $summary_label = $LANG_GF00['statslabel'];        // Label to display

        $retval = "<table border='0' width='100%' cellspacing='0' cellpadding='0'>";
        $retval .= "<tr><td>$summary_label</td>";
        $retval .= "<td align='right'>" . $total_pages . "&nbsp;&nbsp;</td></tr></table>";
    } else {
        $header_arr = array(
            array('text' => $LANG_GF01['TOPICSUBJECT'], 'field' => 'topicsubject'),
            array('text' => $LANG_GF01['VIEWS'], 'field' => 'views'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_GF00['statsheading1'],
        );
        $result = DB_query("SELECT forum,subject,views,id FROM {$_TABLES['gf_topic']} where pid='0' ORDER BY views desc");
        $nrows  = DB_numRows($result);
        if ($nrows > 0) {
            $displaycount = 0;
            while (list ($forum,$subject, $views,$id) = DB_fetchARRAY($result)) {
                $forum_id = DB_getItem($_TABLES['gf_topic'],'forum',"id='$id'");
                $grp_id = DB_getItem($_TABLES['gf_forums'],'grp_id',"forum_id='$forum_id'");
                $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");
                if (SEC_inGroup($groupname) OR $grp_id == 2) {
                    if ($CONF_FORUM['use_censor']) {
                        $subject = COM_checkWords($subject);
                    }
                    $url = $_CONF['site_url']. "/forum/viewtopic.php?showtopic=$id";
                    $S['topicsubject'] = '<a href="' . $url . '">' . COM_truncate($subject,$CONF_FORUM['show_subject_length'],'...') . '</a>';
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
            array('text' => $LANG_GF01['TOPICSUBJECT'], 'field' => 'topicsubject'),
            array('text' => $LANG_GF01['REPLIES'], 'field' => 'replies'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_GF00['statsheading2'],
        );
        $result = DB_query("SELECT forum,subject,replies,id FROM {$_TABLES['gf_topic']} where pid='0' ORDER BY replies desc");
        $nrows  = DB_numRows($result);
        if ($nrows > 0) {
            $stat_templates->set_var('item_label',$LANG_GF01['TOPICSUBJECT']);
            $stat_templates->set_var('stat_name',$LANG_GF01['REPLIES']);
            $displaycount=0;
            while (list ($forum,$subject, $replies,$id) = DB_fetchARRAY($result)) {
                $forum_id = DB_getItem($_TABLES['gf_topic'],'forum',"id='$id'");
                $grp_id = DB_getItem($_TABLES['gf_forums'],'grp_id',"forum_id='$forum_id'");
                $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");
                if (SEC_inGroup($groupname) OR $grp_id == 2) {
                    if ($CONF_FORUM['use_censor']) {
                        $subject = COM_checkWords($subject);
                    }
                    $url = $_CONF['site_url']. "/forum/viewtopic.php?showtopic=$id";
                    $S['topicsubject'] = '<a href="' . $url . '">' . COM_truncate($subject,$CONF_FORUM['show_subject_length'],'...') . '</a>';
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


function plugin_getheadercode_forum() {
    global $_CONF, $CONF_FORUM, $gllabsMooToolsLoaded;

    $gf_header = '';

    if ( $gllabsMooToolsLoaded != 1 ) {
        $gf_header .= '<script type="text/javascript" src="' . $_CONF['site_url'] . '/forum/js/mootools-release-1.11.packed.js"></script>' . LB;
        $gllabsMooToolsLoaded = 1;
    }
    $gf_header .= '<script type="text/javascript">' . LB;
    $gf_header .= '	window.addEvent(\'load\', function() {' . LB;
    $gf_header .= '		var gfTips = new Tips($$(\'.gf_mootip\'), { maxTitleChars: 50, className: \'gftool\' });' . LB;
    $gf_header .= '	});' . LB;
    $gf_header .= '</script>' . LB;
    return $gf_header;
}


/**
* Geeklog is asking us to provide any new items that show up in the type drop-down
* on search.php.  Let's let users search the Filelistings in the Filemgmt Plugin
*
*/

function plugin_searchtypes_forum()
{
    global $LANG_GF00;

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

function plugin_dopluginsearch_forum($query, $datestart, $dateend, $topic, $type, $author,$keyType,$page, $perpage)
{
    global $LANG_GF00, $LANG_GF01, $_TABLES, $_CONF, $CONF_FORUM;

    if (empty($type)) {
        $type = 'all';
    }
    // Bail if we aren't supppose to do our search
    if ($type <> 'all' AND $type <> 'forum') {
        $plugin_results = new Plugin();
        $plugin_results->plugin_name = $LANG_GF00['plugin_name'];
        $plugin_results->searchlabel = $LANG_GF00['searchlabel'];
        return $plugin_results;
    }

    $query = addslashes($query);

    // Build search SQL

    $sqltmp = " WHERE 1=1 ";

    if ( $keyType == 'phrase' ) {
        $sqltmp .= "AND (comment LIKE '%$query%' OR subject LIKE '%$query%')";
    } else  if ( $keyType == 'any' ) {
        $sqltmp .= 'AND ';
        $tmp = '';
        $mywords = explode( ' ', $query );
        foreach( $mywords AS $mysearchitem ) {
            $mysearchitem = addslashes( $mysearchitem );
            $tmp .= "( comment LIKE '%$mysearchitem%' OR subject LIKE '%$mysearchitem%' ) OR ";
        }
        $tmp = substr($tmp, 0, strlen($tmp) - 3);  // pulls off the last OR
        $sqltmp .= "($tmp)";
    } else if ( $keyType == 'all' ) {
        $sqltmp .= 'AND ';
        $tmp = '';
        $mywords = explode( ' ', $query );
        foreach( $mywords AS $mysearchitem ) {
            $mysearchitem = addslashes( $mysearchitem );
            $tmp .= "( comment LIKE '%$mysearchitem%' ) AND ";
        }
        $tmp = substr($tmp, 0, strlen($tmp) - 4);
        $sqltmp .= "($tmp)";

        $sqltmp .= 'OR ';
        $tmp = '';
        $mywords = explode( ' ', $query );
        foreach( $mywords AS $mysearchitem ) {
            $mysearchitem = addslashes( $mysearchitem );
            $tmp .= "( subject LIKE '%$mysearchitem%' ) AND ";
        }
        $tmp = substr($tmp, 0, strlen($tmp) - 4);
        $sqltmp .= "($tmp)";

    } else {
        $sqltmp = "WHERE (comment LIKE '%$query%' OR subject LIKE '%$query%')";
    }

    if ( $author != 0 ) {
        $sqltmp .= " AND uid='" . $author . "'";
    }

    // handle date ranges

    if (!empty($datestart) && !empty($dateend)) {
        $delim = substr($datestart, 4, 1);
        $sd = explode($delim,$datestart);
        $ed = explode($delim,$dateend);
        $startdate = mktime(0,0,0,$sd[1],$sd[2],$sd[0]);
        $enddate = mktime(0,0,0,$ed[1],$ed[2],$ed[0]) + 3600;
        $sqltmp .= " AND date BETWEEN '$startdate' AND '$enddate'";
    }

    $sql = "SELECT id,name,forum,date,subject,comment,views,uid FROM {$_TABLES['gf_topic']} "
           . $sqltmp . " ORDER BY date DESC";

    // limit what we are searching for...
    if ( $perpage < 1 ) {
        $perpage = 10;
    }
    $limitStart = ($page-1) * $perpage;
    $limitEnd   = $limitStart + $perpage;
    $sql .= " LIMIT " . $limitStart . "," . $limitEnd;

    // Perform search
    $result = DB_query($sql);

    // OK, now return coma delmited string of table header labels
    // Need to use language variables
    require_once($_CONF['path_system'] . 'classes/plugin.class.php');
    $plugin_results = new Plugin();
    $plugin_results->plugin_name = 'forum';
    if (!empty($author)) {
       $username = DB_getItem($_TABLES['users'],"username","uid = {$author}");
       $plugin_results->searchlabel = sprintf($LANG_GF00['searchresults'],$LANG_GF01['FOR'] . '&nbsp;' . $username);
    } else {
       $plugin_results->searchlabel = sprintf($LANG_GF00['searchresults'],"");
    }
    $plugin_results->addSearchHeading($LANG_GF01['FORUM']);
    $plugin_results->addSearchHeading($LANG_GF01['SUBJECT']);
    $plugin_results->addSearchHeading($LANG_GF01['DATE']);
    $plugin_results->addSearchHeading($LANG_GF01['VIEWS'] );
    $totalsearched = DB_numRows($result);
    $searchcount=0;
    // NOTE if any of your data items need to be links then add them here!
    // make sure data elements are in an array and in the same order as your
    // headings above!
    for ($i = 1; $i <= $totalsearched; $i++) {
        $A = DB_fetchArray($result);
        $grp_id = DB_getItem($_TABLES['gf_forums'],'grp_id',"forum_id='{$A['forum']}'");
        $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");
        if (SEC_inGroup($groupname) OR $grp_id == 2) {
            if ($CONF_FORUM['use_censor']) {
                $A['subject'] = COM_checkWords($A['subject']);
            }
            $searchcount++;
            $forumname = DB_getItem($_TABLES['gf_forums'],"forum_name","forum_id='{$A['forum']}'");
            $subject = wordwrap($A['subject'],40,"<br" . XHTML . ">");
            $date = strftime('%b %d %Y @ %I:%M %p', $A['date']);
            $url = $_CONF['site_url']. "/forum/viewtopic.php?showtopic={$A['id']}";
            $row = array($forumname, "<a href=\"$url\">" . COM_truncate($subject,$CONF_FORUM['show_subject_length'],'...') . "</a>",$date,$A['views']);
            $plugin_results->addSearchResult($row);
        }
    }
    $plugin_results->num_searchresults = $searchcount;
    $plugin_results->num_itemssearched = DB_count($_TABLES['gf_topic']);

    return $plugin_results;
}


/* RSS FEED Related API functions */

function plugin_getfeednames_forum ()
{
    global $_TABLES;

    $feeds = array ();

    $feeds[] = array ('id' => '0', 'name' => 'ALL FORUMS');

    $asql = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($asql)) {
        $firstforum=true;
        $bsql = DB_query("SELECT * FROM {$_TABLES['gf_forums']} WHERE forum_cat='$A[id]' ORDER BY forum_order ASC");
        while($B = DB_fetchArray($bsql)) {
            if ($firstforum) {
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
    global $pi_version;

    $where = '';
    if ($pi_version < 2.6) {
        return '';
    }

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
            $where = " WHERE forum=$forumID AND no_newposts=0 AND forum.grp_id IN ($grouplist) ";
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
            $where .= "date >= DATE_SUB(NOW(),INTERVAL $hours HOUR) ORDER BY date DESC";
        } else {
            $limitsql = ' ORDER BY date DESC LIMIT ' . $limits;
        }
    }
    else
    {
        $limitsql = ' ORDER BY date DESC LIMIT 10';
    }

    $sql = $where . $limitsql;

    return $sql;
}

function plugin_getfeedcontent_forum ($feed, &$link, &$update)
{
    global $_CONF, $_TABLES, $CONF_FORUM, $LANG_GF01, $pi_version;

    $content = array();
    $lids = array();
    $ids = array();

    if ($pi_version < 2.6) {
        return $content;
    }

    $allow_smilies = $CONF_FORUM['allow_smilies'];
    $CONF_FORUM['allow_smilies']=0;

    require_once ($_CONF['path_html'] . 'forum/include/gf_format.php');
    if( !function_exists( 'str_ireplace' ))
    {
        require_once( 'PHP/Compat.php' );
        PHP_Compat::loadFunction( 'str_ireplace' );
    }
    require_once ($_CONF['path_html'] . 'forum/include/bbcode/stringparser_bbcode.class.php');

    $result = DB_query ("SELECT topic,limits,content_length FROM {$_TABLES['syndication']} WHERE fid = $feed");
    $F = DB_fetchArray ($result);

//    $sql = "SELECT topic.id,topic.forum,topic.subject,topic.uid,topic.name,topic.comment,topic.date,topic.postmode,forum.forum_name,forum.grp_id ";
    $sql = "SELECT * ";
    $sql .= "FROM {$_TABLES['gf_topic']} topic LEFT JOIN {$_TABLES['gf_forums']} forum ON topic.forum=forum.forum_id ";
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
        // Handle Pre ver 2.5 quoting and New Line Formatting - consider adding this to a migrate function
        if ($CONF_FORUM['pre2.5_mode']) {
            if ( stristr($comment,'[code') == false ) {
                $comment = str_replace('<pre>','[code]',$comment);
                $comment = str_replace('</pre>','[/code]',$comment);
            }
            $comment = str_replace(array("<br />\r\n","<br />\n\r","<br />\r","<br />\n"), '<br />', $comment );
            $comment = preg_replace("/\[QUOTE\sBY=\s(.+?)\]/i","[QUOTE] Quote by $1:",$comment);
            /* Reformat code blocks - version 2.3.3 and prior */
            $comment = str_replace( '<pre class="forumCode">', '[code]', $comment );
            $comment = preg_replace("/\[QUOTE\sBY=(.+?)\]/i","[QUOTE] Quote by $1:",$comment);
        }

        $comment = gf_formatTextBlock($comment,$A['postmode'],'preview');

        // we don't have a stylesheet in the news feed, so replace our div with the style...
        $comment = str_replace('<div class="quotemain">','<div style="border: 1px dotted #000;border-left: 4px solid #8394B2;color:#465584;  padding: 4px;  margin: 5px auto 8px auto;">',$comment);

        $desc = $preface . $comment;

        $link = "{$_CONF['site_url']}/forum/viewtopic.php?showtopic={$A['id']}";

        if ($CONF_FORUM['use_censor']) {
            $A['subject'] = COM_checkWords($A['subject']);
        }
        $content[] = array ('title'     => $A['forum_name'] . ' :: ' . $A['subject'],
                            'summary'   => $desc,
                            'text'      => $desc,
                            'link'      => $link,
                            'uid'       => $A['uid'],
                            'date'      => $A['date'],
                            'format'    => $A['postmode'],
                           );
        $ids[] = $A['id'];
    }

    if ($F['topic'] == 0) {
        $link = "{$_CONF['site_url']}/forum/index.php";
    } else {
        $link = "{$_CONF['site_url']}/forum/index.php.php?forum={$F['topic']}";
    }

    if (count($ids) > 0 ) {
        $update = implode (',', $ids);
    }

    $CONF_FORUM['allow_smilies'] = $allow_smilies;

    return $content;
}

function plugin_feedupdatecheck_forum ($feed, $topic, $update_data, $limit)
{
    global $_TABLES;

    $sql = "SELECT id,forum.grp_id FROM {$_TABLES['gf_topic']} topic LEFT JOIN {$_TABLES['gf_forums']} forum ON topic.forum=forum.forum_id " . forum_buildFeedsSql ($topic, $limits);
    $result = DB_query ($sql);
    $num = DB_numRows ($result);

    $ids = array ();
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $ids[] = $A['id'];
    }
    $current = implode (',', $ids);

//    COM_errorLog ("Update check for forum: comparing new list ($current) with old list ($update_data)", 1);

    return ( $current != $update_data ) ? false : true;
}

/* End of RSS Feed Related Functions */



/**
* Called by the plugin Editor to display the current plugin code version
* This may be different then the version installed and registered currently.
* If newer then you may want to run the update
*/
function plugin_chkVersion_forum() {
    global $CONF_FORUM;
    return $CONF_FORUM['version'] ;

}


/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_forum() {
    global $_TABLES,$CONF_FORUM;

    include ('upgrade.inc');        // Include the upgrade functions

    $curversion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name = 'forum'");

    switch ($curversion) {

        case "2.3" :
            if (upgrade_232() == 0 )  {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.5RC1' WHERE `pi_name` = 'forum' LIMIT 1");
                if (upgrade_25() == 0 )  {
                    DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.6', `pi_gl_version` = '1.4.1' WHERE `pi_name` = 'forum' LIMIT 1");
                }
            }
            break;

        case "2.3.2" :
            if (upgrade_232() == 0 )  {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.5RC1' WHERE `pi_name` = 'forum' LIMIT 1");
                if (upgrade_25() == 0 )  {
                    DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.6', `pi_gl_version` = '1.4.1' WHERE `pi_name` = 'forum' LIMIT 1");
                }
            }
            break;

        case "2.5RC1" :
            if (upgrade_25() == 0 )  {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.6', `pi_gl_version` = '1.4.1' WHERE `pi_name` = 'forum' LIMIT 1");
            }
            break;
        case "2.6" :
            DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.7', `pi_gl_version` = '1.4.1' WHERE `pi_name` = 'forum' LIMIT 1");
            break;
        case "2.7" :
            if (upgrade_30() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET `pi_version` = '3.0', `pi_gl_version` = '1.4.1' WHERE `pi_name` = 'forum' LIMIT 1");
            }
            break;
    }

    /* Check if update completed and return a message number to be shown */
    if (DB_getItem($_TABLES['plugins'],'pi_version',"pi_name = 'forum'") == $CONF_FORUM['version']) {
        return 1;
    } else {
        return 5;
    }

}


/**
* Removes the datastructures for this plugin from the Geeklog database
* This may get called by the install routine to undue anything created during the install.
* Added check to see that plugin is first disabled.
*/
function plugin_uninstall_forum($installCheck='')
{
    global $_TABLES, $LANG_GF00;

    $pi_name='forum';
    $FEATURES = array ('forum.edit', 'forum.user');
    $TABLES = array(
        'gf_topic',
        'gf_categories',
        'gf_forums',
        'gf_settings',
        'gf_watch',
        'gf_moderators',
        'gf_banned_ip',
        'gf_log',
        'gf_userprefs',
        'gf_userinfo',
        'gf_attachments',
        'gf_bookmarks');


     // Check and see if plugin is still enabled - if so display warning and exit
    if ($installCheck != '' && DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name = "' .$pi_name. '"')) {
        COM_errorLog("Plugin is installed and enabled. Disable first if you want to de-install it",1);
        $display .= COM_startBlock($LANG_GF00['warning']);
        $display .= $LANG_GF00['enabled'];
        $display .= COM_endBlock();
        echo $display;
        exit;
    }

    // Ok to proceed and delete plugin - Unregister the plugin with Geeklog
    COM_errorLog('Attempting to unregister the Forum plugin from Geeklog',1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'forum'",1);
    // Drop tables
    foreach($TABLES as $table) {
        $t = $_TABLES["$table"];
        COM_errorLog("Removing Table $t",1);
        DB_query("DROP TABLE $t",1);
    }

    // Remove Admin Security for this plugin
    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_admin'");
    COM_errorLog("Removing $pi_name Admin Group", 1);
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_admin'");
    COM_errorLog("Removing root users from admin of $pi_name");
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id",1);

    // Remove all the associated features - access rights. The feature ID's were stored in the vars table during install.
    foreach ($FEATURES as $feature) {
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        COM_errorLog("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }

    COM_errorLog("Removing Forum - Block Definition ", 1);
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn = 'phpblock_forum_newposts'",1);
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn = 'phpblock_forum_newposts2'",1);
    DB_query("DELETE FROM {$_TABLES['blocks']} WHERE phpblockfn = 'phpblock_forum_menu'",1);

    COM_errorLog('...success',1);
    return true;
}

//  ------- GENERAL FORUM FUNCTIONS --------

function forum_chkUsercanPost() {
    global $_CONF,$CONF_FORUM,$LANG_GF00, $LANG_GF01, $LANG_GF02, $_TABLES, $_USER;

    //Check is anonymous users can post
    if($CONF_FORUM['registered_to_post'] && $_USER['uid'] < 2){
        $url = $_CONF['site_url']. '/users.php?mode=new';
        $display .= COM_startBlock($LANG_GF00['access_denied']);
        $display .= '<br>' .$LANG_GF01['loginreqpost']. '<p>';
        $display .= '<meta http-equiv="refresh" content="3; URL=' .$url. '">';
        $display .= COM_endBlock();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }
    // Check if IP of user has been banned
    $ip = getenv("REMOTE_ADDR");
    $sqlresult = DB_query ("SELECT * FROM {$_TABLES['gf_banned_ip']} WHERE host_ip like '$ip'");
    $numRows = DB_numRows($sqlresult);
    if ( $numRows > 0 ) {
        echo COM_startBlock($LANG_GF00['access_denied']);
        echo ($LANG_GF02['msg14']);
        printf ($LANG_GF02['msg15'],$_CONF['site_mail']);
        echo COM_endBlock();
        echo COM_siteFooter(true);
        exit();
    }
}

function forum_chkUsercanAccess($secure=false) {
    global $_CONF, $LANG_GF01, $LANG_GF02, $CONF_FORUM, $_USER;

    if ($CONF_FORUM['registration_required'] && $_USER['uid'] < 2) {
            echo  '<TABLE WIDTH="100%" HEIGHT="100"><TR><TD><CENTER>';
        printf($LANG_GF01['loginreqview'], '<A HREF="' .$_CONF['site_url']. '/users.php?mode=new">', '<A HREF="' .$_CONF['site_url']. '/users.php">');
        echo '</CENTER></TD></TR></TABLE>';
        echo COM_siteFooter();
        exit;
    } elseif($secure AND empty($_USER['uid'])) {
        echo '<br>';
        BlockMessage($LANG_GF01['ACCESSERROR'],$LANG_GF02['msg83'],false);
        echo COM_siteFooter();
        exit;
    }
}


function forum_xchsmilies($message,$reverse=false) {

    $search = array(":D", ":)", ":(", "8O", ":?", "B)", ":lol:", ":x", ":P" ,":oops:", ":o",":cry:",
        ":evil:", ":twisted:", ":roll:", ";)", ":!:", ":question:", ":idea:", ":arrow:", ":|",
        ":mrgreen:",":mrt:",":love:",":cat:");
    $replace = array(
    "<img align=absmiddle src='images/smilies/biggrin.gif' alt='Big Grin'>",
    "<img align=absmiddle src='images/smilies/smile.gif' alt='Smile'>",
    "<img align=absmiddle src='images/smilies/frown.gif' alt='Frown'>",
    "<img align=absmiddle src='images/smilies/eek.gif' alt='Eek!'>",
    "<img align=absmiddle src='images/smilies/confused.gif' alt='Confused'>",
    "<img align=absmiddle src='images/smilies/cool.gif' alt='Cool'>",
    "<img align=absmiddle src='images/smilies/lol.gif' alt='Laughing Out Loud'>",
    "<img align=absmiddle src='images/smilies/mad.gif' alt='Angry'>",
    "<img align=absmiddle src='images/smilies/razz.gif' alt='Razz'>",
    "<img align=absmiddle src='images/smilies/redface.gif' alt='Oops!'>",
    "<img align=absmiddle src='images/smilies/surprised.gif' alt='Surprised!'>",
    "<img align=absmiddle src='images/smilies/cry.gif' alt='Cry'>",
    "<img align=absmiddle src='images/smilies/evil.gif' alt='Evil'>",
    "<img align=absmiddle src='images/smilies/twisted.gif' alt='Twisted Evil'>",
    "<img align=absmiddle src='images/smilies/rolleyes.gif' alt='Rolling Eyes'>",
    "<img align=absmiddle src='images/smilies/wink.gif' alt='Wink'>",
    "<img align=absmiddle src='images/smilies/exclaim.gif' alt='Exclaimation'>",
    "<img align=absmiddle src='images/smilies/question.gif' alt='Question'>",
    "<img align=absmiddle src='images/smilies/idea.gif' alt='Idea'>",
    "<img align=absmiddle src='images/smilies/arrow.gif' alt='Arrow'>",
    "<img align=absmiddle src='images/smilies/neutral.gif' alt='Neutral'>",
    "<img align=absmiddle src='images/smilies/mrgreen.gif' alt='Mr. Green'>");

    if (!$reverse) {
        $message = str_replace($search, $replace, $message);
    } else {
        $message = str_replace($replace, $search, $message);
    }
    return $message;
}


function forum_statusMessage($message='',$url,$prompt,$post=false,$forum='') {
    global $_CONF,$_TABLES,$_USER,$LANG_GF02,$CONF_FORUM;

    if ($prompt=="") {
        $prompt = $LANG_GF02['msg140'];
    }
    $statusmsg = new Template($_CONF['path_layout'] . 'forum/layout');
    $statusmsg->set_file (array ('statusmsg'=>'statusmessage.thtml'));
    $statusmsg->set_var ('imgset', $CONF_FORUM['imgset']);
    $statusmsg->set_var ('heading', $LANG_GF02['StatusHeading']);
    $statusmsg->set_var ('message', $message);
    if ($CONF_FORUM['use_autorefresh'] == "0") {
        $statusmsg->set_var ('prompt',sprintf($LANG_GF02['msg139'],$prompt,$url));
        $statusmsg->set_var('autoredirect_javascript','');
    } else {
        $autorefresh_delay = $CONF_FORUM['autorefresh_delay'] * 1000;
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
    echo $statusmsg->finish ($statusmsg->get_var('output'));

    return;
}


/* Function to create a new forum
*
* @param        string     $name        Forum name
* @param        string     $category    Category id to add the forum
* @param        string     $dscp        Optional Category Description
* @param        string     $order       Optional Display order
* @param        string     $order       Optional Group ID if a private group - Default Group 'All Users'
* @param        string     $order       Optional Readonly flag
* @param        string     $order       Optional Hidden flag
* @param        string     $order       Optional Don't show in newposts and centerblock
* @return       string                  Returns the Forum ID for the new forum if successful
*/
function forum_addForum($name,$category,$dscp="",$order="",$grp_id=2,$is_readonly=0,$is_hidden=0,$no_newposts=0,$attachmentgroup=1) {
    global $_TABLES, $_USER;

    $fields = 'forum_order,forum_name,forum_dscp,forum_cat,grp_id,is_readonly,is_hidden,no_newposts,use_attachment_grpid';
    DB_query("INSERT INTO {$_TABLES['gf_forums']} ($fields)
        VALUES ('$order','$name','$dscp','$category','$grp_id','$is_readonly','$is_hidden','$no_newposts',$attachmentgroup)");

    $query = DB_query("SELECT max(forum_id) FROM {$_TABLES['gf_forums']} ");
    list ($forumid) = DB_fetchArray($query);
    $modquery = DB_query("SELECT * FROM {$_TABLES['gf_moderators']} WHERE mod_uid='{$_USER['uid']}' AND mod_forum='$forumid'");
    if (DB_numrows($modquery) < 1){
        $fields = 'mod_uid,mod_username,mod_forum,mod_delete,mod_ban,mod_edit,mod_move,mod_stick';
        DB_query("INSERT INTO {$_TABLES['gf_moderators']} ($fields) VALUES ('{$_USER['uid']}','{$_USER[username]}', '$forumid','1','1','1','1','1')");
    }
    return $forumid;
}

/* Function to delete a forum
*
* @param        string     $id        Forum id to delete
* @return       boolean               Returns true
*/
function forum_deleteForum($id) {
    global $_TABLES;
    DB_query("DELETE FROM {$_TABLES['gf_forums']} WHERE forum_id='$id'");
    DB_query("DELETE FROM {$_TABLES['gf_topic']} WHERE forum='$id'");
    DB_query("DELETE FROM {$_TABLES['gf_moderators']} WHERE mod_forum='$id'");
    DB_query("DELETE FROM {$_TABLES['gf_watch']} WHERE forum_id ='$id'");
    return true;
}


function phpblock_forum_newposts() {
    global $_CONF, $_USER, $_TABLES, $LANG_GF01, $LANG_GF02, $mode, $CONF_FORUM,$pi_version;

    if ($pi_version < 2.6) {
        return;
    }
    $mode = COM_applyFilter($_GET['mode']);
    $order = COM_applyFilter($_GET['order']);
    if (DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name="forum"')) {
        // Check if user has clicked on a sort option in the block
        if ($mode == 'forumblock') {
            if (($order == 'new') OR ($order == '')) {
                $view_modemsg = sprintf($LANG_GF02['msg74'],$post_limit);
                if ($CONF_FORUM['sb_latestpostonly']) {
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
                if ($CONF_FORUM['sb_latestpostonly']) {
                    $orderby = 'lastupdated';
                } else {
                    $orderby = 'date';
                }
        }

        $block = new Template($_CONF['path_layout'] . 'forum/layout/blocks');
        $block->set_file (array ('block'=>'latestpostsblock.thtml','topicrecord' => 'block_displayline.thtml'));
        $block->set_var ('phpself', $_CONF['site_url'] .'/index.php');
        $block->set_var ('layout_url', $_CONF['layout_url']);
        $block->set_var ('LANG_ORDERBY', $LANG_GF01['ORDERBY']);
        $block->set_var ('LANG_NEW', $LANG_GF01['NEW']);
        $block->set_var ('LANG_VIEWS', $LANG_GF01['VIEWS']);
        $block->set_var ('LANG_POSTS', $LANG_GF01['POSTS']);
        $block->set_var ('view_modemsg', $view_modemsg);

        // Select SQL to either show the latest posts even if they are for the same topic
        // Or show only the lastest post for each of the last updated topics
        if ($CONF_FORUM['sb_latestpostonly']) {
            $sql = "SELECT a.id,a.forum,a.name,a.date,a.subject,a.replies,a.views,a.uid,a.pid FROM {$_TABLES['gf_topic']} a ";
            $sql .= "LEFT JOIN {$_TABLES['gf_forums']} b ON a.forum=b.forum_id ";
            $sql .= "WHERE a.pid=0 AND b.no_newposts = 0 ORDER BY $orderby DESC";
            $result = DB_query($sql);
        } else {
            $sql = "SELECT a.id,a.forum,a.name,a.date,a.subject,a.replies,a.views,a.uid,a.pid FROM {$_TABLES['gf_topic']} a ";
            $sql .= "LEFT JOIN {$_TABLES['gf_forums']} b ON a.forum=b.forum_id ";
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
                $A['subject'] = DB_getItem($_TABLES['gf_topic'],'subject',"id='{$A['pid']}'");
            }

            $fresult = DB_query ("SELECT grp_id, no_newposts FROM {$_TABLES['gf_forums']} WHERE forum_id='{$A['forum']}'");
            list ($grp_id, $no_newposts) = DB_fetchArray ($fresult);
            $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$grp_id'");

            if ($no_newposts==0 AND (SEC_inGroup($groupname) OR $grp_id == 2)) {
                $displaycount++;
                if ($CONF_FORUM['use_censor']) {
                    $A['subject'] = COM_checkWords($A['subject']);
                }
                $fullsubject = $A['subject'];
                if (strlen ($A['subject']) > $CONF_FORUM['sb_subject_size']) {
                    $A['subject'] = substr($A['subject'], 0, $CONF_FORUM['sb_subject_size']);
                    $A['subject'] .= '..';
                    $profilelink = '';
                }

                if ($A['replies'] > 0 AND $CONF_FORUM['sb_latestpostonly']) {
                    $lastreplySQL = DB_query("Select uid,name,date from {$_TABLES['gf_topic']} WHERE pid={$A['id']} ORDER BY date DESC LIMIT 1");
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

               $block->set_var ('site_url', $_CONF['site_url']);
               $block->set_var ('css_id', $onetwo);
               $block->set_var ('img_dir', $CONF_FORUM['imgset']);
               $block->set_var ('forum_id', $A['forum']);
               $block->set_var ('forum_name', DB_getItem($_TABLES['gf_forums'],'forum_name',"forum_id='{$A['forum']}'"));
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
               if ($CONF_FORUM['allow_user_dateformat']) {
                   $date = COM_getUserDateTimeFormat ($postdate);
                   $block->set_var ('date', $date[0]);
               } else {
                   $block->set_var ('date', strftime($CONF_FORUM['default_Datetime_format'], $postdate));
               }

               $block->parse ('block_records', 'topicrecord',true);

                if ($onetwo == 1) {
                    $onetwo = 2;
                } else {
                    $onetwo = 1;
                }
                if ($displaycount >= $CONF_FORUM['sideblock_numposts']) {
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
    global $_CONF, $_USER, $_TABLES, $LANG_GF01,$CONF_FORUM;
    global $LANG_GF02, $mode, $order,$pi_version;

    require_once ($_CONF['path_html'] . 'forum/include/gf_format.php');

    if ($pi_version < 2.6) {
        return;
    }

    if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $CONF_FORUM['registration_required'] == 1 )  {
        return;
    }

    $retval = '';

    // Retrieve Centerblock Settings
    $query = DB_query ("SELECT cb_enable, cb_homepage, cb_where FROM {$_TABLES['gf_settings']}");
    list ($cb_enable,$cb_homepage,$cb_where) = DB_fetchArray ($query);

    // If enabled only for homepage and this is not page 1 or a topic page,
    // then set disable flag
    if ($cb_homepage == 1 AND ($page > 1 OR !empty ($topic))) {
        $cb_enable = 0;
    } elseif ($cb_homepage == 0 and $page > 1) {
        $cb_where = 1;      // Top of Page
    }

    // Check if there are no featured articles in this topic
    // and if so then place it at the top of the page
    if (!empty ($topic)) {
        $wheresql = "WHERE tid='$topic' AND featured > 0";
    } else {
        $wheresql = 'WHERE featured = 1';
    }
    $query = DB_query ("SELECT count(*) as count FROM {$_TABLES['stories']} $wheresql");
    $result = DB_fetchArray ($query);
    if ($result['count'] == 0 and $cb_where == 2) {
        $cb_where = 1;
    }

    if ($cb_enable AND ($cb_where == $where)) {

        $block = new Template ($_CONF['path_layout'] . 'forum/layout/blocks');
        $block->set_file (array ('block'=>'centerblock.thtml','record' => 'centerblock_displayline.thtml'));
        $block->set_var ('phpself', $_CONF['site_url'] .'/index.php');
        $block->set_var ('startblock', COM_startBlock($LANG_GF02['msg170']));
        $block->set_var ('endblock', COM_endBlock());
        $block->set_var ('site_url', $_CONF['site_url']);
        $block->set_var ('layout_url', $_CONF['layout_url']);
        $block->set_var ('LANG_title', $LANG_GF02['msg170']);
        $block->set_var ('LANG_FORUM', $LANG_GF01['FORUM']);
        $block->set_var ('LANG_TOPIC', $LANG_GF01['TOPIC']);
        $block->set_var ('LANG_LASTPOST', $LANG_GF01['LASTPOST']);
        $block->set_var ('LANG_viewlastpost', $LANG_GF02['msg160']);
        $block->set_var ('LANG_forumjump', $LANG_GF02['msg195']);

        $groups = array ();
        $usergroups = SEC_getUserGroups();
        foreach ($usergroups as $group) {
            $groups[] = $group;
        }
        $grouplist = implode(',',$groups);

        $sql  = "SELECT a.id, a.forum, a.name, a.date, a.lastupdated, a.last_reply_rec, a.subject, ";
        $sql .= "a.comment, a.uid, a.name, a.pid, a.replies, a.views, b.forum_name  ";
        $sql .= "FROM {$_TABLES['gf_topic']} a ";
        $sql .= "LEFT JOIN {$_TABLES['gf_forums']} b ON a.forum=b.forum_id ";
        $sql .= "WHERE pid=0 AND b.grp_id IN ($grouplist) AND b.no_newposts = 0 ";
        $sql .= "ORDER BY lastupdated DESC LIMIT {$CONF_FORUM['centerblock_numposts']}";
        $result = DB_query ($sql);

        if (DB_numRows($result) == 0) {
            return;
        }
        $cssid = 0;
        while ($A = DB_fetchArray ($result)) {
            if ($CONF_FORUM['use_censor']) {
                $A['subject'] = COM_checkWords($A['subject']);
                $A['comment'] = COM_checkWords($A['comment']);
            }

            $fullsubject = "{$A['subject']}\n{$LANG_GF01['POSTEDBY']}:{$A['name']}{$LANG_GF01['VIEWS']}:{$A['views']}, {$LANG_GF01['REPLIES']}:{$A['replies']}";
            if(strlen ($A['subject']) > $CONF_FORUM['cb_subject_size']) {
                $A['subject'] = substr ($A['subject'], 0, $CONF_FORUM['cb_subject_size']);
                $A['subject'] .= '...';
            }

            if ($CONF_FORUM['allow_user_dateformat']) {
                $firstdate = COM_getUserDateTimeFormat ($A['date']);
                $firstdate = $firstdate[0];
                $lastdate = COM_getUserDateTimeFormat ($A['lastupdated']);
                $lastdate = $lastdate[0];
            } else {
                $firstdate = strftime($CONF_FORUM['default_Datetime_format'], $A['date']);
                $lastdate = strftime($CONF_FORUM['default_Datetime_format'], $A['lastupdated']);
            }
            if ($A['uid'] > 1) {
                $topicinfo = "{$LANG_GF01['STARTEDBY']} " . COM_getDisplayName($A['uid']) . ', ';
                //$topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],COM_getDisplayName($A['uid']));
            } else {
                $topicinfo = "{$LANG_GF01['STARTEDBY']},{$A['name']},";
            }

            $topicinfo .= "{$firstdate}<br" . XHTML . ">{$LANG_GF01['VIEWS']}:{$A['views']}, {$LANG_GF01['REPLIES']}:{$A['replies']}<br" . XHTML . ">";

            if (empty ($A['last_reply_rec']) || $A['last_reply_rec'] < 1) {
                $lastid = $A['id'];
//                $lastpostinfo = wordwrap(strip_tags(substr($A['comment'],0,$CONF_FORUM['contentinfo_numchars'])),$CONF_FORUM['linkinfo_width'],"<br" . XHTML . ">\n");
                $testText = gf_formatTextBlock($A['comment'],'text','text');
                $testText = strip_tags($testText);
                $lastpostinfo = htmlspecialchars(preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$CONF_FORUM['contentinfo_numchars']). '...')));
            } else {
                $qlreply = DB_query("SELECT id,uid,name,comment FROM {$_TABLES['gf_topic']} WHERE id={$A['last_reply_rec']}");
                $B = DB_fetchArray($qlreply);
                $lastid = $B['id'];
                $lastcomment = $B['comment'];
                if ($B['uid'] > 1) {
                    $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],COM_getDisplayName($B['uid']));
                } else {
                    $topicinfo .= sprintf($LANG_GF01['LASTREPLYBY'],$B['name']);
                }
//                $lastpostinfo = wordwrap(strip_tags(substr($lastcomment,0,$CONF_FORUM['contentinfo_numchars'])),$CONF_FORUM['linkinfo_width'],"<br" . XHTML . ">\n");
                $testText = gf_formatTextBlock($B['comment'],'text','text');
//                $testText = str_replace('"',"'",$testText);
                $testText = strip_tags($testText);
// echo $testText;exit;
                $lastpostinfo = preg_replace('#\r?\n#','<br>',strip_tags(substr($testText,0,$CONF_FORUM['contentinfo_numchars']). '...'));
            }

            $cssid = ($cssid ==1) ? 2: 1;
            $block->set_var ('lastpostinfo', $lastpostinfo);
            $block->set_var ('lastpostid', $lastid);
            $block->set_var ('topicinfo', $topicinfo);
            $block->set_var ('cssid', $cssid);
            $block->set_var ('img_dir', $CONF_FORUM['imgset']);
            $block->set_var ('forum_id', $A['forum']);
            $block->set_var ('forum_name', $A['forum_name']);
            $block->set_var ('topic_id', $A['id']);
            $block->set_var ('topic_subject', $A['subject']);
            $block->set_var ('fullsubject', $fullsubject);
            $block->set_var ('views', $A['views']);
            $block->set_var ('replies', $A['replies']);
            $block->set_var ('date',$lastdate );
            $block->set_var ('lastpostby',$A['name']);
            $block->parse ('block_records', 'record',true);
        }

        $block->parse ('output', 'block');
        $retval .= $block->finish ($block->get_var ('output'));
    }

    return $retval;
}

// +---------------------------------------------------------------------------+
// | Commmon Forum Functions                                                   |
// +---------------------------------------------------------------------------+

function forum_modPermission($forum,$uid='',$permission='') {
    global $_USER,$_TABLES;

    if ($uid == '') {
        $uid = $_USER['uid'];
    }

    if ($uid <=1) {
        return FALSE;  // Invalid ID or anonymous user
    }

    /* Retrieve all moderator records for this forum */
    $modrecords = DB_query("SELECT * FROM {$_TABLES['gf_moderators']} WHERE mod_forum='$forum'");
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

function forum_delAttachment($deleteid) {
    global $_TABLES,$_FM_TABLES,$CONF_FORUM,$filemgmt_FileStore;

    // Check and see if the file is in the repository or a saved to the local forum data area
    $lid = DB_getItem($_TABLES['gf_attachments'],'repository_id',"id=$deleteid");
    if ($lid > 0 AND DB_count($_FM_TABLES['filemgmt_filedetail'],"lid",$lid ) == 1) {
        // Verify Access rights
        $groupsql = filemgmt_buildAccessSql();
        $sql = "SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_FM_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= "WHERE a.lid='$lid' $groupsql";
        list($testaccess_cnt) = DB_fetchArray(DB_query($sql));
        if ($testaccess_cnt == 1) {
            $name = DB_getItem($_FM_TABLES['filemgmt_filedetail'],"url","lid=$lid");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedesc']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_votedata']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");
            $fullname  = $filemgmt_FileStore . $name;
            if ($fullname != '' && file_exists($fullname) && (!is_dir($fullname))) {
                @unlink($fullname);
            }
            DB_query("DELETE FROM {$_TABLES['gf_attachments']} WHERE id=$deleteid");
        }

    } else {
        $filename = DB_getItem($_TABLES['gf_attachments'],'filename',"id=$deleteid");
        $filedata = explode(':', $filename);
        $filename = $filedata[0];
        $realname = $filedata[1];
        $fullname = "{$CONF_FORUM['uploadpath']}/$filename";
        @unlink($fullname);
        $thumbnail = "{$CONF_FORUM['uploadpath']}/tn/$filename";
        @unlink($thumbnail);
        DB_query("DELETE FROM {$_TABLES['gf_attachments']} WHERE id=$deleteid");
    }
}

// +---------------------------------------------------------------------------+
// | Forum's OWN Plugin API Functions                                          |
// +---------------------------------------------------------------------------+


function forumPLG_showsmilies() {
    global $_CONF,$CONF_FORUM;

    // Check and see if glMessenger is installed
    if ($CONF_FORUM['use_smilies_plugin'] AND function_exists(msg_showsmilies)) {
        return msg_showsmilies();
    } else {
        $t = new Template($_CONF['path_layout'] . 'forum/layout');
        $t->set_file ('smilies' , 'smilies.thtml');
        $t->set_var ('imgset', "{$_CONF['site_url']}/forum/images");
        $t->parse ('output', 'smilies');
        return $t->finish($t->get_var('output'));
    }
}

function forumPLG_restoreEmoticons($str) {

    // Check and see if glMessenger is installed
    if (function_exists(msg_showsmilies)) {
        return msg_restoreEmoticons($str);
    } else {
        forum_xchsmilies($str);
    }
}

function forumPLG_getPMlink($member) {
    global $_TABLES,$_CONF;
    if (DB_getItem($_TABLES['plugins'],'pi_enabled',"pi_name='messenger'") == 1) {
        return "{$_CONF['site_url']}/messenger/index.php?action=newpm&toname={$member};";
    } else {
        return '';
    }
}

function forumPLG_getMemberBadge($uid) {
    global $_CONF,$CONF_FORUM;

    if ($uid > 1) {
        if (!SEC_inGroup('Root',$uid)) {
            foreach ($CONF_FORUM['grouptags'] as $groupid => $buttonimg) {
                if (SEC_inGroup($groupid,$uid)) {
                    return "<img src=\"{$CONF_FORUM['imgset']}/badges/{$buttonimg}\" alt=\"\"" . XHTML . "><br" . XHTML . ">";
                }
            }
        } else {
            return "<img src=\"{$CONF_FORUM['imgset']}/badges/{$CONF_FORUM['grouptags']['Root']}\" alt=\"\"" . XHTML . "><br" . XHTML . ">";
        }
    }
}

/* GL-Menu 2.5 version of the Forum Menu */
function phpblock_forum_menu() {
    global $_TABLES,$_CONF,$_USER,$CONF_FORUM,$LANG_GF01,$LANG_GF02,$LANG_GF07,$CONF_GLMENU,$_BLOCK_TEMPLATE;

    if (isset($CONF_GLMENU) AND $CONF_GLMENU['installed_version'] >= 2.5) {
        if ($CONF_GLMENU['milonicmode']) {
            $retval = COM_startBlock( $LANG_GF00['forumblock'], '', 'glmenu/milonicmenu/blockheader-blockmenu.thtml');
            $retval .= 'aI("text='.$LANG_GF01['INDEXPAGE'].';url='.$_CONF['site_url'].'/forum/index.php;");' . LB;
            if ($_USER['uid'] > 1) {
                $retval .= 'aI("text='.$LANG_GF01['USERPREFS'].';url='.$_CONF['site_url'].'/forum/userprefs.php;");' . LB;
                $retval .= 'aI("text='.$LANG_GF01['SUBSCRIPTIONS'].';url='.$_CONF['site_url'].'/forum/notify.php;");' . LB;
            }
            $retval .= 'aI("text='.$LANG_GF02['msg88'].';url='.$_CONF['site_url'].'/forum/memberlist.php;");' . LB;
            $retval .= 'aI("text='.$LANG_GF07['3'].';url='.$_CONF['site_url'].'/forum/index.php?op=popular;");' . LB;
            $retval .= COM_endBlock('glmenu/milonicmenu/blockfooter-blockmenu.thtml');
        } else {
            $retval = COM_startBlock( $LANG01[47], '', 'glmenu/blockheader.thtml');
            $t = new Template($_CONF['path_layout'] . 'glmenu/cssmenu');
            $t->set_file('menu','blockmenu.thtml');

            $links = "<li><a href=\"{$_CONF['site_url']}/forum/index.php\" TITLE=\"{$LANG_GF02['msg196']}\">{$LANG_GF01['INDEXPAGE']}</a></li>" . LB;
            if ($_USER['uid'] > 1) {
                $links .= "<li><a href=\"{$_CONF['site_url']}/forum/index.php?op=markallread\" TITLE=\"{$LANG_GF02['msg197']}\">{$LANG_GF01['MARKALLREAD']}</a></li>" . LB;
                $links .= "<li><a href=\"{$_CONF['site_url']}/forum/userprefs.php\" TITLE=\"{$LANG_GF02['msg198']}\">{$LANG_GF01['USERPREFS']}</a></li>" . LB;
                $links .= "<li><a href=\"{$_CONF['site_url']}/forum/notify.php\" TITLE=\"{$LANG_GF02['msg199']}\">{$LANG_GF01['SUBSCRIPTIONS']}</a></li>" . LB;
            }
            $links .= "<li><a href=\"{$_CONF['site_url']}/forum/memberlist.php\" TITLE=\"{$LANG_GF02['msg200']}\">{$LANG_GF02['msg88']}</a></li>" . LB;
            $links .= "<li><a href=\"{$_CONF['site_url']}/forum/index.php?op=popular\" TITLE=\"{$LANG_GF02['msg201']}\">{$LANG_GF07['3']}</a></li>" . LB;

            $t->set_var('menuitems', $links);
            $t->parse ('output', 'menu');
            $retval .= $t->finish ($t->get_var('output'));
            $retval .= COM_endBlock('glmenu/blockfooter.thtml');
        }

    } else {
        $retval = '<table width="100%" border="0" cellpadding="1" cellspacing="1"><tr><td>';
        $retval .= "<a href=\"{$_CONF['site_url']}/forum/index.php\" TITLE=\"{$LANG_GF02['msg196']}\">{$LANG_GF01['INDEXPAGE']}</a>";
        $retval .= '</td></tr><tr><td>';
        if ($_USER['uid'] > 1) {
            $retval .= "<a href=\"{$_CONF['site_url']}/forum/index.php?op=markallread\" TITLE=\"{$LANG_GF02['msg197']}\">{$LANG_GF01['MARKALLREAD']}</a>";
            $retval .= '</td></tr><tr><td>';
            $retval .= "<a href=\"{$_CONF['site_url']}/forum/userprefs.php\" TITLE=\"{$LANG_GF02['msg198']}\">{$LANG_GF01['USERPREFS']}</a>";
            $retval .= '</td></tr><tr><td>';
            $retval .= "<a href=\"{$_CONF['site_url']}/forum/notify.php\" TITLE=\"{$LANG_GF02['msg199']}\">{$LANG_GF01['SUBSCRIPTIONS']}</a>";
            $retval .= '</td></tr><tr><td>';
        }
        $retval .= "<a href=\"{$_CONF['site_url']}/forum/memberlist.php\" TITLE=\"{$LANG_GF02['msg200']}\">{$LANG_GF02['msg88']}</a>";
        $retval .= '</td></tr><tr><td>';
        $retval .= "<a href=\"{$_CONF['site_url']}/forum/index.php?op=popular\" TITLE=\"{$LANG_GF02['msg201']}\">{$LANG_GF07['3']}</a>";
        $retval .= '</td></tr></table>';
    }
    return $retval;

}


function glmenu_showforums() {
    global $_CONF,$_TABLES,$CONF_GLMENU;

    $retval = '';

    $qforums = DB_query("SELECT forum_name,forum_id,grp_id FROM {$_TABLES['gf_forums']} ORDER BY forum_cat, forum_order ASC");
    while (list($forum_name,$forum_id, $forum_grp) = DB_fetchArray($qforums)) {
        $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='$forum_grp'");
        if (SEC_inGroup($groupname)) {
            if ($CONF_GLMENU['milonicmode']) {
                $retval .= 'aI("text='.$forum_name.';url='.$_CONF['site_url'].'/forum/index.php?forum='.$forum_id.';");';
            } else {
                $retval .= "<li><a href=\"{$_CONF['site_url']}/forum/index.php?forum={$forum_id}\">$forum_name</a></li>";
            }
        }
    }
    return $retval;
}
?>