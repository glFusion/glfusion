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
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*  Consultants4Hire Inc.
*  Blaine Lang       blaine@portalparts.com
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\FieldList;
use Filemgmt\Download;
use Filemgmt\Models\Status;

if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    //catch file overload error...
    $postMax = ini_get('post_max_size'); //grab the size limits...
    $uploadMax = ini_get('upload_max_filesize');

    if (intval($postMax) <= intval($uploadMax)) {
        $maxSize = $postMax;
    } else {
        $maxSize = $uploadMax;
    }

    COM_setMsg(sprintf($LANG_FILEMGMT_ERRORS['1111'],$maxSize),'error');
    echo COM_refresh($_CONF['site_admin_url'].'/plugins/filemgmt/index.php');
    exit;
}


// Set view and action variables.
$op = 'files';
$expected = array(
    // Actions to perform
    'saveCat', 'delCat', 'delVote', 'delDownload', 'saveDownload', 'dl_bulk',
    'ignoreBrokenLink', 'delBrokenLink',
    // Views to display
    'modCat', 'categoryConfigAdmin',
    'modDownload', 'moderate','newfileConfigAdmin', 'listNewDownloads', 'files',
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
if ($op == 'moderate') {
    $op = 'modDownload';
    $opval = (int) $_GET['lid'];
}

if (isset($opval)) {
    $opval = COM_applyFilter($opval,TRUE);
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

if ( isset($_POST['cancel']) ) {
    echo COM_refresh($_FM_CONF['admin_url'] . '/index.php');
    exit;
}

$content = '';
switch ($op) {
case "saveDownload":
    $status = Filemgmt\Download::getInstance($_POST['lid'])->save($_POST);
    switch ($status) {
        case Status::UPL_NODEMO:
            COM_setMsg($LANG_FILEMGMT['err_demomode'], 'error');
            break;
        case Status::UPL_DUPFILE:
            COM_setMsg(_MD_NEWDLADDED_DUPFILE, 'error');
            break;
        case Status::UPL_DUPSNAP:
            COM_setMsg(_MD_NEWDLADDED_DUPSNAP, 'error');
            break;
        case Status::UPL_UPDATED:
            COM_setMsg(_MD_DLUPDATED);
            break;
        case Status::OK:
            COM_setMsg(_MD_NEWDLADDED);
            break;
        case Status::UPL_ERROR:
        default:
            COM_setMsg(_MD_ERRUPLOAD, 'error');
            break;
    }
    if (isset($_POST['redirect_url']) && $_POST['redirect_url'] != '') {
        COM_refresh($_POST['redirect_url']);
    } else {
        COM_refresh($_FM_CONF['admin_url'] . '/index.php');
    }
    break;
case "listBrokenLinks":
    $content .= Filemgmt\BrokenLink::adminList();
    break;
case "delBrokenLink":
    Filemgmt\BrokenLink::delete($opval);
    COM_setMsg(_MD_FILEDELETED);
    COM_refresh($_FM_CONF['admin_url'] . "/index.php?listBrokenLinks");
    break;
case "ignoreBrokenLink":
    Filemgmt\BrokenLink::ignore($opval);
    COM_setMsg(_MD_BROKENDELETED);
    COM_refresh($_FM_CONF['admin_url'] . "/index.php?listBrokenLinks");
    //ignoreBrokenDownloads();
    break;
case 'reportBrokenLink':
    $content .= Filemgmt\BrokenLink::showForm($opval);
    break;

case "delVote":
    RATING_deleteVote($opval);
    $lid = (int)$_GET['lid'];
    COM_setMsg(_MD_VOTEDELETED);
    COM_refresh("{$_FM_CONF['admin_url']}/index.php?modDownload=$lid");
    exit;
    break;
case "delCat":
    // when deleting from the edit form
    if (isset($_POST['cid'])) {
        $opval = (int) COM_applyFilter($_POST['cid'],true);
    }
    if(Filemgmt\Category::getInstance($opval)->delete()) {
        COM_setMsg(_MD_CATDELETED);
    } else {
        COM_setMsg('Error :: Unable to delete category');
    }
    COM_refresh("{$_FM_CONF['admin_url']}/index.php?categoryConfigAdmin");
    break;
case 'modCat':
    $content .= Filemgmt\Category::getInstance((int)$opval)->edit();
    break;
case 'saveCat':
case "modCatS":
    $status = Filemgmt\Category::getInstance($_POST['cid'])->save($_POST);
    COM_refresh("{$_FM_CONF['admin_url']}/index.php?categoryConfigAdmin");
    break;

case 'modDownload':
    $content .= Filemgmt\Download::getInstance($opval)->edit();
    break;

case "delDownload":
    if (Filemgmt\Download::getInstance($opval)->delete()) {
        COM_setMsg(_MD_FILEDELETED);
    } else {
        COM_setMsg(_MD_FILENOTDELETED, 'error');
    }
    COM_refresh("{$_FM_CONF['admin_url']}/index.php");
    break;
case 'dl_bulk':
    if (is_array($opval)) {
        foreach ($opval as $lid) {
            Filemgmt\Download::getInstance($lid)->delete();
        }
    }
    COM_refresh("{$_FM_CONF['admin_url']}/index.php");
    break;
case "categoryConfigAdmin":
    $content .= Filemgmt\Category::adminList();
    break;
case "newfileConfigAdmin":
    $content .= Filemgmt\Download::getInstance(0)->edit();
    break;
case "listNewDownloads":
    $content .= Filemgmt\Download::adminList(0, 1);
    break;
case 'files':
default:
    $op = 'files';

    if (isset($_POST['cat'])) {
        $cat = COM_applyFilter($_POST['cat']);
    } elseif (isset($_GET['cat'])) {
        $cat = COM_applyFilter($_GET['cat']);
    } elseif (SESS_isSet('filemgmt.cat')) {
        $cat = (int) SESS_getVar('filemgmt.cat');
    } else {
        $cat = 0;
    }
    $content .= Filemgmt\Download::adminList($cat,1);
    break;
}

echo COM_siteHeader('menu');
echo Filemgmt\Menu::Admin($op);
echo $content;
echo COM_siteFooter();
