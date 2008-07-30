<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | Implementation of the glFusion Plugin API                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2007 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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

if (strpos ($_SERVER['PHP_SELF'], 'functions.inc') !== false)
{
    die ('This file can not be used on its own.');
}

if (!defined ('BAD_BEHAVIOR_PLUGIN')) {
    define('BAD_BEHAVIOR_PLUGIN', 'bad_behavior2');
}
if (!defined ('BAD_BEHAVIOR_VERSION')) {
    define ('BAD_BEHAVIOR_VERSION', '2.0.13a');
}

define('WP_BB_LOG', $_DB_table_prefix . 'bad_behavior2');

$langfile = $_CONF['path'] . 'plugins/' . BAD_BEHAVIOR_PLUGIN . '/language/'
          . $_CONF['language'] . '.php';
if (file_exists ($langfile)) {
    include ($langfile);
} else {
    include ($_CONF['path'] . 'plugins/' . BAD_BEHAVIOR_PLUGIN
             . '/language/english.php');
}

function plugin_cclabel_bad_behavior2 ()
{
    global $_CONF, $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_head') ) {
        return array ($LANG_BAD_BEHAVIOR['plugin_display_name'],
                $_CONF['site_admin_url'] . '/plugins/' . BAD_BEHAVIOR_PLUGIN
                                         . '/index.php',
                plugin_geticon_bad_behavior2 ());
    }

    return false;
}

function plugin_getadminoption_bad_behavior2 ()
{
    global $_CONF, $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_head') ) {
        return array ($LANG_BAD_BEHAVIOR['plugin_display_name'],
                      $_CONF['site_admin_url'] . '/plugins/'
                            . BAD_BEHAVIOR_PLUGIN . '/index.php?mode=list', DB_count (WP_BB_LOG));
    }
}

function plugin_uninstall_bad_behavior2 ()
{
    global $_CONF, $_TABLES, $_DB_table_prefix;

    require_once($_CONF['path'] . 'plugins/bad_behavior2/install.inc');

    $rc = plugin_remove_bad_behavior2();
    return $rc;
}

function plugin_chkversion_bad_behavior2 ()
{
    return BAD_BEHAVIOR_VERSION;
}

function plugin_upgrade_bad_behavior2 ()
{
    global $_TABLES;

    // Bad Behavior handles its database changes automatically,
    // so only update the version number
    DB_query ("UPDATE {$_TABLES['plugins']} SET pi_version = '" . BAD_BEHAVIOR_VERSION . "', pi_gl_version = '1.4.1', pi_homepage = 'http://www.bad-behavior.ioerror.us/' WHERE pi_name = 'bad_behavior2'");

    return true;
}

function plugin_searchtypes_bad_behavior2 ()
{
    global $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_head')) {
        $tmp['bad_behavior'] = $LANG_BAD_BEHAVIOR['plugin_display_name'];

        return $tmp;
    } else {
        return array ();
    }
}

