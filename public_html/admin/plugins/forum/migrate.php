<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | Story Migration Utility for Geeklog to the Forum                          |
// | Forum admin program to migrate stories to the forum                       |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Script Author                                                             |
// | Blaine Lang,                  blaine@portalparts.com, www.portalparts.com |
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


include_once('gf_functions.php');
require_once($_CONF['path'] . 'plugins/forum/debug.php');  // Common Debug Code

if ($_POST['migrate'] == $LANG_GF01['MIGRATE_NOW'] AND $_POST['selforum'] != "select" AND !empty( $_POST['cb_chkentry']) ) {
    $num_stories = 0;
    $num_posts = 0;
    $forum = COM_applyFilter($_POST['selforum']);
    foreach($_POST['cb_chkentry'] as $sid ) {
        if($_POST['seltopic'] == 'submissions') {
            $topic = DB_getItem($_TABLES['storysubmission'],"tid","sid='$sid'");
            //echo "<br>Migrating SID:$sid for Topic: $topic to Forum: $forum";
            $sql = DB_query("SELECT sid,tid,date,uid,title,introtext from {$_TABLES['storysubmission']} WHERE sid='$sid'");
            list($sid,$tid,$storydate,$uid,$subject,$introtext) = DB_fetchARRAY($sql);
            $num_posts = migratetopic($forum,$sid,$tid,$storydate,$uid,$subject,$introtext,'','0') + $num_posts;
            $num_stories++;
            if( $_POST['delPostMigrate'] == 1) {
                DB_query("DELETE FROM {$_TABLES['storysubmission']} WHERE sid='$sid'");
            }

        } else {
            $topic = DB_getItem($_TABLES['stories'],"tid","sid='$sid'");
            //echo "<br>Migrating SID:$sid for Topic: $topic to Forum: $forum";
            $sql = DB_query("SELECT sid,tid,date,uid,title,introtext,bodytext,hits from {$_TABLES['stories']} WHERE sid='$sid'");
            list($sid,$tid,$storydate,$uid,$subject,$introtext,$bodytext,$hits) = DB_fetchARRAY($sql);
            $num_posts = migratetopic($forum,$sid,$tid,$storydate,$uid,$subject,$introtext,$bodytext,$hits) + $num_posts;
            $num_stories++;
            if( $_POST['delPostMigrate'] == 1) {
                migrate_deletestory($sid);
            }
       }
    }
    gf_resyncforum($forum);
    echo COM_refresh($_CONF['site_admin_url'] . "/plugins/forum/migrate.php?num_stories=". $num_stories. "&num_posts=".$num_posts);
    exit;
}

