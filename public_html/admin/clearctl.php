<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | clearctl.php                                                             |
// |                                                                          |
// | Removed all cached templates                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
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

require_once '../lib-common.php';

$display = '';

if (!SEC_inGroup ('Root')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_startBlock ($LANG20[1], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= '<p>' . $LANG20[6] . '</p>';
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

/*
 * Main processing
 */

// validate the referer here - just to be safe....
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_CONF['site_url'];
if ( $referer == '' ) {
    $referer = $_CONF['site_url'];
}

$sLength = strlen($_CONF['site_url']);
if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
    $referer = $_CONF['site_url'];
}

$hasargs = strstr( $referer, '?' );
if ( $hasargs ) {
    $sep = '&amp;';
} else {
    $sep = '?';
}

CTL_clearCache();

echo COM_refresh($referer . $sep . 'msg=500');
?>