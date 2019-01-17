<?php
/**
* glFusion CMS
*
* glFusion Article Display Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
* @package  glFusion
* @access   public
*/

namespace glFusion\Article;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Article\Article;
use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Formatter;
use \glFusion\Log\Log;

class ArticleDisplay extends Article
{
    /*
     * private $template - story template
     */
    private $template = NULL;

    /*
     * private $rateIds
     *   all stories the current user has rated
     */
    private $rateIds = NULL;

    /*
     * Constructor
     */
    public function __construct()
    {
        global $_CONF;

        parent::__construct();
        $this->template = new \Template($_CONF['path_layout']);

        // setup formatter class for this story

        $this->format = new Formatter();
        $this->format->setNamespace('glfusion');
        $this->format->setAction('story');
        $this->format->setAllowedHTML($_CONF['htmlfilter_story']);
        $this->format->setParseAutoTags(true);
        $this->format->setProcessBBCode(false);
        $this->format->setCensor(true);
        $this->format->setProcessSmilies(true);
        $this->format->setConvertPre(true);
        $this->format->addFilter(FILTER_POST,array($this,'processImageTags'));

        // setup filter / sanitization
        $this->filter = new \sanitizer();
        $this->filter->setPostmode('text'); // default to text - can override later

        $allowedElements = $this->filter->makeAllowedElements($_CONF['htmlfilter_story']);
        $this->filter->setAllowedElements($allowedElements);
        $this->filter->setCensorData(true);
        $this->filter->setNamespace('glfusion','story');
    }

