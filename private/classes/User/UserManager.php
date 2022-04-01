<?php
/**
* glFusion CMS
*
* User Manager - Create user, Delete User, other??
*
* Based off the PHP-Auth (https://github.com/delight-im/PHP-Auth)
* Copyright (c) delight.im (https://www.delight.im/)
* Licensed under the MIT License (https://opensource.org/licenses/MIT)
*
* Heavily modified to retro fit into the glFusion environment to minimize existing
* functionality and capabilities.
*
*  Modifications Copyright (C) 2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

namespace glFusion\User;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use glFusion\User\Status;
use glFusion\Database\Database;
use glFusion\Log\Log;
use glFusion\Notifiers\Email;

class UserManager extends User
{
    /** @var core data fields provided by user */
    protected $userDataFields = ['username','email','passwd','fullname','homepage','remoteusername','service'];
	/** @var all the various user records */
	protected $userId;



    public function __construct()
    {

    }

	/**
	 * Attempts to sign up a user
	 *
	 * If you want the user's account to be activated by default, pass `null` as the callback
	 *
	 * If you want to make the user verify their email address first, pass an anonymous function as the callback
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
	 *
	 * @param string $email the email address to register
	 * @param string $password the password for the new account
	 * @param string|null $username (optional) the username that will be displayed
	 * @param callable|null $callback (optional) the function that sends the confirmation email to the user
	 * @return int the ID of the user that has been created (if any)
	 * @throws InvalidEmailException if the email address was invalid
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws UserAlreadyExistsException if a user with the specified email address already exists
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see confirmEmail
	 * @see confirmEmailAndSignIn
	 */
//@TODO - remove
	public function register($email, $password, $username = null, callable $callback = null) {
		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);
		$this->throttle([ 'createNewAccount', $this->getIpAddress() ], 1, (60 * 60 * 12), 5, true);

		$newUserId = $this->createUserInternal(false, $email, $password, $username, $callback);

		$this->throttle([ 'createNewAccount', $this->getIpAddress() ], 1, (60 * 60 * 12), 5, false);

		return $newUserId;
	}

	/**
	 * Attempts to sign up a user while ensuring that the username is unique
	 *
	 * If you want the user's account to be activated by default, pass `null` as the callback
	 *
	 * If you want to make the user verify their email address first, pass an anonymous function as the callback
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
	 *
	 * @param string $email the email address to register
	 * @param string $password the password for the new account
	 * @param string|null $username (optional) the username that will be displayed
	 * @param callable|null $callback (optional) the function that sends the confirmation email to the user
	 * @return int the ID of the user that has been created (if any)
	 * @throws InvalidEmailException if the email address was invalid
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws UserAlreadyExistsException if a user with the specified email address already exists
	 * @throws DuplicateUsernameException if the specified username wasn't unique
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see confirmEmail
	 * @see confirmEmailAndSignIn
	 */
