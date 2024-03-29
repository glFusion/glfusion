<?php
/**
* glFusion CMS
*
* glFusion Security Library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2009 by the following authors:
*   Tony Bibbs         tony AT tonybibbs DOT com
*   Mark Limburg       mlimburg AT users DOT sourceforge DOT net
*   Vincent Furia      vmf AT abtech DOT org
*   Dirk Haun          dirk AT haun-online DOT de
*   Michael Jervis     mike AT fuckingbrit DOT co
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

if ( !isset($_SYSTEM['token_ttl']) ) $_SYSTEM['token_ttl'] = 1200;

if (!defined ('TOKEN_TTL')) {
    define('TOKEN_TTL', $_SYSTEM['token_ttl']);
}

/**
* This is the security library for glFusion.  This is used to implement glFusion's
* *nix-style security system.
*
* Programming notes:  For items you need security on you need the following for
* each record in your database:
* owner_id        | mediumint(8)
* group_id        | mediumint(8)
* perm_owner      | tinyint(1) unsigned
* perm_group      | tinyint(1) unsigned
* perm_members    | tinyint(1) unsigned
* perm_anon       | tinyint(1) unsigned
*
* For display one function can handle most needs:
* function SEC_hasAccess($owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon)
* A call to this function will allow you to determine if the current user should see the item.
*
* For the admin screen several functions will make life easier:
* function SEC_getPermissionsHTML($perm_owner,$perm_group,$perm_members,$perm_anon)
* This function displays the permissions widget with arrays for each permission
* function SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon)
* This function takes the permissions from the previous function and converts them into
* an integer for saving back to the database.
*
*/

// Turn this on to get various debug messages from the code in this library
$_SEC_VERBOSE = false;
/* Constants for account stats */
define('USER_ACCOUNT_DISABLED', 0); // Account is banned/disabled
define('USER_ACCOUNT_AWAITING_ACTIVATION', 1); // Account awaiting user to login.
define('USER_ACCOUNT_AWAITING_APPROVAL', 2); // Account awaiting moderator approval
define('USER_ACCOUNT_ACTIVE', 3); // active account
define('USER_ACCOUNT_AWAITING_VERIFICATION', 4); // Account waiting for user to complete verification

/* Constants for account types */
define('LOCAL_USER',1);
define('REMOTE_USER',2);

/* Constants for ACCESS */
define('SEC_ACCESS_RW',3);
define('SEC_ACCESS_RO',2);
define('SEC_ACCESS_NONE',0);

/* Constant for Security Token */
if (!defined('CSRF_TOKEN')) {
    define('CSRF_TOKEN', '_sectoken');
}

/**
* Returns the groups a user belongs to
*
* This is part of the GL security implementation.  This function returns
* all the groups a user belongs to.  This function is called recursively
* as groups can belong to other groups
*
* Note: this is an expensive function -- if you are concerned about speed it should only
*       be used once at the beginning of a page.  The resulting array $_GROUPS can then be
*       used through out the page.
*
* @param        int     $uid            User ID to get information for. If empty current user.
* @return   array   Associative Array grp_name -> ug_main_grp_id of group ID's user belongs to
*
*/
function SEC_getUserGroups($uid='')
{
//    global $_USER;

//    if (($_USER['uid'] == $uid) && SEC_inGroup('Root')) {
//        return \Group::getAllAvailable();
//    }
    return \Group::getAll($uid);
}

/**
  * Checks to see if a user has admin access to the "Remote Users" group
  * Admin users will probably not be members, but, User Admin, Root, and
  * group admin will have access to it. However, we can not be sure what
  * the group id for "Remote User" group is, because it's a later static
  * group, and upgraded systems could have it in any id slot.
  *
  * @param      groupid     int     The id of a group, which might be the remote users group
  * @param      groups      array   Array of group ids the user has access to.
  * @return     boolean
  */
