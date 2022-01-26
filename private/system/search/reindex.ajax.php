<?php
/**
* glFusion CMS
*
* glFusion Search Engine Reindexer (all AJAX based)
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

//$_POST = $_GET;

function SEARCH_getContentTypesAjax()
{
    global $_PLUGINS;

    if ( !COM_isAjax()) die();

    $contentTypes = array();
    $retval = array();

    $contentTypes[] = 'article';

    foreach ($_PLUGINS as $pi_name) {
        if (function_exists('plugin_getiteminfo_' . $pi_name)) {
            $contentTypes[] = $pi_name;
        }
    }

    $retval['errorCode'] = 0;
    $retval['contenttypes'] = $contentTypes;

    $retval['statusMessage'] = 'Initialization Successful';

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function SEARCH_removeOldContentAjax()
{
    global $_PLUGINS;

    if ( !COM_isAjax()) die();

    if ( !isset($_POST['type'])) die();

    $type = COM_applyFilter($_POST['type']);

    if ( empty($type) || $type == "" ) die();

    $contentList = array();
    $retval = array();

    \glFusion\Search::Removeall($type);

    $retval['errorCode'] = 0;
    $retval['statusMessage'] = 'Old Entries Purged';

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function SEARCH_getContentListAjax()
{
    global $_CONF, $_PLUGINS;

    if ( !COM_isAjax()) die();

    if ( !isset($_POST['type'])) die();

    $type = COM_applyFilter($_POST['type']);

    $contentList = array();
    $retval = array();

    $infoFields = 'id,search,search_index';
    if ($_CONF['search_summarize_discussions'] == true) {
        $infoFields = 'summary,id,search,search_index';
    }

    $rc = PLG_getItemInfo($type,'*',$infoFields);
    foreach ( $rc AS $id ) {
        $contentList[] = $id;
    }

    \glFusion\Search::Removeall($type);

    $retval['errorCode'] = 0;
    $retval['contentlist'] = $contentList;
    $retval['statusMessage'] = 'Content List Successful';

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function SEARCH_indexContentItemAjax()
{
    global $_CONF, $_PLUGINS;

    if ( !COM_isAjax()) die();

    if ( !isset($_POST['type'])) die();
    if ( !isset($_POST['id'])) die();


    $type = COM_applyFilter($_POST['type']);
    $id   = COM_applyFilter($_POST['id']);

    $contentList = array();
    $retval = array();

    $infoFields = 'id,date,title,searchidx,author,author_name,hits,perms,search_index,reindex,status';
    if ($_CONF['search_summarize_discussions'] == true) {
        $infoFields = 'summary,id,date,title,searchidx,author,author_name,hits,perms,search_index,reindex,status';
    }
    $contentInfo = PLG_getItemInfo($type,$id,'summary,id,date,title,searchidx,author,author_name,hits,perms,search_index,reindex,status');

    if ( is_array($contentInfo) && count($contentInfo) > 0 &&
            (!isset($contentInfo['status']) || $contentInfo['status'] == 1) ) {
        $props = array(
            'item_id' => $id,
            'type'  => $type,
            'title' => $contentInfo['title'],
            'content' => $contentInfo['searchidx'],
            'date' => $contentInfo['date'],
            'author' => $contentInfo['author'],
            'author_name' => $contentInfo['author_name'],
            'perms' => array(
                'owner_id' => $contentInfo['perms']['owner_id'],
                'group_id' => $contentInfo['perms']['group_id'],
                'perm_owner' => $contentInfo['perms']['perm_owner'],
                'perm_group' => $contentInfo['perms']['perm_group'],
                'perm_members' => $contentInfo['perms']['perm_members'],
                'perm_anon' => $contentInfo['perms']['perm_anon'],
            ),
        );

        \glFusion\Search::IndexDoc($props);

        if (function_exists('plugin_commentsupport_'.$type ) || $type == 'article' ) {
            if ( $type != 'article' ) {
                $func = 'plugin_commentsupport_'.$type;
                $rc = $func();
            } else {
                $rc = true;
            }
            if ( $rc == true || $type == 'article') {
                search_IndexAll_comments($type, $id, $props['perms']);
            }
        }

        $retval['errorCode'] = 0;
        $retval['statusMessage'] = 'Content Item Index Successful';
    } else {
        $retval['errorCode'] = -1;
        $retval['statusMessage'] = 'Error indexing content';
    }

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function SEARCH_completeContentAjax()
{
    global $_PLUGINS;

    if ( !COM_isAjax()) die();

    // $_POST['type'] will hold the content type that was just completed.
    $contentType = isset($_POST['type']) ? COM_applyFilter($_POST['type']) : 'unknown';

    $retval['errorCode'] = 0;
    $retval['statusMessage'] = 'Reindexing ' . $contentType . 'Successful';
    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function SEARCH_completeAjax()
{
    global $_PLUGINS;

    if ( !COM_isAjax()) die();

    $retval['errorCode'] = 0;
    $retval['statusMessage'] = 'Reindexing Successful';
    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}
?>
