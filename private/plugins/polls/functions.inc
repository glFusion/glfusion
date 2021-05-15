<?php
/**
* glFusion CMS - Polls Plugin
*
* Plugin APIs
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs         tony AT tonybibbs DOT com
*  Tom Willett        twillett AT users DOT sourceforge DOT ne
*  Blaine Lang        langmail AT sympatico DOT ca
*  Dirk Haun          dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die('This file can not be used on its own.');
}

use \glFusion\Log\Log;
use \glFusion\FieldList;

/*
* load the language file(s), including custom strings if any
*/
$langfile = $_CONF['path'] . 'plugins/polls/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'] . 'plugins/polls/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/polls/language/english_utf-8.php';
    $custfile = $_CONF['path'].'plugins/polls/language/custom/english_utf-8.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

/*
* Check and see if we need to load the plugin configuration
*/
if (!isset($_PO_CONF['pollsloginrequired'])) {
    $po_config = config::get_instance();
    $_PO_CONF = $po_config->get_config('polls');
}

require_once $_CONF['path'].'plugins/polls/polls.php';

// +---------------------------------------------------------------------------+
// | glFusion Plugin API Implementations                                       |
// +---------------------------------------------------------------------------+

/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
* @return   mixed   menu entry, or boolean false if disabled / hidden
*/
function plugin_getmenuitems_polls()
{
    global $_CONF, $_USER, $_PO_CONF, $LANG_POLLS;

    $anon = (COM_isAnonUser()) ? true : false;
    if (($_PO_CONF['hidepollsmenu'] == 1) || ($anon &&
                ($_CONF['loginrequired'] || $_PO_CONF['pollsloginrequired']))) {
        return false;
    }

    $menuitems[$LANG_POLLS['polls']] = $_CONF['site_url'] . '/polls/index.php';

    return $menuitems;
}

/**
* glFusion is checking to see if this plugin supports comments, tell it yes.
*
* NOTE: to support comments you must used the same date/time based ID for your
* widget.  In other words, to make primary keys for your plugin you should call
* makesid().  Comments are keyed off of that...it is a limitation on how glFusion
* does comments.
*
*/
function plugin_commentsupport_polls()
{
    // polls will use comments
    return true;
}

function plugin_getcommenturlid_polls() {
    global $_CONF;
    $retval = array();
    $retval[] = $_CONF['site_url'] . '/polls/index.php';
    $retval[] = 'pid';
    return $retval;
}

/**
 * Poll saves a comment
 *
 * @param   string  $title  comment title
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_savecomment_polls($title, $comment, $id, $pid, $postmode)
{
    global $_CONF, $_TABLES, $LANG03, $_USER;

    $retval = '';

    $commentcode = DB_getItem ($_TABLES['polltopics'], 'commentcode',
                               "pid = '".DB_escapeString($id)."'");
    if ($commentcode != 0) {
        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    $ret = CMT_saveComment ($title, $comment, $id, $pid, 'polls', $postmode);
    if ($ret > 0) {
        $retval .= CMT_commentForm ($title, $comment, $id, $pid, 'polls',
                                   $LANG03[14], $postmode);
    } else { // success
        $retval = COM_refresh ($_CONF['site_url']
                                . "/polls/index.php?pid=$id");
    }
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    return $retval;
}

function plugin_editcomment_polls($cid,$id) {
    global $_CONF;

    echo COM_refresh ($_CONF['site_url']."/polls/index.php?pid=$id");
    exit;
}

/**
 * polls: delete a comment
 *
 * @param   int     $cid    Comment to be deleted
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_deletecomment_polls($cid, $id)
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $has_editPermissions = SEC_hasRights ('polls.edit');
    $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['polltopics']} WHERE pid = '".DB_escapeString($id)."'");
    $A = DB_fetchArray ($result);

    if ($has_editPermissions && SEC_hasAccess ($A['owner_id'],
            $A['group_id'], $A['perm_owner'], $A['perm_group'],
            $A['perm_members'], $A['perm_anon']) == 3) {
        CMT_deleteComment($cid, $id, 'polls');
        $retval .= COM_refresh ($_CONF['site_url']
                                 . "/polls/index.php?pid=$id");
    } else {
        Log::write('system',Log::WARNING,'User '.$_USER['username'].' (IP: '.$_SERVER['REAL_ADDR'].') '
                    . 'did not have permissions to delete comment '.$cid.' from poll '.$id);
        $retval .= COM_refresh ($_CONF['site_url'] . '/index.php');
    }
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    return $retval;
}

function plugin_getwhatsnewcomment_polls()
{
    global $_CONF, $_TABLES, $_USER;

    $commentrow = array();

    $stwhere = '';
    if( !COM_isAnonUser() ) {
        $stwhere .= "({$_TABLES['polltopics']}.owner_id IS NOT NULL AND {$_TABLES['polltopics']}.perm_owner IS NOT NULL) OR ";
        $stwhere .= "({$_TABLES['polltopics']}.group_id IS NOT NULL AND {$_TABLES['polltopics']}.perm_group IS NOT NULL) OR ";
        $stwhere .= "({$_TABLES['polltopics']}.perm_members IS NOT NULL)";
    } else {
        $stwhere .= "({$_TABLES['polltopics']}.perm_anon IS NOT NULL)";
    }
    $sql = "SELECT DISTINCT COUNT(*) AS dups, {$_TABLES['polltopics']}.topic AS title,
            {$_TABLES['polltopics']}.pid AS sid, UNIX_TIMESTAMP(max({$_TABLES['comments']}.date)) AS lastdate
            FROM {$_TABLES['comments']} LEFT JOIN {$_TABLES['polltopics']} ON (({$_TABLES['polltopics']}.pid = {$_TABLES['comments']}.sid)"
            . COM_getPermSQL( 'AND', 0, 2, $_TABLES['polltopics'] ) .
            " AND ({$_TABLES['polltopics']}.commentcode >= 0)" .
            /* COM_getLangSQL( 'pid', 'AND', $_TABLES['polltopics'] ) .*/ ")
            WHERE ({$_TABLES['comments']}.queued = 0 AND {$_TABLES['comments']}.date >= (DATE_SUB('".$_CONF['_now']->toMySQL(true)."', INTERVAL {$_CONF['newcommentsinterval']} SECOND)))
            AND ((({$stwhere}))) GROUP BY {$_TABLES['comments']}.sid,type, {$_TABLES['polltopics']}.topic,
            {$_TABLES['polltopics']}.topic, {$_TABLES['polltopics']}.pid
            ORDER BY 4 DESC LIMIT 15";

    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );
    if ( $nrows > 0 ) {
        $filter = new sanitizer();
        $filter->setPostmode('text');

        for ($x = 0; $x < $nrows; $x++ ) {
            $A = DB_fetchArray($result);
            $cmtReturn['url'] = $_CONF['site_url'].'/polls/index.php?pid=' . $A['sid'] . '#comments';
            $cmtReturn['type'] = 'polls';
            $cmtReturn['dups'] = $A['dups'];
            $cmtReturn['title'] = $filter->filterData($A['title']);
            $cmtReturn['lastdate'] = $A['lastdate'];
            $commentrow[] = $cmtReturn;
        }
    }
    return $commentrow;
}

