<?php
/**
 * Class to handle forum topics.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2017-2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forum;


class Topic
{
    /** Topic record ID.
     * @var integer */
    private $id = 0;

    /** Forum ID.
     * @var integer */
    private $forum = 0;

    /** Parent topic ID, if this is a reply.
     * @var integer */
    private $pid = 0;

    /** Author's user ID.
     * @var integer */
    private $uid = 0;

    /** Poster's user name, as entered for anonymous posters.
     * @var string */
    private $name = '';

    /** Timestamp of submission.
     * @var integer */
    private $date = 0;

    /** Timestamp when last updated.
     * @var integer */
    private $lastupdated = 0;

    /** Timestamp when last edited.
     * @var integer */
    private $lastedited = 0;

    /** Record ID of the last reply.
     * @var integer */
    private $last_reply_rec = 0;

    /** Email address of the submitter.
     * @var string */
    private $email = '';

    /** Submitter's website.
     * @var string */
    private $website = '';

    /** Topic subject, not used except for top topic.
     * @var string */
    private $subject = '';

    /** Text of the post.
     * @var string */
    private $comment = '';

    /** Postmode (html or text).
     * @var string */
    private $postmode = 'text';

    /** Number of replies, if a top topic.
     * @var integer */
    private $replies = 0;

    /** Number of views, if a top topic.
     * @var integer */
    private $views = 0;

    /** Number of attachments.
     * @var integer */
    private $attachments = 0;

    /** IP address of the submitter.
     * @var string */
    private $ip = '';

    /** Mood icon value.
     * @var string */
    private $mood = '';

    /** Flag indicating a sticky post.
     * @var boolean */
    private $sticky = 0;

    /** Flag indicating post was moved.
     * @var boolean */
    private $moved = 0;

    /** Flag indicating a locked post.
     * @var boolean */
    private $locked = 0;

    /** Bitwise-OR value of status checkboxes.
     * @var integer */
    private $status = 0;

    /** Record ID of a post prefix string, top topic only.
     * @var integer */
    private $prefix = 0;

    /** Approval status flag.
     * @var boolean */
    private $approved = 1;

    private static $onetwo = 1; // used for css selection in topic display
    private $_page = 1;         // display page
    private $_is_readonly = 0;  // flag set from forum
    private $_query = '';       // query string
    private $_mode = '';        // viewing mode

    private static $cache = array();

    const DISABLE_BBCODE    = 1;
    const DISABLE_SMILIES   = 2;
    const DISABLE_URLPARSE  = 4;

    const VIEW_PREVIEW = 1;

    /**
     * Constructor.
     * Sets the field values from the supplied array, or reads the record
     * if $A is a badge ID.
     *
     * @param   mixed   $A  Array of properties or group ID
     */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        } else {
            $A = (int)$A;
            $this->Read($A);
        }
    }


    /**
     * Get an instance of a topic by its record ID.
     *
     * @param   integer $id     Topic ID
     * @return  object  Topic object
     */
    public static function getInstance(int $id) : self
    {
        if (!array_key_exists($id, self::$cache)) {
            self::$cache[$id] = new self($id);
        }
        return self::$cache[$id];
    }


    /**
     * Read a single badge record into an instantiated object.
     *
     * @param   integer $id Topic record ID
     * @return  boolean     True on success, False on error or not found
     */
    public function Read($id)
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['ff_topic']}
                WHERE id=" . (int)$id;
        //echo $sql;die;
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            $this->setVars($A);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Set all property values from DB or form.
     *
     * @param  array   $A          Array of property name=>value
     * @param  boolean $from_db    True of coming from the DB, False for form
     */
    public function setVars($A, $from_db = true)
    {
        global $_FF_CONF, $_TABLES, $_CONF;

        foreach ($A as $key=>$value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }


    /**
     * Get all child topics under a given parent topic ID.
     *
     * @param   integer $pid    Parent topic ID
     * @param   integer $offset Starting increment, a multiple of $num
     * @param   integer $num    Number of topics to get in this set
     * @return  array       Array of Topic objects
     */
    public static function getAll($pid, $offset = 0, $num = 0)
    {
        global $_TABLES;

        $cache = array();
        $id = (int)$pid;
        $offset = (int)$offset;
        $num = (int)$num;
        $sql = "SELECT * FROM {$_TABLES['ff_topic']}
                WHERE id = $pid OR pid = $pid
                ORDER BY id ASC";
        if ($num > 0 && $offset >= 0) {
            $sql .= " LIMIT $offset, $num";
        }
        //echo $sql;die;
        $res = DB_query($sql);
        if ($res) {
            while ($A = DB_fetchArray($res, false)) {
                $cache[$A['id']] = new self($A);
            }
        }
        return $cache;
    }


    /**
     * Delete a single toic record.
     *
     * @param   integer $id     Topic ID to delete
     */
    public function Delete($id) : void
    {
        global $_TABLES;

        self::getInstance($this->getPID())->updateReplies(-1);
        DB_delete($_TABLES['ff_topic'], 'id', (int)$this->id);
    }


    public function updateHits() : self
    {
        global $_TABLES;

        self::executeSQL(
            "UPDATE {$_TABLES['ff_topic']}
            SET views = views+1
            WHERE id= {$this->id}"
        );
        return $this;
    }


    public function updateReplies(?int $count=1) : self
    {
        global $_TABLES;
        
        if ($count == NULL) {
            $count  = 1;
        }
        $count = (int)$count;
        self::executeSQL(
            "UPDATE {$_TABLES['ff_topic']}
            SET replies = replies + $count
            WHERE id = {$this->id}"
        );
        return $this;
    }


    public function logRead()
    {
        global $_TABLES, $_USER;

        if (COM_isAnonUser()) return;
        $uid = (int)$_USER['uid'];
        $sql = "INSERT INTO {$_TABLES['ff_log']} SET
                    `uid` = $uid,
                    `forum` = {$this->forum},
                    `topic` = {$this->id},
                    `time` = UNIX_TIMESTAMP()
                ON DUPLICATE KEY UPDATE
                    `time` = UNIX_TIMESTAMP()";
        DB_query($sql);
    }


    public function withPage(int $page) : self
    {
        $this->_page = (int)$page;
        return $this;
    }


    public function withTemplate(object $T) : self
    {
        $this->_template = $T;
        return $this;
    }


    public function withQuery(string $query) : self
    {
        $this->_query = $query;
        return $this;
    }


    public function withMode($mode) : self
    {
        $this->_mode = $mode;
        return $this;
    }


    public function withReadonly(bool $flag) : self
    {
        $this->_is_readonly = $flag ? true : false;
        return $this;
    }


    public function bbcodeDisabled()
    {
        global $_FF_CONF;

        if ($_FF_CONF['bbcode_disabled']) {
            return false;
        } else {
            return $this->status & self::DISABLE_BBCODE != 0;
        }
    }


    public function smiliesDisabled()
    {
        global $_FF_CONF;

        if ($_FF_CONF['smilies_disabled']) {
            return false;
        } else {
            return $this->status & self::DISABLE_SMILIES != 0;
        }
    }


    public function urlparseDisabled()
    {
        global $_FF_CONF;

        if ($_FF_CONF['urlparse_disabled']) {
            return false;
        } else {
            return $this->status & self::DISABLE_URLPARSE != 0;
        }
    }


    public function getID() : int
    {
        return (int)$this->id;
    }

    public function getForum() : int
    {
        return (int)$this->forum;
    }

    public function getPID() : int
    {
        return (int)$this->pid;
    }


    public function getUid() : int
    {
        return (int)$this->uid;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDate() : int
    {
        return (int)$this->date;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getComment() : string
    {
        return $this->comment;
    }


    public static function executeSQL(string $sql) : int
    {
        global $_TABLES;

        DB_query($sql, 1);
        return DB_error();
    }


    // view a topic
        /*$Topic = new Forum\Topic($topicRec);
        $Topic->withPage($page)
              ->withReadonly($topicRec['is_readonly'])
              ->withQuery($highlight)
              ->Render($topicTemplate, $mode);*/

}

