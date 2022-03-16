<?php
/**
 * Endpoint to administer uesr badges.
 *
 * @author      Lee Garner
 * @copyright   Copyright (c) 2022 Mark R. Evans mark AT glfusion DOT org
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

require_once __DIR__ . '/../lib-common.php';
require_once __DIR__ . '/auth.inc.php';
use glFusion\Badges\Badge;
use glFusion\FieldList;

// Make sure user has access to this page
if (!SEC_hasRights('user.edit')) {
    Log::logAccessViolation('User Preference Editor');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
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

// Badge ID can come from $_POST or $_GET
//$bid = isset($_REQUEST['bid']) ? (int)$_REQUEST['bid'] : 0;
$self = $_CONF['site_admin_url'] . '/badges.php';
$content = '';
switch ($action) {
case 'delete':
    Badge::Delete((int)$actionval);
    echo COM_refresh($self);
    break;

case 'delitem':
    if (is_array($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $bid) {
            Badge::Delete((int)$bid);
        }
    }
    echo COM_refresh($self);
    break;

case 'edit':
    $B = Badge::getById((int)$actionval);
    $content = $B->Edit();
    break;

case 'move':
    Badge::Move((int)$_REQUEST['bid'], $actionval);
    echo COM_refresh($self);
    break;

case 'save':
    $B = Badge::getById((int)$_POST['bid']);
    $errors = $B->Save($_POST);
    if (empty($errors)) {
        COM_setMsg($LANG_ADMIN['item_updated']);
    } else {
        COM_setMsg($errors, 'error');
    }
    echo COM_refresh($self);
    exit;
    break;

case 'list':
default:
    $content .= Badge_Adminlist();
    break;
}

$display = COM_siteHeader();
$display .= badges_AdminMenu($action);
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;


/**
 * Create a menu for the admin interface.
 *
 * @param   string  $view   View to mark as active
 * @return  string      HTML for admin menu
 */
function badges_AdminMenu($view='list')
{
    global $_CONF, $LANG_ADMIN, $LANG01;

    USES_lib_admin();

    $menu_arr = array(
        array(
            'url' => $_CONF['site_admin_url'] . '/badges.php',
            'text' => $LANG01[132],
            'active'=> $view == 'list',
        ),
        array(
            'url' => $_CONF['site_admin_url'] . '/badges.php?edit',
            'text' => $LANG_ADMIN['create_new'],
        ),
        array(
            'url' => $_CONF['site_admin_url'].'/index.php',
            'text' => $LANG_ADMIN['admin_home'],
        ),
    );
    $retval = ADMIN_createMenu(
        $menu_arr,
        '',
        $_CONF['layout_url'] . '/images/icons/badges.png',
    );
    return $retval;
}


/**
 * Create the list.
 *
 * @return  string      HTML for admin list
 */
function Badge_AdminList()
{
    global $LANG_ADMIN, $_TABLES, $_CONF, $_USER;

    USES_lib_admin();

    $T = new Template($_CONF['path_layout'] . 'admin/badges/');
    $T->set_file('adminlist', 'adminlist.thtml');

    $header_arr = array(
        array(
            'text'  => $LANG_ADMIN['edit'],
            'field' => 'edit',
            'sort'  => false,
            'align' => 'center',
        ),
        array(
            'text'  => $LANG_ADMIN['enabled'],
            'field' => 'enabled',
            'align' => 'center',
            'sort'  => false,
        ),
        array(
            'text'  => $LANG_ADMIN['order'],
            'field' => 'sortorder',
            'sort'  => false,
        ),
        array(
            'text'  => $LANG_ADMIN['badge_grp'],
            'field' => 'badge_grp',
            'sort'  => false,
        ),
        array(
            'text'  => $LANG_ADMIN['site_grp'],
            'field' => 'grp_name',
            'sort'  => false,
        ),
        array(
            'text'  => $LANG_ADMIN['badge_image'],
            'field' => 'data',
            'sort'  => false,
        ),
        array(
            'text'  => $LANG_ADMIN['delete'],
            'field' => 'delete',
            'sort'  => false,
            'align' => 'center',
        ),
    );

    $form_arr = array();
    $options = array('chkdelete' => 'true', 'chkfield' => 'bid');
    $defsort_arr = array('field' => '', 'direction' => 'asc');
    $query_arr = array(
        'table' => 'badges',
        'sql' => "SELECT b.*, g.grp_name
            FROM {$_TABLES['badges']} b
            LEFT JOIN {$_TABLES['groups']} g
                ON g.grp_id = b.gl_grp
            ORDER BY b.badge_grp ASC, b.sortorder ASC",
        'query_fields' => array('gl_grp'),
    );
    $text_arr = array(
        //'has_extras' => true,
        'form_url' => $_CONF['site_admin_url'] . '/badges.php',
    );

    $list = ADMIN_list(
        'badges', 'Badges_getAdminField',
        $header_arr, $text_arr, $query_arr, $defsort_arr,
        '', '', $options, $form_arr
    );
    $T->set_var(array(
        'lang_create_new' => $LANG_ADMIN['create_new'],
        'admin_list' => $list,
    ) );
    $T->parse('output', 'adminlist');
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}


/**
 * Get the correct display for a single field in the banner admin list.
 *
 * @param   string  $fieldname  Field variable name
 * @param   string  $fieldvalue Value of the current field
 * @param   array   $A          Array of all field names and values
 * @param   array   $icon_arr   Array of system icons
 * @return  string              HTML for field display within the list cell
 */
function Badges_getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_ACCESS, $LANG_ADMIN;

    $retval = '';

    $base_url = $_CONF['site_admin_url'] . '/badges.php';

    switch($fieldname) {
    case 'edit':
        $retval = FieldList::edit(
            array(
                'url' => $base_url . '?edit=' .$A['bid'],
            )
        );
        break;

    case 'sortorder':
        $retval .= FieldList::up(
            array(
                'url' => $base_url . '?move=up&bid=' . $A['bid'],
            )
        );
        $retval .= FieldList::down(
            array(
                'url' => $base_url . '?move=down&bid=' . $A['bid'],
            )
        );
        break;


    case 'enabled':
        $retval = FieldList::checkbox(array(
            'name' => 'badge_ena_check',
            'id' => 'badges_enabled_'. $A['bid'],
            'checked' => $fieldvalue == 1,
            'onclick' => "ajaxtoggle(this,'{$A['bid']}','enabled','badges');",
        ) );
        break;

    case 'delete':
        $retval = FieldList::delete(
            array(
                'delete_url' => $base_url.'?delete='.$A['bid'],
                'attr' => array(
                    'title'   => $LANG_ADMIN['delete'],
                    'onclick' => "return confirm('{$LANG_ADMIN['delete_confirm']}');"
                ),
            )
        );
        break;

    case 'data':
        $badge = new Badge($A);
        $retval = $badge->getBadgeHTML();
        break;

    default:
        $retval = $fieldvalue;
        break;
    }
    return $retval;
}

