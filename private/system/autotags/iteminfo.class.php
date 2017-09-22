<?php
/**
* glFusion CMS
*
* itemInfo Auto tag
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2016-2017 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class autotag_iteminfo extends BaseAutotag {

    function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['iteminfo']['description'];
    }

    function parse($p1, $p2, $fulltag)
    {
        global $_CONF, $_TABLES, $_USER, $LANG01;

        $retval = '';
        $skip = 0;

        $type = $p1;

        $what       = '';       // what to look for
        $id         = '';       // id of item

        $px = explode (' ', trim ($p2));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 3) == 'id:') {
                    $a = explode (':', $part);
                    $id = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 5) == 'what:') {
                    $a = explode (':', $part);
                    $what = $a[1];
                    $skip++;
                } else {
                    break;
                }
            }
        }

        $idwhat = 'id,' . $what;
        $itemInfo = PLG_getItemInfo($type, $id, $idwhat);
        if ( isset($itemInfo[$what]) ) {
            $retval = $itemInfo[$what];
        }
        return $retval;
    }
}
?>