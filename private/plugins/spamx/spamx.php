<?php
/**
* glFusion CMS - SpamX Plugin
*
* glFusion Interface functions.inc
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the prior work
*  Copyright (C) 2004-2010 by the following authors:
*   Authors: Tom Willett     tomw AT pigstye DOT net
*            Dirk Haun       dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// Plugin info

$_SPX_CONF['pi_name']            = 'spamx';
$_SPX_CONF['pi_display_name']    = 'SpamX';
$_SPX_CONF['pi_version']         = '2.0.1';
$_SPX_CONF['gl_version']         = '2.0.0';
$_SPX_CONF['pi_url']             = 'https://www.glfusion.org/';

$_TABLES['spamx']               = $_DB_table_prefix . 'spamx';
$_TABLES['spamx_stats']         = $_DB_table_prefix . 'spamx_stats';
?>