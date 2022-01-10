<?php
/**
 * glFusion CMS - FileMgmt Plugin
 * Automatic installation routines.
 *
 * @license     GNU General Public License version 2 or later
 *              http://www.opensource.org/licenses/gpl-license.php
 *
 * @copyrignt   Copyright (C) 2009-2022 by the following authors:
 *              Mark R. Evans   mark AT glfusion DOT org
 *
 * Based on prior work Copyright (C) 2004 by Consult4Hire Inc.
 *      Author: Blaine Lang          blaine AT portalparts DOT com
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

global $_DB_dbms;

require_once __DIR__ . '/functions.inc';
require_once $_CONF['path'].'plugins/filemgmt/filemgmt.php';
require_once $_CONF['path'].'plugins/filemgmt/sql/mysql_install.php';

// +--------------------------------------------------------------------------+
// | Plugin installation options                                              |
// +--------------------------------------------------------------------------+

$INSTALL_plugin['filemgmt'] = array(
    'installer' => array(
        'type' => 'installer',
        'version' => '1',
        'mode' => 'install',
    ),

    'plugin' => array(
        'type' => 'plugin',
        'name' => $_FM_CONF['pi_name'],
        'ver' => $_FM_CONF['pi_version'],
        'gl_ver' => $_FM_CONF['gl_version'],
        'url' => $_FM_CONF['pi_url'],
        'display' => $_FM_CONF['pi_display_name'],
    ),

    array(
        'type' => 'table',
        'table' => $_TABLES['filemgmt_cat'],
        'sql' => $_SQL['filemgmt_cat'],
    ),
    array(
        'type' => 'table',
        'table' => $_TABLES['filemgmt_filedetail'],
        'sql' => $_SQL['filemgmt_filedetail'],
    ),
    array(
        'type' => 'table',
        'table' => $_TABLES['filemgmt_filedesc'],
        'sql' => $_SQL['filemgmt_filedesc'],
    ),
    array(
        'type' => 'table',
        'table' => $_TABLES['filemgmt_brokenlinks'],
        'sql' => $_SQL['filemgmt_brokenlinks'],
    ),
    array(
        'type' => 'table',
        'table' => $_TABLES['filemgmt_votedata'],
        'sql' => $_SQL['filemgmt_votedata'],
    ),
    array(
        'type' => 'table',
        'table' => $_TABLES['filemgmt_history'],
        'sql' => $_SQL['filemgmt_history'],
    ),

    array(
        'type' => 'group',
        'group' => 'filemgmt Admin',
        'desc' => 'Users in this group can administer the FileMgmt plugin',
        'variable' => 'admin_group_id',
        'addroot' => true,
        'admin' => true,
    ),
    array(
        'type' => 'feature',
        'feature' => 'filemgmt.user',
        'desc' => 'FileMgmt Access',
        'variable' => 'user_feature_id',
    ),
    array(
        'type' => 'feature',
        'feature' => 'filemgmt.edit',
        'desc' => 'FileMgmt Admin Rights',
        'variable' => 'edit_feature_id',
    ),
    array(
        'type' => 'feature',
        'feature' => 'filemgmt.upload',
        'desc' => 'FileMgmt File Upload Rights',
        'variable' => 'upload_feature_id',
    ),

    array(
        'type' => 'mapping',
        'group' => 'admin_group_id',
        'feature' => 'user_feature_id',
        'log' => 'Adding feature to the admin group',
    ),
    array(
        'type' => 'mapping',
        'group' => 'admin_group_id',
        'feature' => 'edit_feature_id',
        'log' => 'Adding feature to the admin group',
    ),
    array(
        'type' => 'mapping',
        'group' => 'admin_group_id',
        'feature' => 'upload_feature_id',
        'log' => 'Adding feature to the admin group',
    ),
);


/**
 * Puts the datastructures for this plugin into the glFusion database.
 * Note: Corresponding uninstall routine is in functions.inc
 *
 * @return   boolean True if successful False otherwise
 */
function plugin_install_filemgmt()
{
    global $INSTALL_plugin, $_FM_CONF;

    $pi_name            = $_FM_CONF['pi_name'];
    $pi_display_name    = $_FM_CONF['pi_display_name'];
    $pi_version         = $_FM_CONF['pi_version'];

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
function plugin_load_configuration_filemgmt()
{
    global $_CONF;

    require_once __DIR__ . '/install_defaults.php';

    return plugin_initconfig_filemgmt();
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

function plugin_autouninstall_filemgmt ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array(
            'filemgmt_cat',
            'filemgmt_filedetail',
            'filemgmt_filedesc',
            'filemgmt_brokenlinks',
            'filemgmt_votedata',
            'filemgmt_history',
        ),
        /* give the full name of the group, as in the db */
        'groups' => array('filemgmt Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('filemgmt.user','filemgmt.edit', 'filemgmt.upload'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array()
    );
    return $out;
}


/**
 * Post-installation tasks required.
 * - create the private data directory
 */
function plugin_postinstall_filemgmt()
{
    global $_FM_CONF;

    glFusion\FileSystem::mkdir($_FM_CONF['FileStore_tmp']);
    glFusion\FileSystem::mkdir($_FM_CONF['SnapStore_tmp']);
}
