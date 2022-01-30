<?php
/**
 * Class to provide admin and user-facing menus.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2019-2021 Lee Garner <lee@leegarner.com>
 * @package     forum
 * @version     v1.3.0
 * @since       v0.7.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forum;


/**
 * Class to provide admin and user-facing menus.
 * @package forum
 */
class Menu
{

    /**
     * Create the administrator sub-menu for the Catalog option.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function adminWarnings($view='')
    {
        global $_CONF, $LANG_GF01;

        $menu_arr = array(
            array(
                'url'  => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?listlevels',
                'text' => $LANG_GF01['warning_levels'],
                'active' => $view == 'listlevels' ? true : false,
            ),
            array(
                'url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?listtypes',
                'text' => $LANG_GF01['warning_types'],
                'active' => $view == 'listtypes' ? true : false,
            ),
            array(
                'url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?log',
                'text' => $LANG_GF01['log'],
                'active' => $view == 'log' ? true : false,
            ),
        );
        return self::_makeSubMenu($menu_arr);
    }


    /**
     * Create a submenu using a standard template.
     *
     * @param   array   $menu_arr   Array of menu items
     * @return  string      HTML for the submenu
     */
    private static function _makeSubMenu($menu_arr)
    {
        global $_CONF;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/');
        $T->set_file('menu', 'submenu.thtml');
        $T->set_block('menu', 'menuItems', 'items');
        $hlp = '';
        foreach ($menu_arr as $mnu) {
            if ($mnu['active'] && isset($mnu['help'])) {
                $hlp = $mnu['help'];
            }
            $url = COM_createLink($mnu['text'], $mnu['url']);
            $T->set_var(array(
                'active'    => $mnu['active'],
                'url'       => $url,
            ) );
            $T->parse('items', 'menuItems', true);
        }
        $T->set_var('help', $hlp);
        $T->parse('output', 'menu');
        $retval = $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Display only the page title.
     * Used for pages that do not feature a menu, such as the catalog.
     *
     * @param   string  $page_title     Page title text
     * @param   string  $page           Page name being displayed
     * @return  string      HTML for page title section
     */
    public static function pageTitle($page_title = '', $page='')
    {
        global $_USER;

        $T = new Template;
        $T->set_file('title', 'forum_title.thtml');
        $T->set_var(array(
            'title' => $page_title,
            'is_admin' => plugin_ismoderator_forum(),
            'link_admin' => plugin_ismoderator_forum(),
            'link_account' => ($page != 'account' && $_USER['uid'] > 1),
        ) );
        if ($page != 'cart' && Cart::getCartID()) {
            $item_count = Cart::getInstance()->hasItems();
            if ($item_count) {
                $T->set_var('link_cart', $item_count);
            }
        }
        return $T->parse('', 'title');
    }


    /**
     * Display the site header, with or without blocks according to configuration.
     *
     * @param   string  $title  Title to put in header
     * @param   string  $meta   Optional header code
     * @return  string          HTML for site header, from COM_siteHeader()
     */
    public static function siteHeader($title='', $meta='')
    {
        global $LANG_SHOP;

        $retval = '';
        if ($title == '') {
            $title = $LANG_SHOP['main_title'];
        }

        switch(Config::get('displayblocks')) {
        case 2:     // right only
        case 0:     // none
            $retval .= COM_siteHeader('none', $title, $meta);
            break;

        case 1:     // left only
        case 3:     // both
        default :
            $retval .= COM_siteHeader('menu', $title, $meta);
            break;
        }

        if (!Config::get('forum_enabled')) {
            $retval .= '<div class="uk-alert uk-alert-danger">' . $LANG_SHOP['forum_closed'] . '</div>';
        }
        return $retval;
    }


    /**
     * Display the site footer, with or without blocks as configured.
     *
     * @return  string      HTML for site footer, from COM_siteFooter()
     */
    public static function siteFooter()
    {
        $retval = '';

        switch(Config::get('displayblocks')) {
        case 2 : // right only
        case 3 : // left and right
            $retval .= COM_siteFooter();
            break;

        case 0: // none
        case 1: // left only
        default :
            $retval .= COM_siteFooter();
            break;
        }
        return $retval;
    }

}
