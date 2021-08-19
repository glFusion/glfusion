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
include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';
include_once $_CONF['path'].'plugins/filemgmt/include/xoopstree.php';
include_once $_CONF['path'].'plugins/filemgmt/include/textsanitizer.php';

$_GROUPS = SEC_getUserGroups( $uid );       // List of groups user is a member of
$numCategoriesPerRow  = 6;

$myts = new MyTextSanitizer;
$mytree = new XoopsTree('',$_TABLES['filemgmt_cat'],'cid','pid');
$mytree->setGroupAccessFilter($_GROUPS);

$page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
$cid  = isset($_GET['cid']) ? COM_applyFilter($_GET['cid'],true) : 0;
$orderby  = isset($_GET['orderby']) ? @html_entity_decode(COM_applyFilter($_GET['orderby'],false)) : '';

$groupsql = filemgmt_buildAccessSql();
$sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_cat']} WHERE cid='".intval($cid)."' $groupsql";
list($category_rows) = DB_fetchArray( DB_query($sql));
if ($cid == 0 OR $category_rows == 0) {
    echo COM_refresh($_CONF['site_url'] . '/filemgmt/index.php');
    exit;
}

$FM_ratedIds = array();
$FM_ratedIds = RATING_getRatedIds('filemgmt');


$display = FM_siteHeader($LANG_FILEMGMT['usermenu1']);
$p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
$p->set_file (array (
    'page'             =>     'filelisting.thtml',
    //'records'          =>     'filelisting_record.thtml',
    'category'         =>     'filelisting_subcategory.thtml',
    'sortmenu'         =>     'sortmenu.thtml'));

$p->set_var ('tablewidth', $mydownloads_shotwidth+10);
$p->set_var('block_header', COM_startBlock(_MD_CATEGORYTITLE));
$p->set_var('block_footer', COM_endBlock());

$trimDescription=true;    // Set to false if you do not want to auto trim the description and insert the <more..> link

if (!isset($page) || $page == 0) {
    // If no page sent then assume the first.
    $page = 1;
}
$show = $mydownloads_perpage;
$offset = ($page - 1) * $show;

if(isset($orderby) && $orderby != "") {
    $orderby = convertorderbyin($orderby);
} else {
    $orderby = "date DESC";
}

$pathstring = "<a href='index.php'>"._MD_MAIN."</a>&nbsp;:&nbsp;";
$nicepath = $mytree->getNicePathFromId($cid, "title", "{$_CONF['site_url']}/filemgmt/viewcat.php");
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
    $totalfiles = $totalfiles + getTotalItems($ele['cid'], 1);
    $subcategories = '<a href="' .$_CONF['site_url'] .'/filemgmt/viewcat.php?cid=' .$ele['cid'] .'">' .$chtitle .'</a>&nbsp;('.$totalfiles.')&nbsp;&nbsp;';
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
$query = DB_query("SELECT cid from  {$_TABLES['filemgmt_cat']} where pid='".intval($cid)."'");
$categories = $cid;
while( list($category) = DB_fetchArray($query)) {
    $categories = $categories . ",$category";
}
$sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
$sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
//$sql .= "WHERE a.cid in ($categories) AND status > 0 $groupsql";
$sql .= "WHERE a.cid=".intval($cid)." AND status > 0 $groupsql";
list($maxrows) = DB_fetchArray(DB_query($sql));
$numpages = ceil($maxrows / $show);

/*
$sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
$sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
$sql .= "WHERE a.cid = $cid AND status > 0 $groupsql";
list($maxrows) = DB_fetchArray(DB_query($sql));
$numpages = ceil($maxrows / $show);
*/

if($maxrows > 0) {
    $sql  = "SELECT a.lid, a.cid, a.title, a.url, a.homepage, a.version, a.size, a.submitter, a.logourl, a.status, a.date, a.hits, a.rating, a.votes, a.comments, b.description ";
    $sql .= "FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN  {$_TABLES['filemgmt_filedesc']} b on a.lid=b.lid ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} c ON a.cid=c.cid ";
    $sql .= "WHERE a.cid='".intval($cid)."' AND a.status > 0 $groupsql ORDER BY {$orderby} LIMIT $offset, $show";
    $result = DB_query($sql);

    $numrows = DB_numROWS($result);

    //if 2 or more items in result, show the sort menu
    if($maxrows > 1){
        $p->set_var('LANG_SORTBY',_MD_SORTBY);
        $p->set_var('LANG_TITLE',_MD_TITLE);
        $p->set_var('LANG_DATE',_MD_DATE);
        $p->set_var('LANG_RATING',_MD_RATING);
        $p->set_var('LANG_POPULARITY',_MD_POPULARITY);
        $p->set_var('LANG_CURSORTBY',_MD_CURSORTBY);
        $p->set_var('orderbyTrans',$orderbyTrans = convertorderbytrans($orderby));
        $p->parse ('sort_menu', 'sortmenu');
    }
    $cssid = 1;
    $p->set_block('page', 'fileRecords', 'fRecord');
    for ($x = 1; $x <= $numrows; $x++) {
        $A = DB_fetchArray($result, false);
        $D = new Filemgmt\Download($A['lid']);
        $p->set_var('filelisting_record', $D->showListingRecord());
        $p->parse('fRecord', 'fileRecords', true);
        continue;

        list($lid, $cid, $dtitle, $url, $homepage, $version, $size, $submitter, $logourl, $status, $time, $hits, $rating, $votes, $comments, $description) = DB_fetchArray($result);
        $rating = number_format($rating, 2);
        $dtitle = $myts->makeTboxData4Show($dtitle);
        $url = $myts->makeTboxData4Show($url);
        $homepage = $myts->makeTboxData4Show($homepage);
        $version = $myts->makeTboxData4Show($version);
        $size = $myts->makeTboxData4Show($size);
        $logourl = $myts->makeTboxData4Show($logourl);
        $datetime = formatTimestamp($time);
        $description = PLG_replaceTags($myts->makeTareaData4Show($description,0)); //no html
        $result2 = DB_query("SELECT username,fullname,photo  FROM {$_TABLES['users']} WHERE uid = $submitter");
        list ($submitter_name,$submitter_fullname,$photo) = DB_fetchARRAY($result2);
        $submitter_name = COM_getDisplayName ($submitter, $submitter_name, $submitter_fullname);
        include $_CONF['path'] .'plugins/filemgmt/include/dlformat.php';
        $p->set_var('cssid',$cssid);
        //$p->parse ('filelisting_records', 'records',true);
        $cssid = ($cssid == 2) ? 1 : 2;

        // Print Google-like paging navigation
        $base_url = $_CONF['site_url'] . '/filemgmt/viewcat.php?cid='.$cid.'&amp;orderby='.convertorderbyout($orderby);
        $p->set_var('page_navigation', COM_printPageNavigation($base_url,$page, $numpages));
    }

    $base_url = $_CONF['site_url'] . '/filemgmt/viewcat.php?cid='.$cid.'&amp;orderby='.convertorderbyout($orderby);
    $p->set_var('page_navigation', COM_printPageNavigation($base_url,$page, $numpages));
    $p->parse ('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
}  else {
    $p->set_var('filelisting_records','<tr><td><div class="pluginAlert" style="width:500px;padding:10px;margin:10px;border:1px dashed #CCC;">'._MD_NOFILES.'</div></td></tr>');
    $p->parse ('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
}

$display .= FM_siteFooter();
echo $display;

?>
