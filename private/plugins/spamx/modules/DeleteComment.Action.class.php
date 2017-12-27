<?php

/**
 * File: DeleteComment.Action.class.php
 * This is the Delete Comment Action  for the glFusion Spam-X plugin
 *
 * Copyright (C) 2004-2005 by the following authors:
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
 * Include Abstract Action Class
 */
require_once $_CONF['path'] . 'plugins/spamx/modules/' . 'BaseCommand.class.php';

/**
 * Action Class which just discards comment
 *
 * @author Tom Willett  tomw@pigstye.net
 * @package Spam-X
 *
 */
class DeleteComment extends BaseCommand {
    /**
     * Constructor
     * Numbers are always binary digits and added together to make call
     */
    function __construct()
    {
        global $num;

        $num = 128;
    }

    function execute($comment,$data)
    {
        global $result, $_CONF, $_TABLES, $LANG_SX00;
        $result = 128;

        // update count of deleted spam posts
        DB_change ($_TABLES['vars'], 'value', 'value + 1', 'name', 'spamx.counter', '', true);

        SPAMX_log($LANG_SX00['spamdeleted']);

        return 1;
    }
}

?>
