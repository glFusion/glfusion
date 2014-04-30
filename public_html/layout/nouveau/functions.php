<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | functions.php                                                            |
// |                                                                          |
// | Support functions for Nouveau theme                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2014 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

//set glFusion style COM_siteHeader/Footer functions.
//Comment out to use old Geeklog functions.
$themeAPI = 2;

$_IMAGE_TYPE = 'png';

$lang = COM_getLanguageId();
if (empty($lang)) {
    $result = DB_query("SELECT onleft,name FROM {$_TABLES['blocks']} WHERE is_enabled = 1");
} else {
    $result = DB_query("SELECT onleft,name FROM {$_TABLES['blocks']}");
}
$nrows = DB_numRows($result);
for ($i = 0; $i < $nrows; $i++) {
    $A = DB_fetchArray($result);
        if ($A['onleft'] == 1) {
            $_BLOCK_TEMPLATE[$A['name']] = 'blockheader-left.thtml,blockfooter-left.thtml';
        } else {
            $_BLOCK_TEMPLATE[$A['name']] = 'blockheader-right.thtml,blockfooter-right.thtml';
    }
}
$_BLOCK_TEMPLATE['_msg_block'] = 'blockheader-message.thtml,blockfooter-message.thtml';
$_BLOCK_TEMPLATE['_persistent_msg_block'] = 'blockheader-message.thtml,blockfooter-persistent-message.thtml';
$_BLOCK_TEMPLATE['whats_related_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
$_BLOCK_TEMPLATE['story_options_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
// Define the blocks that are a list of links styled as an unordered list - using class="blocklist"
$_BLOCK_TEMPLATE['admin_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
$_BLOCK_TEMPLATE['section_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
$_BLOCK_TEMPLATE['user_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
$_BLOCK_TEMPLATE['forum_menu'] = 'blockheader-left.thtml,blockfooter-left.thtml';

// define the JS we need for this theme..
$outputHandle = outputHandler::getInstance();
$outputHandle->addScriptFile($_CONF['path_layout'].'js/mootools-release-1.11.packed.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/fValidator.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/gl_mooreflection.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/gl_moomenu.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/moorating.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/gltips.js');

function theme_getToolTipStyle()
{
    return('gl_mootip');
}
?>