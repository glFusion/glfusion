<?php
// +--------------------------------------------------------------------------+
// | Spam-X Plugin - glFusion CMS                                             |
// +--------------------------------------------------------------------------+
// | upgrade.php                                                              |
// |                                                                          |
// | Upgrade routines                                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors      Tom Willett     tomw AT pigstye DOT net                     |
// |              Dirk Haun       dirk AT haun-online DOT de                  |
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
    die ('This file can not be used on its own.');
}

function spamx_upgrade()
{
    global $_TABLES, $_CONF, $_SPX_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='spamx'");

    switch( $currentVersion ) {
        default :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_SPX_CONF['pi_version']."',pi_gl_version='".$_SPX_CONF['gl_version']."' WHERE pi_name='spamx' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='spamx'") == $_SPX_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}
?>