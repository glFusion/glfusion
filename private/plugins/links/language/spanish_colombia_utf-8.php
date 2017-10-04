<?php
###############################################################################
# spanish_colombia_utf-8.php
#
# This is the spanish (Colombia) language file for the glFusion Links Plugin
#
# Copyright (C) 2001 Tony Bibbs - tony AT tonybibbs DOT com
# Copyright (C) 2005 Trinity Bays - trinity93 AT gmail DOT com
# Copyright (C) 2014 John Toro
# john DOT toro AT newroute DOT net
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_LINKS = array(
    10 => 'Envíos',
    14 => 'Enlaces',
    84 => 'Enlaces',
    88 => 'No hay Enlaces nuevos',
    114 => 'Enlaces',
    116 => 'Agregar un Enlace',
    117 => 'Report Broken Link',
    118 => 'Broken Link Report',
    119 => 'The following link has been reported to be broken: ',
    120 => 'To edit the link, click here: ',
    121 => 'The broken Link was reported by: ',
    122 => 'Thank you for reporting this broken link. The administrator will correct the problem as soon as possible',
    123 => 'Gracias',
    124 => 'Ir',
    125 => 'Categorías',
    126 => 'Ud. esta aquí:',
    'root' => 'Enlaces/Vínculos',
    'error_header' => 'Link Submission Error',
    'verification_failed' => 'The URL specified does not appear to be a valid URL',
    'category_not_found' => 'The Category does not appear to be valid'
);

###############################################################################
# for stats

$LANG_LINKS_STATS = array(
    'links' => 'Links (Clicks) in the System',
    'stats_headline' => 'Enlaces más visitados',
    'stats_page_title' => 'Enlaces',
    'stats_hits' => 'Accesos',
    'stats_no_hits' => 'It appears that there are no links on this site or no one has ever clicked on one.'
);

###############################################################################
# for the search

$LANG_LINKS_SEARCH = array(
    'results' => 'Link Results',
    'title' => 'Título',
    'date' => 'Date Added',
    'author' => 'Enviado por',
    'hits' => 'Clicks'
);

###############################################################################
# for the submission form

