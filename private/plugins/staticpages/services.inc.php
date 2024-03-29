<?php
/**
* glFusion CMS - Static Pages Plugin
*
* Implements the 'services' provided by the Static Pages Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2013-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2010 by the following authors:
*  Tony Bibbs        tony AT tonybibbs DOT com
*  Tom Willett       twillett AT users DOT sourceforge DOT net
*  Blaine Lang       langmail AT sympatico DOT ca
*  Dirk Haun         dirk AT haun-online DOT de
*  Ramnath R. Iyer   rri AT silentyak DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;

// this must be kept in synch with the actual size of 'sp_id' in the db ...
define('STATICPAGE_MAX_ID_LENGTH', 128);

/**
 * Submit static page. The page is updated if it exists, or a new one is created
 *
 * @param   array   args     Contains all the data provided by the client
 * @param   string  &output  OUTPUT parameter containing the returned text
 * @param   string  &svc_msg OUTPUT parameter containing any service messages
 * @return  int		     Response code as defined in lib-plugins.php
 */
function service_submit_staticpages($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $LANG12, $LANG_STATIC,
           $LANG_LOGIN, $_GROUPS, $_SP_CONF;

    $output = '';
    $svc_msg = array();

    if (!SEC_hasRights('staticpages.edit')) {
        $output  = COM_siteHeader('menu', $LANG_STATIC['access_denied']);
        $output .= COM_showMessageText($LANG_STATIC['access_denied_msg'],$LANG_STATIC['access_denied'],true,'error');
        $output .= COM_siteFooter();

        return PLG_RET_AUTH_FAILED;
    }

    if ( defined('DEMO_MODE') ) {
        COM_setMsg( 'Saving Pages is Disabled in Demo Mode', 'error' );
        $url = COM_buildURL($_CONF['site_admin_url'] . '/plugins/staticpages/index.php');
        $output .= PLG_afterSaveSwitch($_SP_CONF['aftersave'], $url,'staticpages');
        return PLG_RET_OK;
    }

    $gl_edit = false;
    if (isset($args['gl_edit'])) {
        $gl_edit = $args['gl_edit'];
    }
    if ($gl_edit) {
        // This is EDIT mode, so there should be an sp_old_id
        if (empty($args['sp_old_id'])) {
            if (!empty($args['id'])) {
                $args['sp_old_id'] = $args['id'];
            } else {
                return PLG_RET_ERROR;
            }

            if (empty($args['sp_id'])) {
                $args['sp_id'] = $args['sp_old_id'];
            }
        }
    } else {
        if (empty($args['sp_id']) && !empty($args['id'])) {
            $args['sp_id'] = $args['id'];
        }
    }

    if ( empty($args['sp_uid']) ) {
        $args['sp_uid'] = $_USER['uid'];
    }

    if (empty($args['sp_title']) && !empty($args['title'])) {
        $args['sp_title'] = $args['title'];
    }

    if (empty($args['sp_content']) && !empty($args['content'])) {
        $args['sp_content'] = $args['content'];
    }

    if (isset($args['category']) && is_array($args['category']) &&
            !empty($args['category'][0])) {
        $args['sp_tid'] = $args['category'][0];
    }

    if (!isset($args['owner_id'])) {
        $args['owner_id'] = $_USER['uid'];
    }

    if (empty($args['group_id'])) {
        $args['group_id'] = SEC_getFeatureGroup('staticpages.edit', $_USER['uid']);
    }

    $args['sp_id'] = COM_sanitizeID($args['sp_id']);
    if (!$gl_edit) {
        if (strlen($args['sp_id']) > STATICPAGE_MAX_ID_LENGTH) {
            $args['sp_id'] = COM_makeSid();
        }
    }

    // Apply filters to the parameters passed by the webservice
    if ($args['gl_svc']) {
        $par_str = array('mode', 'sp_id', 'sp_old_id', 'sp_tid', 'sp_format',
                         'postmode');
        $par_num = array('sp_uid', 'sp_hits', 'owner_id', 'group_id',
                         'sp_where', 'sp_php', 'commentcode','sp_search', 'sp_status');

        foreach ($par_str as $str) {
            if (isset($args[$str])) {
                $args[$str] = COM_applyBasicFilter($args[$str]);
            } else {
                $args[$str] = '';
            }
        }

        foreach ($par_num as $num) {
            if (isset($args[$num])) {
                $args[$num] = COM_applyBasicFilter($args[$num], true);
            } else {
                $args[$num] = 0;
            }
        }
    }

    // START: Staticpages defaults

    if ( $args['sp_status'] != 1 ) {
        $args['sp_status'] = 0;
    }

    if(empty($args['sp_format'])) {
        $args['sp_format'] = 'allblocks';
    }

    if (empty($args['sp_tid'])) {
        $args['sp_tid'] = 'all';
    }

    if (($args['sp_where'] < 0) || ($args['sp_where'] > 4)) {
        $args['sp_where'] = 0;
    }

    if (($args['sp_php'] < 0) || ($args['sp_php'] > 2)) {
        $args['sp_php'] = 0;
    }

    if (($args['commentcode'] < -1) || ($args['commentcode'] > 1)) {
        $args['commentcode'] = $_CONF['comment_code'];
    }

    if ( $args['sp_search'] != 1 ) {
        $args['sp_search'] = 0;
    }

    if ( $args['sp_onmenu'] != 1 ) {
        $args['sp_onmenu'] = 0;
    }
    if ($args['gl_svc']) {
        // Permissions
        if (!isset($args['perm_owner'])) {
            $args['perm_owner'] = $_SP_CONF['default_permissions'][0];
        } else {
            $args['perm_owner'] = COM_applyBasicFilter($args['perm_owner'], true);
        }
        if (!isset($args['perm_group'])) {
            $args['perm_group'] = $_SP_CONF['default_permissions'][1];
        } else {
            $args['perm_group'] = COM_applyBasicFilter($args['perm_group'], true);
        }
        if (!isset($args['perm_members'])) {
            $args['perm_members'] = $_SP_CONF['default_permissions'][2];
        } else {
            $args['perm_members'] = COM_applyBasicFilter($args['perm_members'], true);
        }
        if (!isset($args['perm_anon'])) {
            $args['perm_anon'] = $_SP_CONF['default_permissions'][3];
        } else {
            $args['perm_anon'] = COM_applyBasicFilter($args['perm_anon'], true);
        }
        if (!isset($args['sp_onmenu'])) {
            $args['sp_onmenu'] = 0;
        } else if (($args['sp_onmenu'] == 1) && empty($args['sp_label'])) {
            $svc_msg['error_desc'] = 'Menu label missing';
            return PLG_RET_ERROR;
        }

        if (empty($args['sp_content'])) {
            $svc_msg['error_desc'] = 'No content';
            return PLG_RET_ERROR;
        }

        if (empty($args['sp_inblock']) && ($_SP_CONF['in_block'] == '1')) {
            $args['sp_inblock'] = 'on';
        }

        if (empty($args['sp_centerblock'])) {
            $args['sp_centerblock'] = '';
        }
    }

    // END: Staticpages defaults

    $sp_id = $args['sp_id'];
    $sp_status = $args['sp_status'];
    $sp_uid = $args['sp_uid'];
    $sp_title = $args['sp_title'];
    $sp_content = $args['sp_content'];
    $sp_hits = $args['sp_hits'];
    $sp_format = $args['sp_format'];
    $sp_onmenu = $args['sp_onmenu'];
    $sp_label = '';
    if (!empty($args['sp_label'])) {
        $sp_label = $args['sp_label'];
    }
    $commentcode = $args['commentcode'];
    $owner_id = $args['owner_id'];
    $group_id = $args['group_id'];
    $perm_owner = $args['perm_owner'];
    $perm_group = $args['perm_group'];
    $perm_members = $args['perm_members'];
    $perm_anon = $args['perm_anon'];
    $sp_php = $args['sp_php'];
    $sp_nf = '';
    if (!empty($args['sp_nf'])) {
        $sp_nf = $args['sp_nf'];
    }
    $sp_old_id = $args['sp_old_id'];
    $sp_centerblock = $args['sp_centerblock'];
    $sp_help = '';
    if (!empty($args['sp_help'])) {
        $sp_help = $args['sp_help'];
    }
    $sp_tid = $args['sp_tid'];
    $sp_where = $args['sp_where'];
    $sp_inblock = $args['sp_inblock'];
    $postmode = $args['postmode'];
    $sp_search = $args['sp_search'];

    if ($gl_edit && !empty($args['gl_etag'])) {
        // First load the original staticpage to check if it has been modified
        $o = array();
        $s = array();
        $r = service_get_staticpages(array('sp_id' => $sp_old_id, 'gl_svc' => true), $o, $s);

        if ($r == PLG_RET_OK) {
            if ($args['gl_etag'] != $o['updated']) {
                $svc_msg['error_desc'] = 'A more recent version of the staticpage is available';
                return PLG_RET_PRECONDITION_FAILED;
            }
        } else {
            $svc_msg['error_desc'] = 'The requested staticpage no longer exists';
            return PLG_RET_ERROR;
        }
    }

    // Check for unique page ID
    $duplicate_id = false;
    $delete_old_page = false;
    if (DB_count ($_TABLES['staticpage'], 'sp_id', $sp_id) > 0) {
        if ($sp_id != $sp_old_id) {
            $duplicate_id = true;
        }
    } elseif (!empty ($sp_old_id)) {
        if ($sp_id != $sp_old_id) {
            $delete_old_page = true;
        }
    }

    if ($duplicate_id) {
        $output .= COM_siteHeader ('menu', $LANG_STATIC['staticpageeditor']);
        $output .= $LANG_STATIC['duplicate_id'];
        Log::write('system',Log::ERROR,$LANG_STATIC['duplicate_id']);
        if (!$args['gl_svc']) {
            $output .= PAGE_edit($sp_id);
        }
        $output .= COM_siteFooter ();
        $svc_msg['error_desc'] = 'Duplicate ID';
        return PLG_RET_ERROR;
     } elseif (!empty ($sp_title) && !empty ($sp_content)) {
        if (empty ($sp_hits)) {
            $sp_hits = 0;
        }

        if ( $sp_onmenu == 1 && empty($sp_label) ) {
            $sp_label = $sp_title;
        }

        if ($sp_nf == 'on') {
            $sp_nf = 1;
        } else {
            $sp_nf = 0;
        }
        if ($sp_centerblock == 'on') {
            $sp_centerblock = 1;
        } else {
            $sp_centerblock = 0;
        }
        if ($sp_inblock == 'on') {
            $sp_inblock = 1;
        } else {
            $sp_inblock = 0;
        }

        // Clean up the text
        if ($_SP_CONF['censor'] == 1) {
            $sp_content = COM_checkWords ($sp_content);
            $sp_title = COM_checkWords ($sp_title);
        }
        if ($_SP_CONF['filter_html'] == 1) {
            $sp_content = COM_checkHTML ($sp_content,'staticpages.edit');
        }
        $sp_title = strip_tags ($sp_title);
        $sp_label = strip_tags ($sp_label,'<i>');

        $sp_content = DB_escapeString ($sp_content);
        $sp_title = DB_escapeString ($sp_title);
        $sp_label = DB_escapeString ($sp_label);

        // If user does not have php edit perms, then set php flag to 0.
        if (($_SP_CONF['allow_php'] != 1) || !SEC_hasRights ('staticpages.PHP')) {
            $sp_php = 0;
        }

        // make sure there's only one "entire page" static page per topic
        if (($sp_centerblock == 1) && ($sp_where == 0)) {
            $sql = "UPDATE {$_TABLES['staticpage']} SET sp_centerblock = 0 WHERE sp_centerblock = 1 AND sp_where = 0 AND sp_tid = '".DB_escapeString($sp_tid)."'";

            // multi-language configuration - allow one entire page
            // centerblock for all or none per language
            if ((!empty($_CONF['languages']) &&
                    !empty($_CONF['language_files'])) &&
                    (($sp_tid == 'all') || ($sp_tid == 'none'))) {
                $ids = explode('_', $sp_id);
                if (count($ids) > 1) {
                    $lang_id = array_pop($ids);
                    $sql .= " AND sp_id LIKE '%\\_".DB_escapeString($lang_id)."'";
                }
            }
            DB_query($sql);
        }

        $formats = array ('allblocks', 'blankpage', 'leftblocks', 'rightblocks', 'noblocks');
        if (!in_array ($sp_format, $formats)) {
            $sp_format = 'allblocks';
        }

        if (!$args['gl_svc']) {
            list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);
        }

        DB_save ($_TABLES['staticpage'], 'sp_id,sp_status,sp_uid,sp_title,sp_content,sp_date,sp_hits,sp_format,sp_onmenu,sp_label,commentcode,owner_id,group_id,'
                .'perm_owner,perm_group,perm_members,perm_anon,sp_php,sp_nf,sp_centerblock,sp_help,sp_tid,sp_where,sp_inblock,postmode,sp_search',
                "'$sp_id',$sp_status, $sp_uid,'$sp_title','$sp_content','".$_CONF['_now']->toMySQL(true)."',$sp_hits,'$sp_format',$sp_onmenu,'$sp_label','$commentcode',$owner_id,$group_id,"
                        ."$perm_owner,$perm_group,$perm_members,$perm_anon,'$sp_php','$sp_nf',$sp_centerblock,'$sp_help','$sp_tid',$sp_where,"
                        ."'$sp_inblock','$postmode',$sp_search");

        if ($delete_old_page && !empty ($sp_old_id)) {
            DB_delete ($_TABLES['staticpage'], 'sp_id', $sp_old_id);
            DB_change($_TABLES['comments'], 'sid', DB_escapeString($sp_id),
                      array('sid', 'type'),
                      array(DB_escapeString($sp_old_id), 'staticpages'));
            $c = Cache::getInstance()->deleteItemsByTags(array('sp_'.md5($sp_old_id),'staticpages'));
            PLG_itemDeleted($sp_old_id, 'staticpages');

        }
        $c = Cache::getInstance()->deleteItemsByTags(array('sp_'.md5($sp_id),'staticpages','menu'));
        PLG_itemSaved($sp_id,'staticpages');
        COM_setMsg( $LANG_STATIC['page_saved'], 'info' );
        $url = COM_buildURL($_CONF['site_url'] . '/page.php?page='
                            . $sp_id);
        $output .= PLG_afterSaveSwitch($_SP_CONF['aftersave'], $url,
                                       'staticpages');

        $svc_msg['id'] = $sp_id;
        return PLG_RET_OK;
    } else {
        $output .= COM_siteHeader ('menu', $LANG_STATIC['staticpageeditor']);
        $output .= $LANG_STATIC['no_title_or_content'];
        Log::write('system',Log::ERROR,$LANG_STATIC['no_title_or_content']);
        if (!$args['gl_svc']) {
            $output .= PAGE_edit($sp_id);
        }
        $output .= COM_siteFooter ();
        return PLG_RET_ERROR;
    }
}

