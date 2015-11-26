<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | spanish_colombia_utf-8.php                                               |
// |                                                                          |
// | Spanish (Colombia) language file                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008-2015 by the following authors:                       |
// | John J. Toro A.        john DOT toro AT newroute DOT net                 |
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

$LANG_ST00 = array (
    'menulabel'         => 'Site Tailor',
    'plugin'            => 'sitetailor',
    'access_denied'     => 'Acceso Denegado',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin'             => 'Site Tailor Administration',
    'install_header'    => 'Site Tailor Install/Uninstall',
    'installed'         => 'Site Tailor esta Instalado',
    'uninstalled'       => 'Site Tailor No esta Instalado',
    'install_success'   => 'Site Tailor Plugin has successfully been installed.<br' . XHTML . '><br' . XHTML . '>Please review the system documentation and also visit the  <a href="%s">administration section</a> to ensure your settings correctly match the hosting environment.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Instalar',
    'uninstall'         => 'Desinstalar',
    'warning'           => 'Warning! Plugin is still Enabled',
    'enabled'           => 'Disable plugin before uninstalling.',
    'readme'            => 'Site Tailor Plugin Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/sitetailor/install_doc.html\">Install Document</a>",
    'thank_you'         => 'Thank you for upgrading to the latest release of Site Tailor. Please double check your System Configuration Options, there are many new features in this release that you may need to configure.',
    'support'           => 'For support, questions or enhancement requests, please visit <a href="http://www.gllabs.org">gl Labs</a>.  For the latest documentation, please visist the <a href="http://www.gllabs.org/wiki/">gl Labs Wiki</a>.',
    'success_upgrade'   => 'Site Tailor se Actualizo exitosamente',
    'template_cache'    => 'Template Cache Library Installed',
    'env_check'         => 'Environment Check',
    'gl_version_error'  => 'glFusion version is not v1.0.0 or higher',
    'gl_version_ok'     => 'glFusion version is v1.0.0 or higher',
    'tc_error'          => 'Caching Template Library is not installed',
    'tc_ok'             => 'Caching Template Library is installed',
    'ml_error'          => 'php.ini <strong>memory_limit</strong> is less than 48M.',
    'ml_ok'             => 'php.ini <strong>memory_limit</strong> is 48M or greater.',
    'recheck_env'       => 'Recheck Environment',
    'fix_install'       => 'Please fix the issues above before installing.',
    'need_cache'        => 'Site Tailor requires that you have the <a href="http://www.gllabs.org/filemgmt/index.php?id=156">Caching Template Library Enhancement</a> installed.  Please download and install the library.',
    'need_memory'       => 'Site Tailor recommends that you have at least 48M of memory configured for the <strong>memory_limit</strong> setting in php.ini.',
    'thank_you'         => 'Thank you for upgrading to the latest release of Site Tailor. Please double check your System Configuration Options, there are many new features in this release that you may need to configure.',
    'support'           => 'For support, questions or enhancement requests, please visit <a href="http://www.gllabs.org">gl Labs</a>.  For the latest documentation, please visist the <a href="http://www.gllabs.org/wiki/">Site Tailor Wiki</a>.',
    'success_upgrade'   => 'Site Tailor Successfully Upgraded',
    'overview'          => 'Site Tailor is a required Site Tailor CMS plugin that provides site customization options.',
    'preinstall_check'  => 'Site Tailor has the following requirements:',
    'glfusion_check'    => 'glFusion v1.0.0 or greater, version reported is <b>%s</b>.',
    'php_check'         => 'PHP v4.3.0 or greater, version reported is <b>%s</b>.',
    'preinstall_confirm' => "For full details on installing Site Tailor, please refer to the <a href=\"{$_CONF['site_admin_url']}/plugins/sitetailor/install_doc.html\">Installation Manual</a>.",
);

