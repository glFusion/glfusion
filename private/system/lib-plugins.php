<?php
/**
* glFusion CMS
*
* glFusion Plugin Interface Library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2009 by the following authors:
*   Authors: Tony Bibbs       - tony AT tonybibbs DOT com
*            Blaine Lang      - blaine AT portalparts DOT com
*            Dirk Haun        - dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use glFusion\Database\Database;
use glFusion\Cache\Cache;
use glFusion\Log\Log;

global $autoTagUsage;

/**
* Response codes for the service invocation PLG_invokeService(). Note that
* these are intentionally vague so as not to give away too much information.
*/
define('PLG_RET_OK',                   0);  // success
define('PLG_RET_ERROR',               -1);  // generic error
define('PLG_RET_PERMISSION_DENIED',   -2);  // access to item or object denied
define('PLG_RET_AUTH_FAILED',         -3);  // authentication failed
define('PLG_RET_PRECONDITION_FAILED', -4);  // a precondition was not met

/**
 * Centerblock locations
 */
define('CENTERBLOCK_FULLPAGE',          0);
define('CENTERBLOCK_TOP',               1);
define('CENTERBLOCK_AFTER_FEATURED',    2);
define('CENTERBLOCK_BOTTOM',            3);
define('CENTERBLOCK_NONEWS',            4);
define('CENTERBLOCK_FORCE',             5);

// buffer for function names for the center block API
$PLG_bufferCenterAPI = array ();
$PLG_buffered = false;

/**
* Calls a function for all enabled plugins
*
* @param    string  $function_name  holds name of function to call
* @return   void
* @internal not to be used by plugins
* @todo     only supports functions without any parameters
*
*/
function PLG_callFunctionForAllPlugins($function_name, $args='')
{
    global $_PLUGINS;

    $retval = array();
    if (empty ($args)) {
        $args = array ();
    }

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_' . $function_name . '_' . $pi_name;
        if (function_exists($function)) {
            // Just pass $args through to the target function if it is:
            // a) not an array of args, just a single string, number, etc.
            // b) a normal zero-biased indexed array
            // c) does not contain an index of "1", e.g. an associative array
            if (!is_array($args) || isset($args[0]) || !isset($args[1])) {
                return $function($args);
            }

            switch (count($args)) {
            case 0:
                $retval[$pi_name] = $function();
                break;
            case 1:
                $retval[$pi_name] = $function($args[1]);
                break;
            case 2:
                $retval[$pi_name] = $function($args[1], $args[2]);
                break;
            case 3:
                $retval[$pi_name] = $function($args[1], $args[2], $args[3]);
                break;
            case 4:
                $retval[$pi_name] = $function($args[1], $args[2], $args[3], $args[4]);
                break;
            case 5:
                $retval[$pi_name] = $function($args[1], $args[2], $args[3], $args[4], $args[5]);
                break;
            case 6:
                $retval[$pi_name] = $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
                break;
            case 7:
                $retval[$pi_name] = $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
                break;
            default:
                $retval[$pi_name] = $function($args);
                break;
            }
        }
    }
    $function = 'CUSTOM_' . $function_name;
    if (function_exists($function)) {
        $retval['_custom'] = $function();
    }
    return $retval;
}

/**
* Calls a function for a single plugin
*
* This is a generic function used by some of the other API functions to
* call a function for a specific plugin and, optionally pass parameters.
* This function can handle up to 5 arguments and if more exist it will
* try to pass the entire args array to the function.
*
* @param        string      $function       holds name of function to call
* @param        array       $args           arguments to send to function
* @return       mixed       returns result of function call, otherwise false
* @internal not to be used by plugins
*
*/
function PLG_callFunctionForOnePlugin($function, $args='')
{
    if (function_exists($function)) {
        if (empty ($args)) {
            $args = array ();
        }

        // Just pass $args through to the target function if it is:
        // a) not an array of args, just a single string, number, etc.
        // b) a normal zero-biased indexed array
        // c) does not contain an index of "1", e.g. an associative array
        if (!is_array($args) || isset($args[0]) || !isset($args[1])) {
            return $function($args);
        }

        // great, function exists, run it
        switch (count($args)) {
        case 0:
            return $function();
            break;
        case 1:
            return $function($args[1]);
            break;
        case 2:
            return $function($args[1], $args[2]);
            break;
        case 3:
            return $function($args[1], $args[2], $args[3]);
            break;
        case 4:
            return $function($args[1], $args[2], $args[3], $args[4]);
            break;
        case 5:
            return $function($args[1], $args[2], $args[3], $args[4], $args[5]);
            break;
        case 6:
            return $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
            break;
        case 7:
            return $function($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
            break;
        default:
            return $function($args);
            break;
        }
    } else {
        return false;
    }
}

/**
* Tells a plugin to install itself. NOTE: not currently used anymore
*
* @param        string      $type       Plugin name
* @return       boolean     Returns true on success otherwise false
*
*/
function PLG_install($type)
{
    $c = Cache::getInstance();
    $c->deleteItemsByTag('atperm');
    $c->deleteItemsByTag('plugins');
    $c->deleteItemsByTag('plugin_info');

    return PLG_callFunctionForOnePlugin('plugin_install_' . $type);
}

/**
* Upgrades a plugin. Tells a plugin to upgrade itself.
*
* @param    string  $type   Plugin name
* @return   mixed           true on success, false or error number on failure
*
*/
function PLG_upgrade($type)
{
    $c = Cache::getInstance();
    $c->deleteItemsByTag('atperm');
    $c->deleteItemsByTag('plugins');
    $c->deleteItemsByTag('plugin_info');

    return PLG_callFunctionForOnePlugin('plugin_upgrade_' . $type);
}

/**
* Calls the plugin function to return the current version of code.
* Used to indicate to admin if an update or upgrade is requied.
*
* @param        string      $type       Plugin name
* @return       boolean     Returns true on success otherwise false
*
*/
function PLG_chkVersion($type)
{
    return PLG_callFunctionForOnePlugin('plugin_chkVersion_' . $type);
}

/**
* Tells a plugin to uninstall itself.
*
* @param        string      $type       Plugin to uninstall
* @return       boolean     Returns true on success otherwise false
*
*/
function PLG_uninstall ($type)
{
    global $_CONF, $_PLUGINS, $_TABLES, $LANG_ADM_ACTIONS;

    if (empty ($type)) {
        return false;
    }

    $db = Database::getInstance();

    $c = Cache::getInstance();
    $c->deleteItemsByTag('atperm');
    $c->deleteItemsByTag('plugins');
    $c->deleteItemsByTag('plugin_info');

    if (function_exists('plugin_autouninstall_' . $type)) {
        Log::write('system',Log::INFO, "Auto-uninstalling plugin $type:");
        $function = 'plugin_autouninstall_' . $type;
        $remvars = $function();

        if (empty ($remvars) || $remvars == false) {
            return false;
        }

        // removing tables
        for ($i=0; $i < count($remvars['tables']); $i++) {
            Log::write('system',Log::INFO,"Dropping table {$_TABLES[$remvars['tables'][$i]]}");
            try {
                $db->conn->query("DROP TABLE `{$_TABLES[$remvars['tables'][$i]]}`");
            } catch(Throwable $e) {
                Log::write('system',Log::ERROR,"ERROR: Dropping of Table: {$_TABLES[$remvars['tables'][$i]]} failed.");
            }
            Log::write('system',Log::INFO,'...completed');
        }

        // removing variables
        for ($i = 0; $i < count($remvars['vars']); $i++) {
            Log::write('system',Log::INFO,"Removing variable {$remvars['vars'][$i]}");
            try {
                $db->conn->delete($_TABLES['vars'], array('name' => $remvars['vars'][$i]));
            } catch(Throwable $e) {
                Log::write('system',Log::ERROR,"ERROR: Removing variable {$remvars['vars'][$i]} from VARS table failed");
            }
            Log::write('system',Log::INFO,'...completed');
        }

        // removing groups
        for ($i = 0; $i < count($remvars['groups']); $i++) {
            $grp_id = $db->getItem ($_TABLES['groups'], 'grp_id',array('grp_name' => $remvars['groups'][$i]),array(Database::INTEGER));
            if (!empty ($grp_id) && $grp_id !== false) {
                Log::write('system',Log::INFO,"Attempting to remove the {$remvars['groups'][$i]} group");
                try {
                    $db->conn->delete($_TABLES['groups'], array('grp_id' => $grp_id));
                } catch(Throwable $e) {
                    Log::write('system',Log::ERROR,"ERROR: Removing group {$remvars['groups'][$i]} from GROUPS table failed");
                }
                Log::write('system',Log::INFO,'...completed');
                Log::write('system',Log::INFO,"Attempting to remove the {$remvars['groups'][$i]} group from all groups.");
                try {
                    $db->conn->delete($_TABLES['group_assignments'], array('ug_main_grp_id' => $grp_id));
                } catch(Throwable $e) {
                    Log::write('system',Log::ERROR,"ERROR: Removing group {$remvars['groups'][$i]} from GROUP ASSIGNMENTS table failed");
                }
                Log::write('system',Log::INFO,'...completed');
            }
        }

        // removing features
        for ($i = 0; $i < count($remvars['features']); $i++) {
            $access_id = $db->getItem ($_TABLES['features'], 'ft_id',
                                    array('ft_name' => $remvars['features'][$i]),array(Database::INTEGER));
            if (!empty ($access_id)) {
                Log::write('system',Log::INFO,"Attempting to remove {$remvars['features'][$i]} rights from all groups");
                $db->conn->delete(
                        $_TABLES['access'],
                        array('acc_ft_id' => $access_id),
                        array(Database::INTEGER)
                );
                Log::write('system',Log::INFO,'...success');
                Log::write('system',Log::INFO,"Attempting to remove the {$remvars['features'][$i]} feature");

                $db->conn->delete(
                        $_TABLES['features'],
                        array('ft_name' => $remvars['features'][$i]),
                        array(Database::STRING)
                );
                Log::write('system',Log::INFO,'...success');
            }
        }

        // uninstall feeds
        $Feeds = glFusion\Syndication\Feed::getAll($type);
        if (!empty($Feeds)) {
            Log::write('system',Log::INFO,'removing feed files');
            foreach ($Feeds as $Feed) {
                $Feed->delete();
            }
        }

        // remove comments for this plugin
        Log::write('system',Log::INFO,"Attempting to remove comments for $type");
        $db->conn->delete(
            $_TABLES['comments'],
            array('type' => $type),
            array(Database::STRING)
        );
        Log::write('system',Log::INFO,'...success');

        // uninstall php-blocks
        for ($i=0; $i <  count($remvars['php_blocks']); $i++) {
            $db->conn->delete(
                $_TABLES['blocks'],
                array(
                    'type' => 'phpblock',
                    'phpblockfn' => $remvars['php_blocks'][$i]
                ),
                array(
                    Database::STRING,
                    Database::STRING
                )
            );
        }

        // remove autotag permissions
        $db->conn->executeUpdate(
                "DELETE `{$_TABLES['autotag_perm']}`.*, `{$_TABLES['autotag_usage']}`.*
                  FROM `{$_TABLES['autotag_perm']}` JOIN `{$_TABLES['autotag_usage']}`
                    ON {$_TABLES['autotag_perm']}.autotag_id={$_TABLES['autotag_usage']}.autotag_id
                    WHERE {$_TABLES['autotag_perm']}.autotag_namespace=?",
                array($type),
                array(Database::STRING)
        );
        $db->conn->delete(
            $_TABLES['autotag_usage'],
            array('usage_namespace' => $type),
            array(Database::STRING)
        );

        // remove config table data for this plugin

        Log::write('system',Log::INFO,"Attempting to remove config table records for group_name: $type");

        $c = config::get_instance();
        if ($c->group_exists($type)) {
            $c->delGroup($type);
        }
        Log::write('system',Log::INFO,'...success');

        // remove any rating data for the plugin

        Log::write('system',Log::INFO,"Attempting to remove rating table records for type: $type");

        $db->conn->delete(
            $_TABLES['rating'],
            array('type' => $type),
            array(Database::STRING)
        );
        $db->conn->delete(
            $_TABLES['rating_votes'],
            array('type' => $type),
            array(Database::STRING)
        );

        Log::write('system',Log::INFO,'...success');

        // tell other plugins we are removing all content
        PLG_itemDeleted('*', $type);

        // uninstall the plugin
        Log::write('system',Log::INFO,"Attempting to unregister the $type plugin from glFusion");
        $db->conn->delete(
            $_TABLES['plugins'],
            array('pi_name' => $type),
            array(Database::STRING)
        );
        Log::write('system',Log::INFO,'...success');

        Log::write('system',Log::INFO,"Finished uninstalling the $type plugin.");

        \glFusion\Admin\AdminAction::write('system','plugin_uninstall',sprintf($LANG_ADM_ACTIONS['plugin_uninstall'],$type));

        return true;
    } elseif (function_exists('plugin_uninstall_'.$type)) {
        $retval = PLG_callFunctionForOnePlugin ('plugin_uninstall_' . $type);

        if ($retval === true) {
            $plg = array_search ($type, $_PLUGINS);
            if ($plg !== false) {
                unset ($_PLUGINS[$plg]);
            }
            \glFusion\Admin\AdminAction::write('system','plugin_uninstall',sprintf($LANG_ADM_ACTIONS['plugin_uninstall'],$type));
            return true;
        }
    } else {
        // we got nothing - so let's just remove it from the plugin table
        Log::write('system',Log::WARNING,"WARNING: Unable to locate plugin's source files. Plugin removed from Plugins Table. Plugin's database tables, group, feature and other data may still be installed.");
        PLG_itemDeleted('*', $type);
        $db->conn->delete(
            $_TABLES['plugins'],
            array('pi_name' => $type),
            array(Database::STRING)
        );
        \glFusion\Admin\AdminAction::write('system','plugin_uninstall',sprintf($LANG_ADM_ACTIONS['plugin_uninstall'],$type));
        return true;
    }

    return false;
}

/**
* Inform plugin that it is either being enabled or disabled.
*
* @param    string      $type       Plugin name
* @param    boolean     $enable     true if enabling, false if disabling
* @return   boolean     Returns true on success otherwise false
* @see      PLG_pluginStateChange
*
*/
function PLG_enableStateChange ($type, $enable)
{
   global $_CONF, $_SYSTEM, $_TABLES, $_PLUGIN_INFO, $_USER, $LANG_ADM_ACTIONS;

    $args[1] = $enable;

    $logState = $enable == 1 ? $LANG_ADM_ACTIONS['enabled'] : $LANG_ADM_ACTIONS['disabled'];

    \glFusion\Admin\AdminAction::write('system','plugin_state',sprintf($LANG_ADM_ACTIONS['plugin_state'],$type,$logState));

    // IF we are enabling the plugin
    // THEN we must include its functions.inc so we have access to the function
    if ($enable) {
        if (@file_exists($_CONF['path'] . 'plugins/' . $type . '/functions.inc') ) {
            require_once $_CONF['path'] . 'plugins/' . $type . '/functions.inc';
            return PLG_callFunctionForOnePlugin ('plugin_enablestatechange_' . $type,$args);
        }
    } else {
        return PLG_callFunctionForOnePlugin ('plugin_enablestatechange_' . $type,$args);
    }
}

/**
* Checks to see if user is a plugin moderator
*
* glFusion is asking if the user is a moderator for any installed plugins.
*
* @return   boolean     True if current user is moderator of plugin otherwise false
*
*/
function PLG_isModerator()
{
    global $_PLUGINS;

    $pluginTypes = array_merge(array('article'), $_PLUGINS);

    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_ismoderator_' . $pi_name;
        if (function_exists($function)) {
            if ( $function() == true ) {
                return true;
            }
        }
    }
    $function = 'CUSTOM_ismoderator';
    if (function_exists($function)) {
        if ( $function() == true ) {
            return true;
        }
    }
    return false;
}

