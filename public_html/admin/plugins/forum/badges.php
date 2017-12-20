<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | badges.php                                                               |
// |                                                                          |
// | Forum Plugin Badges administration                                       |
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
    'save', 'delete', 'cancel', 'move', 'delete', 'delitem',
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

$fb_id = isset($_GET['fb_id']) ? (int)$_GET['fb_id'] : 0;
$self = $_CONF['site_admin_url'] . '/plugins/forum/badges.php';
$content = '';
switch ($action) {
case 'delete':
    \Forum\Badge::Delete($_POST['fb_id']);
    echo COM_refresh($self);
    break;

case 'delitem':
    if (is_array($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $fb_id) {
            \Forum\Badge::Delete($fb_id);
        }
    }
    echo COM_refresh($self);
    break;

case 'edit':
    $B = new \Forum\Badge($fb_id);
    $content = $B->Edit();
    break;

case 'move':
    \Forum\Badge::Move($fb_id, $actionval);
    echo COM_refresh($self);
    break;

case 'save':
    $B = new \Forum\Badge($_POST['fb_id']);
    $errors = $B->Save($_POST);
    if (empty($errors)) {
        COM_setMsg($LANG_GF01['badge_updated']);
    } else {
        COM_setMsg($errors, 'error');
    }
    echo COM_refresh($self);
    exit;
    break;

case 'list':
default:
    $content .= '<p>[ <a href="' . $_CONF['site_admin_url'] .
    '/plugins/forum/badges.php?edit">'.$LANG_GF01['add_badge'].'</a> ]</p>';
    $content .= FF_badge_AdminList();
    break;
}

$display = FF_siteHeader();
$display .= FF_navbar($navbarMenu, $LANG_GF01['badges']);
$display .= $content;
$display .= FF_siteFooter();
echo $display;
exit;

/**
*   Create the list
*/
function FF_badge_AdminList()
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
        array(  'text'  => $LANG_ADMIN['enabled'],
                'field' => 'fb_enabled',
                'align' => 'center',
                'sort'  => false),
        array(  'text'  => $LANG_GF93['order'],
                'field' => 'fb_order',
                'sort'  => false),
        array(  'text'  => $LANG_GF01['badge_grp'],
                'field' => 'fb_grp',
                'sort'  => false),
        array(  'text'  => $LANG_GF01['site_grp'],
                'field' => 'grp_name',
                'sort'  => false),
        array(  'text'  => $LANG_GF01['badge_img'],
                'field' => 'fb_image',
                'sort'  => false),
        array(  'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center'),
    );

    $options = array('chkdelete' => 'true', 'chkfield' => 'fb_id');

    $defsort_arr = array('field' => '', 'direction' => 'asc');

    $query_arr = array('table' => 'ff_badges',
            'sql' => "SELECT b.*, g.grp_name
                    FROM {$_TABLES['ff_badges']} b
                    LEFT JOIN {$_TABLES['groups']} g
                        ON g.grp_id = b.fb_gl_grp
                    ORDER BY b.fb_grp ASC, b.fb_order ASC",
            'query_fields' => array('fb_gl_grp'),
    );
    $text_arr = array(
            //'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/plugins/forum/badges.php',
    );

    $retval .= ADMIN_list('badges', 'FF_getAdminField_badges', $header_arr,
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
function FF_getAdminField_badges($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_ACCESS, $LANG_GF01;

    $retval = '';

    $base_url = $_CONF['site_admin_url'] . '/plugins/forum/badges.php';

    switch($fieldname) {
    case 'edit':
        $retval = COM_createLink(
                '<i class="uk-icon uk-icon-edit"></i>',
                $base_url . '?edit=x&amp;fb_id=' .$A['fb_id']
                );
        break;

    case 'fb_order':
        $retval .= COM_createLink(
            '<i class="uk-icon uk-icon-arrow-up"></i>',
            $base_url . '?move=up&fb_id=' . $A['fb_id']
        );
        $retval .= '&nbsp;' . COM_createLink(
            '<i class="uk-icon uk-icon-arrow-down"></i>',
            $base_url . '?move=down&fb_id=' . $A['fb_id']
        );
        break;


    case 'fb_enabled':
        /*if ($fieldvalue == 1) {
            $switch = 'checked="checked"';
        } else {
            $switch = '';
        }
        $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"badge_ena_check\"
                id=\"togena{$A['bid']}\"
                onclick='FF_toggleEnabled(this, \"{$A['fb_id']}\",\"badge\");' />\n";*/
        if ($fieldvalue) {
            $retval .= '<i class="uk-icon uk-icon-check-square-o" style="color:green;"></i>';
        } else {
            $retval .= '<i class="uk-icon uk-icon-square-o" style=color:grey;"></i>';
        }
        break;

    case 'delete':
        $retval = COM_createLink('<i class="uk-icon uk-icon-trash" style="color:red;"></i>',
                "$base_url?fb_id={$A['fb_id']}&delete",
                array(
                     'onclick' => "return confirm('{$LANG_GF01['DELETECONFIRM']}');",
                ) );
        break;

    default:
        $retval = $fieldvalue;
        break;
    }
    return $retval;
}

?>
