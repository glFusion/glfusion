<?php
// +--------------------------------------------------------------------------+
// | Links Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2012-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Mark Limburg       - mlimburg AT users.sourceforge DOT net      |
// |          Jason Whittenburg  - jwhitten AT securitygeeks DOT com          |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Trinity Bays       - trinity93 AT gmail DOT com                 |
// |          Oliver Spiesshofer - oliver AT spiesshofer DOT com              |
// |          Euan McKay         - info AT heatherengineering DOT com         |
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

$plugin_path = $_CONF['path'] . 'plugins/links/';

/*
* load the language file(s), including custom strings if any
*/
$langfile = $_CONF['path'] . 'plugins/links/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'] . 'plugins/links/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/links/language/english.php';
    $custfile = $_CONF['path'].'plugins/links/language/custom/english.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

/*
* Check and see if we need to load the plugin configuration
*/
if (!isset($_LI_CONF['linksloginrequired'])) {
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $li_config = config::get_instance();
    $_LI_CONF = $li_config->get_config('links');
}

require_once $_CONF['path'].'plugins/links/links.php';

// +---------------------------------------------------------------------------+
// | glFusion Plugin API Implementation                                        |
// +---------------------------------------------------------------------------+

/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
* @return   mixed   menu entry, or boolean false if disabled / hidden
*
*/
function plugin_getmenuitems_links()
{
    global $_CONF, $_USER, $_LI_CONF, $LANG_LINKS;

    $anon = (COM_isAnonUser()) ? true : false;
    if (($_LI_CONF['hidelinksmenu'] == 1) || ($anon &&
                ($_CONF['loginrequired'] || $_LI_CONF['linksloginrequired']))) {
        return false;
    }

    $menuitems[$LANG_LINKS[114]] = $_CONF['site_url'] . '/links/index.php';

    return $menuitems;
}


/**
* Return new links for the What's New block
*
* @return   string  HTML list of new links
*
*/
function plugin_getwhatsnew_links()
{
    global $_CONF, $_TABLES, $_USER, $_LI_CONF, $LANG_LINKS, $LANG_WHATSNEW;

    $retval = '';

    // Get newest links
    $sql = "SELECT lid,title FROM {$_TABLES['links']} WHERE (date >= (DATE_SUB(NOW(), INTERVAL {$_LI_CONF['newlinksinterval']} SECOND)))" . COM_getPermSQL( 'AND' ) . ' ORDER BY date DESC LIMIT 15';
    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    if ($nrows > 0) {
        $wntitle = $LANG_LINKS[84];
        $wnlabel = COM_formatTimeString( $LANG_WHATSNEW['new_last'],$_LI_CONF['newlinksinterval'] );

        $newlinks = array();
        for ($x = 0; $x < $nrows; $x++) {
            $A = DB_fetchArray($result);
            $A['title'] = $A['title'];

            // redirect link via portal.php so we can count the clicks
            $lcount = COM_buildUrl($_CONF['site_url']
                    . '/links/portal.php?what=link&amp;item=' . $A['lid']);

            $title = COM_truncate($A['title'], $_CONF['title_trim_length'],
                                  '...');
            $attr = array();
            if ( $_LI_CONF['target_blank'] == 1 ) {
                $attr = array(
                    'title' => @htmlspecialchars($A['title'],ENT_COMPAT,COM_getEncodingt()),
                    'target' => '_blank',
                    'rel' => 'nofollow');
            } else {
                $attr = array(
                    'title' => @htmlspecialchars($A['title'],ENT_COMPAT,COM_getEncodingt()),
                    'rel' => 'nofollow'
                    );
            }

            $newlinks[] = COM_createLink($title, $lcount, $attr);
        }
        return array($wntitle,$wnlabel,$newlinks);
    }

    return '';
}

/**
* Implements the [link:] autotag.
*
* @param    string  $op         operation to perform
* @param    string  $content    item (e.g. story text), including the autotag
* @param    array   $autotag    parameters used in the autotag
* @param    mixed               tag names (for $op='tagname') or formatted content
*
*/
function plugin_autotags_links($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $LANG_LI_AUTOTAG;

    if ($op == 'tagname' ) {
        return 'link';
    } else if ($op == 'tagusage') {
        $tagUsage = array(
            array('namespace' => 'links','usage' => 'description')
        );
        return $tagUsage;
    } elseif ( $op == 'desc' ) {
        $tagDescription = $LANG_LI_AUTOTAG['desc_link'];
        return $tagDescription;
    } else if ($op == 'parse') {
        $lid = COM_applyFilter($autotag['parm1']);
        $url = COM_buildUrl($_CONF['site_url']
                            . '/links/portal.php?what=link&amp;item=' . $lid);

        $sql = "SELECT l.url, l.title, lc.owner_id,lc.group_id,lc.perm_owner,lc.perm_group,lc.perm_members,lc.perm_anon,
                l.owner_id as lowner_id,l.group_id as lgroup_id,l.perm_owner as lperm_owner,l.perm_group as lperm_group,l.perm_members as lperm_members,l.perm_anon as lperm_anon
                FROM {$_TABLES['links']} AS l LEFT JOIN {$_TABLES['linkcategories']} AS lc ON l.cid=lc.cid WHERE l.lid = '".DB_escapeString($lid)."'";

        $result = DB_query($sql);
        $L = DB_fetchArray($result);

        $link = '';

        if (SEC_hasAccess ($L['owner_id'], $L['group_id'], $L['perm_owner'], $L['perm_group'], $L['perm_members'], $L['perm_anon']) >= 2) {
            if (SEC_hasAccess ($L['lowner_id'], $L['lgroup_id'], $L['lperm_owner'], $L['lperm_group'], $L['lperm_members'], $L['lperm_anon']) >= 2) {
                if (empty($autotag['parm2'])) {
                    $siteurl = $L['url'];
                    $linktext = $L['title'];
                } else {
                    $linktext = $autotag['parm2'];
                    $siteurl = $L['url'];
                }
                $attr = array(
                            'title' => $siteurl,
                            'class' => 'ext-link'
                             );
                if ( isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == true && stristr($siteurl,$_CONF['site_url']) === false ) {
                    $attr['target'] = '_blank';
                    $attr['rel'] = 'noopener noreferrer';
                }

                $link = COM_createLink($linktext, $url, $attr);
            }
        }

        $content = str_replace($autotag['tagstr'], $link, $content);

        return $content;
    }
}

