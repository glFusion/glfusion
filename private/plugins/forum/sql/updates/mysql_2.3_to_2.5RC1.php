<?php


$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD allow_htmlsig tinyint(1) DEFAULT '0' NOT NULL AFTER allow_notify";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD allow_userdatefmt tinyint(1) DEFAULT '0' NOT NULL AFTER allow_htmlsig";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD refresh_delay tinyint(1) DEFAULT '0' NOT NULL AFTER autorefresh";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD messagesperpage tinyint(1) DEFAULT '0' NOT NULL AFTER postsperpage";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD searchesperpage tinyint(1) DEFAULT '0' NOT NULL AFTER messagesperpage";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD popular tinyint(1) DEFAULT '0' NOT NULL AFTER searchesperpage";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD speedlimit tinyint(1) DEFAULT '0' NOT NULL AFTER popular";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_spamxfilter tinyint(1) DEFAULT '0' NOT NULL AFTER edit_timewindow";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_smiliesplugin tinyint(1) DEFAULT '0' NOT NULL AFTER use_spamxfilter";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_pmplugin tinyint(1) DEFAULT '0' NOT NULL AFTER use_smiliesplugin";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_subjectsize tinyint(1) DEFAULT '0' NOT NULL AFTER cb_where";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD cb_numposts tinyint(1) DEFAULT '0' NOT NULL AFTER cb_subjectsize";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD sb_subjectsize tinyint(1) DEFAULT '0' NOT NULL AFTER cb_numposts";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD sb_numposts tinyint(1) DEFAULT '0' NOT NULL AFTER sb_subjectsize";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD sb_latestposts tinyint(1) DEFAULT '0' NOT NULL AFTER sb_numposts";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD min_comment_len tinyint(1) DEFAULT '0' NOT NULL AFTER sb_latestposts";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD min_name_len tinyint(1) DEFAULT '0' NOT NULL AFTER min_comment_len";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD min_subject_len tinyint(1) DEFAULT '0' NOT NULL AFTER min_name_len";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD html_newline tinyint(1) DEFAULT '0' NOT NULL AFTER min_subject_len";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD is_hidden tinyint(1) DEFAULT '0' NOT NULL AFTER grp_id";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD is_readonly tinyint(1) DEFAULT '0' NOT NULL AFTER is_hidden";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_forums']} ADD no_newposts tinyint(1) DEFAULT '0' NOT NULL AFTER is_readonly";
----------

$_SQL[] = "ALTER TABLE {$_TABLES['gf_moderators']} ADD mod_uid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_id";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_moderators']} ADD mod_groupid mediumint(8) DEFAULT '0' NOT NULL AFTER mod_uid";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD use_geshi_formatting tinyint(1) DEFAULT '0' NOT NULL AFTER glfilter";
$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} ADD edit_timewindow tinyint(1) DEFAULT '0' NOT NULL AFTER speedlimit";

$_SQL[] = "ALTER TABLE {$_TABLES['gf_settings']} DROP msgauto";




?>