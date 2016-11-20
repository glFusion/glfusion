<?php
/**
 * glFusion Database Administraiton - utf8 to utf8mb4 conversion
 *
 * Converts the database, tables, and columns in each table to the
 * utf8mb4_unicode_ci character set / collation.
 *
 * LICENSE: This program is free software; you can redistribute it
 *  and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @category   glFusion CMS
 * @package    dbAdmin
 * @author     Mark R. Evans  mark AT glFusion DOT org
 * @copyright  2015-2016 - Mark R. Evans
 * @license    http://opensource.org/licenses/gpl-2.0.php - GNU Public License v2 or later
 * @since      File available since Release 1.6.3
 */

require_once '../lib-common.php';
require_once 'auth.inc.php';

if (!SEC_inGroup('Root') ) {
    $display = COM_siteHeader('menu', $LANG_DB_ADMIN['database_admin']);
    $display .= COM_showMessageText($MESSAGE[46],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to access the database administration system without proper permissions.");
    echo $display;
    exit;
}

require_once $_CONF['path_system'].'classes/ajax.class.php';
require_once $_CONF['path_system'].'classes/dbadmin.class.php';
require_once $_CONF['path_system'].'classes/dbadmin-utf.class.php';

$action = '';
$mode = '';
$expected = array('utfdb','utftb','utfcm','utfcomplete','gettables','getcolumns');

$mode = (isset($_POST['mode']) ? $_POST['mode'] : '');

foreach($expected as $provided) {
    if ($mode == $provided ) {
        $action = $provided;
    }
}

if ( isset($_POST['cnlb']) || $action == '' ) {
    COM_refresh($_CONF['site_admin_url'].'/database.php');
    exit;
}

$filter = sanitizer::getInstance();

$table = '';
$column = '';

if ( isset($_POST['table'])) {
    $table = $filter->prepareForDB($_POST['table']);
}

if ( isset($_POST['column'])) {
    $column = $filter->prepareForDB($_POST['column']);
}

$conversion = new dbConvertUTF8($_DB_name, $_DB, $_DB_table_prefix, $_TABLES, true);

switch ($action) {

    case 'gettables' :

        $conversion->getTableList();
        break;

    case 'getcolumns' :

        $conversion->getColumnList ( $table );
        break;

    case 'utfdb' :

        $conversion->processDatabase();
        break;

    case 'utftb' :

        $conversion->processTable( $table );
        break;

    case 'utfcm' :

        $conversion->processColumn( $table, $column );
        break;

    case 'utfcomplete' :

        $conversion->finish();
        break;

    default :

        COM_errorLog("DBadmin: no action passed.");

}
?>