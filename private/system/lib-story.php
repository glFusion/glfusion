<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-story.php                                                            |
// |                                                                          |
// | Story-related functions needed in more than one place.                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark Howard            mark AT usable-web DOT com                        |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

USES_class_story();
USES_lib_comment();

if ($_CONF['allow_user_photo']) {
    // only needed for the USER_getPhoto function
    USES_lib_user();
}

// this must be kept in sync with the actual size of 'sid' in the db ...
define('STORY_MAX_ID_LENGTH', 128);
// Story Record Options for the STATUS Field
if (!defined ('STORY_ARCHIVE_ON_EXPIRE')) {
    define ('STORY_ARCHIVE_ON_EXPIRE', '10');
    define ('STORY_DELETE_ON_EXPIRE',  '11');
}
/**
 * Takes an article class and renders HTML in the specified template and style.
 *
 * Formats the given article into HTML. Called by index.php, article.php,
 * submit.php and admin/story.php (Preview mode for the last two).
 *
 * @param   object  $story      The story to display, an instance of the Story class.
 * @param   string  $index      n = 'Compact display' for list of stories. p = 'Preview' mode. Else full display of article.
 * @param   string  $storytpl   The template to use to render the story.
 * @param   string  $query      A search query, if one was specified.
 *
 * @return  string  Article as formated HTML.
 *
 * Note: Formerly named COM_Article, and re-written totally since then.
 */
