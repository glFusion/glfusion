<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | errorhandler.php                                                         |
// |                                                                          |
// | Displays error box and code                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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
namespace Filemgmt;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class ErrorHandler
{
    public static function show($e_code, $pages=1)
    {
        global $_CONF, $LANG_FILEMGMT_ERRORS;

        // determine the destination of this request
        $destination = COM_getCurrentURL();

        // validate the destination is not blank and is part of our site...
        if ( $destination == '' ) {
            $destination = $_CONF['site_url'] . '/filemgmt/index.php';
        }
        if ( substr($destination, 0,strlen($_CONF['site_url'])) != $_CONF['site_url']) {
            $destination = $_CONF['site_url'] . '/filemgmt/index.php';
        }

        if (!array_key_exists($e_code, $LANG_FILEMGMT_ERRORS)) {
            $e_code = '9999';
        }
        $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file('message', 'errmsg.thtml');
        $T->set_var(array(
            'e_code' => $e_code,
            'e_message' => $LANG_FILEMGMT_ERRORS[$e_code],
            'url' => $destination,
        ) );
        $T->parse('output', 'message');

        $display  = Menu::siteHeader('menu');
        $display .= $T->finish($T->get_var('output'));
        $display .= Menu::siteFooter();
        echo $display;
        die("");
    }
}
