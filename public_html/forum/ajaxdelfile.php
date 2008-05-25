<?php
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin for Geeklog - The Ultimate Weblog                   |
// | Release date: Oct 30,2007                                                 |
// +---------------------------------------------------------------------------+
// | ajaxupdate.php - AJAX Server update functions for delete attachment       |
// +---------------------------------------------------------------------------+
// | Plugin Author:   blaine@portalparts.com, www.portalparts.com              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once("../lib-common.php"); // Path to your lib-common.php
require_once ($_CONF['path_html'] . 'forum/include/gf_format.php');

$deleteid = COM_applyFilter($_GET['id'],true);
$topic = COM_applyFilter($_GET['topic'],true);
COM_errorLog("topic:$topic");

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

$template = new Template($_CONF['path_layout'] . 'forum/layout');
$template->set_file ('attachfile', 'attachment.thtml');
$template->set_var('attachments', gf_showattachments($topic,'edit'));
$template->set_var ('LANG_attachments',$LANG_GF10['attachments']);
$template->set_var ('LANG_maxattachments',sprintf($LANG_GF10['maxattachments'],$CONF_FORUM['maxattachments']));
// Check and see if the filemgmt plugin is installed and enabled
if (function_exists('filemgmt_buildAccessSql')) {
    // Generate the select dropdown HTML for the filemgmt categories
    $template->set_var('filemgmt_category_options',gf_makeFilemgmtCatSelect($_USER['uid']));
    $template->set_var('LANG_usefilemgmt',$LANG_GF10['usefilemgmt']);
    $template->set_var('LANG_description', $LANG_GF10['description']);
    $template->set_var('LANG_category', $LANG_GF10['category']);

} else {
    $template->set_var('show_filemgmt_option','none');
}



$template->parse ('output', 'attachfile');
$html = $template->finish ($template->get_var('output'));

$html = htmlentities ($html);
$retval = "<result>";
$retval .= "<content>$html</content>";
$retval .= "</result>";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("content-type: text/xml");

print $retval;


?>