<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                          |
// | SQL Commands for new install of the Forum plugin.                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

#
# Table structure for table `forum_categories`
#
$_SQL['ff_categories'] = "CREATE TABLE {$_TABLES['ff_categories']} (
  cat_order smallint(4) NOT NULL default '0',
  cat_name varchar(255) NOT NULL default '',
  cat_dscp text NOT NULL,
  id int(2) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ENGINE=MyISAM";
# --------------------------------------------------------

#
# Table structure for table `forum_forums`
#

$_SQL['ff_forums'] = "CREATE TABLE {$_TABLES['ff_forums']} (
  forum_order int(4) NOT NULL default '0',
  forum_name varchar(255) NOT NULL default '0',
  forum_dscp text NOT NULL,
  forum_id int(4) NOT NULL auto_increment,
  forum_cat int(3) NOT NULL default '0',
  grp_id mediumint(8) NOT NULL default '2',
  use_attachment_grpid mediumint(8) NOT NULL default '1',
  is_hidden tinyint(1) NOT NULL default '0',
  is_readonly tinyint(1) NOT NULL default '0',
  no_newposts tinyint(1) NOT NULL default '0',
  topic_count mediumint(8) NOT NULL default '0',
  post_count mediumint(8) NOT NULL default '0',
  last_post_rec mediumint(8) NOT NULL default '0',
  rating_view mediumint(8) NOT NULL default '0',
  rating_post mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (forum_id),
  KEY forum_cat (forum_cat)
) ENGINE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_topic`
#

$_SQL['ff_topic'] = "CREATE TABLE {$_TABLES['ff_topic']} (
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
  attachments int(11) NOT NULL default '0',
  ip varchar(255) default NULL,
  mood varchar(100) default 'indifferent',
  sticky tinyint(1) NOT NULL default '0',
  moved tinyint(1) NOT NULL default '0',
  locked tinyint(1) NOT NULL default '0',
  status int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY `forum_idx` (`forum`),
  KEY `idxtopicuid` (`uid`),
  KEY `idxtopicpid` (`pid`),
  KEY `idxdate` (`date`),
  KEY `idxlastdate` (`lastupdated`)
) ENGINE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_log`
#

$_SQL['ff_log'] = "CREATE TABLE {$_TABLES['ff_log']} (
  uid mediumint(8) NOT NULL default '0',
  forum mediumint(3) NOT NULL default '0',
  topic mediumint(3) NOT NULL default '0',
  time varchar(40) NOT NULL default '0',
  KEY uid_forum (uid,forum),
  KEY uid_topic (uid,topic),
  KEY forum (forum)
) ENGINE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_moderators`
#

$_SQL['ff_moderators'] = "CREATE TABLE {$_TABLES['ff_moderators']} (
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
) ENGINE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_userprefs`
#

$_SQL['ff_userprefs'] = "CREATE TABLE {$_TABLES['ff_userprefs']} (
  uid mediumint(8) NOT NULL default '0',
  topicsperpage int(3) NOT NULL default '5',
  postsperpage int(3) NOT NULL default '5',
  popularlimit int(3) NOT NULL default '10',
  messagesperpage int(3) NOT NULL default '20',
  searchlines int(3) NOT NULL default '20',
  viewanonposts tinyint(1) NOT NULL default '1',
  enablenotify tinyint(1) NOT NULL default '1',
  alwaysnotify tinyint(1) NOT NULL default '0',
  notify_full tinyint(1) NOT NULL default '0',
  membersperpage int(3) NOT NULL default '20',
  showiframe tinyint(1) NOT NULL default '1',
  notify_once tinyint(1) NOT NULL default '0',
  topic_order varchar(10) NOT NULL default 'ASC',
  use_wysiwyg_editor tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;";
# --------------------------------------------------------

#
# Table structure for table `forum_banned_ip`
#
$_SQL['ff_banned_ip'] = "CREATE TABLE {$_TABLES['ff_banned_ip']} (
  host_ip varchar(128) default NULL,
  KEY index1 (host_ip)
) ENGINE=MyISAM;";


# --------------------------------------------------------

#
# Table structure for table `forum_userinfo`
#

$_SQL['ff_userinfo'] = "CREATE TABLE {$_TABLES['ff_userinfo']} (
  `uid` mediumint(8) NOT NULL default '0',
  `rating` mediumint(8) NOT NULL default '0',
  `location` varchar(128) NOT NULL default '',
  `aim` varchar(128) NOT NULL default '',
  `icq` varchar(128) NOT NULL default '',
  `yim` varchar(128) NOT NULL default '',
  `msnm` varchar(128) NOT NULL default '',
  `interests` varchar(255) NOT NULL default '',
  `occupation` varchar(255) NOT NULL default '',
  `signature` mediumtext,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM COMMENT='Forum Extra User Profile Information';";


