<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-trackback.php                                                        |
// |                                                                          |
// | Functions needed to handle trackback comments.                           |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2005-2008 by the following authors:                        |
// |                                                                          |
// | Author: Dirk Haun - dirk AT haun-online DOT de                           |
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

if (!defined ('GVERSION')) {
    die('This file can not be used on its own!');
}

// result codes for TRB_saveTrackbackComment
define('TRB_SAVE_OK', 0);
define('TRB_SAVE_SPAM', -1);
define('TRB_SAVE_REJECT', -2);

// set to true to log rejected Trackbacks
$_TRB_LOG_REJECTS = false;

/**
 * Send a trackback response message
 *
 * @param    int    $error       0 = OK, 1 = an error occured
 * @param    string $errormsg    the error message (ignored for $error == 0)
 * @param    int    $http_status optional HTTP status code
 * @param    string $http_text   optional HTTP status code text
 * @return   void
 */
function TRB_sendTrackbackResponse($error, $errormsg = '', $http_status = 200, $http_text = "OK")
{
    $display = '<?xml version="1.0" encoding="'.COM_getCharset().'"?>' . LB
        . '<response>' . LB
        . '<error>' . $error . '</error>' . LB;
    if (($error != 0) && !empty($errormsg)) {
        // we're creating XML, so encode these ...
        $errormsg = str_replace(array('<', '>', "'"),
            array('&lt;', '&gt;', '&apos;'), $errormsg);
        $display .= '<message>' . $errormsg . '</message>' . LB;
    }
    $display .= '</response>';

    if ($http_status != 200) {
        header("HTTP/1.1 $http_status $http_text");
        header("Status: $http_status $http_text");
    }
    header("Content-type: text/xml; charset=" . COM_getCharset() );
    echo $display;
    exit;
}

/**
 * Helper function for the curious: Log rejected trackbacks
 *
 * @param    string $logmsg Message to log
 * @return   void
 */
function TRB_logRejected($reason, $url = '')
{
    global $_TRB_LOG_REJECTS;

    if ($_TRB_LOG_REJECTS) {

        $logmsg = 'Trackback from IP ' . $_SERVER['REAL_ADDR']
            . ' rejected for ' . $reason . ', URL: ' . $url;

        if (function_exists('SPAMX_log')) {
            SPAMX_log($logmsg);
        } else {
            COM_errorLog($logmsg);
        }
    }
}

/**
 * Creates a piece of RDF pointing out the trackback URL
 * Note: When putting this in an HTML page, it may be advisable to enclose it
 *       in HTML comments, i.e. <!-- ... -->
 *
 * @param    string $article_url   URL of our entry
 * @param    string $title         title of that entry
 * @param    string $trackback_url trackback URL for our entry
 * @return   string                  RDF code with our information embedded
 */
function TRB_trackbackRdf($article_url, $title, $trackback_url)
{
    // we're creating XML, so encode these ...
    $title = str_replace(array('<', '>', "'"),
        array('&lt;', '&gt;', '&apos;'), $title);

    $retval = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"' . LB
        . '         xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/"' . LB
        . '         xmlns:dc="http://purl.org/dc/elements/1.1/">' . LB;
    $retval .= '<rdf:Description' . LB
        . '    rdf:about="' . $article_url . '"' . LB
        . '    trackback:ping="' . $trackback_url . '"' . LB
        . '    dc:title="' . $title . '"' . LB
        . '    dc:identifier="' . $article_url . '" />' . LB;
    $retval .= '</rdf:RDF>';

    return $retval;
}

/**
 * Returns the trackback URL for an entry
 * Note: Trackback pings default to stories, so we leave off the type if it
 *       is 'article' to create shorter URLs.
 *
 * @param    string $id   the entry's ID
 * @param    string $type type of the entry ('article' = story, etc.)
 * @return   string          trackback URL for that entry
 */
function TRB_makeTrackbackUrl($id, $type = 'article')
{
    global $_CONF;

    $url = $_CONF['site_url'] . '/trackback.php?id=' . $id;
    if (!empty($type) && ($type != 'article')) {
        $url .= '&amp;type=' . $type;
    }

    return COM_buildUrl($url);
}

