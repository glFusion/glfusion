<?php
/**
 * Administer forum thread prefixes.
 */
require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

USES_forum_functions();
USES_forum_admin();
use Forum\Modules\Warning\WarningLevel;
use Forum\Modules\Warning\WarningType;
use Forum\Modules\Warning\Warning;
use Forum\Status;

if (!SEC_hasRights('forum.edit')) {
    COM_404();
    exit;
}
$action = 'listlevels';

$expected = array(
    // Actions
    'save', 'deletelevel', 'cancel', 'savewarning', 'savetype', 'savelevel',
    'revokewarning',
    // Views
    'editlevel', 'edittype', 'listlevels', 'listtypes', 'warnuser',
    'viewwarning',
    'log',
);

foreach ($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
    	$action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}

$self = $_CONF['site_admin_url'] . '/plugins/forum/warnings.php';
$content = '';
switch ($action) {
case 'warnuser':
    if (isset($_GET['topic_id'])) {
        $topic_id = (int)$_GET['topic_id'];
    } else {
        COM_setMsg('No topic specified');
        COM_refresh($self);
    }
    $W = new Warning;
    $content .= $W->withUid($actionval)
                  ->withTopicId($topic_id)
                  ->withReturnUrl($_SERVER['HTTP_REFERER'])
                  ->Edit();
    break;

case 'editwarning':
    $W = Warning::getInstance($actionval);
    $content .= $W->Edit();
    break;

case 'savewarning':
    $W = new Warning($_POST['w_id']);
    $W->Save($_POST);
    break;

case 'revokewarning':
    $content = Warning::getInstance((int)$_POST['w_id'])
        ->withRevokedReason($_POST['revoked_reason'])
        ->Revoke();
    if (isset($_POST['redirect_url']) && !empty($_POST['redirect_url'])) {
        COM_refresh($_POST['redirect_url']);
    } else {
        COM_refresh($self . '?log');
    }
    break;

case 'delitem':
    if (is_array($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $pfx_id) {
            Forum\Prefix::Delete($pfx_id);
        }
    }
    echo COM_refresh($self);
    break;

case 'viewwarning':
    $content .= Warning::getInstance($actionval)
        ->withReturnUrl($_SERVER['HTTP_REFERER'])
        ->adminView();
    break;

case 'savelevel':
    $WL = WarningLevel::getInstance($_POST['wl_id']);
    if ($WL->Save($_POST)) {
        COM_setMsg($LANG_GF92['setsavemsg']);
    } else {
        COM_setMsg('An error occurred', 'error');
    }
    echo COM_refresh($self . '?listlevels');
    exit;
    break;

case 'savetype':
    $WT = WarningType::getInstance($_POST['wt_id']);
    if ($WT->Save($_POST)) {
        COM_setMsg($LANG_GF92['setsavemsg']);
    } else {
        COM_setMsg('An error occurred', 'error');
    }
    echo COM_refresh($self . '?listtypes');
    exit;
    break;

case 'listtypes':
    $content .= '<p>[ ' . COM_createLink(
            $LANG_GF01['create_new'],
            $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?edittype',
        ) . ' ]</p>';
    $content .= WarningType::adminList();
    break;

case 'edittype':
    $WT = WarningType::getInstance((int)$actionval);
    $content .= $WT->Edit();
    break;

case 'editlevel':
    $WL = WarningLevel::getInstance((int)$actionval);
    $content .= $WL->Edit();
    break;

case 'log':
    $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
    $T->set_file('log', 'log.thtml');
    if ($actionval > 1) {
        $points = Warning::getUserPoints($actionval);
        $status = Forum\UserInfo::getInstance($actionval)->getForumStatus();
        $T->set_var(array(
            'username' => COM_getDisplayName($actionval),
            'points' => $points,
            'percent' => Warning::getUserPercent($actionval),
            'status' => $status,
            'status_cls' => $status['severity'],
            'status_msg' => $status['message'],
        ) );
    }
    $T->set_var('admin_list', Warning::adminList((int)$actionval, false));
    $T->parse('output', 'log');
    $content .= $T->finish($T->get_var('output'));
    break;

case 'listlevels':
default:
    $content .= '<p>[ <a href="' . $_CONF['site_admin_url'] .
    '/plugins/forum/warnings.php?editlevel">'.$LANG_GF01['create_new'].'</a> ]</p>';
    $content .= WarningLevel::adminList();
    break;
}

$display = FF_siteHeader();
$display .= FF_navbar($navbarMenu, 'Warnings');
$display .= Forum\Menu::adminWarnings($action);
$display .= $content;
$display .= FF_siteFooter();
echo $display;