/**
* Gives plugins a chance to print their menu items in header
*
* Note that this is fairly unflexible.  This simply loops through the plugins
* in the database in the order they were installed and get their menu items.
* If you want more flexibility in your menu then you should hard code the menu
* items in header.thtml for the theme(s) you are using.
*
* @return   array   Returns menu options for plugin
*
*/
function PLG_getMenuItems()
{
    global $_PLUGINS;

    $menu = array();
    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getmenuitems_' . $pi_name;
        if (function_exists($function)) {
            $menuitems = $function();
            if (is_array ($menuitems)) {
                $menu = array_merge ($menu, $menuitems);
            }
        }
    }

    return $menu;
}

/**
 * Get view URL and name of unique identifier
 *
 * @author Vincent Furia, vinny01 AT users DOT sourceforge DOT net
 * @param   string  $type   Plugin to delete comment
 * @return  array   string of URL of view page, name of unique identifier
 */
function PLG_getCommentUrlId($type)
{
    global $_CONF;

    $ret = PLG_callFunctionForOnePlugin('plugin_getcommenturlid_' . $type);
    if (empty($ret[0])) {
        $ret[0] = $_CONF['site_url'] . "/$type/index.php";
    }
    if (empty($ret[1])) {
        $ret[1] = 'id';
    }
    if (empty($ret[2])) {
        $ret[2] = 'page=';
    }

    return $ret;
}

/**
 * Plugin should delete a comment
 *
 * @author Vincent Furia, vinny01 AT users DOT sourceforge DOT net
 * @param   string  $type   Plugin to delete comment
 * @param   int     $cid    Comment to be deleted
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function PLG_commentDelete($type, $cid, $id)
{
    $args[1] = $cid;
    $args[2] = $id;

    return PLG_callFunctionForOnePlugin('plugin_deletecomment_' . $type, $args);
}

/**
 * Plugin edits comment
 *
 * @author Mark R. Evans <mark AT glfusion DOT org>
 * @param   string  $type   Plugin to edit comment
 * @param   int     $cid    Comment to be edited
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function PLG_commentEditSave($type, $cid, $id)
{
    $args[1] = $cid;
    $args[2] = $id;

    return PLG_callFunctionForOnePlugin('plugin_editcomment_' . $type, $args);
}

/**
 * Plugin should save a comment
 *
 * @author Vincent Furia, vinny01 AT users DOT sourceforge DOT net
 * @param   string  $type   Plugin to delete comment
 * @param   string  $title  comment title
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function PLG_commentSave($type, $title, $comment, $id, $pid, $postmode)
{
    $args[1] = $title;
    $args[2] = $comment;
    $args[3] = $id;
    $args[4] = $pid;
    $args[5] = $postmode;

    return PLG_callFunctionForOnePlugin('plugin_savecomment_' . $type, $args);
}

/**
 * Plugin should display [a] comment[s]
 *
 * @author Vincent Furia, vinny01 AT users DOT sourceforge DOT net
 * @param   string  $type   Plugin to display comment
 * @param   string  $id     Unique idenifier for item comment belongs to
 * @param   int     $cid    Comment id to display (possibly including sub-comments)
 * @param   string  $title  Page/comment title
 * @param   string  $order  'ASC' or 'DSC' or blank
 * @param   string  $format 'threaded', 'nested', or 'flat'
 * @param   int     $page   Page number of comments to display
 * @param   boolean $view   True to view comment (by cid), false to display (by $pid)
 * @return  mixed   results of calling the plugin_displaycomment_ function
 */
function PLG_displayComment($type, $id, $cid, $title, $order, $format, $page, $view)
{
    $args[1] = $id;
    $args[2] = $cid;
    $args[3] = $title;
    $args[4] = $order;
    $args[5] = $format;
    $args[6] = $page;
    $args[7] = $view;

    return PLG_callFunctionForOnePlugin('plugin_displaycomment_' . $type, $args);
}

/**
* Allows plugins a chance to handle a comment before GL does.

* This is a first-come-first-serve affair so if a plugin returns an error, other
* plugins wishing to handle comment preprocessing won't get called
*
* @author Tony Bibbs, tony AT tonybibbs DOT com
* @access public
* @param  int       $uid User ID
* @param  string    $title Comment title
* @param  string    $sid Story ID (not always a story, remember!)
* @param  int       $pid Parent comment ID
* @param  string    $type Type of comment
* @param  string    $postmode HTML or text
* @return mixed     an error otherwise false if no errors were encountered
*
*/
function PLG_commentPreSave($uid, &$title, &$comment, $sid, $pid, $type, &$postmode)
{
	global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_commentPreSave_' . $pi_name;
        if (function_exists($function)) {
            $someError = $function($uid, $title, $comment, $sid, $pid, $type, $postmode);
            if ($someError) {
            	// Plugin doesn't want to save the comment
            	return $someError;
            }
        }
    }

    $function = 'CUSTOM_commentPreSave';
    if (function_exists($function)) {
        $someError = $function($uid, $title, $comment, $sid, $pid, $type, $postmode);
        if ($someError) {
            // Custom function refused save:
            return $someError;
        }
    }

    return false;
}

/**
* Allows plugins a chance to handle an item before GL does. Modeled
* after the PLG_commentPreSave() function.
*
* This is a first-come-first-serve affair so if a plugin returns an error, other
* plugins wishing to handle comment preprocessing won't get called
*
* @author Mark Evans, mevans AT ecsnet DOT com
* @access public
* @param string $type Type of item, i.e.; registration, contact ...
* @param string $content item specific content
* @return string empty is no error, error message if error was encountered
*
*/
function PLG_itemPreSave($type, &$content = '')
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_itemPreSave_' . $pi_name;
        if (function_exists ($function)) {
            $msgError = $function ($type, $content);
            if (!empty ($msgError)) {
                // Plugin doesn't want to save the item
                return $msgError;
            }
        }
    }

    $function = 'CUSTOM_itemPreSave';
    if (function_exists ($function)) {
        $msgError = $function ($type, $content);
        if (!empty ($msgError)) {
            // Custom doesn't want to save the item
            return $msgError;
        }
    }

    return '';
}


/**
* Allows a plugin to handle a comment approval
*
* This will only call the plugin owning the comment.
*
* @author Mark Evans, mevans AT ecsnet DOT com
* @access public
* @param string $type Type of item, i.e.; registration, contact ...
* @param string $cid  The comment ID
* @param string $sid  The ID owning the comment
* @return none
*
*/
function PLG_commentApproved( $cid, $type, $sid )
{
    global $_PLUGINS, $_TABLES;

    $pluginTypes = array_merge(array('article'), $_PLUGINS);

    if ( in_array($type,$pluginTypes) ) {
        $function = 'plugin_commentapproved_' . $type;
        if (function_exists ($function)) {
            $function ($cid,$type,$sid);
        }
    }
}

/**
* Allow a plugin to place entries into the glFusion stats page.
*
* $showsitestats == 2 - Return data for the stats summary
* $showsitestats == 3 - Return data for the stats detail


* The only parameter to this function, $showsitestats, was documented as being
* 1 for the site stats and 0 for the plugin-specific stats. However, the latter
* was always called with a value of 2, so plugins only did a check for 1 and
* "else", which makes extensions somewhat tricky.
* Furthermore, due to the original templates for the site stats, it has
* become standard practice to hard-code a <table> in the plugins as the return
* value for $showsitestats == 1. This table, however, didn't align properly
* with the built-in site stats entries.
*
* Because of all this, the new mode, 3, works differently:
* - for $showsitestats == 3, we call a new plugin API function,
*   plugin_statssummary_<plugin-name>, which is supposed to return the plugin's
*   entry for the site stats in an array which stats.php will then properly
*   format, alongside the entries for the built-in items.
* - for $showsitestats == 1, we only call those plugins that do NOT have a
*   plugin_statssummary_<plugin-name> function, thus providing backward
*   compatibility
* - for $showsitestats == 2, nothing has changed
*
* @param    int     $showsitestats      value indicating type of stats to return
* @return   mixed                       array (for mode 3) or string
*
*/
function PLG_getPluginStats ($showsitestats)
{
    global $_PLUGINS;

    if ($showsitestats == 3) {
        $retval = array ();
    } else {
        $retval = '';
    }

    foreach ($_PLUGINS as $pi_name) {
        if ($showsitestats == 3) {
            $function = 'plugin_statssummary_' . $pi_name;
            if (function_exists ($function)) {
                $summary = $function ();
                if (is_array ($summary)) {
                    $retval[$pi_name] = $summary;
                }
            }
        } else if ($showsitestats == 2) {
            $function = 'plugin_showstats_' . $pi_name;
            if (function_exists ($function)) {
                $retval .= $function ($showsitestats);
            }
        }
    }

    if ($showsitestats == 3) {
        $function = 'CUSTOM_statssummary';
        if (function_exists ($function)) {
            $summary = $function ();
            if (is_array ($summary)) {
                $retval['Custom'] = $summary;
            }
        }
    } elseif ($showsitestats == 2) {
        $function = 'CUSTOM_showstats';
        if (function_exists ($function)) {
            $retval .= $function ($showsitestats);
        }
    }

    return $retval;
}

/**
* This function gives each plugin the opportunity to put a value(s) in
* the 'Type' drop down box on the search.php page so that their plugin
* can be incorporated into searches.
*
* @return   array   String array of search types for plugin(s)
*
*/
function PLG_getSearchTypes()
{
    global $_PLUGINS;

    $types = array();
    $cur_types = array();

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_searchtypes_' . $pi_name;
        if (function_exists ($function)) {
            $cur_types = $function ();
            if (is_array ($cur_types) && (count ($cur_types) > 0)) {
                $types = array_merge ($types, $cur_types);
            }
        } // no else because this is not a required API function
    }

    $function = 'CUSTOM_searchtypes';
    if (function_exists ($function)) {
        $cur_types = $function ();
        if (is_array ($cur_types) && (count ($cur_types) > 0)) {
            $types = array_merge ($types, $cur_types);
        }
    }

    asort($types);
    return $types;
}

/**
* This function gives each plugin the opportunity to do their search
* and return their results.  Results comeback in an array of HTML
* formatted table rows that can be quickly printed by search.php
*
* @param    string  $query      What the user searched for
* @param    date    $datestart  beginning of date range to search for
* @param    date    $dateend    ending date range to search for
* @param    string  $topic      the topic the user searched within
* @param    string  $type       Type of items they are searching, or 'all'
* @param    int     $author     UID...only return results for this person
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
* @return   array               Returns search results
*
*/
function PLG_doSearch($query, $datestart, $dateend, $topic, $type, $author, $keyType = 'all', $page = 1, $perpage = 10)
{
    global $_PLUGINS;
    /*
        The new API does not use $page, $perpage
        $type is now only used in the core and should not be passed to the plugin
    */

    $search_results = array();

    // Search a single plugin if needed
    if ($type != 'all' )
    {
        $function = 'plugin_dopluginsearch_' . $type;
        if (function_exists($function))
        {
            $result = $function($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage);
            if (is_array($result))
                $search_results = array_merge($search_results, $result);
            else
                $search_results[] = $result;
        }

        return $search_results;
    }

    foreach ($_PLUGINS as $pi_name)
    {
        $function = 'plugin_dopluginsearch_' . $pi_name;
        if (function_exists($function))
        {
            $result = $function($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage);
            if (is_array($result))
                $search_results = array_merge($search_results, $result);
            else
                $search_results[] = $result;
        }
        // no else because implementation of this API function not required
    }

    $function = 'CUSTOM_dopluginsearch';
    if (function_exists($function))
        $search_results[] = $function($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage);

    return $search_results;
}

