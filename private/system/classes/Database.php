<?php
/**
* glFusion CMS
*
* Database Driver - utilizing Doctrine's DBAL
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*
*/

namespace glFusion;

global $_TABLES, $_DB_table_prefix;

$_TABLES['access']              = $_DB_table_prefix . 'access';
$_TABLES['article_images']      = $_DB_table_prefix . 'article_images';
$_TABLES['autotag_perm']        = $_DB_table_prefix . 'autotag_perm';
$_TABLES['autotag_usage']       = $_DB_table_prefix . 'autotag_usage';
$_TABLES['autotags']            = $_DB_table_prefix . 'autotags';
$_TABLES['blocks']              = $_DB_table_prefix . 'blocks';
$_TABLES['commentcodes']        = $_DB_table_prefix . 'commentcodes';
$_TABLES['commentedits']        = $_DB_table_prefix . 'commentedits';
$_TABLES['commentmodes']        = $_DB_table_prefix . 'commentmodes';
$_TABLES['comments']            = $_DB_table_prefix . 'comments';
$_TABLES['conf_values']         = $_DB_table_prefix . 'conf_values';
$_TABLES['cookiecodes']         = $_DB_table_prefix . 'cookiecodes';
$_TABLES['dateformats']         = $_DB_table_prefix . 'dateformats';
$_TABLES['featurecodes']        = $_DB_table_prefix . 'featurecodes';
$_TABLES['features']            = $_DB_table_prefix . 'features';
$_TABLES['frontpagecodes']      = $_DB_table_prefix . 'frontpagecodes';
$_TABLES['group_assignments']   = $_DB_table_prefix . 'group_assignments';
$_TABLES['groups']              = $_DB_table_prefix . 'groups';
$_TABLES['logo']                = $_DB_table_prefix . 'logo';
$_TABLES['maillist']            = $_DB_table_prefix . 'maillist';
$_TABLES['menu']                = $_DB_table_prefix . 'menu';
$_TABLES['menu_config']         = $_DB_table_prefix . 'menu_config';
$_TABLES['menu_elements']       = $_DB_table_prefix . 'menu_elements';
$_TABLES['pingservice']         = $_DB_table_prefix . 'pingservice';
$_TABLES['plugins']             = $_DB_table_prefix . 'plugins';
$_TABLES['postmodes']           = $_DB_table_prefix . 'postmodes';
$_TABLES['rating']              = $_DB_table_prefix . 'rating';
$_TABLES['rating_votes']        = $_DB_table_prefix . 'rating_votes';
$_TABLES['sessions']            = $_DB_table_prefix . 'sessions';
$_TABLES['social_share']        = $_DB_table_prefix . 'social_share';
$_TABLES['social_follow_services'] = $_DB_table_prefix . 'social_follow_services';
$_TABLES['social_follow_user']  = $_DB_table_prefix . 'social_follow_user';
$_TABLES['sortcodes']           = $_DB_table_prefix . 'sortcodes';
$_TABLES['speedlimit']          = $_DB_table_prefix . 'speedlimit';
$_TABLES['statuscodes']         = $_DB_table_prefix . 'statuscodes';
$_TABLES['stories']             = $_DB_table_prefix . 'stories';
$_TABLES['storysubmission']     = $_DB_table_prefix . 'storysubmission';
$_TABLES['subscriptions']       = $_DB_table_prefix . 'subscriptions';
$_TABLES['syndication']         = $_DB_table_prefix . 'syndication';
$_TABLES['tokens']              = $_DB_table_prefix . 'tokens';
$_TABLES['topics']              = $_DB_table_prefix . 'topics';
$_TABLES['trackback']           = $_DB_table_prefix . 'trackback';
$_TABLES['trackbackcodes']      = $_DB_table_prefix . 'trackbackcodes';
$_TABLES['usercomment']         = $_DB_table_prefix . 'usercomment';
$_TABLES['userindex']           = $_DB_table_prefix . 'userindex';
$_TABLES['userinfo']            = $_DB_table_prefix . 'userinfo';
$_TABLES['userprefs']           = $_DB_table_prefix . 'userprefs';
$_TABLES['users']               = $_DB_table_prefix . 'users';
$_TABLES['vars']                = $_DB_table_prefix . 'vars';
$_TABLES['tfa_backup_codes']    = $_DB_table_prefix . 'tfa_backup_codes';