    /**
     * getDisplayItem return display ready data
     *
     * This method does the heavy lifting of processing all
     * story elements for display.  This method SHOULD NOT
     * be used to retrieve data for processing, in that case
     * use the get() method.
     *
     * @param  mixed $varname - variable to retrieve
     * @return mixed|null
     * @see    get()
     */
    public function getDisplayItem($varname)
    {
        global $_CONF;

        /*
         * several data elements have more descriptive aliases
         * that are used throughout...
         */

        switch ($varname) {
            case 'sid' :
            case 'story_id' :
                return (string) $this->sid;
                break;

            case 'uid' :
            case 'story_author_id' :
                return (int) $this->uid;
                break;

            case 'draft_flag' :
                return (int) $this->draft_flag;
                break;

            case 'tid' :
                return (string) $this->tid;
                break;

            case 'alternate_tid' :
                return $this->alternate_tid;
                break;

            case 'story_image' :
            case 'story_image_url' :
                return $this->story_image;
                break;

            case 'story_video' :
            case 'story_video_url' :
                return $this->story_video;
                break;

            case 'sv_autoplay' :
            case 'story_video_autoplay' :
                return $this->sv_autoplay;
                break;

            case 'date' :
            case 'story_publish_date' :
                return $this->dt->format($this->dt->getUserFormat(),true);
                break;

            case 'iso8601_date' :
            case 'story_publish_date_iso8601' :
                return $this->dt->toISO8601(true);
                break;

            case 'unixdate' :
            case 'story_publish_date_unix' :
                return $this->dt->toUnix();
                break;

            case 'title' :
            case 'story_title' :
                return $this->filter->htmlspecialchars($this->title);
                break;

            case 'subtitle' :
            case 'story_subtitle' :
                return $this->filter->htmlspecialchars($this->subtitle);
                break;

            case 'introtext' :
            case 'bodytext' :
                $this->format->setType($this->postmode);
                return $this->format->parse($this->{$varname});
                break;

            case 'hits' :
            case 'story_views' :
                return COM_numberFormat($this->hits,0);
                break;

            case 'rating' :
            case 'story_rating' :
                return (int) $this->rating;
                break;

            case 'votes' :
            case 'story_votes' :
                return (int) COM_numberFormat($this->votes,0);
                break;

            case 'numemails' :
            case 'story_emailed_count' :
                return COM_numberFormat($this->numemails,0);
                break;

            case 'comments' :
            case 'story_comment_count' :
                return COM_numberFormat($this->comments,0);
                break;

            case 'comment_expire' :
            case 'comment_expire_date' :
                $dtCmtExpire = new \Date($this->comment_expire,$_USER['tzid']);
                return $dtCmtExpire->format($dtCmtExpire->getUserFormat(),true);
                break;

            case 'comment_expire_date_unix' :
                $dtCmtExpire = new \Date($this->comment_expire,$_USER['tzid']);
                return $dtCmtExpire->toUnix();
                break;

            case 'trackbacks' :
            case 'story_trackback_count' :
                return COM_numberFormat($this->trackbacks,0);
                break;

            case 'related' :
            case 'story_whats_related' :
                return $this->related;
                break;

            case 'featured' :
            case 'story_featured' :
                return (int) $this->featured;
                break;

            case 'show_topic_icon' :
                return (int) $this->show_topic_icon;
                break;

            case 'commentcode' :
                return (int) $this->commentcode;
                break;

            case 'trackbackcode' :
                return (int) $this->trackbackcode;
                break;

            case 'statuscode' :
                return (int) $this->statuscode;
                break;

            case 'expire' :
                return $this->expire;
                break;

            case 'attribution_url' :
                return $this->filter->htmlspecialchars($this->attribution_url);
                break;

            case 'attribution_name' :
                return $this->filter->htmlspecialchars($this->attribution_name);
                break;

            case 'attribution_author' :
                return $this->filter->htmlspecialchars($this->attribution_author);
                break;

            case 'postmode' :
                return $this->postmode;
                break;

            case 'advanced_editor_mode' :  // we want to use this as a true / false to trigger showing the visual editor
                return $this->advanced_editor_mode;
                break;

            case 'frontpage' :
            case 'show_on_frontpage' :
                return $this->frontpage;
                break;

            case 'frontpage_date' :
                return $this->frontpage_date;
                break;

            case 'owner_id' :
                return (int) $this->owner_id;
                break;

            case 'group_id' :
                return (int) $this->group_id;
                break;

            case 'perm_owner' :
                return (int) $this->perm_owner;
                break;

            case 'perm_group' :
                return (int) $this->perm_group;
                break;

            case 'perm_members' :
                return (int) $this->perm_members;
                break;

            case 'perm_anon' :
                return $this->filter->htmlspecialchars($this->{$varname});
                break;

            //
            // Dynamic fields used to display a story - not part of the story record

            case 'rating_bar' :
                return $this->getRatingBar();
                break;

            case 'topic_url' :
                return $_CONF['site_url'] . '/index.php?topic=' . urlencode($this->tid);
                break;

            case 'alttopic_url' :
                return $_CONF['site_url'] . '/index.php?topic=' . urlencode($this->alternate_tid);
                break;

            case 'topic' :
                return $this->filter->htmlspecialchars($this->topic);
                break;

            case 'alternate_topic' :
                return $this->filter->htmlspecialchars($this->altTopic);
                break;

            case 'topic_imageurl' :
                return $this->topicImage;
                break;

            case 'topic_description_text' :
                $html2txt = new \Html2Text\Html2Text($this->topicDescription);
                return trim($html2txt->get_text());
                break;

            case 'comments_url' :
                return COM_buildUrl($_CONF['site_url'].'/article.php?story=' . urlencode($this->sid)) . '#comments';
                break;

            case 'author_fullname' :
                if ($_CONF['show_fullname'] == 1) {
                    return $this->fullname;
                } else {
                    return $this->username;
                }
                break;

            case 'trackback_url' :
                return COM_buildUrl($_CONF['site_url'].'/article.php?story='.urlencode($this->sid)).'#trackback';
                break;

            case 'email_story_url' :
                return $_CONF['site_url'].'/profiles.php?sid='.urlencode($this->sid).'&amp;what=emailstory';
                break;

            case 'print_story_url' :
                return COM_buildUrl($_CONF['site_url'].'/article.php?story='.urlencode($this->sid).'&amp;mode=print');
                break;

            case 'feed_url' :
                return $this->getFeedUrl();
                break;

            case 'edit_url' :
                if (SEC_hasRights('story.edit') && ($this->getAccess() == 3) &&
                   (SEC_hasTopicAccess($this->tid) == 3)) {
                    return $_CONF['site_admin_url'].'/story.php?edit=x&amp;sid='.urlencode($this->sid);
                }
                return '';
                break;

            case 'author_photo' :
                return $_CONF['site_url'].'/images/userphotos/'.$this->user_photo;
                break;

            case 'about' :
                return PLG_replaceTags(nl2br($this->user_about),'glfusion','about_user');
                break;

            default :
                // we have not explictly set it up - return blank
                return '';
                break;
        }
    }


