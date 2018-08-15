<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | glFusion homepage.                                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony@tonybibbs.com                          |
// |          Mark Limburg      - mlimburg@users.sourceforge.net              |
// |          Jason Whittenburg - jwhitten@securitygeeks.com                  |
// |          Dirk Haun         - dirk@haun-online.de                         |
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

if (!@file_exists('siteconfig.php') ) {
    header("Location:admin/install/index.php");
    exit;
}

require_once 'lib-common.php';
USES_lib_story();

$newstories = false;
$displayall = false;
$limituser  = false;
$cb         = true;

$display = '';
$pageBody = '';

if (isset ($_GET['display'])) {
    if (($_GET['display'] == 'new') && (empty ($topic))) {
        $newstories = true;
    } else if (($_GET['display'] == 'all') && (empty ($topic))) {
        $displayall = true;
    }
}

if ( isset($_GET['u'])) {
    $limituser = true;
    $limituser_id = COM_applyFilter($_GET['u']);
}

if ( isset($_GET['ncb'])) {
    $cb = false;
}

$page = 1;
if (isset ($_GET['page'])) {
    $page = (int) COM_applyFilter ($_GET['page'], true);
    if ($page == 0) {
        $page = 1;
    }
}

$conn = \glFusion\Database::getInstance();
$archivetid = $conn->fetchColumn("SELECT tid FROM {$_TABLES['topics']} WHERE archive_flag=1");

// get template
$T = new Template($_CONF['path_layout']);
$T->set_file('page','index.thtml');

if ( !empty($topic) ) {
    $sql = "SELECT limitnews,sort_by,sort_dir,description,topic FROM {$_TABLES['topics']} WHERE tid = ?";
    $topicResult = $conn->prepare($sql);
    $topicResult->bindParam(1, $topic,\glFusion\Database::STRING);
    $topicResult->execute();
    $row = $topicResult->fetch();
    if ($row !== false) {
        $topiclimit = $row['limitnews'];
        $story_sort = $row['sort_by'];
        $story_sort_dir = $row['sort_dir'];
        $topic_desc = $row['description'];
        $topicname = $row['topic'];
        if (!empty($topic_desc)) { // set meta data
            $outputHandle = \outputHandler::getInstance();
            $outputHandle->addMeta('name', 'description', $topic_desc, HEADER_PRIO_NORMAL);
            $outputHandle->addMeta('property', 'og:description', $topic_desc, HEADER_PRIO_NORMAL);
            $T->set_var('topic_desc',$topic_desc);
        }
        $T->set_var('breadcrumbs',true);
        $T->set_var('topic',$topicname);
    }
}

if (!$newstories && !$displayall) {
    // give plugins a chance to replace this page entirely
    if ( $cb ) $newcontent = PLG_showCenterblock (CENTERBLOCK_FULLPAGE, $page, $topic);
    if (!empty ($newcontent)) {
        echo $newcontent;
        exit;
    }
}

$ratedIds = array();
if ( $_CONF['rating_enabled'] != 0 ) {
    $ratedIds = RATING_getRatedIds('article');
}

$pageBody .= glfusion_UpgradeCheck();

$pageBody .= glfusion_SecurityCheck();

$msg = COM_getMessage();
if ( $msg > 0 ) {
    $plugin = '';
    if (isset ($_GET['plugin'])) {
        $plugin = COM_applyFilter ($_GET['plugin']);
    }
    $pageBody .= COM_showMessage ($msg, $plugin,'',0,'info');
}

// Show any Plugin formatted blocks
// Requires a plugin to have a function called plugin_centerblock_<plugin_name>

if ( $cb ) $displayBlock = PLG_showCenterblock (CENTERBLOCK_TOP, $page, $topic); // top blocks
if (!empty ($displayBlock)) {
    $pageBody .= $displayBlock;
    // Check if theme has added the template which allows the centerblock
    // to span the top over the rightblocks
    if (file_exists($_CONF['path_layout'] . 'topcenterblock-span.thtml')) {
            $topspan = new Template($_CONF['path_layout']);
            $topspan->set_file (array ('topspan'=>'topcenterblock-span.thtml'));
            $topspan->parse ('output', 'topspan');
            $pageBody .= $topspan->finish ($topspan->get_var('output'));
            $GLOBALS['centerspan'] = true;
    }
}

if (!COM_isAnonUser()) {
    $U['maxstories'] = $_USER['maxstories'];
    $U['aids'] = $_USER['aids'];
    $U['tids'] = $_USER['tids'];
} else {
    $U['maxstories'] = 0;
    $U['aids'] = '';
    $U['tids'] = '';
}

$topiclimit = 0;

$story_sort     = 'date';
$story_sort_dir = 'DESC';

// need to sanitize
if (isset($_CONF['story_sort_by'])) {
    $story_sort = $_CONF['story_sort_by'];
}
if (isset($_CONF['story_sort_dir'])) {
    $story_sort_dir = $_CONF['story_sort_dir'];
}

