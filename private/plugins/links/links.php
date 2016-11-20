<?php
// +--------------------------------------------------------------------------+
// | Links Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | links.php                                                                |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Mark Limburg       - mlimburg AT users.sourceforge DOT net      |
// |          Jason Whittenburg  - jwhitten AT securitygeeks DOT com          |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Trinity Bays       - trinity93 AT gmail DOT com                 |
// |          Oliver Spiesshofer - oliver AT spiesshofer DOT com              |
// |          Euan McKay         - info AT heatherengineering DOT com         |
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

global $_DB_table_prefix, $_TABLES;


// Add to $_TABLES array the tables your plugin uses

$_TABLES['linkcategories']      = $_DB_table_prefix . 'linkcategories';
$_TABLES['links']               = $_DB_table_prefix . 'links';
$_TABLES['linksubmission']      = $_DB_table_prefix . 'linksubmission';

// Plugin info

$_LI_CONF['pi_name']            = 'links';
$_LI_CONF['pi_display_name']    = 'Links';
$_LI_CONF['pi_version']         = '2.1.5';
$_LI_CONF['gl_version']         = '1.6.3';
$_LI_CONF['pi_url']             = 'https://www.glfusion.org/';
?>