function STORY_renderArticle( &$story, $index='', $storytpl='storytext.thtml', $query='')
{
    global $_CONF, $_SYSTEM, $_TABLES, $_USER, $LANG01, $LANG05, $LANG11, $LANG_TRB,
           $_IMAGE_TYPE, $mode, $_GROUPS, $ratedIds, $topic;

    static $storycounter = 0;

    USES_lib_social();

    SESS_setContext(array('type' => 'article','sid' => $story->_sid));

    if ( empty( $storytpl )) {
        $storytpl = 'storytext.thtml';
    }
    $featuredstorytpl = 'featuredstorytext.thtml';
    $archivestorytpl  = 'archivestorytext.thtml';

    if ( isset($_SYSTEM['custom_topic_templates']) && $_SYSTEM['custom_topic_templates'] == true ) {
        if ( $topic != "" ) {
            $storyTid = COM_sanitizeFilename(strtolower($topic));
        } else {
            $storyTid = strtolower($story->DisplayElements('tid'));
        }
        $pos = strpos($storytpl,".");
        if ( $pos !== false ) {
            $base_template = substr($storytpl,0,$pos);
        } else {
            $base_template = 'storytext';
        }
        if ( file_exists($_CONF['path_layout'].'/custom/'.$base_template.'_'.$storyTid.'.thtml') !== false) {
            $storytpl = $base_template.'_'.$storyTid.'.thtml';
        }
        if ( file_exists($_CONF['path_layout'].'/custom/featuredstorytext'.'_'.$storyTid.'.thtml') !== false) {
            $featuredstorytpl = 'featuredstorytext'.'_'.$storyTid.'.thtml';
        }
        if ( file_exists($_CONF['path_layout'].'/custom/archivestorytext'.'_'.$storyTid.'.thtml') !== false) {
            $archivestorytpl = 'archivestorytext'.'_'.$storyTid.'.thtml';
        }
    }

    $introtext = $story->displayElements('introtext');
    $bodytext  = $story->displayElements('bodytext');

    if ( !empty( $query )) {
        $introtext = COM_highlightQuery( $introtext, $query );
        $bodytext  = COM_highlightQuery( $bodytext, $query );
    }

    $article = new Template( $_CONF['path_layout'] );
    $article->set_file( array(
            'article'          => $storytpl,
            'featuredarticle'  => $featuredstorytpl,
            'archivearticle'   => $archivestorytpl,
            ));

    if ( $story->DisplayElements('featured') == 1 ) {
        $article_filevar = 'featuredarticle';
    } elseif ( $story->DisplayElements('statuscode') == STORY_ARCHIVE_ON_EXPIRE AND $story->DisplayElements('expire') <= time() ) {
        $article_filevar = 'archivearticle';
    } else {
        $article_filevar = 'article';
    }

    if ( $_CONF['hideviewscount'] != 1 ) {
        $article->set_var( 'lang_views', $LANG01[106] );
        $article->set_var( 'story_hits', $story->DisplayElements('hits'),false,true );
    }

    if ( $_CONF['hidestorydate'] != 1 ) {
        $article->set_var( 'story_date', $story->DisplayElements('date'), false, true); // make sure date format is in user's preferred format
        $article->set_var( 'story_date_short', $story->DisplayElements('shortdate'), false, true );
        $article->set_var( 'story_date_only', $story->DisplayElements('dateonly'), false, true );
    }

    if ( $index == 'p' || $index == 'n') {
       $article->set_var( 'story_counter', 0 );
    } else {
        $storycounter++;
        $article->set_var( 'story_counter', $storycounter, false, true );
        $article->set_var( 'adblock',PLG_displayAdBlock('story',$storycounter), false, true);
    }
    $topicname = $story->DisplayElements('topic');

    switch ($index) {
        case 'p' :
            $story_display = 'preview';
            break;
        case 'n' :
            $story_display = 'article';
            break;
        case 'y' :
            $story_display = 'index';
            break;
        default :
            $story_display = 'index';
            break;
    }

    $article->set_var( 'story_display', $story_display, false, true );

    if (( $index == 'n' ) || ( $index == 'p' )) {  // full article view or preview
        if ( empty( $bodytext )) {
            $article->set_var( 'story_introtext', $introtext,false,true );
            $article->set_var( 'story_text_no_br', $introtext,false,true );
        } else {
            $article->set_var( 'story_introtext', $introtext . '<br />'
                               . $bodytext,false,true );
            $article->set_var( 'story_text_no_br', $introtext . $bodytext,false,true );
        }
        $article->set_var( 'story_introtext_only', $introtext,false,true );
        $article->set_var( 'story_bodytext_only', $bodytext,false,true );
    } else {
        $article->set_var( 'story_introtext', $introtext,false,true );
        $article->set_var( 'story_text_no_br', $introtext,false,true );
        $article->set_var( 'story_introtext_only', $introtext,false,true );
    }

    if ( $_CONF['rating_enabled'] != 0 && $index != 'p') {
        if ( @in_array($story->getSid(),$ratedIds)) {
            $static = true;
            $voted = 1;
        } else {
            $static = 0;
            $voted = 0;
        }
        $uid = isset($_USER['uid']) ? $_USER['uid'] : 1;
        if ( $_CONF['rating_enabled'] == 2 && $uid != $story->DisplayElements('owner_id')) {
            $article->set_var('rating_bar',RATING_ratingBar( 'article',$story->getSid(), $story->DisplayElements('votes'),$story->DisplayElements('rating'), $voted,5,$static,'sm'),false,true );
        } else if ( !COM_isAnonUser() && $uid != $story->DisplayElements('owner_id')) {
            $article->set_var('rating_bar',RATING_ratingBar( 'article',$story->getSid(), $story->DisplayElements('votes'),$story->DisplayElements('rating'), $voted,5,$static,'sm'),false,true );
        } else {
            $article->set_var('rating_bar',RATING_ratingBar( 'article',$story->getSid(), $story->DisplayElements('votes'),$story->DisplayElements('rating'), $voted,5,TRUE,'sm'),false,true );
        }
    } else {
        $article->set_var('rating_bar','',false,true );
    }

    $topicurl = $_CONF['site_url'] . '/index.php?topic=' . $story->DisplayElements('tid');
    $alttopicurl = $_CONF['site_url'] . '/index.php?topic=' . $story->DisplayElements('alternate_tid');

    if (( !isset( $_USER['noicons'] ) || ( $_USER['noicons'] != 1 )) && $story->DisplayElements('show_topic_icon') == 1 ) {
        $imageurl = $story->DisplayElements('imageurl');
        if ( !empty( $imageurl )) {
            $imageurl = COM_getTopicImageUrl( $imageurl );
            $article->set_var( 'story_topic_image_url', $imageurl, false,true );
            $article->set_var( 'topic_url',$topicurl);
            $topicimage = '<img src="' . $imageurl . '" class="float'
                        . $_CONF['article_image_align'] . '" alt="'
                        . $topicname . '" title="' . $topicname . '" />';
            $article->set_var( 'story_anchortag_and_image',
                COM_createLink(
                    $topicimage,
                    $topicurl,
                    array('rel'=>"category tag")
                ), false,true
            );
            $article->set_var( 'story_topic_image', $topicimage, false,true );
            $topicimage_noalign = '<img src="' . $imageurl . '" alt="'
                        . $topicname . '" title="' . $topicname . '" />';
            $article->set_var( 'story_anchortag_and_image_no_align',
                COM_createLink(
                    $topicimage_noalign,
                    $topicurl,
                    array('rel'=>"category tag")
                ), false,true
            );
            $article->set_var( 'story_topic_image_no_align',
                               $topicimage_noalign, false,true );
        }
    }

    $hash = CACHE_security_hash();
    $instance_id = 'story_'.$story->getSid().'_'.$index.'_'.$article_filevar.'_'.$hash.'_'.$_USER['theme'];

    if ( $index == 'p' || !$article->check_instance($instance_id,$article_filevar)) {
        $article->set_var('article_filevar','');
        $article->set_var( 'site_name', $_CONF['site_name'] );

        $article->set_var( 'story_id', $story->getSid() );
        $article->set_var( 'lang_posted_in', $LANG01['posted_in']);

        $articleUrl = COM_buildUrl($_CONF['site_url'] . '/article.php?story='.$story->getSid());
        $article->set_var('article_url', $articleUrl );
        $article->set_var('story_title', $story->DisplayElements('title'));

        if ($_CONF['contributedbyline'] == 1) {
            $article->set_var('lang_contributed_by', $LANG01[1]);
            $article->set_var('lang_by',$LANG01[1]);

            $article->set_var('contributedby_uid', $story->DisplayElements('uid'));
            $fullname = $story->DisplayElements('fullname');
            $username = $story->DisplayElements('username');
            $article->set_var('contributedby_user', $username);
            if (empty($fullname)) {
                $article->set_var('contributedby_fullname', $username);
            } else {
                $article->set_var('contributedby_fullname',$fullname);
            }
            $authorname = COM_getDisplayName( $story->DisplayElements('uid'),$username, $fullname);

            $article->set_var( 'author', $authorname );
            $profileUrl = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid='
                . $story->DisplayElements('uid');

            if ( $story->DisplayElements('uid') > 1 ) {
                $article->set_var( 'contributedby_url', $profileUrl );
                $authorname = COM_createLink($authorname, $profileUrl, array('class' => 'storybyline'));
            }
            $article->set_var( 'contributedby_author', $authorname );

            $photo = '';
            $photo_raw = '';
            if ($_CONF['allow_user_photo'] == 1) {
                $authphoto = $story->DisplayElements('photo');
                if (empty($authphoto)) {
                    $authphoto = '(none)'; // user does not have a photo
                }
                $photo = USER_getPhoto($story->DisplayElements('uid'), $authphoto,
                                       $story->DisplayElements('email'));
                $photo_raw = USER_getPhoto($story->DisplayElements('uid'), $authphoto,
                                       $story->DisplayElements('email'),64,0);
            }
            $article->set_var('author_photo_raw',$photo_raw);
            if (!empty($photo)) {
                $article->set_var('contributedby_photo', $photo);
                $article->set_var('author_photo', $photo);
                $camera_icon = '<img src="' . $_CONF['layout_url']
                             . '/images/smallcamera.' . $_IMAGE_TYPE . '" alt=""'
                             . '/>';
                $article->set_var('camera_icon',
                                  COM_createLink($camera_icon, $profileUrl));
            } else {
                $article->set_var ('contributedby_photo', '');
                $article->set_var ('author_photo', '');
                $article->set_var ('camera_icon', '');
            }
            if ( $story->DisplayElements('attribution_author') == "" ) {
                $article->set_var('author_about', PLG_replaceTags( nl2br($story->DisplayElements('about')),'glfusion','about_user'));
                $article->set_var('follow_me',SOC_getFollowMeIcons( $story->DisplayElements('uid') ));
            }
        }
        $topic_description = $story->DisplayElements('topic_description');
        $alttopic = '';
        $alttopic_description = '';
        if ( $story->DisplayElements('alternate_tid')  != NULL ) {
            $alttopic = $story->DisplayElements('alternate_topic');
            $alttopic_description = $story->DisplayElements('alternate_topic_description');
        }
        $article->set_var('story_topic_id', $story->DisplayElements('tid'));
        $article->set_var('alt_story_topic_id', $story->DisplayElements('alternate_tid'));
        $article->set_var('story_topic_name', $topicname);
        $article->set_var('story_alternate_topic_name',$alttopic);
        $article->set_var('story_topic_description',$topic_description);
        $article->set_var('story_alternate_topic_description',$alttopic_description);
        $article->set_var('story_topic_description_text',$story->DisplayElements('topic_description_text'));

        $article->set_var('story_subtitle',$story->DisplayElements('subtitle'));

        $attribution_url = $story->DisplayElements('attribution_url');
        $attribution_name = $story->DisplayElements('attribution_name');
        $attribution_author = $story->DisplayElements('attribution_author');

        if ( $attribution_url != '' ) {
            $article->set_var('attribution_url', $attribution_url);
        }
        if ( $attribution_name != '' ) {
            $article->set_var('attribution_name', $attribution_name);
        }
        if ( $attribution_author != '' ) {
            $article->set_var('attribution_author', $attribution_author);
        }

        $article->set_var('lang_source',$LANG01['source']);

        $story_image = $story->DisplayElements('story_image');
        $story_video = $story->DisplayElements('story_video');
        $sv_autoplay = $story->DisplayElements('sv_autoplay');

        $article->set_var('story_image',$story_image);
        $article->set_var('story_video',$story_video);
        if ( $sv_autoplay ) {
            $article->set_var('autoplay',"autoplay");
        } else {
            $article->unset_var('autoplay');
        }

        $article->set_var( 'story_topic_url', $topicurl );
        $article->set_var( 'alt_story_topic_url',$alttopicurl);

        $recent_post_anchortag = '';
        $articleUrl = COM_buildUrl($_CONF['site_url'] . '/article.php?story='
                                    . $story->getSid());
        $article->set_var('story_title', $story->DisplayElements('title'));
        $article->set_var('lang_permalink', $LANG01[127]);

        $show_comments = true;

        // n = 'Compact/index display' for list of stories. p = 'Preview' mode.

        if ((($index != 'n') && ($index != 'p')) || !empty($query)) {
            $attributes = ' class="non-ul"';
            $attr_array = array('class' => 'non-ul');
            if (!empty($query)) {
                $attributes .= ' rel="bookmark"';
                $attr_array['rel'] = 'bookmark';
            }
            $article->set_var('start_storylink_anchortag',
                              '<a href="' . $articleUrl . '"' . $attributes . '>');
            $article->set_var('end_storylink_anchortag', '</a>');
            $article->set_var('story_title_link',
                COM_createLink(
                        $story->DisplayElements('title'),
                        $articleUrl,
                        $attr_array
                )
            );
            $article->set_var('story_url',$articleUrl);
        } else {
            $article->set_var('story_title_link', $story->DisplayElements('title'));
        }

        // full article or preview

        if (( $index == 'n' ) || ( $index == 'p' )) {
            if (( $_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] ) &&
                    SEC_hasRights( 'story.ping' )) {
                $url = $_CONF['site_admin_url']
                     . '/trackback.php?mode=sendall&amp;id=' . $story->getSid();
                $article->set_var( 'send_trackback_link',
                    COM_createLink($LANG_TRB['send_trackback'], $url)
                );
                $pingico = '<img src="' . $_CONF['layout_url'] . '/images/sendping.'
                    . $_IMAGE_TYPE . '" alt="' . $LANG_TRB['send_trackback']
                    . '" title="' . $LANG_TRB['send_trackback'] . '" />';
                $article->set_var( 'send_trackback_icon',
                    COM_createLink($pingico, $url)
                );
                $article->set_var( 'send_trackback_url', $url );
                $article->set_var( 'lang_send_trackback_text',
                                   $LANG_TRB['send_trackback'] );
            }
            // add print icon
            $printUrl = COM_buildUrl( $_CONF['site_url'] . '/article.php?story='
                                      . $story->getSid() . '&amp;mode=print' );
            if ( $_CONF['hideprintericon'] == 1 ) {
                $article->set_var( 'print_icon', '' );
            } else {
                $printicon = '<img src="' . $_CONF['layout_url']
                    . '/images/print.' . $_IMAGE_TYPE . '" alt="' . $LANG01[65]
                    . '" title="' . $LANG11[3] . '" />';
                $article->set_var( 'print_icon',
                    COM_createLink($printicon, $printUrl, array('rel' => 'nofollow'))
                );
                $article->set_var( 'print_story_url', $printUrl );
                $article->set_var( 'lang_print_story', $LANG11[3] );
                $article->set_var( 'lang_print_story_alt', $LANG01[65] );
            }
// email
            if (( $_CONF['hideemailicon'] == 1 ) ||
               ( COM_isAnonUser() &&
                    (( $_CONF['loginrequired'] == 1 ) ||
                     ( $_CONF['emailstoryloginrequired'] == 1 )))) {
                $article->set_var( 'email_icon', '' );
            } else {
                $emailUrl = $_CONF['site_url'] . '/profiles.php?sid=' . $story->getSid()
                          . '&amp;what=emailstory';
                $emailicon = '<img src="' . $_CONF['layout_url'] . '/images/mail.'
                    . $_IMAGE_TYPE . '" alt="' . $LANG01[64] . '" title="'
                    . $LANG11[2] . '" />';
                $article->set_var( 'email_icon',
                    COM_createLink($emailicon, $emailUrl)
                );
                $article->set_var( 'email_story_url', $emailUrl );
                $article->set_var( 'lang_email_story', $LANG11[2] );
                $article->set_var( 'lang_email_story_alt', $LANG01[64] );
            }
// subscribe
            if ($_CONF['backend'] == 1) {
                $tid = $story->displayElements('tid');
                $alt_tid = $story->displayElements('alternate_tid');
                $result = DB_query("SELECT filename, title FROM {$_TABLES['syndication']} WHERE type = 'article' AND topic = '".DB_escapeString($tid)."' AND is_enabled = 1");
                $feeds = DB_numRows($result);
                for ($i = 0; $i < $feeds; $i++) {
                    list($filename, $title) = DB_fetchArray($result);
                    $feedUrl = SYND_getFeedUrl($filename);
                    $feedTitle = sprintf($LANG11[6],$title);
                }
                if ( $feeds > 0 ) {
                    $feedicon = '<img src="'. $_CONF['layout_url'] . '/images/rss_small.'
                             . $_IMAGE_TYPE . '" alt="'. $feedTitle
                             .'" title="'. $feedTitle .'" />';
                    $article->set_var( 'feed_icon',COM_createLink($feedicon, $feedUrl,array("type" =>"application/rss+xml")));
                    $article->set_var( 'feed_url', $feedUrl);
                } else {
                    $article->set_var( 'feed_icon', '' );
                }
            } else {
                $article->set_var( 'feed_icon', '' );
            }
        } else { // index view
            if ( !empty( $bodytext )) {
                $article->set_var( 'lang_readmore', $LANG01[2] );
                $article->set_var( 'lang_readmore_words', $LANG01[62] );
                $numwords = COM_numberFormat (sizeof( explode( ' ', strip_tags( $bodytext ))));
                $article->set_var( 'readmore_words', $numwords );

                $article->set_var( 'readmore_link',
                    COM_createLink(
                        $LANG01[2],
                        $articleUrl,
                        array('class'=>'story-read-more-link')
                    )
                    . ' (' . $numwords . ' ' . $LANG01[62] . ') ' );
                $article->set_var('start_readmore_anchortag', '<a href="'
                        . $articleUrl . '" class="story-read-more-link">');
                $article->set_var('end_readmore_anchortag', '</a>');
                $article->set_var('read_more_class', 'class="story-read-more-link"');
                $article->set_var('readmore_url',$articleUrl);
            }

            if ( ( $story->DisplayElements('commentcode') >= 0 ) and ( $show_comments ) ) {
                $commentsUrl = COM_buildUrl( $_CONF['site_url']
                        . '/article.php?story=' . $story->getSid() ) . '#comments';

                $cmtLinkArray = CMT_getCommentLinkWithCount( 'article', $story->getSid(), $_CONF['site_url'].'/article.php?story=' . $story->getSid(),$story->DisplayElements('comments'),1);
                $article->set_var( 'comments_with_count_link', $cmtLinkArray['link_with_count'] );
                $article->set_var( 'comments_url',$cmtLinkArray['url']);
                $article->set_var( 'comments_url_extra',$cmtLinkArray['url_extra']);
                $article->set_var( 'comments_text', $cmtLinkArray['comment_count']);
                $article->set_var( 'comments_count', $cmtLinkArray['comment_count']);

                $article->set_var( 'lang_comments', $LANG01[3] );
                $comments_with_count = sprintf( $LANG01[121], COM_numberFormat( $story->DisplayElements('comments') ));

                if ( $story->DisplayElements('comments') > 0 ) {
                    $result = DB_query( "SELECT UNIX_TIMESTAMP(date) AS day,username,fullname,{$_TABLES['comments']}.uid as cuid FROM {$_TABLES['comments']},{$_TABLES['users']} WHERE {$_TABLES['users']}.uid = {$_TABLES['comments']}.uid AND sid = '".DB_escapeString($story->getsid())."' ORDER BY date desc LIMIT 1" );
                    $C = DB_fetchArray( $result );

                    $recent_post_anchortag = '<span class="storybyline">'
                            . $LANG01[27] . ': '
                            . strftime( $_CONF['daytime'], $C['day'] ) . ' '
                            . $LANG01[104] . ' ' . COM_getDisplayName ($C['cuid'],
                                                    $C['username'], $C['fullname'])
                            . '</span>';
                    $article->set_var( 'comments_with_count', COM_createLink($comments_with_count, $commentsUrl));
                    $article->set_var( 'start_comments_anchortag', '<a href="'
                            . $commentsUrl . '">' );
                    $article->set_var( 'end_comments_anchortag', '</a>' );
                } else {
                    $article->set_var( 'comments_with_count', $comments_with_count);
                    $recent_post_anchortag = COM_createLink($LANG01[60],
                        $_CONF['site_url'] . '/comment.php?sid=' . $story->getsid() . '#comment_entry'
                            . '&amp;pid=0&amp;type=article');
                }
                if ( $story->DisplayElements( 'commentcode' ) == 0 &&
                 ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
                    $postCommentUrl = $_CONF['site_url'] . '/comment.php?sid='
                                . $story->getSid() . '&amp;pid=0&amp;type=article#comment_entry';
                    $article->set_var( 'post_comment_link',
                            COM_createLink($LANG01[60], $postCommentUrl,
                                           array('rel' => 'nofollow')));
                    $article->set_var( 'lang_post_comment', $LANG01[60] );
                    $article->set_var( 'start_post_comment_anchortag',
                                       '<a href="' . $postCommentUrl
                                       . '" rel="nofollow">' );
                    $article->set_var( 'end_post_comment_anchortag', '</a>' );
                    $article->set_var( 'post_comment_url', $postCommentUrl);
                }
            }
            if (( $_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] ) &&
                    ( $story->DisplayElements('trackbackcode') >= 0 ) && ( $show_comments )) {
                $num_trackbacks = COM_numberFormat( $story->DisplayElements('trackbacks') );
                $trackbacksUrl = COM_buildUrl( $_CONF['site_url']
                        . '/article.php?story=' . $story->getSid() ) . '#trackback';
                $article->set_var( 'trackbacks_url', $trackbacksUrl );
                $article->set_var( 'trackbacks_text', $num_trackbacks . ' '
                                                      . $LANG_TRB['trackbacks'] );
                $article->set_var( 'trackbacks_count', $num_trackbacks );
                $article->set_var( 'lang_trackbacks', $LANG_TRB['trackbacks'] );
                $article->set_var( 'trackbacks_with_count',
                    COM_createLink(
                        sprintf( $LANG01[122], $num_trackbacks ),
                        $trackbacksUrl
                    )
                );

                if (SEC_hasRights( 'story.ping' )) {
                    $pingurl = $_CONF['site_admin_url']
                        . '/trackback.php?mode=sendall&amp;id=' . $story->getSid();
                    $pingico = '<img src="' . $_CONF['layout_url'] . '/images/sendping.'
                        . $_IMAGE_TYPE . '" alt="' . $LANG_TRB['send_trackback']
                        . '" title="' . $LANG_TRB['send_trackback'] . '" />';
                    $article->set_var( 'send_trackback_icon',
                        COM_createLink($pingico, $pingurl)
                    );
                }

                if ( $story->DisplayElements('trackbacks') > 0 ) {
                    $article->set_var( 'trackbacks_with_count',
                        COM_createLink(
                            sprintf( $LANG01[122], $num_trackbacks ),
                            $trackbacksUrl
                        )
                    );
                } else {
                    $article->set_var( 'trackbacks_with_count',
                            sprintf( $LANG01[122], $num_trackbacks )
                    );
                }
            }
            if (( $_CONF['hideemailicon'] == 1 ) ||
               ( COM_isAnonUser() &&
                    (( $_CONF['loginrequired'] == 1 ) ||
                     ( $_CONF['emailstoryloginrequired'] == 1 )))) {
                $article->set_var( 'email_icon', '' );
            } else {
                $emailUrl = $_CONF['site_url'] . '/profiles.php?sid=' . $story->getSid()
                          . '&amp;what=emailstory';
                $emailicon = '<img src="' . $_CONF['layout_url'] . '/images/mail.'
                    . $_IMAGE_TYPE . '" alt="' . $LANG01[64] . '" title="'
                    . $LANG11[2] . '" />';
                $article->set_var( 'email_icon',
                    COM_createLink($emailicon, $emailUrl)
                );
                $article->set_var( 'email_story_url', $emailUrl );
                $article->set_var( 'lang_email_story', $LANG11[2] );
                $article->set_var( 'lang_email_story_alt', $LANG01[64] );
            }
            $printUrl = COM_buildUrl( $_CONF['site_url'] . '/article.php?story='
                                      . $story->getSid() . '&amp;mode=print' );
            if ( $_CONF['hideprintericon'] == 1 ) {
                $article->set_var( 'print_icon', '' );
            } else {
                $printicon = '<img src="' . $_CONF['layout_url']
                    . '/images/print.' . $_IMAGE_TYPE . '" alt="' . $LANG01[65]
                    . '" title="' . $LANG11[3] . '" />';
                $article->set_var( 'print_icon',
                    COM_createLink($printicon, $printUrl, array('rel' => 'nofollow'))
                );
                $article->set_var( 'print_story_url', $printUrl );
                $article->set_var( 'lang_print_story', $LANG11[3] );
                $article->set_var( 'lang_print_story_alt', $LANG01[65] );
            }
            $article->set_var( 'pdf_icon', '' );

            if ($_CONF['backend'] == 1) {
                $tid = $story->displayElements('tid');
                $alt_tid = $story->displayElements('alternate_tid');
                $result = DB_query("SELECT filename, title FROM {$_TABLES['syndication']} WHERE type = 'article' AND topic = '".DB_escapeString($tid)."' AND is_enabled = 1");
                $feeds = DB_numRows($result);
                for ($i = 0; $i < $feeds; $i++) {
                    list($filename, $title) = DB_fetchArray($result);
                    $feedUrl = SYND_getFeedUrl($filename);
                    $feedTitle = sprintf($LANG11[6],$title);
                }
                if ( $feeds > 0 ) {
                    $feedicon = '<img src="'. $_CONF['layout_url'] . '/images/rss_small.'
                             . $_IMAGE_TYPE . '" alt="'. $feedTitle
                             .'" title="'. $feedTitle .'" />';
                    $article->set_var( 'feed_icon',COM_createLink($feedicon, $feedUrl,array("type" =>"application/rss+xml")));
                    $article->set_var( 'feed_url', $feedUrl);
                } else {
                    $article->set_var( 'feed_icon', '' );
                }
            } else {
                $article->set_var( 'feed_icon', '' );
            }
        }
        $article->set_var( 'article_url', $articleUrl );
        $article->set_var( 'recent_post_anchortag', $recent_post_anchortag );

        $access = $story->checkAccess();
        $storyAccess = min($access, SEC_hasTopicAccess($story->DisplayElements('tid')));
        if (($index != 'p') AND SEC_hasRights('story.edit') AND
          ($story->checkAccess() == 3) AND
          (SEC_hasTopicAccess($story->DisplayElements('tid')) == 3)) {
            $article->set_var( 'edit_link',
                COM_createLink($LANG01[4], $_CONF['site_admin_url']
                    . '/story.php?edit=x&amp;sid=' . $story->getSid())
                );
            $article->set_var( 'edit_url', $_CONF['site_admin_url']
                    . '/story.php?edit=x&amp;sid=' . $story->getSid() );
            $article->set_var( 'lang_edit_text',  $LANG01[4] );
            $editicon = $_CONF['layout_url'] . '/images/edit.' . $_IMAGE_TYPE;
            $editiconhtml = '<img src="' . $editicon . '" alt="' . $LANG01[4] . '" title="' . $LANG01[4] . '" />';
            $article->set_var( 'edit_icon',
                COM_createLink(
                    $editiconhtml,
                    $_CONF['site_admin_url'] . '/story.php?edit=x&amp;sid=' . $story->getSid()
                )
            );
            $article->set_var( 'edit_image', $editiconhtml);
        }
        $article->set_var('lang_continue_reading',$LANG01['continue_reading']);

        if ($index != 'p') {
            $article->create_instance($instance_id,$article_filevar);
        }
    }
    PLG_templateSetVars($article_filevar,$article);
    $article->parse('finalstory',$article_filevar);
    SESS_clearContext();
    return $article->finish( $article->get_var( 'finalstory' ));
}

