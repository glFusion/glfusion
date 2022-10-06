<?php
/**
* glFusion CMS
*
* glFusion User Authentication Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*
*/

namespace glFusion\User;

use \glFusion\Database\Database;;

class UserInterface {


    /**
    * Shows the user login form after failed attempts to either login or access a page
    * requiring login.
    *
    * @return   string      HTML for login form
    *
    */
    static function failedLoginForm ($hide_forgotpw_link = false, $statusmode = -1)
    {
        global $_CONF, $LANG04;

        $options = array(
            'hide_forgotpw_link' => $hide_forgotpw_link,
            'form_action'        => $_CONF['site_url'].'/users.php',
        );
        if (isset($_POST['login_landing'])) {
            $options['login_landing'] = $_POST['login_landing'];
        } elseif (isset($_GET['landing'])) {
            $options['login_landing'] = $_GET['landing'];
        }

        if ($statusmode == USER_ACCOUNT_DISABLED) {
            $options['title']   = $LANG04[114]; // account disabled
            $options['message'] = $LANG04[115]; // your account has been disabled, you may not login
            $options['forgotpw_link']      = false;
            $options['newreg_link']        = false;
            $options['verification_link']  = false;
        } elseif ($statusmode == USER_ACCOUNT_AWAITING_APPROVAL) {
            $options['title']   = $LANG04[116]; // account awaiting activation
            $options['message'] = $LANG04[117]; // your account is currently awaiting activation by an admin
            $options['forgotpw_link']      = false;
            $options['newreg_link']        = false;
            $options['verification_link']  = false;
        } elseif ($statusmode == USER_ACCOUNT_AWAITING_VERIFICATION ) {
            $options['title']   = $LANG04[116]; // account awaiting activation
            $options['message'] = $LANG04[177]; // your account is currently awaiting verification
            $options['forgotpw_link']      = false;
            $options['newreg_link']        = false;
            $options['verification_link']  = true;
        } elseif ($statusmode == -1) { // invalid credentials
            $options['title']   = sprintf($LANG04[65],$_CONF['site_name']); // log in to {site_name}
            $options['message'] = $LANG04[113]; // login attempt failed
        } else {
            $options['title']   = sprintf($LANG04[65],$_CONF['site_name']); // log in to {site_name}
            $options['message'] = ''; // $LANG04[66]; // please enter your user name and password below
        }

        return self::loginForm($options);
    }



