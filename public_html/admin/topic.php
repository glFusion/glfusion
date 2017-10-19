<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | topic.php                                                                |
// |                                                                          |
// | glFusion topic administration page.                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
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

require_once '../lib-common.php';
require_once 'auth.inc.php';
USES_lib_story();

if (!SEC_hasRights('topic.edit')) {
    $display = COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[32],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog("User {$_USER['username']} tried to access the topic administration screen.");
    echo $display;
    exit;
}

/**
* Show topic administration form
*
* @param    string  tid     ID of topic to edit
* @param    array   $T      An array of topic fields (optional)
* @return   string          HTML for the topic editor
*
*/
function TOPIC_edit ($tid = '', $T = array(), $msg = '')
{
    global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG27, $LANG_ACCESS,
           $LANG_ADMIN, $MESSAGE, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';
    $topicEdit = 0;
    $assoc_stories_published = 0;
    $assoc_stories_draft = 0;
    $assoc_images = 0;
    $assoc_comments = 0;
    $assoc_trackbacks = 0;

    if (!empty($tid)) {
        $topicEdit = 1;
        // existing topic - pull fields from DB
        $result = DB_query("SELECT * FROM {$_TABLES['topics']} WHERE tid ='" . DB_escapeString($tid) . "'");
        $A = DB_fetchArray($result);
        $access = (SEC_inGroup('Topic Admin')) ? 3 : SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
        if ($access == 0 OR $access == 2) {
            $retval .= COM_showMessageText($LANG27[13],$LANG27[12],true,'error');
            COM_accessLog("User {$_USER['username']} tried to create or edit topic $tid.");
            return $retval;
        }

        // ok let's see what is associated with this topic

        $result2 = DB_query("SELECT bid FROM {$_TABLES['blocks']} WHERE tid = '$tid'");
        $assoc_blocks = DB_numRows($result2);

        $result2 = DB_query("SELECT fid FROM {$_TABLES['syndication']} WHERE topic = '$tid'");
        $assoc_feeds = DB_numRows($result2);

        $result2 = DB_query("SELECT sid FROM {$_TABLES['storysubmission']} WHERE tid = '$tid'");
        $assoc_stories_submitted = DB_numRows($result2);

        $result2 = DB_query("SELECT sid, draft_flag FROM {$_TABLES['stories']} WHERE tid = '$tid'");
        $total_assoc_stories = DB_numRows($result2);
        if ($total_assoc_stories > 0) {
            for ($i = 0; $i < $total_assoc_stories; $i++) {
                $S = DB_fetchArray($result2);
                if ($S['draft_flag'] == 0) {
                    $assoc_stories_published += 1;
                } else {
                    $assoc_stories_draft += 1;
                }
                $result3 = DB_query("SELECT ai_filename FROM {$_TABLES['article_images']} WHERE ai_sid = '{$S['sid']}'");
                $assoc_images += DB_numRows($result3);
                $result3 = DB_query("SELECT cid FROM {$_TABLES['comments']} WHERE sid = '{$S['sid']}' AND type = 'article'");
                $assoc_comments += DB_numRows($result3);
                $result3 = DB_query("SELECT cid FROM {$_TABLES['trackback']} WHERE sid = '{$S['sid']}' AND type = 'article'");
                $assoc_trackbacks += DB_numRows($result3);
            }
        }

    } else {
        // new topic - retain field values if any in case of failed validation
    	$A = array();
        $A['tid']           = isset($T['tid']) ? $T['tid'] : '';
        $A['topic']         = isset($T['topic']) ? $T['topic'] : '';
        $A['description']   = isset($T['description']) ? $T['description'] : '';
        $A['sortnum']       = isset($T['sortnum']) ? $T['sortnum'] : 0;
        $A['limitnews']     = isset($T['limitnews']) ? $T['limitnews'] : ''; // leave empty!
        $A['is_default']    = isset($T['is_default']) && $T['is_default'] == 'on' ? 1 : 0;
        $A['archive_flag']  = isset($T['archive_flag']) && $T['archive_flag'] == 'on' ? 1 : 0;
    	$A['sort_by']       = isset($T['sort_by']) ? $T['sort_by'] : 0;
    	$A['sort_dir']       = isset($T['sort_dir']) && $T['sort_dir'] == 'ASC' ? 'ASC' : 'DESC';
        $A['owner_id']      = isset($T['owner_id']) ? $T['owner_id'] : '';
        $A['group_id']      = isset($T['group_id']) ? $T['group_id'] : '';
        $A['imageurl']      = isset($T['imageurl']) ? $T['imageurl'] : '';

        $assoc_stories_submitted = 0;
        $assoc_blocks = 0;
        $assoc_feeds = 0;
        if ( $A['sortnum'] != '' ) {
            $tidSortNumber = DB_getItem($_TABLES['topics'],'sortnum','tid="'.DB_escapeString($A['sortnum']).'"');
            $newSortNum = $tidSortNumber;
        } else {
            $newSortNum = 0;
        }
        $A['sortnum'] = $newSortNum;

        // an empty owner_id signifies this is a new block, set to current user
        // this will also set the default values for group_id as well as the
        // default values for topic permissions
        if (empty($A['owner_id'])) {
            $A['owner_id'] = $_USER['uid'];
            // this is the one instance where we default the group
            // most topics should belong to the Topic Admin group
            if (isset ($_GROUPS['Topic Admin'])) {
                $A['group_id'] = $_GROUPS['Topic Admin'];
            } else {
                $A['group_id'] = SEC_getFeatureGroup ('topic.edit');
            }
            SEC_setDefaultPermissions ($A, $_CONF['default_permissions_topic']);
        } else {
            if ( isset($T['perm_owner']) ) {
                $A['perm_owner'] = SEC_getPermissionValue($T['perm_owner']);
                $A['perm_group'] = SEC_getPermissionValue($T['perm_group']);
                $A['perm_members'] = SEC_getPermissionValue($T['perm_members']);
                $A['perm_anon'] = SEC_getPermissionValue($T['perm_anon']);
            } else {
                SEC_setDefaultPermissions ($A, $_CONF['default_permissions_topic']);
            }
        }
        $access = 3;
    }

    // display the topic editor
    $topic_templates = new Template($_CONF['path_layout'] . 'admin/topic');
    $topic_templates->set_file('editor','topiceditor.thtml');

    // generate input for topic id
    if (!empty($topicEdit) && SEC_hasRights('topic.edit')) {
        $tid_input = $tid . '<input type="hidden" size="20" maxlength="128" name="tid" value="'.$tid.'">';
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="delete"%s>';
        $jsconfirm = ' onclick="return doubleconfirm(\'' . $LANG27[40] . '\',\'' . $LANG27[6] . '\');"';
        $topic_templates->set_var('delete_option',
                                  sprintf($delbutton, $jsconfirm));
        $topic_templates->set_var('delete_option_no_confirmation',
                                  sprintf($delbutton, ''));
        $topic_templates->clear_var('lang_donotusespaces');
    } else {
        $tid_input = '<input class="required alphanumeric" type="text" size="20" maxlength="128" name="tid" id="tid" value="'.$tid.'">';
        $topic_templates->set_var('lang_donotusespaces', $LANG27[5]);
    }
    $topic_templates->set_var('tid_input',$tid_input);

    $topic_templates->set_var('lang_topicid', $LANG27[2]);
    $topic_templates->set_var('topic_id', $A['tid']);
    $topic_templates->set_var('lang_accessrights',$LANG_ACCESS['accessrights']);
    $topic_templates->set_var('lang_owner', $LANG_ACCESS['owner']);

    $ownername = COM_getDisplayName ($A['owner_id']);
    $topic_templates->set_var('owner_username', DB_getItem ($_TABLES['users'],
                              'username', "uid = {$A['owner_id']}"));
    $topic_templates->set_var('owner_name', $ownername);
    $topic_templates->set_var('owner', $ownername);
    $topic_templates->set_var('owner_id', $A['owner_id']);

    $topic_templates->set_var('owner_dropdown',COM_buildOwnerList('owner_id',$A['owner_id']));

    $topic_templates->set_var('lang_group', $LANG_ACCESS['group']);
    $topic_templates->set_var('lang_save', $LANG_ADMIN['save']);
    $topic_templates->set_var('lang_cancel', $LANG_ADMIN['cancel']);
    $topic_templates->set_var('group_dropdown',
                              SEC_getGroupDropdown ($A['group_id'], $access));
    $topic_templates->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $topic_templates->set_var('lang_permissions_key', $LANG_ACCESS['permissionskey']);
    $topic_templates->set_var('permissions_editor', SEC_getPermissionsHTML($A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']));

    $sort_select = '<select id="sortnum" name="sortnum">' . LB;
    $sort_select .= '<option value="0">' . $LANG27[58] . '</option>' . LB;
    $result = DB_query("SELECT tid,topic,sortnum FROM {$_TABLES['topics']} ORDER BY sortnum ASC");

    if ( $topicEdit == 1 ) {
        $testvar = 10;
    } else {
        $testvar = 0;
    }

    $order = 10;
    while ($row = DB_fetchArray($result)) {
        if ( $row['tid'] != $tid ) {
            $test_sortnum = $order + $testvar;
            $sort_select .= '<option value="' . $row['tid'] . '"'.($A['sortnum'] == $test_sortnum ? ' selected="selected"' : '' ) . '>' . $row['topic'] . ' ('.$row['tid'].')' . '</option>' . LB;
        }
        $order += 10;
    }
    $sort_select .= '</select>' . LB;

    // show sort order only if they specified sortnum as the sort method
    if ($_CONF['sortmethod'] <> 'alpha') {
        $topic_templates->set_var('lang_sortorder', $LANG27[41]);
        if ($A['sortnum'] == 0) {
            $A['sortnum'] = '';
        }
        $topic_templates->set_var('sort_order',$sort_select);
    } else {
        $topic_templates->set_var('lang_sortorder', $LANG27[14]);
        $topic_templates->set_var('sort_order', $LANG27[15]);
    }
    $topic_templates->set_var('lang_storiesperpage', $LANG27[11]);
    if ($A['limitnews'] == 0) {
        $topic_templates->set_var('story_limit', '');
    } else {
        $topic_templates->set_var('story_limit', $A['limitnews']);
    }
    $topic_templates->set_var('default_limit', $_CONF['limitnews']);
    $topic_templates->set_var('lang_defaultis', $LANG27[16]);
    $topic_templates->set_var('lang_topicname', $LANG27[3]);
    $topic_templates->set_var('topic_name', htmlentities($A['topic']));
    $topic_templates->set_var('lang_description', $LANG27[59]);
    $topic_templates->set_var('topic_description', htmlentities($A['description']));
    if (empty($A['tid'])) {
        $A['imageurl'] = '/images/topics/';
    }
    $topic_templates->set_var('lang_topicimage', $LANG27[4]);
    $topic_templates->set_var('lang_uploadimage', $LANG27[27]);
    $topic_templates->set_var('icon_dimensions', $_CONF['max_topicicon_width'].' x '.$_CONF['max_topicicon_height']);
    $topic_templates->set_var('lang_maxsize', $LANG27[28]);
    $topic_templates->set_var('max_url_length', 255);
    $topic_templates->set_var('image_url', $A['imageurl']);

    if ( strtolower(substr(strrchr($A['imageurl'],'.'),1)) == "svg" ) {
        $topic_templates->set_var('topicimage',$_CONF['site_url'].$A['imageurl']);
    } elseif ( @getimagesize($_CONF['path_html'].$A['imageurl']) !== false ) {
        $topic_templates->set_var('topicimage',$_CONF['site_url'].$A['imageurl']);
    }

    $topic_templates->set_var ('lang_defaulttopic', $LANG27[22]);
    $topic_templates->set_var ('lang_defaulttext', $LANG27[23]);
    if ($A['is_default'] == 1) {
        $topic_templates->set_var ('default_checked', 'checked="checked"');
    } else {
        $topic_templates->set_var ('default_checked', '');
    }

    $topic_templates->set_var('lang_sort_story_by',$LANG27[35]);
    $topic_templates->set_var('lang_sort_story_dir',$LANG27[36]);

    $sortSelect =  '<select name="sort_by" id="sort_by">'.LB;
    $sortSelect .= '<option value="0"'.($A['sort_by'] == 0 ? ' selected="selected"' : '') .'>'.$LANG27[30].'</option>'.LB;
    $sortSelect .= '<option value="1"'.($A['sort_by'] == 1 ? ' selected="selected"' : '') .'>'.$LANG27[31].'</option>'.LB;
    $sortSelect .= '<option value="2"'.($A['sort_by'] == 2 ? ' selected="selected"' : '') .'>'.$LANG27[32].'</option>'.LB;
    $sortSelect .= '</select>'.LB;
    $topic_templates->set_var ('story_sort_select', $sortSelect);

    $sort_dir     = '<select name="sort_dir" id="sort_dir">'.LB;
    $sort_dir    .= '<option value="ASC"'.($A['sort_dir'] == 'ASC' ? ' selected="selected"' : '') .'>'.$LANG27[33].'</option>'.LB;
    $sort_dir    .= '<option value="DESC"'.($A['sort_dir'] == 'DESC' ? ' selected="selected"' : '') .'>'.$LANG27[34].'</option>'.LB;
    $sort_dir    .= '</select>';
    $topic_templates->set_var ('story_sort_dir', $sort_dir);

    $topic_templates->set_var ('lang_archivetopic', $LANG27[25]);
    $topic_templates->set_var ('lang_archivetext', $LANG27[26]);
    $topic_templates->set_var ('archive_disabled', '');
    if ($A['archive_flag'] == 1) {
        $topic_templates->set_var ('archive_checked', 'checked="checked"');
    } else {
        $topic_templates->set_var ('archive_checked', '');
        // Only 1 topic can be the archive topic - so check if there already is one
        if (DB_count($_TABLES['topics'], 'archive_flag', '1') > 0) {
            $topic_templates->set_var ('archive_disabled', 'disabled');
        }
    }

    $assoc_stories = (($assoc_stories_published > 0) OR
                        ($assoc_stories_draft > 0) OR
                        ($assoc_stories_submitted > 0) OR
                        ($assoc_images > 0) OR
                        ($assoc_comments > 0) OR
                        ($assoc_trackbacks > 0));

    if (($assoc_blocks > 0) OR ($assoc_feeds > 0) OR ($assoc_stories)) {
        $topic_templates->set_var('lang_assoc_objects', $LANG27[43]);
        if ($assoc_stories_published > 0) {
            $topic_templates->set_var('lang_assoc_stories_published', $LANG27[44]);
            $topic_templates->set_var('assoc_stories_published', $assoc_stories_published);
            $topic_templates->set_var('published_story_admin_link', COM_createLink($LANG27[52], $_CONF['site_admin_url'] . '/story.php'));
        }
        if ($assoc_stories_draft > 0) {
            $topic_templates->set_var('lang_assoc_stories_draft', $LANG27[45]);
            $topic_templates->set_var('assoc_stories_draft', $assoc_stories_draft);
            $topic_templates->set_var('draft_story_admin_link', COM_createLink($LANG27[52], $_CONF['site_admin_url'] . '/story.php'));
        }
        if ($assoc_stories_submitted > 0) {
            $topic_templates->set_var('lang_assoc_stories_submitted', $LANG27[46]);
            $topic_templates->set_var('assoc_stories_submitted', $assoc_stories_submitted);
            $topic_templates->set_var('moderation_link', COM_createLink($LANG27[53], $_CONF['site_admin_url'] . '/moderation.php'));
        }
        if ($assoc_images > 0) {
            $topic_templates->set_var('lang_assoc_images', $LANG27[47]);
            $topic_templates->set_var('assoc_images', $assoc_images);
        }
        if ($assoc_comments > 0) {
            $topic_templates->set_var('lang_assoc_comments', $LANG27[48]);
            $topic_templates->set_var('assoc_comments', $assoc_comments);
        }
        if ($assoc_trackbacks > 0) {
            $topic_templates->set_var('lang_assoc_trackbacks', $LANG27[49]);
            $topic_templates->set_var('assoc_trackbacks', $assoc_trackbacks);
        }
        if ($assoc_blocks > 0) {
            $topic_templates->set_var('lang_assoc_blocks', $LANG27[50]);
            $topic_templates->set_var('assoc_blocks', $assoc_blocks);
            $topic_templates->set_var('block_admin_link', COM_createLink($LANG27[54], $_CONF['site_admin_url'] . '/block.php'));
        }
        if ($assoc_feeds > 0) {
            $topic_templates->set_var('lang_assoc_feeds', $LANG27[51]);
            $topic_templates->set_var('assoc_feeds', $assoc_feeds);
            $topic_templates->set_var('syndication_admin_link', COM_createLink($LANG27[55], $_CONF['site_admin_url'] . '/syndication.php'));
            }
    }

    $topic_templates->set_var('gltoken_name', CSRF_TOKEN);
    $topic_templates->set_var('gltoken', SEC_createToken());
    $topic_templates->parse('output', 'editor');

    if ( $msg != '' ) {
        $retval .= COM_showMessageText($msg);
    }

    $retval .= COM_startBlock ($LANG27[1], '',COM_getBlockTemplate ('_admin_block', 'header'));

    if ( $topicEdit ) {
        $lang_create_or_edit = $LANG_ADMIN['edit'];
    } else {
        $lang_create_or_edit = $LANG_ADMIN['create_new'];
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/topic.php',
              'text' => $LANG_ADMIN['topic_list']),
        array('url' => $_CONF['site_admin_url'] . '/topic.php?edit=x',
              'text' => $lang_create_or_edit,'active'=>true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG27[57],
        $_CONF['layout_url'] . '/images/icons/topic.' . $_IMAGE_TYPE
    );

    $retval .= $topic_templates->finish($topic_templates->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/**
* Save topic to the database
*
* @param    string  $tid            Topic ID
* @param    string  $topic          Name of topic (what the user sees)
* @param    string  $imageurl       (partial) URL to topic image
* @param    int     $sortnum        number for sort order in "Topics" block
* @param    int     $limitnews      number of stories per page for this topic
* @param    int     $owner_id       ID of owner
* @param    int     $group_id       ID of group topic belongs to
* @param    int     $perm_owner     Permissions the owner has
* @param    int     $perm_group     Permissions the group has
* @param    int     $perm_members    Permissions members have
* @param    int     $perm_anon      Permissions anonymous users have
* @param    string  $is_default     'on' if this is the default topic
* @param    string  $archive_flag     'on' if this is the archive topic
* @return   string                  HTML redirect or error message
*/
function TOPIC_save($T)
{
    global $_CONF, $_TABLES, $LANG27, $MESSAGE;

    $retval = '';

    $tid        = isset($T['tid']) ? $T['tid'] : '';
    $topic      = $T['topic'];
    $description = $T['description'];
    $imageurl   = $T['imageurl'];
    $sortnum    = $T['sortnum'];
    $sort_by    = $T['sort_by'];
    $limitnews  = $T['limitnews'];
    $sort_dir   = $T['sort_dir'];
    $owner_id   = $T['owner_id'];
    $group_id   = $T['group_id'];
    $perm_owner = $T['perm_owner'];
    $perm_group = $T['perm_group'];
    $perm_members = $T['perm_members'];
    $perm_anon  = $T['perm_anon'];
    $is_default = $T['is_default'];
    $archive_flag = $T['archive_flag'];

    // error checks...

    if ( empty($tid) ) {
        $msg = $LANG27[7];
        $retval .= COM_siteHeader();
        $retval .= TOPIC_edit('',$T,$msg);
        $retval .= COM_siteFooter();
        return $retval;
    }
    if ( empty($topic) ) {
        $msg = $LANG27[7];
        $retval .= COM_siteHeader();
        $retval .= TOPIC_edit('',$T,$msg);
        $retval .= COM_siteFooter();
        return $retval;
    }
    if ( strstr($tid,' ') ) {
        $msg = $LANG27[42];
        $retval .= COM_siteHeader();
        $retval .= TOPIC_edit('',$T,$msg);
        $retval .= COM_siteFooter();
        return $retval;
    }

    if ( $sortnum != '' ) {
        $tidSortNumber = DB_getItem($_TABLES['topics'],'sortnum','tid="'.DB_escapeString($sortnum).'"');
        $newSortNum = $tidSortNumber + 1;
    } else {
        $newSortNum = 0;
    }
    $T['sortnum'] = $newSortNum;

    list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);

    $tid = COM_sanitizeID ($tid);

    $access = 0;
    if (DB_count ($_TABLES['topics'], 'tid', $tid) > 0) {
        $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['topics']} WHERE tid = '{$tid}'");
        $A = DB_fetchArray ($result);
        if ( SEC_inGroup('Topic Admin') ) {
            $access = 3;
        } else {
            $access = SEC_hasAccess ($A['owner_id'], $A['group_id'],
                    $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                    $A['perm_anon']);
        }
    } else {
        if ( SEC_inGroup('Topic Admin') ) {
            $access = 3;
        } else {
            $access = SEC_hasAccess ($owner_id, $group_id, $perm_owner, $perm_group,
                    $perm_members, $perm_anon);
        }
    }
    if (($access < 3) || !SEC_inGroup ($group_id)) {
        $retval .= COM_siteHeader ('menu', $MESSAGE[30]);
        $retval .= COM_showMessageText($MESSAGE[32],$MESSAGE[30],true,'error');
        $retval .= COM_siteFooter ();
        COM_accessLog("User {$_USER['username']} tried to create or edit topic $tid.");
    } elseif (!empty($tid) && !empty($topic)) {
        if ($imageurl == '/images/topics/') {
            $imageurl = '';
        }
        $topic = DB_escapeString(strip_tags($topic));

        if ($is_default == 'on') {
            $is_default = 1;
            DB_query ("UPDATE {$_TABLES['topics']} SET is_default = 0 WHERE is_default = 1");
        } else {
            $is_default = 0;
        }

        $archive_flag = ($archive_flag == 'on') ? 1 : 0;

        $archivetid = DB_getItem ($_TABLES['topics'], 'tid', "archive_flag=1");
        if ($archive_flag) {
            // $tid is the archive topic
            // - if it wasn't already, mark all its stories "archived" now
            if ($archivetid != $tid) {
                DB_query ("UPDATE {$_TABLES['stories']} SET featured = 0, frontpage = 0, statuscode = " . STORY_ARCHIVE_ON_EXPIRE . " WHERE tid = '$tid'");
                DB_query ("UPDATE {$_TABLES['topics']} SET archive_flag = 0 WHERE archive_flag = 1");
            }
        } else {
            // $tid is not the archive topic
            // - if it was until now, reset the "archived" status of its stories
            if ($archivetid == $tid) {
                DB_query ("UPDATE {$_TABLES['stories']} SET statuscode = 0 WHERE tid = '$tid'");
                DB_query ("UPDATE {$_TABLES['topics']} SET archive_flag = 0 WHERE archive_flag = 1");
            }
        }

        if ( $sortnum != '' ) {
            $tidSortNumber = DB_getItem($_TABLES['topics'],'sortnum','tid="'.DB_escapeString($sortnum).'"');
            $newSortNum = $tidSortNumber + 1;
        } else {
            $newSortNum = 0;
        }
        $description = DB_escapeString($description);
        DB_save($_TABLES['topics'],'tid, topic, description,imageurl, sortnum, sort_by, sort_dir, limitnews, is_default, archive_flag, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon',"'$tid', '$topic', '$description','$imageurl','$newSortNum',$sort_by,'$sort_dir','$limitnews',$is_default,'$archive_flag',$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon");
        TOPIC_reorderTopics();

        // update feed(s) and Older Stories block
        COM_rdfUpToDateCheck ('article', $tid);
        COM_olderStuff ();
        CACHE_remove_instance('menu');
        COM_setMessage(13);
        $retval = COM_refresh ($_CONF['site_admin_url'] . '/topic.php');
    } else {
        $retval .= COM_siteHeader('menu', $LANG27[1]);
        $retval .= COM_errorLog($LANG27[7], 2);
        $retval .= COM_siteFooter();
    }

    return $retval;
}


/**
 * return a field value for the topic administration list
 *
 */
function TOPIC_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ADMIN, $LANG27, $_IMAGE_TYPE;

    $retval = false;

    $access = (SEC_inGroup('Topic Admin')) ? 3 : SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);

    if ($access > 0) {
        switch($fieldname) {

            case 'edit':
                $retval = '';
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['edit'];
                    $retval .= COM_createLink($icon_arr['edit'],
                        $_CONF['site_admin_url'] . '/topic.php?edit=x&amp;tid=' . $A['tid'], $attr);
                }
                break;

            case 'tid':
                $retval = $fieldvalue;
                break;

            case 'limitnews' :
                if ( $fieldvalue == '' ) {
                    return $_CONF['limitnews'];
                }
                return $fieldvalue;
                break;

            case 'topic':
                $retval = $fieldvalue;
                break;

            case 'sort_by':
                $sortByLang = 30 + (int) $fieldvalue;
                if ( isset($LANG27[$sortByLang])) {
                    $retval = $LANG27[$sortByLang]; // 30+$fieldvalue];
                } else {
                    $retval = 'undefined';
                }
                break;

            case 'is_default':
            case 'archive_flag':
                $retval = ($fieldvalue != 0) ? $icon_arr['check'] : '';
                break;

            case 'move':
                if ($access == 3) {
                    if ($A['onleft'] == 1) {
                        $side = $LANG21[40];
                        $blockcontrol_image = 'block-right.' . $_IMAGE_TYPE;
                        $moveTitleMsg = $LANG21[59];
                        $switchside = '1';
                    } else {
                        $blockcontrol_image = 'block-left.' . $_IMAGE_TYPE;
                        $moveTitleMsg = $LANG21[60];
                        $switchside = '0';
                    }
                    $retval.="<img src=\"{$_CONF['layout_url']}/images/admin/$blockcontrol_image\" width=\"45\" height=\"20\" usemap=\"#arrow{$A['bid']}\" alt=\"\">"
                            ."<map id=\"arrow{$A['bid']}\" name=\"arrow{$A['bid']}\">"
                            ."<area coords=\"0,0,12,20\"  title=\"{$LANG21[58]}\" href=\"{$_CONF['site_admin_url']}/block.php?move=1&amp;bid={$A['bid']}&amp;where=up&amp;".CSRF_TOKEN."={$token}\" alt=\"{$LANG21[58]}\">"
                            ."<area coords=\"13,0,29,20\" title=\"$moveTitleMsg\" href=\"{$_CONF['site_admin_url']}/block.php?move=1&amp;bid={$A['bid']}&amp;where=$switchside&amp;".CSRF_TOKEN."={$token}\" alt=\"$moveTitleMsg\">"
                            ."<area coords=\"30,0,43,20\" title=\"{$LANG21[57]}\" href=\"{$_CONF['site_admin_url']}/block.php?move=1&amp;bid={$A['bid']}&amp;where=dn&amp;".CSRF_TOKEN."={$token}\" alt=\"{$LANG21[57]}\">"
                            ."</map>";
                }
                break;

            case 'delete':
                $retval = '';
                if ($access == 3) {
                    $attr['title'] = $LANG_ADMIN['delete'];
                    $attr['onclick'] = 'return doubleconfirm(\'' . $LANG27[40] . '\',\'' . $LANG27[6] . ' ' . $LANG27[56] . '\');';
                    $retval .= COM_createLink($icon_arr['delete'],
                        $_CONF['site_admin_url'] . '/topic.php'
                        . '?delete=x&amp;tid=' . $A['tid'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
                }
                break;

            default:
                $retval = $fieldvalue;
                break;
        }
    }

    return $retval;
}

/**
* Displays a list of topics
*
* Lists all the topics and their icons.
*
* @return   string      HTML for the topic list
*
*/
function TOPIC_list()
{
    global $_CONF, $_TABLES, $LANG27, $LANG_ACCESS, $LANG_ADMIN, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/topic.php',
              'text' => $LANG_ADMIN['topic_list'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/topic.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG27[8], '', COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG27[9],
        $_CONF['layout_url'] . '/images/icons/topic.' . $_IMAGE_TYPE
    );

    $header_arr = array(
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '35px'),
        array('text' => $LANG27[10], 'field' => 'sortnum', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG27[2], 'field' => 'tid', 'sort' => true),
        array('text' => $LANG27[3], 'field' => 'topic', 'sort' => true),
        array('text' => $LANG27[38], 'field' => 'is_default', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG27[39], 'field' => 'archive_flag', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG27[11], 'field' => 'limitnews', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG27[35], 'field' => 'sort_by', 'sort' => false, 'align' => 'center', 'nowrap' => 'true'),
        array('text' => $LANG27[37], 'field' => 'sort_dir', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '35px'),
    );

    $defsort_arr = array('field' => 'sortnum', 'direction' => 'asc');

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/topic.php'
    );

    $query_arr = array(
        'table' => 'topics',
        'sql' => "SELECT * FROM {$_TABLES['topics']} WHERE 1=1",
        'query_fields' => array('tid', 'topic'),
        'default_filter' => COM_getPermSql ('AND')
    );

    $token = SEC_createToken();
    $form_arr = array(
        'bottom'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
    );

    $retval .= ADMIN_list('topics','TOPIC_getListField',
        $header_arr,$text_arr,$query_arr,$defsort_arr,'',$token,'',$form_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;

}


