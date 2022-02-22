<?php
/**
* glFusion CMS
*
* Search Admin Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Admin\AdminAction;

USES_lib_admin();

$display = '';
$pi_title = $LANG_SEARCH_ADMIN['search_admin'];

if (!SEC_hasRights('search.admin')) {
    COM_accessLog("User {$_USER['username']} tried to access the Search admin screen.");
    COM_404();
    exit;
}

/**
*   Create the main menu
*
*   @param  string  $sel    Selected option
*   @return string  HTML for menu area
*/
function SEARCH_adminMenu($sel = 'default')
{

    global $_CONF, $LANG_ADMIN, $LANG_SEARCH, $LANG_SEARCH_ADMIN;

    $retval = '';

    $T = new Template($_CONF['path_layout'].'admin/search');
    $T->set_file('admin', 'admin_header.thtml');

    $token = SEC_createToken();

    $menu_arr = array(
        array(
                'url'  => $_CONF['site_admin_url'].'/search.php',
                'text' => $LANG_SEARCH_ADMIN['search_admin'],
                'active' => $sel == 'counters' ? true : false,
                ),
        array(
                'url'   => $_CONF['site_admin_url'].'/reindex.php',
                'text'  => $LANG_SEARCH_ADMIN['reindex_title'],
                'active' => $sel == 'reindex' ? true : false,
                ),
        array(
                'url' => $_CONF['site_admin_url'].'/index.php',
                'text' => $LANG_ADMIN['admin_home']
                )
    );

    $explanation =  $LANG_SEARCH_ADMIN['hlp_' . $sel];

    $T->set_var('start_block', COM_startBlock($LANG_SEARCH_ADMIN['search_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                $explanation,
                $_CONF['layout_url'] . '/images/icons/search.png')
    );

    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
*   View the search queries made by guests.
*
*   @return string  Admin list of search terms and counts
*/
function SEARCH_admin_terms()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SEARCH_ADMIN;

    $retval = '';
    $filter = '';

    $token = SEC_createToken();

    $header_arr = array(      # display 'text' and use table field 'field'
        array(
            'text' => '',$LANG_SEARCH_ADMIN['search_terms'],
            'field' => 'term',
            'sort' => true,
        ),
        array(
            'text' => $LANG_SEARCH_ADMIN['queries'],
            'field' => 'hits',
            'sort' => true,
            'align' => 'right',
        ),
        array(
            'text' => $LANG_SEARCH_ADMIN['results'],
            'field' => 'results',
            'sort' => true,
            'align' => 'right',
        ),
    );

    $defsort_arr = array('field' => 'hits', 'direction' => 'desc');

    $retval .= SEARCH_adminMenu('counters');

    $text_arr = array(
        'has_extras' => true,
        'form_url' => $_CONF['site_admin_url'].'/search.php?counters=x',
    );
    $filter .= '<button type="submit" name="clearcounters" style="float:left;" class="uk-button uk-button-danger">' .
        $LANG_SEARCH_ADMIN['clear_counters'] . '</button>';

    $query_arr = array('table' => 'search_counters',
        'sql' => "SELECT term, hits, results FROM {$_TABLES['search_stats']}",
        'query_fields' => array('term'),
        'default_filter' => 'WHERE 1=1',
    );
    $retval .= ADMIN_list('search', 'SEARCH_getListField_counters', $header_arr,
                    $text_arr, $query_arr, $defsort_arr, $filter, $token, '', '');

    return $retval;
}


/**
*   Get the value for list fields in admin lists.
*   For the search term list, just returns the field values.
*
*   @param  string  $fieldname  Name of field
*   @param  mixed   $fieldvalue Field value
*   @param  array   $A          Complete database record
*   @param  array   $icon_arr   Icon array (not used)
*   @param  string  $token      Admin token
*/
function SEARCH_getListField_counters($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $LANG_ACCESS, $LANG_ADMIN;

    $retval = '';

    switch($fieldname) {
        case 'term':
            $url = $_CONF['site_url'].'/search.php?mode=search&amp;q=' . urlencode($fieldvalue).'&amp;nc=x';
            $retval = COM_createlink($fieldvalue,$url);
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}


$view = '';
$action = '';
$expected = array(
    // Actions
    'genindex', 'clearcounters',
    // Views, no action
    'counters',
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        break;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
        break;
    }
}

$content = '';
$message = '';
$view = '';
switch ($action) {
case 'genindex':
    if (!isset($_POST['pi']) || empty($_POST['pi'])) {
        break;
    }
    foreach ($_POST['pi'] as $pi_name => $checked) {
        $func = 'search_IndexAll_' . $pi_name;
        if (function_exists($func)) {
            $count = $func();
            $message .= "<br>$pi_name: Indexed $count Items";
        }
    }
    break;
case 'clearcounters':
    $db = \glFusion\Database\Database::getInstance();
    $db->conn->query("TRUNCATE `{$_TABLES['search_stats']}`");
    $view = 'counters';
    AdminAction::write('system','search_engine',"Cleared Search Counters");
    break;

default:
    $view = $action;
    break;
}

switch ($view) {
case 'counters':
default:
    $content .= SEARCH_admin_terms();
    break;
}

$display .= COM_siteHeader('menu', $pi_title);
$display .= $content;
$display .= $message;
$display .= COM_siteFooter();

echo $display;

?>
