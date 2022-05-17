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
use glFusion\Database\Database;


/**
 * Base notification class.
 * @package shop
 */
abstract class Notifier
{
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
    protected $exp_ts = 2145945599;     // 2037-12-31 23:59:59 UTC


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
     * Set the duration, in seconds, for the message.
     *
     * @param   integer $seconds    Number of seconds to keep the message
     * @return  object  $this
     */
    public function setDuration(int $seconds) : self
    {
        $this->exp_ts = time() + $seconds;
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
     * @return  object  Provider object
     */
    public static function getProvider(string $key) : object
    {
        $retval = NULL;
        if (array_key_exists($key, self::$_providers)) {
            $cls = self::$_providers[$key]['cls'];
            if (class_exists($cls)) {
                $retval = new $cls;
            }
        }
        if ($retval === NULL) {
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

