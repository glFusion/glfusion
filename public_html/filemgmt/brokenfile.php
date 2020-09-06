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
include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'] .'plugins/filemgmt/include/functions.php';

$lid = 0;
if ( isset($_REQUEST['lid'])) {
    $lid = COM_applyFilter($_REQUEST['lid'],true);
}
if ($lid == 0) {
    echo COM_refresh($_CONF['site_url'] .'/filemgmt/index.php');
    exit;
}

if ( isset($_POST['submit']) ) {
    if( !$FilemgmtUser ) {
        $sender = 0;
    } else {
        $sender = $uid;
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    if ( $sender != 0 ) {
        // Check if REG user is trying to report twice.
        $result=DB_query("SELECT COUNT(*) FROM {$_TABLES['filemgmt_brokenlinks']} WHERE lid='".DB_escapeString($lid)."' AND sender='".intval($sender)."'");
        list ($count)=DB_fetchARRAY($result);
        if ( $count > 0 ) {
            redirect_header("index.php",2,_MD_ALREADYREPORTED);
            exit();
        }
    } else {
        // Check if the sender is trying to vote more than once.
        $result=DB_query("SELECT COUNT(*) FROM {$_TABLES['filemgmt_brokenlinks']} WHERE lid='".DB_escapeString($lid)."' AND ip='".DB_escapeString($ip)."'");
        list ($count)=DB_fetchARRAY($result);
        if ( $count > 0 ) {
            redirect_header("index.php",2,_MD_ALREADYREPORTED);
            exit();
        }
    }
    DB_query("INSERT INTO {$_TABLES['filemgmt_brokenlinks']} (lid, sender, ip) VALUES ('".DB_escapeString($lid)."', '".DB_escapeString($sender)."', '".DB_escapeString($ip)."')") or die('');
    redirect_header("index.php",2,_MD_THANKSFORINFO);
    exit();

} else {
    $display = FM_siteHeader();
    $display .= COM_startBlock(_MD_REPORTBROKEN);
    $T = new Template($_CONF['path'] . '/plugins/filemgmt/templates/');
    $T->set_file('form', 'brokenfile.thtml');
    $T->set_var(array(
        'lid' => $lid,
        'lang_reportbroken' => _MD_REPORTBROKEN,
        'lang_thanksforhelp' => _MD_THANKSFORHELP,
        'lang_forsecurity' => _MD_FORSECURITY,
        'lang_cancel' => _MD_CANCEL,
    ) );
    $T->parse('output', 'form');
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_endBlock();
    $display .= FM_siteFooter();
    echo $display;
}

?>
