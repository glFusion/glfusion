<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-install.php                                                          |
// |                                                                          |
// | Install/Uninstall library.                                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

    COM_errorLog("AutoInstall: Creating group {$step['group']}...");
    $grp_name = DB_escapeString($step['group']);
    $grp_desc = DB_escapeString($step['desc']);
    if (isset($step['admin']) && $step['admin'] == true) {
        $admin = 2;
    } else {
        $admin = 0;
    }
    if ( isset($step['default']) && $step['default'] == true ) {
        $default = 1;
    } else {
        $default = 0;
    }
    DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr,grp_gl_core,grp_default) VALUES ('$grp_name', '$grp_desc',$admin,$default)", 1);
    if (DB_error()) {
        COM_errorLog("AutoInstall: Group creation failed!");
        return 1;
    }
    $grp_id = DB_insertId();
    $vars[$step['variable']] = $grp_id;
    $vars['__groups'][$step['variable']] = $grp_name;

    if (isset($step['addroot'])) {
        DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_grp_id) VALUES ($grp_id, 1)", 1);
    }

    if ( isset($step['default']) && $step['default'] == true ) {
        INSTALLER_applyGroupDefault($grp_id,true);
    }
    return "DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id";
}

function INSTALLER_install_addgroup($step, &$vars)
{
    global $_TABLES;

    COM_errorLog("AutoInstall: Adding a group to another group...");
    if (array_key_exists('parent_var',$step)) {
        $parent_grp = $vars[$step['parent_var']];
    } elseif (array_key_exists('parent_grp',$step)) {
        $parent_grp = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = '" . DB_escapeString($step['parent_grp']) . "'");
    } else {
        $parent_grp = 0;
    }
    if (array_key_exists('child_var',$step)) {
        $child_grp = $vars[$step['child_var']];
    } elseif (array_key_exists('child_grp',$step)) {
        $child_grp = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = '" . DB_escapeString($step['child_grp']) . "'");
    } else {
        $child_grp = 0;
    }
    $parent_grp = intval($parent_grp);
    $child_grp = intval($child_grp);
    if ($parent_grp == 0 || $child_grp == 0) {
        COM_errorLog("AutoInstall: Parent or child group missing!");
        return 1;
    }

    DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_grp_id) VALUES ($parent_grp, $child_grp)", 1);
    if (DB_error()) {
        COM_errorLog("AutoInstall: Failed to assign group!");
        return 1;
    }
    return "DELETE FROM {$_TABLES['group_assignments']} where ug_main_grp_id = $parent_grp and grp_id = $child_grp";
}

function INSTALLER_install_feature($step, &$vars)
{
    global $_TABLES;

    COM_errorLog("AutoInstall: Creating feature {$step['feature']}...");
    $ft_name = DB_escapeString($step['feature']);
    $ft_desc = DB_escapeString($step['desc']);
    DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) VALUES ('$ft_name', '$ft_desc')", 1);
    if (DB_error()) {
        COM_errorLog("AutoInstall: Feature creation failed!");
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
        COM_errorLog("AutoInstall: ".$step['log']);
    } else {
        COM_errorLog("AutoInstall: Mapping a feature to a group...");
    }
    if (array_key_exists('findgroup', $step)) {
        $grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = '" . DB_escapeString($step['findgroup']) . "'"));

        if ($grp_id == 0) {
            COM_errorLog("AutoInstall: Could not find existing '{$step['findgroup']}' group!");
            return 1;
        }
    } else {
        $grp_id = $vars[$step['group']];
    }
    $ft_id = $vars[$step['feature']];
    DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
    if (DB_error()) {
        COM_errorLog("AutoInstall: Mapping failed!");
        return 1;
    }
    return "DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $ft_id AND acc_grp_id = $grp_id";
}