/**
* glFusion is asking us to provide any items that show up in the type
* drop-down on search.php.  Let's users search for events.
*
* @return   array   (plugin name/entry title) pair for the dropdown
*
*/

function plugin_searchtypes_polls()
{
    global $LANG_POLLS;

    return array('polls' => $LANG_POLLS['polls']);

}

/**
 * This searches for polls matching the user query and returns an array of
 * for the header and table rows back to search.php where it will be formatted and
 * printed.
 *
 * @param    string $query     Keywords user is looking for
 * @param    string $dateStart Start date to get results for
 * @param    string $dateEnd   End date to get results for
 * @param    string $topic     The topic they were searching in
 * @param    string $type      Type of items they are searching, or 'all' (deprecated)
 * @param    int    $author    Get all results by this author
 * @param    string $keyType   search key type: 'all', 'phrase', 'any'
 * @param    int    $page      page number of current search (deprecated)
 * @param    int    $perpage   number of results per page (deprecated)
 * @return   SearchCriteria
  */
function plugin_dopluginsearch_polls($query, $dateStart, $dateEnd, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_CONF, $_TABLES, $_DB_dbms, $LANG09, $LANG_POLLS, $_USER;

    // Make sure the query is SQL safe
    $query = trim(DB_escapeString($query));
    $uid = isset($_USER['uid']) ? $_USER['uid'] : 1;

    // Search polls
    $sql = "SELECT p.pid AS id, p.topic AS title, p.description AS description, UNIX_TIMESTAMP(p.date) AS date, "
        . " p.owner_id AS uid, p.voters AS hits, CONCAT('/polls/index.php?pid=',p.pid) AS url "
        . " FROM {$_TABLES['polltopics']} AS p "
        . " WHERE (p.date <= '".$_CONF['_now']->toMySQL(true)."') "
        . COM_getPermSQL('AND', 0, 2, 'p');

    if (!SEC_inGroup('Root', $uid)) {
        $sql .= " AND ((p.hideresults = 0) OR (p.owner_id = {$uid})) ";
    }

    if (!empty($author)) {
        $sql .= "AND (p.owner_id = '{$author}') ";
    }

    $search = new SearchCriteria('polls', array($LANG_POLLS['polls']));

    $columns = array('title' => 'p.topic', 'description' => 'p.description');
    $sql .= $search->getDateRangeSQL('AND', 'p.date', $dateStart, $dateEnd);
    list($sql, $ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);

    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(2);

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
function plugin_dopluginsearch_comment_polls($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_USER, $_TABLES, $_CONF, $LANG09;

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    $sql =  "SELECT c.cid AS id1, a.pid AS id, c.title AS title, c.comment AS description, UNIX_TIMESTAMP(c.date) AS date, c.uid AS uid, '0' AS hits, ";
    $sql .= "CONCAT('/polls/index.php?pid=',a.pid,'#comments') AS url ";
    $sql .= "FROM {$_TABLES['users']} AS u, {$_TABLES['comments']} AS c ";
    $sql .= "LEFT JOIN {$_TABLES['polltopics']} a ON c.sid=a.pid ";
    $sql .= "WHERE (c.type='polls' AND c.queued = 0) AND (u.uid = c.uid) ";
    $sql .= COM_getPermSQL ('AND');

    if (!empty($author)) {
        $sql .= "AND (c.uid = '$author') ";
    }

    $search_c = new SearchCriteria('comments', 'Polls > '.$LANG09[66]);
    $columns = array('comment','c.title');
    $sql .= $search_c->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(c.date)', $datestart, $dateend);
    list($sql,$ftsql) = $search_c->buildSearchSQL($keyType, $query, $columns, $sql);
    $search_c->setSQL($sql);
    $search_c->setFTSQL($ftsql);
    $search_c->setRank(2);

    return $search_c;
}

/**
* Shows the statistics for the Polls plugin on stats.php.
* If $showsitestats is 1 then we are to only print the overall stats in the
* 'site statistics box' otherwise we show the detailed stats
*
* @param    int     showsitestats   Flag to let us know which stats to get
*/
function plugin_showstats_polls($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_POLLS;

    require_once ($_CONF['path_system'] . 'lib-admin.php');

    $retval = '';

    $result = DB_query ("SELECT pid,topic,voters FROM {$_TABLES['polltopics']} WHERE (voters > 0)" . COM_getPermSQL ('AND') . " ORDER BY voters DESC LIMIT 10");
    $nrows  = DB_numRows ($result);

    if ($nrows > 0) {
        $header_arr = array(
            array('text'         => $LANG_POLLS['stats_topics'],
                  'field'        => 'pid',
                  'header_class' => 'stats-header-title'
            ),
            array('text'         => $LANG_POLLS['stats_votes'],
                  'field'        => 'voters',
                  'header_class' => 'stats-header-count',
                  'field_class'  => 'stats-list-count'
            ),
        );
        $data_arr = array();
        $text_arr = array('has_menu' => false,
                          'title'    => $LANG_POLLS['stats_top10'],
        );

        $filter = new sanitizer();
        $filter->setPostmode('text');

        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray ($result);
            $url = $_CONF['site_url'] . '/polls/index.php?pid=' . $A['pid']
                 . '&amp;aid=-1';
            $pid = COM_createLink($filter->filterData($A['topic']), $url);
            $voters = COM_NumberFormat ($A['voters']);
            $data_arr[] = array ('pid' => $pid, 'voters' => $voters);
        }
        $retval .= ADMIN_simpleList ('', $header_arr, $text_arr, $data_arr);
    } else {
        $retval .= COM_startBlock ($LANG_POLLS['stats_top10'],'',COM_getBlockTemplate('_admin_block', 'header'));
        $retval .= $LANG_POLLS['stats_none'];
        $retval .= COM_endBlock (COM_getBlockTemplate('_admin_block', 'footer'));
    }

    return $retval;
}

/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
*
*/
function plugin_statssummary_polls()
{
    global $LANG_POLLS;

    list($total_polls, $total_answers) = POLLS_countPollsAndVotes ();

    $item_count = COM_numberFormat ($total_polls)
                . ' (' . COM_numberFormat ($total_answers) . ')';

    return array ($LANG_POLLS['stats_summary'], $item_count);
}


/**
* This will put an option for polls in the command and control block on
* moderation.php
*
*/
function plugin_cclabel_polls()
{
    global $_CONF, $LANG_POLLS;

    if (SEC_hasRights ('polls.edit')) {
        return array ($LANG_POLLS['polls'],
                $_CONF['site_admin_url'] . '/plugins/polls/index.php',
                plugin_geticon_polls ());
    }

    return false;
}


/**
* returns the administrative option for this plugin
*
*/
function plugin_getadminoption_polls()
{
    global $_CONF, $_TABLES, $LANG_POLLS;

    if (SEC_hasRights ('polls.edit')) {
        $result = DB_query ("SELECT COUNT(*) AS cnt FROM {$_TABLES['polltopics']}" . COM_getPermSQL ());
        $A = DB_fetchArray ($result);
        $total_pages = $A['cnt'];

        return array ($LANG_POLLS['polls'],
                      $_CONF['site_admin_url'] . '/plugins/polls/index.php',
                      $total_pages);
    }
}

