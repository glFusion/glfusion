<?php
/**
* glFusion CMS
*
* glFusion Plugin Install / Uninstall Library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2010-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2010 by the following authors:
*   Authors: Joe Mucchiello  joe AT throwingdice DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

if (!defined('INSTALLER_VERSION')) {
    define('INSTALLER_VERSION','1');
}

function INSTALLER_install_feature($step, &$vars)
{
    global $_TABLES;

    $db = Database::getInstance();

    Log::write('system',Log::INFO,"AutoInstall: Creating feature {$step['feature']}...");
    $ft_name = $step['feature'];
    $ft_desc = $step['desc'];

    try {
        $stmt = $db->conn->insert(
                            $_TABLES['features'],
                            array(
                                'ft_name' => $step['feature'],
                                'ft_descr' => $step['desc']
                            )
                );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,"AutoInstall: Feature creation failed!");
        return 1;
    }

    $ft_id = $db->conn->lastInsertId();

    $vars[$step['variable']] = $ft_id;
    $vars['__groups'][$step['variable']] = $ft_name;

    return array( 'sql' => array(
                        "DELETE FROM `{$_TABLES['features']}` WHERE ft_id=?",
                        array($ft_id),
                        array(Database::INTEGER)
                    )
            );
}

function INSTALLER_install_group($step, &$vars)
{
    global $_TABLES;

    $db = Database::getInstance();

    Log::write('system',Log::INFO,"AutoInstall: Creating group {$step['group']}...");
    $grp_name = $step['group'];
    $grp_desc = $step['desc'];
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

    try {
        $stmt = $db->conn->insert(
                            $_TABLES['groups'],
                            array(
                                'grp_name' => $grp_name,
                                'grp_descr' => $grp_desc,
                                'grp_gl_core' => $admin,
                                'grp_default' => $default
                            ),
                            array(
                                Database::STRING,
                                Database::STRING,
                                Database::INTEGER,
                                Database::INTEGER
                            )
                 );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,"AutoInstall: Group creation failed!");
        return 1;
    }

    $grp_id = $db->conn->lastInsertId();

    $vars[$step['variable']] = $grp_id;
    $vars['__groups'][$step['variable']] = $grp_name;

    if (isset($step['addroot'])) {
        try {
            $db->conn->insert(
                $_TABLES['group_assignments'],
                array(
                    'ug_main_grp_id' => $grp_id,
                    'ug_grp_id'      => 1
                    ),
                array(Database::INTEGER,Database::INTEGER)
            );
        } catch(\Doctrine\DBAL\DBALException $e) {
            Log::write('system',Log::ERROR,"AutoInstall: Error inserting Group Assignment for Root");
        }

        if ( isset($step['default']) && $step['default'] == true ) {
            INSTALLER_applyGroupDefault($grp_id,true);
        }
    }

    return array(
                    'sql' =>
                        array(
                            "DELETE FROM `{$_TABLES['groups']}` WHERE grp_id=?",
                            array($grp_id),
                            array(Database::INTEGER)
                        )
                );
}

function INSTALLER_install_addgroup($step, &$vars)
{
    global $_TABLES;

    $db = Database::getInstance();

    Log::write('system',Log::INFO,"AutoInstall: Adding a group to another group...");
    if (array_key_exists('parent_var',$step)) {
        $parent_grp = $vars[$step['parent_var']];
    } elseif (array_key_exists('parent_grp',$step)) {

        $parent_grp = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name'=>$step['parent_grp']));
    } else {
        $parent_grp = 0;
    }
    if (array_key_exists('child_var',$step)) {
        $child_grp = $vars[$step['child_var']];
    } elseif (array_key_exists('child_grp',$step)) {
        $child_grp = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name' => $step['child_grp']));
    } else {
        $child_grp = 0;
    }
    $parent_grp = intval($parent_grp);
    $child_grp = intval($child_grp);

    if ($parent_grp == 0) {
        Log::write('system',Log::ERROR,"AutoInstall: ERROR: Parent group not found");
        return 1;
    }
    if ($child_grp == 0) {
        Log::write('system',Log::ERROR,"AutoInstall: ERROR: Child group not found");
        return 1;
    }

    try {
        $db->conn->insert(
            $_TABLES['group_assignments'],
            array(
                'ug_main_grp_id' => $parent_grp,
                'ug_grp_id' => $child_grp
            ),
            array(
                Database::INTEGER,
                Database::INTEGER
            )
        );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,"AutoInstall: Failed to assign group!");
        return 1;
    }

    return array( 'sql' => array(
                            "DELETE FROM `{$_TABLES['group_assignments']}` where ug_main_grp_id = ? and grp_id = ?",
                            array($parent_grp,$child_grp),
                            array(Database::INTEGER,Database::INTEGER)
                            )
           );
}

function INSTALLER_install_mapping($step, &$vars)
{
    global $_TABLES;

    $db = Database::getInstance();

    if (isset($step['log'])) {
        Log::write('system',Log::INFO,"AutoInstall: ".$step['log']);
    } else {
        Log::write('system',Log::INFO,"AutoInstall: Mapping a feature to a group...");
    }
    if (array_key_exists('findgroup', $step)) {

        $grp_id = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name' => $step['findgroup']));

        if ($grp_id === false) {
            Log::write('system',Log::ERROR,"AutoInstall: Could not find existing '{$step['findgroup']}' group!");
            return 1;
        }
    } else {
        $grp_id = $vars[$step['group']];
    }
    $ft_id = $vars[$step['feature']];

    try {
        $db->conn->insert(
            $_TABLES['access'],
            array(
                'acc_ft_id' => $ft_id,
                'acc_grp_id' => $grp_id
            ),
            array(
                Database::INTEGER,
                Database::INTEGER
            )
        );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,"AutoInstall: Mapping failed!");
        return 1;
    }

    return array( 'sql' => array(
                    "DELETE FROM `{$_TABLES['access']}` WHERE acc_ft_id = ? AND acc_grp_id = ?",
                    array($ft_id,$grp_id),
                    array(Database::INTEGER, Database::INTEGER)
                    )
            );
}

function INSTALLER_install_table($step, &$vars)
{
    global $_DB_dbms, $_TABLES;

    Log::write('system',Log::INFO,"AutoInstall: Creating table {$step['table']}...");

    $db = Database::getInstance();

    static $use_innodb = null;

    if ($use_innodb === null) {

        $siteDBType = $db->getItem($_TABLES['vars'],'value',array('name' => 'database_engine'));

        if (($_DB_dbms == 'mysql') AND $siteDBType == 'InnoDB') {
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

    try {
        $stmt = $db->conn->query($sql);
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,"AutoInstall: Failed to create table {$step['table']}");
        return 1;
    }

    return array('sql' => array("DROP TABLE `{$step['table']}`",array(),array()));
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
        Log::write('system',Log::INFO,"AutoInstall: ".$step['log']);
    }

    $db = Database::getInstance();

    if (array_key_exists('sql', $step)) {
        $query = (is_array($step['sql'])) ? $step['sql'] : array($step['sql']);
        foreach ($query as $sql) {
            // check for replaceable parameters
            $params = INSTALLER_extract_params($sql,'%%');
            // replace any that correspond to a $vars key with the assoc value
            foreach($params as $param) {
                $sql = (array_key_exists($param, $vars)) ? str_replace('%%'.$param.'%%', $vars[$param], $sql) : $sql;
            }
            try {
                $stmt = $db->conn->query($sql);
            } catch(\Doctrine\DBAL\DBALException $e) {
                Log::write('system',Log::ERROR,"AutoInstall: SQL failed!", array($step['sql']));
                return 1;
            }
       }
    }

    return isset($step['rev']) ? $step['rev'] : '';
}

function INSTALLER_fail_sql($step, &$vars)
{
    $db = Database::getInstance();

    if (array_key_exists('rev', $step)) {
        $query = (is_array($step['rev'])) ? $step['rev'] : array($step['rev']);
        foreach ($query as $sql) {
            // check for replaceable parameters
            $params = INSTALLER_extract_params($sql,'%%');
            // replace any that correspond to a $vars key with the assoc value
            foreach($params as $param) {
                $sql = (array_key_exists($param, $vars)) ? str_replace('%%'.$param.'%%', $vars[$param], $sql) : $sql;
            }
            try {
                $stmt = $db->conn->query($sql);
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }

        }
    }
}

function INSTALLER_install_block($step, &$vars)
{
    global $_TABLES, $_CONF, $_USER;

    $db = Database::getInstance();

    Log::write('system',Log::INFO,"AutoInstall: Creating block {$step['name']}...");

    $is_enabled     = isset($step['is_enabled']) ? intval($step['is_enabled']) : 1;
    $rdflimit       = isset($step['rdflimit']) ? intval($step['rdflimit']) : 0;
    $onleft         = isset($step['onleft']) ? intval($step['onleft']) : 0;
    $allow_autotags = isset($step['allow_autotags']) ? intval($step['allow_autotags']) : 0;
    $name           = isset($step['name']) ? $step['name'] : '';
    $title          = isset($step['title']) ? $step['title'] : '';
    $type           = isset($step['block_type']) ? $step['block_type'] : 'unknown';
    $phpblockfn     = isset($step['phpblockfn']) ? $step['phpblockfn'] : '';
    $help           = isset($step['help']) ? $step['help'] : '';
    $content        = isset($step['content']) ? $step['content'] : '';
    $blockorder     = isset($step['blockorder']) ? intval($step['blockorder']) : 9999;
    $owner_id       = isset($_USER['uid']) ? $_USER['uid'] : 2;
    $group_id       = isset($vars[$step['group_id']]) ? $vars[$step['group_id']] : 1;
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = $_CONF['default_permissions_block'];

    try {
        $db->conn->insert(
            $_TABLES['blocks'],
            array(
                'is_enabled' => $is_enabled,
                'name' => $name,
                'type' => $type,
                'title' => $title,
                'tid' => 'all',
                'blockorder' => $blockorder,
                'content' => $content,
                'allow_autotags' => $allow_autotags,
                'rdflimit' => $rdflimit,
                'onleft' => $onleft,
                'phpblockfn' => $phpblockfn,
                'help' => $help,
                'owner_id' => $owner_id,
                'group_id' => $group_id,
                'perm_owner' => $perm_owner,
                'perm_group' => $perm_group,
                'perm_members' => $perm_members,
                'perm_anon' => $perm_anon
            ),
            array(
                Database::INTEGER,  // is_enabled
                Database::STRING,   // name
                Database::STRING,   // type
                Database::STRING,   // title
                Database::STRING,   // tid
                Database::INTEGER,  // blockorder
                Database::STRING,   // content
                Database::INTEGER,  // allow_autotags
                Database::INTEGER,  // rdflimit
                Database::INTEGER,  // onleft
                Database::STRING,   // phpblockfn
                Database::STRING,   // help
                Database::INTEGER,  // owner_id
                Database::INTEGER,  // group_id
                Database::INTEGER,  // perm_owner
                Database::INTEGER,  // perm_group
                Database::INTEGER,  // perm_members
                Database::INTEGER   // perm_anonymous
            )
        );
    } catch(\Doctrine\DBAL\DBALException $e) {
        Log::write('system',Log::ERROR,"AutoInstall: Block creation failed!",array('Plugin::'.$type));
        return 1;
    }
    $bid = $db->conn->lastInsertId();

    if (isset($step['variable'])) {
        $vars[$step['variable']] = $bid;
    }

    return array('sql'=>
                    array("DELETE FROM `{$_TABLES['blocks']}` WHERE bid=?",array($bid),array(Database::INTEGER))
           );
}


function INSTALLER_install_createvar($step, &$vars)
{
    global $_TABLES, $_CONF, $_USER;

    $db = Database::getInstance();

    if (isset($step['group'])) {
        $table = $_TABLES['groups'];
        $col = 'grp_id';
//        $where = "grp_name = ?";
//        $data = $step['group'];
//        $type = Database::STRING;
        $major = '__group';

        $itemWhat = array('grp_name' => $step['group']);
        $itemType = array(Database::STRING);

    } elseif (isset($step['feature'])) {
        $table = $_TABLES['features'];
        $col = 'ft_id';
//        $where = "ft_name = ?";
//        $data = $step['feature'];
//        $type = Database::STRING;
        $major = '__feature';

        $itemWhat = array('ft_id' => $step['feature']);
        $itemType = array(Database::INTEGER);

    } else {
        COM_errorLog("AutoInstall: Don't know what var to create");
        return 1;
    }
//    $sql = "SELECT " . $col . " FROM `".$table."` WHERE " . $where;

    $vars[$step['variable']] = $db->getItem($table,$col,$itemWhat, $itemType);

//    $vars[$step['variable']] = $db->conn->fetchColumn($sql,array($data),0,array($type));
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
            COM_errorlog("AutoInstall: UNDO removing directory $path");
            @rmdir($path);
        }
    }
}

function INSTALLER_fail($pluginName,$rev)
{
    $db = Database::getInstance();

    $A = array_reverse($rev);

    foreach ($A AS $action) {
        if (!empty($action['sql'])) {
            COM_errorLog("Autoinstall: UNDO: ". $action['sql'][0]);
            try {
                $db->conn->executeUpdate($action['sql'][0],$action['sql'][1],$action['sql'][2]);
            } catch(\Doctrine\DBAL\DBALException $e) {
                // no action - just ignore the error
            }
        } elseif (!empty($action['type'])) {
            $function = 'INSTALLER_fail_'.$action['type'];
            if (function_exists($function)) {
                COM_errorlog("AutoInstall: UNDO calling $function");
                $function($action);
            }
        }
    }
    PLG_uninstall($pluginName);
}

function INSTALLER_install($A)
{
    global $_TABLES, $_CONF, $_PLUGIN_INFO;

    $db = Database::getInstance();

    COM_errorLog("AutoInstall: **** Start Installation ****");

    if (!function_exists('INSTALLER_install_feature')) {
        print "broken";exit;
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

    // check prerequisites
    require_once $_CONF['path'].'system/classes/pluginxml.class.php';

    $xml = new pluginXML;
    if ( $xml->parseXMLFile($_CONF['path'].'plugins/'.$pluginName.'/plugin.xml') == -1 ) {
        COM_errorLog("AutoInstall: Unable to locate plugin.xml");
    } else {
        $pluginData = $xml->getPluginData();
        $errors = 0;
        if ( isset($pluginData['requires']) && is_array($pluginData['requires']) ) {
            foreach ($pluginData['requires'] AS $reqPlugin ) {
                if ( strstr($reqPlugin,",") !== false ) {
                    list($reqPlugin, $required_ver) = @explode(',', $reqPlugin);
                } else {
                    $reqPlugin = $reqPlugin;
                    $required_ver = '';
                }
                if (!isset($_PLUGIN_INFO[$reqPlugin]) || $_PLUGIN_INFO[$reqPlugin]['pi_enabled'] == 0 ) {
                    COM_errorLog("AutoInstall: Plugin requires " . $reqPlugin . " be installed and enabled");
                    // required plugin not installed
                    $errors++;
                } elseif (!empty($required_ver)) {
                    $installed_ver = $_PLUGIN_INFO[$reqPlugin]['pi_version'];
                    if (!COM_checkVersion($installed_ver, $required_ver)) {
                        // required plugin installed, but wrong version
                        COM_errorLog("AutoInstall: Plugin requires " . $reqPlugin . " v".$required_ver." or greater");
                        $errors++;
                    }
                }
            }
        }
        if ( $errors ) {
            COM_errorLog("AutoInstall: Plugin install failed prerequisite check");
            COM_errorLog("AutoInstall: **** END Installation ****");
            return 1;
        }
    }

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
            if (function_exists("INSTALLER_install_{$step['type']}")) {
                $result = $function($step, $vars);
                if (is_numeric($result)) {
                    INSTALLER_fail($pluginName,$reverse);
                    COM_errorLog("AutoInstall: **** END Installation ****");
                    return $result;
                } else if (!empty($result)) {
                    $reverse[] = $result;
                }
            } else {
                $dump = print_r($step,true);
                COM_errorLog('Can\'t process step: ' . $function .PHP_EOL.$dump);
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

    $db->conn->delete($_TABLES['plugins'], array('pi_name' => $plugin['name']));

    $db->conn->insert($_TABLES['plugins'],
        array(
            'pi_name' => $plugin['name'],
            'pi_version' => $plugin['ver'],
            'pi_gl_version' => $plugin['gl_ver'],
            'pi_homepage' => $plugin['url'],
            'pi_enabled' => 1
            )
    );

    // run any post install routines
    $postInstallFunction = 'plugin_postinstall_'.$plugin['name'];
    if ( function_exists($postInstallFunction) ) {
        COM_errorLog("AutoInstall: Running post installation routine.");
        $postInstallFunction();
    } else {
        COM_errorLog("AutoInstall: No post installation routine found.");
    }

    COM_errorLog("AutoInstall: **** END Installation ****");
    CACHE_clear();
    return 0;
}

function INSTALLER_uninstall($A)
{
    global $_TABLES;

    $db = Database::getInstance();

    $reverse = array_reverse($A);
    $plugin = array();
    foreach ($reverse as $step) {
        if ($step['type'] == 'feature') {
            $ft_id = $db->getItem($_TABLES['features'],'ft_id',array('ft_name' => $step['feature']));

            COM_errorLog("AutoInstall: Removing feature {$step['feature']}....");

            try {
                $db->conn->delete($_TABLES['access'],array('acc_ft_id'=>$ft_id),array(Database::INTEGER));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
            try {
                $db->conn->delete($_TABLES['features'],array('ft_id' => $ft_id),array(Database::INTEGER));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
        } else if ($step['type'] == 'group') {
            $grp_id = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name' => $step['group']));

            COM_errorLog("AutoInstall: Removing group {$step['group']}....");

            try {
                $db->conn->delete($_TABLES['access'],array('acc_grp_id' => $grp_id),array(Database::INTEGER));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
            try {
                $db->conn->delete($_TABLES['group_assignments'],array('ug_main_grp_id' => $grp_id),array(Database::INTEGER));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
            try {
                $db->conn->delete($_TABLES['group_assignments'],array('ug_grp_id' => $grp_id),array(Database::INTEGER));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
            try {
                $db->conn->delete($_TABLES['groups'],array('grp_id' => $grp_id),array(Database::INTEGER));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
;
        } else if ($step['type'] == 'table') {
            COM_errorLog("AutoInstall: Dropping table {$step['table']}....");
            try {
                $stmt = $db->conn->executeUpdate("DROP TABLE `{$step['table']}`");
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore the error
            }
        } else if ($step['type'] == 'block') {
            COM_errorLog("AutoInstall: Removing block {$step['name']}....");
            try {
                $db->conn->delete($_TABLES['blocks'],array('name' => $step['name']),array(Database::STRING));
            } catch(\Doctrine\DBAL\DBALException $e) {
                // ignore error
            }
        } else if ($step['type'] == 'sql') {
            if (isset($step['rev_log'])) {
                COM_errorLog("AutoInstall: ". $step['rev_log']);
            }
            if (isset($step['rev'])) {
                try {
                    $db->conn->query($step['rev']);
                } catch(\Doctrine\DBAL\DBALException $e) {
                    // ignore error
                }
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

        try {
            $db->conn->delete($_TABLES['plugins'],array('pi_name' => $plugin['name']),array(Database::STRING));
        } catch(\Doctrine\DBAL\DBALException $e) {
            // ignore error
        }
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
        $stmt = $db->conn->query("SELECT uid FROM `{$_TABLES['users']}` WHERE uid > 1");

        try {
            $num_users = $stmt->rowCount();
        } catch (PDOException $e) {
            $num_users = 0;
        }
        for ($i = 0; $i < $num_users; $i += $_values_per_insert) {
            $u = array();
            for ($j = 0; $j < $_values_per_insert; $j++) {
                $row = $stmt->fetch(Database::ASSOCIATIVE);
                $u[] = $row['uid'];
                if ($i + $j + 1 >= $num_users) {
                    break;
                }
            }
            $v = "($grp_id," . implode("), ($grp_id,", $u) . ')';
            $db->conn->query("INSERT INTO `{$_TABLES['group_assignments']}` (ug_main_grp_id, ug_uid) VALUES " . $v);
        }
    } else {
        try {
            $db->conn->executeUpdate("DELETE FROM `{$_TABLES['group_assignments']}` WHERE (ug_main_grp_id = ?) AND (ug_grp_id IS NULL)",array($grp_id),array(Database::INTEGER));
        } catch(\Doctrine\DBAL\DBALException $e) {
            COM_errorLog("Error removing group assignments");
        }
    }
}


function _pi_parseXML($tmpDirectory)
{
    global $_CONF, $pluginData;

    $filename = '';

    if (!$dh = @opendir($tmpDirectory)) {
        return false;
    }

    while (false !== ($file = readdir($dh))) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }
        if ( @is_dir($tmpDirectory . '/' . $file) ) {
            $filename = $tmpDirectory . '/' . $file . '/plugin.xml';
            if ( @file_exists($filename)) {
                break;
            }
        }
    }
    closedir($dh);

    if ( $filename == '' ) {
        return -1;
    }

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

function _update_config($plugin, $configData)
{
    global $_CONF, $_TABLES;

    $c = config::get_instance();

    // remove stray items

    $db = Database::getInstance();

    $stmt = $db->conn->executeQuery("SELECT * FROM `{$_TABLES['conf_values']}` WHERE group_name=?",array($plugin),array(Database::STRING));
    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$configData)) === NULL ) {
            try {
                $db->conn->delete($_TABLES['conf_values'],array('name' => $item, 'group_name' => $plugin));
            } catch(\Doctrine\DBAL\DBALException $e) {
                return;
            }
        } else {
            $configData[$key]['indb'] = 1;
        }
    }
    // add any missing items
    foreach ($configData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config($plugin);
    // sync up sequence, etc.
    foreach ( $configData AS $cfgItem ) {
        $c->sync(
            $cfgItem['name'],
            $cfgItem['default_value'],
            $cfgItem['type'],
            $cfgItem['subgroup'],
            $cfgItem['fieldset'],
            $cfgItem['selection_array'],
            $cfgItem['sort'],
            $cfgItem['set'],
            $cfgItem['group']
        );
    }
}


if ( !function_exists('_searchForId')) {
    function _searchForId($id, $array) {
       foreach ($array as $key => $val) {
           if ($val['name'] === $id) {
               return $array[$key];
           }
       }
       return null;
    }
}
if ( !function_exists('_searchForIdKey')) {
    function _searchForIdKey($id, $array) {
       foreach ($array as $key => $val) {
           if ($val['name'] === $id) {
               return $key;
           }
       }
       return null;
    }
}
if ( !function_exists('_addConfigItem')) {
    function _addConfigItem($data = array() )
    {
        global $_TABLES;

        $db = Database::getInstance();

        $Qargs = array(
                       $data['name'],
                       $data['set'] ? serialize($data['default_value']) : 'unset',
                       $data['type'],
                       $data['subgroup'],
                       $data['group'],
                       $data['fieldset'],
                       ($data['selection_array'] === null) ? -1 : $data['selection_array'],
                       $data['sort'],
                       $data['set'],
                       serialize($data['default_value']));

        try {
            $db->conn->insert(
                $_TABLES['conf_values'],
                array(
                    'name' => $Qargs[0],
                    'value' => $Qargs[1],
                    'type' => $Qargs[2],
                    'subgroup' => $Qargs[3],
                    'group_name' => $Qargs[4],
                    'selectionArray' => $Qargs[6],
                    'sort_order' => $Qargs[7],
                    'fieldset' => $Qargs[5],
                    'default_value' => $Qargs[9]
                ),
                array(
                    Database::STRING,
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                    Database::INTEGER,
                    Database::INTEGER,
                    Database::STRING
                )
            );
        } catch(\Doctrine\DBAL\DBALException $e) {
            COM_errorLog("Error updating configuration");
        }
    }
}
?>
