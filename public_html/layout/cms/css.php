<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | css.php                                                                  |
// |                                                                          |
// | glFusion CSS Parser                                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2019 by the following authors:                        |
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
    define('GVERSION', '1.7.8');
}
require_once '../../siteconfig.php';
if ( !isset($_GET['t']) ) {
	exit;
}
if ( !isset($_CONF['css_cache_filename']) ) {
    $_CONF['css_cache_filename'] = 'style.cache';
}
$theme = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$_GET['t'] );
$theme = str_replace( '..', '', $theme );
if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
    $filename = './'.$_CONF['css_cache_filename'].'.css';
} else {
    $filename = $_CONF['path'] . '/data/layout_cache/'.$_CONF['css_cache_filename'].$theme.'.css';
}
$buf = '';
if ( @file_exists ($filename) ) {
    header('Content-type: text/css');
    $buf = file_get_contents($filename);
    echo $buf;
}
?>