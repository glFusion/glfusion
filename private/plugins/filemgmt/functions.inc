<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* glFusion Plugin APIs
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by Consult4Hire Inc.
*  Author:
*  Blaine Lang          blaine AT portalparts DOT com
*
*/
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

define('FILEMGMT_LOG_ERROR', '1');
define('FILEMGMT_LOG_WARNING', '1');
define('FILEMGMT_LOG_DEBUG', '1');

spl_autoload_register(function ($class)
{
    // project-specific namespace prefix
    $prefix = 'Filemgmt\\';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/classes/';

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.class.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
use glFusion\Database\Database;
use Filemgmt\XoopsTree;
use Filemgmt\MyTextSanitizer;

$_FM_CONF = config::get_instance()->get_config('filemgmt');

require_once __DIR__ . '/filemgmt.php';

//$_FM_CONF['extensions_map'] = $_FMDOWNLOAD;

if (empty($_FM_CONF['FileStore'])) {
    $_FM_CONF['FileStore'] = $_CONF['path_html'].'data/filemgmt/files/';
    $_FM_CONF['FileStoreURL'] = $_CONF['site_url'].'/data/filemgmt/files/';
}
if (empty($_FM_CONF['SnapStore'])) {
    $_FM_CONF['SnapStore'] = $_CONF['path_html'].'data/filemgmt/snaps/';
    $_FM_CONF['FileSnapURL'] = $_CONF['site_url'].'/data/filemgmt/snaps/';
}
if (empty($_FM_CONF['SnapCat'])) {
    $_FM_CONF['SnapCat'] = $_CONF['path_html'].'data/filemgmt/category_snaps/';
    $_FM_CONF['SnapCatURL'] = $_CONF['site_url'].'/data/filemgmt/category_snaps/';
}

// Common plugin-specific URLs
$_FM_CONF['url'] = $_CONF['site_url'] . '/filemgmt';
$_FM_CONF['admin_url'] = $_CONF['site_admin_url'] . '/plugins/filemgmt';

// Fixed temporary storage path for submissions, always outside of web root
$_FM_CONF['FileStore_tmp'] = $_CONF['path'] . '/data/filemgmt_data/files/tmp/';
$_FM_CONF['SnapStore_tmp'] = $_CONF['path'] . '/data/filemgmt_data/snaps/tmp/';

if ( COM_isAnonUser() ) {
    if (!isset($_FM_CONF['uploadpublic'])) {
        $_FM_CONF['uploadpublic'] = false;
    }
    $_FM_CONF['uploadselect'] = $_FM_CONF['uploadpublic'];
}

// Load the language file(s), including custom strings if any
$langfile = $_CONF['language'] . '.php';
if (!file_exists(__DIR__ . '/language/' . $langfile)) {
    $langfile = 'english_utf-8.php';
}
include_once __DIR__ . '/language/' . $langfile;
if (file_exists(__DIR__ . '/language/custom/' . $langfile)) {
    include_once __DIR__ . '/language/custom/' . $langfile;
}

/**
 * Returns the items for this plugin that should appear on the main menu.
 *
 * @return  array   Array(label => url)
 */
function plugin_getmenuitems_filemgmt()
{
    global $LANG_FILEMGMT, $_FM_CONF;

    if (COM_isAnonUser() && $_FM_CONF['selectpriv'] == 1) {
        return '';
    }
    return array(
        $LANG_FILEMGMT['downloads'] => $_FM_CONF['url'] . '/index.php',
    );
}


/**
* Called by the plugin Editor to display the current plugin code version
* This may be different then the version installed and registered currently.
* If newer then you may want to run the update
*/
function plugin_chkVersion_filemgmt() {
    global $_FM_CONF;
    return $_FM_CONF['pi_version'];
}


/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_filemgmt()
{
    global $_CONF, $_TABLES;

    require_once $_CONF['path'] . 'plugins/filemgmt/upgrade.php';
    return filemgmt_upgrade();
}

function plugin_getheadercss_filemgmt() {
    global $_CONF;

    $styles = array();

    if ( @file_exists($_CONF['path_layout'].'plugins/filemgmt/style.css') ) {
        $styles[] = $_CONF['path_layout'].'plugins/filemgmt/style.css';
    } else {
        $styles[] = $_CONF['path_html'] . '/filemgmt/style.css';
    }
    return($styles);
}


/**
 * Include if plugin will be supporting comments
 *
 * @author Blaine Lang blaine@portalparts.com
 * @return  boolean  true indicates comments are suppported
 */
function plugin_commentsupport_filemgmt()
{
    // Filemgmt Module will use comments
    return true;
}


/**
 * Plugin function that is called after comment form is submitted.
 * Needs to atleast save the comment and check return value.
 * Add any additional logic your plugin may need to perform on comments.
 *
 * @author Blaine Lang blaine@portalparts.com
 * @param   string  $title   Comment title field in comment form
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   HTML string (redirect?) for success or comment form if failure.
 */
function plugin_savecomment_filemgmt($title,$comment,$id,$pid,$postmode)
{
    global $_CONF, $_FM_CONF, $_TABLES, $LANG03;

    $retval = '';

    $title = strip_tags ($title);
    $pid = COM_applyFilter ($pid, true);
    $postmode = COM_applyFilter ($postmode);

    $ret = CMT_saveComment ( $title, $comment, $id, $pid, 'filemgmt',$postmode);

    if ( $ret > 0 ) { // failure
        if ( SESS_isSet('glfusion.commentpresave.error') ) {
            $retval = COM_showMessageText(SESS_getVar('glfusion.commentpresave.error'), '', true,'error');
            SESS_unSet('glfusion.commentpresave.error');
        }
        $retval .= CMT_commentform ($title, $comment, $id, $pid,'filemgmt', $LANG03[14], $postmode);
        return $retval;
    } else { // success
        $lid = (int) substr($id,7);
        $comments = CMT_getCount('filemgmt', $id);
        DB_change($_TABLES['filemgmt_filedetail'],'comments', $comments, 'lid',DB_escapeString($lid));
//        $lid = (int) substr($id,7);
        echo COM_refresh (COM_buildUrl ($_FM_CONF['url']."/index.php?id=$lid"));
    }
}


/**
 * Plugin function that is called after comment is approved
 * from the moderation queue.
 *
 * $cid         comment id
 * $type        type (i.e.; media gallery, etc.)
 * $sid         ID of owning item
 *
 */
function plugin_commentapproved_filemgmt($cid,$type,$sid)
{
    global $_TABLES;

    if (SEC_hasRights('filemgmt.edit,comment.admin','OR')) {
        $comments = CMT_getCount('filemgmt', $sid);
        DB_change($_TABLES['filemgmt_filedetail'],'comments', $comments, 'lid',DB_escapeString($sid));
    }
}

function plugin_editcomment_filemgmt($cid,$id)
{
    global $_FM_CONF;

    echo COM_refresh (COM_buildUrl ($_FM_CONF['url']."/index.php?id=$id"));
    exit;
}

/**
 * Plugin API to delete a comment
 *
 * @author Blaine Lang blaine@portalparts.com
 * @param   int     $cid    Comment to be deleted
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_deletecomment_filemgmt($cid,$id)
{
    global $_FM_CONF,$_TABLES;

    if (SEC_hasRights("filemgmt.edit")) {
        if (CMT_deleteComment($cid, $id, 'filemgmt') == 0) {
            // Now redirect the program flow to the view of the file and its comments
            return (COM_refresh($_FM_CONF['url'] . "/index.php?id=$id"));
        } else {
            return false;
        }
    } else {
        return false;
    }
}


/**
 * Plugin API to display a specific comment thread
 *
 * @author Blaine Lang blaine@portalparts.com
 * @param   string  $id     Unique idenifier for item comment belongs to
 * @param   int     $commentid    Comment id to display (possibly including sub-comments)
 * @param   string  $title  comment title
 * @param   string  $order  'ASC' or 'DSC' or blank
 * @param   string  $format 'threaded', 'nested', or 'flat'
 * @param   int     $page   Page number of comments to display
 * @param   boolean $view   True to view comment (by cid), false to display (by $pid)
 * @return  mixed   results of calling the CMT_userComments function
 */
function plugin_displaycomment_filemgmt($id,$commentid,$title,$order,$format,$page,$view)
{
    global $_USER, $_GROUPS, $_FM_CONF, $FilemgmtAdmin, $LANG_FILEMGMT, $_TABLES, $_CONF, $LANG01,$_DB_name;

    if (COM_isAnonUser() && $_FM_CONF['selectpriv'] == 1) {
        return '';
    }

    $display = '';

    $lid = str_replace('fileid_','',$id);

    $FM_ratedIds = array();
    $FM_ratedIds = RATING_getRatedIds('filemgmt');
    
    $p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
    $p->set_file (array (
        'page'             =>     'filelisting.thtml',
        'category'         =>     'filelisting_category.thtml'
    ) );

    $myts = new Filemgmt\MyTextSanitizer;
    $mytree = new Filemgmt\XoopsTree('',$_TABLES['filemgmt_cat'],"cid","pid");
    $mytree->setGroupAccessFilter($_GROUPS);

    $groupsql = SEC_buildAccessSql();

    $File = Filemgmt\Download::getInstance($lid);

    if ($File->canRead() && $File->getCommentFlag()) {

        $p->set_var(array(
            'block_header' => COM_startBlock("<b>". $LANG_FILEMGMT['plugin_name'] ."</b>"),
            'block_footer' => COM_endBlock(),
            'cssid' => 1,
            'comment_records' => '',
            'back_to_list' => true,
        ) );
        $p->set_block('page', 'fileRecords', 'fRecord');
        $p->set_var('filelisting_record', $File->showListingRecord());
        $p->parse('fRecord', 'fileRecords');

        $cid = $File->getCid();

        $pathstring = "<li><a href='{$_FM_CONF['url']}/index.php'>"._MD_MAIN."</a></li>";
        $nicepath = $mytree->getNicePathFromId($cid, "title", "{$_FM_CONF['url']}/viewcat.php");
        $pathstring .= $nicepath;

        $p->set_var('category_path_link',$pathstring);

        $p->parse('output', 'page');
        $display .= $p->finish ($p->get_var('output'));

        /* Get formatted comment thread */
        if (SEC_hasRights('filemgmt.edit')) {
            $delete_option = true;
        } else {
            $delete_option = false;
        }
        if ($view == 1) {
            $display .= CMT_userComments ("fileid_$id", $title, 'filemgmt',$order,$format,$commentid,$page,true,$delete_option,0);
        } else {
            $display .= CMT_userComments ("fileid_$id", $title, 'filemgmt',$order,$format,$commentid,$page,false,$delete_option,0);
        }
    } else {
        return false;
    }
    return $display;
}


/**
 * Handle user account deletion.
 * Anonymizes uploads, download history, and ratings.
 * Deletes all pending broken link reports.
 *
 * @param   integer $uid    User ID
 */
function plugin_user_delete_filemgmt( $uid )
{
    global $_CONF, $_USER, $_TABLES;

    $db = Database::getInstance();

    // purge all rows from history
    try {
        $stmt = $db->conn->executeQuery(
            "DELETE FROM {$_TABLES['filemgmt_history']} WHERE uid = ?",
            array($uid),
            array(Database::INTEGER)
        );
    } catch(Throwable $e) {
        // Ignore errors or failed attempts
    }

    // Anonymize all data for ratinguser in filemgmt_vote / Blank out ratinghostname
    try {
        $stmt = $db->conn->executeQuery(
            "UPDATE {$_TABLES['filemgmt_votedata']} SET
            ratinghostname='',
            ratinguser=1
            WHERE ratinguser = ?",
            array($uid),
            array(Database::INTEGER)
        );
    } catch(Throwable $e) {
        // Ignore errors or failed attempts
    }

    // Purge all broken links table entries for sender = uid
    try {
        $stmt = $db->conn->executeQuery(
            "DELETE FROM {$_TABLES['filemgmt_brokenlinks']} WHERE sender = ?",
            array($uid),
            array(Database::INTEGER)
        );
    } catch(Throwable $e) {
        // Ignore errors or failed attempts
    }

    // Anonymize submitter for filemgmt_filedetails
    try {
        $stmt = $db->conn->executeQuery(
            "UPDATE {$_TABLES['filemgmt_filedetail']} SET submitter=1 WHERE submitter = ?",
            array($uid),
            array(Database::INTEGER)
        );
    } catch(Throwable $e) {
        // Ignore errors or failed attempts
    }
}


/**
 * Get the summary stats information for stats.php.
 *
 * @return  string      "total_files (hits)"
 */
function plugin_statssummary_filemgmt ()
{
    global $_USER, $_FM_CONF, $_TABLES, $LANG_FILEMGMT;

    if (COM_isAnonUser() && $_FM_CONF['selectpriv'] == 1) {
        return '';
    }

    $sql = "SELECT COUNT(*), SUM(hits) FROM {$_TABLES['filemgmt_filedetail']} a
        LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid " .
        SEC_buildAccessSql('WHERE');
    list($total_files,$total_downloads) = DB_fetchArray( DB_query($sql));

    return array(
        $LANG_FILEMGMT['nofiles'],
        COM_numberFormat ($total_files) . '(' . COM_numberFormat ($total_downloads) . ')'
    );
}


/**
 * Shows the statistics for the Filemgmot plugin on stats.php.
 * If $showsitestats is 1 then we are to only print the overall stats in
 * the 'site statistics box'. Otherwise we show the detailed stats for the repo.
 *
 * @param   integer $showsitestats  Flag to let us know which stats to get
 */
function plugin_showstats_filemgmt($showsitestats)
{
    global $_USER, $_FM_CONF, $LANG_FILEMGMT, $_TABLES, $_CONF;

    if (COM_isAnonUser() && $_FM_CONF['selectpriv'] == 1) {
        return '';
    }

    $retval = '';

    $stat_templates = new Template($_CONF['path_layout'] . 'stats');
    $stat_templates->set_file(array(
        'itemstats'=>'itemstatistics.thtml',
        'statrow'=>'singlestat.thtml',
    ) );
    if ($showsitestats == 1) {
        // Deprecate?
        $sql = "SELECT COUNT(*), SUM(hits) FROM {$_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= SEC_buildAccessSql('WHERE');
        list($total_files,$total_downloads) = DB_fetchArray( DB_query($sql));
        $retval = "<table border = '0' width='100%' cellspacing='0' cellpadding='0'>";
        $retval .= "<tr><td>" . $LANG_FILEMGMT['nofiles'] . "</td>";
        $retval .= "<td align='right'>" . $total_files . " (" .$total_downloads .")&nbsp;&nbsp;&nbsp;</td></tr></table>";
    } else {
        $header_arr = array(
            array(
                'text' => 'File Name',
                'field' => 'title',
                'header_class' => 'stats-header-title',
            ),
            array(
                'text' => 'Hits',
                'field' => 'hits',
                'field_class' => 'stats-list-count',
            ),
        );
        $data_arr = array();
        $text_arr = array(
            'has_menu'     => false,
            'title'        => 'Top Ten Downloaded Files',
        );

        $sql  = "SELECT a.lid, a.title, hits from {$_TABLES['filemgmt_filedetail']} a
            LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid " .
            SEC_buildAccessSql('WHERE') .
            " AND hits > 0 ORDER BY hits desc LIMIT 10";
        $result = DB_query($sql);
        $nrows  = DB_numRows($result);
        if ($nrows > 0) {
            while ($A = DB_fetchArray($result, false)) {
                $data_arr[] = array(
                    'title' => COM_createLink(
                        $A['title'],
                        $_FM_CONF['url'] . '/index.php?id=' . $A['lid']
                    ),
                    'hits' => (int)$A['hits'],
                );
            }
            $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
        } else {
            $retval .= $LANG_FILEMGMT['nofiles'];
        }
    }
    return $retval;
}


/**
 * glFusion is asking us to provide any new items that show up in the type drop-down
 * on search.php.  Let's let users search the Filelistings in the Filemgmt Plugin
 */
function plugin_searchtypes_filemgmt()
{
    global $_USER, $_FM_CONF, $LANG_FILEMGMT;

    if (!COM_isAnonUser() || $_FM_CONF['selectpriv'] == 0) {
        return array(
            'filemgmt' => $LANG_FILEMGMT['searchlabel'],
        );
    } else {
        return '';
    }
}

/**
* This will put an option for Filemgmt Admin in the command and control block on
* moderation.php
*/
function plugin_cclabel_filemgmt()
{
    global $LANG_FILEMGMT, $_FM_CONF;

    if (SEC_hasRights('filemgmt.edit')) {
        return array(
            $LANG_FILEMGMT['plugin_name'],
            $_FM_CONF['admin_url'] . '/index.php',
            $_FM_CONF['url'] . '/images/filemgmt.png',
        );
    } else {
        return false;
    }
}


/**
 * Setup the user menu options for this plugin.
 *
 * @return  array|boolean   Array of menu information, false if not allowed.
 */
function plugin_getuseroption_filemgmt()
{
    global $_CONF, $_TABLES, $LANG_FILEMGMT, $_FM_CONF;

    if (SEC_hasRights('filemgmt.upload') OR $_FM_CONF['uploadselect'] == 1) {
        $groupsql = SEC_buildAccessSql();
        $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
        $sql .= $groupsql;
        list($catAccessCnt) = DB_fetchArray( DB_query($sql));
        if ( $catAccessCnt > 0 ) {
           return array($LANG_FILEMGMT['usermenu3'], $_FM_CONF['url'] . '/submit.php');
        }
    } else {
        return false;
    }
}


/**
* returns the administrative option for this plugin
* *
*/

function plugin_getadminoption_filemgmt()
{
    global $_FM_CONF, $_TABLES, $LANG_FILEMGMT;

    if (SEC_hasRights('filemgmt.edit')) {
        $count = DB_count($_TABLES['filemgmt_filedetail'], 'status', 1);
        return array(
            $LANG_FILEMGMT['admin_menu'],
            $_FM_CONF['admin_url'] . '/index.php',
            $count,
        );
    }
}


function plugin_getfeednames_filemgmt ()
{
    global $_TABLES;

    $feeds = array ();
    $groupsql = SEC_buildAccessSql('WHERE');
    $result = DB_query ("SELECT cid,title FROM {$_TABLES['filemgmt_cat']} $groupsql ORDER BY title ASC");
    $num = DB_numRows ($result);

    if ($num > 0) {
        $feeds[] = array ('id' => '0', 'name' => 'all files');
    }

    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $feeds[] = array ('id' => $A['cid'], 'name' => $A['title']);
    }

    return $feeds;
}

function filemgmt_buildSql ($topic, $limits)
{
    global $_CONF;

    $groups = array ();
    $usergroups = SEC_getUserGroups(1);
    foreach ($usergroups as $group) {
        $groups[] = $group;
    }
    $grouplist = implode(',',$groups);

    $where = ' filedetail.status=1 AND cat.grp_access IN (' . $grouplist . ') ';
    if ($topic > 0) {
        $where = 'filedetail.cid=' . $topic;
    }

    $limitsql = '';
    if (!empty ($limits)) {
        if (substr ($limits, -1) == 'h') { // last xx hours
            $limitsql = '';
            $hours = substr ($limits, 0, -1);
            if (!empty ($where)) {
                $where .= ' AND ';
            }
            $where .= "filedetail.date >= DATE_SUB('".$_CONF['_now']->toMySQL(true)."',INTERVAL $hours HOUR)";
        } else {
            $limitsql = ' ORDER BY filedetail.date DESC LIMIT ' . $limits;
        }
    } else {
        $limitsql = ' ORDER BY filedetail.date DESC LIMIT 10';
    }

    if (!empty ($where)) {
        $where = ' WHERE ' . $where;
    }

    $sql = $where . $limitsql;
    return $sql;
}

function plugin_getfeedcontent_filemgmt ($feed, &$link, &$update)
{
    global $_CONF, $_FM_CONF, $_TABLES;

    $content = array ();
    $lids = array ();

    $result = DB_query ("SELECT topic,limits,content_length FROM {$_TABLES['syndication']} WHERE fid = $feed");
    $F = DB_fetchArray ($result);
    $sql  = "SELECT *,cat.title as cat_title,filedetail.title as file_title FROM {$_TABLES['filemgmt_filedetail']} filedetail LEFT JOIN {$_TABLES['filemgmt_cat']} cat ON filedetail.cid=cat.cid ";
    $sql .= filemgmt_buildSql( $F['topic'],'' );

    $result = DB_query ($sql);
    $num = DB_numRows ($result);
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $desc = DB_getItem ($_TABLES['filemgmt_filedesc'], 'description',
                            "lid = {$A['lid']}");
        $filelink = $_FM_CONF['url'] . '/index.php?id='
                  . $A['lid'];
        $content[] = array ('title'  => $A['cat_title'] . '::' . $A['file_title'],
                            'summary'   => $desc,
                            'link'   => $filelink,
                            'uid'    => $A['submitter'],
                            'date'   => $A['date'],
                            'format' => 'text'
                           );
        $lids[] = $A['lid'];
    }

    if ($F['topic'] == 0) {
        $link = $_FM_CONF['url'] . '/index.php';
    } else {
        $link = $_FM_CONF['url'] . '/viewcat.php?cid=' . $F['topic'];
    }
    $update = implode (',', $lids);

    return $content;
}

