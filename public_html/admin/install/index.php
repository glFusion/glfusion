<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | glFusion installation script.                                            |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric Warren            eric AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Randy Kolenko     - randy AT nextide DOT ca                     |
// |          Matt West         - matt AT mattdanger DOT net                  |
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

// this should help expose parse errors even when
// display_errors is set to Off in php.ini
if (function_exists ('ini_set')) {
    ini_set ('display_errors', '1');
}
error_reporting (E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

if (!defined ("LB")) {
    define("LB", "\n");
}
if (!defined ('GVERSION')) {
    define('GVERSION', '1.1.0');
}

/**
 * Returns the PHP version
 *
 * Note: Removes appendices like 'rc1', etc.
 *
 * @return array the 3 separate parts of the PHP version number
 *
 */
function php_v ()
{
    $phpv = explode ('.', phpversion ());
    return array ($phpv[0], $phpv[1], (int) $phpv[2]);
}


/**
 * Returns the MySQL version
 *
 * @return  mixed   array[0..2] of the parts of the version number or false
 *
 */
function mysql_v($_DB_host, $_DB_user, $_DB_pass)
{
    if (@mysql_connect($_DB_host, $_DB_user, $_DB_pass) === false) {
        return false;
    }
    $mysqlv = '';

    // mysql_get_server_info() is only available as of PHP 4.0.5
    $phpv = php_v ();
    if (($phpv[0] > 4) || (($phpv[0] == 4) && ($phpv[1] > 0)) ||
        (($phpv[0] == 4) && ($phpv[1] == 0) && ($phpv[2] > 4))) {
        $mysqlv = @mysql_get_server_info();
    }

    if (!empty ($mysqlv)) {
        preg_match ('/^([0-9]+).([0-9]+).([0-9]+)/', $mysqlv, $match);
        $mysqlmajorv = $match[1];
        $mysqlminorv = $match[2];
        $mysqlrev = $match[3];
    } else {
        $mysqlmajorv = 0;
        $mysqlminorv = 0;
        $mysqlrev = 0;
    }
    @mysql_close ();

    return array ($mysqlmajorv, $mysqlminorv, $mysqlrev);
}

/**
 * Check if we can skip upgrade steps (post-1.5.0)
 *
 * If we're doing an upgrade from 1.5.0 or later and we have the necessary
 * DB credentials, skip the forms and upgrade directly.
 *
 * @param   string  $dbconfig_path      path to db-config.php
 * @param   string  $siteconfig_path    path to siteconfig.php
 * @return  string                      database version, if possible
 * @note    Will not return if upgrading from 1.5.0 or later.
 *
 */
function INST_checkOKtoUpgrade($dbconfig_path, $siteconfig_path)
{
    global $_CONF, $_TABLES, $_DB, $_DB_dbms, $_DB_host, $_DB_user, $_DB_pass;

    require $dbconfig_path;
    require $siteconfig_path;

    $connected = false;
    $version = '';

    $db_handle = @mysql_connect($_DB_host, $_DB_user, $_DB_pass);
    if ($db_handle) {
        $connected = @mysql_select_db($_DB_name, $db_handle);
    }

    if ($connected) {
        require $_CONF['path_system'] . 'lib-database.php';

        $version = INST_identifyglFusionVersion();

        @mysql_close($db_handle);

        if (!empty($version) && ($version != GVERSION) &&
                (substr($version, 0, 4) == '1.0.')) {

            // this is a 1.0.x version, so upgrade directly
            $req_string = 'index.php?mode=upgrade&step=3'
                        . '&dbconfig_path=' . $dbconfig_path
                        . '&version=' . $version;

            header('Location: ' . $req_string);
            exit;
        }
    }

    return $version;
}

/*
 * Installer engine
 *
 * The guts of the installation and upgrade package.
 *
 * @param   string  $install_type   'install' or 'upgrade'
 * @param   int     $install_step   1 - 3
 */
function INST_installEngine($install_type, $install_step)
{
    global $_CONF, $LANG_INSTALL, $LANG_CHARSET, $_DB, $_TABLES, $gl_path, $html_path, $dbconfig_path, $siteconfig_path, $display, $language, $label_dir;

    switch ($install_step) {

        /**
         * Page 1 - Enter glFusion config information
         */
        case 1:
            require_once $dbconfig_path; // Get the current DB info

            if ($install_type == 'upgrade') {
                $v = INST_checkOKtoUpgrade($dbconfig_path, $siteconfig_path);

                if ($v == GVERSION) {
                    // looks like we're already up to date
                    $display .= '<h2>' . $LANG_INSTALL[74] . '</h2>' . LB
                             . '<p>' . $LANG_INSTALL[75] . '</p>';
                    return;
                }
            }

            $glSite_name = $LANG_INSTALL[29];
            $glSite_slogan = $LANG_INSTALL[30];
            $glSite_url = 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/\/admin.*/', '', $_SERVER['PHP_SELF']) ;
            $glSite_admin_url = 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/\/install.*/', '', $_SERVER['PHP_SELF']) ;
            $host_name = explode(':', $_SERVER['HTTP_HOST']);
            $host_name = $host_name[0];
            $glSite_mail = ($_CONF['site_mail'] == 'admin@example.com' ? $_CONF['site_mail'] : 'admin@' . $host_name);
            $utf8 = true;

            // Set all the form values either with their defaults or with received POST data.
            // The only instance where you'd get POST data would be if the user has to
            // go back because they entered incorrect database information.
            $site_name = (isset($_POST['site_name'])) ? str_replace('\\', '', $_POST['site_name']) : $glSite_name;
            $site_slogan = (isset($_POST['site_slogan'])) ? str_replace('\\', '', $_POST['site_slogan']) : $glSite_slogan;
            $mysql_innodb_selected = '';
            $mysql_selected = '';
            $mssql_selected = '';
            if (isset($_POST['db_type'])) {
                switch ($_POST['db_type']) {
                    case 'mysql-innodb':
                        $mysql_innodb_selected = ' selected="selected"';
                        break;
                    default:
                        $mysql_selected = ' selected="selected"';
                        break;
                }
            } else {
                $mysql_selected = ' selected="selected"';
            }
            $db_host = isset($_POST['db_host']) ? $_POST['db_host'] : $_DB_host;
            $db_name = isset($_POST['db_name']) ? $_POST['db_name'] : $_DB_name;
            $db_user = isset($_POST['db_user']) ? $_POST['db_user'] : ($_DB_user != 'username' ? $_DB_user : '');
            $db_pass = isset($_POST['db_pass']) ? $_POST['db_pass'] : ($_DB_pass != 'password' ? $_DB_pass : '');
            $db_prefix = isset($_POST['db_prefix']) ? $_POST['db_prefix'] : $_DB_table_prefix;

            $site_url = isset($_POST['site_url']) ? $_POST['site_url'] : $glSite_url; // 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/\/admin.*/', '', $_SERVER['PHP_SELF']) ;
            $site_admin_url = isset($_POST['site_admin_url']) ? $_POST['site_admin_url'] : $glSite_admin_url; //'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/\/install.*/', '', $_SERVER['PHP_SELF']) ;
            $host_name = explode(':', $_SERVER['HTTP_HOST']);
            $host_name = $host_name[0];
            $site_mail = isset($_POST['site_mail']) ? $_POST['site_mail'] : $glSite_mail; //($_CONF['site_mail'] == 'admin@example.com' ? $_CONF['site_mail'] : 'admin@' . $host_name);
            $noreply_mail = isset($_POST['noreply_mail']) ? $_POST['noreply_mail'] : ($_CONF['noreply_mail'] == 'noreply@example.com' ? $_CONF['noreply_mail'] : 'noreply@' . $host_name);

            if (isset($_POST['utf8']) && ($_POST['utf8'] == 'on')) {
                $utf8 = true;
            } else {
                $utf8 = true;
                if (strcasecmp($LANG_CHARSET, 'utf-8') == 0) {
                    $utf8 = true;
                }
            }

            $buttontext = $LANG_INSTALL[50];
            $innodbnote = '<small>' . $LANG_INSTALL[38] . '</small>';

            $display .= '
                <h2>' . $LANG_INSTALL[31] . '</h2>
                <div class="glform">
                <form action="index.php" method="post">
                <input type="hidden" name="mode" value="' . $install_type . '" />
                <input type="hidden" name="step" value="2" />
                <input type="hidden" name="language" value="' . $language . '" />
                <input type="hidden" name="dbconfig_path" value="' . $dbconfig_path . '" />
                <br />
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[32] . ' ' . INST_helpLink('site_name') . '</label> <input type="text" name="site_name" value="' . $site_name . '" size="40" /></p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[33] . ' ' . INST_helpLink('site_slogan') . '</label> <input type="text" name="site_slogan" value="' . $site_slogan . '" size="40" /></p><br />
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[34] . ' ' . INST_helpLink('db_type') . '</label> <select name="db_type">
                    <option value="mysql"' . $mysql_selected . '>' . $LANG_INSTALL[35] . '</option>
                    ' . ($install_type == 'install' ? '<option value="mysql-innodb"' . $mysql_innodb_selected . '>' . $LANG_INSTALL[36] . '</option>' : '') . '
                    </select> ' . $innodbnote . '</p><br />
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[39] . ' ' . INST_helpLink('db_host') . '</label> <input type="text" name="db_host" value="'. $db_host .'" size="20" /></p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[40] . ' ' . INST_helpLink('db_name') . '</label> <input type="text" name="db_name" value="'. $db_name . '" size="20" /></p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[41] . ' ' . INST_helpLink('db_user') . '</label> <input type="text" name="db_user" value="' . $db_user . '" size="20" /></p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[42] . ' ' . INST_helpLink('db_pass') . '</label> <input type="password" name="db_pass" value="" size="20" /></p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[43] . ' ' . INST_helpLink('db_prefix') . '</label> <input type="text" name="db_prefix" value="' . $db_prefix . '" size="20" /></p>

                <br />
                <h2>' . $LANG_INSTALL[44] . '</h2>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[45] . ' ' . INST_helpLink('site_url') . '</label> <input type="text" name="site_url" value="' . $site_url . '" size="50" />  &nbsp; ' . $LANG_INSTALL[46] . '</p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[47] . ' ' . INST_helpLink('site_admin_url') . '</label> <input type="text" name="site_admin_url" value="' . $site_admin_url . '" size="50" />  &nbsp; ' . $LANG_INSTALL[46] . '</p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[48] . ' ' . INST_helpLink('site_mail') . '</label> <input type="text" name="site_mail" value="' . $site_mail . '" size="50" /></p>
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[49] . ' ' . INST_helpLink('noreply_mail') . '</label> <input type="text" name="noreply_mail" value="' . $noreply_mail . '" size="50" /></p>';

            $display .= '
                <p><label class="' . $label_dir . '">' . $LANG_INSTALL[92] . ' ' . INST_helpLink('utf8') . '</label> <input type="checkbox" name="utf8"' . ($utf8 ? ' checked="checked"' : '') . ' /></p>';

            $display .= '
                <br />
                <input type="submit" name="submit" class="submit" value="' . $buttontext . ' &gt;&gt;" />
                </form></div>' . LB;
            break;

        /**
         * Page 2 - Enter information into db-config.php
         * and ask about InnoDB tables (if supported)
         */
        case 2:
            // Set all the variables from the received POST data.
            $site_name      = $_POST['site_name'];
            $site_slogan    = $_POST['site_slogan'];
            $db_type        = $_POST['db_type'];
            $db_host        = $_POST['db_host'];
            $db_name        = $_POST['db_name'];
            $db_user        = $_POST['db_user'];
            $db_pass        = $_POST['db_pass'];
            $db_prefix      = $_POST['db_prefix'];
            $site_url       = $_POST['site_url'];
            $site_admin_url = $_POST['site_admin_url'];
            $site_mail      = $_POST['site_mail'];
            $noreply_mail   = $_POST['noreply_mail'];
            $utf8 = (isset($_POST['utf8']) && ($_POST['utf8'] == 'on')) ? true : false;

            // If using MySQL check to make sure the version is supported
            $outdated_mysql = false;
            $failed_to_connect = false;
            if ($db_type == 'mysql' || $db_type == 'mysql-innodb') {
                $myv = mysql_v($db_host, $db_user, $db_pass);
                if ($myv === false) {
                    $failed_to_connect = true;
                } elseif (($myv[0] < 3) || (($myv[0] == 3) && ($myv[1] < 23)) ||
                        (($myv[0] == 3) && ($myv[1] == 23) && ($myv[2] < 2))) {
                    $outdated_mysql = true;
                }
            }
            if ($outdated_mysql) { // If MySQL is out of date
                $display .= '<h1>' . $LANG_INSTALL[51] . '</h1>' . LB;
                $display .= '<p>' . $LANG_INSTALL[52] . $myv[0] . '.' . $myv[1] . '.' . $myv[2] . $LANG_INSTALL[53] . '</p>' . LB;
            } elseif ($failed_to_connect) {
                $display .= '<h2>' . $LANG_INSTALL[54] . '</h2><p>'
                         . $LANG_INSTALL[55] . '</p>'
                         . INST_showReturnFormData($_POST) . LB;
            } else {
                // Check if you can connect to database
                $invalid_db_auth = false;
                $db_handle = null;
                $innodb = false;
                switch ($db_type) {
                case 'mysql-innodb':
                    $innodb = true;
                    $db_type = 'mysql';
                    // deliberate fallthrough - no "break"
                case 'mysql':
                    if (!$db_handle = @mysql_connect($db_host, $db_user, $db_pass)) {
                        $invalid_db_auth = true;
                    }
                    break;
                case 'mssql':
                    if (!$db_handle = @mssql_connect($db_host, $db_user, $db_pass)) {
                        $invalid_db_auth = true;
                    }
                    break;
                }
                if ($invalid_db_auth) { // If we can't connect to the database server
                    $display .= '<h2>' . $LANG_INSTALL[54] . '</h2><p>'
                             . $LANG_INSTALL[55] . '</p>'
                             . INST_showReturnFormData($_POST) . LB;
                } else { // If we can connect
                    // Check if the database exists
                    $db_exists = false;
                    switch ($db_type) {
                    case 'mysql':
                        if (@mysql_select_db($db_name, $db_handle)) {
                            $db_exists = true;
                        }
                        break;
                    case 'mssql':
                        if (@mssql_select_db($db_name, $db_handle)) {
                            $db_exists = true;
                        }
                        break;
                    }
                    if (!$db_exists) { // If database doesn't exist
                        $display .= '<h2>' . $LANG_INSTALL[56] . '</h2>
                            <p>' . $LANG_INSTALL[57] . '</p>' . INST_showReturnFormData($_POST) . LB;
                    } else { // If database does exist

                        require_once $dbconfig_path; // Grab the current DB values

                        // Read in db-config.php so we can insert the DB information
                        $dbconfig_file = fopen($dbconfig_path, 'r');
                        $dbconfig_data = fread($dbconfig_file, filesize($dbconfig_path));
                        fclose($dbconfig_file);

                        // Replace the values with the new ones
                        $dbconfig_data = str_replace("\$_DB_host = '" . $_DB_host . "';", "\$_DB_host = '" . $db_host . "';", $dbconfig_data); // Host
                        $dbconfig_data = str_replace("\$_DB_name = '" . $_DB_name . "';", "\$_DB_name = '" . $db_name . "';", $dbconfig_data); // Database
                        $dbconfig_data = str_replace("\$_DB_user = '" . $_DB_user . "';", "\$_DB_user = '" . $db_user . "';", $dbconfig_data); // Username
                        $dbconfig_data = str_replace("\$_DB_pass = '" . $_DB_pass . "';", "\$_DB_pass = '" . $db_pass . "';", $dbconfig_data); // Password
                        $dbconfig_data = str_replace("\$_DB_table_prefix = '" . $_DB_table_prefix . "';", "\$_DB_table_prefix = '" . $db_prefix . "';", $dbconfig_data); // Table prefix
                        $dbconfig_data = str_replace("\$_DB_dbms = '" . $_DB_dbms . "';", "\$_DB_dbms = '" . $db_type . "';", $dbconfig_data); // Database type ('mysql' or 'mssql')

                        // Write our changes to db-config.php
                        $dbconfig_file = fopen($dbconfig_path, 'w');
                        if (!fwrite($dbconfig_file, $dbconfig_data)) {
                            exit($LANG_INSTALL[26] . ' ' . $dbconfig_path
                                 . $LANG_INSTALL[58]);
                        }
                        fclose($dbconfig_file);

                        // for the default charset, patch siteconfig.php again
                        if ($install_type != 'upgrade') {
                            if (!INST_setDefaultCharset($siteconfig_path,
                                    ($utf8 ? 'utf-8' : $LANG_CHARSET))) {
                                exit($LANG_INSTALL[26] . ' ' . $siteconfig_path
                                     . $LANG_INSTALL[58]);
                            }
                        }

                        require $dbconfig_path;
                        require_once $siteconfig_path;
                        require_once $_CONF['path_system'] . 'lib-database.php';
                        $req_string = 'index.php?mode=' . $install_type . '&step=3&dbconfig_path=' . $dbconfig_path
                                    . '&language=' . $language
                                    . '&site_name=' . urlencode($site_name)
                                    . '&site_slogan=' . urlencode($site_slogan)
                                    . '&site_url=' . urlencode($site_url)
                                    . '&site_admin_url=' . urlencode($site_admin_url)
                                    . '&site_mail=' . urlencode($site_mail)
                                    . '&noreply_mail=' . urlencode($noreply_mail);
                        if ($utf8) {
                            $req_string .= '&utf8=true';
                        }

                        $hidden_fields = '<input type="hidden" name="mode" value="' . $install_type . '" />
                                    <input type="hidden" name="language" value="' . $language . '" />
                                    <input type="hidden" name="dbconfig_path" value="' . urlencode($dbconfig_path) . '" />
                                    <input type="hidden" name="site_name" value="' . urlencode($site_name) . '" />
                                    <input type="hidden" name="site_slogan" value="' . urlencode($site_slogan) . '" />
                                    <input type="hidden" name="site_url" value="' . urlencode($site_url) . '" />
                                    <input type="hidden" name="site_admin_url" value="' . urlencode($site_admin_url) . '" />
                                    <input type="hidden" name="site_mail" value="' . urlencode($site_mail) . '" />
                                    <input type="hidden" name="noreply_mail" value="' . urlencode($noreply_mail) . '" />
                                    <input type="hidden" name="utf8" value="' . ($utf8 ? 'true' : 'false') . '" />';

                        // If using MySQL check to see if InnoDB is supported
                        if ($innodb && !INST_innodbSupported()) {
                            // Warn that InnoDB tables are not supported
                            $display .= '<h2>' . $LANG_INSTALL[59] . '</h2>
                            <p>' . $LANG_INSTALL['60'] . '</p>

                            <br />
                            <div style="margin-left: auto; margin-right: auto; width: 125px">
                                <div style="position: relative; right: 10px">
                                    <form action="index.php" method="post">
                                    <input type="hidden" name="step" value="1" />
                                    ' . $hidden_fields . '
                                    <input type="submit" value="&lt;&lt; ' . $LANG_INSTALL[61] . '" />
                                    </form>
                                </div>

                                <div style="position: relative; left: 65px; top: -27px">
                                    <form action="index.php" method="post">
                                    <input type="hidden" name="step" value="3" />
                                    ' . $hidden_fields . '
                                    <input type="hidden" name="innodb" value="false" />
                                    <input type="submit" name="submit" value="' . $LANG_INSTALL[62] . ' &gt;&gt;" />
                                    </form>
                                </div>
                            </div>' . LB;
                        } else {
                            // Continue on to step 3 where the installation will happen
                            if ($innodb) {
                                $req_string .= '&innodb=true';
                            }
                            header('Location: ' . $req_string);
                        }
                    }
                }
            }
            break;

        /**
         * Page 3 - Install
         */
        case 3:
            $gl_path = str_replace('db-config.php', '', $dbconfig_path);
            switch ($install_type) {
                case 'install':
                    if (isset($_POST['submit']) &&
                            ($_POST['submit'] == '<< ' . $LANG_INSTALL[61])) {
                        header('Location: index.php?mode=install');
                    }

                    // Check whether to use InnoDB tables
                    $use_innodb = false;
                    if ((isset($_POST['innodb']) && $_POST['innodb'] == 'true') || (isset($_GET['innodb']) && $_GET['innodb'] == 'true')) {
                        $use_innodb = true;
                    }

                    $utf8 = false;
                    if ((isset($_POST['utf8']) && $_POST['utf8'] == 'true') || (isset($_GET['utf8']) && $_GET['utf8'] == 'true')) {
                        $utf8 = true;
                    }

                    // We need all this just to do one DB query
                    require_once $dbconfig_path;
                    require_once $siteconfig_path;
                    require_once $_CONF['path_system'] . 'lib-database.php';

                    // Check if GL is already installed
                    if (INST_checkTableExists('vars')) {

                        $display .= '<p>' . $LANG_INSTALL[63] . '</p>
                            <ol>
                                <li>' . $LANG_INSTALL[64] . '</li>
                                <li>' . $LANG_INSTALL[65] . '</li>
                            </ol>

                            <div style="margin-left: auto; margin-right: auto; width: 125px">
                                <div style="position: absolute">
                                    <form action="index.php" method="post">
                                    <input type="hidden" name="mode" value="install" />
                                    <input type="hidden" name="step" value="3" />
                                    <input type="hidden" name="language" value="' . $language . '" />
                                    <input type="hidden" name="dbconfig_path" value="' . $dbconfig_path . '" />
                                    <input type="hidden" name="innodb" value="' . (($use_innodb) ? 'true' : 'false') . '" />
                                    <input type="submit" value="' . $LANG_INSTALL[66] . '" />
                                    </form>
                                </div>

                                <div style="position: relative; left: 55px; top: 5px">
                                    <form action="index.php" method="post">
                                    <input type="hidden" name="mode" value="upgrade" />
                                    <input type="hidden" name="language" value="' . $language . '" />
                                    <input type="hidden" name="dbconfig_path" value="' . $dbconfig_path . '" />
                                    <input type="submit" value="' . $LANG_INSTALL[25] . '" />
                                    </form>
                                </div>
                            </div>
                            ' . LB;

                    } else {
                        list($rc,$errors) = INST_createDatabaseStructures($use_innodb);
                        if ( $rc ) {
                            $site_name      = isset($_POST['site_name']) ? $_POST['site_name'] : (isset($_GET['site_name']) ? $_GET['site_name'] : '') ;
                            $site_slogan    = isset($_POST['site_slogan']) ? $_POST['site_slogan'] : (isset($_GET['site_slogan']) ? $_GET['site_slogan'] : '') ;
                            $site_url       = isset($_POST['site_url']) ? $_POST['site_url'] : (isset($_GET['site_url']) ? $_GET['site_url'] : '') ;
                            $site_admin_url = isset($_POST['site_admin_url']) ? $_POST['site_admin_url'] : (isset($_GET['site_admin_url']) ? $_GET['site_admin_url'] : '') ;
                            $site_mail      = isset($_POST['site_mail']) ? $_POST['site_mail'] : (isset($_GET['site_mail']) ? $_GET['site_mail'] : '') ;
                            $noreply_mail   = isset($_POST['noreply_mail']) ? $_POST['noreply_mail'] : (isset($_GET['noreply_mail']) ? $_GET['noreply_mail'] : '') ;

                            INST_personalizeAdminAccount($site_mail, $site_url);

                            // Insert the form data into the conf_values table

                            require_once $_CONF['path_system'] . 'classes/config.class.php';
                            require_once 'config-install.php';
                            install_config();

                            $config = config::get_instance();
                            $config->set('site_name', urldecode($site_name));
                            $config->set('site_slogan', urldecode($site_slogan));
                            $config->set('site_url', urldecode($site_url));
                            // FIXME: Check that directory exists
                            $config->set('site_admin_url', urldecode($site_admin_url));
                            $config->set('site_mail', urldecode($site_mail));
                            $config->set('noreply_mail', urldecode($noreply_mail));
                            $config->set('path_html', $html_path);
                            $config->set('path_log', $gl_path . 'logs/');
                            $config->set('path_language', $gl_path . 'language/');
                            $config->set('backup_path', $gl_path . 'backups/');
                            $config->set('path_data', $gl_path . 'data/');
                            $config->set('path_images', $html_path . 'images/');
                            $config->set('path_themes', $html_path . 'layout/');
                            $config->set('rdf_file', $html_path . 'backend/glfusion.rss');
                            $config->set('path_pear', $_CONF['path_system'] . 'pear/');
                            $config->set_default('default_photo', urldecode($site_url) . '/default.jpg');

                            $lng = INST_getDefaultLanguage($gl_path . 'language/', $language, $utf8);
                            if (!empty($lng)) {
                                $config->set('language', $lng);
                            }

                            $_CONF['path_html'] = $html_path;
                            $_CONF['site_url'] =  $site_url;

                            // Hook our plugin installs here...

                            INST_pluginAutoInstall('sitetailor');
                            INST_pluginAutoInstall('captcha');
                            INST_pluginAutoInstall('bad_behavior2');
                            INST_pluginAutoInstall('filemgmt');
                            INST_pluginAutoInstall('forum');
                            INST_pluginAutoInstall('mediagallery');
                            INST_pluginAutoInstall('commentfeeds');

                            // Setup nouveau as the default
                            $config->set('theme', 'nouveau');
                            DB_query("UPDATE {$_TABLES['users']} SET theme='nouveau' WHERE uid=2",1);

                            CTL_clearCache();

                            // Now we're done with the installation so redirect the user to success.php
                            header('Location: success.php?type=install&language=' . $language);
                        } else {
                            $display .= "<h2>" . $LANG_INSTALL[67] . "</h2><p>" . $LANG_INSTALL[68] . "</p>";
                            $display .= '<br />' . $errors;
                        }
                    }
                    break;

                case 'upgrade':
                    // Get and set which version to display
                    if ( !isset($version) ) {
                        $version = '';
                        if (isset($_GET['version'])) {
                            $version = $_GET['version'];
                        } else {
                            if (isset($_POST['version'])) {
                                $version = $_POST['version'];
                            }
                        }
                    }
                    // Let's do this
                    require_once $dbconfig_path;
                    require_once $siteconfig_path;
                    require_once $_CONF['path_system'] . 'lib-database.php';

                    // If this is a MySQL database check to see if it was
                    // installed with InnoDB support
                    if ($_DB_dbms == 'mysql') {
                        // Query `vars` and see if 'database_engine' == 'InnoDB'
                        $result = DB_query("SELECT `name`,`value` FROM {$_TABLES['vars']} WHERE `name`='database_engine'");
                        $row = DB_fetchArray($result);
                        if ($row['value'] == 'InnoDB') {
                           $use_innodb = true;
                        } else {
                           $use_innodb = false;
                        }
                    }

                    list($rc,$errors) = INST_doDatabaseUpgrades($version, $use_innodb);
                    if ( $rc ) {
                        INST_checkPlugins();

                        INST_pluginAutoUpgrade('sitetailor',1);
                        INST_pluginAutoUpgrade('captcha');
                        INST_pluginAutoUpgrade('bad_behavior2');
                        INST_pluginAutoUpgrade('filemgmt');
                        INST_pluginAutoUpgrade('forum');
                        INST_pluginAutoUpgrade('mediagallery');
                        INST_pluginAutoUpgrade('commentfeeds');

                        CTL_clearCache();

                        /*
                         * Insert some blocks
                         */

                        DB_query("REPLACE INTO {$_TABLES['blocks']} (`is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES (0,'blogroll_block','phpblock','Blog Roll','all',30,'',0,'','0000-00-00 00:00:00',NULL,NULL,0,0,'phpblock_blogroll','',2,4,3,3,2,2);",1);

                        // Great, installation is complete, redirect to success page
                        header('Location: success.php?type=upgrade&language=' . $language);
                    } else {
                        $display .= '<h2>' . $LANG_INSTALL[78] . '</h2>
                            <p>' . $LANG_INSTALL[79] . '</p>' . LB;
                        $display .= $errors;
                    }
                    break;
            }
            break;
    }
}


/**
 * Check to see if required files are writeable by the web server.
 *
 * @param   array   $files              list of files to check
 * @return  boolean                     true if all files are writeable
 *
 */
function INST_checkIfWritable($files)
{
    $writable = true;
    foreach ($files as $file) {
        if (!$tmp_file = @fopen($file, 'a')) {
            // Unable to modify
            $writable = false;
        } else {
            fclose($tmp_file);
        }
    }

    return $writable;
}


/**
 * Returns an HTML formatted string containing a list of which files
 * have incorrect permissions.
 *
 * @param   array   $files  List of files to check
 * @return  string          HTML and permission warning message.
 *
 */
function INST_permissionWarning($files)
{
    global $LANG_INSTALL;
    $display .= '
        <div class="install-path-container-outer">
            <div class="install-path-container-inner">
                <h2>' . $LANG_INSTALL[81] . '</h2>

                <p>' . $LANG_INSTALL[82] . '</p>

                <br />
                <p><label class="file-permission-list"><b>' . $LANG_INSTALL[10] . '</b></label> <b>' . $LANG_INSTALL[11] . '</b></p>
        ' . LB;

    foreach ($files as $file) {
        if (!$file_handler = @fopen ($file, 'a')) {
            $display .= '<p><label class="file-permission-list"><code>' . $file . '</code></label>' ;
            $file_perms = sprintf ("%3o", @fileperms ($file) & 0777);
            $display .= '<span class="error">' . $LANG_INSTALL[12] . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $file_perms . ')</p>' . LB ;
        } else {
            fclose ($file_handler);
        }
    }

    $display .= '
            </div>
        </div>

    <br /><br />' . LB;

    return $display;

}


/**
 * Returns the HTML form to return the user's inputted data to the
 * previous page.
 *
 * @return  string  HTML form code.
 *
 */
function INST_showReturnFormData($post_data)
{
    global $mode, $dbconfig_path, $language, $LANG_INSTALL;

    $display = '
        <form action="index.php" method="post">
        <input type="hidden" name="mode" value="' . $mode . '" />
        <input type="hidden" name="step" value="1" />
        <input type="hidden" name="dbconfig_path" value="' . $dbconfig_path . '" />
        <input type="hidden" name="language" value="' . $language . '" />
        <input type="hidden" name="site_name" value="' . $post_data['site_name'] . '" />
        <input type="hidden" name="site_slogan" value="' . $post_data['site_slogan'] . '" />
        <input type="hidden" name="db_type" value="' . $post_data['db_type'] . '" />
        <input type="hidden" name="db_host" value="' . $post_data['db_host'] . '" />
        <input type="hidden" name="db_name" value="' . $post_data['db_name'] . '" />
        <input type="hidden" name="db_user" value="' . $post_data['db_user'] . '" />
        <input type="hidden" name="db_prefix" value="' . $post_data['db_prefix'] . '" />
        <input type="hidden" name="site_url" value="' . $post_data['site_url'] . '" />
        <input type="hidden" name="site_admin_url" value="' . $post_data['site_admin_url'] . '" />
        <input type="hidden" name="site_mail" value="' . $post_data['site_mail'] . '" />
        <input type="hidden" name="noreply_mail" value="' . $post_data['noreply_mail'] . '" />
        <p align="center"><input type="submit" value="&lt;&lt; ' . $LANG_INSTALL[61] . '" /></p>
        </form>';

    return $display;
}


/**
 * Returns the HTML form to return the user's inputted data to the
 * previous page.
 *
 * @return  string  HTML form code.
 *
 */
function INST_helpLink($var)
{
    global $language;

    return '(<a href="help.php?language=' . $language . '#' . $var . '" target="_blank">?</a>)';
}


/**
 * Get the current installed version of glFusion
 *
 * @return glFusion version in x.x.x format
 *
 */
function INST_identifyglFusionVersion ()
{
    global $_TABLES, $_DB, $_DB_dbms, $dbconfig_path, $siteconfig_path;

    $_DB->setDisplayError(true);

    // simple tests for the version of the database:
    // "DESCRIBE sometable somefield", ''
    //  => just test that the field exists
    // "DESCRIBE sometable somefield", 'somefield,sometype'
    //  => test that the field exists and is of the given type
    //
    // Should always include a test for the current version so that we can
    // warn the user if they try to run the update again.


    switch ($_DB_dbms) {

    case 'mysql':
        $test = array(
            '1.0.1'  => array("DESCRIBE {$_TABLES['storysubmission']} bodytext",''),
            );

        break;
    }

    $version = '';

    $result = DB_query("SELECT * FROM {$_TABLES['vars']} WHERE name='glfusion'",1);
    if ( $result !== false ) {
        if ( DB_numRows($result) > 0 ) {
            $row = DB_fetchArray($result);
            $version = $row['value'];
            return $version;
        }
    }

    // we didn't find the easy stuff, so let's see if we can
    // figure it out by snooping the databases

    $result = DB_query("DESCRIBE {$_TABLES['access']} acc_ft_id", 1);
    if ($result === false) {
        // A check for the first field in the first table failed?
        // Sounds suspiciously like an empty table ...

        return 'empty';
    }

    foreach ($test as $v => $qarray) {
        $result = DB_query($qarray[0], 1);
        if ($result === false) {

            // error - continue with next test

        } else if (DB_numRows($result) > 0) {
            $A = DB_fetchArray($result);
            if (empty($qarray[1])) {
                // test only for existence of field - succeeded
                $version = $v;
                break;
            } else {
                if (substr($qarray[0], 0, 6) == 'SELECT') {
                    // text for a certain value
                    if ($A[0] == $qarray[1]) {
                        $version = $v;
                        break;
                    }
                } else {
                    // test for certain type of field
                    $tst = explode(',', $qarray[1]);
                    if (($A['Field'] == $tst[0]) && ($A['Type'] == $tst[1])) {
                        $version = $v;
                        break;
                    }
                }
            }
        }
    }

    return $version;
}


/**
 * Sets up the database tables
 *
 * @param   boolean $use_innodb     Whether to use InnoDB table support if using MySQL
 * @return  boolean                 True if successful
 *
 */
function INST_createDatabaseStructures ($use_innodb = false)
{
    global $_CONF, $_TABLES, $_DB, $_DB_dbms, $_DB_host, $_DB_user, $_DB_pass, $LANG_INSTALL;

    $rc = true;

    $_DB->setDisplayError (true);

    // Because the create table syntax can vary from dbms-to-dbms we are
    // leaving that up to each database driver (e.g. mysql.class.php,
    // postgresql.class.php, etc)

    // Get DBMS-specific create table array and data array
    require_once $_CONF['path'] . 'sql/' . $_DB_dbms . '_tableanddata.php';

    $progress = '';

    if (INST_checkTableExists ('access')) {
        return array(false,$LANG_INSTALL[68]);
    }

    switch($_DB_dbms){
        case 'mysql':
            list($rc,$errors) = INST_updateDB($_SQL);

            if ($use_innodb) {
                DB_query ("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('database_engine', 'InnoDB')");
            }
            break;
        case 'mssql':
            foreach ($_SQL as $sql) {
                $_DB->dbQuery($sql, 0, 1);
            }
            break;
    }

    // Now insert mandatory data and a small subset of initial data
    foreach ($_DATA as $data) {
        $progress .= "executing " . $data . "<br />\n";

        DB_query ($data);
    }

    return array($rc, $errors);
}


/**
 * On a fresh install, set the Admin's account email and homepage
 *
 * @param   string  $site_mail  email address, e.g. the site email
 * @param   string  $site_url   the site's URL
 * @return  void
 *
 */
function INST_personalizeAdminAccount($site_mail, $site_url)
{
    global $_TABLES, $_DB_dbms;

    if (($_DB_dbms == 'mysql') || ($_DB_dbms == 'mssql')) {

        // let's try and personalize the Admin account a bit ...

        if (!empty($site_mail)) {
            if (strpos($site_mail, 'example.com') === false) {
                DB_query("UPDATE {$_TABLES['users']} SET email = '" . addslashes($site_mail) . "' WHERE uid = 2",1);
            }
        }
        if (!empty($site_url)) {
            if (strpos($site_url, 'example.com') === false) {
                DB_query("UPDATE {$_TABLES['users']} SET homepage = '" . addslashes($site_url) . "' WHERE uid = 2",1);
            }
        }
    }
}

/**
* Derive site's default language from available information
*
* @param    string  $langpath   path where the language files are kept
* @param    string  $language   language used in the install script
* @param    boolean $utf8       whether to use UTF-8
* @return   string              name of default language (for the config)
*
*/
function INST_getDefaultLanguage($langpath, $language, $utf8 = false)
{
    $pos = strpos($language, '_utf-8');
    if ($pos !== false) {
        $language = substr($language, 0, $pos);
    }

    if ($utf8) {
        $lngname = $language . '_utf-8';
    } else {
        $lngname = $language;
    }
    $lngfile = $lngname . '.php';

    if (!file_exists($langpath . $lngfile)) {
        // doesn't exist - fall back to English
        if ($utf8) {
            $lngname = 'english_utf-8';
        } else {
            $lngname = 'english';
        }
    }

    return $lngname;
}


/**
 * Make a nice display name from the language filename
 *
 * @param    string  $file   filename without the extension
 * @return   string          language name to display to the user
 * @note     This code is a straight copy from MBYTE_languageList()
 *
 */
function INST_prettifyLanguageName($filename)
{
    $langfile = str_replace ('_utf-8', '', $filename);
    $uscore = strpos ($langfile, '_');
    if ($uscore === false) {
        $lngname = ucfirst ($langfile);
    } else {
        $lngname = ucfirst (substr ($langfile, 0, $uscore));
        $lngadd = substr ($langfile, $uscore + 1);
        $lngadd = str_replace ('utf-8', '', $lngadd);
        $lngadd = str_replace ('_', ', ', $lngadd);
        $word = explode (' ', $lngadd);
        $lngadd = '';
        foreach ($word as $w) {
            if (preg_match ('/[0-9]+/', $w)) {
                $lngadd .= strtoupper ($w) . ' ';
            } else {
                $lngadd .= ucfirst ($w) . ' ';
            }
        }
        $lngname .= ' (' . trim ($lngadd) . ')';
    }

    return $lngname;
}


/**
 * Check if a table exists
 *
 * @param   string $table   Table name
 * @return  boolean         True if table exists, false if it does not
 *
 */
function INST_checkTableExists ($table)
{
    global $_TABLES, $_DB_dbms;

    $exists = false;

    if ($_DB_dbms == 'mysql') {
        $result = DB_query ("SHOW TABLES LIKE '{$_TABLES[$table]}'",1);
        if (DB_numRows ($result) > 0) {
            $exists = true;
        }
    } elseif ($_DB_dbms == 'mssql') {
        $result = DB_Query("SELECT 1 FROM sysobjects WHERE name='{$_TABLES[$table]}' AND xtype='U'");
        if (DB_numRows ($result) > 0) {
            $exists = true;
        }
    }

    return $exists;
}


/**
 * Check for InnoDB table support (usually as of MySQL 4.0, but may be
 * available in earlier versions, e.g. "Max" or custom builds).
 *
 * @return  boolean     true = InnoDB tables supported, false = not supported
 *
 */
function INST_innodbSupported()
{
    $result = DB_query ("SHOW VARIABLES LIKE 'have_innodb'");
    $A = DB_fetchArray ($result, true);

    if (strcasecmp ($A[1], 'yes') == 0) {
        return true;
    } else {
        return false;
    }
}


/**
 * Check InnoDB Upgrade
 *
 * @param   array   $_SQL   List of SQL queries
 * @return  array           InnoDB table style if chosen
 *
 */
function INST_checkInnodbUpgrade($_SQL)
{
    global $use_innodb;

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    return $_SQL;
}

/**
 * Perform database upgrades
 *
 * @param   string  $current_gl_version Current glFusion version
 * @param   boolean $use_innodb         Whether or not to use InnoDB support with MySQL
 * @return  boolean                     True if successful
 *
 */
function INST_doDatabaseUpgrades($current_fusion_version, $use_innodb = false)
{
    global $_TABLES, $_CONF, $_SP_CONF, $_DB, $_DB_dbms, $_DB_table_prefix,
           $dbconfig_path, $siteconfig_path, $html_path,$LANG_INSTALL;

    $rc = true;
    $errors = '';

    $_DB->setDisplayError (true);

    // Because the upgrade sql syntax can vary from dbms-to-dbms we are
    // leaving that up to each glFusion database driver

    $done = false;
    $progress = '';
    while ($done == false) {
        switch ($current_fusion_version) {
        case '1.0.0':
        case '1.0.1':
            require_once $_CONF['path'] . 'sql/updates/mysql_1.0.1_to_1.1.0.php';
            list($rc,$errors) = INST_updateDB($_SQL);

            /*
             * Do the plugin upgrades here
             */

            if (INST_pluginExists('staticpages')) {
                $check = upgrade_StaticpagesPlugin();
                if (!$check) {
                    echo "Error updating the staticpages";
                    return false;
                }
            }
            require_once $_CONF['path_system'] . 'classes/config.class.php';

            $c = config::get_instance();

            $c->add('comment_code',0,'select',4,21,17,1670,TRUE);
            $c->add('comment_edit',0,'select',4,21,0,1680,TRUE);
            $c->add('comment_edittime',1800,'text',4,21,NULL,1690,TRUE);
            $c->add('article_comment_close_days',30,'text',4,21,NULL,1700,TRUE);
            $c->add('comment_close_rec_stories',0,'text',4,21,NULL,1710,TRUE);

            $c->add('jhead_enabled',0,'select',5,22,0,1480,TRUE);
            $c->add('path_to_jhead','','text',5,22,NULL,1490,TRUE);
            $c->add('jpegtrans_enabled',0,'select',5,22,0,1500,TRUE);
            $c->add('path_to_jpegtrans','','text',5,22,NULL,1510,TRUE);

            $c->add('hide_adminmenu',TRUE,'select',3,12,1,1170,TRUE);

            // search stuff (temp for now)
            $c->add('fs_search', NULL, 'fieldset', 0, 6, NULL, 0, TRUE);
            $c->add('search_style','google','select',0,6,18,650,TRUE);
            $c->add('search_limits','10,15,25,30','text',0,6,NULL,660,TRUE);
            $c->add('num_search_results',30,'text',0,6,NULL,670,TRUE);
            $c->add('search_show_limit',TRUE,'select',0,6,1,680,TRUE);
            $c->add('search_show_sort',TRUE,'select',0,6,1,690,TRUE);
            $c->add('search_show_num',TRUE,'select',0,6,1,700,TRUE);
            $c->add('search_show_type',TRUE,'select',0,6,1,710,TRUE);
            $c->add('search_show_user',TRUE,'select',0,6,1,720,TRUE);
            $c->add('search_show_hits',TRUE,'select',0,6,1,730,TRUE);
            $c->add('search_no_data','<i>Not available...</i>','text',0,6,NULL,740,TRUE);
            $c->add('search_separator',' &gt; ','text',0,6,NULL,750,TRUE);
            $c->add('search_def_keytype','phrase','select',0,6,19,760,TRUE);
            $c->restore_param('num_search_results', 'Core');

            // This option should only be set during the install/upgrade because of all
            // the setting up thats required. So hide it from the user.
            $c->add('search_use_fulltext',FALSE,'hidden',0,6);

            $current_fusion_version = '1.1.0';
            $_SQL = '';
            break;

        default:
            $done = true;
        }
    }

    // delete the security check flag on every update to force the user
    // to run admin/sectest.php again
    DB_delete ($_TABLES['vars'], 'name', 'security_check');

    return array($rc,$errors);

}


/**
 * Check if a current plugin is installed
 *
 * @param   string $plugin  Name of plugin to check
 *
 */
function INST_pluginExists($plugin)
{
    global $_DB, $_TABLES;
    $result = DB_query("SELECT `pi_name` FROM {$_TABLES['plugins']} WHERE `pi_name` = '$plugin'");
    if (DB_numRows($result) > 0) {
        return true;
    } else {
        return false;
    }
}


/**
 * Run all the database queries from the update file.
 *
 * @param   array $_SQL   Array of queries
 *
 */
function INST_updateDB($_SQL)
{
    global $progress, $_DB, $_DB_dbms;

    $_DB->setDisplayError (true);
    $errors = '';
    $rc = true;

    $_SQL = INST_checkInnodbUpgrade($_SQL);
    foreach ($_SQL as $sql) {
        $progress .= "executing " . $sql . "<br />\n";
        if ($_DB_dbms == 'mssql') {
            $_DB->dbQuery($sql, 0, 1);
        } else {
            DB_query($sql,1);
            if ( DB_error() ) {
                $errors .= DB_error() . "<br />\n";
                $rc = false;
            }
        }
    }
    return array($rc,$errors);
}

/**
* Check which plugins are actually installed and disable them if needed
*
* @return   int     number of plugins that were disabled
*
*/
function INST_checkPlugins()
{
    global $_CONF, $_TABLES;

    $disabled = 0;
    $plugin_path = $_CONF['path'] . 'plugins/';

    $result = DB_query("SELECT pi_name FROM {$_TABLES['plugins']} WHERE pi_enabled = 1");
    $num_plugins = DB_numRows($result);
    for ($i = 0; $i < $num_plugins; $i++) {
        $A = DB_fetchArray($result);
        if (!file_exists($plugin_path . $A['pi_name'] . '/functions.inc')) {
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled = 0 WHERE pi_name = '{$A['pi_name']}'");
            $disabled++;
        }
    }

    return $disabled;
}

/**
* Change default character set to UTF-8
*
* @param   string   $siteconfig_path  complete path to siteconfig.php
* @param   string   $charset          default character set to use
* @return  boolean                    true: success; false: an error occured
* @note    Yes, this means that we need to patch siteconfig.php a second time.
*
*/
function INST_setDefaultCharset($siteconfig_path, $charset)
{
    $result = true;

    $siteconfig_file = fopen($siteconfig_path, 'r');
    $siteconfig_data = fread($siteconfig_file, filesize($siteconfig_path));
    fclose($siteconfig_file);

    $siteconfig_data = preg_replace
            (
             '/\$_CONF\[\'default_charset\'\] = \'[^\']*\';/',
             "\$_CONF['default_charset'] = '" . $charset . "';",
             $siteconfig_data
            );

    $siteconfig_file = fopen($siteconfig_path, 'w');
    if (!fwrite($siteconfig_file, $siteconfig_data)) {
        $result = false;
    }
    @fclose($siteconfig_file);

    return $result;
}

/**
* Install value added plugins
*
* @param   string   $plugin         plugin name
* @return  boolean                  true: success; false: an error occured
*
*/

function INST_pluginAutoInstall( $plugin )
{
    global $_CONF, $_TABLES, $_DB_table_prefix;
    global $pi_name, $pi_version, $gl_version, $pi_url;
    global $DEFVALUES, $NEWFEATURE;
    global $_DB_dbms;

    $rc = false;

    if ( file_exists($_CONF['path'] . '/plugins/' . $plugin . '/install.inc') ) {
        require_once($_CONF['path'] . '/plugins/' . $plugin . '/install.inc');
        $plgInstallFunction = 'plugin_install_' . $plugin;
        if ( !function_exists($plgInstallFunction) ) {
            $plgInstallFunction = 'glfusion_install_' . $plugin;
        }
        $rc = $plgInstallFunction($_DB_table_prefix);
    }
    return $rc;
}

/**
* Upgrades value added plugins
*
* @param   string   $plugin         plugin name
* @return  boolean                  true: success; false: an error occured
*
*/

function INST_pluginAutoUpgrade( $plugin, $forceInstall = 0 )
{
    global $_CONF, $_TABLES, $_DB_table_prefix;

    $rc = false;

    $active = DB_getItem($_TABLES['plugins'],'pi_enabled','pi_name="' . $plugin . '"');
    if ( $active || $forceInstall == 1) {
        if ( file_exists($_CONF['path'] . '/plugins/' . $plugin . '/glupgrade.inc') ) {
            require_once($_CONF['path'] . '/plugins/' . $plugin . '/glupgrade.inc');
            if ( function_exists( 'plugin_plgupgrade_' . $plugin ) ) {
                $plgUpgradeFunction = 'plugin_plgupgrade_' . $plugin;
                $rc = $plgUpgradeFunction();
            } else {
                if ( $forceInstall == 1 ) {
                    INST_pluginAutoInstall( $plugin );
                }
            }
        }
    }
    return $rc;
}

function CTL_clearCacheDirectories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry) || $entry == '.svn' || $entry == 'index.html') {
                continue;
            } elseif (is_dir($path . $entry)) {
                CTL_clearCacheDirectories($path . $entry, $needle);
                @rmdir($path . $entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                unlink($path . $entry);
            }
        }
        @closedir($dir);
    }
}


