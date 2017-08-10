<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | article.php                                                              |
// |                                                                          |
// | Shows articles in various formats.                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Vincent Furia     - vinny01 AT users DOT sourceforge DOT net    |
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

/**
* This page is responsible for showing a single article in different modes which
* may, or may not, include the comments attached
*
* @author   Jason Whittenburg
* @author   Tony Bibbbs <tony@tonybibbs.com>
* @author   Vincent Furia <vinny01 AT users DOT sourceforge DOT net>
*/

/**
* glFusion common function library
*/
require_once 'lib-common.php';
USES_lib_story();
USES_lib_social();

if ($_CONF['trackback_enabled']) {
    USES_lib_trackback();
}

// MAIN
$display = '';
$pageBody = '';
$pagetitle = '';
$pingback = false;

$order = '';
$query = '';
$reply = '';
if (isset ($_POST['mode'])) {
    $sid = COM_sanitizeID(COM_applyFilter ($_POST['story']));
    $mode = COM_applyFilter ($_POST['mode']);
    if (isset ($_POST['order'])) {
        $order = COM_applyFilter ($_POST['order']);
    }
    if (isset ($_POST['query'])) {
        $query = trim(COM_applyFilter ($_POST['query']));
    }
    if (isset ($_POST['reply'])) {
        $reply = COM_applyFilter ($_POST['reply']);
    }
} else {
    COM_setArgNames (array ('story', 'mode'));
    $sid = COM_sanitizeID(COM_applyFilter (COM_getArgument ('story')));
    $mode = COM_applyFilter (COM_getArgument ('mode'));
    if (isset ($_GET['order'])) {
        $order = COM_applyFilter ($_GET['order']);
    }
    if (isset ($_GET['query'])) {
        $query = trim(COM_applyFilter ($_GET['query']));
    }
    if (isset ($_GET['reply'])) {
        $reply = COM_applyFilter ($_GET['reply']);
    }
}

if (empty ($sid)) {
    COM_404();
}
if ((strcasecmp ($order, 'ASC') != 0) && (strcasecmp ($order, 'DESC') != 0)) {
    $order = '';
}

