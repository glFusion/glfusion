<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Configuration Page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';

use \glFusion\Log\Log;

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
    $display  = COM_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_MG00['access_denied'],true,'error');
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}


function MG_adminBox($mode) {
    global $_MG_CONF, $LANG_MG08;

    $retval = '';
    if ( $mode == 'install' ) {
        $retval .= '<h3>' . $LANG_MG08['success'] . '</h3><br />' . $LANG_MG08['review'] . '<br /><br />';
    }
    $retval .= $LANG_MG08['support'] . '<br /><br />';
    return $retval;
}


// main menu for media gallery administration

$display = '';

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else {
    $mode = '';
}

USES_lib_admin();

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_url'          => $_MG_CONF['site_url'],
    'site_admin_url'    => $_CONF['site_admin_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
    'admin_body'        => plugin_showstats_mediagallery(0),
    'title'             => $LANG_MG00['admin'],
));

$T->parse('output', 'admin');
$display = COM_siteHeader();
$msg = COM_getMessage();
if ( $msg > 0 ) {
    $display .= COM_showMessageText($LANG_MG09[$msg],'mediagallery');
}
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>