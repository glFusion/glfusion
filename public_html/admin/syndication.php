<?php
/**
* glFusion CMS
*
* glFusion content syndication administration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2003-2008 by the following authors:
*  Authors: Dirk Haun         - dirk AT haun-online DOT de
*           Michael Jervis    - mike AT fuckingbrit DOT com
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\FieldList;

$display = '';

// Make sure user has rights to access this page
if (!SEC_hasRights ('syndication.edit')) {
    Log::logAccessViolation('Syndication Administration');
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[34],$MESSAGE[30],true,'error')
        . COM_siteFooter ();
    echo $display;
    exit;
}

/**
* Toggle status of a feed from enabled to disabled and back
*
* @param    int     $fid    ID of the feed
* @return   void
*
*/
function FEED_toggleStatus($fid_arr, $feedarray)
{
    global $_TABLES;

    if (isset($feedarray) && is_array($feedarray) ) {
        foreach ($feedarray AS $feed => $junk ) {
            $feed = intval($feed);
            if ( isset($fid_arr[$feed]) ) {
                DB_query ("UPDATE {$_TABLES['syndication']} SET is_enabled = '1' WHERE fid = ".(int) $feed);
            } else {
                DB_query ("UPDATE {$_TABLES['syndication']} SET is_enabled = '0' WHERE fid = ".(int) $feed);
            }
        }
        $c = Cache::getInstance()->deleteItemsByTag('story');
    }
    return;
}

/**
* Get a list of feed formats from the feed parser factory.
*
* @return   array   array of names of feed formats (and versions)
*
*/
function FEED_findFormats()
{
    global $_CONF;

    $formats = array();
/*
    $formats[] = array('name'=>'RSS','version'=>'0.9x');
    $formats[] = array('name'=>'RSS','version'=>'2.0');
    $formats[] = array('name'=>'RDF','version'=>'1.0');
    $formats[] = array('name'=>'Atom','version'=>'0.3');
    $formats[] = array('name'=>'Atom','version'=>'1.0');
    $formats[] = array('name'=>'ICS','version'=>'1.0');
*/
    $formats[] = array('name'=>'RSS','version'=>'0.91');
    $formats[] = array('name'=>'RSS','version'=>'1.0');
    $formats[] = array('name'=>'RSS','version'=>'2.0');
    $formats[] = array('name'=>'ATOM','version'=>'0.3');
    $formats[] = array('name'=>'ATOM','version'=>'1.0');
    $formats[] = array('name'=>'ICS','version'=>'1.0');

    sort ($formats);

    return $formats;
}

/**
* Create a list of feed types that glFusion offers.
*
* @return   string   an array with id/name pairs for every feed
*
*/
function FEED_getArticleFeeds()
{
    global $_CONF, $_TABLES, $LANG33;

    $options = array ();

    $sql = "SELECT tid,topic FROM {$_TABLES['topics']} WHERE perm_anon >= 2 ORDER BY ";
    if ($_CONF['sortmethod'] == 'alpha') {
        $sql .= 'topic ASC';
    } else {
        $sql .= 'sortnum';
    }
    $result = DB_query ($sql);
    $num = DB_numRows ($result);

    if ($num > 0) {
        $options[] = array ('id' => '::all',       'name' => $LANG33[23]);
        $options[] = array ('id' => '::frontpage', 'name' => $LANG33[53]);
    }

    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $options[] = array ('id' => $A['tid'], 'name' => '-- ' . $A['topic']);
    }

    return $options;
}

/**
 * get field data for the feed list administration
 *
 */
