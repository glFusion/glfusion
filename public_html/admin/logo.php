<?php
/**
* glFusion CMS
*
* glFusion Logo Administration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Log\Log;

USES_lib_admin();

COM_404();

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('logo.admin')) {
    Log::logAccessViolation('Logo Administration');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

function _logoEdit() {
    global $_CONF, $_LOGO, $_TABLES, $LANG_ADMIN, $LANG_LOGO, $_IMAGE_TYPE;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'].'/index.php',
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_LOGO['logo_options'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_LOGO['instructions'],
                                $_CONF['layout_url'] . '/images/icons/logo.' . $_IMAGE_TYPE);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    if ( file_exists($_CONF['path_images'] . $_LOGO['logo_name'] ) ) {
        $current_logo = '<img src="' . $_CONF['path_images_url'] . '/' . $_LOGO['logo_name'] . '" alt="" border="0"/>';
    } else {
        $current_logo = $LANG_LOGO['no_logo_graphic'];
    }

    $T = new Template(($_CONF['path_layout'] . 'admin/logo/'));
    $T->set_file('admin','logo.thtml');
    $T->set_var(array(
        's_form_action'         => $_CONF['site_admin_url'] . '/logo.php',
        'graphic_logo_selected' => $_LOGO['use_graphic_logo'] == 1 ? ' checked="checked"' : '',
        'text_logo_selected'    => $_LOGO['use_graphic_logo'] == 0 ? ' checked="checked"' : '',
        'no_logo_selected'      => $_LOGO['use_graphic_logo'] == -1 ? ' checked="checked"' : '',
        'slogan_selected'       => $_LOGO['display_site_slogan'] == 1 ? ' checked="checked"' : '',
        'current_logo_graphic'  => $current_logo,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function _saveLogo() {
    global $_CONF, $_TABLES, $_LOGO, $LANG_LOGO;

    // return values:
    // 1 = OK
    // 2 = Unknown file type
    // 3 = no file available
    // 4 = invalid size

    $db = Database::getInstance();

    $retval = 1;

    $logo   = isset($_POST['usegraphic']) ? COM_applyFilter($_POST['usegraphic'],true) : 0;
    $slogan = isset($_POST['siteslogan']) ? COM_applyFilter($_POST['siteslogan'],true) : 0;
    $logo_name = $_LOGO['logo_name'];

    $file = array();
    $file = $_FILES['newlogo'];
    if ( isset($file['tmp_name']) && $file['tmp_name'] != '' ) {
        switch ( $file['type'] ) {
            case 'image/png' :
            case 'image/x-png' :
                $ext = '.png';
                break;
            case 'image/gif' :
                $ext = '.gif';
                break;
            case 'image/jpg' :
            case 'image/jpeg' :
            case 'image/pjpeg' :
                $ext = '.jpg';
                break;
            default :
                $ext = 'unknown';
                $retval = 2;
                break;
        }
        if ( $ext != 'unknown' ) {
            $imgInfo = @getimagesize($file['tmp_name']);
            if ( $imgInfo[0] > $_CONF['max_logo_width'] || $imgInfo[1] > $_CONF['max_logo_height'] ) {
                $retval = 4;
            } else {
                $newlogoname = 'logo' . substr(md5(uniqid(rand())),0,8) . $ext;
                $rc = move_uploaded_file($file['tmp_name'], $_CONF['path_images'] . $newlogoname);
                @chmod($_CONF['path_images'] . $newlogoname,0644);
                if ( $rc ) {
                    @unlink($_CONF['path_images'] . $_LOGO['logo_name']);
                    $logo_name = $newlogoname;
                }
            }
        }
    } else {
        $retval = 3;
    }

    $_LOGO['use_graphic_logo'] = $logo;
    $_LOGO['display_site_slogan'] = $slogan;
    $_LOGO['logo_name'] = $logo_name;

    $db->conn->executeQuery(
            "REPLACE INTO `{$_TABLES['logo']}` (config_name,config_value)
             VALUES (?,?)",
            array('use_graphic_logo',$logo),
            array(Database::STRING, Database::STRING)
    );
    $db->conn->executeQuery(
            "REPLACE INTO `{$_TABLES['logo']}` (config_name,config_value)
             VALUES (?,?)",
            array('display_site_slogan',$slogan),
            array(Database::STRING, Database::STRING)
    );
    $db->conn->executeQuery(
            "REPLACE INTO `{$_TABLES['logo']}` (config_name,config_value)
             VALUES (?,?)",
            array('logo_name',$logo_name),
            array(Database::STRING, Database::STRING)
    );

    return $retval;
}

/*
 * Main processing loop
 */

$msg = COM_getMessage();

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else if ( isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = '';
}

if ( (isset($_POST['execute']) || $mode != '') && !isset($_POST['cancel']) && !isset($_POST['defaults'])) {
    switch ( $mode ) {
        case 'logo' :
            $content    = _logoEdit ( );
            $currentSelect = $LANG_LOGO['logo'];
            break;
        case 'savelogo' :
            $mtype = 'info';
            $rc = _saveLogo();
            switch ( $rc ) {
                case 2:
                    $message = $LANG_LOGO['invalid_type'];
                    $mtype = 'error';
                    break;
                case 4 :
                    $message = $LANG_LOGO['invalid_size'].$_CONF['max_logo_height'].'x'.$_CONF['max_logo_width'].'px';
                    $mtype = 'error';
                    break;
                default :
                    $message = $LANG_LOGO['logo_saved'];
                    $mtype = 'info';
                    break;
            }

            $content = COM_setMsg($message,$mtype);
            $content .= _logoEdit( );
            $currentSelect = $LANG_LOGO['logo_admin'];
            break;
        default :
            $content    = _logoEdit ( );
            break;
    }
} else {
    $content    = _logoEdit ( );
}

$display = COM_siteHeader();
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;
?>