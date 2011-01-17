<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | phpbb3_migrate.php                                                       |
// |                                                                          |
// | Migrate phpBB3 Forum to glFusion's Forum Plugin                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

if (!SEC_inGroup('Root')) {
    echo COM_siteHeader();
    echo COM_startBlock("Error");
    echo 'You do not have permission to perform this operation';
    echo COM_endBlock();
    echo COM_siteFooter();
    exit();
}

$categoriesImported = 0;
$forumsImported     = 0;
$topicsImported     = 0;
$usersImported      = 0;

/*
 * Get the phpBB3 Database info and import options
 */

function phpbb3_getInfo( $msg = '' ) {
    global $_phpbb_db_host,$_phpbb_db_user,$_phpbb_db_pass,$_phpbb_db_name,$_phpbb_db_prefix,$_purge_glfusion_forum;
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file (array ('phpbb3_db_info'=>'phpbb3_migrate_db.thtml'));

    $T->set_var(array(
        'site_url'            => $_CONF['site_url'],
        'form_action'         => $_CONF['site_admin_url'].'/plugins/forum/phpbb3_migrate.php',
        'mode'                => 'dbinfo',
        'phpbb3dbserver'      => $_phpbb_db_host,
        'phpbb3dbuser'        => $_phpbb_db_user,
        'phpbb3dbpass'        => $_phpbb_db_pass,
        'phpbb3dbname'        => $_phpbb_db_name,
        'phpbb3dbprefix'      => $_phpbb_db_prefix,
        'purgeglfusionforums' => $_purge_glfusion_forum ? 'checked="checked"' : '',
    ));
    if ( $msg != '' ) {
        $T->set_var('errormessage',$msg);
    }

    $T->parse ('output', 'phpbb3_db_info');
    $retval = $T->finish ($T->get_var('output'));

    return $retval;
}

/**
 * migrate a specific phpbb3 forum
 */

function phpbb3_migrateForum( $forum_id, $glfusion_forum_id ) {
    global $DB_phpBB, $DB_glFusion, $_TABLES, $topicsImported;

    $retval = '';

    // grab all the topics
    $sql        = "SELECT topic_id,topic_title,topic_first_post_id,topic_views,topic_replies,topic_type from {$_TABLES['phpbb_topics']} WHERE forum_id='$forum_id' ";
    $topics     = mysql_query($sql, $DB_phpBB);
    while (list ($topic_id,$topic_title,$topic_pid,$views,$replies,$topic_type) = @mysql_fetch_array($topics) )  {
        // process the first topic (based on $topic_pid)
        $sql  = "SELECT a.post_id, a.poster_id, c.username_clean, a.post_time, a.post_username, a.poster_ip, a.post_subject, a.post_text, a.bbcode_uid ";
        $sql .= "FROM {$_TABLES['phpbb_posts']} a ";
        $sql .= "LEFT JOIN {$_TABLES['phpbb_users']} c on c.user_id=a.poster_id ";
        $sql .= "WHERE a.topic_id='$topic_id' AND a.post_id='$topic_pid'";
        $parent_post = mysql_query($sql, $DB_phpBB);
        if ( mysql_num_rows($parent_post) > 0 ) {
            list ($post_id,$memberid,$membername,$post_time,$membername,$ip,$subject,$message,$bbcode_uid) = @mysql_fetch_array($parent_post);
            $topicsImported++;
            // Check to see if the user exists in the glFusion user table
            $glf_user_id = DB_getItem($_TABLES['users'],'uid','username="'.DB_escapeString($membername).'"');
            if ( $glf_user_id < 2 ) {
                $glf_user_id = 1;
            }

            $message = _convertPost( $message, $bbcode_uid);

            $subject = DB_escapeString($subject);
            $message = DB_escapeString($message);
            $membername = DB_escapeString($membername);
            if ( $topic_type > 0 ) {
                $sticky = 1;
            } else {
                $sticky = 0;
            }

            $sql  = "INSERT INTO {$_TABLES['gf_topic']} (forum,pid,uid,name,date,subject,comment,ip,views,replies,mood,sticky) ";
            $sql .= "VALUES ('$glfusion_forum_id','0','$glf_user_id','$membername','$post_time','$subject','$message','$ip','$views','$replies','',$sticky)";
            if (!@mysql_query($sql,$DB_glFusion) ) {
                die('SQL Error:<br />' . mysql_error($DB_glFusion) . '<br />SQL: ' . $sql );
            }
            $glfusion_pid = @mysql_insert_id($DB_glFusion);

            // now get the children posts for this topic

            $sql  = "SELECT a.post_id, a.poster_id, c.username, a.post_time, a.post_username, a.poster_ip, a.post_subject, a.post_text, a.bbcode_uid ";
            $sql .= "FROM {$_TABLES['phpbb_posts']} a ";
            $sql .= "LEFT JOIN {$_TABLES['phpbb_users']} c on c.user_id=a.poster_id ";
            $sql .= "WHERE a.topic_id='$topic_id'  AND a.post_id <>'$topic_pid'";
            $posts = mysql_query($sql, $DB_phpBB);

            while (list ($post_id,$memberid,$membername,$post_time,$membername,$ip,$subject,$message,$bbcode_uid) = @mysql_fetch_array($posts) )  {
                $topicsImported++;
                // Check to see if the user exists in the glFusion user table
                $glf_user_id = DB_getItem($_TABLES['users'],'uid','username="'.DB_escapeString($membername).'"');
                if ( $glf_user_id < 2 ) {
                    $glf_user_id = 1;
                }

                $message = _convertPost( $message, $bbcode_uid);

                $subject = DB_escapeString($subject);
                $message = DB_escapeString($message);
                $membername = DB_escapeString($membername);

                $sql  = "INSERT INTO {$_TABLES['gf_topic']} (forum,pid,uid,name,date,subject,comment,ip,views,replies,mood) ";
                $sql .= "VALUES ('$glfusion_forum_id','$glfusion_pid','$glf_user_id','$membername','$post_time','$subject','$message','$ip','$views','$replies','')";
                if (!mysql_query($sql,$DB_glFusion) ) {
                    die('SQL Error:<br />' . mysql_error($DB_glFusion) . '<br />SQL: ' . $sql );
                }
            }
        }
    }
    return $retval;
}


