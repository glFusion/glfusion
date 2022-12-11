<?php
/**
 * Base class to send notifications to site members.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @since       v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion;
use glFusion\Log\Log;


/**
 * Base notification class.
 * @package shop
 */
abstract class Notifier
{
    // Flag to store only one message per uid/pi_code combination.
    const UNIQUE = 1;

    // Flag to override existing message vs. leave it alone, if unique is set.
    const OVERWRITE = 2;

    /** Registered notification providers.
     * @var array */
    private static $_providers = array();

    /** Array of user IDs for the To list.
     * @var array */
    protected $recipients = array();

    /** Array of BCC user IDs.
     * @var array */
    protected $bcc = array();

    /** Sending user's user ID. Default is current user.
     * @var integer */
    protected $from_uid = 0;

    /** Sending user's name. Default is current user.
     * @var integer */
    protected $from_name = '';

    /** Message subject text.
     * @var string */
    protected $subject = '';

    /** Message HTML body text.
     * @var string */
    protected $htmlmessage = '';

    /** Message Text body.
     * @var string */
    protected $textmessage = '';

    /** Expiration timestamp, if supported.
     * @var integer */
    protected $exp_ts = 2145916799;     // 2037-12-31 23:59:59 UTC

    /** Message level, default "info".
     * @var integer */
    protected $level = 1;

    /** Plugin Name.
     * @var string */
    protected $pi_name = 'glfusion';

    /** Plugin-supplied code.
     * @var string */
    protected $pi_code = '';

    /** Flag for the message to persist or disappear.
     * @var boolean */
    protected $persist = 1;

    /** Session ID, set for anonymous users.
     * @var string */
    protected $sess_id = '';

    /** Flag indicating only one copy of the message should be stored.
     * 0 = not at all, 1 = leave existing message alone, 2 = overwrite
     * @var integer */
    protected $unique = 0;


    /**
     * Set some common defaults.
     */
    public function __construct()
    {
        global $_USER;
        $this->from_uid = $_USER['uid'];
        $this->from_name = empty($_USER['fullname']) ? $_USER['username'] : $_USER['fullname'];
    }


    /**
     * Add a recipient.
     *
     * @param   integer $uid    User ID
     * @param   string  $name   User Name
     * @param   string  $email  Email address
     * @return  object  $this
     */
    public function addRecipient(int $uid, ?string $name=NULL, ?string $email=NULL)
    {
        $this->recipients[] = array(
            'uid' => $uid,
            'name' => $name,
            'email' => $email,
        );
        return $this;
    }


    /**
     * Add a BCC recipient.
     *
     * @param   integer $uid    User ID
     * @param   string  $name   User Name
     * @param   string  $email  Email address
     * @return  object  $this
     */
    public function addBCC(int $uid, ?string $name=NULL, ?string $email=NULL)
    {
        $this->bcc[] = array(
            'uid' => $uid,
            'name' => $name,
            'email' => $email,
        );
        return $this;
    }


    /**
     * Override the sender's user ID.
     * The default is the current user's ID.
     *
     * @param   integer $uid    User ID
     * @return  object  $this
     */
    public function setFromUid(int $uid) : self
    {
        $this->from_uid = (int)$uid;
        return $this;
    }


    /**
     * Set the sending user's name.
     *
     * @param   string  $name   User's name
     * @return  object  $this
     */
    public function setFromName(string $name) : self
    {
        $this->from_name = $name;
        return $this;
    }


    public function setSubject(string $subject) : self
    {
        $this->subject = $subject;
        return $this;
    }


    /**
     * Set the message content.
     *
     * @param   string  $msg    Message content
     * @param   boolean $html   True if this is HTML, False for Text
     * @return  object  $this
     */
    public function setMessage(string $msg, bool $html=false) : self
    {
        if ($html) {
            $this->htmlmessage = $msg;
        } else {
            $this->textmessage = $msg;
        }
        return $this;
    }


    /**
     * Set the expiration timestamp, for methods that support it.
     *
     * @param   integer $ts     Message expiration as a Unix timestamp
     * @return  objec   $this
     */
    public function setExpiration(int $ts) : self
    {
        $this->exp_ts = $ts;
        return $this;
    }


    /**
     * Get the expiration timestamp.
     *
     * return   integer     Expiration timestamp
     */
    public function getExpiration() : int
    {
        return $this->exp_ts;
    }


    /**
     * Set the plugin name.
     * This may be the plugin name or other optional ID.
     *
     * @param   string  $pi_code    Plugin-supplied code
     * @return  object  $this
     */
    public function setPlugin(string $pi_name, ?string $pi_code=NULL) : self
    {
        $this->pi_name = $pi_name;
        $this->pi_code = $pi_code;
        return $this;
    }