/**
* Called by the plugin Editor to display the current plugin code version
* This may be different than the version installed and registered currently.
* If newer then you may want to run the update
*
* @return   string  version number
*
*/
function plugin_chkVersion_links ()
{
    global $_LI_CONF;

    return $_LI_CONF['pi_version'];
}

/**
* Helper function: count number of links and total number of clicks
*
* @return   array(number of links, number of clicks);
*
*/
function LINKS_countLinksAndClicks ()
{
    global $_TABLES;

    $result = DB_query("SELECT COUNT(*) AS count,SUM(hits) AS clicks FROM {$_TABLES['links']}" . COM_getPermSQL() . LINKS_getCategorySQL('AND'));
    $A = DB_fetchArray($result);
    $total_links = $A['count'];
    $total_clicks = $A['clicks'];
    if (empty ($total_clicks)) {
        $total_clicks = 0;
    }

    return array ($total_links, $total_clicks);
}

/**
* Shows the statistics for the links plugin on stats.php.
* If $showsitestats is 1 then we are to only print the overall stats in the
* 'site statistics box' otherwise we show the detailed stats
*
* @param    int     $showsitestate  Flag to let us know which stats to get
* @param    string                  HTML for the stats section
*
*/
function plugin_showstats_links ($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_LINKS_STATS;

    require_once ($_CONF['path_system'] . 'lib-admin.php');

    $retval = '';

    $result = DB_query("SELECT lid,url,title,hits FROM {$_TABLES['links']} WHERE (hits > 0)" . COM_getPermSQL('AND') . LINKS_getCategorySQL('AND') . " ORDER BY hits DESC LIMIT 10");
    $nrows  = DB_numRows ($result);
    if ($nrows > 0) {
        $header_arr = array(
            array('text'         => $LANG_LINKS_STATS['stats_page_title'],
                  'field'        => 'sid',
                  'header_class' => 'stats-header-title'
            ),
            array('text'         => $LANG_LINKS_STATS['stats_hits'],
                  'field'        => 'hits',
                  'header_class' => 'stats-header-count',
                  'field_class'  => 'stats-list-count'
            )
        );
        $data_arr = array();
        $text_arr = array('has_menu' => false,
                          'title'    => $LANG_LINKS_STATS['stats_headline'],
        );
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray ($result);
            $title = str_replace ('$', '&#36;', $A['title']);
            $url = COM_buildUrl ($_CONF['site_url']
                        . '/links/portal.php?what=link&amp;item=' . $A['lid']);
            $sid = COM_createLink($title, $url, array('title' => $A['url']));
            $hits = COM_numberFormat ($A['hits']);
            $data_arr[] = array('title' => $title,
                                'sid' => $sid,
                                'hits' => $hits
                          );
        }
        $retval .= ADMIN_simpleList ('', $header_arr, $text_arr, $data_arr);
    } else {
        $retval .= COM_startBlock ($LANG_LINKS_STATS['stats_headline']);
        $retval .= $LANG_LINKS_STATS['stats_no_hits'];
        $retval .= COM_endBlock ();
    }

    return $retval;
}

/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
*
*/
function plugin_statssummary_links ()
{
    global $LANG_LINKS_STATS;

    list($total_links, $total_clicks) = LINKS_countLinksAndClicks ();

    $item_count = COM_NumberFormat ($total_links)
                . ' (' . COM_NumberFormat ($total_clicks) . ')';


    return array ($LANG_LINKS_STATS['links'], $item_count);
}

/**
* glFusion is asking us to provide any items that show up in the type
* drop-down on search.php.  Let's users search for links.
*
* @return   array   (plugin name/entry title) pair for the dropdown
*
*/
function plugin_searchtypes_links()
{
    global $LANG_LINKS;

    $tmp['links'] = $LANG_LINKS[14];

    return $tmp;
}


/**
* This searches for links matching the user query and returns an array for the
* header and table rows back to search.php where it will be formated and printed
*
* @param    string  $query      Keywords user is looking for
* @param    date    $datestart  Start date to get results for
* @param    date    $dateend    End date to get results for
* @param    string  $topic      The topic they were searching in
* @param    string  $type       Type of items they are searching, or 'all' (deprecated)
* @param    int     $author     Get all results by this author
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
* @return   object              search result object
*
*/
function plugin_dopluginsearch_links($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_TABLES, $LANG_LINKS;

    // Make sure the query is SQL safe
    $query = trim(DB_escapeString(@htmlspecialchars($query)));

    $sql = "SELECT lid AS id, title, description, UNIX_TIMESTAMP(date) AS date, owner_id AS uid, hits, CONCAT('/links/portal.php?what=link&amp;item=',lid) AS url ";
    $sql .= "FROM {$_TABLES['links']} WHERE date <> 1 ";
    $sql .= COM_getPermSQL('AND') . ' ';

    if (!empty ($author))
        $sql .= "AND (owner_id = '$author') ";

    $search = new SearchCriteria('links', $LANG_LINKS[14]);
    $columns = array('description','title');
    $sql .= $search->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(date)', $datestart, $dateend);
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(3);

    return $search;
}


/**
* This will put an option for links in the command and control block on
* moderation.php
*
* @return   mixed   array(title, url, icon), or boolean false when not allowed
*
*/
function plugin_cclabel_links()
{
    global $_CONF, $LANG_LINKS;

    if (SEC_hasRights ('links.edit')) {
        return array ($LANG_LINKS[14],
                      $_CONF['site_admin_url'] . '/plugins/links/index.php',
                      plugin_geticon_links ());
    }

    return false;
}

/**
* returns the administrative option for this plugin
*
* @return   mixed   array(title, url, num. links), or void when not allowed
*
*/
function plugin_getadminoption_links()
{
    global $_CONF, $_TABLES, $LANG_LINKS;

    if (SEC_hasRights ('links.edit,links.delete', 'OR')) {
        $total_links = DB_getItem ($_TABLES['links'], 'COUNT(*)',
                                   COM_getPermSql (''));

        return array ($LANG_LINKS[14],
                      $_CONF['site_admin_url'] . '/plugins/links/index.php',
                      $total_links);
    }
}