function FEED_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG33, $_IMAGE_TYPE;

    $retval = '';
    $enabled = ($A['is_enabled'] == 1) ? true : false;

    $dt = new Date('now',$_USER['tzid']);

    switch($fieldname) {

        case 'edit':
            $retval = FieldList::edit(
                array(
                    'url' => $_CONF['site_admin_url'] . '/syndication.php?edit=x&amp;fid=' . $A['fid'],
                    'attr' => array(
                        'title' => $LANG_ADMIN['edit']
                    )
                )
            );
            break;
        case 'delete':
            $retval = FieldList::delete(
                array(
                    'delete_url' => $_CONF['site_admin_url'] . '/syndication.php?delete=x&amp;fid=' . $A['fid']. '&amp;' . CSRF_TOKEN . '=' . $token,
                    'attr' => array(
                        'title'   => $LANG_ADMIN['delete'],
                        'onclick' => 'return confirm(\'' . $LANG33[56] . '\');"'
                    ),
                )
            );
            break;

        case 'type':
            if ($A['type'] == 'article') {
                $type = $LANG33[55];
            } else {
                $type = ucwords($A['type']);
            }
            $retval = ($enabled) ? $type : '<span class="disabledfield">' . $type . '</span>';
            break;

        case 'format':
            $format = str_replace ('-' , ' ', ucwords ($A['format']));
            $retval = ($enabled) ? $format : '<span class="disabledfield">' . $format . '</span>';
            break;

        case 'updated':
            $dt->setTimeStamp($A['date']);
            $datetime = $dt->format($_CONF['daytime'],true);
            $retval = ($enabled) ? $datetime : '<span class="disabledfield">' . $datetime . '</span>';
            break;

        case 'is_enabled':
            if ($enabled) {
                $switch = ' checked="checked"';
                $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
            } else {
                $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                $switch = '';
            }
            $switch = ($enabled) ? ' checked="checked"' : '';
            $retval = '<input type="checkbox" name="enabledfeeds[' . $A['fid'] . ']" ' . $title
                . 'onclick="submit()" value="' . $A['fid'] . '"' . $switch . '/>';
            $retval .= '<input type="hidden" name="feedarray[' . $A['fid'] . ']" value="1" ' . '/>';
            break;

        case 'header_tid':
            if ($A['header_tid'] == 'all') {
                $tid = $LANG33[43];
            } elseif ($A['header_tid'] == 'none') {
                $tid = $LANG33[44];
            } else {
                $tid = DB_getItem ($_TABLES['topics'], 'topic',
                                      "tid = '".DB_escapeString($A['header_tid'])."'");
            }
            $retval = ($enabled) ? $tid : '<span class="disabledfield">' . $tid . '</span>';
            break;

        case 'filename':
            if ($enabled) {
                $url = SYND_getFeedUrl ();
                $attr['title'] = $A['description'];
                $retval = COM_createLink($A['filename'], $url . $A['filename'], $attr);
            } else {
                $retval = '<span class="disabledfield">' . $A['filename'] . '</span>';
            }
            break;

        default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }
    return $retval;
}