$maxstories = 0;
if ($U['maxstories'] >= $_CONF['minnews']) {
    $maxstories = $U['maxstories'];
}
if ((!empty ($topic)) && ($maxstories == 0)) {
    if ($topiclimit >= $_CONF['minnews']) {
        $maxstories = $topiclimit;
    }
}
if ($maxstories == 0) {
    $maxstories = $_CONF['limitnews'];
}

$limit = $maxstories;
if ($limit < 1) {
    $limit = 1;
}

// Build the monster query to retrieve articles based on
// user permissions and preferences
$queryBuilder = $conn->createQueryBuilder();
$queryBuilder
    ->select(   's.*',
                'UNIX_TIMESTAMP(s.date) AS unixdate',
                'UNIX_TIMESTAMP(s.expire) as expireunix',
                'UNIX_TIMESTAMP(s.frontpage_date) as frontpage_date_unix',
                't.topic',
                't.imageurl'
            )
    ->from($_TABLES['stories'],'s')
    ->leftJoin('s',$_TABLES['users'],'u','s.uid=u.uid')
    ->leftJoin('s',$_TABLES['topics'],'t','s.tid=t.tid')
    ->where('date <= NOW()')
    ->andWhere('draft_flag = 0')
    ->orderBy('featured', 'DESC');

if (empty ($topic)) {
    $sql = COM_getLangSQL ('tid', '', 's');
    if (!empty($sql)) {
        $queryBuilder->andWhere($sql);
    }
}

// if a topic was provided only select those stories.
if (!empty($topic)) {
    $queryBuilder->andWhere(
        $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq('s.tid', $queryBuilder->createNamedParameter($topic,\glFusion\Database::STRING)),
            $queryBuilder->expr()->eq('s.alternate_tid', $queryBuilder->createNamedParameter($topic,\glFusion\Database::STRING))
        )
    );
} elseif (!$newstories) {
    $queryBuilder->andWhere(
        $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq('frontpage',1),
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('frontpage',2),
                $queryBuilder->expr()->gte('frontpage_date','NOW()')
            )
        )
    );
}

if ($topic != $archivetid) {
    $queryBuilder->andWhere('s.tid != ' . $queryBuilder->createNamedParameter($archivetid,\glFusion\Database::STRING));
}

$sql = COM_getPermSQL('',0,2,'s');

if (!empty($sql)) {
    $queryBuilder->andWhere($sql);
}

if (!empty($U['aids'])) {
    $queryBuilder->andWhere(
        $queryBuilder->expr()->notIn('s.uid', "'".str_replace( ' ', ",", $U['aids'] )."'"  )
    );
}

if (!empty($U['tids'])) {
    $queryBuilder->andWhere(
        $queryBuilder->expr()->notIn('s.uid','"'. str_replace( ' ', "','", $U['tids'] ).'"' )
    );
}

//@TODO - do we need to handle  topic SQL??
$sql = COM_getTopicSQL ('', 0, 's');
if (!empty($sql)) {
    $queryBuilder->andWhere($sql);
}

if ( $limituser ) {
    $queryBuilder->andWhere('s.uid',$queryBuilder->createNamedParameter($limituser_id,\glFusion\Database::INTEGER));
}

if ($newstories) {
    $sql = "(date >= (date_sub(NOW(), INTERVAL {$_CONF['newstoriesinterval']} SECOND))) ";
    $queryBuilder->andWhere($sql);
}

$offset = intval(($page - 1) * $limit);
$userfields = 'u.uid, u.username, u.fullname';
if ($_CONF['allow_user_photo'] == 1) {
    $userfields .= ', u.photo';
    if ($_CONF['use_gravatar']) {
        $userfields .= ', u.email';
    }
}

if ( !empty($topic) ) {
    switch ( $story_sort ) {
        case 0 :    // date
            $orderBy = ' date ';
            break;
        case 1 :    // title
            $orderBy = ' title ';
            break;
        case 2 :    // ID
            $orderBy = ' sid ';
            break;
        default :
            $orderBy = ' date ';
            break;
    }
    switch ( $story_sort_dir ) {
        case 'DESC' :
            $orderBy = $orderBy . ' DESC ';
            break;
        case 'ASC' :
            $orderBy = $orderBy . ' ASC ';
            break;
        default :
            $orderBy = $orderBy . ' DESC ';
            break;
    }
} else {
//    $orderBy = ' date DESC';
    $orderBy = $story_sort . ' ' . $story_sort_dir;
}

//@TODO vet / validate these 2 vars...
$queryBuilder->addOrderBy($story_sort,$story_sort_dir);

// clone the primary query so we can execute
// a query to get total story count - we are overriding
// the SELECT statement to use just the count

$countQueryBuilder = clone $queryBuilder;
$countQueryBuilder->select("COUNT(*) AS count");
$cStmt = $countQueryBuilder->execute();
$D = $cStmt->fetch();

// Build the final query to pull the story data
// Set the limits


$queryBuilder
    ->addSelect(explode(',',$userfields))
    ->setFirstResult($offset)
    ->setMaxResults($limit);

$stmt = $queryBuilder->execute();