function INSTALLER_install_table($step, &$vars)
{
    global $_DB_dbms, $_TABLES;

    COM_errorLog("AutoInstall: Creating table {$step['table']}...");
    static $use_innodb = null;
    if ($use_innodb === null) {
        if (($_DB_dbms == 'mysql') &&
            (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
            $use_innodb = true;
        } else {
            $use_innodb = false;
        }
    }

    $sql55 = str_replace("TYPE=MyISAM","ENGINE=MyISAM", $step['sql']);

    if ($use_innodb) {
        $sql = str_replace('MyISAM', 'InnoDB', $sql55);
    } else {
        $sql = $sql55;
    }

    DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("AutoInstall: Failed to create table {$step['table']}!");
        return 1;
    }
    return "DROP TABLE {$step['table']}";
}

function INSTALLER_extract_params($str, $delim)
{
    $params = array();
    for ($i=0; $i < strlen($str);) {
        $j = strpos($str, $delim, $i);
        if ($j > 0) {
            $j = $j + strlen($delim);
            $k = strpos($str, $delim, $j);
            if ($k > 0) {
                $l = $k - $j;
                $params[] = substr($str, $j, $l);
                $i = $k + strlen($delim);
            } else {
                break;
            }
        } else {
            break;
        }
    }
    return $params;
}

function INSTALLER_install_sql($step, &$vars)
{
    if (isset($step['log'])) {
        COM_errorLog("AutoInstall: ".$step['log']);
    }

    if (array_key_exists('sql', $step)) {
        $query = (is_array($step['sql'])) ? $step['sql'] : array($step['sql']);
        foreach ($query as $sql) {
            // check for replaceable parameters
            $params = INSTALLER_extract_params($sql,'%%');
            // replace any that correspond to a $vars key with the assoc value
            foreach($params as $param) {
                $sql = (array_key_exists($param, $vars)) ? str_replace('%%'.$param.'%%', $vars[$param], $sql) : $sql;
            }
            DB_query($sql, 1);
            if (DB_error()) {
                COM_errorLog("AutoInstall: SQL failed! ".htmlspecialchars($step['sql']));
                return 1;
            }
        }
    }

    return isset($step['rev']) ? $step['rev'] : '';
}

function INSTALLER_fail_sql($step, &$vars)
{
    if (array_key_exists('rev', $step)) {
        $query = (is_array($step['rev'])) ? $step['rev'] : array($step['rev']);
        foreach ($query as $sql) {
            // check for replaceable parameters
            $params = INSTALLER_extract_params($sql,'%%');
            // replace any that correspond to a $vars key with the assoc value
            foreach($params as $param) {
                $sql = (array_key_exists($param, $vars)) ? str_replace('%%'.$param.'%%', $vars[$param], $sql) : $sql;
            }
            DB_query($sql, 1);
        }
    }
}

function INSTALLER_install_block($step, &$vars)
{
    global $_TABLES, $_CONF, $_USER;

    COM_errorLog("AutoInstall: Creating block {$step['name']}...");
    $is_enabled = isset($step['is_enabled']) ? intval($step['is_enabled']) : 1;
    $rdflimit = isset($step['rdflimit']) ? intval($step['rdflimit']) : 0;
    $onleft = isset($step['onleft']) ? intval($step['onleft']) : 0;
    $allow_autotags = isset($step['allow_autotags']) ? intval($step['allow_autotags']) : 0;
    $name = isset($step['name']) ? DB_escapeString($step['name']) : '';
    $title = isset($step['title']) ? DB_escapeString($step['title']) : '';
    $type = isset($step['block_type']) ? DB_escapeString($step['block_type']) : 'unknown';
    $phpblockfn = isset($step['phpblockfn']) ? DB_escapeString($step['phpblockfn']) : '';
    $help = isset($step['help']) ? DB_escapeString($step['help']) : '';
    $content = isset($step['content']) ? DB_escapeString($step['content']) : '';
    $blockorder = isset($step['blockorder']) ? intval($step['blockorder']) : 9999;
    $owner_id = isset($_USER['uid']) ? $_USER['uid'] : 2;
    $group_id = isset($vars[$step['group_id']]) ? $vars[$step['group_id']] : 1;
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = $_CONF['default_permissions_block'];
    DB_query("INSERT INTO {$_TABLES['blocks']} "
           . "(is_enabled,name,type,title,tid,blockorder,content,allow_autotags,rdflimit,onleft,phpblockfn,help,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon)"
   . " VALUES ($is_enabled,'$name','$type','$title','all',$blockorder,'$content',$allow_autotags,$rdflimit,$onleft,'$phpblockfn','$help',$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon)", 1);
    if (DB_error()) {
        COM_errorLog("AutoInstall: Block creation failed!");
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
        COM_errorLog("AutoInstall: Don't know what var to create");
        return 1;
    }
    $vars[$step['variable']] = DB_getItem($table, $col, $where);
    $vars[$major][$step['variable']] = $vars[$step['variable']];
    return '';
}

