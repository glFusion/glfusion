<?php
/**
* glFusion CMS
*
* glFusion Main Index Page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*  Dirk Haun         dirk@haun-online.de
*
*/

if (!@file_exists('siteconfig.php') ) {
    header("Location:admin/install/index.php");
    exit;
}

use glFusion\Database\Database;

require_once 'lib-common.php';
USES_lib_story();

$newstories = false;
$displayall = false;
$limituser  = false;
$centerBlock = true;
$display    = '';
$pageBody   = '';
$topiclimit = 0;
$story_sort     = 'date';
$story_sort_dir = 'DESC';

if (isset($_CONF['story_sort_by'])) {
    $story_sort = $_CONF['story_sort_by'];
}
if (isset($_CONF['story_sort_dir'])) {
    $story_sort_dir = $_CONF['story_sort_dir'];
}

if (isset ($_GET['display'])) {
    if (($_GET['display'] == 'new') && (empty($topic))) {
        $newstories = true;
    } else if (($_GET['display'] == 'all') && (empty ($topic))) {
        $displayall = true;
    }
}

$pageBody .= glfusion_UpgradeCheck();
$pageBody .= glfusion_SecurityCheck();

$topic = Topic::currentID();

$page = (int) filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
if ($page == 0) {
    $page = 1;
} elseif ($page < 0) {
    COM_404();
}

$cronCheck = _cronSchedule();

// If the plugin is hijacking the index page - let it do it here
if (!$newstories && !$displayall) {
    // give plugins a chance to replace this page entirely
    if ($centerBlock) {
        $newcontent = PLG_showCenterblock (CENTERBLOCK_FULLPAGE, $page, $topic);
    }
    if (!empty($newcontent)) {
        echo $pageBody . $newcontent . $cronCheck;
        exit;
    }
}

if (isset($_GET['u'])) {
    $limituser = true;
    $limituser_id = (int) filter_input(INPUT_GET, 'u', FILTER_SANITIZE_NUMBER_INT);
}

if ( isset($_GET['ncb'])) {
    $centerBlock = false;
}

// get DB object
$db = Database::getInstance();

// set archive topic
try {
    $archivetid = $db->conn->fetchColumn("SELECT tid FROM `{$_TABLES['topics']}` WHERE archive_flag=1");
} catch(\Doctrine\DBAL\DBALException $e) {
    if (defined('DVLP_DEBUG')) {
        throw($e);
    }
    $archivetid = '';
}

// create template
$T = new Template($_CONF['path_layout']);
$T->set_file('page','index.thtml');

if (!empty($topic)) {
    $topiclimit     = (int) Topic::Current()->limitnews;
    $story_sort     = Topic::Current()->sort_by;
    $story_sort_dir = Topic::Current()->sort_dir;
    $topic_desc     = Topic::Current()->description;
    $topicname      = Topic::Current()->topic;

    switch ( $story_sort ) {
        case 0 :    // date
            $story_sort = 'date';
            break;
        case 1 :    // title
            $story_sort = 'title';
            break;
        case 2 :    // ID
            $story_sort = 'sid';
            break;
        default :
            $story_sort = 'date';
            break;
    }
    switch ( $story_sort_dir ) {
        case 'DESC' :
            $story_sort_dir = 'DESC';
            break;
        case 'ASC' :
            $story_sort_dir = 'ASC';
            break;
        default :
            $story_sort_dir = 'DESC';
            break;
    }

    if (!empty($topic_desc)) {
        $outputHandle = \outputHandler::getInstance();
        $outputHandle->addMeta('name', 'description', $topic_desc, HEADER_PRIO_NORMAL);
        $outputHandle->addMeta('property', 'og:description', $topic_desc, HEADER_PRIO_NORMAL);
        $T->set_var('topic_desc',$topic_desc);
    }
    $T->set_var('breadcrumbs',true);
    $T->set_var('topic',$topicname);
}

$U['maxstories'] = 0;
$U['aids'] = '';
$U['tids'] = '';
if (!COM_isAnonUser()) {
    $U['maxstories'] = $_USER['maxstories'];
    $U['aids'] = $_USER['aids'];
    $U['tids'] = $_USER['tids'];
}