/**
* A user is about to be deleted. Update ownership of any links owned
* by that user or delete them.
*
* @param    int     $uid    User id of deleted user
* @return   void
*
*/
function plugin_user_delete_links ($uid)
{
    global $_TABLES, $_LI_CONF;

    if (DB_count ($_TABLES['links'], 'owner_id', $uid) == 0) {
        return;
    }

    if ($_LI_CONF['delete_links'] == 1) {
        // delete the links
        DB_delete($_TABLES['links'], 'owner_id', $uid);
    } else {
        // assign ownership to a user from the Root group
        $rootgroup = DB_getItem ($_TABLES['groups'], 'grp_id',
                                 "grp_name = 'Root'");
        $result = DB_query ("SELECT DISTINCT ug_uid FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $rootgroup ORDER BY ug_uid LIMIT 1");
        if ( DB_numRows($result) > 0 ) {
            $A = DB_fetchArray ($result);
            $rootuser = $A['ug_uid'];
        } else {
            $rootuser = 2;
        }
        DB_query ("UPDATE {$_TABLES['links']} SET owner_id = $rootuser WHERE owner_id = $uid");
    }
}

/**
* Do we support feeds?
*
* @return   array   id/name pairs of all supported feeds
*
*/
function plugin_getfeednames_links()
{
    global $_TABLES;

    $feeds = array ();

    $result = DB_query ("SELECT cid,category FROM {$_TABLES['linkcategories']} GROUP BY category ORDER BY category ASC");
    $num = DB_numRows ($result);

    if ($num > 0) {
        $feeds[] = array ('id' => 'all', 'name' => 'all categories');

        for ($i = 0; $i < $num; $i++) {
            $A = DB_fetchArray ($result);
            $feeds[] = array ('id' => $A['cid'], 'name' => $A['category']);
        }
    }

    return $feeds;
}

/**
* Provide feed data
*
* @param    int     $feed       feed ID
* @param    ref     $link
* @param    ref     $update
* @return   array               feed entries
*
*/
function plugin_getfeedcontent_links ($feed, &$link, &$update)
{
    global $_CONF, $_TABLES;

    $result = DB_query( "SELECT topic,limits,content_length FROM {$_TABLES['syndication']} WHERE fid = '$feed'" );
    $S = DB_fetchArray( $result );

    $result = DB_query( "SELECT lid,owner_id,title,description,UNIX_TIMESTAMP(date) AS modified FROM " . $_TABLES['links'] . links_buildSql ($S['topic'], $S['limits']) );

    $content = array();
    $lids = array();
    $nrows = DB_numRows( $result );

    for( $i = 0; $i < $nrows; $i++ )
    {
        $row = DB_fetchArray( $result );
        $lids[] = $row['lid'];

        $linktitle = $row['title'];
        $linkdesc = $row['description'];

        $linklink = COM_buildUrl( $_CONF['site_url']
                  . '/links/portal.php?what=link&amp;item=' . $row['lid'] );

        $content[] = array( 'title'   => $linktitle,
                            'summary' => $linkdesc,
                            'link'    => $linklink,
                            'uid'     => $row['owner_id'],
                            'author'  => COM_getDisplayName( $row['owner_id'] ),
                            'date'    => $row['modified'],
                            'format'  => 'plaintext'
                          );
    }

    $link = $_CONF['site_url'] . '/links/index.php';
    $update = implode( ',', $lids );

    return $content;
}

/**
* Helper function: Build part of an SQL request
*
* @param    string  $cid        category id
* @param    string  $limits     limit (number of entries or number of hours)
* @return   string              part of an SQL request
*
*/
function links_buildSql($cid, $limits)
{
    $where = '';
    if ($cid != 'all') {
        $where = "cid='" . DB_escapeString($cid) . "'";
    }

    $limitsql = '';
    if (!empty ($limits)) {
        if (substr ($limits, -1) == 'h') { // last xx hours
            $limitsql = '';
            $hours = substr ($limits, 0, -1);
            if (!empty ($where)) {
                $where .= ' AND ';
            }
            $where .= "date >= DATE_SUB(NOW(),INTERVAL $hours HOUR)";
        } else {
            $limitsql = ' LIMIT ' . $limits;
        }
    }
    else
    {
        $limitsql = ' LIMIT 10';
    }

    if (!empty ($where)) {
        $where = ' WHERE ' . $where;
    }

    $sql = $where . ' ORDER BY date DESC' . $limitsql;

    return $sql;
}

/**
* Checking if links feeds are up to date
*
* @param    int     $feed           id of feed to be checked
* @param    string  $topic          topic (actually: cid)
* @param    string  $update_data    data describing current feed contents
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_type   (optional) type of feed to be updated
* @param    string  $updated_topic  (optional) feed's "topic" to be updated
* @param    string  $updated_id     (optional) id of entry that has changed
* @return   boolean                 true: feed data is up to date; false: isn't
*
*/
function plugin_feedupdatecheck_links ($feed, $topic, $update_data, $limit, $updated_type = '', $updated_topic = '', $updated_id = '')
{
    global $_TABLES;

    $is_current = true;

    if ($updated_type != 'links') {
        // we're not interested
        $updated_type = '';
        $updated_topic = '';
        $updated_id = '';
    }

    $sql = "SELECT lid FROM {$_TABLES['links']}" . links_buildSql ($topic, $limit);
    $result = DB_query ($sql);
    $num = DB_numRows ($result);

    $lids = array ();
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);

        if ($A['lid'] == $updated_id) {
            // this feed has to be updated - no further checks needed
            return false;
        }

        $lids[] = $A['lid'];
    }
    $current = implode (',', $lids);

    return ($current != $update_data) ? false : true;
}

/**
* Update the Links plugin
*
* @return   int     Number of message to display (true = generic success msg)
*
*/
function plugin_upgrade_links()
{
    global $_TABLES, $_CONF, $_LI_CONF;

    if ( @file_exists($_CONF['path'].'plugins/links/upgrade.php') ) {
        require_once $_CONF['path'].'plugins/links/upgrade.php';
        return links_upgrade();
    } else {
        // upgrades are done by the install script - return a generic error
        COM_errorLog("Links plugin upgrade function not implemented");
        return 3001;
    }
}

/**
* glFusion informs us that we're about to be enabled or disabled
*
* @param    boolean     $enable     true = we're being enabled, false = disabled
* @return   void
*
*/
function plugin_enablestatechange_links ($enable)
{
    global $_TABLES;

    $is_enabled = $enable ? 1 : 0;

    // toggle links feeds
    DB_query ("UPDATE {$_TABLES['syndication']} SET is_enabled = $is_enabled WHERE type = 'links'");
}

