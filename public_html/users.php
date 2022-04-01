<?php
/**
* glFusion CMS
*
* glFusion User authentication module
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark A. Howard  mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*  Dirk Haun         dirk@haun-online.de
*
*/

/**
 * This is the controller for all things authentication, such as logging in, request password reset, etc.
 * Generally $_GET requests are the initial form (exception is oauth_login)
 * $_POST requests are the user submitting the info, such as username, password, token, TFA, etc.
 */

require_once 'lib-common.php';

use \glFusion\Database\Database;
use \glFusion\Log\Log;
use \glFusion\User\UserAuth;
use \glFusion\User\UserManager;
use \glFusion\User\UserInterface;
use \glfusion\User\Exceptions;
use \glFusion\User\Status;
use \Delight\Cookie\Session;

/**
 *  Nothing passed - no get vars and no post vars
 *      - show login form - unless already logged in
 * METHOD = post -
 *      - we are trying to login, set new password, validate TFA, verify account
 * METHOD = GET - need to see what we got
 *      - oauth_login = do the oauth login stuff
 *      - create new user
 *      - request new activation email
 *      - request forgotten password
*/

$method = filter_input(\INPUT_SERVER, 'REQUEST_METHOD', \FILTER_UNSAFE_RAW);
if ($method == '' || empty($method)) {
    $method = "GET";
}

$validPostRequests = ['logout','profile','user','create','getpassword','login'];
$validGetRequests  = ['oauth_login','create',''];

$userManager = new UserAuth();

// cycle thru the modes to find the one that is valid

