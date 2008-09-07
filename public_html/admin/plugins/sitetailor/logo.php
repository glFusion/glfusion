<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | logo.php                                                                 |
// |                                                                          |
// | Logo Administrat.                                                        |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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

require_once('../../../lib-common.php');

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('sitetailor.admin')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Site Tailor Administration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_ST00['access_denied']);
    $display .= $LANG_ST00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function ST_logoEdit() {
    global $_CONF, $_TABLES, $_ST_CONF, $LANG_ST01;

    $retval = '';

    if ( file_exists($_CONF['path_html'] . '/images/' . $_ST_CONF['logo_name'] ) ) {
        $current_logo = '<img src="' . $_CONF['site_url'] . '/images/' . $_ST_CONF['logo_name'] . '" alt="" border="0"' . XHTML . '>';
    } else {
        $current_logo = $LANG_ST01['no_logo_graphic'];
    }

    $T = new Template($_CONF['path'] . 'plugins/sitetailor/templates/');
    $T->set_file( array( 'admin' => 'logo.thtml'));

    $T->set_var(array(
        'site_admin_url'        => $_CONF['site_admin_url'],
        'site_url'              => $_CONF['site_url'],
        's_form_action'         => $_CONF['site_admin_url'] . '/plugins/sitetailor/logo.php',
        'xhtml'                 => XHTML,
        'graphic_logo_selected' => $_ST_CONF['use_graphic_logo'] == 1 ? ' checked="checked"' : '',
        'text_logo_selected'    => $_ST_CONF['use_graphic_logo'] == 0 ? ' checked="checked"' : '',
        'slogan_selected'       => $_ST_CONF['display_site_slogan'] == 1 ? ' checked="checked"' : '',
        'current_logo_graphic'  => $current_logo,
    ));
    $T->parse('output', 'admin');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function ST_saveLogo() {
    global $_CONF, $_TABLES, $_ST_CONF, $LANG_ST01;

    $retval = 1;

    $logo   = isset($_POST['usegraphic']) ? COM_applyFilter($_POST['usegraphic'],true) : 0;
    $slogan = isset($_POST['siteslogan']) ? COM_applyFilter($_POST['siteslogan'],true) : 0;
    $logo_name = $_ST_CONF['logo_name'];

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
            if ( $imgInfo[0] > $_ST_CONF['max_logo_width'] || $imgInfo[1] > $_ST_CONF['max_logo_height'] ) {
                $retval = 4;
            } else {
                $newlogoname = 'logo' . substr(md5(uniqid(rand())),0,8) . $ext;
                $rc = move_uploaded_file($file['tmp_name'], $_CONF['path_html'] . 'images/' . $newlogoname);
                if ( $rc ) {
                    @unlink($_CONF['path_html'] . '/images/' . $_ST_CONF['logo_name']);
                    $logo_name = $newlogoname;
                }
            }
        }
    }
    DB_save($_TABLES['st_config'],"config_name,config_value","'use_graphic_logo','$logo'");
    DB_save($_TABLES['st_config'],"config_name,config_value","'display_site_slogan','$slogan'");
    DB_save($_TABLES['st_config'],"config_name,config_value","'logo_name','$logo_name'");

    $_ST_CONF['use_graphic_logo'] = $logo;
    $_ST_CONF['display_site_slogan'] = $slogan;
    $_ST_CONF['logo_name'] = $logo_name;

    return $retval;
}

/*
 * Main processing loop
 */

if (isset($_GET['msg']) ) {
    $msg = COM_applyFilter($_GET['msg'],true);
} else {
    $msg = 0;
}

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
            $content    = ST_logoEdit ( );
            $currentSelect = $LANG_ST01['logo'];
            break;
        case 'savelogo' :
            $rc = ST_saveLogo();
            $content = COM_showMessage( $rc, 'sitetailor' );
            $content .= ST_logoEdit( );
            $currentSelect = $LANG_ST01['logo'];
            break;
        default :
            $content    = ST_logoEdit ( );
            break;
    }
} else {
    $content    = ST_logoEdit ( );
}

$display = COM_siteHeader();
$display .= '<noscript>' . LB;
$display .= '    <div class="pluginAlert aligncenter" style="border:1px dashed #ccc;margin-top:10px;padding:15px;">' . LB;
$display .= '    <p>' . $LANG_ST01['javascript_required'] . '</p>' . LB;
$display .= '    </div>' . LB;
$display .= '</noscript>' . LB;
$display .= '<div id="sitetailor" style="display:none;">' . LB;

$display .= $content;
$display .= '</div>';
$display .= COM_siteFooter();
echo $display;
exit;
?>