    /**
    * HTML for login form
    *
    * This is the version of the login form displayed in the content area of the
    * page (not the side bar). It will present all options (remote authentication
    * - including new registration link, etc.) according to the current
    * configuration settings.
    *
    * @param    array   $use_options    options to override default settings
    * @return   string                  HTML of the login form
    *
    */
    // we might make this protected - so you have to go thru the other
    // public functions to display this one - this one is the worker
    public static function loginForm($use_options = array())
    {
        global $_CONF, $_USER, $LANG01, $LANG04;

        $retval = '';

        $default_options = array(
            // display options
            'forgotpw_link'     => true,

            // for hidden fields to be included in the form
            'hidden_fields'     => '',

            // options to locally override some specific $_CONF options
            'oauth_login'       => true,    // $_CONF['user_login_method']['oauth']
            '3rdparty_login'    => true,    // $_CONF['user_login_method']['3rdparty']
            'newreg_link'       => true,    // $_CONF['disable_new_user_registration']
            'verification_link' => false,   // resend verification?
            'plugin_vars'       => true,    // call PLG_templateSetVars?
            'prefill_user'      => false,   // prefill username of current user
            'password_only'     => false,   // only prompt for password

            // default texts
            'title'             => sprintf($LANG04[65],$_CONF['site_name']), // Login to site
            'message'           => '', // $LANG04[66], // Please enter username
            'footer_message'    => '',
            'button_text'       => $LANG04[80], // Login
            'prompt'            => '',

            // action
            'form_action' => $_CONF['site_url'].'/users.php',

            // landing page after successful login
            'login_landing' => '',
        );

        $options = array_merge($default_options, $use_options);

        $loginform = new \Template(array($_CONF['path_layout'] . 'users', $_CONF['path_layout']));
        $loginform->set_file('login', 'loginform.thtml');

        $loginform->set_var('form_action', $options['form_action']);
        $loginform->set_var('footer_message',$options['footer_message']);

        $loginform->set_var('start_block_loginagain',COM_startBlock($options['title']));
        $loginform->set_var('lang_message', $options['message']);
        $loginform->set_var('lang_prompt', $options['prompt']);
        if ($options['newreg_link'] == false || $_CONF['disable_new_user_registration']) {
            $loginform->set_var('lang_newreglink', '');
        } else {
            $loginform->set_var('lang_newreglink', sprintf($LANG04[123],$_CONF['site_url']));
        }

        if ($options['password_only'] == false) {
            $loginform->set_var('lang_username', $LANG04[2]);
        } else {
            $loginform->set_var('lang_username', '');
        }

        $loginform->set_var('lang_password', $LANG01[57]);
        if ($options['forgotpw_link'] === true) {
            $loginform->set_var('lang_forgetpassword', $LANG04[25]);
            $forget = COM_createLink($LANG04[25], $_CONF['site_url']
                                                . '/users.php?mode=getpassword',
                                    array('rel' => 'nofollow'));
            $loginform->set_var('forgetpassword_link', $forget);
        } else {
            $loginform->unset_var('lang_forgetpassword');
            $loginform->unset_var('forgetpassword_link');
        }

        $loginform->set_var('lang_login', $options['button_text']);
        $loginform->set_var('end_block', COM_endBlock());

        // 3rd party remote authentication.
        $services = '';

        if ($options['3rdparty_login'] &&
                $_CONF['user_login_method']['3rdparty'] &&
                ($_CONF['usersubmission'] == 0)) {

            $modules = SEC_collectRemoteAuthenticationModules();
            if (count($modules) > 0) {
                if (!$_CONF['user_login_method']['standard'] && (count($modules) == 1)) {
                    $select = '<input type="hidden" name="service" value="'. $modules[0] . '"/>' . $modules[0] . LB;
                } else {
                    // Build select
                    $select = '';
                    if ( isset($_CONF['standard_auth_first']) && $_CONF['standard_auth_first'] == 1 ) {
                        if ($_CONF['user_login_method']['standard']) {
                            $select .= '<option value="">' .  $_CONF['site_name'] . '</option>' . LB;
                        }
                    }
                    foreach ($modules as $service) {
                        $select .= '<option value="' . $service . '">' . $service . '</option>' . LB;
                    }
                    if ( !isset($_CONF['standard_auth_first']) || $_CONF['standard_auth_first'] == 0 ) {
                        if ($_CONF['user_login_method']['standard']) {
                            $select .= '<option value="">' .  $_CONF['site_name'] . '</option>' . LB;
                        }
                    }
                }

                $loginform->set_file('services', 'services.thtml');
                $loginform->set_var('lang_service', $LANG04[121]);
                $loginform->set_var('select_service', $select);
                $loginform->parse('output', 'services');
                $services .= $loginform->finish($loginform->get_var('output'));
            }
        }

        if ($options['login_landing']) {
            $options['hidden_fields'] .= '<input type="hidden" name="login_landing" value="' .
                $options['login_landing'] . '" />' . LB;
        }

        if (! empty($options['hidden_fields'])) {
            // allow caller to (ab)use {services} for hidden fields
            $services .= $options['hidden_fields'];
            //$loginform->set_var('hidden_fields',$options['hidden_fields']);
        }
        $loginform->set_var('services', $services);

        // OAuth remote authentication.
        if ($options['oauth_login'] && $_CONF['user_login_method']['oauth'] ) {
            $modules = SEC_collectRemoteOAuthModules();
            if (count($modules) == 0) {
                $loginform->set_var('oauth_login', '');
            } else {
                $html_oauth = '';
                foreach ($modules as $service) {
                    $loginform->set_file('oauth_login', 'loginform_oauth.thtml');
                    $loginform->set_var('oauth_service', $service);
                    if ($service === 'facebook') {
                        $loginform->set_var('oauth_service-postfix', '-official');
                    } else {
                        $loginform->set_var('oauth_service-postfix', '');
                    }
                    $loginform->set_var('oauth_service_display',ucwords($service));
                    // for sign in image
                    $loginform->set_var('oauth_sign_in_image', $_CONF['site_url'] . '/images/login-with-' . $service . '.png');
                    $loginform->parse('output', 'oauth_login');
                    $html_oauth .= $loginform->finish($loginform->get_var('output'));
                }
                $loginform->set_var('oauth_login', $html_oauth);
            }
        } else {
            $loginform->set_var('oauth_login', '');
        }

        if ($options['verification_link']) {
            $loginform->set_var('lang_verification', $LANG04[169]);
            $verify = COM_createLink($LANG04[25], $_CONF['site_url']
                                                . '/users.php?mode=getnewtoken',
                                    array('rel' => 'nofollow'));
            $loginform->set_var('verification_link', $verify);
        } else {
            $loginform->set_var('lang_verification', '');
            $loginform->set_var('verification_link', '');
        }


        if ($options['prefill_user'] && isset($_USER['username']) && $_USER['username'] != '' ) {
            $loginform->set_var('loginname',$_USER['username']);
            $loginform->set_var('focus', 'passwd');
        } else {
            $loginform->set_var('loginname','');
            $loginform->set_var('focus','loginname');
        }
        if ( $options['plugin_vars'] ) {
            PLG_templateSetVars('loginform', $loginform);
        }
        $loginform->parse('output', 'login');

        $retval .= $loginform->finish($loginform->get_var('output'));

        return $retval;
    }


