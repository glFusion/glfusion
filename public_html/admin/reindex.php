<?php
/**
* glFusion CMS
*
* glFusion Search index Administrative Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017-2018 Lee Garner - lee AT leegarner DOT com
*
*/

require_once '../lib-common.php';
require_once './auth.inc.php';
require_once $_CONF['path'].'system/search/admin.inc.php';
require_once $_CONF['path'].'system/search/reindex.ajax.php';

USES_lib_admin();

function SEARCH_reindex()
{
    global $_CONF, $_CONF, $_PLUGINS, $LANG01, $LANG_ADMIN, $LANG_SEARCH_UI, $LANG_SEARCH_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    $T = new \Template($_CONF['path_layout'] . 'admin/search');
    $T->set_file('page','reindex.thtml');

    $retval .= SEARCH_adminMenu('reindex');

    $T->set_var('lang_title',$LANG_SEARCH_ADMIN['reindex_title']);

    $T->set_var('lang_conversion_instructions', $LANG_SEARCH_ADMIN['index_instructions']);

    $T->set_block('page', 'contenttypes', 'ct');

    $plugintypes = array();
    $plugintypes[] = 'article';
    $plugintypes = array_merge($plugintypes,$_PLUGINS);
    sort($plugintypes);

    $T->set_var('content_type','article');
    $T->parse('ct', 'contenttypes',true);
    foreach ($plugintypes as $pi_name) {
        if (function_exists('plugin_getiteminfo_' . $pi_name)) {
            $T->set_var('content_type',$pi_name);
            $T->parse('ct', 'contenttypes',true);
        }
    }
    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'].'/reindex.php',
        'lang_index'        => $LANG_SEARCH_ADMIN['reindex_button'],
        'lang_cancel'       => $LANG_ADMIN['cancel'],
        'lang_ok'           => $LANG01['ok'],
        'lang_empty'        => $LANG_SEARCH_ADMIN['empty_table'],
        'lang_indexing'     => $LANG_SEARCH_ADMIN['indexing'],
        'lang_success'      => $LANG_SEARCH_ADMIN['success'],
        'lang_ajax_status'  => $LANG_SEARCH_ADMIN['index_status'],
        'lang_retrieve_content_types' => $LANG_SEARCH_ADMIN['retrieve_content_types'],
        'lang_error_header' => $LANG_SEARCH_ADMIN['error_heading'],
        'lang_no_errors'    => $LANG_SEARCH_ADMIN['no_errors'],
        'lang_error_getcontenttypes' => $LANG_SEARCH_ADMIN['error_getcontenttypes'],
        'lang_current_progress' => $LANG_SEARCH_ADMIN['current_progress'],
        'lang_overall_progress' => $LANG_SEARCH_ADMIN['overall_progress'],
        'lang_remove_content_1' => $LANG_SEARCH_ADMIN['remove_content_1'],
        'lang_remove_content_2' => $LANG_SEARCH_ADMIN['remove_content_2'],
        'lang_content_type' => $LANG_SEARCH_ADMIN['content_type'],
        'lang_remove_fail'  => $LANG_SEARCH_ADMIN['remove_fail'],
        'lang_retrieve_content_list' => $LANG_SEARCH_ADMIN['retrieve_content_list'],
    ));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

// main driver
$action = '';
$expected = array('reindex','getcontenttypes','getcontentlist','index','removeoldcontent','complete','contentcomplete');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

if ( isset($_POST['cancelbutton'])) {
    COM_refresh($_CONF['site_admin_url'].'/index.php');
}

switch ($action) {
    case 'reindex':
        $pagetitle = $LANG_SEARCH_UI['reindex_title'];
        $page .= SEARCH_reindex();
        break;
    case 'getcontenttypes' :
        // return json encoded list of content types
        SEARCH_getContentTypesAjax();
        break;
    case 'removeoldcontent' :
        SEARCH_removeOldContentAjax();
        break;
    case 'getcontentlist' :
        // return list of all content type ids
        SEARCH_getContentListAjax();
        break;
    case 'index' :
        // index a content item via ajax
        SEARCH_indexContentItemAjax();
        break;
    case 'contentcomplete' :
        SEARCH_completeContentAjax();
        break;
    case 'complete' :
        SEARCH_completeAjax();
        break;
    default :
        $page = SEARCH_reindex();
        break;
}

$display  = COM_siteHeader('menu', $LANG_SEARCH_ADMIN['search_admin']);
$display .= $page;
$display .= COM_siteFooter();
echo $display;
?>
