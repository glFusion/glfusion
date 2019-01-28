<?php
/**
* glFusion CMS
*
* glFusion Headlines Auto tag
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Article\Article;

class autotag_headlines extends BaseAutotag {

    function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['headlines']['description'];
    }

    public function parse($p1, $p2='', $fulltag)
    {
        global $_CONF, $_TABLES, $_USER, $LANG01;

        USES_lib_comment();

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

        $c = \glFusion\Cache::getInstance();
        $key = 'headlines__'.$uniqueID.'_'.$c->securityHash(true,true);
        if ( $c->has($key) ) {
            return $c->get($key);
        }

        // not cached - need to build it

        $db = Database::getInstance();

        $archivetid = $db->getItem($_TABLES['topics'], 'tid', array('archive_flag' => 1));

        $queryBuilder = $db->conn->createQueryBuilder();
        $queryBuilder
            ->select(   's.*',
                        'UNIX_TIMESTAMP(s.date) AS unixdate',
                        'UNIX_TIMESTAMP(s.expire) as expireunix',
                        'UNIX_TIMESTAMP(s.frontpage_date) as frontpage_date_unix',
                        'u.uid',
                        'u.username',
                        'u.fullname',
                        't.topic',
                        't.imageurl'
                    )
            ->from($_TABLES['stories'],'s')
            ->leftJoin('s',$_TABLES['users'],'u','s.uid=u.uid')
            ->leftJoin('s',$_TABLES['topics'],'t','s.tid=t.tid')
            ->where('date <= NOW()')
            ->andWhere('draft_flag = 0')
            ->orderBy($sortby, $orderby);

        if ($sortby == 'featured') {
            $queryBuilder->addOrderBy('date', 'DESC');
        }

        if (empty($topic)) {
            $sql = $db->getLangSQL ('tid', '', 's');
            if (!empty($sql)) {
                $queryBuilder->andWhere($sql);
            }
        }

        // if a topic was provided only select those stories.
        if (!empty($topic)) {
            switch ($include_alt) {
                case 1 :
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq('s.tid',
                              $queryBuilder->createNamedParameter($topic,Database::STRING)
                            ),
                            $queryBuilder->expr()->eq('s.alternate_tid',
                              $queryBuilder->createNamedParameter($topic,Database::STRING)
                            )
                        )
                    );
                    break;
                case 2 :
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('s.tid',
                              $queryBuilder->createNamedParameter($topic,Database::STRING)
                            ),
                            $queryBuilder->expr()->eq('s.alternate_tid',
                              $queryBuilder->createNamedParameter($topic,Database::STRING)
                            )
                        )
                    );
                    break;
                default :
                case 0 :
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->eq('s.tid',
                          $queryBuilder->createNamedParameter($topic,Database::STRING)
                        )
                    );
                    break;
            }
        }

        if ($topic != $archivetid) {
            $queryBuilder->andWhere('s.tid != ' .
              $queryBuilder->createNamedParameter($archivetid,Database::STRING)
            );
        }

        if ( $featured == 1) {
            $queryBuilder->andWhere('s.featured = 1');
        } else if ( $featured == 2 ) {
            $queryBuilder->andWhere('s.featured = 0');
        }

        if ( $frontpage == 1 ) {
            $queryBuilder->andWhere('frontpage = 1 OR (frontpage = 2 AND frontpage_date >= '.
              $queryBuilder->createNamedParameter($_CONF['_now']->toMySQL(true),Database::STRING) . ')'
            );
        }

        if ( $storyimage != 2 ) {
            if ( $storyimage == 0 ) {
                $queryBuilder->andWhere('story_image = "")');
            } else {
                $queryBuilder->andWhere('story_image != "")');
            }
        }

        $db->qbGetPermSQL($queryBuilder,'',0,SEC_ACCESS_RO,'s');

        $db->qbGetTopicSQL($queryBuilder,'AND',0,'s');

        if ($display > 0 ) {
            $queryBuilder->setMaxResults($display);
        }

        //print $queryBuilder->getSQL();exit;

        try {
            $stmt = $queryBuilder->execute();
        } catch(\Doctrine\DBAL\DBALException $e) {
            if (defined('DVLP_DEBUG')) {
                throw($e);
            }
            $stmt = false;
        }

        $storyRecords = array();
        if ($stmt) {
            $storyRecords = $stmt->fetchAll();
            $stmt->closeCursor();
        }
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

                $story = new Article();
                if ($story->retrieveArticleFromVars($A) != $story::STORY_LOADED_OK) {
                    continue;
                }

                if ($story->get('attribution_author') != '' ) {
                    $author = $story->getDisplayItem('attribution_author');
                } else {
                    $author = $story->getDisplayItem('author_fullname');
                }

                $A['introtext'] = $story->getDisplayItem('introtext');
                $A['bodytext']  = $story->getDisplayItem('bodytext');
                $title = $story->getDisplayItem('title');
                $subtitle = $story->getDisplayItem('subtitle');

                if ($story->get('story_image') != '') {
                    $story_image = $_CONF['site_url'].$story->getDisplayItem('story_image_url');
                } else {
                    $story_image = '';
                }

                if ( $story->hasBody() ) {
                    $readMore = true;

                    // adds the read more link
                    $T->set_var('readmore_url',COM_buildUrl($_CONF['site_url'].'/article.php?story='.urlencode($story->get('sid'))));
                    $T->set_var('lang_readmore',$LANG01['continue_reading']);
                }

                if ( $truncate > 0 ) {
                    $truncatedArticle = COM_truncateHTML($A['introtext'], $truncate,'...');

                    if ( $readMore == false && utf8_strlen($A['introtext']) != utf8_strlen($truncatedArticle) ) {
                        // adds the read more link
                        $T->set_var('readmore_url',COM_buildUrl($_CONF['site_url'].'/article.php?story='.urlencode($story->get('sid'))));
                        $T->set_var('lang_readmore',$LANG01['continue_reading']);
                    }
                    $A['introtext'] = $truncatedArticle;
                }

                $topicurl = $_CONF['site_url'] . '/index.php?topic=' . urlencode($story->get('sid'));
                $dt->setTimestamp($A['unixdate']);

                if ( $story->get('commentcode') >= 0 ) {
                    $cmtLinkArray = CMT_getCommentLinkWithCount(
                                        'article',
                                        $A['sid'],
                                        $_CONF['site_url'].'/article.php?story=' . urlencode($story->get('sid')),
                                        $story->get('comments'),
                                        1
                                   );

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
                    'attribution_name'  => $story->getDisplayItem('attribution_name'),
                    'attribution_url'   => $story->getDisplayItem('attribution_url'),
                    'author'            => $author,
                    'author_id'         => $story->get('uid'),
                    'date'              => $dt->format($dt->getUserFormat(),true),
                    'date_only'         => $dt->format($_CONF['dateonly'],true),
                    'lang_by'           => $LANG01[1],
                    'lang_posted_in'    => $LANG01['posted_in'],
                    'meta'              => ($meta ? TRUE : ''),
                    'short_date'        => $dt->format($_CONF['shortdate'],true),
                    'sid'               => $story->get('sid'),
                    'story_image'       => $story_image,
                    'story_topic_url'   => $topicurl,
                    'subtitle'          => $subtitle,
                    'text'              => $A['introtext'],
                    'tid'               => $story->getDisplayItem('tid'),
                    'time'              => $dt->format('Y-m-d',true).'T'.$dt->format('H:i:s',true),
                    'title'             => $title,
                    'titlelink'         => ($titleLink ? TRUE : ''),
                    'topic'             => $story->getDisplayItem('topic'),
                    'url'               => COM_buildUrl($_CONF['site_url'] . '/article.php?story='.urlencode($story->get('sid'))),
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