    /**
     * render - formats the full story for display
     *
     * @param  string  $displayType - Index 'y' or full article 'n' or preview 'p'
     * @param  string  $tpl - Template to use
     * @return string  fully formatted story ready for display
     */
    public function getDisplayArticle($displayType = 'n', $tpl='article-standard.thtml')
    {
        global $_CONF, $_SYSTEM, $_TABLES, $_USER,
                $LANG01, $LANG05, $LANG11, $LANG_TRB, $_IMAGE_TYPE, $_GROUPS;

        USES_lib_social();

        static $storycounter = 0;

        $topic = \TOPIC::currentID();

        // determine template / display type /
        $article_filevar = 'article';
        if (empty($tpl)) {
            if ($this->isFeatured()) {
                $tpl = 'featuredstorytext.thtml';
                $article_filevar = 'featuredarticle';
            } elseif ($this->statuscode == $this::STORY_ARCHIVE_ON_EXPIRE
                        && $this->get('comment_expire_date_unix') <= time()) {
                $tpl = 'archivestorytext.thtml';
                $article_filevar = 'archivearticle';
            } else {
                $tpl = 'storytext.thtml';
                $article_filevar = 'article';
            }
        }

        if (isset($_SYSTEM['custom_topic_templates']) && $_SYSTEM['custom_topic_templates'] == true ) {
            if ($topic != '') {
                $storyTid = $this->filter->sanitizeFilename(strtolower($topic));
            } else {
                $storyTid = $this->filter->sanitizeFilename(strtolower($this->tid));
            }
            $pos = strpos($tpl,".");
            if ( $pos !== false ) {
                $base_template = substr($tpl,0,$pos);
            } else {
                $base_template = 'storytext';
            }
            if (file_exists($_CONF['path_layout'].'/custom/'.$base_template.'_'.$storyTid.'.thtml') !== false) {
                $tpl = $base_template.'_'.$storyTid.'.thtml';
            }
        }

        $templateHash = substr($tpl,0,strpos($tpl,'.'));

        $this->template->set_file('article', $tpl);

        ## begin processing data

        $introtext = $this->getDisplayItem('introtext');
        if ($displayType != 'y') {
            $bodytext  = $this->getDisplayItem('bodytext');
        } else {
            $bodytext = '';
        }

        # - AdBlock / display info for template
        switch ($displayType) {
            case 'p' :
                $story_display = 'preview';
                $this->template->set_var( 'story_counter', 0 );
                break;
            case 'n' :
                $story_display = 'article';
                $this->template->set_var('story_counter', 0 );
                $this->template->set_var('adblock_content',PLG_displayAdBlock('article',0), false, true);
                $this->template->set_var('breadcrumbs',true);
                break;
            case 'y' :
            default :
                $story_display = 'index';
                $storycounter++;
                $this->template->set_var('story_counter', $storycounter, false, true );
                $this->template->set_var('adblock',PLG_displayAdBlock('article',$storycounter), false, true);
                break;
        }
        $this->template->set_var( 'story_display', $story_display, false, true );

# - dynamic meta data fields (views and comments)

        if ($_CONF['hideviewscount'] != 1) {
            $this->template->set_var(array(
                'lang_views' => $LANG01[106],
                'story_hits' => $this->getDisplayItem('hits')
            ),'',false,true);
        }

        if ($this->get('commentcode') >= 0
                && $displayType != 'n'
                && $displayType != 'p') {
            $commentsUrl = $this->getDisplayItem('comments_url');

            $cmtLinkArray = CMT_getCommentLinkWithCount(
                                'article',
                                $this->get('sid'),
                                $_CONF['site_url'].'/article.php?story='.urlencode($this->get('sid')),
                                $this->get('story_comment_count'),
                                true
                            );

            $this->template->set_var(array(
                'comments_with_count_link'  => $cmtLinkArray['link_with_count'],
                'comments_url'              => $cmtLinkArray['url'],
                'comments_url_extra'        => $cmtLinkArray['url_extra'],
                'comments_text'             => $cmtLinkArray['comment_count'],
                'comments_count'            => $cmtLinkArray['comment_count'],
                'lang_comments'             => $LANG01[3],
            ),'',false,true);

            $comments_with_count = sprintf( $LANG01[121], $this->getDisplayItem('story_comment_count'));

            if ($this->commentcode == 0 && ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
                $postCommentUrl = $_CONF['site_url'] . '/comment.php?sid='.urlencode($this->get('sid')) . '&amp;pid=0&amp;type=article#comment_entry';
                $this->template->set_var( 'post_comment_link',
                    COM_createLink($LANG01[60], $postCommentUrl,
                        array('rel' => 'nofollow')));
                $this->template->set_var( 'lang_post_comment', $LANG01[60] );
                $this->template->set_var( 'post_comment_url', $postCommentUrl);
            }
        }

# - Trackbacks

        if ($_CONF['trackback_enabled'] && $displayType != 'n' && $displayType != 'p') {

            $this->template->set_var(array(
                'trackback_url'     => $this->getDisplayItem('trackback_url'),
                'trackback_text'    => $this->getDisplayItem('trackbacks').' '.$LANG_TRB['trackbacks'],
            ));
        }

# - Ratings
        if ($_CONF['rating_enabled'] != 0 && $displayType != 'p') {
            $this->template->set_var(
                'rating_bar',
                $this->getDisplayItem('rating_bar'),
                false,
                true
            );
        } else {
            $this->template->unset_var('rating_bar');
        }

# - Topic URL - dynamic as it is a user preference

        if ($this->showTopicIcon()) {
            $imageurl = $this->getDisplayItem('topic_imageurl');
            if (!empty($imageurl)) {
                $this->template->set_var(
                    'story_topic_image_url',
                    $imageurl,
                    false,
                    true
                );
            }
        }

        $this->template->set_var(array(
            'story_date'    => $this->getDisplayItem('story_publish_date'),
            'iso8601_date'  => $this->getDisplayItem('story_publish_date_iso8601')
        ),'',false,true);

        $alttopic = '';
        $alttopic_description = '';

        $topic_description = $this->getDisplayItem('topic_description');
        if ($this->alternate_tid  != NULL) {
            $alttopic = $this->getDisplayItem('alternate_topic');
            $alttopic_description = $this->getDisplayItem('alternate_topic_description');
        }

        $this->template->set_var(array(
            'story_topic_url'           => $this->getDisplayItem('topic_url'),
            'alt_story_topic_url'       => $this->getDisplayItem('alttopic_url'),
            'story_topic_id'            => $this->getDisplayItem('tid'),
            'alt_story_topic_id'        => $this->getDisplayItem('alternate_tid'),
            'story_topic_name'          => $this->getDisplayItem('topic'),
            'story_alternate_topic_name'=> $this->getDisplayItem('alternate_topic'),
            'story_topic_description'   => $this->getDisplayItem('topic_description'),
            'story_alternate_topic_description' => $this->getDisplayItem('alternate_topic_description'),
            'story_topic_description_text' => $this->getDisplayItem('topic_description_text'), // text version
            'lang_posted_in'            => $LANG01['posted_in']
         ));

## basic stuff like SID, article permalink, title
        $articleUrl = COM_buildUrl($_CONF['site_url'] . '/article.php?story='.urlencode($this->sid));

        $this->template->set_var(array(
            'story_id'      =>  $this->get('sid'),
            'article_url'   =>  $articleUrl,
            'story_title'   =>  $this->getDisplayItem('title'),
            'story_subtitle'=>  $this->getDisplayItem('subtitle')
        ));

# - Finally set the actual story contents
        if (($displayType == 'n' ) || ( $displayType == 'p')) {  // full article view or preview
            if (empty($bodytext)) {
                $this->template->set_var( 'story_introtext', $introtext,false,true );
                $this->template->set_var( 'story_text_no_br', $introtext,false,true );
            } else {
                $this->template->set_var( 'story_introtext', $introtext .'<br>'.$bodytext,false,true );
                $this->template->set_var( 'story_text_no_br', $introtext . $bodytext,false,true );
            }
            $this->template->set_var( 'story_introtext_only', $introtext,false,true );
            $this->template->set_var( 'story_bodytext_only', $bodytext,false,true );
        } else {
            $this->template->set_var( 'story_introtext', $introtext,false,true );
            $this->template->set_var( 'story_text_no_br', $introtext,false,true );
            $this->template->set_var( 'story_introtext_only', $introtext,false,true );
        }

        // Allow topic to set vars prior to checking the cached stuff

        PLG_templateSetVars($article_filevar,$this->template);

        #
        # - Instance Caching..
        #

        $hash = CACHE_security_hash();
        $instance_id = 'story_'.$this->get('sid').'_'.$displayType.'_'.$article_filevar.'_'.$templateHash.'_'.$hash.'_'.$_USER['theme'];

        if ($displayType == 'p' || !$this->template->check_instance($instance_id,$article_filevar)) {

## - contributed info
            if ($_CONF['contributedbyline'] == 1) {
                $this->template->set_var(array(
                    'lang_contributed_by'   => $LANG01[1],
                    'lang_by'               => $LANG01[1],
                    'contributedby_author'  => $this->getDisplayItem('author_fullname'),
                ));

                if ($this->uid > 1) {
                    $this->template->set_var(
                        'contributedby_url',
                        $_CONF['site_url'].'/users.php?mode=profile&amp;uid='.urlencode($this->get('uid'))
                    );
                }

                if ($this->attribution_author == '') {
                    $this->template->set_var(array(
                        'author_about'  => $this->getDisplayItem('about'),
                        'follow_me'     => SOC_getFollowMeIcons( $this->get('uid'))
                    ));
                }
            }
            $this->template->set_var('author_photo_raw',$this->getDisplayItem('author_photo'));

## - attribution

            $attribution_url = $this->getDisplayItem('attribution_url');
            $attribution_name = $this->getDisplayItem('attribution_name');
            $attribution_author = $this->getDisplayItem('attribution_author');

            if (!empty($attribution_url)) {
                $this->template->set_var('attribution_url', $attribution_url);
            }
            if (!empty($attribution_name)) {
                $this->template->set_var('attribution_name', $attribution_name);
            }
            if (!empty($attribution_author)) {
                $this->template->set_var('attribution_author', $attribution_author);
            }

            $this->template->set_var('lang_source',$LANG01['source']);

## - story image / video
            $story_image = $this->getDisplayItem('story_image_url');
            $story_video = $this->getDisplayItem('story_video_url');
            $sv_autoplay = $this->get('story_video_autoplay');

            $this->template->set_var('story_image',$story_image);
            $this->template->set_var('story_video',$story_video);
            if ($sv_autoplay) {
                $this->template->set_var('autoplay',"autoplay");
            } else {
                $this->template->unset_var('autoplay');
            }

## - title link (or not)
## - Set meta data

            // compact view
            if ($displayType != 'n' && $displayType != 'p') {
                $this->template->set_var('story_url',$articleUrl);
                $this->template->set_var(array(
                    'email_story_url'   => $this->getDisplayItem('email_story_url'),
                    'print_story_url'   => $this->getDisplayItem('print_story_url'),
                    'edit_url'          => $this->getDisplayItem('edit_url'),
                    'feed_url'          => $this->getDisplayItem('feed_url'),
                ));
            } else {
                $this->template->set_var(array(
                    'email_story_url'   => $this->getDisplayItem('email_story_url'),
                    'edit_url'          => $this->getDisplayItem('edit_url'),
                ));
            }

# - create an instance cache - unless preview

            if ($displayType != 'p') {
                $this->template->create_instance($instance_id,$article_filevar);
            }

        }

# finalize the display

        $this->template->parse('finalstory','article');
        SESS_clearContext();
        $output = $this->template->finish($this->template->get_var('finalstory'));
        $output = PLG_outputFilter($output, 'article');
        unset($this->template);
        return $output;
    }

