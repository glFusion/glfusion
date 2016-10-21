<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | envcheck.php                                                             |
// |                                                                          |
// | glFusion Environment Check                                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric Warren            eric AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

require_once '../lib-common.php';
require_once 'auth.inc.php';
USES_lib_admin();

$display = '';

if (!SEC_inGroup ('Root')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[200],$MESSAGE[30],true,'error')
        . COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the hosting environment check screen");
    echo $display;
    exit;
}


function _checkEnvironment()
{
    global $_CONF, $_TABLES, $_PLUGINS, $_SYSTEM, $LANG_ADMIN, $LANG01,
           $filemgmt_FileStore, $filemgmt_SnapStore, $filemgmt_SnapCat,
           $_FF_CONF, $_MG_CONF, $LANG_FILECHECK;

    $retval = '';
    $permError = 0;

    $T = new Template($_CONF['path_layout'] . 'admin');
    $T->set_file('page','envcheck.thtml');

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/envcheck.php',
              'text' => $LANG01['recheck']),
//        array('url'  => $_CONF['site_admin_url'] .'/filecheck.php',
//              'text' => $LANG_FILECHECK['filecheck']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG01['hosting_env'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG01['php_warning'],
        $_CONF['layout_url'] . '/images/icons/envcheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    /*
     * First we will validate the general environment..
     */

    $T->set_block('page','envs','env');

    // PHP Version

    $T->set_var('item',$LANG01['php_version']);

    $classCounter = 0;

    if ( _phpOutOfDate() ) {
        $T->set_var('status','<span class="notok">'.phpversion().'</span>');
    } else {
        $T->set_var('status','<span class="yes">'.phpversion().'</span>');
    }
    $T->set_var('recommended','5.6.0+');

    $phpnotes = $LANG01['php_req_version'];
    if ( !_phpUpToDate() ) {
        $phpnotes .= '<br><span class="notok">'.$LANG01['phpendoflife'].'</span>';
    }
    $T->set_var('notes',$phpnotes);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $rg = ini_get('register_globals');
    $sm = ini_get('safe_mode');
    $ob = ini_get('open_basedir');

    $rg = ini_get('register_globals');
    $T->set_var('item','register_globals');
    $T->set_var('status',$rg == 1 ? '<span class="notok">'.$LANG01['on'].'</span>' : '<span class="yes">'.$LANG01['off'].'</span>');
    $T->set_var('recommended',$LANG01['off']);
    $T->set_var('notes',$LANG01['register_globals']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $sm = ini_get('safe_mode');
    $T->set_var('item','safe_mode');
    $T->set_var('status',$sm == 1 ? '<span class="notok">'.$LANG01['on'].'</span>' : '<span class="yes">'.$LANG01['off'].'</span>');
    $T->set_var('recommended',$LANG01['off']);
    $T->set_var('notes',$LANG01['safe_mode']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $ob = ini_get('open_basedir');
    if ( $ob == '' ) {
        $open_basedir_restriction = 0;
    } else {
        $open_basedir_restriction = 1;
        $open_basedir_directories = $ob;
    }
    $T->set_var('item','open_basedir');
    $T->set_var('status',$ob == '' ? '<span class="yes">'.$LANG01['off'].'</span>' : '<span class="notok">'.$LANG01['enabled'].'</span>');
    $T->set_var('notes',$LANG01['open_basedir']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $memory_limit = _return_bytes(ini_get('memory_limit'));
    $memory_limit_print = _bytes_to_mg($memory_limit); //  / 1024) / 1024;
    $T->set_var('item','memory_limit');
    // check for at least 48M
    $T->set_var('status',$memory_limit < 50331648 ? '<span class="notok">'.$memory_limit_print.'</span>' : '<span class="yes">'.$memory_limit_print.'</span>');
    $T->set_var('recommended','64M');
    $T->set_var('notes',$LANG01['memory_limit']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $fu = ini_get('file_uploads');
    $T->set_var('item','file_uploads');
    $T->set_var('status',$fu == 1 ? '<span class="yes">'.$LANG01['on'].'</span>' : '<span class="notok">'.$LANG01['off'].'</span>');
    $T->set_var('recommended',$LANG01['on']);
    $T->set_var('notes',$LANG01['file_uploads']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $upload_limit = _return_bytes(ini_get('upload_max_filesize'));
    $upload_limit_print = _bytes_to_mg($upload_limit);
    $T->set_var('item','upload_max_filesize');
    // check for at least 8M
    $T->set_var('status',$upload_limit < 8388608 ? '<span class="notok">'.$upload_limit_print.'</span>' : '<span class="yes">'.$upload_limit_print.'</span>');
    $T->set_var('recommended','8M');
    $T->set_var('notes',$LANG01['upload_max_filesize']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $post_limit = _return_bytes(ini_get('post_max_size'));
    if ( $post_limit == 0 ) {
        $post_limit_print = 'unlimited';
        $T->set_var('status','<span class="yes">'.$post_limit_print.'</span>');
    } else {
        $post_limit_print = _bytes_to_mg($post_limit);
        $T->set_var('status',$post_limit < 8388608 ? '<span class="notok">'.$post_limit_print.'</span>' : '<span class="yes">'.$post_limit_print.'</span>');
    }
    $T->set_var('item','post_max_size');
    $T->set_var('recommended','8M');
    $T->set_var('notes',$LANG01['post_max_size']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $max_execution_time = ini_get('max_execution_time');
    $T->set_var('item', 'max_execution_time');
    $T->set_var('status', $max_execution_time < 30 ? '<span class="notok">' . $max_execution_time . ' secs</span>' : '<span class="yes">' . $max_execution_time . ' secs</span>');
    $T->set_var('recommended', '30 secs');
    $T->set_var('notes',$LANG01['max_execution_time']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $mysql_version = DB_getVersion();
    $T->set_var('mysql', 'MySQL Version');
    $T->set_var('mysql_version',$mysql_version);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $classCounter++;

    $T->set_block('page','libs','lib');

    if (extension_loaded('mbstring')) {
        $T->set_var(array(
            'item' => $LANG01['mbstring_library'],
            'status' => '<span class="yes">' . $LANG01['ok'] . '</span>',
            'notes' => $LANG01['mbstring_ok']
        ));
    } else {
        $T->set_var(array(
            'item' => $LANG01['mbstring_library'],
            'status' => '<span class="notok">' .  $LANG01['not_found'] . '</span>',
            'notes' => $LANG01['mbstring_not_found']
        ));
    }

    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('lib','libs',true);
    $classCounter++;

    if (extension_loaded('openssl')) {
        $T->set_var(array(
            'item' => $LANG01['openssl_library'],
            'status' => '<span class="yes">' . $LANG01['ok'] . '</span>',
            'notes' => $LANG01['openssl_ok']
        ));
    } else {
        $T->set_var(array(
            'item' => $LANG01['openssl_library'],
            'status' => '<span class="notok">' .  $LANG01['not_found'] . '</span>',
            'notes' => $LANG01['openssl_not_found']
        ));
    }
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('lib','libs',true);
    $classCounter++;

    if ( $sm != 1 && $open_basedir_restriction != 1 ) {
        switch ( $_CONF['image_lib'] ) {
            case 'imagemagick' :    // ImageMagick
                if (PHP_OS == "WINNT") {
                    $binary = "/convert.exe";
                } else {
                    $binary = "/convert";
                }
                clearstatcache();
                if (! @file_exists( $_CONF['path_to_mogrify'] . $binary ) ) {
                    $T->set_var(array(
                        'item'   =>  $LANG01['imagemagick'],
                        'status' =>  '<span class="notok">' .  $LANG01['not_found'] . '</span>',
                        'notes'  => $LANG01['im_not_found'],
                    ));
                } else {
                    $T->set_var(array(
                        'item'   => $LANG01['imagemagick'],
                        'status' => '<span class="yes">' . $LANG01['ok'] . '</span>',
                        'notes'  => $LANG01['im_ok'],
                    ));
                }
                break;
            case 'gdlib' :        // GD Libs
                if ($gdv = gdVersion()) {
                    if ($gdv >=2) {
                        $T->set_var(array(
                            'item'   => $LANG01['gd_lib'],
                            'status' => '<span class="yes">'.$LANG01['ok'].'</span>',
                            'notes'  => $LANG01['gd_ok'],
                        ));

                    } else {
                        $T->set_var(array(
                            'item'   => $LANG01['gd_lib'],
                            'status' => '<span class="yes">'.$LANG01['ok'].'</span>',
                            'notes'  => $LANG01['gd_v1'],
                        ));
                    }
                } else {
                    $T->set_var(array(
                        'item'   =>  $LANG01['gd_lib'],
                        'status' =>  '<span class="notok">' . $LANG01['not_found'] . '</span>',
                        'notes' =>   $LANG01['gd_not_found'],
                    ));
                }
                break;
            case 'netpbm' :    // NetPBM
                if (PHP_OS == "WINNT") {
                    $binary = "/jpegtopnm.exe";
                } else {
                    $binary = "/jpegtopnm";
                }
                clearstatcache();
                if (! @file_exists( $_CONF['path_to_netpbm'] . $binary ) ) {
                    $T->set_var(array(
                        'item'   => $LANG01['netpbm'],
                        'status' => '<span class="notok">' . $LANG01['not_found'] . '</span>',
                        'notes'  => $LANG01['np_not_found'],
                    ));
                } else {
                    $T->set_var(array(
                        'item'   =>  $LANG01['netpbm'],
                        'status' =>  '<span class="yes">' . $LANG01['ok'] . '</span>',
                        'notes'  => $LANG01['np_ok'],
                    ));
                }
                break;
        }
        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('lib','libs',true);
        $classCounter++;
        if ( $_CONF['jhead_enabled'] ) {
            if (PHP_OS == "WINNT") {
                $binary = "/jhead.exe";
            } else {
                $binary = "/jhead";
            }
            clearstatcache();
            if (! @file_exists( $_CONF['path_to_jhead'] . $binary ) ) {
                $T->set_var(array(
                    'item'      => $LANG01['jhead'],
                    'status'    => '<span class="notok">' .  $LANG01['not_found'] . '</span>',
                    'notes'     => $LANG01['jhead_not_found'],
                ));
            } else {
                $T->set_var(array(
                    'item'      => $LANG01['jhead'],
                    'status'    => '<span class="yes">' . $LANG01['ok'] . '</span>',
                    'notes'     => $LANG01['jhead_ok'],
                ));
            }
            $T->set_var('rowclass',($classCounter % 2)+1);
            $T->parse('lib','libs',true);
            $classCounter++;
        }

        if ( $_CONF['jpegtrans_enabled'] ) {
            if (PHP_OS == "WINNT") {
                $binary = "/jpegtran.exe";
            } else {
                $binary = "/jpegtran";
            }
            clearstatcache();
            if (! @file_exists( $_CONF['path_to_jpegtrans'] . $binary ) ) {
                $T->set_var(array(
                    'item'   => $LANG01['jpegtran'],
                    'status' => '<span class="notok">' .  $LANG01['not_found'] . '</span>',
                    'notes'  => $LANG01['jpegtran_not_found'],
                ));
            } else {
                $T->set_var(array(
                    'item'   => $LANG01['jpegtran'],
                    'status' => '<span class="yes">' . $LANG01['ok'] . '</span>',
                    'notes'  => $LANG01['jpegtran_ok'],
                ));
            }
            $T->set_var('rowclass',($classCounter % 2)+1);
            $T->parse('lib','libs',true);
            $classCounter++;
        }

    } else {
        $T->set_var(array(
            'item'   => $LANG01['graphics'],
            'status' => $LANG01['not_checked'],
            'notes'  => $LANG01['bypass_note'],
        ));
    }

    // extract syndication storage path
    $feedpath = $_CONF['rdf_file'];
    $pos = strrpos( $feedpath, '/' );
    $feedPath = substr( $feedpath, 0, $pos + 1 );

    $file_list = array( $_CONF['path_data'],
                        $_CONF['path_data'].'glfusion.lck',
                        $_CONF['path_data'].'glfusion_css.lck',
                        $_CONF['path_data'].'glfusion_js.lck',
                        $_CONF['path_log'].'error.log',
                        $_CONF['path_log'].'access.log',
                        $_CONF['path_log'].'captcha.log',
                        $_CONF['path_log'].'spamx.log',
                        $_CONF['path_data'].'layout_cache/',
                        $_CONF['path_data'].'temp/',
                        $_CONF['path_html'],
                        $feedPath,
                        $_CONF['rdf_file'],
                        $_CONF['path_html'].'images/articles/',
                        $_CONF['path_html'].'images/topics/',
                        $_CONF['path_html'].'images/userphotos/',
                        $_CONF['path_html'].'images/library/File/',
                        $_CONF['path_html'].'images/library/Flash/',
                        $_CONF['path_html'].'images/library/Image/',
                        $_CONF['path_html'].'images/library/Media/',
                        $_CONF['path_html'].'images/library/userfiles/',
                    );

    $mg_file_list = array($_CONF['path'].'plugins/mediagallery/tmp/',
                        $_MG_CONF['path_mediaobjects'],
                        $_MG_CONF['path_mediaobjects'].'covers/',
                        $_MG_CONF['path_mediaobjects'].'orig/',
                        $_MG_CONF['path_mediaobjects'].'disp/',
                        $_MG_CONF['path_mediaobjects'].'tn/',
                        $_MG_CONF['path_mediaobjects'].'orig/0/',
                        $_MG_CONF['path_mediaobjects'].'disp/0/',
                        $_MG_CONF['path_mediaobjects'].'tn/0/',
                        $_MG_CONF['path_mediaobjects'].'orig/1/',
                        $_MG_CONF['path_mediaobjects'].'disp/1/',
                        $_MG_CONF['path_mediaobjects'].'tn/1/',
                        $_MG_CONF['path_mediaobjects'].'orig/2/',
                        $_MG_CONF['path_mediaobjects'].'disp/2/',
                        $_MG_CONF['path_mediaobjects'].'tn/2/',
                        $_MG_CONF['path_mediaobjects'].'orig/3/',
                        $_MG_CONF['path_mediaobjects'].'disp/3/',
                        $_MG_CONF['path_mediaobjects'].'tn/3/',
                        $_MG_CONF['path_mediaobjects'].'orig/4/',
                        $_MG_CONF['path_mediaobjects'].'disp/4/',
                        $_MG_CONF['path_mediaobjects'].'tn/4/',
                        $_MG_CONF['path_mediaobjects'].'orig/5/',
                        $_MG_CONF['path_mediaobjects'].'disp/5/',
                        $_MG_CONF['path_mediaobjects'].'tn/5/',
                        $_MG_CONF['path_mediaobjects'].'orig/6/',
                        $_MG_CONF['path_mediaobjects'].'disp/6/',
                        $_MG_CONF['path_mediaobjects'].'tn/6/',
                        $_MG_CONF['path_mediaobjects'].'orig/7/',
                        $_MG_CONF['path_mediaobjects'].'disp/7/',
                        $_MG_CONF['path_mediaobjects'].'tn/7/',
                        $_MG_CONF['path_mediaobjects'].'orig/8/',
                        $_MG_CONF['path_mediaobjects'].'disp/8/',
                        $_MG_CONF['path_mediaobjects'].'tn/8/',
                        $_MG_CONF['path_mediaobjects'].'orig/9/',
                        $_MG_CONF['path_mediaobjects'].'disp/9/',
                        $_MG_CONF['path_mediaobjects'].'tn/9/',
                        $_MG_CONF['path_mediaobjects'].'orig/a/',
                        $_MG_CONF['path_mediaobjects'].'disp/a/',
                        $_MG_CONF['path_mediaobjects'].'tn/a/',
                        $_MG_CONF['path_mediaobjects'].'orig/b/',
                        $_MG_CONF['path_mediaobjects'].'disp/b/',
                        $_MG_CONF['path_mediaobjects'].'tn/b/',
                        $_MG_CONF['path_mediaobjects'].'orig/c/',
                        $_MG_CONF['path_mediaobjects'].'disp/c/',
                        $_MG_CONF['path_mediaobjects'].'tn/c/',
                        $_MG_CONF['path_mediaobjects'].'orig/d/',
                        $_MG_CONF['path_mediaobjects'].'disp/d/',
                        $_MG_CONF['path_mediaobjects'].'tn/d/',
                        $_MG_CONF['path_mediaobjects'].'orig/e/',
                        $_MG_CONF['path_mediaobjects'].'disp/e/',
                        $_MG_CONF['path_mediaobjects'].'tn/e/',
                        $_MG_CONF['path_mediaobjects'].'orig/f/',
                        $_MG_CONF['path_mediaobjects'].'disp/f/',
                        $_MG_CONF['path_mediaobjects'].'tn/f/',
                        $_MG_CONF['path_html'].'watermarks/',
                    );

    $fm_file_list = array(
                        $filemgmt_FileStore,
                        $filemgmt_FileStore.'tmp/',
                        $filemgmt_SnapStore,
                        $filemgmt_SnapStore.'tmp/',
                        $filemgmt_SnapCat,
                        $filemgmt_SnapCat.'tmp/',
                    );

    $forum_file_list = array(
                        $_FF_CONF['uploadpath'].'/',
                        $_FF_CONF['uploadpath'].'/tn/',
                      );


    if (in_array('mediagallery', $_PLUGINS)) {
        $file_list = array_merge($file_list, $mg_file_list);
    }
    if (in_array('filemgmt', $_PLUGINS)) {
        $file_list = array_merge($file_list, $fm_file_list);
    }
    if (in_array('forum', $_PLUGINS)) {
        $file_list = array_merge($file_list, $forum_file_list);
    }

    $T->set_block('page','perms','perm');

    $classCounter = 0;
    foreach ($file_list AS $path) {
        $ok = _isWritable($path);
        if ( !$ok ) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? '<span class="yes">'.$LANG01['ok'].'</span>' : '<span class="notwriteable">'.$LANG01['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
            if  ( !$ok ) {
                $permError = 1;
            }
        }
/* --- debug code ---
        else {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? '<span class="yes">'.$LANG01['ok'].'</span>' : '<span class="notwriteable">'.$LANG01['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
        }
----------------------- */
    }
    // special test to see if we can create a directory under layout_cache...
    $rc = @mkdir($_CONF['path_data'].'layout_cache/test/');
    if (!$rc) {
        $T->set_var('location',$_CONF['path_data'].'layout_cache/<br /><strong>'.$_GLFUSION['errstr'].'</strong>');
        $T->set_var('status', '<span class="notwriteable">'.$LANG01['unable_mkdir'].'</span>');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);
        $permError = 1;
        @rmdir($_CONF['path_data'].'layout_cache/test/');
    } else {
        $ok = _isWritable($_CONF['path_data'].'layout_cache/test/');
        if ( !$ok ) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? '<span class="yes">'.$LANG01['ok'].'</span>' : '<span class="notwriteable">'.$LANG01['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
            if  ( !$ok ) {
                $permError = 1;
            }
        }
        @rmdir($_CONF['path_data'].'layout_cache/test/');
    }

    // special test to see if existing cache files exist and are writable...
    $rc = _checkCacheDir($_CONF['path_data'].'layout_cache/',$T,$classCounter);
    if ( $rc > 0 ) {
        $permError = 1;
    }

    if ( $permError ) {
        $button = $LANG01['recheck'];
        $action = 'checkenvironment';
        $T->set_var('error_message',$LANG01['correct_perms']);

        $recheck  = '<button type="submit" name="submit" onclick="submitForm( checkenv, \'checkenvironment\' );">' . LB;
        $recheck .= $LANG01['recheck'] . LB;
        $recheck .= '<img src="layout/arrow-recheck.gif" alt=""/>' . LB;
        $recheck .= '</button>' . LB;

    } else {
        $classCounter = 0;
        $recheck = '';
        $T->set_var('location',$LANG01['directory_permissions']);
        $T->set_var('status', 1 ? '<span class="yes">'.$LANG01['ok'].'</span>' : '<span class="notwriteable">'.$LANG01['not_writable'].'</span>');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);

        $T->set_var('location',$LANG01['file_permissions']);
        $T->set_var('status', 1 ? '<span class="yes">'.$LANG01['ok'].'</span>' : '<span class="notwriteable">'.$LANG01['not_writable'].'</span>');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);
    }

    $T->set_var(array(
        'lang_host_env'     => $LANG01['hosting_env'],
        'lang_setting'      => $LANG01['setting'],
        'lang_current'      => $LANG01['current'],
        'lang_recommended'  => $LANG01['recommended'],
        'lang_notes'        => $LANG01['notes'],
        'lang_filesystem'   => $LANG01['filesystem_check'],
        'lang_php_settings' => $LANG01['php_settings'],
        'lang_php_warning'  => $LANG01['php_warning'],
        'lang_current_php_settings' => $LANG01['current_php_settings'],
        'lang_show_phpinfo' => $LANG01['show_phpinfo'],
        'lang_hide_phpinfo' => $LANG01['hide_phpinfo'],
        'lang_graphics'     => $LANG01['graphics'],
        'lang_extensions'   => $LANG01['extensions'],
        'lang_recheck'      => $LANG01['recheck'],
        'phpinfo'           => _phpinfo(),
    ));

    if ( !defined('DEMO_MODE') ) {
        $T->set_var(array(
            'phpinfo'       => _phpinfo(),
        ));
    } else {
        $T->set_var('phpinfo','');
    }

    $T->parse('output','page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

/**
 * Returns the PHP version
 *
 * Note: Removes appendices like 'rc1', etc.
 *
 * @return array the 3 separate parts of the PHP version number
 *
 */
function php_v()
{
    $phpv = explode('.', phpversion());
    return array($phpv[0], $phpv[1], (int) $phpv[2]);
}

/**
 * Check if the user's PHP version is supported by glFusion
 *
 * @return bool True if supported, falsed if not supported
 *
 */
function _phpOutOfDate()
{
    $phpv = php_v();
    if (($phpv[0] < 5) || (($phpv[0] == 5) && ($phpv[1] < 2))) {
        return true;
    } else {
        return false;
    }
}

function _isWritable($path) {
    if ($path{strlen($path)-1}=='/')
        return _isWritable($path.uniqid(mt_rand()).'.tmp');

    if (@file_exists($path)) {
        if (!($f = @fopen($path, 'r+')))
            return false;
        fclose($f);
        return true;
    }

    if (!($f = @fopen($path, 'w')))
        return false;
    @fclose($f);
    @unlink($path);
    return true;
}

function _return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g':
            $val = (int) $val * pow(1024,2);
        case 'm':
            $val = (int) $val * pow(1024,1);
        case 'k':
            $val = (int) $val * 1024;
    }
    return $val;
}

function _bytes_to_mg($bytes, $precision = 2)
{
    return round ($bytes / pow(1024,2),$precision) . 'M';
}



function _checkCacheDir($path,$template,$classCounter)
{
    $permError = 0;

    // special test to see if existing cache files exist and are writable...
    if ( $dh = @opendir($path) ) {
        while (($file = readdir($dh)) !== false ) {
            if ( $file == '.' || $file == '..' || $file == '.svn' || $file == '.git' ) {
                continue;
            }
            if ( is_dir($path.$file) ) {
                $rc = _checkCacheDir($path.$file.'/',$template,$classCounter);
                if ( $rc > 0 ) {
                    $permError = 1;
                }
            } else {
                $ok = _isWritable($path.$file);
                if ( !$ok ) {
                    $template->set_var('location',$path.$file);
                    $template->set_var('status', $ok ? '<span class="yes">OK</span>' : '<span class="notwriteable">NOT WRITABLE</span>');
                    $template->set_var('rowclass',($classCounter % 2)+1);
                    $classCounter++;
                    $template->parse('perm','perms',true);
                    if  ( !$ok ) {
                        $permError = 1;
                    }
                }
            }
        }
        closedir($dh);
    }
    return $permError;
}

function gdVersion($user_ver = 0) {
    if (! extension_loaded('gd')) {
        return;
    }

    static $gd_ver = 0;

    // Just accept the specified setting if it's 1.
    if ($user_ver == 1) {
        $gd_ver = 1;
        return 1;
    }

    // Use the static variable if function was called previously.
    if ($user_ver !=2 && $gd_ver > 0 ) {
        return $gd_ver;
    }

    // Use the gd_info() function if possible.
    if (function_exists('gd_info')) {
        $ver_info = gd_info();
        preg_match('/\d/', $ver_info['GD Version'], $match);
        $gd_ver = $match[0];
        return $match[0];
    }

   // If phpinfo() is disabled use a specified / fail-safe choice...
   if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
        if ($user_ver == 2) {
            $gd_ver = 2;
            return 2;
        } else {
            $gd_ver = 1;
            return 1;
        }
    }
    // ...otherwise use phpinfo().
    ob_start();
    phpinfo(8);
    $info = ob_get_contents();
    ob_end_clean();
    $info = stristr($info, 'gd version');
    preg_match('/\d/', $info, $match);
    $gd_ver = $match[0];
    return $match[0];
}


function _phpinfo()
{
    ob_start();
    phpinfo();

    preg_match ('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);

    # $matches [1]; # Style information
    # $matches [2]; # Body information

    $retval = "<div class='phpinfodisplay' style=\"font-size:1.2em;width:100%\"><style type='text/css'>\n" .
        join( "\n",
            array_map(
                create_function(
                    '$i',
                    'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'
                    ),
                preg_split( '/\n/', trim(preg_replace( "/\nbody/", "\n", $matches[1])) )
                )
            ) .
        "</style>\n" .
        $matches[2] .
        "\n</div>\n";

    return $retval;

}

$display  = COM_siteHeader();
$display .= _checkEnvironment();
$display .= COM_siteFooter();
echo $display;
?>
