<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | import.php                                                               |
// |                                                                          |
// | Import menu                                                              |
// +--------------------------------------------------------------------------+
// | $Id:: import.php 4573 2009-06-21 05:12:30Z mevans0263                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
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

require_once '../../../lib-common.php';
require_once 'gf_functions.php';

if (!SEC_inGroup('Root')) {
    echo COM_siteHeader();
    echo COM_startBlock("Error");
    echo 'You do not have permission to perform this operation';
    echo COM_endBlock();
    echo COM_siteFooter();
    exit();
}

echo COM_siteHeader();
echo COM_startBlock($LANG_GF06[9]);

echo glfNavbar($navbarMenu,$LANG_GF06[9]);

echo '<ul style="list-style-type:disc;list-style-position:inside;margin-left:15px;padding-right:2px;">';
echo '<li><a href="'.$_CONF['site_admin_url'].'/plugins/forum/phpbb3_migrate.php">phpBB3 Import</a></li>';
echo '</ul>';

echo COM_endBlock();
echo adminfooter();

echo COM_siteFooter();
?>