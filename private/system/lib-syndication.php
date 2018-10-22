<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-syndication.php                                                      |
// |                                                                          |
// | glFusion syndication library.                                            |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2003-2010 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun        - dirk AT haun-online DOT de                   |
// |          Michael Jervis   - mike AT fuckingbrit DOT com                  |
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

// set to true to enable debug output in error.log
$_SYND_DEBUG = false;

if ($_CONF['trackback_enabled']) {
    USES_lib_trackback();
}
USES_lib_story();

/**
* Check if a feed for all stories needs to be updated.
*
* @param    boolean $frontpage_only true: only articles shown on the frontpage
* @param    string  $update_info    list of story ids
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_topic  (optional) topic to be updated
* @param    string  $updated_id     (optional) entry id to be updated
* @return   boolean                 false = feed needs to be updated
*
*/
function SYND_feedUpdateCheckAll( $frontpage_only, $update_info, $limit, $updated_topic = '', $updated_id = '' )
{
    global $_CONF, $_TABLES, $_SYND_DEBUG;

    $where = '';
    if( !empty( $limit ))
    {
        if( substr( $limit, -1 ) == 'h' ) // last xx hours
        {
            $limitsql = '';
            $hours = substr( $limit, 0, -1 );
            $where = " AND date >= DATE_SUB('".$_CONF['_now']->toMySQL(true)."',INTERVAL $hours HOUR)";
        }
        else
        {
            $limitsql = ' LIMIT ' . $limit;
        }
    }
    else
    {
        $limitsql = ' LIMIT 10';
    }

    // get list of topics that anonymous users have access to
    $tresult = DB_query( "SELECT tid FROM {$_TABLES['topics']}"
                         . COM_getPermSQL( 'WHERE', 1 ));
    $tnumrows = DB_numRows( $tresult );
    $topiclist = array();
    for( $i = 0; $i < $tnumrows; $i++ )
    {
        $T = DB_fetchArray( $tresult );
        $topiclist[] = $T['tid'];
    }
    if( count( $topiclist ) > 0 )
    {
        $tlist = "'" . implode( "','", $topiclist ) . "'";
        $where .= " AND (tid IN ($tlist) OR alternate_tid IN ($tlist))";
    }
    if ($frontpage_only) {
        $where .= ' AND ( frontpage = 1 OR (frontpage = 2 AND frontpage_date >= "'.$_CONF['_now']->toMySQL(true).'" ) ) ';
    }
    $result = DB_query( "SELECT sid FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= '".$_CONF['_now']->toMySQL(true)."' $where AND perm_anon > 0 ORDER BY date DESC, sid ASC $limitsql" );
    $nrows = DB_numRows( $result );

    $sids = array ();
    for( $i = 0; $i < $nrows; $i++ )
    {
        $A = DB_fetchArray( $result );

        if( $A['sid'] == $updated_id )
        {
            // no need to look any further - this feed has to be updated
            return false;
        }

        $sids[] = $A['sid'];
    }
    $current = implode( ',', $sids );

    if ($_SYND_DEBUG) {
        COM_errorLog ("Update check for all stories: comparing new list ($current) with old list ($update_info)", 1);
    }

    $rc = ( $current != $update_info ) ? false : true;

    return $rc;
}

/**
* Check if a feed for stories from a topic needs to be updated.
*
* @param    string  $tid            topic id
* @param    string  $update_info    list of story ids
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_topic  (optional) topic to be updated
* @param    string  $updated_id     (optional) entry id to be updated
* @return   boolean                 false = feed needs to be updated
*
*/
function SYND_feedUpdateCheckTopic( $tid, $update_info, $limit, $updated_topic = '', $updated_id = '' )
{
    global $_CONF, $_TABLES, $_SYND_DEBUG;

    $where = '';
    if( !empty( $limit ))
    {
        if( substr( $limit, -1 ) == 'h' ) // last xx hours
        {
            $limitsql = '';
            $hours = substr( $limit, 0, -1 );
            $where = " AND date >= DATE_SUB('".$_CONF['_now']->toMySQL(true)."',INTERVAL $hours HOUR)";
        }
        else
        {
            $limitsql = ' LIMIT ' . $limit;
        }
    }
    else
    {
        $limitsql = ' LIMIT 10';
    }

    $result = DB_query( "SELECT sid FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= '".$_CONF['_now']->toMySQL(true)."' AND (tid = '$tid' OR alternate_tid = '$tid') AND perm_anon > 0 ORDER BY date DESC $limitsql" );
    $nrows = DB_numRows( $result );

    $sids = array ();
    for( $i = 0; $i < $nrows; $i++ )
    {
        $A = DB_fetchArray( $result );

        if( $A['sid'] == $updated_id )
        {
            // no need to look any further - this feed has to be updated
            return false;
        }

        $sids[] = $A['sid'];
    }
    $current = implode( ',', $sids );

    if ($_SYND_DEBUG) {
        COM_errorLog ("Update check for topic $tid: comparing new list ($current) with old list ($update_info)", 1);
    }
    $rc = ( $current != $update_info ) ? false : true;
    return $rc;
}

