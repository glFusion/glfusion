<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | getorder.php                                                             |
// |                                                                          |
// | AJAX server to return available menu options                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
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

require_once '../lib-common.php';

header("Cache-Control: no-cache");
header("Pragma: nocache");

// Only let admin users access this page
if (!SEC_hasRights('menu.admin')) {
    echo "";
    exit;
}

if (!isset($_REQUEST['optionid']) ) {
    echo "";
    exit;
}
if (!isset($_REQUEST['menuid']) ) {
    echo "";
    exit;
}
if ( isset($_REQUEST['edit']) ) {
    $edit = 1;
} else {
    $edit = 0;
}

//getting the values
$id_sent    = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['optionid']);
$menu       = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['menuid']);

$order_select = '<label for="menuorder">' . $LANG_MB01['display_after'] . ':</label>';
$order_select .= '<select id="menuorder" name="menuorder">' . LB;
$order_select .= '<option value="0">' . $LANG_MB01['first_position'] . '</option>' . LB;
$result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['menu_elements']} WHERE menu_id='" . (int) $menu . "' AND pid=" . (int) $id_sent . ' ORDER BY element_order ASC');
while ($row = DB_fetchArray($result)) {
    $order_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
}
$order_select .= '</select>' . LB;
echo $order_select;
?>