<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | install.inc                                                              |
// |                                                                          |
// | Installation routines                                                    |
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
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

$pi_name        = 'captcha';
$pi_version     = '3.1.0';
$gl_version     = '1.1.0';
$pi_url         = 'http://www.glfusion.org';
$base_path      = $_CONF['path'] . 'plugins/' . $pi_name . '/';

/*
 * If we are being called glFusion's install routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLOG') ) {
    function COM_errorLOG() {
    }
}

/**
* Checks the requirements for this plugin and if it is compatible with this
* version of glFusion.
*
* @return   boolean     true = proceed with install, false = not compatible
*
*/
function captcha_compatible_with_this_glfusion_version ()
{
    // requires 1.1.0 or newer
    $glversion = explode(".", GVERSION);
    if ( $glversion[0] < 1 ) {
        return false;
    }
    if ( $glversion[1] < 1 ) {
        return false;
    }
    return true;
}

/**
* Loads the configuration records for the GL Online Config Manager
*
* @return   boolean     true = proceed with install, false = an error occured
*
*/
function captcha_load_configuration()
{
    global $_CONF, $base_path;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $base_path . 'install_defaults.php';

    return plugin_initconfig_captcha();
}


function plugin_install_captcha($_DB_table_prefix)
{
    global $_TABLES, $_CONF;
    global $pi_name, $pi_version, $gl_version, $pi_url, $base_path;

    require_once($_CONF['path'] . '/plugins/captcha/functions.inc');

    $_SQL['cp_sessions'] =
        "CREATE TABLE {$_TABLES['cp_sessions']} ( " .
        "  `session_id` varchar(40) NOT NULL default '', " .
        "  `cptime`  INT(11) NOT NULL default 0, " .
        "  `validation` varchar(40) NOT NULL default '', " .
        "  `counter`    TINYINT(4) NOT NULL default 0, " .
        "  PRIMARY KEY (`session_id`) " .
        " );";

    foreach ($_SQL as $table => $sql) {
        DB_query($sql,1);
        if (DB_error()) {
            plugin_remove_captcha();
            return false;
            exit;
        }
    }

    // Load the online configuration records
    if (function_exists('captcha_load_configuration')) {
        if (!captcha_load_configuration()) {
            plugin_remove_captcha();
            return false;
        }
    }

    // Register the plugin with glFusion
    DB_delete($_TABLES['plugins'],'pi_name','captcha');
    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
        . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

    if (DB_error()) {
        plugin_uninstall_captcha();
        return false;
        exit;
    }

    // Create initial log entry
    return true;
}


function plugin_remove_captcha() {
    global $_DB_table_prefix, $_TABLES;

    $pi_name    = 'captcha';

    DB_query("DROP TABLE " . $_DB_table_prefix . 'cp_sessions',1);

    // Ok to proceed and delete plugin

    // Unregister the plugin with glFusion
    COM_errorLog('Attempting to unregister the CAPTCHA Plugin from glFusion',1);
    DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE group_name='captcha'");
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'captcha'",1);

    COM_errorLog('...success',1);
    return true;
}
?>