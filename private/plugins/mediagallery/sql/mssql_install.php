<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | SQL necessary to install Media Gallery -MSSQL                             |
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
$_SQL['mg_start'] = "begin tran";

$_SQL['mg_albums'] = "CREATE TABLE [dbo].[{$_TABLES['mg_albums']}](
	[album_id] [int] IDENTITY(1,1) NOT NULL,
	[album_title] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[album_desc] [varchar] (2000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[album_order] [int] NULL,
	[skin] [varchar] (255) NOT NULL default ('default'),
	[album_parent] [int] NOT NULL,
	[hidden] [smallint] NULL,
	[podcast] [smallint] NOT NULL DEFAULT ( '0' ),
	[mp3ribbon] [smallint] NOT NULL DEFAULT ( '0' ),
	[album_cover] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[album_cover_filename] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_count] [int] NULL,
	[album_disk_usage] [int] NULL,
	[last_update] [int] NOT NULL,
	[album_views] [int] NULL,
	[display_album_desc] [smallint] NULL,
	[enable_album_views] [smallint] NULL,
    [album_view_type] [smallint] NULL,
    [image_skin] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT ('mgShadow'),
    [album_skin] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT ('mgAlbum'),
    [display_skin] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT ('mgShadow'),
	[enable_comments] [smallint] NULL,
	[exif_display] [smallint] NULL,
	[enable_rating] [smallint] NULL,
	[playback_type] [smallint] NULL,
	[usealternate] [smallint] NOT NULL DEFAULT ( '0' ),
	[tn_attached] [smallint] NULL,
    [tnheight] INT NULL,
    [tnwidth] INT NULL,
	[enable_slideshow] [smallint] NULL,
	[enable_random] [smallint] NULL,
	[enable_shutterfly] [smallint] NULL,
	[enable_views] [smallint] NULL,
	[enable_keywords] [smallint] NULL,
	[enable_sort] [smallint] NULL,
	[enable_rss] [smallint] NULL,
	[enable_postcard] [smallint] NULL,
	[albums_first] [smallint] NULL,
	[allow_download] [smallint] NULL,
	[full_display] [smallint] NULL,
	[tn_size] [smallint] NULL,
	[max_image_height] [int] NULL,
	[max_image_width] [int] NULL,
	[max_filesize] [int] NULL,
	[display_image_size] [smallint] NULL,
	[display_rows] [smallint] NULL,
	[display_columns] [smallint] NULL,
	[valid_formats] [int] NULL,
	[filename_title] [smallint] NULL,
	[shopping_cart] [smallint] NULL,
	[rsschildren] [smallint] NOT NULL DEFAULT ( '0' ),
	[wm_auto] [smallint] NULL,
	[wm_id] [int] NULL,
	[opacity] [int] NULL,
	[wm_location] [smallint] NULL,
	[album_sort_order] [smallint] NULL,
	[member_uploads] [smallint] NULL,
	[moderate] [smallint] NULL,
	[email_mod] [smallint] NULL,
	[featured] [smallint] NULL,
	[cbposition] [smallint] NULL,
	[cbpage] [varchar](20) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[owner_id] [int] NULL,
	[group_id] [int] NULL,
	[mod_group_id] [int] NULL,
	[perm_owner] [smallint] NULL,
	[perm_group] [smallint] NULL,
	[perm_members] [smallint] NULL,
	[perm_anon] [smallint] NULL
) ON [PRIMARY]
";

$_SQL['mg_albums_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_albums']}] ADD
    CONSTRAINT [PK_mg_albums] PRIMARY KEY  CLUSTERED
    (
        [album_id] ASC
    )  ON [PRIMARY]
";

$_SQL['mg_session_items'] = "CREATE TABLE [dbo].[{$_TABLES['mg_session_items']}](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[session_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[mid] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[aid] [int] NULL,
	[data] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data2] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data3] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[status] [smallint] NULL
) ON [PRIMARY]
";
$_SQL['mg_session_items_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_session_items']}] ADD
    CONSTRAINT [PK_mg_batch_session_items] PRIMARY KEY CLUSTERED
    (
	    [id] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_session_items2'] = "CREATE TABLE [dbo].[{$_TABLES['mg_session_items2']}](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[data1] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data2] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data3] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data4] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data5] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data6] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data7] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data8] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[data9] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL
) ON [PRIMARY]
";
$_SQL['mg_session_items2_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_session_items2']}] ADD
     CONSTRAINT [PK_mg_batch_session_items2] PRIMARY KEY CLUSTERED
    (
	    [id] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_session_log'] = "CREATE TABLE [dbo].[{$_TABLES['mg_session_log']}](
	[session_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[session_log] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL
) ON [PRIMARY]
";
$_SQL['mg_session_log_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_session_log']}] ADD
    CONSTRAINT [PK_mg_batch_session_log] PRIMARY KEY CLUSTERED
    (
	    [session_id] ASC
    ) ON [PRIMARY]
