<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | calendar.php                                                             |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

$CONF_FM['pi_name']            = 'filemgmt';
$CONF_FM['pi_display_name']    = 'FileMgmt';
$CONF_FM['pi_version']         = '1.7.6';
$CONF_FM['gl_version']         = '1.1.8';
$CONF_FM['pi_url']             = 'http://www.glfusion.org/';

// Database Tables

$_FM_TABLES['filemgmt_cat']         = $_DB_table_prefix . 'filemgmt_category';
$_FM_TABLES['filemgmt_filedetail']  = $_DB_table_prefix . 'filemgmt_filedetail';
$_FM_TABLES['filemgmt_filedesc']    = $_DB_table_prefix . 'filemgmt_filedesc';
$_FM_TABLES['filemgmt_brokenlinks'] = $_DB_table_prefix . 'filemgmt_broken';
$_FM_TABLES['filemgmt_votedata']    = $_DB_table_prefix . 'filemgmt_votedata';
$_FM_TABLES['filemgmt_history']     = $_DB_table_prefix . 'filemgmt_downloadhistory';

$_TABLES['filemgmt_cat']         = $_DB_table_prefix . 'filemgmt_category';
$_TABLES['filemgmt_filedetail']  = $_DB_table_prefix . 'filemgmt_filedetail';
$_TABLES['filemgmt_filedesc']    = $_DB_table_prefix . 'filemgmt_filedesc';
$_TABLES['filemgmt_brokenlinks'] = $_DB_table_prefix . 'filemgmt_broken';
$_TABLES['filemgmt_votedata']    = $_DB_table_prefix . 'filemgmt_votedata';
$_TABLES['filemgmt_history']     = $_DB_table_prefix . 'filemgmt_downloadhistory';
?>