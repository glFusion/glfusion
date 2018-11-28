<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | Implementation of the glFusion Plugin API                                |
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2014 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

$langfile = $_CONF['path'].'plugins/bad_behavior2/language/'.$_CONF['language'].'.php';
$custfile = $_CONF['path'].'plugins/bad_behavior2/language/custom/'.$_CONF['language'].'.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/bad_behavior2/language/english_utf-8.php';
    $custfile = $_CONF['path'].'plugins/bad_behavior2/language/custom/english_utf-8.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

require_once $_CONF['path'].'plugins/bad_behavior2/bad_behavior2.php';

if (!defined ('BAD_BEHAVIOR_VERSION')) {
    define ('BAD_BEHAVIOR_VERSION', $_BB2_CONF['pi_version']);
}
if (!defined ('BAD_BEHAVIOR_PLUGIN')) {
    define('BAD_BEHAVIOR_PLUGIN', 'bad_behavior2');
}
define('WP_BB_LOG', $_TABLES['bad_behavior2']);


function plugin_cclabel_bad_behavior2 ()
{
    global $_CONF, $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_stats') ) {
        return array ($LANG_BAD_BEHAVIOR['plugin_display_name'],
                $_CONF['site_admin_url'] . '/plugins/bad_behavior2/index.php',
                plugin_geticon_bad_behavior2 ());
    }

    return false;
}

function plugin_getadminoption_bad_behavior2 ()
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR;

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_stats') ) {
        return array ($LANG_BAD_BEHAVIOR['plugin_display_name'],
                      $_CONF['site_admin_url'] . '/plugins/bad_behavior2/index.php?mode=list', DB_count ($_TABLES['bad_behavior2']));
    }
}

function plugin_chkversion_bad_behavior2 ()
{
	global $_BB2_CONF;

	return $_BB2_CONF['pi_version'];
}

function plugin_upgrade_bad_behavior2 ()
{
    global $_TABLES, $_CONF;

    require_once $_CONF['path'].'plugins/bad_behavior2/upgrade.php';

    return bad_behavior2_upgrade();
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

    return $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png';
}

/**
 * Enable / disable plugin - called from plugin admin console
 *
 * If running in Demo mode - never disable the plugin
 *
 * @param   boolean enable state
 * @return  true
 */

function plugin_enablestatechange_bad_behavior2($enable) {

    if ( defined('DEMO_MODE') && $enable == false ) {
        return 99;
    }
    return 1;
}

function plugin_runScheduledTask_bad_behavior2()
{
    global $_CONF;

    if (isset($_CONF['bb2_enabled']) && $_CONF['bb2_enabled']) bb2_expireBans();
}

/**
* Return total number of blocked IPs currently in the BB logs
*
* @return   int     number of distinct IP addresses blocked
*
*/
function BB_countBlockedIPs ()
{
	global $_TABLES;

    $result = DB_query ("SELECT COUNT(DISTINCT ip) AS count FROM " . $_TABLES['bad_behavior2']);
    list($blocked_ips) = DB_fetchArray ($result);

    return $blocked_ips;
}

function plugin_showstats_bad_behavior2 ($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE;

    $retval = '';

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_stats')) {
        $result = DB_query ("SELECT `key`,COUNT(`key`) AS count FROM " . $_TABLES['bad_behavior2'] . " GROUP BY `key` ORDER BY 2 DESC,`key` LIMIT 10");
        $nrows  = DB_numRows ($result);
        if ($nrows > 0) {

            USES_lib_admin();

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
                $url = $_CONF['site_admin_url']
                     . '/plugins/bad_behavior2/index.php?mode=search&amp;key='.urlencode ($A['key']);
                $B['key'] = '<a href="' . $url . '">'
                                    . $LANG_BB2_RESPONSE[$A['key']] . '</a>';
                $B['count'] = COM_numberFormat ($A['count']);
                $data_arr[$i] = $B;
            }
            $retval .= ADMIN_simpleList ('', $header_arr, $text_arr, $data_arr);
        } else {
            $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['stats_headline'],'',COM_getBlockTemplate('_admin_block', 'header'));
            $retval .= $LANG_BAD_BEHAVIOR['stats_no_hits'];
            $retval .= COM_endBlock (COM_getBlockTemplate('_admin_block', 'footer'));
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

    if (SEC_inGroup ('Bad Behavior2 Admin') && function_exists('bb2_insert_stats')) {
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

function _validate_ip_cidr($cidr)
{
    $must_cidr = false;
    if (!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\/[0-9]{1,2})?$/", $cidr)) {
        $return = false;
    } else {
        $return = true;
    }
    if ($return == true) {
        $parts = explode("/", $cidr);
        $ip = @$parts[0];
        $netmask = @$parts[1];
        $octets = explode(".", $ip);
        foreach ($octets as $octet) {
            if ($octet > 255) {
                $return = false;
            }
        }
        if ((($netmask != "") && ($netmask > 32) && !$must_cidr) || (($netmask == ""||$netmask > 32) && $must_cidr)) {
            $return = false;
        }
    }
    return $return;
}
?>