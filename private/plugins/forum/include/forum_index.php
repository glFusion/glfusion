<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | forum_index.php                                                          |
// |                                                                          |
// | Main program to view forum                                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

function forum_index()
{
    global $_CONF, $_FF_CONF, $_TABLES, $_USER, $FF_userprefs, $LANG_GF01, $LANG_GF02, $LANG_GF91;

    USES_forum_functions();
    USES_forum_format();
    require_once $_CONF['path_system'] . 'classes/timer.class.php';

    $forum      = isset($_REQUEST['forum']) ? COM_applyFilter($_REQUEST['forum'],true) : 0;
    $show       = isset($_REQUEST['show']) ? COM_applyFilter($_REQUEST['show'],true) : 0;
    $page       = isset($_REQUEST['page']) ? COM_applyFilter($_REQUEST['page'],true) : 0;
    $order      = isset($_REQUEST['order']) ? COM_applyFilter($_REQUEST['order'],true) : 0;
    $sort       = isset($_REQUEST['sort']) ? COM_applyFilter($_REQUEST['sort'],true) : 0;
    $cat_id     = isset($_REQUEST['cat_id']) ? COM_applyFilter($_REQUEST['cat_id'],true) : 0;
    $forum_id   = isset($_REQUEST['forum_id']) ? COM_applyFilter($_REQUEST['forum_id'],true) : 0;
    $op         = isset($_REQUEST['op']) ? COM_applyFilter($_REQUEST['op']) : '';

    $db = \glFusion\Database::getInstance();

    /*
     * Initialize vars
     */

    $canPost = 0;
    $display = '';
    $pageBody = '';
    $todaysdate=date("l, F d, Y");

    forum_chkUsercanAccess();

    // Check to see if request to mark all topics read was requested
    if (!COM_isAnonUser() && $op == 'markallread') {
        $now = time();
        $categories = array();
        if ($cat_id == 0 && $forum_id == 0) {
            $sql = "SELECT id FROM {$_TABLES['ff_categories']} ORDER BY id";
            $csql = $db->conn->prepare($sql);
            $csql->execute();
            $row = $topicResult->fetchAll();
            foreach($row AS $catRec) {
                $categories[] = $catRec['id'];
            }
        } else {
            $categories[] = $cat_id;
        }

        foreach ($categories as $category) {

            $queryBuilder = $db->conn->createQueryBuilder();
            $queryBuilder
                ->select(   'forum_id',
                            'grp_id'
                        )
                ->from($_TABLES['ff_forums'])
                ->where('forum_cat = :forum_cat')
                ->setParameter('forum_cat',$category,\glFusion\Database::INTEGER);
            if ($forum_id !=0) {
                $queryBuilder->andWhere('forum_id = :forum_id')
                ->setParameter('forum_id',$forum_id,\glFusion\Database::INTEGER);
            }
            $stmt = $queryBuilder->execute();
//            $catRecs = $stmt->fetchAll();

            while($frecord = $stmt->fetch()) {
//            foreach($catRecs AS $frecord) {
                if (SEC_inGroup($frecord['grp_id'])) {
                    $qb = $db->conn->createQueryBuilder()
                      ->delete($_TABLES['ff_log'])
                      ->where('uid = :user_id')
                      ->andwhere('forum = :forum_id')
                      ->setParameter(':user_id', $_USER['uid'],\glFusion\Database::INTEGER)
                      ->setParameter(':forum_id', $frecord['forum_id'],\glFusion\Database::INTEGER);
                    $qb->execute();
//                    DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE uid=".(int) $_USER['uid']." AND forum=".(int)$frecord['forum_id']."");

                    $sql = "SELECT id FROM {$_TABLES['ff_topic']} WHERE forum = :forum_id AND pid = 0";
                    $tsql = $db->conn->prepare($sql);
                    $tsql->bindParam('forum_id',$frecord['forum_id'],\glFusion\Database::INTEGER);
                    $tsql->execute();

//                    $tsql = DB_query("SELECT id FROM {$_TABLES['ff_topic']} WHERE forum={$frecord['forum_id']} and pid=0");
                    while ($trecord = $tsql->fetch()) {
//                    while($trecord = DB_fetchArray($tsql)){
                        $log_sql = DB_query("SELECT * FROM {$_TABLES['ff_log']} WHERE uid=".(int) $_USER['uid']." AND topic=".(int) $trecord['id']." AND forum=".(int) $frecord['forum_id']);
                        if (DB_numRows($log_sql) == 0) {
                            DB_query("INSERT INTO {$_TABLES['ff_log']} (uid,forum,topic,time) VALUES (".(int) $_USER['uid'].",".(int) $frecord['forum_id'].",".(int) $trecord['id'].",'$now')");
                        }
                    }
                }
            }
        }
        COM_setMsg($LANG_GF01['all_read_success'],'info');
        if ( !empty($extraWhere) ) {
            echo COM_refresh($_CONF['site_url'].'/forum/index.php?forum='.(int) $forum_id);
        } else {
            echo COM_refresh($_CONF['site_url'] .'/forum/index.php');
        }
        exit();
    }

    if ($op == 'subscribe') {
        $display = FF_siteHeader();
        if ($forum != 0) {
            $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
            DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',0,'".$LANG_GF02['msg138']."',".(int)$_USER['uid'].", now() )");
            // Delete all individual topic notification records
            DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id > 0" );
            $display .= FF_statusMessage($LANG_GF02['msg134'],$_CONF['site_url'] .'/forum/index.php?forum=' .$forum,$LANG_GF02['msg135']);
        } else {
            $display .= FF_BlockMessage($LANG_GF01['ERROR'],$LANG_GF02['msg136'],false);
        }
        $display .= FF_siteFooter();
        echo $display;
        exit();
    }

    if ($op == 'search') {
//        $prevorder  = isset($_REQUEST['prevorder']) ? COM_applyFilter($_REQUEST['prevorder']) : 0;
//        $direction  = isset($_REQUEST['direction']) ? COM_applyFilter($_REQUEST['direction']) : 'DESC';
//        $sort       = isset($_REQUEST['sort']) ? COM_applyFilter($_REQUEST['sort'],true) : 0;
        $dCat = isset($_GET['cat']) ? COM_applyFilter($_GET['cat'],true) : 0;

        $pageBody = '';

        $report = new Template($_CONF['path'] . 'plugins/forum/templates/');
        $report->set_file('report', 'search_results.thtml');
/*
        switch($order) {
            case 1:
                $orderby = 'subject';
                break;
            case 2:
                $orderby = 'replies';
                break;
            case 3:
                $orderby = 'views';
                break;
            case 4:
                $orderby = 'lastupdated';
                break;
            default:
                $orderby = 'lastupdated';
                $order = 4;
                break;
        }
        if ($order == $prevorder) {
            $direction = ($direction == "DESC") ? "ASC" : "DESC";
        } else {
            $direction = ($direction == "ASC") ? "ASC" : "DESC";
        }
*/
        $returnPostfix = '';
    // need to grab referer
        if ( $forum != 0 ) {
            $returnPostfix = '?forum='.$forum;
        }
        if ( $dCat != 0 ) {
            $returnPostfix = '?cat='.$dCat;
        }

        $html_query = isset($_REQUEST['query']) ? trim(strip_tags($_REQUEST['query'])) : '';
        $query = isset($_REQUEST['query']) ? DB_escapeString($_REQUEST['query']) : '';

        $report->set_var (array(
                'form_action'   => $_CONF['site_url'] . '/forum/index.php?op=search',
                'LANG_TITLE'    => $LANG_GF02['msg119'].' '. @htmlentities($html_query, ENT_QUOTES, COM_getEncodingt()),
                'returnlink'    => $_CONF['site_url'].'/forum/index.php' . $returnPostfix,
                'LANG_return'   => $LANG_GF02['msg175'],
                'LANG_Heading1' => $LANG_GF01['SUBJECT'],
                'LANG_Heading2' => $LANG_GF01['REPLIES'],
                'LANG_Heading3' => $LANG_GF01['VIEWS'],
                'LANG_Heading4' => $LANG_GF01['DATE'],
                'op'            => "&amp;op=search&amp;query=".@htmlentities($html_query, ENT_QUOTES, COM_getEncodingt()),
//                'prevorder'     => $order,
//                'direction'     => $direction,
                'page'          => '1'
        ));

        if ($_FF_CONF['usermenu'] == 'navbar') {
            $report->set_var('navmenu', FF_NavbarMenu());
        } else {
            $report->set_var('navmenu','');
        }

        if ($forum != 0) {
            $inforum = "AND (forum = ".(int) $forum.")";
            $inforum2 = " WHERE (forum = " . (int) $forum. ") ";
            $report->set_var('forum_specific',$forum);
            $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
            $report->set_var('forum_name',$forum_name);
        } else {
            $inforum = "";
            $inforum2 = "";
            $report->unset_var('forum_specific');
        }

        $query      = trim(rtrim($query));
        $query      = ff_limitChars($query);
        $keywords   = ff_filterSearchKeys($query);
        $escQuery   = DB_escapeString($query);

        $titleSQL = array();
        $postSQL = array();
        if (count($keywords) > 1 && !empty($escQuery)) {
            $titleSQL[] = "if (subject LIKE '%".$escQuery."%',3,0)";
            $postSQL[] = "if (comment LIKE '%".$escQuery."%',4,0)";
        }
        foreach($keywords as $key) {
            if ( !empty($key)) {
                $subjectSQL[] = "if (subject LIKE '%".DB_escapeString($key)."%',1,0)";
                $postSQL[] = "if (comment LIKE '%".DB_escapeString($key)."%',2,0)";
            }
        }
        if (empty($subjectSQL)) {
            $subjectSQL[] = 0;
        }
        if (empty($postSQL)) {
            $postSQL[] = 0;
        }
        $where = forum_buildSearchAccessSql('');
        if ($where == '' ) $where = ' 1=1 ';
        $sql = "SELECT *,
                    (
                        (
                        ".implode(" + ", $subjectSQL)."
                        )+
                        (
                        ".implode(" + ", $postSQL)."
                        )
                    ) as relevance
                    FROM {$_TABLES['ff_topic']} t LEFT JOIN {$_TABLES['ff_forums']} f ON t.forum=f.forum_id WHERE " . $where . $inforum . "
                    HAVING relevance > 0
                    ORDER BY relevance DESC, lastupdated DESC
                    LIMIT 50";

        $result = DB_query($sql);

        $nrows = DB_numRows($result);
        $report->set_block('report', 'reportrow', 'rrow');

        if ($nrows > 0) {
            if ($_FF_CONF['enable_user_rating_system'] && !COM_isAnonUser() ) {
                $user_rating = intval(DB_getItem($_TABLES['ff_userinfo'],'rating','uid='.(int)$_USER['uid']));
            }
            $csscode = 1;

            for ($i = 1; $i <= $nrows; $i++) {
                $P = DB_fetchArray($result);
                $fres = DB_query("SELECT grp_id,rating_view FROM {$_TABLES['ff_forums']} WHERE forum_id=".(int)$P['forum']);
                list($forumgrpid,$view_rating) = DB_fetchArray($fres);

                $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id=".(int)$forumgrpid);
                if (SEC_inGroup($groupname)) {
                    if ( $_FF_CONF['enable_user_rating_system'] && !COM_isAnonUser() ) {
                        if ( $view_rating > $user_rating ) {
                            continue;
                        }
                    }

                    if ($_FF_CONF['use_censor']) {
                        $P['subject'] = COM_checkWords($P['subject']);
                    }
                    $postdate = COM_getUserDateTimeFormat($P['date']);
                    $link = '<a href="'.$_CONF['site_url'].'/forum/viewtopic.php?forum='.$P['forum'].'&amp;showtopic='.$P['id'].'&amp;query='.htmlentities($html_query, ENT_QUOTES, COM_getEncodingt()) . '">';
                    if ( $P['pid'] != 0 ) {
                        $pResult = DB_query("SELECT views, replies FROM {$_TABLES['ff_topic']} WHERE id=".(int) $P['pid']);
                        list($views,$replies) = DB_fetchArray($pResult);
                    } else {
                        $views = $P['views'];
                        $replies = $P['replies'];
                    }
                    $report->set_var(array(
                            'post_start_ahref'  => $link,
                            'post_subject'      => $P['subject'], //  . ' ('. $P['relevance'].')',
                            'post_end_ahref'    => '</a>',
                            'post_date'         => $postdate[0],
                            'post_replies'      => $replies, // $P['replies'],
                            'post_views'        => $views, // $P['views'],
                            'csscode'           => $csscode));
                    $report->parse('rrow', 'reportrow',true);

                    if($csscode == 2) {
                        $csscode = 1;
                    } else {
                        $csscode++;
                    }
                }
            }
        }

        if ($forum == 0) {
            $link = '<p><a href="'.$_CONF['site_url'].'/forum/index.php">'.$LANG_GF02['msg175'].'</a></p>';
            $report->set_var ('bottomlink',$link);
        } else {
            $link = '<p><a href="'.$_CONF['site_url'].'/forum/index.php?forum='.$forum.'">'.$LANG_GF02['msg175'].'</a></p>';
            $report->set_var ('bottomlink',$link);
        }
        $report->parse ('output', 'report');
        $pageBody = $report->finish($report->get_var('output'));

        $display .= FF_siteHeader($LANG_GF02['msg45']);
        $display .= $pageBody;
        $display .= FF_siteFooter();
        echo $display;
        exit();
    }

// ************** MAIN CODE - gets executed for category / forum list

    $mytimer = new timerobject();
    $mytimer->startTimer();

    $errMsg = '';

    $uid = 1;
    if ( !COM_isAnonUser() && isset($_USER['uid']) ) {
        $uid = $_USER['uid'];
    }
    $dt = new Date('now',$_USER['tzid']);

    //Display Categories
    if ($forum == 0) {
        $birdSeedStart = '';
        $categorycounter = 0;

        $dCat = isset($_GET['cat']) ? COM_applyFilter($_GET['cat'],true) : 0;
        $groups = array ();
        $usergroups = SEC_getUserGroups();
        foreach ($usergroups as $group) {
            $groups[] = $group;
        }
        $groupAccessList = implode(',',$groups);

        $qb = $db->conn->createQueryBuilder()
          ->select('*')
          ->from($_TABLES['ff_categories'])
          ->orderby('cat_order','ASC');
        if ($dCat > 0) {
            $qb->where('id = ?')
               ->setParameter(1,$dCat,\glFusion\Database::INTEGER);
            $birdSeedStart = '<a href="'.$_CONF['site_url'].'/forum/index.php">Forum Index</a> :: ';
        }
        $sql = $qb->getSQL();

        $c = glFusion\Cache::getInstance();
        $key = 'forumindex__'.md5($sql).'_'.$c->securityHash(true,true);
        if ( COM_isAnonUser() && $c->has($key) ) {
            $pageBody = $c->get($key);
        } else {
            $stmt = $qb->execute();
            $numCategories = $stmt->rowCount();

            $forumlisting = new Template(array($_CONF['path'] . 'plugins/forum/templates/',$_CONF['path'] . 'plugins/forum/templates/links/'));
            $forumlisting->set_file ('forumlisting','homepage.thtml');

            $forumlisting->set_var (array(
                    'forumindeximg' => '<img src="'._ff_getImage('forumindex').'" alt=""/>',
                    'phpself'       => $_CONF['site_url'] .'/forum/index.php',
                    'layout_url'    => $_CONF['layout_url'],
                    'forum_home'    => 'Forum Index'
            ));

            $i=0;
            while ($A = $stmt->fetch()) {
                $i++;
                $forumlisting->set_block('forumlisting', 'catrows', 'crow');
                $forumlisting->clear_var('frow');
                if ( $birdSeedStart == '' ) {
                    $birdseed = $birdSeedStart . '<a href="'.$_CONF['site_url'].'/forum/index.php?cat='.$A['id'].'">'.$A['cat_name'].'</a>';
                } else {
                    $birdseed = $birdSeedStart . $A['cat_name'];
                }
                $forumlisting->set_var (array(
                    'cat_name'  => $A['cat_name'],
                    'cat_desc'  => $A['cat_dscp'],
                    'cat_id'    => $A['id'],
                    'birdseed'  => $birdseed,
                ));

                if (!COM_isAnonUser()) {
                    $link = 'href="'.$_CONF['site_url'].'/forum/index.php?op=markallread&amp;cat_id='.$A['id'].'">';
                    $forumlisting->set_var (array(
                            'markreadlink'  => $link,
                            'LANG_markread' => $LANG_GF02['msg84']
                    ));
                    if ($i == 1) {
                        $newpostslink = 'href="'.$_CONF['site_url'] .'/forum/list.php?op=newposts">';
                        $forumlisting->set_var (array(
                            'newpostslink'  => $newpostslink,
                            'LANG_newposts' => $LANG_GF02['msg112'],
                        ));
                        $viewnewpostslink = true;
                   } else {
                        $forumlisting->clear_var ('newpostslink');
                   }
                } else {
                    $forumlisting->clear_var ('newpostslink');
                    $forumlisting->clear_var ('markreadlink');
                }

                $forumlisting->set_var (array(
                                'LANGGF91_forum'    => $LANG_GF91['forum'],
                                'LANGGF01_TOPICS'   => $LANG_GF01['TOPICS'],
                                'LANGGF01_POSTS'    => $LANG_GF01['POSTS'],
                                'LANGGF01_LASTPOST' => $LANG_GF01['LASTPOST']
                ));

// no user supplied data for this query
// may optimize to use straight SQL. QB gives
// portability...
                //Display all forums under each cat
                $qb = $db->conn->createQueryBuilder()
                  ->select('*')
                  ->from($_TABLES['ff_forums'],'f')
                  ->leftJoin('f',$_TABLES['ff_topic'],'t','f.last_post_rec=t.id')
                  ->where('forum_cat = ?')
                  ->setParameter(1,$A['id'],\glFusion\Database::INTEGER)
                  ->andWhere(
                        $qb->expr()->in('grp_id', $groupAccessList )
                            )
                  ->andWhere('is_hidden=0')
                  ->orderby('forum_order','ASC');

                $forumQuery = $qb->execute();
                $numForums = $forumQuery->rowCount();
                $numForumsDisplayed = 0;

                $forumlisting->set_block('forumlisting', 'forumrows', 'frow');

                while ($B = $forumQuery->fetch()) {
        			if ( _ff_canUserViewRating($B['forum_id'] ) ) {
                    	$lastforum_noaccess = false;
                	    $topicCount = $B['topic_count'];
                    	$postCount = $B['post_count'];
/* - rewrite the whole block
     we can pull all moderator records
     in a single query and build an array
     of arrays to hold the info

                    	if ( $_FF_CONF['show_moderators'] ) {
                        	$modsql = DB_query("SELECT * FROM {$_TABLES['ff_moderators']} WHERE mod_forum=".(int) $B['forum_id']);
                       	 	$moderatorcnt = 1;
                        	if (DB_numRows($modsql) > 0) {
                            	while($showmods = DB_fetchArray($modsql,false)) {
                                	if ($showmods['mod_uid'] == '0') {
                                    	if ($showmods['mod_groupid'] > 0) {
                                            $showmods['mod_username'] = _ff_getGroup($showmods['mod_groupid']);
                                    	}
        	                            if($moderatorcnt == 1 OR $moderators == '') {
            	                            $moderators = $showmods['mod_username'];
                	                    } else {
                    	                    $moderators .= ', ' . $showmods['mod_username'];
                        	            }
                            	    } else {
                                	    if($moderatorcnt == 1 OR $moderators == '') {
        	                                $moderators = COM_getDisplayName($showmods['mod_uid']);
            	                        } else {
                	                        $moderators .= ', ' . COM_getDisplayName($showmods['mod_uid']);
                    	                }
                        	        }
                            	    $moderatorcnt++;
        	                    }
            	            } else {
                	            $moderators = $LANG_GF01['no_one'];
                    	    }
                        	$forumlisting->set_var ('moderator', sprintf($LANG_GF01['MODERATED'],$moderators));
        	            } else {
            	            $forumlisting->set_var ('moderator', '');
                	    }
*/
                   		$numForumsDisplayed ++;
                   		$busyforum = 0;
                   		$quietforum = 1;
                    	if ($postCount > 0) {
                    	    $B['subject'] = COM_truncate($B['subject'],40);
                	        if ($_FF_CONF['use_censor']) {
                    	        $B['subject'] = COM_checkWords($B['subject']);
                        	}
        	                if (!COM_isAnonUser()) {
            	                // Determine if there are new topics since last visit for this user.
$tcount = $db->conn->fetchColumn("SELECT COUNT(uid) FROM {$_TABLES['ff_log']} WHERE uid=? AND forum = ? AND time > 0",array($uid,$B['forum_id']));
//                                $tcount = (int) DB_getItem($_TABLES['ff_log'],'COUNT(uid)',"uid = ".(int) $uid." AND forum = ".(int) $B['forum_id']." AND time > 0");
                                if ($topicCount > $tcount ) {
                                    $busyforum = 1;
                                    $quietforum = 0;
                        	        $folderimg = '<img src="'._ff_getImage('busyforum').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['msg111'].'" title="'.$LANG_GF02['msg111'].'"/>';
                        	        $folder_icon = _ff_getImage('busyforum');
                        	        $folder_msg = $LANG_GF02['msg111'];
                        	    } else {
                        	        $busyforum = 0;
                        	        $quietforum = 1;
                            	    $folderimg = '<img src="'._ff_getImage('quietforum').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['quietforum'].'" title="'.$LANG_GF02['quietforum'].'"/>';
                            	    $folder_icon = _ff_getImage('quietforum');
                            	    $folder_msg = $LANG_GF02['quietforum'];
                            	}
        	                } else {
            	                $folderimg = '<img src="'._ff_getImage('quietforum').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['quietforum'].'" title="'.$LANG_GF02['quietforum'].'"/>';
            	                $folder_icon = _ff_getImage('quietforum');
            	                $folder_msg = $LANG_GF02['quietforum'];
        	                }
        	                $dt->setTimestamp($B['date']);
                            $lastdate1 = $dt->format($_CONF['shortdate'],true);
                            $dtNow = new Date('now',$_USER['tzid']);
                	        if ($dt->isToday()) {
                                $lasttime = $dt->format($_CONF['timeonly'],true);
                        	    $lastdate = $LANG_GF01['TODAY'] .$lasttime;
        	                } elseif ($_FF_CONF['allow_user_dateformat']) {
            	                $lastdate = $dt->format($dt->getUserFormat(),$B['date'],true);
                    	    } else {
                                $lastdate = $dt->format($_CONF['daytime'],true);
        	                }

            	            $lastpostmsgDate  = '<span class="forumtxt">' . $LANG_GF01['ON']. '</span>' .$lastdate;
                	        if($B['uid'] > 1) {
                                $lastposterName = $B['name'];
                        	    $by = '<a href="' .$_CONF['site_url']. '/users.php?mode=profile&amp;uid=' .$B['uid']. '">' .$lastposterName. '</a>';
        	                } else {
            	                $by = $B['name'];
                	        }
                    	    $lastpostmsgBy = $LANG_GF01['BY']. $by;
                        	$forumlisting->set_var (array(
                        	                'lastpostmsgDate'   => $lastpostmsgDate,
                        	                'lastPostDate'      => $lastdate,
        	                                'lastpostmsgTopic'  => $B['subject'],
            	                            'lastpostmsgBy'     => $lastpostmsgBy
            	            ));
            	        }  else {
                	        $forumlisting->set_var (array(
                	                        'lastpostmsgDate'   => $LANG_GF01['nolastpostmsg'],
                    	                    'lastpostmsgTopic'  => '',
                        	                'lastpostmsgBy'     => '',
                        	                'lastPostDate'      => $LANG_GF01['nolastpostmsg'],
                        	));
        	                $folderimg = '<img src="'._ff_getImage('quietforum').'" style="border:none;vertical-align:middle;" alt="'.$LANG_GF02['quietforum'].'" title="'.$LANG_GF02['quietforum'].'"/>';
        	                $folder_icon = _ff_getImage('quietforum');
        	                $folder_msg = $LANG_GF02['quietforum'];
            	        }

            	        if ($B['pid'] == 0) {
                	        $topicparent = $B['id'];
                    	} else {
        	                $topicparent = $B['pid'];
            	        }

        	            $forumlisting->set_var (array(
        	                            'folderimg'     => $folderimg,
        	                            'folder_icon'   => $folder_icon,
        	                            'folder_msg'    => $folder_msg,
            	                        'forum_id'      => $B['forum_id'],
                	                    'forum_name'    => $B['forum_name'],
        	                            'forum_desc'    => $B['forum_dscp'],
            	                        'topics'        => $topicCount,
                	                    'posts'         =>  $postCount,
        	                            'topic_id'      => $topicparent,
            	                        'lastpostid'    => $B['id'],
                	                    'LANGGF01_LASTPOST' => $LANG_GF01['LASTPOST'],
                	                    'quietforum'    => $quietforum,
                	                    'busyforum'     => $busyforum,
                	    ));
                        $forumlisting->parse('frow', 'forumrows',true);
        			}
        			$categorycounter++;
        			$forumlisting->set_var( 'adblock',PLG_displayAdBlock('forum_category_list',$categorycounter), false, true);
                }

                if ($numForumsDisplayed > 0 ) {
                    $forumlisting->parse('crow', 'catrows',true);
                }
            }

            if ($numCategories == 0 ) {         // Do we have any categories defined yet
                $pageBody .= '<h1 style="padding:10px; color:#F00; background-color:#000">No Categories or Forums Defined</h1>';
            }
            $forumlisting->parse ('output', 'forumlisting');
            $pageBody .= $forumlisting->finish ($forumlisting->get_var('output'));
            if ( COM_isAnonUser())  {
                $c->set($key,$pageBody,'forum');
            }
        }
        $DisplayTime = $mytimer->stopTimer();
    }
// ------ Display a specific forum
    // Display Forums
    if ($forum > 0) {
        $skipForum = false;
        if ( !_ff_canUserViewRating($forum) ) {
        	$errMsg = '<div class="pluginAlert" style="padding:10px;margin:10px;">'.$LANG_GF01['rate_too_low_forum'].'</div>';
            $page       = 1;
            $topicCount = 0;
            $numpages   = 1;
            $offset     = 0;
            $show       = 0;
            $skipForum  = true;
        } else {
        	if ($show == 0 AND $FF_userprefs['topicsperpage'] > 0) {
        	    $show = $FF_userprefs['topicsperpage'];
        	} elseif ($show == 0) {
        	    $show = 20;
        	}
        	// Check if this is the first page.
        	if ($page == 0) {
        	    $page = 1;
        	}
            $topicCount = 0;
          	$topicCount = DB_count($_TABLES['ff_topic'],array('pid','forum'),array(0,$forum));
        	$numpages = ceil($topicCount / $show);
        	$offset = ($page - 1) * $show;
        }
     	$base_url = $_CONF['site_url'] . '/forum/index.php?forum='.$forum.'&amp;show='.$show;
        $displaypostpages = '';

        $dt = new Date('now',$_USER['tzid']);

        $topiclisting = new Template( $_CONF['path'] . 'plugins/forum/templates/' );
        $topiclisting->set_file ('topiclisting','topiclisting.thtml');

        $topiclisting->set_var (array(
                        'LANG_HOME' => $LANG_GF01['HOMEPAGE'],
                        'forum_home'=> $LANG_GF01['INDEXPAGE'],
                        'navbreadcrumbsimg'     => '<img src="'._ff_getImage('nav_breadcrumbs').'" alt=""/>',
                        'img_asc1'              => '<img src="'._ff_getImage('asc').'" alt=""/>',
                        'img_asc2'              => '<img src="'._ff_getImage('asc').'" alt=""/>',
                        'img_asc3'              => '<img src="'._ff_getImage('asc').'" alt=""/>',
                        'img_asc4'              => '<img src="'._ff_getImage('asc').'" alt=""/>',
                        'img_asc5'              => '<img src="'._ff_getImage('asc').'" alt=""/>',
                        'img_desc1'             => '<img src="'._ff_getImage('desc').'" alt=""/>',
                        'img_desc2'             => '<img src="'._ff_getImage('desc').'" alt=""/>',
                        'img_desc3'             => '<img src="'._ff_getImage('desc').'" alt=""/>',
                        'img_desc4'             => '<img src="'._ff_getImage('desc').'" alt=""/>',
                        'img_desc5'             => '<img src="'._ff_getImage('desc').'" alt=""/>',
                        'tooltip_style'         => COM_getToolTipStyle()
        ));

        switch($sort) {
            case 1:
                if($order == 0) {
                    $sortOrder = "subject ASC";
                    $topiclisting->set_var ('img_asc1', '<img src="'._ff_getImage('asc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_subject',true);
                    $topiclisting->set_var ('sort_asc',true);
                    $topiclisting->set_var ('new_sort_order','1');
                } else {
                    $sortOrder = "subject DESC";
                    $topiclisting->set_var ('img_desc1', '<img src="'._ff_getImage('desc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_subject',true);
                    $topiclisting->unset_var ('sort_asc');
                    $topiclisting->set_var ('new_sort_order','0');
                }
                break;
            case 2:
                if($order == 0) {
                    $sortOrder = "views ASC";
                    $topiclisting->set_var ('img_asc2', '<img src="'._ff_getImage('asc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_views',true);
                    $topiclisting->set_var ('sort_asc',true);
                    $topiclisting->set_var ('new_sort_order','1');
                } else {
                    $sortOrder = "views DESC";
                    $topiclisting->set_var ('img_desc2', '<img src="'._ff_getImage('desc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_views',true);
                    $topiclisting->unset_var ('sort_asc');
                    $topiclisting->set_var ('new_sort_order','0');
                }
                break;
            case 3:
                if($order == 0) {
                    $sortOrder = "replies ASC";
                    $topiclisting->set_var ('img_asc3', '<img src="'._ff_getImage('asc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_replies',true);
                    $topiclisting->set_var ('sort_asc',true);
                    $topiclisting->set_var ('new_sort_order','1');
                } else {
                    $sortOrder = "replies DESC";
                    $topiclisting->set_var ('img_desc3', '<img src="'._ff_getImage('desc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_replies',true);
                    $topiclisting->unset_var ('sort_asc');
                    $topiclisting->set_var ('new_sort_order','0');
                }
                break;
            case 4:
                if($order == 0) {
                    $sortOrder = "name ASC";
                    $topiclisting->set_var ('img_asc4', '<img src="'._ff_getImage('asc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_author',true);
                    $topiclisting->set_var ('sort_asc',true);
                    $topiclisting->set_var ('new_sort_order','1');
                } else {
                    $sortOrder = "name DESC";
                    $topiclisting->set_var ('img_desc4', '<img src="'._ff_getImage('desc_on').'" alt=""/>');
                    $topiclisting->set_var ('sort_author',true);
                    $topiclisting->unset_var ('sort_asc');
                    $topiclisting->set_var ('new_sort_order','0');
                }
                break;
            case 5:
                if($order == 0) {
                    $sortOrder = "lastupdated ASC";
                    $topiclisting->set_var ('img_asc5', '<img src="' .$_CONF['site_url'] .'/forum/images/asc_on.gif" alt=""/>');
                    $topiclisting->set_var ('sort_lastupdated',true);
                    $topiclisting->set_var ('sort_asc',true);
                    $topiclisting->set_var ('new_sort_order','1');
                } else {
                    $sortOrder = "lastupdated DESC";
                    $topiclisting->set_var ('img_desc5', '<img src="' .$_CONF['site_url']. '/forum/images/desc_on.gif" alt=""/>');
                    $topiclisting->set_var ('sort_lastupdated',true);
                    $topiclisting->unset_var ('sort_asc');
                    $topiclisting->set_var ('new_sort_order','0');
                }
                break;
            default:
                $sortOrder = "lastupdated DESC";
                $topiclisting->set_var ('img_desc5', '<img src="'._ff_getImage('desc_on').'" alt=""/>');
                $sort = 0;
                $topiclisting->set_var ('sort_lastupdated',true);
                $topiclisting->unset_var ('sort_asc');
                $topiclisting->set_var ('new_sort_order','0');
                break;
        }

        $base_url .= "&amp;order=$order&amp;sort=$sort";

        // Retrieve all the Topic Records - where pid is 0 - check to see if user does not want to see anonymous posts
        if (!COM_isAnonUser() AND $FF_userprefs['viewanonposts'] == 0) {
            $sql  = "SELECT topic.*,lp.name AS lpname,lp.id AS lpid FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_topic']} lp ON topic.last_reply_rec=lp.id WHERE topic.forum = ".(int) $forum." AND topic.pid = 0 AND topic.uid > 1 ";
        } else {
            $sql  = "SELECT topic.*,lp.name AS lpname,lp.id AS lpid FROM {$_TABLES['ff_topic']} topic LEFT JOIN {$_TABLES['ff_topic']} lp ON topic.last_reply_rec=lp.id WHERE topic.forum=".(int)$forum." AND topic.pid = 0 ";
        }
        $sql .= "ORDER BY topic.sticky DESC, $sortOrder, topic.id DESC LIMIT $offset, $show";

        $topicResults = DB_query($sql);
        $totalresults = DB_numRows($topicResults);

        // Retrieve forum details and category name
        $sql  = "SELECT forum.forum_name,forum.forum_id AS forum, category.cat_name,category.id,forum.is_readonly,forum.grp_id,forum.rating_post,forum.rating_view FROM {$_TABLES['ff_forums']} forum ";
        $sql .= "LEFT JOIN {$_TABLES['ff_categories']} category on category.id=forum.forum_cat ";
        $sql .= "WHERE forum.forum_id = ".(int)$forum;

        $category = DB_fetchArray(DB_query($sql));
        if ( $totalresults < 1 && $skipForum == false ) {
        	$errMsg .= '<div class="pluginAlert" style="padding:10px;margin:10px;">'.$LANG_GF02['msg05'].'</div>';
        }
        $canPost = _ff_canPost($category);
        $subscribe = '';
        $forumsubscribed = '';
        if (!COM_isAnonUser() && $skipForum == false ) {
            // Check for user subscription status
            $sub_check = PLG_isSubscribed( 'forum', $forum, 0, $uid );
            if ($sub_check == false) {
                $subscribelinkimg = '<img src="'._ff_getImage('forumnotify_on').'" style="vertical-align:middle;" alt="'.$LANG_GF01['FORUMSUBSCRIBE'].'" title="'.$LANG_GF01['FORUMSUBSCRIBE'].'"/>';
                $subscribelink = $_CONF['site_url'].'/forum/index.php?op=subscribe&amp;forum='.$forum;
                $subcribelanguage = $LANG_GF01['FORUMSUBSCRIBE'];
                $sub_option = 'subscribe_forum';
            } else {
                $subscribelinkimg = '<img src="'._ff_getImage('forumnotify_off').'" alt="'.$LANG_GF01['FORUMUNSUBSCRIBE'].'" title="'.$LANG_GF01['FORUMUNSUBSCRIBE'].'" style="vertical-align:middle;"/>';
                $subscribelink = $_CONF['site_url'].'/forum/notify.php?filter=2';
                $subcribelanguage = $LANG_GF01['FORUMUNSUBSCRIBE'];
                $sub_option = 'unsubscribe_forum';
                $formsubscribed = TRUE;
            }
            $token = SEC_createToken();
            $topiclisting->set_var (array(
                                    'subscribelink'     => $subscribelink,
                                    'subscribelinkimg'  => $subscribelinkimg,
                                    'forumsubscribed'   => $forumsubscribed,
                                    'LANG_subscribe'    => $subcribelanguage,
                                    'forum'             => $forum,
                                    'suboption'         => $sub_option,
                                    'token'             => $token,
                                    'token_name'        => CSRF_TOKEN,
            ));
        }
        if (!COM_isAnonUser()) {
            $link = '<a href="'.$_CONF['site_url'].'/forum/index.php?op=markallread&amp;cat_id='.$category['id'].'&amp;forum_id='.(int)$forum.'">';
            $topiclisting->set_var (array(
                    'markreadurl'   => $_CONF['site_url'].'/forum/index.php?op=markallread&amp;cat_id='.$category['id'].'&amp;forum_id='.(int)$forum,
                    'markreadlink'  => $link,
                    'LANG_markread' => $LANG_GF02['msg84']
            ));
        }
        $rssFeed = DB_getItem($_TABLES['syndication'],'filename','type="forum" AND topic='.(int) $forum.' AND is_enabled=1');
        if ( ($rssFeed != '' || $rssFeed != NULL) && $skipForum == false ) {
            $baseurl = SYND_getFeedUrl();
            $imgurl  = '<img src="'._ff_getImage('rss_feed').'" alt="'.$LANG_GF01['rss_link'].'" title="'.$LANG_GF01['rss_link'].'" style="vertical-align:middle;"/>';
            $topiclisting->set_var('rssfeed','<a href="'.$baseurl.$rssFeed.'">'.$imgurl.'</a>');
            $topiclisting->set_var('rssfeed_url',$baseurl.$rssFeed);
        } else {
            $topiclisting->set_var('rssfeed','');
        }
        $topiclisting->set_var (array(
                'cat_name'          => $category['cat_name'],
                'cat_id'            => $category['id'],
                'forum_name'        => $category['forum_name'],
                'forum_id'          => $forum,
                'LANG_TOPIC'        => $LANG_GF01['TOPICSUBJECT'],
                'LANG_STARTEDBY'    => $LANG_GF01['STARTEDBY'],
                'LANG_REPLIES'      => $LANG_GF01['REPLIES'],
                'LANG_VIEWS'        => $LANG_GF01['VIEWS'],
                'LANG_LASTPOST'     => $LANG_GF01['LASTPOST'],
                'LANG_AUTHOR'       => $LANG_GF01['AUTHOR'],
                'LANG_MSG05'        => $LANG_GF01['LASTPOST'],
                'LANG_newforumposts'=> $LANG_GF02['msg113']
        ));

        if ( $canPost && $skipForum == false ) {
            $topiclisting->set_var (array(
                    'LANG_newtopic'     => $LANG_GF01['NEWTOPIC'],
                    'newtopiclinkimg'   => '<img src="'._ff_getImage('post_newtopic').'" style="vertical-align:middle;" alt="'.$LANG_GF01['NEWTOPIC'].'" title="'.$LANG_GF01['NEWTOPIC'].'"/>',
                    'newtopiclink'      => $_CONF['site_url'].'/forum/createtopic.php?mode=newtopic&amp;forum='.$forum
            ));
        }

        $bmArray = array();
        if ( !COM_isAnonUser() && $skipForum == false ) {
            $sql = "SELECT * FROM {$_TABLES['ff_bookmarks']} WHERE uid=".(int) $uid;
            $result = DB_query($sql);
            while (($bm = DB_fetchArray($result)) != NULL ) {
                $bmArray[$bm['topic_id']] = 1;
                if ( $bm['pid'] != 0 ) {
                    $bmArray[$bm['pid']] = 1;
                }
            }
        }

        $displaypostpages .= $LANG_GF01['PAGES'] .':';

        $topiclisting->set_block('topiclisting', 'topicrows', 'trow');
        $displayCount = 0;
        if ( $FF_userprefs['postsperpage'] <= 0 ) {
            $FF_userprefs['postsperpage'] = 20;
        }
        $topiccounter = 2;
//from query earlier
        while (($record = DB_fetchArray($topicResults,false)) != NULL ) {
            if ( ( $record['replies']+1 ) <= $FF_userprefs['postsperpage'] ) {
                $displaypageslink = "";
                $gotomsg = "";
            } else {
                $displaypageslink = "";
                $gotomsg = $LANG_GF02['msg85'] . "&nbsp;";
                if ($FF_userprefs['postsperpage'] > 0) {
                    $pages = ceil(($record['replies']+1)/$FF_userprefs['postsperpage']);
                } else {
                     $pages = ceil(($record['replies']+1)/20);
                }
                for ($p=1; $p <= $pages; $p++) {
                    $displaypageslink .= '<a href="'.$_CONF['site_url'].'/forum/viewtopic.php?forum='.$forum;
                    $displaypageslink .= '&amp;showtopic='.$record['id'].'&amp;show='.$FF_userprefs['postsperpage'].'&amp;page='.$p.'">';
                    $displaypageslink .= $p.'</a>';
                    if ( $p > 9 ) {
                        $displaypageslink .= '...';
                        $displaypageslink .= '<a href="'.$_CONF['site_url'].'/forum/viewtopic.php?forum='.$forum;
                        $displaypageslink .= '&amp;showtopic='.$record['id'].'&amp;show='.$FF_userprefs['postsperpage'].'&amp;page='.$pages.'">'.$pages;
                        $displaypageslink .= '</a>';
                        break;
                    }
                    $displaypageslink .= '&nbsp;';
                }
            }

            // Check if user is an anonymous poster
            if ($record['uid'] > 1) {
                $showuserlink = '<span class="replypagination">';
                $showuserlink .= '<a href="'.$_CONF['site_url'].'/users.php?mode=profile&amp;uid='.$record['uid'].'">'.$record['name'];
                $showuserlink .= '</a></span>';
            } else {
                $showuserlink= $record['name'];
            }

            if ($record['last_reply_rec'] > 0) {
                $lastreply['date'] = $record['lastupdated'];
                $lastreply['name'] = $record['lpname'];
                $dt->setTimestamp($lastreply['date']);
                $lastdate1 = $dt->format($_CONF['shortdate'],true);
                if ($dt->isToday()) {
                    $lasttime = $dt->format($_CONF['timeonly'],true);
                    $lastdate = $LANG_GF01['TODAY'] . $lasttime;
                } elseif ($_FF_CONF['allow_user_dateformat']) {
                    $lastdate = $dt->format($dt->getUserFormat(),true);
                } else {
                    $lastdate = $dt->format($_CONF['daytime'],true);
                }
            } else {
                $dt->setTimestamp($record['lastupdated']);
                $lastdate = $dt->format($_CONF['daytime'],true);
                $lastreply = $record;
            }

            $dt->setTimestamp($record['date']);
            $firstdate1 = $dt->format($_CONF['shortdate'],true);
            if ($dt->isToday() ) {
                $firsttime = $dt->format($_CONF['timeonly'],true);
                $firstdate = $LANG_GF01['TODAY'] . $firsttime;
            } elseif ($_FF_CONF['allow_user_dateformat']) {
                $firstdate = $dt->format($dt->getUserFormat(),true);
            } else {
                $firstdate = $dt->format($_CONF['daytime'],true);
            }

            if (!COM_isAnonUser()) {
                // Determine if there are new topics since last visit for this user.
                // If topic has been updated or is new - then the user will not have record for this parent topic in the log table
                $sql = "SELECT * FROM {$_TABLES['ff_log']} WHERE uid=".(int) $uid." AND topic=".(int) $record['id']." AND time > 0";
                $lsql = DB_query($sql);
                if (DB_numRows($lsql) == 0) {
                    if ($record['sticky'] == 1) {
                        $folderimg = '<img src="'._ff_getImage('sticky_new').'" style="vertical-align:middle;" alt="'.$LANG_GF02['msg115'].'" title="'.$LANG_GF02['msg115'].'"/>';
                        $folder_icon = _ff_getImage('sticky_new');
                        $folder_msg  = $LANG_GF02['msg115'];
                    } elseif ($record['locked'] == 1) {
                        $folderimg = '<img src="'._ff_getImage('locked_new').'" style="vertical-align:middle;" alt="'.$LANG_GF02['msg116'].'" title="'.$LANG_GF02['msg116'].'"/>';
                        $folder_icon = _ff_getImage('locked_new');
                        $folder_msg = $LANG_GF02['msg116'];
                    } else {
                        $folderimg = '<img src="'._ff_getImage('newposts').'" style="vertical-align:middle;" alt="'.$LANG_GF02['msg60'].'" title="'.$LANG_GF02['msg60'].'"/>';
                        $folder_icon = _ff_getImage('newposts');
                        $folder_msg = $LANG_GF02['msg60'];
                    }
                } elseif ($record['sticky'] == 1) {
                    $folderimg = '<img src="'._ff_getImage('sticky').'" style="vertical-align:middle;"alt="'.$LANG_GF02['msg61'].'" title="'.$LANG_GF02['msg61'].'"/>';
                    $folder_icon = _ff_getImage('sticky');
                    $folder_msg = $LANG_GF02['msg61'];
                } elseif ($record['locked'] == 1) {
                    $folder_icon = _ff_getImage('locked');
                    $folder_msg = $LANG_GF02['msg114'];
                    $folderimg = '<img src="'._ff_getImage('locked').'" style="vertical-align:middle;"alt="'.$LANG_GF02['msg114'].'" title="'.$LANG_GF02['msg114'].'"/>';
                } else {
                    $folderimg = '<img src="'._ff_getImage('noposts').'" style="vertical-align:middle;"alt="'.$LANG_GF02['msg59'].'" title="'.$LANG_GF02['msg59'].'"/>';
                    $folder_icon = _ff_getImage('noposts');
                    $folder_msg = $LANG_GF02['msg59'];
                }
                if (isset($bmArray[$record['id']]) ) {
                    $topiclisting->set_var('bookmark_icon','<img src="'._ff_getImage('star_on_sm').'" title="'.$LANG_GF02['msg204'].'" alt=""/>');
                    $topiclisting->set_var('bookmarked',true);
                } else {
                    $topiclisting->set_var('bookmark_icon','<img src="'._ff_getImage('star_off_sm').'" title="'.$LANG_GF02['msg203'].'" alt=""/>');
                    $topiclisting->unset_var('bookmarked');
                }
            } elseif ($record['sticky'] == 1) {
                $folderimg = '<img src="'._ff_getImage('sticky').'" style="vertical-align:middle;" alt="'.$LANG_GF02['msg61'].'" title="'.$LANG_GF02['msg61'].'"/>';
                $folder_icon = _ff_getImage('sticky');
                $folder_msg = $LANG_GF02['msg61'];
            } elseif ($record['locked'] == 1) {
                $folderimg = '<img src="'._ff_getImage('locked').'" style="vertical-align:middle;" alt="'.$LANG_GF02['msg114'].'" title="'.$LANG_GF02['msg114'].'"/>';
                $folder_icon = _ff_getImage('locked');
                $folder_msg = $LANG_GF02['msg114'];
            } else {
               $folderimg = '<img src="'._ff_getImage('noposts').'" style="vertical-align:middle;" alt="'.$LANG_GF02['msg59'].'" title="'.$LANG_GF02['msg59'].'"/>';
               $folder_icon = _ff_getImage('noposts');
               $folder_msg = $LANG_GF02['msg59'];
            }

            $lastposter = $lastreply['name'];

            $moved = '';
            if($record['moved'] == 1){
                $moved = "{$LANG_GF01['MOVED']}: ";
            }
            $subject = COM_truncate($record['subject'],$_FF_CONF['show_subject_length'],'...');
            if ($_FF_CONF['use_censor']) {
                $subject = COM_checkWords($subject);
                $record['subject'] = COM_checkWords($record['subject']);
            }
            if ( $record['attachments'] > 0 ) {
                $subject = $subject . '&nbsp;<img src="'.$_CONF['site_url'].'/forum/images/document_sm.gif" alt=""/>';
            }
            $firstposterName = $record['name'];

            $topicinfo  = htmlspecialchars($record['subject']).'::'.htmlspecialchars(preg_replace('#\r?\n#','<br/>',COM_truncate(strip_tags($record['comment']),$_FF_CONF['contentinfo_numchars'],'...',false)));

            $topiclisting->set_var (array(
                    'folderimg'     => $folderimg,
                    'folder_icon'   => $folder_icon,
                    'folder_msg'    => $folder_msg,
                    'topicinfo'     => $topicinfo,
                    'topic_id'      => $record['id'],
                    'subject'       => $subject,
                    'author'        => $record['uid'] > 1 ? '<a href="' . $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $record['uid'] . '">' . $record['name'] . '</a>' : $record['name'],
                    'fullsubject'   => $record['subject'],
                    'gotomsg'       => $gotomsg,
                    'displaypageslink'  => $displaypageslink,
                    'showuserlink'  => $showuserlink,
                    'lastposter'    => $lastposter,
                    'LANG_lastpost' => $LANG_GF02['msg188'],
                    'moved'         => $moved,
                    'views'         => $record['views'],
                    'replies'       => $record['replies'],
                    'lastdate'      => $lastdate,
                    'lastpostid'    => $record['lpid'], // $lastreply['id'],
                    'LANG_BY'       => $LANG_GF01['BY'],
                    'startdate'     => $firstdate,
            ));
            $topiclisting->parse('trow', 'topicrows',true);
            $displayCount++;
    		$topiclisting->set_var( 'adblock',PLG_displayAdBlock('forum_topic_list',$topiccounter), false, true);
       	    $topiccounter++;
        }
        $topiclisting->set_var ('pagenavigation', forum_pagination($base_url,$page, $numpages));
        $topiclisting->set_var ('page',$page);
        $topiclisting->set_var ('num_pages',$numpages);

        if ( $displayCount > 0 ) {
            $topiclisting->set_var('records_displayed',true);
        }

        $DisplayTime = $mytimer->stopTimer();
        $topiclisting->set_var ('page_generated_time', sprintf($LANG_GF02['msg179'],$DisplayTime));

        if ( $errMsg != '' ) {
            $topiclisting->set_var('no_topics_message',$errMsg);
        }
        $topiclisting->parse ('output', 'topiclisting');

        $pageBody .= $topiclisting->finish ($topiclisting->get_var('output'));
    }

    $display  = FF_siteHeader($LANG_GF01['INDEXPAGE']);
    $display .= FF_ForumHeader($forum,0);

    $display .= $pageBody;

    $display .= FF_BaseFooter();
    $display .= FF_siteFooter();
    echo $display;
    exit;
}

// Remove unnecessary words from the search term and return them as an array
function ff_filterSearchKeys($query)
{
    $query = trim(preg_replace("/(\s+)+/", " ", $query));
    $words = array();
    // expand this list with your words.
    $list = array("in","it","a","the","of","or","I","you","he","me","us","they","she","to","but","that","this","those","then", "and", "or");
    $c = 0;
    foreach (explode(" ", $query) as $key) {
        if (in_array($key, $list)) {
            continue;
        }
        $words[] = $key;
        if ($c >= 15) {
            break;
        }
        $c++;
    }
    return $words;
}

// limit words number of characters
function ff_limitChars($query, $limit = 200)
{
    return substr($query, 0,$limit);
}

?>