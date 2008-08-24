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
$_SQL[] = "REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion', '1.1.0')";
?>