<?php
/**
* glFusion CMS
*
* MySQL Database Schema
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2010 by the following authors:
*    Tony Bibbs       tony AT tonybibbs DOT com
*    Tom Willett      twillett AT users DOT sourceforge DOT net
*    Blaine Lang      blaine AT portalparts DOT com
*    Dirk Haun        dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$_SQL[] = "
CREATE TABLE {$_TABLES['access']} (
  acc_ft_id mediumint(8) NOT NULL default '0',
  acc_grp_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (acc_ft_id,acc_grp_id)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['admin_action']} (
  `id`          mediumint(8) auto_increment,
  `datetime`    datetime  default NULL,
  `module`      varchar(100) NOT NULL DEFAULT 'system',
  `action`      varchar(100) NULL DEFAULT NULL,
  `description` text,
  `user`        varchar(48) default NULL,
  `ip`          varchar(48) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['article_images']} (
  ai_sid varchar(128) NOT NULL,
  ai_img_num tinyint(2) unsigned NOT NULL,
  ai_filename varchar(128) NOT NULL,
  PRIMARY KEY (ai_sid,ai_img_num)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['autotags']} (
  tag varchar( 24 ) NOT NULL DEFAULT '',
  description varchar( 250 ) DEFAULT '',
  is_enabled tinyint( 1 ) NOT NULL DEFAULT '0',
  is_function tinyint( 1 ) NOT NULL DEFAULT '0',
  replacement text,
  PRIMARY KEY ( tag )
) ENGINE=MyISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['autotag_perm']} (
  autotag_id varchar(128) NOT NULL,
  autotag_namespace varchar(128) NOT NULL,
  autotag_name varchar(128) NOT NULL,
  PRIMARY KEY (autotag_id)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['autotag_usage']} (
  autotag_id varchar(128) NOT NULL,
  autotag_allowed tinyint(1) NOT NULL DEFAULT '1',
  usage_namespace varchar(128) NOT NULL,
  usage_operation varchar(128) NOT NULL,
  KEY autotag_id (autotag_id)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['blocks']} (
  bid smallint(5) unsigned NOT NULL auto_increment,
  is_enabled tinyint(1) unsigned NOT NULL DEFAULT '1',
  name varchar(48) NOT NULL default '',
  type varchar(20) NOT NULL default 'normal',
  title varchar(255) default NULL,
  tid varchar(128) NOT NULL default 'All',
  blockorder smallint(5) unsigned NOT NULL default '1',
  content text,
  allow_autotags tinyint(1) unsigned NOT NULL DEFAULT '0',
  rdfurl varchar(255) default NULL,
  rdfupdated datetime NULL default NULL,
  rdf_last_modified varchar(40) default NULL,
  rdf_etag varchar(40) default NULL,
  rdflimit smallint(5) unsigned NOT NULL default '0',
  onleft tinyint(3) unsigned NOT NULL default '1',
  phpblockfn varchar(128) default '',
  help varchar(255) default '',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '3',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  INDEX blocks_is_enabled(is_enabled),
  INDEX blocks_tid(tid),
  INDEX blocks_type(type),
  INDEX blocks_name(name),
  INDEX blocks_onleft(onleft),
  PRIMARY KEY  (bid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['commentcodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['commentedits']} (
  cid int(10) NOT NULL,
  uid mediumint(8) NOT NULL,
  time datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (cid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['commentmodes']} (
  mode varchar(10) NOT NULL default '',
  name varchar(32) default NULL,
  PRIMARY KEY  (mode)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['comments']} (
  cid int(10) unsigned NOT NULL auto_increment,
  type varchar(30) NOT NULL DEFAULT 'article',
  sid varchar(128) NOT NULL default '',
  date datetime default NULL,
  title varchar(128) default NULL,
  comment text,
  pid int(10) unsigned NOT NULL default '0',
  queued TINYINT(3) NOT NULL DEFAULT '0',
  postmode VARCHAR(15) NULL DEFAULT NULL,
  lft mediumint(10) unsigned NOT NULL default '0',
  rht mediumint(10) unsigned NOT NULL default '0',
  indent mediumint(10) unsigned NOT NULL default '0',
  name varchar(32) default NULL,
  uid mediumint(8) NOT NULL default '1',
  ipaddress varchar(45) NOT NULL default '',
  INDEX comments_sid(sid),
  INDEX comments_uid(uid),
  INDEX comments_lft(lft),
  INDEX comments_rht(rht),
  INDEX comments_date(date),
  PRIMARY KEY  (cid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['conf_values']} (
  name varchar(50) default NULL,
  value text,
  type varchar(50) default NULL,
  group_name varchar(50) default NULL,
  default_value text,
  subgroup int(11) default NULL,
  selectionArray int(11) default NULL,
  sort_order int(11) default NULL,
  fieldset int(11) default NULL
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['cookiecodes']} (
  cc_value int(8) unsigned NOT NULL default '0',
  cc_descr varchar(20) NOT NULL default '',
  PRIMARY KEY  (cc_value)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['dateformats']} (
  dfid tinyint(4) NOT NULL default '0',
  format varchar(32) default NULL,
  description varchar(64) default NULL,
  PRIMARY KEY  (dfid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['featurecodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['features']} (
  ft_id mediumint(8) NOT NULL auto_increment,
  ft_name varchar(20) NOT NULL default '',
  ft_descr varchar(255) NOT NULL default '',
  ft_gl_core tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (ft_id),
  KEY ft_name (ft_name)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['frontpagecodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['group_assignments']} (
  ug_main_grp_id mediumint(8) NOT NULL default '0',
  ug_uid mediumint(8) unsigned default NULL,
  ug_grp_id mediumint(8) unsigned default NULL,
  INDEX group_assignments_ug_main_grp_id(ug_main_grp_id),
  INDEX group_assignments_ug_uid(ug_uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['groups']} (
  grp_id mediumint(8) NOT NULL auto_increment,
  grp_name varchar(50) NOT NULL default '',
  grp_descr varchar(255) NOT NULL default '',
  grp_gl_core tinyint(1) unsigned NOT NULL default '0',
  grp_default tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (grp_id),
  UNIQUE grp_name (grp_name)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['maillist']} (
  code int(1) NOT NULL default '0',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['pingservice']} (
  pid smallint(5) unsigned NOT NULL auto_increment,
  name varchar(128) default NULL,
  ping_url varchar(255) default NULL,
  site_url varchar(255) default NULL,
  method varchar(80) default NULL,
  is_enabled tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (pid),
  INDEX pingservice_is_enabled(is_enabled)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['plugins']} (
  pi_name varchar(30) NOT NULL default '',
  pi_version varchar(20) NOT NULL default '',
  pi_gl_version varchar(20) NOT NULL default '',
  pi_enabled tinyint(3) unsigned NOT NULL default '1',
  pi_homepage varchar(128) NOT NULL default '',
  INDEX plugins_enabled(pi_enabled),
  PRIMARY KEY  (pi_name)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['postmodes']} (
  code char(10) NOT NULL default '',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['rating']} (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  type varchar(30) NOT NULL DEFAULT '',
  item_id varchar(128) NOT NULL,
  votes int(11) NOT NULL,
  rating decimal(4,2) NOT NULL,
  KEY id (id)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE {$_TABLES['rating_votes']} (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  type varchar(30) NOT NULL DEFAULT '',
  item_id varchar(128) NOT NULL,
  rating int(11) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) NOT NULL,
  ip_address varchar(45) NOT NULL,
  ratingdate int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY ip_address (ip_address),
  KEY type (type)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['sessions']} (
  sess_id int(10) unsigned NOT NULL default '0',
  browser varchar(255) NOT NULL default '',
  start_time int(10) unsigned NOT NULL default '0',
  remote_ip varchar(45) NOT NULL default '',
  uid mediumint(8) NOT NULL default '1',
  md5_sess_id varchar(128) NOT NULL default '',
  PRIMARY KEY  (md5_sess_id),
  KEY start_time (start_time),
  KEY remote_ip (remote_ip),
  KEY uid (uid)
) ENGINE=MyISAM
";

$_SQL[] = "CREATE TABLE `{$_TABLES['social_share']}` (
  `id` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `display_name` varchar(128) NOT NULL DEFAULT '',
  `icon` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(128) NOT NULL DEFAULT '',
  `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['social_follow_services']} (
  `ssid` int(10) UNSIGNED NOT NULL auto_increment,
  `url` varchar(128) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `icon` varchar(128) NOT NULL,
  `service_name` varchar(128) NOT NULL,
  `display_name` varchar(128) NOT NULL,
  UNIQUE KEY `ssid` (`ssid`),
  UNIQUE KEY `service_name` (`service_name`)
) ENGINE=MyISAM;";

$_SQL[] = "CREATE TABLE {$_TABLES['social_follow_user']} (
  `suid` int(10) NOT NULL AUTO_INCREMENT,
  `ssid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `ss_username` varchar(128) NOT NULL DEFAULT '',
  UNIQUE KEY `suid` (`suid`),
  UNIQUE KEY `ssid` (`ssid`,`uid`)
) ENGINE=MyISAM;";

$_SQL[] = "
CREATE TABLE {$_TABLES['sortcodes']} (
  code char(4) NOT NULL default '0',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['speedlimit']} (
  id int(10) unsigned NOT NULL auto_increment,
  ipaddress varchar(39) NOT NULL default '',
  date int(10) unsigned default NULL,
  type varchar(30) NOT NULL default 'submit',
  PRIMARY KEY (id),
  KEY type_ipaddress (type,ipaddress),
  KEY date (date)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['statuscodes']} (
  code int(1) NOT NULL default '0',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE `{$_TABLES['stories']}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(128) NOT NULL,
  `uid` mediumint(8) NOT NULL DEFAULT '1',
  `draft_flag` tinyint(3) unsigned DEFAULT '0',
  `tid` varchar(20) DEFAULT NULL,
  `alternate_tid` varchar(20) DEFAULT NULL,
  `keywords` text,
  `story_image` varchar(128) DEFAULT NULL,
  `story_video` varchar(255) DEFAULT NULL,
  `sv_autoplay` tinyint(3) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `subtitle` varchar(128) DEFAULT NULL,
  `introtext` mediumtext,
  `bodytext` mediumtext,
  `hits` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rating` float NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `numemails` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `comments` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `comment_expire` datetime DEFAULT NULL,
  `trackbacks` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `related` text,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `show_topic_icon` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `commentcode` tinyint(4) NOT NULL DEFAULT '0',
  `trackbackcode` tinyint(4) NOT NULL DEFAULT '0',
  `statuscode` tinyint(4) NOT NULL DEFAULT '0',
  `expire` datetime DEFAULT NULL,
  `attribution_url` varchar(255) DEFAULT NULL,
  `attribution_name` varchar(255) DEFAULT NULL,
  `attribution_author` varchar(255) DEFAULT NULL,
  `postmode` varchar(10) DEFAULT NULL,
  `advanced_editor_mode` tinyint(1) unsigned DEFAULT '0',
  `frontpage` tinyint(3) unsigned DEFAULT '1',
  `frontpage_date` datetime DEFAULT NULL,
  `owner_id` mediumint(8) NOT NULL DEFAULT '1',
  `group_id` mediumint(8) NOT NULL DEFAULT '2',
  `perm_owner` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `perm_group` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `perm_members` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `perm_anon` tinyint(1) unsigned NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stories_sid` (`sid`),
  KEY `stories_tid` (`tid`),
  KEY `stories_uid` (`uid`),
  KEY `stories_featured` (`featured`),
  KEY `stories_hits` (`hits`),
  KEY `stories_statuscode` (`statuscode`),
  KEY `stories_expire` (`expire`),
  KEY `stories_date` (`date`),
  KEY `stories_frontpage` (`frontpage`),
  KEY `alternate_topic` (`alternate_tid`),
  KEY `frontpage_date` (`frontpage_date`)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['storysubmission']} (
  sid varchar(128) NOT NULL default '',
  uid mediumint(8) NOT NULL default '1',
  tid varchar(128) NOT NULL default 'General',
  title varchar(128) default NULL,
  introtext mediumtext,
  bodytext mediumtext,
  date datetime default NULL,
  postmode varchar(10) NOT NULL default 'html',
  PRIMARY KEY  (sid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['subscriptions']} (
  sub_id int(11) NOT NULL AUTO_INCREMENT,
  type varchar(30) NOT NULL,
  category varchar(128) NOT NULL DEFAULT '',
  category_desc varchar(255) NOT NULL DEFAULT '',
  id varchar(128) NOT NULL DEFAULT '',
  id_desc varchar(255) NOT NULL DEFAULT '',
  uid int(11) NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (`sub_id`),
  KEY uid (uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['syndication']} (
  fid int(10) unsigned NOT NULL auto_increment,
  type varchar(30) NOT NULL default 'article',
  topic varchar(128) NOT NULL default '::all',
  header_tid varchar(128) NOT NULL default 'none',
  format varchar(20) NOT NULL default 'RSS-2.0',
  limits varchar(5) NOT NULL default '10',
  content_length smallint(5) unsigned NOT NULL default '0',
  title varchar(40) NOT NULL default '',
  description text,
  feedlogo varchar(255),
  filename varchar(40) NOT NULL default 'glfusion.rss',
  charset varchar(20) NOT NULL default 'UTF-8',
  language varchar(20) NOT NULL default 'en-gb',
  is_enabled tinyint(1) unsigned NOT NULL default '1',
  updated datetime NOT NULL default '1000-01-01 00:00:00.000000',
  update_info text,
  PRIMARY KEY (fid),
  INDEX syndication_type(type),
  INDEX syndication_topic(topic),
  INDEX syndication_is_enabled(is_enabled),
  INDEX syndication_updated(updated)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['tfa_backup_codes']} (
  uid MEDIUMINT(8) NULL DEFAULT NULL,
  code VARCHAR(128) NULL DEFAULT NULL,
  used TINYINT(4) NULL DEFAULT '0',
  INDEX `uid` (`uid`),
  INDEX `code` (`code`)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['tokens']} (
  token varchar(32) NOT NULL,
  created datetime NOT NULL,
  owner_id mediumint(8) unsigned NOT NULL,
  urlfor varchar(1024) NOT NULL,
  ttl mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY (token)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['topics']} (
  tid varchar(128) NOT NULL default '',
  topic varchar(128) default NULL,
  description text,
  imageurl varchar(255) default NULL,
  sortnum mediumint(8) default NULL,
  limitnews tinyint(3) default NULL,
  is_default tinyint(1) unsigned NOT NULL DEFAULT '0',
  archive_flag tinyint(1) unsigned NOT NULL DEFAULT '0',
  sort_by tinyint(1) unsigned NOT NULL DEFAULT '0',
  sort_dir char(4) NOT NULL DEFAULT 'DESC',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '3',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  PRIMARY KEY  (tid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['trackback']} (
  cid int(10) unsigned NOT NULL auto_increment,
  sid varchar(128) NOT NULL,
  url varchar(255) default NULL,
  title varchar(128) default NULL,
  blog varchar(80) default NULL,
  excerpt text,
  date datetime default NULL,
  type varchar(30) NOT NULL default 'article',
  ipaddress varchar(45) NOT NULL default '',
  PRIMARY KEY (cid),
  INDEX trackback_sid(sid),
  INDEX trackback_type(type),
  INDEX trackback_date(date)
) ENGINE=MyISAM
";
//  INDEX trackback_url(url),

$_SQL[] = "
CREATE TABLE {$_TABLES['trackbackcodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['usercomment']} (
  uid mediumint(8) NOT NULL default '1',
  commentmode varchar(10) NOT NULL default 'nested',
  commentorder varchar(4) NOT NULL default 'ASC',
  commentlimit mediumint(8) unsigned NOT NULL default '100',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['userindex']} (
  uid mediumint(8) NOT NULL default '1',
  tids varchar(255) NOT NULL default '',
  etids text,
  aids varchar(255) NOT NULL default '',
  boxes varchar(255) NOT NULL default '',
  noboxes tinyint(4) NOT NULL default '0',
  maxstories tinyint(4) default NULL,
  INDEX userindex_noboxes(noboxes),
  INDEX userindex_maxstories(maxstories),
  PRIMARY KEY  (uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['userinfo']} (
  uid mediumint(8) NOT NULL default '1',
  about text,
  location varchar(96) NOT NULL default '',
  pgpkey text,
  userspace varchar(255) NOT NULL default '',
  tokens tinyint(3) unsigned NOT NULL default '0',
  totalcomments mediumint(9) NOT NULL default '0',
  lastgranted int(10) unsigned NOT NULL default '0',
  lastlogin VARCHAR(10) NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['userprefs']} (
  uid mediumint(8) NOT NULL default '1',
  noicons tinyint(3) unsigned NOT NULL default '0',
  willing tinyint(3) unsigned NOT NULL default '1',
  dfid tinyint(3) unsigned NOT NULL default '0',
  tzid varchar(125) NOT NULL default '',
  emailstories tinyint(4) NOT NULL default '1',
  emailfromadmin tinyint(1) NOT NULL default '1',
  emailfromuser tinyint(1) NOT NULL default '1',
  showonline tinyint(1) NOT NULL default '1',
  search_result_format varchar( 48 ) NOT NULL DEFAULT 'google',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['users']} (
  uid mediumint(8) NOT NULL auto_increment,
  username varchar(48) NOT NULL default '',
  remoteusername varchar(60) NULL,
  remoteservice varchar(60) NULL,
  fullname varchar(80) default NULL,
  passwd varchar(40) NOT NULL default '',
  email varchar(96) default NULL,
  homepage varchar(255) default NULL,
  sig varchar(160) NOT NULL default '',
  regdate datetime NOT NULL default '1970-01-01 00:00:00',
  photo varchar(128) DEFAULT NULL,
  cookietimeout int(8) unsigned default '28800',
  theme varchar(64) default NULL,
  language varchar(64) default NULL,
  pwrequestid varchar(16) default NULL,
  act_token varchar(32) NOT NULL default '',
  act_time datetime NULL default NULL,
  tfa_enabled tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  tfa_secret varchar(128) NULL DEFAULT NULL,
  status smallint(5) unsigned NOT NULL default '1',
  account_type smallint(5) unsigned NOT NULL default '1',
  num_reminders tinyint(1) NOT NULL default 0,
  remote_ip varchar(45) NOT NULL default '',
  PRIMARY KEY  (uid),
  KEY LOGIN (uid,passwd,username),
  INDEX users_username(username),
  INDEX users_fullname(fullname),
  INDEX users_email(email),
  INDEX users_passwd(passwd),
  INDEX users_pwrequestid(pwrequestid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['vars']} (
  name varchar(20) NOT NULL default '',
  value varchar(128) default NULL,
  PRIMARY KEY  (name)
) ENGINE=MyISAM
";

$_SQL[] = " CREATE TABLE `{$_TABLES['themes']}` (
  `theme` varchar(40) NOT NULL DEFAULT '',
  `logo_type` tinyint(1) NOT NULL DEFAULT -1,
  `display_site_slogan` tinyint(1) NOT NULL DEFAULT -1,
  `logo_file` varchar(40) NOT NULL DEFAULT '',
  `grp_access` int(11) unsigned NOT NULL DEFAULT 2,
  PRIMARY KEY (`theme`)
) ENGINE=MyISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['menu']} (
  id int(11) NOT NULL auto_increment,
  menu_name varchar(64) NOT NULL,
  menu_type tinyint(4) NOT NULL,
  menu_active tinyint(3) NOT NULL,
  group_id mediumint(9) NOT NULL,
  PRIMARY KEY  (id),
  KEY menu_name (menu_name)
) ENGINE=MyISAM;
";

$_SQL[] = "CREATE TABLE {$_TABLES['menu_elements']} (
  id int(11) NOT NULL auto_increment,
  pid int(11) NOT NULL,
  menu_id int(11) NOT NULL default '0',
  element_label varchar(255) NOT NULL,
  element_type int(11) NOT NULL,
  element_subtype varchar(255) NOT NULL,
  element_order int(11) NOT NULL,
  element_active tinyint(4) NOT NULL,
  element_url varchar(255) NOT NULL,
  element_target varchar(255) NOT NULL,
  group_id mediumint(9) NOT NULL,
  PRIMARY KEY( id ),
  INDEX ( pid )
) ENGINE=MyISAM;
";

$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (1,3) ";    // story.edit to Story Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (2,3) ";    // story.moderate to  Story Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (3,3) ";    // story.submit to Story Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (4,3) ";    // story.ping to Story Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (5,9) ";    // user.edit to User admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (5,11) ";   // user.edit to Group Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (6,9) ";    // user.delete to User Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (6,11) ";   // user.delete to Group Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (7,12) ";   // user.mail to Mail Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (8,5) ";    // syndication.edit to Syndication Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (9,8) ";    // webservices.atompub to Webservices User
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (10,4) ";   // block.edit to Block Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (11,6) ";   // topic.edit to Topic Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (13,10) ";  // plugin.edit to Plugin Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (14,11) ";  // group.edit to Group Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (15,11) ";  // group.delete to Group Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (16,4) ";   // block.delete to Block Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (17,1) ";   // stats.view to Root
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (17,13) ";  // stats.view to Logged-in Users
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (18,14) ";  // autotag.admin to Autotag Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (19,14) ";  // autotag.PHP to Autotag Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (20,16) ";  // logo.admin to Logo Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (21,15) ";  // menu.admin to Menu Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (22,17) ";  // social.admin to Social Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (23,19) ";  // comment.moderate to Comment Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (24,19) ";  // comment.submit to Comment Admin
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (25,1) ";   // system.root to Root

$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(56, 1, 'followusblock', 'phpblock', 'Follow Us', 'all', 10, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 0, 'phpblock_social', '', 4, 4, 3, 2, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(1, 0, 'user_block', 'gldefault', 'My Account', 'all', 80, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, '', '', 2, 4, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(2, 0, 'admin_block', 'gldefault', 'Admins Only', 'all', 70, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, '', '', 2, 4, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(3, 1, 'section_block', 'gldefault', 'Topics', 'all', 40, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, '', '', 2, 4, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(4, 1, 'whats_new_block', 'gldefault', 'What''s New', 'all', 30, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, '', '', 2, 4, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(5, 1, 'whosonline_block', 'phpblock', 'Who''s Online', 'all', 50, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, 'phpblock_whosonline', '', 2, 4, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(6, 1, 'older_stories', 'gldefault', 'Older Stories', 'all', 90, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, '', '', 2, 4, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(7, 1, 'navigation_block', 'phpblock', 'Navigation', 'all', 10, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 1, 'phpblock_getMenu(block)', '', 2, 4, 3, 3, 2, 2);";

$_DATA[] = "INSERT INTO {$_TABLES['commentcodes']} (code, name) VALUES (0,'Comments Enabled') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentcodes']} (code, name) VALUES (-1,'Comments Disabled') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentcodes']} (code, name) VALUES (1,'Comments Closed') ";

$_DATA[] = "INSERT INTO {$_TABLES['commentmodes']} (mode, name) VALUES ('flat','Flat') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentmodes']} (mode, name) VALUES ('nested','Nested') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentmodes']} (mode, name) VALUES ('nocomment','No Comments') ";

$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (0,'(don\'t)') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (3600,'1 Hour') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (7200,'2 Hours') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (10800,'3 Hours') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (28800,'8 Hours') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (86400,'1 Day') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (604800,'1 Week') ";
$_DATA[] = "INSERT INTO {$_TABLES['cookiecodes']} (cc_value, cc_descr) VALUES (2678400,'1 Month') ";

$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (0,'','System Default') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (1,'l F d, Y @h:iA','Sunday March 21, 1999 @10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (2,'l F d, Y @H:i','Sunday March 21, 1999 @22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (4,'l F d @H:i','Sunday March 21 @22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (5,'H:i d F Y','22:00 21 March 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (6,'H:i l d F Y','22:00 Sunday 21 March 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (7,'h:iA -- l F d Y','10:00PM -- Sunday March 21 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (8,'D F d, h:iA','Sun March 21, 10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (9,'D F d, H:i','Sun March 21, 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (10,'m-d-y H:i','3-21-99 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (11,'d-m-y H:i','21-3-99 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (12,'m-d-y h:iA','3-21-99 10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (13,'h:iA  F d, Y','10:00PM  March 21st, 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (14,'D M d, \'y h:iA','Sun Mar 21, \'99 10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (15,'Day z, h ish','Day 80, 10 ish') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (16,'y-m-d h:i','99-03-21 10:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (17,'d/m/y H:i','21/03/99 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (18,'D d M h:iA','Sun 21 Mar 10:00PM') ";

$_DATA[] = "INSERT INTO {$_TABLES['featurecodes']} (code, name) VALUES (0,'Not Featured') ";
$_DATA[] = "INSERT INTO {$_TABLES['featurecodes']} (code, name) VALUES (1,'Featured') ";

$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (1,'story.edit','Access to story editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (2,'story.moderate','Ability to moderate pending stories',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (3,'story.submit','May skip the story submission queue',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (4,'story.ping', 'Ability to send pings, pingbacks, or trackbacks for stories', 1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (5,'user.edit','Access to user editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (6,'user.delete','Ability to delete a user',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (7,'user.mail','Ability to send email to members',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (8,'syndication.edit', 'Access to Content Syndication', 1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (9,'webservices.atompub', 'May use Atompub Webservices (if restricted)', 1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (10,'block.edit','Access to block editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (11,'topic.edit','Access to topic editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (13,'plugin.edit','Access to plugin editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (14,'group.edit','Ability to edit groups',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (15,'group.delete','Ability to delete groups',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (16,'block.delete','Ability to delete a block',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (17,'stats.view','Ability to view the stats page',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (18,'autotag.admin','Ability to create / edit autotags',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (19,'autotag.PHP','Ability to create / edit autotags utilizing PHP functions',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (20,'logo.admin','Ability to modify the site logo',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (21,'menu.admin','Ability to create/edit site menus',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (22,'social.admin','Ability to social integrations',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (23,'comment.moderate', 'Ability to moderate comments', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (24,'comment.submit', 'Comments bypass submission queue', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (25,'system.root', 'Allows root access', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (26,'actions.admin', 'Ability to review Admin Actions', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (27,'cache.admin', 'Ability to clear caches', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (28,'config.admin', 'Ability to configure glFusion', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (29,'database.admin', 'Ability to perform Database Administration', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (30,'env.admin', 'Ability to view Environment Check', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (31,'logview.admin', 'Ability to view / clear glFusion logs', 1)";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (32,'upgrade.admin', 'Ability to run Upgrade Check', 1)";

$_DATA[] = "INSERT INTO {$_TABLES['frontpagecodes']} (code, name) VALUES (0,'Show Only in Topic') ";
$_DATA[] = "INSERT INTO {$_TABLES['frontpagecodes']} (code, name) VALUES (1,'Show on Front Page') ";

//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,1,NULL) "; // All Users to Anonymous User
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,1) "; // All users to root group
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (13,2,NULL) "; // Logged-in Users to Admin user
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,12) "; // all users to mail admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,10) "; // all users to plugin admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,9) ";  // all users to user admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,6) ";  // all users to topic admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,4) ";  // all users to block admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,3) ";  // all users to story admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,11) "; // all users to group admin
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,2,NULL) "; // userid 2 (admin) to all users group
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (1,2,NULL) ";  // assign user #2 to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (3,NULL,1) ";  // story admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (4,NULL,1) ";  // block admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (5,NULL,1) ";  // syndication admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (6,NULL,1) ";  // topic admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (8,NULL,1) ";  // webservices user to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (9,NULL,1) ";  // user admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (9,NULL,11) "; // user admin to group admin
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (10,NULL,1) "; // plugin admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (11,NULL,1) "; // group admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (12,NULL,1) "; // mail admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (14,NULL,1) "; // autotag admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (15,NULL,1) "; // menu admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (16,NULL,1) "; // logo admin to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (17,NULL,1) "; // social admin to root
//$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (18,NULL,1) "; // non-logged in to root
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (18,1,NULL) "; // Non-Logged in group to user Anonymous
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (19,NULL,1) "; // comment admin to root

// Traditionally, grp_id 1 = Root, 2 = All Users, 13 = Logged-In Users
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (1,'Root','Has full access to the site',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (2,'All Users','Group that a typical user is added to',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (3,'Story Admin','Has full access to story features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (4,'Block Admin','Has full access to block features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (5,'Syndication Admin', 'Can create and modify web feeds for the site',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (6,'Topic Admin','Has full access to topic features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (7,'Remote Users', 'Users in this group can have authenticated against a remote server.',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (8,'Webservices Users', 'Can use the Webservices API (if restricted)',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (9,'User Admin','Has full access to user features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (10,'Plugin Admin','Has full access to plugin features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (11,'Group Admin','Is a User Admin with access to groups, too',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (12,'Mail Admin','Can use Mail Utility',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (13,'Logged-in Users','All registered members',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (14,'Autotag Admin','Has full access to create and modify autotags',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (15,'Menu Admin','Has full access to create and modify menus',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (16,'Logo Admin','Can modify the site logo',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (17,'Social Admin','Can manage social integrations',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (18,'Non-Logged-in Users','Non Logged-in Users (anonymous users)',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (19,'Comment Admin', 'Can moderate comments', 1)";

$_DATA[] = "INSERT INTO {$_TABLES['maillist']} (code, name) VALUES (0,'Don\'t Email') ";
$_DATA[] = "INSERT INTO {$_TABLES['maillist']} (code, name) VALUES (1,'Email Headlines Each Night') ";

$_DATA[] = "INSERT INTO {$_TABLES['pingservice']} (pid, name, site_url, ping_url, method, is_enabled) VALUES (1, 'Ping-O-Matic', 'http://pingomatic.com/', 'http://rpc.pingomatic.com/', 'weblogUpdates.ping', 1)";

$_DATA[] = "INSERT INTO {$_TABLES['postmodes']} (code, name) VALUES ('plaintext','Plain Old Text') ";
$_DATA[] = "INSERT INTO {$_TABLES['postmodes']} (code, name) VALUES ('html','HTML Formatted') ";

#
# - social sharing sites
#
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('fb', 'facebook', 'Facebook', 'facebook', 'http://www.facebook.com/sharer.php?s=100', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('li', 'linkedin', 'LinkedIn', 'linkedin', 'http://www.linkedin.com', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('lj', 'livejournal', 'Live Journal', 'pencil', 'http://www.livejournal.com', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('mr', 'mail-ru', 'Mail.ru', 'at', 'http://mail-ru.com', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('ok', 'odnoklassniki', 'Odnoklassniki', 'odnoklassniki', 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('pt', 'pinterest', 'Pinterest', 'pinterest-p', 'http://www.pinterest.com', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('rd', 'reddit', 'reddit', 'reddit-alien', 'http://reddit.com/submit?url=%%u&title=%%t', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('tw', 'twitter', 'Twitter', 'twitter', 'http://www.twitter.com', 1);";
$_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('vk', 'vk', 'vk', 'vk', 'http://www.vk.org', 1);";

$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(1, 'https://twitter.com/%%u', 1, 'twitter', 'twitter', 'Twitter');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(2, 'http://facebook.com/%%u', 1, 'facebook', 'facebook', 'Facebook');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(3, 'http://pinterest.com/%%u', 1, 'pinterest-p', 'pinterest', 'Pinterest');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(4, 'http://youtube.com/%%u', 1, 'youtube', 'youtube', 'Youtube');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(6, 'http://linkedin.com/in/%%u', 1, 'linkedin', 'linkedin', 'LinkedIn');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(7, 'http://linkedin.com/company/%%u', 1, 'linkedin-square', 'linkedin-co', 'LinkedIn (Company)');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(8, 'http://github.com/%%u', 1, 'github', 'github', 'GitHub');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(9, 'http://instagram.com/%%u', 1, 'instagram', 'instagram', 'Instagram');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(10, 'http://vimeo.com/%%u', 0, 'vimeo', 'vimeo', 'Vimeo');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(11, 'http://flickr.com/photos/%%u', 1, 'flickr', 'flickr', 'Flickr');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(12, 'http://foursquare.com/%%u', 1, 'foursquare', 'foursquare', 'Foursquare');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(13, 'http://yelp.com/biz/%%u', 1, 'yelp', 'yelp', 'Yelp');";
$_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(14, 'http://dribbble.com/%%u', 1, 'dribbble', 'dribbble', 'Dribbble');";

$_DATA[] = "INSERT INTO {$_TABLES['sortcodes']} (code, name) VALUES ('ASC','Oldest First') ";
$_DATA[] = "INSERT INTO {$_TABLES['sortcodes']} (code, name) VALUES ('DESC','Newest First') ";

$_DATA[] = "INSERT INTO {$_TABLES['statuscodes']} (code, name) VALUES (1,'Refreshing') ";
$_DATA[] = "INSERT INTO {$_TABLES['statuscodes']} (code, name) VALUES (0,'Normal') ";
$_DATA[] = "INSERT INTO {$_TABLES['statuscodes']} (code, name) VALUES (10,'Archive') ";

$_DATA[] = "INSERT INTO {$_TABLES['syndication']} (type, topic, header_tid, format, limits, content_length, title, description, filename, charset, language, is_enabled, updated, update_info) VALUES ('article', '::all', 'all', 'RSS-2.0', 10, 1, 'glFusion Site', 'Fusing Technology with Style', 'glfusion.rss', 'utf-8', 'en-gb', 1, '1000-01-01 00:00:00.000000', NULL)";

$_DATA[] = "INSERT INTO {$_TABLES['topics']} (tid, topic, imageurl, sortnum, sort_by, sort_dir, limitnews, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('General','General News','/assets/topics/topic_news.png',1,0,'DESC',10,6,2,3,2,2,2)";

$_DATA[] = "INSERT INTO {$_TABLES['usercomment']} (uid, commentmode, commentorder, commentlimit) VALUES (1,'nested','ASC',100) ";
$_DATA[] = "INSERT INTO {$_TABLES['usercomment']} (uid, commentmode, commentorder, commentlimit) VALUES (2,'nested','ASC',100) ";

$_DATA[] = "INSERT INTO {$_TABLES['userindex']} (uid, tids, etids, aids, boxes, noboxes, maxstories) VALUES (1,'','-','','',0,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['userindex']} (uid, tids, etids, aids, boxes, noboxes, maxstories) VALUES (2,'','','','',0,NULL) ";

$_DATA[] = "INSERT INTO {$_TABLES['userinfo']} (uid, about, pgpkey, userspace, tokens, totalcomments, lastgranted) VALUES (1,NULL,NULL,'',0,0,0) ";
$_DATA[] = "INSERT INTO {$_TABLES['userinfo']} (uid, about, pgpkey, userspace, tokens, totalcomments, lastgranted) VALUES (2,NULL,NULL,'',0,0,0) ";

$_DATA[] = "INSERT INTO {$_TABLES['userprefs']} (uid, noicons, willing, dfid, tzid, emailstories) VALUES (1,0,0,0,'',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['userprefs']} (uid, noicons, willing, dfid, tzid, emailstories) VALUES (2,0,1,0,'America/Chicago',1) ";

#
# Default data for table 'users'
#

$_DATA[] = "INSERT INTO {$_TABLES['users']} (uid, username, fullname, passwd, email, homepage, sig, regdate, cookietimeout, theme, status) VALUES (1,'Anonymous','Anonymous','',NULL,NULL,'',NOW(),0,NULL,3) ";
$_DATA[] = "INSERT INTO {$_TABLES['users']} (uid, username, fullname, passwd, email, homepage, sig, regdate, cookietimeout, theme, status) VALUES (2,'Admin','glFusion Admin Account','5f4dcc3b5aa765d61d8327deb882cf99','root@localhost','http://www.glfusion.org/','',NOW(),28800,NULL,3) ";

$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('totalhits','0') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('lastemailedstories','') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('last_scheduled_run','') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('last_maint_run','') ";
//$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('spamx.counter','0') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion','2.0.0') ";

$_DATA[] = "INSERT INTO {$_TABLES['trackbackcodes']} (code, name) VALUES (0,'Trackback Enabled') ";
$_DATA[] = "INSERT INTO {$_TABLES['trackbackcodes']} (code, name) VALUES (-1,'Trackback Disabled') ";

/* --------
#
# Default logo / menu data
#

$_DATA[] = "INSERT INTO {$_TABLES['logo']} (id, config_name, config_value) VALUES
(1, 'use_graphic_logo', '0'),
(2, 'display_site_slogan', '1'),
(3, 'logo_name', 'logo1234.png');
";
-------- */
$_DATA[] = "INSERT INTO {$_TABLES['menu']} (`id`, `menu_name`, `menu_type`, `menu_active`, `group_id`) VALUES(1, 'navigation', 1, 1, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu']} (`id`, `menu_name`, `menu_type`, `menu_active`, `group_id`) VALUES(2, 'footer', 2, 1, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu']} (`id`, `menu_name`, `menu_type`, `menu_active`, `group_id`) VALUES(3, 'block', 3, 1, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu']} (`id`, `menu_name`, `menu_type`, `menu_active`, `group_id`) VALUES(4, 'navigation_mobile', 1, 1, 2);";