function plugin_feedupdatecheck_filemgmt ($feed, $topic, $update_data, $limit)
{
    global $_TABLES;

    $is_current = true;

    $sql  = "SELECT *,cat.title as cat_title,filedetail.title as file_title FROM {$_TABLES['filemgmt_filedetail']} filedetail LEFT JOIN {$_TABLES['filemgmt_cat']} cat ON filedetail.cid=cat.cid ";
    $sql .= filemgmt_buildSql( $topic,$limit );

    $result = DB_query ($sql);
    $num = DB_numRows ($result);

    $lids = array ();
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $lids[] = $A['lid'];
    }
    $current = implode (',', $lids);
    return ( $current != $update_data ) ? false : true;
}

/**
  * Whats New Block API Support
  * Return the Headline and Byline for the new section in the Whatsnew Block
*/
function plugin_whatsnewsupported_filemgmtxx()
{
    global $_USER, $_FM_CONF, $_CONF, $LANG_FM00, $_FM_CONF;

    if (COM_isAnonUser() && $_FM_CONF['selectpriv'] == 1) {
        return '';
    }

    if ($_FM_CONF['whatsnew'] == 1) {
        return array(
            $LANG_FM00['WhatsNewLabel'],
            sprintf($LANG_FM00['WhatsNewPeriod'], $_FM_CONF['whatsnewperioddays'])
            );
    } else {
        return false;
    }
}


