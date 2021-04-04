<?php
/**
* glFusion CMS
*
* glFusion Version Check
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2010-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Log\Log;

USES_lib_admin();

$display = '';

if (!SEC_inGroup ('Root')) {
    Log::logAccessViolation('Version Checker');
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[200],$MESSAGE[30],true,'error')
        . COM_siteFooter ();
    echo $display;
    exit;
}

function _displayVersionData()
{
    global $_CONF, $_USER, $LANG01, $LANG_UPGRADE, $LANG_ENVCHK, $LANG01, $LANG_ADMIN, $_PLUGIN_INFO;

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
        array('url'  => $_CONF['site_admin_url'].'/index.php',
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
        case -1 :
            $alertIcon  = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg  = $LANG_UPGRADE['error_title'];
            $statusText = $LANG_UPGRADE['error'];
            $T->set_var('warningalert',true);
            break;
        case 0 :
            $alertIcon  = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg  = $LANG_UPGRADE['upgrade_title'];
            $statusText = sprintf($LANG_UPGRADE['upgrade'],$pluginData['glfusioncms']['installed_version'],$pluginData['glfusioncms']['latest_version']);
            $T->set_var('upgradeneeded',true);
            break;
        case 1 :
            $alertIcon  = $_CONF['layout_url'].'/images/check.png';
            $statusMsg  = $LANG_UPGRADE['uptodate_title'];
            $statusText = $LANG_UPGRADE['uptodate'];
            $T->set_var('uptodate',true);
            break;
        case 2 :
            $alertIcon  = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg  = $LANG_UPGRADE['unknown_title'];
            $statusText = sprintf($LANG_UPGRADE['unknown'],$pluginData['glfusioncms']['installed_version']);
            $T->set_var('warningalert',true);
            break;
        default :
            $alertIcon  = $_CONF['layout_url'].'/images/alert.png';
            $statusMsg  = $LANG_UPGRADE['error_title'];
            $statusText = $LANG_UPGRADE['error'];
            $T->set_var('warningalert',true);
            break;
    }

    $T->set_var(array(
        'alerticon' => $alertIcon,
        'statusmsg' => $statusMsg,
        'statustext' => $statusText,
    ));

    if ( !_phpUpToDate() ) {
        $T->set_var(array(
            'phpendoflife' => true,
            'phpeol_statusmsg' => $LANG_UPGRADE['phpeol'],
            'phpeol_statustext' => $LANG_ENVCHK['phpendoflife'],
        ));
    }

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