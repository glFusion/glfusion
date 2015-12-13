<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | user.class.php                                                           |
// |                                                                          |
// | user PHP autotag functions                                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2015 by the following authors:                        |
// |                                                                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
// +--------------------------------------------------------------------------+
// | Based upon the fine work of:                                             |
// |                                                                          |
// | Joe Mucchiello         joe AT throwingdice DOT com                       |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

class autotag_user extends BaseAutotag {

    function __construct()
    {
        global $_AUTOTAGS;

        $this->description = $_AUTOTAGS['user']['description'];
    }

    function parse($p1, $p2, $fulltag)
    {
        if (empty($p1)) {
            return '';
        }

        $user = "_USER";
        if (isset($GLOBALS[$user][$p1])) {
            return $GLOBALS[$user][$p1];
        }
        return '{$_USER['.$p1.']}';
    }
}