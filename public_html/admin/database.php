<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | database.php                                                             |
// |                                                                          |
// | glFusion database backup administration page.                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2011 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Blaine Lang        - langmail AT sympatico DOT ca               |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Alexander Schmacks - Alexander.Schmacks AT gmx DOT de           |
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

$display = '';
$page    = '';

// If user isn't a root user, bail.
if (!SEC_inGroup('Root') ) {
    $display = COM_siteHeader('menu', $LANG_DB_BACKUP['database_admin']);
    $display .= COM_showMessageText($MESSAGE[46],$MESSAGE[30],true);
    $display .= COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to access the database administration system without proper permissions.");
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

    $lFiletimeA = filemtime($_CONF['backup_path'] . $pFileA);
    $lFiletimeB = filemtime($_CONF['backup_path'] . $pFileB);
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
    global $_CONF, $_TABLES, $_IMAGE_TYPE, $LANG08, $LANG_ADMIN, $LANG_DB_BACKUP;

    $retval = '';

    if (is_writable($_CONF['backup_path'])) {
        $backups = array();
        $fd = opendir($_CONF['backup_path']);
        $index = 0;
        while ((false !== ($file = @readdir($fd)))) {
            if ($file <> '.' && $file <> '..' && $file <> 'CVS' &&
                    preg_match('/\.sql$/i', $file)) {
                $index++;
                clearstatcache();
                $backups[] = $file;
            }
        }

        usort($backups, 'DBADMIN_compareBackupFiles');

        $data_arr = array();
        $thisUrl = $_CONF['site_admin_url'] . '/database.php';
        $diskIconUrl = $_CONF['layout_url'] . '/images/admin/disk.' . $_IMAGE_TYPE;
        $attr['title'] = $LANG_DB_BACKUP['download'];
        $alt = $LANG_DB_BACKUP['download'];
        $num_backups = count($backups);
        for ($i = 0; $i < $num_backups; $i++) {
            $downloadUrl = $thisUrl . '?download=x&amp;file='
                         . urlencode($backups[$i]);

            $downloadLink = COM_createLink(COM_createImage($diskIconUrl, $alt, $attr), $downloadUrl, $attr);
            $downloadLink .= '&nbsp;&nbsp;';
            $attr['style'] = 'vertical-align:top;';
            $downloadLink .= COM_createLink($backups[$i], $downloadUrl, $attr);
            $backupfile = $_CONF['backup_path'] . $backups[$i];
            $backupfilesize = COM_numberFormat(filesize($backupfile))
                            . ' <b>' . $LANG_DB_BACKUP['bytes'] . '</b>';
            $data_arr[$i] = array('file' => $downloadLink,
                                  'size' => $backupfilesize,
                                  'filename' => $backups[$i]);
        }

        $token = SEC_createToken();

        $menu_arr = array();

        if ( $_CONF['allow_mysqldump'] != 0 ) {
            $menu_arr[] = array('url' => $_CONF['site_admin_url'] . '/database.php?backup=x&amp;'.CSRF_TOKEN.'='.$token,
                                'text' => $LANG_DB_BACKUP['create_backup']);
        }
        $menu_arr[] = array('url' => $_CONF['site_admin_url'].'/database.php?optimize=x',
                            'text' => $LANG_DB_BACKUP['optimize_menu']);

        if ( DBADMIN_innodb_supported() ) {
            $menu_arr[] = array('url' => $_CONF['site_admin_url'].'/database.php?innodb=x',
                                'text' => $LANG_DB_BACKUP['convert_menu']);
        }
        $menu_arr[] = array('url' => $_CONF['site_admin_url'],
                            'text' => $LANG_ADMIN['admin_home']);


        $retval .= COM_startBlock($LANG_DB_BACKUP['database_admin'], '',
                            COM_getBlockTemplate('_admin_block', 'header'));
        $retval .= ADMIN_createMenu(
            $menu_arr,
            "<p>{$LANG_DB_BACKUP['db_explanation']}</p>" .
            '<p>' . sprintf($LANG_DB_BACKUP['total_number'], $index) . '</p>',
            $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE
        );

        $header_arr = array(      // display 'text' and use table field 'field'
            array('text' => $LANG_DB_BACKUP['backup_file'], 'field' => 'file'),
            array('text' => $LANG_DB_BACKUP['size'],        'field' => 'size')
        );

        $text_arr = array(
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
        $retval .= $LANG_DB_BACKUP['no_access'];
        COM_errorLog($_CONF['backup_path'] . ' is not writable.', 1);
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
    global $_CONF, $LANG08, $LANG_DB_BACKUP, $MESSAGE, $_IMAGE_TYPE,
           $_DB_host, $_DB_name, $_DB_user, $_DB_pass, $_DB_mysqldump_path;

    $retval = '';

    if (is_dir($_CONF['backup_path'])) {
        $curdatetime = date('Y_m_d_H_i_s');
        $backupfile = "{$_CONF['backup_path']}glfusion_db_backup_{$curdatetime}.sql";
        $command = '"'.$_DB_mysqldump_path.'" ' . " -h$_DB_host -u$_DB_user";
        if (!empty($_DB_pass)) {
            $command .= " -p".escapeshellarg($_DB_pass);
            $parg = " -p".escapeshellarg($_DB_pass);
        }
        if (!empty($_CONF['mysqldump_options'])) {
            $command .= ' ' . $_CONF['mysqldump_options'];
        }
        $command .= " $_DB_name > \"$backupfile\"";
        $log_command = $command;
        if (!empty($_DB_pass)) {
            $log_command = str_replace($parg, ' -p*****', $command);
        }

        if (function_exists('is_executable')) {
            $canExec = @is_executable($_DB_mysqldump_path);
        } else {
            $canExec = @file_exists($_DB_mysqldump_path);
        }
        if ($canExec) {
            DBADMIN_execWrapper($command);
            if (file_exists($backupfile) && filesize($backupfile) > 1000) {
                @chmod($backupfile, 0644);
                $retval .= COM_showMessage(93);
            } else {
                $retval .= COM_showMessage(94);
                COM_errorLog('Backup Filesize was less than 1kb', 1);
                COM_errorLog("Command used for mysqldump: $log_command", 1);
            }
        } else {
            $retval .= COM_startBlock($LANG08[06], '',
                                COM_getBlockTemplate('_msg_block', 'header'));
            $retval .= $LANG_DB_BACKUP['not_found'];
            $retval .= COM_endBlock(COM_getBlockTemplate('_msg_block',
                                                         'footer'));
            COM_errorLog('Backup Error: Bad path, mysqldump does not exist or open_basedir restriction in effect.', 1);
            COM_errorLog("Command used for mysqldump: $log_command", 1);
        }
    } else {
        $retval .= COM_startBlock($MESSAGE[30], '',
                            COM_getBlockTemplate('_msg_block', 'header'));
        $retval .= $LANG_DB_BACKUP['path_not_found'];
        $retval .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
        COM_errorLog("Backup directory '" . $_CONF['backup_path'] . "' does not exist or is not a directory", 1);
    }

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

    require_once $_CONF['path_system'] . 'classes/downloader.class.php';

    $dl = new downloader;

    $dl->setLogFile($_CONF['path'] . 'logs/error.log');
    $dl->setLogging(true);
    $dl->setDebug(true);

    $dl->setPath($_CONF['backup_path']);
    $dl->setAllowedExtensions(array('sql' =>  'application/x-gzip-compressed'));

    $dl->downloadFile($file);
}

function DBADMIN_exec($cmd) {
    global $_CONF, $_DB_pass;

    $debugfile = "";
    $status="";
    $results=array();

    if (!empty($_DB_pass)) {
        $log_command = str_replace($_DB_pass,'*****', $cmd);
    }
    COM_errorLog(sprintf("DBADMIN_exec: Executing: %s",$log_command));

    $debugfile = $_CONF['path'] . 'logs/debug.log';

    if (PHP_OS == "WINNT") {
        $cmd .= " 2>&1";
        exec('"' . $cmd . '"',$results,$status);
    } else {
        exec($cmd, $results, $status);
    }

    return array($results, $status);
}

function DBADMIN_execWrapper($cmd) {

    list($results, $status) = DBADMIN_exec($cmd);

    if ( $status == 0 ) {
        return true;
    } else {
        COM_errorLog("DBADMIN_execWrapper: Failed Command: " . $cmd);
        return false;
    }
}

/**
* Check for InnoDB table support
*
* @return   true = InnoDB tables supported, false = not supported
*
*/
function DBADMIN_innodb_supported()
{
    $retval = false;

    $result = DB_query("SHOW STORAGE ENGINES");
    $numEngines = DB_numRows($result);
    for ($i = 0; $i < $numEngines; $i++) {
        $A = DB_fetchArray($result);

        if (strcasecmp($A['Engine'], 'InnoDB') == 0) {
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
        $result = DB_query("SHOW TABLES");
        $numTables = DB_numRows($result);
        for ($i = 0; $i < $numTables; $i++) {
            $A = DB_fetchArray($result, true);
            $table = $A[0];
            if (in_array($table, $_TABLES)) {
                $result2 = DB_query("SHOW TABLE STATUS FROM $_DB_name LIKE '$table'");
                $B = DB_fetchArray($result2);
                if (strcasecmp($B['Engine'], 'InnoDB') != 0) {
                    break; // found a non-InnoDB table
                }
            }
        }
        if ($i == $numTables) {
            // okay, all the tables are InnoDB already
            $retval = true;
        }
    }

    return $retval;
}

function DBADMIN_innodb()
{
    global $_CONF, $LANG_ADMIN, $LANG_DB_BACKUP, $_IMAGE_TYPE;

    $retval = '';

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/database.php',
              'text' => $LANG_DB_BACKUP['database_admin']),
        array('url' => $_CONF['site_admin_url'].'/database.php?optimize=x',
              'text' => $LANG_DB_BACKUP['optimize_menu']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );
    $retval .= COM_startBlock($LANG_DB_BACKUP['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        "",
        $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE
    );

    $retval .= COM_startBlock($LANG_DB_BACKUP['convert_title']);
    $retval .= '<p>' . $LANG_DB_BACKUP['innodb_instructions'] . '</p>' . LB;

    if (DBADMIN_innodbStatus()) {
        $retval .= '<p>' . $LANG_DB_BACKUP['already_converted'] . '</p>' . LB;
    } else {
        $retval .= '<p>' . $LANG_DB_BACKUP['conversion_message'] . '</p>' . LB;
    }

    if (empty($token)) {
        $token = SEC_createToken();
    }

    $retval .= '<div id="dbconfig">' . LB;
    $retval .= '<form action="' . $_CONF['site_admin_url'] . '/database.php" method="post" style="display:inline;">' . LB;
    $retval .= '<input type="submit" value="' . $LANG_DB_BACKUP['convert_button'] . '">' . LB;
    $retval .= '<input type="hidden" name="doinnodb" value="doinnodb">' . LB;
    $retval .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '">' . LB;
    $retval .= '</form>' . LB;
    $retval .= '<form action="' . $_CONF['site_admin_url']
            . '/database.php" method="post" style="display:inline;">' . LB;
    $retval .= '<input type="submit" value="' . $LANG_ADMIN['cancel'] . '">' . LB;
    $retval .= '</form></div>' . LB;

    $retval .= COM_endBlock();

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

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

    DB_displayError(true);

    $maxtime = @ini_get('max_execution_time');
    if (empty($maxtime)) {
        // unlimited or not allowed to query - assume 30 second default
        $maxtime = 30;
    }
    $maxtime -= 5; // give us some leeway

    $token = ''; // SEC_createToken();

    $result = DB_query("SHOW TABLES");
    $numTables = DB_numRows($result);
    for ($i = 0; $i < $numTables; $i++) {
        $A = DB_fetchArray($result, true);
        $table = $A[0];
        if (in_array($table, $_TABLES)) {
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

            $make_innodb = DB_query("ALTER TABLE $table ENGINE=InnoDB", 1);
            if ($make_innodb === false) {
                $failures++;
                COM_errorLog('SQL error for table "' . $table . '" (ignored): '
                             . DB_error());
            }
        }
    }

    DB_delete($_TABLES['vars'], 'name', 'database_engine');
    DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('database_engine', 'InnoDB')");

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
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_DB_BACKUP, $_IMAGE_TYPE;

    $retval = '';

    $token = SEC_createToken();

    $lastrun = DB_getItem($_TABLES['vars'], 'UNIX_TIMESTAMP(value)',
                          "name = 'lastoptimizeddb'");

    $menu_arr = array(
        array('url' => $_CONF['site_admin_url'] . '/database.php',
              'text' => $LANG_DB_BACKUP['database_admin'])
    );
    if ( DBADMIN_innodb_supported() ) {
        $menu_arr[] = array('url' => $_CONF['site_admin_url'].'/database.php?innodb=x',
                            'text' => $LANG_DB_BACKUP['convert_menu']);
    }
    $menu_arr[] = array('url' => $_CONF['site_admin_url'],
                        'text' => $LANG_ADMIN['admin_home']);

    $retval .= COM_startBlock($LANG_DB_BACKUP['database_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        "",
        $_CONF['layout_url'] . '/images/icons/database.' . $_IMAGE_TYPE
    );

    $retval .= COM_startBlock($LANG_DB_BACKUP['optimize_title']);
    $retval .= '<p>' . $LANG_DB_BACKUP['optimize_explain'] . '</p>' . LB;
    if (!empty($lastrun)) {
        $last = COM_getUserDateTimeFormat($lastrun);
        $retval .= '<p>' . $LANG_DB_BACKUP['last_optimization'] . ': '
                . $last[0] . '</p>' . LB;
    }
    $retval .= '<p>' . $LANG_DB_BACKUP['optimization_message'] . '</p>' . LB;

    $retval .= '<div id="dboptimize">' . LB;
    $retval .= '<form action="' . $_CONF['site_admin_url'] . '/database.php" method="post" style="display:inline;">' . LB;
    $retval .= '<input type="submit" value="' . $LANG_DB_BACKUP['optimize_button'] . '">' . LB;
    $retval .= '<input type="hidden" name="dooptimize" value="dooptimize">' . LB;
    $retval .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '">' . LB;
    $retval .= '</form>' . LB;
    $retval .= '<form action="' . $_CONF['site_admin_url']
            . '/database.php" method="post" style="display:inline;">' . LB;
    $retval .= '<input type="submit" value="' . $LANG_ADMIN['cancel'] . '">' . LB;
    $retval .= '</form></div>' . LB;

    $retval .= COM_endBlock();

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

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

    $result = DB_query("SHOW TABLES");
    $numTables = DB_numRows($result);
    for ($i = 0; $i < $numTables; $i++) {
        $A = DB_fetchArray($result, true);
        $table = $A[0];
        if (in_array($table, $_TABLES)) {
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
            $optimize = DB_query("OPTIMIZE TABLE $table", 1);
            if ($optimize === false) {
                $failures++;
                COM_errorLog('SQL error for table "' . $table . '" (ignored): '
                             . DB_error());

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
    }

    DB_delete($_TABLES['vars'], 'name', 'lastoptimizedtable');
    DB_delete($_TABLES['vars'], 'name', 'lastoptimizeddb');
    DB_query("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('lastoptimizeddb', FROM_UNIXTIME(" . time() . "))");

    return $failures;
}


$action = '';
$expected = array('backup','download','delete','innodb','doinnodb','optimize','dooptimize');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

switch ($action) {

    case 'backup':
        if (SEC_checkToken()) {
            $page .= DBADMIN_backup();
        } else {
            COM_accessLog("User {$_USER['username']} tried to access the DB administration and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
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
                    COM_errorLog('Unable to remove backup file "' . $file . '"');
                }
            }
        } else {
            COM_accessLog("User {$_USER['username']} tried to delete database backup(s) and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'innodb':
        $pagetitle = $LANG_DB_BACKUP['convert_title'];
        if (DBADMIN_innodb_supported()) {
            $page .= DBADMIN_innodb();
        } else {
            $page .= COM_showMessageText($LANG_DB_BACKUP['no_innodb']);
        }
        break;

    case 'doinnodb':
        $pagetitle = $LANG_DB_BACKUP['convert_title'];
        if (DBADMIN_innodb_supported()) {
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
                    $page .= COM_showMessageText($LANG_DB_BACKUP['innodb_success']);
                } else {
                    $page .= COM_showMessageText($LANG_DB_BACKUP['innodb_success'] . ' ' . $LANG_DB_BACKUP['table_issues']);
                }
                $page .= DBADMIN_list();
            }
        } else {
            $page .= COM_showMessageText($LANG_DB_BACKUP['no_innodb']);
        }
        break;

    case 'optimize':
        $pagetitle = $LANG_DB_BACKUP['optimize_title'];
        $page .= DBADMIN_optimize();
        break;

    case 'dooptimize':
        $startwith = '';
        if (isset($_GET['startwith'])) {
            $startwith = COM_applyFilter($_GET['startwith']);
        }
        $pagetitle = $LANG_DB_BACKUP['optimize_title'];
        if (!empty($startwith) || SEC_checkToken()) {
            $failures = 0;
            if (isset($_GET['failures'])) {
                $failures = COM_applyFilter($_GET['failures'], true);
            }
            $num_errors = DBADMIN_dooptimize($startwith, $failures);
            if ($num_errors == 0) {
                $page .= COM_showMessageText($LANG_DB_BACKUP['optimize_success']);
            } else {
                $page .= COM_showMessageText($LANG_DB_BACKUP['optimize_success']
                                . ' ' . $LANG_DB_BACKUP['table_issues']);
            }
            $page .= DBADMIN_list();
        }
        break;

    default :
        $page = DBADMIN_list();
        break;

}

$display  = COM_siteHeader('menu', $LANG_DB_BACKUP['database_admin']);
$display .= $page;
$display .= COM_siteFooter();
echo $display;

?>