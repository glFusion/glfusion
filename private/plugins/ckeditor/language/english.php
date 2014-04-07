<?php
// +--------------------------------------------------------------------------+
// | CKEditor Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | english.php                                                              |
// |                                                                          |
// | English language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014 by the following authors:                             |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$LANG_CK00 = array (
    'menulabel'         => 'CKEditor',
    'plugin'            => 'ckeditor',
    'access_denied'     => 'Access Denied',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin'             => 'CKEditor Administration',
    'install_header'    => 'CKEditor Plugin Install/Uninstall',
    'installed'         => 'CKEditor is Installed',
    'uninstalled'       => 'CKEditor is Not Installed',
    'install_success'   => 'CKEditor Installation Successful.  <br /><br />Please review the system documentation and also visit the  <a href="%s">administration section</a> to insure your settings correctly match the hosting environment.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Install',
    'uninstall'         => 'UnInstall',
    'warning'           => 'Warning! Plugin is still Enabled',
    'enabled'           => 'Disable plugin before uninstalling.',
    'readme'            => 'CKEditor Plugin Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Install Document</a>",
    'overview'          => 'CKEditor is a native glFusion plugin that provides WYSIWYG editor capabilities.',
    'details'           => 'The CKEditor plugin will provide wysiwyg editor features to your site.',
    'preinstall_check'  => 'CKEditor has the following requirements:',
    'glfusion_check'    => 'glFusion v1.3.0 or greater, version reported is <b>%s</b>.',
    'php_check'         => 'PHP v5.2.0 or greater, version reported is <b>%s</b>.',
    'preinstall_confirm'=> "For full details on installing CKEditor, please refer to the <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Installation Manual</a>.",
    'visual'            => 'Visual',
    'html'              => 'HTML',
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label'                 => 'CKEditor',
    'title'                 => 'CKEditor Configuration'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Enable Comment',
    'enable_story'          => 'Enable Story',
    'enable_submitstory'    => 'Enable User Story Contribute',
    'enable_contact'        => 'Enable Contact',
    'enable_emailstory'     => 'Enable Email Story',
    'enable_sp'             => 'Enable Pages Editor Support',
    'enable_block'          => 'Enalbe Block Editor',
);
$LANG_configsubgroups['ckeditor'] = array(
    'sg_main'               => 'Configuration Settings'
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'General Settings',
    'ck_integration'            => 'CKEditor Integration',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['ckeditor'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
);

$PLG_ckeditor_MESSAGE1 = 'CKEditor plugin upgrade: Update completed successfully.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor plugin upgrade failed - check error.log';
$PLG_ckeditor_MESSAGE3 = 'CKEditor Plugin Successfully Installed';
?>