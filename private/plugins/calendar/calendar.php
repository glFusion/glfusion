<?php
// +--------------------------------------------------------------------------+
// | Calendar Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | calendar.php                                                             |
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
// |          Blaine Lang      - langmail AT sympatico DOT ca                 |
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

// Plugin info

$_CA_CONF['pi_name']            = 'calendar';
$_CA_CONF['pi_display_name']    = 'Calendar';
$_CA_CONF['pi_version']         = '1.1.2';
$_CA_CONF['gl_version']         = '2.0.1';
$_CA_CONF['pi_url']             = 'https://www.glfusion.org/';

// Define database tables

$_TABLES['events']              = $_DB_table_prefix . 'events';
$_TABLES['eventsubmission']     = $_DB_table_prefix . 'eventsubmission';
$_TABLES['personal_events']     = $_DB_table_prefix . 'personal_events';
?>