/**
* This function gives each plugin the opportunity to do their search
* and return their results.  Results comeback in an array of HTML
* formatted table rows that can be quickly printed by search.php
*
* @param    string  $query      What the user searched for
* @param    date    $datestart  beginning of date range to search for
* @param    date    $dateend    ending date range to search for
* @param    string  $topic      the topic the user searched within
* @param    string  $type       Type of items they are searching, or 'all'
* @param    int     $author     UID...only return results for this person
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
* @return   array               Returns search results
*
*/
function PLG_doSearchComment($query, $datestart, $dateend, $topic, $type, $author, $keyType = 'all', $page = 1, $perpage = 10)
{
    global $_PLUGINS, $_CONF;
    /*
        The new API does not use $page, $perpage
        $type is now only used in the core and should not be passed to the plugin
    */

    if ( isset($_CONF['comment_engine']) && $_CONF['comment_engine'] != 'internal' ) {

        return array();
    }


    $search_results = array();

    if ( $type == 'all' || $type == 'comments' )
    {
        foreach ($_PLUGINS as $pi_name)
        {
            $function = 'plugin_dopluginsearch_comment_' . $pi_name;
            if (function_exists($function))
            {
                $result = $function($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage);
                if (is_array($result))
                    $search_results = array_merge($search_results, $result);
                else
                    $search_results[] = $result;
            }
            // no else because implementation of this API function not required
        }
    }

    return $search_results;
}

/**
* Asks each plugin to report any submissions they may have in their
* submission queue
*
* @return   int     Number of submissions in queue for plugins
*
*/
function PLG_getSubmissionCount()
{
    global $_PLUGINS;

    $num = 0;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_submissioncount_' . $pi_name;
        if (function_exists($function)) {
            $num = $num + $function();
        }
    }
    if ( function_exists('plugin_submissioncount_comment')) {
        $num = $num + plugin_submissioncount_comment();
    }

    return $num;
}

/**
* This function will get & check user or admin options from plugins and check
* required ones for availability. This function is called by several other
* functions and is not to be called from the plugin directly. The function which
* call this here follow below.
*
* NOTE for plugin developers:
* The plugin is responsible for its own security.
* This supports a plugin having either a single menuitem or multiple menuitems.
* The plugin has to provide an array for the menuitem of the format:
* array (menuitem_title, item_url, submission_count)
* or an array of arrays in case there are several entries:
* <code>
* array (
*   array (menuitem1_title, item1_url, submission1_count),
*   array (menuitem2_title, item2_url, submission2_count),
*   array (menuitem3_title, item3_url, submission3_count))
* </code>
* Plugin function can return a single record array or multiple records
*
*
* @param    array $var_names    An array of the variables that are retrieved.
*                               This has to match the named array that is used
*                               in the function returning the values
* @param    array $required_names An array of true/false-values, describing
*                                 which of the above listed values is required
*                                 to give a valid set of data.
* @param    string $function_name A string that gives the name of the function
*                                 at the plugin that will return the values.
* @return   array Returns options to add to the given menu that is calling this
* @internal not to be used by plugins
*
*/
function PLGINT_getOptionsforMenus($var_names, $required_names, $function_name)
{
    global $_PLUGINS;

    $plgresults = array ();

    $counter = 0;
    foreach ($_PLUGINS as $pi_name) {
        $function = $function_name . $pi_name;
        if (function_exists ($function)) {
            $plg_array = $function();
            if (($plg_array !== false) && (is_array($plg_array) && count ($plg_array) > 0)) {
                // Check if plugin is returning a single record array or multiple records
                $entries = 1;
                if ( is_array($plg_array[0]) ) $entries = count ($plg_array[0]);
                $sets_array = array();
                if ($entries == 1) {
                    // Single record - so we need to prepare the sets_array;
                    $sets_array[0] = $plg_array;
                } else {
                    // Multiple menuitem records - in required format
                    $sets_array = $plg_array;
                }
                foreach ($sets_array as $val) {
                    $plugin = new Plugin();
                    $good_array = true;
                    for ($n = 0; $n < count($var_names); $n++) {
                        if (isset ($val[$n])) {
                            $plugin->{$var_names[$n]} = $val[$n];
                        } else {
                            $plugin->{$var_names[$n]} = '';
                        }
                        if (empty ($plugin->{$var_names[$n]}) && $required_names[$n]) {
                            $good_array = false;
                        }
                    }
                    $counter++;
                    if ($good_array) {
                        $plgresults[$counter] = $plugin;
                    }
                }
            }
        }
    }

    return $plgresults;
}

/**
* This function shows the option for all plugins at the top of the
* command and control center.
*
* This supports that a plugin can have several lines in the CC menu.
* The plugin has to provide simply a set arrays with 3 variables in order to
* get n lines in the menu such as
* <code>
* array(
*   array("first line", "url1", "1"),
*   array("second line", "url2", "44"),
*            etc, etc)
* </code>
* If there is only one item, a single array is enough:
* <code>
* array("first line", "url1", "1")
* </code>
*
* @return   array   Returns Command and Control options for moderation.php
*
*/
function PLG_getCCOptions()
{
    $var_names = array('adminlabel', 'adminurl', 'plugin_image');
    $required_names = array(true, true, true);
    $function_name = 'plugin_cclabel_';
    $plgresults = PLGINT_getOptionsforMenus($var_names, $required_names, $function_name);

    return $plgresults;
}

/**
* This function will show any plugin adminstrative options in the
* admin functions block on every page (assuming the user is an admin
* and is logged in).
*
* NOTE: the plugin is responsible for its own security.
* This supports that a plugin can have several lines in the Admin menu.
* The plugin has to provide simply a set arrays with 3 variables in order to
* get n lines in the menu such as
* <code>
* array(
*   array("first line", "url1", "1"),
*   array("second line", "url2", "44"),,
*            etc, etc)
* </code>
* If there is only one item, a single array is enough:
* <code>
* array("first line", "url1", "1")
* </code>
*
* @return   array   Returns options to put in admin menu
*
*/
function PLG_getAdminOptions($force_reload = false)
{
    static $plgresults = null;

    if (!empty($plgresults) && !$force_reload) {
        return $plgresults;
    }

    $var_names = array('adminlabel', 'adminurl', 'numsubmissions');
    $required_names = array(true, true, false);
    $function_name = 'plugin_getadminoption_';
    $plgresults = PLGINT_getOptionsforMenus($var_names, $required_names, $function_name);

    return $plgresults;
}

/**
* This function will show any plugin user options in the
* user block on every page
*
* This supports that a plugin can have several lines in the User menu.
* The plugin has to provide simply a set of arrays with 3 variables in order to
* get n lines in the menu such as
* <code>
* array(
*   array("first line", "url1", "1"),
*   array("second line", "url2", "44"),
*            etc, etc)
* </code>
* If there is only one item, a single array is enough:
* <code>
* array("first line", "url1", "1")
* </code>
*
* NOTE: the plugin is responsible for its own security.
*
* @return   array   Returns options to add to user menu
*
*/
function PLG_getUserOptions()
{
    // I know this uses the adminlabel, adminurl but who cares?
    $var_names = array('adminlabel', 'adminurl', 'numsubmissions');
    $required_names = array(true, true, false);
    $function_name = 'plugin_getuseroption_';
    $plgresults = PLGINT_getOptionsforMenus($var_names, $required_names, $function_name);

    return $plgresults;
}

/**
* This function is responsible for calling
* plugin_moderationapproves_<pluginname> which approves an item from the
* submission queue for a plugin.
*
* @param        string      $type       Plugin name to do submission approval for
* @param        string      $id         used to identify the record to approve
* @return       boolean     Returns true on success otherwise false
*
*/
function PLG_approveSubmission($type, $id)
{
    $args[1] = $id;

    return PLG_callFunctionForOnePlugin('plugin_moderationapprove_' . $type, $args);
}

/**
* This function is responsible for calling
* plugin_moderationdelete_<pluginname> which deletes an item from the
* submission queue for a plugin.
*
* @param        string      $type       Plugin to do submission deletion for
* @param        string      $id         used to identify the record for which to delete
* @return       boolean     Returns true on success otherwise false
*
*/
function PLG_deleteSubmission($type, $id)
{
    $args[1] = $id;

    return PLG_callFunctionForOnePlugin('plugin_moderationdelete_' . $type, $args);
}

/**
* This function calls the plugin_savesubmission_<pluginname> to save
* a user submission
*
* @param        string      $type       Plugin to save submission for
* @param        array       $A          holds plugin specific data to save
* @return       boolean     Returns true on success otherwise false
*
*/
function PLG_saveSubmission($type, $A)
{
    $args[1] = $A;

    return PLG_callFunctionForOnePlugin('plugin_savesubmission_' . $type, $args);
}

/**
* This function starts the chain of calls needed to show any submissions
* needing moderation for the plugins.
*
* @return   string      returns list of items needing moderation for plugins
*
*/
function PLG_showModerationList($token)
{
    global $_PLUGINS;

    // needed until story becomes a plugin
    // also ensures that story moderation is always first
    // here is where it might be handy to control plugin order ...
    $retval = MODERATE_itemList('article', $token);
    $retval .= MODERATE_itemList('comment',$token);

    foreach ($_PLUGINS as $pi_name) {
        $retval .= MODERATE_itemList($pi_name, $token);
    }

    return $retval;
}

/**
* This function is responsible for setting the plugin-specific values
* needed by moderation.php to approve stuff.
*
* @param        string      $type       Plugin to call function for
* @return       array       $retval     Array of results as follows:
*
* $key              string      name of key field in table (eg. uid, sid)
* $table            string      name of table to which approved items are posted
* $fields           string      fields in submission table that are to be posted
* $submissiontable  string      name of table containing submissions
*
*/
function PLG_getModerationValues($type)
{
    global $_TABLES;

    switch ($type) {

        case 'user':

            return array(
                'uid',
                $_TABLES['users'],
                'email,username,uid,remoteservice,remoteusername',
                ''
            );
            break;

        case 'draftstory':
            return array(
                'sid',
                $_TABLES['stories'],
                '',
                ''
            );
            break;

        default:
            return PLG_callFunctionForOnePlugin('plugin_moderationvalues_' . $type);
            break;
    }

}

/**
* This function is resonsible for calling plugin_submit_<pluginname> so
* that the submission form for the plugin is displayed.
*
* @param        string      $type       Plugin to show submission form for
* @return       string      HTML for submit form for plugin
*
*/
//@TODO - To be retired
function PLG_showSubmitForm($type)
{
    return PLG_callFunctionForOnePlugin('plugin_submit_' . $type);
}

/**
* This function will show the centerblock for any plugin
* It will be display before any news and after any defined staticpage content.
* The plugin is responsible to format the output correctly.
*
* @param   int      $where  1 = top, 2 = after feat. story, 3 = bottom of page
* @param   int      $page   page number (1, ...)
* @param   string   $topic  topic ID or empty string == front page
* @return  string           Formatted center block content
*
*/
function PLG_showCenterblock($where = 1, $page = 1, $topic = '')
{
    global $PLG_bufferCenterAPI, $PLG_buffered, $_PLUGINS;

    $retval = '';

    // buffer function names since we're coming back for them two more times
    if (!$PLG_buffered) {
        $PLG_bufferCenterAPI = array ();
        foreach ($_PLUGINS as $pi_name) {
            $function = 'plugin_centerblock_' . $pi_name;
            if (function_exists($function)) {
                $PLG_bufferCenterAPI[$pi_name] = $function;
            }
        }
        $PLG_buffered = true;
    }

    foreach ($PLG_bufferCenterAPI as $function) {
        $retval .= $function($where, $page, $topic);

        if (($where == 0) && !empty ($retval)) {
            break;
        }
    }
    $function = 'CUSTOM_centerblock';
    if (function_exists($function)) {
        $retval .= $function($where, $page, $topic);
    }

    return $retval;
}

/**
* This function will inform all plugins when a new user account is created.
*
* @param    int     $uid    user id of the new user account
* @return   void
*
*/
function PLG_createUser ($uid)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_user_create_' . $pi_name;
        if (function_exists($function)) {
            $function ($uid);
        }
    }

    $function = 'CUSTOM_user_create';
    if (function_exists($function)) {
        $function($uid);
    }
}

/**
* Inform plugins a user is being merged
*
* @param    int     $originaluid    Original uid ID (to be deleted)
* @param    int     $destinationUID Destination user id
* @since    glFusion v1.5.0
*
*/
function PLG_moveUser($originalUID, $destinationUID)
{
    global $_PLUGINS, $_TABLES;

    $db = Database::getInstance();

    // comments...
    try {
        $db->conn->executeUpdate(
            "UPDATE `{$_TABLES['comments']}` SET uid=?
                WHERE uid=?",
            array(
                $destinationUID,
                $originalUID
            ),
            array(
                Database::INTEGER,
                Database::INTEGER
            )
        );
    } catch(Throwable $e) {
        Log::write('system',Log::ERROR,'Error moving user comments: ' . $e->getMessage());
    }

    // ratings
    try {
        $db->conn->executeUpdate(
            "UPDATE `{$_TABLES['rating_votes']}` SET uid=?
                WHERE uid=?",
            array(
                $destinationUID,
                $originalUID
            ),
            array(
                Database::INTEGER,
                Database::INTEGER
            )
        );
    } catch(Throwable $e) {
        Log::write('system',Log::ERROR,'Error moving user rating votes: ' . $e->getMessage());
    }

    $pluginTypes = array_merge(array('article'), $_PLUGINS);

    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_user_move_' . $pi_name;
        if (function_exists ($function)) {
            $function($originalUID, $destinationUID);
        }
    }

    $function = 'CUSTOM_user_move';
    if (function_exists($function)) {
        $function($originalUID, $destinationUID);
    }
}