    /**
     * isFeatured - determines if article is featured or not
     *
     * @return bool  true if featured, false if not
     */
    public function isFeatured()
    {
        return (bool) ($this->featured == 1 ? true : false);
    }

    /**
     * getWhatRelated - Builds the What's Related section for story
     *
     * @param  string  $related - related DB field for story
     * @param  integer $uid - user ID viewing story
     * @return string
     */
    public function getWhatsRelated( $related, $uid, $tid, $atid = '' )
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
                $author = $this->getDisplayItem('author_fullname');
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
     * getRatingBar - return HTML for rating bar based on user
     *
     * @return string  Rating bar or blank if not enabled
     */
    private function getRatingBar()
    {
        global $_CONF, $_USER;

        if ($_CONF['rating_enabled'] == 0 ) {
            return '';
        }

        if ($this->rateIds === NULL) {
            $this->rateIds = RATING_getRatedIds('article');
        }

        if (@in_array($this->sid,$this->rateIds)) {
            $static = true;
            $voted = 1;
        } else {
            $static = 0;
            $voted = 0;
        }

        $uid = isset($_USER['uid']) ? $_USER['uid'] : 1;

        if ($static == 0) {
            // check if owner
            if ($_CONF['rating_enabled'] == 2 && $uid != $this->owner_id) {
                $static = 0;
            } elseif ( !COM_isAnonUser() && $uid != $this->owner_id) {
                $static = 0;
            } else {
                $static = true;
            }
        }
         return RATING_ratingBar(
                    'article',
                    $this->sid,
                    $this->get('votes'),
                    $this->get('rating'),
                    $voted,
                    5,
                    $static,
                    'sm'
                );

    }


