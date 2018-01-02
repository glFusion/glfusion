<?php
// +--------------------------------------------------------------------------+
// | Spam-X Plugin - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | dashboard.php                                                            |
// |                                                                          |
// | Spam-X Dashbaord                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2016-2018 by the following authors:                        |
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
require_once '../../auth.inc.php';

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

/**
* Main
*/

USES_lib_admin();

$page = '';

$menu_arr = array (
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name'],'active'=>true),
    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php','text' => $LANG_SX00['filters']),
//    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_comments']),
//    array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_trackbacks']),
    array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
);

$T = new Template($_CONF['path'].'plugins/spamx/templates');
$T->set_file('page','dashboard.thtml');

$T->set_var('admin_menu',
    ADMIN_createMenu($menu_arr, $LANG_SX00['instructions'],
                     $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png'));

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

$sql = "select *,count(*) AS count from {$_TABLES['spamx_stats']} GROUP BY module, type";
$result = DB_query($sql);
while ( ( $row = DB_fetchArray($result)) != NULL ) {
    $stats[$row['module']][$row['type']] = $row['count'];
}

$T->set_var(array(
    'lang_spamx'            => $LANG_SX00['plugin_name'],
    'lang_auto_refresh_on'  => $LANG_SX00['auto_refresh_on'],
    'lang_auto_refresh_off' => $LANG_SX00['auto_refresh_off'],
    'lang_spamx_title'      => $LANG_SX00['stats_headline'],
    'lang_type'             => $LANG_SX00['type'],
    'lang_blocked'          => $LANG_SX00['blocked'],
    'lang_no_blocked'       => $LANG_SX00['no_blocked'],
));

$T->set_block('page', 'module', 'moduleblock');

foreach ($stats AS $module => $statistics) {
    $process = 0;
    switch ($module) {
        case 'Akismet' :
            if ( isset($_SPX_CONF['akismet_enable']) && $_SPX_CONF['akismet_enable'] == 1) {
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
                $T->set_var('count',$num);
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