//@TODO remove
	public function registerWithUniqueUsername($email, $password, $username = null, callable $callback = null) {
		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);
		$this->throttle([ 'createNewAccount', $this->getIpAddress() ], 1, (60 * 60 * 12), 5, true);

		$newUserId = $this->createUserInternal(true, $email, $password, $username, $callback);

		$this->throttle([ 'createNewAccount', $this->getIpAddress() ], 1, (60 * 60 * 12), 5, false);

		return $newUserId;
	}

    /**
	 * Attempts to sign up a user
	 *
	 * If you want the user's account to be activated by default, pass `null` as the callback
	 *
	 * If you want to make the user verify their email address first, pass an anonymous function as the callback
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
     *
     * We have 3 ways to create a user
     *  - local registration
     *  - oauth registration
     *  - remote user registration
	 *
	 * @param string $email the email address to register
	 * @param string $password the password for the new account
	 * @param string|null $username (optional) the username that will be displayed
	 * @param callable|null $callback (optional) the function that sends the confirmation email to the user
	 * @return int the ID of the user that has been created (if any)
	 * @throws InvalidEmailException if the email address was invalid
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws UserAlreadyExistsException if a user with the specified email address already exists
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
     *
     * EmailConfirmationMismatchException
     * UserAlreadyExistsException
     * EmailAlreadyExistsException
     * PasswordConfirmationMismatchException
     * PasswordComplexityException
     * InvaidFullnameException
	 *
	 * @see confirmEmail
	 * @see confirmEmailAndSignIn
	 */
    public function registerUser($info = array())
    {
        global $_CONF, $_TABLES, $LANG04, $MESSAGE;

		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);
		$this->throttle([ 'createNewAccount', $this->getIpAddress() ], 1, (60 * 60 * 12), 5, true);

        // build out skeleton record

        $verified = 0;

        $data = array();

        $data['regtype']        = 'local';  // registration type - local or oauth
        $data['username']       = '';       // contains the username for the glFusion site
        $data['email']          = '';       // user's email address
        $data['email_conf']     = '';       // always defaults to blank
        $data['passwd']         = '';       // user's password
        $data['passwd_conf']    = '';       // always defaults to blank
        $data['fullname']       = '';       // user's fullname
        $data['homepage']       = '';       // user's homepage
        $data['oauth_provider'] = '';       // oauth provider (i.e.; oauth.twitter, oauth.facebook, etc.)
        $data['oauth_username'] = '';       // oauth username
        $data['oauth_email']    = '';       // oauth email (not guarenteed to be returned)
        $data['oauth_service']  = '';       // oauth service - same as oauth_provider?

        // submitted data

        $data['regtype']        = isset($info['regtype']) && $info['regtype'] != '' ? $info['regtype'] : 'local';
        $data['username']       = isset($info['username']) ? filter_var($info['username'],FILTER_UNSAFE_RAW) : '';
        $data['email']          = isset($info['email']) ? filter_var($info['email'],FILTER_SANITIZE_EMAIL) : '';
        $data['email_conf']     = isset($info['email_conf']) ? filter_var($info['email_conf'],FILTER_SANITIZE_EMAIL) : '';
        $data['passwd']         = isset($info['passwd']) ? filter_var($info['passwd'],FILTER_UNSAFE_RAW) : '';
        $data['passwd_conf']    = isset($info['passwd_conf']) ? filter_var($info['passwd_conf'],FILTER_UNSAFE_RAW) : '';
        $data['fullname']       = isset($info['fullname']) ? filter_var($info['fullname'],FILTER_UNSAFE_RAW) : '';
        $data['homepage']       = isset($info['homepage']) ? filter_var($info['homepage'],FILTER_SANITIZE_URL) : '';
        $data['oauth_provider'] = isset($info['oauth_provider']) ? $info['oauth_provider'] : '';
        $data['oauth_service']  = isset($info['oauth_service']) ? $info['oauth_service'] : '';
        $data['oauth_username'] = isset($info['oauth_username']) ? $info['oauth_username'] : '';
        $data['oauth_email']    = isset($info['oauth_email']) ? filter_var($info['oauth_email'],FILTER_SANITIZE_EMAIL) : '';

        if ( !empty($data['oauth_email']) ) {
            $data['email'] = $data['oauth_email'];
        }

        // Data Validations

        // throws Exceptions\InvalidEmailException if email is not valid
        $data['email'] = self::validateEmailAddress($data['email']);

        if ($data['regtype'] == 'local') {
            if ($data['email'] !== $data['email_conf']) {
                throw new Exceptions\EmailConfirmationMismatchException($MESSAGE[508]);
            }
        }

        self::checkDisallowedDomains($data['email'],$_CONF['disallow_domains']);

        $occurrencesOfUsername = Database::getInstance()->conn->fetchColumn(
            "SELECT COUNT(*) FROM {$_TABLES['users']} WHERE username = ?",
            [ $data['username'] ],
            0
        );
        // if any user with that username does already exist
        if ($occurrencesOfUsername > 0) {
            // cancel the operation and report the violation of this requirement
            throw new Exceptions\DuplicateUsernameException($LANG04[19]);
        }

        // only check local accounts for duplicate emails
        if ($data['regtype'] == 'local') {
            $occurrencesOfEmail = Database::getInstance()->conn->fetchColumn(
                "SELECT COUNT(*) FROM {$_TABLES['users']} WHERE email = ?",
                [ $data['email'] ],
                0
            );
            // if any user with that username does already exist
            if ($occurrencesOfEmail > 0) {
                // cancel the operation and report the violation of this requirement
                throw new Exceptions\DuplicateEmailException($LANG04[19]);
            }
        }

        $data['passwd'] = trim($data['passwd']);
        $data['passwd_conf'] = trim($data['passwd_conf']);

