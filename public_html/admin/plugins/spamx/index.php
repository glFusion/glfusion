<?php
/**
* glFusion CMS
*
* Spam-X Dashboard
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2016-2019 by the following authors:
*   Mark R. Evans    mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights ('spamx.admin')) {
    COM_accessLog ("Someone has tried to access the Spam-X Admin page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: {$_SERVER['REMOTE_ADDR']}", 1);
    $display = COM_siteHeader ('menu', $LANG_SX00['access_denied']);
    $display .= COM_startBlock ($LANG_SX00['access_denied']);
    $display .= $LANG_SX00['access_denied_msg'];
    $display .= COM_endBlock ();
    $display .= COM_siteFooter (true);
    echo $display;
    exit;
}

function spamx_purge_stats()
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();

    Log::write('system',Log::INFO,"Spam-x: Purging Spam-X stats");
    $mo3 = date("m-1-Y",strtotime("-3 Months")) . 'T00:00:00';
    $dt = new \Date($mo3,$_CONF['timezone']);
    $maxAge = $dt->toMySQL();
    $now = time();
    $sql = "DELETE FROM {$_TABLES['spamx_stats']} WHERE blockdate < '".$maxAge."'";
    try {
        $stmt = $db->conn->executeUpdate(
            "DELETE FROM `{$_TABLES['spamx_stats']}` WHERE blockdate < ?",
            array($maxAge),
            array(Database::STRING)
        );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,'Error purging older Spam-X statistics');
    }

    try {
        $db->conn->executeQuery(
                "REPLACE INTO `{$_TABLES['vars']}` (name,value) VALUES ('spamxstats',?)",
                array($now),
                array(Database::STRING)
        );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,'Error updating Spam-X statistics purge date.');
    }
}

/**
* Main
*/

USES_lib_admin();

$page = '';

if ( !isset($_VARS['spamxstats'])) $_VARS['spamxstats'] = 0;

if ( $_VARS['spamxstats'] + 2592000 < time() ) {
    spamx_purge_stats();
}

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name'],'active'=>true),
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php','text' => $LANG_SX00['filters']),
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/tester.php','text' => $LANG_SX00['interactive_tester']),
    array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
);

$T = new Template($_CONF['path'].'plugins/spamx/templates');
$T->set_file('page','dashboard.thtml');

$T->set_var('admin_menu',
    ADMIN_createMenu($menu_arr, $LANG_SX00['instructions'],
                     $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png'));

/* ---------------------------------
// summary by module only
$sql = "select module,count(*) AS count from {$_TABLES['spamx_stats']} GROUP BY module";
$result = DB_query($sql);
$spamstats = array();
while ( ( $row = DB_fetchArray($result)) != NULL ) {
    $spamstats[$row['module']] = $row['count'];
}

$T->set_block('page','spamxmodule','spamxmoduleblock');
foreach($spamstats AS $mod => $blocked ) {
    $T->set_var('smodule',$mod);
    $T->set_var('sblocked',$blocked);
    $T->parse('spamxmoduleblock','spamxmodule',true);
}
------------------------------------------------------- */
// end of summary by module

$stats = array(
                'Akismet' => array(),
                'Formcheck' => array(),
                'SFS' => array(),
                'SLC' => array(),
                'BlackList' => array(),
                'Header' => array(),
                'IP' => array(),
                'IPofURL' => array(),
         );

$mo3 = date("m-1-Y",strtotime("-3 Months")) . 'T00:00:00';
$dt = new \Date($mo3,$_CONF['timezone']);
$maxAge = $dt->toMySQL();

$db = Database::getInstance();

$stmt = $db->conn->executeQuery(
            "SELECT *,COUNT(*) AS count from `{$_TABLES['spamx_stats']}` WHERE blockdate > ? GROUP BY module, type",
            array($maxAge),
            array(Database::STRING)
);

while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
    $stats[$row['module']][$row['type']] = $row['count'];
}

$T->set_var(array(
    'lang_spamx'            => $LANG_SX00['plugin_name'],
    'lang_auto_refresh_on'  => $LANG_SX00['auto_refresh_on'],
    'lang_auto_refresh_off' => $LANG_SX00['auto_refresh_off'],
    'lang_spamx_title'      => $LANG_SX00['stats_headline'],
    'lang_spamx_history'    => $LANG_SX00['history'],
    'lang_type'             => $LANG_SX00['type'],
    'lang_blocked'          => $LANG_SX00['blocked'],
    'lang_no_blocked'       => $LANG_SX00['no_blocked'],
));

$T->set_block('page', 'module', 'moduleblock');

foreach ($stats AS $module => $statistics) {
    $process = 0;
    switch ($module) {
        case 'Akismet' :
            if ( isset($_SPX_CONF['akismet_enabled']) && $_SPX_CONF['akismet_enabled'] == 1) {
                $process = 1;
            }
            break;
        case 'SFS' :
            if ( isset($_SPX_CONF['sfs_enable']) && $_SPX_CONF['sfs_enable'] == 1) {
                $process = 1;
            }
            break;
        case 'SLC' :
            if ( isset($_SPX_CONF['slc_enable']) && $_SPX_CONF['slc_enable'] == 1) {
                $process = 1;
            }
            break;
        default :
            $process = 1;
            break;
    }
    if ( $process == 1 ) {
        $T->set_var('module',$module);

        if ( is_array($statistics) && count($statistics) > 0 ) {
            foreach ($statistics AS $type => $num) {
                $T->set_block('page', 'type', 'typeblock');
                $T->set_var('type',$type);
                $T->set_var('count',COM_numberFormat($num));
                $T->parse('typeblock', 'type',true);
            }
        } else {
            $T->set_block('page','type','typeblock');
            $T->set_var('no_blocks','none');
            $T->parse('typeblock', 'type',true);
        }
        $T->parse('moduleblock','module',true);
        $T->unset_var('no_blocks');
        $T->unset_var('typeblock');
    }
}

$T->parse( 'output', 'page' );
$page .= $T->finish( $T->get_var( 'output' ));

$display = COM_siteHeader ('menu', $LANG_SX00['plugin_name']);
$display .= $page;
$display .= COM_siteFooter();
echo $display;
?>