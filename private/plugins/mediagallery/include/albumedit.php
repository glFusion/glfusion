<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | albumedit.php                                                            |
// |                                                                          |
// | Album editing administration                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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
* Shows security control for an object
*
* This will return the HTML needed to create the security control see on the admin
* screen for GL objects (i.e. stories, links, etc)
*
* @param        int     $perm_members   Permissions logged in members have
* @param        int     $perm_anon      Permissions anonymous users have
* @return       string  needed HTML (table) in HTML $perm_owner = array of permissions [edit,read], etc edit = 1 if permission, read = 2 if permission
*
*/
function MG_getMemberPermissionsHTML($perm_members,$perm_anon) {
    global $LANG_ACCESS;

    $retval = '<table cellpadding="5" cellspacing="5" border="0">' . LB . '<tr>' . LB
        . '<td><b>' . $LANG_ACCESS['members'] . '</b></td>' . LB
        . '<td><b>' . $LANG_ACCESS['anonymous'] . '</b></td>' . LB
        . '</tr>' . LB . '<tr>' . LB;

    // Member Permissions
    $retval .= '<td align="center"><b>R</b><br/><input type="checkbox" name="perm_members[]" value="2"';
    if ($perm_members == 2) {
        $retval .= ' checked="checked"';
    }
    $retval .= '/></td>' . LB;

    // Anonymous Permissions

    $retval .= '<td align="center"><b>R</b><br/><input type="checkbox" name="perm_anon[]" value="2"';
    if ($perm_anon == 2) {
        $retval .= ' checked="checked"';
    }
    $retval .= '/></td>' . LB;

    // Finish off and return

    $retval .= '</tr>' . LB . '</table>';

    return $retval;
}

