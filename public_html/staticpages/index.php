<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | This is the main page for the glFusion Static Pages Plugin               |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

if (!in_array('staticpages', $_PLUGINS)) {
    COM_404();
    exit;
}

// MAIN

$page = '';
$comment_order = '';
$comment_mode  = '';
$cmt_page = '';
$display_mode = '';

COM_setArgNames(array('page', 'disp_mode'));
$page = COM_applyFilter(COM_getArgument('page'));
$display_mode = COM_applyFilter(COM_getArgument('disp_mode'));

if ($page == '' && isset($_POST['page']) ) {
    $page = COM_sanitizeID(COM_applyFilter($_POST['page']));
}

// from comments display refresh:
if (isset($_POST['order'])) {
    $comment_order = COM_applyFilter($_POST['order']);
    $comment_mode  = COM_applyFilter($_POST['mode']);
    $cmt_page = COM_applyFilter($_POST['cmtpage']);
    if ((strcasecmp($comment_order, 'ASC') != 0) &&
            (strcasecmp($comment_order, 'DESC') != 0)) {
        $comment_order = '';
    }
} else {
    if (isset($_GET['order']) ) {
        $comment_order =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
    } else {
        $comment_order = '';
    }
    if ( isset($_GET['mode']) ) {
        $comment_mode = COM_applyFilter($_GET['mode']);
    } else {
        $comment_mode = '';
    }
    if ( isset($_GET['cmtpage']) ) {
        $cmt_page = COM_applyFilter($_GET['cmtpage'],true);
    }

}
$valid_modes = array('threaded','nested','flat','nocomment');
if ( in_array($comment_mode,$valid_modes) === false ) {
    $comment_mode = '';
}

if ($display_mode != 'print') {
    $display_mode = '';
}
$pageArgument = '?page='.$page;
$dmArgument   = empty($display_mode) ? '' : '&disp_mode='.$display_mode;

$cmtOrderArgument = empty($comment_order) ? '' : 'order='.$comment_order;
$cmtModeArgument  = empty($comment_mode)  ? '' : 'mode='.$comment_mode;
$cmtPageArgument  = empty($cmt_page)      ? '' : 'cmtpage='.(int) $cmt_page;

$baseURL = COM_buildURL($_CONF['site_url'].'/page.php' . $pageArgument . $dmArgument);
if (strpos($baseURL, '?') === false) {
    $sep = '?';
} else {
    $sep = '&';
}
if ( $cmtOrderArgument != '' ) {
    $baseURL = $baseURL . $sep . $cmtOrderArgument;
    $sep = '&';
}
if ( $cmtModeArgument != '' ) {
    $baseURL = $baseURL . $sep . $cmtModeArgument;
    $sep = '&';
}
if ( $cmtPageArgument != '' ) {
    $baseURL = $baseURL . $sep . $cmtPageArgument;
}

// Permanent redirection
header("HTTP/1.1 301 Moved Permanently");
header("Location: ".$baseURL);
exit();
?>