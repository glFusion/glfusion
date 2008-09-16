<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-htmlhead.php                                                         |
// |                                                                          |
// | Functions to add entries to the <HEAD> section of a page.                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Joe Mucchiello         jmucchiello AT yahoo DOT com                      |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+
//

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

define('HTMLHEAD_PRIO_VERYHIGH', 1);
define('HTMLHEAD_PRIO_HIGH', 2);
define('HTMLHEAD_PRIO_NORMAL', 3);
define('HTMLHEAD_PRIO_LOW', 4);
define('HTMLHEAD_PRIO_VERYLOW', 5);

// DO NOT MODIFY THIS ARRAY DIRECTLY!!!!
$HTMLHEAD = Array(
    'title' => '',
    'meta' => Array('http-equiv' => Array(), 'name' => Array()),
    'favicon' => '',
    HTMLHEAD_PRIO_VERYHIGH => Array('style' => Array(), 'script' => Array(), 'raw' => Array() ),
    HTMLHEAD_PRIO_HIGH => Array('style' => Array(), 'script' => Array(), 'raw' => Array() ),
    HTMLHEAD_PRIO_NORMAL => Array('style' => Array(), 'script' => Array(), 'raw' => Array() ),
    HTMLHEAD_PRIO_LOW => Array('style' => Array(), 'script' => Array(), 'raw' => Array() ),
    HTMLHEAD_PRIO_VERYLOW => Array('style' => Array(), 'script' => Array(), 'raw' => Array() ),
);

function HTMLHEAD_style($code, $priority = HTMLHEAD_PRIO_NORMAL, $mime = 'text/css', $cache = false)
{
    global $HTMLHEAD, $_CONF;

    $HTMLHEAD[$priority]['style'][] = '<style type='' . $mime . '\'>\n' . $code . '\n</style>\n';
}

function HTMLHEAD_script($code, $priority = HTMLHEAD_PRIO_NORMAL, $mime = 'text/javascript', $cache = false)
{
    global $HTMLHEAD, $_CONF;

    $HTMLHEAD[$priority]['script'][] = '<script type=\'' . $mime . '\'>\n<!--\n' . $code . '\n// --></script>\n';
}

function HTMLHEAD_link($rel, $href, $type = '', $priority = HTMLHEAD_PRIO_NORMAL, $attrs = array())
{
    global $HTMLHEAD;

    $link = '<link rel='' . $rel .'' href='' . htmlspecialchars($href) . ''';
    if (!empty($type)) {
        $link .= ' type='' . $type . ''';
    }
    if (is_array($attrs)) {
        foreach ($attrs as $k => $v) {
            $link .= ' ' . $k . '='' . $v . ''';
        }
    }
    $HTMLHEAD[$priority][] = $link;
}

function HTMLHEAD_link_style($href, $priority = HTMLHEAD_PRIO_NORMAL, $mime = 'text/css', $attrs = array(), $cache = true)
{
    global $HTMLHEAD;

    $link = '<link rel='stylesheet' type='' . $mime . '' href='' . htmlspecialchars($href) . ''';
    if (is_array($attrs)) {
        foreach ($attrs as $k => $v) {
            $link .= ' ' . $k . '='' . $v . ''';
        }
    }
    $link .= XHTML . '>\n';

    $HTMLHEAD[$priority]['style'][] = $link;
}

function HTMLHEAD_link_script($href, $priority = HTMLHEAD_PRIO_NORMAL, $mime = 'text/javascript', $cache = true)
{
    global $HTMLHEAD, $_CONF;

    $link = '<link rel='stylesheet' type='' . $mime . '' href='' . htmlspecialchars($href) . ''';
    if (!empty($title)) {
        $link .= ' title='' . htmlspecialchars($title) . ''';
    }
    $link .= XHTML . '>\n';

    $HTMLHEAD[$priority]['script'][] = $link;
}

function HTMLHEAD_raw($code, $priority = HTMLHEAD_PRIO_NORMAL)
{
    global $HTMLHEAD, $_CONF;

    $HTMLHEAD[$priority]['raw'][] = $code . '\n';
}

function HTMLHEAD_title($title)
{
    global $HTMLHEAD;
    $HTMLHEAD['title'] = '<title>' . $title . '</title>\n';
}

function HTMLHEAD_favicon($href)
{
    global $HTMLHEAD;
    $HTMLHEAD['favicon'] = '<link rel='SHORTCUT ICON' href='' . htmlspecialchars($href) . ''' . XHTML .'>\n';
}

function HTMLHEAD_meta_name($name, $content)
{
    global $HTMLHEAD;
    $HTMLHEAD['meta']['name'] = '<meta name='' .  $name . '' content='' . $content . ''' . XHTML .'>\n';
}

function HTMLHEAD_meta_http_equiv($header, $content)
{
    global $HTMLHEAD;
    $HTMLHEAD['meta']['http-equiv'] = '<meta http-equiv='' . $header .'' content='' . $content . ''' . XHTML .'>\n';
}



function HTMLHEAD_render()
{
    global $HTMLHEAD;

    return '\n' . array_concat_recursive($HTMLHEAD);
}

function array_concat_recursive($a)
{
    if (is_array($a)) {
        $cat = '';
        foreach ($a as $aa) {
            if (is_array($aa)) {
                $cat .= array_concat_recursive($aa);
            } else {
                $cat .= $aa;
            }
        }
        return $cat;
    } else {
        return false;
    }
}


?>
