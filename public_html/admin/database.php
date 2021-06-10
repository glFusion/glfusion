<?php
/**
* glFusion CMS
*
* glFusion database administration page.
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2011 by the following authors:
*  Tony Bibbs         - tony AT tonybibbs DOT com
*  Blaine Lang        - langmail AT sympatico DOT ca
*  Dirk Haun          - dirk AT haun-online DOT de
*  Alexander Schmacks - Alexander.Schmacks AT gmx DOT de
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';
require_once $_CONF['path'].'system/classes/Diff.class.php';

use \glFusion\Database\Database;
use \glFusion\FileSystem;
use \glFusion\Log\Log;
use \glFusion\Cache\Cache;
use \glFusion\Admin\AdminAction;

$display = '';
$page    = '';

if (!SEC_hasRights('database.admin') ) {
    $display = COM_siteHeader('menu', $LANG_DB_ADMIN['database_admin']);
    $display .= COM_showMessageText($MESSAGE[46],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter();
    Log::logAccessViolation('Database Administration');
    echo $display;
    exit;
}

USES_lib_admin();

/**
* Sort backup files with newest first, oldest last.
* For use with usort() function.
* This is needed because the sort order of the backup files, coming from the
* 'readdir' function, might not be that way.
*/
function DBADMIN_compareBackupFiles($pFileA, $pFileB)
{
    global $_CONF;

    $lFiletimeA = @filemtime($_CONF['backup_path'] . $pFileA);
    $lFiletimeB = @filemtime($_CONF['backup_path'] . $pFileB);
    if ($lFiletimeA == $lFiletimeB) {
       return 0;
    }

    return ($lFiletimeA > $lFiletimeB) ? -1 : 1;
}

/**
* List all backups, i.e. all files ending in .sql
*
* @return   string      HTML for the list of files or an error when not writable
*
*/
function DBADMIN_list()
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE, $LANG08, $LANG_ADMIN, $LANG_DB_ADMIN;

    $retval = '';

    if (FileSystem::isWritable($_CONF['backup_path'])) {
        $backups = array();
        $fd = opendir($_CONF['backup_path']);
        $index = 0;
        while ((false !== ($file = @readdir($fd)))) {
            if ($file <> '.' && $file <> '..' && $file <> 'CVS' &&
                    preg_match('/\.sql(\.gz)?$/i', $file)) {
                $index++;
                clearstatcache();
                $backups[] = $file;
            }
        }

        usort($backups, 'DBADMIN_compareBackupFiles');

        $data_arr = array();
        $thisUrl = $_CONF['site_admin_url'] . '/database.php';
        $diskIconUrl = $_CONF['layout_url'] . '/images/admin/disk.' . $_IMAGE_TYPE;
        $attr['title'] = $LANG_DB_ADMIN['download'];
        $alt = $LANG_DB_ADMIN['download'];
        $num_backups = count($backups);
        for ($i = 0; $i < $num_backups; $i++) {
            $downloadUrl = $thisUrl . '?download=x&amp;file='.urlencode($backups[$i]);
            $downloadLink = COM_createLink(COM_createImage($diskIconUrl, $alt, $attr), $downloadUrl, $attr);
            $downloadLink .= '&nbsp;&nbsp;';
            $attr['style'] = 'vertical-align:top;';
            $downloadLink .= COM_createLink($backups[$i], $downloadUrl, $attr);
            $backupfile = $_CONF['backup_path'] . $backups[$i];
            $backupfilesize = COM_numberFormat(filesize($backupfile))
                            . ' <b>' . $LANG_DB_ADMIN['bytes'] . '</b>';
            $data_arr[$i] = array('file' => $downloadLink,
                                  'size' => $backupfilesize,
                                  'filename' => $backups[$i]);
        }

        $token = SEC_createToken();

        $menu_arr = array();

        $allInnoDB = DBADMIN_innodbStatus();
        $allMyIsam = DBADMIN_myisamStatus();

        $menu_arr = getAdminHeaderMenu('database_admin');

        $retval .= COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                            COM_getBlockTemplate('_admin_block', 'header'));
        $retval .= ADMIN_createMenu(
            $menu_arr,
            "<p>{$LANG_DB_ADMIN['db_explanation']}</p>" .
            '<p>' . sprintf($LANG_DB_ADMIN['total_number'], $index) . '</p>',
            $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE
        );

        $header_arr = array(      // display 'text' and use table field 'field'
            array('text' => $LANG_DB_ADMIN['backup_file'], 'field' => 'file'),
            array('text' => $LANG_DB_ADMIN['size'],        'field' => 'size')
        );

        $text_arr = array(
            'no_data'  => $LANG_DB_ADMIN['no_backups_found'],
            'form_url' => $thisUrl
        );
        $form_arr = array('bottom' => '', 'top' => '');
        if ($num_backups > 0) {
            $form_arr['bottom'] = '<input type="hidden" name="delete" value="x">'
                                . '<input type="hidden" name="' . CSRF_TOKEN
                                . '" value="' . $token . '">' . LB;
        }
        $options = array('chkselect' => true, 'chkminimum' => 0,
                             'chkfield' => 'filename');
        $retval .= ADMIN_simpleList('', $header_arr, $text_arr, $data_arr,
                                    $options, $form_arr);
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    } else {
        $retval .= COM_startBlock($LANG08[06], '',
                            COM_getBlockTemplate('_msg_block', 'header'));
        $retval .= sprintf($LANG_DB_ADMIN['no_access'],$_CONF['backup_path']);
        Log::write('system',Log::ERROR,$_CONF['backup_path'] . ' is not writable.');
        $retval .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    }

    return $retval;
}

