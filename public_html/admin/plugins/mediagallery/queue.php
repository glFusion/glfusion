<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Moderation Queue
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/moderate.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.admin')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

if (isset ($_POST['mode'])) {
    $mode = $_POST['mode'];

    if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {   // save the album...
        // OK, we have a save, now we need to see what we are saving...
        if ( isset($_POST['action']) ) {
            $action   = COM_applyFilter($_POST['action']);
            if ( $action == 'moderate' ) {
                $display .= MG_saveModeration( $album_id, $_MG_CONF['admin_url'] . 'index.php' );
                echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
                exit;
                echo $display;
                exit;
            }
        }
    } else {
        echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
        exit;
    }
} else {
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file (array ('admin' => 'administration.thtml'));
    $T->set_var(array(
        'site_admin_url'  => $_CONF['site_admin_url'],
        'site_url'        => $_MG_CONF['site_url'],
        'admin_body'      => MG_userModerate( -1, $_MG_CONF['admin_url'] . 'queue.php' ),
        'mg_navigation'   => MG_navigation(),
        'title'           => $LANG_MG01['media_queue'],
        'lang_admin'      => $LANG_MG00['admin'],
        'version'         => $_MG_CONF['pi_version'],
    ));

    $T->parse('output', 'admin');
    $display = COM_siteHeader();
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_siteFooter();
    echo $display;
    exit;
}
?>