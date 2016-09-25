<?php
/**
 * @package    glFusion CMS
 *
 * @copyright   Copyright (C) 2014-2016 by the following authors
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
        // display = how many stories to display, if 0, then all
        // meta = show meta data (i.e.; who when etc)
        // titleLink - make title a hot link
        // featured - 0 = show all, 1 = only featured, 2 = all except featured
        // frontpage - 1 = show only items marked for frontpage - 0 = show all
        // cols - number of columns to show
        // template - the template name

        $topic = $p1;
        if ( $topic == 'all' ) $topic = '';
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
        $template   = 'headlines.thtml';

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

        if ( $storyimage != 0 && $storyimage != 1 && $storyimage != 2 ) $storyimage = 2;

        $hash = CACHE_security_hash();
        $instance_id = 'whatsnew_headlines_'.$uniqueID.'_'.$hash.'_'.$_USER['theme'];

        if ( ($cache = CACHE_check_instance($instance_id, 0)) !== FALSE ) {
            return $cache;
        }

        $archivetid = DB_getItem ($_TABLES['topics'], 'tid', "archive_flag=1");

        $sql = " (date <= NOW()) AND (draft_flag = 0)";
        if (empty ($topic)) {
            $sql .= COM_getLangSQL ('tid', 'AND', 's');
        }
        // if a topic was provided only select those stories.
        if (!empty($topic)) {
            $sql .= " AND s.tid = '".DB_escapeString($topic)."' ";
        }

        if ( $featured == 1) {
            $sql .= " AND s.featured = 1 ";
        } else if ( $featured == 2 ) {
            $sql .= " AND s.featured = 0 ";
        }

        if ( $frontpage == 1 ) {
            $sql .= " AND frontpage = 1 ";
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

        $orderBy = ' date DESC ';
        $headlinesSQL = "SELECT STRAIGHT_JOIN s.*, UNIX_TIMESTAMP(s.date) AS unixdate, "
                 . 'UNIX_TIMESTAMP(s.expire) as expireunix, '
                 . $userfields . ", t.topic, t.imageurl "
                 . "FROM {$_TABLES['stories']} AS s, {$_TABLES['users']} AS u, "
                 . "{$_TABLES['topics']} AS t WHERE (s.uid = u.uid) AND (s.tid = t.tid) AND"
                 . $sql . "ORDER BY featured DESC," . $orderBy;

        if ($display > 0 ) {
            $headlinesSQL .= " LIMIT ".$display;
        }

        $result = DB_query ($headlinesSQL);
        $numRows = DB_numRows($result);

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
            while ( $A = DB_fetchArray($result) ) {
                $T->unset_var('readmore_url');
                $T->unset_var('lang_readmore');

                if ( $A['attribution_author'] != '' ){
                    $author = $A['attribution_author'];
                } else {
                    $author = $A['username'];
                }

                $title = COM_undoSpecialChars( $A['title'] );
                $title = str_replace('&nbsp;',' ',$title);
                $subtitle = COM_undoSpecialChars($A['subtitle']);
                if ( $A['story_image'] != '' ) {
                    $story_image = $_CONF['site_url'].$A['story_image'];
                } else {
                    $story_image = '';
                }

                $A['introtext'] = STORY_renderImages($A['sid'], $A['introtext']);

                if ( !empty($A['bodytext']) ) {
                    $closingP = strrpos($A['introtext'], "</p>");
                    if ( $closingP !== FALSE ) {
                        $text = substr($A['introtext'],0,$closingP);
                        $A['introtext'] = $text;
                    }
                    // adds the read more link
                    $T->set_var('readmore_url',COM_buildUrl($_CONF['site_url'].'/article.php?story='.$A['sid']));
                    $T->set_var('lang_readmore',$LANG01['continue_reading']);
                }

                if ( $truncate > 0 ) {
                    $A['introtext'] = $this->truncateHTML($A['introtext'], $truncate,'...');
                }

                $topicurl = $_CONF['site_url'] . '/index.php?topic=' . $A['tid'];
                $dt->setTimestamp($A['unixdate']);

                if ( $A['commentcode'] >= 0 ) {
                    $cmtLinkArray = CMT_getCommentLinkWithCount( 'article', $A['sid'], $_CONF['site_url'].'/article.php?story=' . $A['sid'],$A['comments'],1);

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
                    'titlelink'         => ($titleLink ? TRUE : ''),
                    'meta'              => ($meta ? TRUE : ''),
                    'lang_by'           => $LANG01[1],
                    'lang_posted_in'    => $LANG01['posted_in'],
                    'story_topic_url'   => $topicurl,
                    'title'             => $title,
                    'subtitle'          => $subtitle,
                    'story_image'       => $story_image,
                    'text'              => PLG_replaceTags($A['introtext']),
                    'date'              => $A['date'],
                    'time'              => $dt->format('Y-m-d',true).'T'.$dt->format('H:i:s',true),
                    'topic'             => $A['topic'],
                    'tid'               => $A['tid'],
                    'author'            => $author, // $A['username'],
                    'author_id'         => $A['uid'],
                    'sid'               => $A['sid'],
                    'short_date'        => $dt->format($_CONF['shortdate'],true),
                    'date_only'         => $dt->format($_CONF['dateonly'],true),
                    'date'              => $dt->format($dt->getUserFormat(),true),
                    'url'               => COM_buildUrl($_CONF['site_url'] . '/article.php?story='.$A['sid']),
                    'attribution_url'   => $A['attribution_url'],
                    'attribution_name'  => $A['attribution_name'],
                ));
                $T->parse('hl','headlines',true);
            }
            $retval = $T->finish($T->parse('output','page'));
            CACHE_create_instance($instance_id, $retval, 0);
        }
        return $retval;
    }

    // adapted from http://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
    public static function truncateHTML($str, $len, $end = '&hellip;')
    {

        if ( utf8_strlen($str) <= $len ) return $str;

        $tagPattern = '/(<\/?)([\w]*)(\s*[^>]*)>?|&[\w#]+;/i';  //match html tags and entities
        preg_match_all($tagPattern, $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER );

        $i = 0;
        $closeTagString = '';

        while ( @$matches[$i][0][1] < $len && !empty($matches[$i]) ) {

            $len = $len + strlen($matches[$i][0][0]);
            if (utf8_substr($matches[$i][0][0],0,1) == '&' ) {
                $len = $len-1;
            }

            //if $matches[$i][2] is undefined then its an html entity, want to ignore those for tag counting
            //ignore empty/singleton tags for tag counting
            if (!empty($matches[$i][2][0]) && !in_array($matches[$i][2][0],array('br','img','hr', 'input', 'param', 'link'))) {
                if ( utf8_substr($matches[$i][3][0],-1 ) !='/' && utf8_substr( $matches[$i][1][0],-1 ) != '/') {
                    $openTags[] = $matches[$i][2][0];
                } elseif( end($openTags) == $matches[$i][2][0] ) {
                    array_pop($openTags);
                } else {
                    $warnings[] = "html has some tags mismatched in it:  $str";
                }
            }
            $i++;
        }

        $closeTags = '';

        if (!empty($openTags)) {
            $openTags = array_reverse($openTags);
            foreach ($openTags as $t){
                $closeTagString .="</".$t . ">";
            }
        }

        if (utf8_strlen($str)>$len ) {
            // Finds the last space from the string new length
            $lastWord = utf8_strpos($str, ' ', $len);
            if ($lastWord) {
                //truncate with new len last word
                $str = utf8_substr($str, 0, $lastWord);
                //finds last character
                $last_character = (utf8_substr($str, -1, 1));
                //add the end text
                $truncated_html = ($last_character == '.' ? $str : ($last_character == ',' ? utf8_substr($str, 0, -1) : $str) . $end);
            }
            //restore any open tags
            $truncated_html .= $closeTagString;
        } else {
            $truncated_html = $str;
        }
        return $truncated_html;
    }
}
?>