/**
* edits or creates an album
*
* @param    int     album_id    album_id to edit
* @param    string  mode        create or edit
* @param    string  actionURL   where to redirection on finish
* @param    int     oldaid      original album id
* @return   string              HTML
*
*/
function MG_editAlbum( $album_id=0, $mode ='', $actionURL='', $oldaid = 0 ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $LANG_ACCESS,$REMOTE_ADDR;
    global $MG_albums, $album_selectbox, $_DB_dbms;

    $valid_albums = 0;

    if ($actionURL == '' )
        $actionURL = $_CONF['site_admin_url'] . '/plugins/mediagallery/index.php';

    if ( $_DB_dbms == "mssql" ) {
        $sql        = "SELECT *,CAST(album_desc AS TEXT) as album_desc FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . $album_id;
    } else {
        $sql        = "SELECT * FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . intval($album_id);
    }

    $result     = DB_query( $sql );
    $numRows    = DB_numRows( $result );
    if ( $numRows > 0 ) {
        $A      = DB_fetchArray($result);
    }
    $retval = '';

    $T = new Template( MG_getTemplatePath($album_id) );

    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    if ($album_id != 0 && $mode == 'edit') {
        // If edit, pull up the existing album information...

        if ($MG_albums[$album_id]->access != 3 ) {
            COM_errorLog("MediaGallery: Someone has tried to illegally edit a Media Gallery Album.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
    } else if ($album_id==0 && $mode == 'create') {
        // create the album...

        $A['album_id'] = -1;
        $A['album_order'] = 0;
        $album_id = -1;
        $A['album_parent'] = 0;
        $A['album_title'] = '';
        $A['album_desc'] = '';
        $A['hidden'] = 0;
        $A['album_cover'] = -1;
        $A['featured'] = 0;
        $A['cbposition'] = 0;
        $A['cbpage'] = 'all';
        $A['owner_id']          = $_USER['uid'];
        $A['member_uploads']    = $_MG_CONF['ad_member_uploads'];
        $A['moderate']          = $_MG_CONF['ad_moderate'];
        $A['tn_attached']       = 0;
        $A['exif_display']		= $_MG_CONF['ad_exif_display'];
        $A['enable_slideshow']  = $_MG_CONF['ad_enable_slideshow'];
        $A['enable_random']     = $_MG_CONF['ad_enable_random'];
//        $A['enable_shutterfly'] = $_MG_CONF['ad_enable_shutterfly'];
        $A['enable_views']      = $_MG_CONF['ad_enable_views'];
        $A['enable_keywords']   = $_MG_CONF['ad_enable_keywords'];
        $A['enable_html']       = 0;
        $A['display_album_desc'] = $_MG_CONF['ad_display_album_desc'];
        $A['enable_album_views'] = $_MG_CONF['ad_enable_album_views'];
        $A['image_skin']        = $_MG_CONF['ad_image_skin'];
        $A['album_skin']        = $_MG_CONF['ad_album_skin'];
        $A['display_skin']      = $_MG_CONF['ad_display_skin'];
        $A['enable_sort']       = $_MG_CONF['ad_enable_sort'];
        $A['enable_rss']        = $_MG_CONF['ad_enable_rss'];
        $A['enable_postcard']   = $_MG_CONF['ad_enable_postcard'];
        $A['albums_first']      = $_MG_CONF['ad_albums_first'];
        $A['enable_rating']     = $_MG_CONF['ad_enable_rating'];
        $A['enable_comments']   = $_MG_CONF['ad_enable_comments'];
        $A['tn_size']           = $_MG_CONF['ad_tn_size'];
        $A['allow_download']    = $_MG_CONF['ad_allow_download'];
        $A['max_image_height']  = $_MG_CONF['ad_max_image_height'];
        $A['max_image_width']   = $_MG_CONF['ad_max_image_width'];
        $A['max_filesize']      = $_MG_CONF['ad_max_filesize'];
        $A['display_image_size'] = $_MG_CONF['ad_display_image_size'];
        $A['display_rows']      = $_MG_CONF['ad_display_rows'];
        $A['display_columns']   = $_MG_CONF['ad_display_columns'];
        $A['valid_formats']     = $_MG_CONF['ad_valid_formats'];
        $A['filename_title']    = $_MG_CONF['ad_filename_title'];
        $A['wm_auto']           = $_MG_CONF['ad_wm_auto'];
        $A['wm_id']             = $_MG_CONF['ad_wm_id'];
        $A['opacity']           = $_MG_CONF['ad_wm_opacity'];
        $A['wm_location']       = $_MG_CONF['ad_wm_location'];
        $A['album_sort_order']  = $_MG_CONF['ad_album_sort_order'];
        $A['email_mod']         = $_MG_CONF['ad_email_mod'];
        $A['album_cover_filename'] = '';
        $A['last_update']        = 0;
        $A['media_count']       = 0;
        $A['full_display']      = $_MG_CONF['ad_full_display'];
        $A['playback_type']     = $_MG_CONF['ad_playback_type'];
        $A['podcast']			= isset($_MG_CONF['ad_podcast']) ? $_MG_CONF['ad_podcast'] : 0;
        $A['mp3ribbon']         = 0;
        $A['rsschildren']       = 0;
        $A['usealternate']      = isset($_MG_CONF['ad_use_alternate']) ? $_MG_CONF['ad_use_alternate'] : 0;
        $A['skin']              = isset($_MG_CONF['ad_skin']) ? $_MG_CONF['ad_skin'] : 'default';

        $gresult = DB_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name LIKE 'mediagallery Admin'");
        $grow = DB_fetchArray($gresult);
        $grp_id = $grow['grp_id'];

        $A['group_id']      = $grp_id;
        $A['mod_group_id']  = $grp_id;

        $A['perm_owner']    = $_MG_CONF['ad_perm_owner'];
        $A['perm_group']    = $_MG_CONF['ad_perm_group'];
        $A['perm_members']  = $_MG_CONF['ad_perm_members'];
        $A['perm_anon']     = $_MG_CONF['ad_perm_anon'];
        $A['tnheight']      = $_MG_CONF['ad_tn_height'];
        $A['tnwidth']       = $_MG_CONF['ad_tn_width'];
    }
    $T->set_var('album_id',$A['album_id']);

    $retval .= COM_startBlock (($mode=='create' ? $LANG_MG01['create_album'] : ($LANG_MG01['edit_album'] . ' - ' . strip_tags($A['album_title']))), '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    // If edit, pull up the existing album information...

    $T->set_file(array(
        'admin'        =>  'editalbum.thtml',
        'falbum'       =>  'featured_album.thtml',
        'perms_admin'  =>  'edit_album_permissions.thtml',
        'perms_member' =>  'edit_album_perm_member.thtml',
        'admin_attr'   =>  'editalbum_admin.thtml',
        'admin_formats'=>  'editalbum_formats.thtml',
    ));

    // construct the album jumpbox...

    if ( $mode == 'create' ) {
        $select =  $oldaid;
    } else {
        $select = $A['album_parent'];
    }

    $album_selectbox  = '<select name="parentaid">';
    $valid_albums += $MG_albums[0]->buildAlbumBox($select,3,$A['album_id'],$mode);
    $album_selectbox .= '</select>';
    $album_select = $album_selectbox;

    if ($valid_albums == 0  ) {
        COM_errorLog("MediaGallery: Someone has tried to illegally create a Medig Gallery Album.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    // build exif select box...

    $exif_select  = '<select name="enable_exif">';
    $exif_select .= '<option value="0"' . ($A['exif_display']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disable_exif'] . '</option>';
    $exif_select .= '<option value="1"' . ($A['exif_display']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['display_below_media']  . '</option>';
    $exif_select .= '<option value="2"' . ($A['exif_display']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['display_in_popup'] . '</option>';
    $exif_select .= '<option value="3"' . ($A['exif_display']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['both'] . '</option>';
    $exif_select .= '</select>';

    $full_select  = '<select name="full_display"' . ($_MG_CONF['discard_original'] ? ' disabled=disabled ' : '') . '>';
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

    $comment_select = '<input type="checkbox" name="enable_comments" value="1" ' . ($A['enable_comments'] ? ' checked="checked"' : '') . '/>';

    $ss_select		= '<select name="enable_slideshow">';
    $ss_select		.= '<option value="0" ' . ($A['enable_slideshow'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $ss_select		.= '<option value="1"' . ($A['enable_slideshow'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['js_slideshow'] . '</option>';
    $ss_select		.= '<option value="2"' . ($A['enable_slideshow'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['lightbox'] . '</option>';
    $ss_select		.= '<option value="3"' . ($A['enable_slideshow'] == 3 ? ' selected="selected"' : '') . '>' . $LANG_MG01['flash_slideshow_disp'] . '</option>';
    $ss_select		.= '<option value="4"' . ($A['enable_slideshow'] == 4 ? ' selected="selected"' : '') . '>' . $LANG_MG01['flash_slideshow_full'] . '</option>';
    $ss_select		.= '<option value="5"' . ($A['enable_slideshow'] == 5 ? ' selected="selected"' : '') . '>' . $LANG_MG01['mp3_jukebox'] . '</option>';
    $ss_select      .= '</select>';

//    $sf_select      = '<input type="checkbox" name="enable_shutterfly" value="1" ' . ($A['enable_shutterfly'] ? ' checked="checked"' : '') . '/>';
    $views_select   = '<input type="checkbox" name="enable_views" value="1" ' . ($A['enable_views'] ? ' checked="checked"' : '') . '/>';
    $keywords_select = '<input type="checkbox" name="enable_keywords" value="1" ' . ($A['enable_keywords'] ? ' checked="checked"' : '') . '/>';
    $html_select    = '<input type="checkbox" name="enable_html" value="1" ' . ($A['enable_html'] ? ' checked="checked"' : '') . '/>';
    $sort_select    = '<input type="checkbox" name="enable_sort" value="1" ' . ($A['enable_sort'] ? ' checked="checked"' : '') . '/>';
    $rss_select     = '<input type="checkbox" name="enable_rss" value="1" ' . ($A['enable_rss'] ? ' checked="checked"' : '') . '/>';

    $postcard_select  = '<select name="enable_postcard">';
    $postcard_select .= '<option value="0"' . ($A['enable_postcard']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['disabled'] . '</option>';
    $postcard_select .= '<option value="1"' . ($A['enable_postcard']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['members_only']  . '</option>';
    $postcard_select .= '<option value="2"' . ($A['enable_postcard']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['all_users'] . '</option>';
    $postcard_select .= '</select>';

    $afirst_select   = '<input type="checkbox" name="albums_first" value="1" ' . ($A['albums_first'] ? ' checked="checked"' : '') . '/>';
    $usealternate_select   = '<input type="checkbox" name="usealternate" value="1" ' . ($A['usealternate'] ? ' checked="checked"' : '') . '/>';
    $album_views_select   = '<input type="checkbox" name="enable_album_views" value="1" ' . ($A['enable_album_views'] ? ' checked="checked"' : '') . '/>';
    $display_album_desc_select   = '<input type="checkbox" name="display_album_desc" value="1" ' . ($A['display_album_desc'] ? ' checked="checked"' : '') . '/>';

    $tn_size_select  = '<select name="tn_size">';
    $tn_size_select .= '<option value="0"' . ($A['tn_size']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['small'] . '</option>';
    $tn_size_select .= '<option value="1"' . ($A['tn_size']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['medium'] . '</option>';
    $tn_size_select .= '<option value="2"' . ($A['tn_size']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['large'] . '</option>';
    $tn_size_select .= '<option value="3"' . ($A['tn_size']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['custom'] . '</option>';
    $tn_size_select .= '<option value="4"' . ($A['tn_size']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['square'] . '</option>';
    $tn_size_select .= '</select>';

    $display_image_size_select  = '<select name="display_image_size">';
    $display_image_size_select .= '<option value="0"' . ($A['display_image_size']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_500x375'] . '</option>';
    $display_image_size_select .= '<option value="1"' . ($A['display_image_size']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_600x450'] . '</option>';
    $display_image_size_select .= '<option value="2"' . ($A['display_image_size']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_620x465'] . '</option>';
    $display_image_size_select .= '<option value="3"' . ($A['display_image_size']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_720x540'] . '</option>';
    $display_image_size_select .= '<option value="4"' . ($A['display_image_size']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_800x600'] . '</option>';
    $display_image_size_select .= '<option value="5"' . ($A['display_image_size']==5 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_912x684'] . '</option>';
    $display_image_size_select .= '<option value="6"' . ($A['display_image_size']==6 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_1024x768'] . '</option>';
    $display_image_size_select .= '<option value="7"' . ($A['display_image_size']==7 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_1152x864'] . '</option>';
    $display_image_size_select .= '<option value="8"' . ($A['display_image_size']==8 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_1280x1024'] . '</option>';
    $display_image_size_select .= '<option value="9"' . ($A['display_image_size']==9 ? 'selected="selected"' : '') . '>' . $LANG_MG01['size_custom'] . $_MG_CONF['custom_image_width'] . 'x' . $_MG_CONF['custom_image_height'] . '</option>';
    $display_image_size_select .= '</select>';

    $rows_input = '<input type="text" size="3" name="display_rows" value="' . $A['display_rows'] . '"/>';
    $columns_input = '<input type="text" size="3" name="display_columns" value="' . $A['display_columns'] . '"/>';

    $max_image_height_input = '<input type="text" size="4" name="max_image_height" value="' . $A['max_image_height'] . '"/>';
    $max_image_width_input = '<input type="text" size="4" name="max_image_width" value="' . $A['max_image_width'] . '"/>';

    $tnheight_input = '<input type="text" size="3" name="tnheight" value="' . $A['tnheight'] . '"/>';
    $tnwidth_input  = '<input type="text" size="3" name="tnwidth" value="' . $A['tnwidth'] . '"/>';

    if ( $A['max_filesize'] != 0 ) {
        $A['max_filesize'] = $A['max_filesize'] / 1024;
    }
    $max_filesize_input = '<input type="text" size="10" name="max_filesize" value="' . $A['max_filesize'] . '"/>';

    $email_mod_select = '<input type="checkbox" name="email_mod" value="1" ' . ($A['email_mod'] ? ' checked="checked"' : '') . '/>';

    $playback_type  = '<select name="playback_type">';
    $playback_type .= '<option value="0"' . ($A['playback_type']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG01['play_in_popup'] . '</option>';
    $playback_type .= '<option value="1"' . ($A['playback_type']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['download_to_local'] . '</option>';
    $playback_type .= '<option value="2"' . ($A['playback_type']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['play_inline'] . '</option>';
    $playback_type .= '<option value="3"' . ($A['playback_type']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['use_mms'] . '</option>';
    $playback_type .= '</select>';

    $themes = MG_getThemes();
    $album_theme_select = '<select name="album_theme">';
    for ( $i = 0; $i < count($themes); $i++ ) {
    	$album_theme_select .= '<option value="' . $themes[$i] . '"' . ($A['skin'] == $themes[$i] ? 'selected="selected"' : '') . '>' . $themes[$i] . '</option>';
    }
    $album_theme_select .= '</select>';

    $attach_select = '<input type="checkbox" name="attach_tn" value="1" ' . ($A['tn_attached'] ? ' checked="checked"' : '') . '/>';

    $result = DB_query("SELECT * FROM {$_TABLES['users']}");
    $nRows  = DB_numRows($result);

    $owner_select = '<select name="owner_id">';
    for ($i=0; $i<$nRows;$i++) {
        $row = DB_fetchArray($result);
        if ( $row['uid'] == 1 ) {
            continue;
        }
        $owner_select .= '<option value="'
            . $row['uid'] . '"'
            . ($A['owner_id'] == $row['uid'] ? 'selected="selected"' : '') . '>'
            . COM_getDisplayName($row['uid'],$row['username'],$row['fullname'],$row['remoteusername'],$row['remoteservice'])
            . '</option>';
    }
    $owner_select .= '</select>';

    $album_sort_select  = '<select name="album_sort_order">';
    $album_sort_select .= '<option value="0"' . ($A['album_sort_order']==0 ? 'selected="selected"' : '') . '>' . $LANG_MG03['no_sort'] . '</option>';
    $album_sort_select .= '<option value="1"' . ($A['album_sort_order']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_capture_asc'] . '</option>';
    $album_sort_select .= '<option value="2"' . ($A['album_sort_order']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_capture'] . '</option>';
    $album_sort_select .= '<option value="3"' . ($A['album_sort_order']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_upload_asc'] . '</option>';
    $album_sort_select .= '<option value="4"' . ($A['album_sort_order']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_upload'] . '</option>';
    $album_sort_select .= '<option value="5"' . ($A['album_sort_order']==5 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_alpha'] . '</option>';
    $album_sort_select .= '<option value="6"' . ($A['album_sort_order']==6 ? 'selected="selected"' : '') . '>' . $LANG_MG03['sort_alpha_asc'] . '</option>';

    $album_sort_select .= '</select>';

    if (SEC_hasRights('mediagallery.admin')) {

        //
        // -- build the featured selects and info...
        //

        $featured_select = '<input type="checkbox" name="featured" value="1" ' . ($A['featured'] ? ' checked="checked"' : '') . '/>';

        // build featurepage select...

        $featurepage_select = '<select name="featurepage">';
        $featurepage_select .= '<option value="all"' . ($A['cbpage'] == 'all' ? 'selected="selected"' : '') .'>' . $LANG_MG01['all'] . '</option>';
        $featurepage_select .= '<option value="allnhp"' . ($A['cbpage'] == 'allnhp' ? 'selected="selected"' : '') .'>' . $LANG_MG01['all_nhp'] . '</option>';
        $featurepage_select .= '<option value="none"' . ($A['cbpage'] == 'none' ? 'selected="selected"' : '') .'>' . $LANG_MG01['homepage_only'] . '</option>';
        $featurepage_select .= COM_topicList('tid,topic,sortnum',$A['cbpage'], 2, true);
        $featurepage_select .= '</select>';

        // position

        $feature_pos =  '<select name="featureposition">';
        $feature_pos .= '<option value="1"' . ($A['cbposition'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['top'] . '</option>';
        $feature_pos .= '<option value="2"' . ($A['cbposition'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['after_featured_articles'] . '</option>';
        $feature_pos .= '<option value="3"' . ($A['cbposition'] == 3 ? ' selected="selected"' : '') . '>' . $LANG_MG01['bottom'] . '</option>';
        $feature_pos .= '</select>    ';

        $T->set_var(array(
            'featured_select'       => $featured_select,
            'feature_page_select'   => $featurepage_select,
            'feature_position'      => $feature_pos,
            'lang_featured_album'   => $LANG_MG01['featured_album'],
            'lang_set_featured'     => $LANG_MG01['set_featured'],
            'lang_featured_help'    => $LANG_MG01['featured_help'],
            'lang_position'         => $LANG_MG01['position'],
            'lang_topic'            => $LANG_MG01['topic'],
        ));
        $T->parse('featureselect', 'falbum');

        $ri_select      = '<input type="checkbox" name="enable_random" value="1" ' . ($A['enable_random'] ? ' checked="checked"' : '') . '/>';

        $T->set_var(array(
            'height_input'          => $max_image_height_input,
            'width_input'           => $max_image_width_input,
            'max_size_input'        => $max_filesize_input,
            'ri_select'             => $ri_select,
            'lang_ri_enable'        => $LANG_MG01['ri_enable'],
            'lang_max_image_height' => $LANG_MG01['max_image_height'],
            'lang_max_image_width'  => $LANG_MG01['max_image_width'],
            'lang_max_filesize'     => $LANG_MG01['max_filesize'],
        ));
        $T->parse('adminattr', 'admin_attr');

        $T->set_var(array(
            'jpg_checked'   => ($A['valid_formats'] & MG_JPG ? ' checked="checked"' : ''),
            'png_checked'   => ($A['valid_formats'] & MG_PNG ? ' checked="checked"' : ''),
            'tif_checked'   => ($A['valid_formats'] & MG_TIF ? ' checked="checked"' : ''),
            'gif_checked'   => ($A['valid_formats'] & MG_GIF ? ' checked="checked"' : ''),
            'bmp_checked'   => ($A['valid_formats'] & MG_BMP ? ' checked="checked"' : ''),
            'tga_checked'   => ($A['valid_formats'] & MG_TGA ? ' checked="checked"' : ''),
            'psd_checked'   => ($A['valid_formats'] & MG_PSD ? ' checked="checked"' : ''),
            'mp3_checked'   => ($A['valid_formats'] & MG_MP3 ? ' checked="checked"' : ''),
            'ogg_checked'   => ($A['valid_formats'] & MG_OGG ? ' checked="checked"' : ''),
            'asf_checked'   => ($A['valid_formats'] & MG_ASF ? ' checked="checked"' : ''),
            'swf_checked'   => ($A['valid_formats'] & MG_SWF ? ' checked="checked"' : ''),
            'mov_checked'   => ($A['valid_formats'] & MG_MOV ? ' checked="checked"' : ''),
            'mp4_checked'   => ($A['valid_formats'] & MG_MP4 ? ' checked="checked"' : ''),
            'mpg_checked'   => ($A['valid_formats'] & MG_MPG ? ' checked="checked"' : ''),
            'zip_checked'   => ($A['valid_formats'] & MG_ZIP ? ' checked="checked"' : ''),
            'flv_checked'   => ($A['valid_formats'] & MG_FLV ? ' checked="checked"' : ''),
            'rflv_checked'  => ($A['valid_formats'] & MG_RFLV ? ' checked="checked"' : ''),
            'emb_checked'   => ($A['valid_formats'] & MG_EMB ? ' checked="checked"' : ''),
            'other_checked'   => ($A['valid_formats'] & MG_OTHER ? ' checked="checked"' : ''),
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
    }
	$r = rand();
    if ($A['tn_attached']) {
        $media_size = false;
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $A['album_id'] . $ext) ) {
                $album_last_image = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $A['album_id'] . $ext;
                $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $A['album_id'] . $ext);
                if ( $media_size != false ) {
                    $T->set_var('thumbnail','<img src="' . $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $A['album_id'] . $ext . '?r=' . $r . '" alt=""/>');
                }
                break;
            }
        }
//        $T->set_var('thumbnail','<img src="' . $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $A['album_id'] . '.jpg?r=' . $r . '" alt="">');
    }
    $filename_title_select     = '<input type="checkbox" name="filename_title" value="1" ' . ($A['filename_title'] ? ' checked="checked"' : '') . '/>';

    // watermark stuff...
    $wm_auto_select     = '<input type="checkbox" name="wm_auto" value="1" ' . ($A['wm_auto'] ? ' checked="checked"' : '') . '/>';

    $wm_opacity_select  = '<select name="wm_opacity">';
    $wm_opacity_select .= '<option value="10"' . ($A['opacity']==10 ? 'selected="selected"' : '') . '>10%</option>';
    $wm_opacity_select .= '<option value="20"' . ($A['opacity']==20 ? 'selected="selected"' : '') . '>20%</option>';
    $wm_opacity_select .= '<option value="30"' . ($A['opacity']==30 ? 'selected="selected"' : '') . '>30%</option>';
    $wm_opacity_select .= '<option value="40"' . ($A['opacity']==40 ? 'selected="selected"' : '') . '>40%</option>';
    $wm_opacity_select .= '<option value="50"' . ($A['opacity']==50 ? 'selected="selected"' : '') . '>50%</option>';
    $wm_opacity_select .= '<option value="60"' . ($A['opacity']==60 ? 'selected="selected"' : '') . '>60%</option>';
    $wm_opacity_select .= '<option value="70"' . ($A['opacity']==70 ? 'selected="selected"' : '') . '>70%</option>';
    $wm_opacity_select .= '<option value="80"' . ($A['opacity']==80 ? 'selected="selected"' : '') . '>80%</option>';
    $wm_opacity_select .= '<option value="90"' . ($A['opacity']==90 ? 'selected="selected"' : '') . '>90%</option>';
    $wm_opacity_select .= '</select>';

    $wm_location_select  = '<select name="wm_location">';
    $wm_location_select .= '<option value="1"' . ($A['wm_location']==1 ? 'selected="selected"' : '') . '>' . $LANG_MG01['top_left'] . '</option>';
    $wm_location_select .= '<option value="2"' . ($A['wm_location']==2 ? 'selected="selected"' : '') . '>' . $LANG_MG01['top_center'] . '</option>';
    $wm_location_select .= '<option value="3"' . ($A['wm_location']==3 ? 'selected="selected"' : '') . '>' . $LANG_MG01['top_right'] . '</option>';
    $wm_location_select .= '<option value="4"' . ($A['wm_location']==4 ? 'selected="selected"' : '') . '>' . $LANG_MG01['middle_left'] . '</option>';
    $wm_location_select .= '<option value="5"' . ($A['wm_location']==5 ? 'selected="selected"' : '') . '>' . $LANG_MG01['middle_center'] . '</option>';
    $wm_location_select .= '<option value="6"' . ($A['wm_location']==6 ? 'selected="selected"' : '') . '>' . $LANG_MG01['middle_right'] . '</option>';
    $wm_location_select .= '<option value="7"' . ($A['wm_location']==7 ? 'selected="selected"' : '') . '>' . $LANG_MG01['bottom_left'] . '</option>';
    $wm_location_select .= '<option value="8"' . ($A['wm_location']==8 ? 'selected="selected"' : '') . '>' . $LANG_MG01['bottom_center'] . '</option>';
    $wm_location_select .= '<option value="9"' . ($A['wm_location']==9 ? 'selected="selected"' : '') . '>' . $LANG_MG01['bottom_right'] . '</option>';
    $wm_location_select .= '</select>';

    // now select what watermarks we have permission to use...
    $whereClause = " WHERE wm_id<>0 AND ";
    if ( SEC_hasRights('mediagallery.admin')) {
        $whereClause .= "1=1";
    } else {
        $whereClause .= "(owner_id=" . $_USER['uid'] . " OR owner_id=0)";
    }
    $sql = "SELECT * FROM {$_TABLES['mg_watermarks']} " . $whereClause . " ORDER BY owner_id";
    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );

    $wm_select =  '<select name="wm_id"  onchange="change(this)">';
    $wm_select .= '<option value="blank.png">' . $LANG_MG01['no_watermark'] . '</option>';

    $wm_current = '<img src="' . $_MG_CONF['site_url'] . '/watermarks/blank.png" name="myImage" alt=""/>';

    for ($i=0;$i<$nRows;$i++) {
        $row = DB_fetchArray($result);
        $wm_select .= '<option value="' . $row['filename'] . '"' . ($A['wm_id']==$row['wm_id'] ? 'selected="selected"' : '') . '>' . $row['filename'] . '</option>';
        if ( $A['wm_id'] == $row['wm_id']) {
            $wm_current = '<img src="' . $_MG_CONF['site_url'] . '/watermarks/' . $row['filename'] . '" name="myImage" alt=""/>';
        }
    }
    $wm_select .= '</select>';

    $frames = new mgFrame();
    $skins = array();
    $skins = $frames->getFrames();

    $skin_select = '<select name="skin">';
    $askin_select = '<select name="askin">';
    $dskin_select = '<select name="dskin">';
    for ( $i=0; $i < count($skins); $i++ ) {
        $skin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($A['image_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $askin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($A['album_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
        $dskin_select .= '<option value="' . $skins[$i]['dir'] . '"' . ($A['display_skin'] == $skins[$i]['dir'] ? ' selected="selected" ': '') .'>' . $skins[$i]['name'] .  '</option>';
    }
    $skin_select .= '</select>';
    $askin_select .= '</select>';
    $dskin_select .= '</select>';

    // permission template

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

    $upload_select   = '<input type="checkbox" name="uploads" value="1" ' . ($A['member_uploads'] ? ' checked="checked"' : '') . '/>';
    $moderate_select = '<input type="checkbox" name="moderate" value="1" ' . ($A['moderate'] ? ' checked="checked"' : '') . '/>';
    $child_update_select = '<input type="checkbox" name="force_child_update" value="1"/>';
    $hidden_select = '<input type="checkbox" name="hidden" value="1" ' . ($A['hidden'] ? ' checked="checked"' : '' ) . '/>';
    $allow_download_select = '<input type="checkbox" name="allow_download" value="1" ' . ($A['allow_download'] ? ' checked="checked"' : '') . '/>';

    if ( SEC_hasRights('mediagallery.admin')) {
        $perm_editor = SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
    } else {
        $perm_editor = MG_getMemberPermissionsHTML($A['perm_members'],$A['perm_anon']);
    }

    $T->set_var(array(
        'lang_uploads'          => $LANG_MG01['anonymous_uploads_prompt'],
        'lang_accessrights'     => $LANG_ACCESS['accessrights'],
        'lang_owner'            => $LANG_ACCESS['owner'],
        'owner_username'        => DB_getItem($_TABLES['users'],'username',"uid={$A['owner_id']}"),
        'owner_id'              => $A['owner_id'],
        'lang_group'            => $LANG_ACCESS['group'],
        'lang_permissions'      => $LANG_ACCESS['permissions'],
        'lang_perm_key'         => $LANG_ACCESS['permissionskey'],
        'lang_hidden'           => $LANG_MG01['hidden'],
        'permissions_msg'       => $LANG_ACCESS['permmsg'],
        'permissions_editor'    => $perm_editor,
        'origaid'               => '<input type="hidden" name="origaid" value="' . $oldaid . '"/>',
        'group_dropdown'        => $groupdd,
        'mod_dropdown'          => $moddd,
        'lang_member_upload'    => $LANG_MG01['member_upload'],
        'lang_moderate_album'   => $LANG_MG01['mod_album'],
        'lang_mod_group'        => $LANG_MG01['moderation_group'],
        'uploads'               => $upload_select,
        'moderate'              => $moderate_select,
        'hidden'                => $hidden_select,
        'force_child_update'    => $child_update_select,
        'lang_force_child_update' => $LANG_MG01['force_child_update'],
        'lang_allow_download'   => $LANG_MG01['allow_download'],
        'owner_select'          => $owner_select,
        'email_mod_select'      => $email_mod_select,
        'lang_email_mods_on_submission' => $LANG_MG01['email_mods_on_submission'],
    ));

    if ( SEC_hasRights('mediagallery.admin')) {
        $T->parse('perm_editor','perms_admin');
    } else {
        $T->parse('perm_editor','perms_member');
    }

    $T->set_var(array(
        'action'                => 'album',
        'path_mg'               => $_MG_CONF['site_url'],
        'attach_select'         => $attach_select,
        'comment_select'        => $comment_select,
        'exif_select'           => $exif_select,
        'ranking_select'        => $ranking_select,
        'podcast_select'		=> $podcast_select,
        'mp3ribbon_select'      => $mp3ribbon_select,
        'rsschildren_select'    => $rsschildren_select,
        'full_select'           => $full_select,
        'ss_select'             => $ss_select,
//        'sf_select'             => $sf_select,
        'views_select'          => $views_select,
        'keywords_select'       => $keywords_select,
        'html_select'           => $html_select,
        'album_views_select'    => $album_views_select,
        'display_album_desc_select' => $display_album_desc_select,
        'sort_select'           => $sort_select,
        'rss_select'            => $rss_select,
        'postcard_select'       => $postcard_select,
        'afirst_select'         => $afirst_select,
        'tn_size_select'        => $tn_size_select,
        'display_image_size'    => $display_image_size_select,
        'rows_input'            => $rows_input,
        'columns_input'         => $columns_input,
        'playback_type'         => $playback_type,
        'album_title'           => $A['album_title'],
        'album_desc'            => $A['album_desc'],
        'album_id'              => $A['album_id'],
        'parent_select'         => $album_select,
        'album_cover'           => $A['album_cover'],
        'album_owner'           => $A['owner_id'],
        'album_order'           => $A['album_order'],
        'album_cover_filename'  => $A['album_cover_filename'],
        'last_update'           => $A['last_update'],
        'media_count'           => $A['media_count'],
        'wm_auto_select'        => $wm_auto_select,
        'wm_opacity_select'     => $wm_opacity_select,
        'wm_location_select'    => $wm_location_select,
        'wm_select'             => $wm_select,
        'wm_current'            => $wm_current,
        'album_theme_select'	=> $album_theme_select,
        'album_sort_select'     => $album_sort_select,
        'allow_download_select' => $allow_download_select,
        'filename_title_select' => $filename_title_select,
        'skin_select'           => $skin_select,
        'askin_select'          => $askin_select,
        'dskin_select'          => $dskin_select,
        'tnheight_input'		=> $tnheight_input,
        'tnwidth_input'			=> $tnwidth_input,
        'usealternate_select'   => $usealternate_select,
        'lang_usealternate'     => $LANG_MG01['use_alternate_url'],
        'lang_tnheight'			=> $LANG_MG01['tn_height'],
        'lang_tnwidth'			=> $LANG_MG01['tn_width'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_edit_title'       => ($mode=='create' ? $LANG_MG01['create_album'] : $LANG_MG01['edit_album']),
        's_form_action'         => $actionURL,
        'lang_image_skin'       => $LANG_MG01['image_skin'],
        'lang_album_skin'       => $LANG_MG01['album_skin'],
        'lang_display_skin'     => $LANG_MG01['display_skin'],
        'lang_album_edit_help'  => $LANG_MG01['album_edit_help'],
        'lang_title'            => $LANG_MG01['title'],
        'lang_podcast'			=> $LANG_MG01['podcast'],
        'lang_mp3ribbon'        => $LANG_MG01['mp3ribbon'],
        'lang_rsschildren'      => $LANG_MG01['rsschildren'],
        'lang_parent_album'     => $LANG_MG01['parent_album'],
        'lang_description'      => $LANG_MG01['description'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_delete'           => $LANG_MG01['delete'],
        'lang_comments'         => $LANG_MG01['comments_prompt'],
        'lang_enable_exif'      => $LANG_MG01['enable_exif'],
        'lang_enable_ratings'   => $LANG_MG01['enable_ratings'],
        'lang_ss_enable'        => $LANG_MG01['ss_enable'],
        'lang_sf_enable'        => $LANG_MG01['sf_enable'],
        'lang_tn_size'          => $LANG_MG01['tn_size'],
        'lang_rows'             => $LANG_MG01['rows'],
        'lang_columns'          => $LANG_MG01['columns'],
        'lang_av_play_album'    => $LANG_MG01['av_play_album'],
        'lang_av_play_options'  => $LANG_MG01['av_play_options'],
        'lang_attached_thumbnail' => $LANG_MG01['attached_thumbnail'],
        'lang_thumbnail'        => $LANG_MG01['thumbnail'],
        'lang_album_attributes' => $LANG_MG01['album_attributes'],
        'lang_album_cover'      => $LANG_MG01['album_cover'],
        'lang_enable_views'     => $LANG_MG01['enable_views'],
        'lang_enable_keywords'  => $LANG_MG01['enable_keywords'],
        'lang_enable_html'      => $LANG_MG01['htmlallowed'],
        'lang_enable_album_views'=> $LANG_MG01['enable_album_views'],
        'lang_enable_sort'      => $LANG_MG01['enable_sort'],
        'lang_enable_rss'       => $LANG_MG01['enable_rss'],
        'lang_enable_postcard'  => $LANG_MG01['enable_postcard'],
        'lang_albums_first'     => $LANG_MG01['albums_first'],
        'lang_full_display'     => $LANG_MG01['full_display'],
        'lang_display_image_size' => $LANG_MG01['display_image_size'],
        'lang_album_sort'       => $LANG_MG01['default_album_sort'],
        'lang_watermark'        => $LANG_MG01['watermark'],
        'lang_wm_auto'          => $LANG_MG01['watermark_auto'],
        'lang_wm_opacity'       => $LANG_MG01['watermark_opacity'],
        'lang_wm_location'      => $LANG_MG01['watermark_location'],
        'lang_wm_id'            => $LANG_MG01['watermark_image'],
        'lang_unlimited'        => $LANG_MG01['zero_unlimited'],
        'lang_display_album_desc' => $LANG_MG01['display_album_desc'],
        'lang_filename_title'   => $LANG_MG01['filename_title'],
        'lang_media_attributes' => $LANG_MG01['media_attributes'],
        'lang_theme_select'		=> $LANG_MG01['album_theme'],
    ));

    if ( $A['enable_html'] == 1 ) {
//    if ( $_MG_CONF['htmlallowed'] == 1 ) {
        $T->set_var('allowed_html',COM_allowedHTML(SEC_getUserPermissions(),false,'mediagallery','album_title'));
    }

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}

function MG_quickCreate( $parent, $title, $desc='' ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST;

    if ( $parent == 0 ) {
        $result = DB_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name LIKE 'mediagallery Admin'");
        $row = DB_fetchArray($result);
        $grp_id = $row['grp_id'];
        $mod_grp_id = $row['grp_id'];
    } else {
        $grp_id = $MG_albums[$parent]->group_id;
        $mod_grp_id = $MG_albums[$parent]->mod_group_id;
    }

    $album = new mgAlbum();

//    if ($_MG_CONF['htmlallowed'] == 1 ) {
        $album->title       = $title;
        $album->description = $desc;
//    } else {
//        $album->title       = htmlspecialchars(strip_tags(COM_checkWords($title)));
//        $album->description = htmlspecialchars(strip_tags(COM_checkWords($desc)));
//    }
    if ($album->title == "" ) {
        return -1;
    }

    $album->parent          = $parent;
    $album->group_id        = $grp_id;
    $album->owner_id        = $_USER['uid'];
    $album->mod_group_id    = $mod_grp_id;

    // simple check to see if we can create off the album root...
    if (!SEC_hasRights('mediagallery.admin')) {
        if ( $album->parent == $_MG_CONF['member_album_root'] ) {
            if ( $_MG_CONF['member_create_new'] == 0 ) {
                return -1;
            }
        }
    }

    // final permission check to make sure we have the proper rights to create here....
    if ( $album->parent == 0 && !$_MG_CONF['member_albums'] == 1 && !$_MG_CONF['member_album_root'] == 0 ) {
        // see if we are mediagallery.admin
        if (!SEC_hasRights('mediagallery.admin')) {
            COM_errorLog("MediaGallery: Someone has tried to illegally save a Media Gallery Album in Root.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
    } elseif ($album->parent != 0 ) {
        if ( !isset($MG_albums[$album->parent]->id )) {    // does not exist...
            COM_errorLog("MediaGallery: Someone has tried to save a album to non-existent parent album.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        } else {
            if ( $MG_album[$album->parent]->access != 3 && !SEC_hasRights('mediagallery.admin') && !$_MG_CONF['member_albums'] && !$_MG_CONF['member_album_root'] == $MG_album[$album->parent]->id) {
                COM_errorLog("MediaGallery: Someone has tried to illegally save a Media Gallery Album.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
                return(MG_genericError($LANG_MG00['access_denied_msg']));
            }
        }
    }

    if ( $album->isMemberAlbum() ) { // if a new album, set the member album defaults since we are a non-admin
        $album->perm_owner        = $_MG_CONF['member_perm_owner'];
        $album->perm_group        = $_MG_CONF['member_perm_group'];
        $album->perm_members      = $_MG_CONF['member_perm_members'];
        $album->perm_anon         = $_MG_CONF['member_perm_anon'];
        $album->enable_random     = $_MG_CONF['member_enable_random'];
        $album->max_image_height  = $_MG_CONF['member_max_height'];
        $album->max_image_width   = $_MG_CONF['member_max_width'];
        $album->max_filesize      = $_MG_CONF['member_max_filesize'];
        $album->member_uploads    = $_MG_CONF['member_uploads'];
        $album->moderate          = $_MG_CONF['member_moderate'];
        $album->email_mod         = $_MG_CONF['member_email_mod'];
        $album->valid_formats     = $_MG_CONF['member_valid_formats'];
    }

    $album->id              = $album->createAlbumID();
    $album->order           = $album->getNextSortOrder();
    $album->saveAlbum();
    $aid = $album->id;

    MG_initAlbums(1);

    if ( !function_exists('MG_buildFullRSS') ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    }
    MG_buildFullRSS( );
    MG_buildAlbumRSS( $aid );
    return $aid;
}


/**
* saves the specified album information
*
* @param    int     album_id    album_id to edit
* @return   string              HTML
*
*/
function MG_saveAlbum( $album_id, $actionURL='' ) {
    global $_DB_dbms, $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST;

    $update = 0;

    if ( isset($_POST['album_id']) ) {
        $aid                    = COM_applyFilter($_POST['album_id'],true);
    } else {
        $aid = 0;
    }
    if (isset($_POST['force_child_update']) ) {
        $forceChildPermUpdate   = COM_applyFilter($_POST['force_child_update'],true);
    } else {
        $forceChildPermUpdate = 0;
    }
    $thumb                  = $_FILES['thumbnail'];
    $thumbnail              = $thumb['tmp_name'];
    if ( isset($_POST['attach_tn']) ) {
        $att                = COM_applyFilter($_POST['attach_tn']);
    } else {
        $att = 0;
    }

    if ( $aid > 0 ) {  // should be 0 or negative 1 for create
        $album              = $MG_albums[$aid];
        $oldparent          = $album->parent;
        $old_tn_attached    = $album->tn_attached;
        $old_featured       = $album->featured;
        $update             = 1;
    } else {
        $album              = new mgAlbum();
        $album->id          = $aid;
        $update             = 0;
        $old_tn_attached    = 0;
    }

    if ( isset($_POST['enable_html']) ) {
        $album->enable_html     = COM_applyFilter($_POST['enable_html'],true);
    } else {
        $album->enable_html = 0;
    }

    if ($album->enable_html == 1 ) {
        $album->title       = COM_checkHTML(COM_killJS($_POST['album_name']));
        $album->description = COM_checkHTML(COM_killJS($_POST['album_desc']));
    } else {
        $album->title       = htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($_POST['album_name']))));
        $album->description = htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($_POST['album_desc']))));
    }
    if ($album->title == "" ) {
        return(MG_errorHandler( "You must enter an Album Name" ));
    }
    $album->parent              = COM_applyFilter($_POST['parentaid'],true); // we should not need this
    if ( isset($_POST['hidden']) ) {
        $album->hidden              = COM_applyFilter($_POST['hidden'],true);
    } else {
        $album->hidden = 0;
    }
    $album->cover               = COM_applyFilter($_POST['cover']);
    $album->cover_filename      = COM_applyFilter($_POST['album_cover_filename']);
    if ( isset($_POST['enable_album_views']) ) {
        $album->enable_album_views  = COM_applyFilter($_POST['enable_album_views'],true);
    } else {
        $album->enable_album_views = 0;
    }
    $album->image_skin          = COM_applyFilter($_POST['skin']);
    $album->album_skin          = COM_applyFilter($_POST['askin']);
    $album->display_skin        = COM_applyFilter($_POST['dskin']);
    if ( isset($_POST['display_album_desc']) ) {
        $album->display_album_desc  = COM_applyFilter($_POST['display_album_desc'],true);
    } else {
        $album->display_album_desc = 0;
    }
    if ( isset($_POST['enable_comments']) ) {
        $album->enable_comments     = COM_applyFilter($_POST['enable_comments'],true);
    } else {
        $album->enable_comments     = 0;
    }
    $album->exif_display        = COM_applyFilter($_POST['enable_exif'],true);
    if ( isset($_POST['enable_rating']) ) {
        $album->enable_rating       = COM_applyFilter($_POST['enable_rating'],true);
    } else {
        $album->enable_rating       = 0;
    }
    $album->playback_type       = COM_applyFilter($_POST['playback_type'],true);
    $album->tn_attached         = isset($_POST['attach_tn']) ? COM_applyFilter($_POST['attach_tn'],true) : 0;
    $album->enable_slideshow    = COM_applyFilter($_POST['enable_slideshow'],true);
    if ( isset($_POST['enable_random']) ) {
        $album->enable_random       = COM_applyFilter($_POST['enable_random'],true);
    } else {
        $album->enable_random = 0;
    }
//    if ( isset($_POST['enable_shutterfly'] ) ) {
//        $album->enable_shutterfly   = COM_applyFilter($_POST['enable_shutterfly'],true);
//    } else {
        $album->enable_shutterfly = 0;
//    }
    if ( isset($_POST['enable_views']) ) {
        $album->enable_views        = COM_applyFilter($_POST['enable_views'],true);
    } else {
        $album->enable_views = 0;
    }
    if ( isset($_POST['enable_keywords']) ) {
        $album->enable_keywords     = COM_applyFilter($_POST['enable_keywords'],true);
    } else {
        $album->enable_keywords = 0;
    }
    if ( isset($_POST['enable_sort']) ) {
        $album->enable_sort         = COM_applyFilter($_POST['enable_sort'],true);
    } else {
        $album->enable_sort = 0;
    }
    if ( isset($_POST['enable_rss']) ) {
        $album->enable_rss          = COM_applyFilter($_POST['enable_rss'],true);
    } else {
        $album->enable_rss = 0;
    }
    $album->enable_postcard     = COM_applyFilter($_POST['enable_postcard'],true);
    if ( isset($_POST['albums_first']) ) {
        $album->albums_first        = COM_applyFilter($_POST['albums_first'],true);
    } else {
        $album->albums_first = 0;
    }
    if ( isset($_POST['allow_download'] ) ) {
        $album->allow_download      = COM_applyFilter($_POST['allow_download'],true);
    } else {
        $album->allow_download = 0;
    }
    if ( isset($_POST['usealternate'] ) ) {
        $album->useAlternate    = COM_applyFilter($_POST['usealternate'],true);
    } else {
        $album->useAlternate    = 0;
    }
    $album->full                = COM_applyFilter($_POST['full_display'],true);
    $album->tn_size             = COM_applyFilter($_POST['tn_size'],true);
    if ( isset($_POST['max_image_height'])) $album->max_image_height = COM_applyFilter($_POST['max_image_height'],true);
    if ( isset($_POST['max_image_width']))  $album->max_image_width = COM_applyFilter($_POST['max_image_width'],true);
    if ( isset($_POST['max_filesize'])) $album->max_filesize = COM_applyFilter($_POST['max_filesize'],true);
    if ( $album->max_filesize != 0 ) {
        $album->max_filesize = $album->max_filesize * 1024;
    }
    $album->display_image_size  = COM_applyFilter($_POST['display_image_size'],true);
    $album->display_rows        = COM_applyFilter($_POST['display_rows'],true);
    $album->display_columns     = COM_applyFilter($_POST['display_columns'],true);
    $album->skin				= COM_applyFilter($_POST['album_theme']);

    if ( isset($_POST['filename_title']) ) {
        $album->filename_title      = COM_applyFilter($_POST['filename_title'],true);
    } else {
        $album->filename_title = 0;
    }
    $album->shopping_cart       = 0;
    if ( isset($_POST['wm_auto']) ) {
        $album->wm_auto             = COM_applyFilter($_POST['wm_auto'],true);
    } else {
        $album->wm_auto = 0;
    }
    $album->wm_id               = COM_applyFilter($_POST['wm_id']);
    $album->wm_opacity          = COM_applyFilter($_POST['wm_opacity'],true);
    $album->wm_location         = COM_applyFilter($_POST['wm_location'],true);
    $album->album_sort_order    = COM_applyFilter($_POST['album_sort_order'],true);
    if ( isset($_POST['uploads']) ) {
        $album->member_uploads      = COM_applyFilter($_POST['uploads'],true);
    } else {
        $album->member_uploads = 0;
    }
    if ( isset($_POST['moderate']) ) {
        $album->moderate            = COM_applyFilter($_POST['moderate'],true);
    } else {
        $album->moderate = 0;
    }
    if ( isset($_POST['email_mod']) ) {
        $album->email_mod           = COM_applyFilter($_POST['email_mod'],true);
    } else {
        $album->email_mod = 0;
    }
    if ( isset($_POST['podcast']) ) {
        $album->podcast				= COM_applyFilter($_POST['podcast'],true);
    } else {
        $album->podcast = 0;
    }
    if ( isset($_POST['mp3ribbon']) ) {
        $album->mp3ribbon           = COM_applyFilter($_POST['mp3ribbon'],true);
    } else {
        $album->mp3ribbon = 0;
    }
    if ( isset($_POST['rsschildren']) ) {
        $album->rssChildren         = COM_applyFilter($_POST['rsschildren'],true);
    } else {
        $album->rssChildren = 0;
    }
    if ( isset($_POST['tnheight']) ) {
        $album->tnHeight = COM_applyFilter($_POST['tnheight'],true);
        if ( $album->tnHeight == 0 ) {
            $album->tnHeight = 200;
        }
    } else {
        $album->tnHeight = 200;
    }
    if ( isset($_POST['tnwidth']) ) {
        $album->tnWidth = COM_applyFilter($_POST['tnwidth'],true);
        if ( $album->tnWidth == 0 ) {
            $album->tnWidth = 200;
        }
    } else {
        $album->tnWidth = 200;
    }

    if (SEC_hasRights('mediagallery.admin')) {
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
        $album->valid_formats       = ($format_jpg + $format_png + $format_tif + $format_gif + $format_bmp + $format_tga + $format_psd + $format_mp3 + $format_ogg + $format_asf + $format_swf + $format_mov + $format_mp4 + $format_mpg + $format_zip + $format_other + $format_flv + $format_rflv + $format_emb);

        if ( isset($_POST['featured']) ) {
            $album->featured            = COM_applyFilter($_POST['featured'],true);         // admin only
        } else {
            $album->featured = 0;
        }
        $album->cbposition          = COM_applyFilter($_POST['featureposition'],true);  // admin only
        $album->cbpage              = COM_applyFilter($_POST['featurepage']);           // admin only
        $album->group_id            = isset($_POST['group_id']) ? COM_applyFilter($_POST['group_id']) : 0;                               // admin only
        $album->mod_group_id        = isset($_POST['mod_id']) ? COM_applyFilter($_POST['mod_id'],true) : 0;           // admin only
        $perm_owner                 = isset($_POST['perm_owner']) ? $_POST['perm_owner'] : 0;                             // admin only
        $perm_group                 = isset($_POST['perm_group']) ? $_POST['perm_group'] : 0;                             // admin only
        $perm_members               = isset($_POST['perm_members']) ? $_POST['perm_members'] : 0;
        $perm_anon                  = isset($_POST['perm_anon']) ? $_POST['perm_anon'] : 0;
        list($album->perm_owner,$album->perm_group,$album->perm_members,$album->perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);
    } else {
        $perm_owner                 = $album->perm_owner; // already set by existing album?
        $perm_group                 = $album->perm_group; // already set by existing album?
        if ( $update == 0 ) {
            if (isset($MG_albums[$album->parent]->group_id ) ) {
                $grp_id = $MG_albums[$album->parent]->group_id;
                $album->group_id = $grp_id;
            } else {
                $gresult = DB_query("SELECT grp_id FROM {$_TABLES['groups']} WHERE grp_name LIKE 'mediagallery Admin'");
                $grow = DB_fetchArray($gresult);
                $grp_id = $grow['grp_id'];

                $album->group_id            = $grp_id;  // only do these two if create....
            }
            $album->mod_group_id        = $_MG_CONF['member_mod_group_id'];
            if ( $album->mod_group_id == '' || $album->mod_group_id < 1 ) {
                $album->mod_group_id = $grp_id;
            }
        }
        $perm_members = 0;
        $perm_anon = 0;
        if ( isset($_POST['perm_members'])) $perm_members = $_POST['perm_members'];
        if ( isset($_POST['perm_anon'])) $perm_anon = $_POST['perm_anon'];
        list($junk1,$junk2,$album->perm_members,$album->perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);
    }
    if ( isset($_POST['owner_id']) ) {
        $album->owner_id            = COM_applyFilter($_POST['owner_id']);
    } else {
        $album->owner_id = 2;
    }

    // simple check to see if we can create off the album root...
    if (!SEC_hasRights('mediagallery.admin')) {
        if ( $album->parent == $_MG_CONF['member_album_root'] && $update == 0 ) {
            if ( $_MG_CONF['member_create_new'] == 0 ) {
                return(MG_errorHandler( "Cannot create a new album off the member root, please select a new parent album" ));
            }
        }
    }

    // final permission check to make sure we have the proper rights to create here....
    if ( $album->parent == 0 && $update == 0 && !$_MG_CONF['member_albums'] == 1 && !$_MG_CONF['member_album_root'] == 0 ) {
        // see if we are mediagallery.admin
        if (!SEC_hasRights('mediagallery.admin')) {
            COM_errorLog("MediaGallery: Someone has tried to illegally save a Media Gallery Album in Root.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
    } elseif ($album->parent != 0 ) {
        if ( !isset($MG_albums[$album->parent]->id )) {    // does not exist...
            COM_errorLog("MediaGallery: Someone has tried to save a album to non-existent parent album.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        } else {
            if ( $MG_albums[$album->parent]->access != 3 && !SEC_hasRights('mediagallery.admin') && !$_MG_CONF['member_albums'] && !($_MG_CONF['member_album_root'] == $MG_album[$album->parent]->id)) {
                COM_errorLog("MediaGallery: Someone has tried to illegally save a Media Gallery Album.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
                return(MG_genericError($LANG_MG00['access_denied_msg']));
            }
        }
    }

    if ($old_tn_attached == 0 && $album->tn_attached == 1 && $thumb['tmp_name'] == '') {
        $album->tn_attached = 0;
    }

    if ($old_tn_attached == 1 && $album->tn_attached == 0 ) {
        $remove_old_tn = 1;
    } else {
        $remove_old_tn = 0;
    }
    if ( $thumb['tmp_name'] != '' && $album->tn_attached == 1  ) {
        $thumbnail  = $thumb['tmp_name'];
        $attachtn = 1;
    } else {
        $attachtn = 0;
    }

    // pull the watermark id associated with the filename...

    if ( $album->wm_id == 'blank.png' ) {
        $wm_id = 0;
    } else {
        $wm_id = DB_getItem($_TABLES['mg_watermarks'],'wm_id','filename="' . DB_escapeString($album->wm_id) . '"');
    }
    if ( $wm_id == '' )
        $wm_id = 0;

    if ( $wm_id == 0 ) {
        $album->wm_auto = 0;
    }
    $album->wm_id = $wm_id;

    // handle new featured albums

    if (SEC_hasRights('mediagallery.admin')) {
        if ( $album->featured ) {
            // check for other featured albums, we can only have one
            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE featured=1 AND cbpage='" . DB_escapeString($album->cbpage) . "'";
            $result = DB_query($sql);
            $nRows  = DB_numRows($result);
            if ( $nRows > 0 ) {
                $row    = DB_fetchArray($result);
                $sql    = "UPDATE {$_TABLES['mg_albums']} SET featured=0 WHERE album_id=" . $row['album_id'];
                DB_query($sql);
            }
        }
    } else { // if a new album, set the member album defaults since we are a non-admin
        if ($album->isMemberAlbum() && $update == 0) {
            $album->perm_owner        = $_MG_CONF['member_perm_owner'];
            $album->perm_group        = $_MG_CONF['member_perm_group'];
            $album->enable_random     = $_MG_CONF['member_enable_random'];
            $album->max_image_height  = $_MG_CONF['member_max_height'];
            $album->max_image_width   = $_MG_CONF['member_max_width'];
            $album->max_filesize      = $_MG_CONF['member_max_filesize'];
            $album->member_uploads    = $_MG_CONF['member_uploads'];
            $album->moderate          = $_MG_CONF['member_moderate'];
            $album->email_mod         = $_MG_CONF['member_email_mod'];
            $album->valid_formats     = $_MG_CONF['member_valid_formats'];
        }
    }

    $album->title = substr($album->title,0,254);
    if ( $_DB_dbms == "mssql" ) {
        $album->description = substr($album->description,0,1500);
    }

    if ( $album->last_update == '' ) {
        $album->last_update = 0;
    }
    $album->last_update = intval($album->last_update);

    if ( $album->id < 1 ) {
        $album->id = $album->createAlbumID( );
        $aid = $album->id;
        $album->order = $album->getNextSortOrder();
    }

    if ( $album->id == 0 ) {
        COM_errorLog("MediaGallery: Internal Error - album_id = 0 - Contact mark@glfusion.org  ");
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }
    $album->saveAlbum();
    $album->updateChildPermissions($forceChildPermUpdate);

    // now handle the attached cover...

    if ( $attachtn == 1 ) {
        if ( !function_exists('MG_getFile') ) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
        }
        $media_filename = $_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $album->id;
        MG_attachThumbnail( $album->id,$thumbnail, $media_filename );
    }

    if ($remove_old_tn == 1 ) {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $album->id . $ext) ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $album->id . $ext);
                break;
            }
        }
    }

    MG_initAlbums(1);

    // do any album sorting here...

    if ( isset($MG_albums[$aid]) && $MG_albums[$aid]->parent == 0 ) {
        switch( $MG_albums[$aid]->album_sort_order ) {
            case 0 :
                break;
            case 3 : // upload, asc
                MG_staticSortAlbum( $aid, 2, 1, 0 );
                break;
            case 4 :  // upload, desc
                MG_staticSortAlbum( $aid, 2, 0, 0 );
                break;
            case 5 :  // title, asc
                MG_staticSortAlbum( $aid, 0, 1, 0 );
                break;
            case 6 :  // title, desc
                MG_staticSortAlbum( $aid, 0, 0, 0 );
                break;
            case 7 :  // rating, desc
                MG_staticSortAlbum( $aid, 3, 0, 0 );
                break;
            case 8 :  // rating, desc
                MG_staticSortAlbum( $aid, 3, 1, 0 );
                break;
            default : // skip it...
                break;
        }
    } else {
        // not a root album...
        switch( $MG_albums[$MG_albums[$aid]->parent]->album_sort_order ) {
            case 0 :
                break;
            case 3 : // upload, asc
                MG_staticSortAlbum( $MG_albums[$aid]->parent, 2, 1, 0 );
                break;
            case 4 :  // upload, desc
                MG_staticSortAlbum( $MG_albums[$aid]->parent, 2, 0, 0 );
                break;
            case 5 :  // title, asc
                MG_staticSortAlbum( $MG_albums[$aid]->parent, 0, 1, 0 );
                break;
            case 6 :  // title, desc
                MG_staticSortAlbum( $MG_albums[$aid]->parent, 0, 0, 0 );
                break;
            case 7 :  // rating, desc
                MG_staticSortAlbum( $MG_albums[$aid]->parent, 3, 0, 0 );
                break;
            case 8 :  // rating, desc
                MG_staticSortAlbum( $MG_albums[$aid]->parent, 3, 1, 0 );
                break;
            default : // skip it...
                break;
        }
        // now call it for myself to sort my subs
        switch( $MG_albums[$aid]->album_sort_order ) {
            case 0 :
                break;
            case 3 : // upload, asc
                MG_staticSortAlbum( $aid, 2, 1, 0 );
                break;
            case 4 :  // upload, desc
                MG_staticSortAlbum( $aid, 2, 0, 0 );
                break;
            case 5 :  // title, asc
                MG_staticSortAlbum( $aid, 0, 1, 0 );
                break;
            case 6 :  // title, desc
                MG_staticSortAlbum( $aid, 0, 0, 0 );
                break;
            case 7 :  // rating, desc
                MG_staticSortAlbum( $aid, 3, 0, 0 );
                break;
            case 8 :  // rating, desc
                MG_staticSortAlbum( $aid, 3, 1, 0 );
                break;
            default : // skip it...
                break;
        }
    }

    if ( !function_exists('MG_buildFullRSS') ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    }
    MG_buildFullRSS( );
    MG_buildAlbumRSS( $album->id );

    $actionURL = $_MG_CONF['site_url'] . '/album.php?aid=' . $album->id;
    echo COM_refresh($actionURL);
    exit;
}

function MG_staticSortAlbum($startaid, $sortfield, $sortorder, $process_subs) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03;

    switch ($sortfield) {
        case '0' :  // album title
            $sql_sort_by = " ORDER BY album_title ";
            break;
        case '1' :  // media_count
            $sql_sort_by = " ORDER BY media_count ";
            break;
        case '2' : // last_update
            $sql_sort_by = " ORDER BY last_update ";
            break;
        case '3' : // rating
            $sql_sort_by = " ORDER BY media_rating ";
            break;
        default :
            $sql_sort_by = " ORDER BY album_title ";
            break;
    }

    switch( $sortorder ) {
        case '0' :  // ascending
            $sql_order = " DESC";
            break;
        case '1' :  // descending
            $sql_order = " ASC";
            break;
        default:
            $sql_order = " ASC";
            break;
    }

    if ( $process_subs == 0 ) {
        $sql = "SELECT album_id,album_order FROM {$_TABLES['mg_albums']} WHERE album_parent=" . intval($startaid) . " " . $sql_sort_by . $sql_order;

        $order = 10;
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        for ($x = 0; $x < $numRows; $x++ ) {
            $row = DB_fetchArray($result);
            $album_id[$x] = $row['album_id'];
            $album_order[$x] = $order;
            $order += 10;
        }

        $album_count = $numRows;

        for ($x = 0; $x < $album_count; $x++ ) {
            $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_order=" . $album_order[$x] .
                    " WHERE album_id=" . $album_id[$x];
            $res = DB_query($sql);
        }
    } else {
        MG_staticSortChildAlbum($startaid, $sql_order, $sql_sort_by);
    }
    return;
}

function MG_staticSortChildAlbum($startaid, $sql_order, $sql_sort_by ) {
    global $MG_albums, $_TABLES;

    $sql = "SELECT album_id,album_order FROM {$_TABLES['mg_albums']} WHERE album_parent=" . $startaid . " " . $sql_sort_by . $sql_order;

    $order = 10;
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    for ($x = 0; $x < $numRows; $x++ ) {
        $row = DB_fetchArray($result);
        $album_id[$x] = $row['album_id'];
        $album_order[$x] = $order;
        $order += 10;
    }

    $album_count = $numRows;

    for ($x = 0; $x < $album_count; $x++ ) {
        $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_order=" . $album_order[$x] .
                " WHERE album_id=" . $album_id[$x];
        $res = DB_query($sql);
    }

    if ( !empty($MG_albums[$startaid]->children)) {
        $children = $MG_albums[$startaid]->getChildren();
        foreach($children as $child) {
            MG_staticSortChildAlbum($MG_albums[$child]->id,$sql_order, $sql_sort_by);
        }
    }
}
?>