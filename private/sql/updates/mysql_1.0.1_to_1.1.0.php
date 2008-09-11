<?php

$_SQL[] = "
CREATE TABLE {$_TABLES['commentedits']} (
  cid int(10) NOT NULL,
  uid mediumint(8) NOT NULL,
  time datetime NOT NULL,
  PRIMARY KEY (cid)
) TYPE=MYISAM
";
$_SQL[] = "ALTER TABLE {$_TABLES['comments']} ADD name varchar(32) default NULL AFTER indent";
$_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD comment_expire datetime NOT NULL default '0000-00-00 00:00:00' AFTER comments";
$_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('database_version', '1')";
$_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE type type varchar(30) NOT NULL default 'article'";
$_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'geeklog'";
$_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'glfusion'";
$_SQL[] = "UPDATE {$_TABLES['configuration']} SET type='select',default_value='s:10:\"US/Central\";' WHERE name='timezone'";
$_SQL[] = "UPDATE {$_TABLES['configuration']} SET value='s:10:\"US/Central\";' WHERE name='timezone' AND value=''";
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
            return false;
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