/**
 * perform all database connections
 */

function phpbb3_connect() {
    global $_DB_host, $_DB_user, $_DB_pass, $_DB_name;
    global $_phpbb_db_host, $_phpbb_db_user, $_phpbb_db_pass, $_phpbb_db_name;
    global $_CONF, $_TABLES;
    global $DB_glFusion, $DB_handle_glFusion;
    global $DB_phpBB, $DB_handle_phpBB3;


    /* Connect to the glFusion Database */
    $DB_glFusion = @mysql_connect($_DB_host,$_DB_user,$_DB_pass);
    if (!$DB_glFusion) {
        return 'Could not connect to the glFusion Database. Error:' . @mysql_error();
    }
    @mysql_query ("SET NAMES 'utf8'", $DB_glFusion);
    $DB_handle_glFusion = @mysql_select_db($_DB_name,$DB_glFusion);
    if (!$DB_handle_glFusion) {
       return 'Error selecting the glFusion database $_DB_name : ' . @mysql_error();
    }
    /* Connect to the phpbb Database */
    $DB_phpBB = @mysql_connect($_phpbb_db_host,$_phpbb_db_user,$_phpbb_db_pass,true);
    if (!$DB_phpBB) {
        return 'Could not connect to the phpbb Database. Error:' . @mysql_error($DB_phpBB);
    }
    @mysql_query ("SET NAMES 'utf8'", $DB_phpBB);
    $DB_handle_phpBB3 = @mysql_select_db($_phpbb_db_name,$DB_phpBB);
    if (!$DB_handle_phpBB3) {
       return "Error selecting the phpBB3 database $_phpbb_db_name : " . @mysql_error($DB_phpBB);
    }
    $result = @mysql_query("SELECT * FROM {$_TABLES['phpbb_forums']} LIMIT 1");
    if ( !$result  ) {
        return "Unable to connect to  {$_TABLES['phpbb_forums']} : " . @mysql_error($DB_phpBB);
    }
    return '';
}

/*
 * Purge all existing glFusion forum data
 */

