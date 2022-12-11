<?php
/**
 * Abstract class to represent a comment engine.
 * Instantiate by calling CommentEngine::getEngine() which will return
 * an instance of the configured comment engine.
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
namespace glFusion\Comments;
use glFusion\Database\Database;


/**
 * This class selects the configured comment engine class.
 * Most of the properties are only used by the Internal engine but are
 * declared here as they will be populated by calling routines regardless
 * of which engine is used.
 *
 * @package glfusion
 */
abstract class CommentEngine
{
    /** Comment Code definitions */
    public const ENABLED = 0;
    public const DISABLED = -1;
    public const CLOSED = 1;

    /** Valid comment view modes.
     * @var array */
    protected static $valid_modes = array('nested', 'flat', 'nocomment');

    /** Item (story) ID.
     * @var string */
    protected $sid = '';

    /** Item title.
     * @var string */
    protected $title = '';

    /** Item type, e.g. 'article' or plugin name.
     * @var string */
    protected $type = 'article';

    /** Sort order.
     * @var string */
    protected $order = 'ASC';

    /** Comment display mode (nested, threaded, flat, etc.)
     * @var string */
    protected $mode = '';

    /** Parent comment ID of this one.
     * @var integer */
    protected $pid = 0;

    /** Page number being displayed.
     * @var integer */
    protected $page = 1;

    /** Flag to use PID as the CID
     * @var boolean */
    protected $cid = false;

    /** Flag to indicate the delete option should be shown.
     * Uses plugin_ismoderator_ function if a value is not set.
     * @var boolean */
    protected $delete_option = NULL;

    /** Comment Code (-1=no comments, 0=allowed, 1=closed)
     * @var integer */
    protected $ccode = self::ENABLED;

    /** Item (story) author ID, used for highlighting the author.
     * @var integer */
    protected $sid_author_id = 0;

    /** Flag indicating preview mode.
     * @var boolean */
    protected $preview = false;



    /**
     * Set reasonable defaults not set statically above.
     */
    public function __construct()
    {
        // Call with empty parameter to set configured mode.
        $this->withMode();
    }


    /**
     * Set the item (story) ID.
     *
     * @param   string  $sid    Item ID
     * @return  object  $this
     */
    public function withSid(string $sid) : self
    {
        $this->sid = $sid;
        return $this;
    }


