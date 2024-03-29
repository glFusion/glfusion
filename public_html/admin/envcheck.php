<?php
/**
* glFusion CMS
*
* glFusion Environment Check
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Eric Warren     eric AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\FileSystem;

USES_lib_admin();

$display = '';

if (!SEC_hasRights ('env.admin')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30])
        . COM_showMessageText($MESSAGE[200],$MESSAGE[30],true,'error')
        . COM_siteFooter ();
    Log::logAccessViolation('Environment Check');
    echo $display;
    exit;
}

function _checkEnvironment()
{
    global $_CONF, $_TABLES, $_PLUGINS, $_SYSTEM, $LANG_ADMIN, $LANG_ENVCHK,
           $_FF_CONF, $_MG_CONF, $_DB_dbms,$_DB, $_CP_CONF, $_FM_CONF;

    $retval = '';
    $permError = 0;

    $db = Database::getInstance();

    $required_extensions = array(
        array('extension' => 'ctype',   'fail' => 1),
        array('extension' => 'curl',    'fail' => 0),
        array('extension' => 'date',    'fail' => 1),
        array('extension' => 'filter',  'fail' => 1),
        array('extension' => 'gettext', 'fail' => 0),
        array('extension' => 'hash',    'fail' => 0),
        array('extension' => 'intl',    'fail' => 0),
        array('extension' => 'json',    'fail' => 1),
        array('extension' => 'mbstring','fail' => 0),
        array('extension' => 'openssl', 'fail' => 0),
        array('extension' => 'session', 'fail' => 1),
        array('extension' => 'xml',     'fail' => 1),
        array('extension' => 'zlib',    'fail' => 1)
    );

    $T = new Template($_CONF['path_layout'] . 'admin');
    $T->set_file('page','envcheck.thtml');

    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/envcheck.php',
              'text' => $LANG_ENVCHK['recheck']),
        array('url'  => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_ENVCHK['hosting_env'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_ENVCHK['php_warning'],
        $_CONF['layout_url'] . '/images/icons/envcheck.png'
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    /*
     * First we will validate the general environment..
     */

    $T->set_block('page','envs','env');

    // PHP Version

    $T->set_var('item',$LANG_ENVCHK['php_version']);

    $classCounter = 0;

    $T->set_var('status',phpversion());
    if ( _phpOutOfDate() ) {
        $T->set_var('class','tm-fail');
    } else {
        $T->set_var('class','tm-pass');
    }
    $T->set_var('recommended',PHP_REC_VERSION.'+');

    $phpnotes = sprintf($LANG_ENVCHK['php_req_version'],PHP_MIN_VERSION);
    if ( !_phpUpToDate() ) {
        $phpnotes .= '<br><span class="tm-fail">'.$LANG_ENVCHK['phpendoflife'].'</span>';
    }
    $T->set_var('notes',$phpnotes);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $st = ini_get('short_open_tag');
    $T->set_var('item','short_open_tag');
    $T->set_var('status',$st == 1 ? $LANG_ENVCHK['on'] : $LANG_ENVCHK['off']);
    $T->set_var('class',$st == 1 ? 'tm-fail' : 'tm-pass');
    $T->set_var('recommended',$LANG_ENVCHK['off']);
    $T->set_var('notes',$LANG_ENVCHK['short_open_tags']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    if (version_compare(PHP_VERSION,'7.0.0','<')) {
        $ob = ini_get('open_basedir');
        if ( $ob == '' ) {
            $open_basedir_restriction = 0;
        } else {
            $open_basedir_restriction = 1;
            $open_basedir_directories = $ob;
        }
        $T->set_var('item','open_basedir');
        $T->set_var('status',$ob == '' ? $LANG_ENVCHK['off'] : $LANG_ENVCHK['enabled']);
        $T->set_var('class', $ob == '' ? 'tm-pass' : 'tm-fail');
        $T->set_var('notes',$LANG_ENVCHK['open_basedir']);
        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('env','envs',true);
        $classCounter++;
    } else {
        $open_basedir_restriction = 0;
    }

    $memory_limit = _return_bytes(ini_get('memory_limit'));
    $memory_limit_print = _bytes_to_mg($memory_limit); //  / 1024) / 1024;
    $T->set_var('item','memory_limit');

    $T->set_var('status',$memory_limit < 50331648 ? $memory_limit_print : $memory_limit_print);
    $T->set_var('class',$memory_limit < 50331648 ? 'tm-fail' : 'tm-pass');
    $T->set_var('recommended','64M');
    $T->set_var('notes',$LANG_ENVCHK['memory_limit']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $fu = ini_get('file_uploads');
    $T->set_var('item','file_uploads');
    $T->set_var('status',$fu == 1 ? $LANG_ENVCHK['on'] : $LANG_ENVCHK['off']);
    $T->set_var('class',$fu == 1 ? 'tm-pass' : 'tm-fail');
    $T->set_var('recommended',$LANG_ENVCHK['on']);
    $T->set_var('notes',$LANG_ENVCHK['file_uploads']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $upload_limit = _return_bytes(ini_get('upload_max_filesize'));
    $upload_limit_print = _bytes_to_mg($upload_limit);
    $T->set_var('item','upload_max_filesize');
    // check for at least 8M
    $T->set_var('status',$upload_limit < 8388608 ? $upload_limit_print : $upload_limit_print);
    $T->set_var('class',$upload_limit < 8388608 ? 'tm-fail' : 'tm-pass');
    $T->set_var('recommended','8M');
    $T->set_var('notes',$LANG_ENVCHK['upload_max_filesize']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $post_limit = _return_bytes(ini_get('post_max_size'));
    if ( $post_limit == 0 ) {
        $post_limit_print = $LANG_ENVCHK['unlimited'];
        $T->set_var('status',$post_limit_print);
        $T->set_var('class','tm-pass');
    } else {
        $post_limit_print = _bytes_to_mg($post_limit);
        $T->set_var('status',$post_limit < 8388608 ? $post_limit_print : $post_limit_print);
        $T->set_var('class',$post_limit < 8388608 ? 'tm-fail' : 'tm-pass');
    }
    $T->set_var('item','post_max_size');
    $T->set_var('recommended','8M');
    $T->set_var('notes',$LANG_ENVCHK['post_max_size']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    $max_execution_time = ini_get('max_execution_time');
    $T->set_var('item', 'max_execution_time');
    $T->set_var('status', $max_execution_time < 30 ? $max_execution_time . ' secs' : $max_execution_time . ' secs');
    $T->set_var('class', $max_execution_time < 30 ? 'tm-fail' : 'tm-pass');
    $T->set_var('recommended', '30 secs');
    $T->set_var('notes',$LANG_ENVCHK['max_execution_time']);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('env','envs',true);
    $classCounter++;

    if (defined ('DVLP_DEBUG')) {
        $errorReportingLevel = error_level_tostring(error_reporting(), ' ');
        $T->set_var('item', 'error_reporting');
        $T->set_var('status', '');
        $T->set_var('class', 'tm-pass');
        $T->set_var('recommended', '');
        $T->set_var('notes',$errorReportingLevel);
        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('env','envs',true);
        $classCounter++;
    }

    // instance Caching
    $instance_caching = $_SYSTEM['disable_instance_caching'] ? false : true;
    $T->set_var(array(
        'item'      => 'instance_caching',
        'status'    => $_SYSTEM['disable_instance_caching'] ? $LANG_ENVCHK['disabled'] : $LANG_ENVCHK['enabled'],
        'class'     => $instance_caching ? 'tm-pass' : 'tm-fail',
        'recommended' => $LANG_ENVCHK['enabled'],
        'notes'     => $LANG_ENVCHK['instance_cache'],
        'rowclass'  => ($classCounter % 2)+1
    ));
    $T->parse('env','envs',true);
    $classCounter++;

    $mysql_version = $db->_mysql_version; // DB_getVersion();
    $T->set_var('mysql', $LANG_ENVCHK['database_version']);
    $T->set_var('mysql_version',$mysql_version);
    $T->set_var('rowclass',($classCounter % 2)+1);
    $classCounter++;

// Cache Driver

    $c = Cache::getInstance();
    $cDriver = strtolower($c->getDriverName());

    if ($cDriver != strtolower($_CONF['cache_driver'])) {
        $T->set_var('item', $LANG_ENVCHK['cache_driver']);
        $T->set_var('status', $cDriver);
        $T->set_var('class', 'tm-fail');
        $T->set_var('recommended', '');
        $T->set_var('notes',$LANG_ENVCHK['cache_driver_error'].$_CONF['cache_driver']);
        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('env','envs',true);
    } else {
        $T->set_var('item', $LANG_ENVCHK['cache_driver']);
        $T->set_var('status', $cDriver);
        $T->set_var('class', 'tm-pass');
        $T->set_var('recommended', '');
        $T->set_var('notes',($cDriver == 'devnull') ? $LANG_ENVCHK['cache_disabled'] : $LANG_ENVCHK['cache_driver_ok']);
        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('env','envs',true);
    }
    $classCounter++;

    $T->set_block('page','libs','lib');

    foreach ( $required_extensions AS $extension ) {
        if ( $extension['fail'] ) {
            $note = $LANG_ENVCHK[$extension['extension'].'_extension'] .' '.$LANG_ENVCHK['is_required'];
        } else {
            $note = $LANG_ENVCHK[$extension['extension'].'_extension'] .' '.$LANG_ENVCHK['is_optional'];
        }
        if  (extension_loaded($extension['extension'])) {
            $T->set_var(array(
                'item' => $LANG_ENVCHK[$extension['extension'].'_extension'],
                'status' => $LANG_ENVCHK['ok'],
                'notes' => $note,
                'class' => 'tm-pass',
            ));
        } else {
            $T->set_var(array(
                'item' => $LANG_ENVCHK[$extension['extension'].'_extension'],
                'status' => $LANG_ENVCHK['not_found'],
                'notes' => $note,
                'class' => 'tm-fail',
            ));
        }
        $T->parse('lib','libs',true);
    }

    switch ( $_CONF['image_lib'] ) {
        case 'graphicsmagick' :    // GraphicsMagick
            if (PHP_OS == "WINNT") {
                $binary = "/gm.exe";
            } else {
                $binary = "/gm";
            }
            clearstatcache();
            if (! @file_exists( $_CONF['path_to_mogrify'] . $binary ) ) {
                $T->set_var(array(
                    'item'   =>  $LANG_ENVCHK['graphicsmagick'],
                    'status' =>  $LANG_ENVCHK['not_found'],
                    'class' =>  'tm-fail',
                    'notes'  => $LANG_ENVCHK['gm_not_found'],
                ));
            } else {
                $T->set_var(array(
                    'item'   => $LANG_ENVCHK['graphicsmagick'],
                    'status' => $LANG_ENVCHK['ok'],
                    'class' => 'tm-pass',
                    'notes'  => $LANG_ENVCHK['gm_ok'],
                ));
            }
            break;
        case 'imagemagick' :    // ImageMagick
            if (PHP_OS == "WINNT") {
                $binary = "/convert.exe";
            } else {
                $binary = "/convert";
            }
            clearstatcache();
            if (! @file_exists( $_CONF['path_to_mogrify'] . $binary ) ) {
                $T->set_var(array(
                    'item'   =>  $LANG_ENVCHK['imagemagick'],
                    'status' =>  $LANG_ENVCHK['not_found'],
                    'class' =>  'tm-fail',
                    'notes'  => $LANG_ENVCHK['im_not_found'],
                ));
            } else {
                $T->set_var(array(
                    'item'   => $LANG_ENVCHK['imagemagick'],
                    'status' => $LANG_ENVCHK['ok'],
                    'class' => 'tm-pass',
                    'notes'  => $LANG_ENVCHK['im_ok'],
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
                    'item'   => $LANG_ENVCHK['netpbm'],
                    'status' => $LANG_ENVCHK['not_found'],
                    'class' => 'tm-fail',
                    'notes'  => $LANG_ENVCHK['np_not_found'],
                ));
            } else {
                $T->set_var(array(
                    'item'   =>  $LANG_ENVCHK['netpbm'],
                    'status' =>  $LANG_ENVCHK['ok'],
                    'class' =>  'tm-pass',
                    'notes'  => $LANG_ENVCHK['np_ok'],
                ));
            }
            break;
    }
    $T->set_var('rowclass',($classCounter % 2)+1);
    $T->parse('lib','libs',true);
    $classCounter++;

    if ( $_CONF['image_lib'] == 'gdlib' || (isset($_CP_CONF['gfxDriver']) && ($_CP_CONF['gfxDriver'] == CAPTCHA_GDLIB || $_CP_CONF['gfxDriver'] == CAPTCHA_MATH ))) {
        if ($gdv = gdVersion()) {
            if ($gdv >=2) {
                $T->set_var(array(
                    'item'   => $LANG_ENVCHK['gd_lib'],
                    'status' => $LANG_ENVCHK['ok'],
                    'class' => 'tm-pass',
                    'notes'  => $LANG_ENVCHK['gd_ok'],
                ));

            } else {
                $T->set_var(array(
                    'item'   => $LANG_ENVCHK['gd_lib'],
                    'status' => $LANG_ENVCHK['ok'],
                    'class' => 'tm-pass',
                    'notes'  => $LANG_ENVCHK['gd_v1'],
                ));
            }
        } else {
            $T->set_var(array(
                'item'   =>  $LANG_ENVCHK['gd_lib'],
                'status' =>  $LANG_ENVCHK['not_found'],
                'class'  =>  'tm-fail',
                'notes' =>   $LANG_ENVCHK['gd_not_found'],
            ));
        }

        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('lib','libs',true);
        $classCounter++;
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
                'item'      => $LANG_ENVCHK['jhead'],
                'status'    => $LANG_ENVCHK['not_found'],
                'class'    => 'tm-fail',
                'notes'     => $LANG_ENVCHK['jhead_not_found'],
            ));
        } else {
            $T->set_var(array(
                'item'      => $LANG_ENVCHK['jhead'],
                'status'    => $LANG_ENVCHK['ok'],
                'class'    => 'tm-pass',
                'notes'     => $LANG_ENVCHK['jhead_ok'],
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
                'item'   => $LANG_ENVCHK['jpegtran'],
                'status' => $LANG_ENVCHK['not_found'],
                'class' => 'tm-fail',
                'notes'  => $LANG_ENVCHK['jpegtran_not_found'],
            ));
        } else {
            $T->set_var(array(
                'item'   => $LANG_ENVCHK['jpegtran'],
                'status' => $LANG_ENVCHK['ok'],
                'class' => 'tm-pass',
                'notes'  => $LANG_ENVCHK['jpegtran_ok'],
            ));
        }
        $T->set_var('rowclass',($classCounter % 2)+1);
        $T->parse('lib','libs',true);
        $classCounter++;
    }

    $dbInfo['db_driver'] = $db->dbGetDriverName();
    $dbInfo['db_version'] = $db->_mysql_version;

    try {
        $stmt = $db->conn->query("SELECT @@character_set_database, @@collation_database;");
    } catch(Throwable $e) {
        $dbInfo['db_collation'] = $LANG_ENVCHK['unknown'];
        $dbInfo['db_charset']   = $LANG_ENVCHK['unknown'];
    }
    $dbInfo['db_collation'] = $LANG_ENVCHK['unknown'];
    $dbInfo['db_charset']   = $LANG_ENVCHK['unknown'];
    if ($stmt !== false && $stmt !== null) {
        $row = $stmt->fetch(Database::ASSOCIATIVE);
        if ($row !== false && $row !== null) {
            $dbInfo['db_collation'] = $row["@@collation_database"];
            $dbInfo['db_charset'] = $row["@@character_set_database"];
        }
    }

    $dbInfo['db_engine'] = 'MyISAM';
    $dbType = $db->getItem($_TABLES['vars'],'value',array('name' => 'database_engine'));
    if ($dbType !== null && $dbType !== false) {
        $dbInfo['db_engine'] = $dbType;
    }
    foreach ($dbInfo AS $name => $value ) {
        $T->set_var($name,$value);
    }

    $T->set_var(array(
        'lang_status'       => $LANG_ENVCHK['status'],
        'lang_db_header'    => $LANG_ENVCHK['db_header'],
        'lang_db_driver'    => $LANG_ENVCHK['db_driver'],
        'lang_db_version'   => $LANG_ENVCHK['db_version'],
        'lang_db_engine'    => $LANG_ENVCHK['db_engine'],
        'lang_db_charset'   => $LANG_ENVCHK['db_charset'],
        'lang_db_collation' => $LANG_ENVCHK['db_collation'],
    ));

    // extract syndication storage path
    if (empty($_CONF['path_rss'])) {
        $feedPath = $_CONF['path_html'].'backend/';
    } else {
        $feedPath = $_CONF['path_rss'];
    }

    $file_list = array( $_CONF['path_data'],
                        $_CONF['path_data'].'glfusion.lck',
                        $_CONF['path_data'].'glfusion_css.lck',
                        $_CONF['path_data'].'glfusion_js.lck',
                        $_CONF['path_log'].'error.log',
                        $_CONF['path_log'].'access.log',
                        $_CONF['path_log'].'captcha.log',
                        $_CONF['path_log'].'spamx.log',
                        $_CONF['path_log'].'404.log',
                        $_CONF['path_data'].'cache/',
                        $_CONF['path_data'].'layout_cache/',
                        $_CONF['path_data'].'cache/',
                        $_CONF['path_data'].'temp/',
                        $_CONF['path_data'].'htmlpurifier/',
                        $_CONF['path_html'],
                        $feedPath,
                        $_CONF['path_images'],
                        $_CONF['path_images'].'articles/',
                        $_CONF['path_images'].'topics/',
                        $_CONF['path_images'].'userphotos/',
                        $_CONF['path_images'].'library/File/',
                        $_CONF['path_images'].'library/Flash/',
                        $_CONF['path_images'].'library/Image/',
                        $_CONF['path_images'].'library/Media/',
                        $_CONF['path_images'].'library/userfiles/',
                    );
    $mg_file_list = array();
    if (isset($_MG_CONF)) {
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
                            $_CONF['path_html'].'data/mediagallery/watermarks/',
                        );
    }

    $fm_file_list = array(
                        $_FM_CONF['FileStore'],
                        $_FM_CONF['FileStore'].'tmp/',
                        $_FM_CONF['SnapStore'],
                        $_FM_CONF['SnapStore'].'tmp/',
                        $_FM_CONF['SnapCat'],
                        $_FM_CONF['SnapCat'].'tmp/',
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
        $ok = (bool) _isWritable($path);
        if ( $ok === false) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok === true ? $LANG_ENVCHK['ok'] : $LANG_ENVCHK['not_writable']);
            $T->set_var('class', $ok === true ? 'tm-pass' : 'tm-fail');
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
            $T->set_var('status', $ok ? '<span class="yes">'.$LANG_ENVCHK['ok'].'</span>' : '<span class="notwriteable">'.$LANG_ENVCHK['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
        }
----------------------- */
    }

    // special test to see if we can create a directory under layout_cache...
    $rc = @mkdir($_CONF['path_data'].'layout_cache/test/');
    if (!$rc) {
        $T->set_var('location',$_CONF['path_data'].'layout_cache/');
        $T->set_var('status', $LANG_ENVCHK['unable_mkdir']);
        $T->set_var('class', 'tm-fail');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);
        $permError = 1;
        @rmdir($_CONF['path_data'].'layout_cache/test/');
    } else {
        $ok = _isWritable($_CONF['path_data'].'layout_cache/test/');
        if ( !$ok ) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? $LANG_ENVCHK['ok'] : $LANG_ENVCHK['not_writable']);
            $T->set_var('class', $ok ? 'tm-pass' : 'tm-fail');
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
        $button = $LANG_ENVCHK['recheck'];
        $action = 'checkenvironment';
        $T->set_var('error_message',$LANG_ENVCHK['correct_perms']);

        $recheck  = '<button type="submit" name="submit" onclick="submitForm( checkenv, \'checkenvironment\' );">' . LB;
        $recheck .= $LANG_ENVCHK['recheck'] . LB;
        $recheck .= '<img src="layout/arrow-recheck.gif" alt=""/>' . LB;
        $recheck .= '</button>' . LB;

    } else {
        $classCounter = 0;
        $recheck = '';
        $T->set_var('location',$LANG_ENVCHK['directory_permissions']);
        $T->set_var('status', $LANG_ENVCHK['ok']);
        $T->set_var('class', 'tm-pass');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);

        $T->set_var('location',$LANG_ENVCHK['file_permissions']);
        $T->set_var('status', $LANG_ENVCHK['ok']);
        $T->set_var('class', 'tm-pass');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);
    }

    $T->set_var(array(
        'lang_host_env'     => $LANG_ENVCHK['hosting_env'],
        'lang_setting'      => $LANG_ENVCHK['setting'],
        'lang_current'      => $LANG_ENVCHK['current'],
        'lang_recommended'  => $LANG_ENVCHK['recommended'],
        'lang_notes'        => $LANG_ENVCHK['notes'],
        'lang_filesystem'   => $LANG_ENVCHK['filesystem_check'],
        'lang_php_settings' => $LANG_ENVCHK['php_settings'],
        'lang_php_warning'  => $LANG_ENVCHK['php_warning'],
        'lang_current_php_settings' => $LANG_ENVCHK['current_php_settings'],
        'lang_show_phpinfo' => $LANG_ENVCHK['show_phpinfo'],
        'lang_hide_phpinfo' => $LANG_ENVCHK['hide_phpinfo'],
        'lang_graphics'     => $LANG_ENVCHK['graphics'],
        'lang_extensions'   => $LANG_ENVCHK['extensions'],
        'lang_recheck'      => $LANG_ENVCHK['recheck'],
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

    if ($path[strlen($path)-1]=='/') {
        FileSystem::mkDir($path);
        return ($path.uniqid(mt_rand()).'.tmp');
    }

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
    $last = strtolower(substr($val, -1));
    $val = (int) substr($val, 0, -1);
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
    if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
        return '';
    }
    ob_start();
    phpinfo();

    preg_match ('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);

    $retval = "<div class='phpinfodisplay' style=\"font-size:1.2em;width:100%\"><style type='text/css'>\n" .
        join( "\n",
            array_map(
                function($i) {
                    return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );
                },
                preg_split( '/\n/', trim(preg_replace( "/\nbody/", "\n", $matches[1])) )
                )
            ) .
        "</style>\n" .
        $matches[2] .
        "\n</div>\n";

    return $retval;

}

function error_level_tostring($intval, $separator = ',')
{
    $errorlevels = array(
        E_ALL => 'E_ALL',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_DEPRECATED => 'E_DEPRECATED',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_STRICT => 'E_STRICT',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_NOTICE => 'E_NOTICE',
        E_PARSE => 'E_PARSE',
        E_WARNING => 'E_WARNING',
        E_ERROR => 'E_ERROR');
    $result = '';
    foreach($errorlevels as $number => $name) {
        if (($intval & $number) == $number) {
            $result .= ($result != '' ? $separator : '').$name;
            if ( $name == 'E_ALL' ) break;
        }
    }
    return $result;
}

$page = _checkEnvironment();

$display  = COM_siteHeader();
$display .= $page;
$display .= COM_siteFooter();
echo $display;
?>