/**
* This function will inform all plugins when a user account is deleted.
*
* @param    int     $uid    user id of the deleted user account
* @return   void
*
*/
function PLG_deleteUser ($uid)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_user_delete_' . $pi_name;
        if (function_exists ($function)) {
            $function($uid);
        }
    }

    $function = 'CUSTOM_user_delete';
    if (function_exists($function)) {
        $function($uid);
    }
}

/**
* This function will inform all plugins when a user logs in
*
* Note: This function is NOT called when users are re-authenticated by their
* long-term cookie. The global variable $_USER['auto_login'] will be set to
* 'true' in that case, however.
*
* @param    int     $uid    user id
* @return   void
*
*/
function PLG_loginUser ($uid)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_user_login_' . $pi_name;
        if (function_exists($function)) {
            $function($uid);
        }
    }

    $function = 'CUSTOM_user_login';
    if (function_exists($function)) {
        $function($uid);
    }
}

/**
* This function will inform all plugins when a user logs out.
* Plugins should not rely on this ever being called, as the user may simply
* close the browser instead of logging out.
*
* @param    int     $uid    user id
* @return   void
*
*/
function PLG_logoutUser ($uid)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_user_logout_' . $pi_name;
        if (function_exists($function)) {
            $function($uid);
        }
    }

    $function = 'CUSTOM_user_logout';
    if (function_exists($function)) {
        $function($uid);
    }
}

/**
* This functions is called to inform plugins when a user's information
* (profile or preferences) has changed.
*
* @param    int     $uid    user id
* @return   void
*
*/
function PLG_userInfoChanged ($uid)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_user_changed_' . $pi_name;
        if (function_exists($function)) {
            $function($uid);
        }
    }

    $function = 'CUSTOM_user_changed';
    if (function_exists($function)) {
        $function($uid);
    }
}

/**
* This functions is called to inform plugins when a group's information has
* changed or a new group has been created.
*
* @param    int     $grp_id     Group ID
* @param    string  $mode       type of change: 'new', 'edit', or 'delete'
* @return   void
*
*/
function PLG_groupChanged ($grp_id, $mode)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_group_changed_' . $pi_name;
        if (function_exists($function)) {
            $function($grp_id, $mode);
        }
    }

    $function = 'CUSTOM_group_changed';
    if (function_exists($function)) {
        $function($grp_id, $mode);
    }
}

/**
* glFusion is about to display the edit form for the user's profile. Plugins
* now get a chance to add their own variables and input fields to the form.
*
* @param    int  $uid        user id of the user profile to be edited
* @param    char $panel      profile panel being displayed
* @param    char $fieldset   fieldset being displayed
* @return   void
*
*/
function PLG_profileEdit ($uid, $panel = '', $fieldset='')
{
    global $_PLUGINS;

    $retval = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_profileedit_' . $pi_name;
        if (function_exists($function)) {
            $retval .= $function ($uid, $panel,$fieldset);
        }
    }

    $function = 'CUSTOM_profileedit';
    if (function_exists($function)) {
        $retval .= $function($uid, $panel, $fieldset);
    }

    return $retval;
}

/**
* The user wants to save changes to his/her profile. Any plugin that added its
* own variables or blocks to the profile input form will now have to extract
* its data and save it.
* Plugins will have to refer to the global $_POST array to get the
* actual data.
*
* @param    string  $plugin     name of a specific plugin or empty (all plugins)
* @return   void
*
*/
function PLG_profileSave ($plugin = '', $uid = 0)
{

    $args[1] = $uid;

    if (empty ($plugin)) {
        PLG_callFunctionForAllPlugins ('profilesave', $args);
    } else {
        $function = 'plugin_profilesave_' . $plugin;
        return PLG_callFunctionForOnePlugin($function, $args);
    }
}


/**
* glFusion is about to display the edit form for the user's profile. Plugins
* now get a chance to add their own variables and input fields to the form.
*
* THIS FUNCTION IS DEPRECIATED - see PLG_profileEdit()
*
* @param    int  $uid        user id of the user profile to be edited
* @param    ref  $template   reference of the Template for the profile edit form
* @return   void
*
*/
function PLG_profileVariablesEdit ($uid, &$template)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_profilevariablesedit_' . $pi_name;
        if (function_exists($function)) {
            $function ($uid, $template);
        }
    }

    $function = 'CUSTOM_profilevariablesedit';
    if (function_exists($function)) {
        $function($uid, $template);
    }
}

/**
* glFusion is about to display the edit form for the user's profile. Plugins
* now get a chance to add their own blocks below the standard form.
*
* THIS FUNCTION IS DEPRECIATED - see PLG_profileEdit()
*
* @param    int      $uid   user id of the user profile to be edited
* @return   string          HTML for additional block(s)
*
*/
function PLG_profileBlocksEdit ($uid)
{
    global $_PLUGINS;

    $retval = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_profileblocksedit_' . $pi_name;
        if (function_exists($function)) {
            $retval .= $function ($uid);
        }
    }

    $function = 'CUSTOM_profileblocksedit';
    if (function_exists($function)) {
        $retval .= $function($uid);
    }

    return $retval;
}

/**
* glFusion is about to display the user's profile. Plugins now get a chance to
* add their own variables to the profile.
*
* @param   int   $uid        user id of the user profile to be edited
* @param   ref   $template   reference of the Template for the profile edit form
* @return  void
*
*/
function PLG_profileVariablesDisplay ($uid, &$template)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_profilevariablesdisplay_' . $pi_name;
        if (function_exists($function)) {
            $function ($uid, $template);
        }
    }

    $function = 'CUSTOM_profilevariablesdisplay';
    if (function_exists($function)) {
        $function($uid, $template);
    }
}

/**
* glFusion is about to display the user's profile. Plugins now get a chance to
* add their own blocks below the standard profile form.
*
* @param    int      $uid        user id of the user profile to be edited
* @return   string               HTML for additional block(s)
*
*/
function PLG_profileBlocksDisplay ($uid)
{
    global $_PLUGINS;

    $retval = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_profileblocksdisplay_' . $pi_name;
        if (function_exists($function)) {
            $retval .= $function ($uid);
        }
    }

    $function = 'CUSTOM_profileblocksdisplay';
    if (function_exists($function)) {
        $retval .= $function($uid);
    }

    return $retval;
}


/**
* glFusion is about to display the user's profile. Plugins now get a chance to
* add their own icons under the profile image.
*
* @param    int      $uid        user id of the user profile to be edited
* @return   array                Returns an array of arrays (one set for each plugin),
*                                The return array contains:
*                                   - url - full URL that icon will link to
*                                   - text - hover text
*                                   - icon - full URL to icon
*
*/
function PLG_profileIconDisplay ($uid)
{
    global $_PLUGINS;

    $retval = array();

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_profileicondisplay_' . $pi_name;
        if (function_exists($function)) {
            $retval[] = $function ($uid);
        }
    }
    return $retval;
}

/**
* The user wants to save changes to his/her profile. Any plugin that added its
* own variables or blocks to the profile input form will now have to extract
* its data and save it.
* Plugins will have to refer to the global $_POST array to get the
* actual data.
*
* THIS FUNCTION IS DEPRECIATED - see PLG_profileSave()
*
* @param    string  $plugin     name of a specific plugin or empty (all plugins)
* @return   void
*
*/
function PLG_profileExtrasSave ($plugin = '')
{
    if (empty ($plugin)) {
        PLG_callFunctionForAllPlugins ('profileextrassave');
    } else {
        PLG_callFunctionForOnePlugin ('plugin_profileextrassave_' . $plugin);
    }
}

/**
* This function can be called to check if an plugin wants to set a template
* variable
*
* Example in COM_siteHeader, the API call is now added
* A plugin can check for $templatename == 'header' and then set additional
* template variables
*
* @param    string   $templatename  Name of calling template
* @param    ref     &$template      reference for the Template
* @return   void
* @see      CUSTOM_templateSetVars
*
*/
function PLG_templateSetVars ($templatename, &$template)
{
    global $_PLUGINS;

    if (function_exists ('CUSTOM_templateSetVars')) {
        CUSTOM_templatesetvars($templatename, $template);
    }

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_templatesetvars_' . $pi_name;
        if (function_exists($function)) {
            $function ($templatename, $template);
        }
    }
}

/**
* This function is called from COM_siteHeader and will return additional header
* information. This can be used for JavaScript functions required for the plugin
* or extra Metatags
*
* @return   string      returns a concatenated string of all plugins extra header code
*/
function PLG_getHeaderCode()
{
    global $_PLUGINS;

    $headercode = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getheadercode_' . $pi_name;
        if (function_exists($function)) {
            $headercode .= $function();
        }
    }

    $function = 'CUSTOM_getheadercode';
    if (function_exists($function)) {
        $headercode .= $function();
    }

    return $headercode;
}

/**
* Get a list of all currently supported autolink tags.
*
* Returns an associative array where $A['tag-name'] = 'plugin-name'
*
* @param    string  $namespace      Namespace or plugin name collecting tag info
* @param    string  $operation      Operation being performed
* @return   array   All currently supported autolink tags
*
*/
function PLG_collectTags($namespace='',$operation='')
{
    global $_CONF, $_PLUGINS, $_AUTOTAGS;

    if (isset($_CONF['disable_autolinks']) && ($_CONF['disable_autolinks'] == 1)) {
        // autolinks are disabled - return an empty array
        return array ();
    }

    $autoTagPerms    = PLG_autoTagPerms();
    if ( !empty($namespace) && !empty($operation) ) {
        $postFix = '.'.$namespace.'.'.$operation;
    } else {
        $postFix = '';
    }

    // Determine which Core Modules and Plugins support AutoLinks
    //                        'tag'   => 'module'
    $autolinkModules = array();

//    $coreTags = array ('story' => 'glfusion','story_introtext' => 'glfusion', 'showblock' => 'glfusion', 'menu' => 'glfusion');
    $coreTags = array ('showblock' => 'glfusion', 'menu' => 'glfusion');
    foreach ($coreTags as $tag => $pi_name) {
        $permCheck = $tag.$postFix;
        if ( empty($postFix) || !isset($autoTagPerms[$permCheck]) || $autoTagPerms[$permCheck] == 1 ) {
            $autolinkModules[$tag] = $pi_name;
        }
    }

    $pluginTypes = array_merge(array('article'), $_PLUGINS);

    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_autotags_' . $pi_name;
        if (function_exists($function)) {
            $autotag = $function ('tagname');
            if (is_array($autotag)) {
                foreach ($autotag as $tag) {
                    $permCheck = $tag.$postFix;
                    if ( empty($postFix) || !isset($autoTagPerms[$permCheck]) || $autoTagPerms[$permCheck] == 1 ) {
                        $autolinkModules[$tag] = $pi_name;
                    }
                }
            } else if ( $autotag != '' ) {
                $permCheck = $autotag.$postFix;
                if ( empty($postFix) || !isset($autoTagPerms[$permCheck]) || $autoTagPerms[$permCheck] == 1 ) {
                    $autolinkModules[$autotag] = $pi_name;
                }
            }
        }
    }
    // process user auto tags
    $at = array_keys($_AUTOTAGS);
    if ( is_array($at) ) {
        foreach($at AS $tag) {
            $permCheck = $tag.$postFix;
            if ( empty($postFix) || !isset($autoTagPerms[$permCheck]) || $autoTagPerms[$permCheck] == 1 ) {
                $autolinkModules[$tag] = 'glfusion';
            }
        }
    }
    return $autolinkModules;
}

/**
* Get a list of all areas that utilize autotags via PLG_replaceTags()
*
* Returns an associative array where $A['namespace'] = 'operation'
*
* @return   array   All array of namespace / usage
*
*/
function PLG_collectAutotagUsage()
{
    global $_CONF, $_PLUGINS;

    if (isset($_CONF['disable_autolinks']) && ($_CONF['disable_autolinks'] == 1)) {
        // autolinks are disabled - return an empty array
        return array ();
    }

    $autolinkModules = array(
        array('namespace' => 'glfusion', 'usage'    => 'comment'),
//        array('namespace' => 'glfusion', 'usage'    => 'story'),
        array('namespace' => 'glfusion', 'usage'    => 'contact_user'),
        array('namespace' => 'glfusion', 'usage'    => 'mail_story'),
        array('namespace' => 'glfusion', 'usage'    => 'block'),
        array('namespace' => 'glfusion', 'usage'    => 'about_user'),
    );

    $pluginTypes = array_merge(array('article'), $_PLUGINS);

    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_autotags_' . $pi_name;
        if (function_exists($function)) {
            $autotag = $function ('tagusage');
            if (is_array($autotag)) {
                $autolinkModules = array_merge($autolinkModules,$autotag);
            }
        }
    }
    ksort($autolinkModules);
    return $autolinkModules;
}

