<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* MediaGallery Plugin Installer
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

use \glFusion\Log\Log;

global $_DB_dbms;

require_once $_CONF['path'].'plugins/mediagallery/mediagallery.php';
require_once $_CONF['path'].'plugins/mediagallery/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['mediagallery'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_MG_CONF['pi_name'],
        'ver' => $_MG_CONF['pi_version'], 'gl_ver' => $_MG_CONF['gl_version'],
        'url' => $_MG_CONF['pi_url'], 'display' => $_MG_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['mg_albums'], 'sql' => $_SQL['mg_albums']),
  array('type' => 'table', 'table' => $_TABLES['mg_config'], 'sql' => $_SQL['mg_config']),
  array('type' => 'table', 'table' => $_TABLES['mg_media'],  'sql' => $_SQL['mg_media']),
  array('type' => 'table', 'table' => $_TABLES['mg_mediaqueue'],  'sql' => $_SQL['mg_mediaqueue']),
  array('type' => 'table', 'table' => $_TABLES['mg_media_albums'], 'sql' => $_SQL['mg_media_albums']),
  array('type' => 'table', 'table' => $_TABLES['mg_media_album_queue'], 'sql' => $_SQL['mg_media_album_queue']),
  array('type' => 'table', 'table' => $_TABLES['mg_playback_options'], 'sql' => $_SQL['mg_playback_options']),
  array('type' => 'table', 'table' => $_TABLES['mg_usage_tracking'], 'sql' => $_SQL['mg_usage_tracking']),
  array('type' => 'table', 'table' => $_TABLES['mg_userprefs'], 'sql' => $_SQL['mg_userprefs']),
  array('type' => 'table', 'table' => $_TABLES['mg_watermarks'], 'sql' => $_SQL['mg_watermarks']),
  array('type' => 'table', 'table' => $_TABLES['mg_session_items'], 'sql' => $_SQL['mg_session_items']),
  array('type' => 'table', 'table' => $_TABLES['mg_session_items2'], 'sql' => $_SQL['mg_session_items2']),
  array('type' => 'table', 'table' => $_TABLES['mg_session_log'], 'sql' => $_SQL['mg_session_log']),
  array('type' => 'table', 'table' => $_TABLES['mg_sessions'], 'sql' => $_SQL['mg_sessions']),
  array('type' => 'table', 'table' => $_TABLES['mg_category'], 'sql' => $_SQL['mg_category']),
  array('type' => 'table', 'table' => $_TABLES['mg_sort'], 'sql' => $_SQL['mg_sort']),
  array('type' => 'table', 'table' => $_TABLES['mg_postcard'], 'sql' => $_SQL['mg_postcard']),
  array('type' => 'table', 'table' => $_TABLES['mg_rating'], 'sql' => $_SQL['mg_rating']),
  array('type' => 'table', 'table' => $_TABLES['mg_exif_tags'], 'sql' => $_SQL['mg_exif_tags']),

  array('type' => 'group', 'group' => 'mediagallery Admin', 'desc' => 'Users in this group can administer the Media Gallery plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'mediagallery.admin', 'desc' => 'Ability to administer the Media Gallery Plugin',
        'variable' => 'admin_feature_id'),
  array('type' => 'feature', 'feature' => 'mediagallery.config', 'desc' => 'Ability to configure the Media Gallery Plugin',
        'variable' => 'config_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'admin_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'config_feature_id',
        'log' => 'Adding feature to the admin group'),

  array('type' => 'sql', 'sql' => $_SQL['mg_watermark_data'] ),
  array('type' => 'sql', 'sql' => $_SQL['exif_tag_data'] ),
  array('type' => 'sql', 'sql' => $_SQL['config_data_1'] ),
  array('type' => 'sql', 'sql' => $_SQL['config_data_2'] ),

  array('type' => 'block', 'name' => 'mgrandom', 'title' => 'Random Image',
          'phpblockfn' => 'phpblock_mg_randommedia', 'block_type' => 'phpblock',
          'onleft' => 1, 'group_id' => 'admin_group_id'),

  array('type' => 'block', 'name' => 'mgenroll', 'title' => 'Member Album Enroll',
          'phpblockfn' => 'phpblock_mg_maenroll', 'block_type' => 'phpblock',
          'onleft' => 1, 'group_id' => 'admin_group_id'),

);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_mediagallery()
{
    global $INSTALL_plugin, $_MG_CONF;

    $pi_name            = $_MG_CONF['pi_name'];
    $pi_display_name    = $_MG_CONF['pi_display_name'];
    $pi_version         = $_MG_CONF['pi_version'];

    Log::write('system',Log::INFO,'Attempting to install the '.$pi_display_name.' plugin');

    $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);
    if ($ret > 0) {
        return false;
    }

    return true;
}


/**
* Automatic uninstall function for plugins
*
* @return   array
*
* This code is automatically uninstalling the plugin.
* It passes an array to the core code function that removes
* tables, groups, features and php blocks from the tables.
* Additionally, this code can perform special actions that cannot be
* foreseen by the core code (interactions with other plugins for example)
*
*/
function plugin_autouninstall_mediagallery ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('mg_albums','mg_media','mg_media_albums','mg_usage_tracking','mg_config', 'mg_mediaqueue', 'mg_media_album_queue','mg_playback_options','mg_userprefs','mg_exif_tags', 'mg_watermarks', 'mg_category', 'mg_sessions', 'mg_session_items', 'mg_session_items2','mg_session_log', 'mg_sort', 'mg_postcard','mg_rating'),
        /* give the full name of the group, as in the db */
        'groups' => array('mediagallery Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('mediagallery.admin', 'mediagallery.config'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array('phpblock_mg_randommedia','phpblock_mg_maenroll'),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>