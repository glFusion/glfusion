<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | SQL necessary to install Media Gallery                                    |
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
$_SQL['mg_albums'] = "CREATE TABLE {$_TABLES['mg_albums']} (
  `album_id` int(11) NOT NULL default '0',
  `album_title` varchar(255) NOT NULL default '',
  `album_desc` text NOT NULL,
  `album_parent` int(11) NOT NULL default '0',
  `album_order` int(11) NOT NULL default '0',
  `skin` varchar(255) NOT NULL default 'default',
  `hidden` tinyint(4) NOT NULL default '0',
  `podcast` tinyint(4) NOT NULL default '0',
  `mp3ribbon` tinyint(4) NOT NULL default '0',
  `album_cover` varchar(40) NOT NULL default '-1',
  `album_cover_filename` varchar(255) default '',
  `media_count` int(11) unsigned NOT NULL default '0',
  `album_disk_usage` bigint(20) unsigned NOT NULL default '0',
  `last_update` int(11) NOT NULL default '0',
  `album_views` int(11) NOT NULL default '0',
  `enable_album_views` tinyint(4) NOT NULL default '0',
  `album_view_type` tinyint(4) NOT NULL default '0',
  `image_skin` varchar(255) NOT NULL default 'mgShadow',
  `album_skin` varchar(255) NOT NULL default 'mgAlbum',
  `display_skin` varchar(255) NOT NULL default 'mgShadow',
  `enable_comments` tinyint(4) NOT NULL default '0',
  `exif_display` tinyint(4) NOT NULL default '0',
  `enable_rating` tinyint(4) NOT NULL default '0',
  `va_playback` tinyint(4) NOT NULL default '0',
  `playback_type` tinyint(4) NOT NULL default '0',
  `usealternate` tinyint(4) NOT NULL default '0',
  `tn_attached` tinyint(4) NOT NULL default '0',
  `tnheight` INT NOT NULL DEFAULT '0',
  `tnwidth` INT NOT NULL DEFAULT '0',
  `enable_slideshow` tinyint(4) NOT NULL default '0',
  `enable_random` tinyint(4) NOT NULL default '0',
  `enable_shutterfly` tinyint(4) NOT NULL default '0',
  `enable_views` tinyint(4) NOT NULL default '0',
  `enable_keywords` tinyint(4) NOT NULL default '0',
  `display_album_desc` tinyint(4) NOT NULL default '0',
  `enable_sort` tinyint(4) NOT NULL default '0',
  `enable_rss` tinyint(4) NOT NULL default '0',
  `enable_postcard` tinyint(4) NOT NULL default '0',
  `albums_first` tinyint(4) NOT NULL default '1',
  `allow_download` tinyint(4) NOT NULL default '0',
  `full_display` tinyint(4) NOT NULL default '0',
  `tn_size` tinyint(4) NOT NULL default '0',
  `max_image_height` int(11) NOT NULL default '0',
  `max_image_width` int(11) NOT NULL default '0',
  `max_filesize` bigint(20) unsigned NOT NULL default '0',
  `display_image_size` tinyint(4) NOT NULL default '2',
  `display_rows` tinyint(4) NOT NULL default '3',
  `display_columns` tinyint(4) NOT NULL default '3',
  `valid_formats` INT( 11 ) UNSIGNED NOT NULL DEFAULT '65535',
  `filename_title` TINYINT( 4 ) NOT NULL DEFAULT '0',
  `shopping_cart` TINYINT( 4 )  NOT NULL DEFAULT '0',
  `rsschildren` TINYINT( 4 ) NOT NULL DEFAULT '0',
  `wm_auto` tinyint(4) NOT NULL default '0',
  `wm_id` int(11) NOT NULL default '0',
  `opacity` int(11) NOT NULL default '0',
  `wm_location` tinyint(4) NOT NULL default '0',
  `album_sort_order` tinyint(4) NOT NULL default '0',
  `member_uploads` tinyint(4) NOT NULL default '0',
  `moderate` tinyint(4) NOT NULL default '0',
  `email_mod` tinyint(4) NOT NULL default '0',
  `featured` tinyint(4) NOT NULL default '0',
  `cbposition` tinyint(1) NOT NULL default '0',
  `cbpage` varchar(20) NOT NULL default '',
  `owner_id` mediumint(9) NOT NULL default '0',
  `group_id` mediumint(9) NOT NULL default '0',
  `mod_group_id` mediumint(9) NOT NULL default '0',
  `perm_owner` tinyint(1) unsigned NOT NULL default '0',
  `perm_group` tinyint(1) unsigned NOT NULL default '0',
  `perm_members` tinyint(1) unsigned NOT NULL default '0',
  `perm_anon` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`album_id`),
  KEY `album_parent` (`album_parent`),
  KEY `last_update` (`last_update`)
);";