/**
 * Filter the title for a trackback comment we've received
 *
 * @param    string $title title of the comment
 * @return   string          filtered title
 */
function TRB_filterTitle($title)
{
    return htmlspecialchars(COM_checkWords(strip_tags($title)));
}

/**
 * Filter the blog name for a trackback comment we've received
 *
 * @param    string $blogname blog name for the comment
 * @return   string              filtered blog name
 */
function TRB_filterBlogname($blogname)
{
    return htmlspecialchars(COM_checkWords(strip_tags($blogname)));
}

/**
 * Filter the excerpt of a trackback comment we've received
 * Note: Does not truncate the excerpt.
 *
 * @param    string $excerpt excerpt of the trackback comment
 * @return   string              filtered excerpt
 */
function TRB_filterExcerpt($excerpt)
{
    return COM_checkWords(strip_tags($excerpt));
}

/**
 * Check if the current user is allowed to delete trackback comments.
 *
 * @param    string $sid  ID of the parent object of the comment
 * @param    string $type type of the parent object ('article' = story, etc.)
 * @return   boolean         true = user can delete the comment, false = nope
 */
function TRB_allowDelete($sid, $type)
{
    global $_TABLES;

    $allowed = false;

    if ($type == 'article') {
        $sid = DB_escapeString($sid);

        $sql = "SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['stories']} WHERE sid = '$sid'" . COM_getPermSql('AND', 0, 3);

        $result = DB_query($sql);
        $A = DB_fetchArray($result);

        if (SEC_hasRights('story.edit') && (SEC_hasAccess($A['owner_id'],
                    $A['group_id'], $A['perm_owner'], $A['perm_group'],
                    $A['perm_members'], $A['perm_anon']) == 3)) {
            $allowed = true;
        } else {
            $allowed = false;
        }
    } else {
        $allowed = PLG_handlePingComment($type, $sid, 'delete');
    }

    return $allowed;
}

/**
 * Check a trackback / pingback for spam
 *
 * @param    string $url     URL of the trackback comment
 * @param    string $title   title of the comment (set to $url if empty)
 * @param    string $blog    name of the blog that sent the comment
 * @param    string $excerpt excerpt from the comment
 * @return   int                 TRB_SAVE_OK or TRB_SAVE_SPAM
 */
function TRB_checkForSpam($url, $title = '', $blog = '', $excerpt = '')
{
    global $_CONF;

    $comment = TRB_formatComment($url, $title, $blog, $excerpt);

    $spamData = array(
        'username' => '',
        'email'    => '',
        'ip'       => $_SERVER['REAL_ADDR'],
        'type'     => 'trackback'
    );

    $result = PLG_checkforSpam($comment, $_CONF['spamx'],$spamData);

    if ($result > 0) {
        return TRB_SAVE_SPAM;
    }

    return TRB_SAVE_OK;
}

/**
 * Save a trackback (or pingback) comment.
 * Also filters parameters and handles multiple trackbacks from the same source.
 * Note: Spam check should have been done before calling this function.
 *
 * @param    string $sid     entry id
 * @param    string $type    type of entry ('article' = story, etc.)
 * @param    string $url     URL of the trackback comment
 * @param    string $title   title of the comment (set to $url if empty)
 * @param    string $blog    name of the blog that sent the comment
 * @param    string $excerpt excerpt from the comment
 * @return   int                 < 0: error, > 0: ID of the trackback comment
 */
