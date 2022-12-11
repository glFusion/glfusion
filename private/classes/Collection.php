<?php
/**
 * Base Class to handle searching and retrieving objects.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v2.1.0
 * @since       v2.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion;
use glFusion\Database\Database;
use glFusion\Log\Log;
use glFusion\Cache\Cache;
use Doctrine\DBAL\Query\QueryBuilder;


/**
 * Class to handle searching and retrieving collections of objects.
 * @package glfusion
 */
class Collection
{
    /** Database object.
     * @var object */
    protected $_db = NULL;

    /** QueryBuilder object.
     * @var object */
    protected $_qb = NULL;

    /** Result statement object.
     * @var object */
    protected $_stmt = false;

    /** Tags to add for caching.
     * @var array */
    protected $cache_tags = array();

    /** Cache key constructed from SQL query and parameters.
     * @var string */
    private $_cache_key = '';

    /** Flag to use the cache.
     * Normally true may be disabled by the caller.
     * @var boolean */
    private $_useCache = true;

    /** Holder for the number of rows returned from a query.
     * NULL indicates that the query has not yet been executed.
     * @var integer */
    private $_rowcount = NULL;


    /**
     * Instantiate the Database and QueryBuilder objects.
     */
    public function __construct()
    {
        $this->_db = Database::getInstance();
        $this->_qb = $this->_db->conn->createQueryBuilder();
    }


    /**
     * Generic function to add a `where` clause.
     *
     * @see     self::setParameter()
     * @param   string  $sql    SQL to add
     * @return  object  $this
     */
    public function andWhere(string $sql) : self
    {
        $this->_qb->andWhere($sql);
        return $this;
    }


    /**
     * Generic function to add a parameter value for `andWhere` if needed.
     *
     * @see     self::andWhere()
     * @param   string  $id     Parameter ID name used in andWhere()
     * @param   mixed   $value  Value to set
     * @param   integer $type   Type of parameter
     * @return  object  $this
     */
    public function setParameter(string $id, $value, int $type=Database::STRING) : self
    {
        $this->_qb->setParameter($id, $value, $type);
        return $this;
    }


    /**
     * Set the limit for the result set.
     *
     * @param   integer $start  Starting record, or max if $max is empty
     * @param   integer $max    Max records, optional
     * @return  object  $this
     */
    public function withLimit(int $start, ?int $max=NULL) : self
    {
        if (is_null($max)) {
            $this->_qb->setFirstResult(0)
                      ->setMaxResults($start);
        } else {
            $this->_qb->setFirstResult($start)
                      ->setMaxResults($max);
        }
        return $this;
    }


    /**
     * Add an orderby clause.
     *
     * @param   string  $fld    Field to use for ordering
     * @param   string  $dir    Direction, ASC by default
     * @return  object  $this
     */
    public function withOrderBy(string $fld, string $dir='ASC') : self
    {
        $dir = strtoupper($dir);
        $dir = $dir == 'ASC' ? 'ASC' : 'DESC';
        $this->_qb->addOrderBy(Database::getInstance()->conn->quoteIdentifier($fld), $dir);
        return $this;
    }


    /**
     * Return the QueryBuilder object for further query customization.
     *
     * @return  object      QueryBuilder object
     */
    public function getQueryBuilder() : QueryBuilder
    {
        return $this->_qb;
    }


    /**
     * Return the result statement.
     *
     * @return  object      Result object
     */
    public function getStmt() : object
    {
        return $this->_stmt;
    }


    /**
     * Provide a method for the caller to disable caching.
     * Caching is enabled by default but may be undesirable sometimes,
     * such as when ordering by RAND() which would not create a new
     * cache key.
     *
     * @param   boolean $flag   True to enable cache, False to disable
     * @return  object  $this
     */
    public function withCache(bool $flag=true) : self
    {
        $this->_useCache = $flag;
        return $this;
    }


    /**
     * Try to get data from the cache. First constructs the cache key.
     *
     * @param   string  $key    Additional key to prepend
     * @return  array|null  Array of data, NULL if not found
     */
    protected function tryCache(string $key='') : ?array
    {
        if ($this->_useCache) {
            $this->_cache_key = md5($key . $this->_qb->getSQL() . self::json_encode($this->_qb->getParameters()));
            $Cache = Cache::getInstance();
            if ($Cache->has($this->_cache_key)) {
                return $Cache->get($this->_cache_key);
            }
        }
        return NULL;
    }


    /**
     * Set a data array into the cache. Key is created by tryCache().
     *
     * @param   array   $data   Data to set
     * @param   array   $tags   Tags to apply
     */
    protected function setCache(array $data, array $tags=array()) : void
    {
        if ($this->_useCache) {
            Cache::getInstance()->set($this->_cache_key, $data, $tags);
        }
    }


    /**
     * Execute the query and return the Result object.
     *
     * @return  object      Doctrine Result object
     */
    public function execute() : object
    {
        try {
            $this->_stmt = $this->_qb->execute();
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $this->_stmt = false;
        }
        // Update _rowcount to know that the query has been executed.
        if ($this->_stmt) {
            $this->_rowcount = $this->_stmt->rowCount();
        } else {
            $this->_rowcount = 0;
        }
        return $this;
    }


    /**
     * Get the count of rows that would be returned.
     *
     * @return  integer     Row count
     */
    public function getCount() : int
    {
        $retval = 0;
        if ($this->_rowcount === NULL) {
            // Not set yet, execute the query.
            // $this->_rowcount is set in execute().
            $this->execute();
        }
        return $this->_rowcount;
    }


    /**
     * Get the raw records for the events.
     *
     * @return  array       Array of DB rows
     */
    public function getRows() : array
    {
        $rows = $this->tryCache('rows');
        if (is_array($rows)) {
            return $rows;
        }

        $rows = array();
        if ($this->_stmt) {
            while ($row = $this->_stmt->fetchAssociative()) {
                $rows[] = $row;
            }
            // only cache a good DB response
            $this->setCache($rows, $this->cache_tags);
        }
        return $rows;
    }


    /**
     * Encode an array to a JSON string.
     * Simple wrapper for json_encode() but makes sure a valid JSON string
     * is returned.
     *
     * @param   array   $arr    Array to encode to JSON
     * @return  string      JSON string
     */
    public static function json_encode(array $arr) : string
    {
        try {
            $retval = json_encode($arr, true);
        } catch (\Exception $e) {
            $retval = false;
        }
        if (empty($retval)) {
            $retval = '[]';
        }
        return $retval;
    }


    /**
     * Read the data into objects.
     * The base class can't implement this since the object type
     * is not known.
     *
     * @return  array       Array of objects
     */
    public function readObjects()
    {
        return array();
    }

}