/**
* Check if the contents of glFusion's built-in feeds need to be updated.
*
* @param    string  topic           indicator of the feed's "topic"
* @param    string  limit           number of entries or number of hours
* @param    string  updated_topic   (optional) specific topic to update
* @param    string  updated_id      (optional) specific id to update
* @return   boolean                 false = feed has to be updated, true = ok
*
*/
function SYND_feedUpdateCheck( $topic, $update_data, $limit, $updated_topic = '', $updated_id = '' )
{
    $is_current = true;

    switch($topic) {
    case '::all':
        $is_current = SYND_feedUpdateCheckAll(false, $update_data, $limit,
                            $updated_topic, $updated_id);
        break;

    case '::frontpage':
        $is_current = SYND_feedUpdateCheckAll(true, $update_data, $limit,
                            $updated_topic, $updated_id);
        break;

    default:
        $is_current = SYND_feedUpdateCheckTopic($topic, $update_data,
                            $limit, $updated_topic, $updated_id);
        break;
    }

    return $is_current;
}

/**
* Get content for a feed that holds stories from one topic.
*
* @param    string   $tid      topic id
* @param    string   $limit    number of entries or number of stories
* @param    string   $link     link to topic
* @param    string   $update   list of story ids
* @return   array              content of the feed
*
*/
function SYND_getFeedContentPerTopic( $tid, $limit, &$link, &$update, $contentLength, $feedType, $feedVersion, $fid )
{
    global $_TABLES, $_CONF, $LANG01;

    $content = array ();
    $sids = array();

    if( DB_getItem( $_TABLES['topics'], 'perm_anon', "tid = '".DB_escapeString($tid)."'") >= 2) {
        $where = '';
        if( !empty( $limit )) {
            if( substr( $limit, -1 ) == 'h' ) { // last xx hours
                $limitsql = '';
                $hours = substr( $limit, 0, -1 );
                $where = " AND date >= DATE_SUB('".$_CONF['_now']->toMySQL(true)."',INTERVAL $hours HOUR)";
            } else {
                $limitsql = ' LIMIT ' . $limit;
            }
        } else {
            $limitsql = ' LIMIT 10';
        }

        $topic = DB_getItem( $_TABLES['topics'], 'topic',"tid = '".DB_escapeString($tid)."'" );

        $result = DB_query( "SELECT sid,uid,title,introtext,bodytext,postmode,UNIX_TIMESTAMP(date) AS modified,commentcode,trackbackcode,attribution_author FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= '".$_CONF['_now']->toMySQL(true)."' AND (tid = '".DB_escapeString($tid)."' OR alternate_tid = '".DB_escapeString($tid)."') AND perm_anon > 0 ORDER BY date DESC $limitsql" );

        $nrows = DB_numRows( $result );

        for( $i = 1; $i <= $nrows; $i++ ) {
            $row = DB_fetchArray( $result );
            $sids[] = $row['sid'];

            $storytitle = $row['title'];
            $fulltext   = $row['introtext']."\n".$row['bodytext'];
            $fulltext   = STORY_renderImages($row['sid'],$fulltext);
            $fulltext   = PLG_replaceTags( $fulltext,'glfusion','story' );
            $storytext  = STORY_renderImages($row['sid'],$row['introtext']);
            $storytext  = PLG_replaceTags($storytext,'glfusion','story');

            if ( $contentLength > 1 ) {
                $fulltext  = COM_truncateHTML( $fulltext, $contentLength, ' ...');
                $storytext = COM_truncateHTML( $storytext,$contentLength, ' ...');
            }
            $fulltext = trim( $fulltext );
            $fulltext = str_replace(array("\015\012", "\015"), "\012", $fulltext);

            if($row['postmode']=='plaintext'){
    	        if(!empty($storytext)){
                    $storytext = nl2br($storytext);
                }
                if (!empty($fulltext)){
                    $fulltext = nl2br($fulltext);
                }
            }

            $storylink = COM_buildUrl( $_CONF['site_url']
                                       . '/article.php?story=' . $row['sid'] );
            $extensionTags = PLG_getFeedElementExtensions('article', $row['sid'], $feedType, $feedVersion, $tid, $fid);
            if( $_CONF['trackback_enabled'] && ($feedType == 'RSS') && ($row['trackbackcode'] >= 0)) {
                $trbUrl = TRB_makeTrackbackUrl( $row['sid'] );
                $extensionTags['trackbacktag'] = '<trackback:ping>'.htmlspecialchars($trbUrl).'</trackback:ping>';
            }

            if ( $row['attribution_author'] != "" ) {
                $author = $row['attribution_author'];
            } else {
                $author = COM_getDisplayName( $row['uid'] );
            }

            $article = array( 'title'      => $storytitle,
                              'link'       => $storylink,
                              'uid'        => $row['uid'],
                              'author'     => $author,
                              'date'       => $row['modified'],
                              'format'     => $row['postmode'],
                              'topic'      => $topic,
                              'extensions' => $extensionTags
                            );

            if ( $contentLength > 0 ) {
                $article['summary'] = $storytext;
                $article['text']    = $fulltext;
            }

            if($row['commentcode'] >= 0) {
                $article['commenturl'] = $storylink . '#comments';
            }
            $content[] = $article;
        }
    }
    $link = $_CONF['site_url'] . '/index.php?topic=' . $tid;
    $update = implode( ',', $sids );

    return $content;
}

