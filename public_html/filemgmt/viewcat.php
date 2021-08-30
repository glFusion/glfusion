<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | viewcat.php                                                              |
// |                                                                          |
// | Displays downloads in a specific category                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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

require_once '../lib-common.php';
use \glFusion\Database\Database;

$display = '';
$numCategoriesPerRow  = 6;

$myts = new Filemgmt\MyTextSanitizer;
$mytree = new Filemgmt\XoopsTree('',$_TABLES['filemgmt_cat'],'cid','pid');
$mytree->setGroupAccessFilter($_GROUPS);
$db = Database::getInstance();

$page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 1;
if ($page < 1) {
    $page = 1;
}
$cid  = isset($_GET['cid']) ? COM_applyFilter($_GET['cid'],true) : 0;
$orderby  = isset($_GET['orderby']) ? @html_entity_decode(COM_applyFilter($_GET['orderby'],false)) : 'date';
if (!in_array($orderby, array('date', 'title', 'hits', 'rating'))) {
    $orderby = 'date';
}
if (isset($_GET['dir'])) {
    $sortdir = $_GET['dir'] == 'asc' ? ' asc' : ' desc';
} else {
    $sortdir = 'desc';
}

$groupsql = SEC_buildAccessSql();
try {
    $category_rows = $db->conn->fetchColumn("SELECT COUNT(*) AS num_rows FROM {$_TABLES['filemgmt_cat']} WHERE cid = $cid $groupsql");
} catch(Throwable $e) {
    if (defined('DVLP_DEBUG')) {
        throw($e);
    }
    exit;
}
if ($cid == 0 || $category_rows == 0) {
    echo COM_refresh($_FM_CONF['url'] . '/index.php');
    exit;
}

$p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
$p->set_file (array (
    'page'             =>     'filelisting.thtml',
    //'records'          =>     'filelisting_record.thtml',
    'category'         =>     'filelisting_subcategory.thtml',
    'sortmenu'         =>     'sortmenu.thtml'));

$p->set_var ('tablewidth', $_FM_CONF['shotwidth'] + 10);
$p->set_var('block_header', COM_startBlock(_MD_CATEGORYTITLE));
$p->set_var('block_footer', COM_endBlock());

$trimDescription=true;    // Set to false if you do not want to auto trim the description and insert the <more..> link

$show = (int)$_FM_CONF['perpage'];
$offset = ($page - 1) * $show;

$pathstring = "<a href='index.php'>"._MD_MAIN."</a>&nbsp;:&nbsp;";
$nicepath = $mytree->getNicePathFromId($cid, "title", "{$_FM_CONF['url']}/viewcat.php");
$pathstring .= $nicepath;

$p->set_var('category_path_link',$pathstring);
$p->set_var('cid',$cid);

// get child category objects
$subcategories = '';
$arr=array();
$arr=$mytree->getFirstChild($cid, 'title');

$count = 1;
$eor   = 0;
foreach($arr as $ele) {
    $totalfiles = 0;
    //debugbreak();
    $chtitle=$myts->makeTboxData4Show($ele['title']);
    $totalfiles = $totalfiles + Filemgmt\Download::getTotalByCategory($ele['cid'], 1);
    $subcategories = '<a href="' .$_FM_CONF['url'] .'/viewcat.php?cid=' .$ele['cid'] .'">' .$chtitle .'</a>&nbsp;('.$totalfiles.')&nbsp;&nbsp;';
    $p->set_var('subcategories',$subcategories);
    if ($count == $numCategoriesPerRow) {
        $p->set_var('end_of_row','</tr>');
        $p->parse ('category_records', 'category',true);
        $p->set_var('new_table_row','<tr>');
        $count = 1;
        $subcategories = '';
        $eor = 1;
    } else {
        $p->set_var('end_of_row','');
        $p->parse ('category_records', 'category',true);
        $p->set_var('new_table_row','');
        $eor = 0;
        $count++;
    }
}
if ( $eor == 0 ) {
    $p->set_var('final_end_row','</tr>');
}

