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
    2 => 'Lo siento, no hay eventos para mostrar',
    3 => 'Cuando',
    4 => 'Donde',
    5 => 'Descripción',
    6 => 'Agregar un Evento',
    7 => 'Próximos Eventos',
    8 => 'Al añadir este evento a tu calendario puedes ver rápidamente solo los eventos de tu interés haciendo clic en "Mi Calendario" de las Funciones de Usuario.',
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
    23 => 'Tus Eventos',
    24 => 'Eventos del sitio',
    25 => 'No hay eventos cercanos',
    26 => 'Enviar un Evento',
    27 => "Al enviar un evento a <b>{$_CONF['site_name']}</b> este sera publicado en el Calendario Público, donde los usuarios pueden, opcionalmente, agregarlo a sus calendarios personales. Esta caracteristica <b>NO</b> es para almacenar sus eventos personales tales como cupleaños y aniversarios.<br" . XHTML . "><br" . XHTML . ">Una vez enviado será evaluado por los administradores y si es aprobado, será publicado en el calendario público.<br" . XHTML . "><br" . XHTML . ">",
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
    46 => 'Eventos en el sistema',
    47 => 'Los 10 Eventos',
    48 => 'Accesos',
    49 => 'Parece que no hay eventos en el sitio o que nadie ha hecho clic en uno.',
    50 => 'Eventos',
    51 => 'Borrar',
    52 => 'Enviado por'
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Resultados (Calendario)',
    'title' => 'Título',
    'date_time' => 'Fecha & Hora',
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
    35 => 'Adición Rápida',
    36 => 'Enviar',
    37 => 'Lo siento, la función de agenda personal no esta activa',
    38 => 'Editor de Eventos Personales',
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
    9 => '(incluye: <b>http://</b>)',
    10 => 'Debes proporcionar las fechas / horas, título del evento, y la descripción',
    11 => 'Calendario - Listado de Eventos',
    12 => 'Para modificar ó borrar, haz clic sobre el icono: <img src="/layout/'.$_CONF['theme'].'/images/admin/edit.png" /> o <img src="/layout/'.$_CONF['theme'].'/images/admin/delete.png" /> correspondiente. Para crear, selecciona la opción: "<b><i>Crear</i></b>" arriba. Para crear una copia de un evento existente haz clic en el icono: <img src="/layout/'.$_CONF['theme'].'/images/admin/copy.png" /> correspondiente.',
    13 => 'Autor',
    14 => 'Inicia',
    15 => 'Termina',
    16 => '',
    17 => "Estas intentando acceder a un evento al que no tienes derecho. Este intento se ha registrado. <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">Regresa a la administración de eventos</a>.",
    18 => '',
    19 => '',
    20 => 'guardar',
    21 => 'cancelar',
    22 => 'borrar',
    23 => 'Fecha inicial incorrecta.',
    24 => 'Fecha final incorrecta.',
    25 => 'La Fecha final es anterior a la fecha inicial.',
    26 => 'Borrar Eventos Antiguos',
    27 => 'Estos son los eventos que tienen mas de ',
    28 => ' meses. Actualiza al periodo de tiempo deseado, y luego haz clic en el boton: "<b><i>Actualizar</i></b>". Selecciona uno o mas eventos de los resultados que se muestran, y luego haz clic en el icono: <img src="/layout/'.$_CONF['theme'].'/images/admin/delete.png" /> al final para borrarlos. Solo se borraran los eventos mostrados y seleccionados en esta pagina.',
    29 => '',
    30 => 'Actualizar',
    31 => '¿Seguro deseas borrar permanentemente Todos los usuarios seleccionados?',
    32 => 'Listar todos',
    33 => 'No hay eventos seleccionado para ser borrados',
    34 => 'ID',
    35 => 'no se pudo borrar',
    36 => 'Borrado exitosamente',
    37 => 'Evento Moderado',
    38 => 'Gestión Múltiple',
    39 => 'Eventos',
    40 => 'Eventos',
    41 => 'Esta pantalla te permite modificar/crear eventos. Modifica los campos a continuación y guarda.',
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Enlace: a un evento del Calendario; texto de enlace por defecto  al titulo del evento: [calendar:<i>event_id</i> {link_text}]',
);

$LANG_CAL_MESSAGE = array(
    'save' => 'El evento se ha guardado correctamente.',
    'delete' => 'El evento se ha borrado correctamente.',
    'private' => 'El evento ha sido guardado en tu calendario',
    'login' => 'No se puede abrir tu calendario personal hasta que inicies sesión',
    'removed' => 'El evento ha sido borrado exitosamente de tu calendario personal',
    'noprivate' => 'Lo siento, no están habilitados lo calendarios personales',
    'unauth' => 'Lo siento, no tienes acceso a la administración de eventos. Ten en cuenta que todos los intentos de acceso no autorizados se registran',
    'delete_confirm' => '¿Seguro deseas borrar este evento?'
);

$PLG_calendar_MESSAGE4 = "Gracias por enviar un evento a {$_CONF['site_name']}. Este ha sido remitido a nuestro personal para su aprobación. Si es aprobado, tu evento se vera aquí, en nuestra sección de <a href=\"{$_CONF['site_url']}/calendar/index.php\">calendario</a>.";
$PLG_calendar_MESSAGE17 = 'El evento se ha guardado correctamente.';
$PLG_calendar_MESSAGE18 = 'El evento se ha borrado correctamente.';
$PLG_calendar_MESSAGE24 = 'El evento ha sido guardado en tu calendario.';
$PLG_calendar_MESSAGE26 = 'El evento ha sido borrado exitosamente.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Actualización de extensión no suportada.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Calendario',
    'title' => 'Calendario - Configuración'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => '¿Inicio de Sesión Requerido?',
    'hidecalendarmenu' => '¿Ocultar del Menú?',
    'personalcalendars' => '¿Permitir Calendarios Personales?',
    'eventsubmission' => '¿Permitir Cola de Envios?',
    'showupcomingevents' => '¿Mostrar próximos Eventos?',
    'upcomingeventsrange' => 'Rango para Próximos Eventos',
    'event_types' => 'Tipos de Eventos',
    'hour_mode' => 'Modo de Hora',
    'notification' => '¿Notificación por E-mail?',
    'delete_event' => '¿Borrar Eventos con Propietario?',
    'aftersave' => 'Después de Guardar un Evento',
    'default_permissions' => 'Permisos por defecto de Evento',
    'only_admin_submit' => 'Solo Permitir Envío de Administradores',
    'displayblocks' => 'Mostrar los Bloques glFusion',
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'General',
    'fs_permissions' => 'Permisos'
);

// Note: entries 0, 1, 6, 9, 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['calendar'] = array(
    0 => array('Sí' => 1, 'No' => 0),
    1 => array('Sí' => true, 'No' => false),
    6 => array('12' => 12, '24' => 24),
    9 => array('Ir al Evento' => 'item', 'Ir a la lista' => 'list', 'Ir al Calendario' => 'plugin', 'Ir al Inicio' => 'home', 'Ir al Panel de Control' => 'admin'),
    12 => array('Sin acceso' => 0, 'Solo-Lectura' => 2, 'lectura-Escritura' => 3),
    13 => array('Izquierda' => 0, 'Derecha' => 1, 'Izquierda & Derecha' => 2, 'Ninguno' => 3)
);

?>