function TRB_saveTrackbackComment($sid, $type, $url, $title = '', $blog = '', $excerpt = '')
{
    global $_CONF, $_TABLES;

    $url = COM_applyFilter($url);
    $title = TRB_filterTitle($title);
    $blog = TRB_filterBlogname($blog);
    $excerpt = TRB_filterExcerpt($excerpt);

    // MT does that, so follow its example ...
    if (utf8_strlen($excerpt) > 255) {
        $excerpt = utf8_substr($excerpt, 0, 252) . '...';
    }

    $title = str_replace(array('$', '{', '}'),
        array('&#36;', '&#123;', '&#126;'), $title);
    $excerpt = str_replace(array('$', '{', '}'),
        array('&#36;', '&#123;', '&#126;'), $excerpt);
    $blog = str_replace(array('$', '{', '}'),
        array('&#36;', '&#123;', '&#126;'), $blog);

    $url = DB_escapeString($url);
    $title = DB_escapeString($title);
    $blog = DB_escapeString($blog);
    $excerpt = DB_escapeString($excerpt);

    if ($_CONF['multiple_trackbacks'] == 0) {
        // multiple trackbacks not allowed - check if we have this one already
        if (DB_count($_TABLES['trackback'], array('url', 'sid', 'type'),
                array($url, $sid, $type)) >= 1
        ) {
            return TRB_SAVE_REJECT;
        }
    } else if ($_CONF['multiple_trackbacks'] == 1) {
        // delete any earlier trackbacks from the same URL
        DB_delete($_TABLES['trackback'], array('url', 'sid', 'type'),
            array(DB_escapeString($url), DB_escapeString($sid), DB_escapeString($type)));
    } // else: multiple trackbacks allowed

    DB_save ($_TABLES['trackback'], 'sid,url,title,blog,excerpt,date,type,ipaddress',
             "'".DB_escapeString($sid)."','$url','$title','$blog','$excerpt',NOW(),'".DB_escapeString($type)."','".DB_escapeString($_SERVER['REAL_ADDR'])."'");

    $comment_id = DB_insertId();

    if ($type == 'article') {
        DB_query("UPDATE {$_TABLES['stories']} SET trackbacks = trackbacks + 1 WHERE (sid = '$sid')");
    }
    $c = glFusion\Cache::getInstance();
    $c->deleteItemsByTag('whatsnew');
    $c->deleteItemsByTag('story_'.$sid);
    return $comment_id;
}

/**
 * Delete a trackback comment
 * Note: Permission checks have to be done by the caller.
 *
 * @param    int $cid ID of the trackback comment
 * @return   void
 */
function TRB_deleteTrackbackComment($cid)
{
    global $_TABLES;

    $cid = DB_escapeString($cid);
    DB_delete($_TABLES['trackback'], 'cid', $cid);
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
}

/**
 * Format one trackback comment for display
 * Note: $excerpt is not truncated - this should have been done elsewhere
 *
 * @param    string    $url           URL of the trackback comment
 * @param    string    $title         title of the comment (set to $url if empty)
 * @param    string    $blog          name of the blog that sent the comment
 * @param    string    $excerpt       excerpt from the comment
 * @param    timestamp $date          date and time when the comment was sent
 * @param    boolean   $delete_option whether to display a link to delete the trackback comment
 * @param    string    $cid           id of this trackback comment
 * @param    string    $ipaddress     IP address the comment was sent from
 * @param    string    $token         security token
 * @return   string                  HTML of the formatted trackback comment
 */
