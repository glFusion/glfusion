<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | userprefs.php                                                            |
// |                                                                          |
// | User preferences interface                                               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2011 by the following authors:                        |
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

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

$mode = isset($_REQUEST['mode']) ? COM_applyFilter ($_REQUEST['mode']) : '';
$display = '';

if ($mode == $LANG_MG01['cancel'] ) {
    header("Location: " . $_MG_CONF['site_url'] . '/index.php');
}

if ( $mode == $LANG_MG01['submit'] && !empty($LANG_MG01['submit']) ) {
    $display_rows    = COM_applyFilter($_POST['display_rows'],true);
    $display_columns = COM_applyFilter($_POST['display_columns'],true);
    $mp3_player      = COM_applyFilter($_POST['mp3_player'],true);
    $playback_mode   = COM_applyFilter($_POST['playback_mode'],true);
    $tn_size         = COM_applyFilter($_POST['tn_size'],true);
    $uid             = (int) $_USER['uid'];

    if ( $display_columns < 0 || $display_columns > 5 ) {
        $display_columns = 3;
    }
    if ( $display_rows < 0 || $display_rows > 99 ) {
        $display_rows = 4;
    }

    if ( $_MG_CONF['up_display_rows_enabled'] == 0 ) {
        $display_rows = 0;
    }
    if ( $_MG_CONF['up_display_columns_enabled'] == 0 ) {
        $display_columns = 0;
    }
    if ( $_MG_CONF['up_mp3_player_enabled'] == 0 ) {
        $mp3_player = -1;
    }
    if ( $_MG_CONF['up_av_playback_enabled'] == 0 ) {
        $playback_mode = -1;
    }
    if ( $_MG_CONF['up_thumbnail_size_enabled'] == 0 ) {
        $tn_size = -1;
    }

    DB_save($_TABLES['mg_userprefs'],'uid,display_rows,display_columns,mp3_player,playback_mode,tn_size',"$uid,$display_rows,$display_columns,$mp3_player,$playback_mode,$tn_size");

    header("Location: " . $_MG_CONF['site_url'] . '/index.php');
    exit;

} else {

    $x = 0;
    $display = MG_siteHeader();

    // let's see if anything is actually set...
    if ( !isset($_MG_USERPREFS['mp3_player']) ) {
        $_MG_USERPREFS['mp3_player']    = -1;
        $_MG_USERPREFS['playback_mode'] = 1;
        $_MG_USERPREFS['tn_size']       = -1;
        $_MG_USERPREFS['display_rows']  = 0;
        $_MG_USERPREFS['display_columns'] = 0;
    }

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file ('admin','userprefs.thtml');
    $T->set_block('admin', 'prefRow', 'pRow');

    // build select boxes

    $mp3_select  = '<select name="mp3_player">';
    $mp3_select .= '<option value="-1"' . ($_MG_USERPREFS['mp3_player']== -1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['system_default'] . '</option>';
    $mp3_select .= '<option value="0"'  . ($_MG_USERPREFS['mp3_player'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['windows_media_player'] . '</option>';
    $mp3_select .= '<option value="1"'  . ($_MG_USERPREFS['mp3_player'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['quicktime_player'] . '</option>';
    $mp3_select .= '<option value="2"'  . ($_MG_USERPREFS['mp3_player'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['flashplayer'] . '</option>';
    $mp3_select .= '</select>';

    $playback_select  = '<select name="playback_mode">';
    $playback_select .= '<option value="-1"' . ($_MG_USERPREFS['playback_mode'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['system_default'] . '</option>';
    $playback_select .= '<option value="0"' . ($_MG_USERPREFS['playback_mode'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['play_in_popup'] . '</option>';
    $playback_select .= '<option value="2"' . ($_MG_USERPREFS['playback_mode'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['play_inline'] . '</option>';
    $playback_select .= '<option value="3"' . ($_MG_USERPREFS['playback_mode'] == 3 ? ' selected="selected"' : '') . '>' . $LANG_MG01['use_mms'] . '</option>';
    $playback_select .= '</select>';

    $tn_select  = '<select name="tn_size">';
    $tn_select .= '<option value="-1"' . ($_MG_USERPREFS['tn_size'] == -1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['system_default'] . '</option>';
    $tn_select .= '<option value="0"' . ($_MG_USERPREFS['tn_size'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['small'] . '</option>';
    $tn_select .= '<option value="1"' . ($_MG_USERPREFS['tn_size'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['medium'] . '</option>';
    $tn_select .= '<option value="2"' . ($_MG_USERPREFS['tn_size'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['large'] . '</option>';
    $tn_select .= '</select>';

    if ( $_MG_CONF['up_display_rows_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['display_rows_prompt'],
            'input_field'   => '<input type="text" size="3" name="display_rows" value="' . $_MG_USERPREFS['display_rows'] . '"/>',
            'lang_help'     => $LANG_MG01['display_rows_help'],
            'rowcounter' => $x++ % 2,
        ));
        $T->parse('pRow', 'prefRow',true);
    }
    if ( $_MG_CONF['up_display_columns_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['display_columns_prompt'],
            'input_field'   => '<input type="text" size="3" name="display_columns" value="' . $_MG_USERPREFS['display_columns'] . '"/>',
            'lang_help'     => $LANG_MG01['display_columns_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
    }
    if ( $_MG_CONF['up_mp3_player_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['mp3_player'],
            'input_field'   => $mp3_select,
            'lang_help'     => $LANG_MG01['mp3_player_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
    }
    if ( $_MG_CONF['up_av_playback_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['av_play_options'],
            'input_field'   => $playback_select,
            'lang_help'     => $LANG_MG01['av_play_options_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
    }
    if ( $_MG_CONF['up_thumbnail_size_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['tn_size'],
            'input_field'   => $tn_select,
            'lang_help'     => $LANG_MG01['tn_size_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
    }

    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var(array(
        'site_url'              => $_CONF['site_url'],
        's_form_action'         => $_MG_CONF['site_url'] . '/userprefs.php',
        'lang_user_prefs'       => $LANG_MG01['user_prefs_title'],
        'lang_submit'           => $LANG_MG01['submit'],
        'lang_cancel'           => $LANG_MG01['cancel'],
    ));
    $T->parse('output', 'admin');
    $display .= $T->finish($T->get_var('output'));

    $display .= MG_siteFooter();
    echo $display;
}
?>