$_SQL['mg_config']="CREATE TABLE {$_TABLES['mg_config']} (
  `config_name` varchar(255) NOT NULL default '',
  `config_value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_name`)
);";

$_SQL['mg_media']="CREATE TABLE {$_TABLES['mg_media']} (
  `media_id` varchar(40) NOT NULL default '0',
  `media_filename` varchar(255) NOT NULL default '',
  `media_original_filename` varchar(255) NOT NULL default '',
  `media_mime_ext` varchar(255) NOT NULL default '',
  `media_exif` tinyint(4) NOT NULL default '1',
  `mime_type` varchar(255) NOT NULL default '',
  `media_title` varchar(255) NOT NULL default '',
  `media_desc` text NOT NULL,
  `media_keywords` varchar(255) NOT NULL default '',
  `media_time` int(11) NOT NULL default '0',
  `media_views` int(11) NOT NULL default '0',
  `media_comments` int(11) NOT NULL default '0',
  `media_votes` int(11) NOT NULL default '0',
  `media_rating` decimal(4,2) NOT NULL default '0.00',
  `media_resolution_x` INT(11) NOT NULL default '0',
  `media_resolution_y` int(11) NOT NULL default '0',
  `remote_media` TINYINT(4) NOT NULL default '0',
  `remote_url` TEXT,
  `media_tn_attached` tinyint(4) NOT NULL default '0',
  `media_tn_image` varchar(255) NOT NULL default '',
  `include_ss` tinyint(4) unsigned NOT NULL default '1',
  `media_user_id` mediumint(9) NOT NULL default '0',
  `media_user_ip` varchar(14) NOT NULL default '',
  `media_approval` tinyint(3) NOT NULL default '0',
  `media_type` tinyint(4) NOT NULL default '0',
  `media_upload_time` int(11) NOT NULL default '0',
  `media_category` int(11) NOT NULL default '0',
  `media_watermarked` tinyint(4) NOT NULL default '0',
  `artist` VARCHAR(255) NOT NULL default '',
  `album` VARCHAR(255) NOT NULL default '',
  `genre` VARCHAR(255) NOT NULL default '',
  `v100` tinyint(4) NOT NULL default '0',
  `maint` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`media_id`),
  KEY `media_upload_time` (`media_upload_time`)
);";

$_SQL['mg_mediaqueue']="CREATE TABLE {$_TABLES['mg_mediaqueue']} (
  `media_id` varchar(40) NOT NULL default '0',
  `media_filename` varchar(255) NOT NULL default '',
  `media_original_filename` varchar(255) NOT NULL default '',
  `media_mime_ext` varchar(255) NOT NULL default '',
  `media_exif` tinyint(4) NOT NULL default '1',
  `mime_type` varchar(255) NOT NULL default '',
  `media_title` varchar(255) NOT NULL default '',
  `media_desc` text NOT NULL,
  `media_keywords` varchar(255) NOT NULL default '',
  `media_time` int(11) NOT NULL default '0',
  `media_views` int(11) NOT NULL default '0',
  `media_comments` int(11) NOT NULL default '0',
  `media_votes` int(11) NOT NULL default '0',
  `media_rating` decimal(4,2) NOT NULL default '0.00',
  `media_resolution_x` INT(11) NOT NULL default '0',
  `media_resolution_y` int(11) NOT NULL default '0',
  `remote_media` TINYINT(4) NOT NULL default '0',
  `remote_url` TEXT,
  `media_tn_attached` tinyint(4) NOT NULL default '0',
  `media_tn_image` varchar(255) NOT NULL default '',
  `include_ss` tinyint(4) unsigned NOT NULL default '1',
  `media_user_id` mediumint(9) NOT NULL default '0',
  `media_user_ip` varchar(14) NOT NULL default '',
  `media_approval` tinyint(3) NOT NULL default '0',
  `media_type` tinyint(4) NOT NULL default '0',
  `media_upload_time` int(11) NOT NULL default '0',
  `media_category` int(11) NOT NULL default '0',
  `media_watermarked` tinyint(4) NOT NULL default '0',
  `artist` VARCHAR(255) NOT NULL default '',
  `album` VARCHAR(255) NOT NULL default '',
  `genre` VARCHAR(255) NOT NULL default '',
  `v100` tinyint(4) NOT NULL default '0',
  `maint` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`media_id`),
  KEY `media_upload_time` (`media_upload_time`)
);";

