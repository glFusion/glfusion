<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | Administer the media moderation queue.                                    |
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

require_once('../../../lib-common.php');
require_once($_MG_CONF['path_html'] . 'maint/moderate.php');
require_once($_MG_CONF['path_admin'] . 'navigation.php');


$display = '';

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
    $display = COM_siteHeader();
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file (array ('admin' => 'administration.thtml'));
    $T->set_var(array(
        'site_admin_url'  => $_CONF['site_admin_url'],
        'site_url'        => $_MG_CONF['site_url'],
        'admin_body'      => MG_userModerate( -1, $_MG_CONF['admin_url'] . 'queue.php' ),
        'mg_navigation'   => MG_navigation(),
        'title'           => $LANG_MG01['media_queue'],
        'lang_admin'      => $LANG_MG00['admin'],
        'version'         => $_MG_CONF['version'],
    ));

    $T->parse('output', 'admin');
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_siteFooter();
    echo $display;
    exit;
}
?>