/**
 * API function provides the content of our "What's New" feed.
 */
function plugin_getwhatsnew_filemgmt()
{
    global $_TABLES, $_CONF, $LANG_FILEMGMT,$LANG_FM00, $LANG01, $_FM_CONF;

    $retval = '';
    $interval = 86400 * (int)$_FM_CONF['whatsnewperioddays'];
    $items = array();
    $db = Database::getInstance();

    if ((SEC_hasRights('filemgmt.user') OR $_FM_CONF['selectpriv'] == 0) AND $_FM_CONF['whatsnew'] == 1) {
        $files = Filemgmt\Download::getNewUploads();
        /*$sql  = "SELECT a.lid, a.title FROM {$_TABLES['filemgmt_filedetail']} a
            LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid
            WHERE date >= UNIX_TIMESTAMP() - $interval DATE_SUB('".$_CONF['_now']->toMySQL(true)."', INTERVAL {$_FM_CONF['whatsnewperioddays']} DAY ))";
        $sql .= SEC_buildAccessSql();
        $sql .= "AND STATUS=1 ORDER  BY date DESC LIMIT 15 ";

        $result = DB_query( $sql );

        $nrows1 = DB_numRows( $result );*/
        if (count($files) == 0) {
        //if( $nrows1 == 0 ) {
            $retval .= $LANG_FILEMGMT['no_new_files'] . '<br>' . LB;
        } else {
            $filter = sanitizer::getInstance();
            $filter->setPostmode('text');
            foreach ($files as $A) {
                $File = new Filemgmt\Download($A);
            //for( $i = 0; $i < $nrows1; $i++ ) {
                //list($lid, $ftitle) = DB_fetchArray( $result );
                //$ftitle = $filter->filterData($ftitle);
                $str = COM_createLink(
                    COM_truncate(
                        $filter->filterData($File->getTitle()),
                        $_FM_CONF['whatsnewtitlelength'],
                        '...'
                    ),
                    $_FM_CONF['url'] . '/index.php?id=' . $File->getID(),
                    array(
                        'title' => htmlspecialchars($File->getTitle(),ENT_COMPAT,COM_getEncodingt()),
                        'rel' => 'nofollow',
                    )
                );
                //$str = "<a href=\"{$_CONF['site_url']}/filemgmt/index.php?id={$lid}\" title=\"".htmlspecialchars($ftitle,ENT_COMPAT,COM_getEncodingt())."\" rel=\"nofollow\">";
				/*if( strlen( $ftitle ) > $filemgmtWhatsNewTitleLength ) {
					$titleTrimLength = $filemgmtWhatsNewTitleLength - 3;
                    $str .= COM_truncate( $ftitle,$titleTrimLength, '...' );
				} else {
					$str .= $ftitle;
				}
                $str .= '</a>';*/
                $items[] = $str;
            }
        }

        if ($_FM_CONF['showwhatsnewcomments']) {
            // Search for new comments
            $sql  = "SELECT a.lid,a.title,c.title FROM {$_TABLES['filemgmt_filedetail']} a
                LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid
                LEFT JOIN {$_TABLES['comments']} c ON c.sid = concat('fileid_' ,a.lid )
                WHERE grp_access IN (:groups)
                AND c.date >=  DATE_SUB(:today, INTERVAL :interval DAY )
                AND c.type='filemgmt' AND c.queued = 0
                ORDER  BY c.date DESC
                LIMIT 15";
            $stmt = $db->conn->prepare($sql);
            $stmt->bindValue('today', $_CONF['_now']->toMySQL(true), Database::STRING);
            $stmt->bindValue('interval', $_FM_CONF['whatsnewperioddays'], Database::INTEGER);
            $stmt->bindValue('groups', implode(',', Group::getAll()), Database::STRING);
            $status = $stmt->execute();
            $comments = $stmt->fetchAll(Database::ASSOCIATIVE);

            if( count($comments) == 0 ) {
                $retval .= $LANG_FILEMGMT['no_comments'] . '<br>' . LB;
            } else {
                $filter = sanitizer::getInstance();
                $filter->setPostmode('text');
                $titlePrefix = "<b>C:</b>&nbsp;";
                // Compensate for the added HTML
                $titleLength = $_FM_CONF['whatsnewtitlelength'] + strlen( $titlePrefix ) - 3;
                foreach ($comments as $comment) {
                    $commentTitle = $filter->filterData($comment['title']);
                    $ctitle = $titlePrefix . $commentTitle;
                    $str = COM_createLink(
                        COM_truncate($ctitle, $titleLength, '...'),
                        $_FM_CONF['url'] . '/index.php?id=' . $comment['lid'],
                        array(
                            'title' => htmlspecialchars($commentTitle,ENT_COMPAT,COM_getEncodingt()),
                            'rel' => 'nofollow',
                        )
                    );
                    //$str = "<a href=\"{$_CONF['site_url']}/filemgmt/index.php?id={$lid}\" title=\"".htmlspecialchars($commentTitle,ENT_COMPAT,COM_getEncodingt())."\" rel=\"nofollow\" >";
					/*if( strlen( $ctitle ) > $titleLength ) {
						$titleTrimLength = $titleLength - 3;
	                    $str .= COM_truncate( $ctitle,$titleTrimLength, '...' );
					} else {
						$str .= $ctitle;
					}
                    $str .= '</a>';*/
                    $items[] = $str;
                }
            }
        }

        if (count($items) == 0 and count($comments) == 0) {
            return '';
        } else {
            return array(
                $LANG_FM00['WhatsNewLabel'],
                sprintf($LANG_FM00['WhatsNewPeriod'], $_FM_CONF['whatsnewperioddays']),
                $items
            );
        }
    }
    return '';
}