/**
* Counts the items that are submitted
*
* @return   int     number of items in submission queue
*
*/
function plugin_submissioncount_links()
{
    global $_TABLES;

    $retval = 0;

    if (plugin_ismoderator_links ()) {
        $retval = DB_count ($_TABLES['linksubmission']);
    }

    return $retval;
}

/**
* Checks that the current user has the rights to moderate the
* plugin, returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_links()
{
    return SEC_hasRights ('links.moderate');
}


/**
* Returns SQL & Language texts to moderation.php
*
* @return   mixed   plugin object or void if not allowed
*
*/
function plugin_itemlist_links()
{
    global $_TABLES, $LANG_LINKS_SUBMIT;

    if (plugin_ismoderator_links()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG_LINKS_SUBMIT[11];
        $plugin->submissionhelpfile = 'cclinksubmission.html';
        $plugin->getsubmissionssql = "SELECT lid AS id,title,cid AS category,url,owner_id AS uid FROM {$_TABLES['linksubmission']} ORDER BY title ASC";
        $plugin->addSubmissionHeading($LANG_LINKS_SUBMIT[8]);
        $plugin->addSubmissionHeading($LANG_LINKS_SUBMIT[10]);
        $plugin->addSubmissionHeading($LANG_LINKS_SUBMIT[9]);
        $plugin->addSubmissionHeading($LANG_LINKS_SUBMIT[12]);

        return $plugin;
    }
}

/**
* returns list of moderation values
*
* The array returned contains (in order): the row 'id' label, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_links()
{
    global $_TABLES;

    return array ('lid',
                  $_TABLES['links'],
                  'lid,cid,url,description,title,date,owner_id',
                  $_TABLES['linksubmission']);
}


/**
* Performs plugin exclusive work for items approved by moderation
*
* While moderation.php handles the actual move from linkssubmission
* to links tables, within the function we handle all other approval
* relate tasks
*
* @param    string  $id     Identifying string
* @return   string          Any wanted HTML output
*
*/
function plugin_moderationapprove_links($id)
{
    global $_TABLES, $_USER, $_GROUPS, $_LI_CONF;

    // The linksubmission only keeps track of the submitter's uid, but not
    // of grous and permissions. So set those to sensible defaults.

    if (isset($_GROUPS['Links Admin'])) {
        $group_id = $_GROUPS['Links Admin'];
    } else {
        $group_id = SEC_getFeatureGroup('links.moderate');
    }

    $A = array();
    SEC_setDefaultPermissions($A, $_LI_CONF['default_permissions']);

    DB_query("UPDATE {$_TABLES['links']} SET group_id = '$group_id', perm_owner = '{$A['perm_owner']}', perm_group = '{$A['perm_group']}', perm_members = '{$A['perm_members']}', perm_anon = '{$A['perm_anon']}' WHERE lid = '$id'");

    return '';
}

/**
* Performs plugin exclusive work for items deleted by moderation
*
* While moderation.php handles the actual removal from <plugin>submission
* table, within this function we handle all other deletion
* related tasks
*
* @param    string  $id     Identifying string
* @return   string          Any wanted HTML output
*
*/
function plugin_moderationdelete_links($id)
{
    global $_TABLES;

    // these tables should not contain any rows with ml_id = $id
    // this is done 'just in case'
    DB_delete ($_TABLES['linksubmission'], 'lid', $id);

    return '';
}

/**
* Check submission form values and save if OK. Else show form again
*
* @param    array   $A  The link record
* @return   string      Any wanted HTML output
*
*/
function plugin_savesubmission_links($A)
{
    global $LANG12;

    $retval = '';

    if (!empty ($A['title']) && !empty ($A['description']) &&
                !empty ($A['url'])) {
        $retval = plugin_save_submit_links ($A);
    } else {
        $retval .= COM_showMessageText($LANG12[23],$LANG12[22],true,'error')
                . plugin_submit_links($A);
    }

    return $retval;
}

/**
* Shows link submission form
*
* @return   string  HTML for the link submission form
*
*/
function plugin_submit_links($A = array())
{
    global $_CONF, $LANG_LINKS_SUBMIT, $LANG12;

    $retval = COM_startBlock ($LANG_LINKS_SUBMIT[1], 'submitlink.html');

    $linkform = new Template($_CONF['path'] . 'plugins/links/templates');
    $linkform->set_file('linkform', 'submitlink.thtml');
    $linkform->set_var('lang_title', $LANG12[10]);
    $linkform->set_var('lang_link', $LANG_LINKS_SUBMIT[2]);
    $linkform->set_var('lang_category', $LANG_LINKS_SUBMIT[3]);
    $category = '';
    if (isset($_REQUEST['cid'])) {
        $category = $_REQUEST['cid'];
    }
    $linkform->set_var('link_category_options', links_select_box(2, $category));
    $linkform->set_var('lang_description', $LANG12[15]);
    $linkform->set_var('lang_htmlnotallowed', $LANG12[35]);
    $linkform->set_var('lang_submit', $LANG12[8]);
    $linkform->set_var('max_url_length', 255);

    if ( isset($A['title']) ) {
        $linkform->set_var('title',@htmlspecialchars(strip_tags($A['title']),ENT_QUOTES,COM_getEncodingt()) );
    }
    if ( isset($A['description'] ) ) {
        $linkform->set_var('description',@htmlspecialchars($A['description']));
    }
    if ( isset($A['url']) ) {
        $linkform->set_var('url',@htmlspecialchars($A['url'],ENT_QUOTES,COM_getEncodingt()));
    } else {
        $linkform->set_var('url','http://');
    }

    PLG_templateSetVars ('links', $linkform);
    $linkform->parse('theform', 'linkform');
    $retval .= $linkform->finish($linkform->get_var('theform'));
    $retval .= COM_endBlock();

    return $retval;
}

