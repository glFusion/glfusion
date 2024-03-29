<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | bad_behavior2.php                                                        |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

global $_BB2_CONF;

$_BB2_CONF = array();

// Plugin info

$_BB2_CONF['pi_name']           = 'bad_behavior2';
$_BB2_CONF['pi_display_name']   = 'Bad Behavior2';
$_BB2_CONF['pi_version']        = '2.0.56';
$_BB2_CONF['gl_version']        = '2.0.1';
$_BB2_CONF['pi_url']            = 'https://www.glfusion.org/';

$_TABLES['bad_behavior2']    	    = $_DB_table_prefix . 'bad_behavior2';
$_TABLES['bad_behavior2_ban'] 	    = $_DB_table_prefix . 'bad_behavior2_ban';
$_TABLES['bad_behavior2_whitelist'] = $_DB_table_prefix . 'bad_behavior2_whitelist';
$_TABLES['bad_behavior2_blacklist'] = $_DB_table_prefix . 'bad_behavior2_blacklist';
?>