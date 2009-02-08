<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | glFusion Installation                                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric Warren            eric AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2007-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Aaron Blankstein  - kantai AT gmail DOT com                     |
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
error_reporting( E_ALL );
//error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

if (!defined('GVERSION')) {
    define('GVERSION', '1.1.2');
}

define('SESSION_EXPIRED',           1);
define('SITECONFIG_EXISTS',         2);
define('SITECONFIG_NOT_WRITABLE',   3);
define('SITECONFIG_NOT_FOUND',      4);
define('DBCONFIG_NOT_WRITABLE',     5);
define('DBCONFIG_NOT_FOUND',        6);
define('DB_DATA_MISSING',           7);
define('DB_NO_CONNECT',             8);
define('DB_NO_DATABASE',            9);
define('DB_NO_INNODB',             10);
define('SITE_DATA_MISSING',        11);
define('LIBCUSTOM_NOT_WRITABLE',   12);
define('CORE_UPGRADE_ERROR',       13);
define('PLUGIN_UPGRADE_ERROR',     14);

require_once 'include/install.lib.php';
require_once 'include/template-lite.class.php';

function _checkSession()
{
    if ( !isset($_SESSION['expire']) ) {
        return _displayError(SESSION_EXPIRED,'');
    }

    if ($_SESSION['expire'] < time()  ) {
        return _displayError(SESSION_EXPIRED,'');
    }

    if ( $_SESSION['remoteip'] != $_SERVER['REMOTE_ADDR'] ) {
        return _displayError(SESSION_EXPIRED,'');
    }

    $_SESSION['expire'] = time() + 1800;
    return intval(0);
}

function _displayError($error,$step,$errorText='')
{
    global $LANG_INSTALL;

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'error.thtml');

    $T->set_var('title',$LANG_INSTALL['error']);
    $T->set_var('lang_prev',$LANG_INSTALL['return']);
    switch ($error) {
        case SESSION_EXPIRED :
            $T->set_var('text',$LANG_INSTALL['session_error']);
            break;
        case SITECONFIG_EXISTS :
            $T->set_var('text',$LANG_INSTALL['siteconfig_exists']);
            break;
        case SITECONFIG_NOT_WRITABLE :
            $T->set_var('text',$LANG_INSTALL['siteconfig_not_writable']);
            break;
        case SITECONFIG_NOT_FOUND :
            $T->set_var('text',$LANG_INSTALL['siteconfig_not_found']);
            break;
        case DBCONFIG_NOT_WRITABLE :
            $T->set_var('text',$LANG_INSTALL['dbconfig_not_writable']);
            break;
        case DBCONFIG_NOT_FOUND :
            $T->set_var('text',$LANG_INSTALL['dbconfig_not_found']);
            break;
        case DB_DATA_MISSING :
            $T->set_var('text',$LANG_INSTALL['missing_db_fields'].'<br /><br />'.$errorText);
            break;
        case DB_NO_CONNECT :
            $T->set_var('text',$LANG_INSTALL['no_db_connect']);
            break;
        case DB_NO_DATABASE :
            $T->set_var('text',$LANG_INSTALL['no_db']);
            break;
        case DB_NO_INNODB :
            $T->set_var('text',$LANG_INSTALL['no_innodb_support']);
            break;
        case SITE_DATA_MISSING :
            $T->set_var('text',$LANG_INSTALL['sitedata_missing'].'<br /><br />'.$errorText);
            break;
        case LIBCUSTOM_NOT_WRITABLE :
            $T->set_var('text',$LANG_INSTALL['libcustom_not_writable']);
            break;
        case CORE_UPGRADE_ERROR :
            $T->set_var('text',$LANG_INSTALL['core_upgrade_error'].'<br /><br />'.$errorText);
            break;
        case PLUGIN_UPGRADE_ERROR :
            $T->set_var('text',$LANG_INSTALL['plugin_upgrade_error'].'<br /><br />'.$errorText);
            break;
        default :
            $T->set_var('text',$errorText);
            break;
    }
    $T->set_var('step',$step);

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


function _displayWelcome( )
{
    global $LANG_INSTALL;

    // set the session expire time.
    $_SESSION['expire'] = time() + 1800;
    $_SESSION['remoteip'] = $_SERVER['REMOTE_ADDR'];

    if ( isset($_SESSION['language']) ) {
        $language = $_SESSION['language'];
    } else {
        $language = 'english';
    }

    $retval = '';

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'welcome.thtml');

    // create language select
    $lang_select = '<select name="language">' . LB;
    foreach (glob('language/*.php') as $filename) {
        $filename = preg_replace('/.php/', '', preg_replace('/language\//', '', $filename));
        $lang_select .= '<option value="' . $filename . '"' . (($filename == $language) ? ' selected="selected"' : '') . '>' . INST_prettifyLanguageName($filename) . '</option>' . LB;
    }
    $lang_select .= '</select>';

    $prevAction = '';
    $nextAction = 'finddbconfig';

    $T->set_var(array(
        'language_select'       => $lang_select,
        'nextaction'            => $nextAction,
        'prevaction'            => $prevAction,
        'step_heading'          => $LANG_INSTALL['install_heading'],
        'lang_welcome'          => $LANG_INSTALL['welcome_help'],
        'lang_select_language'  => $LANG_INSTALL['language'],
        'lang_next'             => $LANG_INSTALL['next'],
        'lang_prev'             => $LANG_INSTALL['previous'],
        'lang_select_task'      => $LANG_INSTALL['select_task'],
        'lang_new_install'      => $LANG_INSTALL['new_install'],
        'lang_site_upgrade'     => $LANG_INSTALL['site_upgrade'],
        'lang_geeklog_migrate'  => $LANG_INSTALL['geeklog_migrate'],
        'lang_proceed'          => $LANG_INSTALL['proceed'],
    ));

    $T->parse('output','page');

    $retval =  $T->finish($T->get_var('output'));

    return $retval;
}

