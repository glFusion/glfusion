<?php
/**
* glFusion CMS
*
* glFusion story administration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Authors: Tony Bibbs        - tony AT tonybibbs DOT com
*           Mark Limburg      - mlimburg AT users DOT sourceforge DOT net
*           Jason Whittenburg - jwhitten AT securitygeeks DOT com
*           Dirk Haun         - dirk AT haun-online DOT de
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\Article\Article;
use \glFusion\Database\Database;
use \glFusion\Admin\AdminAction;
use \glFusion\FieldList;

$display = '';

if (!SEC_hasRights('story.edit')) {
    Log::logAccessViolation('Story Administration');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[31],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
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

    $db = Database::getInstance();

    $filter = new \sanitizer();

    switch($fieldname) {

        case "access":
        case "edit":
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
                if ($fieldname == 'edit') {
                    $retval = FieldList::edit(array(
                        'url' => $_CONF['site_admin_url'].'/story.php?edit=x&amp;sid='.$A['sid'],
                    ));
                }
            }
            break;

        case "copy":
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
                $retval = FieldList::copy(array(
                    'url' => $_CONF['site_admin_url'].'/story.php?clone=x&amp;sid='.urlencode($A['sid']),
                ));
            }
            break;

        case "title":
            $A['title'] = $filter->htmlspecialchars(str_replace('$', '&#36;', $A['title']));
            if ($A['draft_flag'] == 0 && $A['date'] < $_CONF['_now']->toMySQL(false)) {
                $article_url = COM_buildUrl ($_CONF['site_url'] . '/article.php?story='.urlencode($A['sid']));
                $retval = COM_createLink($A['title'], $article_url);
            } else {
                $retval = $A['title'];
            }
            break;

        case 'tid':
            if (!isset ($topics[$A['tid']])) {
                $topics[$A['tid']] = $db->getItem(
                                        $_TABLES['topics'],
                                        'topic',
                                        array('tid' => $A['tid']),
                                        array(Database::STRING)
                                     );
            }
            $retval = $topics[$A['tid']];
            break;
        case 'alternate_tid' :
            if (!isset ($topics[$A['alternate_tid']])) {
                $topics[$A['alternate_tid']] = $db->getItem(
                                                $_TABLES['topics'],
                                                'topic',
                                                array('tid' => $A['alternate_tid']),
                                                array(Database::STRING)
                                              );
            }
            $retval = $topics[$A['alternate_tid']];
            break;

        case "draft_flag":
            if ($A['draft_flag'] == 1) {
                $retval = FieldList::checkmark(array('active'=>true));
            }
            break;

        case "featured":
            if ($A['featured']) {
                $retval = FieldList::checkmark(array('active'=>true));
            }
            break;

        case 'username':
            $retval = COM_getDisplayName ($A['uid'], $A['username'], $A['fullname']);
            break;

        case 'date' :
            if ($fieldvalue == NULL || empty($fieldvalue) || $fieldvalue == '1000-01-01 00:00:00') {
                $fieldvalue = time();
            }
            $dtTmp = new \Date($fieldvalue,null);
            $dt = new \Date('now',$_USER['tzid']);
            $dt->setTimestamp($dtTmp->toUnix());
            unset($dtTmp);
            return $dt->format($_CONF['daytime'],true);

        case "ping":
            if (($A['draft_flag'] == 0) && ($A['unixdate'] < time())) {
                $retval = FieldList::ping(array(
                    'url' => $_CONF['site_admin_url'].'/trackback.php?mode=sendall&amp;id=' . $A['sid']
                ));
            } else {
                $retval = '';
            }
            break;

        case 'delete':
            $retval = FieldList::delete(
                array(
                    'delete_url' => $_CONF['site_admin_url'] . '/story.php'.'?deletestory=x&amp;sid=' . $A['sid'] . '&amp;' . CSRF_TOKEN . '=' . $token,
                    'attr' => array(
                        'title' => $LANG_ADMIN['delete'],
                        'onclick' => 'return confirm(\'' . $LANG24[89] .'\');',
                    )
                )
            );
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

    if ( !SEC_inGroup('Root')) {
        COM_refresh($_CONF['site_url']);
    }

    $retval = '';

    // Load HTML templates
    $T = new Template($_CONF['path_layout'] . 'admin/story');
    $T->set_file(array('page' => 'storyglobal.thtml'));

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/story.php',
              'text' => $LANG_ADMIN['story_list']),
       array('url' => $_CONF['site_admin_url'] . '/story.php?newstory=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/story.php?global=x',
                      'text' => $LANG24[111],'active'=>true),
        array('url' => $_CONF['site_admin_url'].'/index.php',
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

    $seltopics = COM_topicList ('tid,topic,sortnum', '', 2, true);
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
    global $_CONF, $_TABLES, $LANG09, $LANG24, $LANG_ADM_ACTIONS;

    if (!SEC_inGroup('Root')) {
        COM_404();
        exit;
    }

    if (!SEC_checkToken()) {
        return STORY_list();
    }

    if (!isset($_POST['cb'])) {
        return STORY_list();
    }

    $sql = '';
    $global_sql = '';
    $msg = '';

    $filter_topic  = filter_input(INPUT_POST,'tid',FILTER_SANITIZE_STRING); //COM_applyFilter($_POST['tid']);
    $move_to_topic = filter_input(INPUT_POST,'move_to_topic',FILTER_SANITIZE_STRING); //COM_applyFilter($_POST['move_to_topic']);
    $on_frontpage  = filter_input(INPUT_POST,'frontpage',FILTER_SANITIZE_NUMBER_INT); //COM_applyFilter($_POST['frontpage'],true);
    $comment       = filter_input(INPUT_POST,'comment',FILTER_SANITIZE_NUMBER_INT); //COM_applyFilter($_POST['comment'],true);
    $trackback     = filter_input(INPUT_POST,'trackback',FILTER_SANITIZE_NUMBER_INT); //COM_applyFilter($_POST['trackback'],true);
    $owner_id      = filter_input(INPUT_POST,'owner_id',FILTER_SANITIZE_NUMBER_INT); //COM_applyFilter($_POST['owner_id'],true);
    $group_id      = filter_input(INPUT_POST,'group_id',FILTER_SANITIZE_NUMBER_INT); //COM_applyFilter($_POST['group_id'],true);
    $show_topic_icon = isset($_POST['show_topic_icon']) ? 1 : 0;


    $db = Database::getInstance();

    // checkboxes
    $active = $_POST['cb'];

    $params = [];
    $types  = [];

    if (isset($active['frontpage'])) {
        $params['frontpage'] = (int) $on_frontpage;
        $types[]  = Database::INTEGER;
        $msg .= $LANG24[116].'<br>';

        AdminAction::write('system','article_global',
            sprintf($LANG_ADM_ACTIONS['article_global'],$LANG24[116]));
    }

    if (isset($active['comment'])) {
        $params['commentcode'] = (int) $comment;
        $types[]  = Database::INTEGER;
        $msg .= $LANG24[117].'<br>';
        AdminAction::write('system','article_global',
            sprintf($LANG_ADM_ACTIONS['article_global'],$LANG24[117]));

    }

    if (isset($active['trackback'])) {
        $params['trackbackcode'] = (int) $trackback;
        $types[]  = Database::INTEGER;
        $msg .= $LANG24[118].'<br>';
        AdminAction::write('system','article_global',
            sprintf($LANG_ADM_ACTIONS['article_global'],$LANG24[118]));

    }

    if (isset($active['owner'])) {
        $params['owner_id'] = (int) $owner_id;
        $types[]  = Database::INTEGER;
        $msg .= $LANG24[119].'<br>';
        AdminAction::write('system','article_global',
            sprintf($LANG_ADM_ACTIONS['article_global'],$LANG24[119]));

    }

    if (isset($active['group'])){
        $params['group_id'] = (int) $group_id;
        $types[] = Database::INTEGER;
        $msg .= $LANG24[120].'<br>';
        AdminAction::write('system','article_global',
            sprintf($LANG_ADM_ACTIONS['article_global'],$LANG24[120]));

    }

    if (isset($active['show_topic_icon'])) {
        $params['show_topic_icon'] = (int) $show_topic_icon;
        $types[] = Database::INTEGER;
        $msg .= $LANG24[121].'<br>';
        AdminAction::write('system','article_global',
            sprintf($LANG_ADM_ACTIONS['article_global'],$LANG24[121]));

    }

    if ($filter_topic != $LANG09[9] && $sql != '' ) {
        $where = array('tid' => $filter_topic);
        $types[] = Database::STRING;
    } else {
        $where = array(1=>1);
    }

    if (count($params) > 0) {
        $db->conn->update(
                $_TABLES['stories'],
                $params,
                $where,
                $types
        );
    }

    if ( isset($active['move_to_topic'])) {
        if (($filter_topic != $LANG09[9]) && ($filter_topic != $move_to_topic)) {
            $db->conn->update(
                $_TABLES['stories'],
                array('featured' => 0),
                array('tid' => $filter_topic),
                array(Database::INTEGER, Database::STRING)
            );

// move the stories
            $db->conn->update(
                $_TABLES['stories'],
                array('tid' => $move_to_topic),
                array('tid' => $filter_topic),
                array(Database::STRING, Database::STRING)
            );
            $msg .= sprintf($LANG24[122],$filter_topic,$move_to_topic);
            AdminAction::write('system','article_global',
                sprintf($LANG_ADM_ACTIONS['article_global'],$msg));

        } else {
            $msg .= $LANG24[123];
        }
    }

    COM_setMsg( $msg, 'error' );

    $_POST['tid'] = '';

    Cache::getInstance()->deleteItemsByTags(array('story','menu'));

    return STORY_list();
}

function STORY_list()
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE,
           $LANG09, $LANG_ADMIN, $LANG_ACCESS, $LANG24;

    USES_lib_admin();

    $retval = '';

    $db = Database::getInstance();

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
            $current_topic = COM_applyFilter(SESS_getVar('story_admin_topic'));
        } else {
            $current_topic = $LANG09[9];
        }
    }
    SESS_setVar('story_admin_topic',$current_topic);

    // topic filter for admin select

    if ($current_topic == $LANG09[9]) {
        $excludetopics = '';
        $seltopics = '';

        $stmt = $db->conn->query(
                    "SELECT tid,topic FROM `{$_TABLES['topics']}`" . $db->getPermSQL() . " ORDER BY sortnum ASC"
        );
        $topicRows = $stmt->fetchAll(Database::ASSOCIATIVE);

        if (count($topicRows) > 0) {
            $excludetopics .= ' (';
            $counter = 1;
            foreach($topicRows AS $T) {
                if ($counter > 1)  {
                    $excludetopics .= ' OR ';
                }
                $counter++;
                $excludetopics .= "tid = " . $db->conn->quote($T['tid']);
                $seltopics .= '<option value="' .$T['tid']. '"';
                if ($current_topic == $T['tid']) {
                    $seltopics .= ' selected="selected"';
                }
                $seltopics .= '>' . $T['topic'] . ' (' . $T['tid'] . ')' . '</option>' . LB;
            }
            $excludetopics .= ') ';
        }
    } else {
        $excludetopics = " (tid = ".$db->conn->quote($current_topic)
                            . ' OR alternate_tid = '.$db->conn->quote($current_topic) . ') ';
        $seltopics = COM_topicList ('tid,topic,sortnum', $current_topic, 2, true);
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
    $header_arr[] = array('text' => $LANG_ADMIN['alt_topic'], 'field' => 'alternate_tid', 'sort' => true);
    $header_arr[] = array('text' => $LANG24[34], 'field' => 'draft_flag', 'sort' => true, 'align' => 'center');
    $header_arr[] = array('text' => $LANG24[32], 'field' => 'featured', 'sort' => true, 'align' => 'center');
    $header_arr[] = array('text' => $LANG24[7], 'field' => 'username', 'sort' => true); //author
    $header_arr[] = array('text' => $LANG24[15], 'field' => 'date', 'sort' => true, 'align' => 'center'); //date
    if (SEC_hasRights ('story.ping') && ($_CONF['trackback_enabled'] ||
            $_CONF['pingback_enabled'] || $_CONF['ping_enabled'])) {
        $header_arr[] = array('text' => $LANG24[20], 'field' => 'ping', 'sort' => false, 'align' => 'center');
    }
    $header_arr[] = array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center');

    $defsort_arr = array('field' => 'date', 'direction' => 'desc');

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/story.php',
              'text' => $LANG_ADMIN['story_list'],'active' => true),
        array('url' => $_CONF['site_admin_url'] . '/story.php?newstory=x',
              'text' => $LANG_ADMIN['create_new']),
        );
        if ( SEC_inGroup('Root')) {
            $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/story.php?global=x',
                      'text' => $LANG24[111]);
        }
        $menu_arr[] = array('url' => $_CONF['site_admin_url'].'/index.php',
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
        'query_fields' => array('title', 'introtext', 'bodytext', 'sid', 'tid', 'alternate_tid'),
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
* Article Editor
*
* Displays the story entry form
*
* @param    Article     $article        Article object
* @param    string      $action         'preview', 'edit', 'moderate', 'draft'
* @param    bool        $preview        Preview mode
* @return   string      HTML for story editor
*
*/
function STORY_edit(Article $article, $action = '',$preview = false)
{
    global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG24, $LANG33, $LANG_ACCESS,
           $LANG_ADMIN, $MESSAGE,$_IMAGE_TYPE;

    $retval = '';

    switch ($action) {
        case 'edit' :
            $title = $LANG24[5];
            break;
        case 'new' :
            $title = $LANG24[5];
            if ($article->get('moderate') === 1) {
                $title = $LANG24[90];
            }
            break;

        default :
            $title = $LANG24[5];
            break;
    }

    $retval = STORY_adminMenu($action);

    // display any errors from previous attempts
    $errors = $article->getErrors();
    foreach($errors AS $errormsg) {
        $retval .= COM_showMessageText($errormsg,$LANG24[25],true,'error');
    }

    if ($action === 'new') {
        // need to setup any defaults for a new story that are
        // dynamic such as uid and owner id

        if ($article->get('id') === 0 && ($article->get('sid') === '' || $article->get('sid') === 0)) {
            $article->set('sid',COM_makesid());
        }
        if ($article->get('uid') == 1) {
            $article->set('uid',$_USER['uid']);
        }
        if ($article->get('uid') == 1) {
            $article->set('owner_id',$_USER['uid']);
        }
    }
    $article->set('moderate',$article->get('moderate'));

    $retval .= COM_startBlock ($title, '',COM_getBlockTemplate ('_admin_block', 'header'));
    $retval .= $article->editForm($action,$preview);
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/*
 * Save a story
 */

function STORY_save()
{
    global $_CONF, $_TABLES, $LANG_ADM_ACTIONS;

    $db = Database::getInstance();

    $article = new Article();

    $rc = $article->retrieveArticleFromVars($_POST);
    if ($rc !== Article::STORY_LOADED_OK) {
        COM_setMsg('There was an error retreiving the submitted article.','error',true);
        return STORY_list();
    }

    $rc = $article->save();

    if ($rc === false) {
        // new or existing?
        $mode = (int) $article->get('id') == 0 ? 'new' : 'edit';

        // display edit form with errors
        return STORY_edit($article,$mode);
    }

    // if saving from moderation queue - remove submission entry
    if ((int) $article->get('moderate') == 1 && $article->get('original_sid') != '') {
        $db->conn->delete(
            $_TABLES['storysubmission'],
            array('sid' => $article->get('original_sid')),
            array(Database::STRING)
        );
    }
    Cache::getInstance()->clear();

    AdminAction::write('system','article_save',
        sprintf($LANG_ADM_ACTIONS['article_save'],$article->get('sid'),$article->getDisplayItem('title')));

    COM_setMessage(9);

    return STORY_list();
}


/*
 * Displays the story admin menu
 */
function STORY_adminMenu($action)
{
    global $_CONF, $LANG_ADMIN, $LANG24, $_IMAGE_TYPE;

    USES_lib_admin();

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
        $menu_arr[] = array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']);

    $retval = ADMIN_createMenu($menu_arr, $LANG24[92],$_CONF['layout_url'] . '/images/icons/story.' . $_IMAGE_TYPE);

    return $retval;
}


$pageTitle = '';
$pageBody = '';
$action = '';
$sid = '';

$expected = array('edit','newstory','moderate','draft','clone','save','previewstory','deletestory','global','globalsave','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

if ($action == 'cancel') {
    if (isset($_POST['moderate']) && (int) $_POST['moderate'] == 1) {
        echo COM_refresh($_CONF['site_admin_url'].'/moderation.php');
    }
    $pageTitle = $LANG24[22];
    $action = 'list';
}

if (isset($_POST['sid'])) {
    $sid = COM_applyFilter($_POST['sid']);
} elseif (isset($_GET['sid'])) {
    $sid = COM_applyFilter($_GET['sid']);
}

$msg = COM_getMessage();

//$topic = (isset($_GET['topic'])) ? COM_applyFilter($_GET['topic']) : '';
//$type = (isset($_POST['type'])) ? COM_applyFilter($_POST['type']) : '';

$db = Database::getInstance();

switch ($action) {

    case 'moderate' :
        if (!isset($_GET['sid'])) {
            COM_setMsg('Invalid Story ID','error',true);
            echo COM_refresh($_CONF['site_admin_url'].'/moderation.php');
        } else {
            $sid = (string) filter_input(INPUT_GET,'sid',FILTER_SANITIZE_STRING);

            $article = new Article();
            if ($article->retrieveSubmission($sid) === Article::STORY_LOADED_OK) {
                $article->set('moderate',1);
                $article->set('original_sid',$article->get('sid'));
                $pageBody = STORY_edit($article, 'new');
            } else {
                $action = 'list';
                COM_setMsg('Invalid SID or permission issue','error',true);
                echo COM_refresh($_CONF['site_admin_url'].'/moderation.php');
            }
        }
        break;

    case 'edit':
        if (!isset($_GET['sid'])) {
            COM_setMsg('Invalid Story ID','error',true);
            $pageBody = STORY_list();
        } else {
            $sid = (string) filter_input(INPUT_GET,'sid',FILTER_SANITIZE_STRING);

            $article = new Article();
            if ($article->retrieveArticleFromDB($sid) === Article::STORY_LOADED_OK) {
                $pageBody = STORY_edit($article, 'edit');
            } else {
                $action = 'list';
                COM_setMsg('Invalid SID or permission issue');
                $pageBody = STORY_list();
            }
        }
        break;

    case 'newstory':
        $article = new Article();
        $pageBody = STORY_edit($article,'new');
        break;

    case 'previewstory' :
        if (SEC_checkToken()) {
            $article = new Article();

            // if an existing story - initialize from DB first, then overlay posted vars
            if (isset($_POST['id']) && $_POST['id'] !== 0) {
                $uniqueID = filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT);
                $sid = $db->getItem($_TABLES['stories'],'sid',array('id' => $uniqueID),array(Database::INTEGER));
                if ($sid !== false && $sid !== null) {
                    $article->retrieveArticleFromDB($sid);
                }
            }

            if ($article->retrieveArticleFromVars($_POST) !== Article::STORY_LOADED_OK) {
                print "error";exit;
            }

            if ((int) $_POST['id'] == 0) {
                $pageBody = STORY_edit($article,'new',true);
            } else {
                $pageBody = STORY_edit($article,'edit',true);
            }
        } else {
            $pageBody .= COM_showMessage(501);
            $pageBody = STORY_list();
        }
        break;

    case 'save' :
        if (SEC_checkToken()) {
            $db->conn->delete(
                $_TABLES['tokens'],
                array(
                    'owner_id' => $_USER['uid'],
                    'urlfor'   => 'advancededitor'
                ),
                array(Database::INTEGER,Database::STRING)
            );
            $pageBody = STORY_save();
        } else {
            $pageBody .= COM_showMessage(501);
            $pageBody = STORY_list();
        }
        break;

    case 'clone' :
        if (!isset($_GET['sid'])) {
            COM_setMsg($LANG24[42],'error',true);
            $pageBody = STORY_list();
        } else {
            $sid = (string) filter_input(INPUT_GET,'sid',FILTER_SANITIZE_STRING);

            $article = new Article();
            if ($article->retrieveArticleFromDB($sid) === Article::STORY_LOADED_OK) {
                // Initialize some fields back to defaults
                $article->set('sid',COM_makesid());
                $article->set('id',0);
                $article->set('date',$_CONF['_now']->toMySQL());
                $article->set('frontpage_date',null);
                $article->set('expire',null);
                if ((int) $article->get('cmt_close_flag') == 1) {
                    $cmtCloseDt = new \Date(time() + 2592000,$_USER['tzid']);
                    $article->set('comment_expire',$cmtCloseDt->format('Y-m-d H:i', true));
                } else {
                    $article->set('comment_expire',null);
                }
                $pageBody = STORY_edit($article, 'new');
            } else {
                $action = 'list';
                COM_setMsg($LANG24[42],'error',true);
                $pageBody = STORY_list();
            }
        }
        break;

    case 'deletestory':
        if (!isset($sid) || empty($sid)) {
            Log::write('system',Log::ERROR,'User ' . $_USER['username'] . ' attempted to delete a story, sid empty or null, sid=' . $sid);
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        } else if (SEC_checkToken()) {
            $rc = Article::delete($sid);
            if ($rc === true) {
                Cache::getInstance()->clear();
                COM_setMsg($MESSAGE[10],'info',false);
            }
        } else {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to a delete a story, sid=$sid and failed CSRF checks");
            $display = COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        $pageBody = STORY_list();
        break;

    case 'global' :
        $pageBody .= STORY_global();
        break;

    case 'globalsave' :
        $pageBody .= STORY_global_save();
        break;

    default:
        // purge any tokens we created for the advanced editor
        $db->conn->delete(
            $_TABLES['tokens'],
            array(
                'owner_id'  => $_USER['uid'],
                'urlfor' => 'advancededitor'
            ),
            array(
                Database::INTEGER,
                Database::STRING
            )
        );
        $pageTitle = $LANG24[22];
        $pageBody .= STORY_list();
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