    /**
     * Set the item title to be the default comment title.
     *
     * @param   string  $title  Item title
     * @return  object  $this
     */
    public function withTitle(string $title) : self
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Set the item type, e.g. article or plugin name.
     *
     * @param   string  $type   Item type
     * @return  object  $this
     */
    public function withType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }


    /**
     * Set the sort order, either ASC or DESC.
     *
     * @param   string  $order  Sort order
     * @return  object  $this
     */
    public function withOrder(string $order) : self
    {
        $this->order = strtoupper($order);
        if ($this->order != 'ASC') $this->order = 'DESC';
        return $this;
    }


    /**
     * Set the comment view mode, e.g. 'nested', 'flat', etc.
     * Uses 'nested' by default.
     *
     * @param   string  $mode   Comment mode
     * @return  object  $this
     */
    public function withMode(?string $mode=NULL) : self
    {
        global $_CONF;

        if (empty($mode)) {
            $mode = $_CONF['comment_mode'];
        }
        if (!in_array($mode, self::$valid_modes)) {
            $mode = 'nested';
        }
        $this->mode = $mode;
        return $this;
    }


    /**
     * Set the parent ID.
     *
     * @param   integer $pid    Parent ID
     * @return  object  $this
     */
    public function withPid(int $pid) : self
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * Set the page number to display.
     *
     * @param   integer $page   Page number
     * @return  object  $this
     */
    public function withPage(int $page) : self
    {
        $this->page = $page;
        return $this;
    }


    /**
     * Set the specific comment ID.
     *
     * @param   integer $cid    Comment record ID
     * @return  object  $this
     */
    public function withCid(bool $cid=true) : self
    {
        $this->cid = $cid;
        return $this;
    }


    /**
     * Set the delete option flag.
     *
     * @param   boolean $delete_option  True if comment deletion allowed
     * @return  object  $this
     */
    public function withDeleteOption(bool $delete_option=true) : self
    {
        $this->delete_option = $delete_option;
        return $this;
    }


    /**
     * Set the comment code (enabled, disabled, closed, etc.
     *
     * @param   boolean $preview    True if previewing a comment
     * @return  object  $this
     */
    public function withCommentCode(int $ccode) : self
    {
        $this->ccode = $ccode;
        return $this;
    }


    /**
     * Set the story author ID.
     *
     * @param   integer $sid_author_id  Story author's user ID
     * @return  object  $this
     */
    public function withSidAuthorId(int $sid_author_id) : self
    {
        $this->sid_author_id = $sid_author_id;
        return $this;
    }


    /**
     * Set the preview flag.
     *
     * @param   boolean $preview    True if previewing a comment
     * @return  object  $this
     */
    public function withPreview(bool $preview=true) : self
    {
        $this->$preview = $preview;
        return $this;
    }


    /**
     * Return an instance of the configured comment engine.
     *
     * @return  object      Comment Engine instance
     */
    public static function getEngine() : object
    {
        global $_CONF;

        if (!isset($_CONF['comment_engine'])) {
            $_CONF['comment_engine'] = 'internal';
        }

        switch ($_CONF['comment_engine']) {
        case 'disqus':
            return new Disqus\Engine;
        case 'facebook':
            return new Facebook\Engine;
        case 'internal':
        default:
            return new Internal\Engine;
        }
    }


    /**
     * Helper function to see if the user must log in.
     *
     * @return  boolean     True if login needed, False if not or logged in
     */
    public static function loginRequired() : bool
    {
        global $_CONF;

        return (
            (($_CONF['loginrequired'] == 1) || ($_CONF['commentsloginrequired'] == 1)) &&
            COM_isAnonUser()
        );
    }


    /**
     * Get the URL to the page hosting the comments.
     *
     * @return  string      Page URL
     */
    public function getPageUrl() : string
    {
        global $_CONF;

        if( $this->type == 'article' ) {
            $url = COM_buildUrl($_CONF['site_url'] . '/article.php?story=' . $this->sid);
        } else {
            // Link to plugin defined link or lacking that a generic link
            // that the plugin should support (hopefully)
            $parts = PLG_getCommentUrlId($this->type);
            if (is_array($parts)) {
                $url = COM_buildUrl("{$parts[0]}?{$parts[1]}={$this->sid}");
            }
        }
        return $url;
    }


    /**
     * Get a page ID for the item.
     * Normally just "type_sid".
     *
     * @return  string      Page ID
     */
    public function getPageId() : string
    {
        return $this->type . '_' . $this->sid;
    }


    /**
     * Get the code needed in the integrated_comments theme field.
     *
     * @return  string      API code.
     */
    public function getApiCode() : string
    {
        return '';
    }


    /**
     * Get the count of comments submitted by user ID.
     *
     * @param   integer $uid        User ID
     * @return  integer     Comment count
     */
    public function getCountByUser(int $uid) : int
    {
        return 0;
    }


    /**
     * Get the count of comments for a specific item.
     *
     * @param   string  $type       Item type, e.g. story or plugin name
     * @param   string  $sid        Item ID
     * @param   integer $queued     1 for queued items, 0 for approved
     * @return  integer     Comment count
     */
    public function getCountByItem(string $type, string $sid, int $queued=0) : int
    {
        return 0;
    }


    /**
     * Get the latest comments from a given user.
     *
     * @param   integer $uid        User ID
     * @param   integer $limit      Optional limit
     * @return  array       Array of Comment objects
     */
    public function getLastX(int $uid, ?int $limit=NULL) : array
    {
        return array();
    }


    abstract public function getLinkWithCount(string $type, string $sid, string $url, ?int $cmtCount = NULL);
}