/**
* Implements the [file:] and [file_download:]  autotags.
*
*/
function plugin_autotags_filemgmt ($op, $content = '', $autotag = '')
{
    global $_USER, $_FM_CONF, $_CONF, $_TABLES, $LANG_FILEMGMT_AUTOTAG;

    if ($op == 'tagname' ) {
        return array('file', 'file_download');
    } else if ($op == 'tagusage') {
        $tagUsage = array(
            array('namespace' => 'filemgmt','usage' => 'description')
        );
        return $tagUsage;
    } else if ($op == 'desc' ) {
        switch ($content) {
            case 'file' :
                return $LANG_FILEMGMT_AUTOTAG['desc_file'];
                break;
            case 'file_download' :
                return $LANG_FILEMGMT_AUTOTAG['desc_file_download'];
                break;
            default :
                return '';
                break;
        }
    } else if ($op == 'parse') {
        if (($autotag['tag'] == 'file') || ($autotag['tag'] == 'file_download') ) {
            $target = '';
            $target_window = '';
            $caption = $autotag['parm2'];
            $skip = 0;

            $file_id = COM_applyFilter ($autotag['parm1']);

            if ( !empty($file_id) ) {
                // do a quick perm check to make sure everything is good!
                $sql  = "SELECT a.lid, a.title FROM {$_TABLES['filemgmt_filedetail']} a ";
                $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
                $sql .= "WHERE a.lid='".DB_escapeString($file_id)."' AND status=1 ";
                $sql .= SEC_buildAccessSql();
                $result = DB_query( $sql );
                $nrows = DB_numRows( $result );
                if ( $nrows < 1 ) {
                    $content = str_replace ($autotag['tagstr'], '', $content);
                    return $content;
                }
            } else {
                return $content;
            }
            $F = DB_fetchArray($result);
            $title = COM_filterHTML($F['title']);

    		// check for target window specification, and user-defined caption, if any
            $px = explode (' ', trim ($autotag['parm2']));
            if (is_array ($px)) {
                foreach ($px as $part) {
                    if (substr ($part, 0, 7) == 'target:') {
                        $a = explode (':', $part);
                        $target_window = $a[1];
                        $skip++;
                    } else {
                        break;
                    }
                }

                if ($skip != 0) {
                    if (count ($px) > $skip) {
                        for ($i = 0; $i < $skip; $i++) {
                            array_shift ($px);
                        }
                        $caption = trim (implode (' ', $px));
                    } else {
                        $caption = '';
                    }
                }
            } else {
                $caption = trim ($autotag['parm2']);
            }

            switch( $autotag['tag'] ) {
                case 'file' :
                    $url = COM_buildUrl ($_FM_CONF['url'] . '/index.php?id='
                                     . $file_id);
                    break;
                case 'file_download' :
                    if ( $target_window != '' ) {
                        $url = $_FM_CONF['url'] . '/visit.php?lid='. $file_id;
                        $target = 'target="'.$target_window.'"';
                    } else {
                        $url = $_FM_CONF['url'] . '/visit.php?lid='.$file_id;
                    }
                    break;
            }
            if ($caption == '' ) {
                $linktext = $title;
            } else {
                $linktext = $caption;
            }
            $link = '<a href="' . $url . '" ' . $target . ' >' . $linktext . '</a>';
            $content = str_replace ($autotag['tagstr'], $link, $content);
        }
        return $content;
    }
}

