<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | sigpreview.php                                                           |
// |                                                                          |
// | returns preview of user signature to AJAX routine                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

if ( !COM_isAjax()) die();

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() || $_FF_CONF['bbcode_signature'] == 0 ) {
    return '';
}

USES_forum_functions();
USES_forum_format();
USES_lib_bbcode();

$retval = array();

if ( isset($_POST['signature']) ) {
    $signature = $_POST['signature'];
} else {
    $signature = '';
}
if ( $_FF_CONF['allow_img_bbcode'] != true ) {
    $exclude = array('img');
} else {
    $exclude = array();
}

$preview_sig = BBC_formatTextBlock($signature,'text',array(),array(),$exclude);

$retval['errorCode'] = 0;
$retval['signature'] = $preview_sig;
$retval['statusMessage'] = '';
$return["js"] = json_encode($retval);
echo json_encode($return);
exit;
?>