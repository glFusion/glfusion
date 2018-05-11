<?php
/**
* File: Akismet.class.php
* Akismet Examine Class
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
require_once $_CONF['path'] . 'plugins/spamx/modules/Akismet.class.php';

/**
 * Sends posts to Akismet for examination
 *
 * @package Spam-X
 */
class Akismet extends BaseCommand
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

        $retval = false;

        if (!isset($_SPX_CONF['akismet_enabled'], $_SPX_CONF['akismet_api_key'])) {
            $_SPX_CONF['akismet_enabled'] = false;
        }

        if (!$_SPX_CONF['akismet_enabled']) {
            return $retval;
        }
        $akismet = new AkismetBase($_CONF['site_url'], $_SPX_CONF['akismet_api_key']);
        if ($akismet->isKeyValid()) {
            if (isset($data['username']) ) {
                $akismet->setCommentAuthor($data['username']);
            }
            if ( isset($data['email']) ) {
                $akismet->setCommentAuthorEmail($data['email']);
            }
            if ( isset($data['ip'])) {
                $akismet->setUserIP($data['ip']);
            } else {
                $akismet->setUserIP($_SERVER['REAL_ADDR']);
            }
            if ( isset($data['type']) ) {
                $akismet->setCommentType($data['type']);
            } else {
                $akismet->setCommentType('comment');
            }
            $akismet->setCommentContent($comment);

            $retval = $akismet->isCommentSpam();

            if ($retval == true) {
                $spamType = isset($data['type']) ? $data['type'] : ' unknown ';
                SPAMX_log ("Akismet: spam detected on " . $spamType);
                SPAMX_log ($LANG_SX00['foundspam'] . 'Akismet'.
                           $LANG_SX00['foundspam2'] . $_USER['uid'] .
                           $LANG_SX00['foundspam3'] . $_SERVER['REAL_ADDR']);
                if ( function_exists('bb2_ban') ) {
                    bb2_ban($_SERVER['REAL_ADDR'],4);
                }

             }
        }

        return $retval;
    }
}