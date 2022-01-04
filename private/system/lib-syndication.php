<?php
/**
* glFusion CMS
*
* glFusion syndication library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2003-2010 by the following authors:
*   Dirk Haun          dirk AT haun-online DOT de
*   Michael Jervis     mike AT fuckingbrit DOT co
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

// set to true to enable debug output in error.log
$_SYND_DEBUG = false;

if ($_CONF['trackback_enabled']) {
    USES_lib_trackback();
}

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

    $db = Database::getInstance();

    $sids = array ();

    $params = array();
    $types  = array();

    $topiclist = array();
    $sql = "SELECT tid FROM `{$_TABLES['topics']}` " . $db->getPermSQL('WHERE',1);
    $stmt = $db->conn->executeQuery($sql);
    while ($T = $stmt->fetch(Database::ASSOCIATIVE)) {
        $topiclist[] = $T['tid'];
    }

    $inTopics = implode(',',
                array_map(
                    function($t) { $db = Database::getInstance(); return $db->conn->quote($t); },
                    $topiclist
                ));

    $where = '';
    $limitsql = '';
    $hours = 0;

    if (!empty($limit)) {
        if (substr($limit, -1) == 'h') { // last xx hours
            $hours = (int) substr( $limit, 0, -1 );
            $where = " AND date >= DATE_SUB(?,INTERVAL ? HOUR)";
            $params[] = $_CONF['_now']->toMySQL(true);
            $params[] = $hours;
            $types[]  = Database::STRING;
            $types[]  = Database::INTEGER;
        } else {
            $limitsql = ' LIMIT ' . (int) $limit;
        }
    } else {
        $limitsql = ' LIMIT 10';
    }

    // if there are topics....
    if (count( $topiclist ) > 0) {
        $where .= " AND (tid IN (".$inTopics.") OR alternate_tid IN (".$inTopics."))";
    }
    if ($frontpage_only) {
        $where .= ' AND ( frontpage = 1 OR (frontpage = 2 AND frontpage_date >= ? ) ) ';
        $params[] = $_CONF['_now']->toMySQL(true);
        $types[]  = Database::STRING;
    }

    $sql = "SELECT sid FROM `{$_TABLES['stories']}`
            WHERE draft_flag = 0 AND date <= ? ".
            $where . " AND perm_anon > 0 ORDER BY date DESC, sid ASC " . $limitsql;

    $params[] = $_CONF['_now']->toMySQL(true);
    $types[]  = Database::STRING;

    $stmt = $db->conn->executeQuery(
                $sql,
                $params,
                $types
    );

    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ($A['sid'] == $updated_id) {
            // no need to look any further - this feed has to be updated
            return false;
        }
        $sids[] = $A['sid'];
    }
    $current = implode( ',', $sids );

    if ($_SYND_DEBUG) {
        Log::write('system',Log::DEBUG,"Update check for all stories: comparing new list ($current) with old list ($update_info)");
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

    $db = Database::getInstance();
    $params = array();
    $types  = array();

    $sql = "SELECT sid FROM `{$_TABLES['stories']}`
                WHERE draft_flag = 0 AND date <= ?
                AND (tid = ? OR alternate_tid = ?) AND perm_anon > 0 ";

    $params[] = $_CONF['_now']->toMySQL(true);
    $params[] = $tid;
    $params[] = $tid;
    $types[]  = Database::STRING;
    $types[]  = Database::STRING;
    $types[]  = Database::STRING;

    if (!empty( $limit)) {
        if (substr( $limit, -1 ) == 'h') { // last xx hours
            $limitsql = '';
            $hours = (int) substr( $limit, 0, -1 );
            $where = " AND date >= DATE_SUB(?,INTERVAL ? HOUR)";
            $params[] = $_CONF['_now']->toMySQL(true);
            $params[] = $hours;
            $types[]  = Database::STRING;
            $types[]  = Database::INTEGER;
        } else {
            $limitsql = ' LIMIT ' . (int) $limit;
            $hours = 0;
        }
    } else {
        $limitsql = ' LIMIT 10';
    }

    $sql .= $where . " ORDER BY `date` DESC " . $limitsql;

    $stmt = $db->conn->executeQuery(
                $sql,
                $params,
                $types
    );

    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ($A['sid'] == $updated_id) {
            // no need to look any further - this feed has to be updated
            return false;
        }
        $sids[] = $A['sid'];
    }
    $current = implode( ',', $sids );

    if ($_SYND_DEBUG) {
        Log::write('system',Log::DEBUG,"Update check for topic $tid: comparing new list ($current) with old list ($update_info)");
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

    $db = Database::getInstance();

    $permAnon = (int) $db->getItem($_TABLES['topics'],'perm_anon',array('tid'=> $tid));

    if ($permAnon >= 2) {
        if( !empty( $limit )) {
            if (substr( $limit, -1 ) == 'h') { // last xx hours
                $limitsql = '';
            } else {
                $limitsql = ' LIMIT ' . (int) $limit;
            }
        } else {
            $limitsql = ' LIMIT 10';
        }

        $topic = $db->getItem($_TABLES['topics'],'topic',array('tid'=>$tid),array(Database::STRING));

        $sql = "SELECT sid,uid,title,introtext,bodytext,postmode,
                    UNIX_TIMESTAMP(date) AS modified,commentcode,trackbackcode,attribution_author
                FROM `{$_TABLES['stories']}`
                WHERE draft_flag = 0 AND
                    date <= ?
                    AND (tid = ? OR alternate_tid = ?)
                    AND perm_anon > 0 ORDER BY date DESC $limitsql";

        $stmt = $db->conn->executeQuery($sql,array($_CONF['_now']->toMySQL(true),$tid,$tid));

        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
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

    $db = Database::getInstance();

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

    $tlist = '';
    $commaCheck = 0;
    $stmt = $db->conn->executeQuery("SELECT tid,topic FROM `{$_TABLES['topics']}` " . $db->getPermSQL('WHERE',1));
    while ($T = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ($commaCheck > 0) {
            $tlist .= ',';
        }
        $commaCheck++;
        $tlist .= $db->conn->quote($T['tid']) ;
        $topics[$T['tid']] = $T['topic'];
    }
    if( !empty( $tlist )) {
        $where .= " AND (tid IN ($tlist))";
    }
    if ($frontpage_only) {
        $where .= ' AND ( frontpage = 1 OR ( frontpage = 2 AND frontpage_date >= "'.$_CONF['_now']->toMySQL(true).'" ) ) ';
    }

    $content = array();
    $sids = array();

    $stmt = $db->conn->executeQuery("SELECT sid,tid,uid,title,introtext,bodytext,postmode,UNIX_TIMESTAMP(date) AS modified,commentcode,trackbackcode,attribution_author FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= '".$_CONF['_now']->toMySQL(true)."' $where AND perm_anon > 0 ORDER BY date DESC, sid ASC $limitsql");
    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
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

    $Feed = glFusion\Syndication\Feed::getById($fid);
    $Feed->Generate();
    return;

    $db = Database::getInstance();

    $A = $db->conn->fetchAssoc("SELECT * FROM {$_TABLES['syndication']} WHERE fid = ?",array($fid),array(Database::STRING));
    if ( $A !== false && $A['is_enabled'] == 1 ) {

        if ($A['format'] == 'ICS-1.0') {
            return SYND_updateFeediCal( $A );
        }

        $format = explode( '-', $A['format'] );
        $rss = new UniversalFeedCreator();
        if ( $A['content_length'] > 1 ) {
            $rss->descriptionTruncSize = $A['content_length'];
        }
        $rss->descriptionHtmlSyndicated = false;
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
            $filename = 'site.rss';
        }
        $rss->syndicationURL = SYND_getFeedUrl( $filename );
        $rss->copyright = 'Copyright ' . strftime( '%Y' ) . ' '.$_CONF['site_name'];

        $content = PLG_getFeedContent($A['type'], $fid, $link, $data, $format[0], $format[1], $A);
        if ($content === NULL) {
            // Special NULL return if the plugin handles its own feed writing
            return;
        } elseif ( is_array($content) ) {
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
            Log::write('system',Log::DEBUG,"update_info for feed $fid is $data");
        }

        $db->conn->executeUpdate(
                "UPDATE {$_TABLES['syndication']} SET updated = ?, update_info = ? WHERE fid = ?",
                array(
                    $_CONF['_now']->toMySQL(true),
                    $data,
                    $fid
                ),
                array(Database::STRING,Database::STRING,Database::STRING)
        );
    }
}

function SYND_updateFeediCal( $A )
{
    global $_CONF, $_TABLES, $_SYND_DEBUG;

    $db = Database::getInstance();

    $fid = $A['fid'];

    if ( $A['is_enabled'] == 1 ) {
        $format = explode( '-', $A['format'] );

        $vCalendar = new \Eluceo\iCal\Component\Calendar(
            $_CONF['site_url'] . '//NONSGML ' . $A['title'] . '//' . strtoupper($_CONF['iso_lang'])
        );

        $vCalendar->setMethod('PUBLISH');
        if (!empty($A['title'])) {
            $vCalendar->setName($A['title']);
        }
        if (!empty($A['description'])) {
            $vCalendar->setDescription($A['description']);
        }
        if ( !empty( $A['filename'] )) {
            $filename = $A['filename'];
        } else {
            $filename = 'glfusion.rss';
//            $pos = strrpos( $_CONF['rdf_file'], '/' );
//            $filename = substr( $_CONF['rdf_file'], $pos + 1 );
        }

        $content = PLG_getFeedContent($A['type'], $A['fid'], $link, $data, $format[0], $format[1], $A);

        if ( is_array($content) ) {
            foreach ( $content AS $feedItem ) {
                if (!isset($feedItem['guid'])) {
                    $feedItem['guid'] = $feedItem['link'];
                }
                $vEvent = new \Eluceo\iCal\Component\Event();
                foreach($feedItem as $var => $value) {
                    switch ($var) {
                        case 'date' :
                            $date = is_numeric($value) ? date('c', $value) : $value;
                            $vEvent->setCreated(new \DateTime($date));
                            break;

                        case 'modified' :
                            $date = is_numeric($value) ? date('c', $value) : $value;
                            $vEvent->setModified(new \DateTime($date));
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

                        case 'status' :
                            $vEvent->setStatus($value);
                            break;

                        case 'sequence':
                            $vEvent->setSequence($value);
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
                                            Log::write('system',Log::ERROR,"SYND: RRULE unknown: " . $type);
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
            Log:;write('system',Log::ERROR,"Error: Unable to open " . SYND_getFeedPath( $filename ) . " for writing");
            return;
        }
        fwrite($handle,$feedData);
        fclose($handle);

        if ($_SYND_DEBUG) {
            Log::write('system',Log::DEBUG,"update_info for feed $fid is $data");
        }
        $db->conn->executeUpdate(
                "UPDATE {$_TABLES['syndication']} SET updated = ?, update_info = ? WHERE fid = ?",
                array(
                    $_CONF['_now']->toMySQL(true),
                    $data,
                    $fid
                ),
                array(Database::STRING,Database::STRING,Database::STRING)
        );
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

    $feed = $_CONF['path_rss'] . $feedfile;

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
