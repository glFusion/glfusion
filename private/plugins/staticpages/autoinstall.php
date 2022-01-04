<?php
/**
* glFusion CMS - Static Pages Plugin
*
* Auto installer module
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony AT tonybibbs DOT com
*  Tom Willett       twillett AT users DOT sourceforge DOT net
*  Blaine Lang       langmail AT sympatico DOT ca
*  Dirk Haun         dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

global $_DB_dbms;

require_once $_CONF['path'].'plugins/staticpages/functions.inc';
require_once $_CONF['path'].'plugins/staticpages/staticpages.php';
require_once $_CONF['path'].'plugins/staticpages/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['staticpages'] = array(
  'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

  'plugin' => array('type' => 'plugin', 'name' => $_SP_CONF['pi_name'],
        'ver' => $_SP_CONF['pi_version'], 'gl_ver' => $_SP_CONF['gl_version'],
        'url' => $_SP_CONF['pi_url'], 'display' => $_SP_CONF['pi_display_name']),

  array('type' => 'table', 'table' => $_TABLES['staticpage'], 'sql' => $_SQL['staticpage']),

  array('type' => 'group', 'group' => 'staticpages Admin', 'desc' => 'Users in this group can administer the StaticPages plugin',
        'variable' => 'admin_group_id', 'addroot' => true, 'admin' => true),

  array('type' => 'feature', 'feature' => 'staticpages.edit', 'desc' => 'Ability to edit static pages',
        'variable' => 'edit_feature_id'),
  array('type' => 'feature', 'feature' => 'staticpages.delete', 'desc' => 'Ability to delete static pages',
        'variable' => 'delete_feature_id'),
  array('type' => 'feature', 'feature' => 'staticpages.PHP', 'desc' => 'Ability use PHP in static pages',
        'variable' => 'php_feature_id'),

  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'edit_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'delete_feature_id',
        'log' => 'Adding feature to the admin group'),
  array('type' => 'mapping', 'group' => 'admin_group_id', 'feature' => 'php_feature_id',
        'log' => 'Adding feature to the admin group'),
);


/**
* Puts the datastructures for this plugin into the glFusion database
*
* Note: Corresponding uninstall routine is in functions.inc
*
* @return   boolean True if successful False otherwise
*
*/
function plugin_install_staticpages()
{
    global $INSTALL_plugin, $_SP_CONF;

    $pi_name            = $_SP_CONF['pi_name'];
    $pi_display_name    = $_SP_CONF['pi_display_name'];
    $pi_version         = $_SP_CONF['pi_version'];

    Log::write('system',Log::INFO,'Attempting to install the $pi_display_name plugin');

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
function plugin_load_configuration_staticpages()
{
    global $_CONF;

    require_once $_CONF['path'].'plugins/staticpages/install_defaults.php';

    return plugin_initconfig_staticpages();
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
function plugin_autouninstall_staticpages ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('staticpage'),
        /* give the full name of the group, as in the db */
        'groups' => array('staticpages Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('staticpages.edit', 'staticpages.delete', 'staticpages.PHP'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}
?>