<?php

$_SQL[] = "Delete from {$_TABLES['ff_log']}";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} ADD moved tinyint(1) DEFAULT '0' NOT NULL AFTER sticky";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} ADD locked tinyint(1) DEFAULT '0' NOT NULL AFTER sticky";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} ADD lastupdated varchar(12) DEFAULT NULL AFTER date";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} CHANGE `id` `id`   MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} CHANGE `pid` `pid` MEDIUMINT( 8 ) NOT NULL";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} CHANGE `uid` `uid` MEDIUMINT( 8 ) NOT NULL";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_watch']} ADD forum_id mediumint(8) NOT NULL AFTER id";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_watch']} ADD topic_id mediumint(8) NOT NULL AFTER id";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_watch']} ADD date_added date NOT NULL AFTER uid";

$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} DROP fid";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} DROP cat";

$_SQL[] = "ALTER TABLE {$_TABLES['ff_watch']} DROP notify_status";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD INDEX forum_cat (forum_cat)";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD INDEX forum_id (forum_id)";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_log']} ADD INDEX uid_forum (uid,forum)";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_log']} ADD INDEX uid_topic (uid,topic)";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_log']} ADD INDEX forum (forum)";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_topic']} ADD INDEX forum (forum)";


$_SQL[] = "CREATE TABLE {$_TABLES['ff_userprefs']} (
	  uid mediumint(8) NOT NULL default '0',
	  topicsperpage int(3) NOT NULL default '5',
	  postsperpage int(3) NOT NULL default '5',
	  popularlimit int(3) NOT NULL default '10',
	  messagesperpage int(3) NOT NULL default '20',
	  searchlines int(3) NOT NULL default '20',
	  viewanonposts int(1) NOT NULL default '1',
	  alwaysnotify int(1) NOT NULL default '0',
	  membersperpage int(3) NOT NULL default '20',
	  showiframe int(1) NOT NULL default '1',
	  PRIMARY KEY  (uid)
	) ENGINE=MyISAM";

?>