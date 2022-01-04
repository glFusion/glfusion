<?php
/**
 * Class to provide admin and user-facing menus.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     filemgmt
 * @version     v1.9.0
 * @since       v0.9.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt;


/**
 * Class to provide admin and user-facing menus.
 * @package filemgmt
 */
class Menu
{
    /**
     * Create the administrator menu.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function Admin($view='')
    {
        global $_CONF, $_FM_CONF, $LANG_FM02, $_TABLES, $LANG_ADMIN, $LANG_FILEMGMT;

        USES_lib_admin();
        $retval = '';
        $totalnewdownloads = DB_count($_TABLES['filemgmt_filedetail'],'status',0);
        $totalbrokendownloads = DB_count($_TABLES['filemgmt_brokenlinks']);
        $index_url = $_FM_CONF['admin_url'] . '/index.php';
        $menu_arr = array (
            array(
                'url' => $index_url . '?files',
                'text' => $LANG_FILEMGMT['Filelisting'],
                'active' => $view == 'files',
            ),
            array(
                'url' => $index_url . '?categoryConfigAdmin',
                'text' => $LANG_FM02['nav2'],
                'active' => $view == 'categoryConfigAdmin',
            ),
            /*array(
                'url' => $index_url . '?listNewDownloads',
                'text' => sprintf($LANG_FM02['nav4'],$totalnewdownloads),
                'active' => $view == 'listNewDownloads',
            ),*/
            array(
                'url' => $index_url . '?listBrokenLinks',
                'text' => sprintf($LANG_FM02['nav5'],$totalbrokendownloads),
                'active' => $view == 'listBrokenLinks',
            ),
            array(
                'url' => $_CONF['site_admin_url'] . '/index.php',
                'text' => $LANG_ADMIN['admin_home'],
            )
        );
        $retval .= COM_startBlock(_MD_ADMINTITLE, '',COM_getBlockTemplate ('_admin_block', 'header'));
        $retval .= \ADMIN_createMenu(
            $menu_arr,
            $LANG_FM02['instructions'],
            $_FM_CONF['url'] . '/images/filemgmt.png'
        );
        return $retval;
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
        global $_FM_CONF;

        $retval = '';

        switch( $_FM_CONF['displayblocks'] ) {
        case 0 : // left only
        case 2 :
            $retval .= COM_siteHeader('menu',$title,$meta);
            break;
        case 1 : // right only
        case 3 :
            $retval .= COM_siteHeader('none',$title,$meta);
            break;
        default :
            $retval .= COM_siteHeader('menu',$title,$meta);
            break;
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
        global $_CONF, $_FM_CONF;

        $retval = '';

        switch( $_FM_CONF['displayblocks'] ) {
        case 0 : // left only
        case 3 : // none
            $retval .= COM_siteFooter();
            break;
        case 1 : // right only
        case 2 : // left and right
            $retval .= COM_siteFooter( true );
            break;
        default :
            $retval .= COM_siteFooter();
            break;
        }
        return $retval;
    }

}
