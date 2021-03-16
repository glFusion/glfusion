<?php
/**
* glFusion CMS
*
* glFusion Configuration Editor
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

define('GVERSION','2.0.0');

if ( !file_exists('../../siteconfig.php')) die('Unable to locate siteconfig.php');

require_once '../../siteconfig.php';

$_SYSTEM['no_fail_sql'] = true;

if ( !file_exists($_CONF['path'].'db-config.php')) die('Unable to located db-config.php');

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;

require_once $_CONF['path'].'db-config.php';
$dbpass = $_DB_pass;

require_once $_CONF['path'] . 'classes/Autoload.php';
glFusion\Autoload::initialize();

$_DB_dbms = 'mysql';
if ( !isset($_CONF['db_charset'])) $_CONF['db_charset'] = '';

require_once $_CONF['path'].'system/db-init.php';

$self = basename(__FILE__);

$rescueFields = array('path_html','site_url','site_admin_url','rdf_file','cache_templates','path_log','path_language','backup_path','path_data','rdf_file','path_images','have_pear','path_pear','theme','path_themes','allow_user_themes','language','cookie_path','cookiedomain','cookiesecure','user_login_method','path_to_mogrify','path_to_netpbm','custom_registration','rootdebug','debug_oauth','debug_html_filter','maintenance_mode','bb2_enabled','cache_driver','enable_twofactor');

/* Constants for account stats */
define('USER_ACCOUNT_DISABLED', 0); // Account is banned/disabled
define('USER_ACCOUNT_AWAITING_ACTIVATION', 1); // Account awaiting user to login.
define('USER_ACCOUNT_AWAITING_APPROVAL', 2); // Account awaiting moderator approval
define('USER_ACCOUNT_ACTIVE', 3); // active account
define('USER_ACCOUNT_AWAITING_VERIFICATION', 4); // Account waiting for user to complete verification

/* Constants for account types */
define('LOCAL_USER',1);
define('REMOTE_USER',2);

function FR_stripslashes( $text ) {
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        if( get_magic_quotes_gpc() == 1 ) {
            return( stripslashes( $text ));
        }
    }
    return( $text );
}

