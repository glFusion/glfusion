<?php
/**
* glFusion CMS
*
* glFusion Search Engine Admin Functions
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017-2018 Lee Garner - lee AT leegarner DOT com
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
*   Create the main menu
*
*   @param  string  $sel    Selected option
*   @return string  HTML for menu area
*/
function SEARCH_adminMenu($sel = 'default')
{

    global $_CONF, $LANG_ADMIN, $LANG_SEARCH, $LANG_SEARCH_ADMIN;

    $retval = '';

    $T = new Template($_CONF['path_layout'].'admin/search');
    $T->set_file('admin', 'admin_header.thtml');

    $token = SEC_createToken();

    $menu_arr = array(
        array(
                'url'  => $_CONF['site_admin_url'].'/search.php',
                'text' => $LANG_SEARCH_ADMIN['search_admin'],
                'active' => $sel == 'counters' ? true : false,
                ),
        array(
                'url'   => $_CONF['site_admin_url'].'/reindex.php',
                'text'  => $LANG_SEARCH_ADMIN['reindex_title'],
                'active' => $sel == 'reindex' ? true : false,
                ),
        array(
                'url' => $_CONF['site_admin_url'].'/index.php',
                'text' => $LANG_ADMIN['admin_home']
                )
    );

    $explanation =  $LANG_SEARCH_ADMIN['hlp_' . $sel];

    $T->set_var('start_block', COM_startBlock($LANG_SEARCH_ADMIN['search_admin'], '',
                        COM_getBlockTemplate('_admin_block', 'header')));

    $T->set_var('admin_menu',ADMIN_createMenu(
                $menu_arr,
                $explanation,
                $_CONF['layout_url'] . '/images/icons/search.png')
    );

    $T->set_var('end_block',COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}
?>