function CTL_clearCache($plugin='')
{
    global $TEMPLATE_OPTIONS, $_CONF;

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }

    CTL_clearCacheDirectories($_CONF['path'] . 'data/layout_cache/', $plugin);
}

// +---------------------------------------------------------------------------+
// | Main                                                                      |
// +---------------------------------------------------------------------------+

// prepare some hints about what /path/to/glfusion/private might be ...
$gl_path    = strtr(__FILE__, '\\', '/'); // replace all '\' with '/'
for ($i = 0; $i < 4; $i++) {
    $remains = strrchr($gl_path, '/');
    if ($remains === false) {
        break;
    } else {
        $gl_path = substr($gl_path, 0, -strlen($remains));
    }
}

$html_path          = str_replace('admin/install/index.php', '', str_replace('admin\install\index.php', '', str_replace('\\', '/', __FILE__)));
$siteconfig_path    = '../../siteconfig.php';
$dbconfig_path      = (isset($_POST['dbconfig_path'])) ? $_POST['dbconfig_path'] : ((isset($_GET['dbconfig_path'])) ? $_GET['dbconfig_path'] : '');
$step               = isset($_GET['step']) ? $_GET['step'] : (isset($_POST['step']) ? $_POST['step'] : 1);
$mode               = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');

$language = 'english';
if (isset($_POST['language'])) {
    $lng = $_POST['language'];
} elseif (isset($_GET['language'])) {
    $lng = $_GET['language'];
} else if (isset($_COOKIE['language'])) {
    // Okay, so the name of the language cookie is configurable, so it may not
    // be named 'language' after all. Still worth a try ...
    $lng = $_COOKIE['language'];
} else {
    $lng = $language;
}
// sanitize value and check for file
$lng = preg_replace('/[^a-z0-9\-_]/', '', $lng);
if (!empty($lng) && is_file('language/' . $lng . '.php')) {
    $language = $lng;
}
require_once 'language/' . $language . '.php';

