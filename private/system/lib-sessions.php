<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-sessions.php                                                         |
// |                                                                          |
// | glFusion session library.                                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Mark Limburg     - mlimburg AT users DOT sourceforge DOT net    |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
* This is the session management library for glFusion.  Some of this code was
* borrowed from phpBB 1.4.x which is also GPL'd
*
*/

if ( !isset($_SYSTEM['session_timeout']) ) {
    $_SYSTEM['session_timeout'] = 3600;
}

// Turn this on if you want to see various debug messages from this library
$_SESS_VERBOSE = false;

if (empty ($_CONF['cookiedomain'])) {
    preg_match ("/\/\/([^\/:]*)/", $_CONF['site_url'], $server);
    if (substr ($server[1], 0, 4) == 'www.') {
        $_CONF['cookiedomain'] = substr ($server[1], 3);
    } else {
        if (strchr ($server[1], '.') === false) {
            // e.g. 'localhost' or other local names
            $_CONF['cookiedomain'] = '';
        } else {
            $_CONF['cookiedomain'] = '.' . $server[1];
        }
    }
    if ($_SESS_VERBOSE) {
        COM_errorLog ("Setting cookiedomain='" . $_CONF['cookiedomain'] . "'", 1);
    }
}

// LOAD USER DATA. NOTE: I'm not sure why I have to set $_USER like this because
// it's supposed to be a global variable.  I tried setting $_USER from within
// SESS_sessionCheck() and it doesn't work.
$_USER = SESS_sessionCheck();