function TRB_formatComment($url, $title = '', $blog = '', $excerpt = '', $date = 0, $delete_option = false, $cid = '', $ipaddress = '', $token = '')
{
    global $_CONF, $LANG01, $LANG_TRB, $MESSAGE;

    if (empty($title)) {
        $title = $url;
    }

    if ($date == 0) {
        $date = time();
    }
    $dt = new Date($date,$_CONF['timezone']);
    $curtime = $dt->format($dt->getUserFormat(),true);

    $template = new Template ($_CONF['path_layout'] . 'trackback');
    $template->set_file (array ('comment' => 'formattedcomment.thtml'));
    $template->set_var('lang_from', $LANG_TRB['from']);
    $template->set_var('lang_tracked_on', $LANG_TRB['tracked_on']);
    $template->set_var('lang_readmore', $LANG_TRB['read_more']);

    $anchor = '<a href="' . $url . '">';
    $readmore = COM_createLink($LANG_TRB['read_more'], $url,$attr = array('target' => '_blank', 'rel' => 'noopener noreferrer'));

    $template->set_var('readmore_link', $readmore);
    $template->set_var('start_readmore_anchortag', $anchor);
    $template->set_var('end_readmore_anchortag', '</a>');

    $template->set_var('trackback_url', $url);
    $template->set_var('trackback_title', $title);
    $template->set_var('trackback_blog_name', $blog);
    $template->set_var('trackback_date', $curtime);

    if (empty($blog)) {
        $template->set_var('trackback_from_blog_name', '');
    } else {
        $template->set_var('trackback_from_blog_name', $LANG_TRB['from'] . ' '
            . $blog);
    }
    if (empty($excerpt)) {
        $template->set_var('trackback_excerpt', '');
        $template->set_var('trackback_excerpt_readmore', '');
        $template->set_var('excerpt_br', '');
    } else {
        $template->set_var('trackback_excerpt', $excerpt);
        $template->set_var('trackback_excerpt_readmore',
            $excerpt . ' ' . $readmore);
        $template->set_var('excerpt_br', '<br' . XHTML . '>');
    }

    $deloption = '';
    if ($delete_option) {
        $deloption .= '[ ';
        $deloption .= COM_createLink($LANG01[28], $_CONF['site_admin_url']
            . '/trackback.php?mode=delete&amp;cid=' . $cid . '&amp;'
            . CSRF_TOKEN . '=' . $token,
            array('onclick' => "return confirm('{$MESSAGE[76]}');")
        );
        if (!empty($ipaddress)) {
            if (empty($_CONF['ip_lookup'])) {
                $deloption .= ' | ' . $ipaddress;
            } else {
                $iplookup = str_replace('*', $ipaddress, $_CONF['ip_lookup']);
                $deloption .= ' | ' . COM_createLink($ipaddress, $iplookup,array('target'=>'_blank'));
            }
        }
        $deloption .= ' ]';
    }
    $template->set_var('delete_option', $deloption);
    $template->parse('output', 'comment');

    return $template->finish($template->get_var('output'));
}

/**
 * Perform a backlink check on an HTML page
 *
 * @param    string $body       complete HTML page to check
 * @param    string $urlToCheck URL to find in that page
 * @return   boolean             true: found a link to us; false: no link to us
 */
function TRB_containsBacklink($body, $urlToCheck)
{
    global $_CONF;

    if (($_CONF['check_trackback_link'] & 3) == 0) {
        // we shouldn't be here - don't do anything
        return true;
    }

    $retval = false;

    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    preg_match_all("/$regexp/siU", $body, $matches);

    for ($i = 0; $i < count($matches[2]); $i++) {
        if ($_CONF['check_trackback_link'] & 1) {
            if (strpos($matches[2][$i], $urlToCheck) === 0) {
                // found it!
                $retval = true;
                break;
            }
        } else {
            if ($matches[2][$i] == $urlToCheck) {
                // found it!
                $retval = true;
                break;
            }
        }
    }

    return $retval;
}

/**
 * Check if a given web page links to us
 *
 * @param    string $sid      ID of entry that got pinged
 * @param    string $type     type of that entry ('article' for stories, etc.)
 * @param    string $urlToGet URL of the page that supposedly links to us
 * @return   boolean             true = links to us, false = doesn't
 */
function TRB_linksToUs($sid, $type, $urlToGet)
{
    global $_CONF;

    if (!isset ($_CONF['check_trackback_link'])) {
        $_CONF['check_trackback_link'] = 2;
    }

    if (($_CONF['check_trackback_link'] & 3) == 0) {
        // we shouldn't be here - don't do anything
        return true;
    }

    $retval = false;

    if ($_CONF['check_trackback_link'] & 2) {
        // build the URL of the pinged page on our site
        $urlToCheck = PLG_getItemInfo($type, $sid, 'url');
    } else {
        // check only against the site_url
        $urlToCheck = $_CONF['site_url'];
    }

    $arguments = array();
    $response = '';

    $http = new http_class;
    $http->timeout=0;
    $http->data_timeout=0;
    $http->debug=0;
    $http->html_debug=0;
    $http->user_agent = 'glFusion/' . GVERSION;
    $url = $urlToGet;

    $error = $http->GetRequestArguments($url,$arguments);
    $error = $http->Open($arguments);
    $error = $http->SendRequest($arguments);
    if ( $error == "" ) {
        $http->ReadReplyHeaders($headers);
        if ( $http->response_status == 200 ) {
            $error = $http->ReadWholeReplyBody($body);
            if ( strlen($body) > 0 ) {
                $retval = TRB_containsBacklink($body, $urlToCheck);
            } else {
                COM_errorLog("Trackback verification: unable to retrieve response body");
            }
        } else {
            COM_errorLog("Trackback verification: Got HTTP response code ".$http->response_status." when requesting ".$urlToGet);
        }
    } else {
        COM_errorLog("Trackback verification: " . $error . " when requesting ".$urlToGet);
    }

    return $retval;
}