function INSTALLER_install_mkdir($step, &$vars)
{
    if (array_key_exists('dirs', $step)) {
        $dirs = (is_array($step['dirs'])) ? $step['dirs'] : array($step['dirs']);
        foreach ($dirs as $path) {
            COM_errorlog("AutoInstall: Creating directory $path");
            $ret = @mkdir($path);
            file_put_contents($path . '/index.html', '');
        }
    }
    return array('type' => 'rmdir', 'dirs' => $dirs);
}

function INSTALLER_fail_rmdir($step)
{
    if (array_key_exists('dirs', $step)) {
        $dirs = (is_array($step['dirs'])) ? $step['dirs'] : array($step['dirs']);
        foreach ($dirs as $path) {
            COM_errorlog("AutoInstall: FAIL: removing directory $path");
            @rmdir($path);
        }
    }
}

function INSTALLER_fail($pluginName,$rev)
{
    $A = array_reverse($rev);
    foreach ($A as $sql) {
        if (empty($sql)) {
            // no step
        } elseif (is_array($sql)) {
            if (array_key_exists('type', $sql)) {
                $function = 'INSTALLER_fail_'.$type;
                if (function_exists($function)) {
                    COM_errorlog("AutoInstall: FAIL: calling $function");
                    $function($sql);
                }
            }
        } else {
            COM_errorLog("AutoInstall: FAIL: $sql");
            DB_query($sql, 1);
        }
    }
    PLG_uninstall($pluginName);
}

