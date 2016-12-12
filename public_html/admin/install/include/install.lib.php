<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | glFusion installation script.                                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Eric Warren            eric AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2007-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Aaron Blankstein  - kantai AT gmail DOT com                     |
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
    die ('This file can not be used on its own.');
}

if (!defined('LB')) {
    define('LB', "\n");
}
if (!defined('SUPPORTED_PHP_VER')) {
    define('SUPPORTED_PHP_VER', '5.3.3');
}
if (!defined('SUPPORTED_MYSQL_VER')) {
    define('SUPPORTED_MYSQL_VER', '5.0.15');
}

if (empty($LANG_DIRECTION)) {
    $LANG_DIRECTION = 'ltr';
}
if ($LANG_DIRECTION == 'rtl') {
    $form_label_dir = 'form-label-right';
    $perms_label_dir = 'perms-label-right';
} else {
    $form_label_dir = 'form-label-left';
    $perms_label_dir = 'perms-label-left';
}

// +---------------------------------------------------------------------------+
// | Functions                                                                 |
// +---------------------------------------------------------------------------+

if (!function_exists('INST_stripslashes') ) {
    if (get_magic_quotes_gpc() == 1) {
        function INST_stripslashes($text) {
            return stripslashes($text);
        }
    } else {
        function INST_stripslashes($text) {
            return $text;
        }
    }
}

//function INST_header($currentAction='',$nextAction='',$prevAction='')
function INST_header($percent_complete)
{
    global $_GLFUSION, $LANG_INSTALL, $LANG_CHARSET;

    $currentStep = isset($_GLFUSION['currentstep']) ? $_GLFUSION['currentstep'] : 'languagetask';

    $header = new TemplateLite('templates/');
    $header->set_file('header','header.thtml');

    $progress_bar = _buildProgressBar($currentStep,$header);

    $header->set_var(array(
        'page_title'        =>  $LANG_INSTALL['install_heading'],
        'charset'           =>  $LANG_CHARSET,
        'language'          =>  $_GLFUSION['language'],
        'wizard_version'    =>  $LANG_INSTALL['wizard_version'],
        'progress_bar'      =>  $progress_bar,
        'percent_complete'  =>  $percent_complete,
    ));

    $header->parse('output','header');
    return $header->finish($header->get_var('output'));
}

function INST_footer()
{
    global $LANG_INSTALL;

    $footer = new TemplateLite('templates/');
    $footer->set_file('footer','footer.thtml');

    $footer->set_var('copyright',$LANG_INSTALL['copyright']);

    $footer->parse('output','footer');
    return $footer->finish($footer->get_var('output'));
}



/**
 * Returns the PHP version
 *
 * Note: Removes appendices like 'rc1', etc.
 *
 * @return array the 3 separate parts of the PHP version number
 *
 */
function php_v()
{
    $phpv = explode('.', phpversion());
    return array($phpv[0], $phpv[1], (int) $phpv[2]);
}

/**
 * Check if the user's PHP version is supported by glFusion
 *
 * @return bool True if supported, falsed if not supported
 *
 */
function INST_phpOutOfDate()
{
    $minv = explode('.', SUPPORTED_PHP_VER);

    $phpv = php_v();

    if (($phpv[0] <  $minv[0]) ||
     (($phpv[0] == $minv[0]) && ($phpv[1] <  $minv[1])) ||
     (($phpv[0] == $minv[0]) && ($phpv[1] == $minv[1]) && ($phpv[2] < $minv[2]))) {

        return true;
    }
    return false;
}

function INST_phpIsGreater($version)
{
    $phpv = php_v();
    $check = explode('.', $version);

    if ( $phpv[0] > $check[0] ) return true;
    if ( $phpv[0] >= $check[0] && $phpv[1] > $check[1] ) return true;
    if ( $phpv[0] >= $check[0] && $phpv[1] >= $check[1] && $phpv[2] >= $check[2]) return true;
    return false;
}

/**
 * Returns the MySQL version
 *
 * @return  mixed   array[0..2] of the parts of the version number or false
 *
 */
function mysql_v($_DB_host, $_DB_user, $_DB_pass)
{
    global $php55;

    if ( $php55 ) {
        if (($res = @mysqli_connect($_DB_host, $_DB_user, $_DB_pass)) === false) {
            return false;
        }
        $mysqlv = @mysqli_get_server_info($res);
    } else {
        if (($res = @mysql_connect($_DB_host, $_DB_user, $_DB_pass)) === false) {
            return false;
        }
        $mysqlv = @mysql_get_server_info();
    }
    if (!empty($mysqlv)) {
        preg_match('/^([0-9]+).([0-9]+).([0-9]+)/', $mysqlv, $match);
        $mysqlmajorv = $match[1];
        $mysqlminorv = $match[2];
        $mysqlrev = $match[3];
    } else {
        $mysqlmajorv = 0;
        $mysqlminorv = 0;
        $mysqlrev = 0;
    }
    @mysql_close($res);

    return array($mysqlmajorv, $mysqlminorv, $mysqlrev);
}

/**
 * Check if the user's MySQL version is supported by glFusion
 *
 * @param   array   $db     Database information
 * @return  bool    True if supported, falsed if not supported
 *
 */
function INST_mysqlOutOfDate($db)
{
    $minv = explode('.', SUPPORTED_MYSQL_VER);

    if ($db['type'] == 'mysql' || $db['type'] == 'mysql-innodb') {
        $myv = mysql_v($db['host'], $db['user'], $db['pass']);
        if (($myv[0] <  $minv[0]) || (($myv[0] == $minv[0]) && ($myv[1] <  $minv[1])) ||
          (($myv[0] == $minv[0]) && ($myv[1] == $minv[1]) && ($myv[2] < $minv[2]))) {
            return true;
        }
    }
    return false;
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
    $utfscore = strstr($filename,'_utf-8');
    if ( $utfscore === false ) {
        $utf = false;
    } else {
        $utf = true;
    }
    $langfile = str_replace('_utf-8', '', $filename);
    $uscore = strpos($langfile, '_');
    if ($uscore === false) {
        $lngname = ucfirst($langfile);
    } else {
        $lngname = ucfirst(substr($langfile, 0, $uscore));
        $lngadd = substr($langfile, $uscore + 1);
        $lngadd = str_replace('utf-8', '', $lngadd);
        $lngadd = str_replace('_', ', ', $lngadd);
        $word = explode(' ', $lngadd);
        $lngadd = '';
        foreach ($word as $w) {
            if (preg_match('/[0-9]+/', $w)) {
                $lngadd .= strtoupper($w) . ' ';
            } else {
                $lngadd .= ucfirst($w) . ' ';
            }
        }
        $lngname .= ' (' . trim($lngadd) . ')';
    }

    if ( $utf ) {
        $lngname .= ' (utf-8)';
    }

    return $lngname;
}

/**
 * Check if a table exists
 * @see DB_checkTableExists
 *
 *
 * @param   string $table   Table name
 * @return  boolean         True if table exists, false if it does not
 *
 */
function INST_checkTableExists($table)
{
    return DB_checkTableExists($table);
}

/**
 * Can the install script connect to the database?
 *
 * @param   array   $db Database information
 * @return  mixed       Returns the DB handle if true, false if not
 *
 */
function INST_dbConnect($db)
{
    global $php55;

    if (empty($db['pass'])) {
        return false;
    }

    $db_handle = false;
    switch ($db['type']) {
    case 'mysql-innodb':
        // deliberate fallthrough - no "break"
    case 'mysql':
        if ($db_handle = @mysql_connect($db['host'], $db['user'], $db['pass'])) {
            return $db_handle;
        }
        break;
    case 'mysqli' :
        if ($db_handle = @mysqli_connect($db['host'], $db['user'], $db['pass'])) {
            return $db_handle;
        }
        break;
    }
    return $db_handle;
}

/**
 * Check if a glFusion database exists
 *
 * @param   array   $db Array containing connection info
 * @return  bool        True if a database exists, false if not
 *
 */
