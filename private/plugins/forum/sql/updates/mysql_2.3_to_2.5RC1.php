<?php

$_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD is_hidden tinyint(1) DEFAULT '0' NOT NULL AFTER grp_id";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD is_readonly tinyint(1) DEFAULT '0' NOT NULL AFTER is_hidden";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_forums']} ADD no_newposts tinyint(1) DEFAULT '0' NOT NULL AFTER is_readonly";

$_SQL[] = "ALTER TABLE {$_TABLES['ff_moderators']} ADD mod_uid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_id";
$_SQL[] = "ALTER TABLE {$_TABLES['ff_moderators']} ADD mod_groupid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_uid";
?>