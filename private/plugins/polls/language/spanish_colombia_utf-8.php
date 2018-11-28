<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Polls Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2005 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

$LANG_POLLS = array(
    'polls'             => 'Encuestas',
    'results'           => 'Resultados',
    'pollresults'       => 'Resultados de la encuesta',
    'votes'             => 'votos',
    'vote'              => 'Votar',
    'pastpolls'         => 'Encuestas anteriores',
    'savedvotetitle'    => 'Voto guardado',
    'savedvotemsg'      => 'Se ha guardado tu voto para la encuesta',
    'pollstitle'        => 'Encuestas disponibles',
    'polltopics'        => 'Otras encuestas',
    'stats_top10'       => '10 encuestas principales',
    'stats_topics'      => 'Tema',
    'stats_votes'       => 'Votos',
    'stats_none'        => 'Parece no haber encuestas o que nadie ha votado.',
    'stats_summary'     => 'Encuestas (Respuestas) en el sistema',
    'open_poll'         => 'Abierto para votar',
    'answer_all'        => 'Please answer all remaining questions',
    'not_saved'         => 'Resultados no guardados',
    'upgrade1'          => 'You installed a new version of the Polls plugin. Please',
    'upgrade2'          => 'actualizar',
    'editinstructions'  => 'Debes indicar el ID, y al menos una pregunta y dos respuestas.',
    'pollclosed'        => 'This poll is closed for voting.',
    'pollhidden'        => 'Poll results will be available only after the Poll has closed.',
    'start_poll'        => 'Iniciar Encuesta',
    'deny_msg' => 'Access to this poll is denied.  Either the poll has been moved/removed or you do not have sufficient permissions.',
    'login_required'    => '<a href="'.$_CONF['site_url'].'/users.php" rel="nofollow">Login</a> required to vote',
    'username'          => 'Nombre',
    'ipaddress'         => 'Dirección IP',
    'date_voted'        => 'Date Voted',
    'description'       => 'Descripción',
    'general'           => 'General',
    'poll_questions'    => 'Poll Questions',
    'permissions'       => 'Permisos',
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Comentarios',
    2 => 'Entra un tema, al menos una pregunta y al menos una respuesta para la pregunta.',
    3 => 'Creada',
    4 => "Encuesta %s guardada",
    5 => 'Editor de Encuestas',
    6 => 'ID',
    7 => '(<i>NO uses espacios</i>)',
    8 => 'Mostrar en el Bloque de Encuestas',
    9 => 'Encuesta',
    10 => 'Respuestas / Votos / Comentarios',
    11 => "There was an error getting poll answer data about the poll %s",
    12 => "There was an error getting poll question data about the poll %s",
    13 => 'Crear Encuesta',
    14 => 'guardar',
    15 => 'cancelar',
    16 => 'borrar',
    17 => 'Please enter a Poll ID',
    18 => 'Listado de Encuestas',
    19 => 'Para modificar ó borrar, haz clic sobre el icono: <img src="/layout//images/admin/edit.png" /> o <img src="/layout//images/admin/delete.png" /> correspondiente. Para crear, haz clic sobre el enlace: "<b><i>Crear</i></b>" arriba.',
    20 => 'Votos',
    21 => 'Acceso Negado',
    22 => "You are trying to access a poll that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/poll.php\">go back to the poll administration screen</a>.",
    23 => 'New Poll',
    24 => 'Página de Inicio - Administrador',
    25 => 'Sí',
    26 => 'No',
    27 => 'Modificar',
    28 => 'Enviar',
    29 => 'Buscar',
    30 => 'Limitar Resultados',
    31 => 'Pregunta',
    32 => 'To remove this question from the poll, remove its question text',
    33 => 'Abierta',
    34 => 'Tema:',
    35 => 'Esta encuesta tiene',
    36 => 'preguntas más.',
    37 => 'Ocultar resultados mientras este abierta',
    38 => 'Mientras la encuesta esta abierta, solo el propietario & root pueden ver los resultados',
    39 => 'El tema solo será mostrado si tiene almenos 1 pregunta.',
    40 => 'Ver todas las respuestas a esta Encuesta',
    41 => '¿Realmente deseas borrar esta Encuesta?',
    42 => 'Are you absolutely sure you want to delete this Poll?  All questions, answers and comments that are associated with this Poll will also be permanently deleted from the database.',
    43 => 'Requiere Inicio de Sesión para Votar',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll'                 => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result'          => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote'            => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Tu encuesta se guardó satisfactoriamente.';
$PLG_polls_MESSAGE20 = 'Tu encuesta se ha borrado satisfactoriamente.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Actualización de extensión no suportada.';
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

$LANG_configSelect['polls'] = array(
    0 => array(1=>'Sí', 0=>'No'),
    1 => array(true=>'No', false=>'Sí'),
    2 => array('submitorder'=>'Por envío', 'voteorder'=>'Por Votos'),
    9 => array('item'=>'Ir a la Encuesta', 'list'=>'Ir a la Lista', 'plugin'=>'Ir a la Lista Publica', 'home'=>'ir al Inicio', 'admin'=>'Ir al Panel de Control'),
    12 => array(0=>'Sin Acceso', 2=>'Solo-Lectura', 3=>'Lectura-escritura'),
    13 => array(0=>'Izquierda', 1=>'Derecha', 2=>'Izquierda & derecha', 3=>'Ninguno')
);

?>
