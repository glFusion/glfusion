<?php
/**
* glFusion CMS
*
* CAPTCHA Plugin SQL Schema
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$_SQL['cp_sessions'] = "
CREATE TABLE {$_TABLES['cp_sessions']} (
  `session_id` varchar(40) NOT NULL default '',
  `cptime`  INT(11) NOT NULL default 0,
  `validation` varchar(40) NOT NULL default '',
  `counter`    TINYINT(4) NOT NULL default 0,
  `ip` VARCHAR(16) NOT NULL default '',
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM
";

?>