function INSTALLER_install($A)
{
    global $_TABLES;

    COM_errorLog("AutoInstall: **** Start Installation ****");

    if (!isset($A['installer']) OR $A['installer']['version'] != INSTALLER_VERSION) {
        COM_errorLog('AutoInstall: Invalid or Unknown installer version');
        COM_errorLog("AutoInstall: **** END Installation ****");
        return 2;
    }
    if (!isset($A['plugin'])) {
        COM_errorLog("AutoInstall: Missing plugin description!");
        COM_errorLog("AutoInstall: **** END Installation ****");
        return 1;
    }
    if ( !isset($A['plugin']['name'])) {
        COM_errorLog("AutoInstall: Missing plugin name!");
        COM_errorLog("AutoInstall: **** END Installation ****");
        return 1;
    }
    if (!COM_checkVersion(GVERSION, $A['plugin']['gl_ver'])) {
        COM_errorLog("AutoInstall: Plugin requires glFusion v".$A['plugin']['gl_ver']." or greater");
        COM_errorLog("AutoInstall: **** END Installation ****");
        return 1;
    }

    $pluginName = $A['plugin']['name'];

    $vars = array('__groups' => array(), '__features' => array(), '__blocks' => array());
    $reverse = array();
    foreach ($A as $meta => $step) {
        if ($meta === 'installer') { // must use === when since 0 == 'anystring' is true
        } elseif ($meta === 'plugin') {
            if (!isset($step['name'])) {
                COM_errorLog("AutoInstall: Missing plugin name!");
                INSTALLER_fail($pluginName,$reverse);
                COM_errorLog("AutoInstall: **** END Installation ****");
                return 1;
            }
        } else {
            $function = "INSTALLER_install_{$step['type']}";
            if (function_exists($function)) {
                $result = $function($step, $vars);
                if (is_numeric($result)) {
                    INSTALLER_fail($pluginName,$reverse);
                    COM_errorLog("AutoInstall: **** END Installation ****");
                    return $result;
                } else if (!empty($result)) {
                    $reverse[] = $result;
                }
            } else {
                $dump = var_dump($step);
                COM_errorLog('Can\'t process step: '.$dump);
                INSTALLER_fail($pluginName,$reverse);
                COM_errorLog("AutoInstall: **** END Installation ****");
                return 1;
            }
        }
    }

    $plugin = $A['plugin'];

    $cfgFunction = 'plugin_load_configuration_'.$plugin['name'];
    // Load the online configuration records
    if (function_exists($cfgFunction)) {
        if (!$cfgFunction()) {
            COM_errorLog("AutoInstall: Failed to load the default configuration");
            INSTALLER_fail($pluginName,$reverse);
            COM_errorLog("AutoInstall: **** END Installation ****");
            return 1;
        }
    } else {
        COM_errorLog("AutoInstall: No default config found: ".$cfgFunction);
    }

    // Finally, register the plugin with glFusion
    COM_errorLog ("AutoInstall: Registering {$plugin['display']} plugin with glFusion", 1);

    // silently delete an existing entry
    DB_delete($_TABLES['plugins'], 'pi_name', $plugin['name']);

    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
           . "VALUES ('{$plugin['name']}', '{$plugin['ver']}', '{$plugin['gl_ver']}', '{$plugin['url']}', 1)", 1);

    // run any post install routines
    $postInstallFunction = 'plugin_postinstall_'.$plugin['name'];
    if ( function_exists($postInstallFunction) ) {
        $postInstallFunction();
    } else {
        COM_errorLog("AutoInstall: No post installation routine found.");
    }

    COM_errorLog("AutoInstall: **** END Installation ****");
    CTL_clearCache();
    return 0;
}

function INSTALLER_uninstall($A)
{
    global $_TABLES;

    $reverse = array_reverse($A);
    $plugin = array();
    foreach ($reverse as $step) {
        if ($step['type'] == 'feature') {
            $ft_name = DB_escapeString($step['feature']);
            $ft_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$ft_name'");

            COM_errorLog("AutoInstall: Removing feature {$step['feature']}....");
            DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $ft_id", 1);
            DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $ft_id", 1);
        } else if ($step['type'] == 'group') {
            $grp_name = DB_escapeString($step['group']);
            $grp_id = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = '$grp_name'");

            COM_errorLog("AutoInstall: Removing group {$step['group']}....");
            DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_grp_id = $grp_id", 1);
            DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id OR ug_grp_id = $grp_id", 1);
            DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id", 1);
        } else if ($step['type'] == 'table') {
            COM_errorLog("AutoInstall: Dropping table {$step['table']}....");
            DB_query("DROP TABLE {$step['table']}",1);
        } else if ($step['type'] == 'block') {
            COM_errorLog("AutoInstall: Removing block {$step['name']}....");
            DB_query("DELETE FROM {$_TABLES['blocks']} WHERE name = '{$step['name']}'", 1);
        } else if ($step['type'] == 'sql') {
            if (isset($step['rev_log'])) {
                COM_errorLog("AutoInstall: ". $step['rev_log']);
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
        COM_errorLog("AutoInstall: Removing plugin {$plugin['name']} from plugins table", 1);
        DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = '{$plugin['name']}'", 1);
    }

    COM_errorLog("AutoInstall: Uninstall complete");
    return true;
}

/**
* Add or remove a default group to/from all existing accounts
*
* @param    int     $grp_id     ID of default group
* @param    boolean $add        true: add, false: remove
* @return   void
*
*/
function INSTALLER_applyGroupDefault($grp_id, $add = true)
{
    global $_TABLES, $_GROUP_VERBOSE;

    /**
    * In the "add" case, we have to insert one record for each user. Pack this
    * many values into one INSERT statement to save some time and bandwidth.
    */
    $_values_per_insert = 25;

    if ($_GROUP_VERBOSE) {
        if ($add) {
            COM_errorLog("Adding group '$grp_id' to all user accounts");
        } else {
            COM_errorLog("Removing group '$grp_id' from all user accounts");
        }
    }

    if ($add) {
        $result = DB_query("SELECT uid FROM {$_TABLES['users']} WHERE uid > 1");
        $num_users = DB_numRows($result);
        for ($i = 0; $i < $num_users; $i += $_values_per_insert) {
            $u = array();
            for ($j = 0; $j < $_values_per_insert; $j++) {
                list($uid) = DB_fetcharray($result);
                $u[] = $uid;
                if ($i + $j + 1 >= $num_users) {
                    break;
                }
            }
            $v = "($grp_id," . implode("), ($grp_id,", $u) . ')';
            DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid) VALUES " . $v);
        }
    } else {
        DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE (ug_main_grp_id = $grp_id) AND (ug_grp_id IS NULL)");
    }
}

} //!defined('INSTALLER_VERSION')

