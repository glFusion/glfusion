<?php

/**
* File: SLCbase.class.php
* Spam Link Counter (SLC) Base Class
*
* Copyright (C) 2016 by the following authors:
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

/**
* Count the links in a post
*
* @author Mark R. Evans     mark AT glfusion DOT org
* based on the works of Tom Willet (Spam-X) and Lee Garner (SFS)
* @package Spam-X
*
*/
class SLCbase {

    var $_debug = false;

    var $_verbose = false;

    /**
    * Constructor
    */
    function __construct()
    {
        $this->_debug = false;
        $this->_verbose = false;
    }

    /**
    * Check number of links in post
    *
    * @param    string  $post   post to check for spam
    * @return   int             the number of links in the post (external to this site)
    *
    *
    */
    function CheckForSpam ($post)
    {
        global $_CONF, $_SPX_CONF, $REMOTE_ADDR;

        $links = 0;

        preg_match_all("/<a\s+(?:[^>]*?\s+)?href=\"([^\"]*)\"/", $post, $matches);
        for ($i = 0; $i < count ($matches[0]); $i++) {
            $url = $matches[1][$i];
            if ($url === "" || strpos($url, $_CONF['site_url']) !== false) {
                // do not process links from this site
                continue;
            } else {
                $links++;
            }
        }

        return $links;
    }
}

?>