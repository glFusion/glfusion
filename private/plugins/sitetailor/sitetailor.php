<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | sitetailor.php                                                           |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008-2009 by the following authors:                       |
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

global $_DB_table_prefix, $_TABLES;

// Add to $_TABLES array the tables your plugin uses

$_TABLES['st_config']       = $_DB_table_prefix . 'st_config';
$_TABLES['st_menus']        = $_DB_table_prefix . 'st_menus';
$_TABLES['st_menus_config'] = $_DB_table_prefix . 'st_menus_config';
$_TABLES['st_menu_elements']= $_DB_table_prefix . 'st_menu_elements';
$_TABLES['st_menu_config']  = $_DB_table_prefix . 'st_menu_config';

$_ST_CONF = array();

// Plugin info

$_ST_CONF['pi_name']            = 'sitetailor';
$_ST_CONF['pi_display_name']    = 'Site Tailor';
$_ST_CONF['pi_version']         = '2.0.2';
$_ST_CONF['gl_version']         = '1.1.6';
$_ST_CONF['pi_url']             = 'http://www.glfusion.org/';
?>