<?php
/**
* glFusion CMS
*
* Admin functions handle Trackback, Pingback, and Ping
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2005-2008 by the following authors:
*  Authors: Dirk Haun         - dirk AT haun-online DOT de
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\FieldList;

if (!$_CONF['trackback_enabled'] && !$_CONF['pingback_enabled'] &&
        !$_CONF['ping_enabled']) {
    echo COM_refresh ($_CONF['site_admin_url'] . '/index.php');
    exit;
}

$display = '';
$page    = '';

if (!SEC_hasRights ('story.ping')) {
    Log::logAccessViolation('Trackback Admin');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[34],$MESSAGE[30],true);
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

require_once $_CONF['path_system'] . 'lib-trackback.php';
require_once $_CONF['path_system'] . 'lib-pingback.php';

/**
* Display trackback comment submission form.
*
* @param    string  $target     URL to send the trackback comment to
* @param    string  $url        URL of our entry
* @param    string  $title      title of our entry
* @param    string  $excerpt    excerpt of our entry
* @param    string  $blog       name of our site
* @return   string              HTML for the trackback comment editor
*
*/
function TRACKBACK_edit($target = '', $url = '', $title = '', $excerpt = '', $blog = '')
{
    global $_CONF, $LANG_TRB, $LANG_ADMIN, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';
    $preview_text = '';

    // show preview if we have at least the URL
    if (!empty ($url)) {
        // filter them for the preview
        $p_title = TRB_filterTitle($title);
        $p_excerpt = TRB_filterExcerpt($excerpt);
        $p_blog = TRB_filterBlogname($blog);

        // MT and other weblogs will shorten the excerpt like this
        if (utf8_strlen($p_excerpt) > 255) {
            $p_excerpt = utf8_substr($p_excerpt, 0, 252) . '...';
        }

        $preview_text .= COM_startBlock($LANG_TRB['preview']);

        $preview = new Template($_CONF['path_layout'] . 'trackback');
        $preview->set_file(array ('comment' => 'trackbackcomment.thtml'));
        $comment = TRB_formatComment($url, $p_title, $p_blog, $p_excerpt);
        $preview->set_var('formatted_comment', $comment);
        $preview->parse('output', 'comment');
        $preview_text = $preview->finish($preview->get_var ('output'));

        $preview_text .= COM_endBlock ();
    }

    if (empty($url) && empty($blog)) {
        $blog = htmlspecialchars($_CONF['site_name'],ENT_COMPAT,COM_getEncodingt());
    }
    $title = htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt());
    $excerpt = htmlspecialchars($excerpt, ENT_NOQUOTES,COM_getEncodingt());

    $retval .= COM_startBlock($LANG_TRB['editor_title'], $_CONF['site_url']
                               . '/docs/trackback.html#trackback',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/trackback.php',
              'text' => $LANG_ADMIN['tb_list']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_TRB['trb_explain'],
        $_CONF['layout_url'] . '/images/icons/trackback.' . $_IMAGE_TYPE
    );

    $retval .= $preview_text;

    $template = new Template($_CONF['path_layout'] . 'admin/trackback');
    $template->set_file(array ('editor' => 'trackbackeditor.thtml'));

    $template->set_var('php_self', $_CONF['site_admin_url']
                                    . '/trackback.php');

    if (empty($url) || empty ($title)) {
        $template->set_var('lang_explain', $LANG_TRB['editor_intro_none']);
    } else {
        $template->set_var('lang_explain',sprintf ($LANG_TRB['editor_intro'], $url, $title));
    }
    $template->set_var('lang_trackback_url', $LANG_TRB['trackback_url']);
    $template->set_var('lang_entry_url', $LANG_TRB['entry_url']);
    $template->set_var('lang_title', $LANG_TRB['entry_title']);
    $template->set_var('lang_blog_name', $LANG_TRB['blog_name']);
    $template->set_var('lang_excerpt', $LANG_TRB['excerpt']);
    $template->set_var('lang_excerpt_truncated', $LANG_TRB['truncate_warning']);
    $template->set_var('lang_send', $LANG_TRB['button_send']);
    $template->set_var('lang_preview', $LANG_TRB['button_preview']);

    $template->set_var('max_url_length', 255);
    $template->set_var('target_url', $target);
    $template->set_var('url', $url);
    $template->set_var('title', $title);
    $template->set_var('blog_name', $blog);
    $template->set_var('excerpt', $excerpt);
    $template->set_var('gltoken_name', CSRF_TOKEN);
    $template->set_var('gltoken', SEC_createToken());

    $template->parse('output', 'editor');
    $retval .= $template->finish($template->get_var('output'));

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
* Deletes a trackback comment. Checks if the current user has proper
* permissions first.
*
* @param    int     $id     ID of the trackback comment to delete
* @return   string          HTML redirect
*
*/
function TRACKBACK_delete($id)
{
    global $_TABLES;

    $cid = DB_escapeString($id);
    $result = DB_query("SELECT sid,type FROM {$_TABLES['trackback']} WHERE cid = '".DB_escapeString($cid)."'");
    $C = DB_fetchArray($result);
    $sid = $C['sid'];
    $type = $C['type'];

    $url = TRACKBACK_getItemInfo($type, $sid, 'url');

    if (TRB_allowDelete($sid, $type)) {
        TRB_deleteTrackbackComment($id);
        if ($type == 'article') {
            DB_query("UPDATE {$_TABLES['stories']} SET trackbacks = trackbacks - 1 WHERE (sid = '".DB_escapeString($sid)."')");
            $c = Cache::getInstance()->deleteItemsByTag('story_'.$sid);
        }
        $msg = 62;
    } else {
        $msg = 63;
    }
    COM_setMessage($msg);
    echo COM_refresh($url);
}

