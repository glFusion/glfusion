<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | spanish_colombia_utf-8.php                                               |
// |                                                                          |
// | Spanish (Colombia) language file                                         |
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2014 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | John J. Toro A.        john DOT toro AT newroute DOT net                 |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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

$LANG_BAD_BEHAVIOR = array(
    'plugin_display_name' => 'Bad Behavior2',
    'page_title' => 'Bad Behavior2',
    'block_title_admin' => 'Bad Behavior2 - Administración',
    'description' => 'Bad Behavior complementa otras soluciones de link spam actuando como un gatekeeper, preveniendo el envio de basura de spammers, y en muchos casos, de haber leído tu sitio en primer lugar. Esto conserva baja la carga de tu sitio, mantiene los logs del sitio limpios, y puede ayudar a prevenir las condiciones de Denegación de servicios causada por spammers.',
    'block_title_list' => 'Bad Behavior2 - Registro de Eventos',
    'block_title_entry' => 'Detalles de Evento',
    'block_title_donate' => 'Donar',
    'list_entries' => 'Muestra eventos en el registro (%d)',
    'list_no_entries' => 'No hay registro de eventos.',
    'row_ip' => 'Dirección IP',
    'row_user_agent' => 'Agente',
    'row_referer' => 'Referente',
    'row_response' => 'Respuesta',
    'row_method' => 'Método',
    'row_protocol' => 'Protocolo',
    'row_date' => 'Fecha',
    'row_reason' => 'Razón',
    'self_test' => 'Probar Bad Behavior2',
    'link_back' => 'Regresar al registro de eventos',
    'title_show_headers' => 'Mostrar encabezados HTTP',
    'title_lookup_ip' => 'Buscar direcciones IP',
    'error' => 'Error',
    'fsockopen_not_available' => 'Sorry, the PHP function <code>fsockopen</code> is not available. Can not perform self test.',
    'fsockopen_failed' => 'Failed to open socket. Could not perform self test.',
    'donate_msg' => 'Si encuentras esta extensión útil, considera hacer una donación a Michael Hampton, el autor original de Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Visita la pagina de Bad Behavior</a>.',
    'denied_reason' => 'Razón',
    'results' => 'Bad Behavior2 - Resultados',
    'search' => 'Buscar',
    'stats_headline' => 'Bad Behavior2 - Estadísticas',
    'stats_reason' => 'Razón',
    'stats_blocked' => 'Bloqueado',
    'stats_no_hits' => 'No hay entradas.',
    'blocked_ips' => 'Bloquear una única dirección IP',
    'unblock' => 'Desbloquear dirección IP',
    'ip_date' => 'IP / Fecha / Estado',
    'headers' => 'Encabezados',
    'log_entries' => 'Eventos en el Registro',
    'list_ips' => 'Lista de IPs Prohibidas',
    'ban_ip' => 'Prohibir IPs',
    'ban_list_info' => 'Las IPs listadas a continuación están prohibidas en el sistema. Las prohibiciones manuales son permanentes. Las prohibiciones automáticas expiran después de 24 horas.',
    'ip_address' => 'Dirección IP',
    'type' => 'Tipo',
    'date' => 'Fecha',
    'reason' => 'Razón',
    'delete' => 'Borrar',
    'enter_ip' => 'IP',
    'delete_confirm_1' => 'Seguro que deseas quitar la prohibición a estas IPs?',
    'delete_confirm_2' => '¿Estas REALMENTE seguro?',
    'delete_info' => 'Borrar IP Prohibida',
    'manually_added' => 'Agregada Manualmente',
    'automatic_captcha' => 'Agregada Automáticamente (CAPTCHA)',
    'enter_ip_info' => 'Escribe las IPs a prohibir en el sitio. Cada IP debería estar en una linea aparte.',
    'ban_results' => 'Las IPs listadas a continuación tienen errores que impide que sean agregadas a la lista de prohibidas.',
    'duplicate_error' => 'La IP %s ya existe en la lista de prohibidas.',
    'invalid_ip' => 'La IP %s no es una dirección IPv4 valida.',
    'submit' => 'Enviar',
    'cancel' => 'Cancelar',
    'filter' => 'Filtro',
    'go' => 'Ir',
    'no_filter' => 'Sin Filtro',
    'manual' => 'Manual',
    'token' => 'Token',
    'captcha' => 'CAPTCHA',
    'auto_refresh_on' => 'Refresco Automático Sí',
    'auto_refresh_off' => 'Refresco Automático No',
);

