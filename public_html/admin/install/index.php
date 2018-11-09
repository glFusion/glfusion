<?php
/**
* glFusion CMS
*
* glFusin Installation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2007-2008 by the following authors:
*  Aaron Blankstein  - kantai AT gmail DOT com
*
*/

error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

@ini_set('opcache.enable','0');
if (!defined('GVERSION')) {
    define('GVERSION', '2.0.0');
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
define('DB_EXISTS',                11);
define('SITE_DATA_MISSING',        12);
define('SITE_DATA_ERROR',          13);
define('LIBCUSTOM_NOT_WRITABLE',   14);
define('LIBCUSTOM_NOT_FOUND',      15);
define('CORE_UPGRADE_ERROR',       16);
define('PLUGIN_UPGRADE_ERROR',     17);
define('NO_MIGRATE_GLFUSION',      19);
define('FILE_INCLUDE_ERROR',       20);
define('NO_DB_DRIVER',             21);
define('FILE_CLEANUP_ERROR',       22);
define('DB_NO_UTF8',               23);
define('DB_NO_CHECK_UTF8',         24);
define('DB_TOO_OLD',               25);

require_once 'include/install.lib.php';
require_once 'include/template-lite.class.php';

require_once 'deleted.php';

if( function_exists('set_error_handler') ) {
    $defaultErrorHandler = set_error_handler('INST_handleError', error_reporting());
}

$_GLFUSION = array();

$glFusionVars = array('language','method','migrate','expire','dbconfig_path','log_path','lang_path','backup_path','data_path','db_type','innodb','db_host','db_name','db_user','db_pass','db_prefix','site_name','site_slogan','site_url','site_admin_url','site_mail','noreply_mail','utf8','original_version','securepassword');

if ( is_array($_POST) ) {
    foreach ($_POST AS $name => $value) {
        if ( in_array($name,$glFusionVars)) {
            switch ($name) {
                case 'dbconfig_path' :
                case 'log_path' :
                case 'lang_path':
                case 'backup_path':
                case 'data_path' :
                    $_GLFUSION[$name] = INST_sanitizePath(INST_stripslashes($value));
                    break;
                default :
                    $_GLFUSION[$name] = INST_stripslashes($value);
                    break;
            }
        }
    }
}

/**
 * Error Handler - attempt to trap certain errors
 *
 * will set the global $_GLFUSION['errstr'] with the error text
 *
 * @return  nothing
 *
 */
function INST_handleError($errno, $errstr, $errfile='', $errline=0, $errcontext='')
{
    global $_GLFUSION;
    $_GLFUSION['errstr'] = $errstr;
    return;
}

/**
 * Builds hidden inputs for all session data
 *
 * @return  string          HTML
 *
 */
function _buildHiddenFields()
{
    global $_GLFUSION;

    $hiddenFields = '';

    if ( is_array($_GLFUSION) ) {
        foreach ($_GLFUSION AS $name => $value) {
            $hiddenFields .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />'.LB;
        }
    }
    return $hiddenFields;
}


/**
 * Builds the progress bar
 *
 * @return  string          HTML
 *
 */
function _buildProgressBar($currentStep, &$T)
{
    global $_GLFUSION, $LANG_INSTALL;

    $installSteps = array('languagetask'        => $LANG_INSTALL['language_task'],
                          'installalert'        => $LANG_INSTALL['instruction_step'],
                          'pathsetting'         => $LANG_INSTALL['path_settings'],
                          'checkenvironment'    => $LANG_INSTALL['env_check'],
                          'getsiteinformation'  => $LANG_INSTALL['site_info'],
                          'contentplugins'      => $LANG_INSTALL['content_plugins'],
                          'complete'            => $LANG_INSTALL['complete']);

    $upgradeSteps = array('languagetask'        => $LANG_INSTALL['language_task'],
                          'upgradealert'        => $LANG_INSTALL['instruction_step'],
                          'checkenvironment'    => $LANG_INSTALL['env_check'],
                          'upgrade'             => $LANG_INSTALL['perform_upgrade'],
                          'cleanup'             => $LANG_INSTALL['cleanup'],
                          'complete'            => $LANG_INSTALL['complete'],
                          );
    $retval = '';
    $first  = 0;
    $found  = 0;

    if ( $_GLFUSION['method'] == 'install' )  {
        $T->set_var('lang_step_title',$LANG_INSTALL['install_steps']);
    } else {
        $T->set_var('lang_step_title',$LANG_INSTALL['upgrade_steps']);
    }
    $T->set_var('lang_online_help',$LANG_INSTALL['online_help_text']);
    $T->set_var('lang_support_resources',$LANG_INSTALL['support_resources']);
    $T->set_var('lang_glfusion_plugins',$LANG_INSTALL['plugins']);
    $T->set_var('lang_support_forums',$LANG_INSTALL['support_forums']);
    $T->set_var('lang_community_chat',$LANG_INSTALL['community_chat']);

    $T->set_block('header','steps','st');

    switch ($_GLFUSION['method']) {
        case 'install' :
            foreach ($installSteps AS $step => $desc) {
                $T->set_var('lang_step',$desc);
                if ( $step == $currentStep ) {
                    $found++;
                    $T->set_var('step_class','arrow-left');
                    $T->set_var('state','tm-step-current');
                } else {
                    if ( $found ) {
                        $T->set_var('step_class','');
                        $T->set_var('state','tm-step-pending');
                    } else {
                        $T->set_var('step_class','check');
                        $T->set_var('state','tm-step-complete');
                    }
                }
                $T->parse('st','steps',true);
            }
            break;
        case 'upgrade' :
            foreach ($upgradeSteps AS $step => $desc) {
                $T->set_var('lang_step',$desc);
                if ( $step == $currentStep ) {
                    $found++;
                    $T->set_var('step_class','arrow-left');
                    $T->set_var('state','tm-step-current');
                } else {
                    if ( $found ) {
                        $T->set_var('step_class','');
                        $T->set_var('state','tm-step-pending');
                    } else {
                        $T->set_var('step_class','check');
                        $T->set_var('state','tm-step-complete');
                    }
                }
                $T->parse('st','steps',true);
            }
            break;
    }

    return $retval;
}


/**
 * Checks to see if the session has timed out.
 *
 * @return  bool          0 - session is OK
 *
 */
function _checkSession()
{
    global $_GLFUSION;

    if ( !isset($_GLFUSION['expire']) ) {
        return _displayError(SESSION_EXPIRED,'');
    }

    if ($_GLFUSION['expire'] < time()  ) {
        return _displayError(SESSION_EXPIRED,'');
    }

    $_GLFUSION['expire'] = time() + 1800;

    return intval(0);
}


/**
 * Displays error text
 *
 * @return  string          HTML
 *
 */
function _displayError($error,$step,$errorText='')
{
    global $_GLFUSION, $LANG_INSTALL;

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'error.thtml');

    $T->set_var('title',$LANG_INSTALL['error']);
    $T->set_var('lang_prev',$LANG_INSTALL['previous']);
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
            $T->set_var('text',$LANG_INSTALL['missing_db_fields'].'<br /><br /><br />'.$errorText);
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
            $T->set_var('text',$LANG_INSTALL['sitedata_missing']);
            break;
        case SITE_DATA_ERROR :
            $T->set_var('text',$LANG_INSTALL['sitedata_missing']);
            break;
        case LIBCUSTOM_NOT_WRITABLE :
            $T->set_var('text',$LANG_INSTALL['libcustom_not_writable']);
            break;
        case LIBCUSTOM_NOT_FOUND :
            $T->set_var('text',$LANG_INSTALL['libcustom_not_found']);
            break;
        case CORE_UPGRADE_ERROR :
            $T->set_var('text',$LANG_INSTALL['core_upgrade_error']);
            break;
        case PLUGIN_UPGRADE_ERROR :
            $T->set_var('text',$LANG_INSTALL['plugin_upgrade_error_desc']);
            break;
        case DB_EXISTS :
            $T->set_var('text',$LANG_INSTALL['database_exists']);
            break;
        case DB_NO_UTF8 :
            $T->set_var('text',$LANG_INSTALL['no_utf8']);
            break;
        case DB_NO_CHECK_UTF8 :
            $T->set_var('text',$LANG_INSTALL['no_check_utf8']);
            break;
        case DB_TOO_OLD :
            $T->set_var('text',$LANG_INSTALL['db_too_old']);
            break;
        case NO_MIGRATE_GLFUSION :
            $T->set_var('text',$LANG_INSTALL['no_migrate_glfusion']);
            break;
        case FILE_INCLUDE_ERROR :
            $T->set_var('text','Internal Error - please contact support@glfusion.org' . ' ' . $errorText);
            break;
        case NO_DB_DRIVER :
            $T->set_var('text',$LANG_INSTALL['no_db_driver']);
            break;
        default :
            $T->set_var('text',$errorText);
            break;
    }
    $T->set_var('detailed_error',$errorText);
    $T->set_var('step',$step);
    $T->set_var('hiddenfields',_buildHiddenFields());
    $T->set_var('lang_online_install_help',$LANG_INSTALL['online_install_help']);

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


/**
 * Display initial welcome screen.
 *
 * Determine what language to use and what task to perform, i.e.;
 * New Installation or Upgrade.
 *
 * @return  string          HTML
 *
 */
function INST_getLanguageTask( )
{
    global $_GLFUSION, $LANG_INSTALL;

    // set the session expire time.
    $_GLFUSION['expire'] = time() + 1800;

    $_GLFUSION['currentstep'] = 'languagetask';

    if ( isset($_GLFUSION['language']) ) {
        $language = $_GLFUSION['language'];
    } else {
        $language = 'english_utf-8';
    }

    $retval = '';

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'languagetask.thtml');

    // create language select
    $lang_select = '<select name="lang" onchange="reload(this.form)">' . LB;
    foreach (glob('language/*.php') as $filename) {
        $filename = preg_replace('/.php/', '', preg_replace('/language\//', '', $filename));
        $lang_select .= '<option value="' . $filename . '"' . (($filename == $language) ? ' selected="selected"' : '') . '>' . INST_prettifyLanguageName($filename) . '</option>' . LB;
    }
    $lang_select .= '</select>';

    $prevAction = '';
    $nextAction = 'installalert';

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
        'lang_proceed'          => $LANG_INSTALL['proceed'],
        'lang_language_support' => $LANG_INSTALL['language_support'],
        'lang_language_pack'    => $LANG_INSTALL['language_pack'],
        'hiddenfields'          => _buildHiddenFields(),
        'percent_complete'      => '10',
    ));

//    if ( !isset($_GLFUSION['method'] ) ) {
        if (@file_exists('../../siteconfig.php' ) ) {
            $T->set_var('upgradeselected',' selected="selected"');
            $_GLFUSION['method'] = 'upgrade';
        }
//    }

    $T->parse('output','page');

    $retval =  $T->finish($T->get_var('output'));

    return $retval;
}


/**
 * Retrieve path to private/ directory
 *
 * Prompt the user to enter (or verify) the path to the system
 * private directory.
 *
 * @return  string          HTML form for path entry
 *
 */