function SEC_groupIsRemoteUserAndHaveAccess($groupid, $groups)
{
    global $_TABLES, $_CONF;

    $db = Database::getInstance();

    if (!isset($_CONF['remote_users_group_id'])) {
        $_CONF['remote_users_group_id'] = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name' => 'Remote Users'));
    }
    if ($groupid == $_CONF['remote_users_group_id']) {
        if ( in_array( 1, $groups ) || // root
             in_array( 9, $groups ) || // user admin
             in_array( 11, $groups ) // Group admin
           )
        {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
* Determines if user belongs to specified group
*
* This is part of the glFusion security implementation. This function
* looks up whether a user belongs to a specified group
*
* @param        string      $grp_to_verify      Group we want to see if user belongs to
* @param        int         $uid                ID for user to check. If empty current user.
* @param        string      $cur_grp_id         NOT USED Current group we are working with in hierarchy
* @return       boolean     true if user is in group, otherwise false
*
*/
function SEC_inGroup($grp_to_verify,$uid='',$cur_grp_id='')
{
    return \Group::inGroup($grp_to_verify, $uid);
}

/**
* Determines if current user is a moderator of any kind
*
* Checks to see if this user is a moderator for any of the GL features OR
* GL plugins
*
* @return   boolean     returns if user has any .moderate rights
*
*/
function SEC_isModerator()
{
    global $_USER,$_RIGHTS;

    // Loop through GL core rights.
    for ($i = 0; $i < count($_RIGHTS); $i++) {
        if (stristr($_RIGHTS[$i],'.moderate')) {
            return true;
        }
    }

    // If we get this far they are not a glFusion moderator
    // So, let's return if they're a plugin moderator

    return PLG_isModerator();
}

/**
* Determines if current user is an Admin of any kind
*
* Checks to see if this user is a administrator for any of the GL features OR
* GL plugins
*
* @return   boolean     returns true if user has any admin rights
*
*/
function SEC_isAdmin()
{
    return SEC_hasRights('system.root,story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit,search.admin,actions.admin,autotag.admin,cache.admin,database.admin,env.admin,logo.admin,menu.admin,social.admin,upgrade.admin','OR') OR (count(PLG_getAdminOptions()) > 0) OR SEC_inGroup('Root');
}


/**
* Determines if user id is a remote user
*
* Checks to see if this user is a remote user
*
* @return   boolean     returns true if user is a remote user
*
*/
function SEC_isRemoteUser($uid)
{
    global $_TABLES;

    $db = Database::getInstance();

    static $remotecheck = array();

    if ( !isset($remotecheck[$uid])) {
        $remoteuserstatus[$uid] = 0;
        $remoteuserstatus = $db->getItem($_TABLES['users'],'account_type',array('uid' => (int) $uid));
        if ( $remoteuserstatus & REMOTE_USER ) {
            $remotecheck[$uid] = 1;
        }
    }
    if ( $remotecheck[$uid] == 1 ) {
        return true;
    }
    return false;
}

/**
* Determines if user id is a local user
*
* Checks to see if this user is a local user
*
* @return   boolean     returns true if user is a local user
*
*/
function SEC_isLocalUser($uid)
{
    global $_TABLES;

    $db = Database::getInstance();

    static $localcheck = array();

    if ( !isset($localcheck[$uid])) {
        $localusercheck[$uid] = 0;
        $localuserstatus = $db->getItem($_TABLES['users'],'account_type',array('uid' => (int) $uid));
        if ( $localuserstatus & LOCAL_USER ) {
            $localcheck[$uid] = 1;
        }
    }
    if ( isset($localcheck[$uid]) && $localcheck[$uid] == 1 ) {
        return true;
    }
    return false;
}

/**
* Checks to see if current user has access to a topic
*
* Checks to see if current user has access to a topic
*
* @param        string      $tid        ID for topic to check on
* @return       int     returns 3 for read/edit 2 for read only 0 for no access
*
*/
function SEC_hasTopicAccess($tid)
{
    global $_TABLES;

    if (empty($tid)) {
        return 0;
    }

    $db = Database::getInstance();

    $A = $db->conn->fetchAssoc(
            "SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM `{$_TABLES['topics']}` WHERE tid = ?",
            array($tid),
            array(Database::STRING)
    );

    return SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);
}

/**
* Checks if current user has access to the given object
*
* This function takes the access info from a glFusion object
* and let's us know if they have access to the object
* returns 3 for read/edit, 2 for read only and 0 for no
* access
*
* @param        int     $owner_id       ID of the owner of object
* @param        int     $group_id       ID of group object belongs to
* @param        int     $perm_owner     Permissions the owner has
* @param        int     $perm_group     Permissions the gorup has
* @param        int     $perm_members   Permissions logged in members have
* @param        int     $perm_anon      Permissions anonymous users have
* @return       int     returns 3 for read/edit 2 for read only 0 for no access
*
*/
function SEC_hasAccess($owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon)
{
    global $_USER;

    // Cache current user id
    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    // If user is in Root group then return full access
    if (SEC_inGroup('Root')) {
        return 3;
    }

    // If user is owner then return 1 now
    if ($uid == $owner_id) return $perm_owner;

    // Not private, if user is in group then give access
    if (SEC_inGroup($group_id)) {
        return $perm_group;
    } else {
        if ($uid == 1) {
            // This is an anonymous user, return it's rights
            return $perm_anon;
        } else {
            // This is a logged in member, return their rights
            return $perm_members;
        }
    }
}

/**
* Checks if current user has rights to a feature
*
* Takes either a single feature or an array of features and returns
* an array of whether the user has those rights
*
* @param        string|array        $features       Features to check
* @param        string              $operator       Either 'and' or 'or'. Default is 'and'.  Used if checking more than one feature.
* @return       boolean     Return true if current user has access to feature(s), otherwise false.
*
*/
function SEC_hasRights($features,$operator='AND')
{
    global $_USER, $_RIGHTS, $_SEC_VERBOSE;

    if (SEC_inGroup('Root')) {
        return true;
    }

    if (is_string($features)) {
        $features = explode(',',$features);
    }

    if (is_array($features)) {
        // check all values passed
        for ($i = 0; $i < count($features); $i++) {
            if ($operator == 'OR') {
                // OR operator, return as soon as we find a true one
                if (in_array($features[$i],$_RIGHTS)) {
                    if ($_SEC_VERBOSE) {
                        Log::write('system',Log::DEBUG,'SECURITY: user has access to ' . $features[$i]);
                    }
                    return true;
                }
            } else {
                // this is an "AND" operator, bail if we find a false one
                if (!in_array($features[$i],$_RIGHTS)) {
                    if ($_SEC_VERBOSE) {
                        Log::write('system',Log::DEBUG,'SECURITY: user does not have access to ' . $features[$i]);
                    }
                    return false;
                }
            }
        }

        if ($operator == 'OR') {
            if ($_SEC_VERBOSE) {
                Log::write('system',Log::DEBUG,'SECURITY: user does not have access to ' . $features[$i]);
            }
            return false;
        } else {
            if ($_SEC_VERBOSE) {
                Log::write('system',Log::DEBUG,'SECURITY: user has access to ' . $features[$i]);
            }
            return true;
        }
    } else {
        // Check the one value
        if ($_SEC_VERBOSE) {
            if (in_array($features,$_RIGHTS)) {
                Log::write('system',Log::DEBUG,'SECURITY: user has access to ' . $features);
            } else {
                Log::write('system',Log::DEBUG,'SECURITY: user does not have access to ' . $features);
            }
        }
        return in_array($features,$_RIGHTS);
    }
}

/**
* Shows security control for an object
*
* This will return the HTML needed to create the security control see on the admin
* screen for GL objects (i.e. stories, etc)
*
* @param        int     $perm_owner     Permissions the owner has 1 = edit 2 = read 3 = read/edit
* @param        int     $perm_group     Permission the group has
* @param        int     $perm_members   Permissions logged in members have
* @param        int     $perm_anon      Permissions anonymous users have
* @return       string  needed HTML (table) in HTML $perm_owner = array of permissions [edit,read], etc edit = 1 if permission, read = 2 if permission
*
*/
function SEC_getPermissionsHTML($perm_owner,$perm_group,$perm_members,$perm_anon)
{
    global $LANG_ACCESS, $_CONF;

    $retval = '';

    $perm_templates = new Template($_CONF['path_layout'] . 'admin/common');
    $perm_templates->set_file(array('editor'=>'edit_permissions.thtml'));

    $perm_templates->set_var ('owner', $LANG_ACCESS['owner']);
    $perm_templates->set_var ('group', $LANG_ACCESS['group']);
    $perm_templates->set_var ('members', $LANG_ACCESS['members']);
    $perm_templates->set_var ('anonymous', $LANG_ACCESS['anonymous']);

    // Owner Permissions
    if ($perm_owner >= 2) {
        $perm_templates->set_var ('owner_r_checked',' checked="checked"');
    }
    if ($perm_owner == 3) {
        $perm_templates->set_var ('owner_e_checked',' checked="checked"');
    }
    // Group Permissions
    if ($perm_group >= 2) {
        $perm_templates->set_var ('group_r_checked',' checked="checked"');
    }
    if ($perm_group == 3) {
        $perm_templates->set_var ('group_e_checked',' checked="checked"');
    }
    // Member Permissions
    if ($perm_members == 2) {
        $perm_templates->set_var ('members_checked',' checked="checked"');
    }
    // Anonymous Permissions
    if ($perm_anon == 2) {
        $perm_templates->set_var ('anon_checked',' checked="checked"');
    }

    $perm_templates->parse('output','editor');
    $retval .= $perm_templates->finish($perm_templates->get_var('output'));

    return $retval;
}

/**
* Gets everything a user has permissions to within the system
*
* This is part of the glFusion security implmentation.  This function
* will get all the permissions the current user has call itself recursively.
*
* @param    int     $grp_id     DO NOT USE (Used for reccursion) Current group function is working on
* @param    int     $uid        User to check, if empty current user.
* @return   string  returns comma delimited list of features the user has access to
*
*/
function SEC_getUserPermissions($grp_id='',$uid='')
{
    global $_TABLES, $_USER, $_SEC_VERBOSE, $_GROUPS;

    $retval = '';

    $db = Database::getInstance();

    if ($_SEC_VERBOSE) {
        Log::write('system',Log::DEBUG,"**********inside SEC_getUserPermissions(grp_id=$grp_id)**********");
    }

    // Get user ID if we don't already have it
    if (empty ($uid)) {
        if (COM_isAnonUser()) {
            $uid = 1;
        } else {
            $uid = $_USER['uid'];
        }
    }

    if ( (isset($_USER['uid']) && $uid == $_USER['uid'])) {
        if (empty ($_GROUPS)) {
            $_GROUPS = SEC_getUserGroups ($uid);
        }
        $groups = $_GROUPS;
    } else {
        $groups = SEC_getUserGroups ($uid);
    }

    if ( is_array($groups) && count($groups) > 0 ) {
        $glist = join(',', $groups);
        $groups = explode(',',$glist);

        $stmt = $db->conn->executeQuery(
            "SELECT DISTINCT ft_name FROM `{$_TABLES['access']}`,`{$_TABLES['features']}`
                WHERE `acc_ft_id`=`ft_id` AND `acc_grp_id` IN (?)",
            array($groups),
            array(Database::PARAM_STR_ARRAY)
        );

        $commaControl = 0;
        while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
            if ($_SEC_VERBOSE) {
                Log::write('system',Log::DEBUG,'Adding right ' . $A['ft_name'] . ' in SEC_getUserPermissions');
            }
            if ($commaControl > 0) {
                $retval .= ',';
            }
            $retval .= $A['ft_name'];
            $commaControl++;
        }
    }

    return $retval;
}

/**
* Converts permissions to numeric values
*
* This function will take all permissions for an object and get the numeric value
* that can then be used to save the database.
*
* @param        array       $perm_owner     Array of owner permissions  These arrays are set up by SEC_getPermissionsHTML
* @param        array       $perm_group     Array of group permissions
* @param        array       $perm_members   Array of member permissions
* @param        array       $perm_anon      Array of anonymous user permissions
* @return       array       returns numeric equivalent for each permissions array (2 = read, 3=edit/read)
* @see  SEC_getPermissionsHTML
* @see  SEC_getPermissionValue
*
*/
function SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon)
{
    global $_SEC_VERBOSE;

    if ($_SEC_VERBOSE) {
        Log::write('system',Log::DEBUG,'**** Inside SEC_getPermissionValues ****');
    }

    if (is_array($perm_owner)) {
        $perm_owner = SEC_getPermissionValue($perm_owner);
    } else {
        $perm_owner = 0;
    }

    if (is_array($perm_group)) {
        $perm_group = SEC_getPermissionValue($perm_group);
    } else {
        $perm_group = 0;
    }

    if (is_array($perm_members)) {
        $perm_members = SEC_getPermissionValue($perm_members);
    } else {
        $perm_members = 0;
    }

    if (is_array($perm_anon)) {
        $perm_anon = SEC_getPermissionValue($perm_anon);
    } else {
        $perm_anon = 0;
    }

    if ($_SEC_VERBOSE) {
        Log::write('system',Log::DEBUG,'perm_owner = ' . $perm_owner);
        Log::write('system',Log::DEBUG,'perm_group = ' . $perm_group);
        Log::write('system',Log::DEBUG,'perm_member = ' . $perm_members);
        Log::write('system',Log::DEBUG,'perm_anon = ' . $perm_anon);
        Log::write('system',Log::DEBUG,'**** Leaving SEC_getPermissionValues ****');
    }

    return array($perm_owner,$perm_group,$perm_members,$perm_anon);
}

/**
* Converts permission array into numeric value
*
* This function converts an array of permissions for either
* the owner/group/members/anon and returns the numeric
* equivalent.  This is typically called by the admin screens
* to prepare the permissions to be save to the database
*
* @param        array       $perm_x     Array of permission values
* @return       int         int representation of a permission array 2 = read 3 = edit/read
* @see SEC_getPermissionValues
*
*/
function SEC_getPermissionValue($perm_x)
{
    global $_SEC_VERBOSE;

    if ($_SEC_VERBOSE) {
        Log::write('system',Log::DEBUG,'**** Inside SEC_getPermissionValue ***');
    }

    $retval = 0;

    for ($i = 1; $i <= sizeof($perm_x); $i++) {
        if ($_SEC_VERBOSE) {
            Log::write('system',Log::DEBUG,"perm_x[$i] = " . current($perm_x));
        }
        $retval = $retval + current($perm_x);
        next($perm_x);
    }

    // if they have edit rights, assume read rights
    if ($retval == 1) {
        $retval = 3;
    }

    if ($_SEC_VERBOSE) {
        Log::write('system',Log::DEBUG,"Got $retval permission value");
        Log::write('system',Log::DEBUG,'**** Leaving SEC_getPermissionValue ***');
    }

    return $retval;
}