//@TODO - remove registration type
        if ($data['regtype'] == 'local' && $_CONF['registration_type'] == 1 ) {
            if ( empty($data['passwd']) || $data['passwd'] != $data['passwd_conf'] ) {
                throw new Exceptions\PasswordConfirmationMismatchException($MESSAGE[67]);
            }
            $err = SEC_checkPwdComplexity($data['passwd']);
            if (count($err) > 0 ) {
                throw new Exceptions\PasswordComplexityException('Password does not meet the complexity requirements');
            }
        }

        $data['fullname'] = substr(
                              trim($this->sanitizeUsername($data['fullname'])),
                              0,80
                            );

        if ( $_CONF['user_reg_fullname'] == 2) {
            if (empty($data['fullname'])) {
                throw new Exceptions\InvaidFullnameException('Full name is required');
            }
        }

        // spam checks

        $spamCheckData = array(
            'username'  => $data['username'],
            'email'     => $data['email'],
            'ip'        => $_SERVER['REAL_ADDR'],
            'type'      => 'registration');

        $msg = PLG_itemPreSave ('registration', $spamCheckData);
        if (!empty ($msg)) {
            throw new Exceptions\RegistrationException($msg);
        }

        // do our spam check
        $result = PLG_checkforSpam($data['username'], $_CONF['spamx'],$spamCheckData);
        if ($result > 0) {
            COM_displayMessageAndAbort($result, 'spamx', 403, 'Forbidden');
        }

        // is there a custom user check
        if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userCheck')) {
            $msg = CUSTOM_userCheck ($data['username'], $data['email']);
            $displayableMessages = '';
            if (is_array($msg)) {
                foreach($msg AS $message) {
                    $displayableMessages .= $message.'<br>';
                }
                throw new Exceptions\CustomRegistrationException($displayableMessages);
            } else {
                throw new Exceptions\CustomRegistrationException($msg);
            }
//@TODO - this needs to move to the users.php - this is where we call the custom form
            if (!empty ($msg)) {
                if (function_exists('CUSTOM_userForm')) {
                    return CUSTOM_userForm ($msg);
                }
            }
