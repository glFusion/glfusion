<?php
/**
* glFusion CMS
*
* Legacy Database Driver - backward compatibility with glFusion v1.x
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on orignal work by the following authors:
*  Copyright (C) 2000-2008 by
*      Tony Bibbs, tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

/*
if ( isset($_SYSTEM['no_fail_sql']) && $_SYSTEM['no_fail_sql'] == 1 ) {
    $_DB->_no_fail = 1;
}
*/

if (isset($_SYSTEM['rootdebug']) && $_SYSTEM['rootdebug']) {
    DB_displayError(true);
}

/**
* @return     string     the version of the database application as integer
*/
function DB_getVersion()
{
    $db = Database::getInstance();

    return $db->_mysql_version;
}


/**
* Turns debug mode on for the database library
*
* Setting this to true will cause the database code to print out
* various debug messages.  Setting it to false will supress the
* messages (is false by default). NOTE: Gl developers have put many
* useful debug messages into the mysql implementation of this.  If
* you are using something other than MySQL and if the GL team did
* not write it then you may or may not get something useful by turning
* this on.
*
* @param        boolean     $flag       true or false
*
*/
function DB_setdebug($flag)
{
    $db = Database::getInstance();
    $db->setVerbose($flag);
}

/** Setting this on will return the SQL error message.
* Default is to not display or return the SQL error but
* to record it in the error.log file
*
* @param        boolean     $flag       true or false
*/
function DB_displayError($flag)
{
    $db = Database::getInstance();
    $db->setDisplayError($flag);
}

/**
* Executes a query on the db server
*
* This executes the passed SQL and returns the recordset or errors out
*
* @param    mixed   $sql            String or array of strings of SQL to be executed
* @param    int     $ignore_errors  If 1 this function supresses any error messages
* @return   object  Returns results from query
*
*/
function DB_query ($sql, $ignore_errors = 0)
{
    global $_SYSTEM, $_DB_dbms;

    $dbError = '';

    $db = Database::getInstance();

    if (is_array ($sql)) {
        if (isset ($sql[$_DB_dbms])) {
            $sql = $sql[$_DB_dbms];
        } else {
            $errmsg = "No SQL request given for DB '$_DB_dbms', only got these:";
            foreach ($sql as $db => $request) {
                $errmsg .= LB . $db . ': ' . $request;
            }
            $result = $errmsg;
            Log::write('system',Log::ERROR,$errmsg);
            die ($result);
        }
    }

    if ( isset($_SYSTEM['no_fail_sql']) && $_SYSTEM['no_fail_sql'] == 1 ) {
        $ignore_errors = true;
    }

    try {
        $result = $db->conn->query($sql);
    } catch (Throwable $e) {
        $err = $db->conn->errorInfo();
        if (isset($err[2])) {
            $dbError = preg_replace('!\s+!', ' ', $err[2]);
        }
        if (defined ('DVLP_DEBUG')) {
            if (class_exists('\glFusion\Log\Log',true)) {
//                Log::write('system',Log::DEBUG,"SQL Error: " . $dbError);
//                Log::write('system',Log::DEBUG,"SQL: " . $sql);
            }
        }
        if ($ignore_errors) {
            return false;
        }
        $db->dbError($dbError,$sql);
    }
    if ($result === false) {
        if ($ignore_errors) {
            return false;
        }
    }
    return $result;

}

/**
* Saves information to the database
*
* This will use a REPLACE INTO to save a record into the
* database.
*
* @param        string      $table          The table to save to
* @param        string      $fields         Comma demlimited list of fields to save
* @param        string      $values         Values to save to the database table
* @param        string      $return_page    URL to send user to when done
*
*/
function DB_save($table,$fields,$values,$return_page='')
{
    $sql = "REPLACE INTO `$table` ($fields) VALUES ($values)";

    DB_query($sql);

    if (!empty($return_page)) {
       echo COM_refresh("$return_page");
    }
}

/**
* Deletes data from the database
*
* This will delete some data from the given table where id = value
*
* @param        string              $table          Table to delete data from
* @param        array|string        $id             field name(s) to use in where clause
* @param        array|string        $value          value(s) to use in where clause
* @param        string              $return_page    page to send user to when done
*
*/
function DB_delete($table,$id,$value,$return_page='')
{
    $sql = "DELETE FROM `$table`";
    $id_and_value = DB_buildIdValuePair($id, $value);

    if ($id_and_value === false) {
        return false;
    } else {
        $sql .= $id_and_value;
    }

    DB_query($sql);

    if (!empty($return_page)) {
        echo COM_refresh("$return_page");
    }
}

/**
* Gets a single item from the database
*
* @param        string      $table      Table to get item from
* @param        string      $what       field name to get
* @param        string      $selection  Where clause to use in SQL
* @return       mixed       Returns value sought
*
*/
function DB_getItem($table,$what,$selection='')
{
    if (!empty($selection)) {
        $sql = "SELECT $what FROM `$table` WHERE $selection";
        $result = DB_query($sql);
    } else {
        $sql = "SELECT $what FROM `$table`";
        $result = DB_query($sql);
    }
	if ($result === false || DB_error($sql) ) {
		return NULL;
	} else if (DB_numRows($result) == 0) {
		return NULL;
	} else {
		$ITEM = DB_fetchArray($result);
		return $ITEM[0];
	}
}