/**
* Return the group to a given feature.
*
* Scenario: We have a feature and we want to know from which group the user
* got this feature. Always returns the lowest group ID, in case the feature
* has been inherited from more than one group.
*
* @param    string  $feature    the feature, e.g 'story.edit'
* @param    int     $uid        (optional) user ID
* @return   int                 group ID or 0
*
*/
function SEC_getFeatureGroup ($feature, $uid = '')
{
    $grpFeature = \Group::withFeature($feature, $uid);
    return $grpFeature[0];
}


/**
* Get an array of all groups this group belongs to.
*
* @param   basegroup   int     id of group
* @return              array   array of all groups 'basegroup' belongs to
*
*/
function SEC_getGroupList ($basegroup)
{
    global $_TABLES;

    $db = Database::getInstance();

    $to_check = array ();
    array_push ($to_check, $basegroup);

    $checked = array ();

    while (sizeof ($to_check) > 0) {
        $thisgroup = array_pop ($to_check);
        if ($thisgroup > 0) {

            $stmt = $db->conn->executeQuery(
                        "SELECT ug_grp_id FROM `{$_TABLES['group_assignments']}`
                            WHERE ug_main_grp_id = ?",
                        array($thisgroup),
                        array(Database::INTEGER)
            );
            while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
                if (!in_array ($A['ug_grp_id'], $checked)) {
                    if (!in_array ($A['ug_grp_id'], $to_check)) {
                        array_push ($to_check, $A['ug_grp_id']);
                    }
                }
            }
            $checked[] = (int) $thisgroup;
        }
    }
    return $checked;
}

/**
* Attempt to login a user.
*
* Checks a users username and password against the database. Returns
* users status.
*
* @param    string  $username   who is logging in?
* @param    string  $password   what they claim is their password
* @param    int     $uid        This is an OUTPUT param, pass by ref,
*                               sends back UID inside it.
* @return   int                 user status, -1 for fail.
*
*/
function SEC_authenticateUser($username, $password, $service, &$uid)
{
    global $_CONF, $_SYSTEM, $_TABLES, $LANG01;

    $rc = -1;
    $options = array();
    $credentials = array();
    $credentials['username'] = isset($username) ? $username : '';
    $credentials['password'] = isset($password) ? $password : '';

    $service = preg_replace( '/[^a-zA-Z0-9\-_]/', '',$service );

    if ( file_exists($_CONF['path'].'lib/authentication/'.$service.'/'.$service.'.class.php') ) {
        require_once $_CONF['path'].'lib/authentication/'.$service.'/'.$service.'.class.php';

    	$className = 'Authenticate'.$service;
        $authenticate = new $className();
        $rc = $authenticate->onUserAuthenticate($credentials,$options,$uid);
    }
    return $rc;
}

/**
* Attempt to login a user.
*
* Checks a users username and password against the database. Returns
* users status.
*
* @param    string  $username   who is logging in?
* @param    string  $password   what they claim is their password
* @param    int     $uid        This is an OUTPUT param, pass by ref,
*                               sends back UID inside it.
* @return   int                 user status, -1 for fail.
*
*/
function SEC_authenticate($username, $password, &$uid)
{
    global $_CONF, $_SYSTEM, $_TABLES, $LANG01;

    $db = Database::getInstance();

    $password = trim(str_replace(array("\015", "\012"), '', $password));

    $U = $db->conn->fetchAssoc(
            "SELECT status, passwd, email, uid FROM `{$_TABLES['users']}`
                 WHERE username=? AND (account_type & ?)",
            array($username,LOCAL_USER),
            array(Database::STRING,Database::INTEGER)
    );
    if ($U === false || $U === NULL) {
        $U = $db->conn->fetchAssoc(
                "SELECT status, passwd, email, uid FROM `{$_TABLES['users']}`
                    WHERE email=? AND (account_type & ?)",
                array($username,LOCAL_USER),
                array(Database::STRING,Database::INTEGER)
        );
    }

    if ($U !== false && $U !== NULL) {
        $uid = $U['uid'];
        if ($U['status'] == USER_ACCOUNT_DISABLED) {
            // banned, jump to here to save an md5 calc.
            return USER_ACCOUNT_DISABLED;
        } elseif ( !SEC_check_hash($password, $U['passwd']) ) {
            return -1;
        } elseif ($U['status'] == USER_ACCOUNT_AWAITING_APPROVAL) {
            return USER_ACCOUNT_AWAITING_APPROVAL;
        } elseif ($U['status'] == USER_ACCOUNT_AWAITING_VERIFICATION ) {
            return USER_ACCOUNT_AWAITING_VERIFICATION;
        } elseif ($U['status'] == USER_ACCOUNT_AWAITING_ACTIVATION) {
            // Awaiting user activation, activate:
            $stmt = $db->conn->update(
                        $_TABLES['users'],
                        array('status' => USER_ACCOUNT_ACTIVE),
                        array('username' => $username),
                        array(Database::INTEGER,Database::STRING)
            );
            return USER_ACCOUNT_ACTIVE;
        } else {
            return $U['status']; // just return their status
        }
    } else {
        $tmp = $LANG01[32] . ": '" . $username;
        return -1;
    }
}

/**
* Return the current user status for a user.
*
* NOTE:     May not return for banned/non-approved users.
*
* @param    int  $userid   Valid uid value.
* @return   int            user status, 0-3
*
*/
function SEC_checkUserStatus($userid)
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();

    // Check user status
    $status = $db->getItem($_TABLES['users'], 'status', array('uid' => (int) $userid),array(Database::INTEGER));

    // only do redirects if we aren't on users.php in a valid mode (logout or
    // default)
    if (strpos($_SERVER['PHP_SELF'], 'users.php') === false) {
        $redirect = true;
    } else {
        if (empty($_REQUEST['mode']) || ($_REQUEST['mode'] == 'logout')) {
            $redirect = false;
        } else {
            $redirect = true;
        }
    }
    if ($status == USER_ACCOUNT_AWAITING_ACTIVATION) {
        $db->conn->update(
                $_TABLES['users'],
                array('status' => USER_ACCOUNT_ACTIVE),
                array('uid' => $userid),
                array(Database::INTEGER, Database::INTEGER)
        );
    } elseif ($status == USER_ACCOUNT_AWAITING_APPROVAL) {
        // If we aren't on users.php with a default action then go to it
        if ($redirect) {
            Log::write('system',Log::WARNING,"SECURITY: Attempted Cookie Session login from user awaiting approval $userid.");
            echo COM_refresh($_CONF['site_url'] . '/users.php?msg=70');
            exit;
        }
    } elseif ($status == USER_ACCOUNT_DISABLED) {
        if ($redirect) {
            Log::write('system',Log::WARNING,"SECURITY: Attempted Cookie Session login from banned user $userid.");
            echo COM_refresh($_CONF['site_url'] . '/users.php?msg=69');
            exit;
        }
    }
    return $status;
}

/**
  * Check to see if we can authenticate this user with a remote server (i.e.; ldap)
  *
  * A user has not managed to login localy, but has an @ in their user
  * name and we have enabled distributed authentication. Firstly, try to
  * see if we have cached the module that we used to authenticate them
  * when they signed up (i.e. they've actualy changed their password
  * elsewhere and we need to synch.) If not, then try to authenticate
  * them with /every/ authentication module. If this suceeds, create
  * a user for them.
  *
  * @param  string  $loginname Their username
  * @param  string  $passwd The password entered
  * @param  string  $server The server portion of $username
  * @param  string  $uid OUTPUT parameter, pass it by ref to get uid back.
  * @return int     user status, -1 for fail.
  */