function plugin_dopluginsearch_bad_behavior2 ($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page = -1, $perpage = -1)
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE;

    if (empty ($type)) {
        $type = 'all';
    }

    // Bail if we aren't supppose to do our search
    if (!SEC_inGroup ('Bad Behavior2 Admin') ||
            (($type != 'all') && ($type != 'bad_behavior2'))) {
        $plugin_results = new Plugin ();
        $plugin_results->plugin_name = 'bad_behavior2';
        $plugin_results->searchlabel = $LANG_BAD_BEHAVIOR['results'];

        return $plugin_results;
    }

    // do empty queries (i.e. return everything) only when explicitly
    // searching for Bad Behavior
    if (empty ($query) && ($type != 'bad_behavior2')) {
        $plugin_results = new Plugin ();
        $plugin_results->plugin_name = 'bad_behavior2';
        $plugin_results->searchlabel = $LANG_BAD_BEHAVIOR['results'];

        return $plugin_results;
    }

    $select = 'SELECT id,ip,UNIX_TIMESTAMP(date) AS day,date,`key`';
    $sql = ' FROM ' . WP_BB_LOG;

    $prefix = ' WHERE';
    if (!empty ($query)) {
        if ($keyType == 'phrase') {
            $mysearchterm = addslashes ($query);
            $sql .= " WHERE ((ip LIKE '%$mysearchterm%')"
                 . " OR (http_headers LIKE '%$mysearchterm%')"
                 . " OR (`key` LIKE '%$mysearchterm%'))";
        } else if ($keyType == 'all') {
            $mywords = explode (' ', $query);
            $sql .= ' WHERE (';
            $tmp = '';
            foreach ($mywords AS $mysearchterm) {
                $mysearchterm = addslashes (trim ($mysearchterm));
                if (!empty ($mysearchterm)) {
                    $tmp .= "((ip LIKE '%$mysearchterm%')"
                         . " OR (http_headers LIKE '%$mysearchterm%')"
                         . " OR (`key` LIKE '%$mysearchterm%')) AND ";
                }
            }
            $tmp = substr ($tmp, 0, strlen ($tmp) - 5);
            $sql .= $tmp . ')';
        } else if ($keyType == 'any') {
            $mywords = explode (' ', $query);
            $sql .= ' WHERE (';
            $tmp = '';
            foreach ($mywords AS $mysearchterm) {
                $mysearchterm = addslashes (trim ($mysearchterm));
                if (!empty ($mysearchterm)) {
                    $tmp .= "(ip LIKE '%$mysearchterm%')"
                         . " OR (http_headers LIKE '%$mysearchterm%')"
                         . " OR (`key` LIKE '%$mysearchterm%') OR ";
                }
            }
            $tmp = substr ($tmp, 0, strlen ($tmp) - 4);
            $sql .= $tmp . ')';
        } else {
            $mysearchterm = addslashes ($query);
            $sql .= " WHERE ((ip LIKE '%$mysearchterm%')"
                 . " OR (http_headers LIKE '%$mysearchterm%')"
                 . " OR (`key` LIKE '%$mysearchterm%'))";
        }
        $prefix = ' AND';
    }

    if (!empty ($datestart) && !empty ($dateend)) {
        $delim = substr ($datestart, 4, 1);
        $DS = explode ($delim, $datestart);
        $DE = explode ($delim, $dateend);
        $startdate = mktime (0, 0, 0, $DS[1], $DS[2], $DS[0]);
        $enddate = mktime (23, 59, 59, $DE[1], $DE[2], $DE[0]);
        $sql .= $prefix;
        $sql .= " (UNIX_TIMESTAMP(date) BETWEEN '$startdate' AND '$enddate')";
    }
    $sql .= ' ORDER BY date DESC';
    if (($perpage > 0) && ($page > 0)) {
        $l = ($perpage * $page) - $perpage;
        $sql .= ' LIMIT ' . $l . ',' . $perpage;
    }

    // Perform search
    $result = DB_query ($select . $sql);
    $mycount = DB_numRows ($result);
    if (($perpage > 0) && ($page > 0)) {
        $result_count = DB_query ('SELECT COUNT(*)' . $sql);
    } else {
        $result_count = DB_query ('SELECT COUNT(*) FROM ' . WP_BB_LOG);
    }
    $B = DB_fetchArray ($result_count, true);

    // OK, now return table header labels
    $plugin_results = new Plugin();
    $plugin_results->plugin_name = 'bad_behavior2';
    $plugin_results->searchlabel = $LANG_BAD_BEHAVIOR['results'];
    $plugin_results->addSearchHeading ($LANG_BAD_BEHAVIOR['row_ip']);
    $plugin_results->addSearchHeading ($LANG_BAD_BEHAVIOR['row_date']);
    $plugin_results->addSearchHeading ($LANG_BAD_BEHAVIOR['denied_reason']);
    $plugin_results->num_searchresults = 0;
    $plugin_results->num_itemssearched = $B[0];
    if (($perpage > 0) && ($page > 0)) {
        $plugin_results->supports_paging = true;
    }

    for ($i = 0; $i < $mycount; $i++) {
        $A = DB_fetchArray ($result);

        $viewurl = $_CONF['site_admin_url']
            . '/plugins/bad_behavior2/index.php?mode=view&amp;id=' . $A['id'];

        if (empty ($_CONF['ip_lookup'])) {
            $ip = $A['ip'];
        } else {
            $iplookup = str_replace ('*', $A['ip'], $_CONF['ip_lookup']);
            $ip = '<a href="' . $iplookup . '" title="'
                . $LANG_BAD_BEHAVIOR['title_lookup_ip'] . '">'
                . $A['ip'] . '</a>';
        }

        $row = array ($ip, '<a href="' . $viewurl . '" title="' . $LANG_BAD_BEHAVIOR['title_show_headers'] . '">' . $A['date'] . '</a>', $LANG_BB2_RESPONSE[$A['key']]);
        $plugin_results->addSearchResult ($row);
        $plugin_results->num_searchresults++;
    }

    return $plugin_results;
}

