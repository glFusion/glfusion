<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | migrate.php                                                              |
// |                                                                          |
// | Story Migration Utility for glFusion to the Forum                        |
// | Forum admin program to migrate stories to the forum                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('forum.edit')) {
  $display = COM_siteHeader();
  $display .= COM_startBlock($LANG_GF00['access_denied']);
  $display .= $LANG_GF00['admin_only'];
  $display .= COM_endBlock();
  $display .= COM_siteFooter(true);
  echo $display;
  exit();
}

USES_forum_functions();
USES_forum_format();
USES_forum_admin();

if (isset($_POST['migrate']) && $_POST['migrate'] == $LANG_GF01['MIGRATE_NOW'] AND $_POST['selforum'] != "select" AND !empty( $_POST['cb_chkentry']) ) {
    $num_stories = 0;
    $num_posts = 0;
    $forum = COM_applyFilter($_POST['selforum'],true);
    if ($forum == 0 ) {
        COM_setMsg( $LANG_GF01['SELECTFORUM'], 'error',true );
        echo COM_refresh($_CONF['site_admin_url'] . "/plugins/forum/migrate.php");
    }
    foreach($_POST['cb_chkentry'] as $sid ) {
        if($_POST['seltopic'] == 'submissions') {
            $topic = DB_getItem($_TABLES['storysubmission'],"tid","sid='".DB_escapeString($sid)."'");
            $sql = DB_query("SELECT sid,tid,date,uid,title,introtext from {$_TABLES['storysubmission']} WHERE sid='".DB_escapeString($sid)."'");
            list($sid,$tid,$storydate,$uid,$subject,$introtext) = DB_fetchArray($sql);
            $num_posts = _ff_migratetopic($forum,$sid,$tid,$storydate,$uid,$subject,$introtext,'','0') + $num_posts;
            $num_stories++;
            if( $_POST['delPostMigrate'] == 1) {
                DB_query("DELETE FROM {$_TABLES['storysubmission']} WHERE sid='".DB_escapeString($sid)."'");
            }

        } else {
            $topic = DB_getItem($_TABLES['stories'],"tid","sid='".DB_escapeString($sid)."'");
            $sql = DB_query("SELECT sid,tid,date,uid,title,introtext,bodytext,hits from {$_TABLES['stories']} WHERE sid='".DB_escapeString($sid)."'");
            list($sid,$tid,$storydate,$uid,$subject,$introtext,$bodytext,$hits) = DB_fetchArray($sql);
            $num_posts = _ff_migratetopic($forum,$sid,$tid,$storydate,$uid,$subject,$introtext,$bodytext,$hits) + $num_posts;
            $num_stories++;
            if( isset($_POST['delPostMigrate']) && $_POST['delPostMigrate'] == 1) {
                migrate_deletestory($sid);
            }
       }
    }
    $msg = sprintf($LANG_GF02['msg192'],$num_stories,$num_posts);
    COM_setMsg( $msg, 'error',true );
    gf_resyncforum($forum);
    CACHE_clear();
    echo COM_refresh($_CONF['site_admin_url'] . "/plugins/forum/migrate.php?num_stories=". $num_stories. "&num_posts=".$num_posts);
    exit;
}