/**
* Get a list of autotag-namespace-operation permissiong mapping
*
* Returns an associative array where $A['autotag.namespace.operation'] = allowed
*
* @return   array   array of autotag.namespace.operation => allowed
*
*/
function PLG_autoTagPerms()
{
    global $_CONF, $_TABLES, $autoTagUsage;

    static $atp_initialized;

    if ( $atp_initialized == 1 ) {
        return $autoTagUsage;
    }

    $c = Cache::getInstance();
    $key = 'atperm__'.$c->securityHash();
    $retval = $c->get($key);

    if ( $retval !== null ) {
        $autoTagUsage = unserialize($retval);
        $atp_initialized = 1;
        return $retval;
    }

    $db = Database::getInstance();

    $autoTagArray = array();
    $tags = array();

    $stmt = $db->conn->query(
            "SELECT * FROM `{$_TABLES['autotag_perm']}`
              JOIN `{$_TABLES['autotag_usage']}`
              ON {$_TABLES['autotag_perm']}.autotag_id = {$_TABLES['autotag_usage']}.autotag_id"
            );

    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $uniqueID = $row['autotag_name'].'.'.$row['usage_namespace'].'.'.$row['usage_operation'];
        $autoTagArray[$uniqueID] = $row['autotag_allowed'];
    }
    $atp_initialized = 1;
    $autoTagUsage = $autoTagArray;

    $c->set($key,serialize($autoTagArray),'atperm');

    return $autoTagArray;
}

/**
* This function will allow plugins to support the use of custom autolinks
* in other site content. Plugins can now use this API when saving content
* and have the content checked for any autolinks before saving.
* The autolink would be like:  [story:20040101093000103 here]
*
* @param   string   $content   Content that should be parsed for autolinks
* @param   string   $namespace Optional Namespace or plugin name collecting tag info
* @param   string   $operation Optional Operation being performed
* @param   string   $plugin    Optional if you only want to parse using a specific plugin
*
*/
function PLG_replaceTags($content,$namespace='',$operation='', $plugin = '')
{
    global $_CONF, $_TABLES, $_BLOCK_TEMPLATE, $LANG32, $_AUTOTAGS, $_VARS, $mbMenu, $autoTagUsage;

    if (isset ($_CONF['disable_autolinks']) && ($_CONF['disable_autolinks'] == 1)) {
        // autolinks are disabled - return $content unchanged
        return $content;
    }

    static $recursionCount = 0;

    if ( $recursionCount > 5 ) {
        Log::write('system',Log::WARNING,"AutoTag infinite recursion detected on " . $namespace . " " . $operation);
        return $content;
    }

    $db = Database::getInstance();

    $autolinkModules = PLG_collectTags ();
    $autoTagUsage    = PLG_autoTagPerms();

    if ( !empty($namespace) && !empty($operation) ) {
        $postFix = '.'.$namespace.'.'.$operation;
        $_VARS['at_namespace'] = $namespace;
        $_VARS['at_operation'] = $operation;
    } else {
        $postFix = '';
        $_VARS['at_namespace'] = '';
        $_VARS['at_operation'] = '';
    }

    // For each supported module, scan the content looking for any AutoLink tags
    $tags = array ();
    $contentlen = utf8_strlen ($content);
    $content_lower = utf8_strtolower ($content);
    foreach ($autolinkModules as $moduletag => $module) {
        $autotag_prefix = '['. $moduletag . ':';
        $offset = 0;
        $prev_offset = 0;
        while ($offset < $contentlen) {
            $start_pos = utf8_strpos ($content_lower, $autotag_prefix,
                                       $offset);
            if ($start_pos === false) {
                break;
            } else {
                $end_pos  = utf8_strpos ($content_lower, ']', $start_pos);
                $next_tag = utf8_strpos ($content_lower, '[', $start_pos + 1);
                if (($end_pos > $start_pos) AND
                        (($next_tag === false) OR ($end_pos < $next_tag))) {
                    $taglength = $end_pos - $start_pos + 1;
                    $orig_tag = utf8_substr ($content, $start_pos, $taglength);
                    $tag = str_replace(array("\xc2\xa0",'&nbsp;'), ' ', $orig_tag);
                    $parms = explode(' ', $tag);
                    // Extra test to see if autotag was entered with a space
                    // after the module name
                    if (utf8_substr ($parms[0], -1) == ':') {
                        $startpos = utf8_strlen ($parms[0]) + utf8_strlen ($parms[1]) + 2;
                        $label = str_replace (']', '', utf8_substr ($tag, $startpos));
                        $tagid = $parms[1];
                    } else {
                        $label = str_replace (']', '',
                                 utf8_substr ($tag, utf8_strlen ($parms[0]) + 1));
                        $parms = explode (':', $parms[0]);
                        if (count ($parms) > 2) {
                            // whoops, there was a ':' in the tag id ...
                            array_shift ($parms);
                            $tagid = implode (':', $parms);
                        } else {
                            $tagid = $parms[1];
                        }
                    }

                    $newtag = array (
                        'module'    => $module,
                        'tag'       => $moduletag,
                        'tagstr'    => $orig_tag,
                        'startpos'  => $start_pos,
                        'length'    => $taglength,
                        'parm1'     => str_replace (']', '', $tagid),
                        'parm2'     => $label
                    );
                    $tags[] = $newtag;
                } else {
                    // Error: tags do not match - return with no changes
                    unset($_VARS['at_namespace']);
                    unset($_VARS['at_operation']);
                    return $content . $LANG32[32];
                }
                $prev_offset = $offset;
                $offset = $end_pos;
            }
        }
    }

    // If we have found 1 or more AutoLink tag
    if (count ($tags) > 0) {       // Found the [tag] - Now process them all
        $recursionCount++;
        foreach ($tags as $autotag) {
            $permCheck = $autotag['tag'].$postFix;
            if ( empty($postFix) || !isset($autoTagUsage[$permCheck]) || $autoTagUsage[$permCheck] == 1 ) {
                $function = 'plugin_autotags_' . $autotag['module'];

                if (($autotag['module'] == 'glfusion') AND (empty ($plugin) OR ($plugin == 'glfusion'))) {
                    $url = '';
                    $linktext = $autotag['parm2'];

                    if ( $autotag['tag'] == 'showblock' ) {
                        $blockName = COM_applyBasicFilter($autotag['parm1']);

                        $B = $db->conn->fetchAssoc(
                            "SELECT * FROM `{$_TABLES['blocks']}`
                                WHERE name = ?" . $db->getPermSQL('AND'),
                            array($blockName),
                            array(Database::STRING)
                        );

                        if ($B !== false && $B !== null) {
                            $skip = 0;
                            $template = '';
                            $side     = '';
                            $px = explode (' ', trim ($autotag['parm2']));
                            if (is_array ($px)) {
                                foreach ($px as $part) {
                                    if (substr ($part, 0, 9) == 'template:') {
                                        $a = explode (':', $part);
                                        $template = $a[1];
                                        $skip++;
                                    } elseif (substr ($part, 0, 5) == 'side:') {
                                        $a = explode (':', $part);
                                        $side = $a[1];
                                        $skip++;
                                        break;
                                    }
                                }
                                if ($skip != 0) {
                                    if (count ($px) > $skip) {
                                        for ($i = 0; $i < $skip; $i++) {
                                            array_shift ($px);
                                        }
                                        $caption = trim (implode (' ', $px));
                                    } else {
                                        $caption = '';
                                    }
                                }
                            }
                            if ( $template != '' ) {
                                $_BLOCK_TEMPLATE[$blockName] = 'blockheader-'.$template.'.thtml,blockfooter-'.$template.'.thtml';
                            }
                            if ( $side == 'left' ) {
                                $B['onleft'] = 1;
                            } else if ( $side == 'right' ) {
                                $B['onleft'] = 0;
                            }
                            $linktext = COM_formatBlock( $B );
                            $content = str_replace($autotag['tagstr'],$linktext,$content);
                        } else {
                            $content = str_replace($autotag['tagstr'],'',$content);
                        }
                    }
                    if ( $autotag['tag'] == 'menu' ) {
                        $menu = '';
                        $menuID = trim($autotag['parm1']);
                        $menuHTML = displayMenu($menuID);
                        $content = str_replace($autotag['tagstr'],$menuHTML,$content);
                    }
                    if (isset($_AUTOTAGS[$autotag['tag']])) {
                        $content = autotags_autotag('parse', $content, $autotag);
                    }
                } else if (function_exists ($function) AND
                        (empty ($plugin) OR ($plugin == $autotag['module']))) {
                    $content = $function ('parse', $content, $autotag);
                }
            }
        }
        $recursionCount--;
    }
    unset($_VARS['at_namespace']);
    unset($_VARS['at_operation']);
    return $content;
}


/**
* Prepare a list of all plugins that support feeds. To do this, we re-use
* plugin_getfeednames_<plugin name> and only keep the names of those plugins
* which support that function
*
* @return   array   array of plugin names (can be empty)
*
*/
function PLG_supportingFeeds()
{
    global $_PLUGINS;

    $plugins = array();

    USES_lib_comment();

    $pluginTypes = array_merge(array('article', 'comment'), $_PLUGINS);

    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_getfeednames_' . $pi_name;
        if (function_exists($function)) {
            $feeds = $function();
            if (is_array($feeds) && (sizeof($feeds) > 0)) {
                $plugins[] = $pi_name;
            }
        }
    }

    $function = 'CUSTOM_getfeednames';
    if (function_exists($function)) {
        $feeds = $function();
        if (is_array($feeds) && (sizeof($feeds) > 0)) {
            $plugins[] = 'custom';
        }
    }

    return $plugins;
}

/**
* Ask the plugin for a list of feeds it supports. The plugin is expected to
* return an array of id/name pairs where 'id' is the plugin's internal id
* for the feed and 'name' is what will be presented to the user.
*
* @param    string   plugin   plugin name
* @return   array             array of id/name pairs
*
*/
function PLG_getFeedNames($plugin)
{
    global $_PLUGINS;

    $feeds = array ();

    if ($plugin == 'custom') {
        $function = 'CUSTOM_getfeednames';
        if (function_exists($function)) {
            $feeds = $function();
        }
    } else {
        $pluginTypes = array_merge(array('comment'), $_PLUGINS);
        USES_lib_comment();
        if (in_array($plugin, $pluginTypes)) {
            $function = 'plugin_getfeednames_' . $plugin;
            if (function_exists($function)) {
                $feeds = $function();
            }
        }
    }

    return $feeds;
}

/**
* Get the content of a feed from the plugin.
* The plugin is expected to return an array holding the content of the feed
* and to fill in 'link' (some link that represents the same content on the
* site as that in the feed) and 'update_data' (to be stored for later up-to-date
* checks.
*
* @param    string   plugin        plugin name
* @param    int      feed          feed id
* @param    string   link          link to content on the site
* @param    string   update_data   information for later up-to-date checks
* @param    string   feedType      The type of feed (RSS/Atom etc)
* @param    string   feedVersion   The version info of the feed.
* @param    array    $A            The full feed record from the syndication table
* @return   array                  content of feed
*
*/
function PLG_getFeedContent($plugin, $feed, &$link, &$update_data, $feedType, $feedVersion, $A=array())
{
    global $_PLUGINS;

    $content = array ();

    if ($plugin == 'custom') {
        $function = 'CUSTOM_getfeedcontent';
        if (function_exists($function)) {
            $content = $function($feed, $link, $update_data, $feedType, $feedVersion, $A);
        }
    } else {
        $pluginTypes = array_merge(array('article','comment'), $_PLUGINS);
        USES_lib_comment();
        if (in_array ($plugin, $pluginTypes)) {
            $function = 'plugin_getfeedcontent_' . $plugin;
            if (function_exists ($function)) {
                $content = $function ($feed, $link, $update_data, $feedType, $feedVersion, $A);
            }
        }
    }

    return $content;
}


function PLG_getFeedContent2(object $Feed) : array
{
    global $_PLUGINS;

    $plugin = $Feed->getType();

    if ($plugin == 'custom') {
        $function = 'CUSTOM_getfeedcontent2';
        if (function_exists($function)) {
            $content = $function($Feed);
        }
    } else {
        $pluginTypes = array_merge(array('article','comment'), $_PLUGINS);
        USES_lib_comment();
        if (in_array ($plugin, $pluginTypes)) {
            $function = 'plugin_getfeedcontent_' . $plugin;
            if (function_exists ($function)) {
                $content = $function ($Feed->getFilename(), $link, $update_data, $feedType, $feedVersion, $A);
            }
        }
    }

    return $content;
}

/**
  * Get extension tags for a feed. For example, some plugins may extened the
  * available elements for an RSS 2.0 feed for articles. For some reason. This
  * function allows that.
  *
  * @param  string  contentType     Type of feed content, article or a plugin specific type
  * @param  string  contentID       Unique identifier of content item to extend
  * @param  string  feedType        Type of feed format (RSS/Atom/etc)
  * @param  string  feedVersion     Type of feed version (RSS 1.0 etc)
  * @param  string  topic           The topic for the feed.
  * @param  string  fid             The ID of the feed being fethed.
  * @return array                   list of extension tags
  *
  */
function PLG_getFeedElementExtensions($contentType, $contentID, $feedType, $feedVersion, $topic, $fid)
{
    global $_PLUGINS;

    $extensions = array();
    foreach( $_PLUGINS as $plugin )
    {
        $function = 'plugin_feedElementExtensions_'.$plugin;
        if (function_exists($function))
        {
            $extensions = array_merge($extensions, $function($contentType, $contentID, $feedType, $feedVersion, $topic, $fid));
        }
    }

    $function = 'CUSTOM_feedElementExtensions';
    if (function_exists($function))
    {
        $extensions = array_merge($extensions, $function($contentType, $contentID, $feedType, $feedVersion, $topic, $fid));
    }

    return $extensions;
}