/**
* Saves a link submission
*
* @param    array   $A  Data for that submission
* @return   string      HTML redirect
*
*/
function plugin_save_submit_links($A)
{
    global $_CONF, $_TABLES, $_USER, $_LI_CONF, $LANG12, $LANG_LINKS;

    $retval = '';
    /* check CAPTCHA */
    $msg = PLG_itemPreSave ('links', $A);
    if (!empty ($msg)) {
        $retval .= COM_showMessageText($msg,$LANG12[22],true,'error')
                . plugin_submit_links($A);

        return $retval;
    }

    // pseudo-formatted link description for the spam check
    $spamcheck = '<p>'. COM_createLink($A['title'], $A['url']) .' ('
               . $A['categorydd'] . ')<br/>' . $A['description']
               . '</p>';
    $result = PLG_checkforSpam($spamcheck, $_CONF['spamx']);
    if ($result > 0) {
        COM_updateSpeedlimit('submit');
        COM_displayMessageAndAbort($result, 'spamx', 403, 'Forbidden');
    }

    $A['cid'] = strip_tags($A['categorydd']);

    $validcat = false;
    if (!empty($A['cid'])) {
        $cid = DB_escapeString($A['cid']);
        $cat = DB_getItem($_TABLES['linkcategories'], 'category',
                          "cid = '$cid'");
        if (!empty($cat)) {
            $validcat = true;
        }
    }

    $urlregex2 = "/^(https?|ftp)\:\/\//i";
    if ( !preg_match($urlregex2,$A['url']) ) {
//    if (!preg_match("/^(http(s?):\\/\\/|ftp:\\/\\/{1})((\w+\.)+)\w{2,}(\/?)$/i", $A['url'])) {
        $retval .= COM_showMessageText($LANG_LINKS['verification_failed'],$LANG_LINKS['error_header'],true,'error')
                . plugin_submit_links($A);

        return $retval;
    }


    if (!$validcat) {
        $retval .= COM_showMessageText($LANG_LINKS['category_not_found'],$LANG_LINKS['error_header'],true,'error')
                . plugin_submit_links($A);

        return $retval;
    }

    $A['cid'] = DB_escapeString($A['cid']);
    $A['description'] = DB_escapeString(@htmlspecialchars(COM_checkWords($A['description'])));
    $A['title'] = DB_escapeString(strip_tags(COM_checkWords($A['title'])));
    $A['url'] = DB_escapeString(COM_sanitizeUrl($A['url']));
    $A['lid'] = DB_escapeString(COM_makeSid());

    COM_updateSpeedlimit('submit');
    if (COM_isAnonUser()) {
        $owner_id = 1; // anonymous user
    } else {
        $owner_id = (int) $_USER['uid'];
    }

    if (($_LI_CONF['linksubmission'] == 1) && !SEC_hasRights('links.submit')) {
        $result = DB_save($_TABLES['linksubmission'],
                    'lid,cid,url,description,title,date,owner_id',
                    "{$A['lid']},'{$A['cid']}','{$A['url']}','{$A['description']}','{$A['title']}',NOW(),$owner_id");

        PLG_itemSaved($A['lid'], 'links');

        if ($_LI_CONF['notification'] == 1) {
            LINKS_sendNotification($_TABLES['linksubmission'], $A);
        }

        $retval = COM_refresh($_CONF['site_url']
                              . '/links/index.php?msg=1&amp;plugin=links');

    } else { // add link directly

        if (SEC_hasRights('links.submit')) {
            $A['group_id'] = SEC_getFeatureGroup('links.submit');
        } else {
            $A['group_id'] = DB_getItem($_TABLES['groups'], 'grp_id',
                                        "grp_name = 'All Users'");
        }
        SEC_setDefaultPermissions($A, $_LI_CONF['default_permissions']);

        $result = DB_save($_TABLES['links'],
                    'lid,cid,url,description,title,date,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon',
                    "{$A['lid']},'{$A['cid']}','{$A['url']}','{$A['description']}','{$A['title']}',NOW(),$owner_id,{$A['group_id']},{$A['perm_owner']},{$A['perm_group']},{$A['perm_members']},{$A['perm_anon']}");

        if ($_LI_CONF['notification'] == 1) {
            LINKS_sendNotification($_TABLES['links'], $A);
        }
        COM_rdfUpToDateCheck('links', $A['cid'], $A['lid']);
        CACHE_remove_instance('whatsnew');
        $retval = COM_refresh($_CONF['site_url']
                              . '/links/index.php?msg=4&amp;plugin=links');
    }

    return $retval;
}

/**
* Send an email notification for a new submission.
*
* @param    string  $table  Table where the new submission can be found
* @param    array   $A      submission data
*
*/
function LINKS_sendNotification ($table, $A)
{
    global $_CONF, $_TABLES, $LANG_LINKS, $LANG_LINKS_SUBMIT, $LANG08;

    $title = $A['title'];
    $description = $A['description'];

    $mailbody = "$LANG_LINKS_SUBMIT[8]: $title\n"
              . "$LANG_LINKS_SUBMIT[9]: <{$A['url']}>\n"
              . "$LANG_LINKS_SUBMIT[3]: {$A['category']}\n\n"
              . $description . "\n\n";
    if ($table == $_TABLES['linksubmission']) {
        $mailbody .= "$LANG_LINKS[10] <{$_CONF['site_admin_url']}/moderation.php>\n\n";
    } else {
        $mailbody .= "$LANG_LINKS[114] <{$_CONF['site_url']}/links/index.php?category=" . urlencode ($A['category']) . ">\n\n";
    }
    $mailsubject = $_CONF['site_name'] . ' ' . $LANG_LINKS_SUBMIT[11];

    $mailbody .= "\n------------------------------\n";
    $mailbody .= "\n$LANG08[34]\n";
    $mailbody .= "\n------------------------------\n";

    $to = array();
    $to = COM_formatEmailAddress( '',$_CONF['site_mail'] );
    COM_mail ($to, $mailsubject, $mailbody);
}

/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_links ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/links/images/links.png';
}

function plugin_getListField_links($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $LANG_ACCESS, $LANG_LINKS_ADMIN, $LANG_ADMIN;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
    if ($access > 0) {
        switch($fieldname) {

            case 'edit':
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['edit'];
                    $retval = COM_createLink(
                        $icon_arr['edit'],
                        $_CONF['site_admin_url'] . '/plugins/links/index.php?edit=x&amp;lid=' . $A['lid'],
                        $attr);
                }
                break;

            case "owner":
                $retval = COM_getDisplayName ($A['owner_id']);
                break;

            case 'access':
                if ($access == 3) {
                   $retval = $LANG_ACCESS['edit'];
                } else {
                   $retval = $LANG_ACCESS['readonly'];
                }
                break;

            case "unixdate":
                $dt->setTimestamp($A['unixdate']);
                $retval = $dt->format($_CONF['daytime'],true);
                break;

            case 'title':
                $retval = COM_createLink($A['title'], $A['url']);
                break;

            case 'dovalidate';
                $retval = links_validateUrl($A['url']);
                break;

            case 'beforevalidate';
                $retval = $LANG_LINKS_ADMIN[57];
                break;

            case 'category':
                if (isset($A['indent'])) {
                    $indent = ($A['indent'] - 1) * 20;
                } else {
                    $indent = 0;
                }
                $cat = COM_createLink($A['category'],
                        "{$_CONF['site_url']}/links/index.php?category="
                        . urlencode($A['cid']));
                $retval = "<span style=\"padding-left:{$indent}px;\">$cat</span>";
                break;

            case 'delete':
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['delete'];
                    $attr['onclick'] = "return confirm('" . $LANG_LINKS_ADMIN[63] . "');";
                    $retval = COM_createLink($icon_arr['delete'],
                        $_CONF['site_admin_url'] . '/plugins/links/index.php'
                        . '?delete=x&amp;lid=' . $A['lid'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
                }
                break;

            default:
                $retval = $fieldvalue;
                break;
        }
    }

    return $retval;
}

