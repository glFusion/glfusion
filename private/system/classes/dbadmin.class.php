<?php
/**
 * glFusion Database Administraiton
 *
 * dbAdmin class
 *
 * LICENSE: This program is free software; you can redistribute it
 *  and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @category   glFusion CMS
 * @package    dbAdmin
 * @author     Mark R. Evans  mark AT glFusion DOT org
 * @copyright  2015-2017 - Mark R. Evans
 * @license    http://opensource.org/licenses/gpl-2.0.php - GNU Public License v2 or later
 * @since      File available since Release 1.6.3
 */

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

class dbAdmin
{
    protected $dbHandle;
    protected $dbName;
    protected $activeTables = array();
    protected $ajaxHandler;
    protected $isAjax = 0;
    protected $lastError = '';
    protected $errorCode = 0;
    protected $returnItem = array();
    protected $prefix = '';

    public function __construct( $database, $dbHandle, $prefix, $activeTables = array(), $isAjax = 0 )
    {
        $this->dbHandle = $dbHandle;
        $this->dbName = $database;
        $this->dbHandle->setDisplayError(true);
        $this->activeTables = $activeTables;
        $this->isAjax = $isAjax;
        if ( $this->isAjax ) $this->ajaxHandler = new AjaxHandler;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function returnResult()
    {
        if ( $this->isAjax ) {
            $this->ajaxHandler->setErrorCode($this->errorCode);
            $this->ajaxHandler->setMessage($this->lastError);
            if ( is_array($this->returnItem) && count($this->returnItem) > 0) {
                list($name, $value) = $this->returnItem;
            } else {
                $name = 'retval';
                $value = $this->returnItem;
            }
            $this->ajaxHandler->setResponse($name,$value);
            $this->ajaxHandler->sendResponse();
        }
        if ( $this->errorCode != 0 ) {
            return null;
        } else {
            return $value;
        }
    }

    public function getTableList( $engine = '' )
    {
        $this->errorCode = 0;
        $this->lastMessage = '';

        $queryShowTables = $this->dbHandle->dbQuery("SHOW TABLES");

        $numTables = $this->dbHandle->dbNumRows($queryShowTables);
// debug / dvlp
//$numTables = 4;
        for ($i = 0; $i < $numTables; $i++) {
            $row = $this->dbHandle->dbFetchArray($queryShowTables,true);
            $table = $row[0];
            $tblPrefix = substr($table,0,strlen($this->prefix));
            if ( $tblPrefix == $this->prefix ) {
                $queryTableStatus = $this->dbHandle->dbQuery("SHOW TABLE STATUS FROM {$this->dbName} LIKE '$table'",true);
                $statusRow = $this->dbHandle->dbFetchArray($queryTableStatus,true);
                if ( $engine != '' ) {
                    if (strcasecmp($statusRow['Engine'], $engine) == 0) {
                        continue;
                    }
                }
                $tableList[] = $table;
            }
        }
        $this->returnItem = array('tablelist',$tableList);
        if ( count ( $tableList) == 0 ) {
            $this->errorCode = 1;
            $this->lastError = 'Table list was empty';
        }
        $this->returnResult();
    }

    public function getColumnList ( $table )
    {
        $this->errorCode = 0;
        $this->lastError = '';
        $columnList = array();

        $describeResult = $this->dbHandle->dbQuery("DESCRIBE {$table};",true);
        $numColumns = $this->dbHandle->dbNumRows($describeResult);

        if ( $numColumns > 0 ) {
            for ($i = 0; $i < $numColumns; $i++ ) {
                $row = $this->dbHandle->dbFetchArray($describeResult,true);
                $columnList[] = $row[0];
            }
        } else {
            $this->errorCode = 1;
            $this->lastError = 'Column list for table ' . $table. ' returned no columns';
        }
        $this->returnItem = array('columnlist',$columnList);
        $this->returnResult();
    }

    public function processDatabase( )
    {
        $this->errorCode = 0;

        $this->returnResult();
    }

    public function processTable( $table )
    {
        $errorCode = 0;

        if ( $this->isAjax ) {
            $this->ajaxHandler->setErrorCode(0);
            $this->ajaxHandler->setResponse('result','ok');
            $this->ajaxHandler->sendResponse();
        }
        return $errorcode;
    }

    public function processColumn( $table, $column )
    {
        $errorCode = 0;

        if ( $this->isAjax ) {
            $this->ajaxHandler->setErrorCode(0);
            $this->ajaxHandler->setResponse('result','ok');
            $this->ajaxHandler->sendResponse();
        }
        return $errorcode;
    }

    public function finish()
    {
        $this->errorCode = 0;
        $this->returnResult();
    }

    public function logError( $table, $column = '')
    {
        $this->lastError = $this->dbHandle->dbError();
        $errorMsg = 'SQL error for table "' . $table . '" ';
        if ( $column != '' ) {
            $errorMsg .= 'column "' . $column . '" ';
        }
        $errorMsg .= '(ignored): ' . $this->lastError;
        $this->errorCode = 1;
        COM_errorLog($errorMsg);
    }

}