$LANG_LINKS_SUBMIT = array(
    1 => 'Enviar un Enlace',
    2 => 'Enlace',
    3 => 'Categoría',
    4 => 'Otro',
    5 => 'Si otra, por favor especifique',
    6 => 'Error: No existe Categoría',
    7 => 'When selecting "Other" please also provide a categoría name',
    8 => 'Título',
    9 => 'URL',
    10 => 'Categoría',
    11 => 'Enlaces Enviados',
    12 => 'Enviado por'
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Thank-you for submitting a link to {$_CONF['site_name']}.  It has been submitted to our staff for approval.  If approved, your link will be seen in the <a href={$_CONF['site_url']}/links/index.php>links</a> section.";
$PLG_links_MESSAGE2 = 'Su enlace ha sido guardado exitosamente.';
$PLG_links_MESSAGE3 = 'The link has been successfully deleted.';
$PLG_links_MESSAGE4 = "Thank-you for submitting a link to {$_CONF['site_name']}.  You can see it now in the <a href={$_CONF['site_url']}/links/index.php>links</a> section.";
$PLG_links_MESSAGE5 = 'You do not have sufficient access rights to view this categoría.';
$PLG_links_MESSAGE6 = 'You do not have sufficient rights to edit this categoría.';
$PLG_links_MESSAGE7 = 'Please enter a Categoría Name and Description.';
$PLG_links_MESSAGE10 = 'Su Categoría ha sido guardado exitosamente.';
$PLG_links_MESSAGE11 = 'You are not allowed to set the id of a categoría to "site" or "user" - these are reserved for internal use.';
$PLG_links_MESSAGE12 = 'You are trying to make a parent categoría the child of it\'s own subcategory. This would create an orphan categoría, so please first move the child categoría or categories up to a higher level.';
$PLG_links_MESSAGE13 = 'The categoría has been successfully deleted.';
$PLG_links_MESSAGE14 = 'Categoría contains links and/or categories. Please remove these first.';
$PLG_links_MESSAGE15 = 'You do not have sufficient rights to delete this categoría.';
$PLG_links_MESSAGE16 = 'No such categoría exists.';
$PLG_links_MESSAGE17 = 'This categoría id is already in use.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/plugins/links/index.php

$LANG_LINKS_ADMIN = array(
    1 => 'Editor de Enlaces',
    2 => 'ID',
    3 => 'Título',
    4 => 'Sitio Web',
    5 => 'Categoría',
    6 => '(<i>incluya: <b>http://</b></i>)',
    7 => 'Otra',
    8 => 'Accesos',
    9 => 'Descripción',
    10 => 'Debes indicar un Título, URL y Descripción.',
    11 => 'Listado de Enlaces',
    12 => 'Para modificar ó borrar, haz clic sobre el icono: <img src="/layout//images/admin/edit.png" /> correspondiente. Para crear, haz clic en la opción: "<b>Crear</b>" arriba.',
    14 => 'Categoría',
    16 => 'Acceso Denegado',
    17 => "Estas tratando de acceder a un enlace al que no tienes derecho. Este intento ha sido registrado. Regresa a la <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">administración de enlaces</a>.",
    20 => 'Si otra, especifique',
    21 => 'guardar',
    22 => 'cancelar',
    23 => 'borrar',
    24 => 'Enlace no encontrado',
    25 => 'El enlace que seleccionaste para modificación no pudo ser encontrado.',
    26 => 'Validar',
    27 => 'Estado HTML',
    28 => 'Modificar categoría',
    29 => 'Escriba ó modifique los detalles a continuación.',
    30 => 'Sub-Categoría',
    31 => 'Descripción',
    32 => 'ID',
    33 => 'Tema',
    34 => 'Categoría',
    35 => 'Todo',
    40 => 'Modificar esta categoría',
    41 => 'Crear Sub-Categoría',
    42 => 'Borrar esta Categoría',
    43 => 'Site categorías',
    44 => 'Agregar Sub',
    46 => 'User %s tried to delete a categoría to which they do not have access rights',
    50 => 'Categorías',
    51 => 'Crear',
    52 => 'Crear',
    53 => 'Enlaces',
    54 => 'Listado de Categorías',
    55 => 'Edit categories below. Note that you cannot delete a categoría that contains other categories or links - you should delete these first, or move them to another categoría.',
    56 => 'Editor de Categorías',
    57 => 'No validado aun',
    58 => 'Validar ahora',
    59 => '<p>Para validar todos los enlaces mostrados, haga click en el enlace: "<b>Validar ahora</b>". Esto podrá tomar un tiempo considerable dependiendo de la cantdad de enalces mostrados.</p>',
    60 => 'User %s tried illegally to edit categoría %s.',
    61 => 'Propietario',
    62 => 'Actualizado',
    63 => '¿Seguro quieres borrar este enlace?',
    64 => '¿Seguro quieres borrar esta categoría?',
    65 => 'Moderar Enlace',
    66 => 'Esta pantalla te permite crear / modificar Enlaces.',
    67 => 'Esta pantalla te permite crear / modificar una Categoría de Enlaces.'
);


$LANG_LINKS_STATUS = array(
    100 => 'Continuar',
    101 => 'Switching Protocols',
    200 => 'Aceptar',
    201 => 'Creado',
    202 => 'Aceptado',
    203 => 'Non-Authoritative Information',
    204 => 'Sin Contenido',
    205 => 'Reset Content',
    206 => 'Contenido Parcial',
    300 => 'Multiple Choices',
    301 => 'Movido Permanentemente',
    302 => 'Encontrado',
    303 => 'Ver Otro',
    304 => 'No Modificado',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Pago Requerido',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflicto',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    500 => 'Error Interno del Servidor',
    501 => 'No Implementado',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    999 => 'Connection Timed out'
);

$LANG_LI_AUTOTAG = array(
    'desc_link' => 'Link: to the detail page for a Link on this site; link_text defaults to the link name. usage: [link:<i>link_id</i> {link_text}]'
);

// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Enlaces',
    'title' => 'Configuración Enlaces'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => '¿Requerir Inicio de Sesión?',
    'linksubmission' => '¿Permitir cola de Envios?',
    'newlinksinterval' => 'Intervalo (<i>seg</i>) para ser Nuevo',
    'hidenewlinks' => '¿Ocultar enlace Nuevos?',
    'hidelinksmenu' => '¿Ocultar entrada de Enlaces en el Menú?',
    'linkcols' => 'Categorías por Columna',
    'linksperpage' => 'Enlaces por Pagina',
    'show_top10' => '¿Mostrar los 10 Más?',
    'notification' => '¿Notificar por E-mail?',
    'delete_links' => '¿Borrar Enlaces con Propietario?',
    'aftersave' => 'Después de Guardar',
    'show_category_descriptions' => 'Mostrar la Descripción de la Categoría ?',
    'root' => 'ID de la Categoría Raíz',
    'default_permissions' => 'Permisos Predeterminados',
    'target_blank' => 'Abrir Enlaces en una Ventana Nueva',
    'displayblocks' => 'Mostrar Bloques'
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Principal'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Publico',
    'fs_admin' => 'Administración',
    'fs_permissions' => 'Permisos'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['links'] = array(
    0 => array('Sí' => 1, 'No' => 0),
    1 => array('Sí' => true, 'No' => false),
    9 => array('Ir al sitio del Enlace' => 'item', 'Ir a la Lista' => 'list', 'Ir a la Lista Publica' => 'plugin', 'Ir al Inicio' => 'home', 'Ir a la Administración' => 'admin'),
    12 => array('Sin Acceso' => 0, 'Solo Lectura' => 2, 'Lectura-Escritura' => 3),
    13 => array('Izquierda' => 0, 'Derecha' => 1, 'Izquierda & Derecha' => 2, 'Ninguno' => 3)
);

?>