/**
* This gets the state for the user
*
* Much of this code if from phpBB (www.phpbb.org).  This checks the session
* cookie and long term cookie to get the users state.
*
* @return   array   returns $_USER array
*
*/
function SESS_sessionCheck()
{
    global $_CONF, $_TABLES, $_USER, $_SESS_VERBOSE, $_SYSTEM;

    if ($_SESS_VERBOSE) {
        COM_errorLog("***Inside SESS_sessionCheck***",1);
    }

    unset($_USER);

    $user_logged_in = 0;
    $logged_in = 0;
    $userdata = array();

    // Check for a cookie on the users's machine.  If the cookie exists, build
    // an array of the users info and setup the theme.

    if (isset ($_COOKIE[$_CONF['cookie_session']])) {
        $sessid = COM_applyFilter ($_COOKIE[$_CONF['cookie_session']]);
        if ($_SESS_VERBOSE) {
            COM_errorLog("got $sessid as the session id from lib-sessions.php",1);
        }

        $userid = SESS_getUserIdFromSession($sessid, $_CONF['session_cookie_timeout'], $_SERVER['REMOTE_ADDR'], $_CONF['cookie_ip']);
        $userid = intval($userid);
        if ($_SESS_VERBOSE) {
            COM_errorLog("Got $userid as User ID from the session ID",1);
        }

        if ($userid > 1) {
            // Check user status
            $status = SEC_checkUserStatus($userid);
            if (($status == USER_ACCOUNT_ACTIVE) || ($status == USER_ACCOUNT_AWAITING_ACTIVATION)) {
                $user_logged_in = 1;

                SESS_updateSessionTime($sessid, $_CONF['cookie_ip']);
                $userdata = SESS_getUserDataFromId($userid);
                if ($_SESS_VERBOSE) {
                    COM_errorLog("Found " . count($userdata) . " pieces of data from userdata", 1);
                    COM_errorLog(COM_debug($userdata), 1);
                }
                $_USER = $userdata;
                $_USER['auto_login'] = false;
            }
        } else {
            // Session probably expired, now check permanent cookie
            if (isset ($_COOKIE[$_CONF['cookie_name']])) {
                $userid = $_COOKIE[$_CONF['cookie_name']];
                if (empty ($userid) || ($userid == 'deleted')) {
                    unset ($userid);
                } else {
                    $userid = (int) COM_applyFilter ($userid, true);
                    $cookie_password = '';
                    if ($userid > 1) {
                        if (array_key_exists('cookie_password', $_CONF)) {
                            $cookie_password = $_COOKIE[$_CONF['cookie_password']];
                        }
                        $result = DB_query("SELECT remote_ip FROM {$_TABLES['users']} WHERE uid=$userid",1);
                        $rip    = DB_fetchArray($result);
                        $remote_ip = $rip['remote_ip'];
                    }
                    $remote_ip_array = explode('.',$remote_ip);
                    $server_ip_array = explode('.',$_SERVER['REMOTE_ADDR']);
                    if ( $remote_ip_array[0] == $server_ip_array[0] &&
                         $remote_ip_array[1] == $server_ip_array[1] &&
                         $remote_ip_array[2] == $server_ip_array[2] ) {
                        $ipmatch = true;
                    } else {
                        $ipmatch = false;
                    }
                    if ( isset($_SYSTEM['skip_ip_check']) && $_SYSTEM['skip_ip_check'] == 1 ) {
                        $ipmatch = true;
                    }
                    if (empty ($cookie_password) || (!SEC_checkTokenGeneral($cookie_password,'ltc',$userid)) || ($ipmatch == false )) {
                        // User may have modified their UID in cookie, ignore them
                        SEC_setCookie ($_CONF['cookie_name'], '', time() - 10000,
                                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                                       $_CONF['cookiesecure'],true);
                        SEC_setcookie ($_CONF['cookie_password'], '', time() - 10000,
                                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                                       $_CONF['cookiesecure'],true);
                    } else if ($userid > 1) {
                        // Check user status
                        $status = SEC_checkUserStatus ($userid);
                        if (($status == USER_ACCOUNT_ACTIVE) || ($status == USER_ACCOUNT_AWAITING_ACTIVATION)) {
                            $user_logged_in = 1;

                            $sessid = SESS_newSession($userid, $_SERVER['REMOTE_ADDR'], $_CONF['session_cookie_timeout'], $_CONF['cookie_ip']);
                            SESS_setSessionCookie($sessid, $_CONF['session_cookie_timeout'], $_CONF['cookie_session'], $_CONF['cookie_path'], $_CONF['cookiedomain'], $_CONF['cookiesecure']);
                            $userdata = SESS_getUserDataFromId($userid);
                            $_USER = $userdata;
                            $_USER['auto_login'] = true;
                        }
                    }
                }
            }
        }
    } else {
        if ($_SESS_VERBOSE) {
            COM_errorLog('session cookie not found from lib-sessions.php',1);
        }

        // Check if the persistent cookie exists

        if (isset ($_COOKIE[$_CONF['cookie_name']])) {
            // Session cookie doesn't exist but a permanent cookie does.
            // Start a new session cookie;
            if ($_SESS_VERBOSE) {
                COM_errorLog('perm cookie found from lib-sessions.php',1);
            }

            $userid = $_COOKIE[$_CONF['cookie_name']];
            if (empty ($userid) || ($userid == 'deleted')) {
                unset ($userid);
            } else {
                $userid = (int) COM_applyFilter ($userid, true);
                $cookie_password = '';
                if ($userid > 1) {
                    $result     = DB_query("SELECT remote_ip FROM {$_TABLES['users']} WHERE uid=$userid",1);
                    $rip        = DB_fetchArray($result);
                    $remote_ip  = $rip['remote_ip'];
                    $cookie_password = $_COOKIE[$_CONF['cookie_password']];
                    $remote_ip_array = explode('.',$remote_ip);
                    $server_ip_array = explode('.',$_SERVER['REMOTE_ADDR']);
                    if ( $remote_ip_array[0] == $server_ip_array[0] &&
                         $remote_ip_array[1] == $server_ip_array[1] &&
                         $remote_ip_array[2] == $server_ip_array[2] ) {
                        $ipmatch = true;
                    } else {
                        $ipmatch = false;
                    }
                }
                if ( isset($_SYSTEM['skip_ip_check']) && $_SYSTEM['skip_ip_check'] == 1 ) {
                    $ipmatch = true;
                }
                if (empty ($cookie_password) || (!SEC_checkTokenGeneral($cookie_password,'ltc',$userid)) || ($ipmatch == false )) {
                    // User could have modified UID in cookie, don't do shit
                    SEC_setcookie ($_CONF['cookie_name'], '', time() - 10000,
                                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                                   $_CONF['cookiesecure'],true);
                    SEC_setcookie ($_CONF['cookie_password'], '', time() - 10000,
                                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                                   $_CONF['cookiesecure'],true);
                } else if ($userid > 1) {
                    // Check user status
                    $status = SEC_checkUserStatus($userid);
                    if (($status == USER_ACCOUNT_ACTIVE) ||
                            ($status == USER_ACCOUNT_AWAITING_ACTIVATION)) {
                        $user_logged_in = 1;

                        // Create new session and write cookie
                        $sessid = SESS_newSession($userid, $_SERVER['REMOTE_ADDR'], $_CONF['session_cookie_timeout'], $_CONF['cookie_ip']);
                        SESS_setSessionCookie($sessid, $_CONF['session_cookie_timeout'], $_CONF['cookie_session'], $_CONF['cookie_path'], $_CONF['cookiedomain'], $_CONF['cookiesecure']);
                        $userdata = SESS_getUserDataFromId($userid);
                        $_USER = $userdata;
                        $_USER['auto_login'] = true;
                    }
                }
            }
        }
    }

    if ($_SESS_VERBOSE) {
        COM_errorLog("***Leaving SESS_sessionCheck***",1);
    }

    // Ensure $_USER is set to avoid warnings (path exposure...)
    if (isset($_USER)) {
        return $_USER;
    } else {
        return NULL;
    }
}