/**
 * Handles a trackback ping for an entry.
 * Also takes care of the speedlimit and spam. Assumes that the caller of this
 * function has already checked permissions!
 * Note: Error messages are XML-formatted and echo'd out directly, as they
 *       are supposed to be processed by some sort of software.
 *
 * @param    string $sid  ID of entry that got pinged
 * @param    string $type type of that entry ('article' for stories, etc.)
 * @return   boolean         true = success, false = an error occured
 *                        P.S. "Critical" errors are rejected with a HTTP 403 Forbidden status code.
 *                        According to RFC2616, this status code means
 *                        "The server understood the request, but is refusing to fulfill it.
 *                        Authorization will not help and the request SHOULD NOT be repeated."
 *                        See http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.4
 */
function TRB_handleTrackbackPing($sid, $type = 'article')
{
    global $_CONF, $_TABLES;

    // Note: Error messages are hard-coded in English since there is no way of
    // knowing which language the sender of the trackback ping may prefer.
    $TRB_ERROR = array(
        'no_url'     => 'No URL given.',
        'rejected'   => 'Multiple posts not allowed.',
        'spam'       => 'Spam detected.',
        'speedlimit' => 'Your last trackback comment was %d seconds ago. This site requires at least %d seconds between trackback comments.',
        'no_link'    => 'Trackback rejected as you do not seem to link to us.',
    );

    // the speed limit applies to trackback comments, too
    if (isset ($_CONF['trackbackspeedlimit'])) {
        $speedlimit = $_CONF['trackbackspeedlimit'];
    } else {
        $speedlimit = $_CONF['commentspeedlimit'];
    }
    COM_clearSpeedlimit($speedlimit, 'trackback');
    $last = COM_checkSpeedlimit('trackback');
    if ($last > 0) {
        TRB_sendTrackbackResponse(1, sprintf($TRB_ERROR['speedlimit'],
            $last, $speedlimit), 403, 'Forbidden');
        TRB_logRejected('Speedlimit', $_POST['url']);

        return false;
    }

    // update speed limit now in any case
    COM_updateSpeedlimit('trackback');

    if (isset ($_POST['url'])) { // a URL is mandatory ...

        if (substr($_POST['url'], 0, 4) != 'http') {
            TRB_sendTrackbackResponse(1, $TRB_ERROR['no_url'],
                403, 'Forbidden');
            TRB_logRejected('No valid URL', $_POST['url']);

            return false;
        }

        // do spam check on the unfiltered post
        $result = TRB_checkForSpam($_POST['url'], $_POST['title'],
            $_POST['blog_name'], $_POST['excerpt']);

        if ($result == TRB_SAVE_SPAM) {
            TRB_sendTrackbackResponse(1, $TRB_ERROR['spam'], 403, 'Forbidden');
            TRB_logRejected('Spam detected', $_POST['url']);

            return false;
        }

        if (!isset ($_CONF['check_trackback_link'])) {
            $_CONF['check_trackback_link'] = 2;
        }

        if ($_CONF['check_trackback_link'] & 4) {
            $parts = parse_url($_POST['url']);
            if (empty($parts['host'])) {
                TRB_sendTrackbackResponse(1, $TRB_ERROR['no_url'],
                    403, 'Forbidden');
                TRB_logRejected('No valid URL', $_POST['url']);

                return false;
            } else {
                $ip = gethostbyname($parts['host']);
                if ($ip != $_SERVER['REAL_ADDR']) {
                    TRB_sendTrackbackResponse(1, $TRB_ERROR['spam'],
                        403, 'Forbidden');
                    TRB_logRejected('IP address mismatch', $_POST['url']);

                    return false;
                }
            }
        }

        if ($_CONF['check_trackback_link'] & 3) {
            if (!TRB_linksToUs($sid, $type, $_POST['url'])) {
                TRB_sendTrackbackResponse(1, $TRB_ERROR['no_link'],
                    403, 'Forbidden');
                $comment = TRB_formatComment($_POST['url'], $_POST['title'],
                    $_POST['blog_name'], $_POST['excerpt']);
                PLG_spamAction($comment, $_CONF['spamx']);
                TRB_logRejected('No link to us', $_POST['url']);

                return false;
            }
        }

        $saved = TRB_saveTrackbackComment($sid, $type, $_POST['url'],
            $_POST['title'], $_POST['blog_name'], $_POST['excerpt']);

        if ($saved == TRB_SAVE_REJECT) {
            TRB_sendTrackbackResponse(1, $TRB_ERROR['rejected'],
                403, 'Forbidden');
            TRB_logRejected('Multiple Trackbacks', $_POST['url']);

            return false;
        }

        if (isset ($_CONF['notification']) &&
            in_array('trackback', $_CONF['notification'])
        ) {
            TRB_sendNotificationEmail($saved, 'trackback');
        }

        TRB_sendTrackbackResponse(0);

        return true;
    } else {
        TRB_sendTrackbackResponse(1, $TRB_ERROR['no_url']);
        TRB_logRejected('No URL', $_POST['url']);
    }

    return false;
}

