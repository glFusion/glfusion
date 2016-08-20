<?php
// +--------------------------------------------------------------------------+
// | Polls Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                          |
// | Installation SQL                                                         |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Trinity Bays      - trinity93 AT gmail DOT com                  |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$_SQL['pollanswers'] = "
CREATE TABLE {$_TABLES['pollanswers']} (
  pid varchar(128) NOT NULL default '',
  qid mediumint(9) NOT NULL default 0,
  aid tinyint(3) unsigned NOT NULL default '0',
  answer varchar(255) default NULL,
  votes mediumint(8) unsigned default NULL,
  remark varchar(255) NULL,
  PRIMARY KEY (pid, qid, aid)
) ENGINE=MyISAM
";

$_SQL['pollquestions'] = "
CREATE TABLE {$_TABLES['pollquestions']} (
    qid mediumint(9) NOT NULL DEFAULT '0',
    pid varchar(128) NOT NULL,
    question varchar(255) NOT NULL,
    PRIMARY KEY (qid, pid)
) ENGINE=MyISAM
";

$_SQL['polltopics'] = "
CREATE TABLE {$_TABLES['polltopics']} (
  pid varchar(128) NOT NULL,
  topic varchar(255) default NULL,
  voters mediumint(8) unsigned default NULL,
  questions int(11) NOT NULL default '0',
  date datetime default NULL,
  display tinyint(4) NOT NULL default '0',
  is_open tinyint(1) NOT NULL default '1',
  login_required tinyint(1) NOT NULL default '0',
  hideresults tinyint(1) NOT NULL default '0',
  commentcode tinyint(4) NOT NULL default '0',
  statuscode tinyint(4) NOT NULL default '0',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  INDEX pollquestions_qid(pid),
  INDEX pollquestions_date(date),
  INDEX pollquestions_display(display),
  INDEX pollquestions_commentcode(commentcode),
  INDEX pollquestions_statuscode(statuscode),
  PRIMARY KEY  (pid)
) ENGINE=MyISAM
";

$_SQL['pollvoters'] = "
CREATE TABLE {$_TABLES['pollvoters']} (
  id int(10) unsigned NOT NULL auto_increment,
  pid varchar(20) NOT NULL default '',
  ipaddress varchar(15) NOT NULL default '',
  uid mediumint(8) NOT NULL default 1,
  date int(10) unsigned default NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM
";

?>