$LANG_ST01 = array (
    'instructions'      => 'Site Tailor allows you to easily customize your site logo and control the display of the site slogan.',
    'javascript_required' => 'Site Tailor Requires that you have JavaScript enabled.',
    'logo_options'      => 'Site Tailor Logo Options',
    'use_graphic_logo'  => 'Usar Logo Grafico',
    'use_text_logo'     => 'Usar Logo en Texto',
    'use_no_logo'       => 'No Mostrar Logo',
    'display_site_slogan'   => 'Mostrar Eslogan',
    'upload_logo'       => 'Cargar Nuevo Logo',
    'current_logo'      => 'Logo Actual',
    'no_logo_graphic'   => 'No Logo Graphic available',
    'logo_help'         => 'Uploaded graphic logo images are not resized, the standard size for Site Tailor logo is 100 pixels tall and should be less than 500 pixels wide.  You can upload larger images, but you will need to modify the site CSS in styles.css to ensure it displays properly.',
    'save'              => 'Guardar',
    'create_element'    => 'Nuevo',
    'add_new'           => 'Add New Menu Item',
    'add_newmenu'       => 'Nuevo',
    'edit_menu'         => 'Modificar Menú',
    'menu_list'         => 'Listado de Menús',
    'configuration'     => 'Configuración',
    'edit_element'      => 'Edit Menu Item',
    'menu_element'      => 'Opción',
    'menu_type'         => 'Tipo',
    'elements'          => 'Elementos',
    'enabled'           => 'Habilitado',
    'edit'              => 'Modificar',
    'delete'            => 'Borrar',
    'move_up'           => 'Subir',
    'move_down'         => 'Bajar',
    'order'             => 'Orden',
    'id'                => 'ID',
    'parent'            => 'Parent',
    'label'             => 'Menú',
    'elementlabel'      => 'Opción',
    'display_after'     => 'Mostrar Después de',
    'type'              => 'Tipo',
    'url'               => 'URL',
    'php'               => 'PHP Function',
    'coretype'          => 'glFusion Menú',
    'group'             => 'Grupo',
    'permission'        => 'Visible Para',
    'active'            => 'Activo',
    'top_level'         => 'Top Level Menu',
    'confirm_delete'    => 'Are you sure you want to delete this menu item?',
    'type_submenu'      => 'Sub-Menú',
    'type_url_same'     => 'Parent Window',
    'type_url_new'      => 'New Window with navigation',
    'type_url_new_nn'   => 'New Window without navigation',
    'type_core'         => 'glFusion Menu',
    'type_php'          => 'PHP Function',
    'gl_user_menu'      => 'User Menu',
    'gl_admin_menu'     => 'Admin Menu',
    'gl_topics_menu'    => 'Topics Menu',
    'gl_sp_menu'        => 'Static Pages Menu',
    'gl_plugin_menu'    => 'Plugin Menu',
    'gl_header_menu'    => 'Header Menu',
    'plugins'           => 'Complemento',
    'static_pages'      => 'Paginas',
    'glfusion_function' => 'Acción',
    'cancel'            => 'Cancelar',
    'action'            => 'Acción',
    'first_position'    => 'First Position',
    'info'              => 'Info',
    'non-logged-in'     => 'Non Logged-In Users Only',
    'target'            => 'URL Window',
    'same_window'       => 'Same Window',
    'new_window'        => 'New Window',
    'menu_color_options'    => 'Menu Color Options',
    'top_menu_bg'           => 'Main Menu BG',
    'top_menu_hover'        => 'Main Menu Hover',
    'top_menu_text'         => 'Main Menu Text',
    'top_menu_text_hover'   => 'Main Menu Text Hover / Sub Menu Text',
    'sub_menu_text_hover'   => 'Sub Menu Text Hover',
    'sub_menu_text'         => 'Sub Menu Text Color',
    'sub_menu_bg'           => 'Sub Menu BG',
    'sub_menu_hover_bg'     => 'Sub Menu Hover BG',
    'sub_menu_highlight'    => 'Sub Menu Highlight',
    'sub_menu_shadow'       => 'Sub Menu Shadow',
    'menu_builder'          => 'Menús',
    'logo'                  => 'Logo',
    'menu_colors'           => 'Menu Options',
    'options'               => 'Opciones',
    'menu_graphics'         => 'Menu Graphics',
    'graphics_or_colors'    => 'Usar Graficos ó Colores?',
    'graphics'              => 'Graficos',
    'colors'                => 'Colores',
    'menu_bg_image'         => 'Main Menu BG Image',
    'currently'             => 'Actualmente',
    'menu_hover_image'      => 'Main Menu Hover Image',
    'parent_item_image'     => 'Sub Menu Parent Indicator',
    'not_used'              => 'Not used if Use Graphics is selected below.',
	'select_color'			=> 'Select Color',
	'menu_alignment'		=> 'Alineación',
	'alignment_question'	=> 'Align the Menu to the',
	'align_left'			=> 'Left',
	'align_right'			=> 'Right',
	'blocks'                => 'Block Styles',
	'reset'                 => 'Reset Form',
	'defaults'              => 'Reset To Default Values',
	'confirm_reset'         => 'This will reset the menu colors and graphics to the installation values and automatically clear the Template Cache. Are you sure you want to continue? When done, make sure to clear your local browser cache as well.',
	'menu_properties'       => 'Menu Properties for',
	'disabled_plugin'       => 'Not found or disabled plugin',
	'clone'                 => 'Copiar',
	'clone_menu_label'      => 'Name for Cloned Menu',
    'topic'                 => 'Tópicos',
);