$result = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE sid = '".DB_escapeString($sid)."'" . COM_getPermSql ('AND'));
$A = DB_fetchArray($result);
if ($A['count'] > 0) {

    $ratedIds = array();
    if ( $_CONF['rating_enabled'] != 0 ) {
        $ratedIds = RATING_getRatedIds('article');
    }

    $story = new Story();

    $args = array (
                    'sid' => $sid,
                    'mode' => 'view'
                  );

    $output = STORY_LOADED_OK;
    $result = PLG_invokeService('story', 'get', $args, $output, $svc_msg);

    if($result == PLG_RET_OK) {
        /* loadFromArray cannot be used, since it overwrites the timestamp */

        foreach ( $story->_dbFields AS $fieldname => $save ) {
            $varname = '_' . $fieldname;

            if (array_key_exists($fieldname, $output)) {
                $story->{$varname} = $output[$fieldname];
            }
        }
       $story->_username = $output['username'];
       $story->_fullname = $output['fullname'];
    }
    if ($output == STORY_PERMISSION_DENIED) {
        $display = COM_siteHeader ('menu', $LANG_ACCESS['accessdenied'])
                 . COM_showMessageText($LANG_ACCESS['storydenialmsg'], $LANG_ACCESS['accessdenied'], true,'error')
                 . COM_siteFooter ();
        echo $display;
        exit;
    } elseif ( $output == STORY_INVALID_SID ) {
        COM_404();
    } elseif (($mode == 'print') && ($_CONF['hideprintericon'] == 0)) {
        $story_template = new Template($_CONF['path_layout'] . 'article');
        $story_template->set_file('article', 'printable.thtml');
        list($cacheFile,$style_cache_url) = COM_getStyleCacheLocation();
        $story_template->set_var('direction', $LANG_DIRECTION);
        $story_template->set_var('css_url',$style_cache_url);
        $story_template->set_var('page_title',
                $_CONF['site_name'] . ': ' . $story->displayElements('title'));
        $story_template->set_var ( 'story_title', $story->DisplayElements( 'title' ) );
        $story_template->set_var ( 'story_subtitle',$story->DisplayElements('subtitle'));
        $story_image = $story->DisplayElements('story_image');
        if ( $story_image != '' ) {
            $story_template->set_var('story_image',$story_image);
        } else {
            $story_template->unset_var('story_image');
        }

         if ( $_CONF['hidestorydate'] != 1 ) {
            $story_template->set_var ('story_date', $story->displayElements('date'));
        }

        if ($_CONF['contributedbyline'] == 1) {
            $story_template->set_var ('lang_contributedby', $LANG01[1]);
            $authorname = COM_getDisplayName ($story->displayElements('uid'));
            $story_template->set_var ('author', $authorname);
            $story_template->set_var ('story_author', $authorname);
            $story_template->set_var ('story_author_username', $story->DisplayElements('username'));
        }

        $story_template->set_var ('story_introtext',
                                    $story->DisplayElements('introtext'));
        $story_template->set_var ('story_bodytext',
                                    $story->DisplayElements('bodytext'));

        $story_template->set_var ('site_name', $_CONF['site_name']);
        $story_template->set_var ('site_slogan', $_CONF['site_slogan']);
        $story_template->set_var ('story_id', $story->getSid());
        $articleUrl = COM_buildUrl ($_CONF['site_url']
                                    . '/article.php?story=' . $story->getSid());
        if ($story->DisplayElements('commentcode') >= 0) {
            $commentsUrl = $articleUrl . '#comments';
            $comments = $story->DisplayElements('comments');
            $numComments = COM_numberFormat ($comments);
            $story_template->set_var ('story_comments', $numComments);
            $story_template->set_var ('comments_url', $commentsUrl);
            $story_template->set_var ('comments_text',
                    $numComments . ' ' . $LANG01[3]);
            $story_template->set_var ('comments_count', $numComments);
            $story_template->set_var ('lang_comments', $LANG01[3]);
            $comments_with_count = sprintf ($LANG01[121], $numComments);

            if ($comments > 0) {
                $comments_with_count = COM_createLink($comments_with_count, $commentsUrl);
            }
            $story_template->set_var ('comments_with_count', $comments_with_count);
        }
        $story_template->set_var ('lang_full_article', $LANG08[33]);
        $story_template->set_var ('article_url', $articleUrl);

        COM_setLangIdAndAttribute($story_template);

        $story_template->parse('output', 'article');
        header ('Content-Type: text/html; charset=' . COM_getCharset ());
        echo $story_template->finish($story_template->get_var('output'));
        exit;
    } else {
        // Set page title
        $pagetitle = $story->DisplayElements('title');
        $story_image = $story->DisplayElements('story_image');

        $outputHandle = outputHandler::getInstance();

        $permalink = COM_buildUrl($_CONF['site_url'] . '/article.php?story='
                                  . $story->getSid());
        $outputHandle->addLink('canonical',$permalink);

        if ($story->DisplayElements('trackbackcode') == 0) {
            if ($_CONF['trackback_enabled']) {
                $trackbackurl = TRB_makeTrackbackUrl($story->getSid());

                $outputHandle->addRaw(LB . '<!--' . LB
                     . TRB_trackbackRdf($permalink, $pagetitle, $trackbackurl)
                     . LB . '-->' . LB);
            }
            $pingback = true;
        }
        $metaDesc = $story->DisplayElements('introtext');
        $metaDesc = strip_tags($metaDesc);
        $html2txt = new Html2Text\Html2Text($metaDesc,false);
        $metaDesc = trim($html2txt->get_text());
        $shortComment = '';
        $metaArray = explode(' ',$metaDesc);
        $wordCount = count($metaArray);
        $lengthCount = 0;
        $tailString = '';
        foreach ($metaArray AS $word) {
            $lengthCount = $lengthCount + strlen($word);
            $shortComment .= $word.' ';
            if ( $lengthCount >= 100 ) {
                $tailString = '...';
                break;
            }
        }
        $metaDesc = trim($shortComment).$tailString;

        $outputHandle->addMeta('property','og:site_name',urlencode($_CONF['site_name']));
        $outputHandle->addMeta('property','og:locale',isset($LANG_LOCALE) ? $LANG_LOCALE : 'en_US');
        $outputHandle->addMeta('property','og:title',$pagetitle);
        $outputHandle->addMeta('property','og:type','article');
        $outputHandle->addMeta('property','og:url',$permalink);
        $metaStoryImage = '';
        if ( $story_image != '' ) {
            $outputHandle->addMeta('property','og:image',$_CONF['site_url'].$story_image);
            $metaStoryImage = $_CONF['site_url'].$story_image;
            if ( $story_image[0] == '/') {
                $siPath = substr($story_image,1);
            } else {
                $siPath = $story_image;
            }
            $siSize = @getimagesize ($_CONF['path_html'].$siPath  );
            if ( $siSize !== FALSE ) {
                $outputHandle->addMeta('property','og:image:width',$siSize[0]);
                $outputHandle->addMeta('property','og:image:height',$siSize[1]);
                $outputHandle->addMeta('property','og:image:type',$siSize['mime']);
            }
        } else {
            if (preg_match('/<img[^>]+src=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/si', $story->DisplayElements('introtext'), $arrResult)) {
                $outputHandle->addMeta('property','og:image',$arrResult[2]);
                $metaStoryImage = $arrResult[2];
            } else if (preg_match('/<img[^>]+src=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/si', $story->DisplayElements('bodytext'), $arrResult)) {
                $outputHandle->addMeta('property','og:image',$arrResult[2]);
                $metaStoryImage = $arrResult[2];
            }
        }
        if ( $story->DisplayElements('subtitle') != "" ) {
            $outputHandle->addMeta('property','og:description',@htmlspecialchars($story->DisplayElements('subtitle'),ENT_QUOTES,COM_getEncodingt()));
        } else {
            $outputHandle->addMeta('property','og:description',@htmlspecialchars($metaDesc,ENT_QUOTES,COM_getEncodingt()));
        }

        $outputHandle->addMeta('name','description',@htmlspecialchars($metaDesc,ENT_QUOTES,COM_getEncodingt()));

        // look for twitter social site config

        $twitterSiteUser = '';
        $sql = "SELECT * FROM {$_TABLES['social_follow_services']} as ss LEFT JOIN
                {$_TABLES['social_follow_user']} AS su ON ss.ssid = su.ssid
                WHERE su.uid = -1 AND ss.enabled = 1 AND ss.service_name='twitter'";

        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        if ( $numRows > 0 ) {
            $row = DB_fetchArray($result);
            $twitterSiteUser = $row['ss_username'];
            if ( $story_image != '' ) {
                $outputHandle->addMeta('property','twitter:card','summary_large_image');
            } else {
                $outputHandle->addMeta('property','twitter:card','summary');
            }
            $outputHandle->addMeta('property','twitter:site','@'.$twitterSiteUser);
            $outputHandle->addMeta('property','twitter:title',$pagetitle);
            if ( $story->DisplayElements('subtitle') != "" ) {
                $outputHandle->addMeta('property','twitter:description',@htmlspecialchars($story->DisplayElements('subtitle'),ENT_QUOTES,COM_getEncodingt()));
            } else {
                $outputHandle->addMeta('property','twitter:description',@htmlspecialchars($metaDesc,ENT_QUOTES,COM_getEncodingt()));
            }
            if ( $metaStoryImage != '' ) {
                $outputHandle->addMeta('property','twitter:image',$metaStoryImage . '?' . rand());
            }
        }

        if (isset($_GET['msg'])) {
            $msg = (int) COM_applyFilter($_GET['msg'], true);
            if ($msg > 0) {
                $plugin = '';
                if (isset($_GET['plugin'])) {
                    $plugin = COM_applyFilter($_GET['plugin']);
                }
                $pageBody .= COM_showMessage($msg, $plugin,'',0,'info');
            }
        }
        DB_query ("UPDATE {$_TABLES['stories']} SET hits = hits + 1 WHERE (sid = '".DB_escapeString($story->getSid())."') AND (date <= NOW()) AND (draft_flag = 0)");

        // Display whats related

        $story_template = new Template($_CONF['path_layout'] . 'article');
        $story_template->set_file('article','article.thtml');

        $story_template->set_var('site_admin_url', $_CONF['site_admin_url']);
        $story_template->set_var('story_id', $story->getSid());
        $story_template->set_var('story_title', $pagetitle);
        $story_template->set_var ( 'story_subtitle',$story->DisplayElements('subtitle'));

        if ( $_CONF['hidestorydate'] != 1 ) {
            $story_template->set_var ('story_date', $story->displayElements('date'));
        }

        if ($_CONF['contributedbyline'] == 1) {
            $story_template->set_var ('lang_contributedby', $LANG01[1]);
            $authorname = COM_getDisplayName ($story->displayElements('uid'));
            $story_template->set_var ('author', $authorname);
            $story_template->set_var ('story_author', $authorname);
            $story_template->set_var ('story_author_username', $story->DisplayElements('username'));
        }

        if ( $story_image != '' ) {
            $story_template->set_var('story_image',$story_image);
        } else {
            $story_template->unset_var('story_image');
        }

        $story_options = array ();

        if (($_CONF['hideemailicon'] == 0) && (!COM_isAnonUser() ||
                (($_CONF['loginrequired'] == 0) &&
                 ($_CONF['emailstoryloginrequired'] == 0)))) {
            $emailUrl = $_CONF['site_url'] . '/profiles.php?sid=' . $story->getSid()
                      . '&amp;what=emailstory';
            $story_options[] = COM_createLink($LANG11[2], $emailUrl,array('rel' => 'nofollow'));
            $story_template->set_var ('email_story_url', $emailUrl);
            $story_template->set_var ('lang_email_story', $LANG11[2]);
            $story_template->set_var ('lang_email_story_alt', $LANG01[64]);
        }

        $printUrl = COM_buildUrl ($_CONF['site_url']
                . '/article.php?story=' . $story->getSid() . '&amp;mode=print');
        if ($_CONF['hideprintericon'] == 0) {
            $story_options[] = COM_createLink($LANG11[3], $printUrl, array('rel' => 'nofollow'));
            $story_template->set_var ('print_story_url', $printUrl);
            $story_template->set_var ('lang_print_story', $LANG11[3]);
            $story_template->set_var ('lang_print_story_alt', $LANG01[65]);
        }

        if ($_CONF['backend'] == 1) {
            $tid = $story->displayElements('tid');
            $result = DB_query("SELECT filename, title, format FROM {$_TABLES['syndication']} WHERE type = 'article' AND topic = '".DB_escapeString($tid)."' AND is_enabled = 1");
            $feeds = DB_numRows($result);
            for ($i = 0; $i < $feeds; $i++) {
                list($filename, $title, $format) = DB_fetchArray($result);
                $feedUrl = SYND_getFeedUrl($filename);
                $feedTitle = sprintf($LANG11[6], $title);
                $feedType = SYND_getMimeType($format);
                $story_options[] = COM_createLink($feedTitle, $feedUrl,
                                                  array('type'  => $feedType,
                                                        'class' => ''));
            }
        }

        $social_icons = SOC_getShareIcons($pagetitle,htmlspecialchars($metaDesc,ENT_QUOTES,COM_getEncodingt()),$permalink,'','article');

        $story_template->set_var('social_share',$social_icons);

        // build what's related section

        $relItems = PLG_getWhatsRelated('article',$story->getSid());
        if ( count( $relItems)  > 0 ) {
            $rel = array();
            foreach ($relItems AS $item ) {
               $rel[] = '<a href="' . $item['url'] . '">'.$item['title'].'</a>';
            }
            $related = COM_makeList( $rel, 'list-whats-related' );
        } else {
            $related = STORY_whatsRelated($story->displayElements('related'),
                                          $story->displayElements('uid'),
                                          $story->displayElements('tid'),
                                          $story->displayElements('alternate_tid'));
        }

        if (!empty ($related)) {
            $related = COM_startBlock ($LANG11[1], '',
                COM_getBlockTemplate ('whats_related_block', 'header'), 'whats-related')
                . $related
                . COM_endBlock (COM_getBlockTemplate ('whats_related_block',
                    'footer'));
        }
        if (count ($story_options) > 0) {
            $optionsblock = COM_startBlock ($LANG11[4], '',
                    COM_getBlockTemplate ('story_options_block', 'header'), 'story-options')
                . COM_makeList ($story_options, 'list-story-options')
                . COM_endBlock (COM_getBlockTemplate ('story_options_block','footer'));
        } else {
            $optionsblock = '';
        }
        $story_template->set_var ('whats_related', $related);
        $story_template->set_var ('story_options', $optionsblock);
        $story_template->set_var ('whats_related_story_options',
                                  $related . $optionsblock);

        $story_template->set_var ('formatted_article',
                                  STORY_renderArticle ($story, 'n', '', $query));

        // display comments or not?
        if ( (is_numeric($mode)) and ($_CONF['allow_page_breaks'] == 1) )
        {
            $story_page = (int) $mode;
            $mode = '';
            if( $story_page <= 0 ) {
                $story_page = 1;
            }
            $article_arr = explode( '[page_break]', $story->displayElements('bodytext'));
            $conf = $_CONF['page_break_comments'];
            if  (
                 ($conf == 'all') or
                 ( ($conf =='first') and ($story_page == 1) ) or
                 ( ($conf == 'last') and (count($article_arr) == ($story_page)) )
                ) {
                $show_comments = true;
            } else {
                $show_comments = false;
            }
        } else {
            $show_comments = true;
        }

        // Display the comments, if there are any ..
        if (($story->displayElements('commentcode') >= 0) and $show_comments) {
            $delete_option = (SEC_hasRights('story.edit') && ($story->getAccess() == 3)
                             ? true : false);
            require_once ( $_CONF['path_system'] . 'lib-comment.php' );
            if ( isset($_GET['mode']) ) {
                $mode = COM_applyFilter($_GET['mode']);
            } elseif ( isset($_POST['mode']) ) {
                $mode = COM_applyFilter($_POST['mode']);
            } else {
                $mode = '';
            }
            if ( isset($_GET['page']) ) {
                $page = (int) COM_applyFilter($_GET['page'],true);
            } else {
                $page = 1;
            }
            $story_template->set_var ('commentbar',
                    CMT_userComments ($story->getSid(), $story->displayElements('title'), 'article',
                                      $order, $mode, 0, $page, false, $delete_option, $story->displayElements('commentcode'),$story->displayElements('uid')));
        }
        if ($_CONF['trackback_enabled'] && ($story->displayElements('trackbackcode') >= 0) &&
                $show_comments) {
            if (SEC_hasRights ('story.ping')) {
                if (($story->displayElements('draft_flag') == 0) &&
                    ($story->displayElements('day') < time ())) {
                    $url = $_CONF['site_admin_url']
                         . '/trackback.php?mode=sendall&amp;id=' . $story->getSid();
                    $story_template->set_var ('send_trackback_link',
                        COM_createLink($LANG_TRB['send_trackback'], $url));
                    $story_template->set_var ('send_trackback_url', $url);
                    $story_template->set_var ('lang_send_trackback_text',
                                              $LANG_TRB['send_trackback']);
                }
            }

            $permalink = COM_buildUrl ($_CONF['site_url']
                                       . '/article.php?story=' . $story->getSid());
            $story_template->set_var ('trackback',
                    TRB_renderTrackbackComments ($story->getSID(), 'article',
                                                 $story->displayElements('title'), $permalink));
        } else {
            $story_template->set_var ('trackback', '');
        }

       if (function_exists('CUSTOM_preContent')) {
            $tvars = $story_template->get_vars();
            CUSTOM_preContent('load','article', $tvars);
        }

        $pageBody .= $story_template->finish ($story_template->parse ('output', 'article'));
    }
} else {
    COM_404();
}

if ($pingback == true && $_CONF['pingback_enabled']) {
    header ('X-Pingback: ' . $_CONF['site_url'] . '/pingback.php');
}
echo COM_siteHeader('menu', $pagetitle);
echo $pageBody;
echo COM_siteFooter();
?>