function plugin_itemrated_filemgmt($lid, $rating, $votes)
{
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['filemgmt_filedetail']} SET rating = '".DB_escapeString($rating). "', votes=".$votes . " WHERE lid=".$lid;
    DB_query($sql);
    return true;
}

function plugin_canuserrate_filemgmt($item_id, $uid)
{
    global $_TABLES, $FilemgmtUser;

    $retval = false;

    if ( COM_isAnonUser() ) {
        return false;
    }

    if (!$FilemgmtUser) {
        $ratinguser = 0;
    } else {
        $ratinguser = $uid;
    }
    // Check if Download POSTER is voting (UNLESS Anonymous users allowed to post)
    if ($ratinguser != 0) {
        $result=DB_query("SELECT submitter FROM {$_TABLES['filemgmt_filedetail']} WHERE lid='".DB_escapeString($item_id)."'");
        while(list($ratinguserDB)=DB_fetchARRAY($result)) {
            if ($ratinguserDB==$ratinguser) {
                return false;
            }
        }
    }
    return true;
}

/**
* Return information for a download
*
* @param    string  $id         file ID or '*'
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_filemgmt($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG_FILEMGMT, $_FM_CONF;

    $id = str_replace('fileid_','',$id);

    $properties = explode(',', $what);
    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
            case 'date' :
            case 'date-modified':
            case 'date-created' :
                $fields[] = 'date AS unixdate';
                break;
            case 'description':
            case 'excerpt':
            case 'searchidx' :
                $fields[] = 'description.description';
                break;
            case 'raw-description' :
                $fields[] = 'description.description';
                break;
            case 'id':
                $fields[] = 'detail.lid';
                break;
            case 'title':
                $fields[] = 'detail.title AS filetitle';
                break;
            case 'label':
            case 'url':
            case 'status' :
                $fields[] = 'detail.lid';
                break;
            case 'author' :
            case 'author_name' :
                $fields[] = 'detail.submitter';
                break;
            case 'hits' :
                $fields[] = 'hits';
                break;
            case 'perms' :

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

    if ($id == '*') {
        $where = '';
        $permOp = 'WHERE ';
    } else {
        $where = " WHERE detail.lid = '" . DB_escapeString($id) . "' ";
        $permOp = 'AND';
    }

    $groupsql = SEC_buildAccessSql();

    $sql = "SELECT " . implode(',', $fields)
            . ",b.* FROM {$_TABLES['filemgmt_filedetail']} AS detail LEFT JOIN {$_TABLES['filemgmt_filedesc']} AS description "
            . " ON detail.lid=description.lid LEFT JOIN {$_TABLES['filemgmt_cat']} b ON detail.cid=b.cid " . $where . $groupsql;
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
                    $props[$p] = $A['unixdate'];
                    break;
                case 'description':
                case 'excerpt':
                case 'searchidx' :
                    $props[$p] = PLG_replaceTags($A['description'],'filemgmt','description');
                    break;
                case 'raw-description':
                    $props[$p] = $A['description'];
                    break;
                case 'id':
                    $props['id'] = $A['lid'];
                    break;
                case 'title':
                    $props['title'] = $A['filetitle'];
                    break;
                case 'url':
                    if (empty($A['id'])) {
                        $props['url'] = COM_buildUrl($_FM_CONF['url'].'/index.php?id=' . $id);
                    } else {
                        $props['url'] = COM_buildUrl($_FM_CONF['url'].'/index.php?id=' . $A['lid']);
                    }
                    break;
                case 'label':
                    $props['label'] = $LANG_FILEMGMT['usermenu1'];
                    break;
                case 'status':
                    $props['status'] = 1; // stub - default
                    break;
                case 'author' :
                    $props['author'] = $A['submitter'];
                    break;
                case 'author_name' :
                    $props['author_name'] = COM_getDisplayName($A['submitter']);
                    break;
                case 'hits' :
                    $props['hits'] = $A['hits'];
                    break;
                case 'perms' :
                    $p_anon = 0;
                    $p_member = 0;
                    if (
                        ($_FM_CONF['selectpriv'] == 0 && $A['grp_access'] == 13 ) ||
                        $A['grp_access'] == 2
                    ) {
                        $p_anon = 2;
                    }
                    if ( $A['grp_access'] == 2 || $A['grp_access'] == 13 ) $p_member = 2;
                    $props['perms'] = array(
                        'owner_id' => 2,
                        'group_id' => $A['grp_access'],
                        'perm_owner' => 3,
                        'perm_group' => 2,
                        'perm_members' => $p_member,
                        'perm_anon' => $p_anon,
                    );
                    break;
                default:
                    $props[$p] = '';
                    break;
            }
        }

        $mapped = array();

        foreach ($props as $key => $value) {
            if ($id == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
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
    if ( $retval === '' || (is_array($retval) && count($retval) == 0 ) ) return NULL;

    return $retval;
}

function plugin_user_move_filemgmt($origUID,$destUID)
{
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['filemgmt_filedetail']} SET submitter=".(int)$destUID." WHERE submitter=".(int)$origUID;
    DB_query($sql,1);
}

function plugin_getconfigelementhelp_filemgmt($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/filemgmt.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/filemgmt.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

function plugin_privacy_export_filemgmt_DISABLED($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_FM_CONF, $_TABLES, $_USER;

    $retval = '';

    $exportFields = array('lid','title','url','homepage','version','submitter','date','permalink');

    try {
        $stmt = $db->conn->executeQuery(
            "SELECT * FROM {$_TABLES['filemgmt_filedetail']} WHERE submitter = ? ORDER BY date ASC",
            array($uid),
            array(Database::INTEGER)
        );
    } catch(Throwable $e) {
        // Ignore errors or failed attempts
    }

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

    $retval .= "<files>\n";

    foreach($rows AS $row) {
        $row['permalink'] = $_FM_CONF['url'].'/index.php?id='.$row['lid'];
        $retval .= "<file>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {
/*
                if ( $item == 'media_upload_time' ) {
                    $dt = new Date($value,$_USER['tzid']);
                    $value = $dt->format($dt->getUserFormat(),true);
                }
*/
                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</file>\n";
    }
    $retval .= "</files>\n";