    /**
    * Builds HTML login form and ask user to re-authenticate
    *
    * @param    string  $desturl    URL to return to after authentication
    * @param    string  $method     original request method: POST or GET
    * @param    string  $postdata   serialized POST data
    * @param    string  $getdata    serialized GET data
    * @param    string  $filedata   serialized FILE data
    * @return   string              HTML for the authentication form
    *
    */
    static public function reauthForm($desturl, $message = '', $prompt = '')
    {
        global $LANG20, $LANG_ADMIN;

        $hidden = '';

        if ( $desturl != '' ) {
            $hidden .= '<input type="hidden" name="token_returnurl" value="'.urlencode($desturl).'">' . LB;
        }

        $quotes = array('/"/',"/'/");
        $replacements = array('%22','%27');
        $desturl = preg_replace($quotes,$replacements,$desturl);

        $options = array(
            'forgotpw_link'   => false,
            'newreg_link'     => false,
            'oauth_login'     => false,
            'plugin_vars'     => false,
            'password_only'   => true,
            'prefill_user'    => false,
            'title'           => $LANG20[1],
            'message'         => $message,
            'prompt'          => $prompt,
            'footer_message'  => $LANG20[6],
            'button_text'     => $LANG_ADMIN['authenticate'],
            'form_action'     => $desturl,
            'hidden_fields'   => $hidden
        );

        return self::loginForm($options);
    }


    /**
    * Display a "to access this area you need to be logged in" message
    *
    * @return   string      HTML for the message
    *
    */
    public static function loginRequiredForm()
    {
        global $LANG_LOGIN;

        $options = array(
            'title'   => $LANG_LOGIN[1],
            'message' => $LANG_LOGIN[2]
        );

        return self::loginForm($options);
    }

    /**
     * Build out full page
     */

    static public function loginPage($options = array())
    {
        $display = '';

        $display = COM_siteHeader();
        $display .= self::loginForm($options);
        $display .= COM_siteFooter();
        echo $display;
        exit;

    }

    // new functions to support new registration system

    static public function registrationPage($data = array(), $messages = '')
    {
        $display = '';

        $display = COM_siteHeader('menu');
        $display .= self::registrationForm($data,$messages);
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }


