<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - blaine AT portalparts DOT com                |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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
            COM_errorLog("StaticPage Update Error: Could not execute the following SQL: " . $sql);
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
