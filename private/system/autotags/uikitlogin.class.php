<?php
/**
 * @package    glFusion CMS
 *
 * @copyright   Copyright (C) 2014-2016 by the following authors
 *              Mark R. Evans          mark AT glfusion DOT org
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class autotag_uikitlogin extends BaseAutotag {

    public function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['uikitlogin']['description'];
    }

    public function parse($p1, $p2='', $fulltag = '')
    {
        global $_CONF, $LANG01, $LANG04, $_SYSTEM;

        $retval = '';

        $modal = (int)$p1;
        if ($modal != 0) {
            $modal = 1;
        }

        $T = new \Template($_CONF['path_layout'] . '/autotags');
        $T->set_file('at', 'uikitlogin.thtml');
        $T->set_var('modal', $modal);

        if ( COM_isAnonUser() ) {
            // Anonymous user, show the login button.
            // Options sent to SEC_loginForm()
            $options = array(
                'hide_forgotpw_link' => false,
                'form_action'        => $_CONF['site_url'].'/users.php',
                'plugin_vars'        => true,
                'title'     => sprintf($LANG04[65],$_CONF['site_name']), // log in to {site_name}
                'message' => '', //$LANG04[66]; // please enter your user name and password below
            );

            $T->set_var('login_button', true);
            $T->set_var('lang_login', $LANG01[58]);
            $T->set_var('login_form', SEC_loginForm($options));
        } else {
            // Already logged in, show the user "My Account" menu.
            $T->set_var('lang_header', $LANG01[47]);
            $T->set_var('lang_login', $LANG01['47']);
            $T->set_block('at', 'MenuItems', 'items');
            $userMenu = getUserMenu();
            foreach ($userMenu as $option) {
                $T->set_var('url', $option['url']);
                $T->set_var('label', $option['label']);
                $T->parse('items', 'MenuItems', true);
            }
        }
        $retval .= $T->finish($T->parse('output', 'at'));
        return $retval;
    }
}