    /**
    * Shows the user registration form
    *
    * @param    int     $msg        message number to show
    * @param    string  $referrer   page to send user to after registration
    * @return   string  HTML for user registration page
    */
    static public function registrationForm($info = array(), $messages = array())
    {
        global $_CONF, $_USER, $LANG01, $LANG04;

        $retval = '';

        // if user is already logged in - take them to their profile page
        if ( !COM_isAnonUser() ) {
            echo COM_refresh($_CONF['site_url'].'/users.php?mode=profile&uid='.$_USER['uid']);
        }

        // if new user registration is disabled - abort
        if ($_CONF['disable_new_user_registration']) {
            COM_setMsg($LANG04[122],'error');
            echo COM_refresh($_CONF['site_url']);
            exit;
        }

        // Hook into Custom Registration
        if ($_CONF['custom_registration'] AND (function_exists('CUSTOM_userForm'))) {
            return CUSTOM_userForm();
        }

        // defaults
        $options = array(
            'oauth_login'       => false,
            'show_password_entry' => true,    // dependent on registration type
            'show_email_confirmation' => true,
            'show_fullname'     => false,
            'plugin_vars'       => true,
            'registration_type' => 1,
            'form_action'       => $_CONF['site_url'].'/users.php',
        );

        // registration type can be
        //  0 - email user their password
        //  1 - user enters password and receives verification link
        if ( (isset($info['oauth_provider']) && !empty($info['oauth_provider']) ) || $_CONF['registration_type'] == 0 ) {
            $options['show_password_entry'] = false;
        }

        if ( (isset($info['oauth_provider']) && !empty($info['oauth_provider'])) && (isset($info['oauth_email']) && !empty($info['oauth_email'])) ) {
            $options['show_email_confirmation'] = false;
            if (!isset($info['email'])) {
                $info['email'] = $info['oauth_email'];
            }
        }

        if ( !isset($info['oauth_service'])) $info['oauth_service'] = '';

        // full name
        // 0 = no
        // 1 = optional
        // 2 = required
        if ( $_CONF['user_reg_fullname'] == 1 || $_CONF['user_reg_fullname'] == 2 ) {
            $options['show_fullname'] = true;
        }
        if ($_CONF['user_reg_fullname'] == 2) {
            $options['require_fullname'] = true;
        }

        $T = new \Template($_CONF['path_layout'].'users');
        $T->set_file('regform', 'registrationform.thtml');

        $T->set_var('form_action',$options['form_action']);

        $T->set_var(array(
            'lang_instructions' => sprintf($LANG04[23],$_CONF['site_name']),
            'lang_username'     => $LANG04[2],
            'lang_fullname'     => $LANG04[3],
            'lang_email'        => $LANG04[5],
            'lang_email_conf'   => $LANG04[124],
            'lang_register'     => $LANG04[27],
            'lang_passwd'       => $LANG01[57],
            'lang_passwd_conf'  => $LANG04[176],
            'lang_oauth_heading' => $LANG04[208],
            'lang_local_heading' => $LANG04[211],
            'lang_info_oauth'   => $LANG04[209],
            'lang_password_help'=> SEC_showPasswordHelp(),  // dynamic based on password rules
            'site_name'         => $_CONF['site_name'],
            'sec_token'         => SEC_createToken(),
            'sec_token_name'    => CSRF_TOKEN,
        ));

        if ( isset($info['oauth_provider']) && !empty($info['oauth_provider']) ) {
            $T->set_var('lang_action',sprintf($LANG04[210],$_CONF['site_name'],$LANG04[$info['oauth_service']]));
            $T->set_var('oauth_login',true);
        }

        if ( $_CONF['registration_type'] == 1 ) { // verification link
            $T->set_var('lang_warning', $LANG04[167]);
        } else {
            $T->set_var('lang_warning', $LANG04[24]);
        }

        if ($options['show_fullname']) {
            $T->set_var('show_fullname',true);
        }
        if ($_CONF['user_reg_fullname'] == 2) {
            $T->set_var('require_fullname',true);
        }
        if ($options['show_email_confirmation']) {
            $T->set_var('show_email_confirmation',true);
        }

        // registration type can be
        //  0 - email user their password
        //  1 - user enters password and receives verification link

        if ($options['show_password_entry']) {
            $T->set_var('show_password_entry',true);
        }

        foreach($info AS $item => $value) {
            $T->set_var($item,$value);
        }

        // Plugin Hook
        if ( $options['plugin_vars'] ) {
            PLG_templateSetVars('registration', $T);
        }

        // display any error messages
    //    $T->set_var('feedback',implode('<br>',$messages));
        $T->set_var('feedback',$messages);

        $T->parse('output', 'regform');
        $retval .= $T->finish($T->get_var('output'));

        return $retval;
    }


    /**
    * Display a form where the user can request a new token.
    *
    * @param uid       int      user id
    * @return          string   new token form
    *
    */
    static public function newTokenForm ()
    {
        global $_CONF, $LANG01, $LANG04;

        $tokenform = new \Template ($_CONF['path_layout'] . 'users');
        $tokenform->set_file ('newtoken', 'newtoken.thtml');
        $tokenform->set_var (array(
                'user_id'       => 1, // $uid,
                'lang_explain'  => $LANG04[175],
                'lang_username' => $LANG04[2],
                'lang_password' => $LANG01[57],
                'lang_submit'   => $LANG04[169]));

        $retval = COM_startBlock ($LANG04[169]);
        $retval .= $tokenform->finish ($tokenform->parse ('output', 'newtoken'));
        $retval .= COM_endBlock ();

        echo COM_siteHeader();
        echo $retval;
        echo COM_siteFooter();
        exit;
        return $retval;
    }