/**
* Creates new user session (short term cookie)
*
* Adds a new session to the database for the given userid and returns a new session ID.
* Also deletes all expired sessions from the database, based on the given session lifespan.
*
* @param        int         $userid         User ID to create session for
* @param        string      $remote_ip      IP address user is connected from
* @param        string      $lifespan       How long (seconds) this cookie should persist
* @param        string      $md5_based      If 1 session will be MD5 hash of ip address
* @return       string      Session ID
*
*/
function SESS_newSession($userid, $remote_ip, $lifespan, $md5_based=0)
{
    global $_TABLES, $_CONF, $_SESS_VERBOSE;

    if ($_SESS_VERBOSE) {
        COM_errorLog("*************inside new_session*****************",1);
        COM_errorLog("Args to new_session: userid = $userid, remote_ip = $remote_ip, lifespan = $lifespan, md5_based = $md5_based",1);
    }
    mt_srand((double)microtime()*1000000);
    $sessid = mt_rand();

    // For added security we are adding the option to build a IP-based
    // session ID.  This has the advantage of better security but it may
    // required dialed users to login every time.  You can turn the below
    // code on in the configuration (it's turned off by default)
    if ($md5_based == 1) {
        $ip = str_replace('.','',$remote_ip);
        $md5_sessid = md5($ip + $sessid);
    } else {
        $md5_sessid = '';
    }

    $currtime = (string) (time());
    $expirytime = (string) (time() - $lifespan);
    if (!isset($_COOKIE[$_CONF['cookie_session']])) {
        // ok, delete any old sessons for this user
        DB_query("DELETE FROM {$_TABLES['sessions']} WHERE uid = ".(int) $userid,1);
        if ( DB_error() ) {
            DB_query("REPAIR TABLE {$_TABLES['sessions']}",1);
            COM_errorLog("***** REPAIR SESSIONS TABLE *****");
        }
    } else {
        $deleteSQL = "DELETE FROM {$_TABLES['sessions']} WHERE (start_time < $expirytime)";
        $delresult = DB_query($deleteSQL,1);
        if ( DB_error() ) {
            DB_query("REPAIR TABLE {$_TABLES['sessions']}",1);
            COM_errorLog("***** REPAIR SESSIONS TABLE *****");
            $delresult = DB_query($deleteSQL,1);
        }

        if ($_SESS_VERBOSE) {
            COM_errorLog("Attempted to delete rows from session table with following SQL\n$deleteSQL\n",1);
            COM_errorLog("Got $delresult as a result from the query",1);
        }

        if (!$delresult) {
            die("Delete failed in new_session()");
        }
    }
    // Remove the anonymous sesssion for this user
    DB_query("DELETE FROM {$_TABLES['sessions']} WHERE uid = 1 AND remote_ip = '".DB_escapeString($remote_ip)."'");

    // Create new session
    if (empty ($md5_sessid)) {
        $sql = "INSERT INTO {$_TABLES['sessions']} (sess_id, uid, start_time, remote_ip) VALUES ('$sessid', '$userid', '$currtime', '$remote_ip')";
    } else {
        $sql = "INSERT INTO {$_TABLES['sessions']} (sess_id, md5_sess_id, uid, start_time, remote_ip) VALUES ('$sessid', '$md5_sessid', '$userid', '$currtime', '".DB_escapeString($remote_ip)."')";
    }
    $result = DB_query($sql);
    if ($result) {
        if ($_CONF['lastlogin'] == true) {
            // Update userinfo record to record the date and time as lastlogin
            DB_query("UPDATE {$_TABLES['userinfo']} SET lastlogin = UNIX_TIMESTAMP() WHERE uid='".intval($userid)."'");
        }
        if ($_SESS_VERBOSE) COM_errorLog("Assigned the following session id: $sessid",1);
        if ($_SESS_VERBOSE) COM_errorLog("*************leaving SESS_newSession*****************",1);
        if ($md5_based == 1) {
            return $md5_sessid;
        } else {
            return $sessid;
        }
    } else {
        echo DB_error().": ".DB_error()."<br" . XHTML . ">";
        die("Insert failed in new_session()");
    }
    if ($_SESS_VERBOSE) COM_errorLog("*************leaving SESS_newSession*****************",1);
}

