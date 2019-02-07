<?php
/**
* glFusion CMS
*
* Handle trackback pings for articles and plugins
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2005-2008 by the following authors:
*  Dirk Haun         dirk@haun-online.de
*
*/

require_once ('lib-common.php');
require_once ($_CONF['path_system'] . 'lib-trackback.php');

use \glFusion\Database\Database;

// Note: Error messages are hard-coded in English since there is no way of
// knowing which language the sender of the trackback ping may prefer.
$TRB_ERROR = array (
    'not_enabled'       => 'Trackback not enabled.',
    'illegal_request'   => 'Illegal request.',
    'no_access'         => 'You do not have access to this entry.'
);

if (!$_CONF['trackback_enabled']) {
    TRB_sendTrackbackResponse (1, $TRB_ERROR['not_enabled']);
    exit;
}

if (isset ($_SERVER['REQUEST_METHOD'])) {
    // Trackbacks are only allowed as POST requests
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        header ('Allow: POST');
        COM_displayMessageAndAbort (75, '', 405, 'Method Not Allowed');
    }
}

COM_setArgNames (array ('id', 'type'));
$id = COM_applyFilter (COM_getArgument ('id'));
$type = COM_applyFilter (COM_getArgument ('type'));

if (empty ($id)) {
    TRB_sendTrackbackResponse (1, $TRB_ERROR['illegal_request']);
    exit;
}

$db = Database::getInstance();

if (empty ($type)) {
    $type = 'article';
}

if ($type == 'article') {
    // check if they have access to this story

    $sql = "SELECT trackbackcode
            FROM `{$_TABLES['stories']}`
            WHERE (sid = ?) AND (date <= NOW()) AND (draft_flag = 0) "
            . $db->getPermSql ('AND') . $db->getTopicSql ('AND');

    $tbCode = $db->conn->fetchColumn(
            $sql,
            array($id),
            0,
            array(Database::STRING)
    );
    if ($tbCode === 0) {
        TRB_handleTrackbackPing ($id, $type);
    } else {
        TRB_sendTrackbackResponse (1, $TRB_ERROR['no_access']);
    }
} else if (PLG_handlePingComment ($type, $id, 'acceptByID') === true) {
    TRB_handleTrackbackPing ($id, $type);
} else {
    TRB_sendTrackbackResponse (1, $TRB_ERROR['no_access']);
}
?>