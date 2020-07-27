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
include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';

// Comment out the following security check if you want general filemgmt users access to this report
if (!SEC_hasRights("filemgmt.edit")) {
    COM_errorLOG("Downloadhistory.php => Filemgmt Plugin Access denied. Attempted access for file ID:{$lid}, Remote address is:{$_SERVER['REMOTE_ADDR']}");
    redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOADMIN);
    exit();
}

USES_lib_admin();

$display = COM_siteHeader('none');

$lid = COM_applyFilter($_GET['lid'],true);

$result=DB_query("SELECT title FROM {$_TABLES['filemgmt_filedetail']} WHERE lid='".DB_escapeString($lid)."'");
list($dtitle)=DB_fetchArray($result);

$sql = "SELECT fh.date, fh.uid, fh.remote_ip, u.username
    FROM {$_TABLES['filemgmt_history']} fh
    LEFT JOIN {$_TABLES['users']} u
    ON u.uid = fh.uid
    WHERE fh.lid = $lid";
$header_arr = array(
    array(
        'text'  => 'Date',
        'field' => 'date',
        'sort'  => true,
    ),
    array(
        'text'  => 'User',
        'field' => 'username',
        'sort'  => false,
    ),
    array(
        'text'  => 'Remote IP',
        'field' => 'remote_ip',
        'sort'  => false,
    ),
);
$defsort_arr = array(
    'field' => 'date',
    'direction' => 'desc',
);

$content = '';
$query_arr = array(
    'table' => 'shop.products',
    'sql'   => $sql,
    'query_fields' => array(),
    'default_filter' => '',
);
$filter = '';
$options = '';
$text_arr = array(
    'has_extras' => false,
    'form_url' => $_CONF['site_url'] . "/filemgmt/downloadhistory.php?lid=$lid",
);

$content .= ADMIN_list(
    'filemgmt_downloadhistory',
    NULL,
    $header_arr, $text_arr, $query_arr, $defsort_arr,
    $filter, '', $options, ''
);

$T = new Template($_CONF['path'] . 'plugins/filemgmt/templates/');
$T->set_file('report', 'downloadhistory.thtml');
$T->set_var('dtitle', $dtitle);
$T->set_var('admin_list', $content);

/*$result = DB_query($sql);
$T->set_block('report', 'dataRow', 'dr');
while ($A = DB_fetchArray($result, false)) {
    $T->set_var(array(
        'date'  => $A['date'],
        'username' => $A['username'],
        'remote_ip' => $A['remote_ip'],
    ) );
    $T->parse('dr', 'dataRow', true);
}*/
$T->parse('output', 'report');
$display .= $T->finish ($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;

?>
