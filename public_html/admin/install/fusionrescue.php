<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | fusionrescue.php                                                         |
// |                                                                          |
// | Safely edit glFusion configuration                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
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
//

error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

define('GVERSION','1.6.0');

require_once '../../siteconfig.php';
$_SYSTEM['no_fail_sql'] = true;
require_once $_CONF['path'].'db-config.php';
$dbpass = $_DB_pass;
require_once $_CONF['path_system'].'lib-database.php';

$self = basename(__FILE__);

$rescueFields = array('path_html','site_url','site_admin_url','rdf_file','cache_templates','path_log','path_language','backup_path','path_data','rdf_file','path_images','have_pear','path_pear','theme','path_themes','allow_user_themes','language','cookie_path','cookiedomain','cookiesecure','user_login_method','path_to_mogrify','path_to_netpbm','custom_registration');

function FR_stripslashes( $text ) {
    if( get_magic_quotes_gpc() == 1 ) {
        return( stripslashes( $text ));
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

function rescue_mysqldump_path_validate( $value ) {
    $value = trim($value);

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
        		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.3/js/uikit.min.js"></script>
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
                        <li><a  href="fusionrescue.php?mode=cancel">Logout</a></li>
                        </ul>
                    </ul>
                </div>
            </div>
            <ul class="uk-navbar-nav tm-navbar-nav uk-hidden-small">
            <li class="uk-parent" data-uk-dropdown="{remaintime:\'300\',delay:\'300\'}"">
            <a href="">Configuration <i class="uk-icon-caret-down"></i></a>
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
            <li><a href="fusionrescue.php?mode=repair">Repair Database</a></li>
            </ul>
            <div class="uk-navbar-flip uk-hidden-small">
            <div class="uk-navbar-content">
            <a class="uk-button uk-button-danger" type="cancel" name="mode" href="fusionrescue.php?mode=cancel">Logout</a>
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

    $sql = "SELECT * FROM " . $_DB_table_prefix . "conf_values WHERE name='allow_embed_object' OR name='use_safe_html'";
    $result = DB_query($sql);
    if ( DB_numRows($result) < 1 ) die('Invalid glFusion Database');

    $sql = "SELECT * FROM " . $_DB_table_prefix . "plugins";
    $result = DB_query($sql);
    while ($plugins[] = DB_fetchArray($result) ) { }

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

    $sql = "UPDATE " . $_DB_table_prefix . "plugins SET pi_enabled=0";

    $result = DB_query($sql);

    $enabled = array();
    $enabled = $_POST['enabled'];

    $changed = 0;
    foreach ($enabled as $plugin => $value) {
        $sql = "UPDATE " . $_DB_table_prefix . "plugins SET pi_enabled=1 WHERE pi_name='".DB_escapeString($plugin)."'";
        DB_query($sql);
    }
    $retval = 'Plugins have been updated';
    return $retval;
}

function repairDatabase() {
    global $rescueFields, $_TABLES, $_DB_table_prefix;

    $retval = array();

    $maxtime = @ini_get('max_execution_time');
    if (empty($maxtime)) {
        // unlimited or not allowed to query - assume 30 second default
        $maxtime = 30;
    }
    $maxtime -= 5;

    DB_displayError(true);

    $result = DB_query("SHOW TABLES");
    $numTables = DB_numRows($result);
    for ($i = 0; $i < $numTables; $i++) {
        $A = DB_fetchArray($result, true);
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
/*
            if (time() > $start + $maxtime) {
                $startwith = $table;
                $url = "fusionrescue.php?mode=repair";
                header("Location: $url&startwith=$startwith&failures=$failures");
                exit;
            }
*/
            $repair = DB_query("REPAIR TABLE " . $table, 1);
            if ($repair === false) {
                $retval[] = 'Repair failed for ' . $able;
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

    if ( $group == 'Core' ) {
        $where = "group_name='".$group."' AND ";
    } else {
        $where = '';
    }

    $group = DB_escapeString($group);

    $sql = "SELECT * FROM " . $_DB_table_prefix . "conf_values WHERE name='allow_embed_object' OR name='use_safe_html'";
    $result = DB_query($sql,1) or die('Cannot execute query');

    if ( DB_numRows($result) < 1 ) die('Invalid glFusion Database');
    $sql = "SELECT * FROM " . $_DB_table_prefix . "conf_values WHERE group_name='".$group."' AND ((type <> 'subgroup') AND (type <> 'fieldset')) ORDER BY subgroup,sort_order ASC";
    $result = DB_query($sql,1) or die('Cannot execute query');
    while ($row = DB_fetchArray($result) ) {
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
        if ( is_bool(@unserialize($value)) ) {
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
                </select>
                </div>
                </div>
            ';
        }  else {
            $retval .= '
                <div class="uk-form-row">
                <label class="uk-form-label">'.$option.'</label>
                <div class="uk-form-controls">
                &nbsp;&nbsp;<input type="checkbox" name="default[' . $option . ']" value="1" />&nbsp;&nbsp;
                <input class="uk-form-width-large" type="text" name="cfgvalue[' . $option . ']" value="' . @unserialize($value) . '" />
                </select>
                </div>
                </div>
            ';
        }
    }

    $retval .= '
        </div>
        <div class="uk-text-center">
        <button class="uk-button uk-button-success" type="submit" name="mode" value="save" />Save</button>
        <button class="uk-button uk-button-danger" type="cancel" name="mode" value="cancel" />Logout</button>
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

    $sql = "SELECT * FROM " . $_DB_table_prefix . "conf_values WHERE group_name='".DB_escapeString($group)."' AND ((type <> 'subgroup') AND (type <> 'fieldset'))";

    $result = DB_query($sql);

    while ($row = DB_fetchArray($result) ) {
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
            DB_query($sql);
            $retval[] = 'Resetting ' . $option;
            $changed++;
        } else {
            $sVal = validateInput($value);
            if ( $config[$option] != $sVal && $option != 'user_login_method' && $option != 'event_types' && $option != 'default_permissions' && $option != 'grouptags') {
                $fn = 'rescue_' . $option . '_validate';
                if (function_exists($fn)) {
                    $sVal = $fn($sVal);
                }
                $sql = "UPDATE " . $_DB_table_prefix . "conf_values SET value='" . serialize($sVal) . "' WHERE name='" . $option . "' AND group_name='".$group."'";
                DB_query($sql);
                $retval[] = 'Saving ' . $option;
                $changed++;
            }
        }
    }
    if ( $changed == 0 ) {
        $retval[] = 'No changes detected';
    } else {
        @unlink($cfgvalue['path_data'] .'$$$config$$$.cache');
        @unlink($config['path_data'] .'$$$config$$$.cache');
        @unlink($cfgvalue['path_data'] .'layout_cache/$$$config$$$.cache');
        @unlink($config['path_data'] .'layout_cache/$$$config$$$.cache');
    }

    return $retval;
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