<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | includehtml.php                                                           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Plugin Authors                                                            |
// | Blaine Lang,                  blaine@portalparts.com, www.portalparts.com |
// | Version 1.0 co-developer:     Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :         Mr.GxBlock, www.gxblock.com                 |
// +---------------------------------------------------------------------------+
// |                                                                           |
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
//

$smilies ="
<table width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr align='center'>
<td style=\"white-space:nowrap;\">
    <a href=\"javascript:emoticon(':D')\"><img align=\"middle\" src=\"images/smilies/biggrin.gif\" alt=\"Big Grin\" title=\"Big Grin\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':)')\"><img align=\"middle\" src=\"images/smilies/smile.gif\" alt=\"Smile\" title=\"Smile\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':(')\"><img align=\"middle\" src=\"images/smilies/frown.gif\" alt=\"Frown\" title=\"Frown\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon('8O')\"><img align=\"middle\" src=\"images/smilies/eek.gif\" alt=\"Eek!\" title=\"Eek\" border=\"0\" /></a>
</td>
<td height=\"2\"></td>
</tr>
<tr align='center'>
<td style=\"white-space:nowrap;\">
    <a href=\"javascript:emoticon(':?')\"><img align=\"middle\" src=\"images/smilies/confused.gif\" alt=\"Confused\" title=\"Confused\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon('B)')\"><img align=\"middle\" src=\"images/smilies/cool.gif\" alt=\"Cool\" title=\"Cool\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':lol:')\"><img align=\"middle\" src=\"images/smilies/lol.gif\" alt=\"Laughing Out Loud\" title=\"Laughing Out Loud\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':x')\"><img align=\"middle\" src=\"images/smilies/mad.gif\" alt=\"Angry\" title=\"Angry\" border=\"0\" /></a>
</td>
<td height=\"2\"></td>
</tr>
<tr align='center'>
<td style=\"white-space:nowrap;\">
    <a href=\"javascript:emoticon(':P')\"><img align=\"middle\" src=\"images/smilies/razz.gif\" alt=\"Razz\" title=\"Razz\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':oops:')\"><img align=\"middle\" src=\"images/smilies/redface.gif\" alt=\"Oops!\" title=\"Oops!\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':o')\"><img align=\"middle\" src=\"images/smilies/surprised.gif\" alt=\"Surprised!\" title=\"Surprised!\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':cry:')\"><img align=\"middle\" src=\"images/smilies/cry.gif\" alt=\"Cry\" title=\"Cry\" border=\"0\" /></a>
</td>
<td height=\"2\"></td>
</tr>
<tr align='center'>
<td style=\"white-space:nowrap;\">
    <a href=\"javascript:emoticon(':evil:')\"><img align=\"middle\" src=\"images/smilies/evil.gif\" alt=\"Evil\" title=\"Evil\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':twisted:')\"><img align=\"middle\" src=\"images/smilies/twisted.gif\" alt=\"Twisted Evil\" title=\"Twisted Evil\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':roll:')\"><img align=\"middle\" src=\"images/smilies/rolleyes.gif\" alt=\"Rolling Eyes\" title=\"Rolling Eyes\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(';)')\"><img align=\"middle\" src=\"images/smilies/wink.gif\" alt=\"Wink\" title=\"Wink\" border=\"0\" /></a>
</td>
<td height=\"2\"></td>
</tr>
<tr align='center'>
<td style=\"white-space:nowrap;\">
    <a href=\"javascript:emoticon(':!:')\"><img align=\"middle\" src=\"images/smilies/exclaim.gif\" alt=\"Exclaimation\" title=\"Exclaimation\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':question:')\"><img align=\"middle\" src=\"images/smilies/question.gif\" alt=\"Question\" title=\"Question\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':idea:')\"><img align=\"middle\" src=\"images/smilies/idea.gif\" alt=\"Idea\" title=\"Idea\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':arrow:')\"><img align=\"middle\" src=\"images/smilies/arrow.gif\" alt=\"Arrow\" title=\"Arrow\" border=\"0\" /></a>
</td>
<td height=\"2\"></td>
</tr>
<tr align='center'>
<td style=\"white-space:nowrap;\">
    <a href=\"javascript:emoticon(':|')\"><img align=\"middle\" src=\"images/smilies/neutral.gif\" alt=\"Neutral\" title=\"Neutral\" border=\"0\" /></a> |
    <a href=\"javascript:emoticon(':mrgreen:')\"><img align=\"middle\" src=\"images/smilies/mrgreen.gif\" alt=\"Mr. Green\" title=\"Mr. Green\" border=\"0\" /></a>
</td>
<td height=\"2\"></td>
</tr>
</table>";

?>