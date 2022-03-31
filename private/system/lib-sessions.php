<?php
/**
* glFusion CMS
*
* glFusion Sessions Library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Authors: Tony Bibbs       - tony AT tonybibbs DOT com
*            Mark Limburg     - mlimburg AT users DOT sourceforge DOT net
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use glFusion\Database\Database;
use glFusion\Cache\Cache;
use glFusion\Log\Log;
use glFusion\User\UserAuth;

// ensure cookie domain is properly initialized

if (empty ($_CONF['cookiedomain'])) {
    preg_match ("/\/\/([^\/:]*)/", $_CONF['site_url'], $server);
    if (substr ($server[1], 0, 4) == 'www.') {
        $_CONF['cookiedomain'] = substr ($server[1], 3);
    } else {
        if (strchr ($server[1], '.') === false) {
            // e.g. 'localhost' or other local names
            $_CONF['cookiedomain'] = '';
        } else {
            $_CONF['cookiedomain'] = $server[1];
        }
    }
}

session_set_cookie_params(array(
    'lifetime' => 0,
    'path' => $_CONF['cookie_path'],
    'domain' => $_CONF['cookiedomain'],
    'secure' => $_CONF['cookiesecure'],
    'httponly' => true,
    'samesite' => 'Lax',
));

// Need to destroy any existing sessions started with session.auto_start
if (session_id()) {
	session_unset();
	session_destroy();
}

$_SERVER['REMOTE_USER'] = 'anonymous';

session_name($_CONF['cookie_session']);
$_USER = SESS_sessionCheck();

$_CONTEXT = array();

/**
* Check if user has valid session
*
* Checks to see if the session cookie is set and validates it
* If no session cookie, then check for remember me settings
*
* If no valid session is found - one will be created
*
* @return       array   user data array or null if anonymous user
*
*/
function SESS_sessionCheck()
{
    global $_CONF, $_USER;

    unset($_USER);


    session_name($_CONF['cookie_session']);

    $_UserInstance = new UserAuth();
    $_USER = $_UserInstance->getUserData();

    unset($_USER['passwd']);
    unset($_USER['tfa_secret']);

    return $_USER;
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
* @return       string      Session ID
*
*/
function SESS_newSession_REMOVE($userid, $remote_ip, $lifespan)
{
    global $_TABLES, $_CONF;

    $sessid = 0;
    $md5_sessid = _createID();

    $db = Database::getInstance();

    $currtime   = (string) (time());
    $expirytime = (string) (time() - $lifespan);

    $browser = (!empty($_SERVER['HTTP_USER_AGENT'])) ? md5((string) $_SERVER['HTTP_USER_AGENT']) : '';

    if (isset($_COOKIE[$_CONF['cookie_session']])) {
        // delete old sesson records for this user
        $oldsessionid = COM_applyFilter($_COOKIE[$_CONF['cookie_session']]);
        if ( !empty($oldsessionid) ) {
            try {
                $stmt = $db->conn->delete($_TABLES['sessions'],array('md5_sess_id' => $oldsessionid));
            } catch(Throwable $e) {
//                throw($e);
                try {
                    $db->conn->query("REPAIR TABLE `{$_TABLES['sessions']}`");
                    Log::write('system',Log::ERROR,"Attempting to repair the glFusion Sessions Table");
                } catch(Throwable $e) {
                    Log::write('system',Log::ERROR,"Unable to write to the glFusion Sessions Table");
                }
            }
    		if (session_id()) {
    			session_unset();
    			session_destroy();
                }
            SEC_setCookie ($_CONF['cookie_session'], '', time() - 3600,
                           $_CONF['cookie_path'], $_CONF['cookiedomain'],
                           $_CONF['cookiesecure'],true);
        }
    }

    if ( $userid > 1 ) {
        $deleteSQL = "DELETE FROM `{$_TABLES['sessions']}` WHERE (start_time < ?)";
        $stmt = $db->conn->prepare($deleteSQL);
        $stmt->bindValue(1,$expirytime,Database::INTEGER);
        $stmt->execute();
    }
    $result = $db->conn->insert($_TABLES['sessions'],
                    array(
                        'sess_id' => $sessid,
                        'browser' => $browser,
                        'md5_sess_id' => $md5_sessid,
                        'uid'   => $userid,
                        'start_time' => $currtime,
                        'remote_ip' => $remote_ip
                    ),
                    array(
                        Database::STRING,
                        Database::STRING,
                        Database::STRING,
                        Database::INTEGER,
                        Database::STRING,
                        Database::STRING
                    )
    );
    if ($result) {
        if ($userid > 1 && $_CONF['lastlogin'] == true) {
            $db->conn->update($_TABLES['userinfo'],
                        array('lastlogin' => $_CONF['_now']->toUnix(true)),
                        array('uid' => $userid),
                        array(Database::INTEGER)
            );
        }
    } else {
        echo $db->dbError();
        $md5_sessid = false;
    }
    return $md5_sessid;
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
* @return       int         User ID
*/
function SESS_getUserIdFromSession_REMOVE($sessid, $cookietime, $remote_ip)
{
    global $_CONF, $_TABLES;

    $uid = 0;

    $db = Database::getInstance();

    $mintime = time() - $cookietime;

    $sql = "SELECT uid,start_time,remote_ip,browser FROM `{$_TABLES['sessions']}` WHERE "
        . "(md5_sess_id = ?) AND (start_time > ?)";

    try {
        $row = $db->conn->fetchAssoc($sql,
            array($sessid, $mintime),
            array(Database::STRING,Database::INTEGER)
        );
    } catch(Throwable $e) {
        if (defined('DVLP_DEBUG')) {
            throw($e);
        }
        $db->conn->executeQuery("REPAIR TABLE `{$_TABLES['sessions']}`");
    }

    if ( !$row ) {
        return 0;
    }

    $uid = (int) $row['uid'];
    $server_ip = $row['remote_ip'];

    $ipmatch = false;

    $ipmatch = _ipCheck( $server_ip, $remote_ip, true );

	$browser = (!empty($_SERVER['HTTP_USER_AGENT'])) ? md5((string) $_SERVER['HTTP_USER_AGENT']) : '';
	$browsermatch = false;

	if ( $browser != $row['browser'] ) {
	    $browsermatch = false;
	} else {
	    $browsermatch = true;
	}

    if ( $ipmatch == false || $browsermatch == false) {
        // destroy old session
		if (session_id()) {
			session_unset();
			session_destroy();
		}

        try {
            $db->conn->delete($_TABLES['sessions'], array('md5_sess_id' => $sessid));
        } catch(Throwable $e) {
            if (defined('DVLP_DEBUG')) {
                throw($e);
            }
        }
        return 0;
    }

    if ( $row['start_time'] + 60 < time() ) {
        SESS_updateSessionTime($sessid);
    }
    return $uid;
}

/**
* Updates a session cookies timeout
*
* Refresh the start_time of the given session in the database.
* This is called whenever a page is hit by a user with a valid session.
*
* @param        string      $sessid     Session ID to update time for
* @return       boolean     always true for some reason
*
*/
function SESS_updateSessionTime_REMOVE($sessid)
{
    global $_TABLES;

    $newtime = (string) time();

    $db = Database::getInstance();

    $db->conn->update($_TABLES['sessions'],
                array('start_time' => $newtime),
                array('md5_sess_id' => $sessid),
                array(Database::INTEGER,Database::STRING)
    );

    return true;
}

/**
* This ends a user session
*
* Delete the given session from the database. Used by the logout page.
*
* @param        int     $userid     User ID to end session of
* @return       boolean     Always true
*
*/
function SESS_endUserSession_REMOVE($userid)
{
    global $_TABLES, $_CONF;

    $db = Database::getInstance();

    if ( !defined('DEMO_MODE') ) {
        try {
            $db->conn->delete($_TABLES['sessions'],array('uid' => $userid),array(Database::INTEGER));
    } catch(Throwable $e) {
            $db->dbError($e->getMessage(),'');
        }
		if (session_id()) {
			session_unset();
			session_destroy();
		}
    } else {
        if ( isset($_COOKIE[$_CONF['cookie_session']] )) {
            $sess = $_COOKIE[$_CONF['cookie_session']];
            try {
                $db->conn->delete($_TABLES['sessions'], array('md5_sess_id' => $sess));
            } catch(Throwable $e) {
                $db->dbError($e->getMessage(),'');
            }
        }
		if (session_id()) {
			session_unset();
			session_destroy();
		}
    }

    return 1;
}

//@TODO - Does not seem to ever be called!!
/**
* Gets a user's data
*
* Gets user's data based on their username
*
* @param        string     $username        Username of user to get data for
* @return       array       returns user's data in an array
*
*/
function SESS_getUserData_REMOVE($username)
{
    global $_TABLES;

    $db = Database::getInstance();

    $sql = "SELECT *,format FROM `{$_TABLES['users']}`, `{$_TABLES['userprefs']}`, `{$_TABLES['dateformats']}` "
        . "WHERE {$_TABLES['dateformats']}.dfid = {$_TABLES['userprefs']}.dfid AND "
        . "{$_TABLES['userprefs']}.uid = {$_TABLES['users']}.uid AND username = ?";

    try {
        $myrow = $db->conn->fetchAssoc(
                $sql,
                array($username),
                array(Database::STRING)
        );
    } catch(Throwable $e) {
        $db->dbError($e->getMessage(),$sql);
    }

    if ($myrow === false ) {
        Log::write('system',Log::ERROR,"Error executing the SESS_getUserData() request");
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
function SESS_getUserDataFromId_REMOVE($userid)
{
    global $_TABLES;

    $db = Database::getInstance();

    $sql = "SELECT *,format FROM `{$_TABLES['dateformats']}`,`{$_TABLES['users']}`,`{$_TABLES['userprefs']}`,`{$_TABLES['userinfo']}`,`{$_TABLES['userindex']}` "
     . "WHERE {$_TABLES['dateformats']}.dfid = {$_TABLES['userprefs']}.dfid AND "
     . "{$_TABLES['userprefs']}.uid = :userid
        AND {$_TABLES['users']}.uid = :userid
        AND {$_TABLES['userinfo']}.uid = :userid
        AND {$_TABLES['userindex']}.uid= :userid";

    $cacheKey = (string) 'userdata_'.(int)$userid;

    try {
        $stmt = $db->conn->executeQuery($sql,
            array('userid'=>$userid),
            array(Database::INTEGER),
            new \Doctrine\DBAL\Cache\QueryCacheProfile(3600, $cacheKey)
            );
    } catch(Throwable $e) {
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
* Complete the login process - setup new session
*
* Complete the login process - create new session for user
*
* @param    int     $uid        User ID of logged in user
* @return   none
*
*/
function SESS_completeLogin_REMOVE($uid, $authenticated = 1)
{
    global $_TABLES, $_CONF, $_SYSTEM, $_USER;

    return;
    $db = Database::getInstance();

    $request_ip = (!empty($_SERVER['REAL_ADDR'])) ? htmlspecialchars($_SERVER['REAL_ADDR']) : '';

    // build the $_USER array
    $userdata = SESS_getUserDataFromId($uid);

    if ( isset($_CONF['enable_twofactor']) && $_CONF['enable_twofactor'] && isset($userdata['tfa_enabled']) && $userdata['tfa_enabled'] && $authenticated == 0 && function_exists('hash_hmac')) {
        if ( !SESS_isSet('login_referer')) {
            if ( isset($_SERVER['HTTP_REFERER'])) {
                SESS_setVar('login_referer',$_SERVER['HTTP_REFERER']);
            }
        }
        SEC_2FAForm($uid);
    }
    $_USER = $userdata;

    // save old session data
    $savedSessionData = json_encode($_SESSION);

    // create the session
    $sessid = SESS_newSession($_USER['uid'], $request_ip, $_CONF['session_cookie_timeout']);

	if (isset($_COOKIE[$_CONF['cookie_session']])) {
		$cookie_domain = $_CONF['cookiedomain'];
                $cookie_path   = $_CONF['cookie_path'];
                SEC_setCookie($_CONF['cookie_session'],'', time()-42000);
                //$cookie_path, $cookie_domain,$_CONF['cookiesecure'],true);
	}

    session_id($sessid);
    session_start();

    $_SESSION = json_decode($savedSessionData, true);

    // initialize session counter
    SESS_setVar('session.counter',1);

    if ( !isset($_USER['tzid']) || empty($_USER['tzid']) ) {
        $_USER['tzid'] = $_CONF['timezone'];
    }

    // Let plugins act on login event
    PLG_loginUser ($_USER['uid']);

    // check and see if they have remember me set
    $cooktime = (int) $_USER['cookietimeout'];
    if ( $cooktime > 0 ) {
        $cookieTimeout = time() + $cooktime;
        $token_ttl = $cooktime;
        // set userid cookie
        SEC_setCookie ($_CONF['cookie_name'], $_USER['uid'],
                       $cookieTimeout, $_CONF['cookie_path'],
                       $_CONF['cookiedomain'], $_CONF['cookiesecure'],true);
        $ltToken = SEC_createTokenGeneral('ltc',$token_ttl);
        // set long term cookie
        SEC_setCookie ($_CONF['cookie_password'],
                       $ltToken, $cookieTimeout,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure'],true);
    }

    try {
        $db->conn->update($_TABLES['users'],
                            array('remote_ip' => $request_ip),
                            array('uid' => $_USER['uid']),
                            array(Database::STRING, Database::INTEGER)
        );
    } catch(Throwable $e) {
        if (defined('DVLP_DEBUG')) {
            throw($e);
        }
        $db->dbError($e->getMessage(),'');
    }

    if ( $_CONF['allow_user_themes'] ) {
        // set theme cookie (or update it )
        SEC_setCookie ($_CONF['cookie_theme'], $_USER['theme'], time() + 31536000,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure'],true);
    }
}

/**
* Set session variable
*
* Sets a session variable
*
* @param    string  $name     session variable name
* @param    var     $value    value to assign
* @return   none
*
*/
function SESS_setVar($name, $value)
{
    $_SESSION[$name] = $value;
}

/**
* Get session variable
*
* Retrieve a session variable
*
* @param    string  $name     session variable name
* @return   var     the session value or 0 if not set
*
*/
function SESS_getVar($name)
{
    if ( isset($_SESSION[$name]) ) {
        return $_SESSION[$name];
    } else {
        return 0;
    }
}

/**
* Check if session variable is set
*
* Check if session variable is set
*
* @param    string  $name     session variable name
* @return   bool    true if set / false if not
*
*/
function SESS_isSet($name)
{
    return isset($_SESSION[$name]);
}

/**
* Remove a session variable
*
* remove a session variable
*
* @param    string  $name     session variable name
* @return   none
*
*/
function SESS_unSet($name)
{
    unset ($_SESSION[$name]);
}

/**
* Set current context
*
* set current context variables
*
* @param    array   $data     array of variable / value pairs of current context
* @return   none
*
*/
function SESS_setContext($data)
{
    global $_CONTEXT;

    if (is_array($data) ) {
        foreach ($data as $item => $value ) {
            $_CONTEXT[$item] = $value;
        }
    }
}

/**
* Clear context
*
* clears the current context variables (reset)
*
* @return   none
*
*/
function SESS_clearContext()
{
    global $_CONTEXT;

    $_CONTEXT = array();
}


/**
* Check IPs for match
*
* Checks a portion (or the entire) IP to determine if they match
*
* @param    string  $stored_ip  source IP address
* @param    string  $remote_ip  remote IP address
* @param    int     $force      true - force IP check regardless of
*                               configuration setting
* @return   boolean true on match / false on mis-match
*
*/
function _ipCheck( $stored_ip, $remote_ip, $force = 0 ) {
    global $_CONF;

    if ( $_CONF['session_ip_check'] == 0 && $force == 0) {
        return true;
    }

    if ( $force ) {
        $ipLength = 3;
    } else {
        $ipLength = $_CONF['session_ip_check'] + 1;
    }

	if (strpos($remote_ip, ':') !== false && strpos($stored_ip, ':') !== false) {
		$s_ip = short_ipv6($stored_ip, $ipLength);
		$r_ip = short_ipv6($remote_ip, $ipLength);
	} else {
		$s_ip = implode('.', array_slice(explode('.', $stored_ip), 0, $ipLength));
		$r_ip = implode('.', array_slice(explode('.', $remote_ip), 0, $ipLength));
	}

    if ($r_ip === $s_ip ) {
        return true;
    }
    return false;
}


/**
* Truncate IPv6 address to first block (or more if specified)
*
* Returns the first block of the specified IPv6 address and as many additional
* ones as specified in the length paramater.
*
* @param    string  $ip     IPv6 address
* @param    int     $length Number of IP blocks - 0 = empty return >3 the
*                           complete IP is returned
* @return   string  truncated IPv6 address
*
*/
function short_ipv6($ip, $length)
{
	if ($length < 1) {
		return '';
	}

	// extend IPv6 addresses
	$blocks = substr_count($ip, ':') + 1;
	if ($blocks < 9) {
		$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
	}
	if ($ip[0] == ':') {
		$ip = '0000' . $ip;
	}
	if ($length < 4) {
		$ip = implode(':', array_slice(explode(':', $ip), 0, 1 + $length));
	}

	return $ip;
}


/**
* Create session id
*
* Creates session id
*
* @return   string  Session ID
*
*/
function _createID() {
    global $_SYSTEM;

    $rand_seed = COM_makesid();

    $val = $rand_seed . microtime();
    $val = md5($val);
    $rand_seed = md5($rand_seed . $val);
    $id = substr($val, 3, 18);
    return $id;
}
?>
