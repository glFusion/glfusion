<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-install.php                                                          |
// |                                                                          |
// | Install/Uninstall library.                                               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Joe Mucchiello         jmucchiello AT yahoo DOT com                      |
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
//

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


if (!defined('INSTALLER_VERSION')) {
define('INSTALLER_VERSION','1');

function INSTALLER_install_group($step, &$vars)
{
    global $_TABLES;

    COM_errorLog("Creating group {$step['group']}...");
    $grp_name = addslashes($step['group']);
    $grp_desc = addslashes($step['desc']);
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) VALUES ('$grp_name', '$grp_desc')", 1);
    if (DB_error()) {
        COM_errorLog("Group creation failed!");
        return 1;
    }
    $grp_id = DB_insertId();
    $vars[$step['variable']] = $grp_id;
    $vars['__groups'][$step['variable']] = $grp_name;

    if (isset($step['addroot'])) {
        DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_grp_id) VALUES ($grp_id, 1)", 1);
    }

    return "DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id";
}

function INSTALLER_install_addgroup($step, &$vars)
{
    global $_TABLES;

    COM_errorLog("Adding a group to another group...");
    if (array_key_exists('parent_var',$step)) {
        $parent_grp = $vars[$step['parent_var']];
    } elseif (array_key_exists('parent_grp',$step)) {
        $parent_grp = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = '" . addslashes($step['parent_grp']) . "'");
    } else {
        $parent_grp = 0;
    }
    if (array_key_exists('child_var',$step)) {
        $child_grp = $vars[$step['child_var']];
    } elseif (array_key_exists('child_grp',$step)) {
        $child_grp = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = '" . addslashes($step['child_grp']) . "'");
    } else {
        $child_grp = 0;
    }
    $parent_grp = intval($parent_grp);
    $child_grp = intval($child_grp);
    if ($parent_grp == 0 || $child_grp == 0) {
        COM_errorLog("Parent or child group missing!");
        return 1;
    }

    DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_grp_id) VALUES ($parent_grp, $child_grp)", 1);
    if (DB_error()) {
        COM_errorLog("Failed to assign group!");
        return 1;
    }
    return "DELETE FROM {$_TABLES['group_assignments']} where ug_main_grp_id = $parent_grp and grp_id = $child_grp";
}

function INSTALLER_install_feature($step, &$vars)
{
    global $_TABLES;

    COM_errorLog("Creating feature {$step['feature']}...");
    $ft_name = addslashes($step['feature']);
    $ft_desc = addslashes($step['desc']);
    DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) VALUES ('$ft_name', '$ft_desc')", 1);
    if (DB_error()) {
        COM_errorLog("Feature creation failed!");
        return 1;
    }
    $ft_id = DB_insertId();
    $vars[$step['variable']] = $ft_id;
    $vars['__groups'][$step['variable']] = $ft_name;
    return "DELETE FROM {$_TABLES['features']} WHERE ft_id = $ft_id";
}

function INSTALLER_install_mapping($step, &$vars)
{
    global $_TABLES;

    if (isset($step['log'])) {
        COM_errorLog($step['log']);
    } else {
        COM_errorLog("Mapping a feature to a group...");
    }
    if (array_key_exists('findgroup', $step)) {
        $grp_id = DB_getItem($_TABLES['groups'],'grp_id',"grp_name = '" . addslashes($step['findgroup']) . "'");
        if ($grp_id == 0) {
            COM_errorLog("Could not find existing '{$step['findgroup']}' group!");
            return 1;
        }
    } else {
        $grp_id = $vars[$step['group']];
    }
    $ft_id = $vars[$step['feature']];
    DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
    if (DB_error()) {
        COM_errorLog("Mapping failed!");
        return 1;
    }
    return "DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $ft_id AND acc_grp_id = $grp_id";
}

function INSTALLER_install_table($step, &$vars)
{
    global $_DB_dbms, $_TABLES;

    COM_errorLog("Creating table {$step['table']}...");
    static $use_innodb = null;
    if ($use_innodb === null) {
        if (($_DB_dbms == 'mysql') &&

            (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
        $use_innodb = true;
        }
        $use_innodb = false;
    }
    if ($use_innodb) {
        $sql = str_replace('MyISAM', 'InnoDB', $step['sql']);
    } else {
        $sql = $step['sql'];
    }

    DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("Failed to create table {$step['table']}!");
        return 1;
    }
    return "DROP TABLE {$step['table']}";
}

