<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | errorhandler.php                                                         |
// |                                                                          |
// | Displays error box and code                                              |
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

class ErrorHandler
{
    public static function show($e_code, $pages=1)
    {
        global $_CONF;
        $errmsg = array(
            "0001" =>"Could not connect to the forums database.",
            "0002" => "The forum you selected does not exist. Please go back and try again.",
            "0003" => "Password Incorrect.",
            "0004" => "Could not query the topics database.",
            "0005" => "Error getting messages from the database.",
            "0006" => "Please enter the Nickname and the Password.",
            "0007" => "You are not the Moderator of this forum therefore you can't perform this function.",
            "0008" => "You did not enter the correct password, please go back and try again.",
            "0009" => "Could not remove posts from the database.",
            "0010" => "Could not move selected topic to selected forum. Please go back and try again.",
            "0011" => "Could not lock the selected topic. Please go back and try again.",
            "0012" => "Could not unlock the selected topic. Please go back and try again.",
            "0013" => "Could not query the database. <BR>Error: ".DB_error()."",
            "0014" => "No such user or post in the database.",
            "0015" => "Search Engine was unable to query the forums database.",
            "0016" => "That user does not exist. Please go back and search again.",
            "0017" => "You must type a subject to post. You can't post an empty subject. Go back and enter the subject",
            "0018" => "You must choose message icon to post. Go back and choose message icon.",
            "0019" => "You must type a message to post. You can't post an empty message. Go back and enter a message.",
            "0020" => "Could not enter data into the database. Please go back and try again.",
            "0021" => "Can't delete the selected message.",
            "0022" => "An error ocurred while querying the database.",
            "0023" => "Selected message was not found in the forum database.",
            "0024" => "You can't reply to that message. It wasn't sent to you.",
            "0025" => "You can't post a reply to this topic, it has been locked. Contact the administrator if you have any question.",
            "0026" => "The forum or topic you are attempting to post to does not exist. Please try again.",
            "0027" => "You must enter your username and password. Go back and do so.",
            "0028" => "You have entered an incorrect password. Go back and try again.",
            "0029" => "Couldn't update post count.",
            "0030" => "The forum you are attempting to post to does not exist. Please try again.",
            "0031" => "Unknown Error",
            "0035" => "You can't edit a post that's not yours.",
            "0036" => "You do not have permission to edit this post.",
            "0037" => "You did not supply the correct password or do not have permission to edit this post. Please go back and try again.",
            "1001" => "Please enter value for Title.",
            "1002" => "Please enter value for Phone.",
            "1003" => "Please enter value for Summary.",
            "1004" => "Please enter value for Address.",
            "1005" => "Please enter value for City.",
            "1006" => "Please enter value for State/Province.",
            "1007" => "Please enter value for Zipcode.",
            "1008" => "Please enter value for Description.",
            "1009" => "Vote for the selected resource only once.<br>All votes are logged and reviewed.",
            "1010" => "You cannot vote on the resource you submitted.<br>All votes are logged and reviewed.",
            "1011" => "No rating selected - no vote tallied.",
            "1013" => "Please enter a search query.",
            "1016" => "Please enter value for Filename.",
            "1017" => "The file was not uploaded - reported filesize of 0 bytes.",
            "1101" => "Upload approval Error: The temporary file was not found. Check error.log",
            "1102" => "Upload submit Error: The temporary filestore file was not created. Check error.log",
            "1103" => "The download info you provided is already in the database!",
            "1104" => "The download info was not complete - Need to enter a title for the new file",
            "1105" => "The download info was not complete - Need to enter a description for the new file",
            "1106" => "Upload Add Error: The new file was not created. Check error.log",
            "1107" => "Upload Add Error: The temporary file was not found. Check error.log",
            "1108" => "Duplicate file - already existing in filestore",
            "1109" => "File type not allowed",
            "1110" => "You must define and select a category for the uploaded file",

            "9999" => "Unknown Error"
        );

        // determine the destination of this request
        $destination = COM_getCurrentURL();

        // validate the destination is not blank and is part of our site...
        if ( $destination == '' ) {
            $destination = $_CONF['site_url'] . '/filemgmt/index.php';
        }
        if ( substr($destination, 0,strlen($_CONF['site_url'])) != $_CONF['site_url']) {
            $destination = $_CONF['site_url'] . '/filemgmt/index.php';
        }

        $errorno = array_keys($errmsg);
        if (!in_array($e_code, $errorno)) {
            $e_code = '9999';
        }
        //include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
        $display  = COM_siteHeader('menu');
        $display .= '<table width="100%" class="plugin" border="0" cellspacing="0" cellpadding="1">';
        $display .= '<tr><td class="pluginAlert" style="text-align:right;padding:5px;">File Management Plugin</td>';
        $display .= "<td class=\"pluginAlert\" width=\"50%\" style=\"padding:5px 0px 5px 10px;\">Error Code: $e_code</td></tr>";
        $display .= "<tr><td colspan=\"2\" class=\"pluginInfo\"><b>ERROR:</b> $errmsg[$e_code]</td></tr>";
        $display .= '<tr><td colspan="2" class="pluginInfo" style="text-align:center;padding:10px;">';
        $display .= '[ <a href="'.$destination.'">Go Back</a> ]</td></tr></table>';
        $display .= COM_siteFooter();
        echo $display;
        die("");
    }
}