    /**
    * Shows the password retrieval form
    *
    * @return   string  HTML for form used to retrieve user's password
    *
    */
    static public function getPasswordPage()
    {
        global $_CONF, $LANG04;

        $form = '';

        $user_templates = new \Template($_CONF['path_layout'] . 'users');
        $user_templates->set_file('form', 'getpasswordform.thtml');
        $user_templates->set_var(array(
            'start_block_forgetpassword'    => COM_startBlock($LANG04[25]),
            'lang_instructions'             => $LANG04[26],
            'lang_username'                 => $LANG04[2],
            'lang_email'                    => $LANG04[5],
            'lang_emailpassword'            => $LANG04[28],
            'end_block'                     => COM_endBlock()
        ));

        PLG_templateSetVars('forgotpassword',$user_templates);

        $user_templates->parse('output', 'form');

        $form .= $user_templates->finish($user_templates->get_var('output'));

        echo COM_siteHeader();
        echo $form;
        echo COM_siteFooter();
        exit;
    }


    static public function newPasswordPage ($uid, $selector, $token)
    {
        global $_CONF, $_TABLES, $LANG04;

        $db = Database::getInstance();

        $pwform = new \Template ($_CONF['path_layout'] . 'users');
        $pwform->set_file ('newpw','newpassword.thtml');
        $pwform->set_var (array(
                'user_id'       => $uid,
                'user_name'     => $db->getItem ($_TABLES['users'], 'username',array('uid' => $uid),array(Database::INTEGER)),
                'selector'      => $selector,
                'token'         => $token,
                'password_help' => SEC_showPasswordHelp(),
                'lang_explain'  => $LANG04[90],
                'lang_username' => $LANG04[2],
                'lang_newpassword'  => $LANG04[4],
                'lang_newpassword_conf' => $LANG04[108],
                'lang_setnewpwd'    => $LANG04[91])
        );

        $form = COM_startBlock ($LANG04[92]);
        $form .= $pwform->finish ($pwform->parse ('output', 'newpw'));
        $form .= COM_endBlock ();

        echo COM_siteHeader('menu');
        echo $form;
        echo COM_siteFooter();
        exit;
    }

    /**
    * Displays the Two Factor Auth token entry form
    *
    * @return   no return
    *
    */
    static public function TFAvalidationPage($uid)
    {
        global $_CONF, $LANG_TFA;

        $retval = '';

        $T = new \Template($_CONF['path_layout'] . 'users');
        $T->set_file('tfa', 'tfa-entry-form.thtml');

        $T->set_var(array(
            'uid'           => $uid,
            'token_name'    => CSRF_TOKEN,
            'token_value'   => SEC_createToken(),
            'lang_two_factor'   => $LANG_TFA['two_factor'],
            'lang_auth_code'    => $LANG_TFA['auth_code'],
            'lang_verify'       => $LANG_TFA['verify'],
        ));

        $T->parse('output', 'tfa');

        $retval .= $T->finish($T->get_var('output'));

        $display = COM_siteHeader();
        $display .= $retval;
        $display .= COM_siteFooter();
        echo $display;
        exit;
    }

