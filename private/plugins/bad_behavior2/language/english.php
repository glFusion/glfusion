<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | english.php                                                              |
// |                                                                          |
// | English language file                                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2008 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

$LANG_BAD_BEHAVIOR = array (
    'plugin_display_name'   => 'Bad Behavior2',
    'page_title'            => 'Bad Behavior2',
    'block_title_admin'     => 'Bad Behavior2 Administration',
    'block_title_list'      => 'Log entries',
    'block_title_entry'     => 'Entry details',
    'block_title_donate'    => 'Donate',
    'list_entries'          => 'Show log entries (%d)',
    'list_no_entries'       => 'No log entries.',
    'row_ip'                => 'IP Address',
    'row_user_agent'        => 'User Agent',
    'row_referer'           => 'Referrer',
    'row_response'          => 'Response',
    'row_method'            => 'Method',
    'row_protocol'          => 'Protocol',
    'row_date'              => 'Date',
    'row_reason'            => 'Reason',
    'self_test'             => 'Test Bad Behavior2',
    'link_back'             => 'Back to list of log entries',
    'title_show_headers'    => 'Show HTTP headers',
    'title_lookup_ip'       => 'Lookup IP address',
    'error'                 => 'Error',
    'fsockopen_not_available' => 'Sorry, the PHP function <code>fsockopen</code> is not available. Can not perform self test.',
    'fsockopen_failed'      => 'Failed to open socket. Could not perform self test.',
    'donate_msg'            => 'If you find this plugin useful, please consider making a donation to Michael Hampton, the original author of Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Visit the Bad Behavior homepage</a>.',
    'denied_reason'         => 'Reason',
    'results'               => 'Bad Behavior2 Results',
    'search'                => 'Search',
    'stats_headline'        => 'Bad Behavior2 Statistics',
    'stats_reason'          => 'Reason',
    'stats_blocked'         => 'Blocked',
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
	'2b90f772' => 'Connection: TE present, not supported by MSIE',
	'408d7e72' => 'POST comes too quickly after GET',
	'41feed15' => 'Header \'Pragma\' without \'Cache-Control\' prohibited for HTTP/1.1 requests',
	'45b35e30' => 'Header \'Referer\' is corrupt',
	'57796684' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
	'582ec5e4' => '"Header \'TE\' present but TE not specified in \'Connection\' header',
	'69920ee5' => 'Header \'Referer\' present but blank',
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
	'd60b87c7' => 'Trackback received via proxy server',
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