/**
* Delete a topic
*
* @param    string  $tid    Topic ID
* @return   string          HTML redirect
*
*/
function TOPIC_delete($tid)
{
    global $_CONF, $_TABLES, $_USER;

    $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['topics']} WHERE tid ='$tid'");
    $A = DB_fetchArray ($result);
    if ( SEC_inGroup('Topic Admin') ) {
        $access = 3;
    } else {
        $access = SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'],
                $A['perm_group'], $A['perm_members'], $A['perm_anon']);
    }
    if ($access < 3) {
        COM_accessLog ("User {$_USER['username']} tried to delete topic $tid.");
        return COM_refresh ($_CONF['site_admin_url'] . '/topic.php');
    }

    // don't delete topic blocks - assign them to 'all' and disable them
    DB_query ("UPDATE {$_TABLES['blocks']} SET tid = 'all', is_enabled = 0 WHERE tid = '$tid'");

    // same with feeds
    DB_query ("UPDATE {$_TABLES['syndication']} SET topic = '::all', is_enabled = 0 WHERE topic = '$tid'");
    // remove any alternate topics
    DB_query ("UPDATE {$_TABLES['stories']} SET alternate_tid = NULL WHERE alternate_tid = '$tid'");
    // promote stories with a different alt topic
    $result = DB_query("SELECT sid,alternate_tid FROM {$_TABLES['stories']} WHERE tid = '$tid' AND (alternate_tid != NULL || alternate_tid != '')");
    while ( ( $A = DB_fetchArray($result)) != NULL ) {
        DB_query("UPDATE {$_TABLES['stories']} SET tid='".$A['alternate_tid']."', alternate_tid=NULL WHERE sid='".$A['sid']."'");
    }

    // delete comments, trackbacks, images associated with stories in this topic
    $result = DB_query ("SELECT sid FROM {$_TABLES['stories']} WHERE tid = '$tid'");
    $numStories = DB_numRows ($result);
    for ($i = 0; $i < $numStories; $i++) {
        $A = DB_fetchArray ($result);
        STORY_removeStory($A['sid']);
    }

    // delete these
    DB_delete ($_TABLES['storysubmission'], 'tid', $tid);
    DB_delete ($_TABLES['topics'], 'tid', $tid);

    TOPIC_reorderTopics();

    // update feed(s) and Older Stories block
    COM_rdfUpToDateCheck ('article');
    COM_olderStuff ();
    CACHE_remove_instance('menu');
    COM_setMessage(14);
    return COM_refresh ($_CONF['site_admin_url'] . '/topic.php');
}

