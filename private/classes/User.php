<?php
/**
 * Class to handle glFusion user-related operations.
 *
 * @author     Lee Garner <lee@leegarner.com>
 * @copyright  Copyright (c) 2017-2022 Lee Garner <lee@leegarner.com>
 * @package    glfusion
 * @version    v2.0.2
 * @license    http://opensource.org/licenses/gpl-2.0.php
 *             GNU Public License v2 or later
 * @filesource
 */
namespace glFusion;
use glFusion\Database\Database;
use glFusion\Log\Log;
use Group;

class User
{
    /** Account is banned/disabled.
     * @const integer */
    public const DISABLED = 0;

    /** Account awaiting user to login.
     * @const integer */
    public const AWAITING_ACTIVATION = 1;

    /** Account awaiting moderator approval.
     * @const integer */
    public const AWAITING_APPROVAL = 2;

    /** Active account.
     * @const integer */
    public const ACTIVE =  3;

    /** Account waiting for user to complete verification.
     * @const integer */
    public const AWAITING_VERIFICATION = 4;

    /** User record ID.
     * @var integer */
    private $uid = 0;

    /** User's login name.
     * @var string */
    private $username = '';

    /** Remote login service user name.
     * @var string */
    private $remoteusername = '';

    /** Remote login service name.
     * @var string */
    private $remoteservice = '';

    /** User's full name.
     * @var string */
    private $fullname = '';

    /** Password (encrypted).
     * @var string */
    private $passwd = '';

    /** Email address.
     * @var string */
    private $email = '';

    /** User's homepage.
     * @var string */
    private $homepage = '';

    /** Signature for forum, etc.
     * @var string */
    private $sig = '';

    /** Registration Date.
     * @var object */
    private $regdate = NULL;

    /** Avatar.
     * @var string */
    private $photo = '';

    /** Cookie timeout (remember me).
     * @var integer */
    private $cookietimeout = 28800;

    /** Site theme.
     * @var string */
    private $theme = 'cms';

    /** Language.
     * @var string */
    private $language = 'english_utf-8';

    /** Password reset request ID.
     * @var string */
    private $pwrequestid = '';

    /** Act token.
     * @var string */
    private $act_token = '';

    /** Act time.
     * @var object */
    private $act_time = NULL;

    /** TFA Enabled?
     * @var boolean */
    private $tfa_enabled = 0;

    /** TFA Secret.
     * @var string */
    private $tfa_secret = '';

    /** Account Status.
     * @var integer */
    private $status = self::AWAITING_ACTIVATION

    /** Account Type (local, remote).
     * @var integer */
    private $account_type = 1;

    /** Number of password reminders sent.
     * @var integer */
    private $num_reminders = 0;

    /** Remote IP Address.
     * @var string */
    private $remote_ip = '';

    /** About information (userinfo).
     * @var string */
    private $about = '';

    /** Location (userinfo).
     * @var string */
    private $location = '';

    /** PGP Key (userinfo).
     * @var string */
    private $pgpkey = '';

    /** User Space (userinfo).
     * @var string */
    private $userspace = '';

    /** Tokens (userinfo).
     * @var string */
    private $tokens = 0;

    /** Last login timestamp.
     * @var integer */
    private $lastlogin = 0;

    /** Total comments submitted (userinfo).
     * @var integer */
    private $totalcomments = 0;

    /** Last granted access (userinfo).
     * @var integer */
    private $lastgranted = 0;

    /** Last login timestamp (userinfo).
     * @var integer */
    private $last_login = 0;

    /** Do not show topic icons? (userprefs).
     * @var boolean */
    private $noicons = 0;

    /** Willing to receive notifications? (userprefs).
     * @var boolean */
    private $willing = 1;

    /** Date Format ID (userprefs).
     * @var integer */
    private $dfid = 0;

    /** Timezone ID (userprefs).
     * @var integer */
    private $tzid = 0;

    /** Email stories? (userprefs).
     * @var boolean */
    private $email_stories = 1;