function _getDBconfigPath()
{
    global $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    if ( isset($_SESSION['method']) && $_SESSION['method'] == 'install' ) {
        // if it isn't there, ask again...
        if ( @file_exists('../../siteconfig.php') ) {
            return _displayError(SITECONFIG_EXISTS,'');
        }
    }

    $fusion_path    = strtr(__FILE__, '\\', '/'); // replace all '\' with '/'
    for ($i = 0; $i < 4; $i++) {
        $remains = strrchr($fusion_path, '/');
        if ($remains === false) {
            break;
        } else {
            $fusion_path = substr($fusion_path, 0, -strlen($remains));
        }
    }

    if (!preg_match('/^.*\/$/', $fusion_path)) {
        $fusion_path .= '/';
    }

    $dbconfig_path = $fusion_path;

    // check the session to see if we have already defined the path...
    if ( isset($_SESSION['dbconfig_path']) ) {
        $dbconfig_path = $_SESSION['dbconfig_path'];
    }
    $dbconfig_file  = 'db-config.php';

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'dbconfigpath.thtml');


    if (@file_exists($dbconfig_path . $dbconfig_file) || @file_exists($dbconfig_path . 'public_html/' . $dbconfig_file) || @file_exists($dbconfig_path.'private/'.$dbconfig_file)
    || @file_exists($dbconfig_path . $dbconfig_file.'.dist') || @file_exists($dbconfig_path . 'public_html/' . $dbconfig_file.'.dist') || @file_exists($dbconfig_path.'private/'.$dbconfig_file.'.dist')
    ) {
        if ( @file_exists($dbconfig_path . 'public_html/' . $dbconfig_file)  || @file_exists($dbconfig_path . 'public_html/' . $dbconfig_file.'.dist')) {
            $dbconfig_path   = $dbconfig_path.'public_html/';
        } else if ( @file_exists($dbconfig_path.'private/'.$dbconfig_file) || @file_exists($dbconfig_path.'private/'.$dbconfig_file.'.dist')) {
            $dbconfig_path   = $dbconfig_path.'private/';
        }
        if ($dbconfig_path != '' ) {
            // found it, set the session
            $_SESSION['dbconfig_path'] = $dbconfig_path;
        }
    }
    $T->set_var(array(
        'dbconfig_path'     => $dbconfig_path,
        'step_heading'      => $LANG_INSTALL['system_path'],
        'lang_next'         => $LANG_INSTALL['next'],
        'lang_prev'         => $LANG_INSTALL['previous'],
        'lang_sys_path_help'=> $LANG_INSTALL['system_path_prompt'],
        'lang_sys_path_exp' => $LANG_INSTALL['system_path_example'],
    ));
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}

/*
 * Validate the dbconfig_path
 */

function _gotDBconfigPath($dbc_path = '')
{
    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    // was it passed from the previous step, or via $_POST?
    if ( $dbc_path == '' ) {
        $dbconfig_path = INST_stripslashes($_POST['dbconfig_path']);
    } else {
        $dbconfig_path = $dbc_path;
    }

    // check to see if the path contains the actual filename?
    if (!strstr($dbconfig_path, 'db-config.php')) {
        // If the user did not provide a trailing '/' then add one
        if (!preg_match('/^.*\/$/', $dbconfig_path)) {
            $dbconfig_path .= '/';
        }
    } else {
        $dbconfig_path = str_replace('db-config.php', '', $dbconfig_path);
    }

    // store entered path into the session var
    $_SESSION['dbconfig_path'] = $dbconfig_path;

    // now, lets see if it exists, if not, try to rename the .dist file...

    if (!@file_exists($dbconfig_path.'db-config.php') ) {
        // see if the .dist is there
        if ( @file_exists($dbconfig_path.'db-config.php.dist') ) {
            // found it, try to rename..
            $rc = @copy($dbconfig_path.'db-config.php.dist',$dbconfig_path.'db-config.php');
            if ( $rc !== true ) {
                return _displayError(DBCONFIG_NOT_WRITABLE,'finddbconfig');
            }
        } else {
            return _displayError(DBCONFIG_NOT_FOUND,'finddbconfig');
        }
    }

    // if it isn't there, ask again...
    if ( !@file_exists($dbconfig_path.'db-config.php') ) {
        return _displayError(DBCONFIG_NOT_FOUND,'finddbconfig');
    }
    // found it, but it is read-only...
    if ( !INST_isWritable($dbconfig_path.'db-config.php') ) {
        return _displayError(DBCONFIG_NOT_WRITABLE,'finddbconfig');
    }

    // we have a good path to /private, off to the next step...
    return _checkSitePermissions($dbconfig_path);
}