    /**
     * Set the flag to determine if the message stays on-screen.
     * Assumes persistence is desired if called without a parameter.
     *
     * @param   boolean $persist    True to persist, False to disappear
     * @return  object  $this
     */
    public function setPersists(bool $persist=true) : self
    {
        $this->persist = $persist ? 1 : 0;
        return $this;
    }


    /**
     * Use the session ID, used for anonymous users.
     *
     * @param   boolean $flag   True to use the session ID
     * @return  object  $this
     */
    public function setSessId(bool $flag=true) : self
    {
        $this->sess_id = $flag ? session_id() : '';
        return $this;
    }


    /**
     * Set the flag indicating whether to store only one copy of this message.
     *
     * @param   boolean $flag   True to store only one, False for multiple
     * @return  object  $this
     */
    public function setUnique(bool $flag=true) : self
    {
        if ($flag) {
            $this->unique |= self::UNIQUE;
        } else {
            $this->unique -= self::UNIQUE;
        }
        return $this;
    }


    /**
     * Set the flag indicating whether to overwrite this message when updated.
     *
     * @param   boolean $flag   True to overwrite, False to leave alone.
     * @return  object  $this
     */
    public function setOverwrite(bool $flag=true) : self
    {
        if ($flag) {
            $this->unique |= self::OVERWRITE;
        } else {
            $this->unique -= self::OVERWRITE;
        }
        return $this;
    }


    /**
     * Set the message level (info, error, etc).
     * Several options can be supplied for the level values.
     *
     * @param   string  $level  Message level.
     * @return  object  $this
     */
    public function setLevel(string $level) : self
    {
        switch ($level) {
        case 'error':
        case 'err':
        case false:
        case 'alert':
        case 4:
        case Log::ERROR:
            $this->level = 4;
            break;
        case 'warn':
        case 'warning':
        case 3:
        case Log::WARNING:
            $this->level = 3;
            break;
        case 'success':
        case 2:
            // No corresponding Log level
            $this->level = 2;
            break;
        case 'info':
        case 1:
        case Log::INFO:
        default:
            $this->level = 1;
            break;
        }
        return $this;
    }


    /**
     * Get the user object.
     *
     * @param   integer $uid    User ID
     * @return  object      User object
     */
    protected function getUser(int $uid) : User
    {
        return User::getInstance($uid);
    }


    /**
     * Delete expired messages for notifiers that support it.
     */
    public static function deleteExpired() : void
    {
        foreach (self::$_providers as $key=>$info) {
            $Provider = self::getProvider($key, false);
            if ($Provider) {
                $Provider->_deleteExpired();
            }
        }
    }


    /**
     * Stub function for notifiers that don't support expiration handling.
     */
    protected function _deleteExpired() : void
    {
    }


    /**
     * Register a notification method.
     *
     * @param   string  $key    Short description, e.g. plugin name
     * @param   string  $cls    Class name, e.g. PM\Notifier
     * @param   string  $dscp   Description
     */
    public static function Register(string $key, string $cls, ?string $dscp=NULL) : void
    {
        self::$_providers[$key] = array(
            'cls' => $cls,
            'dscp' => $dscp === NULL ? "$key Notifications" : $dscp,
        );
    }


    /**
     * Get an array of provider information.
     *
     * @return  array   Array of (key, classname, description) elements
     */
    public static function getProviders() : array
    {
        return self::$_providers;
    }


    /**
     * Get an instance of a provider class.
     * The default `email` provider is returned if the requested key
     * is not available. If a plugin needs to take other action when
     * the requested provider is not available, it should call
     * Notifier::exists($key) first to check.
     *
     * @param   string  $key    Provider ID key
     * @param   boolean $default    False to return NULL instead of default
     * @return  object  Provider object
     */
    public static function getProvider(string $key, bool $default=true) : ?object
    {
        $retval = NULL;
        if (array_key_exists($key, self::$_providers)) {
            $cls = self::$_providers[$key]['cls'];
            if (class_exists($cls)) {
                $retval = new $cls;
            }
        }
        if ($default && $retval === NULL) {
            $retval = new Notifiers\Email;
        }
        return $retval;
    }


    /**
     * Check if a provider is available.
     * This allows plugins to take other action if the default email
     * notification is not desired.
     *
     * @param   string  $key    Provider ID key
     * @return  boolean     True if provider is available
     */
    public static function exists(string $key) : bool
    {
        $retval = false;
        if (array_key_exists($key, self::$_providers)) {
            $cls = self::$_providers[$key]['cls'];
            if (class_exists($cls)) {
                $retval = true;
            }
        }
        return $retval;
    }


    /**
     * Send the notification.
     * Must be implemented by child classes.
     */
    abstract public function send();

}