/**
* Extract links from an HTML-formatted text.
*
* Collects all the links in a story and returns them in an array.
*
* @param    string  $fulltext   the text to search for links
* @param    int     $maxlength  max. length of text in a link (can be 0)
* @return   array   an array of strings of form <a href="...">link</a>
*
*/
function STORY_extractLinks( $fulltext, $maxlength = 100 )
{
    $rel = array();

    /* Only match anchor tags that contain 'href="<something>"'
     */
    preg_match_all( "/<a[^>]*href=[\"']([^\"']*)[\"'][^>]*>(.*?)<\/a>/i", $fulltext, $matches );
    for ( $i=0; $i< count( $matches[0] ); $i++ )
    {
        $matches[2][$i] = strip_tags( $matches[2][$i] );
        if ( !utf8_strlen( trim( $matches[2][$i] ) ) ) {
            $matches[2][$i] = strip_tags( $matches[1][$i] );
        }

        // if link is too long, shorten it and add ... at the end
        if ( ( $maxlength > 0 ) && ( utf8_strlen( $matches[2][$i] ) > $maxlength ) ) {
            $matches[2][$i] = substr( $matches[2][$i], 0, $maxlength - 3 ) . '...';
        }

        $rel[] = '<a href="' . $matches[1][$i] . '">'
               . str_replace(array("\015", "\012"), '', $matches[2][$i])
               . '</a>';
    }

    return( $rel );
}