#
# Table structure for table `forum_bookmarks`
#
$_SQL['ff_bookmarks'] = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_bookmarks']} (
  `uid` mediumint(8) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL default '0',
  KEY `topic_id` (`topic_id`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM ;";

#
# Table structure for table `forum_attachments`
#
$_SQL['ff_attachments'] = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_attachments']} (
  `id` int(11) NOT NULL auto_increment,
  `topic_id` int(11) NOT NULL,
  `repository_id` int(11) default NULL,
  `filename` varchar(255) NOT NULL,
  `tempfile` tinyint(1) NOT NULL default '0',
  `show_inline` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;";

#
# Table structures for table 'forum_rating_assoc'
#

$_SQL['ff_rating_assoc'] = "CREATE TABLE IF NOT EXISTS {$_TABLES['ff_rating_assoc']} (
  `user_id` mediumint(9) NOT NULL,
  `voter_id` mediumint(9) NOT NULL,
  `grade` smallint(6) NOT NULL,
  `topic_id` int(11) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `voter_id` (`voter_id`)
) ENGINE=MyISAM ; ";

#
# Table structures for table 'forum_badges'
#
$_SQL['ff_badges'] = "CREATE TABLE {$_TABLES['ff_badges']} (
  `fb_id` int(11) NOT NULL AUTO_INCREMENT,
  `fb_grp` varchar(20) NOT NULL DEFAULT '',
  `fb_order` int(3) NOT NULL DEFAULT '99',
  `fb_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `fb_gl_grp` MEDIUMINT(8) NOT NULL,
  `fb_type` varchar(10) DEFAULT 'img',
  `fb_data` varchar(255) DEFAULT NULL,
  `fb_dscp` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`fb_id`),
  KEY `grp` (`fb_grp`,`fb_order`)
) ENGINE=MyISAM;";

#
# Table structures for table 'forum_ranks'
#
$_SQL['ff_ranks'] = "CREATE TABLE {$_TABLES['ff_ranks']} (
  `posts` int(11) unsigned NOT NULL DEFAULT '0',
  `dscp` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`posts`)
) ENGINE=MyISAM;";

#
# Table structures for table 'forum_likes_assoc'
#
$_SQL['ff_likes_assoc'] = "CREATE TABLE `{$_TABLES['ff_likes_assoc']}` (
  `poster_id` mediumint(9) NOT NULL,
  `voter_id` mediumint(9) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `username` varchar(40),
  `like_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`poster_id`,`voter_id`,`topic_id`),
  KEY `voter_id` (`voter_id`),
  KEY `poster_id` (`poster_id`)
) ENGINE=MyISAM;";

$_SQL['d1'] = "INSERT INTO {$_TABLES['ff_categories']} (`cat_order`, `cat_name`, `cat_dscp`, `id`) VALUES (0,'General','General News and Discussions',1);";
$_SQL['d2'] = "INSERT INTO {$_TABLES['ff_forums']} (`forum_order`, `forum_name`, `forum_dscp`, `forum_id`, `forum_cat`, `grp_id`, `use_attachment_grpid`, `is_hidden`, `is_readonly`, `no_newposts`, `topic_count`, `post_count`, `last_post_rec`) VALUES (0,'News and Announcements','Site News and Special Announcements',1,1,2,1,0,1,0,1,1,1);";
$_SQL['d3'] = "INSERT INTO {$_TABLES['ff_moderators']} (`mod_id`, `mod_uid`, `mod_groupid`, `mod_username`, `mod_forum`, `mod_delete`, `mod_ban`, `mod_edit`, `mod_move`, `mod_stick`) VALUES (1,2,0,'Admin','1',1,1,1,1,1);";
$_SQL['d4'] = "INSERT INTO {$_TABLES['ff_topic']} (`id`, `forum`, `pid`, `uid`, `name`, `date`, `lastupdated`, `last_reply_rec`, `email`, `website`, `subject`, `comment`, `postmode`, `replies`, `views`, `attachments`,`ip`, `mood`, `sticky`, `moved`, `locked`) VALUES (1,1,0,2,'Admin','1211775931','1211775931',0,NULL,'','Welcome to glFusion','Welcome to glFusion!  We hope you enjoy using your new glFusion site.\r\n\r\nglFusion is designed to provide you with features, functionality, and style, all in an easy to use package.\r\n\r\nYou can visit the [url=http://www.glfusion.org/wiki/]glFusion Wiki[/url] for the latest information on features and how to use them.\r\n\r\nThanks and enjoy!\r\nThe glFusion Team\r\n','text',0,1,0,'127.0.0.1','',0,0,0);";
$_SQL['d5'] = "INSERT INTO {$_TABLES['ff_badges']} VALUES
    (0,'1_site',20,1,'13','img','forum_user.png','Forum User'),
    (0,'1_site',10,1,'1','img','siteadmin_badge.png','Site Admin');";
$_SQL['d6'] = "INSERT INTO {$_TABLES['ff_ranks']} VALUES
    (1, 'Newbie'), (15, 'Junior'), (35, 'Chatty'), (70, 'Regular Member'), (120, 'Active Member');";

?>