function rescue_path_html_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_path_log_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_path_language_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_backup_path_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_path_data_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_path_images_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_path_pear_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_path_themes_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_site_url_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] == '/' ) {
        return (substr($value,0,strlen($value)-1));
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_site_admin_url_validate( $value ) {
    $value = trim($value);
    if ( $value[strlen($value)-1] == '/' ) {
        return (substr($value,0,strlen($value)-1));
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function rescue_rdf_file_validate( $value ) {
    $value = trim($value);
    return $value;
}

function validateInput( &$input_val ) {
    if (is_array($input_val)) {
        $r = array();
        foreach ($input_val as $key => $val) {
            if ($key !== 'placeholder') {
                $r[$key] = validateInput($val);
            }
        }
    } else {
        $r = FR_stripslashes($input_val);
        if ($r == 'b:0' OR $r == 'b:1') {
            $r = ($r == 'b:1');
        }
        if (is_numeric($r)) {
            $r = $r + 0;
        }
    }
    return $r;
}

function getConfigSetting($name)
{
    global $_CONF, $_TABLES, $_DB_name;

    $db = Database::getInstance();
    $setting = $db->getItem($_TABLES['conf_values'],'value',array('name' => $name,'group_name'=>'Core'));
    return @unserialize($setting);
}


function rescue_innodbStatus()
{
    global $_CONF, $_TABLES, $_DB_name;

    $retval = false;
    $numTables = 0;
    $i = 0;

    $numTables = count($_TABLES);

    $db = Database::getInstance();
    $engine = $db->getItem($_TABLES['vars'],'value',array('name'=>'database_engine'));

    if (!empty($engine) && ($engine == 'InnoDB')) {
        // need to look at all the tables
        $stmt = $db->conn->query("SHOW TABLES");
        $tableRecs = $stmt->fetchAll(Database::NUMERIC);
        foreach ($tableRecs AS $A) {
            $table = $A[0];
            if (in_array($table, $_TABLES)) {
                $i++;
                $engineData = $db->conn->fetchAssoc("SHOW TABLE STATUS FROM `{$_DB_name}` LIKE ?",array($table));
                if (strcasecmp($engineData['Engine'], 'InnoDB') != 0) {
                    break; // found a non-InnoDB table
                }
            }
        }
        if ($i == $numTables) {
            // okay, all the tables are InnoDB already
            $retval = true;
        }
    }

    return $retval;
}


/*
 * items to handle
 *
 * validate user - i.e.; login
 * set configuration variables
 * turn on / off plugins
 *
 **/

function showPage($page)
{
    $retval = '';

    switch ($page) {
        case 'passwordform' :
            $retval = page_passwordForm();
            break;
    }
    return $retval;
}

function page_passwordForm()
{
    $retval = '
        <div class="uk-text-center uk-margin-large-top uk-margin-bottom">
        Please enter the password for the database connected to this site
        </div>
        <div class="uk-vertical-align uk-text-center uk-height-1-1">
        <div class="uk-vertical-align-middle" style="width: 250px;">
        <form class="uk-panel uk-panel-box uk-form" method="post">
            <div class="uk-form-row">
                <input type="password" id="fusionpwd" name="fusionpwd" class="uk-width-1-1 uk-form-large" type="text" placeholder="Password">
            </div>
            <div class="uk-form-row">
                <button type="submit" class="uk-width-1-1 uk-button uk-button-primary uk-button-large">Login</button>
            </div>
        </form>
        </div>
        </div>
    ';

    return $retval;
}


function rescue_header( $authenticated ) {
    $retval = '<!DOCTYPE html>
        <html class="uk-height-1-1">
        	<head>
        		<meta charset="{charset}">
        		<meta name="viewport" content="width=device-width, initial-scale=1">
        		<link rel="apple-touch-icon-precomposed" href="../../layout/cms/images/apple-touch-icon.png">
        		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/css/uikit.almost-flat.min.css">
        		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        		<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/js/uikit.min.js"></script>
        		<meta name="robots" content="noindex,nofollow" />
        		<title>glFusion Rescue</title>
                <style>
                    html{background:#f9f9f9}.tm-dropdown,.tm-navbar{background:#325482!important}.uk-form label{font-weight:700}.tm-navbar{border-radius:0!important;padding:1px 0 6px!important;border:none!important}.tm-navbar-brand{color:#eee!important}.tm-navbar-brand a{text-shadow:none;color:#fff}.tm-navbar-brand a:hover{color:#ababab;text-decoration:none}.tm-navbar-brand-oc a{text-shadow:none;color:#fff}.tm-navbar-brand-oc a:hover{color:#ababab;text-decoration:none}.tm-navbar-nav>li>a{border:none;border-radius:3px!important;font-size:15px!important;height:40px!important;margin:0!important}.tm-dropdown{color:#000;border-radius:0!important;border:0!important}.tm-nav-navbar>li>a{color:#fff!important}.tm-navbar::after,.tm-navbar::before{margin-top:7px!important}.tm-navbar-nav>li.uk-open>a,.tm-navbar-nav>li:hover>a,.tm-navbar-nav>li>a:focus{color:#fff;outline:0;background:#35B3EE!important}.tm-navbar-nav>li>a{color:#fff!important;text-shadow:none!important}.tm-navbar-toggle{color:#ccc!important;text-shadow:none!important}.tm-navbar-toggle:focus,.tm-navbar-toggle:hover{color:#fff!important}.tm-footer{background:#252525;border-top:1px solid #232425;margin-bottom:0;bottom:0;padding:10px;color:#fff}.tm-wrapper{min-height:100%;margin:0 auto -51px!important}.tm-push,footer{height:51px}
                </style>
        	</head>
        	<body class="uk-height-1-1">
        	<div class="tm-wrapper">
                <nav class="uk-navbar uk-navbar-attached tm-navbar uk-margin-bottom">
                    <a href="#ocnav" class="tm-navbar-toggle uk-navbar-toggle uk-visible-small" data-uk-offcanvas></a>
        			<div class="uk-navbar-brand tm-navbar-brand">
        				glFusion Rescue Utility
        			</div>
        ';

    if ( $authenticated ) {
        $retval .= '
            <div id="ocnav" class="uk-offcanvas">
                <div class="uk-offcanvas-bar">
                    <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-width-medium-2-3 uk-nav-offcanvas" data-uk-nav>
                       <li class="uk-parent">
                             <a class="parent" href="#">Configuration</a>
                                <ul class="uk-nav-sub">
                                    <li><a href="fusionrescue.php?mode=submit&groupmode=Core">glFusion Core</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=calendar">Calendar Plugin</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=captcha">CAPTCHA</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=filemgmt">FileMgmt</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=forum">Forum</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=links">Links</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=polls">Polls</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=spamx">Spamx</a></li>
                                    <li><a href="fusionrescue.php?mode=submit&group=staticpages">StaticPages</a></li>
                                </ul>
                        </li>
                        <li><a href="fusionrescue.php?mode=plugins">Plugins</a></li>
                        <li><a href="fusionrescue.php?mode=repair">Repair Database</a></li>
                        <li><a href="fusionrescue.php?mode=resetpassword">Reset Password</a></li>
        ';
        if ( getConfigSetting('enable_twofactor') == true ) {
            $retval .= '                <li><a href="fusionrescue.php?mode=resetmfa">Disable 2FA</a></li>';
        }
        $retval .= '
                        <li><a  href="fusionrescue.php?mode=cancel">Logout</a></li>
                        </ul>
                    </ul>
                </div>
            </div>
            <ul class="uk-navbar-nav tm-navbar-nav uk-hidden-small">
            <li class="uk-parent" data-uk-dropdown="{remaintime:\'300\',delay:\'300\'}"">
            <a>Configuration <i class="uk-icon-caret-down"></i></a>
            <div class="uk-dropdown uk-dropdown-navbar uk-dropdown-bottom tm-dropdown">
                <ul class="uk-nav uk-nav-navbar tm-nav-navbar">
                    <li><a href="fusionrescue.php?mode=submit&groupmode=Core">glFusion Core</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=calendar">Calendar Plugin</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=captcha">CAPTCHA</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=filemgmt">FileMgmt</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=forum">Forum</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=links">Links</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=polls">Polls</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=spamx">Spamx</a></li>
                    <li><a href="fusionrescue.php?mode=submit&group=staticpages">StaticPages</a></li>
                </ul>
            </div>
            </li>
            <li><a href="fusionrescue.php?mode=plugins">Plugins</a></li>
        ';
//        if ( !rescue_innodbStatus() ) {
            $retval .= '
                <li><a href="fusionrescue.php?mode=repair">Repair Database</a></li>
            ';
//        }
        $retval .= '<li><a href="fusionrescue.php?mode=resetpassword">Reset Password</a></li>';

        if ( getConfigSetting('enable_twofactor') == true ) {
            $retval .= '<li><a href="fusionrescue.php?mode=resetmfa">Disable 2FA</a></li>';
        }

        $retval .= '
            </ul>
            <div class="uk-navbar-flip uk-hidden-small">
            <div class="uk-navbar-content">
            <a class="uk-button uk-button-danger" type="submit" name="mode" href="fusionrescue.php?mode=cancel">Logout</a>
            </div>
            </div>
        ';
    }

    $retval .= '
        </nav>
    	<div class="uk-container uk-container-center uk-margin-bottom">
    ';

    if ( $authenticated ) {
        $retval .= '
            <div class="uk-alert uk-alert-danger uk-text-large uk-text-center">
            Please delete the fusionrescue.php file and the install directory once you are done!
            </div>
        ';
    }

    return $retval;
}

function rescue_footer()
{
    $retval = '
        		</div> <!-- end of container -->
        	    <div class="tm-push"></div>
            </div>
        	<footer class="tm-footer uk-width-1-1 uk-text-center uk-margin-bottom-remove">
        		<div class="uk-hidden-small" style="padding-top:5px;"></div>
        	    <div>
        			<a href="https://www.glfusion.org" target="_blank">glFusion</a> is free software released under the <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>
        		</div>
        	</footer>
            </body>
        </html>
    ';

    return $retval;
}

function processPlugins() {
    global $rescueFields, $_DB_table_prefix;

    $plugins = array();
    $retval  = '';

    $db = Database::getInstance();

    // validate the glFusion database

    $sql = "SELECT * FROM `" . $_DB_table_prefix . "conf_values` WHERE name='allow_embed_object' OR name='use_safe_html'";
    $record = $db->conn->fetchAssoc($sql);
    if ($record === false || $record === NULL) {
        die('Invalid glFusion Database');
    }

    $stmt = $db->conn->executeQuery("SELECT * FROM `".$_DB_table_prefix."plugins` ORDER by pi_name");
    $plugins = $stmt->fetchAll(Database::ASSOCIATIVE);

    $retval .= '
        <ul class="uk-breadcrumb">
            <li>Plugin Administration</li>
        </ul>

        <form class="uk-form uk-form-horizontal" method="post" action="fusionrescue.php">
        <div class="uk-panel uk-panel-box uk-margin-bottom">
        <table class="uk-table uk-table-hover">
        <tr><th>Plugin</th><th class="uk-text-center">Enabled</th></tr>
    ';

    foreach ($plugins as $row) {
        if ( $row['pi_name'] != '' ) {
            $retval .= '
                <tr>
                <td>'.$row['pi_name'].'</td>
                <td class="uk-text-center"><input type="checkbox" name="enabled[' . $row['pi_name'] . ']" value="1" '. ($row['pi_enabled'] ? ' checked="checked"' : '') .'/></td>
                </tr>
            ';
        }
    }
    $retval .= '
        </table>
        </div>
        <div class="uk-text-center">
        <button class="uk-button uk-button-success" type="submit" name="mode" value="saveplugins">Save</button>
        </div>
        </form>
    ';
    return $retval;
}

function savePlugins(  ) {
    global $rescueFields, $_DB_table_prefix;

    $retval = '';

    $db = Database::getInstance();

    $table = $_DB_table_prefix . 'plugins';

    $sql = "UPDATE `{$table}` SET pi_enabled=0";

    $db->conn->query($sql);

    $enabled = array();
    $enabled = $_POST['enabled'];

    $changed = 0;
    foreach ($enabled as $plugin => $value) {
        $sql = "UPDATE `{$table}` SET pi_enabled=1 WHERE pi_name=?";
        $db->conn->executeQuery($sql,array($plugin));
    }
    $retval = 'Plugins have been updated';
    return $retval;
}

function repairDatabase() {
    global $rescueFields, $_TABLES, $_DB_table_prefix;

    $retval = array();

    $db = Database::getInstance();

    $maxtime = @ini_get('max_execution_time');
    if (empty($maxtime)) {
        // unlimited or not allowed to query - assume 30 second default
        $maxtime = 30;
    }
    $maxtime -= 5;

    $stmt = $db->conn->query("SHOW TABLES");
    $tableRecs = $stmt->fetchAll(Database::NUMERIC);

    foreach($tableRecs AS $A) {
        $table = $A[0];

        if (in_array($table, $_TABLES)) {
            if (! empty($startwith)) {
                if ($table == $startwith) {
                    $startwith = '';
                } else {
                    continue; // already handled - skip
                }
                if (!empty($lasttable) && ($lasttable == $table)) {
                    continue; // skip
                }
            }
            try {
                $stmt = $db->conn->executeQuery("REPAIR TABLE `{$table}`");
            } catch(Throwable | \Doctrine\DBAL\DBALException $e) {
                $retval[] = "Repair failed for " . $table;
            }
        }
    }
    return $retval;
}

function rescue_authenticated()
{
    if ( isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == 1 ) {
        return true;
    }
    return false;
}


function getNewPaths( $group = 'Core') {
    global $rescueFields, $_DB_table_prefix;

    $retval = '';

    $db = Database::getInstance();

    // validate the glFusion database

    $sql = "SELECT * FROM `" . $_DB_table_prefix . "conf_values` WHERE name='allow_embed_object' OR name='use_safe_html'";
    $record = $db->conn->fetchAssoc($sql);
    if ($record === false || $record === NULL) {
        $retval = rescue_header(0);
        $retval .= '<div class="uk-alert uk-alert-danger">
                    fusionrecue is unable to retrieve the glFusion configuration data from the database.<br>
                    Please validate that the glFusion database is not corrupted and that it contains an
                    active glFusion site\'s data.
                    </div>
                    ';
        $retval .= rescue_footer();
        echo $retval;
        exit;
    }

    $sql = "SELECT * FROM `" . $_DB_table_prefix . "conf_values` WHERE group_name=? AND ((type <> 'subgroup') AND (type <> 'fieldset')) ORDER BY subgroup,sort_order ASC";

    $stmt = $db->conn->executeQuery($sql,array($group));

    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ( $group != 'Core' || in_array($row['name'],$rescueFields)) {
            $config[$row['name']] = $row['value'];
            $configDetail[$row['name']]['type'] = $row['type'];
            if ( $row['name'] == 'site_url' || $row['name'] == 'site_admin_url' ) {
                $configDetail[$row['name']]['type'] = 'text';
            }
            $configDetail[$row['name']]['selectionArray'] = $row['selectionArray'];
        }
    }

    $retval .= '
        <ul class="uk-breadcrumb">
            <li>Configuration</li>
            <li class="uk-active"><span>'.$group.'</span></li>
        </ul>
        <form class="uk-form uk-form-horizontal" method="post" action="fusionrescue.php">
        <input type="hidden" name="group" value="'.$group.'">
        <div class="uk-form-row uk-alert">
        <label class="uk-form-label">Option</label>
        <div class="uk-form-controls uk-text-bold" style="margin-left:-5px;padding-top:5px;">
        Reset&nbsp;&nbsp;
        Setting
        </div>
        </div>
        <div class="uk-panel uk-panel-box uk-margin-bottom">
    ';

    foreach ($config as $option => $value) {
        if ( $option === 'cache_driver' ) {
            $retval .= '
                <div class="uk-form-row">
                <label class="uk-form-label">'.$option.'</label>
                <div class="uk-form-controls">
                &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                <select name="cfgvalue[' . $option . ']">
                <option ' . ( @unserialize($value) == 'Devnull' ? ' selected="selected"' : '') . ' value="Devnull">Disabled</option>
                <option ' . ( @unserialize($value) == 'files' ? ' selected="selected"' : '') . ' value=\'files\'>Files</option>
            ';
            if (extension_loaded('apcu')) {
                $retval .= '<option ' . ( @unserialize($value) == 'apcu' ? ' selected="selected"' : '') . ' value="apcu">APCu</option>';
            }
            if (extension_loaded('memcache')) {
                $retval .= '<option ' . ( @unserialize($value) == 'memcache' ? ' selected="selected"' : '') . ' value="memcache">Memcache</option>';
            }
            if (extension_loaded('memcached')) {
                $retval .= '<option ' . ( @unserialize($value) == 'memcached' ? ' selected="selected"' : '') . ' value="memcached">Memcached</option>';
            }
            if (extension_loaded('redis')) {
                $retval .= '<option ' . ( @unserialize($value) == 'redis' ? ' selected="selected"' : '') . ' value="redis">Redis</option>';
            }
            if (extension_loaded('wincache')) {
                $retval .= '<option ' . ( @unserialize($value) == 'wincache' ? ' selected="selected"' : '') . ' value="wincache">Wincache</option>';
            }
            $retval .= '
                </select>
                </div>
                </div>
            ';
        } elseif ( is_bool(@unserialize($value)) ) {
            $retval .= '
                <div class="uk-form-row">
                <label class="uk-form-label">'.$option.'</label>
                <div class="uk-form-controls">
                &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                <select name="cfgvalue[' . $option . ']">
                <option ' . ( @unserialize($value) == 0 ? ' selected="selected"' : '') . ' value="b:0">False</option>
                <option ' . ( @unserialize($value) == 1 ? ' selected="selected"' : '') . ' value="b:1">True</option>
                </select>
                </div>
                </div>
            ';
        } elseif ( $configDetail[$option]['type'] == 'select' && ($configDetail[$option]['selectionArray'] == 0 || $configDetail[$option]['selectionArray'] == 1)) {
            $retval .= '
                <div class="uk-form-row">
                <label class="uk-form-label">'.$option.'</label>
                <div class="uk-form-controls">
                &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                <select name="cfgvalue[' . $option . ']">
                <option ' . ( @unserialize($value) == 0 ? ' selected="selected"' : '') . ' value="b:0">False</option>
                <option ' . ( @unserialize($value) == 1 ? ' selected="selected"' : '') . ' value="b:1">True</option>
                </select>
                </div>
                </div>
            ';
        } elseif ($configDetail[$option]['type'] != '@text' &&  $configDetail[$option]['type'] != '%text' && $configDetail[$option]['type'] != '@select' && $configDetail[$option]['type'] != '*text' && $configDetail[$option]['type'] != '**placeholder')  {
            $retval .= '
                <div class="uk-form-row">
                <label class="uk-form-label">'.$option.'</label>
                <div class="uk-form-controls">
                &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                <input class="uk-form-width-large" type="text" name="cfgvalue[' . $option . ']" value="' . @unserialize($value) . '" />
                </div>
                </div>
            ';
        } elseif ( $configDetail[$option]['type'] == '@select' ) {
            if ($option !== 'user_login_method') {
                $retval .= '
                    <div class="uk-form-row">
                    <label class="uk-form-label">'.$option.'</label>
                    <div class="uk-form-controls">
                    &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                    <input class="uk-form-width-large" type="hidden" name="cfgvalue[' . $option . ']" value="' . @unserialize($value) . '" />
                ';
                $retval .= '
                    </select>
                    </div>
                    </div>
                ';
            } else {
                $ua_array = unserialize($value);

                foreach($ua_array AS $op => $val) {
                    $retval .= '
                        <div class="uk-form-row">
                        <label class="uk-form-label">'.$option.'['.$op.']</label>
                        <div class="uk-form-controls">
                        &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                        <select name="cfgvalue[' . $option . ']['.$op.']">
                        <option ' . ( $val == 0 ? ' selected="selected"' : '') . ' value="0">False</option>
                        <option ' . ( $val == 1 ? ' selected="selected"' : '') . ' value="1">True</option>
                        </select>
                        </div>
                        </div>
                    ';
                }
            }

        }  else {
            $item = @unserialize($value);

            $retval .= '
                <div class="uk-form-row">
                <label class="uk-form-label">'.$option.'</label>
                <div class="uk-form-controls">
                &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
            ';
            if (!is_array($item)) {
                $retval .= '
                    <input class="uk-form-width-large" type="text" name="cfgvalue[' . $option . ']" value="' . @unserialize($value) . '" />
                ';
            }
            $retval .= '
                </div>
                </div>
            ';
        }
    }

    $retval .= '
        </div>
        <div class="uk-text-center">
        <button class="uk-button uk-button-success" type="submit" name="mode" value="save" />Save</button>
        <button class="uk-button uk-button-danger" type="submit" name="mode" value="cancel" />Logout</button>
        </div>
        </form>
    ';

    if ( !isset($_SESSION['warning']) || $_SESSION['warning'] != 1 ) {
        $retval .= '
            <script>
                jQuery(document).ready(function($) {
                var welcome = "glFusion Rescue is a utility that allows you to directly modify glFusion configuration settings. Please consult the <a href=\"https://www.glfusion.org/wiki/glfusion:fusionrescue\" target=\"#_blank\">glFusion Rescue Documentation</a> for details on how to use this utility.";
                    $.UIkit.modal.alert(welcome).show();
                });
            </script>
        ';
        $_SESSION['warning'] = 1;
    }

    return $retval;
}

function saveNewPaths( $group='Core' ) {
    global $rescueFields, $_DB_table_prefix;

    $retval = array();

    $db = Database::getInstance();

    $sql = "SELECT * FROM `".$_DB_table_prefix."conf_values` WHERE group_name=? AND ((type <> 'subgroup') AND (type <> 'fieldset'))";
    $stmt = $db->conn->executeQuery($sql,array($group));

    while ($row = $stmt->fetch(Database::ASSOCIATIVE) ) {
        if ( $group != 'Core' || in_array($row['name'],$rescueFields)) {
            $config[$row['name']] = @unserialize($row['value']);
            $default[$row['name']] = $row['default_value'];
        }
    }

    $cfgvalues = array();
    $reset     = array();
    $cfgvalues = $_POST['cfgvalue'];
    $reset     = (isset($_POST['default']) ? $_POST['default'] : array());
    $changed = 0;
    foreach ($cfgvalues as $option => $value) {
        if ( isset($reset[$option]) && $reset[$option] == 1 ) {
            $sql = "UPDATE " . $_DB_table_prefix . "conf_values SET value='" . $default[$option] . "' WHERE name='" . $option . "' AND group_name='".$group."'";
            $sql = "UPDATE `" . $_DB_table_prefix . "conf_values`
                    SET value=? WHERE name=? AND group_name=?";
            try {
                $stmt = $db->conn->executeUpdate($sql,array($default[$option],$option,$group));
            } catch(Throwable | \Doctrine\DBAL\DBALException $e) {
                $retval[] = 'Error Resetting ' . $option;
                $stmt = false;
            }
            if ($stmt !== false) {
                $retval[] = 'Resetting ' . $option;
                $changed++;
            }
        } else {
            $sVal = validateInput($value);
            if ( $config[$option] != $sVal && $option != 'user_login_method' && $option != 'event_types' && $option != 'default_permissions' && $option != 'grouptags') {
                $fn = 'rescue_' . $option . '_validate';
                if (function_exists($fn)) {
                    $sVal = $fn($sVal);
                }
                $sql = "UPDATE `" . $_DB_table_prefix . "conf_values`
                        SET value=? WHERE name=? AND group_name=?";
                try {
                    $stmt = $db->conn->executeUpdate(
                                $sql,
                                array(
                                    serialize($sVal),
                                    $option,
                                    $group
                                )
                    );
                } catch(Throwable | \Doctrine\DBAL\DBALException $e) {
                    $retval[] = 'Error saving ' . $option;
                    $stmt = false;
                }
                if ($stmt !== false) {
                    $retval[] = 'Saving ' . $option;
                    $changed++;
                }
            } else if ($option == 'user_login_method') {
                $methods = array('standard', '3rdparty', 'oauth');
                $methods_disabled = 0;
                foreach ($methods as $m) {
                    if (isset($value[$m]) && $value[$m] == 0) {
                        $methods_disabled++;
                        $value[$m] = (int) 0;
                    } else {
                        $value[$m] = (int) 1;
                    }
                }
                if ($methods_disabled == count($methods)) {
                    // just to make sure people don't lock themselves out of their site
                    $value['standard'] = true;
                }
                $sql = "UPDATE `" . $_DB_table_prefix . "conf_values`
                        SET value=? WHERE name=? AND group_name=?";
                try {
                    $stmt = $db->conn->executeUpdate(
                                $sql,
                                array(
                                    serialize($value),
                                    $option,
                                    $group
                                )
                    );
                } catch(Throwable | \Doctrine\DBAL\DBALException $e) {
                    $retval[] = 'Error saving ' . $option;
                    $stmt = false;
                }
                if ($stmt !== false) {
                    $retval[] = 'Saving ' . $option;
                    $changed++;
                }
            }
        }
    }
    if ( $changed == 0 ) {
        $retval[] = 'No changes detected';
    } else {
        @unlink($cfgvalue['path_data'] .'$$$config$$$.cache');
        @unlink($config['path_data'] .'$$$config$$$.cache');
        @unlink($cfgvalue['path_data'] .'cache/$$$config$$$.cache');
        @unlink($config['path_data'] .'cache/$$$config$$$.cache');
        $c = Cache::getInstance();
        $c->clear();
    }

    return $retval;
}

// returns a form to request a new password
function requestNewPassword($errorMsg = '')
{
    global $rescueFields, $_DB_table_prefix;

    $form = '
        <ul class="uk-breadcrumb">
            <li>Reset Password</li>
        </ul>';

    if ( $errorMsg != '' ) {
        $form .= '
            <div class="uk-alert uk-alert-danger">
            <p>' . $errorMsg . '</p></div>';
    }
    $form .= '
        <form class="uk-form uk-form-horizontal" method="post" action="fusionrescue.php">
          <div class="uk-panel uk-panel-box uk-margin-bottom">
            <div class="uk-form-row">
                <label class="uk-form-label">Username</label>
                <div class="uk-form-controls">
                    <input type="text" id="username" name="username">
                </div>
            </div>
            <div class="uk-form-row">
                <label class="uk-form-label">Email</label>
                <div class="uk-form-controls">
                    <input type="text" id="email" name="email">
                </div>
            </div>
            <div class="uk-form-row">
                <label class="uk-form-label">Password</label>
                <div class="uk-form-controls">
                    <input type="password" id="passwd" name="passwd">
                </div>
            </div>
            <div class="uk-form-row">
                <label class="uk-form-label">Confirm Password</label>
                <div class="uk-form-controls">
                    <input type="password" id="passwd2" name="passwd2">
                </div>
            </div>
          </div>
          <div class="uk-text-center">
            <button class="uk-button uk-button-success" type="submit" name="mode" value="reset" />Reset Password</button>
            <button class="uk-button uk-button-danger" type="submit" name="mode" value="cancel" />Logout</button>
          </div>
        </form>
    ';

    return $form;
}

function resetPassword()
{
    global $rescueFields, $_DB_table_prefix;

    $db = Database::getInstance();

    // check if passwd and passwd2 match

    $passwd = '';
    $passwd2 = '';
    $username = '';

    if ( isset($_POST['passwd']) ) {
        $passwd = $_POST['passwd'];
    }
    if ( isset($_POST['passwd2']) ) {
        $passwd2 = $_POST['passwd2'];
    }
    if ( empty($passwd) || empty($passwd2) || $passwd != $passwd2 ) {
        return requestNewPassword('Password blank or does not match confirmation');
    }

    if ( !isset($_POST['username']) || !isset($_POST['email'])) {
        return requestNewPassword('Please provide both username and email address.');
    }

    $username = $_POST['username'];
    $email    = $_POST['email'];

    $stmt = $db->conn->executeQuery(
            "SELECT uid,email,passwd,status FROM `".$_DB_table_prefix."users`
                WHERE username = ?
                AND email = ? AND (account_type & ?)",
            array(
                $username,
                $email,
                LOCAL_USER
            ),
            array(
                Database::STRING,
                Database::STRING,
                Database::INTEGER
            )
    );
    $userRecs = $stmt->fetchAll(Database::ASSOCIATIVE);

    if (count($userRecs) === 1) {
        $U = $userRecs[0];

        $encrypted_password = SEC_hash($passwd);
        $stmt = $db->conn->executeUpdate(
            "UPDATE `".$_DB_table_prefix."users` SET passwd = ? WHERE uid=?",
            array(
                $encrypted_password,
                $U['uid']
            ),
            array(
                Database::STRING,
                Database::INTEGER
            )
        );
    } else {
        return requestNewPassword('Username or email incorrect - requires both');
    }

    $retval = '<div class="uk-alert uk-alert-success" data-uk-alert>';
    $retval .= '<a href="" class="uk-alert-close uk-close"></a>';
    $retval .= 'Password has been reset.';
    $retval .= '</div>';
    $retval .= requestNewPassword();

    return $retval;
}


// returns a form to request a new password
function requestDisableMFA($errorMsg = '')
{
    global $rescueFields, $_DB_table_prefix;

    $form = '
        <ul class="uk-breadcrumb">
            <li>Disable Two Factor Authentication</li>
        </ul>';

    if ( $errorMsg != '' ) {
        $form .= '
            <div class="uk-alert uk-alert-danger">
            <p>' . $errorMsg . '</p></div>';
    }
    $form .= '
        <form class="uk-form uk-form-horizontal" method="post" action="fusionrescue.php">
          <div class="uk-panel uk-panel-box uk-margin-bottom">
            <div class="uk-form-row">
                <label class="uk-form-label">Username</label>
                <div class="uk-form-controls">
                    <input type="text" id="username" name="username">
                </div>
            </div>
          </div>
          <div class="uk-text-center">
            <button class="uk-button uk-button-success" type="submit" name="mode" value="disablemfa" />Disable 2FA</button>
            <button class="uk-button uk-button-danger" type="submit" name="mode" value="cancel" />Logout</button>
          </div>
        </form>
    ';

    return $form;
}

function resetMFA()
{
    global $rescueFields, $_DB_table_prefix;

    $db = Database::getInstance();

    $username = '';

    if ( !isset($_POST['username']) ) {
        return requestDisableMFA('Please provide a username to disable two-factor authentication.');
    }

    $username = $_POST['username'];

    $userRec = $db->conn->fetchAssoc(
            "SELECT uid,email,passwd,status FROM `".$_DB_table_prefix."users`
                WHERE username = ?",
            array($username)
    );

    if ($userRec !== false && $userRec !== NULL) {
        $db->conn->executeUpdate(
            "UPDATE `".$_DB_table_prefix."users` SET tfa_enabled=0 WHERE uid=?",
            array($userRec['uid']),
            array(Database::INTEGER)
        );
    } else {
        return requestDisableMFA('Username was not found');
    }

    $retval = '<div class="uk-alert uk-alert-success" data-uk-alert>';
    $retval .= '<a href="" class="uk-alert-close uk-close"></a>';
    $retval .= 'Two Factor Authentication has been disabled for the user.';
    $retval .= '</div>';
    $retval .= requestDisableMFA();

    return $retval;
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
    static $dss_seeded = false;
    global $_SYSTEM;

    $rand_seed = COM_makesid();

    $val = $rand_seed . microtime();
    $val = md5($val);
    $rand_seed = md5($rand_seed . $val . $extra);

    return substr($val, 4, 16);
}

function COM_makesid()
{
    $sid = date( 'YmdHis' );
    $sid .= mt_rand( 0, 999 );

    return $sid;
}

// main processing

$display = '';
$page    = '';
$authenticated = 0;

session_start();

if ( rescue_authenticated() ) {
    $authenticated = 1;
} else{
    $authenticated = 0;
}

$group = 'Core';

if ( $authenticated == 0 && isset($_POST['fusionpwd']) ) {
    $pwd = $_POST['fusionpwd'];
    if ( $dbpass == $pwd ) {
        $_SESSION['authenticated'] = 1;
        $_SESSION['warning'] = 0;
        $authenticated = 1;
        $page = getNewPaths($group);
    } else {
        unset($_SESSION["authenticated"]);
        unset($_SESSION["warning"]);
        $authenticated = 0;
        $page = showPage('passwordform');
    }
} elseif ( $authenticated != 1 ) {
    $page = showPage('passwordform');
} else {
    $mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
    switch ( $mode ) {
        case 'submit' :
            $group = isset($_GET['group']) ? $_GET['group'] : (isset($_POST['group']) ? $_POST['group'] : 'Core');
            $page = getNewPaths($group);
            break;
        case 'save' :
            $group = isset($_GET['group']) ? $_GET['group'] : (isset($_POST['group']) ? $_POST['group'] : 'Core');
            $results = saveNewPaths($group);
            if ( is_array($results)) {
                $page .= '<div class="uk-alert uk-alert-success" data-uk-alert>';
                $page .= '<a href="" class="uk-alert-close uk-close"></a>';
                $page .= '<ul>';
                foreach ( $results AS $msg ) {
                    $page .= '<li>'.$msg.'</li>';
                }
                $page .= '</ul>';
                $page .= '</div>';
            }
            $page .= getNewPaths($group);
            break;
        case 'plugins' :
            $page .= processPlugins();
            break;
        case 'repair' :
            $results = repairDatabase();
            if ( is_array($results)) {
                $page .= '<div class="uk-alert uk-alert-success" data-uk-alert>';
                $page .= '<a href="" class="uk-alert-close uk-close"></a>';
                $page .= '<ul>';
                if ( count($results) > 0 ) {
                    foreach ( $results AS $msg ) {
                        $page .= '<li>'.$msg.'</li>';
                    }
                } else {
                    $page .= '<li>Database has been repaired</li>';
                }
                $page .= '</ul>';
                $page .= '</div>';
            }
            $page .=  getNewPaths($group);
            break;
        case 'saveplugins' :
            $results = savePlugins();
            $page .= '<div class="uk-alert uk-alert-success" data-uk-alert>';
            $page .= '<a href="" class="uk-alert-close uk-close"></a>';
            $page .= $results;
            $page .= '</div>';
            $page .= processPlugins();
            break;
        case 'resetpassword' :
            $page = requestNewPassword();
            break;
        case 'reset' :
            $page = resetPassword();
            break;

        case 'resetmfa' :
            $page = requestDisableMFA();
            break;
        case 'disablemfa' :
            $page = resetMFA();
            break;

        case 'cancel' :
            session_start();
            session_unset();
            session_destroy();
            header("location:fusionrescue.php");
            exit();
            break;
        default :
            $page = getNewPaths($group);
            break;
    }
}

$display = rescue_header($authenticated);
$display .= $page;
$display .= rescue_footer();

echo $display;
?>