<?php
/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE. 
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
*/

require_once '../../../../lib-common.php';

function checkAccess($action){

    global $_CONF, $_CK_CONF, $_USER;

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    $urlfor = 'advancededitor';
    if (COM_isAnonUser() ) {
        $urlfor = 'advancededitor'.md5($_SERVER['REAL_ADDR']);
    }
    $cookiename = $_CONF['cookie_name'].'adveditor';
    if ( isset($_COOKIE[$cookiename]) ) {
        $token = $_COOKIE[$cookiename];
    } else {
        $token = '';
    }
    if (!SEC_checkTokenGeneral($token,$urlfor)  ) {
        exit;
    }

    $urlparts = parse_url($_CONF['site_url']);
    if ( isset($urlparts['path']) ) {
        $relRoot = $urlparts['path'];
        $relRoot = trim($relRoot);
        if ( $relRoot[strlen($relRoot)-1] != '/' ) {
            $relRoot = $relRoot.'/';
        }
    } else {
        $relRoot = '/';
    }

    // removes the leading '/'
    $imagePath = substr($_CONF['path_images'],strlen($_CONF['path_html']));
    $imagePath = rtrim($imagePath, '/\\');

    if (SEC_inGroup('Root') ) {
        $_SESSION['fileman_files_root'] = $relRoot . $imagePath;
        return true;
    }
    $userImagePath = $imagePath . '/library/Image';
    $_SESSION['fileman_files_root'] = $relRoot . $userImagePath;

    if ( $_CK_CONF['filemanager_per_user_dir'] ) {
        $filePath = $relRoot . $imagePath.'/library/userfiles/'.$uid;

        $_SESSION['fileman_files_root'] = $filePath;

        if ( !is_dir($_CONF['path_html'].$imagePath.'/library/userfiles/'.$uid) ) {
            $rc = @mkdir($_CONF['path_html'].$imagePath.'/library/userfiles/'.$uid, 0755, true);
            if ( $rc === false ) {
                $_CK_CONF['filemanager_per_user_dir'] = false;
                $_SESSION['fileman_files_root'] = $relRoot . $userImagePath;
            }
        }
    }

    // final security check on action

    if ( $_CK_CONF['filemanager_per_user_dir'] == false ) {
        switch ($action) {
            case 'DELETEDIR' :
            case 'CREATEDIR' :
            case 'RENAMEDIR' :
            case 'RENAMEFILE' :
            case 'DELETEFILE' :
                exit;
        }
    }

    return true;
}
?>