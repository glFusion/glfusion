<?php
/**
* glFusion CMS - CAPTCHA Plugin
*
* Plugin APIs
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

define('CAPTCHA_GDLIB', 0);
define('CAPTCHA_IMGMAGICK', 1); // retired
define('CAPTCHA_STATICIMG', 2); // retired
define('CAPTCHA_RECAPTCHA', 3);
define('CAPTCHA_MATH', 6);

// load the language file(s), including custom strings if any

$langfile = $_CONF['path'].'plugins/captcha/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'].'plugins/captcha/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/captcha/language/english_utf-8.php';
    $custfile = $_CONF['path'].'plugins/captcha/language/custom/english_utf-8.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}

require_once $_CONF['path'].'plugins/captcha/captcha.php';

if (!isset($_CP_CONF['gfxDriver'])) {
    $cp_config = \config::get_instance();
    $_CP_CFG = $cp_config->get_config('captcha');

    if ( is_array($_CP_CONF)  && is_array($_CP_CFG) ) {
        $_CP_CONF = array_merge($_CP_CONF, $_CP_CFG);
    }
    unset($_CP_CFG);
}

if ( !isset($_CP_CONF['gfxDriver'])) $_CP_CONF['gfxDriver'] = CAPTCHA_MATH;

if ( isset($_CP_CONF['gfxDriver']) && $_CP_CONF['gfxDriver'] == 3 ) {
    if ( $_CP_CONF['publickey'] == '' || $_CP_CONF['privatekey'] == '' ) {
        $_CP_CONF['gfxDriver'] = 2;
    } else {
        require_once $_CONF['path'] . 'plugins/captcha/class/autoload.php';
    }

}
if ( isset($_CP_CONF['gfxDriver']) && $_CP_CONF['gfxDriver'] == 5 ) {
    $_CP_CONF['gfxDriver'] = 2;
}

if ( !isset($_CP_CONF['enable_loginform'])) $_CP_CONF['enable_loginform'] = 0;
if ( !isset($_CP_CONF['enable_forgotpassword'])) $_CP_CONF['enable_forgotpassword'] = 0;

switch ($_CP_CONF['gfxDriver']) {
    case CAPTCHA_STATICIMG :
        $_CP_CONF['gfxDriver'] = CAPTCHA_GDLIB;
        // fall through on purpose
    case CAPTCHA_GDLIB :
    case CAPTCHA_MATH :
        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            // no gd lib support
            $_CP_CONF['enable_loginform'] = 0;
            $_CP_CONF['remoteusers'] = 0;
            $_CP_CONF['enable_comment'] = 0;
            $_CP_CONF['enable_story'] = 0;
            $_CP_CONF['enable_registration'] = 0;
            $_CP_CONF['enable_forgotpassword'] = 0;
            $_CP_CONF['enable_contact'] = 0;
            $_CP_CONF['enable_emailstory'] = 0;
            $_CP_CONF['enable_forum'] = 0;
            $_CP_CONF['enable_mediagallery'] = 0;
            $_CP_CONF['enable_links'] = 0;
            $_CP_CONF['enable_calendar'] = 0;
            $_CP_CONF['disabled_error'] = 1;
        }
        break;
}

$_CP_CONF['gfxFormat'] = 'png';

/* Auto loader */

spl_autoload_register('autoloader_captcha');

function autoloader_captcha($class) {
    global $_CONF;
    if (strpos($class, 'Captcha\\') === 0) {
        $class = str_replace('Captcha\\', '', $class);
    }
    $path = $_CONF['path'].'plugins/captcha/class/' . $class . '.class.php';
    if (file_exists($path)) {
        include $path;
    }
}

/**
 * Returns URL for the plugin's icon
 *
 * @return  string  URL to icon
 */
function plugin_geticon_captcha ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/captcha/captcha.png';
}

/**
 * Enable / disable plugin - called from plugin admin console
 *
 * If running in Demo mode - never disable the plugin
 *
 * @param   boolean enable state
 * @return  true
 */

function plugin_enablestatechange_captcha($enable) {

    if ( defined('DEMO_MODE') && $enable == false ) {
        return 99;
    }
    return 1;
}

/**
 * Set template variable with CAPTCHA image
 *
 * This plugin uses the 'templatesetvars' hook to insert the CAPTCHA
 * challenge in user accessible scripts. This allows users to create
 * or edit content of type specified ($ctype). The template handle
 * relating to the input/edit form for the content type is passed
 * to allow the corresponding captcha template variable to be set.
 *
 * @param   string  CAPTCHA type - comment, registration, etc.
 * @param   class   Template class handle
 * @return  string  blank
 */