function _ff_migratetopic($forum,$sid,$tid,$storydate,$uid,$subject,$introtext,$bodytext,$hits) {
    global $_TABLES;

    $num_posts = 0;

    $comment = $introtext . $bodytext;
    $comment = prepareStringForDB($comment);
    $subject = prepareStringForDB($subject);
    $postmode = "html";
    $name       = DB_getITEM($_TABLES['users'],'username',"uid=".(int) $uid);
    $email      = DB_getITEM($_TABLES['users'],'email',"uid=".(int) $uid);
    $website    = DB_getITEM($_TABLES['users'],'homepage',"uid=".(int) $uid);

    $datetime = explode(" ", $storydate);
    $date = explode("-",$datetime[0]);
    $time = explode(":",$datetime[1]);
    $year  = ($date[0] > 1969) ? $date[0] : "2001";
    $month = $date[1];
    $day   = $date[2];
    $hour  = $time[0];
    $min   = $time[1];
    $timestamp = mktime($hour,$min,0,$month,$day,$year);

    DB_query("INSERT INTO {$_TABLES['ff_topic']} (forum,name,date,lastupdated, email, website, subject, comment, views, postmode, ip, mood, uid, pid, sticky, locked)
        VALUES (".(int) $forum.",'".DB_escapeString($name)."','$timestamp','$timestamp','".DB_escapeString($email)."','".DB_escapeString($website)."','$subject','$comment',".(int) $hits.",'".DB_escapeString($postmode)."','','',".(int) $uid.",'0','0','0')");
    $parent = DB_insertID();
    $comments = 0;
    if (isset($_POST['seltopic']) && $_POST['seltopic'] != 'submissions') {
        $comments = _ff_migrateComments($forum,$sid, $parent);
    }
    $num_posts = $num_posts + $comments;

    return $num_posts;
}


function _ff_migrateComments($forum,$sid, $parent) {
    global $verbose,$_TABLES,$_CONF,$migratedcomments;

    $sql = DB_query("SELECT sid,date,uid,title,comment from {$_TABLES['comments']} WHERE sid = '".DB_escapeString($sid)."' ORDER BY date ASC");
    $num_comments = DB_numRows($sql);
    $i = 0;
    while ( list($sid,$commentdate,$uid,$subject,$comment) = DB_fetchArray($sql)) {

        $sqlid = DB_query("SELECT id from {$_TABLES['ff_topic']} ORDER BY id desc LIMIT 1");
        list ($lastid) = DB_fetchArray($sqlid);

        $comment = prepareStringForDB($comment);
        $subject = prepareStringForDB($subject);
        $postmode = "html";
        $name     = DB_getITEM($_TABLES['users'],'username',"uid=".(int) $uid);
        $email    = DB_getITEM($_TABLES['users'],'email',"uid=".(int) $uid);
        $website  = DB_getITEM($_TABLES['users'],'homepage',"uid=".(int) $uid);

        $datetime = explode(" ", $commentdate);
        $date = explode("-",$datetime[0]);
        $time = explode(":",$datetime[1]);
        $year  = ($date[0] > 1969) ? $date[0] : "2001";
        $month = $date[1];
        $day   = $date[2];
        $hour  = $time[0];
        $min   = $time[1];
        $timestamp = mktime($hour,$min,0,$month,$day,$year);
        $lastupdated = $timestamp;
        $migratedcomments++;

        DB_query("INSERT INTO {$_TABLES['ff_topic']} (forum,name,date,lastupdated, email, website, subject, comment, postmode, ip, mood, uid, pid, sticky, locked)
            VALUES (".(int) $forum.",'".DB_escapeString($name)."','$timestamp','$lastupdated','".DB_escapeString($email)."','".DB_escapeString($website)."','$subject','$comment','".DB_escapeString($postmode)."','','',".(int) $uid.",".(int) $parent.",'0','0')");
        $i++;
    }

    DB_query("UPDATE {$_TABLES['ff_topic']} SET replies = $num_comments WHERE id=".(int) $parent);
    return $num_comments;
}

function prepareStringForDB($message,$postmode="html",$censor=TRUE,$htmlfilter=TRUE) {
    global $_FF_CONF;

    if ($censor) {
        $message = COM_checkWords($message);
    }
    if($postmode == 'html') {
        if ($htmlfilter) {
            // Need to call addslahes again as COM_checkHTML stips it out
            $message = DB_escapeString(COM_checkHTML($message));
        } else {
            $message = DB_escapeString($message);
        }
    } else {
        $message = DB_escapeString(@htmlspecialchars($message,ENT_QUOTES,COM_getEncodingt()));
    }
    return $message;
}


function _ff_migrate_topicsList($selected='') {
    global $_TABLES,$LANG_GF01;

    $retval = '<select name="seltopic"><option value="all">'.$LANG_GF01['ALL'].'</option>' . LB;
    $retval .= '<option value="submissions"';
    if ($selected == "submissions") {
        $retval .= ' selected="selected"';
    }
    $retval .= '>'.$LANG_GF01['SUBMISSIONS'].'</option>' .LB;

    $result = DB_query( "SELECT tid,topic FROM {$_TABLES['topics']} ORDER BY topic" );
    $nrows = DB_numRows( $result );

    for( $i = 0; $i < $nrows; $i++ )
    {
        $A = DB_fetchArray( $result );
        $retval .= '<option value="' . $A[0] . '"';

        if ( $A[0] == $selected ) {
            $retval .= ' selected="selected"';
        }

        $retval .= '>' . $A[1] . '</option>' . LB;
    }
    $retval .= '</select>';

    return $retval;
}


function migrate_deletestory ($sid)
{
    global $_TABLES, $_CONF;

    $result = DB_query ("SELECT ai_filename FROM {$_TABLES['article_images']} WHERE ai_sid='".DB_escapeString($sid)."'");
    $nrows = DB_numRows ($result);
    for ($i = 1; $i <= $nrows; $i++) {
        $A = DB_fetchArray ($result);
        $filename = $_CONF['path_html'] . 'images/articles/' . $A['ai_filename'];
        if (!@unlink ($filename)) {
            // log the problem but don't abort the script
            COM_errorLog ('Unable to remove the following image from the article: ' . $filename);
        }

        // remove unscaled image, if it exists
        $lFilename_large = substr_replace ($A['ai_filename'], '_original.',
                                           strrpos ($A['ai_filename'], '.'), 1);
        $lFilename_large_complete = $_CONF['path_html'] . 'images/articles/'
                                  . $lFilename_large;
        if (file_exists ($lFilename_large_complete)) {
            if (!@unlink ($lFilename_large_complete)) {
                // ;og the problem but don't abort the script
                COM_errorLog ('Unable to remove the following image from the article: ' . $lFilename_large_complete);
            }
        }
    }
    DB_delete ($_TABLES['article_images'], 'ai_sid', DB_escapeString($sid));
    DB_delete ($_TABLES['comments'], 'sid', DB_escapeString($sid));
    DB_delete ($_TABLES['stories'], 'sid', DB_escapeString($sid));

    // update RSS feed and Older Stories block
    COM_rdfUpToDateCheck ();
    COM_olderStuff ();

    return;
}

$display = FF_siteHeader();

// Check if the number of records was specified to show
$page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
$show = isset($_GET['show']) ? COM_applyFilter($_GET['show'],true) : 0;
if (empty($show)) {
    $show = 20;
}
// Check if this is the first page.
if (empty($page) || $page < 1) {
    $page = 1;
}

$display .= FF_Navbar($navbarMenu,$LANG_GF06['5']);
$display .= COM_startBlock($LANG_GF02['msg193']);

$p = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
$p->set_file (array ('page'=>'migratestories.thtml','records' => 'migrate_records.thtml'));

if (!empty($_GET['num_stories'])) {
    $p->set_var ('status_message',sprintf($LANG_GF02['msg192'],$_GET['num_stories'],$_GET['num_posts']));
} else {
    $p->set_var ('show_message','none');
}

if (!empty($_REQUEST['seltopic']) AND $_REQUEST['seltopic'] != 'all') {
    $curtopic = $_REQUEST['seltopic'];
    if ($_REQUEST['seltopic'] == "submissions") {
        $sql = "select tid,sid,title,date, 0 as comments from {$_TABLES['storysubmission']}";
        $countsql = DB_query("SELECT COUNT(*) FROM {$_TABLES['storysubmission']}");
    } else {
        $sql = "select tid,sid,title,date,comments from {$_TABLES['stories']} where tid='".DB_escapeString($curtopic)."'";
        $countsql = DB_query("SELECT COUNT(*) FROM {$_TABLES['stories']} where tid='".DB_escapeString($curtopic)."'");
    }

} else {
    $curtopic = '';
    $sql = "select tid,sid,title,date,comments from {$_TABLES['stories']}";
    $countsql = DB_query("SELECT COUNT(*) FROM {$_TABLES['stories']}");
}

list($maxrows) = DB_fetchArray($countsql);
$numpages = ceil($maxrows / $show);
$offset = ($page - 1) * $show;

$sql .= " ORDER BY sid DESC LIMIT $offset, $show";

$result  = DB_query($sql);
$numrows = DB_numRows($result);

$selectedForum = isset($_POST['selforum']) ? COM_applyFilter($_POST['selforum']) : '';

$p->set_var ('action_url', $_CONF['site_admin_url'] . '/plugins/forum/migrate.php');
$p->set_var ('filter_topic_selection',_ff_migrate_topicsList($curtopic));
$p->set_var ('select_filter_options',COM_optionList($_TABLES['ff_forums'], "forum_id,forum_name",$selectedForum));
$p->set_var ('LANG_migrate',$LANG_GF01['MIGRATE_NOW']);
$p->set_var ('LANG_filterlist',$LANG_GF01['FILTERLIST']);
$p->set_var ('LANG_selectforum',$LANG_GF01['SELECTFORUM']);
$p->set_var ('LANG_deleteafter',$LANG_GF01['DELETEAFTER']);
$p->set_var ('LANG_all',$LANG_GF01['ALL']);
$p->set_var ('LANG_topic',$LANG_GF01['TOPIC']);
$p->set_var ('LANG_title',$LANG_GF01['TITLE']);
$p->set_var ('LANG_date',$LANG_GF01['DATE']);
$p->set_var ('LANG_comments',$LANG_GF01['COMMENTS']);

if ($numrows > 0) {
    $base_url = $_CONF['site_admin_url'] . '/plugins/forum/migrate.php?seltopic='.$curtopic;
    for ($i = 0; $i < $numrows; $i++) {
        list($topic,$sid,$story,$date,$comments) = DB_fetchArray($result);
        $p->set_var ('sid',$sid);
        $p->set_var ('topic',$topic);
        if (isset($_POST['seltopic']) && $_POST['seltopic'] == "submissions") {
            $p->set_var ('story_link', $_CONF['site_admin_url'] . '/story.php?moderate=x&amp;sid=' . $sid);
        } else {
            $p->set_var ('story_link', $_CONF['site_url'] . '/article.php?story=' . $sid);
        }
        $p->set_var ('story_title',$story);
        $p->set_var ('date',$date);
        $p->set_var ('num_comments',$comments);
        $p->set_var ('cssid', ($i%2)+1);
        $p->parse('story_record','records',true);
    }
    $p->set_var ('page_navigation',COM_printPageNavigation($base_url,$page,$numpages));
}
$p->parse ('output', 'page');
$display .= $p->finish ($p->get_var('output'));

$display .= COM_endBlock();
$display .= FF_adminfooter();
$display .= FF_siteFooter();
echo $display;
?>