function INSTALLER_install_sql($step, &$vars)
{
    if (isset($step['log'])) {
        COM_errorLog($step['log']);
    }

    DB_query($step['sql'], 1);
    if (DB_error()) {
        COM_errorLog("SQL failed!");
        return 1;
    }
    return isset($step['rev']) ? $step['rev'] : '';
}

function INSTALLER_install_block($step, &$vars)
{
    global $_TABLES, $_CONF, $_USER;

    COM_errorLog("Creating block {$step['name']}...");
    $is_enabled = isset($step['is_enabled']) ? intval($step['is_enabled']) : 1;
    $rdflimit = isset($step['rdflimit']) ? intval($step['rdflimit']) : 0;
    $onleft = isset($step['onleft']) ? intval($step['onleft']) : 0;
    $allow_autotags = isset($step['allow_autotags']) ? intval($step['allow_autotags']) : 0;
    $name = isset($step['name']) ? addslashes($step['name']) : '';
    $title = isset($step['title']) ? addslashes($step['title']) : '';
    $type = isset($step['block_type']) ? addslashes($step['block_type']) : 'unknown';
    $phpblockfn = isset($step['phpblockfn']) ? addslashes($step['phpblockfn']) : '';
    $help = isset($step['help']) ? addslashes($step['help']) : '';
    $content = isset($step['content']) ? addslashes($step['content']) : '';
    $owner_id = $_USER['uid'];
    $group_id = $vars[$step['group_id']];
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = $_CONF['default_permissions_block'];
    DB_query("INSERT INTO {$_TABLES['blocks']} "
           . "(is_enabled,name,type,title,tid,blockorder,content,allow_autotags,rdflimit,onleft,phpblockfn,help,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon)"
   . " VALUES ($is_enabled,'$name','$type','$title','all',9999,'$content',$allow_autotags,$rdflimit,$onleft,'$phpblockfn','$help',$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon)", 1);
    if (DB_error()) {
        COM_errorLog("Block creation failed!");
        return 1;
    }
    $bid = DB_insertId();
    if (isset($step['variable'])) {
        $vars[$step['variable']] = $bid;
    }
    return "DELETE FROM {$_TABLES['blocks']} WHERE bid = $bid";
}


function INSTALLER_install_createvar($step, &$vars)
{
    global $_TABLES, $_CONF, $_USER;

    if (isset($step['group'])) {
        $table = $_TABLES['groups'];
        $col = 'grp_id';
        $where = "grp_name = '{$step['group']}'";
        $major = '__group';
    } elseif (isset($step['feature'])) {
        $table = $_TABLES['features'];
        $col = 'ft_id';
        $where = "ft_name = '{$step['feature']}'";
        $major = '__feature';
    } else {
        COM_errorLog("Don't know what var to create");
        return 1;
    }
    $vars[$step['variable']] = DB_getItem($table, $col, $where);
    $vars[$major][$step['variable']] = $vars[$step['variable']];
    return '';
}

function INSTALLER_install_mkdir($step, &$vars)
{
    if (array_key_exists('dirs', $step)) {
        if (!is_array($step['dirs'])) {
            $dirs = Array($step['dirs']);
        } else {
            $dirs = $step['dirs'];
        }
        foreach ($dirs as $path) {
            COM_errorlog("Creating directory $path");
            $ret = @mkdir($path);
            file_put_contents($path . '/index.html', '');
        }
    }
    return Array('type' => 'rmdir', 'dirs' => $dirs);
}

function INSTALLER_fail_rmdir($step)
{
    if (array_key_exists('dirs', $step)) {
        if (!is_array($step['dirs'])) {
            $dirs = Array($step['dirs']);
        } else {
            $dirs = $step['dirs'];
        }
        foreach ($dirs as $path) {
            COM_errorlog("FAIL: removing directory $path");
            @rmdir($path);
        }
    }
}

function INSTALLER_fail($rev)
{
    $A = array_reverse($rev);
    foreach ($A as $sql) {
        if (empty($sql)) {
            // no step
        } elseif (is_array($sql)) {
            if (array_key_exists('type', $sql)) {
                $function = 'INSTALLER_fail_'.$type;
                if (function_exists($function)) {
                    COM_errorlog("FAIL: calling $function");
                    $function($sql);
                }
            }
        } else {
            COM_errorLog("FAIL: $sql");
            DB_query($sql, 1);
        }
    }
}