";


$_SQL['mg_sessions'] = "CREATE TABLE [dbo].[{$_TABLES['mg_sessions']}](
	[session_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[session_uid] [int] NULL,
	[session_description] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_status] [smallint] NULL,
	[session_cycles] [smallint] NULL,
	[session_action] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_origin] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_start_time] [int] NULL,
	[session_end_time] [int] NULL,
	[session_var0] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_var1] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_var2] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_var3] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[session_var4] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL
) ON [PRIMARY]
";
$_SQL['mg_sessions_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_sessions']}] ADD
    CONSTRAINT [PK_mg_batch_sessions] PRIMARY KEY CLUSTERED
    (
	    [session_id] ASC
    ) ON [PRIMARY]
";


$_SQL['mg_category'] = "CREATE TABLE [dbo].[{$_TABLES['mg_category']}](
	[cat_id] [int] NOT NULL,
	[cat_name] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[cat_description] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[cat_order] [int] NULL
) ON [PRIMARY]
";
$_SQL['mg_category_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_category']}] ADD
    CONSTRAINT [PK_mg_category] PRIMARY KEY CLUSTERED
    (
	    [cat_id] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_config']="CREATE TABLE [dbo].[{$_TABLES['mg_config']}](
	[config_name] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[config_value] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL
) ON [PRIMARY]
";
$_SQL['mg_config_index']="ALTER TABLE [dbo].[{$_TABLES['mg_config']}] ADD
    CONSTRAINT [PK_mg_config] PRIMARY KEY CLUSTERED
    (
	    [config_name] ASC
    ) ON [PRIMARY]
";


$_SQL['mg_exif_tags']="CREATE TABLE [dbo].[{$_TABLES['mg_exif_tags']}](
	[name] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[selected] [smallint] NULL
) ON [PRIMARY]
";
$_SQL['mg_exif_tags_index']="ALTER TABLE [dbo].[{$_TABLES['mg_exif_tags']}] ADD
    CONSTRAINT [PK_mg_exif_tags] PRIMARY KEY CLUSTERED
    (
	    [name] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_media']="CREATE TABLE [dbo].[{$_TABLES['mg_media']}](
	[media_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[media_filename] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_original_filename] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_mime_ext] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_exif] [smallint] NULL,
	[mime_type] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_title] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_desc] [varchar] (2000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_keywords] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_time] [int] NULL,
	[media_views] [int] NULL,
	[media_comments] [int] NULL,
	[media_votes] [int] NULL,
	[media_rating] [decimal](4, 2) NULL,
	[media_resolution_x] [int] NOT NULL DEFAULT ('0'),
	[media_resolution_y] [int] NOT NULL DEFAULT ('0'),
    [remote_media] [int] NOT NULL DEFAULT ('0'),
    [remote_url] [varchar] (2000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_tn_attached] [smallint] NULL,
	[media_tn_image] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[include_ss] [smallint] NULL,
	[media_user_id] [int] NULL,
	[media_user_ip] [varchar](14) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_approval] [smallint] NULL,
	[media_type] [smallint] NULL,
	[media_upload_time] [int] NULL,
	[media_category] [int] NULL,
	[media_watermarked] [smallint] NULL,
    [artist] [varchar](255) NULL,
    [album] [varchar](255) NULL,
    [genre] [varchar](255) NULL,
	[v100] [smallint] NULL,
	[maint] [smallint] NULL
) ON [PRIMARY]
";
$_SQL['mg_media_index']="ALTER TABLE [dbo].[{$_TABLES['mg_media']}] ADD
    CONSTRAINT [PK_mg_media] PRIMARY KEY CLUSTERED
    (
	    [media_id] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_media_album_queue']="CREATE TABLE [dbo].[{$_TABLES['mg_media_album_queue']}](
	[album_id] [int] NULL,
	[media_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_order] [int] NULL
) ON [PRIMARY]
";