    /**
    * Shows a profile for a user
    *
    * This grabs the user profile for a given user and displays it
    *
    * @return   string          HTML for user profile page
    *
    */
    static public function userProfile()
    {
        global $_CONF, $_TABLES, $_USER, $LANG01, $LANG04, $LANG09, $LANG28, $LANG_LOGIN;
        global $LANG_ADMIN;

        USES_lib_user();

        $db = Database::getInstance();
        $CommentEngine = \glFusion\Comments\CommentEngine::getEngine();

        $retval = '';

        if (COM_isAnonUser() &&
            (($_CONF['loginrequired'] == 1) || ($_CONF['profileloginrequired'] == 1))) {
            self::loginPage();
        }

        if ( isset($_GET['uid']) ) {
            $user = COM_applyFilter ($_GET['uid'], true);
            if (!is_numeric ($user) || ($user < 2)) {
                COM_404();
            }
        } else if ( isset($_GET['username']) ) {
            $username = $_GET['username'];
            if ( !USER_validateUsername($username,true) ) {
                COM_404();
            }
            if ( empty($username) || $username == '' ) {
                COM_404();
            }

            $user = (int) $db->getItem(
                            $_TABLES['users'],
                            'uid',
                            array('username'=>$username),
                            array(Database::STRING)
                          );

            if ($user < 2) {
                COM_404();
            }
        } else {
            COM_404();
        }
        $msg = 0;
        if (isset ($_GET['msg'])) {
            $msg = COM_applyFilter ($_GET['msg'], true);
        }
        $plugin = '';

        if (($msg > 0) && isset($_GET['plugin'])) {
            $plugin = COM_applyFilter($_GET['plugin']);
        }

        $A = $db->conn->fetchAssoc(
                    "SELECT u.uid,username,fullname,regdate,lastlogin,homepage,about,location,pgpkey,photo,email,status,emailfromadmin,emailfromuser,showonline
                     FROM `{$_TABLES['userinfo']}` AS ui,`{$_TABLES['userprefs']}` AS up,`{$_TABLES['users']}` AS u
                     WHERE ui.uid = u.uid AND ui.uid = up.uid AND u.uid = ?",
                    array($user),
                    array(Database::INTEGER)
        );
        if ($A === false || $A === null) {
            COM_404();
        }

        if ($A['status'] == USER_ACCOUNT_DISABLED && !SEC_hasRights ('user.edit')) {
            COM_displayMessageAndAbort (30, '', 403, 'Forbidden');
        }

        $display_name = @htmlspecialchars(COM_getDisplayName($user, $A['username'],$A['fullname']),ENT_COMPAT,COM_getEncodingt());

        if ($msg > 0) {
            $retval .= COM_showMessage($msg, $plugin,'',0,'info');
        }

        // format date/time to user preference
        $curtime = COM_getUserDateTimeFormat ($A['regdate']);
        $A['regdate'] = $curtime[0];

        $user_templates = new \Template ($_CONF['path_layout'] . 'users');
        $user_templates->set_file (array ('profile' => 'profile.thtml',
                                          'email'   => 'email.thtml',
                                          'row'     => 'commentrow.thtml',
                                          'strow'   => 'storyrow.thtml'));
        $user_templates->set_var ('layout_url', $_CONF['layout_url']);
        $user_templates->set_var ('start_block_userprofile',
                COM_startBlock ($LANG04[1] . ' ' . $display_name));
        $user_templates->set_var ('end_block', COM_endBlock ());
        $user_templates->set_var ('lang_username', $LANG04[2]);
        $user_templates->set_var ('tooltip', COM_getTooltipStyle());

        if ($_CONF['show_fullname'] == 1) {
            if (empty ($A['fullname'])) {
                $username = $A['username'];
                $fullname = '';
            } else {
                $username = $A['fullname'];
                $fullname = $A['username'];
            }
        } else {
            $username = $A['username'];
            $fullname = '';
        }
        $username = @htmlspecialchars($username,ENT_COMPAT,COM_getEncodingt());
        $fullname = @htmlspecialchars($fullname,ENT_COMPAT,COM_getEncodingt());

        if ($A['status'] == USER_ACCOUNT_DISABLED) {
            $username = sprintf ('%s - %s', $username, $LANG28[42]);
            if (!empty ($fullname)) {
                $fullname = sprintf ('% - %s', $fullname, $LANG28[42]);
            }
        }

        $user_templates->set_var ('username', $username);
        $user_templates->set_var ('user_fullname', $fullname);

        if (SEC_hasRights('user.edit') || (isset($_USER['uid']) && $_USER['uid'] == $A['uid'])) {
            $edit_icon = '<img src="' . $_CONF['layout_url'] . '/images/edit.png'
                       . '" alt="' . $LANG_ADMIN['edit']
                       . '" title="' . $LANG_ADMIN['edit'] . '" />';
            if ($_USER['uid'] == $A['uid']) {
                $edit_url = "{$_CONF['site_url']}/usersettings.php";
            } else {
                $edit_url = "{$_CONF['site_admin_url']}/user.php?edit=x&amp;uid={$A['uid']}";
            }

            $edit_link_url = COM_createLink($edit_icon, $edit_url);
            $user_templates->set_var('edit_icon', $edit_icon);
            $user_templates->set_var('edit_link', $edit_link_url);
            $user_templates->set_var('user_edit', $edit_url);
        } else {
            $user_templates->set_var('user_edit', '');
        }

        if (isset ($A['photo']) && empty ($A['photo'])) {
            $A['photo'] = '(none)'; // user does not have a photo
        }

        $lastlogin = $A['lastlogin'];
        $lasttime = COM_getUserDateTimeFormat ($lastlogin);

        $photo = \USER_getPhoto ($user, $A['photo'], $A['email'], -1,0);
        $user_templates->set_var ('user_photo', $photo);

        $user_templates->set_var ('lang_membersince', $LANG04[67]);
        $user_templates->set_var ('user_regdate', $A['regdate']);

        if ($_CONF['lastlogin'] && $A['showonline']) {
            $user_templates->set_var('lang_lastlogin', $LANG28[35]);
            if ( !empty($lastlogin) ) {
                $user_templates->set_var('user_lastlogin', $lasttime[0]);
            } else {
                $user_templates->set_var('user_lastlogin', $LANG28[36]);
            }
        }

        if ($A['showonline']) {
            if ( $db->getCount($_TABLES['sessions'],'uid',$user,Database::INTEGER)) {
                $user_templates->set_var ('online', 'online');
            }
        }

        $user_templates->set_var ('lang_email', $LANG04[5]);
        $user_templates->set_var ('user_id', $user);

        if ( $A['email'] == '' || $A['emailfromuser'] == 0 ) {
            $user_templates->set_var ('email_option', '');
        } else {
            $user_templates->set_var ('lang_sendemail', $LANG04[81]);
            $user_templates->parse ('email_option', 'email', true);
        }

        $user_templates->set_var ('lang_homepage', $LANG04[6]);
        $user_templates->set_var ('user_homepage', COM_killJS ($A['homepage']));
        $user_templates->set_var ('lang_location', $LANG04[106]);
        $user_templates->set_var ('user_location', strip_tags ($A['location']));
        $user_templates->set_var ('lang_online', $LANG04[160]);
        $user_templates->set_var ('lang_bio', $LANG04[7]);
        $user_templates->set_var ('user_bio', PLG_replaceTags(nl2br ($A['about']),'glfusion','about_user'));

        $user_templates->set_var('follow_me',\glFusion\Social\Social::getFollowMeIcons( $user, 'follow_user_profile.thtml' ));

        $user_templates->set_var ('lang_pgpkey', $LANG04[8]);
        $user_templates->set_var ('user_pgp', nl2br ($A['pgpkey']));
        $user_templates->set_var ('start_block_last10stories',
                COM_startBlock ($LANG04[82] . ' ' . $display_name));

        if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
            $user_templates->set_var ('start_block_last10comments',
                    COM_startBlock($LANG04[10] . ' ' . $display_name));
        }
        $user_templates->set_var ('start_block_postingstats',
                COM_startBlock ($LANG04[83] . ' ' . $display_name));
        $user_templates->set_var ('lang_title', $LANG09[16]);
        $user_templates->set_var ('lang_date', $LANG09[17]);

