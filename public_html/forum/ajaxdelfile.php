<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | ajaxdelfile.php                                                          |
// |                                                                          |
// | AJAX Server update functions for delete attachment                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
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
    COM_errorLog("Forum warning, invalid attempt to delete an attachment - topic:$topic, user:{$_USER['uid']}");
}

$retval = _ff_showattachments($topic,'edit');

print $retval;
exit;
?>