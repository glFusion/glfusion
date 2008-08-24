<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | mysql_tableanddata.php                                                   |
// |                                                                          |
// | glFusion installation SQL                                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - blaine AT portalparts DOT com                |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

$_SQL[] = "
CREATE TABLE {$_TABLES['access']} (
  acc_ft_id mediumint(8) NOT NULL default '0',
  acc_grp_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (acc_ft_id,acc_grp_id)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['article_images']} (
  ai_sid varchar(40) NOT NULL,
  ai_img_num tinyint(2) unsigned NOT NULL,
  ai_filename varchar(128) NOT NULL,
  PRIMARY KEY (ai_sid,ai_img_num)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['blocks']} (
  bid smallint(5) unsigned NOT NULL auto_increment,
  is_enabled tinyint(1) unsigned NOT NULL DEFAULT '1',
  name varchar(48) NOT NULL default '',
  type varchar(20) NOT NULL default 'normal',
  title varchar(48) default NULL,
  tid varchar(20) NOT NULL default 'All',
  blockorder smallint(5) unsigned NOT NULL default '1',
  content text,
  allow_autotags tinyint(1) unsigned NOT NULL DEFAULT '0',
  rdfurl varchar(255) default NULL,
  rdfupdated datetime NOT NULL default '0000-00-00 00:00:00',
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
  INDEX blocks_bid(bid),
  INDEX blocks_is_enabled(is_enabled),
  INDEX blocks_tid(tid),
  INDEX blocks_type(type),
  INDEX blocks_name(name),
  INDEX blocks_onleft(onleft),
  PRIMARY KEY  (bid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['commentcodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['commentedits']} (
  cid int(10) NOT NULL,
  uid mediumint(8) NOT NULL,
  time datetime NOT NULL,
  PRIMARY KEY (cid)
) TYPE=MYISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['commentmodes']} (
  mode varchar(10) NOT NULL default '',
  name varchar(32) default NULL,
  PRIMARY KEY  (mode)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['comments']} (
  cid int(10) unsigned NOT NULL auto_increment,
  type varchar(30) NOT NULL DEFAULT 'article',
  sid varchar(40) NOT NULL default '',
  date datetime default NULL,
  title varchar(128) default NULL,
  comment text,
  score tinyint(4) NOT NULL default '0',
  reason tinyint(4) NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  lft mediumint(10) unsigned NOT NULL default '0',
  rht mediumint(10) unsigned NOT NULL default '0',
  indent mediumint(10) unsigned NOT NULL default '0',
  name varchar(32) default NULL,
  uid mediumint(8) NOT NULL default '1',
  ipaddress varchar(15) NOT NULL default '',
  INDEX comments_sid(sid),
  INDEX comments_uid(uid),
  INDEX comments_lft(lft),
  INDEX comments_rht(rht),
  INDEX comments_date(date),
  PRIMARY KEY  (cid)
) TYPE=MyISAM
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
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['cookiecodes']} (
  cc_value int(8) unsigned NOT NULL default '0',
  cc_descr varchar(20) NOT NULL default '',
  PRIMARY KEY  (cc_value)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['dateformats']} (
  dfid tinyint(4) NOT NULL default '0',
  format varchar(32) default NULL,
  description varchar(64) default NULL,
  PRIMARY KEY  (dfid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['featurecodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['features']} (
  ft_id mediumint(8) NOT NULL auto_increment,
  ft_name varchar(20) NOT NULL default '',
  ft_descr varchar(255) NOT NULL default '',
  ft_gl_core tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (ft_id),
  KEY ft_name (ft_name)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['frontpagecodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['group_assignments']} (
  ug_main_grp_id mediumint(8) NOT NULL default '0',
  ug_uid mediumint(8) unsigned default NULL,
  ug_grp_id mediumint(8) unsigned default NULL,
  INDEX group_assignments_ug_main_grp_id(ug_main_grp_id),
  INDEX group_assignments_ug_uid(ug_uid),
  KEY ug_main_grp_id (ug_main_grp_id)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['groups']} (
  grp_id mediumint(8) NOT NULL auto_increment,
  grp_name varchar(50) NOT NULL default '',
  grp_descr varchar(255) NOT NULL default '',
  grp_gl_core tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (grp_id),
  UNIQUE grp_name (grp_name)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['maillist']} (
  code int(1) NOT NULL default '0',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
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
) TYPE=MyISAM
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
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['postmodes']} (
  code char(10) NOT NULL default '',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['sessions']} (
  sess_id int(10) unsigned NOT NULL default '0',
  start_time int(10) unsigned NOT NULL default '0',
  remote_ip varchar(15) NOT NULL default '',
  uid mediumint(8) NOT NULL default '1',
  md5_sess_id varchar(128) default NULL,
  PRIMARY KEY  (sess_id),
  KEY sess_id (sess_id),
  KEY start_time (start_time),
  KEY remote_ip (remote_ip)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['sortcodes']} (
  code char(4) NOT NULL default '0',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['speedlimit']} (
  id int(10) unsigned NOT NULL auto_increment,
  ipaddress varchar(15) NOT NULL default '',
  date int(10) unsigned default NULL,
  type varchar(30) NOT NULL default 'submit',
  PRIMARY KEY (id),
  KEY type_ipaddress (type,ipaddress),
  KEY date (date)
) TYPE = MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['statuscodes']} (
  code int(1) NOT NULL default '0',
  name char(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['stories']} (
  sid varchar(40) NOT NULL default '',
  uid mediumint(8) NOT NULL default '1',
  draft_flag tinyint(3) unsigned default '0',
  tid varchar(20) NOT NULL default 'General',
  date datetime default NULL,
  title varchar(128) default NULL,
  introtext text,
  bodytext text,
  hits mediumint(8) unsigned NOT NULL default '0',
  numemails mediumint(8) unsigned NOT NULL default '0',
  comments mediumint(8) unsigned NOT NULL default '0',
  comment_expire datetime NOT NULL default '0000-00-00 00:00:00',
  trackbacks mediumint(8) unsigned NOT NULL default '0',
  related text,
  featured tinyint(3) unsigned NOT NULL default '0',
  show_topic_icon tinyint(1) unsigned NOT NULL default '1',
  commentcode tinyint(4) NOT NULL default '0',
  trackbackcode tinyint(4) NOT NULL default '0',
  statuscode tinyint(4) NOT NULL default '0',
  expire DATETIME NOT NULL default '0000-00-00 00:00:00',
  postmode varchar(10) NOT NULL default 'html',
  advanced_editor_mode tinyint(1) unsigned default '0',
  frontpage tinyint(3) unsigned default '1',
  in_transit tinyint(1) unsigned default '0',
  owner_id mediumint(8) NOT NULL default '1',
  group_id mediumint(8) NOT NULL default '2',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '3',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  INDEX stories_sid(sid),
  INDEX stories_tid(tid),
  INDEX stories_uid(uid),
  INDEX stories_featured(featured),
  INDEX stories_hits(hits),
  INDEX stories_statuscode(statuscode),
  INDEX stories_expire(expire),
  INDEX stories_date(date),
  INDEX stories_frontpage(frontpage),
  INDEX stories_in_transit(in_transit),
  PRIMARY KEY  (sid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['storysubmission']} (
  sid varchar(20) NOT NULL default '',
  uid mediumint(8) NOT NULL default '1',
  tid varchar(20) NOT NULL default 'General',
  title varchar(128) default NULL,
  introtext text,
  bodytext text,
  date datetime default NULL,
  postmode varchar(10) NOT NULL default 'html',
  PRIMARY KEY  (sid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['syndication']} (
  fid int(10) unsigned NOT NULL auto_increment,
  type varchar(30) NOT NULL default 'article',
  topic varchar(48) NOT NULL default '::all',
  header_tid varchar(48) NOT NULL default 'none',
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
  updated datetime NOT NULL default '0000-00-00 00:00:00',
  update_info text,
  PRIMARY KEY (fid),
  INDEX syndication_type(type),
  INDEX syndication_topic(topic),
  INDEX syndication_is_enabled(is_enabled),
  INDEX syndication_updated(updated)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['tokens']} (
  token varchar(32) NOT NULL,
  created datetime NOT NULL,
  owner_id mediumint(8) unsigned NOT NULL,
  urlfor varchar(255) NOT NULL,
  ttl mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY (token)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['topics']} (
  tid varchar(20) NOT NULL default '',
  topic varchar(48) default NULL,
  imageurl varchar(255) default NULL,
  sortnum tinyint(3) default NULL,
  limitnews tinyint(3) default NULL,
  is_default tinyint(1) unsigned NOT NULL DEFAULT '0',
  archive_flag tinyint(1) unsigned NOT NULL DEFAULT '0',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '3',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  PRIMARY KEY  (tid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['trackback']} (
  cid int(10) unsigned NOT NULL auto_increment,
  sid varchar(40) NOT NULL,
  url varchar(255) default NULL,
  title varchar(128) default NULL,
  blog varchar(80) default NULL,
  excerpt text,
  date datetime default NULL,
  type varchar(30) NOT NULL default 'article',
  ipaddress varchar(15) NOT NULL default '',
  PRIMARY KEY (cid),
  INDEX trackback_sid(sid),
  INDEX trackback_url(url),
  INDEX trackback_type(type),
  INDEX trackback_date(date)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['trackbackcodes']} (
  code tinyint(4) NOT NULL default '0',
  name varchar(32) default NULL,
  PRIMARY KEY  (code)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['usercomment']} (
  uid mediumint(8) NOT NULL default '1',
  commentmode varchar(10) NOT NULL default 'threaded',
  commentorder varchar(4) NOT NULL default 'ASC',
  commentlimit mediumint(8) unsigned NOT NULL default '100',
  PRIMARY KEY  (uid)
) TYPE=MyISAM
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
  INDEX userindex_uid(uid),
  INDEX userindex_noboxes(noboxes),
  INDEX userindex_maxstories(maxstories),
  PRIMARY KEY  (uid)
) TYPE=MyISAM
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
) TYPE=MyISAM
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
  PRIMARY KEY  (uid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['users']} (
  uid mediumint(8) NOT NULL auto_increment,
  username varchar(16) NOT NULL default '',
  remoteusername varchar(60) NULL,
  remoteservice varchar(60) NULL,
  fullname varchar(80) default NULL,
  passwd varchar(32) NOT NULL default '',
  email varchar(96) default NULL,
  homepage varchar(96) default NULL,
  sig varchar(160) NOT NULL default '',
  regdate datetime NOT NULL default '0000-00-00 00:00:00',
  photo varchar(128) DEFAULT NULL,
  cookietimeout int(8) unsigned default '28800',
  theme varchar(64) default NULL,
  language varchar(64) default NULL,
  pwrequestid varchar(16) default NULL,
  status smallint(5) unsigned NOT NULL default '1',
  num_reminders tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (uid),
  KEY LOGIN (uid,passwd,username),
  INDEX users_username(username),
  INDEX users_fullname(fullname),
  INDEX users_email(email),
  INDEX users_passwd(passwd),
  INDEX users_pwrequestid(pwrequestid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['vars']} (
  name varchar(20) NOT NULL default '',
  value varchar(128) default NULL,
  PRIMARY KEY  (name)
) TYPE=MyISAM
";

// Tables used by the bundled plugins

// Calendar plugin
$_SQL[] = "
CREATE TABLE {$_TABLES['events']} (
  eid varchar(20) NOT NULL default '',
  title varchar(128) default NULL,
  description text,
  postmode varchar(10) NOT NULL default 'plaintext',
  datestart date default NULL,
  dateend date default NULL,
  url varchar(255) default NULL,
  hits mediumint(8) unsigned NOT NULL default '0',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '3',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  address1 varchar(40) default NULL,
  address2 varchar(40) default NULL,
  city varchar(60) default NULL,
  state varchar(40) default NULL,
  zipcode varchar(5) default NULL,
  allday tinyint(1) NOT NULL default '0',
  event_type varchar(40) NOT NULL default '',
  location varchar(128) default NULL,
  timestart time default NULL,
  timeend time default NULL,
  INDEX events_eid(eid),
  INDEX events_event_type(event_type),
  INDEX events_datestart(datestart),
  INDEX events_dateend(dateend),
  PRIMARY KEY  (eid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['eventsubmission']} (
  eid varchar(20) NOT NULL default '',
  title varchar(128) default NULL,
  description text,
  location varchar(128) default NULL,
  datestart date default NULL,
  dateend date default NULL,
  url varchar(255) default NULL,
  allday tinyint(1) NOT NULL default '0',
  zipcode varchar(5) default NULL,
  state varchar(40) default NULL,
  city varchar(60) default NULL,
  address2 varchar(40) default NULL,
  address1 varchar(40) default NULL,
  event_type varchar(40) NOT NULL default '',
  timestart time default NULL,
  timeend time default NULL,
  PRIMARY KEY  (eid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['personal_events']} (
  eid varchar(20) NOT NULL default '',
  title varchar(128) default NULL,
  event_type varchar(40) NOT NULL default '',
  datestart date default NULL,
  dateend date default NULL,
  address1 varchar(40) default NULL,
  address2 varchar(40) default NULL,
  city varchar(60) default NULL,
  state varchar(40) default NULL,
  zipcode varchar(5) default NULL,
  allday tinyint(1) NOT NULL default '0',
  url varchar(255) default NULL,
  description text,
  postmode varchar(10) NOT NULL default 'plaintext',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '3',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  uid mediumint(8) NOT NULL default '0',
  location varchar(128) default NULL,
  timestart time default NULL,
  timeend time default NULL,
  PRIMARY KEY  (eid,uid)
) TYPE=MyISAM
";

// Links plugin
$_SQL[] = "
CREATE TABLE {$_TABLES['linkcategories']} (
  cid varchar(32) NOT NULL,
  pid varchar(32) NOT NULL,
  category varchar(32) NOT NULL,
  description text DEFAULT NULL,
  tid varchar(20) DEFAULT NULL,
  created datetime DEFAULT NULL,
  modified datetime DEFAULT NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  PRIMARY KEY (cid),
  KEY links_pid (pid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['links']} (
  lid varchar(40) NOT NULL default '',
  cid varchar(32) default NULL,
  url varchar(255) default NULL,
  description text,
  title varchar(96) default NULL,
  hits int(11) NOT NULL default '0',
  date datetime default NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  INDEX links_category(cid),
  INDEX links_date(date),
  PRIMARY KEY (lid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['linksubmission']} (
  lid varchar(40) NOT NULL default '',
  cid varchar(32) default NULL,
  url varchar(255) default NULL,
  description text,
  title varchar(96) default NULL,
  hits int(11) default NULL,
  date datetime default NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY (lid)
) TYPE=MyISAM
";

// Polls plugin
$_SQL[] = "
CREATE TABLE {$_TABLES['pollanswers']} (
  pid varchar(20) NOT NULL default '',
  qid mediumint(9) NOT NULL default '0',
  aid tinyint(3) unsigned NOT NULL default '0',
  answer varchar(255) default NULL,
  votes mediumint(8) unsigned default NULL,
  remark varchar(255) NULL,
  PRIMARY KEY (pid, qid, aid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['pollquestions']} (
  qid mediumint(9) NOT NULL DEFAULT '0',
  pid varchar(20) NOT NULL,
  question varchar(255) NOT NULL,
  PRIMARY KEY (qid, pid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['polltopics']} (
  pid varchar(20) NOT NULL,
  topic varchar(255) default NULL,
  voters mediumint(8) unsigned default NULL,
  questions int(11) NOT NULL default '0',
  date datetime default NULL,
  display tinyint(4) NOT NULL default '0',
  is_open tinyint(1) NOT NULL default '1',
  hideresults tinyint(1) NOT NULL default '0',
  commentcode tinyint(4) NOT NULL default '0',
  statuscode tinyint(4) NOT NULL default '0',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  INDEX pollquestions_pid(pid),
  INDEX pollquestions_date(date),
  INDEX pollquestions_display(display),
  INDEX pollquestions_commentcode(commentcode),
  INDEX pollquestions_statuscode(statuscode),
  PRIMARY KEY  (pid)
) TYPE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['pollvoters']} (
  id int(10) unsigned NOT NULL auto_increment,
  pid varchar(20) NOT NULL default '',
  ipaddress varchar(15) NOT NULL default '',
  date int(10) unsigned default NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM
";

// Spam-X plugin
$_SQL[] = "
CREATE TABLE {$_TABLES['spamx']} (
  name varchar(20) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  INDEX spamx_name(name)
) TYPE=MyISAM
";

// Static Pages plugin
$_SQL[] = "
CREATE TABLE {$_TABLES['staticpage']} (
  sp_id varchar(40) NOT NULL default '',
  sp_uid mediumint(8) NOT NULL default '1',
  sp_title varchar(128) NOT NULL default '',
  sp_content text NOT NULL,
  sp_hits mediumint(8) unsigned NOT NULL default '0',
  sp_date datetime NOT NULL default '0000-00-00 00:00:00',
  sp_format varchar(20) NOT NULL default '',
  sp_onmenu tinyint(1) unsigned NOT NULL default '0',
  sp_label varchar(64) default NULL,
  commentcode tinyint(4) NOT NULL default '0',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  sp_centerblock tinyint(1) unsigned NOT NULL default '0',
  sp_help varchar(255) default '',
  sp_tid varchar(20) NOT NULL default 'none',
  sp_where tinyint(1) unsigned NOT NULL default '1',
  sp_php tinyint(1) unsigned NOT NULL default '0',
  sp_nf tinyint(1) unsigned default '0',
  sp_inblock tinyint(1) unsigned default '1',
  postmode varchar(16) NOT NULL default 'html',
  PRIMARY KEY  (sp_id),
  KEY staticpage_sp_uid (sp_uid),
  KEY staticpage_sp_date (sp_date),
  KEY staticpage_sp_onmenu (sp_onmenu),
  KEY staticpage_sp_centerblock (sp_centerblock),
  KEY staticpage_sp_tid (sp_tid),
  KEY staticpage_sp_where (sp_where)
) TYPE=MyISAM
";


$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (1,3) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (2,3) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (3,5) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (4,5) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (5,9) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (5,11) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (6,9) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (6,11) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES(7,12) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (8,7) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (9,7) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (10,4) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (11,6) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (12,8) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (13,10) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (14,11) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (15,11) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (16,4) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (17,14) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (18,14) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (23,15) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (24,3) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (25,17) ";
$_DATA[] = "INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (26,18) ";

$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (1,1,'user_block','gldefault','User Functions','all',2,'','','0000-00-00 00:00:00',1,'',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (2,1,'admin_block','gldefault','Admins Only','all',1,'','','0000-00-00 00:00:00',1,'',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (3,1,'section_block','gldefault','Topics','all',0,'','','0000-00-00 00:00:00',1,'',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (4,1,'polls_block','phpblock','Poll','all',3,'','','0000-00-00 00:00:00',0,'phpblock_polls',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (5,1,'events_block','phpblock','Events','all',4,'','','0000-00-00 00:00:00',1,'phpblock_calendar',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (6,1,'whats_new_block','gldefault','What\'s New','all',3,'','','0000-00-00 00:00:00',0,'',4,2,3,3,2,2) ";

$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (8,1,'whosonline_block','phpblock','Who\'s Online','all',0,'','','0000-00-00 00:00:00',0,'phpblock_whosonline',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (9,1,'older_stories','gldefault','Older Stories','all',5,'','','0000-00-00 00:00:00',1,'',4,2,3,3,2,2) ";
$_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (10,1,'blogroll_block','phpblock','Blog Roll','all',2,'','','0000-00-00 00:00:00',0,'phpblock_blogroll',4,2,3,3,2,2) ";

$_DATA[] = "INSERT INTO {$_TABLES['commentcodes']} (code, name) VALUES (0,'Comments Enabled') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentcodes']} (code, name) VALUES (-1,'Comments Disabled') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentcodes']} (code, name) VALUES (1,'Comments Closed') ";

$_DATA[] = "INSERT INTO {$_TABLES['commentmodes']} (mode, name) VALUES ('flat','Flat') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentmodes']} (mode, name) VALUES ('nested','Nested') ";
$_DATA[] = "INSERT INTO {$_TABLES['commentmodes']} (mode, name) VALUES ('threaded','Threaded') ";
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
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (1,'%A %B %d, %Y @%I:%M%p','Sunday March 21, 1999 @10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (2,'%A %b %d, %Y @%H:%M','Sunday March 21, 1999 @22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (4,'%A %b %d @%H:%M','Sunday March 21 @22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (5,'%H:%M %d %B %Y','22:00 21 March 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (6,'%H:%M %A %d %B %Y','22:00 Sunday 21 March 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (7,'%I:%M%p - %A %B %d %Y','10:00PM -- Sunday March 21 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (8,'%a %B %d, %I:%M%p','Sun March 21, 10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (9,'%a %B %d, %H:%M','Sun March 21, 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (10,'%m-%d-%y %H:%M','3-21-99 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (11,'%d-%m-%y %H:%M','21-3-99 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (12,'%m-%d-%y %I:%M%p','3-21-99 10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (13,'%I:%M%p  %B %D, %Y','10:00PM  March 21st, 1999') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (14,'%a %b %d, \'%y %I:%M%p','Sun Mar 21, \'99 10:00PM') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (15,'Day %j, %I ish','Day 80, 10 ish') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (16,'%y-%m-%d %I:%M','99-03-21 10:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (17,'%d/%m/%y %H:%M','21/03/99 22:00') ";
$_DATA[] = "INSERT INTO {$_TABLES['dateformats']} (dfid, format, description) VALUES (18,'%a %d %b %I:%M%p','Sun 21 Mar 10:00PM') ";

$_DATA[] = "INSERT INTO {$_TABLES['eventsubmission']} (eid, title, description, location, datestart, dateend, url, allday, zipcode, state, city, address2, address1, event_type, timestart, timeend) VALUES ('2006051410130162','glFusion installed','Today, you successfully installed this glFusion site.','Your webserver',CURDATE(),CURDATE(),'http://www.glfusion.org/',1,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL) ";

$_DATA[] = "INSERT INTO {$_TABLES['featurecodes']} (code, name) VALUES (0,'Not Featured') ";
$_DATA[] = "INSERT INTO {$_TABLES['featurecodes']} (code, name) VALUES (1,'Featured') ";

$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (1,'story.edit','Access to story editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (2,'story.moderate','Ability to moderate pending stories',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (3,'links.moderate','Ability to moderate pending links',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (4,'links.edit','Access to links editor',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (5,'user.edit','Access to user editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (6,'user.delete','Ability to delete a user',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (7,'user.mail','Ability to send email to members',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (8,'calendar.moderate','Ability to moderate pending events',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (9,'calendar.edit','Access to event editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (10,'block.edit','Access to block editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (11,'topic.edit','Access to topic editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (12,'polls.edit','Access to polls editor',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (13,'plugin.edit','Access to plugin editor',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (14,'group.edit','Ability to edit groups',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (15,'group.delete','Ability to delete groups',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (16,'block.delete','Ability to delete a block',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (17,'staticpages.edit','Ability to edit a static page',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (18,'staticpages.delete','Ability to delete static pages',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (19,'story.submit','May skip the story submission queue',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (20,'links.submit','May skip the links submission queue',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (21,'calendar.submit','May skip the event submission queue',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (22,'staticpages.PHP','Ability use PHP in static pages',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (23,'spamx.admin', 'Full access to Spam-X plugin', 0) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (24,'story.ping', 'Ability to send pings, pingbacks, or trackbacks for stories', 1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (25,'syndication.edit', 'Access to Content Syndication', 1) ";
$_DATA[] = "INSERT INTO {$_TABLES['features']} (ft_id, ft_name, ft_descr, ft_gl_core) VALUES (26,'webservices.atompub', 'May use Atompub Webservices (if restricted)', 1) ";

$_DATA[] = "INSERT INTO {$_TABLES['frontpagecodes']} (code, name) VALUES (0,'Show Only in Topic') ";
$_DATA[] = "INSERT INTO {$_TABLES['frontpagecodes']} (code, name) VALUES (1,'Show on Front Page') ";

$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,1,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (3,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (4,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (5,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (6,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (7,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (8,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (9,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (10,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (11,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (13,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (12,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (11,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,12) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,10) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,9) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,8) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,7) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,6) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,5) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,4) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,3) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (12,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (9,NULL,11) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,NULL,11) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (10,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (9,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (8,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (7,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (6,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (5,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (4,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (3,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (1,2,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (14,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (15,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (17,NULL,1) ";
$_DATA[] = "INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (18,NULL,1) ";

$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (1,'Root','Has full access to the site',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (2,'All Users','Group that a typical user is added to',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (3,'Story Admin','Has full access to story features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (4,'Block Admin','Has full access to block features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (5,'Links Admin','Has full access to links features',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (6,'Topic Admin','Has full access to topic features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (7,'Calendar Admin','Has full access to calendar features',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (8,'Polls Admin','Has full access to polls features',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (9,'User Admin','Has full access to user features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (10,'Plugin Admin','Has full access to plugin features',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (11,'Group Admin','Is a User Admin with access to groups, too',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (12,'Mail Admin','Can use Mail Utility',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (13,'Logged-in Users','All registered members',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (14,'Static Page Admin','Can administer static pages',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (15,'spamx Admin', 'Users in this group can administer the Spam-X plugin',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (16,'Remote Users', 'Users in this group can have authenticated against a remote server.',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (17,'Syndication Admin', 'Can create and modify web feeds for the site',1) ";
$_DATA[] = "INSERT INTO {$_TABLES['groups']} (grp_id, grp_name, grp_descr, grp_gl_core) VALUES (18,'Webservices Users', 'Can use the Webservices API (if restricted)',0) ";

$_DATA[] = "INSERT INTO {$_TABLES['linkcategories']} (cid, pid, category, description, tid, created, modified, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('site', 'root', 'Root', 'Website root', NULL, NOW(), NOW(), 2, 5, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['linkcategories']} (cid, pid, category, description, tid, created, modified, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('blog-roll', 'site', 'Blog Roll', 'glFusion Related Sites', NULL, NOW(), NOW(), 2, 5, 3, 3, 2, 2);";
$_DATA[] = "INSERT INTO {$_TABLES['links']} (lid, cid, url, description, title, hits, date, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('gllabs.org', 'blog-roll', 'http://www.glfusion.org/', 'Visit gl Labs - A site dedicated to enhancing glFusion.', 'gl Labs - Enhancing glFusion', 123, NOW(), 1, 5, 3, 3, 2, 2);";

$_DATA[] = "INSERT INTO {$_TABLES['maillist']} (code, name) VALUES (0,'Don\'t Email') ";
$_DATA[] = "INSERT INTO {$_TABLES['maillist']} (code, name) VALUES (1,'Email Headlines Each Night') ";

$_DATA[] = "INSERT INTO {$_TABLES['pingservice']} (pid, name, site_url, ping_url, method, is_enabled) VALUES (1, 'Ping-O-Matic', 'http://pingomatic.com/', 'http://rpc.pingomatic.com/', 'weblogUpdates.ping', 1)";

$_DATA[] = "INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_enabled, pi_homepage) VALUES ('staticpages', '1.5.0','1.0.0',1,'http://www.glfusion.org/') ";
$_DATA[] = "INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_enabled, pi_homepage) VALUES ('spamx', '1.1.1','1.0.0',1,'http://www.glfusion.org') ";
$_DATA[] = "INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_enabled, pi_homepage) VALUES ('links', '2.0.0', '1.0.0', 1, 'http://www.glfusion.org/')";
$_DATA[] = "INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_enabled, pi_homepage) VALUES ('polls', '2.0.1', '1.0.0', '1', 'http://www.glfusion.org/')";
$_DATA[] = "INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_enabled, pi_homepage) VALUES ('calendar', '1.0.2', '1.0.0', '1', 'http://www.glfusion.org/')";

$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 1, 'CTL Support', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 2, 'Integrated Plugins', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 3, 'Nouveau Theme', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 4, 'Enhanced Security', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 5, 'Other', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 1, 'Media Gallery', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 2, 'Site Tailor', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 3, 'Forum', 0, '');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 4, 'File Management', 0, '');";

$_DATA[] = "INSERT INTO `{$_TABLES['pollquestions']}` (`pid`, `qid`, `question`) VALUES ('glfusionfeaturepoll', 0, 'What is the best new feature of glFusion?');";
$_DATA[] = "INSERT INTO `{$_TABLES['pollquestions']}` (`pid`, `qid`, `question`) VALUES ('glfusionfeaturepoll', 1, 'What is your favorite plugin?');";

$_DATA[] = "INSERT INTO `{$_TABLES['polltopics']}` (`pid`, `topic`, `voters`, `questions`, `date`, `display`, `is_open`, `hideresults`, `commentcode`, `statuscode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES ('glfusionfeaturepoll', 'Tell us your opinion about glFusion', 0, 2, '2007-01-16 12:24:22', 1, 1, 1, 0, 0, 2, 8, 3, 2, 2, 2);";

$_DATA[] = "INSERT INTO {$_TABLES['postmodes']} (code, name) VALUES ('plaintext','Plain Old Text') ";
$_DATA[] = "INSERT INTO {$_TABLES['postmodes']} (code, name) VALUES ('html','HTML Formatted') ";

$_DATA[] = "INSERT INTO {$_TABLES['sortcodes']} (code, name) VALUES ('ASC','Oldest First') ";
$_DATA[] = "INSERT INTO {$_TABLES['sortcodes']} (code, name) VALUES ('DESC','Newest First') ";

$_DATA[] = "INSERT INTO {$_TABLES['statuscodes']} (code, name) VALUES (1,'Refreshing') ";
$_DATA[] = "INSERT INTO {$_TABLES['statuscodes']} (code, name) VALUES (0,'Normal') ";
$_DATA[] = "INSERT INTO {$_TABLES['statuscodes']} (code, name) VALUES (10,'Archive') ";

$_DATA[] = "INSERT INTO {$_TABLES['stories']} (`sid`, `uid`, `draft_flag`, `tid`, `date`, `title`, `introtext`, `bodytext`, `hits`, `numemails`, `comments`, `trackbacks`, `related`, `featured`, `show_topic_icon`, `commentcode`, `trackbackcode`, `statuscode`, `expire`, `postmode`, `advanced_editor_mode`, `frontpage`, `in_transit`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES ('20080528115808580',2,0,'General',NOW(),'glFusion v1.0.1 - The Next Generation','<div style=\"margin-bottom: 10px\">&nbsp;</div>\r<blockquote>\r<p><a href=\"http://www.glfusion.org\" target=\"_blank\">glFusion</a>, the next generation content management system built on the foundation of <a href=\"http://www.geeklog.net\" target=\"_blank\">Geeklog</a>.</p>\r</blockquote>\r<p>&nbsp;</p>\r<div class=\"yui-g\" style=\"border-bottom: rgb(247,247,247) 2px solid\">\r<div class=\"yui-u first\">\r<h2>Better Style</h2>\r<ul>\r    <li>Nouveau Theme - An update on the old look of Geeklog</li>\r    <li>Improved WYSIWYG editor integration - See the styles available from the theme in the editor.</li>\r    <li>Pure CSS driven layout - Seach Engine friendly and provides a more accessible web site, aiding visually impaired users.</li>\r    <li>XHTML Compliant</li>\r    <li>Additional authoring styles - Improves the look and feel of your stories.</li>\r</ul>\r</div>\r<div class=\"yui-u\">\r<h2>Better Usability</h2>\r<ul>\r    <li>Caching Template Library - Speed and Scalability</li>\r    <li>Integrated Menu Builder</li>\r    <li>Integrated Logo management</li>\r    <li>Integrated Security Features\r    <ul>\r        <li>CAPTCHA Plugin</li>\r        <li>Bad Behavior2 Plugin</li>\r    </ul>\r    </li>\r    <li>Integrated Collaboration Tools\r    <ul>\r        <li>Forum Plugin</li>\r        <li>File Management Plugin</li>\r        <li>Media Management Plugin</li>\r    </ul>\r    </li>\r</ul>\r</div>\r</div>\r<p><em>glFusion</em> builds on the foundation of Geeklog to bring a complete content management solution in a single package. <em>glFusion</em> lets you get your site up and running in no time.</p>\r<p><span class=\"alert\"><b>Don\'t forget to change your password after logging in!</b></span></p>','',5,0,0,0,'',1,0,0,0,0,'0000-00-00 00:00:00','html',1,1,0,2,3,3,2,2,2);";
$_DATA[] = "INSERT INTO {$_TABLES['stories']} (sid, uid, draft_flag, tid, date, title, introtext, bodytext, hits, numemails, comments, trackbacks, related, featured, show_topic_icon, commentcode, trackbackcode, statuscode, expire, postmode, advanced_editor_mode, frontpage, in_transit, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glfusion', 2, 0, 'glFusion', NOW(), 'What Can You Do with glFusion?', '<p><strong><em>glFusion</em></strong> gives the ability to quickly and easily setup your web sites.&nbsp; Unique features include:</p>\r<ul>\r    <li>Integrated Menu Builder - Allowing you to change the site menu using an online editor.</li>\r    <li>Direct integration of the FCKeditor WYSIWYG editor with your theme\'s styles.&nbsp; See exactly how your story will look as you type it.</li>\r    <li>Pre-installed plugins, bringing additional functionality and enhanced security:\r    <ul>\r        <li>Bad Behavior2 - A great tool that blocks bot attacks, spam bots, and other nasties from the Internet</li>\r        <li>CAPTCHA - Ensures that humans are the only ones entering content on your site</li>\r        <li>Forum - A community collaboration and discussion tool.</li>\r        <li>FileMgmt - A complete File Manager for your site</li>\r        <li>Media Gallery - A complete multi-media manager.</li>\r    </ul>\r    </li>\r    <li>glFusion gives you a true XHTML compliant site with a pure CSS driven layout.&nbsp; This is both search engine friendly and also much more accessible by screen readers and other assistance tools used by the visually impaired.</li>\r    <li>Better documentation - gl Labs has published a users guide to glFusion - Check out the online wiki at http://www.glfusion.org</li>\r</ul>\r<p>Welcome to your new site, we hope you enjoy using it as much as we did assembling the software to drive it!</p>\r<p>The glLabs Team</p>', '', 0, 0, 0, 0, '', 0, 1, 0, 0, 0, '0000-00-00 00:00:00', 'html', 0, 1, 0, 3, 3, 3, 2, 2, 2)";

$_DATA[] = "INSERT INTO {$_TABLES['storysubmission']} (sid, uid, tid, title, introtext, date, postmode) VALUES ('security-reminder',2,'glFusion','Are you secure?','<p>This is a reminder to secure your site once you have glFusion up and running. What you should do:</p>\r\r<ol>\r<li>Change the default password for the Admin account.</li>\r<li>Remove the install directory (you won\'t need it any more).</li>\r</ol>',NOW(),'html') ";

$_DATA[] = "INSERT INTO {$_TABLES['syndication']} (type, topic, header_tid, format, limits, content_length, title, description, filename, charset, language, is_enabled, updated, update_info) VALUES ('article', '::all', 'all', 'RSS-2.0', 10, 1, 'glFusion Site', 'Fusing Technology with Style', 'glfusion.rss', 'iso-8859-1', 'en-gb', 1, '0000-00-00 00:00:00', NULL)";

$_DATA[] = "INSERT INTO {$_TABLES['topics']} (tid, topic, imageurl, sortnum, limitnews, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('General','General News','/images/topics/topic_news.png',1,10,6,2,3,2,2,2)";
$_DATA[] = "INSERT INTO {$_TABLES['topics']} (tid, topic, imageurl, sortnum, limitnews, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glFusion','glFusion','/images/topics/topic_gl.png',2,10,6,2,3,2,2,2)";

$_DATA[] = "INSERT INTO {$_TABLES['usercomment']} (uid, commentmode, commentorder, commentlimit) VALUES (1,'nested','ASC',100) ";
$_DATA[] = "INSERT INTO {$_TABLES['usercomment']} (uid, commentmode, commentorder, commentlimit) VALUES (2,'threaded','ASC',100) ";

$_DATA[] = "INSERT INTO {$_TABLES['userindex']} (uid, tids, etids, aids, boxes, noboxes, maxstories) VALUES (1,'','-','','',0,NULL) ";
$_DATA[] = "INSERT INTO {$_TABLES['userindex']} (uid, tids, etids, aids, boxes, noboxes, maxstories) VALUES (2,'','','','',0,NULL) ";

$_DATA[] = "INSERT INTO {$_TABLES['userinfo']} (uid, about, pgpkey, userspace, tokens, totalcomments, lastgranted) VALUES (1,NULL,NULL,'',0,0,0) ";
$_DATA[] = "INSERT INTO {$_TABLES['userinfo']} (uid, about, pgpkey, userspace, tokens, totalcomments, lastgranted) VALUES (2,NULL,NULL,'',0,0,0) ";

$_DATA[] = "INSERT INTO {$_TABLES['userprefs']} (uid, noicons, willing, dfid, tzid, emailstories) VALUES (1,0,0,0,'',0) ";
$_DATA[] = "INSERT INTO {$_TABLES['userprefs']} (uid, noicons, willing, dfid, tzid, emailstories) VALUES (2,0,1,0,'',1) ";

#
# Dumping data for table 'users'
#

$_DATA[] = "INSERT INTO {$_TABLES['users']} (uid, username, fullname, passwd, email, homepage, sig, regdate, cookietimeout, theme, status) VALUES (1,'Anonymous','Anonymous','',NULL,NULL,'',NOW(),0,NULL,3) ";
$_DATA[] = "INSERT INTO {$_TABLES['users']} (uid, username, fullname, passwd, email, homepage, sig, regdate, cookietimeout, theme, status) VALUES (2,'Admin','glFusion Admin Account','5f4dcc3b5aa765d61d8327deb882cf99','root@localhost','http://www.glfusion.org/','',NOW(),28800,NULL,3) ";

$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('totalhits','0') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('lastemailedstories','') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('last_scheduled_run','') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('spamx.counter','0') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('database_version','1') ";
$_DATA[] = "INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('glfusion','1.1.0') ";

$_DATA[] = "INSERT INTO {$_TABLES['trackbackcodes']} (code, name) VALUES (0,'Trackback Enabled') ";
$_DATA[] = "INSERT INTO {$_TABLES['trackbackcodes']} (code, name) VALUES (-1,'Trackback Disabled') ";

?>