<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Main public script to view filemgmt categories and files                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

// Setup how many categories you want to show in the category row display
$numCategoriesPerRow   = $_FM_CONF['numcategoriesperrow'];
$numSubCategories2Show = $_FM_CONF['numsubcategories2show'];

if ( COM_isAnonUser() && $mydownloads_publicpriv != 1 )  {
    $display = FM_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= FM_siteFooter();
    echo $display;
    exit;
}
$FM_ratedIds = array();
$FM_ratedIds = RATING_getRatedIds('filemgmt');

$p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
$p->set_file (array (
    'page'             =>     'filelisting.thtml',
    'records'          =>     'filelisting_record.thtml',
    'category'         =>     'filelisting_category.thtml'));

$myts = new MyTextSanitizer;
$mytree = new XoopsTree('',$_TABLES['filemgmt_cat'],"cid","pid");
$mytree->setGroupAccessFilter($_GROUPS);

COM_setArgNames( array('id') );
$lid = COM_applyFilter(COM_getArgument( 'id' ),true);

if ($lid == 0) {  // Check if the script is being called from the commentbar
    if ( isset($_GET['id']) ) {
        $lid = intval(str_replace('fileid_','',$_GET['id']));
    } elseif ( isset($_POST['id']) ) {
        $lid = intval(str_replace('fileid_','',$_POST['id']));
    }
}

$groupsql = filemgmt_buildAccessSql();

$sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_filedetail']} a ";
$sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
$sql .= "WHERE a.lid='".DB_escapeString($lid)."' $groupsql AND a.status > 0";
list($fileAccessCnt) = DB_fetchArray( DB_query($sql));

