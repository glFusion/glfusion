<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | finnish_utf-8.php                                                        |
// |                                                                          |
// | finnish language file                                                    |
// +--------------------------------------------------------------------------+
// | $Id:: english_utf-8.php 4753 2009-08-05 14:39:39Z mevans0263            $|
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2009 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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

$LANG_BAD_BEHAVIOR = array (
    'plugin_display_name'   => 'Bad Behavior2',
    'page_title'            => 'Bad Behavior2',
    'block_title_admin'     => 'Bad Behavior2 Ylläpito',
    'block_title_list'      => 'Bad Behavior2 Osuma Logo',
    'block_title_entry'     => 'Osuma Tiedot',
    'block_title_donate'    => 'Lahjoita',
    'list_entries'          => 'Näytä Osuma Logi (%d)',
    'list_no_entries'       => 'No log entries.',
    'row_ip'                => 'IP Osoite',
    'row_user_agent'        => 'User Agent',
    'row_referer'           => 'Referrer',
    'row_response'          => 'Vastaus',
    'row_method'            => 'Metodi',
    'row_protocol'          => 'Protokolli',
    'row_date'              => 'Päivä',
    'row_reason'            => 'Syy',
    'self_test'             => 'Testaa Bad Behavior2',
    'link_back'             => 'Takaisin Logiin',
    'title_show_headers'    => 'Näytä HTTP headers',
    'title_lookup_ip'       => 'Lookup IP address',
    'error'                 => 'Virhe',
    'fsockopen_not_available' => 'Sorry, the PHP function <code>fsockopen</code> is not available. Can not perform self test.',
    'fsockopen_failed'      => 'Failed to open socket. Could not perform self test.',
    'donate_msg'            => 'If you find this plugin useful, please consider making a donation to Michael Hampton, the original author of Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Visit the Bad Behavior homepage</a>.',
    'denied_reason'         => 'Syy',
    'results'               => 'Bad Behavior2 Results',
    'search'                => 'Etsi',
    'stats_headline'        => 'Bad Behavior2 Tilastot',
    'stats_reason'          => 'Syy',
    'stats_blocked'         => 'Blokattu',
    'stats_no_hits'         => 'No entries.',
    'blocked_ips'           => 'Blocked unique IP addresses',
    'unblock'               => 'Unblock IP address'
);

$LANG_BB2_RESPONSE = array (
	'00000000' => 'Request Passed - No User Agent Specified',
	'136673cd' => 'IP address found on external blacklist',
	'17566707' => 'Required header \'Accept\' missing',
	'17f4e8c8' => 'User-Agent was found on blacklist',
	'21f11d3f' => 'User-Agent claimed to be AvantGo, claim appears false',
	'2b021b1f' => 'IP address found on http:BL blacklist',
	'2b90f772' => 'Connection: TE present, not supported by MSIE',
    '35ea7ffa' => 'Invalid language specified',
	'408d7e72' => 'POST comes too quickly after GET',
	'41feed15' => 'Header \'Pragma\' without \'Cache-Control\' prohibited for HTTP/1.1 requests',
	'45b35e30' => 'Header \'Referer\' is corrupt',
	'57796684' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
	'582ec5e4' => '"Header \'TE\' present but TE not specified in \'Connection\' header',
	'69920ee5' => 'Header \'Referer\' present but blank',
    '6c502ff1' => 'Bot not fully compliant with RFC 2965',
	'799165c2' => 'Rotating user-agents detected',
	'7a06532b' => 'Required header \'Accept-Encoding\' missing',
	'7ad04a8a' => 'Prohibited header \'Range\' present',
	'7d12528e' => 'Prohibited header \'Range\' or \'Content-Range\' in POST request',
	'939a6fbb' => 'Banned proxy server in use',
	'9c9e4979' => 'Prohibited header \'via\' present',
	'a0105122' => 'Header \'Expect\' prohibited; resend without Expect',
	'a1084bad' => 'User-Agent claimed to be MSIE, with invalid Windows version',
	'a52f0448' => 'Header \'Connection\' contains invalid values',
	'b40c8ddc' => 'POST more than two days after GET',
	'b7830251' => 'Prohibited header \'Proxy-Connection\' present',
	'b9cc1d86' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
	'c1fa729b' => 'Use of rotating proxy servers detected',
    'cd361abb' => 'Referer did not point to a form on this site',
	'd60b87c7' => 'Trackback received via proxy server',
    'e3990b47' => 'Obviously fake trackback received',
	'dfd9b1ad' => 'Request contained a malicious JavaScript or SQL injection attack',
	'e4de0453' => 'User-Agent claimed to be msnbot, claim appears to be false',
	'e87553e1' => 'I know you and I don\'t like you, dirty spammer.',
	'f0dcb3fd' => 'Web browser attempted to send a trackback',
	'f1182195' => 'User-Agent claimed to be Googlebot, claim appears to be false.',
	'f9f2b8b9' => 'A User-Agent is required but none was provided.'
);


$PLG_bad_behavior_MESSAGE1  = 'If you see this message, then Bad Behavior2 is <b>not</b> installed correctly! Please read the installation instructions again carefully.';
$PLG_bad_behavior_MESSAGE100 = 'IP address has been unblocked.';
$PLG_bad_behavior_MESSAGE101 = 'There was a problem unblocking the IP address.';
?>