/**
* Create "What's Related" links for a story
*
* Creates an HTML-formatted list of links to be used for the What's Related
* block next to a story (in article view).
*
* @param        string      $related    contents of gl_stories 'related' field
* @param        int         $uid        user id of the author
* @param        int         $tid        topic id
* @param        int         $atid       alternate tid
* @return       string      HTML-formatted list of links
*/

function STORY_whatsRelated( $related, $uid, $tid, $atid = '' )
{
    global $_CONF, $_TABLES, $_USER, $LANG24;

    if ( function_exists( 'CUSTOM_whatsRelated' )) {
        return CUSTOM_whatsRelated( $related,$uid,$tid );
    }

    // get the links from the story text
    if (!empty ($related)) {
        $rel = explode ("\n", $related);
    } else {
        $rel = array ();
    }

    if ( !COM_isAnonUser() || (( $_CONF['loginrequired'] == 0 ) &&
           ( $_CONF['searchloginrequired'] == 0 ))) {
        // add a link to "search by author"
        if ( $_CONF['contributedbyline'] == 1 ) {
            $author = COM_getDisplayName( $uid );
            $rel[] = "<a href=\"{$_CONF['site_url']}/search.php?mode=search&amp;type=stories&amp;author=$uid\">{$LANG24[37]} $author</a>";
        }

        // add a link to "search by topic"
        $topic = DB_getItem( $_TABLES['topics'], 'topic', "tid = '".DB_escapeString($tid)."'" );
        $rel[] = '<a href="' . $_CONF['site_url']
               . '/index.php?topic=' . $tid
               . '">' . $LANG24[38] . ' ' . $topic . '</a>';
        if ( $atid != '' ) {
            $atopic = DB_getItem( $_TABLES['topics'], 'topic', "tid = '".DB_escapeString($atid)."'" );
            $rel[] = '<a href="' . $_CONF['site_url']
                   . '/index.php?topic=' . $atid
                   . '">' . $LANG24[38] . ' ' . $atopic . '</a>';
        }
    }

    $related = '';
    if ( sizeof( $rel ) > 0 ) {
        $related = COM_checkWords( COM_makeList( $rel, 'list-whats-related' ));
    }

    return( $related );
}

/**
* Delete one image from a story
*
* Deletes scaled and unscaled image, but does not update the database.
*
* @param    string  $image  file name of the image (without the path)
*
*/
function STORY_deleteImage ($image)
{
    global $_CONF;

    if (empty ($image)) {
        return;
    }

    $filename = $_CONF['path_images'] . 'articles/' . $image;
    if (!@unlink ($filename)) {
        // log the problem but don't abort the script
        COM_errorLog ('Unable to remove the following image from the article: ' . $filename);
    }

    // remove unscaled image, if it exists
    $lFilename_large = substr_replace ($image, '_original.',
                                       strrpos ($image, '.'), 1);
    $lFilename_large_complete = $_CONF['path_images'] . 'articles/'
                              . $lFilename_large;
    if (file_exists ($lFilename_large_complete)) {
        if (!@unlink ($lFilename_large_complete)) {
            // again, log the problem but don't abort the script
            COM_errorLog ('Unable to remove the following image from the article: ' . $lFilename_large_complete);
        }
    }
}

/**
* Delete all images from a story
*
* Deletes all scaled and unscaled images from the file system and the database.
*
* @param    string  $sid    story id
*
*/
function STORY_deleteImages ($sid)
{
    global $_TABLES;

    $result = DB_query ("SELECT ai_filename FROM {$_TABLES['article_images']} WHERE ai_sid = '".DB_escapeString($sid)."'");
    $nrows = DB_numRows ($result);
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray ($result);
        STORY_deleteImage ($A['ai_filename']);
    }
    DB_delete ($_TABLES['article_images'], 'ai_sid', DB_escapeString($sid));
}