/**
* Show an error or warning message
*
* @param    string  $title      block title
* @param    string  $message    the actual message
* @return   string              HTML for the message block
*
*/
function TRACKBACK_showMessage($title, $message)
{
    $retval = '';

    $retval .= COM_showMessageText($message,$title,true,'error');
    return $retval;
}

/**
* Send a Pingback to all the links in our entry
*
* @param    string  $type   type of entry we're advertising ('article' = story)
* @param    string  $id     ID of that entry
* @return   string          pingback results
*
*/
function TRACKBACK_sendPingbacks($type, $id)
{
    global $_CONF, $LANG_TRB;

    $retval = '';

    $itemInfo = TRACKBACK_getItemInfo($type, $id, 'url,description');
    $url = $itemInfo['url'];
    $text = $itemInfo['description'];

    // extract all links from the text
    preg_match_all("/<a[^>]*href=[\"']([^\"']*)[\"'][^>]*>(.*?)<\/a>/i", $text,$matches);
    $numlinks = count($matches[0]);
    if ($numlinks > 0) {
        $links = array();
        for ($i = 0; $i < $numlinks; $i++) {
            if (!isset($links[$matches[1][$i]])) {
                $links[$matches[1][$i]] = $matches[2][$i];
            }
        }

        $template = new Template($_CONF['path_layout'] . 'admin/trackback');
        $template->set_file(array ('list' => 'pingbacklist.thtml',
                                   'item' => 'pingbackitem.thtml'));
        $template->set_var('lang_resend', $LANG_TRB['resend']);
        $template->set_var('lang_results', $LANG_TRB['pingback_results']);

        $counter = 1;
        foreach ($links as $URLtoPing => $linktext) {
            $result = PNB_sendPingback($url, $URLtoPing);
            $resend = '';
            if (empty ($result)) {
                $result = '<b>' . $LANG_TRB['pingback_success'] . '</b>';
            } else if ($result != $LANG_TRB['no_pingback_url']) {
                $result = '<span class="warningsmall">' . $result . '</span>';
                // TBD: $resend = '...';
            }
            $parts = parse_url($URLtoPing);

            $template->set_var('url_to_ping', $URLtoPing);
            $template->set_var('link_text', $linktext);
            $template->set_var('host_name', $parts['host']);
            $template->set_var('pingback_result', $result);
            $template->set_var('resend', $resend);
            $template->set_var('alternate_row', ($counter % 2) == 0 ? 'row-even' : 'row-odd');
            $template->set_var('cssid',($i % 2) + 1);
            $template->parse('pingback_results', 'item', true);
            $counter++;
        }
        $template->parse('output', 'list');
        $retval .= $template->finish ($template->get_var ('output'));

    } else {
        $retval = '<p>' . $LANG_TRB['no_links_pingback'] . '</p>';
    }

    return $retval;
}

