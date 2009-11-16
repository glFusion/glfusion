<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | glFusion integration functions.                                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$langfile = $_CONF['path'].'plugins/captcha/language/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once $langfile;
} else {
    include_once $_CONF['path'].'plugins/captcha/language/english.php';
}

require_once $_CONF['path'].'plugins/captcha/captcha.php';

// load the plugin configuration, but only if needed

if (!isset($_CP_CONF['gfxDriver'])) {
    require_once $_CONF['path_system'].'classes/config.class.php';

    $cp_config = config::get_instance();
    $_CP_CFG = $cp_config->get_config('captcha');

    if ( is_array($_CP_CONF)  && is_array($_CP_CFG) ) {
        $_CP_CONF = array_merge($_CP_CONF, $_CP_CFG);
    }
    unset($_CP_CFG);
}

if ( $_CP_CONF['gfxDriver'] == 3 ) {
    if ( $_CP_CONF['publickey'] == '' || $_CP_CONF['privatekey'] == '' ) {
        $_CP_CONF['gfxDriver'] = 2;
    }
}

if ( $_CP_CONF['gfxDriver'] == 3 ) {
    require_once $_CONF['path'] . 'plugins/captcha/class/recaptchalib.php';
}


/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_captcha ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/captcha/captcha.png';
}

// glFusion plugin API functions for CAPTCHA plugin

function plugin_enablestatechange_captcha($enable) {

    if ( defined('DEMO_MODE') && $enable == false ) {
        return -2;
    }
    return true;
}

// this plugin uses the 'templatesetvars' hook to insert the CAPTCHA challenge in user-accessible scripts
// that allow users to create or edit content of type specified (ctype).  the template handle relating to the
// input/edit form for the content type is passed to allow the corresponding 'captcha' template var to be set.
// the plugin configuration allows the challenge to be presented only for certain content types, and/or based
// upon whether this is an anonymous user

function plugin_templatesetvars_captcha($ctype, &$template) {
    global $_CP_CONF, $_USER, $_TABLES;

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
        case 'rating' :
            if ( $_CP_CONF['enable_rating'] != 1 ) {
                $template->set_var('captcha','');
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

        // for the 'general' content type case, there is no template var, and we are returning raw CAPTCHA_html

        case 'general' :
            break;

        // the content type was null, or unrecognizable, so therefore there can be no plugin config
        // option controlling it, and we should just clear the 'captcha' template var

        default :
            $template->set_var('captcha', '');
            return;
            break;
    }
    if ( !isset($_USER['uid']) ) {
        $_USER['uid'] = 1;
    }

    if ( ($_CP_CONF['anonymous_only'] && $_USER['uid'] < 2) || $_CP_CONF['anonymous_only'] == 0 || ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) ) {

        // expire stale CAPTCHA sessions
        CAPTCHA_expireSessions();

        // create a CAPTCHA session, or use the existing session if user entry failed
        $csid = CAPTCHA_getSession();

        // retrieve the CAPTCHA challenge in HTML form.  pass the CAPTCHA session id (csid)
        // and the format of the challenge is controlled by template selected by content type (ctype)
        $captcha = CAPTCHA_getHTML($csid, $ctype);

        // if our content type (ctype) was specified as 'general'
        if ( $ctype == 'general' ) {
            return $captcha;
        }

        $template->set_var('captcha', $captcha);
    } else {
        $template->set_var('captcha', '');
    }

    return '';
}

// this plugin hooks the content 'presave' hooks to check the user response to the CAPTCHA challenge

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

    if ( ($_CP_CONF['anonymous_only'] && $uid < 2) || $_CP_CONF['anonymous_only'] == 0 || ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) ) {
        $str = COM_applyFilter($_POST['captcha']);
        list( $rc, $msg) = CAPTCHA_checkInput( $str, 'comment' );
        if ( $rc == 1 )
            return 0;
        else
            return 1;
    }
    return 0;
}

function plugin_itemPreSave_captcha( $ctype='registration', $content='' ) {
    global $_CP_CONF, $_USER, $_POST;

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
        case 'rating' :
            if ( $_CP_CONF['enable_rating'] != 1 ) {
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
        case 'general' :
            break;
        default :
            return '';
    }
    if ( !isset($_USER['uid']) ) {
        $_USER['uid'] = 1;
    }
    if ( ($_CP_CONF['anonymous_only'] && $_USER['uid'] < 2) || $_CP_CONF['anonymous_only'] == 0 || ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) ) {
        $response = COM_applyFilter($_POST['captcha']);
        list( $rc, $msg )  = CAPTCHA_checkInput( $response, $ctype );
        return $msg;
    }
    return '';
}

function plugin_chkVersion_captcha() {
    global $_CP_CONF;

    return $_CP_CONF['pi_version'];
}

function plugin_upgrade_captcha() {
    global $_CONF, $_TABLES;

    require_once $_CONF['path'].'plugins/captcha/upgrade.php';

    return captcha_upgrade();
}

// CAPTCHA functions...

// create a unique but random CAPTCHA session identifer

function CAPTCHA_sid() {
    return dechex(time()).dechex(mt_rand(1,65535));
}

// creates a new (or refreshes existing) CAPTCHA session, the session identifier (csid) is returned

