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
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
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

function MODERATE_submissions()
{
    global $_CONF, $LANG01, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $token = SEC_createToken();

    $userlist = (SEC_hasRights ('user.edit') &&
                 SEC_hasRights ('user.delete') &&
                 ($_CONF['usersubmission'] == 1)) ? MODERATE_userList($token) : '';
    $storylist = (SEC_hasRights('story.moderate')) ? MODERATE_itemList('story', $token) : '';
    $draftlist = (SEC_hasRights('story.edit') &&
                       ($_CONF['listdraftstories'] == 1)) ? MODERATE_draftList($token) : '';
    $pluginlist = PLG_showModerationList($token);
    $moderationlist = $userlist . $storylist . $draftlist . $pluginlist;

    $retval  = COM_startBlock($LANG01[10],'', COM_getBlockTemplate('_admin_block', 'header'));

    $menu_arr = array(
            array('url' => $_CONF['site_admin_url'],
                  'text' => $LANG_ADMIN['admin_home']),
    );

    $retval .= ADMIN_createMenu($menu_arr, $LANG29['info'],
                                $_CONF['layout_url'] . '/images/icons/moderation.'. $_IMAGE_TYPE);

    $retval .= (!empty($moderationlist)) ? $moderationlist : '<br ' . XHTML . '><p>' . $LANG29[39] . '</p>';

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
 * used for the lists of submissions and draft stories in admin/moderation.php
 *
 */
function MODERATE_getListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    $type = '';
    if (isset ($A['_type_'])) {
        $type = $A['_type_'];
    }

    $field = $fieldname;
    $field = ($type == 'user' && $fieldname == 1) ? 'user' : $field;
    $field = ($type == 'story' && $fieldname == 2) ? 'day' : $field;
    $field = ($type == 'story' && $fieldname == 3) ? 'tid' : $field;
    $field = ($type <> 'user' && $fieldname == 4) ? 'uid' : $field;

    switch ($field) {

        case 'edit':
            $retval = COM_createLink($icon_arr['edit'], $A['edit']);
            break;

        case 'user':
            $retval =  '<img src="' . $_CONF['layout_url']
            . '/images/admin/user.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;"' . XHTML . '>&nbsp;' . $fieldvalue;
            break;

        case 'day':
            $retval = strftime($_CONF['daytime'], $A['day']);
            break;

        case 'tid':
            $retval = DB_getItem($_TABLES['topics'], 'topic',
                                 "tid = '".DB_escapeString($A['tid'])."'");
            break;

        case 'uid':
            $username = DB_getItem($_TABLES['users'], 'username',
                                   "uid = ".intval($A['uid']));
            if ($A['uid'] == 1) {
                $retval = $username;
            } else {
                $attr['title'] = 'View Profile';
                $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'];
                $retval = COM_createLink($icon_arr['user'], $url, $attr);
                $retval .= '&nbsp;&nbsp;';
                $attr['style'] = 'vertical-align:top;';
                $retval .= COM_createLink($username, $url, $attr);
            }
            break;

        default:
            $retval = COM_makeClickableLinks(stripslashes($fieldvalue));
            break;
    }

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
    global $_CONF, $_TABLES, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';
    $sql = "SELECT uid as id,username,fullname,email FROM {$_TABLES['users']} WHERE status = 2";
    $result = DB_query ($sql);
    $nrows = DB_numRows($result);

    if ($nrows > 0) {
        $data_arr = array();
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            $A['edit'] = $_CONF['site_admin_url'].'/user.php?edit=x&amp;uid='.$A['id'];
            $A['row'] = $i;
            $A['fullname'] = stripslashes($A['fullname']);
            $A['email'] = stripslashes($A['email']);
            $A['_type_'] = 'user';
            $data_arr[$i] = $A;
        }

        $header_arr = array(
            array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
            array('text' => $LANG29[16], 'field' => 1),
            array('text' => $LANG29[17], 'field' => 2),
            array('text' => $LANG29[18], 'field' => 3, 'width' => '30%'),
        );

        $text_arr = array('has_menu'  => false,
                          'title'     => $LANG29[40],
                          'help_url'  => 'ccusersubmission.html',
                          'no_data'   => '',
                          'form_url'  => "{$_CONF['site_admin_url']}/moderation.php"
        );

        $approve_action = '&nbsp;&nbsp;&nbsp;&nbsp;<input name="approve" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/accept.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;" title="' . $LANG29[44]
            . '" onclick="return confirm(\'' . $LANG29[45] . '\');"'
            . XHTML . '>&nbsp;' . $LANG29[1];

        $options = array('chkselect' => true,
                         'chkfield' => 'id',
                         'chkname' => 'selitem',
                         'chkminimum' => 0,
                         'chkall' => false,
                         'chkactions' => $approve_action
                         );

        $form_arr['bottom'] = '<input type="hidden" name="type" value="user"' . XHTML . '>' . LB
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"'. XHTML . '>' . LB
                . '<input type="hidden" name="moderation" value="x"' . XHTML . '>' . LB
                . '<input type="hidden" name="count" value="' . $nrows . '"' . XHTML . '>';

        $retval = ADMIN_simpleList('MODERATE_getListField', $header_arr,
                              $text_arr, $data_arr, $options, $form_arr);
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
    global $_CONF, $_TABLES, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';
    $isplugin = false;

    if ((strlen ($type) > 0) && ($type <> 'story')) {
        // we are being called back from a plugin, via PLG_showModerationList
        $function = 'plugin_itemlist_' . $type;
        if (function_exists ($function)) {
            $plugin = new Plugin();
            $plugin = $function($token);
            // if the plugin returns a string, it wants to control it's own
            // moderation.  as far as I can tell - no plugin has used this yet
            // it appears to be a feature that was added in glFusion 1.1.0rc1
            // but never actually used
            if (is_string($plugin) && !empty($plugin)) {
                return '<div class="block-box">' . $plugin . '</div>';
            // otherwise this is a plugin object (historical approach)
            } elseif (is_object($plugin)) {
                $helpfile = $plugin->submissionhelpfile;
                $sql = $plugin->getsubmissionssql;
                $H = $plugin->submissionheading;
                $section_title = $plugin->submissionlabel;
                $section_help = $helpfile;
                $isplugin = true;
            }
        }
    } else {
        // it's a story submission (this needs to move into the story plugin)
        $sql = "SELECT sid AS id,title,UNIX_TIMESTAMP(date) AS day,tid,uid FROM {$_TABLES['storysubmission']}" . COM_getTopicSQL ('WHERE') . " ORDER BY date ASC";
        $H =  array($LANG29[10],$LANG29[14],$LANG29[15],$LANG29[46],);
        $section_title = $LANG29[35];
        $section_help = 'ccstorysubmission.html';
    }

    // the first 4 columns default to Title, Date, User and Topic unless otherwise
    // specified.  not sure I like this approach - but whatever - it's not
    // breaking anything at the momemnt
    if ( !isset($H[0]) || empty($H[0]) ) {
        $H[0] = $LANG29[10];
    }
    if ( !isset($H[1]) || empty($H[0]) ) {
        $H[1] = $LANG29[14];
    }
    if ( !isset($H[2]) || empty($H[2]) ) {
        $H[2] = $LANG29[15];
    }
    if ( !isset($H[3]) || empty($H[3]) ) {
        $H[3] = $LANG29[46];
    }

    // run SQL but this time ignore any errors.  note that the max number of items
    // that can be modified by type is limited to 50
    if (!empty($sql)) {
        $sql .= ' LIMIT 50'; // quick'n'dirty workaround to prevent timeouts
        $result = DB_query($sql, 1);
    }

    if (empty($sql) || DB_error()) {
        $nrows = 0; // more than likely a plugin that doesn't need moderation
    } else {
        $nrows = DB_numRows($result);
    }

    if ($nrows > 0) {
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
            $A['_type_'] = $type;
            $data_arr[$i] = $A;
        }

        $header_arr = array(      // display 'text' and use table field 'field'
            array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
            array('text' => $H[0], 'field' => 1),
            array('text' => $H[1], 'field' => 2, 'align' => 'center', 'width' => '15%'),
            array('text' => $H[2], 'field' => 3, 'width' => '30%'),
            array('text' => $H[3], 'field' => 4, 'width' => '15%'),
        );

        $text_arr = array('has_menu'    => false,
                          'title'       => $section_title,
                          'help_url'    => $section_help,
                          'no_data'   => $LANG29[39],
                          'form_url'  => "{$_CONF['site_admin_url']}/moderation.php"
        );

        $approve_action = '&nbsp;&nbsp;&nbsp;&nbsp;<input name="approve" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/accept.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;" title="' . $LANG29[44]
            . '" onclick="return confirm(\'' . $LANG29[45] . '\');"'
            . XHTML . '>&nbsp;' . $LANG29[1];

        $options = array('chkselect' => true,
                         'chkfield' => 'id',
                         'chkname' => 'selitem',
                         'chkminimum' => 0,
                         'chkall' => false,
                         'chkactions' => $approve_action,
                         );

        $form_arr['bottom'] = '<input type="hidden" name="type" value="' . $type . '"' . XHTML . '>' . LB
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"'. XHTML . '>' . LB
                . '<input type="hidden" name="moderation" value="x"' . XHTML . '>' . LB
                . '<input type="hidden" name="count" value="' . $nrows . '"' . XHTML . '>';

        $retval .= ADMIN_simpleList('MODERATE_getListField', $header_arr,
                                  $text_arr, $data_arr, $options, $form_arr);
    }

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
    global $_CONF, $_TABLES, $LANG24, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    $result = DB_query ("SELECT sid AS id,title,UNIX_TIMESTAMP(date) AS day,tid,uid FROM {$_TABLES['stories']} WHERE (draft_flag = 1)" . COM_getTopicSQL ('AND') . COM_getPermSQL ('AND', 0, 3) . " ORDER BY date ASC");
    $nrows = DB_numRows($result);

    if ($nrows > 0) {
        $data_arr = array();
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            $A['edit'] = $_CONF['site_admin_url'] . '/story.php?edit=x&amp;sid='
                        . $A['id'];
            $A['row'] = $i;
            $A['title'] = stripslashes($A['title']);
            $A['tid'] = stripslashes($A['tid']);
            $A['_type_'] = 'draft';
            $data_arr[$i] = $A;
        }

        $header_arr = array(
            array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
            array('text' => $LANG29[10], 'field' => 'title'),
            array('text' => $LANG29[14], 'field' => 'day', 'align' => 'center', 'width' => '15%'),
            array('text' => $LANG29[15], 'field' => 'tid', 'width' => '30%'),
            array('text' => $LANG29[46], 'field' => 'uid', 'width' => '15%'),
            );

        $text_arr = array('has_menu'  => false,
                          'title'     => $LANG29[35] . ' (' . $LANG24[34] . ')',
                          'help_url'  => '',
                          'no_data'   => $LANG29[39],
                          'form_url'  => "{$_CONF['site_admin_url']}/moderation.php");

        $approve_action = '&nbsp;&nbsp;&nbsp;&nbsp;<input name="approve" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/accept.' . $_IMAGE_TYPE
            . '" style="vertical-align:bottom;" title="' . $LANG29[44]
            . '" onclick="return confirm(\'' . $LANG29[45] . '\');"'
            . XHTML . '>&nbsp;' . $LANG29[1];

        $options = array('chkselect' => true,
                         'chkfield' => 'id',
                         'chkname' => 'selitem',
                         'chkminimum' => 0,
                         'chkall' => false,
                         'chkactions' => $approve_action,
                         );

        $form_arr['bottom'] = '<input type="hidden" name="type" value="draft"' . XHTML . '>' . LB
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"'. XHTML . '>' . LB
                . '<input type="hidden" name="count" value="' . $nrows . '"' . XHTML . '>';

        $retval .= ADMIN_simpleList('MODERATE_getListField', $header_arr,
                                  $text_arr, $data_arr, $options, $form_arr);
    }

    return $retval;
}

