<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | brokenfile.php                                                           |
// |                                                                          |
// | Allows users to report broken file links                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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

$lid = 0;
if (isset($_REQUEST['lid'])) {
    $lid = COM_applyFilter($_REQUEST['lid'],true);
}
if ($lid == 0) {
    COM_refresh($_FM_CONF['url'] .'/index.php');
    exit;
}

$reported_count = 0;
if (isset($_POST['saveBrokenReport']) && SEC_hasRights('filemgmt.user')) {
    $sender = (int)$_USER['uid'];
    $ip = $_SERVER['REMOTE_ADDR'];
    if ( $sender != 1 ) {
        // Check if REG user is trying to report twice.
        $reported_count = DB_count($_TABLES['filemgmt_brokenlinks'], 'lid', $lid);
    } else {
        // Check if the sender is trying to vote more than once.
        $reported_count = DB_count(
            $_TABLES['filemgmt_brokenlinks'],
            array('lid', 'ip'),
            array($lid, DB_escapeString($ip))
        );
    }
    if ($reported_count > 0 ) {
        COM_setMsg(_MD_ALREADYREPORTED);
    } else {
        DB_query(
            "INSERT INTO {$_TABLES['filemgmt_brokenlinks']}
            (lid, sender, ip)
            VALUES
            ($lid, $sender, '" . DB_escapeString($ip) . "')"
        ) or die('');
        COM_setMsg(_MD_THANKSFORINFO);
    }
    COM_refresh($_FM_CONF['url'] . '/index.php');
} else {
    $display = Filemgmt\Menu::siteHeader();
    $display .= Filemgmt\BrokenLink::showForm($lid);
    $display .= Filemgmt\Menu::siteFooter();
    echo $display;
}