function DBADMIN_backupAjax()
{
    global $_CONF, $_DB_name;

    if ( !COM_isAjax()) die();

    $retval = array();
    $errorCode = 0;

    $backup = new dbBackup();

    $backup_filename = $backup->getBackupFilename();

    $rc = $backup->initBackup();
    if ( $rc === false ) {
        Log::write('system',Log::ERROR,"DBADMIN error - unable to initialize backup");
        $errorCode = 1;
        $retval['statusMessage'] = 'Unable to initialize backup - see error.log for details';
    }

    $tableList = array();

    $rowCount = 0;

    $backup_tables = $backup->getTableList();
    if ( is_array($backup_tables)) {
        foreach ($backup_tables AS $index => $value) {
            $tableList[] = $value;
            $rowCount += DB_count($value);
        }
    }

    $retval['errorCode'] = $errorCode;
    $retval['backup_filename'] = $backup_filename;
    $retval['tablelist'] = $tableList;
    $retval['totalrows'] = $rowCount;
    $retval['statusMessage'] = 'Initialization Successful';

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function DBADMIN_backupCompleteAjax()
{
    if ( !COM_isAjax()) die();

    $filename = '';
    $retval = array();

    $filter = sanitizer::getInstance();

    if (isset($_POST['backup_filename'])) {
        $filename = $_POST['backup_filename'];
        $filename = $filter->sanitizeFilename($filename,true);
    }
    $backup = new dbBackup();
    $backup->setBackupFilename($filename);
    $backup->completeBackup();
    $backup->save_backup_time();
    $backup->Purge();
    $retval['errorCode'] = 0;
    $return["js"] = json_encode($retval);
    echo json_encode($return);
    exit;
}

function DBADMIN_backupTableAjax()
{
    global $_VARS;

    if ( !COM_isAjax()) die();

    $retval = array();

    if (!isset($_VARS['_dbback_allstructs'])) {
        $_VARS['_dbback_allstructs'] = 0;
    }

    $filename = '';

    $filter = sanitizer::getInstance();

    if (isset($_POST['backup_filename'])) {
        $filename = $_POST['backup_filename'];
        $filename = $filter->sanitizeFilename($filename,true);
    }

    $table = COM_applyFilter($_POST['table']);

    if ( isset($_POST['start']) ) {
        $start = COM_applyFilter($_POST['start'],true);
    } else {
        $start = 0;
    }

    $backup = new dbBackup();
    $backup->setBackupFilename($filename);
    list($rc,$sessionCounter,$recordCounter) = $backup->backupTable($table,$_VARS['_dbback_allstructs'],$start);

    switch ( $rc ) {
        case 1 :
            $retval['errorCode'] = 2;
            $retval['startrecord'] = $recordCounter;
            $retval['processed'] = $sessionCounter;
            $return["js"] = json_encode($retval);
            echo json_encode($return);
            exit;
        case -2 :
            // serious error
            $retval['errorCode'] = 3;
            $return["js"] = json_encode($retval);
            echo json_encode($return);
            exit;
        default :
            $retval['errorCode'] = 0;
            $retval['processed'] = $sessionCounter;
            $return["js"] = json_encode($retval);
            echo json_encode($return);
            exit;
    }
    exit;
}


/**
* Prepare to backup
*
* @return   string  HTML form
*
*/
function DBADMIN_backupPrompt()
{
    global $_CONF, $_TABLES, $_VARS, $_IMAGE_TYPE, $LANG01, $LANG08, $LANG_ADMIN, $LANG_DB_ADMIN;

    $retval = '';

    if (is_writable($_CONF['backup_path'])) {

        $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
        $T->set_file('page','dbbackup.thtml');

        $lastrun = DB_getItem($_TABLES['vars'], 'UNIX_TIMESTAMP(value)',
                                  "name = 'db_backup_lastrun'");

        $menu_arr = array();

        $allInnoDB = DBADMIN_innodbStatus();
        $allMyIsam = DBADMIN_myisamStatus();

        $menu_arr = getAdminHeaderMenu('create_backup');

        $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                            COM_getBlockTemplate('_admin_block', 'header')));

        $T->set_var('admin_menu',ADMIN_createMenu(
            $menu_arr,
            "",
            $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE
        ));

        $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

        $T->set_var('security_token',SEC_createToken());
        $T->set_var('security_token_name',CSRF_TOKEN);

        if (!empty($lastrun)) {
            $last = COM_getUserDateTimeFormat($lastrun);
            $T->set_var('lang_last_backup',$LANG_DB_ADMIN['latest_backup']);
            $T->set_var('last_backup',$last[0]);
        }

        if ( isset($_VARS['_dbback_allstructs']) && $_VARS['_dbback_allstructs'] ) {
            $T->set_var('struct_warning',$LANG_DB_ADMIN['backup_warning']);
        }

        $timeout = (int) ini_get('max_execution_time');
        if ( $timeout == 0 ) $timeout = 60;
        $T->set_var('timeout', $timeout * 1000 );

        $T->set_var(array(
            'action'            => 'backup',
        	'lang_backingup'    => $LANG_DB_ADMIN['backingup'],
        	'lang_backup'       => $LANG_DB_ADMIN['do_backup'],
        	'lang_success'      => $LANG_DB_ADMIN['backup_successful'],
            'lang_cancel'       => $LANG_ADMIN['cancel'],
            'lang_ajax_status'  => $LANG_DB_ADMIN['backup_status'],
            'lang_backup_instructions' => $LANG_DB_ADMIN['backup_instructions'],
            'lang_title'        => $LANG_DB_ADMIN['backup_title'],
            'lang_ok'           => $LANG01['ok'],
        ));

        $T->parse('output', 'page');
        $retval .= $T->finish($T->get_var('output'));
    } else {
        $retval .= COM_startBlock($LANG08[06], '',
                            COM_getBlockTemplate('_msg_block', 'header'));
        $retval .= sprintf($LANG_DB_ADMIN['no_access'],$_CONF['backup_path']);
        Log::write('system',Log::ERROR,$_CONF['backup_path'] . ' is not writable.');
        $retval .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    }
    return $retval;
}


/**
* Perform database backup
*
* @return   string      HTML success or error message
*
*/
function DBADMIN_backup()
{
    global $_CONF, $LANG08, $LANG_DB_ADMIN, $MESSAGE, $_IMAGE_TYPE,
           $_DB_host, $_DB_name, $_DB_user, $_DB_pass;

    $retval = '';

    $backup = new dbBackup();
    $backup->perform_backup();
    $backup->Purge();

    $retval .= DBADMIN_list();

    return $retval;
}

/**
* Download a backup file
*
* @param    string  $file   Filename (without the path)
* @return   void
* @note     Filename should have been sanitized and checked before calling this.
*
*/
function DBADMIN_download($file)
{
    global $_CONF;

    $dl = new downloader;

    $dl->setLogFile($_CONF['path'] . 'logs/error.log');
    $dl->setLogging(true);
    $dl->setDebug(true);

    $dl->setPath($_CONF['backup_path']);
    $dl->setAllowedExtensions(array(
            'sql' =>  'application/x-gzip-compressed',
            'gz'  =>  'application/x-gzip-compressed',
    ));
    $dl->downloadFile($file);
}

/**
* Check for DB storage engine support
*
* @return   true = if engine is supported, false = not supported
*
*/
function DBADMIN_supported_engine( $type = 'MyISAM' )
{
    $retval = false;

    if ( $type != 'MyISAM' || $type != 'InnoDB' ) {
        $type = 'MyISAM';
    }

    $result = DB_query("SHOW STORAGE ENGINES");
    $numEngines = DB_numRows($result);
    for ($i = 0; $i < $numEngines; $i++) {
        $A = DB_fetchArray($result);

        if (strcasecmp($A['Engine'], $type ) == 0) {
            if ((strcasecmp($A['Support'], 'yes') == 0) ||
                (strcasecmp($A['Support'], 'default') == 0)) {
                $retval = true;
            }
            break;
        }
    }

    return $retval;
}



function DBADMIN_innodbStatus()
{
    global $_CONF, $_TABLES, $_DB_name;

    $retval = false;

    $engine = DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'");
    if (!empty($engine) && ($engine == 'InnoDB')) {
        // need to look at all the tables
        $tableList = DBADMIN_getTableList();
        foreach ( $tableList AS $table ) {
            $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
            $B = DB_fetchArray($result2);
            if (strcasecmp($B['Engine'], 'InnoDB') != 0) {
                return false;
            }
        }
        $retval = true;
    }
    return $retval;
}

function DBADMIN_myisamStatus()
{
    global $_CONF, $_TABLES, $_DB_name;

    $retval = false;

    $tableList = DBADMIN_getTableList();
    foreach ( $tableList AS $table ) {
        $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
        $B = DB_fetchArray($result2);
        if (strcasecmp($B['Engine'], 'MyISAM') != 0) {
            return false;
        }
    }
    return true;
}


