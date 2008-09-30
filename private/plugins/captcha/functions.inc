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
// | Copyright (C) 2002-2008 by the following authors:                        |
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

$langfile = $_CONF['path'] . 'plugins/captcha/language/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once ($langfile);
} else {
    include_once ($_CONF['path'] . 'plugins/captcha/language/english.php');
}
/*
* Include captcha config file
*/
require_once($_CONF['path'] . 'plugins/captcha/config.php');

/*
* Check and see if we need to load the plugin configuration
*/
if (!isset($_CP_CONF['gfxDriver'])) {
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $cp_config = config::get_instance();
    $_CP_CFG = $cp_config->get_config('captcha');

    if ( is_array($_CP_CONF)  && is_array($_CP_CFG) ) {
        $_CP_CONF = array_merge($_CP_CONF, $_CP_CFG);
    }
    unset($_CP_CFG);
}

function CAPTCHA_sid() {
    return dechex(time()).dechex(mt_rand(1,65535));
}

function plugin_commentPreSave_captcha($uid, $title, $comment, $sid, $pid, $type, $postmode) {
    global $_CP_CONF, $_USER, $_POST;

    if ( $_CP_CONF['enable_comment'] != 1 ) {
        return 0;
    }

    if ( ($_CP_CONF['anonymous_only'] && $_USER['uid'] < 2) || $_CP_CONF['anonymous_only'] == 0 || ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) ) {
        $str = $_POST['captcha'];
        list( $rc, $msg )  = CAPTCHA_checkInput( 'comment',$str );
        if ( $rc == 1 )
            return 0;
        else
            return 1;
    }
    return 0;
}

function plugin_itemPreSave_captcha( $type='registration', $content='' ) {
    global $_CP_CONF, $_USER, $_POST;

    $msg = '';

    switch( $type ) {
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
        default :
            return '';
    }
    if ( !isset($_USER['uid']) ) {
        $_USER['uid'] = 1;
    }
    if ( ($_CP_CONF['anonymous_only'] && $_USER['uid'] < 2) || $_CP_CONF['anonymous_only'] == 0 || ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) ) {
        $str = COM_applyFilter($_POST['captcha']);
        list( $rc, $msg )  = CAPTCHA_checkInput( $type, $str );
        return $msg;
    }
    return '';
}

function plugin_templatesetvars_captcha($name, &$template) {
    global $_CP_CONF, $_USER, $_TABLES;

    switch( $name ) {
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
        default :
            $template->set_var('captcha', '');
            return;
            break;
    }
    if ( !isset($_USER['uid']) ) {
        $_USER['uid'] = 1;
    }

    if ( ($_CP_CONF['anonymous_only'] && $_USER['uid'] < 2) || $_CP_CONF['anonymous_only'] == 0 || ($_CP_CONF['remoteusers'] == 1 && SEC_inGroup("Remote Users") ) ) {
        // housekeeping, delete old captcha sessions
        $oldSessions = time() - ($_CP_CONF['expire']+900);
        DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE cptime < " . $oldSessions,1);

        // OK, we need to insert the CAPTCHA, so now we need to setup the session_id:

        // check to see if a failed entry happened...
        if ( isset($_POST['csid']) ) {
            $csid = COM_applyFilter($_POST['csid']);
        } else {
            $csid    = CAPTCHA_sid();
        }
        $time    = time();
        $counter = 0;
        $validation = '';  // this will be filled in by the CAPTCHA

        DB_save($_TABLES['cp_sessions'],"session_id,cptime,validation,counter","'$csid','$time','','0'");

        $captcha = CAPTCHA_getHTML($csid,$name);
        $template->set_var('captcha', $captcha);
    } else {
        $template->set_var('captcha', '');
    }
}

// Internal CAPTCHA functions...

