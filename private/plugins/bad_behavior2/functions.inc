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
// | Copyright (C) 2005-2009 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

if (!defined ('BAD_BEHAVIOR_PLUGIN')) {
    define('BAD_BEHAVIOR_PLUGIN', 'bad_behavior2');
}
if (!defined ('BAD_BEHAVIOR_VERSION')) {
    define ('BAD_BEHAVIOR_VERSION', '2.0.38');
}

define('WP_BB_LOG', $_DB_table_prefix . 'bad_behavior2');

$langfile = $_CONF['path'] . 'plugins/' . BAD_BEHAVIOR_PLUGIN
    . '/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'] . 'plugins/' . BAD_BEHAVIOR_PLUGIN
    . '/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/' . BAD_BEHAVIOR_PLUGIN
        . '/language/english.php';
    $custfile = $_CONF['path'].'plugins/' . BAD_BEHAVIOR_PLUGIN
        . '/language/custom/english.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

require_once $_CONF['path'].'plugins/bad_behavior2/bad_behavior2.php';

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

function plugin_chkversion_bad_behavior2 ()
{
    return BAD_BEHAVIOR_VERSION;
}

function plugin_upgrade_bad_behavior2 ()
{
    global $_TABLES, $_CONF;

    require_once $_CONF['path'].'plugins/bad_behavior2/upgrade.php';

    return bad_behavior2_upgrade();
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

function plugin_searchtypes_bad_behavior2 ()
{
    global $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup('Bad Behavior2 Admin') &&
            function_exists('bb2_insert_head')) {
        $tmp['bad_behavior2'] = $LANG_BAD_BEHAVIOR['plugin_display_name'];

        return $tmp;
    }

    return array();
}

function plugin_dopluginsearch_bad_behavior2($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page = -1, $perpage = -1)
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE;

    $query = DB_escapeString(trim($query));

    if (empty($type)) {
        $type = 'all';
    }

    if (!SEC_inGroup('Bad Behavior2 Admin') ||
            (($type != 'all') && ($type != 'bad_behavior2'))) {
        return;
    }

    // do empty queries (i.e. return everything) only when explicitly
    // searching for Bad Behavior
    if (empty($query) && ($type != 'bad_behavior2')) {
        return;
    }

    // Build search SQL
    $sql = 'SELECT id, ip AS title, UNIX_TIMESTAMP(date) AS date, http_headers AS description, `key`, ';
    $sql .= "CONCAT('{$_CONF['site_admin_url']}/plugins/bad_behavior2/index.php?mode=view&amp;id=',id) AS url ";
    $sql .= 'FROM ' . WP_BB_LOG . ' WHERE 1=1 ';

    // Create a SearchCriteria instance with the name of the plugin
    $search = new SearchCriteria('bad_behavior2', $LANG_BAD_BEHAVIOR['results']);

    // These are the columns in the table that need searching
    $columns = array('title' => 'ip', 'http_headers');

    $sql .= $search->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(date)', $datestart, $dateend);

    // Get back the completed SQL query
    list($sql, $ftsql) = $search->buildSearchSQL($keyType, $query, $columns,
                                                 $sql);

    // Set the Std. SQL Query
    $search->setSQL($sql);
    $search->setRank(2);

    return $search;
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