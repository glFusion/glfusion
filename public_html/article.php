<?php
/**
* glFusion CMS
*
* Article Display
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Jason Whittenburg jwhitten@securitygeeks.com
*  Dirk Haun         dirk@haun-online.de
*  Vincent Furia     vinny01@users.sourceforge.net
*
*/

require_once 'lib-common.php';

use \glFusion\Database\Database;
use \glFusion\Article\ArticleDisplay;

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
    $sid = isset($_POST['story']) ? COM_sanitizeID(COM_applyFilter ($_POST['story'])) : '';
    $mode = isset($_POST['mode']) ? COM_applyFilter ($_POST['mode']) : '';
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

$story = new ArticleDisplay();
if ( $story->retrieveArticleFromDB($sid) != $story::STORY_LOADED_OK ) {
    COM_404();
}

$db = Database::getInstance();

$introtext = $story->getDisplayItem('introtext');

// handle print first
if (($mode == 'print') && ($_CONF['hideprintericon'] == 0)) {
    $story_template = new Template($_CONF['path_layout'] . 'article');
    $story_template->set_file('article', 'printable.thtml');
    list($cacheFile,$style_cache_url) = COM_getStyleCacheLocation();

    $story_template->set_var(array(
        'direction' => $LANG_DIRECTION,
        'css_url' => $style_cache_url,
        'page_title' => $_CONF['site_name'] . ': ' . $story->getDisplayItem('title'),
        'story_title' => $story->getDisplayItem( 'title' ),
        'story_subtitle' => $story->getDisplayItem('subtitle'),
        'breadcrumbs'   => true,
        'topic_name'    => $story->getDisplayItem('topic'),

    ));
    $story_image = $story->getDisplayItem('story_image');
    if ( $story_image != '' ) {
        $story_template->set_var('story_image',$story_image);
    } else {
        $story_template->unset_var('story_image');
    }

     if ( $_CONF['hidestorydate'] != 1 ) {
        $story_template->set_var ('story_date', $story->getDisplayItem('date'));
    }

    if ($_CONF['contributedbyline'] == 1) {
        $story_template->set_var ('lang_contributedby', $LANG01[1]);
        $authorname = COM_getDisplayName ($story->getDisplayItem('uid'));
        $story_template->set_var ('author', $authorname);
        $story_template->set_var ('story_author', $authorname);
        $story_template->set_var ('story_author_username', $story->getDisplayItem('username'));
    }

    $story_template->set_var ('story_introtext',
                                $introtext);
    $story_template->set_var ('story_bodytext',
                                $story->getDisplayItem('bodytext'));

    $story_template->set_var ('site_name', $_CONF['site_name']);
    $story_template->set_var ('site_slogan', $_CONF['site_slogan']);
    $story_template->set_var ('story_id', $story->get('sid'));
    $articleUrl = COM_buildUrl ($_CONF['site_url']
                                . '/article.php?story=' . urlencode($story->get('sid')));
    if ($story->get('commentcode') >= 0) {
        $commentsUrl = $articleUrl . '#comments';
        $comments = $story->get('comments');
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
    $outputHandle = outputHandler::getInstance();

    // Set page title
    $pagetitle      = $story->getDisplayItem('title');
    $story_image    = $story->getDisplayItem('story_image');

    $permalink = COM_buildUrl($_CONF['site_url'] . '/article.php?story='.urlencode($story->get('sid')));
    $outputHandle->addLink('canonical',$permalink);

    if ($story->get('trackbackcode') == 0) {
        if ($_CONF['trackback_enabled']) {
            $trackbackurl = TRB_makeTrackbackUrl($story->get('sid'));

            $outputHandle->addRaw(LB . '<!--' . LB
                 . TRB_trackbackRdf($permalink, $pagetitle, $trackbackurl)
                 . LB . '-->' . LB);
        }
        $pingback = true;
    }
    // build meta data elements
    $metaDesc = $introtext;
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

    $outputHandle->addMeta('property','og:site_name',$_CONF['site_name']);
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
        if (preg_match('/<img[^>]+src=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/si', $introtext, $arrResult)) {
            $outputHandle->addMeta('property','og:image',$arrResult[2]);
            $metaStoryImage = $arrResult[2];
        } else if (preg_match('/<img[^>]+src=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/si', $story->getDisplayItem('bodytext'), $arrResult)) {
            $outputHandle->addMeta('property','og:image',$arrResult[2]);
            $metaStoryImage = $arrResult[2];
        }
    }
    if (!empty($story->get('subtitle'))) {
        $outputHandle->addMeta('property','og:description',$story->getDisplayItem('subtitle'));
    } else {
        $outputHandle->addMeta('property','og:description',$metaDesc);
    }

    $outputHandle->addMeta('name','description',$metaDesc);

    // look for twitter social site config

    $twitterSiteUser = '';
    $sql = "SELECT ss_username FROM `{$_TABLES['social_follow_services']}` as ss LEFT JOIN
            `{$_TABLES['social_follow_user']}` AS su ON ss.ssid = su.ssid
            WHERE su.uid = -1 AND ss.enabled = 1 AND ss.service_name='twitter'";

    $row = $db->conn->fetchAssoc(
            $sql,
            array(),
            array(),
            new \Doctrine\DBAL\Cache\QueryCacheProfile(86400, 'twitter')
    );
    if ($row !== false && $row !== null) {
        $twitterSiteUser = $row['ss_username'];
        if ($story_image != '') {
            $outputHandle->addMeta('property','twitter:card','summary_large_image');
        } else {
            $outputHandle->addMeta('property','twitter:card','summary');
        }
        $outputHandle->addMeta('property','twitter:site','@'.$twitterSiteUser);
        $outputHandle->addMeta('property','twitter:title',$pagetitle);
        if (!empty($story->get('subtitle'))) {
            $outputHandle->addMeta('property','twitter:description',$story->getDisplayItem('subtitle'));
        } else {
            $outputHandle->addMeta('property','twitter:description',$metaDesc);
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
    try {
        $db->conn->executeUpdate(
            "UPDATE `{$_TABLES['stories']}`
                SET hits = hits + 1
                WHERE (sid = ?) AND (date <= ?) AND (draft_flag = 0)",
            array($story->get('sid'),$_CONF['_now']->toMySQL(true)),
            array(Database::STRING,Database::STRING)
        );
    } catch(\Doctrine\DBAL\DBALException $e) {
        // ignore error
    }

    // Display whats related

    $story_template = new Template($_CONF['path_layout'] . 'article');
    $story_template->set_file('article','article.thtml');
    $story_options = array ();

    if (($_CONF['hideemailicon'] == 0) && (!COM_isAnonUser() ||
            (($_CONF['loginrequired'] == 0) &&
             ($_CONF['emailstoryloginrequired'] == 0)))) {
        $emailUrl = $_CONF['site_url'] . '/profiles.php?sid=' . urlencode($story->get('sid'))
                  . '&amp;what=emailstory';
        $story_options[] = COM_createLink($LANG11[2], $emailUrl,array('rel' => 'nofollow'));
    }

    $printUrl = COM_buildUrl ($_CONF['site_url']
            . '/article.php?story=' . urlencode($story->get('sid')) . '&amp;mode=print');
    if ($_CONF['hideprintericon'] == 0) {
        $story_options[] = COM_createLink($LANG11[3], $printUrl, array('rel' => 'nofollow'));
    }

    if ($_CONF['backend'] == 1) {

        // if multiple - return the most recent updated
        $sql = "SELECT filename,title,format
                FROM `{$_TABLES['syndication']}`
                WHERE type = 'article' AND topic = ? AND is_enabled = 1
                ORDER BY title ASC";

        $stmt = $db->conn->executeQuery(
                    $sql,
                    array($story->get('tid')),
                    array(Database::STRING),
                    new \Doctrine\DBAL\Cache\QueryCacheProfile(86400, 'synd_'.$story->get('tid'))
        );
        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $feedUrl = SYND_getFeedUrl($row['filename']);
            $feedTitle = sprintf($LANG11[6], $row['title']);
            $feedType = SYND_getMimeType($row['format']);
            $story_options[] = COM_createLink($feedTitle, $feedUrl,
                                              array('type'  => $feedType,
                                                    'class' => ''));
        }
    }
    if (($_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] ||
            $_CONF['ping_enabled']) && SEC_hasRights('story.ping') &&
        ($story->get('draft_flag') == 0) &&
        ($story->get('unixdate') < time()) &&
        ($story->get('perm_anon') != 0)
    ) {
        // also check permissions for the topic
        $topic_anon = $story->get('topic_perm_anon');
        // check special case: no link when Trackbacks are disabled for this
        // story AND pinging weblog directories is disabled
        if (($topic_anon != 0) &&
            (($story->get('trackbackcode') >= 0) ||
                $_CONF['ping_enabled'])
        ) {
            $url = $_CONF['site_admin_url']
                . '/trackback.php?mode=sendall&amp;id=' . urlencode($story->get('sid'));
            $story_options[] = COM_createLink($LANG_TRB['send_trackback'],$url);
        }
    }

    $social_icons = SOC_getShareIcons($pagetitle,htmlspecialchars($metaDesc,ENT_QUOTES,COM_getEncodingt()),$permalink,'','article');

    $story_template->set_var('social_share',$social_icons);

    // build what's related section

    $relItems = PLG_getWhatsRelated('article',$story->get('sid'));
    if ( count( $relItems)  > 0 ) {
        $rel = array();
        foreach ($relItems AS $item ) {
           $rel[] = '<a href="' . $item['url'] . '">'.$item['title'].'</a>';
        }
        $related = COM_makeList( $rel, 'list-whats-related' );
    } else {
        $related = $story->getWhatsRelated($story->getDisplayItem('related'),
                                      $story->get('uid'),
                                      $story->get('tid'),
                                      $story->get('alternate_tid'));

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

    $story_template->set_var ('formatted_article',$story->getDisplayArticle('n'));

    // display comments or not?
    $show_comments = true;

    // Display the comments, if there are any ..
    if ($story->get('commentcode') >= 0) {
        $delete_option = (SEC_hasRights('story.edit') && ($story->getAccess() == 3) ? true : false);
        USES_lib_comment();
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
                CMT_userComments ($story->get('sid'), $story->getDisplayItem('title'), 'article',
                                  $order, $mode, 0, $page, false, $delete_option, $story->get('commentcode'),$story->get('uid')));
    }
    if ($_CONF['trackback_enabled'] && ($story->get('trackbackcode') >= 0)) {
        if (SEC_hasRights ('story.ping')) {
            if (($story->get('draft_flag') == 0) &&
                ($story->getDisplayItem('unixdate') < time ())) {
                $url = $_CONF['site_admin_url']
                     . '/trackback.php?mode=sendall&amp;id=' . urlencode($story->get('sid'));
                $story_template->set_var ('send_trackback_link',
                    COM_createLink($LANG_TRB['send_trackback'], $url));
                $story_template->set_var ('send_trackback_url', $url);
                $story_template->set_var ('lang_send_trackback_text',
                                          $LANG_TRB['send_trackback']);
            }
        }

        $permalink = COM_buildUrl ($_CONF['site_url']
                                   . '/article.php?story=' . urlencode($story->get('sid')));
        $story_template->set_var ('trackback',
                TRB_renderTrackbackComments ($story->get('sid'), 'article',
                                             $story->getDisplayItem('title'), $permalink));
    } else {
        $story_template->set_var ('trackback', '');
    }

   if (function_exists('CUSTOM_preContent')) {
        $tvars = $story_template->get_vars();
        CUSTOM_preContent('load','article', $tvars);
    }

    $pageBody .= $story_template->finish ($story_template->parse ('output', 'article'));
}

if ($pingback == true && $_CONF['pingback_enabled']) {
    header ('X-Pingback: ' . $_CONF['site_url'] . '/pingback.php');
}
echo COM_siteHeader('menu', $pagetitle);
echo $pageBody;
echo COM_siteFooter();
?>
