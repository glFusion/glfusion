<?php
// +---------------------------------------------------------------------------+
// | CAPTCHA v3 Plugin                                                         |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | Geeklog integration functions.                                            |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2007 by the following authors:                              |
// |                                                                           |
// | Author: Mark R. Evans - mevans@ecsnet.com                                 |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'functions.inc') !== false)
{
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

$result = DB_query("SELECT * FROM {$_TABLES['cp_config']}",1);
while ($row = DB_fetchArray($result)) {
    $_CP_CONF[$row['config_name']] = stripslashes($row['config_value']);
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
* This will put an option for the plugin in the command and control block on moderation.php
*
* Add the plugin name, icon and link to the command and control block in moderation.php
*
* @return   array   Array containing (plugin name, admin url, url of plugin icon)
*
*/

function plugin_cclabel_captcha() {
    global $_CONF, $LANG_CP00;

    if (SEC_inGroup('Root') ) {
        return array($LANG_CP00['plugin'],
            $_CONF['site_admin_url'] . '/plugins/captcha/index.php',
            $_CONF['site_url'] . '/captcha/captcha.png');
    } else {
        return '';
    }
}

/**
* Returns the administrative option for this plugin
*
* Adds the plugin to the Admin menu
*
* Universal Plugin:  Modify/Uncomment use it
*
* @return   array   Array containing (plugin name, plugin admin url, # of items in plugin or '')
*
*/
function plugin_getadminoption_captcha() {
    global $_CONF, $_TABLES, $LANG_CP00;

    if (SEC_inGroup('Root')) {
        return array($LANG_CP00['plugin'], $_CONF['site_admin_url'] . '/plugins/captcha/index.php','');
    }
}

/**
* Removes the data structures for this plugin from the Geeklog database.
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
    global $_DB_table_prefix, $_TABLES, $LANG_CP00;

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

    DB_query("DROP TABLE " . $_DB_table_prefix . 'cp_config',1);
    DB_query("DROP TABLE " . $_DB_table_prefix . 'cp_sessions',1);

    // Ok to proceed and delete plugin

    // Unregister the plugin with Geeklog
    COM_errorLog('Attempting to unregister the CAPTCHA Plugin from Geeklog',1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'captcha'",1);

    COM_errorLog('...success',1);
    return true;
}

function plugin_upgrade_captcha()
{
    global $_TABLES, $_CONF, $_CP_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='captcha'");

    switch( $currentVersion ) {
        case "2.0.0" :
        case "2.0.1" :
        case "2.0.2" :
            $_SQL['cp_config'] =
                "CREATE TABLE {$_TABLES['cp_config']} ( " .
                "  `config_name` varchar(255) NOT NULL default '', " .
                "  `config_value` varchar(255) NOT NULL default '', " .
                "   PRIMARY KEY  (`config_name`) " .
                " );";

            foreach ($_SQL as $table => $sql) {
                COM_errorLog("Creating $table table",1);
                DB_query($sql,1);
                if (DB_error()) {
                    COM_errorLog("Error Creating $table table",1);
                }
                COM_errorLog("Success - Created $table table",1);
            }

            $SQL_DEFAULTS = "INSERT INTO `{$_TABLES['cp_config']}` (`config_name`, `config_value`) VALUES " .
                            " ('anonymous_only', '1'), " .
                            " ('debug', '0'), " .
                            " ('enable_comment', '0'), " .
                            " ('enable_contact', '0'), " .
                            " ('enable_emailstory', '0'), " .
                            " ('enable_forum', '0'), " .
                            " ('enable_registration', '0'), " .
                            " ('enable_story', '0'), " .
                            " ('gfxDriver', '2'), " .
                            " ('gfxFormat', 'jpg'), " .
                            " ('gfxPath', '');";

            DB_query($SQL_DEFAULTS,1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='2.1.0' WHERE pi_name='captcha' LIMIT 1");
        case "2.1.0" :
        case "2.1.1" :
        case "2.1.2" :
            $_SQL['cp_sessions'] =
                            "CREATE TABLE {$_TABLES['cp_sessions']} ( " .
                            "  `session_id` varchar(40) NOT NULL default '', " .
                            "  `cptime`  INT(11) NOT NULL default 0, " .
                            "  `validation` varchar(40) NOT NULL default '', " .
                            "  `counter`    TINYINT(4) NOT NULL default 0, " .
                            "  PRIMARY KEY (`session_id`) " .
                            " );";

            foreach ($_SQL as $table => $sql) {
                COM_errorLog("Creating $table table",1);
                DB_query($sql,1);
                if (DB_error()) {
                    COM_errorLog("Error Creating $table table",1);
                }
                COM_errorLog("Success - Created $table table",1);
            }

            $SQL_DEFAULTS = "INSERT INTO `{$_TABLES['cp_config']}` (`config_name`, `config_value`) VALUES " .
                            " ('enable_mediagallery', '0'), " .
                            " ('remoteusers', '0');";

            DB_query($SQL_DEFAULTS,1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='3.0.0' WHERE pi_name='captcha' LIMIT 1");
        case "3.0.0" :
        case "3.0.1" :
        case "3.0.2" :
            $SQL_DEFAULTS = "INSERT INTO `{$_TABLES['cp_config']}` (`config_name`, `config_value`) VALUES " .
                            " ('logging', '0'), " .
                            " ('enable_rating', '0');";
            DB_query($SQL_DEFAULTS,1);
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='3.1.0' WHERE pi_name='captcha' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='captcha'") == $_CP_CONF['version']) {
        return 1;
    } else {
        return 2;
    }
}
?>