function _checkSitePermissions($dbconfig_path='')
{
    global $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $previousaction = 'finddbconfig';

    // was it passed from the previous step
    if ( $dbconfig_path == '') {
        if ( !isset($_SESSION['dbconfig_path']) ) {
            return _getDBconfigPath();
        }
        $dbconfig_path = $_SESSION['dbconfig_path'];
    }

    $permError = 0;

    $T = new TemplateLite('templates/');
    $T->set_file('page','permission.thtml');

    $T->set_var('step_heading','Hosting Environment Check');

    /*
     * First we will validate the general environment..
     */

    $T->set_block('page','envs','env');

    // PHP Version

    $T->set_var('item','PHP Version');
    if ( INST_phpOutOfDate() ) {
        $T->set_var('status','<span class="no">'.$LANG_INSTALL['too_old'].'</span>');
    } else {
        $T->set_var('status','<span class="yes">'.$LANG_INSTALL['ok'].'</span>');
    }
    $T->set_var('recommended','4.3.0+');
    $T->parse('env','envs',true);

    $rg = ini_get('register_globals');
    $sm = ini_get('safe_mode');
    $ob = ini_get('open_basedir');

    $rg = ini_get('register_globals');
    $T->set_var('item','register_globals');
    $T->set_var('status',$rg == 1 ? '<span class="no">'.$LANG_INSTALL['on'].'</span>' : '<span class="yes">'.$LANG_INSTALL['off'].'</span>');
    $T->set_var('recommended',$LANG_INSTALL['off']);
    $T->parse('env','envs',true);

    $sm = ini_get('safe_mode');
    $T->set_var('item',$LANG_INSTALL['safe_mode']);
    $T->set_var('status',$sm == 1 ? '<span class="no">'.$LANG_INSTALL['on'].'</span>' : '<span class="yes">'.$LANG_INSTALL['off'].'</span>');
    $T->set_var('recommended',$LANG_INSTALL['off']);
    $T->parse('env','envs',true);

    $ob = ini_get('open_basedir');
    $T->set_var('item',$LANG_INSTALL['open_basedir']);
    $T->set_var('status',$ob == '' ? '<span class="yes">'.$LANG_INSTALL['none'].'</span>' : '<span class="no">'.$ob.'</span>');
    $T->parse('env','envs',true);

    if ( $_SESSION['method'] == 'upgrade' && @file_exists('../../siteconfig.php')) {
        include '../../siteconfig.php';
        $_PATH['public_html']   = INST_getHtmlPath();
        $_PATH['dbconfig_path'] = $_CONF['path'];
        $_PATH['admin_path']    = INST_getAdminPath();
    } else {
        $_PATH['public_html']   = INST_getHtmlPath();
        if ( $dbconfig_path == '' ) {
            $_PATH['dbconfig_path'] = INST_stripslashes($_POST['dbconfig_path']);
        } else {
            $_PATH['dbconfig_path']     = $dbconfig_path;
        }
        $_PATH['admin_path']        = INST_getAdminPath();
    }

    if (!preg_match('/^.*\/$/', $_PATH['public_html'])) {
        $_PATH['public_html'] .= '/';
    }
    if (!preg_match('/^.*\/$/', $_PATH['dbconfig_path'])) {
        $_PATH['dbconfig_path'] .= '/';
    }
    if (!preg_match('/^.*\/$/', $_PATH['admin_path'])) {
        $_PATH['admin_path'] .= '/';
    }

    $file_list = array( $_PATH['dbconfig_path'],
                        $_PATH['dbconfig_path'].'db-config.php',
                        $_PATH['dbconfig_path'].'data/',
                        $_PATH['dbconfig_path'].'logs/error.log',
                        $_PATH['dbconfig_path'].'data/layout_cache/',
                        $_PATH['dbconfig_path'].'data/temp/',

                        $_PATH['public_html'],
                        $_PATH['public_html'].'siteconfig.php',
                        $_PATH['public_html'].'backend/glfusion.rss',
                        $_PATH['public_html'].'images/articles/',
                        $_PATH['public_html'].'images/topics/',
                        $_PATH['public_html'].'images/userphotos/',

                        $_PATH['public_html'].'mediagallery/mediaobjects/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/covers/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/orig/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/disp/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/tn/',
                        $_PATH['public_html'].'mediagallery/rss/',
                        $_PATH['public_html'].'mediagallery/watermarks/',

                        $_PATH['public_html'].'filemgmt_data/',
                        $_PATH['public_html'].'filemgmt_data/category_snaps/',
                        $_PATH['public_html'].'filemgmt_data/files/',
                        $_PATH['public_html'].'filemgmt_data/snaps/',

                        $_PATH['public_html'].'forum/media/',
                        $_PATH['public_html'].'forum/media/tn/',

                      );

    $T->set_var('dbconfig_path',$_PATH['dbconfig_path']);

    $T->set_block('page','perms','perm');

    $classCounter = 0;
    foreach ($file_list AS $path) {
        $ok = INST_isWritable($path);
        if ( !$ok ) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? '<span class="yes">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
            if  ( !$ok ) {
                $permError = 1;
            }
        }
    }
    // special test to see if we can create a directory under layout_cache...
    $rc = @mkdir($_PATH['dbconfig_path'].'data/layout_cache/test/');
    if (!$rc) {
        $T->set_var('location',$_PATH['dbconfig_path'].'data/layout_cache/');
        $T->set_var('status', $ok ? '<span class="yes">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['unable_mkdir'].'</span>');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);
        $permError = 1;
    } else {
        $ok = INST_isWritable($_PATH['dbconfig_path'].'data/layout_cache/test/');
        if ( !$ok ) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? '<span class="yes">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
            if  ( !$ok ) {
                $permError = 1;
            }
        }
        @rmdir($_PATH['dbconfig_path'].'data/layout_cache/test/');
    }

    // special test to see if existing cache files exist and are writable...
    $rc = INST_checkCacheDir($_PATH['dbconfig_path'].'data/layout_cache/',$T,$classCounter);
    if ( $rc > 0 ) {
        $permError = 1;
    }

    if ( $permError ) {
        $button = 'Recheck';
        $action = 'checkperms';
        $T->set_var('error_message',$LANG_INSTALL['correct_perms']);
    } else {
        $T->set_var('location',$LANG_INSTALL['directory_permissions']);
        $T->set_var('status', 1 ? '<span class="yes">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
        $classCounter++;
        $T->parse('perm','perms',true);

        $T->set_var('location',$LANG_INSTALL['file_permissions']);
        $T->set_var('status', 1 ? '<span class="yes">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
        $classCounter++;
        $T->parse('perm','perms',true);

        $button = $LANG_INSTALL['next'];

        if ( $_SESSION['method'] == 'upgrade' ) {
            $action = 'doupgrade';
            $previousaction = '';
        } else {
            $action = 'getdatabase';
            $previousaction = 'finddbconfig';
        }
    }
    $T->set_var(array(
        'previousaction'    => $previousaction,
        'nextaction'        => $action,
        'button'            => $button,
        'lang_previous'     => $LANG_INSTALL['previous'],
        'lang_host_env'     => $LANG_INSTALL['hosting_env'],
        'lang_setting'      => $LANG_INSTALL['setting'],
        'lang_current'      => $LANG_INSTALL['current'],
        'lang_recommended'  => $LANG_INSTALL['recommended'],
        'lang_warning'      => $LANG_INSTALL['perm_warning'],
        'lang_filesystem'   => $LANG_INSTALL['filesystem_check'],
    ));
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}

function _getDBData()
{
    global $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    if ( !isset($_SESSION['dbconfig_path']) ) {
        return _getDBconfigPath();
    }
    $dbconfig_path = $_SESSION['dbconfig_path'];

    require $dbconfig_path.'db-config.php';

    $T = new TemplateLite('templates/');
    $T->set_file('page','database.thtml');

    if ( isset($_SESSION['db_type']) ) {
        $_DB_dbms = $_SESSION['db_type'];
    }
    if ( isset($_SESSION['db_host']) ) {
        $_DB_host = $_SESSION['db_host'];
    }
    if ( isset($_SESSION['db_name']) ) {
        $_DB_name = $_SESSION['db_name'];
    }
    if ( isset( $_SESSION['db_user']) ) {
        $_DB_user = $_SESSION['db_user'];
    }
    if ( isset( $_SESSION['db_pass']) ) {
        $_DB_pass = $_SESSION['db_pass'];
    } else {
        $_DB_pass = '';
    }
    if ( isset( $_SESSION['db_prefix']) ) {
        $_DB_table_prefix = $_SESSION['db_prefix'];
    }

    if ( isset($_SESSION['innodb']) && $_SESSION['innodb'] ) {
        $T->set_var('innodb_selected',' selected="selected"');
        $T->set_var('noinnodb_selected','');
    } else {
        $T->set_var('noinnodb_selected',' selected="selected"');
        $T->set_var('innodb_selected','');
    }

    $T->set_var(array(
        'db_type'                       => $_DB_dbms,
        'db_host'                       => $_DB_host,
        'db_name'                       => $_DB_name,
        'db_user'                       => $_DB_user,
        'db_pass'                       => $_DB_pass,
        'db_prefix'                     => $_DB_table_prefix,
        'lang_next'                     => $LANG_INSTALL['next'],
        'lang_prev'                     => $LANG_INSTALL['previous'],
        'step_heading'                  => $LANG_INSTALL['database_info'],
        'lang_database_type'            => $LANG_INSTALL['db_type'],
        'lang_database_hostname'        => $LANG_INSTALL['db_hostname'],
        'lang_database_name'            => $LANG_INSTALL['db_name'],
        'lang_database_user'            => $LANG_INSTALL['db_user'],
        'lang_database_password'        => $LANG_INSTALL['db_pass'],
        'lang_database_table_prefix'    => $LANG_INSTALL['db_table_prefix'],
        'lang_connection_settings'      => $LANG_INSTALL['connection_settings'],
        'lang_connection_settings_help' => $LANG_INSTALL['connection_setting_help'],
    ));

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}

function _gotDBData()
{
    global $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $numErrors = 0;
    $errText   = '';

    if ( isset($_POST['db_type']) && $_POST['db_type'] != '') {
        $db_type = INST_stripslashes($_POST['db_type']);
    } else {
        $db_type = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_type_error'].'<br />';
    }
    if ( isset($_POST['db_host']) && $_POST['db_host'] != '') {
        $db_host = INST_stripslashes($_POST['db_host']);
    } else {
        $db_host = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_hostname_error'].'<br />';
    }
    if ( isset($_POST['db_name']) && $_POST['db_name'] != '') {
        $db_name = INST_stripslashes($_POST['db_name']);
    } else {
        $db_name = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_name_error'].'<br />';
    }
    if ( isset($_POST['db_user']) && $_POST['db_user'] != '') {
        $db_user = INST_stripslashes($_POST['db_user']);
    } else {
        $db_user = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_user_error'].'<br />';
    }
    if ( isset($_POST['db_pass']) && $_POST['db_pass'] != '') {
        $db_pass = INST_stripslashes($_POST['db_pass']);
    } else {
        $db_pass = '';
    }
    if ( isset($_POST['db_prefix']) ) {
        $db_prefix = INST_stripslashes($_POST['db_prefix']);
    } else {
        $db_prefix = '';
    }

    $innodb = false;
    switch ($db_type) {
        case 'mysql-innodb':
            $innodb = true;
            $db_type = 'mysql';
        case 'mysql' :
            break;
    }

    // populate the session vars...

   $_SESSION['db_type']     = $db_type;
   $_SESSION['innodb']      = $innodb;
   $_SESSION['db_host']     = $db_host;
   $_SESSION['db_name']     = $db_name;
   $_SESSION['db_user']     = $db_user;
   $_SESSION['db_pass']     = $db_pass;
   $_SESSION['db_prefix']   = $db_prefix;

    if ( $numErrors > 0 ) {
        return _displayError(DB_DATA_MISSING,'getdatabase',$errText);
    }

    $db_handle = @mysql_connect($db_host, $db_user, $db_pass);
    if (!$db_handle) {
        return _displayError(DB_NO_CONNECT,'getdatabase');
    }
    if ($db_handle) {
        $connected = @mysql_select_db($db_name, $db_handle);
    }
    if ( !$connected) {
        return _displayError(DB_NO_DATABASE,'getdatabase');
    }
    if ( $innodb ) {
        $res = @mysql_query("SHOW VARIABLES LIKE 'have_innodb'");
        $A = @mysql_fetch_array($res);
        if (strcasecmp ($A[1], 'yes') != 0) {
            return _displayError(DB_NO_INNODB,'getdatabase');
        }
    }

    return _getSiteData();
}


function _getSiteData()
{
    global $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $T = new TemplateLite('templates/');
    $T->set_file('page','sitedata.thtml');

    $site_name      = (isset($_SESSION['site_name']) ? $_SESSION['site_name'] : '');
    $site_slogan    = (isset($_SESSION['site_slogan']) ? $_SESSION['site_slogan'] : '');
    $site_url       = (isset($_SESSION['site_url']) ? $_SESSION['site_url'] : INST_getSiteUrl());
    $site_admin_url = (isset($_SESSION['site_admin_url']) ? $_SESSION['site_admin_url'] : INST_getSiteAdminUrl());
    $site_mail      = (isset($_SESSION['site_mail']) ? $_SESSION['site_mail'] : '');
    $noreply_mail   = (isset($_SESSION['noreply_mail']) ? $_SESSION['noreply_mail'] : '');
    $utf8           = (isset($_SESSION['utf8']) ? $_SESSION['utf8'] : 1);

    $T->set_var(array(
        'site_name'     => $site_name,
        'site_slogan'   => $site_slogan,
        'site_url'      => $site_url,
        'site_admin_url'=> $site_admin_url,
        'site_mail'     => $site_mail,
        'noreply_mail'  => $noreply_mail,
        'lang_next'                 => $LANG_INSTALL['next'],
        'lang_prev'                 => $LANG_INSTALL['previous'],
        'lang_install'              => $LANG_INSTALL['install'],
        'lang_site_information'     => $LANG_INSTALL['site_info'],
        'lang_site_name'            => $LANG_INSTALL['site_name'],
        'lang_site_slogan'          => $LANG_INSTALL['site_slogan'],
        'lang_site_url'             => $LANG_INSTALL['site_url'],
        'lang_site_admin_url'       => $LANG_INSTALL['site_admin_url'],
        'lang_site_email'           => $LANG_INSTALL['site_email'],
        'lang_site_noreply_email'   => $LANG_INSTALL['site_noreply_email'],
        'lang_utf8'                 => $LANG_INSTALL['use_utf8'],
        'lang_sitedata_help'        => $LANG_INSTALL['sitedata_help'],
    ));

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}

function _gotSiteData()
{
    global $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $numErrors = 0;
    $errText   = '';

    if ( isset($_POST['site_name']) && $_POST['site_name'] != '' ) {
        $site_name = INST_stripslashes($_POST['site_name']);
    } else {
        $site_name = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_name_error'].'<br />';
    }
    if ( isset($_POST['site_slogan']) && $_POST['site_slogan'] != '' ) {
        $site_slogan = INST_stripslashes($_POST['site_slogan']);
    } else {
        $site_slogan = '';
    }
    if ( isset($_POST['site_url']) && $_POST['site_url'] != '' ) {
        $site_url = INST_stripslashes($_POST['site_url']);
    } else {
        $site_url = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_url_error'].'<br />';
    }

    if ( isset($_POST['site_admin_url']) && $_POST['site_admin_url'] != '' ) {
        $site_admin_url = INST_stripslashes($_POST['site_admin_url']);
    } else {
        $site_admin_url = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_admin_url_error'].'<br />';
    }
    if ( isset($_POST['site_mail']) && $_POST['site_mail'] != '' ) {
        $site_mail = INST_stripslashes($_POST['site_mail']);
    } else {
        $site_mail = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_email_error'].'<br />';
    }
    if ( isset($_POST['noreply_mail']) && $_POST['noreply_mail'] != '' ) {
        $noreply_mail = INST_stripslashes($_POST['noreply_mail']);
    } else {
        $noreply_mail = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_noreply_email_error'].'<br />';
    }

    $_SESSION['site_name']       = $site_name;
    $_SESSION['site_slogan']     = $site_slogan;
    $_SESSION['site_url']        = $site_url;
    $_SESSION['site_admin_url']  = $site_admin_url;
    $_SESSION['site_mail']       = $site_mail;
    $_SESSION['noreply_mail']    = $noreply_mail;
    $_SESSION['utf8']            = 1;

    if ( $numErrors > 0 ) {
        return _displayError(SITE_DATA_MISSING,'getdata',$errText);
    }
    return _doInstall();
}

/*
 * Perform the installation.
 */
function _doInstall()
{
    global $_SYSTEM, $_CONF, $_TABLES, $_DB, $_DB_dbms, $_DB_host, $_DB_user,
           $_DB_pass, $site_url,$_DB_table_prefix, $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    if ( isset($_SESSION['innodb']) ) {
        $use_innodb = $_SESSION['innodb'];
    } else {
        $use_innodb = false;
    }

    $utf8 = (isset($_SESSION['utf8']) ? $_SESSION['utf8'] : 1);
    if ( isset($_SESSION['language']) ) {
        $language = $_SESSION['language'];
    } else {
        $language = 'english';
    }

    $_PATH['dbconfig_path'] = $_SESSION['dbconfig_path'];
    $_PATH['public_html']   = INST_getHtmlPath();
    if (!preg_match('/^.*\/$/', $_PATH['public_html'])) {
        $_PATH['public_html'] .= '/';
    }
    $dbconfig_path = str_replace('db-config.php', '', $_PATH['dbconfig_path']);

    // check the lib-custom...
    if (!@file_exists($_PATH['dbconfig_path'].'system/lib-custom.php') ) {
        if ( @file_exists($_PATH['dbconfig_path'].'system/lib-custom.php.dist') ) {
            $rc = @copy($_PATH['dbconfig_path'].'system/lib-custom.php.dist',$_PATH['dbconfig_path'].'system/lib-custom.php');
            if ( $rc === false ) {
                return _displayError(LIBCUSTOM_NOT_WRITABLE,'getdata');
            }
        }
    }

    // check and see if site config really exists...
    if (!@file_exists($_PATH['public_html'].'siteconfig.php') ) {
        if ( @file_exists($_PATH['public_html'].'siteconfig.php.dist') ) {
            $rc = @copy($_PATH['public_html'].'siteconfig.php.dist',$_PATH['public_html'].'siteconfig.php');
            if ( $rc === false ) {
                return _displayError(SITECONFIG_NOT_WRITABLE,'getdata');
            }
        }
    }

    // Edit siteconfig.php and enter the correct GL path and system directory path
    $siteconfig_path = $_PATH['public_html'] . 'siteconfig.php';
    $siteconfig_file = fopen($siteconfig_path, 'r');
    $siteconfig_data = fread($siteconfig_file, filesize($siteconfig_path));
    fclose($siteconfig_file);

    require $siteconfig_path;
    $siteconfig_data = str_replace("\$_CONF['path'] = '{$_CONF['path']}';",
                        "\$_CONF['path'] = '" . str_replace('db-config.php', '', $_PATH['dbconfig_path']) . "';",
                        $siteconfig_data);

    $siteconfig_file = fopen($siteconfig_path, 'w');
    if (!fwrite($siteconfig_file, $siteconfig_data)) {
        return _displayError(SITECONFIG_NOT_WRITABLE,'getdata');
    }
    fclose ($siteconfig_file);
    require $siteconfig_path;

    $config_file = $_SESSION['dbconfig_path'].'db-config.php';

    require $config_file;

    $db = array('host' => (isset($_SESSION['db_host']) ? $_SESSION['db_host'] : $_DB_host),
                'name' => (isset($_SESSION['db_name']) ? $_SESSION['db_name'] : $_DB_name),
                'user' => (isset($_SESSION['db_user']) ? $_SESSION['db_user'] : $_DB_user),
                'pass' => (isset($_SESSION['db_pass']) ? $_SESSION['db_pass'] : $_DB_pass),
                'table_prefix' => (isset($_SESSION['db_prefix']) ? $_SESSION['db_prefix'] : $_DB_table_prefix),
                'type' => 'mysql');

    $dbconfig_file = fopen($config_file, 'r');
    $dbconfig_data = fread($dbconfig_file, filesize($config_file));
    fclose($dbconfig_file);

    $dbconfig_data = str_replace("\$_DB_host = '" . $_DB_host . "';", "\$_DB_host = '" . $_SESSION['db_host'] . "';", $dbconfig_data); // Host
    $dbconfig_data = str_replace("\$_DB_name = '" . $_DB_name . "';", "\$_DB_name = '" . $_SESSION['db_name'] . "';", $dbconfig_data); // Database
    $dbconfig_data = str_replace("\$_DB_user = '" . $_DB_user . "';", "\$_DB_user = '" . $_SESSION['db_user'] . "';", $dbconfig_data); // Username
    $dbconfig_data = str_replace("\$_DB_pass = '" . $_DB_pass . "';", "\$_DB_pass = '" . $_SESSION['db_pass'] . "';", $dbconfig_data); // Password
    $dbconfig_data = str_replace("\$_DB_table_prefix = '" . $_DB_table_prefix . "';", "\$_DB_table_prefix = '" . $_SESSION['db_prefix'] . "';", $dbconfig_data); // Table prefix
    $dbconfig_data = str_replace("\$_DB_dbms = '" . $_DB_dbms . "';", "\$_DB_dbms = '" . 'mysql' . "';", $dbconfig_data); // Database type

    // Write changes to db-config.php
    $dbconfig_file = fopen($config_file, 'w');
    if (!fwrite($dbconfig_file, $dbconfig_data)) {
        return _displayError(DBCONFIG_NOT_WRITABLE,'getdata');
    }
    fclose($dbconfig_file);
    require $config_file;

    require $_CONF['path_system'].'lib-database.php';

    list($rc,$errors) = INST_createDatabaseStructures($use_innodb);
    if ( $rc != true ) {
        return _displayError(DB_NO_CONNECT,'getdata',$errors);
    }
    $site_name      = isset($_SESSION['site_name']) ? $_SESSION['site_name'] : '';
    $site_slogan    = isset($_SESSION['site_slogan']) ? $_SESSION['site_slogan'] : '';
    $site_url       = isset($_SESSION['site_url']) ? $_SESSION['site_url'] : INST_getSiteUrl();
    $site_admin_url = isset($_SESSION['site_admin_url']) ? $_SESSION['site_admin_url'] : INST_getSiteAdminUrl();
    $site_mail      = isset($_SESSION['site_mail']) ? $_SESSION['site_mail'] : '' ;
    $noreply_mail   = isset($_SESSION['noreply_mail']) ? $_SESSION['noreply_mail'] : '' ;

    INST_personalizeAdminAccount($site_mail, $site_url);

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once 'config-install.php';
    install_config($site_url);

    $gl_path    = $_SESSION['dbconfig_path'];
    $html_path  = $_PATH['public_html'];

    $config = config::get_instance();
    $config->set('site_name', urldecode($site_name));
    $config->set('site_slogan', urldecode($site_slogan));
    $config->set('site_url', urldecode($site_url));
    $config->set('site_admin_url', urldecode($site_admin_url));
    $config->set('site_mail', urldecode($site_mail));
    $config->set('noreply_mail', urldecode($noreply_mail));
    $config->set('path_html', $html_path);
    $config->set('path_log', $gl_path . 'logs/');
    $config->set('path_language', $gl_path . 'language/');
    $config->set('backup_path', $gl_path . 'backups/');
    $config->set('path_data', $gl_path . 'data/');
    $config->set('path_images', $html_path . 'images/');
    $config->set('path_themes', $html_path . 'layout/');
    $config->set('rdf_file', $html_path . 'backend/glfusion.rss');
    $config->set('path_pear', $_CONF['path_system'] . 'pear/');
    $config->set_default('default_photo', urldecode($site_url) . '/default.jpg');

    $lng = INST_getDefaultLanguage($gl_path . 'language/', $language, $utf8);
    if (!empty($lng)) {
        $config->set('language', $lng);
    }

    DB_change($_TABLES['vars'], 'value', GVERSION,
                                'name', 'database_version');

    $_CONF['path_html']         = $html_path;
    $_CONF['site_url']          = $site_url;
    $_CONF['site_admin_url']    = $site_admin_url;

    // Setup nouveau as the default
    $config->set('theme', 'nouveau');
    DB_query("UPDATE {$_TABLES['users']} SET theme='nouveau' WHERE uid=2",1);

    $config->_purgeCache();
    // rebuild the config array
    include $siteconfig_path;
    $config->set_configfile($_CONF['path'] . 'db-config.php');
    $config->load_baseconfig();
    $config->initConfig();
    $_CONF = $config->get_config('Core');

    $config->_purgeCache();

    global $_CONF, $_SYSTEM, $_DB, $_GROUPS, $_RIGHTS, $TEMPLATE_OPTIONS;

    require $_CONF['path_html'].'lib-common.php';

    INST_pluginAutoInstall('bad_behavior2');
    INST_pluginAutoInstall('captcha');
    INST_pluginAutoInstall('commentfeeds');
    INST_pluginAutoInstall('sitetailor');
    INST_pluginAutoInstall('spamx');
    INST_pluginAutoInstall('staticpages');

    $config->_purgeCache();
    INST_clearCache();

    $T = new TemplateLite('templates/');
    $T->set_file('page','plugins.thtml');

    $T->set_var(array(
        'lang_almost_done'          =>  $LANG_INSTALL['almost_done'],
        'lang_step_description'     =>  $LANG_INSTALL['step_description'],
        'lang_load_sample_content'  =>  $LANG_INSTALL['load_sample_content'],
        'lang_calendar'             =>  $LANG_INSTALL['calendar'],
        'lang_filemgmt'             =>  $LANG_INSTALL['filemgmt'],
        'lang_mediagallery'         =>  $LANG_INSTALL['mediagallery'],
        'lang_forum'                =>  $LANG_INSTALL['forum'],
        'lang_polls'                =>  $LANG_INSTALL['polls'],
        'lang_links'                =>  $LANG_INSTALL['links'],
        'lang_calendar_desc'        =>  $LANG_INSTALL['calendar_desc'],
        'lang_filemgmt_desc'        =>  $LANG_INSTALL['filemgmt_desc'],
        'lang_mediagallery_desc'    =>  $LANG_INSTALL['mediagallery_desc'],
        'lang_forum_desc'           =>  $LANG_INSTALL['forum_desc'],
        'lang_polls_desc'           =>  $LANG_INSTALL['polls_desc'],
        'lang_links_desc'           =>  $LANG_INSTALL['links_desc'],
        'lang_next'                 =>  $LANG_INSTALL['next'],
    ));

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


function _doPluginInstall()
{
    global $_CONF, $_TABLES, $_DB_table_prefix;

    $site_url = $_CONF['site_url'];
    $language = $_SESSION['language'];

    $pluginsToInstall = $_POST['plugin'];
    if ( is_array($pluginsToInstall) ) {
        foreach ($pluginsToInstall AS $plugin => $settings ) {
            $rc = INST_pluginAutoInstall($plugin);
        }
    }
    if ( isset($_POST['installdefaultdata']) ) {
        require_once $_CONF['path'].'sql/default_content.php';
        // pull a list of all plugins that are installed....
        $result = DB_query("SELECT pi_name FROM {$_TABLES['plugins']} WHERE pi_enabled = 1");
        $installedPlugins = array();
        while ($A = DB_fetchArray($result)) {
            $installedPlugins[] = $A['pi_name'];
        }
        // install the core default data first
        if ( is_array($_CORE_DEFAULT_DATA) ) {
            foreach ($_CORE_DEFAULT_DATA AS $sql) {
                DB_query($sql,1);
            }
        }
        // install the static pages default data
        if ( is_array($_SP_DEFAULT_DATA) ) {
            foreach ($_SP_DEFAULT_DATA AS $sql) {
                $fsql = str_replace("xxxSITEURLxxx",$site_url,$sql);
                DB_query($fsql,1);
            }
        }
        // update the site tailor menu to reflect the static pages content
        if ( is_array($_ST_DEFAULT_DATA) ) {
            foreach ($_ST_DEFAULT_DATA AS $sql) {
                DB_query($sql,1);
            }
        }
        // cycle through the rest of the installed plugins and add their data
        if ( is_array($installedPlugins) ) {
            foreach ($installedPlugins AS $plugin) {
                if ( is_array($_DATA[$plugin]) ) {
                    foreach ($_DATA[$plugin] AS $sql) {
                        DB_query($sql,1);
                    }
                }
            }
        }
    }

    INST_clearCache();

    header('Location: success.php?type=install&language=' . $language);
    exit;
}

/*
function _validatePath()
{
    $dbconfig_path = $_SESSION['dbconfig_path'];

    if (@file_exists($dbconfig_path.'db-config.php') && INST_isWritable($dbconfig_path.'db-config.php') ) {
        return true;
    }
    return false;
}
*/

function _doUpgrade()
{
    global $_CONF,  $_TABLES, $_DB_dbms, $LANG_INSTALL;

    $version = INST_identifyglFusionVersion();

    // Query `vars` and see if 'database_engine' == 'InnoDB'
    $result = DB_query("SELECT `name`,`value` FROM {$_TABLES['vars']} WHERE `name`='database_engine'");
    $row = DB_fetchArray($result);
    if ($row['value'] == 'InnoDB') {
       $use_innodb = true;
    } else {
       $use_innodb = false;
    }

    list($rc,$errors) = INST_doDatabaseUpgrades($version, $use_innodb);

// ******* TESTING CODE - FORCE ERROR
//    $rc = 0;
//    $errors = 'Error 1<br />Error 2<br />Error 3<br />';
// **********************************
    INST_clearCache();

    if ( $rc ) {
        require_once $_CONF['path_system'] . 'classes/config.class.php';
        $config = config::get_instance();
        $config->_purgeCache();

        /*
         * We are done with this step, return so we can fall through
         * to the plugin upgrades.
         */
        return;
    } else {
        $display = '';
        $display .= '<h2>' . $LANG_INSTALL['upgrade_error'] . '</h2>
            <p>' . $LANG_INSTALL['upgrade_error_text'] . '</p>' . LB;
        $display .= $errors;
        return _displayError(CORE_UPGRADE_ERROR,'done',$display);
    }
    return;
}

function _doPluginUpgrade()
{
    global $_CONF, $_TABLES, $LANG_INSTALL;

    $language = $_SESSION['language'];

    $upgradeError = '';
    $error        = '';

    INST_checkPlugins();

    $rc = INST_pluginAutoUpgrade('calendar');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Calendar');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('links');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Links');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('polls');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Polls');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('spamx');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Spamx');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('staticpages');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Static Pages');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('sitetailor',1);
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Site Tailor');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('captcha');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'CAPTCHA');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('bad_behavior2',1);
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Bad Behavior2');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('filemgmt');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'FileMgmt');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('forum');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Forum');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('mediagallery');
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Media Gallery');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('commentfeeds',1);
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Comment Feeds');
        $upgradeError = 1;
    }

    $stdPlugins=array('staticpages','spamx','links','polls','calendar','sitetailor','captcha','bad_behavior2','forum','mediagallery','filemgmt','commentfeeds');
    foreach ($stdPlugins AS $pi_name) {
        DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version='1.1.2', pi_homepage='http://www.glfusion.org' WHERE pi_name='".$pi_name."'",1);
    }

    INST_clearCache();

    // ************* TEST CODE - REMOVE *****
    // $upgradeError = 1;
    // $error = 'Problem 1<br>Problem 2<br>';
    // **************************************

    if ( $upgradeError ) {
        return _displayError(PLUGIN_UPGRADE_ERROR,'done',$error);
    }

    header('Location: success.php?type=upgrade&language=' . $language);
}

