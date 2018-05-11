<?php

/**
* File: IPofUrl.Examine.class.php
* This is the Personal BlackList Examine class for the glFusion Spam-X plugin
*
* Copyright (C) 2004-2006 by the following authors:
* Author        Tom Willett        tomw AT pigstye DOT net
*
* Licensed under GNU General Public License
*
* @package Spam-X
* @subpackage Modules
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
* Include Abstract Examine Class
*/
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'BaseCommand.class.php';

/**
* Examines Comment according to Personal BLacklist
*
* @author Tom Willett tomw AT pigstye DOT net
*
* @package Spam-X
*
*/

class IPofUrl extends BaseCommand {
    /**
     * No Constructor Use BaseCommand constructor
     */
    /**
     * Here we do the work
     */
    function execute($comment,$data)
    {
        global $_CONF, $_TABLES, $_USER, $LANG_SX00, $result;

        if (isset ($_USER['uid']) && ($_USER['uid'] > 1)) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }

        /**
         * Check for IP of url in blacklist
         */
        /*
        * regex to find urls $1 = fqd
        */
        $num = preg_match_all("/<a\s+(?:[^>]*?\s+)?href=\"([^\"]*)\"/", $comment, $urls);

        $result = DB_query ("SELECT value FROM {$_TABLES['spamx']} WHERE name='IPofUrl'", 1);
        $nrows = DB_numRows ($result);

        $ans = 0;
        for ($j = 1; $j <= $nrows; $j++) {
            list ($val) = DB_fetchArray ($result);
            for ($i = 0; $i < $num; $i++) {
                $url = $urls[1][$i];
                if ($url === "" || strpos($url, $_CONF['site_url']) !== false) {
                    continue;
                }
                $parsedURL = parse_url($url);
                $ip = gethostbyname ($parsedURL['host']);
                if ($val == $ip) {
                    $ans = 1; // quit on first positive match
                    SPAMX_log ($LANG_SX00['foundspam'] . $urls[2][$i] .
                               $LANG_SX00['foundspam2'] . $uid .
                               $LANG_SX00['foundspam3'] . $_SERVER['REAL_ADDR']);
                    break;
                }
            }
            if ($ans == 1) {
                break;
            }
        }
        return $ans;
    }
}

?>
