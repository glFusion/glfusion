<?php

/**
 * File: BlackList.Examine.class.php
 * This is the Personal BlackList Examine class for the glFusion Spam-X plugin
 *
 * Copyright (C) 2004-2006 by the following authors:
 * Author   Tom Willett     tomw AT pigstye DOT net
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
 * @package Spam-X
 *
 */
class BlackList extends BaseCommand
{
    // Callback functions for preg_replace_callback()
    protected function callbackDecimal($str)
    {
        if (!is_array($str)) {
            return chr($str);
        }
        return $str;
    }

    protected function callbackHex($str)
    {
        if ( is_array($str)) $str = implode(' ',$str);
        return @chr('0x' . (string) $str);
    }

    /**
     * Here we do the work
     */
    public function execute($comment,$data)
    {
        global $_CONF, $_TABLES, $_USER, $LANG_SX00;

        if (isset ($_USER['uid']) && ($_USER['uid'] > 1)) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }

        /**
         * Include Blacklist Data
         */
        $result = DB_query("SELECT value FROM {$_TABLES['spamx']} WHERE name='Personal'", 1);
        $nrows = DB_numRows($result);

        // named entities
        $comment = html_entity_decode($comment);
        // decimal notation
        $comment = @preg_replace_callback('/&#(\d+);/m', array($this, 'callbackDecimal'), $comment);
        // hex notation
        $comment = @preg_replace_callback('/&#x([a-f0-9]+);/mi', array($this, 'callbackHex'), $comment);
         $ans = 0;
        for ($i = 1; $i <= $nrows; $i++) {
            list ($val) = DB_fetchArray ($result);

            $val = str_replace ('#', '\\#', $val);

            if (preg_match ("#$val#i", $comment)) {
                $ans = 1;  // quit on first positive match

                SPAMX_log ($LANG_SX00['foundspam'] . $val .
                           $LANG_SX00['foundspam2'] . $uid .
                           $LANG_SX00['foundspam3'] . $_SERVER['REMOTE_ADDR']);
                break;
            }
        }

        return $ans;
    }
}

?>
