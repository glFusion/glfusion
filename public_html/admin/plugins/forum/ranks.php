<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | ranks.php                                                                |
// |                                                                          |
// | Forum Plugin Rankings administration                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2016-2017 by the following authors:                        |
// |                                                                          |
// | Lee Garner             lee AT leegarner DOT com                          |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
USES_forum_functions();
USES_forum_admin();

if (!SEC_hasRights('forum.edit')) {
    COM_404();
    exit;
}
$action = 'list';
$expected = array(
    'save', 'delete', 'cancel', 'delete', 'delitem',
    'edit', 'list',
);
foreach ($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
    	$action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}

// Rank ID can come from $_POST or $_GET
$posts = isset($_REQUEST['posts']) ? (int)$_REQUEST['posts'] : 0;
$self = $_CONF['site_admin_url'] . '/plugins/forum/ranks.php';
$content = '';
switch ($action) {
case 'delete':
    \Forum\Rank::Delete($posts);
    echo COM_refresh($self);
    break;

case 'delitem':
    if (is_array($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $posts) {
            \Forum\Rank::Delete($posts);
        }
    }
    echo COM_refresh($self);
    break;

case 'edit':
    $B = new \Forum\Rank($posts);
    $content = $B->Edit();
    break;

case 'save':
    $B = new \Forum\Rank($_POST['posts']);
    $errors = $B->Save($_POST);
    if (empty($errors)) {
        COM_setMsg($LANG_GF01['rank_updated']);
        echo COM_refresh($self);
        exit;
    } else {
        COM_setMsg($errors, 'error');
        $content .= $B->Edit();
    }
    break;

case 'list':
default:
    $content .= '<p>[ <a href="' . $_CONF['site_admin_url'] .
    '/plugins/forum/ranks.php?edit">'.$LANG_GF01['add_rank'].'</a> ]</p>';
    $content .= FF_rank_AdminList();
    break;
}
$display = FF_siteHeader();
$display .= FF_navbar($navbarMenu, $LANG_GF01['ranks']);
$display .= $content;
$display .= FF_siteFooter();
echo $display;
exit;

/**
*   Create the list
*/
function FF_rank_AdminList()
{
    global $LANG_ADMIN, $_TABLES, $_CONF, $_USER, $LANG_GF01, $LANG_GF93;

    USES_lib_admin();

    $uid = (int)$_USER['uid'];
    $retval = '';
    $form_arr = array();

    $header_arr = array(
        array(  'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center'),
        array(  'text'  => $LANG_GF93['posts'],
                'field' => 'posts',
                'sort'  => true),
        array(  'text'  => $LANG_GF01['DESCRIPTION'],
                'field' => 'dscp',
                'sort'  => false),
        array(  'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center'),
    );

    $options = array('chkdelete' => 'true', 'chkfield' => 'posts');
    $defsort_arr = array('field' => 'posts', 'direction' => 'asc');
    $query_arr = array(
            'table' => 'ff_ranks',
            'sql' => "SELECT* FROM {$_TABLES['ff_ranks']}",
            //'query_fields' => array(),
    );
    $text_arr = array(
            //'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/plugins/forum/ranks.php',
    );

    $retval .= ADMIN_list('ranks', 'FF_getAdminField_ranks', $header_arr,
                $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr);
    return $retval;
}


/**
*   Get the correct display for a single field in the banner admin list
*
*   @param  string  $fieldname  Field variable name
*   @param  string  $fieldvalue Value of the current field
*   @param  array   $A          Array of all field names and values
*   @param  array   $icon_arr   Array of system icons
*   @return string              HTML for field display within the list cell
*/
function FF_getAdminField_ranks($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_ACCESS, $LANG_GF01;

    $retval = '';
    $base_url = $_CONF['site_admin_url'] . '/plugins/forum/ranks.php';

    switch($fieldname) {
    case 'edit':
        $retval = COM_createLink(
                '<i class="uk-icon uk-icon-edit"></i>',
                $base_url . '?edit=x&amp;posts=' .$A['posts']
                );
        break;

    case 'delete':
        $retval = COM_createLink('<i class="uk-icon uk-icon-trash" style="color:red;"></i>',
                "$base_url?posts={$A['posts']}&delete",
                array(
                     'onclick' => "return confirm('{$LANG_GF01['DELETECONFIRM']}');",
                ) );
        break;

    case 'posts':
        $retval = (int)$fieldvalue;
        break;

    default:
        $retval = $fieldvalue;
        break;
    }
    return $retval;
}

?>
