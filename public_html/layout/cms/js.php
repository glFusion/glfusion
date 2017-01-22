<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | js.php                                                                   |
// |                                                                          |
// | glFusion JS Parser                                                      |
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

if (!defined ('GVERSION')) {
    define('GVERSION', '1.6.6');
}

require_once '../../siteconfig.php';

if ( !isset($_GET['t']) ) {
	exit;
}

if ( !isset($_CONF['js_cache_filename']) ) {
    $_CONF['js_cache_filename'] = 'js.cache';
}

$theme = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '',$_GET['t'] );
$theme = str_replace( '..', '', $theme );
$buf = '';
if ( @file_exists ($_CONF['path'] . '/data/layout_cache/'.$_CONF['js_cache_filename'].'_'.$theme.'.js') ) {
    header('Content-type: text/javascript');
    $buf = file_get_contents($_CONF['path'] . '/data/layout_cache/'.$_CONF['js_cache_filename'].'_'.$theme.'.js');
    echo $buf;
}
?>