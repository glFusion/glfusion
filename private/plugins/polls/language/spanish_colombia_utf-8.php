<?php

###############################################################################
# spanish_colombia_utf-8.php
#
# This is the Spanish (Colombia) language file for the glFusion Polls plugin
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
    die ('This file can not be used on its own.');
}

global $LANG32;

$LANG_POLLS = array(
    'polls' => 'Encuestas',
    'results' => 'Resultados',
    'pollresults' => 'Resultados de la encuesta',
    'votes' => 'votos',
    'vote' => 'Votar',
    'pastpolls' => 'Encuestas anteriores',
    'savedvotetitle' => 'Voto guardado',
    'savedvotemsg' => 'Se ha guardado tu voto para la encuesta',
    'pollstitle' => 'Encuestas disponibles',
    'polltopics' => 'Otras encuestas',
    'stats_top10' => '10 encuestas principales',
    'stats_topics' => 'Tema',
    'stats_votes' => 'Votos',
    'stats_none' => 'Parece no haber encuestas o que nadie ha votado.',
    'stats_summary' => 'Encuestas (Respuestas) en el sistema',
    'open_poll' => 'Abierto para votar',
    'answer_all' => 'Please answer all remaining questions',
    'not_saved' => 'Resultados no guardados',
    'upgrade1' => 'You installed a new version of the Polls plugin. Please',
    'upgrade2' => 'actualizar',
    'editinstructions' => 'Debes indicar el ID, y al menos una pregunta y dos respuestas.',
    'pollclosed' => 'This poll is closed for voting.',
    'pollhidden' => 'Poll results will be available only after the Poll has closed.',
    'start_poll' => 'Iniciar Encuesta',
    'deny_msg' => 'Access to this poll is denied.  Either the poll has been moved/removed or you do not have sufficient permissions.',
    'login_required' => "<a href=\"{$_CONF['site_url']}/users.php\" rel=\"nofollow\">Login</a> required to vote",
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Comentarios',
    2 => 'Entra un tema, al menos una pregunta y al menos una respuesta para la pregunta.',
    3 => 'Creada',
    4 => 'Encuesta %s guardada',
    5 => 'Editor de Encuestas',
    6 => 'ID',
    7 => '(<i>NO uses espacios</i>)',
    8 => 'Mostrar en el Bloque de Encuestas',
    9 => 'Encuesta',
    10 => 'Respuestas / Votos / Comentarios',
    11 => 'There was an error getting poll answer data about the poll %s',
    12 => 'There was an error getting poll question data about the poll %s',
    13 => 'Crear Encuesta',
    14 => 'guardar',
    15 => 'cancelar',
    16 => 'borrar',
    17 => 'Please enter a Poll ID',
    18 => 'Listado de Encuestas',
    19 => 'Para modificar ó borrar, haz clic sobre el icono: <img src="/layout/'.$_CONF['theme'].'/images/admin/edit.png" /> o <img src="/layout/'.$_CONF['theme'].'/images/admin/delete.png" /> correspondiente. Para crear, haz clic sobre el enlace: "<b><i>Crear</i></b>" arriba.',
    20 => 'Votos',
    21 => 'Acceso Negado',
    22 => "You are trying to access a poll that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/poll.php\">go back to the poll administration screen</a>.",
    23 => 'New Poll',
    24 => 'Admin Home',
    25 => 'Sí',
    26 => 'No',
    27 => 'Modificar',
    28 => 'Enviar',
    29 => 'Search',
    30 => 'Limit Results',
    31 => 'Pregunta',
    32 => 'To remove this question from the poll, remove its question text',
    33 => 'Abierta',
    34 => 'Tema:',
    35 => 'Esta encuesta tiene',
    36 => 'preguntas más.',
    37 => 'Ocultar resultados mientras este abierta',
    38 => 'Mientras la encuesta esta abierta, solo el propietario &amp; root pueden ver los resultados',
    39 => 'El tema solo será mostrado si tiene almenos 1 pregunta.',
    40 => 'Ver todas las respuestas a esta Encuesta',
    41 => '¿Realmente deseas borrar esta Encuesta?',
    42 => 'Are you absolutely sure you want to delete this Poll?  All questions, answers and comments that are associated with this Poll will also be permanently deleted from the database.',
    43 => 'Requiere Inicio de Sesión para Votar',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll' => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result' => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote' => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Tu encuesta se guardó satisfactoriamente.';
$PLG_polls_MESSAGE20 = 'Tu encuesta se ha borrado satisfactoriamente.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_polls_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Encuestas',
    'title' => 'Configuración Encuestas'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => '¿Requiere Inicio de Sesión?',
    'hidepollsmenu' => '¿Ocultar Entrada del Menú?',
    'maxquestions' => 'Max. Preguntas por Encuesta',
    'maxanswers' => 'Max. Opciones por Pregunta',
    'answerorder' => 'Ordenar Resultados ...',
    'pollcookietime' => 'Cookie valida para Votar por',
    'polladdresstime' => 'Dirección IP valida para Votar por',
    'delete_polls' => '¿Borrar Encuestas con Propietario?',
    'aftersave' => 'Después de Guardar',
    'default_permissions' => 'Permisos por Defecto',
    'displayblocks' => 'Mostrar Bloques',
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Principal'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'General',
    'fs_permissions' => 'Permisos'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('Sí' => 1, 'No' => 0),
    1 => array('Sí' => true, 'No' => false),
    2 => array('Por envío' => 'submitorder', 'Por Votos' => 'voteorder'),
    9 => array('Ir a la Encuesta' => 'item', 'Ir a la Lista' => 'list', 'Ir a la Lista Publica' => 'plugin', 'ir al Inicio' => 'home', 'Ir al Panel de Control' => 'admin'),
    12 => array('Sin Acceso' => 0, 'Solo-Lectura' => 2, 'Lectura-escritura' => 3),
    13 => array('Izquierda' => 0, 'Derecha' => 1, 'Izquierda & derecha' => 2, 'Ninguno' => 3)
);

?>
