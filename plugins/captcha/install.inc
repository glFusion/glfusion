<?php
// +---------------------------------------------------------------------------+
// | CAPTCHA v3 Plugin                                                         |
// +---------------------------------------------------------------------------+
// | $Id:: install.php 1294 2007-09-30 21:00:59Z mevans0263                   $|
// | install.php                                                               |
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
if (strpos ($_SERVER['PHP_SELF'], 'install.inc') !== false)
{
    die ('This file can not be used on its own.');
}

function plugin_install_captcha($_DB_table_prefix)
{
    global $_TABLES, $_CONF, $LANG_CP00, $_DB_dbms;
    global $_CP_CONF;

    require_once($_CONF['path'] . '/plugins/captcha/config.php');
    require_once($_CONF['path'] . '/plugins/captcha/functions.inc');

    $pi_version = $_CP_CONF['version'];
    $pi_name    = $_CP_CONF['pi_name'];
    $gl_version = $_CP_CONF['gl_version'];
    $pi_url     = 'http://www.gllabs.org';  // Plugin Homepage

    $_SQL['cp_config'] =
        "CREATE TABLE {$_TABLES['cp_config']} ( " .
        "  `config_name` varchar(255) NOT NULL default '', " .
        "  `config_value` varchar(255) NOT NULL default '', " .
        "   PRIMARY KEY  (`config_name`) " .
        " );";

    if ( $_DB_dbms == 'mysql' ) {
        $_SQL['cp_sessions'] =
            "CREATE TABLE {$_TABLES['cp_sessions']} ( " .
            "  `session_id` varchar(40) NOT NULL default '', " .
            "  `cptime`  INT(11) NOT NULL default 0, " .
            "  `validation` varchar(40) NOT NULL default '', " .
            "  `counter`    TINYINT(4) NOT NULL default 0, " .
            "  PRIMARY KEY (`session_id`) " .
            " );";
    } else {
            "CREATE TABLE {$_TABLES['cp_sessions']} ( " .
            "  `session_id` varchar(40) NOT NULL default '', " .
            "  `cptime`  INT NOT NULL default 0, " .
            "  `validation` varchar(40) NOT NULL default '', " .
            "  `counter`    TINYINT NOT NULL default 0, " .
            "  PRIMARY KEY (`session_id`) " .
            " );";
    }
    foreach ($_SQL as $table => $sql) {
        DB_query($sql,1);
        if (DB_error()) {
            plugin_uninstall_captcha();
            return false;
            exit;
        }
    }

    $SQL_DEFAULTS = "INSERT INTO `{$_TABLES['cp_config']}` (`config_name`, `config_value`) VALUES " .
                    " ('anonymous_only', '1'), " .
                    " ('remoteusers','0'), " .
                    " ('debug', '0'), " .
                    " ('enable_comment', '1'), " .
                    " ('enable_contact', '1'), " .
                    " ('enable_emailstory', '1'), " .
                    " ('enable_forum', '1'), " .
                    " ('enable_registration', '1'), " .
                    " ('enable_mediagallery', '1'), " .
                    " ('enable_rating', '1'), " .
                    " ('enable_story', '1'), " .
                    " ('gfxDriver', '2'), " .
                    " ('gfxFormat', 'jpg'), " .
                    " ('imageset','default'), " .
                    " ('logging', '0'), " .
                    " ('gfxPath', '');";

    DB_query($SQL_DEFAULTS,1);

    // Register the plugin with Geeklog
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
?>