function phpbb3_purge_glfusion_forum() {
    global $_TABLES, $DB_glFusion, $DB_phpBB, $_purge_glfusion_forum;

    // Delete all records in the forum
    if ($_purge_glfusion_forum) {
        if (!mysql_query("DELETE FROM {$_TABLES['gf_categories']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_forums']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_topic']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_attachments']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_bookmarks']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_log']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_moderators']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        if (!mysql_query("DELETE FROM {$_TABLES['gf_watch']}",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
    }
}

/*
 * Import the select categories and forums
 */

function phpbb3_import( ) {
    global $_CONF, $_TABLES;
    global $DB_phpBB, $DB_glFusion;
    global $categoriesImported, $forumsImported, $topicsImported;

    $retval = '';

    $categoryToImport = array();
    $categoryToImport = $_POST['category'];

    $forumToImport = array();
    $forumToImport = $_POST['forum'];

    // Get max id from existing glFusion Forum
    $order_result = mysql_query("SELECT MAX(cat_order) + 10 AS corder FROM {$_TABLES['gf_categories']}", $DB_glFusion);
    $order_row = mysql_fetch_array($order_result);
    $order = $order_row['corder'];

    // Create the Forum Categories
    $sql = "SELECT forum_id as cat_id, forum_name as cat_title, forum_desc FROM {$_TABLES['phpbb_forums']} WHERE forum_type = 0 ORDER BY left_id ASC";
    $categories = mysql_query($sql, $DB_phpBB);
    while ( list($id,$name,$desc) = @mysql_fetch_array($categories) )   {
        if ( !@in_array($id,$categoryToImport) ) {
            continue;
        }
        $categoriesImported++;
        $order+=10;
        $name = DB_escapeString($name);
        if (!mysql_query("INSERT INTO {$_TABLES['gf_categories']} (cat_name,cat_dscp,cat_order) VALUES ('$name','','$order')",$DB_glFusion)) {
            die('SQL Error:<br />' . mysql_error($DB_glFusion) );
        }
        // get glFusion Category id..
        $glFusion_Category_ID = @mysql_insert_id($DB_glFusion);

        // now process all the forums in this category...
        $forum_order = 0;
        $boards = mysql_query("SELECT forum_id,parent_id as cat_id,forum_name,forum_desc FROM {$_TABLES['phpbb_forums']} WHERE forum_type = 1 AND parent_id=".$id." ORDER BY left_id ASC", $DB_phpBB);
        while (list($forum,$cat,$forum_name,$forum_desc) = @mysql_fetch_array($boards) )   {
            if ( !@in_array($forum,$forumToImport) ) {
                continue;
            }
            $forumsImported++;
            $forum_order+=10;
            $forum_name = DB_escapeString($forum_name);
            $forum_desc = DB_escapeString($forum_desc);
            if (!mysql_query("INSERT INTO {$_TABLES['gf_forums']} (forum_cat,forum_name,forum_dscp,forum_order) VALUES ('$glFusion_Category_ID','$forum_name','$forum_desc','$forum_order')",$DB_glFusion) ) {
                die('SQL Error:<br />' . mysql_error($DB_glFusion) );
            }
            // get glFusion Forum ID
            $glFusion_Forum_ID = @mysql_insert_id($DB_glFusion);
            // now get all the topics for this forum...
            phpbb3_migrateForum($forum,$glFusion_Forum_ID );
            gf_resyncforum($glFusion_Forum_ID);
        }
    }
    CTL_clearCache();

    return $retval;
}


/**
 * start the import process
 */

function _processImport()
{
    global $_purge_glfusion_forum;
    global $_CONF, $_TABLES;
    global $DB_phpBB, $DB_glFusion;
    global $categoriesImported, $forumsImported, $topicsImported, $usersImported;

    $display = '';

    // Delete all records in the forum
    if ($_purge_glfusion_forum) {
        phpbb3_purge_glfusion_forum();
    }

    $importResults = phpbb3_import();

    mysql_close($DB_glFusion);
    mysql_close($DB_phpBB);

    CTL_clearCache();

    // display summary stats

    $display .= "<h1>phpBB3 Import Complete</h1>";
    $display .= '<table style="width:100%;border:none;">';
    $display .= '<tr><td style="width:25%;text-align:right;">';
    $display .= '<b>Categories Imported:&nbsp;</b></td><td style="text-align:left;">';
    $display .= $categoriesImported.'</td></tr>';

    $display .= '<tr><td style="width:25%;text-align:right;">';
    $display .= '<b>Forums Imported:&nbsp;</b></td><td style="text-align:left;">';
    $display .= $forumsImported.'</td></tr>';

    $display .= '<tr><td style="width:25%;text-align:right;">';
    $display .= '<b>Topics Imported:&nbsp;</b></td><td style="text-align:left;">';
    $display .= $topicsImported.'</td></tr>';

    $display .= '<tr><td style="width:25%;text-align:right;">';
    $display .= '<b>Users Imported:&nbsp;</b></td><td style="text-align:left;">';
    $display .= $usersImported.'</td></tr>';

    $display .= '</table>';

    $display .= '<div style="text-align:center;padding:5px;width:30%;"><a href="'.$_CONF['site_url'].'/forum/index.php">Forum Index</a></div>';

    return $display;
}

/**
 * Confirm the action we are about to take
 */

function _confirmImport()
{
    global $_phpbb_db_host, $_phpbb_db_user, $_phpbb_db_pass,
           $_phpbb_db_name, $_purge_glfusion_forum,$_phpbb_db_prefix,
           $_import_phpbb3_users, $_purge_glfusion_users, $_highest_uid,
           $_CONF, $_TABLES, $DB_phpBB;

    $retval = '';

    // validate the DB credentials
    $rc = phpbb3_connect();
    if ( $rc != '' ) {
        $retval = phpbb3_getInfo($rc);
        return $retval;
    }

    $selection = '';

    $i=0;
    $sql = "SELECT forum_id as cat_id, forum_name as cat_title, forum_desc FROM {$_TABLES['phpbb_forums']} WHERE forum_type = 0 ORDER BY left_id ASC";
    $categories = mysql_query($sql, $DB_phpBB);
    while ( list($id,$name,$desc) = @mysql_fetch_array($categories) )   {
        $phpbb3_category[$i]['id'] = $id;
        $phpbb3_category[$i]['name'] = $name;
        $phpbb3_category[$i]['forums'] = array();

        $x = 0;
        $boards = mysql_query("SELECT forum_id,parent_id as cat_id,forum_name,forum_desc FROM {$_TABLES['phpbb_forums']} WHERE forum_type = 1 AND parent_id=".$id." ORDER BY left_id ASC", $DB_phpBB);
        while (list($forum,$cat,$name,$description) = @mysql_fetch_array($boards) )   {
            $phpbb3_category[$i]['forums'][$x]['id'] = $forum;
            $phpbb3_category[$i]['forums'][$x]['name'] = $name;
            $x++;
        }
        $i++;
    }

    $total_categories = count($phpbb3_category);
    for ( $z=0;$z<$total_categories;$z++) {
        $selection .= '<input type="checkbox" name="category[]" value="'.$phpbb3_category[$z]['id'].'" />';
        $selection .= '<b>'.$phpbb3_category[$z]['name'].'</b><br />';
        $total_forums = count($phpbb3_category[$z]['forums']);
        for ($y=0;$y<$total_forums;$y++) {
            $selection.= '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="forum[]" value="'.$phpbb3_category[$z]['forums'][$y]['id'].'" />';
            $selection.='&nbsp;'.$phpbb3_category[$z]['forums'][$y]['name'].'<br />';
        }
    }

    // confirm what we plan to do...

    $T = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
    $T->set_file (array ('phpbb3_db_confirm'=>'phpbb3_migrate_confirm.thtml'));

    $T->set_var(array(
        'site_url'            => $_CONF['site_url'],
        'form_action'         => $_CONF['site_admin_url'].'/plugins/forum/phpbb3_migrate.php',
        'mode'                => 'migrate',
        'phpbb3dbserver'      => $_phpbb_db_host,
        'phpbb3dbuser'        => $_phpbb_db_user,
        'phpbb3dbpass'        => $_phpbb_db_pass,
        'phpbb3dbname'        => $_phpbb_db_name,
        'phpbb3dbprefix'      => $_phpbb_db_prefix,
        'purgeglfusionforum'  => $_purge_glfusion_forum == 1 ? 'Yes' : 'No',
        'purgeglfusionforums' => $_purge_glfusion_forum,
        'importphpbb3user'    => $_import_phpbb3_users == 1 ? 'Yes' : 'No',
        'importphpbb3users'   => $_import_phpbb3_users,
        'purgeglfusionuser'   => $_purge_glfusion_users == true ? 'Yes' : 'No',
        'purgeglfusionusers'  => $_purge_glfusion_users,
        'highestuid'          => $_highest_uid,
        'selection'           => $selection,
    ));

    $T->parse ('output', 'phpbb3_db_confirm');
    $retval .= $T->finish ($T->get_var('output'));

    return $retval;
}

function gf_resyncforum($id) {
    global $_CONF,$_TABLES;

    COM_errorLog("Re-Syncing Forum id:$id");
    // Update all the Topics lastupdated timestamp to that of the last posted comment
    $topicsQuery = DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE forum=$id and pid=0");
    $topicCount = DB_numRows($topicsQuery);
    if ($topicCount > 0) {
        $lastTopicQuery = DB_query("SELECT MAX(id) as maxid FROM {$_TABLES['gf_topic']} WHERE forum=$id");
        $lasttopic = DB_fetchArray($lastTopicQuery);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET last_post_rec = {$lasttopic['maxid']} WHERE forum_id=$id");
        $postCount = DB_Count($_TABLES['gf_topic'],'forum',$id);
        // Update the forum definition record to know the number of topics and number of posts
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=$topicCount, post_count=$postCount WHERE forum_id=$id");

        $recCount = 0;
        while($trecord = DB_fetchArray($topicsQuery)) {
            $recCount++;
            // Retrieve the oldest post records for this topic and update the lastupdated time in the parent topic record
            $lsql = DB_query("SELECT MAX(id)as maxid FROM {$_TABLES['gf_topic']} WHERE pid={$trecord['id']}");
            $lastrec = DB_fetchArray($lsql);
            if ($lastrec['maxid'] != NULL) {
                $postCount = DB_count($_TABLES['gf_topic'],'forum',$id);
                $latest = DB_getITEM($_TABLES['gf_topic'],date,"id={$lastrec['maxid']}");
                DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated = '$latest' where id='{$trecord['id']}'");
                // Update the parent topic record to know the id of the Last Reply
                DB_query("UPDATE {$_TABLES['gf_topic']} SET last_reply_rec = {$lastrec['maxid']} where id='{$trecord['id']}'");
            } else {
                $latest = DB_getITEM($_TABLES['gf_topic'],date,"id={$trecord['id']}");
                DB_query("UPDATE {$_TABLES['gf_topic']} SET lastupdated = '$latest' WHERE id='{$trecord['id']}'");
            }
            // Recalculate and Update the number of replies
            $numreplies = DB_Count($_TABLES['gf_topic'], "pid", $trecord['id']);
            DB_query("UPDATE {$_TABLES['gf_topic']} SET replies = '$numreplies' WHERE id='{$trecord['id']}'");
        }
        COM_errorLog("$recCount Topic Records Updated");
    } else {
        DB_query("UPDATE {$_TABLES['gf_forums']} SET topic_count=0, post_count=0 WHERE forum_id=$id");
        COM_errorLog("No topic records to resync");
    }
}

function phpbb3_purgeUser($next_uid) {
    global $_CONF, $_TABLES, $_PLUGINS;
    global $DB_phpBB, $DB_glFusion;
    global $categoriesImported, $forumsImported, $topicsImported, $usersImported;

    if ( $next_uid < 2 ) {
        return;
    }

    $autoIncrement = $next_uid + 1;

    if (!mysql_query("DELETE FROM {$_TABLES['users']} WHERE uid > ".$next_uid,$DB_glFusion)) {
        die('SQL Error:<br/>' . mysql_error($DB_glFusion) );
    }

    @mysql_query("ALTER TABLE {$_TABLES['users']} AUTO_INCREMENT = ".$autoIncrement,$DB_glFusion);

    @mysql_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_uid > ".$next_uid,$DB_phpBB);
    @mysql_query("DELETE FROM {$_TABLES['userprefs']} WHERE uid > ".$next_uid,$DB_glFusion);
    @mysql_query("DELETE FROM {$_TABLES['userindex']} WHERE uid > ".$next_uid,$DB_glFusion);
    @mysql_query("DELETE FROM {$_TABLES['usercomment']} WHERE uid > ".$next_uid,$DB_glFusion);
    @mysql_query("DELETE FROM {$_TABLES['userinfo']} WHERE uid > ".$next_uid,$DB_glFusion);
    @mysql_query("DELETE FROM {$_TABLES['gf_userinfo']} WHERE uid > ".$next_uid,$DB_glFusion);

    // handle any plugins we know carry user preferences....

    if (in_array('mediagallery', $_PLUGINS)) {
        @mysql_query("DELETE FROM {$_TABLES['mg_userprefs']} WHERE uid > ".$next_uid,$DB_glFusion);
    }
    if (in_array('pm',$_PLUGINS)) {
        @mysql_query("DELETE FROM {$_TABLES['pm_userprefs']} WHERE uid > ".$next_uid, $DB_glFusion);
    }
    return;
}


function phpbb3_migrateUsers() {
    global $_CONF, $_TABLES;
    global $DB_phpBB, $DB_glFusion;
    global $categoriesImported, $forumsImported, $topicsImported, $usersImported;

    $retval = '';

    @mysql_query("ALTER TABLE {$_TABLES['users']} CHANGE `passwd` `passwd` VARCHAR( 40 ))", $DB_glFusion);

    $loggedgrp_result = mysql_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name = 'Logged-in Users'",$DB_glFusion);
    list ($loggedin_grp) = mysql_fetch_array($loggedgrp_result);
    $allgrp_result = mysql_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name = 'All Users'",$DB_glFusion);
    list ($all_grp) = mysql_fetch_array($allgrp_result);

    $phpbb3_users_result = mysql_query("SELECT user_id,username_clean,user_password,user_email,user_regdate,user_lastvisit,user_sig,user_website,user_sig_bbcode_uid FROM {$_TABLES['phpbb_users']} WHERE user_email <> ''", $DB_phpBB);
    while (list($uid,$membername,$passwd, $email,$dateRegistered,$lastvisit,$signature,$website,$sig_bbcode_uid) = mysql_fetch_array($phpbb3_users_result) )   {
        $username  = DB_escapeString(trim($membername));
        $emailaddr = DB_escapeString(trim($email));
        if ($emailaddr != '' /*&& COM_isEmail ($email)*/) {
            $user_count  = DB_count ($_TABLES['users'], 'username', $username);
            $email_count = DB_count ($_TABLES['users'], 'email', $emailaddr);
            if ($user_count == 0 && $email_count == 0) {
                $regdate = strftime('%Y-%m-%d %H:%M:%S',$dateRegistered);

                // insert new user record into the appropriate tables
                if ( !mysql_query("INSERT INTO {$_TABLES['users']} (username,passwd,email,regdate,sig,homepage,status) VALUES ('$username','$passwd','$emailaddr','$regdate','','".DB_escapeString($website)."',3)", $DB_glFusion) ) {
                    die('SQL Error:<br />' . mysql_error($DB_glFusion) );
                }
                $uid_result = mysql_query("SELECT uid FROM {$_TABLES['users']} WHERE username = '{$username}'",$DB_glFusion);
                list ($uid) = mysql_fetch_array($uid_result);


                mysql_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id,ug_uid) values ($loggedin_grp, $uid)",$DB_glFusion);
                mysql_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id,ug_uid) values ($all_grp, $uid)",$DB_glFusion);
                mysql_query("INSERT INTO {$_TABLES['userprefs']} (uid) VALUES ($uid)",$DB_glFusion);
                if ($_CONF['emailstoriesperdefault'] == 1) {
                    mysql_query("INSERT INTO {$_TABLES['userindex']} (uid) VALUES ($uid)",$DB_glFusion);
                } else {
                    mysql_query("INSERT INTO {$_TABLES['userindex']} (uid,etids) VALUES ($uid, '-')",$DB_glFusion);
                }
                mysql_query("INSERT INTO {$_TABLES['usercomment']} (uid) VALUES ($uid)",$DB_glFusion);
                mysql_query("INSERT INTO {$_TABLES['userinfo']} (uid,lastlogin) VALUES ($uid,$lastvisit)",$DB_glFusion);

                $signature = _convertPost($signature, $sig_bbcode_uid);

                $sql = "INSERT INTO {$_TABLES['gf_userinfo']} (uid,rating,signature) VALUES ('".$uid."',0,'".DB_escapeString($signature)."')";
                mysql_query($sql,$DB_glFusion);

                $usersImported++;
            } else {
                $retval .= "<br /><b>$membername</b> or <b>$email</b> already exist.<br />";
            }
        } else {
            $retval .= "<br /><b>$email</b> is not a valid email address, account not created<br />";
        }
    }
    return $retval;
}