switch ($method) {
    // form submission - trying to login, validate token, request token, reset password, etc.
    case 'POST' :
        $mode = filter_input(\INPUT_POST, 'mode', \FILTER_UNSAFE_RAW);

        switch ($mode) {
            case 'login' :
                try {
                    $userManager->userLocalLoginController();
                } catch (Exceptions\LocalLoginServiceDisabledException $e) {
                    die('Local Login has been disabled');
                } catch (\Error $e) {
                    Log::write('system',Log::ERROR,'users.php - Unknown error: ' . $e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine());
                }
                break;

            case 'tfa' :
                // user is submitting their TFA validation
                $authenticated = 1;
                $uid = Session::take('tfa');

                try {
                    $userManager->authenticateUserTFA($uid);
                } catch (\Throwable $e) {
                    $authenticated = 0;
                    COM_setMsg($LANG_TFA['error_invalid_code'],'error',true);
                    Session::set('tfa',$uid);
                    UserInterface::TFAvalidationPage($uid);
                }
                break;

            case 'create' :
                // user is submitting their create credentials
                $userRegister = new UserManager();
                try {
                    $uid = $userRegister->registerUser($_POST);
                } catch (\Throwable $e) {
                    UserInterface::registrationPage($_POST,$e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                }
                // if we made it here - we should have a valid user id
                if ($uid > 1) {
                    // pull the user status and verification mode - here we can determine the type of message to display.
                    try {
                        $userData = Database::getInstance()->conn->fetchAssoc(
                            "SELECT uid, email, username, status, account_type, roles_mask, force_logout, verified FROM {$_TABLES['users']} WHERE uid = ?",
                            [$uid],
                            [Database::INTEGER]
                        );
                    } catch (\Throwable $e) {
                        throw new Exceptions\DatabaseError($e->getMessage());
                    }

                    if ($userData != null) {
                        if ($userData['status'] == Status::PENDING_REVIEW) {
                            COM_setMsg( '<h2>'.$LANG04[174].'</h2>' . $LANG04[117], 'modal');
                        } elseif ($userData['status'] == Status::NORMAL && $userData['verified'] == 0) {
                            COM_setMsg( '<h2>'.$LANG04[174].'</h2>' . $LANG04[177], 'modal');
                        } elseif ($userData['verified'] == 0) {
                            COM_setMsg( '<h2>'.$LANG04[174].'</h2>' . $LANG04[177], 'modal');
                        } elseif ($userData['status'] == Status::NORMAL) {
                            // no submission queue or email validation configured - so redirect user to the login page
//@TODO - Language String
                            COM_setMsg('<h2>'.'Account Successfully Created'.'</h2>'.'You may now login with your new account. Enjoy the site!');
                            COM_refresh($_CONF['site_url'].'/users.php');
                            exit;
                        }
                    }
                } else {
                    Log::write('system',Log::ERROR,'users.php::Error creating a new user.');
                }
                COM_refresh($_CONF['site_url']);

                break;

            case 'getnewtoken' :
                // user is requesting a new token
                try {
                    $userManager->userResendConfirmationController();
                } catch (Exceptions\TooManyRequestsException $e) {
                    Log::write('system',Log::WARNING,'users.php:: User has exceeded the max number of resend confirmations');
                    COM_refresh($_CONF['site_url'].'/index.php');
                } catch (\Throwable $e) {
                    // error - redirect to main page
                    Log::write('system',Log::WARNING,'users.php::' . $e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine());
                    COM_refresh($_CONF['site_url']);
                }
                COM_refresh($_CONF['site_url'].'/index.php?msg=3');

                break;

            case 'emailpasswd' :
                $email = filter_input(\INPUT_POST,'email',\FILTER_SANITIZE_EMAIL);

                $msg = PLG_itemPreSave ('forgotpassword');
                if (!empty ($msg)) {
                    COM_setMsg($msg,'error');
                    echo COM_refresh ($_CONF['site_url'].'/users.php?mode=getpassword');
                }
                $msg = 55; // an email has been sent...
//@TODO - CSRF not implemented
                try {
                    $userManager->forgotPassword($email, array($userManager,'sendPasswordReset'));
//@TODO - Language updates
                } catch (Exceptions\EmailNotVerifiedException $e) {
                    COM_setMsg('Your email address has not been verified. Please follow the instructions in the verification email before you can reset your password.');
                } catch (Exceptions\ResetDisabledException $e) {
                    COM_setMsg('Password resets have been disabled for this user account');
                } catch (Exceptions\TooManyRequestsException $e) {
                    COM_setMsg('There are already several outstanding password reset requests for this account. No additional requests can be made at this time.','modal');
                } catch (\Throwable $e) {
                    Log::write('system',Log::ERROR,'users.php :: Error sending forgot password email. '.$e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                }
                COM_refresh($_CONF['site_url'] . '/index.php?msg=55');
                break;

            case 'setnewpwd' :
 //@TODO - CSRF
                $passwd = filter_input(\INPUT_POST,'passwd',FILTER_UNSAFE_RAW);
                $passwd_conf = filter_input(\INPUT_POST,'passwd_conf',FILTER_UNSAFE_RAW);
                $uid = (int) filter_input(INPUT_POST,'uid',FILTER_SANITIZE_NUMBER_INT);
                $selector = filter_input(\INPUT_POST,'selector',FILTER_UNSAFE_RAW);
                $token = filter_input(\INPUT_POST,'token',FILTER_UNSAFE_RAW);

                if ((empty($_POST['passwd'])) || ($_POST['passwd'] != $_POST['passwd_conf'])) {
//@TODO - Language updates
                    COM_setMsg('Please ensure you have typed the confirmation password correctly.','error');
                    UserInterface::newPasswordPage($uid,$selector,$token);
                }

                try {
                    $userManager->resetPassword($selector, $token, $passwd);
                } catch (Exceptions\TokenExpiredException $e) {
                    COM_refresh($_CONF['site_url'].'/index.php?msg=54');
                } catch (Exceptions\InvalidSelectorTokenPairException $e) {
                    COM_404();
                } catch (Exceptions\ResetDisabledException $e) {
                    die('resets are disabled for this account');

                } catch (Exceptions\InvalidSelectorTokenPairException $e) {
                    COM_404();

                } catch (\Throwable $e) {
                    Log::write('system',Log::ERROR,'users.php::Error resetting users password: ' . $e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine());
                    COM_refresh($_CONF['site_url'].'/index.php');
                }
                COM_refresh ($_CONF['site_url'] . '/users.php?msg=53');

                break;

            default :
                // nothing passed or nothing that was recognized - default to the login page
                userInterface::loginPage();
                break;

        } // end of switch mode

        break;

    // oauth - initial request for token, password, etc.
    // this is also the default - so no mode means show login form
    case 'GET' :
        $mode = filter_input(\INPUT_GET, 'mode', \FILTER_UNSAFE_RAW);

        if (empty($mode)) {
            $service = filter_input(\INPUT_GET, 'oauth_login', \FILTER_UNSAFE_RAW);
            if (!empty($service)) {
                $mode = 'oauth_login';
            }
        }

        switch ($mode) {
            case 'logout' :
                try {
                    $userManager->logout();
                } catch (Exceptions\NotLoggedInException $e) {
                    // inform the user they are not logged in and redirect to home page
                    die('Local Login has been disabled');
                } catch (\Throwable $e) {
                    // log the unknown error and redirect to home page
                }
                COM_refresh($_CONF['site_url']);
                break;

            case 'oauth_login' :
                try {
                    $userManager->userOauthLoginController();
                } catch (Exceptions\OauthLoginServiceDisabledException $e) {
                    die('Oauth Login has been disabled');
                } catch (\Throwable $e) {
                    Log::write('system',Log::DEBUG,'users.php :: Oauth Login Error: ' . $e->getMessage());
                }
                COM_refresh($_CONF['site_url']);
                break;

            case 'getnewtoken' :
                if ($userManager->isLoggedIn()) {
                    Log::write('system',Log::DEBUG,'users.php :: Request New Token :: User is already logged in - redirecting to usersettings.php');
                    COM_refresh($_CONF['site_url'].'/usersettings.php');
                    exit;
                }
                UserInterface::newTokenForm();
                break;

            case 'getpassword' :
                if ($userManager->isLoggedIn()) {
                    Log::write('system',Log::DEBUG,'users.php :: Forgot Password :: User is already logged in - redirecting to usersettings.php');
                    COM_refresh($_CONF['site_url'].'/usersettings.php');
                    exit;
                }
                UserInterface::getPasswordPage();
                break;

            case 'new' :
                if ($userManager->isLoggedIn()) {
                    Log::write('system',Log::DEBUG,'users.php :: New User Registration :: User is already logged in - redirecting to usersettings.php');
                    COM_refresh($_CONF['site_url'].'/usersettings.php');
                    exit;
                }
                UserInterface::registrationPage();
                break;

            case 'verify' :
                if ($userManager->isLoggedIn()) {
                    Log::write('system',Log::DEBUG,'users.php :: Verify Email :: User is already logged in - redirecting to usersettings.php');
                    COM_refresh($_CONF['site_url'].'/usersettings.php');
                    exit;
                }
                if (!isset($_GET['s']) || !isset($_GET['t'])) {
                    COM_404();
                }
                $s = $_GET['s'];
                $t = $_GET['t'];
                try {
                    $rc = $userManager->confirmEmail($s, $t);
                } catch (Exceptions\TokenExpiredException $e) {
                    COM_setMsg($MESSAGE[516],'error',true);
                    UserInterface::newTokenForm();

                } catch (Exceptions\InvalidSelectorTokenPairException $e) {
                    COM_404();
                }

                COM_refresh($_CONF['site_url'].'/index.php?msg=515');
                break;

            case 'newpwd' :
                if (!isset($_GET['s']) || !isset($_GET['t'])) {
                    COM_404();
                }
                $selector = filter_input(\INPUT_GET,'s',FILTER_UNSAFE_RAW); //$_GET['s'];
                $token = filter_input(\INPUT_GET,'t',FILTER_UNSAFE_RAW); //$_GET['t'];

                if (empty($selector) || empty($token)) {
                    COM_404();
                }

                if ($userManager->canResetPassword($selector, $token)) {
                    $uid = Database::getInstance()->getItem ($_TABLES['users_resets'], 'user',array('selector' => $selector),array(Database::STRING));
                    if ($uid != null) {
                        UserInterface::newPasswordPage($uid,$selector,$token);
                    }
                }
                // not a valid selector / token pair
                else {
                    COM_404();
                }
                break;

            case 'profile':
            case 'user' :
                echo COM_siteHeader('menu');
                echo UserInterface::userProfile();
                echo COM_siteFooter();
                exit;

                break;

            default :
                if ($userManager->isLoggedIn()) {
                    Log::write('system',Log::DEBUG,'users.php :: User is already logged in - redirecting to usersettings.php');
                    COM_refresh($_CONF['site_url'].'/usersettings.php');
                    exit;
                }
                UserInterface::loginPage();
                break;
        }

        break;
}
COM_refresh($_CONF['site_url'].'/index.php');
?>