    /** Email from admin? (userprefs).
     * @var boolean */
    private $emailfromadmin = 1;

    /** Email from users? (userprefs).
     * @var boolean */
    private $emailfromuser = 1;

    /** Show online status? (userprefs).
     * @var boolean */
    private $showonline = 1;

    /** Search results format (userprefs).
     * @var string */
    private $search_result_format = 'google';

    private static $cache = array();

    private static $current = 1;

    private $_rights = NULL;

    private static $_fields = array(
        'uid' => 'int',
        'username' => 'string',
        'remoteusername' => 'string',
        'remoteservice' => 'string',
        'fullname' => 'string',
        'passwd' => 'string',
        'email' => 'string',
        'homepage' => 'string',
        'sig' => 'string',
        'regdate' => 'date',
        'photo' => 'string',
        'cookietimeout' => 'int',
        'theme' => 'string',
        'language' => 'string',
        'pwrequestid' => 'string',
        'act_token' => 'string',
        'act_time' => 'date',
        'tfa_enabled' => 'bool',
        'tfa_secret' => 'string',
        'status' => 'int',
        'account_type' => 'int',
        'num_reminders' => 'int',
        'remote_ip' => 'string',
        'about' => 'string',
        'location' => 'string',
        'pgpkey' => 'string',
        'userspace' => 'string',
        'tokens' => 'int',
        'totalcomments' => 'int',
        'lastlogin' => 'int',
        'lastgranted' => 'int',
        'last_login' => 'int',
        'noicons' => 'bool',
        'willing' => 'bool',
        'dfid' => 'int',
        'tzid' => 'int',
        'emailstories' => 'bool',
        'emailfromadmin' => 'bool',
        'emailfromuser' => 'bool',
        'showonline' => 'bool',
        'search_result_format' => 'string',
    );


