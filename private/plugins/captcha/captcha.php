<?php
/**
* glFusion CMS
*
* CAPTCHA Plugin
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

global $_DB_table_prefix, $_TABLES, $_CP_CONF;

// Plugin info

$_CP_CONF['pi_name']            = 'captcha';
$_CP_CONF['pi_display_name']    = 'CAPTCHA';
$_CP_CONF['pi_version']         = '3.7.1';
$_CP_CONF['gl_version']         = '2.0.1';
$_CP_CONF['pi_url']             = 'https://www.glfusion.org/';

// Database table definitions

$_TABLES['cp_sessions']         = $_DB_table_prefix . 'cp_sessions';
?>