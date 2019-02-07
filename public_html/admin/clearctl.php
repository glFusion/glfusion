<?php
/**
* glFusion CMS
*
* glFusion Cache Clear
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

$display = '';

if (!SEC_inGroup ('Root')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_showMessageText($LANG20[6],$LANG20[1],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}


/*
 * Main processing
 */

// validate the referer here - just to be safe....
$dirty_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_CONF['site_url'];
if ( $dirty_referer == '' ) {
    $dirty_referer = $_CONF['site_url'];
}
$referer = COM_sanitizeUrl($dirty_referer);
$sLength = strlen($_CONF['site_url']);
if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
    $referer = $_CONF['site_url'];
}
CACHE_clear();
COM_setMessage(500);
echo COM_refresh($referer);
?>