/**
  * Get namespaces extensions for a feed. If a plugin has added extended tags
  * to a feed, then it may also need to insert some extensions to the name
  * spaces.
  *
  * @param string contentType   Type of feed content, article or a plugin specific type
  * @param  string  feedType        Type of feed format (RSS/Atom/etc)
  * @param  string  feedVersion     Type of feed version (RSS 1.0 etc)
  * @param  string  topic           The topic for the feed.
  * @param  string  fid             The ID of the feed being fethed.
  * @return array                   list of extension namespaces
  *
  */
function PLG_getFeedNSExtensions($contentType, $feedType, $feedVersion, $topic, $fid)
{
    global $_PLUGINS;

    $namespaces = array();
    foreach( $_PLUGINS as $plugin )
    {
        $function = 'plugin_feedNSExtensions_'.$plugin;
        if (function_exists($function))
        {
            $namespaces = array_merge($namespaces, $function($contentType, $feedType, $feedVersion, $topic, $fid));
        }
    }

    $function = 'CUSTOM_feedNSExtensions';
    if (function_exists($function))
    {
        $namespaces = array_merge($namespaces, $function($contentType, $feedType, $feedVersion, $topic, $fid));
    }

    return $namespaces;
}

/**
  * Get meta tag extensions for a feed. Add extended tags to the meta
  * area of a feed.
  *
  * @param  string contentType      Type of feed content, article or a plugin specific type
  * @param  string  feedType        Type of feed format (RSS/Atom/etc)
  * @param  string  feedVersion     Type of feed version (RSS 1.0 etc)
  * @param  string  topic           The topic for the feed.
  * @param  string  fid             The ID of the feed being fethed.
  * @return array                   list of meta tag extensions
  *
  */
function PLG_getFeedExtensionTags($contentType, $feedType, $feedVersion, $topic, $fid)
{
    global $_PLUGINS;

    $tags = array();
    foreach( $_PLUGINS as $plugin )
    {
        $function = 'plugin_feedExtensionTags_'.$plugin;
        if (function_exists($function))
        {
            $tags = array_merge($tags, $function($contentType, $feedType, $feedVersion, $topic, $fid));
        }
    }

    $function = 'CUSTOM_feedExtensionTags';
    if (function_exists($function))
    {
        $tags = array_merge($tags, $function($contentType, $feedType, $feedVersion, $topic, $fid));
    }

    return $tags;
}

/**
* The plugin is expected to check if the feed content needs to be updated.
* This is called from COM_rdfUpToDateCheck() every time glFusion's index.php
* is displayed - it should try to be as efficient as possible ...
*
* NOTE: The presence of non-empty $updated_XXX parameters indicates that an
*       existing entry has been changed. The plugin may therefore apply a
*       different method to check if its feed has to be updated.
*
* @param    string  plugin          plugin name
* @param    int     feed            feed id
* @param    string  topic           "topic" of the feed - plugin specific
* @param    string  limit           number of entries or number of hours
* @param    string  updated_type    (optional) type of feed to update
* @param    string  updated_topic   (optional) topic to update
* @param    string  updated_id      (optional) entry id to update
* @return   bool                    false = feed has to be updated, true = ok
*
*/
function PLG_feedUpdateCheck($plugin, $feed, $topic, $update_data, $limit, $updated_type = '', $updated_topic = '', $updated_id = '')
{
    global $_PLUGINS;

    $is_current = true;

    if ($plugin == 'custom') {
        $function = 'CUSTOM_feedupdatecheck';
        if (function_exists($function)) {
            $is_current = $function ($feed, $topic, $update_data, $limit,
                            $updated_type, $updated_topic, $updated_id);
        }
    } else {
        $pluginTypes = array_merge(array('comment'), $_PLUGINS);
        USES_lib_comment();
        if (in_array($plugin, $pluginTypes)) {
            $function = 'plugin_feedupdatecheck_' . $plugin;
            if (function_exists($function)) {
                $is_current = $function($feed, $topic, $update_data, $limit,
                                $updated_type, $updated_topic, $updated_id);
            }
        }
    }

    return $is_current;
}

/**
* Ask plugins if they want to add something to glFusion's What's New block.
*
* @return   array   array($headlines[], $bylines[], $content[$entries[]])
*
*/
function PLG_getWhatsNew()
{
    global $_PLUGINS;

    $newheadlines = array();
    $newbylines   = array();
    $newcontent   = array();

    foreach ($_PLUGINS as $pi_name) {
        $fn_head = 'plugin_whatsnewsupported_' . $pi_name;
        $fn_new = 'plugin_getwhatsnew_' . $pi_name;
        if (function_exists($fn_head)) {
            // Old style- Separate functions to get header & content
            $supported = $fn_head();
            if (is_array($supported)) {
                list($headline, $byline) = $supported;

                if (function_exists($fn_new)) {
                    $whatsnew = $fn_new ();
                    $newcontent[] = $whatsnew;
                    $newheadlines[] = $headline;
                    $newbylines[] = $byline;
                }
            }
        } elseif (function_exists($fn_new)) {
            // 1.3.0 style- get all 3 elements from one function
            $whatsnew = $fn_new();
            if (is_array($whatsnew) && !empty($whatsnew)) {
                $newheadlines[] = $whatsnew[0];
                $newbylines[] = $whatsnew[1];
                $newcontent[] = $whatsnew[2];
            }
        }
    }

    $fn_head = 'CUSTOM_whatsnewsupported';
    $fn_new = 'CUSTOM_getwhatsnew';
    if (function_exists($fn_head)) {
        // Old style- Separate functions to get header & content
        $supported = $fn_head();
        if (is_array($supported)) {
            list($headline, $byline) = $supported;

            if (function_exists($fn_new)) {
                $whatsnew = $fn_new ();
                $newcontent[] = $whatsnew;
                $newheadlines[] = $headline;
                $newbylines[] = $byline;
            }
        }
    } elseif (function_exists($fn_new)) {
        // 1.3.0 style- get all 3 elements from one function
        $whatsnew = $fn_new();
        if (is_array($whatsnew) && !empty($whatsnew)) {
            $newheadlines[] = $whatsnew[0];
            $newbylines[] = $whatsnew[1];
            $newcontent[] = $whatsnew[2];
        }
    }

    return array($newheadlines, $newbylines, $newcontent);
}

/**
* Ask plugins if they want to add something to glFusion's What's New comment block.
*
* @return   array   array( array(dups, type, title, sid, lastdate) )
*
*/
function PLG_getWhatsNewComment()
{
    global $_PLUGINS;

    $commentrows = array();
    $comments    = array();

    foreach ($_PLUGINS as $pi_name) {
        $fn = 'plugin_getwhatsnewcomment_' . $pi_name;
        if ( function_exists($fn) ) {
            $commentrows = $fn();
            if ( is_array($commentrows) ) {
                $comments = array_merge($commentrows,$comments);
            }
        }
    }
    return $comments;
}

/**
* Allows plugins and core Components to filter out spam.
*
* The Spam-X Plugin is now part of the glFusion Distribution
* This plugin API will call the main function in the Spam-X plugin
* but can also be used to call other plugins or custom functions
* if available for filtering spam or content.
*
* The caller should check for return values > 0 in which case spam has been
* detected and the poster should be told, either via
* <code>
*   echo COM_refresh ($_CONF['site_url'] . '/index.php?msg=' . $result
*                     . '&amp;plugin=spamx');
* </code>
* or by
* <code>
*   COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
* </code>
* Where the former will only display a "spam detected" message while the latter
* will also send an HTTP status code 403 with the message.
*
* @param    string  $content    Text to be filtered or checked for spam
* @param    int     $action     what to do if spam found
* @return   int                 > 0: spam detected, == 0: no spam detected
*
*/
function PLG_checkforSpam($content, $action = -1, $data = array() )
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_checkforSpam_' . $pi_name;
        if (function_exists($function)) {
            $result = $function($content, $action, $data);
            if ($result > 0) { // Plugin found a match for spam
                $result = PLG_spamAction($content, $action,$data);
                return $result;
            }
        }
    }

    $function = 'CUSTOM_checkforSpam';
    if (function_exists($function)) {
        $result = $function($content, $action,$data);
        if ($result > 0) { // Plugin found a match for spam
            $result = PLG_spamAction($content, $action,$data);
            return $result;
        }
    }

    return 0;
}

/**
* Act on spam
*
* This is normally called from PLG_checkforSpam (see above) automatically when
* spam has been detected. There may however be situations where spam has been
* detected by some other means, in which case you may want to trigger the
* spam action explicitly.
*
* @param    string  $content    Text to be filtered or checked for spam
* @param    int     $action     what to do if spam found
* @return   int                 > 0: spam detected, == 0: no spam detected
* @see      PLG_checkforSpam
*
*/
function PLG_spamAction($content, $action = -1,$data = array())
{
    global $_PLUGINS;

    $result = 0;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_spamaction_' . $pi_name;
        if (function_exists($function)) {
            $res = $function($content, $action,$data);
            $result = max($result, $res);
        }
    }

    $function = 'CUSTOM_spamaction';
    if (function_exists($function)) {
        $res = $function($content, $action,$data);
        $result = max($result, $res);
    }

    return $result;
}

/**
* Ask plugin for information about a specific item
*
* Item properties that can be requested:
* - 'date-created'    - creation date, if available
* - 'date-modified'   - date of last modification, if available
* - 'description'     - full description of the item (formatted)
* - 'raw-description' - full raw description (no parsing of tags, etc.)
* - 'excerpt'         - short description of the item
* - 'id'              - ID of the item, e.g. sid for articles
* - 'title'           - title of the item
* - 'url'             - URL of the item
* - 'label'           - Plugin label
*
* 'excerpt' and 'description' may return the same value. Properties that are
* not available should return an empty string.
* Return false for errors (e.g. access denied, item does not exist, etc.).
*
* @param    string  $type       plugin type (incl. 'article' for stories)
* @param    string  $id         ID of an item under the plugin's control or '*'
* @param    string  $what       comma-separated list of item properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function PLG_getItemInfo($type, $id, $what, $uid = 0, $options = array())
{
    global $_CONF;

    if ($type == 'comment') {
        USES_lib_comment();
    }

    $args[1] = $id;
    $args[2] = $what;
    $args[3] = $uid;
    $args[4] = $options;

    $function = 'plugin_getiteminfo_' . $type;
    return PLG_callFunctionForOnePlugin($function, $args);
}

/**
* Allow plugins to provide what's related information on the current content
*
* @param    string  $type       type of the current content
* @param    string  $id         id of the current content
* @return   array               array of arrays of ('title' => $title, 'url' => $url)
*
*/
function PLG_getWhatsRelated( $type, $id )
{
    global $_CONF, $_PLUGINS;

    $res = array();
    $retval = array();

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getwhatsrelated_' . $pi_name;
        if (function_exists($function)) {
            $res = $function($type, $id);
            $retval = array_merge($retval,$res);

        }
    }
    return $retval;
}

/**
* glFusion is about to perform an operation on a trackback or pingback comment
* to one of the items under the plugin's control and asks for the plugin's
* permission to continue.
*
* glFusion handles receiving and deleting trackback comments and pingbacks
* for the plugin but since it doesn't know about the plugin's access control,
* it has to ask the plugin to approve / reject such an operation.
*
* $operation can be one of the following:
* - 'acceptByID':  accept a trackback comment on item with ID $id
*                  returns: true for accept, false for reject
* - 'acceptByURI': accept a pingback comment on item at URL $id
*                  returns: the item's ID for accept, false for reject
* - 'delete':      is the current user allowed to delete item with ID $id?
*                  returns: true for accept, false for reject
*
* @param    string  $type       plugin type
* @param    string  $id         an ID or URL, depending on the operation
* @param    string  $operation  operation to perform
* @return   mixed               depends on $operation
*
*/
function PLG_handlePingComment ($type, $id, $operation)
{
    $args[1] = $id;
    $args[2] = $operation;

    $function = 'plugin_handlepingoperation_' . $type;

    return PLG_callFunctionForOnePlugin ($function, $args);
}


/**
* Check if plugins have a scheduled task they want to run
* The interval between runs is determined by $_CONF['cron_schedule_interval']
*
* @return void
*
*/
function PLG_runScheduledTask ()
{
    global $_PLUGINS;

    if (function_exists ('CUSTOM_runScheduledTask')) {
        CUSTOM_runScheduledTask();
    }
    if ( is_array($_PLUGINS) ) {
        foreach ($_PLUGINS as $pi_name) {
            $function = 'plugin_runScheduledTask_' . $pi_name;
            if (function_exists ($function)) {
                Log::write('system',Log::DEBUG,'CRON: Running PLG_runScheduledTask for ' . $pi_name);
                $function ();
            }
        }
    }
}

/**
* "Generic" plugin API: Save item
*
* To be called whenever glFusion saves an item into the database.
* Plugins can define their own 'itemsaved' function to be notified whenever
* an item is saved or modified.
*
* @param    string  $id     unique ID of the item
* @param    string  $type   type of the item, e.g. 'article'
* @param    string  $old_id (optional) old ID when the ID was changed
* @returns  bool            false
*
*/
function PLG_itemSaved($id, $type, $old_id = '')
{
    global $_PLUGINS;

    $pluginTypes = array('comment','search');

    USES_lib_comment();

    $pluginTypes = array_merge($pluginTypes, $_PLUGINS);

    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_itemsaved_' . $pi_name;
        if (function_exists($function)) {
            $function($id, $type, $old_id);
        }
    }

    if (function_exists('CUSTOM_itemsaved')) {
        CUSTOM_itemsaved($id, $type, $old_id);
    }

    return false;
}