function INSTALLER_install($A)
{
    global $_TABLES;

    if (!isset($A['installer']) OR $A['installer']['version'] != INSTALLER_VERSION) {
        COM_errorLog('Invalid or Unknown installer version');
        return 2;
    }
    if (!isset($A['plugin'])) {
        COM_errorLog("Missing plugin description!");
        return 1;
    }
    if ( !isset($A['plugin']['name'])) {
        COM_errorLog("Missing plugin name!");
        return 1;
    }

    $vars = Array('__groups' => Array(), '__features' => Array(), '__blocks' => Array());
    $reverse = Array();
    foreach ($A as $meta => $step) {
        if ($meta === 'installer') { // must use === when since 0 == 'anystring' is true
        } elseif ($meta === 'plugin') {
            if (!isset($meta['name'])) {
                COM_errorLog("Missing plugin name!");
                INSTALLER_fail($reverse);
                return 1;
            }
        } else {
            $function = "INSTALLER_install_{$step['type']}";
            if (function_exists($function)) {
                $result = $function($step, $vars);
                if (is_numeric($result)) {
                    INSTALLER_fail($reverse);
                    return $result;
                } else if (!empty($result)) {
                    $reverse[] = $result;
                }
            } else {
                $dump = var_dump($step);
                COM_errorLog('Can\'t process step: '.$dump);
                INSTALLER_fail($reverse);
                return 1;
            }
        }
    }

    $plugin = $A['plugin'];

    $cfgFunction = $plugin['name'].'_load_configuration';
    // Load the online configuration records
    if (function_exists($cfgFunction)) {
        if (!$cfgFunction()) {
            COM_errorLog("AutoInstall: Failed to load the default configuration");
            INSTALLER_fail($reverse);
            return 1;
        }
    } else {
        COM_errorLog("AutoInstall: No default config found: ".$cfgFunction);
    }

    // Finally, register the plugin with glFusion
    COM_errorLog ("Registering {$plugin['display']} plugin with glFusion", 1);

    // silently delete an existing entry
    DB_delete($_TABLES['plugins'], 'pi_name', $plugin['name']);

    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
           . "VALUES ('{$plugin['name']}', '{$plugin['ver']}', '{$plugin['gl_ver']}', '{$plugin['url']}', 1)", 1);

    return 0;
}

function INSTALLER_uninstall($A)
{
    global $_TABLES;

    $reverse = array_reverse($A);
    $plugin = Array();
    foreach ($reverse as $step) {
        if ($step['type'] == 'feature') {
            $ft_name = addslashes($step['feature']);
            $ft_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$ft_name'");

            COM_errorLog("Removing feature {$step['feature']}....");
            DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $ft_id", 1);
            DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $ft_id", 1);
        } else if ($step['type'] == 'group') {
            $grp_name = addslashes($step['group']);
            $grp_id = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = '$grp_name'");

            COM_errorLog("Removing group {$step['group']}....");
            DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_grp_id = $grp_id", 1);
            DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id OR ug_grp_id = $grp_id", 1);
            DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id", 1);
        } else if ($step['type'] == 'table') {
            COM_errorLog("Dropping table {$step['table']}....");
            DB_query("DROP TABLE {$step['table']}",1);
        } else if ($step['type'] == 'block') {
            COM_errorLog("Removing block {$step['name']}....");
            DB_query("DELETE FROM {$_TABLES['blocks']} WHERE name = '{$step['name']}'", 1);
        } else if ($step['type'] == 'sql') {
            if (isset($step['rev_log'])) {
                COM_errorLog($step['rev_log']);
            }
            if (isset($step['rev'])) {
                DB_query($step['rev'],1);
            }
        } else if (array_key_exists('type', $step)) {
            $function = 'INSTALLER_uninstall_'.$step['type'];
            if (function_exists($function)) {
                $function($step);
            }
        }
    }

    if (array_key_exists('plugin', $A)) {
        $plugin = $A['plugin'];
        COM_errorLog("Removing plugin {$plugin['name']} from plugins table", 1);
        DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = '{$plugin['name']}'", 1);
    }

    COM_errorLog("Uninstall complete");
    return true;
}

} //!defined('INSTALLER_VERSION')

?>