/**
* Return information for a story
*
* This is the story equivalent of PLG_getItemInfo. See lib-plugins.php for
* details.
*
* @param    string  $sid        story ID or '*'
* @param    string  $what       comma-separated list of story properties
* @param    int     $uid        user ID or 0 = current user
* @return   mixed               string or array of strings with the information
*
*/
function STORY_getItemInfo($sid, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG09;

    $properties = explode(',', $what);

    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
            case 'date' :
            case 'date-created':
                $fields[] = 'UNIX_TIMESTAMP(date) AS unixdate';
                break;
            case 'description':
            case 'raw-description':
            case 'searchidx' :
                $fields[] = 'introtext';
                $fields[] = 'bodytext';
                break;
            case 'excerpt':
                $fields[] = 'introtext';
                break;
            case 'feed':
                $fields[] = 'tid';
                break;
            case 'id':
                $fields[] = 'sid';
                break;
            case 'title':
                $fields[] = 'title';
                break;
            case 'url':
            case 'label':
                $fields[] = 'sid';
                break;
            case 'status' :
                $fields[] = 'draft_flag';
                break;
            case 'author' :
                $fields[] = 'uid';
                break;
            case 'image_url' :
                $fields[] = 'story_image';
                break;
            case 'video_url' :
                $fields[] = 'story_video';
                break;
            case 'perms' :
                $fields[] = 'owner_id';
                $fields[] = 'group_id';
                $fields[] = 'perm_owner';
                $fields[] = 'perm_group';
                $fields[] = 'perm_members';
                $fields[] = 'perm_anon';
                break;
            case 'hits' :
                $fields[] = 'hits';
                break;
            default:
                break;
        }
    }

    $fields = array_unique($fields);

    if (count($fields) == 0) {
        $retval = array();
        return NULL;
    }

    if ($sid == '*') {
        $where = ' WHERE';
    } else {
        $where = " WHERE (sid = '" . DB_escapeString($sid) . "') AND";
    }
    $where .= ' (date <= NOW())';
    if ($uid > 0) {
        $permSql = COM_getPermSql('AND', $uid)
                 . COM_getTopicSql('AND', $uid);
    } else {
        $permSql = COM_getPermSql('AND') . COM_getTopicSql('AND');
    }
    $sql = "SELECT " . implode(',', $fields) . " FROM {$_TABLES['stories']}" . $where . $permSql;
    if ($sid != '*') {
        $sql .= ' LIMIT 1';
    }

    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);

        $props = array();
        foreach ($properties as $p) {
            switch ($p) {
                case 'date' :
                    $props['date'] = $A['unixdate'];
                    break;
                case 'date-created':
                    $props['date-created'] = $A['unixdate'];
                    break;
                case 'description':
                case 'searchidx' :
                    $props[$p] = trim(PLG_replaceTags($A['introtext'] . ' ' . $A['bodytext'],'glfusion','story'));
                    break;
                case 'raw-description':
                    $props['raw-description'] = trim($A['introtext'] . ' ' . $A['bodytext']);
                    break;
                case 'excerpt':
                    $excerpt = $A['introtext'];
                    $props['excerpt'] = trim(PLG_replaceTags($excerpt,'glfusion','story'));
                    break;
                case 'feed':
                    $feedfile = DB_getItem($_TABLES['syndication'], 'filename',
                                           "topic = '::all'");
                    if (empty($feedfile)) {
                        $feedfile = DB_getItem($_TABLES['syndication'], 'filename',
                                               "topic = '::frontpage'");
                    }
                    if (empty($feedfile)) {
                        $feedfile = DB_getItem($_TABLES['syndication'], 'filename',
                                               "topic = '{$A['tid']}'");
                    }
                    if (empty($feedfile)) {
                        $props['feed'] = '';
                    } else {
                        $props['feed'] = SYND_getFeedUrl($feedfile);
                    }
                    break;
                case 'id':
                    $props['id'] = $A['sid'];
                    break;
                case 'title':
                    $props['title'] = $A['title'];
                    break;
                case 'url':
                    if (empty($A['sid'])) {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/article.php?story=' . $sid);
                    } else {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/article.php?story=' . $A['sid']);
                    }
                    break;
                case 'label':
                    $props['label'] = $LANG09[65];
                    break;
                case 'status' :
                    if ( $A['draft_flag'] == 0 ) {
                        $props['status'] = 1;
                    } else {
                        $props['status'] = 0;
                    }
                    break;
                case 'author' :
                    $props['author'] = $A['uid'];
                    break;
                case 'image_url' :
                    if ( $A['story_image'] != '' && $A['story_image'] != NULL ) {
                        $props['image_url'] = $_CONF['site_url'].$A['story_image'];
                    }
                    break;
                case 'video_url' :
                    if ( $A['story_video'] != '' && $A['story_video'] != NULL ) {
                        $props['video_url'] = $_CONF['site_url'].$A['story_video'];
                    }
                    break;
                case 'hits' :
                    $props['hits'] = $A['hits'];
                    break;
                case 'perms' :
                    $props['perms'] = array(
                        'owner_id' => $A['owner_id'],
                        'group_id' => $A['group_id'],
                        'perm_owner' => $A['perm_owner'],
                        'perm_group' => $A['perm_group'],
                        'perm_members' => $A['perm_members'],
                        'perm_anon' => $A['perm_anon'],
                    );
                    break;

                default:
                    $props[$p] = '';
                    break;
            }
        }

        $mapped = array();
        foreach ($props as $key => $value) {
            if ($sid == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
            }
        }

        if ($sid == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($sid != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }
    if ( $retval === '' || (is_array($retval) && count($retval) == 0 ) ) return NULL;

    return $retval;
}



/**
* Delete a story.
*
* This is used to delete a story from the list of stories.
*
* @param    string  $sid    ID of the story to delete
* @return   string          HTML, e.g. a meta redirect
*
*/
function STORY_deleteStory($sid)
{
    $args = array (
                    'sid' => $sid
                  );

    $output = '';

    PLG_invokeService('story', 'delete', $args, $output, $svc_msg);

    return $output;
}

/**
* Checks and Updates the featured status of all articles.
*
* Checks to see if any articles that were published for the future have been
* published and, if so, will see if they are featured.  If they are featured,
* this will set old featured article (if there is one) to normal
*
*/

function STORY_featuredCheck()
{
    global $_TABLES;

    // allow only 1 featured for frontpage
    $sql = "SELECT sid FROM {$_TABLES['stories']} WHERE featured = 1 AND draft_flag = 0 AND frontpage = 1 AND date <= NOW() ORDER BY date DESC LIMIT 2";
    $result = DB_query($sql);
    $numrows = DB_numRows($result);
    if ($numrows > 1) {
        $F = DB_fetchArray($result);
        // un-feature all other featured frontpage story
        $sql = "UPDATE {$_TABLES['stories']} SET featured = 0 WHERE featured = 1 AND draft_flag = 0 AND frontpage = 1 AND date <= NOW() AND sid <> '{$F['sid']}'";
        DB_query($sql);
    }
    // check all topics
    $sql = "SELECT tid FROM {$_TABLES['topics']}";
    $topicResult = DB_query($sql);
    $topicRows = DB_numRows($topicResult);
    for($i = 0; $i < $topicRows; $i++) {
        $T = DB_fetchArray($topicResult);
        $sql = "SELECT sid FROM {$_TABLES['stories']} WHERE featured = 1 AND draft_flag = 0 AND tid = '{$T['tid']}' AND date <= NOW() ORDER BY date DESC LIMIT 2";
        $storyResult = DB_query($sql);
        $storyRows   = DB_numRows($storyResult);
        if ($storyRows > 1) {
            // OK, we have two or more featured stories in a topic, fix that
            $S = DB_fetchArray($storyResult);
            $sql = "UPDATE {$_TABLES['stories']} SET featured = 0 WHERE featured = 1 AND draft_flag = 0 AND tid = '{$T['tid']}' AND date <= NOW() AND sid <> '{$S['sid']}'";
            DB_query($sql);
        }
    }
}

/**
 * Inserts image HTML into the place of Image Placeholders
 *
 * @return string   Text with image placeholders removed
 */
