<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* Plugin Upgrade
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by Consult4Hire Inc.
*  Author:
*  Blaine Lang          blaine AT portalparts DOT com
*
*/
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function filemgmt_upgrade()
{
    global $_TABLES,$_CONF,$_TABLES,$_FM_CONF, $_DB_table_prefix;;

    include $_CONF['path'].'/plugins/filemgmt/config.php';
    include $_CONF['path'].'/plugins/filemgmt/filemgmt.php';

    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $cur_version = DB_getItem($_TABLES['plugins'],'pi_version', "pi_name='filemgmt'");

    switch ( $cur_version ) {
        case '1.3' :
            DB_query("ALTER TABLE {$_TABLES['filemgmt_cat']} ADD `grp_access` mediumint(8) DEFAULT '2' NOT NULL AFTER imgurl");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.5' WHERE pi_name = 'filemgmt'");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version = '1.0.0' WHERE pi_name = 'filemgmt'");

            // Update all the comment records
            $result = DB_query("SELECT cid,sid FROM {$_TABLES['comments']} WHERE type='filemgmt'");
            while (list($cid,$sid) = DB_fetchArray($result)) {
                if (strpos($sid,'fileid_') === FALSE) {
                    $sid = "fileid_{$sid}";
                    DB_query("UPDATE {$_TABLES['comments']} SET sid='$sid' WHERE cid='$cid'");
                }
            }
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.5' WHERE pi_name = 'filemgmt'");
        case '1.5' :
        case '1.5.1' :
        case '1.5.2' :
        case '1.5.3' :
            DB_query("ALTER TABLE {$_TABLES['filemgmt_cat']} ADD `grp_writeaccess` MEDIUMINT( 8 ) NOT NULL DEFAULT '1'");
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '1.6' WHERE pi_name = 'filemgmt'");
        case '1.6' :
        case '1.6.0' :
            // need to migrate the configuration to our new online configuration.
            require_once $_CONF['path_system'] . 'classes/config.class.php';
            require_once $_CONF['path'] . 'plugins/filemgmt/install_defaults.php';
            plugin_initconfig_filemgmt();
        case '1.7.0.fusion' :
            $c = config::get_instance();
            $c->add('outside_webroot', 0, 'select', 0, 2, 0, 100, true, 'filemgmt');
        case '1.7.0' :
        case '1.7.1' :
        case '1.7.2' :
        case '1.7.3' :
        case '1.7.4' :
            DB_query("UPDATE {$_TABLES['filemgmt_filedetail']} set rating = rating / 2",1);
            $result = DB_query("SELECT * FROM {$_TABLES['filemgmt_filedetail']} WHERE votes > 0");
            while ( $F = DB_fetchArray($result) ) {
                $item_id = $F['lid'];
                $votes   = $F['votes'];
                $rating  = $F['rating'];
                DB_query("INSERT INTO {$_TABLES['rating']} (type,item_id,votes,rating) VALUES ('filemgmt','".$item_id."',$votes,$rating);",1);
            }

            $result = DB_query("SELECT * FROM {$_TABLES['filemgmt_votedata']}");
            while ( $H = DB_fetchArray($result) ) {
                $item_id = $H['lid'];
                $user_id = $H['ratinguser'];
                $ip      = $H['ratinghostname'];
                $time    = $H['ratingtimestamp'];
                $rating  = $H['rating'] / 2;
                DB_query("INSERT INTO {$_TABLES['rating_votes']} (type,item_id,rating,uid,ip_address,ratingdate) VALUES ('filemgmt','".$item_id."',$rating,$user_id,'".$ip."',$time);",1);
            }
        case '1.7.5' :
        case '1.7.6' :
            $c = config::get_instance();
            $c->add('enable_rating', 1,'select',0, 2, 0, 35, true, 'filemgmt');
            $c->add('silent_edit_default', 1,'select',0, 2, 0, 37, true, 'filemgmt');
            $c->add('displayblocks', 0,'select', 0, 0, 3, 115, true, 'filemgmt');

        case '1.7.7' :
            DB_query("UPDATE `{$_TABLES['filemgmt_history']}` SET `date` = '1970-01-01 00:00:00' WHERE CAST(`date` AS CHAR(20)) = '0000-00-00 00:00:00';",1);
            DB_query("ALTER TABLE `{$_TABLES['filemgmt_history']}` CHANGE COLUMN `date` `date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';",1);

        case '1.7.8' :
            DB_query("ALTER TABLE `{$_TABLES['filemgmt_history']}` CHANGE COLUMN `remote_ip` `remote_ip` VARCHAR(48) NOT NULL DEFAULT '' ;",1);
            DB_query("ALTER TABLE `{$_TABLES['filemgmt_brokenlinks']}` CHANGE COLUMN `ip` `ip` VARCHAR(48) NOT NULL DEFAULT '' ;",1);

        case '1.7.9' :
            // no changes

        case '1.8.0':
            DB_query("ALTER TABLE {$_TABLES['filemgmt_filedesc']} DROP KEY `lid`", 1);
            DB_query("ALTER TABLE {$_TABLES['filemgmt_filedesc']} ADD PRIMARY KEY (`lid`)");

        case '1.9.0':
            DB_query("ALTER TABLE {$_TABLES['filemgmt_filedetail']} CHANGE COLUMN `version` `version` VARCHAR(25) NOT NULL DEFAULT '';");

        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '".$_FM_CONF['pi_version']."',pi_gl_version = '".$_FM_CONF['gl_version']."' WHERE pi_name = 'filemgmt'");
            return true;
    }


    filemgmt_update_config();

    CTL_clearCache();    

     if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='filemgmt'") == $_FM_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }


}

function filemgmt_update_config()
{
    global $_CONF, $_FM_CONF, $_TABLES;

    $c = config::get_instance();

    require_once $_CONF['path'].'plugins/filemgmt/sql/filemgmt_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='filemgmt'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$filemgmtConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='filemgmt'");
        } else {
            $filemgmtConfigData[$key]['indb'] = 1;
        }
    }
    // add any missing items
    foreach ($filemgmtConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('filemgmt');
    // sync up sequence, etc.
    foreach ( $filemgmtConfigData AS $cfgItem ) {
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

        $Qargs = array(
                       $data['name'],
                       $data['set'] ? serialize($data['default_value']) : 'unset',
                       $data['type'],
                       $data['subgroup'],
                       $data['group'],
                       $data['fieldset'],
                       ($data['selection_array'] === null) ?
                        -1 : $data['selection_array'],
                       $data['sort'],
                       $data['set'],
                       serialize($data['default_value']));
        $Qargs = array_map('DB_escapeString', $Qargs);

        $sql = "INSERT INTO {$_TABLES['conf_values']} (name, value, type, " .
            "subgroup, group_name, selectionArray, sort_order,".
            " fieldset, default_value) VALUES ("
            ."'{$Qargs[0]}',"   // name
            ."'{$Qargs[1]}',"   // value
            ."'{$Qargs[2]}',"   // type
            ."{$Qargs[3]},"     // subgroup
            ."'{$Qargs[4]}',"   // groupname
            ."{$Qargs[6]},"     // selection array
            ."{$Qargs[7]},"     // sort order
            ."{$Qargs[5]},"     // fieldset
            ."'{$Qargs[9]}')";  // default value

        DB_query($sql);
    }
}
