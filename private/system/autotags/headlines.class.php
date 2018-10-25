<?php
/**
 * @package    glFusion CMS
 *
 * @copyright   Copyright (C) 2014-2018 by the following authors
 *              Mark R. Evans          mark AT glfusion DOT org
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class autotag_headlines extends BaseAutotag {

    function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['headlines']['description'];
    }

    public function parse($p1, $p2='', $fulltag)
    {
        global $_CONF, $_TABLES, $_USER, $LANG01;

        USES_lib_comments();

        $retval = '';
        $skip = 0;

        $dt  = new Date('now',$_USER['tzid']);

        // topic = specific topic or 'all'
        //
        // display = how many stories to display, if 0, then all
        // meta = show meta data (i.e.; who when etc)
        // titleLink - make title a hot link
        // featured - 0 = show all, 1 = only featured, 2 = all except featured
        // frontpage - 1 = show only items marked for frontpage - 0 = show all
        // cols - number of columns to show
        // sort - sort by date, views, rating, featured (implies date)
        // order - desc, asc
        // template - the template name

        $topic = $p1;

        if ( $topic == 'all' ) {
            $topic = '';
        }

        $uniqueID = md5($p1.$p2);

        $display    = 10;       // display 10 articles
        $meta       = 0;        // do not display meta data
        $titleLink  = 0;        // do not use links in title
        $featured   = 0;        // 0 = show all, 1 = only featured, 2 = all except featured
        $frontpage  = 0;        // only show items marked for frontpage
        $cols       = 3;        // number of columns
        $truncate   = 0;        // maximum number of characters to include in story text
        $storyimage = 2;        // display stories with / without story images
                                // 0 = display those without
                                // 1 = display those with
                                // 2 - don't care - just pull all stories
        $sortby     = 'featured';  // sort by: date, views, rating, featured
        $orderby    = 'desc';   // order by - desc or asc
        $template   = 'headlines.thtml';
        $include_alt = 0;       // search both primary topic and alternate topics if true

        $px = explode (' ', trim ($p2));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 8) == 'display:') {
                    $a = explode (':', $part);
                    $display = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 5) == 'meta:') {
                    $a = explode (':', $part);
                    $meta = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 10) == 'titlelink:') {
                    $a = explode(':', $part);
                    $titleLink = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 9) == 'featured:') {
                    $a = explode(':', $part);
                    $featured = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 10) == 'frontpage:') {
                    $a = explode(':', $part);
                    $frontpage = (int) $a[1];
                    $skip++;
                } elseif (substr ($part,0, 5) == 'cols:') {
                    $a = explode(':', $part);
                    $cols = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 9) == 'template:') {
                    $a = explode(':', $part);
                    $template = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 9) == 'truncate:') {
                    $a = explode(':', $part);
                    $truncate = (int) $a[1];
                    $skip++;
                } elseif (substr ($part,0, 11) == 'storyimage:') {
                    $a = explode(':', $part);
                    $storyimage = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 5) == 'sort:') {
                    $a = explode(':', $part);
                    $sortby = strtolower($a[1]);
                    $skip++;
                } elseif (substr ($part,0, 9) == 'incl_alt:') {
                    $a = explode(':', $part);
                    $include_alt = (int) $a[1];
                    $skip++;
                } elseif (substr ($part,0, 6) == 'order:') {
                    $a = explode(':', $part);
                    $orderby = strtolower($a[1]);
                    $skip++;
                } else {
                    break;
                }
            }

            if ($skip != 0) {
                if (count ($px) > $skip) {
                    for ($i = 0; $i < $skip; $i++) {
                        array_shift ($px);
                    }
                    $caption = trim (implode (' ', $px));
                } else {
                    $caption = '';
                }
            }
        } else {
            $caption = trim ($p2);
        }
        if ( $display < 0 ) $display = 3;

        $valid_sortby = array('date','views','rating','featured');
        if ( !in_array($sortby,$valid_sortby)) {
            $sortby = 'featured';
        }
        if( $sortby == 'views' ) {
            $sortby = 'hits';
        }
        $valid_order = array('desc','asc');
        if ( !in_array($orderby,$valid_order)) {
            $orderby = 'desc';
        }

        if ( $storyimage != 0 && $storyimage != 1 && $storyimage != 2 ) {
            $storyimage = 2;
        }

        $c = glFusion\Cache::getInstance();
        $key = 'headlines__'.$uniqueID.'_'.$c->securityHash(true,true);
        if ( $c->has($key) ) {
            return $c->get($key);
        }

        $archivetid = DB_getItem ($_TABLES['topics'], 'tid', "archive_flag=1");

        $sql = " (date <= '".$_CONF['_now']->toMySQL(true)."') AND (draft_flag = 0)";
        if (empty ($topic)) {
            $sql .= COM_getLangSQL ('tid', 'AND', 's');
        }
        // if a topic was provided only select those stories.
        if (!empty($topic)) {
            $sql .= " AND (s.tid = '".DB_escapeString($topic)."' ";
            if ($include_alt) {
                $sql .= " OR s.alternate_tid = '".DB_escapeString($topic)."') ";
            } else {
                $sql .= ') ';
            }
        }

        if ( $featured == 1) {
            $sql .= " AND s.featured = 1 ";
        } else if ( $featured == 2 ) {
            $sql .= " AND s.featured = 0 ";
        }

        if ( $frontpage == 1 ) {
            $sql .= " AND ( frontpage = 1 OR ( frontpage = 2 AND frontpage_date >= '".$_CONF['_now']->toMySQL(true)."' ) ) ";
        }

        if ( $storyimage != 2 ) {
            if ( $storyimage == 0 ) {
                $sql .= " AND story_image = '' ";
            } else {
                $sql .= " AND story_image != '' ";
            }
        }

        if ($topic != $archivetid) {
            $sql .= " AND s.tid != '{$archivetid}' ";
        }

        $sql .= COM_getPermSQL ('AND', 0, 2, 's');

        $sql .= COM_getTopicSQL ('AND', 0, 's') . ' ';

        $userfields = 'u.uid, u.username, u.fullname';
        if ($_CONF['allow_user_photo'] == 1) {
            $userfields .= ', u.photo';
            if ($_CONF['use_gravatar']) {
                $userfields .= ', u.email';
            }
        }

        $sort_order = $sortby.' ' .$orderby.' ';

        if ( $sortby == 'featured' ) {
            $featuredOrderBy = 'featured ' . $orderby . ', ';
            $sort_order = 'date ' . $orderby.' ';
        } else {
            $featuredOrderBy = ' ';
        }

        $headlinesSQL = "SELECT STRAIGHT_JOIN s.*, UNIX_TIMESTAMP(s.date) AS unixdate, "
                 . 'UNIX_TIMESTAMP(s.expire) as expireunix, '
                 . $userfields . ", t.topic, t.imageurl "
                 . "FROM {$_TABLES['stories']} AS s, {$_TABLES['users']} AS u, "
                 . "{$_TABLES['topics']} AS t WHERE (s.uid = u.uid) AND (s.tid = t.tid) AND"
                 . $sql . "ORDER BY " . $featuredOrderBy . $sort_order;

        if ($display > 0 ) {
            $headlinesSQL .= " LIMIT ".$display;
        }

        $result  = DB_query ($headlinesSQL);
        $storyRecords = DB_fetchAll($result);
        $numRows = @count($storyRecords);

        if ( $numRows < $cols ) {
            $cols = $numRows;
        }
        if ( $cols > 6 ) {
            $cols = 6;
        }

        if ( $numRows > 0 ) {
            $T = new Template($_CONF['path'].'system/autotags/');
            $T->set_file('page',$template);
            $T->set_var('columns',$cols);
            $T->set_block('page','headlines','hl');

            $newstories = array();
            foreach ($storyRecords AS $A) {
                $readMore = false;
                $T->unset_var('readmore_url');
                $T->unset_var('lang_readmore');

                if ( $A['attribution_author'] != '' ){
                    $author = $A['attribution_author'];
                } else {
                    $author = $A['username'];
                }

                $story = new Story();
                $story->loadFromArray($A);
                $A['introtext'] = $story->displayElements('introtext');
                $A['bodytext']  = $story->displayElements('bodytext');
                $title = $story->displayElements('title');
                $subtitle = $story->displayElements('subtitle');

                if ( $A['story_image'] != '' ) {
                    $story_image = $_CONF['site_url'].$A['story_image'];
                } else {
                    $story_image = '';
                }

                if ( !empty($A['bodytext']) ) {
                    $readMore = true;

                    // adds the read more link
                    $T->set_var('readmore_url',COM_buildUrl($_CONF['site_url'].'/article.php?story='.$A['sid']));
                    $T->set_var('lang_readmore',$LANG01['continue_reading']);
                }

                if ( $truncate > 0 ) {
                    $truncatedArticle = COM_truncateHTML($A['introtext'], $truncate,'...');

                    if ( $readMore == false && utf8_strlen($A['introtext']) != utf8_strlen($truncatedArticle) ) {
                        // adds the read more link
                        $T->set_var('readmore_url',COM_buildUrl($_CONF['site_url'].'/article.php?story='.$A['sid']));
                        $T->set_var('lang_readmore',$LANG01['continue_reading']);
                    }
                    $A['introtext'] = $truncatedArticle;
                }

                $topicurl = $_CONF['site_url'] . '/index.php?topic=' . $A['tid'];
                $dt->setTimestamp($A['unixdate']);

                if ( $A['commentcode'] >= 0 ) {
                    $cmtLinkArray = CMT_getCommentLinkWithCount('article', $A['sid'], $_CONF['site_url'].'/article.php?story=' . $A['sid'],$A['comments'],1);

                    $T->set_var(array(
                        'lang_comments'     => '',
                        'comments_count'    => $cmtLinkArray['comment_count'],
                        'comments_url'      => $cmtLinkArray['url'],
                        'comments_url_extra'=> $cmtLinkArray['url_extra'],
                    ));
                } else {
                    $T->unset_var('lang_comments');
                    $T->unset_var('comments_count');
                    $T->unset_var('comments_url');
                    $T->unset_var('comments_url_extra');
                }

                $T->set_var(array(
                    'attribution_name'  => $A['attribution_name'],
                    'attribution_url'   => $A['attribution_url'],
                    'author'            => $author,
                    'author_id'         => $A['uid'],
                    'date'              => $dt->format($dt->getUserFormat(),true),
                    'date_only'         => $dt->format($_CONF['dateonly'],true),
                    'lang_by'           => $LANG01[1],
                    'lang_posted_in'    => $LANG01['posted_in'],
                    'meta'              => ($meta ? TRUE : ''),
                    'short_date'        => $dt->format($_CONF['shortdate'],true),
                    'sid'               => $A['sid'],
                    'story_image'       => $story_image,
                    'story_topic_url'   => $topicurl,
                    'subtitle'          => $subtitle,
                    'text'              => $A['introtext'],
                    'tid'               => $A['tid'],
                    'time'              => $dt->format('Y-m-d',true).'T'.$dt->format('H:i:s',true),
                    'title'             => $title,
                    'titlelink'         => ($titleLink ? TRUE : ''),
                    'topic'             => $A['topic'],
                    'url'               => COM_buildUrl($_CONF['site_url'] . '/article.php?story='.$A['sid']),
                ));
                $T->parse('hl','headlines',true);
                unset($story);
            }
            $retval = $T->finish($T->parse('output','page'));
            $c->set($key,$retval,array('whatsnew','story'));
        }
        return $retval;
    }
}
?>