function CAPTCHA_getHTML($csid, $type='general' ) {
    global $_CONF, $_CP_CONF, $LANG_CP00;

    $retval = '';

    $T = new Template($_CONF['path'] . 'plugins/captcha/templates');
    switch ( $type ) {
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
        case 'links' :
        case 'calendar' :
            $T->set_file('page', 'captcha_emailstory.thtml');
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

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function CAPTCHA_checkInput( $type, $str ) {
    global $_CONF, $_CP_CONF, $_TABLES, $LANG_CP00, $_SERVER;

    $msg = '';
    $rc = 0;

    // housekeeping, delete old captcha sessions
    $oldSessions = time() - ($_CP_CONF['expire']+900);
    DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE cptime < " . $oldSessions,1);

    if ( !isset($_POST['csid']) || $str == '' ) {
        if ( !isset($_POST['csid']) ) {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to bypass CAPTCHA (no session id) in " . $type . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
        } else {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to bypass CAPTCHA (blank input string) in " . $type . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
        }
        $msg = $LANG_CP00['bypass_error_blank'];
        $rc = 0;
    } else {
        $csid = COM_applyFilter($_POST['csid']);
        // check the time
        $cTime = DB_getItem($_TABLES['cp_sessions'],'cptime',"session_id='" . $csid . "'");
        $now = time();
        if ( $cTime == '' || $cTime+$_CP_CONF['expire'] < $now ) {
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to use an expired CAPTCHA in " . $type . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
            $msg = $LANG_CP00['session_expired'];
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        $validationString = DB_getItem($_TABLES['cp_sessions'],'validation',"session_id='" . $csid . "'");
        if ( $validationString == '' ) {
           // no active session was found
            CAPTCHA_errorLog("CAPTCHA: Detected an attempt to use old CAPTCHA Session in " . $type . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
            $msg = $LANG_CP00['bypass_error_blank'];
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        if ( $validationString != $str ) {
            $msg = $LANG_CP00['entry_error'];
            CAPTCHA_errorLog("CAPTCHA: An invalid CAPTCHA string was entered in " . $type . " - IP Address: " . $_SERVER['REMOTE_ADDR'] );
            $rc = 0;
            return ( array( $rc, $msg ) );
        }
        if ( $validationString == $str && $str != '' ) {
            // we got it, so delete the record to keep others from using it
            DB_query("DELETE FROM {$_TABLES['cp_sessions']} WHERE session_id='" . $csid . "'",1);
            $rc = 1;
        }
    }
    return ( array( $rc, $msg ) );
}

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

function plugin_chkVersion_captcha() {
    global $_CP_CONF;

    return $_CP_CONF['version'];
}


/**
* Removes the data structures for this plugin from the glFusion database.
* This routine will get called from the Plugin install program if user select De-Install or if Delete is used in the Plugin Editor.
* The Plugin Installer will also call this routine upon and install error to remove anything it has created.
* The Plugin installer will pass the optional parameter which will then double check that plugin has first been disabled.
*
* For this plugin, this routine will also remove the Block definition.
*
* Returns True if all Plugin related data is removed without error
*
* @param    string   $installCheck     Defult is blank but if set, check if plugin is disabled first
*
* @return   boolean True if successful false otherwise
*
*/
function plugin_uninstall_captcha($installCheck='') {
    global $_CONF, $_DB_table_prefix, $_TABLES, $LANG_CP00;

    $pi_name    = 'captcha';

    // Check and see if plugin is still enabled - if so display warning and exit
    if ($installCheck != '' && DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name = "' .$pi_name. '"')) {
        COM_errorLog("Plugin is installed and enabled. Disable first if you want to de-install it",1);
        $display .= COM_startBlock($LANG_CP00['warning']);
        $display .= $LANG_CP00['enabled'];
        $display .= COM_endBlock();
        echo $display;
        return false;
        exit;
    }
    require_once($_CONF['path'] . 'plugins/captcha/install.inc');

    $rc = plugin_remove_captcha();
    return $rc;

}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_captcha() {
    global $_CONF, $_TABLES;

    require_once($_CONF['path'] . 'plugins/captcha/glupgrade.inc');

    $rc = plugin_plgupgrade_captcha();

    return true;

}

?>