$maxstories = 0;
if ($U['maxstories'] >= $_CONF['minnews']) {
    $maxstories = $U['maxstories'];
}
if ((!empty ($topic)) && ($maxstories == 0)) {
    if ($topiclimit >= $_CONF['minnews']) {
        $maxstories = (int) $topiclimit;
    }
}
if ($maxstories == 0) {
    $maxstories = (int) $_CONF['limitnews'];
}

$limit = (int) $maxstories;
if ($limit < 1) {
    $limit = 1;
}

$ratedIds = array();
if ( $_CONF['rating_enabled'] != 0 ) {
    $ratedIds = RATING_getRatedIds('article');
}

// Check session to determine if any pending messages need to be displayed
$msg = COM_getMessage();
if ( $msg > 0 ) {
    $plugin = '';
    if (isset ($_GET['plugin'])) {
        $plugin = (string) filter_input(INPUT_GET, 'plugin', FILTER_SANITIZE_STRING);
        if (!in_array($plugin,$_PLUGINS)) {
            $plugin = '';
        }
    }
    $pageBody .= COM_showMessage ($msg, $plugin,'',0,'info');
}

// Show any Plugin formatted blocks
// Requires a plugin to have a function called plugin_centerblock_<plugin_name>

if ($centerBlock) {
    $displayBlock = PLG_showCenterblock (CENTERBLOCK_TOP, $page, $topic); // top blocks
}

if (!empty ($displayBlock)) {
    $pageBody .= $displayBlock;
}

// Build the monster query to retrieve articles based on
// user permissions and preferences
$queryBuilder = $db->conn->createQueryBuilder();
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

if (empty($topic)) {
    $sql = $db->getLangSQL ('tid', '', 's');
    if (!empty($sql)) {
        $queryBuilder->andWhere($sql);
    }
}

// if a topic was provided only select those stories.
if (!empty($topic)) {
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
    $queryBuilder->andWhere('s.tid != ' .
      $queryBuilder->createNamedParameter($archivetid,Database::STRING)
    );
}

$db->qbGetPermSQL($queryBuilder,'',0,SEC_ACCESS_RO,'s');

// test data
//$U['aids'] = $U['aids'] . "5 10 escape'd bad";