function INST_getPathSetting()
{
    global $_GLFUSION, $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $_GLFUSION['currentstep'] = 'pathsetting';

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
    if ( isset($_GLFUSION['dbconfig_path']) ) {
        $dbconfig_path = $_GLFUSION['dbconfig_path'];
    }
    $dbconfig_file  = 'db-config.php';

    $htmlpath = INST_getHtmlPath();

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'pathsetting.thtml');

    clearstatcache();
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
            $_GLFUSION['dbconfig_path'] = $dbconfig_path;
        }
    }
    $T->set_var(array(
        'dbconfig_path'     => $dbconfig_path,
        'log_path'          => isset($_GLFUSION['log_path']) ? $_GLFUSION['log_path'] : '',
        'lang_path'         => isset($_GLFUSION['lang_path']) ? $_GLFUSION['lang_path'] : '',
        'backup_path'       => isset($_GLFUSION['backup_path']) ? $_GLFUSION['backup_path'] : '',
        'data_path'         => isset($_GLFUSION['data_path']) ? $_GLFUSION['data_path'] : '',
        'step_heading'      => $LANG_INSTALL['system_path'],
        'lang_next'         => $LANG_INSTALL['next'],
        'lang_prev'         => $LANG_INSTALL['previous'],
        'lang_sys_path_help'=> sprintf($LANG_INSTALL['system_path_prompt'],$htmlpath),
        'lang_path_prompt'  => $LANG_INSTALL['path_prompt'],
        'lang_advanced_settings' => $LANG_INSTALL['advanced_settings'],
        'lang_log_path'     => $LANG_INSTALL['log_path'],
        'lang_lang_path'    => $LANG_INSTALL['lang_path'],
        'lang_backup_path'  => $LANG_INSTALL['backup_path'],
        'lang_data_path'    => $LANG_INSTALL['data_path'],
        'hiddenfields'      => _buildHiddenFields(),
    ));
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}



/*
 * Validate the dbconfig_path
 */

/**
 * Validate private/ directory path.
 *
 * Check to see if db-config.php or db-config.php.dist exist
 * in the specified directory.
 *
 * @note   This will return _displayError() if files not found
 *         or will call INST_checkEnvironment() to validate
 *         directory and file permissions.
 *
 * @return  string          HTML
 *
 */
