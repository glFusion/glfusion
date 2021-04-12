<?php
/**
* glFusion CMS
*
* FileMgmt Plugin Configuration Installer
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die('This file can not be used on its own!');
}

/*
 * FileMgmt default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_FM_DEFAULT;
$_FM_DEFAULT = array();

$_FM_DEFAULT['popular_download'] = 20;
$_FM_DEFAULT['newdownloads']     = 10;
$_FM_DEFAULT['perpage']          = 5;
$_FM_DEFAULT['trimdesc']         = true;
$_FM_DEFAULT['whatsnew']         = true;
$_FM_DEFAULT['dlreport']         = true;
$_FM_DEFAULT['selectpriv']       = false;
$_FM_DEFAULT['publicpriv']       = true;
$_FM_DEFAULT['uploadselect']     = true;
$_FM_DEFAULT['uploadpublic']     = true;
$_FM_DEFAULT['useshots']         = true;
$_FM_DEFAULT['shotwidth']        = 50;
$_FM_DEFAULT['emailoption']      = true;
$_FM_DEFAULT['FileStore']        = $_CONF['path_html'] . 'data/filemgmt/files/';
$_FM_DEFAULT['SnapStore']        = $_CONF['path_html'] . 'data/filemgmt/snaps/';
$_FM_DEFAULT['SnapCat']          = $_CONF['path_html'] . 'data/filemgmt/category_snaps/';
$_FM_DEFAULT['FileStoreURL']     = $_CONF['site_url']  . '/data/filemgmt/files/';
$_FM_DEFAULT['FileSnapURL']      = $_CONF['site_url']  . '/data/filemgmt/snaps/';
$_FM_DEFAULT['SnapCatURL']       = $_CONF['site_url']  . '/data/filemgmt/category_snaps/';

$_FM_DEFAULT['FilePermissions']     = (int) 0755;
$_FM_DEFAULT['WhatsNewPeriodDays']  = 14;
$_FM_DEFAULT['WhatsNewTitleLength'] = 20;
$_FM_DEFAULT['showWhatsNewComments']= true;

$_FM_DEFAULT['numCategoriesPerRow']     = 2;
$_FM_DEFAULT['numSubCategories2Show']   = 5;
$_FM_DEFAULT['displayblocks']           = 0;


/**
* the filemgmt plugin's config array
*/
global $_FM_CONF;
$_FM_CONF = array();

if ( isset($filemgmt_FileStore) && $filemgmt_FileStore != '' && (strpos($filemgmt_FileStore,'##FILESTORE##') !== false )) {
    $_FM_CONF['popular_download'] = $mydownloads_popular;
    $_FM_CONF['newdownloads']     = $mydownloads_newdownloads;
    $_FM_CONF['perpage']          = $mydownloads_perpage;
    $_FM_CONF['trimdesc']         = $mydownloads_trimdesc;
    $_FM_CONF['whatsnew']         = $mydownloads_whatsnew;
    $_FM_CONF['dlreport']         = $mydownloads_dlreport;
    $_FM_CONF['selectpriv']       = $mydownloads_selectpriv;
    $_FM_CONF['publicpriv']       = $mydownloads_publicpriv;
    $_FM_CONF['uploadselect']     = $mydownloads_uploadselect;
    $_FM_CONF['uploadpublic']     = $mydownloads_uploadpublic;
    $_FM_CONF['useshots']         = $mydownloads_useshots;
    $_FM_CONF['shotwidth']        = $mydownloads_shotwidth;
    $_FM_CONF['emailoption']      = $filemgmt_Emailoption;
    $_FM_CONF['FileStore']        = $filemgmt_FileStore;
    $_FM_CONF['SnapStore']        = $filemgmt_SnapStore;
    $_FM_CONF['SnapCat']          = $filemgmt_SnapCat;
    $_FM_CONF['FileStoreURL']     = $filemgmt_FileStoreURL;
    $_FM_CONF['FileSnapURL']      = $filemgmt_FileSnapURL;
    $_FM_CONF['SnapCatURL']       = $filemgmt_SnapCatURL;
}