function FEED_list()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG33, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';

    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center'),
                    array('text' => $LANG_ADMIN['title'], 'field' => 'title', 'sort' => true),
                    array('text' => $LANG_ADMIN['type'], 'field' => 'type', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG33[17], 'field' => 'format', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG33[16], 'field' => 'filename', 'sort' => true),
                    array('text' => $LANG_ADMIN['topic'], 'field' => 'header_tid', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG33[18], 'field' => 'updated', 'sort' => true, 'align' => 'center'),
                    array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center'),
                    array('text' => $LANG_ADMIN['enabled'], 'field' => 'is_enabled', 'sort' => true, 'align' => 'center')
    );

    $defsort_arr = array('field' => 'title', 'direction' => 'asc');

    $menu_arr = array (
                    array('url' => $_CONF['site_admin_url'].'/syndication.php',
                          'text' => $LANG33[57],'active' =>true),
                    array('url' => $_CONF['site_admin_url'] . '/syndication.php?edit=x',
                          'text' => $LANG_ADMIN['create_new']),
                    array('url' => $_CONF['site_admin_url'].'/index.php',
                          'text' => $LANG_ADMIN['admin_home'])
    );
    $retval .= COM_startBlock($LANG33[10], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG33[13],
        $_CONF['layout_url'] . '/images/icons/syndication.' . $_IMAGE_TYPE
    );

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/syndication.php'
    );

    $query_arr = array('table' => 'syndication',
                       'sql' => "SELECT *,UNIX_TIMESTAMP(updated) AS date FROM {$_TABLES['syndication']} WHERE 1=1",
                       'query_fields' => array('title', 'filename'),
                       'default_filter' => '');

    // embed a CSRF token as a hidden var at the top of each of the lists
    // this is used to validate block enable/disable

    $token = SEC_createToken();

    // feedenabler is a hidden field which if set, indicates that one of the
    // feeds has been enabled or disabled

    $form_arr = array(
        'top'    => '<input type="hidden" name="'.CSRF_TOKEN.'" value="'.$token.'"/>',
        'bottom' => '<input type="hidden" name="feedenabler" value="true"/>'
    );

    $retval .= ADMIN_list('syndication', 'FEED_getListField',
                          $header_arr, $text_arr, $query_arr, $defsort_arr,
                          '', $token, '', $form_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}