function TRACKBACK_pingForm($targetUrl = '')
{
    global $_CONF, $LANG_TRB, $LANG_ADMIN, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';
    $retval .= COM_startBlock($LANG_TRB['pingback_button'],
                               $_CONF['site_url'] . '/docs/trackback.html',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/trackback.php',
              'text' => $LANG_ADMIN['tb_list']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_TRB['ping_explain'],
        $_CONF['layout_url'] . '/images/icons/trackback.' . $_IMAGE_TYPE
    );

    $template = new Template($_CONF['path_layout'] . 'admin/trackback');
    $template->set_file(array('list' => 'pingbackform.thtml'));

    $template->set_var('lang_explain', $LANG_TRB['pingback_explain']);
    $template->set_var('lang_pingback_url', $LANG_TRB['pingback_url']);
    $template->set_var('lang_site_url', $LANG_TRB['site_url']);
    $template->set_var('lang_send', $LANG_TRB['button_send']);
    $template->set_var('max_url_length', 255);

    $template->set_var('target_url', $targetUrl);
    $template->set_var('gltoken_name', CSRF_TOKEN);
    $template->set_var('gltoken', SEC_createToken());

    $template->parse('output', 'list');
    $retval .= $template->finish($template->get_var('output'));

    $retval .= COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/**
* Ping weblog directory services
*
* @param    string  $type   type of entry we're advertising ('article' = story)
* @param    string  $id     ID of that entry
* @return   string          result of the pings
*
*/
function TRACKBACK_sendPings($type, $id)
{
    global $_CONF, $_TABLES, $LANG_TRB;

    $retval = '';

    $itemInfo = TRACKBACK_getItemInfo($type, $id, 'url,feed');
    $itemurl = $itemInfo['url'];
    $feedurl = $itemInfo['feed'];

    $template = new Template($_CONF['path_layout'] . 'admin/trackback');
    $template->set_file(array ('list' => 'pinglist.thtml',
                                'item' => 'pingitem.thtml'));
    $template->set_var('lang_resend', $LANG_TRB['resend']);
    $template->set_var('lang_results', $LANG_TRB['ping_results']);

    $result = DB_query("SELECT ping_url,method,name,site_url FROM {$_TABLES['pingservice']} WHERE is_enabled = 1");
    $services = DB_numRows($result);
    if ($services > 0) {
        for ($i = 0; $i < $services; $i++) {
            $A = DB_fetchArray($result);
            $resend = '';
            if ($A['method'] == 'weblogUpdates.ping') {
                $pinged = PNB_sendPing($A['ping_url'], $_CONF['site_name'],
                                        $_CONF['site_url'], $itemurl);
            } else if ($A['method'] == 'weblogUpdates.extendedPing') {
                $pinged = PNB_sendExtendedPing($A['ping_url'],
                            $_CONF['site_name'], $_CONF['site_url'], $itemurl,
                            $feedurl);
            } else {
                $pinged = $LANG_TRB['unknown_method'] . ': ' . $A['method'];
            }
            if (empty ($pinged)) {
                $pinged = '<b>' . $LANG_TRB['ping_success'] . '</b>';
            } else {
                $pinged = '<span class="warningsmall">' . $pinged . '</span>';
            }

            $template->set_var('service_name', $A['name']);
            $template->set_var('service_url', $A['site_url']);
            $template->set_var('service_ping_url', $A['ping_url']);
            $template->set_var('ping_result', $pinged);
            $template->set_var('resend', $resend);
            $template->set_var('alternate_row',
                                (($i + 1) % 2) == 0 ? 'row-even' : 'row-odd');
            $template->set_var('cssid', ($i % 2) + 1);
            $template->parse('ping_results', 'item', true);
        }
    } else {
        $template->set_var('ping_results', '<tr><td colspan="2">' .
                            $LANG_TRB['no_services'] . '</td></tr>');
    }
    $template->set_var('gltoken_name', CSRF_TOKEN);
    $template->set_var('gltoken', SEC_createToken());
    $template->parse('output', 'list');
    $retval .= $template->finish($template->get_var ('output'));

    return $retval;
}

/**
* Prepare a list of all links in a story/item so that we can ask the user
* which one to send the trackback to.
*
* @param    string  $type   type of entry ('article' = story, etc.)
* @param    string  $id     ID of that entry
* @param    string  $text   text of that entry, to get the links from
* @return   string          formatted list of links
*
*/
function TRACKBACK_prepAutoDetect($type, $id, $text)
{
    global $_CONF, $LANG_TRB;

    $retval = '';

    $baseurl = $_CONF['site_admin_url']
             . '/trackback.php?mode=autodetect&amp;id=' . $id;
    if ($type != 'article') {
        $baseurl .= '&type' . $type;
    }

    // extract all links from the text
    preg_match_all("/<a[^>]*href=[\"']([^\"']*)[\"'][^>]*>(.*?)<\/a>/i", $text,
                    $matches);
    $numlinks = count($matches[0]);

    if ($numlinks == 1) {
        // skip the link selection when there's only one link in the story
        $url = urlencode($matches[1][0]);
        $link = $baseurl .= '&amp;url=' . $url;

        echo COM_refresh($link);
        exit;
    } else if ($numlinks > 0) {
        $template = new Template($_CONF['path_layout'] . 'admin/trackback');
        $template->set_file(array ('list' => 'autodetectlist.thtml',
                                    'item' => 'autodetectitem.thtml'));

        $url = $_CONF['site_admin_url'] . '/trackback.php?mode=new&amp;id=' . $id;
        if ($type != 'article') {
            $url .= '&amp;type=' . $type;
        }
        $template->set_var('lang_trackback_explain',
                            sprintf($LANG_TRB['trackback_explain'], $url));

        for ($i = 0; $i < $numlinks; $i++) {
            $url = urlencode($matches[1][$i]);
            $link = $baseurl .= '&amp;url=' . $url;

            $template->set_var('autodetect_link', $link);
            $template->set_var('link_text', $matches[2][$i]);
            $template->set_var('link_url', $matches[1][$i]);
            $template->set_var('alternate_row',
                    (($i + 1) % 2) == 0 ? 'row-even' : 'row-odd');
            $template->set_var('cssid', ($i % 2) + 1);
            $template->parse('autodetect_items', 'item', true);
        }
        $template->parse('output', 'list');
        $retval .= $template->finish($template->get_var('output'));
    } else {
        $retval .= $LANG_TRB['no_links_trackback'];
    }

    return $retval;
}

/**
* Wrapper for STORY_getItemInfo / PLG_getItemInfo to keep things readable
*
* @param    string  $type   type of entry ('article' = story, else plugin)
* @param    string  $id     ID of that entry
* @param    string  $what   info requested
* @return   mixed           requested info, as a string or array of strings
*
*/
function TRACKBACK_getItemInfo($type, $id, $what)
{
    return PLG_getItemInfo($type, $id, $what);

}

/**
 * used for the list of ping services in admin/trackback.php
 *
 */
function TRACKBACK_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ADMIN, $LANG_TRB;

    $retval = '';
    $enabled = ($A['is_enabled'] == 1) ? true : false;

    switch($fieldname) {

        case "edit":
            $retval = FieldList::edit(array(
                'url' => $_CONF['site_admin_url'].'/trackback.php?mode=editservice&amp;service_id='.$A['pid'],
                'attr' => array(
                    'title' => $LANG_ADMIN['edit'],
                )
            ));
            break;

        case "name":
            $retval = ($enabled) ? COM_createLink($A['name'], $A['site_url'],array('target'=>'_blank')) : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;

        case "method":
            if ($A['method'] == 'weblogUpdates.ping') {
                $retval = $LANG_TRB['ping_standard'];
            } else if ($A['method'] == 'weblogUpdates.extendedPing') {
                $retval = $LANG_TRB['ping_extended'];
            } else {
                $retval = '<span class="warningsmall">' .
                        $LANG_TRB['ping_unknown'] .  '</span>';
            }
            $retval = ($enabled) ? $retval : '<span class="disabledfield">' . $retval . '</span>';
            break;

        case "is_enabled":
            if ($enabled) {
                $switch = ' checked="checked"';
                $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
            } else {
                $switch = '';
                $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
            }
            $retval = '<input type="checkbox" name="changedservices[' . $A['pid'] . ']" ' . $title
                        . 'onclick="submit()" value="' . $A['pid'] . '"' . $switch . '/>';
            $retval .= '<input type="hidden" name="tbarray[' . $A['pid'] . ']" value="1" />';
            break;

        default:
            $retval = ($enabled) ? $fieldvalue : '<span class="disabledfield">' . $fieldvalue . '</span>';
            break;
    }

    return $retval;
}