function DBADMIN_innodb()
{
    global $_CONF, $LANG01, $LANG_ADMIN, $LANG_DB_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    if ( defined('DEMO_MODE') ) return DBADMIN_list();

    $allInnoDB = DBADMIN_innodbStatus();
    $allMyIsam = DBADMIN_myisamStatus();

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbconvert.thtml');

    $menu_arr = getAdminHeaderMenu('convert_menu');

    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                "",
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );
    $timeout = (int) ini_get('max_execution_time');
    if ( $timeout == 0 ) $timeout = 60;
    $T->set_var('timeout', $timeout * 1000 );

    $T->set_var('lang_title',$LANG_DB_ADMIN['convert_title']);
    $T->set_var('lang_conversion_instructions',$LANG_DB_ADMIN['innodb_instructions']);
    if (DBADMIN_innodbStatus()) {
        $T->set_var('lang_conversion_status',$LANG_DB_ADMIN['already_converted']);
    } else {
        $T->set_var('lang_conversion_status',$LANG_DB_ADMIN['conversion_message']);
    }
    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'lang_convert'      => $LANG_DB_ADMIN['convert_button'],
        'lang_cancel'       => $LANG_ADMIN['cancel'],
        'lang_ok'           => $LANG01['ok'],
        'lang_converting'   => $LANG_DB_ADMIN['converting'],
        'lang_success'      => $LANG_DB_ADMIN['innodb_success'],
        'lang_ajax_status'  => $LANG_DB_ADMIN['conversion_status'],
        'lang_errors'       => $LANG_DB_ADMIN['error_msg'],
        'to_engine'         => 'InnoDB',
        'action'            => "doinnodb",
        'mode'              => "convertdb",
    ));
    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function DBADMIN_myisam()
{
    global $_CONF, $LANG01, $LANG_ADMIN, $LANG_DB_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    if ( defined('DEMO_MODE') ) return DBADMIN_list();

    $allInnoDB = DBADMIN_innodbStatus();

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbconvert.thtml');

    $menu_arr = getAdminHeaderMenu('convert_myisam_menu');

    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                "",
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );
    $timeout = (int) ini_get('max_execution_time');
    if ( $timeout == 0 ) $timeout = 60;
    $T->set_var('timeout', $timeout * 1000 );

    $T->set_var('lang_title',$LANG_DB_ADMIN['convert_myisam_title']);
    $T->set_var('lang_conversion_instructions',$LANG_DB_ADMIN['myisam_instructions']);
    if (DBADMIN_myisamStatus()) {
        $T->set_var('lang_conversion_status',$LANG_DB_ADMIN['already_converted']);
    } else {
        $T->set_var('lang_conversion_status',$LANG_DB_ADMIN['conversion_message']);
    }
    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'lang_convert'      => $LANG_DB_ADMIN['convert_button'],
        'lang_cancel'       => $LANG_ADMIN['cancel'],
        'lang_ok'           => $LANG01['ok'],
        'lang_converting'   => $LANG_DB_ADMIN['converting'],
        'lang_success'      => $LANG_DB_ADMIN['myisam_success'],
        'lang_ajax_status'  => $LANG_DB_ADMIN['conversion_status'],
        'lang_errors'       => $LANG_DB_ADMIN['error_msg'],
        'to_engine'         => 'MyISAM',
        'action'            => "domyisam",
        'mode'              => "convertdb",
    ));
    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}


/**
* Convert to InnoDB tables
*
* @param    string  $startwith  table to start with
* @param    int     $failures   number of previous errors
* @return   int                 number of errors during conversion
*
*/
function DBADMIN_convert_innodb($startwith = '', $failures = 0)
{
    global $_CONF, $_TABLES, $_DB_name;

    $retval = '';
    $start = time();

    if ( defined('DEMO_MODE') ) return DBADMIN_list();

    DB_displayError(true);

    $maxtime = @ini_get('max_execution_time');
    if (empty($maxtime)) {
        // unlimited or not allowed to query - assume 30 second default
        $maxtime = 30;
    }
    $maxtime -= 5; // give us some leeway

    $token = ''; // SEC_createToken();

    $tableList = DBADMIN_getTableList();
    foreach ( $tableList AS $table ) {
        if (! empty($startwith)) {
            if ($table == $startwith) {
                $startwith = '';
            } else {
                continue; // handled - skip
            }
        }

        $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
        $B = DB_fetchArray($result2);
        if (strcasecmp($B['Engine'], 'InnoDB') == 0) {
            continue; // converted - skip
        }

        if (time() > $start + $maxtime) {
            // this is taking too long - kick off another request
            $startwith = $table;
            $url = $_CONF['site_admin_url'] . '/database.php?doinnodb=x';
            if (! empty($token)) {
                $token = '&' . CSRF_TOKEN . '=' . $token;
            }
            header("Location: $url&startwith=$startwith&failures=$failures"
                              . $token);
            exit;
        }

        $sql = "ALTER TABLE ".$table." ENGINE=InnoDB";
        $make_innodb = DB_query($sql, 1);
        if ($make_innodb === false) {
            $failures++;
            Log::write('system',Log::ERROR,'SQL error for table "' . $table . '" (ignored): '.DB_error($sql));
        }
    }

    DB_delete($_TABLES['vars'], 'name', 'database_engine');
    DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('database_engine', 'InnoDB')");

    return $failures;
}

function DBADMIN_ajaxFinishCvt($engine)
{
    global $_CONF, $_TABLES;

    switch ($engine) {
        case 'InnoDB' :
            DB_delete($_TABLES['vars'], 'name', 'database_engine');
            DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('database_engine', 'InnoDB')");
            break;
       case 'MyISAM' :
            DB_delete($_TABLES['vars'], 'name', 'database_engine');
            break;
    }
    return true;
}


/**
* Convert to MyISAM tables
*
* @param    string  $startwith  table to start with
* @param    int     $failures   number of previous errors
* @return   int                 number of errors during conversion
*
*/
function DBADMIN_convert_myisam($startwith = '', $failures = 0)
{
    global $_CONF, $_TABLES, $_DB_name;

    $retval = '';
    $start = time();

    if ( defined('DEMO_MODE') ) return DBADMIN_list();

    DB_displayError(true);

    $maxtime = @ini_get('max_execution_time');
    if (empty($maxtime)) {
        // unlimited or not allowed to query - assume 30 second default
        $maxtime = 30;
    }
    $maxtime -= 5; // give us some leeway

    $token = ''; // SEC_createToken();

    $tableList = DBADMIN_getTableList();
    foreach ( $tableList AS $table ) {
        if (! empty($startwith)) {
            if ($table == $startwith) {
                $startwith = '';
            } else {
                continue; // handled - skip
            }
        }

        $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
        $B = DB_fetchArray($result2);
        if (strcasecmp($B['Engine'], 'MyISAM') == 0) {
            continue; // converted - skip
        }

        if (time() > $start + $maxtime) {
            // this is taking too long - kick off another request
            $startwith = $table;
            $url = $_CONF['site_admin_url'] . '/database.php?domyisam=x';
            if (! empty($token)) {
                $token = '&' . CSRF_TOKEN . '=' . $token;
            }
            header("Location: $url&startwith=$startwith&failures=$failures"
                              . $token);
            exit;
        }

        $sql = "ALTER TABLE ".$table." ENGINE=MyISAM";
        $make_myisam = DB_query($sql, 1);
        if ($make_myisam === false) {
            $failures++;
            Log::write('system',Log::ERROR,'SQL error for table "' . $table . '" (ignored): '.DB_error($sql));
        }
    }

    DB_delete($_TABLES['vars'], 'name', 'database_engine');

    return $failures;
}