function SEC_remoteAuthentication(&$loginname, $passwd, $service, &$uid)
{
    global $_CONF, $_TABLES;

    $db = Database::getInstance();
    $filter = new \sanitizer();

    /* First try a local cached login */
    $remoteusername = $loginname;
    $remoteservice = $service;
    $U = $db->conn->fetchAssoc(
            "SELECT passwd, status, uid FROM `{$_TABLES['users']}`
                WHERE remoteusername=? AND remoteservice=?",
                array(
                    $loginname,
                    $service
                ),
                array(
                    Database::STRING,
                    Database::STRING
                )
    );

    if ($U !== false && $U !== NULL) {
        $uid = $U['uid'];
        $mypass = $U['passwd']; // also used to see if the user existed later.
        if ($mypass == SEC_encryptPassword($passwd)) {
            /* Valid password for cached user, return status */
            return $U['status'];
        }
    }

    $service = $filter->sanitizeFilename($service,false);
    $servicefile = $_CONF['path_system'] . 'classes/authentication/' . $service
                 . '.auth.class.php';
    if (file_exists($servicefile)) {
        require_once $servicefile;

        $authmodule = new $service();
        if ($authmodule->authenticate($loginname, $passwd)) {
            /* check to see if they have logged in before: */
            if (empty($mypass)) {
                // no such user, create them

                // Check to see if their remoteusername is unique locally
                $checkName = $db->getItem($_TABLES['users'], 'username',
                                        array('username' => $loginname));
                if (!empty($checkName)) {
                    // no, call custom function.
                    if (function_exists('CUSTOM_uniqueRemoteUsername')) {
                        $loginname = CUSTOM_uniqueRemoteUsername($loginname,
                                                                 $service);
                    }
                }
                USER_createAccount($loginname, $authmodule->email, SEC_encryptPassword($passwd), $authmodule->fullname, $authmodule->homepage, $remoteusername, $remoteservice);
                $uid = $db->getItem($_TABLES['users'], 'uid',
                            array('remoteusername' => $loginname, 'remoteservice' => $service));
                // Store full remote account name:

                $db->conn->update(
                        $_TABLES['users'],
                        array(
                            'remoteusername' => $loginname,
                            'remoteservice'  => $service,
                            'status'        => USER_ACCOUNT_ACTIVE
                        ),
                        array(
                            'uid' => $uid,
                        ),
                        array(
                            Database::STRING,
                            Database::STRING,
                            Database::INTEGER,
                            Database::INTEGER
                        )
                );
                // Add to remote users:
                $remote_grp = $db->getItem($_TABLES['groups'], 'grp_id',
                                         array('grp_name' => 'Remote Users'));

                $db->conn->insert(
                        $_TABLES['group_assignments'],
                        array(
                            'ug_main_grp_id' => $remote_grp,
                            'ug_uid'    => $uid
                        ),
                        array(
                            Database::INTEGER,
                            Database::INTEGER
                        )
                );
                return USER_ACCOUNT_ACTIVE; // Remote auth precludes usersubmission,
                                              // and integrates user activation, see?
            } else {
                // user existed, update local password:
                $db->conn->update(
                        $_TABLES['users'],
                        array('passwd' => SEC_encryptPassword($passwd)),
                        array(
                            'remoteusername' => $remoteusername,
                            'remoteservice' => $remoteservice
                        ),
                        array(
                            Database::STRING,       // passwd
                            Database::STRING,       // remote username
                            Database::STRING,       // remoteservice
                        )
                );
                // and return their status
                $retStatus = $db->getItem(
                                $_TABLES['users'],
                                'status',
                                array(
                                    'remoteusername' => $remoteusername,
                                    'remoteservice' => $remoteservice
                                )
                );
                return $retStatus;
            }
        } else {
            return -1;
        }
    } else {
        return -1;
    }
}

/**
* Return available modules for Remote Authentication
*
* @return   array   Names of available remote authentication modules
*
*/
function SEC_collectRemoteAuthenticationModules()
{
    global $_CONF;

    $modules = array();

    $modulespath = $_CONF['path_system'] . 'classes/authentication/';
    if (is_dir($modulespath)) {
        $folder = opendir($modulespath);
        while (($filename = @readdir($folder)) !== false) {
            $pos = strpos($filename, '.auth.class.php');
            if ($pos && (substr($filename, strlen($filename) - 4) == '.php')) {
                $modules[] = substr($filename, 0, $pos);
            }
        }
    }

    return $modules;
}

/**
  * Add user to a group
  *
  * @author Trinity L Bays, trinity93 AT gmail DOT com
  *
  * @param  string  $uid    Their user id
  * @param  string  $gname  The group name
  * @return boolean status  true or false.
  */
function SEC_addUserToGroup($uid, $gname)
{
    global $_TABLES, $_CONF;

    $uid = (int) $uid;

    $db = Database::getInstance();

    $remote_grp = (int) $db->getItem (
                            $_TABLES['groups'],
                            'grp_id',
                            array('grp_name' => $gname)
                        );
    if ( $remote_grp !== false && $remote_grp !== NULL && $remote_grp !== 0 ) {
        $db->conn->insert(
                $_TABLES['group_assignments'],
                array(
                    'ug_main_grp_id' => $remote_grp,
                    'ug_uid' => $uid
                ),
                array(
                    Database::INTEGER,
                    Database::INTEGER
                )
        );
        return true;
    }
    return false;
}

/**
* Set default permissions for an object
*
* @param    array   $A                  target array
* @param    array   $use_permissions    permissions to set
*
*/
function SEC_setDefaultPermissions (&$A, $use_permissions = array ())
{
    if (!is_array ($use_permissions) || (count ($use_permissions) != 4)) {
        $use_permissions = array (3, 2, 2, 2);
    }

    // sanity checks
    if (($use_permissions[0] > 3) || ($use_permissions[0] < 0) ||
            ($use_permissions[0] == 1)) {
        $use_permissions[0] = 3;
    }
    if (($use_permissions[1] > 3) || ($use_permissions[1] < 0) ||
            ($use_permissions[1] == 1)) {
        $use_permissions[1] = 2;
    }
    if (($use_permissions[2] != 2) && ($use_permissions[2] != 0)) {
        $use_permissions[2] = 2;
    }
    if (($use_permissions[3] != 2) && ($use_permissions[3] != 0)) {
        $use_permissions[3] = 2;
    }

    $A['perm_owner']   = $use_permissions[0];
    $A['perm_group']   = $use_permissions[1];
    $A['perm_members'] = $use_permissions[2];
    $A['perm_anon']    = $use_permissions[3];
}


/**
* Common function used to build group access SQL
* Field ID can include a table identifier, e.g. 'tbl.fldname'
*
* @param   string  $clause    Optional parm 'WHERE' - default is 'AND'
* @param   string  $field     Optional field name, default is 'grp_access'
* @return  string  $groupsql  Formatted SQL string to be appended in calling script SQL statement
*/
function SEC_buildAccessSql ($clause = 'AND', $field = 'grp_access', $uid = 0)
{
    global $_GROUPS;

    if ($uid == 0) {
        if (empty ($_GROUPS)) {
            $userGroups = SEC_getUserGroups();
        } else {
            $userGroups = $_GROUPS;
        }
    } else {
        $userGroups = SEC_getUserGroups($uid);
    }
    $groupsql = '';
    if (count($userGroups) == 1) {
        $groupsql .= " $clause $field = '" . current($userGroups) ."'";
    } else {
        $groupsql .= " $clause $field IN (" . implode(',',array_values($userGroups)) .")";
    }

    return $groupsql;
}

/**
* Remove a feature from the database entirely.
*
* This function can be used by plugins during uninstall.
*
* @param    string  $feature_name   name of the feature, e.g. 'foo.edit'
* @param    boolean $logging        whether to log progress in error.log
* @return   void
*
*/
function SEC_removeFeatureFromDB ($feature_name, $logging = false)
{
    global $_TABLES;

    $db = Database::getInstance();

    if (!empty ($feature_name)) {
        $feat_id = $db->getItem(
                        $_TABLES['features'],
                        'ft_id',
                        array('ft_name' => $feature_name),
                        array(Database::STRING)
        );

        if ($feat_id !== false && $feat_id !== NULL) {
            // Before removing the feature itself, remove it from all groups
            if ($logging) {
                Log::write('system',Log::DEBUG,"Attempting to remove '$feature_name' rights from all groups");
            }
            $db->conn->delete(
                $_TABLES['access'],
                array('acc_ft_id' => $feat_id),
                array(Database::INTEGER)
            );
            if ($logging) {
                Log::write('system',Log::DEBUG,'...success');
            }

            // now remove the feature itself
            if ($logging) {
                Log::write('system',Log::DEBUG,"Attempting to remove the '$feature_name' feature");
            }
            $db->conn->delete(
                $_TABLES['features'],
                array('ftp_id' => $feat_id),
                array(Database::INTEGER)
            );
            if ($logging) {
                Log::write('system',Log::DEBUG,'...success');
            }
        } else if ($logging) {
            Log::write('system',Log::WARNING,"SEC_removeFeatureFromDB: Feature '$feature_name' not found.");
        }
    }
}

/**
* Create a group dropdown
*
* Creates the group dropdown menu that's used on pretty much every admin page
*
* @param    int     $group_id   current group id (to be selected)
* @param    int     $access     access permission
* @param    string  $var_name   Optional variable name, "group_id" if empty
* @return   string              HTML for the dropdown
*
*/
function SEC_getGroupDropdown ($group_id, $access, $var_name='group_id')
{
    global $_TABLES, $_CONF;

    $db = Database::getInstance();

    $T = new Template($_CONF['path_layout'] . '/fields');
    $T->set_file(array(
        'dropdown' => 'selection.thtml',
        'optionlist' => 'optionlist.thtml',
    ) );
    $T->set_var('var_name', $var_name);

    if ($access == 3) {
        $usergroups = SEC_getUserGroups ();
        uksort($usergroups, "strnatcasecmp");

        $T->set_block('optionlist', 'options', 'opts');
        foreach ($usergroups as $ug_name => $ug_id) {
            $T->set_var(array(
                'opt_name' => ucfirst($ug_name),
                'opt_value' => $ug_id,
                'selected' => ($group_id == $ug_id),
            ) );
            $T->parse('opts', 'options', true);
        }
        $T->parse('option_list', 'opts');
    } else {
        // They can't set the group then
        $group_name = $db->getItem (
                        $_TABLES['groups'],
                        'grp_name',
                        array('grp_id' => $group_id),
                        array(Database::STRING)
        );
        $T->set_var(array(
            'item_name' => $group_name,
            'item_id' => $group_id,
        ) );
    }
    $T->parse('output', 'dropdown');
    $groupdd = $T->finish($T->get_var('output'));
    return $groupdd;
}