function plugin_user_move_polls($origUID, $destUID)
{
    global $_TABLES;
    DB_query ("UPDATE {$_TABLES['polltopics']} SET owner_id = ".(int)$destUID." WHERE owner_id = ".(int)$origUID,1);
}


/**
* A user is about to be deleted. Update ownership of any polls owned
* by that user or delete them.
*
* @param   uid   int   User id of deleted user
*
*/
function plugin_user_delete_polls($uid)
{
    global $_TABLES, $_PO_CONF;

    // Anonymize pollvoters by uid / ipaddress

    DB_query("UPDATE {$_TABLES['pollvoters']} SET uid=1, ipaddress='' WHERE uid=".(int) $uid);

    if (DB_count ($_TABLES['polltopics'], 'owner_id', intval($uid)) == 0) {
        // there are no polls owned by this user
        return;
    }

    if ($_PO_CONF['delete_polls'] == 1) {
        // delete the polls
        $result = DB_query ("SELECT pid FROM {$_TABLES['polltopics']} WHERE owner_id=".intval($uid));
        $numPolls = DB_numRows ($result);
        for ($i = 0; $i < $numPolls; $i++) {
            list($pid) = DB_fetchArray ($result);
            DB_delete ($_TABLES['pollanswers'], 'pid', $pid);
            DB_delete ($_TABLES['pollvoters'], 'pid', $pid);
            DB_delete($_TABLES['comments'], array('sid', 'type'),
                                            array(DB_escapeString($pid), 'polls'));
        }
        DB_delete ($_TABLES['polltopics'], 'owner_id', intval($uid));
    } else {
        // assign ownership to a user from the Root group
        $rootgroup = DB_getItem ($_TABLES['groups'], 'grp_id',
                                 "grp_name = 'Root'");
        $result = DB_query ("SELECT DISTINCT ug_uid FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $rootgroup ORDER BY ug_uid LIMIT 1");
        list($rootuser) = DB_fetchArray ($result);

        DB_query ("UPDATE {$_TABLES['polltopics']} SET owner_id = $rootuser WHERE owner_id = ".intval($uid));
    }
}

/**
* Return the current version of code.
* Used in the Plugin Editor to show the registered version and code version
*/
function plugin_chkVersion_polls()
{
    global $_PO_CONF;

    return $_PO_CONF['pi_version'];
}

/**
* glFusion informs us that we're about to be enabled or disabled
*
* @param    boolean     $enable     true = we're being enabled, false = disabled
* @return   void
*
*/
function plugin_enablestatechange_polls($enable)
{
    global $_TABLES;

    $is_enabled = $enable ? 1 : 0;

    // toggle Poll block
    DB_query ("UPDATE {$_TABLES['blocks']} SET is_enabled = $is_enabled WHERE (type = 'phpblock') AND (phpblockfn = 'phpblock_polls')");
}

/**
* Display the current poll(s) in a side block
*
* @return   string  HTML for the poll(s) to be displayed (or an empty string)
*
*/
function phpblock_polls()
{
    $retval = POLLS_showPoll(60,'',false,1);
    return $retval;
}


/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_polls()
{
    global $_CONF;
    return $_CONF['site_url'] . '/polls/images/polls.png';
}

/**
* Plugin should display [a] comment[s]
*
* @param   string  $id     Unique idenifier for item comment belongs to
* @param   int     $cid    Comment id to display (possibly including sub-comments)
* @param   string  $title  Page/comment title
* @param   string  $order  'ASC' or 'DESC' or blank
* @param   string  $format 'threaded', 'nested', or 'flat'
* @param   int     $page   Page number of comments to display
* @param   boolean $view   True to view comment (by cid), false to display (by $pid)
* @return  mixed   results of calling the plugin_displaycomment_ function
*/
function plugin_displaycomment_polls($id, $cid, $title, $order, $format, $page, $view)
{
    global $_TABLES, $LANG_ACCESS;

    $retval = '';

    $pid = $id;
    $aid = 0;

    $result = DB_query("SELECT is_open FROM {$_TABLES['polltopics']} WHERE pid='".DB_escapeString($pid)."'");
    if ( $result && DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);
        $is_open = $row['is_open'];
        if ($is_open != 1) {
            $aid = -1; // poll closed - show result
        }
        if (!isset ($_COOKIE['poll-'.$pid]) && !POLLS_ipAlreadyVoted ($pid) && $aid != -1 ) {
            $retval .= POLLS_pollVote($pid);
        } else {
            $retval .= POLLS_pollResults($pid, 400, '', '');
        }
    } else {
        return false;
    }
    return $retval;
}

/**
* Set template variables
*
* @param    string  $templatename   name of template, e.g. 'header'
* @param    ref     $template       reference of actual template
* @return   void
*
* Note: A plugin should use its name as a prefix for the names of its
* template variables, e.g. 'polls_xxx' and 'lang_polls_xxx'.
* 'button_polls' is an exception, as such a variable existed for header.thtml
*
*/
function plugin_templatesetvars_polls ($templatename, &$template)
{
    global $LANG_POLLS;

    if ($templatename == 'header') {
        $template->set_var ('button_polls', $LANG_POLLS['polls']);
    }
}

function plugin_getheadercss_polls() {
    global $_CONF;

    $styles = array();

    if ( @file_exists($_CONF['path_layout'].'plugins/polls/style.css') ) {
        $styles[] = $_CONF['path_layout'].'plugins/polls/style.css';
    } else {
        $styles[] = $_CONF['path_html'] . '/polls/style.css';
    }

	return ($styles);
}


/**
* Update the Polls plugin
*
* @return   int     Number of message to display (true = generic success msg)
*
*/
function plugin_upgrade_polls()
{
    global $_TABLES, $_CONF, $_PO_CONF;

    if ( @file_exists($_CONF['path'].'plugins/polls/upgrade.php') ) {
        require_once $_CONF['path'].'plugins/polls/upgrade.php';
        return polls_upgrade();
    } else {
        // upgrades are done by the install script - return a generic error
        Log::write('system',Log::WARNING,"Polls plugin upgrade function not implemented");
        return 3001;
    }
}