        // for alternative layouts: use these as headlines instead of block titles
        $user_templates->set_var ('headline_last10stories', $LANG04[82] . ' ' . $display_name);
        if (!isset($_CONF['comment_engine']) || $_CONF['comment_engine'] == 'internal') {
            $user_templates->set_var ('headline_last10comments', $LANG04[10] . ' ' . $display_name);
        }
        $user_templates->set_var ('headline_postingstats', $LANG04[83] . ' ' . $display_name);

        $tids = array ();
        $stmt = $db->conn->query(
                    "SELECT tid FROM `{$_TABLES['topics']}`" . $db->getPermSQL()
        );
        while ($T = $stmt->fetch(Database::ASSOCIATIVE)) {
            $tids[] = $T['tid'];
        }
        $topics = "'" . implode ("','", $tids) . "'";

        // list of last 10 stories by this user
        if (count($tids) > 0) {
            $sql = "SELECT sid,title,UNIX_TIMESTAMP(date) AS unixdate
                     FROM `{$_TABLES['stories']}` WHERE (uid = ?)
                           AND (draft_flag = 0)
                           AND (date <= ?)
                           AND (tid IN (?))" . $db->getPermSQL('AND') .
                    " ORDER BY unixdate DESC LIMIT 10";

            $stmt = $db->conn->executeQuery(
                        $sql,
                        array(
                            $user,
                            $_CONF['_now']->toMySQL(true),
                            $tids
                        ),
                        array(
                            Database::INTEGER,
                            Database::STRING,
                            Database::PARAM_STR_ARRAY
                        )
            );
            $last10Stories = $stmt->fetchAll(Database::ASSOCIATIVE);
        } else {
            $last10Stories = array();
        }
        $i = 0;
        if (count($last10Stories) > 0) {
            foreach($last10Stories AS $C) {
                $user_templates->set_var ('cssid', ($i % 2) + 1);
                $user_templates->set_var ('row_number', ($i + 1) . '.');
                $articleUrl = COM_buildUrl ($_CONF['site_url'].'/article.php?story=' . $C['sid']);
                $user_templates->set_var ('article_url', $articleUrl);
                $C['title'] = str_replace ('$', '&#36;', $C['title']);
                $user_templates->set_var ('story_title',
                    COM_createLink(
                        $C['title'],
                        $articleUrl,
                        array ('class'=>''))
                );
                $storytime = COM_getUserDateTimeFormat ($C['unixdate']);
                $user_templates->set_var ('story_date', $storytime[0]);
                $user_templates->parse ('story_row', 'strow', true);
                $i++;
            }
        } else {
            $user_templates->set_var ('story_row','<tr><td>' . $LANG01[37] . '</td></tr>');
        }