function plugin_getListField_categories($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ACCESS, $LANG_ADMIN, $LANG_LINKS_ADMIN;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);

    if ($access > 0) {

        switch($fieldname) {

            case 'edit':
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['edit'];
                    $retval = COM_createLink(
                        $icon_arr['edit'],
                        $_CONF['site_admin_url'] . '/plugins/links/category.php?edit=x&amp;cid=' . urlencode($A['cid']),
                        $attr);
                }
                break;

            case 'addchild':
                if ($access == 3) {
                    $attr['title'] = $LANG_LINKS_ADMIN[44];
                    $retval .= COM_createLink(
                        $icon_arr['addchild'],
                        $_CONF['site_admin_url'] . '/plugins/links/category.php?edit=x&amp;pid=' . urlencode($A['cid']),
                        $attr);
                }
                break;

            case "owner":
                $retval = COM_getDisplayName ($A['owner_id']);
                break;

            case 'access':
                if ($access == 3) {
                   $retval = $LANG_ACCESS['edit'];
                } else {
                   $retval = $LANG_ACCESS['readonly'];
                }
                break;

            case "unixdate":
                $dt->setTimestamp($A['unixdate']);
                $retval = $dt->format($_CONF['daytime'],true);
                break;

            case 'category':
                $indent = ($A['indent'] - 1) * 20;
                $cat = COM_createLink($A['category'],
                        "{$_CONF['site_url']}/links/index.php?category="
                        . urlencode($A['cid']));
                $retval = "<span style=\"padding-left:{$indent}px;\">$cat</span>";
                break;

            case 'addchild';
                if ($access == 3) {
                    $retval = COM_createLink(
                        $icon_arr['addchild'],
                        "{$_CONF['site_admin_url']}/plugins/links/category.php?edit=x&amp;pid=" . urlencode($A['cid'])
                    );
                }
                break;

            case 'tid';
                if ($A['tid'] == 'all') {
                    $retval = $LANG_LINKS_ADMIN[35];
                } else {
                    $retval = DB_getItem($_TABLES['topics'], 'topic',
                                         "tid = '{$A['tid']}'");
                }
                if (empty($retval)) {
                    $retval = $A['tid'];
                }
                break;

            case 'delete':
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['delete'];
                    $attr['onclick'] = "return confirm('" . $LANG_LINKS_ADMIN[64] . "');";
                    $retval = COM_createLink($icon_arr['delete'],
                        $_CONF['site_admin_url'] . '/plugins/links/category.php'
                        . '?delete=x&amp;cid=' . $A['cid'] . '&amp;' . CSRF_TOKEN . '=' . SEC_createToken(), $attr);
                }
                break;

            default:
                $retval = $fieldvalue;
                break;
        }
    }

    return $retval;
}


