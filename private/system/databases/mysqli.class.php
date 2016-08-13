<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | mysqli.class.php                                                         |
// |                                                                          |
// | mysqli database class                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2000-2011 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs, tony AT tonybibbs DOT com                           |
// |          Kenji Ito, geeklog AT mystral-kk DOT net                        |
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

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

class database
{
    /**
    * @var string
    */
    private $_host = '';

    /**
    * @var string
    */
    private $_name = '';

    /**
    * @var sring|string
    */
    private $_user = '';

    /**
    * @var string
    */
    private $_pass = '';

    /**
    * @var mysqli|false
    */
    private $_db = false;

    /**
    * @var bool
    */
    private $_verbose = false;

    /**
    * @var bool
    */
    private $_display_error = false;

    /**
    * @var string|callable
    */
    private $_errorlog_fn = '';

    /**
    * @var string
    */
    private $_charset = '';

    /**
    * @var int
    */
    private $_mysql_version = 0;

    /**
    * Logs messages
    *
    * Logs messages by calling the function held in $_errorlog_fn
    *
    * @param    string $msg Message to log
    * @access   private
    */
    private function _errorlog($msg)
    {
        $function = $this->_errorlog_fn;

        if (function_exists($function)) {
            $function($msg);
        }
    }

    /**
    * Connects to the MySQL database server
    *
    * This function connects to the MySQL server and returns the connection object
    *
    * @return   object      Returns connection object
    * @access   private
    */
    private function _connect()
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - inside database->_connect");
        }

        // Connect to MySQL server
        $this->_db = @new mysqli($this->_host, $this->_user, $this->_pass);

        if ($this->_db->connect_errno) {
            die('Connect Error: ' . $this->_db->connect_errno);
        }

        $this->_mysql_version = $this->_db->server_version;

        // Set the database
        $this->_db->select_db($this->_name) OR die('error selecting database');

        if (!($this->_db)) {
            if ($this->_verbose) {
                $this->_errorlog("DEUBG: mysqli - error in database->_connect");
            }

            // damn, got an error.
            $this->dbError();
        }

        if ($this->_charset === 'utf-8') {
            $result = false;

            if (method_exists($this->_db, 'set_charset')) {
                $result = $this->_db->set_charset('utf8');
            }

            if (!$result) {
                @$this->_db->query("SET NAMES 'utf8'");
            }
        }

        if ($this->_mysql_version >= 50700) {
            $result = $this->_db->query("SELECT @@sql_mode");
            $modeData = $this->dbFetchArray($result);
            $updatedMode = '';
            $first = 0;
            $found = 0;
            if ( isset($modeData["@@sql_mode"])) {
                $modeArray = explode(",",$modeData["@@sql_mode"]);
                foreach ($modeArray as $setting ) {
                    if ( $setting == 'ONLY_FULL_GROUP_BY') {
                        $found = 1;
                        continue;
                    }
                    if ( $first != 0 ) $updatedMode .= ',';
                    $updatedMode .= $setting;
                    $first++;
                }
                if ( $found == 1 ) {
                    @$this->_db->query("SET sql_mode = '".$updatedMode."'");
                }
            }
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - leaving database->_connect");
        }
    }

    /**
    * constructor for database class
    *
    * This initializes an instance of the database object
    *
    * @param        string      $dbhost     Database host
    * @param        string      $dbname     Name of database
    * @param        sring       $dbuser     User to make connection as
    * @param        string      $pass       Password for dbuser
    * @param        string      $errorlogfn Name of the errorlog function
    * @param        string      $charset    character set to use
    */
    public function __construct($dbhost, $dbname, $dbuser, $dbpass, $errorlogfn = '', $charset = '')
    {
        $this->_host = $dbhost;
        $this->_name = $dbname;
        $this->_user = $dbuser;
        $this->_pass = $dbpass;
        $this->_verbose = false;
        $this->_errorlog_fn = $errorlogfn;
        $this->_charset = strtolower($charset);
        $this->_mysql_version = 0;

        $this->_connect();
    }

    public function __destruct()
    {
        @$this->_db->close();
        $this->_db = null;
    }

    /**
    * Turns debug mode on
    * Set this to TRUE to see debug messages
    *
    * @param    bool $flag
    */
    public function setVerbose($flag)
    {
        $this->_verbose = (bool) $flag;
    }

    /**
    * Turns detailed error reporting on
    *
    * If set to TRUE, this will display detailed error messages on the site.
    * Otherwise, it will only that state an error occurred without going into
    * details. The complete error message (including the offending SQL request)
    * is always available from error.log.
    *
    * @param    bool $flag
    */
    public function setDisplayError($flag)
    {
        $this->_display_error = (bool) $flag;
    }

    /**
    * Checks to see if debug mode is on
    *
    * Returns value of $_verbose
    *
    * @return   bool     TRUE if in verbose mode otherwise FALSE
    */
    public function isVerbose()
    {
        if ($this->_verbose
        && (empty($this->_errorlog_fn) || !function_exists($this->_errorlog_fn))) {
            echo "\n<br/><b>Cannot run mysqli.class.php in verbose mode because the errorlog "
            . "function was not set or does not exist.</b><br/>\n";
            return false;
        }

        return $this->_verbose;
    }

    /**
    * Sets the function this class should call to log debug messages
    *
    * @param        string      $functionname   Function name
    */
    public function setErrorFunction($functionname)
    {
        if (is_callable($functionname)) {
            $this->_errorlog_fn = $functionname;
        }
    }

    /**
    * Executes a query on the MySQL server
    *
    * This executes the passed SQL and returns the recordset or errors out
    *
    * @param    string      $sql            SQL to be executed
    * @param    int         $ignore_errors  If 1 this function supresses any
    *                                       error messages
    * @return   mysqli_result|false         Returns results of query
    */
    public function dbQuery($sql, $ignore_errors=0)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - inside database->dbQuery");
            $this->_errorlog("DEBUG: mysqli - SQL query is " . $sql);
        }

        // Run query
        if ($ignore_errors) {
            $result = @$this->_db->query($sql);
        } else {
            $result = @$this->_db->query($sql) OR trigger_error($this->dbError($sql), E_USER_ERROR);
        }

        // If OK, return otherwise echo error
        if ($this->_db->errno == 0 AND ($result !== false)) {
            if ($this->_verbose) {
                $this->_errorlog("DEBUG: mysqli - SQL query ran without error");
                $this->_errorlog("DEBUG: mysqli - Leaving database->dbQuery");
            }

            return $result;
        } else {
            // callee may want to suppress printing of errors
            if ($ignore_errors) {
                return false;
            }

            if ($this->_verbose) {
                $this->_errorlog("DEBUG: mysqli - SQL caused an error");
                $this->_errorlog("DEBUG: mysqli - Leaving database->dbQuery");
            }
            return false;
        }
    }

    /**
    * Saves information to the database
    *
    * This will use a REPLACE INTO to save a record into the
    * database
    *
    * @param    string      $table      The table to save to
    * @param    string      $fields     Comma-delimited list of fields to save
    * @param    string      $values     Values to save to the database table
    */
    public function dbSave($table, $fields, $values)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbSave");
        }

        $sql = "REPLACE INTO $table ($fields) VALUES ($values)";

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbSave");
        }
    }

    /**
    * Builds a pair of a column name and a value that can be used as a part of an SQL
    *
    * @param   mixed|array $id
    * @param   mixed|array $value
    * @return  mixed         string = column_name-value pair(s), FALSE = error
    */
    private function _buildIdValuePair($id, $value)
    {
        $retval = '';

        if (is_array($id) || is_array($value)) {
            $num_ids = count($id);

            if (is_array($id) && is_array($value) && ($num_ids === count($value))) {
                // they are arrays, traverse them and build sql
                $retval .= ' WHERE ';

                for ($i = 1; $i <= $num_ids; $i ++) {
                    $retval .= current($id) . " = '"
                    .  $this->dbEscapeString(current($value)) . "'";
                    if ($i !== $num_ids) {
                        $retval .= " AND ";
                    }

                    next($id);
                    next($value);
                }
            } else {
                // error, they both have to be arrays and of the same size
                $retval = false;
            }
        } else {
            // just regular string values, build sql
            if (!empty($id) && (isset($value) || ($value != ''))) {
                $retval .= " WHERE $id = '$value'";
            }
        }

        return $retval;
    }

    /**
    * Deletes data from the database
    *
    * This will delete some data from the given table where id = value.  If
    * id and value are arrays then it will traverse the arrays setting
    * $id[curval] = $value[curval].
    *
    * @param    string          $table      Table to delete data from
    * @param    array|string    $id         field name(s) to include in where clause
    * @param    array|string    $value      field value(s) corresponding to field names
    * @return   bool                        Returns TRUE on success otherwise FALSE
    */
    public function dbDelete($table, $id, $value)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbDelete");
        }

        $sql = "DELETE FROM $table";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbDelete");
        }

        return TRUE;
    }

    /**
    * Changes records in a table
    *
    * This will change the data in the given table that meet the given criteria and will
    * redirect user to another page if told to do so
    *
    * @param    string          $table          Table to perform change on
    * @param    string          $item_to_set    field name of unique ID field for table
    * @param    string          $value_to_set   Value for id
    * @param    array|string    $id             additional field name used in where clause
    * @param    array|string    $value          additional values used in where clause
    * @param    bool            $suppress_quotes if FALSE it will not use '<value>' in where clause
    * @return   bool                            Returns TRUE on success otherwise FALSE
    */
    public function dbChange($table, $item_to_set, $value_to_set, $id, $value,
    $suppress_quotes = false)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside dbChange");
        }

        if ($suppress_quotes) {
            $sql = "UPDATE $table SET $item_to_set = $value_to_set";
        } else {
            $sql = "UPDATE $table SET $item_to_set = '$value_to_set'";
        }

        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - dbChange sql = " . $sql);
        }

        $retval = $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbChange");
        }
        return $retval;
    }

    /**
    * Returns the number of records for a query that meets the given criteria
    *
    * This will build a SELECT count(*) statement with the given criteria and
    * return the result
    *
    * @param    string          $table  Table to perform count on
    * @param    array|string    $id     field name(s) of fields to use in where clause
    * @param    array|string    $value  Value(s) to use in where clause
    * @return   bool     returns count on success otherwise FALSE
    *
    */
    public function dbCount($table, $id = '', $value = '')
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbCount");
        }

        $sql = "SELECT COUNT(*) FROM $table";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - sql = " . $sql);
        }

        $result = $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbCount");
        }

        return ($this->dbResult($result,0));
    }

    /**
    * Copies a record from one table to another (can be the same table)
    *
    * This will use a REPLACE INTO...SELECT FROM to copy a record from one table
    * to another table.  They can be the same table.
    *
    * @param    string          $table      Table to insert record into
    * @param    string          $fields     Comma delmited list of fields to copy over
    * @param    string          $values     Values to store in database fields
    * @param    string          $tablefrom  Table to get record from
    * @param    array|string    $id         field name(s) to use in where clause
    * @param    array|string    $value      Value(s) to use in where clause
    * @return   bool     Returns TRUE on success otherwise FALSE
    */
    public function dbCopy($table, $fields, $values, $tablefrom, $id, $value)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbCopy");
        }

        $sql = "REPLACE INTO $table ($fields) SELECT $values FROM $tablefrom";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        $retval = $this->dbQuery($sql);
        $retval = $retval && $this->dbDelete($tableFrom, $id, $value);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbCopy");
        }
        return $retval;
    }

    /**
    * Retrieves the number of rows in a recordset
    *
    * This returns the number of rows in a recordset
    *
    * @param    mysqli_result $recordSet The record set to operate one
    * @return   int            Returns number of rows otherwise FALSE (0)
    */
    public function dbNumRows($recordSet)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbNumRows");
        }

        // return only if recordset exists, otherwise 0
        if ($recordSet instanceof mysqli_result) {
            if ($this->_verbose) {
                $this->_errorlog('DEBUG: mysqli - got ' . $recordSet->num_rows . ' rows');
                $this->_errorlog("DEBUG: mysqli - Leaving database->dbNumRows");
            }

            return $recordSet->num_rows;
        } else {
            if ($this->_verbose) {
                $this->_errorlog("DEBUG: mysqli - Returned no rows");
                $this->_errorlog("DEBUG: mysqli - Leaving database->dbNumRows");
            }
            return 0;
        }
    }

    /**
    * Returns the contents of one cell from a MySQL result set
    *
    * @param    mysqli_result $recordSet The recordset to operate on
    * @param    int         $row         row to get data from
    * @param    mixed       $field       field to return
    * @return   mixed (depends on field content)
    */
    public function dbResult($recordset, $row, $field = 0)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbResult");
            if (empty($recordset)) {
                $this->_errorlog("DEBUG: mysqli - Passed recordset is not valid");
            } else {
                $this->_errorlog("DEBUG: mysqli - Everything looks good");
            }
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbResult");
        }

        $retval = '';

        if ($recordset->data_seek($row)) {
            if (is_numeric($field)) {
                $field = intval($field, 10);
                $row = $recordset->fetch_row();
            } else {
                $row = $recordset->fetch_assoc();
            }
            if (($row !== null) && isset($row[$field])) {
                $retval = $row[$field];
            }
        }

        return $retval;
    }

    /**
    * Retrieves the number of fields in a recordset
    *
    * This returns the number of fields in a recordset
    *
    * @param   mysqli_result $recordSet The recordset to operate on
    * @return  int     Returns number of rows from query
    */
    public function dbNumFields($recordset)
    {
        return $recordset->field_count;
    }

    /**
    * Retrieves returns the field name for a field
    *
    * Returns the field name for a given field number
    *
    * @param    mysqli_result $recordSet   The recordset to operate on
    * @param    int           $fieldNumber field number to return the name of
    * @return   string      Returns name of specified field
    *
    */
    public function dbFieldName($recordSet, $fieldNumber)
    {
        $result = $recordSet->fetch_field_direct($fieldNumber);

        return ($result === false) ? '' : $result->name;
    }

    /**
    * Retrieves returns the number of effected rows for last query
    *
    * Retrieves returns the number of effected rows for last query
    *
    * @param    mysqli_result $recordSet The record set to operate on
    * @return   int     Number of rows affected by last query
    */
    public function dbAffectedRows($recordset)
    {
        return $this->_db->affected_rows;
    }

    /**
    * Retrieves record from a recordset
    *
    * Gets the next record in a recordset and returns in array
    *
    * @param    mysqli_result $recordSet The record set to operate on
    * @param    bool          $both      get both assoc and numeric indices
    * @return   array       Returns data array of current row from recordset
    */
    public function dbFetchArray($recordSet, $both = false)
    {
        $result_type = $both ? MYSQLI_BOTH : MYSQLI_ASSOC;
        $result = $recordSet->fetch_array($result_type);

        return ($result === null) ? false : $result;
    }

    /**
    * Retrieves full record set
    *
    * Gets the full recordset and returns in array
    *
    * @param    object      $recordset  The recordset to operate on
    * @param    boolean     $both       get both assoc and numeric indices
    * @return   array       Returns data array of current row from recordset
    */
    public function dbFetchAll($recordset, $both = false)
    {
        $result_type = $both ? MYSQLI_BOTH : MYSQLI_ASSOC;

        $result = $recordset->fetch_all($result_type);
        return ($result === null) ? array() : $result;
    }

    /**
    * Returns the last ID inserted
    *
    * Returns the last auto_increment ID generated
    *
    * @param    resource    $link_identifier    identifier for opened link
    * @param    string      $sequence
    * @return   int                             Returns last auto-generated ID
    */
    public function dbInsertId($link_identifier = null, $sequence = '')
    {
        return $this->_db->insert_id;
    }

    /**
    * returns a database error string
    *
    * Returns an database error message
    *
    * @param    string      $sql    SQL that may have caused the error
    * @return   string      Text for error message
    */
    public function dbError($sql = '')
    {
        if ($this->_db->errno) {
            $fn = '';
            $btr = debug_backtrace();
            if (! empty($btr)) {
                for ($i = 0; $i < 100; $i++) {
                    if (isset($btr[$i])) {
                        $b = $btr[$i];
                        if ($b['function'] == 'DB_query') {
                            if (!empty($b['file']) && !empty($b['line'])) {
                                $fn = $b['file'] . ':' . $b['line'];
                            }
                            break;
                        }
                    } else {
                        break;
                    }
                }
            }
            if (empty($fn)) {
                $this->_errorlog($this->_db->errno . ': ' . $this->_db->error . ". SQL in question: $sql");
            } else {
                $this->_errorlog($this->_db->errno . ': ' . $this->_db->error . " in $fn. SQL in question: $sql");
            }

            if ($this->_display_error) {
                return  $this->_db->errno . ': ' . $this->_db->error;
            } else {
                return 'An SQL error has occurred. Please see error.log for details.';
            }
        }

        return '';
    }

    /**
    * Lock a table
    *
    * Locks a table for write operations
    *
    * @param    string      $table      Table to lock
    * @return   void
    * @see dbUnlockTable
    */
    public function dbLockTable($table)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbLockTable");
        }

        $sql = "LOCK TABLES $table WRITE";

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbLockTable");
        }
    }

    /**
    * Unlock a table
    *
    * Unlocks a table after a dbLockTable (actually, unlocks all tables)
    *
    * @param    string      $table      Table to unlock (ignored)
    * @return   void
    * @see dbLockTable
    */
    public function dbUnlockTable($table)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Inside database->dbUnlockTable");
        }

        $sql = 'UNLOCK TABLES';

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysqli - Leaving database->dbUnlockTable");
        }
    }

    /**
     * Escapes a string so that it can be safely used in a query
     *
     * @param   string $str a string to be escaped
     * @return  string
     */
    public function dbEscapeString($value, $is_numeric = false)
    {
        $value = $this->_db->real_escape_string($value);
        return $value;
    }

    /**
     * @return     string     the version of the database application
     */
    public function dbGetVersion()
    {
        return $this->_db->_mysql_version;
    }

    public function dbStartTransaction()
    {
        return $this->_db->autocommit(false);
    }

    public function dbCommit()
    {
        return $this->_db->commit();
    }

    public function dbRollback()
    {
        return $this->_db->rollback();
    }
}

?>