// $display holds all the outputted HTML and content
$display = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
if (empty($LANG_DIRECTION)) {
    $LANG_DIRECTION = 'ltr';
}
if ($LANG_DIRECTION == 'rtl') {
    $label_dir = 'label-right';
} else {
    $label_dir = 'label-left';
}
$display .= '<head>
<meta http-equiv="Content-Type" content="text/html;charset=' . $LANG_CHARSET . '" />
<link rel="stylesheet" type="text/css" href="layout/style.css" />
<meta name="robots" content="noindex,nofollow" />
<title>' . $LANG_INSTALL[0] . '</title>
</head>
<body dir="' . $LANG_DIRECTION . '">
    <div id="gl_container_fluid">
        <div id="gl_header">
            <div class="top-r-corner">
                <div class="top-l-corner">
					<div class="floatright install-slogan" style="color:#FFF;">
						<a style="color:#fff;text-decoration:none;" href="' . $LANG_INSTALL[87] . '" target="_blank">' . $LANG_INSTALL[1] . '</a>
					</div>
                    <div class="floatleft">
                      <img src="layout/logo.png" height="100" width="330" alt="' . $LANG_INSTALL[0] . '" title="' . $LANG_INSTALL[0] . '" id="header-site-logo" />
                    </div>
                </div>
            </div>
        </div> <!-- end of gl_header -->

        <div id="gl_moomenu"></div>

        <div id="gl_wrapper">' . LB;

