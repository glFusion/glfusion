<?php
/**
* glFusion CMS
*
* MySQL PDO database class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Copyright (C) 2000-2011 by the following authors:
*
*  Authors: Tony Bibbs, tony AT tonybibbs DOT com
*           Kenji Ito, geeklog AT mystral-kk DOT net
*
*/

class database
{
    /**
    * @var string
    */
    private $driverName = 'pdo_mysql';

     /**
    * @var string
    */
    private $_host = '';

    /**
    * @var string
    */
    private $_name = '';

    /**
     * @var string
     */
    private $_user = '';

    /**
    * @var string
    */
    private $_pass = '';

    /**
    * @var PDO object|null
    */
    private $_db = null;

    /**
    * @var bool
    */
    private $_verbose = true;

    /**
    * @var bool
    */
    private $_display_error = true;

    /**
    * @var string|callable
    */
    private $_errorlog_fn = '';

    /**
    * @var string
    */
    private $_charset = '';

    /**
    * @var string
    */
    private $_character_set_database = '';

    /**
    * @var int
    */
    private $_mysql_version = 0;

    /**
    * @var int
    */
    private $_filter = 1;

    /**
    * @var int
    */
    private $_errno = 0;

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
     * This function connects to the MySQL server and returns the connection object
     *
     * @return   object Returns PDO object
     */
    private function _connect()
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - inside database->_connect");
        }

        $dsn = 'mysql:dbname='.$this->_name.';host='.$this->_host.';charset='.$this->_character_set_database;

        try {
            $db = @new PDO($dsn, $this->_user, $this->_pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            die ('Connection failed: ' . $e->getMessage());
        }
        $this->_db = $db;

        $this->_mysql_version = $db->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (version_compare($this->_mysql_version,'5.7.0','>=')) {
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
            $this->_errorlog("DEBUG: mysql_pdo - leaving database->_connect");
        }
    }

    /**
    * constructor for database class
    *
    * This initializes an instance of the database object
    *
    * @param        string      $dbhost     Database host
    * @param        string      $dbname     Name of database
    * @param        string      $dbuser     User to make connection as
    * @param        string      $dbpass     Password for dbuser
    * @param        string      $errorlogfn Name of the errorlog function
    * @param        string      $charset    character set of site
    * @param        string      $db_charset character of db
    */
    public function __construct($dbhost, $dbname, $dbuser, $dbpass, $errorlogfn = '', $charset = '', $db_charset = '' )
    {
        $this->_host = $dbhost;
        $this->_name = $dbname;
        $this->_user = $dbuser;
        $this->_pass = $dbpass;
        $this->_verbose = false;
        $this->_errorlog_fn = $errorlogfn;
        $this->_charset = strtolower($charset);
        $this->_character_set_database = strtoupper(str_replace("-","",$db_charset));
        $this->_mysql_version = 0;

        $this->_connect();
    }

    public function __destruct()
    {
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
     * Returns value of $_verbose
     *
     * @return   bool     TRUE if in verbose mode otherwise FALSE
     */
    public function isVerbose()
    {
        if ($this->_verbose
        && (empty($this->_errorlog_fn) || !function_exists($this->_errorlog_fn))) {
            echo "\n<br/><b>Cannot run mysql_pdo.class.php in verbose mode because the errorlog "
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
    * @return   mysql_pdo_result|false      Returns results of query
    */
    public function dbQuery($sql, $ignore_errors=0)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - inside database->dbQuery");
            $this->_errorlog("DEBUG: mysql_pdo - SQL query is " . $sql);
        }
        try {
            $result = $this->_db->query($sql);
        } catch (PDOException $e) {
            if ($ignore_errors) {
                $result = false;
                $this->_errorlog("SQL Error: " . $e->getMessage() . PHP_EOL. $sql);
            } else {
                trigger_error($this->dbError($sql), E_USER_ERROR);
            }
        }

        $this->_errno = $this->_db->errorCode();

        if ($result === false) {
            if ($ignore_errors) {
                return false;
            }
            if ($this->_verbose) {
                $this->_errorlog("DEBUG: mysql_pdo - SQL caused an error");
                $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbQuery");
            }
        }
        return $result;
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbSave");
        }

        $sql = "REPLACE INTO $table ($fields) VALUES ($values)";

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbSave");
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbDelete");
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
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbDelete");
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside dbChange");
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
            $this->_errorlog("DEBUG: mysql_pdo - dbChange sql = " . $sql);
        }

        $retval = $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbChange");
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbCount");
        }

        $sql = "SELECT COUNT(*) FROM $table";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - sql = " . $sql);
        }

        $result = $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbCount");
        }
        return ($result->fetchColumn());
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbCopy");
        }

        $sql = "REPLACE INTO $table ($fields) SELECT $values FROM $tablefrom";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        $retval = $this->dbQuery($sql);
        $retval = $retval && $this->dbDelete($tablefrom, $id, $value);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbCopy");
        }
        return $retval;
    }

    /**
    * Retrieves the number of rows in a recordset
    *
    * This returns the number of rows in a recordset
    *
    * @param    PDO  $recordSet The record set to operate one
    * @return   int            Returns number of rows otherwise FALSE (0)
    */
    public function dbNumRows($recordSet)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbNumRows");
        }

        /*
         * NOTE: not all databases return accurate results
         * for rowCount() on selects.
         * for now it appears MySQL does
         * Need to move away from rowcounts
         */

        if ($recordSet === false || $recordSet === null) return 0;

        try {
            $rowcount = $recordSet->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
        return $rowcount;
    }


    /**
    * Returns the contents of one cell from a MySQL result set
    *
    * @param    PDO $recordSet The recordset to operate on
    * @param    int         $row         row to get data from
    * @param    mixed       $field       field to return
    * @return   mixed (depends on field content)
    */
    public function dbResult($recordset, $row, $field = 0)
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbResult");
            if (empty($recordset) || $recordset === null) {
                $this->_errorlog("DEBUG: mysql_pdo - Passed recordset is not valid");
            } else {
                $this->_errorlog("DEBUG: mysql_pdo - Everything looks good");
            }
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbResult");
        }

        $retval = '';

        if (is_numeric($field)) {
            $field = intval($field, 10);
            $targetRow = $recordset->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_ABS, $row);
        } else {
            $targetRow = $recordset->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row);
        }
        if (($targetRow !== false) && isset($targetRow[$field])) {
            $retval = $targetRow[$field];
        }

        return $retval;
    }

    /**
    * Retrieves the number of fields in a recordset
    *
    * This returns the number of fields in a recordset
    *
    * @param   PDO $recordSet The recordset to operate on
    * @return  int     Returns number of rows from query
    */
    public function dbNumFields($recordset)
    {
        if ($recordset === false || $recordset === null) return 0;
        return $recordset->columnCount();
    }

    /**
    * Retrieves returns the field name for a field
    *
    * Returns the field name for a given field number
    *
    * @param    PDO $recordSet   The recordset to operate on
    * @param    int           $fieldNumber field number to return the name of
    * @return   string      Returns name of specified field
    *
    */
    public function dbFieldName($recordSet, $fieldNumber)
    {
        if ($recordSet === false || $recordSet === null) return '';

        $meta = $recordSet->getColumnMeta($fieldNumber);
        if (isset($meta['name'])) return $meta['name'];
        return '';
    }

    /**
    * Retrieves returns the number of effected rows for last query
    *
    * Retrieves returns the number of effected rows for last query
    *
    * @param    PDO  $recordSet The record set to operate on
    * @return   int     Number of rows affected by last query
    */
    public function dbAffectedRows($recordset)
    {
        if ($recordset === false || $recordset === null) return 0;
        return $recordset->rowCount();
    }

    /**
    * Retrieves record from a recordset
    *
    * Gets the next record in a recordset and returns in array
    *
    * @param    PDO $recordSet The record set to operate on
    * @param    bool          $both      get both assoc and numeric indices
    * @return   array       Returns data array of current row from recordset
    */
    public function dbFetchArray($recordSet, $both = true)
    {

        if ($recordSet === false || $recordSet == null) return false;

        $result_type = $both ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;

        try {
            $result = $recordSet->fetch($result_type);
        } catch (PDOException $e) {
            $result = false;
        }

        return ($result === false) ? false : $result;
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
        $result_type = $both ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;

        if ($recordset === false || $recordset === null) return array();

        try {
            $result = $recordset->fetchAll($result_type);
        } catch (Exception $e) {
            $result = false;
//            trigger_error($this->dbError($e->getMessage()), E_USER_ERROR);
        }
        return ($result === false) ? array() : $result;
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
        return $this->_db->lastInsertId();
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
        if ((int)$this->_db->errorCode() > 0) {
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
            $info = $this->_db->errorInfo();

            if (empty($fn)) {
                $errorMessage = $this->_db->errorCode() . ': '.$info[2];
                if ( $sql != '' ) {
                    $errorMessage .= " SQL in question: " . $sql;
                }
                $this->_errorlog($errorMessage);
            } else {
                $errorMessage = $this->_db->errorCode() . ': ' . $info[2] . " in " . $fn;
                if ( $sql != '' ) {
                    $errorMessage .= " SQL in question: ".$sql;
                }
                $this->_errorlog($errorMessage);
            }

            if ($this->_display_error) {
                return  $this->_db->errorCode() . ': ' . $info[2];
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbLockTable");
        }

        $sql = "LOCK TABLES $table WRITE";

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbLockTable");
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
            $this->_errorlog("DEBUG: mysql_pdo - Inside database->dbUnlockTable");
        }

        $sql = 'UNLOCK TABLES';

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: mysql_pdo - Leaving database->dbUnlockTable");
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
        $value = $this->_db->quote($value);
        $value = substr($value, 1, -1);
        return $value;
    }

    /**
     * @return     string     the version of the database application
     */
    public function dbGetVersion()
    {
        return $this->_db->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Return driver name
     *
     * @return  string
     */
    public function dbGetDriverName()
    {
        return $this->driverName;
    }

    public function dbStartTransaction()
    {
        return $this->_db->beginTransaction();
    }

    public function dbCommit()
    {
        return $this->_db->commit();
    }

    public function dbRollback()
    {
        return $this->_db->rollBack();
    }

    public function getFilter()
    {
        return $this->_filter;
    }

    public function getErrno()
    {
        return $this->_errno;
    }

    public function dbGetClientVersion()
    {
        $v = $this->_db->getAttribute(PDO::ATTR_CLIENT_VERSION);
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $v, $version);
        return $version[0];
    }

    public function dbGetServerVersion()
    {
        return $this->_db->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    // PDO routines

    /*
     *
     * @param $sql  valid SQL statement template for the target database server.
     * @param $options  This array holds one or more key=>value pairs to
     *                  set attribute values for the PDOStatement object that
     *                  this method returns. You would most commonly use this
     *                  to set the PDO::ATTR_CURSOR value to PDO::CURSOR_SCROLL
     *                  to request a scrollable cursor. Some drivers have
     *                  driver specific options that may be set at prepare-time.
     * @return returns a PDOStatement object.
     */
    public function dbPrepare($sql, $options = array())
    {
        return $this->_db->prepare($sql, $options);
    }

    /*
    * @param stmtResource  statement resource
    * @param string     Parameter identifier. For a prepared statement using named placeholders,
    *                   this will be a parameter name of the form :name. For a prepared statement
    *                   using question mark placeholders, this will be the 1-indexed position of the parameter.
    * @param value      The value to bind to the parameter
    * @param dataType   Explicit data type for parameter using the PDO::PARAM_* constants
    *
    * return bool true / false
    */
    public function dbBindValue($sth, $parameter, $value, $dataType = 0)
    {
        return $sth->bindValue($parameter,$value,$dataType);
    }

    /*
    *  Executes a prepared statement
    *
    * @param $sth           Statement resource
    * @param $inputParms    An array of values with as many elements as there are
    *                       bound parameters
    *
    * @return bool  TRUE on success or FALSE on failure.
    */
    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
     * and returns the number of affected rows.
     *
     * This method supports PDO binding types
     *
     * @param string $query  The SQL query.
     * @param array  $params The query parameters.
     * @param array  $types  The parameter types.
     *
     * @return integer The number of affected rows.
     *
     * @throws some type of exception...
     */
    public function dbExecute($sth, $inputParms)
    {
        return $stmt->execute($inputParams);
    }

}

/* to add

execute
executeQuery
executeUpdate
fetchColumn
fetchAssoc
delete
insert
update
quote
quoteIdentifier
*/

?>