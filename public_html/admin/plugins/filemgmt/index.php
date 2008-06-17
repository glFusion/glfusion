<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | File Management Plugin for Geeklog - by portalparts www.portalparts.com |
// | File: index.php                                                         |
// | Main admin script                                                       |
// +-------------------------------------------------------------------------+
// | Filemgmt plugin - version 1.5                                           |
// | Date: Mar 18, 2006                                                      |
// +-------------------------------------------------------------------------+
// | Copyright (C) 2004 by Consult4Hire Inc.                                 |
// |                                                                         |
// | Author:                                                                 |
// | Blaine Lang                 -    blaine@portalparts.com                 |
// +-------------------------------------------------------------------------+
// |                                                                         |
// | This program is free software; you can redistribute it and/or           |
// | modify it under the terms of the GNU General Public License             |
// | as published by the Free Software Foundation; either version 2          |
// | of the License, or (at your option) any later version.                  |
// |                                                                         |
// | This program is distributed in the hope that it will be useful,         |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                    |
// | See the GNU General Public License for more details.                    |
// |                                                                         |
// | You should have received a copy of the GNU General Public License       |
// | along with this program; if not, write to the Free Software Foundation, |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.         |
// |                                                                         |
// +-------------------------------------------------------------------------+
//

require_once("../../../lib-common.php");
include_once($_CONF['path_html']."filemgmt/include/header.php");
include_once($_CONF['path_html']."filemgmt/include/functions.php");
include_once($_CONF['path_html']."filemgmt/include/xoopstree.php");
include_once($_CONF['path_html']."filemgmt/include/textsanitizer.php");
include_once($_CONF['path_html']."filemgmt/include/errorhandler.php");
include_once($_CONF['path']."system/classes/navbar.class.php");

$op = isset($_REQUEST['op']) ? COM_applyFilter($_REQUEST['op']) : '';
$display = '';
if (!SEC_hasRights('filemgmt.edit')) {
    if ($op != 'comment') {
        $display .= COM_siteHeader('menu');
        $display .= COM_startBlock(_GL_ERRORNOACCESS);
        $display .= _MD_USER." ".$_USER['username']. " " ._GL_NOUSERACCESS;
        $display .= COM_endBlock();
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }
}

function filemgmt_navbar($selected='') {
    global $_CONF,$LANG_FM02,$_FM_TABLES;

    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_brokenlinks']}");
    list($totalbrokendownloads) = DB_fetchARRAY($result);
    if($totalbrokendownloads > 0){
        $totalbrokendownloads = "<font color=\"#ff0000\"><b>$totalbrokendownloads</b></font>";
    }
    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE status=0");
    list($totalnewdownloads) = DB_fetchARRAY($result);
    if($totalnewdownloads > 0){
        $totalnewdownloads = "<font color=\"#ff0000\"><b>$totalnewdownloads</b></font>";
    }

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_FM02['nav1'],$_CONF['site_admin_url'] .'/plugins/filemgmt/index.php?op=filemgmtConfigAdmin');
    $navbar->add_menuitem($LANG_FM02['nav2'],$_CONF['site_admin_url'] .'/plugins/filemgmt/index.php?op=categoryConfigAdmin');
    $navbar->add_menuitem($LANG_FM02['nav3'],$_CONF['site_admin_url'] .'/plugins/filemgmt/index.php?op=newfileConfigAdmin');
    $navbar->add_menuitem(sprintf($LANG_FM02['nav4'],$totalnewdownloads),$_CONF['site_admin_url'] .'/plugins/filemgmt/index.php?op=listNewDownloads');
    $navbar->add_menuitem(sprintf($LANG_FM02['nav5'],$totalbrokendownloads),$_CONF['site_admin_url'] .'/plugins/filemgmt/index.php?op=listBrokenDownloads');

    if ($selected == $LANG_FM02['nav4']) {
        $navbar->set_selected(sprintf($LANG_FM02['nav4'],$totalnewdownloads));
    } elseif ($selected == $LANG_FM02['nav5']) {
        $navbar->set_selected(sprintf($LANG_FM02['nav5'],$totalbrokendownloads));
    } else {
        $navbar->set_selected($selected);
    }

    return $navbar->generate();

}

$myts = new MyTextSanitizer;
$mytree = new XoopsTree($_DB_name,$_FM_TABLES['filemgmt_cat'],"cid","pid");
$eh = new ErrorHandler;

function mydownloads() {
    global $_CONF,$LANG_FM02,$_FM_TABLES;

    $display = COM_siteHeader('menu');
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");

    $display .= filemgmt_navbar();

    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE status > 0");
    list($numrows) = DB_fetchARRAY($result);
    $display .= "<br" . XHTML . "><br" . XHTML . "><div style=\"text-align:center;padding:10px;\">";
    $display .= sprintf(_MD_THEREARE,$numrows);
    $display .= "</div>";
    $display .= "<br" . XHTML . ">";
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
}

function listNewDownloads(){
    global $_CONF,$_TABLES,$_FM_TABLES,$myts,$eh,$mytree,$filemgmt_FileStoreURL,$filemgmt_FileSnapURL,$LANG_FM02;

    // List downloads waiting for validation
    $sql = "SELECT lid, cid, title, url, homepage, version, size, logourl, submitter, comments, platform ";
    $sql .= "FROM {$_FM_TABLES['filemgmt_filedetail']} where status=0 ORDER BY date DESC";
    $result = DB_query($sql);
    $numrows = DB_numROWS($result);
    $display = COM_siteHeader('menu');
    $display .= COM_startBlock('<b>'._MD_ADMINTITLE.'</b>');
    $display .= filemgmt_navbar($LANG_FM02['nav4']);

    $i = 1;
    if ($numrows > 0) {
        $display .= '<table width="100%" border="0" class="plugin">';
        $display .= '<tr><td width="100%" class="pluginHeader" style="padding:5px;">' . _MD_DLSWAITING. "&nbsp;($numrows)</td></tr>";
        while(list($lid, $cid, $title, $url, $homepage, $version, $size, $logourl, $submitter, $comments, $tmpnames) = DB_fetchARRAY($result)) {
            $result2 = DB_query("SELECT description FROM {$_FM_TABLES['filemgmt_filedesc']} WHERE lid='$lid'");
            list($description) = DB_fetchARRAY($result2);
            $title = $myts->makeTboxData4Edit($title);
            $url = rawurldecode($myts->makeTboxData4Edit($url));
            $logourl = rawurldecode($myts->makeTboxData4Edit($logourl));
            $homepage = $myts->makeTboxData4Edit($homepage);
            $version = $myts->makeTboxData4Edit($version);
            $size = $myts->makeTboxData4Edit($size);
            $description = $myts->makeTareaData4Edit($description);
            $tmpfilenames = explode(";",$tmpnames);
            $tempfileurl = $filemgmt_FileStoreURL . 'tmp/' .$tmpfilenames[0];
            if (isset($tmpfilenames[1]) and $tmpfilenames[1] != '') {
                $tempsnapurl = $filemgmt_FileSnapURL . 'tmp/' .$tmpfilenames[1];
            } else {
                $tempsnapurl = '';
            }
            $display .= '<tr><td>';
            $display .= '<form action="index.php" method="post" enctype="multipart/form-data" style="margin:0px;">';
            $display .= '<table width="100%" border="0" class="plugin">';
            $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_SUBMITTER.'</td><td>';
            $display .= '<a href="'. $_CONF['site_url'] . '/users.php?mode=profile&amp;uid='. $submitter. '">'.COM_getDisplayName ($submitter).'</a>';
            $display .= '</td></tr>';
            $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_FILETITLE.'</td><td>';
            $display .= '<input type="text" name="title" size="50" maxlength="100" value="'.$title.'"' . XHTML . '>';
            $display .= '</td></tr><tr><td align="right" style="white-space:nowrap;">'._MD_DLFILENAME.'</td><td>';
            $display .= '<input type="text" name="url" size="50" maxlength="250" value="'.$url.'"' . XHTML . '>';
            $display .= '</td></tr>';
            $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_CATEGORYC.'</td><td>';
            $display .= $mytree->makeMySelBox('title', 'title', $cid);
            $display .= '</td></tr>';
            $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_HOMEPAGEC.'</td><td>';
            $display .= '<input type="text" name="homepage" size="50" maxlength="100" value="'.$homepage.'"' . XHTML . '></td></tr>';
            $display .= '<tr><td align="right">'._MD_VERSIONC.'</td><td>';
            $display .= '<input type="text" name="version" size="10" maxlength="10" value="'.$version.'"' . XHTML . '></td></tr>';
            $display .= '<tr><td align="right">'._MD_FILESIZEC.'</td><td>';
            $display .= '<input type="text" name="size" size="10" maxlength="8" value="'.$size.'"' . XHTML . '>&nbsp;'._MD_BYTES.'</td></tr>';
            $display .= '<tr><td align="right" style="vertical-align:top;white-space:nowrap;">'._MD_DESCRIPTIONC.'</td><td>';
            $display .= '<textarea name=description cols="60" rows="5">'.$description.'</textarea>';
            $display .= '</td></tr>';
            $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_SHOTIMAGE.'</td><td>';
            $display .= '<input type="text" name="logourl" size="50" maxlength="250" value="'.$logourl.'"' . XHTML . '>';
            if ($tempsnapurl != '') {
                $display .= '<span style="padding-left:20px;"><a href="' . $tempsnapurl . '">Preview</a></span>';
            }
            $display .= '</td></tr>';
            $display .= '<tr><td></td><td>';
            $display .= '</td></tr><tr><td style="white-space:nowrap;" align="right">'._MD_COMMENTOPTION.'</td><td>';
            if ($comments) {
                $display .= '<input type="radio" name="commentoption" value="1" checked="checked"' . XHTML . '>&nbsp;' ._MD_YES.'&nbsp;';
                $display .= '<input type="radio" name="commentoption" value="0"' . XHTML . '>&nbsp;' ._MD_NO.'&nbsp;';
            } else {
                $display .= '<input type="radio" name="commentoption" value="1"' . XHTML . '>&nbsp;' ._MD_YES.'&nbsp;';
                $display .= '<input type="radio" name="commentoption" value="0" checked="checked"' . XHTML . '>&nbsp;' ._MD_NO.'&nbsp;';
            }
            $display .= '</td></tr>';
            $display .= '<tr><td style="text-align:right;padding:10px;">';
            $display .= '<input type="submit" onclick=\'this.form.op.value="delNewDownload"\' value="Delete"' . XHTML . '>';
            $display .= '<input type="hidden" name="op" value=""' . XHTML . '>';
            $display .= '<input type="hidden" name="lid" value="'.$lid.'"' . XHTML . '>';
            $display .= '<span style="padding-left:10px;">';
            $display .= '<input type="submit" value="'._MD_APPROVE.'" onclick=\'this.form.op.value="approve"\'' . XHTML . '></span>';
            $display .= '</td><td style="padding:10px;">Download to preview:&nbsp;<a href="' . $tempfileurl . '">tempfile</a></td></tr>';
            if ($numrows > 1 and $i < $numrows ) {
               $i++;
            }
            $display .= '</table></form></td></tr>';
        }
        $display .= '</table>';
    } else {
        $display .= '<div style="padding:20px">' . _MD_NOSUBMITTED .'</div>';
    }

    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
}