function INST_gotPathSetting($dbc_path = '')
{
    global $_GLFUSION;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $_GLFUSION['currentstep'] = 'pathsetting';

    // initialize the advanced paths to empty

    $log_path    = '';
    $lang_path   = '';
    $backup_path = '';
    $data_path   = '';

    // was it passed from the previous step, or via $_POST?
    if ( $dbc_path == '' ) {
        $dbconfig_path = INST_sanitizePath(INST_stripslashes($_POST['private_path']));
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
    $_GLFUSION['dbconfig_path'] = $dbconfig_path;

    // check and see if the advanced path settings were entered...

    if ( isset($_POST['logpath']) && $_POST['logpath'] != '') {
        $log_path = INST_sanitizePath(INST_stripslashes($_POST['logpath']));
        if (!preg_match('/^.*\/$/', $log_path)) {
            $log_path .= '/';
        }
        $_GLFUSION['log_path']      = $log_path;
    }

    if ( isset($_POST['langpath']) && $_POST['langpath'] != '') {
        $lang_path = INST_sanitizePath(INST_stripslashes($_POST['langpath']));
        if (!preg_match('/^.*\/$/', $lang_path)) {
            $lang_path .= '/';
        }
        $_GLFUSION['lang_path']     = $lang_path;
    }

    if ( isset($_POST['backuppath']) && $_POST['backuppath'] != '') {
        $backup_path = INST_sanitizePath(INST_stripslashes($_POST['backuppath']));
        if (!preg_match('/^.*\/$/', $backup_path)) {
            $backup_path .= '/';
        }
        $_GLFUSION['backup_path']   = $backup_path;
    }

    if ( isset($_POST['datapath']) && $_POST['datapath'] != '') {
        $data_path = INST_sanitizePath(INST_stripslashes($_POST['datapath']));
        if (!preg_match('/^.*\/$/', $data_path)) {
            $data_path .= '/';
        }
        $_GLFUSION['data_path']     = $data_path;
    }

    // now, lets see if it exists, if not, try to rename the .dist file...
    clearstatcache();
    if (!@file_exists($dbconfig_path.'db-config.php') ) {
        // see if the .dist is there
        if ( @file_exists($dbconfig_path.'db-config.php.dist') ) {
            // found it, try to rename..
            $rc = @copy($dbconfig_path.'db-config.php.dist',$dbconfig_path.'db-config.php');
            if ( $rc !== true ) {
                return _displayError(DBCONFIG_NOT_WRITABLE,'pathsetting');
            }
            @chmod($dbconfig_path.'db-config.php',0777);
        } else {
            return _displayError(DBCONFIG_NOT_FOUND,'pathsetting');
        }
    }
    clearstatcache();
    // if it isn't there, ask again...
    if ( !@file_exists($dbconfig_path.'db-config.php') ) {
        return _displayError(DBCONFIG_NOT_FOUND,'pathsetting');
    }
    // found it, but it is read-only...
    if ( !INST_isWritable($dbconfig_path.'db-config.php') ) {
        return _displayError(DBCONFIG_NOT_WRITABLE,'pathsetting');
    }

    /* now set the other paths */

    if ( $log_path == '' ) {
        $log_path = $dbconfig_path .'logs/';
    }

    if ( $lang_path == '') {
        $lang_path = $dbconfig_path .'language/';
    }

    if ( $backup_path == '' ) {
        $backup_path = $dbconfig_path .'backups/';
    }
    if ( $data_path == '' ) {
        $data_path = $dbconfig_path .'data/';
    }

    $_GLFUSION['log_path']      = $log_path;
    $_GLFUSION['lang_path']     = $lang_path;
    $_GLFUSION['backup_path']   = $backup_path;
    $_GLFUSION['data_path']     = $data_path;

    // we have a good path to /private, off to the next step...
    return INST_checkEnvironment($dbconfig_path);
}

/**
 * Check PHP settings and path permissions
 *
 * Validates the PHP settings will support glFusion and also
 * checks for proper permissions on the file system.
 *
 * @return  string          HTML screen with environment status
 *
 */
function INST_checkEnvironment($dbconfig_path='')
{
    global $_GLFUSION, $LANG_INSTALL, $_DB, $_DB_host, $_DB_name, $_DB_user,
           $_DB_pass,$_DB_table_prefix,$_DB_dbms, $_TABLES, $_SYSTEM;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $required_extensions = array(
        array('extension' => 'ctype',   'fail' => 1),
        array('extension' => 'curl',    'fail' => 0),
        array('extension' => 'date',    'fail' => 1),
        array('extension' => 'filter',  'fail' => 1),
        array('extension' => 'gd',      'fail' => 0),
        array('extension' => 'gettext', 'fail' => 0),
        array('extension' => 'hash',    'fail' => 0),
        array('extension' => 'json',    'fail' => 1),
        array('extension' => 'mbstring','fail' => 0),
        array('extension' => 'openssl', 'fail' => 0),
        array('extension' => 'session', 'fail' => 1),
        array('extension' => 'xml',     'fail' => 1),
        array('extension' => 'zlib',    'fail' => 1)
    );

    $_GLFUSION['currentstep'] = 'checkenvironment';

    $previousaction = 'pathsetting';

    // was it passed from the previous step
    if ( $dbconfig_path == '') {
        if ( !isset($_GLFUSION['dbconfig_path']) ) {
            return INST_getPathSetting();
        }
        $dbconfig_path = $_GLFUSION['dbconfig_path'];
    }

    $permError = 0;
    $envError  = 0;
    $envWarning = 0;

    $T = new TemplateLite('templates/');
    $T->set_file('page','checkenvironment.thtml');

    $T->set_var('step_heading',$LANG_INSTALL['hosting_env']);

    /*
     * First we will validate the general environment..
     */

    $T->set_block('page','extensions','extension');

    foreach ( $required_extensions AS $extension ) {
        $available = extension_loaded($extension['extension']);
// test fail condition
//if ( $extension['extension'] == 'mbstring' || $extension['extension'] == 'ctype') $available = 0;
//$available = 0;
        if ( $available != 1 && $extension['fail'] ) {
            $envError = 1;
            $color = "uk-text-danger";
        } else if ( $available != 1 && $extension['fail'] == 0 ) {
            $envWarning = 1;
            $color = "uk-text-warning";
        }
        if ( $available != 1 ) {
            $T->set_var('item',$LANG_INSTALL[$extension['extension'].'_extension']);
            $T->set_var('status',$available == 1 ? '<span class="uk-text-success">'.$LANG_INSTALL['ext_installed'].'</span>' : '<span class="'.$color.' uk-text-bold">'.$LANG_INSTALL['ext_missing'].'</span>');

            if ( $extension['fail'] == 1 ) {
                $msg = ' '.$LANG_INSTALL['ext_required_desc'];
            } else {
                $msg = ' '. $LANG_INSTALL['ext_optional_desc'];
            }
            $T->set_var('notes', $LANG_INSTALL[$extension['extension'].'_extension'] . $msg);
            $T->set_var('recommended',$extension['fail'] == 1 ? $LANG_INSTALL['ext_required'] : $LANG_INSTALL['ext_optional']);
            $T->parse('extension','extensions',true);

        } else {
            $msg = ' '. $LANG_INSTALL['ext_good'];
        }
    }

    if ( !extension_loaded('pdo_mysql') && !function_exists('mysql_connect') && !function_exists('mysqli_connect') ) {
        $envError = 1;
        $color = "uk-text-danger";
        $T->set_var('item',$LANG_INSTALL['mysql_extension']);
        $T->set_var('status','<span class="'.$color.' uk-text-bold">'.$LANG_INSTALL['ext_missing'].'</span>');
        $msg = ' '.$LANG_INSTALL['ext_required_desc'];
        $T->set_var('notes', $LANG_INSTALL['mysql_extension'] . $msg);
        $T->set_var('recommended',$LANG_INSTALL['ext_required']);
        $T->parse('extension','extensions',true);
    }

    if ( $envError == 0 && $envWarning == 0) {
        $T->set_var('item',$LANG_INSTALL['required_php_ext']);
        $T->set_var('status','<span class="uk-text-success">'.$LANG_INSTALL['ext_installed'].'</span>');
        $T->set_var('notes', $LANG_INSTALL['all_ext_present']);
        $T->set_var('recommended','');
        $T->parse('extension','extensions',true);
    }

    $T->set_block('page','envs','env');

    // PHP Version

    $T->set_var('item',$LANG_INSTALL['php_version']);

    if ( INST_phpOutOfDate() ) {
        $T->set_var('status','<span class="uk-text-danger uk-text-bold">'.phpversion().'</span>');
    } else {
        $T->set_var('status','<span class="uk-text-success">'.phpversion().'</span>');
    }
    $T->set_var('recommended','7.1+');
    $T->set_var('notes',$LANG_INSTALL['php_req_version']);
    $T->parse('env','envs',true);

    $st = ini_get('short_open_tag');
    $T->set_var('item','short_open_tag');
    $T->set_var('status',$st == 1 ? '<span class="uk-text-danger uk-text-bold">'.$LANG_INSTALL['on'].'</span>' : '<span class="uk-text-success">'.$LANG_INSTALL['off'].'</span>');
    $T->set_var('recommended',$LANG_INSTALL['off']);
    $T->set_var('notes',$LANG_INSTALL['short_open_tags']);
    $T->parse('env','envs',true);

    if (version_compare(PHP_VERSION,'7.1.0','<')) {
        $ob = ini_get('open_basedir');
        if ( $ob == '' ) {
            $open_basedir_restriction = 0;
        } else {
            $open_basedir_restriction = 1;
            $open_basedir_directories = $ob;
        }
        $T->set_var('item','open_basedir');
        $T->set_var('status',$ob == '' ? '<span class="uk-text-success">'.$LANG_INSTALL['none'].'</span>' : '<span class="uk-text-danger uk-text-bold">'.$LANG_INSTALL['enabled'].'</span>');
        $T->set_var('notes',$LANG_INSTALL['open_basedir']);
        $T->parse('env','envs',true);
    }
    $memory_limit = INST_return_bytes(ini_get('memory_limit'));
    $memory_limit_print = ($memory_limit / 1024) / 1024;
    $T->set_var('item','memory_limit');
    $T->set_var('status',$memory_limit < 50331648 ? '<span class="uk-text-danger uk-text-bold">'.$memory_limit_print.'M</span>' : '<span class="uk-text-success">'.$memory_limit_print.'M</span>');
    $T->set_var('recommended','64M');
    $T->set_var('notes',$LANG_INSTALL['memory_limit']);
    $T->parse('env','envs',true);

    $fu = ini_get('file_uploads');
    $T->set_var('item','file_uploads');
    $T->set_var('status',$fu == 1 ? '<span class="uk-text-success">'.$LANG_INSTALL['on'].'</span>' : '<span class="uk-text-danger uk-text-bold">'.$LANG_INSTALL['off'].'</span>');
    $T->set_var('recommended',$LANG_INSTALL['on']);
    $T->set_var('notes',$LANG_INSTALL['file_uploads']);
    $T->parse('env','envs',true);

    $upload_limit = INST_return_bytes(ini_get('upload_max_filesize'));
    $upload_limit_print = ($upload_limit / 1024) / 1024;
    $T->set_var('item','upload_max_filesize');
    $T->set_var('status',$upload_limit < 8388608 ? '<span class="uk-text-danger uk-text-bold">'.$upload_limit_print.'M</span>' : '<span class="uk-text-success">'.$upload_limit_print.'M</span>');
    $T->set_var('recommended','8M');
    $T->set_var('notes',$LANG_INSTALL['upload_max_filesize']);
    $T->parse('env','envs',true);

    $post_limit = INST_return_bytes(ini_get('post_max_size'));
    $post_limit_print = ($post_limit / 1024) / 1024;
    $T->set_var('item','post_max_size');
    $T->set_var('status',$post_limit < 8388608 ? '<span class="uk-text-danger uk-text-bold">'.$post_limit_print.'M</span>' : '<span class="uk-text-success">'.$post_limit_print.'M</span>');
    $T->set_var('recommended','8M');
    $T->set_var('notes',$LANG_INSTALL['post_max_size']);
    $T->parse('env','envs',true);

    $max_execution_time = ini_get('max_execution_time');
    $T->set_var('item', 'max_execution_time');
    $T->set_var('status', $max_execution_time < 30 ? '<span class="uk-text-danger uk-text-bold">'.$max_execution_time . ' secs</span>' : '<span class="uk-text-success">'.$max_execution_time . ' secs</span>');
    $T->set_var('recommended', '30 secs');
    $T->set_var('notes',$LANG_INSTALL['max_execution_time']);
    $T->parse('env','envs',true);

    clearstatcache();
    if ( $_GLFUSION['method'] == 'upgrade' && @file_exists('../../siteconfig.php')) {
        require '../../siteconfig.php';
        $_GLFUSION['dbconfig_path'] = $_CONF['path'];
        if ( !file_exists($_CONF['path'].'db-config.php') ) {
            return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error code: ' . __LINE__);
        }
        require $_CONF['path'].'db-config.php';
        if ( !file_exists($_CONF['path_system'].'lib-database.php') ) {
            return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error code: ' .  __LINE__);
        }
        require_once $_CONF['path_system'] . 'classes/Autoload.php';
        glFusion\Autoload::initialize();

        require_once $_CONF['path'].'system/db-init.php';

        if ( !file_exists($_CONF['path_system'].'classes/config.class.php') ) {
            return _displayError(FILE_INCLUDE_ERROR,'pathsetting', 'Error code: ' . __LINE__);
        }
        require_once $_CONF['path_system'].'classes/config.class.php';
        $config = config::get_instance();

        $config->load_baseconfig();
        $config->initConfig();
        $_CONF = $config->get_config('Core');
        $_PATH['public_html']   = $_CONF['path_html'];
        $_PATH['dbconfig_path'] = $_CONF['path'];
        $_PATH['admin_path']    = INST_getAdminPath();
        $_PATH['log_path']      = $_CONF['path_log'];
        $_PATH['lang_path']     = $_CONF['path_language'];
        $_PATH['backup_path']   = $_CONF['backup_path'];
        $_PATH['data_path']     = $_CONF['path_data'];
    } else {
        $_PATH['public_html']   = INST_getHtmlPath();
        if ( $dbconfig_path == '' ) {
            $_PATH['dbconfig_path'] = INST_sanitizePath(INST_stripslashes($_POST['private_path']));
        } else {
            $_PATH['dbconfig_path']     = $dbconfig_path;
        }
        $_PATH['admin_path']        = INST_getAdminPath();

        $_PATH['log_path']      = $_GLFUSION['log_path'];
        $_PATH['lang_path']     = $_GLFUSION['lang_path'];
        $_PATH['backup_path']   = $_GLFUSION['backup_path'];
        $_PATH['data_path']     = $_GLFUSION['data_path'];
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

    $file_list = array( /*$_PATH['dbconfig_path'],*/
                        $_PATH['dbconfig_path'].'db-config.php',
                        $_PATH['data_path'],
                        $_PATH['data_path'].'glfusion.lck',
                        $_PATH['data_path'].'glfusion_css.lck',
                        $_PATH['data_path'].'glfusion_js.lck',
                        $_PATH['log_path'].'error.log',
                        $_PATH['log_path'].'access.log',
                        $_PATH['log_path'].'captcha.log',
                        $_PATH['log_path'].'spamx.log',
                        $_PATH['log_path'].'404.log',
                        $_PATH['data_path'].'cache/',
                        $_PATH['data_path'].'layout_cache/',
                        $_PATH['data_path'].'temp/',
                        $_PATH['data_path'].'htmlpurifier/',
                        $_PATH['dbconfig_path'].'plugins/mediagallery/',
                        $_PATH['dbconfig_path'].'plugins/mediagallery/tmp/',
                        $_PATH['dbconfig_path'].'system/lib-custom.php',

                        $_PATH['public_html'],
                        $_PATH['public_html'].'siteconfig.php',
                        $_PATH['public_html'].'backend/glfusion.rss',
                        $_PATH['public_html'].'images/articles/',
                        $_PATH['public_html'].'images/topics/',
                        $_PATH['public_html'].'images/userphotos/',
                        $_PATH['public_html'].'images/library/File/',
                        $_PATH['public_html'].'images/library/Flash/',
                        $_PATH['public_html'].'images/library/Image/',
                        $_PATH['public_html'].'images/library/Media/',

                        $_PATH['public_html'].'mediagallery/mediaobjects/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/covers/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/orig/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/disp/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/tn/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/orig/0/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/disp/0/',
                        $_PATH['public_html'].'mediagallery/mediaobjects/tn/0/',
                        $_PATH['public_html'].'mediagallery/watermarks/',

                        $_PATH['public_html'].'filemgmt_data/',
                        $_PATH['public_html'].'filemgmt_data/category_snaps/',
                        $_PATH['public_html'].'filemgmt_data/category_snaps/tmp/',
                        $_PATH['public_html'].'filemgmt_data/files/',
                        $_PATH['public_html'].'filemgmt_data/files/tmp/',
                        $_PATH['public_html'].'filemgmt_data/snaps/',
                        $_PATH['public_html'].'filemgmt_data/snaps/tmp/',

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
            $T->set_var('status', $ok ? '<span class="uk-text-success">'.$LANG_INSTALL['ok'].'</span>' : '<span class="uk-text-danger">'.$LANG_INSTALL['not_writable'].'</span>');
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
        $T->set_var('location',$_PATH['dbconfig_path'].'data/layout_cache/<br /><strong>'.$_GLFUSION['errstr'].'</strong>');
        $T->set_var('status', '<span class="Unwriteable">'.$LANG_INSTALL['unable_mkdir'].'</span>');
        $T->set_var('rowclass',($classCounter % 2)+1);
        $classCounter++;
        $T->parse('perm','perms',true);

        $permError = 1;
        @rmdir($_PATH['dbconfig_path'].'data/layout_cache/test/');
    } else {
        $ok = INST_isWritable($_PATH['dbconfig_path'].'data/layout_cache/test/');
        if ( !$ok ) {
            $T->set_var('location',$path);
            $T->set_var('status', $ok ? '<span class="uk-text-success">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
            $T->set_var('rowclass',($classCounter % 2)+1);
            $classCounter++;
            $T->parse('perm','perms',true);
            $permError = 1;
        }
        @rmdir($_PATH['dbconfig_path'].'data/layout_cache/test/');
    }

    // special test to see if existing cache files exist and are writable...
    $rc = INST_checkCacheDir($_PATH['dbconfig_path'].'data/layout_cache/',$T,$classCounter);
    if ( $rc > 0 ) {
        $permError = 1;
    }

    if ( $permError == 0 ) {
        $T->set_var('location',$LANG_INSTALL['directory_permissions']);
        $T->set_var('status', 1 ? '<span class="uk-text-success">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
        $classCounter++;
        $T->parse('perm','perms',true);
        $T->set_var('location',$LANG_INSTALL['file_permissions']);
        $T->set_var('status', 1 ? '<span class="uk-text-success">'.$LANG_INSTALL['ok'].'</span>' : '<span class="Unwriteable">'.$LANG_INSTALL['not_writable'].'</span>');
        $classCounter++;
        $T->parse('perm','perms',true);
    }

    $T->set_var('icon','arrow-right');

    if ( $permError || $envError ) {
        $button = 'Recheck';
        $action = 'checkenvironment';
    }

    if ( $permError || $envError) {
        $T->set_var('error_message','<div class="uk-alert uk-alert-danger">'.$LANG_INSTALL['correct_perms'].'</div>');
        $T->set_var('icon','repeat');
        $recheck = '';
    }

    if ( $permError == 0 && $envError == 0 ) {
        $recheck = '';
        $button = $LANG_INSTALL['next'];

        if ( $_GLFUSION['method'] == 'upgrade' ) {
            $action = 'doupgrade';
            $button = $LANG_INSTALL['upgrade'];
            $previousaction = 'upgradealert';
        } else {
            $action = 'getsiteinformation';
            $previousaction = 'pathsetting';
            $button = $LANG_INSTALL['next'];
        }
    }

    $T->set_var(array(
        'previousaction'    => $previousaction,
        'nextaction'        => $action,
        'button'            => $button,
        'recheck'           => $recheck,
        'back_to_top'       => $LANG_INSTALL['back_to_top'],
        'lang_previous'     => $LANG_INSTALL['previous'],
        'lang_host_env'     => $LANG_INSTALL['hosting_env'],
        'lang_setting'      => $LANG_INSTALL['setting'],
        'lang_current'      => $LANG_INSTALL['current'],
        'lang_recommended'  => $LANG_INSTALL['recommended'],
        'lang_notes'        => $LANG_INSTALL['notes'],
        'lang_filesystem'   => $LANG_INSTALL['filesystem_check'],
        'lang_php_settings' => $LANG_INSTALL['php_settings'],
        'lang_php_warning'  => $LANG_INSTALL['php_warning'],
        'lang_ext_heading'  => $LANG_INSTALL['ext_heading'],
        'lang_extension'    => $LANG_INSTALL['extension'],
        'lang_status'       => $LANG_INSTALL['status'],
        'hiddenfields'      => _buildHiddenFields(),
        'percent_complete'      => '20',
    ));
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}

/**
 * Retrieves database settings and site information
 *
 * Prompt the user for the database setting and site
 * information such as site name, url, etc.
 *
 * @return  string          HTML form
 *
 */
function INST_getSiteInformation()
{
    global $_GLFUSION, $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    if ( !isset($_GLFUSION['dbconfig_path']) ) {
        return INST_getPathSetting();
    }

    $_GLFUSION['currentstep'] = 'getsiteinformation';


    $T = new TemplateLite('templates/');
    $T->set_file('page','siteinformation.thtml');

    $site_name      = (isset($_GLFUSION['site_name']) ? $_GLFUSION['site_name'] : '');
    $site_slogan    = (isset($_GLFUSION['site_slogan']) ? $_GLFUSION['site_slogan'] : '');
    $site_url       = (isset($_GLFUSION['site_url']) ? $_GLFUSION['site_url'] : INST_getSiteUrl());
    $site_admin_url = (isset($_GLFUSION['site_admin_url']) ? $_GLFUSION['site_admin_url'] : INST_getSiteAdminUrl());
    $site_mail      = (isset($_GLFUSION['site_mail']) ? $_GLFUSION['site_mail'] : '');
    $noreply_mail   = (isset($_GLFUSION['noreply_mail']) ? $_GLFUSION['noreply_mail'] : '');
    $securePassword = (isset($_GLFUSION['securepassword']) ? $_GLFUSION['securepassword'] : '');
    $utf8           = (isset($_GLFUSION['utf8']) ? $_GLFUSION['utf8'] : 1);
    $dbconfig_path  = $_GLFUSION['dbconfig_path'];

    if ( $securePassword == '' ) {
        $securePassword = INST_securePassword(15);
        $_GLFUSION['securepassword'] = $securePassword;
    }

    clearstatcache();
    if ( !file_exists($dbconfig_path.'db-config.php') ) {
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
    }
    require $dbconfig_path.'db-config.php';

    if ( isset($_GLFUSION['db_type']) ) {
        $_DB_dbms = $_GLFUSION['db_type'];
    }
    if ( isset($_GLFUSION['db_host']) ) {
        $_DB_host = $_GLFUSION['db_host'];
    }
    if ( isset($_GLFUSION['db_name']) ) {
        $_DB_name = $_GLFUSION['db_name'];
    }
    if ( isset( $_GLFUSION['db_user']) ) {
        $_DB_user = $_GLFUSION['db_user'];
    }
    if ( isset( $_GLFUSION['db_pass']) ) {
        $_DB_pass = $_GLFUSION['db_pass'];
    } else {
        $_DB_pass = '';
    }
    if ( isset( $_GLFUSION['db_prefix']) ) {
        $_DB_table_prefix = $_GLFUSION['db_prefix'];
    }

    if ( isset($_GLFUSION['innodb']) && $_GLFUSION['innodb'] ) {
        $T->set_var('innodb_selected',' selected="selected"');
        $T->set_var('noinnodb_selected','');
        $T->set_var('mysqli_selected','');
    } elseif (isset($_GLFUSION['db_type']) && $_GLFUSION['db_type'] == 'mysqli' ) {
        $T->set_var('mysqli_selected',' selected="selected"');
        $T->set_var('innodb_selected','');
        $T->set_var('noinnodb_selected','');
    } else {
        $T->set_var('noinnodb_selected', '');
        $T->set_var('innodb_selected','');
        $T->set_var('mysqli_selected',' selected="selected"');
    }

    $T->set_var(array(
        'back_to_top'                   => $LANG_INSTALL['back_to_top'],
        'db_type'                       => $_DB_dbms,
        'db_host'                       => $_DB_host,
        'db_name'                       => $_DB_name,
        'db_user'                       => $_DB_user,
        'db_pass'                       => $_DB_pass,
        'db_prefix'                     => $_DB_table_prefix,
        'lang_database_type'            => $LANG_INSTALL['db_type'],
        'lang_database_hostname'        => $LANG_INSTALL['db_hostname'],
        'lang_database_name'            => $LANG_INSTALL['db_name'],
        'lang_database_user'            => $LANG_INSTALL['db_user'],
        'lang_database_password'        => $LANG_INSTALL['db_pass'],
        'lang_database_table_prefix'    => $LANG_INSTALL['db_table_prefix'],
        'lang_connection_settings'      => $LANG_INSTALL['connection_settings'],
        'site_name'                     => $site_name,
        'site_slogan'                   => $site_slogan,
        'site_url'                      => $site_url,
        'site_admin_url'                => $site_admin_url,
        'site_mail'                     => $site_mail,
        'noreply_mail'                  => $noreply_mail,
        'securepassword'                => $securePassword,
        'lang_adminuser'                => $LANG_INSTALL['adminuser'],
        'lang_next'                     => $LANG_INSTALL['next'],
        'lang_prev'                     => $LANG_INSTALL['previous'],
        'lang_install'                  => $LANG_INSTALL['install'],
        'lang_site_information'         => $LANG_INSTALL['site_info'],
        'lang_site_name'                => $LANG_INSTALL['site_name'],
        'lang_site_slogan'              => $LANG_INSTALL['site_slogan'],
        'lang_site_url'                 => $LANG_INSTALL['site_url'],
        'lang_site_admin_url'           => $LANG_INSTALL['site_admin_url'],
        'lang_site_email'               => $LANG_INSTALL['site_email'],
        'lang_site_noreply_email'       => $LANG_INSTALL['site_noreply_email'],
        'lang_securepassword'           => $LANG_INSTALL['securepassword'],
        'lang_utf8'                     => $LANG_INSTALL['use_utf8'],
        'lang_sitedata_help'            => $LANG_INSTALL['sitedata_help'],
        'hiddenfields'                  => _buildHiddenFields(),
    ));

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


/**
 * Validates database and site settings.
 *
 * Checks to ensure connectivity to the database and that
 * the email fields are properly formed email addresses.
 *
 * @return  string          HTML screen with environment status
 *
 * @note This will return _displayError() if there are data issues.
 *       otherwise it calls INST_installAndContentPlugins()
 *
 */
function INST_gotSiteInformation()
{
    global $_GLFUSION, $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }
    $_GLFUSION['currentstep'] = 'getsiteinformation';

    require_once $_GLFUSION['dbconfig_path'] . 'system/classes/Autoload.php';
    glFusion\Autoload::initialize();

    $dbconfig_path = $_GLFUSION['dbconfig_path'];
    $log_path = $dbconfig_path .'logs/';
    clearstatcache();
    if ( !file_exists($dbconfig_path.'vendor/aziraphale/email-address-validator/EmailAddressValidator.php') ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $dbconfig_path.'lib/email-address-validation/EmailAddressValidator.php');
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
    }
    $validator = new EmailAddressValidator;

    $numErrors = 0;
    $errText   = '';

    if ( isset($_POST['dbtype']) && $_POST['dbtype'] != '') {
        $db_type = INST_stripslashes($_POST['dbtype']);
    } else {
        $db_type = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_type_error'].'<br />';
    }
    if ( isset($_POST['dbhost']) && $_POST['dbhost'] != '') {
        $db_host = INST_stripslashes($_POST['dbhost']);
    } else {
        $db_host = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_hostname_error'].'<br />';
    }
    if ( isset($_POST['dbname']) && $_POST['dbname'] != '') {
        $db_name = INST_stripslashes($_POST['dbname']);
    } else {
        $db_name = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_name_error'].'<br />';
    }
    if ( isset($_POST['dbuser']) && $_POST['dbuser'] != '') {
        $db_user = INST_stripslashes($_POST['dbuser']);
    } else {
        $db_user = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['db_user_error'].'<br />';
    }
    if ( isset($_POST['dbpass']) && $_POST['dbpass'] != '') {
        $db_pass = INST_stripslashes($_POST['dbpass']);
    } else {
        $db_pass = '';
    }
    if ( isset($_POST['dbprefix']) ) {
        $db_prefix = INST_stripslashes($_POST['dbprefix']);
    } else {
        $db_prefix = '';
    }

    $innodb = false;
    switch ($db_type) {
        case 'mysql-innodb':
            $innodb = true;
            $db_type = 'mysql';
        case 'mysql' :
        case 'mysqli' :
            $db_type = 'mysql';
            break;
    }

    // populate the session vars...

    $_GLFUSION['db_type']     = $db_type;
    $_GLFUSION['innodb']      = $innodb;
    $_GLFUSION['db_host']     = $db_host;
    $_GLFUSION['db_name']     = $db_name;
    $_GLFUSION['db_user']     = $db_user;
    $_GLFUSION['db_pass']     = $db_pass;
    $_GLFUSION['db_prefix']   = $db_prefix;

    if ( isset($_POST['sitename']) && $_POST['sitename'] != '' ) {
        $site_name = INST_stripslashes($_POST['sitename']);
    } else {
        $site_name = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_name_error'].'<br />';
    }
    if ( isset($_POST['siteslogan']) && $_POST['siteslogan'] != '' ) {
        $site_slogan = INST_stripslashes($_POST['siteslogan']);
    } else {
        $site_slogan = '';
    }
    if ( isset($_POST['siteurl']) && $_POST['siteurl'] != '' ) {
        $site_url = INST_stripslashes($_POST['siteurl']);
    } else {
        $site_url = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_url_error'].'<br />';
    }

    if ( isset($_POST['siteadminurl']) && $_POST['siteadminurl'] != '' ) {
        $site_admin_url = INST_stripslashes($_POST['siteadminurl']);
    } else {
        $site_admin_url = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_admin_url_error'].'<br />';
    }
    if ( isset($_POST['sitemail']) && $_POST['sitemail'] != '' ) {
        $site_mail = INST_stripslashes($_POST['sitemail']);
        if ( !$validator->checkEmailAddress( $site_mail ) ) {
            $numErrors++;
            $errText .= $LANG_INSTALL['site_email_notvalid'].'<br />';
        }
    } else {
        $site_mail = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_email_error'].'<br />';
    }
    if ( isset($_POST['noreplymail']) && $_POST['noreplymail'] != '' ) {
        $noreply_mail = INST_stripslashes($_POST['noreplymail']);
        if ( !$validator->checkEmailAddress( $noreply_mail ) ) {
            $numErrors++;
            $errText .= $LANG_INSTALL['site_noreply_notvalid'].'<br />';
        }
    } else {
        $noreply_mail = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['site_noreply_email_error'].'<br />';
    }
    if ( isset($_POST['securepassword']) && $_POST['securepassword'] != '' ) {
        $securePassword = $_POST['securepassword'];
    } else {
        $securePassword = '';
        $numErrors++;
        $errText .= $LANG_INSTALL['securepassword_error'].'<br>';
    }

    $_GLFUSION['site_name']       = $site_name;
    $_GLFUSION['site_slogan']     = $site_slogan;
    $_GLFUSION['site_url']        = $site_url;
    $_GLFUSION['site_admin_url']  = $site_admin_url;
    $_GLFUSION['site_mail']       = $site_mail;
    $_GLFUSION['noreply_mail']    = $noreply_mail;
    $_GLFUSION['securepassword']  = $securePassword;
    $_GLFUSION['utf8']            = isset($_POST['use_utf8']) ? 1 : 0;

    if ( $numErrors > 0 ) {
        return _displayError(SITE_DATA_ERROR,'getsiteinformation',$errText);
    }

    INST_errorLog($log_path,'INSTALL: Validating MySQL drivers are present in PHP');

    if ( !extension_loaded('pdo_mysql') && !function_exists('mysql_connect') && !function_exists('mysqli_connect') ) {
        INST_errorLog($log_path,'INSTALL: ERROR: No MySQL drivers found');
        return _displayError(NO_DB_DRIVER,'getsiteinformation');
    }

    if (extension_loaded('pdo_mysql')) {
        $driver = 'pdo_mysql';
    } else if (class_exists('MySQLi')) {
        $driver = 'mysqli';
    } else {
        die("No Suitable driver found in PHP environment.");
    }

    if ($_GLFUSION['utf8']) {
        $charset = 'utf8';
    } else {
        $charset = 'latin1';
    }

    $config = new \Doctrine\DBAL\Configuration();
    $dsn = 'mysql:dbname='.$db_name.';host='.$db_host;
    $connectionParams = array(
        'dbname'    => $db_name,
        'user'      => $db_user,
        'password'  => $db_pass,
        'host'      => $db_host,
        'driver'    => $driver,
        'charset'   => $charset,
    );

    if ($charset === 'utf8') {
        $connectionParams['driverOptions'] = [1002 => "SET NAMES 'UTF8'"];
    }

    try {
        $db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    } catch(\Doctrine\DBAL\DBALException | PDOException $e) {
        return _displayError(DB_NO_CONNECT,'getsiteinformation',$e->getMessage());
    }
    $db->setFetchMode( \Doctrine\DBAL\FetchMode::MIXED );

    try {
        $mysqlVersion = $db->getWrappedConnection()->getServerVersion();
    } catch(\Doctrine\DBAL\DBALException | PDOException $e) {
        return _displayError(DB_NO_CONNECT,'getsiteinformation',$e->getMessage());
    }

    if (!empty($mysqlVersion)) {
        preg_match('/^([0-9]+).([0-9]+).([0-9]+)/', $mysqlVersion, $match);
        $mysqlmajorv = $match[1];
        $mysqlminorv = $match[2];
        $mysqlrev = $match[3];
    } else {
        $mysqlmajorv = 0;
        $mysqlminorv = 0;
        $mysqlrev = 0;
    }
    $mySqlVersionOK = true;
    $minv = explode('.', SUPPORTED_MYSQL_VER);
    if (($mysqlmajorv <  $minv[0]) || (($mysqlmajorv == $minv[0]) && ($mysqlminorv <  $minv[1])) ||
      (($mysqlmajorv == $minv[0]) && ($mysqlminorv == $minv[1]) && ($mysqlrev < $minv[2]))) {
        $mySqlVersionOK = false;
    }
    if ( $mySqlVersionOK === false ) {
        return _displayError(DB_TOO_OLD,'getsiteinformation');
    }

    if ( $innodb ) {
        INST_errorLog($log_path,'INSTALL: Checking MySQL Storage Engines');
        $foundInnoDB = false;
        try {
            $res = @$db->query("SHOW STORAGE ENGINES");
        } catch(\Doctrine\DBAL\DBALException | PDOException $e) {

        }

        while ( ( $A = $res->fetch(\glFusion\Database::ASSOCIATIVE) ) !== false ) {
            if (strcasecmp($A['Engine'], 'InnoDB') == 0) {
                if ((strcasecmp($A['Support'], 'yes') == 0) ||
                    (strcasecmp($A['Support'], 'default') == 0)) {
                    $foundInnoDB = true;
                }
                break;
            }
        }
        if ( $foundInnoDB === false ) {
            return _displayError(DB_NO_INNODB,'getsiteinformation');
        }
    }
    INST_errorLog($log_path,'INSTALL: Checking MySQL Character Set and Collation');
    try {
        $collationResult = $db->query("SELECT @@character_set_database, @@collation_database;");
    } catch(\Doctrine\DBAL\DBALException | PDOException $e) {
        // ignore error
    }
    $collation = @$collationResult->fetch(\glFusion\Database::ASSOCIATIVE);
    $collation_database = $collation["@@collation_database"];
    $character_set = $collation["@@character_set_database"];
    $_GLFUSION['db_charset'] = $character_set;
    if ( $_GLFUSION['utf8'] ) {
        if ( (substr($collation_database,0,4) != "utf8") || (substr($character_set,0,4) != "utf8")  ) {
            return _displayError(DB_NO_UTF8, 'getsiteinformation');
        }
    } else {
        if ( (substr($collation_database,0,4) == "utf8") || (substr($character_set,0,4) == "utf8")  ) {
            return _displayError(DB_NO_CHECK_UTF8, 'getsiteinformation');
        }
    }
    INST_errorLog($log_path,'INSTALL: Checking for existing glFusion tables');
    $result = @$db->query("SHOW TABLES LIKE '".$db_prefix."vars'");
    if ( $result->rowCount() > 0 ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Found existing glFusion tables');
        return _displayError(DB_EXISTS,'');
    }

    if ( $numErrors > 0 ) {
        return _displayError(SITE_DATA_MISSING,'getsiteinformation',$errText);
    }

    return INST_installAndContentPlugins();
}


/**
 * Performs base site install and prompts for plugin / content install
 *
 * Initializes the database and configuration settings.
 * Prompts user for optional content and plugins to install.
 *
 * @return  string          HTML form
 *
 */
function INST_installAndContentPlugins()
{
    global $_GLFUSION, $_SYSTEM, $_CONF, $_TABLES, $_DB, $_DB_dbms,
           $_DB_host, $_DB_user,$_DB_pass, $site_url,$_DB_table_prefix,
           $LANG_INSTALL;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $_GLFUSION['currentstep'] = 'contentplugins';

    if ( isset($_GLFUSION['innodb']) ) {
        $use_innodb = $_GLFUSION['innodb'];
    } else {
        $use_innodb = false;
    }

    $utf8 = (isset($_GLFUSION['utf8']) ? $_GLFUSION['utf8'] : 1);
    if ( $utf8 ) {
        $charset = 'utf-8';
    } else {
        $charset = 'iso-8859-1';
    }

    if ( isset($_GLFUSION['language']) ) {
        $language = $_GLFUSION['language'];
    } else {
        $language = 'english_utf-8';
    }

    $_PATH['dbconfig_path'] = $_GLFUSION['dbconfig_path'];
    $_PATH['public_html']   = INST_getHtmlPath();
    if (!preg_match('/^.*\/$/', $_PATH['public_html'])) {
        $_PATH['public_html'] .= '/';
    }
    $dbconfig_path = str_replace('db-config.php', '', $_PATH['dbconfig_path']);
    $gl_path    = $dbconfig_path;
    $log_path   = isset($_GLFUSION['log_path'])    ? $_GLFUSION['log_path']    : $gl_path . 'logs/';

    INST_errorLog($log_path,'INSTALL: lib-custom installation');
    clearstatcache();
    // check the lib-custom...
    if (!@file_exists($_PATH['dbconfig_path'].'system/lib-custom.php') ) {
        if ( @file_exists($_PATH['dbconfig_path'].'system/lib-custom.php.dist') ) {
            INST_errorLog($log_path,'INSTALL: Creating ' . $_PATH['dbconfig_path'].'system/lib-custom.php');
            $rc = @copy($_PATH['dbconfig_path'].'system/lib-custom.php.dist',$_PATH['dbconfig_path'].'system/lib-custom.php');
            if ( $rc === false ) {
                INST_errorLog($log_path,'INSTALL: ERROR: Unable to create lib-custom.php - directory not writable?');
                return _displayError(LIBCUSTOM_NOT_WRITABLE,'getsiteinformation');
            }
        } else {
            // no lib-custom.php.dist found
            INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $_PATH['dbconfig_path'].'system/lib-custom.php.dist');
            return _displayError(LIBCUSTOM_NOT_FOUND,'getsiteinformation');
        }
    }

    // check the mg config...
    INST_errorLog($log_path,'INSTALL: Media Gallery config.php installation.');
    if (!@file_exists($_PATH['dbconfig_path'].'plugins/mediagallery/config.php') ) {
        if ( @file_exists($_PATH['dbconfig_path'].'plugins/mediagallery/config.php.dist') ) {
            INST_errorLog($log_path,'INSTALL: Creating ' . $_PATH['dbconfig_path'].'plugins/mediagallery/config.php');
            $rc = @copy($_PATH['dbconfig_path'].'plugins/mediagallery/config.php.dist',$_PATH['dbconfig_path'].'plugins/mediagallery/config.php');
            if ( $rc === false ) {
                INST_errorLog($log_path,'INSTALL: ERROR: Unable to create Media Gallery config.php - directory not writable?');
                return _displayError(LIBCUSTOM_NOT_WRITABLE,'getsiteinformation');
            }
        } else {
            // no config.php.dist found
            INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $_PATH['dbconfig_path'].'plugins/mediagallery/config.php.dist');
            return _displayError(LIBCUSTOM_NOT_FOUND,'getsiteinformation');
        }
    }

    // check and see if site config really exists...
    INST_errorLog($log_path,'INSTALL: siteconfig.php Installation');
    if (!@file_exists($_PATH['public_html'].'siteconfig.php') ) {
        if ( @file_exists($_PATH['public_html'].'siteconfig.php.dist') ) {
            INST_errorLog($log_path,'INSTALL: Creating ' . $_PATH['public_html'].'siteconfig.php');
            $rc = @copy($_PATH['public_html'].'siteconfig.php.dist',$_PATH['public_html'].'siteconfig.php');
            if ( $rc === false ) {
                INST_errorLog($log_path,'INSTALL: ERROR: Unable to create siteconfig.php - directory or file not writable?');
                return _displayError(SITECONFIG_NOT_WRITABLE,'getsiteinformation');
            }
            @chmod($_PATH['public_html'].'siteconfig.php',0777);
            if ( !@file_exists($_PATH['public_html'].'siteconfig.php') ) {
                INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $_PATH['public_html'].'siteconfig.php');
                return _displayError(SITECONFIG_NOT_WRITABLE,'getsiteinformation');
            }
        } else {
            // no site config found return error
            INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $_PATH['public_html'].'siteconfig.php.dist');
            return _displayError(SITECONFIG_NOT_FOUND,'getsiteinformation');
        }
    }

    // Edit siteconfig.php and enter the correct path and system directory path
    INST_errorLog($log_path,'INSTALL: Opening siteconfig.php for writing');
    $siteconfig_path = $_PATH['public_html'] . 'siteconfig.php';
    $siteconfig_file = fopen($siteconfig_path, 'r');
    if ( $siteconfig_file === false ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to open ' . $_PATH['public_html'] . 'siteconfig.php for writing');
        return _displayError(SITECONFIG_NOT_WRITABLE,'getsiteinformation');
    }
    INST_errorLog($log_path,'INSTALL: Writing configuration data to siteconfig.php');
    $siteconfig_data = fread($siteconfig_file, filesize($siteconfig_path));
    fclose($siteconfig_file);

    if ( !file_exists($siteconfig_path) ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate siteconfig.php after configuration update');
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
    }
    require $siteconfig_path;
    $siteconfig_data = str_replace("\$_CONF['path'] = '{$_CONF['path']}';",
                        "\$_CONF['path'] = '" . str_replace('db-config.php', '', $_PATH['dbconfig_path']) . "';",
                        $siteconfig_data);

    $siteconfig_data = preg_replace
            (
             '/\$_CONF\[\'default_charset\'\] = \'[^\']*\';/',
             "\$_CONF['default_charset'] = '" . $charset . "';",
             $siteconfig_data
            );

