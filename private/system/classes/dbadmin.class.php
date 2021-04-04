<?php
/**
* glFusion CMS
*
* dbAdmin Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

class dbAdmin
{
    protected $dbName;
    protected $activeTables = array();
    protected $ajaxHandler;
    protected $isAjax = 0;
    protected $lastError = '';
    protected $errorCode = 0;
    protected $returnItem = array();
    protected $prefix = '';

    public function __construct( $database, $prefix, $activeTables = array(), $isAjax = 0 )
    {
        $this->dbName = $database;
        $this->activeTables = $activeTables;
        $this->isAjax = $isAjax;
        if ($this->isAjax) {
            $this->ajaxHandler = new AjaxHandler;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function returnResult()
    {
        if ( is_array($this->returnItem) && count($this->returnItem) > 0) {
            list($name, $value) = $this->returnItem;
        } else {
            $name = 'retval';
            $value = $this->returnItem;
        }
        if ( $this->isAjax ) {
            $this->ajaxHandler->setErrorCode($this->errorCode);
            $this->ajaxHandler->setMessage($this->lastError);
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

        $db = Database::getInstance();

        $this->errorCode = 0;
        $this->lastMessage = '';

        $sql = "SHOW TABLES";
        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch(Throwable $e) {
            $this->errorCode = 1;
            $this->lastError = 'Table list was empty';
            $this->returnResult();
        }
        $queryShowTables = $stmt->fetchAll(Database::NUMERIC);
        $stmt->closeCursor();
        foreach($queryShowTables AS $row) {
            $table = $row[0];
            $tblPrefix = substr($table,0,strlen($this->prefix));
            if ( $tblPrefix == $this->prefix ) {
                $sql = "SHOW TABLE STATUS FROM {$this->dbName} LIKE '$table'";
                $statusRow = $db->conn->fetchAssoc($sql);
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
        $db = Database::getInstance();

        $this->errorCode = 0;
        $this->lastError = '';
        $columnList = array();

        try {
            $stmt = $db->conn->executeQuery("DESCRIBE {$table}");
        } catch(Throwable $e) {
            $this->errorCode = 1;
            $this->lastError = 'Column list for table ' . $table. ' returned no columns';
            $this->returnResult();
        }

        $dbColumns = $stmt->fetchAll(Database::NUMERIC);
        foreach($dbColumns AS $row) {
                $columnList[] = $row[0];
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
        $errorMsg = 'SQL error for table "' . $table . '" ';
        if ( $column != '' ) {
            $errorMsg .= 'column "' . $column . '" ';
        }
        $this->errorCode = 1;
        Log::write('system',Log::ERROR,$errorMsg);
    }

}