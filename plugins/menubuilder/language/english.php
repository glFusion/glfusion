<?php
// +--------------------------------------------------------------------------+
// | gl Labs Menu Builder Plugin 1.0                                          |
// +--------------------------------------------------------------------------+
// | $Id:                                                                    $|
// | This is the English language page for the gl Labs Menu Builder Plugin    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans              - mark at gllabs.org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | If you translate this file, please consider uploading a copy at          |
// |    http://www.gllabs.org so others can benefit from your                 |
// |    translation.  Thank you!                                              |
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

$LANG_MB00 = array (
    'menulabel'         => 'Menu Builder',
    'plugin'            => 'Menu Builder',
    'access_denied'     => 'Access Denied',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin'             => 'Menu Builder Administration',
    'install_header'    => 'Menu Builder Install/Uninstall',
    'installed'         => 'Menu Builder is Installed',
    'uninstalled'       => 'Menu Builder is Not Installed',
    'install_success'   => 'Menu Builder Plugin has successfully been installed.<br' . XHTML . '><br' . XHTML . '>Please review the system documentation and also visit the  <a href="%s">administration section</a> to ensure your settings correctly match the hosting environment.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Install',
    'uninstall'         => 'UnInstall',
    'warning'           => 'Warning! Plugin is still Enabled',
    'enabled'           => 'Disable plugin before uninstalling.',
    'readme'            => 'Menu Builder Plugin Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/menubuilder/install_doc.html\">Install Document</a>",
    'thank_you'         => 'Thank you for upgrading to the latest release of Menu Builder. Please double check your System Configuration Options, there are many new features in this release that you may need to configure.',
    'support'           => 'For support, questions or enhancement requests, please visit <a href="http://www.gllabs.org">gl Labs</a>.  For the latest documentation, please visist the <a href="http://www.gllabs.org/wiki/doku.php?id=mediagallery16:start">gl Labs Wiki</a>.',
    'success_upgrade'   => 'Media Gallery Successfully Upgraded',
    'template_cache'    => 'Template Cache Library Installed',
    'env_check'         => 'Environment Check',
    'gl_version_error'  => 'Geeklog version is not v1.4.1 or higher',
    'gl_version_ok'     => 'Geeklog version is v1.4.1 or higher',
    'tc_error'          => 'Caching Template Library is not installed',
    'tc_ok'             => 'Caching Template Library is installed',
    'ml_error'          => 'php.ini <strong>memory_limit</strong> is less than 48M.',
    'ml_ok'             => 'php.ini <strong>memory_limit</strong> is 48M or greater.',
    'recheck_env'       => 'Recheck Environment',
    'fix_install'       => 'Please fix the issues above before installing.',
    'need_cache'        => 'Menu Builder requires that you have the <a href="http://www.gllabs.org/filemgmt/index.php?id=156">Caching Template Library Enhancement</a> installed.  Please download and install the library.',
    'need_memory'       => 'Menu Builder recommends that you have at least 48M of memory configured for the <strong>memory_limit</strong> setting in php.ini.',
    'thank_you'         => 'Thank you for upgrading to the latest release of Menu Builder. Please double check your System Configuration Options, there are many new features in this release that you may need to configure.',
    'support'           => 'For support, questions or enhancement requests, please visit <a href="http://www.gllabs.org">gl Labs</a>.  For the latest documentation, please visist the <a href="http://www.gllabs.org/wiki/doku.php?id=menubuilder:start">Menu Builder Wiki</a>.',
    'success_upgrade'   => 'Menu Builder Successfully Upgraded',
    'overview'          => 'Menu Builder is a native Geeklog plugin that provides a full featured menu management system.',
    'preinstall_check'  => 'Menu Builder has the following requirements:',
    'geeklog_check'     => 'Geeklog v1.4.0 or greater, version reported is <b>%s</b>.',
    'php_check'         => 'PHP v4.3.0 or greater, version reported is <b>%s</b>.',
    'preinstall_confirm' => "For full details on installing Menu Builder, please refer to the <a href=\"{$_CONF['site_admin_url']}/plugins/menubuilder/install_doc.html\">Installation Manual</a>.",
);

$LANG_MB01 = array(
    'create_element'    => 'Create Menu Element',
    'add_new'           => 'Add New Menu Item',
    'menu_list'         => 'Menu Listing',
    'configuration'     => 'Configuration',
    'edit_element'      => 'Edit Menu Item',
    'menu_element'      => 'Menu Element',
    'enabled'           => 'Enabled',
    'edit'              => 'Edit',
    'delete'            => 'Delete',
    'move_up'           => 'Move Up',
    'move_down'         => 'Move Down',
    'order'             => 'Order',
    'id'                => 'ID',
    'parent'            => 'Parent',
    'label'             => 'Label',
    'display_after'     => 'Display After',
    'type'              => 'Type',
    'url'               => 'URL',
    'php'               => 'PHP Function',
    'coretype'          => 'Geeklog Menu',
    'group'             => 'Group',
    'permission'        => 'Visible To',
    'active'            => 'Active',
    'top_level'         => 'Top Level Menu',
    'confirm_delete'    => 'Are you sure you want to delete this menu item?',
    'type_submenu'      => 'Sub Menu',
    'type_url_same'     => 'Parent Window',
    'type_url_new'      => 'New Window with navigation',
    'type_url_new_nn'   => 'New Window without navigation',
    'type_core'         => 'Geeklog Menu',
    'type_php'          => 'PHP Function',
    'gl_user_menu'      => 'User Menu',
    'gl_admin_menu'     => 'Admin Menu',
    'gl_topics_menu'    => 'Topics Menu',
    'gl_sp_menu'        => 'Static Pages Menu',
    'gl_plugin_menu'    => 'Plugin Menu',
    'gl_header_menu'    => 'Header Menu',
    'plugins'           => 'Plugin',
    'static_pages'      => 'Static Pages',
    'geeklog_function'  => 'Geeklog Function',
    'save'              => 'Save',
    'cancel'            => 'Cancel',
    'action'            => 'Action',
    'first_position'    => 'First Position',
);

$LANG_MB_TYPES = array(
    1                   => 'Sub Menu',
    2                   => 'Geeklog Action',
    3                   => 'Geeklog Menu',
    4                   => 'Plugin',
    5                   => 'Static Page',
    6                   => 'External URL',
    7                   => 'PHP Function',
);


$LANG_MB_TARGET = array(
    1                   => 'Parent Window',
    2                   => 'New Window with navigation',
    3                   => 'New Window without navigation',
);

$LANG_MB_GLFUNCTION = array(
    0                   => 'Home',
    1                   => 'Contribute',
    2                   => 'Directory',
    3                   => 'Preferences',
    4                   => 'Search',
    5                   => 'Site Stats',
);

$LANG_MB_GLTYPES = array(
    1                   => 'User Menu',
    2                   => 'Admin Menu',
    3                   => 'Topics Menu',
    4                   => 'Static Pages Menu',
    5                   => 'Plugin Menu',
    6                   => 'Header Menu',
);
?>