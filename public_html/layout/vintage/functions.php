<?php
/**
* @package  glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2010-2016 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

$themeAPI = 3;
$_SYSTEM['framework'] = 'legacy';

$_SYSTEM['disable_mootools'] = true;
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

// must load the jquery ui library we want to use.
$outputHandle->addScriptFile($_CONF['path_html'].'javascript/jquery/jquery-ui.min.js');
$outputHandle->addLinkStyle($_CONF['layout_url'].'/css/ui-lightness/jquery-ui.min.css');

// check to see if we have a custom.css file to load

if ( file_exists($_CONF['path_layout'] .'custom.css') ) {
    $outputHandle->addLinkStyle($_CONF['layout_url'] . '/custom.css');
}

// uncomment the line below to enable chronometer header rotator
// MAKE SURE TO CLEAR BROWSER & C.T.L. CACHE when activating/deactivating
// $outputHandle->addScriptFile($_CONF['path_layout'].'js/chronometer.js');

// Media Player
$outputHandle->addScriptFile($_CONF['path_html'].'javascript/addons/mediaplayer/mediaelement-and-player.min.js');
$outputHandle->addCSSFile($_CONF['path_html'] .'javascript/addons/mediaplayer/mediaelementplayer.css');

$outputHandle->addLinkStyle($_CONF['layout_url'] . '/font-awesome/css/font-awesome.css');


function theme_getToolTipStyle()
{
    return('tooltip');
}
?>