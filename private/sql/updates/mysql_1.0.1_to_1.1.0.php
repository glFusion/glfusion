<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | mysql_1.0.1_to_1.1.0.php                                                 |
// |                                                                          |
// | glFusion Upgrade SQL                                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

$_SQL[] = "
CREATE TABLE {$_TABLES['commentedits']} (
  cid int(10) NOT NULL,
  uid mediumint(8) NOT NULL,
  time datetime NOT NULL,
  PRIMARY KEY (cid)
) ENGINE=MYISAM
";

$_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD name varchar(32) default NULL AFTER indent";
$_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD comment_expire datetime NOT NULL default '1000-01-01 00:00:00.000000' AFTER comments";
$_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('database_version', '1')";
$_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE type type varchar(30) NOT NULL default 'article'";
$_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'geeklog'";
$_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'glfusion'";
$_SQL[] = "UPDATE {$_TABLES['conf_values']} SET type='select',default_value='s:10:\"US/Central\";' WHERE name='timezone'";
$_SQL[] = "UPDATE {$_TABLES['conf_values']} SET value='s:10:\"US/Central\";' WHERE name='timezone' AND value=''";
$_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion', '1.1.0')";

// Staticpages plugin updates
function upgrade_StaticpagesPlugin()
{
    global $_CONF, $_TABLES;

    $plugin_path = $_CONF['path'] . 'plugins/staticpages/';

    $P_SQL = array();
    $P_SQL[] = "ALTER TABLE {$_TABLES['staticpage']} ADD sp_search tinyint(4) NOT NULL default '1' AFTER postmode";
    // allow searching on all existing static pages
    $P_SQL[] = "UPDATE {$_TABLES['staticpage']} SET sp_search = 1";
    $P_SQL[] = "UPDATE {$_TABLES['plugins']} SET pi_version = '1.5.1', pi_gl_version = '1.1.0', pi_homepage='http://www.glfusion.org' WHERE pi_name = 'staticpages'";

    foreach ($P_SQL as $sql) {
        $rst = DB_query($sql,1);
        if (DB_error()) {
            echo "There was an error upgrading the Static Pages plugin, SQL: $sql<br>";
        }
    }
    if (file_exists($plugin_path . 'config.php')) {
        // Rename the existing config.php as it's not needed any more
        $ren = @rename($plugin_path . 'config.php',
                       $plugin_path . 'config-pre1.1.0.php');
    }
    return true;
}

?>