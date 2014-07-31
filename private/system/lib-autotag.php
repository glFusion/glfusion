<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-autotag.php                                                          |
// |                                                                          |
// | glFusion autotag library.                                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2014 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark Howard            mark AT usable-web DOT com                        |
// |                                                                          |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

//@TODO Move this to online configuration

$_AM_CONF['allow_php'] = 1;
$_AM_CONF['show_in_menu'] = 1;
$_AM_CONF['disallow'] = array();

$_AUTOTAGS = AT_loadTags();

function AT_description($tag='', $module='')
{
    global $_AUTOTAGS, $LANG_AM;

    $retval = '';
    if(!empty($tag)) {
        if (isset($_AUTOTAGS[$tag])) {
            $retval = $_AUTOTAGS[$tag]['description'];
        } else {
            if (!empty($module)) {
                // look for a plugin API function in $module that describes $tag
                $fn = 'plugin_autotags_' . $module;
                if (function_exists($fn)) {
                    $retval = $fn('desc',$tag);
                }
                // allow for tag descriptions to be defined in the AutoTag Manager language file
                if (empty($retval)) {
                    $desc = 'desc_' . $tag;
                    $retval = isset($LANG_AM[$desc]) ? $LANG_AM[$desc] : '';
                }
                // let's make sure we have something in there anyway
                if (empty($retval)) {
                    $retval = ($module == 'glfusion') ? 'glFusion Core AutoTag' : "Part of the $module plugin";
                }
            }
        }
    }
    return $retval;
}

// returns an array of tag information for core, plugin and user-defined tags

function AT_collectTags()
{
    global $_AM_CONF, $_AUTOTAGS;

    $A = array();
    $P = PLG_collectTags();
    foreach($P as $tag => $module) {
        if (isset($_AUTOTAGS[$tag])) {
            if ($_AUTOTAGS[$tag]['is_function'] == 1) {
                $isfunction = true;
                $type = 'F';
            } else {
                $isfunction = false;
                $type = 'U';
            }
        } else {
            $type = ($module == 'glfusion') ? 'C' : 'P';
            $isfunction = true;
            $isenabled = true;
        }
        $A[] = array(
            'tag'           => $tag,
            'module'        => $module,
            'type'          => $type,
            'description'   => AT_description($tag, $module),
            'is_function'   => $isfunction,
            'is_enabled'    => true,
        );
    }
    return $A;
}


function AT_loadTags() {
    global $_TABLES, $_AM_CONF;

    $A = array();
    $sql = "SELECT * FROM {$_TABLES['autotags']} WHERE is_enabled = 1";
    $result = DB_query($sql,1);
    $rows = DB_numrows($result);
    $allow_php = ($_AM_CONF['allow_php'] == 1) ? true : false;
    for ($i = 0; $i < $rows; ++$i) {
        $R = DB_fetchArray($result);
        $isfunction = ($R['is_function'] == 1) ? true : false;
        if (!$isfunction OR ($isfunction AND $allow_php)) {
            $A[$R['tag']] = $R;
        }
    }
    return $A;
}

// this is the secret sauce

function autotags_autotag( $op, $content = '', $autotag = '' )
{
    global $_CONF, $_AM_CONF, $_TABLES, $_AUTOTAGS;

    if ($op == 'tagname' ) {
        return array_keys($_AUTOTAGS);
    } else if ($op == 'parse') {
        require_once $_CONF['path_system'].'autotags/base.class.php';
        $p1 = $autotag['parm1'];
        $p2 = $autotag['parm2'];
        if (empty($p2)) $p2 = $p1;
        if (isset($_AUTOTAGS[$autotag['tag']])) {
            $record = $_AUTOTAGS[$autotag['tag']];
            if ($record['is_function'] == 1) {
                if ($_AM_CONF['allow_php'] == 1) {
                    $tagClass = 'autotag_'.$autotag['tag'];
                    $filename = $autotag['tag'] . '.class.php';
                    if ( @file_exists($_CONF['path_system'].'autotags/' . $filename)) {
                        require_once $_CONF['path_system'].'autotags/' . $filename;
                        $parser = new $tagClass;
                        $replace = $parser->parse ($p1,$p2,$autotag['tagstr']);
                        $content = str_replace($autotag['tagstr'], $replace, $content);
                    }
                }
            } else {
                $p0 = substr($autotag['tagstr'], strlen($autotag['tag']) + 2);
                $p0 = substr($p0, 0, strlen($p0)-1);
                $replace = array('%1%', '%2%', '%0%','%site_url%','%site_admin_url%');
                $with = array($p1, $p2, $p0, $_CONF['site_url'], $_CONF['site_admin_url'] );

                $subject = $record['replacement'];
                $subject = str_replace($replace, $with, $subject);
                $content = str_replace($autotag['tagstr'], $subject, $content);
            }
        }
        return $content;
    }
}
?>