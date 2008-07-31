<?php
$_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('database_version', '1')";
$_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE type type varchar(30) NOT NULL default 'article'";
$_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'geeklog'";
$_SQL[] = "UPDATE {$_TABLES['syndication']} SET type = 'article' WHERE type = 'glfusion'";
$_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion', '1.1.0')";
?>