<?php
/**
* glFusion CMS
*
* AJAX handler to reset item rating
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2006-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

$type       = COM_applyFilter($_GET['t']);
$id         = COM_applyFilter($_GET['id']);
$ip         = $_SERVER['REMOTE_ADDR'];
$ratingdate = time();
$uid        = isset($_USER['uid']) ? $_USER['uid'] : 1;

if ( SEC_hasRights('story.edit') ) {
    RATING_resetRating( $type, $id );
    CACHE_clear();

    Log::write('system',Log::INFO,"RATING: User: " . $_USER['username']. " has reset the rating on item: " . $id . " of type " . $type);

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