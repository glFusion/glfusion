<?php
/**
* File: SFS.Examine.class.php
* This is the Stop Forum Spam Examine class for the glFusion Spam-X plugin
*
* Copyright (C) 2011-2019 by the following authors:
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

use \glFusion\Log\Log;

/**
* Sends IP to SFS (stopforumspam.org) for examination
*
* @author Mark R. Evans     mark AT glfusion DOT org
* based on the works of Tom Willet (Spam-X) and Lee Garner
* @package Spam-X
*
*/
class SFS extends BaseCommand {
    /**
     * No Constructor Use BaseCommand constructor
     */

    /**
     * Here we do the work
     */
    function execute ($comment,$data)
    {
        global $_USER, $_SPX_CONF, $LANG_SX00;

        if ( !isset($_SPX_CONF['sfs_enable']) || $_SPX_CONF['sfs_enable'] == 0 ) {
            return false;
        }

        $ans = 0;

        if (isset ($_USER['uid']) && ($_USER['uid'] > 1)) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }

        $sfs = new SFSbase();
        if ($sfs->CheckForSpam ($comment,$data)) {
            $ans = 1;
            Log::write('system',Log::INFO,
                        $LANG_SX00['foundspam'] . 'Stop Forum Spam (SFS)'.
                        $LANG_SX00['foundspam2'] . $uid .
                        $LANG_SX00['foundspam3'] . $_SERVER['REAL_ADDR']);
            SESS_setVar('spamx_msg','Failed Stop Forum Spam IP / username check');
            if ( function_exists('bb2_ban') ) {
                bb2_ban($_SERVER['REAL_ADDR'],4);
            }
        }
        $this->response = $sfs->getResponse();
        return $ans;
    }

	public function getResponse()
	{
	    return $this->response;
	}
}

?>