/**
* Create a group dropdown of all groups on the system - regardless of user's permission
*
* Creates the group dropdown menu that's used on pretty much every admin page
*
* @param    int     $group_id   current group id (to be selected)
* @param    int     $access     access permission
* @param    string  $var_name   Optional variable name, "group_id" if empty
* @return   string              HTML for the dropdown
*
*/
function SEC_getGroupDropdownAll ($group_id, $access, $var_name='group_id')
{
    global $_TABLES, $_CONF;

    $db = Database::getInstance();

    $T = new Template($_CONF['path_layout'] . '/fields');
    $T->set_file(array(
        'dropdown' => 'selection.thtml',
        'optionlist' => 'optionlist.thtml',
    ) );
    $T->set_var('var_name', $var_name);

    if ($access == 3) {
        $usergroups = \Group::getAllAvailable();
        uksort($usergroups, "strnatcasecmp");

        $T->set_block('optionlist', 'options', 'opts');
        foreach ($usergroups as $ug_name => $ug_id) {
            $T->set_var(array(
                'opt_name' => ucfirst($ug_name),
                'opt_value' => $ug_id,
                'selected' => ($group_id == $ug_id),
            ) );
            $T->parse('opts', 'options', true);
        }
        $T->parse('option_list', 'opts');
    } else {
        // They can't set the group then
        $group_name = $db->getItem (
                        $_TABLES['groups'],
                        'grp_name',
                        array('grp_id' => $group_id),
                        array(Database::STRING)
        );
        $T->set_var(array(
            'item_name' => $group_name,
            'item_id' => $group_id,
        ) );
    }
    $T->parse('output', 'dropdown');
    $groupdd = $T->finish($T->get_var('output'));
    return $groupdd;
}

/**
* Encrypt password
*
* For now, this is only a wrapper function to get all the direct calls to
* md5() out of the core code so that we can switch to another method of
* encoding / encrypting our passwords in some future release ...
*
* @param    string  $password   the password to encrypt, in clear text
* @return   string              encrypted password
*
*/
function SEC_encryptPassword($password)
{
    return SEC_hash($password);
}

/**
  * Generate a security token.
  *
  * This generates and stores a one time security token. Security tokens are
  * added to forms and urls in the admin section as a non-cookie double-check
  * that the admin user really wanted to do that...
  *
  * @param $ttl int Time to live for token in seconds. Default is 20 minutes.
  *
  * @return string  Generated token, it'll be an MD5 hash (32chars)
  */
function SEC_createToken($ttl = TOKEN_TTL)
{
    global $_CONF, $_SYSTEM, $_USER, $_TABLES;

    static $_tokenKey;

    if ($ttl == -1 ) {
        $tokenKey = '';
        return;
    }

    if (isset($_tokenKey) && !empty($_tokenKey) ) {
        return $_tokenKey;
    }

    $db = Database::getInstance();

    $uid = isset($_USER['uid']) ? $_USER['uid'] : 1;

    if ( isset($_SYSTEM['token_ip']) && $_SYSTEM['token_ip'] == true ) {
        $pageURL  = $_SERVER['REAL_ADDR'];
    } else {
        $pageURL = COM_getCurrentURL();
    }

    /* Generate the token */
    $token = md5($uid.$pageURL.uniqid (mt_rand (), 1));

    /* Destroy exired tokens: */

    $stmt = $db->conn->executeUpdate(
                "DELETE FROM `{$_TABLES['tokens']}`
                  WHERE (DATE_ADD(created, INTERVAL ttl SECOND) < ?)
                  AND (ttl > 0)",
                array($_CONF['_now']->toMySQL(true)),
                array(Database::STRING)
    );

    /* Destroy tokens for this user/url combination */
    if ( !COM_isAnonUser()) {
        $db->conn->delete(
                $_TABLES['tokens'],
                array(
                    'owner_id' => $uid,
                    'urlfor' => $pageURL
                ),
                array(
                    Database::INTEGER,
                    Database::STRING
                )
        );
    }
    /* Create a token for this user/url combination */
    /* NOTE: TTL mapping for PageURL not yet implemented */

    try {
        $db->conn->insert(
                $_TABLES['tokens'],
                array(
                    'token' => $token,
                    'created' => $_CONF['_now']->toMySQL(true),
                    'owner_id' => $uid,
                    'urlfor' => $pageURL,
                    'ttl' => $ttl
                ),
                array(
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                    Database::STRING,
                    Database::INTEGER
                )
        );
    } catch(Throwable $e) {
        Log::write('system',Log::ERROR,'Error inserting token into DB: ' . $e->getMessage());
    }


    $tokenKey = $token;

    /* And return the token to the user */
    return $token;
}


