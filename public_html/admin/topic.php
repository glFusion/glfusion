<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | topic.php                                                                |
// |                                                                          |
// | glFusion topic administration page.                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
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

function TOPIC_menu($action = '', $title = '')
{
    global $_CONF, $LANG_ADMIN, $LANG27, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';

    if ( $action == 'edit' ) {
        $lang_create_or_edit = $LANG_ADMIN['edit'];
    } else {
        $lang_create_or_edit = $LANG_ADMIN['create_new'];
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/topic.php',
              'text' => $LANG_ADMIN['topic_list'],'active'=> ($action == '' || $action == 'list') ? true : false ),
        array('url' => $_CONF['site_admin_url'] . '/topic.php?edit=x',
              'text' => $lang_create_or_edit,'active'=> ($action == 'edit') ? true : false),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($title, '', COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG27[9],
        $_CONF['layout_url'] . '/images/icons/topic.' . $_IMAGE_TYPE
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
 * return a field value for the topic administration list
 *
 */
function TOPIC_getListField($fieldname, $fieldvalue, $A, $icon_arr, $extra)
{
    global $_CONF, $LANG_ADMIN, $LANG27, $_IMAGE_TYPE;

    $retval = false;
    $token = $extra['token'];
    $topic_count = $extra['topic_count'];

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

            case 'sortnum':
                if ($fieldvalue > 10) {
                    $retval .= COM_createLink(
                        '<img src="' . $_CONF['layout_url'] .
                        '/images/up.png" height="16" width="16" border="0" />',
                        $_CONF['site_admin_url'] . '/topic.php?move=up&tid=' . $A['tid']
                    );
                } else {
                    $retval .= '<img src="' . $_CONF['layout_url'] .
                        '/images/blank.gif" height="16" width="16" border="0" />';
                }
                if ($fieldvalue < $topic_count) {
                    $retval .= COM_createLink(
                        '<img src="' . $_CONF['layout_url'] .
                            '/images/down.png" height="16" width="16" border="0" />',
                        $_CONF['site_admin_url'] . '/topic.php?move=down&tid=' . $A['tid']
                        );
                } else {
                    $retval .= '<img src="' . $_CONF['layout_url'] .
                        '/images/blank.gif" height="16" width="16" border="0" />';
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

//    $retval .= COM_startBlock ($LANG27[8], '', COM_getBlockTemplate ('_admin_block', 'header'));

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
    $extra = array(
        'token' => $token,
        'topic_count' => count(Topic::All()) * 10,
    );

    $retval .= ADMIN_list('topics','TOPIC_getListField',
        $header_arr,$text_arr,$query_arr,$defsort_arr,'',$extra,'', $form_arr);

//    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;

}

// MAIN
$display = '';
$page    = '';
$title   = $LANG27[8];

$action = '';
$expected = array('edit','save','delete','cancel','move');
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
        $title   = $LANG27[1];
        $T = new Topic($tid);
        $page = $T->Edit();
        break;

    case 'save':
        if (SEC_checkToken()) {
            $tid = isset($_POST['old_tid']) ? $_POST['old_tid'] : '';
            $T = new Topic($tid);
            $status = $T->Save($_POST);
            if ( !$status ) {
                $page = $T->Edit($_POST);
                break;
            }
            $c = glFusion\Cache::getInstance()->deleteItemsByTag('story');
            echo COM_refresh($_CONF['site_admin_url'] . '/topic.php');
        } else {
            $page = $T->Edit($_POST);
        }
        break;

    case 'delete':
        if (!empty($tid) && SEC_checkToken()) {
            $T = new Topic($tid);
            if ($T) {
                $T->Delete();
            }
        }
        echo COM_refresh($_CONF['site_admin_url'] . '/topic.php');
        break;

    case 'move':
        $dir = $_GET['move'];
        Topic::Move($tid, $dir);
        COM_refresh($_CONF['site_admin_url'] . '/topic.php');
        break;

    default:
        $page = TOPIC_list();
        break;

}

$display = COM_siteHeader('menu', $LANG27[1]);

$display .= TOPIC_menu($action, $title);

$msg = COM_getMessage();
$display .= ($msg > 0) ? COM_showMessage($msg) : '';

$display .= $page;
$display .= COM_siteFooter();

echo $display;

?>