// Get a list of subcategories for this category
try {
    $stmt = $db->conn->prepare("SELECT cid FROM {$_TABLES['filemgmt_cat']} WHERE pid = ?");
    $stmt->bindParam(1, $cid, Database::INTEGER);
    $stmt->execute();
} catch(Throwable $e) {
    if (defined('DVLP_DEBUG')) {
        throw($e);
    }
    exit;
}
if ($stmt) {
    $results = $stmt->fetchAll(Database::ASSOCIATIVE);
}
$categories = $cid;
foreach ($results as $A) {
    $categories .= ",{$A['cid']}";
}

try {
    $stmt = $db->conn->prepare(
        "SELECT COUNT(*) as num_rows FROM {$_TABLES['filemgmt_filedetail']} a
        LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid
        WHERE a.cid IN ($categories) AND status > 0 $groupsql"
    );
    $stmt->execute();
    $maxrows = $stmt->fetchColumn();
} catch(Throwable $e) {
    if (defined('DVLP_DEBUG')) {
        throw($e);
    }
    exit;
}
$numpages = ceil($maxrows / $show);

if($maxrows > 0) {
    try {
        $stmt = $db->conn->prepare(
            "SELECT a.*, b.description
                FROM {$_TABLES['filemgmt_filedetail']} a
                LEFT JOIN  {$_TABLES['filemgmt_filedesc']} b on a.lid=b.lid
                LEFT JOIN {$_TABLES['filemgmt_cat']} c ON a.cid=c.cid
                WHERE a.cid IN ($categories)
                AND a.status > 0 $groupsql
                ORDER BY a.$orderby $sortdir
                LIMIT :offset, :show"
        );
        $stmt->bindParam('offset', $offset, Database::INTEGER);
        $stmt->bindParam('show', $show, Database::INTEGER);
        $stmt->execute();
        $records = $stmt->fetchAll(Database::ASSOCIATIVE);
    } catch(Throwable $e) {
        if (defined('DVLP_DEBUG')) {
            throw($e);
        }
        exit;
    }

    switch ($orderby) {
    case 'hits':
        $orderbyTrans = $sortdir == 'asc' ? _MD_POPULARITYLTOM : _MD_POPULARITYMTOL;
        break;
    case 'title':
        $orderbyTrans = $sortdir == 'asc' ? _MD_TITLEATOZ : _MD_TITLEZTOA;
        break;
    case 'date':
        $orderbyTrans = $sortdir == 'asc' ? _MD_DATEOLD : _MD_DATENEW;
        break;
    case 'rating asc':
        $orderbyTrans = $sortdir == 'asc' ? _MD_RATINGLTOH : _MD_RATINGHTOL;
        break;
    default:
        $orderbyTrans = _MD_TITLEATOZ;
        break;
    }

    //if 2 or more items in result, show the sort menu
    if($maxrows > 1){
        $p->set_var('LANG_SORTBY',_MD_SORTBY);
        $p->set_var('LANG_TITLE',_MD_TITLE);
        $p->set_var('LANG_DATE',_MD_DATE);
        $p->set_var('LANG_RATING',_MD_RATING);
        $p->set_var('LANG_POPULARITY',_MD_POPULARITY);
        $p->set_var('LANG_CURSORTBY',_MD_CURSORTBY);
        $p->set_var('orderbyTrans',$orderbyTrans);
        $p->parse('sort_menu', 'sortmenu');
    }

    $cssid = 1;
    $p->set_block('page', 'fileRecords', 'fRecord');
    foreach ($records as $A) {
        $D = new Filemgmt\Download($A);
        $p->set_var('filelisting_record', $D->showListingRecord());
        $p->parse('fRecord', 'fileRecords', true);
    }

    $base_url = $_FM_CONF['url'] . "/viewcat.php?cid=$cid&amp;orderby=$orderby&amp;dir=$sortdir";
    $p->set_var('page_navigation', COM_printPageNavigation($base_url,$page, $numpages));
}  else {
    $p->set_var('no_files', true);
    $p->set_var('lang_nofiles', _MD_NOFILES);
}
$p->parse ('output', 'page');
$display .= $p->finish ($p->get_var('output'));

echo Filemgmt\Menu::siteHeader($LANG_FILEMGMT['usermenu1']);
echo $display;
echo Filemgmt\Menu::siteFooter();
