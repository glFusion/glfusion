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

USES_lib_admin();

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('logo.admin')) {
    Log::logAccessViolation('Logo Administration');
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
    $Logo = new glFusion\Theme($_POST['theme']);
    $retval = array(
        'status' => false,
    );
    $retval = $Logo->handleUpload($_FILES['images'], 0);
    echo json_encode($retval);
    exit;
    break;

case 'ajaxtoggle':
    // Toggle a database field
    $Logo = new glFusion\Theme($_POST['theme']);
    if (!$Logo->Exists()) {
        // If the logo record doesn't exist yet, create it.
        $Logo->createRecord();
    }
    switch ($_POST['type']) {
    case 'display_site_slogan':
    case 'logo_type':
    case 'enabled':
    case 'grp_access':
        $oldval = (int)$_POST['oldval'];
        $newval = (int)$_POST['newval'];
        $result = $Logo->setval($_POST['type'], $oldval, $newval);
        if ($newval == $result) {   // successfully changed
            $msg = _('Field has been updated.');
        } else {
            $msg = _('Item was not changed.');
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
    $Logo = new glFusion\Theme($_POST['theme']);
    if ($Logo->delImage()) {
        $retval = array(
            'status' => true,
            'statusMessage' => 'Image was deleted.',
        );
    } else {
        $retval = array(
            'status' => false,
            'statusMessage' => 'No change made',
        );
    }
    echo json_encode($retval);
    exit;
    break;

case 'savelogos':
    // @deprecated, handled by AJAX now
    $messages = glFusion\Theme::saveLogos();
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
    COM_refresh($_CONF['site_url'] . '/admin/logo.php');
    break;

case 'listlogos':
default:
    $content = glFusion\Theme::adminList();
    break;
}

$display = COM_siteHeader();
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;