/**
* Get content for a feed that holds all stories.
*
* @param    boolean  $frontpage_only true: only articles shown on the frontpage
* @param    string   $limit    number of entries or number of stories
* @param    string   $link     link to homepage
* @param    string   $update   list of story ids
* @param    int      $contentLength Length of summary to allow.
* @param    int      $fid       the id of the feed being fetched
* @return   array              content of the feed
*
*/
function SYND_getFeedContentAll($frontpage_only, $limit, &$link, &$update, $contentLength, $feedType, $feedVersion, $fid)
{
    global $_TABLES, $_CONF, $LANG01;

    $where = '';
    if( !empty( $limit )) {
        if( substr( $limit, -1 ) == 'h' ) { // last xx hours
            $limitsql = '';
            $hours = substr( $limit, 0, -1 );
            $where = " AND date >= DATE_SUB('".$_CONF['_now']->toMySQL(true)."',INTERVAL $hours HOUR)";
        } else {
            $limitsql = ' LIMIT ' . $limit;
        }
    } else {
        $limitsql = ' LIMIT 10';
    }

    // get list of topics that anonymous users have access to
    $topics = array();
    $tresult = DB_query( "SELECT tid,topic FROM {$_TABLES['topics']}"
                         . COM_getPermSQL( 'WHERE', 1 ));
    $tnumrows = DB_numRows( $tresult );
    $tlist = '';
    for( $i = 1; $i <= $tnumrows; $i++ ) {
        $T = DB_fetchArray( $tresult );
        $tlist .= "'" . $T['tid'] . "'";
        if( $i < $tnumrows ) {
            $tlist .= ',';
        }
        $topics[$T['tid']] = $T['topic'];
    }
    if( !empty( $tlist )) {
        $where .= " AND (tid IN ($tlist))";
    }
    if ($frontpage_only) {
        $where .= ' AND ( frontpage = 1 OR ( frontpage = 2 AND frontpage_date >= "'.$_CONF['_now']->toMySQL(true).'" ) ) ';
    }
    $result = DB_query( "SELECT sid,tid,uid,title,introtext,bodytext,postmode,UNIX_TIMESTAMP(date) AS modified,commentcode,trackbackcode,attribution_author FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= '".$_CONF['_now']->toMySQL(true)."' $where AND perm_anon > 0 ORDER BY date DESC, sid ASC $limitsql" );

    $content = array();
    $sids = array();
    $nrows = DB_numRows( $result );

    for( $i = 1; $i <= $nrows; $i++ ) {
        $row = DB_fetchArray( $result );
        $sids[] = $row['sid'];

        $storytitle = $row['title'];
        $fulltext   = $row['introtext']."\n".$row['bodytext'];
        $fulltext   = STORY_renderImages($row['sid'],$fulltext);
        $fulltext   = PLG_replaceTags( $fulltext,'glfusion','story' );
        $storytext  = STORY_renderImages($row['sid'],$row['introtext']);
        $storytext  = PLG_replaceTags($storytext,'glfusion','story');

        if ( $contentLength > 1 ) {
            $fulltext  = COM_truncateHTML($fulltext,$contentLength,' ...');
            $storytext = COM_truncateHTML($storytext,$contentLength,' ...');
        }

        $fulltext = trim( $fulltext );
        $fulltext = str_replace(array("\015\012", "\015"), "\012", $fulltext);

        if($row['postmode']=='plaintext') {
            if(!empty($storytext)){
                $storytext = nl2br($storytext);
            }
            if (!empty($fulltext)){
                $fulltext = nl2br($fulltext);
            }
        }

        $storylink = COM_buildUrl( $_CONF['site_url'] . '/article.php?story='.$row['sid'] );
        $extensionTags = PLG_getFeedElementExtensions('article', $row['sid'], $feedType, $feedVersion, $fid, ($frontpage_only ? '::frontpage' : '::all'));
        if( $_CONF['trackback_enabled'] && ($feedType == 'RSS') && ($row['trackbackcode'] >= 0)) {
            $trbUrl = TRB_makeTrackbackUrl( $row['sid'] );
            $extensionTags['trackbacktag'] = '<trackback:ping>'.htmlspecialchars($trbUrl).'</trackback:ping>';
        }
        if ( $row['attribution_author'] != "" ) {
            $author = $row['attribution_author'];
        } else {
            $author = COM_getDisplayName( $row['uid'] );
        }

        $article = array( 'title'      => $storytitle,
                          'link'       => $storylink,
                          'uid'        => $row['uid'],
                          'author'     => $author,
                          'date'       => $row['modified'],
                          'format'     => $row['postmode'],
                          'topic'      => $topics[$row['tid']],
                          'extensions' => $extensionTags
                          );
        if ( $contentLength > 0 ) {
            $article['summary'] = $storytext;
            $artcile['text'] = $fulltext;
        }

        if($row['commentcode'] >= 0) {
            $article['commenturl'] = $storylink . '#comments';
        }
        $content[] = $article;
    }

    $link = $_CONF['site_url'];
    $update = implode( ',', $sids );

    return $content;
}

