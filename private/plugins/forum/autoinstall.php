<?php
/**
* glFusion CMS - Forum Plugin
*
* Auto Installer
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Blaine Lang          blaine AT portalparts DOT com
*                        www.portalparts.com
*   Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com
*   Prototype & Concept :        Mr.GxBlock, www.gxblock.com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

global $_DB_dbms;

require_once $_CONF['path'].'plugins/forum/functions.inc';
require_once $_CONF['path'].'plugins/forum/forum.php';
require_once $_CONF['path'].'plugins/forum/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['forum'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_FF_CONF['pi_name'],
        'ver' => $_FF_CONF['pi_version'], 'gl_ver' => $_FF_CONF['gl_version'],
        'url' => $_FF_CONF['pi_url'], 'display' => $_FF_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['ff_categories'], 'sql' => $_SQL['ff_categories']),
  array('type' => 'table', 'table' => $_TABLES['ff_forums'], 'sql' => $_SQL['ff_forums']),
  array('type' => 'table', 'table' => $_TABLES['ff_topic'], 'sql' => $_SQL['ff_topic']),
  array('type' => 'table', 'table' => $_TABLES['ff_log'], 'sql' => $_SQL['ff_log']),
  array('type' => 'table', 'table' => $_TABLES['ff_moderators'], 'sql' => $_SQL['ff_moderators']),
  array('type' => 'table', 'table' => $_TABLES['ff_userprefs'], 'sql' => $_SQL['ff_userprefs']),
  array('type' => 'table', 'table' => $_TABLES['ff_banned_ip'], 'sql' => $_SQL['ff_banned_ip']),
  array('type' => 'table', 'table' => $_TABLES['ff_userinfo'], 'sql' => $_SQL['ff_userinfo']),
  array('type' => 'table', 'table' => $_TABLES['ff_bookmarks'], 'sql' => $_SQL['ff_bookmarks']),
  array('type' => 'table', 'table' => $_TABLES['ff_attachments'], 'sql' => $_SQL['ff_attachments']),
  array('type' => 'table', 'table' => $_TABLES['ff_rating_assoc'], 'sql' => $_SQL['ff_rating_assoc']),
  array('type' => 'table', 'table' => $_TABLES['ff_badges'], 'sql' => $_SQL['ff_badges']),
  array('type' => 'table', 'table' => $_TABLES['ff_ranks'], 'sql' => $_SQL['ff_ranks']),
  array('type' => 'table', 'table' => $_TABLES['ff_likes_assoc'], 'sql' => $_SQL['ff_likes_assoc']),

//  array('type' => 'sql', 'sql' => $_SQL['d1'] ),
//  array('type' => 'sql', 'sql' => $_SQL['d2'] ),
//  array('type' => 'sql', 'sql' => $_SQL['d3'] ),
//  array('type' => 'sql', 'sql' => $_SQL['d4'] ),
  array('type' => 'sql', 'sql' => $_SQL['d5'] ),
  array('type' => 'sql', 'sql' => $_SQL['d6'] ),

  array('type' => 'group', 'group' => 'forum Admin', 'desc' => 'Users in this group can administer the Forum plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'forum.edit', 'desc' => 'Ability to edit Forum Posts',
        'variable' => 'edit_feature_id'),
  array('type' => 'feature', 'feature' => 'forum.user', 'desc' => 'Ability to use the Forum Plugin',
        'variable' => 'user_feature_id'),
  array('type' => 'feature', 'feature' => 'forum.html', 'desc' => 'Ability to use HTML in Forum posts',
        'variable' => 'html_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'edit_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'user_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'html_feature_id',
        'log' => 'Adding feature to the admin group'),

  array('type' => 'block', 'name' => 'forum_news', 'title' => 'Forum Posts',
          'phpblockfn' => 'phpblock_forum_newposts', 'block_type' => 'phpblock',
          'group_id' => 'admin_group_id', 'onleft' => 1, 'is_enabled' => 0),

  array('type' => 'block', 'name' => 'forum_menu', 'title' => 'Forum Menu',
          'phpblockfn' => 'phpblock_forum_menu', 'block_type' => 'phpblock',
          'group_id' => 'admin_group_id', 'onleft' => 1, 'is_enabled' => 0),

);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_forum()
{
    global $INSTALL_plugin, $_FF_CONF;

    $pi_name            = $_FF_CONF['pi_name'];
    $pi_display_name    = $_FF_CONF['pi_display_name'];
    $pi_version         = $_FF_CONF['pi_version'];

    Log::write('system',Log::INFO,'Attempting to install the '.$pi_display_name.' plugin');

    $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);
    if ($ret > 0) {
        return false;
    }

    return true;
}


/**
* Loads the configuration records for the Online Config Manager
*
* @return   boolean     true = proceed with install, false = an error occured
*
*/
function plugin_load_configuration_forum()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/forum/install_defaults.php';

    return plugin_initconfig_forum();
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
function plugin_autouninstall_forum ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('ff_topic','ff_categories','ff_forums','ff_moderators','ff_banned_ip', 'ff_log', 'ff_userprefs','ff_userinfo','ff_attachments','ff_bookmarks','ff_rating_assoc', 'ff_badges', 'ff_ranks', 'ff_likes_assoc'),
        /* give the full name of the group, as in the db */
        'groups' => array('forum Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('forum.edit', 'forum.user','forum.html'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array('phpblock_forum_newposts','phpblock_forum_menu'),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>
