<?php
/**
* glFusion CMS
*
* glFusion JavaScript Parser
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

if (!defined ('GVERSION')) {
    define('GVERSION', '2.0.0');
}
require_once 'data/siteconfig.php';
if ( !isset($_GET['t']) ) {
	exit;
}
if ( !isset($_CONF['js_cache_filename']) ) {
    $_CONF['js_cache_filename'] = 'js.cache';
}
$theme = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$_GET['t'] );
$theme = str_replace( '..', '', $theme );
if ( @file_exists ($_CONF['path'] . 'data/layout_cache/'.$_CONF['js_cache_filename'] . $theme.'.js') ) {
    header('Content-type: text/javascript');
    ob_clean();
    flush();
    readfile($_CONF['path'] . 'data/layout_cache/'.$_CONF['js_cache_filename'].$theme.'.js');
}
exit;
?>