/**
* Poll Autotags
* [poll: pid] - Displays a link to a poll using the Poll Topic as the title.
*               A alternate title may be specified.
* [poll_vote: pid pid class:poll-autotag showall:1] - Class and showall not
*             required. Class specifies the css class and Showall if set to 1, shows all questions
* [poll_result: pid class:poll-autotag] - Class not required. Class specifies
*                                         the css class
*
*/
function plugin_autotags_polls($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $LANG_PO_AUTOTAG;

    $skip = 0;

    if ($op == 'tagname' ) {
        return array('poll_vote', 'poll_result', 'poll');
    } elseif ( $op == 'desc' ) {
        switch($content) {
            case 'poll_vote' :
                return $LANG_PO_AUTOTAG['desc_poll_vote'];
                break;
            case 'poll_result' :
                return $LANG_PO_AUTOTAG['desc_poll_result'];
                break;
            case 'poll' :
                return $LANG_PO_AUTOTAG['desc_poll'];
                break;
            default :
                return '';
                break;
        }
    } elseif ($op == 'parse') {
        $tag = str_replace('&nbsp;',' ',$autotag['tagstr']);
        $parms = explode (' ', $tag);
        // Extra test to see if autotag was entered with a space
        // after the module name
        if (substr ($parms[0], -1) == ':') {
            $startpos = strlen ($parms[0]) + strlen ($parms[1]) + 2;
            $label = str_replace (']', '', substr ($tag, $startpos));
            $tagid = $parms[1];
        } else {
            $label = str_replace (']', '',
                     substr ($tag, strlen ($parms[0]) + 1));
            $parms = explode (':', $parms[0]);
            if (count ($parms) > 2) {
                // whoops, there was a ':' in the tag id ...
                array_shift ($parms);
                $tagid = implode (':', $parms);
            } else {
                $tagid = $parms[1];
            }
        }
        $autotag['parm1'] = str_replace(']','',$tagid);
        $autotag['parm2'] = $label;

        $class = 'poll-autotag';
        $showall = false;

        $px = explode (' ', trim ($autotag['parm2']));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 6) == 'class:') {
                    $a = explode (':', $part);
                    $class = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 8) == 'showall:') {
                    $a = explode (':', $part);
                    $val = (int) $a[1];
                    if ( $val == 1 ) {
                        $showall = true;
                    }
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

        $pid = COM_applyFilter($autotag['parm1']);

        switch ($autotag['tag']) {
            case 'poll':
                $filter = new sanitizer();
                $filter->setPostmode('text');

                if (!empty($autotag['parm2'])) {
                    $title = COM_applyFilter($autotag['parm2']);
                } else {
                    $title = DB_getItem($_TABLES['polltopics'], 'topic',
                                       "pid = '$pid'");
                }
                if ( $title == '' ) {
                    $retval = '';
                } else {
                    $retval = COM_createLink($filter->filterData($title), $_CONF['site_url']
                                . '/polls/index.php?pid=' . $pid);
                }
                break;
        case 'poll_vote':
        case 'poll_result':
            if ($autotag['tag'] == 'poll_vote') {
                $retval = POLLS_showPoll(400, $pid, $showall, 2);
            } else {
                $retval = POLLS_pollResults($pid, 0,'', false, 2);
            }

            if ( $retval != '' ) {
                $retval = '<div class="' . $class . '">' . $retval . '</div>';
            }
            break;
        }

        $content = str_replace($autotag['tagstr'], $retval, $content);
    }

    return $content;
}

/**
* Return information for a poll
*
* @param    string  $id         poll ID or '*'
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_polls($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG_POLLS;

    $properties = explode(',', $what);
    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
            case 'date-modified':
            case 'date' :
                $fields[] = 'UNIX_TIMESTAMP(date) AS unixdate';
                break;
            case 'id':
                $fields[] = 'pid';
                break;
            case 'description' :
            case 'searchidx' :
                $fields[] = 'description';
                $fields[] = 'topic';
                break;
            case 'title':
                $fields[] = 'topic';
                break;
            case 'url':
            case 'label':
                $fields[] = 'pid';
                $fields[] = 'hideresults';
                break;
            case 'author' :
            case 'author_name' :
                $fields[] = 'owner_id';
                break;
            case 'hits' :
                $fields[] = 'voters';
                break;
            case 'perms' :
                $fields[] = 'owner_id';
                $fields[] = 'group_id';
                $fields[] = 'perm_owner';
                $fields[] = 'perm_group';
                $fields[] = 'perm_members';
                $fields[] = 'perm_anon';
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
        $permOp = 'WHERE';
    } else {
        $where = " WHERE pid = '" . DB_escapeString($id) . "'";
        $permOp = 'AND';
    }
    if ($uid > 0) {
        $permSql = COM_getPermSql($permOp, $uid);
    } else {
        $permSql = COM_getPermSql($permOp);
    }
    $sql = "SELECT " . implode(',', $fields) . " FROM {$_TABLES['polltopics']}" . $where . $permSql;
    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }

    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    $filter = new sanitizer();
    $filter->setPostmode('text');

    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);

        $props = array();
        foreach ($properties as $p) {
            switch ($p) {
                case 'date-modified':
                case 'date' :
                    $props[$p] = $A['unixdate'];
                    break;
                case 'id':
                    $props['id'] = $A['pid'];
                    break;
                case 'description' :
                    $props['description'] = $filter->filterData($A['description']);
                    break;
                case 'searchidx' :
                    if ( $A['description'] == '' ) {
                        $props['searchidx'] = $A['topic'];
                    } else {
                        $props['searchidx'] = $A['topic'] . ' ' . $A['description'];
                    }
                    break;
                case 'title':
                    $props['title'] = $filter->filterData($A['topic']);
                    break;
                case 'url':
                    if (empty($A['pid'])) {
                        $props['url'] = $_CONF['site_url'].'/polls/index.php?pid='.$pid.'&amp;aid=-1';
                    } else {
                        $pollEdit = SEC_hasRights('polls.edit');
                        // Users who have already voted but cannot see the results cannot view link
                        if ($pollEdit || (!$pollEdit && !($A['hideresults'] == 1 && (isset($_COOKIE['poll-' . $A['pid']]) || POLLS_ipAlreadyVoted($A['pid']))))) {
                            $props['url'] = $_CONF['site_url'] . '/polls/index.php?pid=' . $A['pid'];
                        }
                    }
                    break;
                case 'label':
                    $props['label'] = $LANG_POLLS['polls'];
                    break;
                case 'status':
                    $props['status'] = 1; // stub - default
                    break;
                case 'author':
                    $props['author'] = $A['owner_id'];
                    break;
                case 'author_name' :
                    $props['author_name'] = COM_getDisplayName($A['owner_id']);
                    break;
                case 'hits' :
                    $props['hits'] = $A['voters'];
                    break;
                case 'perms' :
                    $props['perms'] = array(
                        'owner_id' => $A['owner_id'],
                        'group_id' => $A['group_id'],
                        'perm_owner' => $A['perm_owner'],
                        'perm_group' => $A['perm_group'],
                        'perm_members' => $A['perm_members'],
                        'perm_anon' => $A['perm_anon'],
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

function plugin_getContentTableInfo_polls()
{
    global $_DB_table_prefix;

    $contentItems = array(
            $_DB_table_prefix.'pollquestions' => array(
                'plugin' => 'polls',
                'primary_key' => 'pid',
                'columns' => array(
                    'question'
                )
            ),
    );

    return $contentItems;
}


function plugin_getconfigelementhelp_polls($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/polls.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/polls.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

/**
* Helper function: count number of polls and total number of votes
*
* @return   array(number of polls, number of votes);
*
*/
function POLLS_countPollsAndVotes()
{
    global $_TABLES;

    $total_polls = 0;
    $total_answers = 0;

    $result = DB_query ("SELECT COUNT(*) AS count FROM {$_TABLES['polltopics']}" . COM_getPermSQL ());
    $A = DB_fetchArray($result);
    $total_polls = $A['count'];

    $result = DB_query ("SELECT pid FROM {$_TABLES['polltopics']}" . COM_getPermSQL ());
    $nrows = DB_numRows ($result);
    if ($nrows > 0) {
        $topics = '';
        for ($i = 1; $i <= $nrows; $i++) {
            $A = DB_fetchArray($result);
            if ($i > 1) {
                $topics .= ',';
            }
            $topics .= "'" . $A['pid'] . "'";
        }
        $result = DB_query ("SELECT SUM(votes) FROM {$_TABLES['pollanswers']} WHERE pid IN ({$topics})");
        $A = DB_fetchArray($result, true);
        $total_answers = $A[0];
    }

    return array ($total_polls, $total_answers);
}