$_SQL['mg_media_albums']="CREATE TABLE [dbo].[{$_TABLES['mg_media_albums']}](
	[album_id] [int] NOT NULL,
	[media_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_order] [int] NULL
) ON [PRIMARY]
";


$_SQL['mg_mediaqueue']="CREATE TABLE [dbo].[{$_TABLES['mg_mediaqueue']}](
	[media_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[media_filename] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_original_filename] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_mime_ext] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_exif] [smallint] NULL,
	[mime_type] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_title] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_desc] [varchar] (2000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_keywords] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_time] [int] NULL,
	[media_views] [int] NULL,
	[media_comments] [int] NULL,
	[media_votes] [int] NULL,
	[media_rating] [decimal](4, 2) NULL,
	[media_resolution_x] [int] NOT NULL DEFAULT ('0'),
	[media_resolution_y] [int] NOT NULL DEFAULT ('0'),
    [remote_media] [int] NOT NULL DEFAULT ('0'),
    [remote_url] [varchar] (2000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_tn_attached] [smallint] NULL,
	[media_tn_image] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[include_ss] [smallint] NULL,
	[media_user_ip] [varchar](14) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[media_approval] [smallint] NULL,
	[media_type] [smallint] NULL,
	[media_upload_time] [int] NULL,
	[media_category] [int] NULL,
	[media_watermarked] [smallint] NULL,
    [artist] [varchar](255) NULL,
    [album] [varchar](255) NULL,
    [genre] [varchar](255) NULL,
	[v100] [smallint] NULL,
	[maint] [smallint] NULL
) ON [PRIMARY]
";
$_SQL['mg_mediaqueue_index']="ALTER TABLE [dbo].[{$_TABLES['mg_mediaqueue']}] ADD
    CONSTRAINT [PK_mg_media_queue] PRIMARY KEY CLUSTERED
    (
	    [media_id] ASC
    ) ON [PRIMARY]
";


$_SQL['mg_playback_options']="CREATE TABLE [dbo].[{$_TABLES['mg_playback_options']}](
	[media_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[option_name] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[option_value] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL
)
";
$_SQL['mg_playback_options_index']="ALTER TABLE [dbo].[{$_TABLES['mg_playback_options']}] ADD
    CONSTRAINT [PK_mg_playback_options] PRIMARY KEY CLUSTERED
    (
	    [media_id] ASC
    ) ON [PRIMARY]
";


$_SQL['mg_postcard'] = "CREATE TABLE [dbo].[{$_TABLES['mg_postcard']}](
	[pc_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[mid] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[to_name] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[to_email] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[from_name] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[from_email] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[subject] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[message] [varchar] (4000) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[pc_time] [int] NOT NULL DEFAULT ('0'),
	[uid] [int] NOT NULL DEFAULT ('0')
)
";

$_SQL['mg_postcard_index'] = "ALTER TABLE [dbo].[{$_TABLES['mg_postcard']}] ADD
    CONSTRAINT [PK_mg_postcard] PRIMARY KEY CLUSTERED
    (
	    [pc_id] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_sort'] = "CREATE TABLE [dbo].[{$_TABLES['mg_sort']}](
	[sort_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[sort_user] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[sort_query] [varchar] (2000) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[sort_results] [int] NOT NULL DEFAULT ('0'),
	[sort_datetime] [int] NOT NULL DEFAULT ('0'),
    [referer] [varchar] (255) NOT NULL default (''),
    [keywords] [varchar] (255) NOT NULL default ('')
) ON [PRIMARY]
";

$_SQL['mg_usage_tracking']="CREATE TABLE [dbo].[{$_TABLES['mg_usage_tracking']}](
	[time] [int] NOT NULL DEFAULT ('0'),
	[user_id] [int] NOT NULL DEFAULT ('0'),
	[user_name] [varchar](127) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[user_ip] [varchar](16) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[application] [varchar](127) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[album_title] [varchar](127) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[media_title] [varchar](127) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[media_id] [varchar](40) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT ('0')
) ON [PRIMARY]
";

