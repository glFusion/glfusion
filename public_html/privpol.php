<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | privpol.php                                                              |
// |                                                                          |
// | glFusion Privacy Policy Page                                             |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Eric Warren            eric AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tom Willett        - twillett@users.sourceforge.net             |
// |          John Hughes       - jlhughes@users.sf.net                       |
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

require_once('lib-common.php');

$display = COM_siteHeader();
$display .= "<div><h1><b>Privacy Policy</b></h1><br />";
$display .= $_CONF['site_name'] . ' has created this privacy statement in order to demonstrate our commitment to privacy. The following discloses the information gathering and dissemination practices for this Web site.<br /><br />';
$display .= '<b>Information is Automatically Logged</b><br />This site uses a visitor statistics package that logs the following information on each page access: userid, user_agent, IP, host, browser type, computing platform, date and time, page viewed, referer, request type, and query_string.  We use this information to help diagnose problems with our server and to administer our web site, and to help identify you. This information is also gathered because we like to see who is visiting. <b>This information is not and will never be divulged to a third party.</b><br /><br />';
$display .= '<b>Cookies </b><br />This site uses cookies to deliver content specific to your interests, to save your password so you don\'t have to re-enter it each time you visit our site, and for other purposes.<br /><br />';
$display .= '<b>Registration Forms</b><br />Our site\'s registration form requires users to give us contact information (i.e. email address). Contact information from the registration forms is used to validate the user\'s account. This enables the site administrators to provide moderation for the various public features of this site, and to get in touch with the user when necessary. <b>This information is not and will never be divulged to a third party.</b> We use this data to tailor our visitor\'s experience at our site showing them content that we think they might be interested in, and displaying the content according to their preferences.<br /><br /> ';
$display .= '<b>External Links </b><br />This site contains links to other sites. '. $_CONF['site_name'] . ' is not responsible for the privacy practices or the content of such Web sites.<br /><br /> ';
$display .= '<b>Public Forums </b><br />This site makes message boards available to its users. Please remember that any information that is disclosed in these areas becomes public information and you should exercise caution when deciding to disclose your personal information. While we do our best to ensure only appropriate information is posted, we cannot and do not assume responsibility for our user\'s postings. <b>All postings are property of their respective author.</b> Improper language is automatically censored by the system.  All submissions are subject to our editorial control. Where appropriate, editorial changes will be indicated.<br /><br />';
$display .= '<b>Security </b><br />This site has security measures in place to protect the loss, misuse, and alteration of the information under our control.<br /><br /> ';
$display .= '<b>Data Quality/Access </b><br />This site gives users control over their user experience and content that they may have provided.  Users can freely modify or delete any content they post.  Users have the ability to customize various aspects of the look and feel of the site.<br /><br />';
$display .= '<b>Limitation of Liability</b><br />'. $_CONF['site_name'] . ' is not liable for any damages caused by any of the site content, whether directly provided by ' . $_CONF['site_name'] . ' or its employees or not.<br /><br />';
$display .= '<b>Contacting the Web Site </b><br />If you have any questions about this privacy statement, the practices of this site, or your dealings with this Web site, you can contact the <a href="' . $_CONF['site_url'] . '/profiles.php?uid=2">webmaster</a>.<br /><br /></div>';
$display .= COM_siteFooter(true);
echo $display;
?>