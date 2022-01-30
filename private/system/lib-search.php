<?php
/**
* glFusion CMS
*
* glFusion Search Engine
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


if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;
use \glFusion\Cache\Cache;

/**
*   Index an item when it is saved.
*   First deletes any existing index records, then creates newones.
*
*   @param  string  $id     Item ID
*   @param  string  $type   Item Type
*/
function plugin_itemsaved_search($id, $type, $old_id = '')
{
    global $_CONF, $_TABLES;

    if ($type == 'search') return;

    if (!function_exists('plugin_getiteminfo_' . $type)) {
        return;
    }

    if ($_CONF['search_summarize_discussions'] == true) {
        $contentInfo = PLG_getItemInfo(
            $type, $id,
            'summary,id,date,parent_type,parent_id,title,searchidx,author,author_name,hits,perms,search_index,status'
        );
    } else {
        $contentInfo = PLG_getItemInfo(
            $type, $id,
            'id,date,parent_type,parent_id,title,searchidx,author,author_name,hits,perms,search_index,status'
        );
    }

    if ($contentInfo !== false && is_array($contentInfo) && count($contentInfo) > 0) {
        if (!isset($contentInfo['perms']) || empty($contentInfo['perms'])) {
            $contentInfo['perms'] = array(
                'owner_id' => 2,
                'group_id' => 1,
                'perm_owner' => 3,
                'perm_group' => 2,
                'perm_members' => 2,
                'perm_anon' => 2,
            );
        }
        // If an "enabled" status isn't returned by the plugin, assume enabled
        if (!isset($contentInfo['status']) || is_null($contentInfo['status'])) {
            $contentInfo['status'] = 1;
        }

        $props = array(
            'item_id' => $contentInfo['id'],
            'type'  => $type,
            'author' => $contentInfo['author'],
            'author_name' => $contentInfo['author_name'],
            // Hack to avoid indexing comment titles which don't show anyway
            'title' => $type == 'comment' ? NULL : $contentInfo['title'],
            'content' => $contentInfo['searchidx'],
            'date' => $contentInfo['date'],
            'perms' => $contentInfo['perms'],
            'parent_id' => $contentInfo['parent_id'],
            'parent_type' => $contentInfo['parent_type'],
        );
        if ( $old_id != '' && $id != $old_id ) {
            \glFusion\Search::RemoveDoc($type, $old_id);
        }
        if ($contentInfo['status']) {
            // Index only if status is nonzero (i.e. not draft or disabled)
            \glFusion\Search::IndexDoc($props);
        }
    } else {
        // if we didn't find anything for this item - assume it is gone
        \glFusion\Search::RemoveDoc($type, $id);
        Log::write('system',Log::WARNING,"Search - Unable to retrieve content info (could be deleted or marked as non-searchable) - Type = $type, ID = $id - removing from index.");
    }

    $c = Cache::getInstance()->deleteItemsByTag('searchcache');
}


/**
*   Delete index records for a deleted item.
*
*   @param  string  $id     Item ID
*   @param  string  $type   Item Type, e.g. plugin name
*   @param  string  $children   Optional comma-separated values to delete
*/
function plugin_itemdeleted_search($id, $type, $children='')
{
    if ( $type == 'search' ) return;
    if (!empty($children)) {
        $id = explode(',', $children);
    }
    \glFusion\Search::RemoveDoc($type, $id);
    $c = \glFusion\Cache::getInstance()->deleteItemsByTag('searchcache');
}


/**
*   PLG function to index a single document
*
*   @param  array   $args       Args, including type, item_id, title, etc.
*   @return boolean     True on success, False on failure
*/
function plugin_indexDoc_search($args)
{
    // Check that the minimum required fields are set
    if (!isset($args['item_id']) || !isset($args['type']) ||
        (!isset($args['content']) && !isset($args['title']) && !isset($args['author']))
    ) {
        return false;
    }
    \glFusion\Search::RemoveDoc($args['type'], $args['item_id']);
    return \glFusion\Search::IndexDoc($args);
}


