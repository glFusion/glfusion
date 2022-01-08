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

        $themes = self::getAll(true, false);
        $themes[Theme::DEFAULT_NAME] = self::getDefault();
        // Sync added and removed themes from the filesystem
        $themes = self::syncFilesystem($themes);
        ksort($themes);

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

        $T = new \Template($_CONF['path_layout'] . '/admin');
        $T->set_file('form', 'themes.thtml');
        $T->set_block('form', 'dataRow', 'DR');
        foreach ($themes as $Theme){
            $img_path = $Theme->getImagePath();
            if (!is_file($img_path)) {
                $img_path = '';
                $img_url = '';
            } else {
                $img_url = COM_createImage(
                    $_CONF['path_images_url'] . '/' . $Theme->getImageName(),
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
                'theme_name'    => $Theme->getName(),
                'is_default'    => $Theme->getName() == self::DEFAULT_NAME,
                'type_sel_' . $Theme->getLogoType() => 'selected="selected"',
                'slogan_sel_' . $Theme->displaySlogan() => 'selected="selected"',
                'img_path'      => $img_path,
                'img_url'       => $img_url,
                'type_sel'      => $Theme->getLogoType(),
                'slogan_sel'    => $Theme->displaySlogan(),
                'old_gid'       => $Theme->getGrpAccess(),
                'is_site_theme' => $Theme->getName() == $_CONF['theme'],
                'grp_access_options' => COM_optionList(
                    $_TABLES['groups'],
                    'grp_id,grp_name',
                    $Theme->getGrpAccess(),
                    1
                ),
                'grp_0_sel' => $Theme->getGrpAccess() == 0 ? 'selected="selected"' : '',
            ) );
            $T->parse('DR', 'dataRow', true);
        }
        $T->parse('output', 'form');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }

}
