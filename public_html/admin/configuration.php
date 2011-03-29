<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | configuration.php                                                        |
// |                                                                          |
// | Loads the administration UI and sends input to config.class              |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
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

require_once '../lib-common.php';
require_once 'auth.inc.php';

$conf_group = array_key_exists('conf_group', $_POST) ? COM_applyFilter($_POST['conf_group']) : 'Core';
$config =& config::get_instance();

/**
* Helper function: Provide language dropdown
*
* @return   Array   Array of (filename, displayname) pairs
*
* @note     Note that key/value are being swapped!
*
*/
function configmanager_select_language_helper()
{
    global $_CONF;

    return array_flip(MBYTE_languageList($_CONF['default_charset']));
}

/**
* Helper function: Provide themes dropdown
*
* @return   Array   Array of (filename, displayname) pairs
*
* @note     Beautifying code duplicated from usersettings.php
*
*/
function configmanager_select_theme_helper()
{
    $themes = array();

    $themeFiles = COM_getThemes(true);

    usort($themeFiles,
          create_function('$a,$b', 'return strcasecmp($a,$b);'));

    foreach ($themeFiles as $theme) {
        $words = explode ('_', $theme);
        $bwords = array ();
        foreach ($words as $th) {
            if ((strtolower ($th{0}) == $th{0}) &&
                (strtolower ($th{1}) == $th{1})) {
                $bwords[] = strtoupper ($th{0}) . substr ($th, 1);
            } else {
                $bwords[] = $th;
            }
        }

        $themes[implode(' ', $bwords)] = $theme;
    }
    return $themes;
}


/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_html_validate($value)
{
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

function configmanager_path_log_validate($value)
{
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

function configmanager_path_language_validate($value)
{
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

function configmanager_backup_path_validate($value)
{
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

function configmanager_path_data_validate($value)
{
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

function configmanager_path_images_validate($value)
{
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

function configmanager_path_pear_validate($value)
{
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

function configmanager_path_themes_validate($value)
{
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

function configmanager_site_url_validate($value)
{
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

function configmanager_site_admin_url_validate($value)
{
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

function configmanager_rdf_file_validate($value)
{
    $value = trim($value);
    return $value;
}

/**
* Helper function: Provide timezone dropdown
*
* @return   array   Array of (timezone-long-name, timezone-short-name) pairs
*
*/

function configmanager_select_timezone_helper()
{
    $locations = array();
    $zones = timezone_identifiers_list();
    foreach ($zones as $zone) {
        $zone = explode('/', $zone);
        if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific') {
            if (isset($zone[1]) != '') {
                $tzname = $zone[0].'/'.$zone[1];
                $locations[$tzname] = str_replace('_', ' ', $tzname);
            }
        }
    }
    return $locations;
}

$tokenstate = SEC_checkToken();

// MAIN
if (array_key_exists('set_action', $_POST) && $tokenstate){
    if (SEC_inGroup('Root')) {
        if ($_POST['set_action'] == 'restore') {
            $name = COM_applyFilter($_POST['name']);
            $config->restore_param($name, $conf_group);
        } elseif ($_POST['set_action'] == 'unset') {
            $name = COM_applyFilter($_POST['name']);
            $config->unset_param($name, $conf_group);
        }
    }
}

if (array_key_exists('form_submit', $_POST) && $tokenstate) {
    $result = null;
    if (! array_key_exists('form_reset', $_POST)) {
        $result = $config->updateConfig($_POST, $conf_group);
        CTL_clearCache();
        /*
         * An ugly hack to get the proper theme selected
         */
        if( $_CONF['allow_user_themes'] == 1 ) {
            if( isset( $_COOKIE[$_CONF['cookie_theme']] ) && empty( $_USER['theme'] )) {
                $theme = COM_sanitizeFilename($_COOKIE[$_CONF['cookie_theme']], true);
                if( is_dir( $_CONF['path_themes'] . $theme )) {
                    $_USER['theme'] = $theme;
                }
            }

            if( !empty( $_USER['theme'] )) {
                if( is_dir( $_CONF['path_themes'] . $_USER['theme'] )) {
                    $_CONF['theme'] = $_USER['theme'];
                    $_CONF['path_layout'] = $_CONF['path_themes'] . $_CONF['theme'] . '/';
                    $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_CONF['theme'];
                } else {
                    $_USER['theme'] = $_CONF['theme'];
                }
            }
        }
    }
    $sub_group = array_key_exists('sub_group', $_POST) ? COM_applyFilter($_POST['sub_group']) : null;
    echo $config->get_ui($conf_group, $sub_group, $result);
} else {
    $sub_group = array_key_exists('subgroup', $_POST) ? COM_applyFilter($_POST['subgroup']) : null;
    echo $config->get_ui($conf_group, $sub_group);
}
?>