<?php
/**
* glFusion CMS
*
* Database Driver - utilizing Doctrine's DBAL
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*
*/

namespace glFusion\Database;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Cache\GlFusionCache;

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
    public const PARAM_INT_ARRAY = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
    public const PARAM_STR_ARRAY = \Doctrine\DBAL\Connection::PARAM_STR_ARRAY;
    public const BINARY = 16;

    /**
    * @var string
    */
    private $driverName = 'dbal';

    /**
    * @var string
    */
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
    public $conn = null;

    /**
    * @var bool
    */
    private $_verbose = true;

    /**
    * @var bool
    */
    private $_ignore = false;

    /**
    * @var bool
    */
    public $_display_error = true;

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
    public $_mysql_version = 0;

    /**
    * @var int
    */
    private $_filter = 1;

    /**
    * @var int
    */
    private $_errno = 0;

    public $cacheHandle = null;

    /**
    * Logs messages
    *
    * Logs messages by calling the function held in $_errorlog_fn
    *
    * @param    string $msg Message to log
    * @access   private
    */
    public function _errorlog($msg)
    {
        $function = $this->_errorlog_fn;
        if (function_exists($function)) {
            $msgL = preg_replace('!\s+!', ' ', $msg);
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
        global $_CONF;

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
        );
        if ($this->_charset === 'utf-8' && extension_loaded('pdo_mysql')) {
            $connectionParams['driverOptions'] = [1002 => "SET NAMES '".$this->_character_set_database."'"];
        }

        $cache = new glFusionCache();

        $this->cacheHandle = $cache;

        $config->setResultCacheImpl($cache);

        $db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        $db->setFetchMode( \Doctrine\DBAL\FetchMode::MIXED );

        $this->_mysql_version = $db->getWrappedConnection()->getServerVersion();

        $this->conn = $db;

        if ($this->_charset === 'utf-8') {
            $result = false;

            if ( $this->_character_set_database == '' ) {
                $result = $this->conn->query("SELECT @@character_set_database");
                $collation = $result->fetch();
                $this->_character_set_database = $collation["@@character_set_database"];
            }
            if (version_compare($this->_mysql_version,'5.5.3','>=')) {
                if ( $this->_character_set_database == "utf8mb4" ) {
                    if (method_exists($this->conn, 'set_charset')) {
                        $result = $this->conn->set_charset('utf8mb4');
                    }

                    if (!$result) {
                        $this->conn->query("SET NAMES 'utf8mb4'");
                    }
                    $this->_filter = 0;
                } else {
                    if (method_exists($this->conn, 'set_charset')) {
                        $result = $this->conn->set_charset('utf8');
                    }

                    if (!$result) {
                        $this->conn->query("SET NAMES 'utf8'");
                    }
                }
            } else {
                if (method_exists($this->conn, 'set_charset')) {
                    $result = $this->conn->set_charset('utf8');
                }
                if (!$result) {
                    $this->conn->query("SET NAMES 'utf8'");
                }
            }
        }

        if (version_compare($this->_mysql_version,'5.7.0','>=')) {
            $result = $this->conn->query("SELECT @@sql_mode");
            $modeData = $result->fetch();
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
                    $this->conn->query("SET sql_mode = '".$updatedMode."'");
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
    * @param        string      $charset    character set of site
    * @param        string      $db_charset character of db
    */
    public function __construct($dbhost, $dbname, $dbuser, $dbpass, $charset = '', $db_charset = '' )
    {
        $this->_host = $dbhost;
        $this->_name = $dbname;
        $this->_user = $dbuser;
        $this->_pass = $dbpass;
        $this->_verbose = false;
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
            $instance = new Database($_DB_host, $_DB_name, $_DB_user, $_DB_pass,
                     $_CONF['default_charset'], $_CONF['db_charset']);
        }
        return $instance;
    }

    public function __destruct()
    {
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
     * Turns debug mode on
     * Set this to TRUE to see debug messages
     *
     * @param    bool $flag
     */
    public function setIgnore($flag)
    {
        $this->_ignore = (bool) $flag;
    }

    /**
     * Turns debug mode on
     * Set this to TRUE to see debug messages
     *
     * @param    bool $flag
     */
    public function getIgnore()
    {
        return (bool) $this->_ignore;
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


    // criteria will be an array (fieldname => value)
    public function getItem($table, $column, $criteria = array())
    {
        global $_TABLES;

        $whereItems = array();
        $whereTypes = array();

        $whereSQL = '';

        $andCounter = 0;

        foreach ($criteria AS $dbColumn => $dbValue) {
            if ($andCounter > 0) {
                $whereSQL .= " AND ";
            }
            $whereSQL .= $this->conn->quoteIdentifier($dbColumn) . ' = ?';

            $andCounter++;

            $whereItems[] = $dbValue;
            $whereTypes[] = Database::STRING;
        }
        $sql = "SELECT " . $this->conn->quoteIdentifier($column) . " FROM `".$table."` WHERE " . $whereSQL;

        $retval = $this->conn->fetchColumn($sql,$whereItems,0,$whereTypes);

        return $retval;

    }

    /**
    * Returns count of rows matching column / value pairs
    *
    * $column and $value must match - either both a string or array
    * and the number of elements in the array must be the same
    *
    * @param    string      $table  Table to count
    * @param    mixed       $column Column or array of columns to query
    * @param    mixed       $value  Values or array of values to query
    * @params   mixed       $type   Data type or array of data types
    */
    public function getCount($table, $column = '', $value = '', $type = '')
    {
        global $_TABLES;

        $params = array();
        $types  = array();

        $sql = "SELECT COUNT(*) FROM `$table`";

        if (is_array($column) || is_array($value)) {
            $num_ids = count($column);

            if (is_array($column) && is_array($value) && ($num_ids === count($value))) {
                $sql .= ' WHERE ';

                for ($i = 1; $i <= $num_ids; $i ++) {
                    $sql .= $this->conn->quoteIdentifier(current($column)) . ' = ? ';
                    $params[] = current($value);

                    if (empty(current($type))) {
                        $type[] = Database::STRING;
                    } else {
                        $type[] = current($type);
                    }

                    if ($i !== $num_ids) {
                        $sql .= " AND ";
                    }
                    next($column);
                    next($value);
                    next($type);
                }
            } else {
                // error, they both have to be arrays and of the same size
                return false;
            }
        } else {
            // just regular string values, build sql
            if (!empty($column) && (isset($value) || ($value != ''))) {
                $sql .= " WHERE ".$this->conn->quoteIdentifier($column)." = ?";
                $params[] = $value;
                if (!is_array($type)) {
                    $types[] = $type;
                } else {
                    $types[] = Database::STRING;
                }
            }
        }

        $retval = $this->conn->fetchColumn($sql,$params,0,$types);
        if ($retval === false) {
            return false;
        }
        return (int) $retval;
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

    public function getFilter()
    {
        return $this->_filter;
    }

    public function getErrno()
    {
        return $this->_errno;
    }

    public function dbGetServerVersion()
    {
        return $this->_mysql_version;
    }

    public function dbError($msg,$sql = '')
    {
        $retval = '';

        $fn = '';
        $btr = debug_backtrace();
        $sql = preg_replace('!\s+!', ' ', $sql);

        if (! empty($btr)) {
            for ($i = 0; $i < 100; $i++) {
                if (isset($btr[$i])) {
                    $b = $btr[$i];
                    if (!empty($b['file']) && !empty($b['line'])) {
                        $fn = $b['file'] . ':' . $b['line'];
                    }
                    break;
                } else {
                    break;
                }
            }
        }

        if (empty($fn)) {
            $errorMessage = $msg;
            if ( $sql != '' ) {
                $errorMessage .= " SQL in question: " . $sql;
            }
            $this->_errorlog($errorMessage);
        } else {
            $errorMessage = $fn . ': ' . $msg;
            if ( $sql != '' ) {
                $errorMessage .= " SQL in question: ".$sql;
            }
            $this->_errorlog($errorMessage);
        }

        if ($this->_display_error) {
            $retval =  $this->conn->errorCode() . ': ' . $msg;
        } else {
            $retval = 'An SQL error has occurred. Please see error.log for details.';
        }
        print $retval;
        exit;
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

        $UserGroups = array_map('intval', $UserGroups);
        $sql = ' ' . $type . ' (';

        if ($uid > 1) {
            $sql .= "(({$table}owner_id = ".(int) $uid. ") AND ({$table}perm_owner >= ".(int)$access.")) OR ";

            $sql .= "(({$table}group_id IN (" . implode( ',', $UserGroups )
                 . ")) AND ({$table}perm_group >= ".(int)$access.")) OR ";
            $sql .= "({$table}perm_members >= $access)";
        } else {
            $sql .= "(({$table}group_id IN (" . implode( ',', $UserGroups )
                 . ")) AND ({$table}perm_group >= ".(int)$access.")) OR ";
            $sql .= "({$table}perm_anon >= ".(int)$access.")";
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
                          $queryBuilder->createNamedParameter($uid,Database::INTEGER)
                        ),
                        $queryBuilder->expr()->gte($table.'perm_owner',
                          $queryBuilder->createNamedParameter($access,Database::INTEGER)
                        )
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->in($table.'group_id',
                          $queryBuilder->createNamedParameter($userGroups,\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                        ),
                        $queryBuilder->expr()->gte($table.'perm_group',$access,Database::INTEGER)
                    ),
                    $queryBuilder->expr()->gte($table.'perm_members',$access,Database::INTEGER)
                )
            );
        } else {
            $queryBuilder->$method(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->in($table.'group_id',
                          $queryBuilder->createNamedParameter($userGroups,\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                        ),
                        $queryBuilder->expr()->gte($table.'perm_group',$access,Database::INTEGER)
                    ),
                    $queryBuilder->expr()->gte($table.'perm_anon',$access,Database::INTEGER)
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
                $u_id = $_USER['uid'];
            } else {
                $u_id = 1;
            }
        }

        // root users have access to all data
        if (SEC_inGroup('Root', $u_id)) {
            return '';
        }

        $UserGroups = SEC_getUserGroups($u_id);

        $topicsql = ' ' . $type . ' ';

        if (!empty( $table)) {
            $table .= '.';
        }

        if (empty($UserGroups)) {
            // this shouldn't really happen, but if it does, handle user
            // like an anonymous user
            $u_id = 1;
        }

        $sql = "SELECT tid FROM `{$_TABLES['topics']}` "
               . $this->getPermSQL('WHERE',$u_id);

        try {
            $stmt = $this->conn->query($sql);
        } catch(Throwable | \Doctrine\DBAL\DBALException $e) {
            if (defined('DVLP_DEBUG')) {
                throw($e);
            }
        }
        if ($stmt) {
            while ($T = $stmt->fetch()) {
                $tids[] = $this->conn->quote($T['tid']);
            }
        }

        if (sizeof($tids) > 0) {
            $topicsql .= "({$table}tid IN (" . implode( ",", $tids ) . "))";
        } else {
            $topicsql .= '0';
        }

        return $topicsql;
    }

    /**
    * Common function used to build group access SQL
    * Field ID can include a table identifier, e.g. 'tbl.fldname'
    *
    * @param   string  $clause    Optional parm 'WHERE' - default is 'AND'
    * @param   string  $field     Optional field name, default is 'grp_access'
    * @return  string  $groupsql  Formatted SQL string to be appended in calling script SQL statement
    */
    public function getAccessSQL($clause = 'AND', $field = 'grp_access')
    {
        global $_GROUPS;

        if (empty ($_GROUPS)) {
            $_GROUPS = SEC_getUserGroups();
        }
        $groupsql = '';
        if (count($_GROUPS) == 1) {
            $groupsql .= " $clause $field = '" . intval(current($_GROUPS)) ."'";
        } else {
            $group_arr = array_map(
                    function($group) { return (int) intval($group); },
                    array_values($_GROUPS)
            );
            $groupsql .= " $clause $field IN (" . implode(', ',$group_arr) .")";
        }

        return $groupsql;
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
        $db = Database::getInstance();
        $topicQB = $db->conn->createQueryBuilder();
        $topicQB->select('tid')
                ->from($_TABLES['topics']);
        $this->qbGetPermSQL($topicQB,'WHERE',$uid);
        $topicStmt = $topicQB->execute();
        $tids = $topicStmt->fetchAll(\PDO::FETCH_COLUMN);

        if (count($tids) > 0) {
            $queryBuilder->$method(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in($table.
                      'tid',
                       $queryBuilder->createNamedParameter($tids,\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                       ),
                    $queryBuilder->expr()->in($table.
                      'alternate_tid',
                       $queryBuilder->createNamedParameter($tids,\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                       )
                )
            );
        }
        return;
    }
}
?>