/**
* Display the feed editor.
*
* @param    int      $fid    feed id (0 for new feeds)
* @param    string   $type   type of feed, e.g. 'article'
* @param    array    $A      Array of preset values, e.g. from $_POST
* @return   string           HTML for the feed editor
*
*/
function FEED_edit($fid = 0, $type = '', $A = array())
{
    global $_CONF, $_TABLES, $LANG33, $LANG_ADMIN, $MESSAGE,$_IMAGE_TYPE;

    USES_lib_admin();

    $editMode = false;

    if ($fid > 0) {
        $result = DB_query ("SELECT *,UNIX_TIMESTAMP(updated) AS date FROM {$_TABLES['syndication']} WHERE fid = '$fid'");
        $A = DB_fetchArray ($result);
        $fid = $A['fid'];
        $editMode = true;
    }
    if ($fid == 0 && empty($A)) {
        if (!empty ($type)) { // set defaults
            $A['fid'] = $fid;
            $A['type'] = $type;
            $A['topic'] = '::all';
            $A['header_tid'] = 'none';
            $A['format'] = 'RSS-2.0';
            $A['limits'] = $_CONF['rdf_limit'];
            $A['content_length'] = $_CONF['rdf_storytext'];
            $A['title'] = $_CONF['site_name'];
            $A['description'] = $_CONF['site_slogan'];
            $A['feedlogo'] = '';
            $A['filename'] = '';
            $A['charset'] = $_CONF['default_charset'];
            $A['language'] = $_CONF['rdf_language'];
            $A['is_enabled'] = 1;
            $A['updated'] = '';
            $A['update_info'] = '';
            $A['date'] = time ();
        } else {
            return COM_refresh ($_CONF['site_admin_url'] . '/syndication.php');
        }
    } else {
        // Fields coming from $_POST after an error. Some need massaging.
        $A['limits'] = (int)$A['limits'];   // sanitize entry or hour number
        if ($A['limits_in'] == 1) {
            $A['limits'] .= 'h';
        }
        $A['date'] = time();
    }

    $retval = '';

    if ( $editMode ) {
        $lang_create_edit = $LANG33[24];
    } else {
        $lang_create_edit = $LANG_ADMIN['create_new'];
    }

    $menu_arr = array (
                    array('url' => $_CONF['site_admin_url'].'/syndication.php',
                          'text' => $LANG33[57]),
                    array('url' => $_CONF['site_admin_url'] . '/syndication.php?edit=x',
                          'text' => $lang_create_edit,'active'=>true),
                    array('url' => $_CONF['site_admin_url'].'/index.php',
                          'text' => $LANG_ADMIN['admin_home'])
    );

    $feed_template = new Template ($_CONF['path_layout'] . 'admin/syndication');
    $feed_template->set_file ('editor', 'feededitor.thtml');

    $feed_template->set_var ('start_feed_editor', COM_startBlock ($LANG33[24],
            '', COM_getBlockTemplate ('_admin_block', 'header')));
    $feed_template->set_var ('end_block',
            COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer')));

    $feed_template->set_var ('lang_feedtitle', $LANG33[25]);
    $feed_template->set_var ('lang_enabled', $LANG33[19]);
    $feed_template->set_var ('lang_format', $LANG33[17]);
    $feed_template->set_var ('lang_limits', $LANG33[26]);
    $feed_template->set_var ('lang_content_length', $LANG33[27]);
    $feed_template->set_var ('lang_clen_explain', $LANG33[28]);
    $feed_template->set_var ('lang_description', $LANG33[29]);
    $feed_template->set_var ('lang_feedlogo', $LANG33[49]);
    $feed_template->set_var ('lang_feedlogo_explain', sprintf($LANG33[50],$_CONF['site_url']));
    $feed_template->set_var ('lang_filename', $LANG33[16]);
    $feed_template->set_var ('lang_updated', $LANG33[30]);
    $feed_template->set_var ('lang_type', $LANG33[15]);
    $feed_template->set_var ('lang_charset', $LANG33[31]);
    $feed_template->set_var ('lang_language', $LANG33[32]);
    $feed_template->set_var ('lang_topic', $LANG33[33]);
    $feed_template->set_var ('admin_menu', ADMIN_createMenu($menu_arr,$LANG33[58],$_CONF['layout_url'] . '/images/icons/syndication.' . $_IMAGE_TYPE));

    if ($A['header_tid'] == 'all') {
        $feed_template->set_var('all_selected', 'selected="selected"');
    } elseif ($A['header_tid'] == 'none') {
        $feed_template->set_var('none_selected', 'selected="selected"');
    }

    $feed_template->set_var('lang_header_all', $LANG33[43]);
    $feed_template->set_var('lang_header_none', $LANG33[44]);
    $feed_template->set_var('lang_header_topic', $LANG33[45]);
    $feed_template->set_var('header_topic_options',
                        COM_topicList('tid,topic,sortnum', $A['header_tid'], 2, true));
    $feed_template->set_var('lang_save', $LANG_ADMIN['save']);
    $feed_template->set_var('lang_cancel', $LANG_ADMIN['cancel']);
    if ($A['fid'] > 0) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="mode"%s' . '/>';
        $jsconfirm = ' onclick="return confirm(\'' . $LANG33[56] . '\');"';
        $feed_template->set_var ('delete_option',
                                 sprintf ($delbutton, $jsconfirm));
        $feed_template->set_var ('delete_option_no_confirmation',
                                 sprintf ($delbutton, ''));
        $feed_template->set_var('delete_button',true);
        $feed_template->set_var('lang_delete_confirm', $LANG33[56]);
        $feed_template->set_var('lang_delete',$LANG_ADMIN['delete']);

    }
    $feed_template->set_var ('feed_id', $A['fid']);
    $feed_template->set_var ('feed_title', $A['title']);
    $feed_template->set_var ('feed_description', $A['description']);
    $feed_template->set_var ('feed_logo', $A['feedlogo']);
    $feed_template->set_var ('feed_content_length', $A['content_length']);
    $feed_template->set_var ('feed_filename', $A['filename']);
    $feed_template->set_var ('feed_type', $A['type']);
    if ($A['type'] == 'article') {
        $feed_template->set_var('feed_type_display', $LANG33[55]);
    } else {
        $feed_template->set_var('feed_type_display', ucwords($A['type']));
    }
    $feed_template->set_var ('feed_charset', $A['charset']);
    $feed_template->set_var ('feed_language', $A['language']);

    $nicedate = COM_getUserDateTimeFormat ($A['date']);
    $feed_template->set_var ('feed_updated', $nicedate[0]);

    $formats = FEED_findFormats();
    //$selection = '<select name="format">' . LB;
    $selection = '';
    foreach ($formats as $f) {
        // if one changes this format below ('name-version'), also change parsing
        // in COM_siteHeader. It uses explode( "-" , $string )
        $selection .= '<option value="' . $f['name'] . '-' . $f['version']
                   . '"';
        if ($A['format'] == $f['name'] . '-' . $f['version']) {
            $selection .= ' selected="selected"';
        }
        $selection .= '>' . ucwords ($f['name'] . ' ' . $f['version'])
                   . '</option>' . LB;
    }
    //$selection .= '</select>' . LB;
    $feed_template->set_var ('feed_format', $selection);

    $limits = $A['limits'];
    $hours = false;
    if (substr ($A['limits'], -1) == 'h') {
        $limits = substr ($A['limits'], 0, -1);
        $hours = true;
    }
    //$selection = '<select name="limits_in">' . LB;
    $selection = '';
    $selection .= '<option value="0"';
    if (!$hours) {
        $selection .= ' selected="selected"';
    }
    $selection .= '>' . $LANG33[34] . '</option>' . LB;
    $selection .= '<option value="1"';
    if ($hours) {
        $selection .= ' selected="selected"';
    }
    $selection .= '>' . $LANG33[35] . '</option>' . LB;
    //$selection .= '</select>' . LB;
    $feed_template->set_var ('feed_limits', $limits);
    $feed_template->set_var ('feed_limits_what', $selection);

    if ($A['type'] == 'article') {
        $options = FEED_getArticleFeeds();
    } elseif ( $A['type'] == 'comment' ) {
        $options = PLG_getFeedNames('comment');
    } else {
        $result = DB_query("SELECT pi_enabled FROM {$_TABLES['plugins']} WHERE pi_name='{$A['type']}'");
        if ($result) {
            $P = DB_fetchArray($result);
            if($P['pi_enabled'] == 0) {
                COM_setMessage(80);
                echo COM_refresh($_CONF['site_admin_url'].'/syndication.php');
                exit;
            }
        }
        $options = PLG_getFeedNames($A['type']);
    }
    //$selection = '<select name="topic">' . LB;
    $selection = '';
    foreach ($options as $o) {
        $selection .= '<option value="' . $o['id'] . '"';
        if ($A['topic'] == $o['id']) {
            $selection .= ' selected="selected"';
        }
        $selection .= '>' . $o['name'] . '</option>' . LB;
    }
    //$selection .= '</select>' . LB;
    $feed_template->set_var ('feed_topic', $selection);

    if ($A['is_enabled'] == 1) {
        $feed_template->set_var ('is_enabled', 'checked="checked"');
    } else {
        $feed_template->set_var ('is_enabled', '');
    }
    $security_token = SEC_createToken();
    $feed_template->set_var('sec_token_name', CSRF_TOKEN);
    $feed_template->set_var('sec_token', $security_token);
//depreciated
    $feed_template->set_var('gltoken_name', CSRF_TOKEN);
    $feed_template->set_var('gltoken', $security_token);
//end of depreciated
    $retval .= $feed_template->finish ($feed_template->parse ('output','editor'));
    return $retval;
}