// put database default character set here

    $siteconfig_data = preg_replace
            (
             '/\$_CONF\[\'db_charset\'\] = \'[^\']*\';/',
             "\$_CONF['db_charset'] = '" . $_GLFUSION['db_charset'] . "';",
             $siteconfig_data
            );


    $siteconfig_file = fopen($siteconfig_path, 'w');
    if (!fwrite($siteconfig_file, $siteconfig_data)) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to write to ' . $siteconfig_path);
        return _displayError(SITECONFIG_NOT_WRITABLE,'getsiteinformation');
    }
    fclose ($siteconfig_file);
    require $siteconfig_path;

    $config_file = $_GLFUSION['dbconfig_path'].'db-config.php';

    if ( !file_exists($config_file) ) {
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
    }
    require $config_file;
    INST_errorLog($log_path,'INSTALL: db-config.php Installation');
    $db = array('host' => (isset($_GLFUSION['db_host']) ? $_GLFUSION['db_host'] : $_DB_host),
                'name' => (isset($_GLFUSION['db_name']) ? $_GLFUSION['db_name'] : $_DB_name),
                'user' => (isset($_GLFUSION['db_user']) ? $_GLFUSION['db_user'] : $_DB_user),
                'pass' => (isset($_GLFUSION['db_pass']) ? $_GLFUSION['db_pass'] : $_DB_pass),
                'table_prefix' => (isset($_GLFUSION['db_prefix']) ? $_GLFUSION['db_prefix'] : $_DB_table_prefix),
                'type' => (isset($_GLFUSION['db_type']) ? $_GLFUSION['db_type'] : $_DB_type));

    $dbconfig_file = fopen($config_file, 'r');
    $dbconfig_data = fread($dbconfig_file, filesize($config_file));
    fclose($dbconfig_file);

    $dbconfig_data = str_replace("\$_DB_host = '" . $_DB_host . "';", "\$_DB_host = '" . $_GLFUSION['db_host'] . "';", $dbconfig_data); // Host
    $dbconfig_data = str_replace("\$_DB_name = '" . $_DB_name . "';", "\$_DB_name = '" . $_GLFUSION['db_name'] . "';", $dbconfig_data); // Database
    $dbconfig_data = str_replace("\$_DB_user = '" . $_DB_user . "';", "\$_DB_user = '" . $_GLFUSION['db_user'] . "';", $dbconfig_data); // Username
    $dbconfig_data = str_replace("\$_DB_pass = '" . $_DB_pass . "';", "\$_DB_pass = '" . $_GLFUSION['db_pass'] . "';", $dbconfig_data); // Password
    $dbconfig_data = str_replace("\$_DB_table_prefix = '" . $_DB_table_prefix . "';", "\$_DB_table_prefix = '" . $_GLFUSION['db_prefix'] . "';", $dbconfig_data); // Table prefix
    $dbconfig_data = str_replace("\$_DB_dbms = '" . $_DB_dbms . "';", "\$_DB_dbms = '" . $_GLFUSION['db_type'] . "';", $dbconfig_data); // Database type

    // Write changes to db-config.php
    $dbconfig_file = fopen($config_file, 'w');
    if (!fwrite($dbconfig_file, $dbconfig_data)) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to write to ' . $config_file);
        return _displayError(DBCONFIG_NOT_WRITABLE,'getsiteinformation');
    }
    fflush($dbconfig_file);
    fclose($dbconfig_file);

    require $config_file;

    if ( !file_exists($_CONF['path_system'].'lib-database.php') ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $_CONF['path_system'].'lib-database.php');
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error code: ' . __LINE__);
    }

    require_once $_CONF['path_system'] . 'classes/Autoload.php';
    glFusion\Autoload::initialize();

    require_once $_CONF['path'].'system/db-init.php';
    require $_CONF['path_system'].'lib-security.php';
    if ( $_DB_dbms == 'mysqli' || $_DB_dbms == 'pdo') $_DB_dbms = 'mysql';
    INST_errorLog($log_path,'INSTALL: Installing Database Tables and Default Data');
    list($rc,$errors) = INST_createDatabaseStructures($use_innodb);
    if ( $rc != true ) {
        INST_errorLog($log_path,'INSTALL: ERROR: create tables failed');
        return _displayError(DB_NO_CONNECT,'getsiteinformation',$errors);
    }
    $site_name      = isset($_GLFUSION['site_name']) ? $_GLFUSION['site_name'] : '';
    $site_slogan    = isset($_GLFUSION['site_slogan']) ? $_GLFUSION['site_slogan'] : '';
    $site_url       = isset($_GLFUSION['site_url']) ? $_GLFUSION['site_url'] : INST_getSiteUrl();
    $site_admin_url = isset($_GLFUSION['site_admin_url']) ? $_GLFUSION['site_admin_url'] : INST_getSiteAdminUrl();
    $site_mail      = isset($_GLFUSION['site_mail']) ? $_GLFUSION['site_mail'] : '' ;
    $noreply_mail   = isset($_GLFUSION['noreply_mail']) ? $_GLFUSION['noreply_mail'] : '' ;