function _convertPost( $message, $bbcode_uid = '' )
{
    // clean up the phpbb post for the glFusion database
    if ( $bbcode_uid != '' ) {
        $message = str_replace(":".$bbcode_uid,"",$message);
    }
    $message = preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message);

    $message = preg_replace('/(\[quote=)(.*)(&quot;\])/','[quote]',$message);
    $message = preg_replace('/(\[quote=)(.*)("\])/','[quote]',$message);
    $message = preg_replace('/(\[quote=)(.*)(\])/','[quote]',$message);
    $message = preg_replace('/(\[code:)(.*)(\])/','[code]',$message);
    $message = preg_replace('/(\[\/code:)(.*)(\])/','[/code]',$message);
    $message = preg_replace('/(\[\/list:)(.*)(\])/','[/list]',$message);

    $message = str_replace("size=0"  ,"size=7",$message);
    $message = str_replace("size=9"  ,"size=7",$message);
    $message = str_replace("size=17" ,"size=7",$message);
    $message = str_replace("size=24" ,"size=9",$message);
    $message = str_replace("size=25" ,"size=9",$message);
    $message = str_replace("size=34" ,"size=12",$message);
    $message = str_replace("size=42" ,"size=12",$message);
    $message = str_replace("size=50" ,"size=12",$message);
    $message = str_replace("size=59" ,"size=12",$message);
    $message = str_replace("size=92" ,"size=18",$message);
    $message = str_replace("size=100","size=18",$message);
    $message = str_replace("size=117","size=18",$message);
    $message = str_replace("size=134","size=18",$message);
    $message = str_replace("size=150","size=18",$message);
    $message = str_replace("size=167","size=18",$message);
    $message = str_replace("size=200","size=24",$message);

    $message = str_replace("&#58;",":",$message);
    $message = str_replace("&#46;",".",$message);

    return $message;
}