/**
* Check a security token.
*
* Checks the POST and GET data for a security token, if one exists, validates
* that it's for this user and URL. If the token is not valid, it asks the user
* to re-authenticate and resends the request if authentication was successful.
*
* @return   boolean     true if the token is valid; does not return if not!
*
*/
function SEC_checkToken()
{
    global $_CONF, $_USER, $LANG20, $LANG_ADMIN;

    if (_sec_checkToken()) {
        SEC_createToken(-1);
        return true;
    }

    if ( !SEC_isLocalUser($_USER['uid']) ) {
        return false;
    }

    // determine the destination of this request
    $destination = COM_getCurrentURL();

    // validate the destination is not blank and is part of our site...
    if ( $destination == '' ) {
        $destination = $_CONF['site_url'] . '/index.php';
    }
    if ( substr($destination, 0,strlen($_CONF['site_url'])) != $_CONF['site_url']) {
        $destination = $_CONF['site_url'] . '/index.php';
    }
    $method   = strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' ? 'GET' : 'POST';
    $postdata = serialize($_POST);
    $getdata  = serialize($_GET);
    $filedata = '';
    if (! empty($_FILES)) {
        foreach ($_FILES as $key => $file) {
            if ( is_array($file['name']) ) {
                foreach ($file['name'] as $offset => $filename) {
                    if ( !empty($file['name'][$offset]) ) {
                        $filename = basename($file['tmp_name'][$offset]);
                        move_uploaded_file($file['tmp_name'][$offset],$_CONF['path_data'] . 'temp/'. $filename);
                        $_FILES[$key]['tmp_name'][$offset] = $filename;
                    }
                }
            } else {
                if (! empty($file['name']) && !empty($file['tmp_name'])) {
                    $filename = basename($file['tmp_name']);
                    move_uploaded_file($file['tmp_name'],$_CONF['path_data'] . 'temp/'. $filename);
                    $_FILES[$key]['tmp_name'] = $filename;
                }
            }
        }
        $filedata = serialize($_FILES);
    }
    SESS_setVar('glfusion.auth.method',$method);
    SESS_setVar('glfusion.auth.dest',$destination);
    SESS_setVar('glfusion.auth.post',$postdata);
    SESS_setVar('glfusion.auth.get',$getdata);
    if ( !empty($filedata) ) {
        SESS_setVar('glfusion.auth.file',$filedata);
    }

    $display = COM_siteHeader();
    $display .= SEC_tokenreauthForm('',$destination);
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

/**
  * Check a security token.
  *
  * Checks the POST and GET data for a security token, if one exists, validates that it's for this
  * user and URL.
  *
  * @return boolean     true if the token is valid and for this user.
  */
function _sec_checkToken($ajax=0)
{
    global $_CONF, $_SYSTEM, $_USER, $_TABLES;

    $token = ''; // Default to no token.
    $return = false; // Default to fail.

    $db = Database::getInstance();

    if ( isset($_SYSTEM['token_ip']) && $_SYSTEM['token_ip'] == true ) {
        $referCheck  = $_SERVER['REAL_ADDR'];
    } else {
        $referCheck = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REAL_ADDR'];
    }
    $_SERVER['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REAL_ADDR'];
    /*
     * We cannot use filter_input here because it will pull the orignal
     * $_GET vars passed by the server, not a modified version value that
     * could potentially be updated by auth.inc.php
     */

    if (array_key_exists(CSRF_TOKEN, $_GET)) {
        $token = COM_applyFilter($_GET[CSRF_TOKEN]);
    } else if(array_key_exists(CSRF_TOKEN, $_POST)) {
        $token = COM_applyFilter($_POST[CSRF_TOKEN]);
    }
    if (trim($token) != '') {
        $stmt = $db->conn->executeQuery(
                    "SELECT
                    ((DATE_ADD(created, INTERVAL ttl SECOND) < ?) AND ttl > 0) as expired,
                    owner_id, urlfor FROM `{$_TABLES['tokens']}`
                    WHERE token=?",
                    array(
                        $_CONF['_now']->toMySQL(true),
                        $token,
                    ),
                    array(
                        Database::STRING,
                        Database::STRING
                    )
        );

        $tokenRows = $stmt->fetchAll(Database::ASSOCIATIVE);
        $numberOfTokens = count($tokenRows);
        if ( $numberOfTokens != 1 ) {
            if ( $numberOfTokens == 0 ) {
                Log::write('system',Log::WARNING,"CheckToken: Token failed - no token found in database - " . $referCheck);
            } else {
                Log::write('system',Log::WARNING,"CheckToken: Token failed - more than 1 token found in database");
            }
            $return = false; // none, or multiple tokens. Both are invalid. (token is unique key...)
        } else {
            $tokendata = $tokenRows[0];
            /* Check that:
             *  token's user is the current user.
             *  token is not expired.
             *  the http referer is the url for which the token was created.
             */
            if( $_USER['uid'] != $tokendata['owner_id'] ) {
                Log::write('system',Log::WARNING,"CheckToken: Token failed - userid does not match token owner id");
                $return = false;
            } else if($tokendata['urlfor'] != $referCheck) {
                Log::write('system',Log::WARNING,"CheckToken: Token failed - token URL/IP does not match referer URL/IP.");
                Log::write('system',Log::WARNING,"Expected URL: " . $tokendata['urlfor'] . " - REFERER URL: " . $_SERVER['HTTP_REFERER']);

                if ( function_exists('bb2_ban') ) {
                    bb2_ban($_SERVER['REAL_ADDR'],3);
                }

                $return = false;
            } else if($tokendata['expired'] != 0) {
                Log::write('system',Log::WARNING,"CheckToken: Token failed - token has expired.");
                $return = false;
            } else {
                $return = true; // Everything is AOK in only one condition...
            }
            if ( $ajax == 0 ) {
                // It's a one time token. So eat it.
                $db->conn->delete(
                        $_TABLES['tokens'],
                        array(
                            'token' => $token
                        ),
                        array(
                            Database::STRING
                        )
                );
            }
        }
    } else {
        $return = false; // no token.
    }

    return $return;
}

/**
  * Generate a security token.
  *
  * This generates and stores a security token. These general security tokens
  * can be used in cookies to validate an action is allows.
  *
  * @param $ttl integer Time to live for token in seconds. Default is 20 minutes.
  *
  * @return string  Generated token, it'll be an MD5 hash (32chars)
  */
function SEC_createTokenGeneral($action='general',$ttl = TOKEN_TTL)
{
    global $_CONF, $_USER, $_TABLES;

    if ( !isset($_USER['uid'] ) || $_USER['uid'] == '' ) {
        $_USER['uid'] = 1;
    }

    $db = Database::getInstance();

    /* Generate the token */
    $token = md5($_USER['uid'].$_USER['uid'].uniqid (mt_rand (), 1));

    $db->conn->executeUpdate(
            "DELETE FROM `{$_TABLES['tokens']}`
              WHERE (DATE_ADD(created, INTERVAL ttl SECOND) < ?)
               AND (ttl > 0)",
            array(
                $_CONF['_now']->toMySQL(true)
            ),
            array(
                Database::STRING
            )
    );

    /* Destroy tokens for this user/url combination */
    if ( !defined('DEMO_MODE') ) {
        $db->conn->delete(
            $_TABLES['tokens'],
            array(
                'owner_id' => $_USER['uid'],
                'urlfor' => $action
            ),
            array(
                Database::INTEGER,
                Database::STRING
            )
        );
    }

    try {
        $db->conn->insert(
                $_TABLES['tokens'],
                array(
                    'token' => $token,
                    'created' => $_CONF['_now']->toMySQL(true),
                    'owner_id' => $_USER['uid'],
                    'urlfor' => $action,
                    'ttl' => $ttl
                ),
                array(
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                    Database::STRING,
                    Database::INTEGER
                )
        );
    } catch(Throwable $e) {
        Log::write('system',Log::ERROR,'Error inserting token into DB: ' . $e->getMessage());
    }
    /* And return the token to the user */
    return $token;
}



function SEC_checkTokenGeneral($token,$action='general',$uid=0)
{
    global $_CONF, $_USER, $_TABLES;

    $db = Database::getInstance();

    $return = false; // Default to fail.

    if ( !isset($_USER['uid'])) $_USER['uid'] = 1;

    if ( $uid == 0 ) {
        $uid = $_USER['uid'];
    }

    if(trim($token) != '') {
        $token = COM_applyFilter($token); // filter_var($token,FILTER_SANITIZE_STRING);

        $stmt = $db->conn->executeQuery(
                    "SELECT ((DATE_ADD(created, INTERVAL ttl SECOND) < ?)
                        AND ttl > 0) as expired, owner_id, urlfor
                     FROM `{$_TABLES['tokens']}`
                       WHERE token=?",
                    array(
                        $_CONF['_now']->toMySQL(true),
                        $token
                    ),
                    array(
                        Database::STRING,
                        Database::STRING
                    )
        );
        $tokenRows = $stmt->fetchAll(Database::ASSOCIATIVE);
        $numberOfTokens = count($tokenRows);
        if ( $numberOfTokens != 1 ) {
            if ( $numberOfTokens == 0 ) {
                Log::write('system',Log::INFO,"CheckTokenGeneral: Token failed - no token found in the database (user must re-autheticate) - " . $action . " :: User: " . $_USER['uid']);
            } else {
                Log::write('system',Log::WARNING,"CheckTokenGeneral: Token failed - more than one token found in the database :: User: " . $_USER['uid']);
            }
            $return = false; // none, or multiple tokens. Both are invalid. (token is unique key...)
        } else {
            $tokendata = $tokenRows[0];
            /* Check that:
             *  token's user is the current user.
             *  token is not expired.
             */
            if( $uid != $tokendata['owner_id'] ) {
                Log::write('system',Log::WARNING,"CheckTokenGeneral: Token failed - userid does not match token owner id");
                $return = false;
            } else if($tokendata['expired']) {
                $return = false;
            } else if($tokendata['urlfor'] != $action) {
                Log::write('system',Log::WARNING,"CheckTokenGeneral: Token failed - token action does not match referer action.");
                Log::write('system',Log::WARNING,"Token Action: " . $tokendata['urlfor'] . " - ACTION: " . $action);

                if ( function_exists('bb2_ban') ) {
                    bb2_ban($_SERVER['REAL_ADDR'],3);
                }
                $return = false;
            } else {
                $return = true; // Everything is OK
            }
        }
    } else {
        $return = false; // no token.
    }
    return $return;
}


/**
* Send a cookie
*
* Use this function to set browser cookies
*
* @param string $name   the name of the cookie
* @param string $value  the value of the cookie
* @param int    $expire the time the cookie expires - this is a Unix timestamp
* @param string $path   the path on the server in which the cookie will be available - defaults to $_CONF['cookie_path']
* @param string $domain the domain that the cookie is available - defaults to $_CONF['cookiedomain']
* @param bool   $secure indicates that the cookie shoul only be transmitted over secure HTTPS connection - defaults to $_CONF['cookiesecure']
* @param string $samesite the samesite flag setting. Allows 'Lax', 'Strict' or 'None'
* @param bool   $httponly when true the cookie will be made accessible only through the HTTP protocol
*
*/
function SEC_setCookie($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false, $samesite='Lax')
{
    global $_CONF, $_SYSTEM;

    $retval = false;

    if ( isset($_SYSTEM['nohttponly']) && $_SYSTEM['nohttponly'] == 1 ) {
        $httponly = 0;
    }

    if ($path == '') {
        $path = $_CONF['cookie_path'];
    }

    if ($domain == '') {
        $domain = $_CONF['cookiedomain'];
    }

    if (!in_array($samesite, array('Lax', 'Strict', 'None'))) {
        $samesite = 'Lax';
    }
    if ($samesite == 'None') {
        $secure = true;
    } elseif ($secure == '') {
        $secure = $_CONF['cookiesecure'];
    }

    if (version_compare(PHP_VERSION, '7.3.0') >= 0 ) {
        $options = array(
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure ? true : false,
            'httponly' => $httponly ? true : false,
            'expires' => $expire,
            'samesite' => $samesite,
        );
        $retval = @setcookie($name, $value, $options);
    } else {
        $retval = @setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    return $retval;
}


/**
* Clean up any leftover files on failed re-authentication
*
* When re-authentication fails, we need to clean up any files that may have
* been rescued during the original POST request.
*
* @param    mixed   $files  original or recreated $_FILES array
* @return   void
*
*/
function SEC_cleanupFiles($files)
{
    global $_CONF;

    // first, some sanity checks
    if (! is_array($files)) {
        if (empty($files)) {
            return; // nothing to do
        } else {
            $files = @unserialize($files);
        }
    }
    if (!is_array($files) || empty($files)) {
        return;
    }

    foreach ($files as $key => $value) {
        if (! empty($value['tmp_name'])) {
            $filename = COM_sanitizeFilename(basename($value['tmp_name']), true);
            $orphan = $_CONF['path_data'] .'temp/'. $filename;
            if (file_exists($orphan)) {
                if (! @unlink($orphan)) {
                    Log::write('system',Log::WARNING,"SEC_cleanupFile: Unable to remove file $filename from 'data' directory");
                }
            }
        }
    }
}


/**
*
* Borrowed from the phpBB3 project
*
* Portable PHP password hashing framework.
*
* Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
* the public domain.
*
* There's absolutely no warranty.
*
* The homepage URL for this framework is:
*
*   http://www.openwall.com/phpass/
*
* Please be sure to update the Version line if you edit this file in any way.
* It is suggested that you leave the main version number intact, but indicate
* your project name (after the slash) and add your own revision information.
*
* Please do not change the "private" password hashing method implemented in
* here, thereby making your hashes incompatible.  However, if you must, please
* change the hash type identifier (the "$P$") to something different.
*
* Obviously, since this code is in the public domain, the above are not
* requirements (there can be none), but merely suggestions.
*
*
* Hash the password
*/
function SEC_hash($password)
{
    $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    $random_state = _unique_id();
    $random = '';
    $count = 6;

    if (($fh = @fopen('/dev/urandom', 'rb'))) {
        $random = fread($fh, $count);
        fclose($fh);
    }

    if (strlen($random) < $count) {
        $random = '';

        for ($i = 0; $i < $count; $i += 16) {
            $random_state = md5(_unique_id() . $random_state);
            $random .= pack('H*', md5($random_state));
        }
        $random = substr($random, 0, $count);
    }

    $hash = _hash_crypt_private($password, _hash_gensalt_private($random, $itoa64), $itoa64);

    if (strlen($hash) == 34) {
        return $hash;
    }

    return md5($password);
}

/**
* Check for correct password
*
* @param string $password The password in plain text
* @param string $hash The stored password hash
*
* @return bool Returns true if the password is correct, false if not.
*/
function SEC_check_hash($password, $hash)
{
    $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    if (strlen($hash) == 34) {
        return (_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
    }

    return (md5($password) === $hash) ? true : false;
}

/**
* Generate salt for hash generation
*/
function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6)
{
    if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31) {
        $iteration_count_log2 = 8;
    }

    $output = '$H$';
    $output .= $itoa64[min($iteration_count_log2 + 5, 30)];
    $output .= _hash_encode64($input, 6, $itoa64);

    return $output;
}

