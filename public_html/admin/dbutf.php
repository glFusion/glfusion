<?php
/**
* glFusion CMS
*
* glFusion Database Administraiton - utf8 to utf8mb4 conversion
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

if (!SEC_inGroup('Root') ) {
    $display = COM_siteHeader('menu', $LANG_DB_ADMIN['database_admin']);
    $display .= COM_showMessageText($MESSAGE[46],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter();
    Log::logAccessViolation('Database Administration');
    echo $display;
    exit;
}

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

        Log::write('system',Log::ERROR,"DBadmin: no action passed.");

}
?>