//    $gl_path      = $_GLFUSION['dbconfig_path'];

    $log_path     = isset($_GLFUSION['log_path'])    ? $_GLFUSION['log_path']    : $gl_path . 'logs/';
    $lang_path    = isset($_GLFUSION['lang_path'])   ? $_GLFUSION['lang_path']   : $gl_path . 'language/';
    $backup_path  = isset($_GLFUSION['backup_path']) ? $_GLFUSION['backup_path'] : $gl_path . 'backups/';
    $data_path    = isset($_GLFUSION['data_path'])   ? $_GLFUSION['data_path']   : $gl_path . 'data/';
    INST_errorLog($log_path,'INSTALL: Personalizing the default Admin account');
    INST_personalizeAdminAccount($site_mail, $site_url);

    if ( !file_exists($_CONF['path_system'].'classes/config.class.php') ) {
    INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate ' . $_CONF['path_system'].'classes/config.class.php');
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__ );
    }
    require_once $_CONF['path_system'].'classes/config.class.php';
    require_once $_CONF['path'].'sql/core_config_data.php';
    require_once 'config-install.php';
    INST_errorLog($log_path,'INSTALL: Installing default configuration data');
    install_config($site_url,$coreConfigData);

    $html_path  = $_PATH['public_html'];

    $config = config::get_instance();
    $config->set('site_name', $site_name);
    $config->set('site_slogan', $site_slogan);
    $config->set('site_url', $site_url);
    $config->set('site_admin_url', $site_admin_url);
    $config->set('site_mail', $site_mail);
    $config->set('noreply_mail', $noreply_mail);
    $config->set('path_html', $html_path);
    $config->set('path_log', $log_path);
    $config->set('path_language', $lang_path);
    $config->set('backup_path', $backup_path);
    $config->set('path_data', $data_path);
    $config->set('path_images', $html_path . 'images/');
    $config->set('path_themes', $html_path . 'layout/');
    $config->set('rdf_file', $html_path . 'backend/glfusion.rss');
    $config->set('path_pear', $_CONF['path_system'] . 'pear/');
    $config->set_default('default_photo', $site_url.'/default.jpg');

    $lng = INST_getDefaultLanguage($gl_path . 'language/', $language, $utf8);
    if (!empty($lng)) {
        $config->set('language', $lng);
    }

    $_CONF['path_html']         = $html_path;
    $_CONF['site_url']          = $site_url;
    $_CONF['site_admin_url']    = $site_admin_url;

    // Setup default theme
    $config->set('theme', 'cms');
    DB_query("UPDATE {$_TABLES['users']} SET theme='cms' WHERE uid=2",1);

    $var = time() - rand();
    $session_cookie = 'pw'.substr(md5($var),0,3);
    DB_query("UPDATE {$_TABLES['conf_values']} SET value='".serialize($session_cookie)."' WHERE name='cookie_password'",1);

    $var = time() - rand();
    $session_cookie = 'pc'.substr(md5($var),0,3);
    DB_query("UPDATE {$_TABLES['conf_values']} SET value='".serialize($session_cookie)."' WHERE name='cookie_name'",1);

    $var = time() - rand();
    $session_cookie = 'sc'.substr(md5($var),0,3);
    DB_query("UPDATE {$_TABLES['conf_values']} SET value='".serialize($session_cookie)."' WHERE name='cookie_session'",1);

    $rk = INST_randomKey(80);
    DB_query("INSERT INTO {$_TABLES['vars']} (name,value) VALUES ('guid','".$rk."')",1);

    $securePassword = isset($_GLFUSION['securepassword']) ? $_GLFUSION['securepassword'] : INST_securePassword(15);

    $encryptedPassword = SEC_encryptPassword($securePassword);
    DB_query("UPDATE {$_TABLES['users']} SET passwd='".$encryptedPassword."' WHERE uid=2",1);
    $_GLFUSION['securepassword'] = $securePassword;

    INST_errorLog($log_path,'INSTALL: Completed installation of default configuration data');
    $config->_purgeCache();
    // rebuild the config array
    if ( !file_exists($siteconfig_path) ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to locate '. $siteconfig_path . ' at line ' . __LINE__);
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
    }
    include $siteconfig_path;

    $config->load_baseconfig();
    $config->initConfig();
    $_CONF = $config->get_config('Core');

    $config->_purgeCache();
    INST_errorLog($log_path,'INSTALL: Touching default log files');
    @touch($log_path.'error.log');
    @touch($log_path.'access.log');
    @touch($log_path.'captcha.log');
    @touch($log_path.'spamx.log');

    global $_CONF, $_SYSTEM, $_VARS, $_DB, $_DB_dbms, $_GROUPS, $_RIGHTS, $TEMPLATE_OPTIONS;

    if ( !file_exists($_CONF['path_html'].'lib-common.php') ) {
        INST_errorLog($log_path,'INSTALL: ERROR: Unable to loate ' . $_CONF['path_html'].'lib-common.php');
        return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
    }
    require $_CONF['path_html'].'lib-common.php';

    if ( $_DB_dbms == 'mysqli' || $_DB_dbms == 'pdo') $_DB_dbms = 'mysql';

    INST_errorLog($log_path,'INSTALL: Performing default plugin installations');
    INST_pluginAutoInstall('bad_behavior2');
    INST_pluginAutoInstall('captcha');
    INST_pluginAutoInstall('ckeditor');
    INST_pluginAutoInstall('spamx');
    INST_pluginAutoInstall('staticpages');

    $config->_purgeCache();
    INST_clearCache();

    $T = new TemplateLite('templates/');
    $T->set_file('page','contentplugins.thtml');

    $T->set_var(array(
        'lang_content_plugins'      =>  $LANG_INSTALL['content_plugins'],
        'lang_load_sample_content'  =>  $LANG_INSTALL['load_sample_content'],
        'lang_samplecontent_desc'   =>  $LANG_INSTALL['samplecontent_desc'],
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
        'hiddenfields'              => _buildHiddenFields(),
    ));

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