if ($fileAccessCnt > 0 AND DB_count($_TABLES['filemgmt_filedetail'],"lid",DB_escapeString($lid) ) == 1) {

    $p->set_var('block_header', COM_startBlock("<b>". $LANG_FILEMGMT['plugin_name'] ."</b>"));
    $p->set_var('block_footer', COM_endBlock());

    USES_lib_comment();

    $sql = "SELECT d.lid, d.cid, d.title, d.url, d.homepage, d.version, d.size, d.logourl, d.submitter, d.status, d.date, ";
    $sql .= "d.hits, d.rating, d.votes, d.comments, t.description FROM {$_TABLES['filemgmt_filedetail']} d, ";
    $sql .= "{$_TABLES['filemgmt_filedesc']} t WHERE d.lid='".DB_escapeString($lid)."' AND d.lid=t.lid AND status > 0";

    $result = DB_query($sql);
    list($lid, $cid, $dtitle, $url, $homepage, $version, $size, $logourl, $submitter, $status, $time, $hits, $rating, $votes, $comments, $description) = DB_fetchArray($result);
    $display = FM_siteHeader($dtitle);
    $pathstring = "<a href='{$_CONF['site_url']}/filemgmt/index.php'>"._MD_MAIN."</a>&nbsp;:&nbsp;";
    $nicepath = $mytree->getNicePathFromId($cid, "title", "{$_CONF['site_url']}/filemgmt/viewcat.php");
    $pathstring .= $nicepath;
    $p->set_var('category_path_link',$pathstring);

    $rating = number_format($rating, 2);
    $dtitle = $myts->makeTboxData4Show($dtitle);
    $url = $myts->makeTboxData4Show($url);
    $homepage = $myts->makeTboxData4Show($homepage);
    $version = $myts->makeTboxData4Show($version);
    $size = $myts->makeTboxData4Show($size);
    $platform = $myts->makeTboxData4Show(isset($platform) ? $platform : '');
    $logourl = $myts->makeTboxData4Show($logourl);
    $datetime = formatTimestamp($time);
    $description = PLG_replaceTags($myts->makeTareaData4Show($description,0),'filemgmt','description'); //no html
    $result2 = DB_query("SELECT username,fullname,photo FROM {$_TABLES['users']} WHERE uid = $submitter");
    list ($submitter_name,$submitter_fullname,$photo) = DB_fetchArray($result2);
    $submitter_name = COM_getDisplayName ($submitter, $submitter_name, $submitter_fullname);
    include $_CONF['path'] .'plugins/filemgmt/include/dlformat.php';

    $p->set_var('cssid',1);
    $p->parse ('filelisting_records', 'records');
    if (SEC_hasRights('filemgmt.edit')) {
        $delete_option = true;
    } else {
        $delete_option = false;
    }
    $title = $dtitle;
    if ( !isset($title)) {
        $title = '';
    }

    if ( $comments ) {
        $cmt_page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 1;
        if ( isset($_POST['order']) ) {
            $cmt_order  =  $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';
        } elseif (isset($_GET['order']) ) {
            $cmt_order =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
        } else {
            $cmt_order = '';
        }
        if ( isset($_POST['mode']) ) {
            $cmt_mode = COM_applyFilter($_POST['mode']);
        } elseif ( isset($_GET['mode']) ) {
            $cmt_mode = COM_applyFilter($_GET['mode']);
        } else {
            $cmt_mode = '';
        }
        $valid_cmt_modes = array('flat','nested','nocomment','threaded','nobar');
        if ( !in_array($cmt_mode,$valid_cmt_modes) ) {
            $cmt_mode = '';
        }

        $p->set_var('comment_records', CMT_userComments( "fileid_{$lid}", $title, 'filemgmt',$cmt_order,$cmt_mode,0,$cmt_page,false,$delete_option,0,$submitter));
    } else {
        $p->set_var('comment_records','');
    }
    $p->parse ('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
} else {
    $display = FM_siteHeader($LANG_FILEMGMT['usermenu1']);
    $p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
    $p->set_file (array (
        'page'             =>     'filelisting.thtml',
        'records'          =>     'filelisting_record.thtml',
        'category'         =>     'filelisting_category.thtml'));

    $p->set_var ('imgset',$_CONF['layout_url'] . '/nexflow/images');
    $p->set_var ('tablewidth', $mydownloads_shotwidth+10);

    $page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
    if (!isset($page) OR $page == 0) {
        $page = 1;
    }
    $show = $mydownloads_perpage;

    $groupsql = filemgmt_buildAccessSql();
    $sql = "SELECT cid, title, imgurl,grp_access FROM {$_TABLES['filemgmt_cat']} WHERE pid = 0 ";
    $sql .= $groupsql . ' ORDER BY CID';
    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    // Need to use a SQL stmt that does a join on groups user has access to  - for file count
    $sql  = "SELECT count(*)  FROM {$_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid WHERE status > 0 ";
    $sql .= $groupsql;
    $countsql = DB_query($sql);
    list($maxrows) = DB_fetchArray($countsql);
    $numpages = ceil($maxrows / $show);

    $p->set_var('block_header', COM_startBlock(sprintf(_MD_LISTINGHEADING,$maxrows)));
    $p->set_var('block_footer', COM_endBlock());
    $count = 0;

    if ($nrows > 0) {        // Display the categories - Top Level (plus #files) with links to sub categories
        for ($i = 1; $i <= $nrows; $i++) {
            $myrow = DB_fetchArray($result);
            $secGroup = DB_getItem($_TABLES['groups'], "grp_name", "grp_id='{$myrow['grp_access']}'");
            if (SEC_inGroup($secGroup)) {
                $p->set_var('cid',$myrow['cid']);
                $p->set_var('category_name',$myts->makeTboxData4Show($myrow['title']));

                if ( $mydownloads_useshots && $myrow['imgurl'] && $myrow['imgurl'] != "http://") {
                    $imgurl = $myts->makeTboxData4Edit($myrow['imgurl']);
                    $category_image_link = '<a href="' .$_CONF['site_url'] .'/filemgmt/viewcat.php?cid=' .$myrow['cid'] .'">';
                    $category_image_link .= '<img src="' .$filemgmt_SnapCatURL.$imgurl .'" width="'.$mydownloads_shotwidth.'" border="0" alt="'.$myts->makeTboxData4Show($myrow['title']).'" /></a>';
                    $p->set_var('category_link',$category_image_link);
                } else {
                    $p->set_var('category_link','');
                }

                $downloadsWaitingSubmission = getTotalItems($myrow['cid'], 0);
                $p->set_var('num_files',getTotalItems($myrow['cid'], 1));
                if ($downloadsWaitingSubmission > 0) {
                    $p->set_var('files_waiting_submission','(' . getTotalItems($myrow['cid'], 0) .')');
                } else {
                   $p->set_var('files_waiting_submission','');
                }

                // get child category objects
                $subcategories = '';
                $arr=array();
                $arr=$mytree->getFirstChild($myrow['cid'], 'title');
                $space = 0;
                $chcount = 0;
                foreach($arr as $ele) {
                    $chtitle=$myts->makeTboxData4Show($ele['title']);
                    if ($chcount >= $numSubCategories2Show){
                        $subcategories .= "...";
                        break;
                    }
                    if ($space>0) {
                        $subcategories .= ", ";
                    }
                    $subcategories .= "<a href=\"{$_CONF['site_url']}/filemgmt/viewcat.php?cid={$ele['cid']}\">{$chtitle}</a>";
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

    $sql = "SELECT d.lid, d.cid, d.title, url, homepage, version, size, platform, submitter, logourl, status, ";
    $sql .= "date, hits, rating, votes, comments, description, grp_access FROM ({$_TABLES['filemgmt_filedetail']} d, ";
    $sql .= "{$_TABLES['filemgmt_filedesc']} t) LEFT JOIN {$_TABLES['filemgmt_cat']} c ON d.cid=c.cid ";
    $sql .= "WHERE status > 0 ".$groupsql." AND d.lid=t.lid ORDER BY date DESC LIMIT $offset, $show";
    $result = DB_query($sql);
    $numrows = DB_numROWS($result);
    $countsql = DB_query("SELECT COUNT(*) FROM ".$_TABLES['filemgmt_filedetail']." WHERE status > 0");

    $p->set_var('listing_heading', _MD_LATESTLISTING);
    if ($numrows > 0 ) {
        $cssid = 1;
        for ($x =1; $x <=$numrows; $x++) {
            list($lid, $cid, $dtitle, $url, $homepage, $version, $size, $platform, $submitter, $logourl, $status, $time, $hits, $rating, $votes, $comments, $description,$grp_access)=DB_fetchArray($result);
            $secGroup = DB_getItem($_TABLES['groups'], "grp_name", "grp_id='$grp_access'");
            if (SEC_inGroup($secGroup)) {
                $rating = number_format($rating, 2);
                $dtitle = $myts->makeTboxData4Show($dtitle);
                $url = $myts->makeTboxData4Show($url);
                $homepage = $myts->makeTboxData4Show($homepage);
                $version = $myts->makeTboxData4Show($version);
                $size = $myts->makeTboxData4Show($size);
                $platform = $myts->makeTboxData4Show(isset($platform) ? $platform : '');
                $logourl = $myts->makeTboxData4Show($logourl);
                $datetime = formatTimestamp($time);
                $description = PLG_replaceTags($myts->makeTareaData4Show($description,0),'filemgmt','description'); //no html
                $breakPosition = strpos($description,"<br /><br />");
                if (($breakPosition > 0) AND ($breakPosition < strlen($description)) AND $mydownloads_trimdesc) {
                    $description = substr($description, 0,$breakPosition) . "<p style=\"text-align:left;\"><a href=\"{$_CONF['site_url']}/filemgmt/index.php?id=$lid&amp;comments=1\">{$LANG_FILEMGMT['more']}</a></p>";
                }
                $result2 = DB_query("SELECT username,fullname,photo  FROM {$_TABLES['users']} WHERE uid = $submitter");
                list ($submitter_name,$submitter_fullname,$photo) = DB_fetchARRAY($result2);
                $submitter_name = COM_getDisplayName ($submitter, $submitter_name, $submitter_fullname);
                include $_CONF['path'] .'plugins/filemgmt/include/dlformat.php';
                $p->set_var('cssid',$cssid);
                $p->parse ('filelisting_records', 'records',true);
                $cssid = ($cssid == 2) ? 1 : 2;
            }
        }

        // Print Google-like paging navigation
        $base_url = $_CONF['site_url'] . '/filemgmt/index.php';
        $p->set_var('page_navigation', COM_printPageNavigation($base_url,$page, $numpages));
    }

    $p->parse ('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
}

$display .= FM_siteFooter();
echo $display;

?>