/*
 * Main processing loop
 */

$display = '';

$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');

// see if we have the necessary phpbb3 information available

if ( isset($_POST['phpbb3dbserver']) ) {
    $_phpbb_db_host     = $_POST['phpbb3dbserver'];
    $_phpbb_db_user     = $_POST['phpbb3dbuser'];
    $_phpbb_db_pass     = $_POST['phpbb3dbpass'];
    $_phpbb_db_name     = $_POST['phpbb3dbname'];
    $_phpbb_db_prefix   = $_POST['phpbb3dbprefix'];

    $_TABLES['phpbb_forums']       = $_phpbb_db_prefix .'forums';
    $_TABLES['phpbb_topics']       = $_phpbb_db_prefix .'topics';
    $_TABLES['phpbb_posts']        = $_phpbb_db_prefix .'posts';
    $_TABLES['phpbb_users']        = $_phpbb_db_prefix .'users';

    if ( isset($_POST['purge_glfusion_forum']) && ($_POST['purge_glfusion_forum'] == 'on' || $_POST['purge_glfusion_forum'] == 1 )) {
        $_purge_glfusion_forum = true;
    } else {
        $_purge_glfusion_forum = false;
    }

    if ( isset($_POST['purge_glfusion_users']) && ($_POST['purge_glfusion_users'] == 'on' || $_POST['purge_glfusion_users'] == 1 )) {
        $_purge_glfusion_users = true;
    } else {
        $_purge_glfusion_users = false;
    }

    if ( isset($_POST['highest_uid']) ) {
        $_highest_uid = COM_applyFilter($_POST['highest_uid'],true);
    }

    if ( $_purge_glfusion_users && ($_highest_uid == 0 || $_highest_uid < 2 ) ) {
        $_purge_glfusion_users = 0;
    }

    if ( isset($_POST['import_phpbb3_users']) && ($_POST['import_phpbb3_users'] == 'on' || $_POST['import_phpbb3_users'] == 1 ) ) {
        $_import_phpbb3_users = true;
    } else {
        $_import_phpbb3_users = false;
    }
}

if ( isset($_POST['cancel']) ) {
    $mode = '';
}

$display = '';
$userImport = '';

switch ( $mode ) {
    case 'dbinfo' : // confirm what we are about to do
        $display = _confirmImport();
        break;
    case 'migrate' :
        $rc = phpbb3_connect();
        if ( $rc != '' ) {
            $display = phpbb3_getInfo();
        } else {
            if ( $_import_phpbb3_users ) {
                if ($_purge_glfusion_users) {
                    phpbb3_purgeUser($_highest_uid);
                }
                $userImport = phpbb3_migrateUsers();
            }
            $display .= _processImport();
            $display .= $userImport;
        }
        break;
    default :
        $display = phpbb3_getInfo( );
        break;
}

echo COM_siteHeader();
echo $display;
echo COM_siteFooter();
?>