function categoryConfigAdmin(){
    global $_CONF, $_TABLES, $LANG_FM02, $_FM_TABLES, $myts, $eh, $mytree;

    $display = COM_siteHeader('menu');
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");
    $display .= filemgmt_navbar($LANG_FM02['nav2']);
    $display .= '<table width="100%" cellpadding="0" cellspacing="0"><tr><td style="width:100%;">';
    $display .= '<form action="index.php" method="post" enctype="multipart/form-data" style="margin:0px;">';
    $display .= '<table width="100%" border="0" class="plugin">';
    $display .= '<tr><td colspan="2" class="pluginHeader" style="width:100%;padding:5px;">' . _MD_ADDMAIN . '</td></tr>';
    $display .= '<tr><td>' . _MD_TITLEC. '</td><td><input type="text" name="title" size="30" maxlength="50"' . XHTML . '></td></tr>';
    $display .= '<tr><td>' . _MD_CATSEC. '</td><td><select name="sel_access">';
    $display .= COM_optionList($_TABLES['groups'], "grp_id,grp_name") . '</select></td></tr>';
    $display .= '<tr><td>'. _MD_ADDCATEGORYSNAP . '<br' . XHTML . '><span style="text-size:-2">'. _MD_ADDIMAGENOTE .'</span></td>';
    $display .= '<td><input type="file" name="uploadfile" size="50" maxlength="200"' . XHTML . '></td></tr>';
    $display .= '<tr><td colspan="2" style="text-align:center;padding:10px;">';
    $display .= "<input type=\"hidden\" name=\"cid\" value=\"0\"" . XHTML . ">\n";
    $display .= "<input type=\"hidden\" name=\"op\" value=\"addCat\"" . XHTML . ">";
    $display .= "<input type=\"submit\" value=\""._MD_ADD."\"" . XHTML . "></td></tr></table></form><br" . XHTML . ">";

    // Add a New Sub-Category
    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_cat']}");
    $numrows = DB_numROWS($result);
    if($numrows > 0) {
        $display .= '</td></tr><tr><td>';
        $display .= '<form method="post" action="index.php" style="margin:0px;">';
        $display .= '<table width="100%" border="0" class="plugin">';
        $display .= '<tr><td colspan="2" class="pluginHeader" style="width:100%;padding:5px;">' . _MD_ADDSUB . '</td></tr>';

        $display .= '<tr><td style="width:20%;">'. _MD_TITLEC.'</td><td><input type="text" name="title" size="30" maxlength="50"' . XHTML . '>&nbsp;' ._MD_IN. '&nbsp;';
        $display .= $mytree->makeMySelBox('title', 'title') . '</td></tr>';
        $display .= '<tr><td colspan="2" style="text-align:center;padding:10px;">';
        $display .= '<input type="hidden" name="op" value="addCat"' . XHTML . '>';
        $display .= "<input type=\"submit\" value=\""._MD_ADD."\"" . XHTML . "></td></tr></table></form><br" . XHTML . ">";
        // Modify Category
        $display .= '</td></tr><tr><td>';
        $display .= '<form method="post" action="index.php" style="margin:0px;">';
        $display .= '<table width="100%" border="0" class="plugin">';
        $display .= '<tr><td colspan="2" class="pluginHeader" style="width:100%;padding:5px;">' . _MD_MODCAT . '</td></tr>';
        $display .= '<tr><td style="width:20%;">'. _MD_CATEGORYC .'</td><td>';
        $display .= $mytree->makeMySelBox('title', 'title') . '</td></tr>';
        $display .= '<tr><td colspan="2" style="text-align:center;padding:10px;">';
        $display .= '<input type="hidden" name="op" value="modCat"' . XHTML . '>';
        $display .= "<input type=\"submit\" value=\""._MD_MODIFY."\"" . XHTML . "></td></tr></table></form><br" . XHTML . ">";
    }
    $display .= '</td></tr></table>';

    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;

}

function newfileConfigAdmin(){
    global $_CONF,$myts,$eh,$mytree,$LANG_FM02;

    $display = COM_siteHeader('menu');
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");
    $display .= filemgmt_navbar($LANG_FM02['nav3']);
    $display .= '<form method="post" enctype="multipart/form-data" action="index.php" style="margin:0px;">';
    $display .= '<table width="100%" border="0" class="plugin">';
    $display .= '<tr><td colspan="2" class="pluginHeader" style="width:100%;padding:5px;">' . _MD_ADDNEWFILE . '</td></tr>';
    $display .= '<tr><td align="right">'._MD_FILETITLE.'</td><td>';
    $display .= '<input type="text" name="title" size="50" maxlength="100"' . XHTML . '>';
    $display .= '</td></tr><tr><td align="right" style="white-space:nowrap;">File:</td><td>';
    $display .= '<input type="file" name="newfile" size="50" maxlength="100"' . XHTML . '>';
    $display .= '</td></tr>';
    $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_CATEGORYC.'</td><td>';
    $display .= $mytree->makeMySelBox('title', 'title');
    $display .= '</td></tr><tr><td></td><td></td></tr>';
    $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_HOMEPAGEC.'</td><td>';
    $display .= '<input type="text" name="homepage" size="50" maxlength="100"' . XHTML . '></td></tr>';
    $display .= '<tr><td align="right">'._MD_VERSIONC.'</td><td>';
    $display .= '<input type="text" name="version" size="10" maxlength="10"' . XHTML . '></td></tr>';
    $display .= '<tr><td align="right" style="vertical-align:top;white-space:nowrap;">'._MD_DESCRIPTIONC.'</td><td>';
    $display .= '<textarea name="description" cols="60" rows="5"></textarea>';
    $display .= '</td></tr>';
    $display .= '<tr><td align="right" style="white-space:nowrap;">'._MD_SHOTIMAGE.'</td><td>';
    $display .= '<input type="file" name="newfileshot" size="50" maxlength="60"' . XHTML . '></td></tr>';
    $display .= '<tr><td align="right"></td><td>';
    $display .= '</td></tr><tr><td align="right">'._MD_COMMENTOPTION.'</td><td>';
    $display .= '<input type="radio" name="commentoption" value="1" checked="checked"' . XHTML . '>&nbsp;' ._MD_YES.'&nbsp;';
    $display .= '<input type="radio" name="commentoption" value="0"' . XHTML . '>&nbsp;' ._MD_NO.'&nbsp;';
    $display .= '</td></tr>';
    $display .= '<tr><td colspan="2" style="text-align:center;padding:10px;">';
    $display .= '<input type="hidden" name="op" value="addDownload"' . XHTML . '>';
    $display .= '<input type="submit" class="button" value="'._MD_ADD.'"' . XHTML . '>';
    $display .= '</td></tr></table>';
    $display .= '</form>';
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;

}

