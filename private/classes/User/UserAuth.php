<?php
/**
* glFusion CMS
*
* glFusion Authentication Library
*
* @license MIT License (https://opensource.org/licenses/MIT)
*
* Heavily modified to retro fit to the glFusion environment

* PHP-Auth (https://github.com/delight-im/PHP-Auth)
* Copyright (c) delight.im (https://www.delight.im/)
* Licensed under the MIT License (https://opensource.org/licenses/MIT)
*
*/

/*
 * PHP-Auth (https://github.com/delight-im/PHP-Auth)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace glFusion\User;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use glFusin\User\UserAuthOauth;
use glFusion\User\Status;
use Delight\Base64\Base64;
use Delight\Cookie\Cookie;
use Delight\Cookie\Session;
use glFusion\Database\Database;
use glFusion\Log\Log;

/** Component that provides all features and utilities for secure authentication of individual users */
final class UserAuth extends User {

	const COOKIE_PREFIXES = [ Cookie::PREFIX_SECURE, Cookie::PREFIX_HOST ];
	const COOKIE_CONTENT_SEPARATOR = '~';


	/** @var int the interval in seconds after which to resynchronize the session data with its authoritative source in the database */
	private $sessionResyncInterval;
	/** @var string the name of the cookie used for the 'remember me' feature */
	private $rememberCookieName;
	/** @var bool inside oauth authentication */
	private $inOauth = false;

 	/**
	 * @param string|null $ipAddress (optional) the IP address that should be used instead of the default setting (if any), e.g. when behind a proxy
	 * @param bool|null $throttling (optional) whether throttling should be enabled (e.g. in production) or disabled (e.g. during development)
	 * @param int|null $sessionResyncInterval (optional) the interval in seconds after which to resynchronize the session data with its authoritative source in the database
	 */
	public function __construct($ipAddress = null, $throttling = null, $sessionResyncInterval = null) {
		parent::__construct();

		$this->ipAddress = !empty($ipAddress) ? $ipAddress : (isset($_SERVER['REAL_ADDR']) ? $_SERVER['REAL_ADDR'] : null);
		$this->throttling = isset($throttling) ? (bool) $throttling : true;
		$this->sessionResyncInterval = isset($sessionResyncInterval) ? ((int) $sessionResyncInterval) : (60 * 5);
		$this->rememberCookieName = self::createRememberCookieName();

        // added for glFusion - set the initial values of the userdata array
        $this->initializeUserdata();

		$this->initSessionIfNecessary();
		$this->enhanceHttpSecurity();
		$this->processRememberDirective();
		$this->resyncSessionIfNecessary();
	}

	/** Initializes the session and sets the correct configuration */
	private function initSessionIfNecessary() {
		if (\session_status() === \PHP_SESSION_NONE) {
			// use cookies to store session IDs
			\ini_set('session.use_cookies', 1);
			// use cookies only (do not send session IDs in URLs)
			\ini_set('session.use_only_cookies', 1);
			// do not send session IDs in URLs
			\ini_set('session.use_trans_sid', 0);
			// start the session (requests a cookie to be written on the client)
			@Session::start();
		}
	}

	/** Initialize the userData info */
	private function initializeUserdata() {
		global $_CONF;

		return array(
			'uid'               => 1,
			'theme'             => $_CONF['theme'],
			'tzid'              => $_CONF['timezone'],
			'language'          => $_CONF['language'],
			'username'          => 'Anonymous',
			'remoteusername'    => '',
			'remoteservice'     => '',
			'fullname'          => 'Anonymous',
			'passwd'            => '',
			'email'             => '',
			'homepage'          => '',
			'sig'               => '',
			'regdate'           => '',
			'photo'             => 'default.jpg',
			'cookietimeout'     => 0,
			'pwrequest'         => '',
			'act_token'         => '',
			'act_time'          => '',
			'tfa_enabled'       => false,
			'tfa_secret'        => '',
			'status'            => 3,
			'account_type'      => '',
			'num_reminders'     => 0,
			'lastlogin'			=> 0,
			'remote_ip'         => $this->ipAddress
		);
	}

    /** Pulls the full glFusion record */
    public function getUserData()
    {
        global $_TABLES;

		if (!$this->isLoggedIn()) {
			return $this->initializeUserdata();
		}

		$db = Database::getInstance();

		$sql = "SELECT *,format FROM `{$_TABLES['dateformats']}`,`{$_TABLES['users']}`,`{$_TABLES['userprefs']}`,`{$_TABLES['userinfo']}`,`{$_TABLES['userindex']}` "
		 . "WHERE {$_TABLES['dateformats']}.dfid = {$_TABLES['userprefs']}.dfid AND "
		 . "{$_TABLES['userprefs']}.uid = :userid
			AND {$_TABLES['users']}.uid = :userid
			AND {$_TABLES['userinfo']}.uid = :userid
			AND {$_TABLES['userindex']}.uid= :userid";

		$cacheKey = (string) 'userdata_'.(int)$this->getUserId();

		try {
			$stmt = $db->conn->executeQuery($sql,
				array('userid' => $this->getUserId()),
				array(Database::INTEGER),
				new \Doctrine\DBAL\Cache\QueryCacheProfile(3600, $cacheKey)
				);
		} catch(\Throwable $e) {
			$db->dbError($e->getMessage(),$sql);
		}

		$data = $stmt->fetchAll(Database::ASSOCIATIVE);
		$stmt->closeCursor();
		if (count($data) < 1) {
			return false;
		}
		$myrow = $data[0];

		if (isset($myrow['passwd'])) {
			unset($myrow['passwd']);
		}
		return $myrow;
    }

	/**
	 * This either logs in the user or throws the appropriate error and redirects to the proper place.
	 */
	public function userLocalLoginController()
	{
		global $_CONF, $MESSAGE, $LANG04, $LANG12;

		$options = array();

		// user has submitted a username and password with no service or local service

		// need to validate CSRF token

		// validate we accept local logins
		if ($_CONF['user_login_method']['standard'] != 1) {
			die('Local Login is disabled');
//			throw new Exceptions\LocalLoginServiceDisabledException();
		}

		$loginname = filter_input(\INPUT_POST, 'loginname', \FILTER_UNSAFE_RAW);
		$passwd    = filter_input(\INPUT_POST,'passwd', \FILTER_UNSAFE_RAW);

		if (empty($loginname) || empty($passwd)) {
			COM_setMsg($MESSAGE[81],'error');
			UserInterface::loginPage();
		}

		try {
			self::loginWithUsername($loginname, $passwd,0,array($this,'userLoginBeforeSuccess'));

		} catch (Exceptions\InvalidPasswordException | Exceptions\UnknownUsernameException $e) {
			COM_setMsg($MESSAGE[81],'error');
			UserInterface::loginPage();

		} catch (Exceptions\TooManyRequestsException $e) {
			displayLoginErrorAndAbort(82, $LANG12[26], $LANG04[112]);

		} catch (Exceptions\AttemptCancelledException $e) {
			// the attempt was cancelled - possibly CAPTCHA failed or other validation before login failed
			UserInterface::loginPage();

		} catch (Exceptions\AccountPendingReviewException $e) {
			$options['title']   = $LANG04[116]; // account awaiting activation
			$options['message'] = $LANG04[117]; // your account is currently awaiting activation by an admin
			$options['forgotpw_link']      = false;
			$options['newreg_link']        = false;
			$options['verification_link']  = false;
			UserInterface::loginPage($options);

		} catch (Exceptions\EmailNotVerifiedException $e) {
			$options['title']   = $LANG04[116]; // account awaiting activation
			$options['message'] = $LANG04[177]; // your account is currently awaiting verification
			$options['forgotpw_link']      = false;
			$options['newreg_link']        = false;
			$options['verification_link']  = true;
			UserInterface::loginPage($options);

		} catch (\Throwable $e) {
			// some other error - so we abort...
			die('UserAuth.php :: unknown error in loginWithUsername() ' . $e->getMessage());
		}

		// finalize local login by passing true
		$this->userFinalLogin(true);

	}

	/**
	 * Oauth Login Controller
	 */
	public function userOauthLoginController()
	{
		global $_CONF, $MESSAGE, $LANG04, $LANG12;

		// validate we accept oauth logins
		if ($_CONF['user_login_method']['oauth'] != 1) {
			die('Oauth Login is disabled');
//			throw new Exceptions\OauthLoginServiceDisabledException();
		}

		try {
			self::loginWithOauth(0, array($this,'userLoginBeforeSuccess'));
		} catch (Exceptions\EmailNotVerifiedException $e) {
			COM_setMsg($LANG04[177],'error');
			COM_refresh($_CONF['site_url']);
		} catch (\Throwable $e) {
			Log::write('system',Log::ERROR,"OAuth Error: " . $e->getMessage());
			COM_setMsg($MESSAGE[111],'error');
			UserInterface::loginPage();
		}

		// finalize oauth login - pass false as this is not local
		$this->userFinalLogin(false);
	}


