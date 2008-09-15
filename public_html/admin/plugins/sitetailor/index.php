<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Main administrative interface to Site Tailor.                            |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

require_once('../../../lib-common.php');

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('sitetailor.admin')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Site Tailor Administration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_ST00['access_denied']);
    $display .= $LANG_ST00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$display = COM_siteHeader();
$display .= '<span><img style="vertical-align:middle;padding-right:10px;float:left;" src="images/sitetailor.png" alt=""' . XHTML . '></span><h1 style="float:left">' . $LANG_ST00['menulabel'] . '</h1>' . LB;
$display .= '<div style="clear:both;"></div>' . LB;
$display .= '<ul>' . LB;
$display .= '<li><a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/menu.php">Menu Administration</a></li>' . LB;
$display .= '<li><a href="'.$_CONF['site_admin_url'].'/plugins/sitetailor/logo.php">Logo Administration</a></li>' . LB;
$display .= '</ul>' . LB;
$display .= COM_siteFooter();
echo $display;
exit;
?>