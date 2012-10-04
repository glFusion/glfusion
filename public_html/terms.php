<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | terms.php                                                                |
// |                                                                          |
// | glFusion Terms and Conditions.                                           |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Eric Warren            eric AT glfusion DOT org                          |
// |                                                                          |
// | Based on the prior work by                                               |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Michael M. Wechsler, Esq. (michael@thelaw.com)                  |
// |                                                                          |
// | This was originaly part the legal module for POST-NUKE Content Management|
// | System (http://www.postnuke.com) to display terms use and a privacy      |
// | policy on your site.                                                     |
// | Incorperated into GeekLog by Jeffrey "mrjeff" Randall jeffjpr@yahoo.com  |
// |  -rewrote script, kept only the terms of use text and html formatting    |
// |  -named script terms.php                                                 |
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

require_once('lib-common.php'); // This path should point to your lib-common.php


$siteName   = $_CONF['site_name'];
$siteURL    = $_CONF['site_url'];
$layoutURL  = $_CONF['layout_url'];
$adminEmail = "<a href=\"$siteURL/profiles.php?uid=2\">here</a>";
$sitePrivacy="<a href=\"$siteURL/privpol.php\">Privacy Policy</a>";
$siteTerms  = "<a href=\"$siteURL/terms.php\">Terms of Use</a>";
$register   = "<a href=\"$siteURL/users.php?mode=new\">Register</a>";
$display  = '';
$display .= "<div><h1>Terms of Use</h1>";
$display .= "<br /><p><b>1. Acceptance of Terms of Use and Amendments</b><br />Each time you use or cause access to this web site, you agree to be bound by these $siteTerms, and as amended from time to time with or without notice to you. In addition, if you are using a particular service on or through this web site, you will be subject to any rules or guidelines applicable to those services and they shall be incorporated by reference into these $siteTerms. Please see our $sitePrivacy, which is incorporated into these $siteTerms by reference.</p><br />
<p><b>2. Our Service</b><br />Our web site and services provided to you on and through our web site on an &quot;AS IS&quot; basis. You agree that the owners of this web site exclusively reserve the right and may, at any time and without notice and any liability to you, modify or discontinue this web site and its services or delete the data you provide, whether temporarily or permanently. We shall have no responsibilty or liability for the timeliness, deletion, failure to store, inaccuracy, or improper delivery of any data or information.</p><br />
<p><b>3. Your Responsibilities and Registration Obligations</b><br />In order to use some of the functions of this web site, you must $register on our site, and agree to provide truthful information when requested. When applying for membership, you explicitly agree to our $siteTerms and as may be modified by us from time to time and available here.</p><br />
<p><b>4. Privacy Policy</b><br />Registration data and other personally identifiable information that we may collect is subject to the terms of our $sitePrivacy.</p><br />
<p><b>5. Registration and Password</b><br />You are responsible to maintain the confidentiality of your password and shall be responsible for all uses via your registration and/or login, whether authorized or unauthorized by you. You agree to immediately notify us of any unauthorized use or your registration, user account or password.</p> <br />
<p><b>6. Your Conduct</b><br />You agree that all information or data of any kind, whether text, software, code, music or sound, photographs or graphics, video or other materials (&quot;Content&quot;), publicly or privately provided, shall be the sole responsibility of the person providing the Content or the person whose user account is used. You agree that our web site may expose you to Content that may be objectionable or offensive. We shall not be responsible to you in any way for the Content that appears on this web site nor for any error or omission.</p> <p>You explicitly agree, in using this web site or any service provided, that you shall not:<br />(a) provide any Content or perform any conduct that may be unlawful, illegal, threatening, harmful, abusive, harassing, stalking, tortious, defamatory, libelous, vulgar, obscene, offensive, objectionable, pornographic, designed to or does interfere or interrupt this web site or any service provided, infected with a virus or other destructive or deleterious programming routine, give rise to civil or criminal liability, or which may violate an applicable local, national or international law;<br />(b) impersonate or misrepresent your association with any person or entity, or forge or otherwise seek to conceal or misrepresent the origin of any Content provided by you;<br />(c) collect or harvest any data about other users;<br />(d) provide or use this web site and any Content or service in any commercial manner or in any manner that would involve junk mail, spam, chain letters, pyramid schemes, or any other form of unauthorized advertising without our prior written consent; <br />(e) provide any Content that may give rise to our civil or criminalliability or which may consititue or be considered a violation of any local, national or international law, including but not limited to laws relating to copyright, trademark, patent, or trade secrets.</p><br />
<p><b>7. Submission of Content on this Web Site</b><br />By providing any Content to our web site:<br />(a) you agree to grant to us a worldwide, royalty-free, perpetual, non-exclusive right and license (including any moral rights or other necessary rights) to use, display, reproduce, modify, adapt, publish, distribute, perform, promote, archive, translate, and to create derivative works and compilations, in whole or in part. Such license will apply with respect to any form, media, technology known or later developed;<br />(b) you warrant and represent that you have all legal, moral, and other rights that may be necessary to grant us with the license set forth in this Section 7;<br />(c) you acknowledge and agree that we shall have the right (but not obligation), in our sole discretion, to refuse to publish or to remove or block access to any Content you provide at any time and for any reason, with or without notice.</p><br />
<p><b>8. Third Party Services</b><br />Goods and services of third parties may be advertised and/or made available on or through this web site. Representations made regarding products and services provided by third parties are governed by the policies and representations made by these third parties. We shall not be liable for or responsible in any manner for any of your dealings or interaction with third parties.</p><br />
<p><b>9. Indemnification</b><br />You agree to indemnify and hold us harmless, our subsidiaries, affiliates, related parties, officers, directors, employees, agents, independent contractors, advertisers, partners, and co-branders from any claim or demand, including reasonable attorney's fees, that may be made by any third party, that is due to or arising out of your conduct or connection with this web site or service, your provision of Content, your violation of this $siteTerms or any other violation of the rights of another person or party.</p><br />
<p><b>10. DISCLAIMER OF WARRANTIES</b><br />YOU UNDERSTAND AND AGREE THAT YOUR USE OF THIS WEB SITE AND ANY SERVICES OR CONTENT PROVIDED (THE &quot;SERVICE&quot;) IS MADE AVAILABLE AND PROVIDED TO YOU AT YOUR OWN RISK. IT IS PROVIDED TO YOU &quot;AS IS&quot; AND WE EXPRESSLY DISCLAIM ALL WARRANTIES OF ANY KIND, IMPLIED OR EXPRESS, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p><br /><p>WE MAKE NO WARRANTY, IMPLIED OR EXPRESS, THAT ANY PART OF THE SERVICE WILL BE UNINTERRUPTED, ERROR-FREE, VIRUS-FREE, TIMELY, SECURE, ACCURATE, RELIABLE, OF ANY QUALITY, NOR THAT ANY CONTENT IS SAFE IN ANY MANNER FOR DOWNLOAD. YOU UNDERSTAND AND AGREE THAT NEITHER US NOR ANY PARTICIPANT IN THE SERVICE PROVIDES PROFESSIONAL ADVICE OF ANY KIND AND THAT USE OF SUCH ADVICE OR ANY OTHER INFORMATION IS SOLELY AT YOUR OWN RISK AND WITHOUT OUR LIABILITY OF ANY KIND.</p><br />
<p>Some jurisdictions may not allow disclaimers of implied warranties and the above disclaimer may not apply to you only as it relates to implied warranties.</p><br />
<p><b>11. LIMITATION OF LIABILITY</b><br />YOU EXPRESSLY UNDERSTAND AND AGREE THAT WE SHALL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL, INDICENTAL, CONSEQUENTIAL OR EXEMPLARY DAMAGES, INCLUDING BUT NOT LIMITED TO, DAMAGES FOR LOSS OF PROFITS, GOODWILL, USE, DATA OR OTHER INTANGIBLE LOSS (EVEN IF WE HAVE BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES), RESULTING FROM OR ARISING OUT OF (I) THE USE OF OR THE INABILITY TO USE THE SERVICE, (II) THE COST TO OBTAIN SUBSTITUTE GOODS AND/OR SERVICES RESULTING FROM ANY TRANSACTION ENTERED INTO ON THROUGH THE SERVICE, (III) UNAUTHORIZED ACCESS TO OR ALTERATION OF YOUR DATA TRANSMISSIONS, (IV) STATEMENTS OR CONDUCT OF ANY THIRD PARTY ON THE SERVICE, OR (V) ANY OTHER MATTER RELATING TO THE SERVICE.</p><p>In some jurisdictions, it is not permitted to limit liability and therefore such limitations may not apply to you.</p><br />
<p><b>12. Reservation of Rights</b><br />We reserve all of our rights, including but not limited to any and all copyrights, trademarks, patents, trade secrets, and any other proprietary right that we may have in our web site, its content, and the goods and services that may be provided. The use of our rights and property requires our prior written consent. We are not providing you with any implied or express licenses or rights by making services available to you and you will have no rights to make any commercial uses of our web site or service without our prior written consent.</p><br />
<p><b>13. Notification of Copyright Infringement</b><br />All copyrights and trademarks on this site are owned by their respective owners. If you believe that your property has been used in any way that would be considered copyright infringement or a violation of your intellectual property rights, our copyright agent may be contacted $adminEmail.</p><br />
<p><b>14. Applicable Law</b><br />You agree that this $siteTerms and any dispute arising out of your use of this web site or our products or services shall be governed by and construed in accordance with local laws where the headquarters of the owner of this web site is located, without regard to its conflict of law provisions. By registering or using this web site and service you consent and submit to the exclusive jurisdiction and venue of the county or city where the headquarters of the owner of this web site is located.</p><br />
<p><b>15. Miscellaneous Information</b><br />(i) In the event that this $siteTerms conflicts with any law under which any provision may be held invalid by a court with jurisdiction over the parties, such provision will be interpreted to reflect the original intentions of the parties in accordance with applicable law, and the remainder of this $siteTerms will remain valid and intact; (ii) The failure of either party to assert any right under this $siteTerms shall not be considered a waiver of any that party's right and that right will remain in full force and effect; (iii) You agree that without regard to any statue or contrary law that any claim or cause arising out of this web site or its services must be filed within one (1) year after such claim or cause arose or the claim shall be forever barred; (iv) We may assign our rights and obligations under this $siteTerms and we shall be relieved of any further obligation.</p><br /></div>";

echo COM_siteHeader();
echo $display;
echo COM_siteFooter();
?>