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

function plugin_install_bad_behavior2($_DB_table_prefix)
{
    global $_CONF, $_TABLES, $_USER, $_DB_dbms;

    require_once($_CONF['path'] . '/plugins/bad_behavior2/functions.inc');

    $pi_display_name = 'Bad Behavior2';
    $pi_name         = 'bad_behavior2';
    $pi_version      = '2.0.13a';
    $gl_version      = '1.5.0';
    $pi_url          = 'http://www.gllabs.org/';

    // name of the Admin group
    $pi_admin        = $pi_display_name . ' Admin';

    // the plugin's groups - assumes first group to be the Admin group
    $GROUPS = array();
    $GROUPS[$pi_admin] = 'Users in this group can administer the Bad Behavior2 plugin';

    $FEATURES = array();
    $MAPPINGS = array();

    $DEFVALUES = array();
    $DEFVALUES[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('bad_behavior2.donate', 1)";

    $uninstall_plugin = 'plugin_uninstall_' . $pi_name;

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
?>