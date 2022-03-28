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
        global $_CONF, $LANG_LOGIN;

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
        global $_CONF, $_TABLES, $LANG01, $LANG04;

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
    static public function getPasswordForm()
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


}