/**
* Display a list of all weblog directory services in the system
*
* @return   string          HTML for the list
*
*/
function TRACKBACK_serviceList()
{
    global $LANG_ADMIN, $LANG_TRB, $_CONF, $_IMAGE_TYPE, $_TABLES;

    USES_lib_admin();

    $retval = '';
    $token = SEC_createToken();

    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_TRB['service'], 'field' => 'name', 'sort' => true),
        array('text' => $LANG_TRB['ping_method'], 'field' => 'method', 'sort' => true),
        array('text' => $LANG_TRB['service_ping_url'], 'field' => 'ping_url', 'sort' => true),
        array('text' => $LANG_ADMIN['enabled'], 'field' => 'is_enabled', 'sort' => false, 'align' => 'center')
    );

    $defsort_arr = array('field' => 'name', 'direction' => 'asc');

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/trackback.php',
              'text' => $LANG_ADMIN['tb_list'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/trackback.php?mode=editservice',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= COM_startBlock($LANG_TRB['services_headline'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_TRB['service_explain'],
        $_CONF['layout_url'] . '/images/icons/trackback.' . $_IMAGE_TYPE
    );

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/trackback.php',
        'help_url'   => $_CONF['site_url'] . '/docs/trackback.html#ping'
    );

    $query_arr = array(
        'table' => 'pingservice',
        'sql' => "SELECT * FROM {$_TABLES['pingservice']} WHERE 1=1",
        'query_fields' => array('name', 'ping_url'),
        'default_filter' => "",
        'no_data' => $LANG_TRB['no_services']
    );

    // this is a dummy variable so we know the form has been used if all services
    // should be disabled in order to disable the last one.
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'.$token.'"/>',
        'bottom' => '<input type="hidden" name="serviceChanger" value="true"/>'
    );

    $retval .= ADMIN_list('pingservice', 'TRACKBACK_getListField',
                          $header_arr, $text_arr, $query_arr, $defsort_arr,
                          '', $token, '', $form_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    if ($_CONF['trackback_enabled']) {
        $retval .= TRACKBACK_freshTrackback();
    }
    if ($_CONF['pingback_enabled']) {
        $retval .= TRACKBACK_freshPingback();
    }

    return $retval;
}

/**
* Display weblog directory service editor
*
* @param    int     $pid            ID of the service or 0 for new service
* @param    string  $msg            an error message to display
* @param    string  $new_name       name of the service
* @param    string  $new_site_url   URL of the service's site
* @param    string  $new_ping_url   URL to ping at the service
* @param    string  $new_method     ping method to use
* @param    int     $new_enabled    service is enabled (1) / disabled (0)
* @return   string                  HTML for the editor
*
*/
function TRACKBACK_editService($pid, $msg = '', $new_name = '', $new_site_url = '', $new_ping_url = '', $new_method = '', $new_enabled = -1)
{
    global $_CONF, $_TABLES, $LANG_TRB, $LANG_ADMIN, $MESSAGE, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';
    $editMode = false;

    if ($pid > 0) {
        $editMode = true;
        $result = DB_query("SELECT * FROM {$_TABLES['pingservice']} WHERE pid = '$pid'");
        $A = DB_fetchArray($result);
    } else {
        $A['is_enabled'] = 1;
        $A['method'] = 'weblogUpdates.ping';
    }

    if (!empty ($new_name)) {
        $A['name'] = $new_name;
    }
    if (!empty ($new_site_url)) {
        $A['site_url'] = $new_site_url;
    }
    if (!empty ($new_ping_url)) {
        $A['ping_url'] = $new_ping_url;
    }
    if (!empty ($new_method)) {
        $A['method'] = $new_method;
    }
    if ($new_enabled >= 0) {
        $A['is_enabled'] = $new_enabled;
    }

//    $retval .= COM_siteHeader('menu', $LANG_TRB['edit_service']);

    if (!empty ($msg)) {
        $retval .= TRACKBACK_showMessage('Error', $msg);
    }

    $retval .= COM_startBlock($LANG_TRB['edit_service'], $_CONF['site_url']
                               . '/docs/trackback.html#ping',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    if ( $editMode ) {
        $lang_create_or_edit = $LANG_ADMIN['edit'];
    } else {
        $lang_create_or_edit = $LANG_ADMIN['create_new'];
    }
    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/trackback.php',
              'text' => $LANG_ADMIN['tb_list']),
        array('url' => $_CONF['site_admin_url'] . '/trackback.php?mode=editservice',
              'text' => $lang_create_or_edit,'active'=>true),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_TRB['edit_explain'],
        $_CONF['layout_url'] . '/images/icons/trackback.' . $_IMAGE_TYPE
    );

    $template = new Template($_CONF['path_layout'] . 'admin/trackback');
    $template->set_file(array ('editor' => 'serviceeditor.thtml'));
    $template->set_var('max_url_length', 255);
    $template->set_var('method_ping', 'weblogUpdates.ping');
    $template->set_var('method_ping_extended', 'weblogUpdates.extendedPing');

    $template->set_var('lang_name', $LANG_TRB['service']);
    $template->set_var('lang_site_url', $LANG_TRB['service_website']);
    $template->set_var('lang_ping_url', $LANG_TRB['service_ping_url']);
    $template->set_var('lang_enabled', $LANG_ADMIN['enabled']);
    $template->set_var('lang_method', $LANG_TRB['ping_method']);
    $template->set_var('lang_method_standard', $LANG_TRB['ping_standard']);
    $template->set_var('lang_method_extended', $LANG_TRB['ping_extended']);
    $template->set_var('lang_save', $LANG_ADMIN['save']);
    $template->set_var('lang_cancel', $LANG_ADMIN['cancel']);

    if ($pid > 0) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="servicemode[2]"%s' . '/>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $template->set_var('delete_option',
                            sprintf ($delbutton, $jsconfirm));
        $template->set_var('delete_option_no_confirmation',
                            sprintf ($delbutton, ''));
    } else {
        $template->set_var('delete_option', '');
    }

    if (isset ($A['pid'])) {
        $template->set_var('service_id', $A['pid']);
    } else {
        $template->set_var('service_id', '');
    }
    if (isset ($A['name'])) {
        $template->set_var('service_name', $A['name']);
    } else {
        $template->set_var('service_name', '');
    }
    if (isset ($A['site_url'])) {
        $template->set_var('service_site_url', $A['site_url']);
    } else {
        $template->set_var('service_site_url', '');
    }
    if (isset ($A['ping_url'])) {
        $template->set_var('service_ping_url', $A['ping_url']);
    } else {
        $template->set_var('service_ping_url', '');
    }
    if ($A['is_enabled'] == 1) {
        $template->set_var('is_enabled', 'checked="checked"');
    } else {
        $template->set_var('is_enabled', '');
    }
    if ($A['method'] == 'weblogUpdates.ping') {
        $template->set_var('standard_is_checked', 'checked="checked"');
        $template->set_var('extended_is_checked', '');
    } else {
        $template->set_var('standard_is_checked', '');
        $template->set_var('extended_is_checked', 'checked="checked"');
    }
    $template->set_var('gltoken_name', CSRF_TOKEN);
    $template->set_var('gltoken', SEC_createToken());

    $template->parse ('output', 'editor');
    $retval .= $template->finish($template->get_var ('output'));

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
//    $retval .= COM_siteFooter();

    return $retval;
}