/**
* Shows a poll form
*
* Shows an HTML formatted poll for the given topic ID
*
* @param      string      $pid      ID for poll topic
* @param      boolean     $showall  Show only the first question in the poll or all?
* @param        int        $displaytype       Possible values 0 = Normal, 1 = In Block, 2 = autotag
* @see function COM_pollResults
* @see function COM_showPoll
* @return       string  HTML Formatted Poll
*
*/

function POLLS_pollVote($pid, $showall = true, $displaytype = 0)
{
    global $_CONF, $_TABLES, $LANG_POLLS, $LANG01, $_USER, $LANG25, $_IMAGE_TYPE;

    USES_lib_comment();

    $filterS = new sanitizer();
    $filterS->setPostmode('text');

    $retval = '';

    $result = DB_query("SELECT topic,description,voters,commentcode,is_open,login_required,hideresults,owner_id "
        . ",group_id,perm_owner,perm_group,perm_anon,perm_members "
        . "FROM {$_TABLES['polltopics']} WHERE pid='".DB_escapeString($pid)."'" . COM_getPermSql('AND'));
    $ntopics = DB_numRows($result);

    if($ntopics == 0 ) {
        return $retval;
    }
    $P = DB_fetchArray($result);
    if ((!isset($_COOKIE[$pid]) && !POLLS_ipAlreadyVoted($pid)) && ($P['is_open'] == 1)) {
        if ($ntopics == 1) {
            $questions_sql = "SELECT question,qid "
                . "FROM {$_TABLES['pollquestions']} "
                . "WHERE pid='".DB_escapeString($pid)."' ORDER BY qid";

            $questions = DB_query($questions_sql);
            $nquestions = DB_numRows($questions );

            if($nquestions > 0 ) {
                $poll = new Template($_CONF['path'] . 'plugins/polls/templates/');
                $poll->set_file(array('panswer' => 'pollanswer.thtml',
                                        'block' => 'pollblock.thtml',
                                        'pquestions' => 'pollquestions.thtml',
                                        'comments' => 'pollcomments.thtml'));
                $poll->set_var('poll_id', $pid );
            //    if ($nquestions > 1) {
                    $poll->set_var('poll_topic', $LANG25['34'] . " " . $filterS->filterData($P['topic']));
                    $poll->set_var('lang_question', $LANG25[31].':');
            //    }
                $poll->set_var('poll_description', $filterS->filterData($P['description']));
                $poll->set_var('num_votes', COM_numberFormat($P['voters']));
                $poll->set_var('poll_vote_url', $_CONF['site_url']
                                                . '/polls/index.php');
                if (($nquestions == 1) || $showall) {
                    $poll->set_var('lang_vote', $LANG_POLLS['vote']);
                    $poll->set_var('showall',true);
                    if ( $displaytype == 2 ) {
                        $poll->set_var('autotag',true);
                    } else {
                        $poll->unset_var('autotag');
                    }
                } else {
                    $poll->set_var('lang_vote', $LANG_POLLS['start_poll']);
                    $poll->unset_var('showall');
                    $poll->unset_var('autotag');
                }

                $poll->set_var('lang_votes', $LANG_POLLS['votes']);

                $results = '';
                if (($P['is_open'] == 0) || ($P['hideresults'] == 0) ||
                    (($P['hideresults'] == 1) &&
                        (
                        SEC_inGroup('Root') ||
                        (isset($_USER['uid'])
                            && ($_USER['uid'] == $P['owner_id']))
                        ))) {
                    $results = COM_createLink($LANG_POLLS['results'],
                        $_CONF['site_url'] . '/polls/index.php?pid=' . $pid
                        . '&amp;aid=-1');
                }
                $poll->set_var('poll_results', $results);

                $access = SEC_hasAccess($P['owner_id'], $P['group_id'],
                                $P['perm_owner'], $P['perm_group'],
                                $P['perm_members'], $P['perm_anon']);
                if (($access == 3) && SEC_hasRights('polls.edit')) {
                    $editlink = COM_createLink($LANG25[27], $_CONF['site_admin_url']
                        . '/plugins/polls/index.php?edit=x&amp;pid=' . $pid);
                    $poll->set_var('edit_link', $editlink);
                    $editicon = $_CONF['layout_url'] . '/images/edit.' . $_IMAGE_TYPE;
                    $icon_attr = array('title' => $LANG25[27]);
                    $editiconhtml = COM_createImage($editicon, $LANG25[27], $icon_attr);
                    $editlink = COM_createLink($editiconhtml, $_CONF['site_admin_url']
                        . '/plugins/polls/index.php?edit=x&amp;pid=' . $pid);
                    $poll->set_var('edit_icon', $editlink);
                    $poll->set_var('edit_url',  $_CONF['site_admin_url'].'/plugins/polls/index.php?edit=x&amp;pid=' . $pid);
                }

                if (array_key_exists('aid', $_POST)) {
                    $aid = $_POST['aid'];
                } else {
                    $aid = array();
                }

                for($j=0; $j<$nquestions; $j++) {
                    $Q = DB_fetchArray($questions);
                    $poll->set_var('poll_question', " ".$filterS->filterData($Q['question']));
                    $poll->set_var('question_id', $j);
                    $notification = "";
                    $filter = "";
                    if ($showall == false) {
                        $nquestions--;
                        $notification = $LANG25[35] . " $nquestions " . $LANG25[36];
                        $filter = "AND qid='0'";
                        $nquestions = 1;
                    } else {
                        $filter = "AND qid = '{$Q['qid']}'";
                        $poll->set_var('lang_question_number', " ". ($j+1).":");
                    }
                    $answer_sql = "SELECT answer,aid "
                        . "FROM {$_TABLES['pollanswers']} "
                        . "WHERE pid='".DB_escapeString($pid)."'$filter ORDER BY qid, aid";
                    $answers = DB_query($answer_sql);
                    $nanswers = DB_numRows($answers );
                    for($i=0; $i<$nanswers; $i++) {
                        $A = DB_fetchArray($answers );
                        if (($j < count($aid)) && ($aid[$j] == $A['aid'])) {
                            $poll->set_var('selected', ' checked="checked"');
                        }

                        $poll->set_var('answer_id', $A['aid']);
                        $poll->set_var('answer_text', $filterS->filterData($A['answer']));
                        $poll->parse('poll_answers', 'panswer', true);
                        $poll->clear_var('selected');
                    }
                    $poll->parse('poll_questions', 'pquestions', true);
                    $poll->clear_var('poll_answers');
                }
                $poll->set_var('lang_polltopics', $LANG_POLLS['polltopics']);
                $poll->set_var('poll_notification', $notification);
                if($P['commentcode'] >= 0 ) {
                    $num_comments = CMT_getCount('polls',$pid);  //DB_count($_TABLES['comments'], array('sid', 'type'), array(DB_escapeString($pid), 'polls'));
                    $poll->set_var('num_comments',COM_numberFormat($num_comments));
                    $poll->set_var('lang_comments', $LANG01[3]);

                    $comment_link = CMT_getCommentLinkWithCount(
                            'polls',
                            $pid,
                            $_CONF['site_url'].'/polls/index.php?pid=' . $pid,
                            $num_comments,
                            0
                        );

                    $poll->set_var('poll_comments_url', $comment_link['link_with_count']);
                    $poll->parse('poll_comments', 'comments', true);
                } else {
                    $poll->set_var('poll_comments', '');
                    $poll->set_var('poll_comments_url', '');
                }

                if ( isset($P['login_required']) && $P['login_required']  && COM_isAnonUser() ) {
                    $poll->set_var('poll_login_required', true);
                    $poll->set_var('lang_login_required',$LANG_POLLS['login_required']);
                } else {
                    $poll->unset_var('poll_login_required');
                }

                $retval =  $poll->finish($poll->parse('output', 'block')) . LB;

                if ($showall && ($P['commentcode'] >= 0 AND $displaytype != 2)) {
                    $delete_option = (SEC_hasRights('polls.edit') &&
                        SEC_hasAccess($P['owner_id'], $P['group_id'],
                        $P['perm_owner'], $P['perm_group'], $P['perm_members'],
                        $P['perm_anon']) == 3 ? true : false);

                    USES_lib_comment();

                    $page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
                    if ( isset($_POST['order']) ) {
                        $order  =  $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';
                    } elseif (isset($_GET['order']) ) {
                        $order =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
                    } else {
                        $order = '';
                    }
                    if ( isset($_POST['mode']) ) {
                        $mode = COM_applyFilter($_POST['mode']);
                    } elseif ( isset($_GET['mode']) ) {
                        $mode = COM_applyFilter($_GET['mode']);
                    } else {
                        $mode = '';
                    }
                    $valid_cmt_modes = array('flat','nested','nocomment','threaded','nobar');
                    if ( !in_array($mode,$valid_cmt_modes) ) {
                        $mode = '';
                    }
                    $retval .= CMT_userComments($pid, $filterS->filterData($P['topic']), 'polls',
                                                $order, $mode, 0, $page, false,
                                                $delete_option, $P['commentcode'],$P['owner_id']);
                }
            }
        }
    } else {
        $retval .= POLLS_pollResults($pid, 0, '', '', $displaytype);
    }

    return $retval;
}

