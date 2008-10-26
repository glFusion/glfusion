<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | fusionrescue.php                                                         |
// |                                                                          |
// | Safely edit glFusion configuration                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

$rescueFields = array('path_html','site_url','site_admin_url','path_log','path_language','backup_path','path_data','path_images','have_pear','path_pear','theme','path_themes','allow_user_themes','language','cookie_path','cookiedomain','cookiesecure');

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

function getDBLogin( ) {
    echo '
    <form method="post" action="fusionrescue.php">
    <table style="width:100%;border:none;padding:5px;" cellspacing="5" cellpadding="5">
    <tr>
      <td style="text-align:right;">Database Server</td><td><input type="text" name="dbserver" value="" /></td>
    </tr>
    <tr>
      <td style="text-align:right;">Database User</td><td><input type="text" name="dbuser" value="" /></td>
    </tr>
    <tr>
      <td style="text-align:right;">Database Password</td><td><input type="password" name="dbpass" value="" /></td>
    </tr>
    <tr>
      <td style="text-align:right;">Database Name</td><td><input type="text" name="dbname" value="" /></td>
    </tr>
    <tr>
      <td style="text-align:right;">Database Prefix</td><td><input type="text" name="dbprefix" value="" /></td>
    </tr>
    </table>
    <br />
    <center><input type="submit" value="submit" name="mode" /></center><br />
    </form>
    ';
}

function getNewPaths( $dbserver, $dbuser, $dbpass, $dbname, $dbprefix ) {
    global $rescueFields;

    $db = @mysql_connect($dbserver,$dbuser,$dbpass) or die('Cannot connect to DB server');
    @mysql_select_db($dbname) or die('error selecting database');
    $sql = "SELECT * FROM " . $dbprefix . "conf_values WHERE name='allow_embed_object'";
    $result = @mysql_query($sql,$db) or die('Cannot execute query');
    if ( @mysql_num_rows($result) < 1 ) die('Invalid glFusion Database');
    $sql = "SELECT * FROM " . $dbprefix . "conf_values WHERE group_name='Core' AND ((type <> 'subgroup') AND (type <> 'fieldset')) ORDER BY subgroup,sort_order ASC";
    $result = @mysql_query($sql,$db) or die('Cannot execute query');
    while ($row = mysql_fetch_array($result,MYSQL_ASSOC) ) {
        if ( in_array($row['name'],$rescueFields)) {
            $config[$row['name']] = $row['value'];
            $configDetail[$row['name']]['type'] = $row['type'];
            if ( $row['name'] == 'site_url' || $row['name'] == 'site_admin_url' ) {
                $configDetail[$row['name']]['type'] = 'text';
            }
            $configDetail[$row['name']]['selectionArray'] = $row['selectionArray'];
        }
    }

    echo '
<form method="post" action="fusionrescue.php">
<input type="hidden" name="dbserver" value="' . $dbserver .'" />
<input type="hidden" name="dbuser" value="' . $dbuser . '" />
<input type="hidden" name="dbpass" value="' . $dbpass . '" />
<input type="hidden" name="dbname" value="' . $dbname . '" />
<input type="hidden" name="dbprefix" value="' . $dbprefix . '" />
<table style="width:100%;border:none;padding:5px;" cellspacing="5" cellpadding="5">
<tr><th style="text-align:right;width:20%;">Option</th><th style="text-align:middle; width:60%;">Value</th><th style="text-align:middle;width:20%;">Reset Default</th></tr>
';
foreach ($config as $option => $value) {
    if ( is_bool(@unserialize($value)) ) {
        echo '<tr onmouseover="this.className=\'hover\';" onmouseout="this.className=\'\';"><td style="text-align:right;width:20%;">' . $option . '</td><td>';
        echo '<select name="cfgvalue[' . $option . ']">';
        echo '<option ' .( @unserialize($value) == 0 ? ' selected="selected"' : '') . ' value="b:0">False</option>';
        echo '<option ' . ( @unserialize($value) == 1 ? ' selected="selected"' : '') . ' value="b:1">True</option>';
        echo '</select>';
        echo '</td><td style="text-align:center;width:20%;"><input type="checkbox" name="default[' . $option . ']" value="1" /></td></tr>';
    } elseif ( $configDetail[$option]['type'] == 'select' && ($configDetail[$option]['selectionArray'] == 0 || $configDetail[$option]['selectionArray'] == 1)) {
        echo '<tr onmouseover="this.className=\'hover\';" onmouseout="this.className=\'\';"><td style="text-align:right;width:20%;">' . $option . '</td><td>';
        echo '<select name="cfgvalue[' . $option . ']">';
        echo '<option ' .( @unserialize($value) == 0 ? ' selected="selected"' : '') . ' value="b:0">False</option>';
        echo '<option ' . ( @unserialize($value) == 1 ? ' selected="selected"' : '') . ' value="b:1">True</option>';
        echo '</select>';
        echo '</td><td style="text-align:center;width:20%;"><input type="checkbox" name="default[' . $option . ']" value="1" /></td></tr>';
    } elseif ($configDetail[$option]['type'] != '@text' &&  $configDetail[$option]['type'] != '%text' && $configDetail[$option]['type'] != '@select' && $configDetail[$option]['type'] != '*text' && $configDetail[$option]['type'] != '**placeholder')  {
        echo '<tr onmouseover="this.className=\'hover\';" onmouseout="this.className=\'\';"><td style="text-align:right;width:20%;">' . $option . '</td><td><input type="text" name="cfgvalue[' . $option . ']" size="90" value="' . @unserialize($value) . '" /></td><td style="text-align:center;width:20%;"><input type="checkbox" name="default[' . $option . ']" value="1" /></td></tr>';
    }
}

echo '
</table>
<center><input type="submit" name="mode" value="save" /></center>
</form>
';
}

