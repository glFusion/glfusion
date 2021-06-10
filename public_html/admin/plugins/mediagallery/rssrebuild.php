<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* RSS Feeds
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
require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::ERROR,'Someone has tried to access the Media Gallery Configuration page.  User id: '.$_USER['uid'].'/'.$_USER['username'].', IP: ' . $_SERVER['REMOTE_ADDR']);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_rebuildAllAlbumsRSS( $aid ){
    global $MG_albums;

    MG_buildAlbumRSS($aid);

    if ( !empty($MG_albums[$aid]->children)) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            MG_rebuildAllAlbumsRSS($MG_albums[$child]->id);
        }
    }
}

$mode = COM_applyFilter($_REQUEST['mode']);

switch( $mode ) {
    case 'full' :
        MG_buildFullRSS();
        break;
    case 'album' :
        MG_rebuildAllAlbumsRSS(0);
        break;
}

echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=7');
exit;
?>