/**
* This shows a poll
*
* This will determine if a user needs to see the poll form OR the poll result.
*
* @param        int     $size           Size in pixels of poll results
* @param        string  $pid            Poll topic ID to show (optional)
* @param        boolean $showall        Show only the first question or all
* @param        int     $displaytype    Possible values 0 = Normal, 1 = In Block, 2 = autotag
* @return       string  HTML formatted string of poll
* @see function COM_pollVote
* @see function COM_pollResults
*
*/
function POLLS_showPoll($size, $pid = '', $showall = false, $displaytype = 0)
{
    global $_CONF, $_PO_CONF, $_TABLES, $LANG_POLLS;

    $retval = '';
    $is_open = 0;
    // housekeeping - delete old anonymous voting data
    if ( !isset($_PO_CONF['polladdresstime']) ) {
        $polladdresstime = 86400; // 24 hours
    } else {
        $polladdresstime = $_PO_CONF['polladdresstime'];
    }
    if ( $polladdresstime != 0 ) {
        $checkTime = time() - $polladdresstime;
        $sql = "DELETE FROM {$_TABLES['pollvoters']} WHERE uid = 1 && date < " . $checkTime . "";
        DB_query($sql,1);
    }
    if(!empty($pid)) {
        $sql = "SELECT * FROM {$_TABLES['polltopics']} WHERE pid='".DB_escapeString($pid)."'";
        $result = DB_query($sql);
        if ( DB_numRows($result) > 0 ) {
            $row = DB_fetchArray($result);
            $is_open = $row['is_open'];

            if (($displaytype == 2) && ($is_open == 0)) {
                $retval = '<div class="poll-autotag-message">'
                        . $LANG_POLLS['pollclosed'] . '</div>';
            }
            if(!isset($_COOKIE["poll-".$pid]) && !POLLS_ipAlreadyVoted($pid) && ($is_open == 1)) {
                // poll is open and this user has not voted yet
                $retval .= POLLS_pollVote($pid,$showall,$displaytype);
            } else {
                // poll is open, and this user has already voted
                $retval .= POLLS_pollResults($pid, $size,'','',$displaytype);
            }
        } else {
            if ( $displaytype == 2 ) {
                $retval = '';
            } else {
                if(!isset($_COOKIE["poll-".$pid]) && !POLLS_ipAlreadyVoted($pid) && ($is_open == 1)) {
                    // poll is open and this user has not voted yet
                    $retval .= POLLS_pollVote($pid,$showall,$displaytype);
                } else {
                    // poll is open and this user has already voted
                    $retval .= POLLS_pollResults($pid, $size,'','',$displaytype);
                }
            }
        }
    } else {
        $result = DB_query("SELECT pid,topic,is_open from {$_TABLES['polltopics']} WHERE display = 1 ORDER BY date DESC");
        $nrows = DB_numRows($result );

        $title = DB_getItem($_TABLES['blocks'], 'title', "name='poll_block'");

        if ($nrows > 0) {
            for ($i = 1; $i <= $nrows; $i++) {
                $Q = DB_fetchArray($result);
                $pid = $Q['pid'];
                if ($displaytype == 1) { // in the poll block
                    $showall = false;
                } else { // assume we're in polls/index.php
                    $retval .= COM_startBlock($title);
                    $showall = true;
                }

                if (!isset($_COOKIE['poll-' . $pid]) &&
                        !POLLS_ipAlreadyVoted($pid) && ($Q['is_open'] == 1)) {
                    $retval .= POLLS_pollVote($pid, $showall, $displaytype);
                } else {
                    $retval .= POLLS_pollResults($pid, $size, '', '',
                                                 $displaytype);
                }

                if ($displaytype == 1) { // in the poll block
                    if (($i < $nrows) && !empty($retval)) {
                        $retval .= '<div class="poll-divider"></div>';
                    }
                } else {
                    $retval .= COM_endBlock();
                }
            }
        }
    }
    return $retval;
}

/**
* Saves a user's vote
*
* Saves the users vote, if allowed for the poll $pid.
* NOTE: all data comes from form post
*
* @param    string   $pid   poll id
* @param    array    $aid   selected answers
* @return   string   HTML for poll results
*
*/
function POLLS_saveVote($pid, $aid)
{
    global $_PO_CONF, $_USER, $_TABLES, $LANG_POLLS;

    $retval = '';

    if (POLLS_ipAlreadyVoted ($pid)) {
        exit;
    }
    DB_change($_TABLES['polltopics'],'voters',"voters + 1",'pid',DB_escapeString($pid),'',true);
    // This call to DB-change will properly supress the insertion of quotes around $value in the sql
    $answers = count($aid);
    for ($i = 0; $i < $answers; $i++) {
        DB_change(
            $_TABLES['pollanswers'],
            'votes',
            "votes + 1",
            array('pid', 'qid', 'aid'),
            array(DB_escapeString($pid),  $i, COM_applyFilter($aid[$i], true)),
            '',
            true
        );
    }

    if ( COM_isAnonUser() ) {
        $userid = 1;
    } else {
        $userid = $_USER['uid'];
    }
    // This always does an insert so no need to provide key_field and key_value args
    $sql = "INSERT INTO {$_TABLES['pollvoters']} (ipaddress,uid,date,pid) VALUES ('".DB_escapeString($_SERVER['REAL_ADDR'])."',".(int)$userid.",".time().",'".DB_escapeString($pid)."')";
    $result = DB_query($sql);

    $eMsg = $LANG_POLLS['savedvotemsg'] . ' "'
        . DB_getItem ($_TABLES['polltopics'], 'topic', "pid = '".DB_escapeString($pid)."'").'"';

    $retval .= COM_showMessageText($eMsg,$LANG_POLLS['savedvotetitle'],false)
            . POLLS_pollResults($pid);

    return $retval;
}

