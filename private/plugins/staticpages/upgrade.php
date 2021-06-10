<?php
/**
* glFusion CMS - Static Pages Plugin
*
* Upgrade
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
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

function staticpages_upgrade()
{
    global $_TABLES, $_CONF, $_SP_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='staticpages'");

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    switch( $currentVersion ) {
        case '1.5.0' :
            $rc = update_150_to_151();
        case '1.5.1' :
        case '1.5.2' :
        case '1.5.3' :
            DB_query("ALTER TABLE {$_TABLES['staticpage']} ADD sp_search tinyint(4) NOT NULL default '1' AFTER postmode",1);
        case '1.5.4' :
            DB_query("ALTER TABLE {$_TABLES['staticpage']} ADD sp_status tinyint(3) NOT NULL DEFAULT '1' AFTER sp_id");
            // static pages configuration options
            $c->add('include_search', 1, 'select',0, 0, 0, 95, true, 'staticpages');
            $c->add('comment_code', -1, 'select',0, 0,17, 97, true, 'staticpages');
            $c->add('status_flag', 1, 'select',0, 0, 13, 99, true, 'staticpages');
        case '1.6.0' :
            DB_query("ALTER TABLE {$_TABLES['staticpage']} CHANGE `sp_tid` `sp_tid` VARCHAR(128) NOT NULL DEFAULT 'none';",1);

        case '1.6.1' :
            DB_query("ALTER TABLE {$_TABLES['staticpage']} CHANGE `sp_id` `sp_id` VARCHAR(128) NOT NULL DEFAULT '';",1);

        case '1.6.2' :
            $c->del('atom_max_items', 'staticpages');

        case '1.6.3' :
            DB_query("ALTER TABLE {$_TABLES['staticpage']} CHANGE `sp_content` `sp_content` MEDIUMTEXT NOT NULL;",1);

        case '1.6.4' :
            DB_query("UPDATE `{$_TABLES['staticpage']}` SET `sp_date` = '1970-01-01 00:00:00' WHERE CAST(`sp_date` AS CHAR(20)) = '0000-00-00 00:00:00';");
            DB_query("UPDATE `{$_TABLES['staticpage']}` SET `sp_date` = '1970-01-01 00:00:00' WHERE CAST(`sp_date` AS CHAR(20)) = '1000-00-00 01:01:00';");
            DB_query("ALTER TABLE `{$_TABLES['staticpage']}` CHANGE COLUMN `sp_date` `sp_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';",1);

        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_SP_CONF['pi_version']."',pi_gl_version='".$_SP_CONF['gl_version']."' WHERE pi_name='staticpages' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='staticpages'") == $_SP_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}

function update_150_to_151()
{
    global $_TABLES, $_CONF, $_SP_CONF;

    $P_SQL = array();

    $P_SQL[] = "ALTER TABLE {$_TABLES['staticpage']} ADD sp_search tinyint(4) NOT NULL default '1' AFTER postmode";
    // allow searching on all existing static pages
    $P_SQL[] = "UPDATE {$_TABLES['staticpage']} SET sp_search = 1";
    $P_SQL[] = "UPDATE {$_TABLES['plugins']} SET pi_version = '1.5.1', pi_gl_version = '1.1.0', pi_homepage='http://www.glfusion.org' WHERE pi_name = 'staticpages'";

    foreach ($P_SQL as $sql) {
        $rst = DB_query($sql,1);
        if (DB_error()) {
            Log::write('system',Log::ERROR,'StaticPage Update Error: Could not execute the following SQL: ' . $sql);
            return false;
        }
    }
    $res = DB_query("SELECT * FROM {$_TABLES['vars']} WHERE name='sp_fix_01'");
    if ( DB_numRows($res) < 1 ) {
        $sql = "SELECT * FROM {$_TABLES['staticpage']}";

        $result = DB_query($sql);
        while ($A=DB_fetchArray($result)) {
            $newcontent = stripslashes($A['sp_content']);
            $newcontent = mysql_real_escape_string($newcontent);
            DB_query("UPDATE {$_TABLES['staticpage']} SET sp_content='".$newcontent."' WHERE sp_id='".$A['sp_id']."'");
        }
        DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('sp_fix_01', 1)",1);
    }

    return true;
}
?>
