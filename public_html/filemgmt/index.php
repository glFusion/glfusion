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
    //'records'          =>     'filelisting_record.thtml',
    'category'         =>     'filelisting_category.thtml'
) );

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

$groupsql = SEC_buildAccessSql();

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

    $p->parse('output', 'page');
    $display .= $p->finish ($p->get_var('output'));
} else {
    // No file specified, show the listing.
    $p = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
    $p->set_file(array (
        'page'             =>     'filelisting.thtml',
        //'records'          =>     'filelisting_record.thtml',
        'category'         =>     'filelisting_category.thtml',
    ));

    $p->set_var ('imgset',$_CONF['layout_url'] . '/nexflow/images');
    $p->set_var ('tablewidth', $_FM_CONF['shotwidth'] + 10);
    $p->set_var('can_submit', Filemgmt\Download::canSubmit());

    $page = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
    if ($page < 1) {
        $page = 1;
    }
    $show = (int)$_FM_CONF['perpage'];

    $groupsql = SEC_buildAccessSql();
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
                    if ($chcount >= $numSubCategories2Show){
                        $subcategories .= "...";
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

    $sql = "SELECT d.*, t.description, c.grp_access
        FROM {$_TABLES['filemgmt_filedetail']} d
        LEFT JOIN {$_TABLES['filemgmt_cat']} c
            ON d.cid=c.cid
        LEFT JOIN {$_TABLES['filemgmt_filedesc']} t
            ON t.lid = d.lid
        WHERE d.status > 0 " . $groupsql .
        " AND d.lid=t.lid ORDER BY date DESC LIMIT $offset, $show";
    //echo $sql;die;
    $result = DB_query($sql);
    $numrows = DB_numROWS($result);
    $countsql = DB_query("SELECT COUNT(*) FROM ".$_TABLES['filemgmt_filedetail']." WHERE status > 0");

    $p->set_var('listing_heading', _MD_LATESTLISTING);
    if ($numrows > 0 ) {
        $p->set_block('page', 'fileRecords', 'fRecord');
        for ($x =1; $x <=$numrows; $x++) {
            $A = DB_fetchArray($result, false);
            $D = new Filemgmt\Download($A);
            $p->set_var('filelisting_record', $D->showListingRecord());
            $p->parse('fRecord', 'fileRecords', true);
        }

        // Print Google-like paging navigation
        $p->set_var(
            'page_navigation',
            COM_printPageNavigation($_FM_CONF['url'] . '/index.php', $page, $numpages)
        );
    }

    $p->parse ('output', 'page');
    $display .= $p->finish($p->get_var('output'));
}
echo Filemgmt\Menu::siteHeader($LANG_FILEMGMT['usermenu1']);
echo $display;
echo Filemgmt\Menu::siteFooter();