/**
* Shows the results of a poll
*
* Shows the poll results for a given poll topic
*
* @param        string      $pid        ID for poll topic to show
* @param        int         $scale      Size in pixels to scale formatted results to
* @param        string      $order      'ASC' or 'DESC' for Comment ordering (SQL statment ordering)
* @param        string      $mode       Comment Mode possible values 'nocomment', 'flat', 'nested', 'threaded'
* @param        int        $displaytype       Possible values 0 = Normal, 1 = In Block, 2 = autotag
* @see POLLS_pollVote
* @see POLLS_showPoll
* @return     string   HTML Formated Poll Results
*
*/
function POLLS_pollResults($pid, $scale=400, $order='', $mode='', $displaytype = 0)
{
    global $_CONF, $_TABLES, $_USER, $_IMAGE_TYPE,
           $_PO_CONF, $LANG01, $LANG_POLLS, $_COM_VERBOSE, $LANG25;

    USES_lib_comments();

    $filter = new sanitizer();
    $filter->setPostmode('text');

    $retval = '';
    $topic_sql = "SELECT topic,description,voters,is_open,hideresults,commentcode,owner_id,group_id,"
        . "perm_owner,perm_group,perm_members,perm_anon "
        . "FROM {$_TABLES['polltopics']} WHERE pid='".DB_escapeString($pid)."'";
    $result = DB_query($topic_sql);
    $P = DB_fetchArray($result);

    if(SEC_hasAccess($P['owner_id'], $P['group_id'], $P['perm_owner'],
        $P['perm_group'], $P['perm_members'], $P['perm_anon']) == 0 ) {
        return $retval;
    }
    if ((
            $P['hideresults'] == 0
        ) || (
            ($P['hideresults'] == 1) && (isset($_USER['uid']) &&
                                         ($_USER['uid'] == $P['owner_id']))
        ) || (
            ($P['hideresults'] == 1) && (SEC_inGroup('Root'))
        )
    ) {
        $retval = '';
    } else {
        if ($displaytype == 2) {
            $retval = '<div class="poll-autotag-message">' . $LANG_POLLS['pollhidden']. "</div>";
        } else if ($displaytype == 1 ) {
            $retval = '';
        } else {
            $retval = COM_showMessageText($LANG_POLLS['pollhidden'],'', true,'error');
            $retval .= POLLS_pollList();
        }

        return $retval;
    }

    $ntopic = DB_numRows($result);
    if ($ntopic == 1) {
        $questions_sql = "SELECT qid,pid,question "
            . "FROM {$_TABLES['pollquestions']} "
            . "WHERE pid='".DB_escapeString($pid)."' "
            . "ORDER BY qid";
        $questions = DB_query ($questions_sql);
        $nquestions = DB_numRows($questions);

        if ($nquestions > 0){
            $poll = new Template($_CONF['path'] . 'plugins/polls/templates/' );
            $poll->set_file(array('result' => 'pollresult.thtml',
                'question' => 'pollquestion.thtml',
                'comments' => 'pollcomments.thtml',
                'votes_bar' => 'pollvotes_bar.thtml',
                'votes_num' => 'pollvotes_num.thtml' ));
            $poll->set_var('layout_url', $_CONF['layout_url']);
            $poll->set_var('poll_topic', $filter->filterData($P['topic']));
            $poll->set_var('poll_description',$filter->filterData($P['description']));

            $poll->set_var('poll_id', $pid);
            $poll->set_var('num_votes', COM_numberFormat($P['voters']));
            $poll->set_var('lang_votes', $LANG_POLLS['votes']);
            $access = SEC_hasAccess($P['owner_id'],$P['group_id'],$P['perm_owner'],
                $P['perm_group'],$P['perm_members'],$P['perm_anon']);
            if ($access == 3) {
                $editlink = COM_createLink($LANG25[27], $_CONF['site_admin_url']
                    . '/plugins/polls/index.php?edit=x&amp;pid=' . $pid );
                $poll->set_var('edit_link', $editlink);
                $poll->set_var('edit_url',$_CONF['site_admin_url'].'/plugins/polls/index.php?edit=x&amp;pid=' . $pid);
                $editicon = $_CONF['layout_url'] . '/images/edit.' . $_IMAGE_TYPE;
                $icon_attr = array('title' => $LANG25[27]);
                $editiconhtml = COM_createImage($editicon, $LANG25[27], $icon_attr);
                $editlink = COM_createLink($editiconhtml, $_CONF['site_admin_url']
                    . '/plugins/polls/index.php?edit=x&amp;pid=' . $pid );
                $poll->set_var('edit_icon', $editlink);
            }
            if ($_PO_CONF['answerorder'] == 'voteorder'){
                $order = "votes DESC";
            } else {
                $order = "aid";
            }

            for ($j = 0; $j < $nquestions; $j++) {
                $Q = DB_fetchArray($questions);

                if ($nquestions >= 1) {
                    $counter = ($j + 1) . "/$nquestions: " ;
                }
                $poll->set_var('poll_question', $counter . $filter->filterData($Q['question']));

                $answer_sql = "SELECT votes,answer,remark "
                    . "FROM {$_TABLES['pollanswers']} "
                    . "WHERE pid='".DB_escapeString($pid)."' and qid='".DB_escapeString($Q['qid'])."' "
                    . "ORDER BY $order";

                $answers = DB_query ($answer_sql);
                $nanswers = DB_numRows($answers);

                $a_summ_sql = "SELECT SUM(votes) as votesumm FROM {$_TABLES['pollanswers']} "
                    . "WHERE pid='".DB_escapeString($pid)."' and qid='".DB_escapeString($Q['qid'])."' "
                    . "GROUP BY qid";

                $a_summ = DB_query ($a_summ_sql);
                $S = DB_fetchArray($a_summ);

                for ($i=1; $i<=$nanswers; $i++) {
                    $A = DB_fetchArray($answers);
                    if ($S['votesumm'] == 0) {
                        $percent = 0;
                    } else {
                        $percent = $A['votes'] / $S['votesumm'];
                    }
                    $poll->set_var('cssida', 1);
                    $poll->set_var('cssidb', 2);
                    $poll->set_var('answer_text', $filter->filterData($A['answer']));
                    $poll->set_var('remark_text', $filter->filterData($A['remark']));
                    $poll->set_var('answer_counter', $i);
                    $poll->set_var('answer_odd', (($i - 1) % 2));
                    $poll->set_var('answer_num', COM_numberFormat($A['votes']));
                    $poll->set_var('answer_percent',
                                    sprintf('%.2f', $percent * 100));
                    if ($scale < 120) {
                        $poll->parse('poll_votes', 'votes_num', true );
                    } else {
                        $width = (int) ($percent * 100 );
                        $poll->set_var('bar_width', $width);
                        $poll->parse('poll_votes', 'votes_bar', true);
                    }
                }
                $poll->parse('poll_questions', 'question', true);
                $poll->clear_var('poll_votes');
                if (($scale < 100) && ($j < 1)) {
                    $url = $_CONF['site_url'] . "/polls/index.php?pid=$pid";
                    $poll->set_var('notification', COM_createLink($LANG25[40], $url). "<br>");
                    break;
                }
            }
            if($P['commentcode'] >= 0 ) {
                $num_comments = CMT_getCount('polls',$pid);  //DB_count($_TABLES['comments'], array('sid', 'type'), array(DB_escapeString($pid), 'polls'));
                $poll->set_var('num_comments',COM_numberFormat($num_comments));
                $poll->set_var('lang_comments', $LANG01[3]);
                $comment_link = CMT_getCommentLinkWithCount(
                        'polls',
                        $pid,
                        $_CONF['site_url'].'/polls/index.php?pid=' . $pid,
                        $num_comments,
                        0
                    );
                $poll->set_var('poll_comments_url', $comment_link['link_with_count']);
                $poll->parse('poll_comments', 'comments', true);
            } else {
                $poll->set_var('poll_comments_url', '');
                $poll->set_var('poll_comments', '');
            }

            $poll->set_var('lang_polltopics', $LANG_POLLS['polltopics'] );

            $retval .= $poll->finish($poll->parse('output', 'result' ));

            if($scale > 399 && $P['commentcode'] >= 0 && $displaytype != 2) {
                $delete_option = (SEC_hasRights('polls.edit') &&
                    SEC_hasAccess($P['owner_id'], $P['group_id'],
                    $P['perm_owner'], $P['perm_group'], $P['perm_members'],
                    $P['perm_anon']) == 3 ? true : false);

                USES_lib_comment();

                $page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
                if ( isset($_POST['order']) ) {
                    $order  =  $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';
                } elseif (isset($_GET['order']) ) {
                    $order =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
                } else {
                    $order = 'DESC';
                }
                if ( isset($_POST['mode']) ) {
                    $mode = COM_applyFilter($_POST['mode']);
                } elseif ( isset($_GET['mode']) ) {
                    $mode = COM_applyFilter($_GET['mode']);
                } else {
                    $mode = '';
                }
                $retval .= CMT_userComments($pid, $filter->filterData($P['topic']), 'polls',
                                            $order, $mode, 0, $page, false,
                                            $delete_option, $P['commentcode'],$P['owner_id']);
            }
        }
    }

    return $retval;
}