/**
 * Installs optional plugins and content
 *
 * @note redirects to success page.
 *
 */
function INST_doPluginInstall()
{
    global $_GLFUSION, $_CONF, $_TABLES, $_DB_table_prefix;

    $site_url = $_CONF['site_url'];
    $language = $_GLFUSION['language'];

    $pluginsToInstall = $_POST['plugin'];
    if ( is_array($pluginsToInstall) ) {
        foreach ($pluginsToInstall AS $plugin => $settings ) {
            $rc = INST_pluginAutoInstall($plugin);
            if ( $rc === false ) {
                return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__ );
            }
        }
    }
    if ( isset($_POST['installdefaultdata']) ) {
        if ( !file_exists($_CONF['path'].'sql/default_content.php') ) {
            return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__ );
        }
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
        // cycle through the rest of the installed plugins and add their data
        if ( is_array($installedPlugins) ) {
            foreach ($installedPlugins AS $plugin) {
                if ( isset($_DATA[$plugin]) && is_array($_DATA[$plugin]) ) {
                    foreach ($_DATA[$plugin] AS $sql) {
                        DB_query($sql,1);
                    }
                }
            }
        }
    }

    INST_clearCache();

    return INST_complete();
}

/**
 * Displays final install / upgrade success page
 * with details on the process.
 *
 */