/**
* Changes records in a table
*
* This will change the data in the given table that meet the given criteria and will
* redirect user to another page if told to do so
*
* @param        string          $table              Table to perform change on
* @param        string          $item_to_set        field name to set
* @param        string          $value_to_set       Value to set abovle field to
* @param        array|string    $id                 field name(s) to use in where clause
* @param        array|string    $value              Value(s) to use in where clause
* @param	    string          $return_page        page to send user to when done with change
* @param        boolean         $supress_quotes     whether or not to use single quotes in where clause
*
*/
function DB_change($table,$item_to_set,$value_to_set,$id='',$value='',$return_page='',$supress_quotes=false)
{
    global $_TABLES,$_CONF;

    if ($supress_quotes) {
        $sql = "UPDATE `$table` SET `$item_to_set` = $value_to_set";
    } else {
        $sql = "UPDATE `$table` SET `$item_to_set` = '$value_to_set'";
    }

    $id_and_value = DB_buildIdValuePair($id, $value);

    if ($id_and_value === false) {
        return false;
    } else {
        $sql .= $id_and_value;
    }

    $retval = DB_query($sql);

    if (!empty($return_page)) {
        echo COM_refresh("$return_page");
    }
}

/**
* Count records in a table
*
* This will return the number of records which meet the given criteria in the
* given table.
*
* @param        string              $table      Table to perform count on
* @param        array|string        $id         field name(s) to use in where clause
* @param        array|string        $value      Value(s) to use in where clause
* @return       int     Returns row count from generated SQL
*
*/
function DB_count($table,$id='',$value='')
{
    $sql = "SELECT COUNT(*) FROM `$table`";
    $id_and_value = DB_buildIdValuePair($id, $value);

    if ($id_and_value === false) {
        return false;
    } else {
        $sql .= $id_and_value;
    }
    $result = DB_query($sql);

    return ($result->fetchColumn());
}

/**
* Copies a record from one table to another (can be the same table)
*
* This will use a REPLACE INTO...SELECT FROM to copy a record from one table
* to another table.  They can be the same table.
*
* @param        string          $table          Table to insert record into
* @param        string          $fields         Comma delmited list of fields to copy over
* @param        string          $values         Values to store in database field
* @param        string          $tablefrom      Table to get record from
* @param        array|string   	$id             Field name(s) to use in where clause
* @param        array|string    $value          Value(s) to use in where clause
* @param        string          $return_page    Page to send user to when done
*
*/
function DB_copy($table,$fields,$values,$tablefrom,$id,$value,$return_page='')
{
    $sql = "REPLACE INTO `$table` ($fields) SELECT $values FROM $tablefrom";
    $id_and_value = DB_buildIdValuePair($id, $value);

    if ($id_and_value === false) {
        return false;
    } else {
        $sql .= $id_and_value;
    }

    $retval = DB_query($sql);
    $retval = $retval && DB_delete($tablefrom, $id, $value);

    if (!empty($return_page)) {
        echo COM_refresh("$return_page");
    }
}

