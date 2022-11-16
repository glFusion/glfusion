<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* Plugin system integration options
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by Consult4Hire Inc.
*  Author:
*  Blaine Lang          blaine AT portalparts DOT com
*
*/
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_table_prefix, $_TABLES;

// Plugin info

$_FM_CONF['pi_name']            = 'filemgmt';
$_FM_CONF['pi_display_name']    = 'FileMgmt';
$_FM_CONF['pi_version']         = '1.9.2.1';
$_FM_CONF['gl_version']         = '2.0.1';
$_FM_CONF['pi_url']             = 'https://www.glfusion.org/';

// Database Tables

$_TABLES['filemgmt_cat']         = $_DB_table_prefix . 'filemgmt_category';
$_TABLES['filemgmt_filedetail']  = $_DB_table_prefix . 'filemgmt_filedetail';
$_TABLES['filemgmt_filedesc']    = $_DB_table_prefix . 'filemgmt_filedesc';
$_TABLES['filemgmt_brokenlinks'] = $_DB_table_prefix . 'filemgmt_broken';
$_TABLES['filemgmt_votedata']    = $_DB_table_prefix . 'filemgmt_votedata';
$_TABLES['filemgmt_history']     = $_DB_table_prefix . 'filemgmt_downloadhistory';
