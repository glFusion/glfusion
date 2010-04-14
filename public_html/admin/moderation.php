<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | moderation.php                                                           |
// |                                                                          |
// | glFusion main administration page.                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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
require_once 'auth.inc.php';

USES_lib_user();
USES_lib_story();
USES_lib_admin();

function MODERATE_submissions($token)
{
    global $_CONF, $LANG01, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $menu_arr = array(
            array('url' => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );
    $retval  = COM_startBlock($LANG01[10],'', COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu($menu_arr, $LANG29['info'],
                                $_CONF['layout_url'] . '/images/icons/moderation.'. $_IMAGE_TYPE);

    if (SEC_hasRights('story.moderate')) {
        $retval .= MODERATE_itemList('story', $token);
    }

    if (SEC_hasRights('story.edit')) {
        if ($_CONF['listdraftstories'] == 1) {
            $retval .= MODERATE_draftList($token);
        }
    }
    if ($_CONF['usersubmission'] == 1) {
        if (SEC_hasRights ('user.edit') && SEC_hasRights ('user.delete')) {
            $retval .= MODERATE_userList($token);
        }
    }

    $retval .= PLG_showModerationList($token);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
 * used for the lists of submissions and draft stories in admin/moderation.php
 *
 */
function MODERATE_getListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN;

    $retval = '';

    $type = '';
    if (isset ($A['_moderation_type'])) {
        $type = $A['_moderation_type'];
    }
    switch ($fieldname) {
    case 'edit':
        $retval = COM_createLink($icon_arr['edit'], $A['edit']);
        break;

    case 'delete':
        $retval = "<input type=\"radio\" name=\"action[{$A['row']}]\" value=\"delete\"" . XHTML . ">";
        break;

    case 'approve':
        $retval = "<input type=\"radio\" name=\"action[{$A['row']}]\" value=\"approve\"" . XHTML . ">"
                 ."<input type=\"hidden\" name=\"id[{$A['row']}]\" value=\"{$A[0]}\"" . XHTML . ">";
        break;

    case 'day':
        $retval = strftime($_CONF['daytime'], $A['day']);
        break;

    case 'tid':
        $retval = DB_getItem($_TABLES['topics'], 'topic',
                             "tid = '".DB_escapeString($A['tid'])."'");
        break;

    case 'uid':
        $name = '';
        if ($A['uid'] == 1) {
            $name = htmlspecialchars(COM_stripslashes(DB_getItem($_TABLES['commentsubmissions'], 'name', "cid = '".DB_escapeString($A['id'])."'")));
        }
        if (empty($name)) {
            $name = DB_getItem($_TABLES['users'], 'username',
                               "uid = ".intval($A['uid']));
        }
        if ($A['uid'] == 1) {
            $retval = $name;
        } else {
            $retval = COM_createLink($name, $_CONF['site_url']
                            . '/users.php?mode=profile&amp;uid=' . $A['uid']);
        }
        break;

    case 'publishfuture':
        if (!SEC_inGroup('Comment Submitters', $A['uid']) && ($A['uid'] > 1)) {
            $retval = "<input type=\"checkbox\" name=\"publishfuture[]\" value=\"{$A['uid']}\"" . XHTML . ">";
        } else {
            $retval = $LANG_ADMIN['na'];
        }
        break;

    default:
        if (($fieldname == 3) && ($type == 'story')) {
            $retval = DB_getItem($_TABLES['topics'], 'topic',
                                  "tid = '".DB_escapeString($A[3])."'");
        } elseif (($fieldname == 2) && ($type == 'comment')) {
            $retval = COM_truncate(strip_tags($A['comment']), 40, '...');
        } else {
            $retval = COM_makeClickableLinks(stripslashes($fieldvalue));
        }
        break;
    }

    return $retval;
}

/**
* Displays items needing moderation
*
* Displays the moderation list of items from the submission tables
*
* @type     string      Type of object to build list for
*
*/
function MODERATE_itemList($type, $token)
{
    global $_CONF, $_TABLES, $LANG29, $LANG_ADMIN;

    require_once( $_CONF['path_system'] . 'lib-admin.php' );

    $retval = '';
    $isplugin = false;

    if ((strlen ($type) > 0) && ($type <> 'story')) {
        $function = 'plugin_itemlist_' . $type;
        if (function_exists ($function)) {
            $plugin = new Plugin();
            $plugin = $function($token);
            if (is_string($plugin)) {
                return '<div class="block-box">'.$plugin.'</div>';
            } elseif (is_object($plugin)) {
                $helpfile = $plugin->submissionhelpfile;
                $sql = $plugin->getsubmissionssql;
                $H = $plugin->submissionheading;
                $section_title = $plugin->submissionlabel;
                $section_help = $helpfile;
                $isplugin = true;
            }
        }
    } else { // story submission
        $sql = "SELECT sid AS id,title,date,tid FROM {$_TABLES['storysubmission']}" . COM_getTopicSQL ('WHERE') . " ORDER BY date ASC";
        $H =  array($LANG29[10],$LANG29[14],$LANG29[15]);
        $section_title = $LANG29[35];
        $section_help = 'ccstorysubmission.html';
    }
    if ( !isset($H[0]) || empty($H[0]) ) {
        $H[0] = $LANG29[10];
    }
    if ( !isset($H[1]) || empty($H[0]) ) {
        $H[1] = $LANG29[14];
    }
    if ( !isset($H[2]) || empty($H[2]) ) {
        $H[2] = $LANG29[15];
    }

    // run SQL but this time ignore any errors
    if (!empty ($sql)) {
        $sql .= ' LIMIT 50'; // quick'n'dirty workaround to prevent timeouts
        $result = DB_query($sql, 1);
    }
    if (empty ($sql) || DB_error()) {
        // was more than likely a plugin that doesn't need moderation
        //$nrows = -1;
        return;
    } else {
        $nrows = DB_numRows($result);
    }
    $data_arr = array();
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        if ($isplugin)  {
            $A['edit'] = $_CONF['site_admin_url'] . '/plugins/' . $type
                     . '/index.php?mode=editsubmission&amp;id=' . $A[0];
        } else {
            $A['edit'] = $_CONF['site_admin_url'] . '/' .  $type
                     . '.php?editsubmission=x&amp;id=' . $A[0];
        }
        $A['row'] = $i;
        $A['_moderation_type'] = $type;
        $data_arr[$i] = $A;
    }

    $header_arr = array(      // display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center'),
        array('text' => $H[0], 'field' => 1),
        array('text' => $H[1], 'field' => 2),
        array('text' => $H[2], 'field' => 3),
        array('text' => $LANG29[2], 'field' => 'delete', 'align' => 'center'),
        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center'));

    $text_arr = array('has_menu'    => false,
                      'title'       => $section_title,
                      'help_url'    => $section_help,
                      'no_data'   => $LANG29[39],
                      'form_url'  => "{$_CONF['site_admin_url']}/moderation.php"
    );
    $form_arr = array("bottom" => '', "top" => '');
    if ($nrows > 0) {
        $form_arr['bottom'] = '<input type="hidden" name="type" value="' . $type . '"' . XHTML . '>' . LB
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"'. XHTML . '>' . LB
                . '<input type="hidden" name="mode" value="moderation"' . XHTML . '>' . LB
                . '<input type="hidden" name="count" value="' . $nrows . '"' . XHTML . '>'
                . '<p class="aligncenter"><input type="submit" value="'
                . $LANG_ADMIN['submit'] . '"' . XHTML . '></p>' . LB;
    }

    $options = array('chkselect' => true, 'chkfield' => 'id');
    $table = ADMIN_simpleList('MODERATE_getListField', $header_arr,
                              $text_arr, $data_arr, $options, $form_arr);
    $retval .= $table;

    return $retval;
}

/**
* Displays new user submissions
*
* When enabled, this will list all the new users which have applied for a
* site membership. When approving an application, an email containing the
* password is sent out immediately.
*
*/
function MODERATE_userList($token)
{
    global $_CONF, $_TABLES, $LANG29, $LANG_ADMIN;

    require_once ($_CONF['path_system'] . 'lib-admin.php');

    $retval = '';
    $sql = "SELECT uid as id,username,fullname,email FROM {$_TABLES['users']} WHERE status = 2";
    $result = DB_query ($sql);
    $nrows = DB_numRows($result);
    $data_arr = array();
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['edit'] = $_CONF['site_admin_url'].'/user.php?edit=x&amp;uid='.$A['id'];
        $A['row'] = $i;
        $A['fullname'] = stripslashes($A['fullname']);
        $A['email'] = stripslashes($A['email']);
        $data_arr[$i] = $A;
    }
    $header_arr = array(
        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center'),
        array('text' => $LANG29[16], 'field' => 1),
        array('text' => $LANG29[17], 'field' => 2),
        array('text' => $LANG29[18], 'field' => 3),
        array('text' => $LANG29[2], 'field' => 'delete', 'align' => 'center'),
        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center')
    );

    $text_arr = array('has_menu'  => false,
                      'title'     => $LANG29[40],
                      'help_url'  => '',
                      'no_data'   => $LANG29[39],
                      'form_url'  => "{$_CONF['site_admin_url']}/moderation.php"
    );

    $options = array('chkselect' => true, 'chkfield' => 'id');

    $form_arr = array("bottom" => '', "top" => '');
    if ($nrows > 0) {
        $form_arr['bottom'] = '<input type="hidden" name="type" value="user"' . XHTML . '>' . LB
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"'. XHTML . '>' . LB
                . '<input type="hidden" name="mode" value="moderation"' . XHTML . '>' . LB
                . '<input type="hidden" name="count" value="' . $nrows . '"' . XHTML . '>'
                . '<p align="center"><input type="submit" value="'
                . $LANG_ADMIN['submit'] . '"' . XHTML . '></p>' . LB;
    }

    $table = ADMIN_simpleList('MODERATE_getListField', $header_arr,
                              $text_arr, $data_arr, $options, $form_arr);
    $retval .= $table;


    return $retval;
}

/**
* Displays a list of all the stories that have the 'draft' flag set.
*
* When enabled, this will list all the stories that have been marked as
* 'draft'. Approving a story from this list will clear the draft flag and
* thus publish the story.
*
*/
function MODERATE_draftList($token)
{
    global $_CONF, $_TABLES, $LANG24, $LANG29, $LANG_ADMIN;

    require_once( $_CONF['path_system'] . 'lib-admin.php' );

    $retval = '';

    $result = DB_query ("SELECT sid AS id,title,UNIX_TIMESTAMP(date) AS day,tid FROM {$_TABLES['stories']} WHERE (draft_flag = 1)" . COM_getTopicSQL ('AND') . COM_getPermSQL ('AND', 0, 3) . " ORDER BY date ASC");
    $nrows = DB_numRows($result);
    $data_arr = array();

    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray($result);
        $A['edit'] = $_CONF['site_admin_url'] . '/story.php?edit=x&amp;sid='
                    . $A['id'];
        $A['row'] = $i;
        $A['title'] = stripslashes($A['title']);
        $A['tid'] = stripslashes($A['tid']);
        $data_arr[$i] = $A;
    }

    $header_arr = array(
        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center'),
        array('text' => $LANG29[10], 'field' => 'title'),
        array('text' => $LANG29[14], 'field' => 'day'),
        array('text' => $LANG29[15], 'field' => 'tid', 'align' => 'center'),
        array('text' => $LANG29[2], 'field' => 'delete', 'align' => 'center'),
        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center'));

    $text_arr = array('has_menu'  => false,
                      'title'     => $LANG29[35] . ' (' . $LANG24[34] . ')',
                      'help_url'  => '',
                      'no_data'   => $LANG29[39],
                      'form_url'  => "{$_CONF['site_admin_url']}/moderation.php");

    $form_arr = array("bottom" => '', "top" => '');
    if ($nrows > 0) {
        $form_arr['bottom'] = '<input type="hidden" name="type" value="draft"' . XHTML . '>' . LB
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"'. XHTML . '>' . LB
                . '<input type="hidden" name="mode" value="moderation"' . XHTML . '>' . LB
                . '<input type="hidden" name="count" value="' . $nrows . '"' . XHTML . '>'
                . '<p align="center"><input type="submit" value="'
                . $LANG_ADMIN['submit'] . '"' . XHTML . '></p>' . LB;
    }

    $options = array('chkselect' => true, 'chkfield' => 'id');
    $table = ADMIN_simpleList('MODERATE_getListField', $header_arr,
                              $text_arr, $data_arr, $options, $form_arr);
    $retval .= $table;
    return $retval;
}

/**
* Moderates an item
*
* This will actually perform moderation (approve or delete) one or more items
*
* @param    array   $mid        Array of items
* @param    array   $action     Array of actions to perform on items
* @param    string  $type       Type of items ('story', etc.)
* @param    int     $count      Number of items to moderate
* @return   string              HTML for "command and control" page
*
*/
function MODERATE_items($mid, $action, $type, $count)
{
    global $_CONF, $_TABLES;

    $retval = '';

    switch ($type) {
    case 'story':
        $id = 'sid';
        $table = $_TABLES['stories'];
        $submissiontable = $_TABLES['storysubmission'];
        $fields = 'sid,uid,tid,title,introtext,date,postmode';
        break;
    default:
        if (strlen($type) <= 0) {
            // something is terribly wrong, bail
            $retval .= COM_errorLog("Unable to find type of $type in moderation() in moderation.php");
            return $retval;
        }
        list($id, $table, $fields, $submissiontable) = PLG_getModerationValues($type);
    }

    // Set true if an valid action other than delete_all is selected
    $formaction = false;

    for ($i = 0; $i < $count; $i++) {
        if (isset($action[$i]) AND ($action[$i] != '')) {
            $formaction = true;
        } else {
            continue;
        }

        switch ($action[$i]) {
        case 'delete':
            if (!empty ($type) && ($type <> 'story') && ($type <> 'draft')) {
                // There may be some plugin specific processing that needs to
                // happen first.
                $retval .= PLG_deleteSubmission($type, $mid[$i]);
            }
            if (empty($mid[$i])) {
                $retval .= COM_errorLog("moderation.php just tried deleting everything in table $submissiontable because it got an empty id.  Please report this immediately to your site administrator");
                return $retval;
            }
            if ($type == 'draft') {
                STORY_deleteStory($mid[$i]);
            } else {
                DB_delete($submissiontable,"$id",$mid[$i]);
            }
            break;

        case 'approve':
            if ($type == 'story') {
                $result = DB_query ("SELECT * FROM {$_TABLES['storysubmission']} WHERE sid = '$mid[$i]'");
                $A = DB_fetchArray ($result);
                $A['related'] = DB_escapeString (implode ("\n", STORY_extractLinks ($A['introtext'])));
                $A['owner_id'] = $A['uid'];
                $A['title'] = DB_escapeString ($A['title']);
                $A['introtext'] = DB_escapeString ($A['introtext']);
                $A['bodytext'] = DB_escapeString( $A['bodytext'] );
                $result = DB_query ("SELECT group_id,perm_owner,perm_group,perm_members,perm_anon,archive_flag FROM {$_TABLES['topics']} WHERE tid = '{$A['tid']}'");
                $T = DB_fetchArray ($result);
                if ($T['archive_flag'] == 1) {
                    $frontpage = 0;
                } else if (isset ($_CONF['frontpage'])) {
                    $frontpage = $_CONF['frontpage'];
                } else {
                    $frontpage = 1;
                }
                DB_save ($_TABLES['stories'],'sid,uid,tid,title,introtext,bodytext,related,date,show_topic_icon,commentcode,trackbackcode,postmode,frontpage,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon',
                "'{$A['sid']}',{$A['uid']},'{$A['tid']}','{$A['title']}','{$A['introtext']}','{$A['bodytext']}','{$A['related']}','{$A['date']}','{$_CONF['show_topic_icon']}','{$_CONF['comment_code']}','{$_CONF['trackback_code']}','{$A['postmode']}',$frontpage,{$A['owner_id']},{$T['group_id']},{$T['perm_owner']},{$T['perm_group']},{$T['perm_members']},{$T['perm_anon']}");
                DB_delete($_TABLES['storysubmission'],"$id",$mid[$i]);
                PLG_itemSaved($A['sid'], 'article');
                COM_rdfUpToDateCheck ();
                COM_olderStuff ();
            } else if ($type == 'draft') {
                DB_query ("UPDATE {$_TABLES['stories']} SET draft_flag = 0 WHERE sid = '{$mid[$i]}'");

                COM_rdfUpToDateCheck ();
                COM_olderStuff ();
            } else {
                // This is called in case this is a plugin. There may be some
                // plugin specific processing that needs to happen.
                DB_copy($table,$fields,$fields,$submissiontable,$id,$mid[$i]);
                $retval .= PLG_approveSubmission($type,$mid[$i]);
            }
            break;
        }
    }

    // Check if there was no direct action used on the form
    // and if the delete_all submit action was used
    if (!$formaction AND isset($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $delitem) {
            $delitem = COM_applyFilter($delitem);
            if (!empty ($type) && ($type <> 'story') && ($type <> 'draft')) {
                // There may be some plugin specific processing that needs to
                // happen first.
                $retval .= PLG_deleteSubmission($type, $delitem);
            }
            if ($type == 'draft') {
                STORY_deleteStory($delitem);
            } else {
                DB_delete($submissiontable,"$id",$delitem);
            }
        }
    }

    $retval .= MODERATE_submissions(SEC_createToken());

    return $retval;
}

/**
* Moderate user submissions
*
* Users from the user submission queue are either appoved (an email containing
* the password is sent out) or deleted.
*
* @param    int     $uid        Array of items
* @param    array   $action     Action to perform ('delete', 'approve')
* @param    int     $count      Number of items
* @return   string              HTML for "command and control" page
*
*/
function MODERATE_users($uid, $action, $count)
{
    global $_CONF, $_TABLES, $LANG04;

    $retval = '';

    // Set true if an valid action other then delete_all is selected
    $formaction = false;

    for ($i = 0; $i < $count; $i++) {
        if (isset($action[$i]) AND ($action[$i] != '')) {
            $formaction = true;
        } else {
            continue;
        }

        switch ($action[$i]) {
            case 'delete': // Ok, delete everything related to this user
                if ($uid[$i] > 1) {
                    USER_deleteAccount ($uid[$i]);
                }
                break;

            case 'approve':
                $uid[$i] = COM_applyFilter($uid[$i], true);
                $result = DB_query ("SELECT email,username, uid FROM {$_TABLES['users']} WHERE uid = $uid[$i]");
                $nrows = DB_numRows($result);
                if ($nrows == 1) {
                    $A = DB_fetchArray($result);
                    if ( $_CONF['registration_type'] == 1 ) {
                        $sql = "UPDATE {$_TABLES['users']} SET status=".USER_ACCOUNT_AWAITING_VERIFICATION." WHERE uid={$A['uid']}";
                    } else {
                        $sql = "UPDATE {$_TABLES['users']} SET status=".USER_ACCOUNT_AWAITING_ACTIVATION." WHERE uid={$A['uid']}";
                    }
                    DB_query($sql);
                    USER_createAndSendPassword ($A['username'], $A['email'], $A['uid']);
                }
                break;
        }
    }

    // Check if there was no direct action used on the form
    // and if the delete_all submit action was used
    if (!$formaction AND isset($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $del_uid) {
            $del_uid = COM_applyFilter($del_uid,true);
            if ($del_uid > 1) {
                USER_deleteAccount ($del_uid);
            }
        }
    }

    $retval .= MODERATE_submissions(SEC_createToken());

    return $retval;
}

// MAIN ========================================================================

$display = '';

$display .= COM_siteHeader ('menu', $LANG01[10]);

$msg = 0;
if (isset($_GET['msg'])) {
    $msg = COM_applyFilter($_GET['msg'], true);
}

if ($msg > 0) {
    $plugin = '';
    if (isset($_GET['plugin'])) {
        $plugin = COM_applyFilter($_GET['plugin']);
    }
    $display .= COM_showMessage($msg, $plugin);
}

if (isset ($_POST['mode']) && ($_POST['mode'] == 'moderation') && SEC_checkToken()) {
    $action = array();
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    }
    if ($_POST['type'] == 'user') {
        $display .= MODERATE_users($_POST['id'], $action,
                                  COM_applyFilter($_POST['count'], true));
    } else {
        $display .= MODERATE_items($_POST['id'], $action, $_POST['type'],
                               COM_applyFilter ($_POST['count'], true));
    }
} else {
    $display .= MODERATE_submissions(SEC_createToken());
}

$display .= COM_siteFooter();

echo $display;

?>