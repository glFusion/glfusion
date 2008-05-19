<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | File Management Plugin for Geeklog - by portalparts www.portalparts.com | 
// +-------------------------------------------------------------------------+
// | Filemgmt plugin - version 1.5                                           |
// | Date: Mar 18, 2006                                                      |    
// +-------------------------------------------------------------------------+
// | Copyright (C) 2004 by Consult4Hire Inc.                                 |
// | Author:                                                                 |
// | Blaine Lang                 -    blaine@portalparts.com                 |
// |                                                                         |
// | Based on:                                                               |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                     |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                        |
// | Thatware - http://thatware.org/                                         |
// +-------------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or           |
// | modify it under the terms of the GNU General Public License             |
// | as published by the Free Software Foundation; either version 2          |
// | of the License, or (at your option) any later version.                  |
// |                                                                         |
// | This program is distributed in the hope that it will be useful,         |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                    |
// | See the GNU General Public License for more details.                    |
// |                                                                         |
// | You should have received a copy of the GNU General Public License       |
// | along with this program; if not, write to the Free Software Foundation, |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.         |
// |                                                                         |
// +-------------------------------------------------------------------------+
//

require_once("../lib-common.php");
include_once($_CONF[path_html]."filemgmt/include/header.php");
include($_CONF[path_html] ."filemgmt/include/functions.php"); 

// Comment out the following security check if you want general filemgmt users access to this report
if (!SEC_hasRights("filemgmt.edit")) {
    COM_errorLOG("Downloadhistory.php => Filemgmt Plugin Access denied. Attempted access for file ID:{$lid}, Remote address is:{$_SERVER['REMOTE_ADDR']}");
    redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOADMIN);
    exit();
}
$lid = COM_applyFilter($_GET['lid'],true);

$result=DB_query("SELECT title FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid=$lid");
list($dtitle)=DB_fetchARRAY($result); 

$result=DB_query("SELECT date,uid,remote_ip FROM {$_FM_TABLES['filemgmt_history']} WHERE lid=$lid");
$display = COM_siteHeader('none');

$display .= "<table width='100%' border='0' cellspacing='1' cellpadding='4' class='plugin'><tr>";
$display .= "<td colspan='3'><center><H2>". $LANG_FILEMGMT['DownloadReport'] ."</H2></center></td></tr><tr>";
$display .= "<td colspan='3'><H4>File: " .$dtitle ."</H4></td></tr><tr>";
$display .= "<td bgcolor='#000000' width='20%'><b><center><font color='#ffffff' size='3'>Date</font></center></b></td>";
$display .= "<td bgcolor='#000000' width='20%'><b><center><font color='#ffffff' size='3'>User</font></center></b></td>";
$display .= "<td bgcolor='#000000' width='20%'><b><center><font color='#ffffff' size='3'>Remote IP</font></center></b></td>";
$display .= "</tr>";

$highlight = true;
while(list($date,$uid,$remote_ip)=DB_fetchARRAY($result)){
    $result2 = DB_query("SELECT username  FROM {$_TABLES['users']} WHERE uid = $uid");
    list ($username) = DB_fetchARRAY($result2);    
    $result2 = DB_query("SELECT username  FROM {$_TABLES['users']} WHERE uid = $uid");
    list ($username) = DB_fetchARRAY($result2);

    if ($highlight) {
           $highlight=false;
        $display .= "<td bgcolor='#f5f5f5' width=20%>$date</td>";
        $display .= "<td bgcolor='#f5f5f5' width=20%>$username</td>";
        $display .= "<td bgcolor='#f5f5f5' width=20%>$remote_ip</td>";
        $display .= "</tr>";
    }else {
        $highlight=true;
        $display .= "<td  width=20%>$date</td>";
        $display .= "<td  width=20%>$username</td>";
        $display .= "<td  width=20%>$remote_ip</td>";
        $display .= "</tr>";
    }

}
$display .= "</table>";
$display .= "<br>";
$display .= COM_siteFooter();
echo $display;

?>