/**
* Sets the session cookie
*
* This saves the session ID to the session cookie on client's machine for
* later use
*
* @param        string      $sessid         Session ID to save to cookie
* @param        int         $cookietime     Cookie timeout value (not used)
* @param        string      $cookiename     Name of cookie to save sessiond ID to
* @param        string      $cookiepath     Path in which cookie should be sent to server for
* @param        string      $cookiedomain   Domain in which cookie should be sent to server for
* @param        int         $cookiesecure   if =1, set cookie only on https connection
*
*/
function SESS_setSessionCookie($sessid, $cookietime, $cookiename, $cookiepath, $cookiedomain, $cookiesecure)
{
    global $_SESS_VERBOSE;

    // This sets a cookie that will persist until the user closes their browser
    // window. since session expiry is handled on the server-side, cookie expiry
    // time isn't a big deal.
    if ($_SESS_VERBOSE) {
        COM_errorLog ("Setting session cookie: setcookie($cookiename, $sessid, 0, $cookiepath, $cookiedomain, $cookiesecure);", 1);
    }

    if (SEC_setCookie ($cookiename, $sessid, 0, $cookiepath, $cookiedomain, $cookiesecure,true) === false) {
        COM_errorLog ('Failed to set session cookie.', 1);
    }
}

/**
* Gets the user id from Session ID
*
* Returns the userID associated with the given session, based on
* the given session lifespan $cookietime and the given remote IP
* address. If no match found, returns 0.
*
* @param        string      $sessid         Session ID to get user ID from
* @param        string      $cookietime     Used to query DB for valid sessions
* @param        string      $remote_ip      Used to pull session we need
* @param        int         $md5_based      Let's us now if we need to take MD5 hash into consideration
* @return       int         User ID
*/
function SESS_getUserIdFromSession($sessid, $cookietime, $remote_ip, $md5_based=0)
{
    global $_CONF, $_TABLES, $_SESS_VERBOSE;

    if ($_SESS_VERBOSE) {
        COM_errorLog("****Inside SESS_getUserIdFromSession",1);
    }

    $mintime = time() - $cookietime;

    if ($md5_based == 1) {
        $sql = "SELECT uid FROM {$_TABLES['sessions']} WHERE "
        . "(md5_sess_id = '".DB_escapeString($sessid)."') AND (start_time > $mintime) AND (remote_ip = '".DB_escapeString($remote_ip)."')";
    } else {
        $sql = "SELECT uid FROM {$_TABLES['sessions']} WHERE "
        . "(sess_id = '".DB_escapeString($sessid)."') AND (start_time > $mintime) AND (remote_ip = '".DB_escapeString($remote_ip)."')";
    }

    if ($_SESS_VERBOSE) {
        COM_errorLog("SQL in SESS_getUserIdFromSession is:\n $sql\n");
    }

    $result = DB_query($sql,1);
    if ( DB_error() ) {
        DB_query("REPAIR TABLE {$_TABLES['sessions']}");
        COM_errorLog("**** REPAIRING SESSION TABLE ******");
        $result = DB_query($sql,1);
    }
    $row = DB_fetchArray($result);

    if ($_SESS_VERBOSE) {
        COM_errorLog("****Leaving SESS_getUserIdFromSession",1);
    }

    if (!$row) {
        return 0;
    } else {
        return $row['uid'];
    }
}