class Database
{
    public const ASSOCIATIVE = \Doctrine\DBAL\FetchMode::ASSOCIATIVE;
    public const NUMERIC = \Doctrine\DBAL\FetchMode::NUMERIC;
    public const MIXED = \Doctrine\DBAL\FetchMode::MIXED;

    public const STANDARD_OBJECT = \Doctrine\DBAL\FetchMode::STANDARD_OBJECT;
    public const COLUMN = \Doctrine\DBAL\FetchMode::COLUMN;
    public const CUSTOM_OBJECT = \Doctrine\DBAL\FetchMode::CUSTOM_OBJECT;

    public const NULL = \Doctrine\DBAL\ParameterType::NULL;
    public const INTEGER = \Doctrine\DBAL\ParameterType::INTEGER;
    public const STRING = \Doctrine\DBAL\ParameterType::STRING;
    public const LARGE_OBJECT = \Doctrine\DBAL\ParameterType::LARGE_OBJECT;
    public const BOOLEAN = \Doctrine\DBAL\ParameterType::BOOLEAN;
    public const BINARY = 16;


    /**
    * @var string
    */
    private $driverName = 'dbal';

    private $internalDriverName = 'pdo_mysql';

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
    * @var DBAL object|null
    */
    private static $_db = null;
    public $conn = null;
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
     * Connects to the database server
     * This function connects to the server and returns the connection object
     *
     * @return   object Returns DBAL object
     */
    private function _connect()
    {
        if ($this->_verbose) {
            $this->_errorlog("DEBUG: dbal - inside database->_connect");
        }

        $config = new \Doctrine\DBAL\Configuration();

        $dsn = 'mysql:dbname='.$this->_name.';host='.$this->_host.';charset='.$this->_character_set_database;

        $connectionParams = array(
            'dbname'    => $this->_name,
            'user'      => $this->_user,
            'password'  => $this->_pass,
            'host'      => $this->_host,
            'driver'    => $this->internalDriverName,
            'charset'   => $this->_character_set_database,
            'collate'   => 'utf8mb4_unicode_ci'
        );
        if ($this->_charset === 'utf-8') {
            $connectionParams['driverOptions'] = [1002 => "SET NAMES '".$this->_character_set_database."'"];
        }

        $db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        $db->setFetchMode( \Doctrine\DBAL\FetchMode::MIXED );

        $this->_mysql_version = $db->getWrappedConnection()->getServerVersion();

        self::$_db = $db;
        $this->conn = $db;

        if ($this->_charset === 'utf-8') {
            $result = false;

            if ( $this->_character_set_database == '' ) {
                $result = $this->query("SELECT @@character_set_database");
                $collation = $this->dbFetchArray($result);
                $this->_character_set_database = $collation["@@character_set_database"];
            }
            if (version_compare($this->_mysql_version,'5.5.3','>=')) {
                if ( $this->_character_set_database == "utf8mb4" ) {
                    if (method_exists($this->conn, 'set_charset')) {
                        $result = $this->conn->set_charset('utf8mb4');
                    }

                    if (!$result) {
                        @$this->conn->query("SET NAMES 'utf8mb4'");
                    }
                    $this->_filter = 0;
                } else {
                    if (method_exists(self::$_db, 'set_charset')) {
                        $result = self::$_db->set_charset('utf8');
                    }

                    if (!$result) {
                        @self::$_db->query("SET NAMES 'utf8'");
                    }
                }
            } else {
                if (method_exists(self::$_db, 'set_charset')) {
                    $result = self::$_db->set_charset('utf8');
                }
                if (!$result) {
                    @self::$_db->query("SET NAMES 'utf8'");
                }
            }
        }

        if (version_compare($this->_mysql_version,'5.7.0','>=')) {
            $result = self::$_db->query("SELECT @@sql_mode");
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
                    @self::$_db->query("SET sql_mode = '".$updatedMode."'");
                }
            }
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: database - leaving database->_connect");
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
        $this->_character_set_database = strtolower(str_replace("-","",$db_charset));
        $this->_mysql_version = 0;

        if ($this->_character_set_database == '' || $this->_character_set_database == null) {
            $this->_character_set_database = 'utf8';
        }

