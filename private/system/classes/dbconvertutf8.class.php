<?php
/**
* glFusion CMS
*
* utf8 to utf8mb4 conversion
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

class dbConvertUTF8 extends dbAdmin
{
    private $toCharset   = 'utf8mb4';
    private $toCollation = 'utf8mb4_unicode_ci';

    public function processDatabase()
    {
        $db = Database::getInstance();

        $this->errorCode = 0;

        $sql = "ALTER DATABASE ".$this->dbName." CHARACTER SET = ".$this->toCharset." COLLATE = ".$this->toCollation."";

        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch(Throwable $e) {
            $this->errorCode = 1;
            $this->lastError = 'Error setting character set and collation on database '.$this->dbName;
            $this->returnResult();
        }
        Log::write('system',Log::DEBUG,'dbConvertUTF8 :: Processed Database: '.$this->dbName);
        $this->returnResult();
    }

    public function processTable( $table )
    {
        $this->errorCode = 0;

        $db = Database::getInstance();

        $sql = "SHOW TABLE STATUS FROM ".$this->dbName." LIKE '$table'";
        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch(Throwable $e) {
            $this->errorCode = 1;
            $this->lastError = 'Error retrieving table status for table ' . $table;
            $this->returnResult();
        }
        try {
            $row = $stmt->fetch(Database::ASSOCIATIVE);
        } catch (Throwable $e) {
            $this->errorCode = 1;
            $this->lastError = 'Error retrieving table information on table ' . $table;
            $this->returnResult();
        }
        $tb_collation = substr($row['Collation'],0,4);
        if ( strcasecmp($tb_collation, 'utf8') === 0 ) {
            if (strcasecmp($row['Collation'],$this->toCollation) !== 0) {
                Log::write('system',Log::DEBUG,'dbConvertUTF8 :: Updating Table: '.$table . ' Old Collation :: ' . $row['Collation']);
                try {
                    $db->conn->executeQuery("ALTER TABLE $table CHARACTER SET = ".$this->toCharset." COLLATE = ".$this->toCollation."");
                } catch (Throwable $e) {
                    $this->errorCode = 1;
                    $this->lastError = 'Error setting character set and collations on table ' . $table;
                    $this->returnResult();
                }
            }
        }
        $this->returnResult();
    }

    public function processColumn( $table, $column )
    {
        $this->errorCode = 0;

        $db = Database::getInstance();

        $sql = "SHOW FULL COLUMNS FROM {$table} WHERE field='".$column."'";

        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch (Throwable $e) {
            $this->errorCode = 1;
            $this->lastError = 'Error retrieving column data for column ' . $column . ' on table ' . $table;
            $this->returnResult();
        }
        $row = $stmt->fetch(Database::NUMERIC);
        if ($row === false) {
            $this->errorCode = 1;
            $this->lastError = 'Error retrieving column data for column ' . $column . ' on table ' . $table;
            $this->returnResult();
        }
        $name = $row[0];
        $type = $row[1];
        $collation = $row[2];

        if ($collation == '' || strcasecmp($collation,$this->toCollation) === 0) {
            $this->returnResult();
        }
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
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` VARCHAR({$size}) CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }
        } else if (strcasecmp(substr($type,0,4), "CHAR") === 0) {
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` CHAR({$size}) CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }
        } else if (strcasecmp(substr($type,0,7), "VARCHAR") === 0) {
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` VARCHAR({$size}) CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }

       } else if (!strcasecmp($type, "TINYTEXT")) {
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` TINYTEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }
       } else if (!strcasecmp($type, "MEDIUMTEXT")) {
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` MEDIUMTEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "LONGTEXT")) {
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` LONGTEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }
        } else if (!strcasecmp($type, "TEXT")) {
            $sql = "ALTER TABLE {$table} MODIFY `{$name}` TEXT CHARACTER SET ".$this->toCharset." COLLATE ".$this->toCollation.$null;
            try {
                $stmt = $db->conn->executeQuery($sql);
            } catch (Throwable $e) {
                $this->errorCode = 1;
                $this->lastError = 'Error setting column collation on column ' . $name . ' on table ' . $table;
                $this->logError($table,$name);
            }

        }
        Log::write('system',Log::DEBUG,'dbConvertUTF8 :: Set collation and character set on column: '.$name);
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
            Log::write('system',Log::WARNING,$this->lastError);
        }
        $this->returnResult();
    }
}
?>