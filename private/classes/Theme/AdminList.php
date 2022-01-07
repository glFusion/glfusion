<?php
/**
 * Class to display the theme admin list.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020-2022 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Theme;
use glFusion\Database\Database;
use Template;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 * This class handles saving and retrieving logo information for
 * different themes.
 * @package glfusion
 */
class AdminList extends Theme
{
    /**
     * Logo Admin List View.
     *
     * @return  string      HTML for the logo list.
     */
    public static function render() : string
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_LOGO, $_IMAGE_TYPE;

        $dbThemes = self::getThemes();
        $data_arr = array(
            array(
                'theme' => self::$default,
                'logo_type' => (int)$dbThemes[self::$default]['logo_type'],
                'display_site_slogan' => (int)$dbThemes[self::$default]['display_site_slogan'],
                'logo_file' => $dbThemes[self::$default]['logo_file'],
                'disabled' => 1,
                'grp_access' => 0,
            )
        );

        $tmp = array_diff(scandir($_CONF['path_themes']), array('.', '..'));
        if ($tmp !== false) {
            foreach ($tmp as $dirname) {
                if (self::pathExists($dirname)) {
                    if (isset($dbThemes[$dirname])) {
                        // Theme already exists in the database, use those values
                        $logo_type = (int)$dbThemes[$dirname]['logo_type'];
                        $show_slogan = (int)$dbThemes[$dirname]['display_site_slogan'];
                        $logo_file = $dbThemes[$dirname]['logo_file'];
                        if ($dbThemes[$dirname]['theme'] == $_CONF['theme']) {
                            $grp_access = 2;
                        } else {
                            $grp_access = $dbThemes[$dirname]['grp_access'];
                        }
                    } else {
                        // Theme not saved in DB yet, use default values
                        $logo_type = 0;
                        $show_slogan = 0;
                        $logo_file = '';
                        $grp_access = 2;
                    }
                    $data_arr[] = array(
                        'theme' => $dirname,
                        'logo_type' => $logo_type,
                        'display_site_slogan' => $show_slogan,
                        'logo_file' => $logo_file,
                        'disabled' => 0,
                        'grp_access' => $grp_access,
                    );
                }
            }
        }

        USES_lib_admin();

        $menu_arr = array(
            array(
                'url'  => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home'],
            ),
        );

        $retval = '';
        $retval .= COM_startBlock($LANG_LOGO['logo_options'],'', COM_getBlockTemplate('_admin_block', 'header'));
        $retval .= ADMIN_createMenu(
            $menu_arr,
            $LANG_LOGO['instructions'],
            $_CONF['layout_url'] . '/images/icons/logo.' . $_IMAGE_TYPE
        );
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

        $T = new \Template($_CONF['path_layout'] . '/admin/logo');
        $T->set_file('form', 'themes.thtml');
        $T->set_block('form', 'dataRow', 'DR');
        foreach ($data_arr as $A){
            $img_path = $_CONF['path_html'] . '/images/' . $A['logo_file'];
            if (!is_file($img_path)) {
                $img_path = '';
                $img_url = '';
            } else {
                $img_url = COM_createImage(
                    $_CONF['site_url'] . '/images/' . $A['logo_file'],
                    _('Logo Image'),
                    array(
                        'class' => 'themeAdminImage',
                    )
                );
            }
            $T->clear_var('type_sel_-1');
            $T->clear_var('type_sel_0');
            $T->clear_var('type_sel_1');
            $T->clear_var('type_sel_2');
            $T->clear_var('slogan_sel_-1');
            $T->clear_var('slogan_sel_0');
            $T->clear_var('slogan_sel_1');
            $T->set_var(array(
                'theme_name'    => $A['theme'],
                'is_default'    => $A['theme'] == self::$default,
                'type_sel_' . $A['logo_type'] => 'selected="selected"',
                'slogan_sel_' . $A['display_site_slogan'] => 'selected="selected"',
                'img_path'      => $img_path,
                'img_url'       => $img_url,
                'type_sel'      => $A['logo_type'],
                'slogan_sel'    => $A['display_site_slogan'],
                'disabled'      => $A['disabled'],
                'old_gid'       => $A['grp_access'],
                'is_site_theme' => $A['theme'] == $_CONF['theme'],
                'grp_access_options' => COM_optionList(
                    $_TABLES['groups'],
                    'grp_id,grp_name',
                    $A['grp_access'],
                    1
                ),
                'grp_0_sel' => $A['grp_access'] == 0 ? 'selected="selected"' : '',
            ) );
            $T->parse('DR', 'dataRow', true);
        }
        $T->parse('output', 'form');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }

}
