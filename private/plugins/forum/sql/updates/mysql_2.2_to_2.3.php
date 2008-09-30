<?php


$_SQL[] = "UPDATE {$_TABLES['plugins']} SET `pi_version` = '2.3' WHERE `pi_name` = 'forum' LIMIT 1";

$_SQL[] = "ALTER TABLE  {$_TABLES['gf_topic']} ADD INDEX `idxtopicuid` ( `uid` )";
$_SQL[] = "ALTER TABLE  {$_TABLES['gf_topic']} ADD INDEX `idxtopicpid` ( `pid` )";

$_SQL[] = "ALTER TABLE {$_TABLES['gf_userprefs']} ADD enablenotify tinyint(1) DEFAULT '1' NOT NULL AFTER viewanonposts";

?>