/**
* "Generic" plugin API: Delete item
*
* To be called whenever glFusion removes an item from the database.
* Plugins can define their own 'itemdeleted' function to be notified whenever
* an item is deleted.
*
* @param    string  $id     ID of the item
* @param    string  $type   type of the item, e.g. 'article'
* @param    string  $children  comma separated list of children ids to delete
* @return   void
* @since    glFusion v1.1.6
*
*/
function PLG_itemDeleted($id, $type, $children = null)
{
    global $_PLUGINS;

    $pluginTypes = array('comment','search');

    USES_lib_comment();

    if (!isset($_PLUGINS) || !is_array($_PLUGINS)) {
        $_PLUGINS = array();
    }

    $pluginTypes = array_merge($pluginTypes, $_PLUGINS);
    foreach ($pluginTypes as $pi_name) {
        $function = 'plugin_itemdeleted_' . $pi_name;
        if (function_exists($function)) {
            $function($id, $type, $children);
        }
    }

    if (function_exists('CUSTOM_itemdeleted')) {
        CUSTOM_itemdeleted($id, $type, $children);
    }
}

/**
* "Generic" plugin API: Display item
*
* To be called (eventually) whenever glFusion displays an item.
* Plugins can hook into this and add content to the displayed item, in the form
* of an array (true, string1, string2...).
*
* The object that called can then display one or several items with a
* object-defined layout.
*
* Plugins can signal an error by returning an array (false, 'Error Message')
* In case of an error, the error message will be written to the error.log and
* nothing will be displayed on the output.
*
* @param    string  $id     unique ID of the item
* @param    string  $type   type of the item, e.g. 'article'
* @return   array           array with a status and one or several strings
*
*/

function PLG_itemDisplay($id, $type)
{
    global $_PLUGINS;
    $result_arr = array();

    $plugins = count($_PLUGINS);
    for ($display = 0; $display < $plugins; $display++) {
        $function = 'plugin_itemdisplay_' . $_PLUGINS[$display];
        if (function_exists($function)) {
            $result = $function($id, $type);
            if ($result[0] == false) {
                // plugin reported a problem - do not add and continue
                Log::write('system',Log::ERROR,$result[1]);
            } else {
                array_shift($result);
                $result_arr = array_merge($result_arr,$result);
            }
        }
    }

    $function = 'CUSTOM_itemdisplay';
    if (function_exists ($function)) {
        $result = $function ($id, $type);
        if ($result[0] == false) {
            // plugin reported a problem - do not add and continue
            Log::write('system',Log::ERROR,$result[1]);
        } else {
            array_shift($result);
            $result_arr = array_merge($result_arr,$result);
        }
    }

    return $result_arr;
}



/**
* Gets glFusion blocks from plugins
*
* Returns data for blocks on a given side and, potentially, for
* a given topic.
*
* @param        string      $side       Side to get blocks for (right or left for now)
* @param        string      $topic      Only get blocks for this topic
* @return   array of block data
*
*/
function PLG_getBlocks($side, $topic='')
{
    global $_PLUGINS;

    $ret = array();
    $cust_items = '';
    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getBlocks_' . $pi_name;
        if (function_exists($function)) {
            $items = $function($side, $topic='');
            if (is_array($items)) {
                $ret = array_merge($ret, $items);
            }
        }
    }

    if (function_exists('CUSTOM_getBlocks')) {
       $cust_items .= CUSTOM_getBlocks($side, $topic='');
       if (is_array($cust_items)) {
          $ret = array_merge($ret, $cust_items);
       }
    }

    return $ret;
}

/**
* Get the URL of a plugin's icon
*
* @param    string  $type   plugin name
* @return   string          URL of the icon
*
*/
function PLG_getIcon($type)
{
    global $_CONF;

    $retval = '';

    // try the "geticon" function first
    $function = 'plugin_geticon_' . $type;
    if (function_exists($function)) {
        $retval = $function ();
    }

    // if that didn't work, try the "cclabel" function
    if (empty ($retval)) {
        $function = 'plugin_cclabel_' . $type;
        if (function_exists($function)) {
            $cclabel = $function ();
            if (is_array($cclabel)) {
                if (!empty($cclabel[2])) {
                    $retval = $cclabel[2];
                }
            }
        }
    }

    // lastly, search for the icon (assuming it's a GIF)
    if (empty($retval)) {
        $icon = $_CONF['site_url'] . '/' . $type . '/images/' . $type . '.gif';
        $fh = @fopen ($icon, 'r');
        if ($fh === false) {
            $icon = $_CONF['site_admin_url'] . '/plugins/' . $type . '/images/'
                  . $type . '.gif';
            $fh = @fopen ($icon, 'r');
            if ($fh === false) {
                // give up and use a generic icon
                $retval = $_CONF['site_url'] . '/images/icons/plugins.gif';
            } else {
                $retval = $icon;
                fclose ($fh);
            }
        } else {
            $retval = $icon;
            fclose ($fh);
        }
    }

    return $retval;
}

/**
 * Invoke a service
 *
 * @param   string  $type    The plugin type whose service is to be called
 * @param   string  $action  The service action to be performed
 * @param   array   $args    The arguments to be passed to the service invoked
 * @param   array   $output  The output variable that will contain the output after invocation
 * @param   array   $svc_msg The output variable that will contain the service messages
 * @return  int              The result of the invocation
 *
 */
function PLG_invokeService($type, $action, $args, &$output, &$svc_msg)
{
    global $_CONF;

    $retval = PLG_RET_ERROR;

    $output  = '';
    $svc_msg = '';

    // Check if the plugin type and action are valid
    $function = 'service_' . $action . '_' . $type;

    if (function_exists($function) && PLG_wsEnabled($type)) {
        if (!isset($args['gl_svc'])) {
            $args['gl_svc'] = false;
        }
        $retval = $function($args, $output, $svc_msg);
    }

    return $retval;
}

/**
 * Returns true if the plugin supports webservices
 *
 * @param   string  $type   The plugin type that is to be checked
 * @return  boolean         true: enabled, false: disabled
 */
function PLG_wsEnabled($type)
{
    global $_CONF;

    $function = 'plugin_wsEnabled_' . $type;
    if (function_exists($function)) {
        return $function();
    } else {
        return false;
    }
}

/**
* Forward the user depending on config setting after saving something
*
* @param  string  $item_url   the url of the item saved
* @param  string  $plugin     the name of the plugin that saved the item
* @return string              the url where the user will be forwarded to
*
*/
function PLG_afterSaveSwitch($target, $item_url, $plugin, $message = '')
{
    global $_CONF;

    if (isset($message) && (!empty($message) || is_numeric($message))) {
        $msg = "msg=$message";
    } else {
        $msg = '';
    }

    switch ($target) {
    case 'item':
        $url = $item_url;
        if (!empty($msg) && ($plugin != 'story')) {
            if (strpos($url, '?') === false) {
                $url .= '?' . $msg;
            } else {
                $url .= '&amp;' . $msg;
            }
        }
        break;

    case 'home':
        $url = $_CONF['site_url'] . '/index.php';
        if (!empty($msg)) {
            $url .= '?' . $msg;
            if (($plugin != 'story') && ($plugin != 'user')) {
                $url .= '&amp;plugin=' . $plugin;
            }
        }
        break;

    case 'admin':
        $url = $_CONF['site_admin_url'] . '/index.php';
        if (!empty($msg)) {
            $url .= '?' . $msg;
            if (($plugin != 'story') && ($plugin != 'user')) {
                $url .= '&amp;plugin=' . $plugin;
            }
        }
        break;

    case 'plugin':
        $url = $_CONF['site_url'] . "/$plugin/index.php";
        if (!empty($msg)) {
            $url .= '?' . $msg;
        }
        break;

    case 'list':
    default:
        if ($plugin == 'story') {
            $url = $_CONF['site_admin_url'] . "/$plugin.php";
        } elseif ($plugin == 'user') {
            $url = $_CONF['site_admin_url'] . "/user.php";
        } else {
            $url = $_CONF['site_admin_url'] . "/plugins/$plugin/index.php";
        }
        if (!empty($msg)) {
            $url .= '?' . $msg;
        }
        break;
    }

    return COM_refresh($url);
}

/**
* Ask plugin for the URL to its configuration help
*
* @param    string  $option  plugin name
* @param    string  $doclang the current language
* @return   array
* @since    glFusion v1.1.6
*
*/
function PLG_getConfigElementHelp($type, $option, $doclang = 'english' )
{
    $args[1] = $option;
    $args[2] = $doclang;
    $function = 'plugin_getconfigelementhelp_' . $type;

    $retval = array();

    $retval = PLG_callFunctionForOnePlugin($function,$args);
    if ( $retval === false ) {
        return array('',0);
    } else {
        return $retval;
    }
}

/**
* An item has been rated, allow plugin to update their records
*
* @param    string  $plugin  plugin name
* @param    string  $id_sent the id of the item rated
* @param    float   $new_rating  the rating value for the item
* @param    int     $votes       The number of votes
* @return   void
* @since    glFusion v1.1.7
*
*/
function PLG_itemRated( $plugin, $id_sent, $new_rating, $added )
{
    global $_CONF, $_TABLES;

    $retval = true;

    $db = Database::getInstance();

    if ( $plugin == 'article' ) {
        $db->conn->update(
            $_TABLES['stories'],
            array(
                'rating' => $new_rating,
                'votes'  => $added
            ),
            array(
                'sid' => $id_sent
            ),
            array(
                Database::INTEGER,
                Database::INTEGER,
                Database::STRING
            )
        );
    } else {
        $args[1] = $id_sent;
        $args[2] = $new_rating;
        $args[3] = $added;
        $function = 'plugin_itemrated_' . $plugin;

        $retval = PLG_callFunctionForOnePlugin($function,$args);
    }

    return $retval;
}

function PLG_canUserRate( $type, $item_id, $uid )
{
   global $_CONF, $_TABLES;

    $retval = false;

    $args[1] = $item_id;
    $args[2] = $uid;
    $function = 'plugin_canuserrate_' . $type;

    $retval = PLG_callFunctionForOnePlugin($function,$args);

    return $retval;
}

// return the path to a plugin's templates

function PLG_templatePath($plugin, $path = '')
{
    global $_CONF, $_TABLES;

    $fn = 'plugin_templatePath_' . $plugin;
    if (function_exists($fn)) {
        $args = (!empty($path)) ? array($path) : array();
        return PLG_callFunctionForOnePlugin($fn, $args);
    } else {
        $layout_path = $_CONF['path'] . 'plugins/' . $plugin . 'templates';
        $layout_path .= (empty($path)) ? '/' . $path : '';
    }
    return $layout_path;
}


/**
* Subscribe user to notification feed for an item
*
* @param    string  $type     plugin name or comment
* @param    string  $category category type (i.e.; for comment it would contain
                              filemgmt, article, etc.)
* @param    string  $id       item id to subscribe to (i.e.; article sid, mg album)
* @param    int     $uid      user to subscribe
* @param    string  $cat_desc Text description of category i.e.; forum cat title
* @param    string  $id_desc  Text description of id, i.e.; article title
* @return   boolean           true on succes, false on fail
* @since    glFusion v1.3.0
*
*/
function PLG_subscribe($type,$category,$id,$uid = 0,$cat_desc='',$id_desc='')
{
    global $_CONF, $_TABLES, $_USER;

    $db = Database::getInstance();

    $dt = new Date('now',$_USER['tzid']);
    if ( $uid == 0 ) {
        if ( !COM_isAnonUser() ) {
            $uid = $_USER['uid'];
        } else {
            return false;
        }
    }
    // check to ensure we don't have a subscription yet...
    $wid = $db->getItem(
        $_TABLES['subscriptions'],
        'sub_id',
        array(
            'category'  => $category,
            'uid'       => $uid,
            'id'        => $id
        ),
        array(
            Database::STRING,
            Database::INTEGER,
            Database::STRING
        )
    );

    if ($wid > 0) {
        return false;
    }

    $db->conn->insert(
        $_TABLES['subscriptions'],
        array(
            'type'  => $type,
            'uid'   => $uid,
            'category' => $category,
            'id'    => $id,
            'date_added' => $dt->toMySQL(true),
            'category_desc' => $cat_desc,
            'id_desc' => $id_desc
        ),
        array(
            Database::STRING,
            Database::INTEGER,
            Database::STRING,
            Database::STRING,
            Database::STRING,
            Database::STRING,
            Database::STRING
        )
    );

    return true;
}


/**
* Unsubscribe user to notification feed for an item
*
* @param    string  $type     plugin name or comment
* @param    string  $category category type (i.e.; for comment it would contain
                              filemgmt, article, etc.)
* @param    string  $id       item id to subscribe to (i.e.; article sid, mg album)
* @param    int     $uid      user to subscribe
* @return   boolean           true on succes, false on fail
* @since    glFusion v1.3.0
*
*/
function PLG_unsubscribe($type,$category,$id,$uid = 0)
{
    global $_CONF, $_TABLES, $_USER;

    if ( $uid == 0 || $uid == '' ) {
        if ( isset($_USER['uid']) ) {
            $uid = $_USER['uid'];
        } else {
            return false;
        }
    }

    $db = Database::getInstance();

    $db->conn->delete(
        $_TABLES['subscriptions'],
        array(
            'uid' => $uid,
            'category' => $category,
            'id' => $id,
            'type' => $type
        ),
        array(
            Database::INTEGER,
            Database::STRING,
            Database::STRING,
            Database::STRING,
        )
    );

    return true;
}