    /**
     * Constructor.
     *
     * @param   mixed   $A  Array of properties or group ID
     */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
            $this->isNew = false;
        } elseif (is_numeric($A)) {
            $this->isNew = !$this->Read($A);
        }
    }


    /**
     * Return the username as the string representation of the class.
     *
     * @return  string      Username
     */
    public function __toString()
    {
        return $this->username;
    }


    /**
     * Getter function. Returns property value or NULL if not set.
     *
     * @param   string  $key    Name of property
     * @return  mixed           Value of property
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        } else {
            return NULL;
        }
    }


    public function __set($key, $value) : void
    {
        if (property_exists($this, $key)) {
            switch(self::$_fields[$key]) {
            case 'int':
                $this->$key = (int)$value;
                break;
            case 'bool':
                $this->$key = $value ? 1 : 0;
                break;
            case 'date':
                $this->$key = new \Date($value, $_CONF['timezone']);    // todo
                break;
            default:
                $this->$key = $value;
                break;
            }
        }
    }


    /**
     * Get an instance of a user record.
     *
     * @param   integer $uid    User ID to read
     * @return  boolean         True on success, False on error.
     */
    public function Read(int $uid) : bool
    {
        global $_TABLES;

        $qb = Database::getInstance()->conn->createQueryBuilder();
        /*$sql = "SELECT users.*, userprefs.*, userinfo.*
            FROM {$_TABLES['users']} users
            LEFT JOIN {$_TABLES['userprefs']} userprefs ON users.uid=userprefs.uid
            LEFT JOIN {$_TABLES['userinfo']} userinfo ON users.uid=userinfo.uid
            WHERE users.uid = ?
            LIMIT 1";*/
        $db = Database::getInstance();
        try {
            $userData = $qb->select('users.*', 'userprefs.*', 'userinfo.*')
                           ->from($_TABLES['users'], 'u')
                           ->leftJoin('u', $_TABLES['userprefs'], 'up', 'u.uid=up.uid')
                           ->leftJoin('u', $_TABLES['userinfo'], 'ui', 'u.uid=ui.uid')
                           ->where('u.uid = ?')
                           ->setParameter(0, $uid, Database::INTEGER)
                           ->execute()
                           ->fetch(Database::ASSOCIATIVE);
        } catch (\Throwable $e) {
            self::errorLog(__FUNCTION__, $e->getMessage());
            $userData = false;
        }
        if (is_array($userData) && !empty($userData)) {
            $this->setVars($userData);
            return true;
        }
        return false;
    }


    /**
     * Get all users in the table.
     *
     * @param   integer $status     User status, default = active
     * @return  array   Array of user records
     */
    public static function getAll(int $status = self::ACTIVE) : array
    {
        global $_TABLES;

        $qb = Database::getInstance()->conn->createQueryBuilder();
        $qb->select('u.*', 'up.*', 'ui.*')
            ->from($_TABLES['users'], 'u')
            ->leftJoin('u', $_TABLES['userprefs'], 'up', 'u.uid=up.uid')
            ->leftJoin('u', $_TABLES['userinfo'], 'ui', 'u.uid=ui.uid');
        if ($status > 0) {
            $qb->where('users.status = ?')
               ->setParameter(0, $status, Database::INTEGER);
        }
        try {
            $stmt = $qb->execute();
            $userData = $stmt->fetchAll(Database::ASSOCIATIVE);
        } catch (Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage();
            $userData = array();
        }
        return $userData;
    }


    /**
     * Set the current user's user ID.
     *
     * @param   integer $uid        User ID
     */
    public static function setCurrent($uid)
    {
        self::$current = (int)$uid;
    }


    /**
     * Get the current user.
     *
     * @return  object      User object
     */
    public static function getCurrent() : self
    {
        return self::getInstance();
    }


    /**
     * Check if this is an anonymous user. Similar to COM_isAnonUser().
     *
     * @return  boolean     True if anonymous
     */
    public function isAnon() : bool
    {
        return $this->uid == 1;
    }


    /**
     * Get a user object. Leverages caching.
     *
     * @param   integer $uid    User ID
     * @return  object          User object
     */
    public static function getInstance(?int $uid = NULL) : self
    {
        $uid = (int)$uid;
        if ($uid == 0) {
            if (self::$current > 0) {
                $uid = self::$current;
            } else {
                return NULL;
            }
        }
        if (!isset(self::$cache[$uid])) {
            $key = 'user_' . $uid;
            if (Cache::getInstance()->has($key)) {
                self::$cache[$uid] = Cache::getInstance()->get($key);
            } else {
                self::$cache[$uid] = new self($uid);
                Cache::getInstance()->set($key, self::$cache[$uid], 'users');
            }
        }
        return self::$cache[$uid];
    }


    /**
     * Set all property values from DB or form.
     *
     * @param  array   $A          Array of property name=>value
     * @param  boolean $from_db    True of coming from the DB, False for form
     */
    public function setVars(array $A, bool $from_db = true) : self
    {
        global $_CONF;

        foreach ($A as $key=>$value) {
            if (array_key_exists($key, self::$_fields)) {
                switch(self::$_fields[$key]) {
                case 'int':
                    $this->$key = (int)$value;
                    break;
                case 'bool':
                    $this->$key = $value ? 1 : 0;
                    break;
                case 'date':
                    $this->$key = new \Date($value, $_CONF['timezone']);    // todo
                    break;
                default:
                    $this->$key = $value;
                    break;
                }
            }
        }
        return $this;
    }


    /**
     * Checks if current user has rights to one or more features.
     *
     * Takes either a single feature, comma-separated string of features,
     * or an array of features and returns True if the current user has access.
     *
     * @param   string|array    $features       Array or CSV string of features to check
     * @param   string          $operator       "AND" or "OR" when checking multiple features
     * @return  boolean     Return true if current user has access to feature(s), otherwise false.
     */
    public function hasRights($features, $operator='AND')
    {
        global $_USER, $_RIGHTS, $_SEC_VERBOSE;

        // Root has access to everything
        if (Group::inGroup('Root', $this->uid)) return true;

        if ($this->isAnon()) {
            return false;     // invalid current user id
        }
        if (is_string($features)) {
            $features = explode(',',$features);
        }
        $operator = strtoupper($operator);
        $rights = Feature::getByUser($this->uid);

        // check all values passed
        if ($operator == 'OR') {
            foreach ($features as $feature) {
                // OR operator, return as soon as we find a true one
                if (in_array($feature, $rights)) {
                    if ($_SEC_VERBOSE) {
                        self::_accessLog('has access to ' . $feature);
                    }
                    return true;
                }
            }
            // Got here, user does not have any of the request rights
            if ($_SEC_VERBOSE) {
                self::accessLog('does not have access to ' . $feature);
            }
            return false;
        } else {
            foreach ($features as $feature) {
                if (!in_array($feature, $rights)) {
                    if ($_SEC_VERBOSE) {
                        self::accessLog('does not have access to ' . $feature);
                    }
                    return false;
                }
            }
            if ($_SEC_VERBOSE) {
                self::accessLog('has access to ' . $feature);
            }
            // Got here, user has all of the requested rights
            return true;
        }
    }


    /**
     * Get the user display name.
     * Checks several optional parameters that can override the values in the DB.
     * If $username is not empty, then only the supplied parameters are used.
     *
     * @param  string  $username       Optional username override
     * @param  string  $fullname       Optional fullname override
     * @param  string  $remoteusername Optional remote username override
     * @param  string  $remoteservice  Optional remote service override
     * @return string                  User's display name
     */
    public function getDisplayName(
        string $username='',
        string $fullname='',
        string $remoteusername='',
        string $remoteservice=''
    ) : string {
        global $_CONF;

        if (empty($username)) {
            $username = $this->username;
            $fullname = $this->fullname;
            $remoteusername = $this->remoteusername;
            $remoteservice = $this->remoteservice;
        }
        $ret = $username;
        if (!empty($fullname) && ($_CONF['show_fullname'] == 1)) {
            $ret = $fullname;
        } else if ($_CONF['user_login_method']['3rdparty'] && !empty($remoteusername)) {
            if (!empty($username)) {
                $remoteusername = $username;
            }

            if ($_CONF['show_servicename']) {
                $ret = "$remoteusername@$remoteservice";
            } else {
                $ret = $remoteusername;
            }
        }
        return $ret;
    }


    /**
     * Get a user's photo, either uploaded or from an external service
     *
     * @param    int     $uid    User ID
     * @param    string  $photo  name of the user's uploaded image
     * @param    string  $email  user's email address (for gravatar.com)
     * @param    int     $width  preferred image width
     * @param    int     $fullURL if true, send full <img> tag, otherwise just the path to image
     * @return   string          <img> tag or empty string if no image available
     */
    public function getPhoto(int $width = 0, int $fullURL = 1) : string
    {
        global $_CONF;

        $userphoto = '';
        if ($this->photo == '(none)') {
            $userphoto = '';
        }

        if ($_CONF['allow_user_photo'] == 1) {
            if (($width == 0) && !empty ($_CONF['force_photo_width'])) {
                $width = $_CONF['force_photo_width'];
            }
        }

        if (empty($photo) || (empty ($email) && $_CONF['use_gravatar'])) {
           if (empty ($photo)) {
                $photo = $this->photo;
            }
            if (empty ($email)) {
                $email = $this->email;
            }
        }

        $img = '';
        if (empty($this->photo)) {
           // no photo - try gravatar.com, if allowed
            if ($_CONF['use_gravatar']) {
                $img = '//www.gravatar.com/avatar/'.md5($this->email);
                $url_parms = array();
                if ($width > 0) {
                    $url_parms[] = 's=' . $width;
                }
                if (!empty($_CONF['gravatar_rating'])) {
                    $url_parms[] = 'r=' . $_CONF['gravatar_rating'];
                }
                if (!empty($_CONF['default_photo'])) {
                    $url_parms[] = 'd=' . urlencode($_CONF['default_photo']);
                }
                if (count($url_parms) > 0) {
                    $img .= '?' . implode('&amp;', $url_parms);
                }
            }
        } else {
            // check if images are inside or outside the document root
            if (strstr($_CONF['path_images'], $_CONF['path_html'])) {
                $imgpath = substr($_CONF['path_images'], strlen($_CONF['path_html']));
                $img = $_CONF['site_url'] . '/' . $imgpath . 'userphotos/' . $photo;
                if (!@file_exists( $_CONF['path_html'] . $imgpath . 'userphotos/'.$photo)) {
                    $img = '';
                }
            } else {
                $img = $_CONF['site_url']
                     . '/getimage.php?mode=userphotos&amp;image=' . $photo;
            }
        }

        if (empty($img)) {
            if (!isset($_CONF['default_photo']) || $_CONF['default_photo'] == '') {
                $img = $_CONF['site_url'] . '/images/userphotos/default.jpg';
            } else {
                $img = $_CONF['default_photo'];
            }
        }

        if ($fullURL && !empty($img)) {
            $img = '<img src="' . $img . '"';
            if ($width > 0) {
                $img .= ' width="' . $width . '"';
            }
            $img .= ' alt="" class="userphoto" />';
        }
        return $img;
    }


    /**
     * Delete a user's photo image file. Does not update the database.
     * Will silently ignore non-existing files.
     *
     * @param    boolean $abortonerror   true: abort script on error, false: don't
     * @return   void
     */
    public function deletePhoto(bool $abortonerror = true) : void
    {
        global $_CONF, $LANG04;

        if (!empty($this->photo)) {
            $filetodelete = $_CONF['path_images'] . 'userphotos/' . $this->photo;
            if (file_exists($filetodelete)) {
                if (!@unlink($filetodelete)) {
                    if ($abortonerror) {
                        $display = COM_siteHeader ('menu', $LANG04[21])
                                 . COM_errorLog ("Unable to remove file {$this->photo}")
                                 . COM_siteFooter ();
                        echo $display;
                        exit;
                    } else {
                        // just log the problem, but don't abort
                        self::accessLog("Unable to remove file {$this->photo}");
                    }
                } else {
                    $this->photo = '';
                }
            }
        }
    }


    /**
     * Delete a user account.
     *
     * @return   boolean   true = user deleted, false = an error occured
     */
    public function Delete() : bool
    {
        global $_CONF, $_TABLES;

        if ($this->uid == 1) {
            self::accessLog("just tried to delete the Anonymous user.");
            return false;
        }
        $uid = $this->uid;

        // first some checks ...
        if ($uid == self::Current()-uid) {
            if ($_CONF['allow_account_delete'] == 0 && !$this->isAdmin()) {
                // you can only delete your own account (if enabled) or you need
                // proper permissions to do so (user.delete)
                self::accessLog("just tried to delete their account with insufficient privileges.");
                return false;
            }
        } else {
            if (!$this->isAdmin()) {
                self::accessLog("just tried to delete user $uid with insufficient privileges.");
                return false;
            }
        }

        if (Group::inGroup(1, $uid)) {
            if (!Group::inGroup (1)) {
                // can't delete a Root user without being in the Root group
                self::accessLog("just tried to delete Root user $uid with insufficient privileges.");
                return false;
            } else {
                // ok to delete the root user as long as there will be at
                // least one other root user left
                $rootusers = Group::getMembers(1);
                if (count($rootusers) < 2) {
                    self::accessLog('can\'t delete the last user from the Root group.', 1);
                    return false;
                }
            }
        }

        $db = Database::getInstance();

        // log the user out
        SESS_endUserSession ($uid);

        // Ok, now delete everything related to this user

        // let plugins update their data for this user
        PLG_deleteUser ($uid);

        if ( function_exists('CUSTOM_userDeleteHook')) {
            CUSTOM_userDeleteHook($uid);
        }

        // Call custom account profile delete function if enabled and exists
        if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userDelete')) {
            CUSTOM_userDelete ($uid);
        }

        // common SQL params and types for this operation
        $uid_parms = array('uid' => $uid);
        $uid_types = array(Database::INTEGER);

        // remove from all security groups
        $db->conn->delete(
            $_TABLES['group_assignments'],
            array('ug_uid' => $uid),
            $uid_types
        );

        // remove user information and preferences
        $db->conn->delete($_TABLES['userprefs'], $uid_parms, $uid_types);
        $db->conn->delete($_TABLES['userindex'], $uid_parms, $uid_types);
        $db->conn->delete($_TABLES['usercomment'], $uid_parms, $uid_types);
        $db->conn->delete($_TABLES['userinfo'], $uid_parms, $uid_types);
        $db->conn->delete($_TABLES['social_follow_user'], $uid_parms, $uid_types);

        // avoid having orphaned stories/comments by making them anonymous posts
        $db->conn->update(
            $_TABLES['comments'],
            array('uid' => 1),
            array('uid' => $this->uid),
            array(Database::INTEGER,Database::INTEGER)
        );

        $db->conn->update(
            $_TABLES['comments'],
            array('uid' => 1),
            array('uid' => $this->uid),
            array(Database::INTEGER,Database::INTEGER)
        );
        $db->conn->update(
            $_TABLES['stories'],
            array('uid' => 1),
            array('uid' => $this->uid),
            array(Database::INTEGER,Database::INTEGER)
        );
        $db->conn->update(
            $_TABLES['stories'],
            array('uid' => 1),
            array('uid' => $this->uid),
            array(Database::INTEGER,Database::INTEGER)
        );

        // delete story submissions
        $db->conn->delete($_TABLES['storysubmission'], $uid_parms, $uid_types);

        // delete user photo, if enabled & exists
        $this->deletePhoto();

        // delete subscriptions
        $db->conn->delete($_TABLES['subscriptions'], $uid_parms, $uid_types);

        // in case the user owned any objects that require Admin access, assign
        // them to the Root user with the lowest uid.
        // Obviously avoid the current user being deleted
        $newrootuser = '';
        foreach ($rootusers as $u) {
            if ($u != $uid) {
                $newrootuser = $u;
                break;
            }
        }
        if ($newrootuser == '' || $newrootuser < 2 ) {
            // This shouldn't happen since we already checked there's at least
            // one more root user.
            $newrootuser = 2;
        }
        $db->conn->update(
            $_TABLES['blocks'],
            array('owner_id' => $newrootuser),
            array('owner_id' => $uid),
            array(Database::INTEGER,Database::INTEGER)
        );

        // now delete the user itself
        $db->conn->delete($_TABLES['users'], $uid_parms, $uid_types);
        self::accessLog("deleted user $uid");
        $this->uid = 0;
        return true;
    }


    /**
     * Shortcut function to check if this is a user administrator.
     *
     * @return  boolean     True for an admin, False if not
     */
    public function isAdmin()
    {
        static $isAdmin = NULL;
        if ($isAdmin === NULL) {
            $isAdmin = $this->hasRights(array('user.edit','user.delete', 'OR'));
        }
        return $isAdmin;
    }


    /**
     * Log a message to the access log with the current user ID included.
     *
     * @param   string  $msg    Message to log
     */
    private static function accessLog(string $msg) : void
    {
        COM_accessLog('User ' . self::getInstance()->getUid() . ' ' . $msg, 1);
    }


    /**
     * Write an error log message, including the class and function name.
     *
     * @param   string  $fn     Function where the error occurred
     * @param   string  $msg    Message to log
     */
    private static function errorLog(string $fn, string $msg) : void
    {
        Log::write('system', Log::ERROR, __CLASS__ . '::' . __FUNCTION__ . ': ' . $msg);
    } 

}