/**
* Create a new feed. This is an extra step to take once you have a plugin
* installed that supports the new Feed functions in the Plugin API. This
* will let you select for which plugin (or glFusion) you're creating the feed.
*
* @return   string   HTML for the complete page (selection or feed editor)
*
*/
function FEED_newFeed()
{
    global $_CONF, $LANG33, $LANG_ADMIN,$_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';

    $availableFeeds = array();

    $availableFeeds[] = 'article';
    $plugins = PLG_supportingFeeds();
    $availableFeeds = array_merge($availableFeeds, $plugins);
    asort($availableFeeds);

    if (sizeof ($plugins) == 0) {
        // none of the installed plugins are supporting feeds
        // - go directly to the feed editor
        $retval = COM_siteHeader ('menu', $LANG33[11])
                . FEED_edit(0, 'article')
                . COM_siteFooter ();
    } else {
        //$selection = '<select name="type">' . LB;
        $selection = '';
        foreach ($availableFeeds as $p) {
            $selection .= '<option value="' . $p . '">' . ucwords ($p)
                       . '</option>' . LB;
        }
        //$selection .= '</select>' . LB;

        $menu_arr = array (
                        array('url' => $_CONF['site_admin_url'].'/syndication.php',
                              'text' => $LANG33[57]),
                    array('url' => $_CONF['site_admin_url'] . '/syndication.php?edit=x',
                          'text' => $LANG_ADMIN['create_new'],'active' => true),
                        array('url' => $_CONF['site_admin_url'].'/index.php',
                              'text' => $LANG_ADMIN['admin_home'])
        );

        $feed_template = new Template ($_CONF['path_layout'].'admin/syndication');
        $feed_template->set_file ('type', 'selecttype.thtml');
        $feed_template->set_var ('type_selection', $selection);
        $feed_template->set_var ('lang_explain', $LANG33[54]);
        $feed_template->set_var ('lang_go', $LANG33[1]);
        $retval .= COM_siteHeader ('menu', $LANG33[11]);
        $retval .= COM_startBlock ($LANG33[36], '',
                COM_getBlockTemplate ('_admin_block', 'header'));
        $retval .= ADMIN_createMenu($menu_arr,$LANG33[54],$_CONF['layout_url'] . '/images/icons/syndication.' . $_IMAGE_TYPE);

        $retval .= $feed_template->finish ($feed_template->parse ('output','type'));
        $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
        $retval .= COM_siteFooter ();
    }

    return $retval;
}

