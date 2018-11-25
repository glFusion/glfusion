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

    public function parse($p1, $p2='', $fulltag)
    {
        global $_CONF, $LANG01, $LANG04;

        $retval = '';
        $modal = 0;

        if ( $p1 != 0 && $p1 != 1 ) $p1 = 1;

        $modal = (int) $p1;
        if ( $modal != 0 && $modal != 1) $modal = 0;

        if ( COM_isAnonUser() ) {
            $options = array(
                'hide_forgotpw_link' => false,
                'form_action'        => $_CONF['site_url'].'/users.php',
                'plugin_vars'        => true,
            );
            $options['title']   = sprintf($LANG04[65],$_CONF['site_name']); // log in to {site_name}
            $options['message'] = ''; //$LANG04[66]; // please enter your user name and password below

            $retval .= '<div class="uk-navbar-content uk-navbar-flip uk-hidden-small">';

            if ( $modal == 0 ) {
                $retval .= '<button class="uk-button uk-button-success tm-button-login" type="button" data-uk-modal="{target:\'#modalOpen\'}">'.$LANG01[58].'</button></div>';
                $retval .= '<div id="modalOpen" class="uk-modal">';
                $retval .= '<div class="uk-modal-dialog uk-modal-dialog-medium"><a href="#" class="uk-modal-close uk-close"></a>';
                $retval .= SEC_loginForm($options);
                $retval .= '</div></div>';
                $retval .= "
                <script>
                $('#modalOpen').on({ 'show.uk.modal': function(){ $('#loginname').focus(); }, });
                </script>
                ";
            } else {
                $retval .= '<a href="'.$_CONF['site_url'].'/users.php" class="uk-button uk-button-success" type="button">'.$LANG01[58].'</a></div>';
            }
        } else {
            $retval .= '<ul class="uk-navbar-nav tm-navbar-nav uk-navbar-flip uk-margin-right">';
            $retval .= '<li class="uk-parent uk-hidden-small" data-uk-dropdown>';
            $retval .= '<a href="#">'.$LANG01[47].'&nbsp;<i class="uk-icon-caret-down"></i></a>';
            $retval .= '<div class="uk-dropdown tm-dropdown uk-dropdown-navbar">';
            $retval .= '<ul class="uk-nav uk-nav-navbar tm-nav-navbar">';
            $userMenu = getUserMenu();
            foreach ($userMenu as $option) {
                $retval .= '<li><a href="'.$option['url'].'">'.$option['label'].'</a></li>';
            }
            $retval .= '</ul></div></li></ul>';
        }
        return $retval;
    }
}
?>