/**
* Creates a unique temporary directory
*
* Creates a temp directory int he $_CONF['path_data] directory
*
* @return   bool              True on success, false on fail
*
*/
function _io_mktmpdir() {
    global $_CONF;

    $base = $_CONF['path_data'];
    $dir  = md5(uniqid(mt_rand(), true));
    $tmpdir = $base.$dir;

    if(fusion_io_mkdir_p($tmpdir)) {
        return($dir);
    } else {
        return false;
    }
}

/**
* Creates a directory
*
* @parm     string  $target   Directory to create.
* @return   bool              True on success, false on fail
*
*/
function fusion_io_mkdir_p($target){
    global $_CONF;

    if (@is_dir($target)||empty($target)) return 1; // best case check first

    if (@file_exists($target) && !@is_dir($target)) return 0;

    if (fusion_io_mkdir_p(substr($target,0,strrpos($target,'/')))){
        $ret = @mkdir($target,0755);
        @chmod($target, 0755);
        return $ret;
    }
    return 0;
}


/**
* Deletes a directory (with recursive sub-directory support)
*
* @parm     string            Path of directory to remove
* @return   bool              True on success, false on fail
*
*/
function _pi_deleteDir($path) {
    if (!is_string($path) || $path == "") return false;
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
      if (!$dh = @opendir($path)) return false;

    while (false !== ($f = readdir($dh))) {
        if ($f == '..' || $f == '.') continue;
        _pi_deleteDir("$path/$f");
    }

      closedir($dh);
      return @rmdir($path);
    } else {
      return @unlink($path);
    }

    return false;
}


function _pi_parseXML($tmpDirectory)
{
    global $_CONF, $pluginData;

    if (!$dh = @opendir($tmpDirectory)) {
        return false;
    }
    while (false !== ($file = readdir($dh))) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }
        if ( @is_dir($tmpDirectory . '/' . $file) ) {
            $filename = $tmpDirectory . '/' . $file . '/plugin.xml';
            break;
        }
    }
    closedir($dh);

    if (!($fp=@fopen($filename, "r"))) {
        return -1;
    }

    $pluginData = array();

    if (!($xml_parser = xml_parser_create()))
        return false;

    xml_set_element_handler($xml_parser,"_pi_startElementHandler","_pi_endElementHandler");
    xml_set_character_data_handler( $xml_parser, "_pi_characterDataHandler");

    while( $data = fread($fp, 4096)){
        if(!xml_parse($xml_parser, $data, feof($fp))) {
            break;
        }
    }
    xml_parser_free($xml_parser);
}