function INST_complete()
{
    global $_GLFUSION, $_SYSTEM, $_CONF, $_TABLES, $_DB, $_DB_dbms,
           $_DB_host, $_DB_user,$_DB_pass, $site_url,$_DB_table_prefix,
           $LANG_INSTALL, $LANG_SUCCESS, $percent_complete;

    if ( ($rc = _checkSession() ) !== 0 ) {
        return $rc;
    }

    $_GLFUSION['currentstep'] = 'complete';

    $method = $_GLFUSION['method'];
    $method = preg_replace('/[^a-z0-9\-_]/', '', $method);
    $templateFile = 'success_'.$method.'.thtml';

    if ( isset($_GLFUSION['securepassword'])) {
        $securepassword = $_GLFUSION['securepassword'];
    } else {
        $securepassword = '';
    }

    $T = new TemplateLite('templates/');
    $T->set_file('page',$templateFile);

    $T->set_var(array(
        'securepassword'            => $securepassword,
        'method'                    => $method,
        'lang_success'              => $LANG_SUCCESS[1].'v' . GVERSION . $LANG_SUCCESS[2],
        'lang_congradulations'      => $LANG_SUCCESS[3] . (($method == 'install') ? $LANG_SUCCESS[20] : $LANG_SUCCESS[21]) . $LANG_SUCCESS[4],
        'lang_login'                => $LANG_SUCCESS[5],
        'lang_username'             => $LANG_SUCCESS[6],
        'lang_password'             => $LANG_SUCCESS[8],
        'lang_sec_warning'          => $LANG_SUCCESS[10],
        'lang_dont_forget'          => $LANG_SUCCESS[11],
        'lang_number_of_things'     => (($method == 'upgrade') ? '2' : '3'),
        'lang_things'               => $LANG_SUCCESS[12],
        'lang_rename'               => $LANG_SUCCESS[13],
        'install_directory'         => $_CONF['path_admin'] . 'install/',
        'lang_change_password'      => $LANG_SUCCESS[14],
        'lang_account_password'     => $LANG_SUCCESS[15],
        'password_link'             => $_CONF['site_url'].'/usersettings.php?mode=edit',
        'lang_set_perms'            => $LANG_SUCCESS[16],
        'db_config_path'            => $_CONF['path'] . 'db-config.php',
        'lang_and'                  => $LANG_SUCCESS[17],
        'siteconfig_path'           => $_CONF['path_html'] . 'siteconfig.php',
        'lang_backto'               => $LANG_SUCCESS[18],
        'lang_quick_start'          => $LANG_INSTALL['quick_start'],
        'lang_quick_start_help'     => $LANG_INSTALL['quick_start_help'],
        'lang_remove_install_directory' => $LANG_SUCCESS[22],
        'lang_remove_install_help'      => $LANG_SUCCESS[23],
        'lang_remove_install_files'     => $LANG_SUCCESS[24],
        'lang_whats_new'                => $LANG_SUCCESS[25],
        'lang_whats_new_help'           => $LANG_SUCCESS[26],
        'lang_goto_site'                => $LANG_SUCCESS[27],
        'lang_button_files_removed'     => $LANG_SUCCESS[28],
        'lang_error_removing_files'     => $LANG_SUCCESS[29],
        'lang_error_message'            => $LANG_SUCCESS[30],
        'lang_record_password'          => $LANG_SUCCESS[31],
        'lang_password_confirm'         => $LANG_SUCCESS[32],
        'lang_continue'                 => $LANG_SUCCESS[33],
        'lang_cancel'                   => $LANG_SUCCESS[34],
        'new_site_url'              => $_CONF['site_url'],
    ));
    $alertMsg = '';
    if ( $method == 'upgrade' ) {
        if ( @file_exists($_CONF['path_admin'].'install/alert.html') ) {
            $alertMsg = file_get_contents($_CONF['path_admin'].'install/alert.html');
            if ( $alertMsg != '' ) {
                $T->set_var('alert_message',$alertMsg);
            }
        }
        $T->set_var(array(
            'lang_version_check' => $LANG_INSTALL['version_check'],
            'lang_check_for_updates' => $LANG_INSTALL['check_for_updates'],
        ));
    }
    $percent_complete = 100;
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}

/**
 * Upgrades an existing glFusion installation
 *
 * @return  string          HTML if error or NULL if success
 *
 */
function INST_doSiteUpgrade()
{
    global $_GLFUSION, $_CONF,  $_TABLES, $_DB_dbms, $LANG_INSTALL;

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
        if ( !file_exists($_CONF['path_system'] . 'classes/config.class.php') ) {
            return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
        }
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
        return _displayError(CORE_UPGRADE_ERROR,'checkenvironment',$errors /*$display*/);
    }
    return;
}

/**
 * Calls all bundled plugin upgrade routines
 *
 * @return  string          HTML or redirects to success page
 *
 */
function INST_doPluginUpgrade()
{
    global $_GLFUSION, $_CONF, $_TABLES, $LANG_INSTALL;

    $language = $_GLFUSION['language'];

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
    $rc = INST_pluginAutoUpgrade('spamx',1);
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Spamx');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('staticpages',1);
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'Static Pages');
        $upgradeError = 1;
    }
    $rc = INST_pluginAutoUpgrade('captcha',1);
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
    $rc = INST_pluginAutoUpgrade('ckeditor',1);
    if ( $rc == false ) {
        $error .= sprintf($LANG_INSTALL['plugin_upgrade_error'],'CKEditor');
        $upgradeError = 1;
    }

    $stdPlugins=array('ckeditor','staticpages','spamx','links','polls','calendar','captcha','bad_behavior2','forum','mediagallery','filemgmt');
    foreach ($stdPlugins AS $pi_name) {
        DB_query("UPDATE {$_TABLES['plugins']} SET pi_gl_version='".GVERSION."', pi_homepage='http://www.glfusion.org' WHERE pi_name='".$pi_name."'",1);
    }

    INST_clearCache();

    // ************* TEST CODE - REMOVE *****
    // $upgradeError = 1;
    // $error = 'Problem 1<br>Problem 2<br>';
    // **************************************

    if ( $upgradeError ) {
        return _displayError(PLUGIN_UPGRADE_ERROR,'done',$error);
    }

    global $obsoletePrivateDir,$obsoletePublicDir, $obsoletePrivateFiles, $obsoletePublicFiles;

    if ( count ($obsoletePrivateDir) > 0 ||
         count ($obsoletePublicDir) > 0 ||
         count ($obsoletePrivateFiles) > 0 ||
         count ($obsoletePublicFiles) > 0
    ) {
        return INST_FileCleanUp();
    }

    return INST_complete();
}


/**
 * Prompts use to see if they want to have glFusion automatically
 * remove obsolete files.
 *
 * @return  HTML    Confirmation screen to delete files...
 *
 */
function INST_FileCleanUp()
{
    global $_GLFUSION, $_CONF, $_TABLES, $LANG_INSTALL;
    global $obsoletePrivateDir,$obsoletePublicDir, $obsoletePrivateFiles, $obsoletePublicFiles;

    $language = $_GLFUSION['language'];
    $_GLFUSION['currentstep'] = 'cleanup';

    // show a screen to see if we need to remove stuff...

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'removefiles.thtml');

    $T->set_var(array(
        'step_heading'      => $LANG_INSTALL['cleanup'],
        'lang_install_heading' => $LANG_INSTALL['remove_obsolete'],
        'lang_cleanup'      => $LANG_INSTALL['remove_instructions'],
        'lang_delete_files' => $LANG_INSTALL['delete_files'],
        'lang_cancel'       => $LANG_INSTALL['cancel'],
        'lang_skip'         => $LANG_INSTALL['skip'],
        'lang_show_files'   => $LANG_INSTALL['show_files_to_delete'],
        'lang_skip_warning' => $LANG_INSTALL['remove_skip_warning'],
        'hiddenfields'      => _buildHiddenFields(),
    ));

    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


/**
 * Removes unused / obsolete files from tree.
 *
 * @return  html    a list of files removed or errors if there were any...
 *
 */
function INST_doFileCleanUp()
{
    global $_GLFUSION, $_CONF, $_TABLES, $LANG_INSTALL;
    global $obsoletePrivateDir,$obsoletePublicDir, $obsoletePrivateFiles, $obsoletePublicFiles, $obsoleteAdminFiles;

    $language = $_GLFUSION['language'];

    $_GLFUSION['currentstep'] = 'cleanup';

    $retval       = '';
    $failure      = '';

    if (isset($obsoletePrivateFiles) && is_array($obsoletePrivateFiles) && count($obsoletePrivateFiles) > 0 ) {
        foreach ( $obsoletePrivateFiles AS $file ) {
            if ( file_exists( $_CONF['path'].$file )) {
                $rc = @unlink($_CONF['path'].$file);
                if ( $rc === false ) {
                    $failure .= '<li>FILE: '.$_CONF['path'].$file.'</li>';
                }
            }
        }
    }

    if (isset($obsoletePublicFiles) && is_array($obsoletePublicFiles) && count($obsoletePublicFiles) > 0 ) {
        foreach ( $obsoletePublicFiles AS $file ) {
            if ( file_exists( $_CONF['path_html'].$file )) {
                $rc = @unlink($_CONF['path_html'].$file);
                if ( $rc === false ) {
                    $failure .= '<li>FILE: '.$_CONF['path_html'].$file.'</li>';
                }
            }
        }
    }
    if (isset($obsoleteAdminFiles) && is_array($obsoleteAdminFiles) && count($obsoleteAdminFiles) > 0 ) {
        foreach ( $obsoleteAdminFiles AS $file ) {
            if ( file_exists( $_CONF['path_admin'].$file )) {
                $rc = @unlink($_CONF['path_admin'].$file);
                if ( $rc === false ) {
                    $failure .= '<li>FILE: '.$_CONF['path_admin'].$file.'</li>';
                }
            }
        }
    }
    if (isset($obsoletePublicDir) && is_array($obsoletePublicDir) && count($obsoletePublicDir) > 0 ) {
        foreach ( $obsoletePublicDir AS $directory ) {
            if ( is_dir($_CONF['path_html'].$directory)) {
                $rc = INST_deleteDir($_CONF['path_html'].$directory);
                if ( $rc === false ) {
                    $failure .= '<li>DIR: '.$_CONF['path_html'].$directory.'</li>';
                }
            }
        }
    }
    if (isset($obsoletePrivateDir) && is_array($obsoletePrivateDir) && count($obsoletePrivateDir) > 0  ) {
        foreach ( $obsoletePrivateDir AS $directory ) {
            if ( is_dir($_CONF['path'].$directory)) {
                $rc = INST_deleteDir($_CONF['path'].$directory);
                if ( $rc === false ) {
                    $failure .= '<li>DIR: '.$_CONF['path'].$directory.'</li>';
                }
            }
        }
    }

    // special handling of the theme files from 1.5.0 and 1.5.1
    if ( $_GLFUSION['original_version'] == '1.5.0' || $_GLFUSION['original_version'] == '1.5.1' ) {
        if (file_exists('deleted15.php')) {
            include 'deleted15.php';
            if (isset($obsoleteTemplateFiles) && is_array($obsoleteTemplateFiles) && count($obsoleteTemplateFiles) > 0 ) {
                  foreach ( $obsoleteTemplateFiles AS $item ) {
                    if ( file_exists( $_CONF['path_layout'].$item['file'] )) {
                        $sha = sha1_file($_CONF['path_layout'].$item['file']);
                        if ( $sha == $item['sha1'] ) {
                            $rc = @unlink($_CONF['path_layout'].$item['file']);
                            if ( $rc === false ) {
                                $failure .= '<li>FILE: '.$_CONF['path_layout'].$item['file'].'</li>';
                            }
                        } else {
                            COM_errorLog("UPGRADE: Keeping modified template file: " . $item['file']);
                        }
                    }
                }
            }
        }
        if (isset($obsoleteTemplateDir) && is_array($obsoleteTemplateDir) && count($obsoleteTemplateDir) > 0  ) {
            foreach ( $obsoleteTemplateDir AS $directory ) {
                if ( is_dir($_CONF['path_layout'].$directory)) {
                    $rc = INST_deleteDirIfEmpty($_CONF['path_layout'].$directory);
                }
            }
        }
    }

// test failure message
// $failure = '<li>DIR: Test failure message</li><li>FILE: /www/www/www/www.txt</li>';

    if ( $failure == '') {
        $method = $_GLFUSION['method'];
        return INST_complete();
    }

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'removefiles_complete.thtml');

    $T->set_var(array(
        'step_heading'      => $LANG_INSTALL['obsolete_confirm'],
        'lang_complete'     => $LANG_INSTALL['complete'],
        'hiddenfields'      => _buildHiddenFields(),
    ));
    if ( $failure != '' ) {
        $T->set_var(array(
            'confirm_heading'   => $LANG_INSTALL['obsolete_confirm'],
            'confirm_message'   => '<div class="uk-alert uk-alert-danger">'.$LANG_INSTALL['removal_fail_msg'].'</div>',
            'confirm_details'   => '<ul class="uk-list uk-list-striped">'.$failure.'</ul>',
        ));
    } else {
        $T->set_var(array(
            'confirm_heading'   => $LANG_INSTALL['removal_success'],
            'confirm_details'   => $LANG_INSTALL['removal_success_msg'],
        ));
    }
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