/**
* Moderates an item
*
* This will actually perform moderation (approve or delete) one or more items
*
* @param    string  $type       Type of items ('story', 'user', etc.)
* @param    string  $action     Action to perform ('delete' or 'approve')
* @return   string              HTML for "command and control" page
*
*/
function MODERATE_items($type='', $action='')
{
    global $_CONF, $_TABLES;

    $retval = '';

    switch ($type) {

        case 'user':
            $id = 'uid';
            $fields = 'email,username,uid';
            $table = $_TABLES['users'];
            break;

        case 'story':
            $id = 'sid';
            $table = $_TABLES['stories'];
            $fields = 'sid,uid,tid,title,introtext,date,postmode';
            $submissiontable = $_TABLES['storysubmission'];
            break;

        case 'draft':
            $id = 'sid';
            $table = $_TABLES['stories'];
            break;

        default:
            if (empty($type)) {
                // null item type
                $retval .= COM_errorLog("Submissions Error: An attempt was made to moderate a null item type.");
                return $retval;
            } else {
                list($id, $table, $fields, $submissiontable) = PLG_getModerationValues($type);
                if (empty($id)) {
                    $retval .= COM_errorLog("Submissions Error: A request was made to moderate an item of unknown type: $type");
                    return $retval;
                }
            }
    }

    $item_list = array();
    if (isset($_POST['selitem'])) {
        $item_list = $_POST['selitem'];
    }

    if (isset($item_list) AND is_array($item_list)) {
        foreach($item_list as $selitem) {
            $item_id = COM_applyFilter($selitem);
            if (empty($item_id)) {
                $retval .= COM_errorLog("Submissions error: a null item id was specified for action: $action, type: $type");
                return $retval;
            }

            switch ($action) {

                case 'delete':

                    switch ($type) {

                        case 'user':
                            // user
                            if ($item_id > 1) {
                               USER_deleteAccount ($item_id);
                            }
                            break;

                        case 'story':
                            // story (needs to move to a plugin)
                            DB_delete($submissiontable,"$id",$item_id);
                            break;

                        case 'draft':
                            // draft story
                            STORY_deleteStory($item_id);
                            break;

                        default:
                            // plugin
                            $retval .= PLG_deleteSubmission($type, $item_id);
                            DB_delete($submissiontable,"$id",$item_id);
                            break;
                    }

                    break;


                case 'approve':

                    switch ($type) {

                        case 'story':
                            // story (needs to move to a plugin)
                            $result = DB_query("SELECT * FROM $submissiontable WHERE $id = '$item_id'");
                            $A = DB_fetchArray($result);
                            $A['related'] = DB_escapeString(implode("\n", STORY_extractLinks($A['introtext'])));
                            $A['owner_id'] = $A['uid'];
                            $A['title'] = DB_escapeString($A['title']);
                            $A['introtext'] = DB_escapeString($A['introtext']);
                            $A['bodytext'] = DB_escapeString( $A['bodytext'] );
                            $result = DB_query("SELECT group_id,perm_owner,perm_group,perm_members,perm_anon,archive_flag FROM {$_TABLES['topics']} WHERE tid = '{$A['tid']}'");
                            $T = DB_fetchArray($result);
                            if ($T['archive_flag'] == 1) {
                                $frontpage = 0;
                            } else if (isset ($_CONF['frontpage'])) {
                                $frontpage = $_CONF['frontpage'];
                            } else {
                                $frontpage = 1;
                            }
                            DB_save ($table,'sid,uid,tid,title,introtext,bodytext,related,date,show_topic_icon,commentcode,trackbackcode,postmode,frontpage,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon',
                            "'{$A['sid']}',{$A['uid']},'{$A['tid']}','{$A['title']}','{$A['introtext']}','{$A['bodytext']}','{$A['related']}','{$A['date']}','{$_CONF['show_topic_icon']}','{$_CONF['comment_code']}','{$_CONF['trackback_code']}','{$A['postmode']}',$frontpage,{$A['owner_id']},{$T['group_id']},{$T['perm_owner']},{$T['perm_group']},{$T['perm_members']},{$T['perm_anon']}");
                            DB_delete($submissiontable,"$id",$item_id);
                            PLG_itemSaved($A['sid'], 'article');
                            COM_rdfUpToDateCheck();
                            COM_olderStuff();
                            break;

                        case 'draft':
                            // draft story
                            DB_query("UPDATE $table SET draft_flag = 0 WHERE $id = '$item_id'");
                            COM_rdfUpToDateCheck();
                            COM_olderStuff();
                            break;

                        case 'user':
                            // user
                            $result = DB_query("SELECT $fields FROM $table WHERE $id = '$item_id'");
                            $nrows = DB_numRows($result);
                            if ($nrows == 1) {
                                $A = DB_fetchArray($result);
                                if ( $_CONF['registration_type'] == 1 ) {
                                    $sql = "UPDATE $table SET status=".USER_ACCOUNT_AWAITING_VERIFICATION." WHERE $id = '{$A['uid']}'";
                                } else {
                                    $sql = "UPDATE $table SET status=".USER_ACCOUNT_AWAITING_ACTIVATION." WHERE $id = '{$A['uid']}'";
                                }
                                DB_query($sql);
                                USER_createAndSendPassword($A['username'], $A['email'], $A['uid']);
                            }
                            break;

                        default:
                            // plugin
                            DB_copy($table,$fields,$fields,$submissiontable,$id,$item_id);
                            $retval .= PLG_approveSubmission($type,$item_id);
                            break;
                    }

                    break;

            }
        }
    }

    return $retval;
}

// MAIN ========================================================================

$display = '';

$action = '';
$expected = array('delbutton_x', 'approve_x');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$type = (isset($_POST['type'])) ? COM_applyFilter($_POST['type']) : '';

$validtoken = SEC_checkToken();

$display .= COM_siteHeader ('menu', $LANG01[10]);

switch ($action) {

    case 'delbutton_x':
        if ($validtoken) {
            $retval .= MODERATE_items($type, 'delete');
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to illegally moderate a submission and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'approve_x':
        if ($validtoken) {
            $retval .= MODERATE_items($type, 'approve');
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to illegally moderate a submission and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;
}

$msg = (isset($_GET['msg'])) ? COM_applyFilter($_GET['msg'], true) : 0;
$plugin = (isset($_GET['plugin'])) ? COM_applyFilter($_GET['plugin']) : '';
$display .= ($msg > 0) ? COM_showMessage($msg, $plugin) : '';

$display .= MODERATE_submissions();

$display .= COM_siteFooter();

echo $display;

?>
