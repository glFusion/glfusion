<?php
/**
* glFusion CMS
*
* glFusion Article / Story API Interface Library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2009 by the following authors:
*   Authors: Tony Bibbs         - tony AT tonybibbs DOT com
*            Blaine Lang        - blaine AT portalparts DOT com
*            Dirk Haun          - dirk AT haun-online DOT de
*            Mark Limburg       - mlimburg AT users DOT sourceforge DOT net
*            Jason Whittenburg  - jwhitten AT securitygeeks DOT com
*            Vincent Furia      - vinny01 AT users DOT sourceforge DOT net
*            Jared Wenerd       - wenerd87 AT gmail DOT com
*            Michael Jervis     - mike AT fuckingbrit DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use glFusion\Database\Database;
use glFusion\Cache\Cache;
use glFusion\Log\Log;
use glFusion\Article\Article;

function plugin_autotags_article( $op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $_USER;

    $db = Database::getInstance();

    if ($op == 'tagname' ) {
        return array('story','story_introtext');
    } else if ( $op == 'tagusage' ) {
        $tagUsage = array(
            array('namespace' => 'glfusion', 'usage'    => 'story'),
        );
        return $tagUsage;
    } else if ($op == 'desc' ) {
        switch ($content) {
            case 'story' :
                return 'story auto tag';
                beak;
            case 'story_introtext' :
                return 'story_introtext autotag';
                break;
            default :
                return '';
        }
    } else if ($op == 'parse') {
        if ($autotag['tag'] == 'story') {
            $parm1_parts = explode('#', $autotag['parm1']);
            $autotag['parm1'] = COM_applyFilter ($autotag['parm1']);
            $url = COM_buildUrl ($_CONF['site_url'].'/article.php?story=' . $autotag['parm1']);
            if (empty ($linktext)) {
                 $linktext = $db->getItem(
                                $_TABLES['stories'],
                                'title',
                                array('sid' => $parm1_parts[0]),
                                array(Database::STRING)
                            );
            }
        }
        if (!empty ($url)) {
            $filelink = COM_createLink($linktext, $url);
            $content = str_replace ($autotag['tagstr'], $filelink,$content);
        }
        if ( $autotag['tag'] == 'story_introtext' ) {
            $url = '';
            $linktext = '';

            $article = new Article();
            if ($article->retrieveArticleFromDB($autotag['parm1']) == Article::STORY_LOADED_OK) {
                if ($article->isViewable() && $article->getAccess() > 0) {
                    $linktext = $article->getDisplayArticle('y');
                    $content = str_replace($autotag['tagstr'],$linktext,$content);
                }
            }
        }
    }
    return $content;
}

/**
 * article: saves a comment
 *
 * @param   string  $title  comment title
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_savecomment_article($title, $comment, $id, $pid, $postmode)
{
    global $_CONF, $_TABLES, $LANG03, $_USER;

    $retval = '';

    $db = Database::getInstance();

    $sql = "SELECT commentcode FROM `{$_TABLES['stories']}` WHERE sid=? AND (draft_flag = 0) AND (date <= ?) " . $db->getPermSQL('AND');

    $commentcode = $db->conn->fetchColumn($sql,
                                          array($id,$_CONF['_now']->toMySQL(false)),
                                          0,
                                          array(Database::STRING,Database::STRING)
    );

    if (!isset($commentcode) || ($commentcode != 0)) {
        return COM_refresh($_CONF['site_url'] . '/index.php');
    }

    $ret = CMT_saveComment($title, $comment, $id, $pid, 'article', $postmode);
    if ($ret > 0) { // failure
        $msg = '';
        if ( SESS_isSet('glfusion.commentpresave.error') ) {
            $msg = COM_showMessageText(SESS_getVar('glfusion.commentpresave.error'),'',1,'error');
            SESS_unSet('glfusion.commentpresave.error');
        } else {
            if ( empty($comment) ) {
                $msg = COM_showMessageText($LANG03[12],'',1,'error');
            }
        }
        $retval .= $msg . CMT_commentForm ($title,$comment,$id,$pid,'article',$LANG03[14],$postmode);

    } else { // success
        $comments = CMT_getCount('article',$id);

        try {
            $db->conn->executeUpdate("UPDATE `{$_TABLES['stories']}` SET comments=? WHERE sid=?",
                                     array($comments,$id),
                                     array(Database::INTEGER, Database::STRING)
            );
        } catch(Throwable $e) {
            $db->error($e->getMessage());
        }
        COM_olderStuff(); // update comment count in Older Stories block
        $retval = COM_refresh(COM_buildUrl($_CONF['site_url']
                              . "/article.php?story=$id#comments"));
    }

    return $retval;
}


/**
 * article: delete a comment
 *
 * @param   int     $cid    Comment to be deleted
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_deletecomment_article($cid, $id)
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $db = Database::getInstance();

    $has_editPermissions = SEC_hasRights ('story.edit');

    $sql = "SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon
            FROM `{$_TABLES['stories']}` WHERE sid = ?";

    $commentRow = $db->conn->fetchAssoc($sql,array($id),array(Database::STRING));

    if ($commentRow === null || $commentRow === false) {
        return $retval;
    }

    if ($has_editPermissions && SEC_hasAccess ($commentRow['owner_id'],
            $commentRow['group_id'], $commentRow['perm_owner'], $commentRow['perm_group'],
            $commentRow['perm_members'], $commentRow['perm_anon']) == 3) {

        CMT_deleteComment($cid, $id, 'article');

        $commentCount = $db->conn->fetchColumn("SELECT COUNT(*) FROM `{$_TABLES['comments']}` WHERE sid = ? AND queued = 0",
                            array($id),
                            0,
                            array(Database::STRING)
        );

        $db->conn->executeUpdate("UPDATE `{$_TABLES['stories']}` SET comments=? WHERE sid=?",
                            array($commentCount,$id),
                            array(Database::INTEGER, Database::STRING)
        );

        $c = Cache::getInstance()->deleteItemsByTag('whatsnew');
        $retval .= COM_refresh(COM_buildUrl($_CONF['site_url']
                 . "/article.php?story=$id") . '#comments');
    } else {
        COM_errorLog ("User {$_USER['username']} "
                    . "did not have permissions to delete comment $cid from $id");
        $retval .= COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    return $retval;
}


/**
 * article: display comment(s)
 *
 * @param   string  $id     Unique idenifier for item comment belongs to
 * @param   int     $cid    Comment id to display (possibly including sub-comments)
 * @param   string  $title  Page/comment title
 * @param   string  $order  'ASC' or 'DESC' or blank
 * @param   string  $format 'threaded', 'nested', or 'flat'
 * @param   int     $page   Page number of comments to display
 * @param   boolean $view   True to view comment (by cid), false to display (by $pid)
 * @return  mixed   results of calling the plugin_displaycomment_ function
*/
function plugin_displaycomment_article($id, $cid, $title, $order, $format, $page, $view)
{
    global $_CONF, $_TABLES, $LANG_ACCESS;

    $db = Database::getInstance();

    $retval = '';

    $article = new Article();
    if ($article->retrieveArticleFromDB($id) == $article::STORY_LOADED_OK) {
        $retval .= $article->getDisplayArticle('p');
    } else {
        return false;
    }

    $delete_option = (SEC_hasRights('story.edit') && $article->getAccess() == 3);

    $retval .= CMT_userComments ($id, $title, 'article', $order,
                    $format, $cid, $page, $view, $delete_option,
                    $article->get('commentcode'),$article->get('uid'));

    return $retval;
}

