<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | resetrating_rpc.php                                                      |
// |                                                                          |
// | AJAX handler to reset item rating.                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2018 by the following authors:                        |
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

$type       = COM_applyFilter($_GET['t']);
$id         = COM_applyFilter($_GET['id']);
$ip         = $_SERVER['REMOTE_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;

if ( SEC_inGroup('Root') ) {
    RATING_resetRating( $type, $id );
    CACHE_clear();

    COM_errorLog("RATING: User: " . $_USER['username']. " has reset the rating on item: " . $id . " of type " . $type);

    $html = " 0.00 / 5 (0 ".$LANG13['votes'].")";
    $html = htmlentities ($html);

    $retval = "<result>";
    $retval .= "<html>$html</html>";
    $retval .= "</result>";

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("content-type: text/xml");
    print $retval;
}
?>