/**
* Encode hash
*/
function _hash_encode64($input, $count, &$itoa64)
{
    $output = '';
    $i = 0;

    do {
        $value = ord($input[$i++]);
        $output .= $itoa64[$value & 0x3f];

        if ($i < $count) {
            $value |= ord($input[$i]) << 8;
        }

        $output .= $itoa64[($value >> 6) & 0x3f];

        if ($i++ >= $count) {
            break;
        }

        if ($i < $count) {
            $value |= ord($input[$i]) << 16;
        }

        $output .= $itoa64[($value >> 12) & 0x3f];

        if ($i++ >= $count) {
            break;
        }

        $output .= $itoa64[($value >> 18) & 0x3f];
    } while ($i < $count);

    return $output;
}

/**
* The crypt function/replacement
*/
function _hash_crypt_private($password, $setting, &$itoa64)
{
    $output = '*';

    // Check for correct hash
    if (substr($setting, 0, 3) != '$H$') {
        return $output;
    }

    $count_log2 = strpos($itoa64, $setting[3]);

    if ($count_log2 < 7 || $count_log2 > 30) {
        return $output;
    }

    $count = 1 << $count_log2;
    $salt = substr($setting, 4, 8);

    if (strlen($salt) != 8) {
        return $output;
    }

    /**
    * We're kind of forced to use MD5 here since it's the only
    * cryptographic primitive available in all versions of PHP
    * currently in use.  To implement our own low-level crypto
    * in PHP would result in much worse performance and
    * consequently in lower iteration counts and hashes that are
    * quicker to crack (by non-PHP code).
    */
    $hash = md5($salt . $password, true);
    do {
        $hash = md5($hash . $password, true);
    }
    while (--$count);

    $output = substr($setting, 0, 12);
    $output .= _hash_encode64($hash, 16, $itoa64);

    return $output;
}

/**
* Return unique id
* @param string $extra additional entropy
*/
function _unique_id($extra = 'c')
{
    global $_SYSTEM;
    static $dss_seeded = false;
    $sid = date( 'YmdHis' );
    $sid .= mt_rand( 0, 999 );
    $rand_seed = $sid;
    $val = $rand_seed . microtime();
    $val = md5($val);
    $rand_seed = md5($rand_seed . $val . $extra);
    return substr($val, 4, 16);
}


/**
* Display validation form and ask user to re-validate token
*
* @param    string  $message    Message to display
* @return   string              HTML for the validation form
*
*/
function SEC_tokenreauthform($message = '',$destination = '')
{
    global $_CONF, $_USER, $LANG20, $LANG_ACCESS, $LANG_ADMIN;

    COM_clearSpeedlimit($_CONF['login_speedlimit'], 'tokenexpired');

    $userid = 0;
    if (isset ($_COOKIE[$_CONF['cookie_name']])) {
        $userid = COM_applyFilter($_COOKIE[$_CONF['cookie_name']]); // filter_var($_COOKIE[$_CONF['cookie_name']],FILTER_SANITIZE_STRING);
        if (empty ($userid) || ($userid == 'deleted')) {
            $userid = 0;
        } else {
            $userid = (int) filter_var($userid, FILTER_SANITIZE_NUMBER_INT);
        }
    } elseif ( isset($_POST['token_ref']) ) {
        $userid = COM_applyFilter($_POST['token_ref']); // filter_input(INPUT_POST,'token_ref',FILTER_SANITIZE_STRING);
        if (empty ($userid) || ($userid == 'deleted')) {
            $userid = 0;
        } else {
            $userid = (int) filter_var($userid, FILTER_SANITIZE_NUMBER_INT);
        }
    }

    $is_anonUser = COM_isAnonUser();
    if ( $userid > 1 ) {
        $is_anonUser = 0;
    }

    if ( $is_anonUser || !SEC_isLocalUser($_USER['uid']) ) {
        return _sec_reauthOther($message,$destination);
    }

    $hidden = '';

    $hidden .= '<input type="hidden" name="type" value="user"/>' . LB;
    $hidden .= '<input type="hidden" name="token_revalidate" value="true"/>' . LB;
    $hidden .= '<input type="hidden" name="token_ref" value="'.$userid.'"/>' . LB;

    $options = array(
        'forgotpw_link'   => false,
        'newreg_link'     => false,
        'oauth_login'     => false,
        'plugin_vars'     => false,
        '3rdparty_login'  => false,
        'prefill_user'    => COM_isAnonUser() ? false : true,
        'title'           => $LANG_ACCESS['token_expired'],
        'message'         => $message,
        'footer_message'  => $LANG_ACCESS['token_expired_footer'],
        'button_text'     => $LANG_ADMIN['authenticate'],
        'form_action'     => $destination,
        'hidden_fields'   => $hidden
    );
    return SEC_loginForm($options);
}


/**
* Display CAPTCHA validation form and ask user to re-validate token
*
* @param    string  $message    Message to display
* @return   string              HTML for the validation form
*
*/
function _sec_reauthOther( $message = '',$destination = '' )
{
    global $_CONF, $LANG20, $LANG_ACCESS, $LANG_ADMIN;

    $hidden = '';
    $retval = '';
    $hidden .= '<input type="hidden" name="type" value="other"/>' . LB;
    $hidden .= '<input type="hidden" name="token_revalidate" value="true"/>' . LB;
    $reauthform = new Template($_CONF['path_layout'] . 'users');
    $reauthform->set_file('login', 'reauthform.thtml');
    $reauthform->set_var('form_action', $destination);
    $reauthform->set_var('footer_message',$LANG_ACCESS['token_expired_footer']);
    $reauthform->set_var('start_block_loginagain',COM_startBlock($LANG_ACCESS['token_expired']));
    $reauthform->set_var('lang_message', $message);
    $reauthform->set_var('lang_login', 'Validate');
    $reauthform->set_var('end_block', COM_endBlock());
    PLG_templateSetVars ('token', $reauthform);
    $reauthform->set_var('hidden', $hidden);
    $reauthform->parse('output', 'login');
    $retval .= $reauthform->finish($reauthform->get_var('output'));
    return $retval;
}


/**
* Display login form and ask user to re-authenticate
*
* @param    string  $desturl    URL to return to after authentication
* @param    string  $method     original request method: POST or GET
* @param    string  $postdata   serialized POST data
* @param    string  $getdata    serialized GET data
* @param    string  $filedata   serialized FILE data
* @return   string              HTML for the authentication form
*
*/
function SEC_reauthform($desturl, $message = '',$method = '', $postdata = '', $getdata = '', $filedata = '')
{
    global $LANG20, $LANG_ADMIN;

    $hidden = '';

    if ( $desturl != '' ) {
        $hidden .= '<input type="hidden" name="token_returnurl" value="'.urlencode($desturl).'"/>' . LB;
    }
    $hidden .= '<input type="hidden" name="token_postdata" value="'.urlencode($postdata).'"/>' . LB;
    $hidden .= '<input type="hidden" name="token_getdata" value="'.urlencode($getdata).'"/>' . LB;
    $hidden .= '<input type="hidden" name="token_filedata" value="'.urlencode($filedata).'"/>' . LB;
    $hidden .= '<input type="hidden" name="token_requestmethod" value="'.$method.'"/>' . LB;

    $quotes = array('/"/',"/'/");
    $replacements = array('%22','%27');
    $desturl = preg_replace($quotes,$replacements,$desturl);

    $options = array(
        'forgotpw_link'   => false,
        'newreg_link'     => false,
        'oauth_login'     => false,
        'plugin_vars'     => false,
        'prefill_user'    => COM_isAnonUser() ? false : true,
        'title'           => $LANG20[1],
        'message'         => $message,
        'footer_message'  => $LANG20[6],
        'button_text'     => $LANG_ADMIN['authenticate'],
        'form_action'     => $desturl,
        'hidden_fields'   => $hidden
    );

    return SEC_loginForm($options);
}


