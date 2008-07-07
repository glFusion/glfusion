<?php
// +---------------------------------------------------------------------------+
// | Theme Functions                                               |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2007 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Mark R. Evans              - mevans@ecsnet.com                            |
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
// | DOCUMENTATION                                                             |
// |                                                                           |
// | This custom functions.php will allow you to build Geeklog content in any  |
// | order.  By default, the functions.php is designed to build centercontent  |
// | first, then left, then right.                                             |
// |                                                                           |
// | USAGE                                                                     |
// |                                                                           |
// | You MUST rename the nouveau_siteHeader() and nouveau_siteFooter()     |
// | functions for your theme, by using your themename_siteHeader()/_siteFooter|
// |                                                                           |
// | This file also requires a new template file, htmlheader.thtml which is    |
// | included in the sample theme.  This new template sends just the HTML      |
// | header to the browser, it does not send the site content.                 |
// |                                                                           |
// | This functions sets a new template variable for the header.thtml called   |
// | {centercolumn}, this holds the proper class name depending on what content|
// | is going to be displayed.  For example, with left, center, right content  |
// | {centercolumn} is set to content.                                         |
// |                                                                           |
// | What's Displayed               CSS Class                                  |
// | -------------------            ---------------------                      |
// | left, center, right            content                                    |
// | left, center                   content-wide-left                          |
// | center, right                  content-wide-right                         |
// | center                         content-full                               |
// |                                                                           |
// +---------------------------------------------------------------------------+

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'functions.php') !== false) {
    die ('This file can not be used on its own!');
}

$_IMAGE_TYPE = 'png';

if (!defined ('XHTML')) {
    define('XHTML',' /'); // change this to ' /' for XHTML, and '' for HTML. Don't forget to update your doctype in htmlheader.thtml.
}
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

$_BLOCK_TEMPLATE['whats_related_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
$_BLOCK_TEMPLATE['story_options_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
// Define the blocks that are a list of links styled as an unordered list - using class="blocklist"
$_BLOCK_TEMPLATE['admin_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
$_BLOCK_TEMPLATE['section_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
// $_BLOCK_TEMPLATE['user_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';



// A portal staticpage (that bases itself on a user created proper portal block called gl_mootickerRSS)
//modified from LWC's forum post http://www.geeklog.net/forum/viewtopic.php?showtopic=67396 by Mark R. Evans and Joe Mucchiello
function gl_mootickerRSS() {
	global $_CONF, $_TABLES;
	$retval = '';
	$result = DB_query("SELECT *, rdfupdated as date FROM
{$_TABLES['blocks']} WHERE name='gl_mootickerRSS'");
	$numRows = DB_numRows($result);
	if ( $numRows < 1 || $result == NULL ) {
		return $retval;
	}
	$B = DB_fetchArray($result);
	if ( $B['is_enabled'] == 0 ) {
		$retval = <<<EOT
<script type="text/javascript"
src="{layout_url}/js/gl_mooticker.js"></script>
<script type="text/javascript">
         window.addEvent('domready', function() {
                 var x = new MooTicker('gl_mooticker', {
                         controls: true,
                         delay: 2500,
                         duration: 600 });
         });
</script>
<div id="gl_mooticker">
   <span class="tickertitle">Latest News:</span>
EOT;
		$retval = str_replace('{layout_url}',$_CONF['layout_url'] , $retval);
		$retval .= $B['content'] . '</div>';
	}
	return $retval;
}

?>