$_SQL['mg_media_albums']="CREATE TABLE {$_TABLES['mg_media_albums']} (
  `album_id` int(11) NOT NULL default '0',
  `media_id` varchar(40) NOT NULL default '0',
  `media_order` int(11) NOT NULL default '0',
  KEY `media_id` (`media_id`),
  KEY `album_id` (`album_id`)
);";

$_SQL['mg_media_album_queue']="CREATE TABLE {$_TABLES['mg_media_album_queue']} (
  `album_id` int(11) NOT NULL default '0',
  `media_id` varchar(40) NOT NULL default '0',
  `media_order` int(11) NOT NULL default '0',
  KEY `media_id` (`media_id`),
  KEY `album_id` (`album_id`)
);";

$_SQL['mg_playback_options']="CREATE TABLE {$_TABLES['mg_playback_options']} (
  `media_id` varchar(40) NOT NULL default '',
  `option_name` varchar(255) NOT NULL default '',
  `option_value` varchar(255) NOT NULL default '',
  UNIQUE KEY `media_id_2` (`media_id`,`option_name`),
  KEY `media_id` (`media_id`)
);";

$_SQL['mg_usage_tracking']="CREATE TABLE {$_TABLES['mg_usage_tracking']} (
  `time` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `user_name` varchar(127) NOT NULL default '',
  `user_ip` varchar(16) NOT NULL default '',
  `application` varchar(127) NOT NULL default '',
  `album_title` varchar(127) NOT NULL default '',
  `media_title` varchar(127) NOT NULL default '',
  `media_id` varchar(40) NOT NULL default '0',
  KEY `time` (`time`)
);";

$_SQL['mg_userprefs']="CREATE TABLE {$_TABLES['mg_userprefs']} (
  `uid` mediumint(8) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '1',
  `display_rows` tinyint(4) NOT NULL default '0',
  `display_columns` tinyint(4) NOT NULL default '0',
  `mp3_player` tinyint(4) NOT NULL default '-1',
  `playback_mode` tinyint(4) NOT NULL default '-1',
  `tn_size` tinyint(4) NOT NULL default '1',
  `quota` bigint(20) unsigned NOT NULL default '0',
  `member_gallery` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
);";

$_SQL['mg_watermark']="CREATE TABLE {$_TABLES['mg_watermarks']} (
  `wm_id` int(11) NOT NULL default 0,
  `owner_id` int(11) NOT NULL default '0',
  `filename` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`wm_id`)
);";

$_SQL['mg_watermark_data']="INSERT INTO {$_TABLES['mg_watermarks']} VALUES (0, 0, 'blank.png', '---');";

$_SQL['mg_session_items'] = "CREATE TABLE {$_TABLES['mg_session_items']} (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `session_id` varchar(40) NOT NULL default '',
  `mid` varchar(40) NOT NULL default '',
  `aid` int(11) NOT NULL default '0',
  `data` varchar(255) NOT NULL default '',
  `data2` varchar(255) NOT NULL default '',
  `data3` varchar(255) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `session_id` (`session_id`)
);";

$_SQL['mg_session_items2'] = "CREATE TABLE {$_TABLES['mg_session_items2']} (
  `id` bigint(20) NOT NULL,
  `data1` varchar(255) NOT NULL,
  `data2` varchar(255) NOT NULL,
  `data3` varchar(255) NOT NULL,
  `data4` varchar(255) NOT NULL,
  `data5` varchar(255) NOT NULL,
  `data6` varchar(255) NOT NULL,
  `data7` varchar(255) NOT NULL,
  `data8` varchar(255) NOT NULL,
  `data9` varchar(255) NOT NULL,
  KEY `id` (`id`)
);";

$_SQL['mg_session_log'] = "CREATE TABLE {$_TABLES['mg_session_log']} (
  `session_id` varchar(40) NOT NULL default '',
  `session_log` varchar(255) NOT NULL default '',
  KEY `session_id` (`session_id`)
);";

