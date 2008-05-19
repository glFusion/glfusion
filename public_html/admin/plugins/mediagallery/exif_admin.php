<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id:: exif_admin.php 1990 2008-02-13 04:22:12Z mevans0263                $|
// | select which exif/iptc tags admin cares about                             |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
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

require_once('../../../lib-common.php');
require_once($_MG_CONF['path_html'] . 'lib-exif.php');
require_once($_MG_CONF['path_admin'] . 'navigation.php');

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_adminEXIF() {
    global $_TABLES, $_MG_CONF, $_CONF, $LANG_MG01, $LANG_MG04;

    $retval = '';

    $T = new Template($_MG_CONF['template_path']);
    $T->set_file ('admin','exif_tags.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var('xhtml',XHTML);

    $T->set_block('admin', 'exifRow', 'eRow');

    $sql = "SELECT * FROM {$_TABLES['mg_exif_tags']}";
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    for ($i=0;$i<$nRows;$i++) {
        $row = DB_fetchArray($result);
        $properties[] = $row['name'];
        $tag[$row['name']][] = $row['selected'];
    }
    $exifKeys   = getExifKeys();
    $x = 0;
    foreach( $properties as $property ) {
        $title = $exifKeys[$property][0];
        $T->set_var(array(
            'exif_tag'  => $title,
            'selected'  => $tag[$property][0] ? ' checked="checked"' : '',
            'tag'       => $property,
            'rowcounter' => $x % 2,
        ));
        $T->parse('eRow', 'exifRow',true);
        $x++;
    }

    $T->set_var(array(
        'lang_select'           => $LANG_MG01['select'],
        'lang_exiftag'          => $LANG_MG01['exiftag'],
        'lang_exif_admin_help'  => $LANG_MG01['exif_admin_help'],
        'lang_check_all'        => $LANG_MG01['check_all'],
        'lang_uncheck_all'      => $LANG_MG01['uncheck_all'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        's_form_action'         => $_MG_CONF['admin_url'] . 'exif_admin.php',
    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_adminEXIFsave() {
    global $_MG_CONF, $_TABLES, $_CONF, $LANG_MG01;

    $numItems = count($_POST['sel']);

    for ($i=0; $i < $numItems; $i++) {
        $exif[$i]['tag'] = $_POST['tag'][$i];
        $exif[$i]['sel'] = $_POST['sel'][$i];
    }
    DB_query("UPDATE {$_TABLES['mg_exif_tags']} set selected=0"); // resets all to 0
    for ( $i=0; $i < $numItems; $i++ ) {
        $sql = "UPDATE {$_TABLES['mg_exif_tags']} set selected=1 WHERE name='" . $exif[$i]['sel'] . "'";
        DB_query($sql);
    }
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=3');
    exit;
}

$display = COM_siteHeader();
$T = new Template($_MG_CONF['template_path']);
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['version'],
    'xhtml'             => XHTML,
));

/* --- Main Processing Here --- */

if (!isset($_POST['mode']) ) {
    $T->set_var(array(
        'admin_body'    => MG_adminEXIF(),
        'title'         => $LANG_MG01['exif_admin_header'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"' . XHTML . '>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#EXIFIPTC_Administration',
    ));
} else {
    $mode = COM_applyFilter($_POST['mode']);
    switch ($mode) {
        case $LANG_MG01['save'] :
            MG_adminEXIFsave();
            break;
        case $LANG_MG01['cancel'] :
            echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
            break;
        default :
            echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=3');
            break;
    }
}

$T->parse('output', 'admin');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;


?>