/**
* Prepare for optimizing tables
*
* @return   string  HTML form
*
*/
function DBADMIN_optimize()
{
    global $_CONF, $_TABLES, $LANG01, $LANG_ADMIN, $LANG_DB_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    $allInnoDB = DBADMIN_innodbStatus();
    $allMyIsam = DBADMIN_myisamStatus();

    $lastrun = DB_getItem($_TABLES['vars'], 'UNIX_TIMESTAMP(value)',
                          "name = 'lastoptimizeddb'");

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbconvert.thtml');

    $menu_arr = getAdminHeaderMenu('optimize_menu');

    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                "",
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );

    $T->set_var('lang_title',$LANG_DB_ADMIN['optimize_title']);
    $T->set_var('lang_conversion_instructions',$LANG_DB_ADMIN['optimize_explain']);
    $T->set_var('lang_conversion_status',$LANG_DB_ADMIN['optimization_message']);

    if (!empty($lastrun)) {
        $last = COM_getUserDateTimeFormat($lastrun);
        $T->set_var('lang_last_optimization',$LANG_DB_ADMIN['last_optimization']);
        $T->set_var('last_optimization',$last[0]);
    }
    $timeout = (int) ini_get('max_execution_time');
    if ( $timeout == 0 ) $timeout = 60;
    $T->set_var('timeout', $timeout * 1000 );

    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'lang_convert'      => $LANG_DB_ADMIN['optimize_button'],
        'lang_cancel'       => $LANG_ADMIN['cancel'],
        'lang_ok'           => $LANG01['ok'],
        'lang_converting'   => $LANG_DB_ADMIN['optimizing'],
        'lang_success'      => $LANG_DB_ADMIN['optimize_success'],
        'lang_ajax_status'  => $LANG_DB_ADMIN['optimization_status'],
        'lang_errors'       => $LANG_DB_ADMIN['error_msg'],
        'to_engine'         => 'all',
        'action'            => "dooptimize",
        'mode'              => "optimize",
    ));
    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

/**
* Optimize database tables
*
* @param    string  $startwith  table to start with
* @param    int     $failures   number of previous errors
* @return   int                 number of errors during conversion
*
*/
function DBADMIN_dooptimize($startwith = '', $failures = 0)
{
    global $_CONF, $_TABLES;

    $retval = '';
    $start = time();

    $lasttable = DB_getItem($_TABLES['vars'], 'value',
                            "name = 'lastoptimizedtable'");
    if (empty($startwith) && !empty($lasttable)) {
        $startwith = $lasttable;
    }

    $maxtime = @ini_get('max_execution_time');
    if (empty($maxtime)) {
        // unlimited or not allowed to query - assume 30 second default
        $maxtime = 30;
    }
    $maxtime -= 5;

    DB_displayError(true);

    $token = ''; // SEC_createToken();

    $tableList = DBADMIN_getTableList();
    foreach ( $tableList AS $table ) {
        if (! empty($startwith)) {
            if ($table == $startwith) {
                $startwith = '';
            } else {
                continue; // already handled - skip
            }
            if (!empty($lasttable) && ($lasttable == $table)) {
                continue; // skip
            }
        }

        if (time() > $start + $maxtime) {
            // this is taking too long - kick off another request
            $startwith = $table;
            $url = $_CONF['site_admin_url']
                 . '/database.php?dooptimize=x';
            if (! empty($token)) {
                $token = '&' . CSRF_TOKEN . '=' . $token;
            }
            header("Location: $url&startwith=$startwith&failures=$failures"
                              . $token);
            exit;
        }

        if (empty($lasttable)) {
            DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('lastoptimizedtable', '$table')");
            $lasttable = $table;
        } else {
            DB_query("UPDATE {$_TABLES['vars']} SET value = '$table' WHERE name = 'lastoptimizedtable'");
        }
        $sql = "OPTIMIZE TABLE " . $table;
        $optimize = DB_query($sql, 1);
        if ($optimize === false) {
            $failures++;
            Log::write('system',Log::ERROR,'SQL error for table "' . $table . '" (ignored): '.DB_error($sql));

            $startwith = $table;
            $url = $_CONF['site_admin_url']
                 . '/database.php?dooptimize=x';
            if (! empty($token)) {
                $token = '&' . CSRF_TOKEN . '=' . $token;
            }
            header("Location: $url&startwith=$startwith&failures=$failures"
                              . $token);
            exit;
        }
    }

    DB_delete($_TABLES['vars'], 'name', 'lastoptimizedtable');
    DB_delete($_TABLES['vars'], 'name', 'lastoptimizeddb');
    DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('lastoptimizeddb', FROM_UNIXTIME(" . time() . "))");

    return $failures;
}

function DBADMIN_alterEngine($table_name, $engine = 'MyISAM')
{
    global $_CONF;

    $retval = true;
    $sql = "ALTER TABLE ".$table_name." ENGINE=".$engine;
    $convert_engine = DB_query($sql, 1);
    if ($convert_engine === false) {
        $retval = false;
        Log::write('system',Log::ERROR,'SQL error converting table "' . $table_name . '" to '.$engine.' (ignored): '.DB_error($sql));
    }
    return $retval;
}