/*
 * Start of the main program
 */

$_SYSTEM['no_cache_config']  = true;

session_name(md5('glfusioninstallation'));
session_start();

/*
 * The driver, based on inputs received, we'll decide what to do and where to go
 */

$fusion_path    = strtr(__FILE__, '\\', '/'); // replace all '\' with '/'
for ($i = 0; $i < 4; $i++) {
    $remains = strrchr($fusion_path, '/');
    if ($remains === false) {
        break;
    } else {
        $fusion_path = substr($fusion_path, 0, -strlen($remains));
    }
}

if ( isset($_SESSION['language']) ) {
    $lng = $_SESSION['language'];
} else {
    $lng = 'english';
}
if ( isset($_POST['language']) ) {
    $lng = $_POST['language'];
}

// sanitize value and check for file
$lng = preg_replace('/[^a-z0-9\-_]/', '', $lng);
if (!empty($lng) && is_file('language/' . $lng . '.php')) {
    $language = $lng;
} else {
    $language = 'english';
}

$_SESSION['language'] = $language;
require_once 'language/'.$language.'.php';

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

if ( isset($_POST['prev']) ) {
    $mode = $_POST['previousstep'];
}

if ( !isset($_SESSION['method'])) {
    $method = 'install';
} else {
    $method = $_SESSION['method'];
}

