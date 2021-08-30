<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | textsanitizer.php                                                        |
// |                                                                          |
// | input / display cleaner                                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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
namespace Filemgmt;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class MyTextSanitizer
{

    /**
     * Constructor of this class.
     * Gets allowed html tags from admin config settings
     * <br> should not be allowed since nl2br will be used
     * when storing data.
     */
    function __construct(){
    }

    public static function getInstance(){
        return new MyTextSanitizer();
    }

     /*
     * Rewritten by Nathan Codding - Feb 6, 2001.
     * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
     *     to that URL
     * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
     *     to http://www.xxxx.yyyy[/zzzz]
     * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
     *        to that email address
     * - Only matches these 2 patterns either after a space, or at the beginning of a line
     *
     * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
     * have it require something like xxxx@yyyy.zzzz or such. We'll see.
     */
    function makeClickable($text) {
            // pad it with a space so we can match things at the start of the 1st line.
        $ret = " " . $text;

        // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
        // xxxx can only be alpha characters.
        // yyyy is anything up to the first space, newline, or comma.
        $ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", $ret);

        // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
        // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
        // yyyy contains either alphanum, "-", or "."
        // zzzz is optional.. will contain everything up to the first space, newline, or comma.
        // This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
        // This is to keep it from getting annoying and matching stuff that's not meant to be a link.
        $ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a>", $ret);

        // matches an email@domain type address at the start of a line, or after a space.
        // Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
        // After the @ sign, we accept anything up to the first space, linebreak, or comma.
        $ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);

        // Remove our padding..
        $ret = substr($ret, 1);

