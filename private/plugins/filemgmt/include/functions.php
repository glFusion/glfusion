<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | functions.php                                                            |
// |                                                                          |
// | General functions for FileMgmt                                           |
// +--------------------------------------------------------------------------+
// | $Id:: functions.php 3155 2008-09-16 02:13:18Z mevans0263                $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

function newdownloadgraphic($time, $status) {
    global $_CONF;

    $functionretval = '';
    $count = 7;
    $startdate = (time()-(86400 * $count));
    if ($startdate < $time) {
        if($status==1){
            $functionretval = "&nbsp;<img src=\"{$_CONF['site_url']}/filemgmt/images/newred.gif\" alt=\"" ._MD_NEWTHISWEEK . '"' . XHTML . '>';
        }elseif($status==2){
            $functionretval = "&nbsp;<img src=\"{$_CONF['site_url']}/filemgmt/images/update.gif\" alt=\"" ._MD_UPTHISWEEK . '"' . XHTML . '>';
            }
        }
        return $functionretval;
}

function popgraphic($hits) {
        global $_CONF, $mydownloads_popular;

        $functionretval = '';

        if ($hits >= $mydownloads_popular) {
            $functionretval = "&nbsp;<img src=\"{$_CONF['site_url']}/filemgmt/images/pop.gif\" alt=\"" ._MD_POPULAR . '"' . XHTML . '>';
        }
        return $functionretval;
}

//updates rating data in itemtable for a given item
function updaterating($sel_id){
    global $_FM_TABLES;
    $voteresult = DB_query("select rating FROM {$_FM_TABLES['filemgmt_votedata']} WHERE lid = '$sel_id'");
    $votesDB = DB_numROWS($voteresult);
    $totalrating = 0;
    if ($votesDB > 0) {
           while(list($rating)=DB_fetchARRAY($voteresult)){
            $totalrating += $rating;
        }
        $finalrating = $totalrating/$votesDB;
    }
    $finalrating = number_format($finalrating, 4);
    DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET rating='$finalrating', votes='$votesDB' WHERE lid = '$sel_id'");
}

//returns the total number of items in items table that are accociated with a given table $table id
function getTotalItems($sel_id, $status=''){
    global $_FM_TABLES,$mytree;
    $count = 0;
    $arr = array();
    $sql = "SELECT count(*) from {$_FM_TABLES['filemgmt_filedetail']} a ";
    $sql .= "LEFT JOIN {$_FM_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
    $sql .= "WHERE  a.cid='$sel_id' and a.status='$status' $mytree->filtersql";
    list($thing) = DB_fetchArray(DB_query($sql));
    $count = $thing;
    $arr = $mytree->getAllChildId($sel_id);
    $size = sizeof($arr);
    for($i=0;$i<$size;$i++){
        $sql = "SELECT count(*) from {$_FM_TABLES['filemgmt_filedetail']} a ";
        $sql .= "LEFT JOIN {$_FM_TABLES['filemgmt_cat']} b ON a.cid=b.cid ";
        $sql .= "WHERE  a.cid='{$arr[$i]}}'and a.status='$status' $mytree->filtersql";
        list($thing) = DB_fetchArray(DB_query($sql));
        $count += $thing;
    }
    return $count;
}

/*
* Function to display formatted times in user timezone
*/
function formatTimestamp($usertimestamp) {
    $datetime = date("M.d.y", $usertimestamp);
    $datetime = ucfirst($datetime);
    return $datetime;
}

function PrettySize($size) {
    $mb = 1024*1024;
    if ( $size > $mb ) {
        $mysize = sprintf ("%01.2f",$size/$mb) . " MB";
    }
    elseif ( $size >= 1024 ) {
        $mysize = sprintf ("%01.2f",$size/1024) . " KB";
    }
    else {
        $mysize = sprintf(_MD_NUMBYTES,$size);
    }
    return $mysize;
}


function myTextForm($url , $value) {
    return "<form action='$url' method='post'><input type='submit' value='$value' /></form>\n";
}