/**
* Updates a session cookies timeout
*
* Refresh the start_time of the given session in the database.
* This is called whenever a page is hit by a user with a valid session.
*
* @param        string      $sessid     Session ID to update time for
* @param        int         $md5_based  Indicates if sessid is MD5 hash
* @return       boolean     always true for some reason
*
*/
function SESS_updateSessionTime($sessid, $md5_based=0)
{
    global $_TABLES;

    $newtime = (string) time();

    if ($md5_based == 1) {
        $sql = "UPDATE {$_TABLES['sessions']} SET start_time=$newtime WHERE (md5_sess_id = '".DB_escapeString($sessid)."')";
    } else {
        $sql = "UPDATE {$_TABLES['sessions']} SET start_time=$newtime WHERE (sess_id = '".DB_escapeString($sessid)."')";
    }

    $result = DB_query($sql);

    return 1;
}

/**
* This ends a user session
*
* Delete the given session from the database. Used by the logout page.
*
* @param        int     $userid     User ID to end session of
* @return       boolean     Always true for some reason
*
*/
function SESS_endUserSession($userid)
{
    global $_TABLES;

    $sql = "DELETE FROM {$_TABLES['sessions']} WHERE (uid = ".intval($userid).")";
    $result = DB_query($sql);

    return 1;
}

/**
* Gets a user's data
*
* Gets user's data based on their username
*
* @param        string     $username        Username of user to get data for
* @return       array       returns user's data in an array
*
*/
function SESS_getUserData($username)
{
    global $_TABLES;

    $sql = "SELECT *,format FROM {$_TABLES['users']}, {$_TABLES['userprefs']}, {$_TABLES['dateformats']} "
        . "WHERE {$_TABLES['dateformats']}.dfid = {$_TABLES['userprefs']}.dfid AND "
        . "{$_TABLES['userprefs']}.uid = {$_TABLES['users']}.uid AND username = '".DB_escapeString($username)."'";

    if(!$result = DB_query($sql)) {
        COM_errorLog("error in get_userdata");
    }

    if(!$myrow = DB_fetchArray($result)) {
        COM_errorLog("error in get_userdata");
    }

    return($myrow);
}

