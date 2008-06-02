<?php
// +---------------------------------------------------------------------------+
// | glFusion Enhancement Library                                              |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Mark R. Evans              - mark AT gllabs DOT org                       |
// +---------------------------------------------------------------------------+
// | LICENSE                                                                   |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'lib-glfusion.php') !== false) {
    die ('This file can not be used on its own!');
}

function phpblock_blogroll ()
{
    global $_CONF, $_TABLES;

    // configuration options:

    $cat = 'blog-roll';     // Category to take links from
    $directlink = false;        // Use direct links (true) or portal.php (false)
    $random = false;            // Random order (true) or sort by $sort (false)
    $sort = 'date';             // Sort by ... e.g. 'date', 'title', 'url'

    // === you shouldn't need to change anything below this line ==============
    $retval = '';

    if ( function_exists('LINKS_countLinksAndClicks') ) {

        $result = DB_query ("SELECT lid,url,title,description,hits FROM {$_TABLES['links']} WHERE cid = '$cat'" . COM_getPermSql ('AND') . " ORDER BY $sort");
        $numLinks = DB_numRows ($result);

        $links = array ();
        for ($i = 0; $i < $numLinks; $i++) {
            $A = DB_fetchArray ($result);

            if ($directlink) {
                $url = $A['url'];
                $link = '<a href="' . $url . '">';

            } else {
                $url = $_CONF['site_url']
                     . COM_buildUrl ('/links/portal.php?what=link&amp;item=' . $A['lid']);
                $link = '<a href="' . $url . '" title="' . $A['url'] . '">';

            }
            $links[] = $link . $A['title'] . '</a>'; // . ' (' . ($A['hits']) . ')' . '<br' . XHTML . '><em>' . ($A['description']) . '</em><br' . XHTML . '><br' . XHTML . '>';
        }

        if (count ($links) > 0) {

            if ($random) {
                $min = 0;
                $max = count ($links) - 1;

                $newlist = array ();
                do {
                    $r = rand ($min, $max);

                    if (!empty ($links[$r])) {
                        $newlist[] = $links[$r];
                        unset ($links[$r]);
                    }

                    if ($r == $min) {
                        $min = $r + 1;
                    } else if ($r == $max) {
                        $max = $r - 1;
                    }
                    if ($min == $max) {
                        if (!empty ($links[$min])) {
                            $newlist[] = $links[$min];
                        }
                        break;
                    }
                }
                while ($max > $min);

                $retval = COM_makeList ($newlist, 'list-blogroll');
            } else {
                $retval = COM_makeList ($links, 'list-blogroll');
            }
        }
    }

    return $retval;
}
?>