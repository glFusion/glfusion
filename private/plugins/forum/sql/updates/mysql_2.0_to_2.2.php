<?php

$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_enable tinyint(1) DEFAULT '0' NOT NULL AFTER postsperpage";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_homepage tinyint(1) DEFAULT '0' NOT NULL AFTER cb_enable";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_where tinyint(1) DEFAULT '0' NOT NULL AFTER cb_homepage";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_topic']} DROP cat";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_topic']} DROP topicimg";

$_SQL[] = "CREATE TABLE {$_TABLES['gf_userinfo']} (
  `uid` mediumint(8) NOT NULL default '0',
  `location` varchar(128) NOT NULL default '',
  `aim` varchar(128) NOT NULL default '',
  `icq` varchar(128) NOT NULL default '',
  `yim` varchar(128) NOT NULL default '',
  `msnm` varchar(128) NOT NULL default '',
  `interests` varchar(255) NOT NULL default '',
  `occupation` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`uid`)
) TYPE=MyISAM COMMENT='Forum Extra User Profile Information';";

?>