$LANG_HC = array (
    'main_menu_bg_color'         => 'Main Menu BG',
    'main_menu_hover_bg_color'   => 'Main Menu Hover',
    'main_menu_text_color'       => 'Main Menu Text',
    'main_menu_hover_text_color' => 'Main Menu Text Hover / Sub Menu Text',
    'submenu_hover_text_color'   => 'Sub Menu Text Hover',
    'submenu_background_color'   => 'Sub Menu BG',
    'submenu_hover_bg_color'     => 'Sub Menu Hover BG',
    'submenu_highlight_color'    => 'Sub Menu Highlight',
    'submenu_shadow_color'       => 'Sub Menu Shadow',
);
$LANG_HS = array (
    'main_menu_text_color'          => 'Text',
    'main_menu_hover_text_color'    => 'Hover',
    'submenu_highlight_color'       => 'Seperator',
);
$LANG_VC = array(
    'main_menu_bg_color'           => 'Menu BG',
    'main_menu_hover_bg_color'     => 'Menu BG Hover',
    'main_menu_text_color'         => 'Menu Text',
    'main_menu_hover_text_color'   => 'Text Hover',
    'submenu_text_color'           => 'Sub Menu Text Hover',
    'submenu_hover_text_color'     => 'Sub Menu Text Color',
    'submenu_highlight_color'      => 'Border',
);
$LANG_VS = array (
    'main_menu_text_color'          => 'Menu Text',
    'main_menu_hover_text_color'    => 'Menu Text Hover',
);

$LANG_ST_MENU_TYPES = array(
    1                   => 'Horizontal - Cascading',
    2                   => 'Horizontal - Simple',
    3                   => 'Vertical - Cascading',
    4                   => 'Vertical - Simple',
);

$LANG_ST_TYPES = array(
    1                   => 'Sub Menu',
    2                   => 'glFusion Action',
    3                   => 'glFusion Menu',
    4                   => 'Plugin',
    5                   => 'Static Page',
    6                   => 'External URL',
    7                   => 'PHP Function',
    8                   => 'Label',
    9                   => 'Topic',
);


$LANG_ST_TARGET = array(
    1                   => 'Parent Window',
    2                   => 'New Window with navigation',
    3                   => 'New Window without navigation',
);

$LANG_ST_GLFUNCTION = array(
    0                   => 'Inicio',
    1                   => 'Contribuir',
    2                   => 'Directory',
    3                   => 'Preferences',
    4                   => 'Buscar',
    5                   => 'Estadisticas',
);

$LANG_ST_GLTYPES = array(
    1                   => 'User Menu',
    2                   => 'Admin Menu',
    3                   => 'Topics Menu',
    4                   => 'Static Pages Menu',
    5                   => 'Plugin Menu',
    6                   => 'Header Menu',
);

$LANG_ST_ADMIN = array(
    1                   => 'Menu Builder allows you to create and edit menus for your site. To add a new menu, click the Create New Menu link above. To edit a menu\'s items, click the icon under the Elements column. To change the menu colors, click the icon under the Options column.',
    2                   => 'To create a new menu, specify a Menu Name and Menu type below. You can also set the active status, and what group of users will be able to see the menu, with the Active and Visible To fields.',
    3                   => 'Click on the icon under the Edit column to edit a menu item\'s properties. Arrange the items by moving them up or down with the arrows under the Order column.',
    4                   => 'To create a new menu element, specify its details and permissions below.',
    5                   => 'Once an element is created, you can always go back and edit its details and permissions below.',
    6                   => 'Menu Builder allows you to easily customize the look and feel of your menus. Adjust the values below to create a unique menu style.',
);

$PLG_sitetailor_MESSAGE1 = 'Site Tailor Logo Options Successfully Saved.';
$PLG_sitetailor_MESSAGE2 = 'Uploaded logo was not a JPG, GIF, or PNG image.';
$PLG_sitetailor_MESSAGE3 = 'There was a problem upgrading Site Tailor, please check the error log file.';
$PLG_sitetailor_MESSAGE4 = 'Logo exceeds the maximum allowed height or width.';
?>