$nrows = $stmt->rowCount();

$num_pages = ceil ($D['count'] / $limit);
$articleCounter = 0;

$storyRecs = $stmt->fetchAll();
if (count($storyRecs > 0)) {
    foreach($storyRecs AS $A) {
        $story = new Story();
        $story->loadFromArray($A);
        if ($articleCounter == 0) {
            if ( $_CONF['showfirstasfeatured'] == 1 ) {
                $story->_featured = 1;
            }
            if ($story->DisplayElements('featured') == 1) {
                if ($cb) {
                    $pageBody .= STORY_renderArticle ($story, 'y');
                    $pageBody .= PLG_showCenterblock (CENTERBLOCK_AFTER_FEATURED, $page, $topic);
                }
            } else {
                if ($cb) {
                    $pageBody .= PLG_showCenterblock (CENTERBLOCK_AFTER_FEATURED, $page, $topic);
                }
                $pageBody .= STORY_renderArticle ($story, 'y');
            }
        } else {
            $pageBody .= STORY_renderArticle ($story, 'y');
        }

        $articleCounter++;
    }

    // get plugin center blocks that follow articles
    if ( $cb ) $pageBody .= PLG_showCenterblock (CENTERBLOCK_BOTTOM, $page, $topic); // bottom blocks

    // Print Google-like paging navigation
    if (!isset ($_CONF['hide_main_page_navigation']) ||
            ($_CONF['hide_main_page_navigation'] == 0)) {
        if (empty ($topic)) {
            $parm1set = false;
            $base_url = $_CONF['site_url'] . '/index.php';
            if ($newstories) {
                $base_url .= '?display=new';
                $parm1set = true;
            }
            if ( $limituser ) {
                $base_url .= ($parm1set ? '&' : '?') . 'u=' . $limituser_id;
                $parm1set = true;
            }
            if ( $cb == false ) {
                $base_url .= ($parm1set ? '&' : '?') . 'ncb=x';
            }
        } else {
            $base_url = $_CONF['site_url'] . '/index.php?topic=' . $topic;
            if ( $limituser ) {
                $base_url .= '&u='.$limituser_id;
            }
            if ( $cb == false ) {
                $base_url .= '&ncb=x';
            }
        }
        $pagination = COM_printPageNavigation ($base_url, $page, $num_pages);
        $T->set_var('pagination',$pagination);
    }
} else { // no stories to display
    $cbDisplay = '';
    if ($cb) {
        $cbDisplay .= PLG_showCenterblock (CENTERBLOCK_AFTER_FEATURED, $page, $topic);
        $cbDisplay .= PLG_showCenterblock (CENTERBLOCK_BOTTOM, $page, $topic); // bottom blocks
    }
    if ( (!isset ($_CONF['hide_no_news_msg']) ||
            ($_CONF['hide_no_news_msg'] == 0)) && $cbDisplay == '') {
        // If there's still nothing to display, show any default centerblocks.
        if ( $cb ) $cbDisplay .= PLG_showCenterblock(CENTERBLOCK_NONEWS, $page, $topic);
        if ($cbDisplay == '') {
            // If there's *still* nothing to show, show the stock message
            $eMsg = $LANG05[2];
            if (!empty ($topic)) {
                $eMsg .= sprintf ($LANG05[3], $topicname);
            }
            $cbDisplay .= COM_showMessageText($eMsg, $LANG05[1],true,'warning');
        }
    }
    $pageBody .= $cbDisplay;
}

$cronCheck = _cronSchedule();
if ( $cronCheck != '' ) $T->set_var('cron',$cronCheck);

if (isset($_CONF['infinite_scroll']) && $_CONF['infinite_scroll'] == true ) {
    $T->set_var('enable_infinite_scroll',true);
    if ( isset($_CONF['comment_engine']) && $_CONF['comment_engine'] == 'facebook') {
        $T->set_var('fb_comment_engine',true);
    }
    if ( isset($_CONF['comment_engine']) && $_CONF['comment_engine'] == 'disqus') {
        $T->set_var('comment_disqus_shortname',$_CONF['comment_disqus_shortname']);
    }

    $pluginData = PLG_isOnPageLoad();
    if ( $pluginData != '' ) {
        $T->set_var('plugin_scripts',$pluginData);
    }
}

$T->set_var('page_contents',$pageBody);
$T->parse( 'output', 'page' );

$display = COM_siteHeader();
$display .= $T->finish( $T->get_var('output'));
$display .= COM_siteFooter (true); // The true value enables right hand blocks.

// Output page
echo $display;

function _cronSchedule() {
    global $_CONF, $_VARS;

    if ( !isset($_VARS['last_maint_run'] ) || $_VARS['last_maint_run'] == '') {
        $_VARS['last_maint_run'] = 0;
    }
    if (  (( $_VARS['last_maint_run'] + 600 ) <= time()) ) {
        return '<img alt="" src="'.$_CONF['site_url'].'/cron.php?id='.time().'" height="1" width="2" />';
    } else {
        return '';
    }
}
?>