function DBADMIN_ajaxConvertTable( $table, $engine = 'MyISAM')
{
    if ( !COM_isAjax()) die();

    $retval = array();
    $return = array();

    $rc = DBADMIN_alterEngine($table,$engine);
    if ( $rc === false ) {
        $retval['errorCode'] = 1;
        $retval['statusMessage'] = 'Failure: '.$table.' was not converted to '.$engine;
    } else {
        $retval['errorCode'] = 0;
    }

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function DBADMIN_ajaxOptimizeTable( $table )
{
    global $_DB_name;
    if ( !COM_isAjax()) die();

    if ( defined('DEMO_MODE') ) {
        $retval['errorCode'] = 0;
        $return["js"] = json_encode($retval);
        echo json_encode($return);
        exit;
    }

    $retval = array();
    $return = array();
/* - allow optimization on innodb tables
    $result = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
    $I = DB_fetchArray($result);
    if (strcasecmp($I['Engine'], 'InnoDB') == 0) {
        $rc = true;
        Log::write('system',Log::INFO,"DBadmin: Skipping optimization for table '".$table."' because it is a InnoDB table");
    } else {
        $rc = DB_query("OPTIMIZE TABLE $table", 1);
    }
*/
    $rc = DB_query("OPTIMIZE TABLE $table", 1);
    if ( $rc == false ) {
        $retval['errorCode'] = 1;
        $retval['statusMessage'] = 'Failure: '.$table.' was not optimized.';
        Log::write('system',Log::INFO,"DBadmin: Error optimizing table " . $table);
    } else {
        $retval['errorCode'] = 0;
    }

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function DBADMIN_ajaxGetTableList($engine = 'MyISAM')
{
    global $_CONF, $_TABLES, $_DB_name, $_DB_table_prefix;

    $tableList = array();
    $retval = array();

    if ( !COM_isAjax()) die();

    $result = DB_query("SHOW TABLES");
    $numTables = DB_numRows($result);
    for ($i = 0; $i < $numTables; $i++) {
        $A = DB_fetchArray($result, true);
        $table = $A[0];
        $tblPrefix = substr($table,0,strlen($_DB_table_prefix));
        if ($tblPrefix == $_DB_table_prefix ) { // in_array($table, $_TABLES)) {
            $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
            $B = DB_fetchArray($result2);
            if (strcasecmp($B['Engine'], $engine) == 0) {
                continue;
            }
            $tableList[] = $table;
        }
    }

    $retval['errorCode'] = 0;
    $retval['tablelist'] = $tableList;

    $return["js"] = json_encode($retval);

    echo json_encode($return);
    exit;
}

function DBADMIN_validateEngine( $engine )
{
    $validEngineTypes = array('MyISAM', 'InnoDB');

    if ( in_array($engine,$validEngineTypes)) return true;
    return false;
}

/**
*   Provide an interface to configure backups
*
*   @return string  HTML for configuration function
*/
function DBADMIN_configBackup()
{
    global $_CONF, $_TABLES, $_VARS, $LANG_DB_ADMIN, $LANG_ADMIN, $_IMAGE_TYPE;

    $allInnoDB = DBADMIN_innodbStatus();
    $allMyIsam = DBADMIN_myisamStatus();
    $tablenames = DBADMIN_getTableList();
    $included = '';
    $excluded = '';
    $retval   = '';

    $cfg =& config::get_instance();
    $_dbCfg = $cfg->get_config('dbadmin_internal');
    $exclude_tables = array();
    if ( isset($_dbCfg['dbback_exclude'])) {
        $exclude_tables = $_dbCfg['dbback_exclude'];
    }
    if (!is_array($exclude_tables)) {
        $exclude_tables = array();
    }

    $chk_gzip = (isset($_VARS['_dbback_gzip']) &&
            $_VARS['_dbback_gzip'] == 1) ? ' checked="checked" ' : '';

    $chk_allstructs = (isset($_VARS['_dbback_allstructs']) &&
            $_VARS['_dbback_allstructs'] == 1) ? ' checked="checked" ' : '';

    $max_files = (isset($_VARS['_dbback_files']) ? (int)$_VARS['_dbback_files'] : 0);

    $menu_arr = getAdminHeaderMenu('configure');

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbbackupcfg.thtml');

    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                $LANG_DB_ADMIN['config_instructions'],
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );

    $include_tables = array_diff($tablenames, $exclude_tables);

    foreach ($include_tables as $key=>$name) {
        if ( $name != '' ) {
            $included .= "<option value=\"$name\">$name</option>\n";
        }
    }
    foreach ($exclude_tables as $key=>$name) {
        if ( $name != '' ) {
            $excluded .= "<option value=\"$name\">$name</option>\n";
        }
    }

    $T->set_var(array(
        'lang_tables_to_backup' => $LANG_DB_ADMIN['tables_to_backup'],
        'lang_include'          => $LANG_DB_ADMIN['include'],
        'lang_exclude'          => $LANG_DB_ADMIN['exclude'],
        'lang_options'          => $LANG_DB_ADMIN['options'],
        'lang_struct_only'      => $LANG_DB_ADMIN['struct_only'],
        'lang_max_files'        => $LANG_DB_ADMIN['max_files'],
        'lang_disable_purge'    => $LANG_DB_ADMIN['disable_purge'],
        'lang_use_gzip'         => $LANG_DB_ADMIN['use_gzip'],
        'lang_save'             => $LANG_ADMIN['save'],
        'included_tables'       => $included,
        'excluded_tables'       => $excluded,
        'max_files'             => $max_files,
        'chk_gzip'              => $chk_gzip,
        'chk_allstructs'        => $chk_allstructs,
    ) );

    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function DBADMIN_getTableList($engine = '')
{
    global $_CONF, $_TABLES, $_DB_name, $_DB_table_prefix;

    $tableList = array();

    $result = DB_query("SHOW TABLES");
    $numTables = DB_numRows($result);
    for ($i = 0; $i < $numTables; $i++) {
        $A = DB_fetchArray($result, true);
        $table = $A[0];
        $tblPrefix = substr($table,0,strlen($_DB_table_prefix));
        if ($tblPrefix == $_DB_table_prefix ) {
            $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
            $B = DB_fetchArray($result2);
            if ( $engine != '' ) {
                if (strcasecmp($B['Engine'], $engine) == 0) {
                    continue;
                }
            }
            $tableList[] = $table;
        }
    }
    return $tableList;

}

function DBADMIN_utf8mb4()
{
    global $_CONF, $LANG01, $LANG_ADMIN, $LANG_DB_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    $allInnoDB = DBADMIN_innodbStatus();
    $allMyIsam = DBADMIN_myisamStatus();

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbconvert-utf.thtml');

    $menu_arr = getAdminHeaderMenu('utf8_title');

    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                "",
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );

    $result = DB_query("SELECT @@character_set_database, @@collation_database;",1);
    if ( $result ) {
        $row = DB_fetchArray($result);
        $collation_database = $row["@@collation_database"];
        $charset_database = $row["@@character_set_database"];
    }

    $T->set_var('lang_title',$LANG_DB_ADMIN['utf8_title']);

    $cnv_instr = sprintf($LANG_DB_ADMIN['utf8_instructions'],$collation_database, $charset_database);

    $T->set_var('lang_conversion_instructions', $cnv_instr);

    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'].'/dbutf.php',
        'current_char_set'  => $charset_database,
        'current_collation' => $collation_database,
        'lang_convert'      => $LANG_DB_ADMIN['convert_button'],
        'lang_cancel'       => $LANG_ADMIN['cancel'],
        'lang_ok'           => $LANG01['ok'],
        'lang_converting'   => $LANG_DB_ADMIN['converting'],
        'lang_success'      => $LANG_DB_ADMIN['utf8_success'],
        'lang_ajax_status'  => $LANG_DB_ADMIN['conversion_status'],
        'lang_error_gettable' => $LANG_DB_ADMIN['retrieve_tables'],
        'lang_error_header' => $LANG_DB_ADMIN['error_heading'],
        'lang_no_errors'    => $LANG_DB_ADMIN['no_errors'],
        'lang_error_db'     => $LANG_DB_ADMIN['error_db_utf'],
        'lang_error_table'  => $LANG_DB_ADMIN['error_table_utf'],
        'lang_error_getcolumn' => $LANG_DB_ADMIN['error_column_utf'],
        'lang_sc_error'     => $LANG_DB_ADMIN['error_sc'],
        'lang_current_progress' => $LANG_DB_ADMIN['current_progress'],
        'lang_overall_progress' => $LANG_DB_ADMIN['overall_progress'],
    ));
    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}


function DBADMIN_supportUtf8mb()
{
    global $_CONF, $_TABLES, $_DB_name;

    $collation_database = '';

    $result = DB_query("SELECT @@character_set_database, @@collation_database;",1);
    if ( $result ) {
        $collation = DB_fetchArray($result);
        $collation_database = substr($collation["@@collation_database"],0,4);
    }

    if ( $collation_database != "utf8" ) {
        return false;
    }
    $serverVersion = DB_getServerVersion();

    if (version_compare($serverVersion,'5.6.0','<')) {
        return false;
    }
    $clientVersion = DB_getClientVersion();
    $dbDriver = DB_getDriverName();

    if ($dbDriver == 'pdo_mysql') {
        if (version_compare($clientVersion,'5.0.9','<')) {
            return false;
        }
    } elseif (function_exists('mysqli_get_client_stats')) {
        // mysqlnd
        if (version_compare($clientVersion,'5.0.9','<')) {
            return false;
        }
    } else {
        if (version_compare($clientVersion,'5.5.3','<')) {
            return false;
        }
    }
    return true;
}

function getAdminHeaderMenu( $activeItem = '' )
{
    global $_CONF, $LANG_ADMIN, $LANG_DB_ADMIN;

    $allInnoDB = DBADMIN_innodbStatus();
    $allMyIsam = DBADMIN_myisamStatus();
    $dbVersion = DB_getServerVersion();

    $menu_items = array(
        'database_admin' => array(
                'url'  => $_CONF['site_admin_url'].'/database.php',
                'text' => $LANG_DB_ADMIN['database_admin']
                ),
        'create_backup' => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?backupdb=x',
                'text'  => $LANG_DB_ADMIN['create_backup']
                ),
        'optimize_menu' => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?optimize=x',
                'text'  => $LANG_DB_ADMIN['optimize_menu']
                ),
        'convert_menu'  => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?innodb=x',
                'text'  => $LANG_DB_ADMIN['convert_menu']
                ),
        'convert_myisam_menu' => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?myisam=x',
                'text'  => $LANG_DB_ADMIN['convert_myisam_menu']
                ),
        'utf8_title'    => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?utf8mb4=x',
                'text'  => $LANG_DB_ADMIN['utf8_title']
                ),
        'sr_menu'    => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?sr=x',
                'text'  => 'Search/Replace'
                ),

        'configure'     => array(
                'url'   => $_CONF['site_admin_url'].'/database.php?config=x',
                'text'  => $LANG_DB_ADMIN['configure']
                ),
        'admin_home'    => array(
                'url' => $_CONF['site_admin_url'].'/index.php',
                'text' => $LANG_ADMIN['admin_home']
                )
    );
    $menu_arr = array();
    foreach ($menu_items AS $item => $info ) {
        $active = 0;
        if ( $activeItem == $item ) $active = 1;
        if ( $item == 'convert_menu' ) {
            if ( !$allInnoDB && DBADMIN_supported_engine( 'InnoDB' ) ) {
                $menu_arr[] = array(
                                'url' => $info['url'],
                                'text'=> $info['text'],
                                'active' => $active
                            );
            }
        } else if ( $item == 'convert_myisam_menu' ) {
            if ( !$allMyIsam && DBADMIN_supported_engine( 'MyISAM' ) ) {
                $menu_arr[] = array(
                                'url' => $info['url'],
                                'text'=> $info['text'],
                                'active' => $active
                            );
            }
        } else if ( $item == 'utf8_title' ) {
            if (version_compare($dbVersion,'5.3.3','>=')) {
                $menu_arr[] = array(
                                'url' => $info['url'],
                                'text'=> $info['text'],
                                'active' => $active
                            );
            }
        } else {
            $menu_arr[] = array(
                            'url' => $info['url'],
                            'text'=> $info['text'],
                            'active' => $active
                        );
        }
    }

    return $menu_arr;
}


