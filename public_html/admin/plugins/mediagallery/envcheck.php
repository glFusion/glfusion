<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | envcheck.php                                                             |
// |                                                                          |
// | Post configuration checks to validate environemnt                        |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

// this file can't be used on its own
if (stripos ($_SERVER['PHP_SELF'], 'envcheck.php') !== false)
{
    die ('This file can not be used on its own.');
}

function gdVersion($user_ver = 0) {
   if (! extension_loaded('gd')) { return; }
   static $gd_ver = 0;
   // Just accept the specified setting if it's 1.
   if ($user_ver == 1) { $gd_ver = 1; return 1; }
   // Use the static variable if function was called previously.
   if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
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
} // End gdVersion()

function MG_checkEnvironment( ) {
    global $_CONF,  $_MG_CONF, $LANG_MG01, $_TABLES;

    $retval = '';

    $retval .= COM_startBlock ('', '', COM_getBlockTemplate ('_admin_block', 'header'));
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file (array ('admin' => 'envcheck.thtml'));
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_block('admin', 'CheckRow','CRow');

    if ( ini_get('safe_mode') != 1 ) {

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
                        'config_item'   =>  'ImageMagick Programs',
                        'status'        =>  '<font color="red">' .  $LANG_MG01['not_found'] . '</font>'
                    ));
                } else {
                    $T->set_var(array(
                        'config_item'   =>  'ImageMagick Programs',
                        'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
                    ));
                }
                $T->parse('CRow','CheckRow',true);
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
                        'config_item'   =>  'NetPBM Programs',
                        'status'        =>  '<font color="red">' . $LANG_MG01['not_found'] . '</font>'
                    ));
                } else {
                    $T->set_var(array(
                        'config_item'   =>  'NetPBM Programs',
                        'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
                    ));
                }
                $T->parse('CRow','CheckRow',true);
                break;
            case 'gdlib' :        // GD Libs
                if ($gdv = gdVersion()) {
                    if ($gdv >=2) {
                        $T->set_var(array(
                            'config_item'   =>  'GD Libraries',
                            'status'        =>  '<font color="green">v2 Installed</font>'
                        ));

                    } else {
                        $T->set_var(array(
                            'config_item'   =>  'GD Libraries',
                            'status'        =>  '<font color="yellow">v1 Installed</font>'
                        ));
                    }
                } else {
                        $T->set_var(array(
                            'config_item'   =>  'GD Libraries',
                            'status'        =>  '<font color="red">' . $LANG_MG01['not_found'] . '</font>'
                        ));
                }
                $T->parse('CRow','CheckRow',true);
                break;
        }

        if ( $_CONF['jhead_enabled'] ) {
            if (PHP_OS == "WINNT") {
                $binary = "/jhead.exe";
            } else {
                $binary = "/jhead";
            }
            clearstatcache();
            if (! @file_exists( $_CONF['path_to_jhead'] . $binary ) ) {
                $T->set_var(array(
                    'config_item'   =>  'jhead Program',
                    'status'        =>  '<font color="red">' .  $LANG_MG01['not_found'] . '</font>'
                ));
            } else {
                $T->set_var(array(
                    'config_item'   =>  'jhead Program',
                    'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
                ));
            }
            $T->parse('CRow','CheckRow',true);
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
                    'config_item'   =>  'jpegtran Program',
                    'status'        =>  '<font color="red">' .  $LANG_MG01['not_found'] . '</font>'
                ));
            } else {
                $T->set_var(array(
                    'config_item'   =>  'jpegtran Program',
                    'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
                ));
            }
            $T->parse('CRow','CheckRow',true);
        }
        if ( $_MG_CONF['ffmpeg_enabled'] ) {
            if (PHP_OS == "WINNT") {
                $binary = "/ffmpeg.exe";
            } else {
                $binary = "/ffmpeg";
            }
            clearstatcache();
            if (! @file_exists( $_MG_CONF['ffmpeg_path'] . $binary ) ) {
                $T->set_var(array(
                    'config_item'   =>  'ffmpeg Program',
                    'status'        =>  '<font color="red">' .  $LANG_MG01['not_found'] . '</font>'
                ));
            } else {
                $T->set_var(array(
                    'config_item'   =>  'ffmpeg Program',
                    'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
                ));
            }
            $T->parse('CRow','CheckRow',true);
        }

        if ( $_MG_CONF['zip_enabled'] ) {
            if (PHP_OS == "WINNT") {
                $binary = "/unzip.exe";
            } else {
                $binary = "/unzip";
            }
            clearstatcache();
            if (! @file_exists( $_MG_CONF['zip_path'] . $binary ) ) {
                $T->set_var(array(
                    'config_item'   =>  'unzip Program',
                    'status'        =>  '<font color="red">' .  $LANG_MG01['not_found'] . '</font>'
                ));
            } else {
                $T->set_var(array(
                    'config_item'   =>  'unzip Program',
                    'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
                ));
            }
            $T->parse('CRow','CheckRow',true);
        }
    } else {
        $T->set_var(array(
            'config_item'   =>  'Program Locations',
            'status'        =>  '<font color="red">Unable to check because of safe_mode restrictions</font>',
        ));
        $T->parse('CRow','CheckRow',true);
    }

    // Now Check the directory permissions...

    $T->set_var(array(
        'config_item'   =>  '<b>' . $LANG_MG01['mg_dir_structure'] . '</b>',
        'status'        =>  ''
    ));
    $T->parse('CRow','CheckRow',true);

    $errCount = 0;

    // check tmp path

    if (! is_writable( $_MG_CONF['tmp_path'] ) ) {
        $T->set_var(array(
            'config_item'   =>  'tmp Path',
            'status'        =>  '<font color="red">' .  $LANG_MG01['not_writable'] . '</font>'
        ));
        $T->parse('CRow', 'CheckRow', true);
    } else {
        $T->set_var(array(
            'config_item'   =>  'tmp Path',
            'status'        =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
        ));
        $T->parse('CRow', 'CheckRow', true);
    }
    //      Now check directory permissions...
    $loopy=array('1','2','3','4','5','6','7','8','9','0','a','b','c','d','e','f');
    $elements = count($loopy);
    // do orig
    for ($i=0; $i<$elements; $i++) {
        if (! is_writable( $_MG_CONF['path_mediaobjects'] . 'orig/' . $loopy[$i] ) ) {
            $errCount++;
            $T->set_var(array(
                'config_item'   =>  $_MG_CONF['path_mediaobjects'] . 'orig/' . $loopy[$i],
                'status'        =>  '<font color="red">' . $LANG_MG01['not_writable'] . '</font>'
            ));
            $T->parse('CRow', 'CheckRow', true);
        }
    }

    for ($i=0; $i<$elements; $i++) {
        if (! is_writable( $_MG_CONF['path_mediaobjects'] . 'disp/' . $loopy[$i] ) ) {
            $T->set_var(array(
                'config_item'   =>  $_MG_CONF['path_mediaobjects'] . 'disp/' . $loopy[$i],
                'status'        =>  '<font color="red">' . $LANG_MG01['not_writable'] . '</font>'
            ));
            $errCount++;
            $T->parse('CRow', 'CheckRow', true);
        }
    }

    for ($i=0; $i<$elements; $i++) {
        if (! is_writable( $_MG_CONF['path_mediaobjects'] . 'tn/' . $loopy[$i] ) ) {
            $T->set_var(array(
                'config_item'   =>  $_MG_CONF['path_mediaobjects'] . 'tn/' . $loopy[$i],
                'status'        =>  '<font color="red">' . $LANG_MG01['not_writable'] . '</font>'
            ));
            $T->parse('CRow', 'CheckRow', true);
            $errCount++;
        }
    }

    if ( !is_writable( $_MG_CONF['path_mediaobjects'] . 'covers/' ) ) {
        $T->set_var(array(
            'config_item'   =>  $_MG_CONF['path_mediaobjects'] . 'covers/',
            'status'        =>  '<font color="red">' . $LANG_MG01['not_writable'] . '</font>'
        ));
        $T->parse('CRow', 'CheckRow', true);
        $errCount++;
    }

    if ( $errCount == 0 ) {
        $T->set_var(array(
            'config_item'       =>  $LANG_MG01['mg_directories'],
            'status'            =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
        ));
        $T->parse('CRow','CheckRow',true);
    }

    // Now check /rss directory ...
    if ( !is_writable( $_MG_CONF['path_html'] . 'rss/' ) ) {
        $T->set_var(array(
            'config_item'   =>  $_MG_CONF['path_html'] . 'rss/',
            'status'        =>  '<font color="red">' . $LANG_MG01['not_writable'] . '</font>'
        ));
        $T->parse('CRow', 'CheckRow', true);
        $errCount++;
    }

    if ( $errCount == 0 ) {
        $T->set_var(array(
            'config_item'       =>  $LANG_MG01['rss_directory'],
            'status'            =>  '<font color="green">' . $LANG_MG01['ok'] . '</font>'
        ));
        $T->parse('CRow','CheckRow',true);
    }


    // check php.ini settings...

    $T->set_var(array(
        'config_item'   =>  '<b>PHP php.ini settings</b>',
        'status'        =>  ''
    ));
    $T->parse('CRow','CheckRow',true);

    $inichecks = array('upload_max_filesize','file_uploads','post_max_size','max_execution_time','memory_limit','max_input_time','safe_mode','upload_tmp_dir');

    for ( $i=0; $i < count($inichecks); $i++) {
        $T->set_var(array(
            'config_item'   =>  $inichecks[$i],
            'status'        =>  ini_get($inichecks[$i])
        ));
        $T->parse('CRow', 'CheckRow', true);
    }

    $T->set_var(array(
        'lang_recheck'  => $LANG_MG01['recheck'],
        'lang_continue' => $LANG_MG01['continue']
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}
?>