		/**
		 * this is called after a user has been authenticated and sets up all the user information
		 * needed to have a user authenticated to the system.
		 */
	private function userFinalLogin($local_login = false)
	{
		global $_CONF, $_USER, $_GROUPS, $_RIGHTS, $_SYSTEM;

		$status = $this->getStatus();

        if ($status == Status::NORMAL || $status == USER_ACCOUNT_AWAITING_ACTIVATION ) { // logged in AOK.
            $_USER = $this->getUserData();
            $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
            $_RIGHTS = explode( ',', SEC_getUserPermissions() );

            if ((int) $_SYSTEM['admin_session'] > 0 && $local_login ) {
                if (SEC_isModerator() || SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR') || (count(PLG_getAdminOptions()) > 0)) {
                    Session::set(self::SESSION_FIELD_ADMIN_SESSION,\time() + $_SYSTEM['admin_session']);
                }
            }
            if ( !isset($_USER['theme']) ) {
                $_USER['theme'] = $_CONF['theme'];
                $_CONF['path_layout'] = $_CONF['path_themes'] . $_USER['theme'] . '/';
                $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_USER['theme'];
                if ( $_CONF['allow_user_themes'] == 1 ) {
                    if ( isset( $_COOKIE[$_CONF['cookie_theme']] ) ) {
                        $theme = COM_sanitizeFilename($_COOKIE[$_CONF['cookie_theme']], true);
                        if ( is_dir( $_CONF['path_themes'] . $theme )) {
                            $_USER['theme'] = $theme;
                            $_CONF['path_layout'] = $_CONF['path_themes'] . $theme . '/';
                            $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $theme;
                        }
                    }
                }
            }
			// need to go with the cookie manager

            SEC_setCookie ($_CONF['cookie_language'], $_USER['language'], time() + 31536000,
                           $_CONF['cookie_path'], $_CONF['cookiedomain'],
                           $_CONF['cookiesecure'],false);
//            COM_resetSpeedlimit('login');

            // we are now fully logged in, let's see if there is someplace we need to go....
            // First, check for a landing page supplied by the form or global config
            foreach (array($_POST, $_CONF) as $A) {
                if (isset($A['login_landing']) && !empty($A['login_landing'])) {
                    if ($A['login_landing'][0] != '/') {
                        $A['login_landing'] = '/' . $A['login_landing'];
                    }
                    COM_refresh($_CONF['site_url'] . $A['login_landing']);
                }
            }

            if ( SESS_isSet('login_referer') ) {
                $_SERVER['HTTP_REFERER'] = SESS_getVar('login_referer');
                SESS_unSet('login_referer');
            }
            if (!empty($_SERVER['HTTP_REFERER'])
                    && (strstr($_SERVER['HTTP_REFERER'], '/users.php') === false)
                    && (substr($_SERVER['HTTP_REFERER'], 0,
                            strlen($_CONF['site_url'])) == $_CONF['site_url'])) {
                $indexMsg = $_CONF['site_url'] . '/index.php?msg=';
                if (substr ($_SERVER['HTTP_REFERER'], 0, strlen ($indexMsg)) == $indexMsg) {
                    echo COM_refresh ($_CONF['site_url'] . '/index.php');
                } else {
                    // If user is trying to login - force redirect to index.php
                    if (strstr ($_SERVER['HTTP_REFERER'], 'mode=login') === false) {
                    // if article - we need to ensure we have the story
                        if ( substr($_SERVER['HTTP_REFERER'], 0,strlen($_CONF['site_url'])) == $_CONF['site_url']) {
                            echo COM_refresh (COM_sanitizeUrl($_SERVER['HTTP_REFERER']));
                        } else {
                            echo COM_refresh($_CONF['site_url'].'/index.php');
                        }
                    } else {
                        echo COM_refresh ($_CONF['site_url'] . '/index.php');
                    }
                }
            } else {
                echo COM_refresh ($_CONF['site_url'] . '/index.php');
            }
        }
	}

	/** Improves the application's security over HTTP(S) by setting specific headers */
	private function enhanceHttpSecurity() {
		// remove exposure of PHP version (at least where possible)
		\header_remove('X-Powered-By');

		// if the user is signed in
		if ($this->isLoggedIn()) {
			// prevent clickjacking
			\header('X-Frame-Options: sameorigin');
			// prevent content sniffing (MIME sniffing)
			\header('X-Content-Type-Options: nosniff');

			// disable caching of potentially sensitive data
			\header('Cache-Control: no-store, no-cache, must-revalidate', true);
			\header('Expires: Thu, 19 Nov 1981 00:00:00 GMT', true);
			\header('Pragma: no-cache', true);
		}
	}

	/** Checks if there is a "remember me" directive set and handles the automatic login (if appropriate) */
	private function processRememberDirective() {
        global $_TABLES;

		// if the user is not signed in yet
		if (!$this->isLoggedIn()) {
			// if there is currently no cookie for the 'remember me' feature
			if (!isset($_COOKIE[$this->rememberCookieName])) {
				// if an old cookie for that feature from versions v1.x.x to v6.x.x has been found
				if (isset($_COOKIE['auth_remember'])) {
					// use the value from that old cookie instead
					$_COOKIE[$this->rememberCookieName] = $_COOKIE['auth_remember'];
				}
			}

			// if a remember cookie is set
			if (isset($_COOKIE[$this->rememberCookieName])) {
				// assume the cookie and its contents to be invalid until proven otherwise
				$valid = false;

				// split the cookie's content into selector and token
				$parts = \explode(self::COOKIE_CONTENT_SEPARATOR, $_COOKIE[$this->rememberCookieName], 2);

				// if both selector and token were found
				if (!empty($parts[0]) && !empty($parts[1])) {
					try {

                        $rememberData = Database::getInstance()->conn->fetchAssoc(
                            "SELECT a.user, a.token,a.expires,b.email,b.username,b.status,b.roles_mask,b.force_logout FROM {$_TABLES['users_remembered']} AS a JOIN {$_TABLES['users']} AS b ON a.user=b.uid WHERE a.selector = ?",
                            array($parts[0]),
                            array(Database::STRING)
                        );
					}
                    catch(\Throwable $e) {
                        throw new Exceptions\DatabaseError($e->getMessage());
					}

					if (!empty($rememberData)) {
						if ($rememberData['expires'] >= \time()) {
                            if (\password_verify($parts[1], $rememberData['token'])) {
								// the cookie and its contents have now been proven to be valid
								$valid = true;
								$this->onLoginSuccessful($rememberData['user'], $rememberData['email'], $rememberData['username'], $rememberData['status'], $rememberData['roles_mask'], $rememberData['force_logout'], true);
							}
						}
					}
				}

				// if the cookie or its contents have been invalid
				if (!$valid) {
					// mark the cookie as such to prevent any further futile attempts
					$this->setRememberCookie('', '', \time() + 60 * 60 * 24 * 365.25);
				}
			}
		}
	}

	private function resyncSessionIfNecessary() {
        global $_TABLES;
		// if the user is signed in
		if ($this->isLoggedIn()) {
			// the following session field may not have been initialized for sessions that had already existed before the introduction of this feature
			if (!isset($_SESSION[self::SESSION_FIELD_LAST_RESYNC])) {
				$_SESSION[self::SESSION_FIELD_LAST_RESYNC] = 0;
			}

			// if it's time for resynchronization
			if (($_SESSION[self::SESSION_FIELD_LAST_RESYNC] + $this->sessionResyncInterval) <= \time()) {
				Log::write('system',Log::DEBUG,'UserAuth() :: Resyncing Session Data');
				// fetch the authoritative data from the database again
				try {

                    $authoritativeData = Database::getInstance()->conn->fetchAssoc(
                        "SELECT email, username, status, roles_mask, force_logout FROM {$_TABLES['users']} WHERE uid = ?",
                        array($this->getUserId()),
                        array(Database::INTEGER)
                    );
				}
				catch (\Throwable $e) {
					throw new Exceptions\DatabaseError($e->getMessage());
				}

				// if the user's data has been found
				if (!empty($authoritativeData)) {
					// the following session field may not have been initialized for sessions that had already existed before the introduction of this feature
					if (!isset($_SESSION[self::SESSION_FIELD_FORCE_LOGOUT])) {
						$_SESSION[self::SESSION_FIELD_FORCE_LOGOUT] = 0;
					}

					// if the counter that keeps track of forced logouts has been incremented
					if ($authoritativeData['force_logout'] > $_SESSION[self::SESSION_FIELD_FORCE_LOGOUT]) {
						// the user must be signed out
						$this->logOut();
					}
					// if the counter that keeps track of forced logouts has remained unchanged
					else {
						// the session data needs to be updated
						$_SESSION[self::SESSION_FIELD_EMAIL] = $authoritativeData['email'];
						$_SESSION[self::SESSION_FIELD_USERNAME] = $authoritativeData['username'];
						$_SESSION[self::SESSION_FIELD_STATUS] = (int) $authoritativeData['status'];
						$_SESSION[self::SESSION_FIELD_ROLES] = (int) $authoritativeData['roles_mask'];

						// remember that we've just performed the required resynchronization
						$_SESSION[self::SESSION_FIELD_LAST_RESYNC] = \time();
					}
				}
				// if no data has been found for the user
				else {
					// their account may have been deleted so they should be signed out
					$this->logOut();
				}
			}
		}
	}

