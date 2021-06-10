<?php
/**
* glFusion CMS
*
* Actions
*
* Displays the administrative actions performed on the site
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2019-2020 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\Formatter;
use \glFusion\Admin\AdminAction;

// Only let admin users access this page
if (!SEC_hasRights ('actions.admin')) {
    Log::logAccessViolation('Admin Actions');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

USES_lib_admin();

/*
 * Display admin list of all Admin Actions
*/
function listActions()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACTIONS, $_IMAGE_TYPE;

    $retval = '';

    $db = Database::getInstance();

    $header_arr = array(
        array('text' => $LANG_ACTIONS['date'], 'field' => 'datetime',   'sort' => true, 'align' => 'left'),
        array('text' => $LANG_ACTIONS['module'], 'field' => 'module',   'sort' => true, 'align' => 'left'),
        array('text' => $LANG_ACTIONS['action'], 'field' => 'action',   'sort' => true, 'align' => 'left'),
        array('text' => $LANG_ACTIONS['description'], 'field' => 'description', 'sort' => false, 'align' => 'left'),
        array('text' => $LANG_ACTIONS['user'], 'field' => 'user','sort' => true, 'align' => 'center'),
        array('text' => 'IP', 'field' => 'ip','sort' => true, 'align' => 'center'),
    );

    $defsort_arr = array('field'     => 'datetime',
                         'direction' => 'DESC');
    $text_arr = array(
            'form_url'      => $_CONF['site_admin_url'] . '/actions.php',
            'help_url'      => '',
            'has_search'    => true,
            'has_limit'     => true,
            'has_paging'    => true,
            'no_data'       => $LANG_ACTIONS['no_data'],
    );

    $sql = "SELECT * FROM {$_TABLES['admin_action']} WHERE 1=1 ";

    $query_arr = array('table' => 'admin_action',
                        'sql' => $sql,
                        'query_fields' => array('module','description'),
                        'default_filter' => '',
                        'group_by' => "");

    $option_arr = array('chkselect' => false,
            'chkfield' => 'datetime',
            'chkname' => 'datetime',
            'chkminimum' => 0,
            'chkall' => true,
            'chkactions' => ''
    );

    $token = SEC_createToken();

    $formfields = '
        <input name="action" type="hidden" value="delete">
        <input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'">
    ';

    $form_arr = array(
        'top' => $formfields
    );

    $retval .= ADMIN_list('actionlist', 'Actions_getListField', $header_arr,
                $text_arr, $query_arr, $defsort_arr, "", "", $option_arr, $form_arr);

    return $retval;
}


function Actions_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token = "")
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_ACTIONS, $LANG04, $LANG28, $_IMAGE_TYPE;

    $retval = '';

    $filter = \sanitizer::getInstance();

    return $filter->htmlspecialchars($fieldvalue);
}

function actions_admin_menu($action = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACTIONS;

    $retval = '';

    $menu_arr = array(
        array( 'url' => $_CONF['site_admin_url'].'/actions.php','text' => $LANG_ACTIONS['label'],'active' => true),
        array( 'url' => $_CONF['site_admin_url'].'/index.php', 'text' => $LANG_ADMIN['admin_home'])
    );

    $retval = '<h2>'.$LANG_ACTIONS['label'].'</h2>';

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_ACTIONS['help_text'],
        $_CONF['layout_url'] . '/images/icons/actions.png'
    );

    return $retval;
}

$page = '';
$display = '';

if (isset($_CONF['enable_admin_actions']) && $_CONF['enable_admin_actions'] == 1 && SEC_hasRights('actions.admin')) {
    $page = listActions();
} else {
    $page = '<div class="uk-panel uk-panel-box"><h2>Admin Actions are currently disabled in the configuration</h2></div>';
}

$display  = COM_siteHeader ('menu', $LANG_ACTIONS['label']);
$display .= actions_admin_menu();
$display .= $page;
$display .= COM_siteFooter (false);
echo $display;

?>
