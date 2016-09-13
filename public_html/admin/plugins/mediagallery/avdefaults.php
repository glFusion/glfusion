<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | Edit Media Gallery A/V Default Settings.                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2015 by the following authors:                        |
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
//

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}",1);
    $display  = COM_siteHeader();
    $dipslay .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_MG00['access_denied'],true,'error');
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_editAVDefaults( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG07, $LANG_MG01, $LANG_MG03, $LANG_ACCESS, $LANG_DIRECTION;
    global $glversion,$LANG04;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');

    $T->set_file(array(
        'admin'         =>  'editavdefaults.thtml',
    ));

    $T->set_var('site_url', $_MG_CONF['site_url']);

    require_once $_CONF['path_system'].'classes/navbar.class.php';

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_MG07['wmp_options'],'showhideMGAdminEditorDiv("wmp",0);return false;',true);
    $navbar->add_menuitem($LANG_MG07['qt_options'],'showhideMGAdminEditorDiv("qt",1);return false;',true);
    $navbar->add_menuitem($LANG_MG07['mp3_options'],'showhideMGAdminEditorDiv("mp3",2);return false;',true);
    $navbar->add_menuitem($LANG_MG07['swf_options'],'showhideMGAdminEditorDiv("flash",3);return false;',true);
    $navbar->set_selected($LANG_MG07['wmp_options']);
    $T->set_var ('navbar', $navbar->generate());
    $T->set_var ('no_javascript_warning',$LANG04[150]);

    // ui mode select

    $asf_uimode_select = '<select name="asf_uimode">';
    $asf_uimode_select .= '<option value="none" ' . ($_MG_CONF['asf_uimode'] == 'none' ? ' selected="selected"' : '') . '>' . $LANG_MG07['none'] . '</option>';
    $asf_uimode_select .= '<option value="mini" ' . ($_MG_CONF['asf_uimode'] == 'mini' ? ' selected="selected"' : '') . '>' . $LANG_MG07['mini'] . '</option>';
    $asf_uimode_select .= '<option value="full" ' . ($_MG_CONF['asf_uimode'] == 'full' ? ' selected="selected"' : '') . '>' . $LANG_MG07['full'] . '</option>';
    $asf_uimode_select .= '</select>';

    $mov_scale_select = '<select name="mov_scale">';
    $mov_scale_select .= '<option value="tofit" '  . ($_MG_CONF['mov_scale'] == 'tofit' ? ' selected="selected"' : '') . '>' . $LANG_MG07['to_fit'] . '</option>';
    $mov_scale_select .= '<option value="aspect" ' . ($_MG_CONF['mov_scale'] == 'aspect' ? ' selected="selected"' : '') . '>' . $LANG_MG07['aspect'] . '</option>';
    $mov_scale_select .= '<option value="1" ' . ($_MG_CONF['mov_scale'] == '1' ? ' selected="selected"' : '') . '>' . $LANG_MG07['normal_size'] . '</option>';
    $mov_scale_select .= '</select>';

    $mp3_uimode_select = '<select name="mp3_uimode">';
    $mp3_uimode_select .= '<option value="none" ' . ($_MG_CONF['mp3_uimode'] == 'none' ? ' selected="selected"' : '') . '>' . $LANG_MG07['none'] . '</option>';
    $mp3_uimode_select .= '<option value="mini" ' . ($_MG_CONF['mp3_uimode'] == 'mini' ? ' selected="selected"' : '') . '>' . $LANG_MG07['mini'] . '</option>';
    $mp3_uimode_select .= '<option value="full" ' . ($_MG_CONF['mp3_uimode'] == 'full' ? ' selected="selected"' : '') . '>' . $LANG_MG07['full'] . '</option>';
    $mp3_uimode_select .= '</select>';

    $swf_quality_select = '<select name="swf_quality">';
    $swf_quality_select .= '<option value="low" '  . ($_MG_CONF['swf_quality'] == 'low' ? ' selected="selected"' : '') . '>' . $LANG_MG07['low'] . '</option>';
    $swf_quality_select .= '<option value="high" ' . ($_MG_CONF['swf_quality'] == 'high' ? ' selected="selected"' : '') . '>' . $LANG_MG07['high'] . '</option>';
    $swf_quality_select .= '</select>';

    $swf_scale_select = '<select name="swf_scale">';
    $swf_scale_select .= '<option value="showall" '  . ($_MG_CONF['swf_scale'] == 'showall'  ? ' selected="selected"' : '') . '>' . $LANG_MG07['showall'] . '</option>';
    $swf_scale_select .= '<option value="noborder" ' . ($_MG_CONF['swf_scale'] == 'noborder' ? ' selected="selected"' : '') . '>' . $LANG_MG07['noborder'] . '</option>';
    $swf_scale_select .= '<option value="exactfit" ' . ($_MG_CONF['swf_scale'] == 'exactfit' ? ' selected="selected"' : '') . '>' . $LANG_MG07['exactfit'] . '</option>';
    $swf_scale_select .= '</select>';

    $swf_wmode_select = '<select name="swf_wmode">';
    $swf_wmode_select .= '<option value="window" '      . ($_MG_CONF['swf_wmode'] == 'window'      ? ' selected="selected"' : '') . '>' . $LANG_MG07['window'] . '</option>';
    $swf_wmode_select .= '<option value="opaque" '      . ($_MG_CONF['swf_wmode'] == 'opaque'      ? ' selected="selected"' : '') . '>' . $LANG_MG07['opaque'] . '</option>';
    $swf_wmode_select .= '<option value="transparent" ' . ($_MG_CONF['swf_wmode'] == 'transparent' ? ' selected="selected"' : '') . '>' . $LANG_MG07['transparent'] . '</option>';
    $swf_wmode_select .= '</select>';

    $swf_asa_select = '<select name="swf_allowscriptaccess">';
    $swf_asa_select .= '<option value="always" '      . ($_MG_CONF['swf_allowscriptaccess'] == 'always'      ? ' selected="selected"' : '') . '>' . $LANG_MG07['always'] . '</option>';
    $swf_asa_select .= '<option value="sameDomain" '  . ($_MG_CONF['swf_allowscriptaccess'] == 'sameDomain'  ? ' selected="selected"' : '') . '>' . $LANG_MG07['sameDomain'] . '</option>';
    $swf_asa_select .= '<option value="never" '       . ($_MG_CONF['swf_allowscriptaccess'] == 'never'       ? ' selected="selected"' : '') . '>' . $LANG_MG07['never'] . '</option>';
    $swf_asa_select .= '</select>';

    $T->set_var(array(
        'lang_save'                     => $LANG_MG01['save'],
        'lang_cancel'                   => $LANG_MG01['cancel'],
        's_form_action'                 => $_MG_CONF['admin_url'] . 'avdefaults.php',
        'lang_asf_options'              => $LANG_MG07['wmp_options'],
        'lang_mov_options'              => $LANG_MG07['qt_options'],
        'lang_mp3_options'              => $LANG_MG07['mp3_options'],
        'lang_swf_options'              => $LANG_MG07['swf_options'],
        'lang_playcount'                => $LANG_MG07['playcount'],
        'lang_playcount_help'           => $LANG_MG07['playcount_help'],
        'lang_option'                   => $LANG_MG07['option'],
        'lang_description'              => $LANG_MG07['description'],
        'lang_on'                       => $LANG_MG07['on'],
        'lang_off'                      => $LANG_MG07['off'],
        'lang_auto_start'               => $LANG_MG07['auto_start'],
        'lang_auto_start_help'          => $LANG_MG07['auto_start_help'],
        'lang_enable_context_menu'      => $LANG_MG07['enable_context_menu'],
        'lang_enable_context_menu_help' => $LANG_MG07['enable_context_menu_help'],
        'lang_stretch_to_fit'           => $LANG_MG07['stretch_to_fit'],
        'lang_stretch_to_fit_help'      => $LANG_MG07['stretch_to_fit_help'],
        'lang_status_bar'               => $LANG_MG07['status_bar'],
        'lang_status_bar_help'          => $LANG_MG07['status_bar_help'],
        'lang_ui_mode'                  => $LANG_MG07['ui_mode'],
        'lang_ui_mode_help'             => $LANG_MG07['ui_mode_help'],
        'lang_height'                   => $LANG_MG07['height'],
        'lang_width'                    => $LANG_MG07['width'],
        'lang_height_help'              => $LANG_MG07['height_help'],
        'lang_width_help'               => $LANG_MG07['width_help'],
        'lang_bgcolor'                  => $LANG_MG07['bgcolor'],
        'lang_bgcolor_help'             => $LANG_MG07['bgcolor_help'],
        'lang_auto_ref'                 => $LANG_MG07['auto_ref'],
        'lang_auto_ref_help'            => $LANG_MG07['auto_ref_help'],
        'lang_controller'               => $LANG_MG07['controller'],
        'lang_controller_help'          => $LANG_MG07['controller_help'],
        'lang_kiosk_mode'               => $LANG_MG07['kiosk_mode'],
        'lang_kiosk_mode_help'          => $LANG_MG07['kiosk_mode_help'],
        'lang_scale'                    => $LANG_MG07['scale'],
        'lang_scale_help'               => $LANG_MG07['scale_help'],
        'lang_loop'                     => $LANG_MG07['loop'],
        'lang_loop_help'                => $LANG_MG07['loop_help'],
        'lang_menu'                     => $LANG_MG07['menu'],
        'lang_menu_help'                => $LANG_MG07['menu_help'],
        'lang_scale'                    => $LANG_MG07['scale'],
        'lang_swf_scale_help'           => $LANG_MG07['swf_scale_help'],
        'lang_wmode'                    => $LANG_MG07['wmode'],
        'lang_wmode_help'               => $LANG_MG07['wmode_help'],
        'lang_quality'                  => $LANG_MG07['quality'],
        'lang_quality_help'             => $LANG_MG07['quality_help'],
        'lang_flash_vars'               => $LANG_MG07['flash_vars'],
        'lang_asa'                      => $LANG_MG07['asa'],
        'lang_asa_help'                 => $LANG_MG07['asa_help'],
        'lang_bgcolor'                  => $LANG_MG07['bgcolor'],
        'lang_bgcolor_help'             => $LANG_MG07['bgcolor_help'],
        'lang_clsid'                    => $LANG_MG07['clsid'],
        'lang_codebase'                 => $LANG_MG07['codebase'],
        'lang_swf_version_help'         => $LANG_MG07['swf_version_help'],
        'asf_autostart_enabled'         => $_MG_CONF['asf_autostart'] ? ' checked="checked"' : '',
        'asf_autostart_disabled'        => $_MG_CONF['asf_autostart'] ? '' : ' checked="checked"',
        'asf_enablecontextmenu_enabled' => $_MG_CONF['asf_enablecontextmenu'] ? ' checked="checked"' : '',
        'asf_enablecontextmenu_disabled'=> $_MG_CONF['asf_enablecontextmenu'] ? '' : ' checked="checked"',
        'asf_stretchtofit_enabled'      => $_MG_CONF['asf_stretchtofit'] ? ' checked="checked"' : '',
        'asf_stretchtofit_disabled'     => $_MG_CONF['asf_stretchtofit'] ? '' : ' checked="checked"',
        'asf_showstatusbar_enabled'     => $_MG_CONF['asf_showstatusbar'] ? ' checked="checked"' : '',
        'asf_showstatusbar_disabled'    => $_MG_CONF['asf_showstatusbar'] ? '' : ' checked="checked"',
        'asf_uimode_select'             => $asf_uimode_select,
        'asf_uimode'                    => $_MG_CONF['asf_uimode'],
        'asf_playcount'                 => $_MG_CONF['asf_playcount'],
        'asf_height'                    => $_MG_CONF['asf_height'],
        'asf_width'                     => $_MG_CONF['asf_width'],
        'asf_bgcolor'                   => $_MG_CONF['asf_bgcolor'],
        'mov_autoref_enabled'           => $_MG_CONF['mov_autoref'] ? ' checked="checked"' : '',
        'mov_autoref_disabled'          => $_MG_CONF['mov_autoref'] ? '' : ' checked="checked"',
        'mov_autoplay_enabled'          => $_MG_CONF['mov_autoplay'] ? ' checked="checked"' : '',
        'mov_autoplay_disabled'         => $_MG_CONF['mov_autoplay'] ? '' : ' checked="checked"',
        'mov_controller_enabled'        => $_MG_CONF['mov_controller'] ? ' checked="checked"' : '',
        'mov_controller_disabled'       => $_MG_CONF['mov_controller'] ? '' : ' checked="checked"',
        'mov_kioskmode_enabled'         => $_MG_CONF['mov_kioskmode'] ? ' checked="checked"' : '',
        'mov_kioskmode_disabled'        => $_MG_CONF['mov_kioskmode'] ? '' : ' checked="checked"',
        'mov_scale_select'              => $mov_scale_select,
        'mov_loop_enabled'              => $_MG_CONF['mov_loop'] ? ' checked="checked"' : '',
        'mov_loop_disabled'             => $_MG_CONF['mov_loop'] ? '' : ' checked="checked"',
        'mov_height'                    => $_MG_CONF['mov_height'],
        'mov_width'                     => $_MG_CONF['mov_width'],
        'mov_bgcolor'                   => $_MG_CONF['mov_bgcolor'],
        'mp3_autostart_enabled'         => $_MG_CONF['mp3_autostart'] ? ' checked="checked"' : '',
        'mp3_autostart_disabled'        => $_MG_CONF['mp3_autostart'] ? '' : ' checked="checked"',
        'mp3_enablecontextmenu_enabled' => $_MG_CONF['mp3_enablecontextmenu'] ? ' checked="checked"' : '',
        'mp3_enablecontextmenu_disabled'=> $_MG_CONF['mp3_enablecontextmenu'] ? '' : ' checked="checked"',
        'mp3_showstatusbar_enabled'     => $_MG_CONF['mp3_showstatusbar'] ? ' checked="checked"' : '',
        'mp3_showstatusbar_disabled'    => $_MG_CONF['mp3_showstatusbar'] ? '' : ' checked="checked"',
        'mp3_loop_enabled'              => $_MG_CONF['mp3_loop'] ? ' checked="checked"' : '',
        'mp3_loop_disabled'             => $_MG_CONF['mp3_loop'] ? '' : ' checked="checked"',
        'mp3_uimode_select'             => $mp3_uimode_select,
        'mp3_uimode'                    => $_MG_CONF['mp3_uimode'],
        'swf_play_enabled'              => $_MG_CONF['swf_play'] ? ' checked="checked"' : '',
        'swf_play_disabled'             => $_MG_CONF['swf_play'] ? '' : ' checked="checked"',
        'swf_menu_enabled'              => $_MG_CONF['swf_menu'] ? ' checked="checked"' : '',
        'swf_menu_disabled'             => $_MG_CONF['swf_menu'] ? '' : ' checked="checked"',
        'swf_loop_enabled'              => $_MG_CONF['swf_loop'] ? ' checked="checked"' : '',
        'swf_loop_disabled'             => $_MG_CONF['swf_loop'] ? '' : ' checked="checked"',
        'swf_quality_select'            => $swf_quality_select,
        'swf_scale_select'              => $swf_scale_select,
        'swf_wmode_select'              => $swf_wmode_select,
        'swf_asa_select'                => $swf_asa_select,
        'swf_flashvars'                 => $_MG_CONF['swf_flashvars'],
        'swf_height'                    => $_MG_CONF['swf_height'],
        'swf_width'                     => $_MG_CONF['swf_width'],
        'swf_bgcolor'                   => $_MG_CONF['swf_bgcolor'],
        'swf_codebase'                  => $_MG_CONF['swf_version'],
        'swf_version'                   => $_MG_CONF['swf_version'],
        'rtl'                           => $LANG_DIRECTION == "rtl" ? "rtl" : "",
        'gltoken_name'                  => CSRF_TOKEN,
        'gltoken'                       => SEC_createToken(),
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveAVDefaults( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $_POST;

    $asf_autostart          = COM_applyFilter($_POST['asf_autostart'],true);
    $asf_enablecontextmenu  = COM_applyFilter($_POST['asf_enablecontextmenu'],true);
    $asf_stretchtofit       = COM_applyFilter($_POST['asf_stretchtofit'],true);
    $asf_showstatusbar      = COM_applyFilter($_POST['asf_showstatusbar'],true);
    $asf_uimode             = COM_applyFilter($_POST['asf_uimode']);
    $asf_playcount          = COM_applyFilter($_POST['asf_playcount'],true);
    $asf_height             = COM_applyFilter($_POST['asf_height'],true);
    $asf_width              = COM_applyFilter($_POST['asf_width'],true);
    $asf_bgcolor            = COM_applyFilter($_POST['asf_bgcolor']);

    $mov_autoref            = COM_applyFilter($_POST['mov_autoref'],true);
    $mov_autoplay           = COM_applyFilter($_POST['mov_autoplay'],true);
    $mov_controller         = COM_applyFilter($_POST['mov_controller'],true);
    $mov_kioskmode          = COM_applyFilter($_POST['mov_kioskmode'],true);
    $mov_scale              = COM_applyFilter($_POST['mov_scale']);
    $mov_loop               = COM_applyFilter($_POST['mov_loop'],true);
    $mov_height             = COM_applyFilter($_POST['mov_height'],true);
    $mov_width              = COM_applyFilter($_POST['mov_width'],true);
    $mov_bgcolor            = COM_applyFilter($_POST['mov_bgcolor']);

    $mp3_autostart          = COM_applyFilter($_POST['mp3_autostart'],true);
    $mp3_enablecontextmenu  = COM_applyFilter($_POST['mp3_enablecontextmenu'],true);
    $mp3_showstatusbar      = COM_applyFilter($_POST['mp3_showstatusbar'],true);
    $mp3_loop               = COM_applyFilter($_POST['mp3_loop'],true);
    $mp3_uimode             = COM_applyFilter($_POST['mp3_uimode']);

    $swf_play               = COM_applyFilter($_POST['swf_play'],true);
    $swf_menu               = COM_applyFilter($_POST['swf_menu'],true);
    $swf_loop               = COM_applyFilter($_POST['swf_loop'],true);
    $swf_quality            = COM_applyFilter($_POST['swf_quality']);
    $swf_scale              = COM_applyFilter($_POST['swf_scale']);
    $swf_wmode              = COM_applyFilter($_POST['swf_wmode']);
    $swf_asa                = COM_applyFilter($_POST['swf_allowscriptaccess']);
    $swf_flashvars          = COM_applyFilter($_POST['swf_flashvars']);
    $swf_version            = COM_applyFilter($_POST['swf_version'],true);
    $swf_height             = COM_applyFilter($_POST['swf_height'],true);
    $swf_width              = COM_applyFilter($_POST['swf_width'],true);
    $swf_bgcolor            = COM_applyFilter($_POST['swf_bgcolor']);

    // put any error checking / validation here
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_autostart','$asf_autostart'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_enablecontextmenu','$asf_enablecontextmenu'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_stretchtofit','$asf_stretchtofit'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_showstatusbar','$asf_showstatusbar'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_uimode','$asf_uimode'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_playcount','$asf_playcount'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_height','$asf_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_width','$asf_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'asf_bgcolor','$asf_bgcolor'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_autoref','$mov_autoref'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'move_autoplay','$mov_autoplay'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_controller','$mov_controller'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_kioskmode','$mov_kioskmode'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_scale','$mov_scale'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_loop','$mov_loop'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_height','$mov_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_width','$mov_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mov_bgcolor','$mov_bgcolor'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mp3_autostart','$mp3_autostart'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mp3_enablecontextmenu','$mp3_enablecontextmenu'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mp3_showstatusbar','$mp3_showstatusbar'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mp3_loop','$mp3_loop'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'mp3_uimode','$mp3_uimode'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_play','$swf_play'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_menu','$swf_menu'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_loop','$swf_loop'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_quality','$swf_quality'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_scale','$swf_scale'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_wmode','$swf_wmode'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_allowscriptaccess','$swf_asa'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_flashvars','$swf_flashvars'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_version','$swf_version'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_height','$swf_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_width','$swf_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'swf_bgcolor','$swf_bgcolor'");

    COM_setMessage(5);
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
    exit;
}

/**
* Main
*/

$display = '';
$mode = '';

if (isset ($_POST['save'])) {
    $mode = 'save';
}
if (isset ($_POST['cancel'])) {
    $mode = 'cancel';
}

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

if ($mode == 'save' && SEC_checkToken() ) {
    $T->set_var(array(
        'admin_body'    => MG_saveAVDefaults(),
        'title'         => $LANG_MG01['av_default_editor'],
    ));
} elseif ($mode == 'cancel') {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_editAVDefaults(),
        'title'         => $LANG_MG01['av_default_editor'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Audio__Video_Defaults',

    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader('menu','');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>