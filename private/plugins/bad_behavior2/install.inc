<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | install.inc                                                              |
// |                                                                          |
// | Installation routines for Bad Behavior Plugin                            |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2007 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

$pi_name        = 'bad_behavior2';
$pi_version     = '2.0.23';
$gl_version     = '1.1.0';
$pi_url         = 'http://www.glfusion.org';
$base_path      = $_CONF['path'] . 'plugins/' . $pi_name . '/';

/*
 * If we are being called glFusion's install routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

/**
* Checks the requirements for this plugin and if it is compatible with this
* version of glFusion.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function bad_behavior2_compatible_with_this_glfusion_version ()
{
    // requires 1.1.0 or newer
    $glversion = explode(".", GVERSION);
    if ( $glversion[0] < 1 ) {
        return false;
    }
    if ( $glversion[1] < 1 ) {
        return false;
    }
    return true;
}


function plugin_install_bad_behavior2($_DB_table_prefix)
{
    global $_TABLES, $_CONF;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    require_once($_CONF['path'] . '/plugins/bad_behavior2/functions.inc');

    // name of the Admin group
    $pi_admin        = 'Bad Behavior2 Admin';

    // the plugin's groups - assumes first group to be the Admin group
    $GROUPS = array();
    $GROUPS[$pi_admin] = 'Users in this group can administer the Bad Behavior2 plugin';

    $FEATURES = array();
    $MAPPINGS = array();

    $DEFVALUES = array();
    $DEFVALUES[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('bad_behavior2.donate', 1)";

    $uninstall_plugin = 'plugin_remove_' . $pi_name;

    // create the plugin's groups
    $admin_group_id = 0;
    foreach ($GROUPS as $name => $desc) {
        $grp_name = addslashes ($name);
        $grp_desc = addslashes ($desc);
        DB_query ("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) VALUES ('$grp_name', '$grp_desc')", 1);
        if (DB_error ()) {
            $uninstall_plugin ();
            return false;
        }
        // replace the description with the new group id so we can use it later
        $GROUPS[$name] = DB_insertId ();

        // assume that the first group is the plugin's Admin group
        if ($admin_group_id == 0) {
            $admin_group_id = $GROUPS[$name];
        }
    }

    // Add the plugin's features

    foreach ($FEATURES as $feature => $desc) {
        $ft_name = addslashes ($feature);
        $ft_desc = addslashes ($desc);
        DB_query ("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
                  . "VALUES ('$ft_name', '$ft_desc')", 1);
        if (DB_error ()) {
            $uninstall_plugin ();

            return false;
        }

        $feat_id = DB_insertId ();

        if (isset ($MAPPINGS[$feature])) {
            foreach ($MAPPINGS[$feature] as $group) {
                DB_query ("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, {$GROUPS[$group]})");
                if (DB_error ()) {
                    $uninstall_plugin ();

                    return false;
                }
            }
        }
    }

    // Add plugin's Admin group to the Root user group
    // (assumes that the Root group's ID is always 1)

    DB_query ("INSERT INTO {$_TABLES['group_assignments']} VALUES "
              . "($admin_group_id, NULL, 1)");
    if (DB_error ()) {
        $uninstall_plugin ();
        return false;
    }

     // Finally, register the plugin with glFusion

    // silently delete an existing entry
    DB_delete ($_TABLES['plugins'], 'pi_name', $pi_name);

    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) VALUES "
        . "('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error ()) {
        $uninstall_plugin ();
        return false;
    }

    return true;
}

function plugin_remove_bad_behavior2 ()
{
    global $_CONF, $_DB_table_prefix, $_TABLES;

    $group_id = DB_getItem ($_TABLES['groups'], 'grp_id',
                            "grp_name = 'Bad Behavior2 Admin'");
    if ($group_id > 1) {
        DB_query ("DELETE FROM {$_TABLES['groups']} WHERE grp_name = 'Bad Behavior2 Admin'");
        DB_query ("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $group_id");
    }

    DB_query("DROP TABLE " . $_DB_table_prefix . "bad_behavior2",1);

    DB_query ("DELETE FROM {$_TABLES['vars']} WHERE name = 'bad_behavior2.donate'");
    DB_query ("DELETE FROM {$_TABLES['vars']} WHERE name = 'bb2_installed'");
    DB_query ("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'bad_behavior2'");

    return true;
}

?>