/**
* Update a feed.
*
* @param   int   $fid   feed id
*
*/
function SYND_updateFeed( $fid )
{
    global $_CONF, $_TABLES, $_SYND_DEBUG;

    $result = DB_query( "SELECT * FROM {$_TABLES['syndication']} WHERE fid = '".DB_escapeString($fid)."'");
    $A = DB_fetchArray( $result );

    if ( $A['is_enabled'] == 1 ) {
        $format = explode( '-', $A['format'] );

        if ($A['format'] == 'ICS-1.0') {
            return SYND_updateFeediCal( $A );
        }

        $rss = new UniversalFeedCreator();
        if ( $A['content_length'] > 1 ) {
            $rss->descriptionTruncSize = $A['content_length'];
        }
        $rss->descriptionHtmlSyndicated = false;
//        $rss->encoding = $A['charset'];
        $rss->language = $A['language'];
        $rss->title = $A['title'];
        $rss->description = $A['description'];

        $imgurl = '';
        if ($A['feedlogo'] != '' ) {
        	$image = new FeedImage();
        	$image->title = $A['title'];
        	$image->url = $_CONF['site_url'] . $A['feedlogo'];
    	    $image->link = $_CONF['site_url'];
        	$rss->image = $image;
        }
        $rss->link = $_CONF['site_url'];
        if ( !empty( $A['filename'] )) {
            $filename = $A['filename'];
        } else {
            $pos = strrpos( $_CONF['rdf_file'], '/' );
            $filename = substr( $_CONF['rdf_file'], $pos + 1 );
        }
        $rss->syndicationURL = SYND_getFeedUrl( $filename );
        $rss->copyright = 'Copyright ' . strftime( '%Y' ) . ' '.$_CONF['site_name'];

        if ($A['type'] == 'article') {
            if ($A['topic'] == '::all') {
                $content = SYND_getFeedContentAll(false, $A['limits'],
                                $link, $data, $A['content_length'],
                                $format[0], $format[1], $fid);
            } elseif ($A['topic'] == '::frontpage') {
                $content = SYND_getFeedContentAll(true, $A['limits'],
                                $link, $data, $A['content_length'],
                                $format[0], $format[1], $fid);
            } else { // feed for a single topic only
                $content = SYND_getFeedContentPerTopic($A['topic'],
                                $A['limits'], $link, $data,
                                $A['content_length'], $format[0],
                                $format[1], $fid);
            }
        } else {
            $content = PLG_getFeedContent($A['type'], $fid, $link, $data, $format[0], $format[1]);
        }

        if ( is_array($content) ) {
            foreach ( $content AS $feedItem ) {
                $item = new FeedItem();

                foreach($feedItem as $var => $value) {
                    if ( $var == 'date') {
                        $dt = new Date($value,$_CONF['timezone']);
                        $item->date = $dt->toISO8601(true);
                    } else if ( $var == 'summary' ) {
                        $item->description = $value;
                    } else if ( $var == 'link' ) {
                        $item->guid = $value;
                        $item->$var = $value;
                    } else {
                        $item->$var = $value;
                    }
                }
                $rss->addItem($item);
            }
        }
        if (empty($link)) {
            $link = $_CONF['site_url'];
        }

        $rss->editor = $_CONF['site_mail'];
        $rc = $rss->saveFeed($format[0].'-'.$format[1], SYND_getFeedPath( $filename ) ,0);

        if( empty( $data )) {
            $data = 'NULL';
        } else {
            $data = "'" . $data . "'";
        }
        if ($_SYND_DEBUG) {
            COM_errorLog ("update_info for feed $fid is $data", 1);
        }

        DB_query( "UPDATE {$_TABLES['syndication']} SET updated = '".$_CONF['_now']->toMySQL(true)."', update_info = $data WHERE fid = '".DB_escapeString($fid)."'");
    }
}