function saveNewPaths( $dbserver, $dbuser, $dbpass, $dbname, $dbprefix ) {
    global $rescueFields;

    $db = @mysql_connect($dbserver,$dbuser,$dbpass) or die('Cannot connect to DB server');
    @mysql_select_db($dbname) or die('error selecting database');

    $sql = "SELECT * FROM " . $dbprefix . "conf_values WHERE group_name='Core' AND ((type <> 'subgroup') AND (type <> 'fieldset'))";

    $result = @mysql_query($sql,$db) or die('Cannot execute query');

    while ($row = mysql_fetch_array($result,MYSQL_ASSOC) ) {
        if ( in_array($row['name'],$rescueFields)) {
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
            $sql = "UPDATE " . $dbprefix . "conf_values SET value='" . $default[$option] . "' WHERE name='" . $option . "'";
            mysql_query($sql,$db);
            echo 'Resetting ' . $option . '<br />';
            $changed++;
        } else {
            $sVal = validateInput($value);
            if ( $config[$option] != $sVal ) {
                $fn = 'rescue_' . $option . '_validate';
                if (function_exists($fn)) {
                    $sVal = $fn($sVal);
                }
                $sql = "UPDATE " . $dbprefix . "conf_values SET value='" . serialize($sVal) . "' WHERE name='" . $option . "'";
                mysql_query($sql,$db);
                echo 'Saving ' . $option . '<br />';
                $changed++;
            }
        }
    }
    if ( $changed == 0 ) {
        echo 'No changes detected<br />';
    } else {
        @unlink($cfgvalue['path_data'] .'$$$config$$$.cache');
        @unlink($config['path_data'] .'$$$config$$$.cache');
    }
    echo '<br />Done!';
}

function printHeader() {

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta name="robots" content="noindex,nofollow" />
<title>glFusion Rescue Configuration Editor</title>
<style type="text/css">
.hover {background:#ccc;}
</style>
</head>
<body>
<center>
  <h1>glFusion Rescue Configuration Editor</h1></center>
';
}

function printFooter() {
echo '
</body>
</html>';
}

/*
 * Main processing
 */

printHeader();

$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
switch ( $mode ) {
    case 'submit' :
        $dbserver = $_POST['dbserver'];
        $dbuser   = $_POST['dbuser'];
        $dbpasswd = $_POST['dbpass'];
        $dbname   = $_POST['dbname'];
        $dbprefix = $_POST['dbprefix'];
        getNewPaths($dbserver,$dbuser,$dbpasswd,$dbname,$dbprefix);
        break;
    case 'save' :
        $dbserver = $_POST['dbserver'];
        $dbuser   = $_POST['dbuser'];
        $dbpasswd = $_POST['dbpass'];
        $dbname   = $_POST['dbname'];
        $dbprefix = $_POST['dbprefix'];
        saveNewPaths($dbserver,$dbuser,$dbpasswd,$dbname,$dbprefix);
        break;
    default :
        getDBLogin();
        break;
}

printFooter();
?>