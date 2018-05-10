<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | Plugin interface to glFusion                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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
/*
* load the language file(s), including custom strings if any
*/
$langfile = $_CONF['path'].'plugins/filemgmt/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'].'plugins/filemgmt/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/filemgmt/language/english.php';
    $custfile = $_CONF['path'].'plugins/filemgmt/language/custom/english.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

/*
* Check and see if we need to load the plugin configuration
*/
if (!isset($_FM_CONF['perpage'])) {
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $fm_config = config::get_instance();
    $_FM_CONF = $fm_config->get_config('filemgmt');

    /*
     * Backward compatibility for now...
     */
    $mydownloads_popular        = $_FM_CONF['popular_download'];
    $mydownloads_newdownloads   = $_FM_CONF['newdownloads'];
    $mydownloads_perpage        = $_FM_CONF['perpage'];
    $mydownloads_trimdesc       = $_FM_CONF['trimdesc'];
    $mydownloads_whatsnew       = $_FM_CONF['whatsnew'];
    $mydownloads_dlreport       = $_FM_CONF['dlreport'];
    $mydownloads_selectpriv     = $_FM_CONF['selectpriv'];
    if ( $_FM_CONF['selectpriv'] == 1 ) {
        $mydownloads_publicpriv     = 0;
    } else {
        $mydownloads_publicpriv     = 1;
    }
    $mydownloads_uploadselect   = $_FM_CONF['uploadselect'];
    $mydownloads_uploadpublic   = $_FM_CONF['uploadpublic'];
    $mydownloads_useshots       = $_FM_CONF['useshots'];
    $mydownloads_shotwidth      = $_FM_CONF['shotwidth'];
    $filemgmt_Emailoption       = $_FM_CONF['Emailoption'];
    $filemgmt_FileStore         = $_FM_CONF['FileStore'];
    $filemgmt_SnapStore         = $_FM_CONF['SnapStore'];
    $filemgmt_SnapCat           = $_FM_CONF['SnapCat'];
    $filemgmt_FileStoreURL      = $_FM_CONF['FileStoreURL'];
    $filemgmt_FileSnapURL       = $_FM_CONF['FileSnapURL'];
    $filemgmt_SnapCatURL        = $_FM_CONF['SnapCatURL'];
    $filemgmtWhatsNewPeriodDays = $_FM_CONF['whatsnewperioddays'];
    $filemgmtWhatsNewTitleLength= $_FM_CONF['whatsnewtitlelength'];
    $filemgmt_showWhatsNewComments = $_FM_CONF['showwhatsnewcomments'];
}

if ( COM_isAnonUser() ) {
    $mydownloads_uploadselect = $mydownloads_uploadpublic;
}

require_once $_CONF['path'].'plugins/filemgmt/filemgmt.php';
require_once $_CONF['path'].'plugins/filemgmt/config.php';

/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
*/
function plugin_getmenuitems_filemgmt()
{
    global $_USER, $mydownloads_publicpriv, $_CONF, $LANG_FILEMGMT;

    if ( (COM_isAnonUser() ) && $mydownloads_publicpriv != 1 )  {
        return '';
    }

    $menuitems = array();
    $menuitems["{$LANG_FILEMGMT['downloads']}"] = $_CONF['site_url'] . "/filemgmt/index.php";
    return $menuitems;
}


/**
* Called by the plugin Editor to display the current plugin code version
* This may be different then the version installed and registered currently.
* If newer then you may want to run the update
*/
function plugin_chkVersion_filemgmt() {
    global $CONF_FM;
    return $CONF_FM['pi_version'];
}


