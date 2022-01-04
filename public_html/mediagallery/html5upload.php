<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Media Upload via HTML5
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

global $_CONF, $_USER, $_PLUGINS, $_MG_CONF;

require_once '../lib-common.php';

use \glFusion\Log\Log;

// main

if (!in_array('mediagallery', $_PLUGINS)) {
    Log::write('system',Log::ERROR,'HTML5Upload: MediaGallery not found in $_PLUGINS');
    COM_404();
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

$uid = (isset($_GET['uid'])) ? COM_applyFilter( $_GET['uid'], true ) : '';
$sid = (isset($_GET['sid'])) ? COM_applyFilter( $_GET['sid'], false ) : '';
$aid = (isset($_GET['aid'])) ? COM_applyFilter( $_GET['aid'], true ) : '';

if( $_MG_CONF['verbose'] ) {
    Log::write('system',Log::DEBUG,'***Inside HTML5Upload main()***');
    Log::write('system',Log::DEBUG,'received uid=' . $uid);
    Log::write('system',Log::DEBUG,'received sid=' . $sid);
    Log::write('system',Log::DEBUG,'received aid=' . $aid);
}

// let's try to set the $_USER array
$_USER = SESS_getUserDataFromId( $uid );
if( isset($_USER['error']) && $_USER['error'] == '1' ) {
    Log::write('system',Log::ERROR,'HTML5Upload: User identified by uid=' . $uid . ' not found.');
    echo $LANG_MG01['swfupload_err_session'];
    exit (0);
} elseif(!isset($_USER['uid']) || $_USER['uid'] < 2 ) {
    Log::write('system',Log::ERROR,'HTML5Upload: Anonymous upload rejection.');
    echo 'Anonymous upload rejected';
    exit(0);
}

// ok, we have a valid uid, but now check the token.  if it is invalid, then
// return the user to the swfupload page.
if( !(SEC_checkTokenGeneral( $sid, 'html5upload' )) ) {
    Log::write('system',Log::ERROR,'HTML5Upload: Invalid token=' . $sid . ' for uid=' . $uid);
    echo "Session has expired, please reload the page";
    exit(0);
}

// the upload is authenticated

if ( $_MG_CONF['verbose'] ) {
    Log::write('system',Log::DEBUG,'The upload is authentic');
    Log::write('system',Log::DEBUG,'Retrieved ' . count($_USER) . ' user data values');
    Log::write('system',Log::DEBUG,'***Leaving HTML5Upload main()***');
}

$_GROUPS = SEC_getUserGroups( $_USER['uid'] );
$_RIGHTS = explode( ',', SEC_getUserPermissions() );

MG_initAlbums();

// now that we're sure we have the right user

require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    exit;
}

$rc = MG_saveHTML5Upload( $aid );
echo $rc;
exit(0);
?>