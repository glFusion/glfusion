<?php
###############################################################################
# spanish_colombia_utf-8.php
#
# This is the Spanish (Colombia) language file for the glFusion Calendar plugin
#
# Copyright (C) 2001 Tony Bibbs - tony AT tonybibbs DOT com
# Copyright (C) 2005 Trinity Bays - trinity93 AT gmail DOT com
# Copyright (C) 2015 John Toro
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
    die ('This file can not be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

# index.php
$LANG_CAL_1 = array(
    1 => 'Calendario de Eventos',
    2 => 'I\'m sorry, there are no events to display.',
    3 => 'Cuando',
    4 => 'Donde',
    5 => 'Descripción',
    6 => 'Agregar un Evento',
    7 => 'Próximos Eventos',
    8 => 'By adding this event to your calendar you can quickly view only the events you are interested in by clicking "My Calendar" from the User Functions area.',
    9 => 'Agregar a Mi Calendario',
    10 => 'Borrar de Mi Calendario',
    11 => 'Agregando Evento al Calendario de %s',
    12 => 'Evento',
    13 => 'Inicia',
    14 => 'Termina',
    15 => 'Regresar a Calendario',
    16 => 'Calendario',
    17 => 'Inicia',
    18 => 'Termina',
    19 => 'EVENTOS:',
    20 => 'Título',
    21 => 'Inicia',
    22 => 'URL',
    23 => 'Your Events',
    24 => 'Site Events',
    25 => 'No hay eventos cercanos',
    26 => 'Enviar un Evento',
    27 => "Al enviar un evento a <b>{$_CONF['site_name']}</b> este sera publicado en el Calendario Público, donde los usuarios pueden, opcionalmente, agregarlo a sus calendarios personales. Esta caracteristica <b>NO</b> es para almacenar sus eventos personales tales como cupleaños y aniversarios.<br" . XHTML . "><br" . XHTML . ">Una vez enviado será evaluado por los administradores y si es aprovado, será publicado en el calendario público.<br" . XHTML . "><br" . XHTML . ">",
    28 => 'Título',
    29 => 'Hora',
    30 => 'Hora',
    31 => 'Todo el Día',
    32 => 'Dirección, Linea 1',
    33 => 'Dirección, Linea 2',
    34 => 'Ciudad/Municipio',
    35 => 'Dpto./Estado',
    36 => 'Cód. Postal',
    37 => 'Tipo',
    38 => 'Edit Tipos de Eventos',
    39 => 'Lugar',
    40 => 'Agregar Evento a',
    41 => 'Calendario',
    42 => 'Calendario Personal',
    43 => 'Enlace',
    44 => 'No se permiten etiquetas HTML',
    45 => 'Enviar',
    46 => 'Events in the system',
    47 => 'Top Ten Events',
    48 => 'Accesos',
    49 => 'It appears that there are no events on this site or no one has ever clicked on one.',
    50 => 'Eventos',
    51 => 'Borrar',
    52 => 'Enviado por'
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Resultados (Calendario)',
    'title' => 'Título',
    'date_time' => 'Date & Time',
    'location' => 'Lugar',
    'description' => 'Descripción'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Agregar un Evento Personal',
    9 => 'Evento de %s',
    10 => 'Eventos para',
    11 => 'Calendario',
    12 => 'Mi Calendario',
    25 => 'Regresar a ',
    26 => 'Todo el Día',
    27 => 'Semana',
    28 => 'Calendario Personal para',
    29 => 'Calendario Público',
    30 => 'borrar evento',
    31 => 'Agregar',
    32 => 'Evento',
    33 => 'Fecha',
    34 => 'Hora',
    35 => 'Quick Add',
    36 => 'Enviar',
    37 => 'Sorry, the personal calendar feature is not enabled on this site',
    38 => 'Personal Event Editor',
    39 => 'Día',
    40 => 'Semana',
    41 => 'Mes',
    42 => 'Agregar Evento',
    43 => 'Eventos Enviados'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Editor de Eventos',
    2 => 'Error',
    3 => 'Formato',
    4 => 'Sitio Web',
    5 => 'Inicia',
    6 => 'Termina',
    7 => 'Lugar',
    8 => 'Descripción',
    9 => '(incluya: <b>http://</b>)',
    10 => 'You must provide the dates/times, event title, and description',
    11 => 'Calendario - Listado de Eventos',
    12 => 'Para modificar ó borrar, haga click sobre el icono: <img src="/layout//images/edit.png" /> correspondiente. Para crear, seleccione: "<b><i>Nuevo</i></b>" arriba. Para crear una copia de un evento existente haga clic en el icono: <img src="/layout/cory/images/copy.png" /> correspondiente.',
    13 => 'Autor',
    14 => 'Inicia',
    15 => 'Termina',
    16 => '',
    17 => "You are trying to access an event that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">go back to the event administration screen</a>.",
    18 => '',
    19 => '',
    20 => 'guardar',
    21 => 'cancelar',
    22 => 'borrar',
    23 => 'Bad start date.',
    24 => 'Bad end date.',
    25 => 'End date is before start date.',
    26 => 'Borrar Eventos Antiguos',
    27 => 'Eso son los eventos que tinenen mas de ',
    28 => ' meses. Haga clic en el icono: <img src="/layout/cory/images/deleteitem.png" /> al final para borrarlos, ó seleccione un periodo de tiempo diferente:<br' . XHTML . '>Encontrar todos los eventos con mas de: ',
    29 => '',
    30 => 'Actualizar',
    31 => 'Are You sure you want to permanently delete ALL selected users?',
    32 => 'Listar todos',
    33 => 'No events selected for deletion',
    34 => 'Event ID',
    35 => 'could not be deleted',
    36 => 'Sucessfully deleted',
    37 => 'Moderate Event',
    38 => 'Gestión Multiple',
    39 => 'Eventos',
    40 => 'Event List',
    41 => 'This screen allows you to edit / create events. Edit the fields below and save.'
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: to a Calendar event on this site; link_text defaults to event title: [calendar:<i>event_id</i> {link_text}]'
);

$LANG_CAL_MESSAGE = array(
    'save' => 'El evento se ha guardado correctamente.',
    'delete' => 'El evento se ha borrado correctamente.',
    'private' => 'The event has been saved to your calendar',
    'login' => 'Cannot open your personal calendar until you login',
    'removed' => 'Event was successfully removed from your personal calendar',
    'noprivate' => 'Sorry, personal calendars are not enabled on this site',
    'unauth' => 'Sorry, you do not have access to the event administration page.  Please note that all attempts to access unauthorized features are logged',
    'delete_confirm' => 'Are you sure you want to delete this event?'
);

$PLG_calendar_MESSAGE4 = "Thank-you for submitting an event to {$_CONF['site_name']}.  It has been submitted to our staff for approval.  If approved, your event will be seen here, in our <a href=\"{$_CONF['site_url']}/calendar/index.php\">calendar</a> section.";
$PLG_calendar_MESSAGE17 = 'El evento se ha guardado correctamente.';
$PLG_calendar_MESSAGE18 = 'El evento se ha borrado correctamente.';
$PLG_calendar_MESSAGE24 = 'The event has been saved to your calendar.';
$PLG_calendar_MESSAGE26 = 'The event has been successfully deleted.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Calendar',
    'title' => 'Calendar Configuration'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Calendar Login Required?',
    'hidecalendarmenu' => 'Hide Calendar Menu Entry?',
    'personalcalendars' => 'Enable Personal Calendars?',
    'eventsubmission' => 'Enable Submission Queue?',
    'showupcomingevents' => 'Show upcoming Events?',
    'upcomingeventsrange' => 'Upcoming Events Range',
    'event_types' => 'Event Types',
    'hour_mode' => 'Hour Mode',
    'notification' => 'Notification Email?',
    'delete_event' => 'Delete Events with Owner?',
    'aftersave' => 'After Saving Event',
    'default_permissions' => 'Event Default Permissions',
    'only_admin_submit' => 'Only Allow Admins to Submit',
    'displayblocks' => 'Display glFusion Blocks'
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'General Calendar Settings',
    'fs_permissions' => 'Default Permissions'
);

// Note: entries 0, 1, 6, 9, 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['calendar'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    6 => array('12' => 12, '24' => 24),
    9 => array('Forward to Event' => 'item', 'Display Admin List' => 'list', 'Display Calendar' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>