/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_filemgmt() {
    global $_CONF,$_TABLES,$CONF_FM;

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
function plugin_savecomment_filemgmt($title,$comment,$id,$pid,$postmode) {
    global $_CONF,$_TABLES, $LANG03;

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
        $comments = CMT_getCount('filemgmt', $id);
        DB_change($_TABLES['filemgmt_filedetail'],'comments', $comments, 'lid',DB_escapeString($id));
        $lid = (int) substr($id,7);
        echo COM_refresh (COM_buildUrl ($_CONF['site_url']."/filemgmt/index.php?id=$lid"));
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
    $comments = CMT_getCount('filemgmt', $sid);
    DB_change($_TABLES['filemgmt_filedetail'],'comments', $comments, 'lid',DB_escapeString($sid));
}

function plugin_editcomment_filemgmt($cid,$id) {
    global $_CONF;

    echo COM_refresh (COM_buildUrl ($_CONF['site_url'].'/filemgmt/index.php?id=$id'));
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
function plugin_deletecomment_filemgmt($cid,$id) {
    global $_CONF,$_TABLES;

    if (SEC_hasRights("filemgmt.edit")) {
        if (CMT_deleteComment($cid, $id, 'filemgmt') == 0) {
            // Now redirect the program flow to the view of the file and its comments
            return (COM_refresh($_CONF['site_url'] . "/filemgmt/index.php?id=$id"));
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
function plugin_displaycomment_filemgmt($id,$commentid,$title,$order,$format,$page,$view) {
    global $_USER, $_FM_CONF, $filemgmt_FileSnapURL, $FilemgmtAdmin ,$mydownloads_dlreport ,$mydownloads_publicpriv, $LANG_FILEMGMT, $_TABLES, $_CONF, $LANG01,$_DB_name;
    global $filemgmt_FileStore;

    if ( COM_isAnonUser() && $mydownloads_publicpriv != 1 )  {
        return '';
    }

    $id = str_replace('fileid_','',$id);
    /* Plugin specific code to display relevant content above the comment thread */
    /* Example: Display the file details */
    include $_CONF['path'] .'plugins/filemgmt/include/functions.php';
    include_once $_CONF['path']. 'plugins/filemgmt/include/xoopstree.php';
    include_once $_CONF['path']. 'plugins/filemgmt/include/textsanitizer.php';

    $groupsql = filemgmt_buildAccessSql();

    $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
    $sql .= "WHERE a.lid='".DB_escapeString($id)."' $groupsql AND a.status > 0";
    list($fileAccessCnt) = DB_fetchArray( DB_query($sql));

    if ($fileAccessCnt > 0 AND DB_count($_TABLES['filemgmt_filedetail'],"lid",DB_escapeString($id) ) == 1) {

        $comments = true;   // Checked in filemgmt code in dlformat.php

        $myts = new MyTextSanitizer; // MyTextSanitizer object
        $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");

        $display = COM_startBlock("<b>". $LANG_FILEMGMT['plugin_name'] ."</b>");
        $fields = 'd.lid, d.cid, d.title, d.url, d.homepage, d.version, d.size, d.logourl,';
        $fields .= 'd.submitter, d.status, d.date, d.hits, d.rating, d.votes, t.description';

        $sql = "SELECT $fields FROM {$_TABLES['filemgmt_filedetail']} d, ";
        $sql .= "{$_TABLES['filemgmt_filedesc']} t ";
        $sql .= "WHERE d.lid='".DB_escapeString($id)."' AND d.lid=t.lid AND status > 0";
        $result=DB_query($sql);
        list($lid, $cid, $dtitle, $url, $homepage, $version, $size, $logourl, $submitter, $status, $time, $hits, $rating, $votes, $description)=DB_fetchARRAY($result);

        $p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $p->set_file (array (
            'page'             =>     'filelisting.thtml',
            'records'          =>     'filelisting_record.thtml',
            'category'         =>     'filelisting_category.thtml'));

        $p->set_var ('layout_url', $_CONF['layout_url']);
        $p->set_var ('site_url',$_CONF['site_url']);

        $pathstring = "<a href='index.php'>"._MD_MAIN."</a>&nbsp;:&nbsp;";
        $nicepath = $mytree->getNicePathFromId($cid, "title", "{$_CONF['site_url']}/filemgmt/viewcat.php?op=");
        $pathstring .= $nicepath;
        $p->set_var('category_path_link',$pathstring);

        $rating = number_format($rating, 2);
        $dtitle = $myts->makeTboxData4Show($dtitle);
        $url = $myts->makeTboxData4Show($url);
        $homepage = $myts->makeTboxData4Show($homepage);
        $version = $myts->makeTboxData4Show($version);
        $size = $myts->makeTboxData4Show($size);
    //    $platform = $myts->makeTboxData4Show($platform);
        $logourl = $myts->makeTboxData4Show($logourl);
        $datetime = formatTimestamp($time);
        $description = $myts->makeTareaData4Show($description,0); //no html
        $result2 = DB_query("SELECT username,fullname,photo FROM {$_TABLES['users']} WHERE uid = $submitter");
        list ($submitter_name,$submitter_fullname,$photo) = DB_fetchARRAY($result2);
        $submitter_name = COM_getDisplayName ($submitter, $submitter_name, $submitter_fullname);
        include $_CONF['path'] .'plugins/filemgmt/include/dlformat.php';
        $p->set_var('cssid',1);
        $p->parse ('filelisting_records', 'records');
        $p->parse ('output', 'page');
        $display .= $p->finish ($p->get_var('output'));

        $display .= COM_endBlock();

        /* Get formatted comment thread */
        if (SEC_hasRights('filemgmt.edit')) {
            $delete_option = true;
        } else {
            $delete_option = false;
        }

        if ($view == 1) {
            $display .= CMT_userComments ("fileid_$id", $title, 'filemgmt',$order,$format,$commentid,$page,true,$delete_option,0,$submitter);
        } else {
            $display .= CMT_userComments ("fileid_$id", $title, 'filemgmt',$order,$format,$commentid,$page,false,$delete_option,0,$submitter);
        }
    } else {
        return false;
    }

    return $display;
}

function plugin_user_delete_filemgmt( $uid )
{
    global $_CONF, $_USER, $_TABLES;

    // purge all rows from history
    DB_delete ($_TABLES['filemgmt_history'], 'uid', $uid);

    // Anonymize all data for ratinguser in filemgmt_vote / Blank out ratinghostname
    DB_query("UPDATE {$_TABLES['filemgmt_votedata']} SET ratinghostname='', ratinguser=1 WHERE ratinguser = ".(int) $uid,1);

    // Purge all broken links table entries for sender = uid
    DB_delete ($_TABLES['filemgmt_brokenlinks'], 'sender', $uid);

    // Anonymize submitter for filemgmt_filedetails
    DB_query("UPDATE {$_TABLES['filemgmt_filedetail']} SET submitter=1 WHERE submitter = ".(int) $uid,1);
}

function plugin_statssummary_filemgmt ()
{
    global $_USER, $mydownloads_publicpriv, $_TABLES, $LANG_FILEMGMT;

    if ( (COM_isAnonUser() ) && $mydownloads_publicpriv != 1 )  {
        return '';
    }

    $sql = "SELECT COUNT(*), SUM(hits) FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
    $sql .= filemgmt_buildAccessSql('WHERE');
    list($total_files,$total_downloads) = DB_fetchArray( DB_query($sql));

    return array ($LANG_FILEMGMT['nofiles'], COM_numberFormat ($total_files)
                  . '(' . COM_numberFormat ($total_downloads) . ')');
}


/**
* shows the statistics for the Filemgmot plugin on stats.php.  If $showsitestats
* is 1 then we are to only print the overall stats in the 'site statistics box'
* otherwise we show the detailed stats for the photo album
*
* @showsitestats        int         Flag to let us know which stats to get
*/
function plugin_showstats_filemgmt($showsitestats)
{
    global $_USER, $mydownloads_publicpriv, $LANG_FILEMGMT, $_TABLES, $_CONF;

    if ( COM_isAnonUser() && $mydownloads_publicpriv != 1 )  {
        return '';
    }

    $retval = '';

    $stat_templates = new Template($_CONF['path_layout'] . 'stats');
    $stat_templates->set_file(array('itemstats'=>'itemstatistics.thtml',
                            'statrow'=>'singlestat.thtml'));
    if ($showsitestats == 1) {
        $sql = "SELECT COUNT(*), SUM(hits) FROM {$_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= filemgmt_buildAccessSql('WHERE');
        list($total_files,$total_downloads) = DB_fetchArray( DB_query($sql));
        $retval = "<table border = '0' width='100%' cellspacing='0' cellpadding='0'>";
        $retval .= "<tr><td>" . $LANG_FILEMGMT['nofiles'] . "</td>";
        $retval .= "<td align='right'>" . $total_files . " (" .$total_downloads .")&nbsp;&nbsp;&nbsp;</td></tr></table>";
    } else {
        $header_arr = array(
            array('text' => 'File Name', 'field' => 'title','header_class' => 'stats-header-title'),
            array('text' => 'Hits', 'field' => 'hits','field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => 'Top Ten Downloaded Files',
        );

        $sql  = "SELECT a.lid, a.title, hits from {$_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= filemgmt_buildAccessSql('WHERE');
        $sql .= " AND hits > 0 ORDER BY hits desc LIMIT 10";
        $result = DB_query($sql);
        $nrows  = DB_numRows($result);
        if ($nrows > 0) {
            for ($i = 0; $i < $nrows && $i < 10; $i++) {
                list ($lid, $title,$hits) = DB_fetchARRAY($result);
                $S['title'] = '<a href="' . $_CONF['site_url'] . '/filemgmt/index.php?id=' . $lid . '">' . $title . '</a>';
                $S['hits']  = @number_format($hits);
                $data_arr[$i] = $S;
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
*
*/

function plugin_searchtypes_filemgmt()
{
    global $_USER, $mydownloads_publicpriv, $LANG_FILEMGMT;

    if ( (COM_isAnonUser() ) && $mydownloads_publicpriv != 1 )  {
        return '';
    }

    $tmp['filemgmt'] = $LANG_FILEMGMT['searchlabel'];

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
function plugin_dopluginsearch_filemgmt($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_USER, $mydownloads_publicpriv, $LANG_FILEMGMT, $_TABLES, $_CONF, $filemgmt_FileStoreURL;

    if ( COM_isAnonUser() && $mydownloads_publicpriv != 1 )  {
        return '';
    }
    $query = trim(DB_escapeString(htmlspecialchars($query)));

    $sql  = "SELECT a.lid AS id, a.title, description, date, submitter AS uid, hits, CONCAT('/filemgmt/index.php?id=',a.lid) AS url ";
    $sql .= "FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON b.cid=a.cid ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_filedesc']} c ON c.lid=a.lid ";
    $sql .= filemgmt_buildAccessSql('WHERE');
    $sql .= " AND a.status > 0 ";

    if (!empty ($author))
        $sql .= "AND (submitter = '$author') ";

    $search = new SearchCriteria('filemgmt', 'FileMgmt');
    $columns = array('c.description','a.title');
    $sql .= $search->getDateRangeSQL('AND', 'date', $datestart, $dateend);
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(3);

    return $search;
}

/**
* this searches the plugins comments matching the user query and returns an array of
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
function plugin_dopluginsearch_comment_filemgmt($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_USER, $mydownloads_publicpriv, $LANG_FILEMGMT, $_TABLES, $_CONF, $filemgmt_FileStoreURL, $LANG09;

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    $sql =  "SELECT a.lid AS id, c.title AS title, c.comment AS description, UNIX_TIMESTAMP(c.date) AS date, c.uid AS uid, '0' AS hits, ";
    $sql .= "CONCAT('/filemgmt/index.php?id=',a.lid,'#comments') AS url ";
    $sql .= "FROM {$_TABLES['users']} AS u, {$_TABLES['comments']} AS c ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_filedetail']} a ON c.sid=CONCAT('fileid_',a.lid) ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON b.cid=a.cid ";
    $sql .= "WHERE (c.type='filemgmt' AND c.queued = 0) AND (u.uid = c.uid) ";
    $sql .= filemgmt_buildAccessSql('AND');
    $sql .= " AND a.status > 0 ";

    if (!empty($author)) {
        $sql .= "AND (c.uid = '$author') ";
    }

    $search_c = new SearchCriteria('comments', 'FileMgmt > '.$LANG09[66]);
    $columns = array('comment','c.title');
    $sql .= $search_c->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(c.date)', $datestart, $dateend);
    list($sql,$ftsql) = $search_c->buildSearchSQL($keyType, $query, $columns, $sql);
    $search_c->setSQL($sql);
    $search_c->setFTSQL($ftsql);
    $search_c->setRank(2);

    return $search_c;
}


/**
* This will put an option for Filemgmt Admin in the command and control block on
* moderation.php
*
*/
function plugin_cclabel_filemgmt()
{
    global $LANG_FILEMGMT, $_CONF;
    if (SEC_hasRights('filemgmt.edit')) {
        return array($LANG_FILEMGMT['plugin_name'],$_CONF['site_admin_url'] . '/plugins/filemgmt/index.php',$_CONF['site_url'] . '/filemgmt/images/filemgmt.png');
    } else {
        return false;
    }

}

/**
* Setup the user menu options for this plugin
*
*/

function plugin_getuseroption_filemgmt() {
    global $_CONF, $_TABLES, $LANG_FILEMGMT,$mydownloads_uploadselect;

    if (SEC_hasRights('filemgmt.upload') OR $mydownloads_uploadselect == 1) {
        $groupsql = filemgmt_buildAccessSql();
        $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
        $sql .= $groupsql;
        list($catAccessCnt) = DB_fetchArray( DB_query($sql));
        if ( $catAccessCnt > 0 ) {
           return array($LANG_FILEMGMT['usermenu3'], $_CONF['site_url'] . '/filemgmt/submit.php');
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
    global $_CONF, $_TABLES, $LANG_FILEMGMT;
    if (SEC_hasRights('filemgmt.edit')) {
        $result = DB_query("SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} WHERE status=0");
        list($submittedfiles) = DB_fetchARRAY($result);
        return array($LANG_FILEMGMT['admin_menu'], $_CONF['site_admin_url'] . '/plugins/filemgmt/index.php', $submittedfiles);
    }
}


// Common function used to build group access SQL
function filemgmt_buildAccessSql($clause='AND') {
    global $_TABLES,$_USER;

    if (!COM_isAnonUser() ) {
        $uid = $_USER['uid'];
    } else {
        $uid = 1;
    }

    $_GROUPS = SEC_getUserGroups($uid);
    $groupsql = '';
    if (count($_GROUPS) == 1) {
        $groupsql .= " $clause grp_access = '" . current($_GROUPS) ."'";
    } else {
        $groupsql .= " $clause grp_access IN (" . implode(',',array_values($_GROUPS)) .")";
    }
    return $groupsql;
}

function plugin_getfeednames_filemgmt ()
{
    global $_TABLES;

    $feeds = array ();
    $groupsql = filemgmt_buildAccessSql('WHERE');
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
            $where .= "filedetail.date >= DATE_SUB(NOW(),INTERVAL $hours HOUR)";
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
    global $_CONF, $_TABLES;

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
        $filelink = $_CONF['site_url'] . '/filemgmt/index.php?id='
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
        $link = $_CONF['site_url'] . '/filemgmt/index.php';
    } else {
        $link = $_CONF['site_url'] . '/filemgmt/viewcat.php?cid=' . $F['topic'];
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
function plugin_whatsnewsupported_filemgmtxx() {
    global $_USER, $mydownloads_publicpriv, $_CONF, $LANG_FM00,$mydownloads_whatsnew,$filemgmtWhatsNewPeriodDays;

    if ( (COM_isAnonUser() ) && $mydownloads_publicpriv != 1 )  {
        return '';
    }

    if ($mydownloads_whatsnew == 1) {
        return array(
            $LANG_FM00['WhatsNewLabel'],
            sprintf($LANG_FM00['WhatsNewPeriod'], $filemgmtWhatsNewPeriodDays)
            );
    } else {
        return false;
    }
}


/**
  * API function provides the content of our "What's New" feed
*/
function plugin_getwhatsnew_filemgmt() {
    global $_TABLES, $_CONF, $LANG_FILEMGMT,$LANG_FM00, $LANG01;
    global $filemgmtWhatsNewTitleLength,$filemgmtWhatsNewPeriodDays,$filemgmt_showWhatsNewComments,$mydownloads_publicpriv;
    global $mydownloads_whatsnew;

    $retval = '';

    $items = array();
    if ((SEC_hasRights('filemgmt.user') OR $mydownloads_publicpriv == 1) AND $mydownloads_whatsnew == 1) {
        $sql  = "SELECT a.lid, a.title FROM {$_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= "WHERE date >= UNIX_TIMESTAMP( DATE_SUB(NOW(), INTERVAL $filemgmtWhatsNewPeriodDays DAY )) ";
        $sql .= filemgmt_buildAccessSql();
        $sql .= "AND STATUS=1 ORDER  BY date DESC LIMIT 15 ";

        $result = DB_query( $sql );

        $nrows1 = DB_numRows( $result );
        if( $nrows1 == 0 ) {
            $retval .= $LANG_FILEMGMT['no_new_files'] . '<br' . XHTML . '>' . LB;
        } else {
            for( $i = 0; $i < $nrows1; $i++ ) {
                list($lid, $ftitle) = DB_fetchArray( $result );
                $str = "<a href=\"{$_CONF['site_url']}/filemgmt/index.php?id={$lid}\" title=\"".htmlspecialchars($ftitle,ENT_COMPAT,COM_getEncodingt())."\" rel=\"nofollow\">";
				if( strlen( $ftitle ) > $filemgmtWhatsNewTitleLength ) {
					$titleTrimLength = $filemgmtWhatsNewTitleLength - 3;
                    $str .= COM_truncate( $ftitle,$titleTrimLength, '...' );
				} else {
					$str .= $ftitle;
				}
                $str .= '</a>';
                $items[] = $str;
            }
        }
        if ($filemgmt_showWhatsNewComments) {
            // Search for new comments
            $sql  = "SELECT a.lid,a.title,c.title FROM {$_TABLES['filemgmt_filedetail']} a ";
            $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
            $sql .= "LEFT JOIN {$_TABLES['comments']} c ON c.sid = concat('fileid_' ,a.lid )";
            $sql .= filemgmt_buildAccessSql('WHERE');
            $sql .= " AND c.date >=  DATE_SUB(NOW(), INTERVAL $filemgmtWhatsNewPeriodDays DAY ) AND c.type='filemgmt' AND c.queued = 0 ";
            $sql .= "ORDER  BY c.date DESC LIMIT 15 ";

            $result = DB_query($sql);

            $nrows2 = DB_numRows( $result );
            if( $nrows2 == 0 ) {
                $retval .= $LANG_FILEMGMT['no_comments'] . '<br' . XHTML . '>' . LB;
            } else {
                for( $i = 0; $i < $nrows2; $i++ ) {
                    list($lid, $ftitle, $ctitle) = DB_fetchArray( $result );
                    $commentTitle = $ctitle;
                    $titlePrefix = "<b>C:</b>&nbsp;";
                    $ctitle = $titlePrefix . $ctitle;
                    $titleLength = $filemgmtWhatsNewTitleLength + strlen( $titlePrefix ) - 3;        // Compensate for the added HTML
                    $str = "<a href=\"{$_CONF['site_url']}/filemgmt/index.php?id={$lid}\" title=\"".htmlspecialchars($commentTitle,ENT_COMPAT,COM_getEncodingt())."\" rel=\"nofollow\" >";
					if( strlen( $ctitle ) > $titleLength ) {
						$titleTrimLength = $titleLength - 3;
	                    $str .= COM_truncate( $ctitle,$titleTrimLength, '...' );
					} else {
						$str .= $ctitle;
					}
                    $str .= '</a>';
                    $items[] = $str;
                }
            }
        }

        if ($nrows1 == 0 and $nrows2 == 0) {
            return '';
        } else {
            return array($LANG_FM00['WhatsNewLabel'],sprintf($LANG_FM00['WhatsNewPeriod'], $filemgmtWhatsNewPeriodDays),$items);
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
    global $_USER, $mydownloads_publicpriv, $_CONF, $_TABLES, $LANG_FILEMGMT_AUTOTAG;

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
                $sql .= filemgmt_buildAccessSql();
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
            $title = $F['title'];

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
                    $url = COM_buildUrl ($_CONF['site_url'] . '/filemgmt/index.php?id='
                                     . $file_id);
                    break;
                case 'file_download' :
                    if ( $target_window != '' ) {
                        $url = $_CONF['site_url'] . '/filemgmt/visit.php?lid='. $file_id;
                        $target = 'target="'.$target_window.'"';
                    } else {
                        $url = $_CONF['site_url'] . '/filemgmt/visit.php?lid='.$file_id;
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
    global $_CONF, $_TABLES, $LANG_FILEMGMT, $mydownloads_publicpriv;

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

    $groupsql = filemgmt_buildAccessSql();

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
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/filemgmt/index.php?id=' . $id);
                    } else {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/filemgmt/index.php?id=' . $A['lid']);
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
                    if ( ($mydownloads_publicpriv == 1 && $A['grp_access'] == 13 ) || $A['grp_access'] == 2 ) $p_anon = 2;
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

function plugin_privacy_export_filemgmt($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_FM_CONF, $_TABLES, $_USER;

    $retval = '';

// files

    $exportFields = array('lid','title','url','homepage','version','submitter','date','permalink');

    $sql = "SELECT * FROM {$_TABLES['filemgmt_filedetail']} WHERE submitter = ". (int) $uid;
/*
    if ( $ip != '' ) {
        $sql .= " OR media_user_ip = '" . DB_escapeString($ip)."'";
    }
*/
    $sql .= " ORDER BY date ASC";

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

    $retval .= "<files>\n";

    foreach($rows AS $row) {
        $row['permalink'] = $_CONF['site_url'].'/filemgmt/index.php?id='.$row['lid'];
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
        $row['permalink'] = $_CONF['site_url'].'/filemgmt/index.php?id='.$row['lid'];
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
        $retval = tidy_repair_string($retval, ['input-xml' => 1]);
    }

    return $retval;
}

?>