/**
* Initialize FileMgmt plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_FM_CONF if available (e.g. from
* an old config.php), uses $_FM_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_filemgmt()
{
    global $_FM_CONF, $_FM_DEFAULT;

    if (is_array($_FM_CONF) && (count($_FM_CONF) > 1)) {
        $_FM_DEFAULT = array_merge($_FM_DEFAULT, $_FM_CONF);
    }
    $c = config::get_instance();
    if (!$c->group_exists('filemgmt')) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'filemgmt');
        $c->add('fs_public', NULL, 'fieldset', 0, 0, NULL, 0, true, 'filemgmt');
        $c->add('perpage', $_FM_DEFAULT['perpage'], 'select',
                0, 0, 2, 10, true, 'filemgmt');
        $c->add('popular_download', $_FM_DEFAULT['popular_download'], 'text',
                0, 0, 0, 20, true, 'filemgmt');
        $c->add('newdownloads', $_FM_DEFAULT['newdownloads'], 'text',
                0, 0, 0, 30, true, 'filemgmt');
        $c->add('dlreport', $_FM_DEFAULT['dlreport'],'select',
                0, 0, 0, 40, true, 'filemgmt');
        $c->add('trimdesc', $_FM_DEFAULT['trimdesc'], 'select',
                0, 0, 0, 50, true, 'filemgmt');
        $c->add('whatsnew', $_FM_DEFAULT['whatsnew'],'select',
                0, 0, 0, 60, true, 'filemgmt');
        $c->add('whatsnewperioddays', $_FM_DEFAULT['WhatsNewPeriodDays'], 'text',
                0, 0, 0, 70, true, 'filemgmt');
        $c->add('whatsnewtitlelength', $_FM_DEFAULT['WhatsNewTitleLength'], 'text',
                0, 0, 0, 80, true, 'filemgmt');
        $c->add('showwhatsnewcomments', $_FM_DEFAULT['showWhatsNewComments'],'select',
                0, 0, 0, 90, true, 'filemgmt');
        $c->add('numcategoriesperrow', $_FM_DEFAULT['numCategoriesPerRow'],'text',
                0, 0, 0, 100, true, 'filemgmt');
        $c->add('numsubcategories2show', $_FM_DEFAULT['numSubCategories2Show'],'text',
                0, 0, 0, 110, true, 'filemgmt');
        $c->add('displayblocks', $_FM_DEFAULT['displayblocks'],'select',
                0, 0, 3, 115, true, 'filemgmt');
        $c->add('fm_access', NULL, 'fieldset', 0, 1, NULL, 0, true,
                'filemgmt');
        $c->add('selectpriv', $_FM_DEFAULT['selectpriv'],'select',
                0, 1, 0, 80, true, 'filemgmt');
        $c->add('uploadselect', $_FM_DEFAULT['uploadselect'],'select',
                0, 1, 0, 90, true, 'filemgmt');
        $c->add('uploadpublic', $_FM_DEFAULT['uploadpublic'],'select',
                0, 1, 0, 100, true, 'filemgmt');
        $c->add('fm_general', NULL, 'fieldset', 0, 2, NULL, 0, true,
                'filemgmt');
        $c->add('useshots', $_FM_DEFAULT['useshots'],'select',
                0, 2, 0, 10, true, 'filemgmt');
        $c->add('shotwidth', $_FM_DEFAULT['shotwidth'],'text',
                0, 2, 0, 20, true, 'filemgmt');
        $c->add('Emailoption', $_FM_DEFAULT['emailoption'],'select',
                0, 2, 0, 30, true, 'filemgmt');
        $c->add('enable_rating', 1,'select',
                0, 2, 0, 35, true, 'filemgmt');
        $c->add('silent_edit_default', 1,'select',
                0, 2, 0, 37, true, 'filemgmt');
        $c->add('FileStore', $_FM_DEFAULT['FileStore'], 'text',
                0, 2, 0, 40, true, 'filemgmt');
        $c->add('SnapStore', $_FM_DEFAULT['SnapStore'], 'text',
                0, 2, 0, 50, true, 'filemgmt');
        $c->add('SnapCat', $_FM_DEFAULT['SnapCat'], 'text',
                0, 2, 0, 60, true, 'filemgmt');
        $c->add('FileStoreURL', $_FM_DEFAULT['FileStoreURL'], 'text',
                0, 2, 0, 70, true, 'filemgmt');
        $c->add('FileSnapURL', $_FM_DEFAULT['FileSnapURL'], 'text',
                0, 2, 0, 80, true, 'filemgmt');
        $c->add('SnapCatURL', $_FM_DEFAULT['SnapCatURL'], 'text',
                0, 2, 0, 90, true, 'filemgmt');
        $c->add('outside_webroot', 0, 'select', 0, 2, 0, 100, true, 'filemgmt');
    }

    return true;
}
?>