/**
 * Display installation details
 *
 * Provide important details to user on what is needed for installation
 *
 * @return  string          HTML
 *
 */
function INST_installAlert( )
{
    global $_GLFUSION, $LANG_INSTALL;

    // set the session expire time.
    $_GLFUSION['expire'] = time() + 1800;

    $_GLFUSION['currentstep'] = 'installalert';

    if ( isset($_GLFUSION['language']) ) {
        $language = $_GLFUSION['language'];
    } else {
        $language = 'english_utf-8';
    }

    $retval = '';

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'install-alert.thtml');

    $prevAction = 'languagetask';
    $nextAction = 'pathsetting';

    $T->set_var(array(
        'nextaction'            => $nextAction,
        'prevaction'            => $prevAction,
        'step_heading'          => $LANG_INSTALL['install_stepheading'],
        'lang_next'             => $LANG_INSTALL['next'],
        'lang_prev'             => $LANG_INSTALL['previous'],
        'lang_doc_alert'        => $LANG_INSTALL['install_doc_alert'],
        'lang_doc_alert2'       => $LANG_INSTALL['install_doc_alert2'],
        'lang_install_heading'  => $LANG_INSTALL['install_header'],
        'lang_install_bullet1'  => $LANG_INSTALL['install_bullet1'],
        'lang_install_bullet2'  => $LANG_INSTALL['install_bullet2'],
        'lang_install_bullet3'  => $LANG_INSTALL['install_bullet3'],
        'lang_install_bullet4'  => $LANG_INSTALL['install_bullet4'],
        'lang_install_bullet5'  => $LANG_INSTALL['install_bullet5'],
        'lang_install_bullet6'  => $LANG_INSTALL['install_bullet6'],
        'lang_install_bullet7'  => $LANG_INSTALL['install_bullet7'],
        'hiddenfields'          => _buildHiddenFields(),
    ));

    $T->parse('output','page');

    $retval =  $T->finish($T->get_var('output'));

    return $retval;
}

/**
 * Display upgrade details
 *
 * Displays important upgrade informaton to the user
 *
 * @return  string          HTML
 *
 */
function INST_upgradeAlert( )
{
    global $_GLFUSION, $LANG_INSTALL;

    // set the session expire time.
    $_GLFUSION['expire'] = time() + 1800;

    $_GLFUSION['currentstep'] = 'upgradealert';

    if ( isset($_GLFUSION['language']) ) {
        $language = $_GLFUSION['language'];
    } else {
        $language = 'english_utf-8';
    }

    $retval = '';

    $T = new TemplateLite('templates/');
    $T->set_file('page', 'upgrade-alert.thtml');

    $prevAction = 'languagetask';
    $nextAction = 'startupgrade';

    $T->set_var(array(
        'nextaction'            => $nextAction,
        'prevaction'            => $prevAction,
        'step_heading'          => $LANG_INSTALL['upgrade_heading'],
        'lang_next'             => $LANG_INSTALL['next'],
        'lang_prev'             => $LANG_INSTALL['previous'],
        'lang_doc_alert'        => $LANG_INSTALL['doc_alert'],
        'lang_doc_alert2'       => $LANG_INSTALL['doc_alert2'],
        'lang_backup'           => $LANG_INSTALL['backup'],
        'lang_backup_instructions' => $LANG_INSTALL['backup_instructions'],
        'lang_bullet1'          => $LANG_INSTALL['upgrade_bullet1'],
        'lang_bullet2'          => $LANG_INSTALL['upgrade_bullet2'],
        'lang_bullet3'          => $LANG_INSTALL['upgrade_bullet3'],
        'lang_bullet4'          => $LANG_INSTALL['upgrade_bullet4'],
        'lang_bullet_title'     => $LANG_INSTALL['upgrade_bullet_title'],
        'hiddenfields'          => _buildHiddenFields(),
    ));

    $T->parse('output','page');

    $retval =  $T->finish($T->get_var('output'));

    return $retval;
}

/*
 * Start of the main program
 */

$_SYSTEM['no_cache_config'] = true;

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

if ( isset($_GLFUSION['language']) ) {
    $lng = $_GLFUSION['language'];
} else {
    $lng = 'english_utf-8';
}
if ( isset($_POST['lang']) ) {
    $lng = $_POST['lang'];
}
if ( isset($_GET['lang']) ) {
    $lng = $_GET['lang'];
}

// sanitize value and check for file
$lng = preg_replace('/[^a-z0-9\-_]/', '', $lng);
if (!empty($lng) && is_file('language/' . $lng . '.php')) {
    $language = $lng;
} else {
    $language = 'english_utf-8';
}

$_GLFUSION['language'] = $language;
if ( !file_exists('language/'.$language.'.php') ) {
    return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__ );
}
require_once 'language/'.$language.'.php';

if ( isset($_POST['task']) ) {
    $mode = $_POST['task'];
} else {
    $mode = '';
}


if ( !isset($_GLFUSION['method'])) {
    $method = 'install';
} else {
    $method = $_GLFUSION['method'];
}

if ( isset($_POST['type']) ) {
    switch($_POST['type']) {
        case 'install' :
            $method = 'install';
            $mode   = 'installalert';
            break;
        case 'upgrade' :
            $method = 'upgrade';
            $mode   = 'upgradealert';
            break;
        case 'migrate' :
            $method = 'upgrade';
            $mode   = 'migrate';
            $_GLFUSION['migrate'] = 1;
            break;
    }
}

$_GLFUSION['method'] = $method;

switch($mode) {

    case 'installalert' :
        $pageBody = INST_installAlert();
        $percent_complete = 20;
        break;

    case 'upgradealert' :
        $percent_complete = 20;
        $pageBody = INST_upgradeAlert();
        break;

    case 'pathsetting' :
        $pageBody = INST_getPathSetting();
        $percent_complete = 30;
        break;

    case 'gotpathsetting':
        $pageBody =   INST_gotPathSetting();
        $percent_complete = 50;
        break;

    case 'checkenvironment' :
        $pageBody = INST_checkEnvironment();
        $percent_complete = 50;
        break;

    case 'getsiteinformation' :
        $pageBody = INST_getSiteInformation();
        $percent_complete = 70;
        break;

    case 'gotsiteinformation' :
        $pageBody = INST_gotSiteInformation();
        $percent_complete = 80;
        break;

    case 'contentplugins' :
        $pageBody = INST_installAndContentPlugins();
        $percent_complete = 90;
        break;

    case 'installplugins' :
        require '../../lib-common.php';
        $pageBody = INST_doPluginInstall();
        break;

    case 'startupgrade' :
        if ( !@file_exists('../../siteconfig.php') ) {
            $pageBody = _displayError(SITECONFIG_NOT_FOUND,'');
            $percent_complete = 50;
        } else {
            $percent_complete = 50;
            require '../../siteconfig.php';
            if ( !file_exists($_CONF['path'].'db-config.php') ) {
                return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
            }
            require $_CONF['path'].'db-config.php';
            $_GLFUSION['dbconfig_path'] = $_CONF['path'];
            if ( !file_exists($_CONF['path_system'].'lib-database.php') ) {
                return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__ );
            }
            require_once $_CONF['path_system'] . 'classes/Autoload.php';
            glFusion\Autoload::initialize();
            require_once $_CONF['path_system'].'db-init.php';
            $version = INST_identifyglFusionVersion();
            if ($version == '' || $version == 'empty' ) {
                $pageBody = _displayError(CORE_UPGRADE_ERROR,'',$LANG_INSTALL['unable_to_find_ver']);
            } else {
                $pageBody = INST_checkEnvironment();
            }
        }
        break;

    case 'doupgrade' :
        if ( !@file_exists('../../siteconfig.php') ) {
            $pageBody = _displayError(SITECONFIG_NOT_FOUND,'');
            $percent_complete = 50;
        } else {
            $percent_complete = 80;
            require '../../siteconfig.php';
            if ( !file_exists($_CONF['path'].'db-config.php') ) {
                return _displayError(FILE_INCLUDE_ERROR,'pathsetting','Error Code: ' . __LINE__);
            }
            require $_CONF['path'].'db-config.php';
            $_GLFUSION['dbconfig_path'] = $_CONF['path'];
            if ( !file_exists($_CONF['path_system'] . 'lib-database.php') ) {
                return _displayError(FILE_INCLUDE_ERROR,'pathsetting', 'Error Code: ' . __LINE__);
            }
            require_once $_CONF['path_system'] . 'classes/Autoload.php';
            glFusion\Autoload::initialize();
            require $_CONF['path_system'] . 'db-init.php';

            $pageBody = INST_doSiteUpgrade();
        }
        if ( $pageBody != '' ) {
            break;
        }
        // fall through here on purpose and process the siteconfig upgrade...
        // at this point we have a fully updated database and core environment

    case 'dositeconfigupgrade' :
        require '../../lib-common.php';
        INST_doSiteConfigUpgrade();

        // fall through here on purpose and process the plugin upgrades.....
        // at this point we have a fully updated database and core environment

    case 'dopluginupgrade' :
        $pageBody = INST_doPrePluginUpgrade();
        $pageBody .= INST_doPluginUpgrade();
        break;

    case 'dofilecleanup' :
        $action = 'cleanup';
        $percent_complete = 95;
        require_once '../../lib-common.php';
        $pageBody = INST_doFileCleanUp();
        break;

    case 'done' :
        require '../../lib-common.php';
        $percent_complete = 100;
        $method = $_GLFUSION['method'];
        $pageBody = INST_complete($method);
        break;

    default:
        $percent_complete = 10;
        $_GLFUSION['language'] = $language;
        $_GLFUSION['method'] = $method;
        $pageBody = INST_getLanguageTask( );
        break;
}

echo INST_header($percent_complete);
echo $pageBody;
echo INST_footer();
exit;
?>
