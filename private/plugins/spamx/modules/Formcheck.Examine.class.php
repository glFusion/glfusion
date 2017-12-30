<?php
/**
* File: Formcheck.class.php
* Formcheck Examine Class
*
* Copyright (C) 2017 by the following authors:
* Author        Mark R. Evans   mark AT glfusion DOT org
*
* Licensed under the GNU General Public License
*
* @package Spam-X
* @subpackage Modules
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

// Include Base Classes
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'BaseCommand.class.php';

/**
 * Analyzes POST vars
 *
 * @package Spam-X
 */
class Formcheck extends BaseCommand
{
    /**
     * Here we do the work
     *
     * @param  string $comment
     * @param  array $data
     * @return int      true or false
     */
    public function execute($comment, $data)
    {
        global $_CONF, $_USER, $_SPX_CONF, $LANG_SX00, $REMOTE_ADDR;
        $retval = false;
        if ( isset($_POST['fcfield'] ) ) {
            $rand = COM_applyFilter($_POST['fcfield']);
            $fieldName = 'fc_email_'.$rand;
            if ( isset($_POST[$fieldName] ) && $_POST[$fieldName] != '' ) {
                SPAMX_log('FormCheck: spam identified in ' . $data['type'] . ' - ' . $REMOTE_ADDR);
                if ( function_exists('bb2_ban') ) {
                    bb2_ban($REMOTE_ADDR,4);
                }
                $retval = true;
            }
        }
        return $retval;
    }
}