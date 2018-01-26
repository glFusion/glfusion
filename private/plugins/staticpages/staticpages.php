<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | staticpages.php                                                          |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_table_prefix, $_TABLES;

// Add to $_TABLES array the tables your plugin uses

$_TABLES['staticpage']          = $_DB_table_prefix . 'staticpage';

// Plugin info

$_SP_CONF['pi_name']            = 'staticpages';
$_SP_CONF['pi_display_name']    = 'Pages';
$_SP_CONF['pi_version']         = '1.6.4';
$_SP_CONF['gl_version']         = '1.8.0';
$_SP_CONF['pi_url']             = 'https://www.glfusion.org/';
?>
