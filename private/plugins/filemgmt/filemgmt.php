<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | filemgmt.php                                                             |
// |                                                                          |
// | Plugin system integration options                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2021 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

$_FM_CONF['pi_name']            = 'filemgmt';
$_FM_CONF['pi_display_name']    = 'FileMgmt';
$_FM_CONF['pi_version']         = '1.9.0';
$_FM_CONF['gl_version']         = '2.0.0';
$_FM_CONF['pi_url']             = 'https://www.glfusion.org/';

// Database Tables

$_TABLES['filemgmt_cat']         = $_DB_table_prefix . 'filemgmt_category';
$_TABLES['filemgmt_filedetail']  = $_DB_table_prefix . 'filemgmt_filedetail';
$_TABLES['filemgmt_filedesc']    = $_DB_table_prefix . 'filemgmt_filedesc';
$_TABLES['filemgmt_brokenlinks'] = $_DB_table_prefix . 'filemgmt_broken';
$_TABLES['filemgmt_votedata']    = $_DB_table_prefix . 'filemgmt_votedata';
$_TABLES['filemgmt_history']     = $_DB_table_prefix . 'filemgmt_downloadhistory';
