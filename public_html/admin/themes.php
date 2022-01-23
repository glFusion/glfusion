<?php
/**
* glFusion CMS
*
* glFusion Theme Administration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Log\Log;
use \glFusion\Theme\Theme;
use \glFusion\Theme\AdminList;

USES_lib_admin();

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('logo.admin')) {
    Log::logAccessViolation('Theme Administration');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

/*
 * Main processing loop
 */

$msg = COM_getMessage();

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else if ( isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = '';
}

$content = '';
$expected = array('ajaxupload', 'ajaxtoggle', 'savelogos', 'del_logo_img');

$action = 'listlogos';
foreach ($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    }
}

switch ($action) {
case 'ajaxupload':
    // Upload a logo image
    $Theme = Theme::getInstance($_POST['theme']);
    $retval = array(
        'status' => false,
    );
    $retval = $Theme->handleUpload($_FILES['images'], 0);
    echo json_encode($retval);
    exit;
    break;

case 'ajaxtoggle':
    // Toggle a database field
    $Theme = Theme::getInstance($_POST['theme']);
    if (!$Theme->Exists()) {
        // If the logo record doesn't exist yet, create it.
        $Theme->Save();
    }
    switch ($_POST['type']) {
    case 'display_site_slogan':
    case 'logo_type':
    case 'enabled':
    case 'grp_access':
        $oldval = (int)$_POST['oldval'];
        $newval = (int)$_POST['newval'];
        $result = $Theme->setval($_POST['type'], $oldval, $newval);
        if ($newval == $result) {   // successfully changed
            $msg = $LANG_LOGO['item_updated'];
        } else {
            $msg = $LANG_LOGO['item_unchanged'];
        }
        $retval = array(
            'newval' => $newval,
            'statusMessage' => $msg,
        );
        echo json_encode($retval);
        break;
    }
    exit;
    break;

case 'del_logo_img':
    // Delete a logo image via AJAX
    $Theme = Theme::getInstance($_POST['theme']);
    if ($Theme->delImage()) {
        $retval = array(
            'status' => true,
            'statusMessage' => $LANG_LOGO['image_deleted'],
        );
    } else {
        $retval = array(
            'status' => false,
            'statusMessage' => $LANG_LOGO['item_unchanged'],
        );
    }
    echo json_encode($retval);
    exit;
    break;

case 'savelogos':
    // @deprecated, handled by AJAX now
    $messages = Theme::saveLogos();
    $msg = '';
    foreach ($messages as $key=>$info) {
        if (isset($info['message'])) {
            $msg .= '<li>' . $info['theme'] . ': ' . $info['message'] . '</li>';
        }
    }
    if (!empty($msg)) {
        $msg = '<ul>' . $msg . '</ul>';
        COM_setMsg($msg, 'error');
    }
    COM_refresh($_CONF['site_admin_url'] . '/themes.php');
    break;

case 'listlogos':
default:
    $content = AdminList::render();
    break;
}

$display = COM_siteHeader();
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;