	/**
	 * Attempts to sign in a user with their email address and password
	 *
	 * @param string $email the user's email address
	 * @param string $password the user's password
	 * @param int|null $rememberDuration (optional) the duration in seconds to keep the user logged in ("remember me"), e.g. `60 * 60 * 24 * 365.25` for one year
	 * @param callable|null $onBeforeSuccess (optional) a function that receives the user's ID as its single parameter and is executed before successful authentication; must return `true` to proceed or `false` to cancel
	 * @throws InvalidEmailException if the email address was invalid or could not be found
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws EmailNotVerifiedException if the email address has not been verified yet via confirmation email
	 * @throws AttemptCancelledException if the attempt has been cancelled by the supplied callback that is executed before success
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function login($email, $password, $rememberDuration = null, callable $onBeforeSuccess = null) {
		$this->throttle([ 'attemptToLogin', 'email', $email ], 500, (60 * 60 * 24), null, true);

		$this->authenticateUserInternal($password, $email, null, $rememberDuration, $onBeforeSuccess);
	}

	/**
	 * Attempts to sign in a user with their username and password
	 *
	 * When using this method to authenticate users, you should ensure that usernames are unique
	 *
	 * Consistently using {@see registerWithUniqueUsername} instead of {@see register} can be helpful
	 *
	 * @param string $username the user's username
	 * @param string $password the user's password
	 * @param int|null $rememberDuration (optional) the duration in seconds to keep the user logged in ("remember me"), e.g. `60 * 60 * 24 * 365.25` for one year
	 * @param callable|null $onBeforeSuccess (optional) a function that receives the user's ID as its single parameter and is executed before successful authentication; must return `true` to proceed or `false` to cancel
	 * @throws UnknownUsernameException if the specified username does not exist
	 * @throws AmbiguousUsernameException if the specified username is ambiguous, i.e. there are multiple users with that name
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws EmailNotVerifiedException if the email address has not been verified yet via confirmation email
	 * @throws AttemptCancelledException if the attempt has been cancelled by the supplied callback that is executed before success
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function loginWithUsername($username, $password, $rememberDuration = null, callable $onBeforeSuccess = null) {
		$this->throttle([ 'attemptToLogin', 'username', $username ], 500, (60 * 60 * 24), null, true);

		$this->authenticateUserInternal($password, null, $username, $rememberDuration, $onBeforeSuccess);
	}

	public function finalizeLogin($uid)
	{
		global $_TABLES;

		if ($uid > 1) {
			try {
				$userData = Database::getInstance()->conn->fetchAssoc(
					"SELECT uid,email,passwd,cookietimeout,verified,username,status,roles_mask,force_logout FROM " . $_TABLES['users'] . " WHERE uid = ?",
					[ $uid ],
					[ Database::INTEGER ]
				);
			}
			catch (\Throwable $e) {
				throw new Exceptions\DatabaseError($e->getMessage());
			}

			if (empty($userData)) {
				throw new Exceptions\UnknownIdException();
			}

			if ((int) $userData['verified'] === 1 && ($userData['status'] == Status::NORMAL || $userData['status'] == 1)) {
				$this->onLoginSuccessful($userData['uid'], $userData['email'], $userData['username'], $userData['status'], $userData['roles_mask'], $userData['force_logout'], false);

				$rememberDuration = $userData['cookietimeout'];

				// continue to support the old parameter format
				if ($rememberDuration === true) {
					$rememberDuration = 60 * 60 * 24 * 28;
				}
				elseif ($rememberDuration === false) {
					$rememberDuration = null;
				}

				if ($rememberDuration !== null) {
					$this->createRememberDirective($userData['uid'], $rememberDuration);
				}

				return;
			}
			else {
				if ($userData['verified'] != 1) {
					throw new Exceptions\EmailNotVerifiedException();
				}
                switch ($userData['status']) {
					case Status::ARCHIVED :
					case Status::LOCKED :
					case Status::SUSPENDED :
					case Status::BANNED :
                        throw new Exceptions\UnknownIdException();
                        break;
                    case Status::PENDING_REVIEW :
						throw new Exceptions\AccountPendingReviewException();
                        break;
                    default :
                        throw new Exceptions\AuthError();
                        break;
                }
				throw new Exceptions\AuthError();
			}
		}
		// anonymous user
		else {
			throw new Exceptions\NotLoggedInException();
		}
	}

	/**
	 * Attempts to confirm the currently signed-in user's password again
	 *
	 * Whenever you want to confirm the user's identity again, e.g. before
	 * the user is allowed to perform some "dangerous" action, you should
	 * use this method to confirm that the user is who they claim to be.
	 *
	 * For example, when a user has been remembered by a long-lived cookie
	 * and thus {@see isRemembered} returns `true`, this means that the
	 * user has not entered their password for quite some time anymore.
	 *
	 * @param string $password the user's password
	 * @return bool whether the supplied password has been correct
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function reconfirmPassword($password) {
        global $_TABLES;

		if ($this->isLoggedIn()) {
			try {
				$password = self::validatePassword($password);
			}
			catch (Exceptions\InvalidPasswordException $e) {
				return false;
			}

			$this->throttle([ 'reconfirmPassword', $this->getIpAddress() ], 3, (60 * 60), 4, true);

			$uid = $this->getUserId();

			try {
                $expectedHash = Database::getInstance()->conn->fetchColumn(
                    "SELECT passwd FROM {$_TABLES['users']} WHERE uid = ?",
                    array($uid),
                    0
                );
			}
			catch (\Throwable $e) {
				throw new Exceptions\DatabaseError($e->getMessage());
			}

			if (!empty($expectedHash)) {
				$validated = _check_hash($password, $expectedHash);

				if (!$validated) {
					$this->throttle([ 'reconfirmPassword', $this->getIpAddress() ], 3, (60 * 60), 4, false);
				} else {
					Session::set(self::SESSION_FIELD_REMEMBERED,false);
				}

				return $validated;
			}
			else {
				throw new Exceptions\NotLoggedInException();
			}
		}
		else {
			throw new Exceptions\NotLoggedInException();
		}
	}

	/**
	 * Logs the user out
	 *
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function logOut() {
		// if the user has been signed in
		if ($this->isLoggedIn()) {
			// retrieve any locally existing remember directive
			$rememberDirectiveSelector = $this->getRememberDirectiveSelector();

			// if such a remember directive exists
			if (isset($rememberDirectiveSelector)) {
				// delete the local remember directive
				$this->deleteRememberDirectiveForUserById(
					$this->getUserId(),
					$rememberDirectiveSelector
				);
			}

			// remove all session variables maintained by this library
			unset($_SESSION[self::SESSION_FIELD_LOGGED_IN]);
			unset($_SESSION[self::SESSION_FIELD_USER_ID]);
			unset($_SESSION[self::SESSION_FIELD_EMAIL]);
			unset($_SESSION[self::SESSION_FIELD_USERNAME]);
			unset($_SESSION[self::SESSION_FIELD_STATUS]);
			unset($_SESSION[self::SESSION_FIELD_ROLES]);
			unset($_SESSION[self::SESSION_FIELD_REMEMBERED]);
			unset($_SESSION[self::SESSION_FIELD_LAST_RESYNC]);
			unset($_SESSION[self::SESSION_FIELD_FORCE_LOGOUT]);
			unset($_SESSION[self::SESSION_FIELD_ADMIN_SESSION]);
		}
	}

	/**
	 * Logs the user out in all other sessions (except for the current one)
	 *
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function logOutEverywhereElse() {
		if (!$this->isLoggedIn()) {
			throw new Exceptions\NotLoggedInException();
		}

		// determine the expiry date of any locally existing remember directive
		$previousRememberDirectiveExpiry = $this->getRememberDirectiveExpiry();

		// schedule a forced logout in all sessions
		$this->forceLogoutForUserById($this->getUserId());

		// the following session field may not have been initialized for sessions that had already existed before the introduction of this feature
		if (!isset($_SESSION[self::SESSION_FIELD_FORCE_LOGOUT])) {
			$_SESSION[self::SESSION_FIELD_FORCE_LOGOUT] = 0;
		}

		// ensure that we will simply skip or ignore the next forced logout (which we have just caused) in the current session
		$_SESSION[self::SESSION_FIELD_FORCE_LOGOUT]++;

		// re-generate the session ID to prevent session fixation attacks (requests a cookie to be written on the client)
		Session::regenerate(true);

		// if there had been an existing remember directive previously
		if (isset($previousRememberDirectiveExpiry)) {
			// restore the directive with the old expiry date but new credentials
			$this->createRememberDirective(
				$this->getUserId(),
				$previousRememberDirectiveExpiry - \time()
			);
		}
	}

	/**
	 * Logs the user out in all sessions
	 *
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function logOutEverywhere() {
		if (!$this->isLoggedIn()) {
			throw new Exceptions\NotLoggedInException();
		}

		// schedule a forced logout in all sessions
		$this->forceLogoutForUserById($this->getUserId());
		// and immediately apply the logout locally
		$this->logOut();
	}

	/**
	 * Destroys all session data
	 *
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function destroySession() {
		// remove all session variables without exception
		$_SESSION = [];
		// delete the session cookie
		$this->deleteSessionCookie();
		// let PHP destroy the session
		\session_destroy();
	}

	/**
	 * Creates a new directive keeping the user logged in ("remember me")
	 *
	 * @param int $userId the user ID to keep signed in
	 * @param int $duration the duration in seconds
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function createRememberDirective($userId, $duration) {
        global $_TABLES;
		$selector = self::createRandomString(24);
		$token = self::createRandomString(32);
		$tokenHashed = \password_hash($token, \PASSWORD_DEFAULT);
		$expires = \time() + ((int) $duration);

		try {
            Database::getInstance()->conn->insert(
                $_TABLES['users_remembered'],
                array(
					'user' => $userId,
					'selector' => $selector,
					'token' => $tokenHashed,
					'expires' => $expires
                ),
                array(
                    Database::INTEGER,
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER
                )
            );
		}
		catch (\Throwable $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		$this->setRememberCookie($selector, $token, $expires);
	}

	protected function deleteRememberDirectiveForUserById($userId, $selector = null) {
		parent::deleteRememberDirectiveForUserById($userId, $selector);

		$this->setRememberCookie(null, null, \time() - 3600);
	}

	/**
	 * Sets or updates the cookie that manages the "remember me" token
	 *
	 * @param string|null $selector the selector from the selector/token pair
	 * @param string|null $token the token from the selector/token pair
	 * @param int $expires the UNIX time in seconds which the token should expire at
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function setRememberCookie($selector, $token, $expires) {
		$params = \session_get_cookie_params();

		if (isset($selector) && isset($token)) {
			$content = $selector . self::COOKIE_CONTENT_SEPARATOR . $token;
		}
		else {
			$content = '';
		}

		// save the cookie with the selector and token (requests a cookie to be written on the client)
		$cookie = new Cookie($this->rememberCookieName);
		$cookie->setValue($content);
		$cookie->setExpiryTime($expires);
		$cookie->setPath($params['path']);
		$cookie->setDomain($params['domain']);
		$cookie->setHttpOnly($params['httponly']);
		$cookie->setSecureOnly($params['secure']);
		$result = $cookie->save();

		if ($result === false) {
			throw new Exceptions\HeadersAlreadySentError();
		}

		// if we've been deleting the cookie above
		if (!isset($selector) || !isset($token)) {
			// attempt to delete a potential old cookie from versions v1.x.x to v6.x.x as well (requests a cookie to be written on the client)
			$cookie = new Cookie('auth_remember');
			$cookie->setPath((!empty($params['path'])) ? $params['path'] : '/');
			$cookie->setDomain($params['domain']);
			$cookie->setHttpOnly($params['httponly']);
			$cookie->setSecureOnly($params['secure']);
			$cookie->delete();
		}
	}

	protected function onLoginSuccessful($userId, $email, $username, $status, $roles, $forceLogout, $remembered) {
        global $_CONF, $_TABLES;
		// update the timestamp of the user's last login
		try {
            Database::getInstance()->conn->update(
                $_TABLES['userinfo'],
                array(
                    'lastlogin' => $_CONF['_now']->toUnix(true),
                    'uid' => $userId
                ),
                array(
                    Database::INTEGER,
                    Database::INTEGER
                )
            );
		}
		catch (\Throwable $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		parent::onLoginSuccessful($userId, $email, $username, $status, $roles, $forceLogout, $remembered);
	}

	/**
	 * Deletes the session cookie on the client
	 *
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function deleteSessionCookie() {
		$params = \session_get_cookie_params();

		// ask for the session cookie to be deleted (requests a cookie to be written on the client)
		$cookie = new Cookie(\session_name());
		$cookie->setPath($params['path']);
		$cookie->setDomain($params['domain']);
		$cookie->setHttpOnly($params['httponly']);
		$cookie->setSecureOnly($params['secure']);
		$result = $cookie->delete();

		if ($result === false) {
			throw new Exceptions\HeadersAlreadySentError();
		}
	}


//@TODO - move to userCreate?
	/**
	 * Confirms an email address (and activates the account) by supplying the correct selector/token pair
	 *
	 * The selector/token pair must have been generated previously by registering a new account
	 *
	 * @param string $selector the selector from the selector/token pair
	 * @param string $token the token from the selector/token pair
	 * @return string[] an array with the old email address (if any) at index zero and the new email address (which has just been verified) at index one
	 * @throws InvalidSelectorTokenPairException if either the selector or the token was not correct
	 * @throws TokenExpiredException if the token has already expired
	 * @throws UserAlreadyExistsException if an attempt has been made to change the email address to a (now) occupied address
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function confirmEmail($selector, $token) {
        global $_TABLES;
        $this->throttle([ 'confirmEmail', $this->getIpAddress() ], 5, (60 * 60), 10);
		$this->throttle([ 'confirmEmail', 'selector', $selector ], 3, (60 * 60), 10);
		$this->throttle([ 'confirmEmail', 'token', $token ], 3, (60 * 60), 10);

		try {
            $confirmationData = Database::getInstance()->conn->fetchAssoc(
				"SELECT a.id, a.user_id, a.email AS new_email, a.token, a.expires, b.email AS old_email FROM {$_TABLES['users_confirmations']} AS a JOIN {$_TABLES['users']} AS b ON b.uid = a.user_id WHERE a.selector = ?",
    			[ $selector ],
                [ DATABASE::STRING ]
            );
		}
		catch (\Throwable $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		if (!empty($confirmationData)) {
			if (\password_verify($token, $confirmationData['token'])) {
				if ($confirmationData['expires'] >= \time()) {
					// invalidate any potential outstanding password reset requests
					try {
                        Database::getInstance()->conn->delete(
                            $_TABLES['users_resets'],
                            [ 'user' => $confirmationData['user_id'] ],
                            [ Database::INTEGER ]
                        );
					}
					catch (\Throwable $e) {
						throw new Exceptions\DatabaseError($e->getMessage());
					}

					// mark the email address as verified (and possibly update it to the new address given)
					try {
                        Database::getInstance()->conn->update(
                            $_TABLES['users'],
							[
								'email' => $confirmationData['new_email'],
								'verified' => 1
							],
							[ 'uid' => $confirmationData['user_id'] ],
                            [ Database::STRING, Database::INTEGER]
                        );
					}
                    catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
						throw new Exceptions\DuplicateEmailException();
					}
					catch (\Throwable $e) {
						throw new Exceptions\DatabaseError($e->getMessage());
					}

					// if the user is currently signed in
					if ($this->isLoggedIn()) {
						// if the user has just confirmed an email address for their own account
						if ($this->getUserId() === $confirmationData['user_id']) {
							// immediately update the email address in the current session as well
							$_SESSION[self::SESSION_FIELD_EMAIL] = $confirmationData['new_email'];
						}
					}

					// consume the token just being used for confirmation
					try {
						Database::getInstance()->conn->delete(
							$_TABLES['users_confirmations'],
							[ 'id' => $confirmationData['id'] ],
							[ Database::INTEGER]
						);
					}
					catch (\Error $e) {
						throw new Exceptions\DatabaseError($e->getMessage());
					}

					// if the email address has not been changed but simply been verified
					if ($confirmationData['old_email'] === $confirmationData['new_email']) {
						// the output should not contain any previous email address
						$confirmationData['old_email'] = null;
					}

					return [
						$confirmationData['old_email'],
						$confirmationData['new_email']
					];
				}
				else {
					throw new Exceptions\TokenExpiredException();
				}
			}
			else {
				throw new Exceptions\InvalidSelectorTokenPairException();
			}
		}
		else {
			throw new Exceptions\InvalidSelectorTokenPairException();
		}
	}

//@TODO - move to userCreate?
	/**
	 * Confirms an email address and activates the account by supplying the correct selector/token pair
	 *
	 * The selector/token pair must have been generated previously by registering a new account
	 *
	 * The user will be automatically signed in if this operation is successful
	 *
	 * @param string $selector the selector from the selector/token pair
	 * @param string $token the token from the selector/token pair
	 * @param int|null $rememberDuration (optional) the duration in seconds to keep the user logged in ("remember me"), e.g. `60 * 60 * 24 * 365.25` for one year
	 * @return string[] an array with the old email address (if any) at index zero and the new email address (which has just been verified) at index one
	 * @throws InvalidSelectorTokenPairException if either the selector or the token was not correct
	 * @throws TokenExpiredException if the token has already expired
	 * @throws UserAlreadyExistsException if an attempt has been made to change the email address to a (now) occupied address
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function confirmEmailAndSignIn($selector, $token, $rememberDuration = null) {
		$emailBeforeAndAfter = $this->confirmEmail($selector, $token);

		if (!$this->isLoggedIn()) {
			if ($emailBeforeAndAfter[1] !== null) {
				$emailBeforeAndAfter[1] = self::validateEmailAddress($emailBeforeAndAfter[1]);

				$userData = $this->getUserDataByEmailAddress(
					$emailBeforeAndAfter[1],
					[ 'uid', 'email', 'username', 'status', 'roles_mask', 'force_logout' ]
				);

				$this->onLoginSuccessful($userData['uid'], $userData['email'], $userData['username'], $userData['status'], $userData['roles_mask'], $userData['force_logout'], true);

				if ($rememberDuration !== null) {
					$this->createRememberDirective($userData['uid'], $rememberDuration);
				}
			}
		}

		return $emailBeforeAndAfter;
	}

	/**
	 * Changes the currently signed-in user's password while requiring the old password for verification
	 *
	 * @param string $oldPassword the old password to verify account ownership
	 * @param string $newPassword the new password that should be set
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws InvalidPasswordException if either the old password has been wrong or the desired new one has been invalid
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function changePassword($oldPassword, $newPassword) {
		if ($this->reconfirmPassword($oldPassword)) {
			$this->changePasswordWithoutOldPassword($newPassword);
		}
		else {
			throw new Exceptions\InvalidPasswordException();
		}
	}

	/**
	 * Changes the currently signed-in user's password without requiring the old password for verification
	 *
	 * @param string $newPassword the new password that should be set
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws InvalidPasswordException if the desired new password has been invalid
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function changePasswordWithoutOldPassword($newPassword) {
		if ($this->isLoggedIn()) {
			$newPassword = self::validatePassword($newPassword);
			$this->updatePasswordInternal($this->getUserId(), $newPassword);

			try {
				$this->logOutEverywhereElse();
			}
			catch (Exceptions\NotLoggedInException $ignored) {}
		}
		else {
			throw new Exceptions\NotLoggedInException();
		}
	}

	/**
	 * Attempts to change the email address of the currently signed-in user (which requires confirmation)
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
	 *
	 * @param string $newEmail the desired new email address
	 * @param callable $callback the function that sends the confirmation email to the user
	 * @throws InvalidEmailException if the desired new email address is invalid
	 * @throws UserAlreadyExistsException if a user with the desired new email address already exists
	 * @throws EmailNotVerifiedException if the current (old) email address has not been verified yet
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see confirmEmail
	 * @see confirmEmailAndSignIn
	 */
	public function changeEmail($newEmail, callable $callback) {
		if ($this->isLoggedIn()) {
			$newEmail = self::validateEmailAddress($newEmail);

			$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);

			try {
				$existingUsersWithNewEmail = $this->db->selectValue(
					'SELECT COUNT(*) FROM ' . $this->makeTableName('users') . ' WHERE email = ?',
					[ $newEmail ]
				);
			}
			catch (\Error $e) {
				throw new Exceptions\DatabaseError($e->getMessage());
			}

			if ((int) $existingUsersWithNewEmail !== 0) {
				throw new Exceptions\UserAlreadyExistsException();
			}

			try {
				$verified = $this->db->selectValue(
					'SELECT verified FROM ' . $this->makeTableName('users') . ' WHERE id = ?',
					[ $this->getUserId() ]
				);
			}
			catch (\Error $e) {
				throw new Exceptions\DatabaseError($e->getMessage());
			}

			// ensure that at least the current (old) email address has been verified before proceeding
			if ((int) $verified !== 1) {
				throw new Exceptions\EmailNotVerifiedException();
			}

			$this->throttle([ 'requestEmailChange', 'userId', $this->getUserId() ], 1, (60 * 60 * 24));
			$this->throttle([ 'requestEmailChange', $this->getIpAddress() ], 1, (60 * 60 * 24), 3);

			$this->createConfirmationRequest($this->getUserId(), $newEmail, $callback);
		}
		else {
			throw new Exceptions\NotLoggedInException();
		}
	}

	/**
	 * Attempts to re-send an earlier confirmation request for the user with the specified email address
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
	 *
	 * @param string $email the email address of the user to re-send the confirmation request for
	 * @param callable $callback the function that sends the confirmation request to the user
	 * @throws ConfirmationRequestNotFound if no previous request has been found that could be re-sent
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 */
	public function resendConfirmationForEmail($email, callable $callback) {
		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);

		$this->resendConfirmationForColumnValue('email', $email, $callback);
	}

	/**
	 * Attempts to re-send an earlier confirmation request for the user with the specified ID
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
	 *
	 * @param int $userId the ID of the user to re-send the confirmation request for
	 * @param callable $callback the function that sends the confirmation request to the user
	 * @throws ConfirmationRequestNotFound if no previous request has been found that could be re-sent
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 */
	public function resendConfirmationForUserId($userId, callable $callback) {
		$this->resendConfirmationForColumnValue('user_id', $userId, $callback);
	}

	/**
	 * Attempts to re-send an earlier confirmation request
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to verify their email address as a next step, both pieces will be required again
	 *
	 * You must never pass untrusted input to the parameter that takes the column name
	 *
	 * @param string $columnName the name of the column to filter by
	 * @param mixed $columnValue the value to look for in the selected column
	 * @param callable $callback the function that sends the confirmation request to the user
	 * @throws ConfirmationRequestNotFound if no previous request has been found that could be re-sent
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function resendConfirmationForColumnValue($columnName, $columnValue, callable $callback) {
		try {
			$latestAttempt = $this->db->selectRow(
				'SELECT uid, email FROM ' . $this->makeTableName('users_confirmations') . ' WHERE ' . $columnName . ' = ? ORDER BY id DESC LIMIT 1 OFFSET 0',
				[ $columnValue ]
			);
		}
		catch (\Error $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		if ($latestAttempt === null) {
			throw new Exceptions\ConfirmationRequestNotFound();
		}

		$this->throttle([ 'resendConfirmation', 'userId', $latestAttempt['user_id'] ], 1, (60 * 60 * 6));
		$this->throttle([ 'resendConfirmation', $this->getIpAddress() ], 4, (60 * 60 * 24 * 7), 2);

		$this->createConfirmationRequest(
			$latestAttempt['user_id'],
			$latestAttempt['email'],
			$callback
		);
	}

	/**
	 * Initiates a password reset request for the user with the specified email address
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to proceed to the second step of the password reset, both pieces will be required again
	 *
	 * @param string $email the email address of the user who wants to request the password reset
	 * @param callable $callback the function that sends the password reset information to the user
	 * @param int|null $requestExpiresAfter (optional) the interval in seconds after which the request should expire
	 * @param int|null $maxOpenRequests (optional) the maximum number of unexpired and unused requests per user
	 * @throws InvalidEmailException if the email address was invalid or could not be found
	 * @throws EmailNotVerifiedException if the email address has not been verified yet via confirmation email
	 * @throws ResetDisabledException if the user has explicitly disabled password resets for their account
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see canResetPasswordOrThrow
	 * @see canResetPassword
	 * @see resetPassword
	 * @see resetPasswordAndSignIn
	 */
	public function forgotPassword($email, callable $callback, $requestExpiresAfter = null, $maxOpenRequests = null) {
		$email = self::validateEmailAddress($email);

		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);

		if ($requestExpiresAfter === null) {
			// use six hours as the default
			$requestExpiresAfter = 60 * 60 * 6;
		}
		else {
			$requestExpiresAfter = (int) $requestExpiresAfter;
		}

		if ($maxOpenRequests === null) {
			// use two requests per user as the default
			$maxOpenRequests = 2;
		}
		else {
			$maxOpenRequests = (int) $maxOpenRequests;
		}

		$userData = $this->getUserDataByEmailAddress(
			$email,
			[ 'id', 'verified', 'resettable' ]
		);

		// ensure that the account has been verified before initiating a password reset
		if ((int) $userData['verified'] !== 1) {
			throw new Exceptions\EmailNotVerifiedException();
		}

		// do not allow a password reset if the user has explicitly disabled this feature
		if ((int) $userData['resettable'] !== 1) {
			throw new Exceptions\ResetDisabledException();
		}

		$openRequests = $this->throttling ? (int) $this->getOpenPasswordResetRequests($userData['id']) : 0;

		if ($openRequests < $maxOpenRequests) {
			$this->throttle([ 'requestPasswordReset', $this->getIpAddress() ], 4, (60 * 60 * 24 * 7), 2);
			$this->throttle([ 'requestPasswordReset', 'user', $userData['id'] ], 4, (60 * 60 * 24 * 7), 2);

			$this->createPasswordResetRequest($userData['id'], $requestExpiresAfter, $callback);
		}
		else {
			throw new Exceptions\TooManyRequestsException('', $requestExpiresAfter);
		}
	}

	/**
	 * Authenticates an existing user
	 *
	 * @param string $password the user's password
	 * @param string|null $email (optional) the user's email address
	 * @param string|null $username (optional) the user's username
	 * @param int|null $rememberDuration (optional) the duration in seconds to keep the user logged in ("remember me"), e.g. `60 * 60 * 24 * 365.25` for one year
	 * @param callable|null $onBeforeSuccess (optional) a function that receives the user's ID as its single parameter and is executed before successful authentication; must return `true` to proceed or `false` to cancel
	 * @throws InvalidEmailException if the email address was invalid or could not be found
	 * @throws UnknownUsernameException if an attempt has been made to authenticate with a non-existing username
	 * @throws AmbiguousUsernameException if an attempt has been made to authenticate with an ambiguous username
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws EmailNotVerifiedException if the email address has not been verified yet via confirmation email
	 * @throws AttemptCancelledException if the attempt has been cancelled by the supplied callback that is executed before success
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function authenticateUserInternal($password, $email = null, $username = null, $rememberDuration = null, callable $onBeforeSuccess = null) {
		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);
		$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 60), 5, true);

		$columnsToFetch = [ 'uid', 'email', 'passwd', 'cookietimeout','verified', 'username', 'status', 'roles_mask', 'force_logout' ];

		if ($email !== null) {
			$email = self::validateEmailAddress($email);

			// attempt to look up the account information using the specified email address
			$userData = $this->getUserDataByEmailAddress(
				$email,
				$columnsToFetch
			);
		}
		elseif ($username !== null) {
			$username = \trim($username);

			// attempt to look up the account information using the specified username
			$userData = $this->getUserDataByUsername(
				$username,
				$columnsToFetch
			);
		}
		// if neither an email address nor a username has been provided
		else {
			// we can't do anything here because the method call has been invalid
			throw new Exceptions\EmailOrUsernameRequiredError();
		}
        if ($userData === false) {
			$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 5), null, false);

			if (isset($email)) {
				$this->throttle([ 'attemptToLogin', 'email', $email ], 500, (60 * 60 * 24), null, false);
			}
			elseif (isset($username)) {
				$this->throttle([ 'attemptToLogin', 'username', $username ], 500, (60 * 60 * 24), null, false);
			}
			throw new Exceptions\UnknownUsernameException();
        }

		$password = self::validatePassword($password);

		if (_check_hash($password, $userData['passwd'])) {

			// if the password needs to be re-hashed to keep up with improving password cracking techniques
//			if (\password_needs_rehash($userData['password'], \PASSWORD_DEFAULT)) {
//				// create a new hash from the password and update it in the database
//				$this->updatePasswordInternal($userData['id'], $password);
//			}
			if ((int) $userData['verified'] === 1 && ($userData['status'] == Status::NORMAL || $userData['status'] == 1)) {
				if (!isset($onBeforeSuccess) || (\is_callable($onBeforeSuccess) && $onBeforeSuccess($userData['uid']) === true)) {
					$this->onLoginSuccessful($userData['uid'], $userData['email'], $userData['username'], $userData['status'], $userData['roles_mask'], $userData['force_logout'], false);

					$rememberDuration = $userData['cookietimeout'];

					// continue to support the old parameter format
					if ($rememberDuration === true) {
						$rememberDuration = 60 * 60 * 24 * 28;
					}
					elseif ($rememberDuration === false) {
						$rememberDuration = null;
					}

					if ($rememberDuration !== null) {
						$this->createRememberDirective($userData['uid'], $rememberDuration);
					}

					return;
				}
                // this could happen if PLG_preItemSave() failed
				else {
					$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 5), null, false);

					if (isset($email)) {
						$this->throttle([ 'attemptToLogin', 'email', $email ], 500, (60 * 60 * 24), null, false);
					}
					elseif (isset($username)) {
						$this->throttle([ 'attemptToLogin', 'username', $username ], 500, (60 * 60 * 24), null, false);
					}

					throw new Exceptions\AttemptCancelledException();
				}
			}
			else {
				if ($userData['verified'] != 1) {
					throw new Exceptions\EmailNotVerifiedException();
				}
                switch ($userData['status']) {
					case Status::ARCHIVED :
					case Status::LOCKED :
					case Status::SUSPENDED :
					case Status::BANNED :
                        throw new Exceptions\UnknownIdException();
                        break;
                    case Status::PENDING_REVIEW :
						throw new Exceptions\AccountPendingReviewException();
                        break;
                    default :
                        throw new Exceptions\AuthError();
                        break;
                }
				throw new Exceptions\AuthError();
			}
		}
		else {
			$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 5), null, false);

			if (isset($email)) {
				$this->throttle([ 'attemptToLogin', 'email', $email ], 500, (60 * 60 * 24), null, false);
			}
			elseif (isset($username)) {
				$this->throttle([ 'attemptToLogin', 'username', $username ], 500, (60 * 60 * 24), null, false);
			}

			// we cannot authenticate the user due to the password being wrong
			throw new Exceptions\InvalidPasswordException();
		}
	}


	/**
	 * Authenticates Two Factor Authentication for user
	 *
	 * @param int $uid the user id for the user
	 * @throws InvalidEmailException if the email address was invalid or could not be found
	 * @throws UnknownUsernameException if an attempt has been made to authenticate with a non-existing username
	 * @throws AmbiguousUsernameException if an attempt has been made to authenticate with an ambiguous username
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws EmailNotVerifiedException if the email address has not been verified yet via confirmation email
	 * @throws AttemptCancelledException if the attempt has been cancelled by the supplied callback that is executed before success
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function authenticateUserTFA($uid) {
        global $_TABLES;

		$this->throttle([ 'enumerateUsers', $this->getIpAddress() ], 1, (60 * 60), 75);
		$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 60), 5, true);

		$columnsToFetch = [ 'uid', 'email', 'passwd', 'verified', 'username', 'cookietimeout','status', 'roles_mask', 'force_logout' ];

		if ($uid > 1) {
            try {
                $projection = \implode(', ', $columnsToFetch);
                $userData = Database::getInstance()->conn->fetchAssoc(
                    'SELECT ' . $projection . ' FROM ' . $_TABLES['users'] . ' WHERE uid = ?',
                    [ $uid ]
                );
            }
            catch (\Throwable $e) {
                throw new Exceptions\DatabaseError($e->getMessage());
            }
            $rememberDuration = $userData['cookietimeout'];
		}
		// if neither an email address nor a username has been provided
		else {
			// we can't do anything here because the method call has been invalid
			throw new Exceptions\EmailOrUsernameRequiredError();
		}
        if ($userData === false) {
			$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 5), null, false);

			if (isset($email)) {
				$this->throttle([ 'attemptToLogin', 'email', $email ], 500, (60 * 60 * 24), null, false);
			}
			elseif (isset($username)) {
				$this->throttle([ 'attemptToLogin', 'username', $username ], 500, (60 * 60 * 24), null, false);
			}

            throw new Exceptions\UnknownUsernameException();
        }
		if (validateTFA() == true) {
            if ((int) $userData['verified'] === 1 && $userData['status'] == 3) {
                $this->onLoginSuccessful($userData['uid'], $userData['email'], $userData['username'], $userData['status'], $userData['roles_mask'], $userData['force_logout'], false);

                // continue to support the old parameter format
                if ($rememberDuration === true) {
                    $rememberDuration = 60 * 60 * 24 * 28;
                }
                elseif ($rememberDuration === false) {
                    $rememberDuration = null;
                }

                if ($rememberDuration !== null && $rememberDuration != 0) {
                    $this->createRememberDirective($userData['uid'], $rememberDuration);
                }
                return;
			}
			else {
				throw new Exceptions\EmailNotVerifiedException();
			}
		} else {
			$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 60), 5, false);

			if (isset($email)) {
				$this->throttle([ 'attemptToLogin', 'email', $email ], 500, (60 * 60 * 24), null, false);
			}
			elseif (isset($username)) {
				$this->throttle([ 'attemptToLogin', 'username', $username ], 500, (60 * 60 * 24), null, false);
			}
			// we cannot authenticate the user due to the 2FA being wrong
			throw new Exceptions\TwoFactorVerificationException();
		}
	}

	/** hook function used by login */
	public function userLoginBeforeSuccess($uid)
	{
		global $_CONF, $_TABLES;

		// call the plugin presave
		$userInfo = Database::getInstance()->conn->fetchAssoc("SELECT username, tfa_enabled from {$_TABLES['users']} WHERE uid=?",[ $uid ], [ Database::INTEGER ] );
		if ($this->inOauth === false) {
			$msg = PLG_itemPreSave ('loginform', $userInfo['username']);
			if (!empty ($msg)) {
				COM_setMsg($msg,'error');
				return false;
			}
		}

		if ( isset($_CONF['enable_twofactor']) &&
				$_CONF['enable_twofactor'] &&
				isset($userInfo['tfa_enabled']) &&
				$userInfo['tfa_enabled'] && function_exists('hash_hmac')) {
			if ( !SESS_isSet('login_referer')) {
				if ( isset($_SERVER['HTTP_REFERER'])) {
					SESS_setVar('login_referer',$_SERVER['HTTP_REFERER']);
				}
			}
			Session::set('2fa_attempt',1);
			SEC_2FAForm($uid);
		}
		return true;
	}

	/**
	 * Authenticates a user using Oauth
	 *
	 * @param int|null $rememberDuration (optional) the duration in seconds to keep the user logged in ("remember me"), e.g. `60 * 60 * 24 * 365.25` for one year
	 * @param callable|null $onBeforeSuccess (optional) a function that receives the user's ID as its single parameter and is executed before successful authentication; must return `true` to proceed or `false` to cancel
	 * @throws InvalidEmailException if the email address was invalid or could not be found
	 * @throws UnknownUsernameException if an attempt has been made to authenticate with a non-existing username
	 * @throws AmbiguousUsernameException if an attempt has been made to authenticate with an ambiguous username
	 * @throws InvalidPasswordException if the password was invalid
	 * @throws EmailNotVerifiedException if the email address has not been verified yet via confirmation email
	 * @throws AttemptCancelledException if the attempt has been cancelled by the supplied callback that is executed before success
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function loginWithOauth($rememberDuration = null, callable $onBeforeSuccess = null) {
		Log::write('system',Log::DEBUG,'in loginWithOauth()');
		$this->throttle([ 'attemptToLogin', $this->getIpAddress() ], 4, (60 * 5), null, true);
		$this->authenticateOauthInternal($rememberDuration, $onBeforeSuccess);
	}


	private function authenticateOauthInternal($rememberDuration = null, callable $onBeforeSuccess = null) {
		global $_CONF, $_TABLES;

		// ensure oauth authentication is allowed
		if ($_CONF['user_login_method']['oauth'] != true) {
			return;
		}

		if (!isset($_GET['oauth_login'])) {
			Log::write('system',Log::DEBUG,'oauth_login not set - returning');
			return;
		}

		if (!isset($_SERVER['HTTP_REFERER'])) {
			Log::write('system',Log::DEBUG,'no referer - returning');
			return;
		}

		// we have determined we should be here

		if ( !Session::has('login_referer') && isset($_SERVER['HTTP_REFERER']) ) {
			if ( substr($_SERVER['HTTP_REFERER'], 0,strlen($_CONF['site_url'])) == $_CONF['site_url']) {
				Session::set('login_referer',$_SERVER['HTTP_REFERER']);
			} else {
				Session::set('login_referer', $_CONF['site_url']);
			}
		}

		$query = array_merge($_GET, $_POST);
		$service = preg_replace("/[^a-zA-Z0-9]+/", "", $query['oauth_login']);

		Log::write('system',Log::DEBUG,'Requested Oauth Service is ' . $service);

		Log::write('system',Log::DEBUG,'Collecting Oauth modules');
//@TODO - move this to this function
		$modules = SEC_collectRemoteOAuthModules();

		$active_service = (count($modules) == 0) ? false : in_array($service, $modules);
		if (!$active_service) {
			Log::write('system',Log::ERROR,"OAuth login failed - there was no consumer available for the service:" . $service);
			throw new Exceptions\InvalidOauthProviderException();
		}
		$consumer = new \glFusion\User\UserAuthOauth($service);

		$callback_url = $_CONF['site_url'] . '/users.php?oauth_login=' . $service;
		$consumer->setRedirectURL($callback_url);
		$oauth_userinfo = $consumer->authenticateUser();

		if ($oauth_userinfo !== false) {

			// fields for users table
//@TODO
			$users      = $consumer->getUserData($oauth_userinfo); // this pull info back from the oauth provider - in oauthconsumer.class
			// fields for userinfo table
//@TODO
			$userinfo   = $consumer->getUserInfoData($oauth_userinfo); // this pulls info back from the oauth provider - in oauthconsumer.class

			// is this an existing user?
			$userData = Database::getInstance()->conn->fetchAssoc(
						"SELECT uid,email,passwd,cookietimeout,verified,username,status,roles_mask,force_logout FROM `{$_TABLES['users']}`
							WHERE remoteusername = ?
								AND remoteservice = ?",
						array(
							$users['remoteusername'],
							$users['remoteservice']
						),
						array(
							Database::STRING,
							Database::STRING
						)
			);
			if ($userData !== false && $userData !== null) {
				Log::write('system',Log::DEBUG,'Oauth user found in glFusion user table: '. $userData['uid']);
				// existing user - so we probably need to resync the data
				// populate the $_USER record and do the final login
				if ((int) $userData['verified'] === 1 && ($userData['status'] == 3 || $userData['status'] == 1)) {
					$this->inOauth = true;
					if (!isset($onBeforeSuccess) || (\is_callable($onBeforeSuccess) && $onBeforeSuccess($userData['uid']) === true)) {
						$this->inOauth = false;
						$this->onLoginSuccessful($userData['uid'], $userData['email'], $userData['username'], $userData['status'], $userData['roles_mask'], $userData['force_logout'], false);

						$rememberDuration = $userData['cookietimeout'];

						// continue to support the old parameter format
						if ($rememberDuration === true) {
							$rememberDuration = 60 * 60 * 24 * 28;
						}
						elseif ($rememberDuration === false) {
							$rememberDuration = null;
						}

						if ($rememberDuration !== null && $rememberDuration != 0) {
							$this->createRememberDirective($userData['uid'], $rememberDuration);
						}
						$_SERVER['HTTP_REFERER'] = Session::take('login_referer', $_CONF['site_url']);
						return;
					}
					// this happens if onBeforeSuccess fails
					else {
						$this->inOauth = false;
						throw new Exceptions\AttemptCancelledException();
					}
				}
				// user is not verified or status is not active
				else {
					Log::write('system',Log::DEBUG,'User is not verified or status is not 3');
					throw new Exceptions\EmailNotVerifiedException();
				}
			}
			// first time the user has logged into glFusion via Oauth
			else {

//should we call userCreate() instead - no because this calls the user registration form and
// then hooks into userCreate().

				Log::write('system',Log::DEBUG,'Authenticated Oauth user was not found in local glFusion users table');
				// new user
				if (!isset($users['loginname']) || empty($users['loginname'])) {
					$users['loginname'] = 'RemoteUser';
				}
				$loginname = $users['loginname'];
				$checkName = Database::getInstance()->getItem($_TABLES['users'], 'username', array('username' => $loginname),array(Database::STRING));
				if (!empty($checkName)) {
					if (function_exists('CUSTOM_uniqueRemoteUsername')) {
						$loginname = CUSTOM_uniqueRemoteUsername($loginname);
					}
					if (strcasecmp($checkName,$loginname) === 0) {
						$loginname = USER_uniqueUsername($loginname);
					}
				}
				$users['loginname'] = $loginname;

				$userData = array(
					'regtype'           => 'oauth',
					'username'          => $loginname,
					'email'             => $users['email'],
					'fullname'          => $users['fullname'],
					'oauth_provider'    => strtolower($users['remoteservice']),
					'oauth_username'    => $users['remoteusername'],
					'oauth_email'       => $users['email'],
					'oauth_photo'       => $users['remotephoto'],
					'oauth_homepage'    => $users['homepage'],
					'oauth_service'     => $service,
				);
				$page = USER_registrationForm($userData);
				echo COM_siteHeader('menu') . $page . COM_siteFooter();
				exit;
			}
		} else {
			throw new Exceptions\OauthProviderException();
		}
	}



	/**
	 * Returns the requested user data for the account with the specified email address (if any)
	 *
	 * You must never pass untrusted input to the parameter that takes the column list
	 *
	 * @param string $email the email address to look for
	 * @param array $requestedColumns the columns to request from the user's record
	 * @return array the user data (if an account was found)
	 * @throws InvalidEmailException if the email address could not be found
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function getUserDataByEmailAddress($email, array $requestedColumns) {
		global $_TABLES;

		try {
			$projection = \implode(', ', $requestedColumns);
			$userData = Database::getInstance()->conn->fetchAssoc(
				'SELECT ' . $projection . ' FROM ' . $_TABLES['users'] . ' WHERE email = ?',
				[ $email ],
				[ DATABASE::STRING]
			);
		}
		catch (\Throwable $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		if (!empty($userData)) {
			return $userData;
		}
		else {
			throw new Exceptions\InvalidEmailException();
		}
	}

	/**
	 * Returns the number of open requests for a password reset by the specified user
	 *
	 * @param int $userId the ID of the user to check the requests for
	 * @return int the number of open requests for a password reset
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function getOpenPasswordResetRequests($userId) {
		try {
			$requests = $this->db->selectValue(
				'SELECT COUNT(*) FROM ' . $this->makeTableName('users_resets') . ' WHERE user = ? AND expires > ?',
				[
					$userId,
					\time()
				]
			);

			if (!empty($requests)) {
				return $requests;
			}
			else {
				return 0;
			}
		}
		catch (\Error $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}
	}

	/**
	 * Creates a new password reset request
	 *
	 * The callback function must have the following signature:
	 *
	 * `function ($selector, $token)`
	 *
	 * Both pieces of information must be sent to the user, usually embedded in a link
	 *
	 * When the user wants to proceed to the second step of the password reset, both pieces will be required again
	 *
	 * @param int $userId the ID of the user who requested the reset
	 * @param int $expiresAfter the interval in seconds after which the request should expire
	 * @param callable $callback the function that sends the password reset information to the user
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	private function createPasswordResetRequest($userId, $expiresAfter, callable $callback) {
		$selector = self::createRandomString(20);
		$token = self::createRandomString(20);
		$tokenHashed = \password_hash($token, \PASSWORD_DEFAULT);
		$expiresAt = \time() + $expiresAfter;

		try {
			$this->db->insert(
				$this->makeTableNameComponents('users_resets'),
				[
					'user' => $userId,
					'selector' => $selector,
					'token' => $tokenHashed,
					'expires' => $expiresAt
				]
			);
		}
		catch (\Error $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		if (\is_callable($callback)) {
			$callback($selector, $token);
		}
		else {
			throw new Exceptions\MissingCallbackError();
		}
	}

	/**
	 * Resets the password for a particular account by supplying the correct selector/token pair
	 *
	 * The selector/token pair must have been generated previously by calling {@see forgotPassword}
	 *
	 * @param string $selector the selector from the selector/token pair
	 * @param string $token the token from the selector/token pair
	 * @param string $newPassword the new password to set for the account
	 * @return string[] an array with the user's ID at index `id` and the user's email address at index `email`
	 * @throws InvalidSelectorTokenPairException if either the selector or the token was not correct
	 * @throws TokenExpiredException if the token has already expired
	 * @throws ResetDisabledException if the user has explicitly disabled password resets for their account
	 * @throws InvalidPasswordException if the new password was invalid
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see forgotPassword
	 * @see canResetPasswordOrThrow
	 * @see canResetPassword
	 * @see resetPasswordAndSignIn
	 */
	public function resetPassword($selector, $token, $newPassword) {
		$this->throttle([ 'resetPassword', $this->getIpAddress() ], 5, (60 * 60), 10);
		$this->throttle([ 'resetPassword', 'selector', $selector ], 3, (60 * 60), 10);
		$this->throttle([ 'resetPassword', 'token', $token ], 3, (60 * 60), 10);

		try {
			$resetData = $this->db->selectRow(
				'SELECT a.id, a.user, a.token, a.expires, b.email, b.resettable FROM ' . $this->makeTableName('users_resets') . ' AS a JOIN ' . $this->makeTableName('users') . ' AS b ON b.id = a.user WHERE a.selector = ?',
				[ $selector ]
			);
		}
		catch (\Error $e) {
			throw new Exceptions\DatabaseError($e->getMessage());
		}

		if (!empty($resetData)) {
			if ((int) $resetData['resettable'] === 1) {
				if (_check_hash($token, $resetData['token'])) {
					if ($resetData['expires'] >= \time()) {
						$newPassword = self::validatePassword($newPassword);
						$this->updatePasswordInternal($resetData['user'], $newPassword);
						$this->forceLogoutForUserById($resetData['user']);

						try {
							$this->db->delete(
								$this->makeTableNameComponents('users_resets'),
								[ 'id' => $resetData['id'] ]
							);
						}
						catch (\Error $e) {
							throw new Exceptions\DatabaseError($e->getMessage());
						}

						return [
							'id' => $resetData['user'],
							'email' => $resetData['email']
						];
					}
					else {
						throw new Exceptions\TokenExpiredException();
					}
				}
				else {
					throw new Exceptions\InvalidSelectorTokenPairException();
				}
			}
			else {
				throw new Exceptions\ResetDisabledException();
			}
		}
		else {
			throw new Exceptions\InvalidSelectorTokenPairException();
		}
	}

	/**
	 * Resets the password for a particular account by supplying the correct selector/token pair
	 *
	 * The selector/token pair must have been generated previously by calling {@see forgotPassword}
	 *
	 * The user will be automatically signed in if this operation is successful
	 *
	 * @param string $selector the selector from the selector/token pair
	 * @param string $token the token from the selector/token pair
	 * @param string $newPassword the new password to set for the account
	 * @param int|null $rememberDuration (optional) the duration in seconds to keep the user logged in ("remember me"), e.g. `60 * 60 * 24 * 365.25` for one year
	 * @return string[] an array with the user's ID at index `id` and the user's email address at index `email`
	 * @throws InvalidSelectorTokenPairException if either the selector or the token was not correct
	 * @throws TokenExpiredException if the token has already expired
	 * @throws ResetDisabledException if the user has explicitly disabled password resets for their account
	 * @throws InvalidPasswordException if the new password was invalid
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see forgotPassword
	 * @see canResetPasswordOrThrow
	 * @see canResetPassword
	 * @see resetPassword
	 */
	public function resetPasswordAndSignIn($selector, $token, $newPassword, $rememberDuration = null) {
		$idAndEmail = $this->resetPassword($selector, $token, $newPassword);

		if (!$this->isLoggedIn()) {
			$idAndEmail['email'] = self::validateEmailAddress($idAndEmail['email']);

			$userData = $this->getUserDataByEmailAddress(
				$idAndEmail['email'],
				[ 'username', 'status', 'roles_mask', 'force_logout' ]
			);

			$this->onLoginSuccessful($idAndEmail['id'], $idAndEmail['email'], $userData['username'], $userData['status'], $userData['roles_mask'], $userData['force_logout'], true);

			if ($rememberDuration !== null) {
				$this->createRememberDirective($idAndEmail['id'], $rememberDuration);
			}
		}

		return $idAndEmail;
	}

	/**
	 * Check if the supplied selector/token pair can be used to reset a password
	 *
	 * The password can be reset using the supplied information if this method does *not* throw any exception
	 *
	 * The selector/token pair must have been generated previously by calling {@see forgotPassword}
	 *
	 * @param string $selector the selector from the selector/token pair
	 * @param string $token the token from the selector/token pair
	 * @throws InvalidSelectorTokenPairException if either the selector or the token was not correct
	 * @throws TokenExpiredException if the token has already expired
	 * @throws ResetDisabledException if the user has explicitly disabled password resets for their account
	 * @throws TooManyRequestsException if the number of allowed attempts/requests has been exceeded
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see forgotPassword
	 * @see canResetPassword
	 * @see resetPassword
	 * @see resetPasswordAndSignIn
	 */
	public function canResetPasswordOrThrow($selector, $token) {
		try {
			// pass an invalid password intentionally to force an expected error
			$this->resetPassword($selector, $token, null);

			// we should already be in one of the `catch` blocks now so this is not expected
			throw new Exceptions\AuthError();
		}
		// if the password is the only thing that's invalid
		catch (Exceptions\InvalidPasswordException $ignored) {
			// the password can be reset
		}
		// if some other things failed (as well)
		catch (Exceptions\AuthException $e) {
			// re-throw the exception
			throw $e;
		}
	}

	/**
	 * Check if the supplied selector/token pair can be used to reset a password
	 *
	 * The selector/token pair must have been generated previously by calling {@see forgotPassword}
	 *
	 * @param string $selector the selector from the selector/token pair
	 * @param string $token the token from the selector/token pair
	 * @return bool whether the password can be reset using the supplied information
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 *
	 * @see forgotPassword
	 * @see canResetPasswordOrThrow
	 * @see resetPassword
	 * @see resetPasswordAndSignIn
	 */
	public function canResetPassword($selector, $token) {
		try {
			$this->canResetPasswordOrThrow($selector, $token);

			return true;
		}
		catch (Exceptions\AuthException $e) {
			return false;
		}
	}

	/**
	 * Sets whether password resets should be permitted for the account of the currently signed-in user
	 *
	 * @param bool $enabled whether password resets should be enabled for the user's account
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function setPasswordResetEnabled($enabled) {
		$enabled = (bool) $enabled;

		if ($this->isLoggedIn()) {
			try {
				$this->db->update(
					$this->makeTableNameComponents('users'),
					[
						'resettable' => $enabled ? 1 : 0
					],
					[
						'id' => $this->getUserId()
					]
				);
			}
			catch (\Error $e) {
				throw new Exceptions\DatabaseError($e->getMessage());
			}
		}
		else {
			throw new Exceptions\NotLoggedInException();
		}
	}

	/**
	 * Returns whether password resets are permitted for the account of the currently signed-in user
	 *
	 * @return bool
	 * @throws NotLoggedInException if the user is not currently signed in
	 * @throws AuthError if an internal problem occurred (do *not* catch)
	 */
	public function isPasswordResetEnabled() {
		if ($this->isLoggedIn()) {
			try {
				$enabled = $this->db->selectValue(
					'SELECT resettable FROM ' . $this->makeTableName('users') . ' WHERE id = ?',
					[ $this->getUserId() ]
				);

				return (int) $enabled === 1;
			}
			catch (\Error $e) {
				throw new Exceptions\DatabaseError($e->getMessage());
			}
		}
		else {
			throw new Exceptions\NotLoggedInException();
		}
	}

	/**
	 * Returns whether the user is currently logged in by reading from the session
	 *
	 * @return boolean whether the user is logged in or not
	 */
	public function isLoggedIn() {
		return isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_LOGGED_IN]) && $_SESSION[self::SESSION_FIELD_LOGGED_IN] === true;
	}

	/**
	 * Shorthand/alias for ´isLoggedIn()´
	 *
	 * @return boolean
	 */
	public function check() {
		return $this->isLoggedIn();
	}

	/**
	 * Returns the currently signed-in user's ID by reading from the session
	 *
	 * @return int the user ID
	 */
	public function getUserId() {
		if (isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_USER_ID])) {
			return $_SESSION[self::SESSION_FIELD_USER_ID];
		}
		else {
            return 1;
//			return null;
		}
	}

	/**
	 * Shorthand/alias for {@see getUserId}
	 *
	 * @return int
	 */
	public function id() {
		return $this->getUserId();
	}

	/**
	 * Returns the currently signed-in user's email address by reading from the session
	 *
	 * @return string the email address
	 */
	public function getEmail() {
		if (isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_EMAIL])) {
			return $_SESSION[self::SESSION_FIELD_EMAIL];
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the currently signed-in user's display name by reading from the session
	 *
	 * @return string the display name
	 */
	public function getUsername() {
		if (isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_USERNAME])) {
			return $_SESSION[self::SESSION_FIELD_USERNAME];
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the currently signed-in user's status by reading from the session
	 *
	 * @return int the status as one of the constants from the {@see Status} class
	 */
	public function getStatus() {
		if (isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_STATUS])) {
			return $_SESSION[self::SESSION_FIELD_STATUS];
		}
		else {
			return null;
		}
	}

	/**
	 * Returns whether the currently signed-in user is in "normal" state
	 *
	 * @return bool
	 *
	 * @see Status
	 * @see Auth::getStatus
	 */
	public function isNormal() {
		return $this->getStatus() === Status::NORMAL;
	}

	/**
	 * Returns whether the currently signed-in user is in "archived" state
	 *
	 * @return bool
	 *
	 * @see Status
	 * @see Auth::getStatus
	 */
	public function isArchived() {
		return $this->getStatus() === Status::ARCHIVED;
	}

	/**
	 * Returns whether the currently signed-in user is in "banned" state
	 *
	 * @return bool
	 *
	 * @see Status
	 * @see Auth::getStatus
	 */
	public function isBanned() {
		return $this->getStatus() === Status::BANNED;
	}

	/**
	 * Returns whether the currently signed-in user is in "locked" state
	 *
	 * @return bool
	 *
	 * @see Status
	 * @see Auth::getStatus
	 */
	public function isLocked() {
		return $this->getStatus() === Status::LOCKED;
	}

	/**
	 * Returns whether the currently signed-in user is in "pending review" state
	 *
	 * @return bool
	 *
	 * @see Status
	 * @see Auth::getStatus
	 */
	public function isPendingReview() {
		return $this->getStatus() === Status::PENDING_REVIEW;
	}

	/**
	 * Returns whether the currently signed-in user is in "suspended" state
	 *
	 * @return bool
	 *
	 * @see Status
	 * @see Auth::getStatus
	 */
	public function isSuspended() {
		return $this->getStatus() === Status::SUSPENDED;
	}

	/**
	 * Returns whether the currently signed-in user has the specified role
	 *
	 * @param int $role the role as one of the constants from the {@see Role} class
	 * @return bool
	 *
	 * @see Role
	 */
	public function hasRole($role) {
		if (empty($role) || !\is_numeric($role)) {
			return false;
		}

		if (isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_ROLES])) {
			$role = (int) $role;

			return (((int) $_SESSION[self::SESSION_FIELD_ROLES]) & $role) === $role;
		}
		else {
			return false;
		}
	}

	/**
	 * Returns whether the currently signed-in user has *any* of the specified roles
	 *
	 * @param int[] ...$roles the roles as constants from the {@see Role} class
	 * @return bool
	 *
	 * @see Role
	 */
	public function hasAnyRole(...$roles) {
		foreach ($roles as $role) {
			if ($this->hasRole($role)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns whether the currently signed-in user has *all* of the specified roles
	 *
	 * @param int[] ...$roles the roles as constants from the {@see Role} class
	 * @return bool
	 *
	 * @see Role
	 */
	public function hasAllRoles(...$roles) {
		foreach ($roles as $role) {
			if (!$this->hasRole($role)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array of the user's roles, mapping the numerical values to their descriptive names
	 *
	 * @return array
	 */
	public function getRoles() {
		return \array_filter(
			Role::getMap(),
			[ $this, 'hasRole' ],
			\ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Returns whether the currently signed-in user has been remembered by a long-lived cookie
	 *
	 * @return bool whether they have been remembered
	 */
	public function isRemembered() {
		if (isset($_SESSION) && isset($_SESSION[self::SESSION_FIELD_REMEMBERED])) {
			return $_SESSION[self::SESSION_FIELD_REMEMBERED];
		}
		else {
			return null;
		}
	}





	/**
	 * Returns the component that can be used for administrative tasks
	 *
	 * You must offer access to this interface to authorized users only (restricted via your own access control)
	 *
	 * @return Administration
	 */
	public function admin() {
		return new Administration($this->db, $this->dbTablePrefix, $this->dbSchema);
	}

	/**
	 * Creates a UUID v4 as per RFC 4122
	 *
	 * The UUID contains 128 bits of data (where 122 are random), i.e. 36 characters
	 *
	 * @return string the UUID
	 * @author Jack @ Stack Overflow
	 */
	public static function createUuid() {
		$data = \openssl_random_pseudo_bytes(16);

		// set the version to 0100
		$data[6] = \chr(\ord($data[6]) & 0x0f | 0x40);
		// set bits 6-7 to 10
		$data[8] = \chr(\ord($data[8]) & 0x3f | 0x80);

		return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($data), 4));
	}

	/**
	 * Generates a unique cookie name for the given descriptor based on the supplied seed
	 *
	 * @param string $descriptor a short label describing the purpose of the cookie, e.g. 'session'
	 * @param string|null $seed (optional) the data to deterministically generate the name from
	 * @return string
	 */
	public static function createCookieName($descriptor, $seed = null) {
		// use the supplied seed or the current UNIX time in seconds
		$seed = ($seed !== null) ? $seed : \time();

		foreach (self::COOKIE_PREFIXES as $cookiePrefix) {
			// if the seed contains a certain cookie prefix
			if (\strpos($seed, $cookiePrefix) === 0) {
				// prepend the same prefix to the descriptor
				$descriptor = $cookiePrefix . $descriptor;
			}
		}

		// generate a unique token based on the name(space) of this library and on the seed
		$token = Base64::encodeUrlSafeWithoutPadding(
			\md5(
				__NAMESPACE__ . "\n" . $seed,
				true
			)
		);

		return $descriptor . '_' . $token;
	}

	/**
	 * Generates a unique cookie name for the 'remember me' feature
	 *
	 * @param string|null $sessionName (optional) the session name that the output should be based on
	 * @return string
	 */
	public static function createRememberCookieName($sessionName = null) {
		return self::createCookieName(
			'remember',
			($sessionName !== null) ? $sessionName : \session_name()
		);
	}

	/**
	 * Returns the selector of a potential locally existing remember directive
	 *
	 * @return string|null
	 */
	private function getRememberDirectiveSelector() {
		if (isset($_COOKIE[$this->rememberCookieName])) {
			$selectorAndToken = \explode(self::COOKIE_CONTENT_SEPARATOR, $_COOKIE[$this->rememberCookieName], 2);

			return $selectorAndToken[0];
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the expiry date of a potential locally existing remember directive
	 *
	 * @return int|null
	 */
	private function getRememberDirectiveExpiry() {
		// if the user is currently signed in
		if ($this->isLoggedIn()) {
			// determine the selector of any currently existing remember directive
			$existingSelector = $this->getRememberDirectiveSelector();

			// if there is currently a remember directive whose selector we have just retrieved
			if (isset($existingSelector)) {
				// fetch the expiry date for the given selector
                $existingExpiry = Database::getInstance()->conn->fetchColumn(
                    'SELECT expires FROM ' . $this->makeTableName('users_remembered') . ' WHERE selector = ? AND user = ?',
                    [
						$existingSelector,
						$this->getUserId()
                    ]
                );
				// if an expiration date has been found
				if (isset($existingExpiry)) {
					// return the date
					return (int) $existingExpiry;
				}
			}
		}

		return null;
	}
}