// Show the language drop down selection on the first page
if ($mode == 'check_permissions') {
    $display .='<form action="index.php" method="post">' . LB;

    $_PATH = array('dbconfig', 'public_html');
    if (isset($_GET['mode']) || isset($_POST['mode'])) {
        $value = (isset($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];
        $display .= '<input type="hidden" name="mode" value="' . $value . '" />' . LB;
    }
    foreach ($_PATH as $name) {
        if (isset($_GET[$name . '_path']) || isset($_POST[$name . '_path'])) {
            $value = (isset($_POST[$name . '_path'])) ? $_POST[$name . '_path'] : $_GET[$name . '_path'];
            $display .= '<input type="hidden" name="' . $name .'_path" value="' . $value . '" />' . LB;
        }
    }

    $display .= $LANG_INSTALL[86] . ':  <select name="language">' . LB;

    foreach (glob('language/*.php') as $filename) {
        $filename = preg_replace('/.php/', '', preg_replace('/language\//', '', $filename));
        $display .= '<option value="' . $filename . '"' . (($filename == $language) ? ' selected="selected"' : '') . '>' . INST_prettifyLanguageName($filename) . '</option>' . LB;
    }

    $display .= '</select>
                    <input type="submit" value="' . $LANG_INSTALL[80] . '" />
            </form>';
}
$display .= '
        <div class="installation-body-container">
            <h1 class="heading">' . $LANG_INSTALL[3] . '</h1>' . LB;


switch ($mode) {

    /**
     * The first thing the script does is to check for the location of
     * the db-config.php file. It checks for the file in the default location
     * and also the public_html/ directory. If the script can't find the file in
     * either of those places it will ask the user to specify its location.
     */
    default:

        // Before we do anything make sure the version of PHP is supported.
        $phpv = php_v ();
        if (($phpv[0] < 4) || (($phpv[0] == 4) && ($phpv[1] < 1))) {
            $display .= '<h1>' . $LANG_INSTALL[4] . '</h1>' . LB;
            $display .= '<p>' . $LANG_INSTALL[5] . $phpv[0] . '.' . $phpv[1] . '.' . (int) $phpv[2] . $LANG_INSTALL[6] . '</p>' . LB;
        } else {

            // Check the location of db-config.php
            // We'll base our /path/to/glfusion/private/ on its location
            $gl_path        .= '/';
            $form_fields    = '';
            $num_errors     = 0;
            $dbconfig_path  = '';
            $dbconfig_file  = 'db-config.php';

            if (!file_exists($gl_path . $dbconfig_file) && !file_exists($gl_path . 'public_html/' . $dbconfig_file)) {
                // If the file/directory is not located in the default location
                // or in public_html have the user enter its location.
                $form_fields .= '<p><label>db-config.php</label> <input type="text" name="dbconfig_path" value="/path/to/'
                            . $dbconfig_file . '" size="65" />&nbsp;&nbsp;Examples: <b>/usr/bin/glfusion/private/</b> or <b>C:/glFusion/private/</b></p>'  . LB;
                $num_errors++;
            } else {
                // See whether the file/directory is located in the default place or in public_html
                $dbconfig_path = file_exists($gl_path . $dbconfig_file)
                                    ? $gl_path . $dbconfig_file
                                    : $gl_path . 'public_html/' . $dbconfig_file;
            }


            if ($num_errors == 0) {
                // If the script was able to locate all the system files/directories move onto the next step
                header('Location: index.php?mode=check_permissions&dbconfig_path=' . urlencode($dbconfig_path));
            } else {
                // If the script was not able to locate all the system files/directories ask the user to enter their location
                $display .= '<h2>' . $LANG_INSTALL[7] . '</h2>
                    <p style="margin-bottom:20px;">' . $LANG_INSTALL[8] . '</p>
                    <form action="index.php" method="post">
                    <input type="hidden" name="mode" value="check_permissions" />
                    ' . $form_fields . '
                    <input style="margin-top:10px;float:right" type="submit" name="submit" class="submit" value="Next &gt;&gt;" />
                    </form>' . LB;
            }
        }
        break;

    /**
     * The second step is to check permissions on the files/directories
     * that glFusion needs to be able to write to. The script uses the location of
     * db-config.php from the previous step to determine location of everything.
     */
    case 'check_permissions':

        // Get the paths from the previous page
        $_PATH = array('db-config.php' => urldecode(isset($_GET['dbconfig_path'])
                                            ? $_GET['dbconfig_path'] : $_POST['dbconfig_path']),
                        'public_html/' => str_replace('admin/install/index.php', '', str_replace('admin\install\index.php', '', __FILE__)));

        // Be fault tolerant with the path the user enters
        if (!strstr($_PATH['db-config.php'], 'db-config.php')) {
            // If the user did not provide a trailing '/' then add one
            if (!preg_match('/^.*\/$/', $_PATH['db-config.php'])) {
                $_PATH['db-config.php'] .= '/';
            }
            $_PATH['db-config.php'] .= 'db-config.php';
        }

        // The path to db-config.php is what we'll use to generate our /path/to/glFusion/private so
        // we want to make sure it's valid and exists before we continue and create problems.
        if (!file_exists($_PATH['db-config.php'])) {
            $display .= '<h2>' . $LANG_INSTALL[83] . '</h2>'
                    . $LANG_INSTALL[84] . $_PATH['db-config.php'] . $LANG_INSTALL[85]
                    . '<br /><br />
                      <div style="margin-left: auto; margin-right: auto; width: 1px">
                        <form action="index.php" method="post">
                        <input type="submit" value="&lt;&lt; ' . $LANG_INSTALL[61] . '" />
                        </form>
                      </div>';
        } else {

            require_once $_PATH['db-config.php'];  // We need db-config.php the current DB information

            // siteconfig.php
            @include_once $siteconfig_path;         // We need siteconfig.php for core $_CONF values.

            $gl_path                = str_replace('db-config.php', '', $_PATH['db-config.php']);
            $log_path               = $gl_path . 'logs/';
            $_CONF['rdf_file']      = $_PATH['public_html/'] . 'backend/glfusion.rss';
            $_CONF['path_images']   = $_PATH['public_html/'] . 'images/';
            $data_path              = $gl_path . (file_exists($gl_path . 'data') ? 'data/' : 'public_html/data/');
            if (!isset($_CONF['allow_mysqldump'])) {
                if ($_DB_dbms == 'mysql') {
                    $_CONF['allow_mysqldump'] = 1;
                }
            }
            $failed                 = 0; // number of failed tests
            $display_permissions    = '<br /><p><label class="file-permission-list"><b>' . $LANG_INSTALL[10]
                                    . '</b></label> <b>' . $LANG_INSTALL[11] . '</b></p>' . LB;
            $_PERMS                 = array('db-config.php', 'siteconfig.php', 'error.log', 'access.log',
                                            'rdf', 'userphotos', 'articles', 'topics', 'backups', 'data','fm','ctl');


            // db-config.php
            if (!$dbconfig_file = @fopen($_PATH['db-config.php'], 'a')) {
                $_PERMS['db-config.php'] = sprintf("%3o", @fileperms($_PATH['db-config.php']) & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_PATH['db-config.php']
                                        . '</code></label><span class="error">' . $LANG_INSTALL[12] . ' 777</span> ('
                                        . $LANG_INSTALL[13] . ' ' . $_PERMS['db-config.php'] . ')</p>' . LB ;
                $failed++;
            } else {
                fclose($dbconfig_file);
            }

            // siteconfig.php
            if ( !file_exists($_PATH['public_html/'] . 'siteconfig.php' ) ) {
                if (!$siteconfig_file = @fopen($_PATH['public_html/'] . 'siteconfig.php', 'a')) {
                    $_PERMS['siteconfig.php'] = sprintf("%3o", @fileperms($_PATH['public_html/'] . 'siteconfig.php') & 0777);
                    $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_PATH['public_html/']
                                            . 'siteconfig.php</code></label><span class="error">' . $LANG_INSTALL[500]
                                            . '</p>' . LB ;
                    $failed++;
                } else {
                    @fclose($siteconfig_file);
                    $siteconfig_path = $_PATH['public_html/'] . 'siteconfig.php.dist';
                    $siteconfig_file = @fopen($siteconfig_path, 'r');
                    $siteconfig_data = @fread($siteconfig_file, filesize($siteconfig_path));
                    fclose($siteconfig_file);

                    $siteconfig_path = $_PATH['public_html/'] . 'siteconfig.php';
                    // $_CONF['path']

                    $siteconfig_file = @fopen($siteconfig_path, 'w');
                    if (!@fwrite($siteconfig_file, $siteconfig_data)) {
                        $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_PATH['public_html/']
                                                . 'siteconfig.php</code></label><span class="error">' . $LANG_INSTALL[12]
                                                . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['siteconfig.php'] . ')</p>' . LB ;
                        $failed++;
                    }
                    @fclose ($siteconfig_file);
                }
            } else {
                if (!$siteconfig_file = @fopen($_PATH['public_html/'] . 'siteconfig.php', 'a')) {
                    $_PERMS['siteconfig.php'] = sprintf("%3o", @fileperms($_PATH['public_html/'] . 'siteconfig.php') & 0777);
                    $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_PATH['public_html/']
                                            . 'siteconfig.php</code></label><span class="error">' . $LANG_INSTALL[12]
                                            . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['siteconfig.php'] . ')</p>' . LB ;
                    $failed++;
                } else {
                    @fclose($siteconfig_file);
                }
            }

            // lib-custom.php
            if ( !file_exists($gl_path . 'system/lib-custom.php' ) ) {
                if (!$libcustom_file = @fopen($gl_path . 'system/lib-custom.php', 'a')) {
                    $_PERMS['lib-custom.php'] = sprintf("%3o", @fileperms($gl_path . 'system/lib-custom.php') & 0777);
                    $display_permissions    .= '<p><label class="file-permission-list"><code>' . $gl_path
                                            . 'system/lib-custom.php</code></label><span class="error">' . $LANG_INSTALL[501]
                                            . '</p>' . LB ;
                    $failed++;
                } else {
                    @fclose($libcustom_file);
                    $libcustom_path = $gl_path . 'system/lib-custom.php.dist';
                    $libcustom_file = @fopen($libcustom_path, 'r');
                    $libcustom_data = @fread($libcustom_file, filesize($libcustom_path));
                    @fclose($libcustom_file);

                    $libcustom_path = $gl_path . 'system/lib-custom.php';

                    $libcustom_file = @fopen($libcustom_path, 'w');
                    if (!@fwrite($libcustom_file, $libcustom_data)) {
                        $display_permissions    .= '<p><label class="file-permission-list"><code>' . $gl_path
                                                . 'system/lib-custom.php</code></label><span class="error">' . $LANG_INSTALL[12]
                                                . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['lib-custom.php'] . ')</p>' . LB ;
                        $failed++;
                    }
                    @fclose ($libcustom_file);
                }
            }

            // backend directory & glfusion.rss
            if (!$file = @fopen($_CONF['rdf_file'], 'w')) {
                // Permissions are incorrect
                $_PERMS['rdf']          = sprintf("%3o", @fileperms($_CONF['rdf_file']) & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_CONF['rdf_file']
                                        . '</code></label><span class="error">' . $LANG_INSTALL[12] . ' 777</span> (' . $LANG_INSTALL[13] . ' '
                                        . $_PERMS['rdf'] . ') </p>' . LB;
                $failed++;
            } else {
                // Permissions are correct
                fclose ($file);
            }

            // backups directory
            if ($_CONF['allow_mysqldump'] == 1) {
                // If backups are enabled
                if (!$file = @fopen($gl_path . 'backups/test.txt', 'w')) {
                    // Permissions are incorrect
                    $_PERMS['backups']      = sprintf("%3o", @fileperms($gl_path . 'backups/') & 0777);
                    $display_permissions    .= '<p><label class="file-permission-list"><code>' . $gl_path
                                            . 'backups/</code></label><span class="error">' . $LANG_INSTALL[14]
                                            . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['backups'] . ') </p>' . LB;
                    $failed++;
                } else {
                    // Permissions are correct
                    fclose($file);
                    unlink($gl_path . 'backups/test.txt');
                }
            }

            // data directory
            if (!$file = @fopen($data_path . 'test.txt', 'w')) {
                // Permissions are incorrect
                $_PERMS['data']         = sprintf("%3o", @fileperms($data_path) & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $data_path
                                        . '</code></label><span class="error">' . $LANG_INSTALL[14]
                                        . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['data'] . ') </p>' . LB;
                $failed++;
            } else {
                // Permissions are correct
                fclose($file);
                unlink($data_path . 'test.txt');
            }

            // articles directory
            if (!$file = @fopen($_CONF['path_images'] . 'articles/test.gif', 'w')) {
                // Permissions are incorrect
                $_PERMS['articles']     = sprintf("%3o", @fileperms($_CONF['path_images'] . 'articles/') & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_CONF['path_images']
                                        . 'articles/</code></label><span class="error">' . $LANG_INSTALL[14]
                                        . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['articles'] . ') </p>' . LB;
                $failed++;
            } else {
                // Permissions are correct
                fclose($file);
                unlink($_CONF['path_images'] . 'articles/test.gif');
            }

            // topics directory
            if (!$file = @fopen($_CONF['path_images'] . 'topics/test.gif', 'w')) {
                // Permissions are incorrect
                $_PERMS['topics']       = sprintf("%3o", @fileperms($_CONF['path_images'] . 'topics/') & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_CONF['path_images']
                                        . 'topics/</code></label><span class="error">' . $LANG_INSTALL[14]
                                        . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['topics'] . ') </p>' . LB;
                $failed++;
            } else {
                // Permissions are correct
                fclose($file);
                unlink($_CONF['path_images'] . 'topics/test.gif');
            }

            // userphotos directory
            if (!$file = @fopen($_CONF['path_images'] . 'userphotos/test.gif', 'w')) {
                // Permissions are incorrect
                $_PERMS['userphoto']    = sprintf("%3o", @fileperms($_CONF['path_images'] . 'userphotos/') & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $_CONF['path_images']
                                        . 'userphotos/</code></label><span class="error">' . $LANG_INSTALL[14]
                                        . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['userphoto'] . ') </p>' . LB;
            } else {
                // Permissions are correct
                fclose($file);
                unlink($_CONF['path_images'] . 'userphotos/test.gif');
            }

            // logs
            if (!$err_file = @fopen($log_path . 'error.log', 'a')) {
                // Permissions are incorrect
                $_PERMS['error.log']    = sprintf("%3o", @fileperms($log_path) & 0775);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $log_path . '</code></label><span class="error">'
                                        . $LANG_INSTALL[88] . ' 777</span> (' . $LANG_INSTALL[13] . ' '
                                        . ($_PERMS['error.log'] == 0 ? $LANG_INSTALL[22] : $_PERMS['error.log']) . ')</p>' . LB ;
                $failed++;
            } else {
                // Permissions are correct
                fclose($err_file);
            }

            // Template Caching Directory
            if (!$file = @fopen($gl_path . 'data/layout_cache/test.txt', 'w')) {
                // Permissions are incorrect
                $_PERMS['ctl']    = sprintf("%3o", @fileperms($gl_path . 'data/layout_cache/') & 0777);
                $display_permissions    .= '<p><label class="file-permission-list"><code>' . $gl_path
                                        . 'data/layout_cache/</code></label><span class="error">' . $LANG_INSTALL[14]
                                        . ' 777</span> (' . $LANG_INSTALL[13] . ' ' . $_PERMS['ctl'] . ') </p>' . LB;
            } else {
                // Permissions are correct
                @fclose($file);
                @unlink($_CONF['path'] . 'data/layout_cache/test.txt');
            }

            $display .= $LANG_INSTALL[9] . '<br /><br />' . LB;

            if ($failed) {

                $display .= '
                <p>' . $LANG_INSTALL[19] . '</p>
                ' . $display_permissions . '<br /><p><strong><span class="error">' . $LANG_INSTALL[20] . '</span></strong>
                ' . $LANG_INSTALL[21] . '</p>
                <br /><br />' . LB;

                $req_string = 'index.php?mode=check_permissions'
                            . '&amp;dbconfig_path=' . urlencode($_PATH['db-config.php'])
                            . '&amp;public_html_path=' . urlencode($_PATH['public_html/'])
                            . '&amp;language=' . $language;
            } else {
                // Set up the request string
                $req_string = 'index.php?mode=write_paths'
                            . '&amp;dbconfig_path=' . urlencode($_PATH['db-config.php'])
                            . '&amp;public_html_path=' . urlencode($_PATH['public_html/'])
                            . '&amp;language=' . $language;
                $migrate_string = 'migrate.php?mode=write_paths'
                            . '&amp;dbconfig_path=' . urlencode($_PATH['db-config.php'])
                            . '&amp;public_html_path=' . urlencode($_PATH['public_html/'])
                            . '&amp;language=' . $language;
            }

            if ($LANG_DIRECTION == 'rtl') {
                $upgr_class = 'upgrade-rtl';
            } else {
                $upgr_class = 'upgrade';
            }

            if ( $failed ) {
                $display .= '
                <div class="install-type-container-outer">
                   <div class="install-type-container-inner">
                       <div class="install floatleft" style="margin-left:10px;margin-bottom:10px;"><a href="' . $req_string
                        . '">' . 'Recheck' . '</a></div>
                   </div>
    			</div>' . LB;
			} else {
                $display .= '
                <div class="install-type-container-outer">
                   <div class="install-type-container-inner">
                       <h2>' . $LANG_INSTALL[23] . '</h2>
                       <div class="install floatleft" style="margin-left:10px;margin-bottom:10px;"><a href="' . $req_string
                        . '&amp;op=install">' . $LANG_INSTALL[24] . '</a></div>
                       <div class="' . $upgr_class . ' floatleft" style="margin-left:10px;"><a href="' . $req_string
                        . '&amp;op=upgrade">' . $LANG_INSTALL[25] . '</a></div>
                       <div class="' . $upgr_class . ' floatleft" style="margin-left:10px;"><a href="' . $migrate_string
                        . '&amp;op=upgrade">' . $LANG_INSTALL[93] . '</a></div>
                   </div>
    			</div>' . LB;
		    }
        }
        break;

    /**
     * Write the GL path to db-config.php
     */
    case 'write_paths':

        // Get the paths from the previous page
        $_PATH = array('db-config.php' => urldecode(isset($_GET['dbconfig_path'])
                                                    ? $_GET['dbconfig_path']
                                                    : $_POST['dbconfig_path']),
                        'public_html/' => urldecode(isset($_GET['public_html_path'])
                                                    ? $_GET['public_html_path']
                                                    : $_POST['public_html_path']));
        $dbconfig_path = str_replace('db-config.php', '', $_PATH['db-config.php'] );

        if (!INST_checkIfWritable(array($_PATH['db-config.php'],
                                            $_PATH['public_html/'] . 'siteconfig.php'))) { // Can't write to db-config.php or siteconfig.php

            $display .= INST_permissionWarning(array($_PATH['db-config.php'],
                                                      $_PATH['public_html/'] . 'siteconfig.php'));

        } else { // Permissions are ok

            // Edit siteconfig.php and enter the correct GL path and system directory path
            $siteconfig_path = $_PATH['public_html/'] . 'siteconfig.php';
            $siteconfig_file = fopen($siteconfig_path, 'r');
            $siteconfig_data = @fread($siteconfig_file, filesize($siteconfig_path));
            fclose($siteconfig_file);

            // $_CONF['path']
            require_once $siteconfig_path;
            $siteconfig_data = str_replace("\$_CONF['path'] = '{$_CONF['path']}';",
                                "\$_CONF['path'] = '" . str_replace('db-config.php', '', $_PATH['db-config.php']) . "';",
                                $siteconfig_data);

            $siteconfig_file = fopen($siteconfig_path, 'w');
            if (!fwrite($siteconfig_file, $siteconfig_data)) {
                exit ($LANG_INSTALL[26] . ' ' . $_PATH['public_html/'] . $LANG_INSTALL[28]);
            }
            fclose ($siteconfig_file);

            // Continue to the next step: Fresh install or Upgrade
            header('Location: index.php?mode=' . $_GET['op'] . '&dbconfig_path=' . urlencode($_PATH['db-config.php']) . '&language=' . $language);

        }
        break;

    /**
     * Start the install/upgrade process
     */
    case 'install' || 'upgrade':

        INST_installEngine($mode, $step);
        break;

}

$display .= '
    <br /><br />
			</div>
        </div> <!-- end of gl_wrapper -->
		<div id="gl_footer">
            <div class="bottom-r-corner">
                <div class="bottom-l-corner"></div>
            </div>
        </div> <!-- end of gl_footer-->
    </div> <!-- end of gl_container -->
</body>
</html>' . LB;

echo $display;

?>