function links_validateUrl($url)
{
    global $_CONF, $LANG_LINKS_STATUS;

    require_once $_CONF['path'].'lib/http/http.php';

    $retval = '';

	set_time_limit(0);
	$req=new http_class;
	$req->timeout=0;
	$req->data_timeout=0;
	$req->debug=0;
	$req->html_debug=0;
	$req->accept = "*/*";
    $req->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
    $error=$req->GetRequestArguments($url,$arguments);
    $arguments["Headers"]["Pragma"]="nocache";
    $error=$req->Open($arguments);
    if ( $error != "" ) {
        $retval = $error;
    } else {
        $headers=array();
        $error=$req->SendRequest($arguments);
        if ( $error != "" ) {
            $retval = $error;
        } else {
            $error=$req->ReadReplyHeaders($headers);
            if ( $error != "") {
                $retval = $error;
            } else {
                $status_code = $req->response_status;
                $retval = $status_code . ": " . $LANG_LINKS_STATUS[$status_code];
            }
        }
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
* template variables, e.g. 'links_xxx' and 'lang_links_xxx'.
* 'button_links' is an exception
*
*/
function plugin_templatesetvars_links ($templatename, &$template)
{
    global $LANG_LINKS;

    if ($templatename == 'header') {
        $template->set_var ('button_links', $LANG_LINKS[14]);
    }
}


/**
* Return information for a link
*
* @param    string  $lid        link ID or '*'
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_links($lid, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG_LINKS;

    $properties = explode(',', $what);
    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
            case 'date' :
            case 'date-modified':
                $fields[] = 'UNIX_TIMESTAMP(date) AS unixdate';
                break;
            case 'description':
            case 'excerpt':
            case 'raw-description' :
            case 'searchidx' :
                $fields[] = 'description';
                break;
            case 'id':
                $fields[] = 'lid';
                break;
            case 'title':
                $fields[] = 'title';
                break;
            case 'label':
            case 'url':
                $fields[] = 'lid';
                break;
            case 'perms' :
                $fields[] = 'owner_id';
                $fields[] = 'group_id';
                $fields[] = 'perm_owner';
                $fields[] = 'perm_group';
                $fields[] = 'perm_members';
                $fields[] = 'perm_anon';
                break;
            case 'hits' :
                $fields[] = 'hits';
                break;
            default:
                // nothing to do
                break;
        }
    }

    $fields = array_unique($fields);

    if (count($fields) == 0) {
        $retval = array();

        return $retval;
    }

    if ($lid == '*') {
        $where = '';
        $permOp = 'WHERE';
    } else {
        $where = " WHERE lid = '" . DB_escapeString($lid) . "'";
        $permOp = 'AND';
    }
    if ($uid > 0) {
        $permSql = COM_getPermSql($permOp, $uid) . LINKS_getCategorySQL('AND', $uid);
    } else {
        $permSql = COM_getPermSql($permOp) . LINKS_getCategorySQL('AND');
    }
    $sql = "SELECT " . implode(',', $fields) . " FROM {$_TABLES['links']}" . $where . $permSql;
    if ($lid != '*') {
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
                    $props['date'] = $A['unixdate'];
                    break;
                case 'date-modified':
                    $props['date-modified'] = $A['unixdate'];
                    break;
                case 'description':
                case 'excerpt':
                case 'raw-description' :
                case 'searchidx' :
                    $props[$p] = $A['description'];
                    break;
                case 'id':
                    $props['id'] = $A['lid'];
                    break;
                case 'title':
                    $props['title'] = $A['title'];
                    break;
                case 'url':
                    if (empty($A['lid'])) {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/links/portal.php?what=link&amp;item='.$lid);
                    } else {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/links/portal.php?what=link&amp;item='.$A['lid']);
                    }
                    break;
                case 'label':
                    $props['label'] = $LANG_LINKS[14];
                    break;
                case 'status':
                    $props['status'] = 1; // stub - default status
                    break;
                case 'hits' :
                    $props['hits'] = $A['hits'];
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
            if ($lid == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
            }
        }

        if ($lid == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($lid != '*') && (count($retval) == 1)) {
        $retval = $retval[0];
    }

    return $retval;
}


// +--------------------------------------------------------------------------+
// | Category HTML functions                                                  |
// | Functions for building select boxes and breadcrumb trails etc.           |
// +--------------------------------------------------------------------------+

/*
* Build selection list
*/
function links_select_box ($access, $sel = '')
{
    global $_CONF, $LANG_LINKS, $_LI_CONF;

    // set root value
    $menu = '<option value="' . $_LI_CONF['root'] . '">' . $LANG_LINKS['root'] . '</option>';
    // get option values
    $list = links_select_box_recursive($menu, $_LI_CONF['root'], $sel, '&nbsp;&nbsp;', $access);
    // return list of options
    return $list;
}


/*
* Build recursive tree
*/
function links_select_box_recursive (&$menu, $cid, $sel, $indent, $access)
{
    global $_CONF, $_TABLES;

    $cat = DB_escapeString($cid);
    $sql = "SELECT cid,category
            FROM {$_TABLES['linkcategories']}
            WHERE (pid='{$cat}') " . COM_getPermSQL('AND', 0, $access) . "
            ORDER BY category";
    $query = DB_query($sql);
    while (list($cid, $category) = DB_fetchArray($query)) {
        // set selected item
        if ($cid == $sel) {
            // yes, selected
            $menu .= '<option value="' . @htmlspecialchars($cid)
                  . '" selected="selected">' . $indent . $category
                  . '</option>';
        } else {
            // no, not selected
            $menu .= '<option value="' . @htmlspecialchars($cid) . '">'
                  . $indent . $category . '</option>';
        }
        // Check and see if this category has any sub categories
        if (DB_count($_TABLES['linkcategories'], 'pid', DB_escapeString($cid)) > 0) {
            // yes, call self
            $dum = links_select_box_recursive ($menu, $cid, $sel,
                        $indent . '&nbsp;&nbsp;', $access);
        }
   }

   return $menu;
}


/*
* Build breadcrumb trail
*
* Breadcrumb trail does not use the "root" category in the database: the top
* level category is set from the language file using $LANG_LINKS['root']
*/
function links_breadcrumbs($root, $cid)
{
    global $_CONF, $_TABLES, $LANG_LINKS;

    $breadcrumb = '';
    $separator  = ' &gt; ';

    $cat = DB_escapeString($cid);
    $c = $cid;
    $pid = '';
    if ($root != $cid) {
        while ($pid != $root) {
            $parent = DB_query("SELECT cid,pid,category FROM {$_TABLES['linkcategories']} WHERE cid='{$cat}'");
            if ( DB_numRows($parent) == 0 ) {
                break;
            }
            $A = DB_fetchArray($parent);
            if ($cid != $c) {
                $content = $A['category'];
                $url = $_CONF['site_url'] . '/links/index.php?category='
                                          . urlencode($A['cid']);
                $breadcrumb = COM_createLink($content, $url) . $separator
                            . $breadcrumb;
            } else {
                $breadcrumb = '<b>' . $A['category'] . '</b>' . $breadcrumb;
            }
            $pid = $A['pid'];
            $c = $A['pid'];
            $cat = DB_escapeString($c);
        }
    }

    $url = $_CONF['site_url'] . '/links/index.php';
    if (empty($breadcrumb)) {
        $breadcrumb = '<b>' . $LANG_LINKS['root'] . '</b>';
    } else {
        $breadcrumb = COM_createLink($LANG_LINKS['root'], $url) . $separator . $breadcrumb;
    }

    $breadcrumb = '<span class="links-breadcrumb">' . $LANG_LINKS[126] . ' '
                . $breadcrumb . '</span>';

    return $breadcrumb;
}

/*
* Configuration validation
*
* Update DB if root category name is changed.
*/
function plugin_configmanager_root_links_validate($cid)
{
    global $_LI_CONF, $_TABLES;

    // need to pull old value

    $old_cid = $_LI_CONF['root'];

    if ( $old_cid != $cid ) {
        $sql = "UPDATE {$_TABLES['linkcategories']} SET pid='".DB_escapeString($cid)."' WHERE pid = '".DB_escapeString($old_cid)."'";

        DB_query($sql);

        $sql = "UPDATE {$_TABLES['linkcategories']}
                SET cid='".DB_escapeString($cid)."'
                WHERE cid = '".DB_escapeString($old_cid)."'";

        DB_query($sql);
    }
    return $cid;
}

function plugin_getconfigelementhelp_links($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/links.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/links.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

// +--------------------------------------------------------------------------+
// | PHP Block functions                                                      |
// +--------------------------------------------------------------------------+

/**
 * Returns a list of links that belong to categories associated with
 * the current topic
 *
 * Relies on the fact that $topic is set (and sanitized) in lib-common.php
 */
function phpblock_topic_links()
{
    global $_CONF, $_TABLES, $LANG_LINKS, $topic;

    $retval = '';

    if (!empty($topic)) {
        $tid = DB_escapeString($topic);
        $result = DB_query("SELECT l.lid, l.title, l.url, c.cid FROM {$_TABLES['links']} AS l LEFT JOIN {$_TABLES['linkcategories']} AS c ON l.cid=c.cid WHERE c.tid='{$tid}' OR c.tid='all'" . COM_getPermSQL('AND', 0, 2, 'c'));
        $nrows = DB_numRows($result);
        if ($nrows > 0) {
            for ($i = 0; $i < $nrows; $i++) {
                $A = DB_fetchArray($result);
                $content = $A['title'];
                $url = COM_buildUrl($_CONF['site_url']
                     . '/links/portal.php?what=link&amp;item=' . $A['lid']);
                $retval .= COM_createLink($content, $url,
                                          array('title' => $A['url']))
                        . '<br/>';
            }
        }
    }

    return $retval;
}


/**
 * Returns a list of categories that are associated with the current topic
 *
 * Relies on the fact that $topic is set (and sanitized) in lib-common.php
 */
function phpblock_topic_categories()
{
    global $_CONF, $_TABLES, $LANG_LINKS, $topic;

    $retval = '';

    if (!empty($topic)) {
        $tid = DB_escapeString($topic);
        $result = DB_query("SELECT category, cid FROM {$_TABLES['linkcategories']} WHERE tid='{$tid}' OR tid='all'" . COM_getPermSQL ('AND'));
        $nrows = DB_numRows($result);
        if ($nrows > 0) {
            for ($i = 0; $i < $nrows; $i++) {
                $A = DB_fetchArray($result);
                $content = $A['category'];
                $url = $_CONF['site_url'] . '/links/index.php?category='
                                          . urlencode($A['cid']);
                $retval .= COM_createLink($content, $url) . '<br/>';
            }
        }
    }

    return $retval;
}

/**
 * Returns HTML-formatted list of links from a given category
 *
 */
function phpblock_blogroll()
{
    global $_CONF, $_TABLES;

    // configuration options:

    $cat = 'blog-roll'; // Category to take links from
    $directlink = false;    // Use direct links (true) or portal.php (false)
    $random = false;        // Random order (true) or sort by $sort (false)
    $sort = 'date';         // Sort by ... e.g. 'date', 'title', 'url'

    // === you shouldn't need to change anything below this line ==============
    $retval = '';

    if ( function_exists('LINKS_countLinksAndClicks') ) {

        $result = DB_query ("SELECT lid,url,title,description,hits FROM {$_TABLES['links']} WHERE cid = '".DB_escapeString($cat)."'" . COM_getPermSql ('AND') . " ORDER BY $sort");
        $numLinks = DB_numRows ($result);

        $links = array ();
        for ($i = 0; $i < $numLinks; $i++) {
            $A = DB_fetchArray ($result);

            if ($directlink) {
                $url = $A['url'];
                $link = '<a href="' . $url . '">';

            } else {
                $url = $_CONF['site_url']
                     . COM_buildUrl ('/links/portal.php?what=link&amp;item=' . $A['lid']);
                $link = '<a href="' . $url . '" title="' . $A['url'] . '">';

            }
            $links[] = $link . COM_truncate($A['title'],25,'...') . '</a>'; // . ' (' . ($A['hits']) . ')' . '<br/><em>' . ($A['description']) . '</em><br/><br/>';
        }

        if (count ($links) > 0) {

            if ($random) {
                $min = 0;
                $max = count ($links) - 1;

                $newlist = array ();
                do {
                    $r = rand ($min, $max);

                    if (!empty ($links[$r])) {
                        $newlist[] = $links[$r];
                        unset ($links[$r]);
                    }

                    if ($r == $min) {
                        $min = $r + 1;
                    } else if ($r == $max) {
                        $max = $r - 1;
                    }
                    if ($min == $max) {
                        if (!empty ($links[$min])) {
                            $newlist[] = $links[$min];
                        }
                        break;
                    }
                }
                while ($max > $min);

                $retval = COM_makeList ($newlist, 'list-blogroll');
            } else {
                $retval = COM_makeList ($links, 'list-blogroll');
            }
        }
    }

    return $retval;
}

/**
* Return SQL expression to check for allowed categories.
*
* Creates part of an SQL expression that can be used to only request links
* from categories to which the user has access to.
*
* Note that this function does SQL requests, so you should cache
* the resulting SQL expression if you need it more than once.
*
* @param    string  $type   part of the SQL expr. e.g. 'WHERE', 'AND'
* @param    int     $u_id   user id or 0 = current user
* @param    string  $table  table name if ambiguous (e.g. in JOINs)
* @return   string          SQL expression string (may be empty)
* @see      COM_getTopicSQL
*
*/
function LINKS_getCategorySQL($type = 'WHERE', $u_id = 0, $table = '')
{
    global $_TABLES, $_USER, $_GROUPS;

    $categorysql = ' ' . $type . ' ';

    if (!empty($table)) {
        $table .= '.';
    }

    $UserGroups = array();
    if (($u_id <= 0) || (isset($_USER['uid']) && ($u_id == $_USER['uid']))) {
        if (!COM_isAnonUser()) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }
        $UserGroups = $_GROUPS;
    } else {
        $uid = $u_id;
        $UserGroups = SEC_getUserGroups($uid);
    }

    if (empty($UserGroups)) {
        // this shouldn't really happen, but if it does, handle user
        // like an anonymous user
        $uid = 1;
    }

    if (SEC_inGroup('Root', $uid)) {
        return '';
    }

    $parents = array('root');
    $cids = array();

    do {
        $result = DB_query("SELECT cid FROM {$_TABLES['linkcategories']}"
                           . COM_getPermSQL('WHERE', $uid) . " AND pid IN ('"
                           . implode("','", $parents) . "')");

        $parents = array();
        while ($C = DB_fetchArray($result)) {
            $parents[] = $C['cid'];
            $cids[] = $C['cid'];
        }
    } while (count($parents) > 0);

    if (count($cids) > 0) {
        $categorysql .= "({$table}cid IN ('" . implode("','", $cids) . "'))";
    } else {
        $categorysql .= '0';
    }

    return $categorysql;
}

function LINKS_siteHeader($title='', $meta='')
{
    global $_LI_CONF;

    $retval = '';

    switch( $_LI_CONF['displayblocks'] ) {
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

function LINKS_siteFooter() {
    global $_CONF, $_LI_CONF;

    $retval = '';

    switch( $_LI_CONF['displayblocks'] ) {
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
