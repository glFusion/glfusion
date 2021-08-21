<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | downloadhistory.php                                                      |
// |                                                                          |
// | Displays a report of downloaded files                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

$lid = COM_applyFilter($_GET['lid'],true);

// Comment out the following security check if you want general filemgmt users access to this report
if (!SEC_hasRights("filemgmt.edit")) {
    COM_errorLOG("Downloadhistory.php => Filemgmt Plugin Access denied. Attempted access for file ID:{$lid}, Remote address is:{$_SERVER['REMOTE_ADDR']}");
    COM_setMsg(_GL_ERRORNOADMIN, 'error');
    COM_refresh($_CONF['site_url']."/index.php");
    exit();
}

$File = Filemgmt\Download::getInstance($lid);

$T = new Template($_CONF['path'] . 'plugins/filemgmt/templates/');
$T->set_file('report', 'downloadhistory.thtml');
$T->set_var(array(
    'dtitle' => $File->getTitle(),
    'admin_list' => $File->getDownloadHistory(),
) );
$T->parse('output', 'report');

$display = Filemgmt\Menu::siteHeader('none');
$display .= $T->finish ($T->get_var('output'));
$display .= Filemgmt\Menu::siteFooter();
echo $display;