/**
* Search / Replace
*
* @return   string  HTML form
*
*/
function DBADMIN_searchAndreplace($err = '')
{
    global $_CONF, $_TABLES, $LANG01, $LANG_ADMIN, $LANG_DB_ADMIN, $_IMAGE_TYPE;

    $filter = new sanitizer();

    $retval = '';
    $tables = '';
    $includetables = '';

    // let's see if we are returning from somewhere else - if the POST vars are set
    // load them up

    if (isset($_POST['groupmembers'])) {
        $includedTables = explode('|', $_POST['groupmembers']);
    } else {
        $includedTables = array();
    }
    if (isset($_POST['searchfor'])) {
        $searchFor = $_POST['searchfor'];
    } else {
        $searchFor = '';
    }
    if (isset($_POST['replacewith'])) {
        $replaceWith = $_POST['replacewith'];
    } else {
        $replaceWith = '';
    }
    if (isset($_POST['dryrun'])) {
        $dryRun = 1;
    } else {
        $dryRun = 1;
    }
    if (isset($_POST['caseinsensitive'])) {
        $caseInsensitive = 1;
    } else {
        $caseInsensitive = 0;
    }

    $tablenames = DBADMIN_getTableList();

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbsearchreplace.thtml');

    $menu_arr = getAdminHeaderMenu('sr_menu');

    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'] . ' Search and Replace', '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                "",
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );

    $itemsToProcess = PLG_getContentTableInfo();

    $glFusionItems = array(
        'gl_stories' => array(
            'plugin' => 'glFusion',
            'primary_key' => 'id',
            'columns' => array(
                'title',
                'subtitle',
                'introtext',
                'bodytext',
                'story_image',
                'story_video',
            )
        ),
        'gl_comments' => array(
            'plugin' => 'glFusion',
            'primary_key' => 'cid',
            'columns' => array(
                'title',
                'comment'
            )
        )
    );
    $itemsToProcess = array_merge($itemsToProcess,$glFusionItems);

    $granularArray = array();
    foreach($itemsToProcess AS $item => $itemInfo) {
        foreach($itemInfo['columns'] AS $column) {
            $granularArray[] = array(
                'value' => $item . ','. $column,
                'desc' => $itemInfo['plugin']
            );
        }
    }
    krsort($granularArray);

    foreach ($granularArray AS $data) {
        list($table, $column) = explode(',',$data['value']);
        if ( in_array($data['value'], $includedTables)) {
            $includetables .= '<option value="'.$data['value'].'">'.$data['desc'] . ' :: ' . $table.' -> '. $column ;
        } else {
            $tables .= '<option value="'.$data['value'].'">'.$data['desc'] . ' :: ' . $table.' -> '. $column ;
        }
    }

    $T->set_var('glfusion_tables',$tables);

    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'lang_title'        => $LANG_DB_ADMIN['sr_title'],
        'lang_search_for'   => $LANG_DB_ADMIN['search_for'],
        'lang_replace_with' => $LANG_DB_ADMIN['replace_with'],
        'lang_tables_to_search' => $LANG_DB_ADMIN['tables_to_search'],
        'lang_search'       => $LANG_DB_ADMIN['search'],
        'lang_remove'       => $LANG_DB_ADMIN['remove'],
        'lang_case'         => $LANG_DB_ADMIN['case'],
        'lang_dry_run'      => $LANG_DB_ADMIN['dry_run'],
        'lang_available_tables' => $LANG_DB_ADMIN['available_tables'],
        'lang_execute'      => $LANG_DB_ADMIN['execute'],
        'lang_sr_warning_banner' => $LANG_DB_ADMIN['sr_warning_banner'],
        'lang_plugin_table_column' => $LANG_DB_ADMIN['plugin_table_column'],
        'action'            => 'searchreplace',
        'mode'              => "searchreplace",
    ));

    $T->set_var(array(
        'includedtables'    => $includetables,
        'replacewith'       => $filter->editableText($replaceWith),
        'searchfor'         => $filter->editableText($searchFor),
    ));

    if ( $caseInsensitive ) {
        $T->set_var('caseinsensitive_checked', ' checked="checked" ');
    } else {
        $T->set_var('caseinsensitive_checked', '');
    }
    if ( $dryRun ) {
        $T->set_var('dryrun_checked', ' checked="checked" ');
    } else {
        $T->set_var('dryrun_checked', '');
    }

    if (!empty($err)) {
        $T->set_var('error_message',$err);
    }

    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function DBADMIN_srExecute()
{
    global $_CONF, $LANG_DB_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    $filter = new sanitizer();

    $selectedTables = explode('|', $_POST['groupmembers']);

    if (!isset($_POST['searchfor']) || empty($_POST['searchfor'])) {
        return DBADMIN_searchAndreplace($LANG_DB_ADMIN['missing_required']);
    }
    if (!isset($_POST['replacewith']) || empty($_POST['replacewith'])) {
        return DBADMIN_searchAndreplace($LANG_DB_ADMIN['missing_required']);
    }
    if (count($selectedTables) < 1) {
        return DBADMIN_searchAndreplace($LANG_DB_ADMIN['missing_required']);
    }

    $args = array();
    $args['search_for'] 	    = $_POST['searchfor'];
    $args['replace_with'] 	    = $_POST['replacewith'];
    $args['dry_run']            = isset($_POST['dryrun']) ? $_POST['dryrun'] : '';
    $args['case_insensitive']   = isset($_POST['caseinsensitive']) ? $_POST['caseinsensitive'] : '';

    if ($args['dry_run'] !== 'on' ) {
        $aaMessage = 'Replaced ' . $args['search_for'] . ' with ' . $args['replace_with'] . ' on ' . $_POST['groupmembers'];
        AdminAction::write('system','replace',$aaMessage);
    }
    $dbsr = new dbsr();

    $tablesToProcess = array();  // the tables selected by the user
    $columnsToProcess = array(); // table-> columns selected by user
    foreach($selectedTables AS $pair) {
        list($table, $column) = explode(',',$pair);
        $tablesToProcess[] = $table;
        $columnsToProcess[$table]['columns'][] = $column;
    }

    // this needs to pass $selectedTables which is just an array of tables only.
    // need to build out a new array to handle this.
    $allTables = $dbsr->getTableList($tablesToProcess);

    $availablePluginTables = PLG_getContentTableInfo();

    $availableglFusionTables = array(
        'gl_stories' => array(
            'plugin' => 'glFusion',
            'primary_key' => 'id',
            'columns' => array(
                'title',
                'subtitle',
                'introtext',
                'bodytext',
                'story_image',
                'story_video',
            )
        ),
        'gl_comments' => array(
            'plugin' => 'glFusion',
            'primary_key' => 'cid',
            'columns' => array(
                'title',
                'comment'
            )
        )
    );

    // add the glFusion Items

    $allAvailableTables = array_merge($availablePluginTables, $availableglFusionTables);

    $finalList = array();
    foreach($allTables AS $table) {
        if (in_array($table,$tablesToProcess) && isset($allAvailableTables[$table])) {
            $finalList[$table] = $allAvailableTables[$table];
        }
    }

    // now we have our final list of selected tables with all of the columns - need to trim down the columns
    // to just those selected.

    foreach($finalList AS $table => $data) {
        $index = 0;
        foreach($data['columns'] AS $column) {
            if (!in_array($column,$columnsToProcess[$table]['columns'])) {
                unset($finalList[$table]['columns'][$index]);
                $index++;
            }
        }
    }

    // DONE!  $finalList() has all the items we are supposed to process

    $resultReport = array();
    foreach($finalList AS $table => $details) {
        $report = $dbsr->srdb( $table, $details['primary_key'], $details['columns'], $args );
        $resultReport[$table] = $report;
    }

    $T = new Template($_CONF['path_layout'] . 'admin/dbadmin');
    $T->set_file('page','dbsr-results.thtml');

    $menu_arr = getAdminHeaderMenu('sr_menu');
    $T->set_var('start_block', COM_startBlock($LANG_DB_ADMIN['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));
    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                "",
                $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE)
    );

    $T->set_var(array(
        'lang_title'            => $LANG_DB_ADMIN['sr_title'],
        'lang_time'             => $LANG_DB_ADMIN['time'],
        'lang_table'            => $LANG_DB_ADMIN['table'],
        'lang_changes_found'    => $LANG_DB_ADMIN['changes_found'],
        'lang_rows_updated'     => $LANG_DB_ADMIN['rows_updated'],
        'lang_dry_run_complete' => $LANG_DB_ADMIN['dry_run_complete'],
        'lang_sr_parameters'    => $LANG_DB_ADMIN['sr_parameters'],
        'lang_search_for'       => $LANG_DB_ADMIN['search_for'],
        'lang_replace_with'     => $LANG_DB_ADMIN['replace_with'],
        'lang_case'             => $LANG_DB_ADMIN['case'],
        'lang_sr_warning_1'     => $LANG_DB_ADMIN['sr_warning_1'],
        'lang_sr_warning_2'     => $LANG_DB_ADMIN['sr_warning_2'],
        'lang_edit'             => $LANG_DB_ADMIN['edit'],
        'lang_cancel'           => $LANG_DB_ADMIN['cancel'],
        'lang_seconds'          => $LANG_DB_ADMIN['seconds'],
        'case'                  => $args['case_insensitive'] == 'on' ? 'Yes' : 'No',
    ));

    if ($args['dry_run'] === 'on') {
        $T->set_var('dry_run_results',true);

        $T->set_var(array(
            'includedtables'    => $_POST['groupmembers'],
            'searchfor'         => $filter->editableText($args['search_for']),
            'replacewith'       => $filter->editableText($args['replace_with']),
            'dryrun'            => $args['dry_run'],
            'caseinsensitive'   => $args['case_insensitive'],
            'lang_execute'      => $LANG_DB_ADMIN['execute'],
        ));
        if ($args['case_insensitive'] == 'on') {
            $T->set_var('caseinsensitive','on');
        } else {
            $T->unset_var('caseinsensitive');
        }
    } else {
        $T->unset_var('dry_run_results');
    }

    $T->set_block('page', 'ReportRow', 'RRow');
    $T->set_block('page', 'DiffTableRow', 'DTRow');
    $T->set_block('page', 'DiffRow', 'DRow');

    foreach($resultReport AS $table => $tableRun) {
        $T->set_var(array(
            'table'         => $table,
            'changes'       => $tableRun['table_report']['change'],
            'rowsupdated'   => $tableRun['table_report']['updates'],
            'time'          => round((float) ($tableRun['table_report']['end'] - $tableRun['table_report']['start']),5),
            'errors'        => count($tableRun['table_report']['errors']),
        ));
        if (count($tableRun['table_report']['errors']) > 0) {
            $T->set_var('showerrors',true);
        } else {
            $T->unset_var('showerrors');
        }
        if (isset($tableRun['table_report']['diffs'])) {
            if (count($tableRun['table_report']['diffs']) > 0) {
                $T->set_var('showdiffs',true);
            } else {
                $T->unset_var('showdiffs');
            }
        }
        $T->parse('RRow','ReportRow',true);
        if ( isset($tableRun['table_report']['diffs'])) {
            foreach($tableRun['table_report']['diffs'] AS $diff) {
                $T->set_var(array(
                    'table' => $table,
                    'column' => $diff[2],
                ));
                if (strlen($diff[0]) < 10000 && strlen($diff[1]) < 10000) {
                    $T->set_var(
                        'diff', \Diff::toTable(\Diff::compare($diff[0],$diff[1])),
                    );
                    $T->unset_var('truncated');
                } else {
                    $T->set_var('truncated',true);
                }
                $T->parse('DTRow','DiffTableRow',true);
            }
            $T->parse('DRow','DiffRow',true);
            $T->set_var('DTRow','');
        }
        $T->unset_var('showerrors');
        $T->unset_var('showdiffs');
    }

    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));
    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