$_SQL['mg_userprefs']="CREATE TABLE [dbo].[{$_TABLES['mg_userprefs']}](
	[uid] [int] NOT NULL DEFAULT ('0'),
	[active] [smallint] NOT NULL DEFAULT ('1'),
	[display_rows] [smallint] NOT NULL DEFAULT ('0'),
	[display_columns] [smallint] NOT NULL DEFAULT ('0'),
	[mp3_player] [smallint] NOT NULL DEFAULT ('-1'),
	[playback_mode] [smallint] NOT NULL DEFAULT ('-1'),
	[tn_size] [smallint] NOT NULL DEFAULT ('-1'),
	[quota] [bigint] NOT NULL DEFAULT ('0'),
	[member_gallery] [int] NOT NULL DEFAULT ('0')
) ON [PRIMARY]
";

$_SQL['mg_userprefs_index']="ALTER TABLE [dbo].[{$_TABLES['mg_userprefs']}] ADD
    CONSTRAINT [PK_mg_userprefs] PRIMARY KEY CLUSTERED
    (
	    [uid] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_watermark']="CREATE TABLE [dbo].[{$_TABLES['mg_watermarks']}](
	[wm_id] [int] NOT NULL DEFAULT ('0'),
	[owner_id] [int] NOT NULL DEFAULT ('0'),
	[filename] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT (''),
	[description] [varchar](255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT ('')
) ON [PRIMARY]
";
$_SQL['mg_watermark_index']="ALTER TABLE [dbo].[{$_TABLES['mg_watermarks']}] ADD
    CONSTRAINT [PK_mg_watermark]  PRIMARY KEY CLUSTERED
    (
	    [wm_id] ASC
    ) ON [PRIMARY]
";

$_SQL['mg_rating'] = "CREATE TABLE [dbo].[{$_TABLES['mg_rating']}] (
  [id] [int] PRIMARY KEY CLUSTERED,
  [ip_address] [varchar] (14) NOT NULL DEFAULT ('0'),
  [uid] [int] NOT NULL DEFAULT ('0'),
  [media_id] [varchar] (40) NOT NULL DEFAULT ('0'),
  [ratingdate] [int] NOT NULL DEFAULT ('0'),
  [owner_id] [int] NOT NULL default ('2')
) ON [PRIMARY]
";

$_SQL['mg_rating_index2'] = "CREATE NONCLUSTERED INDEX [IX_mg_rating_owner_id] ON [dbo].[{$_TABLES['mg_rating']}]
(
	[owner_id] ASC
) ON [PRIMARY]
";

$_SQL['idx1'] = "CREATE UNIQUE NONCLUSTERED INDEX [IX_mg_media_albums] ON [dbo].[{$_TABLES['mg_media_albums']}]
(
	[album_id] ASC,
	[media_id] ASC
) ON [PRIMARY]
";

$_SQL['idx2'] = "CREATE UNIQUE NONCLUSTERED INDEX [IX_mg_media_album_queue] ON [dbo].[{$_TABLES['mg_media_album_queue']}]
(
	[album_id] ASC,
	[media_id] ASC
) ON [PRIMARY]
";

$_SQL['idx3'] = "CREATE NONCLUSTERED INDEX [IX_mg_playback_options] ON [dbo].[{$_TABLES['mg_playback_options']}]
(
	[media_id] ASC
) ON [PRIMARY]
";


$_SQL['idx4'] = "CREATE NONCLUSTERED INDEX [IX_mg_albums_pid] ON [dbo].[{$_TABLES['mg_albums']}]
(
	[album_parent] ASC
) ON [PRIMARY]
";


$_SQL['idx5'] = "CREATE NONCLUSTERED INDEX [IX_mg_albums_lu] ON [dbo].[{$_TABLES['mg_albums']}]
(
	[last_update] ASC
) ON [PRIMARY]
";

$_SQL['idx7'] = "CREATE NONCLUSTERED INDEX [IX_mg_session_items_id] ON [dbo].[{$_TABLES['mg_session_items']}]
(
	[session_id] ASC
) ON [PRIMARY]
";

$_SQL['idx8'] = "CREATE NONCLUSTERED INDEX [IX_mg_session_log_id] ON [dbo].[{$_TABLES['mg_session_log']}]
(
	[session_id] ASC
) ON [PRIMARY]
";

$_SQL['idx9'] = "CREATE NONCLUSTERED INDEX [IX_mg_usage_tracking_time] ON [dbo].[{$_TABLES['mg_usage_tracking']}]
(
	[time] ASC
) ON [PRIMARY]
";

$_SQL['idx10'] = "CREATE NONCLUSTERED INDEX [IX_mg_media_mut] ON [dbo].[{$_TABLES['mg_media']}]
(
	[media_upload_time] ASC
) ON [PRIMARY]
";

