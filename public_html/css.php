<?php
/**
* glFusion CMS
*
* glFusion CSS Parser
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
    define('GVERSION', '2.0.1');
}
require_once 'data/siteconfig.php';
if ( !isset($_GET['t']) ) {
	exit;
}
if ( !isset($_CONF['css_cache_filename']) ) {
    $_CONF['css_cache_filename'] = 'style.cache';
}
$theme = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$_GET['t'] );
$theme = str_replace( '..', '', $theme );
if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
    $filename = './'.$_CONF['css_cache_filename'].$theme.'.css';
} else {
    $filename = $_CONF['path'] . 'data/layout_cache/'.$_CONF['css_cache_filename'].$theme.'.css';
}
if ( @file_exists ($filename) ) {
    header('Content-type: text/css');
    ob_clean();
    flush();
    readfile($filename);
}
exit;
?>