// download history

    $exportFields = array('uid','title','remote_ip','date');

    $sql = "SELECT * FROM {$_TABLES['filemgmt_history']} AS fh LEFT JOIN {$_TABLES['filemgmt_filedetail']} AS fd ON fh.lid=fd.lid WHERE uid = ". (int) $uid;

    if ( $ip != '' ) {
        $sql .= " OR remote_ip = '" . DB_escapeString($ip)."'";
    }

    $sql .= " ORDER BY fh.date ASC";

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

    $retval .= "<download_history>\n";

    foreach($rows AS $row) {
        $row['permalink'] = $_FM_CONF['url'].'/index.php?id='.$row['lid'];
        $retval .= "<download>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {

                if ( $item == 'date' ) {
                    $dt = new Date($value,$_USER['tzid']);
                    $value = $dt->format($dt->getUserFormat(),true);
                }

                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</download>\n";
    }
    $retval .= "</download_history>\n";

// broken links

    $exportFields = array('title','sender','ip');

    $sql = "SELECT * FROM {$_TABLES['filemgmt_brokenlinks']} AS bl LEFT JOIN {$_TABLES['filemgmt_filedetail']} AS fd ON bl.lid=fd.lid WHERE sender = ". (int) $uid;

    if ( $ip != '' ) {
        $sql .= " OR ip = '" . DB_escapeString($ip)."'";
    }

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

    $retval .= "<broken_link_report>\n";

    foreach($rows AS $row) {
        $retval .= "<report>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {
                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</report>\n";
    }
    $retval .= "</broken_link_report>\n";

    if ( function_exists('tidy_repair_string')) {
        $retval = tidy_repair_string($retval, array('input-xml' => 1));
    }

    return $retval;
}