$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(1, 0, 1, 'Home', 2, '0', 10, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(37, 0, 4, 'Directory', 2, '2', 20, 0, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(4, 0, 1, 'Directory', 2, '2', 20, 0, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(39, 0, 4, 'My Account', 3, '1', 60, 1, '', '', 13);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(6, 0, 1, 'Extras', 3, '5', 30, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(38, 0, 4, 'Extras', 3, '5', 30, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(45, 0, 4, 'Login', 6, '%site_url%/users.php', 70, 1, '%site_url%/users.php', '', 18);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(36, 0, 4, 'Home', 2, '0', 10, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(20, 0, 2, 'Home', 2, '0', 10, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(21, 0, 2, 'Contribute', 2, '1', 20, 1, '', '', 13);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(22, 0, 2, 'Search', 2, '4', 30, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(23, 0, 2, 'Site Stats', 2, '5', 40, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(24, 0, 2, 'Terms of Use', 6, '%site_url%/page.php?page=terms-of-use', 50, 1, '%site_url%/page.php?page=terms-of-use', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(25, 0, 2, 'Privacy Policy', 6, '%site_url%/page.php?page=privacy-policy', 60, 1, '%site_url%/page.php?page=privacy-policy', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(26, 0, 2, 'RSS', 6, '%site_url%/backend/glfusion.rss', 70, 1, '%site_url%/backend/glfusion.rss', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(27, 0, 2, 'Contact Us', 6, '%site_url%/profiles.php?uid=2', 80, 1, '%site_url%/profiles.php?uid=2', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(28, 0, 2, 'Top', 6, '#top', 80, 1, '#top', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(29, 0, 3, 'Home', 2, '0', 10, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(30, 0, 3, 'Downloads', 4, 'filemgmt', 20, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(31, 0, 3, 'Forums', 4, 'forum', 30, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(32, 0, 3, 'Topic Menu', 3, '3', 40, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(33, 0, 3, 'User Menu', 3, '1', 50, 1, '', '', 13);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(34, 0, 3, 'Admin Options', 3, '2', 60, 1, '', '', 1);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(35, 0, 3, 'Logout', 6, '%site_url%/users.php?mode=logout', 70, 1, '%site_url%/users.php?mode=logout', '', 13);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(7, 0, 1, 'Widgets', 1, '', 40, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(10, 7, 1, 'Rotator', 5, 'rotator', 30, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(12, 7, 1, 'Tab Slider', 5, 'tab-slider-example', 50, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(13, 7, 1, 'Spring Menu', 5, 'spring-menu', 60, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(46, 7, 1, 'RSS Ticker', 5, 'ticker', 70, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(16, 0, 1, 'Typography', 5, 'typography', 50, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(40, 0, 4, 'Widgets', 1, '', 40, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(41, 40, 4, 'Rotator', 5, 'rotator', 30, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(42, 40, 4, 'Tab Slider', 5, 'tab-slider-example', 50, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(43, 40, 4, 'Spring Menu', 5, 'spring-menu', 60, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(47, 40, 4, 'RSS Ticker', 5, 'ticker', 70, 1, '', '', 2);";
$_DATA[] = "INSERT INTO {$_TABLES['menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(44, 0, 4, 'Typography', 5, 'typography', 50, 1, '', '', 2);";

#
# Default autotags
#

$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('cipher', 'Text: substitution cipher. Usage example is [wikipedia:ROT13]: [cipher:<i>nopqrstuvwxyzabcdefghijklm</i> <i>text_to_encode</i>]', 1, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('topic', 'Link: to home page to display specified topic: link_text defaults to description. usage: [topic:<i>topic_id</i> {link_text}]', 1, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('glfwiki', 'Link: to the glfusion.or wiki search result for the text specified. usage: [glfwiki:<i>text</i>]', 1, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('lang', 'Text: expands \$LANG global var, eg. [lang:p1 p2] -> value of \$LANGp1[p2] or \$LANG_p1[p2]', 0, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('conf', 'Text: expands \$_CONF global var, eg. [conf:p1] -> value of \$_CONF[p1]', 0, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('user', 'Text: expands \$_USER global var, eg. [user:p1] -> value of \$_USER[p1]', 0, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('wikipedia', 'Link: to the wikipedia search result for the text specified. usage: [wikipedia:<i>text</i>]', 1, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('youtube', 'Embed Youtube videos into content. Usage:[youtube:ID height:PX width:PX align:LEFT/RIGHT pad:PX]', 1, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('vimeo', 'Embed Vimeo videos into content. Usage:[vimeo:ID height:PX width:PX align:LEFT/RIGHT pad:PX responsive:0/1]', 1, 1, NULL)";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('uikitlogin', 'UIKIT Login Widget', 1, 1, NULL);";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('headlines', 'HTML: embeds article headslines. usage: [headlines:<i>topic_name or all</i> display:## meta:0/1 titlelink:0/1 featured:0/1 frontpage:0/1 cols:# template:template_name]', 1, 1, '');";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('newimage', 'HTML: embeds new images in flexible grid. usage: [newimage:<i>#</i> - How many images to display <i>truncate:0/1</i> - 1 = truncate number of images to keep square grid <i>caption:0/1</i> 1 = include title]', 1, 1, '');";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('mgslider', 'HTML: displays Media Gallery album. usage: [mgslider:<i>#album_id#</i> - Album ID for images <i>kenburns:0/1</i> - 1 = Enable Ken Burns effect <i>autoplay:0/1</i> 1 = Autoplay the slides <i>template:_name_</i> - Custom template name if wanted]', 1, 1, '');";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('url', 'HTML: Create a link with description. usage: [url:<i>http://link.com/here</i> - Full URL <i>text</i> - text to be used for the URL link]', 1, 1, '');";
$_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('iteminfo', 'HTML: Returns an info from content. usage: [iteminfo:<i>content_type</i> - Content Type - i.e.; article, mediagallery <i>id:</i> - id of item to get info from <i>what:</i> - what to return, i.e.; url, description, excerpt, date, author, etc.]', 1, 1, '');";
$_DATA[] = "INSERT INTO {$_TABLES['themes']} (theme, logo_type, display_site_slogan) VALUES
    ('_default', 0, 1),
    ('cms', -1, -1)";
?>