/**
* XML startElement callback
*
* used for plugin.xml parsing
*
* @param    object $parser  Handle to the parser object
* @param    string $name    Name of element
* @param    array  $attrib  array of attributes for element
* @return   none
*
*/
function _pi_startElementHandler ($parser,$name,$attrib) {
    global $pluginData;
    global $state;

    switch ($name) {
        case 'ID' :
            $state = 'id';
            break;
        case 'NAME' :
            $state = 'pluginname';
            break;
        case 'VERSION' :
            $state = 'pluginversion';
            break;
        case 'GLFUSIONVERSION' :
            $state = 'glfusionversion';
            break;
        case 'PHPVERSION' :
            $state = 'phpversion';
            break;
        case 'DESCRIPTION' :
            $state = 'description';
            break;
        case 'URL' :
            $state = 'url';
            break;
        case 'MAINTAINER' :
            $state = 'maintainer';
            break;
        case 'DATABASE' :
            $state = 'database';
            break;
        case 'REQUIRES' :
            $state = 'requires';
            break;
        case 'DATAPROXYDRIVER' :
            $state = 'dataproxydriver';
            break;
        case 'LAYOUT' :
            $state = 'layout';
            break;
        case 'RENAMEDIST' :
            $state = 'renamedist';
            break;
    }
}

function _pi_endElementHandler ($parser,$name){
    global $pluginData;
    global $state;

    $state='';
}

function _pi_characterDataHandler ($parser, $data) {
    global $pluginData;
    global $state;


    if (!$state) {
        return;
    }

    switch ($state) {
        case 'id' :
            $pluginData['id'] = $data;
            break;
        case 'pluginname' :
            $pluginData['name'] = $data;
            break;
        case 'pluginversion' :
            $pluginData['version'] = $data;
            break;
        case 'glfusionversion' :
            $pluginData['glfusionversion'] = $data;
            break;
        case 'phpversion' :
            $pluginData['phpversion'] = $data;
            break;
        case 'description' :
            $pluginData['description'] = $data;
            break;
        case 'url' :
            $pluginData['url'] = $data;
            break;
        case 'maintainer' :
            $pluginData['author'] = $data;
            break;
        case 'database' :
            $pluginData['database'] = $data;
            break;
        case 'requires' :
            $pluginData['requires'][] = $data;
            break;
        case 'dataproxydriver' :
            $pluginData['dataproxydriver'] = $data;
            break;
        case 'layout' :
            $pluginData['layout'] = $data;
            break;
        case 'renamedist' :
            $pluginData['renamedist'][] = $data;
            break;
    }
}


/**
* Copies srcdir to destdir (recursive)
*
* @param    string  $srcdir Source Directory
* @param    string  $dstdir Destination Directory
*
* @return   string          comma delimited list success,fail,size,failedfiles
*                           5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
*
*/
function _pi_dir_copy($srcdir, $dstdir )
{
    $num = 0;
    $fail = 0;
    $sizetotal = 0;
    $fifail = '';
    $verbose = 0;
    $ret ='0,0,0,';
    $failedFiles = array();
    $success = 1;


    if (!@is_dir($dstdir)) fusion_io_mkdir_p($dstdir);
    if ($curdir = @opendir($srcdir)) {
        while (false !== ($file = readdir($curdir))) {
            if ($file != '.' && $file != '..') {
                $srcfile = $srcdir . '/' . $file;
                $dstfile = $dstdir . '/' . $file;
                if (is_file($srcfile)) {
                    if (@copy($srcfile, $dstfile)) {
                        @touch($dstfile, filemtime($srcfile)); $num++;
                        @chmod($dstfile, 0644);
                        $sizetotal = ($sizetotal + filesize($dstfile));
                    } else {
                        COM_errorLog("PLG-INSTALL: File '$srcfile' could not be copied!");
                        $fail++;
                        $fifail = $fifail.$srcfile.'|';
                        $success = 0;
                    }
                }
                else if (@is_dir($srcfile)) {
                    $ret = _pi_dir_copy($srcfile, $dstfile, $verbose);
                    list($dcsuccess,$dcfailed,$dcsize,$dcfaillist) = explode(',',$ret);
                    $success = $dcsuccess;
                    $fail += $dcfailed;
                    $sizetotal += $dcsize;
                    $fifail .= $fifail.$dcfaillist;
                }
            }
        }
        closedir($curdir);
    } else {
        COM_errorLog("PLG-INSTALL: Unable to open temporary directory: " . $srcdir);
        $ret ='0,1,0,Unable to open temp. directory';
        return $ret;
    }
    $retval = $success . ',' . $fail . ',' . $sizetotal . ',' . $fifail;
    return $retval;
}