if (!empty($U['aids'])) {
    $aids = explode(' ',$U['aids']);
    $aids = array_map( 'intval', $aids);
    $U['aids'] = implode(',',$aids);
    $queryBuilder->andWhere(
        $queryBuilder->expr()->notIn('s.uid',
         $queryBuilder->createNamedParameter($aids,\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
        )
    );
}

// test data
//$U['tids'] = "one two escape'd ";

if (!empty($U['tids'])) {
    $tids = explode(' ',$U['tids']);
    $U['tids'] = implode(',',$tids);
    $queryBuilder->andWhere(
        $queryBuilder->expr()->notIn('s.uid',
          $queryBuilder->createNamedParameter($tids,\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
        )
    );
}

$db->qbGetTopicSQL($queryBuilder,'AND',0,'s');

if ( $limituser ) {
    $queryBuilder->andWhere('s.uid',
      $queryBuilder->createNamedParameter($limituser_id,Database::INTEGER)
    );
}

if ($newstories) {
    $sql = "(date >= (date_sub(NOW(), INTERVAL :interval SECOND))) ";
    $queryBuilder->andWhere($sql);
    $queryBuilder->setParameter('interval',$_CONF['newstoriesinterval'],Database::INTEGER);
}

//print $_CONF['newstoriesinterval'].'<br>';

$offset = intval(($page - 1) * $limit);
$userfields = 'u.uid, u.username, u.fullname';
if ($_CONF['allow_user_photo'] == 1) {
    $userfields .= ', u.photo';
    if ($_CONF['use_gravatar']) {
        $userfields .= ', u.email';
    }
}

$orderBy = $story_sort . ' ' . $story_sort_dir;

//@TODO vet / validate these 2 vars...
$queryBuilder->addOrderBy($story_sort,$story_sort_dir);

// clone the primary query so we can execute
// a query to get total story count - we are overriding
// the SELECT statement to use just the count

$countQueryBuilder = clone $queryBuilder;
$countQueryBuilder->select("COUNT(*) AS count");

try {
    $cStmt = $countQueryBuilder->execute();
} catch(\Doctrine\DBAL\DBALException $e) {
    if (defined('DVLP_DEBUG')) {
        throw($e);
    }
}

if ($cStmt) {
    $totalStoryCount = $cStmt->fetchColumn();
    $cStmt->closeCursor();
} else {
    $totalStoryCount = 0;
}

// Build the final query to pull the story data
// Set the limits

$queryBuilder
    ->addSelect(explode(',',$userfields))
    ->setFirstResult($offset)
    ->setMaxResults($limit);

//print $queryBuilder->getSQL();exit;

try {
    $stmt = $queryBuilder->execute();
} catch(\Doctrine\DBAL\DBALException $e) {
    if (defined('DVLP_DEBUG')) {
        throw($e);
    }
    $stmt = false;
}

$num_pages = ceil ($totalStoryCount / $limit);
$articleCounter = 0;

$storyRecs = array();
if ($stmt) {
    $storyRecs = $stmt->fetchAll();
    $stmt->closeCursor();
}
$nrows = 0;
foreach($storyRecs AS $A) {
    $nrows++;
    $story = new Story();
    $story->loadFromArray($A);
    if ($articleCounter == 0) {
        // processing the first story
        if ( $_CONF['showfirstasfeatured'] == 1 ) {
            $story->_featured = 1;
        }
        if ($story->DisplayElements('featured') == 1) {
            if ($centerBlock) {
                $pageBody .= STORY_renderArticle ($story, 'y');
                $pageBody .= PLG_showCenterblock (CENTERBLOCK_AFTER_FEATURED, $page, $topic);
            }
        } else {
            if ($centerBlock) {
                $pageBody .= PLG_showCenterblock (CENTERBLOCK_AFTER_FEATURED, $page, $topic);
            }
            $pageBody .= STORY_renderArticle ($story, 'y');
        }
    } else {
        $pageBody .= STORY_renderArticle ($story, 'y');
    }

    $articleCounter++;
}

if ($nrows > 0) {
    // get plugin center blocks that follow articles
    if ($centerBlock) {
        $pageBody .= PLG_showCenterblock (CENTERBLOCK_BOTTOM, $page, $topic); // bottom blocks
    }

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
            if ( $centerBlock == false ) {
                $base_url .= ($parm1set ? '&' : '?') . 'ncb=x';
            }
        } else {
            $base_url = $_CONF['site_url'] . '/index.php?topic=' . $topic;
            if ( $limituser ) {
                $base_url .= '&u='.$limituser_id;
            }
            if ( $centerBlock == false ) {
                $base_url .= '&ncb=x';
            }
        }
        $pagination = COM_printPageNavigation ($base_url, $page, $num_pages);
        $T->set_var('pagination',$pagination);
    }
} else { // no stories to display
    $cbDisplay = '';

    if ($centerBlock) {
        $cbDisplay .= PLG_showCenterblock (CENTERBLOCK_AFTER_FEATURED, $page, $topic);
        $cbDisplay .= PLG_showCenterblock (CENTERBLOCK_BOTTOM, $page, $topic); // bottom blocks
    }

    if ( (!isset ($_CONF['hide_no_news_msg']) || ($_CONF['hide_no_news_msg'] == 0)) && $cbDisplay == '') {
        // If there's still nothing to display, show any default centerblocks.
        if ( $centerBlock ) {
            $cbDisplay .= PLG_showCenterblock(CENTERBLOCK_NONEWS, $page, $topic);
        }
        if ($cbDisplay == '') {
            // If there is still nothing to show, show the stock message
            $eMsg = $LANG05[2];
            if (!empty ($topic)) {
                $eMsg .= sprintf ($LANG05[3], $topicname);
            }
            $cbDisplay .= COM_showMessageText($eMsg, $LANG05[1],true,'warning');
        }
    }
    $pageBody .= $cbDisplay;
}

if ( $cronCheck != '' ) {
    $T->set_var('cron',$cronCheck);
}

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
    if ((($_VARS['last_maint_run'] + 600 ) <= time())) {
        return '<img alt="" src="'.$_CONF['site_url'].'/cron.php?id='.time().'" height="1" width="2" />';
    } else {
        return '';
    }
}
?>