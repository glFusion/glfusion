<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin                                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans              - mark at gllabs.org                          |
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
//

require_once('../../../lib-common.php');

header("Cache-Control: no-cache");
header("Pragma: nocache");

// Only let admin users access this page
if (!SEC_hasRights('sitetailor.admin')) {
    echo "";
    exit;
}

if (!isset($_REQUEST['optionid']) ) {
    echo "";
    exit;
}

$menu = 0;

//getting the values
$id_sent    = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['optionid']);

$order_select = '<label for="menuorder">' . $LANG_ST01['display_after'] . '</label>';
$order_select .= '<select id="menuorder" name="menuorder">' . LB;
if ( $id_sent == 0 ) {
    $order_select .= '<option value="0">' . $LANG_ST01['top_level'] . '</option>' . LB;
} else {
    $order_select .= '<option value="0">' . $LANG_ST01['first_position'] . '</option>' . LB;
}
$result = DB_query("SELECT id,element_label,element_order FROM {$_TABLES['st_menu_elements']} WHERE menu_id='" . $menu . "' AND pid=" . $id_sent . ' ORDER BY element_order ASC');
while ($row = DB_fetchArray($result)) {
    $order_select .= '<option value="' . $row['id'] . '">' . $row['element_label'] . '</option>' . LB;
}
$order_select .= '</select>' . LB;
echo $order_select;
?>