function STORY_renderImages($sid, $text)
{
    global $_CONF, $_TABLES, $LANG24;

    $parsedText = $text;
    $ai_sid = $sid;

    $result = DB_query("SELECT ai_filename FROM {$_TABLES['article_images']} WHERE ai_sid = '{$ai_sid}' ORDER BY ai_img_num");

    $nrows = DB_numRows($result);
    $errors = array();
    $stdImageLoc = true;

    if (!strstr($_CONF['path_images'], $_CONF['path_html'])) {
        $stdImageLoc = false;
    }

    for ($i = 1; $i <= $nrows; $i++) {
        $A = DB_fetchArray($result);

        $sizeattributes = COM_getImgSizeAttributes($_CONF['path_images'] . 'articles/' . $A['ai_filename']);

        $norm = '[image' . $i . ']';
        $left = '[image' . $i . '_left]';
        $right = '[image' . $i . '_right]';

        $unscalednorm = '[unscaled' . $i . ']';
        $unscaledleft = '[unscaled' . $i . '_left]';
        $unscaledright = '[unscaled' . $i . '_right]';


        $imgpath = '';

        // If we are storing images on a "standard path" i.e. is
        // available to the host web server, then the url to this
        // image is based on the path to images, site url, articles
        // folder and it's filename.
        //
        // Otherwise, we have to use the image handler to load the
        // image from whereever else on the file system we're
        // keeping them:
        if ($stdImageLoc) {
            $imgpath = substr($_CONF['path_images'], strlen($_CONF['path_html']));
            $imgSrc = $_CONF['site_url'] . '/' . $imgpath . 'articles/' . $A['ai_filename'];
        } else {
            $imgSrc = $_CONF['site_url'] . '/getimage.php?mode=articles&amp;image=' . $A['ai_filename'];
        }

        // Build image tags for each flavour of the image:
        $img_noalign = '<img ' . $sizeattributes . 'src="' . $imgSrc . '" alt=""' . XHTML . '>';
        $img_leftalgn = '<img ' . $sizeattributes . 'class="floatleft" src="' . $imgSrc . '" alt=""' . XHTML . '>';
        $img_rightalgn = '<img ' . $sizeattributes . 'class="floatright" src="' . $imgSrc . '" alt=""' . XHTML . '>';


        // Are we keeping unscaled images?
        if ($_CONF['keep_unscaled_image'] == 1) {
            // Yes we are, so, we need to find out what the filename
            // of the original, unscaled image is:
            $lFilename_large = substr_replace($A['ai_filename'], '_original.',
                                    strrpos($A['ai_filename'], '.'), 1);
            $lFilename_large_complete = $_CONF['path_images'] . 'articles/' .
                                            $lFilename_large;

            // We need to map that filename to the right location
            // or the fetch script:
            if ($stdImageLoc) {
                $lFilename_large_URL = $_CONF['site_url'] . '/' . $imgpath .
                                        'articles/' . $lFilename_large;
            } else {
                $lFilename_large_URL = $_CONF['site_url'] .
                                        '/getimage.php?mode=show&amp;image=' .
                                        $lFilename_large;
            }

            // And finally, replace the [imageX_mode] tags with the
            // image and its hyperlink (only when the large image
            // actually exists)
            $lLink_url  = '';
            $lLink_attr = '';
            if (file_exists($lFilename_large_complete)) {
                $lLink_url = $lFilename_large_URL;
                $lLink_attr = array('rel' => 'lightbox','data-uk-lightbox' => '');
            }
        }

        if (!empty($lLink_url)) {
            $parsedText = str_replace($norm,  COM_createLink($img_noalign,   $lLink_url, $lLink_attr), $parsedText);
            $parsedText = str_replace($left,  COM_createLink($img_leftalgn,  $lLink_url, $lLink_attr), $parsedText);
            $parsedText = str_replace($right, COM_createLink($img_rightalgn, $lLink_url, $lLink_attr), $parsedText);
        } else {
            // We aren't wrapping our image tags in hyperlinks, so
            // just replace the [imagex_mode] tags with the image:
            $parsedText = str_replace($norm,  $img_noalign,   $parsedText);
            $parsedText = str_replace($left,  $img_leftalgn,  $parsedText);
            $parsedText = str_replace($right, $img_rightalgn, $parsedText);
        }

        // And insert the unscaled mode images:
        if (($_CONF['allow_user_scaling'] == 1) and ($_CONF['keep_unscaled_image'] == 1)) {
            if (file_exists($lFilename_large_complete)) {
                $imgSrc = $lFilename_large_URL;
                $sizeattributes = COM_getImgSizeAttributes($lFilename_large_complete);
            }

            $parsedText = str_replace($unscalednorm, '<img ' . $sizeattributes . 'src="' .
                                 $imgSrc . '" alt=""' . XHTML . '>', $parsedText);
            $parsedText = str_replace($unscaledleft, '<img ' . $sizeattributes .
                                 'align="left" src="' . $imgSrc . '" alt=""' . XHTML . '>', $parsedText);
            $parsedText = str_replace($unscaledright, '<img ' . $sizeattributes .
                                 'align="right" src="' . $imgSrc. '" alt=""' . XHTML . '>', $parsedText);
        }
    }
    return $parsedText;
}

/*
 * START SERVICES SECTION
 * This section implements the various services offered by the story module
 */


/**
 * Submit a new or updated story. The story is updated if it exists, or a new one is created
 *
 * @param   array   args    Contains all the data provided by the client
 * @param   string  &output OUTPUT parameter containing the returned text
 * @return  int		    Response code as defined in lib-plugins.php
 */
