<?php

# SQL Commands for New Install of the Geelog Forum Project Version 2.5
# Last updated Oct 01 /2005
# Blaine Lang  blaine@portalparts.com

#
# Table structure for table `forum_categories`
#
$_SQL[] = "CREATE TABLE {$_TABLES['gf_categories']} (
  cat_order smallint(4) NOT NULL default '0',
  cat_name varchar(255) NOT NULL default '',
  cat_dscp text NOT NULL,
  id int(2) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_forums`
#


$_SQL[] = "CREATE TABLE {$_TABLES['gf_forums']} (
  forum_order int(4) NOT NULL default '0',
  forum_name varchar(255) NOT NULL default '0',
  forum_dscp text NOT NULL,
  forum_id int(4) NOT NULL auto_increment,
  forum_cat int(3) NOT NULL default '0',
  grp_id mediumint(8) NOT NULL default '2',
  is_hidden tinyint(1) NOT NULL default '0', 
  is_readonly tinyint(1) NOT NULL default '0',
  no_newposts tinyint(1) NOT NULL default '0',
  topic_count mediumint(8) NOT NULL default '0',
  post_count mediumint(8) NOT NULL default '0',
  last_post_rec mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (forum_id),
  KEY forum_cat (forum_cat),
  KEY forum_id (forum_id)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_topic`
#

$_SQL[] = "CREATE TABLE {$_TABLES['gf_topic']} (
  id mediumint(8) NOT NULL auto_increment,
  forum int(3) NOT NULL default '0',
  pid mediumint(8) NOT NULL default '0',
  uid mediumint(8) NOT NULL default '0',
  name varchar(50) default NULL,
  date varchar(12) default NULL,
  lastupdated varchar(12) default NULL,
  last_reply_rec mediumint(8) NOT NULL default '0',
  email varchar(50) default NULL,
  website varchar(100) NOT NULL default '',
  subject varchar(100) NOT NULL default '',
  comment longtext,
  postmode varchar(10) NOT NULL default '',
  replies bigint(10) NOT NULL default '0',
  views bigint(10) NOT NULL default '0',
  ip varchar(255) default NULL,
  mood varchar(100) default 'indifferent',
  sticky tinyint(1) NOT NULL default '0',
  moved tinyint(1) NOT NULL default '0',
  locked tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY `forum_idx` (`forum`),
  KEY `idxtopicuid` (`uid`),
  KEY `idxtopicpid` (`pid`),
  KEY `idxdate` (`date`),
  KEY `idxlastdate` (`lastupdated`)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_log`
#

$_SQL[] = "CREATE TABLE {$_TABLES['gf_log']} (
  uid mediumint(8) NOT NULL default '0',
  forum mediumint(3) NOT NULL default '0',
  topic mediumint(3) NOT NULL default '0',
  time varchar(40) NOT NULL default '0',
  KEY uid_forum (uid,forum),
  KEY uid_topic (uid,topic),
  KEY forum (forum)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_moderators`
#

