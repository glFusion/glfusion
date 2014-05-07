<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | functions.php                                                            |
// |                                                                          |
// | Support functions for Newvou theme                                       |
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
// core js
$outputHandle->addScriptFile($_CONF['path_layout'].'js/jquery-1.11.0.min.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/jquery-ui-1.10.4.min.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/hoverIntent.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/jqrating.js');

// tooltips
$outputHandle->addScriptFile($_CONF['path_layout'].'js/jquery.tooltipster.min.js');
$outputHandle->addScriptFile($_CONF['path_layout'].'js/tooltip.js');
// menu animation
$outputHandle->addScriptFile($_CONF['path_layout'].'js/superfish.js');


$outputHandle->addLinkStyle($_CONF['layout_url'].'/css/tooltipster.css');

function theme_getToolTipStyle()
{
    return('tooltip');
}

// compatibility

if ( !function_exists('WIDGET_mooslide') ) {
    function WIDGET_mooslide($page_ids, $width = 550, $height = 160, $id = 'gl_', $slide_interval = 0) {}
}
if ( !function_exists('WIDGET_mootickerRSS') ) {
    function WIDGET_mootickerRSS($block = 'gl_mootickerRSS', $id = 'gl_mooticker') { }
}
if ( !function_exists('WIDGET_moospring') ) {
    function WIDGET_moospring() { }
}
if ( !function_exists('WIDGET_moorotator') ) {
    function WIDGET_moorotator() { }
}
if ( !function_exists('WIDGETS_moowrapper') ) {
    function WIDGET_wrapper() { }
}
?>