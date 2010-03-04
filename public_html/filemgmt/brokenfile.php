<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | brokenfile.php                                                           |
// |                                                                          |
// | Allows users to report broken file links                                 |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
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

$lid = COM_applyFilter($_REQUEST['lid'],true);
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
        $result=DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='".addslashes($lid)."' AND sender='".intval($sender)."'");
        list ($count)=DB_fetchARRAY($result);
        if ( $count > 0 ) {
            redirect_header("index.php",2,_MD_ALREADYREPORTED);
            exit();
        }
    } else {
        // Check if the sender is trying to vote more than once.
        $result=DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='".addslashes($lid)."' AND ip='".addslashes($ip)."'");
        list ($count)=DB_fetchARRAY($result);
        if ( $count > 0 ) {
            redirect_header("index.php",2,_MD_ALREADYREPORTED);
            exit();
        }
    }
    DB_query("INSERT INTO {$_FM_TABLES['filemgmt_brokenlinks']} (lid, sender, ip) VALUES ('".addslashes($lid)."', '".addslashes($sender)."', '".addslashes($ip)."')") or die('');
    redirect_header("index.php",2,_MD_THANKSFORINFO);
    exit();

} else {
    $display = FM_siteHeader();
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");
    $display .= "<form action=\"brokenfile.php\" method=\"post\">";
    $display .= '<input type="hidden" name="lid" value="'.$lid.'"' . XHTML . '>';
    $display .= '<table border="0" cellpadding="1" cellspacing="0" width="80%" class="plugin"><tr>';
    $display .= '<td class="pluginHeader">'._MD_REPORTBROKEN.'</td></tr>';
    $display .= '<tr><td style="padding:10px;">';
    $display .= _MD_THANKSFORHELP;
    $display .= "<br" . XHTML . ">"._MD_FORSECURITY."<br" . XHTML . "><br" . XHTML . ">";
    $display .= '</td></tr><tr><td style="padding:0px 0px 10px 10px;">';
    $display .= '<input type="submit" name="submit" value="'._MD_REPORTBROKEN.'"' . XHTML . '>';
    $display .= '&nbsp;<input type="button" value="'._MD_CANCEL.'" onclick="javascript:history.go(-1)"' . XHTML . '>';
    $display .= "</td></tr></table></form>";
    $display .= COM_endBlock();
    $display .= FM_siteFooter();
    echo $display;
}

?>