function plugin_templatesetvars_captcha($ctype, &$template)
{
    global $_CONF, $_CP_CONF, $_USER, $_TABLES;

    static $jsLoaded = false;

    // establish whether we are offering the challenge for this content type
    // and if so, initially clear the 'captcha' template var

    switch( $ctype ) {
        case 'comment' :
            if ( $_CP_CONF['enable_comment'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'story' :
            if ( $_CP_CONF['enable_story'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'registration' :
            if ( $_CP_CONF['enable_registration'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'loginform' :
            if ( $_CP_CONF['enable_loginform'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;

        case 'forgotpassword' :
            if ( $_CP_CONF['enable_forgotpassword'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;

        case 'contact' :
            if ( $_CP_CONF['enable_contact'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'emailstory' :
            if ( $_CP_CONF['enable_emailstory'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'forum' :
            if ( $_CP_CONF['enable_forum'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'mediagallery' :
            if ( $_CP_CONF['enable_mediagallery'] != 1 ) {
                $template->set_var('captcha', '');
                return;
            }
            break;
        case 'links' :
            if ( $_CP_CONF['enable_links'] != 1 ) {
                $template->set_var('captcha','');
                return;
            }
            break;
        case 'calendar' :
            if ( $_CP_CONF['enable_calendar'] != 1 ) {
                $template->set_var('captcha','');
                return;
            }
            break;
        case 'token' :
            break;

        // for the 'general' content type case, there is no template var, and we are returning raw CAPTCHA_html

        case 'general' :
            break;

        // the content type was null, or unrecognizable, so therefore there can be no plugin config
        // option controlling it, and we should just clear the 'captcha' template var

        default :
            return;
            break;
    }

    if (function_exists('imagettftext') == FALSE && ( $_CP_CONF['gfxDriver'] == 0 || $_CP_CONF['gfxDriver'] == 6) ) {
        return;
    }

    if ( COM_isAnonUser() ) {
        $_USER['uid'] = 1;
    }
    if ( $ctype == 'token' ||
        ($_CP_CONF['anonymous_only'] && COM_isAnonUser() ) ||
        $_CP_CONF['anonymous_only'] == 0 ||
        ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) &&
        !SEC_inGroup('Root')
        ) {

        if ( $_CP_CONF['gfxDriver'] == 3 && $jsLoaded == false ) {
            $outputHandle = outputHandler::getInstance();
            $outputHandle->addLinkScriptFooter('https://www.google.com/recaptcha/api.js?onload=onloadCallbackRC&render=explicit&hl='.$_CONF['iso_lang'],HEADER_PRIO_VERYLOW,'text/javascript',true);
            $outputHandle->addLinkScript($_CONF['site_url'].'/captcha/recaptcha.js');
            $jsLoaded = true;
        }

        // expire stale CAPTCHA sessions
        CAPTCHA_expireSessions();

        // create a CAPTCHA session, or use the existing session if user entry failed
        $csid = CAPTCHA_getSession($ctype);
        // retrieve the CAPTCHA challenge in HTML form.  pass the CAPTCHA session id (csid)
        // and the format of the challenge is controlled by template selected by content type (ctype)
        $captcha = CAPTCHA_getHTML($csid, $ctype);

        if ( $_CP_CONF['gfxDriver'] == 3 ) $template->set_var('recaptcha',true);

        // if our content type (ctype) was specified as 'general'
        if ( $ctype == 'general' ) {
            return $captcha;
        }

        $template->set_var('captcha', $captcha,false,true);


    } else {
        $template->set_var('captcha', '');
    }

    return '';
}

/**
 * Plugin hook to content's pre-save - checks user's response
 *
 * @param   integer user id
 * @param   string  content title
 * @param   string  content text
 * @param   string  content SID
 * @param   string  content PID
 * @param   string  content type
 * @param   string  content postmode (i.e. HTML, TEXT)
 * @return  boolean 0 / 1
 */
function plugin_commentPreSave_captcha($uid, $title, $comment, $sid, $pid, $type, $postmode) {
    global $_CP_CONF, $_USER;

    if ( $_CP_CONF['enable_comment'] != 1 ) {
        return 0;
    }

    if (COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    if ( ($_CP_CONF['anonymous_only'] && $uid < 2) ||
        $_CP_CONF['anonymous_only'] == 0 ||
        ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) &&
        !SEC_inGroup('Root')
        ) {
        if ( isset($_POST['captcha']) ) {
            $str = COM_applyFilter($_POST['captcha']);
        } else {
            $str = '';
        }
        list( $rc, $msg) = CAPTCHA_checkInput( $str, 'comment' );
        if ( $rc == 1 ) {
            return 0;
        } else {
            if ( SESS_isSet('glfusion.commentpresave.error') ) {
                $msg = SESS_getVar('glfusion.commentpresave.error') . '<br/>' . $msg;
            }
            SESS_setVar('glfusion.commentpresave.error',$msg);
            return 1;
        }
    }
    return 0;
}

/**
 * Plugin hook to content's pre-save - checks user's response
 *
 * @param   integer user id
 * @param   string  content type
 * @param   string  content
 * @return  string  error message - blank signals success
 */
function plugin_itemPreSave_captcha( $ctype='registration', $content='' ) {
    global $_CP_CONF, $_USER;

    $msg = '';

    switch( $ctype ) {
        case 'comment' :
            if ( $_CP_CONF['enable_comment'] != 1 ) {
                return '';
            }
            break;
        case 'story' :
            if ( $_CP_CONF['enable_story'] != 1 ) {
                return '';
            }
            break;
        case 'registration' :
            if ( $_CP_CONF['enable_registration'] != 1 ) {
                return '';
            }
            break;
        case 'loginform' :
            if ( $_CP_CONF['enable_loginform'] != 1 ) {
                return '';
            }
            break;

        case 'forgotpassword' :
            if ( $_CP_CONF['enable_forgotpassword'] != 1 ) {
                return '';
            }
            break;

        case 'contact' :
            if ( $_CP_CONF['enable_contact'] != 1 ) {
                return '';
            }
            break;
        case 'emailstory' :
            if ( $_CP_CONF['enable_emailstory'] != 1 ) {
                return '';
            }
            break;
        case 'forum' :
            if ( $_CP_CONF['enable_forum'] != 1 ) {
                return '';
            }
            break;
        case 'mediagallery' :
            if ( $_CP_CONF['enable_mediagallery'] != 1 ) {
                return '';
            }
            break;
        case 'links' :
            if ( $_CP_CONF['enable_links'] != 1 ) {
                return '';
            }
            break;
        case 'calendar' :
            if ( $_CP_CONF['enable_calendar'] != 1 ) {
                return '';
            }
            break;
        case 'token' :
        case 'general' :
            break;
        default :
            return '';
    }
    if ( COM_isAnonUser() ) {
        $_USER['uid'] = 1;
    }
    if ( ($_CP_CONF['anonymous_only'] && COM_isAnonUser() ) ||
          $_CP_CONF['anonymous_only'] == 0 ||
          ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) &&
          !SEC_inGroup('Root')
          ) {
        if ( isset($_POST['captcha']) ) {
            $response = COM_applyFilter($_POST['captcha']);
        } else {
            $response = '';
        }
        list( $rc, $msg )  = CAPTCHA_checkInput( $response, $ctype );
        return $msg;
    }
    return '';
}

/**
 * Return plugin's version information
 *
 * @return  string   plugin version number
 */
function plugin_chkVersion_captcha() {
    global $_CP_CONF;

    return $_CP_CONF['pi_version'];
}

/**
 * Upgrade plugin
 *
 * @return  string   plugin version number
 */
function plugin_upgrade_captcha() {
    global $_CONF, $_TABLES;

    require_once $_CONF['path'].'plugins/captcha/upgrade.php';

    return captcha_upgrade();
}

function plugin_getconfigelementhelp_captcha($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/captcha.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/captcha.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

function plugin_getheaderjs_captcha()
{
    global $_CONF, $_CP_CONF;

    $js = array();
    if ( $_CP_CONF['gfxDriver'] == 3 ) {
//        $js[] = $_CONF['path'].'plugins/captcha/recaptcha.js';
    }
	return ($js);
}

function plugin_getheadercode_captcha()
{
    global $_CONF, $_CP_CONF;
/*
    if ( $_CP_CONF['gfxDriver'] == 3 ) {
        $outputHandle = outputHandler::getInstance();
        $outputHandle->addLinkScriptFooter('https://www.google.com/recaptcha/api.js?onload=onloadCallbackRC&render=explicit&hl='.$_CONF['iso_lang'],HEADER_PRIO_VERYLOW,'text/javascript',true);
        $outputHandle->addLinkScript($_CONF['site_url'].'/captcha/recaptcha.js');
    }
*/
}
/**
 * Create unique session id for CAPTCHA
 *
 * @return  string   session id
 */
function CAPTCHA_sid() {

    if (function_exists('openssl_random_pseudo_bytes')) {
        return substr(bin2hex(openssl_random_pseudo_bytes(32)),3,24);
    } else {
        $sid = date( 'YmdHis' );
        $sid .= mt_rand( 0, 999 );

        $val = $sid . microtime();
        $val = md5($val);
        $rand_seed = md5($sid . $val);
        $id = substr($val, 3, 18);

        return $id;
    }


//    return dechex(time()).dechex(mt_rand(1,65535));
}

/**
 * Create a new, or refresh existing, session id.
 *
 * @return  string   session id
 */
function CAPTCHA_getSession($ctype = '') {
    global $_CP_CONF, $_TABLES;

    $counter = 0;

    if ( $ctype == 'token' ) {
        if ( isset($_POST['csidt']) ) {
            $csid = COM_applyFilter($_POST['csidt']);
            $counter = (int) DB_getItem($_TABLES['cp_sessions'],'counter','session_id="'.DB_escapeString($csid).'"');
        } else {
            $csid    = CAPTCHA_sid();
            $counter = 0;
        }
    } else {
        // if (csid) is in POST array, this is a reuse of an old session, else create a new one
        if ( isset($_POST['csid']) ) {
            $csid = COM_applyFilter($_POST['csid']);
            $counter = (int) DB_getItem($_TABLES['cp_sessions'],'counter','session_id="'.DB_escapeString($csid).'"');
        } else  {
            $csid    = CAPTCHA_sid();
            $counter = 0;
        }
    }
    // set the current time
    $time    = time();

    // this will be filled in by the CAPTCHA
    $validation = '';
    $ip = $_SERVER['REAL_ADDR'];

    // update the session table
    if ( $_CP_CONF['gfxDriver'] != 3 ) {
        DB_save($_TABLES['cp_sessions'],"session_id,cptime,validation,counter,ip","'".DB_escapeString($csid)."','$time','',$counter,'$ip'");
    }
    // return the CAPTCHA session identifier
    return $csid;
}

/**
 * Prune CAPTCHA session table by expiring old sessions
 *
 * @return  none
 */
function CAPTCHA_expireSessions() {
    global $_CP_CONF, $_TABLES;
    if ( $_CP_CONF['gfxDriver'] == 3 ) return;
    $oldSessions = time() - ($_CP_CONF['expire']+3600);
    DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE cptime < " . $oldSessions,1);
    return;
}

/**
 * Create HTML to be inserted in content based on current
 * session id and content type
 *
 * @param   string  session id
 * @param   string  content type
 * @return  string  HTML
 */
function CAPTCHA_getHTML($csid, $ctype='general' ) {
    global $_CONF, $_CP_CONF, $LANG_CP00;

    $retval = '';

    static $captchaCounter = 0;

    $T = new \Template($_CONF['path'] . 'plugins/captcha/templates');
    switch ( $ctype ) {
        case 'comment' :
            $T->set_file('page', 'captcha_comment.thtml');
            break;
        case 'story' :
            $T->set_file('page', 'captcha_story.thtml');
            break;
        case 'registration' :
            $T->set_file('page', 'captcha_registration.thtml');
            break;
        case 'loginform' :
            $T->set_file('page', 'captcha_loginform.thtml');
            break;
        case 'forgotpassword' :
            $T->set_file('page', 'captcha_forgotpassword.thtml');
            break;
        case 'contact' :
            $T->set_file('page', 'captcha_contact.thtml');
            break;
        case 'emailstory' :
            $T->set_file('page', 'captcha.thtml');
            break;
        case 'links' :
            $T->set_file('page', 'captcha_links.thtml');
            break;
        case 'calendar' :
            $T->set_file('page', 'captcha_calendar.thtml');
            break;
        case 'forum' :
            $T->set_file('page', 'captcha_forum.thtml');
            break;
        case 'mediagallery' :
            $T->set_file('page', 'captcha_mediagallery.thtml');
            break;
        case 'token' :
            $T->set_file('page','captcha_token.thtml');
            break;
        case 'general' :
            $T->set_file('page', 'captcha.thtml');
            break;
        default :
            return '';
            break;
    }
    $T->set_var(array(
        'lang_refresh'          => $LANG_CP00['reload'],
        'lang_alt'              => $LANG_CP00['captcha_alt'],
        'captcha_image'         => $_CONF['site_url'] . '/captcha/captcha.php?csid=' . $csid . '&amp;.' . ($_CP_CONF['gfxFormat'] != '' ? $_CP_CONF['gfxFormat'] : 'jpg'),
        'lang_reload'           => $LANG_CP00['reload'],
        'lang_reload_failed'    => $LANG_CP00['reload_failed'],
        'lang_too_many'         => $LANG_CP00['reload_too_many'],
        'csid'                  => $csid,
        'lang_are_you_human'    => $LANG_CP00['captcha_prompt'],
        'counter'               => $captchaCounter,
    ),false,true);

    $captchaCounter++;

    if ( $_CP_CONF['gfxDriver'] == 6 ) {
        $T->set_var('lang_captcha_help', $LANG_CP00['captcha_math']);
    } else {
        $T->set_var('lang_captcha_help', $LANG_CP00['captcha_help']);
    }
    if ( $_CP_CONF['gfxDriver'] == 3 ) {
        $T->set_var('recaptcha', recaptcha_get_html($_CP_CONF['publickey'], $_CP_CONF['recaptcha_theme']));
        $T->set_var('recaptcha_theme',$_CP_CONF['recaptcha_theme']);
    }

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function recaptcha_get_html($pkey,$theme = 'light')
{
    global $_CONF;

    static $recaptchaCounter = 0;

    $retval = '';
    $retval .= '<div class="g-recaptcha" id="recaptcha_'.$recaptchaCounter.'" data-sitekey="'
             . $pkey . '" data-callback="cp_enable" data-theme="'.$theme.'"></div>';

    $recaptchaCounter++;

    return $retval;
}

function CAPTCHA_checkRetries( $csid )
{
    global $_CONF, $_TABLES, $_SYSTEM;

    if ( !isset($_SYSTEM['max_captcha_attempts']) ) {
        $_SYSTEM['max_captcha_atttempts'] = 3;  // this + 1 is max tries
    }

    $counter = (int) DB_getItem($_TABLES['cp_sessions'],'counter',"session_id='" . DB_escapeString($csid) . "'");

    if ( $counter > $_SYSTEM['max_captcha_atttempts'] ) {
        if ( function_exists('bb2_ban') ) {
            CAPTCHA_errorLog("CAPTCHA: Banning for too many retries - IP Address: " . $_SERVER['REAL_ADDR'] );
            bb2_ban($_SERVER['REAL_ADDR'],2);
        }
    } else {
        $counter++;
        $sql = "UPDATE {$_TABLES['cp_sessions']} SET counter=".(int) $counter ." WHERE session_id='".DB_escapeString($csid)."'";
        DB_query($sql,1);
    }
}

/**
 * Verify user's response to CAPTCHA challenge
 *
 * @param   string  user' response
 * @param   string  content type
 * @return  array   array containing boolean true / false and error message
 */
function CAPTCHA_checkInput( $response, $ctype='general' ) {
    global $_CP_CONF, $_TABLES, $LANG_CP00, $_SERVER;
    // if rc=0 on return, this is failure, and msg will contain detail
    $msg = '';
    $rc = 0;

    // expire stale CAPTCHA sessions
    CAPTCHA_expireSessions();

    if ( isset($_POST['validation_field']) && !empty($_POST['validation_field']) ) {
        CAPTCHA_errorLog("CAPTCHA: Honey Pot Field was populated in " . $ctype . " - IP Address: " . $_SERVER['REAL_ADDR'] );
        // immediate ban!
        if ( function_exists('bb2_ban') ) {
            bb2_ban($_SERVER['REAL_ADDR'],2);
        }
        return array(0,$LANG_CP00['entry_error']);
    }

    // check the session data prior to checking the external items

    $csid_name = 'csid';
    if ( $ctype == 'token' ) {
        $csid_name = 'csidt';
    }

    // if the csid is not set - an attempt to bypass
    if ( !isset($_POST[$csid_name]) ) {
        // immediate ban!
        if ( function_exists('bb2_ban') ) {
            bb2_ban($_SERVER['REAL_ADDR'],2);
        }
        CAPTCHA_errorLog("CAPTCHA: Detected an attempt to bypass CAPTCHA (no session id) in " . $ctype . " - IP Address: " . $_SERVER['REAL_ADDR'] );
        $msg = $LANG_CP00['bypass_error_blank'];
        $rc = 0;
        return ( array( $rc, $msg ) );
    } else {
        $csid = COM_applyFilter($_POST[$csid_name]);
    }

    if ( $_CP_CONF['gfxDriver'] == 3 ) {
        $resp = recaptcha_check_answer ();
        $errors = array();
        if ($resp != NULL && $resp->isSuccess()) {
            DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE session_id='" . DB_escapeString($csid) . "'",1);
            return array(1,'');
        } else {
            if ( $resp) $errors = $resp->getErrorCodes();
            if ( is_array($errors) ) {
                foreach ($errors as $code) {
                    Log::write('system',Log::ERROR,$code);
                }
            }

            # set the error code so that we can display it
            $error = $LANG_CP00['recaptcha_entry_error'];
            CAPTCHA_errorLog("CAPTCHA: The reCAPTCHA verification failed in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );

            CAPTCHA_checkRetries( $csid );
            return array(0,$error);
        }
    }

    if ( $response == '' ) {
        CAPTCHA_errorLog("CAPTCHA: Detected an attempt to bypass CAPTCHA (blank input string) in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
        $msg = $LANG_CP00['bypass_error_blank'];
        $rc = 0;
        CAPTCHA_checkRetries( $csid );
    } else {
        // check the time
        $cTime = DB_getItem($_TABLES['cp_sessions'],'cptime',"session_id='" . DB_escapeString($csid) . "'");
        $now = time();
        if ( $cTime == '' || $cTime+$_CP_CONF['expire']+3600 < $now ) {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to use an expired CAPTCHA in " . $ctype . " - IP Address: " . $_SERVER['REAL_ADDR'] );
            $msg = $LANG_CP00['session_expired'];
            $rc = 0;
            CAPTCHA_checkRetries( $csid );
            return ( array( $rc, $msg ) );
        }
        $validationString = DB_getItem($_TABLES['cp_sessions'],'validation',"session_id='" . DB_escapeString($csid) . "'");
        if ( $validationString == '' ) {
           // no active session was found
            CAPTCHA_checkRetries( $csid );
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to use old CAPTCHA Session in " . $ctype . " - IP Address: " . $_SERVER['REAL_ADDR'] );
            $msg = $LANG_CP00['bypass_error_blank'];
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        if ( $validationString != $response ) {
            $msg = $LANG_CP00['entry_error'];
            CAPTCHA_errorLog("CAPTCHA: An invalid CAPTCHA string was entered in " . $ctype . " - IP Address: " . $_SERVER['REAL_ADDR'] );
            $rc = 0;
            CAPTCHA_checkRetries( $csid );
            return ( array( $rc, $msg ) );
        }
        if ( $validationString == $response && $response != '' ) {
            // we got it, so delete the record to keep others from using it
            DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE session_id='" . DB_escapeString($csid) . "'",1);
            $rc = 1;
        }
    }
    return ( array( $rc, $msg ) );
}

function recaptcha_check_answer()
{
    global $_CONF, $_CP_CONF;

    $rc = 0;
    require_once $_CONF['path'] . 'plugins/captcha/class/autoload.php';

    // The response from reCAPTCHA
    $resp = null;
    // The error code from reCAPTCHA, if any
    $error = null;
    $reCaptcha = new \ReCaptcha\ReCaptcha($_CP_CONF['privatekey'],new \ReCaptcha\RequestMethod\CurlPost());
    //$reCaptcha = new \ReCaptcha\ReCaptcha($_CP_CONF['privatekey'],new \ReCaptcha\RequestMethod\SocketPost());
    // Was there a reCAPTCHA response?
    if (isset($_POST["g-recaptcha-response"]) && $_POST["g-recaptcha-response"]) {
        $resp = $reCaptcha->verify($_POST["g-recaptcha-response"],$_SERVER["REAL_ADDR"]);
    }
    return $resp;
}

/**
 * Log message in CAPTCHA log
 *
 * @param   string  log entry
 * @param   int     override (forces log entry regardless of log setting)
 * @return  none
 */
function CAPTCHA_errorLog( $logentry, $override = 0) {
    global $_CONF, $_CP_CONF;

    if ( !isset($_CP_CONF['logging']) ) {
        $_CP_CONF['logging'] = 0;
    }

    if ( $_CP_CONF['logging'] != 1 && $override == 0) {
        return;
    }
    if ( !empty($logentry)) {
        Log::write('system',Log::WARNING,$logentry);
    }
}
?>
