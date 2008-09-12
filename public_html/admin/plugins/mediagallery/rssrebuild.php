<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | Media Gallery Maintenance Routines                                        |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once '../../../lib-common.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
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