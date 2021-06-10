<?php
/**
* glFusion CMS - Forum Plugin
*
* AJAX Server update functions for delete attachment
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Blaine Lang          blaine AT portalparts DOT com
*                        www.portalparts.com
*
*/

use \glFusion\Log\Log;

require_once '../lib-common.php';
if (!in_array('forum', $_PLUGINS)) {
    exit;
}

USES_forum_functions();
USES_forum_format();

$deleteid = COM_applyFilter($_GET['id'],true);
$topic    = COM_applyFilter($_GET['topic'],true);

$query  = DB_query("SELECT uid,forum,date FROM {$_TABLES['ff_topic']} WHERE id=".(int) $topic);
$edittopic = DB_fetchArray($query,false);
$editAllowed = false;

if ( COM_isAnonUser() ) {
    $uid = 1;
} else {
    $uid = $_USER['uid'];
}

if (forum_modPermission($edittopic['forum'],$uid,'mod_edit')) {
    $editAllowed = true;
} elseif ($edittopic['uid'] > 1 AND $edittopic['uid'] == $uid) {
    // User is trying to edit their topic post - this is allowed
    if ($edittopic['date'] > 0 ) {
        if ($_FF_CONF['allowed_editwindow'] > 0) {   // Check if edit timeframe is still valid
            $t2 = $_FF_CONF['allowed_editwindow'];
            $time = time();
            if ((time() - $t2) < $edittopic['date']) {
                $editAllowed = true;
            }
        } else {
            $editAllowed = true;
        }
    }
} elseif (DB_getItem($_TABLES['ff_attachments'],'tempfile',"id=".(int) $deleteid) == 1) {
    $editAllowed = true;
}
// Moderator or logged-in User is editing their topic post
if ($editAllowed) {
    forum_delAttachment($deleteid);
} else {
    Log::write('system',Log::WARNING,'Forum warning, invalid attempt to delete an attachment - topic: '.$topic.', user: '.$_USER['uid']);
}

$retval = _ff_showattachments($topic,'edit');

print $retval;
exit;
?>