$_SQL['mg_sessions'] = "CREATE TABLE {$_TABLES['mg_sessions']} (
  `session_id` varchar(40) NOT NULL default '',
  `session_uid` mediumint(8) NOT NULL default '0',
  `session_description` varchar(255) NOT NULL default '',
  `session_status` tinyint(4) NOT NULL default '0',
  `session_cycles` tinyint(4) NOT NULL default '0',
  `session_action` varchar(255) NOT NULL default '',
  `session_origin` varchar(255) NOT NULL default '',
  `session_start_time` bigint(11) NOT NULL default '0',
  `session_end_time` bigint(11) NOT NULL default '0',
  `session_var0` varchar(255) default NULL,
  `session_var1` varchar(255) default NULL,
  `session_var2` varchar(255) default NULL,
  `session_var3` varchar(255) default NULL,
  `session_var4` varchar(255) default NULL,
  PRIMARY KEY  (`session_id`)
);";

$_SQL['mg_category'] = "CREATE TABLE {$_TABLES['mg_category']} (
  `cat_id` mediumint(9) NOT NULL default '0',
  `cat_name` varchar(255) NOT NULL default '',
  `cat_description` varchar(255) NOT NULL default '',
  `cat_order` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`cat_id`)
);";

$_SQL['mg_sort'] = "CREATE TABLE {$_TABLES['mg_sort']} (
  `sort_id` varchar(40) NOT NULL default '',
  `sort_user` varchar(40) NOT NULL default '',
  `sort_query` text NOT NULL,
  `sort_results` int(11) NOT NULL default '0',
  `sort_datetime` int(11) NOT NULL default '0',
  `referer` varchar(255) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default ''
);";

$_SQL['mg_postcard'] = "CREATE TABLE {$_TABLES['mg_postcard']} (
  `pc_id` varchar(40) NOT NULL default '',
  `mid` varchar(40) NOT NULL default '',
  `to_name` varchar(255) NOT NULL default '',
  `to_email` varchar(255) NOT NULL default '',
  `from_name` varchar(255) NOT NULL default '',
  `from_email` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `pc_time` int(11) NOT NULL default '0',
  `uid` mediumint(9) NOT NULL default '0',
   PRIMARY KEY  (`pc_id`)
);";

$_SQL['mg_rating'] = "CREATE TABLE {$_TABLES['mg_rating']} (
  `id` int(11) unsigned NOT NULL default '0',
  `ip_address` varchar(14) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `media_id` varchar(40) NOT NULL,
  `ratingdate` int(11) NOT NULL,
  `owner_id` mediumint(8) NOT NULL default '2',
  PRIMARY KEY  (`id`),
  KEY `owner_id` (`owner_id`)
);";

$_SQL['mg_exif_tags']="CREATE TABLE {$_TABLES['mg_exif_tags']} (
  `name` varchar(255) NOT NULL default '',
  `selected` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`name`)
);";