/**
 * Render all the trackback comments for a specific entry
 *
 * @param    string $sid       entry id
 * @param    string $type      type of entry ('article' = story, etc.)
 * @param    string $title     the entry's title
 * @param    string $permalink link to the entry
 * @param           string     trackback_url   trackback URL for this entry
 * @return   string                  HTML (formatted list of trackback comments)
 */
function TRB_renderTrackbackComments($sid, $type, $title, $permalink, $trackback_url = '')
{
    global $_CONF, $_TABLES, $LANG_TRB;

    $link_and_title = COM_createLink($title, $permalink,$attr = array('target' => '_blank', 'rel' => 'noopener noreferrer'));
    if (empty($trackback_url)) {
        $trackback_url = TRB_makeTrackbackUrl($sid, $type);
    }

    $template = new Template ($_CONF['path_layout'] . 'trackback');
    $template->set_file (array ('trackback' => 'trackback.thtml',
                                'comment'   => 'trackbackcomment.thtml'));
    $template->set_var('lang_trackback', $LANG_TRB['trackback']);
    $template->set_var('lang_trackback_url', $LANG_TRB['this_trackback_url']);

    $template->set_var('permalink', $permalink);
    $template->set_var('permalink_and_title', $link_and_title);
    $template->set_var('trackback_url', $trackback_url);

    $result = DB_query ("SELECT cid,url,title,blog,excerpt,ipaddress,UNIX_TIMESTAMP(date) AS day "
        . "FROM {$_TABLES['trackback']} WHERE sid = '".DB_escapeString($sid)."' AND type = '".DB_escapeString($type)."' ORDER BY date");
    $numrows = DB_numRows ($result);

    $template->set_var('trackback_comment_count', $numrows);
    $num_comments = sprintf($LANG_TRB['num_comments'], $numrows);
    $template->set_var('trackback_comment_text', $num_comments);
    if ($numrows == 0) {
        $template->set_var('lang_trackback_comments',
            $LANG_TRB['no_comments']);
        $template->set_var('lang_trackback_comments_no_link',
            $LANG_TRB['no_comments']);
    } else {
        $template->set_var('lang_trackback_comments',
            sprintf($LANG_TRB['intro_text'], $link_and_title));
        $template->set_var('lang_trackback_comments_no_link',
            sprintf($LANG_TRB['intro_text'], $title));
    }

    $delete_option = TRB_allowDelete($sid, $type);
    $token = '';
    if ($delete_option && ($numrows > 0)) {
        $token = SEC_createToken();
    }

    for ($i = 0; $i < $numrows; $i++) {
        $A = DB_fetchArray($result);
        $comment = TRB_formatComment($A['url'], $A['title'], $A['blog'],
            $A['excerpt'], $A['day'], $delete_option, $A['cid'],
            $A['ipaddress'], $token);
        $template->set_var('formatted_comment', $comment);
        $template->parse('trackback_comments', 'comment', true);
    }
    $template->parse('output', 'trackback');

    return $template->finish($template->get_var('output'));
}

