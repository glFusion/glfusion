<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | SQL to set Media Gallery Defaults                                         |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}
// Must set $ftp_path and $tmp_path in main routine before including this file.

$_SQL_DEF[]="DELETE FROM {$_TABLES['mg_config']}";

$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('display_rows', '3');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('album_display_columns', '1');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('album_display_rows', '9');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('display_columns', '3');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('loginrequired', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('graphicspackage', '2');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('graphicspackage_path', '/usr/local/bin/');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('displayblocks',0);";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('usage_tracking',0);";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('dfid',0);";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('htmlallowed','0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('whatsnew','1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('truncate_breadcrumb','0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('jpg_orig_quality', '85');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('jpg_quality', '85');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('whatsnew_time', '7');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_tn_size', '1');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_tn_height', '200');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_tn_width', '200');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mp3_player', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('seperator', '::');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('up_display_rows_enabled',   '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('up_display_columns_enabled','1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('up_mp3_player_enabled',     '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('up_av_playback_enabled',    '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('up_thumbnail_size_enabled', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('tn_jpg_quality',            '85')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ftp_path', '$ftp_path');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('tmp_path', '$tmp_path');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('preserve_filename', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('discard_original', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('verbose', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('disable_whatsnew_comments', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('enable_media_id', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('full_in_popup', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('commentbar', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('title_length', '28')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('profile_hook', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('indexskin', 'mgAlbum')";

$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_podcast', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_comments', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_exif_display', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_rating', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_playback_type', '2')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_slideshow', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_random', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_shutterfly', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_views', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_keywords','1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_display_album_desc', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_album_views', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_sort', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_rss', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_enable_postcard', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_albums_first', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_full_display', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_tn_size', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_tn_height', '200')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_tn_width', '200')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_display_rows', '4')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_display_columns', '3')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_member_uploads', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_moderate', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_email_mod', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_wm_auto', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_wm_id', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_wm_opacity', '10')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_wm_location', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_album_sort_order', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_max_filesize', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_max_image_height', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_max_image_width', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_display_image_size', '2')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_perm_owner', '3')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_perm_group', '3')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_perm_members', '2')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_perm_anon', '2')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_allow_download', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_valid_formats', '983039')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_filename_title', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_image_skin', 'mgShadow')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_album_skin', 'mgAlbum')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_display_skin', 'mgShadow')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_skin', 'default')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_mp3ribbon', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('ad_rsschildren', '1')";

$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('custom_image_height', '412')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('custom_image_width', '550')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('random_width', '120')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('def_refresh_rate', '30')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('def_time_limit', '90')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('def_item_limit', '10')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_autostart', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_enablecontextmenu', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_stretchtofit', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_showstatusbar', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_uimode', 'full')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_playcount', '9999')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_height', '480')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_width', '640')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('asf_bgcolor', '#FFFFFF')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_autoref', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_autoplay', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_controller', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_kioskmode', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_scale', 'tofit')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_loop', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_height', '480')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_width', '640')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mov_bgcolor', '#FFFFFF')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mp3_autostart', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mp3_enablecontextmenu', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mp3_showstatusbar', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mp3_loop', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('mp3_uimode', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_play', '1')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_menu', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_loop', '0')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_quality', 'high')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_scale', 'showall')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_wmode', 'transparent')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_flashvars', '')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_version', '6')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_height', '480')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_width', '640')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_bgcolor', '#FFFFFF')";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('swf_allowscriptaccess', 'sameDomain')";

$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('rss_full_enabled', '1');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('rss_feed_type', 'RSS2.0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('rss_ignore_empty', '1');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('rss_anonymous_only', '1');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('rss_feed_name', 'mgmedia');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('postcard_retention', '7');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('subalbum_select', '0');";

// member albums defaults
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_albums', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_quota', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_auto_create', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_create_new', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_album_root', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_album_archive', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_enable_random', '1');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_max_width', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_max_height', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_max_filesize', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_uploads', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_moderate', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_email_mod', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_perm_owner', '3');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_perm_group', '3');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_perm_members', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_perm_anon', '0');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('member_valid_formats', '65535');";
$_SQL_DEF[]="INSERT INTO {$_TABLES['mg_config']} VALUES ('last_usage_purge', '0');";

// auto tag defaults
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('random_skin','mgShadow')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_border','1')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_align','auto')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_width','0')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_height','0')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_src','tn')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_autoplay','0')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_enable_link','1')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_delay','5')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('at_showtitle','0')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('use_flowplayer','0')";

$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_columns','3')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_rows','4')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_playback_type','0')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_enable_views','1')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('search_enable_rating','1')";
$_SQL_DEF[] = "INSERT INTO {$_TABLES['mg_config']} VALUES ('gallery_only','0')";


?>