function SYND_updateFeediCal( $A )
{
    global $_CONF, $_TABLES, $_SYND_DEBUG;

    $fid = $A['fid'];

    if ( $A['is_enabled'] == 1 ) {
        $format = explode( '-', $A['format'] );

        $vCalendar = new \Eluceo\iCal\Component\Calendar($_CONF['site_url']);

        if ( !empty( $A['filename'] )) {
            $filename = $A['filename'];
        } else {
            $pos = strrpos( $_CONF['rdf_file'], '/' );
            $filename = substr( $_CONF['rdf_file'], $pos + 1 );
        }

        $content = PLG_getFeedContent($A['type'], $A['fid'], $link, $data, $format[0], $format[1]);

        if ( is_array($content) ) {
            foreach ( $content AS $feedItem ) {
                if (!isset($feedItem['guid'])) {
                    $feedItem['guid'] = $feedItem['link'];
                }
                $vEvent = new \Eluceo\iCal\Component\Event();
                foreach($feedItem as $var => $value) {
                    switch ($var) {
                        case 'date' :
                            $vEvent->setCreated(new \DateTime($value));
                            break;

                        case 'title' :
                            $vEvent->setSummary($value);
                            break;

                        case 'summary' :
                            $vEvent->setDescription($value);
                            break;

                        case 'guid' :
                            $vEvent->setUniqueId($value);
                            break;

                        case 'link' :
                            $vEvent->setUrl($value);
                            break;

                        case 'dtstart' :
                            $vEvent->setDtStart(new \DateTime($value));
                            break;
                        case 'dtend' :
                            $vEvent->setDtEnd(new \DateTime($value));
                            break;

                        case 'location' :
                            $vEvent->setLocation($value);
                            break;

                        case 'allday' :
                            $vEvent->setNoTime($value);
                            break;

                        case 'rrule' :
                            if ($value !== null && $value !== '') {
                                $rrule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
                                $ruleArray = explode(';',$value);
                                $rules = array();
                                foreach ( $ruleArray AS $element ) {
                                    $rule = explode('=',$element);
                                    if ( $rule[0] != '' ) {
                                        $rules[$rule[0]] = $rule[1];
                                    }
                                }
                                foreach ($rules AS $type => $var) {
                                    switch ($type) {
                                        case 'FREQ' :
                                            $rrule->setFreq($var);
                                            break;
                                        case 'INTERVAL' :
                                            $rrule->setInterval($var);
                                            break;
                                        case 'BYSETPOS' :
                                            $rrule->setBySetPos($var);
                                            break;
                                        case 'BYDAY' :
                                            $rrule->setByDay($var);
                                            break;
                                        case 'BYMONTHDAY' :
                                            $rrule->setByMonthDay((int)$var);
                                            break;
                                        case 'BYMONTH' :
                                            $rrule->setByMonth( (int) $var);
                                            break;
                                        case 'DTSTART' :
                                            $vEvent->setDtStart(new \DateTime($var));
                                            break;
                                        case 'COUNT' :
                                            $rrule->setCount($var);
                                            break;
                                        default :
                                            COM_errorLog("SYND: RRULE unknown: " . $type);
                                            break;
                                    }
                                }
                                $vEvent->setRecurrenceRule($rrule);

                            }
                            break;
                    }
                }
                $vCalendar->addComponent($vEvent);
            }
        }
        if (empty($link)) {
            $link = $_CONF['site_url'];
        }
        $feedData = $vCalendar->render();
        $handle = fopen(SYND_getFeedPath( $filename ), "w");
        if ($handle === false) {
            COM_errorLog("Error: Unable to open " . SYND_getFeedPath( $filename ) . " for writing");
            return;
        }
        fwrite($handle,$feedData);
        fclose($handle);

        if ($_SYND_DEBUG) {
            COM_errorLog ("update_info for feed $fid is $data", 1);
        }

        DB_query( "UPDATE {$_TABLES['syndication']} SET updated = '".$_CONF['_now']->toMySQL(true)."', update_info = '".DB_escapeString($data)."' WHERE fid = '".DB_escapeString($fid)."'");
    }
}


