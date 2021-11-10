<?php
/**
 * Class to handle forum user information.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2017 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forum;

class UserInfo
{
    /** User ID.
     * @var integer */
    private $uid = 0;

    /** User Rating.
     * @var integer */
    private $rating = 0;

    /** User Location.
     * @var string */
    private $location = '';

    /** AIM account.
     * @var string */
    private $aim = '';

    /** ICQ handle.
     * @var string */
    private $icq = '';

    /** Yahoo Instant Messenger ID.
     * @var string */
    private $yim = '';

    /** Microsoft Network Messenger ID.
     * @var string */
    private $msnm = '';

    /** Interests, bio, etc.
     * @var string */
    private $interests = '';

    /** Occupation.
     * @var string */
    private $occupation = '';

    /** Forum Signature.
     * @var string */
    private $signature = '';

    /** Cache of UserInfo objects.
     * @var array() */
    private static $cache = array();

    /** Timestamp when the posting suspension expires.
     * @var integer */
    private $suspend_expires = 0;

    /** Timestamp when the forum ban expires.
     * @var integer */
    private $ban_expires = 0;

    /** Timestamp when the post moderation requirement expires.
     * @var integer */
    private $moderate_expires = 0;

    /** The status text to be shown to the user if banned, suspended, etc.
     * @var string */
    private $_user_status_msg = '';

    /** Flag to indicate that the user's IP address is banned.
     * @var boolean */
    private $_is_banned_ip = false;


    /**
     * Constructor.
     * Sets the field values from the supplied array, or reads the record
     * if $A is a user ID.
     *
     * @param   mixed   $A  Array of properties or usr ID
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
     * Read a single user record into the object.
     *
     * @param   integer $uid    User record ID
     * @return  boolean     True on success, False on error or not found
     */
    public function Read(int $uid) : bool
    {
        global $_TABLES;

        $status = DB_count(
            $_TABLES['ff_banned_ip'],
            'host_ip',
            DB_escapeString($_SERVER['REAL_ADDR'])
        );
        if ($status > 0) {
            $this->_is_banned_ip = true;
        }

        $sql = "SELECT * FROM {$_TABLES['ff_userinfo']}
                WHERE uid = " . (int)$uid;
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
     * @param   array   $A          Array of property name=>value
     * @param   boolean $from_db    True of coming from the DB, False for form
     */
    public function setVars($A, $from_db = true)
    {
        foreach ($A as $key=>$value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
        return true;
    }


    /**
     * Get an instance of a user record.
     *
     * @param   integer $uid    User ID
     * @return  object  UserInfo object
     */
    public static function getInstance(int $uid) : self
    {
        static $cache = array();

        $uid = (int)$uid;
        if (!array_key_exists($uid, $cache)) {
            $cache[$uid] = new self($uid);
        }
        return $cache[$uid];
    }


    /**
     * Get the user's record ID.
     *
     * @return  integer     User ID
     */
    public function getUid() : int
    {
        return (int)$this->uid;
    }


    public function getBanExpiration() : int
    {
        return (int)$this->ban_expires;
    }

    public function getModerationExpiration() : int
    {
        return (int)$this->moderate_expires;
    }

    public function getSuspensionExpiration() : int
    {
        return (int)$this->supend_expires;
    }


    /**
     * Check if a timestamp value is active (not expired).
     * A value of (-1) indicates permanently active.
     *
     * @param   integer $ts     Timestamp to check
     * @return  boolean     True if not expired, False otherwise
     */
    private function _isActive($ts) : bool
    {
        global $_CONF;

        if ($ts == -1) {
            // Never expires
            return true;
        } elseif ($ts > time()) {
            // Expires in the future
            $dt = new \Date($ts, $_CONF['timezone']);
            $this->_user_expires_msg = ' until ' . $dt->format($_CONF['date']);
            return true;
        } else {
            // Expired or never set
            return false;
        }
    }


    /**
     * Check if this user is banned from the forum.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if banned, False if not
     */
    public function isBanned() : bool
    {
        global $LANG_GF02;

        $status = $this->_is_banned_ip || $this->_isActive($this->ban_expires);
        if ($status) {
            $this->_user_status_msg = 'Sorry, you have been banned from the forum' .
                $this->_user_expires_msg . '.';
        }
        return $status;
    }


    /**
     * Check if this user's posts are to be moderated.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if moderated, False if not
     */
    public function isModerated() : bool
    {
        return $this->_isActive($this->moderate_expires);
    }


    /**
     * Check if this user's posting privilege is suspended.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if suspended, False if not
     */
    public function isSuspended() : bool
    {
        $status = $this->_isActive($this->suspend_expires);
        if ($status) {
            $this->_user_status_msg = 'Sorry, You have suspended from making entries' .
                $this->_user_expires_msg . '.';
        }
        return $status;
    }


    /**
     * Get the message text to show the user regarding bans and suspensions.
     *
     * @param   boolean $format     True to format the message in an alert
     * @return  string      HTML to show the user
     */
    public function getUserStatusMsg(?bool $format=false) : string
    {
        global $_CONF, $LANG_GF02;

        $display = $this->_user_status_msg;
        if ($format) {
            $display = '<div class="uk-alert uk-alert-danger">' . $display . '<br />' .
                sprintf ($LANG_GF02['msg15'],$_CONF['site_mail']) .
                '</div>';
        }
        return $display;
    }


    /**
     * Returns an array of format status information for the user.
     *
     * @return  array       Array of status info
     */
    public function getForumStatus() : array
    {
        global $_CONF;

        $retval = array(
            'status' => 0,
            'expires' => 0,
            'severity' => '',
            'message' => 'No Restriction',
        );
        if ($this->isBanned()) {
            $status = Status::BAN;
            $exp = $this->ban_expires;
            $retval = array(
                'status' => Status::BAN,
                'expires' => $this->ban_expires,
                'severity' => 'danger',
                'message' => 'User is banned from the forum',
            );
        } elseif ($this->isSuspended()) {
            $status = Status::SUSPEND;
            $exp = $this->suspend_expires;
            $retval = array(
                'status' => Status::SUSPEND,
                'expires' => $this->suspend_expires,
                'severity' => 'warning',
                'message' => 'User\'s posting privilege is suspended',
            );
        } elseif ($this->isModerated()) {
            $status = Status::MODERATE;
            $exp = $this->moderate_expires;
            $retval = array(
                'status' => Status::MODERATE,
                'expires' => $this->moderate_expires,
                'severity' => 'warning',
                'message' => 'User\'s posts must be moderated',
            );
        /*} else {
            $status = Status::NONE;
            $exp = 0;*/
        }
        if ($retval['expires'] > time()) {
            $dt = new \Date($retval['expires'], $_CONF['timezone']);
            $retval['message'] .= ' until ' . $dt->toMySQL(true);
        }

        return $retval;
        /*return array(
            'status' => $status,
            'expires' => $exp,
        );*/
    }


    /**
     * Delete a single user record.
     *
     * @param   integer $uid    User ID
     */
    public static function Delete(int $uid) : void
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_userinfo'], 'uid', (int)$uid);
    }


    /**
     * Get user information to be shown to administrators in the profile block.
     *
     * @return  string      HTML to add to the profile block
     */
    public function getAdminProfileBlock() : string
    {
        global $_CONF;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
        $T->set_file('adm_block', 'user_profile.thtml');
        foreach (array('ban', 'suspend', 'moderate') as $key) {
            $property = $key . '_expires';
            if ($this->$property > 0) {
                $dt = new \Date($this->$property, $_CONF['timezone']);
                $T->set_var($property, $dt->format('Y-m-d H:i'));
            }
        }
        $T->parse('output', 'adm_block');
        return $T->finish($T->get_var('output'));
    }

}