function migratetopic($forum,$sid,$tid,$storydate,$uid,$subject,$introtext,$bodytext,$hits) {
    global $_TABLES;
    $comment = $introtext . $bodytext;
    $comment = prepareStringForDB($comment);
    $subject = prepareStringForDB($subject);
    $postmode = "HTML";
    $name = DB_getITEM($_TABLES['users'],'username',"uid=$uid");
    $email = DB_getITEM($_TABLES['users'],'email',"uid=$uid");
    $website = DB_getITEM($_TABLES['users'],'homepage',"uid=$uid");

    $datetime = explode(" ", $storydate);
    $date = explode("-",$datetime[0]);
    $time = explode(":",$datetime[1]);
    $year  = ($date[0] > 1969) ? $date[0] : "2001";
    $month = $date[1];
    $day   = $date[2];
    $hour  = $time[0];
    $min   = $time[1];
    $timestamp = mktime($hour,$min,0,$month,$day,$year);

    DB_query("INSERT INTO {$_TABLES['gf_topic']} (forum,name,date,lastupdated, email, website, subject, comment, views, postmode, ip, mood, uid, pid, sticky, locked)
        VALUES ('$forum','$name','$timestamp','$timestamp','$email','$website','$subject','$comment','$hits','$postmode','','','$uid','0','0','0')");
    $parent = DB_insertID();
    $i++;
    $comments = 0;
    if($_POST['seltopic'] != 'submissions') {
    $comments = migrateComments($forum,$sid, $parent);
    }
    $num_posts = $num_posts + $comments;
    return $num_posts;
}


function migrateComments($forum,$sid, $parent) {
    global $verbose,$_TABLES,$_CONF,$migratedcomments;
    $sql = DB_query("SELECT sid,date,uid,title,comment from {$_TABLES['comments']} WHERE sid = '".$sid."' ORDER BY date ASC");
    $num_comments = DB_numROWS($sql);
    if ($verbose) {
        echo "Found $num_comments Comments to migrate for this topic";
    }
    $i = 0;
    while ( list($sid,$commentdate,$uid,$subject,$comment) = DB_fetchARRAY($sql)) {

        $sqlid = DB_query("SELECT id from {$_TABLES['gf_topic']} ORDER BY id desc LIMIT 1");
        list ($lastid) = DB_fetchARRAY($sqlid);

        $comment = prepareStringForDB($comment);
        $subject = prepareStringForDB($subject);
        $postmode = "HTML";
        $name = DB_getITEM($_TABLES['users'],'username',"uid=$uid");
        $email = DB_getITEM($_TABLES['users'],'email',"uid=$uid");
        $website = DB_getITEM($_TABLES['users'],'homepage',"uid=$uid");

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

        DB_query("INSERT INTO {$_TABLES['gf_topic']} (forum,name,date,lastupdated, email, website, subject, comment, postmode, ip, mood, uid, pid, sticky, locked)
            VALUES ('$forum','$name','$timestamp','$lastupdated','$email','$website','$subject','$comment','$postmode','','','$uid','$parent','0','0')");
        $i++;
    }

    DB_query("UPDATE {$_TABLES['gf_topic']} SET replies = $num_comments WHERE id=$parent");
    return $num_comments;
}

function prepareStringForDB($message,$postmode="html",$censor=TRUE,$htmlfilter=TRUE) {
    global $CONF_FORUM;

    if ($censor) {
        $message = COM_checkWords($message);
    }
    if($postmode == 'html') {
        if ($htmlfilter) {
            // Need to call addslahes again as COM_checkHTML stips it out
            $message = addslashes(COM_checkHTML($message));
        } elseif (!get_magic_quotes_gpc() ) {
            $message = addslashes($message);
        }    
    } else {
        if(get_magic_quotes_gpc() ) {
            $message = @htmlspecialchars($message,ENT_QUOTES,$CONF_FORUM['charset']);
        } else {    
            $message = addslashes(@htmlspecialchars($message,ENT_QUOTES,$CONF_FORUM['charset']));
        }    
    }    
    return $message;
}


function migrate_topicsList($selected='') {
    global $_TABLES,$LANG_GF01;

    $retval = '<select name="seltopic"><option value="all">'.$LANG_GF01['ALL'].'</option>' . LB;
    $retval .= '<option value="submissions"';
    if($selected == "submissions") {
        $retval .= ' selected="selected"';
    }
    $retval .= '>'.$LANG_GF01['SUBMISSIONS'].'</option>' .LB;

    $result = DB_query( "SELECT tid,topic FROM {$_TABLES['topics']} ORDER BY topic" );
    $nrows = DB_numRows( $result );

    for( $i = 0; $i < $nrows; $i++ )
    {
        $A = DB_fetchArray( $result );
        $retval .= '<option value="' . $A[0] . '"';

        if( $A[0] == $selected )
        {
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

    $result = DB_query ("SELECT ai_filename FROM {$_TABLES['article_images']} WHERE ai_sid = '$sid'");
    $nrows = DB_numRows ($result);
    for ($i = 1; $i <= $nrows; $i++) {
        $A = DB_fetchArray ($result);
        $filename = $_CONF['path_html'] . 'images/articles/' . $A['ai_filename'];
        if (!@unlink ($filename)) {
            // log the problem but don't abort the script
            echo COM_errorLog ('Unable to remove the following image from the article: ' . $filename);
        }

        // remove unscaled image, if it exists
        $lFilename_large = substr_replace ($A['ai_filename'], '_original.',
                                           strrpos ($A['ai_filename'], '.'), 1);
        $lFilename_large_complete = $_CONF['path_html'] . 'images/articles/'
                                  . $lFilename_large;
        if (file_exists ($lFilename_large_complete)) {
            if (!@unlink ($lFilename_large_complete)) {
                // again, log the problem but don't abort the script
                echo COM_errorLog ('Unable to remove the following image from the article: ' . $lFilename_large_complete);
            }
        }
    }
    DB_delete ($_TABLES['article_images'], 'ai_sid', $sid);
    DB_delete ($_TABLES['comments'], 'sid', $sid);
    DB_delete ($_TABLES['stories'], 'sid', $sid);

    // update RSS feed and Older Stories block
    COM_rdfUpToDateCheck ();
    COM_olderStuff ();

    return;
}



echo COM_siteHeader();

// Check if the number of records was specified to show
$page = COM_applyFilter($_GET['page'],true);
$show = COM_applyFilter($_GET['show'],true);
if (empty($show)) {
    $show = 20;
}
// Check if this is the first page.
if (empty($page)) {
    $page = 1;
}

echo COM_startBlock($LANG_GF02['msg193']);
echo ppNavbar($navbarMenu,$LANG_GF06['5']);
$p= new Template($_CONF['path_layout'] . 'forum/layout/admin');
$p->set_file (array ('page'=>'migratestories.thtml','records' => 'migrate_records.thtml'));

if (!empty($_GET['num_stories'])) {
    $p->set_var ('status_message',sprintf($LANG_GF02['msg192'],$_GET['num_stories'],$_GET['num_posts']));
} else {
    $p->set_var ('show_message','none');
}

if (!empty($_POST['seltopic']) AND $_POST['seltopic'] != 'all') {
    $curtopic = $_POST['seltopic']; 
    if($_POST['seltopic'] == "submissions") {
        $sql = "select tid,sid,title,date, 0 as comments from {$_TABLES['storysubmission']}";
        $countsql = DB_query("SELECT COUNT(*) FROM {$_TABLES['storysubmission']}");
    } else {
        $sql = "select tid,sid,title,date,comments from {$_TABLES['stories']} where tid='$curtopic'";
        $countsql = DB_query("SELECT COUNT(*) FROM {$_TABLES['stories']} where tid='$curtopic'");
    }

} else {
    $sql = "select tid,sid,title,date,comments from {$_TABLES['stories']}";
    $countsql = DB_query("SELECT COUNT(*) FROM {$_TABLES['stories']}");
}

list($maxrows) = DB_fetchArray($countsql);
$numpages = ceil($maxrows / $show);
$offset = ($page - 1) * $show;

$sql .= " ORDER BY sid DESC LIMIT $offset, $show";

$result  = DB_query($sql);
$numrows = DB_numROWS($result);

$p->set_var ('action_url', $_CONF['site_admin_url'] . '/plugins/forum/migrate.php');
$p->set_var ('filter_topic_selection',migrate_topicsList($curtopic));
$p->set_var ('select_filter_options',COM_optionList($_TABLES['gf_forums'], "forum_id,forum_name",$_POST['selforum']));
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
    $base_url = $_CONF['site_admin_url'] . '/plugins/forum/migrate.php?tid='.$tid;
    for ($i = 0; $i < $numrows; $i++) {
        list($topic,$sid,$story,$date,$comments) = DB_fetchArray($result);
        $p->set_var ('sid',$sid);
        $p->set_var ('topic',stripslashes($topic));
        if($_POST['seltopic'] == "submissions") {
            $p->set_var ('story_link', $_CONF['site_admin_url'] . '/story.php?mode=editsubmission&id=' . $sid);
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
echo $p->finish ($p->get_var('output'));

echo COM_endBlock();
echo COM_siteFooter();

?>