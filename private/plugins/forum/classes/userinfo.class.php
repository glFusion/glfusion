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
        return false;
        return self::_isActive($this->suspension_expires);
    }


    /**
     * Check if this user's posts are to be moderated.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if moderated, False if not
     */
    public function isModerated() : bool
    {
        return false;
        return self::_isActive($this->moderation_expires);
    }


    /**
     * Check if this user's posting privilege is suspended.
     *
     * @uses    self::_isActive()
     * @return  boolean     True if suspended, False if not
     */
    public function isSuspended() : bool
    {
        return false;
        return self::_isActive($this->suspension_expires);
    }


    public function getForumStatus()
    {
        $percent = Modules\Warning\Warning::getUserPercent($this->uid);
        $WL = Modules\Warning\WarningLevel::getByPercent($percent);
        return $WL->getAction();

        if ($this->isBanned()) {
            $retval = Status::BAN;
        } elseif ($this->isSuspended()) {
            $retval = Status::SUSPEND;
        } elseif ($this->isModerated()) {
            $retval = Status::MODERATE;
        } else {
            $retval = Status::NONE;
        }
        return $retval;
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

}
