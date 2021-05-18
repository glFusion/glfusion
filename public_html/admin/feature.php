<?php
/**
 * glFusion Feature administration page.
 *
 * @license GNU General Public License version 2 or later
 *     http://www.opensource.org/licenses/gpl-license.php
 *
 *  Copyright (C) 2009-2019 by the following authors:
 *   Mark R. Evans   mark AT glfusion DOT org
 *   Mark Howard     mark AT usable-web DOT com
 *
 *  Based on prior work Copyright (C) 2000-2008 by the following authors:
 *  Tony Bibbs         - tony AT tonybibbs DOT com
 *  Mark Limburg       - mlimburg AT users DOT sourceforge DOT net
 *  Jason Whittenburg  - jwhitten AT securitygeeks DOT com
 *  Dirk Haun          - dirk AT haun-online DOT de
 *
 */

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\Admin\AdminAction;

$display = '';

// Make sure user has rights to access this page.
// Leverage the group.edit permission for now.
if (!SEC_hasRights('group.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    Log::logAccessViolation('Feature Administration');
    echo $display;
    exit;
}

// MAIN ========================================================================

$action = '';
$actionval = 0;
$expected = array('edit', 'save'); 
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
        $actionval = $_GET[$provided];
    }
}

if (isset($_POST['cancel'])) {
    echo COM_refresh($_CONF['site_admin_url'].'/feature.php');
}

switch ($action) {
case 'edit':
    $FT = glFusion\Feature::getById($actionval);
    $display .= COM_siteHeader('menu', $LANG_ACCESS['groupeditor']);
    $display .= $FT->Edit();
    $display .= COM_siteFooter();
    break;

case 'save':
    $status = false;
    if (SEC_checkToken()) {
        $FT = glFusion\Feature::getById($_POST['ft_id']);
        $status = $FT->Save($_POST);
    }
    if ($status) {
        COM_refresh($_CONF['site_admin_url'] . '/feature.php?msg=119');
    } else {
        COM_refresh($_CONF['site_admin_url'] . '/feature.php?msg=120');
    }
    break;

case 'list':
default:
    $display .= COM_siteHeader('menu', 'Feature Administrator');
    $display .= COM_showMessageFromParameter();
    $display .= glFusion\Feature::adminList();
    $display .= COM_siteFooter();
    break;
}
echo $display;
