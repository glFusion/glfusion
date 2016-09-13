<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | vercheck.php                                                             |
// |                                                                          |
// | glFusion Version Check                                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2016 by the following authors:                        |
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

require_once '../lib-common.php';
require_once 'auth.inc.php';
USES_lib_admin();

$display = '';

if (!SEC_inGroup ('Root')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[200],$MESSAGE[30],true,'error')
        . COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to access the version check screen");
    echo $display;
    exit;
}

function _displayVersionData()
{
    global $_CONF, $_USER, $LANG_UPGRADE, $LANG01, $LANG_FILECHECK, $LANG_ADMIN, $_PLUGIN_INFO;

    $retval = '';
    $upToDate = 0;
    $classCounter = 0;
    $pluginInfo = '';

    list($upToDate,$pluginsUpToDate,$pluginData) = _checkVersion();

    $T = new Template($_CONF['path_layout'] . 'admin');
    $T->set_file('page','vercheck.thtml');

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/vercheck.php',
              'text' => $LANG_UPGRADE['recheck']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_UPGRADE['title'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_UPGRADE['desc'],
        $_CONF['layout_url'] . '/images/icons/versioncheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    switch ( $upToDate ) {
        case 0 :
            $alertIcon  = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg  = $LANG_UPGRADE['upgrade_title'];
            $statusText = sprintf($LANG_UPGRADE['upgrade'],$pluginData['glfusioncms']['installed_version'],$pluginData['glfusioncms']['latest_version']);
            break;
        case 1 :
            $alertIcon  = $_CONF['layout_url'].'/images/check.png';
            $statusMsg  = $LANG_UPGRADE['uptodate_title'];
            $statusText = $LANG_UPGRADE['uptodate'];
            break;
        case 2 :
            $alertIcon  = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg  = $LANG_UPGRADE['unknown_title'];
            $statusText = sprintf($LANG_UPGRADE['unknown'],$pluginData['glfusioncms']['installed_version']);
            break;
        default :
            $alertIcon = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg = $LANG_UPGRADE['error_title'];
            $statusText = $LANG_UPGRADE['error'];
            break;
    }

    $T->set_var(array(
        'alerticon' => $alertIcon,
        'statusmsg' => $statusMsg,
        'statustext' => $statusText,
    ));

    if ( $pluginsUpToDate != -1 ) {
        $pluginInfo .= '<div style="margin-top:10px;"><h3>'.$LANG_UPGRADE['plugin_title'].'</h3>';
        $dt = new Date('now',$_USER['tzid']);

        $data_arr = array();
        $text_arr = array();

        $header_arr = array(
            array('text' => $LANG_UPGRADE['plugin'],  'field' => 'display_name'),
            array('text' => $LANG_UPGRADE['installed_version'], 'field' => 'installed_version'),
            array('text' => $LANG_UPGRADE['latest_version'], 'field' => 'latest_version'),
            array('text' => $LANG_UPGRADE['notes'], 'field' => 'notes'),
        );
        asort($pluginData);
        foreach ($pluginData AS $plugin) {
            if ( $plugin['plugin'] == 'glfusioncms' ) {
                continue;
            }
            $dt->setTimestamp($plugin['release_date']);
            if ( $plugin['latest_version'] == 0 ) {
                $upToDate = -1;
            } else {
                $upToDate = _upToDate($plugin['latest_version'],$plugin['installed_version']);
            }
            switch ($upToDate) {
                case 0 :
                    $notes = sprintf($LANG_UPGRADE['was_released'],$plugin['latest_version'],$dt->format("M d, Y",true));
                    $class = "notok";
                    if ( strlen($plugin['url']) > 0 ) {
                        $latest_version = '<a href="'.$plugin['url'].'" target="_blank">'.$plugin['latest_version'].'</a>';
                    } else {
                        $latest_version = $plugin['latest_version'];
                    }
                    break;
                case 1 :
                    $notes = $LANG_UPGRADE['plugin_uptodate'];
                    $class = "yes";
                    $latest_version = $plugin['latest_version'];
                    break;
                case 2 :
                    $notes = $LANG_UPGRADE['plugin_newer'];
                    $class = "yes";
                    $latest_version = $plugin['latest_version'];
                    break;
                default:
                    $notes = $LANG_UPGRADE['no_data'];
                    $class ="ok";
                    $latest_version = '???';
                    break;
            }

            $data_arr[] = array(
                'display_name'  => $plugin['display_name'],
                'installed_version' => $plugin['installed_version'],
                'latest_version' => '<span class="'.$class.'">'.$latest_version.'</span>',
                'url'            => $plugin['url'],
                'notes'          => $notes,
                'release_date'   => $plugin['release_date'],
                'update_available' => $upToDate,
            );
        }
        $pluginInfo .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
        $pluginInfo .= '</div>';
    }
    $T->parse('output','page');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= $pluginInfo;
    return $retval;
}

$page = _displayVersionData();
$display  = COM_siteHeader();
$display .= $page;
$display .= COM_siteFooter();
echo $display;
?>