if ( isset($_POST['type']) ) {
    switch($_POST['type']) {
        case 'install' :
            $method = 'install';
            $mode   = 'finddbconfig';
            break;
        case 'upgrade' :
            $method = 'upgrade';
            $mode   = 'startupgrade';
            break;
    }
}

$_SESSION['method'] = $method;

switch($mode) {
    case 'finddbconfig' :
        $pageBody = _getDBconfigPath();
        break;
    case 'gotdbconfig':
        $pageBody =   _gotDBconfigPath();
        break;
    case 'checkperms' :
        $pageBody = _checkSitePermissions();
        break;
    case 'getdatabase' :
        $pageBody = _getDBData();
        break;
    case 'gotdbdata' :
        $pageBody = _gotDBData();
        break;
    case 'getdata' :
        $pageBody = _getSiteData();
        break;
    case 'gotsitedata' :
        $pageBody = _gotSiteData();
        break;
    case 'doinstall' :
        $pageBody = _doInstall();
        break;
    case 'installplugins' :
        require '../../lib-common.php';
        $pageBody = _doPluginInstall();
        break;
    case 'startupgrade' :
        if ( !@file_exists('../../siteconfig.php') ) {
            $pageBody = _displayError(SITECONFIG_NOT_FOUND,'');
        } else {
            require '../../siteconfig.php';
            require $_CONF['path'].'db-config.php';
            $_SESSION['dbconfig_path'] = $_CONF['path'];
            $pageBody = _checkSitePermissions();
        }
        break;
    case 'doupgrade' :
        if ( !@file_exists('../../siteconfig.php') ) {
            $pageBody = _displayError(SITECONFIG_NOT_FOUND,'');
        } else {
            require '../../siteconfig.php';
            require $_CONF['path'].'db-config.php';
            $_SESSION['dbconfig_path'] = $_CONF['path'];
            require $_CONF['path_system'] . 'lib-database.php';
            $pageBody = _doUpgrade();
        }
        if ( $pageBody != '' ) {
            break;
        }
        // fall through here on purpose and process the plugin upgrades.....
        // at this point we have a fully updated database and core environment
    case 'dopluginupgrade' :
        require '../../lib-common.php';
        $pageBody = _doPluginUpgrade();
        break;
    case 'done' :
        $method = $_SESSION['method'];
        header('Location: success.php?type='.$method.'&language=' . $language);
        exit;
    default:
        if ( !isset($_POST['prev']) ) {
            session_unset();
        }
        $_SESSION['language'] = $language;
        $_SESSION['method'] = $method;
        $pageBody = _displayWelcome( );
        break;
}

echo INST_header();
echo $pageBody;
echo INST_footer();
exit;
?>