function plugin_getcommenturlid_article( )
{
    global $_CONF;
    $retval = array();
    $retval[] = $_CONF['site_url'] . '/article.php';
    $retval[] = 'story';
    $retval[] = 'page=';
    return $retval;
}

function plugin_getfeedcontent_article($feed, &$link, &$data,$feedType, $feedVersion)
{
    global $_CONF, $_TABLES;

    $content = '';

    $db = Database::getInstance();

    $row = $db->conn->fetchAssoc(
        "SELECT topic,limits,content_length FROM `{$_TABLES['syndication']}` WHERE fid=?",
        array($feed),
        array(Database::INTEGER)
    );
    if ($row !== false && $row !== null) {

        if ($row['topic'] == '::all') {
            $content = ARTICLE_getFeedContentAll(false, $row['limits'],
                            $link, $data, $row['content_length'],
                            $feedType, $feedVersion, $feed);
        } elseif ($row['topic'] == '::frontpage') {
            $content = ARTICLE_getFeedContentAll(true, $row['limits'],
                            $link, $data, $row['content_length'],
                            $feedType, $feedVersion, $feed);
        } else { // feed for a single topic only
            $content = ARTICLE_getFeedContentPerTopic($row['topic'],
                            $row['limits'], $link, $data,
                            $row['content_length'], $feedType,
                            $feedVersion, $feed);
        }
    }
    return $content;
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
function ARTICLE_getFeedContentPerTopic( $tid, $limit, &$link, &$update, $contentLength, $feedType, $feedVersion, $fid )
{
    global $_TABLES, $_CONF, $LANG01;

    $content = array ();
    $sids = array();
    $bindHours = false;

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

        $sql = "SELECT *
                FROM `{$_TABLES['stories']}`
                WHERE draft_flag = 0 AND
                    date <= ?
                    AND (tid = ? OR alternate_tid = ?)
                    AND perm_anon > 0 ORDER BY date DESC $limitsql";

        $stmt = $db->conn->executeQuery($sql,array($_CONF['_now']->toMySQL(false),$tid,$tid));

        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $sids[] = $row['sid'];

            $story = new Article();
            if ($story->retrieveArticleFromVars($row) != $story::STORY_LOADED_OK) {
                continue;
            }
            $storytitle = $story->getDisplayItem('title');
            $fulltext = $story->getDisplayItem('introtext') . "\n" . $story->getDisplayItem('bodytext');
            $storytext = $story->getDisplayItem('introtext_text');

            if ( $contentLength > 1 ) {
                $fulltext  = COM_truncateHTML( $fulltext, $contentLength, ' ...');
                $storytext = COM_truncateHTML( $storytext,$contentLength, ' ...');
            }
            $fulltext = trim( $fulltext );
            $fulltext = str_replace(array("\015\012", "\015"), "\012", $fulltext);

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
                              'date'       => $story->get('date'),
                              'format'     => $story->get('postmode'),
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
function ARTICLE_getFeedContentAll($frontpage_only, $limit, &$link, &$update, $contentLength, $feedType, $feedVersion, $fid)
{
    global $_TABLES, $_CONF, $LANG01;

    $db = Database::getInstance();

    $where = '';
    if( !empty( $limit )) {
        if( substr( $limit, -1 ) == 'h' ) { // last xx hours
            $limitsql = '';
            $hours = substr( $limit, 0, -1 );
            $where = " AND date >= DATE_SUB('".$_CONF['_now']->toMySQL(false)."',INTERVAL $hours HOUR)";
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
        $where .= ' AND ( frontpage = 1 OR ( frontpage = 2 AND frontpage_date >= "'.$_CONF['_now']->toMySQL(false).'" ) ) ';
    }

    $content = array();
    $sids = array();

    $stmt = $db->conn->executeQuery(
        "SELECT * FROM `{$_TABLES['stories']}`
          WHERE draft_flag = 0 AND date <= '".$_CONF['_now']->toMySQL(false)."' $where AND perm_anon > 0 ORDER BY date DESC, sid ASC $limitsql");

    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $sids[] = $row['sid'];

        $story = new Article();
        if ($story->retrieveArticleFromVars($row) != $story::STORY_LOADED_OK) {
            continue;
        }
        $storytitle = $story->getDisplayItem('title');
        $fulltext = $story->getDisplayItem('introtext') . "\n" . $story->getDisplayItem('bodytext');
        $storytext = $story->getDisplayItem('introtext_text');

        if ( $contentLength > 1 ) {
            $fulltext  = COM_truncateHTML($fulltext,$contentLength,' ...');
            $storytext = COM_truncateHTML($storytext,$contentLength,' ...');
        }

        $fulltext = trim( $fulltext );
        $fulltext = str_replace(array("\015\012", "\015"), "\012", $fulltext);

        $storylink = COM_buildUrl( $_CONF['site_url'] . '/article.php?story='.urlencode($row['sid']) );
        $extensionTags = PLG_getFeedElementExtensions('article', $row['sid'], $feedType, $feedVersion, $fid, ($frontpage_only ? '::frontpage' : '::all'));
        if( $_CONF['trackback_enabled'] && ($feedType == 'RSS') && ($row['trackbackcode'] >= 0)) {
            $trbUrl = TRB_makeTrackbackUrl( $row['sid'] );
            $extensionTags['trackbacktag'] = '<trackback:ping>'.htmlspecialchars($trbUrl).'</trackback:ping>';
        }
        if ( $row['attribution_author'] != "" ) {
            $author = $row['attribution_author'];
        } else {
            $author = $story->getDisplayItem('author_fullname');
        }
        $article = array( 'title'      => $storytitle,
                          'link'       => $storylink,
                          'uid'        => $row['uid'],
                          'author'     => $author,
                          'date'       => $story->get('date'),
                          'format'     => $story->get('postmode'),
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
* This will email new stories in the topics that the user is interested in
*
* In account information the user can specify which topics for which they
* will receive any new article for in a daily digest.
*
* @return   void
*/
function ARTICLE_emailUserTopics()
{
    global $_CONF, $_USER, $_VARS, $_TABLES, $LANG04, $LANG08, $LANG24;

    if ($_CONF['emailstories'] == 0) {
        return;
    }

    $db = Database::getInstance();

    $storytext = '';
    $storytext_text = '';


    $subject = strip_tags( $_CONF['site_name'] . $LANG08[30] . strftime( '%Y-%m-%d', time() ));

    $authors = array();

    // Get users who want stories emailed to them
    $usersql = "SELECT username,email,etids,{$_TABLES['users']}.uid AS uuid, status "
        . "FROM {$_TABLES['users']}, {$_TABLES['userindex']} "
        . "WHERE {$_TABLES['users']}.uid > 1
           AND {$_TABLES['userindex']}.uid = {$_TABLES['users']}.uid
           AND status = ".USER_ACCOUNT_ACTIVE."
           AND (etids <> '-' OR etids IS NULL)
           ORDER BY {$_TABLES['users']}.uid";


    try {
        $stmt = $db->conn->executeQuery($usersql);
    } catch(Throwable $e) {
        if (defined('DVLP_DEBUG')) {
            throw($e);
        }
        return;
    }

    if ( !isset($_VARS['lastemailedstories']) ) {
        $_VARS['lastemailedstories'] = 0;
    }
    $lastrun = $_VARS['lastemailedstories'];
    if (empty($lastrun)) {
        $lastrun = '1970-01-01 00:00:00';
    }

    while($U = $stmt->fetch(Database::ASSOCIATIVE)) {

        $topicSQL = "SELECT tid FROM `{$_TABLES['topics']}` " . $db->getPermSQL('WHERE',$U['uuid']);

        // pull topic info
        $topicStmt = $db->conn->executeQuery($topicSQL);
        $topicData = $topicStmt->fetchAll(Database::ASSOCIATIVE);
        if (count($topicData) == 0) {
            continue;
        }
        $TIDS = array();
        foreach($topicData AS $T) {
            $TIDS[] = $T['tid'];
        }

        if ( !empty( $U['etids'] )) {
            $ETIDS = explode( ' ', $U['etids'] );
            $TIDS = array_intersect( $TIDS, $ETIDS );
        }

        $storySQL = "SELECT *
                     FROM `{$_TABLES['stories']}`
                        WHERE draft_flag = 0
                        AND date <= ".$db->conn->quote($_CONF['_now']->toMySQL(false))."
                        AND date >= ".$db->conn->quote($lastrun);


        $tidsArray = array_map(function($tid) {
          $db = Database::getInstance();
          return $db->conn->quote($tid);
        }, $TIDS);

        if ( sizeof( $TIDS ) > 0) {
            $storySQL .= " AND (tid IN (" . implode( ",", $tidsArray ) . "))";
        }

        $storySQL .= $db->getPermSQL( 'AND', $U['uuid'] );
        $storySQL .= ' ORDER BY featured DESC, date DESC';

        // run the story select

        $storyStmt = $db->conn->executeQuery($storySQL,array(),array());
        $storyData = $storyStmt->fetchAll(Database::ASSOCIATIVE);

        if ( count($storyData) == 0 ) {
            // If no new stories where pulled for this user, continue with next
            continue;
        }

        $T = new Template($_CONF['path_layout']);
        $T->set_file(array('message'     => 'digest.thtml',
                           'story'       => 'digest_story.thtml'));

        $TT = new Template($_CONF['path_layout']);
        $TT->set_file(array('message'     => 'digest_text.thtml',
                           'story'        => 'digest_story_text.thtml'));

        $T->set_var('week_date',strftime( $_CONF['shortdate'], time() ));
        $TT->set_var('week_date',strftime( $_CONF['shortdate'], time() ));

        $T->set_var('site_name',$_CONF['site_name']);
        $TT->set_var('site_name',$_CONF['site_name']);

        $T->set_var('title', sprintf($LANG08[29],$_CONF['site_name']));
        $TT->set_var('title', sprintf($LANG08[29],$_CONF['site_name']));

        $T->set_var('remove_msg',sprintf($LANG08[36],$_CONF['site_name'],$_CONF['site_url']));
        $TT->set_var('remove_msg',sprintf($LANG08[37],$_CONF['site_name'],$_CONF['site_url']));

        foreach($storyData AS $S) {
            $story = new Article();
            if ($story->retrieveArticleFromVars($S) != $story::STORY_LOADED_OK) {
                continue;
            }
            $story_url = COM_buildUrl( $_CONF['site_url'] . '/article.php?story=' . urlencode($story->get('sid')) );
            $title     = $story->getDisplayItem('title');

            if ( $_CONF['contributedbyline'] == 1 ) {
                $storyauthor = $story->getDisplayItem('author_fullname');
            }


            $story_date = $story->getDisplayItem('date');

            if ( $_CONF['emailstorieslength'] > 0 ) {
                $storytext = $story->getDisplayItem('introtext');
                $storytext_text = $story->getDisplayItem('introtext_text');

                if ( $_CONF['emailstorieslength'] > 1 ) {
                    $storytext = COM_truncateHTML( $storytext,$_CONF['emailstorieslength'], '...' );
                    $storytext_text = COM_truncate( $storytext,$_CONF['emailstorieslength'], '...' );
                }
            } else {
                $storytext = '';
                $storytext_text = '';
            }
            $T->set_var ('story_introtext',$storytext);
            $TT->set_var ('story_introtext',$storytext_text);

            $T->set_var(array(
                'story_url'     => $story_url,
                'story_title'   => $title,
                'story_author'  => $storyauthor,
                'story_date'    => $story_date,
                'story_text'    => $storytext,
            ));
            $T->parse('digest_stories', 'story', true);

            $TT->set_var(array(
                'story_url'     => $story_url,
                'story_title'   => $title,
                'story_author'  => $storyauthor,
                'story_date'    => $story_date,
                'story_text'    => $storytext_text,
            ));
            $TT->parse('digest_stories', 'story', true);
        }

        $T->parse('digest', 'message', true);
        $TT->parse('digest', 'message', true);

        $mailtext = $T->finish($T->get_var('digest'));
        $mailtext_text = $TT->finish($TT->get_var('digest'));

        $mailfrom = $_CONF['noreply_mail'];
        $mailtext .= PHP_EOL . PHP_EOL . $LANG04[159];
        $mailtext_text .= PHP_EOL . PHP_EOL . $LANG04[159];

        $to = array();
        $from = array();
        $from = COM_formatEmailAddress('',$mailfrom);
        $to   = COM_formatEmailAddress( $U['username'],$U['email'] );
        COM_mail ($to, $subject, $mailtext, $from,1,0,'',$mailtext_text);

    }

    $db->conn->executeUpdate(
        "UPDATE `{$_TABLES['vars']}` SET value = ? WHERE name = 'lastemailedstories'",
        array($_CONF['_now']->toMySQL(false)),
        array(Database::STRING)
    );
}

function plugin_getiteminfo_article($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG09;

    $db = Database::getInstance();

    $buildingSearchIndex = false;

    $properties = explode(',', $what);

    $fields = array();

    foreach ($properties as $p) {
        switch ($p) {
            case 'search_index' :
                $buildingSearchIndex = true;
                break;
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
                $fields[] = 'subtitle';
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
            case 'author_name' :
                $fields[] = 'uid';
                $fields[] = 'attribution_author';
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

    $params = array();
    $types  = array();

    if (count($fields) == 0) {
        $retval = array();
        return NULL;
    }

    if ($id == '*') {
        if ( $buildingSearchIndex ) {
            $where = " WHERE draft_flag=0 AND ";
        } else {
            $where = ' WHERE';
        }
    } else {
        $where = " WHERE (sid = ?) AND";
        $params[] = $id;
        $types[]  = Database::STRING;

    }
    $where .= ' (date <= ?)';

    $params[] = $_CONF['_now']->toMySQL(false);
    $types[]  = Database::STRING;

    if ($uid > 0) {
        $permSql = $db->getPermSql('AND', $uid)
                 . $db->getTopicSql('AND', $uid);
    } else {
        $permSql = $db->getPermSql('AND') . $db->getTopicSql('AND');
    }
    $sql = "SELECT *,UNIX_TIMESTAMP(date) AS unixdate FROM `{$_TABLES['stories']}`" . $where . $permSql;
    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }

    $stmt = $db->conn->executeQuery(
                $sql,
                $params,
                $types
    );

    $retval = array();

    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {

        $article = new Article();
        $article->retrieveArticleFromVars($A);
        if (!$article->isViewable() || $article->getAccess() < 2) {
            continue;
        }

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
                    $props[$p] = trim($article->getDisplayItem('introtext') . $article->getDisplayItem('bodytext'));
                    break;
                case 'raw-description':
                case 'searchidx' :
                    $props[$p] = trim($article->get('introtext') . $article->get('bodytext'));
                    break;
                case 'excerpt':
                    $props['excerpt'] = trim($article->getDisplayItem('introtext'));
                    break;
                case 'feed':
                    $feedfile = $db->getItem($_TABLES['syndication'], 'filename',
                                           array('topic'=> '::all'));
                    if (empty($feedfile)) {
                        $feedfile = $db->getItem($_TABLES['syndication'], 'filename',
                                                array('topic'=> '::frontpage'));
                    }
                    if (empty($feedfile)) {
                        $feedfile = $db->getItem($_TABLES['syndication'], 'filename',
                                               array('topic' => $A['tid']));
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
                    $props['title'] = $article->getDisplayItem('title');
                    if ($buildingSearchIndex) {
                        $props['title'] .= ' ' . $article->getDisplayItem('subtitle') ;
                    }
                    break;
                case 'url':
                    if (empty($A['sid'])) {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/article.php?story=' . $id);
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
                case 'author_name' :
                    if ( $A['attribution_author'] != "" ) {
                        $props['author_name'] = $article->getDisplayItem('attribution_author');
                    } else {
                        $props['author_name'] = COM_getDisplayName($A['uid']);
                    }
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
            if ($id == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
            }
        }

        if ($id == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($id != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }
    if ( $retval === '' || (is_array($retval) && count($retval) == 0 ) ) return NULL;

    return $retval;
}

/**
 * START STORY PLUGIN STUB SECTION
 *
 */

/**
 * Return true since this component supports webservices
 *
 * @return  bool	True, if webservices are supported
 */
function plugin_wsEnabled_article()
{
    return false;
}

/**
*
* Checks that the current user has the rights to moderate a story
* returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_article()
{
    return SEC_hasRights('story.moderate');
}


/**
* Returns SQL & Language texts to moderation.php
*
* @return   mixed   Plugin object or void if not allowed
*
*/
function plugin_itemlist_article()
{
    global $_TABLES, $LANG29;

    if (plugin_ismoderator_article()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG29[35];
        $plugin->submissionhelpfile = 'ccstorysubmission.html';
        $plugin->getsubmissionssql = "SELECT sid AS id,title,UNIX_TIMESTAMP(date) AS day,tid,uid"
                                    . " FROM {$_TABLES['storysubmission']}"
                                    . COM_getTopicSQL ('WHERE')
                                    . " ORDER BY date ASC";
        $plugin->addSubmissionHeading($LANG29[10]);
        $plugin->addSubmissionHeading($LANG29[14]);
        $plugin->addSubmissionHeading($LANG29[15]);
        $plugin->addSubmissionHeading($LANG29[46]);

        return $plugin;
    }
}

/**
* returns list of moderation values
*
* The array returned contains (in order): the key field name, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_article()
{
    global $_TABLES;

    return array (
        'sid',
        $_TABLES['stories'],
        'sid,uid,tid,title,introtext,date,postmode',
        $_TABLES['storysubmission']
    );
}

/**
* Counts the number of stories that are submitted
*
* @return   int     number of stories in submission queue
*
*/
function plugin_submissioncount_article()
{
    global $_TABLES;

    return (plugin_ismoderator_article()) ? DB_count ($_TABLES['storysubmission']) : 0;
}

/**
* Handles a comment submission approval for a story
*
* @return   none
*
*/
function plugin_commentapproved_article($cid,$type,$sid)
{
    global $_PLUGINS, $_TABLES;

    $comments = DB_count($_TABLES['comments'], array('type', 'sid','queued'), array('article', $sid,0));
    DB_change($_TABLES['stories'], 'comments', $comments, 'sid', $sid);
    COM_olderStuff(); // update comment count in Older Stories block
}

function plugin_user_move_article($origUID, $destUID)
{
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['stories']} SET uid=".(int)$destUID." WHERE uid=".(int)$origUID;
    DB_query($sql,1);
    $sql = "UPDATE {$_TABLES['stories']} SET owner_id=".(int) $destUID." WHERE owner_id=".(int) $origUID;
    DB_query($sql,1);
    $sql = "UPDATE {$_TABLES['storysubmission']} SET uid=".(int) $destUID." WHERE uid=".(int)$origUID;
    DB_query($sql,1);
}

/* -- do we want to support export for articles?? */

function plugin_privacy_export_article($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_TABLES;

    $retval = '';

    $exportFields = array('sid','date','title','tid');

    $sql = "SELECT * FROM {$_TABLES['stories']} WHERE uid = ". (int) $uid . " OR owner_id = ".(int) $uid . " ORDER BY date ASC";

    $result = DB_query($sql);
    $rows = DB_fetchAll($result);

    $retval .= "<stories>\n";

    foreach($rows AS $row) {
        $retval .= "<story>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {
                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</story>\n";
    }
    $retval .= "</stories>\n";

    if ( function_exists('tidy_repair_string')) {
        $retval = tidy_repair_string($retval, array('input-xml' => 1));
    }

    return $retval;

}

function plugin_canuserrate_article($item_id, $uid)
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();

    $retval = false;

    // check to see if we own it...
    // check to see if we have permission to vote
    // check to see if we have already voted (Handled by library)...

    if ( $_CONF['rating_enabled'] != 0 ) {
        if ( $_CONF['rating_enabled'] == 2 ) {
            $retval = true;
        } else if ( !COM_isAnonUser() ) {
            $retval = true;
        } else {
            $retval = false;
        }
    }

    if ($retval == true) {
        $row = $db->conn->fetchAssoc(
                "SELECT owner_id
                        FROM `{$_TABLES['stories']}`
                        WHERE sid=? " . $db->getPermSQL( 'AND', $uid, 2),
                array($item_id),
                array(Database::STRING)
        );

        if ($row !== false && $row !== null) {
            if ($row['owner_id'] != $uid) {
                $retval = true;
            }
        } else {
            $retval = false;
        }
    }
    return $retval;
}

?>