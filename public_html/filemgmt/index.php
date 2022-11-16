<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* Primary user interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by Consult4Hire Inc.
*  Author:
*  Blaine Lang          blaine AT portalparts DOT com
*
*/

require_once '../lib-common.php';
use glFusion\Database\Database;
use glFusion\Log\Log;

if (!in_array('filemgmt', $_PLUGINS)) {
    COM_404();
    exit;
}

// Setup how many categories you want to show in the category row display
$numCategoriesPerRow   = (int)$_FM_CONF['numcategoriesperrow'];
$numSubCategories2Show = (int)$_FM_CONF['numsubcategories2show'];

if ( COM_isAnonUser() && $_FM_CONF['selectpriv'] == 1 )  {
    $display = Filemgmt\Menu::siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= Filemgmt\Menu::siteFooter();
    echo $display;
    exit;
}

$display = '';
$FM_ratedIds = array();
$FM_ratedIds = RATING_getRatedIds('filemgmt');

$p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
$p->set_file (array (
    'page'             =>     'filelisting.thtml',
    'category'         =>     'filelisting_category.thtml'
));

$myts = new Filemgmt\MyTextSanitizer;
$mytree = new Filemgmt\XoopsTree('',$_TABLES['filemgmt_cat'],"cid","pid");
$mytree->setGroupAccessFilter($_GROUPS);

COM_setArgNames( array('id') );
$lid = COM_applyFilter(COM_getArgument( 'id' ),true);
if ($lid == 0) {  // Check if the script is being called from the commentbar
    if (isset($_GET['id'])) {
        $lid = (int)str_replace('fileid_','',$_GET['id']);
    } elseif ( isset($_POST['id']) ) {
        $lid = (int)str_replace('fileid_','',$_POST['id']);
    }
}

$db = Database::getInstance();
$groupsql = $db->getAccessSql('');