/**
 * Send a trackback ping
 * Based on a code snippet by Jannis Hermanns,
 * http://www.jannis.to/programming/trackback.html
 *
 * @param    string $targeturl URL to ping
 * @param    string $url       URL of our entry
 * @param    string $title     title of our entry
 * @param    string $excerpt   text excerpt from our entry
 * @param    string $blog      name of our glFusion site
 * @return   mixed               true = success, otherwise: error message
 */
function TRB_sendTrackbackPing($targeturl, $url, $title, $excerpt, $blog = '')
{
    global $_CONF, $LANG_TRB;

    if (empty($blog)) {
        $blog = $_CONF['site_name'];
    }
    $target = parse_url($targeturl);
    if (!isset ($target['query'])) {
        $target['query'] = '';
    } else if (!empty($target['query'])) {
        $target['query'] = '?' . $target['query'];
    }
    if (!isset ($target['port']) || !is_numeric($target['port'])) {
        if ( $target['scheme'] == 'https' ) {
            $target['port'] = 443;
        } else {
            $target['port'] = 80;
        }
    }

    $arguments = array();
    $response = '';

    $http=new http_class;
    $http->timeout=0;
    $http->data_timeout=0;
    $http->debug=0;
    $http->html_debug=0;
    $http->user_agent = 'glFusion/' . GVERSION;
    $error = $http->GetRequestArguments($targeturl,$arguments);
    $error=$http->Open($arguments);
    if ( $error == "" ) {
        $arguments['RequestMethod'] = "POST";
        $arguments['PostValues'] = array(
            'url' => $url,
            'title' => $title,
            'blog_name' => $blog,
            'excerpt' => $excerpt,
        );
    }
    $error=$http->SendRequest($arguments);
    if ( $error == "" ) {
        $http->ReadWholeReplyBody($body);
        if ( strlen($body) > 0 ) {
            $res = $body;
            // firing up the XML parser for this would be overkill ...
            $r1 = strpos($res, '<error>');
            $r2 = strpos($res, '</error>');
            if (($r1 === false) || ($r2 === false)) {
                return $LANG_TRB['error_response'];
            }
            $r1 += strlen('<error>');
            $e = trim(substr($res, $r1, $r2 - $r1));

            if ($e != 0) {
                $r1 = strpos($res, '<message>');
                $r2 = strpos($res, '</message>');
                $r1 += strlen('<message>');
                if (($r1 === false) || ($r2 === false)) {
                    return $LANG_TRB['error_unspecified'];
                }
                $m = trim(substr($res, $r1, $r2 - $r1));

                return $m;
            }
            return true;
        } else {
            return $LANG_TRB['error_unspecified'];
        }
    } else {
        return $LANG_TRB['error_unspecified'];
    }
}

/**
 * Attempt to auto-detect the Trackback URL of a post.
 *
 * @param    string $url URL of post with embedded RDF for the Trackback URL
 * @return   mixed           Trackback URL, or false on error
 *                       Note: The RDF, if found, is only parsed using a regular expression. Using
 *                       the XML parser may be more successful on some occassions ...
 */