/**
* Display a "to access this area you need to be logged in" message
*
* @return   string      HTML for the message
*
*/
function SEC_loginRequiredForm()
{
    global $_CONF, $LANG_LOGIN;

    $options = array(
        'title'   => $LANG_LOGIN[1],
        'message' => $LANG_LOGIN[2]
    );

    return SEC_loginForm($options);
}

/**
* Displays a login form
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
function SEC_loginForm($use_options = array())
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

        // default texts
        'title'             => sprintf($LANG04[65],$_CONF['site_name']), // Login to site
        'message'           => '', // $LANG04[66], // Please enter username
        'footer_message'    => '',
        'button_text'       => $LANG04[80], // Login

        // action
        'form_action' => $_CONF['site_url'].'/users.php',

        // landing page after successful login
        'login_landing' => '',
    );

    $options = array_merge($default_options, $use_options);

    $loginform = new Template(array($_CONF['path_layout'] . 'users', $_CONF['path_layout']));
    $loginform->set_file('login', 'loginform.thtml');

    $loginform->set_var('form_action', $options['form_action']);
    $loginform->set_var('footer_message',$options['footer_message']);

    $loginform->set_var('start_block_loginagain',COM_startBlock($options['title']));
    $loginform->set_var('lang_message', $options['message']);
    if ($options['newreg_link'] == false || $_CONF['disable_new_user_registration']) {
        $loginform->set_var('lang_newreglink', '');
    } else {
        $loginform->set_var('lang_newreglink', sprintf($LANG04[123],$_CONF['site_url']));
    }

    $loginform->set_var('lang_username', $LANG04[2]);
    $loginform->set_var('lang_password', $LANG01[57]);
    if ($options['forgotpw_link']) {
        $loginform->set_var('lang_forgetpassword', $LANG04[25]);
        $forget = COM_createLink($LANG04[25], $_CONF['site_url']
                                              . '/users.php?mode=getpassword',
                                 array('rel' => 'nofollow'));
        $loginform->set_var('forgetpassword_link', $forget);
    } else {
        $loginform->set_var('lang_forgetpassword', '');
        $loginform->set_var('forgetpassword_link', '');
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
* Return available modules for Remote OAuth
*
* @return   array   Names of available remote OAuth modules
*
*/
function SEC_collectRemoteOAuthModules()
{
    global $_CONF;

    $available_modules = array('facebook','google','twitter','microsoft','linkedin','github');

    $modules = array();

    if (extension_loaded('openssl')) {
        foreach ($available_modules as $mod) {
            if (isset($_CONF[$mod . '_login'])) {
                if ($_CONF[$mod . '_login']) {
                    // Now check if a Consumer Key and Secret exist and are set
                    if (isset($_CONF[$mod . '_consumer_key'])) {
                        if ($_CONF[$mod . '_consumer_key'] != '') {
                            if (isset($_CONF[$mod . '_consumer_secret'])) {
                                if ($_CONF[$mod . '_consumer_secret'] != '') {
                                    $modules[] = $mod;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $modules;
}

function SEC_validate2FACode($code)
{
    global $_USER;
    $tfa = \TwoFactor::getInstance($_USER['uid']);
    return $tfa->validateCode($code);
}

/**
* Displays the Two Factor Auth token entry form
*
* @return   no return
*
*/
function SEC_2FAForm($uid)
{
    global $_CONF, $_USER, $LANG_TFA;

    $retval = '';

    $T = new Template($_CONF['path_layout'] . 'users');
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
* Checks password complexity to ensure it meets the configured rules
*
* @return   array of errors or empty array if no errors
*
*/
function SEC_checkPwdComplexity($pwd)
{
    global $_CONF, $LANG_PWD;

    $errors = array();

    if ((!isset($_CONF['pwd_min_length'])  || $_CONF['pwd_min_length'] == 0) &&
        (!isset($_CONF['pwd_max_length'])  || $_CONF['pwd_max_length'] == 0 ) &&
        (!isset($_CONF['pwd_req_num'])     || $_CONF['pwd_req_num']    == 0 ) &&
        (!isset($_CONF['pwd_req_letter'])  || $_CONF['pwd_req_letter'] == 0 ) &&
        (!isset($_CONF['pwd_req_cap'])     || $_CONF['pwd_req_cap']    == 0 ) &&
        (!isset($_CONF['pwd_req_lower'])   || $_CONF['pwd_req_lower']  == 0 ) &&
        (!isset($_CONF['pwd_req_symbol'])  || $_CONF['pwd_req_symbol'] == 0 )
        ) {
        return array();
    }

    if ( isset($_CONF['pwd_min_length']) && $_CONF['pwd_min_length'] > 0 ) {
        if ( strlen($pwd) < $_CONF['pwd_min_length'] ) {
           $errors[] = $LANG_PWD['error_too_short'];
        }
    }

    if ( isset($_CONF['pwd_max_length']) && $_CONF['pwd_max_length'] > 0 ) {
        if ( strlen($pwd) > $_CONF['pwd_max_length'] ) {
            $errors[] = $LANG_PWD['error_too_long'];
        }
    }

    if ( isset($_CONF['pwd_req_num']) && $_CONF['pwd_req_num'] == 1 ) {
        if ( !preg_match("#[0-9]+#", $pwd) ) {
            $errors[] = $LANG_PWD['error_no_number'];
        }
    }

    if ( isset($_CONF['pwd_req_letter']) && $_CONF['pwd_req_letter'] == 1) {
        if ( !preg_match("/[A-za-z]+/", $pwd) ) {
            $errors[] = $LANG_PWD['error_no_letter'];
        }
    }

    if ( isset($_CONF['pwd_req_cap']) && $_CONF['pwd_req_cap'] == 1) {
        if ( !preg_match('/[A-Z]/', $pwd) ) {
            $errors[] = $LANG_PWD['error_no_cap'];
        }
    }

    if ( isset($_CONF['pwd_req_lower']) && $_CONF['pwd_req_lower'] == 1) {
        if ( !preg_match('/[a-z]/', $pwd) ) {
            $errors[] = $LANG_PWD['error_no_lower'];
        }
    }

    if ( isset($_CONF['pwd_req_symbol']) && $_CONF['pwd_req_symbol'] == 1 ) {
        if ( !preg_match("#\W+#", $pwd) ) {
            $errors[] = $LANG_PWD['error_no_symbol'];
        }
    }

    return $errors;
}

/**
* Builds the password requirements help widget
*
* @return   HTML
*
*/
function SEC_showPasswordHelp()
{
    global $_CONF, $LANG_PWD;

    $retval = '';

    if ((!isset($_CONF['pwd_min_length'])  || $_CONF['pwd_min_length'] == 0) &&
        (!isset($_CONF['pwd_max_length'])  || $_CONF['pwd_max_length'] == 0 ) &&
        (!isset($_CONF['pwd_req_num'])     || $_CONF['pwd_req_num']    == 0 ) &&
        (!isset($_CONF['pwd_req_letter'])  || $_CONF['pwd_req_letter'] == 0 ) &&
        (!isset($_CONF['pwd_req_cap'])     || $_CONF['pwd_req_cap']    == 0 ) &&
        (!isset($_CONF['pwd_req_lower'])   || $_CONF['pwd_req_lower']  == 0 ) &&
        (!isset($_CONF['pwd_req_symbol'])  || $_CONF['pwd_req_symbol'] == 0 )
        ) {
        return '';
    }

    $retval .= '<strong>'.$LANG_PWD['title'].'</strong>';
    $retval .= '<br>';

    if ( $_CONF['pwd_min_length'] > 0 ) {
        $retval .= sprintf('<li>'.$LANG_PWD['min_length'].'</li>',$_CONF['pwd_min_length']);
    }
    if ( $_CONF['pwd_max_length'] > 0 ) {
        $retval .= sprintf('<li>'.$LANG_PWD['max_length'].'</li>',$_CONF['pwd_max_length']);
    }
    if ( $_CONF['pwd_req_num'] > 0 ) {
        $retval .= '<li>'.$LANG_PWD['req_num'].'</li>';
    }
    if ( $_CONF['pwd_req_letter'] > 0 ) {
        $retval .= '<li>'.$LANG_PWD['req_letter'].'</li>';
    }
    if ( $_CONF['pwd_req_cap'] > 0 ) {
        $retval .= '<li>'.$LANG_PWD['req_cap'].'</li>';
    }
    if ( $_CONF['pwd_req_lower'] > 0 ) {
        $retval .= '<li>'.$LANG_PWD['req_lower'].'</li>';
    }
    if ( $_CONF['pwd_req_symbol'] > 0 ) {
        $retval .= '<li>'.$LANG_PWD['req_symbol'].'</li>';
    }
    $retval .'</ul>';
    return $retval;
}

/**
* Generate random password
*
* @return   string
*
*/
function SEC_generateStrongPassword($length = 9, $available_sets = 'luds')
{
	$sets = array();
	if(strpos($available_sets, 'l') !== false)
		$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	if(strpos($available_sets, 'u') !== false)
		$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	if(strpos($available_sets, 'd') !== false)
		$sets[] = '23456789';
	if(strpos($available_sets, 's') !== false)
		$sets[] = '!@#$%&*?';
	$all = '';
	$password = '';
	foreach($sets as $set) {
		$password .= $set[array_rand(str_split($set))];
		$all .= $set;
	}
	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++) {
		$password .= $all[array_rand($all)];
	}
	$password = str_shuffle($password);

    return $password;
}

?>