function themecenterposts($title, $content) {
 $retval .= "<table border='0' cellpadding='3' cellspacing='5' width='100%'>"
    ."<tr>"
    ."<td><div class='indextitle'>$title</div><br /></td>"
    ."</tr>"
    ."<tr><td>$content</td>"
    ."</tr>"
    ."<tr><td align='right'>&nbsp;</td>"
    ."</tr>"
    ."</table>";

 return $retval;
}

function redirect_header($url, $time=3, $message=''){
    global $pageHandle;


    $pageHandle->addDirectHeader("<meta http-equiv='Refresh' content='$time; url=$url' />'");

    $display  = "<div id='content'>\n";
    $display .= COM_startBlock();
    $display .= "<center>";
    if ( $message!="" ) {
        $display .= "<br" . XHTML . "><p><h4>".$message."</h4>\n";
    }
    $display .= "<br" . XHTML . "><b>\n";
    $display .= sprintf(_IFNOTRELOAD,$url);
    $display .= "</b>\n";
    $display .= "</center></div>\n";
    $display .= COM_endBlock();

    $pageHandle->addContent($display);
    $pageHandle->displayPage();
}

//Reusable Link Sorting Functions
function convertorderbyin($orderby) {
        if ($orderby == "titleA") {
            $orderby = "a.title ASC";
        } else if ($orderby == "dateA") {
            $orderby = "date ASC";
        } else if ($orderby == "hitsA") {
            $orderby = "hits ASC";
        } else if ($orderby == "ratingA") {
            $orderby = "rating ASC";
        } else if ($orderby == "titleD") {
            $orderby = "a.title DESC";
        } else if ($orderby == "dateD") {
            $orderby = "date DESC";
        } else if ($orderby == "hitsD") {
            $orderby = "hits DESC";
        } else if ($orderby == "ratingD") {
            $orderby = "rating DESC";
        } else {
            $orderby = "a.title ASC";
        }
        return $orderby;
}
function convertorderbytrans($orderby) {
        if ($orderby == "hits ASC") {
            $orderbyTrans = _MD_POPULARITYLTOM;
        } else if ($orderby == "hits DESC") {
            $orderbyTrans = _MD_POPULARITYMTOL;
        } else if ($orderby == "title ASC") {
            $orderbyTrans = _MD_TITLEATOZ;
        } else if ($orderby == "a.title ASC") {
            $orderbyTrans = _MD_TITLEATOZ;
        } else if ($orderby == "title DESC") {
            $orderbyTrans = _MD_TITLEZTOA;
        } else if ($orderby == "a.title DESC") {
            $orderbyTrans = _MD_TITLEZTOA;
        } else if ($orderby == "date ASC") {
            $orderbyTrans = _MD_DATEOLD;
        } else if ($orderby == "date DESC") {
            $orderbyTrans = _MD_DATENEW;
        } else if ($orderby == "rating ASC") {
            $orderbyTrans = _MD_RATINGLTOH;
        } else if ($orderby == "rating DESC") {
            $orderbyTrans = _MD_RATINGHTOL;
        } else {
            $orderbyTrans = _MD_TITLEATOZ;
        }
        return $orderbyTrans;
}
function convertorderbyout($orderby) {
        if ($orderby == "title ASC") {
            $orderby = "titleA";
        } else if ($orderby == "a.title ASC") {
            $orderby = "titleA";
        } else if ($orderby == "date ASC") {
            $orderby = "dateA";
        } else if ($orderby == "hits ASC") {
            $orderby = "hitsA";
        } else if ($orderby == "rating ASC") {
            $orderby = "ratingA";
        } else if ($orderby == "title DESC") {
            $orderby = "titleD";
        } else if ($orderby == "a.title DESC") {
            $orderby = "titleD";
        } else if ($orderby == "date DESC") {
            $orderby = "dateD";
        } else if ($orderby == "hits DESC") {
            $orderby = "hitsD";
        } else if ($orderby == "rating DESC") {
            $orderby = "ratingD";
        } else {
            $orderby = "dateA";
        }
        return $orderby;
}

function randomfilename() {

    $length=10;
    srand((double)microtime()*1000000);
    $possible_charactors = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = "";
    while(strlen($string)<$length) {
        $string .= substr($possible_charactors, rand()%(strlen($possible_charactors)),1);
    }
    return($string);
}


?>