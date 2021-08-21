<?php
/**
* glFusion CMS
*
* FileMgmt Plugions - glFusion CMS
*
* Administrative interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*  Consultants4Hire Inc.
*  Blaine Lang       blaine@portalparts.com
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';
include_once $_CONF['path'].'plugins/filemgmt/include/xoopstree.php';
include_once $_CONF['path'].'plugins/filemgmt/include/textsanitizer.php';
include_once $_CONF['path'].'plugins/filemgmt/include/errorhandler.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\FieldList;
use Filemgmt\Download;
use Filemgmt\Models\Status;

USES_lib_admin();

// Set view and action variables.
$op = 'files';
$expected = array(
    // Actions to perform
    'saveCat', 'delCat', 'delVote', 'delDownload', 'saveDownload',
    'ignoreBrokenLink', 'delBrokenLink',
    // Views to display
    'modCat', 'categoryConfigAdmin',
    'modDownload', 'newfileConfigAdmin', 'listNewDownloads', 'files',
    'listBrokenLinks', 'reportBrokenLink',
    // Check "op" last
    'op'
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $op = $provided;
        $opval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
        $op = $provided;
        $opval = $_GET[$provided];
        break;
    }
}
if ($op == 'op') {
    // legacy support
    $op = $opval;
}

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

$myts = new MyTextSanitizer;
$mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");
$eh = new ErrorHandler;

if ( isset($_POST['cancel']) ) {
    echo COM_refresh($_CONF['site_url'].'/filemgmt/index.php');
    exit;
}

$content = '';
switch ($op) {
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
case "saveDownload":
    $status = Filemgmt\Download::getInstance($_POST['lid'])->Save($_POST);
    switch ($status) {
    case Status::UPL_NODEMO:
        COM_setMsg('Uploads are disabled in demo mode', 'error');
        break;
    case Status::UPL_DUPFILE:
        COM_setMsg(_MD_NEWDLADDED_DUPFILE, 'error');
        break;
    case Status::UPL_DUPSNAP:
        COM_setMsg(_MD_NEWDLADDED_DUPSNAP, 'error');
        break;
    case Status::OK:
        COM_setMsg(_MD_NEWDLADDED);
        break;
    case Status::UPL_ERROR:
    default:
        COM_setMsg(_MD_ERRUPLOAD, 'error');
        break;
    }
    COM_refresh($_CONF['site_admin_url'] . '/plugins/filemgmt/index.php');
    //addDownload();
    break;
case "listBrokenLinks":
    $content .= Filemgmt\BrokenLink::adminList();
    break;
case "delBrokenLink":
    Filemgmt\BrokenLink::delete($opval);
    COM_setMsg(_MD_FILEDELETED);
    COM_refresh($_CONF['site_admin_url'] . "/plugins/filemgmt/index.php?listBrokenDownloads");
    //delBrokenDownloads();
    break;
case "ignoreBrokenLink":
    Filemgmt\BrokenLink::ignore($opval);
    COM_setMsg(_MD_BROKENDELETED);
    COM_refresh($_CONF['site_admin_url'] . "/plugins/filemgmt/index.php?listBrokenDownloads");
    //ignoreBrokenDownloads();
    break;
case 'reportBrokenLink':
    $content .= Filemgmt\BrokenLink::showForm($opval);
    break;
case "approve":
    approve();
    break;
case "delVote":
    RATING_deleteVote($opval);
    $lid = (int)$_GET['lid'];
    COM_setMsg(_MD_VOTEDELETED);
    COM_refresh("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php?modDownload=$lid");
    exit;
    break;
case "delCat":
    delCat();
    break;
case 'modCat':
    $content .= Filemgmt\Category::getInstance($opval)->edit();
    //modCat();
    break;
case 'saveCat':
case "modCatS":
    $status = Filemgmt\Category::getInstance($_POST['cid'])->save($_POST);
    COM_refresh("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php?categoryConfigAdmin");
    modCatS();
    break;
case 'modDownload':
    $content .= Filemgmt\Download::getInstance($opval)->edit();
    //modDownload();
    break;
case "modDownloadS":
    echo "here";die;
    modDownloadS();
    break;
case "delDownload":
    if (Filemgmt\Download::getInstance($opval)->delete()) {
        COM_setMsg(_MD_FILEDELETED);
    } else {
        COM_setMsg(_MD_FILENOTDELETED, 'error');
    }
    COM_refresh("{$_CONF['site_admin_url']}/plugins/filemgmt/index.php");
    break;
case "categoryConfigAdmin":
    $content .= Filemgmt\Category::adminList();
    //categoryConfigAdmin();
    break;
case "newfileConfigAdmin":
    $content .= Filemgmt\Download::getInstance(0)->edit();
    break;
case "listNewDownloads":
    $content .= Filemgmt\Download::adminList(0, 0);
    break;
case 'files':
default:
    $op = 'files';
    //$content .= mydownloads();
    if (isset($_REQUEST['cat'])) {
        $cat = $_REQUEST['cat'];
    } else {
        $cat = 0;
    }
    $content .= Filemgmt\Download::adminList($cat);
    break;
}

echo COM_siteHeader('menu');
echo Filemgmt\Menu::Admin($op);
echo $content;
echo COM_siteFooter();