        return($ret);
    }

    function xoopsCodeDecode($text)
    {
        $patterns = array();
        $replacements = array();
        $patterns[] = "/\[url=(['\"]?)(http[s]?:\/\/[^\"']*)\\1](.*)\[\/url\]/sU";
        $replacements[] = "<a href='\\2' target='_blank'>\\3</a>";
        $patterns[] = "/\[url=(['\"]?)([^\"']*)\\1](.*)\[\/url\]/sU";
        $replacements[] = "<a href='http://\\2' target='_blank'>\\3</a>";
        $patterns[] = "/\[color=(['\"]?)([^\"']*)\\1](.*)\[\/color\]/sU";
        $replacements[] = "<span style='color: #\\2;'>\\3</span>";
        $patterns[] = "/\[size=(['\"]?)([^\"']*)\\1](.*)\[\/size\]/sU";
        $replacements[] = "<span style='font-size: \\2;'>\\3</span>";
        $patterns[] = "/\[font=(['\"]?)([^\"']*)\\1](.*)\[\/font\]/sU";
        $replacements[] = "<span style='font-family: \\2;'>\\3</span>";
        $patterns[] = "/\[email]([^\"]*)\[\/email\]/sU";
        $replacements[] = "<a href='mailto:\\1'>\\1</a>";
        $patterns[] = "/\[b](.*)\[\/b\]/sU";
        $replacements[] = "<b>\\1</b>";
        $patterns[] = "/\[i](.*)\[\/i\]/sU";
        $replacements[] = "<i>\\1</i>";
        $patterns[] = "/\[u](.*)\[\/u\]/sU";
        $replacements[] = "<u>\\1</u>";
        //$patterns[] = "/\[li](.*)\[\/li\]/sU";
        //$replacements[] = "<li>\\1</li>";
        $patterns[] = "/\[img align=(['\"]?)(left|right)\\1]([^\"\(\)\?\&]*)\[\/img\]/sU";
        $replacements[] = "<img src='\\3' align='\\2' alt'/' />";
        $patterns[] = "/\[img]([^\"\(\)\?\&]*)\[\/img\]/sU";
        $replacements[] = "<img src='\\1' alt'/' />";
        $text = preg_replace($patterns, $replacements, $text);
        $text = str_replace("[quote]", "<div style='text-align:left;width:85%;'><small>"._QUOTEC."</small><hr /><small><blockquote>", $text);
        $text = str_replace("[/quote]", "</blockquote></small><hr /></div>", $text);
        //$text = str_replace("[ul]","<ul>",$text);
        //$text = str_replace("[/ul]","</ul>",$text);
        //$text = str_replace("[hr]","<hr />",$text);
        return $text;
    }

    /*
    * Nathan Codding - August 24, 2000.
    * Takes a string, and does the reverse of the PHP standard function
    * HtmlSpecialChars().
    * Original Name : undo_htmlspecialchars
    */
        function undoHtmlSpecialChars($input) {
        $input = preg_replace("/&gt;/i", ">", $input);
        $input = preg_replace("/&lt;/i", "<", $input);
        $input = preg_replace("/&quot;/i", "\"", $input);
        //$input = preg_replace("/&amp;/i", "&", $input);
        $input = preg_replace("/&#039;/i", "'", $input);
        return $input;
    }


    function oopsNl2Br($text) {
        $text = preg_replace("/(\015\012)|(\015)|(\012)/","<br />",$text);
        return $text;
    }

    /*
    * if magic_quotes_gpc is off, add back slashes
    */
    function oopsDB_escapeString($text) {
        $text = DB_escapeString($text);
        return $text;
    }

    /*
    * if magic_quotes_gpc is on, stirip back slashes
    */
    function oopsStripSlashesGPC($text) {
        return $text;
    }

    /*
    * if magic_quotes_runtime is on, stirip back slashes
    */
    function oopsStripSlashesRT($text) {
        return $text;
    }

    /*
    *  htmlspecialchars will not convert single quotes by default,
    *  so i made this function.
    */
    function oopsHtmlSpecialChars($text) {
        $text = htmlspecialchars($text);
        $text = str_replace("'","&#039;",$text);
        $text = preg_replace("/&amp;/i", "&", $text);
        return $text;
    }

    /*
    *  Filters both textbox and textarea form data before display
    *  For internal use
    */
    function sanitizeForDisplay($text, $allowhtml = 0, $smiley = 1, $bbcode = 1) {
        //$text = $this->oopsStripSlashesRT($text);
        if ( $allowhtml == 0 ) {
            $text = $this->oopsHtmlSpecialChars($text);
        } else {
            $config =& $GLOBALS['xoopsConfig'];
            $allowed = $config['allowed_html'];
            $text = strip_tags($text, $allowed);
            $text = $this->makeClickable($text);
        }
        if ( $smiley == 1 ) {
            $text = $this->smiley($text);
        }
        if ( $bbcode == 1 ) {
            $text = $this->xoopsCodeDecode($text);
        }
        $text = $this->oopsNl2Br($text);
        return $text;
    }

    /*
    *  Filters both textbox and textarea form data before preview
    *  For internal use
    */
    function sanitizeForPreview($text, $allowhtml = 0, $smiley = 1, $bbcode = 1) {
        $text = $this->oopsStripSlashesGPC($text);
        if ( $allowhtml == 0 ) {
            $text = $this->oopsHtmlSpecialChars($text);
        } else {
            $config =& $GLOBALS['xoopsConfig'];
            $allowed = $config['allowed_html'];
            $text = strip_tags($text, $allowed);
            $text = $this->makeClickable($text);
        }
        if ( $smiley == 1 ) {
            $text = $this->smiley($text);
        }
        if ( $bbcode == 1 ) {
            $text = $this->xoopsCodeDecode($text);
        }
        $text = $this->oopsNl2Br($text);
        return $text;
    }


    /*
    *  Used for saving textbox form data.
    *  Adds slashes if magic_quotes_gpc is off.
    */
    function makeTboxData4Save($text){
        //$text = $this->undoHtmlSpecialChars($text);
        $text = $this->oopsDB_escapeString($text);
        return $text;
    }

    /*
    *  Used for displaying textbox form data from DB.
    *  Smilies can also be used.
    */
    function makeTboxData4Show($text,$smiley=0){
        $text = $this->sanitizeForDisplay($text,0,$smiley,0); //do HtmlSpecialChars
        return $text;
    }

    /*
    *  Used when textbox data in DB is to be editted in html form.
    *  "&amp;" must be converted back to "&" to maintain the correct
    *  ISO encoding values, which is needed for some multi-bytes chars.
    */
    function makeTboxData4Edit($text){
        //$text = $this->oopsStripSlashesRT($text);
        $text = $this->oopsHtmlSpecialChars($text);
        return $text;
    }

    /*
    *  Called when previewing textbox form data submitted from a form.
    *  Smilies can be used if needed
    *  Use makeTboxData4PreviewInForm when textbox data is to be
    *  previewed in textbox again
    */
    function makeTboxData4Preview($text,$smiley=0){
        $text = $this->sanitizeForPreview($text,0,$smiley,0); //do HtmlSpecialChars
        return $text;
    }
    function makeTboxData4PreviewInForm($text){
        $text = $this->oopsStripSlashesGPC($text);
        $text = $this->oopsHtmlSpecialChars($text);
        return $text;
    }


    /*
    *  Called before saving first-time or editted textarea
    *  data into DB
    */
    function makeTareaData4Save($text){
        $text = $this->oopsDB_escapeString($text);
        return $text;
    }

    /*
    *   Called before displaying textarea form data from DB
    */
    function makeTareaData4Show($text, $allowhtml=1, $smiley=0, $bbcode=0){
        $text = $this->sanitizeForDisplay($text,$allowhtml,$smiley,$bbcode);
        return $text;
    }

    /*
    *   Called when textarea data in DB is to be editted in html form
    */
    function makeTareaData4Edit($text){
        //if magic_quotes_runtime is on, do stipslashes
        //$text = $this->oopsStripSlashesRT($text);
        $text = $this->oopsHtmlSpecialChars($text);
        return $text;
    }

    /*
    *   Called when previewing textarea data which was submitted
    *   via an html form
    */
    function makeTareaData4Preview($text, $allowhtml=1, $smiley=0, $bbcode=0){
        $text = $this->sanitizeForPreview($text,$allowhtml,$smiley,$bbcode);
        return $text;
    }

    /*
    *  Called when previewing textarea data whih was submitted via an
    *  html form.
    *  This time, text area data is inserted into textarea again
    */
    function makeTareaData4PreviewInForm($text){
        //if magic_quotes_gpc is on, do stipslashes
        $text = $this->oopsStripSlashesGPC($text);
        $text = $this->oopsHtmlSpecialChars($text);
        return $text;
    }

    /*
    *  Use this function when you need to output textarea value inside
    *  quotes. For example, meta keywords are saved as textarea value
    *  but it is displayed inside <meta> tag keywords attribute with
    *  quotes around it. This can be also used for textbox values.
    */
    function makeTareaData4InsideQuotes($text){
        //$text = $this->oopsStripSlashesRT($text);
        $text = $this->oopsHtmlSpecialChars($text);
        return $text;
    }

    /*
     *  Replaces banned words in a string with their replacements
     */
    function censorString($text) {
        global $xoopsBadWords;
        $replacement = "####";
        foreach ($xoopsBadWords as $bad) {
                  $bad = quotemeta($this->undoHtmlSpecialChars($bad));
            $patterns[] = "/(\s)".$bad."/siU";
            $replacements[] = "\\1".$replacement."";
            $patterns[] = "/^".$bad."/siU";
            $replacements[] = $replacement;
            $patterns[] = "/(\n)".$bad."/siU";
            $replacements[] = "\\1".$replacement."";
            $patterns[] = "/]".$bad."/siU";
            $replacements[] = "]".$replacement."";
            $text = preg_replace($patterns, $replacements, $text);
           }
           return $text;
    }
}

?>