function plugin_getContentTableInfo_filemgmt()
{
    global $_DB_table_prefix;

    $contentItems = array(
            $_DB_table_prefix.'filemgmt_filedesc' => array(
                'plugin' => 'filemgmt',
                'primary_key' => 'id',
                'columns' => array(
                    'description',
                )
            ),
            $_DB_table_prefix.'filemgmt_filedetail' => array(
                'plugin' => 'filemgmt',
                'primary_key' => 'lid',
                'columns' => array(
                    'title',
                )
            )
    );

    return $contentItems;
}


/**
 * Get the field names to use for moderation.
 *
 * @return  array   Array(key_field, prod_table, fields, submission_table)
 */
function plugin_moderationvalues_filemgmt()
{
    global $_TABLES;

    if (SEC_hasRights('filemgmt.edit')) {
        return array (
            'lid',
            $_TABLES['filemgmt_filedetail'],
            "lid,status",
            '',
        );
    }
}


/**
 * Additional activity when a submission is approved.
 *
 * @param   string  $lid    File ID being approved
 */
function plugin_moderationapprove_filemgmt($lid)
{
    if (SEC_hasRights('filemgmt.edit')) {
        Filemgmt\Download::getInstance($lid)->approve();
    }
}


/**
 * Additional activity when a submission is deleted.
 *
 * @param   string  $lid    File ID being approved
 */
