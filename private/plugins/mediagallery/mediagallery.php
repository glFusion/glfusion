<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_table_prefix, $_TABLES;

$_MG_CONF = array();

// Plugin info

$_MG_CONF['pi_name']            = 'mediagallery';
$_MG_CONF['pi_display_name']    = 'Media Gallery';
$_MG_CONF['pi_version']         = '2.2.0';
$_MG_CONF['gl_version']         = '2.0.0';
$_MG_CONF['pi_url']             = 'https://www.glfusion.org/';

$_MG_table_prefix = $_DB_table_prefix;

// Media Gallery tables

$_TABLES['mg_albums']           = $_MG_table_prefix . 'mg_albums';
$_TABLES['mg_media_albums']     = $_MG_table_prefix . 'mg_media_albums';
$_TABLES['mg_media']            = $_MG_table_prefix . 'mg_media';
$_TABLES['mg_usage_tracking']   = $_MG_table_prefix . 'mg_usage_tracking';
$_TABLES['mg_config']           = $_MG_table_prefix . 'mg_config';
$_TABLES['mg_mediaqueue']       = $_MG_table_prefix . 'mg_media_queue';
$_TABLES['mg_media_album_queue']= $_MG_table_prefix . 'mg_media_album_queue';
$_TABLES['mg_playback_options'] = $_MG_table_prefix . 'mg_playback_options';
$_TABLES['mg_userprefs']        = $_MG_table_prefix . 'mg_userprefs';
$_TABLES['mg_exif_tags']        = $_MG_table_prefix . 'mg_exif_tags';
$_TABLES['mg_watermarks']       = $_MG_table_prefix . 'mg_watermarks';
$_TABLES['mg_category']         = $_MG_table_prefix . 'mg_category';
$_TABLES['mg_sessions']         = $_MG_table_prefix . 'mg_batch_sessions';
$_TABLES['mg_session_items']    = $_MG_table_prefix . 'mg_batch_session_items';
$_TABLES['mg_session_items2']   = $_MG_table_prefix . 'mg_batch_session_items2';
$_TABLES['mg_session_log']      = $_MG_table_prefix . 'mg_batch_session_log';
$_TABLES['mg_sort']             = $_MG_table_prefix . 'mg_sort';
$_TABLES['mg_postcard']         = $_MG_table_prefix . 'mg_postcard';
$_TABLES['mg_rating']           = $_MG_table_prefix . 'mg_rating';
?>