/**
* Truncate a feed item's text to a given max. length of characters
*
* @param    string  $text       the item's text
* @param    int     $length     max. length
* @return   string              truncated text
*
*/
function SYND_truncateSummary( $text, $length )
{
    return COM_truncateHTML ($text, $length, ' ...');
}


/**
* Get the path of the feed directory or a specific feed file
*
* @param    string  $feedfile   (option) feed file name
* @return   string              path of feed directory or file
*
*/
function SYND_getFeedPath( $feedfile = '' )
{
    global $_CONF;

    $feedpath = $_CONF['rdf_file'];
    $pos = strrpos( $feedpath, '/' );
    $feed = substr( $feedpath, 0, $pos + 1 );
    $feed .= $feedfile;

    return $feed;
}

/**
* Get the URL of the feed directory or a specific feed file
*
* @param    string  $feedfile   (option) feed file name
* @return   string              URL of feed directory or file
*
*/
function SYND_getFeedUrl( $feedfile = '' )
{
    global $_CONF;

    $feedpath = SYND_getFeedPath();
    $url = substr_replace ($feedpath, $_CONF['site_url'], 0,
                           strlen ($_CONF['path_html']) - 1);
    $url .= $feedfile;

    return $url;
}

/**
* Helper function: Return MIME type for a feed format
*
* @param    string  $format     internal name of the feed format, e.g. Atom-1.0
* @return   string              MIME type, e.g. application/atom+xml
*
*/
function SYND_getMimeType($format)
{
    $fmt = explode('-', $format);
    $type = strtolower($fmt[0]);

    return 'application/' . $type . '+xml';
}
?>