//@END OF TODO - move
        }

        // all validations have passed - create the user record

        // create the account and send notification if passed.
        $newUserId = self::createAccount($data['username'],$data['email'],$data['passwd'],
                        $data['fullname'],$data['homepage'],$data['oauth_username'],$data['oauth_provider'],
                        array($this,'sendConfirmationRequest'));

        $this->throttle([ 'createNewAccount', $this->getIpAddress() ], 1, (60 * 60 * 12), 5, false);

		return $newUserId;
    }

    /**
    * Create a new user
    *
    * Also calls the custom user registration (if enabled) and plugin functions.
    * Notififies admins of new account
    *
    * General createAccount function - will create the skeleton record and return
    * the new user id.
    *
    * @param    string  $username   user name (mandatory)
    * @param    string  $email      user's email address (mandatory)
    * @param    string  $passwd     password (optional, see above)
    * @param    string  $fullname   user's full name (optional)
    * @param    string  $homepage   user's home page (optional)
    * @param    string  $remoteusername  oauth username  (optional)
    * @param    string  $service    oauth service (optional)
    * @param    callable $callback  Used to send confirmation email (optional)
    * @return   int                 new user's ID or null
    *
    */
    protected function createAccount ($username, $email, $passwd = '', $fullname = '', $homepage = '', $remoteusername = '', $service = '', callable $callback = null)
    {
        global $_CONF, $_USER, $_TABLES;

        \ignore_user_abort(true);

        $dt = new \Date('now',$_USER['tzid']);

        $db = Database::getInstance();

        $fields = array();
        $types  = array();

        $queueUser = false;

        $verified = \is_callable($callback) ? 0 : 1;

        $regdate = $dt->toMySQL(true);

        $fields = array('username'      => $username,
                        'email'         => $email,
                        'regdate'       => $regdate,
                        'cookietimeout' => $_CONF['default_perm_cookie_timeout'],
                        'status'        => Status::NORMAL,      // default value
                        'verified'      => $verified,           // default value
                        'account_type'  => LOCAL_USER           // default value
                );
        $types  = array(
                        Database::STRING,   // username
                        Database::STRING,   // email
                        Database::STRING,   // regdate
                        Database::STRING,   // cookie timeout
                        Database::INTEGER,  // status
                        Database::INTEGER,  // verified
                        Database::INTEGER   // account type
                );

        if (!empty ($passwd)) {
            $fields['passwd'] = SEC_encryptPassword($passwd);
            $types[] = Database::STRING;
        }
        if (!empty ($fullname)) {
            $fields['fullname'] = $fullname;
            $types[] = Database::STRING;
        }
        if (!empty ($homepage)) {
            $fields['homepage'] = $homepage;
            $types[] = Database::STRING;
        }

        if (($_CONF['usersubmission'] == 1) && !SEC_hasRights ('user.edit')) {
            $queueUser = true;
            if (!empty ($_CONF['allow_domains'])) {
                if (USER_emailMatches ($email, $_CONF['allow_domains'])) {
                    $queueUser = false;
                }
            }

            if ($queueUser) {
                $fields['status'] = Status::PENDING_REVIEW;
            }
        }

        // if remote username or remote service is set - change account type to remote_user
        if (!empty($remoteusername)) {
            $fields['remoteusername'] = $remoteusername;
            $types[] = Database::STRING;
            $fields['account_type']= REMOTE_USER;
        }
        if (!empty($service)) {
            $fields['remoteservice'] = $service;
            $types[] = Database::STRING;
        }

        // This is the same for all user types - it builds out the database records
        // and makes all default group assignments.

        $db->conn->beginTransaction();
        try {
            $db->conn->beginTransaction();
            try {
                $db->conn->insert($_TABLES['users'],$fields,$types);
                $db->conn->commit();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $db->conn->rollBack();
                throw new Exceptions\UserAlreadyExistsException();
            } catch(\Throwable $e) {
                $db->conn->rollBack();
                Log::write('system',Log::ERROR,"UserManager::createUser() :: Error inserting user into USERS table :: " . $e->getMessage());
                return null;
            }

            // Get the uid of the user, possibly given a service:
            if ($remoteusername != '') {
                $uid = $db->getItem($_TABLES['users'],'uid',array('remoteusername' => $remoteusername,'remoteservice' => $service));
            } else {
                $uid = $db->conn->fetchColumn(
                    "SELECT uid FROM `{$_TABLES['users']}` WHERE username=? AND remoteservice IS NULL",
                    array($username),
                    0,
                    array(Database::STRING)
                );
            }
            if ( $uid === false  ) {
                Log::write('system',Log::ERROR,"UserManager::createUser() :: Error: Unable to retrieve uid after creating user");
                $db->conn->rollBack();
                return null;
            }

            // any default groups?
            $stmt = $db->conn->query("SELECT grp_id FROM `{$_TABLES['groups']}` WHERE grp_default=1");
            $grpDefaults = $stmt->fetchAll(Database::ASSOCIATIVE);
            foreach ($grpDefaults AS $row) {
                $db->conn->insert($_TABLES['group_assignments'],
                                    array(
                                        'ug_main_grp_id' => $row['grp_id'],
                                        'ug_uid'=> $uid
                                    ),
                                    array(
                                        Database::INTEGER,
                                        Database::INTEGER
                                    )
                );
            }
            $db->conn->insert($_TABLES['userprefs'],
                                array(
                                    'uid' => $uid,
                                    'tzid' => $_CONF['timezone']
                                ),
                                array(
                                    Database::INTEGER,
                                    Database::STRING
                                )
            );
            $etids = '';
            if ($_CONF['emailstoriesperdefault'] == 1) {
                $etids = '-';
            }

            $db->conn->insert($_TABLES['userindex'],
                                array('uid' => $uid,'etids' => $etids),
                                array(Database::INTEGER, Database::STRING)
            );

            $db->conn->insert($_TABLES['usercomment'],
                                array('uid' => $uid,'commentmode' => $_CONF['comment_mode'], 'commentlimit' => $_CONF['comment_limit']),
                                array(Database::INTEGER, Database::STRING, Database::INTEGER)
            );

            $db->conn->insert($_TABLES['userinfo'],
                array('uid' => $uid),
                array(Database::INTEGER)
            );

            $db->conn->commit();

        } catch (\Exception $e) {
            $db->conn->rollBack();
            Log::write('system',Log::ERROR,'UserManager::createUser() - Error creating user records - Database transaction rolledback ' . $e->getMessage());
            return NULL;
        }

        // call custom registration function and plugins
        if ($_CONF['custom_registration'] && (function_exists ('CUSTOM_userCreate'))) {
            CUSTOM_userCreate ($uid);
        }
        if ( function_exists('CUSTOM_userCreateHook') ) {
            CUSTOM_userCreateHook($uid);
        }

        // let plugins know we have a new user
        PLG_createUser($uid);

        // Notify the admin?
        if (($verified == 0) && (isset ($_CONF['notification']) && in_array ('user', $_CONF['notification']))) {
            if ($queueUser) {
                $mode = 'inactive';
            } else {
                $mode = 'active';
            }
            try {
                $this->notifyAdmin($username,$email,$uid,$mode);
            } catch (\Throwable $e) {
                Log::write('system',Log::ERROR,'UserManager::createUser() - Error notifying the admin via email of a new user registration');
            }
        }

        // send notification if the callback was provided

        if ($verified == 0) {
            $this->createConfirmationRequest($uid, $email, $callback);
        }

        return $uid;
    }

    public static function sanitizeUsername($username)
    {
        $filter = \sanitizer::getInstance();
        return $filter->sanitizeUsername($username);
    }

    protected function validateUserData()
    {

    }

    protected function spamCheck()
    {

    }

    protected function insertUserRecord()
    {

    }

    protected function notifyAdmins()
    {

    }

    protected function sendVerificationEmail()
    {

    }

    /**
    * Send an email notification when a new user registers with the site.
    *
    * @param username string      User name of the new user
    * @param email    string      Email address of the new user
    * @param uid      int         User id of the new user
    * @param mode     string      Mode user was added at.
    *
    */
    protected function notifyAdmin ($username, $email, $uid, $mode='inactive')
    {
        global $_CONF, $_USER, $LANG01, $LANG04, $LANG08, $LANG28, $LANG29;

        $dt = new \Date('now',$_USER['tzid']);

        $mailbody = "$LANG04[2]: $username\n"
                . "$LANG04[5]: $email\n"
                . "$LANG28[14]: " . $dt->format($_CONF['date'],true) . "\n\n";

        if ($mode == 'inactive') {
            // user needs admin approval
            $mailbody .= "$LANG01[10] {$_CONF['site_admin_url']}/moderation.php\n\n";
        } else {
            // user has been created, or has activated themselves:
            $mailbody .= "$LANG29[4] {$_CONF['site_url']}/users.php?mode=profile&uid={$uid}\n\n";
        }
        $mailbody .= "\n------------------------------\n";
        $mailbody .= "$LANG08[34]";
        $mailbody .= "\n------------------------------\n";

        $mailsubject = $_CONF['site_name'] . ' ' . $LANG29[40];

        $msgData = array();

		$msgData['textmessage'] = $mailbody;
		$msgData['subject'] = $mailsubject;

		$msgData['to'] = $_CONF['site_mail'];

		Log::write('system',Log::DEBUG,'UserManager::notifyAdmin() - Sending Admin Notification via Email::sendNotification(()');

        $emailHandler = new Email();
		$emailHandler->sendNotification($msgData);
    }

}