/**
 * Delete an existing static page
 *
 * @param   array   args    Contains all the data provided by the client
 * @param   string  &output OUTPUT parameter containing the returned text
 * @param   string  &svc_msg OUTPUT parameter containing any service messages
 * @return  int		    Response code as defined in lib-plugins.php
 */
function service_delete_staticpages($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $LANG12, $LANG_STATIC, $LANG_LOGIN;

    if ( defined('DEMO_MODE') ) {
        COM_setMsg( 'Deleting Pages is Disabled in Demo Mode', 'error' );
        $output = COM_refresh($_CONF['site_admin_url'] . '/plugins/staticpages/index.php');
        return PLG_RET_OK;
    }

    if (empty($args['sp_id']) && !empty($args['id']))
        $args['sp_id'] = $args['id'];

    // Apply filters to the parameters passed by the webservice

    if ($args['gl_svc']) {
        $args['sp_id'] = COM_applyBasicFilter($args['sp_id']);
        $args['mode'] = COM_applyBasicFilter($args['mode']);
    }

    $sp_id = $args['sp_id'];

    if (!SEC_hasRights ('staticpages.delete')) {
        $output = COM_siteHeader ('menu', $LANG_STATIC['access_denied']);
        $output .= COM_showMessageText($LANG_STATIC['access_denied_msg'],$LANG_STATIC['access_denied'],true,'error');
        $output .= COM_siteFooter ();
        if (!COM_isAnonUser()) {
            return PLG_RET_PERMISSION_DENIED;
        } else {
            return PLG_RET_AUTH_FAILED;
        }
    }

    DB_delete ($_TABLES['staticpage'], 'sp_id', $sp_id);
    DB_delete($_TABLES['comments'], array('sid',  'type'),array($sp_id, 'staticpages'));
    PLG_itemDeleted($sp_id, 'staticpages');
    COM_setMsg( $LANG_STATIC['page_deleted'], 'error' );
    $output = COM_refresh($_CONF['site_admin_url'] . '/plugins/staticpages/index.php');

    return PLG_RET_OK;
}

