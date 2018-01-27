<?php

/**
* File: SLC.Examine.class.php
* This is the Spam Link Counter Examine class for the glFusion Spam-X plugin
*
* Copyright (C) 2016-2018 by the following authors:
* Author        Mark R. Evans       mark AT glfusion DOT org
*
* Licensed under the GNU General Public License
*
* @package Spam-X
* @subpackage Modules
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
* Include Base Classes
*/
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'BaseCommand.class.php';
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'SLCbase.class.php';

/**
* Check number of links in post
*
* @author Mark R. Evans     mark AT glfusion DOT org
* based on the works of Tom Willet (Spam-X) and Lee Garner
* @package Spam-X
*
*/
class SLC extends BaseCommand {
    /**
     * No Constructor Use BaseCommand constructor
     */

    /**
     * Here we do the work
     */
    function execute ($comment,$data)
    {
        global $_USER, $_SPX_CONF, $LANG_SX00;

        if ( !isset($_SPX_CONF['slc_enable']) || $_SPX_CONF['slc_enable'] == 0 ) {
            return false;
        }

        if ( !isset($_SPX_CONF['slc_max_links'])) {
            $_SPX_CONF['slc_max_links'] = 5;
        }

        $tooManyLinks = 0;

        if (isset ($_USER['uid']) && ($_USER['uid'] > 1)) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }

        $slc = new SLCbase();
        $linkCount = $slc->CheckForSpam ($comment);
        if ( $linkCount > $_SPX_CONF['slc_max_links']) {
            SPAMX_log ($LANG_SX00['foundspam'] . 'Spam Link Counter (SLC)'.
                       $LANG_SX00['foundspam2'] . $uid .
                       $LANG_SX00['foundspam3'] . $_SERVER['REMOTE_ADDR']);
            $tooManyLinks = 1;
            SESS_setVar('spamx_msg','Too many links in post');
        }

        // tell the Action module that we've already been triggered
        $GLOBALS['slc_triggered'] = true;

        return $tooManyLinks;
    }
}

?>