/**
* Save information of a weblog directory service
*
* @param    int     $pid        ID of service or 0 for new entry
* @param    string  $name       name of the service
* @param    string  $site_url   Homepage URL of the service
* @param    string  $ping_url   URL to ping at the service
* @param    string  $method     method used for the ping
* @param    string  $enabled    'on' when enabled
* @return   string              HTML redirect or service editor
*
*/
function TRACKBACK_saveService($pid, $name, $site_url, $ping_url, $method, $enabled)
{
    global $_CONF, $_TABLES, $LANG_TRB;

    $enabled = ($enabled == 'on' ? 1 : 0);
    if ($method == 'extended') {
        $method = 'weblogUpdates.extendedPing';
    } else {
        $method = 'weblogUpdates.ping';
    }

    $name     = strip_tags($name);
    $site_url = strip_tags($site_url);
    $ping_url = strip_tags($ping_url);

    $errormsg = '';
    if (empty ($name)) {
        $errormsg = $LANG_TRB['error_site_name'];
    } else {
        // all URLs must start with http: or https:
        $parts = explode(':', $site_url);
        if (($parts[0] != 'http') && ($parts[0] != 'https')) {
            $errormsg = $LANG_TRB['error_site_url'];
        } else {
            $parts = explode(':', $ping_url);
            if (($parts[0] != 'http') && ($parts[0] != 'https')) {
                $errormsg = $LANG_TRB['error_ping_url'];
            }
        }
    }

    if (!empty ($errormsg)) {
        return TRACKBACK_editService($pid, $errormsg, $name, $site_url, $ping_url,
                                $method, $enabled);
    }

    $name     = DB_escapeString($name);
    $site_url = DB_escapeString($site_url);
    $ping_url = DB_escapeString($ping_url);

    if ($pid > 0) {
        DB_save($_TABLES['pingservice'],
                 'pid,name,site_url,ping_url,method,is_enabled',
                 "'$pid','$name','$site_url','$ping_url','$method','$enabled'");
    } else {
        DB_save($_TABLES['pingservice'],
                 'name,site_url,ping_url,method,is_enabled',
                 "'$name','$site_url','$ping_url','$method','$enabled'");
    }

    COM_setMessage(65);
    return COM_refresh ($_CONF['site_admin_url']
                        . '/trackback.php?mode=listservice');
}

/**
* Toggle status of a ping service from enabled to disabled and back
*
* @param    int     $pid    ID of the service
* @return   void
*
*/
function TRACKBACK_toggleStatus($pid_arr,$tbarray)
{
    global $_TABLES;

    if (isset($tbarray) && is_array($tbarray) ) {
        foreach ($tbarray AS $tid => $junk ) {
            $tid = intval($tid);
            if ( isset($pid_arr[$tid]) ) {
                DB_query("UPDATE {$_TABLES['pingservice']} SET is_enabled = '1' WHERE pid = $tid");
            } else {
                DB_query("UPDATE {$_TABLES['pingservice']} SET is_enabled = '0' WHERE pid = $tid");
            }
        }
    }
}