$_SQL['exif_tag_data']="INSERT INTO {$_TABLES['mg_exif_tags']} (`name`, `selected`) VALUES ('ApertureValue', 1),
('ShutterSpeedValue', 1),
('ISO', 1),
('FocalLength', 0),
('Flash', 1),
('ACDComment', 0),
('AEWarning', 0),
('AFFocusPosition', 0),
('AFPointSelected', 0),
('AFPointUsed', 0),
('Adapter', 0),
('Artist', 0),
('BatteryLevel', 0),
('BitsPerSample', 0),
('BlurWarning', 0),
('BrightnessValue', 0),
('CCDSensitivity', 0),
('CameraID', 0),
('CameraSerialNumber', 0),
('Color', 0),
('ColorMode', 0),
('ColorSpace', 0),
('ComponentsConfiguration', 0),
('CompressedBitsPerPixel', 0),
('Compression', 0),
('ContinuousTakingBracket', 0),
('Contrast', 0),
('Converter', 0),
('Copyright', 0),
('CustomFunctions', 0),
('CustomerRender', 0),
('DateTime', 1),
('DigitalZoom', 0),
('DigitalZoomRatio', 0),
('DriveMode', 0),
('EasyShooting', 0),
('ExposureBiasValue', 0),
('ExposureIndex', 0),
('ExposureMode', 0),
('ExposureProgram', 0),
('FileSource', 0),
('FirmwareVersion', 0),
('FlashBias', 0),
('FlashDetails', 0),
('FlashEnergy', 0),
('FlashMode', 0),
('FlashPixVersion', 0),
('FlashSetting', 0),
('FlashStrength', 0),
('FocalPlaneResolutionUnit', 0),
('FocalPlaneXResolution', 0),
('FocalPlaneYResolution', 0),
('FocalUnits', 0),
('Focus', 0),
('FocusMode', 0),
('FocusWarning', 0),
('GainControl', 0),
('ImageAdjustment', 0),
('ImageDescription', 0),
('ImageHistory', 0),
('ImageLength', 0),
('ImageNumber', 0),
('ImageSharpening', 0),
('ImageSize', 0),
('ImageType', 0),
('ImageWidth', 0),
('InterColorProfile', 0),
('Interlace', 0),
('InteroperabilityIFD.InteroperabilityIndex', 0),
('InteroperabilityIFD.InteroperabilityVersion', 0),
('InteroperabilityIFD.RelatedImageFileFormat', 0),
('InteroperabilityIFD.RelatedImageLength', 0),
('InteroperabilityIFD.RelatedImageWidth', 0),
('JPEGTables', 0),
('JpegIFByteCount', 0),
('JpegIFOffset', 0),
('JpegQual', 0),
('LightSource', 0),
('LongFocalLength', 0),
('Macro', 0),
('Make', 1),
('ManualFocusDistance', 0),
('MaxApertureValue', 0),
('MeteringMode', 0),
('Model', 1),
('Noise', 0),
('NoiseReduction', 0),
('Orientation', 0),
('OwnerName', 0),
('PhotometricInterpret', 0),
('PhotoshopSettings', 0),
('PictInfo', 0),
('PictureMode', 0),
('PlanarConfiguration', 0),
('Predictor', 0),
('PrimaryChromaticities', 0),
('Quality', 0),
('ReferenceBlackWhite', 0),
('RelatedSoundFile', 0),
('ResolutionUnit', 0),
('RowsPerStrip', 0),
('SamplesPerPixel', 0),
('Saturation', 0),
('SceneCaptureMode', 0),
('SceneType', 0),
('SecurityClassification', 0),
('SelfTimer', 0),
('SelfTimerMode', 0),
('SensingMethod', 0),
('SequenceNumber', 0),
('Sharpness', 0),
('ShortFocalLength', 0),
('SlowSync', 0),
('Software', 0),
('SoftwareRelease', 0),
('SpatialFrequencyResponse', 0),
('SpecialMode', 0),
('SpectralSensitivity', 0),
('StripByteCounts', 0),
('StripOffsets', 0),
('SubIFDs', 0),
('SubfileType', 0),
('SubjectDistance', 0),
('SubjectLocation', 0),
('SubsecTime', 0),
('SubsecTimeDigitized', 0),
('SubsecTimeOriginal', 0),
('TIFF/EPStandardID', 0),
('TileByteCounts', 0),
('TileLength', 0),
('TileOffsets', 0),
('TileWidth', 0),
('TimeZoneOffset', 0),
('Tone', 0),
('TransferFunction', 0),
('UserComment', 0),
('Version', 0),
('WhiteBalance', 0),
('WhitePoint', 0),
('YCbCrCoefficients', 0),
('YCbCrPositioning', 0),
('YCbCrSubSampling', 0),
('xResolution', 0),
('yResolution', 0),
('ExifImageHeight', 0),
('ExifImageWidth', 0),
('IPTC/SupplementalCategories', 0),
('IPTC/Keywords', 0),
('IPTC/Caption', 0),
('IPTC/CaptionWriter', 0),
('IPTC/Headline', 0),
('IPTC/SpecialInstructions', 0),
('IPTC/Category', 0),
('IPTC/Byline', 0),
('IPTC/BylineTitle', 0),
('IPTC/Credit', 0),
('IPTC/Source', 0),
('IPTC/CopyrightNotice', 0),
('IPTC/ObjectName', 0),
('IPTC/City', 0),
('IPTC/ProvinceState', 0),
('IPTC/CountryName', 0),
('IPTC/OriginalTransmissionReference', 0),
('IPTC/DateCreated', 0),
('IPTC/CopyrightFlag', 0),
('IPTC/TimeCreated', 0);";
?>