/**
* Upload new topic icon, replaces previous icon if one exists
*
* @param    string  tid     ID of topic to prepend to filename
* @return   string          filename of new photo (empty = no new photo)
*
*/
function TOPIC_iconUpload($tid)
{
    global $_CONF, $_TABLES, $LANG27;

    $upload = new upload();
    if (!empty ($_CONF['image_lib'])) {

        $upload->setAutomaticResize (true);
        if (isset ($_CONF['debug_image_upload']) &&
                $_CONF['debug_image_upload']) {
            $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
            $upload->setDebug (true);
        }
    }
    $upload->setAllowedMimeTypes (array ('image/gif'   => '.gif',
                                         'image/jpeg'  => '.jpg,.jpeg',
                                         'image/pjpeg' => '.jpg,.jpeg',
                                         'image/x-png' => '.png',
                                         'image/png'   => '.png',
                                         'image/svg+xml' => '.svg',
                                 )      );
    if (!$upload->setPath ($_CONF['path_images'] . 'topics')) {
        $display  = COM_siteHeader ('menu', $LANG27[29]);
        $display .= COM_showMessageText($upload->printErrors (false),$LANG27[29],true);
        $display .= COM_siteFooter ();
        echo $display;
        exit; // don't return
    }
    $upload->setFieldName('newicon');

    $filename = '';

    // see if user wants to upload a (new) icon
    $newicon = $_FILES['newicon'];
    if (!empty ($newicon['name'])) {
        $pos = strrpos ($newicon['name'], '.') + 1;
        $fextension = substr ($newicon['name'], $pos);
        $filename = 'topic_' . $tid . '.' . $fextension;
    }

    // do the upload
    if (!empty ($filename)) {

        $upload->setFileNames ($filename);
        $upload->setPerms ('0644');
        if (($_CONF['max_topicicon_width'] > 0) &&
            ($_CONF['max_topicicon_height'] > 0)) {
            $upload->setMaxDimensions ($_CONF['max_topicicon_width'],
                                       $_CONF['max_topicicon_height']);
        } else {
            $upload->setMaxDimensions ($_CONF['max_image_width'],
                                       $_CONF['max_image_height']);
        }
        if ($_CONF['max_topicicon_size'] > 0) {
            $upload->setMaxFileSize($_CONF['max_topicicon_size']);
        } else {
            $upload->setMaxFileSize($_CONF['max_image_size']);
        }
        $upload->uploadFiles ();

        if ($upload->areErrors ()) {
            $display = COM_siteHeader ('menu', $LANG27[29]);
            $display .= COM_showMessageText($upload->printErrors (false),$LANG27[29],true);
            $display .= COM_siteFooter ();
            echo $display;
            exit; // don't return
        }
        $filename = '/images/topics/' . $filename;
    }

    return $filename;
}