/**
* Save feed.
*
* @param    array    $A
* @return   string   HTML redirect on success or feed editor + error message
*
*/
function FEED_save($A)
{
    global $_CONF, $_TABLES, $LANG33;

    foreach ($A as $name => $value) {
        $A[$name] = $value;
    }

    if (isset($A['is_enabled']) && $A['is_enabled'] == 'on') {
        $A['is_enabled'] = 1;
    } else {
        $A['is_enabled'] = 0;
    }
    if (
        empty ($A['title']) ||
        empty ($A['description']) ||
        empty ($A['filename'])
    ) {
        $retval = COM_siteHeader ('menu', $LANG33[38])
                . COM_showMessageText($LANG33[39],$LANG33[38],true,'error')
                . FEED_edit($A['fid'], $A['type'], $A)
                . COM_siteFooter ();
        return $retval;
    }

    $result = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['syndication']} WHERE filename = '{$A['filename']}' AND (fid <> '{$A['fid']}')");
    $C = DB_fetchArray($result);
    if ($C['count'] > 0) {
        $retval = COM_siteHeader ('menu', $LANG33[52])
                . COM_showMessageText($LANG33[51],$LANG33[52],true,'error')
                . FEED_edit($A['fid'], $A['type'])
                . COM_siteFooter ();
        return $retval;
    }

    if ($A['limits'] <= 0) {
        $retval = COM_siteHeader ('menu', $LANG33[38])
                . COM_showMessageText($LANG33[40],$LANG33[38],true,'error')
                . FEED_edit($A['fid'], $A['type'])
                . COM_siteFooter ();
        return $retval;
    }
    if ($A['limits_in'] == 1) {
        $A['limits'] .= 'h';
    }

    // we can compensate if these are missing ...
    if (empty ($A['charset'])) {
        $A['charset'] = $_CONF['default_charset'];
        if (empty ($A['charset'])) {
            $A['charset'] = 'UTF-8';
        }
    }
    if (empty ($A['language'])) {
        $A['language'] = $_CONF['rdf_language'];
        if (empty ($A['language'])) {
            $A['language'] = $_CONF['locale'];
        }
    }
    if (empty ($A['content_length']) || ($A['content_length'] < 0)) {
        $A['content_length'] = 0;
    }

    foreach ($A as $name => $value) {
        $A[$name] = DB_escapeString ($value);
    }

    DB_save ($_TABLES['syndication'], 'fid,type,topic,header_tid,format,limits,content_length,title,description,feedlogo,filename,charset,language,is_enabled,updated,update_info',
        "{$A['fid']},'{$A['type']}','{$A['topic']}','{$A['header_tid']}','{$A['format']}','{$A['limits']}',{$A['content_length']},'{$A['title']}','{$A['description']}','{$A['feedlogo']}','{$A['filename']}','{$A['charset']}','{$A['language']}',{$A['is_enabled']},'1000-01-01 00:00:00',NULL");

    if ($A['fid'] == 0) {
        $A['fid'] = DB_insertId ();
    }
    SYND_updateFeed ($A['fid']);
    $c = Cache::getInstance()->deleteItemsByTag('story');
    COM_setMessage(58);
    return COM_refresh ($_CONF['site_admin_url'] . '/syndication.php');
}

