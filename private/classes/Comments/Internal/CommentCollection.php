<?php
/**
 * Class to handle comment collections.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner
 * @package     glfusion
 * @version     v2.1.0
 * @since       v2.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Comments\Internal;
use \glFusion\Database\Database;


/**
 * Class to abstact comment searching.
 * @package glfusion
 */
class CommentCollection extends \glFusion\Collection
{
    /** Tags to add for caching.
     * Userdata included to clear when comments are edited.
     * @var array */
    protected $cache_tags = array('comments', 'userdata');

    /** Flag to indicate "pindent" has been set.
     * If not set, "0 as pindent" will be added during execute() to ensure
     * a field value is present.
     * @var boolean */
    private $_pindent_set = false;

    private $Comments = array();

    /**
     * Set up the common query elements for comments.
     */
    public function __construct()
    {
        global $_TABLES;

        parent::__construct();
        $this->_qb->select(
            'c.*', 'u.username', 'u.fullname', 'u.photo', 'u.email',
            'UNIX_TIMESTAMP(c.date) AS nice_date'
        )
                 ->from($_TABLES['comments'], 'c')
                 ->leftJoin('c', $_TABLES['users'], 'u', 'u.uid = c.uid');
    }


    /**
     * For nested layouts, get the comments sorted by left/right.
     *
     * @param   string  $mode   Mode (nested, flat)
     * @return  object  $this;
     */
    public function withMode(string $mode) : self
    {
        global $_TABLES;

        $this->_qb->addSelect($this->_db->conn->quote($mode) . ' AS mode');
        if ($mode == 'nested') {
            $this->_qb->addSelect('c2.indent AS pindent');
            $this->_qb->join('c', $_TABLES['comments'], 'c2')
                      ->andWhere('(c.lft >= c2.lft AND c.lft <= c2.rht)');
            // Flag that pindent is set so it won't get added again during execute()
            $this->_pindent_set = true;
        } 
        return $this;
    }


    /**
     * Limit to queued or nonqueued comments.
     *
     * @param   boolean $queued     True for queued, false for not.
     * @return  object  $this
     */
    public function withQueued(bool $queued=true) : self
    {
        $queued = $queued ? 1 : 0;
        $this->_qb->andWhere('queued = :queued')
                  ->setParameter('queued', $queued, Database::INTEGER);
        return $this;
    }


    /**
     * Set a field to group results.
     *
     * @param   string  $var    DB Field name
     * @return  object  $this
     */
    public function withGroupBy(string $var) : self
    {
        $this->_qb->addGroupBy($var);
        return $this;
    }


    /**
     * Set the item ID to limit results.
     *
     * @param   string  $sid    Item ID
     * @return  object  $this
     */
    public function withSid(string $sid) : self
    {
        $this->_qb->andWhere('c.sid = :sid')
                  ->setParameter('sid', $sid);
        return $this;
    }


    /**
     * Set the item type, e.g. "article" or plugin name.
     *
     * @param   string  $type   Item type
     * @return  object  $this
     */
    public function withType(string $type) : self
    {
        $this->_qb->andWhere('c.type = :type')
                  ->setParameter('type', $type, Database::STRING);
        return $this;
    }


    /**
     * Set the field or value for indentation.
     *
     * @param   string  $val    Field name or value
     * @return  object  $this
     */
    public function withPindent(bool $val=true) : self
    {
        $this->_qb->addSelect($this->_db->conn->quote($val) . ' AS pindent');
        return $this;
    }


    /**
     * Push a single comment into the collection.
     *
     * @param   object  $Comment    Comment object
     * @return  object  $this
     */
    public function pushComment(Comment $C) : self
    {
        $this->Comments[] = $C;
        return $this;
    }


    /**
     * Get an array of Comment objects.
     *
     * @return  array   Array of Result objects
     */
    public function getObjects() : array
    {
        $this->Comments = $this->tryCache();
        if (is_array($this->Comments)) {
            return $this->Comments;
        }

        $this->Comments = array();
        if ($this->_stmt) {
            while ($row = $this->_stmt->fetchAssociative()) {
                $this->Comments[] = Comment::fromArray($row);
            }
            // only cache a good DB response
            $this->setCache($this->Comments, $this->cache_tags);
        }
        return $this->Comments;
    }


    /**
     * Returns the raw Comments array.
     *
     * @return  array   Array of comment records.
     */
    public function getComments() : array
    {
        return $this->Comments;
    }


    /**
     * Wrapper to execute the query.
     * Adds a selection value for 'pindent' if not already added.
     *
     * @return  object      Query result statement
     */
    public function execute() : object
    {
        if (!$this->_pindent_set) {
            $this->_qb->addSelect('0 AS pindent');
        }
        return parent::execute();
    }

}

