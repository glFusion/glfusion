<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | js.php                                                                   |
// |                                                                          |
// | glFusion CSS Parser                                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
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
error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

if (!defined ('GVERSION')) {
    define('GVERSION', '1.7.3');
}
require_once 'siteconfig.php';
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