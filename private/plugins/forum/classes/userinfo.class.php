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


    private $suspend_expires = 0;
    private $ban_expires = 0;
    private $moderate_expires = 0;

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
     * Read a single badge record into an instantiated object.
     *
     * @param   integer $fb_id  Badge record ID
     * @return  boolean     True on success, False on error or not found
     */
    public function Read(int $uid) : bool
    {
        global $_TABLES;

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


    public static function getInstance(int $uid) : self
    {
        static $cache = array();

        $uid = (int)$uid;
        if (!array_key_exists($uid, $cache)) {
            $cache[$uid] = new self($uid);
        }
        return $cache[$uid];
    }


    public function getBanExpiration() : int
    {
        return 0;
    }

    public function getModerationExpiration() : int
    {
        return 0;
    }

    public function getSuspensionExpiration() : int
    {
        return 0;
    }


    /**
     * Check if a timestamp value is active (not expired).
     * A value of (-1) indicates permanently active.
     *
     * @param   integer $ts     Timestamp to check
     * @return  boolean     True if not expired, False otherwise
     */
    private static function _isActive($ts) : bool
    {
        if ($ts == -1 || $ts > time()) {
            // Never expires, or expires in the future
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
        return self::_isActive($this->ban_expires);
    }


    /**
     * Check if this user's posts are to be moderated.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if moderated, False if not
     */
    public function isModerated() : bool
    {
        return self::_isActive($this->moderate_expires);
    }


    /**
     * Check if this user's posting privilege is suspended.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if suspended, False if not
     */
    public function isSuspended() : bool
    {
        return self::_isActive($this->suspend_expires);
    }


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
     * Delete a single badge record. Does not delete the image.
     *
     * @param   integer $uid    User ID
     */
    public static function Delete(int $uid) : void
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_userinfo'], 'uid', (int)$uid);
    }


    public function getAdminProfileBlock()
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