$_SQL[] = "CREATE TABLE {$_TABLES['gf_moderators']} (
  mod_id int(11) NOT NULL auto_increment,
  mod_uid mediumint(8) NOT NULL default '0', 
  mod_groupid mediumint(8) NOT NULL default '0',
  mod_username varchar(30) default NULL,
  mod_forum varchar(30) default NULL,
  mod_delete tinyint(1) NOT NULL default '0',
  mod_ban tinyint(1) NOT NULL default '0',
  mod_edit tinyint(1) NOT NULL default '0',
  mod_move tinyint(1) NOT NULL default '0',
  mod_stick tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (mod_id)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_settings`
#


$_SQL[] = "CREATE TABLE {$_TABLES['gf_settings']} (
  slogan varchar(255) NOT NULL default '',
  registrationrequired tinyint(1) unsigned NOT NULL default '0',
  registerpost tinyint(1) unsigned NOT NULL default '0',
  allowhtml tinyint(1) unsigned NOT NULL default '1',
  glfilter tinyint(1) unsigned NOT NULL default '0',
  use_geshi_formatting tinyint(1) NOT NULL default '1', 
  censor tinyint(1) unsigned NOT NULL default '1',
  showmood tinyint(1) unsigned NOT NULL default '1',
  allowsmilies tinyint(1) unsigned NOT NULL default '1',
  allowavatar tinyint(1) unsigned NOT NULL default '1',
  allow_notify tinyint(1) unsigned NOT NULL default '1',
  post_htmlmode tinyint(1) NOT NULL default '1',
  allow_userdatefmt tinyint(1) NOT NULL default '0',
  showiframe tinyint(1) unsigned NOT NULL default '1',
  autorefresh tinyint(1) NOT NULL default '1', 
  refresh_delay tinyint(1) NOT NULL default '0', 
  xtrausersettings tinyint(1) unsigned NOT NULL default '0',
  viewtopicnumchars int(4) NOT NULL default '20',
  topicsperpage int(4) NOT NULL default '10',
  postsperpage int(4) NOT NULL default '10',
  messagesperpage int(4) NOT NULL default '0', 
  searchesperpage int(4) NOT NULL default '0', 
  popular int(4) NOT NULL default '0',  
  speedlimit int(1) NOT NULL default '60', 
  edit_timewindow int(11) NOT NULL default '3600', 
  use_spamxfilter tinyint(1) NOT NULL default '0', 
  use_smiliesplugin tinyint(1) NOT NULL default '0',
  use_pmplugin tinyint(1) NOT NULL default '0', 
  imgset varchar(30) NOT NULL default '',
  cb_enable tinyint(1) NOT NULL default '0',
  cb_homepage tinyint(1) NOT NULL default '0',
  cb_where tinyint(1) NOT NULL default '0',
  cb_subjectsize tinyint(1) NOT NULL default '0', 
  cb_numposts tinyint(1) NOT NULL default '0',  
  sb_subjectsize tinyint(1) NOT NULL default '0', 
  sb_numposts tinyint(1) NOT NULL default '0', 
  sb_latestposts tinyint(1) NOT NULL default '0', 
  min_comment_len tinyint(1) NOT NULL default '0', 
  min_name_len tinyint(1) NOT NULL default '0', 
  min_subject_len tinyint(1) NOT NULL default '0', 
  html_newline tinyint(1) NOT NULL default '0', 
  level1 int(5) NOT NULL default '1',
  level2 int(5) NOT NULL default '15',
  level3 int(5) NOT NULL default '35',
  level4 int(5) NOT NULL default '70',
  level5 int(5) NOT NULL default '120',
  level1name varchar(40) NOT NULL default 'Newbie',
  level2name varchar(40) NOT NULL default 'Junior',
  level3name varchar(40) NOT NULL default 'Chatty',
  level4name varchar(40) NOT NULL default 'Regular Member',
  level5name varchar(40) NOT NULL default 'Active Member'
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_userprefs`
#

$_SQL[] = "CREATE TABLE {$_TABLES['gf_userprefs']} (
  uid mediumint(8) NOT NULL default '0',
  topicsperpage int(3) NOT NULL default '5',
  postsperpage int(3) NOT NULL default '5',
  popularlimit int(3) NOT NULL default '10',
  messagesperpage int(3) NOT NULL default '20',
  searchlines int(3) NOT NULL default '20',
  viewanonposts tinyint(1) NOT NULL default '1',
  enablenotify tinyint(1) NOT NULL default '1',
  alwaysnotify tinyint(1) NOT NULL default '0',
  membersperpage int(3) NOT NULL default '20',
  showiframe tinyint(1) NOT NULL default '1',
  notify_once tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_watch`
#

$_SQL[] = "CREATE TABLE {$_TABLES['gf_watch']} (
  id mediumint(8) NOT NULL auto_increment,
  forum_id mediumint(8) NOT NULL default '0',
  topic_id mediumint(8) NOT NULL default '0',
  uid mediumint(8) NOT NULL default '0',
  date_added date NOT NULL default '0000-00-00',
  PRIMARY KEY  (id),
  KEY uid (uid),
  KEY forum_id (forum_id),
  KEY topic_id (topic_id)
) TYPE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_banned_ip`
#
$_SQL[] = "CREATE TABLE {$_TABLES['gf_banned_ip']} (
  host_ip varchar(255) default NULL,
  KEY index1 (host_ip)
) TYPE=MyISAM;";


# --------------------------------------------------------

#
# Table structure for table `forum_userinfo`
#
# Creation: Jul 19, 2003 at 01:23 PM
# Last update: Jul 19, 2003 at 01:53 PM
#

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