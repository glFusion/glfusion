<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | print.php                                                                |
// |                                                                          |
// | Display Forum post in a printable format                                 |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

require_once '../lib-common.php'; // Path to your lib-common.php

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';
require_once $_CONF['path'] . 'plugins/forum/debug.php';  // Common Debug Code
require_once $_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php';

function gf_FormatForPrint( $str, $postmode='html' ) {
    global $CONF_FORUM;

    // Handle Pre ver 2.5 quoting and New Line Formatting - consider adding this to a migrate function
    if ($CONF_FORUM['pre2.5_mode']) {
        if ( stristr($str,'[code') == false ) {
            $str = str_replace('<pre>','[code]',$str);
            $str = str_replace('</pre>','[/code]',$str);
        }
        $str = str_replace(array("<br />\r\n","<br />\n\r","<br />\r","<br />\n"), '<br />', $str );
        $str = preg_replace("/\[QUOTE\sBY=\s(.+?)\]/i","[QUOTE] Quote by $1:",$str);
        /* Reformat code blocks - version 2.3.3 and prior */
        $str = str_replace( '<pre class="forumCode">', '[code]', $str );
        $str = preg_replace("/\[QUOTE\sBY=(.+?)\]/i","[QUOTE] Quote by $1:",$str);
    }

    $str = gf_formatTextBlock($str,$postmode,'preview');

    $str = str_replace('{','&#123;',$str);
    $str = str_replace('}','&#125;',$str);

    // we don't have a stylesheet for printing, so replace our div with the style...
    $str = str_replace('<div class="quotemain">','<div style="border: 1px dotted #000;border-left: 4px solid #8394B2;color:#465584;  padding: 4px;  margin: 5px auto 8px auto;">',$str);
    return $str;
}

// Pass thru filter any get or post variables to only allow numeric values and remove any hostile data
$id = intval(COM_applyFilter($_REQUEST['id'],true));

//Check is anonymous users can access
if ($CONF_FORUM['registration_required'] && $_USER['uid'] < 2) {
    echo COM_siteHeader();
    echo COM_startBlock();
    alertMessage($LANG_GF02['msg01'],$LANG_GF02['msg171']);
    echo COM_endBlock();
    echo COM_siteFooter();
    exit;
}


//Check is anonymous users can access
if ($id == 0 OR DB_count($_TABLES['gf_topic'],"id",$id) == 0) {
        echo COM_siteHeader();
        forum_statusMessage($LANG_GF02['msg166'], $_CONF['site_url'] . "/forum/index.php?forum=$forum",$LANG_GF02['msg166']);
        echo COM_siteFooter();
        exit;
}

$forum = DB_getItem($_TABLES['gf_topic'],"forum","id=$id");
$query = DB_query("SELECT grp_name from {$_TABLES['groups']} groups, {$_TABLES['gf_forums']} forum WHERE forum.forum_id='{$forum}' AND forum.grp_id=groups.grp_id");
list ($groupname) = DB_fetchArray($query);
if (!SEC_inGroup($groupname) AND $grp_id != 2) {
        echo COM_siteHeader();
        alertMessage($LANG_GF02['msg02'],$LANG_GF02['msg171']);
        echo COM_siteFooter();
        exit;
}

if (!can_view_forum($forum)) {
        echo COM_siteHeader();
        alertMessage($LANG_GF02['msg02'],$LANG_GF02['msg171']);
        echo COM_siteFooter();
        exit;
}

$result = DB_query("SELECT * FROM {$_TABLES['gf_topic']} WHERE (id=$id)");
$A = DB_fetchArray($result);

if ($CONF_FORUM['allow_smilies']) {
        $search = array(":D", ":)", ":(", "8O", ":?", "B)", ":lol:", ":x", ":P" ,":oops:", ":o",":cry:", ":evil:", ":twisted:", ":roll:", ";)", ":!:", ":question:", ":idea:", ":arrow:", ":|", ":mrgreen:",":mrt:",":love:",":cat:");
        $replace = array("<img style=\"vertical-align:middle;\" src='images/smilies/biggrin.gif' alt='Big Grin'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/smile.gif' alt='Smile'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/frown.gif' alt='Frown'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/eek.gif' alt='Eek!'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/confused.gif' alt='Confused'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/cool.gif' alt='Cool'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/lol.gif' alt='Laughing Out Loud'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/mad.gif' alt='Angry'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/razz.gif' alt='Razz'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/redface.gif' alt='Oops!'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/surprised.gif' alt='Surprised!'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/cry.gif' alt='Cry'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/evil.gif' alt='Evil'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/twisted.gif' alt='Twisted Evil'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/rolleyes.gif' alt='Rolling Eyes'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/wink.gif' alt='Wink'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/exclaim.gif' alt='Exclaimation'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/question.gif' alt='Question'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/idea.gif' alt='Idea'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/arrow.gif' alt='Arrow'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/neutral.gif' alt='Neutral'" . XHTML . ">", "<img style=\"vertical-align:middle;\" src='images/smilies/mrgreen.gif' alt='Mr. Green'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/mrt.gif' alt='Mr. T'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/heart.gif' alt='Love'" . XHTML . ">","<img style=\"vertical-align:middle;\" src='images/smilies/cat.gif' alt='Kitten'" . XHTML . ">");
}

$A["name"] = COM_checkWords($A["name"]);
$A["name"] = @htmlspecialchars($A["name"],ENT_QUOTES,COM_getEncodingt());

$A["subject"] = COM_checkWords($A["subject"]);
$A["subject"] = stripslashes(@htmlspecialchars($A["subject"],ENT_QUOTES,COM_getEncodingt()));

$A['comment'] = gf_FormatForPrint( $A['comment'], $A['postmode'] );

$date = strftime('%B %d %Y @ %I:%M %p', $A['date']);
echo"
    <html>
    <head>
        <title>$_CONF[site_name] - ".$LANG_GF02['msg147']." $A[id]]</title>
    </head>
    <body>
        <font face=\"verdana\" size=\"2\">
                <h3>{$LANG_GF01['SUBJECT']}: $A[subject]</h3>
                <b>{$LANG_GF01['POSTEDON']}:</b> $date
            <br>
                <b>{$LANG_GF01['BY']}</b> $A[name]
            <br>
            <br>
            <b>{$LANG_GF01['CONTENT']}:</b>
            <p>$A[comment]</p>
            <hr width=\"25%\" align=\"left\">

        <br>
        <b>{$LANG_GF01['REPLIES']}:</b>
        <hr width=\"50%\" align=\"left\">
        <br>
";

$result2 = DB_query("SELECT * FROM {$_TABLES['gf_topic']} WHERE (pid=$id)");
while($B = DB_fetchArray($result2)){
$date = strftime('%B %d %Y @ %I:%M %p', $B['date']);

echo"

                <h4>$B[subject]</h4>
                <b>{$LANG_GF01['POSTEDON']}:</b> $date
            <br>
                <b>{$LANG_GF01['BY']}</b> $B[name]
            <br>
            <br>
            <b>{$LANG_GF01['CONTENT']}:</b>
            <p>" . gf_FormatForPrint( $B['comment'], $B['postmode'] ) . "</p>
            <hr width=\"25%\" align=\"left\">

";

}

echo"

            <p>$_CONF[site_name] - {$LANG_GF01['FORUM']}<br" . XHTML . ">
                    <a href=\"$_CONF[site_url]/forum/viewtopic.php?showtopic=$A[id]\">$_CONF[site_url]/forum/viewtopic.php?showtopic=$A[id]</a>
            </p>

        </font>

    </body>
    </html>
";

?>