function TOPIC_reorderTopics()
{
    global $_TABLES;

    // reorder the topics...
    $orderCount = 10;
    $sql = "SELECT tid,sortnum FROM {$_TABLES['topics']} ORDER BY sortnum ASC";
    $result = DB_query($sql);
    while ($M = DB_fetchArray($result)) {
        $M['sortnum'] = $orderCount;
        $orderCount += 10;
        DB_query("UPDATE {$_TABLES['topics']} SET sortnum=" . $M['sortnum'] . " WHERE tid='".$M['tid']."'" );
    }
}


// MAIN
$display = '';

$action = '';
$expected = array('edit','save','delete','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

$tid = '';
if (isset($_POST['tid'])) {
    $tid = COM_applyFilter($_POST['tid']);
} elseif (isset($_GET['tid'])) {
    $tid = COM_applyFilter($_GET['tid']);
}

switch ($action) {

    case 'edit':
        $display .= COM_siteHeader('menu', $LANG27[1]);
        $display .= TOPIC_edit($tid);
        $display .= COM_siteFooter();
        break;

    case 'save':
        $T = array();

        $T['tid']           = (isset($_POST['tid']) ? $_POST['tid'] : '');
        $T['topic']         = (isset($_POST['topic']) ? $_POST['topic'] : '');
        $T['description']   = (isset($_POST['description']) ? $_POST['description'] : '');
        $T['sortnum']       = (isset($_POST['sortnum']) ? COM_applyFilter($_POST['sortnum']) : '');
        $T['limitnews']     = (isset($_POST['limitnews']) ? COM_applyFilter($_POST['limitnews'],true) : '');
        $T['owner_id']      = (isset($_POST['owner_id']) ? COM_applyFilter($_POST['owner_id'],true) : 2 );
        $T['group_id']      = (isset($_POST['group_id']) ? COM_applyFilter($_POST['group_id'],true) : 1 );
        $T['perm_owner']    = (isset($_POST['perm_owner']) ? $_POST['perm_owner'] : array());
        $T['perm_group']    = (isset($_POST['perm_group']) ? $_POST['perm_group'] : array());
        $T['perm_members']  = (isset($_POST['perm_members']) ? $_POST['perm_members'] : array());
        $T['perm_anon']     = (isset($_POST['perm_anon']) ? $_POST['perm_anon'] : array());
        $T['imageurl']      = (empty($_FILES['newicon']['name'])) ? COM_applyFilter ($_POST['imageurl']) : COM_applyFilter(TOPIC_iconUpload($tid));
        $T['is_default']    = (isset($_POST['is_default'])) ? $_POST['is_default'] : '';
        $T['archive_flag']  = (isset($_POST['archive_flag'])) ? $_POST['archive_flag'] : '';
        $T['sort_by']       = (isset($_POST['sort_by'])) ? COM_applyFilter($_POST['sort_by'],true) : 0;
        $T['sort_by']       = (($T['sort_by'] < 0) || ($T['sort_by'] > 2)) ? 0 : $T['sort_by'];
        $T['sort_dir']      = (isset($_POST['sort_dir'])) ? (($_POST['sort_dir'] == 'ASC') ? 'ASC' : 'DESC') : 'DESC';

        if (!SEC_checkToken()) {
            $display .= COM_siteHeader('menu');
            $display .= TOPIC_edit('',$T,$MESSAGE[501]);
            $display .= COM_siteFooter();
            echo $display;
            exit;
        }
        $display .= TOPIC_save($T);
        CACHE_remove_instance('story');
        break;

    case 'delete':
        if (!isset($tid) || empty($tid)) {
            COM_errorLog('Attempted to delete topic, tid empty or null, value = ' . $tid);
            $display .= COM_refresh($_CONF['site_admin_url'] . '/topic.php');
        } elseif (SEC_checkToken()) {
            $display .= TOPIC_delete($tid);
        } else {
            COM_accessLog("User {$_USER['username']} tried to delete topic $tid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader('menu', $LANG27[8]);
        $msg = COM_getMessage();
        $display .= ($msg > 0) ? COM_showMessage($msg) : '';
        $display .= TOPIC_list();
        $display .= COM_siteFooter();
        break;

}

echo $display;

?>