$_SQL['idx11'] = "CREATE NONCLUSTERED INDEX [IX_mg_media_queue_mut] ON [dbo].[{$_TABLES['mg_mediaqueue']}]
(
	[media_upload_time] ASC
) ON [PRIMARY]
";


$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ApertureValue', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ShutterSpeedValue', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ISO', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocalLength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Flash', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ACDComment', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('AEWarning', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('AFFocusPosition', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('AFPointSelected', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('AFPointUsed', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Adapter', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Artist', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('BatteryLevel', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('BitsPerSample', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('BlurWarning', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('BrightnessValue', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('CCDSensitivity', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('CameraID', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('CameraSerialNumber', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Color', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ColorMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ColorSpace', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ComponentsConfiguration', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('CompressedBitsPerPixel', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Compression', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ContinuousTakingBracket', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Contrast', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Converter', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Copyright', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('CustomFunctions', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('CustomerRender', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('DateTime', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('DigitalZoom', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('DigitalZoomRatio', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('DriveMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('EasyShooting', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ExposureBiasValue', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ExposureIndex', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ExposureMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ExposureProgram', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FileSource', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FirmwareVersion', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashBias', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashDetails', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashEnergy', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashPixVersion', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashSetting', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FlashStrength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocalPlaneResolutionUnit', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocalPlaneXResolution', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocalPlaneYResolution', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocalUnits', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Focus', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocusMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('FocusWarning', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('GainControl', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageAdjustment', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageDescription', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageHistory', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageLength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageNumber', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageSharpening', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageSize', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageType', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ImageWidth', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('InterColorProfile', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Interlace', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('InteroperabilityIFD.InteroperabilityIndex', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('InteroperabilityIFD.InteroperabilityVersion', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('InteroperabilityIFD.RelatedImageFileFormat', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('InteroperabilityIFD.RelatedImageLength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('InteroperabilityIFD.RelatedImageWidth', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('JPEGTables', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('JpegIFByteCount', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('JpegIFOffset', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('JpegQual', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('LightSource', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('LongFocalLength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Macro', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Make', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ManualFocusDistance', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('MaxApertureValue', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('MeteringMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Model', 1)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Noise', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('NoiseReduction', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Orientation', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('OwnerName', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('PhotometricInterpret', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('PhotoshopSettings', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('PictInfo', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('PictureMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('PlanarConfiguration', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Predictor', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('PrimaryChromaticities', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Quality', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ReferenceBlackWhite', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('RelatedSoundFile', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ResolutionUnit', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('RowsPerStrip', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SamplesPerPixel', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Saturation', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SceneCaptureMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SceneType', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SecurityClassification', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SelfTimer', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SelfTimerMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SensingMethod', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SequenceNumber', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Sharpness', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ShortFocalLength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SlowSync', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Software', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SoftwareRelease', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SpatialFrequencyResponse', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SpecialMode', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SpectralSensitivity', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('StripByteCounts', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('StripOffsets', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubIFDs', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubfileType', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubjectDistance', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubjectLocation', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubsecTime', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubsecTimeDigitized', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('SubsecTimeOriginal', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TIFF/EPStandardID', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TileByteCounts', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TileLength', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TileOffsets', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TileWidth', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TimeZoneOffset', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Tone', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('TransferFunction', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('UserComment', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('Version', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('WhiteBalance', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('WhitePoint', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('YCbCrCoefficients', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('YCbCrPositioning', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('YCbCrSubSampling', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('xResolution', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('yResolution', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ExifImageHeight', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ExifImageWidth', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/SupplementalCategories', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Keywords', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Caption', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/CaptionWriter', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Headline', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/SpecialInstructions', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Category', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Byline', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/BylineTitle', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Credit', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/Source', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/CopyrightNotice', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/ObjectName', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/City', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/ProvinceState', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/CountryName', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/OriginalTransmissionReference', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/DateCreated', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/CopyrightFlag', 0)";
$_SQL[]="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('IPTC/TimeCreated', 0);";

$_SQL['mg_watermark_data']="INSERT INTO {$_TABLES['mg_watermarks']} VALUES (0, 0, 'blank.png', '---');";

$_SQL[] = "
if @@error=0
begin
    commit tran
end

else
begin
    rollback tran
end";
?>