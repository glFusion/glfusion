<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | dvlpupdate.php                                                           |
// |                                                                          |
// | glFusion Development SQL Updates                                         |
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

require_once '../../lib-common.php';

// Only let admin users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the glFusion Development Code Upgrade Routine.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG27[12]);
    $display .= $LANG27[12];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

COM_errorLog("glFusion: Running code update for glFusion v1.2.0.svn");

$retval .= 'Performing database and configuration upgrades if necessary...<br />';

/*
 * Define SQL update in the $_SQL[] array
 */

$_SQL = array();

/* Execute SQL now to perform the upgrade */
for ($i = 1; $i <= count($_SQL); $i++) {
    COM_errorLOG("glFusion 1.1.0svn Development update: Executing SQL => " . current($_SQL));
    DB_query(current($_SQL),1);
    next($_SQL);
}

/*
 * Place configuration updates here
 */

$c = config::get_instance();

/*
 * Always clear the cache after running development update.
 */

CTL_clearCache();
$c->_purgeCache();

$retval .= 'Development Code upgrades complete - see error.log for details<br />';

$display = COM_siteHeader();
$display .= $retval;
$display .= COM_siteFooter();
echo $display;
exit;
?>