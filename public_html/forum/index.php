<?php
/**
* glFusion CMS - Forum Plugin
*
* Index page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2010 by the following authors:
*   Blaine Lang          blaine AT portalparts DOT com
*                        www.portalparts.com
*   Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com
*   Prototype & Concept :        Mr.GxBlock, www.gxblock.com
*
*/

require_once '../lib-common.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

//Check is anonymous users can access
if ($_FF_CONF['registration_required'] && COM_isAnonUser()) {
    $display  = COM_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/forum/include/forum_index.php';

forum_index();
?>