function service_submit_story($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $_USER, $LANG24, $MESSAGE, $_GROUPS;

    if (!SEC_hasRights('story.edit')) {
        $output .= COM_showMessageText($MESSAGE[31], $MESSAGE[30],true,'error');

        return PLG_RET_AUTH_FAILED;
    }

    $gl_edit = false;
    if (isset($args['gl_edit'])) {
        $gl_edit = $args['gl_edit'];
    }
    if ($gl_edit) {
        /* This is EDIT mode, so there should be an old sid */
        if (empty($args['old_sid'])) {
            if (!empty($args['id'])) {
                $args['old_sid'] = $args['id'];
            } else {
                return PLG_RET_ERROR;
            }

            if (empty($args['sid'])) {
                $args['sid'] = $args['old_sid'];
            }
        }
    } else {
        if (empty($args['sid']) && !empty($args['id'])) {
            $args['sid'] = $args['id'];
        }
    }

    /* Store the first CATEGORY as the Topic ID */
    if (!empty($args['category'][0])) {
        $args['tid'] = $args['category'][0];
    }

    $content = '';
    if (!empty($args['content'])) {
        $content = $args['content'];
    } else if (!empty($args['summary'])) {
        $content = $args['summary'];
    }
    if (!empty($content)) {
        $parts = explode('[page_break]', $content);
        if (count($parts) == 1) {
            $args['introtext'] = $content;
            $args['bodytext']  = '';
        } else {
            $args['introtext'] = array_shift($parts);
            $args['bodytext']  = implode('[page_break]', $parts);
        }
    }

    /* Apply filters to the parameters passed by the webservice */

    if ($args['gl_svc']) {
        if (isset($args['mode'])) {
            $args['mode'] = COM_applyBasicFilter($args['mode']);
        }
        if (isset($args['editopt'])) {
            $args['editopt'] = COM_applyBasicFilter($args['editopt']);
        }
    }

    /* - START: Set all the defaults - */

    if (empty($args['tid'])) {
        // see if we have a default topic
        $topic = DB_getItem($_TABLES['topics'], 'tid','is_default = 1' . COM_getPermSQL('AND'));
        if (!empty($topic)) {
            $args['tid'] = $topic;
        } else {
            // otherwise, just use the first one
            $o = array();
            $s = array();
            if (service_getTopicList_story(array('gl_svc' => true), $o, $s) == PLG_RET_OK) {
                $args['tid'] = $o[0];
            } else {
                $svc_msg['error_desc'] = 'No topics available';
                return PLG_RET_ERROR;
            }
        }
    }

    if (empty($args['owner_id'])) {
        $args['owner_id'] = $_USER['uid'];
    }

    if (empty($args['group_id'])) {
        $args['group_id'] = SEC_getFeatureGroup('story.edit', $_USER['uid']);
    }

    if ( isset($args['alternate_tid']) && $args['tid'] == $args['alternate_tid']) {
        $args['alternate_tid'] = NULL;
    }

    if (empty($args['postmode'])) {
        $args['postmode'] = $_CONF['postmode'];

        if (!empty($args['content_type'])) {
            if ($args['content_type'] == 'text') {
                $args['postmode'] = 'text';
            } else if (($args['content_type'] == 'html')
                    || ($args['content_type'] == 'xhtml')) {
                $args['postmode'] = 'html';
            }
        }
    }

    if ($args['gl_svc']) {

        /* Permissions */
        if (!isset($args['perm_owner'])) {
            $args['perm_owner'] = $_CONF['default_permissions_story'][0];
        } else {
            $args['perm_owner'] = COM_applyBasicFilter($args['perm_owner'], true);
        }
        if (!isset($args['perm_group'])) {
            $args['perm_group'] = $_CONF['default_permissions_story'][1];
        } else {
            $args['perm_group'] = COM_applyBasicFilter($args['perm_group'], true);
        }
        if (!isset($args['perm_members'])) {
            $args['perm_members'] = $_CONF['default_permissions_story'][2];
        } else {
            $args['perm_members'] = COM_applyBasicFilter($args['perm_members'], true);
        }
        if (!isset($args['perm_anon'])) {
            $args['perm_anon'] = $_CONF['default_permissions_story'][3];
        } else {
            $args['perm_anon'] = COM_applyBasicFilter($args['perm_anon'], true);
        }
        if (!isset($args['draft_flag'])) {
            $args['draft_flag'] = $_CONF['draft_flag'];
        }

        if (empty($args['frontpage'])) {
            $args['frontpage'] = $_CONF['frontpage'];
        }

        if (empty($args['show_topic_icon'])) {
            $args['show_topic_icon'] = $_CONF['show_topic_icon'];
        }
    }
    /* - END: Set all the defaults - */

    if ( isset($args['draft_flag_yes'] ) || isset($args['draft_flag_no']) ) {
        if ( isset($args['draft_flag_yes']) ) {
            $args['draft_flag'] = 1;
        } else {
            $args['draft_flag'] = 0;
        }
    }

    if (!isset($args['sid'])) {
        $args['sid'] = '';
    }
    $args['sid'] = COM_sanitizeID($args['sid']);
    if (!$gl_edit) {
        if (strlen($args['sid']) > STORY_MAX_ID_LENGTH) {
            $args['sid'] = COM_makesid();
        }
    }
    $story = new Story();

    $gl_edit = false;
    if (isset($args['gl_edit'])) {
        $gl_edit = $args['gl_edit'];
    }
    if ($gl_edit && !empty($args['gl_etag'])) {
        /* First load the original story to check if it has been modified */
        $result = $story->loadFromDatabase($args['sid']);
        if ($result == STORY_LOADED_OK) {
            if ($args['gl_etag'] != date('c', $story->_date)) {
                $svc_msg['error_desc'] = 'A more recent version of the story is available';
                return PLG_RET_PRECONDITION_FAILED;
            }
        } else {
            $svc_msg['error_desc'] = 'Error loading story';
            return PLG_RET_ERROR;
        }
    }

    /* This function is also doing the security checks */
    $result = $story->loadFromArgsArray($args);

    $sid = $story->getSid();

    switch ($result) {
        case STORY_DUPLICATE_SID:
            if (!$args['gl_svc']) {
                if ( isset($args['type']) && $args['type'] == 'submission' ) {
                    $output .= STORY_edit($sid,'moderate');
                } else {
                    $output .= STORY_edit($sid,'error');
                }
            }
            return PLG_RET_ERROR;

        case STORY_EXISTING_NO_EDIT_PERMISSION:
            $output .= COM_showMessageText($MESSAGE[31], $MESSAGE[30],true,'error');
            COM_accessLog("User {$_USER['username']} tried to illegally submit or edit story $sid.");
            return PLG_RET_PERMISSION_DENIED;
        case STORY_NO_ACCESS_PARAMS:
            $output .= COM_showMessageText($MESSAGE[31], $MESSAGE[30],true,'error');
            COM_accessLog("User {$_USER['username']} tried to illegally submit or edit story $sid.");
            return PLG_RET_PERMISSION_DENIED;
        case STORY_EMPTY_REQUIRED_FIELDS:
            if (!$args['gl_svc']) {
                $output .= STORY_edit($sid,'error');
            }
            return PLG_RET_ERROR;
        default:
            break;
    }

    /* Image upload is not supported by the web-service at present */
    if (!$args['gl_svc']) {
        // Delete any images if needed
        if (array_key_exists('delete', $args)) {
            $delete = count($args['delete']);
            for ($i = 1; $i <= $delete; $i++) {
                $ai_filename = DB_getItem ($_TABLES['article_images'],'ai_filename', "ai_sid = '".DB_escapeString($sid)."' AND ai_img_num = " . intval(key($args['delete'])));
                STORY_deleteImage ($ai_filename);
                DB_query ("DELETE FROM {$_TABLES['article_images']} WHERE ai_sid = '".DB_escapeString($sid)."' AND ai_img_num = '" . intval(key($args['delete'])) ."'");
                next($args['delete']);
            }
        }

        // OK, let's upload any pictures with the article
        if (DB_count($_TABLES['article_images'], 'ai_sid', DB_escapeString($sid)) > 0) {
            $index_start = DB_getItem($_TABLES['article_images'],'max(ai_img_num)',"ai_sid = '".DB_escapeString($sid)."'") + 1;
        } else {
            $index_start = 1;
        }

        if (count($_FILES) > 0 AND $_CONF['maximagesperarticle'] > 0) {
            require_once $_CONF['path_system'].'classes/upload.class.php';
            $upload = new upload();

            if (isset ($_CONF['debug_image_upload']) && $_CONF['debug_image_upload']) {
                $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
                $upload->setDebug (true);
            }
            $upload->setMaxFileUploads ($_CONF['maximagesperarticle']);
            $upload->setAutomaticResize(true);
            if ($_CONF['keep_unscaled_image'] == 1) {
                $upload->keepOriginalImage (true);
            } else {
                $upload->keepOriginalImage (false);
            }
            $upload->setAllowedMimeTypes (array (
                    'image/gif'   => '.gif',
                    'image/jpeg'  => '.jpg,.jpeg',
                    'image/pjpeg' => '.jpg,.jpeg',
                    'image/x-png' => '.png',
                    'image/png'   => '.png'
                    ));
            $upload->setFieldName('file');

//@TODO - better error handling...

            if (!$upload->setPath($_CONF['path_images'] . 'articles')) {
                $output = COM_siteHeader ('menu', $LANG24[30]);
                $output .= COM_showMessageText($upload->printErrors(false),$LANG24[30],true,'error');
                $output .= COM_siteFooter ();
                echo $output;
                exit;
            }

            // NOTE: if $_CONF['path_to_mogrify'] is set, the call below will
            // force any images bigger than the passed dimensions to be resized.
            // If mogrify is not set, any images larger than these dimensions
            // will get validation errors
            $upload->setMaxDimensions($_CONF['max_image_width'], $_CONF['max_image_height']);
            $upload->setMaxFileSize($_CONF['max_image_size']); // size in bytes, 1048576 = 1MB

            // Set file permissions on file after it gets uploaded (number is in octal)
            $upload->setPerms('0644');
            $filenames = array();

            $sql = "SELECT MAX(ai_img_num) + 1 AS ai_img_num FROM " . $_TABLES['article_images'] . " WHERE ai_sid = '" . DB_escapeString($sid) ."'";
	        $result = DB_query( $sql,1 );
	        $row = DB_fetchArray( $result );
	        $ai_img_num = $row['ai_img_num'];
	        if ( $ai_img_num < 1 ) {
	            $ai_img_num = 1;
	        }

            for ($z = 0; $z < $_CONF['maximagesperarticle']; $z++ ) {
                $curfile['name'] = '';
                if ( isset($_FILES['file']['name'][$z]) ) {
                    $curfile['name'] = $_FILES['file']['name'][$z];
                }
                if (!empty($curfile['name'])) {
                    $pos = strrpos($curfile['name'],'.') + 1;
                    $fextension = substr($curfile['name'], $pos);

                    $filenames[] = $sid . '_' . $ai_img_num . '.' . $fextension;
                    $ai_img_num++;
                } else {
                    $filenames[] = '';
                }
            }
            $upload->setFileNames($filenames);
            $upload->uploadFiles();
//@TODO - better error handling
            if ($upload->areErrors()) {
                $retval = COM_siteHeader('menu', $LANG24[30]);
                $retval .= COM_showMessageText($upload->printErrors(false),$LANG24[30],true,'error');
                $retval .= STORY_edit($sid,'error');
                $retval .= COM_siteFooter();
                echo $retval;
                exit;
            }
            for ($z = 0; $z < $_CONF['maximagesperarticle']; $z++ ) {
                if ( $filenames[$z] != '' ) {
                    $sql = "SELECT MAX(ai_img_num) + 1 AS ai_img_num FROM " . $_TABLES['article_images'] . " WHERE ai_sid = '" . DB_escapeString($sid) ."'";
        	        $result = DB_query( $sql,1 );
        	        $row = DB_fetchArray( $result );
        	        $ai_img_num = $row['ai_img_num'];
        	        if ( $ai_img_num < 1 ) {
        	            $ai_img_num = 1;
        	        }
                    DB_query("INSERT INTO {$_TABLES['article_images']} (ai_sid, ai_img_num, ai_filename) VALUES ('".DB_escapeString($sid)."', $ai_img_num, '" . DB_escapeString($filenames[$z]) . "')");
                }
            }
        }

        if ($_CONF['maximagesperarticle'] > 0) {
            $errors = $story->checkImages();
            if (count($errors) > 0) {
                $output = COM_siteHeader ('menu', $LANG24[54]);
                $eMsg = $LANG24[55] . '<p>';
                for ($i = 1; $i <= count($errors); $i++) {
                    $eMsg .= current($errors) . '<br />';
                    next($errors);
                }
//@TODO - use return here...
                $output .= COM_showMessageText($eMsg,$LANG24[54],true,'error');
                $output .= STORY_edit($sid,'error');
                $output .= COM_siteFooter();
                echo $output;
                exit;
            }
        }
    }

    $result = $story->saveToDatabase();

    if ($result == STORY_SAVED) {
        // see if any plugins want to act on that story
        if ((! empty($args['old_sid'])) && ($args['old_sid'] != $sid)) {
            PLG_itemSaved($sid, 'article', $args['old_sid']);
        } else {
            PLG_itemSaved($sid, 'article');
        }

        // update feed(s) and Older Stories block
        COM_rdfUpToDateCheck ('article', $story->DisplayElements('tid'), $sid);
        COM_olderStuff ();

        if ($story->type == 'submission') {
            COM_setMessage(9);
            echo COM_refresh ($_CONF['site_admin_url'] . '/moderation.php');
            exit;
        } else {
            $output = PLG_afterSaveSwitch($_CONF['aftersave_story'],
                    COM_buildURL("{$_CONF['site_url']}/article.php?story=$sid"),
                        'story', 9);
        }

        /* @TODO Set the object id here */
        $svc_msg['id'] = $sid;

        return PLG_RET_OK;
    }
}

