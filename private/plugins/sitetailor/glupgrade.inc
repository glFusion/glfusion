<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | glupgrade.inc                                                            |
// |                                                                          |
// | Upgrade routines                                                         |
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

if (strpos ($_SERVER['PHP_SELF'], 'glupgrade.inc') !== false)
{
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'] . '/plugins/sitetailor/config.php';

/*
 * If we are being called glFusion's upgrade routine, there is no
 * COM_errorLog() function available.
 */

if (!function_exists('COM_errorLog') ) {
    function COM_errorLog() {
    }
}

function glfusion_plgupgrade_sitetailor()
{
    global $_TABLES, $_CONF, $_ST_CONF, $_DB_dbms, $TEMPLATE_OPTIONS;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='sitetailor'");

    switch( $currentVersion ) {
        case "0.00" :
            if ( SITETAILOR_upgrade_100() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='1.00' WHERE pi_name='sitetailor' LIMIT 1");
            }
        case "1.0.0" :
            if ( SITETAILOR_upgrade_200() == 0 ) {
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='2.00' WHERE pi_name='sitetailor' LIMIT 1");
            }
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_homepage='http://www.glfusion.org' WHERE pi_name='sitetailor'",1);
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='2.0.0' WHERE pi_name='sitetailor' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='sitetailor'") == $_ST_CONF['version']) {
        return true;
        exit;
    } else {
        return false;
    }
}

function SITETAILOR_upgrade_100() {
    global $_TABLES, $_CONF, $_ST_CONF;

    $_SQL = array();

    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("SiteTailor plugin 1.0.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Site Tailor plugin update",1);
            return 1;
        }
        next($_SQL);
    }
    return 0;
}

function SITETAILOR_upgrade_200() {
    global $_TABLES, $_CONF, $_ST_CONF;

    $_SQL = array();

    $_SQL['st_menus'] = "CREATE TABLE {$_TABLES['st_menus']} (
      `id` int(11) NOT NULL auto_increment,
      `menu_name` varchar(64) NOT NULL,
      `menu_type` tinyint(4) NOT NULL,
      `menu_active` tinyint(3) NOT NULL,
      `group_id` mediumint(9) NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `menu_name` (`menu_name`)
    ) TYPE=MyISAM;";

    $_SQL['st_menus_config'] = "CREATE TABLE {$_TABLES['st_menus_config']} (
      `id` int(11) NOT NULL auto_increment,
      `menu_id` int(11) NOT NULL,
      `conf_name` varchar(64) NOT NULL,
      `conf_value` varchar(64) NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `Config` (`menu_id`,`conf_name`),
      KEY `menu_id` (`menu_id`)
    ) TYPE=MyISAM;";

    $_SQL_DEF = array();

    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus']} VALUES(1, 'navigation', 1, 1, 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus']} VALUES(2, 'footer', 2, 1, 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus']} VALUES(3, 'block', 3, 1, 2);";

    // footer menu color defaults
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'main_menu_bg_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'main_menu_hover_bg_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'main_menu_text_color', '#3677c0');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'main_menu_hover_text_color', '#679ef1');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'submenu_text_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'submenu_hover_text_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'submenu_background_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'submenu_hover_bg_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'submenu_highlight_color', '#999999');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'submenu_shadow_color', '#000000');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'menu_alignment', '0');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(2, 'use_images', '0');";

    // block menu color defaults
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'main_menu_bg_color', '#DDDDDD');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'main_menu_hover_bg_color', '#BBBBBB');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'main_menu_text_color', '#0000ff');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'main_menu_hover_text_color', '#FFFFFF');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'submenu_text_color', '#0000FF');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'submenu_hover_text_color', '#FFFFFF');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'submenu_background_color', '#DDDDDD');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'submenu_hover_bg_color', '#BBBBBB');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'submenu_highlight_color', '#999999');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'submenu_shadow_color', '#999999');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'menu_alignment', '0');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'use_images', '1');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'menu_bg_filename', 'menu_bg.gif');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'menu_hover_filename', 'menu_hover_bg.gif');";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menus_config']} (`menu_id`, `conf_name`, `conf_value`) VALUES(3, 'menu_parent_filename', 'menu_parent.png');";

    // default footer menu elements
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 2, 'Home', 2, '0', 10, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 2, 'Contribute', 2, '1', 30, 1, '', '', 13);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 2, 'Search', 2, '4', 20, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 2, 'Site Stats', 2, '5', 40, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 2, 'Contact Us', 6, '%site_url%/profiles.php?uid=2', 50, 1, '%site_url%/profiles.php?uid=2', '', 2);";

    // default block menu elements
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'Home', 2, '0', 10, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'Downloads', 4, 'filemgmt', 20, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'Forums', 4, 'forum', 30, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'Topic Menu', 3, '3', 40, 1, '', '', 2);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'User Menu', 3, '1', 50, 1, '', '', 13);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'Admin Options', 3, '2', 60, 1, '', '', 1);";
    $_SQL_DEF[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES(0, 3, 'Logout', 6, '%site_url%/users.php?mode=logout', 70, 1, '%site_url%/users.php?mode=logout', '', 13);";


    /* Execute SQL now to perform the upgrade */
    for ($i = 1; $i <= count($_SQL); $i++) {
        COM_errorLOG("SiteTailor plugin 2.0.0 update: Executing SQL => " . current($_SQL));
        DB_query(current($_SQL),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Site Tailor plugin update",1);
            return 1;
        }
        next($_SQL);
    }

    DB_query("UPDATE {$_TABLES['st_menu_elements']} SET menu_id=1",1);

    $result = DB_query("SELECT * FROM {$_TABLES['st_menu_config']} WHERE menu_id=0",1);
    if ( DB_numRows($result) > 0 ) {
        list($id,$menu_id,$tmbg,$tmh,$tmt,$tmth,$smth,$smbg,$smh,$sms,$gorc,$bgimage,$hoverimage,$parentimage,$alignment,$enabled) = DB_fetchArray($result);

        $mc['main_menu_bg_color']           = $tmbg;
        $mc['main_menu_hover_bg_color']     = $tmh;
        $mc['main_menu_text_color']         = $tmt;
        $mc['main_menu_hover_text_color']   = $tmth;
        $mc['submenu_text_color']           = $smth;
        $mc['submenu_hover_text_color']     = '#679EF1';
        $mc['submenu_background_color']     = $smbg;
        $mc['submenu_hover_bg_color']       = $smh;
        $mc['submenu_highlight_color']      = $sms;
        $mc['submenu_shadow_color']         = $sms;
        $mc['menu_bg_filename']             = $bgimage;
        $mc['menu_hover_filename']          = $hoverimage;
        $mc['menu_parent_filename']         = $parentimage;
        $mc['menu_alignment']               = $alignment;
        $mc['use_images']                   = $enabled;

        $menu_id = 1;

        foreach ($mc AS $name => $value) {
            DB_save($_TABLES['st_menus_config'],"menu_id,conf_name,conf_value","$menu_id,'$name','$value'");
        }
    }

    for ($i = 1; $i <= count($_SQL_DEF); $i++) {
        COM_errorLOG("SiteTailor plugin 2.0.0 update: Executing SQL => " . current($_SQL_DEF));
        DB_query(current($_SQL_DEF),1);
        if (DB_error()) {
            COM_errorLog("SQL Error during Site Tailor plugin update",1);
            return 1;
        }
        next($_SQL_DEF);
    }
    CACHE_remove_instance('stmenu');
    return 0;
}
?>