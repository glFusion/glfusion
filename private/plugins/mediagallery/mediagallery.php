<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | mediagallery.php                                                         |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C)  2009-2016 by the following authors:                       |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_table_prefix, $_TABLES;

$_MG_CONF = array();

// Plugin info

$_MG_CONF['pi_name']            = 'mediagallery';
$_MG_CONF['pi_display_name']    = 'Media Gallery';
$_MG_CONF['pi_version']         = '2.1.3';
$_MG_CONF['gl_version']         = '1.7.0';
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