function INST_dbExists($db)
{
    $db_handle = INST_dbConnect($db);
    $db_exists = false;
    switch ($db['type']) {
    case 'mysql':
        if (@mysql_select_db($db['name'], $db_handle)) {
            return true;
        }
        break;
    case 'mysqli':
        if (@mysqli_select_db($db_handle, $db['name'], $db_handle)) {
            return true;
        }
        break;
    }
    return false;
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
* Do a sanity check on the paths and URLs
*
* This is somewhat speculative but should provide the user with a working
* site even if, for example, a site backup was installed elsewhere.
*
* @param    string  $path           proper /path/to/glfusion
* @param    string  $path_html      path to public_html
* @param    string  $site_url       The site's URL
* @param    string  $site_admin_url URL to the admin directory
*
*/
function INST_fixPathsAndUrls($path, $path_html, $site_url, $site_admin_url)
{
    // no "global $_CONF" here!

    if ( !@file_exists($path . 'system/classes/config.class.php') ) {
        echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
        exit;
    }
    require_once $path . 'system/classes/config.class.php';

    $config = config::get_instance();
    $config->set_configfile($path . 'db-config.php');
    $config->load_baseconfig();
    $config->initConfig();
    $_CONF = $config->get_config('Core');

    if (! file_exists($_CONF['path_log'] . 'error.log')) {
        $config->set('path_log', $path . 'logs/');
    }
    if (! file_exists($_CONF['path_language'] . $_CONF['language'] . '.php')) {
        $config->set('path_language', $path . 'language/');
    }
    if (! file_exists($_CONF['backup_path'])) {
        $config->set('backup_path', $path . 'backups/');
    }
    if (! file_exists($_CONF['path_data'])) {
        $config->set('path_data', $path . 'data/');
    }
    if ((! $_CONF['have_pear']) &&
            (! file_exists($_CONF['path_pear'] . 'PEAR.php'))) {
        $config->set('path_pear', $path . 'system/pear/');
    }

    if (! file_exists($_CONF['path_html'] . 'lib-common.php')) {
        $config->set('path_html', $path_html);
    }
    if (! file_exists($_CONF['path_themes'] . $_CONF['theme']
                                            . '/header.thtml')) {
        $config->set('path_themes', $path_html . 'layout/');

        if (! file_exists($path_html . 'layout/' . $_CONF['theme']
                                                 . '/header.thtml')) {
            $config->set('theme', 'cms');
        }
    }
    if (! file_exists($_CONF['path_images'] . 'articles')) {
        $config->set('path_images', $path_html . 'images/');
    }
    if (substr($_CONF['rdf_file'], strlen($path_html)) != $path_html) {
        // this may not be correct but neither was the old value apparently ...
        $config->set('rdf_file', $path_html . 'backend/glfusion.rss');
    }

    if (! empty($site_url) && ($_CONF['site_url'] != $site_url)) {
        $config->set('site_url', $site_url);

        // if we had to fix the site's URL, chances are that cookie domain
        // and path are also wrong and the user won't be able to log in
        $config->set('cookiedomain', '');
        $config->set('cookie_path', '/');
    }
    if (! empty($site_admin_url) &&
            ($_CONF['site_admin_url'] != $site_admin_url)) {
        $config->set('site_admin_url', $site_admin_url);
    }
}

/**
 * Helper function: Derive 'path_html' from __FILE__
 *
 */
function INST_getHtmlPath()
{
    $path = str_replace('\\', '/', __FILE__);
    if ( $path[1] == '/' ) {
        $double = true;
    } else {
        $double = false;
    }
    $path = str_replace('//', '/', $path);
    $parts = explode('/', $path);
    $num_parts = count($parts);
    if (($num_parts < 3) || ($parts[$num_parts - 1] != 'install.lib.php')) {
        die('Fatal error - can not figure out my own path');
    }
    $returnPath = implode('/', array_slice($parts, 0, $num_parts - 4)) . '/';
    if ( $double ) {
        $returnPath = '/'.$returnPath;
    }
    return $returnPath;
}

/**
 * Helper function: Derive path of the 'admin' directory from __FILE__
 *
 */
function INST_getAdminPath()
{
    $path = str_replace('\\', '/', __FILE__);
    if ( $path[1] == '/' ) {
        $double = true;
    } else {
        $double = false;
    }
    $path = str_replace('//', '/', $path);
    $parts = explode('/', $path);
    $num_parts = count($parts);
    if (($num_parts < 3) || ($parts[$num_parts - 1] != 'install.lib.php')) {
        die('Fatal error - can not figure out my own path');
    }
    $returnPath = implode('/', array_slice($parts, 0, $num_parts - 3)) . '/';
    if ( $double ) {
        $returnPath = '/'.$returnPath;
    }
    return $returnPath;
}

/**
 * Helper function: Derive 'site_url' from PHP_SELF
 *
 */
function INST_getSiteUrl()
{
    $url = str_replace('//', '/', $_SERVER['PHP_SELF']);
    $parts = explode('/', $url);
    $num_parts = count($parts);
    if (($num_parts < 3) || (substr($parts[$num_parts - 1], -4) != '.php')) {
        die('Fatal error - can not figure out my own URL');
    }

    $url = implode('/', array_slice($parts, 0, $num_parts - 3));

    return 'http://' . $_SERVER['HTTP_HOST'] . $url;
}

/**
 * Helper function: Derive 'site_admin_url' from PHP_SELF
 *
 */
function INST_getSiteAdminUrl()
{
    $url = str_replace('//', '/', $_SERVER['PHP_SELF']);
    $parts = explode('/', $url);
    $num_parts = count($parts);
    if (($num_parts < 3) || (substr($parts[$num_parts - 1], -4) != '.php')) {
        die('Fatal error - can not figure out my own URL');
    }

    $url = implode('/', array_slice($parts, 0, $num_parts - 2));

    return 'http://' . $_SERVER['HTTP_HOST'] . $url;
}


/**
 * Check InnoDB Upgrade
 *
 * @param   array   $_SQL   List of SQL queries
 * @return  array           InnoDB table style if chosen
 *
 */
function INST_checkInnodbUpgrade($_SQL,$use_innodb)
{
    global $_GLFUSION;

    $use_innodb = $_GLFUSION['innodb'];

    if ($use_innodb) {
        $statements = count($_SQL);
        for ($i = 0; $i < $statements; $i++) {
            $_SQL[$i] = str_replace('MyISAM', 'InnoDB', $_SQL[$i]);
        }
    }

    return $_SQL;
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

    switch ( $_DB_dbms ) {
        case 'mysql' :
        case 'mysqli' :
        default :
            $dbDriver = 'mysql';
            break;
    }

    $_DB->setDisplayError (true);

    // Because the create table syntax can vary from dbms-to-dbms we are
    // leaving that up to each database driver (e.g. mysql.class.php,
    // postgresql.class.php, etc)

    // Get DBMS-specific create table array and data array
    if ( !@file_exists($_CONF['path'] . 'sql/' . $dbDriver . '_tableanddata.php') ) {
        echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
        exit;
    }
    if ( $dbDriver == 'mysql' || $dbDriver == 'mysqli' ) $sqldatafile = 'mysql';
    require_once $_CONF['path'] . 'sql/' . $sqldatafile . '_tableanddata.php';

    $progress = '';
    $errors = '';
    $rc = true;

    if (INST_checkTableExists ('access')) {
        return array(false,$LANG_ISNTALL['database_exists']);
    }

    switch($_DB_dbms){
        case 'mysql':
        case 'mysqli' :
            list($rc,$errors) = INST_updateDB($_SQL,$use_innodb);
            if ( $rc != true ) {
                return array($rc,$errors);
            }
            if ($use_innodb) {
                DB_query ("INSERT INTO {$_TABLES['vars']} (name, value) VALUES ('database_engine', 'InnoDB')");
            }
            break;
    }

    // Now insert mandatory data and a small subset of initial data
    foreach ($_DATA as $data) {
        $progress .= "executing " . $data . "<br />\n";
        DB_query ($data,1);
        if ( DB_error() ) {
            $errors .= $data . '<br>'. DB_error() . "<br />\n";
            $rc = false;
        }
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

    if (($_DB_dbms == 'mysql') || ($_DB_dbms == 'mysqli')) {

        // let's try and personalize the Admin account a bit ...

        if (!empty($site_mail)) {
            if (strpos($site_mail, 'example.com') === false) {
                DB_query("UPDATE {$_TABLES['users']} SET email = '" . addslashes($site_mail) . "' WHERE uid = 2");
            }
        }
        if (!empty($site_url)) {
            if (strpos($site_url, 'example.com') === false) {
                DB_query("UPDATE {$_TABLES['users']} SET homepage = '" . addslashes($site_url) . "' WHERE uid = 2");
            }
        }
    }
}

/**
 * Run all the database queries from the update file.
 *
 * @param   array $_SQL   Array of queries
 *
 */
function INST_updateDB($_SQL,$use_innodb)
{
    global $_DB, $_DB_dbms;

    $_DB->setDisplayError (true);
    $errors = '';
    $rc = true;

    $_SQL = INST_checkInnodbUpgrade($_SQL,$use_innodb);
    foreach ($_SQL as $sql) {
        DB_query($sql,1);
        if ( DB_error() ) {
            $errors .= $sql . '<br>' . DB_error() . '<br />' . LB;
            $rc = false;
        }
    }
    return array($rc,$errors);
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
    global $_TABLES, $_CONF, $_SYSTEM, $_SP_CONF, $_DB, $_DB_dbms, $_DB_table_prefix,
           $LANG_AM, $dbconfig_path, $siteconfig_path, $html_path,$LANG_INSTALL;
    global $_GLFUSION;

    $rc = true;
    $errors = '';

    $_DB->setDisplayError (true);

    // Because the upgrade sql syntax can vary from dbms-to-dbms we are
    // leaving that up to each glFusion database driver

    $progress = '';

    switch ($current_fusion_version) {
        case '1.0.0':
        case '1.0.1':
        case '1.0.2':
            $_SQL = array();
            if ( !@file_exists($_CONF['path'] . 'sql/updates/mysql_1.0.1_to_1.1.0.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path'] . 'sql/updates/mysql_1.0.1_to_1.1.0.php';
            list($rc,$errors) = INST_updateDB($_SQL);
            if ( $rc === false ) {
                return array($rc,$errors);
            }

            // index cleanup...
            $_SQLi = array();
            $_SQLi[] = "ALTER TABLE {$_TABLES['blocks']} DROP INDEX blocks_bid";
            $_SQLi[] = "ALTER TABLE {$_TABLES['events']} DROP INDEX events_eid";
            $_SQLi[] = "ALTER TABLE {$_TABLES['group_assignments']} DROP INDEX ug_main_grp_id";
            $_SQLi[] = "ALTER TABLE {$_TABLES['sessions']} DROP INDEX sess_id";
            $_SQLi[] = "ALTER TABLE {$_TABLES['stories']} DROP INDEX stories_sid";
            $_SQLi[] = "ALTER TABLE {$_TABLES['userindex']} DROP INDEX userindex_uid";
            if ( isset($_TABLES['polltopics']) ) {
                $_SQLi[] = "ALTER TABLE {$_TABLES['polltopics']} DROP INDEX pollquestions_pid";
            }
            foreach ($_SQLi as $sqli) {
                $rc = DB_query($sqli,1);
            }
            $_SQLi = array();
            if ( !@file_exists($_CONF['path_system'].'classes/config.class.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();

            $c->add('comment_code',0,'select',4,21,17,1670,TRUE);
            $c->add('comment_edit',0,'select',4,21,0,1680,TRUE);
            $c->add('comment_edittime',1800,'text',4,21,NULL,1690,TRUE);
            $c->add('article_comment_close_days',30,'text',4,21,NULL,1700,TRUE);
            $c->add('comment_close_rec_stories',0,'text',4,21,NULL,1710,TRUE);

            $c->add('image_lib','gdlib','select',5,22,10,1450,TRUE);
            $c->add('jhead_enabled',0,'select',5,22,0,1480,TRUE);
            $c->add('path_to_jhead','','text',5,22,NULL,1490,TRUE);
            $c->add('jpegtrans_enabled',0,'select',5,22,0,1500,TRUE);
            $c->add('path_to_jpegtrans','','text',5,22,NULL,1510,TRUE);

            $c->add('hide_adminmenu',TRUE,'select',3,12,1,1170,TRUE);

            $c->add('fs_search', NULL, 'fieldset', 0, 6, NULL, 0, TRUE);
            $c->add('search_style','google','select',0,6,18,650,TRUE);
            $c->add('search_limits','10,15,25,30','text',0,6,NULL,660,TRUE);
            $c->add('num_search_results',25,'text',0,6,NULL,670,TRUE);
            $c->add('search_show_limit',TRUE,'select',0,6,1,680,TRUE);
            $c->add('search_show_sort',TRUE,'select',0,6,1,690,TRUE);
            $c->add('search_show_num',TRUE,'select',0,6,1,700,TRUE);
            $c->add('search_show_type',TRUE,'select',0,6,1,710,TRUE);
            $c->add('search_show_user',TRUE,'select',0,6,1,720,TRUE);
            $c->add('search_show_hits',TRUE,'select',0,6,1,730,TRUE);
            $c->add('search_no_data','<i>Not available...</i>','text',0,6,NULL,740,TRUE);
            $c->add('search_separator',' &gt; ','text',0,6,NULL,750,TRUE);
            $c->add('search_def_keytype','phrase','select',0,6,19,760,TRUE);
            $c->add('default_search_order','date','select',0,6,22,770,TRUE);
            $c->add('search_use_fulltext',FALSE,'hidden',0,6);
            $c->add('mail_backend','mail','select',0,1,20,60,TRUE);
            $c->add('mail_sendmail_path','','text',0,1,NULL,70,TRUE);
            $c->add('mail_sendmail_args','','text',0,1,NULL,80,TRUE);
            $c->add('mail_smtp_host','','text',0,1,NULL,90,TRUE);
            $c->add('mail_smtp_port','','text',0,1,NULL,100,TRUE);
            $c->add('mail_smtp_auth',FALSE,'select',0,1,0,110,TRUE);
            $c->add('mail_smtp_username','','text',0,1,NULL,120,TRUE);
            $c->add('mail_smtp_password','','text',0,1,NULL,130,TRUE);
            $c->add('mail_smtp_secure','none','select',0,1,21,140,TRUE);
            $c->add('compress_css',TRUE,'select',2,11,0,1370,TRUE);
            $c->add('allow_embed_object',TRUE,'select',7,34,1,1720,TRUE);
            $c->add('digg_enabled',1,'select',1,7,0,1235,TRUE);
            // now delete the old setting - we don't want it anymore...
            $c->del('mail_settings','Core');
            $c->del('use_safe_html','Core');
            $c->del('user_html','Core');
            $c->del('admin_html','Core');
            $c->del('allowed_protocols','Core');
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.0',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.0' WHERE name='glfusion'",1);
            $current_fusion_version = '1.1.0';
            $_SQL = array();
        case '1.1.0' :
        case '1.1.1' :
            if ( !@file_exists($_CONF['path_system'].'classes/config.class.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('story_submit_by_perm_only',0,'select',4,20,0,780,TRUE);
            $c->add('use_from_site_mail',0,'select',0,1,0,150,TRUE);
            $c->del('pdf_enabled','Core');
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.2',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.2' WHERE name='glfusion'",1);
            $current_fusion_version = '1.1.2';
        case '1.1.2' :
            $_SQL = array();
            if ( !@file_exists($_CONF['path'] . 'sql/updates/mysql_1.1.2_to_1.1.3.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path'] . 'sql/updates/mysql_1.1.2_to_1.1.3.php';
            list($rc,$errors) = INST_updateDB($_SQL);
            if ( $rc === false ) {
                return array($rc,$errors);
            }
            if ( !@file_exists($_CONF['path_system'].'classes/config.class.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();

            $c->add('hidestorydate',0,'select',1,7,0,1205,TRUE);

            $c->add('fs_caching', NULL, 'fieldset', 2, 12, NULL, 0, TRUE);
            $c->add('cache_templates',1,'select',2,12,0,1375,TRUE);
            $c->add('template_comments',FALSE,'select',2,11,0,1373,TRUE);

            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.3',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.3' WHERE name='glfusion'",1);
            $current_fusion_version = '1.1.3';
        case '1.1.3' :
            $_SQL = array();
            if ( !@file_exists($_CONF['path'] . 'sql/updates/mysql_1.1.3_to_1.1.4.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path'] . 'sql/updates/mysql_1.1.3_to_1.1.4.php';
            list($rc,$errors) = INST_updateDB($_SQL);
            if ( $rc === false ) {
                return array($rc,$errors);
            }
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.4',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.4' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.1.4';
        case '1.1.4' :
            DB_query("ALTER TABLE {$_TABLES['stories']} DROP INDEX stories_in_transit",1);
            DB_query("ALTER TABLE {$_TABLES['stories']} DROP COLUMN in_transit",1);
            DB_query("ALTER TABLE {$_TABLES['userprefs']} ADD search_result_format VARCHAR( 48 ) NOT NULL DEFAULT 'google'",1);
            DB_query("UPDATE {$_TABLES['conf_values']} SET type='text' WHERE name='mail_smtp_host'",1);
            DB_query("UPDATE {$_TABLES['conf_values']} SET selectionArray='23' WHERE name='censormode'",1);
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.5',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.5' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);

            if ( !@file_exists($_CONF['path_system'].'classes/config.class.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('hide_exclude_content',0,'select',4,16,0,295,TRUE);
            $c->add('maintenance_mode',0,'select',0,0,0,520,TRUE);
            $c->del('search_show_limit', 'Core');
            $c->del('search_show_sort', 'Core');

            $_SQL = array();
            if ( !@file_exists($_CONF['path'] . 'sql/updates/mysql_1.1.4_to_1.1.5.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path'] . 'sql/updates/mysql_1.1.4_to_1.1.5.php';
            list($rc,$errors) = INST_updateDB($_SQL);
            if ( $rc === false ) {
                return array($rc,$errors);
            }
            $current_fusion_version = '1.1.5';
        case '1.1.5' :
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.6',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.6' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.1.6';
        case '1.1.6' :
            $_SQL = array();
            if ( !@file_exists($_CONF['path'] . 'sql/updates/mysql_1.1.6_to_1.1.7.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path'] . 'sql/updates/mysql_1.1.6_to_1.1.7.php';
            list($rc,$errors) = INST_updateDB($_SQL);
            if ( $rc === false ) {
                return array($rc,$errors);
            }

            if ( !@file_exists($_CONF['path_system'].'classes/config.class.php') ) {
                echo _displayError(FILE_INCLUDE_ERROR,'pathsetting');
                exit;
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('rating_enabled',1,'select',1,7,24,1237,TRUE);

            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.7',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.7' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.1.7';
        case '1.1.7' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('user_reg_fullname',1,'select',4,19,25,980,TRUE);
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.1.8',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.1.8' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.1.8';
        case '1.1.8' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('article_comment_close_enabled',0,'select',4,21,0,1695,TRUE);
            $session_ip_check = 1;
            if ( isset($_SYSTEM['skip_ip_check']) && $_SYSTEM['skip_ip_check'] == 1 ) {
                $session_ip_check = 0;
            }
            $c->add('session_ip_check',$session_ip_check,'select',7,30,26,545,TRUE);
            $c->del('default_search_order','Core');
            DB_query("UPDATE {$_TABLES['conf_values']} SET selectionArray = '0' WHERE  name='searchloginrequired' AND group_name='Core'");

            DB_query("ALTER TABLE {$_TABLES['groups']} ADD grp_default tinyint(1) unsigned NOT NULL default '0' AFTER grp_gl_core");
            DB_query("ALTER TABLE {$_TABLES['users']} CHANGE `passwd` `passwd` VARCHAR( 40 ) NOT NULL default ''");

            // clean up group names and assign proper admin setting
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='Bad Behavior2 Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_name='calendar Admin' WHERE grp_name='Calendar Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='calendar Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='filemgmt Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='forum Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_name='links Admin' WHERE grp_name='Links Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='links Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='mediagallery Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_name='polls Admin' WHERE grp_name='Polls Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='polls Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='sitetailor Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_name='staticpages Admin' WHERE grp_name='Static Page Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='staticpages Admin'",1);
            DB_query("UPDATE {$_TABLES['groups']} SET grp_gl_core=2 WHERE grp_name='spamx Admin'",1);

            // move multi-language support to its own fieldset
            DB_query("INSERT INTO {$_TABLES['conf_values']} (name,value,type,group_name,default_value,subgroup,selectionArray,sort_order,fieldset) VALUES ('fs_mulitlanguage','N;','fieldset','Core','N;',6,-1,0,41)",1);
            DB_query("UPDATE {$_TABLES['conf_values']} SET fieldset='41' WHERE name='language_files' AND group_name='Core'",1);
            DB_query("UPDATE {$_TABLES['conf_values']} SET fieldset='41' WHERE name='languages' AND group_name='Core'",1);

            // topic sort
            DB_query("ALTER TABLE {$_TABLES['topics']} ADD sort_by TINYINT(1) NOT NULL DEFAULT '0' AFTER archive_flag",1);
            DB_query("ALTER TABLE {$_TABLES['topics']} ADD sort_dir CHAR( 4 ) NOT NULL DEFAULT 'DESC' AFTER sort_by",1);

            // new stats.view permission
            DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('stats.view','Allows access to the Stats page.',0)",1);
            $ft_id = DB_insertId();
            $all_grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'All Users'"));
            $loggedin_grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Logged-in Users'"));
            $root_grp_id = intval(DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'"));
            if ( $_CONF['statsloginrequired'] || $_CONF['loginrequired'] ) {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $loggedin_grp_id)", 1);
            } else {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $all_grp_id)", 1);
            }
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $root_grp_id)", 1);
            $c->del('statsloginrequired','Core');

            $c->add('registration_type',0,'select',4,19,27,785,TRUE,'Core');
            DB_query("ALTER TABLE {$_TABLES['users']} ADD act_token VARCHAR(32) NOT NULL DEFAULT '' AFTER pwrequestid",1);
            DB_query("ALTER TABLE {$_TABLES['users']} ADD act_time DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00.000000' AFTER act_token",1);

            $c->del('cookie_ip','Core');
            DB_query("ALTER TABLE {$_TABLES['sessions']} DROP PRIMARY KEY",1);
            DB_query("ALTER TABLE {$_TABLES['sessions']} ADD PRIMARY KEY (md5_sess_id)",1);

            $c->add('comment_postmode','plaintext','select',4,21,5,1693,TRUE);
            $c->add('comment_editor',0,'select',4,21,28,1694,TRUE);

            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.2.0',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.2.0' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.2.0';
        case '1.2.0' :
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.2.1',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.2.1' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.2.1';
        case '1.2.1' :
        case '1.2.2' :
        case '1.2.3' :
            require_once $_CONF['path'] . 'sql/updates/mysql_1.2.2_to_1.3.0.php';
            list($rc,$errors) = INST_updateDB($_SQL);
            if ( $rc === false ) {
                return array($rc,$errors);
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            // logo
            $c->add('fs_logo', NULL, 'fieldset', 5, 28, NULL, 0, TRUE);
            $c->add('max_logo_height',150,'text',5,28,NULL,1630,TRUE);
            $c->add('max_logo_width', 500,'text',5,28,NULL,1640,TRUE);
            // whats new cache time
            $c->add('whatsnew_cache_time',3600,'text',3,15,NULL,1060,TRUE);
            // add user photo option to whosonline block
            $c->add('whosonline_photo',FALSE,'select',3,14,0,930,TRUE);
            // remove old wikitext configuration
            $c->del('wikitext_editor','Core');
            // add oauth user_login_method
            $c->del('user_login_method', 'Core');
            // delete microsummary
            $c->del('microsummary_short', 'Core');
            $standard = ($_CONF['user_login_method']['standard']) ? true : false;
            $thirdparty = ($_CONF['user_login_method']['3rdparty']) ? true: false;

            // OAuth configuration settings
            $oauth = false;
            $c->add('user_login_method',array('standard' => $standard , '3rdparty' => $thirdparty , 'oauth' => $oauth),'@select',4,16,1,320,TRUE);
            $c->add('facebook_login',0,'select',4,16,1,330,TRUE);
            $c->add('facebook_consumer_key','not configured yet','text',4,16,NULL,335,TRUE);
            $c->add('facebook_consumer_secret','not configured yet','text',4,16,NULL,340,TRUE);
            $c->add('linkedin_login',0,'select',4,16,1,345,TRUE);
            $c->add('linkedin_consumer_key','not configured yet','text',4,16,NULL,350,TRUE);
            $c->add('linkedin_consumer_secret','not configured yet','text',4,16,NULL,355,TRUE);
            $c->add('twitter_login',0,'select',4,16,1,360,TRUE);
            $c->add('twitter_consumer_key','not configured yet','text',4,16,NULL,365,TRUE);
            $c->add('twitter_consumer_secret','not configured yet','text',4,16,NULL,370,TRUE);
            $c->add('google_login',0,'select',4,16,1,375,TRUE);
            $c->add('google_consumer_key','not configured yet','text',4,16,NULL,380,TRUE);
            $c->add('google_consumer_secret','not configured yet','text',4,16,NULL,385,TRUE);
            $c->add('microsoft_login',0,'select',4,16,1,390,TRUE);
            $c->add('microsoft_consumer_key','not configured yet','text',4,16,NULL,395,TRUE);
            $c->add('microsoft_consumer_secret','not configured yet','text',4,16,NULL,400,TRUE);

            // date / time format changes
            $c->add('date','l, F d Y @ h:i A T','text',6,29,NULL,370,TRUE);
            $c->add('daytime','m/d h:iA','text',6,29,NULL,380,TRUE);
            $c->add('shortdate','m/d/y','text',6,29,NULL,390,TRUE);
            $c->add('dateonly','d-M','text',6,29,NULL,400,TRUE);
            $c->add('timeonly','H:iA','text',6,29,NULL,410,TRUE);
            // hide what's new if empty
            $c->add('hideemptyblock',0,'select',3,15,0,1045,TRUE);
            // update check
            $c->add('fs_update', NULL, 'fieldset', 0, 7, NULL, 0, TRUE);
            $c->add('update_check_interval','86400','select',0,7,29,765,TRUE);
            $c->add('send_site_data',TRUE,'select',0,7,1,770,TRUE);

            // rating
            $c->add('fs_rating',NULL, 'fieldset', 4,7,NULL,0,TRUE);
            $c->add('rating_speedlimit',15,'text',4,7,NULL,10,TRUE);

            // add new logo.admin permission
            $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='logo.admin'");
            if ( DB_numRows($result) == 0 ) {
                DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('logo.admin','Ability to modify site logo',1)",1);
                $ft_id  = DB_insertId();
                $grp_id = (int) DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'");
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
            }

            // add new menu.admin permission
            $result = DB_query("SELECT * FROM {$_TABLES['features']} WHERE ft_name='menu.admin'");
            if ( DB_numRows($result) == 0 ) {
                DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('menu.admin','Ability to create/edit site menus',1)",1);
                $ft_id  = DB_insertId();
                $grp_id = (int) DB_getItem($_TABLES['groups'],'grp_id',"grp_name = 'Root'");
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($ft_id, $grp_id)", 1);
            }

            // add new autotag features
            $autotag_admin_ft_id = 0;
            $autotag_php_ft_id   = 0;
            $autotag_group_id    = 0;

            $tmp_admin_ft_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = 'autotag.admin'");
            if (empty ($tmp_admin_ft_id)) {
                DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('autotag.admin','Ability to create / edit autotags',1)",1);
                $autotag_admin_ft_id  = DB_insertId();
            }
            $tmp_php_ft_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = 'autotag.PHP'");
            if (empty ($tmp_php_ft_id)) {
                DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('autotag.PHP','Ability to create / edit autotags utilizing PHP functions',1)",1);
                $autotag_php_ft_id  = DB_insertId();
            }
            // now check for the group
            $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Autotag Admin'");
            if ( DB_numRows($result) == 0 ) {
                DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core, grp_default) VALUES ('Autotag Admin','Has full access to create and modify autotags',1,0)");
                $autotag_group_id  = DB_insertId();
            }
            if ( $autotag_admin_ft_id != 0 && $autotag_group_id != 0 ) {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (".$autotag_admin_ft_id.",".$autotag_group_id.")");
            }
            if ( $autotag_php_ft_id != 0 && $autotag_group_id != 0 ) {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (".$autotag_php_ft_id.",".$autotag_group_id.")");
            }
            if ( $autotag_group_id != 0 ) {
                DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id,ug_grp_id) VALUES (".$autotag_group_id.",1)");
            }

            DB_query("INSERT INTO {$_TABLES['vars']} SET value='1.3.0',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='1.3.0' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            $current_fusion_version = '1.3.0';
        case '1.3.0' :
            $current_fusion_version = '1.3.1';
        case '1.3.1' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $current_fusion_version = '1.3.2';
        case '1.3.2' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            // remove menu_elements - no longer used
            $c->del('menu_elements','Core');
            $c->del('mailstory_postmode','Core');
            $c->del('comment_editor','Core');
            $c->del('advanced_editor','Core');
            if ( !isset($_CONF['mailuser_postmode'] ) ) {
                $c->add('mailuser_postmode','html','select',4,5,5,43,TRUE);
            }
            // set the initial set of html elements
            if ( !isset($_CONF['htmlfilter_comment']) ) {
                $c->add('htmlfilter_default','p,b,a,i,strong,em,br','text',7,5,NULL,30,true);
                $c->add('htmlfilter_comment','p,b,a,i,strong,em,br,tt,hr,li,ol,ul,code,pre','text',7,5,NULL,35,TRUE);
                $c->add('htmlfilter_story','div[class],h1,h2,h3,pre,br,p[style],b[style],s,strong[style],i[style],em[style],u[style],strike,a[style|href|title|target],ol[style|class],ul[style|class],li[style|class],hr[style],blockquote[style],img[style|alt|title|width|height|src|align],table[style|width|bgcolor|align|cellspacing|cellpadding|border],tr[style],td[style],th[style],tbody,thead,caption,col,colgroup,span[style|class],sup,sub','text',7,5,NULL,40,TRUE);
                $c->add('htmlfilter_root','div[style|class],span[style|class],table,tr,td,th','text',7,5,NULL,50,TRUE);
            }
            $sql = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('youtube', 'Embed Youtube videos into content. Usage:[youtube:ID height:px width:px align:left/right/center pad:px]', 1, 1, NULL)";
            DB_query($sql,1);

            $current_fusion_version = '1.4.0';
        case '1.4.0' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('github_login',0,'select',4,1,1,271,TRUE);
            $c->add('github_consumer_key','not configured yet','text',4,1,NULL,272,TRUE);
            $c->add('github_consumer_secret','not configured yet','text',4,1,NULL,273,TRUE);

            $current_fusion_version = '1.4.1';
        case '1.4.1' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $current_fusion_version = '1.4.2';
        case '1.4.2' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('min_username_length','4','text',4,4,NULL,60,TRUE);
            $current_fusion_version = '1.4.3';
        case '1.4.3' :
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `alternate_tid` VARCHAR(20) NULL DEFAULT NULL AFTER `tid`, ADD INDEX `alternate_topic` (`alternate_tid`) ;";
            $_SQL[] = "ALTER TABLE {$_TABLES['tokens']} CHANGE `urlfor` `urlfor` VARCHAR( 1024 ) NOT NULL";
            $_SQL[] = "ALTER TABLE {$_TABLES['comments']} CHANGE  `ipaddress`  `ipaddress` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
            $_SQL[] = "ALTER TABLE {$_TABLES['rating_votes']} CHANGE  `ip_address`  `ip_address` VARCHAR( 45 ) NOT NULL";
            $_SQL[] = "ALTER TABLE {$_TABLES['sessions']} CHANGE  `remote_ip`  `remote_ip` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
            $_SQL[] = "ALTER TABLE {$_TABLES['trackback']}  `ipaddress`  `ipaddress` VARCHAR( 45 ) NOT NULL DEFAULT  ''";
            $_SQL[] = "ALTER TABLE {$_TABLES['users']} CHANGE  `remote_ip`  `remote_ip` VARCHAR( 45 ) NOT NULL DEFAULT  ''";

            $_SQL[] = "ALTER TABLE {$_TABLES['topics']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['topics']} CHANGE `topic` `topic` VARCHAR(128) NULL DEFAULT NULL;";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT 'General';";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `alternate_tid` `alternate_tid` VARCHAR(128) NULL DEFAULT NULL;";
            $_SQL[] = "ALTER TABLE {$_TABLES['blocks']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT 'All';";
            $_SQL[] = "ALTER TABLE {$_TABLES['storysubmission']} CHANGE `tid` `tid` VARCHAR(128) NOT NULL DEFAULT 'General';";

            foreach ($_SQL as $sql) {
                DB_query($sql,1);
            }

            $result = DB_query("SELECT * FROM {$_TABLES['autotags']} WHERE tag='uikitlogin'");
            if ( DB_numRows($result) < 1 ) {
                $sql = "INSERT INTO {$_TABLES['autotags']} (`tag`, `description`, `is_enabled`, `is_function`, `replacement`) VALUES ('uikitlogin', 'UIKit Login Widget', '1', '1', NULL);";
                DB_query($sql,1);
            }
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();

            $current_fusion_version = '1.5.0';

        case '1.5.0' :
            $_SQL[] = "ALTER TABLE {$_TABLES['article_images']} CHANGE `ai_sid` `ai_sid` VARCHAR(128);";
            $_SQL[] = "ALTER TABLE {$_TABLES['comments']} CHANGE `sid` `sid` VARCHAR(128);";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} CHANGE `sid` `sid` VARCHAR(128);";
            $_SQL[] = "ALTER TABLE {$_TABLES['storysubmission']} CHANGE `sid` `sid` VARCHAR(128);";
            $_SQL[] = "ALTER TABLE {$_TABLES['syndication']} CHANGE `topic` `topic` VARCHAR(128);";
            $_SQL[] = "ALTER TABLE {$_TABLES['trackback']} CHANGE `sid` `sid` VARCHAR(128);";

            foreach ($_SQL as $sql) {
                DB_query($sql,1);
            }

            $current_fusion_version = '1.5.1';

        case '1.5.1' :
        case '1.5.2' :

            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->add('infinite_scroll',1,'select',1,1,0,25,TRUE);
            $c->add('comment_engine','internal','select',4,6,30,1,TRUE);
            $c->add('comment_disqus_shortname','not defined','text',4,6,NULL,2,TRUE);
            $c->add('comment_fb_appid','not defined','text',4,6,NULL,3,TRUE);
            $c->add('social_site_extra','', 'text',0,0,NULL,1,TRUE,'social_internal');
            $c->add('fb_appid','','text',0,0,NULL,90,TRUE);

            // remove openid
            $sql = "SELECT * FROM {$_TABLES['conf_values']} WHERE name='user_login_method' AND group_name='Core'";
            $result = DB_query($sql,1);
            if ( DB_numRows($result)  > 0 ) {
                $row        = DB_fetchArray($result);
                $methods    = @unserialize($row['value']);
                $standard   = ($methods['standard']) ? true : false;
                $thirdparty = ($methods['3rdparty']) ? true: false;
                $oauth      = ($methods['oauth']) ? true: false;

                if ( $standard === false && $thirdparty === false && $oauth === false ) {
                    $standard = true;
                }

                $c->del('user_login_method', 'Core');
                $c->add('user_login_method',array('standard' => $standard , '3rdparty' => $thirdparty , 'oauth' => $oauth),'@select',4,1,1,120,TRUE);
            }

            DB_query("ALTER TABLE {$_TABLES['subscriptions']} DROP INDEX `type`",1);
            DB_query("DROP INDEX `trackback_url` ON {$_TABLES['trackback']};",1);

            $_SQL = array();

            $_SQL[] = "ALTER TABLE {$_TABLES['sessions']} CHANGE `md5_sess_id` `md5_sess_id` VARCHAR(128) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `subtitle` VARCHAR(128) DEFAULT NULL AFTER `title`;";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `story_image` VARCHAR(128) DEFAULT NULL AFTER `alternate_tid`;";
            $_SQL[] = "UPDATE {$_TABLES['plugins']} SET pi_enabled='0' WHERE pi_name='ban'";
            $_SQL[] = "ALTER TABLE {$_TABLES['autotags']} CHANGE `description` `description` VARCHAR(250) NULL DEFAULT '';";
            $_SQL[] = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('vimeo', 'Embed Vimeo videos into content. Usage:[vimeo:ID height:PX width:PX align:LEFT/RIGHT pad:PX responsive:0/1]', 1, 1, NULL)";
            $_SQL[] = "REPLACE INTO {$_TABLES['autotags']} (tag, description, is_enabled, is_function, replacement) VALUES ('newimage', 'HTML: embeds new images in flexible grid. usage: [newimage:<i>#</i> - How many images to display <i>truncate:0/1</i> - 1 = truncate number of images to keep square grid <i>caption:0/1</i> 1 = include title]', 1, 1, '');";
            $_SQL[] = "ALTER TABLE {$_TABLES['rating']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['rating_votes']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['subscriptions']} CHANGE `id` `id` VARCHAR(128) NOT NULL DEFAULT '';";

            $_SQL[] = "CREATE TABLE `{$_TABLES['social_share']}` (
              `id` varchar(128) NOT NULL DEFAULT '',
              `name` varchar(128) NOT NULL DEFAULT '',
              `display_name` varchar(128) NOT NULL DEFAULT '',
              `icon` varchar(128) NOT NULL DEFAULT '',
              `url` varchar(128) NOT NULL DEFAULT '',
              `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
              PRIMARY KEY (id)
            ) ENGINE=MyISAM;
            ";

            $_SQL[] = "CREATE TABLE {$_TABLES['social_follow_services']} (
              `ssid` int(10) UNSIGNED NOT NULL auto_increment,
              `url` varchar(128) NOT NULL DEFAULT '',
              `enabled` tinyint(1) NOT NULL DEFAULT '1',
              `icon` varchar(128) NOT NULL,
              `service_name` varchar(128) NOT NULL,
              `display_name` varchar(128) NOT NULL,
              UNIQUE KEY `ssid` (`ssid`),
              UNIQUE KEY `service_name` (`service_name`)
            ) ENGINE=MyISAM;";

            $_SQL[] = "CREATE TABLE {$_TABLES['social_follow_user']} (
              `suid` int(10) NOT NULL AUTO_INCREMENT,
              `ssid` int(11) NOT NULL DEFAULT '0',
              `uid` int(11) NOT NULL,
              `ss_username` varchar(128) NOT NULL DEFAULT '',
              UNIQUE KEY `suid` (`suid`),
              UNIQUE KEY `ssid` (`ssid`,`uid`)
            ) ENGINE=MyISAM;";

            $_SQL[] = "ALTER TABLE {$_TABLES['rating']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['rating_votes']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['subscriptions']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';";
            $_SQL[] = "ALTER TABLE {$_TABLES['logo']} CHANGE `config_name` `config_name` varchar(128) DEFAULT NULL;";

            list($rc,$errors) = INST_updateDB($_SQL);

            $_DATA = array();

            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('fb', 'facebook', 'Facebook', 'facebook', 'http://www.facebook.com/sharer.php?s=100', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('gg', 'google-plus', 'Google+', 'google-plus', 'https://plus.google.com/share?url', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('li', 'linkedin', 'LinkedIn', 'linkedin', 'http://www.linkedin.com', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('lj', 'livejournal', 'Live Journal', 'pencil', 'http://www.livejournal.com', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('mr', 'mail-ru', 'Mail.ru', 'at', 'http://mail-ru.com', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('ok', 'odnoklassniki', 'Odnoklassniki', 'odnoklassniki', 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('pt', 'pinterest', 'Pinterest', 'pinterest-p', 'http://www.pinterest.com', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('rd', 'reddit', 'reddit', 'reddit-alien', 'http://reddit.com/submit?url=%%u&title=%%t', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('tw', 'twitter', 'Twitter', 'twitter', 'http://www.twitter.com', 1);";
            $_DATA[] = "INSERT INTO `{$_TABLES['social_share']}` (`id`, `name`, `display_name`, `icon`, `url`, `enabled`) VALUES('vk', 'vk', 'vk', 'vk', 'http://www.vk.org', 1);";

            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(1, 'https://twitter.com/%%u', 1, 'twitter', 'twitter', 'Twitter');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(2, 'http://facebook.com/%%u', 1, 'facebook', 'facebook', 'Facebook');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(3, 'http://pinterest.com/%%u', 1, 'pinterest-p', 'pinterest', 'Pinterest');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(4, 'http://youtube.com/%%u', 1, 'youtube', 'youtube', 'Youtube');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(5, 'http://plus.google.com/+%%u', 1, 'google-plus', 'google-plus', 'Google+');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(6, 'http://linkedin.com/in/%%u', 1, 'linkedin', 'linkedin', 'LinkedIn');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(7, 'http://linkedin.com/company/%%u', 1, 'linkedin-square', 'linkedin-co', 'LinkedIn (Company)');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(8, 'http://github.com/%%u', 1, 'github', 'github', 'GitHub');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(9, 'http://instagram.com/%%u', 1, 'instagram', 'instagram', 'Instagram');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(10, 'http://vimeo.com/%%u', 1, 'vimeo', 'vimeo', 'Vimeo');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(11, 'http://flickr.com/photos/%%u', 1, 'flickr', 'flickr', 'Flickr');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(12, 'http://foursquare.com/%%u', 1, 'foursquare', 'foursquare', 'Foursquare');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(13, 'http://yelp.com/biz/%%u', 1, 'yelp', 'yelp', 'Yelp');";
            $_DATA[] = "INSERT INTO {$_TABLES['social_follow_services']} (`ssid`, `url`, `enabled`, `icon`, `service_name`, `display_name`) VALUES(14, 'http://dribbble.com/%%u', 1, 'dribbble', 'dribbble', 'Dribbble');";

            $_DATA[] = "REPLACE INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('headlines', 'HTML: embeds article headslines. usage: [headlines:<i>topic_name or all</i> display:## meta:0/1 titlelink:0/1 featured:0/1 frontpage:0/1 cols:# template:template_name]', 1, 1, '');";
            $_DATA[] = "REPLACE INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('mgslider', 'HTML: displays Media Gallery album. usage: [mgslider:<i>#album_id#</i> - Album ID for images <i>kenburns:0/1</i> - 1 = Enable Ken Burns effect <i>autoplay:0/1</i> 1 = Autoplay the slides <i>template:_name_</i> - Custom template name if wanted]', 1, 1, '');";
            $_DATA[] = "REPLACE INTO {$_TABLES['blocks']} (`bid`, `is_enabled`, `name`, `type`, `title`, `tid`, `blockorder`, `content`, `allow_autotags`, `rdfurl`, `rdfupdated`, `rdf_last_modified`, `rdf_etag`, `rdflimit`, `onleft`, `phpblockfn`, `help`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES(56, 1, 'followusblock', 'phpblock', 'Follow Us', 'all', 0, '', 0, '', '1000-01-01 00:00:00.000000', NULL, NULL, 0, 0, 'phpblock_social', '', 4, 4, 3, 2, 2, 2);";

            foreach ($_DATA as $sql) {
                DB_query($sql,1);
            }

            // add new social features
            $sis_admin_ft_id = 0;
            $sis_group_id    = 0;

            $tmp_admin_ft_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = 'social.admin'");
            if (empty ($tmp_admin_ft_id)) {
                DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr, ft_gl_core) VALUES ('social.admin','Ability to manage social features.',1)",1);
                $sis_admin_ft_id  = DB_insertId();
            }
            // now check for the group
            $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Social Admin'");
            if ( DB_numRows($result) == 0 ) {
                DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core, grp_default) VALUES ('Social Admin','Has full access to manage social integrations.',1,0)");
                $sis_group_id  = DB_insertId();
            }
            if ( $sis_admin_ft_id != 0 && $sis_group_id != 0 ) {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES (".$sis_admin_ft_id.",".$sis_group_id.")");
            }
            if ( $sis_group_id != 0 ) {
                DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id,ug_grp_id) VALUES (".$sis_group_id.",1)");
            }

            $current_fusion_version = '1.6.0';

        case '1.6.0' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->del('fs_mysql','Core');
            $c->del('allow_mysqldump','Core');
            $c->del('mysqldump_path','Core');
            $c->del('mysqldump_options','Core');

            $c->del('atom_max_stories','Core');
            $c->del('restrict_webservices','Core');
            $c->del('disable_webservices','Core');
            $c->del('fs_webservices','Core');

            $_SQL = array();

            $_SQL[] = "ALTER TABLE {$_TABLES['blocks']} CHANGE `title` `title` VARCHAR(255) NULL DEFAULT NULL;";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `attribution_url` VARCHAR(255) NOT NULL default '' AFTER `expire`;";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `attribution_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `attribution_url`;";
            $_SQL[] = "ALTER TABLE {$_TABLES['stories']} ADD `attribution_author` VARCHAR(255) NOT NULL DEFAULT '' AFTER `attribution_name`;";

            list($rc,$errors) = INST_updateDB($_SQL);

            $current_fusion_version = '1.6.1';

        case '1.6.1' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();

            $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Non-Logged-in Users'");
            if ( DB_numRows($result) == 0 ) {
                DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr, grp_gl_core, grp_default) VALUES ('Non-Logged-in Users','Non Logged-in Users (anonymous users)',1,0)",1);

                $result = DB_query("SELECT * FROM {$_TABLES['groups']} WHERE grp_name='Non-Logged-in Users'");
                if ( $result !== false ) {
                    $row = DB_fetchArray($result);
                    $nonloggedin_group_id = $row['grp_id'];
                    // assign all anonymous users to the group
                    DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (".$nonloggedin_group_id.",1,NULL) ",1);
                    // assign root group
                    DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (".$nonloggedin_group_id.",NULL,1) ",1);
                    $sql = "UPDATE {$_TABLES['menu']} SET group_id = " . $nonloggedin_group_id . " WHERE group_id = 998";
                    DB_query($sql);
                    $sql = "UPDATE {$_TABLES['menu_elements']} SET group_id = " . $nonloggedin_group_id . " WHERE group_id = 998";
                    DB_query($sql);
                }
            }
            $atSQL = "REPLACE INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('url', 'HTML: Create a link with description. usage: [url:<i>http://link.com/here</i> - Full URL <i>text</i> - text to be used for the URL link]', 1, 1, '');";
            DB_query($atSQL,1);

            $current_fusion_version = '1.6.2';

        case '1.6.2' :

            DB_query("ALTER TABLE {$_TABLES['subscriptions']} DROP INDEX `type`",1);
            DB_query("ALTER TABLE {$_TABLES['sessions']} CHANGE `md5_sess_id` `md5_sess_id` VARCHAR(128) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['stories']} ADD `subtitle` VARCHAR(128) DEFAULT NULL AFTER `title`;",1);
            DB_query("ALTER TABLE {$_TABLES['stories']} ADD `story_image` VARCHAR(128) DEFAULT NULL AFTER `alternate_tid`;",1);
            DB_query("ALTER TABLE {$_TABLES['autotags']} CHANGE `description` `description` VARCHAR(250) NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['rating']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['rating_votes']} CHANGE `item_id` `item_id` VARCHAR(128) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['subscriptions']} CHANGE `id` `id` VARCHAR(128) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['rating']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['rating_votes']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['subscriptions']} CHANGE `type` `type` varchar(30) NOT NULL DEFAULT '';",1);
            DB_query("ALTER TABLE {$_TABLES['logo']} CHANGE `config_name` `config_name` varchar(128) DEFAULT NULL;",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled='0' WHERE pi_name='ban'",1);
            DB_query("REPLACE INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid, ug_grp_id) VALUES (2,1,NULL)",1);

            $current_fusion_version = '1.6.3';

        case '1.6.3' :
        case '1.6.3.pl1' :
        case '1.6.3.pl2' :
            require_once $_CONF['path_system'].'classes/config.class.php';
            $c = config::get_instance();
            $c->load_baseconfig();
            $c->initConfig();
            $tmpConf = $c->get_config('Core');
            if ( stristr($tmpConf['htmlfilter_story'],'array') !== FALSE ) {
                $newfilter = serialize("div[class],h1,h2,h3,pre,br,p[style],b[style],s,strong[style],i[style],em[style],u[style],strike,a[style|href|title|target],ol[style|class],ul[style|class],li[style|class],hr[style],blockquote[style],img[style|alt|title|width|height|src|align],table[style|width|bgcolor|align|cellspacing|cellpadding|border],tr[style],td[style],th[style],tbody,thead,caption,col,colgroup,span[style|class],sup,sub");
                DB_query("UPDATE {$_TABLES['conf_values']} SET value='".$newfilter."' WHERE name='htmlfilter_story' AND group_name='Core'",1);
            }
            if ( stristr($tmpConf['htmlfilter_root'],'array') !== FALSE ) {
                $newfilter = serialize("div[style|class],span[style|class],table,tr,td,th,img[src|width|height|class|style]");
                DB_query("UPDATE {$_TABLES['conf_values']} SET value='".$newfilter."' WHERE name='htmlfilter_root' AND group_name='Core'",1);
            }
            $current_fusion_version = '1.6.4';

        case '1.6.4' :

            $current_fusion_version = '1.6.5';

        default:
            DB_query("INSERT INTO {$_TABLES['vars']} SET value='".$current_fusion_version."',name='glfusion'",1);
            DB_query("UPDATE {$_TABLES['vars']} SET value='".$current_fusion_version."' WHERE name='glfusion'",1);
            DB_query("DELETE FROM {$_TABLES['vars']} WHERE name='database_version'",1);
            break;
    }

    DB_query("ALTER TABLE {$_TABLES['userprefs']} ADD search_result_format VARCHAR( 48 ) NOT NULL DEFAULT 'google'",1);

    // delete the security check flag on every update to force the user
    // to run admin/sectest.php again
    DB_delete ($_TABLES['vars'], 'name', 'security_check');
    INST_resyncConfig();
    return array($rc,$errors);
}

function INST_doPrePluginUpgrade()
{
    global $_TABLES, $_CONF, $_SYSTEM, $_SP_CONF, $_DB, $_DB_dbms, $_DB_table_prefix,
           $LANG_AM, $dbconfig_path, $siteconfig_path, $html_path,$LANG_INSTALL, $_GLFUSION;

    $retval = '';

    require_once $_CONF['path_system'].'classes/config.class.php';
    $c = config::get_instance();

    switch ($_GLFUSION['original_version']) {
        case '1.0.0':
        case '1.0.1':
        case '1.0.2':
        case '1.1.0' :
        case '1.1.1' :
        case '1.1.2' :
        case '1.1.3' :
        case '1.1.4' :
        case '1.1.5' :
        case '1.1.6' :
        case '1.1.7' :
        case '1.1.8' :
        case '1.2.0' :
        case '1.2.1' :
        case '1.2.2' :
            require_once $_CONF['path_system'].'lib-install.php';

            // move sitetailor data over
            $complete = DB_getItem($_TABLES['vars'],'value','name="stcvt"');
            if ( $complete != 1 ) {
                $_TABLES['st_config']       = $_DB_table_prefix . 'st_config';
                $_TABLES['st_menus']        = $_DB_table_prefix . 'st_menus';
                $_TABLES['st_menus_config'] = $_DB_table_prefix . 'st_menus_config';
                $_TABLES['st_menu_elements']= $_DB_table_prefix . 'st_menu_elements';
                if ( DB_checkTableExists('st_config') ) {
                    DB_query("INSERT INTO {$_TABLES['logo']} SELECT * FROM {$_TABLES['st_config']}");
                    DB_query("INSERT INTO {$_TABLES['menu']} SELECT * FROM {$_TABLES['st_menus']}");
                    DB_query("INSERT INTO {$_TABLES['menu_elements']} SELECT * FROM {$_TABLES['st_menu_elements']}");
                    DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled=0 WHERE pi_name='sitetailor'",1);
                    DB_query("INSERT INTO {$_TABLES['vars']} (name,value) VALUES ('stcvt','1')",1);

                    $remvars = array (
                        /* give the name of the tables, without $_TABLES[] */
                        'tables' => array('st_config','st_menus','st_menu_config','st_menu_elements'),
                        /* give the full name of the group, as in the db */
                        'groups' => array('sitetailor Admin'),
                        /* give the full name of the feature, as in the db */
                        'features' => array('sitetailor.admin'),
                        /* give the full name of the block, including 'phpblock_', etc */
                        'php_blocks' => array(''),
                        /* give all vars with their name */
                        'vars'=> array()
                    );
                    // removing tables
                    for ($i=0; $i < count($remvars['tables']); $i++) {
                        DB_query ("DROP TABLE {$_TABLES[$remvars['tables'][$i]]}", 1    );
                    }
                    // removing variables
                    for ($i = 0; $i < count($remvars['vars']); $i++) {
                        DB_delete($_TABLES['vars'], 'name', $remvars['vars'][$i]);
                    }
                    // removing groups
                    for ($i = 0; $i < count($remvars['groups']); $i++) {
                        $grp_id = DB_getItem ($_TABLES['groups'], 'grp_id',
                                              "grp_name = '{$remvars['groups'][$i]}'");
                        if (!empty ($grp_id)) {
                            DB_delete($_TABLES['groups'], 'grp_id', $grp_id);
                            DB_delete($_TABLES['group_assignments'], 'ug_main_grp_id', $grp_id);
                        }
                    }
                    // removing features
                    for ($i = 0; $i < count($remvars['features']); $i++) {
                        $access_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = '{$remvars['features'][$i]}'");
                        if (!empty ($access_id)) {
                            DB_delete($_TABLES['access'], 'acc_ft_id', $access_id);
                            DB_delete($_TABLES['features'], 'ft_name', $remvars['features'][$i]);
                        }
                    }
                    if ($c->group_exists('sitetailor')) {
                        $c->delGroup('sitetailor');
                    }
                    DB_delete($_TABLES['plugins'], 'pi_name', 'sitetailor');
                }
            }

            $_TABLES['am_autotags'] = $_DB_table_prefix . 'am_autotags';
            $_TABLES['autotags'] = $_DB_table_prefix . 'autotags';

            if ( DB_checkTableExists('am_autotags') && isset($_TABLES['am_autotags']) ) {
                // we have an installed version of autotags plugin....
                DB_query("INSERT INTO {$_TABLES['autotags']} SELECT * FROM " . $_TABLES['am_autotags'],1);

                // delete the old autotag plugin
                $remvars = array (
                    /* give the name of the tables, without $_TABLES[] */
                    'tables' => array ( 'am_autotags' ),
                    /* give the full name of the group, as in the db */
                    'groups' => array('AutoTag Users'),
                    /* give the full name of the feature, as in the db */
                    'features' => array(),
                    /* give the full name of the block, including 'phpblock_', etc */
                    'php_blocks' => array(),
                    /* give all vars with their name */
                    'vars'=> array()
                );
                // removing tables
                for ($i=0; $i < count($remvars['tables']); $i++) {
                    DB_query ("DROP TABLE {$_TABLES[$remvars['tables'][$i]]}", 1    );
                }

                // removing variables
                for ($i = 0; $i < count($remvars['vars']); $i++) {
                    DB_delete($_TABLES['vars'], 'name', $remvars['vars'][$i]);
                }

                // removing groups
                for ($i = 0; $i < count($remvars['groups']); $i++) {
                    $grp_id = DB_getItem ($_TABLES['groups'], 'grp_id',
                                          "grp_name = '{$remvars['groups'][$i]}'");
                    if (!empty ($grp_id)) {
                        DB_delete($_TABLES['groups'], 'grp_id', $grp_id);
                        DB_delete($_TABLES['group_assignments'], 'ug_main_grp_id', $grp_id);
                    }
                }

                // removing features
                for ($i = 0; $i < count($remvars['features']); $i++) {
                    $access_id = DB_getItem ($_TABLES['features'], 'ft_id',"ft_name = '{$remvars['features'][$i]}'");
                    if (!empty ($access_id)) {
                        DB_delete($_TABLES['access'], 'acc_ft_id', $access_id);
                        DB_delete($_TABLES['features'], 'ft_name', $remvars['features'][$i]);
                    }
                }
                if ($c->group_exists('autotag')) {
                    $c->delGroup('autotag');
                }
                DB_delete($_TABLES['plugins'], 'pi_name', 'autotag');

            } else {
                $_DATA = array();
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('cipher', '{$LANG_AM['desc_cipher']}', 1, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('topic', '{$LANG_AM['desc_topic']}', 1, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('glfwiki', '{$LANG_AM['desc_glfwiki']}', 1, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('lang', '{$LANG_AM['desc_lang']}', 0, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('conf', '{$LANG_AM['desc_conf']}', 0, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('user', '{$LANG_AM['desc_user']}', 0, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('wikipedia', '{$LANG_AM['desc_wikipedia']}', 1, 1, NULL)";
                $_DATA[] = "INSERT INTO " . $_TABLES['autotags'] . " (tag, description, is_enabled, is_function, replacement) VALUES ('youtube', '{$LANG_AM['desc_youtube']}', 1, 0, '<object width=\"425\" height=\"350\"><param name=\"movie\" value=\"http://www.youtube.com/v/%1%\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"http://www.youtube.com/v/%1%\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"425\" height=\"350\"></embed></object>')";
                foreach ($_DATA as $sql) {
                    DB_query($sql,1);
                }
            }
            break;
        default :
            break;
    }
    return $retval;
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
    $retval = false;

    $result = DB_query("SHOW STORAGE ENGINES");
    $numEngines = DB_numRows($result);
    for ($i = 0; $i < $numEngines; $i++) {
        $A = DB_fetchArray($result);

        if (strcasecmp($A['Engine'], 'InnoDB') == 0) {
            if ((strcasecmp($A['Support'], 'yes') == 0) ||
                (strcasecmp($A['Support'], 'default') == 0)) {
                $retval = true;
            }
            break;
        }
    }

    return $retval;
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
    global $_DB_dbms;

    $ret = false;

    if ( !@file_exists($_CONF['path'] . '/system/lib-install.php') ) {
        return false;
    }
    require_once $_CONF['path'] . '/system/lib-install.php';

    if ( file_exists($_CONF['path'].'/plugins/'.$plugin.'/autoinstall.php') ) {

        require_once $_CONF['path'].'/plugins/'.$plugin.'/autoinstall.php';

        $ret = INSTALLER_install($INSTALL_plugin[$plugin]);
        if ( $ret == 0 ) {
            $ret = true;
        } else {
            $ret = false;
        }
    }
    return $ret;
}


function INST_isWritable($path) {
    if ($path{strlen($path)-1}=='/')
        return INST_isWritable($path.uniqid(mt_rand()).'.tmp');

    if (@file_exists($path)) {
        if (!($f = @fopen($path, 'r+')))
            return false;
        fclose($f);
        return true;
    }

    if (!($f = @fopen($path, 'w')))
        return false;
    @fclose($f);
    @unlink($path);
    return true;
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
        if ( $active && file_exists($_CONF['path'] . '/plugins/' . $plugin . '/upgrade.php') ) {
            require_once($_CONF['path'] . '/plugins/' . $plugin . '/upgrade.php');
            if ( function_exists( $plugin.'_upgrade' ) ) {
                $plgUpgradeFunction = $plugin.'_upgrade';
                $rc = $plgUpgradeFunction();
            }
        } else {
            if ( !$active && $forceInstall == 1 ) {
                // don't force install if already installed but marked inactive...
                $pcount = DB_count($_TABLES['plugins'],'pi_name',$plugin);
                if ( $pcount < 1 ) {
                    $rc = INST_pluginAutoInstall($plugin);
                } else {
                    $rc = true;
                }
            } else {
                $rc = true;
            }
        }
    } else {
        $rc = true; // not active, so just skip without error
    }
    return $rc;
}

function INST_clearCacheDirectories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry) || $entry == '.svn' || $entry == 'index.html') {
                continue;
            } elseif (is_dir($path . $entry)) {
                INST_clearCacheDirectories($path . $entry, $needle);
                @rmdir($path . $entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                @unlink($path . $entry);
            }
        }
        @closedir($dir);
    }
}


function INST_clearCache($plugin='')
{
    global $_CONF;

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }

    INST_clearCacheDirectories($_CONF['path'] . 'data/layout_cache/', $plugin);
}

/**
 * Get the current installed version of glFusion
 *
 * @return glFusion version in x.x.x format
 *
 */
function INST_identifyglFusionVersion ()
{
    global $_GLFUSION, $_TABLES, $_DB, $_DB_dbms, $_CONF, $dbconfig_path, $siteconfig_path;

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
    case 'mysqli':
        $test = array(
            '1.1.0'  => array("SELECT name FROM {$_TABLES['conf_values']} WHERE name='allow_embed_object'",'allow_embed_object'),
            '1.0.0'  => array("SELECT name FROM {$_TABLES['conf_values']} WHERE name='use_safe_html'",'use_safe_html'),
            );

        break;
    }

    $version = '';

    $result = DB_query("SELECT * FROM {$_TABLES['vars']} WHERE name='database_version'",1);
    if ( $result !== false ) {
        if ( DB_numRows($result) > 0 ) {
            $row = DB_fetchArray($result);
            if ( $row['value'] == '1.1.3' ) {
                $version = $row['value'];
                $_GLFUSION['original_version'] = $version;
                return $version;
            }
        }
    }

    $result = DB_query("SELECT * FROM {$_TABLES['vars']} WHERE name='glfusion'",1);
    if ( $result !== false ) {
        if ( DB_numRows($result) > 0 ) {
            $row = DB_fetchArray($result);
            $version = $row['value'];
            $_GLFUSION['original_version'] = $version;
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
    $_GLFUSION['original_version'] = $version;
    return $version;
}


function INST_checkCacheDir($path,$template,$classCounter)
{
    $permError = 0;

    // special test to see if existing cache files exist and are writable...
    if ( $dh = @opendir($path) ) {
        while (($file = readdir($dh)) !== false ) {
            if ( $file == '.' || $file == '..' || $file == '.svn') {
                continue;
            }
            if ( is_dir($path.$file) ) {
                $rc = INST_checkCacheDir($path.$file.'/',$template,$classCounter);
                if ( $rc > 0 ) {
                    $permError = 1;
                }
            } else {
                $ok = INST_isWritable($path.$file);
                if ( !$ok ) {
                    $template->set_var('location',$path.$file);
                    $template->set_var('status', $ok ? '<span class="yes">OK</span>' : '<span class="Unwriteable">NOT WRITABLE</span>');
                    $template->set_var('rowclass',($classCounter % 2)+1);
                    $classCounter++;
                    $template->parse('perm','perms',true);
                    if  ( !$ok ) {
                        $permError = 1;
                    }
                }
            }
        }
        closedir($dh);
    }
    return $permError;
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

function INST_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function INST_sanitizePath($path)
{
    $path = strip_tags($path);
    $path = str_replace(array('"', "'"), '', $path);
    $path = str_replace('..', '', $path);

    return $path;
}

function INST_resyncConfig() {
    global $_CONF, $_SYSTEM, $_TABLES;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $_CONF['path'].'sql/core_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='Core'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$coreConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='Core'");
        } else {
            $coreConfigData[$key]['indb'] = 1;
        }
    }
    foreach ($coreConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('Core');

    $site_url = $tcnf['site_url'];
    $cookiesecure = $tcnf['cookiesecure'];
    $def_photo = urldecode($_CONF['site_url']) . '/images/userphotos/default.jpg';

    foreach ( $coreConfigData AS $cfgItem ) {
        if ( $cfgItem['name'] == 'default_photo' )
            $cfgItem['default_value'] = $def_photo;

        $c->sync(
            $cfgItem['name'],
            $cfgItem['default_value'],
            $cfgItem['type'],
            $cfgItem['subgroup'],
            $cfgItem['fieldset'],
            $cfgItem['selection_array'],
            $cfgItem['sort'],
            $cfgItem['set'],
            $cfgItem['group']
        );
    }
}

function INST_doSiteConfigUpgrade() {
    global $_SYSTEM, $_CONF;

    _doSiteConfigUpgrade();
    return;
}

/**
* Deletes a directory (with recursive sub-directory support)
*
* @parm     string            Path of directory to remove
* @return   bool              True on success, false on fail
*
*/
function INST_deleteDir($path) {
    if (!is_string($path) || $path == "") return false;
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
        if (!$dh = @opendir($path)) return false;
        while (false !== ($f = readdir($dh))) {
            if ($f == '..' || $f == '.') continue;
            INST_deleteDir("$path/$f");
        }
        closedir($dh);
        return @rmdir($path);
    } else {
        return @unlink($path);
    }
    return false;
}

/**
* Deletes a directory if empty (with recursive sub-directory support)
*
* @parm     string            Path of directory to remove
* @return   bool              True on success, false on fail
*
*/
function INST_deleteDirIfEmpty($path) {
    $hasFiles = 0;
    $rc = true;

    if (!is_string($path) || $path == "") return false;
    if ( function_exists('set_time_limit') ) {
        @set_time_limit( 30 );
    }
    if (@is_dir($path)) {
        if (!$dh = @opendir($path)) return false;
        while (false !== ($f = readdir($dh))) {
            if ($f == '..' || $f == '.') continue;
            $rc = INST_deleteDirIfEmpty("$path/$f");
            if ( $rc === false ) $hasFiles++;
        }
        closedir($dh);
        if ( $hasFiles == 0 ) {
            return @rmdir($path);
        } else {
            return false;
        }
    } else {
        return false;       // found a file...
    }
    return true;
}

function _searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $array[$key];
       }
   }
   return null;
}

function _searchForIdKey($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $key;
       }
   }
   return null;
}

function _addConfigItem($data = array() )
{
    global $_TABLES;

    $Qargs = array(
                   $data['name'],
                   $data['set'] ? serialize($data['default_value']) : 'unset',
                   $data['type'],
                   $data['subgroup'],
                   $data['group'],
                   $data['fieldset'],
                   ($data['selection_array'] === null) ?
                    -1 : $data['selection_array'],
                   $data['sort'],
                   $data['set'],
                   serialize($data['default_value']));
    $Qargs = array_map('DB_escapeString', $Qargs);

    $sql = "INSERT INTO {$_TABLES['conf_values']} (name, value, type, " .
        "subgroup, group_name, selectionArray, sort_order,".
        " fieldset, default_value) VALUES ("
        ."'{$Qargs[0]}',"   // name
        ."'{$Qargs[1]}',"   // value
        ."'{$Qargs[2]}',"   // type
        ."{$Qargs[3]},"     // subgroup
        ."'{$Qargs[4]}',"   // groupname
        ."{$Qargs[6]},"     // selection array
        ."{$Qargs[7]},"     // sort order
        ."{$Qargs[5]},"     // fieldset
        ."'{$Qargs[9]}')";  // default value

    DB_query($sql);
}

?>