/**
* Gets user's data
*
* Gets user's data based on their user id
*
* @param    int     $userid     User ID of user to get data for
* @return   array               returns user's data in an array
*
*/
function SESS_getUserDataFromId($userid)
{
    global $_TABLES;

    $sql = "SELECT *,format FROM {$_TABLES['dateformats']},{$_TABLES['users']},{$_TABLES['userprefs']} "
     . "WHERE {$_TABLES['dateformats']}.dfid = {$_TABLES['userprefs']}.dfid AND "
     . "{$_TABLES['userprefs']}.uid = ".intval($userid)." AND {$_TABLES['users']}.uid = ".intval($userid);

    if (!$result = DB_query($sql)) {
        $userdata = array('error' => '1');
        return $userdata;
    }

    if (!$myrow = DB_fetchArray($result, false)) {
        $userdata = array('error' => '1');
        return $userdata;
    }

    if (isset($myrow['passwd'])) {
        unset($myrow['passwd']);
    }

    return $myrow;
}

function SESS_completeLogin($uid)
{
    global $_TABLES, $_CONF, $_SYSTEM, $_USER;

    DB_change($_TABLES['users'],'pwrequestid',"NULL",'uid',(int) $uid);
    $userdata = SESS_getUserDataFromId($uid);
    $_USER = $userdata;
    $sessid = SESS_newSession($_USER['uid'], $_SERVER['REMOTE_ADDR'], $_CONF['session_cookie_timeout'], $_CONF['cookie_ip']);
    SESS_setSessionCookie($sessid, $_CONF['session_cookie_timeout'], $_CONF['cookie_session'], $_CONF['cookie_path'], $_CONF['cookiedomain'], $_CONF['cookiesecure']);
    PLG_loginUser ($_USER['uid']);

    // Now that we handled session cookies, handle longterm cookie
    if (!isset($_COOKIE[$_CONF['cookie_name']]) || !isset($_COOKIE['password'])) {
        // Either their cookie expired or they are new
        $cooktime = COM_getUserCookieTimeout();
        if ($VERBOSE) {
            COM_errorLog("Trying to set permanent cookie with time of $cooktime",1);
        }
        if ($cooktime > 0) {
            $cookieTimeout = time() + $cooktime;
            $token_ttl = $cooktime;
        } else {
            $cookieTimeout = 0;  // session cookie
            $token_ttl = $_SYSTEM['session_timeout'];
        }
        // They want their cookie to persist for some amount of time so set it now
        if ($VERBOSE) {
            COM_errorLog('Trying to set permanent cookie',1);
        }
        SEC_setCookie ($_CONF['cookie_name'], $_USER['uid'],
                       $cookieTimeout, $_CONF['cookie_path'],
                       $_CONF['cookiedomain'], $_CONF['cookiesecure'],true);
        $ltToken = SEC_createTokenGeneral('ltc',$token_ttl);
        SEC_setCookie ($_CONF['cookie_password'],
                       $ltToken, $cookieTimeout,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure'],true);
        DB_query("UPDATE {$_TABLES['users']} set remote_ip='".DB_escapeString($_SERVER['REMOTE_ADDR'])."' WHERE uid=".$_USER['uid'],1);
    } else {
        $userid = $_COOKIE[$_CONF['cookie_name']];
        if (empty ($userid) || ($userid == 'deleted')) {
            unset ($userid);
        } else {
            $userid = (int) COM_applyFilter ($userid, true);
            if ($userid > 1) {
                if ($VERBOSE) {
                    COM_errorLog ('NOW trying to set permanent cookie',1);
                    COM_errorLog ('Received '.$userid.' from perm cookie in users.php',1);
                }
                // Create new session
                $userdata = SESS_getUserDataFromId ($userid);
                $_USER = $userdata;
                if ($VERBOSE) {
                    COM_errorLog ('Received '.$_USER['username'].' for the username in user.php',1);
                }
            }
        }
    }

    // Now that we have users data see if their theme cookie is set.
    // If not set it
    SEC_setcookie ($_CONF['cookie_theme'], $_USER['theme'], time() + 31536000,
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure'],true);

    return true;
}

?>