/**
 * Get an existing static page
 *
 * @param   array   args    Contains all the data provided by the client
 * @param   string  &output OUTPUT parameter containing the returned text
 * @param   string  &svc_msg OUTPUT parameter containing any service messages
 * @return  int		    Response code as defined in lib-plugins.php
 */
function service_get_staticpages($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $LANG12, $LANG_STATIC, $LANG_LOGIN, $_SP_CONF;

    $output = '';
    $svc_msg = array();

    $svc_msg['output_fields'] = array(
                                    'sp_hits',
                                    'sp_format',
                                    'owner_id',
                                    'group_id',
                                    'perm_owner',
                                    'perm_group',
                                    'perm_members',
                                    'perm_anon',
                                    'sp_help',
                                    'sp_php',
                                    'sp_inblock',
                                    'commentcode'
                                     );

    if (empty($args['sp_id']) && !empty($args['id'])) {
        $args['sp_id'] = $args['id'];
    }

    if ($args['gl_svc']) {
        if (isset($args['sp_id'])) {
            $args['sp_id'] = COM_applyBasicFilter($args['sp_id']);
        }
        if (isset($args['mode'])) {
            $args['mode'] = COM_applyBasicFilter($args['mode']);
        }

        if (empty($args['sp_id'])) {
            $svc_msg['gl_feed'] = true;
        } else {
            $svc_msg['gl_feed'] = false;
        }
    } else {
        $svc_msg['gl_feed'] = false;
    }

    if (!$svc_msg['gl_feed']) {
        $page = '';
        if (isset($args['sp_id'])) {
            $page = $args['sp_id'];
        }
        $mode = '';
        if (isset($args['mode'])) {
            $mode = $args['mode'];
        }

        $error = 0;

        if ($page == '') {
            $error = 1;
        }
        $perms = SP_getPerms ();

        $c = Cache::getInstance();
        $key = 'sp__' . md5($args['sp_id']) . '_'.md5($perms) . '_' . $c->securityHash();
        $cacheCheck = $c->get($key);
        if ( $cacheCheck !== null ) {
            $output = unserialize($cacheCheck);
        } else {
            if (!empty ($perms)) {
                $perms = ' AND ' . $perms;
            }
            $sql          = "SELECT sp_title,sp_content,sp_hits,sp_date,sp_format,"
                          . "commentcode,sp_uid,owner_id,group_id,perm_owner,perm_group,"
                          . "perm_members,perm_anon,sp_tid,sp_help,sp_php,"
                          . "sp_inblock FROM {$_TABLES['staticpage']} "
                          . "WHERE (sp_id = '".DB_escapeString($page)."') AND (sp_status = 1)" . $perms;

            $result = DB_query ($sql);
            $count = DB_numRows ($result);

            if ($count == 0 || $count > 1) {
                $error = 1;
            } else {
                $output = DB_fetchArray($result,false);
                $c->set($key,serialize($output),array('staticpage','sp_'.md5($args['sp_id'])));
            }
        }

        if (!($error)) {
            // output now filled above...
//           $output = DB_fetchArray ($result, false);

            if ( $mode !== 'autotag' ) $_CONF['pagetitle'] = $output['sp_title'];

        } else { // an error occured (page not found, access denied, ...)
            if (empty ($page)) {
                $failflg = 0;
            } else {
                $failflg = DB_getItem ($_TABLES['staticpage'], 'sp_nf', "sp_id='$page'");
            }
            if ($failflg) {
                if ($mode !== 'autotag' && $mode !== 'comment') {
                    $output = COM_siteHeader ('menu');
                }
                $output .= SEC_loginRequiredForm();

                if ($mode !== 'autotag' && $mode !== 'comment') {
                    $output .= COM_siteFooter ();
                }
            } else {
                if ($mode !== 'autotag') {
                    COM_404();
                }
            }
            return PLG_RET_ERROR;
        }

        if ($args['gl_svc']) {
            // This date format is PHP 5 only,
            // but only the web-service uses the value
            $output['published']    = date('c', strtotime($output['sp_date']));
            $output['updated']      = date('c', strtotime($output['sp_date']));
            $output['id']           = $page;
            $output['title']        = $output['sp_title'];
            $output['category']     = array($output['sp_tid']);
            $output['content']      = $output['sp_content'];
            $output['content_type'] = 'html';

            $output['author_name']  = DB_getItem($_TABLES['users'],'username','uid='.(int)$output['owner_id']);

            $output['link_edit'] = $page;
        }
    } else {
        $output = array();

        $mode = '';
        if (isset($args['mode'])) {
            $mode = $args['mode'];
        }

        $perms = SP_getPerms();
        if (!empty ($perms)) {
            $perms = ' AND ' . $perms;
        }

        $offset = 0;
        if (isset($args['offset'])) {
            $offset = COM_applyBasicFilter($args['offset'], true);
        }
        $max_items = 10 + 1;

        $limit = " LIMIT $offset, $max_items";
        $order = " ORDER BY sp_date DESC";

        $sql   = "SELECT sp_id,sp_title,sp_content,sp_hits,sp_date,sp_format,owner_id,"
                ."group_id,perm_owner,perm_group,perm_members,perm_anon,sp_tid,sp_help,sp_php,"
                ."sp_inblock FROM {$_TABLES['staticpage']} WHERE (sp_status = 1)" . $perms . $order . $limit;
        $result = DB_query ($sql);

        $count = 0;
        while (($output_item = DB_fetchArray ($result, false)) !== false) {
            // WE ASSUME $output doesn't have any confidential fields

            $count += 1;
            if ($count == $max_items) {
                $svc_msg['offset'] = $offset + 10;
                break;
            }

            if($args['gl_svc']) {
                // This date format is PHP 5 only, but only the web-service uses the value
                $output_item['published']    = date('c', strtotime($output_item['sp_date']));
                $output_item['updated']      = date('c', strtotime($output_item['sp_date']));
                $output_item['id']           = $output_item['sp_id'];
                $output_item['title']        = $output_item['sp_title'];
                $output_item['category']     = array($output_item['sp_tid']);
                $output_item['content']      = $output_item['sp_content'];
                $output_item['content_type'] = 'html';
                $output_item['author_name']  = DB_getItem($_TABLES['users'],'username','uid='.(int)$output['owner_id']);
            }
            $output[] = $output_item;
        }
    }

    return PLG_RET_OK;
}

/**
 * Get all the topics available
 *
 * @param   array   args    Contains all the data provided by the client
 * @param   string  &output OUTPUT parameter containing the returned text
 * @return  int         Response code as defined in lib-plugins.php
 */
function service_getTopicList_staticpages($args, &$output, &$svc_msg)
{
    //$output = COM_topicArray('tid');
    $output[] = 'all';
    $output[] = 'none';

    return PLG_RET_OK;
}

?>