<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | story.php                                                                |
// |                                                                          |
// | glFusion story administration page.                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

/**
* This is the glFusion story administration page.
*
* @author   Jason Whittenburg
* @author   Tony Bibbs <tony AT tonybibbs DOT com>
*
*/

/**
* glFusion common function library
*/
require_once '../lib-common.php';
require_once 'auth.inc.php';

USES_lib_story();

$_STORY_VERBOSE = false; // verbose logging option

$display = '';

if (!SEC_hasRights('story.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[31],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog("User {$_USER['username']} tried to illegally access the story administration screen.");
    echo $display;
    exit;
}


/**
 * used for the list of stories in admin/story.php
 *
 */
function STORY_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG24, $LANG_ACCESS, $_IMAGE_TYPE;

    static $topics;

    if (!isset ($topics)) {
        $topics = array ();
    }

    $retval = '';

    switch($fieldname) {

        case "access":
        case "edit":
        case "edit_adv":
            if ( SEC_inGroup('Story Admin') ) {
                $access = $LANG_ACCESS['edit'];
            } else {
                $access = SEC_hasAccess ($A['owner_id'], $A['group_id'],
                                         $A['perm_owner'], $A['perm_group'],
                                         $A['perm_members'], $A['perm_anon']);
                if ($access == 3) {
                    if (SEC_hasTopicAccess ($A['tid']) == 3) {
                        $access = $LANG_ACCESS['edit'];
                    } else {
                        $access = $LANG_ACCESS['readonly'];
                    }
                } else {
                    $access = $LANG_ACCESS['readonly'];
                }
            }
            if ($fieldname == 'access') {
                $retval = $access;
            } else if ($access == $LANG_ACCESS['edit']) {
                if ($fieldname == 'edit_adv' || $fieldname == 'edit') {
                    $retval = COM_createLink($icon_arr['edit'],
                        "{$_CONF['site_admin_url']}/story.php?edit=x&amp;sid={$A['sid']}");
                }
            }
            break;

        case "copy":
        case "copy_adv":
            if ( SEC_inGroup('Story Admin') ) {
                $access = $LANG_ACCESS['copy'];
            } else {
                $access = SEC_hasAccess ($A['owner_id'], $A['group_id'],
                                         $A['perm_owner'], $A['perm_group'],
                                         $A['perm_members'], $A['perm_anon']);
                if ($access == 3) {
                    if (SEC_hasTopicAccess ($A['tid']) == 3) {
                        $access = $LANG_ACCESS['copy'];
                    } else {
                        $access = $LANG_ACCESS['readonly'];
                    }
                } else {
                    $access = $LANG_ACCESS['readonly'];
                }
            }
            if ($fieldname == 'access') {
                $retval = $access;
            } else if ($access == $LANG_ACCESS['copy']) {
                if ($fieldname == 'copy_adv') {
                    $retval = COM_createLink($icon_arr['copy'],
                        "{$_CONF['site_admin_url']}/story.php?clone=x&amp;editor=adv&amp;sid={$A['sid']}");
                } else if ($fieldname == 'copy') {
                    $retval = COM_createLink($icon_arr['copy'],
                        "{$_CONF['site_admin_url']}/story.php?clone=x&amp;editor=std&amp;sid={$A['sid']}");
                }
            }
            break;

        case "title":
            $A['title'] = str_replace('$', '&#36;', $A['title']);
            $article_url = COM_buildUrl ($_CONF['site_url'] . '/article.php?story='
                                  . $A['sid']);
            $retval = COM_createLink($A['title'], $article_url);
            break;

        case 'tid':
            if (!isset ($topics[$A['tid']])) {
                $topics[$A['tid']] = DB_getItem ($_TABLES['topics'], 'topic',"tid = '".DB_escapeString($A['tid'])."'");
            }
            $retval = $topics[$A['tid']];
            break;

        case "draft_flag":
            $retval = ($A['draft_flag'] == 1) ? $icon_arr['check'] : '';
            break;

        case "featured":
            $retval = ($A['featured'] == 1) ? $icon_arr['check'] : '';
            break;

        case 'username':
            $retval = COM_getDisplayName ($A['uid'], $A['username'], $A['fullname']);
            break;

        case "unixdate":
            $dt = new Date($A['unixdate'],$_USER['tzid']);
            $retval = $dt->format($_CONF['daytime'],true);
            break;

        case "ping":
            $pingico = '<img src="' . $_CONF['layout_url'] . '/images/sendping.'
                     . $_IMAGE_TYPE . '" alt="' . $LANG24[21] . '" title="'
                     . $LANG24[21] . '"/>';
            if (($A['draft_flag'] == 0) && ($A['unixdate'] < time())) {
                $url = $_CONF['site_admin_url']
                     . '/trackback.php?mode=sendall&amp;id=' . $A['sid'];
                $retval = COM_createLink($pingico, $url);
            } else {
                $retval = '';
            }
            break;

        case 'delete':
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return confirm(\'' . $LANG24[89] .'\');';
            $retval .= COM_createLink($icon_arr['delete'],
                $_CONF['site_admin_url'] . '/story.php'
                . '?deletestory=x&amp;sid=' . $A['sid'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

function STORY_global($errorMsg = '')
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE,
           $LANG09, $LANG_ADMIN, $LANG_ACCESS, $LANG24;

    USES_lib_admin();

    if ( !SEC_inGroup('Root')) COM_refresh($_CONF['site_url']);

    $retval = '';

    // Load HTML templates
    $T = new Template($_CONF['path_layout'] . 'admin/story');
    $T->set_file(array('page' => 'storyglobal.thtml'));

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/story.php',
              'text' => $LANG_ADMIN['story_list']),
       array('url' => $_CONF['site_admin_url'] . '/story.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
//        array('url' => $_CONF['site_admin_url'] . '/moderation.php',
//              'text' => $LANG_ADMIN['submissions']),
        array('url' => $_CONF['site_admin_url'] . '/story.php?global=x',
                      'text' => $LANG24[111],'active'=>true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home']),
    );

    $T->set_var('block_start',COM_startBlock($LANG24[100], '',
                              COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var ('admin_menu',
        ADMIN_createMenu($menu_arr, $LANG24[99],$_CONF['layout_url'] . '/images/icons/story.' . $_IMAGE_TYPE));

    $T->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    if ( $errorMsg != '' ) {
        $T->set_var('error_message',$errorMsg);
    }

    $current_topic = $LANG09[9];

    $seltopics = COM_topicList ('tid,topic', '', 1, true);
    $alltopics = '<option value="'.$LANG09[9].'"';
    if ($current_topic == 'all') {
        $alltopics .= ' selected="selected"';
    }
    $alltopics .= '>' .$LANG09[9]. '</option>' . LB;
    $filter = $LANG_ADMIN['topic']
        . ': <select name="tid">'
        . $alltopics . $seltopics . '</select>';

    $move_to_topic_list = $seltopics;

    $sec_token_name = CSRF_TOKEN;
    $sec_token = SEC_createToken();

    $T->set_var(array(
                'topiclist' => $filter,
                'owner_dropdown' => COM_buildOwnerList('owner_id',''),
                'group_dropdown' => SEC_getGroupDropdown ('', 3),
                'frontpage_options' => COM_optionList ($_TABLES['frontpagecodes'], 'code,name',''),
                'comment_options' => COM_optionList ($_TABLES['commentcodes'], 'code,name',''),
                'trackback_options' => COM_optionList ($_TABLES['trackbackcodes'], 'code,name',''),
                'move_to_topic_list' => $move_to_topic_list,
                'lang_show_topic_icon' => $LANG24[56],
                'lang_group' => $LANG_ACCESS['group'],
                'lang_topic' => $LANG_ADMIN['topic'],
                'lang_owner' => $LANG_ACCESS['owner'],
                'lang_comments' => $LANG24[19],
                'lang_trackbacks' => $LANG24[29],
                'lang_display' => $LANG24[93],
                'lang_move_to_topic' => $LANG24[112],
                'lang_move_to_topic_help' => $LANG24[113],
                'lang_confirm' => $LANG24[114],
                'lang_confirm_confirm' => $LANG24[115],
                'lang_save' => $LANG_ADMIN['save'],
                'lang_cancel' => $LANG_ADMIN['cancel'],
                'security_token' => $sec_token,
                'security_token_name' => $sec_token_name
    ));

    $T->parse('output','page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function STORY_global_save()
{
    global $_CONF, $_TABLES, $LANG09, $LANG24;

    if ( !SEC_inGroup('Root')) COM_refresh($_CONF['site_url']);

    $sql = '';
    $global_sql = '';
    $msg = '';

    if (!SEC_checkToken()) {
        COM_refresh($_CONF['site_url']);
    }

    $filter_topic = COM_applyFilter($_POST['tid']);
    $move_to_topic = COM_applyFilter($_POST['move_to_topic']);
    $on_frontpage = COM_applyFilter($_POST['frontpage'],true);
    $comment      = COM_applyFilter($_POST['comment'],true);
    $trackback    = COM_applyFilter($_POST['trackback'],true);
    $owner_id     = COM_applyFilter($_POST['owner_id'],true);
    $group_id     = COM_applyFilter($_POST['group_id'],true);
    $show_topic_icon = isset($_POST['show_topic_icon']) ? 1 : 0;

    if ( !isset($_POST['cb'])) return STORY_list();

    $active = $_POST['cb'];

    $comma = 0;

    if ( isset($active['frontpage'])) {
        if ( $comma == 1 ) {
            $sql .= ",";
        } else {
            $sql .= " SET ";
        }
        $sql .= "frontpage=".(int) $on_frontpage;
        $comma = 1;
        $msg .= $LANG24[116].'<br>';
    }

    if ( isset($active['comment'])) {
        if ( $comma == 1 ) {
            $sql .= ",";
        } else {
            $sql .= " SET ";
        }

        $sql .= "commentcode=".(int) $comment;
        $comma = 1;
        $msg .= $LANG24[117].'<br>';
    }

    if ( isset($active['trackback'])) {
        if ( $comma == 1 ) {
            $sql .= ",";
        } else {
            $sql .= " SET ";
        }

        $sql .= "trackbackcode=".(int) $trackback;
        $comma = 1;
        $msg .= $LANG24[118].'<br>';
    }

    if ( isset($active['owner'])) {
        if ( $comma == 1 ) {
            $sql .= ",";
        } else {
            $sql .= " SET ";
        }

        $sql .= "owner_id=".(int) $owner_id;
        $comma = 1;
        $msg .= $LANG24[119].'<br>';
    }

    if ( isset($active['group'])){
        if ( $comma == 1 ) {
            $sql .= ",";
        } else {
            $sql .= " SET ";
        }
        $sql .= "group_id=".(int) $group_id;
        $comma = 1;
        $msg .= $LANG24[120].'<br>';
    }

    if ( isset($active['show_topic_icon'])) {
        if ( $comma == 1 ) {
            $sql .= ",";
        } else {
            $sql .= " SET ";
        }
        $sql .= "show_topic_icon=".(int) $show_topic_icon;
        $comma = 1;
        $msg .= $LANG24[121].'<br>';
    }

    if ( $filter_topic != $LANG09[9] && $sql != '' ) {
        $sql .= " WHERE tid='".DB_escapeString($filter_topic)."'";
    }

    if ( $sql != '' ) {
        $global_sql = "UPDATE {$_TABLES['stories']} " . $sql;
    }

    if ( $global_sql != '' ) {
        DB_query($global_sql);
    }

    if ( isset($active['move_to_topic'])) {
        if ( ($filter_topic != $LANG09[9]) && ($filter_topic != $move_to_topic) ) {    // don't allow all
// reset the featured flag
            $sql = "UPDATE {$_TABLES['stories']} SET featured = 0 WHERE WHERE tid='".DB_escapeString($filter_topic)."'";
            DB_query($sql);
// move the stories
            $sql = "UPDATE {$_TABLES['stories']} SET tid='".DB_escapeString($move_to_topic)."' WHERE tid='".DB_escapeString($filter_topic)."'";
            DB_query($sql);
            $msg .= sprintf($LANG24[122],$filter_topic,$move_to_topic);
        } else {
            $msg .= $LANG24[123];
        }
    }

    COM_setMsg( $msg, 'error' );

    $_POST['tid'] = '';

    CTL_clearCache();

    return STORY_list();
}

function STORY_list()
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE,
           $LANG09, $LANG_ADMIN, $LANG_ACCESS, $LANG24;

    USES_lib_admin();

    $retval = '';

    $form = new Template($_CONF['path_layout'] .'admin/story/');
    $form->set_file('form', 'story_admin.thtml');


    if (!empty ($_GET['tid'])) {
        $current_topic = COM_applyFilter($_GET['tid']);
    } elseif (!empty ($_POST['tid'])) {
        $current_topic = COM_applyFilter($_POST['tid']);
    } elseif ( !empty($_GET['ptid'])) {
        $current_topic = COM_applyFilter($_GET['ptid']);
    } else {
        if ( SESS_isSet('story_admin_topic') ) {
            $current_topic = SESS_getVar('story_admin_topic');
        } else {
            $current_topic = $LANG09[9];
        }
    }
    SESS_setVar('story_admin_topic',$current_topic);

    if ($current_topic == $LANG09[9]) {
        $excludetopics = '';
        $seltopics = '';
        $topicsql = "SELECT tid,topic FROM {$_TABLES['topics']}" . COM_getPermSQL ();
        $tresult = DB_query( $topicsql );
        $trows = DB_numRows( $tresult );
        if( $trows > 0 ) {
            $excludetopics .= ' (';
            for( $i = 1; $i <= $trows; $i++ )  {
                $T = DB_fetchArray ($tresult);
                if ($i > 1)  {
                    $excludetopics .= ' OR ';
                }
                $excludetopics .= "tid = '{$T['tid']}'";
                $seltopics .= '<option value="' .$T['tid']. '"';
                if ($current_topic == "{$T['tid']}") {
                    $seltopics .= ' selected="selected"';
                }
                $seltopics .= '>' . $T['topic'] . ' (' . $T['tid'] . ')' . '</option>' . LB;
            }
            $excludetopics .= ') ';
        }
    } else {
        $excludetopics = " tid = '$current_topic' ";
        $seltopics = COM_topicList ('tid,topic', $current_topic, 1, true);
    }

    $alltopics = '<option value="' .$LANG09[9]. '"';
    if ($current_topic == $LANG09[9]) {
        $alltopics .= ' selected="selected"';
    }
    $alltopics .= '>' .$LANG09[9]. '</option>' . LB;
    $filter = $LANG_ADMIN['topic']
        . ': <select name="tid" style="width: 125px" onchange="this.form.submit()">'
        . $alltopics . $seltopics . '</select>';

    $header_arr = array();

    $header_arr[] = array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '35px');
    $header_arr[] = array('text' => $LANG_ADMIN['copy'], 'field' => 'copy', 'sort' => false, 'align' => 'center', 'width' => '35px');
    $header_arr[] = array('text' => $LANG_ADMIN['title'], 'field' => 'title', 'sort' => true);
    $header_arr[] = array('text' => $LANG_ADMIN['topic'], 'field' => 'tid', 'sort' => true);
    $header_arr[] = array('text' => $LANG_ACCESS['access'], 'field' => 'access', 'sort' => false, 'align' => 'center');
    $header_arr[] = array('text' => $LANG24[34], 'field' => 'draft_flag', 'sort' => true, 'align' => 'center');
    $header_arr[] = array('text' => $LANG24[32], 'field' => 'featured', 'sort' => true, 'align' => 'center');
    $header_arr[] = array('text' => $LANG24[7], 'field' => 'username', 'sort' => true); //author
    $header_arr[] = array('text' => $LANG24[15], 'field' => 'unixdate', 'sort' => true, 'align' => 'center'); //date
    if (SEC_hasRights ('story.ping') && ($_CONF['trackback_enabled'] ||
            $_CONF['pingback_enabled'] || $_CONF['ping_enabled'])) {
        $header_arr[] = array('text' => $LANG24[20], 'field' => 'ping', 'sort' => false, 'align' => 'center');
    }
    $header_arr[] = array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center');

    $defsort_arr = array('field' => 'unixdate', 'direction' => 'desc');

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/story.php',
              'text' => $LANG_ADMIN['story_list'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/story.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        );
        if ( SEC_inGroup('Root')) {
            $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/story.php?global=x',
                      'text' => $LANG24[111]);
        }
        $menu_arr[] = array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home']);

    $form->set_var('block_start',COM_startBlock($LANG24[22], '',
                              COM_getBlockTemplate('_admin_block', 'header')));

    $form->set_var('admin_menu',ADMIN_createMenu(
        $menu_arr,
        $LANG24[23],
        $_CONF['layout_url'] . '/images/icons/story.' . $_IMAGE_TYPE
    ));
    $text_arr = array(
        'has_extras' => true,
//        'form_url'   => $_CONF['site_admin_url'] . '/story.php?ptid='.urlencode($current_topic)
        'form_url'   => $_CONF['site_admin_url'] . '/story.php'
    );

    $sql = "SELECT {$_TABLES['stories']}.*, {$_TABLES['users']}.username, {$_TABLES['users']}.fullname, "
          ."UNIX_TIMESTAMP(date) AS unixdate  FROM {$_TABLES['stories']} "
          ."LEFT JOIN {$_TABLES['users']} ON {$_TABLES['stories']}.uid={$_TABLES['users']}.uid "
          ."WHERE 1=1 ";

    if (!empty ($excludetopics)) {
        $excludetopics = 'AND ' . $excludetopics;
    }
    $query_arr = array(
        'table' => 'stories',
        'sql' => $sql,
        'query_fields' => array('title', 'introtext', 'bodytext', 'sid', 'tid'),
        'default_filter' => $excludetopics . COM_getPermSQL ('AND')
    );

    $token = SEC_createToken();
    $form_arr = array(
        'bottom'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
    );

    $form->set_var('admin_list',ADMIN_list('story', 'STORY_getListField', $header_arr,
                          $text_arr, $query_arr, $defsort_arr, $filter, $token, '', $form_arr));
    $form->set_var('block_end',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $retval = $form->parse('output','form');

    return $retval;
}

/**
* Shows story editor
*
* Displays the story entry form
*
* @param    string      $sid            ID of story to edit
* @param    string      $action         'preview', 'edit', 'moderate', 'draft'
* @param    string      $errormsg       a message to display on top of the page
* @param    string      $currenttopic   topic selection for drop-down menu
* @return   string      HTML for story editor
*
*/
function STORY_edit($sid = '', $action = '', $errormsg = '', $currenttopic = '')
{
    global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG24, $LANG33, $LANG_ACCESS,
           $LANG_ADMIN, $MESSAGE,$_IMAGE_TYPE;

    USES_lib_admin();

    $display = '';
    $editStory = false;

    switch ($action) {
        case 'clone' :
        case 'edit':
        case 'preview':
        case 'error' :
            $title = $LANG24[5];
            $saveoption = $LANG_ADMIN['save'];
            $submission = false;
            break;
        case 'moderate':
            $title = $LANG24[90];
            $saveoption = $LANG_ADMIN['moderate'];
            $submission = true;
            break;
        case 'draft':
            $title = $LANG24[91];
            $saveoption = $LANG_ADMIN['save'];
            $submission = true;
            $action = 'edit';
            break;
        default :
            $title = $LANG24[5];
            $saveoption = $LANG_ADMIN['save'];
            $submission = false;
            $action = 'edit';
            break;
    }

    // Load HTML templates
    $story_templates = new Template($_CONF['path_layout'] . 'admin/story');
    $story_templates->set_file(array('editor' => 'storyeditor.thtml'));

    if (!isset ($_CONF['hour_mode'])) {
        $_CONF['hour_mode'] = 12;
    }

    if (!empty ($errormsg)) {
        $display .= COM_showMessageText($errormsg,$LANG24[25],true,'error');
    }

    if (!empty ($currenttopic)) {
        $allowed = DB_getItem ($_TABLES['topics'], 'tid',
                                "tid = '" . DB_escapeString ($currenttopic) . "'" .
                                COM_getTopicSql ('AND'));

        if ($allowed != $currenttopic) {
            $currenttopic = '';
        }
    }

    $story = new Story();
    if ($action == 'preview' || $action == 'error') {
        while (list($key, $value) = each($_POST)) {
            if (!is_array($value)) {
                $_POST[$key] = $value;
            } else {
                while (list($subkey, $subvalue) = each($value)) {
                    $value[$subkey] = $subvalue;
                }
            }
        }
        $result = $story->loadFromArgsArray($_POST);
    } else {
        $result = $story->loadFromDatabase($sid, $action);
    }

    if( ($result == STORY_PERMISSION_DENIED) || ($result == STORY_NO_ACCESS_PARAMS) ) {
        $display .= COM_showMessageText($LANG24[42],$LANG_ACCESS['accessdenied'],true,'error');
        COM_accessLog("User {$_USER['username']} tried to access story $sid. - STORY_PERMISSION_DENIED or STORY_NO_ACCESS_PARAMS - ".$result);
        return $display;
    } elseif( ($result == STORY_EDIT_DENIED) || ($result == STORY_EXISTING_NO_EDIT_PERMISSION) ) {
        $display .= COM_showMessageText($LANG24[41],$LANG_ACCESS['accessdenied'],true,'error');
        $display .= STORY_renderArticle ($story, 'p');
        COM_accessLog("User {$_USER['username']} tried to illegally edit story $sid. - STORY_EDIT_DENIED or STORY_EXISTING_NO_EDIT_PERMISSION");
        return $display;
    } elseif( $result == STORY_INVALID_SID ) {
        if ( $action == 'moderate' ) {
            // that submission doesn't seem to be there any more (may have been
            // handled by another Admin) - take us back to the moderation page
            echo COM_refresh( $_CONF['site_admin_url'] . '/moderation.php' );
        } else {
            echo COM_refresh( $_CONF['site_admin_url'] . '/story.php' );
        }
    } elseif( $result == STORY_DUPLICATE_SID) {
        $story_templates->set_var('error_message',$LANG24[24]);
    } elseif ( $result == STORY_EMPTY_REQUIRED_FIELDS ) {
        $story_templates->set_var('error_message',$LANG24[31]);
    }

    if(empty($currenttopic) && ($story->EditElements('tid') == '')) {
        $story->setTid( DB_getItem ($_TABLES['topics'], 'tid','is_default = 1' . COM_getPermSQL ('AND')));
    } else if ($story->EditElements('tid') == '') {
        $story->setTid($currenttopic);
    }
    if ( SEC_hasRights('story.edit') ) {
        $allowedTopicList = COM_topicList ('tid,topic', $story->EditElements('tid'), 1, true,0);
        $allowedAltTopicList = '<option value="">'.$LANG33[44].'</option>'.COM_topicList ('tid,topic', $story->EditElements('alternate_tid'), 1, true,0);
    } else {
        $allowedTopicList = COM_topicList ('tid,topic', $story->EditElements('tid'), 1, true,3);
        $allowedAltTopicList = '<option value="">'.$LANG33[44].'</option>'.COM_topicList ('tid,topic', $story->EditElements('alternate_tid'), 1, true,3);
    }
    if ( $allowedTopicList == '' ) {
        $display .= COM_showMessageText($LANG24[42],$LANG_ACCESS['accessdenied'],true,'error');
        COM_accessLog("User {$_USER['username']} tried to illegally access story $sid. No allowed topics.");
        return $display;
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/story.php',
              'text' => $LANG_ADMIN['story_list']),
        array('url' => $_CONF['site_admin_url'] . '/story.php?edit=x',
              'text' => $LANG24[5],'active'=>true),
        );
        if ( SEC_inGroup('Root')) {
            $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/story.php?global=x',
                      'text' => $LANG24[111]);
        }
        $menu_arr[] = array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home']);

    require_once $_CONF['path_system'] . 'classes/navbar.class.php';

    $story_templates->set_var ('hour_mode',      $_CONF['hour_mode']);

    if ($story->hasContent()) {
        $previewContent = STORY_renderArticle($story, 'p');
        if ($previewContent != '' ) {
            $story_templates->set_var('preview_content', $previewContent);
        }
    }

    $navbar = new navbar;
    if (!empty ($previewContent)) {
        $navbar->add_menuitem($LANG24[79],'showhideEditorDiv("preview",0);return false;',true);
        $navbar->add_menuitem($LANG24[80],'showhideEditorDiv("editor",1);return false;',true);
        $navbar->add_menuitem($LANG24[81],'showhideEditorDiv("publish",2);return false;',true);
        $navbar->add_menuitem($LANG24[82],'showhideEditorDiv("images",3);return false;',true);
        $navbar->add_menuitem($LANG24[83],'showhideEditorDiv("archive",4);return false;',true);
        $navbar->add_menuitem($LANG24[84],'showhideEditorDiv("perms",5);return false;',true);
        $navbar->add_menuitem($LANG24[85],'showhideEditorDiv("all",6);return false;',true);
    }  else {
        $navbar->add_menuitem($LANG24[80],'showhideEditorDiv("editor",0);return false;',true);
        $navbar->add_menuitem($LANG24[81],'showhideEditorDiv("publish",1);return false;',true);
        $navbar->add_menuitem($LANG24[82],'showhideEditorDiv("images",2);return false;',true);
        $navbar->add_menuitem($LANG24[83],'showhideEditorDiv("archive",3);return false;',true);
        $navbar->add_menuitem($LANG24[84],'showhideEditorDiv("perms",4);return false;',true);
        $navbar->add_menuitem($LANG24[85],'showhideEditorDiv("all",5);return false;',true);
    }

    if ($action == 'preview') {
        $story_templates->set_var ('show_preview', '');
        $story_templates->set_var ('show_htmleditor', 'none');
        $story_templates->set_var ('show_texteditor', 'none');
        $story_templates->set_var ('show_submitoptions', 'none');
        $navbar->set_selected($LANG24[79]);
    } else {
        $navbar->set_selected($LANG24[80]);
        $story_templates->set_var ('show_preview', 'none');
    }
    $story_templates->set_var ('navbar', $navbar->generate() );

    $story_templates->set_var('start_block',COM_startBlock ($title, '',COM_getBlockTemplate ('_admin_block', 'header')));

    // start generating the story editor block
    $story_templates->set_var('block_start',COM_startBlock ($title, '',COM_getBlockTemplate ('_admin_block', 'header')));

    $oldsid = $story->EditElements('originalSid');
    if (!empty ($oldsid)) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="deletestory"%s/>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';

        $story_templates->set_var ('delete_option',
                                   sprintf ($delbutton, $jsconfirm));
        $story_templates->set_var ('delete_option_no_confirmation',
                                   sprintf ($delbutton, ''));
        $story_templates->set_var('lang_delete_confirm',$MESSAGE[76]);

    }
    if ($submission || ($story->type == 'submission')) {
        $story_templates->set_var ('submission_option',
                '<input type="hidden" name="type" value="submission"/>');
    }
    $story_templates->set_var ('admin_menu',    ADMIN_createMenu($menu_arr, $LANG24[92],$_CONF['layout_url'] . '/images/icons/story.' . $_IMAGE_TYPE));

    $story_templates->set_var ('lang_author', $LANG24[7]);
    $storyauthor = COM_getDisplayName ($story->EditElements('uid'));
    $storyauthor_select= COM_optionList($_TABLES['users'], 'uid,username',
            $story->EditElements('uid'));
    $story_templates->set_var ('story_author', $storyauthor);
    $story_templates->set_var ('story_author_select', $storyauthor_select);
    $story_templates->set_var ('author', $storyauthor);
    $story_templates->set_var ('story_uid', $story->EditElements('uid'));

    // user access info
    $story_templates->set_var('lang_accessrights',$LANG_ACCESS['accessrights']);
    $story_templates->set_var('lang_owner', $LANG_ACCESS['owner']);
    $ownername = COM_getDisplayName ($story->EditElements('owner_id'));
    $story_templates->set_var( 'owner_username', DB_getItem ($_TABLES['users'],
                              'username', 'uid = ' .
                              (int) $story->EditElements( 'owner_id' ) ) );
    $story_templates->set_var('owner_name', $ownername);
    $story_templates->set_var('owner', $ownername);
    $story_templates->set_var('owner_id', $story->EditElements('owner_id'));

    if ( SEC_hasRights('story.edit') ) {
        $story_templates->set_var('owner_dropdown',COM_buildOwnerList('owner_id',$story->EditElements('owner_id')));
    } else {
        $ownerInfo = '<input type="hidden" name="owner_id" value="'.$story->editElements('owner_id').'" />'.$ownername;
        $story_templates->set_var('owner_dropdown',$ownerInfo);
    }

    $story_templates->set_var('lang_group', $LANG_ACCESS['group']);

    if ( SEC_inGroup($story->EditElements('group_id'))) {
        $story_templates->set_var('group_dropdown',
                                  SEC_getGroupDropdown ($story->EditElements('group_id'), 3));
    } else {
        $gdrpdown = '<input type="hidden" name="group_id" value="'.$story->EditElements('group_id').'"/>';
        $grpddown .= DB_getItem($_TABLES['groups'],'grp_name','grp_id='.(int) $story->EditElements('group_id'));
        $story_templates->set_var('group_dropdown',$grpddown);
    }
    $story_templates->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $story_templates->set_var('lang_perm_key', $LANG_ACCESS['permissionskey']);
    $story_templates->set_var('permissions_editor', SEC_getPermissionsHTML(
    $story->EditElements('perm_owner'),$story->EditElements('perm_group'),
    $story->EditElements('perm_members'),$story->EditElements('perm_anon')));
    $story_templates->set_var('permissions_msg', $LANG_ACCESS['permmsg']);
    $curtime = COM_getUserDateTimeFormat($story->EditElements('date'));
    $story_templates->set_var('lang_date', $LANG24[15]);

    $story_templates->set_var('publish_second', $story->EditElements('publish_second'));

    $publish_ampm = '';
    $publish_hour = $story->EditElements('publish_hour');
    if ($publish_hour >= 12) {
        if ($publish_hour > 12) {
            $publish_hour = $publish_hour - 12;
        }
        $ampm = 'pm';
    } else {
        $ampm = 'am';
    }
    $ampm_select = COM_getAmPmFormSelection ('publish_ampm', $ampm);
    $story_templates->set_var ('publishampm_selection', $ampm_select);

    $month_options = COM_getMonthFormOptions($story->EditElements('publish_month'));
    $story_templates->set_var('publish_month_options', $month_options);

    $day_options = COM_getDayFormOptions($story->EditElements('publish_day'));
    $story_templates->set_var('publish_day_options', $day_options);

    $year_options = COM_getYearFormOptions($story->EditElements('publish_year'));
    $story_templates->set_var('publish_year_options', $year_options);

    if ($_CONF['hour_mode'] == 24) {
        $hour_options = COM_getHourFormOptions ($story->EditElements('publish_hour'), 24);
    } else {
        $hour_options = COM_getHourFormOptions ($publish_hour);
    }
    $story_templates->set_var('publish_hour_options', $hour_options);

    $minute_options = COM_getMinuteFormOptions($story->EditElements('publish_minute'));
    $story_templates->set_var('publish_minute_options', $minute_options);

    $story_templates->set_var('publish_date_explanation', $LANG24[46]);
    $story_templates->set_var('story_unixstamp', $story->EditElements('unixdate'));

    $story_templates->set_var('expire_second', $story->EditElements('expire_second'));

    $expire_ampm = '';
    $expire_hour = $story->EditElements('expire_hour');
    if ($expire_hour >= 12) {
        if ($expire_hour > 12) {
            $expire_hour = $expire_hour - 12;
        }
        $ampm = 'pm';
    } else {
        $ampm = 'am';
    }
    $ampm_select = COM_getAmPmFormSelection ('expire_ampm', $ampm);
    if (empty ($ampm_select)) {
        // have a hidden field to 24 hour mode to prevent JavaScript errors
        $ampm_select = '<input type="hidden" name="expire_ampm" value=""/>';
    }
    $story_templates->set_var ('expireampm_selection', $ampm_select);

    $month_options = COM_getMonthFormOptions($story->EditElements('expire_month'));
    $story_templates->set_var('expire_month_options', $month_options);

    $day_options = COM_getDayFormOptions($story->EditElements('expire_day'));
    $story_templates->set_var('expire_day_options', $day_options);

    $year_options = COM_getYearFormOptions($story->EditElements('expire_year'));
    $story_templates->set_var('expire_year_options', $year_options);

    if ($_CONF['hour_mode'] == 24) {
        $hour_options = COM_getHourFormOptions ($story->EditElements('expire_hour'), 24);
    } else {
        $hour_options = COM_getHourFormOptions ($expire_hour);
    }
    $story_templates->set_var('expire_hour_options', $hour_options);

    $minute_options = COM_getMinuteFormOptions($story->EditElements('expire_minute'));
    $story_templates->set_var('expire_minute_options', $minute_options);

    $story_templates->set_var('expire_date_explanation', $LANG24[46]);
    $story_templates->set_var('story_unixstamp', $story->EditElements('expirestamp'));
    if ($story->EditElements('statuscode') == STORY_ARCHIVE_ON_EXPIRE) {
        $story_templates->set_var('is_checked2', 'checked="checked"');
        $story_templates->set_var('is_checked3', 'checked="checked"');
        $story_templates->set_var('showarchivedisabled', 'false');
    } elseif ($story->EditElements('statuscode') == STORY_DELETE_ON_EXPIRE) {
        $story_templates->set_var('is_checked2', 'checked="checked"');
        $story_templates->set_var('is_checked4', 'checked="checked"');
        $story_templates->set_var('showarchivedisabled', 'false');
    } else {
        $story_templates->set_var('showarchivedisabled', 'true');
    }
    $story_templates->set_var('lang_archivetitle', $LANG24[58]);
    $story_templates->set_var('lang_option', $LANG24[59]);
    $story_templates->set_var('lang_enabled', $LANG_ADMIN['enabled']);
    $story_templates->set_var('lang_story_stats', $LANG24[87]);
    $story_templates->set_var('lang_optionarchive', $LANG24[61]);
    $story_templates->set_var('lang_optiondelete', $LANG24[62]);
    $story_templates->set_var('lang_title', $LANG_ADMIN['title']);

    $story_templates->set_var('story_title', $story->EditElements('title'));
    $story_templates->set_var('story_subtitle',$story->EditElements('subtitle'));
    $story_templates->set_var('lang_topic', $LANG_ADMIN['topic']);
    $story_templates->set_var('lang_alt_topic', $LANG_ADMIN['alt_topic']);

    $story_templates->set_var ('topic_options',$allowedTopicList);
    $story_templates->set_var ('alt_topic_options',$allowedAltTopicList);
    $story_templates->set_var('lang_show_topic_icon', $LANG24[56]);
    if ($story->EditElements('show_topic_icon') == 1) {
        $story_templates->set_var('show_topic_icon_checked', 'checked="checked"');
    } else {
        $story_templates->set_var('show_topic_icon_checked', '');
    }
    $story_templates->set_var('story_image_url',$story->EditElements('story_image'));
    $story_templates->set_var('story_video_url',$story->EditElements('story_video'));

    $story_templates->set_var('lang_draft', $LANG24[34]);
    if ($story->EditElements('draft_flag')) {
        $story_templates->set_var('is_checked', 'checked="checked"');
        $story_templates->set_var('unpublished_selected','selected="selected"');
    } else {
        $story_templates->set_var('published_selected','selected="selected"');
    }
    $story_templates->set_var ('lang_mode', $LANG24[3]);
    $story_templates->set_var ('status_options',
            COM_optionList ($_TABLES['statuscodes'], 'code,name',
                            $story->EditElements('statuscode')));
    $story_templates->set_var ('comment_options',
            COM_optionList ($_TABLES['commentcodes'], 'code,name',
                            $story->EditElements('commentcode')));
    $story_templates->set_var ('trackback_options',
            COM_optionList ($_TABLES['trackbackcodes'], 'code,name',
                            $story->EditElements('trackbackcode')));
    // comment expire
    $story_templates->set_var ('lang_cmt_disable', $LANG24[63]);
    if ($story->EditElements('cmt_close') ) {
        $story_templates->set_var('is_checked5', 'checked="checked"'); //check box if enabled
        $story_templates->set_var('showcmtclosedisabled', 'false');
    } else {
        $story_templates->set_var('showcmtclosedisabled', 'true');
    }

    $month_options = COM_getMonthFormOptions($story->EditElements('cmt_close_month'));
    $story_templates->set_var('cmt_close_month_options', $month_options);

    $day_options = COM_getDayFormOptions($story->EditElements('cmt_close_day'));
    $story_templates->set_var('cmt_close_day_options', $day_options);

    $year_options = COM_getYearFormOptions($story->EditElements('cmt_close_year'));
    $story_templates->set_var('cmt_close_year_options', $year_options);

    $cmt_close_ampm = '';
    $cmt_close_hour = $story->EditElements('cmt_close_hour');
    //correct hour
    if ($cmt_close_hour >= 12) {
        if ($cmt_close_hour > 12) {
            $cmt_close_hour = $cmt_close_hour - 12;
        }
        $ampm = 'pm';
    } else {
        $ampm = 'am';
    }
    $ampm_select = COM_getAmPmFormSelection ('cmt_close_ampm', $ampm);
    if (empty ($ampm_select)) {
        // have a hidden field to 24 hour mode to prevent JavaScript errors
        $ampm_select = '<input type="hidden" name="cmt_close_ampm" value="" />';
    }
    $story_templates->set_var ('cmt_close_ampm_selection', $ampm_select);

    if ($_CONF['hour_mode'] == 24) {
        $hour_options = COM_getHourFormOptions ($story->EditElements('cmt_close_hour'), 24);
    } else {
        $hour_options = COM_getHourFormOptions ($cmt_close_hour);
    }
    $story_templates->set_var('cmt_close_hour_options', $hour_options);

    $minute_options = COM_getMinuteFormOptions($story->EditElements('cmt_close_minute'));
    $story_templates->set_var('cmt_close_minute_options', $minute_options);

    $story_templates->set_var('cmt_close_second', $story->EditElements('cmt_close_second'));

    if (($_CONF['onlyrootfeatures'] == 1 && SEC_inGroup('Root'))
        or ($_CONF['onlyrootfeatures'] !== 1)) {
        $featured_options = "<select name=\"featured\">" . LB
                          . COM_optionList ($_TABLES['featurecodes'], 'code,name', $story->EditElements('featured'))
                          . "</select>" . LB;
        $featured_options_data =  COM_optionList ($_TABLES['featurecodes'], 'code,name', $story->EditElements('featured'));
        $story_templates->set_var('featured_options_data',$featured_options_data);
    } else {
        $featured_options = "<input type=\"hidden\" name=\"featured\" value=\"0\"/>";
        $story_templates->unset_var('featured_options_data');
    }
    $story_templates->set_var ('featured_options',$featured_options);
    $story_templates->set_var ('frontpage_options',
            COM_optionList ($_TABLES['frontpagecodes'], 'code,name',
                            $story->EditElements('frontpage')));

    $story_templates->set_var('story_introtext', $story->EditElements('introtext'));

    $story_templates->set_var('story_bodytext', $story->EditElements('bodytext'));
    $story_templates->set_var('lang_introtext', $LANG24[16]);
    $story_templates->set_var('lang_bodytext', $LANG24[17]);
    $story_templates->set_var('lang_postmode', $LANG24[4]);
    $story_templates->set_var('lang_publishoptions',$LANG24[76]);
    $story_templates->set_var('lang_publishdate', $LANG24[69]);
    $story_templates->set_var('lang_nojavascript',$LANG24[77]);
    $story_templates->set_var('postmode',$story->EditElements('postmode'));

    if ( $story->EditElements('postmode') == 'plaintext' || $story->EditElements('postmode') == 'text' ) {
        $allowedHTML = '';
    } else {
        $allowedHTML = COM_allowedHTML(SEC_getUserPermissions(),false,'glfusion','story') . '<br/>';
    }
    $allowedHTML .= COM_allowedAutotags(SEC_getUserPermissions(),false,'glfusion','story');
    $story_templates->set_var('lang_allowed_html', $allowedHTML);

    $fileinputs = '';
    $saved_images = '';
    if ($_CONF['maximagesperarticle'] > 0) {
        $story_templates->set_var('lang_images', $LANG24[47]);
        $icount = DB_count($_TABLES['article_images'],'ai_sid', DB_escapeString($story->getSid()));
        if ($icount > 0) {
            $result_articles = DB_query("SELECT * FROM {$_TABLES['article_images']} WHERE ai_sid = '".DB_escapeString($story->getSid())."'");
            for ($z = 1; $z <= $icount; $z++) {
                $I = DB_fetchArray($result_articles);
                $saved_images .= $z . ') '
                    . COM_createLink($I['ai_filename'],
                        $_CONF['site_url'] . '/images/articles/' . $I['ai_filename'])
                    . '&nbsp;&nbsp;&nbsp;' . $LANG_ADMIN['delete']
                    . ': <input type="checkbox" name="delete[' .$I['ai_img_num']
                    . ']" /><br />';
            }
        }

        $newallowed = $_CONF['maximagesperarticle'] - $icount;
        for ($z = $icount + 1; $z <= $_CONF['maximagesperarticle']; $z++) {
            $fileinputs .= $z . ') <input type="file" dir="ltr" name="file[]'
                        . '" />';
            if ($z < $_CONF['maximagesperarticle']) {
                $fileinputs .= '<br />';
            }
        }
        $fileinputs .= '<br />' . $LANG24[51];
        if ($_CONF['allow_user_scaling'] == 1) {
            $fileinputs .= $LANG24[27];
        }
        $fileinputs .= $LANG24[28] . '<br />';
    }
    $story_templates->set_var('saved_images', $saved_images);
    $story_templates->set_var('image_form_elements', $fileinputs);
    $story_templates->set_var('lang_hits', $LANG24[18]);
    $story_templates->set_var('story_hits', $story->EditElements('hits'));
    $story_templates->set_var('lang_comments', $LANG24[19]);
    $story_templates->set_var('story_comments', $story->EditElements('comments'));
    $story_templates->set_var('lang_trackbacks', $LANG24[29]);
    $story_templates->set_var('story_trackbacks', $story->EditElements('trackbacks'));
    $story_templates->set_var('lang_emails', $LANG24[39]);
    $story_templates->set_var('story_emails', $story->EditElements('numemails'));

    if ( $_CONF['rating_enabled'] ) {
        $rating = @number_format($story->EditElements('rating'),2);
        $votes  = $story->EditElements('votes');
        $story_templates->set_var('rating',$rating);
        $story_templates->set_var('votes',$votes);
    }

    $story_templates->set_var('attribution_url', $story->EditElements('attribution_url'));
    $story_templates->set_var('attribution_name', $story->EditElements('attribution_name'));
    $story_templates->set_var('attribution_author', $story->EditElements('attribution_author'));

    $story_templates->set_var('lang_attribution_url', $LANG24[105]);
    $story_templates->set_var('lang_attribution_name', $LANG24[106]);
    $story_templates->set_var('lang_attribution_author', $LANG24[107]);
    $story_templates->set_var('lang_attribution', $LANG24[108]);

    $sec_token_name = CSRF_TOKEN;
    $sec_token = SEC_createToken();
    $story_templates->set_var('story_id', $story->getSid());
    $story_templates->set_var('old_story_id', $story->EditElements('originalSid'));
    $story_templates->set_var('lang_sid', $LANG24[12]);
    $story_templates->set_var('lang_save', $saveoption);
    $story_templates->set_var('lang_preview', $LANG_ADMIN['preview']);
    $story_templates->set_var('lang_cancel', $LANG_ADMIN['cancel']);
    $story_templates->set_var('lang_delete', $LANG_ADMIN['delete']);
    $story_templates->set_var('lang_timeout', $LANG_ADMIN['timeout_msg']);
    $story_templates->set_var('gltoken_name', CSRF_TOKEN);
    $story_templates->set_var('gltoken', $sec_token);
    $story_templates->set_var('security_token',$sec_token);
    $story_templates->set_var('security_token_name',$sec_token_name);
    $story_templates->set_var('end_block',COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer')));

    PLG_templateSetVars('storyeditor',$story_templates);
    if ( $story->EditElements('postmode') != 'html' ) {
        $story_templates->unset_var('wysiwyg');
    }

    SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                   time() + 1200, $_CONF['cookie_path'],
                   $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);

    $story_templates->parse('output','editor');
    $display .= $story_templates->finish($story_templates->get_var('output'));

    return $display;
}

/**
* Saves story to database
*
* @param    string      $type           story submission or (new) story
* @param    string      $sid            ID of story to save
* @param    int         $uid            ID of user that wrote the story
* @param    string      $tid            Topic ID story belongs to
* @param    string      $title          Title of story
* @param    string      $introtext      Introduction text
* @param    string      $bodytext       Text of body
* @param    int         $hits           Number of times story has been viewed
* @param    string      $unixdate       Date story was originally saved
* @param    int         $featured       Flag on whether or not this is a featured article
* @param    string      $commentcode    Indicates if comments are allowed to be made to article
* @param    string      $trackbackcode  Indicates if trackbacks are allowed to be made to article
* @param    string      $statuscode     Status of the story
* @param    string      $postmode       Is this HTML or plain text?
* @param    string      $frontpage      Flag indicates if story will appear on front page and topic or just topic
* @param    int         $draft_flag     Flag indicates if story is a draft or not
* @param    int         $numemails      Number of times this story has been emailed to someone
* @param    int         $owner_id       ID of owner (not necessarily the author)
* @param    int         $group_id       ID of group story belongs to
* @param    int         $perm_owner     Permissions the owner has on story
* @param    int         $perm_group     Permissions the group has on story
* @param    int         $perm_member    Permissions members have on story
* @param    int         $perm_anon      Permissions anonymous users have on story
* @param    int         $delete         String array of attached images to delete from article
*
*/
function STORY_submit($type='')
{
    $output = '';

    $args = &$_POST;

    while (list($key, $value) = each($args)) {
        if (!is_array($value)) {
            $args[$key] = $value;
        } else {
            while (list($subkey, $subvalue) = each($value)) {
                $value[$subkey] = $subvalue;
            }
        }
    }

    /* ANY FURTHER PROCESSING on POST variables - COM_stripslashes etc.
     * Do it HERE on $args */

    $rc = PLG_invokeService('story', 'submit', $args, $output, $svc_msg);

    switch ( $rc ) {
        case PLG_RET_ERROR :
            break;
        case PLG_RET_PERMISSION_DENIED :
            break;
        case PLG_RET_PERMISSION_DENIED :
            break;
        case PLG_RET_PRECONDITION_FAILED :
            break;
        case PLG_RET_AUTH_FAILED :
            break;
    }

    return $output;
}

// MAIN ========================================================================

$pageTitle = '';
$pageBody = '';
$action = '';
$expected = array('edit','moderate','draft','clone','save','previewstory','deletestory','global','globalsave','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$sid = '';
if (isset($_POST['sid'])) {
    $sid = COM_applyFilter($_POST['sid']);
} elseif (isset($_GET['sid'])) {
    $sid = COM_applyFilter($_GET['sid']);
}

$editopt = '';
if (isset($_POST['editopt'])) {
    $editopt = COM_applyFilter($_POST['editopt']);
} elseif (isset($_GET['editopt'])) {
    $editopt = COM_applyFilter($_GET['editopt']);
}
$msg = COM_getMessage();

$topic = (isset($_GET['topic'])) ? COM_applyFilter($_GET['topic']) : '';
$type = (isset($_POST['type'])) ? COM_applyFilter($_POST['type']) : '';

switch ($action) {

    case 'edit':
    case 'moderate':
    case 'draft':
        switch ($action) {
            case 'edit':
                $blocktitle = $LANG24[5];
                break;
            case 'moderate':
                $blocktitle = $LANG24[90];
                break;
            case 'draft':
                $blocktitle = $LANG24[91];
                break;
        }
        $pageTitle = $blocktitle;
        $pageBody .= STORY_edit($sid, $action, '', $topic);
        break;

    case 'clone':
        if (!empty($sid)) {
            $pageTitle = $LANG24[5];
            $pageBody .= STORY_edit($sid, $action);
        } else {
            COM_errorLog('User ' . $_USER['username'] . ' attempted to clone a story, sid empty or null, sid=' . $sid);
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'save':
        // purge any tokens we created for the advanced editor
        DB_query("DELETE FROM {$_TABLES['tokens']} WHERE owner_id=".(int) $_USER['uid']." AND urlfor='advancededitor'",1);

        if (SEC_checkToken()) {
            $pageBody = STORY_submit();
        } else {
            $pageTitle = $LANG24[5];
            $pageBody .= COM_showMessage(501);
            $pageBody .= STORY_edit(COM_applyFilter ($_POST['sid']), 'preview');
        }
        break;

    case 'previewstory':
        $pageTitle = $LANG24[5];
        $pageBody .= STORY_edit($sid, 'preview');
        break;

    case 'deletestory':
        if (!isset($sid) || empty($sid)) {
            COM_errorLog('User ' . $_USER['username'] . ' attempted to delete a story, sid empty or null, sid=' . $sid);
            echo COM_refresh($_CONF['site_admin_url'] . '/story.php');
        } elseif ($type == 'submission') {
            $tid = DB_getItem($_TABLES['storysubmission'], 'tid', "sid = '".DB_escapeString($sid)."'");
            if (SEC_hasTopicAccess($tid) < 3) {
                COM_accessLog ('User ' . $_USER['username'] . ' had insufficient rights to delete a story submission, sid=' . $sid);
                echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
            } elseif (SEC_checkToken()) {
                DB_delete ($_TABLES['storysubmission'], 'sid', DB_escapeString($sid),
                           $_CONF['site_admin_url'] . '/moderation.php');
            } else {
                COM_accessLog ("User {$_USER['username']} tried to illegally delete a story submission, sid=$sid and failed CSRF checks.");
                echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
            }
        } else if (SEC_checkToken()) {
            $display .= STORY_deleteStory($sid);
        } else {
            COM_accessLog ("User {$_USER['username']} tried to a delete a story, sid=$sid and failed CSRF checks");
            $display = COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'global' :
        $pageBody .= STORY_global();
        break;

    case 'globalsave' :
        $pageBody .= STORY_global_save();
        break;

    default:
        // purge any tokens we created for the advanced editor
        DB_query("DELETE FROM {$_TABLES['tokens']} WHERE owner_id=".(int) $_USER['uid']." AND urlfor='advancededitor'",1);
        if (($action == 'cancel') && ($type == 'submission')) {
            echo COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        } else {
            $pageTitle = $LANG24[22];
            $pageBody .= STORY_list();
        }
        break;

}


$display = COM_siteHeader('menu',$pageTitle);
if(isset($msg)) {
    $display .= (is_numeric($msg)) ? COM_showMessage($msg) : COM_showMessageText( $msg );
}
$display .= $pageBody;
$display .= COM_siteFooter();

echo $display;

?>