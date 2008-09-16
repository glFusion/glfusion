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

require_once '../lib-common.php'; // Path to your lib-common.php
require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';

$deleteid = COM_applyFilter($_GET['id'],true);
$topic = COM_applyFilter($_GET['topic'],true);

$query  = DB_query("SELECT uid,forum,date FROM {$_TABLES['gf_topic']} WHERE id=$topic");
$edittopic = DB_fetchArray($query,false);
$editAllowed = false;

if (forum_modPermission($edittopic['forum'],$_USER['uid'],'mod_edit')) {
    $editAllowed = true;
} elseif ($edittopic['uid'] > 1 AND $edittopic['uid'] == $_USER['uid']) {
    // User is trying to edit their topic post - this is allowed
    if ($edittopic['date'] > 0 ) {
        if ($CONF_FORUM['allowed_editwindow'] > 0) {   // Check if edit timeframe is still valid
            $t2 = $CONF_FORUM['allowed_editwindow'];
            $time = time();
            if ((time() - $t2) < $edittopic['date']) {
                $editAllowed = true;
            }
        } else {
            $editAllowed = true;
        }
    }
} elseif (DB_getItem($_TABLES['gf_attachments'],'tempfile',"id=$deleteid") == 1) {
    $editAllowed = true;
}
// Moderator or logged-in User is editing their topic post
if ($editAllowed) {
    forum_delAttachment($deleteid);
} else {
    COM_errorLog("Forum warning, invalid attempt to delete an attachment - topic:$topic, user:{$_USER['uid']}");
}

$retval = gf_showattachments($topic,'edit');

print $retval;
exit;
?>