/**
* Display a note about how trackbacks are supposed to be used
*
*/
function TRACKBACK_freshTrackback()
{
    global $_CONF, $LANG_TRB;

    $retval = '';

    $freshurl = $_CONF['site_admin_url'] . '/trackback.php?mode=fresh';

    $retval .= COM_startBlock($LANG_TRB['trackback'],
                               $_CONF['site_url'] . '/docs/trackback.html',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= sprintf($LANG_TRB['trackback_note'], $freshurl);
    $retval .= COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/**
* Display a note about how pingbacks are supposed to be used
*
*/
function TRACKBACK_freshPingback()
{
    global $_CONF, $LANG_TRB;

    $retval = '';

    $freshurl = $_CONF['site_admin_url'] . '/trackback.php?mode=freepb';

    $retval .= COM_startBlock($LANG_TRB['pingback'],
                               $_CONF['site_url'] . '/docs/trackback.html',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= sprintf($LANG_TRB['pingback_note'], $freshurl);
    $retval .= COM_endBlock(COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}


// MAIN ========================================================================


$mode = '';

if ($_CONF['ping_enabled'] && isset($_POST['serviceChanger']) && SEC_checkToken()) {
    $changedservices = array();
    if (isset($_POST['changedservices'])) {
        $changedservices = $_POST['changedservices'];
    }
    $tbarray = array();
    if ( isset($_POST['tbarray']) ) {
        $tbarray = $_POST['tbarray'];
    }
    TRACKBACK_toggleStatus($changedservices,$tbarray);
}

if (isset ($_POST['mode']) && is_array($_POST['mode'])) {
    $mode = $_POST['mode'];
    if (isset ($mode[0])) {
        $mode = 'send';
    } else if (isset ($mode[1])) {
        $mode = 'preview';
    } else if (isset ($mode[2])) {
        $mode = 'sendpingback';
    } else {
        $mode = '';
    }
} else if (isset ($_POST['servicemode']) && is_array($_POST['servicemode'])) {
    $mode = $_POST['servicemode'];
    if (isset ($mode[0])) {
        $mode = 'saveservice';
    } else if (isset ($mode[2])) {
        $mode = 'deleteservice';
    } else { // $mode[1], Cancel
        $mode = '';
    }
} else {
    if (isset($_REQUEST['mode'])) {
        $mode = COM_applyFilter($_REQUEST['mode']);
    }
}

// sanity check for modes, depending on enabled features ...
if (!$_CONF['ping_enabled']) {
    if (($mode == 'deleteservice') || ($mode == 'saveservice') ||
            ($mode == 'editservice')) {
        $mode = '';
    }
}
if (!$_CONF['trackback_enabled']) {
    if (($mode == 'send') || ($mode == 'new') || ($mode == 'pretrackback') ||
            ($mode == 'autodetect') || ($mode == 'preview')) {
        $mode = '';
    }
}
if (!$_CONF['pingback_enabled']) {
    if ($mode == 'pingback') {
        $mode = '';
    }
}
if (!$_CONF['trackback_enabled'] && !$_CONF['pingback_enabled']) {
    if ($mode == 'delete') {
        $mode = '';
    }
}

// default action depends on which features are enabled ...
if (empty ($mode)) {
    if ($_CONF['ping_enabled']) {
        $mode = 'listservice';
    } else if ($_CONF['trackback_enabled']) {
        $mode = 'fresh';
    } else if ($_CONF['pinback_enabled']) {
        $mode = 'freepb';
    }
}

if (($mode == 'delete') && SEC_checkToken()) {
    $cid = COM_applyFilter($_REQUEST['cid'], true);
    if ($cid > 0) {
        TRACKBACK_delete($cid); // does not return
    } else {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
    }
} else if ($mode == 'send') {
    $target = COM_applyFilter ($_POST['target']);
    $url = COM_applyFilter ($_POST['url']);
    $title = $_POST['title'];
    $excerpt = $_POST['excerpt'];
    $blog = $_POST['blog'];

    if (empty ($target)) {
        $title = $LANG_TRB['trackback'];
        $page .= TRACKBACK_showMessage($LANG_TRB['target_missing'],$LANG_TRB['target_required']);
        $page .= TRACKBACK_edit($target, $url, $title, $excerpt, $blog);
    } else if (empty ($url)) {
        $title = $LANG_TRB['trackback'];
        $page .= TRACKBACK_showMessage($LANG_TRB['url_missing'],$LANG_TRB['url_required']);
        $page .= TRACKBACK_edit($target, $url, $title, $excerpt, $blog);
    } elseif (SEC_checkToken()) {
        // prepare for send
        $send_title = TRB_filterTitle($title);
        $send_excerpt = TRB_filterExcerpt($excerpt);
        $send_blog = TRB_filterBlogname($blog);

        $result = TRB_sendTrackbackPing($target, $url, $send_title,
                                         $send_excerpt, $send_blog);

        $title = $LANG_TRB['trackback'];
        if ($result === true) {
            $page .= COM_showMessage(64);
            $page .= TRACKBACK_edit();
        } else {
            $message = '<p>' . $LANG_TRB['send_error_details']
                     . '<br/><span class="warningsmall">'
                     . htmlspecialchars($result,ENT_COMPAT,COM_getEncodingt()) . '</span></p>';
            $page .= TRACKBACK_showMessage($LANG_TRB['send_error'], $message);

            // display editor with the same contents again
            $page .= TRACKBACK_edit($target, $url, $title, $excerpt, $blog);
        }
    }
} else if ($mode == 'new') {
    $type = COM_applyFilter($_REQUEST['type']);
    if (empty ($type)) {
        $type = 'article';
    }
    if ( $type != 'article' ) {
        if (!in_array($type,$_PLUGINS) ) {
            $type = 'article';
        }
    }

    $id = COM_sanitizeID(COM_applyFilter($_REQUEST['id']));
    if (!empty ($id)) {
        $itemInfo = TRACKBACK_getItemInfo($type, $id,'url,title,excerpt');
        $url = $itemInfo['url'];
        $title = $itemInfo['title'];
        $excerpt = $itemInfo['excerpt'];

        $excerpt = trim(strip_tags ($excerpt));
        $blog = TRB_filterBlogname($_CONF['site_name']);

        $title = $LANG_TRB['trackback'];
        $page .= TRACKBACK_edit($target, $url, $title, $excerpt, $blog);
    } else {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
    }
} else if ($mode == 'pingback') {
    $type = COM_applyFilter($_REQUEST['type']);
    if (empty ($type)) {
        $type = 'article';
    }
    $id = COM_applyFilter($_REQUEST['id']);
    if (!empty ($id)) {
        $title = $LANG_TRB['pingback'];
        $page .=  COM_startBlock($LANG_TRB['pingback_results'])
                  . TRACKBACK_sendPingbacks($type, $id)
                  . COM_endBlock();
    } else {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
    }
} else if ($mode == 'sendall') {
    $id = COM_sanitizeID(COM_applyFilter($_REQUEST['id']));
    if (empty ($id)) {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
    }
    $type = '';
    if (isset ($_REQUEST['type'])) {
        $type = COM_applyFilter($_REQUEST['type']);
    }
    if (empty ($type)) {
        $type = 'article';
    }

    $pingback_sent  = isset($_REQUEST['pingback_sent']);
    $ping_sent      = isset($_REQUEST['ping_sent']);
    $trackback_sent = isset($_REQUEST['trackback_sent']);

    $pingresult = '';
    if (isset ($_POST['what']) && is_array($_POST['what'])) {
        $what = $_POST['what'];
        if (isset ($what[0])) {         // Pingback
            $pingresult = TRACKBACK_sendPingbacks($type, $id);
            $pingback_sent = true;
        } else if (isset ($what[1])) {  // Ping
            $pingresult = TRACKBACK_sendPings($type, $id);
            $ping_sent = true;
        } else if (isset ($what[2])) {  // Trackback
            $url = $_CONF['site_admin_url']
                 . '/trackback.php?mode=pretrackback&amp;id=' . $id;
            if ($type != 'article') {
                $url .= '&amp;type=' . $type;
            }
            echo COM_refresh($url);
            exit;
        }
    }

    $title = TRACKBACK_getItemInfo($type, $id, 'title');

    $title = $LANG_TRB['send_pings'];
    $page .= COM_startBlock(sprintf ($LANG_TRB['send_pings_for'], $title));

    $template = new Template($_CONF['path_layout'] . 'admin/trackback');
    $template->set_file(array ('form' => 'pingform.thtml'));
    $template->set_var('php_self', $_CONF['site_admin_url']
                                    . '/trackback.php');
    $template->set_var('lang_may_take_a_while', $LANG_TRB['may_take_a_while']);
    $template->set_var('lang_ping_explain', $LANG_TRB['ping_all_explain']);

    $template->set_var('ping_results', $pingresult);

    if ($_CONF['pingback_enabled']) {
        if (!$pingback_sent) {
            $template->set_var('lang_pingback_button',
                                $LANG_TRB['pingback_button']);
            $template->set_var('lang_pingback_short',
                                $LANG_TRB['pingback_short']);
            $button = '<input type="submit" name="what[0]" value="'
                    . $LANG_TRB['pingback_button'] . '"/>';
            $template->set_var('pingback_button', $button);
        }
    } else {
        $template->set_var('pingback_button', $LANG_TRB['pingback_disabled']);
    }
    if ($_CONF['ping_enabled']) {
        if (!$ping_sent) {
            $template->set_var('lang_ping_button', $LANG_TRB['ping_button']);
            $template->set_var('lang_ping_short', $LANG_TRB['ping_short']);
            $button = '<input type="submit" name="what[1]" value="'
                    . $LANG_TRB['ping_button'] . '"/>';
            $template->set_var('ping_button', $button);
        }
    } else {
        $template->set_var('ping_button', $LANG_TRB['ping_disabled']);
    }
    if ($_CONF['trackback_enabled']) {
        if (!$trackback_sent) {
            $template->set_var('lang_trackback_button',
                                $LANG_TRB['trackback_button']);
            $template->set_var('lang_trackback_short',
                                $LANG_TRB['trackback_short']);
            $button = '<input type="submit" name="what[2]" value="'
                    . $LANG_TRB['trackback_button'] . '"/>';
            $template->set_var('trackback_button', $button);
        }
    } else {
        $template->set_var('trackback_button', $LANG_TRB['trackback_disabled']);
    }

    $hidden = '';
    if ($pingback_sent) {
        $hidden .= '<input type="hidden" name="pingback_sent" value="1"/>';
    }
    if ($ping_sent) {
        $hidden .= '<input type="hidden" name="ping_sent" value="1"/>';
    }
    if ($trackback_sent) {
        $hidden .= '<input type="hidden" name="trackback_sent" value="1" />';
    }
    $hidden .= '<input type="hidden" name="id" value="' . $id . '" />';
    $hidden .= '<input type="hidden" name="type" value="' . $type . '" />';
    $hidden .= '<input type="hidden" name="mode" value="sendall" />';
    $template->set_var('hidden_input_fields', $hidden);

    $template->parse('output', 'form');
    $page .= $template->finish($template->get_var('output'));

    $page .= COM_endBlock();
} else if ($mode == 'pretrackback') {
    $id = COM_sanitizeID(COM_applyFilter($_REQUEST['id']));
    if (empty ($id)) {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
    }
    $type = '';
    if (isset ($_REQUEST['type'])) {
        $type = COM_applyFilter($_REQUEST['type']);
    }
    if (empty ($type)) {
        $type = 'article';
    }

    $fulltext = TRACKBACK_getItemInfo($type, $id, 'description');

    $page .=  COM_startBlock($LANG_TRB['select_url'], $_CONF['site_url']
                                . '/docs/trackback.html#trackback')
              . TRACKBACK_prepAutoDetect($type, $id, $fulltext)
              . COM_endBlock();
} else if ($mode == 'autodetect') {
    $id = COM_applyFilter($_REQUEST['id']);
    $url = $_REQUEST['url'];
    if (empty ($id) || empty ($url)) {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        exit;
    }
    $type = '';
    if (isset ($_REQUEST['type'])) {
        $type = COM_applyFilter($_REQUEST['type']);
    }
    if (empty ($type)) {
        $type = 'article';
    }

    $trackbackUrl = TRB_detectTrackbackUrl($url);

    $itemInfo = TRACKBACK_getItemInfo($type, $id,'url,title,excerpt');
    $url = $itemInfo['url'];
    $title = $itemInfo['title'];
    $excerpt = $itemInfo['excerpt'];

    $excerpt = trim(strip_tags($excerpt));
    $blog = TRB_filterBlogname($_CONF['site_name']);

    if ($trackbackUrl === false) {
        $page .= TRACKBACK_showMessage($LANG_TRB['not_found'],$LANG_TRB['autodetect_failed']);
    }
    $page .= TRACKBACK_edit($trackbackUrl, $url, $title, $excerpt, $blog);
} else if (($mode == 'fresh') || ($mode == 'preview')) {
    $title = $LANG_TRB['trackback'];

    $msg = COM_getMessage();
    if ( $msg > 0 ) {
        $page .= COM_showMessage($msg);
    }

    $target = '';
    if (isset ($_REQUEST['target'])) {
        $target = COM_applyFilter($_REQUEST['target']);
    }
    $url = '';
    if (isset ($_REQUEST['url'])) {
        $url = COM_applyFilter($_REQUEST['url']);
    }
    $title = '';
    if (isset ($_REQUEST['title'])) {
        $title = $_REQUEST['title'];
    }
    $excerpt = '';
    if (isset ($_REQUEST['excerpt'])) {
        $excerpt = $_REQUEST['excerpt'];
    }
    $blog = '';
    if (isset ($_REQUEST['blog'])) {
        $blog = $_REQUEST['blog'];
    }

    if (isset ($_REQUEST['id']) && isset ($_REQUEST['type'])) {
        $id = COM_applyFilter ($_REQUEST['id']);
        $type = COM_applyFilter ($_REQUEST['type']);
        if (!empty ($id) && !empty ($type)) {
            $itemInfo = TRACKBACK_getItemInfo($type, $id, 'url,title,excerpt');
            $newurl = $itemInfo['url'];
            $newtitle = $itemInfo['title'];
            $newexcerpt = $itemInfo['excerpt'];
            $newexcerpt = trim(strip_tags($newexcerpt));

            if (empty ($url) && !empty ($newurl)) {
                $url = $newurl;
            }
            if (empty ($title) && !empty ($newtitle)) {
                $title = $newtitle;
            }
            if (empty ($newexcerpt) && !empty ($newexcerpt)) {
                $excerpt = $newexcerpt;
            }

            if (empty ($blog)) {
                $blog = TRB_filterBlogname($_CONF['site_name']);
            }
        }
    }

    if (($mode == 'preview') && empty ($url)) {
        $page .= TRACKBACK_showMessage($LANG_TRB['url_missing'],
                                          $LANG_TRB['url_required']);
    }

    $page .= TRACKBACK_edit($target, $url, $title, $excerpt, $blog);

} elseif (($mode == 'deleteservice') && SEC_checkToken()) {
    $pid = COM_applyFilter ($_POST['service_id'], true);
    if ($pid > 0) {
        DB_delete($_TABLES['pingservice'], 'pid', $pid);
        COM_setMessage(66);
        echo COM_refresh($_CONF['site_admin_url'].'/trackback.php?mode=listservice');
    } else {
        echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
    }
} elseif (($mode == 'saveservice') && SEC_checkToken()) {
    $is_enabled = '';
    if (isset($_POST['is_enabled'])) {
        $is_enabled = $_POST['is_enabled'];
    }
    $page .= TRACKBACK_saveService(COM_applyFilter($_POST['service_id'], true),
                            $_POST['service_name'], $_POST['service_site_url'],
                            $_POST['service_ping_url'], $_POST['method'],
                            $is_enabled);
} else if ($mode == 'editservice') {
    $service_id = 0;
    if (isset ($_GET['service_id'])) {
        $service_id = COM_applyFilter($_GET['service_id'], true);
    }
    $pid = COM_applyFilter($service_id, true);

    $page .= TRACKBACK_editService($pid);
} else if ($mode == 'listservice') {
    $title = $LANG_TRB['services_headline'];
    $msg = COM_getMessage();
    if ( $msg > 0 ) {
        $page .= COM_showMessage(COM_applyFilter($msg, true));
    }
    $page .= TRACKBACK_serviceList();
} else if ($mode == 'freepb') {
    $title = $LANG_TRB['pingback'];
    $page .= TRACKBACK_pingForm();
} else if ($mode == 'sendpingback') {
    $target = COM_applyFilter($_POST['target']);
    $title = $LANG_TRB['pingback'];
    if (empty ($target)) {
        $page .= TRACKBACK_showMessage($LANG_TRB['pbtarget_missing'],$LANG_TRB['pbtarget_required']);
    } elseif (SEC_checkToken()) {
        $result = PNB_sendPingback($_CONF['site_url'], $target);
        if (empty ($result)) {
            $page .= COM_showMessage(74);
            $target = '';
        } else {
            $message = '<p>' . $LANG_TRB['pb_error_details'] . '<br />'
                     . '<span class="warningsmall">'
                     . htmlspecialchars($result,ENT_COMPAT,COM_getEncodingt()) . '</span></p>';
            $page .= TRACKBACK_showMessage($LANG_TRB['send_error'], $message);
        }
    }
    $page .= TRACKBACK_pingForm($target);
} else {
    echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
}

if ( !isset($title ) ) $title = $LANG_TRB['trackback'];

$display .= COM_siteHeader('menu', $title);
$display .= $page;
$display .= COM_siteFooter();
echo $display;

?>