function plugin_getheadercode_bad_behavior2() {

    if ( function_exists('bb2_insert_head') ) {
        return  bb2_insert_head();
    }
    return '';
}


/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_bad_behavior2 ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/' . BAD_BEHAVIOR_PLUGIN
                              . '/images/bad_behavior2.png';
}

/**
* Return total number of blocked IPs currently in the BB logs
*
* @return   int     number of distinct IP addresses blocked
*
*/
function BB_countBlockedIPs ()
{
    $result = DB_query ("SELECT COUNT(DISTINCT ip) AS count FROM " . WP_BB_LOG);
    list($blocked_ips) = DB_fetchArray ($result);

    return $blocked_ips;
}

function plugin_showstats_bad_behavior2 ($showsitestats)
{
    global $_CONF, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE;

    $retval = '';

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_head')) {
        $result = DB_query ("SELECT `key`,COUNT(`key`) AS count FROM " . WP_BB_LOG . " GROUP BY `key` ORDER BY 2 DESC,`key` LIMIT 10");
        $nrows  = DB_numRows ($result);
        if ($nrows > 0) {

            require_once ($_CONF['path_system'] . 'lib-admin.php');

            $header_arr = array(
                array ('text'         => $LANG_BAD_BEHAVIOR['stats_reason'],
                       'field'        => 'key',
                       'header_class' => 'stats-header-title'),
                array ('text'         => $LANG_BAD_BEHAVIOR['stats_blocked'],
                       'field'        => 'count',
                       'header_class' => 'stats-header-count',
                       'field_class'  => 'stats-list-count' )
            );
            $data_arr = array();
            $text_arr = array(
                'has_menu' => false,
                'title'    => $LANG_BAD_BEHAVIOR['stats_headline']
            );

            for ($i = 0; $i < $nrows; $i++) {
                $B = array();
                $A = DB_fetchArray ($result);
                $url = $_CONF['site_url']
                     . '/search.php?mode=search&amp;type=bad_behavior2'
                     . '&amp;query=' . urlencode ($A['key']);
                $B['key'] = '<a href="' . $url . '">'
                                    . $LANG_BB2_RESPONSE[$A['key']] . '</a>';
                $B['count'] = COM_numberFormat ($A['count']);
                $data_arr[$i] = $B;
            }
            $retval .= ADMIN_simpleList ('', $header_arr, $text_arr, $data_arr);
        } else {
            $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['stats_headline']);
            $retval .= $LANG_BAD_BEHAVIOR['stats_no_hits'];
            $retval .= COM_endBlock ();
        }
    }

    return $retval;
}

/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
*
*/
function plugin_statssummary_bad_behavior2 ()
{
    global $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_head')) {
        $blocked_ips = BB_countBlockedIPs ();

        return array ($LANG_BAD_BEHAVIOR['blocked_ips'],
                      COM_numberFormat ($blocked_ips));
    } else {
        return false;
    }
}

function plugin_templatesetvars_bad_behavior2($name, &$template) {
    global $_CP_CONF, $_USER, $_TABLES;

    if ( function_exists('bb2_insert_stats') ) {
        $template->set_var('bb2stats', bb2_insert_stats());
    }
}

function phpblock_bad_behavior2_stats() {
    if ( function_exists('bb2_insert_stats') ) {
        return bb2_insert_stats();
    }
    return '';
}
?>