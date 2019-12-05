<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | page.php                                                                 |
// |                                                                          |
// | This is the main page for the glFusion Static Pages Plugin               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2019 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

require_once 'lib-common.php';

if (!in_array('staticpages', $_PLUGINS)) {
    COM_404();
    exit;
}
$page = '';
$comment_order = '';
$comment_mode  = '';
$cmt_page = '';
$display_mode = '';
$mode = '';

COM_setArgNames(array('page', 'disp_mode'));
$page = COM_applyFilter(COM_getArgument('page'));
$display_mode = COM_applyFilter(COM_getArgument('disp_mode'));
if ($page == '' && isset($_POST['page']) ) {
    $page = COM_sanitizeID(COM_applyFilter($_POST['page']));
}
// from comments display refresh:
if (isset($_POST['order'])) {
    $comment_order =  $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';
    if ( isset($_POST['mode']) ) {
        $comment_mode  = COM_applyFilter($_POST['mode']);
    }
    if ( isset($_POST['cmtpage']) ) {
        $cmt_page      = COM_applyFilter($_POST['cmtpage']);
    }
} else {
    if (isset($_GET['order']) ) {
        $comment_order =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
    }
    if ( isset($_GET['mode']) ) {
        $comment_mode = COM_applyFilter($_GET['mode']);
    }
    if ( isset($_GET['cmtpage']) ) {
        $cmt_page = COM_applyFilter($_GET['cmtpage'],true);
    }
}
$valid_modes = array('threaded','nested','flat','nocomment');
if ( in_array($mode,$valid_modes) === false ) {
    $mode = '';
}

if ($display_mode != 'print') {
    $display_mode = '';
}
$retval = SP_returnStaticpage($page, $display_mode, $comment_order, $comment_mode);
echo $retval;
?>
