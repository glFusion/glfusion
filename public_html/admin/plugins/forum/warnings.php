<?php
/**
 * Administer forum thread prefixes.
 */
require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

USES_forum_functions();

if (!SEC_hasRights('forum.edit')) {
    COM_404();
    exit;
}
$action = 'listlevels';

$expected = array(
    'save', 'deletelevel', 'cancel', 'savewarning', 
    // Views
    'editlevel', 'listlevels', 'warnuser',
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

// Prefix ID can come from $_POST or $_GET
//$wl_id = isset($_REQUEST['wl_id']) ? (int)$_REQUEST['pfx_id'] : 0;
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
    $W = new Forum\Modules\Warning\Warning;
    $content .= $W->withUid($actionval)
      ->withTopicId($topic_id)
      ->Edit();
    break;

case 'savewarning':
    $W = new Forum\Modules\Warning\Warning($_POST['w_id']);
    $W->Save($_POST);
    break;

case 'delete':
    Forum\Prefix::Delete($pfx_id);
    echo COM_refresh($self);
    break;

case 'delitem':
    if (is_array($_POST['delitem'])) {
        foreach ($_POST['delitem'] as $pfx_id) {
            Forum\Prefix::Delete($pfx_id);
        }
    }
    echo COM_refresh($self);
    break;

case 'edit':
    $P = new \Forum\Prefix($pfx_id);
    $content = $P->Edit();
    break;

case 'save':
    $P = new Forum\Prefix($_POST['pfx_id']);
    if ($P->Save($_POST)) {
        COM_setMsg($LANG_GF92['setsavemsg']);
    } else {
        COM_setMsg('An error occurred', 'error');
    }
    echo COM_refresh($self);
    exit;
    break;

case 'listtypes':
    break;

case 'editlevel':
    $WL = Forum\Modules\Warning\WarningLevel::getInstance($actionval);
    $content .= $WL->Edit();
    break;

case 'listlevels':
default:
    $content .= '<p>[ <a href="' . $_CONF['site_admin_url'] .
    '/plugins/forum/warnings.php?editlevel">'.$LANG_GF01['create_new'].'</a> ]</p>';
    $content .= Forum\Modules\Warning\WarningLevel::adminList();
    break;
}

$display = FF_siteHeader();
//$display .= FF_navbar($navbarMenu, $LANG_GF01['prefixes']);
$display .= $content;
$display .= FF_siteFooter();
echo $display;

