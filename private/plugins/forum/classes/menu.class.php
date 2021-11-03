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
     * Create the user menu.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function User($view='')
    {
        global $_CONF, $LANG_SHOP;

        USES_lib_admin();

        $hdr_txt = SHOP_getVar($LANG_SHOP, 'user_hdr_' . $view);
        $menu_arr = array(
            array(
                'url'  => SHOP_URL . '/index.php',
                'text' => $LANG_SHOP['back_to_catalog'],
            ),
            array(
                'url'  => COM_buildUrl(SHOP_URL . '/account.php?mode=orderhist'),
                'text' => $LANG_SHOP['purchase_history'],
                'active' => $view == 'orderhist' ? true : false,
            ),
            array(
                'url' => COM_buildUrl(SHOP_URL . '/account.php?mode=addresses'),
                'text' => $LANG_SHOP['addresses'],
                'active' => $view == 'addresses' ? true : false,
            ),
        );

        // Show the Gift Cards menu item only if enabled.
        if (Config::get('gc_enabled')) {
            $active = $view == 'couponlog' ? true : false;
            $menu_arr[] = array(
                'url'  => COM_buildUrl(SHOP_URL . '/account.php?mode=couponlog'),
                'text' => $LANG_SHOP['gc_activity'],
                'active' => $active,
                'link_admin' => plugin_ismoderator_forum(),
            );
        }

        // Show the Affiliate Sales item only if enabled.
        if (Config::get('aff_enabled')) {
            $menu_arr[] = array(
                'url' => COM_buildUrl(SHOP_URL . '/affiliate.php'),
                'text' => $LANG_SHOP['affiliates'],
                'active' => $view == 'affiliate' ? true : false,
            );
        }
        
        return \ADMIN_createMenu($menu_arr, $hdr_txt);
    }


    /**
     * Create the administrator menu.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function Admin($view='')
    {
        global $_CONF, $LANG_ADMIN, $LANG_SHOP;

        USES_lib_admin();
        if (isset($LANG_SHOP['admin_hdr_' . $view]) &&
            !empty($LANG_SHOP['admin_hdr_' . $view])) {
            $hdr_txt = $LANG_SHOP['admin_hdr_' . $view];
        } else {
            $hdr_txt = '';
        }

        $menu_arr = array(
            array(
                'url' => SHOP_ADMIN_URL . '/index.php?products',
                'text' => $LANG_SHOP['catalog'],
                'active' => $view == 'products' ? true : false,
            ),
            array(
                'url' => SHOP_ADMIN_URL . '/orders.php',
                'text' => $LANG_SHOP['orders'],
                'active' => $view == 'orders' || $view == 'shipments' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/index.php?shipping=x',
                'text' => $LANG_SHOP['shipping'],
                'active' => $view == 'shipping' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/gateways.php',
                'text' => $LANG_SHOP['gateways'],
                'active' => $view == 'gwadmin' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/index.php?wfadmin=x',
                'text' => $LANG_SHOP['mnu_wfadmin'],
                'active' => $view == 'wfadmin' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/report.php',
                'text' => $LANG_SHOP['reports'],
                'active' => $view == 'reports' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/regions.php',
                'text' => $LANG_SHOP['regions'],
                'active' => $view == 'regions' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/index.php?other=x',
                'text' => $LANG_SHOP['other_func'],
                'active' => $view == 'other' ? true : false,
            ),
        );
        if (Config::get('aff_enabled')) {
            $menu_arr[] = array(
                'url' => SHOP_ADMIN_URL . '/affiliates.php',
                'text' => $LANG_SHOP['affiliates'],
                'active' => $view == 'affiliates' ? true : false,
            );
        }
        $menu_arr[] = array(
            'url'  => $_CONF['site_admin_url'],
            'text' => $LANG_ADMIN['admin_home'],
        );

        $T = new Template;
        $T->set_file('title', 'forum_title.thtml');
        $T->set_var(array(
            'title' => $LANG_SHOP['admin_title'] . ' (' . Config::get('pi_version') . ')',
            'link_store' => true,
            'icon'  => plugin_geticon_forum(),
            'is_admin' => true,
            'link_catalog' => true,
        ) );
        $todo_arr = self::AdminTodo();
        if (!empty($todo_arr)) {
            $todo_list = '';
            foreach ($todo_arr as $item_todo) {
                $todo_list .= "<li>$item_todo</li>" . LB;
            }
            $T->set_var('todo', '<ul>' . $todo_list . '</ul>');
        }
        $retval = $T->parse('', 'title');
        $retval .= \ADMIN_createMenu(
            $menu_arr,
            $hdr_txt,
            plugin_geticon_forum()
        );
        return $retval;
    }


    /**
     * Create the administrator sub-menu for the Region option.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function adminRegions($view='')
    {
        global $LANG_SHOP;

        $menu_arr = array(
            array(
                'url'  => SHOP_ADMIN_URL . '/regions.php?regions',
                'text' => $LANG_SHOP['regions'],
                'active' => $view == 'regions' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/regions.php?countries',
                'text' => $LANG_SHOP['countries'],
                'active' => $view == 'countries' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/regions.php?states',
                'text' => $LANG_SHOP['states'],
                'active' => $view == 'states' ? true : false,
            ),
            array(
                'url'  => SHOP_ADMIN_URL . '/rules.php',
                'text' => $LANG_SHOP['rules'],
                'active' => $view == 'rules' ? true : false,
            ),
        );
        return self::_makeSubMenu($menu_arr);
    }


    /**
     * Create the administrator sub-menu for the Catalog option.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function adminWarnings($view='')
    {
        global $_CONF;

        $menu_arr = array(
            array(
                'url'  => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?listlevels',
                'text' => 'Warning Levels',
                'active' => $view == 'listlevels' ? true : false,
            ),
            array(
                'url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?listtypes',
                'text' => 'Warning Types',
                'active' => $view == 'listtypes' ? true : false,
            ),
            array(
                'url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?log',
                'text' => 'Log',
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