        $commentCounter = 0;
        $Comments = $CommentEngine->getLastX($user);
        if (count($Comments) > 0) {
            $user_templates->set_block('profile', 'comment_row', 'commentRow');
            foreach ($Comments as $row) {
                // Get the plugin item info to be sure it wasn't deleted.
                $itemInfo = PLG_getItemInfo($row['type'], $row['sid'],'id');
                if ( is_array($itemInfo) || $itemInfo == '' ) {
                    continue;
                }
                $user_templates->set_var ('cssid', ($commentCounter % 2) + 1);
                $user_templates->set_var ('row_number', ($commentCounter + 1) . '.');
                $row['title'] = html_entity_decode(str_replace ('$', '&#36;', $row['title']));
                $comment_url = $_CONF['site_url'] .
                        '/comment.php?mode=view&amp;cid=' . $row['cid'] . '#cid_' . $row['cid'];
                $user_templates->set_var ('comment_title',
                    COM_createLink(
                        $row['title'],
                        $comment_url,
                        array ('class'=>''))
                );
                $commenttime = COM_getUserDateTimeFormat ($row['date']);
                $user_templates->set_var ('comment_date', $commenttime[0]);
                $user_templates->parse ('commentRow', 'comment_row', true);
                $commentCounter++;
            }
        }

        // posting stats for this user
        $user_templates->set_var ('lang_number_stories', $LANG04[84]);

        $storyCount = (int) $db->conn->fetchColumn(
                        "SELECT COUNT(*) AS count
                         FROM `{$_TABLES['stories']}`
                         WHERE (uid = ?) AND (draft_flag = 0) AND (date <= ?)" . $db->getPermSQL('AND'),
                        array($user,$_CONF['_now']->toMySQL(true)),
                        0,
                        array(Database::INTEGER,Database::STRING)
        );
        $user_templates->set_var ('number_stories', COM_numberFormat($storyCount));
        $commentCount = $CommentEngine->getCountByUser($user);
        if ($commentCount > 0) {
            $user_templates->set_var ('lang_number_comments', $LANG04[85]);
            $user_templates->set_var ('number_comments', COM_numberFormat($commentCount));
        }
        $user_templates->set_var ('lang_all_postings_by',
                                  $LANG04[86] . ' ' . $display_name);
        // hook to the profile icon display

        $profileIcons = PLG_profileIconDisplay($user);
        if ( is_array($profileIcons) && count($profileIcons) > 0 ) {
            $user_templates->set_block('profile', 'profileicon', 'pi');
            for ($x=0;$x<count($profileIcons);$x++) {
                if ( isset($profileIcons[$x]['url']) && $profileIcons[$x]['url'] != '' && isset($profileIcons[$x]['icon']) && $profileIcons[$x]['icon'] != '' ) {
                    $user_templates->set_var('profile_icon_url',$profileIcons[$x]['url']);
                    $user_templates->set_var('profile_icon_icon',$profileIcons[$x]['icon']);
                    $user_templates->set_var('profile_icon_text',$profileIcons[$x]['text']);
                    $user_templates->parse('pi', 'profileicon',true);
                }
            }
        }

        // Call custom registration function if enabled and exists
        if ($_CONF['custom_registration'] && function_exists ('CUSTOM_userDisplay') ) {
            $user_templates->set_var ('customfields', CUSTOM_userDisplay ($user));
        }
        PLG_profileVariablesDisplay ($user, $user_templates);

        $user_templates->parse ('output', 'profile');
        $retval .= $user_templates->finish ($user_templates->get_var ('output'));

        $retval .= PLG_profileBlocksDisplay ($user);

        return $retval;
    }



}
