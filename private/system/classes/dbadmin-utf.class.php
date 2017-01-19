<?php
/**
 * glFusion Database Administraiton
 *
 * utf8 to utf8mb4 upgrade
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

class dbConvertUTF8 extends dbAdmin
{
    private $toCharset   = 'utf8mb4';
    private $toCollation = 'utf8mb4_unicode_ci';

    public function processDatabase()
    {
        $this->errorCode = 0;
        $rc = $this->dbHandle->dbQuery("ALTER DATABASE ".$this->dbName." CHARACTER SET = ".$this->toCharset." COLLATE = ".$this->toCollation.";",true);
        if ($rc === false) {
            $this->logError($this->dbName);
        }
        $this->returnResult();
    }

    public function processTable( $table )
    {
        $this->errorCode = 0;

        $showTableResult = $this->dbHandle->dbQuery("SHOW TABLE STATUS FROM ".$this->dbName." LIKE '$table'");
        $row = $this->dbHandle->dbFetchArray($showTableResult,true);
        $tb_collation = substr($row['Collation'],0,4);
        if ( strcasecmp($tb_collation, 'utf8') === 0 ) {
            $make_utf8mb4 = $this->dbHandle->dbQuery("ALTER TABLE $table CHARACTER SET = ".$this->toCharset." COLLATE = ".$this->toCollation."", true);
            $errNo = $this->dbHandle->getErrno();
            if ($make_utf8mb4 === false  || $errNo != 0 ) {
                $this->logError($table);
            }
        }
        $this->returnResult();
    }

    public function processColumn( $table, $column )
    {
        $this->errorCode = 0;

        $columnResult = $this->dbHandle->dbQuery("SHOW FULL COLUMNS FROM {$table} WHERE field='".$column."'",true);

        if ( $columnResult === false || $this->dbHandle->dbNumRows($columnResult) != 1 ) {
            $this->logError($table);
        }
        $row = $this->dbHandle->dbFetchArray($columnResult,true);
        $name = $row[0];
        $type = $row[1];
        $collation = $row[2];
        if ( substr($collation,0,7) == 'utf8mb4')
            $this->returnResult();
        $null = '';
        if ( $row[3] == "NO" ) {
            $null = " NOT NULL ";
            if ( $row[5] !== NULL ) {
                $null .= "DEFAULT '".$row[5]."' ";
            }
        } else {
            if ( $row[5] !== NULL ) {
                $null .= " DEFAULT '".$row[5]."' ";
            }
        }
        if (preg_match("/^varchar\((\d+)\)$/i", $type, $mat)) {
            $size = $mat[1];
            $rc = $this->dbHandle->dbQuery("ALTER TABLE {$table} MODIFY `{$name}` VARCHAR({$size}) CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null,true);
            $errNo = $this->dbHandle->getErrno();
            if ($rc === false || $errNo != 0) {
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "CHAR")) {
            $rc = $this->dbHandle->dbQuery("ALTER TABLE {$table} MODIFY `{$name}` VARCHAR(1) CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null,true);
            $errNo = $this->dbHandle->getErrno();
            if ($rc === false || $errNo != 0) {
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "TINYTEXT")) {
            $rc = $this->dbHandle->dbQuery("ALTER TABLE {$table} MODIFY `{$name}` TINYTEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null,true);
            $errNo = $this->dbHandle->getErrno();
            if ($rc === false || $errNo != 0) {
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "MEDIUMTEXT")) {
            $rc = $this->dbHandle->dbQuery("ALTER TABLE {$table} MODIFY `{$name}` MEDIUMTEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null,true);
            $errNo = $this->dbHandle->getErrno();
            if ($rc === false || $errNo != 0) {
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "LONGTEXT")) {
            $rc = $this->dbHandle->dbQuery("ALTER TABLE {$table} MODIFY `{$name}` LONGTEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null,true);
            $errNo = $this->dbHandle->getErrno();
            if ($rc === false || $errNo != 0) {
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "TEXT")) {
            $rc = $this->dbHandle->dbQuery("ALTER TABLE {$table} MODIFY `{$name}` TEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null,true);
            $errNo = $this->dbHandle->getErrno();
            if ($rc === false || $errNo != 0) {
                $this->logError($table,$name);
            }
        }
        $this->returnResult();
    }

    public function finish()
    {
        global $_CONF;

        $this->errorCode = 0;

        // update siteconfig.php
        $_CONF['db_charset'] = 'utf8mb4';
        $rc = _doSiteConfigUpgrade();
        if ( $rc === false ) {
            $this->errorCode = 1;
            $this->lastError = 'DBadmin: Unable to automatically update siteconfig.php with new db_charset';
            COM_errorLog($this->lastError);
        }
        $this->returnResult();
    }
}
?>