    /**
     * showTopicIcon - Should topic icon be shown or not
     *
     * @return bool  true - show topic icon, false - do not show
     */
    private function showTopicIcon()
    {
        global $_USER;

        if ((!isset( $_USER['noicons'] ) || ( $_USER['noicons'] != 1 ))
                && $this->show_topic_icon == 1 ) {
            return true;
        }
        return false;
    }


    /**
     * getFeedUrl - returns feed URL for topic
     *
     * @return string  blank if no feed URL exists, or the actual URL
     */
    private function getFeedUrl()
    {
        global $_CONF, $_TABLES, $LANG11;

        $retval = '';

        $db = Database::getInstance();

        if ($_CONF['backend'] == 1) {

            // if multiple - return the most recent updated
            $sql = "SELECT filename,title,format
                    FROM `{$_TABLES['syndication']}`
                    WHERE type = 'article' AND topic = ? AND is_enabled = 1
                    ORDER BY updated DESC";

            $row = $db->conn->fetchAssoc(
                        $sql,
                        array($this->tid),
                        array(Database::STRING),
                        new \Doctrine\DBAL\Cache\QueryCacheProfile(3600, 'synd_'.$this->tid)
            );
            if ($row !== false && $row !== null) {
                $retval = SYND_getFeedUrl($row['filename']);
            }
        }
        return $retval;
    }

}