function POLLS_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG25, $LANG_ACCESS, $LANG_ADMIN, $_USER;

    $retval = '';

    $access = SEC_hasAccess($A['owner_id'], $A['group_id'],
                             $A['perm_owner'], $A['perm_group'],
                             $A['perm_members'], $A['perm_anon']);
    if ($access > 0) {

        $dt = new Date('now',$_USER['tzid']);

        switch($fieldname) {

            case 'edit':
                if ($access == 3) {
                    $retval = FieldList::edit(
                        array(
                            'url' => $_CONF['site_admin_url'].'/plugins/polls/index.php?edit=x&amp;pid='.$A['pid'],
                        )
                    );
                }
                break;

            case 'unixdate':
                $dt->setTimestamp($A['unixdate']);
                $retval = $dt->format($_CONF['daytime'],true);
                break;

            case 'topic':
            case 'topic' :
                $filter = new sanitizer();
                $filter->setPostmode('text');
                $fieldvalue = $filter->filterData($fieldvalue);

                $retval = COM_createLink($fieldvalue,
                        "{$_CONF['site_url']}/polls/index.php?pid={$A['pid']}");
                break;

            case 'access':
                if ($access == 3) {
                    $access = $LANG_ACCESS['edit'];
                } else {
                    $access = $LANG_ACCESS['readonly'];
                }
                $retval = $access;
                break;

            case 'is_open':
                if ($A['is_open'] == 1) {
                    $retval = FieldList::checkmark(array('active'=>true));
                }
                break;

            case 'display':
                if ($A['display'] == 1) {
                    $retval = $LANG25[25];
                } else {
                    $retval = $LANG25[26];
                }
                break;

            case 'voters':
                // add a link there to the list of voters
                $url = $_CONF['site_admin_url'].'/plugins/polls/index.php?lv=x&amp;pid='.urlencode($A['pid']);
                $retval = '<a href="'.$url.'">'.COM_numberFormat($A['voters']).'</a>';
                break;

            case 'delete':
                if ($access == 3) {
                    $retval = FieldList::delete(
                        array(
                            'delete_url' =>  $_CONF['site_admin_url'] . '/plugins/polls/index.php'.'?delete=x&amp;pid=' . $A['pid'] . '&amp;' . CSRF_TOKEN . '=' . $token,
                            'attr' => array(
                                'title' => $LANG_ADMIN['delete'],
                                'onclick' => "return doubleconfirm('" . $LANG25[41] . "','" . $LANG25[42] . "');"
                            )
                        )
                    );
                }
                break;


            default:
                $retval = $fieldvalue;
                break;
        }
    } else {
        $retval = false;
    }

    return $retval;
}

/**
* Check if we already have a vote from this IP address
*
* @param    string  $pid    Poll ID
* @param    string  $ip     (optional) IP address
* @return   boolean         true: IP already voted; false: didn't
*
*/
function POLLS_ipAlreadyVoted($pid, $ip = '' )
{
    global $_USER, $_TABLES;

    $retval = false;

    if(empty($ip )) {
        $ip = $_SERVER['REAL_ADDR'];
    }

    if ( !COM_isAnonUser() ) {
        if (DB_count($_TABLES['pollvoters'],
                 array('uid', 'pid'),
                 array( (int) $_USER['uid'], DB_escapeString($pid))) > 0) {
            $retval = true;
        } else {
            $retval = false;
        }
    } elseif ($ip != '' && DB_count($_TABLES['pollvoters'],
                 array('ipaddress', 'pid'),
                 array(DB_escapeString($ip), DB_escapeString($pid))) > 0) {
        $retval = true;
    }

    return $retval;
}

function POLLS_siteHeader($title='', $meta='')
{
    global $_PO_CONF;

    $retval = '';

    switch( $_PO_CONF['displayblocks'] ) {
        case 0 : // left only
        case 2 :
            $retval .= COM_siteHeader('menu',$title,$meta);
            break;
        case 1 : // right only
        case 3 :
            $retval .= COM_siteHeader('none',$title,$meta);
            break;
        default :
            $retval .= COM_siteHeader('menu',$title,$meta);
            break;
    }
    return $retval;
}

function POLLS_siteFooter() {
    global $_CONF, $_PO_CONF;

    $retval = '';

    switch( $_PO_CONF['displayblocks'] ) {
        case 0 : // left only
        case 3 : // none
            $retval .= COM_siteFooter();
            break;
        case 1 : // right only
        case 2 : // left and right
            $retval .= COM_siteFooter( true );
            break;
        default :
            $retval .= COM_siteFooter();
            break;
    }
    return $retval;
}
?>
