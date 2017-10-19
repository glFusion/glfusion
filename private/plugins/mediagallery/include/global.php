<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | global.php                                                               |
// |                                                                          |
// | Global album edit/perm administration routines                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}
require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';

/**
* Global album attribute editor
*
* @return   string              HTML
*
**/
function MG_globalAlbumPermEditor($adminMenu=0) {
    global $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_ACCESS;

    $retval = '';

    if (!SEC_hasRights('mediagallery.admin')) {
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_admin_block', 'header'));
        $T = new Template($_MG_CONF['template_path']);
        $T->set_file('admin','error.thtml');
        $T->set_var('site_url', $_CONF['site_url']);
        $T->set_var('site_admin_url', $_CONF['site_admin_url']);
        $T->set_var('errormessage',$LANG_MG00['access_denied_msg']);
        $T->parse('output', 'admin');
        $display .= $T->finish($T->get_var('output'));
        $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
        $display .= MG_siteFooter();
        return $display;
    }

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $A['moderate']          = 0;
    $A['member_uploads']    = 0;
    $A['email_mod']         = 0;

    // If edit, pull up the existing album information...

    $T->set_file(array(
        'admin' =>  'global_album_perm.thtml'
    ));

    $A['group_id'] = '';
    $A['mod_group_id']  = '';

    $usergroups = SEC_getUserGroups();
    for ($i = 0; $i < count($usergroups); $i++) {
        if ('mediagallery Admin' == key($usergroups)) {
            $A['group_id'] = $usergroups[key($usergroups)];
            $A['mod_group_id'] = $A['group_id'];
        }
        next($usergroups);
    }
    $A['perm_owner'] = 3;
    $A['perm_group'] = 3;
    $A['perm_members'] = 2;
    $A['perm_anon'] = 2;

    $usergroups = SEC_getUserGroups();
    $groupdd = '';
    $moddd = '';

    $groupdd .= '<select name="group_id">';
    $moddd .= '<select name="mod_id">';
    for ($i = 0; $i < count($usergroups); $i++) {
        if ( $usergroups[key($usergroups)] != 2 && $usergroups[key($usergroups)] != 13 ) {
            $groupdd .= '<option value="' . $usergroups[key($usergroups)] . '"';
            $moddd   .= '<option value="' . $usergroups[key($usergroups)] . '"';
            if ($A['group_id'] == $usergroups[key($usergroups)]) {
                $groupdd .= ' selected="selected"';
                $groupname = key($usergroups);
            }
            if ($A['mod_group_id'] == $usergroups[key($usergroups)]) {
                $moddd   .= ' selected="selected"';
            }
            $groupdd .= '>' . key($usergroups) . '</option>';
            $moddd   .= '>' . key($usergroups) . '</option>';
        }
        next($usergroups);
    }
    $groupdd .= '</select>';
    $moddd .= '</select>';

    $T->set_var(array(
        'action'                => 'globalperm',
        'permissions_editor'    => SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']),
        'permissions_msg'       => $LANG_ACCESS['permmsg'],
        'group_select'          => $groupdd,
        'mod_group_select'      => $moddd,
        'admin_menu'            => $adminMenu,
        'lang_save'             => $LANG_MG01['save'],
        's_form_action'         => $_MG_CONF['site_url'] . '/admin.php',
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_global_perm_help' => $LANG_MG01['global_perm_help'],
        'lang_value'            => $LANG_MG01['value'],
        'lang_attribute'        => $LANG_MG01['attribute'],
        'lang_update'           => $LANG_MG01['update'],
        'lang_group'            => $LANG_ACCESS['group'],
        'lang_permissions'      => $LANG_ACCESS['permissions'],
        'lang_perm_key'         => $LANG_ACCESS['permissionskey'],
        'lang_member_upload'    => $LANG_MG01['member_upload'],
        'lang_moderate_album'   => $LANG_MG01['mod_album'],
        'lang_mod_group'        => $LANG_MG01['moderation_group'],
        'lang_email_mods_on_submission' => $LANG_MG01['email_mods_on_submission']
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
* Saves the global configuration to all albums
*
* @return   string              HTML
*
*/
function MG_saveGlobalAlbumPerm() {
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST;

    if (!SEC_hasRights('mediagallery.admin')) {
        COM_errorLog("Media Gallery user attempted to edit global album attributes without proper accss.");
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $A['group_id']          = isset($_POST['group_id']) ? COM_applyFilter($_POST['group_id'],true) : 0;
    $A['member_uploads']    = isset($_POST['member_upload']) ? COM_applyFilter($_POST['member_upload'],true) : 0;
    $A['moderate']          = isset($_POST['moderation']) ? COM_applyFilter($_POST['moderation'],true) : 0;
    $A['mod_group_id']      = isset($_POST['mod_id']) ? COM_applyFilter($_POST['mod_id'],true) : 0;
    $A['email_mod']         = isset($_POST['email_mod']) ? COM_applyFilter($_POST['email_mod'],true) : 0;
    $adminMenu              = isset($_POST['admin_menu']) ? COM_applyFilter($_POST['admin_menu'],true) : 0;

    $perm_owner     = isset($_POST['perm_owner']) ? $_POST['perm_owner'] : '';
    $perm_group     = isset($_POST['perm_group']) ? $_POST['perm_group'] : '';
    $perm_members   = isset($_POST['perm_members']) ? $_POST['perm_members'] : '';
    $perm_anon      = isset($_POST['perm_anon']) ? $_POST['perm_anon'] : '';
    $group_id       = isset($_POST['group_id']) ? $_POST['group_id'] : '';

    // Convert array values to numeric permission values
    list($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);

    $group_active           = isset($_POST['group_active']) ? COM_applyFilter($_POST['group_active'],true) : 0;
    $perm_active            = isset($_POST['perm_active']) ? COM_applyFilter($_POST['perm_active'],true) : 0;
    $upload_active          = isset($_POST['upload_active']) ? COM_applyFilter($_POST['upload_active'],true) : 0;
    $moderate_active        = isset($_POST['moderate_active']) ? COM_applyFilter($_POST['moderate_active'],true) : 0;
    $mod_group_active       = isset($_POST['mod_group_active']) ? COM_applyFilter($_POST['mod_group_active'],true) : 0;
    $email_mod_active       = isset($_POST['email_mod_active']) ? COM_applyFilter($_POST['email_mod_active'],true) : 0;

    $updateSQL = '';
    $updateSQL .= ($group_active     ? "group_id=$group_id" : '');
    $updateSQL .= ($perm_active      ? ($updateSQL != '' ? ',' : '') . "perm_owner={$A['perm_owner']},perm_group={$A['perm_group']},perm_members={$A['perm_members']},perm_anon={$A['perm_anon']}" : '');
    $updateSQL .= ($upload_active    ? ($updateSQL != '' ? ',' : '') . "member_uploads={$A['member_uploads']}" : '');
    $updateSQL .= ($moderate_active  ? ($updateSQL != '' ? ',' : '') . "moderate={$A['moderate']}" : '');
    $updateSQL .= ($mod_group_active ? ($updateSQL != '' ? ',' : '') . "mod_group_id={$A['mod_group_id']}" : '');
    $updateSQL .= ($email_mod_active ? ($updateSQL != '' ? ',' : '') . "email_mod={$A['email_mod']}" : '');

    if ($updateSQL != '' ) {
        $sql = "UPDATE {$_TABLES['mg_albums']} SET " . $updateSQL;
        DB_query( $sql );
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
        MG_buildFullRSS( );
        MG_GlobalrebuildAllAlbumsRSS( 0 );
    }



    if ($adminMenu == 1 ) {
        echo COM_refresh($_MG_CONF['admin_url'] . '/index.php?msg=10');
    } else {
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
    }
    exit;
}


/**
* Global album attribute editor
*
* @return   string              HTML
*
**/
function MG_globalAlbumAttributeEditor($adminMenu=0) {
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $REMOTE_ADDR;
    global $MG_albums, $album_jumpbox;

    $retval = '';
    $valid_albums = 0;

    if (!SEC_hasRights('mediagallery.admin')) {
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_admin_block', 'header'));
        $T = new Template($_MG_CONF['template_path']);
        $T->set_file('admin','error.thtml');
        $T->set_var('site_url', $_CONF['site_url']);
        $T->set_var('site_admin_url', $_CONF['site_admin_url']);
        $T->set_var('errormessage',$LANG_MG00['access_denied_msg']);
        $T->parse('output', 'admin');
        $display .= $T->finish($T->get_var('output'));
        $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
        $display .= MG_siteFooter();
        return $display;
    }

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $A['enable_slideshow']   = 0;
    $A['enable_random']      = 0;
    $A['enable_shutterfly']  = 0;
    $A['enable_views']       = 0;
    $A['enable_keywords']    = 0;
    $A['enable_html']        = 0;
    $A['enable_sort']        = 0;
    $A['enable_rating']      = 0;
    $A['albums_first']       = 0;
    $A['tn_size']            = 1;
    $A['display_rows']       = 3;
    $A['display_columns']    = 3;
    $A['full_display']       = 0;
    $A['enable_album_views'] = 0;
    $A['allow_download']     = 0;
    $A['display_album_desc'] = 0;
    $A['filename_title']     = 0;
    $A['podcast']            = 0;
    $A['mp3ribbon']          = 0;
    $A['rsschildren']        = 1;
    $A['skin']               = '';

    $retval .= COM_startBlock ($LANG_MG01['global_attr_editor'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $T->set_file(array(
        'admin' =>  'global_album_attr.thtml'
    ));

    // build exif select box...

    $exif_select  = '<select name="enable_exif">';
    $exif_select .= '<option value="0">' . $LANG_MG01['disable_exif'] . '</option>';
    $exif_select .= '<option value="1">' . $LANG_MG01['display_below_media']  . '</option>';
    $exif_select .= '<option value="2">' . $LANG_MG01['display_in_popup'] . '</option>';
    $exif_select .= '<option value="3">' . $LANG_MG01['both'] . '</option>';
    $exif_select .= '</select>';

    $full_select  = '<select name="full_display">';
    $full_select .= '<option value="0"' . ($A['full_display']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['always'] . '</option>';
    $full_select .= '<option value="1"' . ($A['full_display']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['members_only']  . '</option>';
    $full_select .= '<option value="2"' . ($A['full_display']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $full_select .= '</select>';

    $ranking_select = '<select name="enable_rating">';
    $ranking_select .= '<option value="0"' . ($A['enable_rating']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $ranking_select .= '<option value="1"' . ($A['enable_rating']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['members_only'] . '</option>';
    $ranking_select .= '<option value="2"' . ($A['enable_rating']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['always'] . '</option>';
    $ranking_select .= '</select>';

    $podcast_select = '<input type="checkbox" name="podcast" value="1" ' . ($A['podcast'] ? ' checked="checked"' : '') . '/>';
    $mp3ribbon_select = '<input type="checkbox" name="mp3ribbon" value="1" ' . ($A['mp3ribbon'] ? ' checked="checked"' : '') . '/>';
    $rsschildren_select = '<input type="checkbox" name="rsschildren" value="1" ' . ($A['rsschildren'] ? ' checked="checked"' : '') . '/>';

    $filename_title_select = '<input type="checkbox" name="filename_title" value="1" />';

    $comment_select = '<input type="checkbox" name="enable_comments" value="1" />';
    $ss_select		= '<select name="enable_slideshow">';
    $ss_select		.= '<option value="0">' . $LANG_MG01['disabled'] . '</option>';
    $ss_select		.= '<option value="1">' . $LANG_MG01['js_slideshow'] . '</option>';
    $ss_select		.= '<option value="2">' . $LANG_MG01['lightbox'] . '</option>';
    $ss_select      .= '</select>';

    $ri_select      = '<input type="checkbox" name="enable_random" value="1" />';
//    $sf_select      = '<input type="checkbox" name="enable_shutterfly" value="1" />';
    $views_select   = '<input type="checkbox" name="enable_views" value="1" />';
    $keywords_select = '<input type="checkbox" name="enable_keywords" value="1" />';
    $html_select = '<input type="checkbox" name="enable_html" value="1" />';
    $sort_select    = '<input type="checkbox" name="enable_sort" value="1" />';
    $afirst_select  = '<input type="checkbox" name="albums_first" value="1" />';
    $album_views_select   = '<input type="checkbox" name="enable_album_views" value="1" />';

    $tn_size_select  = '<select name="tn_size">';
    $tn_size_select .= '<option value="0">' . $LANG_MG01['small'] . '</option>';
    $tn_size_select .= '<option value="1">' . $LANG_MG01['medium'] . '</option>';
    $tn_size_select .= '<option value="2">' . $LANG_MG01['large'] . '</option>';
    $tn_size_select .= '<option value="3">' . $LANG_MG01['custom'] . '</option>';
    $tn_size_select .= '<option value="4">' . $LANG_MG01['square'] . '</option>';
    $tn_size_select .= '</select>';

    $tnheight_input = '<input type="text" size="3" name="tnheight" value="" />';
    $tnwidth_input  = '<input type="text" size="3" name="tnwidth" value="" />';

    $display_image_size_select  = '<select name="display_image_size">';
    $display_image_size_select .= '<option value="0">' . $LANG_MG01['size_500x375'] . '</option>';
    $display_image_size_select .= '<option value="1">' . $LANG_MG01['size_600x450'] . '</option>';
    $display_image_size_select .= '<option value="2">' . $LANG_MG01['size_620x465'] . '</option>';
    $display_image_size_select .= '<option value="3">' . $LANG_MG01['size_720x540'] . '</option>';
    $display_image_size_select .= '<option value="4">' . $LANG_MG01['size_800x600'] . '</option>';
    $display_image_size_select .= '<option value="5">' . $LANG_MG01['size_912x684'] . '</option>';
    $display_image_size_select .= '<option value="6">' . $LANG_MG01['size_1024x768'] . '</option>';
    $display_image_size_select .= '<option value="7">' . $LANG_MG01['size_1152x864'] . '</option>';
    $display_image_size_select .= '<option value="8">' . $LANG_MG01['size_1280x1024'] . '</option>';
    $display_image_size_select .= '<option value="9">' . $LANG_MG01['size_custom'] . $_MG_CONF['custom_image_width'] . 'x' . $_MG_CONF['custom_image_height'] . '</option>';
    $display_image_size_select .= '</select>';

    $max_image_height_input = '<input type="text" size="4" name="max_image_height" value="0"' . '/>';
    $max_image_width_input  = '<input type="text" size="4" name="max_image_width" value="0" />';
    $max_filesize_input     = '<input type="text" size="10" name="max_filesize" value="0" />';

    $rows_input      = '<input type="text" size="3" name="display_rows" value="' . $_MG_CONF['display_rows'] . '" />';
    $columns_input   = '<input type="text" size="3" name="display_columns" value="' . $_MG_CONF['display_columns'] . '" />';

    $playback_type  = '<select name="playback_type">';
    $playback_type .= '<option value="0">' . $LANG_MG01['play_in_popup'] . '</option>';
    $playback_type .= '<option value="1">' . $LANG_MG01['download_to_local'] . '</option>';
    $playback_type .= '<option value="2">' . $LANG_MG01['play_inline'] . '</option>';
    $playback_type .= '<option value="3">' . $LANG_MG01['use_mms'] . '</option>';
    $playback_type .= '</select>';

    $rss_select     = '<input type="checkbox" name="enable_rss" value="1" />';
    $display_album_desc_select     = '<input type="checkbox" name="display_album_desc" value="1" />';

    $postcard_select  = '<select name="enable_postcard">';
    $postcard_select .= '<option value="0">' . $LANG_MG01['disabled'] . '</option>';
    $postcard_select .= '<option value="1">' . $LANG_MG01['members_only']  . '</option>';
    $postcard_select .= '<option value="2">' . $LANG_MG01['all_users'] . '</option>';
    $postcard_select .= '</select>';

    $allow_download_select     = '<input type="checkbox" name="allow_download" value="1" />';

    // build album list for starting point...

    $album_jumpbox  = '<select name="startaid">';
    $album_jumpbox .= '<option value="0">------</option>';
    $valid_albums  += $MG_albums[0]->buildJumpBox(0,3);
    $album_jumpbox .= '</select>';

    $frames = new mgFrame();
    $skins = array();
    $skins = $frames->getFrames();

    $skin_select = '<select name="skin">';
    $askin_select = '<select name="askin">';
    $dskin_select = '<select name="dskin">';
    for ( $i=0; $i < count($skins); $i++ ) {
        $skin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['ad_image_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $askin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['ad_album_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $dskin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['ad_display_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
    }
    $skin_select .= '</select>';
    $askin_select .= '</select>';
    $dskin_select .= '</select>';

    $themes = MG_getThemes();
    $album_theme_select = '<select name="album_theme">';
    for ( $i = 0; $i < count($themes); $i++ ) {
    	$album_theme_select .= '<option value="' . $themes[$i] . '"' . ($A['skin'] == $themes[$i] ? 'selected="selected"' : '') . '>' . $themes[$i] . '</option>';
    }
    $album_theme_select .= '</select>';

    $T->set_var(array(
        'action'                => 'globalattr',
        'album_list'            => $album_jumpbox,
        'display_image_size_select' => $display_image_size_select,
        'max_image_height_input' => $max_image_height_input,
        'max_image_width_input' => $max_image_width_input,
        'max_filesize_input'    => $max_filesize_input,
        'comment_select'        => $comment_select,
        'exif_select'           => $exif_select,
        'ranking_select'        => $ranking_select,
        'podcast_select'		=> $podcast_select,
        'mp3ribbon_select'      => $mp3ribbon_select,
        'rsschildren_select'    => $rsschildren_select,
        'ss_select'             => $ss_select,
        'full_select'           => $full_select,
        'ri_select'             => $ri_select,
//        'sf_select'             => $sf_select,
        'rss_select'            => $rss_select,
        'postcard_select'       => $postcard_select,
        'views_select'          => $views_select,
        'keywords_select'       => $keywords_select,
        'html_select'           => $html_select,
        'album_theme_select'    => $album_theme_select,
        'display_album_desc_select' => $display_album_desc_select,
        'album_views_select'    => $album_views_select,
        'sort_select'           => $sort_select,
        'afirst_select'         => $afirst_select,
        'tn_size_select'        => $tn_size_select,
        'tnheight_input'		=> $tnheight_input,
        'tnwidth_input'			=> $tnwidth_input,
        'rows_input'            => $rows_input,
        'height_input'          => $max_image_height_input,
        'width_input'           => $max_image_width_input,
        'max_size_input'        => $max_filesize_input,
        'columns_input'         => $columns_input,
        'playback_type'         => $playback_type,
        'admin_menu'            => $adminMenu,
        'allow_download_select' => $allow_download_select,
        'filename_title_select' => $filename_title_select,
        'skin_select'           => $skin_select,
        'askin_select'          => $askin_select,
        'dskin_select'          => $dskin_select,
        'lang_image_skin'       => $LANG_MG01['image_skin'],
        'lang_album_skin'       => $LANG_MG01['album_skin'],
        'lang_display_skin'     => $LANG_MG01['display_skin'],
        'lang_jpg'              => $LANG_MG01['jpg'],
        'lang_png'              => $LANG_MG01['png'],
        'lang_tif'              => $LANG_MG01['tif'],
        'lang_gif'              => $LANG_MG01['gif'],
        'lang_bmp'              => $LANG_MG01['bmp'],
        'lang_tga'              => $LANG_MG01['tga'],
        'lang_psd'              => $LANG_MG01['psd'],
        'lang_mp3'              => $LANG_MG01['mp3'],
        'lang_ogg'              => $LANG_MG01['ogg'],
        'lang_asf'              => $LANG_MG01['asf'],
        'lang_swf'              => $LANG_MG01['swf'],
        'lang_mov'              => $LANG_MG01['mov'],
        'lang_mp4'              => $LANG_MG01['mp4'],
        'lang_mpg'              => $LANG_MG01['mpg'],
        'lang_zip'              => $LANG_MG01['zip'],
        'lang_flv'              => $LANG_MG01['flv'],
        'lang_rflv'             => $LANG_MG01['rflv'],
        'lang_emb'              => $LANG_MG01['emb'],
        'lang_other'            => $LANG_MG01['other'],
        'lang_allowed_formats'  => $LANG_MG01['allowed_media_formats'],
        'lang_image'            => $LANG_MG01['image'],
        'lang_audio'            => $LANG_MG01['audio'],
        'lang_video'            => $LANG_MG01['video'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_edit_title'       => $LANG_MG01['edit_album'],
        's_form_action'         => $_MG_CONF['site_url'] . '/admin.php',
        'lang_album_edit_help'  => $LANG_MG01['album_edit_help'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_comments'         => $LANG_MG01['comments_prompt'],
        'lang_enable_exif'      => $LANG_MG01['enable_exif'],
        'lang_enable_ratings'   => $LANG_MG01['enable_ratings'],
        'lang_ss_enable'        => $LANG_MG01['ss_enable'],
        'lang_ri_enable'        => $LANG_MG01['ri_enable'],
        'lang_sf_enable'        => $LANG_MG01['sf_enable'],
        'lang_tn_size'          => $LANG_MG01['tn_size'],
        'lang_rows'             => $LANG_MG01['rows'],
        'lang_columns'          => $LANG_MG01['columns'],
        'lang_av_play_album'    => $LANG_MG01['av_play_album'],
        'lang_av_play_options'  => $LANG_MG01['av_play_options'],
        'lang_album_attributes' => $LANG_MG01['album_attributes'],
        'lang_global_attr_help' => $LANG_MG01['global_attr_help'],
        'lang_value'            => $LANG_MG01['value'],
        'lang_attribute'        => $LANG_MG01['attribute'],
        'lang_update'           => $LANG_MG01['update'],
        'lang_enable_views'     => $LANG_MG01['enable_views'],
        'lang_enable_keywords'  => $LANG_MG01['enable_keywords'],
        'lang_enable_html'      => $LANG_MG01['htmlallowed'],
        'lang_enable_album_views' => $LANG_MG01['enable_album_views'],
        'lang_enable_sort'      => $LANG_MG01['enable_sort'],
        'lang_albums_first'     => $LANG_MG01['albums_first'],
        'lang_full_display'     => $LANG_MG01['full_display'],
        'lang_max_image_height' => $LANG_MG01['max_image_height'],
        'lang_max_image_width'  => $LANG_MG01['max_image_width'],
        'lang_max_filesize'     => $LANG_MG01['max_filesize'],
        'lang_display_image_size' => $LANG_MG01['display_image_size'],
        'lang_starting_album'   => $LANG_MG01['starting_album'],
        'lang_enable_rss'       => $LANG_MG01['enable_rss'],
        'lang_enable_postcard'  => $LANG_MG01['enable_postcard'],
        'lang_allow_download'   => $LANG_MG01['allow_download'],
        'lang_display_album_desc' => $LANG_MG01['display_album_desc'],
        'lang_filename_title'   => $LANG_MG01['filename_title'],
        'lang_theme_select'		=> $LANG_MG01['album_theme'],
        'lang_podcast'			=> $LANG_MG01['podcast'],
        'lang_mp3ribbon'        => $LANG_MG01['mp3ribbon'],
        'lang_rsschildren'      => $LANG_MG01['rsschildren'],
        'lang_tnheight'			=> $LANG_MG01['tn_height'],
        'lang_tnwidth'			=> $LANG_MG01['tn_width'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}


function MG_saveGlobalAlbumAttrChildren($aid, $sql) {
    global $_TABLES, $MG_albums;

    $sqltmp = "UPDATE {$_TABLES['mg_albums']} SET " . $sql . " WHERE album_id=" . $aid;
    DB_query( $sqltmp );

    if ( !empty($MG_albums[$aid]->children)) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            MG_saveGlobalAlbumAttrChildren($MG_albums[$child]->id,$sql);
        }
    }
}


/**
* Saves the global configuration to all albums
*
* @return   string              HTML
*
*/
function MG_saveGlobalAlbumAttr() {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST;

    if (!SEC_hasRights('mediagallery.admin')) {
        COM_errorLog("Media Gallery user attempted to edit global album attributes without proper accss.");
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $startaid = COM_applyFilter($_POST['startaid'],true);

    $A['enable_comments']   = isset($_POST['enable_comments']) ? COM_applyFilter($_POST['enable_comments'],true) : 0;
    $A['exif_display']      = isset($_POST['enable_exif']) ? COM_applyFilter($_POST['enable_exif'],true) : 0;
    $A['enable_rating']     = isset($_POST['enable_rating']) ? COM_applyFilter($_POST['enable_rating'],true) : 0;
    $A['rsschildren']       = isset($_POST['rsschildren']) ? COM_applyFilter($_POST['rsschildren'],true) : 0;
    $A['podcast']           = isset($_POST['podcast']) ? COM_applyFilter($_POST['podcast'],true) : 0;
    $A['mp3ribbon']         = isset($_POST['mp3ribbon']) ? COM_applyFilter($_POST['mp3ribbon'],true) : 0;
    $A['playback_type']     = isset($_POST['playback_type']) ? COM_applyFilter($_POST['playback_type'],true) : 0;
    $A['enable_slideshow']  = isset($_POST['enable_slideshow']) ? COM_applyFilter($_POST['enable_slideshow'],true) : 0;
    $A['enable_random']     = isset($_POST['enable_random']) ? COM_applyFilter($_POST['enable_random'],true) : 0;
    $A['enable_shutterfly'] = isset($_POST['enable_shutterfly']) ? COM_applyFilter($_POST['enable_shutterfly'],true) : 0;
    $A['enable_views']      = isset($_POST['enable_views']) ? COM_applyFilter($_POST['enable_views'],true) : 0;
    $A['enable_keywords']   = isset($_POST['enable_keywords']) ? COM_applyFilter($_POST['enable_keywords'],true) : 0;
    $A['enable_html']       = isset($_POST['enable_html']) ? COM_applyFilter($_POST['enable_html'],true) : 0;
    $A['enable_sort']       = isset($_POST['enable_sort']) ? COM_applyFilter($_POST['enable_sort'],true) : 0;
    $A['albums_first']      = isset($_POST['albums_first']) ? COM_applyFilter($_POST['albums_first'],true) : 0;
    $A['tn_size']           = isset($_POST['tn_size']) ? COM_applyFilter($_POST['tn_size'],true) : 0;
    $A['tn_height']         = isset($_POST['tnheight']) ? COM_applyFilter($_POST['tnheight'],true) : 200;
    $A['tn_width']          = isset($_POST['tnwidth']) ? COM_applyFilter($_POST['tnwidth'],true) : 200;
    if ( $A['tn_height'] == 0 ) {
        $A['tn_height'] = 200;
    }
    if ( $A['tn_width'] == 0 ) {
        $A['tn_width'] = 200;
    }
    $A['display_rows']      = isset($_POST['display_rows']) ? COM_applyFilter($_POST['display_rows'],true) : 0;
    $A['display_columns']   = isset($_POST['display_columns']) ? COM_applyFilter($_POST['display_columns'],true) : 0;
    $A['full_display']      = isset($_POST['full_display']) ? COM_applyFilter($_POST['full_display'],true) : 0;
    $A['max_image_height']  = isset($_POST['max_image_height']) ? COM_applyFilter($_POST['max_image_height'],true) : 0;
    $A['max_image_width']   = isset($_POST['max_image_width']) ? COM_applyFilter($_POST['max_image_width'],true) : 0;
    $A['max_filesize']      = isset($_POST['max_filesize']) ? COM_applyFilter($_POST['max_filesize'],true) : 0;
    $A['display_image_size'] = isset($_POST['display_image_size']) ? COM_applyFilter($_POST['display_image_size'],true) : 0;
    $A['enable_album_views'] = isset($_POST['enable_album_views']) ? COM_applyFilter($_POST['enable_album_views'],true) : 0;
    $A['enable_rss']         = isset($_POST['enable_rss']) ? COM_applyFilter($_POST['enable_rss'],true) : 0;
    $A['enable_postcard']    = isset($_POST['enable_postcard']) ? COM_applyFilter($_POST['enable_postcard'],true) : 0;
    $A['allow_download']     = isset($_POST['allow_download']) ? COM_applyFilter($_POST['allow_download'],true) : 0;
    $A['display_album_desc'] = isset($_POST['display_album_desc']) ? COM_applyFilter($_POST['display_album_desc'],true) : 0;
    $A['filename_title']     = isset($_POST['filename_title']) ? COM_applyFilter($_POST['filename_title'],true) : 0;
    $A['image_skin']         = COM_applyFilter($_POST['skin']);
    $A['album_skin']         = COM_applyFilter($_POST['askin']);
    $A['display_skin']       = COM_applyFilter($_POST['dskin']);
    $A['skin']               = COM_applyFilter($_POST['album_theme']);
    // valid media formats....
    $format_jpg                 = isset($_POST['format_jpg']) ? COM_applyFilter($_POST['format_jpg'],true) : 0;
    $format_png                 = isset($_POST['format_png']) ? COM_applyFilter($_POST['format_png'],true) : 0;
    $format_tif                 = isset($_POST['format_tif']) ? COM_applyFilter($_POST['format_tif'],true) : 0;
    $format_gif                 = isset($_POST['format_gif']) ? COM_applyFilter($_POST['format_gif'],true) : 0;
    $format_bmp                 = isset($_POST['format_bmp']) ? COM_applyFilter($_POST['format_bmp'],true) : 0;
    $format_tga                 = isset($_POST['format_tga']) ? COM_applyFilter($_POST['format_tga'],true) : 0;
    $format_psd                 = isset($_POST['format_psd']) ? COM_applyFilter($_POST['format_psd'],true) : 0;
    $format_mp3                 = isset($_POST['format_mp3']) ? COM_applyFilter($_POST['format_mp3'],true) : 0;
    $format_ogg                 = isset($_POST['format_ogg']) ? COM_applyFilter($_POST['format_ogg'],true) : 0;
    $format_asf                 = isset($_POST['format_asf']) ? COM_applyFilter($_POST['format_asf'],true) : 0;
    $format_swf                 = isset($_POST['format_swf']) ? COM_applyFilter($_POST['format_swf'],true) : 0;
    $format_mov                 = isset($_POST['format_mov']) ? COM_applyFilter($_POST['format_mov'],true) : 0;
    $format_mp4                 = isset($_POST['format_mp4']) ? COM_applyFilter($_POST['format_mp4'],true) : 0;
    $format_mpg                 = isset($_POST['format_mpg']) ? COM_applyFilter($_POST['format_mpg'],true) : 0;
    $format_zip                 = isset($_POST['format_zip']) ? COM_applyFilter($_POST['format_zip'],true) : 0;
    $format_other               = isset($_POST['format_other']) ? COM_applyFilter($_POST['format_other'],true) : 0;
    $format_flv                 = isset($_POST['format_flv']) ? COM_applyFilter($_POST['format_flv'],true) : 0;
    $format_rflv                = isset($_POST['format_rflv']) ? COM_applyFilter($_POST['format_rflv'],true) : 0;
    $format_emb                 = isset($_POST['format_emb']) ? COM_applyFilter($_POST['format_emb'],true) : 0;
    $valid_formats = ($format_jpg + $format_png + $format_tif + $format_gif + $format_bmp + $format_tga + $format_psd + $format_mp3 + $format_ogg + $format_asf + $format_swf + $format_mov + $format_mp4 + $format_mpg + $format_zip + $format_other + $format_flv + $format_rflv + $format_emb);

    $comment_active         = isset($_POST['comment_active']) ? COM_applyFilter($_POST['comment_active'],true) : 0;
    $exif_active            = isset($_POST['exif_active']) ? COM_applyFilter($_POST['exif_active'],true) : 0;
    $rating_active          = isset($_POST['rating_active']) ? COM_applyFilter($_POST['rating_active'],true) : 0;
    $rsschildren_active     = isset($_POST['rsschildren_active']) ? COM_applyFilter($_POST['rsschildren_active'],true) : 0;
    $podcast_active         = isset($_POST['podcast_active']) ? COM_applyFilter($_POST['podcast_active'],true) : 0;
    $mp3ribbon_active       = isset($_POST['mp3ribbon_active']) ? COM_applyFilter($_POST['mp3ribbon_active'],true) : 0;
    $playback_active        = isset($_POST['playback_active']) ? COM_applyFilter($_POST['playback_active'],true) : 0;
    $slideshow_active       = isset($_POST['slideshow_active']) ? COM_applyFilter($_POST['slideshow_active'],true) : 0;
    $random_active          = isset($_POST['random_active']) ? COM_applyFilter($_POST['random_active'],true) : 0;
    $shutterfly_active      = isset($_POST['shutterfly_active']) ? COM_applyFilter($_POST['shutterfly_active'],true) : 0;
    $views_active           = isset($_POST['views_active']) ? COM_applyFilter($_POST['views_active'],true) : 0;
    $keywords_active        = isset($_POST['keywords_active']) ? COM_applyFilter($_POST['keywords_active'],true) : 0;
    $html_active            = isset($_POST['html_active']) ? COM_applyFilter($_POST['html_active'],true) : 0;
    $sort_active            = isset($_POST['sort_active']) ? COM_applyFilter($_POST['sort_active'],true) : 0;
    $afirst_active          = isset($_POST['afirst_active']) ? COM_applyFilter($_POST['afirst_active'],true) : 0;
    $thumbnail_active       = isset($_POST['thumbnail_active']) ? COM_applyFilter($_POST['thumbnail_active'],true) : 0;
    $tnheight_active        = isset($_POST['tnheight_active']) ? COM_applyFilter($_POST['tnheight_active'],true) : 0;
    $tnwidth_active         = isset($_POST['tnwidth_active']) ? COM_applyFilter($_POST['tnwidth_active'],true) : 0;
    $rows_active            = isset($_POST['rows_active']) ? COM_applyFilter($_POST['rows_active'],true) : 0;
    $columns_active         = isset($_POST['columns_active']) ? COM_applyFilter($_POST['columns_active'],true) : 0;
    $full_display_active    = isset($_POST['full_display_active']) ? COM_applyFilter($_POST['full_display_active'],true) : 0;
    $max_image_height_active= isset($_POST['max_image_height_active']) ? COM_applyFilter($_POST['max_image_height_active'],true) : 0;
    $max_image_width_active = isset($_POST['max_image_width_active']) ? COM_applyFilter($_POST['max_image_width_active'],true) : 0;
    $max_filesize_active    = isset($_POST['max_filesize_active']) ? COM_applyFilter($_POST['max_filesize_active'],true) : 0;
    $display_image_size_active = isset($_POST['display_image_size_active']) ? COM_applyFilter($_POST['display_image_size_active'],true) : 0;
    $album_views_active     = isset($_POST['album_views_active']) ? COM_applyFilter($_POST['album_views_active'],true) : 0;
    $enable_rss_active      = isset($_POST['enable_rss_active']) ? COM_applyFilter($_POST['enable_rss_active'],true) : 0;
    $enable_postcard_active = isset($_POST['enable_postcard_active'])? COM_applyFilter($_POST['enable_postcard_active'],true) : 0;
    $allow_download_active  = isset($_POST['allow_download_active']) ? COM_applyFilter($_POST['allow_download_active'],true) : 0;
    $display_album_desc_active = isset($_POST['display_album_desc_active']) ? COM_applyFilter($_POST['display_album_desc_active'],true) : 0;
    $formats_active         = isset($_POST['formats_active']) ? COM_applyFilter($_POST['formats_active'],true) : 0;
    $filename_title_active  = isset($_POST['filename_title_active']) ? COM_applyFIlter($_POST['filename_title_active'],true) : 0;
    $image_skin_active      = isset($_POST['image_skin_active']) ? COM_applyFilter($_POST['image_skin_active'],true) : 0;
    $album_skin_active      = isset($_POST['album_skin_active']) ? COM_applyFilter($_POST['album_skin_active'],true) : 0;
    $display_skin_active    = isset($_POST['display_skin_active']) ? COM_applyFilter($_POST['display_skin_active'],true) : 0;
    $admin_menu             = isset($_POST['admin_menu']) ? COM_applyFilter($_POST['admin_menu'],true) : 0;
    $album_theme_active     = isset($_POST['album_theme_active']) ? COM_applyFilter($_POST['album_theme_active'],true) : 0;

    if ($A['display_rows'] < 1 || $A['display_rows'] > 99 ) {
        $A['display_rows'] = 4;
    }
    if ($A['display_columns'] < 1 || $A['display_columns'] > 9 ) {
        $A['display_columns'] = 3;
    }

    $updateSQL = '';
    $updateSQL .= ($comment_active ? "enable_comments={$A['enable_comments']}" : '');
    $updateSQL .= ($exif_active    ? ($updateSQL != '' ? ',' : '') . "exif_display={$A['exif_display']}" : '');
    $updateSQL .= ($rating_active  ? ($updateSQL != '' ? ',' : '') . "enable_rating={$A['enable_rating']}" : '');
    $updateSQL .= ($rsschildren_active  ? ($updateSQL != '' ? ',' : '') . "rsschildren={$A['rsschildren']}" : '');
    $updateSQL .= ($podcast_active  ? ($updateSQL != '' ? ',' : '') . "podcast={$A['podcast']}" : '');
    $updateSQL .= ($mp3ribbon_active  ? ($updateSQL != '' ? ',' : '') . "mp3ribbon={$A['mp3ribbon']}" : '');
    $updateSQL .= ($playback_active ? ($updateSQL != '' ? ',' : '') . "playback_type={$A['playback_type']}" : '');
    $updateSQL .= ($slideshow_active ? ($updateSQL != '' ? ',' : '') . "enable_slideshow={$A['enable_slideshow']}" : '');
    $updateSQL .= ($random_active ? ($updateSQL != '' ? ',' : '') . "enable_random={$A['enable_random']}" : '');
    $updateSQL .= ($shutterfly_active ? ($updateSQL != '' ? ',' : '') . "enable_shutterfly={$A['enable_shutterfly']}" : '');
    $updateSQL .= ($views_active ? ($updateSQL != '' ? ',' : '') . "enable_views={$A['enable_views']}" : '');
    $updateSQL .= ($html_active ? ($updateSQL != '' ? ',' : '') . "enable_html={$A['enable_html']}" : '');
    $updateSQL .= ($keywords_active ? ($updateSQL != '' ? ',' : '') . "enable_keywords={$A['enable_keywords']}" : '');
    $updateSQL .= ($sort_active ? ($updateSQL != '' ? ',' : '') . "enable_sort={$A['enable_sort']}" : '');
    $updateSQL .= ($afirst_active ? ($updateSQL != '' ? ',' : '') . "albums_first={$A['albums_first']}" : '');
    $updateSQL .= ($thumbnail_active ? ($updateSQL != '' ? ',' : '') . "tn_size={$A['tn_size']}" : '');
    $updateSQL .= ($tnheight_active ? ($updateSQL != '' ? ',' : '') . "tnheight={$A['tn_height']}" : '');
    $updateSQL .= ($tnwidth_active ? ($updateSQL != '' ? ',' : '') . "tnwidth={$A['tn_width']}" : '');
    $updateSQL .= ($rows_active ? ($updateSQL != '' ? ',' : '') . "display_rows={$A['display_rows']}" : '');
    $updateSQL .= ($columns_active ? ($updateSQL != '' ? ',' : '') . "display_columns={$A['display_columns']}" : '');
    $updateSQL .= ($full_display_active ? ($updateSQL != '' ? ',' : '') . "full_display={$A['full_display']}" : '');
    $updateSQL .= ($allow_download_active ? ($updateSQL != '' ? ',' : '') . "allow_download={$A['allow_download']}" : '');
    $updateSQL .= ($display_album_desc_active ? ($updateSQL != '' ? ',' : '') . "display_album_desc={$A['display_album_desc']}" : '');
    $updateSQL .= ($formats_active ? ($updateSQL != '' ? ',' : '') . "valid_formats=$valid_formats" : '');
    $updateSQL .= ($filename_title_active ? ($updateSQL != '' ? ',' : '') . "filename_title={$A['filename_title']}" : '');
    $updateSQL .= ($album_theme_active ? ($updateSQL != '' ? ',' : '') . "skin=\"{$A['skin']}\"" : '');


    $updateSQL .= ($max_image_height_active ? ($updateSQL != '' ? ',' : '') . "max_image_height={$A['max_image_height']}" : '');
    $updateSQL .= ($max_image_width_active ? ($updateSQL != '' ? ',' : '') . "max_image_width={$A['max_image_width']}" : '');
    $updateSQL .= ($max_filesize_active ? ($updateSQL != '' ? ',' : '') . "max_filesize={$A['max_filesize']}" : '');
    $updateSQL .= ($display_image_size_active ? ($updateSQL != '' ? ',' : '') . "display_image_size={$A['display_image_size']}" : '');
    $updateSQL .= ($album_views_active ? ($updateSQL != '' ? ',' : '') . "enable_album_views={$A['enable_album_views']}" : '');

    $updateSQL .= ($enable_rss_active ? ($updateSQL != '' ? ',' : '') . "enable_rss={$A['enable_rss']}" : '');
    $updateSQL .= ($enable_postcard_active ? ($updateSQL != '' ? ',' : '') . "enable_postcard={$A['enable_postcard']}" : '');
    $updateSQL .= ($image_skin_active ? ($updateSQL != '' ? ',' : '') . "image_skin=\"{$A['image_skin']}\"" : '');
    $updateSQL .= ($album_skin_active ? ($updateSQL != '' ? ',' : '') . "album_skin=\"{$A['album_skin']}\"" : '');
    $updateSQL .= ($display_skin_active ? ($updateSQL != '' ? ',' : '') . "display_skin=\"{$A['display_skin']}\"" : '');

    if ($updateSQL != '' ) {
        if ( $startaid == 0 ) {
            $sql = "UPDATE {$_TABLES['mg_albums']} SET " . $updateSQL;
            DB_query( $sql );
            if ( $enable_rss_active ) {
                require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
                MG_buildFullRSS( );
                MG_GlobalrebuildAllAlbumsRSS( 0 );
            }
        } else {
            MG_saveGlobalAlbumAttrChildren($startaid, $updateSQL);
            if ( $enable_rss_active ) {
                require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
                MG_buildFullRSS( );
                MG_GlobalrebuildAllAlbumsRSS( $startaid );
            }
        }
    }

    if ( $admin_menu == 1 ) {
        echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=11');
    } else {
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
    }
    exit;
}

function MG_GlobalrebuildAllAlbumsRSS( $aid ){
    global $MG_albums;

    MG_buildAlbumRSS($aid);
    if ( !empty($MG_albums[$aid]->children)) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            MG_GlobalrebuildAllAlbumsRSS($MG_albums[$child]->id);
        }
    }
}

?>