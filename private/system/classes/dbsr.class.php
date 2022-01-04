<?php
/**
* glFusion CMS
*
* glFusion Search / Replace
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Borrowed heavily on concept from WordPress Better Search Tool
*  https://bettersearchreplace.com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;
use \glFusion\Cache\Cache;

class dbsr
{
	private $prefix = '';

	/**
	 * Initializes the class and its properties.
	 * @access public
	 */
	public function __construct()
	{
		global $_DB_table_prefix;

		$this->prefix = $_DB_table_prefix;
	}


   /**
    *   Builds the list of tables to process
    *   Generates the internal array of tables to backup, including only
    *   tables defined in the $_TABLES array and excluding any tables listed
    *   in the exclusion array
    *
    *   @return array - array of table names
    */
    public function getTableList( $tablesToUse = array() )
    {
		$db = Database::getInstance();

        // Get all tables in the database
        $mysql_tables = array();
		$pfLength = strlen($this->prefix);

		$stmt = $db->conn->executeQuery("SHOW TABLES");
		while ($row = $stmt->fetchColumn()) {
            if (!empty($this->prefix)) {
                $prefix = substr($row,0,$pfLength);
                if ($prefix == $this->prefix) {
                    $mysql_tables[] = $row;
				}
            } else {
                $mysql_tables[] = $row;
            }
        }
        $this->tablenames = $mysql_tables;

		if (!is_array($tablesToUse)) {
            $tablesTouse = array($tablesToUse);
 		}

        $this->tablenames = array_intersect($this->tablenames, $tablesToUse);
        return $this->tablenames;
    }

	/**
	 * Gets the columns in a table.
	 * @access public
	 * @param  string $table The table to check.
	 * @return array
	 */
	public function getColumns( $table )
	{
		$db = Database::getInstance();

		$primary_key 	= null;
		$columns 		= array();
		$fields  		= $db->conn->fetchAll( 'DESCRIBE ' . $table );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				$columns[] = $column['Field'];
				if ( $column['Key'] == 'PRI' ) {
					$primary_key = $column['Field'];
				}
			}
		}

		return array( $primary_key, $columns );
	}

	/**
	 * Adapated from interconnect/it's search/replace script.
	 *
	 * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
	 *
	 * @access public
	 * @param  string 	$table 	The table to run the replacement on.
	 * @param  int 		$page  	The page/block to begin the query on.
	 * @param  array 	$args 	An associative array containing arguements for this run.
	 * @return array
	 */
	public function srdb($table, $primary_key, $columnsToProcess, $args)
	{

		$db = Database::getInstance();

		// Load up the default settings for this chunk.
		$done 			= false;

		$table_report = array(
			'change' 	=> 0,
			'updates' 	=> 0,
			'start' 	=> microtime( true ),
			'end'		=> microtime( true ),
			'errors' 	=> array(),
		);

		// Get a list of columns in this table.
		list( $primary_key, $columns ) = $this->getColumns($table);

		// If no primary key - cannot move forward
		if ($primary_key === null) {
			$table_report['skipped'] = true;
			return array( 'table_complete' => true, 'table_report' => $table_report );
		}

		$current_row 	= 0;

		// Grab all the data from all the tables...
        $stmt = $db->conn->executeQuery(
            "SELECT * FROM `$table`"
        );

		$diffs = array();

		// Loop through the data.
		while (($row = $stmt->fetchAssociative()) !== false) {
			$current_row++;
			$update_sql = array();
			$where_sql 	= array();
			$upd 		= false;

			foreach( $columns as $column ) {
				if ($column != $primary_key && !in_array($column,$columnsToProcess)) {
					continue;
				}
				if ( $column == $primary_key ) {
					$where_sql[] = $column . ' = "' .  $this->mysql_escape_mimic( $row[$column] ) . '"';
					continue;
				}
				$data_to_process = $row[$column];

				// Run a search replace on the data
				$edited_data = $this->replace( $args['search_for'], $args['replace_with'], $data_to_process, $args['case_insensitive'] );

				// Data was changed
				if ($edited_data != $data_to_process) {
					$update_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $edited_data ) . '"';
					$upd = true;
					$table_report['change']++;
					if ($args['dry_run'] === 'on') {
						$table_report['diffs'][] = array($data_to_process,$edited_data,$column);
					}
				}
			}

			// Determine what to do with updates.
			if ( $args['dry_run'] === 'on' ) {
				// No SQL on dry run
			} elseif ( $upd && ! empty( $where_sql ) ) {
				// otherwise run the query
				$sql 	= 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
				try {
					$result = $db->conn->executeQuery($sql);
				} catch (\Exception $e) {
					$table_report['errors'][] = sprintf('Error updating row: %d.', $current_row );
					continue;
				}
				$table_report['updates']++;
			}
		}

		$done = true;

		$table_report['end'] = microtime( true );
		if ($args['dry_run'] !== 'on') {
			Cache::getInstance()->clear();
		}

		return array( 'table_complete' => $done, 'table_report' => $table_report );
	}

	public function replace( $from = '', $to = '', $data = '', $case_insensitive = false )
	{
		try {
			if ( is_string( $data ) ) {
				$data = $this->str_replace( $from, $to, $data, $case_insensitive );
			}
		} catch( Exception $error ) {
			// ignore errors
		}

		return $data;
	}

	/**
	 * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
	 * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
	 * @access public
	 * @param  string $input The string to escape.
	 * @return string
	 */
	public function mysql_escape_mimic( $input ) {
	    if ( is_array( $input ) ) {
	        return array_map( __METHOD__, $input );
	    }
	    if ( ! empty( $input ) && is_string( $input ) ) {
	        return str_replace( array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ), array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ), $input );
	    }

	    return $input;
	}

	/**
	 * Wrapper for str_replace
	 */
	public function str_replace( $from, $to, $data, $case_insensitive = false ) {
		if ( 'on' === $case_insensitive ) {
			$data = str_ireplace( $from, $to, $data );
		} else {
			$data = str_replace( $from, $to, $data );
		}
		return $data;
	}

}