        if (extension_loaded('pdo_mysql')) {
            $this->internalDriverName = 'pdo_mysql';
        } else if (class_exists('MySQLi')) {
            $this->internalDriverName = 'mysqli';
        } else {
            die("No Suitable driver found in PHP environment.");
        }

        $this->_connect();
    }

    /*
     * returns instance of DBAL object
     */

    public static function getInstance()
    {
        global $_CONF;

        static $instance;

        if (!isset($instance) ) {
            include $_CONF['path'].'db-config.php';
            if ( !isset($_CONF['db_charset'])) $_CONF['db_charset'] = '';
            $instance = new Database($_DB_host, $_DB_name, $_DB_user, $_DB_pass, 'COM_errorLog',
                     $_CONF['default_charset'], $_CONF['db_charset']);
        }
        return $instance;
    }

    public function __destruct()
    {
        self::$_db = null;
        $this->conn = null;
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
            echo "\n<br/><b>Cannot run Database.php in verbose mode because the errorlog "
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
            $this->_errorlog("DEBUG: Database - inside database->dbQuery");
            $this->_errorlog("DEBUG: Database - SQL query is " . $sql);
        }
        try {
            $result = $this->conn->query($sql);
        } catch (PDOException $e) {
            if ($ignore_errors) {
                $result = false;
                if (defined ('DVLP_DEBUG') || $this->_verbose) {
                    $this->_errorlog("SQL Error: " . $e->getMessage() . PHP_EOL. $sql);
                }
            } else {
                trigger_error($this->dbError($sql), E_USER_ERROR);
            }
        }

        $this->_errno = $this->conn->errorCode();

        if ($result === false) {
            if ($ignore_errors) {
                return false;
            }
            if ($this->_verbose) {
                $this->_errorlog("DEBUG: Database - SQL caused an error");
                $this->_errorlog("DEBUG: Database - Leaving database->dbQuery");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbSave");
        }

        $sql = "REPLACE INTO $table ($fields) VALUES ($values)";

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbSave");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbDelete");
        }

        $sql = "DELETE FROM `$table`";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbDelete");
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
            $this->_errorlog("DEBUG: Database - Inside dbChange");
        }

        if ($suppress_quotes) {
            $sql = "UPDATE `$table` SET $item_to_set = $value_to_set";
        } else {
            $sql = "UPDATE `$table` SET $item_to_set = '$value_to_set'";
        }

        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - dbChange sql = " . $sql);
        }

        $retval = $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbChange");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbCount");
        }

        $sql = "SELECT COUNT(*) FROM `$table`";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - sql = " . $sql);
        }

        $result = $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbCount");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbCopy");
        }

        $sql = "REPLACE INTO `$table` ($fields) SELECT $values FROM $tablefrom";
        $id_and_value = $this->_buildIdValuePair($id, $value);

        if ($id_and_value === false) {
            return false;
        } else {
            $sql .= $id_and_value;
        }

        $retval = $this->dbQuery($sql);
        $retval = $retval && $this->dbDelete($tablefrom, $id, $value);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbCopy");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbNumRows");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbResult");
            if (empty($recordset) || $recordset === null) {
                $this->_errorlog("DEBUG: Database - Passed recordset is not valid");
            } else {
                $this->_errorlog("DEBUG: Database - Everything looks good");
            }
            $this->_errorlog("DEBUG: Database - Leaving database->dbResult");
        }

        $retval = '';

        if (is_numeric($field)) {
            $field = intval($field, 10);
            $targetRow = $recordset->fetch(\Doctrine\DBAL\FetchMode::NUMERIC, \PDO::FETCH_ORI_ABS, $row);
        } else {
            $targetRow = $recordset->fetch(\Doctrine\DBAL\FetchMode::ASSOCIATIVE, \PDO::FETCH_ORI_ABS, $row);
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

        $result_type = $both ? \Doctrine\DBAL\FetchMode::MIXED  : \Doctrine\DBAL\FetchMode::ASSOCIATIVE;

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
        $result_type = $both ? \Doctrine\DBAL\FetchMode::MIXED : \Doctrine\DBAL\FetchMode::ASSOCIATIVE;

        if ($recordset === false || $recordset === null) return array();

        try {
            $result = $recordset->fetchAll($result_type);
        } catch (Exception $e) {
            $result = false;
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
        return self::$_db->lastInsertId();
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
        if ((int)$this->conn->errorCode() > 0) {
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
            $info = self::$_db->errorInfo();

            if (empty($fn)) {
                $errorMessage = $this->conn->errorCode() . ': '.$info[2];
                if ( $sql != '' ) {
                    $errorMessage .= " SQL in question: " . $sql;
                }
                $this->_errorlog($errorMessage);
            } else {
                $errorMessage = $this->conn->errorCode() . ': ' . $info[2] . " in " . $fn;
                if ( $sql != '' ) {
                    $errorMessage .= " SQL in question: ".$sql;
                }
                $this->_errorlog($errorMessage);
            }

            if ($this->_display_error) {
                return  $this->conn->errorCode() . ': ' . $info[2];
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
            $this->_errorlog("DEBUG: Database - Inside database->dbLockTable");
        }

        $sql = "LOCK TABLES `$table` WRITE";

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbLockTable");
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
            $this->_errorlog("DEBUG: Database - Inside database->dbUnlockTable");
        }

        $sql = 'UNLOCK TABLES';

        $this->dbQuery($sql);

        if ($this->_verbose) {
            $this->_errorlog("DEBUG: Database - Leaving database->dbUnlockTable");
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
        $value = $this->conn->quote($value);
        $value = substr($value, 1, -1);
        return $value;
    }

    /**
     * @return     string     the version of the database application
     */
    public function dbGetVersion()
    {
        return $this->_mysql_version;
    }

    /**
     * Return driver name
     *
     * @return  string
     */
    public function dbGetDriverName()
    {
        return $this->driverName . ' / '. $this->internalDriverName;
    }

    public function dbStartTransaction()
    {
        return self::$_db->beginTransaction();
    }

    public function dbCommit()
    {
        return self::$_db->commit();
    }

    public function dbRollback()
    {
        return self::$_db->rollBack();
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
        $version = $this->conn->query('select version()')->fetchColumn();
        preg_match("/^[0-9\.]+/", $version, $match);
        return $match[0];
    }

    public function dbGetServerVersion()
    {
        return $this->_mysql_version;
    }

// helper functions

    /**
    * Return SQL expression to check for permissions.
    *
    * Creates part of an SQL expression that can be used to request items with the
    * standard set of glFusion permissions.
    *
    * @param        string      $type     part of the SQL expr. e.g. 'WHERE', 'AND'
    * @param        int         $u_id     user id or 0 = current user
    * @param        int         $access   access to check for (2=read, 3=r&write)
    * @param        string      $table    table name if ambiguous (e.g. in JOINs)
    * @return       string      SQL expression string (may be empty)
    *
    */
    public function getPermSQL($type = 'WHERE', $u_id = 0, $access = 2, $table = '')
    {
        global $_USER, $_GROUPS;

        if (!empty($table)) {
            $table .= '.';
        }

        $uid = $u_id;
        if ($uid <= 0) {
            $uid = $_USER['uid'];
            if (COM_isAnonUser()) {
                $uid = 1;
            }
        }

        $UserGroups = array();
        if ((empty( $_USER['uid']) && ($uid == 1)) || ($uid == $_USER['uid'])) {
            if (empty($_GROUPS)) {
                $_GROUPS = SEC_getUserGroups($uid);
            }
            $UserGroups = $_GROUPS;
        } else {
            $UserGroups = SEC_getUserGroups($uid);
        }

        if (empty($UserGroups)) {
            // this shouldn't really happen, but if it does, handle user
            // like an anonymous user
            $uid = 1;
        }

        if (SEC_inGroup('Root', $uid)) {
            return '';
        }

        $sql = ' ' . $type . ' (';

        if ($uid > 1) {
            $sql .= "(({$table}owner_id = '{$uid}') AND ({$table}perm_owner >= $access)) OR ";

            $sql .= "(({$table}group_id IN (" . implode( ',', $UserGroups )
                 . ")) AND ({$table}perm_group >= $access)) OR ";
            $sql .= "({$table}perm_members >= $access)";
        } else {
            $sql .= "(({$table}group_id IN (" . implode( ',', $UserGroups )
                 . ")) AND ({$table}perm_group >= $access)) OR ";
            $sql .= "({$table}perm_anon >= $access)";
        }

        $sql .= ')';

        return $sql;
    }

    /**
    * Adds appropriate WHERE expressesion to check for permissions.
    *
    * Creates part of an SQL expression that can be used to request items with the
    * standard set of glFusion permissions.
    *
    * @param        string      $type     part of the SQL expr. e.g. 'WHERE', 'AND'
    * @param        int         $u_id     user id or 0 = current user
    * @param        int         $access   access to check for (2=read, 3=r&write)
    * @param        string      $table    table name if ambiguous (e.g. in JOINs)
    * @return       string      SQL expression string (may be empty)
    *
    */
//WIP Concept
    public function qbGetPermSQL($queryBuilder, $type = '', $u_id = 0, $access = 2, $table = '')
    {
        global $_USER, $_GROUPS;

        // $type is either blank (implies WHERE, AND or OR)
        switch (strtolower($type)) {
            case 'where' :
                $method = 'where';
                break;
            case 'and' :
                $method = 'andWhere';
                break;
            case 'or' :
                $method = 'orWhere';
                break;
            default :
                $method = 'andWhere';
                break;
        }

        $uid = $u_id;
        if ($uid <= 0) {
            $uid = $_USER['uid'];
            if (COM_isAnonUser()) {
                $uid = 1;
            }
        }

        $userGroups = array();
        if ((empty( $_USER['uid']) && ($uid == 1)) || ($uid == $_USER['uid'])) {
            if (empty($_GROUPS)) {
                $_GROUPS = SEC_getUserGroups($uid);
            }
            $userGroups = $_GROUPS;
        } else {
            $userGroups = SEC_getUserGroups($uid);
        }

        if (empty($userGroups)) {
            // this shouldn't really happen, but if it does, handle user
            // like an anonymous user
            $uid = 1;
        }

        if (SEC_inGroup('Root', $uid)) {
            return '';
        }

        if (!empty($table)) {
            $table = $table.'.';
        }

        if ($uid > 1) {
            $queryBuilder->$method(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq($table.'owner_id',
                          $queryBuilder->createNamedParameter($uid,\glFusion\Database::INTEGER)
                        ),
                        $queryBuilder->expr()->gte($table.'perm_owner',
                          $queryBuilder->createNamedParameter($access,\glFusion\Database::INTEGER)
                        )
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->in($table.'group_id',
                          $queryBuilder->createNamedParameter($userGroups,\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                        ),
                        $queryBuilder->expr()->gte($table.'perm_group',$access,\glFusion\Database::INTEGER)
                    ),
                    $queryBuilder->expr()->gte($table.'perm_members',$access,\glFusion\Database::INTEGER)
                )
            );
        } else {
            $queryBuilder->$method(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->in($table.'group_id',
                          $queryBuilder->createNamedParameter($userGroups,\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                        ),
                        $queryBuilder->expr()->gte($table.'perm_group',$access,\glFusion\Database::INTEGER)
                    ),
                    $queryBuilder->expr()->gte($table.'perm_anon',$access,\glFusion\Database::INTEGER)
                )
            );
        }
        return;
    }

    /**
    * Return SQL expression to request language-specific content
    *
    * Creates part of an SQL expression that can be used to request items in the
    * current language only.
    *
    * @param    string  $field  name of the "id" field, e.g. 'sid' for stories
    * @param    string  $type   part of the SQL expression, e.g. 'WHERE', 'AND'
    * @param    string  $table  table name if ambiguous, e.g. in JOINs
    * @return   string          SQL expression string (may be empty)
    *
    */

    // we may make part of an overall query builder call
    // so we can pass the object for the querybuilder
    // and add the appropriate addwhere() calls
    // if for some reason we need the actual SQL we could pass
    // a null query builder object????


    public function getLangSQL($field, $type = 'WHERE',$table = '')
    {
        global $_CONF, $_TABLES;

        $sql = '';

        // do some validations
        // type should only be WHERE, AND, OR, blank

        if (empty($_CONF['languages']) || empty($_CONF['language_files'])) {
            // no multi-language support
            return $sql;
        }

        if (!empty( $table)) {
            $table .= '.';
        }

        $lang_id = COM_getLanguageId();

        if (!empty($lang_id)) {
            $sql = ' ' . $type . " ({$table}$field LIKE '%\\_$lang_id')";
        }
        return $sql;
    }

    /**
    * Return SQL expression to check for allowed topics.
    *
    * Creates part of an SQL expression that can be used to only request stories
    * from topics to which the user has access to.
    *
    * Note that this function does an SQL request, so you should cache
    * the resulting SQL expression if you need it more than once.
    *
    * @param    string  $type   part of the SQL expr. e.g. 'WHERE', 'AND'
    * @param    int     $u_id   user id or 0 = current user
    * @param    string  $table  table name if ambiguous (e.g. in JOINs)
    * @return   string          SQL expression string (may be empty)
    *
    */
    public function getTopicSQL($type = 'WHERE', $u_id = 0, $table = '')
    {
        global $_TABLES, $_USER, $_GROUPS;

        $UserGroups = array();
        $tids = array();

        if (($u_id <= 0) || (isset($_USER['uid']) && $u_id == $_USER['uid'])) {
            if (!COM_isAnonUser()) {
                $uid = $_USER['uid'];
            } else {
                $uid = 1;
            }
        }

        // root users have access to all data
        if (SEC_inGroup('Root', $uid)) {
            return '';
        }

        $UserGroups = SEC_getUserGroups($uid);

        $topicsql = ' ' . $type . ' ';

        if (!empty( $table)) {
            $table .= '.';
        }

        if (empty($UserGroups)) {
            // this shouldn't really happen, but if it does, handle user
            // like an anonymous user
            $uid = 1;
        }
// need to build a query that returns all topics we have access

        $sql = "SELECT tid FROM `{$_TABLES['topics']}` "
               . $this->getPermSQL('WHERE',$uid);

        $stmt = $this->conn->query($sql);
        while ($T = $stmt->fetch()) {
            $tids[] = $T['tid'];
        }

        if (sizeof($tids) > 0) {
            $topicsql .= "({$table}tid IN ('" . implode( "','", $tids ) . "'))";
        } else {
            $topicsql .= '0';
        }

        return $topicsql;
    }

    /**
    * Adds appropriate WHERE statements to QueryBuilder object
    *
    * Creates part of an SQL expression that can be used to only request stories
    * from topics to which the user has access to.
    *
    * Note that this function does an SQL request, so you should cache
    * the resulting SQL expression if you need it more than once.
    *
    * @param    object  $queryBuilder - queryBuilder handle
    * @param    string  $type   part of the SQL expr. e.g. 'WHERE', 'AND'
    * @param    int     $u_id   user id or 0 = current user
    * @param    string  $table  table name if ambiguous (e.g. in JOINs)
    * @return   none
    *
    */
    public function qbGetTopicSQL($queryBuilder, $type = 'WHERE', $u_id = 0, $table = '')
    {
        global $_TABLES, $_USER;

        // $type is either WHERE, blank (implies AND), AND or OR)
        switch (strtolower($type)) {
            case 'where' :
                $method = 'where';
                break;
            case 'and' :
                $method = 'andWhere';
                break;
            case 'or' :
                $method = 'orWhere';
                break;
            default :
                $method = 'andWhere';
                break;
        }

        $tids = array();

        if (($u_id <= 0) || (isset($_USER['uid']) && $u_id == $_USER['uid'])) {
            if (!COM_isAnonUser()) {
                $uid = $_USER['uid'];
            } else {
                $uid = 1;
            }
        }
        // root users have access to all topics
        if (SEC_inGroup('Root', $uid)) {
            return;
        }
        if (!empty( $table)) {
            $table .= '.';
        }

        // retrieve all topics the user has access to
        $db = \glFusion\Database::getInstance();
        $topicQB = $db->conn->createQueryBuilder();
        $topicQB->select('tid')
                ->from($_TABLES['topics']);
        $this->qbGetPermSQL($topicQB,'WHERE',$uid);
        $topicStmt = $topicQB->execute();
        $tids = $topicStmt->fetchAll(\PDO::FETCH_COLUMN);

        if (count($tids) > 0) {
            $queryBuilder->$method(
                $queryBuilder->expr()->in($table.
                  'tid',
                   $queryBuilder->createNamedParameter($tids,\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                   )
            );
        }
        return;
    }
}
?>