$action = '';
$expected = array('backup','backupdb','config','download','delete','innodb','doinnodb','myisam','domyisam','optimize','dooptimize','mode','saveconfig','doutf8','utf8mb4','sr','searchreplace');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

if ( isset($_POST['dbcancelbutton'])) $action = '';

switch ($action) {

    case 'sr' :
        $page = DBADMIN_searchAndreplace();
        break;

    case 'config' :
        $page = DBADMIN_configBackup();
        break;

    case 'backup':
        if (SEC_checkToken()) {
            $page .= DBADMIN_backup();
        } else {
            COM_accessLog("User {$_USER['username']} tried to access the DB administration and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'backupdb' :

        $page .= DBADMIN_backupPrompt();
        break;
    case 'download':
        $file = '';
        if (isset($_GET['file'])) {
            $file = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', COM_applyFilter($_GET['file']));
            $file = str_replace('..', '', $file);
            if (!file_exists($_CONF['backup_path'] . $file)) {
                $file = '';
            }
        }
        if (!empty($file)) {
            DBADMIN_download($file);
            exit;
        }
        break;

    case 'delete':
        if (isset($_POST['delitem']) AND SEC_checkToken()) {
            foreach ($_POST['delitem'] as $delfile) {
                $file = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', COM_applyFilter($delfile));
                $file = str_replace('..', '', $file);
                if (!@unlink($_CONF['backup_path'] . $file)) {
                    Log::write('system',Log::ERROR,'Unable to remove backup file "' . $file . '"');
                }
            }
        } else {
            COM_accessLog("User {$_USER['username']} tried to delete database backup(s) and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        $page = DBADMIN_list();
        break;

    case 'myisam':
        $pagetitle = $LANG_DB_ADMIN['convert_myisam_title'];
        if (DBADMIN_supported_engine( 'MyISAM')) {
            $page .= DBADMIN_myisam();
        } else {
            $page .= COM_showMessageText($LANG_DB_ADMIN['no_myisam'],'',true,'error');
        }
        break;

    case 'innodb':
        $pagetitle = $LANG_DB_ADMIN['convert_title'];
        if (DBADMIN_supported_engine( 'InnoDB')) {
            $page .= DBADMIN_innodb();
        } else {
            $page .= COM_showMessageText($LANG_DB_ADMIN['no_innodb'],'',true,'error');
        }
        break;

    case 'doinnodb':
        $pagetitle = $LANG_DB_ADMIN['convert_title'];
        if (DBADMIN_supported_engine( 'InnoDB')) {
            $startwith = '';
            if (isset($_GET['startwith'])) {
                $startwith = COM_applyFilter($_GET['startwith']);
            }
            if (!empty($startwith) || SEC_checkToken()) {
                $failures = 0;
                if (isset($_GET['failures'])) {
                    $failures = COM_applyFilter($_GET['failures'], true);
                }
                $num_errors = DBADMIN_convert_innodb($startwith, $failures);
                if ($num_errors == 0) {
                    $page .= COM_showMessageText($LANG_DB_ADMIN['innodb_success']);
                } else {
                    $page .= COM_showMessageText($LANG_DB_ADMIN['innodb_success'] . ' ' . $LANG_DB_ADMIN['table_issues'],'',true,'error');
                }
                $page .= DBADMIN_list();
            }
        } else {
            $page .= COM_showMessageText($LANG_DB_ADMIN['no_innodb'],'',true,'error');
        }
        break;

    case 'domyisam':
        $pagetitle = $LANG_DB_ADMIN['convert_myisam_title'];
        if (DBADMIN_supported_engine( 'MyISAM' )) {
            $startwith = '';
            if (isset($_GET['startwith'])) {
                $startwith = COM_applyFilter($_GET['startwith']);
            }
            if (!empty($startwith) || SEC_checkToken()) {
                $failures = 0;
                if (isset($_GET['failures'])) {
                    $failures = COM_applyFilter($_GET['failures'], true);
                }
                $num_errors = DBADMIN_convert_myisam($startwith, $failures);
                if ($num_errors == 0) {
                    $page .= COM_showMessageText($LANG_DB_ADMIN['myisam_success']);
                } else {
                    $page .= COM_showMessageText($LANG_DB_ADMIN['myisam_success'] . ' ' . $LANG_DB_ADMIN['table_issues'],'',true,'error');
                }
                $page .= DBADMIN_list();
            }
        } else {
            $page .= COM_showMessageText($LANG_DB_ADMIN['no_innodb'],'',true,'error');
        }
        break;

    case 'utf8mb4':
        $pagetitle = $LANG_DB_ADMIN['utf8_title'];
        if ( DBADMIN_supportUtf8mb() ) {
            $page .= DBADMIN_utf8mb4();
        } else {
            $page .= COM_showMessageText('Server does not support utf8mb4 - see error.log for details.','',true,'error');
            Log::write('system',Log::ERROR,"UTF8MB4 Not Supported - MySQL PHP client is older than v5.0.9");
            $page .= DBADMIN_list();
        }
        break;

    case 'optimize':
        $pagetitle = $LANG_DB_ADMIN['optimize_title'];
        $page .= DBADMIN_optimize();
        break;

    case 'dooptimize':
        $startwith = '';
        if (isset($_GET['startwith'])) {
            $startwith = COM_applyFilter($_GET['startwith']);
        }
        $pagetitle = $LANG_DB_ADMIN['optimize_title'];
        if (!empty($startwith) || SEC_checkToken()) {
            $failures = 0;
            if (isset($_GET['failures'])) {
                $failures = COM_applyFilter($_GET['failures'], true);
            }
            $num_errors = DBADMIN_dooptimize($startwith, $failures);
            if ($num_errors == 0) {
                $page .= COM_showMessageText($LANG_DB_ADMIN['optimize_success']);
            } else {
                $page .= COM_showMessageText($LANG_DB_ADMIN['optimize_success']
                                . ' ' . $LANG_DB_ADMIN['table_issues'],'',true,'error');
            }
            $page .= DBADMIN_list();
        }
        break;

    case 'saveconfig' :
        $items = array();

        if ( !defined('DEMO_MODE') ) {
            // Get the excluded tables into a serialized string
            $tables = explode('|', $_POST['groupmembers']);

            $cfg =& config::get_instance();
            if ( is_array($tables)) {
                $cfg->set('dbback_exclude', $tables, 'dbadmin_internal');
            }

            $items['_dbback_files'] = (int)$_POST['db_backup_maxfiles'];

            $items['_dbback_gzip'] = isset($_POST['use_gzip']) ? 1 : 0;
            $items['_dbback_allstructs'] = isset($_POST['allstructs']) ? 1 : 0;

            foreach ($items as $name => $value) {
                $sql = "INSERT INTO {$_TABLES['vars']} (name, value)
                        VALUES ('$name', '$value')
                        ON DUPLICATE KEY UPDATE value='$value'";
                DB_query($sql);
            }
        } else {
            COM_setMsg('Saving Database Admin Options is Disabled in Demo Mode','error');
        }
        $page = DBADMIN_list();

        break;

    case 'mode' :
        $mode = COM_applyFilter($_POST['mode']);
        switch ( $mode ) {
            case 'optimize' :
                $tbl = COM_applyFilter($_POST['table']);
                $rc = DBADMIN_ajaxOptimizeTable($tbl);
                if ( $rc === false ) {
                    $retval['errorCode'] = 1;
                    $retval['statusMessage'] = 'Failed optimizing '.$tbl;
                } else {
                    $retval['statusMessage'] = 'Table '.$tbl.' successfully optimized';
                    $retval['errorCode'] = 0;
                }
                $retval['errorCode'] = 0;
                $return["js"] = json_encode($retval);
                echo json_encode($return);
                exit;
                break;
            case 'optimizecomplete' :
                DB_delete($_TABLES['vars'], 'name', 'lastoptimizedtable');
                DB_delete($_TABLES['vars'], 'name', 'lastoptimizeddb');
                DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('lastoptimizeddb', FROM_UNIXTIME(" . time() . "))");
                $retval['errorCode'] = 0;
                $return["js"] = json_encode($retval);
                echo json_encode($return);
                exit;
                break;
            case 'dblist' :
                $engine = COM_applyFilter($_POST['engine']);
                DBADMIN_ajaxGetTableList($engine);
                break;
            case 'convertdb' :
                $tbl = COM_applyFilter($_POST['table']);
                $engine = COM_applyFilter($_POST['engine']);
                $rc = DBADMIN_ajaxConvertTable( $tbl, $engine);
                if ( $rc === false ) {
                    $retval['errorCode'] = 1;
                    $retval['statusMessage'] = 'Failed converting '.$tbl.' to '.$engine;
                } else {
                    $retval['statusMessage'] = 'Table '.$tbl.' successfully converted to '.$engine;
                    $retval['errorCode'] = 0;
                }
                $retval['errorCode'] = 0;
                $return["js"] = json_encode($retval);
                echo json_encode($return);
                exit;
                break;
            case 'convertdbcomplete' :
                $engine = COM_applyFilter($_POST['engine']);
                DBADMIN_ajaxFinishCvt($engine);
                $retval['errorCode'] = 0;
                $return["js"] = json_encode($retval);
                echo json_encode($return);
                exit;
                break;
            case 'dbbackup_init' :
                DBADMIN_backupAjax();
                break;
            case 'dbbackup_table' :
                DBADMIN_backupTableAjax();
                break;
            case 'dbbackup_complete' :
                DBADMIN_backupCompleteAjax();
                break;

        }
        break;

    case 'searchreplace' :
        $page .= DBADMIN_srExecute();
//        $page .= DBADMIN_searchAndreplace();
        break;
    default :
        $page = DBADMIN_list();
        break;

}

$display  = COM_siteHeader('menu', $LANG_DB_ADMIN['database_admin']);
$display .= $page;
$display .= COM_siteFooter();
echo $display;

?>