function CAPTCHA_getSession() {
    global $_TABLES;

    // if (csid) is in POST array, this is a reuse of an old session, else create a new one
    if ( isset($_POST['csid']) ) {
        $csid = COM_applyFilter($_POST['csid']);
    } else {
        $csid    = CAPTCHA_sid();
    }

    // set the current time
    $time    = time();

    // reset the number of tries
    $counter = 0;

    // this will be filled in by the CAPTCHA
    $validation = '';

    // update the session table
    DB_save($_TABLES['cp_sessions'],"session_id,cptime,validation,counter","'".addslashes($csid)."','$time','','0'");

    // return the CAPTCHA session identifier
    return $csid;
}

// prune the CAPTCHA session table by expiring stale sessions

function CAPTCHA_expireSessions() {
    global $_CP_CONF, $_TABLES;
    $oldSessions = time() - ($_CP_CONF['expire']+900);
    DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE cptime < " . $oldSessions,1);
    return;
}

// create the HTML to be inserted based upon the current CAPTCHA session id (csid) and content type (ctype)

function CAPTCHA_getHTML($csid, $ctype='general' ) {
    global $_CONF, $_CP_CONF, $LANG_CP00;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/captcha/templates');
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
        case 'rating' :
            $T->set_file('page', 'captcha_rating.thtml');
            break;
        case 'general' :
            $T->set_file('page', 'captcha.thtml');
            break;
        default :
            return '';
            break;
    }

    $T->set_var(array(
        'lang_captcha_help'     => $LANG_CP00['captcha_help'],
        'lang_refresh'          => $LANG_CP00['reload'],
        'lang_alt'              => $LANG_CP00['captcha_alt'],
        'captcha_image'         => $_CONF['site_url'] . '/captcha/captcha.php?csid=' . $csid . '&amp;.' . ($_CP_CONF['gfxFormat'] != '' ? $_CP_CONF['gfxFormat'] : 'jpg'),
        'lang_reload'           => $LANG_CP00['reload'],
        'lang_reload_failed'    => $LANG_CP00['reload_failed'],
        'lang_too_many'         => $LANG_CP00['reload_too_many'],
        'csid'                  => $csid,
    ));

    if ( $_CP_CONF['gfxDriver'] == 3 ) {
        $T->set_var('recaptcha', recaptcha_get_html($_CP_CONF['publickey'], NULL));
        $T->set_var('recaptcha_theme',$_CP_CONF['recaptcha_theme']);
    }

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

// check the user entry (response) to see if it matches the challenge inserted for the current CAPTCHA session
// the current content type (ctype) is passed only as a clue to be used in the error message.  note that the order of
// the arguments to this function was reversed in v3.2.4, however this should not have broken anything in the core or
// bundled plugins.  this was done to improve usability, eg. to allow a simpler form of the function to be used in
// external staticpage, block, or other user custom code

function CAPTCHA_checkInput( $response, $ctype='general' ) {
    global $_CP_CONF, $_TABLES, $LANG_CP00, $_SERVER;

    // if rc=0 on return, this is failure, and msg will contain detail
    $msg = '';
    $rc = 0;

    if ( $_CP_CONF['gfxDriver'] == 3 ) {
        $resp = recaptcha_check_answer ($_CP_CONF['privatekey'],
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

        if ($resp->is_valid) {
            return array(1,'');
        } else {
            # set the error code so that we can display it
            return array(0,$resp->error);
        }
    }

    // expire stale CAPTCHA sessions
    CAPTCHA_expireSessions();

    if ( !isset($_POST['csid']) || $response == '' ) {
        if ( !isset($_POST['csid']) ) {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to bypass CAPTCHA (no session id) in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
        } else {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to bypass CAPTCHA (blank input string) in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
        }
        $msg = $LANG_CP00['bypass_error_blank'];
        $rc = 0;
    } else {
        $csid = COM_applyFilter($_POST['csid']);
        // check the time
        $cTime = DB_getItem($_TABLES['cp_sessions'],'cptime',"session_id='" . addslashes($csid) . "'");
        $now = time();
        if ( $cTime == '' || $cTime+$_CP_CONF['expire'] < $now ) {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to use an expired CAPTCHA in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
            $msg = $LANG_CP00['session_expired'];
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        $validationString = DB_getItem($_TABLES['cp_sessions'],'validation',"session_id='" . addslashes($csid) . "'");
        if ( $validationString == '' ) {
           // no active session was found
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to use old CAPTCHA Session in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
            $msg = $LANG_CP00['bypass_error_blank'];
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        if ( $validationString != $response ) {
            $msg = $LANG_CP00['entry_error'];
            CAPTCHA_errorLog("CAPTCHA: An invalid CAPTCHA string was entered in " . $ctype . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        if ( $validationString == $response && $response != '' ) {
            // we got it, so delete the record to keep others from using it
            DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE session_id='" . addslashes($csid) . "'",1);
            $rc = 1;
        }
    }
    return ( array( $rc, $msg ) );
}

// CAPTCHA likes it's own log, however someday we're going to unify these things i hope

function CAPTCHA_errorLog( $logentry, $override = 0) {
    global $_CONF, $_CP_CONF;

    if ( !isset($_CP_CONF['logging']) ) {
        $_CP_CONF['logging'] = 0;
    }

    if ( $_CP_CONF['logging'] != 1 && $override == 0) {
        return;
    }

    if( !empty( $logentry )) {
        $timestamp = strftime( '%c' );
        $logfile = $_CONF['path_log'] . 'captcha.log';

        if( $file = fopen( $logfile, 'a' )) {
            fputs( $file, "$timestamp - $logentry \n" );
            fclose($file);
        }
    }
}


?>