/**
* Check if user is subscribed
*
* @param    string  $id       item id to subscribe to (i.e.; article sid, mg album)
* @param    string  $type     plugin name or comment
* @param    int     $uid      user to subscribe
* @return   boolean           true on succes, false on fail
* @since    glFusion v1.3.0
*
*/
function PLG_isSubscribed( $type, $category, $id, $uid = 0 )
{
    global $_TABLES, $_USER;

    if ( $uid == 0 || $uid == '' ) {
        if ( isset($_USER['uid']) ) {
            $uid = $_USER['uid'];
        } else {
            return false;
        }
    }

    $db = Database::getInstance();

    $count = $db->conn->fetchColumn(
            "SELECT COUNT(*) FROM `{$_TABLES['subscriptions']}`
                WHERE uid=? AND id=? and type=? and category=?",
            array(
                $uid,
                $id,
                $type,
                $category
            ),
            0,
            array(
                Database::INTEGER,
                Database::STRING,
                Database::STRING,
                Database::STRING,
            )
    );
    if ( $count !== false && $count > 0 ) {
        return true;
    }
    return false;
}


/**
* Send new item notification emails
*
* @param    string  $type     plugin name or comment
* @param    string  $category category type (i.e.; for comment it would contain
                              filemgmt, article, etc.)
* @param    string  $track_id id of item being tracked
* @param    string  $post_id  id of new item posted (i.e.; comment id, media_id, etc.)
* @param    int     $post_uid user who posted item
* @return   boolean           true on succes, false on fail
* @since    glFusion v1.3.0
*
*/
function PLG_sendSubscriptionNotification($type,$category,$track_id,$post_id,$post_uid)
{
    global $_CONF, $_TABLES, $LANG04;

    $function = 'plugin_subscription_email_format_' . $type;
    if ( function_exists($function) ) {
        $args[1] = $category;
        $args[2] = $track_id;
        $args[3] = $post_id;
        $args[4] = $post_uid;

        // modify plugin_subscription_email_format to return array

        $htmlmsg = '';
        $textmsg = '';
        $imageData = array();

        $msgData = PLG_callFunctionForOnePlugin($function,$args);
        if (isset($msgData['msgtext'])) {
            $textmsg = $msgData['msgtext'];
        }
        if (isset($msgData['msghtml'])) {
            $htmlmsg = $msgData['msghtml'];
        }
        if ( isset($msgData['imagedata'])) {
            $imageData = $msgData['imagedata'];
        }
        if ( isset($msgData['subject'])) {
            $subject = $msgData['subject'];
        } else {
            $subject = $LANG04[184];
        }
    } else {
        Log::write('system',Log::WARNING,"PLG_sendSubscriptionNotification() - No plugin_subscription_email_format_ defined");
        return false;
    }

    $db = Database::getInstance();

    if ( $track_id == 0 ) {
        $sql    = "SELECT s.uid,email,id
                    FROM `{$_TABLES['subscriptions']}` AS s
                      LEFT JOIN `{$_TABLES['users']}` AS u ON s.uid=u.uid
                   WHERE u.status=?
                         AND category=?
                         AND type=?";

        $stmt = $db->conn->prepare($sql);
        $stmt->bindValue(1,USER_ACCOUNT_ACTIVE,Database::INTEGER);
        $stmt->bindValue(2,$category,Database::STRING);
        $stmt->bindValue(3,$type,Database::STRING);

    } else {
        $sql    = "SELECT s.uid,email,id
                    FROM `{$_TABLES['subscriptions']}` AS s
                      LEFT JOIN `{$_TABLES['users']}` AS u ON s.uid=u.uid
                   WHERE u.status=?
                         AND category=?
                         AND id=?
                         AND type=?";
        $stmt = $db->conn->prepare($sql);
        $stmt->bindValue(1,USER_ACCOUNT_ACTIVE,Database::INTEGER);
        $stmt->bindValue(2,$category,Database::STRING);
        $stmt->bindValue(3,$track_id,Database::INTEGER);
        $stmt->bindValue(4,$type,Database::STRING);
    }
    $stmt->execute();

    $messageData = array();
    $messageData['subject'] = $subject;
    $messageData['from']    = $_CONF['noreply_mail'];
    $messageData['htmlmessage'] = $htmlmsg;
    $messageData['textmessage'] = $textmsg;
    if ( is_array($imageData) && count($imageData) > 0 ) {
        $messageData['embeddedImage'] = $imageData;
    }

    $to = array();

    while($S = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ( $S['uid'] == $post_uid ) {  // skip author
            continue;
        }
        if ( $S['id'] < 0 ) {   // allows exclude records...
            continue;
        }
        $to[] = $S['email'];
    }
    $messageData['to'] = $to;
    COM_emailNotification($messageData);
    return true;
}

/**
* Remove the file structure(s) associated with a plugin
*
* @param    string  $type     plugin name
* @return   boolean           true on success, false on fail
* @since    glFusion v1.3.0
*
*/

function PLG_remove($pi_name)
{
    global $_CONF;

    $p = array();

    $filter = new \sanitizer();

    $pi_name = $filter->sanitizeFilename($pi_name, false);

    $p['admin'] = $_CONF['path_admin'] . 'plugins/' . $pi_name;
    $p['public'] = $_CONF['path_html'] . $pi_name;
    $p['private'] =  $_CONF['path'] . 'plugins/' . $pi_name;

    foreach($p as $location => $path ) {
        if (is_dir($path)) {
            Log::write('system',Log::INFO,"Removing {$location} files ...");
            if (! \glFusion\FileSystem::deleteDir($path)) {
                return false;
            }
        }
    }

    return true;
}


/**
* Returns the WYSIWYG editor type
*
* @return   string            name of wysiwyg editor or blank if none configured
* @since    glFusion v1.4.0
*
*/
function PLG_getEditorType()
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getEditorType_' . $pi_name;
        if (function_exists($function)) {
            return $function ();
        }
    }
    return '';
}

/**
* Register a plugin's template for the wysiwyg editor
*
* @param    string  $plugin   plugin name
* @param    string  $feature  function / feature (i.e.; story_edit)
* @param    string  $template handle to template
* @return   boolean          true if processed / false if no editor available.
* @since    glFusion v1.4.0
*
*/
function PLG_requestEditor($plugin, $feature, $template)
{
    global $_PLUGINS;

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_requestEditor_' . $pi_name;
        if (function_exists($function)) {
            return $function ($plugin, $feature, $template);
        }
    }
    return false;
}

function PLG_supportAdBlock()
{
    global $_PLUGINS;

    $retval = array();

    $retval[] = 'article';
    $retval[] = 'header';
    $retval[] = 'footer';

    if ( is_array($_PLUGINS) ) {
        foreach ($_PLUGINS as $pi_name) {
            $function = 'plugin_supportadblock_' . $pi_name;
            if (function_exists ($function)) {
                $rc = $function ();
                if ( is_array($rc) ) {
                    foreach ($rc AS $item) {
                        $retval[] = $item;
                    }
                } elseif ( $rc == true ) {
                    $retval[] = $pi_name;
                }
            }
        }
    }
    return $retval;
}


function PLG_displayAdBlock($plugin, $counter)
{
    global $_PLUGINS;

    $retval = '';

    if (function_exists ('CUSTOM_displayAdBlock')) {
        $retval = CUSTOM_displayAdBlock($plugin, $counter);
    } elseif ( is_array($_PLUGINS) ) {
        foreach ($_PLUGINS as $pi_name) {
            $function = 'plugin_displayAdblock_' . $pi_name;
            if (function_exists ($function)) {
                $retval .= $function ($plugin,$counter);
            }
        }
    }
    return $retval;
}

/**
* Allow plugins to add JavaScript to execute on page load for infinite scroll
*
* @return   string  JavaScript to include in afterPageLoad function
* @since    glFusion v1.6.6
*
*/
function PLG_isOnPageLoad()
{
    global $_PLUGINS;

    $retval = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_isOnPageLoad_' . $pi_name;
        if (function_exists($function)) {
            $retval .= $function ();
        }
    }
    return $retval;
}

/**
* Allow a plugin to override glFusion's social share icons
*
* @param    string  $type    plugin name or article
* @param    string  $title   title of item to share
* @param    string  $url     permalink URL for item to share
* @param    string  $desc    description of item to share
* @return   string           HTML of the social share icons
* @since    glFusion v1.6.6
*
*/
function PLG_replaceSocialShare($type='article',$title='',$url='',$desc='')
{
    global $_PLUGINS;

    $retval = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_social_share_replacement_' . $pi_name;
        if (function_exists($function)) {
            $retval .= $function ($type,$title,$url,$desc);
            break; //first one wins!
        }
    }
    return $retval;
}

function PLG_overrideSocialShare()
{
    global $_PLUGINS;

    $retval = '';

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_social_share_override_' . $pi_name;
        if (function_exists($function)) {
            $retval = $function ();
            if ( $retval != '' ) return $retval;
        }
    }
    return false;
}


/**
* This functions allows a plugin to override a glFusion service
*
* @param    char     $service    Service to Override
* @param    char     $class      Classname for plugin
* @return   void
*
*/
function PLG_registerService($service, $class )
{
    global $_VARS;

    $_VARS['service_'.$service] = $class;
}

/**
* This functions allows a plugin filter / modify output prior to displaying
*
* @param    char     $output       output to display
* @param    char     $type         content type
* @return   char     output to display
*
*/
function PLG_outputFilter($output, $type='')
{
    global $_PLUGINS;

    if (function_exists ('CUSTOM_outputFilter')) {
        $output = CUSTOM_outputFilter($output, $type);
    }

    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_outputfilter_' . $pi_name;
        if (function_exists($function)) {
            $output = $function ($output, $type);
        }
    }
    return $output;
}


/**
* Privacy Hooks
*
* @param    int      $uid       User id
* @param    char     $email     email address
* @param    char     $username  username
* @param    char     $ip        ip address
* @return   char     XML formatted ata
*
*/
function PLG_privacyExport($uid=0,$email='',$username='',$ip='')
{
    global $_PLUGINS;

    USES_lib_comment();

    $output = '';

    $internalContent = array('social','article');

    $contentTypes = array_merge($internalContent, $_PLUGINS);

    foreach ($contentTypes as $pi_name) {
        $function = 'plugin_privacy_export_' . $pi_name;
        if (function_exists($function)) {
            $output .= $function ($uid,$email,$username,$ip);
        }
    }
    return $output;
}


/**
* Get Plugin Content Info
*
*   plugins should return an array of the following:
*
* array(
*   array(
*       'table_name' => 'gl_stories',
*       'primary_key' => 'id',
*       'columns'    => array(
*           'title',
*           'subtitle',
*           'introtext',
*           'bodytext'
*       )
*   )
* );
*
*
* @return   array()
*
*/
function PLG_getContentTableInfo()
{
    global $_PLUGINS;

    $itemList = array();
    $totalItemList = array();

    foreach ($_PLUGINS AS $plugin) {
        $function = 'plugin_getContentTableInfo_'.$plugin;
        if (function_exists($function)) {
            $itemList = $function();
            if (is_array($itemList) && count($itemList) > 0) {
                $totalItemList = array_merge($itemList,$totalItemList);
            }
        }
    }
    return $totalItemList;
}

/*
 * Plugin APIs Stubs
 */
function plugin_privacy_export_rating($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $db = Database::getInstance();

    $exportFields = array('type','item_id','rating','uid','ip_address','ratingdate');

    $sql = "SELECT * FROM `{$_TABLES['rating_votes']}` WHERE uid = ?";
    if (!empty($ip)) {
        $sql .= " OR ip_address = ?";
    }
    $sql .= " ORDER BY ratingdate ASC";
    $stmt = $db->conn->prepare($sql);
    $stmt->bindValue(1,$uid,Database::INTEGER);
    if (!empty($ip)) {
        $stmt->bindValue(2,$ip,Database::STRING);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(Database::ASSOCIATIVE);

    $retval .= "<ratings>\n";

    foreach($rows AS $row) {
        $retval .= "<rating>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {

                if ( $item == 'ratingdate' ) {
                    $dt = new Date($value,$_USER['tzid']);
                    $value = $dt->format($dt->getUserFormat(),true);
                }

                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</rating>\n";
    }
    $retval .= "</ratings>\n";

    if ( function_exists('tidy_repair_string')) {
        $retval = tidy_repair_string($retval, array('input-xml' => 1));
    }

    return $retval;

}

function plugin_privacy_export_social($uid,$email='',$username='',$ip='')
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $exportFields = array('social_service','ss_username','rating','uid','ip_address','ratingdate');

    $db = Database::getInstance();

    $sql = "SELECT *, ss.display_name AS social_service
             FROM `{$_TABLES['social_follow_user']}` AS su
             LEFT JOIN `{$_TABLES['social_follow_services']}` AS ss
             ON su.ssid=ss.ssid
             WHERE uid = ?";

    $stmt = $db->conn->executeQuery(
                $sql,
                array($uid),
                array(Database::INTEGER)
    );
    $rows = $stmt->fetchAll(Database::ASSOCIATIVE);

    $retval .= "<socialservices>\n";

    foreach($rows AS $row) {
        $retval .= "<service>\n";
        foreach($row AS $item => $value) {
            if ( in_array($item,$exportFields) && $item != '0') {

                $retval .= '<'.$item.'>'.addSlashes(htmlentities($value)).'</'.$item.">\n";
            }
        }
        $retval .= "</service>\n";
    }
    $retval .= "</socialservices>\n";

    if ( function_exists('tidy_repair_string')) {
        $retval = tidy_repair_string($retval, array('input-xml' => 1));
    }

    return $retval;
}

/**
*
* Checks that the current user has the rights to moderate a comment
* returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_comment()
{
    return SEC_hasRights('comment.moderate');
}

?>