function modDownload() {
    global $_CONF,$_FM_TABLES,$_USER,$myts,$eh,$mytree,$filemgmt_SnapStore,$filemgmt_FileSnapURL;

    $lid = $_GET['lid'];
    $result = DB_query("SELECT cid, title, url, homepage, version, size, logourl, comments FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
    $nrows = DB_numROWS($result);
    if ($nrows == 0) {
        redirect_header("index.php",2,_MD_NOMATCH);
        exit();
    }

    $display = COM_siteHeader('menu');
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");

    $display .= '<form method="post" enctype="multipart/form-data" action="index.php">';
    $display .= '<input type="hidden" name="op" value="modDownloadS"' . XHTML . '>';
    $display .= '<input type="hidden" name="lid" value="'.$lid.'"' . XHTML . '>';
    $display .= '<table width="100%" border="0" class="plugin">';

    list($cid, $title, $url, $homepage, $version, $size, $logourl,$comments) = DB_fetchARRAY($result);
    $title = $myts->makeTboxData4Edit($title);
    $pathstring = "<a href=\"{$_CONF['site_url']}/filemgmt/index.php\">"._MD_MAIN."</a>&nbsp;:&nbsp;";
    $nicepath = $mytree->getNicePathFromId($cid, "title", "{$_CONF['site_url']}/filemgmt/viewcat.php");
    $pathstring .= $nicepath;
    $pathstring .= "<a href=\"{$_CONF['site_url']}/filemgmt/index.php?id=$lid\">{$title}</a>";

    $display .= '<tr><td colspan="3" width="100%" style="padding:5px;">' . $pathstring. '</td></tr>';
    $display .= '<tr><td colspan="3" width="100%" class="pluginHeader" style="padding:5px;">' . _MD_MODDL. '</td></tr>';

    $url = rawurldecode($myts->makeTboxData4Edit($url));
    $homepage = $myts->makeTboxData4Edit($homepage);
    $version = $myts->makeTboxData4Edit($version);
    $size = $myts->makeTboxData4Edit($size);
    $logourl = rawurldecode($myts->makeTboxData4Edit($logourl));
    $result2 = DB_query("SELECT description FROM {$_FM_TABLES['filemgmt_filedesc']} WHERE lid=$lid");
    list($description)=DB_fetchARRAY($result2);
    $description = $myts->makeTareaData4Edit($description);
    $display .= '<tr><td>'._MD_FILEID.'</td><td colspan="2"><b>'.$lid.'</b></td></tr>';
    $display .= '<tr><td>'._MD_FILETITLE.'</td><td colspan="2"><input type="text" name="title" value="'.$title.'" size="50" maxlength="200"' . XHTML . '></td></tr>' .LB;
    $display .= '<tr><td>'._MD_DLFILENAME.'</td><td colspan="2"><input type="text" name="url" value="'.$url.'" size="50" maxlength="200"' . XHTML . '></td></tr>' .LB;
    $display .= '<tr><td width="25%">'._MD_REPLFILENAME.'</td><td colspan="2"><input type="file" name="newfile" size="50" maxlength="200"' . XHTML . '></td></tr>' .LB;
    $display .= '<tr><td>'._MD_HOMEPAGEC.'</td><td colspan="2"><input type="text" name="homepage" value="'.$homepage.'" size="50" maxlength="150"' . XHTML . '></td></tr>' .LB;
    $display .= '<tr><td>'._MD_VERSIONC.'</td><td colspan="2"><input type="text" name="version" value="'.$version.'" size="10" maxlength="10"' . XHTML . '></td></tr>' .LB;
    $display .= '<tr><td>'._MD_FILESIZEC.'</td><td colspan="2"><input type="text" name="size" value="'.$size.'" size="10" maxlength="20"' . XHTML . '>'._MD_BYTES.'</td></tr>' .LB;
    $display .= '<tr><td style="vertical-align:top;">'._MD_DESCRIPTIONC.'</td><td colspan="2"><textarea name="description" cols="55" rows="10">'.$description.'</textarea></td></tr>' .LB;
    $display .= '<tr><td>'._MD_CATEGORYC.'</td><td colspan="2">';
    $display .= $mytree->makeMySelBox("title", "title", $cid,0,"cid");
    $display .= '</td></tr>' .LB;

    if (!empty($logourl) AND file_exists($filemgmt_SnapStore.$logourl)) {
        $display .= '<tr><td>'._MD_SHOTIMAGE.'</td><td width="5%"><img src="' .$filemgmt_FileSnapURL.$logourl. '" width="80"></td>' .LB;
        $display .= '<td width="35%"><input type="file" size="40" name="newfileshot"' . XHTML . '><br' . XHTML . '><br' . XHTML . '><input type="checkbox" name="deletesnap"' . XHTML . '>&nbsp;Delete</td></tr>' .LB;
    } else {
        $display .= '<tr><td>'._MD_SHOTIMAGE.'</td>' .LB;
        $display .= '<td colspan="2"><input type="file" size="40" name="newfileshot"' . XHTML . '></td></tr>' .LB;
    }

    $display .= '<tr><td>'._MD_COMMENTOPTION.'</td><td colspan="2">';
    if ($comments) {
        $display .= '<input type="radio" name="commentoption" value="1" checked="checked"' . XHTML . '>&nbsp;'._MD_YES.'&nbsp;';
        $display .= '<input type="radio" name="commentoption" value="0"' . XHTML . '>&nbsp;'._MD_NO.'&nbsp;';
    } else {
        $display .= '<input type="radio" name="commentoption" value="1"' . XHTML . '>&nbsp;'._MD_YES.'&nbsp;';
        $display .= '<input type="radio" name="commentoption" value="0" checked="checked"' . XHTML . '>&nbsp;'._MD_NO.'&nbsp;';
    }
    $display .= '</td></tr>' .LB;
    $display .= '<tr><td>Silent Edit</td><td colspan="2">';
    $display .= '<input type="checkbox" name="silentedit" value="1"' . XHTML . '>';
    $display .= '</td></tr>' . LB;


    $display .= '<tr><td colspan="3" style="text-align:center;padding:10px;">';
    $display .= '<input type="submit" value="'._MD_SUBMIT.'"' . XHTML . '><span style="padding-left:15px;padding-right:15px;">';
    $display .= '<input type="submit" value="'._MD_DELETE.'" onclick=\'if (confirm("Delete this file ?")) {this.form.op.value="delDownload";return true}; return false\'' . XHTML . '>';
    $display .= "</span><input type=\"button\" value=\""._MD_CANCEL."\" onclick=\"javascript:history.go(-1)\"" . XHTML . ">";
    $display .= '</td></tr></table></form>' .LB;


    /* Display File Voting Information */
    $result5 = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_votedata']}");
    list ($totalvotes) = DB_fetchARRAY($result5);

    $display .= '<form method="post" action="index.php">';
    $display .= '<input type="hidden" name="op" value=""' . XHTML . '>';
    $display .= '<input type="hidden" name="rid" value=""' . XHTML . '>';
    $display .= '<input type="hidden" name="lid" value="'.$lid.'"' . XHTML . '>';
    $display .= '<table style="vertical-align:top;" width="100%" class="pluginSubTable">';
    $display .= '<tr><th colspan="7">';
    if ($totalvotes == '')
       $totalvotes = 0;
    $display .= sprintf(_MD_DLRATINGS,$totalvotes);
    $display .= '</th></tr>';
    // Show Registered Users Votes
    $result5 = DB_query("SELECT ratingid, ratinguser, rating, ratinghostname, ratingtimestamp FROM {$_FM_TABLES['filemgmt_votedata']} WHERE lid='$lid' AND ratinguser != 0 ORDER BY ratingtimestamp DESC");
    $votes = DB_numROWS($result5);
    $display .= '<tr><td colspan="7">';
    $display .= sprintf(_MD_REGUSERVOTES,$votes);
    $display .= '</td></tr>';
    $display .= '<tr><th>'._MD_USER.'</th><th>'._MD_IP.'</th><th>'._MD_RATING.'</th><th>'._MD_USERAVG.'</th><th>'._MD_TOTALRATE.'</th><th>'._MD_DATE.'</th><th align="center">'._MD_DELETE.'</th></tr>';
    if ($votes == 0){
          $display .= '<tr><td align="center" colspan="7">'._MD_NOREGVOTES.'<br' . XHTML . '></td></tr>';
    }
    $x=0;
    $cssid = 1;
    while(list($ratingid, $ratinguser, $rating, $ratinghostname, $ratingtimestamp)=DB_fetchARRAY($result5)) {
        $formatted_date = formatTimestamp($ratingtimestamp);

        //Individual user information
        $result2 = DB_query("SELECT rating FROM {$_FM_TABLES['filemgmt_votedata']} WHERE ratinguser='$ratinguser'");
        $uservotes = DB_numROWS($result2);
        $useravgrating = 0;
        while(list($rating2) = DB_fetchARRAY($result2)){
             $useravgrating = $useravgrating + $rating2;
        }
        $useravgrating = $useravgrating / $uservotes;
        $useravgrating = number_format($useravgrating, 1);
        $ratinguname = $_USER['username'];
        $display .= "<tr class=\"pluginRow{$cssid}\"><td>$ratinguname</td><td>$ratinghostname</td><td>$rating</td>";
        $display .= "<td>$useravgrating</td><td>$uservotes</td><td>$formatted_date</td><td style=\"text-align:right;padding-right:20px;\">";
        $display .= '<input type="image" src="'.$_CONF['site_url'].'/filemgmt/images/delete.gif" ';
        $display .= 'onclick=\'if (confirm("Delete this record")) {this.form.op.value="delVote";this.form.lid.value="'.$lid.'";this.form.rid.value="'.$ratingid.'";return true};return false;\' value="Delete"' . XHTML . '>';
        $display .= "</td></tr>\n";
        $x++;
        $cssid = ($cssid == 1) ? 2 : 1;

    }
    $display .= '</table></form>' .LB;
    // Show Unregistered Users Votes
    $result5 = DB_query("SELECT ratingid, rating, ratinghostname, ratingtimestamp FROM {$_FM_TABLES['filemgmt_votedata']} WHERE lid='$lid' AND ratinguser=0 ORDER BY ratingtimestamp DESC");
    $votes = DB_numROWS($result5);
    $display .= '<form method="post" action="index.php" onsubmit="alert(this.form.op.value)">';
    $display .= '<input type="hidden" name="op" value=""' . XHTML . '>';
    $display .= '<input type="hidden" name="rid" value=""' . XHTML . '>';
    $display .= '<input type="hidden" name="lid" value="'.$lid.'"' . XHTML . '>';
    $display .= '<table style="vertical-align:top;" width="100%" class="pluginSubTable">';
    $display .= '<tr><th colspan="7">';
    $display .= sprintf(_MD_ANONUSERVOTES,$votes);
    $display .= '</th></tr>';
    $display .= '<tr><th colspan="2">'._MD_IP.'</th><th colspan="3">'._MD_RATING.'</th><th colspan="2">'._MD_DATE.'</th></tr>';
    if ($votes == 0) {
           $display .= "<tr><td colspan=\"7\" align=\"center\">" ._MD_NOUNREGVOTES."<br" . XHTML . "></td></tr>";
    }
    $x=0;
    $cssid = 1;
    while(list($ratingid, $rating, $ratinghostname, $ratingtimestamp)=DB_fetchARRAY($result5)) {
        $formatted_date = formatTimestamp($ratingtimestamp);
        $display .= "<tr class=\"pluginRow{$cssid}\" style=\"vertical-align:bottom;\"><td colspan=\"2\">$ratinghostname</td><td colspan=\"3\">$rating</td>";
        $display .= "<td>$formatted_date</td><td style=\"text-align:right;padding-right:20px;\">";
        $display .= '<input type="image" src="'.$_CONF['site_url'].'/filemgmt/images/delete.gif" ';
        $display .= 'onclick=\'if (confirm("Delete this record")) {this.form.op.value="delVote";this.form.lid.value="'.$lid.'";this.form.rid.value="'.$ratingid.'";return true};return false;\' value="Delete"' . XHTML . '>';
        $display .= "</td></tr>";
        $x++;
        $cssid = ($cssid == 1) ? 2 : 1;
    }
    $display .= "<tr><td colspan=\"6\">&nbsp;<br" . XHTML . "></td></tr>\n";
    $display .= "</table></form>";
//    $display .= CloseTable();
    $display .= "<br" . XHTML . ">";
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
}

function listBrokenDownloads() {
    global $_CONF,$_TABLES,$_FM_TABLES,$LANG_FM02,$myts,$eh;

    $result = DB_query("SELECT * FROM {$_FM_TABLES['filemgmt_brokenlinks']} ORDER BY reportid");
    $totalbrokendownloads = DB_numROWS($result);

    $display = COM_siteHeader('menu');
    $display .= COM_startBlock('<b>'._MD_ADMINTITLE.'</b>');
    $display .= filemgmt_navbar($LANG_FM02['nav5']);

    if ($totalbrokendownloads==0) {
        $display .= '<div style="padding:20px">' . _MD_NOBROKEN . '</div>';
    } else {
        $display .= '<form method="post" action="index.php">';
        $display .= '<input type="hidden" name="op" value="">';
        $display .= '<input type="hidden" name="lid" value="">';
        $display .= '<table width="100%" border="0" class="plugin">';
        $display .= '<tr><td colspan="5" width="100%" class="pluginHeader" style="padding:5px;">' . _MD_BROKENREPORTS. "&nbsp;($totalbrokendownloads)</td></tr>";
        $display .= '<tr><td colspan="5">' . _MD_IGNOREDESC . "<br" . XHTML . ">"._MD_DELETEDESC."</td></tr>";
        $display .= '<tr class="pluginHeader"><th>'._MD_FILETITLE.'</th><th>'._MD_REPORTER.'</th>';
        $display .= '<th>'._MD_FILESUBMITTER.'</th><th>'._MD_IGNORE.'</th><th>'._MD_DELETE.'</th></tr>';

        $cssid = 1;
        while(list($reportid, $lid, $sender, $ip) = DB_fetchARRAY($result)) {
           $result2 = DB_query("SELECT title, url, submitter FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
           if ($sender != 0) {
               $result3 = DB_query("SELECT username, email FROM {$_TABLES['users']} WHERE uid='$sender'");
               list($sendername, $email) = DB_fetchARRAY($result3);
            }
            list($title, $url, $owner) = DB_fetchARRAY($result2);
            $result4 = DB_query("SELECT username, email FROM {$_TABLES['users']} WHERE uid='$owner'");
            list($ownername, $owneremail) = DB_fetchARRAY($result4);
            $display .= '<tr class="pluginRow'.$cssid.'"><td><a href="'.$_CONF['site_url'].'/filemgmt/visit.php?lid='.$lid.'">'.$title.'</a></td>';

            if ($email == '') {
                $display .= "<td>$sendername ($ip)";
            } else {
               $display .= "<td><a href=mailto:$email>$sendername</a> ($ip)";
            }
            $display .= "</td>";
            if ($owneremail == '') {
                $display .= "<td>$ownername";
            } else {
                $display .= "<td><a href=mailto:$owneremail>$ownername</a>";
            }
            $display .= "</td><td style='text-align:center'>";
            $display .= '<input type="image" src="'.$_CONF['site_url'].'/filemgmt/images/delete.gif" ';
            $display .= 'onClick=\'if (confirm("Delete this broken file report?")) {this.form.op.value="ignoreBrokenDownloads";';
            $display .= 'this.form.lid.value="'.$lid.'";return true};return false;\'">';
            $display .= "</td>";
            $display .= "<td style='text-align:center'>";
            $display .= '<input type="image" src="'.$_CONF['site_url'].'/filemgmt/images/delete.gif" ';
            $display .= 'onClick=\'if (confirm("Delete the file from your repository?")) {this.form.op.value="delBrokenDownloads";';
            $display .= 'this.form.lid.value="'.$lid.'";return true};return false;\'">';
            $display .= "</td></tr>\n";
            $cssid = ($cssid == 1) ? 2 : 1;
        }
        $display .= "</table>";
    }
//    $display .= CloseTable();
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
}

function delBrokenDownloads() {
    global $_FM_TABLES,$eh;

    $lid = $_POST['lid'];
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedetail']}  WHERE lid='$lid'");
    redirect_header("index.php?op=listBrokenDownloads",1,_MD_FILEDELETED);
    exit();
}

function ignoreBrokenDownloads() {
    global $_FM_TABLES,$eh;

    $lid = $_POST['lid'];
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");
    redirect_header("index.php?op=listBrokenDownloads",1,_MD_BROKENDELETED);
    exit();
}

function delVote() {
   global $_CONF,$_FM_TABLES,$eh;

   $rid = $_POST['rid'];
   $lid = $_POST['lid'];
   DB_query("DELETE FROM {$_FM_TABLES['filemgmt_votedata']} WHERE ratingid='$rid'");
   updaterating($lid);
   redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php?lid=$lid&amp;op=modDownload",2,_MD_VOTEDELETED);
   exit();
}


function modDownloadS() {
    global $_CONF,$_FM_TABLES,$myts,$eh,$filemgmt_SnapStore,$filemgmt_FileStore;

    $cid = $_POST["cid"];
    if (($_POST["url"]) || ($_POST["url"]!="")) {
        $url = rawurlencode($myts->makeTboxData4Save($_POST['url']));
    }

    $silentEdit = COM_applyFilter($_POST['silentedit'],true);

    $currentfile = DB_getITEM($_FM_TABLES['filemgmt_filedetail'], 'url', "lid='{$_POST['lid']}'");
    $currentfileFQN = $filemgmt_FileStore . $myts->makeTboxData4Save(rawurldecode($currentfile));
    $newfile = rawurlencode($myts->makeTboxData4Save($_FILES['newfile']['name']));
    COM_errorLOG("Currentfilename is:'$currentfile' and new file is:'$newfile'");
    if (($newfile != '' AND $currentfile != $newfile)  OR ($newfile != '' and $currentfile == '')) {
        COM_errorLOG("Download file has changed");
        if (uploadNewFile($_FILES["newfile"],$filemgmt_FileStore)) {
            if (file_exists($currentfileFQN) && (!is_dir($currentfileFQN))) {
               $err=@unlink ($currentfileFQN);
            }
            $url = rawurlencode($myts->makeTboxData4Save($_FILES['newfile']['name']));
            DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET url='$url' WHERE lid='{$_POST['lid']}'");
       }
    }

    $currentsnapfile = DB_getITEM($_FM_TABLES['filemgmt_filedetail'], 'logourl', "lid='{$_POST['lid']}'");
    $currentSnapFQN = $filemgmt_SnapStore . $myts->makeTboxData4Save(rawurldecode($currentsnapfile));
    $newsnapfile = rawurlencode($myts->makeTboxData4Save($_FILES['newfileshot']['name']));
    if (($newsnapfile !="" AND $currentsnapfile != $newsnapfile)  OR ($newsnapfile != '' and $currentsnapfile == '')) {
        //COM_errorLOG("Snap file has changed");
        if (uploadNewFile($_FILES["newfileshot"],$filemgmt_SnapStore)) {
            if (file_exists($currentSnapFQN) && (!is_dir($currentSnapFQN))) {
               $err=@unlink ($currentSnapFQN);
            }
            $logourl = rawurlencode($myts->makeTboxData4Save($_FILES['newfileshot']['name']));
            DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET logourl='$logourl' WHERE lid='{$_POST['lid']}'");
       }
    } elseif(isset($_POST['deletesnap'])) {
        if (file_exists($currentSnapFQN) && (!is_dir($currentSnapFQN))) {
            $err=@unlink ($currentSnapFQN);
            DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET logourl='' WHERE lid='{$_POST['lid']}'");
            COM_errorLOG("Delete repository snapfile:$currentSnapFQN.");
        }
    }

    $title = $myts->makeTboxData4Save($_POST['title']);
    $homepage = $myts->makeTboxData4Save($_POST['homepage']);
    $version = $myts->makeTboxData4Save($_POST['version']);
    $size = $myts->makeTboxData4Save($_POST['size']);
    $description = $myts->makeTareaData4Save($_POST['description']);
    $commentoption = $_POST['commentoption'];
    if ( $silentEdit ) {
    	DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET cid='$cid', title='$title', url='$url', homepage='$homepage', version='$version', size='$size', status=1, comments='$commentoption' WHERE lid='{$_POST['lid']}'");
	} else {
   		DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET cid='$cid', title='$title', url='$url', homepage='$homepage', version='$version', size='$size', status=1, date=".time().", comments='$commentoption' WHERE lid='{$_POST['lid']}'");
	}
    DB_query("UPDATE {$_FM_TABLES['filemgmt_filedesc']} SET description='$description' WHERE lid='{$_POST['lid']}'");
    CACHE_remove_instance('whatsnew');
    redirect_header("{$_CONF['site_url']}/filemgmt/index.php",2,_MD_DBUPDATED);
    exit();
}

function delDownload() {
    global $_FM_TABLES,$_CONF,$myts,$filemgmt_FileStore,$filemgmt_SnapStore,$eh;

    $lid = $myts->makeTboxData4Save($_POST['lid']);
    $name = $myts->makeTboxData4Save(rawurldecode($_POST['url']));
    $tmpurl = rawurlencode($_POST['url']);
    $tmpfile  = $filemgmt_FileStore . $name;

    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE url='$tmpurl'");
    list($numrows) = DB_fetchARRAY($result);
    $tmpsnap = DB_getItem($_FM_TABLES['filemgmt_filedetail'],'logourl',"lid='$lid'");
    $tmpsnap  = $filemgmt_SnapStore . $tmpsnap;

    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedetail']}  WHERE lid='$lid'");
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedesc']}    WHERE lid='$lid'");
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_votedata']}    WHERE lid='$lid'");
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");

    // Check for duplicate files of the same filename (actual filename in repository)
    // We don't want to delete actual file if there are more then 1 record linking to it.
    // Site may be allowing more then 1 file listing to duplicate files
    if ($numrows > 1) {
         redirect_header("{$_CONF['site_url']}/filemgmt/index.php",2,_MD_FILENOTDELETED);
         exit();
    } else {
        if ($tmpfile != "" && file_exists($tmpfile) && (!is_dir($tmpfile))) {
            $err=@unlink ($tmpfile);
        }
        if ($tmpsnap != "" && file_exists($tmpsnap) && (!is_dir($tmpsnap))) {
            $err=@unlink ($tmpsnap);
        }
    }
    CACHE_remove_instance('whatsnew');
    redirect_header("{$_CONF['site_url']}/filemgmt/index.php",2,_MD_FILEDELETED);
    exit();
}

function modCat() {
    global $_CONF,$_TABLES,$_FM_TABLES,$myts,$eh,$mytree,$LANG_FM02;

    $cid = $_POST["cid"];
    $display = COM_siteHeader('menu');
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");
    $display .= filemgmt_navbar($LANG_FM02['nav2']);
    $display .= '<form action="index.php" method="post" enctype="multipart/form-data" style="margin:0px;">';
    $display .= '<input type="hidden" name="op" value="modCatS">';
    $display .= '<input type="hidden" name="cid" value="'.$cid.'">';
    $display .= '<table width="100%" border="0" class="plugin">';
    $display .= '<tr><td colspan="2" class="pluginHeader" style="width:100%;padding:5px;">' . _MD_MODCAT . '</td></tr>';

    $result = DB_query("SELECT pid, title, imgurl, grp_access,grp_writeaccess FROM {$_FM_TABLES['filemgmt_cat']} WHERE cid='$cid'");
    list($pid,$title,$imgurl,$grp_access,$writeaccess) = DB_fetchARRAY($result);
    $title = $myts->makeTboxData4Edit($title);
    $imgurl = rawurldecode($myts->makeTboxData4Edit($imgurl));

    $display .= '<form action="index.php" method="post" enctype="multipart/form-data">';
    $display .= '<tr><td>' . _MD_TITLEC. '</td><td><input type="text" name="title" value="'.$title.'" size="51" maxlength="50"></td></tr>';
    $display .= '<tr><td>' . _MD_CATSEC. '</td><td><select name="sel_access"><option value="0">Select Access</option>';
    $display .= COM_optionList($_TABLES['groups'], "grp_id,grp_name",$grp_access) . '</select></td></tr>';
    $display .= '<tr><td>' . _MD_UPLOADSEC. '</td><td><select name="sel_uploadaccess"><option value="0">Select Access</option>';
    $display .= COM_optionList($_TABLES['groups'], "grp_id,grp_name",$writeaccess) . '</select></td></tr>';
    $display .= '<tr><td>' ._MD_IMGURLMAIN. '</td><td><input type="file" name="imgurl" value="'.$imgurl.'" size="50" maxlength="100"></td></tr>';
    $display .= '<tr><td>' . _MD_PARENT. '</td><td>';
    $display .= $mytree->makeMySelBox("title", "title", $pid, 1, "pid");
    $display .= '</td></tr>';
    $display .= '<tr><td colspan="2" style="text-align:center;padding:10px;">';
    $display .= '<input type="submit" value="'._MD_SAVE.'">';
    $display .= '<input type="submit" value="'._MD_DELETE.'" onClick=\'if (confirm("Delete this file ?")) {this.form.op.value="delCat";return true}; return false\'>';
    $display .= "&nbsp;<input type=button value="._MD_CANCEL." onclick=\"javascript:history.go(-1)\">";
    $display .= '</td></tr></table>';
    $display .= "</form>";
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
}


function delNewDownload() {
    global $_FM_TABLES,$filemgmt_FileStore,$filemgmt_SnapCat,$filemgmt_SnapStore,$myts,$eh;

    $lid = $_POST['lid'];
    if (DB_count($_FM_TABLES['filemgmt_filedetail'],'lid',$lid) == 1) {
        $tmpnames = explode(";",DB_getItem($_FM_TABLES['filemgmt_filedetail'],'platform',"lid='$lid'"));
        $tmpfilename = $tmpnames[0];
        $tmpshotname = $tmpnames[1];
        $tmpfilename = $filemgmt_FileStore ."tmp/" . $tmpfilename;
        $tmpshotname = $filemgmt_SnapStore ."tmp/" . $tmpshotname;

        DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
        DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedesc']} WHERE lid='$lid'");
        if ($tmpfilename != '' && file_exists($tmpfilename) && (!is_dir($tmpfilename))) {
            $err=@unlink ($tmpfilename);
            //COM_errorLOG("Delete submitted file: ".$tmpfilename." Return status of unlink is: ".$err);
        }
        if ($tmpshotname != '' && file_exists($tmpshotname) && (!is_dir($tmpshotename))) {
            $err=@unlink ($tmpshotname);
            //COM_errorLOG("Delete submitted snapshot: ".$tmpshotname." Return status of unlink is: ".$err);
        }
        CACHE_remove_instance('whatsnew');
        redirect_header("index.php?op=listNewDownloads",1,_MD_FILEDELETED);
    } else {
        redirect_header("index.php?op=listNewDownloads",1,_MD_ERRORNOFILE);
    }
    exit();
}



function modCatS() {
    global $_CONF,$_FM_TABLES,$myts,$eh, $filemgmt_SnapCat;

    $cid =  $_POST['cid'];
    $sid =  $_POST['pid'];
    $title =  $myts->makeTboxData4Save($_POST['title']);
    $title = str_replace('/','&#47',$title);
    $grp_access = $_POST['sel_access'];
    if ($grp_access < 1 ) {
        $grp_access = 2;  // All Users Group
    }
    $write_access = $_POST['sel_uploadaccess'];
    if ($write_access < 1 ) {
        $write_access = 2;  // All Users Group
    }
    if ($_FILES['imgurl']['name']!='') {
        $name = $_FILES['imgurl']['name'];        // this is the real name of your file
        $tmp  = $_FILES['imgurl']['tmp_name'];    // temporary name of file in temporary directory on server
        $imgurl = rawurlencode($myts->makeTboxData4Save($name));
        $target = $filemgmt_SnapCat.$name;
        if (is_uploaded_file($_FILES['imgurl']['tmp_name'])) {                       // is this temporary file really uploaded?
           if(!file_exists($target)) {       // Check to see the file already exists
            $returnMove = move_uploaded_file($tmp, $target);    // move temporary file to your upload directory
            }
         }
    } else {
        $imgurl = '';
    }

    $sql = "UPDATE {$_FM_TABLES['filemgmt_cat']} ";
    $sql .= "SET title='$title', imgurl='$imgurl', pid='$sid', grp_access=$grp_access, grp_writeaccess=$write_access ";
    $sql .= "where cid='$cid'";
    DB_query($sql);
    CACHE_remove_instance('whatsnew');
    redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php",2,_MD_DBUPDATED);
    exit();
}

function delCat() {
    global $_CONF,$_FM_TABLES,$eh, $mytree,$filemgmt_FileStore,$filemgmt_SnapCat,$filemgmt_SnapStore;

    $cid =  $_POST['cid'];
    //get all subcategories under the specified category
    $arr=$mytree->getAllChildId($cid);
    for($i=0;$i<sizeof($arr);$i++){
        //get all downloads in each subcategory
        $result = DB_query("SELECT lid,url,logourl FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE cid='{$arr[$i]}'");
        //now for each download, delete the text data and votes associated with the download
        while(list($lid,$url,$logourl)= DB_fetchARRAY($result)){
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedesc']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_votedata']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");
            $name = rawurldecode($url);
            $fullname  = $filemgmt_FileStore . $name;
            if ($fullname !="" && file_exists($fullname) && (!is_dir($fullname))) {
                $err=@unlink($fullname);
            }
            $name = rawurldecode($logourl);
            $fullname  = $filemgmt_SnapStore . $name;
            if ($fullname !="" && file_exists($fullname) && (!is_dir($fullname))) {
                $err=@unlink($fullname);
            }
        }
        //all downloads for each subcategory is deleted, now delete the subcategory data
        $catimage = DB_getItem($_FM_TABLES['filemgmt_cat'],'imgurl', "cid='{$arr[$i]}'");
        $catimage_filename = $filemgmt_SnapCat . $catimage;
        if ($catimage != '' && file_exists($catimage_filename) && (!is_dir($catimage_filename))) {
            // Check that there is only one category using this image
            if (DB_count($_FM_TABLES['filemgmt_cat'],'imgurl',$catimage) == 1) {
                @unlink($catimage_filename);
            }
        }
        DB_query("DELETE FROM {$_FM_TABLES['filemgmt_cat']} WHERE cid='{$arr[$i]}'");
    }
    //all subcategory and associated data are deleted, now delete category data and its associated data
    $result = DB_query("SELECT lid,url,logourl FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE cid='$cid'");
    while(list($lid,$url,$logourl)= DB_fetchARRAY($result)){
       DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE lid='$lid'");
       DB_query("DELETE FROM {$_FM_TABLES['filemgmt_filedesc']} WHERE lid='$lid'");
       DB_query("DELETE FROM {$_FM_TABLES['filemgmt_brokenlinks']} WHERE lid='$lid'");

       $name = rawurldecode($url);
            $fullname  = $filemgmt_FileStore . $name;
            if ($fullname !="" && file_exists($fullname) && (!is_dir($fullname))) {
                $err=@unlink($fullname);
            }
            $name = rawurldecode($logourl);
            $fullname  = $filemgmt_SnapStore . $name;
            if ($fullname != '' && file_exists($fullname) && (!is_dir($fullname))) {
                $err=@unlink($fullname);
            }
    }
    $catimage = DB_getItem($_FM_TABLES['filemgmt_cat'],'imgurl', "cid='$cid'");
    $catimage_filename = $filemgmt_SnapCat . $catimage;
    if ($catimage != '' && file_exists($catimage_filename) && (!is_dir($catimage_filename))) {
        // Check that there is only one category using this image
        if (DB_count($_FM_TABLES['filemgmt_cat'],'imgurl',$catimage) == 1) {
            @unlink($catimage_filename);
        }
    }
    DB_query("DELETE FROM {$_FM_TABLES['filemgmt_cat']} WHERE cid='$cid'");
    CACHE_remove_instance('whatsnew');
    redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php?op=categoryConfigAdmin",2,_MD_CATDELETED);
    exit();

}

function addCat() {
    global $_CONF, $_FM_TABLES, $filemgmt_SnapCat,$filemgmt_SnapCatURL,$myts,$eh;

    $pid = $_POST['cid'];
    $title = $_POST['title'];
    $title = str_replace('/','&#47',$title);
    $grp_access = $_POST['sel_access'];
    if ($grp_access < 2) {
       $grp_access = 2;
    }
    $write_access = $_POST['sel_uploadaccess'];
    if ($write_access < 2) {
       $write_access = 2;
    }
    if ($title != '') {
        $title = $myts->makeTboxData4Save($title);
        if ($_FILES["uploadfile"]["name"]!="") {
            $name = $_FILES["uploadfile"]['name'];        // this is the real name of your file
            $tmp  = $_FILES["uploadfile"]['tmp_name'];    // temporary name of file in temporary directory on server
            $imgurl = rawurlencode($myts->makeTboxData4Save($name));
            if (is_uploaded_file ($tmp)) {                       // is this temporary file really uploaded?
               if(!file_exists($filemgmt_SnapCat.$name)) {       // Check to see the file already exists
                $target = $filemgmt_SnapCat.$name;
                $returnMove = move_uploaded_file($tmp, $target);    // move temporary file to your upload directory
                }
             }
        } else {
            $imgurl = '';
        }
        $sql = "INSERT INTO {$_FM_TABLES['filemgmt_cat']} (pid, title, imgurl,grp_access,grp_writeaccess) ";
        $sql .= "VALUES ('$pid', '$title', '$imgurl',$grp_access,$write_access)";
        DB_query($sql);
    }
    CACHE_remove_instance('whatsnew');
    redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php?op=categoryConfigAdmin",2,_MD_NEWCATADDED);
    exit();
}

function addDownload() {
    global $_CONF,$_USER,$_FM_TABLES,$filemgmt_FileStoreURL,$filemgmt_FileSnapURL,$filemgmt_FileStore,$filemgmt_SnapStore;
    global $myts,$eh;

    $filename = $myts->makeTboxData4Save($_FILES['newfile']['name']);
    $url = $myts->makeTboxData4Save(rawurlencode($filename));
    $snapfilename = $myts->makeTboxData4Save($_FILES['newfileshot']['name']);
    $logourl = $myts->makeTboxData4Save(rawurlencode($snapfilename));
    $title = $myts->makeTboxData4Save($_POST['title']);
    $homepage = $myts->makeTboxData4Save($_POST['homepage']);
    $version = $myts->makeTboxData4Save($_POST['version']);
    $description = $myts->makeTareaData4Save($_POST['description']);
    $commentoption = $_POST['commentoption'];
    $submitter = $_USER['uid'];
    $size = $myts->makeTboxData4Save(intval($_FILES['newfile']['size']));
    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE url='$url'");
    list($numrows) = DB_fetchARRAY($result);
    $errormsg = "";

    // Check if Title blank
    if ($title=="") {
        $eh->show("1104");
     }
    // Check if Description blank
    if ($description=="") {
           $eh->show("1105");
     }
     // Check if a file was uploaded
    if ($_FILES['newfile']['size'] == 0) {
        $eh->show("1017");
    }

    if ( !empty($_POST['cid']) ) {
           $cid = $_POST['cid'];
    } else {
        $cid = 0;
    }

    if (uploadNewFile($_FILES["newfile"],$filemgmt_FileStore)) {
        $AddNewFile = true;
    }
    if (uploadNewFile($_FILES["newfileshot"],$filemgmt_SnapStore)) {
        $AddNewFile = true;
    }

    if ($AddNewFile){
        $fields = 'cid, title, url, homepage, version, size, logourl, submitter, status, date, hits, rating, votes, comments';
        $sql = "INSERT INTO {$_FM_TABLES['filemgmt_filedetail']} ($fields) VALUES ";
        $sql .= "('$cid','$title','$url','$homepage','$version','$size','$logourl','$submitter',1,UNIX_TIMESTAMP(),0,0,0,'$commentoption')";
        DB_query($sql);
        $newid = DB_insertID();
        DB_query("INSERT INTO {$_FM_TABLES['filemgmt_filedesc']} (lid, description) VALUES ($newid, '$description')");
        CACHE_remove_instance('whatsnew');
        if (isset($duplicatefile) && $duplicatefile) {
            redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php",2,_MD_NEWDLADDED_DUPFILE);
        } elseif (isset($duplicatesnap) && $duplicatesnap) {
            redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php",2,_MD_NEWDLADDED_DUPSNAP);
        } else {
            redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php",2,_MD_NEWDLADDED);
        }
        exit();

    } else {
        redirect_header("index.php",2,_MD_ERRUPLOAD."");
        exit();
    }

}


function approve(){
    global $_FM_TABLES,$_TABLES,$_CONF,$myts,$eh,$filemgmt_FileStore,$filemgmt_SnapStore,$filemgmt_Emailoption,$filemgmtFilePermissions;

    $lid = $_POST['lid'];
    $title = $_POST['title'];
    $cid = $_POST['cid'];
    if ( empty($cid) ) {
        $cid = 0;
    }
    $homepage = $_POST['homepage'];
    $version = $_POST['version'];
    $size = $_POST['size'];
    $description = $_POST['description'];
    if (($_POST['url']) || ($_POST['url'] != '')) {
        $name = $myts->makeTboxData4Save($_POST['url']);
        $url = rawurlencode($name);
    }
    if (($_POST['logourl']) || ($_POST['logourl'] != '')) {
        $shotname = $myts->makeTboxData4Save($_POST['logourl']);
        $logourl = $myts->makeTboxData4Save(rawurlencode($_POST['logourl']));
    }

    $result = DB_query("SELECT COUNT(*) FROM {$_FM_TABLES['filemgmt_filedetail']} WHERE url='$url' and status=1");
    list($numrows) = DB_fetchARRAY($result);

    // Comment out this check if you want to allow duplicate filelistings for same file in the repository
    // Check for duplicate files of the same filename (actual filename in repository)
    if ($numrows>0) {
        $eh->show("1108");
    }

    $title = $myts->makeTboxData4Save($title);
    $homepage = $myts->makeTboxData4Save($homepage);
    $version = $myts->makeTboxData4Save($_POST['version']);
    $size = $myts->makeTboxData4Save($_POST['size']);
    $description = $myts->makeTareaData4Save($description);
    $commentoption = $_POST["commentoption"];

    // Move file from tmp directory under the document filestore to the main file directory
    // Now to extract the temporary names for both the file and optional thumbnail. I've used th platform field which I'm not using now for anything.
    $tmpnames = explode(";",DB_getItem($_FM_TABLES['filemgmt_filedetail'],'platform',"lid='$lid'"));
    $tmpfilename = $tmpnames[0];
    $tmpshotname = $tmpnames[1];
    $tmp = $filemgmt_FileStore ."tmp/" . $tmpfilename;
    if (file_exists($tmp) && (!is_dir($tmp))) {                      // if this temporary file was really uploaded?
        $newfile = $filemgmt_FileStore .$name;
        COM_errorLOG("File move from ".$tmp. " to " .$newfile );
        $rename = @rename ($tmp,$newfile);
        COM_errorLOG("Results of rename is: ".$rename);
        $chown = @chmod ($newfile,$filemgmtFilePermissions);
        if (!file_exists($newfile )) {
            COM_errorLOG("Filemgmt upload approve error: New file does not exist after move of tmp file: '".$newfile ."'");
            $AddNewFile = false;    // Set false again - in case it was set true above for actual file
            $eh->show("1101");
        } else {
           $AddNewFile = true;
        }
    } else {
        COM_errorLOG("Filemgmt upload approve error: Temporary file does not exist: '".$tmp ."'");
        $eh->show("1101");
    }

    if ($tmpshotname !="") {
        $tmp  = $filemgmt_SnapStore ."tmp/" . $tmpshotname;
        if (file_exists($tmp) && (!is_dir($tmp))) {                // if this temporary Thumbnail was really uploaded?
            $newfile = $filemgmt_SnapStore .$shotname;
            $rename = @rename ($tmp,$newfile);
            $chown = @chmod ($newfile,$filemgmtFilePermissions);
            if (!file_exists($newfile )) {
                COM_errorLOG("Filemgmt upload approve error: New file does not exist after move of tmp file: '".$newfile ."'");
                $AddNewFile = false;    // Set false again - in case it was set true above for actual file
                $eh->show("1101");
            }
        } else {
            COM_errorLOG("Filemgmt upload approve error: Temporary file does not exist: '".$tmp ."'");
            $eh->show("1101");
        }
    }
    if ($AddNewFile) {
        DB_query("UPDATE {$_FM_TABLES['filemgmt_filedetail']} SET cid='$cid', title='$title', url='$url', homepage='$homepage', version='$version', size='$size', logourl='$logourl', status=1, date=".time() .", comments='$commentoption' where lid='$lid'");
        DB_query("UPDATE {$_FM_TABLES['filemgmt_filedesc']} SET description='$description' where lid='$lid'");
        CACHE_remove_instance('whatsnew');
        // Send a email to submitter notifying them that file was approved
        if ($filemgmt_Emailoption) {
            $result = DB_query("SELECT username, email FROM {$_TABLES['users']} a, {$_FM_TABLES['filemgmt_filedetail']} b WHERE a.uid=b.submitter and b.lid='$lid'");
            list ($submitter_name, $emailaddress) = DB_fetchARRAY($result);
            $mailtext  = sprintf(_MD_HELLO,$submitter_name);
            $mailtext .= ",\n\n" ._MD_WEAPPROVED. " " .$title. " \n" ._MD_THANKSSUBMIT. "\n\n";
            $mailtext .= "{$_CONF["site_name"]}\n";
            $mailtext .= "{$_CONF['site_url']}\n";
            //COM_errorLOG("email: ".$emailaddress.", text: ".$mailtext);
            if (function_exists(COM_mail) ) {
                COM_mail($emailaddress,_MD_APPROVED,$mailtext);
            } else {
                mail($emailaddress ,"{$_CONF["site_name"]}: " . _MD_UPLOADAPPROVED ,$mailtext, "From: {$_CONF["site_name"]} <{$_CONF["site_mail"]}>\nReturn-Path: <{$_CONF["site_mail"]}>\nX-Mailer: GeekLog $VERSION" );
            }
         }
    }
    CACHE_remove_instance('whatsnew');
    redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php?op=listNewDownloads",2,_MD_NEWDLADDED);
    exit();
}


function filemgmtConfigAdmin() {
    global $filemgmt_AllUserAccess, $filemgmt_AllowUserUploads,$LANG_FM02;
    global $mydownloads_perpage, $mydownloads_popular, $mydownloads_newdownloads, $mydownloads_trimdesc, $mydownloads_dlreport;
    global $mydownloads_selectpriv, $mydownloads_uploadselect, $mydownloads_publicpriv, $mydownloads_uploadpublic;
    global $mydownloads_useshots, $mydownloads_shotwidth, $mydownloads_whatsnew, $filemgmt_Emailoption;
    global $filemgmt_FileStoreURL,$filemgmt_FileSnapURL, $filemgmt_FileStore, $filemgmt_SnapStore, $filemgmt_SnapCat, $filemgmt_SnapCatURL;

    $display = COM_siteHeader('menu');
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");
    $display .= filemgmt_navbar($LANG_FM02['nav1']);
    $display .= '<form action="index.php" method="post" style="margin:0px;">';
    $display .= '<table width="100%" border="0" class="plugin">';
    $display .= '<tr><th colspan="2" class="pluginHeader" style="width:100%;padding:5px;">' . _MD_GENERALSET . '</th></tr>';
    $display .= '<tr class="pluginRow1"><td style="white-space:nowrap;">' ._MD_DLSPERPAGE. '</td>';
    $display .= "<td>
        <select name=\"xmydownloads_perpage\">
        <option value=\"$mydownloads_perpage\" selected=\"selected\">$mydownloads_perpage</option>
        <option value=\"5\">5</option>
        <option value=\"10\">10</option>
        <option value=\"15\">15</option>
        <option value=\"20\">20</option>
        <option value=\"25\">25</option>
        <option value=\"30\">30</option>
        <option value=\"50\">50</option>
        </select>
        </td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">
        "._MD_HITSPOP."</td><td>
        <select name=\"xmydownloads_popular\">
        <option value=\"$mydownloads_popular\" selected=\"selected\">$mydownloads_popular</option>
        <option value=\"5\">5</option>
        <option value=\"10\">10</option>
        <option value=\"20\">20</option>
        <option value=\"50\">50</option>
        <option value=\"100\">100</option>
        <option value=\"500\">500</option>
        <option value=\"1000\">1000</option>
        </select>
        </td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">
        "._MD_DLSNEW."</td><td>
        <select name=\"xmydownloads_newdownloads\">
        <option value=\"$mydownloads_newdownloads\" selected=\"selected\">$mydownloads_newdownloads</option>
        <option value=\"10\">10</option>
        <option value=\"15\">15</option>
        <option value=\"20\">20</option>
        <option value=\"25\">25</option>
        <option value=\"30\">30</option>
        <option value=\"50\">50</option>
       </select><br" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow2\"><td  style=\"white-space:nowrap;\">" . _MD_DLREPORT . " </td><td>";
    if ($mydownloads_dlreport==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_dlreport\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_dlreport\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_dlreport\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_dlreport\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">" . _MD_TRIMDESC . " </td><td>";
    if ($mydownloads_trimdesc==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_trimdesc\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_trimdesc\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_trimdesc\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_trimdesc\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">" . _MD_WHATSNEWDESC . " </td><td>";
    if ($mydownloads_whatsnew==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_whatsnew\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_whatsnew\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_whatsnew\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_whatsnew\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr><tr class=\"pluginRow1\"><td colspan=\"2\"><hr" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">" . _MD_SELECTPRIV . " </td><td>";
    if ($mydownloads_selectpriv==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_selectpriv\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_selectpriv\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_selectpriv\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_selectpriv\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">" . _MD_UPLOADSELECT . " </td><td>";
    if ($mydownloads_uploadselect==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadselect\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadselect\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadselect\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadselect\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
      $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">" . _MD_ACCESSPRIV . " </td><td>";
    if ($mydownloads_publicpriv==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_publicpriv\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_publicpriv\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_publicpriv\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_publicpriv\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">" . _MD_UPLOADPUBLIC . " </td><td>";
    if ($mydownloads_uploadpublic==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadpublic\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadpublic\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadpublic\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_uploadpublic\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr><tr class=\"pluginRow1\"><td colspan=\"2\"><hr" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">" . _MD_USESHOTS . " </td><td>";
    if ($mydownloads_useshots==1) {
        $display .= "<input type=\"radio\" name=\"xmydownloads_useshots\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_useshots\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"xmydownloads_useshots\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"xmydownloads_useshots\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }
    $display .= "</td></tr>";
    $display .= "<tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">" . _MD_IMGWIDTH . " </td><td>";
    if($mydownloads_shotwidth!=""){
       $display .= "<input type=\"text\" size=\"10\" name=\"xmydownloads_shotwidth\" value=\"$mydownloads_shotwidth\"" . XHTML . ">";
    }else{
       $display .= "<input type=\"text\" size=\"10\" name=\"xmydownloads_shotwidth\" value=\"140\"" . XHTML . ">";
    }

    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">"._MD_EMAILOPTION."</td><td>";
    if ($filemgmt_Emailoption == true) {
        $display .= "<input type=\"radio\" name=\"my_emailoption\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"my_emailoption\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    } else {
        $display .= "<input type=\"radio\" name=\"my_emailoption\" value=\"1\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"my_emailoption\" value=\"0\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
    }

    $display .= "</td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">Directory to store files: </td><td>";
    $display .= "<input type='text' size='60' maxlength='150' name='my_filestore' value='$filemgmt_FileStore'" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">Directory to store file thumbnails: </td><td>";
    $display .= "<input type='text' size='60' maxlength='150' name='my_snapstore' value='$filemgmt_SnapStore'" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">Directory to store category thumbnails: </td><td>";
    $display .= "<input type='text' size='60' maxlength='150' name='my_snapcat' value='$filemgmt_SnapCat'" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">URL to files: </td><td>";
    $display .= "<input type='text' size='60' maxlength='150' name='my_filestoreurl' value='$filemgmt_FileStoreURL'" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow1\"><td style=\"white-space:nowrap;\">URL to file thumbnails: </td><td>";
    $display .= "<input type='text' size='60' maxlength='150' name='my_filesnapurl' value='$filemgmt_FileSnapURL'" . XHTML . ">";
    $display .= "</td></tr><tr class=\"pluginRow2\"><td style=\"white-space:nowrap;\">URL to category thumbnails: </td><td>";
    $display .= "<input type='text' size='60' maxlength='150' name='my_snapcaturl' value='$filemgmt_SnapCatURL'" . XHTML . ">";
    $display .= "</td></tr>";
    $display .= '<tr><td colspan="2" style="padding:10px;text-align:center">';
    $display .= "<input type=\"hidden\" name=\"op\" value=\"filemgmtConfigChange\"" . XHTML . ">";
    $display .= "<input type=\"submit\" value=\""._MD_SAVE."\"" . XHTML . ">";
    $display .= "&nbsp;<input type=\"button\" value=\""._MD_CANCEL."\" onclick=\"javascript:history.go(-1)\"" . XHTML . ">";
    $display .= "</td></tr></table>";
    $display .= "</form>";
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
}

function filemgmtConfigChange($op='') {
        global $_TABLES, $_CONF;
        global $filemgmt_AllUserAccess, $filemgmt_AllowUserUploads;
        global $mydownloads_perpage, $mydownloads_popular, $mydownloads_newdownloads, $mydownloads_trimdesc, $mydownloads_dlreport;
        global $mydownloads_selectpriv, $mydownloads_uploadselect, $mydownloads_publicpriv, $mydownloads_uploadpublic;
        global $mydownloads_useshots, $mydownloads_shotwidth, $mydownloads_whatsnew, $filemgmt_Emailoption;
        global $filemgmt_FileStoreURL,$filemgmt_FileSnapURL, $filemgmt_FileStore, $filemgmt_SnapStore, $filemgmt_SnapCat, $filemgmt_SnapCatURL;

        $configfile                 = $_CONF['path'] . 'plugins/filemgmt/filemgmt.php';

        if ($op == 'init') {
            $xmydownloads_popular       = $mydownloads_popular;
            $xmydownloads_newdownloads  = $mydownloads_newdownloads;
            $xmydownloads_perpage       = $mydownloads_perpage;
            $xmydownloads_dlreport      = $mydownloads_dlreport;
            $xmydownloads_trimdesc      = $mydownloads_trimdesc;
            $xmydownloads_selectpriv    = $mydownloads_selectpriv;
            $xmydownloads_publicpriv    = $mydownloads_publicpriv;
            $xmydownloads_uploadselect  = $mydownloads_uploadselect;
            $xmydownloads_uploadpublic  = $mydownloads_uploadpublic;
            $xmydownloads_useshots      = $mydownloads_useshots;
            $xmydownloads_shotwidth     = $mydownloads_shotwidth;
            $xmydownloads_whatsnew      = $mydownloads_whatsnew;
            $my_emailoption             = $filemgmt_Emailoption;
            $my_filestoreurl            = $_CONF['site_url'] . '/filemgmt_data/files/';
            $my_filesnapurl             = $_CONF['site_url'] . '/filemgmt_data/snaps/';
            $my_snapcaturl              = $_CONF['site_url'] . '/filemgmt_data/category_snaps/';
            $my_filestore               = $_CONF['path_html'] . 'filemgmt_data/files/';
            $my_snapstore               = $_CONF['path_html'] . 'filemgmt_data/snaps/';
            $my_snapcat                 = $_CONF['path_html'] . 'filemgmt_data/category_snaps/';

        } else {
            $xmydownloads_popular       = $_POST['xmydownloads_popular'];
            $xmydownloads_newdownloads  = $_POST['xmydownloads_newdownloads'];
            $xmydownloads_perpage       = $_POST['xmydownloads_perpage'];
            $xmydownloads_dlreport      = $_POST['xmydownloads_dlreport'];
            $xmydownloads_trimdesc      = $_POST['xmydownloads_trimdesc'];
            $xmydownloads_selectpriv    = $_POST['xmydownloads_selectpriv'];
            $xmydownloads_publicpriv    = $_POST['xmydownloads_publicpriv'];
            $xmydownloads_uploadselect  = $_POST['xmydownloads_uploadselect'];
            $xmydownloads_uploadpublic  = $_POST['xmydownloads_uploadpublic'];
            $xmydownloads_useshots      = $_POST['xmydownloads_useshots'];
            $xmydownloads_shotwidth     = $_POST['xmydownloads_shotwidth'];
            $xmydownloads_whatsnew      = $_POST['xmydownloads_whatsnew'];
            $my_emailoption             = $_POST['my_emailoption'];
            $my_filestoreurl            = $_POST['my_filestoreurl'];
            $my_filesnapurl             = $_POST['my_filesnapurl'];
            $my_snapcaturl              = $_POST['my_snapcaturl'];
            $my_filestore               = $_POST['my_filestore'];
            $my_snapstore               = $_POST['my_snapstore'];
            $my_snapcat                 = $_POST['my_snapcat'];
        }

        // Check to see if Access Priv or Upload priv have changed
        // Will need to update the GL access table if they have

        $feature1_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = 'filemgmt.user'");
        $feature2_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = 'filemgmt.upload'");

        if ($xmydownloads_selectpriv != $mydownloads_selectpriv ) {
            // Note: assuming "Logged-in Users" group is 13 - always has been
            $result = DB_query("SELECT COUNT(*) FROM {$_TABLES['access']} WHERE acc_ft_id = '$feature1_id' AND acc_grp_id = 13");
            list($nrows) = DB_fetchArray($result);
            if ($xmydownloads_selectpriv == 1 && $nrows == 0) {   // Enable and there is no record now
                COM_errorLog('Granting Logged-In users access to filemgmt.user feature',1);
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ('$feature1_id', 13)");
            } elseif ($xmydownloads_selectpriv == 0 && $nrows == 1) {  // Disable and there is a record
                COM_errorLog('Removing Logged-In users access with filemgmt.user feature',1);
                DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_grp_id = 13 AND acc_ft_id = '$feature1_id'");
            }
        }
        if ($xmydownloads_publicpriv != $mydownloads_publicpriv ) {
            $result = DB_query("SELECT COUNT(*) FROM {$_TABLES['access']} WHERE acc_ft_id = '$feature1_id' AND acc_grp_id = 2");
            list($nrows) = DB_fetchArray($result);
            if ($xmydownloads_publicpriv == 1 && $nrows == 0) {   // Enable and there is no record now
                COM_errorLog('Granting anonymous access to filemgmt.user feature',1);
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ('$feature1_id', 2)");
            } elseif ($xmydownloads_publicpriv == 0 && $nrows == 1) {  // Disable and there is a record
                COM_errorLog('Removing anonymous access with filemgmt.user feature',1);
                DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_grp_id = 2 AND acc_ft_id = '$feature1_id'");
            }
        }
        if ($xmydownloads_uploadselect != $mydownloads_uploadselect ) {
            // Note: assuming "Logged-in Users" group is 13 - always has been
            $result = DB_query("SELECT COUNT(*) FROM {$_TABLES['access']} WHERE acc_ft_id = '$feature2_id' AND acc_grp_id = 13");
            list($nrows) = DB_fetchArray($result);
            if ($xmydownloads_uploadselect == 1 && $nrows == 0) {   // Enable and there is no record now
                COM_errorLog('Granting Logged-In users upload privilage to filemgmt.user feature',1);
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ('$feature2_id', 13)");
            } elseif ($xmydownloads_uploadselect == 0 && $nrows == 1) {  // Disable and there is a record
                COM_errorLog('Removing Logged-In users upload privilage with filemgmt.user feature',1);
                DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_grp_id = 13 AND acc_ft_id = '$feature2_id'");
            }
        }
        if ($xmydownloads_uploadpublic != $mydownloads_uploadpublic ) {
            $result = DB_query("SELECT COUNT(*) FROM {$_TABLES['access']} WHERE acc_ft_id = '$feature2_id' AND acc_grp_id = 2");
            list($nrows) = DB_fetchArray($result);
            if ($xmydownloads_uploadpublic == 1 && $nrows == 0) {   // Enable and there is no record now
                COM_errorLog('Granting anonymous upload privilage to filemgmt.user feature',1);
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ('$feature2_id', 2)");
            } elseif ($xmydownloads_uploadpublic == 0 && $nrows == 1) {  // Disable and there is a record
                COM_errorLog('Removing anonymous upload privilage with filemgmt.user feature',1);
                DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_grp_id = 2 AND acc_ft_id = '$feature2_id'");
            }
        }

        $file = fopen($configfile, "w");
        $content = "";
        $content .= "<?php\n";
        $content .= "\n";
        $content .= "\$mydownloads_popular      = $xmydownloads_popular;\n";
        $content .= "\$mydownloads_newdownloads = $xmydownloads_newdownloads;\n";
        $content .= "\$mydownloads_perpage      = $xmydownloads_perpage;\n";
        $content .= "\$mydownloads_trimdesc     = $xmydownloads_trimdesc;\n";
        $content .= "\$mydownloads_whatsnew     = $xmydownloads_whatsnew;\n";
        $content .= "\$mydownloads_dlreport     = $xmydownloads_dlreport;\n";
        $content .= "\$mydownloads_selectpriv   = $xmydownloads_selectpriv;\n";
        $content .= "\$mydownloads_publicpriv   = $xmydownloads_publicpriv;\n";
        $content .= "\$mydownloads_uploadselect = $xmydownloads_uploadselect;\n";
        $content .= "\$mydownloads_uploadpublic = $xmydownloads_uploadpublic;\n";
        $content .= "\$mydownloads_useshots     = $xmydownloads_useshots;\n";
        $content .= "\$mydownloads_shotwidth    = $xmydownloads_shotwidth;\n";
        $content .= "\$filemgmt_Emailoption     = $my_emailoption;\n";
        $content .= "\$filemgmt_FileStore        = \"$my_filestore\";\n";
        $content .= "\$filemgmt_SnapStore        = \"$my_snapstore\";\n";
        $content .= "\$filemgmt_SnapCat          = \"$my_snapcat\";\n";
        $content .= "\$filemgmt_FileStoreURL     = \"$my_filestoreurl\";\n";
        $content .= "\$filemgmt_FileSnapURL      = \"$my_filesnapurl\";\n";
        $content .= "\$filemgmt_SnapCatURL       = \"$my_snapcaturl\";\n";
        $content .= "\n";
        $content .= "?>\n";

        fwrite($file, $content);
        fclose($file);
        CACHE_remove_instance('whatsnew');

}

function uploadNewFile($newfile,$directory) {
    global $myts,$eh,$filemgmtDuplicatesAllowed,$filemgmtFilePermissions;

    if ($newfile["name"]!="") {
        $name = $newfile['name'];        // this is the real name of your file
        $tmp  = $newfile['tmp_name'];    // temporary name of file in temporary directory on server
        $name = $myts->makeTboxData4Save($name);
        $logourl = rawurlencode($name);
        COM_errorLOG("AddNewFileShot - Upload filename  is " .$directory.$myts->makeTboxData4Save($name));
        if (is_uploaded_file ($tmp)) {             // is this temporary file really uploaded?
            $newfile = $directory.$name;
            if(!file_exists($newfile)) {   // Check to see the snapfile already exists
                $returnMove = move_uploaded_file($tmp, $newfile);    // move temp file to upload directory
                if (!$returnMove) {
                    COM_errorLOG("Filemgmt File add by admin error: New file could not be created: ".$tmp." to ".$name);
                    $eh->show("1106");
                    return false;
                } else {
                    $chown =@chmod ($newfile,$filemgmtFilePermissions);
                    COM_errorLOG("File uploaded and moved ok");
                    return true;
                }
            } else {
                // Allow duplicate file names, user may want to have two filelisting to same file or has already copied the files manually
                COM_errorLOG("Filemgmt - Warning: Added new filelisting for a file that already exists ". $directory.$name);
                if (!$filemgmtDuplicatesAllowed) {
                    $eh->show("1108");
                    return false;
                } else {
                   return true;
                }
            }
        } else {
            COM_errorLOG("Filemgmt upload error: Temporary file does not exist: '".$tmp ."'");
            $eh->show("1107");
            return false;
        }
    }
    return false;
}



function filemgmt_comments($firstcomment) {
    global $_USER,$_CONF;

    $comment_id  = "filemgmt-".$_GET['lid'];
    $file = $_GET['filename'];
    if ($firstcomment) {
        $story=$comment_id;
        $pid=0;
        $type="filemgmt";
        echo COM_refresh($_CONF['site_url'] . "/comment.php?sid=$story&amp;pid=$pid&amp;type=$type");
    } else {
        $display = COM_siteHeader() . COM_userComments($comment_id,$file,'filemgmt','','nested');
        $display .= COM_siteFooter();
    }
    echo $display;
    exit();

}

function filemgmt_error($errormsg) {
    $display .= COM_startBlock("<b>"._MD_ADMINTITLE."</b>");
    $display .= $errormsg;
    $display .= COM_endBlock();
    echo $display;
    exit();
}


// Check and see if the current config has values for the filemgmt repository set.
// If not - then initialize these variables and write the filemgmt.php - config file.
if ($filemgmt_FileStoreURL == '' OR $filemgmt_FileStore == '') {
    // Set default values and write over the config file
    filemgmtConfigChange('init');
    // Read in the new values
    include ($_CONF['path'] .'plugins/filemgmt/filemgmt.php');
}

//debugbreak();
switch ($op) {
        default:
            mydownloads();
            break;
        case "comment":
            filemgmt_comments($firstcomment);
            break;
        case "delNewDownload":
            delNewDownload();
            break;
        case "addCat":
            addCat();
            break;
        case "addSubCat":
            addSubCat();
            break;
        case "addDownload":
            addDownload();
            break;
        case "listBrokenDownloads":
            listBrokenDownloads();
            break;
        case "delBrokenDownloads":
            delBrokenDownloads();
            break;
        case "ignoreBrokenDownloads":
            ignoreBrokenDownloads();
            break;
        case "approve":
            approve();
            break;
        case "delVote":
            delVote();
            modDownload();
            break;
        case "delCat":
            delCat();
            break;
        case "modCat":
            modCat();
            break;
        case "modCatS":
            modCatS();
            break;
        case "modDownload":
            modDownload();
            break;
        case "modDownloadS":
            modDownloadS();
            break;
        case "delDownload":
            delDownload();
            break;
        case "filemgmtConfigAdmin":
            filemgmtConfigAdmin();
            break;
        case "filemgmtConfigChange":
            filemgmtConfigChange();
            redirect_header("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php",2,_MD_CONFIGUPDATED);
            exit();
            break;
        case "categoryConfigAdmin":
            categoryConfigAdmin();
            break;
        case "newfileConfigAdmin":
              newfileConfigAdmin();
            break;
        case "listNewDownloads":
            listNewDownloads();
            break;
}

?>