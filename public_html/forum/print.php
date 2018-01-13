<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | print.php                                                                |
// |                                                                          |
// | Display Forum post in a printable format                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

require_once '../lib-common.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

USES_forum_functions();
USES_forum_format();
USES_forum_topic();

function ff_FormatForPrint( $str, $postmode='html', $status=0 ) {
    global $_FF_CONF;

    $str = FF_formatTextBlock($str,$postmode,'preview',$status);

    $str = str_replace('{','&#123;',$str);
    $str = str_replace('}','&#125;',$str);

    // we don't have a stylesheet for printing, so replace our div with the style...
//    $str = str_replace('<div class="quotemain">','<div style="border: 1px dotted #000;border-left: 4px solid #8394B2;color:#465584;  padding: 4px;  margin: 5px auto 8px auto;">',$str);
    return $str;
}

$id = isset($_REQUEST['id']) ? COM_applyFilter($_REQUEST['id'],true) : 0;

if ($_FF_CONF['registration_required'] && COM_isAnonUser()) {
    $display  = COM_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

//Check is anonymous users can access
if ($id == 0 OR DB_count($_TABLES['ff_topic'],"id",(int) $id) == 0) {
    echo COM_siteHeader();
    echo FF_statusMessage($LANG_GF02['msg166'], $_CONF['site_url'] . "/forum/index.php",$LANG_GF02['msg166']);
    echo COM_siteFooter();
    exit;
}

$forum = DB_getItem($_TABLES['ff_topic'],"forum","id=".(int)$id);
$query = DB_query("SELECT grp_name from {$_TABLES['groups']} groups, {$_TABLES['ff_forums']} forum WHERE forum.forum_id=".(int) $forum." AND forum.grp_id=groups.grp_id");
list ($groupname) = DB_fetchArray($query);
if (!SEC_inGroup($groupname) AND $grp_id != 2) {
    echo COM_siteHeader();
    echo _ff_alertMessage($LANG_GF02['msg02'],$LANG_GF02['msg171']);
    echo COM_siteFooter();
    exit;
}

if (!_ff_canUserViewRating($forum)) {
    echo COM_siteHeader();
    echo _ff_alertMessage($LANG_GF02['msg02'],$LANG_GF02['msg171']);
    echo COM_siteFooter();
    exit;
}

$result = DB_query("SELECT * FROM {$_TABLES['ff_topic']} WHERE (id=".(int)$id.")");
$A = DB_fetchArray($result);

if ($_FF_CONF['allow_smilies']) {
    $search = array(":D", ":)", ":(", "8O", ":?", "B)", ":lol:", ":x", ":P" ,":oops:", ":o",":cry:", ":evil:", ":twisted:", ":roll:", ";)", ":!:", ":question:", ":idea:", ":arrow:", ":|", ":mrgreen:",":mrt:",":love:",":cat:");
    $replace = array("<img style=\"vertical-align:middle;\" src='images/smilies/biggrin.gif' alt='Big Grin'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/smile.gif' alt='Smile'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/frown.gif' alt='Frown'/>","<img style=\"vertical-align:middle;\" src='images/smilies/eek.gif' alt='Eek!'/>","<img style=\"vertical-align:middle;\" src='images/smilies/confused.gif' alt='Confused'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/cool.gif' alt='Cool'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/lol.gif' alt='Laughing Out Loud'/>","<img style=\"vertical-align:middle;\" src='images/smilies/mad.gif' alt='Angry'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/razz.gif' alt='Razz'/>","<img style=\"vertical-align:middle;\" src='images/smilies/redface.gif' alt='Oops!'/>","<img style=\"vertical-align:middle;\" src='images/smilies/surprised.gif' alt='Surprised!'/>","<img style=\"vertical-align:middle;\" src='images/smilies/cry.gif' alt='Cry'/>","<img style=\"vertical-align:middle;\" src='images/smilies/evil.gif' alt='Evil'/>","<img style=\"vertical-align:middle;\" src='images/smilies/twisted.gif' alt='Twisted Evil'/>","<img style=\"vertical-align:middle;\" src='images/smilies/rolleyes.gif' alt='Rolling Eyes'/>","<img style=\"vertical-align:middle;\" src='images/smilies/wink.gif' alt='Wink'/>","<img style=\"vertical-align:middle;\" src='images/smilies/exclaim.gif' alt='Exclaimation'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/question.gif' alt='Question'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/idea.gif' alt='Idea'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/arrow.gif' alt='Arrow'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/neutral.gif' alt='Neutral'/>", "<img style=\"vertical-align:middle;\" src='images/smilies/mrgreen.gif' alt='Mr. Green'/>","<img style=\"vertical-align:middle;\" src='images/smilies/mrt.gif' alt='Mr. T'/>","<img style=\"vertical-align:middle;\" src='images/smilies/heart.gif' alt='Love'/>","<img style=\"vertical-align:middle;\" src='images/smilies/cat.gif' alt='Kitten'/>");
}

$A["name"] = COM_checkWords($A["name"]);
$A["name"] = @htmlspecialchars($A["name"],ENT_QUOTES,COM_getEncodingt());

$A["subject"] = COM_checkWords($A["subject"]);
$A["subject"] = stripslashes(@htmlspecialchars($A["subject"],ENT_QUOTES,COM_getEncodingt()));

$A['comment'] = ff_FormatForPrint( $A['comment'], $A['postmode'],'',$A['status'] );
list($cacheFile,$style_cache_url) = COM_getStyleCacheLocation();
$date = strftime('%B %d %Y @ %I:%M %p', $A['date']);
echo"
    <html>
    <head>
        <title>$_CONF[site_name] - ".$LANG_GF02['msg147']." $A[id]]</title>
        <link rel=\"stylesheet\" type=\"text/css\" href=\"$style_cache_url\">
    </head>
    <body onload=\"window.print();\">
      <div style=\"box-sizing: border-box;max-width:980px;padding:0px 25px;\">
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

$result2 = DB_query("SELECT * FROM {$_TABLES['ff_topic']} WHERE (pid=".(int)$id.")");
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
            <p>" . ff_FormatForPrint( $B['comment'], $B['postmode'] ) . "</p>
            <hr width=\"25%\" align=\"left\">

";

}

echo"

            <p>$_CONF[site_name] - {$LANG_GF01['FORUM']}<br/>
                    <a href=\"$_CONF[site_url]/forum/viewtopic.php?showtopic=$A[id]\">$_CONF[site_url]/forum/viewtopic.php?showtopic=$A[id]</a>
            </p>

        </font>
      </div>
    </body>
    </html>
";

?>