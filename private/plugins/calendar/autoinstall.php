<?php
// +--------------------------------------------------------------------------+
// | Calendar Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | autoinstall.php                                                          |
// |                                                                          |
// | glFusion Auto Installer module                                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - langmail AT sympatico DOT ca                 |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

global $_DB_dbms;

require_once $_CONF['path'].'plugins/calendar/functions.inc';
require_once $_CONF['path'].'plugins/calendar/calendar.php';
require_once $_CONF['path'].'plugins/calendar/sql/'.$_DB_dbms.'_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['calendar'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_CA_CONF['pi_name'],
        'ver' => $_CA_CONF['pi_version'], 'gl_ver' => $_CA_CONF['gl_version'],
        'url' => $_CA_CONF['pi_url'], 'display' => $_CA_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['events'], 'sql' => $_SQL['events']),

  array('type' => 'table', 'table' => $_TABLES['eventsubmission'], 'sql' => $_SQL['eventsubmission']),

  array('type' => 'table', 'table' => $_TABLES['personal_events'], 'sql' => $_SQL['personal_events']),

  array('type' => 'group', 'group' => 'calendar Admin', 'desc' => 'Users in this group can administer the Calendar plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'calendar.edit', 'desc' => 'Ability to edit Calendar events',
        'variable' => 'edit_feature_id'),
  array('type' => 'feature', 'feature' => 'calendar.moderate', 'desc' => 'Ability to moderate Calendar events',
        'variable' => 'moderate_feature_id'),
  array('type' => 'feature', 'feature' => 'calendar.submit', 'desc' => 'Ability to submit Calendar events',
        'variable' => 'submit_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'edit_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'moderate_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'submit_feature_id',
        'log' => 'Adding feature to the admin group'),

  array('type' => 'block',  'name' => 'block_calevents', 'title' => 'Upcoming Events',
          'phpblockfn' => 'phpblock_calendar', 'block_type' => 'phpblock', 'onleft' => 1,
          'group_id' => 'admin_group_id'),

);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_calendar()
{
    global $INSTALL_plugin, $_CA_CONF;

    $pi_name            = $_CA_CONF['pi_name'];
    $pi_display_name    = $_CA_CONF['pi_display_name'];
    $pi_version         = $_CA_CONF['pi_version'];

    COM_errorLog("Attempting to install the $pi_display_name plugin", 1);

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
function plugin_load_configuration_calendar()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/calendar/install_defaults.php';

    return plugin_initconfig_calendar();
}


/**
* When the install went through, give the plugin a chance for any
* plugin-specific post-install fixes
*
* @return   boolean     true = proceed with install, false = an error occured
*
*/
function plugin_postinstall_calendar()
{
    global $_CONF, $_TABLES, $LANG_CAL_1;

    require_once $_CONF['path'].'plugins/calendar/functions.inc';

    // fix Upcoming Events block group ownership
    $blockAdminGroup = DB_getItem ($_TABLES['groups'], 'grp_id',
                                   "grp_name = 'Block Admin'");
    if ($blockAdminGroup > 0) {
        // set the block's permissions
        $A = array ();
        SEC_setDefaultPermissions ($A, $_CONF['default_permissions_block']);

        // set the block's title in the current language, while we're at it
        $title = 'Upcoming Events';

        // ... and make it the last block on the left side
        $result = DB_query ("SELECT MAX(blockorder) FROM {$_TABLES['blocks']} WHERE onleft = 1");
        list($order) = DB_fetchArray ($result);
        $order += 10;

        DB_query ("UPDATE {$_TABLES['blocks']} SET group_id = $blockAdminGroup, title = '$title', blockorder = $order, perm_owner = {$A['perm_owner']}, perm_group = {$A['perm_group']}, perm_members = {$A['perm_members']}, perm_anon = {$A['perm_anon']} WHERE (type = 'phpblock') AND (phpblockfn = 'phpblock_calendar')");

        return true;
    }

    return false;
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
function plugin_autouninstall_calendar ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('events','eventsubmission','personal_events'),
        /* give the full name of the group, as in the db */
        'groups' => array('calendar Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('calendar.edit', 'calendar.moderate', 'calendar.submit'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array('phpblock_calendar'),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>