if ($lid > 0) {
    $File = Filemgmt\Download::getInstance($lid);

    if (!$File->canRead()) {
        COM_404();
    }

    $p->set_var(array(
        'block_header' => COM_startBlock("<b>". $LANG_FILEMGMT['plugin_name'] ."</b>"),
        'block_footer' => COM_endBlock(),
        'cssid' => 1,
        'comment_records' => $File->showComments(),
        'back_to_list' => true,
    ) );
    $p->set_block('page', 'fileRecords', 'fRecord');
    $p->set_var('filelisting_record', $File->showListingRecord());
    $p->parse('fRecord', 'fileRecords');

    $cid = $File->getCid();

    $pathstring = "<li><a href='{$_FM_CONF['url']}/index.php'>"._MD_MAIN."</a></li>";
    $nicepath = $mytree->getNicePathFromId($cid, "title", "{$_FM_CONF['url']}/viewcat.php");
    $pathstring .= $nicepath;

    $p->set_var('category_path_link',$pathstring);


    $p->parse('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
} else {

    $qb = $db->conn->createQueryBuilder();

    // No file specified, show the listing.
    $p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
    $p->set_file(array (
        'page'             =>     'filelisting.thtml',
        'category'         =>     'filelisting_category.thtml',
    ));

    $p->set_var('tablewidth', $_FM_CONF['shotwidth'] + 10);

// determine if user can submit files and that there are valid categories to submit to...
    $p->set_var('can_submit',false);
    if (Filemgmt\Download::canSubmit()) {
        $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
        $sql .= "AND " . $groupsql;
        list($catAccessCnt) = DB_fetchArray( DB_query($sql));
        if ( $catAccessCnt < 1 ) {
            $p->unset_var('can_submit');
        } else {
            $p->set_var('can_submit',true);
            if (SEC_hasRights("filemgmt.admin")) {
                $p->set_var('submit_url', $_CONF['site_admin_url'].'/plugins/filemgmt/index.php?modDownload=0');
            } else {
                $p->set_var('submit_url', $_CONF['site_url'].'/filemgmt/submit.php');
            }
        }
    }

    $p->set_var('lang_categories',_MD_CATEGORIES);

    $page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
    if ($page < 1) {
        $page = 1;
    }
    $show = (int)$_FM_CONF['perpage'];

    $sql = "SELECT cid, title, imgurl,grp_access FROM {$_TABLES['filemgmt_cat']} WHERE pid = 0 ";
    $sql .= 'AND ' . $groupsql . ' ORDER BY CID';
    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    $columns = 1;

    if ($nrows > 2) {
        $columns = 3;
    } elseif ($nrows > 1) {
        $columns = 2;
    } elseif ($nrows > 0) {
        $columns = 1;
    }
 
    $p->set_var('columns',$columns);
 
    // Need to use a SQL stmt that does a join on groups user has access to  - for file count
    $sql  = "SELECT count(*)  FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid WHERE status > 0 ";
    $sql .= ' AND ' . $groupsql;
    $countsql = DB_query($sql);
    list($maxrows) = DB_fetchArray($countsql);
    $numpages = ceil($maxrows / $show);

    $p->set_var('block_header', COM_startBlock(sprintf(_MD_LISTINGHEADING,$maxrows)));
    $p->set_var('block_footer', COM_endBlock());
    $count = 0;

    if ($nrows > 0) {
        // Display the categories - Top Level (plus #files) with links to sub categories
        for ($i = 1; $i <= $nrows; $i++) {
            $myrow = DB_fetchArray($result);
            $secGroup = DB_getItem($_TABLES['groups'], "grp_name", "grp_id='{$myrow['grp_access']}'");
            if (SEC_inGroup($secGroup)) {
                $p->set_var('cid',$myrow['cid']);
                $p->set_var('category_name',$myts->makeTboxData4Show($myrow['title']));

                if ( $_FM_CONF['useshots'] && $myrow['imgurl'] && $myrow['imgurl'] != "http://") {
                    $imgurl = $myts->makeTboxData4Edit($myrow['imgurl']);
                    $p->set_var(array(
                        'cat_img_url' => $_FM_CONF['SnapCatURL'] . $imgurl,
                        'shotwidth' => $_FM_CONF['shotwidth'],
                    ) );
                } else {
                    $p->set_var('cat_img_url','');
                }

                $downloadsWaitingSubmission = Filemgmt\Download::getTotalByCategory($myrow['cid'], 0);
                $p->set_var('num_files', Filemgmt\Download::getTotalByCategory($myrow['cid'], 1));
                $p->set_var('files_waiting_submission', $downloadsWaitingSubmission);

                // get child category objects
                $subcategories = '';
                $arr=array();
                $arr=$mytree->getFirstChild($myrow['cid'], 'title');
                $space = 0;
                $chcount = 0;
                foreach($arr as $ele) {
                    $chtitle=$myts->makeTboxData4Show($ele['title']);
                    if ($chcount >= $numSubCategories2Show) {
                        if ($numSubCategories2Show != 0) {
                            $subcategories .= "...";
                        }
                        break;
                    }
                    if ($space>0) {
                        $subcategories .= ", ";
                    }
                    $subcategories .= "<a href=\"{$_FM_CONF['url']}/viewcat.php?cid={$ele['cid']}\">{$chtitle}</a>";
                    $space++;
                    $chcount++;
                }
                $p->set_var('subcategories',$subcategories);
                $count++;
                if ($count == $numCategoriesPerRow) {
                    $p->set_var('end_of_row','</tr>');
                    $p->parse ('category_records', 'category',true);
                    $p->set_var('new_table_row','<tr>');
                    $count = 0;
                } else {
                    $p->set_var('end_of_row','');
                    $p->parse ('category_records', 'category',true);
                    $p->set_var('new_table_row','');
                }
            }
        }
    }
    if ($count != $numCategoriesPerRow  && $count != 0) {
        $p->set_var('final_end_row','</tr>');
    }
    $offset = ($page - 1) * $show;

    try {
        $qb->select('d.*', 't.description', 'c.grp_access')
           ->from($_TABLES['filemgmt_filedetail'], 'd')
           ->leftJoin('d', $_TABLES['filemgmt_cat'], 'c', 'd.cid=c.cid')
           ->leftJoin('d', $_TABLES['filemgmt_filedesc'], 't', 't.lid = d.lid')
           ->where('d.status > 0')
           ->andWhere('d.lid = t.lid')
           ->orderBy('date', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($show);
        if ($_USER['uid'] > 1) {
            $qb->andWhere('(c.submitterview = 1 AND d.submitter = :submitter) OR ' . $groupsql)
               ->setParameter('submitter', $_USER['uid'], Database::INTEGER);
        } else {
            $qb->andWhere($groupsql);
        }
        $stmt = $qb->execute();
        $numrows = $stmt->rowCount();
    } catch (\Throwable $e) {
        Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        $stmt = false;
        $numrows = 0;
    }

    if ($stmt && $numrows) {
        $countsql = DB_query("SELECT COUNT(*) FROM ".$_TABLES['filemgmt_filedetail']." WHERE status > 0");
        $p->set_var('listing_heading', _MD_LATESTLISTING);
        $p->set_block('page', 'fileRecords', 'fRecord');
        while ($A = $stmt->fetchAssociative()) {
            $D = new Filemgmt\Download($A);
            $p->set_var('filelisting_record', $D->showListingRecord());
            $p->parse('fRecord', 'fileRecords', true);
        }

        // Print Google-like paging navigation
        $p->set_var(
            'page_navigation',
            COM_printPageNavigation($_FM_CONF['url'] . '/index.php', $page, $numpages)
        );
        $p->unset_var('no_files');
    } else {
        $p->set_var('no_files', true);
        $p->set_var('lang_nofiles', _MD_NOFILES);        
    }        

    $p->parse ('output', 'page');
    $display .= $p->finish($p->get_var('output'));
}
echo Filemgmt\Menu::siteHeader($LANG_FILEMGMT['usermenu1']);
echo $display;
echo Filemgmt\Menu::siteFooter();