function plugin_moderationdelete_filemgmt($lid)
{
    if (SEC_hasRights('filemgmt.edit')) {
        Filemgmt\Download::getInstance($lid)->delete();
    }
}

/**
* Counts the items that are submitted
*
* @return   int     number of items in submission queue
*
*/
function plugin_submissioncount_filemgmt()
{
    global $_TABLES;

    $retval = 0;

    if (SEC_hasRights('filemgmt.edit')) {
        $retval = DB_count ($_TABLES['filemgmt_filedetail'],'status',0);
    }

    return $retval;
}


/**
 * Get a list of items to be moderated.
 *
 * @return  object  Plugin object containg item data
 */
function plugin_itemlist_filemgmt()
{
    global $_TABLES;

    if (SEC_hasRights('filemgmt.admin')) {
        $plugin = new Plugin();
        $plugin->submissionlabel = 'File Submissions';
        $plugin->getsubmissionssql =
            "SELECT f.lid AS id, f.title, FROM_UNIXTIME(f.date), c.title AS cat_title, f.submitter AS uid
            FROM {$_TABLES['filemgmt_filedetail']} f
            LEFT JOIN {$_TABLES['filemgmt_cat']} c
            ON c.cid = f.cid
            WHERE f.status = 0
            ORDER BY f.date ASC";
        return $plugin;
    }
}


/**
 * Check if the current user is a plugin administrator.
 *
 * @return  boolean     True for administrators, False for others
 */
function plugin_isadmin_filemgmt()
{
    static $isAdmin = NULL;
    if ($isAdmin === NULL) {
        $isAdmin = SEC_hasRights('filemgmt.admin');
    }
    return $isAdmin;
}


/**
 * Check if the current user is authorized to moderate the plugin.
 *
 * @return  boolean     True if a plugin moderator, False if not
 */
function plugin_ismoderator_filemgmt()
{
    static $isMod = NULL;
    if ($isMod === NULL) {
        if (
            plugin_isadmin_filemgmt() ||
            SEC_hasRights('filemgmt.edit')
        ) {
            $isMod = true;
        } else {
            $isMod = false;
        }
    }
    return $isMod;
}
