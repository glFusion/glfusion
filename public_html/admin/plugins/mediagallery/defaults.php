<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Edit Media Gallery Default Settings
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/classFrame.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_editDefaults( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG03, $LANG_ACCESS, $LANG_DIRECTION;
    global $LANG04;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');

    $T->set_file(array(
        'admin'         =>  'editdefaults.thtml',
        'admin_formats' =>  'editalbum_formats.thtml',

    ));

    $T->set_var('site_url', $_MG_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_MG01['album_attributes'],'showhideMGAdminEditorDiv("attributes",0);return false;',true);
    $navbar->add_menuitem($LANG_MG01['watermark'],'showhideMGAdminEditorDiv("watermark",1);return false;',true);
    $navbar->add_menuitem($LANG_MG01['allowed_media_formats'],'showhideMGAdminEditorDiv("media",2);return false;',true);
    $navbar->add_menuitem($LANG_MG01['anonymous_uploads_prompt'],'showhideMGAdminEditorDiv("useruploads",3);return false;',true);
    $navbar->add_menuitem($LANG_ACCESS['accessrights'],'showhideMGAdminEditorDiv("access",4);return false;',true);
    $navbar->set_selected($LANG_MG01['album_attributes']);
    $T->set_var ('navbar', $navbar->generate());
    $T->set_var ('no_javascript_warning',$LANG04[150]);

    // build exif select box...

    $exif_select  = '<select name="enable_exif">';
    $exif_select .= '<option value="0"' . ($_MG_CONF['ad_exif_display']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disable_exif'] . '</option>';
    $exif_select .= '<option value="1"' . ($_MG_CONF['ad_exif_display']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['display_below_media']  . '</option>';
    $exif_select .= '<option value="2"' . ($_MG_CONF['ad_exif_display']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['display_in_popup'] . '</option>';
    $exif_select .= '<option value="3"' . ($_MG_CONF['ad_exif_display']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['both'] . '</option>';
    $exif_select .= '</select>';

    $full_select  = '<select name="full_display"' . ($_MG_CONF['discard_original'] ? ' disabled=disabled ' : '') . '>';
    $full_select .= '<option value="0"' . ($_MG_CONF['ad_full_display']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['always'] . '</option>';
    $full_select .= '<option value="1"' . ($_MG_CONF['ad_full_display']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['members_only']  . '</option>';
    $full_select .= '<option value="2"' . ($_MG_CONF['ad_full_display']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $full_select .= '</select>';

    $ranking_select = '<select name="enable_rating">';
    $ranking_select .= '<option value="0"' . ($_MG_CONF['ad_enable_rating']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $ranking_select .= '<option value="1"' . ($_MG_CONF['ad_enable_rating']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['members_only'] . '</option>';
    $ranking_select .= '<option value="2"' . ($_MG_CONF['ad_enable_rating']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['always'] . '</option>';
    $ranking_select .= '</select>';

    $mp3ribbon_select   = '<input type="checkbox" name="mp3ribbon" value="1" />';
    $rsschildren_select = '<input type="checkbox" name="rsschildren" value="1" />';

    $themes = MG_getThemes();
    $album_theme_select = '<select name="album_theme">';
    for ( $i = 0; $i < count($themes); $i++ ) {
    	$album_theme_select .= '<option value="' . $themes[$i] . '"' . ($_MG_CONF['ad_album_skin'] == $themes[$i] ? 'selected="selected"' : '') . '>' . $themes[$i] . '</option>';
    }
    $album_theme_select .= '</select>';

    $comment_select = '<input type="checkbox" name="enable_comments" value="1" ' . ($_MG_CONF['ad_enable_comments'] ? ' checked="checked"' : '') . '/>';
    $podcast_select = '<input type="checkbox" name="podcast" value="1" />';

    $ss_select      = '<select name="enable_slideshow">';
    $ss_select      .= '<option value="0" ' . ($_MG_CONF['ad_enable_slideshow'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $ss_select      .= '<option value="1"' . ($_MG_CONF['ad_enable_slideshow'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['js_slideshow'] . '</option>';
    $ss_select      .= '<option value="2"' . ($_MG_CONF['ad_enable_slideshow'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['lightbox'] . '</option>';
    $ss_select      .= '<option value="3"' . ($_MG_CONF['ad_enable_slideshow'] == 3 ? ' selected="selected"' : '') . '>' . $LANG_MG01['flash_slideshow_disp'] . '</option>';
    $ss_select      .= '<option value="4"' . ($_MG_CONF['ad_enable_slideshow'] == 4 ? ' selected="selected"' : '') . '>' . $LANG_MG01['flash_slideshow_full'] . '</option>';
    $ss_select      .= '</select>';

    $ri_select      = '<input type="checkbox" name="enable_random" value="1" ' . ($_MG_CONF['ad_enable_random'] ? ' checked="checked"' : '') . '/>';
    $sf_select      = '<input type="checkbox" name="enable_shutterfly" value="1" ' . ($_MG_CONF['ad_enable_shutterfly'] ? ' checked="checked"' : '') . '/>';
    $views_select   = '<input type="checkbox" name="enable_views" value="1" ' . ($_MG_CONF['ad_enable_views'] ? ' checked="checked"' : '') . '/>';
    $keywords_select   = '<input type="checkbox" name="enable_keywords" value="1" ' . ($_MG_CONF['ad_enable_keywords'] ? ' checked="checked"' : '') . '/>';
    $sort_select    = '<input type="checkbox" name="enable_sort" value="1" ' . ($_MG_CONF['ad_enable_sort'] ? ' checked="checked"' : '') . '/>';

    $rss_select     = '<input type="checkbox" name="enable_rss" value="1" ' . ($_MG_CONF['ad_enable_rss'] ? ' checked="checked"' : '') . '/>';

    $postcard_select  = '<select name="enable_postcard">';
    $postcard_select .= '<option value="0"' . ($_MG_CONF['ad_enable_postcard']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $postcard_select .= '<option value="1"' . ($_MG_CONF['ad_enable_postcard']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['members_only']  . '</option>';
    $postcard_select .= '<option value="2"' . ($_MG_CONF['ad_enable_postcard']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['all_users'] . '</option>';
    $postcard_select .= '</select>';

    $afirst_select   = '<input type="checkbox" name="albums_first" value="1" ' . ($_MG_CONF['ad_albums_first'] ? ' checked="checked"' : '') . '/>';
    $album_views_select   = '<input type="checkbox" name="enable_album_views" value="1" ' . ($_MG_CONF['ad_enable_album_views'] ? ' checked="checked"' : '') . '/>';

    $tn_size_select  = '<select name="tn_size">';
    $tn_size_select .= '<option value="0"' . ($_MG_CONF['ad_tn_size']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['small'] . '</option>';
    $tn_size_select .= '<option value="1"' . ($_MG_CONF['ad_tn_size']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['medium'] . '</option>';
    $tn_size_select .= '<option value="2"' . ($_MG_CONF['ad_tn_size']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['large'] . '</option>';
    $tn_size_select .= '<option value="3"' . ($_MG_CONF['ad_tn_size']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['custom'] . '</option>';
    $tn_size_select .= '<option value="4"' . ($_MG_CONF['ad_tn_size']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['square'] . '</option>';
    $tn_size_select .= '</select>';

    $tnheight_input = '<input type="text" size="3" name="tnheight" value="' . $_MG_CONF['ad_tn_height'] . '"' . '/>';
    $tnwidth_input  = '<input type="text" size="3" name="tnwidth" value="' . $_MG_CONF['ad_tn_width'] . '"' . '/>';

    $display_image_size_select  = '<select name="display_image_size">';
    $display_image_size_select .= '<option value="0"' . ($_MG_CONF['ad_display_image_size']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_500x375'] . '</option>';
    $display_image_size_select .= '<option value="1"' . ($_MG_CONF['ad_display_image_size']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_600x450'] . '</option>';
    $display_image_size_select .= '<option value="2"' . ($_MG_CONF['ad_display_image_size']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_620x465'] . '</option>';
    $display_image_size_select .= '<option value="3"' . ($_MG_CONF['ad_display_image_size']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_720x540'] . '</option>';
    $display_image_size_select .= '<option value="4"' . ($_MG_CONF['ad_display_image_size']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_800x600'] . '</option>';
    $display_image_size_select .= '<option value="5"' . ($_MG_CONF['ad_display_image_size']==5 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_912x684'] . '</option>';
    $display_image_size_select .= '<option value="6"' . ($_MG_CONF['ad_display_image_size']==6 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_1024x768'] . '</option>';
    $display_image_size_select .= '<option value="7"' . ($_MG_CONF['ad_display_image_size']==7 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_1152x864'] . '</option>';
    $display_image_size_select .= '<option value="8"' . ($_MG_CONF['ad_display_image_size']==8 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_1280x1024'] . '</option>';
    $display_image_size_select .= '<option value="9"' . ($_MG_CONF['ad_display_image_size']==9 ? 'selected="selected"' : '') . '>' . $LANG_MG01['custom'] . ' - ' . $_MG_CONF['custom_image_width'] . 'x' . $_MG_CONF['custom_image_height'] . '</option>';
    $display_image_size_select .= '</select>';

    $rows_input = '<input type="text" size="3" name="display_rows" value="' . $_MG_CONF['ad_display_rows'] . '"' . '/>';
    $columns_input = '<input type="text" size="3" name="display_columns" value="' . $_MG_CONF['ad_display_columns'] . '"' . '/>';

    $max_image_height_input = '<input type="text" size="4" name="max_image_height" value="' . $_MG_CONF['ad_max_image_height'] . '"' . '/>';
    $max_image_width_input = '<input type="text" size="4" name="max_image_width" value="' . $_MG_CONF['ad_max_image_width'] . '"' . '/>';
    if ($_MG_CONF['ad_max_filesize'] != 0 ) {
        $max_filesize = $_MG_CONF['ad_max_filesize'] / 1024;
    } else {
        $max_filesize = 0;
    }
    $max_filesize_input = '<input type="text" size="10" name="max_filesize" value="' . $max_filesize . '"' . '/>';

    $email_mod_select = '<input type="checkbox" name="email_mod" value="1" ' . ($_MG_CONF['ad_email_mod'] ? ' checked="checked"' : '') . '/>';

    $playback_type  = '<select name="playback_type">';
    $playback_type .= '<option value="0"' . ($_MG_CONF['ad_playback_type']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['play_in_popup'] . '</option>';
    $playback_type .= '<option value="1"' . ($_MG_CONF['ad_playback_type']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['download_to_local'] . '</option>';
    $playback_type .= '<option value="2"' . ($_MG_CONF['ad_playback_type']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['play_inline'] . '</option>';
    $playback_type .= '<option value="3"' . ($_MG_CONF['ad_playback_type']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['use_mms'] . '</option>';
    $playback_type .= '</select>';

    $album_sort_select  = '<select name="album_sort_order">';
    $album_sort_select .= '<option value="0"' . ($_MG_CONF['ad_album_sort_order']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG03['no_sort'] . '</option>';
    $album_sort_select .= '<option value="1"' . ($_MG_CONF['ad_album_sort_order']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_capture_asc'] . '</option>';
    $album_sort_select .= '<option value="2"' . ($_MG_CONF['ad_album_sort_order']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_capture'] . '</option>';
    $album_sort_select .= '<option value="3"' . ($_MG_CONF['ad_album_sort_order']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_upload_asc'] . '</option>';
    $album_sort_select .= '<option value="4"' . ($_MG_CONF['ad_album_sort_order']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_upload'] . '</option>';
    $album_sort_select .= '<option value="5"' . ($_MG_CONF['ad_album_sort_order']==5 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_alpha'] . '</option>';
    $album_sort_select .= '<option value="6"' . ($_MG_CONF['ad_album_sort_order']==6 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_alpha_asc'] . '</option>';
    $album_sort_select .= '</select>';

    $display_album_desc_select  = '<input type="checkbox" name="display_album_desc" value="1" ' . ($_MG_CONF['ad_display_album_desc'] ? ' checked="checked"' : '') . '/>';

    // watermark stuff...
    $wm_auto_select     = '<input type="checkbox" name="wm_auto" value="1" ' . ($_MG_CONF['ad_wm_auto'] ? ' checked="checked"' : '') . '/>';

    $wm_opacity_select  = '<select name="wm_opacity">';
    $wm_opacity_select .= '<option value="10"' . ($_MG_CONF['ad_wm_opacity']==10 ? 'selected="selected"' : '') . '>10%</option>';
    $wm_opacity_select .= '<option value="20"' . ($_MG_CONF['ad_wm_opacity']==20 ? 'selected="selected"' : '') . '>20%</option>';
    $wm_opacity_select .= '<option value="30"' . ($_MG_CONF['ad_wm_opacity']==30 ? 'selected="selected"' : '') . '>30%</option>';
    $wm_opacity_select .= '<option value="40"' . ($_MG_CONF['ad_wm_opacity']==40 ? 'selected="selected"' : '') . '>40%</option>';
    $wm_opacity_select .= '<option value="50"' . ($_MG_CONF['ad_wm_opacity']==50 ? 'selected="selected"' : '') . '>50%</option>';
    $wm_opacity_select .= '<option value="60"' . ($_MG_CONF['ad_wm_opacity']==60 ? 'selected="selected"' : '') . '>60%</option>';
    $wm_opacity_select .= '<option value="70"' . ($_MG_CONF['ad_wm_opacity']==70 ? 'selected="selected"' : '') . '>70%</option>';
    $wm_opacity_select .= '<option value="80"' . ($_MG_CONF['ad_wm_opacity']==80 ? 'selected="selected"' : '') . '>80%</option>';
    $wm_opacity_select .= '<option value="90"' . ($_MG_CONF['ad_wm_opacity']==90 ? 'selected="selected"' : '') . '>90%</option>';
    $wm_opacity_select .= '</select>';

    $wm_location_select  = '<select name="wm_location">';
    $wm_location_select .= '<option value="1"' . ($_MG_CONF['ad_wm_location']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['top_left'] . '</option>';
    $wm_location_select .= '<option value="2"' . ($_MG_CONF['ad_wm_location']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['top_center'] . '</option>';
    $wm_location_select .= '<option value="3"' . ($_MG_CONF['ad_wm_location']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['top_right'] . '</option>';
    $wm_location_select .= '<option value="4"' . ($_MG_CONF['ad_wm_location']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['middle_left'] . '</option>';
    $wm_location_select .= '<option value="5"' . ($_MG_CONF['ad_wm_location']==5 ? 'selected="selected"' : '') . '>' . $LANG_MG01['middle_center'] . '</option>';
    $wm_location_select .= '<option value="6"' . ($_MG_CONF['ad_wm_location']==6 ? 'selected="selected"' : '') . '>' . $LANG_MG01['middle_right'] . '</option>';
    $wm_location_select .= '<option value="7"' . ($_MG_CONF['ad_wm_location']==7 ? 'selected="selected"' : '') . '>' . $LANG_MG01['bottom_left'] . '</option>';
    $wm_location_select .= '<option value="8"' . ($_MG_CONF['ad_wm_location']==8 ? 'selected="selected"' : '') . '>' . $LANG_MG01['bottom_center'] . '</option>';
    $wm_location_select .= '<option value="9"' . ($_MG_CONF['ad_wm_location']==9 ? 'selected="selected"' : '') . '>' . $LANG_MG01['bottom_right'] . '</option>';
    $wm_location_select .= '</select>';

    // now select what watermarks we have permission to use...
    $whereClause = " WHERE wm_id<>0 AND ";
    if ( SEC_hasRights('mediagallery.config')) {
        $whereClause .= "1=1";
    } else {
        $whereClause .= "(owner_id=" . $_USER['uid'] . " OR owner_id=0)";
    }
    $sql = "SELECT * FROM {$_TABLES['mg_watermarks']} " . $whereClause . " ORDER BY owner_id";
    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );

    $wm_select =  '<select name="wm_id"  onchange="javascript:change(this)">';
    $wm_select .= '<option value="blank.png">' . $LANG_MG01['no_watermark'] . '</option>';

    $wm_current = '<img src="' . $_MG_CONF['assets_url'] . '/watermarks/blank.png" name="myImage" alt=""' . '/>';

    for ($i=0;$i<$nRows;$i++) {
        $row = DB_fetchArray($result);
        $wm_select .= '<option value="' . $row['filename'] . '"' . ($_MG_CONF['ad_wm_id']==$row['wm_id'] ? 'selected="selected"' : '') . '>' . $row['filename'] . '</option>';
        if ( $_MG_CONF['ad_wm_id'] == $row['wm_id']) {
            $wm_current = '<img src="' . $_MG_CONF['site_url'] . '/watermarks/' . $row['filename'] . '" name="myImage" alt=""' . '/>';
        }
    }
    $wm_select .= '</select>';

    $allow_download_select     = '<input type="checkbox" name="allow_download" value="1" ' . ($_MG_CONF['ad_allow_download'] ? ' checked="checked"' : '') . '/>';
    $filename_title_select     = '<input type="checkbox" name="filename_title" value="1" ' . ($_MG_CONF['ad_filename_title'] ? ' checked="checked"' : '') . '/>';


    // permission template

    $usergroups = SEC_getUserGroups();
    $groupdd = '';
    $moddd = '';

    $gresult = DB_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name LIKE 'mediagallery Admin'");
    $grow = DB_fetchArray($gresult);
    $default_group_id = $grow['grp_id'];

    if ( !isset($_MG_CONF['ad_mod_group_id'] ) ) {
        $_MG_CONF['ad_mod_group_id'] = $default_group_id;
    }
    $groupdd .= '<select name="group_id">';
    $moddd .= '<select name="mod_id">';
    for ($i = 0; $i < count($usergroups); $i++) {
        if ( $usergroups[key($usergroups)] != 2 && $usergroups[key($usergroups)] != 13 ) {
            $groupdd .= '<option value="' . $usergroups[key($usergroups)] . '"';
            $moddd   .= '<option value="' . $usergroups[key($usergroups)] . '"';
            if ($default_group_id == $usergroups[key($usergroups)]) {
                $groupdd .= ' selected="selected"';
                $groupname = key($usergroups);
            }
            if ($_MG_CONF['ad_mod_group_id'] == $usergroups[key($usergroups)]) {
                $moddd   .= ' selected="selected"';
            }
            $groupdd .= '>' . key($usergroups) . '</option>';
            $moddd   .= '>' . key($usergroups) . '</option>';
        }
        next($usergroups);
    }
    $groupdd .= '</select>';
    $moddd .= '</select>';

    $upload_select   = '<input type="checkbox" name="uploads" value="1" ' . ($_MG_CONF['ad_member_uploads'] ? ' checked="checked"' : '') . '/>';
    $moderate_select = '<input type="checkbox" name="moderate" value="1" ' . ($_MG_CONF['ad_moderate'] ? ' checked="checked"' : '') . '/>';

    $frames = new mgFrame();
    $skins = array();
    $skins = $frames->getFrames();

    $skin_select  = '<select name="skin">';
    $askin_select = '<select name="askin">';
    $dskin_select = '<select name="dskin">';
    for ( $i=0; $i < count($skins); $i++ ) {
        $skin_select  .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['ad_image_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $askin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['ad_album_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $dskin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($_MG_CONF['ad_display_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
    }
    $skin_select  .= '</select>';
    $askin_select .= '</select>';
    $dskin_select .= '</select>';

    $T->set_var(array(
        'jpg_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_JPG ? ' checked="checked"' : ''),
        'png_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_PNG ? ' checked="checked"' : ''),
        'tif_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_TIF ? ' checked="checked"' : ''),
        'gif_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_GIF ? ' checked="checked"' : ''),
        'bmp_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_BMP ? ' checked="checked"' : ''),
        'tga_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_TGA ? ' checked="checked"' : ''),
        'psd_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_PSD ? ' checked="checked"' : ''),
        'mp3_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_MP3 ? ' checked="checked"' : ''),
        'ogg_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_OGG ? ' checked="checked"' : ''),
        'asf_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_ASF ? ' checked="checked"' : ''),
        'swf_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_SWF ? ' checked="checked"' : ''),
        'mov_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_MOV ? ' checked="checked"' : ''),
        'mp4_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_MP4 ? ' checked="checked"' : ''),
        'mpg_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_MPG ? ' checked="checked"' : ''),
        'zip_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_ZIP ? ' checked="checked"' : ''),
        'flv_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_FLV ? ' checked="checked"' : ''),
        'rflv_checked'  => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_RFLV ? ' checked="checked"' : ''),
        'emb_checked'   => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_EMB  ? ' checked="checked"' : ''),
        'other_checked' => ((int)$_MG_CONF['ad_valid_formats'] & (int)MG_OTHER ? ' checked="checked"' : ''),
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
    ));
    $T->parse('valid_formats','admin_formats');

    $T->set_var(array(
        'lang_uploads'          => $LANG_MG01['anonymous_uploads_prompt'],
        'lang_accessrights'     => $LANG_ACCESS['accessrights'],
        'lang_owner'            => $LANG_ACCESS['owner'],
        'lang_group'            => $LANG_ACCESS['group'],
        'lang_permissions'      => $LANG_ACCESS['permissions'],
        'lang_perm_key'         => $LANG_ACCESS['permissionskey'],
        'lang_hidden'           => $LANG_MG01['hidden'],
        'permissions_editor'    => SEC_getPermissionsHTML($_MG_CONF['ad_perm_owner'],$_MG_CONF['ad_perm_group'],$_MG_CONF['ad_perm_members'],$_MG_CONF['ad_perm_anon']),
        'permissions_msg'       => $LANG_ACCESS['permmsg'],
        'group_dropdown'        => $groupdd,
        'mod_dropdown'          => $moddd,
        'lang_member_upload'    => $LANG_MG01['member_upload'],
        'lang_moderate_album'   => $LANG_MG01['mod_album'],
        'lang_mod_group'        => $LANG_MG01['moderation_group'],
        'lang_zero_unlimited'   => $LANG_MG01['zero_unlimited'],
        'uploads'               => $upload_select,
        'moderate'              => $moderate_select,
    ));

    $T->set_var(array(
        'action'                => 'album',
        'path_mg'               => $_MG_CONF['site_url'], // $_MG_CONF['path_mg'],
        'comment_select'        => $comment_select,
        'exif_select'           => $exif_select,
        'ranking_select'        => $ranking_select,
        'full_select'           => $full_select,
        'ss_select'             => $ss_select,
        'ri_select'             => $ri_select,
        'sf_select'             => $sf_select,
        'views_select'          => $views_select,
        'keywords_select'       => $keywords_select,
        'album_views_select'    => $album_views_select,
        'display_album_desc_select' => $display_album_desc_select,
        'sort_select'           => $sort_select,
        'rss_select'            => $rss_select,
        'podcast_select'        => $podcast_select,
        'postcard_select'       => $postcard_select,
        'afirst_select'         => $afirst_select,
        'tn_size_select'        => $tn_size_select,
        'tnheight_input'		=> $tnheight_input,
        'tnwidth_input'			=> $tnwidth_input,
        'height_input'          => $max_image_height_input,
        'width_input'           => $max_image_width_input,
        'max_size_input'        => $max_filesize_input,
        'display_image_size'    => $display_image_size_select,
        'rows_input'            => $rows_input,
        'columns_input'         => $columns_input,
        'email_mod_select'      => $email_mod_select,
        'playback_type'         => $playback_type,
        'album_theme_select'    => $album_theme_select,
        'rsschildren_select'    => $rsschildren_select,
        'mp3ribbon_select'      => $mp3ribbon_select,
        'wm_auto_select'        => $wm_auto_select,
        'wm_opacity_select'     => $wm_opacity_select,
        'wm_location_select'    => $wm_location_select,
        'wm_select'             => $wm_select,
        'wm_current'            => $wm_current,
        'album_sort_select'     => $album_sort_select,
        'allow_download_select' => $allow_download_select,
        'filename_title_select' => $filename_title_select,
        'skin_select'           => $skin_select,
        'askin_select'          => $askin_select,
        'dskin_select'          => $dskin_select,
        'lang_save'             => $LANG_MG01['save'],
        's_form_action'         => $_CONF['site_admin_url'] . '/plugins/mediagallery/defaults.php',
        'lang_album_edit_help'  => $LANG_MG01['album_edit_help'],
        'lang_title'            => $LANG_MG01['title'],
        'lang_parent_album'     => $LANG_MG01['parent_album'],
        'lang_description'      => $LANG_MG01['description'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_delete'           => $LANG_MG01['delete'],
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
        'lang_attached_thumbnail' => $LANG_MG01['attached_thumbnail'],
        'lang_thumbnail'        => $LANG_MG01['thumbnail'],
        'lang_email_mods_on_submission' => $LANG_MG01['email_mods_on_submission'],
        'lang_album_attributes' => $LANG_MG01['album_attributes'],
        'lang_album_cover'      => $LANG_MG01['album_cover'],
        'lang_enable_views'     => $LANG_MG01['enable_views'],
        'lang_enable_keywords'  => $LANG_MG01['enable_keywords'],
        'lang_enable_album_views'=> $LANG_MG01['enable_album_views'],
        'lang_enable_sort'      => $LANG_MG01['enable_sort'],
        'lang_enable_rss'       => $LANG_MG01['enable_rss'],
        'lang_enable_postcard'  => $LANG_MG01['enable_postcard'],
        'lang_albums_first'     => $LANG_MG01['albums_first'],
        'lang_full_display'     => $LANG_MG01['full_display'],
        'lang_max_image_height' => $LANG_MG01['max_image_height'],
        'lang_max_image_width'  => $LANG_MG01['max_image_width'],
        'lang_max_filesize'     => $LANG_MG01['max_filesize'],
        'lang_display_image_size' => $LANG_MG01['display_image_size'],
        'lang_album_sort'       => $LANG_MG01['default_album_sort'],
        'lang_watermark'        => $LANG_MG01['watermark'],
        'lang_wm_auto'          => $LANG_MG01['watermark_auto'],
        'lang_wm_opacity'       => $LANG_MG01['watermark_opacity'],
        'lang_wm_location'      => $LANG_MG01['watermark_location'],
        'lang_wm_id'            => $LANG_MG01['watermark_image'],
        'lang_allow_download'   => $LANG_MG01['allow_download'],
        'lang_display_album_desc' => $LANG_MG01['display_album_desc'],
        'lang_filename_title'   => $LANG_MG01['filename_title'],
        'lang_image_skin'       => $LANG_MG01['image_skin'],
        'lang_album_skin'       => $LANG_MG01['album_skin'],
        'lang_display_skin'     => $LANG_MG01['display_skin'],
        'rtl'                   => $LANG_DIRECTION == "rtl" ? "rtl" : "",
        'lang_podcast'          => $LANG_MG01['podcast'],
        'lang_theme_select'		=> $LANG_MG01['album_theme'],
        'lang_rsschildren'      => $LANG_MG01['rsschildren'],
        'lang_mp3ribbon'        => $LANG_MG01['mp3ribbon'],
        'lang_tnheight'			=> $LANG_MG01['tn_height'],
        'lang_tnwidth'			=> $LANG_MG01['tn_width'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveDefaults( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $_POST;

    $enable_comments        = isset($_POST['enable_comments']) ? COM_applyFilter($_POST['enable_comments'],true) : 0;
    $enable_exif            = isset($_POST['enable_exif']) ? COM_applyFilter($_POST['enable_exif'],true) : 0;
    $enable_rating          = isset($_POST['enable_rating']) ? COM_applyFilter($_POST['enable_rating'],true) : 0;
    $enable_album_views     = isset($_POST['enable_album_views']) ? COM_applyFilter($_POST['enable_album_views'],true) : 0;
    $enable_views           = isset($_POST['enable_views']) ? COM_applyFilter($_POST['enable_views'],true) : 0;
    $enable_keywords        = isset($_POST['enable_keywords']) ? COM_applyFilter($_POST['enable_keywords'],true) : 0;
    $enable_sort            = isset($_POST['enable_sort']) ? COM_applyFilter($_POST['enable_sort'],true) : 0;
    $enable_rss             = isset($_POST['enable_rss']) ? COM_applyFilter($_POST['enable_rss'],true) : 0;
    $enable_postcard        = isset($_POST['enable_postcard']) ? COM_applyFilter($_POST['enable_postcard'],true) : 0;
    $enable_podcast         = isset($_POST['podcast']) ? COM_applyFilter($_POST['podcast'],true) : 0;
    $album_sort_order       = COM_applyFilter($_POST['album_sort_order'],true);
    $playback_type          = COM_applyFilter($_POST['playback_type'],true);
    $enable_slideshow       = isset($_POST['enable_slideshow']) ? COM_applyFilter($_POST['enable_slideshow'],true) : 0;
    $enable_random          = isset($_POST['enable_random']) ? COM_applyFilter($_POST['enable_random'],true) : 0;
    $albums_first           = isset($_POST['albums_first']) ? COM_applyFilter($_POST['albums_first'],true) : 0;
    $enable_shutterfly      = isset($_POST['enable_shutterfly']) ? COM_applyFilter($_POST['enable_shutterfly'],true) : 0;
    $full_display           = COM_applyFilter($_POST['full_display'],true);
    $tn_size                = COM_applyFilter($_POST['tn_size'],true);
    $tn_height  			= COM_applyFilter($_POST['tnheight'],true);
    $tn_width       		= COM_applyFilter($_POST['tnwidth'],true);
    $max_image_width        = COM_applyFilter($_POST['max_image_width'],true);
    $max_image_height       = COM_applyFilter($_POST['max_image_height'],true);
    $max_filesize           = COM_applyFilter($_POST['max_filesize'],true);
    $display_image_size     = COM_applyFilter($_POST['display_image_size'],true);
    $display_rows           = COM_applyFIlter($_POST['display_rows'],true);
    $display_columns        = COM_applyFilter($_POST['display_columns'],true);
    $wm_auto                = isset($_POST['wm_auto']) ? COM_applyFilter($_POST['wm_auto'],true) : 0;
    $wm_opacity             = COM_applyFilter($_POST['wm_opacity'],true);
    $wm_location            = COM_applyFilter($_POST['wm_location'],true);
    $wm_id                  = COM_applyFilter($_POST['wm_id']);
    $uploads                = isset($_POST['uploads']) ? COM_applyFilter($_POST['uploads'],true) : 0;
    $moderate               = isset($_POST['moderate']) ? COM_applyFilter($_POST['moderate'],true) : 0;
    $email_mod              = isset($_POST['email_mod']) ? COM_applyFilter($_POST['email_mod'],true) : 0;
    $mod_id                 = COM_applyFilter($_POST['mod_id'],true);
    $allow_download         = isset($_POST['allow_download']) ? COM_applyFilter($_POST['allow_download'],true) : 0;
    $display_album_desc     = isset($_POST['display_album_desc']) ? COM_applyFilter($_POST['display_album_desc'],true) : 0;
    $filename_title         = isset($_POST['filename_title']) ? COM_applyFilter($_POST['filename_title'],true) : 0;
    $image_skin             = COM_applyFilter($_POST['skin']);
    $album_skin             = COM_applyFilter($_POST['askin']);
    $display_skin           = COM_applyFilter($_POST['dskin']);
    $mp3ribbon              = isset($_POST['mp3ribbon']) ? COM_applyFilter($_POST['mp3ribbon'],true) : 0;
    $rsschildren            = isset($_POST['rsschildren']) ? COM_applyFilter($_POST['rsschildren'],true) : 0;
    $album_theme            = COM_applyFilter($_POST['album_theme']);
    // Convert array values to numeric permission values
    $tperm_owner                 = isset($_POST['perm_owner']) ? $_POST['perm_owner'] : 0;                             // admin only
    $tperm_group                 = isset($_POST['perm_group']) ? $_POST['perm_group'] : 0;                             // admin only
    $tperm_members               = isset($_POST['perm_members']) ? $_POST['perm_members'] : 0;
    $tperm_anon                  = isset($_POST['perm_anon']) ? $_POST['perm_anon'] : 0;
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($tperm_owner,$tperm_group,$tperm_members,$tperm_anon);

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

    // put any error checking / validation here

    if ( $wm_id == 'blank.png' ) {
        $wm_id = 0;
    } else {
        $wm_id = DB_getItem($_TABLES['mg_watermarks'],'wm_id','filename="' . $wm_id . '"');
    }
    if ( $wm_id == '' )
        $wm_id = 0;

    if ( $wm_id == 0 ) {
        $wm_auto = 0;
    }

    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_comments','$enable_comments'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_exif_display','$enable_exif'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_rating','$enable_rating'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_playback_type','$playback_type'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_slideshow','$enable_slideshow'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_random','$enable_random'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_shutterfly','$enable_shutterfly'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_views','$enable_views'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_keywords','$enable_keywords'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_album_views','$enable_album_views'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_sort','$enable_sort'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_albums_first','$albums_first'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_full_display','$full_display'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_tn_size','$tn_size'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_tn_height','$tn_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_tn_width','$tn_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_display_rows','$display_rows'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_display_columns','$display_columns'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_member_uploads','$uploads'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_moderate','$moderate'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_email_mod','$email_mod'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_wm_auto','$wm_auto'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_wm_id','$wm_id'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_wm_opacity','$wm_opacity'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_wm_location','$wm_location'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_album_sort_order','$album_sort_order'");
    if ( $max_filesize != 0 ) {
        $max_filesize = $max_filesize * 1024;
    }
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_max_filesize','$max_filesize'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_max_image_height','$max_image_height'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_max_image_width','$max_image_width'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_display_image_size','$display_image_size'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_perm_owner','$perm_owner'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_perm_group','$perm_group'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_perm_members','$perm_members'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_perm_anon','$perm_anon'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_mod_group_id','$mod_id'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_rss','$enable_rss'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_enable_postcard','$enable_postcard'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_allow_download','$allow_download'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_display_album_desc','$display_album_desc'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_valid_formats','$valid_formats'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_filename_title','$filename_title'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_image_skin','$image_skin'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_album_skin','$album_skin'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_display_skin','$display_skin'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_podcast','$enable_podcast'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_mp3ribbon','$mp3ribbon'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_rsschildren','$rsschildren'");
    DB_save($_TABLES['mg_config'],"config_name, config_value","'ad_skin','$album_theme'");

    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=4');
    exit;
}

/**
* Main
*/

$display = '';
$mode = '';

if (isset ($_POST['mode'])) {
    $mode = COM_applyFilter($_POST['mode']);
} else if (isset ($_GET['mode'])) {
    $mode = COM_applyFilter($_GET['mode']);
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

if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {   // save the config
    $T->set_var(array(
        'admin_body'    => MG_saveDefaults(),
    ));
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_editDefaults(),
        'title'         => $LANG_MG01['album_default_editor'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"' . '/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Album_Defaults',
    ));
}
$T->parse('output', 'admin');
$display = COM_siteHeader('menu','');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>