/**
* Delete a feed.
*
* @param    int      $fid   feed id
* @return   string          HTML redirect
*
*/
function FEED_delete($fid)
{
    global $_CONF, $_TABLES;

    if ($fid > 0) {
        $feedfile = DB_getItem($_TABLES['syndication'], 'filename',
                               "fid = $fid");
        if (!empty($feedfile)) {
            @unlink(SYND_getFeedPath($feedfile));
        }
        DB_delete($_TABLES['syndication'], 'fid', $fid);
        $c = Cache::getInstance()->deleteItemsByTag('story');
        COM_setMessage(59);
        return COM_refresh ($_CONF['site_admin_url']
                            . '/syndication.php');
    }

    return COM_refresh ($_CONF['site_admin_url'] . '/syndication.php');
}


// MAIN ========================================================================

$action = '';
$expected = array('create', 'edit', 'save', 'delete', 'cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$fid = 0;
if (isset($_POST['fid'])) {
    $fid = COM_applyFilter($_POST['fid'], true);
} elseif (isset($_GET['fid'])) {
    $fid = COM_applyFilter($_GET['fid'], true);
}

if ($_CONF['backend'] && isset($_POST['feedenabler']) && SEC_checkToken()) {
    $enabledfeeds = array();
    if (isset($_POST['enabledfeeds'])) {
        $enabledfeeds = $_POST['enabledfeeds'];
    }
    $feedarray = array();
    if ( isset($_POST['feedarray']) ) {
        $feedarray = $_POST['feedarray'];
    }
    FEED_toggleStatus($enabledfeeds, $feedarray);
}

switch ($action) {

    case 'create':
        $display .= COM_siteHeader ('menu', $LANG33[24])
                 . FEED_edit(0, COM_applyFilter($_REQUEST['type']))
                 . COM_siteFooter ();
        break;

    case 'edit':
        if ($fid == 0) {
            $display .= FEED_newFeed();
        } else {
            $display .= COM_siteHeader ('menu', $LANG33[24])
                     . FEED_edit($fid)
                     . COM_siteFooter ();
        }
        break;

    case 'save':
        if (SEC_checkToken()) {
            $display .= FEED_save($_POST);
        } else {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to edit feed $fid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if (SEC_checkToken()) {
            $display .= FEED_delete($fid);
        } else {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to delete feed $fid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader ('menu', $LANG33[10]);
        $msg = COM_getMessage();
        $display .= ($msg > 0) ? COM_showMessage($msg) : '';
        $display .= FEED_list();
        $display .= COM_siteFooter ();
        break;
}

echo $display;

?>
