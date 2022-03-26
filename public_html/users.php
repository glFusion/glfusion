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
use \glFusion\User;
use \glFusion\User\UserAuth;
use \glFusion\User\UserCreate;
use \glFusion\User\UserInterface;
use \glfusion\User\Exceptions;
use \glFusion\User\Status;
use \Delight\Cookie\Session;

/**
 * What we know:
 *
 *  Nothing passed - no get vars and no post vars
 *      - show login form - unless already logged in
 * METHOD = post -
 *      - we are trying to login or do something else
 * METHOD = GET - need to see what we got
 *      - oauth_login = do the oauth login stuff
 * mode = everything else
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
                }
                break;
            case 'tfa' :
                // user is submitting their TFA validation

                break;
            case 'create' :
                // user is submitting their create credentials

                break;

            default :
                // we don't know what they want
                userInterface::loginPage();
                break;

        } // end of switch mode

        break;

    // oauth - initial request for token, password, etc.
    // this is also the default - so no mode means show login form
    case 'GET' :
        Log::write('system',Log::DEBUG,'users.php :: method is GET');
        $mode = filter_input(\INPUT_GET, 'mode', \FILTER_UNSAFE_RAW);

        if (empty($mode)) {
            $service = filter_input(\INPUT_GET, 'oauth_login', \FILTER_UNSAFE_RAW);
            if (!empty($service)) {
                $mode = 'oauth_login';
            }
        }
        Log::write('system',Log::DEBUG,'users.php :: mode is '.$mode);


        switch ($mode) {
            case 'logout' :
                try {
                    $userManager->logout();
                } catch (Exceptions\NotLoggedInException $e) {
                    // inform the user they are not logged in and redirect to home page
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

die('we are now done');





if ( isset($_POST['mode']) ) {
    $mode = $_POST['mode'];
} elseif (isset($_GET['mode']) ) {
    $mode = $_GET['mode'];
} else {
    $mode = '';
    if (isset($_GET['oauth_login'])) {
        $mode = 'oauth_login';
    } else {
        if (isset($_POST['service'])) {

        }
    }
}

$display = '';
$pageBody = '';

if ( isset($_POST['cancel']) ) {
    echo COM_refresh($_CONF['site_url'].'/index.php');
}

$db = Database::getInstance();

switch ($mode) {
    case 'logout':
        $pageBody = userLogout();
        break;
    case 'profile':
    case 'user' :
        $pageBody .= userprofile();
        break;
    case 'create':
        USER_registerUserAction($_POST);
        break;
    case 'getpassword':
        $pageBody .= _userGetpassword();
        break;
    case 'newpwd':
        $pageBody .= _userNewpwd();
        break;
    case 'setnewpwd':
        $pageBody .= _userSetnewpwd();
        break;
    case 'emailpasswd':
        $pageBody .= _userEmailpassword();
        break;
    case 'new':
        $pageBody .= USER_registrationForm();
        break;
    case 'verify':
        $pageBody .= _userVerify();
        break;
    case 'getnewtoken':
        $pageBody .= _userGetnewtoken();
        break;
    case 'mergeacct' :
        if ( SEC_checkToken() ) {
            $pageBody .= USER_mergeAccounts();
        } else {
            echo COM_refresh($_CONF['site_url']);
        }
        break;
    case 'oauth_login' :

        break;







    default:
        /**
         * This is where it gets complicated.  nothing is passed so we assume it is either
         * logging in or need to show the login form - but we don't know which
         * and if the user is already logged in - then we need to redirect to userprofile
         */









    $status = -2;
        $local_login = false;
        $newTwitter  = false;
        $authenticated = 0;

        $_UserInstance = new User\UserAuth();

        $loginname = '';
        if (isset ($_POST['loginname'])) {
            $loginname = $_POST['loginname'];
            if ( !USER_validateUsername($loginname,true) ) {
                $loginname = '';
            }
        }
        $passwd = '';
        if (isset ($_POST['passwd'])) {
            $passwd = $_POST['passwd'];
        }

        $service = '';
        if (isset ($_POST['service'])) {
            $service = COM_applyFilter($_POST['service']);
        }

        $uid = '';
        /** Check for local login first */
        if (!empty($loginname) && !empty($passwd) && empty($service)) {
            if (empty($service) && $_CONF['user_login_method']['standard']) {

                try {
                    $_UserInstance->loginWithUsername($loginname, $passwd,0,array($_UserInstance,'userLoginBeforeSuccess'));
                    $status = $_UserInstance->getStatus();
                } catch (\glFusion\User\Exceptions\InvalidPasswordException | \glFusion\User\Exceptions\UnknownUsernameException $e) {
                    COM_setMsg($MESSAGE[81],'error');
                    $status = -2;
                } catch (User\Exceptions\TooManyRequestsException $e) {
                    displayLoginErrorAndAbort(82, $LANG12[26], $LANG04[112]);
                } catch (User\Exceptions\AttemptCancelledException $e) {
                    // the attempt was cancelled - possibly CAPTCHA failed or other validation before login failed
                    $status = -2;
                } catch (User\Exceptions\AccountPendingReviewException $e) {
                    $status = USER_ACCOUNT_AWAITING_APPROVAL;
                } catch (User\Exceptions\EmailNotVerifiedException $e) {
                    $status = USER_ACCOUNT_AWAITING_VERIFICATION;
                    COM_setMsg($LANG04[177],'error');
                    COM_refresh($_CONF['site_url']);
                }

                if ($status == Status::NORMAL) {
                    $local_login = true;
                }
            } else {
                Log::write('system',Log::ERROR,"ERROR: Username and Password were provided, but local authentication is disabled - check configuration settings");
                $status = -2;
            }
            // begin distributed (3rd party) remote authentication method

        }
        /** Check for 3rd party login */
        elseif (!empty($loginname) && $_CONF['user_login_method']['3rdparty'] &&
            ($_CONF['usersubmission'] == 0) && ($service != '')) {
            COM_updateSpeedlimit('login');
            //pass $loginname by ref so we can change it ;-)
            $status = SEC_remoteAuthentication($loginname, $passwd, $service, $uid);

        // end distributed (3rd party) remote authentication method
        }
        /** check for Oauth Login */
        elseif ($_CONF['user_login_method']['oauth'] && isset($_GET['oauth_login'])) {
            try {
                $_UserInstance->loginWithOauth(0, array($_UserInstance,'userLoginBeforeSuccess'));
                $status = $_UserInstance->getStatus();
            } catch (User\Exceptions\EmailNotVerifiedException $e) {
                $status = USER_ACCOUNT_AWAITING_VERIFICATION;
                COM_setMsg($LANG04[177],'error');
                COM_refresh($_CONF['site_url']);
            } catch (\Throwable $e) {
                COM_updateSpeedlimit('login');
                Log::write('system',Log::ERROR,"OAuth Error: " . $e->getMessage());
                COM_setMsg($MESSAGE[111],'error');
            }
        //  end OAuth authentication method

        }
        // check if we are authenticating the multi factor authentication
        elseif ($mode == 'tfa' ) {
            $authenticated = 1;

            $uid = (int) filter_input(INPUT_POST,'uid',FILTER_SANITIZE_NUMBER_INT);

            try {
                $_UserInstance->authenticateUserTFA($uid);
            } catch (\Throwable $e) {
                $authenticated = 0;
                COM_setMsg($LANG_TFA['error_invalid_code'],'error',true);
                SEC_2FAForm($uid);
            }

            $row = $db->conn->fetchAssoc(
                    "SELECT * FROM `{$_TABLES['users']}` WHERE uid=?",
                    array($uid),
                    array(Database::INTEGER)
            );
            if ($row !== false && $row !== null) {
                $status = $row['status'];
                $local_login = $row['account_type'] & LOCAL_USER;
            } else {
                $status = -2;
            }
        } else {
            $status = -2;
        }

        if ($status == USER_ACCOUNT_ACTIVE || $status == USER_ACCOUNT_AWAITING_ACTIVATION ) { // logged in AOK.
            $_USER = $_UserInstance->getUserData();
            $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
            $_RIGHTS = explode( ',', SEC_getUserPermissions() );

            if ((int) $_SYSTEM['admin_session'] > 0 && $local_login ) {
                if (SEC_isModerator() || SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit','OR') || (count(PLG_getAdminOptions()) > 0)) {
                    Session::set($_UserInstance::SESSION_FIELD_ADMIN_SESSION,\time() + $_SYSTEM['admin_session']);
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
        // user account is not active or awaiting initial login
        else {
            $msg = COM_getMessage();
            if ($msg > 0) {
                $pageBody .= COM_showMessage($msg,'','',0,'info');
            }
// odd - we fall thru here is bad account info?  need to trap this way earlier
            switch ($mode) {
                case 'create':
                    // Got bad account info from registration process, show error
                    // message and display form again
                    $pageBody .= USER_registrationForm ();
                    break;
// we fall thru to here if we have nothing passed
// we check speed limit
// if the msg isn't 69 or 70 (what are there??) - and we are logged in - we go to the user profile screen
// 69 blocked
// 70 awaiting verification
                default:
                    if (!empty($_SERVER['HTTP_REFERER'])
                            && (strstr($_SERVER['HTTP_REFERER'], '/users.php') === false)
                            && (substr($_SERVER['HTTP_REFERER'], 0,
                                    strlen($_CONF['site_url'])) == $_CONF['site_url'])) {
                            SESS_setVar('login_referer',$_SERVER['HTTP_REFERER']);
                    }

                    // check to see if this was the last allowed attempt
                    if (COM_checkSpeedlimit('login', $_CONF['login_attempts']) > 0) {
                        displayLoginErrorAndAbort(82, $LANG04[113], $LANG04[112]);
                    } else { // Show login form
                        if(($msg != 69) && ($msg != 70)) {
                            if ( !COM_isAnonUser() ) {
                                echo COM_refresh($_CONF['site_url'].'/usersettings.php');
                                exit;
                            }
                            if ($_CONF['custom_registration'] AND function_exists('CUSTOM_loginErrorHandler') && $msg != 0) {
                                $pageBody .= CUSTOM_loginErrorHandler($msg);
                            } else {
                                $pageBody .= loginform(false, $status);
                            }
                        }
                    }
                    break;
            }
        }
        break;
}

$display = COM_siteHeader('menu');
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>
