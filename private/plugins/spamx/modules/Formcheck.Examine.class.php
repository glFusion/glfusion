<?php
/**
* File: Formcheck.class.php
* Formcheck Examine Class
*
* Copyright (C) 2017=2019 by the following authors:
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

use \glFusion\Log\Log;

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
        global $_CONF, $_USER, $_SPX_CONF, $LANG_SX00;

        if (!isset($_SPX_CONF['fc_enable']) || $_SPX_CONF['fc_enable'] == 0) {
            return false;
        }

        $retval = false;
        if (isset($_POST['fcfield'])) {
            $rand = filter_input(INPUT_POST,'fcfield',FILTER_SANITIZE_STRING);
            $fieldName = 'fc_email_'.$rand;
            if (isset($_POST[$fieldName] ) && $_POST[$fieldName] != '') {
                if (!isset($data['type'])) {
                    $data['type'] = 'default';
                }
                Log::write('system',Log::WARNING,'FormCheck: spam identified in ' . $data['type'] . ' - ' . $_SERVER['REAL_ADDR']);
                if ( function_exists('bb2_ban') ) {
                    bb2_ban($_SERVER['REAL_ADDR'],4);
                }
                $retval = true;
            }
        }
        return $retval;
    }
}