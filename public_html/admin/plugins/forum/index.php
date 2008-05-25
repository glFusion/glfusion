<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | Index.php                                                                 |
// | Main Forum Admin program                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Plugin Authors                                                            |
// | Blaine Lang,                  blaine@portalparts.com, www.portalparts.com |
// | Version 1.0 co-developer:     Matthew DeWyer, matt@mycws.com              |   
// | Prototype & Concept :         Mr.GxBlock, www.gxblock.com                 |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

include_once('gf_functions.php');

echo COM_siteHeader();
echo COM_startBlock($LANG_GF91['gfstats']);

echo ppNavbar($navbarMenu,$LANG_GF06['1']);

// CATEGORIES
$numcats=DB_query("SELECT id FROM {$_TABLES['gf_categories']}");
$totalcats=DB_numRows($numcats);
// FORUMS
$numforums=DB_query("SELECT forum_id FROM {$_TABLES['gf_forums']}");
$totalforums=DB_numRows($numforums);
// TOPICS
$numtopics=DB_query("SELECT id FROM {$_TABLES['gf_topic']} WHERE pid = 0");
$totaltopics=DB_numRows($numtopics);
// POSTS
$numposts=DB_query("SELECT id FROM {$_TABLES['gf_topic']}");
$totalposts=DB_numRows($numposts);
// VIEWS
$numviews=DB_query("SELECT SUM(views) AS TOTAL FROM {$_TABLES['gf_topic']}");
$totalviews=DB_fetchArray($numviews);

// AVERAGE POSTS
if ($totalposts != 0) {
    $avgcposts = $totalposts / $totalcats;
    $avgcposts = round($avgcposts);
    $avgfposts = $totalposts / $totalforums;
    $avgfposts = round($avgfposts);
    $avgtposts = $totalposts / $totaltopics;
    $avgtposts = round($avgtposts);
} else {
    $avgcposts = 0;
    $avgfposts = 0;
    $avgtposts = 0;
}


// AVERAGE VIEWS
if ($totalviews['TOTAL'] != 0) {
    $avgcviews = $totalviews['TOTAL'] / $totalcats;
    $avgcviews = round($avgcviews);
    $avgfviews = $totalviews['TOTAL'] / $totalforums;
    $avgfviews = round($avgfviews);
    $avgtviews = $totalviews['TOTAL'] / $totaltopics;
    $avgtviews = round($avgtviews);
} else {
    $avgcviews = 0;
    $avgfviews = 0;
    $avgtviews = 0;
}


    $indextemplate = new Template($_CONF['path_layout'] . 'forum/layout/admin');
    $indextemplate->set_file (array ('indextemplate'=>'index.thtml'));
    
    $indextemplate->set_var ('statsmsg', $LANG_GF91['statsmsg']);
    $indextemplate->set_var ('totalcatsmsg', $LANG_GF91['totalcats']);
    $indextemplate->set_var ('totalcats', $totalcats);
    $indextemplate->set_var ('totalforumsmsg', $LANG_GF91['totalforums']);
    $indextemplate->set_var ('totalforums', $totalforums);
    $indextemplate->set_var ('totaltopicsmsg', $LANG_GF91['totaltopics']);
    $indextemplate->set_var ('totaltopics', $totaltopics);
    $indextemplate->set_var ('totalpostsmsg', $LANG_GF91['totalposts']);
    $indextemplate->set_var ('totalposts', $totalposts);
    $indextemplate->set_var ('totalviewsmsg', $LANG_GF91['totalviews']);
    $indextemplate->set_var ('totalviews', $totalviews['TOTAL']);
    $indextemplate->set_var ('category', $LANG_GF91['category']);
    $indextemplate->set_var ('forum', $LANG_GF91['forum']);
    $indextemplate->set_var ('topic', $LANG_GF91['topic']);
    $indextemplate->set_var ('avgpmsg', $LANG_GF91['avgpmsg']);
    $indextemplate->set_var ('avgcposts', $avgcposts);
    $indextemplate->set_var ('avgfposts', $avgfposts);
    $indextemplate->set_var ('avgtposts', $avgtposts);
    $indextemplate->set_var ('avgvmsg', $LANG_GF91['avgvmsg']);
    $indextemplate->set_var ('avgcviews', $avgcviews);
    $indextemplate->set_var ('avgfviews', $avgfviews);
    $indextemplate->set_var ('avgtviews', $avgtviews);
    
    $indextemplate->parse ('output', 'indextemplate');
    echo $indextemplate->finish ($indextemplate->get_var('output'));


echo COM_endBlock();
echo adminfooter();

echo COM_siteFooter();

?>