$LANG_BB2_RESPONSE = array(
    '00000000' => 'Request Passed - No User Agent Specified',
    '136673cd' => 'IP address found on external blacklist',
    '17566707' => 'Required header \'Accept\' missing',
    '17f4e8c8' => 'User-Agent was found on blacklist',
    '21f11d3f' => 'User-Agent claimed to be AvantGo, claim appears false',
    '2b021b1f' => 'IP address found on http:BL blacklist',
    '2b90f772' => 'Connection: TE present, not supported by MSIE',
    '35ea7ffa' => 'Invalid language specified',
    '408d7e72' => 'POST comes too quickly after GET',
    '41feed15' => 'Header \'Pragma\' without \'Cache-Control\' prohibited for HTTP/1.1 requests',
    '45b35e30' => 'Header \'Referer\' is corrupt',
    '57796684' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
    '582ec5e4' => 'Header \'TE\' present but TE not specified in \'Connection\' header',
    '69920ee5' => 'Header \'Referer\' present but blank',
    '6c502ff1' => 'Bot not fully compliant with RFC 2965',
    '71436a15' => 'User-Agent claimed to be Yahoo, claim appears to be false',
    '799165c2' => 'Rotating user-agents detected',
    '7a06532b' => 'Required header \'Accept-Encoding\' missing',
    '7ad04a8a' => 'Prohibited header \'Range\' present',
    '7d12528e' => 'Prohibited header \'Range\' or \'Content-Range\' in POST request',
    '939a6fbb' => 'Banned proxy server in use',
    '9c9e4979' => 'Prohibited header \'via\' present',
    'a0105122' => 'Header \'Expect\' prohibited; resend without Expect',
    'a1084bad' => 'User-Agent claimed to be MSIE, with invalid Windows version',
    'a52f0448' => 'Header \'Connection\' contains invalid values',
    'b40c8ddc' => 'POST more than two days after GET',
    'b7830251' => 'Prohibited header \'Proxy-Connection\' present',
    'b9cc1d86' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
    'c1fa729b' => 'Use of rotating proxy servers detected',
    'cd361abb' => 'Referer did not point to a form on this site',
    'd60b87c7' => 'Trackback received via proxy server',
    'e3990b47' => 'Obviously fake trackback received',
    'dfd9b1ad' => 'Request contained a malicious JavaScript or SQL injection attack',
    'e4de0453' => 'User-Agent claimed to be msnbot, claim appears to be false',
    'e87553e1' => 'I know you and I don\'t like you, dirty spammer.',
    'f0dcb3fd' => 'Web browser attempted to send a trackback',
    'f1182195' => 'User-Agent claimed to be Googlebot, claim appears to be false.',
    'f9f2b8b9' => 'A User-Agent is required but none was provided.',
    'f9f3b8b0' => 'Question mark at end of query.',
    '96c0bd29' => 'SQL Injection detected',
    'f9f3b8b1' => 'do=register BOT Attack',
    'f9f3b8b2' => '/RK=0/RS= BOT',
    '96c0bd30' => 'IP Prohibida',
);


$PLG_bad_behavior_MESSAGE1 = '¡Si ves este mensaje, Bad Behavior2 <b>NO</b> esta instalado correctamente! Por favor lee cuidadosamente las instrucciones de instalación nuevamente.';
$PLG_bad_behavior_MESSAGE100 = 'La dirección IP ha sido desbloqueada.';
$PLG_bad_behavior_MESSAGE101 = 'Ocurrió un problema al  desbloquear la dirección IP.';
?>