/**
 * Delete an existing story
 *
 * @param   array   args    Contains all the data provided by the client
 * @param   string  &output OUTPUT parameter containing the returned text
 * @return  int		    Response code as defined in lib-plugins.php
 */
function service_delete_story($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $_USER;

    if (empty($args['sid']) && !empty($args['id'])) {
        $args['sid'] = $args['id'];
    }

    if ($args['gl_svc']) {
        $args['sid'] = COM_applyBasicFilter($args['sid']);
    }

    $sid = $args['sid'];

    $result = DB_query ("SELECT tid,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['stories']} WHERE sid = '".DB_escapeString($sid)."'");
    $A = DB_fetchArray ($result);
    $access = SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'],
                             $A['perm_group'], $A['perm_members'], $A['perm_anon']);
    $access = min ($access, SEC_hasTopicAccess ($A['tid']));
    if ($access < 3) {
        COM_accessLog ("User {$_USER['username']} tried to illegally delete story $sid.");
        $output = COM_refresh ($_CONF['site_admin_url'] . '/story.php');
        if ($_USER['uid'] > 1) {
            return PLG_RET_PERMISSION_DENIED;
        } else {
            return PLG_RET_AUTH_FAILED;
        }
    }

    STORY_deleteImages ($sid);
    DB_query("DELETE FROM {$_TABLES['comments']} WHERE sid = '".DB_escapeString($sid)."' AND type = 'article'");
    DB_delete ($_TABLES['stories'], 'sid', DB_escapeString($sid));

    // delete Trackbacks
    DB_query ("DELETE FROM {$_TABLES['trackback']} WHERE sid = '".DB_escapeString($sid)."' AND type = 'article';");

    PLG_itemDeleted($sid, 'article');

    // update RSS feed and Older Stories block
    CTL_clearCache();
    COM_rdfUpToDateCheck ();
    COM_olderStuff ();
    COM_setMessage(10);
    $output = COM_refresh ($_CONF['site_admin_url'] . '/story.php');

    return PLG_RET_OK;
}

/**
 * Get an existing story
 *
 * @param   array   args    Contains all the data provided by the client
 * @param   string  &output OUTPUT parameter containing the returned text
 * @return  int		    Response code as defined in lib-plugins.php
 */
function service_get_story($args, &$output, &$svc_msg)
{
    global $_CONF, $_TABLES, $_USER;

    $output = array();
    $svc_msg = array();
    $retval = '';

    if (!isset($_CONF['atom_max_stories'])) {
        $_CONF['atom_max_stories'] = 10; // set a resonable default
    }

    $svc_msg['output_fields'] = array(
                                    'draft_flag',
                                    'hits',
                                    'numemails',
                                    'comments',
                                    'trackbacks',
                                    'featured',
                                    'commentcode',
                                    'statuscode',
                                    'expire_date',
                                    'postmode',
                                    'frontpage',
                                    'owner_id',
                                    'group_id',
                                    'perm_owner',
                                    'perm_group',
                                    'perm_members',
                                    'perm_anon'
                                     );

    if (empty($args['sid']) && !empty($args['id'])) {
        $args['sid'] = $args['id'];
    }

    if ($args['gl_svc']) {
        if (isset($args['mode'])) {
            $args['mode'] = COM_applyBasicFilter($args['mode']);
        }
        if (isset($args['sid'])) {
            $args['sid'] = COM_applyBasicFilter($args['sid']);
        }

        if (empty($args['sid'])) {
            $svc_msg['gl_feed'] = true;
        } else {
            $svc_msg['gl_feed'] = false;
        }
    } else {
        $svc_msg['gl_feed'] = false;
    }

    if (empty($args['mode'])) {
        $args['mode'] = 'view';
    }

    if (!$svc_msg['gl_feed']) {
        $sid = $args['sid'];
        $mode = $args['mode'];

        $story = new Story();

        $retval = $story->loadFromDatabase($sid, $mode);

        if ($retval != STORY_LOADED_OK) {
            $output = $retval;
            return PLG_RET_ERROR;
        }

        foreach ( $story->_dbFields AS $fieldname => $save ) {
            $varname = '_' . $fieldname;
            $output[$fieldname] = $story->{$varname};
        }
        $output['username'] = $story->_username;
        $output['fullname'] = $story->_fullname;

        if ($args['gl_svc']) {
            if (($output['statuscode'] == STORY_ARCHIVE_ON_EXPIRE) ||
                ($output['statuscode'] == STORY_DELETE_ON_EXPIRE)) {
                // This date format is PHP 5 only,
                // but only the web-service uses the value
                $output['expire_date']  = date('c', $output['expire']);
            }
            $output['id']           = $output['sid'];
            $output['category']     = array($output['tid']);
            $output['published']    = date('c', $output['date']);
            $output['updated']      = date('c', $output['date']);
            if (empty($output['bodytext'])) {
                $output['content']  = $output['introtext'];
            } else {
                $output['content']  = $output['introtext'] . LB
                                    . '[page_break]' . LB . $output['bodytext'];
            }
            $output['content_type'] = ($output['postmode'] == 'html')
                                    ? 'html' : 'text';

            $owner_data = SESS_getUserDataFromId($output['owner_id']);

            $output['author_name']  = $owner_data['username'];

            $output['link_edit'] = $sid;
        }
    } else {
        $output = array();

        $mode = $args['mode'];

        if (isset($args['offset'])) {
            $offset = COM_applyBasicFilter($args['offset'], true);
        } else {
            $offset = 0;
        }
        $max_items = $_CONF['atom_max_stories'] + 1;

        $limit = " LIMIT $offset, $max_items";
        $order = " ORDER BY unixdate DESC";

        $sql
        = "SELECT STRAIGHT_JOIN s.*, UNIX_TIMESTAMP(s.date) AS unixdate, UNIX_TIMESTAMP(s.expire) as expireunix, "
            . "u.username, u.fullname, u.photo, u.email, p.about, p.uid, t.topic, t.imageurl " . "FROM {$_TABLES['stories']} AS s, {$_TABLES['users']} AS u, {$_TABLES['userinfo']} AS p, {$_TABLES['topics']} AS t " . "WHERE (s.uid = u.uid) AND (s.uid = p.uid) AND (s.tid = t.tid)" . COM_getPermSQL('AND', $_USER['uid'], 2, 's') . $order . $limit;
        $result = DB_query($sql);

        $count = 0;

        while (($story_array = DB_fetchArray($result, false)) !== false) {

            $count += 1;
            if ($count == $max_items) {
                $svc_msg['offset'] = $offset + $_CONF['atom_max_stories'];
                break;
            }

            $story = new Story();

            $story->loadFromArray($story_array);

            // This access check is not strictly necessary
            $access = SEC_hasAccess($story_array['owner_id'], $story_array['group_id'], $story_array['perm_owner'], $story_array['perm_group'],
                                $story_array['perm_members'], $story_array['perm_anon']);
            $story->_access = min($access, SEC_hasTopicAccess($story->_tid));

            if ($story->_access == 0) {
                continue;
            }

            $story->_sanitizeData();

            $output_item = array ();

            foreach ( $story->_dbFields AS $fieldname => $save ) {
                $varname = '_' . $fieldname;
                $output_item[$fieldname] = $story->{$varname};
            }

            if ($args['gl_svc']) {
                if (($output_item['statuscode'] == STORY_ARCHIVE_ON_EXPIRE) ||
                    ($output_item['statuscode'] == STORY_DELETE_ON_EXPIRE)) {
                    // This date format is PHP 5 only,
                    // but only the web-service uses the value
                    $output_item['expire_date']  = date('c', $output_item['expire']);
                }
                $output_item['id']           = $output_item['sid'];
                $output_item['category']     = array($output_item['tid']);
                $output_item['published']    = date('c', $output_item['date']);
                $output_item['updated']      = date('c', $output_item['date']);
                if (empty($output_item['bodytext'])) {
                    $output_item['content']  = $output_item['introtext'];
                } else {
                    $output_item['content']  = $output_item['introtext'] . LB
                            . '[page_break]' . LB . $output_item['bodytext'];
                }
                $output_item['content_type'] = ($output_item['postmode'] == 'html') ? 'html' : 'text';

                $owner_data = SESS_getUserDataFromId($output_item['owner_id']);

                $output_item['author_name']  = $owner_data['username'];
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
function service_getTopicList_story($args, &$output, &$svc_msg)
{
    $output = COM_topicArray('tid');

    return PLG_RET_OK;
}

/*
 * END SERVICES SECTION
 */

?>