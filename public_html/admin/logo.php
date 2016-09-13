<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | logo.php                                                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
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

// Only let admin users access this page
if (!SEC_hasRights('logo.admin')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog("User {$_USER['username']} tried to illegally access the logo administration screen.");
    echo $display;
    exit;
}

function _logoEdit() {
    global $_CONF, $_LOGO, $_TABLES, $LANG_ADMIN, $LANG_LOGO, $_IMAGE_TYPE;

    $retval = '';

    $menu_arr = array(
            array('url'  => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  .= COM_startBlock($LANG_LOGO['logo_options'],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval  .= ADMIN_createMenu($menu_arr, $LANG_LOGO['instructions'],
                                $_CONF['layout_url'] . '/images/icons/logo.' . $_IMAGE_TYPE);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    if ( file_exists($_CONF['path_html'] . '/images/' . $_LOGO['logo_name'] ) ) {
        $current_logo = '<img src="' . $_CONF['site_url'] . '/images/' . $_LOGO['logo_name'] . '" alt="" border="0"/>';
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
                $rc = move_uploaded_file($file['tmp_name'], $_CONF['path_html'] . 'images/' . $newlogoname);
                @chmod($_CONF['path_html'] . 'images/' . $newlogoname,0644);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/images/' . $_LOGO['logo_name']);
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

    $logo = DB_escapeString($logo);
    $slogan = DB_escapeString($slogan);
    $logo_name = DB_escapeString($logo_name);

    DB_save($_TABLES['logo'],"config_name,config_value","'use_graphic_logo','$logo'");
    DB_save($_TABLES['logo'],"config_name,config_value","'display_site_slogan','$slogan'");
    DB_save($_TABLES['logo'],"config_name,config_value","'logo_name','$logo_name'");


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
            $rc = _saveLogo();
            switch ( $rc ) {
                case 2:
                    $message = $LANG_LOGO['invalid_type'];
                    break;
                case 4 :
                    $message = $LANG_LOGO['invalid_size'].$_CONF['max_logo_height'].'x'.$_CONF['max_logo_width'].'px';
                    break;
                default :
                    $message = $LANG_LOGO['logo_saved'];
                    break;
            }

            $content = COM_showMessageText( $message, 'Logo Administration' );
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