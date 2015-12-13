<?php

/**
* File: SFS.Action.class.php
* This is the Stop Forum Spam Action class for the glFusion Spam-X plugin
*
* Copyright (C) 2011 by the following authors:
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
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'SFSbase.class.php';

/**
* Sends IP to SFS (stopforumspam.org)
*
* @author Mark R. Evans     mark AT glfusion DOT org
* based on the works of Tom Willet (Spam-X) and Lee Garner
* @package Spam-X
*
*/
class SFSreport extends BaseCommand {
    /**
     * Constructor
     */
    function __construct()
    {
        global $num;

        $num = 128;
    }

    /**
     * Perform the check
     */
    function execute ($comment)
    {
        global $result;

        $result = 128;

        if (isset ($GLOBALS['sfs_triggered']) && $GLOBALS['sfs_triggered']) {
            // the Examine class already reported these to SFS
            return 1;
        }

        $sfs = new SFSbase();
        $sfs->CheckForSpam ($comment);

        return 1;
    }
}

?>