<?php
/**
* glFusion CMS
*
* User Manager - Create user
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
    die ('This file can not be used on its own!');
}

use glFusin\User\UserAuthOauth;
use glFusion\User\Status;
use glFusion\User\Exception;
use Delight\Base64\Base64;
use Delight\Cookie\Cookie;
use Delight\Cookie\Session;
use glFusion\Database\Database;
use glFusion\Log\Log;

class UserCreate extends User
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
    public function createUser($info = array())
    {
        global $_CONF, $_TABLES;

        // build out skeleton record
        $data = array();

        $data['regtype']        = 'local';  // registration type - local or oauth
        $data['username']       = '';       // contains the username for the glFusion site
        $data['email']          = '';       // user's email address
        $data['email_conf']     = '';       // always defaults to blank
        $data['passwd']         = '';       // user's password
        $data['passwd_conf']    = '';       // always defaults to blank
        $data['fullname']       = '';       // user's fullname
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
        $data['oauth_provider'] = isset($info['oauth_provider']) ? $info['oauth_provider'] : '';
        $data['oauth_service']  = isset($info['oauth_service']) ? $info['oauth_service'] : '';
        $data['oauth_username'] = isset($info['oauth_username']) ? $info['oauth_username'] : '';
        $data['oauth_email']    = isset($info['oauth_email']) ? filter_var($info['oauth_email'],FILTER_SANITIZE_EMAIL) : '';

        // data validations

        // email
        $data['email'] = substr((trim($data['email'])),0,96);
        $data['email_conf'] = substr((trim($data['email_conf'])),0,96);

        if ( !empty($data['oauth_email']) ) {
            $data['email'] = $data['oauth_email'];
        }
        if ($data['email'] !== $data['email_conf']) {
            throw new Exceptions\EmailConfirmationMismatchException();
        }

        $data['email'] = self::validateEmailAddress($data['email']);

        self::checkDisallowedDomains($data['email'],$_CONF['disallow_domains']);

        $ucount = $ecount = 0;

        $ucount = $db->getCount($_TABLES['users'], 'username',$data['username'],Database::STRING);
        if ( $ucount != 0 ) {
            throw new \glFusion\User\Exceptions\UserAlreadyExistsException();
        }

        if ( $data['regtype'] == 'local' || $data['regtype'] == '' ) {
            $ecount = $db->getCount($_TABLES['users'], 'email', $data['email'], Database::STRING);
            if ( $ecount != 0 ) {
                throw new \glFusion\User\Exceptions\EmailAlreadyExistsException();
            }
        }

        $data['passwd'] = trim($data['passwd']);
        $data['passwd_conf'] = trim($data['passwd_conf']);

        if ($data['regtype'] == 'local' && $_CONF['registration_type'] == 1 ) {
            if ( empty($data['passwd']) || $data['passwd'] != $data['passwd_conf'] ) {
                throw new \glFusion\User\Exceptions\PasswordConfirmationMismatchException();
            }
            $err = SEC_checkPwdComplexity($data['passwd']);
            if (count($err) > 0 ) {
                throw new \glFusion\User\Exceptions\PasswordComplexityException();
            }
        }

        $data['fullname'] = substr(
                              trim($this->sanitizeUsername($data['fullname'])),
                              0,80
                            );

        if ( $_CONF['user_reg_fullname'] == 2) {
            if (empty($data['fullname'])) {
                throw new Exceptions\InvaidFullnameException();
            }
        }



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

}