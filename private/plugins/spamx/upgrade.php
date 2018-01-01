<?php
// +--------------------------------------------------------------------------+
// | Spam-X Plugin - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors      Tom Willett     tomw AT pigstye DOT net                     |
// |              Dirk Haun       dirk AT haun-online DOT de                  |
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

function spamx_upgrade()
{
    global $_TABLES, $_CONF, $_DB_dbms, $_SPX_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='spamx'");

    switch( $currentVersion ) {
        case '1.1.0' :
        case '1.1.1' :
        case '1.1.2' :
        case '1.1.3' :
        case '1.1.4' :
        case '1.1.5' :
        case '1.1.6' :
        case '1.1.7' :
        case '1.1.8' :
        case '1.1.9' :
        case '1.2.0' :
            $c = config::get_instance();
            $c->add('fs_sfs', NULL, 'fieldset', 0, 1, NULL, 0, true, 'spamx');
            $c->add('sfs_username_check', false, 'select',0, 1, 1, 10, true, 'spamx');
            $c->add('sfs_email_check', true, 'select',0, 1, 1, 20, true, 'spamx');
            $c->add('sfs_ip_check', true, 'select',0, 1, 1, 30, true, 'spamx');
            $c->add('sfs_username_confidence', '99.00', 'text',0, 1, 1, 40, true, 'spamx');
            $c->add('sfs_email_confidence', '50.00', 'text',0, 1, 1, 50, true, 'spamx');
            $c->add('sfs_ip_confidence', '25.00', 'text',0, 1, 1, 60, true, 'spamx');

        case '1.2.1' :
            $c = config::get_instance();
            $c->add('fs_slc', NULL, 'fieldset', 0, 2, NULL, 0, true, 'spamx');
            $c->add('slc_max_links', 5, 'text',0, 2, 1, 10, true, 'spamx');

        case '1.2.2' :
            $c = config::get_instance();
            $c->add('debug', 0, 'select',0, 0, 1, 15, true, 'spamx');
            $c->add('fs_akismet', NULL, 'fieldset', 0, 3, NULL, 0, true, 'spamx');
            $c->add('akismet_enabled', 0, 'select',0, 3, 1, 10, true, 'spamx');
            $c->add('akismet_api_key', '', 'text',0, 3, NULL, 20, true, 'spamx');

        case '1.3.0' :
            $_SQL = array();

            $_SQL[] = "ALTER TABLE {$_TABLES['spamx']} ADD COLUMN id INT(10) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)";

            $_SQL[] = "
            CREATE TABLE {$_TABLES['spamx_stats']} (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `module` VARCHAR(128) NOT NULL DEFAULT '',
              `type` VARCHAR(50) NOT NULL DEFAULT '',
              `blockdate` DATETIME,
              `ip` VARCHAR(50) NOT NULL DEFAULT '',
              `email` VARCHAR(50) NOT NULL DEFAULT '',
              `username` VARCHAR(50) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              INDEX `type` (`type`),
              INDEX `blockdate` (`blockdate`)
            ) ENGINE=MyISAM
            ";

            if (($_DB_dbms == 'mysql') && (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'") == 'InnoDB')) {
                $use_innodb = true;
            } else {
                $use_innodb = false;
            }

            foreach ($_SQL AS $sql) {
                if ($use_innodb) {
                    $sql = str_replace('MyISAM', 'InnoDB', $sql);
                }
                DB_query($sql,1);
            }

        default :
            spamx_update_config();
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_SPX_CONF['pi_version']."',pi_gl_version='".$_SPX_CONF['gl_version']."' WHERE pi_name='spamx' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='spamx'") == $_SPX_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}

function spamx_update_config()
{
    global $_CONF, $_AC_CONF, $_TABLES;

    $c = config::get_instance();

    require_once $_CONF['path'].'plugins/spamx/sql/spamx_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='spamx'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$spamxConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='spamx'");
        } else {
            $spamxConfigData[$key]['indb'] = 1;
        }
    }
    // add any missing items
    foreach ($spamxConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('spamx');
    // sync up sequence, etc.
    foreach ( $spamxConfigData AS $cfgItem ) {
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

function _searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $array[$key];
       }
   }
   return null;
}

function _searchForIdKey($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $key;
       }
   }
   return null;
}

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
?>