/**
*   PLG function to remove an item from the index.
*   Makes sure that a valid type and item_id are set to remove one item.
*
*   @param  array   $args       Array of item info.
*   @param  mixed   $output     Output to set (not used)
*   @param  mixed   $svc_msg    Service message (not used)
*   @return integer     PLG_RET_OK on success, PLG_RET_ERROR on error
*/
function plugin_RemoveDoc_search($type, $item_id)
{
    if (!\glFusion\Search::RemoveDoc($type, $item_id)) return false;
    \glFusion\Search::RemoveComments($type, $item_id);
    return true;
}


/**
*   PLG function to remove all items for a plugin from the index.
*   May be called during plugin removal.
*   Makes sure that a valid type is set to remove all items for a single type.
*
*   @param  array   $args       Array of item info.
*   @param  mixed   $output     Output to set (not used)
*   @param  mixed   $svc_msg    Service message (not used)
*   @return integer     PLG_RET_OK on success, PLG_RET_ERROR on error
*/
function plugin_removeAll_search($type)
{
    // Check that the minimum required fields are set. Don't allwow plugins
    // to accidentally delete all.
    if (empty($type) || $type == 'all') {
        return false;
    }
    if (!\glFusion\Search::RemoveAll($type)) return false;
    \glFusion\Search::RemoveComments($type);
    return true;
}


/**
*   Selection dropdown to pick the stemmer in the configuration manager.
*
*   @return Array Associative array of section_name=>section_id
*/
function plugin_configmanager_select_stemmer_search()
{
    global $LANG_SEARCH;

    $A = array($LANG_SEARCH['none'] => '');
    // Collect the available stemmers
    $results = @glob(__DIR__ . '/classes/stemmer/*.class.php');
    $installable = '';
    if (is_array($results)) {
        foreach ($results as $fullpath) {
            $parts = explode('/', $fullpath);
            list($class,$x1,$x2) = explode('.', $parts[count($parts)-1]);
            $A[$class] = $class;
        }
    }
    return $A;
}


/**
*   Reindex all comments for the given type
*
*   @param  string  $type   Content type, e.g. "article", "staticpages"
*   @param  mixed   $pid    Parent content item ID
*   @param  mixed   $perms  Permission array from content item, or NULL
*   @return integer     Count of articles indexed
*/

function search_IndexAll_comments($type, $pid, $perms=NULL)
{
    global $_TABLES;

    if ( ! \glFusion\Search::CommentsEnabled() ) {
        return 0;
    }
    $type = DB_escapeString($type);
    $pid = DB_escapeString($pid);
    $sql = "SELECT cid, sid, uid, title, comment, UNIX_TIMESTAMP(date) AS unixdate
        FROM {$_TABLES['comments']}
        WHERE type = '$type' AND sid = '$pid'";
    //echo $sql;die;
    $res = DB_query($sql);
    $count = 0;
    // Remove all existing index records for this content
    \glFusion\Search::RemoveComments($type, $pid);
    while ($A = DB_fetchArray($res, false)) {
        $count++;
        $props = array(
            'item_id' => $A['cid'],
            'parent_id' => $A['sid'],
            'parent_type' => $type,
            'type'  => 'comment',
            //'title' => $A['title'],
            'author' => $A['uid'],
            'content' => $A['comment'],
            'date' => $A['unixdate'],
        );
        if (is_array($perms)) {
            $props['perms'] = array(
                'owner_id' => $perms['owner_id'],
                'group_id' => $perms['group_id'],
                'perm_owner' => $perms['perm_owner'],
                'perm_group' => $perms['perm_group'],
                'perm_members' => $perms['perm_members'],
                'perm_anon' => $perms['perm_anon'],
            );
        }
        \glFusion\Search::IndexDoc($props);
    }
    return $count;
}

?>