/**
* Retrieves the number of rows in a recordset
*
* This returns the number of rows in a recordset
*
* @param        object     $recordset      The recordset to operate one
* @return       int         Returns number of rows returned by a previously executed query
*
*/
function DB_numRows($recordSet)
{
    $db = Database::getInstance();

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
* Retrieves the contents of a field
*
* This returns the contents of a field from a result set
*
* @param        object      $recordset      The recordset to operate on
* @param        int         $row            row to get data from
* @param        string      $field          field to return
* @return       (depends on the contents of the field)
*
*/
function DB_result($recordset,$row,$field)
{
    $db = Database::getInstance();

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
* @param        object     $recordset       The recordset to operate on
* @return       int         Returns the number fields in a result set
*
*/
function DB_numFields($recordset)
{
    $db = Database::getInstance();
    if ($recordset === false || $recordset === null) return 0;
    return $recordset->columnCount();
}

/**
* Retrieves returns the field name for a field
*
* Returns the field name for a given field number
*
* @param        object      $recordset      The recordset to operate on
* @param        int         $fnumber        field number to return the name of
* @return       string      Returns name of specified field
*
*/
function DB_fieldName($recordset,$fnumber)
{
    if ($recordSet === false || $recordSet === null) return '';

    $meta = $recordset->getColumnMeta($fieldNumber);
    if (isset($meta['name'])) return $meta['name'];
    return '';
}

/**
* Retrieves returns the number of effected rows for last query
*
* Retrieves returns the number of effected rows for last query
*
* @param        object      $recordset      The recordset to operate on
* @return       int         returns numbe of rows affected by previously executed query
*
*/
function DB_affectedRows($recordset)
{
    if ($recordset === false || $recordset === null) return 0;
    return $recordset->rowCount();
}

/**
* Retrieves record from a recordset
*
* Gets the next record in a recordset and returns in array
*
* @param        object      $recordset      The recordset to operate on
* @param        boolean     $both           get both assoc and numeric indices
* @return       Array      Returns data for a record in an array
*
*/
function DB_fetchArray($recordset, $both = true)
{
    $db = Database::getInstance();

    if ($recordset === false || $recordset == null) return false;

    $result_type = $both ? \Doctrine\DBAL\FetchMode::MIXED  : \Doctrine\DBAL\FetchMode::ASSOCIATIVE;

    try {
        $result = $recordset->fetch($result_type);
    } catch (PDOException $e) {
        $result = false;
    }

    return ($result === false) ? false : $result;
}

/**
* Retrieves all records from a recordset
*
* Gets all records in a recordset and returns in array
*
* @param        object      $recordset      The recordset to operate on
* @param        boolean     $both           get both assoc and numeric indices
* @return       Array      Returns data for a record in an array
*
*/
function DB_fetchAll($recordset, $both = true)
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
* @param    resources   $link_identifier    identifier for opened link
* @return   int                             Returns the last ID auto-generated
*
*/
function DB_insertId($link_identifier = '')
{
    $db = Database::getInstance();
    return $db->conn->lastInsertId();
}

/**
* returns a database error string
*
* Returns an database error message
*
* @return   string  Returns database error message
*
*/
function DB_error($sql = '')
{
    $db = Database::getInstance();
    if ((int)$db->conn->errorCode() > 0) {
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
//        Log::write('system',Log::ERROR,"SQL Error: " . $db->getErrno());
//        Log::write('system',Log::ERROR,"SQL: " . $sql);

        if ($db->_display_error) {
            return  $db->getErrno() . ': ' . $sql;
        } else {
            return 'An SQL error has occurred. Please see error.log for details.';
        }
    }
}

/**
* Lock a table
*
* Locks a table for write operations
*
* @param    string      $table      Table to lock
* @return   void
* @see DB_unlockTable
*
*/
function DB_lockTable($table)
{
    $sql = "LOCK TABLES `$table` WRITE";

    DB_query($sql);
}

/**
* Unlock a table
*
* Unlocks a table after DB_lockTable
*
* @param    string      $table      Table to unlock
* @return   void
* @see DB_lockTable
*
*/
function DB_unlockTable($table)
{
    $sql = 'UNLOCK TABLES';
    DB_query($sql);
}

/**
 * Check if a table exists
 *
 * @param   string $table   Table name
 * @return  boolean         True if table exists, false if it does not
 *
 */
function DB_checkTableExists($table)
{
    global $_TABLES, $_DB_dbms;

    $exists = false;

    $result = DB_query ("SHOW TABLES LIKE '{$_TABLES[$table]}'");
    if (DB_numRows ($result) > 0) {
        $exists = true;
    }

    return $exists;
}

/**
* escape a string
*
* Escapes special characters in the unescaped_string , taking into account
* the current character set of the connection so that it is safe to place
* it in a SQL query.
*
* @param    string      $str      String to escape
* @return   void
*
*/
function DB_escapeString($str)
{
    $db = Database::getInstance();

    if ( $db->getFilter() != 0 ) {
        $str = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "?", $str);
        $str = preg_replace('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{200D}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?/u', '?', $str);
    }
    $value = $db->conn->quote($str);
    $value = substr($value, 1, -1);
    return $value;
}

/**
* @return     int     the version of the client libraries application as integer
*/
function DB_getClientVersion()
{
    $db = Database::getInstance();
    $version = $db->conn->query('select version()')->fetchColumn();
    preg_match("/^[0-9\.]+/", $version, $match);
    return $match[0];
}

/**
* @return     int     the version of the database application as integer
*/
function DB_getServerVersion()
{
    $db = Database::getInstance();

    return $db->dbGetServerVersion();
}

/**
* @return     string     the database driver name
*/
function DB_getDriverName()
{
    $db = Database::getInstance();

    return $db->dbGetDriverName();
}

function DB_executeUpdate($sql,$params = array(),$types = array())
{
    $db = Database::getInstance();

    $ignore_errors = false;
    if ( isset($_SYSTEM['no_fail_sql']) && $_SYSTEM['no_fail_sql'] == 1 ) {
        $ignore_errors = true;
    }

    try {
        $result = $db->conn->executeUpdate($sql,$params,$types);
    } catch (Exception $e) {
        if ($ignore_errors) {
            $result = false;
        } else {
            trigger_error(DB_error($sql), E_USER_ERROR);
        }
    }
    return $result;
}

function DB_buildIdValuePair($id, $value)
{
    $retval = '';

    if (is_array($id) || is_array($value)) {
        $num_ids = count($id);

        if (is_array($id) && is_array($value) && ($num_ids === count($value))) {
            // they are arrays, traverse them and build sql
            $retval .= ' WHERE ';

            for ($i = 1; $i <= $num_ids; $i ++) {
                $retval .= current($id) . " = '"
                .  DB_escapeString(current($value)) . "'";
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

?>