function TRB_detectTrackbackUrl($url)
{
    $retval = false;

    $http = new http_class;
    $http->timeout=0;
    $http->data_timeout=0;
    $http->debug=0;
    $http->html_debug=0;
    $http->user_agent = 'glFusion/' . GVERSION;

    $error = $http->GetRequestArguments($url,$arguments);
    $error = $http->Open($arguments);
    $error = $http->SendRequest($arguments);
    if ( $error == "" ) {
        $http->ReadReplyHeaders($headers);
        if ( $http->response_status == 200 ) {
            $error = $http->ReadWholeReplyBody($page);
            if ( $error != "" && strlen($body) === 0 ) {
                COM_errorLog("Trackback Detect TRB URL: unable to retrieve response body");
                return false;
            }
        } else {
            COM_errorLog("Trackback Detect TRB URL: Got HTTP response code ".$http->response_status." when requesting ".$url);
            return false;
        }
    } else {
        COM_errorLog("Trackback Detect TRB URL: " . $error . " when requesting ".$url);
        return false;
    }

    // search for the RDF first
    $startPos = strpos($page, '<rdf:RDF ');
    if ($startPos !== false) {
        $endPos = strpos($page, '</rdf:RDF>', $startPos);
        $endPos += strlen('</rdf:RDF>');
        $rdf = substr($page, $startPos, $endPos - $startPos);

        // Okay, we COULD fire up the XML parser now. But then again ...
        if (preg_match('/trackback:ping="(.*)"/', $rdf, $matches) == 1) {
            if (!empty($matches[1])) {
                $retval = $matches[1];
            }
        }
    }

    // no luck with the RDF? try searching for a rel="trackback" link
    if ($retval === false) {
        // remove all linefeeds first to help the regexp below
        $page = str_replace(array("\015", "\012"), '', $page);

        preg_match_all("/<a[^>]*href=[\"']([^\"']*)[\"'][^>]*>(.*?)<\/a>/i", $page, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $link = $matches[0][$i];
            if (strpos($link, 'rel="trackback"') !== false) {
                $retval = $matches[1][$i];
                break;
            }
        }
    }

    return $retval;
}

/**
 * Send a notification email when a new trackback comment has been posted
 *
 * @param    int    $cid  ID of the trackback comment
 * @param    string $what type of notification: 'trackback' or 'pingback'
 * @return   void
 */
function TRB_sendNotificationEmail($cid, $what = 'trackback')
{
    global $_CONF, $_TABLES, $LANG03, $LANG08, $LANG09, $LANG29, $LANG_TRB;

    $cid = DB_escapeString($cid);
    $result = DB_query("SELECT sid,type,title,excerpt,url,blog,ipaddress FROM {$_TABLES['trackback']} WHERE (cid = '$cid')");
    $A = DB_fetchArray($result);
    $type = $A['type'];
    $id = $A['sid'];

    $mailbody = '';
    if (!empty($A['title'])) {
        $mailbody .= $LANG03[16] . ': ' . $A['title'] . "\n";
    }
    $mailbody .= $LANG_TRB['blog_name'] . ': ';
    if (!empty($A['blog'])) {
        $mailbody .= $A['blog'] . ' ';
    }
    $mailbody .= '(' . $A['ipaddress'] . ")\n";
    $mailbody .= $LANG29[12] . ': ' . $A['url'] . "\n";

    if ($type != 'article') {
        $mailbody .= $LANG09[5] . ': ' . $type . "\n";
    }

    if (!empty($A['excerpt'])) {
        // the excerpt is max. 255 characters long anyway, so we add it
        // in its entirety
        $mailbody .= $A['excerpt'] . "\n\n";
    }

    // assume that plugins follow the convention and have a 'trackback' anchor
    $trackbackurl = PLG_getItemInfo($type, $id, 'url') . '#trackback';

    $mailbody .= $LANG08[33] . ' <' . $trackbackurl . ">\n\n";

    $mailbody .= "\n------------------------------\n";
    $mailbody .= "\n$LANG08[34]\n";
    $mailbody .= "\n------------------------------\n";

    if ($what == 'pingback') {
        $mailsubject = $_CONF['site_name'] . ' ' . $LANG_TRB['pingback'];
    } else {
        $mailsubject = $_CONF['site_name'] . ' ' . $LANG_TRB['trackback'];
    }

    $to = array();
    $to = COM_formatEmailAddress( '',$_CONF['site_mail'] );
    COM_mail ($to, $mailsubject, $mailbody);
}
?>