/**
* Copies srcfile to destdir
*
* @param    string  $srcdir Source Directory
* @param    string  $dstdir Destination Directory
*
* @return   string          comma delimited list success,fail,size,failedfiles
*                           5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
*
*/
function _pi_file_copy($srcfile, $dstdir )
{
    if (!@is_dir($dstdir)) fusion_io_mkdir_p($dstdir);
    if (is_file($srcfile)) {
        $dstfile = $dstdir . '/' . basename($srcfile);
        if (@copy($srcfile, $dstfile)) {
            @touch($dstfile, filemtime($srcfile));
            @chmod($dstfile, 0644);
        } else {
            COM_errorLog("INSTALL: File '$srcfile' could not be copied!");
            return false;
        }
    } else {
        COM_errorLog("INSTALL: Unable to open temporary file: " . $srcfile);
        return false;
    }
    return true;
}

function _pi_test_copy($srcdir, $dstdir)
{
    $num        = 0;
    $fail       = 0;
    $sizetotal  = 0;
    $createdDst = 0;
    $ret        = '';
    $verbose    = 0;

    $failedFiles = array();

    if(!@is_dir($dstdir)) {
        $rc = fusion_io_mkdir_p($dstdir);
        if ($rc == false ) {
            $failedFiles[] = $dstdir;
            COM_errorLog("PLG-INSTALL: Error: Unable to create directory " . $dstdir);
            return array(1,$failedFiles);
        }
        $createdDst = 1;
    }

    if ($curdir = @opendir($srcdir)) {
        while (false !== ($file = readdir($curdir))) {
            if ($file != '.' && $file != '..') {
                $srcfile = $srcdir . '/' . $file;
                $dstfile = $dstdir . '/' . $file;
                if (is_file($srcfile)) {
                    if ( !COM_isWritable($dstfile) ) {
                        $failedFiles[] = $dstfile;
                        COM_errorLog("PLG-INSTALL: Error: File '$dstfile' cannot be written");
                        $fail++;
                    }
                } else if (@is_dir($srcfile)) {
                    $res = explode(',',$ret);
                    list($ret,$failed) = _pi_test_copy($srcfile, $dstfile, $verbose);
                    $failedFiles = array_merge($failedFiles,$failed);
                    if ( $ret != 0 ) $fail++;
                }
            }
        }
        closedir($curdir);
    }
    if ($createdDst == 1) {
        @rmdir($dstdir);
    }
    return array($fail,$failedFiles);
}


function _pi_errorBox( $errMsg )
{
    global $_CONF,$LANG32;

    $retval = '';

    return COM_showMessageText($errMsg, $LANG32[56], true, 'error');
}


function _pi_Header()
{
    global $_CONF, $LANG_ADMIN,$LANG32;

    $retval = '';

    $retval .= COM_startBlock($LANG32[73], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));


    return $retval;
}

?>