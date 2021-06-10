<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* EXIF / IPTC Tag Administration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-exif.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
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

    $T = new Template($_MG_CONF['template_path'].'/admin/');
    $T->set_file ('admin','exif_tags.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

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
        $sql = "UPDATE {$_TABLES['mg_exif_tags']} set selected=1 WHERE name='" . DB_escapeString($exif[$i]['sel']) . "'";
        DB_query($sql);
    }
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=3');
    exit;
}


$T = new Template($_MG_CONF['template_path'].'/admin/');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

/* --- Main Processing Here --- */

if (!isset($_POST['mode']) ) {
    $T->set_var(array(
        'admin_body'    => MG_adminEXIF(),
        'title'         => $LANG_MG01['exif_admin_header'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
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
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>